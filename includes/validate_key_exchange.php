<?php
	session_start();

	if(isset($_SESSION['steam_id']))
	{
		include_once "connection.php";

		$query_1 = $connection -> prepare("SELECT `steam_trade_url` FROM `users` WHERE `steam_id` = :steam_id LIMIT 1");
		$query_1 -> bindParam(":steam_id", $_SESSION['steam_id']);
		$query_1 -> execute();
		$query_1_result = $query_1 -> fetch();

		if($query_1_result['steam_trade_url'] != NULL)
		{
			include_once "function_currency_format.php";

			$my_keys = json_decode($_POST['my_keys'], true);
			$exchange = json_decode($_POST['exchange'], true);

			$extended_key_info = array();

			$query_2 = $connection -> prepare("SELECT `name`, `price` FROM `extended_key_info`");
			$query_2 -> execute();

			while($query_2_result = $query_2 -> fetch())
			{
				$extended_key_info[$query_2_result['name']] = array(
					"price" => (float) $query_2_result['price']);
			}

			$send_expresstrade = NULL;
			$send_steam = NULL;
			$receive_expresstrade = NULL;
			$receive_steam = NULL;
			$send_value = 0.00;
			$receive_value = 0.00;

			for($i = 0, $j = sizeof($my_keys); $i < $j; $i ++)
			{
				if(isset($extended_key_info[$my_keys[$i]['name']]))
				{
					$key = $my_keys[$i];
					$name = $key['name'];
					$selected_quantity = preg_replace("/[^0-9]/", "", $key['selected_quantity']);

					if($selected_quantity == NULL || $selected_quantity < 1)
					{
						$selected_quantity = 1;
					}

					switch($key['platform'])
					{
						case 1: // ExpressTrade
						{
							if($send_expresstrade == NULL)
							{
								$send_expresstrade = $name . "|separator|" . $selected_quantity;
							}
							else
							{
								$send_expresstrade .= "," . $name . "|separator|" . $selected_quantity;
							}
							break;
						}
						case 2: // Steam
						{
							if($send_steam == NULL)
							{
								$send_steam = $name . "|separator|" . $selected_quantity . "|separator|" . $key['app_id'];
							}
							else
							{
								$send_steam .= "," . $name . "|separator|" . $selected_quantity . "|separator|" . $key['app_id'];
							}
							break;
						}
					}

					$send_value += (float) currency_format(($extended_key_info[$name]['price'] - ($extended_key_info[$name]['price'] * 0.10)) * $selected_quantity, 2, ".", "", ""); // CHANGE TO 0.10
				}
			}

			for($i = 0, $j = sizeof($exchange); $i < $j; $i ++)
			{
				if(isset($extended_key_info[$exchange[$i]['name']]))
				{
					$key = $exchange[$i];
					$name = $key['name'];
					$selected_quantity = preg_replace("/[^0-9]/", "", $key['selected_quantity']);

					if($selected_quantity == NULL || $selected_quantity < 1)
					{
						$selected_quantity = 1;
					}

					switch($key['platform'])
					{
						case 1: // ExpressTrade
						{
							if($receive_expresstrade == NULL)
							{
								$receive_expresstrade = $name . "|separator|" . $selected_quantity;
							}
							else
							{
								$receive_expresstrade .= "," . $name . "|separator|" . $selected_quantity;
							}
							break;
						}
						case 2: // Steam
						{
							if($receive_steam == NULL)
							{
								$receive_steam = $name . "|separator|" . $selected_quantity . "|separator|" . $key['app_id'];
							}
							else
							{
								$receive_steam .= "," . $name . "|separator|" . $selected_quantity . "|separator|" . $key['app_id'];
							}
							break;
						}
					}

					$receive_value += (float) currency_format($extended_key_info[$name]['price'] * $selected_quantity, 2, ".", "", "");
				}
			}

			if($send_value && $receive_value && ((float) currency_format($send_value, 2, ".", "", "")) >= ((float) currency_format($receive_value, 2, ".", "", "")))
			{
				$remaining_balance = (float) currency_format($send_value - $receive_value, 2, ".", "", "");

				$query_3 = $connection -> prepare("INSERT INTO `key_exchanges` (`send_expresstrade`, `receive_expresstrade`, `send_steam`, `receive_steam`, `issuer_id`, `remaining_balance`, `steam_trade_url`) VALUES (:send_expresstrade, :receive_expresstrade, :send_steam, :receive_steam, :issuer_id, :remaining_balance, :steam_trade_url)");
				$query_3 -> bindParam(":send_expresstrade", $send_expresstrade);
				$query_3 -> bindParam(":receive_expresstrade", $receive_expresstrade);
				$query_3 -> bindParam(":send_steam", $send_steam);
				$query_3 -> bindParam(":receive_steam", $receive_steam);
				$query_3 -> bindParam(":issuer_id", $_SESSION['steam_id']);
				$query_3 -> bindParam(":remaining_balance", $remaining_balance);
				$query_3 -> bindParam(":steam_trade_url", $query_1_result['steam_trade_url']);
				$query_3 -> execute();

				echo "Key Exchange Validated|separator|Valid selection of items.";
			}
			else
			{
				echo "Alert|separator|Invalid selection of items.";
			}
		}
		else
		{
			echo "Alert|separator|<a class = \"p_link\" href = \"?profile=" . $_SESSION['steam_id'] . "\">Set your Steam Trade URL</a> first.";
		}
	}
	else
	{
		echo "Alert|separator|Guests can't exchange keys.";
	}
?>