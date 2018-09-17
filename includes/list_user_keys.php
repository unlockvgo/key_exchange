<?php
	session_start();

	$my_keys_state = preg_replace("/[^0-9]/", "", $_POST['my_keys_state']);

	if($my_keys_state == NULL)
	{
		$my_keys_state = 1;
	}

	if(($my_keys_state == 1 && isset($_SESSION['steam_id'])) || !$my_keys_state)
	{
		include_once "connection.php";
		include_once "get_api_key.php";
		include_once "execute_api_call.php";
		include_once "function_currency_format.php";

		$platform = preg_replace("/[^0-9]/", "", $_POST['platform']);
		$app_id = preg_replace("/[^0-9]/", "", $_POST['app_id']);

		if($my_keys_state == 1)
		{
			$steam_id = $_SESSION['steam_id'];
		}
		else
		{
			$steam_id = "76561198364842175";
		}

		switch($platform)
		{
			case 1: // ExpressTrade
			{
				$app_info = array(
					1 => array(
						"name" => "VGO",
						"search_filter" => "Skeleton%20Key"));

				$response = ExecuteAPICall("GET", "ITrade/GetUserInventoryFromSteamId/v1", array("key=" . GetAPIKey() . "&steam_id=" . $steam_id . "&app_id=" . $app_id . "&page=1&per_page=500&search=" . $app_info[$app_id]['search_filter']));

				if($response != NULL)
				{
					$response_data = json_decode($response, true);

					if(isset($response_data[0]['response']['items']))
					{
						$extended_key_info = array();

						$query = $connection -> prepare("SELECT `name`, `opens`, `price` FROM `extended_key_info`");
						$query -> execute();

						while($query_result = $query -> fetch())
						{
							$extended_key_info[$query_result['name']] = array(
								"opens" => (string) $query_result['opens'],
								"price" => (float) $query_result['price']);
						}

						$image_background_state = 0;
						$key_data = array();
						$extended_key_data = array();
						$trade_locked_keys = array();
						$count = 0;

						if($_SESSION['mobile'])
						{
							$key_background = "key_background.png";
						}
						else
						{
							$key_background = "key_background.svg";
						}

						foreach($response_data[0]['response']['items'] as $item)
						{
							if(isset($key_data[$item['sku']]['name']))
							{
								$key_data[$item['sku']]['quantity'] += 1;
							}
							else
							{
								$key_data[$item['sku']]['name'] = $item['name'];
								$key_data[$item['sku']]['quantity'] = 1;

								if($item['name'] == "Skeleton Key")
								{
									$key_data[$item['sku']]['extra'] = "1912_1";
								}

								array_push($extended_key_data, $item);
							}
						}

						for($i = 0, $j = sizeof($extended_key_data); $i < $j; $i ++)
						{
							$key = $extended_key_data[$i];
							$name = $key['name'];

							if(isset($extended_key_info[$name]))
							{
								$price_1 = (($extended_key_info[$name]['price']) - ($extended_key_info[$name]['price'] * (($my_keys_state == 1) ? (0.10) : (0))));
								$price_2 = currency_format($price_1, -2, ".", ",");
								$quantity_1 = $key_data[$key['sku']]['quantity'];
								$quantity_2 = currency_format($quantity_1, 0, ".", ",", "");
								$image = $name . ".png";
								$opens = explode(",", $extended_key_info[$name]['opens']);

								if($key['tradable'])
								{
									$element_identifier = (($my_keys_state == 1) ? ('p_id_my_keys_') : ('p_id_exchange_')) . $platform . "_" . $app_id . "_" . $name;

									?>

									<div id = "<?php echo $element_identifier; ?>" style = "margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;" onclick = 'P_AddKeyToTrade(<?php echo $my_keys_state; ?>, "<?php echo $name; ?>", <?php echo json_encode($opens); ?>, <?php echo $price_1; ?>, "<?php echo $price_2; ?>", <?php echo $quantity_1; ?>, "<?php echo $quantity_2; ?>", "<?php echo $key_background; ?>", "<?php echo $image; ?>", <?php echo $image_background_state; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]['name']; ?>", "<?php echo $key_data[$key['sku']]['extra']; ?>");' oncontextmenu = 'P_ShowKeyInfo("<?php echo $name; ?>", <?php echo json_encode($opens); ?>, "<?php echo $price_2; ?>", "<?php echo $image; ?>", <?php echo $image_background_state; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]['name']; ?>", "<?php echo $key_data[$key['sku']]['extra']; ?>"); return false;'>
										<div style = "width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;" title = "<?php echo $name . ' - ' . $price_2; ?>">
											<img style = "width: 70px; height: 70px; position: absolute;" alt = "Key Background" src = "images/<?php echo $key_background; ?>"/>

											<img class = "animation_group_11_1" style = "width: 70px; height: 70px;<?php echo (($image_background_state) ? (" border-radius: 50%;") : ("")); ?> z-index: 1; position: absolute;" alt = "<?php echo $name; ?>" src = "images/keys/<?php echo $image; ?>"/>

											<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
												x<div id = "<?php echo $element_identifier; ?>_quantity" style = "display: inline-block;"><?php echo $quantity_2; ?></div>
											</div>

											<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
												<?php

												echo $price_2;

												?>
											</div>
										</div>
									</div>

									<?php
								}
								else
								{
									array_push($trade_locked_keys, array(
										"name" => (string) $name,
										"price" => (string) $price_2,
										"quantity" => (string) $quantity_2,
										"key_background" => (string) $key_background,
										"image" => (string) $image,
										"image_background_state" => (int) $image_background_state,
										"opens" => $opens));
								}

								$count += 1;
							}
						}

						if($count)
						{
							for($i = 0, $j = sizeof($trade_locked_keys); $i < $j; $i ++)
							{
								?>

								<div style = "margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;" onclick = 'P_ShowKeyInfo("<?php echo $trade_locked_keys[$i]['name']; ?>", <?php echo json_encode($trade_locked_keys[$i]['opens']); ?>, "<?php echo $trade_locked_keys[$i]['price']; ?>", "<?php echo $trade_locked_keys[$i]['image']; ?>", <?php echo $trade_locked_keys[$i]['image_background_state']; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]['name']; ?>", "<?php echo $key_data[$key['sku']]['extra']; ?>"); return false;'>
									<div style = "width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;" title = "<?php echo $trade_locked_keys[$i]['name'] . ' - ' . $trade_locked_keys[$i]['price'] . ' (Trade Locked)'; ?>">
										<img style = "width: 70px; height: 70px; position: absolute;" alt = "Key Background" src = "images/<?php echo $trade_locked_keys[$i]['key_background']; ?>"/>

										<img class = "animation_group_11_1" style = "width: 70px; height: 70px;<?php echo (($trade_locked_keys[$i]['image_background_state']) ? (" border-radius: 50%;") : ("")); ?> z-index: 1; position: absolute;" alt = "<?php echo $trade_locked_keys[$i]['name']; ?>" src = "images/keys/<?php echo $trade_locked_keys[$i]['image']; ?>"/>

										<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
											x<?php echo $trade_locked_keys[$i]['quantity']; ?>
										</div>

										<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
											<?php

											echo $trade_locked_keys[$i]['price'];

											?>
										</div>

										<div style = "width: 75px; height: 75px; margin: -2.5px 0px 0px -2.5px; background: rgba(179, 1, 1, 0.50); border-radius: 50%; text-align: center; z-index: 3; position: absolute;">
											<img style = "height: 27px; margin-top: 24px;" alt = "Trade Locked" src = "images/lock.png"/>
										</div>
									</div>
								</div>

								<?php
							}

							?>

							<script type = "text/javascript">
								OnAnimationGroupStatusChange(11, g_animation_status[10]);
							</script>

							<?php
						}
						else
						{
							?>

							<div style = "margin: 5px 0px 0px 5px;">
								<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
									<td>
										You don't have any <?php echo $app_info[$app_id]['name']; ?> keys
									</td>
								</table>
							</div>

							<?php
						}
					}
					else
					{
						?>

						<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
							<td>
								Unable to retrieve your <?php echo $app_info[$app_id]['name']; ?> keys
							</td>
						</table>

						<?php
					}
				}
				else
				{
					?>

					<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
						<td>
							Unable to retrieve your <?php echo $app_info[$app_id]['name']; ?> keys
						</td>
					</table>

					<?php
				}
				break;
			}
			case 2: // Steam
			{
				$app_info = array(
					440 => "TF2",
					730 => "CS:GO",
					232090 => "KF2");

				$response = ExecuteAPICall("GET", "inventory/" . $steam_id . "/" . $app_id . "/2", NULL, "https://steamcommunity.com/");

				if($response != NULL)
				{
					$response_data = json_decode($response, true);

					if(isset($response_data[0]['assets']) && isset($response_data[0]['descriptions']))
					{
						$extended_key_info = array();

						$query = $connection -> prepare("SELECT `name`, `opens`, `price` FROM `extended_key_info`");
						$query -> execute();

						while($query_result = $query -> fetch())
						{
							$extended_key_info[$query_result['name']] = array(
								"opens" => (string) $query_result['opens'],
								"price" => (float) $query_result['price']);
						}

						$forbidden_search = array("CS:GO", "|", "#");
						$forbidden_replace = array("CSGO", "-", "");
						$quantities = array();
						$trade_locked_keys = array();
						$count = 0;

						if($_SESSION['mobile'])
						{
							$key_background = "key_background.png";
						}
						else
						{
							$key_background = "key_background.svg";
						}

						if($app_id == 232090)
						{
							$image_background_state = 1;
						}
						else
						{
							$image_background_state = 0;
						}

						foreach($response_data[0]['assets'] as $asset)
						{
							if(isset($quantities[$asset['classid']]))
							{
								$quantities[$asset['classid']] += 1;
							}
							else
							{
								$quantities[$asset['classid']] = 1;
							}
						}

						for($i = 0, $j = sizeof($response_data[0]['descriptions']); $i < $j; $i ++)
						{
							$key = $response_data[0]['descriptions'][$i];
							$name = $key['market_name'];

							if(isset($extended_key_info[$name]))
							{
								$price_1 = (($extended_key_info[$name]['price']) - ($extended_key_info[$name]['price'] * (($my_keys_state == 1) ? (0.10) : (0))));
								$price_2 = currency_format($price_1, -2, ".", ",");
								$quantity_1 = $quantities[$key['classid']];
								$quantity_2 = currency_format($quantity_1, 0, ".", ",", "");
								$image = str_replace($forbidden_search, $forbidden_replace, $name) . ".png";
								$opens = explode(",", str_replace($forbidden_search, $forbidden_replace, $extended_key_info[$name]['opens']));

								if($key['tradable'])
								{
									$element_identifier = (($my_keys_state == 1) ? ('p_id_my_keys_') : ('p_id_exchange_')) . $platform . "_" . $app_id . "_" . $name;

									?>

									<div id = "<?php echo $element_identifier; ?>" style = "margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;" onclick = 'P_AddKeyToTrade(<?php echo $my_keys_state; ?>, "<?php echo $name; ?>", <?php echo json_encode($opens); ?>, <?php echo $price_1; ?>, "<?php echo $price_2; ?>", <?php echo $quantity_1; ?>, "<?php echo $quantity_2; ?>", "<?php echo $key_background; ?>", "<?php echo $image; ?>", <?php echo $image_background_state; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]; ?>");' oncontextmenu = 'P_ShowKeyInfo("<?php echo $name; ?>", <?php echo json_encode($opens); ?>, "<?php echo $price_2; ?>", "<?php echo $image; ?>", <?php echo $image_background_state; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]; ?>"); return false;'>
										<div style = "width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;" title = "<?php echo $name . ' - ' . $price_2; ?>">
											<img style = "width: 70px; height: 70px; position: absolute;" alt = "Key Background" src = "images/<?php echo $key_background; ?>"/>

											<img class = "animation_group_11_1" style = "width: 70px; height: 70px;<?php echo (($image_background_state) ? (" border-radius: 50%;") : ("")); ?> z-index: 1; position: absolute;" alt = "<?php echo $name; ?>" src = "images/keys/<?php echo $image; ?>"/>

											<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
												x<div id = "<?php echo $element_identifier; ?>_quantity" style = "display: inline-block;"><?php echo $quantity_2; ?></div>
											</div>

											<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
												<?php

												echo $price_2;

												?>
											</div>
										</div>
									</div>

									<?php
								}
								else
								{
									array_push($trade_locked_keys, array(
										"name" => (string) $name,
										"price" => (string) $price_2,
										"quantity" => (string) $quantity_2,
										"key_background" => (string) $key_background,
										"image" => (string) $image,
										"image_background_state" => (int) $image_background_state,
										"opens" => $opens));
								}

								$count += 1;
							}
						}

						if($count)
						{
							for($i = 0, $j = sizeof($trade_locked_keys); $i < $j; $i ++)
							{
								?>

								<div style = "margin: 5px 0px 0px 5px; text-align: left; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; user-select: none; standard-user-select: none; cursor: pointer; display: inline-block;" onclick = 'P_ShowKeyInfo("<?php echo $trade_locked_keys[$i]['name']; ?>", <?php echo json_encode($trade_locked_keys[$i]['opens']); ?>, "<?php echo $trade_locked_keys[$i]['price']; ?>", "<?php echo $trade_locked_keys[$i]['image']; ?>", <?php echo $trade_locked_keys[$i]['image_background_state']; ?>, <?php echo $platform; ?>, <?php echo $app_id; ?>, "<?php echo $app_info[$app_id]; ?>"); return false;'>
									<div style = "width: 70px; height: 70px; font-family: arial; font-size: 9pt; color: #AFAFAF; -webkit-transform: translateX(-50%); transform: translateX(-50%); left: 50%; position: relative;" title = "<?php echo $trade_locked_keys[$i]['name'] . ' - ' . $trade_locked_keys[$i]['price'] . ' (Trade Locked)'; ?>">
										<img style = "width: 70px; height: 70px; position: absolute;" alt = "Key Background" src = "images/<?php echo $trade_locked_keys[$i]['key_background']; ?>"/>

										<img class = "animation_group_11_1" style = "width: 70px; height: 70px;<?php echo (($trade_locked_keys[$i]['image_background_state']) ? (" border-radius: 50%;") : ("")); ?> z-index: 1; position: absolute;" alt = "<?php echo $trade_locked_keys[$i]['name']; ?>" src = "images/keys/<?php echo $trade_locked_keys[$i]['image']; ?>"/>

										<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
											x<?php echo $trade_locked_keys[$i]['quantity']; ?>
										</div>

										<div style = "width: 70px; height: 70px; color: #FFC62E; text-align: center; text-shadow: #000000 1px 1px 5px; -webkit-font-smoothing: antialiased; line-height: 129px; z-index: 2; position: absolute; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
											<?php

											echo $trade_locked_keys[$i]['price'];

											?>
										</div>

										<div style = "width: 75px; height: 75px; margin: -2.5px 0px 0px -2.5px; background: rgba(179, 1, 1, 0.50); border-radius: 50%; text-align: center; z-index: 3; position: absolute;">
											<img style = "height: 27px; margin-top: 24px;" alt = "Trade Locked" src = "images/lock.png"/>
										</div>
									</div>
								</div>

								<?php
							}

							?>

							<script type = "text/javascript">
								OnAnimationGroupStatusChange(11, g_animation_status[10]);
							</script>

							<?php
						}
						else
						{
							?>

							<div style = "margin: 5px 0px 0px 5px;">
								<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
									<td>
										You don't have any <?php echo $app_info[$app_id]; ?> keys
									</td>
								</table>
							</div>

							<?php
						}
					}
					else
					{
						?>

						<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
							<td>
								You don't have any <?php echo $app_info[$app_id]; ?> keys
							</td>
						</table>

						<?php
					}
				}
				else
				{
					?>

					<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
						<td>
							Unable to retrieve your <?php echo $app_info[$app_id]; ?> keys
						</td>
					</table>

					<?php
				}
				break;
			}
		}
	}
	else
	{
		?>

		<table style = "width: 100%; height: 100%; font-family: arial; font-size: 11pt; color: #B30101; text-align: center;" border = "0" cellspacing = "0" cellpadding = "0">
			<td>
				Guests can't exchange keys
			</td>
		</table>

		<?php
	}
?>