<?php
// fluid.selector.php
// Michael Rajotte - 2017 Octobre
// Used for selecting items for the Formula Links

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

function php_fluid_load_item_selector($data = NULL) {
	$fluid = new Fluid();
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['data'])) {
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		}
		else if(isset($data)) {
			$f_data = (object)json_decode(base64_decode($data));
		}
		else {
			$f_data = NULL;
		}

		if(isset($f_data->f_page_num)) {
			$f_page = $f_data->f_page_num;
			$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
		}
		else {
			$f_page = 0;
			$f_start = 0;
		}

		$f_quantity = FALSE;

		if(isset($f_data->f_quantity)) {
			if($f_data->f_quantity == 1) {
				$f_quantity = TRUE;
			}
		}

		$f_tmp_count = 0;

		$mode = $f_data->mode;

		if($mode == "items") {
			// --> Item selector
			$fluid->php_db_begin();

			if(isset($f_data->query_count))
				$f_query_count = $f_data->query_count;
			else
				$f_query_count = "SELECT COUNT(*) AS tmp_u_count FROM " . TABLE_PRODUCTS;

			$fluid->php_db_query($f_query_count);

			if(isset($fluid->db_array))
				$f_tmp_count = $fluid->db_array[0]['tmp_u_count'];

			if(isset($f_data->query))
				$f_query = $f_data->query;
			else
				$f_query = "SELECT p.*, c.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id ORDER BY p_id ASC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

			$fluid->php_db_query($f_query);

			$tmp_array = $fluid->db_array;

			$tmp_selection_array = NULL;
			if(isset($f_data->f_selection)) {
				$selection_data = base64_encode(json_encode($f_data->f_selection->p_selection));
				if(isset($f_data->f_selection->p_selection))
					foreach($f_data->f_selection->p_selection as $product) {
						$tmp_selection_array[$product->p_id] = $product->p_id;
					}
			}
			else
				$selection_data = NULL;

			$fluid->php_db_commit();

			$html = php_fluid_html_selection_menu(Array('tmp_array' => $tmp_array, 'f_items' => $data['f_items'], 'f_quantity' => $f_quantity, 'mode' => 'items', 'f_tmp_count' => $f_tmp_count, 'f_selection' => $selection_data, "f_data" => $f_data));
		}
		else {
			if(isset($f_data->f_selection))
				$selection_data = base64_encode(json_encode($f_data->f_selection));
			else
				$selection_data = NULL;

			$html = php_fluid_html_category_menu($mode, NULL, FALSE, $selection_data);
		}

		if(isset($f_data->f_refresh)) {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("modal-fluid-div"), "innerHTML" => base64_encode($html))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else {
			$html_btn_back = "<button type=\"button\" class=\"btn btn-danger\" onClick=\"js_modal_hide('#fluid-main-modal'); js_modal_show('#fluid-modal');\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button>";
			$html_btn_save = "<button type=\"button\" class=\"btn btn-success\" onClick=\"js_fluid_item_selector_save();\"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Save</button>";

			$execute_functions[]['function'] = "js_modal_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

			$execute_functions[]['function'] = "js_html_style_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-modal-close-button")));

			$execute_functions[]['function'] = "js_html_style_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-modal-back-button")));

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-back-button"), "innerHTML" => base64_encode($html_btn_back))));

			$execute_functions[]['function'] = "js_html_style_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "fluid-modal-trigger-button")));

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-trigger-button"), "innerHTML" => base64_encode($html_btn_save))));

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("modal-fluid-header-div"), "innerHTML" => base64_encode("Item Selector"))));

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("modal-fluid-div"), "innerHTML" => base64_encode($html))));

			$execute_functions[]['function'] = "js_modal_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-main-modal"));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_html_selection_menu($data) {
	try {
		$fluid = new Fluid();

		if(isset($data['f_data'])) {
			$f_data = (object)$data['f_data'];
		}
		else {
			$f_data = NULL;
		}

		if(isset($f_data->f_page_num)) {
			$f_page = $f_data->f_page_num;
			$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
		}
		else {
			$f_page = 0;
			$f_start = 0;
		}

		$f_quantity = FALSE;
		if(isset($f_data->f_quantity)) {
			if($f_data->f_quantity == 1) {
				$f_quantity = TRUE;
			}
		}

		$mode = $data['mode'];

		$tmp_selection_array = NULL;
		if(isset($f_data->f_selection)) {
			$selection_data = base64_encode(json_encode($f_data->f_selection->p_selection));

			if(isset($f_data->f_selection->p_selection))
				foreach($f_data->f_selection->p_selection as $product) {
					$tmp_selection_array[$product->p_id] = $product->p_id;
				}
		}
		else {
			$selection_data = NULL;
		}

		if(isset($f_data->pagination_function)) {
			$f_pagination_function = $f_data->pagination_function;
		}
		else {
			$f_pagination_function = "js_fluid_load_items_selector";
		}

		$html = "<div class=\"input-group\" style='padding-bottom: 10px;'>
             <input id=\"search-input-selector\" type=\"text\" class=\"form-control\" placeholder=\"Search...\" onkeydown=\"if(event.keyCode == 13){js_search_selector('" . $data['mode'] . "', document.getElementById('search-input-selector').value,  " . $f_quantity . "); document.getElementById('search-input-selector').value = '';}\">
				<span class=\"input-group-btn\">
					<button id=\"search-button-selector\" onclick=\"js_search_selector('" . $data['mode'] . "', document.getElementById('search-input-selector').value, " . $f_quantity . "); document.getElementById('search-input-selector').value = ''\" type=\"submit\" class=\"btn btn-default\">
						<span id=\"search-glyph\" class=\"glyphicon glyphicon-search\"></span>
					</button>
				</span>
         </div>";

		$html .= "<div id='fluid-category-listing-selector' class='list-group'>";
			$html .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-users'><li>";
				if(isset($tmp_selection_array)) {
					if(count($tmp_selection_array) > 0)
						$html .= "<div id='category-selector-a-items' style='height: 40px; background-color:" . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
					else
						$html .= "<div id='category-selector-a-items' style='height: 40px;' class='list-group-item'>";
				}
				else {
					$html .= "<div id='category-selector-a-items' style='height: 40px;' class='list-group-item'>";
				}

					$html .= "<span id='category-selector-badge-count-items' class='badge'>" . $data['f_tmp_count'] . "</span>";

					if(isset($tmp_selection_array))
						$html.= "<span id='category-selector-badge-select-count-items' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$html .= "<span id='category-selector-badge-select-count-items' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$html .= "<span id='category-selector-badge-select-lock-items' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$html .= " <span id='category-selector-span-open-items' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Items</div></span>";
				$html .= "</div>";
			$html .= "</li></ul>";

			$html .= "<div class='f-pagination'>" . $fluid->php_pagination($data['f_tmp_count'], FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode, NULL, "FluidSelector.f_page_num", $f_quantity) . "</div>";

			$html .= "<div id='category-div-users'>";
			$html .= php_html_item_list($data['tmp_array'], $selection_data, $data['mode'], $f_quantity);
			$html .= "</div>";

		$html .= "</div>";

		$html .= "<div class='f-pagination'>" . $fluid->php_pagination($data['f_tmp_count'], FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode, NULL, "FluidSelector.f_page_num", $f_quantity) . "</div>";

		return $html;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_html_category_menu($mode = NULL, $where_query = NULL, $results_false = FALSE, $selection_data = NULL, $f_quantity = FALSE) {
	try {
		$html = "<div class=\"input-group\" style='padding-bottom: 10px;'>
             <input id=\"search-input-selector\" type=\"text\" class=\"form-control\" placeholder=\"Search...\" onkeydown=\"if(event.keyCode == 13){js_search_selector('" . $mode . "', document.getElementById('search-input-selector').value, " . $f_quantity . "); document.getElementById('search-input-selector').value = '';}\">
				<span class=\"input-group-btn\">
					<button id=\"search-button-selector\" onclick=\"js_search_selector('" . $mode . "', document.getElementById('search-input-selector').value, " . $f_quantity . "); document.getElementById('search-input-selector').value = ''\" type=\"submit\" class=\"btn btn-default\">
						<span id=\"search-glyph\" class=\"glyphicon glyphicon-search\"></span>
					</button>
				</span>
         </div>";

		$html .= "<div id='fluid-category-listing' class='list-group'>";
		if($results_false == TRUE)
			$html .= $where_query;
		else {
			$data = php_html_categories_selector(NULL, $mode, $where_query, $selection_data, $f_quantity); // Fetch the category listing.
			$html .= $data['html'];
		}
		$html .= "</div>";

		return $html;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// This takes a array of product data and displays them in a neatly formatted html table.
function php_html_item_list($data_array, $selection_array = NULL, $mode = NULL, $f_quantity = FALSE) {
	$fluid_mode = new Fluid_Mode($mode);

	// Used for keeping track which items are already selected.
	$tmp_selection_array = Array();
	$f_quantity_array = Array();
	if(isset($selection_array)) {
		foreach(json_decode(base64_decode($selection_array)) as $product) {
			$tmp_selection_array[$product->p_id] = $product->p_id;

			if($f_quantity == TRUE) {
				$f_quantity_array[$product->p_id] = $product->p_quantity;
			}
		}
	}

	// Used for passing the products in this category to the select all products js functions.
	$tmp_product_array = Array();
	$tmp_product_stock_array = Array();
	$tmp_product_no_stock_array = Array();
	$tmp_product_enabled_array = Array();
	$tmp_product_disabled_array = Array();
	$tmp_product_price_array = Array();
	$tmp_product_noprice_array = Array();
	$tmp_product_nodimensions_array = Array();
	$tmp_product_noweight_array = Array();
	$tmp_product_noready_array = Array();
	$tmp_product_ready_array = Array();
	$tmp_product_images_array = Array();
	$tmp_product_noimages_array = Array();
	$tmp_product_c_filters_array = Array();
	$tmp_product_no_c_filters_array = Array();

	$p_catmfgid = NULL;

	if(isset($data_array)) {
		if(isset($data_array[0]) && $fluid_mode->mode != "items") {
			$p_catmfgid = (int)$data_array[0][$fluid_mode->p_catmfg_id];
		}
		else {
			$p_catmfgid = $fluid_mode->mode;
		}

		foreach($data_array as $value) {
			$tmp_product_array[$value['p_id']] = $value['p_enable'];

			if($value['p_stock'] > 0)
				$tmp_product_stock_array[$value['p_id']] = $value['p_enable'];
			else
				$tmp_product_no_stock_array[$value['p_id']] = $value['p_enable'];

			if($value['p_enable'] > 0)
				$tmp_product_enabled_array[$value['p_id']] = $value['p_enable'];

			if($value['p_enable'] < 1)
				$tmp_product_disabled_array[$value['p_id']] = $value['p_enable'];

			if($value['p_price'] > 0)
				$tmp_product_price_array[$value['p_id']] = $value['p_enable'];

			if($value['p_price'] <= 0)
				$tmp_product_noprice_array[$value['p_id']] = $value['p_enable'];

			if($value['p_length'] <= 0 || $value['p_width'] <= 0 || $value['p_height'] <= 0)
				$tmp_product_nodimensions_array[$value['p_id']] = $value['p_enable'];

			if($value['p_weight'] <= 0)
				$tmp_product_noweight_array[$value['p_id']] = $value['p_enable'];

			if($value['p_weight'] <= 0 || $value['p_length'] <= 0 || $value['p_width'] <= 0 || $value['p_height'] <= 0 || $value['p_price'] <= 0 || empty($value['p_weight']) || empty($value['p_height'])) {
				$tmp_product_noready_array[$value['p_id']] = $value['p_enable'];
			}
			else {
				$tmp_product_ready_array[$value['p_id']] = $value['p_enable'];
			}

			$img_tmp = json_decode(base64_decode($value['p_images']));

			if(!empty($img_tmp)) {
				if(count($img_tmp) > 0)
					$tmp_product_images_array[$value['p_id']] = $value['p_enable'];
				else
					$tmp_product_noimages_array[$value['p_id']] = $value['p_enable'];
			}
			else
				$tmp_product_noimages_array[$value['p_id']] = $value['p_enable'];

			$c_filters = json_decode($value['p_c_filters']);

			if(!empty($c_filters)) {
				if(count($c_filters) > 0)
					$tmp_product_c_filters_array[$value['p_id']] = $value['p_enable'];
				else
					$tmp_product_no_c_filters_array[$value['p_id']] = $value['p_enable'];
			}
			else
				$tmp_product_no_c_filters_array[$value['p_id']] = $value['p_enable'];
		}
	}
	$return = "<div class='table-responsive panel panel-default'>";

	$select_button = "<div class='dropdown'>
	<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
      Select <span class='caret'></span>
    </a>
	  <ul class='dropdown-menu' aria-labelledby='dropdownMenu1'>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_images_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/images</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noimages_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/no images</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_enabled_array)) . "\");'
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_stock_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all in stock</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_no_stock_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no stock</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_enabled_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all enabled</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_disabled_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all disabled</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_price_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/price</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noprice_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no price</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_nodimensions_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no dimensions</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noweight_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no weight</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_c_filters_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/c filters</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_no_c_filters_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/no c filters</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noready_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all not ready</a></li>
		<li><a onClick='js_selector_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_ready_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all ready</a></li>
		<li><a onClick='js_select_clear_p_selection_category_selector(\"" . base64_encode(json_encode(array($p_catmfgid))) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-minus\" aria-hidden=\"true\"></span> Un-select all</a></li>
	  </ul>
	  </div>";

	$return .= "<table class='table table-hover' id='cat-" . $p_catmfgid . "'>";

	if(count($data_array) == 0) {
		$return .= "<tr><td>" . $fluid_mode->msg_no_products . "</td></tr>";
	}
	else {
		$return .= "<thead>";
		$return .= "<tr style='font-weight: bold;'>";

		//if($fluid_mode->mode != "items")
			//$return .= "<td></td>";

		$return .= "<td style='width:20px;'></td><td style='text-align:center; min-width: 75px;'>" . $select_button . "</td><td>Image</td><td style='text-align: center;'>" . $fluid_mode->mode_name . "</td>";

		if($fluid_mode->mode == "items") {
			$return .= "<td style='text-align: center;'>" . $fluid_mode->mode_name_real_cap . "</td>";
		}

		if($f_quantity == TRUE) {
			$return .= "<td>Item</td><td style='text-align:center;'>UPC/EAN</td><td style='text-align:center;'>Code</td><td style='text-align:center;'>Stock</td><td style='text-align: center;'>Component Qty</td><td style='text-align:right;'>Cost</td><td style='text-align:right;'>Price</td>";
		}
		else {
			$return .= "<td>Item</td><td style='text-align:center;'>UPC/EAN</td><td style='text-align:center;'>Code</td><td style='text-align:center;'>Stock</td><td style='text-align:right;'>Cost</td><td style='text-align:right;'>Price</td>";
		}

		$return .= "</tr>";

		$return .= "</thead>";
		$return .= "<tbody class='fsortable-" . $p_catmfgid . "'>";
		foreach($data_array as $value) {
			if(in_array($value['p_id'], $tmp_selection_array)) {
				if($value['p_enable'] == 0)
					$style_line_through = "text-decoration: line-through; ";
				else
					$style_line_through = "text-decoration: none; ";

				$style = "style='" . $style_line_through . "font-style: italic; vertical-align: middle; background-color: " . COLOUR_SELECTED_ITEMS . ";'";
				$checked = "checked";
			}
			else if($value['p_enable'] == 0) {
				$style = "style='text-decoration: line-through; vertical-align: middle; background-color: " . COLOUR_DISABLED_ITEMS . ";'";
				$checked = "";
			}
			else if($value['p_enable'] == 2) {
				$line_through_d = "text-decoration: line-through; ";
				if($value['p_stock'] > 0) {
					$line_through_d = NULL;
				}
				
				$style = "style='" . $line_through_d . "vertical-align: middle; background-color: " . COLOUR_DISCONTINUED_ITEMS . ";'";
				$checked = "";
			}
			else {
				$style = "style='background-color: transparent; vertical-align: middle;'";
				$checked = "";
			}

			$return .= "<tr class='ui-state-default' id='selector_p_id_tr_" . $value['p_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"selector_p_id_" . $value['p_id'] . "\").click();'>";

			if($value['p_enable'] > 0)
				$style_eye = " style='text-decoration: none; font-size:12px; display:none; vertical-align: middle;' ";
			else
				$style_eye = " style='text-decoration: none !important; font-size:12px; display:block; vertical-align: middle;' ";

			$p_c_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
			if(isset($value['p_c_filters'])) {
				if($value['p_c_filters'] == '{}')
					$p_c_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
				else
					$p_c_filters = NULL;
			}

			$p_m_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
			if(isset($value['p_m_filters'])) {
				if($value['p_m_filters'] == '{}')
					$p_m_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
				else
					$p_m_filters = NULL;
			}

			$p_trending = NULL;
			if(isset($value['p_trending'])) {
				if($value['p_trending'] == 1)
					$p_trending = "<span style='color: #00FF1F;' class=\"glyphicon glyphicon-fire\" aria-hidden=\"true\">_t</span>";
				else
					$p_trending = NULL;
			}

			$p_preorder = NULL;
			if(isset($value['p_preorder'])) {
				if($value['p_preorder'] == 1)
					$p_preorder = "<span style='color: #0081FF' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_p</span>";
				else
					$p_preorder = NULL;
			}

			$p_rebate_claim = NULL;
			if(isset($value['p_rebate_claim'])) {
				if($value['p_rebate_claim'] == 1)
					$p_rebate_claim = "<span style='color: #00FF40' class=\"glyphicon glyphicon-usd\" aria-hidden=\"true\">_r</span>";
				else
					$p_rebate_claim = NULL;
			}

			$p_stock_end = NULL;
			if(isset($value['p_stock_end'])) {
				if($value['p_stock_end'] == 1)
					$p_stock_end = "<span style='color: #00EFFF;' class=\"glyphicon glyphicon-cloud\" aria-hidden=\"true\">_e</span>";
				else
					$p_stock_end = NULL;
			}

			$p_showalways = NULL;
			if(isset($value['p_showalways'])) {
				if($value['p_showalways'] == 1)
					$p_showalways = "<span style='color: #0081FF' class=\"glyphicon glyphicon-flash\" aria-hidden=\"true\">_a</span>";
				else
					$p_showalways = NULL;
			}

			$p_namenum = NULL;
			if(isset($value['p_namenum'])) {
				if($value['p_namenum'] == 1)
					$p_namenum = "<span style='color: #FFE900' class=\"glyphicon glyphicon-paperclip\" aria-hidden=\"true\">_#</span>";
				else
					$p_namenum = NULL;
			}

			$p_discontinued = NULL;
			if(isset($value['p_enable'])) {
				if($value['p_enable'] == 2)
					$p_discontinued= "<span style='color: #5BC0DE' class=\"glyphicon glyphicon-certificate\" aria-hidden=\"true\">_d</span>";
				else
					$p_discontinued = NULL;
			}

			$p_rental = NULL;
			if(isset($value['p_rental'])) {
				if($value['p_rental'] == 1)
					$p_rental= "<span style='color: #DEC35B' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_r</span>";
				else
					$p_rental = NULL;
			}

			$p_special_order = NULL;
			if(isset($value['p_special_order'])) {
				if($value['p_special_order'] == 1)
					$p_special_order= "<span style='color: #DE815B' class=\"glyphicon glyphicon-star\" aria-hidden=\"true\">_s</span>";
				else
					$p_special_order = NULL;
			}

			$return .= "<td class='f-td' style='text-align:center; vertical-align: middle;'><span " . $style_eye . " id='selector_p_id_tr_" . $value['p_id'] . "_eye' class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\"></span> " . $p_c_filters . " " . $p_m_filters . " " . $p_trending . " " . $p_preorder . " " . $p_rebate_claim . " " . $p_stock_end . " " . $p_showalways . " " . $p_namenum . " " . $p_discontinued . " " . $p_rental . " " . $p_special_order . "</td>";

			if($fluid_mode->mode != "items") {
				$p_catmfgid = $value[$fluid_mode->p_catmfg_id];
			}
			else {
				$p_catmfgid = $fluid_mode->mode;
			}

			// Hide this column. It contains data used for sortable product updates.
			$return .= "<td class='f-td' style='display:none;' id='selector_p_id_tr_" . $value['p_id'] . "_td'>" . base64_encode(json_encode(Array('p_id' => $value['p_id'], $fluid_mode->id => $p_catmfgid, 'p_sortorder' . $fluid_mode->sort_order => $value['p_sortorder' . $fluid_mode->sort_order], 'mode' => $mode))) . "</td>";

			$return .= "<td class='f-td' style='text-align:center; vertical-align: middle;'><input id='selector_p_id_" . $value['p_id'] . "' onClick='js_cancel_event(event); js_fluid_item_selector(\"" . $value['p_id'] . "\", \"" . $p_catmfgid . "\", \"" . $value['p_enable'] . "\", " . $f_quantity . ");' type=\"checkbox\" " . $checked . "></td>";

			$fluid = new Fluid ();
			$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
			$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
			$p_images = $fluid->php_process_images($value['p_images']);
			$width_height_admin = $fluid->php_process_image_resize($p_images[0], "80", "80", $f_img_name);
			$f_image_html = "<img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $width_height_admin['image'] . "' alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"/></img>";
			$return .= "<td class='f-td' style='vertical-align: middle;'>" . $f_image_html . "</td>";

			if($fluid_mode->mode == "manufacturers")
				$return .= "<td class='f-td' style='vertical-align: middle; text-align: center;'>" . $value['c_name'] . "</td>";
			else
				$return .= "<td class='f-td' style='vertical-align: middle; text-align: center;'>" . $value['m_name'] . "</td>";

			if($fluid_mode->mode == "items")
				$return .= "<td class='f-td' style='vertical-align: middle; text-align: center;'>" . $value['c_name'] . "</td>";

			$return .= "<td class='f-td' style='vertical-align: middle;'>" . $value['p_name'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $value['p_mfgcode'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $value['p_mfg_number'] . "</td><td id='p_td_id_stock_" . $value['p_id'] . "' class='f-td' style='text-align:center; vertical-align: middle;'>" . $value['p_stock'] . "</td>";

			if($f_quantity == TRUE) {
				$f_qty_tmp = 1;
				if(isset($f_quantity_array[$value['p_id']])) {
					$f_qty_tmp = $f_quantity_array[$value['p_id']];
				}

				$return .= "<td id='p_td_id_component_quantity_" . $value['p_id'] . "' class='f-td' style='text-align:center; vertical-align: middle;'><input id='p_td_id_component_quantity_spinner_" . $value['p_id'] . "' onkeyup='js_fluid_item_selector_quantity_update(\"" . $value['p_id'] . "\", this.value);' onClick='js_cancel_event(event); js_fluid_item_selector_quantity_update(\"" . $value['p_id'] . "\", this.value);' type='number' min='1' style='width: 60px;' value='" . $f_qty_tmp . "'></input></td>";
			}

			//$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($value['p_cost'], 2, '.', ',') . "</td>";
			$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($value['p_cost_real'], 2, '.', ',') . "</td>";

			if($value['p_price_discount'] && strtotime($value['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($value['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) {
				$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'><div style='font-style: italic; text-decoration: line-through;'>" . number_format($value['p_price'], 2, '.', ',') . "</div><div style='color: red;'>" . number_format($value['p_price_discount'], 2, '.', ',') . "</div></td>";
			}
			else {
				$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($value['p_price'], 2, '.', ',') . "</td>";
			}

			$return .= "</tr>";
		}
		$return .= "</tbody>";
	}

	$return .= "</table>";
	$return .= "</div>";

	return $return;
}

// Perform a search.
function php_search_selector() {
	$fluid = new Fluid ();

	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($f_data->mode)) {
			$mode = $f_data->mode;
		}
		else {
			$mode = NULL;
		}

		$f_quantity = FALSE;

		if(isset($f_data->f_quantity)) {
			if($f_data->f_quantity == 1) {
				$f_quantity = TRUE;
			}
		}

		$fluid_mode = new Fluid_Mode($mode);
		$return_data = NULL;

		// We are in item mode, return back a nicely formatted html item page.
		if($fluid_mode->mode == "items") {
			if(isset($f_data))
				$f_data_s = Array("f_page_num" => $f_data->f_page_num, "f_refresh" => $f_data->f_refresh, "f_selection" => $f_data->f_selection);
			else
				$f_data_s = NULL;

			if(isset($f_data_s['f_page_num'])) {
				$f_page = $f_data_s['f_page_num'];
				$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
			}
			else {
				$f_page = 0;
				$f_start = 0;
			}

			if(isset($f_data->f_items)) {
				$f_items = $f_data->f_items;
			}
			else {
				$f_items = NULL;
			}

			$fluid->php_db_begin();
			$search_input = $fluid->php_escape_string(rtrim(trim($f_data->search_input)));

			if(strlen($search_input) < 1) {
				$fluid->php_db_query("SELECT COUNT(*) AS tmp_u_count FROM " . TABLE_PRODUCTS);

				$f_tmp_count = 0;
				if(isset($fluid->db_array))
					$f_tmp_count = $fluid->db_array[0]['tmp_u_count'];

				$fluid->php_db_query("SELECT p.*, c.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id ORDER BY p_id ASC LIMIT 0, " . FLUID_ADMIN_LISTING_LIMIT);

				$tmp_array = Array();
				if(isset($fluid->db_array)) {
					$tmp_array = $fluid->db_array;
				}
			}
			else {
				$fluid->php_db_query("SELECT COUNT(*) AS tmp_u_count FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')");

				$f_tmp_count = 0;
				if(isset($fluid->db_array))
					$f_tmp_count = $fluid->db_array[0]['tmp_u_count'];

				$fluid->php_db_query("SELECT p.*, c.*, m.*, IF(`p_name` LIKE '%" . $search_input . "%',  20, IF(`p_name` LIKE '%" . $search_input . "%', 10, 0)) + IF(`p_mfgcode` LIKE '%" . $search_input . "%', 15,  IF(`p_mfgcode` LIKE '%" . $search_input . "%', 8, 0)) + IF(`p_mfg_number` LIKE '%" . $search_input . "%', 8,  0), IF(`c_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`p_details` LIKE '%" . $search_input . "%', 2, 0) +  IF(`p_keywords` LIKE '%" . $search_input . "%', 4,  0) + IF(`p_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_desc` LIKE '%" . $search_input . "%', 1,  0) AS `weight` FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%') ORDER BY `weight` DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT);

				$tmp_array = Array();
				if(isset($fluid->db_array)) {
					$tmp_array = $fluid->db_array;
				}

				$f_data_s['pagination_function'] = "js_pagination_search_selector";
			}

			$f_data_s['mode'] = "items";
			$f_data_s['f_quantity'] = $f_quantity;
			//$data_tmp['tmp_array'] = $tmp_array;
			//$data_tmp['mode'] = $fluid_mode->mode;
			$return_data = php_fluid_html_selection_menu(Array('tmp_array' => $tmp_array, 'f_items' => $f_items, 'mode' => $mode, 'f_tmp_count' => $f_tmp_count, 'f_data' => $f_data_s));

			$fluid->php_db_commit();
		}
		// Category or manufacturer mode searching.
		else {
			//$return_data = "<div id='fluid-category-listing' class='list-group'>";
			if(isset($f_data->f_selection))
				$selection_data = base64_encode(json_encode($f_data->f_selection));
			else
				$selection_data = NULL;

			if(!empty($f_data->search_input)) {
				$fluid->php_db_begin();
				$search_input = $fluid->php_escape_string(rtrim(trim($f_data->search_input)));
				// Scan for items and categories
				$fluid->php_db_query("SELECT p.*, c.*, m.*, IF(`c_name` LIKE '%" . $search_input . "%',  20, IF(`c_name` LIKE '%" . $search_input . "%', 10, 0)) + IF(`p_name` LIKE '%" . $search_input . "%', 15,  IF(`p_name` LIKE '%" . $search_input . "%', 8, 0)) + IF(`p_mfgcode` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`c_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_keywords` LIKE '%" . $search_input . "%', 4,  0) + IF(`p_desc` LIKE '%" . $search_input . "%', 1,  0) +  IF(`m_desc` LIKE '%" . $search_input . "%', 1,  0) +  IF(`c_desc` LIKE '%" . $search_input . "%', 1,  0) AS `weight` FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%') ORDER BY `weight` DESC");

				$tmp_array = NULL;

				if(isset($fluid->db_array))
					foreach($fluid->db_array as $value) {
						$tmp_array[$value[$fluid_mode->id]]['id'] = $value[$fluid_mode->id];
						$tmp_array[$value[$fluid_mode->id]]['parent_id'] = $value[$fluid_mode->X . "_parent_id"];
					}

				// Scan for categories / manufacturers only. Picks up empty categories / manufacturers.
				$fluid->php_db_query("SELECT " . $fluid_mode->X_id . ", " . $fluid_mode->X . "_parent_id, IF(`" . $fluid_mode->name . "` LIKE '%" . $search_input . "%',  20, IF(`" . $fluid_mode->name . "` LIKE '%" . $search_input . "%', 10, 0)) + IF(`" . $fluid_mode->seo . "` LIKE '%" . $search_input . "%', 5,  0) + IF(`" . $fluid_mode->desc . "` LIKE '%" . $search_input . "%', 3,  0) AS `weight` FROM " . $fluid_mode->table . " " . $fluid_mode->X . " WHERE (" . $fluid_mode->X . "." . $fluid_mode->name . " LIKE '%" . $search_input . "' OR " . $fluid_mode->X . "." . $fluid_mode->seo . " LIKE '%" . $search_input . "%' OR " . $fluid_mode->X . "." . $fluid_mode->desc . " LIKE '%" . $search_input . "%') ORDER BY `weight` DESC");

				if(isset($fluid->db_array))
					foreach($fluid->db_array as $value) {
						$tmp_array[$value[$fluid_mode->id]]['id'] = $value[$fluid_mode->id];
						$tmp_array[$value[$fluid_mode->id]]['parent_id'] = $value[$fluid_mode->X . "_parent_id"];
				}

				$where_query = NULL;
				$where_query_parent = NULL;
				if(isset($tmp_array)) {
					$where_query = $fluid_mode->id . " IN (";
					$iIn = 0;
					$iParent = 0;
					foreach($tmp_array as $key => $value) {
						if($iIn > 0)
							$where_query .= ",";

						$where_query .= $value['id'];

						if(!isset($value['parent_id'])) {
							if($iParent > 0)
								$where_query_parent .= ",";
							else
								$where_query_parent = " OR " . $fluid_mode->X . "_parent_id IN (";

							$where_query_parent .= $value['id'];

							$iParent++;
						}
						else if(isset($value['parent_id']))
							$where_query .= "," . $value['parent_id'];

						$iIn++;
					}
					$where_query .= ")";

					if(isset($where_query_parent)) {
						$where_query_parent .= ")";
						$where_query .= $where_query_parent;
					}
				}

				$fluid->php_db_commit();

				if(!$where_query)
					$return_data = php_fluid_html_category_menu($mode, "No results found.", TRUE, $selection_data, $f_quantity);
				else {
					$return_data = php_fluid_html_category_menu($mode, $where_query, FALSE, $selection_data, $f_quantity); // Fetch the categories / manufacturers listings.
				}
			}
			else {
				// Search query was blank, fetch all categories / manufacturers.
				$return_data = php_fluid_html_category_menu($mode, NULL, FALSE, $selection_data, $f_quantity);
			}

			//$return_data .= "</div>";
		}

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("modal-fluid-div"), "innerHTML" => base64_encode($return_data))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

// This returns a array of a category or categories.
function php_html_categories_selector($c_id = NULL, $mode = NULL, $query_where = NULL, $selection_data = NULL, $f_quantity) {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		$fluid_mode = new Fluid_Mode($mode);

		if($c_id)
			$where = "WHERE " . $fluid_mode->X_id . " = '" . $fluid->php_escape_string($c_id) . "' ";
		else if(isset($query_where))
			$where = "WHERE " . $fluid->php_escape_string($query_where);
		else
			$where = NULL;

		if($mode == "manufacturers") {
			$mode_where = "mfgid";
		}
		else {
			$mode_where = "catid";
		}

		//$fluid->php_db_query("SELECT " . $fluid_mode->X . ".*, (SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id) AS product_count FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");
		$fluid->php_db_query("SELECT " . $fluid_mode->X . ".* FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");

		$cats_array = NULL;

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $value) {
				// This is a root category.
				if(!isset($value[$fluid_mode->X . '_parent_id']))
					$cats_array['root']['childs'][$value[$fluid_mode->id]] = $value;
				else
					$cats_array[$value[$fluid_mode->X . '_parent_id']]['childs'][$value[$fluid_mode->id]] = $value; // Has a parent so this is a child.
			}
		}

		$return = "";
		$tmp_array = Array();
		$action_array = Array();

		if(isset($cats_array)) {
			if(isset($cats_array['root']['childs'])) {
				// Loop through the parents and build them.
				foreach($cats_array['root']['childs'] as $parent_id => $parent) {
						$action_button = "<div id='dropdown-cat' name='dropdown-cat-id-" . $parent[$fluid_mode->id] . "' style='display:inline-block;'>
						  </div><div id='dropdown-cat' style='display: inline-block; font-family: sans-serif;'>" . $parent[$fluid_mode->name] . "</div>";
						$action_array[$parent[$fluid_mode->id]] = $action_button;

					// If the category is disabled, lets load the lock badge.
					if($parent[$fluid_mode->enable] == 0)
						$disable_style = "block";
					else
						$disable_style = "none";

					$eye = "<span id='category-badge-select-lock-" . $parent[$fluid_mode->id] . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					// Encoded data of a empty div. Stored into a data attribute which the javascript code can grab when required.
					$data_crumb['parent'] = base64_encode("fluid-category-listing-childs-" . $parent[$fluid_mode->id]);
					$data_crumb['innerHTML'] = base64_encode("<div id='cat-parent-empty-" . $parent[$fluid_mode->id] . "'>No child " . strtolower($fluid_mode->breadcrumb) . " in this parent " . $fluid_mode->mode_name_real . ".</div>");

					$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $parent[$fluid_mode->id] . "'><li><div class='list-group-item moveparent' style='border: 1px solid #BBBBBB; background-color: #DDDDDD;'><span id='category-span-open-" . $parent[$fluid_mode->id] . "-alt' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>" . $eye . "</div><div name='fluid-category-listing-childs' data-crumb='" . base64_encode(json_encode($data_crumb)) . " ' id='fluid-category-listing-childs-" . $parent[$fluid_mode->id] . "' style='padding: 5px 5px 5px 5px; border: 1px solid #DDDDDD; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;'>";

					$parent[$fluid_mode->name] = base64_encode($parent[$fluid_mode->name]);
					$parent[$fluid_mode->seo] = base64_encode($parent[$fluid_mode->seo]);
					$parent[$fluid_mode->desc] = base64_encode($parent[$fluid_mode->desc]);
					$parent['mode'] = $mode;

					// This hidden div is making our sortables +1 higher in the sorting update in php_sortable_categories_update(); if it is put after the first <ul> in parent. It needs to be before the first <ul>. $return_last solves the problem as we merge it after.
					// This is used by js_sortable_categories() and js_sortable_categories_update() for passing some data.
					$return_last = "<div id='category-list-div-" . $parent[$fluid_mode->id] . "-data' style='display:none;'>" . base64_encode(json_encode($parent)) . "</div>";

					$data_tmp = php_html_categories_child_selector($cats_array, $parent_id, $fluid_mode, $selection_data, $f_quantity);
					// In case anyone wonders if the index keys overlap? According to php.net - "The + operator returns the right-hand array appended to the left-hand array; for keys that exist in both arrays, the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored.
					$action_array = $action_array + $data_tmp['action_array'];
					$return .= $data_tmp['html'];
					$return .= "</div>" . $return_last . "</li></ul>";
				}
			}
			else {
				// At the moment, only php_category_create_and_edit() uses this while in edit mode to refresh a updated category.
				foreach($cats_array as $parent_id => $parent) {
					$data_tmp = php_html_categories_child_selector($cats_array, $parent_id, $fluid_mode, $selection_data, $f_quantity);
					// In case anyone wonders if the index keys overlap? According to php.net - "The + operator returns the right-hand array appended to the left-hand array; for keys that exist in both arrays, the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored.
					$action_array = $action_array + $data_tmp['action_array'];
					$return .= $data_tmp['html'];
					$data['return_last'] = base64_encode(json_encode($parent));
				}

			}
		}

		$data['html'] = $return;
		$data['action_html'] = $action_array;
		$data['error'] = $fluid->db_error;

		$fluid->php_db_commit();

		return $data;
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_html_categories_child_selector($cats_array, $parent_id, $fluid_mode, $selection_data = NULL, $f_quantity = FALSE) {
	$return = NULL;
	$action_array = Array();

	$f_cat_select_count_array = NULL;
	if(isset($selection_data)) {
		$f_cat_select_count = (array)json_decode(base64_decode($selection_data));
		$f_cat_select_count = (array)($f_cat_select_count['c_selection']);

		foreach($f_cat_select_count as $f_tmp_select_count_key => $f_tmp_select_count)
			$f_cat_select_count_array[$f_tmp_select_count_key] = $f_tmp_select_count;
	}

	if(isset($cats_array[$parent_id]['childs'])) {
		// Loop through childs and build them.
		foreach($cats_array[$parent_id]['childs'] as $c_id => $value) {
			$temp_url = $_SERVER['SERVER_NAME'] . "/" . FLUID_SELECTOR_ADMIN;
			$mode_array = base64_encode(json_encode(Array($fluid_mode->X_id => $value[$fluid_mode->id], "mode" => $fluid_mode->mode)));

			$temp_data = "load=true&function=php_load_category_products_selector&data=" . $mode_array;
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $value[$fluid_mode->id] . "'><li>"; //list-style: none; removes bullet poins on <ul> lists. <ul> and <li> needed for sortable to work properly with nested divs in the orders I want it to be. padding-left needs to be reset as well to remove indents.

				$f_cat_select_items = 0;

				if(isset($f_cat_select_count_array[$value[$fluid_mode->id]])) {
					if($f_cat_select_count_array[$value[$fluid_mode->id]] > 0) {
						$return .= "<div id='category-selector-a-" . $value[$fluid_mode->id] . "' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_category_stack(\"" . $value[$fluid_mode->id] . "-alt\", \"" . $temp_url . "\", \"" . $temp_data . "\", \"category-div-" . $value[$fluid_mode->id] . "-alt\", event, FluidSelector.v_selection.p_selection, " . $f_quantity . ");' class='list-group-item movecategory'>";

						$f_cat_select_items = $f_cat_select_count_array[$value[$fluid_mode->id]];
					}
					else {
						$return .= "<div id='category-selector-a-" . $value[$fluid_mode->id] . "' style='height: 40px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_category_stack(\"" . $value[$fluid_mode->id] . "-alt\", \"" . $temp_url . "\", \"" . $temp_data . "\", \"category-div-" . $value[$fluid_mode->id] . "-alt\", event, FluidSelector.v_selection.p_selection, " . $f_quantity . ");' class='list-group-item movecategory'>";
					}
				}
				else {
					$return .= "<div id='category-selector-a-" . $value[$fluid_mode->id] . "' style='height: 40px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_category_stack(\"" . $value[$fluid_mode->id] . "-alt\", \"" . $temp_url . "\", \"" . $temp_data . "\", \"category-div-" . $value[$fluid_mode->id] . "-alt\", event, FluidSelector.v_selection.p_selection, " . $f_quantity . ");' class='list-group-item movecategory'>";
				}
					//$return .= "<span id='category-selector-badge-count-" . $value[$fluid_mode->id] . "' class='badge'>" . $value['product_count'] . "</span>";

					if($f_cat_select_items > 0)
						$return .= "<span id='category-selector-badge-select-count-" . $value[$fluid_mode->id] . "' class='badge'>" . $f_cat_select_items . " selected</span>";
					else
						$return .= "<span id='category-selector-badge-select-count-" . $value[$fluid_mode->id] . "' class='badge' style='display:none;'></span>";

					// If the category is disabled, lets load the lock badge.
					if($value[$fluid_mode->enable] == 0)
						$disable_style = "block";
					else
						$disable_style = "none";

					$return .= "<span id='category-selector-badge-select-lock-" . $value[$fluid_mode->id] . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$action_button = "<div id='dropdown-cat' name='dropdown-cat-id-" . $value[$fluid_mode->id] . "' class='dropdown' style='display:inline;'>
					<div id='dropdown-cat' style='display: inline-block;'>" . $value[$fluid_mode->name] . "
					</div>

					  </div>";
					$action_array[$value[$fluid_mode->id]] = $action_button;

					$return .= " <span id='category-span-closed-" . $value[$fluid_mode->id] . "-alt' class=\"glyphicon glyphicon-expand\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
					$return .= " <span id='category-span-open-" . $value[$fluid_mode->id] . "-alt' style='display:none;' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
				$return .= "</div>";

				// Need to encode these as they can break json arrays.
				// These values could probably be removed. I have to double check. I think only the id and sort order is required. This would save some bandwidth and increase speeds.
				$value[$fluid_mode->name] = base64_encode($value[$fluid_mode->name]);
				$value[$fluid_mode->seo] = base64_encode($value[$fluid_mode->seo]);
				$value[$fluid_mode->desc] = base64_encode($value[$fluid_mode->desc]);
				$value['mode'] = $fluid_mode->mode;

				// This is used by js_sortable_categories() and js_sortable_categories_update() for passing some data.
				$return .= "<div id='category-list-div-" . $value[$fluid_mode->id] . "-data' style='display:none;'>" . base64_encode(json_encode($value)) . "</div>";

				$return .= "<div id='category-div-" . $value[$fluid_mode->id] . "-alt'></div>";
			$return .= "</li></ul>";
			$tmp_array['categories'][$value[$fluid_mode->id]]['div'] = base64_encode("#category-list-div-" . $value[$fluid_mode->id]);
		}
	}
	else
		$return .= "<div id='cat-parent-empty-" . $parent_id . "'>No child " . strtolower($fluid_mode->breadcrumb) . " in this parent " . $fluid_mode->mode_name_real . ".</div>";

	$data_return['action_array'] = $action_array;
	$data_return['html'] = $return;

	return $data_return;
}

function php_load_category_products_selector() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();
		$data = json_decode(base64_decode($_REQUEST['data']));
		$fluid_mode = new Fluid_Mode($data->mode);

		if($data->mode == "manufacturers")
			$fluid->php_db_query("SELECT p.*, c.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE p.p_mfgid = '" . $fluid->php_escape_string($data->{$fluid_mode->X_id}) . "' ORDER BY p_mfgid ASC, p_sortorder_mfg ASC");
		else
			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id WHERE p.p_catid = '" . $fluid->php_escape_string($data->{$fluid_mode->X_id}) . "' ORDER BY p_catid ASC, p_sortorder ASC");

		/*
		$tmp_array = Array();
		if(isset($fluid->db_array))
			foreach($fluid->db_array as $value)
				$tmp_array[] = $value;
		*/

		if(isset($_REQUEST['selection']))
			$selection_data = $_REQUEST['selection'];
		else
			$selection_data = NULL;

		$f_quantity = FALSE;
		if(isset($_REQUEST['f_quantity'])) {
			if($_REQUEST['f_quantity'] == 1) {
				$f_quantity = TRUE;
			}
		}

		if(isset($fluid->db_array))
			$return = php_html_item_list($fluid->db_array, $selection_data, $data->mode, $f_quantity);
		else
			$return = php_html_item_list(NULL, $selection_data, $data->mode, $f_quantity);


		$execute_functions[0]['function'] = "js_category_stack_open";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("div" => base64_encode("category-div-" . $data->{$fluid_mode->X_id} . "-alt"), "innerHTML" => base64_encode($return), "cid" => base64_encode($data->{$fluid_mode->X_id} . "-alt"))));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}
?>
