<?php
	session_start();

	if(isset($_SESSION['steam_id']))
	{
		include_once "connection.php";
		include_once "otphp/lib/otphp.php";
		include_once "get_api_key.php";
		include_once "get_opskins_api_key.php";
		include_once "get_opskins_tfa_secret.php";
		include_once "get_key_exchange_bot_steam_id.php";
		include_once "execute_api_call.php";
		include_once "function_is_valid_domain.php";

		$query_1 = $connection -> prepare("SELECT `id`, `send_expresstrade`, `receive_expresstrade`, `send_steam`, `receive_steam`, `remaining_balance`, `send_steam_items`, `receive_steam_items`, `send_expresstrade_offer_id`, `receive_expresstrade_offer_id`, `send_steam_offer_id`, `receive_steam_offer_id`, `send_steam_informed`, `next_step_after_send_steam_informed`, `send_expresstrade_accepted`, `send_steam_accepted`, `cancel` FROM `key_exchanges` WHERE `issuer_id` = :issuer_id ORDER BY `id` DESC LIMIT 1");
		$query_1 -> bindParam(":issuer_id", $_SESSION['steam_id']);
		$query_1 -> execute();

		if($query_1_result = $query_1 -> fetch())
		{
			if($query_1_result['cancel'])
			{
				$query_2 = $connection -> prepare("UPDATE `key_exchanges` SET `send_expresstrade_offer_id` = -1, `send_steam_offer_id` = -1, `receive_expresstrade_offer_id` = -1, `receive_steam_offer_id` = -1, `send_expresstrade_accepted` = 1, `send_steam_accepted` = 1 WHERE `id` = :id LIMIT 1");
				$query_2 -> bindParam(":send_expresstrade_offer_id", $trade_offer_id);
				$query_2 -> bindParam(":id", $query_1_result['id']);
				$query_2 -> execute();

				echo "Alert|separator|The exchange has been canceled.";
			}
			else if($query_1_result['send_expresstrade'] != NULL && !$query_1_result['send_expresstrade_accepted'])
			{
				if($query_1_result['send_expresstrade_offer_id'] == NULL)
				{
					$response_1 = ExecuteAPICall("GET", "ITrade/GetUserInventoryFromSteamId/v1", array("key=" . GetAPIKey() . "&steam_id=" . $_SESSION['steam_id'] . "&app_id=1&page=1&per_page=500&search=Skeleton%20Key")); // VGO

					if($response_1 != NULL)
					{
						$response_1_data = json_decode($response_1, true);

						if(isset($response_1_data[0]['response']['items']))
						{
							$send_expresstrade = explode(",", $query_1_result['send_expresstrade']);

							$extended_key_info = array();

							$query_2 = $connection -> prepare("SELECT `name` FROM `extended_key_info`");
							$query_2 -> execute();

							while($query_2_result = $query_2 -> fetch())
							{
								$extended_key_info[$query_2_result['name']] = 1;
							}

							$items = array();

							for($i = 0, $j = sizeof($send_expresstrade); $i < $j; $i ++)
							{
								$send_expresstrade_data = explode("|separator|", $send_expresstrade[$i]);
								$name = (string) $send_expresstrade_data[0];

								if(isset($extended_key_info[$name]))
								{
									$quantity = (int) $send_expresstrade_data[1];

									for($k = 0, $l = $quantity; $k < $l; $k ++)
									{
										if(isset($response_1_data[0]['response']['items'][$k]))
										{
											$key = $response_1_data[0]['response']['items'][$k];

											if($key['name'] == $name && $key['tradable'])
											{
												array_push($items, $key['id']);
											}
											else
											{
												$l += 1;
											}
										}
										else
										{
											echo "ExpressTrade|separator|Unable to request your key(s).";
											return;
										}
									}
								}
							}

							if(file_get_contents(__DIR__ . "/../documents/domain.txt", FILE_USE_INCLUDE_PATH) == "localhost")
							{
								$twofactor_code = "841278";
							}
							else
							{
								$totp = new \OTPHP\TOTP(GetOPSkinsTFASecret());
								$twofactor_code = $totp -> now();
							}

							$response_2 = ExecuteAPICall("POST", "ITrade/SendOfferToSteamId/v1", array(
								"key" => GetOPSkinsAPIKey(),
								"twofactor_code" => $twofactor_code,
								"steam_id" => $_SESSION['steam_id'],
								"items" => implode(",", $items),
								"message" => "Request from unlockvgo.com/?exchange"));

							if($response_2 != NULL)
							{
								$response_2_data = json_decode($response_2, true);

								if(isset($response_2_data[0]['response']['offer']))
								{
									$trade_offer_id = (string) $response_2_data[0]['response']['offer']['id'];

									$query_3 = $connection -> prepare("UPDATE `key_exchanges` SET `send_expresstrade_offer_id` = :send_expresstrade_offer_id WHERE `id` = :id LIMIT 1");
									$query_3 -> bindParam(":send_expresstrade_offer_id", $trade_offer_id);
									$query_3 -> bindParam(":id", $query_1_result['id']);
									$query_3 -> execute();

									echo "Next Step|separator|Request for your key(s) sent|separator|Accept the <a class = \"p_link\" href = \"https://trade.opskins.com/trade-offers/" . $trade_offer_id . "\" target = \"_blank\">trade offer</a> on ExpressTrade";
								}
								else
								{
									echo "ExpressTrade|separator|" . $response_2_data[0]['message'];
								}
							}
						}
					}
				}
				else
				{
					$response = ExecuteAPICall("GET", "ITrade/GetOffer/v1", array("key=" . GetOPSkinsAPIKey() . "&offer_id=" . $query_1_result['send_expresstrade_offer_id']));

					if($response != NULL)
					{
						$response_data = json_decode($response, true);

						if(isset($response_data[0]['response']['offer']['state']))
						{
							if($response_data[0]['response']['offer']['state'] == 3) // ACCEPTED
							{
								$query_2 = $connection -> prepare("UPDATE `key_exchanges` SET `send_expresstrade_accepted` = 1 WHERE `id` = :id LIMIT 1");
								$query_2 -> bindParam(":id", $query_1_result['id']);
								$query_2 -> execute();

								if($query_1_result['send_steam'] != NULL)
								{
									echo "Next Step|separator|Requesting your key(s)...";
								}
								else
								{
									echo "Next Step|separator|Sending your new key(s)...";
								}
							}
						}
					}
				}
			}
			else if($query_1_result['send_steam'] != NULL && !$query_1_result['send_steam_accepted'])
			{
				if($query_1_result['send_steam_items'] == NULL)
				{
					$response_1 = ExecuteAPICall("GET", "inventory/" . $_SESSION['steam_id'] . "/440/2", NULL, "https://steamcommunity.com/"); // TF2
					$response_2 = ExecuteAPICall("GET", "inventory/" . $_SESSION['steam_id'] . "/730/2", NULL, "https://steamcommunity.com/"); // CS:GO
					$response_3 = ExecuteAPICall("GET", "inventory/" . $_SESSION['steam_id'] . "/232090/2", NULL, "https://steamcommunity.com/"); // KF2

					if($response_1 != NULL && $response_2 != NULL && $response_3 != NULL)
					{
						$response_1_data = json_decode($response_1, true);
						$response_2_data = json_decode($response_2, true);
						$response_3_data = json_decode($response_3, true);

						if(isset($response_1_data[0]['descriptions']) || isset($response_2_data[0]['descriptions']) || isset($response_3_data[0]['descriptions']))
						{
							$send_steam = explode(",", $query_1_result['send_steam']);

							$extended_key_info = array();

							$query_2 = $connection -> prepare("SELECT `name` FROM `extended_key_info`");
							$query_2 -> execute();

							while($query_2_result = $query_2 -> fetch())
							{
								$extended_key_info[$query_2_result['name']] = 1;
							}

							$assets_1 = array();
							$assets_2 = array();
							$assets_3 = array();
							$items = NULL;

							if(isset($response_1_data[0]['assets']))
							{
								foreach($response_1_data[0]['assets'] as $asset)
								{
									if(!isset($assets_1[$asset['classid']]))
									{
										$assets_1[$asset['classid']] = array();
									}

									array_push($assets_1[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							if(isset($response_2_data[0]['assets']))
							{
								foreach($response_2_data[0]['assets'] as $asset)
								{
									if(!isset($assets_2[$asset['classid']]))
									{
										$assets_2[$asset['classid']] = array();
									}

									array_push($assets_2[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							if(isset($response_3_data[0]['assets']))
							{
								foreach($response_3_data[0]['assets'] as $asset)
								{
									if(!isset($assets_3[$asset['classid']]))
									{
										$assets_3[$asset['classid']] = array();
									}

									array_push($assets_3[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							for($i = 0, $j = sizeof($send_steam); $i < $j; $i ++)
							{
								$send_steam_data = explode("|separator|", $send_steam[$i]);
								$name = (string) $send_steam_data[0];

								if(isset($extended_key_info[$name]))
								{
									$quantity = (int) $send_steam_data[1];
									$app_id = (int) $send_steam_data[2];

									switch($app_id)
									{
										case 440: // TF2
										{
											for($k = 0, $l = sizeof($response_1_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_1_data[0]['descriptions'][$k]))
												{
													$key = $response_1_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_1[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_1[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_1[$key['classid']][$m]['context_id'] . "|separator|" . $assets_1[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_1[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_1[$key['classid']][$m]['context_id'] . "|separator|" . $assets_1[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
										case 730: // CS:GO
										{
											for($k = 0, $l = sizeof($response_2_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_2_data[0]['descriptions'][$k]))
												{
													$key = $response_2_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_2[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_2[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_2[$key['classid']][$m]['context_id'] . "|separator|" . $assets_2[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_2[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_2[$key['classid']][$m]['context_id'] . "|separator|" . $assets_2[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
										case 232090: // KF2
										{
											for($k = 0, $l = sizeof($response_3_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_3_data[0]['descriptions'][$k]))
												{
													$key = $response_3_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_3[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_3[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_3[$key['classid']][$m]['context_id'] . "|separator|" . $assets_3[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_3[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_3[$key['classid']][$m]['context_id'] . "|separator|" . $assets_3[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
									}
								}
							}

							if($items != NULL)
							{
								$query_3 = $connection -> prepare("UPDATE `key_exchanges` SET `send_steam_items` = :send_steam_items WHERE `id` = :id LIMIT 1");
								$query_3 -> bindParam(":send_steam_items", $items);
								$query_3 -> bindParam(":id", $query_1_result['id']);
								$query_3 -> execute();

								echo "Next Step|separator|Waiting for bot to send request...";
							}
						}
					}
				}
				else if($query_1_result['send_steam_offer_id'] != NULL && !$query_1_result['send_steam_informed'])
				{
					$query_2 = $connection -> prepare("UPDATE `key_exchanges` SET `send_steam_informed` = 1 WHERE `id` = :id LIMIT 1");
					$query_2 -> bindParam(":id", $query_1_result['id']);
					$query_2 -> execute();

					echo "Next Step|separator|Request for your key(s) sent|separator|Accept the <a class = \"p_link\" href = \"https://steamcommunity.com/tradeoffer/" . $query_1_result['send_steam_offer_id'] . "\" target = \"_blank\">trade offer</a> on Steam";
				}
			}
			else if($query_1_result['receive_expresstrade'] != NULL && $query_1_result['receive_expresstrade_offer_id'] == NULL)
			{
				if($query_1_result['send_steam'] != NULL && !$query_1_result['next_step_after_send_steam_informed'])
				{
					$query_2 = $connection -> prepare("UPDATE `key_exchanges` SET `next_step_after_send_steam_informed` = 1 WHERE `id` = :id LIMIT 1");
					$query_2 -> bindParam(":id", $query_1_result['id']);
					$query_2 -> execute();

					echo "Next Step|separator|Sending your new key(s)...";
					return;
				}

				$response_1 = ExecuteAPICall("GET", "ITrade/GetUserInventoryFromSteamId/v1", array("key=" . GetAPIKey() . "&steam_id=" . GetKeyExchangeBotSteamID() . "&app_id=1&page=1&per_page=500&search=Skeleton%20Key")); // VGO

				if($response_1 != NULL)
				{
					$response_1_data = json_decode($response_1, true);

					if(isset($response_1_data[0]['response']['items']))
					{
						$receive_expresstrade = explode(",", $query_1_result['receive_expresstrade']);

						$extended_key_info = array();

						$query_3 = $connection -> prepare("SELECT `name` FROM `extended_key_info`");
						$query_3 -> execute();

						while($query_3_result = $query_3 -> fetch())
						{
							$extended_key_info[$query_3_result['name']] = 1;
						}

						$items = array();

						for($i = 0, $j = sizeof($receive_expresstrade); $i < $j; $i ++)
						{
							$receive_expresstrade_data = explode("|separator|", $receive_expresstrade[$i]);
							$name = (string) $receive_expresstrade_data[0];

							if(isset($extended_key_info[$name]))
							{
								$quantity = (int) $receive_expresstrade_data[1];

								for($k = 0, $l = $quantity; $k < $l; $k ++)
								{
									if(isset($response_1_data[0]['response']['items'][$k]))
									{
										$key = $response_1_data[0]['response']['items'][$k];

										if($key['name'] == $name && $key['tradable'])
										{
											array_push($items, $key['id']);
										}
										else
										{
											$l += 1;
										}
									}
									else
									{
										echo "ExpressTrade|separator|Unable to send your new key(s).";
										return;
									}
								}
							}
						}

						if(file_get_contents(__DIR__ . "/../documents/domain.txt", FILE_USE_INCLUDE_PATH) == "localhost")
						{
							$twofactor_code = "841278";
						}
						else
						{
							$totp = new \OTPHP\TOTP(GetOPSkinsTFASecret());
							$twofactor_code = $totp -> now();
						}

						$response_2 = ExecuteAPICall("POST", "ITrade/SendOfferToSteamId/v1", array(
							"key" => GetOPSkinsAPIKey(),
							"twofactor_code" => $twofactor_code,
							"steam_id" => $_SESSION['steam_id'],
							"items" => implode(",", $items),
							"message" => "Your new key(s) from unlockvgo.com/?exchange"));

						if($response_2 != NULL)
						{
							$response_2_data = json_decode($response_2, true);

							if(isset($response_2_data[0]['response']['offer']))
							{
								$trade_offer_id = (string) $response_2_data[0]['response']['offer']['id'];

								$query_4 = $connection -> prepare("UPDATE `key_exchanges` SET `receive_expresstrade_offer_id` = :receive_expresstrade_offer_id WHERE `id` = :id LIMIT 1");
								$query_4 -> bindParam(":receive_expresstrade_offer_id", $trade_offer_id);
								$query_4 -> bindParam(":id", $query_1_result['id']);
								$query_4 -> execute();

								if($query_1_result['receive_steam'] != NULL)
								{
									echo "Next Step|separator|Sending your new key(s)...";
								}
								else
								{
									$query_5 = $connection -> prepare("UPDATE `users` SET `free_balance` = (`free_balance` + :free_balance) WHERE `steam_id` = :steam_id LIMIT 1");
									$query_5 -> bindParam(":free_balance", $query_1_result['remaining_balance']);
									$query_5 -> bindParam(":steam_id", $_SESSION['steam_id']);
									$query_5 -> execute();

									echo "Exchange Complete|separator|Accept the <a class = \"p_link\" href = \"https://trade.opskins.com/trade-offers/" . $trade_offer_id . "\" target = \"_blank\">trade offer</a> on ExpressTrade.<br><br>Your remaining balance is stored, and<br>you will soon be able to cash it out or<br>complete offers to match for key(s).";
								}
							}
							else
							{
								echo "ExpressTrade|separator|" . $response_2_data[0]['message'];
							}
						}
					}
				}
			}
			else if($query_1_result['receive_steam'] != NULL)
			{
				if($query_1_result['receive_expresstrade'] == NULL && !$query_1_result['next_step_after_send_steam_informed'])
				{
					$query_2 = $connection -> prepare("UPDATE `key_exchanges` SET `next_step_after_send_steam_informed` = 1 WHERE `id` = :id LIMIT 1");
					$query_2 -> bindParam(":id", $query_1_result['id']);
					$query_2 -> execute();

					echo "Next Step|separator|Sending your new key(s)...";
					return;
				}

				if($query_1_result['receive_steam_items'] == NULL)
				{
					$response_1 = ExecuteAPICall("GET", "inventory/" . GetKeyExchangeBotSteamID() . "/440/2", NULL, "https://steamcommunity.com/"); // TF2
					$response_2 = ExecuteAPICall("GET", "inventory/" . GetKeyExchangeBotSteamID() . "/730/2", NULL, "https://steamcommunity.com/"); // CS:GO
					$response_3 = ExecuteAPICall("GET", "inventory/" . GetKeyExchangeBotSteamID() . "/232090/2", NULL, "https://steamcommunity.com/"); // KF2

					if($response_1 != NULL && $response_2 != NULL && $response_3 != NULL)
					{
						$response_1_data = json_decode($response_1, true);
						$response_2_data = json_decode($response_2, true);
						$response_3_data = json_decode($response_3, true);

						if(isset($response_1_data[0]['descriptions']) || isset($response_2_data[0]['descriptions']) || isset($response_3_data[0]['descriptions']))
						{
							$receive_steam = explode(",", $query_1_result['receive_steam']);

							$extended_key_info = array();

							$query_3 = $connection -> prepare("SELECT `name` FROM `extended_key_info`");
							$query_3 -> execute();

							while($query_3_result = $query_3 -> fetch())
							{
								$extended_key_info[$query_3_result['name']] = 1;
							}

							$assets_1 = array();
							$assets_2 = array();
							$assets_3 = array();
							$items = NULL;

							if(isset($response_1_data[0]['assets']))
							{
								foreach($response_1_data[0]['assets'] as $asset)
								{
									if(!isset($assets_1[$asset['classid']]))
									{
										$assets_1[$asset['classid']] = array();
									}

									array_push($assets_1[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							if(isset($response_2_data[0]['assets']))
							{
								foreach($response_2_data[0]['assets'] as $asset)
								{
									if(!isset($assets_2[$asset['classid']]))
									{
										$assets_2[$asset['classid']] = array();
									}

									array_push($assets_2[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							if(isset($response_3_data[0]['assets']))
							{
								foreach($response_3_data[0]['assets'] as $asset)
								{
									if(!isset($assets_3[$asset['classid']]))
									{
										$assets_3[$asset['classid']] = array();
									}

									array_push($assets_3[$asset['classid']], array(
										"context_id" => $asset['contextid'],
										"asset_id" => $asset['assetid'],
										"amount" => $asset['amount']));
								}
							}

							for($i = 0, $j = sizeof($receive_steam); $i < $j; $i ++)
							{
								$receive_steam_data = explode("|separator|", $receive_steam[$i]);
								$name = (string) $receive_steam_data[0];

								if(isset($extended_key_info[$name]))
								{
									$quantity = (int) $receive_steam_data[1];
									$app_id = (int) $receive_steam_data[2];

									switch($app_id)
									{
										case 440: // TF2
										{
											for($k = 0, $l = sizeof($response_1_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_1_data[0]['descriptions'][$k]))
												{
													$key = $response_1_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_1[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_1[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_1[$key['classid']][$m]['context_id'] . "|separator|" . $assets_1[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_1[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_1[$key['classid']][$m]['context_id'] . "|separator|" . $assets_1[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
										case 730: // CS:GO
										{
											for($k = 0, $l = sizeof($response_2_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_2_data[0]['descriptions'][$k]))
												{
													$key = $response_2_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_2[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_2[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_2[$key['classid']][$m]['context_id'] . "|separator|" . $assets_2[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_2[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_2[$key['classid']][$m]['context_id'] . "|separator|" . $assets_2[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
										case 232090: // KF2
										{
											for($k = 0, $l = sizeof($response_3_data[0]['descriptions']); $k <= $l; $k ++)
											{
												if(isset($response_3_data[0]['descriptions'][$k]))
												{
													$key = $response_3_data[0]['descriptions'][$k];

													if($key['name'] == $name && $key['tradable'])
													{
														for($m = 0; $m < $quantity; $m ++)
														{
															if(isset($assets_3[$key['classid']][$m]))
															{
																if($items == NULL)
																{
																	$items = $assets_3[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_3[$key['classid']][$m]['context_id'] . "|separator|" . $assets_3[$key['classid']][$m]['amount'];
																}
																else
																{
																	$items .= "," . $assets_3[$key['classid']][$m]['asset_id'] . "|separator|" . $app_id . "|separator|" . $assets_3[$key['classid']][$m]['context_id'] . "|separator|" . $assets_3[$key['classid']][$m]['amount'];
																}
															}
															else
															{
																echo "Steam|separator|Unable to request your key(s).";
																return;
															}
														}
														break;
													}
												}
												else
												{
													echo "Steam|separator|Unable to request your key(s).";
													return;
												}
											}
											break;
										}
									}
								}
							}

							if($items != NULL)
							{
								$query_4 = $connection -> prepare("UPDATE `key_exchanges` SET `receive_steam_items` = :receive_steam_items WHERE `id` = :id LIMIT 1");
								$query_4 -> bindParam(":receive_steam_items", $items);
								$query_4 -> bindParam(":id", $query_1_result['id']);
								$query_4 -> execute();

								echo "Next Step|separator|Waiting for bot to send your new key(s)...";
							}
						}
					}
				}
				else if($query_1_result['receive_steam_offer_id'] != NULL)
				{
					if($query_1_result['receive_expresstrade'] != NULL)
					{
						echo "Exchange Complete|separator|Accept the <a class = \"p_link\" href = \"https://trade.opskins.com/trade-offers/" . $query_1_result['receive_expresstrade_offer_id'] . "\" target = \"_blank\">trade offer</a> on ExpressTrade.<br>Accept the <a class = \"p_link\" href = \"https://steamcommunity.com/tradeoffer/" . $query_1_result['receive_steam_offer_id'] . "\" target = \"_blank\">trade offer</a> on Steam.<br><br>Your remaining balance is stored, and<br>you will soon be able to cash it out or<br>complete offers to match for key(s).";
					}
					else
					{
						echo "Exchange Complete|separator|Accept the <a class = \"p_link\" href = \"https://steamcommunity.com/tradeoffer/" . $query_1_result['receive_steam_offer_id'] . "\" target = \"_blank\">trade offer</a> on Steam.<br><br>Your remaining balance is stored, and<br>you will soon be able to cash it out or<br>complete offers to match for key(s).";
					}
				}
			}
		}
	}
?>