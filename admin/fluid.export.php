<?php
// fluid.export.php
// Michael Rajotte - 2017 Novembre

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

if(empty($_SESSION['fluid_admin'])) {
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);
}

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load'])) {
	if(function_exists($_REQUEST['function'])) {
		echo call_user_func($_REQUEST['function']);
	}
	else {
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");
	}
}

function php_fluid_export() {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['f_mode']))
			$f_mode = $_REQUEST['f_mode'];

		if(isset($f_data) && isset($f_mode)) {
			$fluid = new Fluid();
			$fluid->php_db_begin();

			if($f_mode == "stock")
				$where = "WHERE p.p_stock > 0";
			else if($f_mode == "all")
				$where = NULL;
			else {
				$where = "WHERE p.p_id IN (";
				$i = 0;
				foreach($f_data as $item) {
					if($i != 0)
						$where .= ", ";

					$where .= $fluid->php_escape_string($item->p_id);

					$i++;
				}
				$where .= ")";
			}

			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id " . $where);

			$fluid->php_db_commit();

			//$execute_functions[]['function'] = "js_fluid_logs_delete_cleanup";
			//id, title, description, condition, price, availability, link, image link, gtin, mpn, brand, google product category, sale price, sale price effective date, product type,
			$f_csv = NULL;
			$f_delim = ";";
			$i = 0;

			if($f_mode == "google")
				$f_csv[$i] = Array("id", "title", "description", "condition", "price", "availability", "link", "image link", "gtin", "mpn", "brand", "google product category", "sale price", "sale price effective date", "product type");
			else if($f_mode == "stock" || $f_mode == "all")
				$f_csv[$i] = Array("UPC", "MFG Code", "Category", "Manufacturer", "Description", "Stock", "Width (cm)", "Length (cm)", "Height (cm)", "Weight (kg)", "Cost", "Cost Avg", "Price", "Cost Total (stock * cost avg)");
			else
				$f_csv[$i] = Array("p_id", "p_mfgcode", "p_mfg_number", "p_mfgid", "p_catid", "p_category", "p_manufacturer", "p_name", "p_stock", "p_discount_date_start", "p_discount_date_end", "p_width", "p_length", "p_height", "p_weight", "p_cost", "p_cost_real", "p_price", "p_price_discount");

			$i++;
			$f_total_cost = 0;
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $f_item) {
					$f_csv_tmp = Array();

					if($f_mode == "google") {
						$f_csv_tmp[] = $f_item['p_id'];
						$f_csv_tmp[] = $f_item['p_name'];

						$f_details = $f_item['p_details'];
						$f_details = str_replace("<ul>", "", $f_details);
						$f_details = str_replace("<li>", "", $f_details);
						$f_details = str_replace("</li>", ". ", $f_details);
						$f_details = str_replace("</ul>", "", $f_details);
						$f_details = str_replace("\n", "", $f_details);
						$f_details = strip_tags($f_details);

						$f_csv_tmp[] = $f_details;

						$f_csv_tmp[] = "New";

						$f_csv_tmp[] = "CAD " . $f_item['p_price'];

						if($f_item['p_stock'] > 0)
							$f_csv_tmp[] = "In Stock";
						else
							$f_csv_tmp[] = "Out of Stock";

						if(empty($f_item['p_mfgcode']))
							$ft_mfgcode = $f_item['p_id'];
						else
							$ft_mfgcode = $f_item['p_mfgcode'];

						if(empty($f_item['p_name']))
							$ft_name = $f_item['p_id'];
						else
							$ft_name = $f_item['m_name'] . " " . $f_item['p_name'];

						$f_csv_tmp[] = WWW_SITE . FLUID_ITEM_VIEW_REWRITE . "/" . $f_item['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name);

						$p_images = $fluid->php_process_images($f_item['p_images']);
						$f_img_name = str_replace(" ", "_", $f_item['m_name'] . "_" . $f_item['p_name'] . "_" . $f_item['p_mfgcode']);
						$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
						$width_height_l = $fluid->php_process_image_resize($p_images[0], "500", "500", $f_img_name);
						$f_csv_tmp[] = WWW_SITE . $width_height_l['image'];

						$f_csv_tmp[] = (string)$ft_mfgcode;
						$f_csv_tmp[] = (string)$f_item['p_mfg_number'];
						$f_csv_tmp[] = $f_item['m_name'];
						$f_csv_tmp[] = $f_item['c_google_cat_id'];
						$f_csv_tmp[] = "CAD " . $f_item['p_price_discount'];

						if(isset($f_item['p_discount_date_start']))
							$f_date_start = date('c', strtotime($f_item['p_discount_date_start']));
						else
							$f_date_start = NULL;

						if(isset($f_item['p_discount_date_end']))
							$f_date_end = date('c', strtotime($f_item['p_discount_date_end']));
						else
							$f_date_end = NULL;

						$f_csv_tmp[] = $f_date_start . "/" . $f_date_end;

						$f_csv_tmp[] = $f_item['c_name'];

						$f_csv[$i] = $f_csv_tmp;
					}
					else if($f_mode == "stock" || $f_mode == "all") {
						if(empty($f_item['p_mfgcode']))
							$ft_mfgcode = "";
						else
							$ft_mfgcode = $f_item['p_mfgcode'];

						$f_csv_tmp[] = (string)$ft_mfgcode;
						$f_csv_tmp[] = (string)$f_item['p_mfg_number'];
						$f_csv_tmp[] = $f_item['c_name'];
						$f_csv_tmp[] = $f_item['m_name'];
						$f_csv_tmp[] = $f_item['p_name'];

						if($f_item['p_stock'] <= 0) {
							$f_stock_tmp = 0;
							$f_csv_tmp[] = "0";
						}
						else {
							$f_csv_tmp[] = (string)$f_item['p_stock'];
							$f_stock_tmp = $f_item['p_stock'];
						}

						$f_csv_tmp[] = (string)$f_item['p_width'];
						$f_csv_tmp[] = (string)$f_item['p_length'];
						$f_csv_tmp[] = (string)$f_item['p_height'];
						$f_csv_tmp[] = (string)$f_item['p_weight'];
						$f_csv_tmp[] = (string)$f_item['p_cost'];
						$f_csv_tmp[] = (string)$f_item['p_cost_real'];
						$f_csv_tmp[] = (string)$f_item['p_price'];
						$f_cost_total_tmp = $f_stock_tmp * $f_item['p_cost_real'];
						$f_csv_tmp[] = (string)$f_cost_total_tmp;

						$f_total_cost = $f_total_cost + $f_cost_total_tmp;
						$f_csv[$i] = $f_csv_tmp;
					}
					else {
						//Array("p_id", "p_mfgcode", "p_mfg_number", "p_mfgid", "p_catid", "p_category", "p_manufacturer", "p_name", "p_stock", "p_discount_date_start", "p_discount_date_end", "p_width", "p_length", "p_height", "p_weight", "p_cost", "p_cost_real", "p_price", "p_price_discount")

						$f_csv_tmp[] = $f_item['p_id'];
						if(empty($f_item['p_mfgcode']))
							$ft_mfgcode = $f_item['p_id'];
						else
							$ft_mfgcode = $f_item['p_mfgcode'];

						$f_csv_tmp[] = (string)$ft_mfgcode;
						$f_csv_tmp[] = (string)$f_item['p_mfg_number'];
						$f_csv_tmp[] = $f_item['p_mfgid'];
						$f_csv_tmp[] = $f_item['p_catid'];
						$f_csv_tmp[] = $f_item['c_name'];
						$f_csv_tmp[] = $f_item['m_name'];
						$f_csv_tmp[] = $f_item['p_name'];

						if($f_item['p_stock'] <= 0)
							$f_csv_tmp[] = "0";
						else
							$f_csv_tmp[] = (string)$f_item['p_stock'];

						if(isset($f_item['p_discount_date_start']))
							$f_date_start = $f_item['p_discount_date_start'];
						else
							$f_date_start = NULL;

						$f_csv_tmp[] = $f_date_start;

						if(isset($f_item['p_discount_date_end']))
							$f_date_end = $f_item['p_discount_date_end'];
						else
							$f_date_end = NULL;

						$f_csv_tmp[] = $f_date_end;

						$f_csv_tmp[] = (string)$f_item['p_width'];
						$f_csv_tmp[] = (string)$f_item['p_length'];
						$f_csv_tmp[] = (string)$f_item['p_height'];
						$f_csv_tmp[] = (string)$f_item['p_weight'];
						$f_csv_tmp[] = (string)$f_item['p_cost'];
						$f_csv_tmp[] = (string)$f_item['p_cost_real'];
						$f_csv_tmp[] = (string)$f_item['p_price'];
						$f_csv_tmp[] = (string)$f_item['p_price_discount'];

						$f_csv[$i] = $f_csv_tmp;
					}

					$i++;
				}
			}

			if($f_mode == "stock" || $f_mode == "all") {
				$f_csv_tmp = Array("", "", "", "", "", "", "", "", "", "", "", "", "Total Cost: ", (string)$f_total_cost);
				$f_csv[$i] = $f_csv_tmp;
			}

			header("Content-Type: text/csv");
			header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
			header("Content-Transfer-Encoding: binary\n");
			header('Content-Disposition: attachment; filename="downloaded.csv"');
			php_fluid_output_csv($f_csv);

			//return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Error exporting data.");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_output_csv($data) {
  	$output = fopen("php://output", "w");
  	
	foreach ($data as $row) {
    	fputcsv($output, $row); // here you can change delimiter/enclosure
	}
  	
	fclose($output);
}
?>
