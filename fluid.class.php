<?php
// Michael Rajotte - 2016 June
// fluid.class.php
// Fluid main class.

class Fluid {
	protected $db_server;
	protected $db_database;
	protected $db_username;
	protected $db_password;
	protected $db_link;
	protected $db_result;

	public $db_array; // MySQL result data sets are stored here.
	public $db_affected_rows;
	public $db_error; // Error messages.
	public $tmp_files; // $_GET['files'];

	public function __construct () {
		$this->db_server = DB_SERVER;
		$this->db_database = DB_DATABASE;
		$this->db_username = DB_USERNAME;
		$this->db_password = DB_PASSWORD;
	}

	public function php_db_begin() {
		$this->php_db_connect();
		$this->db_error = NULL;

		$this->php_debug("START TRANSACTION;", TRUE);
		mysqli_query($this->db_link, "START TRANSACTION;");
	}

	private function php_db_close() {
		if($this->db_link)
			mysqli_close($this->db_link);
	}

	public function php_db_commit() {
		$this->php_debug("COMMIT\n", TRUE);

		mysqli_query($this->db_link, "COMMIT;");

		$this->php_db_close();
	}

	private function php_db_connect() {
		$this->db_link = mysqli_connect($this->db_server, $this->db_username, $this->db_password, $this->db_database);

		if(!$this->db_link) {
			$error_msg = base64_encode("Error: Unable to connect to MySQL." . PHP_EOL . "<br><br>Debugging errno: " . mysqli_connect_errno() . PHP_EOL . "<br><br>Debugging error: " . mysqli_connect_error() . PHP_EOL);

			echo json_encode(array("error" => 1, "error_message" => $error_msg));

			exit;
		}
	}

	public function php_db_query($query) {
		try {
			$this->php_debug($query, TRUE); // Temporary output the query to the debug file for development testing.
			$status = TRUE;
			$this->db_result = mysqli_query($this->db_link, $query);

			$this->db_affected_rows = mysqli_affected_rows($this->db_link);

			unset($this->db_array); // Empty the array before refilling it with data.

			if(empty($this->db_result)) {
				if(mysqli_error($this->db_link)) {
					//$backtrace = debug_backtrace();

					//if($this->db_error)
						//$error_message = "<br><br>";
					//else
						//$error_message = "";

					//$error_message .= $query . "<br><br>" . mysqli_error($this->db_link);
					//$error_message .= "<br>" . basename($backtrace[0]['file']) . " : " . $backtrace[0]['line'];

					//$error_message = "Database query error. Please try again.";
					//$this->db_error .= $error_message;
					//$this->db_error = "Database query error. Please try again.";
					$status = FALSE;

					if(mysqli_errno($this->db_link) == 1062)
						$error_message = mysqli_error($this->db_link);
					else
						$error_message = "Database query error. Please try again.";

					$this->db_error .= $error_message;
					throw new Exception($error_message);
				}
			}
			else {
				// MYSQLI_BOTH | MYSQLI_ASSOC | MYSQLI_NUM <- Array key format. [#] or [abc] or contain both.
				if(is_object($this->db_result)) {
					while($row = mysqli_fetch_array($this->db_result, MYSQLI_ASSOC)) {
						$this->db_array[] = $row;
					}
				}
			}

			if($this->db_result && is_object($this->db_result))
				mysqli_free_result($this->db_result);

			return $status;
		}
		catch (Exception $err) {
			throw new Exception($err->getMessage()); // Pass the throw error to the calling function so it catches this error and stops the code.
		}
	}

	public function php_db_rollback() {
		$this->php_debug("ROLLBACK\n", TRUE);

		mysqli_query($this->db_link, "ROLLBACK;");

		$this->php_db_close();
	}

	public function php_debug($data, $enable_log = FALSE) {
		if(ENABLE_LOG == TRUE) {
			if(is_array($data))
				$data = json_encode($data);

			$req_dump = print_r("\n" . $data, TRUE);
			$fp = fopen(DEBUG_LOG, 'a');
			fwrite($fp, $req_dump);
			fclose($fp);
		}
	}

	public function php_fluid_fraud_score($s_data = NULL, $f_moneris_obj = NULL, $o_ship = NULL, $paypal = NULL, $auth_net_object = NULL) {
		try {
			// Initalise some variables.
			$f_fraud_score = 0;
			$f_verify = NULL;
			$f_paypal_email_check = NULL;
			$f_paypal_country = NULL;
			$f_paypal_protection_html = NULL;
			$f_address_match = NULL;
			$f_avs = NULL;
			$f_cvd = NULL;
			$f_purchase_email = NULL;
			$f_paypal_protection_html = NULL;
			$f_protection_types = NULL;

			$f_avs = "PASSED";
			if(isset($s_data['s_avs_failed'])) {
				if($s_data['s_avs_failed'] == 1) {
					$f_avs = "FAILED";
					$f_fraud_score = $f_fraud_score + 10;
				}
								
				if(isset($auth_net_object->avsResultCode)) {
					switch ($f_auth_net_object->avsResultCode) {
						case 'A':
							$f_fraud_score = $f_fraud_score - 6;
							break;
						case 'B':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case 'E':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case 'N':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case 'R':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case 'G':
							$f_fraud_score = $f_fraud_score - 1;
							break;
						case 'S':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case 'U':
							$f_fraud_score = $f_fraud_score - 2;
							break;
						case 'W':
							$f_fraud_score = $f_fraud_score - 3;
							break;
						default:
							$f_fraud_score = $f_fraud_score;
					}
				}

				if(isset($f_moneris_obj['AvsResultCode'])) {
					switch($f_moneris_obj['AvsResultCode']) {
						case "A":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case "B":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case "C":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case "X":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case "P":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						case "U":
							$f_fraud_score = $f_fraud_score - 2;
							break;
						case "G":
							$f_fraud_score = $f_fraud_score - 2;
							break;
						case "Z":
							$f_fraud_score = $f_fraud_score - 3;
							break;
						default:
							$f_fraud_score = $f_fraud_score;

					}
				}
			}

			$f_cvd = "PASSED";
			if(isset($s_data['s_cvd_failed'])) {
				if($s_data['s_cvd_failed'] == 1) {
					$f_cvd = "FAILED";
					$f_fraud_score = $f_fraud_score + 4;
				}
			}

			if(isset($auth_net_object->STATUS)) {
				$f_cvd = "Customer will pay for order during pickup";
			}
			
			if(isset($auth_net_object->STATUS)) {
				$f_avs = "Customer will pay for order during pickup";
			}
			
			$this->php_db_begin();
			//s_u_email, s_address_street, s_address_postalcode
			$this->php_db_query("SELECT COUNT(s_id) AS id_count FROM " . TABLE_SALES . " WHERE s_u_email = '" . $s_data['s_u_email'] . "'");

			$f_purchase_email = 0;
			if(isset($this->db_array)) {
				$f_purchase_email = $this->db_array[0]['id_count'] - 1;
			}

			if($f_purchase_email == 0) {
				$f_fraud_score = $f_fraud_score + 1;
			}
			else if($f_purchase_email == 1) {
				$f_fraud_score--;
			}
			else if($f_purchase_email == 2) {
				$f_fraud_score = $f_fraud_score - 2;
			}
			else if($f_purchase_email == 3) {
				$f_fraud_score = $f_fraud_score - 3;
			}
			else if($f_purchase_email >= 4) {
				$f_fraud_score = $f_fraud_score - 5;
			}

			$this->php_db_query("SELECT COUNT(s_id) AS id_count FROM " . TABLE_SALES . " WHERE s_address_street = '" . $s_data['s_address_street'] . "' AND s_address_postalcode = '" . $s_data['s_address_postalcode'] . "'");

			$f_purchase_address = 0;
			if(isset($this->db_array)) {
				$f_purchase_address = $this->db_array[0]['id_count'] - 1;
			}

			if($f_purchase_address < 0) {
				$f_purchase_address = 0;
			}

			if($f_purchase_address == 0) {
				$f_fraud_score = $f_fraud_score + 1;
			}
			else if($f_purchase_address == 1) {
				$f_fraud_score--;
			}
			else if($f_purchase_address == 2) {
				$f_fraud_score = $f_fraud_score - 2;
			}
			else if($f_purchase_address == 3) {
				$f_fraud_score = $f_fraud_score - 3;
			}
			else if($f_purchase_address >= 4) {
				$f_fraud_score = $f_fraud_score - 5;
			}

			$this->php_db_commit();

			if(isset($paypal->id)) {
				if($paypal->payer->payer_info->country_code == "CA") {
					$f_fraud_score = $f_fraud_score - 2;
					$f_paypal_country = $paypal->payer->payer_info->country_code;
				}
				else {
					$f_fraud_score = $f_fraud_score + 5;
					$f_paypal_country = $paypal->payer->payer_info->country_code;
				}

				if($paypal->payer->status == "VERIFIED") {
					$f_fraud_score = $f_fraud_score - 2;
					$f_verify = "VERIFIED";
				}
				else {
					$f_fraud_score = $f_fraud_score + 4;
					$f_verify = "NOT VERIFIED";
				}

				if($paypal->payer->payer_info->email != $s_data['s_u_email']) {
					$f_fraud_score = $f_fraud_score + 4;
					$f_paypal_email_check = "DOES NOT MATCH";
				}
				else {
					$f_fraud_score = $f_fraud_score - 2;
					$f_paypal_email_check = "MATCH";
				}

				foreach($paypal->transactions as $p_trans) {
					if(isset($p_trans->related_resources)) {
						foreach($p_trans->related_resources as $f_resource) {
							if(isset($f_resource->sale->protection_eligibility) == "ELIGIBLE") {
								$f_fraud_score = $f_fraud_score - 3;
								$f_paypal_protection_html = "Eligible";
							}

							if(isset($f_resource->sale->protection_eligibility_type))
								$f_protection_types = $f_resource->sale->protection_eligibility_type;

							if(isset($f_resource->sale->transaction_fee))
								$f_transaction_fee = $f_resource->sale->transaction_fee;

							if(isset($f_resrouce->sale->payment_mode))
								$f_payment_mode = $f_resource->sale->payment_mode;
						}
					}
				}

				if(empty($f_paypal_protection_html)) {
					$f_paypal_protection_html = "NO";
					$f_fraud_score = $f_fraud_score + 20;
				}

				if(empty($f_protection_types)) {
					$f_protection_types = "?";
				}
			}
			else {
				$f_address_match = "NO";
				$f_check_payment = json_decode(base64_decode($s_data['s_address_payment_64']), TRUE);
				if($f_check_payment['a_street'] == $s_data['s_address_street'] && $f_check_payment['a_postalcode'] == $s_data['s_address_postalcode'] && $f_check_payment['a_province'] == $s_data['s_address_province'] && $f_check_payment['a_country'] == $s_data['s_address_country'])
					$f_address_match = "YES";

				if($f_address_match == "NO") {
					$f_fraud_score = $f_fraud_score + 3;

					if($f_purchase_address == 0) {
						$f_fraud_score++;
					}

					if($f_purchase_email == 0) {
						$f_fraud_score++;
					}
				}
				else {
					if($f_avs == "FAILED")
						$f_fraud_score--;
				}
			}

			if(isset($o_ship)) {
				if($o_ship['data']['ship_type'] == IN_STORE_PICKUP) {
					$f_fraud_score = $f_fraud_score - 5;
				}
			}

			if($f_fraud_score > 10) {
				$f_fraud_score = 10;
			}
			else if($f_fraud_score < 0) {
				$f_fraud_score = 0;
			}

			return Array("fraud_score" => $f_fraud_score, "f_avs" => $f_avs, "f_cvd" => $f_cvd, "f_address_match" => $f_address_match, "f_paypal_protection" => $f_paypal_protection_html, "f_verify" => $f_verify, "f_paypal_email_check" => $f_paypal_email_check, "f_paypal_country" => $f_paypal_country, "f_purchase_email" => $f_purchase_email, "f_purchase_address" => $f_purchase_address, "f_paypal_protection_types" => $f_protection_types);
		}
		catch (Exception $err) {
			// Was a error processing the fraud score, it could be high, lets return 100%.
			return Array("fraud_score" => 10, "f_avs" => "FAILED", "f_cvd" => "FAILED", "f_address_match" => "NO", "f_paypal_protection" => "NO", "f_verify" => "NO", "f_paypal_email_check" => "NO", "f_paypal_country" => "Unknown");
		}
	}

	public function php_delete_file($file) {
		try {
			set_error_handler(function($errno, $errstr, $errfile, $errline) {
				throw new Exception($errstr . " on line " . $errline . " in file " . $errfile);
			});

			if(file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $file)) {
				unlink(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $file);

				return json_encode(array("error" => 0, "error_message" => base64_encode("Deleted file: " . $file)));
			}
			else
				return json_encode(array("error" => 0, "error_message" => base64_encode("Deleted file not found in " . FOLDER_IMAGES_TEMP . " --> " . $file)));
		}
		catch (Exception $err) {
			restore_error_handler();
			throw $err->getMessage();
		}
	}

	public function php_delete_image_temp() {
		try {
			set_error_handler(function($errno, $errstr, $errfile, $errline) {
				throw new Exception($errstr . " on line " . $errline . " in file " . $errfile);
			});

			if(isset($_SESSION['fluid_admin'])) {
				// Get all the temp image file names.
				$files = glob(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . '/*');

				foreach($files as $file) {
				  if(is_file($file))
					unlink($file);
				}

				if(file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']))
					$f_remove = rmdir(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']);
			}
			else {
				// Get all the temp image file names.
				$files = glob(FOLDER_IMAGES_TEMP . '*');

				foreach($files as $file) {
				  if(is_file($file))
					unlink($file);
				}
			}

			return json_encode(array("error" => 0, "error_message" => base64_encode("Folder : " . FOLDER_IMAGES_TEMP)));
		}
		catch (Exception $err) {
			restore_error_handler();
			throw $err->getMessage();
		}
	}

	public function php_encode_array($array) {
		$array_temp = NULL;

		foreach($array as $key => $data) {
			$array_temp[$key] = base64_encode($data);
		}

		return json_encode($array_temp);
	}

	public function php_escape_string($data) {
		$escape_string = mysqli_real_escape_string($this->db_link, $data);

		return $escape_string;
	}

	public function php_html_process_rating_stars($rating) {
		$data = "";

		for($i = 1; $i <= $rating; $i++)
			$data .= "<img src='files/review-star-yellow.png'></img> ";

		for($i = ($rating + 1); $i <= VAR_MAXRATING; $i++)
			$data .= "<img src='files/review-star-grey.png'></img> ";

		return $data;
	}

	public function php_math_price($price_regular, $price_discount, $discount_date_end, $discount_date_start) {
		if($price_discount && ((strtotime($discount_date_start) < strtotime(date('Y-m-d H:i:s')) && strtotime($discount_date_end) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($discount_date_start) < strtotime(date('Y-m-d H:i:s')) && $discount_date_end == NULL) || ($discount_date_start == NULL && $discount_date_end == NULL) ))
			return $price_discount;
		else
			return $price_regular;
	}

	public function php_math_savings($price_regular, $price_discount, $discount_date_end) {
		return Array("dollar" => $price_regular - $price_discount, "percent" => (($price_regular-$price_discount) / $price_regular) * 100);
	}

	public function php_nl2p($string, $line_breaks = true, $xml = true) {
		$string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

		// You might want to still have line breaks without breaking into a new paragraph.
		if ($line_breaks == true)
			return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($string)).'</p>';
		else
			return '<p>'.preg_replace(
			array("/([\n]{2,})/i", "/([\r\n]{3,})/i","/([^>])\n([^<])/i"),
			array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'),

			trim($string)).'</p>';
	}

	public function php_object_to_array($obj, &$arr = NULL) {
		if(!is_object($obj) && !is_array($obj)) {
			$arr = $obj;
			return $arr;
		}

		foreach ($obj as $key => $value) {
			if(!empty($value)) {
				$arr[$key] = array();
				$this->php_object_to_array($value, $arr[$key]);
			}
			else {
				$arr[$key] = $value;
			}
		}

		return $arr;
	}

	public function php_process_csv_upload($tmp_files, $delimiter) {
		try {
			$rand = rand(100, 999999999999);
			$f_file = FOLDER_ADMIN_TEMP . $rand . ".csv";
			move_uploaded_file($tmp_files["tmp_name"], $f_file);
			chmod($f_file, 0777);

			$delim_data = json_decode(base64_decode($delimiter), TRUE);

			if(($handle = fopen($f_file, "r")) !== FALSE) {
				$this->php_db_begin();

				$f_return_msg = "error";

				$row = 1;
				$f_tbl_headers = NULL;

				while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
					$num = count($data);
					//$this->php_debug($num . " fields in line " . $row, TRUE);

					$row++;
					for ($c=0; $c < $num; $c++) {
						if($c == 0)
							$f_tbl_headers .= "( ";
						else
							$f_tbl_headers .= ", ";

						//$this->php_debug($data[$c], TRUE);
						$f_tbl_headers .= "`" . str_replace($delim_data['delim_text'], "", $data[$c]) . "` TEXT NOT NULL";
					}

					$f_tbl_headers .= ")";

					break;
				}

				if(!empty($f_tbl_headers)) {
					$this->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_staging_" . $rand . "` " . $f_tbl_headers . " ENGINE=InnoDB DEFAULT CHARSET=latin1;");
					$this->php_db_query("ALTER TABLE temp_table_staging_" . $rand . " ADD fluid_import_id INT auto_increment primary key NOT NULL;");

					// --> Then alter table and crate a id that is auto incremented.
					$this->php_db_query("LOAD DATA LOCAL INFILE '" . $f_file . "' INTO TABLE temp_table_staging_" . $rand . " FIELDS TERMINATED BY '" . $this->php_escape_string($delim_data['delim']) . "' ENCLOSED BY '" . $this->php_escape_string($delim_data['delim_text']) . "' IGNORE 1 LINES;");

					$this->php_db_query("DROP TABLE IF EXISTS `" . TABLE_IMPORT_STAGING . "`");
					$this->php_db_query("CREATE TABLE `" . TABLE_IMPORT_STAGING . "` AS SELECT * FROM temp_table_staging_" . $rand);
					$this->php_db_query("ALTER TABLE `" . TABLE_IMPORT_STAGING . "` ADD PRIMARY KEY(`fluid_import_id`);");

					$this->php_db_query("DROP TABLE IF EXISTS `temp_table_staging_" . $rand . "`");

					$f_return_msg = "success";
				}

				// --> Build some checks to make sure imports are success.

				//csvsql -d \; -q \" --tables test_table_1233 --snifflimit 1000000 fuji\ csv\ import.csv > table.sql

				$this->php_db_commit();

				fclose($handle);

				// Delete the file.
				if(file_exists($f_file)) {
					unlink($f_file);
				}

				return $f_return_msg;
			}
			else
				return "error opening file -> " . $tmp_files["file"]["tmp_name"];
		}
		catch (Exception $err) {
			return $err;
		}
	}

	public function php_process_file_uploads($tmp_files) {
		unset($this->tmp_files); // Clear out the array.

		$this->tmp_files = $tmp_files;

		if(!file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']))
			mkdir(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']);

		// End can not be a reference, so explode() into a variable first.
		$tmp_file_name = explode(".", $this->tmp_files['file']['name']);
		$this->tmp_files["file"]["extension"] = end($tmp_file_name);

		$this->tmp_files["file"]["rand"] = substr(str_shuffle(md5(time())),0,30);
		$this->tmp_files["file"]["fullpath"] = FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $this->tmp_files["file"]["rand"] . "." . $this->tmp_files["file"]["extension"];
		$this->tmp_files["file"]["image"] = $this->tmp_files["file"]["rand"] . "." . $this->tmp_files["file"]["extension"];

		$this->tmp_files["file"]["noerror"] = move_uploaded_file($this->tmp_files["file"]["tmp_name"], FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $this->tmp_files["file"]["rand"] . "." . $this->tmp_files["file"]["extension"]);

		return $this->tmp_files;
	}

	// Return image size ratios only, does not resize. Used for fluid.listing.php
	public function php_process_image_ratios($image, $modwidth, $modheight) {
		if($image == WWW_FILES . IMG_NO_IMAGE)
			list($width, $height) = getimagesize($image);
		else
			list($width, $height) = getimagesize(FOLDER_IMAGES . str_replace(WWW_IMAGES, "", $image));

		if($width > $height) {
			$diff = $width / $modwidth;
			$modheight = $height / $diff;

			$height=$modheight;
			$width=$modwidth;
		}
		else if($height > $width) {
			$diff = $height / $modheight;
			$modwidth = $width / $diff;

			$width=$modwidth;
			$height=$modheight;
		}
		else {
			$diff = $width / $modwidth;
			$modheight = $height / $diff;
			$height=$modheight;
			$width=$modwidth;
		}

		return Array("height" => $height, "width" => $width);
	}

	public function php_process_image_resize($image, $modwidth, $modheight, $img_name = NULL, $location_override = NULL) {
		// If the image is no longer on the server, we will set the image temporary to NO IMAGE.
		if(!file_exists(FOLDER_ROOT . $image))
			$image = FOLDER_FILES . IMG_NO_IMAGE;

		if($image == WWW_FILES . IMG_NO_IMAGE) {
			$img_tmp = FOLDER_ROOT . WWW_FILES . IMG_NO_IMAGE;
			$img_name = $modwidth . "x" . $modheight . "x" . "_" . IMG_NO_IMAGE;
			list($width, $height, $type) = getimagesize($img_tmp);
		}
		else {
			$img_tmp = FOLDER_ROOT . $image;

			if($img_name == NULL)
				$img_name = $modwidth . "x" . $modheight . "x" . "_" . str_replace(WWW_IMAGES, "", $image);
			else
				$img_name = substr($img_name, 0, 50) . "_" . $modwidth . "x" . $modheight . "_" . str_replace(WWW_IMAGES, "", $image);

			if($location_override == TRUE) {
				$img_name = str_replace("/", "", $img_name);
				list($width, $height, $type) = getimagesize(FOLDER_ROOT . $image);
			}
			else
				list($width, $height, $type) = getimagesize(FOLDER_IMAGES . str_replace(WWW_IMAGES, "", $image));
		}

		$target = $img_name;
		$invalid_img_type = FALSE;

		if(!file_exists(FOLDER_CACHED_IMAGES . $target)) {
			ini_set('memory_limit', 128*1024*1024);

			// Load the image
			switch ($type) {
				case IMAGETYPE_GIF:
					$img_org = imagecreatefromgif($img_tmp);
				break;

				case IMAGETYPE_JPEG:
					$img_org = imagecreatefromjpeg($img_tmp);
				break;

				case IMAGETYPE_PNG:
					$img_org = imagecreatefrompng($img_tmp);
				break;

				default:
					$invalid_img_type = TRUE;
					//die("Invalid image type (#{$type} = " . image_type_to_extension($type) . ")");
			}

			// Reset to the no image if the image is a invalid type.
			if($invalid_img_type == TRUE) {
				$image = FOLDER_FILES . IMG_NO_IMAGE;
				$img_tmp = FOLDER_ROOT . WWW_FILES . IMG_NO_IMAGE;
				$img_name = $modwidth . "x" . $modheight . "x" . "_" . IMG_NO_IMAGE;
				list($width, $height, $type) = getimagesize($img_tmp);

				// Load the image
				switch ($type) {
					case IMAGETYPE_GIF:
						$img_org = imagecreatefromgif($img_tmp);
					break;

					case IMAGETYPE_JPEG:
						$img_org = imagecreatefromjpeg($img_tmp);
					break;

					case IMAGETYPE_PNG:
						$img_org = imagecreatefrompng($img_tmp);
					break;

					default:
						die("Invalid image type (#{$type} = " . image_type_to_extension($type) . ")"); // --> Crap, the no image image is invalid. hmmm.... strange things are a brewing at the circle k.
				}
			}

			// Calculate the new image dimensions.
			if($width > $height) {
				$diff = $width / $modwidth;
				$modheight = $height / $diff;

				$new_height = $modheight;
				$new_width = $modwidth;
			}
			else if($height > $width) {
				$diff = $height / $modheight;
				$modwidth = $width / $diff;

				$new_width = $modwidth;
				$new_height = $modheight;
			}
			else {
				$diff = $width / $modwidth;
				$modheight = $height / $diff;
				$new_height = $modheight;
				$new_width = $modwidth;
			}

			// Coordinates of original image to use
			$x1 = floor($new_width - $width) / 2;
			$y1 = floor($new_height - $height) / 2;

			// Resize the image
			$thumb_image = imagecreatetruecolor($new_width, $new_height);

			// Set the transparency on the new image.
			ImageColorTransparent($thumb_image, ImageColorAllocate($thumb_image, 0, 0, 0));
			ImageAlphaBlending($thumb_image, false);
			ImageSaveAlpha($thumb_image,true);

			// Draw the original image into the new one.
			imagecopyresampled($thumb_image, $img_org, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			// Save the new image
			switch ($type) {
				case IMAGETYPE_GIF:
				  imagegif($thumb_image, FOLDER_CACHED_IMAGES . $target);
				  break;
				case IMAGETYPE_JPEG:
				  imagejpeg($thumb_image, FOLDER_CACHED_IMAGES . $target, 90);
				  break;
				case IMAGETYPE_PNG:
				  imagepng($thumb_image, FOLDER_CACHED_IMAGES . $target);
				  break;
				default:
				  throw new LogicException;
			}

			// Make the new image writable
			chmod(FOLDER_CACHED_IMAGES . $target, 0666);

			// Close the files
			imagedestroy($img_org);
			imagedestroy($thumb_image);
		}
		else
			list($new_width, $new_height, $type) = getimagesize(FOLDER_CACHED_IMAGES . $target);

		return Array("image" => WWW_IMAGES_CACHED . $target, "height" => $new_height, "width" => $new_width);
	}

	// Used when resizing image from a remote server. --> fluid.account.php uses this to resize avatar images from google accounts.
	public function php_process_image_resize_remote($image, $modwidth, $modheight) {
		list($width, $height) = getimagesize($image);

		if($width > $height) {
			$diff = $width / $modwidth;
			$modheight = $height / $diff;
			$height=$modheight;
			$width=$modwidth;
		}
		else if($height > $width) {
			$diff = $height / $modheight;
			$modwidth = $width / $diff;

			$width=$modwidth;
			$height=$modheight;
		}
		else {
			$diff = $width / $modwidth;
			$modheight = $height / $diff;
			$height=$modheight;
			$width=$modwidth;
		}

		return Array("height" => $height, "width" => $width);
	}

	public function php_process_images($data) {
		$array = json_decode(base64_decode($data));
		$image_array = array();

		/*
		$imagecache = new ImageCache();
		$imagecache->cached_image_directory = FOLDER_CACHED_IMAGES;

		foreach($array as $image) {
			$cached_image = $imagecache->cache(FOLDER_IMAGES . $image->file->image);
			$image_array[] = WWW_IMAGES_CACHED . $cached_image;
		}
		*/


		if(!empty($array)) {
			foreach($array as $image) {
				$image_array[] = WWW_IMAGES . $image->file->image;
			}

			if(count($image_array) < 1) {
				$image_array[0] = WWW_FILES . IMG_NO_IMAGE;
				//$image_array['bool_no_image'] = TRUE;
			}
		}
		else {
			$image_array[0] = WWW_FILES . IMG_NO_IMAGE;
		}

		return $image_array;
	}

	public function php_item_available($arrival_date) {
		if(!empty($arrival_date)) {
			if(strtotime($arrival_date) > strtotime(date('Y-m-d H:i:s'))) {
				return FALSE;
			}
			else {
				return TRUE;
			}
		}
		else {
			return TRUE;
		}
	}

	public function php_utf8_decoder_encoder($string) {
		if(preg_match('!!u', $string)) {
			// Encoded in utf8.
			//return utf8_decode($string);
			return $string;
		}
		else {
			// Not a utf8 string
			return $string;
		}
	}
	
	// Updates the stock on component items during a scan.
	public function php_process_component_stock($db_data, $data) {
		if($db_data['p_component'] == TRUE) {
			$this->php_db_begin();

			$this->php_db_query("SELECT * FROM " . TABLE_PRODUCT_COMPONENT . " c, " . TABLE_PRODUCTS . " p WHERE c.cp_master_id = '" . $this->php_escape_string($db_data['p_id']) . "' AND c.cp_p_id = p.p_id");


			if(isset($this->db_array)) {
				$where = "WHERE p_id IN (";
				$c_set = "CASE";
				$c_set_stock = "CASE";
				$c_set_discount_date_end = "CASE";
				$i = 0;
				$db_tmp = $this->db_array;

				foreach($db_tmp as $c_items) {
					if($i != 0) {
						$where .= ", ";
					}

					$where .= $this->php_escape_string($c_items['p_id']);

					$o_data['old_stock'] = $c_items['p_stock'];
					$o_data['old_cost'] = $c_items['p_cost_real'];
					$o_data['old_cost_avg'] = $c_items['p_cost'];
					$n_data['new_cost'] = $c_items['p_cost_real'];
					$n_data['new_stock'] = floor($data['new_stock'] * $c_items['cp_p_stock']);

					$c_items['p_cost'] = $this->php_calculate_cost_average($o_data, $n_data);

					$p_avg_cost = !empty($c_items['p_cost']) ? "'" . $this->php_escape_string($c_items['p_cost']) . "'" : "NULL";

					$c_set .= " WHEN (`p_id`) = ('" . $c_items['p_id'] . "') THEN (" . $p_avg_cost . ")";
					$c_set_stock .= " WHEN (`p_id`) = ('" . $c_items['p_id'] . "') THEN ('" . $this->php_escape_string($n_data['new_stock']) . "')";

					// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
					$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $c_items['p_id'] . "') THEN (p_discount_date_end)";
					if(isset($c_items['p_stock_end']) && isset($n_data['new_stock'])) {
						if($c_items['p_stock_end']  == 1 && $n_data['new_stock'] < 1) {
							$f_date_end = strtotime($c_items['p_discount_date_end']);

							if($f_date_end > strtotime(date("Y-m-d H:i:s"))) {
								$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

								$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $c_items['p_id'] . "') THEN (" . $p_discount_date_end . ")";
							}
							else if(empty($c_items['p_discount_date_end'])) {
								$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

								$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $c_items['p_id'] . "') THEN (" . $p_discount_date_end . ")";
							}
						}
					}

					$c_set_discount_date_end .= $c_set_discount_date_end_tmp;

					$i++;
				}

				$where .= ")";

				$f_update_query = "UPDATE " . TABLE_PRODUCTS . " SET `p_cost` = " . $c_set . " END, `p_discount_date_end` = " . $c_set_discount_date_end . " END, `p_stock` = " . $c_set_stock . " END " . $where;

				$this->php_db_query($f_update_query);

				$this->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('ADMIN: scan update', '" . $this->php_escape_string(serialize(print_r($f_update_query, TRUE))) . "')");
			}

			$this->php_db_commit();
		}
	}

	// Returns the stock of a item. Checks for component items, etc.
	public function php_process_stock($data) {
		try {
			if($data['p_component'] == TRUE) {
				$this->php_db_begin();

				$this->php_db_query("SELECT * FROM " . TABLE_PRODUCT_COMPONENT . " c, " . TABLE_PRODUCTS . " p WHERE c.cp_master_id = '" . $this->php_escape_string($data['p_id']) . "' AND c.cp_p_id = p.p_id");

				$this->php_db_commit();

				if(isset($this->db_array)) {
					$p_stock_array = NULL;
					foreach($this->db_array as $item) {
						if($item['cp_master_id'] != $item['p_id']) {
							$p_stock_array[] = floor($item['p_stock'] / $item['cp_p_stock']);
						}
					}

					sort($p_stock_array);

					if(isset($p_stock_array[0])) {
						if($p_stock_array[0] < 0) {
							$p_stock_array[0] = 0;
						}

						return $p_stock_array[0] + $data['p_stock'];
					}
					else {
						return $data['p_stock'];
					}
				}
				else {
					return $data['p_stock'];
				}
			}
			else {
				return $data['p_stock'];
			}
		}
		catch (Exception $err) {
			return 0;
		}
	}

	public function php_process_stock_status($p_instore, $stock, $p_enable, $arrival_date = NULL, $preorder = NULL, $arrival_type = 0) {
		$return = NULL;

		if(isset($arrival_date)) {
			if(strtotime($arrival_date) > strtotime(date('Y-m-d H:i:s'))) {
				if($arrival_type == 1) {
					$return = "<div>Available: " . date("Y-m-d", strtotime($arrival_date)) . "</div>";
				}
				else {
					$return = "<div>Available: " . date("F Y", strtotime($arrival_date)) . "</div>";
				}
			}
			else {
				if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE) {
					if($stock > 0)
						$return = "<div class='f-stock-listings-font'>Availability: IN STOCK</div>";
					else if($p_enable == 2)
						$return = "<div class='f-call-stock' style='color: #a26500'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Item discontinued</div>";
					else
						$return = "<div class='fa-list-stock' style='color: #b96404;'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Temporarily out of stock.</div><div class='fa-list-stock'><i class='fa fa-truck aria-hidden='true'></i> Usually ships in 5-10 <div class='fa-bus-desktop'>business</div> days.</div>";
				}
				else {
					if($stock > 0)
						$return = "<div style='font-size: 12px;'>Availability: IN STOCK</div>";
					else if($p_enable == 2)
						$return = "<div class='f-call-stock' style='color: #a26500'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Item discontinued</div>";
					else
						$return = "<div class='f-call-stock' style='color: #FFD600;'><a href='tel:+16046855331'><i class='fa fa-phone' aria-hidden='true'></i> Call for availability.</a></div>";
				}
			}
		}
		else {
			if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE) {
				if($stock > 0)
					$return = "<div class='f-stock-listings-font'>Availability: IN STOCK</div>";
				else if($p_enable == 2)
					$return = "<div class='f-call-stock' style='color: #a26500'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Item discontinued</div>";
				else
					$return = "<div class='fa-list-stock' style='color: #b96404;'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Temporarily out of stock.</div><div class='fa-list-stock'><i class='fa fa-truck aria-hidden='true'></i> Usually ships in 5-10 <div class='fa-bus-desktop'>business</div> days.</div>";
			}
			else {
				if($stock > 0)
					$return = "<div class='f-stock-listings-font'>Availability: IN STOCK</div>";
				else if($p_enable == 2)
					$return = "<div class='f-call-stock' style='color: #a26500'><i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Item discontinued</div>";
				else
					$return = "<div class='f-call-stock' style='color: #FF9500'><a href='tel:+16046855331'><i class='fa fa-phone' aria-hidden='true'></i> Call for availability.</a></div>";
			}
		}

		// In store pickup only.
		if($p_instore == 1) {
			$return .= "<div class='f-call-stock'>* In store pickup only.</div>";
		}

		return $return;
	}

	// Remove unnecessary words from the search term and return them as an array
	public function php_filter_search_keys($query, $f_filter_words = TRUE) {
		$query = trim(preg_replace("/(\s+)+/", " ", $query));
		$words = array();

		// Expand this list with words to filter out of the search query.
		if($f_filter_words == TRUE) {
			$list = array("a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "as", "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "could", "did", "do", "does", "doing", "down", "during", "each", "few", "for", "from", "further", "had", "has", "have", "having", "he", "he'd", "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "I", "i", "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "it", "it's", "its", "itself", "let's", "me", "more", "most", "my", "myself", "nor", "of", "on", "once", "only", "or", "other", "ought", "our", "ours", "ourselves", "out", "over", "own", "same", "she", "she'd", "she'll", "she's", "should", "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves", "then", "there", "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too", "under", "until", "us", "up", "very", "was", "we", "we'd", "we'll", "we're", "we've", "were", "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", "why's", "with", "would", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", ";", "$", "%", "&", "!", "(", ")", "*", "^", "@", "`", "~");
		}
		else {
			// Used only in fluid.search.suggestions.php
			$list = array(";", "$", "%", "&", "!", "(", ")", "*", "^", "@", "`", "~");
		}

		//$list = array("in","it","a","the","of","or","I","you","he","me","us","they","she","to","but","that","this","those","then","for",";");
		$c = 0;

		foreach(explode(" ", $query) as $key) {
			if (in_array($key, $list)) {
				continue;
			}

			$words[] = $key;

			if ($c >= 15) {
				break;
			}

			$c++;
		}

		return $words;
	}

	// Calculate the cost average.
	public function php_calculate_cost_average($o_data, $n_data) {
		/*
		$o_data['old_cost'];
		$o_data['old_stock'];
		$o_data['old_cost_avg'];

		$n_data['new_cost'];
		$n_data['new_stock'];
		*/

		// When stock is added
		if($n_data['new_stock'] == 0) {
			$f_average_cost = $n_data['new_cost'];
		}
		else if($n_data['new_stock'] > $o_data['old_stock']) {
			//if($o_data['old_cost_avg'] == 0)
				//$f_average_cost = $n_data['new_cost'];
			//else {
				$stockDiff = $n_data['new_stock'] - $o_data['old_stock'];
				$f_average_cost = (($o_data['old_cost_avg'] * $o_data['old_stock']) + ($n_data['new_cost'] * $stockDiff)) / ($o_data['old_stock'] + $stockDiff);
			//}

		}
		// When stock is subtracted
		else if($n_data['new_stock'] < $o_data['old_stock'] && $n_data['new_stock'] > 0) {
			$stockDiff = abs($o_data['old_stock'] - $n_data['new_stock']);
			$f_average_cost = (($o_data['old_cost_avg'] * $o_data['old_stock']) + (($o_data['old_cost_avg'] * $stockDiff) * -1)) / ($o_data['old_stock'] - $stockDiff);
		}
		else if($n_data['new_stock'] == $o_data['old_stock']) {
			// No change in stock. Cost average doesn't change.
			//if($o_data['old_cost_avg'] == 0)
				//$f_average_cost = $n_data['new_cost'];
			//else
				$f_average_cost = $o_data['old_cost_avg'];
		}
		else {
			 // When stock is now 0
			$f_average_cost = $n_data['new_cost'];
		}

		return $f_average_cost;
	}

	// Limit words number of characters
	public function php_limit_chars($query, $limit = 200) {
		return substr($query, 0, $limit);
	}

	// Cleans a string, removes hypens and special characters. Used for seo links.
	function php_clean_string($f_string) {
		$f_string = str_replace(' ', '-', $f_string);
		$f_string = str_replace('.', '-', $f_string);
		$f_string = str_replace('%', '', $f_string);
		$f_string = str_replace('!', '', $f_string);
		$f_string = str_replace('&', '', $f_string);
		$f_string = str_replace('@', '', $f_string);
		$f_string = str_replace('#', '', $f_string);
		$f_string = str_replace('$', '', $f_string);
		$f_string = str_replace('^', '', $f_string);
		$f_string = str_replace('*', '', $f_string);
		$f_string = str_replace('(', '', $f_string);
		$f_string = str_replace(')', '', $f_string);
		$f_string = str_replace('+', '', $f_string);
		$f_string = str_replace('~', '', $f_string);
		$f_string = str_replace('"', '', $f_string);
		$f_string = str_replace("'", "", $f_string);
		$f_string = str_replace('`', '', $f_string);

		$f_string = preg_replace('/[^A-Za-z0-9_\-]/', '', $f_string);
		//preg_replace("/[^ \w]+/", "", $f_string);
		//preg_replace('/[^A-Za-z0-9_.]/', '', $string);
		return $f_string;
	}

	public function php_send_email($from, $to, $subject, $message) {
		$from = "From: " . $from;

		// --> 1. Insert logo into top of message.
		// --> 2. Insert social media links into bottom of message.
		mail($to, $subject, $message, $from); // send a email.
	}

	public function php_country_name_to_ISO3166($country_name, $language) {
		if (strlen($language) != 2) {
			//Language must be on 2 caracters
			return NULL;
		}

		//Set uppercase if never
		$language = strtoupper($language);

		$countrycode_list = array('AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI', 'KH', 'CM', 'CA', 'CV', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CW', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR', 'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT', 'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV', 'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'NC', 'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO', 'RU', 'RW', 'BL', 'SH', 'KN', 'LC', 'MF', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SX', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'SS', 'ES', 'LK', 'SD', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH', 'YE', 'ZM', 'ZW');
		$ISO3166 = NULL;

		//Loop all country codes
		foreach ($countrycode_list as $countrycode) {
			$locale_cc = locale_get_display_region('-' . $countrycode, $language);
			// Case insensitive
			if (strcasecmp($country_name, $locale_cc) == 0) {
				$ISO3166 = $countrycode;
				break;
			}
		}

		// return NULL if not found or country code
		return $ISO3166;
	}

	// Return the status of a order.
	public function php_fluid_order_status($s_status) {
		$f_status = NULL;

		switch($s_status) {
			case 0:
				$f_status = ORDER_STATUS_ERROR;
				break;
			case 1:
				$f_status = ORDER_STATUS_PROCESSING;
				break;
			case 2:
				$f_status = ORDER_STATUS_SHIPPED;
				break;
			case 3:
				$f_status = ORDER_STATUS_PICKUP;
				break;
			case 4:
				$f_status = ORDER_STATUS_PREORDERED;
				break;
			case 5:
				$f_status = ORDER_REFUND;
				break;
			case 6:
				$f_status = ORDER_CANCELLED;
				break;
			default:
				$f_status = ORDER_STATUS_PROCESSING;
		}

		return $f_status;
	}

	// --> Recursion to turn a multi dimensional array into a singular array.
	public function php_array_flatten($array) {
		if(!is_array($array)) {
			return FALSE;
		}

		$result = array();

		foreach($array as $key => $value) {
			if(is_array($value))
			  $result[$key] = array_merge($result, $this->php_array_flatten($value));
			else
			  $result[$key] = $value;
		}

		return $result;
	}

    public function php_format_array($val) {
        $do_nothing = true;
        $data = NULL;

		$colours = array("Teal","YellowGreen","Tomato","Navy","MidnightBlue","FireBrick","DarkGreen");
        $indent_size = "20";

        // Get string structure
        if(is_array($val)) {
            ob_start();
            print_r($val);
            $val = ob_get_contents();
            ob_end_clean();
        }

        // Color counter
        $current = 0;

        // Split the string into character array
        $array = preg_split('//', $val, -1, PREG_SPLIT_NO_EMPTY);
        foreach($array as $char) {
            if($char == "[")
                if(!$do_nothing)
                    $data .= "</div>";
                else $do_nothing = false;
            if($char == "[")
                $data .= "<div>";
            if($char == ")") {
                $data .= "</div></div>";
                $current--;
            }

            $data .= $char;

            if($char == "(") {
                $data .= "<div class='indent' style='padding-left: {$indent_size}px; color: ".($colours[$current % count($colours)]).";'>";
                $do_nothing = true;
                $current++;
            }
        }

        return $data;
    }

	public function php_pagination($total_pages, $limit, $page, $functionname, $mode, $d_mode = NULL, $f_var_name = "FluidVariables.f_page_num", $f_quantity = FALSE) {
		// Setup page vars for display.
		if ($page == 0)
			$page = 1;								//if no page var is given, default to 1.

		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1; 						//last page minus 1
		$adjacents = 2;

		// Now we apply our rules and draw the pagination object. We're actually saving the code to a variable in case we want to draw it more than once.
		$pagination = "";
		if($lastpage > 1) {
			$pagination .= "<div class=\"fluid-pagination\">";
			//previous button
			if ($page > 1)
				$pagination.= "<div style='display: inline-block;'><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=0; " . $functionname . "('1', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">« <div class='f-pagination-hide'>First</div></a><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $prev . "; " . $functionname . "('" . $prev . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">« <div class='f-pagination-hide'>Previous</div></a></div>";
			else
				$pagination.= "<div style='display: inline-block;'><span class=\"disabled f-pagination-block\">« <div class='f-pagination-hide'>First</div></span><span class=\"disabled f-pagination-block\">« <div class='f-pagination-hide'>Previous</div></span></div>";

			$pagination .= "<div class='f-hide-pagination'>";
			// pages
			// not enough pages to bother breaking it up
			if ($lastpage < 7 + ($adjacents * 2)) {
				for ($counter = 1; $counter <= $lastpage; $counter++) {
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $counter . "; " . $functionname . "('" . $counter . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$counter</a>";
				}
			}
			else if($lastpage > 5 + ($adjacents * 2)) { // enough pages to hide some

				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2)) {
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $counter . "; " . $functionname . "('" . $counter . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$counter</a>";
					}
					$pagination.= "...";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $lpm1 . ";" . $functionname . "('" . $lpm1 . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$lpm1</a>";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $lastpage . ";" . $functionname . "('" . $lastpage . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$lastpage</a>";
				}
				else if($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) { // in middle; hide some front and some back
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=1; " . $functionname . "('1', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">1</a>";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=2; " . $functionname . "('2', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">2</a>";
					$pagination.= "...";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $counter . ";" . $functionname . "('" . $counter . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$counter</a>";
					}
					$pagination.= "...";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $lpm1 . ";" . $functionname . "('" . $lpm1 . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$lpm1</a>";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $lastpage . ";" . $functionname . "('" . $lastpage . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$lastpage</a>";
				}
				else { //close to end; only hide early pages
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=1;" . $functionname . "('1', '" . $mode . "', '" . $d_mode . "');\">1</a>";
					$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=2;" . $functionname . "('2', '" . $mode . "', '" . $d_mode . "');\">2</a>";
					$pagination.= "...";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $counter . ";" . $functionname . "('" . $counter . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\">$counter</a>";
					}
				}
			}
			$pagination .= "</div>";
			// last button
			if ($page < $counter - 1)
				$pagination.= "<div style='display: inline-block;'><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $next . ";" . $functionname . "('" . $next . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\"><div class='f-pagination-hide'>Next</div> »</a>&nbsp;<a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $f_var_name . "=" . $lastpage . ";" . $functionname . "('" . $lastpage . "', '" . $mode . "', '" . $d_mode . "', " . $f_quantity . ");\"><div class='f-pagination-hide'>Last</div> »</a></div>";
			else
				$pagination.= "<div style='display: inline-block;'><span class=\"disabled f-pagination-block\"><div class='f-pagination-hide'>Next</div> »</span><span class=\"disabled f-pagination-block\"><div class='f-pagination-hide'>Last</div> »</span></div>";


			$pagination.= "</div>";

			if($page == $lastpage)
				$f_items_on_page = $total_pages;
			else
				$f_items_on_page = $limit * $page;

			$pagination .= "<div class='f-pagination-footer'>Page " . $page . " of " . $lastpage . " | " . (($limit * $page) - $limit + 1) . " - " . $f_items_on_page . " of " . $total_pages . " items</div>";
		}

		return $pagination;
	}

	// --> Auto versioning of css and js files to force reloads when changes are made.
	function php_fluid_auto_version($f_root, $f_url){
		$path = pathinfo($f_url);

		$ver = '.' .filemtime($f_root . $f_url) . '.';

		$pos = strrpos($path['basename'], '.');

		if(isset($_SESSION['fluid_uri']))
			$f_path = $_SESSION['fluid_uri'] . $path['dirname'] . '/';
		else
			$f_path = $path['dirname'] . '/';

		if($pos !== false)
			return $f_path . substr_replace($path['basename'], $ver, $pos, strlen('.'));
		else
			return $f_path . str_replace('.', $ver, $path['basename']);
	}

	// For getting the 2 letter provincial code based on the postal code. Required by FedEx.
	public function php_fluid_provincial_code($postal_code) {
		switch(strtoupper(substr($postal_code, 0, 1))) {
			case "A": return "NL"; // Newfoundland and Labrador
			case "B": return "NS"; // Nova Scotia
			case "C": return "PE"; // Prince Edward Island
			case "E": return "NB"; // New Brunswick
			case "G": return "QC"; // Eastern Quebec
			case "H": return "QC"; // Metropolitan Montreal
			case "J": return "QC"; // Western Quebec
			case "K": return "ON"; // Eastern Ontario
			case "L": return "ON"; // Central Ontario
			case "M": return "ON"; // Metropolitan Toronto
			case "N": return "ON"; // Southwestern Ontario
			case "P": return "ON"; // Northern Ontario
			case "R": return "MB"; // Manitoba
			case "S": return "SK"; // Saskatchewan
			case "T": return "AB"; // Alberta
			case "V": return "BC"; // British Columbia
			case "X": return "NT,NU"; // Northwest Territories and Nunavut
			case "Y": return "YT"; // Yukon Territory

			default : return "";
		}
	}

	public function php_validate_canada_postal_codes($mValue, $sRegion = '') {
		$mValue = strtolower($mValue);
		$sFirst = substr($mValue, 0, 1);
		$sRegion = strtolower($sRegion);

		$aRegion = array(
		'nl' => 'a',
		'ns' => 'b',
		'pe' => 'c',
		'nb' => 'e',
		'qc' => array('g', 'h', 'j'),
		'on' => array('k', 'l', 'm', 'n', 'p'),
		'mb' => 'r',
		'sk' => 's',
		'ab' => 't',
		'bc' => 'v',
		'nt' => 'x',
		'nu' => 'x',
		'yt' => 'y'
		);

		if (preg_match('/[abceghjlkmnprstvxy]/', $sFirst) && !preg_match('/[dfioqu]/', $mValue) && preg_match('/^\w\d\w[- ]?\d\w\d$/', $mValue)) {
			if (!empty($sRegion) && array_key_exists($sRegion, $aRegion)) {
				if (is_array($aRegion[$sRegion]) && in_array($sFirst, $aRegion[$sRegion]))
					return true;
				else if (is_string($aRegion[$sRegion]) && $sFirst == $aRegion[$sRegion])
					return true;
			}
			else if (empty($sRegion))
			  return true;
		}

	  return false;
	}

	// Get the 2 Letter state abbrevation. FedEx requires a 2 digit state code.
	public function php_fluid_state_abbr($name, $get = 'abbr') {
		//make sure the state name has correct capitalization:
		if($get != 'name') {
			$name = strtolower($name);
			$name = ucwords($name);
		}
		else
			$name = strtoupper($name);

		$states = array(
			'Alabama'=>'AL',
			'Alaska'=>'AK',
			'Arizona'=>'AZ',
			'Arkansas'=>'AR',
			'California'=>'CA',
			'Colorado'=>'CO',
			'Connecticut'=>'CT',
			'Delaware'=>'DE',
			'Florida'=>'FL',
			'Georgia'=>'GA',
			'Hawaii'=>'HI',
			'Idaho'=>'ID',
			'Illinois'=>'IL',
			'Indiana'=>'IN',
			'Iowa'=>'IA',
			'Kansas'=>'KS',
			'Kentucky'=>'KY',
			'Louisiana'=>'LA',
			'Maine'=>'ME',
			'Maryland'=>'MD',
			'Massachusetts'=>'MA',
			'Michigan'=>'MI',
			'Minnesota'=>'MN',
			'Mississippi'=>'MS',
			'Missouri'=>'MO',
			'Montana'=>'MT',
			'Nebraska'=>'NE',
			'Nevada'=>'NV',
			'New Hampshire'=>'NH',
			'New Jersey'=>'NJ',
			'New Mexico'=>'NM',
			'New York'=>'NY',
			'North Carolina'=>'NC',
			'North Dakota'=>'ND',
			'Ohio'=>'OH',
			'Oklahoma'=>'OK',
			'Oregon'=>'OR',
			'Pennsylvania'=>'PA',
			'Rhode Island'=>'RI',
			'South Carolina'=>'SC',
			'South Dakota'=>'SD',
			'Tennessee'=>'TN',
			'Texas'=>'TX',
			'Utah'=>'UT',
			'Vermont'=>'VT',
			'Virginia'=>'VA',
			'Washington'=>'WA',
			'West Virginia'=>'WV',
			'Wisconsin'=>'WI',
			'Wyoming'=>'WY',
			'AL'=>'AL',
			'AK'=>'AK',
			'AZ'=>'AZ',
			'AR'=>'AR',
			'CA'=>'CA',
			'CO'=>'CO',
			'CT'=>'CT',
			'DE'=>'DE',
			'FL'=>'FL',
			'GA'=>'GA',
			'HI'=>'HI',
			'ID'=>'ID',
			'IL'=>'IL',
			'IN'=>'IN',
			'IA'=>'IA',
			'KS'=>'KS',
			'KY'=>'KY',
			'LA'=>'LA',
			'ME'=>'ME',
			'MD'=>'MD',
			'MA'=>'MA',
			'MI'=>'MI',
			'MN'=>'MN',
			'MS'=>'MS',
			'MO'=>'MO',
			'MT'=>'MT',
			'NE'=>'NE',
			'NV'=>'NV',
			'NH'=>'NH',
			'NJ'=>'NJ',
			'NM'=>'NM',
			'NY'=>'NY',
			'NC'=>'NC',
			'ND'=>'ND',
			'OH'=>'OH',
			'OK'=>'OK',
			'OR'=>'OR',
			'PA'=>'PA',
			'RI'=>'RI',
			'SC'=>'SC',
			'SD'=>'SD',
			'TN'=>'TN',
			'TX'=>'TX',
			'UT'=>'UT',
			'VT'=>'VT',
			'VA'=>'VA',
			'WA'=>'WA',
			'WV'=>'WV',
			'WI'=>'WI',
			'WY'=>'WY'
		);

		if($get == 'name') {
		// in this case $name is actually the abbreviation of the state name and you want the full name
			$states = array_flip($states);
		}

		return $states[$name];
	}
}

?>
