// ** MODULES

var FS = require("fs"),
MySQL = require("mysql"),
SteamUser = require("steam-user"),
SteamTotp = require("steam-totp"),
SteamCommunity = require("steamcommunity"),
TradeOfferManager = require("steam-tradeoffer-manager");

// ** CONFIGURATION

var domain = FS.readFileSync("../../documents/domain.txt").toString(),
config = require("./config.json");

// ** VARIABLES

if(["localhost", "unlockvgo.com", "www.unlockvgo.com"].indexOf(domain) != -1)
{
	if(domain == "localhost")
	{
		var connection = MySQL.createConnection(
		{
			host: config.local_database_host,
			user: config.local_database_user,
			password: config.local_database_pass,
			database: config.local_database_db
		});
	}
	else
	{
		var connection = MySQL.createConnection(
		{
			host: config.production_database_host,
			user: config.production_database_user,
			password: config.production_database_pass,
			database: config.production_database_db
		});
	}
}

var client = new SteamUser(),
community = new SteamCommunity(),
manager = new TradeOfferManager(
{
	steam: client,
	community: community,
	language: "en"
}),
process_trade_offers = 1;

// ** INITIALIZE

connection.connect();

client.logOn(
{
	"accountName": config.username,
	"password": config.password,
	"twoFactorCode": SteamTotp.generateAuthCode(config.shared_secret)
});

// ** CALLBACKS

client.on("loggedOn", () =>
{
	client.setPersona(SteamUser.EPersonaState.Online);

	console.log("Signed in as " + config.username + " (" + client.steamID.getSteamID64() + ")");
});

client.on("webSession", (session_id, cookies) =>
{
	manager.setCookies(cookies, function(error)
	{
		if(error)
		{
			console.log("Unable to get API key");
		}
		else
		{
			console.log("Retrieved API key: " + manager.apiKey);

			setInterval(processTradeOffers, 2000);
		}
	});

	community.setCookies(cookies);
	community.startConfirmationChecker(20000, config.identity_secret);
});

manager.on("sentOfferChanged", function(offer, oldState)
{
	if(TradeOfferManager.ETradeOfferState[offer.state] == "Accepted")
	{
		connection.query("UPDATE `key_exchanges` SET `send_steam_accepted` = 1 WHERE `send_steam_offer_id` = " + offer.id + " LIMIT 1", function(error, results, fields)
		{
			if(error)
			{
				console.log(error);
			}

			process_trade_offers = 1;
		});
	}
});

client.on("error", function(error)
{
	console.log(error);
});

// ** FUNCTIONS

function processTradeOffers()
{
	if(process_trade_offers)
	{
		process_trade_offers = 0;

		// -----

		connection.query("SELECT `id`, `send_steam_items`, `steam_trade_url` FROM `key_exchanges` WHERE `send_steam` IS NOT NULL AND `send_steam_items` IS NOT NULL AND `send_steam_offer_id` IS NULL LIMIT 1", function(error, results, fields)
		{
			if(error)
			{
				console.log(error);
			}
			else
			{
				if(typeof results[0] !== "undefined")
				{
					var items = results[0].send_steam_items.split(",");
					let offer = manager.createOffer(results[0].steam_trade_url);
					for(i = 0, j = items.length; i < j; i ++)
					{
						var item_data = items[i].split("|separator|"), item = {
							assetid: item_data[0],
							appid: item_data[1],
							contextid: item_data[2],
							amount: item_data[3]
						};
						offer.addTheirItem(item);
					}

					offer.setMessage("Request from unlockvgo.com/?exchange");
					offer.send(function(error, status)
					{
						if(error)
						{
							console.log(error);

							connection.query("UPDATE `key_exchanges` SET `cancel` = 1 WHERE `id` = " + results[0].id + " LIMIT 1", function(error, results, fields)
							{
								if(error)
								{
									console.log(error);
								}
								else
								{
									console.log("Offer #" + offer.id + " has been canceled");
								}
							});
						}
						else
						{
							connection.query("UPDATE `key_exchanges` SET `send_steam_offer_id` = " + offer.id + " WHERE `id` = " + results[0].id + " LIMIT 1", function(error, results, fields)
							{
								if(error)
								{
									console.log(error);
								}
								else
								{
									console.log("Offer #" + offer.id + " has been sent");
								}
							});
						}
					});
				}
			}
		});

		connection.query("SELECT `id`, `receive_steam_items`, `steam_trade_url` FROM `key_exchanges` WHERE `receive_steam` IS NOT NULL AND `receive_steam_items` IS NOT NULL AND `receive_steam_offer_id` IS NULL LIMIT 1", function(error, results, fields)
		{
			if(error)
			{
				console.log(error);

				process_trade_offers = 1;
			}
			else
			{
				if(typeof results[0] !== "undefined")
				{
					var items = results[0].receive_steam_items.split(",");
					let offer = manager.createOffer(results[0].steam_trade_url);
					for(i = 0, j = items.length; i < j; i ++)
					{
						var item_data = items[i].split("|separator|"), item = {
							assetid: item_data[0],
							appid: item_data[1],
							contextid: item_data[2],
							amount: item_data[3]
						};
						offer.addMyItem(item);
					}

					offer.setMessage("Your new key(s) from unlockvgo.com/?exchange");
					offer.send(function(error, status)
					{
						if(error)
						{
							console.log(error);

							connection.query("UPDATE `key_exchanges` SET `cancel` = 1 WHERE `id` = " + results[0].id + " LIMIT 1", function(error, results, fields)
							{
								if(error)
								{
									console.log(error);
								}
								else
								{
									console.log("Offer #" + offer.id + " has been canceled");
								}

								process_trade_offers = 1;
							});
						}
						else
						{
							community.checkConfirmations();

							connection.query("UPDATE `key_exchanges` SET `receive_steam_offer_id` = " + offer.id + " WHERE `id` = " + results[0].id + " LIMIT 1", function(error, results, fields)
							{
								if(error)
								{
									console.log(error);
								}
								else
								{
									console.log("Offer #" + offer.id + " has been sent");
								}

								process_trade_offers = 1;
							});
						}
					});
				}
				else
				{
					process_trade_offers = 1;
				}
			}
		});
	}
}