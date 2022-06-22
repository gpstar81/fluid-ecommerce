<?php
// fluid.pos.php
// Michael Rajotte - 2017 Octobre
// A point of sale for Fluid.

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


function php_load_pos() {
	try {
		$fluid = new Fluid();
		$fluid->php_db_begin();

		$html = "<div class='container-fluid' style='background-color: white;'>";
			$html .= "<div class='row'>";
				$html .= "<div style='border-bottom: 1px solid #212121; background-color: #252525' class='col-sm-12 col-md-12'>";
					$html .= "<div class='header-logo' style='display: table-cell; text-align: left; vertical-align: middle;'><span class='icon-leos-logo-rotate' style='font-size: 40px; color: red;'></span></div>";
				$html .= "</div>";
			$html .= "</div>";

			$html .= "<div class='row' style='border-bottom: 1px solid #212121;'>";

				$html .= "<div style='padding-left: 0px; padding-right: 0px;' class='col-sm-5 col-md-5'>";
					$html .= "<div id='f-stock-scroll-div' style='max-height: 85vh; min-height: 85vh; overflow-y: scroll;'>";
						$html .= "<div id='f-scan-holder'></div>";
						//$html .= "";<div id='fluid-cart-scroll-edit'>
					$html .= "</div>";
				$html .= "</div>";

				// --> Build the category data.
				$fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " WHERE c_enable = 1 ORDER BY c_sortorder ASC");
				$category_data_raw = NULL;
				if(isset($fluid->db_array)) {
					if(count($fluid->db_array) > 0) {
						foreach($fluid->db_array as $key => $value) {
							if($value['c_parent_id'] == NULL)
								$category_data_raw[$value['c_id']]['parent'] = $value;
							else
								$category_data_raw[$value['c_parent_id']]['childs'][] = $value;
						}
					}
				}

				$category_data = NULL;

				// Resort the categories into the proper order.
				foreach($category_data_raw as $parent) {
					if(isset($parent['parent'])) {
						if(isset($category_data[$parent['parent']['c_sortorder']]))
							$category_data[] = $parent; // Make a new key if this already exists.
						else
							$category_data[$parent['parent']['c_sortorder']] = $parent;
					}
				}

				ksort($category_data);
				$category_html = NULL;

				if(isset($category_data)) {
					foreach($category_data as $parent) {

						if(isset($parent['childs'])) {
							foreach($parent['childs'] as $value) {
								$category_html .= "<div class='f-pos-cat-btns-container'>";
									$category_html .= "<div class='btn btn-default f-pos-cat-btns'>";
										$category_html .= "<div class='f-pos-cat-btn-text'>" . $value['c_name'] . "</div>";
									$category_html .= "</div>";
								$category_html .= "</div>";
							}
						}
					}
				}

				$html .= "<div style='padding-left: 0px; padding-right: 0px;' class='col-sm-7 col-md-7'>";
					$html .= "<div>
							<ul class='nav nav-pills nav-justified f-pills'>
								<li style='border-left: 1px solid #A4B4C5; border-right: 1px solid #A4B4C5; border-bottom: 1px solid #A4B4C5;' role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#fposcats' data-target='#fposdashboard' data-toggle='tab' style='border-radius: 0px;'><span class='glyphicon glyphicon-dashboard'></span> Dashboard</a></li>
								<li style='border-right: 1px solid #A4B4C5; border-bottom: 1px solid #A4B4C5;' class='active' role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#fposcats' data-target='#fposcats' data-toggle='tab' style='border-radius: 0px;'><span class='glyphicon glyphicon-th-large'></span> Categories</a></li>
								<li style='border-bottom: 1px solid #A4B4C5;' role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#fpositems' data-target='#fpositems' data-toggle='tab' style='border-radius: 0px;'><span class='glyphicon glyphicon-list-alt'></span> Items</a></li>
							</ul>
							</div>";

					$html .= "<div class='tab-content' style='border-left: 1px solid #A4B4C5;'>";
						$html .= "<div id='fposdashboard' class='tab-pane fade in'>";
							$html .= "<div class='f-right-container'>Dash board goes here. Credit Card keys, debit keys, cash keys, N/S# key, open draw key, complete sale key, tax region or tax selections (drop down menu with selected. ie: BC, ALBERTA, QUEBEC, ONTARIO, etc (under each selection it shows GST line and PST line in the dropdown menu, or just GST if it's another province like ALBERTA, etc. HST for HST provinces.</div>";
						$html .= "</div>";

						$html .= "<div id='fposcats' class='tab-pane fade in active'>";
							if(isset($category_html)) {
								$html .= "<div class='row' style='margin: 0px;'>";
									$html .= "<div class='col-sm-12 col-md-12' style='padding-left: 0px; padding-right: 0px;'>";
										$html .= "<div class='f-right-container'>" . $category_html . "</div>";
									$html .= "</div>";
								$html .= "</div>";
							}
							else
								$html .= "<div class='f-right-container'>No you have categories.</div>";
						$html .= "</div>";

						$html .= "<div id='fpositems' class='tab-pane fade in'>";
							$html .= "<div class='f-right-container'>No Items.</div>";
						$html .= "</div>";
					$html .= "</div>";

				$html .= "</div>";


			$html .= "</div>"; // row #2

			$html .= "<div id='pos-footer' class='pos-footer'>";
				$html .= "<div class='row'>";
					$html .= "<div id='f-pos-subtotal' class='col-xs-4 col-sm-4 col-md-4'>Sub-total</div>";
					$html .= "<div id='f-pos-tax' class='col-xs-4 col-sm-4 col-md-4'>Tax</div>";
					$html .= "<div id='f-pos-total' class='col-xs-4 col-sm-4 col-md-4'>Total</div>";
				$html .= "</div>"; // row #3
			$html .= "</div>"; // pos-footer

		$html .= "</div>"; // Container


		// --> Build a login menu, basically a modal screen with user names to touch name to log in.
		// --> Left side of grid is item list and totals and finish sale button at bottom.
		// --> Left side grid, items are group by qty's. If item has no barcode and was entered by category, only one is added but can be increased by qty if required. Adding another from same category adds another line of that item, but scanning a item barcode will increase qty if it exists. Have + and - buttons similar to the stock editing system beside the items.
		// --> Right side of grid, 3 tabs at top, Categories (default) and items. Both have search.
		// --> Category tab, works like the old register system, touch a category button, then a modal with touch calculator pad to enter a price. It adds item into left grid as cat name.
		// --> At anytime during POS, scanning something enters it into the left grid (use stock scanning code). If item doesn't exist, popup comes up saying so.
		// --> Split payment types during purchasing. (half credit, half cash, half other etc).

		$fluid->php_db_commit();

		$execute_functions[]['function'] = "js_clear_fluid_selection";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_html_style_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-admin-div")));

		$execute_functions[]['function'] = "js_html_style_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-admin-navbar")));

		$execute_functions[]['function'] = "js_pos_set_body_colour";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-pos-div"), "innerHTML" => base64_encode($html))));

		$execute_functions[]['function'] = "js_pos_update_totals";

		$execute_functions[]['function'] = "js_pos_scan_clear";
		$execute_functions[]['function'] = "js_pos_scan_init";

		$execute_functions[]['function'] = "js_html_style_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-pos-div")));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_pos_scan() {
	try {
		$fluid = new Fluid ();
		$f_scan = json_decode(base64_decode($_REQUEST['data']), TRUE);
		$f_return_data = NULL;
		$f_row_id_random = NULL;

		$html_edit = NULL;

		if(isset($f_scan['s_code']) && strlen($f_scan['s_code']) > 0) {
			$fluid->php_db_begin();

			$p_in = "WHERE p_mfgcode IN ('" . $fluid->php_escape_string($f_scan['s_code']) . "'";

			/*
			if(isset($f_scan['s_scan'])) {
				foreach($f_scan['s_scan'] as $s_key => $s_data) {
					$p_in .= ", ";
					$p_in .= "'" . $s_data['p_mfgcode'] . "'";
				}
			}
			*/

			$p_in .= ");";

			// Change query to select all in the s_code and IN WHERE and rebuild the display html and return back?
			$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $p_in);

			$fluid->php_db_commit();

			if(isset($fluid->db_array)) {
				//$html_edit = "<div id='fluid-cart-scroll-edit'>";

				foreach($fluid->db_array as $key => $data) {
					$f_row_id_random = "section-" . $data['p_mfgcode'] . "-" . rand(1, 100) . "-" . time();

					// Process the image.
					$p_images = $fluid->php_process_images($data['p_images']);
					$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					if($f_scan['s_code'] == $data['p_mfgcode'])
						$f_flash_style = " class='f-scan-animated'";
					else
						$f_flash_style = NULL;

					// Item exists, we are not really adjusting anything. Just sending back some HTML to redraw the list of items.
					if(isset($f_scan['s_scan'][$data['p_mfgcode']])) {
						$p_stock = (int)$f_scan['s_scan'][$data['p_mfgcode']]['p_stock'];
						$p_adj = $f_scan['s_scan'][$data['p_mfgcode']]['p_adj'];
						$p_stock_adj = (int)$f_scan['s_scan'][$data['p_mfgcode']]['p_stock_adj'];
					}
					else {
						// Item doesn't exists, lets build some data.
						$p_stock = (int)$data['p_stock'];

						if(isset($f_scan['f_mode'])) {
							if($f_scan['f_mode'] == "minus") {
								if($p_stock > 0) {
									$p_adj = -1;
									$p_stock_adj = $p_stock - 1;
								}
								else {
									$p_adj = 0;
									$p_stock_adj = $p_stock;
								}
							}
							else if($f_scan['f_mode'] == "plus") {
								$p_adj = 1;
								$p_stock_adj = $p_stock + $p_adj;
							}
							else {
								$p_adj = 0;
								$p_stock_adj = $p_stock;
							}
						}
						else {
							$p_adj = 1;
							$p_stock_adj = $p_stock + $p_adj;
						}
					}

					//$f_scan['s_scan'][$data['p_mfgcode']] = Array('p_id' => $data['p_id'], 'p_mfgcode' => $data['p_mfgcode'], 'p_stock' => $p_stock, 'p_stock_adj' => $p_stock_adj, 'p_adj' => $p_adj);
					$f_return_data = Array('p_id' => $data['p_id'], 'p_mfgcode' => $data['p_mfgcode'], 'p_price' => $data['p_price'], 'p_cost' => $data['p_cost_real'], 'p_stock' => $p_stock, 'p_stock_adj' => $p_stock_adj, 'p_adj' => $p_adj);

					$confirm_message_item = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
					$confirm_message_item .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img>";
					$confirm_message_item .= $data['m_name'] . " " . $data['p_name'];
					$confirm_message_item .= "</div>";

					$html_edit .= "<div class='fluid-cart' name='fluid-cart-editor-items' id='fluid-cart-editor-item-" . $data['p_id'] . "' data-id='" . $data['p_id'] . "' data-price='" . $data['p_price'] . "'>";
						$html_edit .= "<div class='divTable' id='" . $f_row_id_random . "'>";
							$html_edit .= "<div class='divTableBody'>";
								$html_edit .= "<div name='f-scan-row' id='f-scan-row-" . $data['p_mfgcode'] . "'" . $f_flash_style . " style='display: table-row;'>";

									$html_edit .= "<div class='divTableCell divTablePadding f-scan-image-min' style='vertical-align:middle;'>";
										$html_edit .= "<div class='f-scan-trash-btn' style='margin-top: 0px; margin-bottom: 0px; display: inline-block; vertical-align: middle;'>";
											/*
											$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Remove this item from the list and cancel its stock updates?</div>";
											$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='this.blur(); document.getElementById(\"fluid-cart-scroll-edit\").removeChild(document.getElementById(\"fluid-cart-editor-item-" . $data['p_id'] . "\")); delete FluidVariables.s_scan[\"" . $data['p_mfgcode'] . "\"]; js_scan_init(\"f_scan_code\"); js_scan_set_save_all_button(); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");
											$html_edit .= "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='this.blur(); $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete . $confirm_message_item) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
											*/
											$html_edit .= "<button type='button' class='btn btn-default' aria-haspopup='true' aria-expanded='false' style='float:left;'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span></button>";
										$html_edit .= "</div>";
										$html_edit .= "<img class='f-scan-image-show' src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img>";
									$html_edit .= "</div>";

									$html_edit .= "<div class='divTableCell divTablePadding' style='vertical-align:middle;'>";
										$html_edit .= "<div style='display: table-row; style='width: 100%;'>";
										$html_edit .= "<div style='display: table-cell; width: 100%; vertical-align: middle;'>" . $data['m_name'] . " " . $data['p_name'] . " - " . $data['p_mfgcode'] . "</div>";
										$html_edit .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; padding-right: 5px;'><div style='font-weight: 600; margin: auto; text-align: center;'>Price</div><div>" . number_format($data['p_price'], 2, '.', ',') .  "</div></div>";
										$html_edit .= "</div>";
									$html_edit .= "</div>";

								$html_edit .= "</div>"; // --> divTableRow

							$html_edit .= "</div>";
						$html_edit .= "</div>";
					$html_edit .= "</div>";
				}
				//$html_edit .= "</div>";
			}
		}

		// --> Need to re-enable typing.
		$execute_functions[]['function'] = "js_pos_scan_init";

		if(isset($html_edit)) {
			// --> Set the scan data.
			$execute_functions[]['function'] = "js_pos_scan_data_set";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_return_data));

			$execute_functions[]['function'] = "js_pos_scan_reset_css";

			$execute_functions[]['function'] = "js_pos_scan_append";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($html_edit), "parent" => base64_encode("f-scan-holder"))));

			$execute_functions[]['function'] = "js_pos_scan_scroll";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_row_id_random));

			$execute_functions[]['function'] = "js_pos_update_totals";
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$execute_functions[]['function'] = "js_scan_init";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
}

?>
