<?php
// Michael Rajotte - 2016 June
// fluid.define.html.php

if(!isset($_SESSION))
	session_start();

if(!isset($_SESSION['fluid_uri']))
	$_SESSION['fluid_uri'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/";

// If we are in admin mode, set default table column views if neccessary.
if(isset($_SESSION['fluid_admin'])) {
	if(empty($_SESSION['f_admin_columns'])) {
		$_SESSION['f_admin_columns']['f-cell-select'] = Array("data" => "table-cell", "column_name" => "Select");
		$_SESSION['f_admin_columns']['f-cell-image'] = Array("data" => "table-cell", "column_name" => "Image");
		$_SESSION['f_admin_columns']['f-cell-manufacturer'] = Array("data" => "table-cell", "column_name" => "Manufacturer");
		$_SESSION['f_admin_columns']['f-cell-category'] = Array("data" => "table-cell", "column_name" => "Category");
		$_SESSION['f_admin_columns']['f-cell-name'] = Array("data" => "table-cell", "column_name" => "Name");
		$_SESSION['f_admin_columns']['f-cell-upc'] = Array("data" => "table-cell", "column_name" => "UPC/EAN");
		$_SESSION['f_admin_columns']['f-cell-code'] = Array("data" => "table-cell", "column_name" => "Mfg code");
		$_SESSION['f_admin_columns']['f-cell-length'] = Array("data" => "table-cell", "column_name" => "Length");
		$_SESSION['f_admin_columns']['f-cell-width'] = Array("data" => "table-cell", "column_name" => "Width");
		$_SESSION['f_admin_columns']['f-cell-height'] = Array("data" => "table-cell", "column_name" => "Height");
		$_SESSION['f_admin_columns']['f-cell-weight'] = Array("data" => "table-cell", "column_name" => "Weight");
		$_SESSION['f_admin_columns']['f-cell-stock'] = Array("data" => "table-cell", "column_name" => "Stock");
		$_SESSION['f_admin_columns']['f-cell-costavg'] = Array("data" => "table-cell", "column_name" => "Cost Avg");
		$_SESSION['f_admin_columns']['f-cell-cost'] = Array("data" => "table-cell", "column_name" => "Cost");
		$_SESSION['f_admin_columns']['f-cell-margin'] = Array("data" => "table-cell", "column_name" => "Margin");
		$_SESSION['f_admin_columns']['f-cell-price'] = Array("data" => "table-cell", "column_name" => "Price");
		$_SESSION['f_admin_columns']['f-cell-discountstart'] = Array("data" => "none", "column_name" => "Discount Start");
		$_SESSION['f_admin_columns']['f-cell-discountend'] = Array("data" => "none", "column_name" => "Discount End");
		$_SESSION['f_admin_columns']['f-cell-discountprice'] = Array("data" => "none", "column_name" => "Discount Price");
	}

	if(empty($_SESSION['f_admin_item_filters'])) {
		$_SESSION['f_admin_item_filters']['f-filter-enabled'] = Array("data" => TRUE, "filter_name" => "Enabled", "query" => "p.p_enable = '1'", "column" => "p_enable");
		$_SESSION['f_admin_item_filters']['f-filter-disabled'] = Array("data" => TRUE, "filter_name" => "Disabled", "query" => "p.p_enable = '0'", "column" => "p_enable");
		$_SESSION['f_admin_item_filters']['f-filter-discontinued'] = Array("data" => TRUE, "filter_name" => "Discontinued", "query" => "p.p_enable = '2'", "column" => "p_enable");
		$_SESSION['f_admin_item_filters']['f-filter-instock'] = Array("data" => TRUE, "filter_name" => "In Stock", "query" => "p.p_stock > '0'", "column" => "p_stock");
		$_SESSION['f_admin_item_filters']['f-filter-nostock'] = Array("data" => TRUE, "filter_name" => "No Stock", "query" => "p.p_stock < '1'", "column" => "p_stock");
		$_SESSION['f_admin_item_filters']['f-filter-preorders'] = Array("data" => TRUE, "filter_name" => "Preorders", "query" => "p.p_preorder = '1'", "column" => "p_preorder");
		$_SESSION['f_admin_item_filters']['f-filter-nopreorders'] = Array("data" => TRUE, "filter_name" => "No Preorders", "query" => "p.p_preorder = '0'", "column" => "p_preorder");
		$_SESSION['f_admin_item_filters']['f-filter-specialorder'] = Array("data" => TRUE, "filter_name" => "Special Order", "query" => "p.p_special_order = '1'", "column" => "p_special_order");
		$_SESSION['f_admin_item_filters']['f-filter-nospecialorder'] = Array("data" => TRUE, "filter_name" => "No Special Order", "query" => "p.p_special_order = '0'", "column" => "p_special_order");
		$_SESSION['f_admin_item_filters']['f-filter-rebate'] = Array("data" => TRUE, "filter_name" => "Rebate", "query" => "p.p_rebate_claim = '1'", "column" => "p_rebate_claim");
		$_SESSION['f_admin_item_filters']['f-filter-norebate'] = Array("data" => TRUE, "filter_name" => "No Rebate", "query" => "p.p_rebate_claim = '0'", "column" => "p_rebate_claim");
		$_SESSION['f_admin_item_filters']['f-filter-rental'] = Array("data" => TRUE, "filter_name" => "Rental", "query" => "p.p_rental = '1'", "column" => "p_rental");
		$_SESSION['f_admin_item_filters']['f-filter-norental'] = Array("data" => TRUE, "filter_name" => "No Rental", "query" => "p.p_rental = '0'", "column" => "p_rental");
		$_SESSION['f_admin_item_filters']['f-filter-trending'] = Array("data" => TRUE, "filter_name" => "Trending", "query" => "p.p_trending = '1'", "column" => "p_trending");
		$_SESSION['f_admin_item_filters']['f-filter-nottrending'] = Array("data" => TRUE, "filter_name" => "Not Trending", "query" => "p.p_trending = '0'", "column" => "p_trending");
		$_SESSION['f_admin_item_filters']['f-filter-freeshipping'] = Array("data" => TRUE, "filter_name" => "Free Shipping", "query" => "p.p_freeship = '1'", "column" => "p_freeship");
		$_SESSION['f_admin_item_filters']['f-filter-nofreeshipping'] = Array("data" => TRUE, "filter_name" => "No Free Shipping", "query" => "p.p_freeship = '0'", "column" => "p_freeship");
		$_SESSION['f_admin_item_filters']['f-filter-shippickup'] = Array("data" => TRUE, "filter_name" => "Ship & Pickup", "query" => "p.p_instore = '0'", "column" => "p_instore");
		$_SESSION['f_admin_item_filters']['f-filter-pickuponly'] = Array("data" => TRUE, "filter_name" => "Pickup Only", "query" => "p.p_instore = '1'", "column" => "p_instore");
		$_SESSION['f_admin_item_filters']['f-filter-shippedonly'] = Array("data" => TRUE, "filter_name" => "Shipped Only", "query" => "p.p_instore = '2'", "column" => "p_instore");

		$_SESSION['f_admin_item_filters_enabled'] = FALSE; // Reset all filters to active. Query's will ignore the filters if all active.
		$_SESSION['f_admin_item_filters_query'] = NULL; // This gets built in php_fluid_item_filters_save() -> fluid.loader.php.
	}
}

function php_html_navbar_right($mode = NULL) {
	$detect = new Mobile_Detect;

	if($detect->isTablet()) {
		$f_large_buttons = " fluid-large-menu";
		//$f_large_action = " btn-lg";
		$f_large_action = NULL;
		$f_special = NULL;
	}
	else {
		$f_large_buttons = NULL;
		$f_large_action = NULL;
		$f_special = "style='font-size: 10px;";
	}

	$add_product_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_product_creator_editor")));

	if(!$mode) {
		$add_parent_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor&parent=true")));
		$menu_button = "<li role='separator' class='divider'></li><li><a onClick='js_fluid_ajax(\"" . $add_parent_category_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add parent category</a></li>";

		$add_child_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor")));
		$menu_button .= "<li><a onClick='js_fluid_ajax(\"" . $add_child_category_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add child category</a></li>";

		if(isset($_SESSION['f_show_data'])) {
			if($_SESSION['f_show_data'] == TRUE) {
				$temp_url_categories = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories")));

				$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_categories . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-close' aria-hidden='true'></span> <span id='li-barcode-html'>Hide category data</span></a></li>";
			}
			else { // --> Fastest.
				$temp_url_categories = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&f_show_data=true")));

				$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_categories . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-open' aria-hidden='true'></span> <span id='li-category-html'>Show category data</span></a></li>";
			}
		}
		else { // --> Fastest.
			$temp_url_categories = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&f_show_data=true")));

			$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_categories . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-open' aria-hidden='true'></span> <span id='li-category-html'>Show category data</span></a></li>";
		}

	}
	else if($mode != "items" && $mode != "import" && $mode != "banners" && $mode != "accounts" && $mode != "feedback" && $mode != "logs") {
		$add_parent_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor&mode=manufacturers&parent=true")));
		$menu_button = "<li role='separator' class='divider'></li><li><a onClick='js_fluid_ajax(\"" . $add_parent_category_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add parent manufacturer</a></li>";

		$add_child_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor&mode=manufacturers")));
		$menu_button .= "<li><a onClick='js_fluid_ajax(\"" . $add_child_category_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add child manufacturer</a></li>";

		if(isset($_SESSION['f_show_data'])) {
			if($_SESSION['f_show_data'] == TRUE) {
				$temp_url_manufacturers = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&mode=manufacturers")));

				$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_manufacturers . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-close' aria-hidden='true'></span> <span>Hide manufacturer data</span></a></li>";
			}
			else { // --> Fastest.
				$temp_url_manufacturers = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&f_show_data=true&mode=manufacturers")));

				$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_manufacturers . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-open' aria-hidden='true'></span> <span>Show manufacturer data</span></a></li>";
			}
		}
		else { // --> Fastest.
			$temp_url_manufacturers = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&f_show_data=true&mode=manufacturers")));

			$f_show_btn = "<li role='separator' class='divider'></li><li><a onClick='FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax(\"" . $temp_url_manufacturers . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-glyphicon glyphicon-eye-open' aria-hidden='true'></span> <span>Show manufacturer data</span></a></li>";
		}
	}
	else
		$menu_button = NULL;

	if($mode == "items") {
		//$scan_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_scanning_items_modal&fmode=plus&mode=items")));
		$scan_button = "<li><a onClick='js_scan_modal_prep(\"plus\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-barcode' aria-hidden='true'></span> Add stock <span class='glyphicon glyphicon-plus' aria-hidden='true' " . $f_special . "'></span></a></li>";

		//$scan_link_minus = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_scanning_items_modal&fmode=minus&mode=items")));
		$scan_button .= "<li><a onClick='js_scan_modal_prep(\"minus\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-barcode' aria-hidden='true'></span> Remove stock <span class='glyphicon glyphicon-minus' aria-hidden='true' " . $f_special . "'></span></a></li>";

		//$scan_link_check = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_scanning_items_modal&fmode=none&mode=items")));
		$scan_button .= "<li><a onClick='js_scan_modal_prep(\"none\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-barcode' aria-hidden='true'></span> Check stock <span class='glyphicon glyphicon-check' aria-hidden='true' " . $f_special . "'></span></a></li>";

		$print_items = "<li id='li-f-items'><a onClick='js_fluid_item_filter();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> <span id='li-f-items-html'>Item Filter</span></a></li><li role='separator' class='divider'></li>
				<li id='li-items-print'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"category-div-items\").innerHTML; window.print();'><i class=\"fa fa-print\" aria-hidden=\"true\"></i><div style='display: inline-block; padding-left: 4px;'>Print page</div></a></li>";
	}
	else {
		$scan_button = NULL;
		$print_items = NULL;
	}


	// Actions editor button.
	if($mode == "banners") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
		define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
			  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
			  </button>
			  <ul class='dropdown-menu" . $f_large_buttons . "'>" .
				$scan_button .
			  "
				<li><a onClick='js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners_creator"))) . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add banner</a></li>
				<li id='li-editmulti' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER . "\", \"load=true&function=php_load_multi_banner_editor&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span> <span id='li-editmulti-html'>Edit banners</span></a></li>
				<li role='separator' class='divider'></li>
				<li id='li-delete' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER . "\", \"load=true&function=php_load_banners_delete&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span> <span id='li-delete-html'>Delete banner</span></a></li>
				" . $menu_button . "
			  </ul>
			</div>");
	}
	else if($mode == "accounts") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
			define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
				  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
				  </button>
				  <ul class='dropdown-menu" . $f_large_buttons . "'>" .
					$scan_button .
				  "
					<li id='li-emails' class='disabled'><a onClick='js_fluid_accounts_load_email_creator();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-envelope' aria-hidden='true'></span><div id='li-emails-html' style='display: inline-block; padding-left: 4px;'>Send email</div></a></li>
					<li role='separator' class='divider'></li>
					<li id='li-account-print'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"category-div-users\").innerHTML; window.print();'><i class=\"fa fa-print\" aria-hidden=\"true\"></i><div style='display: inline-block; padding-left: 4px;'>Print page</div></a></li>
				  </ul>
				</div>");
	}
	else if($mode == "feedback") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
			define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
				  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
				  </button>
				  <ul class='dropdown-menu" . $f_large_buttons . "'>" .
					$scan_button .
				  "
					<li id='li-account-print'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"category-div-feedback\").innerHTML; window.print();'><i class=\"fa fa-print\" aria-hidden=\"true\"></i><div style='display: inline-block; padding-left: 4px;'>Print page</div></a></li>
				  </ul>
				</div>");
	}
	else if($mode == "logs") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
			define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
				  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
				  </button>
				  <ul class='dropdown-menu" . $f_large_buttons . "'>" .
					$scan_button .
				  "
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-list' aria-hidden='true'></span> All logs</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=1"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-search' aria-hidden='true'></span> Search logs</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=6"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><i class='fa fa-truck' aria-hidden='true'></i> Shipping log</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=2"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-list-alt' aria-hidden='true'></span> Checkout log</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=3"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-inbox' aria-hidden='true'></span> Order log</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=5"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Cart log</a></li>
					<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load&d_mode=4"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-barcode' aria-hidden='true'></span> Admin scan log</a></li>
					<li role='separator' class='divider'></li>
					<li id='li-log-print'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"category-div-users\").innerHTML; window.print();'><i class=\"fa fa-print\" aria-hidden=\"true\"></i><div style='display: inline-block; padding-left: 4px;'>Print page</div></a></li>
					<li role='separator' class='divider'></li>
					<li id='li-log-delete' class='disabled'><a onClick='js_fluid_logs_delete();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span><div id='li-log-delete-html' style='display: inline-block; padding-left: 4px;'>Delete log</div></a></li>
				  </ul>
				</div>");
	}
	else if($mode == "orders") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
		define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
			  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
			  </button>
			  <ul class='dropdown-menu" . $f_large_buttons . "'>
				<li><a onClick='FluidVariables.d_mode = null; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-list' aria-hidden='true'></span> All orders</a></li>
				<li><a onClick='FluidVariables.d_mode = 1; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=1"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span> Processing</a></li>
				<li><a onClick='FluidVariables.d_mode = 6; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=6"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-gift' aria-hidden='true'></span> Pre-ordered</a></li>
				<li><a onClick='FluidVariables.d_mode = 10; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=10"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Shipped / Pickups</a></li>
				<li><a onClick='FluidVariables.d_mode = 2; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=2"))). "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-road' aria-hidden='true'></span> Shipped</a></li>
				<li><a onClick='FluidVariables.d_mode = 3; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=3"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-random' aria-hidden='true'></span> Pickups</a></li>
				<li><a onClick='FluidVariables.d_mode = 4; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=4"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-pushpin' aria-hidden='true'></span> Refunds</a></li>
				<li><a onClick='FluidVariables.d_mode = 5; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=5"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-ban-circle' aria-hidden='true'></span> Cancelled</a></li>
				<li><a onClick='FluidVariables.d_mode = 0; js_fluid_ajax(\"" . base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders&d_mode=0"))) . "\", \"content-div\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-warning-sign' aria-hidden='true'></span> Errors</a></li>
				<li role='separator' class='divider'></li>
				<li id='li-orders-print'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"category-div-orders\").innerHTML; window.print();'><i class=\"fa fa-print\" aria-hidden=\"true\"></i><div style='display: inline-block; padding-left: 4px;'>Print page</div></a></li>
			  </ul>
			</div>");
	}
	else if($mode == "import") {
		if(!defined('HTML_NAVBAREDITORRIGHT'))
		define("HTML_NAVBAREDITORRIGHT", "<div class='btn-group'>
			  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
			  </button>
			  <ul class='dropdown-menu" . $f_large_buttons . "'>
				<li id='li-remove-row' class='disabled'><a onClick='js_fluid_import_remove_rows();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> <span id='li-remove-row-html'>Remove Row</span></a></li>

				<li id='li-remove-column' class='disabled'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> <span id='li-remove-column-html'>Remove Column</span></a></li>

				<li role='separator' class='divider'></li>
					<li id='li-select-row' class='disabled'><a onClick='js_staging_select_all();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-check' aria-hidden='true'></span> <span id='li-select-row-html'>Select existing items</span></a></li>

					<li id='li-select-items' class='disabled'><a onClick='js_fluid_switch_to_item_mode();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-list-alt' aria-hidden='true'></span> <span id='li-select-items'>Switch to items module</span></a></li>

					<li id='li-scan-duplicates' class='disabled'><a onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-random' aria-hidden='true'></span> <span id='li-scan-duplicates-html'>Scan for duplicates</span></a></li>

					<li id='li-refresh-items'><a onClick='js_fluid_import_staging_refresh();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span> <span id='li-refresh-items-html'>Refresh table</span></a></li>

					<li id='li-scan-items' class='disabled'><a onClick='js_fluid_scan_items_menu(\"p_mfgcode\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-retweet' aria-hidden='true'></span> <span id='li-scan-items-html'>Scan for items <div style='display: inline-block; font-size: 70%'>(p_mfgcode)</div></span></a></li>

					<li id='li-scan-items-mfg-number' class='disabled'><a onClick='js_fluid_scan_items_menu(\"p_mfg_number\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-retweet' aria-hidden='true'></span> <span id='li-scan-items-html'>Scan for items <div style='display: inline-block; font-size: 70%'>(p_mfg_number)</div></span></a></li>

				<li role='separator' class='divider'></li>
					<li id='li-merge-data' class='disabled'><a onClick='js_fluid_merge_data_confirm();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-import' aria-hidden='true'></span> <span id='li-merge-data-html'>Merge data</span></a></li>

				<li role='separator' class='divider'></li>
					<li id='li-import'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_IMPORT_ADMIN . "\", \"load=true&function=php_load_import_uploader\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-transfer' aria-hidden='true'></span> <span id='li-import-html'>Import CSV</span></a></li>
				" . $menu_button . "
			  </ul>
			</div>");
	}
	else {
		if(!defined('HTML_NAVBAREDITORRIGHT')) {
			$f_html = "<div class='btn-group'>
			  <button type='button' class='btn" . $f_large_action . " btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span> Actions <span class='caret'></span>
			  </button>
			  <ul class='dropdown-menu" . $f_large_buttons . "'>" .
				$scan_button .
			  "
				<li><a onClick='js_fluid_ajax(\"" . $add_product_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add item</a></li>
				<li id='li-move' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_load_product_move&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-move' aria-hidden='true'></span> <span id='li-move-html'>Move item</span></a></li>
				<li id='li-copy' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_load_product_copy&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-duplicate' aria-hidden='true'></span> <span id='li-copy-html'>Copy item</span></a></li>
				<li id='li-editmulti' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_load_multi_item_editor&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span> <span id='li-editmulti-html'>Edit items</span></a></li>
				<li id='li-attribute' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_ATTRIBUTES_ADMIN . "\", \"load=true&function=php_load_set_attribute&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-list' aria-hidden='true'></span> <span id='li-attribute-html'>Set attribute</span></a></li>
				";

			if(isset($f_show_btn))
				$f_html .= $f_show_btn;

			$f_html .= "<li role='separator' class='divider'></li>
				<li id='li-barcode' class='disabled'><a onClick='js_fluid_barcode_modal();' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-barcode' aria-hidden='true'></span> <span id='li-barcode-html'>Print barcode</span></a></li>
				<li id='li-export' class='disabled'><a onClick='js_fluid_export(\"fluid\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> <span id='li-export-html'>Export selected</span></a></li>
				<li id='li-export-google' class='disabled'><a onClick='js_fluid_export(\"google\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> <span id='li-export-google-html'>Export selected <div style='display: inline-block; font-size: 70%;'>(Google)</div></span></a></li>
				<li role='separator' class='divider'></li>
				<li id='li-export-stock-all'><a onClick='js_fluid_export(\"stock\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> <span id='li-export-all-stock-html'>Export All <div style='display: inline-block; font-size: 70%;'>(In stock)</div></span></a></li>
				<li id='li-export-all'><a onClick='js_fluid_export(\"all\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> <span id='li-export-all-html'>Export All</span></a></li>
				<li role='separator' class='divider'></li>";

				//<li id='li-product-linking'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_product_linking_editor&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-link' aria-hidden='true'></span> <span id='li-product-linking-html'>Product Linking Editor</span></a></li>
				$f_html .= "
				<li id='li-download-images' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_load_search_images&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-picture' aria-hidden='true'></span> <span id='li-download-images-html'>Item downloader</span></a></li>
				<li id='li-f-columns'><a onClick='js_fluid_columns(\"all\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-sort-by-attributes-alt' aria-hidden='true'></span> <span id='li-f-columns-html'>Hide/Unhide columns</span></a></li>
				" . $print_items . "
				<li role='separator' class='divider'></li>
				<li id='li-delete' class='disabled'><a onClick='js_load_php_fluid_loader(\"" . $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER . "\", \"load=true&function=php_load_product_delete&mode=" . $mode . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span> <span id='li-delete-html'>Delete item</span></a></li>
				" . $menu_button . "
			  </ul>
			</div>";

			define("HTML_NAVBAREDITORRIGHT", $f_html);
		}
	}
	return HTML_NAVBAREDITORRIGHT;
}

function php_html_admin_search_input($mode = NULL) {
	$detect = new Mobile_Detect;

	if($detect->isTablet())
		$f_large_buttons = " fluid-admin-search-btn-large";
	else
		$f_large_buttons = NULL;

	// Admin search bar input.
	if($mode == "import" || $mode == "banners" || $mode == "logs" || $mode == "feedback") {
		$f_disabled = "disabled";
		$f_keydown = "disabled";
	}
	else {
		$f_disabled = "onClick='js_search(\"" . $mode . "\", document.getElementById(\"search-input\").value); document.getElementById(\"search-input\").value = \"\";' type=\"submit\"";
		$f_keydown = "onkeydown = 'if(event.keyCode == 13){js_search(\"" . $mode . "\", document.getElementById(\"search-input\").value); document.getElementById(\"search-input\").value = \"\";}'";
	}

	if(!defined('HTML_ADMIN_SEARCH_INPUT'))
		define("HTML_ADMIN_SEARCH_INPUT", "
		<div class=\"navbar-form navbar-left\" style='margin: 0px 0px 0px 0px;' role=\"search\">
			 <div class=\"input-group\">
				 <input id='search-input' type=\"text\" class=\"form-control" . $f_large_buttons . "\" placeholder=\"Search...\" " . $f_keydown . ">
					<span class=\"input-group-btn\">
						<button id='search-button' " . $f_disabled . " class=\"btn btn-default" . $f_large_buttons . "\">
							<span id='search-glyph' class=\"glyphicon glyphicon-search\"></span>
						</button>
					</span>
			 </div>
		</div>
		");

	return HTML_ADMIN_SEARCH_INPUT;
}

if(empty($_SESSION['fluid_uri'])) {
	$f_url = WWW_SITE;
}
else {
	$f_url = $_SESSION['fluid_uri'];
}

	define("OPTION_PROVINCE_CANADA_LIST", "
		<option disabled selected style='display: none;'> -- select a province -- </option>
		<option value=\"Alberta\">Alberta</option>
		<option value=\"British Columbia\">British Columbia</option>
		<option value=\"Manitoba\">Manitoba</option>
		<option value=\"New Brunswick\">New Brunswick</option>
		<option value=\"Newfoundland and Labrador\">Newfoundland and Labrador</option>
		<option value=\"Nova Scotia\">Nova Scotia</option>
		<option value=\"Ontario\">Ontario</option>
		<option value=\"Prince Edward Island\">Prince Edward Island</option>
		<option value=\"Quebec\">Quebec</option>
		<option value=\"Saskatchewan\">Saskatchewan</option>
		<option value=\"Northwest Territories\">Northwest Territories</option>
		<option value=\"Nunavut\">Nunavut</option>
		<option value=\"Yukon\">Yukon</option>
	");

	define("OPTION_USA_STATES_LIST", "
		<option disabled selected style='display: none;'> -- select a state -- </option>
		<option value=\"Alabama\">Alabama</option>
		<option value=\"Alaska\">Alaska</option>
		<option value=\"Arizona\">Arizona</option>
		<option value=\"Arkansas\">Arkansas</option>
		<option value=\"California\">California</option>
		<option value=\"Colorado\">Colorado</option>
		<option value=\"Connecticut\">Connecticut</option>
		<option value=\"Delaware\">Delaware</option>
		<option value=\"Florida\">Florida</option>
		<option value=\"Georgia\">Georgia</option>
		<option value=\"Hawaii\">Hawaii</option>
		<option value=\"Idaho\">Idaho</option>
		<option value=\"Illinois\">Illinois</option>
		<option value=\"Indiana\">Indiana</option>
		<option value=\"Iowa\">Iowa</option>
		<option value=\"Kansas\">Kansas</option>
		<option value=\"Kentucky\">Kentucky</option>
		<option value=\"Louisiana\">Louisiana</option>
		<option value=\"Maine\">Maine</option>
		<option value=\"Maryland\">Maryland</option>
		<option value=\"Massachusetts\">Massachusetts</option>
		<option value=\"Michigan\">Michigan</option>
		<option value=\"Minnesota\">Minnesota</option>
		<option value=\"Mississippi\">Mississippi</option>
		<option value=\"Missouri\">Missouri</option>
		<option value=\"Montana\">Montana</option>
		<option value=\"Nebraska\">Nebraska</option>
		<option value=\"Nevada\">Nevada</option>
		<option value=\"New Hampshire\">New Hampshire</option>
		<option value=\"New Jersey\">New Jersey</option>
		<option value=\"New Mexico\">New Mexico</option>
		<option value=\"New York\">New York</option>
		<option value=\"North Carolina\">North Carolina</option>
		<option value=\"North Dakota\">North Dakota</option>
		<option value=\"Ohio\">Ohio</option>
		<option value=\"Oklahoma\">Oklahoma</option>
		<option value=\"Oregon\">Oregon</option>
		<option value=\"Pennsylvania\">Pennsylvania</option>
		<option value=\"Rhode Island\">Rhode Island</option>
		<option value=\"South Carolina\">South Carolina</option>
		<option value=\"South Dakota\">South Dakota</option>
		<option value=\"Tennessee\">Tennessee</option>
		<option value=\"Texas\">Texas</option>
		<option value=\"Utah\">Utah</option>
		<option value=\"Vermont\">Vermont</option>
		<option value=\"Virginia\">Virginia</option>
		<option value=\"Washington\">Washington</option>
		<option value=\"West Virginia\">West Virginia</option>
		<option value=\"Wisconsin\">Wisconsin</option>
		<option value=\"Wyoming\">Wyoming</option>
	");

	define("OPTION_COUNTRY_LIST_ISO_3116_1_ALPHA_LONG_CODES", "
	<option disabled style='display: none;'> -- select a country -- </option>
	<option value=\"Afghanistan\">Afghanistan</option>
	<option value=\"Åland Islands\">Åland Islands</option>
	<option value=\"Albania\">Albania</option>
	<option value=\"Algeria\">Algeria</option>
	<option value=\"American Samoa\">American Samoa</option>
	<option value=\"Andorra\">Andorra</option>
	<option value=\"Angola\">Angola</option>
	<option value=\"Anguilla\">Anguilla</option>
	<option value=\"Antarctica\">Antarctica</option>
	<option value=\"Antigua and Barbuda\">Antigua and Barbuda</option>
	<option value=\"Argentina\">Argentina</option>
	<option value=\"Armenia\">Armenia</option>
	<option value=\"Aruba\">Aruba</option>
	<option value=\"Australia\">Australia</option>
	<option value=\"Austria\">Austria</option>
	<option value=\"Azerbaijan\">Azerbaijan</option>
	<option value=\"Bahamas\">Bahamas</option>
	<option value=\"Bahrain\">Bahrain</option>
	<option value=\"Bangladesh\">Bangladesh</option>
	<option value=\"Barbados\">Barbados</option>
	<option value=\"Belarus\">Belarus</option>
	<option value=\"Belgium\">Belgium</option>
	<option value=\"Belize\">Belize</option>
	<option value=\"Benin\">Benin</option>
	<option value=\"Bermuda\">Bermuda</option>
	<option value=\"Bhutan\">Bhutan</option>
	<option value=\"Bolivia, Plurinational State of\">Bolivia, Plurinational State of</option>
	<option value=\"Bonaire, Sint Eustatius and Saba\">Bonaire, Sint Eustatius and Saba</option>
	<option value=\"Bosnia and Herzegovina\">Bosnia and Herzegovina</option>
	<option value=\"Botswana\">Botswana</option>
	<option value=\"Bouvet Island\">Bouvet Island</option>
	<option value=\"Brazil\">Brazil</option>
	<option value=\"British Indian Ocean Territory\">British Indian Ocean Territory</option>
	<option value=\"Brunei Darussalam\">Brunei Darussalam</option>
	<option value=\"Bulgaria\">Bulgaria</option>
	<option value=\"Burkina Faso\">Burkina Faso</option>
	<option value=\"Burundi\">Burundi</option>
	<option value=\"Cambodia\">Cambodia</option>
	<option value=\"Cameroon\">Cameroon</option>
	<option value=\"Canada\" selected>Canada</option>
	<option value=\"Cape Verde\">Cape Verde</option>
	<option value=\"Cayman Islands\">Cayman Islands</option>
	<option value=\"Central African Republic\">Central African Republic</option>
	<option value=\"Chad\">Chad</option>
	<option value=\"Chile\">Chile</option>
	<option value=\"China\">China</option>
	<option value=\"Christmas Island\">Christmas Island</option>
	<option value=\"Cocos (Keeling) Islands\">Cocos (Keeling) Islands</option>
	<option value=\"Colombia\">Colombia</option>
	<option value=\"Comoros\">Comoros</option>
	<option value=\"Congo\">Congo</option>
	<option value=\"Congo, the Democratic Republic of the\">Congo, the Democratic Republic of the</option>
	<option value=\"Cook Islands\">Cook Islands</option>
	<option value=\"Costa Rica\">Costa Rica</option>
	<option value=\"Côte d'Ivoire\">Côte d'Ivoire</option>
	<option value=\"Croatia\">Croatia</option>
	<option value=\"Cuba\">Cuba</option>
	<option value=\"Curaçao\">Curaçao</option>
	<option value=\"Cyprus\">Cyprus</option>
	<option value=\"Czech Republic\">Czech Republic</option>
	<option value=\"Denmark\">Denmark</option>
	<option value=\"Djibouti\">Djibouti</option>
	<option value=\"Dominica\">Dominica</option>
	<option value=\"Dominican Republic\">Dominican Republic</option>
	<option value=\"Ecuador\">Ecuador</option>
	<option value=\"Egypt\">Egypt</option>
	<option value=\"El Salvador\">El Salvador</option>
	<option value=\"Equatorial Guinea\">Equatorial Guinea</option>
	<option value=\"Eritrea\">Eritrea</option>
	<option value=\"Estonia\">Estonia</option>
	<option value=\"Ethiopia\">Ethiopia</option>
	<option value=\"Falkland Islands (Malvinas)\">Falkland Islands (Malvinas)</option>
	<option value=\"Faroe Islands\">Faroe Islands</option>
	<option value=\"Fiji\">Fiji</option>
	<option value=\"Finland\">Finland</option>
	<option value=\"France\">France</option>
	<option value=\"French Guiana\">French Guiana</option>
	<option value=\"French Polynesia\">French Polynesia</option>
	<option value=\"French Southern Territories\">French Southern Territories</option>
	<option value=\"Gabon\">Gabon</option>
	<option value=\"Gambia\">Gambia</option>
	<option value=\"Georgia\">Georgia</option>
	<option value=\"Germany\">Germany</option>
	<option value=\"Ghana\">Ghana</option>
	<option value=\"Gibraltar\">Gibraltar</option>
	<option value=\"Greece\">Greece</option>
	<option value=\"Greenland\">Greenland</option>
	<option value=\"Grenada\">Grenada</option>
	<option value=\"Guadeloupe\">Guadeloupe</option>
	<option value=\"Guam\">Guam</option>
	<option value=\"Guatemala\">Guatemala</option>
	<option value=\"Guernsey\">Guernsey</option>
	<option value=\"Guinea\">Guinea</option>
	<option value=\"Guinea-Bissau\">Guinea-Bissau</option>
	<option value=\"Guyana\">Guyana</option>
	<option value=\"Haiti\">Haiti</option>
	<option value=\"Heard Island and McDonald Islands\">Heard Island and McDonald Islands</option>
	<option value=\"Holy See (Vatican City State)\">Holy See (Vatican City State)</option>
	<option value=\"Honduras\">Honduras</option>
	<option value=\"Hong Kong\">Hong Kong</option>
	<option value=\"Hungary\">Hungary</option>
	<option value=\"Iceland\">Iceland</option>
	<option value=\"India\">India</option>
	<option value=\"Indonesia\">Indonesia</option>
	<option value=\"Iran, Islamic Republic of\">Iran, Islamic Republic of</option>
	<option value=\"Iraq\">Iraq</option>
	<option value=\"Ireland\">Ireland</option>
	<option value=\"Isle of Man\">Isle of Man</option>
	<option value=\"Israel\">Israel</option>
	<option value=\"Italy\">Italy</option>
	<option value=\"Jamaica\">Jamaica</option>
	<option value=\"Japan\">Japan</option>
	<option value=\"Jersey\">Jersey</option>
	<option value=\"Jordan\">Jordan</option>
	<option value=\"Kazakhstan\">Kazakhstan</option>
	<option value=\"Kenya\">Kenya</option>
	<option value=\"Kiribati\">Kiribati</option>
	<option value=\"Korea, Democratic People's Republic of\">Korea, Democratic People's Republic of</option>
	<option value=\"Korea, Republic of\">Korea, Republic of</option>
	<option value=\"Kuwait\">Kuwait</option>
	<option value=\"Kyrgyzstan\">Kyrgyzstan</option>
	<option value=\"Lao People's Democratic Republic\">Lao People's Democratic Republic</option>
	<option value=\"Latvia\">Latvia</option>
	<option value=\"Lebanon\">Lebanon</option>
	<option value=\"Lesotho\">Lesotho</option>
	<option value=\"Liberia\">Liberia</option>
	<option value=\"Libya\">Libya</option>
	<option value=\"Liechtenstein\">Liechtenstein</option>
	<option value=\"Lithuania\">Lithuania</option>
	<option value=\"Luxembourg\">Luxembourg</option>
	<option value=\"Macao\">Macao</option>
	<option value=\"Macedonia, the former Yugoslav Republic of\">Macedonia, the former Yugoslav Republic of</option>
	<option value=\"Madagascar\">Madagascar</option>
	<option value=\"Malawi\">Malawi</option>
	<option value=\"Malaysia\">Malaysia</option>
	<option value=\"Maldives\">Maldives</option>
	<option value=\"Mali\">Mali</option>
	<option value=\"Malta\">Malta</option>
	<option value=\"Marshall Islands\">Marshall Islands</option>
	<option value=\"Martinique\">Martinique</option>
	<option value=\"Mauritania\">Mauritania</option>
	<option value=\"Mauritius\">Mauritius</option>
	<option value=\"Mayotte\">Mayotte</option>
	<option value=\"Mexico\">Mexico</option>
	<option value=\"Micronesia, Federated States of\">Micronesia, Federated States of</option>
	<option value=\"Moldova, Republic of\">Moldova, Republic of</option>
	<option value=\"Monaco\">Monaco</option>
	<option value=\"Mongolia\">Mongolia</option>
	<option value=\"Montenegro\">Montenegro</option>
	<option value=\"Montserrat\">Montserrat</option>
	<option value=\"Morocco\">Morocco</option>
	<option value=\"Mozambique\">Mozambique</option>
	<option value=\"Myanmar\">Myanmar</option>
	<option value=\"Namibia\">Namibia</option>
	<option value=\"Nauru\">Nauru</option>
	<option value=\"Nepal\">Nepal</option>
	<option value=\"Netherlands\">Netherlands</option>
	<option value=\"New Caledonia\">New Caledonia</option>
	<option value=\"New Zealand\">New Zealand</option>
	<option value=\"Nicaragua\">Nicaragua</option>
	<option value=\"Niger\">Niger</option>
	<option value=\"Nigeria\">Nigeria</option>
	<option value=\"Niue\">Niue</option>
	<option value=\"Norfolk Island\">Norfolk Island</option>
	<option value=\"Northern Mariana Islands\">Northern Mariana Islands</option>
	<option value=\"Norway\">Norway</option>
	<option value=\"Oman\">Oman</option>
	<option value=\"Pakistan\">Pakistan</option>
	<option value=\"Palau\">Palau</option>
	<option value=\"Palestinian Territory, Occupied\">Palestinian Territory, Occupied</option>
	<option value=\"Panama\">Panama</option>
	<option value=\"Papua New Guinea\">Papua New Guinea</option>
	<option value=\"Paraguay\">Paraguay</option>
	<option value=\"Peru\">Peru</option>
	<option value=\"Philippines\">Philippines</option>
	<option value=\"Pitcairn\">Pitcairn</option>
	<option value=\"Poland\">Poland</option>
	<option value=\"Portugal\">Portugal</option>
	<option value=\"Puerto Rico\">Puerto Rico</option>
	<option value=\"Qatar\">Qatar</option>
	<option value=\"Réunion\">Réunion</option>
	<option value=\"Romania\">Romania</option>
	<option value=\"Russian Federation\">Russian Federation</option>
	<option value=\"Rwanda\">Rwanda</option>
	<option value=\"Saint Barthélemy\">Saint Barthélemy</option>
	<option value=\"Saint Helena, Ascension and Tristan da Cunha\">Saint Helena, Ascension and Tristan da Cunha</option>
	<option value=\"Saint Kitts and Nevis\">Saint Kitts and Nevis</option>
	<option value=\"Saint Lucia\">Saint Lucia</option>
	<option value=\"Saint Martin (French part)\">Saint Martin (French part)</option>
	<option value=\"Saint Pierre and Miquelon\">Saint Pierre and Miquelon</option>
	<option value=\"Saint Vincent and the Grenadines\">Saint Vincent and the Grenadines</option>
	<option value=\"Samoa\">Samoa</option>
	<option value=\"San Marino\">San Marino</option>
	<option value=\"Sao Tome and Principe\">Sao Tome and Principe</option>
	<option value=\"Saudi Arabia\">Saudi Arabia</option>
	<option value=\"Senegal\">Senegal</option>
	<option value=\"Serbia\">Serbia</option>
	<option value=\"Seychelles\">Seychelles</option>
	<option value=\"Sierra Leone\">Sierra Leone</option>
	<option value=\"Singapore\">Singapore</option>
	<option value=\"Sint Maarten (Dutch part)\">Sint Maarten (Dutch part)</option>
	<option value=\"Slovakia\">Slovakia</option>
	<option value=\"Slovenia\">Slovenia</option>
	<option value=\"Solomon Islands\">Solomon Islands</option>
	<option value=\"Somalia\">Somalia</option>
	<option value=\"South Africa\">South Africa</option>
	<option value=\"South Georgia and the South Sandwich Islands\">South Georgia and the South Sandwich Islands</option>
	<option value=\"South Sudan\">South Sudan</option>
	<option value=\"Spain\">Spain</option>
	<option value=\"Sri Lanka\">Sri Lanka</option>
	<option value=\"Sudan\">Sudan</option>
	<option value=\"Suriname\">Suriname</option>
	<option value=\"Svalbard and Jan Mayen\">Svalbard and Jan Mayen</option>
	<option value=\"Swaziland\">Swaziland</option>
	<option value=\"Sweden\">Sweden</option>
	<option value=\"Switzerland\">Switzerland</option>
	<option value=\"Syrian Arab Republic\">Syrian Arab Republic</option>
	<option value=\"Taiwan, Province of China\">Taiwan, Province of China</option>
	<option value=\"Tajikistan\">Tajikistan</option>
	<option value=\"Tanzania, United Republic of\">Tanzania, United Republic of</option>
	<option value=\"Thailand\">Thailand</option>
	<option value=\"Timor-Leste\">Timor-Leste</option>
	<option value=\"Togo\">Togo</option>
	<option value=\"Tokelau\">Tokelau</option>
	<option value=\"Tonga\">Tonga</option>
	<option value=\"Trinidad and Tobago\">Trinidad and Tobago</option>
	<option value=\"Tunisia\">Tunisia</option>
	<option value=\"Turkey\">Turkey</option>
	<option value=\"Turkmenistan\">Turkmenistan</option>
	<option value=\"Turks and Caicos Islands\">Turks and Caicos Islands</option>
	<option value=\"Tuvalu\">Tuvalu</option>
	<option value=\"Uganda\">Uganda</option>
	<option value=\"Ukraine\">Ukraine</option>
	<option value=\"United Arab Emirates\">United Arab Emirates</option>
	<option data-tokens=\"UK Great Britain United Kingdom England Scotland Wales Northern Ireland\" value=\"United Kingdom\">United Kingdom</option>
	<option data-tokens=\"USA United States United States Of America\" value=\"United States\">United States</option>
	<option value=\"United States Minor Outlying Islands\">United States Minor Outlying Islands</option>
	<option value=\"Uruguay\">Uruguay</option>
	<option value=\"Uzbekistan\">Uzbekistan</option>
	<option value=\"Vanuatu\">Vanuatu</option>
	<option value=\"Venezuela, Bolivarian Republic of\">Venezuela, Bolivarian Republic of</option>
	<option value=\"Viet Nam\">Viet Nam</option>
	<option value=\"Virgin Islands, British\">Virgin Islands, British</option>
	<option value=\"Virgin Islands, U.S.\">Virgin Islands, U.S.</option>
	<option value=\"Wallis and Futuna\">Wallis and Futuna</option>
	<option value=\"Western Sahara\">Western Sahara</option>
	<option value=\"Yemen\">Yemen</option>
	<option value=\"Zambia\">Zambia</option>
	<option value=\"Zimbabwe\">Zimbabwe</option>
	");

	define("OPTION_COUNTRY_LIST_ISO_3116_1_ALPHA_2_CODES", "
	<option disabled selected value style='display: none;'> -- select a country -- </option>
	<option value=\"AF\">Afghanistan</option>
	<option value=\"AX\">Åland Islands</option>
	<option value=\"AL\">Albania</option>
	<option value=\"DZ\">Algeria</option>
	<option value=\"AS\">American Samoa</option>
	<option value=\"AD\">Andorra</option>
	<option value=\"AO\">Angola</option>
	<option value=\"AI\">Anguilla</option>
	<option value=\"AQ\">Antarctica</option>
	<option value=\"AG\">Antigua and Barbuda</option>
	<option value=\"AR\">Argentina</option>
	<option value=\"AM\">Armenia</option>
	<option value=\"AW\">Aruba</option>
	<option value=\"AU\">Australia</option>
	<option value=\"AT\">Austria</option>
	<option value=\"AZ\">Azerbaijan</option>
	<option value=\"BS\">Bahamas</option>
	<option value=\"BH\">Bahrain</option>
	<option value=\"BD\">Bangladesh</option>
	<option value=\"BB\">Barbados</option>
	<option value=\"BY\">Belarus</option>
	<option value=\"BE\">Belgium</option>
	<option value=\"BZ\">Belize</option>
	<option value=\"BJ\">Benin</option>
	<option value=\"BM\">Bermuda</option>
	<option value=\"BT\">Bhutan</option>
	<option value=\"BO\">Bolivia, Plurinational State of</option>
	<option value=\"BQ\">Bonaire, Sint Eustatius and Saba</option>
	<option value=\"BA\">Bosnia and Herzegovina</option>
	<option value=\"BW\">Botswana</option>
	<option value=\"BV\">Bouvet Island</option>
	<option value=\"BR\">Brazil</option>
	<option value=\"IO\">British Indian Ocean Territory</option>
	<option value=\"BN\">Brunei Darussalam</option>
	<option value=\"BG\">Bulgaria</option>
	<option value=\"BF\">Burkina Faso</option>
	<option value=\"BI\">Burundi</option>
	<option value=\"KH\">Cambodia</option>
	<option value=\"CM\">Cameroon</option>
	<option value=\"CA\">Canada</option>
	<option value=\"CV\">Cape Verde</option>
	<option value=\"KY\">Cayman Islands</option>
	<option value=\"CF\">Central African Republic</option>
	<option value=\"TD\">Chad</option>
	<option value=\"CL\">Chile</option>
	<option value=\"CN\">China</option>
	<option value=\"CX\">Christmas Island</option>
	<option value=\"CC\">Cocos (Keeling) Islands</option>
	<option value=\"CO\">Colombia</option>
	<option value=\"KM\">Comoros</option>
	<option value=\"CG\">Congo</option>
	<option value=\"CD\">Congo, the Democratic Republic of the</option>
	<option value=\"CK\">Cook Islands</option>
	<option value=\"CR\">Costa Rica</option>
	<option value=\"CI\">Côte d'Ivoire</option>
	<option value=\"HR\">Croatia</option>
	<option value=\"CU\">Cuba</option>
	<option value=\"CW\">Curaçao</option>
	<option value=\"CY\">Cyprus</option>
	<option value=\"CZ\">Czech Republic</option>
	<option value=\"DK\">Denmark</option>
	<option value=\"DJ\">Djibouti</option>
	<option value=\"DM\">Dominica</option>
	<option value=\"DO\">Dominican Republic</option>
	<option value=\"EC\">Ecuador</option>
	<option value=\"EG\">Egypt</option>
	<option value=\"SV\">El Salvador</option>
	<option value=\"GQ\">Equatorial Guinea</option>
	<option value=\"ER\">Eritrea</option>
	<option value=\"EE\">Estonia</option>
	<option value=\"ET\">Ethiopia</option>
	<option value=\"FK\">Falkland Islands (Malvinas)</option>
	<option value=\"FO\">Faroe Islands</option>
	<option value=\"FJ\">Fiji</option>
	<option value=\"FI\">Finland</option>
	<option value=\"FR\">France</option>
	<option value=\"GF\">French Guiana</option>
	<option value=\"PF\">French Polynesia</option>
	<option value=\"TF\">French Southern Territories</option>
	<option value=\"GA\">Gabon</option>
	<option value=\"GM\">Gambia</option>
	<option value=\"GE\">Georgia</option>
	<option value=\"DE\">Germany</option>
	<option value=\"GH\">Ghana</option>
	<option value=\"GI\">Gibraltar</option>
	<option value=\"GR\">Greece</option>
	<option value=\"GL\">Greenland</option>
	<option value=\"GD\">Grenada</option>
	<option value=\"GP\">Guadeloupe</option>
	<option value=\"GU\">Guam</option>
	<option value=\"GT\">Guatemala</option>
	<option value=\"GG\">Guernsey</option>
	<option value=\"GN\">Guinea</option>
	<option value=\"GW\">Guinea-Bissau</option>
	<option value=\"GY\">Guyana</option>
	<option value=\"HT\">Haiti</option>
	<option value=\"HM\">Heard Island and McDonald Islands</option>
	<option value=\"VA\">Holy See (Vatican City State)</option>
	<option value=\"HN\">Honduras</option>
	<option value=\"HK\">Hong Kong</option>
	<option value=\"HU\">Hungary</option>
	<option value=\"IS\">Iceland</option>
	<option value=\"IN\">India</option>
	<option value=\"ID\">Indonesia</option>
	<option value=\"IR\">Iran, Islamic Republic of</option>
	<option value=\"IQ\">Iraq</option>
	<option value=\"IE\">Ireland</option>
	<option value=\"IM\">Isle of Man</option>
	<option value=\"IL\">Israel</option>
	<option value=\"IT\">Italy</option>
	<option value=\"JM\">Jamaica</option>
	<option value=\"JP\">Japan</option>
	<option value=\"JE\">Jersey</option>
	<option value=\"JO\">Jordan</option>
	<option value=\"KZ\">Kazakhstan</option>
	<option value=\"KE\">Kenya</option>
	<option value=\"KI\">Kiribati</option>
	<option value=\"KP\">Korea, Democratic People's Republic of</option>
	<option value=\"KR\">Korea, Republic of</option>
	<option value=\"KW\">Kuwait</option>
	<option value=\"KG\">Kyrgyzstan</option>
	<option value=\"LA\">Lao People's Democratic Republic</option>
	<option value=\"LV\">Latvia</option>
	<option value=\"LB\">Lebanon</option>
	<option value=\"LS\">Lesotho</option>
	<option value=\"LR\">Liberia</option>
	<option value=\"LY\">Libya</option>
	<option value=\"LI\">Liechtenstein</option>
	<option value=\"LT\">Lithuania</option>
	<option value=\"LU\">Luxembourg</option>
	<option value=\"MO\">Macao</option>
	<option value=\"MK\">Macedonia, the former Yugoslav Republic of</option>
	<option value=\"MG\">Madagascar</option>
	<option value=\"MW\">Malawi</option>
	<option value=\"MY\">Malaysia</option>
	<option value=\"MV\">Maldives</option>
	<option value=\"ML\">Mali</option>
	<option value=\"MT\">Malta</option>
	<option value=\"MH\">Marshall Islands</option>
	<option value=\"MQ\">Martinique</option>
	<option value=\"MR\">Mauritania</option>
	<option value=\"MU\">Mauritius</option>
	<option value=\"YT\">Mayotte</option>
	<option value=\"MX\">Mexico</option>
	<option value=\"FM\">Micronesia, Federated States of</option>
	<option value=\"MD\">Moldova, Republic of</option>
	<option value=\"MC\">Monaco</option>
	<option value=\"MN\">Mongolia</option>
	<option value=\"ME\">Montenegro</option>
	<option value=\"MS\">Montserrat</option>
	<option value=\"MA\">Morocco</option>
	<option value=\"MZ\">Mozambique</option>
	<option value=\"MM\">Myanmar</option>
	<option value=\"NA\">Namibia</option>
	<option value=\"NR\">Nauru</option>
	<option value=\"NP\">Nepal</option>
	<option value=\"NL\">Netherlands</option>
	<option value=\"NC\">New Caledonia</option>
	<option value=\"NZ\">New Zealand</option>
	<option value=\"NI\">Nicaragua</option>
	<option value=\"NE\">Niger</option>
	<option value=\"NG\">Nigeria</option>
	<option value=\"NU\">Niue</option>
	<option value=\"NF\">Norfolk Island</option>
	<option value=\"MP\">Northern Mariana Islands</option>
	<option value=\"NO\">Norway</option>
	<option value=\"OM\">Oman</option>
	<option value=\"PK\">Pakistan</option>
	<option value=\"PW\">Palau</option>
	<option value=\"PS\">Palestinian Territory, Occupied</option>
	<option value=\"PA\">Panama</option>
	<option value=\"PG\">Papua New Guinea</option>
	<option value=\"PY\">Paraguay</option>
	<option value=\"PE\">Peru</option>
	<option value=\"PH\">Philippines</option>
	<option value=\"PN\">Pitcairn</option>
	<option value=\"PL\">Poland</option>
	<option value=\"PT\">Portugal</option>
	<option value=\"PR\">Puerto Rico</option>
	<option value=\"QA\">Qatar</option>
	<option value=\"RE\">Réunion</option>
	<option value=\"RO\">Romania</option>
	<option value=\"RU\">Russian Federation</option>
	<option value=\"RW\">Rwanda</option>
	<option value=\"BL\">Saint Barthélemy</option>
	<option value=\"SH\">Saint Helena, Ascension and Tristan da Cunha</option>
	<option value=\"KN\">Saint Kitts and Nevis</option>
	<option value=\"LC\">Saint Lucia</option>
	<option value=\"MF\">Saint Martin (French part)</option>
	<option value=\"PM\">Saint Pierre and Miquelon</option>
	<option value=\"VC\">Saint Vincent and the Grenadines</option>
	<option value=\"WS\">Samoa</option>
	<option value=\"SM\">San Marino</option>
	<option value=\"ST\">Sao Tome and Principe</option>
	<option value=\"SA\">Saudi Arabia</option>
	<option value=\"SN\">Senegal</option>
	<option value=\"RS\">Serbia</option>
	<option value=\"SC\">Seychelles</option>
	<option value=\"SL\">Sierra Leone</option>
	<option value=\"SG\">Singapore</option>
	<option value=\"SX\">Sint Maarten (Dutch part)</option>
	<option value=\"SK\">Slovakia</option>
	<option value=\"SI\">Slovenia</option>
	<option value=\"SB\">Solomon Islands</option>
	<option value=\"SO\">Somalia</option>
	<option value=\"ZA\">South Africa</option>
	<option value=\"GS\">South Georgia and the South Sandwich Islands</option>
	<option value=\"SS\">South Sudan</option>
	<option value=\"ES\">Spain</option>
	<option value=\"LK\">Sri Lanka</option>
	<option value=\"SD\">Sudan</option>
	<option value=\"SR\">Suriname</option>
	<option value=\"SJ\">Svalbard and Jan Mayen</option>
	<option value=\"SZ\">Swaziland</option>
	<option value=\"SE\">Sweden</option>
	<option value=\"CH\">Switzerland</option>
	<option value=\"SY\">Syrian Arab Republic</option>
	<option value=\"TW\">Taiwan, Province of China</option>
	<option value=\"TJ\">Tajikistan</option>
	<option value=\"TZ\">Tanzania, United Republic of</option>
	<option value=\"TH\">Thailand</option>
	<option value=\"TL\">Timor-Leste</option>
	<option value=\"TG\">Togo</option>
	<option value=\"TK\">Tokelau</option>
	<option value=\"TO\">Tonga</option>
	<option value=\"TT\">Trinidad and Tobago</option>
	<option value=\"TN\">Tunisia</option>
	<option value=\"TR\">Turkey</option>
	<option value=\"TM\">Turkmenistan</option>
	<option value=\"TC\">Turks and Caicos Islands</option>
	<option value=\"TV\">Tuvalu</option>
	<option value=\"UG\">Uganda</option>
	<option value=\"UA\">Ukraine</option>
	<option value=\"AE\">United Arab Emirates</option>
	<option value=\"GB\">United Kingdom</option>
	<option value=\"US\">United States</option>
	<option value=\"UM\">United States Minor Outlying Islands</option>
	<option value=\"UY\">Uruguay</option>
	<option value=\"UZ\">Uzbekistan</option>
	<option value=\"VU\">Vanuatu</option>
	<option value=\"VE\">Venezuela, Bolivarian Republic of</option>
	<option value=\"VN\">Viet Nam</option>
	<option value=\"VG\">Virgin Islands, British</option>
	<option value=\"VI\">Virgin Islands, U.S.</option>
	<option value=\"WF\">Wallis and Futuna</option>
	<option value=\"EH\">Western Sahara</option>
	<option value=\"YE\">Yemen</option>
	<option value=\"ZM\">Zambia</option>
	<option value=\"ZW\">Zimbabwe</option>
	");

	define("HTML_ADMIN_RIGHT_CLICK_MUTLI_ITEM_EDITOR_MENU", "
		<ul class='custom-menu dropdown-menu'>
		      <li id='f-editor-copy-description'><a href=\"#\" onClick='js_editor_copy(\"description\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"false\"></span> Copy Description</a></li>
			  <li id='f-editor-copy-details'><a href=\"#\" onClick='js_editor_copy(\"details\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Details</a></li>
			  <li id='f-editor-copy-specs'><a href=\"#\" onClick='js_editor_copy(\"specs\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Specs</a></li>
			  <li id='f-editor-copy-inbox'><a href=\"#\" onClick='js_editor_copy(\"inbox\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy In the box</a></li>
			  <li id='f-editor-copy-keywords'><a href=\"#\" onClick='js_editor_copy(\"keywords\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Keywords</a></li>
			  <li id='f-editor-copy-dimensions'><a href=\"#\" onClick='js_editor_copy(\"dimensions\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Dimensions</a></li>
			  <li id='f-editor-copy-weight'><a href=\"#\" onClick='js_editor_copy(\"weight\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Weight</a></li>
			  <li role=\"separator\" class=\"divider\"></li>
			  <li id='f-editor-copy-p_category_items_data'><a href=\"#\" onClick='js_editor_copy(\"p_category_items_data\");'><span class=\"glyphicon glyphicon-copy\" aria-hidden=\"true\"></span> Copy Product Linking</a></li>
		      <li role=\"separator\" class=\"divider\"></li>
			  <li id='f-editor-paste-description' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"description\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Description</a></li>
			  <li id='f-editor-paste-details' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"details\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Details</a></li>
			  <li id='f-editor-paste-specs' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"specs\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Specs</a></li>
			  <li id='f-editor-paste-inbox' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"inbox\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste In the box</a></li>
			  <li id='f-editor-paste-keywords' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"keywords\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Keywords</a></li>
			  <li id='f-editor-paste-dimensions' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"dimensions\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Dimensions</a></li>
			  <li id='f-editor-paste-weight' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"weight\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Weight</a></li>
			  <li role=\"separator\" class=\"divider\"></li>
			  <li id='f-editor-paste-p_category_items_data' class='disabled'><a href=\"#\" onClick='js_editor_paste(\"p_category_items_data\");'><span class=\"glyphicon glyphicon-paste\" aria-hidden=\"true\"></span> Paste Product Linking</a></li>
			  <li role=\"separator\" class=\"divider\"></li>
  	  		  <li id='f-editor-append-p_category_items_data' class='disabled'><a href=\"#\" onClick='js_editor_append(\"p_category_items_data\");'><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Append Product Linking</a></li>
		</ul>
	");

	define("HTML_ADMIN_RIGHT_CLICK_EDITOR_MENU", "
		<div class='custom-menu-editor dropdown-menu'>
			<div style='min-width: 200px; margin: 5px;'>
				<div id='f-quick-edit-name'>Column name</div>
				<div style='width: 100%;'><input id='f-quick-edit-data' type='text' style='width: 100%;'></input></div>

				<div style='display: table; margin-top: 20px; margin-bottom: 10px; width: 100%;'>
					<div style='display: table-cell; text-align: left;'>
						<div class='btn btn-sm btn-danger' onClick='$(\".custom-menu-editor\").hide(100);'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</div>
					</div>

					<div style='display: table-cell; text-align: right;'>
						<div class='btn btn-sm btn-success' onClick='js_fluid_quick_edit();'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</div>
					</div>
				</div>

			</div>
		</div>
	");

	// A modal for checkout guest confirmation before going to the checkout..
	define("HTML_CHECKOUT_GUEST_MODAL_FLUID", "
	<div id=\"fluid-checkout-guest-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-checkout-div-header' class='modal-header fluid-modal-header' style='display: none;'><i class=\"fa fa-user\"></i> Create Account</div>
				<div id='modal-checkout-fluid-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>
				Note that you are about to enter the checkout as a guest.
				</div>

				<div class=\"modal-footer\">
					<div id='fluid-checkout-guest-back-button' class='pull-left'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
					<div id=\"fluid-continue-as-guest\" class='pull-right'><a href=\"" . $f_url . FLUID_CHECKOUT_REWRITE . "\" onClick='js_loading_start();'><button type=\"button\" class=\"btn btn-success\"><span class='glyphicon glyphicon-arrow-right' aria-hidden=\"true\"></span> <div id='fluid-modal-close-button-text' style='display: inline-block;'>Continue as guest</div></button></a></div>
					<div id=\"fluid-guest-container\" class='pull-right'></div>
				</div>
			</div>

		</div>
	</div>
	");

	// A modal container.
	define("HTML_MODAL", "
	<div class='modal fade' id='fluid-modal' tabindex='-1' role='dialog' aria-labelledby='FluidModal' data-backdrop='static' data-keyboard='false'>

	</div>");

	// A overflow modal container. Used when we want to load another module but return back to the previous module which is loaded into the fluid-modal above.
	define("HTML_MODAL_OVERFLOW", "
	<div class='modal fade' id='fluid-modal-overflow' tabindex='-1' role='dialog' aria-labelledby='FluidModal' data-backdrop='static' data-keyboard='false'>

	</div>");

	define("HTML_MODAL_MSG", "
	<div class='modal fade' id='fluid-modal-msg' tabindex='-1' role='dialog' aria-labelledby='FluidModal' data-backdrop='static' data-keyboard='false'>

	</div>");

	define("HTML_MODAL_FLUID", "
	<div id=\"fluid-main-modal\" class=\"modal fade\" tabindex='-1' role='dialog' aria-labelledby='FluidModal' data-backdrop='static' data-keyboard='false'>
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-fluid-header-div' class='modal-header fluid-modal-header'></div>
				<div id='modal-fluid-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div id=\"fluid-main-modal-footer\" class=\"modal-footer\">
					<div id='fluid-modal-back-button' class='pull-left'></div>
					<div id='fluid-modal-close-button' class='pull-right'><button type=\"button\" class=\"btn btn-danger\" onClick=\"js_modal_hide('#fluid-main-modal');\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> <div id='fluid-modal-close-button-text' style='display: inline-block;'>Close</div></button></div>
					<div id='fluid-modal-trigger-button' class='pull-right'></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_FLUID_MSG", "
	<div id=\"fluid-main-modal-msg\" class=\"modal fade\" role=\"dialog\" tabindex='-1' aria-labelledby='FluidModal' data-backdrop='static' data-keyboard='false'>
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-fluid-header-div-msg' class='modal-header fluid-modal-header'></div>
				<div id='modal-fluid-div-msg' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div id=\"fluid-main-modal-footer-msg\" class=\"modal-footer\">
					<div id='fluid-modal-back-button-msg' class='pull-left'></div>
					<div id='fluid-modal-close-button-msg' class='pull-right'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> <div id='fluid-modal-close-button-text-msg' style='display: inline-block;'>Cancel</div></button></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_FLUID_CHECKOUT_THANK_YOU", "
	<div id=\"fluid-main-modal-checkout\" class=\"modal fade f-modal-print\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-fluid-header-div-checkout' class='modal-header fluid-modal-header'></div>
				<div id='modal-fluid-div-checkout' class=\"modal-body f-checkout-modal-confirm-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div id=\"fluid-main-modal-footer-checkout\" class=\"modal-footer\">
					<div id='fluid-modal-continue-div' class='pull-right'><a href=\"" . $_SESSION['fluid_uri'] . "\" onClick='js_loading_start()';><button type=\"button\" class=\"btn btn-success\"><span class='glyphicon glyphicon-arrow-right' aria-hidden=\"true\"></span> <div id='fluid-modal-continue-button-text' style='display: inline-block;'>Continue</div></button></a></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_FLUID_CHECKOUT", "
	<div id=\"fluid-checkout-main-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-fluid-checkout-header-div' class='modal-header fluid-checkout-modal-header'></div>
				<div id='modal-fluid-checkout-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div id=\"fluid-checkout-main-modal-footer\" class=\"modal-footer\">
					<div id='fluid-checkout-modal-back-button' class='pull-left'></div>
					<div id='fluid-checkout-modal-close-button' class='pull-right'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> <div id='fluid-checkout-modal-close-button-text' style='display: inline-block;'>Close</div></button></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_FLUID_SHIPPING", "
	<div id=\"fluid-shipping-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div class='modal-header fluid-modal-header'><i class=\"fa fa-truck\"></i> Shipping Methods</div>
				<div id='modal-fluid-shipping-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div class=\"modal-footer\">
					<div class='pull-right'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_LOGIN", "
	<div id=\"fluid-login-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div id='modal-login-div-header' class='modal-header fluid-modal-header' style='display: none;'><i class=\"fa fa-user\"></i> Create Account</div>

				<div id='modal-login-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

				</div>

				<div class=\"modal-footer\">
					<div id='fluid-login-back-button' class='pull-left' style='display: none;'><button type=\"button\" class=\"btn btn-warning\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Back</button></div>
					<div id='f-login-pull-right-div' class='pull-right'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
					<div id='fluid-modal-trigger-button-login' class='pull-right'></div>
				</div>
			</div>

		</div>
	</div>
	");


	$form_address_create =
	"<form id=\"fluid_form_address\" data-toggle=\"validator\" data-focus=\"false\" role=\"form\" onsubmit=\"js_fluid_address_create();\" autocomplete=\"on\">

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-name\" class=\"cols-sm-2 control-label\">Your Name</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-name\" id=\"fluid-address-name\" placeholder=\"Enter your Name\" autocomplete=\"name\" required autocomplete=\"name\" autofocus=\"autofocus\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-apt-number\" class=\"cols-sm-2 control-label\">Apartment or Room Number</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-apt-number\" id=\"fluid-address-apt-number\" placeholder=\"Enter Apartment or Room number (Optional)\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-street\" class=\"cols-sm-2 control-label\">Street</label>
			<input type=\"text\" name=\"fluid-address-street\" class=\"form-control\" name=\"fluid-address-street\" id=\"fluid-address-street\" 		onFocus='js_google_maps_geo_locate();' placeholder=\"Enter your street\" required autocomplete=\"shipping street-address\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-city\" class=\"cols-sm-2 control-label\">City</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-city\" id=\"fluid-address-city\" placeholder=\"Enter your City\" required autocomplete=\"shipping locality\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\" disabled></span>
		</div>

		<div id='f-province-address' class=\"form-group has-feedback\">
			<label id='province-canada' for=\"fluid-address-province\" class=\"cols-sm-2 control-label\">Province</label>
			<select class=\"selectpicker form-control\" data-live-search=\"true\" show-tick show-menu-arrow data-size=\"5\" name=\"fluid-address-province\" id=\"fluid-address-province\" autocomplete=\"shipping region\" required>
			" . OPTION_PROVINCE_CANADA_LIST . "
			</select>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-country\" class=\"cols-sm-2 control-label\">Country</label>
			<select class=\"selectpicker form-control\" data-live-search=\"true\" show-tick show-menu-arrow data-size=\"5\" name=\"fluid-address-country\" id=\"fluid-address-country\" onChange='js_fluid_country_change(this.options[this.selectedIndex].value);' autocomplete=\"shipping country\" required>
			" . OPTION_COUNTRY_LIST_ISO_3116_1_ALPHA_LONG_CODES . "
			</select>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-postal-code\" class=\"cols-sm-2 control-label\">Postal Code</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-postal-code\" id=\"fluid-address-postal-code\" placeholder=\"Enter your Postal Code\" required autocomplete=\"shipping postal-code\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-phone-number\" class=\"cols-sm-2 control-label\">Phone Number</label>
			<input type=\"text\" min=\"0\" inputmode=\"numeric\" class=\"form-control\" name=\"fluid-address-phone-number\" id=\"fluid-address-phone-number\" placeholder=\"Enter your Phone Number\" required autocomplete=\"tel\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div class=\"form-group has-feedback\">
			<label class=\"control-label\" for=\"fluid-email-address\">Email</label>
			<input id=\"fluid-email-address\" name=\"fluid-email-address\" type=\"email\" maxlength=\"50\" class=\"form-control\" placeholder=\"Email address\" required autocomplete=\"email\"";
			if(isset($_SESSION['u_id']))
				if(isset($_SESSION['u_email']))
					$form_address_create .= " value\"" . $_SESSION['u_email'] . "\"";
			$form_address_create .= ">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
			<div class=\"help-block with-errors\"></div>
		</div>

		<div class=\"form-group has-feedback\">
			<label class=\"control-label\" for=\"fluid-email-again-address\">Email again</label>
			<input id=\"fluid-email-again-address\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-match=\"#fluid-email-address\" data-match-error=\"Emails do not match.\" required autocomplete=\"email\"";
			if(isset($_SESSION['u_id']))
				if(isset($_SESSION['u_email']))
					$form_address_create .= " value\"" . $_SESSION['u_email'] . "\"";
			$form_address_create .= ">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
			<div class=\"help-block with-errors\"></div>
		</div>

		<div class=\"form-group\">
			<button style='display: none;' id='f-address-submit-button' type=\"submit\" class=\"btn btn-info btn-block\"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Create Address</button>
		</div>

	</form>";
/*
		<div class=\"form-group has-feedback\">
			<label for=\"fluid-address-country\" class=\"cols-sm-2 control-label\">Country</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-country\" id=\"fluid-address-country\" placeholder=\"Enter your Country\" required autocomplete=\"shipping country\" disabled>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>
*/
	define("HTML_ADDRESS_CREATE_FORM", $form_address_create);

	define("HTML_ADDRESS_PROVINCE", "
			<label id='province-canada' for=\"fluid-address-province\" class=\"cols-sm-2 control-label\">Province</label>
			<select class=\"selectpicker form-control\" data-live-search=\"true\" show-tick show-menu-arrow data-size=\"5\" name=\"fluid-address-province\" id=\"fluid-address-province\" autocomplete=\"shipping region\" required>
			" . OPTION_PROVINCE_CANADA_LIST . "
			</select>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
	");

	define("HTML_ADDRESS_STATES", "
			<label id='state-usa' for=\"fluid-address-province\" class=\"cols-sm-2 control-label\">State</label>
			<select class=\"selectpicker form-control\" data-live-search=\"true\" show-tick show-menu-arrow data-size=\"5\" name=\"fluid-address-province\" id=\"fluid-address-province\" autocomplete=\"shipping region\" required>
			" . OPTION_USA_STATES_LIST . "
			</select>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
	");

	define("HTML_ADDRESS_WORLD", "
			<label id='state-world' for=\"fluid-address-province\" class=\"cols-sm-2 control-label\">Region</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-province\" id=\"fluid-address-province\" placeholder=\"Example: State or Province\" required autocomplete=\"shipping region\">
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
	");

	define("HTML_ADDRESS_COUNTRY", "
			<label for=\"fluid-address-country\" class=\"cols-sm-2 control-label\">Country</label>
			<select class=\"selectpicker form-control\" data-live-search=\"true\" show-tick show-menu-arrow data-size=\"5\" name=\"fluid-address-country\" id=\"fluid-address-country\" onChange='js_fluid_country_change(this.options[this.selectedIndex].value);' autocomplete=\"shipping country\" required>
			" . OPTION_COUNTRY_LIST_ISO_3116_1_ALPHA_LONG_CODES . "
			</select>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
	");

	define("HTML_ADDRESS_COUNTRY_WORLD", "
			<label for=\"fluid-address-country\" class=\"cols-sm-2 control-label\">Country</label>
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-country\" id=\"fluid-address-country\" placeholder=\"Enter your Country\" required autocomplete=\"shipping country\" value=''>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
	");

	define("HTML_CART_SHIP_ESTIMATOR", "
	<form id=\"fluid_form_address_cart_ship\" data-toggle=\"validator\" data-focus=\"false\" role=\"form\" onsubmit=\"js_fluid_address_ship();\" autocomplete=\"off\">

		<div class=\"form-group has-feedback\">
			<input type=\"text\" class=\"form-control\" name=\"fluid-address-cart-ship\" id=\"fluid-address-cart-ship\" data-minlength=\"6\" autofocus=\"autofocus\" placeholder=\"Enter your Address\" autocomplete=\"address\" required>
			<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
		</div>

		<div style='table-cell; border-bottom: 0px; text-align:center;'>
			<button type='button' class='btn btn-danger pull-left' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_cart_editor_cancel(true);';><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Cancel</button>

			<button type=\"submit\" id='fluid-address-ship-estimator-button' class=\"btn btn-info pull-right\" aria-haspopup='true' aria-expanded='false' disabled><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Add Address</button>
		</div>
	</form>
	");

	define("HTML_MODAL_ADDRESS_BOOK", "
	<div id=\"fluid-address-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div class='modal-header fluid-modal-header'><i class=\"fa fa-home\"></i> Shipping Address</div>
				<div id='modal-address-div' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>
				" . HTML_ADDRESS_CREATE_FORM . "


				</div>

				<div class=\"modal-footer\">


					<div class='pull-left'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
					<div class='pull-right'><button type=\"button\" class=\"btn btn-info\" onClick='$(\"#f-address-submit-button\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Create Address</button></div>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_FORGET", "
	<div id=\"fluid-forgot-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">

				<div class=\"modal-body\" style='padding: 5px;'>

					<div id='forget-hide-id' class=\"row\">
					  <div class=\"panel-body\" style='padding: 15px;'>
						<div class=\"text-center\">
						  <h2><i class=\"fa fa-lock fa-3x f-title-forgot\"></i></h3>
						  <h2 class=\"text-center f-title-forgot\">Forgot Password?</h2>
						  <p>You can reset your password here.</p>
						  <div class=\"panel-body\">

							<form id=\"fluid_form_forgot_password\" data-toggle=\"validator\" role=\"form\" data-focus=\"false\" autocomplete=\"off\" onsubmit=\"js_fluid_reset_password();\">

							  <div class=\"form-group has-feedback\">
							   <label class=\"control-label\" for=\"fluid_email_forgot\">Email</label>
								<div class=\"input-group\">
								  <span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-envelope color-blue\"></i></span>
								  <input id=\"fluid_email_forgot\" name=\"fluid_email_forgot\" placeholder=\"email address\" autofocus=\"autofocus\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-error=\"Email address not found.\" data-remote=\"" . $f_url . FLUID_ACCOUNT . "?load_func=true&fluid_function=php_validate_email_reset\" required>
								</div>
							   <span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							   <div class=\"help-block with-errors\"></div>
							  </div>
							  <div class=\"form-group\">
								<button id='fluid_forgot_password_button' class=\"btn btn-info btn-block\" type=\"submit\">Reset Password</button>
							  </div>
							</form>

						  </div>
						</div>
					  </div>
					</div>

					<div id='fluid-reset-forget-div' class=\"row\" style='display: none;'>

					</div>

				</div>

				<div class=\"modal-footer\">
					<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button>
				</div>
			</div>

		</div>
	</div>
	");

	define("HTML_REGISTER", "
					<div class=\"fa-header-hide form-group text-center\">
						  <div class=\"fa-fluid-size\"><i class=\"fa fa-user\"></i></div>
						  <div class=\"fa-fluid-header text-center\">Create your account</div>
					</div>

					<form id=\"fluid_form_register\" data-toggle=\"validator\" role=\"form\" data-focus=\"false\" onsubmit=\"js_fluid_register();\" autocomplete=\"on\">
						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_first_name_register\">First name</label>
							<input id=\"fluid_first_name_register\" type=\"text\" maxlength=\"50\" autofocus=\"autofocus\" class=\"form-control\" required autocomplete=\"fname\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_last_name_register\">Last name</label>
							<input id=\"fluid_last_name_register\" type=\"text\" maxlength=\"50\" class=\"form-control\" required autocomplete=\"lname\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_email_register\">Email</label>
							<input id=\"fluid_email_register\" name=\"fluid_email_register\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-error=\"Invalid email address.\" data-remote=\"" . $f_url . FLUID_ACCOUNT . "?load_func=true&fluid_function=php_validate_email\" required autocomplete=\"email\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>
						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_email_again_register\">Email again</label>
							<input id=\"fluid_email_again_register\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-match=\"#fluid_email_register\" data-match-error=\"Emails do not match.\" required autocomplete=\"email\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_password_register\">Password</label>
							<input id=\"fluid_password_register\" type=\"password\" maxlength=\"100\" class=\"form-control\" placeholder=\"Password\" data-minlength=\"6\" required>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\">Minimum of 6 characters</div>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_password_again_register\">Password again</label>
							<input id=\"fluid_password_again_register\" type=\"password\" maxlength=\"100\" class=\"form-control\" data-match=\"#fluid_password_register\" data-match-error=\"Passwords do not match.\" required>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>

						<div class=\"form-group\">
							<button id=\"fluid_button_register\" style='display: none;' type=\"submit\" class=\"btn btn-info btn-block\">Create your account</button>
						</div>

					</form>
	");

	define("HTML_MODAL_REGISTER_EMPTY", "
	<div id=\"fluid-register-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div class='modal-header fluid-modal-header'><i class=\"fa fa-user\"></i> Create Account</div>
				<div id='fluid-modal-register-div-body' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>



				</div>

				<div class=\"modal-footer\">
					<div class='pull-left'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
					<div class='pull-right'><button type=\"button\" class=\"btn btn-info\" onClick='$(\"#fluid_button_register\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> <div style='display: inline-block;'>Create Account</div></button></div>
				</div>

			</div>

		</div>
	</div>
	");

	define("HTML_MODAL_REGISTER", "
	<div id=\"fluid-register-modal\" class=\"modal fade\" role=\"dialog\">
		<div class=\"modal-dialog\">

			<div class=\"modal-content\">
				<div class='modal-header fluid-modal-header'><i class=\"fa fa-user\"></i> Create Account</div>
				<div id='fluid-modal-register-div-body' class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>

					<div class=\"form-group text-center\">
						  <h3><i class=\"fa fa-user fa-3x\"></i></h3>
						  <h2 class=\"text-center\">Create your account</h2>
					</div>

					<form id=\"fluid_form_register\" data-toggle=\"validator\" role=\"form\" data-focus=\"false\" onsubmit=\"js_fluid_register();\" autocomplete=\"on\">
						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_first_name_register\">First name</label>
							<input id=\"fluid_first_name_register\" type=\"text\" maxlength=\"50\" autofocus=\"autofocus\" class=\"form-control\" required autocomplete=\"fname\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_last_name_register\">Last name</label>
							<input id=\"fluid_last_name_register\" type=\"text\" maxlength=\"50\" class=\"form-control\" required autocomplete=\"lname\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_email_register\">Email</label>
							<input id=\"fluid_email_register\" name=\"fluid_email_register\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-error=\"Invalid email address.\" data-remote=\"" . $f_url . FLUID_ACCOUNT . "?load_func=true&fluid_function=php_validate_email\" required autocomplete=\"email\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>
						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_email_again_register\">Email again</label>
							<input id=\"fluid_email_again_register\" type=\"email\" maxlength=\"50\" class=\"form-control\" data-match=\"#fluid_email_register\" data-match-error=\"Emails do not match.\" required autocomplete=\"email\">
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_password_register\">Password</label>
							<input id=\"fluid_password_register\" type=\"password\" maxlength=\"100\" class=\"form-control\" placeholder=\"Password\" data-minlength=\"6\" required>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\">Minimum of 6 characters</div>
						</div>

						<div class=\"form-group has-feedback\">
							<label class=\"control-label\" for=\"fluid_password_again_register\">Password again</label>
							<input id=\"fluid_password_again_register\" type=\"password\" maxlength=\"100\" class=\"form-control\" data-match=\"#fluid_password_register\" data-match-error=\"Passwords do not match.\" required>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							<div class=\"help-block with-errors\"></div>
						</div>

						<div class=\"form-group\">
							<button id=\"fluid_button_register\" style='display: none;' type=\"submit\" class=\"btn btn-info btn-block\">Create your account</button>
						</div>

					</form>

				</div>

				<div class=\"modal-footer\">
					<div class='pull-left'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div>
					<div class='pull-right'><button type=\"button\" class=\"btn btn-info\" onClick='$(\"#fluid_button_register\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> <div style='display: inline-block;'>Create Account</div></button></div>
				</div>
			</div>

		</div>
	</div>
	");

	// A modal container.
	define("HTML_ERROR_MODAL", "
	<div class='modal fade' id='fluid-error-modal' tabindex='-1' style='z-index: 9000;' role='dialog' aria-labelledby='myModalLabel' data-backdrop='static' data-keyboard='false'>
	  <div class='modal-dialog' role='document'>
		<div class='modal-content'>

			<div class='modal-body'>
				<div id='modal-error-msg-div' class='alert alert-danger'></div>
			</div>

			<div id='error-modal-footer' class='modal-footer'>
				<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button></div>
			</div>

		</div>
	  </div>
	</div>");

	// A modal container.
	define("HTML_CONFIRM_MODAL", "
	<div class='modal fade' id='fluid-confirm-modal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' data-backdrop='static' data-keyboard='false'>
	  <div class='modal-dialog' role='document'>
		<div class='modal-content'>

				<div id='modal-fluid-confirm-header-div' class='modal-header fluid-modal-header'>Confirm</div>
				<div class=\"modal-body\" style='max-height: 70vh; overflow-y: auto;'>
					<div id='modal-override-div'>
						<div id='modal-confirm-msg-div' class=\"alert alert-danger\" role=\"alert\"></div>
					</div>
				</div>

			<div class='modal-footer' id='modal-confirm-footer'>
				<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button></div>
			</div>

		</div>
	  </div>
	</div>");

	define("HTML_LOADING_OVERLAY", "<div class='css-loading-overlay' id='loading-overlay'></div>");

	define("HTML_IMAGE_DROPZONE", "
		<div style='margin-top: 10px; margin-right: 5px; padding: 0 0 0 0;' id='actions' class='row'>

		  <div class='col-lg-7'>
			<!-- The fileinput-button span is used to style the file input field as button -->
			<span class='btn btn-success fileinput-button'>
				<i class='glyphicon glyphicon-folder-open'></i>
				<span>Add files</span>
			</span>
			<button type='submit' class='btn btn-primary start'>
				<i class='glyphicon glyphicon-upload'></i>
				<span>Upload all</span>
			</button>
			<button type='reset' class='btn btn-warning cancel'>
				<i class='glyphicon glyphicon-ban-circle'></i>
				<span>Cancel all</span>
			</button>
		  </div>

		  <div class='col-lg-5'>
			<!-- The global file processing state -->
			<span class='fileupload-process'>
			  <div id='total-progress' class='progress progress-striped active' role='progressbar' aria-valuemin='0' aria-valuemax='100' aria-valuenow='0'>
				<div class='progress-bar progress-bar-success' style='width:0%;' data-dz-uploadprogress></div>
			  </div>
			</span>
		  </div>

		</div>

		<div class='table table-striped files' id='previews'>

		  <div id='template' class='file-row'>
			<!-- This is used as the file preview template -->
			<div>
				<span class='preview'><img data-dz-thumbnail /></span>
			</div>
			<div>
				<p class='name' data-dz-name></p>
				<strong class='error text-danger' data-dz-errormessage></strong>
			</div>
			<div>
				<p class='size' data-dz-size></p>
				<div class='progress progress-striped active' role='progressbar' aria-valuemin='0' aria-valuemax='100' aria-valuenow='0'>
				  <div class='progress-bar progress-bar-success' style='width:0%;' data-dz-uploadprogress></div>
				</div>
			</div>
			<div style='display:none;'>
				<p class='size' data-dz-hiddendata></p>
			</div>
			<div>
			  <button class='btn btn-primary start'>
				  <i class='glyphicon glyphicon-upload'></i>
				  <span>Start</span>
			  </button>
			  <button data-dz-remove class='btn btn-warning cancel'>
				  <i class='glyphicon glyphicon-ban-circle'></i>
				  <span>Cancel</span>
			  </button>
			  <button id='delete-button' data-dz-remove class='btn btn-danger delete'>
				<i class='glyphicon glyphicon-trash'></i>
				<span>Delete</span>
			  </button>
			</div>
		  </div>

		</div>");

		define("HTML_TERMS_CONDITIONS", "
			<div style='font-size: 18px; font-weight: bold;'>Online Orders and Shipping</div>
			<div>All prices are in Canadian dollars.</div>
			<div style='padding-top: 10px;'>We ship only to the billing address of the card-holder or to a verified shipping address. This is the address that Visa, Mastercard or American Express has on file for you. If the name and address you give us do not match, we will not ship your order. Orders paid via PayPal will be shipped only to a valid confirmed address that PayPal has on file for your account. Please see our Shipping Policy for further information, and to view our shipping policy.</div>
			<div style='padding-top: 10px;'>Our on-line sales department is closed on Saturdays and Sundays, as well as statutory holidays. We do not ship out on Saturdays or Sundays. When shopping on-line during busy holiday seasons (for eg: Christmas), please order early to provide for ample time for shipping as volume delays do occur.</div>
			<div style='padding-top: 10px;'>Items purchased for in-store pickup are temporarly put on hold for up to 2 weeks and payment must be made at the time of pickup. Please contact us if you request us to hold the item for a longer duration.</div>
			<div style='padding-top: 10px;'>Please allow up to 48 hours after your order is placed before coming into the store to pick up your order. If you place an in-store pick-up order during the weekend, please be aware your order will NOT be ready for pick-up until 12 noon on the following business day. You will receive 3 emails from us, confirming: 1) that we have received your on-line order and it is pending; 2) that we are Processing your on-line order; and 3) that your order is completed and ready for pick-up</div>
			<div style='padding-top: 10px;'>If you purchase a product or service from us, we request certain personally identifiable information from you on our order form. You must provide accurate contact information (such as name, email, telephone number and shipping address) and financial information (such as credit card number, expiration date). We and your financial institution use this information for billing purposes and to fill your order. If we have any issue processing an order, we will use this information to contact you.</div>

			<div style='padding-top: 10px; font-size: 18px; font-weight: bold;'>Pricing Policy</div>
			<div>While we make every effort to ensure our prices are accurate both in-store as well as on-line, prices are subject to change without notice. We are not liable for any pricing increases or decreases that may take place. We do reserve the right to advise you of any errors in our listings or price changes prior to processing, and you will have the option to accept the newly adjusted price or to cancel the order.</div>

			<div style='padding-top: 10px; font-size: 18px; font-weight: bold;'>Online Shopping Policy</div>
			<div>Orders placed on-line with Leo's Camera Supply Ltd. are not binding until the order is processed and accepted by Leo's Camera Supply Ltd.. Your credit card will be charged at the time you place your order online for in stock merchandise.</div>
			<div style='padding-top: 10px;'>Leo's Camera Supply reserves the right to reject any sale for any reason.</div>
			<div style='padding-top: 10px;'>Prices advertised do not include shipping, handling or applicable sales taxes.</div>
			<div style='padding-top: 10px;'>Although we make every effort to have available stock of all of our product, certain items such as binoculars, some lenses, or printers may be labeled as <div style='display: inline-block; font-weight: bold;'>\"Special Order Only\"</div> on our web site. This means we must order in your item from the vendor for you, and we will contact you once your product has arrived in the store. Unless a deposit is required for the transaction, we will NOT charge your credit card until the item has been received in the store. In the event a deposit is required, you will be contacted to discuss the matter further via email or telephone.</div>
			<div style='padding-top: 10px;'>Over-sized items do NOT qualify for standard shipping charges, or free shipping offers unless otherwise specified. This includes all over-sized items such as printers, tripods, backpacks, lighting kits, backdrop stands, and all other larger items or multiple-item orders that qualify as oversize. Also, larger items/orders with long distances to travel or some remote destinations do not qualify for standard shipping charges or free shipping offers. Our web site may NOT automatically account for this charge, and you will be contacted via email by our web department prior to processing to discuss shipping costs and to obtain your approval.</div>
			<div style='padding-top: 10px;'>When new equipment is introduced by a manufacturer, you will be required to to contact us if you wish to pre-order (order an item of merchandise before it is available, with the understanding that it will be shipped later). Pre-orders require a 20% deposit of the merchandise pre-ordered. This amount will be processed and charged to your card after you contact us. Should you decide to cancel your pre-order after your card has been charged by us, you will be charged a ten percent fee, (card charge back costs + processing fee) against your down payment. (Eg: $1000.00 equipment purchased = $100.00 down payment = a processing fee of $10.00.)</div>
		");

		define("HTML_PRIVACY_CONDITIONS", "
			<div style='font-weight: bold; font-size: 18px;'>Security</div>
			<div>The security of your personal information is very important to us. In an effort to protect your personal information, we use Secure Sockets Layer (SSL) technology with 256 bit encryption. If your browser supports SSL (most current browsers do), it will automatically encrypt the information you provide to us on the secure pages on our site, before sending it over the internet.</div>
			<div style='padding-top: 10px;'>When visiting a page on our site that includes any of your personal information, the top of your browser window in the URL address bar will display an icon of a closed lock or an unbroken key (depending upon your browser). This indicates that you have successfully connected with our secure server.</div>
			<div style='padding-top: 10px;'>To authenticate the security of your connection before submitting any of your personal information, you can double-click on the icon to display our digital certificate. Some browser versions and some firewalls do not permit communication through secure servers. In that case, we suggest that you visit Leo's Camera Supply in store, or contact us by phone, or email your order to us.</div>

			<div style='padding-top: 20px; font-size: 18px; font-weight: bold;'>Cookies</div>
			<div>When you visit our site, we will ask your browser to place a permanent \"cookie\" (a very small text file) on your computer. If your browser is configured to accept cookies, it will accept the cookie and place it on your computer's hard drive. We collect this information to analyze where our customers are coming from and how often they visit our site. We also collect certain technical information from your computer, like your IP address and the address of a referring web site, if any. This information will allow us to enhance our site to match our customer's preferences. This information is used only for the benefit of Leo's Camera Supply and you, our customer. You can visit our site without cookies if you choose to do so. However, please be aware that some features of the site may not function properly or may be slower if you refuse cookies. To visit our site without cookies, you can configure your browser to reject all cookies or to notify you when a cookie is set. Check the browser's help menu to learn how to change your cookie preferences.</div>

			<div style='padding-top: 20px; font-size: 18px; font-weight: bold;'>What information is collected</div>
			<div>When you choose to place an order with us, you will be asked for your credit card information (name, card number, expiry, etc), this information is <div style='display: inline-block; font-weight: bold;'>NOT</div> collected, stored or recorded by Leo's Camera Supply. You will also be invited to log in (optional), so that we can identify who you are. As a newcomer, it will be suggested that you register for future identification. The registration page asks you to enter your name, a user ID, a password and your email address. You can update this information at any time by clicking on the <div style='display: inline-block; font-weight: bold;'>\"my account\"</div> link at the top of every page.</div>
		");

		define("HTML_PAYMENT_OPTIONS", "
			<div>We accept the following payment methods: <i class=\"fa fa-paypal\" aria-hidden=\"true\"></i> PayPal, <i class=\"fa fa-cc-mastercard\" aria-hidden=\"true\"></i> MasterCard <i class=\"fa fa-cc-amex\" aria-hidden=\"true\"></i> AMEX and <i class=\"fa fa-cc-visa\" aria-hidden=\"true\"></i> Visa.</div></div>
		");

		define("HTML_RETURN_POLICY", "
			<div style='font-size: 18px; font-weight: bold;'>Our Return Policy</div>
			<div>We offer a 14 day return policy on most orders. Items must be returned in original packaging and in <div style='display: inline-block; font-weight: bold;'>\"as new\"</div> condition. Returns are not accepted on memory cards, software, film, batteries, clothing, darkroom chemicals and other consumables. Final sale and special order items (not normally stocked by us) cannot not be returned at our discretion and if accepted for return may be subject to fees. Shipping & Handling charges are not covered by our return policy and will not be refunded.</div>

			<div style='padding-top: 10px; font-size: 18px; font-weight: bold;'>Online Orders</div>
			<div>Prior to returning any item you must contact us for a return Authorization Number. Orders that are returned without an authorization number will be refused by our shipping department.</div>
			<div style='padding-top: 10px;'>Guidelines when you receive your order you are required to follow:</div>
			<div style='padding-top: 10px;'>1: Upon receipt of your new merchandise, please inspect it carefully as to contents and condition. All claims for damaged or missing items MUST be reported to Leo's Camera Supply Ltd. within two (2) business days of receipt of merchandise. In the event your package arrives damaged, it is the responsibility of the customer to contact the carrier to inspect the package to assure full refund/replacement. All packaging MUST be retained until the issue has been resolved.</div>
			<div style='padding-top: 10px;'>2: Carefully unpack and inspect all merchandise. Please DO NOT damage the manufacturer's packaging. DO NOT fill out the manufacturer's warranty cards until you are absolutely sure you want to keep your merchandise. We cannot accept merchandise for return with completed warranty cards or damaged/missing collateral material. Do not throw away any of the packaging materials such as boxes, instructions, inserts, bags, etc until you are completely sure you want to keep the equipment.</div>
			<div style='padding-top: 10px;'>3: Keep your Invoice with your important records.</div>
			<div style='padding-top: 10px;'>4: Read all instruction manuals BEFORE testing your equipment.</div>
			<div style='padding-top: 20px;'>Leo's Camera Supply Ltd. will NOT be responsible for any consequential or incidental damage resulting from the sale or use of any merchandise bought from us. Our sole responsibility will only be the monetary value of the merchandise. All warranties are to be completed by the items manufacturer's according to their terms and conditions & handling instructions.</div>
		");

		if(FLUID_SHIP_NON_BILLING == FALSE) {
			$f_ship_non_billing = "Yes, as long as you select PayPal as your secure payment method. The shipping address must be in the same province as your credit card billing address. Please note that additional security checks may be placed on the order which could delay processing.";
		}
		else {
			$f_ship_non_billing = "Yes. Please note that additional security checks may be placed on the order which could delay processing.";
		}

		$f_shipping_value = NULL;
		if(FREE_SHIPPING_CART_TOTAL_STEP_1 > 0) {
			$f_shipping_value = "Standard sized in stock items over $" . FREE_SHIPPING_CART_TOTAL_STEP_1 . " are shipped free to many Canadian destinations, (excluding NT, NU and YT). Standard sized shipments under $" . FREE_SHIPPING_CART_TOTAL_STEP_1 . " are usually around $15.00 for most Canadian destinations. ";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_2 > 0) {
			$f_shipping_value = "Standard sized in stock items over $" . FREE_SHIPPING_CART_TOTAL_STEP_2 . "  are shipped free to many Canadian destinations, (excluding NT, NU and YT). Standard sized shipments under $" . FREE_SHIPPING_CART_TOTAL_STEP_2 . " are usually around $15.00 for most Canadian destinations. ";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_3 > 0) {
			$f_shipping_value = "Standard sized in stock items over $" . FREE_SHIPPING_CART_TOTAL_STEP_3 . "  are shipped free to many Canadian destinations, (excluding NT, NU and YT). Standard sized shipments under $" . FREE_SHIPPING_CART_TOTAL_STEP_3 . " are usually around $15.00 for most Canadian destinations. ";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_4 > 0) {
			$f_shipping_value = "Standard sized in stock items over $" . FREE_SHIPPING_CART_TOTAL_STEP_4 . "  are shipped free to many Canadian destinations, (excluding NT, NU and YT). Standard sized shipments under $" . FREE_SHIPPING_CART_TOTAL_STEP_4 . " are usually around $15.00 for most Canadian destinations. ";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_5 > 0) {
			$f_shipping_value = "Standard sized in stock items over $" . FREE_SHIPPING_CART_TOTAL_STEP_5 . "  are shipped free to many Canadian destinations, (excluding NT, NU and YT). Standard sized shipments under $" . FREE_SHIPPING_CART_TOTAL_STEP_5 . " are usually around $15.00 for most Canadian destinations. ";
		}
		
		$html_ship_policy = "<div style='font-size: 18px; font-weight: bold;'>Shipping Rates</div>
					<div>" . $f_shipping_value . "Prices for shipping will be shown in the checkout page.</div>
					<div style='padding-top: 10px;'>Some hazardous items such as darkroom chemicals and batteries (including lithium) may not be accepted or may require special handling by Canada Post and other shipping methods. Some oversize items (example: background rolls of various types, lighting kits, printers, large light stands / booms, large camera support systems) may not be eligible for free shipping. However we will provide you with a freight quote and recommended shipping quote on request. 9 ft long background rolls are in store pick only. Special order items do not include free shipping. Clearance items are also not eligible for free shipping.</div>

					<div style='padding-top: 20px; font-size: 18px; font-weight: bold;'>Frequently Asked Questions</div>
					<div><div style='display: inline-block; font-weight: bold;'>Q:</div> Can my order be shipped with a different courier?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes. We ship mainly with Canada Post and FedEx. Shipping via alternate couriers is available. (Eg:  Purolator, UPS, etc). Please contact us before ordering to discuss shipping rates if you wish to use a method other than Canada Post or FedEx.</div>

					<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> If I order today, when can I expect to receive my order?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A:</div>  When you receive your order depends on the availability of the product (Eg: in stock, on order, on back order etc), the shipping method, and your location. If orders are placed before 11:00AM PST/PDT we typically can ship the same day or the next day for in-stock items. An email is sent after your order has been processed letting your know when your order will be shipped out. If you are in a rush, please contact us at locally @ 604-685-5331. Otherwise your order will be shipped out on the following business day. The On-line Sales department is closed on weekends and holidays so all orders will be shipped out the next business day. Delivery to major Canadian cities is typically two to three business days. Rural delivery can be 4-7 business days or longer.</div>

					<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can you rush my order?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes we can in many cases, but at an additional charge. Please contact us before ordering to discuss this option.</div>

					<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> If I order more than one product, do I have to pay for shipping on each of the items?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A:</div> We charge shipping on a per package basis. As long as we can ship your order in one package the normal fee (either free, or standard shipping rates) will apply. For back ordered items we wait and ship your items together when the order is ready. Please contact us before ordering if you would like us to split the shipment, in which case an extra shipping fee may apply.</div>

					<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can I order a product and have it shipped to an address other than my credit cards billing address?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A: </div> " . $f_ship_non_billing . "</div>

					<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Do you ship to countries other than Canada?</div>
					<div><div style='display: inline-block; font-weight: bold;'>A:</div> No, we only deliver physical products to Canada at this moment. You can however use our free in store pickup option if you wish to order products with PayPal or with a non Canadian credit card.</div>";
					
					if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
						$html_ship_policy .= "<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can I order a product on-line and pick it up in store?</div>
						<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes. You will however be required to pay for the order when you come pick it up in store.</div>";	
					}
					else {
						$html_ship_policy .= "<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can I order a product on-line and pick it up in store?</div>
						<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes, as long as the card holder is picking up the product and the secure payment method is <b>not</b> PayPal.</div>";	
					}
					
					$html_ship_policy .= "<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can I use PayPal as a payment option if I do not have a major credit card for you to ship my order?</div>
								<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes. We are able to ship most items with the PayPal payment option. You can however use our free in store pickup option if you wish to pre-order products with a non Canadian credit card.</div>

								<div style='padding-top: 10px;'><div style='display: inline-block; font-weight: bold;'>Q:</div> Can I shop locally in-store with other forms of payment?</div>
								<div><div style='display: inline-block; font-weight: bold;'>A:</div> Yes. In-store shopping can also be paid with cash or Canadian debit card of most Canadian financial institutions.</div>";
								
		define("HTML_SHIPPING_POLICY", $html_ship_policy);



		define("HTML_ORDER_CHECK", "
			<div style='font-size: 18px; font-weight: bold;'>Single Order Status Lookup</div>
			<div>For single order lookup for an individual order that was placed online please enter the email address you provided when placing the order and your order number.</div>

			<div id='f-order-check-error'></div>

			<form id=\"fluid_form_order_check\" data-toggle=\"validator\" role=\"form\" accept-charset=\"UTF-8\">
			<div style='padding-top: 10px;'>
				<div class=\"form-group has-feedback\">
					<label for=\"fluid-order-email\" class=\"cols-sm-2 control-label\">Email address</label>
					<input type=\"email\" class=\"form-control\" name=\"fluid-order-email\" id=\"fluid-order-email\" placeholder=\"Enter your email address\" autocomplete=\"forderemail\" required autocomplete=\"forderphone\" autofocus=\"autofocus\">
					<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
				</div>

				<div class=\"form-group has-feedback\">
					<label for=\"fluid-order-number\" class=\"cols-sm-2 control-label\">Order number</label>
					<input type=\"text\" class=\"form-control\" name=\"fluid-order-number\" id=\"fluid-order-number\" placeholder=\"Enter the order number\" autocomplete=\"fordernumber\" required autocomplete=\"fordernumber\">
					<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
				</div>
			</div>
			</form>

		");

		define("HTML_FEEDBACK_DIALOG", "
			<div class=\"modal-dialog f-feedback-modal\">

				<div class=\"modal-content\">
					<div id='modal-fluid-header-feedback-div' class='modal-header fluid-modal-header'>Customer Feedback: Your opinion matters!</div>
					<div id='modal-fluid-feedback-div' class=\"modal-body f-feedback-body\">
						<p>We are constantly working to improve. Please provide us with your feedback so that we can deliver the best web experience possible.</p>

					<form id=\"fluid_form_feedback\" data-toggle=\"validator\" data-focus=\"false\" role=\"form\" onsubmit=\"js_fluid_feedback_send();\">
						<div class='form-group has-feedback'>
							<label for='f-feedback-reason' class='control-label'>What was the main reason that you came to our site?</label>
								<select id='f-feedback-reason' name='f-feedback-reason' required class='btn-group form-control bootstrap-select f-bootstrap-100 show-menu-arrow show-tick' style='color: #7d7d7d;' onChange='this.style.color=\"#555555\";'>
									<option selected disabled style='display: none;' value=''> -- select a reason -- </option>
									<option style='color: #555555;' value='Browse product, see what is new'>Browse product, see what is new</option>
									<option style='color: #555555;' value='Check product availability'>Check product availability</option>
									<option style='color: #555555;' value='Purchase product'>Purchase product</option>
									<option style='color: #555555;' value='See shipping rates'>See shipping rates</option>
									<option style='color: #555555;' value='Track an existing order'>Track an existing order</option>
									<option style='color: #555555;' value='Other: Please describe.'>Other: Please describe.</option>
								</select>
							<div class=\"help-block with-errors\"></div>
						</div>

						<div class='form-group'>
							<label for='f-feedback-comment'>Please comment on any areas that you would like to see improvement.</label>
							<textarea class='form-control' id='f-feedback-comment' rows='2' style='resize: none;' maxlength='300'></textarea>
						</div>

						<div class='form-group'>
							<label for='f-feedback-exit'>At what stage did you exit our site, and why.</label>
							<textarea class='form-control' id='f-feedback-exit' rows='2' style='resize: none;' maxlength='300'></textarea>
						</div>

						<div class='form-group has-feedback'>
							<label for='f-feedback-find' class='control-label'>Did you find what you were looking for?</label>
								<select id='f-feedback-find' name='f-feedback-find' required class='btn-group form-control bootstrap-select f-bootstrap-100 show-menu-arrow show-tick' style='color: #7d7d7d;' onChange='this.style.color=\"#555555\";'>
									<option disabled selected style='display: none;' value=''> -- select an option -- </option>
									<option style='color: #555555;' value='Yes'>Yes</option>
									<option style='color: #555555;' value='No'>No</option>
									<option style='color: #555555;' value='Not sure'>Not sure</option>
								</select>
								<div class=\"help-block with-errors\"></div>
						</div>

						<div class='form-group has-feedback'>
							<label for='f-feedback-likely' class='control-label'>How likely are you to recommend leos.com to a friend or family member.</label>
								<select id='f-feedback-likely' name='f-feedback-likely' required class='btn-group form-control bootstrap-select f-bootstrap-100 show-menu-arrow show-tick' style='color: #7d7d7d;' onChange='this.style.color=\"#555555\";'>
									<option disabled selected style='display: none;' value=''> -- select your recommendation -- </option>
									<option style='color: #555555;' value='10'>10 (Highly Recommend)</option>
									<option style='color: #555555;' value='9'>9</option>
									<option style='color: #555555;' value='8'>8</option>
									<option style='color: #555555;' value='7'>7</option>
									<option style='color: #555555;' value='6'>6</option>
									<option style='color: #555555;' value='5'>5</option>
									<option style='color: #555555;' value='4'>4</option>
									<option style='color: #555555;' value='3'>3</option>
									<option style='color: #555555;' value='2'>2</option>
									<option style='color: #555555;' value='1'>1 (Never)</option>
								</select>
								<div class=\"help-block with-errors\"></div>
						</div>

						<div class='form-group has-feedback'>
							<label for='f-feedback-rate' class='control-label'>How would you rate your overall experience with our site.</label>
								<select id='f-feedback-rate' name='f-feedback-rate' required class='btn-group form-control bootstrap-select f-bootstrap-100 show-menu-arrow show-tick' style='color: #7d7d7d;' onChange='this.style.color=\"#555555\";'>
									<option disabled selected style='display: none;' value=''> -- select your experience -- </option>
									<option style='color: #555555;' value='10'>10 (Excellent)</option>
									<option style='color: #555555;' value='9'>9</option>
									<option style='color: #555555;' value='8'>8</option>
									<option style='color: #555555;' value='7'>7</option>
									<option style='color: #555555;' value='6'>6</option>
									<option style='color: #555555;' value='5'>5</option>
									<option style='color: #555555;' value='4'>4</option>
									<option style='color: #555555;' value='3'>3</option>
									<option style='color: #555555;' value='2'>2</option>
									<option style='color: #555555;' value='1'>1 (Poorly)</option>
								</select>
								<div class=\"help-block with-errors\"></div>
						</div>

						<div class='form-group'>
							<label for='f-feedback-extra'>What would you like to see going forward from leos.com?</label>
							<textarea class='form-control' id='f-feedback-extra' rows='2' style='resize: none;' maxlength='300'></textarea>
						</div>

						<div class=\"form-group\">
							<button style='display: none;' id='f-feedback-submit-button' type=\"submit\" class=\"btn btn-info btn-block\"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Send Feedback</button>
						</div>

						</form>
					</div>

					<div id=\"fluid-main-modal-feedback-footer\" class=\"modal-footer\">
						<div id='fluid-modal-back-feedback-button' class='pull-left'><button type=\"button\" class=\"btn btn-danger\" onClick=\"js_fluid_feedback_none();\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> <div id='fluid-modal-close-feedback-button-text' style='display: inline-block;'>No Thanks</div></button></div>
						<div id='fluid-modal-close-feedback-button' class='pull-right'><button type=\"button\" class=\"btn btn-success\" onclick=\"$('#f-feedback-submit-button').click();\"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> <div id='fluid-modal-send-feedback-button-text' style='display: inline-block;'>Send Feedback</div></button></div>
					</div>

				</div>

			</div>
		");

		define("HTML_SUPPORT_DIALOG", "
		<div class='modal-dialog' role='document'>
		  <div class='modal-content'>

			  <div id='store-message-modal' class='modal-body'>

			  </div>

			  <div id='error-modal-footer' class='modal-footer'>
				  <div style='float:right;'><button type='button' class='btn btn-success' data-dismiss='modal'>Close</button></div>
			  </div>

		  </div>
		</div>
		");
?>
