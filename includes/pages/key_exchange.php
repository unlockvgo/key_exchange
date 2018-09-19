<?php
	session_start();
?>

<html>
	<head>
		<style>
			.p_exchange_button
			{
				width: 80px;
				height: 80px;

				margin: 63.7px 0px 0px -41.65px;

				background: #FFC62E;

				border: 1px solid #F59C00;
				border-radius: 50%;

				-webkit-touch-callout: none;
				-webkit-user-select: none;
				-khtml-user-select: none;
				user-select: none;
				standard-user-select: none;

				top: 50%;
				left: 50%;

				z-index: 2;

				position: absolute;

				overflow: hidden;

				display: inline-block;
			}

			.p_exchange_button:hover
			{
				background: #FEC019;

				cursor: pointer;
			}

			.p_link
			{
				color: #FFC62E;

				text-decoration: none;
			}

			.p_link:hover
			{
				color: #F59C00;

				cursor: pointer;
			}
		</style>

		<script type = "text/javascript">
			var p_last_element_p_id_my_keys, p_last_element_p_id_exchange, p_selected_my_keys = [], p_selected_my_keys_value = 0.00, p_selected_my_keys_platform = 0, p_selected_my_keys_app_id = 0, p_selected_exchange = [], p_selected_exchange_value = 0.00, p_selected_exchange_platform = 0, p_selected_exchange_app_id = 0, p_show_key_info_move_up_and_down_interval = -1, p_call_process_key_exchange = 1, p_keys_loaded = 0, p_perform_action = 1;
			function P_MoveExchangeButton(quadrant, return_position)
			{
				if(return_position)
				{
					$("#p_id_exchange_button").css(
					{
						"transform": "translate(0px, 0px)",
						"transition": "1s"
					});

					switch(quadrant)
					{
						case 1: case 3: // TOP LEFT, BOTTOM LEFT
						{
							$("#p_id_exchange_quadrant_label_" + quadrant).removeClass("animated fadeOutLeft");
							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeInLeft");
							break;
						}
						case 2: case 4: // TOP RIGHT, BOTTOM RIGHT
						{
							$("#p_id_exchange_quadrant_label_" + quadrant).removeClass("animated fadeOutRight");
							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeInRight");
							break;
						}
					}
				}
				else
				{
					switch(quadrant)
					{
						case 1: // TOP LEFT
						{
							$("#p_id_exchange_button").css(
							{
								"transform": "translate(41px, 0px)",
								"transition": "1s"
							});

							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeOutLeft");
							break;
						}
						case 2: // TOP RIGHT
						{
							$("#p_id_exchange_button").css(
							{
								"transform": "translate(-40px, 0px)",
								"transition": "1s"
							});

							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeOutRight");
							break;
						}
						case 3: // BOTTOM LEFT
						{
							$("#p_id_exchange_button").css(
							{
								"transform": "translate(0px, -41px)",
								"transition": "1s"
							});

							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeOutLeft");
							break;
						}
						case 4: // BOTTOM RIGHT
						{
							$("#p_id_exchange_button").css(
							{
								"transform": "translate(0px, -41px)",
								"transition": "1s"
							});

							$("#p_id_exchange_quadrant_label_" + quadrant).addClass("animated fadeOutRight");
							break;
						}
					}
				}
			}

			function P_ListUserKeys(my_keys_state, platform, app_id, initial)
			{
				var unrestricted_call = 0;
				if(!p_keys_loaded)
				{
					if(!my_keys_state && initial)
					{
						p_keys_loaded = 1;

						// -----

						unrestricted_call = 1;
					}
				}

				if(p_perform_action || unrestricted_call)
				{
					p_perform_action = 0;

					// -----

					if(!initial)
					{
						g_element_id_sound_action.play();

						if(my_keys_state == 1)
						{
							var element_p_id_my_keys = "p_id_my_keys_" + platform + "_" + app_id;
							document.getElementById(p_last_element_p_id_my_keys).style.removeProperty("background");
							document.getElementById(element_p_id_my_keys).style.background = "#F59C00";

							document.getElementById("p_id_my_keys_container").innerHTML =
							"<table style = \"width: 100%; height: 100%; text-align: center;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
								<td>\
									<img height = \"100px\" alt = \"Loading\" src = \"images/loading.svg\"/>\
								</td>\
							</table>";

							p_last_element_p_id_my_keys = element_p_id_my_keys;
						}
						else
						{
							var element_p_id_exchange = "p_id_exchange_" + platform + "_" + app_id;
							document.getElementById(p_last_element_p_id_exchange).style.removeProperty("background");
							document.getElementById(element_p_id_exchange).style.background = "#F59C00";

							document.getElementById("p_id_exchange_container").innerHTML =
							"<table style = \"width: 100%; height: 100%; text-align: center;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
								<td>\
									<img height = \"100px\" alt = \"Loading\" src = \"images/loading.svg\"/>\
								</td>\
							</table>";

							p_last_element_p_id_exchange = element_p_id_exchange;
						}
					}
					else
					{
						if(my_keys_state == 1)
						{
							p_last_element_p_id_my_keys = "p_id_my_keys_" + platform + "_" + app_id;
						}
						else
						{
							p_last_element_p_id_exchange = "p_id_exchange_" + platform + "_" + app_id;
						}
					}

					$.ajax(
					{
						type: "POST",
						url: "includes/ajax_call_handler.php",
						data: {include_path: "list_user_keys.php", my_keys_state: my_keys_state, platform: platform, app_id: app_id},

						success: function(html)
						{
							if(my_keys_state == 1)
							{
								$("#p_id_my_keys_container").html(html);

								for(i = 0, j = p_selected_my_keys.length; i < j; i ++)
								{
									if(p_selected_my_keys[i]['platform'] == platform && p_selected_my_keys[i]['app_id'] == app_id)
									{
										if(p_selected_my_keys[i]['selected_quantity'] == p_selected_my_keys[i]['max_quantity'])
										{
											$(document.getElementById("p_id_my_keys_" + platform + "_" + app_id + "_" + p_selected_my_keys[i]['name'])).remove();
										}
										else
										{
											document.getElementById("p_id_my_keys_" + platform + "_" + app_id + "_" + p_selected_my_keys[i]['name'] + "_quantity").innerHTML = (p_selected_my_keys[i]['max_quantity'] - p_selected_my_keys[i]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
										}
									}
								}

								p_selected_my_keys_platform = platform;
								p_selected_my_keys_app_id = app_id;
							}
							else
							{
								$("#p_id_exchange_container").html(html);

								for(i = 0, j = p_selected_exchange.length; i < j; i ++)
								{
									if(p_selected_exchange[i]['platform'] == platform && p_selected_exchange[i]['app_id'] == app_id)
									{
										if(p_selected_exchange[i]['selected_quantity'] == p_selected_exchange[i]['max_quantity'])
										{
											$(document.getElementById("p_id_exchange_" + platform + "_" + app_id + "_" + p_selected_exchange[i]['name'])).remove();
										}
										else
										{
											document.getElementById("p_id_exchange_" + platform + "_" + app_id + "_" + p_selected_exchange[i]['name'] + "_quantity").innerHTML = (p_selected_exchange[i]['max_quantity'] - p_selected_exchange[i]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
										}
									}
								}

								p_selected_exchange_platform = platform;
								p_selected_exchange_app_id = app_id;
							}

							p_perform_action = 1;
						}
					});
				}
			}

			function P_ShowKeyInfo(name, opens, price, image, image_background_state, platform, app_id, app_name, extra)
			{
				if(!g_modal_state)
				{
					g_modal_state = 1;

					clearInterval(p_show_key_info_move_up_and_down_interval);

					// -----

					var html;
					g_element_id_sound_action.play();

					html =
					"<div style = \"text-align: center;\">\
						<div style = \"width: 298px; height: 260px; margin-top: -100px; text-align: left; position: relative; display: inline-block;\">\
							<img id = \"p_id_show_key_info_image\" class = \"animation_group_12_1\" style = \"width: 250px; margin-left: 24px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " position: absolute;\" alt = \"" + name + "\" src = \"images/keys/" + image + "\"/>\
							\
							<div class = \"p_show_key_info_details\" style = \"width: 298px; height: 260px; font-size: 15pt; font-weight: bold; color: #EEEEEE; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; z-index: 1; position: absolute;\">\
								" + name + "\
							</div>\
							\
							<div class = \"p_show_key_info_details\" style = \"width: 298px; height: 260px; font-size: 13pt; font-weight: bold; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 265px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; z-index: 1; position: absolute;\">\
								" + app_name + " Key\
							</div>\
							\
							<div class = \"p_show_key_info_details\" style = \"width: 298px; height: 260px; font-size: 25pt; font-weight: bold; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 340px; z-index: 1; position: absolute;\">\
								" + price + "\
							</div>\
						</div>\
					</div>\
					\
					<div class = \"p_show_key_info_details\" style = \"font-size: 10pt; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased;\">\
						THIS KEY OPENS\
					</div>\
					\
					<div style = \"text-align: center;\">\
						<div class = \"p_show_key_info_details\" style = \"width: 298px; margin-top: 5px; display: inline-block;\">\
							<div style = \"margin: -5px 0px 0px -5px;\">";

					for(i = 0, j = opens.length; i < j; i ++)
					{
						html +=
						"<div style = \"margin: 5px 0px 0px 5px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " overflow: hidden; display: inline-block;\" title = \"" + opens[i] + "\">\
							<img width = \"96px\" alt = \"" + opens[i] + "\" src = \"images/containers/" + opens[i] + ".png\"/>\
						</div>";
					}

					html +=
					"		</div>\
						</div>\
					</div>\
					\
					<div style = \"margin-top: 15px; text-align: center;\">\
						<img id = \"id_toggle_animation_group_12\" style = \"height: 15px; cursor: pointer;\" alt = \"Toggle Animation(s)\" src = \"images/animation.png\" onclick = \"ToggleAnimationGroup(12);\" title = \"Toggle Animation(s)\"/>\
					</div>";

					document.getElementById("id_modal_content_main").innerHTML = html;

					html =
					"<div class = \"modal_button\" onclick = \"HideStandardModal();\">\
						DISMISS\
					</div>";

					switch(platform)
					{
						case 1: // ExpressTrade
						{
							html +=
							"\
							<div style = \"display: inline-block;\">\
								<a href = \"https://opskins.com/?app=" + extra + "&loc=shop_search&sort=lh&type=key\" target = \"_blank\">\
									<div class = \"modal_button\">\
										BUY ON OPSKINS\
									</div>\
								</a>\
							</div>";
							break;
						}
						case 2: // Steam
						{
							html +=
							"\
							<div style = \"display: inline-block;\">\
								<a href = \"https://bitskins.com/?market_hash_name=" + encodeURI(name).replace(/#/g, "%23") + "&appid=" + app_id + "&is_stattrak=0&has_stickers=0&is_souvenir=0&show_trade_delayed_items=0&sort_by=bumped_at&order=desc\" target = \"_blank\">\
									<div class = \"modal_button\">\
										BUY ON BITSKINS\
									</div>\
								</a>\
							</div>";
							break;
						}
					}

					document.getElementById("id_modal_content_footer").innerHTML = html;

					ShowModal(1);

					// -----

					var selector_p_id_show_key_info_image = $("#p_id_show_key_info_image");
					selector_p_id_show_key_info_image.addClass("animated bounceInDown");
					$(".p_show_key_info_details").addClass("animated zoomIn");

					if(g_animation_status[11] == 0)
					{
						document.getElementById("id_toggle_animation_group_12").src = "images/animation_off.png";

						if(g_mobile)
						{
							document.getElementById("id_toggle_animation_group_12").style.cursor = "not-allowed";
						}
					}

					p_show_key_info_move_up_and_down_interval = setInterval(function()
					{
						clearInterval(p_show_key_info_move_up_and_down_interval);

						// -----

						selector_p_id_show_key_info_image.removeClass("animated bounceInDown");

						OnAnimationGroupStatusChange(12, g_animation_status[11]);
					}, 1000);
				}
			}

			function P_AddKeyToTrade(my_keys_state, name, opens, price_1, price_2, quantity_1, quantity_2, key_background, image, image_background_state, platform, app_id, app_name, extra)
			{
				if(p_perform_action)
				{
					p_perform_action = 0;

					// -----

					g_element_id_sound_action.play();

					if(my_keys_state == 1)
					{
						var key = p_selected_my_keys.findIndex(p_selected_my_keys => p_selected_my_keys.name == name);
						if(key == -1)
						{
							var element_identifier_part = platform + "_" + app_id + "_" + name, old_selected_value = p_selected_my_keys_value, html =
							"\
							<div id = \"p_id_selected_my_keys_" + element_identifier_part + "\" style = \"margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;\" onclick = 'P_RemoveKeyFromTrade(" + my_keys_state + ", \"" + name + "\", " + JSON.stringify(opens) + ", " + price_1 + ", \"" + price_2 + "\", " + quantity_1 + ", \"" + quantity_2 + "\", \"" + key_background + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\");' oncontextmenu = 'P_ShowKeyInfo(\"" + name + "\", " + JSON.stringify(opens) + ", \"" + price_2 + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\"); return false;'>\
								<div style = \"width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;\" title = \"" + name + " - " + price_2 + "\">\
									<img style = \"width: 70px; height: 70px; position: absolute;\" alt = \"Key Background\" src = \"images/" + key_background + "\"/>\
									\
									<img class = \"animation_group_11_1\" style = \"width: 70px; height: 70px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " z-index: 1; position: absolute;\" alt = \"" + name + "\" src = \"images/keys/" + image + "\"/>\
									\
									<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
										x<div id = \"p_id_selected_my_keys_" + element_identifier_part + "_quantity\" style = \"display: inline-block;\">1</div>\
									</div>\
									\
									<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
										" + price_2 + "\
									</div>\
								</div>\
							</div>";

							p_selected_my_keys.splice(p_selected_my_keys.length, 0, {
								name: name,
								price: price_1,
								selected_quantity: 1,
								max_quantity: quantity_1,
								platform: platform,
								app_id: app_id});

							p_selected_my_keys_value += price_1;

							key = p_selected_my_keys.findIndex(p_selected_my_keys => p_selected_my_keys.name == name);

							if(p_selected_my_keys.length == 1)
							{
								document.getElementById("p_id_send_container").innerHTML = html;
							}
							else
							{
								$(document.getElementById("p_id_send_container")).append(html);
							}

							document.getElementById("p_id_my_keys_" + platform + "_" + app_id + "_" + name + "_quantity").innerHTML = (quantity_1 - p_selected_my_keys[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});

							$("#p_id_selected_my_keys_value").stop().prop("number", old_selected_value).animateNumber(
							{
								number: p_selected_my_keys_value,
								numberStep: function(now, tween)
								{
									var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
									if(!decimal_1 && !decimal_2)
									{
										$(tween.elem).text(value.substr(0, size_of_value - 3));
									}
									else
									{
										$(tween.elem).text(value);
									}
								}
							});
						}
						else
						{
							if(p_selected_my_keys[key]['selected_quantity'] < quantity_1)
							{
								var element_identifier_part = platform + "_" + app_id + "_" + name + "_quantity", old_selected_value = p_selected_my_keys_value;
								p_selected_my_keys_value += p_selected_my_keys[key]['price'];
								p_selected_my_keys[key]['selected_quantity'] += 1;

								document.getElementById("p_id_my_keys_" + element_identifier_part).innerHTML = (quantity_1 - p_selected_my_keys[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
								document.getElementById("p_id_selected_my_keys_" + element_identifier_part).innerHTML = p_selected_my_keys[key]['selected_quantity'].toLocaleString("en", {minimumFractionDigits: 0});

								$("#p_id_selected_my_keys_value").stop().prop("number", old_selected_value).animateNumber(
								{
									number: p_selected_my_keys_value,
									numberStep: function(now, tween)
									{
										var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
										if(!decimal_1 && !decimal_2)
										{
											$(tween.elem).text(value.substr(0, size_of_value - 3));
										}
										else
										{
											$(tween.elem).text(value);
										}
									}
								});
							}
						}

						if(p_selected_my_keys[key]['selected_quantity'] == quantity_1)
						{
							$(document.getElementById("p_id_my_keys_" + platform + "_" + app_id + "_" + name)).remove();
						}
					}
					else
					{
						var key = p_selected_exchange.findIndex(p_selected_exchange => p_selected_exchange.name == name);
						if(key == -1)
						{
							var element_identifier_part = platform + "_" + app_id + "_" + name, old_selected_value = p_selected_exchange_value, html =
							"\
							<div id = \"p_id_selected_exchange_" + element_identifier_part + "\" style = \"margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;\" onclick = 'P_RemoveKeyFromTrade(" + my_keys_state + ", \"" + name + "\", " + JSON.stringify(opens) + ", " + price_1 + ", \"" + price_2 + "\", " + quantity_1 + ", \"" + quantity_2 + "\", \"" + key_background + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\");' oncontextmenu = 'P_ShowKeyInfo(\"" + name + "\", " + JSON.stringify(opens) + ", \"" + price_2 + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\"); return false;'>\
								<div style = \"width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;\" title = \"" + name + " - " + price_2 + "\">\
									<img style = \"width: 70px; height: 70px; position: absolute;\" alt = \"Key Background\" src = \"images/" + key_background + "\"/>\
									\
									<img class = \"animation_group_11_1\" style = \"width: 70px; height: 70px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " z-index: 1; position: absolute;\" alt = \"" + name + "\" src = \"images/keys/" + image + "\"/>\
									\
									<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
										x<div id = \"p_id_selected_exchange_" + element_identifier_part + "_quantity\" style = \"display: inline-block;\">1</div>\
									</div>\
									\
									<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
										" + price_2 + "\
									</div>\
								</div>\
							</div>";

							p_selected_exchange.splice(p_selected_exchange.length, 0, {
								name: name,
								price: price_1,
								selected_quantity: 1,
								max_quantity: quantity_1,
								platform: platform,
								app_id: app_id});

							p_selected_exchange_value += price_1;

							key = p_selected_exchange.findIndex(p_selected_exchange => p_selected_exchange.name == name);

							if(p_selected_exchange.length == 1)
							{
								document.getElementById("p_id_receive_container").innerHTML = html;
							}
							else
							{
								$(document.getElementById("p_id_receive_container")).append(html);
							}

							document.getElementById("p_id_exchange_" + platform + "_" + app_id + "_" + name + "_quantity").innerHTML = (quantity_1 - p_selected_exchange[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});

							$("#p_id_selected_exchange_value").stop().prop("number", old_selected_value).animateNumber(
							{
								number: p_selected_exchange_value,
								numberStep: function(now, tween)
								{
									var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
									if(!decimal_1 && !decimal_2)
									{
										$(tween.elem).text(value.substr(0, size_of_value - 3));
									}
									else
									{
										$(tween.elem).text(value);
									}
								}
							});
						}
						else
						{
							if(p_selected_exchange[key]['selected_quantity'] < quantity_1)
							{
								var element_identifier_part = platform + "_" + app_id + "_" + name + "_quantity", old_selected_value = p_selected_exchange_value;
								p_selected_exchange_value += p_selected_exchange[key]['price'];
								p_selected_exchange[key]['selected_quantity'] += 1;

								document.getElementById("p_id_exchange_" + element_identifier_part).innerHTML = (quantity_1 - p_selected_exchange[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
								document.getElementById("p_id_selected_exchange_" + element_identifier_part).innerHTML = p_selected_exchange[key]['selected_quantity'].toLocaleString("en", {minimumFractionDigits: 0});

								$("#p_id_selected_exchange_value").stop().prop("number", old_selected_value).animateNumber(
								{
									number: p_selected_exchange_value,
									numberStep: function(now, tween)
									{
										var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
										if(!decimal_1 && !decimal_2)
										{
											$(tween.elem).text(value.substr(0, size_of_value - 3));
										}
										else
										{
											$(tween.elem).text(value);
										}
									}
								});
							}
						}

						if(p_selected_exchange[key]['selected_quantity'] == quantity_1)
						{
							$(document.getElementById("p_id_exchange_" + platform + "_" + app_id + "_" + name)).remove();
						}
					}

					OnAnimationGroupStatusChange(11, g_animation_status[10]);

					p_perform_action = 1;
				}
			}

			function P_RemoveKeyFromTrade(my_keys_state, name, opens, price_1, price_2, quantity_1, quantity_2, key_background, image, image_background_state, platform, app_id, app_name, extra)
			{
				if(p_perform_action)
				{
					p_perform_action = 0;

					// -----

					g_element_id_sound_action.play();

					if(my_keys_state == 1)
					{
						var key = p_selected_my_keys.findIndex(p_selected_my_keys => p_selected_my_keys.name == name);
						if(key != -1)
						{
							var element_identifier_part = platform + "_" + app_id + "_" + name + "_quantity", old_selected_value = p_selected_my_keys_value;
							p_selected_my_keys_value -= p_selected_my_keys[key]['price'];

							if(p_selected_my_keys_value < 0.00)
							{
								p_selected_my_keys_value = 0.00;
							}

							if(p_selected_my_keys_platform == platform && p_selected_my_keys_app_id == app_id)
							{
								if(p_selected_my_keys[key]['selected_quantity'] == p_selected_my_keys[key]['max_quantity'])
								{
									html =
									"\
									<div id = \"p_id_my_keys_" + platform + "_" + app_id + "_" + name + "\" style = \"margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;\" onclick = 'P_AddKeyToTrade(" + my_keys_state + ", \"" + name + "\", " + JSON.stringify(opens) + ", " + price_1 + ", \"" + price_2 + "\", " + quantity_1 + ", \"" + quantity_2 + "\", \"" + key_background + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\");' oncontextmenu = 'P_ShowKeyInfo(\"" + name + "\", " + JSON.stringify(opens) + ", \"" + price_2 + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\"); return false;'>\
										<div style = \"width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;\" title = \"" + name + " - " + price_2 + "\">\
											<img style = \"width: 70px; height: 70px; position: absolute;\" alt = \"Key Background\" src = \"images/" + key_background + "\"/>\
											\
											<img class = \"animation_group_11_1\" style = \"width: 70px; height: 70px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " z-index: 1; position: absolute;\" alt = \"" + name + "\" src = \"images/keys/" + image + "\"/>\
											\
											<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
												x<div id = \"p_id_my_keys_" + platform + "_" + app_id + "_" + name + "_quantity\" style = \"display: inline-block;\">1</div>\
											</div>\
											\
											<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
												" + price_2 + "\
											</div>\
										</div>\
									</div>";

									$(document.getElementById("p_id_my_keys_container")).prepend(html);
								}
							}

							if(p_selected_my_keys[key]['selected_quantity'] > 1)
							{
								p_selected_my_keys[key]['selected_quantity'] -= 1;

								if(p_selected_my_keys_platform == platform && p_selected_my_keys_app_id == app_id)
								{
									document.getElementById("p_id_my_keys_" + element_identifier_part).innerHTML = (p_selected_my_keys[key]['max_quantity'] - p_selected_my_keys[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
								}

								document.getElementById("p_id_selected_my_keys_" + element_identifier_part).innerHTML = p_selected_my_keys[key]['selected_quantity'].toLocaleString("en", {minimumFractionDigits: 0});
							}
							else
							{
								var max_quantity = p_selected_my_keys[key]['max_quantity'];
								p_selected_my_keys.splice(key, 1);

								if(p_selected_my_keys_platform == platform && p_selected_my_keys_app_id == app_id)
								{
									document.getElementById("p_id_my_keys_" + element_identifier_part).innerHTML = max_quantity.toLocaleString("en", {minimumFractionDigits: 0});
								}

								$(document.getElementById("p_id_selected_my_keys_" + platform + "_" + app_id + "_" + name)).remove();

								if(!p_selected_my_keys.length)
								{
									document.getElementById("p_id_send_container").innerHTML =
									"<table style = \"width: 100%; height: 100%; font-size: 11pt; color: #AFAFAF; text-align: center;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
										<td>\
											<img height = \"40px\" alt = \"Above\" src = \"images/above.png\"/>\
											\
											<div style = \"margin-top: 15px;\">\
												Select the keys you want to exchange\
											</div>\
										</td>\
									</table>";
								}
							}

							$("#p_id_selected_my_keys_value").stop().prop("number", old_selected_value).animateNumber(
							{
								number: p_selected_my_keys_value,
								numberStep: function(now, tween)
								{
									var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
									if(!decimal_1 && !decimal_2)
									{
										$(tween.elem).text(value.substr(0, size_of_value - 3));
									}
									else
									{
										$(tween.elem).text(value);
									}
								}
							});
						}
					}
					else
					{
						var key = p_selected_exchange.findIndex(p_selected_exchange => p_selected_exchange.name == name);
						if(key != -1)
						{
							var element_identifier_part = platform + "_" + app_id + "_" + name + "_quantity", old_selected_value = p_selected_exchange_value;
							p_selected_exchange_value -= p_selected_exchange[key]['price'];

							if(p_selected_exchange_value < 0.00)
							{
								p_selected_exchange_value = 0.00;
							}

							if(p_selected_exchange_platform == platform && p_selected_exchange_app_id == app_id)
							{
								if(p_selected_exchange[key]['selected_quantity'] == p_selected_exchange[key]['max_quantity'])
								{
									html =
									"\
									<div id = \"p_id_exchange_" + platform + "_" + app_id + "_" + name + "\" style = \"margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;\" onclick = 'P_AddKeyToTrade(" + my_keys_state + ", \"" + name + "\", " + JSON.stringify(opens) + ", " + price_1 + ", \"" + price_2 + "\", " + quantity_1 + ", \"" + quantity_2 + "\", \"" + key_background + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\");' oncontextmenu = 'P_ShowKeyInfo(\"" + name + "\", " + JSON.stringify(opens) + ", \"" + price_2 + "\", \"" + image + "\", " + image_background_state + ", " + platform + ", " + app_id + ", \"" + app_name + "\", \"" + extra + "\"); return false;'>\
										<div style = \"width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;\" title = \"" + name + " - " + price_2 + "\">\
											<img style = \"width: 70px; height: 70px; position: absolute;\" alt = \"Key Background\" src = \"images/" + key_background + "\"/>\
											\
											<img class = \"animation_group_11_1\" style = \"width: 70px; height: 70px;" + ((image_background_state) ? (" border-radius: 50%;") : ("")) + " z-index: 1; position: absolute;\" alt = \"" + name + "\" src = \"images/keys/" + image + "\"/>\
											\
											<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
												x<div id = \"p_id_exchange_" + platform + "_" + app_id + "_" + name + "_quantity\" style = \"display: inline-block;\">1</div>\
											</div>\
											\
											<div style = \"width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;\">\
												" + price_2 + "\
											</div>\
										</div>\
									</div>";

									$(document.getElementById("p_id_exchange_container")).prepend(html);
								}
							}

							if(p_selected_exchange[key]['selected_quantity'] > 1)
							{
								p_selected_exchange[key]['selected_quantity'] -= 1;

								if(p_selected_exchange_platform == platform && p_selected_exchange_app_id == app_id)
								{
									document.getElementById("p_id_exchange_" + element_identifier_part).innerHTML = (p_selected_exchange[key]['max_quantity'] - p_selected_exchange[key]['selected_quantity']).toLocaleString("en", {minimumFractionDigits: 0});
								}

								document.getElementById("p_id_selected_exchange_" + element_identifier_part).innerHTML = p_selected_exchange[key]['selected_quantity'].toLocaleString("en", {minimumFractionDigits: 0});
							}
							else
							{
								var max_quantity = p_selected_exchange[key]['max_quantity'];
								p_selected_exchange.splice(key, 1);

								if(p_selected_exchange_platform == platform && p_selected_exchange_app_id == app_id)
								{
									document.getElementById("p_id_exchange_" + element_identifier_part).innerHTML = max_quantity.toLocaleString("en", {minimumFractionDigits: 0});
								}

								$(document.getElementById("p_id_selected_exchange_" + platform + "_" + app_id + "_" + name)).remove();

								if(!p_selected_exchange.length)
								{
									document.getElementById("p_id_receive_container").innerHTML =
									"<table style = \"width: 100%; height: 100%; font-size: 11pt; color: #AFAFAF; text-align: center;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
										<td>\
											<img height = \"40px\" alt = \"Above\" src = \"images/above.png\"/>\
											\
											<div style = \"margin-top: 15px;\">\
												Select the keys you want to exchange\
											</div>\
										</td>\
									</table>";
								}
							}

							$("#p_id_selected_exchange_value").stop().prop("number", old_selected_value).animateNumber(
							{
								number: p_selected_exchange_value,
								numberStep: function(now, tween)
								{
									var value = now.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"), size_of_value = value.length, decimal_1 = parseInt(value.substr(size_of_value - 2, size_of_value - 3)), decimal_2 = parseInt(value.substr(size_of_value - 1));
									if(!decimal_1 && !decimal_2)
									{
										$(tween.elem).text(value.substr(0, size_of_value - 3));
									}
									else
									{
										$(tween.elem).text(value);
									}
								}
							});
						}
					}

					OnAnimationGroupStatusChange(11, g_animation_status[10]);

					p_perform_action = 1;
				}
			}

			function P_ProcessKeyExchange()
			{
				if(p_perform_action)
				{
					p_perform_action = 0;

					// -----

					g_element_id_sound_action.play();

					if(!g_modal_state)
					{
						g_shown_modal_id = -1;
						g_modal_state = 1;

						// -----

						document.getElementById("id_modal_content_main").innerHTML =
						"<table style = \"font-family: arial; font-size: 14pt; color: #EEEEEE; display: inline-block;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
							<td>\
								<img height = \"100px\" alt = \"Loading\" src = \"images/loading.svg\"/>\
							</td>\
							\
							<td style = \"padding-left: 10px;\">\
								Validating selection...\
							</td>\
						</table>";

						ShowModal(1, 1);
					}

					$.ajax(
					{
						type: "POST",
						url: "includes/ajax_call_handler.php",
						data: {include_path: "validate_key_exchange.php", my_keys: JSON.stringify(p_selected_my_keys), exchange: JSON.stringify(p_selected_exchange)},

						success: function(html)
						{
							var response = html.split("|separator|");
							if(response[0] == "Alert")
							{
								g_shown_modal_id = 0;

								// -----

								document.getElementById("id_modal_content_header").innerHTML =
								"<table style = \"font-size: 20pt; font-weight: bold; color: #EA4141; display: inline-table;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
									<td>\
										<img height = \"28px\" alt = \"Alert\" src = \"images/alert.png\"/>\
									</td>\
									\
									<td style = \"padding-left: 7px;\">\
										" + response[0] + "\
									</td>\
								</table>";

								document.getElementById("id_modal_content_main").innerHTML = response[1];

								document.getElementById("id_modal_content_footer").innerHTML =
								"<div class = \"modal_button\" onclick = \"HideStandardModal();\">\
									OKAY\
								</div>";

								ShowModal();

								p_perform_action = 1;
							}
							else
							{
								document.getElementById("id_modal_content_main").innerHTML =
								"<table style = \"font-family: arial; font-size: 14pt; color: #EEEEEE; display: inline-block;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
									<td>\
										<img height = \"100px\" alt = \"Loading\" src = \"images/loading.svg\"/>\
									</td>\
									\
									<td style = \"padding-left: 10px;\">\
										Requesting your key(s)...\
									</td>\
								</table>";

								var delay = setInterval(function()
								{
									if(p_call_process_key_exchange)
									{
										p_call_process_key_exchange = 0;

										// -----

										$.ajax(
										{
											type: "POST",
											url: "includes/ajax_call_handler.php",
											data: {include_path: "process_key_exchange.php"},

											success: function(html)
											{
												if(html)
												{
													response = html.split("|separator|");

													switch(response[0])
													{
														case "Alert": case "ExpressTrade": case "Steam":
														{
															g_shown_modal_id = 0;

															clearInterval(delay);

															// -----

															document.getElementById("id_modal_content_header").innerHTML =
															"<table style = \"font-size: 20pt; font-weight: bold; color: #EA4141; display: inline-table;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
																<td>\
																	<img height = \"28px\" alt = \"Alert\" src = \"images/alert.png\"/>\
																</td>\
																\
																<td style = \"padding-left: 7px;\">\
																	" + response[0] + "\
																</td>\
															</table>";

															document.getElementById("id_modal_content_main").innerHTML = response[1];

															document.getElementById("id_modal_content_footer").innerHTML =
															"<div class = \"modal_button\" onclick = \"HideStandardModal();\">\
																OKAY\
															</div>";

															ShowModal();

															p_perform_action = 1;
															break;
														}
														case "Exchange Complete":
														{
															clearInterval(delay);

															// -----

															document.getElementById("id_modal_content_header").innerHTML =
															"<table style = \"font-size: 20pt; font-weight: bold; color: #95EA41; display: inline-table;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
																<td>\
																	<img height = \"28px\" alt = \"Info\" src = \"images/info.png\"/>\
																</td>\
																\
																<td style = \"padding-left: 7px;\">\
																	" + response[0] + "\
																</td>\
															</table>";

															document.getElementById("id_modal_content_main").innerHTML = response[1];

															document.getElementById("id_modal_content_footer").innerHTML =
															"<a href = \"?exchange\">\
																<div class = \"modal_button\">\
																	DISMISS\
																</div>\
															</a>";

															ShowModal();
															break;
														}
														default:
														{
															var html_2 =
															"<table style = \"font-family: arial; font-size: 14pt; color: #EEEEEE; display: inline-block;\" border = \"0\" cellspacing = \"0\" cellpadding = \"0\">\
																<td>\
																	<img height = \"100px\" alt = \"Loading\" src = \"images/loading.svg\"/>\
																</td>\
																\
																<td style = \"padding-left: 10px;\">\
																	" + response[1] + "\
																	\
																	";

															if(typeof response[2] !== "undefined")
															{
																html_2 +=
																"<div style = \"padding-top: 1px; font-size: 11pt; color: #AFAFAF;\">\
																	" + response[2] + "\
																</div>";
															}

															document.getElementById("id_modal_content_main").innerHTML = html_2 +
															"	</td>\
															</table>";
															break;
														}
													}
												}

												p_call_process_key_exchange = 1;
											}
										});
									}
								}, 1000);
							}
						}
					});
				}
			}
		</script>
	</head>
</html>

<?php
	include_once __DIR__ . "/../connection.php";

	?>

	<div style = "height: 100vh; min-height: 700px; margin-top: -213px; position: relative;">
		<div id = "p_id_exchange_button" class = "p_exchange_button" onclick = "P_ProcessKeyExchange();">
			<table style = "width: inherit; height: inherit; font-family: arial; font-size: 8pt; color: #FFC62E; text-shadow: #000000 0px 0px 1px; -webkit-font-smoothing: antialiased; text-align: center; display: inline-table;" border = "0" cellspacing = "0" cellpadding = "0">
				<tr>
					<td>
						<div class = "animate_move_right_and_left">
							<img style = "height: 32px; pointer-events: none;" alt = "Exchange" src = "images/arrow_right.png"/>

							<div style = "width: 100%; margin-top: -22px; text-align: center; position: absolute;">
								$<div id = "p_id_selected_my_keys_value" style = "display: inline-block;">0</div>
							</div>
						</div>

						<div class = "animate_move_left_and_right" style = "margin-top: -6px;">
							<img style = "height: 32px; pointer-events: none;" alt = "Exchange" src = "images/arrow_left.png"/>

							<div style = "width: 100%; margin-top: -22px; text-align: center; position: absolute;">
								$<div id = "p_id_selected_exchange_value" style = "display: inline-block;">0</div>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<table style = "width: 100%; height: inherit; min-height: inherit; padding-top: 213px; font-family: arial; font-size: 10pt; color: #AFAFAF;" border = "0" cellspacing = "0" cellpadding = "0">
			<tr>
				<td style = "width: 50%; max-width: 0px; height: 50%; border: 1px solid #181818; border-style: none solid solid none; vertical-align: top;" onmouseover = "P_MoveExchangeButton(1);" onmouseout = "P_MoveExchangeButton(1, 1);">
					<div style = "height: 100%; border-top-left-radius: 5px; position: relative; overflow: hidden;">
						<div id = "p_id_exchange_quadrant_label_1" style = "padding: 10px 15px 10px 15px; background: #FFC62E; border: 1px solid #F59C00; border-style: solid solid solid none; border-top-right-radius: 3px; border-bottom-right-radius: 3px; font-size: 11pt; font-weight: bold; color: #F59C00; text-shadow: #000000 0px 0px 1px; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; bottom: 25px; z-index: 1; position: absolute; display: inline-block;">
							MY KEYS
						</div>

						<div style = "height: 47px; padding: 0px 10px 0px 10px; background: rgba(0, 0, 0, 0.21); overflow-x: auto;">
							<table style = "width: 100%; height: 100%; text-align: center; white-space: nowrap;" border = "0" cellspacing = "0" cellpadding = "0">
								<td>
									<div id = "p_id_my_keys_1_1" style = "padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(1, 1, 1);" title = "VGO">
										<img style = "height: 29px; border-radius: 3px;" alt = "VGO" src = "images/icons/vgo.png"/>
									</div><div id = "p_id_my_keys_2_730" style = "margin-left: 6px; padding: 1px; background: #F59C00; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(1, 2, 730);" title = "Counter-Strike: Global Offensive">
										<img style = "height: 29px; border-radius: 3px;" alt = "CS" src = "images/icons/cs.png"/>
									</div><div id = "p_id_my_keys_2_440" style = "margin-left: 6px; padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(1, 2, 440);" title = "Team Fortress 2">
										<img style = "height: 29px; border-radius: 3px;" alt = "TF2" src = "images/icons/tf2.webp"/>
									</div><div id = "p_id_my_keys_2_232090" style = "margin-left: 6px; padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(1, 2, 232090);" title = "Killing Floor 2">
										<img style = "height: 29px; border-radius: 3px;" alt = "KF2" src = "images/icons/kf2.png"/>
									</div>
								</td>
							</table>
						</div>

						<div style = "height: 100%; margin-top: -47px; background: rgba(0, 0, 0, 0.15);">
							<table style = "width: 100%; height: 100%; padding-top: 47px;" border = "0" cellspacing = "0" cellpadding = "0">
								<td>
									<div style = "height: 100%; padding: 10px 7px 10px 7px; text-align: center; overflow-y: auto;">
										<div id = "p_id_my_keys_container" style = "margin: -5px 0px 0px -5px;">
											<table style = "width: 100%; height: 100%; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
												<td>
													<img height = "100px" alt = "Loading" src = "images/loading.svg"/>
												</td>
											</table>
										</div>
									</div>
								</td>
							</table>
						</div>
					</div>
				</td>

				<td style = "width: 50%; max-width: 0px; height: 50%; border: 1px solid #181818; border-style: none none solid none; vertical-align: top;" onmouseover = "P_MoveExchangeButton(2);" onmouseout = "P_MoveExchangeButton(2, 1);">
					<div style = "height: 100%; border-top-right-radius: 5px; position: relative; overflow: hidden;">
						<div id = "p_id_exchange_quadrant_label_2" style = "padding: 12px 15px 12px 15px; background: #FFC62E; border: 1px solid #F59C00; border-style: solid none solid solid; border-top-left-radius: 3px; border-bottom-left-radius: 3px; font-size: 11pt; font-weight: bold; color: #F59C00; text-shadow: #000000 0px 0px 1px; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; bottom: 25px; right: 0; z-index: 1; position: absolute; display: inline-block;">
							EXCHANGE
						</div>

						<div style = "height: 47px; padding: 0px 10px 0px 10px; background: rgba(0, 0, 0, 0.21); overflow-x: auto;">
							<table style = "width: 100%; height: 100%; text-align: center; white-space: nowrap;" border = "0" cellspacing = "0" cellpadding = "0">
								<td>
									<div id = "p_id_exchange_1_1" style = "padding: 1px; background: #F59C00; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(0, 1, 1);" title = "VGO">
										<img style = "height: 29px; border-radius: 3px;" alt = "VGO" src = "images/icons/vgo.png"/>
									</div><div id = "p_id_exchange_2_730" style = "margin-left: 6px; padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(0, 2, 730);" title = "Counter-Strike: Global Offensive">
										<img style = "height: 29px; border-radius: 3px;" alt = "CS" src = "images/icons/cs.png"/>
									</div><div id = "p_id_exchange_2_440" style = "margin-left: 6px; padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(0, 2, 440);" title = "Team Fortress 2">
										<img style = "height: 29px; border-radius: 3px;" alt = "TF2" src = "images/icons/tf2.webp"/>
									</div><div id = "p_id_exchange_2_232090" style = "margin-left: 6px; padding: 1px; border-radius: 3px; cursor: pointer; display: inline-block;" onclick = "P_ListUserKeys(0, 2, 232090);" title = "Killing Floor 2">
										<img style = "height: 29px; border-radius: 3px;" alt = "KF2" src = "images/icons/kf2.png"/>
									</div>
								</td>
							</table>
						</div>

						<div style = "height: 100%; margin-top: -47px; background: rgba(0, 0, 0, 0.15);">
							<table style = "width: 100%; height: 100%; padding-top: 47px;" border = "0" cellspacing = "0" cellpadding = "0">
								<td>
									<div style = "height: 100%; padding: 10px 7px 10px 7px; text-align: center; overflow-y: auto;">
										<div id = "p_id_exchange_container" style = "margin: -5px 0px 0px -5px;">
											<table style = "width: 100%; height: 100%; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
												<td>
													<img height = "100px" alt = "Loading" src = "images/loading.svg"/>
												</td>
											</table>
										</div>
									</div>
								</td>
							</table>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<td style = "width: 50%; height: 50%; background: rgba(0, 0, 0, 0.15); border: 1px solid #181818; border-style: none solid none none; border-bottom-left-radius: 5px; vertical-align: top;" onmouseover = "P_MoveExchangeButton(3);" onmouseout = "P_MoveExchangeButton(3, 1);">
					<div style = "height: 100%; position: relative; overflow: hidden;">
						<div id = "p_id_exchange_quadrant_label_3" style = "padding: 10px 15px 10px 15px; background: #FFC62E; border: 1px solid #F59C00; border-style: solid solid solid none; border-top-right-radius: 3px; border-bottom-right-radius: 3px; font-size: 11pt; font-weight: bold; color: #F59C00; text-shadow: #000000 0px 0px 1px; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; bottom: 25px; z-index: 1; position: absolute; display: inline-block;">
							SEND
						</div>

						<table style = "width: 100%; height: 100%;" border = "0" cellspacing = "0" cellpadding = "0">
							<td>
								<div style = "height: 100%; padding: 10px 7px 10px 7px; text-align: center; overflow-y: auto;">
									<div id = "p_id_send_container" style = "margin: -5px 0px 0px -5px;">
										<table style = "width: 100%; height: 100%; font-size: 11pt; color: #AFAFAF; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
											<td>
												<img height = "40px" alt = "Above" src = "images/above.png"/>

												<div style = "margin-top: 15px;">
													Select the keys you want to exchange
												</div>
											</td>
										</table>
									</div>
								</div>
							</td>
						</table>
					</div>
				</td>

				<td style = "width: 50%; height: 50%; background: rgba(0, 0, 0, 0.15); border-bottom-right-radius: 5px; vertical-align: top;" onmouseover = "P_MoveExchangeButton(4);" onmouseout = "P_MoveExchangeButton(4, 1);">
					<div style = "height: 100%; position: relative; overflow: hidden;">
						<div id = "p_id_exchange_quadrant_label_4" style = "padding: 12px 15px 12px 15px; background: #FFC62E; border: 1px solid #F59C00; border-style: solid none solid solid; border-top-left-radius: 3px; border-bottom-left-radius: 3px; font-size: 11pt; font-weight: bold; color: #F59C00; text-shadow: #000000 0px 0px 1px; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; bottom: 25px; right: 0; z-index: 1; position: absolute; display: inline-block;">
							RECEIVE
						</div>

						<table style = "width: 100%; height: 100%;" border = "0" cellspacing = "0" cellpadding = "0">
							<td>
								<div style = "height: 100%; padding: 10px 7px 10px 7px; text-align: center; overflow-y: auto;">
									<div id = "p_id_receive_container" style = "margin: -5px 0px 0px -5px;">
										<table style = "width: 100%; height: 100%; font-size: 11pt; color: #AFAFAF; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
											<td>
												<img height = "40px" alt = "Above" src = "images/above.png"/>

												<div style = "margin-top: 15px;">
													Select the keys you want to exchange
												</div>
											</td>
										</table>
									</div>
								</div>
							</td>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<div style = "margin-top: 20px; text-align: center;">
		<table style = "font-family: arial; font-size: 11pt; color: #AFAFAF; display: inline-block;" border = "0" cellspacing = "0" cellpadding = "0">
			<td>
				<img id = "id_toggle_animation_group_11" style = "height: 15px; cursor: pointer;" alt = "Toggle Animation(s)" src = "images/animation.png" onclick = "ToggleAnimationGroup(11);" title = "Toggle Animation(s)"/>
			</td>

			<td style = "padding-left: 10px;">
				<font color = "#EEEEEE">Right click</font> for key info
			</td>
		</table>

		<script type = "text/javascript">
			if(g_animation_status[10] == 0)
			{
				document.getElementById("id_toggle_animation_group_11").src = "images/animation_off.png";

				if(g_mobile)
				{
					document.getElementById("id_toggle_animation_group_11").style.cursor = "not-allowed";
				}
			}
		</script>
	</div>

	<script type = "text/javascript">
		P_ListUserKeys(1, 2, 730, 1);
		P_ListUserKeys(0, 1, 1, 1);
	</script>