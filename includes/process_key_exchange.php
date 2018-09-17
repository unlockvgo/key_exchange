<?php
	session_start();

	if(isset($_SESSION['steam_id']))
	{
		include_once "connection.php";

		$my_keys = json_decode($_POST['my_keys'], true);
		$exchange = json_decode($_POST['exchange'], true);

		$extended_key_info = array();

		$query = $connection -> prepare("SELECT `name`, `price` FROM `extended_key_info`");
		$query -> execute();

		while($query_result = $query -> fetch())
		{
			$extended_key_info[$query_result['name']] = array(
				"price" => (float) $query_result['price']);
		}

		$send_expresstrade = NULL;
		$send_steam = NULL;
		$receive_expresstrade = NULL;
		$receive_steam = NULL;
		$send_price = 0.00;
		$receive_price = 0.00;

		for($i = 0, $j = sizeof($my_keys); $i < $j; $i ++)
		{
			if(isset($extended_key_info[$my_keys[$i]['name']]))
			{
				$key = $my_keys[$i];
				$name = $key['name'];

				switch($key['platform'])
				{
					case 1: // ExpressTrade
					{
						if($send_expresstrade == NULL)
						{
							$send_expresstrade = $name . "|separator|" . $key['selected_quantity'];
						}
						else
						{
							$send_expresstrade .= "," . $name . "|separator|" . $key['selected_quantity'];
						}
						break;
					}
					case 2: // Steam
					{
						if($send_steam == NULL)
						{
							$send_steam = $name . "|separator|" . $key['selected_quantity'];
						}
						else
						{
							$send_steam .= "," . $name . "|separator|" . $key['selected_quantity'];
						}
						break;
					}
				}

				$send_price += (($extended_key_info[$name]['price'] - ($extended_key_info[$name]['price'] * 0.10)) * $key['selected_quantity']);
			}
		}

		for($i = 0, $j = sizeof($exchange); $i < $j; $i ++)
		{
			if(isset($extended_key_info[$exchange[$i]['name']]))
			{
				$key = $exchange[$i];
				$name = $key['name'];

				switch($key['platform'])
				{
					case 1: // ExpressTrade
					{
						if($receive_expresstrade == NULL)
						{
							$receive_expresstrade = $name . "|separator|" . $key['selected_quantity'];
						}
						else
						{
							$receive_expresstrade .= "," . $name . "|separator|" . $key['selected_quantity'];
						}
						break;
					}
					case 2: // Steam
					{
						if($receive_steam == NULL)
						{
							$receive_steam = $name . "|separator|" . $key['selected_quantity'];
						}
						else
						{
							$receive_steam .= "," . $name . "|separator|" . $key['selected_quantity'];
						}
						break;
					}
				}

				$receive_price += ($extended_key_info[$name]['price'] * $key['selected_quantity']);
			}
		}

		if($receive_price < $send_price)
		{
			
		}
		else
		{
			echo "Alert|separator|Error in your selection.";
		}
	}
?>