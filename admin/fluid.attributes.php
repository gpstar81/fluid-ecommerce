<?php
// fluid.attributes.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load']))
	if(function_exists($_REQUEST['function']))
		echo call_user_func($_REQUEST['function']);
	else
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");


// Sets a attribute change to multiple items at once.
function php_set_attribute() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		// $data_send_obj->mode		<- contains the column to edit in TABLE_PRODUCTS
		// $data_send_obj->data		<- contains the data to insert into the column.
		// $data	<- the selected products to apply the changes to.
		$data = json_decode(base64_decode($_REQUEST['data']));
		if(isset($_REQUEST['f_selection_data'])) {
			$selection = $_REQUEST['f_selection_data'];
		}
		else {
			$selection = $_REQUEST['data'];
		}

		if(isset($_REQUEST['page_num'])) {
			$f_page_num = $_REQUEST['page_num'];
		}
		else {
			$f_page_num = 1;
		}

		if($f_page_num < 1) {
			$f_page_num = 1;
		}

		$data_send_obj = json_decode(base64_decode($_REQUEST['data_send_obj']));
		// $attrib_id = base64_decode($_REQUEST['attrib_id']);

		if(isset($_REQUEST['mode'])) {
			$mode = $_REQUEST['mode'];
		}
		else {
			$mode = NULL;
		}

		$cat_refresh = Array();
		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			$cat_refresh[$product->p_catid] = $product->p_catid;

			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		if($data_send_obj->type == "stock") {
			// Need to get new cost avg since we are changing stock.
			$fluid->php_db_query("SELECT p_id, p_cost, p_cost_real, p_stock, p_stock_end, p_discount_date_end FROM " . TABLE_PRODUCTS . " " . $where);
			if(empty($db_data['p_discount_date_end']))
				$f_new_stock = base64_decode($data_send_obj->data);
			else
				$f_new_stock = base64_decode($data_send_obj->data);

			if(isset($fluid->db_array)) {
				$f_cost = NULL;

				$c_set = "CASE";
				$c_set_date = "CASE";
				foreach($fluid->db_array as $key => $db_data) {
					$o_data['old_stock'] = $db_data['p_stock'];
					$o_data['old_cost'] = $db_data['p_cost_real'];
					$o_data['old_cost_avg'] = $db_data['p_cost'];
					$n_data['new_cost'] = $db_data['p_cost_real'];
					$n_data['new_stock'] = $f_new_stock;

					$fluid->db_array[$key]['p_cost'] = $fluid->php_calculate_cost_average($o_data, $n_data);

					$p_avg_cost = !empty($fluid->db_array[$key]['p_cost']) ? "'" . $fluid->php_escape_string($fluid->db_array[$key]['p_cost']) . "'" : "NULL";

					$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_avg_cost . ")";

					// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
					$c_set_date_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (p_discount_date_end)";
					if(isset($db_data['p_stock_end']) && isset($n_data['new_stock'])) {
						if($db_data['p_stock_end'] == 1 && $n_data['new_stock'] < 1) {
							$f_date_end = strtotime($db_data['p_discount_date_end']);

							if($f_date_end > strtotime(date("Y-m-d H:i:s")) || empty($db_data['p_discount_date_end'])) {
								$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

								$c_set_date_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
							}
						}
					}
					$c_set_date .= $c_set_date_tmp;

				}

				// --> $fluid->db_array[]['p_cost'] is the new average cost.

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_cost` = " . $c_set . " END " . $where);

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = '" . $fluid->php_escape_string($f_new_stock) . "' " . $where);

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_discount_date_end` = " . $c_set_date . " END " . $where);
			}
		}
		else if($data_send_obj->type == "p_stock_end") {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = '" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "' " . $where);

			// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
			$fluid->php_db_query("SELECT p_id, p_stock, p_discount_date_end FROM " . TABLE_PRODUCTS . " " . $where);

			if(isset($fluid->db_array)) {
				$c_set = "CASE";

				foreach($fluid->db_array as $key => $db_data) {
					$c_set_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (p_discount_date_end)";

					if(isset($db_data['p_stock']) && isset($db_data['p_discount_date_end'])) {
						if(base64_decode($data_send_obj->data) == 1 && $db_data['p_stock'] < 1) {
							$f_date_end = strtotime($db_data['p_discount_date_end']);

							if($f_date_end > strtotime(date("Y-m-d H:i:s"))) {
								$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

								$c_set_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
							}
						}
					}

					$c_set .= $c_set_tmp;
				}

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_discount_date_end` = " . $c_set . " END " . $where);
			}
		}
		else if($data_send_obj->type == "discount_date_end") {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = '" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "' " . $where);

			// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
			$fluid->php_db_query("SELECT p_id, p_stock, p_stock_end, p_discount_date_end FROM " . TABLE_PRODUCTS . " " . $where);

			if(isset($fluid->db_array)) {
				$c_set = "CASE";
				foreach($fluid->db_array as $key => $db_data) {
					$c_set_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (p_discount_date_end)";

					if(isset($db_data['p_stock_end']) && isset($db_data['p_stock'])) {
						if($db_data['p_stock_end'] == 1 && $db_data['p_stock'] < 1) {
							$f_date_end = strtotime($db_data['p_discount_date_end']);

							if($f_date_end > strtotime(date("Y-m-d H:i:s")) || empty($db_data['p_discount_date_end'])) {
								$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

								$c_set_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
							}
						}
					}

					$c_set .= $c_set_tmp;
				}

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_discount_date_end` = " . $c_set . " END " . $where);
			}
		}
		else if($data_send_obj->type == "product_keywords_create") {
			$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_mfgcode, p.p_mfg_number, p.p_keywords, m.m_name, c.c_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id " . $where);

			if(isset($fluid->db_array)) {
				$c_set = "CASE";

				foreach($fluid->db_array as $key => $db_data) {
					$f_keywords_explode = $fluid->php_filter_search_keys($db_data['p_name']);
					$f_keywords_tmp = NULL;
					$i = 0;
					foreach($f_keywords_explode as $f_key) {
						if($i > 0)
							$f_keywords_tmp .= ";";

						$f_keywords_tmp .= $f_key;
						$i++;
					}

					$f_name_tmp = $fluid->php_filter_search_keys($db_data['m_name'] . " " . $db_data['p_name']);
					$i = 0;
					$f_name = NULL;
					foreach($f_name_tmp as $f_key_tmp) {
						if($i > 0)
							$f_name .= " ";

						$f_name .= $f_key_tmp;

						$i++;
					}

					$f_cat_name_tmp = $fluid->php_filter_search_keys($db_data['c_name']);
					$i = 0;
					$f_cat_name = NULL;
					foreach($f_cat_name_tmp as $f_key_tmp) {
						if($i > 0)
							$f_cat_name .= " ";

						$f_cat_name .= $f_key_tmp;

						$i++;
					}

					$f_man_name_tmp = $fluid->php_filter_search_keys($db_data['m_name']);
					$i = 0;
					$f_man_name = NULL;
					foreach($f_man_name_tmp as $f_key_tmp) {
						if($i > 0)
							$f_man_name .= " ";

						$f_man_name .= $f_key_tmp;

						$i++;
					}

					$f_keywords_array = $fluid->php_filter_search_keys($db_data['p_mfgcode'] . ";" . $db_data['p_mfg_number']);

					$f_keywords = $f_man_name . ";" . $f_cat_name . ";" . $f_name . ";";
					foreach($f_keywords_array as $f_key_tmp)
						$f_keywords .= $f_key_tmp;

					$f_keywords .= ";" . $f_keywords_tmp;

					$f_keywords = str_replace('%', '', $f_keywords);
					$f_keywords = str_replace('!', '', $f_keywords);
					$f_keywords = str_replace('&', '', $f_keywords);
					$f_keywords = str_replace('@', '', $f_keywords);
					$f_keywords = str_replace('$', '', $f_keywords);
					$f_keywords = str_replace('^', '', $f_keywords);
					$f_keywords = str_replace('*', '', $f_keywords);
					$f_keywords = str_replace('(', '', $f_keywords);
					$f_keywords = str_replace(')', '', $f_keywords);
					$f_keywords = str_replace('+', '', $f_keywords);
					$f_keywords = str_replace('~', '', $f_keywords);
					$f_keywords = str_replace('"', '', $f_keywords);
					$f_keywords = str_replace("'", "", $f_keywords);
					$f_keywords = str_replace('`', '', $f_keywords);

					switch (base64_decode($data_send_obj->data)) {
						case 0: // --> Overwrite existing keywords.
							$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN ('" . $fluid->php_escape_string($f_keywords) . "')";
						break;

						case 1: // --> Merges generated keywords into the old keywords. It will however search for old generated keywords and not duplicate them.
							$f_keywords_strip = str_replace($f_keywords, "", $db_data['p_keywords']);

							$f_keywords .= $f_keywords_strip;

							$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN ('" . $fluid->php_escape_string($f_keywords) . "')";
						break;
					}
				}

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_keywords` = " . $c_set . " END " . $where);
			}

		}
		else if($data_send_obj->type == "namenum-merge") {
			$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_mfgcode, p.p_mfg_number, m.m_name, c.c_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id " . $where);

			if(isset($fluid->db_array)) {
				$c_set = "CASE";

				foreach($fluid->db_array as $key => $db_data) {
					$p_name = trim($db_data['p_name']);
					$p_mfg_number = trim($db_data['p_mfg_number']);

					switch (base64_decode($data_send_obj->data)) {
						case 0: // --> Remove .
							$p_name = str_ireplace($p_mfg_number, "", $p_name);

							$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN ('" . $fluid->php_escape_string($p_name) . "')";
						break;

						case 1: // --> Merges the mfg number into the p_name.
							// --> Need a strict comparison for FALSE strpos() will return the strpos, and the first position is 0 on a string which is FALSE.
							if(stripos($p_name, $p_mfg_number) === FALSE)
								$p_name = $p_mfg_number . " " . $p_name;

							$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN ('" . $fluid->php_escape_string($p_name) . "')";
						break;
					}
				}

				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_name` = " . $c_set . " END " . $where);
			}
		}
		else if($data_send_obj->type == "p_cost_reset") {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = p_cost_real " . $where);
		}
		else if($data_send_obj->type == "floor_plus") {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = FLOOR(" . base64_decode($data_send_obj->mode) . ") + '" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "' " . $where);
		}
		else if($data_send_obj->type == "floor_minus") {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = FLOOR(" . base64_decode($data_send_obj->mode) . ") - '" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "' " . $where);
		}
		else if($data_send_obj->type == "product_keywords_merge") {		
			//update tablename set col1name = concat(ifnull(col1name,""), 'a,b,c');
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = concat(ifnull(" . base64_decode($data_send_obj->mode) . ", ''), ';" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "') " . $where);
		}
		else {
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET " . base64_decode($data_send_obj->mode) . " = '" . $fluid->php_escape_string(base64_decode($data_send_obj->data)) . "' " . $where);
		}

		$fluid->php_db_commit();

		$execute_functions[0]['function'] = "js_modal_hide";
		$execute_functions[0]['data'] = base64_encode(json_encode("#fluid-modal"));

		if($mode == "items") {
			if(isset($data_send_obj->f_search_input))
				if($data_send_obj->f_search_input != '')
					$f_search_data_input = base64_encode($data_send_obj->f_search_input);
		}

		if(isset($f_search_data_input)) {
			$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $selection . "&page_num=" . $f_page_num . "&mode=" . $mode . "&f_search_data=" . $f_search_data_input)));
		}
		else {
			$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $selection . "&page_num=" . $f_page_num . "&mode=" . $mode)));
		}

		$execute_functions[1]['function'] = "js_fluid_ajax";
		$execute_functions[1]['data'] = base64_encode(json_encode($temp_data));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));

	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_set_attribute() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		// p_enable, p_stock, p_price, p_price_discount, p_discount_date_end, p_newarrivalenddate, p_buyqty
		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = "items";

		$html_status = "<div class=\"input-group\">";
		$html_status .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Status</div></span>";
			$html_status .= "<select id='product-status' class=\"form-control selectpicker show-menu-arrow show-tick\"  data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_status .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
				$html_status .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</option>";

				$html_status .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
				$html_status .= "<span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</option>";

				$html_status .= "<option value='2' style='background: #5BC0DE; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</span>\">";
				$html_status .= "<span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</option>";

			$html_status .= "</select>";
		$html_status .= "</div>";

		$html_zero_status = "<div class=\"input-group\">";
		$html_zero_status .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Zero Status</div></span>";
			$html_zero_status .= "<select id='product-zero-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_zero_status .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
				$html_zero_status .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</option>";

				$html_zero_status .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
				$html_zero_status .= "<span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</option>";

				$html_zero_status .= "<option value='2' style='background: #5BC0DE; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</span>\">";
				$html_zero_status .= "<span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</option>";

			$html_zero_status .= "</select>";
		$html_zero_status .= "</div>";

		$html_stock = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Stock</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Leave blank to set stock to zero.\" aria-describedby=\"basic-addon1\" id='product-stock'>
			</div>";

		$html_price = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Price</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Leave blank to set price to zero.\" aria-describedby=\"basic-addon1\" id='product-price'>
			</div>";

		$html_cost_reset = "<div class=\"input-group\">";
		$html_cost_reset .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:130px !important;'>Reset Cost Avg.</div></span>";
			$html_cost_reset .= "<select id='product-cost-reset' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_cost_reset .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_cost_reset .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

			$html_cost_reset .= "</select>";
		$html_cost_reset .= "</div>";
		$html_cost_reset .= "<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> Reset the cost average of a item to it's current cost.</div>";

		$html_cost = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Cost</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Leave blank to set cost to zero.\" aria-describedby=\"basic-addon1\" id='product-cost'>
			</div>";

		$html_price_discount = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Price</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Leave blank for no discount.\" aria-describedby=\"basic-addon1\" id='product-price-discount'
			</div>";

		$html_date_end_discount = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Discount End</div></span>
			  <div class='input-group date' id='datetimepicker'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-discount-price-end-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
			</div>";

		$html_date_start_discount = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Discount Start</div></span>
			  <div class='input-group date' id='datetimepicker-start'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-discount-price-start-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
			</div>";

		$html_date_end_arrival = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:100px !important;'>Arrival End</div></span>
			  <div class='input-group date' id='datetimepicker-arrival'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-arrival-end-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
			</div>";

		$html_buy_qty = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Max buy qty.</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Max buy qty.\" aria-describedby=\"basic-addon1\" id='product-buyqty' value='1'>
			</div>";

		$html_length = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Length</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Length (cm).\" aria-describedby=\"basic-addon1\" id='product-length'>
			</div>";

		$html_width = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Width</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Width (cm).\" aria-describedby=\"basic-addon1\" id='product-width'>
			</div>";

		$html_height = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Height</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Height (cm).\" aria-describedby=\"basic-addon1\" id='product-height'>
			</div>";

		$html_weight = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Weight</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Weight (kg).\" aria-describedby=\"basic-addon1\" id='product-weight-attrib'>
			</div>";

		$html_trending = "<div class=\"input-group\">";
		$html_trending .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Trending</div></span>";
			$html_trending .= "<select id='product-trending' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_trending .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_trending .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_trending .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_trending .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_trending .= "</select>";
		$html_trending .= "</div>";

		$html_preorder = "<div class=\"input-group\">";
		$html_preorder .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Allow Preorders</div></span>";
			$html_preorder .= "<select id='product-preorder' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_preorder .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_preorder .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_preorder .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_preorder .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_preorder .= "</select>";
		$html_preorder .= "</div>";

		$html_rental = "<div class=\"input-group\">";
		$html_rental .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Rental Flag</div></span>";
			$html_rental .= "<select id='product-rental' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_rental .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_rental .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_rental .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_rental .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_rental .= "</select>";
		$html_rental .= "</div>";

		$html_special_order = "<div class=\"input-group\">";
		$html_special_order .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Special Order</div></span>";
			$html_special_order .= "<select id='product-special-order' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_special_order .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_special_order .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_special_order .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_special_order .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_special_order .= "</select>";
		$html_special_order .= "</div>";

		$html_namenum = "<div class=\"input-group\">";
		$html_namenum .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:130px !important;'>Link Name/Mfg #</div></span>";
			$html_namenum .= "<select id='product-namenum' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_namenum .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_namenum .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_namenum .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_namenum .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_namenum .= "</select>";
		$html_namenum .= "</div>";
		$html_namenum .= "<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This will generate link the mfg number to the item name when displayed. Use the Merge Name/Mfg # attribute if you want to merge the mfg number into the name instead. Merging will actually change the item description, while linking does not.</div>";

		$html_rebate_claim = "<div class=\"input-group\">";
		$html_rebate_claim .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Rebate / Claim</div></span>";
			$html_rebate_claim .= "<select id='product-rebate-claim' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_rebate_claim .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_rebate_claim .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_rebate_claim .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_rebate_claim .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_rebate_claim .= "</select>";
		$html_rebate_claim .= "</div>";
		
		$html_component = "<div class=\"input-group\">";
		$html_component .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Component Item</div></span>";
			$html_component .= "<select id='product-component' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_component .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_component .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_component .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_component .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_component .= "</select>";
		$html_component .= "</div>";

		$html_stock_end = "<div class=\"input-group\">";
		$html_stock_end .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:5px; height: 20px; width:100px !important; font-size: 88%;'>End at 0 stock</div></span>";
			$html_stock_end .= "<select id='product-stock-end' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_stock_end .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_stock_end .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_stock_end .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_stock_end .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_stock_end .= "</select>";
		$html_stock_end .= "</div>";
		$html_stock_end .= "<div style='display: inline-block; padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> When stock reaches zero, the discount will end.</div>";

		$html_showalways = "<div class=\"input-group\">";
		$html_showalways .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Override Listing</div></span>";
			$html_showalways .= "<select id='product-alwaysshow' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_showalways .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_showalways .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_showalways .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_showalways .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_showalways .= "</select>";
		$html_showalways .= "</div>";

		$html_instore = "<div class=\"input-group\">";
		$html_instore .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Shipping Methods</div></span>";
			$html_instore .= "<select id='product-instore' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_instore .= "<option value='0' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Ship & Pickup</span>\"";
				$html_instore .= "><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Ship & Pickup</option>";

				$html_instore .= "<option value='1' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Pickup only</span>\">";
				$html_instore .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Pickup only</option>";

				$html_instore .= "<option value='2' style='background: #4F8CD9; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Shipped only</span>\">";
				$html_instore .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Shipped only</option>";
			$html_instore .= "</select>";
		$html_instore .= "</div>";

		$html_arrivaltype = "<div class=\"input-group\">";
		$html_arrivaltype .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Arrival Type</div></span>";
			$html_arrivaltype .= "<select id='product-arrivaltype' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_arrivaltype .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-time' aria-hidden='true'></span> Display as date</span>\"";
				$html_arrivaltype .= "><span class='glyphicon glyphicon-time' aria-hidden='true'></span> Display as date</option>";

				$html_arrivaltype .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Display as estimated date</span>\">";
				$html_arrivaltype .= "<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Display as estimated date</option>";
			$html_arrivaltype .= "</select>";
		$html_arrivaltype .= "</div>";

		$html_round_plus_price = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Floor Price +</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Example: 0.99\" aria-describedby=\"basic-addon1\" id='product-floor-plus'>
			</div>
			<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This performs a FLOOR operation and adds your input number. Example: Enter 0.99 to end in 99, or 0.49 to end in .49. Leave blank to end prices in .00, or enter whole numbers such as 2.99 to increase by 2 and end in .99. If your price is 499.99 and you enter 1.99, the new price will be 500.99. If you enter 2.99, the new price will be 501.99 (FLOOR(499.99) + 2.99) = 501.99</div>
			";

		$html_round_minus_price = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Floor Price -</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"Example: 0.99\" aria-describedby=\"basic-addon1\" id='product-floor-minus'>
			</div>
			<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This performs a FLOOR operation and minuses your input number. Example: Enter 0.01 to end in .99, or 0.51 to end in .49. Leave blank to end prices in .00, or enter whole numbers such as 2.01 to decrease by 2 and end in .99. If your price is 499.99 and you enter 1.01, the new price will be 497.99. If you enter 2.01, the new price will be 496.99 (FLOOR(499.99) - 2.01) = 496.99</div>
			";

		$html_keywords = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Keywords</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"keywords.\" aria-describedby=\"basic-addon1\" id='product-keywords'>
			</div>";

		$html_keyword_generator = "<div class=\"input-group\">";
		$html_keyword_generator .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:130px !important;'>Keyword Generator</div></span>";
			$html_keyword_generator .= "<select id='product-keywords-create' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_keyword_generator .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Merge into existing</span>\"";
				$html_keyword_generator .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Merge into existing</option>";

				$html_keyword_generator .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Overwrite old keywords</span>\">";
				$html_keyword_generator .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Overwrite old keywords</option>";
			$html_keyword_generator .= "</select>";
		$html_keyword_generator .= "</div>";
		$html_keyword_generator .= "<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This will generate new keywords based on the item name, mfg code and mfg number. You have the option to append onto the existing keywords for items or overwrite and replace the old keywords.</div>";
		
		$html_keywords_merge = "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:110px !important;'>Keywords Merge</div></span>
			  <input style='width:50%;' type=\"text\" class=\"form-control\" placeholder=\"keywords.\" aria-describedby=\"basic-addon1\" id='product-keywords-merge'>
			</div>";
		$html_keywords_merge .= "<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This merge your new keywords into the existing ones for the selected items. It is recommended to separate your keywords with a ; (The system will automatically separate the old keywords with the new ones with ; delimiter).</div>";

		$html_namenum_merge = "<div class=\"input-group\">";
		$html_namenum_merge .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:130px !important;'>Merge Name/Mfg #</div></span>";
			$html_namenum_merge .= "<select id='product-namenum-merge' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_namenum_merge .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
				$html_namenum_merge .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

				$html_namenum_merge .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
				$html_namenum_merge .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";
			$html_namenum_merge .= "</select>";
		$html_namenum_merge .= "</div>";
		$html_namenum_merge .= "<div style=\"padding-top: 10px; padding-left: 3px; font-size: 80%;\"><span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> This will merge the mfg number to the item name. Use the Link Name/Mfg # attribute if you want to link the mfg number into the name instead of merging it. Merging it will actually change the item name description, linking does not.</div>";

		$html_freeship = "<div class=\"input-group\">";
		$html_freeship .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Allow free shipping</div></span>";
			$html_freeship .= "<select id='product-freeship' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html_freeship .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Allow free shipping</span>\"";
				$html_freeship .= "><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Allow free shipping</option>";

				$html_freeship .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No free shipping</span>\">";
				$html_freeship .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No free shipping</option>";

			$html_freeship .= "</select>";
		$html_freeship .= "</div>";

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Quick change item attributes</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
					<div>
							<div style='margin-left:10px; margin-right: 10px;'>";
							$modal .= "<div class=\"input-group\">";
								$modal .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Attribute select:</div></span>";

									$modal .= "<select id='attribute-select' class=\"form-control selectpicker show-menu-arrow show-tick\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='document.getElementById(\"div-attribute-html\").innerHTML = Base64.decode(document.getElementById(\"div-attribute-html-\" + this.options[this.selectedIndex].value).innerHTML); if(this.options[this.selectedIndex].value == 4)js_load_datetime_picker(\"datetimepicker\"); if(this.options[this.selectedIndex].value == 5)js_load_datetime_picker(\"datetimepicker-arrival\"); if(this.options[this.selectedIndex].value == 13)js_load_datetime_picker(\"datetimepicker-start\");$(\"select\").selectpicker();'>";

										$modal .= "<option value='0' data-content=\"<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Status\"";
										$modal .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Status</option>";

										$modal .= "<option value='1' data-content=\"<span class='glyphicon glyphicon-list-alt' aria-hidden='true'></span> Stock\">";
										$modal .= "<span class='glyphicon glyphicon-list-alt' aria-hidden='true'></span> Stock</option>";

										$modal .= "<option value='2' data-content=\"<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Price\">";
										$modal .= "<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Price</option>";

										$modal .= "<option value='3' data-content=\"<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Price Discount\">";
										$modal .= "<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Price Discount</option>";

										$modal .= "<option value='4' data-content=\"<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Discount Date End\">";
										$modal .= "<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Discount Date End</option>";

										$modal .= "<option value='5' data-content=\"<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> New Arrival Date End\">";
										$modal .= "<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> New Arrival Date End</option>";

										$modal .= "<option value='6' data-content=\"<span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Buy Qty\">";
										$modal .= "<span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Buy Qty</option>";

										$modal .= "<option value='7' data-content=\"<span class='glyphicon glyphicon-resize-horizontal' aria-hidden='true'></span> Length\">";
										$modal .= "<span class='glyphicon glyphicon-resize-horizontal' aria-hidden='true'></span> Length</option>";

										$modal .= "<option value='8' data-content=\"<span class='glyphicon glyphicon-retweet' aria-hidden='true'></span> Width\">";
										$modal .= "<span class='glyphicon glyphicon-retweet' aria-hidden='true'></span> Width</option>";

										$modal .= "<option value='9' data-content=\"<span class='glyphicon glyphicon-resize-vertical' aria-hidden='true'></span> Height\">";
										$modal .= "<span class='glyphicon glyphicon-resize-vertical' aria-hidden='true'></span> Height</option>";

										$modal .= "<option value='10' data-content=\"<span class='fa fa-balance-scale' aria-hidden='true'></span> Weight\">";
										$modal .= "<span class='fa fa-balance-scale' aria-hidden='true'></span> Weight</option>";

										$modal .= "<option value='11' data-content=\"<span class='glyphicon glyphicon-fire' aria-hidden='true'></span> Trending\">";
										$modal .= "<span class='glyphicon glyphicon-fire' aria-hidden='true'></span> Trending</option>";

										$modal .= "<option value='12' data-content=\"<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Cost\">";
										$modal .= "<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Cost</option>";

										$modal .= "<option value='13' data-content=\"<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Discount Date Start\">";
										$modal .= "<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Discount Date Start</option>";

										$modal .= "<option value='14' data-content=\"<span class='glyphicon glyphicon-gift' aria-hidden='true'></span> Allow preorders\">";
										$modal .= "<span class='glyphicon glyphicon-gift' aria-hidden='true'></span> Allow preorders</option>";
										$modal .= "<option value='15' data-content=\"<span class='glyphicon glyphicon-usd' aria-hidden='true'></span> Rebate / Claim\">";
										$modal .= "<span class='glyphicon glyphicon-usd' aria-hidden='true'></span> Rebate / Claim</option>";

										$modal .= "<option value='16' data-content=\"<span class='glyphicon glyphicon-flash' aria-hidden='true'></span> Override Listing\">";
										$modal .= "<span class='glyphicon glyphicon-flash' aria-hidden='true'></span> Override Listing</option>";

										$modal .= "<option value='17' data-content=\"<span class='glyphicon glyphicon-paperclip' aria-hidden='true'></span> Link Name/Mfg #\">";
										$modal .= "<span class='glyphicon glyphicon-flash' aria-hidden='true'></span> Link Name/Mfg #</option>";

										$modal .= "<option value='18' data-content=\"<span class='glyphicon glyphicon-repeat' aria-hidden='true'></span> Floor Price +\">";
										$modal .= "<span class='glyphicon glyphicon-repeat' aria-hidden='true'></span> Floor Price +</option>";

										$modal .= "<option value='19' data-content=\"<span class='glyphicon glyphicon-repeat' aria-hidden='true'></span> Floor Price -\">";
										$modal .= "<span class='glyphicon glyphicon-repeat' aria-hidden='true'></span> Floor Price -</option>";

										$modal .= "<option value='20' data-content=\"<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span> Keywords\">";
										$modal .= "<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span> Keywords</option>";

										$modal .= "<option value='21' data-content=\"<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Keyword Generator\">";
										$modal .= "<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Keyword Generator</option>";
										$modal .= "<option value='22' data-content=\"<span class='glyphicon glyphicon-resize-small' aria-hidden='true'></span> Merge Name/Mfg #\">";
										$modal .= "<span class='glyphicon glyphicon-resize-small' aria-hidden='true'></span> Merge Name/Mfg #</option>";

										$modal .= "<option value='23' data-content=\"<span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> End discount at zero stock\">";
										$modal .= "<span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> End discount at zero stock</option>";

										$modal .= "<option value='24' data-content=\"<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Reset Cost Avg.\">";
										$modal .= "<span class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></span> Reset Cost Avg.</option>";

										$modal .= "<option value='25' data-content=\"<span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> Shipping Methods\">";
										$modal .= "<span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> Shipping Methods</option>";

										$modal .= "<option value='26' data-content=\"<span class='glyphicon glyphicon-gift' aria-hidden='true'></span> Rental Flag\">";
										$modal .= "<span class='glyphicon glyphicon-gift' aria-hidden='true'></span> Rental Flag</option>";

										$modal .= "<option value='27' data-content=\"<span class='glyphicon glyphicon-star' aria-hidden='true'></span> Special Order\">";
										$modal .= "<span class='glyphicon glyphicon-star' aria-hidden='true'></span> Special Order</option>";

										$modal .= "<option value='28' data-content=\"<span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Zero Status\"";
										$modal .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Zero Status</option>";

										$modal .= "<option value='29' data-content=\"<span class='glyphicon glyphicon-plane' aria-hidden='true'></span> Allow free shipping\"";
										$modal .= "><span class='glyphicon glyphicon-plane' aria-hidden='true'></span> Allow free shipping</option>";

										$modal .= "<option value='30' data-content=\"<span class='glyphicon glyphicon-time' aria-hidden='true'></span> Arrival Type\"";
										$modal .= "><span class='glyphicon glyphicon-time' aria-hidden='true'></span> Arrival Type</option>";

										$modal .= "<option value='31' data-content=\"<span class='glyphicon glyphicon-magnet' aria-hidden='true'></span> Keywords Merge\">";
										$modal .= "<span class='glyphicon glyphicon-magnet' aria-hidden='true'></span> Keywords Merge</option>";
										
										$modal .= "<option value='32' data-content=\"<span class='glyphicon glyphicon-compressed' aria-hidden='true'></span> Component Item\">";
										$modal .= "<span class='glyphicon glyphicon-magnet' aria-hidden='true'></span> Component Item</option>";
										
									$modal .= "</select>";
								$modal .= "</div>";
							$modal .= "</div>
					</div>";

					$modal .= "<div id='div-attribute-html-0' style='display:none;'>" . base64_encode($html_status) . "</div>";
					$modal .= "<div id='div-attribute-html-1' style='display:none;'>" . base64_encode($html_stock) . "</div>";
					$modal .= "<div id='div-attribute-html-2' style='display:none;'>" . base64_encode($html_price) . "</div>";
					$modal .= "<div id='div-attribute-html-3' style='display:none;'>" . base64_encode($html_price_discount) . "</div>";
					$modal .= "<div id='div-attribute-html-4' style='display:none;'>" . base64_encode($html_date_end_discount) . "</div>";
					$modal .= "<div id='div-attribute-html-5' style='display:none;'>" . base64_encode($html_date_end_arrival) . "</div>";
					$modal .= "<div id='div-attribute-html-6' style='display:none;'>" . base64_encode($html_buy_qty) . "</div>";
					$modal .= "<div id='div-attribute-html-7' style='display:none;'>" . base64_encode($html_length) . "</div>";
					$modal .= "<div id='div-attribute-html-8' style='display:none;'>" . base64_encode($html_width) . "</div>";
					$modal .= "<div id='div-attribute-html-9' style='display:none;'>" . base64_encode($html_height) . "</div>";
					$modal .= "<div id='div-attribute-html-10' style='display:none;'>" . base64_encode($html_weight) . "</div>";
					$modal .= "<div id='div-attribute-html-11' style='display:none;'>" . base64_encode($html_trending) . "</div>";
					$modal .= "<div id='div-attribute-html-12' style='display:none;'>" . base64_encode($html_cost) . "</div>";
					$modal .= "<div id='div-attribute-html-13' style='display:none;'>" . base64_encode($html_date_start_discount) . "</div>";
					$modal .= "<div id='div-attribute-html-14' style='display:none;'>" . base64_encode($html_preorder) . "</div>";
					$modal .= "<div id='div-attribute-html-15' style='display:none;'>" . base64_encode($html_rebate_claim) . "</div>";
					$modal .= "<div id='div-attribute-html-16' style='display:none;'>" . base64_encode($html_showalways) . "</div>";
					$modal .= "<div id='div-attribute-html-17' style='display:none;'>" . base64_encode($html_namenum) . "</div>";
					$modal .= "<div id='div-attribute-html-18' style='display:none;'>" . base64_encode($html_round_plus_price) . "</div>";
					$modal .= "<div id='div-attribute-html-19' style='display:none;'>" . base64_encode($html_round_minus_price) . "</div>";
					$modal .= "<div id='div-attribute-html-20' style='display:none;'>" . base64_encode($html_keywords) . "</div>";
					$modal .= "<div id='div-attribute-html-21' style='display:none;'>" . base64_encode($html_keyword_generator) . "</div>";
					$modal .= "<div id='div-attribute-html-22' style='display:none;'>" . base64_encode($html_namenum_merge) . "</div>";
					$modal .= "<div id='div-attribute-html-23' style='display:none;'>" . base64_encode($html_stock_end) . "</div>";
					$modal .= "<div id='div-attribute-html-24' style='display:none;'>" . base64_encode($html_cost_reset) . "</div>";
					$modal .= "<div id='div-attribute-html-25' style='display:none;'>" . base64_encode($html_instore) . "</div>";
					$modal .= "<div id='div-attribute-html-26' style='display:none;'>" . base64_encode($html_rental) . "</div>";
					$modal .= "<div id='div-attribute-html-27' style='display:none;'>" . base64_encode($html_special_order) . "</div>";
					$modal .= "<div id='div-attribute-html-28' style='display:none;'>" . base64_encode($html_zero_status) . "</div>";
					$modal .= "<div id='div-attribute-html-29' style='display:none;'>" . base64_encode($html_freeship) . "</div>";
					$modal .= "<div id='div-attribute-html-30' style='display:none;'>" . base64_encode($html_arrivaltype) . "</div>";
					$modal .= "<div id='div-attribute-html-31' style='display:none;'>" . base64_encode($html_keywords_merge) . "</div>";
					$modal .= "<div id='div-attribute-html-32' style='display:none;'>" . base64_encode($html_component) . "</div>";

					$modal .= "<div id='div-attribute-html' class='well' style='margin-right:10px; margin-left:10px; margin-top:10px; margin-bottom:10px;'>";
					$modal .= $html_status;
					$modal .= "</div>
				</div>
			  </div>";

		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

			$selection_output = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";

			foreach($fluid->db_array as $value) {
				// Process the image.
				$p_images = $fluid->php_process_images($value['p_images']);
				$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

				$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

				$selection_output .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";
			}

			$selection_output .= "</div>";

		$selection_output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-warning' role='alert'>Are you sure you want to make changes to the selected items?</div>" . $selection_output);
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\" onClick='js_set_attribute(\"" . $mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

			  $modal .= "<div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>
				<div style='float:right;'><button type='button' class='btn btn-primary' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			  </div>

			</div>
		  </div>";

		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));

	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}
?>
