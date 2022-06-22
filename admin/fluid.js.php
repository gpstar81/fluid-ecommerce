<?php
// fluid.js.php
// Michael Rajotte - 2016 June
?>

<script>
var FluidVariables = {};
FluidVariables.v_product = {};
	FluidVariables.v_product.p_id = "";
	FluidVariables.v_product.p_status = "";
	FluidVariables.v_product.p_zero_status = "";
	FluidVariables.v_product.p_manufacturer = "";
	FluidVariables.v_product.p_category = "";
	FluidVariables.v_product.p_price = "";
	FluidVariables.v_product.p_cost = "";
	FluidVariables.v_product.p_cost_real = "";
	FluidVariables.v_product.p_cost_real_old = "";
	FluidVariables.v_product.p_price_discount = "";
	FluidVariables.v_product.p_discount_date_end = "";
	FluidVariables.v_product.p_discount_date_start = "";
	FluidVariables.v_product.p_newarrivalenddate = "";
	FluidVariables.v_product.p_stock = "";
	FluidVariables.v_product.p_stock_old = "";
	FluidVariables.v_product.p_buyqty = "";
	FluidVariables.v_product.p_name = "";
	FluidVariables.v_product.p_barcode = "";
	FluidVariables.v_product.p_mfg_number = "";
	FluidVariables.v_product.p_instore = "";
	FluidVariables.v_product.p_arrivaltype = "";
	FluidVariables.v_product.p_freeship = "";
	FluidVariables.v_product.p_trending = "";
	FluidVariables.v_product.p_preorder = "";
	FluidVariables.v_product.p_rental = "";
	FluidVariables.v_product.p_special_order = "";
	FluidVariables.v_product.p_namenum = "";
	FluidVariables.v_product.p_showalways = "";
	FluidVariables.v_product.p_rebate_claim = "";
	FluidVariables.v_product.p_component = "";
	FluidVariables.v_product.p_component_data = "";
	FluidVariables.v_product.p_component_html = "";
	FluidVariables.v_product.p_stock_end = "";
	FluidVariables.v_product.p_description = "";
	FluidVariables.v_product.p_details = "";
	FluidVariables.v_product.p_specs = "";
	FluidVariables.v_product.p_inthebox = "";
	FluidVariables.v_product.p_seo = "";
	FluidVariables.v_product.p_keywords = "";
	FluidVariables.v_product.p_images = {};
	FluidVariables.v_product.p_imageorder = {};
	FluidVariables.v_product.p_c_filters = "";
	FluidVariables.v_product.p_c_linking = "";
	FluidVariables.v_product.p_m_filters = "";
	FluidVariables.v_product.p_length = "";
	FluidVariables.v_product.p_width = "";
	FluidVariables.v_product.p_height = "";
	FluidVariables.v_product.p_weight = "";
	FluidVariables.v_product.p_formula_status = "";
	FluidVariables.v_product.p_formula_operation = "";
	FluidVariables.v_product.p_formula_math = "";
	FluidVariables.v_product.p_formula_application = "";
	FluidVariables.v_product.p_formula_discount_date_end = "";
	FluidVariables.v_product.p_formula_discount_date_start = "";
	FluidVariables.v_product.p_formula_item_html = "";
	FluidVariables.v_product.p_formula_item_faux_html = "";
	FluidVariables.v_product.p_formula_items_data = "";
	FluidVariables.v_product.p_formula_items_faux_data = "";
	FluidVariables.v_product.p_formula_flip = "";
	FluidVariables.v_product.p_formula_message_display = "";
	FluidVariables.v_product.p_formula_message = "";
	FluidVariables.v_product.p_date_hide = "";
	FluidVariables.v_product.p_category_items_data = "";

FluidVariables.v_multi_editor = {};
	FluidVariables.v_multi_editor.items = {};
	FluidVariables.v_multi_editor.footer_back_html = "";
	FluidVariables.v_multi_editor.footer_save_html = "";
	FluidVariables.v_multi_editor.item_list_html = "";
	FluidVariables.v_multi_editor.tabs_html = "";
	FluidVariables.v_multi_editor.item_tabs_html = "";
	FluidVariables.v_multi_editor.editor_html = "";
	FluidVariables.v_multi_editor.image_html = "";
	FluidVariables.v_multi_editor.imageDropZoneDelete = null;
	FluidVariables.v_multi_editor.formula_html = "";
	FluidVariables.v_multi_editor.link_html = "";
	FluidVariables.v_multi_editor.component_html = "";

FluidVariables.v_category = {};
	FluidVariables.v_category.f_id = "";
	FluidVariables.v_category.c_id = "";
	FluidVariables.v_category.c_parent_id = "";
	FluidVariables.v_category.c_status = "";
	FluidVariables.v_category.c_name = "";
	FluidVariables.v_category.c_seo = "";
	FluidVariables.v_category.c_keywords = "";
	FluidVariables.v_category.c_desc = "";
	FluidVariables.v_category.c_weight = "";
	FluidVariables.v_category.c_google_cat_id = "";
	FluidVariables.v_category.c_images = {};
	FluidVariables.v_category.c_imageorder = {};
	FluidVariables.v_category.c_formula_status = null;
	FluidVariables.v_category.c_formula_math = null;


FluidVariables.v_manufacturer = {};
FluidVariables.v_selection = {};
	FluidVariables.v_selection.p_selection = {};
	FluidVariables.v_selection.c_selection = {};
FluidVariables.v_r_selection = {};
FluidVariables.v_r_total = parseFloat(0.00);
FluidVariables.v_r_total_tax = parseFloat(0.00);
FluidVariables.v_r_total_complete = parseFloat(0.00);
FluidVariables.v_r_manual_input = parseFloat(0.00);
FluidVariables.v_r_refund_tax_toggle == true
FluidVariables.v_sort_prevent = false;
FluidVariables.f_import = {};
FluidVariables.f_staging_prevent_import = true;
FluidVariables.s_serial = {};
FluidVariables.s_scan = {};
FluidVariables.s_scan_buffer = {};
FluidVariables.b_boxes = {};
FluidVariables.t_taxes = {};

FluidVariables.v_multi_item_scroll = 0;
FluidVariables.v_select_option = 0;
FluidVariables.v_import_items_found = {};

FluidVariables.f_page_num = 1;
FluidVariables.f_search_input = "";
FluidVariables.d_mode = null;

FluidVariables.f_quick_edit_id = null;
FluidVariables.f_quick_edit_mode = null;
FluidVariables.f_quick_edit_cat = null;
FluidVariables.f_quick_edit_type = null;

FluidVariables.v_multi_editor_operations = {};
	FluidVariables.v_multi_editor_operations.f_id = null;
	FluidVariables.v_multi_editor_operations.f_copy_buffer = "";
	FluidVariables.v_multi_editor_operations.f_copy_buffer_mode = "";

<?php
	$detect = new Mobile_Detect;
?>

<?php
/*FluidVariables.f_order = {};
	FluidVariables.f_order.s_status = "";
*/
?>
FluidVariables.error = function() { alert("test"); }

var imageDropzone;

$(document).bind("contextmenu", function (event) {
	if(event.delegateTarget.activeElement.name == "p_item") {
	    event.preventDefault();

		FluidVariables.v_multi_editor_operations.f_id = event.delegateTarget.activeElement.getAttribute("data-id");

	    // Show the menu.
	    $(".custom-menu").finish().toggle(100).css({
	        top: event.pageY + "px",
	        left: event.pageX + "px"
	    });
	}
});

// If the document is clicked somewhere.
$(document).bind("mousedown", function (e) {
    // If the clicked element is not the menu.
    if (!$(e.target).parents(".custom-menu").length > 0) {
        // Hide it.
        $(".custom-menu").hide(100);
    }
});

$(document).bind("contextmenu", function (event) {
	if(event.target.getAttribute("data-editor") == "f_quickedit") {
	    event.preventDefault();

		FluidVariables.f_quick_edit_id = event.target.getAttribute("data-id");
		FluidVariables.f_quick_edit_mode = event.target.getAttribute("data-fmode");
		FluidVariables.f_quick_edit_cat = event.target.getAttribute("data-catid");
		FluidVariables.f_quick_edit_column = event.target.getAttribute("data-column");
		FluidVariables.f_quick_edit_type = event.target.getAttribute("data-type");

		document.getElementById("f-quick-edit-name").innerHTML = event.target.getAttribute("data-name");
		document.getElementById("f-quick-edit-data").value = Base64.decode(event.target.getAttribute("data-values"));

	    // Show the menu.
	    $(".custom-menu-editor").finish().toggle(100).css({
	        top: event.pageY + "px",
	        left: event.pageX - 190 + "px"
	    });

		document.getElementById("f-quick-edit-data").focus();
	}
});

// If the document is clicked somewhere.
$(document).bind("mousedown", function (e) {
    // If the clicked element is not the menu.
    if (!$(e.target).parents(".custom-menu-editor").length > 0) {
        // Hide it.
        $(".custom-menu-editor").hide(100);
    }
});

$(document).keyup(function(event) {
    if ($("#f-quick-edit-data").is(":focus") && event.key == "Enter") {
		if(event.keyCode == 13) {
			js_fluid_quick_edit();
		}
    }
});

function js_fluid_quick_edit() {
	try {
		var f_item = {};
			f_item[FluidVariables.f_quick_edit_id] = {};
			f_item[FluidVariables.f_quick_edit_id]['p_id'] = FluidVariables.f_quick_edit_id;
			f_item[FluidVariables.f_quick_edit_id]['p_catid'] = FluidVariables.f_quick_edit_cat;

		var f_selection_data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data = Base64.encode(JSON.stringify(f_item));
		var data_send = {};
			data_send.mode = {};
			data_send.data = {};
			data_send.type = {};

		data_send.mode = Base64.encode(FluidVariables.f_quick_edit_column);
		data_send.type = FluidVariables.f_quick_edit_type;
		data_send.data = Base64.encode(document.getElementById("f-quick-edit-data").value);

		data_send.f_search_input = FluidVariables.f_search_input;
		var data_send_obj = Base64.encode(JSON.stringify(data_send));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ATTRIBUTES_ADMIN;?>", dataobj: "load=true&function=php_set_attribute&data=" + data + "&data_send_obj=" + data_send_obj + "&f_selection_data=" + f_selection_data + "&page_num=" + FluidVariables.f_page_num + "&mode=" + FluidVariables.f_quick_edit_mode}));

		$(".custom-menu-editor").hide(100);

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_editor_copy(f_mode) {
	try {
		js_editor_update_disable_all();
		
		FluidVariables.v_multi_editor_operations.f_copy_buffer = FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'];
		document.getElementById('f-editor-paste-' + f_mode).className = "";
		
		if(f_mode == "p_category_items_data") {
			document.getElementById('f-editor-append-' + f_mode).className = "";
		}

		// Hide the menu.
		$(".custom-menu").hide(100);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_editor_paste(f_mode) {
	try {
		switch(f_mode) {
			case "description":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_description = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_description;
			break;

			case "details":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_details = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_details;
			break;

			case "specs":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_specs = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_specs;
			break;

			case "inbox":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_inthebox = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_inthebox;
			break;

			case "keywords":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_keywords = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_keywords;
			break;

			case "dimensions":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_length = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_length;
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_width = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_width;
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_height = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_height;
			break;

			case "weight":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_weight = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_weight;

			case "p_category_items_data":
				FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_category_items_data = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_category_items_data;
			break;
		}

		FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['update'] = true;

		// Update the pasted cell to be highlighted after a paste (flash green then to it's edited state after).
		<?php // --> Temporary hold the new item innerHTML data before we switch the old innerHTML list over. ?>
		<?php
		/*
		var new_html_data = Base64.decode(FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_weight) + " " + document.getElementById("product-name").value + "<div><div style='display: inline-block; font-size: 10px; font-style: oblique; font-weight: 600;'>upc: " + document.getElementById("product-barcode").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>code: " + document.getElementById("product-mfg-number").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>cost: " + document.getElementById("product-cost").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>price: " + document.getElementById("product-price").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; color: red; font-weight: 600;'>price disc: " + document.getElementById("product-price-discount").value + "</div></div>";
		*/
		?>

		<?php // --> Store the updated full name into the obj listing, for building the item list in the confirmation save menu. -> ?>
		<?php
		/*
		FluidVariables.v_multi_editor.items[p_id]['p_fullname'] = Base64.encode(document.getElementById('product-manufacturer').options[document.getElementById('product-manufacturer').selectedIndex].getAttribute('data-name') + " " + document.getElementById("product-name").value);
		*/
		?>

		<?php // --> Update the item in the list to have a new background color to show changes were made. ?>
		document.getElementById('multi-item-button-' + FluidVariables.v_multi_editor_operations.f_id).style.backgroundColor = '#92FFA6';

		<?php // --> Then save the updated item list to the object. ?>
		FluidVariables.v_multi_editor.item_list_html = Base64.encode(document.getElementById('multi-item-editor-div').innerHTML);

		<?php // --> Enable the continue button to allow saving changes. ?>
		document.getElementById('multi-item-continue-button').disabled = false;

		// Hide the menu.
		$(".custom-menu").hide(100);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_editor_append(f_mode) {
	try {
		switch(f_mode) {
			case "p_category_items_data":
				var buffer_decode;
				var buffer_original;
				
				if(FluidVariables.v_multi_editor_operations.f_copy_buffer.p_category_items_data != "") {
					buffer_decode = JSON.parse(Base64.decode(FluidVariables.v_multi_editor_operations.f_copy_buffer.p_category_items_data));
									
					if(FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_category_items_data != "") {
						buffer_original = JSON.parse(Base64.decode(FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_category_items_data));
						
						var length = Object.keys(buffer_original).length;
						
						for(var obj in buffer_decode) {
							buffer_original[length] = buffer_decode[obj];

							length++;
						}
						
						FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_category_items_data = Base64.encode(JSON.stringify(buffer_original));
					}
					else {
						FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['data_obj'].p_category_items_data = FluidVariables.v_multi_editor_operations.f_copy_buffer.p_category_items_data;
					}
				}
				
			break;
		}

		FluidVariables.v_multi_editor.items[FluidVariables.v_multi_editor_operations.f_id]['update'] = true;

		<?php // --> Update the item in the list to have a new background color to show changes were made. ?>
		document.getElementById('multi-item-button-' + FluidVariables.v_multi_editor_operations.f_id).style.backgroundColor = '#92FFA6';

		<?php // --> Then save the updated item list to the object. ?>
		FluidVariables.v_multi_editor.item_list_html = Base64.encode(document.getElementById('multi-item-editor-div').innerHTML);

		<?php // --> Enable the continue button to allow saving changes. ?>
		document.getElementById('multi-item-continue-button').disabled = false;

		// Hide the menu.
		$(".custom-menu").hide(100);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_editor_update_disable_all() {
	try {
		document.getElementById('f-editor-paste-description').className = "disabled";
		document.getElementById('f-editor-paste-details').className = "disabled";
		document.getElementById('f-editor-paste-specs').className = "disabled";
		document.getElementById('f-editor-paste-inbox').className = "disabled";
		document.getElementById('f-editor-paste-keywords').className = "disabled";
		document.getElementById('f-editor-paste-dimensions').className = "disabled";
		document.getElementById('f-editor-paste-weight').className = "disabled";
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Update any select pickers in case any are in the new innerHTML data. ?>
function js_update_select_pickers() {
	$('select').selectpicker('refresh');
	<?php
	if($detect->isMobile() && !$detect->isTablet())
		echo "$('select').selectpicker('mobile');";
	else
		echo "$('select').selectpicker();";
	?>
}

<?php // Clear out serial temporary scan data. ?>
function js_scan_clear(f_update) {
	try {
		for(var key in FluidVariables.s_scan) {
			if(f_update == true) {
				if(document.getElementById('p_td_id_stock_' + FluidVariables.s_scan[key]['p_id']) != undefined)
					document.getElementById('p_td_id_stock_' + FluidVariables.s_scan[key]['p_id']).innerHTML = FluidVariables.s_scan[key]['p_stock_adj'];
			}

			delete FluidVariables.s_scan[key];
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Cleans up scan data after saving the updates ?>
function js_scan_cleanup(f_scan) {
	try {
		for(var key in f_scan) {
			if(document.getElementById('p_td_id_stock_' + f_scan[key]['p_id']) != undefined)
				document.getElementById('p_td_id_stock_' + f_scan[key]['p_id']).innerHTML = f_scan[key]['p_stock_adj'];

			delete FluidVariables.s_scan[f_scan[key]['p_mfgcode']];
			document.getElementById("fluid-cart-scroll-edit").removeChild(document.getElementById("fluid-cart-editor-item-" + f_scan[key]['p_id']));
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Decrease the quantity of a item in the cart. ?>
function js_fluid_scan_decrease_num(p_id, p_mfgcode) {
	try {
		var element = $('#fluid-cart-editor-qty-' + p_id);
		var el_adj = $('#fluid-cart-editor-qty-adj-' + p_id);

		var v = element.val()-1;

		if(v >= element.attr('min')) {
			element.val(v);
			FluidVariables.s_scan[p_mfgcode]['p_stock_adj'] = v;
			FluidVariables.s_scan[p_mfgcode]['p_adj'] = FluidVariables.s_scan[p_mfgcode]['p_stock_adj'] - FluidVariables.s_scan[p_mfgcode]['p_stock']
			el_adj.val(FluidVariables.s_scan[p_mfgcode]['p_adj']);
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Increase the quantity of a item in the cart. ?>
function js_fluid_scan_increase_num(p_id, p_mfgcode) {
	try {
		var element = $('#fluid-cart-editor-qty-' + p_id);
		var el_adj = $('#fluid-cart-editor-qty-adj-' + p_id);

		var v = element.val()*1+1;

		element.val(v);

		FluidVariables.s_scan[p_mfgcode]['p_stock_adj'] = v;
		FluidVariables.s_scan[p_mfgcode]['p_adj'] = FluidVariables.s_scan[p_mfgcode]['p_stock_adj'] - FluidVariables.s_scan[p_mfgcode]['p_stock']
		el_adj.val(FluidVariables.s_scan[p_mfgcode]['p_adj']);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Control the Save All button during stock scanning. ?>
function js_scan_set_save_all_button() {
	try {
		if(Object.keys(FluidVariables.s_scan).length > 0)
			document.getElementById('fluid_scan_save_all_btn').disabled = false;
		else
			document.getElementById('fluid_scan_save_all_btn').disabled = true;
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets the scan data object. ?>
function js_fluid_scan_data_set(data) {
	try {
		FluidVariables.s_scan = data;
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets the scan history data object. ?>
function js_fluid_scan_history_data_set(f_data) {
	try {
		var Fluid_tmp_data = {};
		var f_html = "";

		var i = 0;
		for(var key in f_data) {
			if(i == 30)
				break;

			Fluid_tmp_data[i] = f_data[key];

			f_html += Base64.decode(f_data[key]['p_html']);

			i++;
		}

		for(var key in FluidVariables.s_scan_history) {
			if(i == 30)
				break;

			Fluid_tmp_data[i] = FluidVariables.s_scan_history[key];

			f_html += Base64.decode(FluidVariables.s_scan_history[key]['p_html']);

			i++;
		}

		FluidVariables.s_scan_history = Fluid_tmp_data;
		document.getElementById('f-scan-history').innerHTML = f_html;
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Prep the loading of the scan modal. ?>
function js_scan_modal_prep(f_mode) {
	try {
		var FluidData = {};
			FluidData = FluidVariables.s_scan_history;
			<?php //FluidData.s_selection = FluidVariables.v_selection.p_selection; ?>

		var data = Base64.encode(JSON.stringify(FluidData));
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_scanning_items_modal&fmode=" + f_mode + "&mode=items&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Update a individual scanned item stock numbers. ?>
function js_scan_update(p_mfgcode) {
	try {
		var FluidData = {};
			FluidData.s_all = false;
			FluidData.s_scan = {};
			FluidData.s_scan[Base64.decode(p_mfgcode)] = FluidVariables.s_scan[Base64.decode(p_mfgcode)];
			<?php //FluidData.s_selection = FluidVariables.v_selection.p_selection; ?>

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_scan_update&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Updates all the scanned items. ?>
function js_scan_update_all() {
	try {
		var FluidData = {};
			FluidData.s_all = true;
			FluidData.s_scan = FluidVariables.s_scan;
			<?php //FluidData.s_selection = FluidVariables.v_selection.p_selection; ?>

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_scan_update&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_scan_scroll_top() {
	try {
		if(document.getElementById('f_scan_code') != null) {
			var topPos = document.getElementById('f_scan_code').offsetTop;

			$('#f-stock-scroll-div').animate({
			scrollTop: topPos
			}, "slow");
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_scan_scroll(data) {
	try {
		if(document.getElementById('section-' + data) != null) {
			var topPos = document.getElementById('section-' + data).offsetTop;

			$('#f-stock-scroll-div').animate({
			scrollTop: topPos
			}, "slow");
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // A item was scanned, update and or send some of the data. ?>
function js_fluid_scan(f_mode) {
	try {
		if(FluidVariables.s_scan[document.getElementById('f_scan_code').value] != null) {
			js_scan_scroll(document.getElementById('f_scan_code').value);

			if(f_mode == "minus")
				js_fluid_scan_decrease_num(FluidVariables.s_scan[document.getElementById('f_scan_code').value]['p_id'], FluidVariables.s_scan[document.getElementById('f_scan_code').value]['p_mfgcode']);
			else if(f_mode == "plus")
				js_fluid_scan_increase_num(FluidVariables.s_scan[document.getElementById('f_scan_code').value]['p_id'], FluidVariables.s_scan[document.getElementById('f_scan_code').value]['p_mfgcode']);

			for(var key in FluidVariables.s_scan) {
				var s_element = document.getElementById('f-scan-row-' + key);

				if(s_element != undefined) {
					s_element.classList.remove("f-scan-animated");
					void s_element.offsetWidth;
				}
			}

			var f_element = document.getElementById('f-scan-row-' + document.getElementById('f_scan_code').value);
			<?php
			/*
				//f_element.className = f_element.className.replace( /(?:^|\s)f-scan-animated(?!\S)/g , '' );
				//f_element.className += "f-scan-animated";
			*/
			?>

			f_element.classList.remove("f-scan-animated");
			void f_element.offsetWidth;
			f_element.classList.add("f-scan-animated");

			document.getElementById('f_scan_code').value = "";
		}
		else {
			$(document).off("keypress");

			<?php // Lets disable all key pressing ?>
			$(document).on("keypress", function (e) {
				e.stopPropagation();
			});

			document.getElementById('f_scan_code').disabled = true;

			var FluidData = {};
				FluidData.s_code = document.getElementById('f_scan_code').value;
				FluidData.s_scan = FluidVariables.s_scan;
				FluidData.f_mode = f_mode;

			var data = Base64.encode(JSON.stringify(FluidData));

			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_scan&data=" + data}));

			document.getElementById('f_scan_code').value = "";

			js_fluid_ajax(data_obj);
		}
	}
	catch(err) {
		js_scan_init("f_scan_code"); <?php // --> If scanning failed, reset the keyboard. ?>
		js_debug_error(err);
	}
}

<?php // Scan initalization for stock scanning updating. ?>
function js_scan_init(data) {
	try {
		$(document).off("keydown");
		$(document).off("keypress");

		document.getElementById('f_scan_code').disabled = false;

		$(document).on("keypress", function (e) {
			<?php // 13 enter. e.keyCode != 13 ?>

			if(e.keyCode != 13 && $('#' + data).is(":focus") == false) {
				document.getElementById(data).value += e.key;
			}

			if(e.keyCode == 13) {
				<?php // If enter was detected, lets go scan and find the item. ?>
				$('#f_scan_btn').click();
			}
		});
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Maximize a dialog modal window to full screen. ?>
function js_fluid_maximize() {
	try {
		$("#editing-dialog").css({"max-width": "100%", "width": "100%", "margin": "0px"});
		$("#fluid-modal").removeClass("f-modal-padding");
		document.getElementById("fluid-modal").className += " f-modal-padding";

		$("#fluid-modal").css({"overflow-y": "auto"});

		if(document.getElementById('product-create-innerhtml') != null)
			$("#product-create-innerhtml").css({"max-height": "100%"}); //order-innerhtml serial-innerhtml f-multi-scroll-div

		if(document.getElementById('f-multi-scroll-div') != null)
			$("#f-multi-scroll-div").css({"max-height": "100%"});

		if(document.getElementById('order-innerhtml') != null)
			$("#order-innerhtml").css({"max-height": "100%"});

		if(document.getElementById('serial-innerhtml') != null)
			$("#serial-innerhtml").css({"max-height": "100%"});

		$("#f-window-maximize").css({"display": "none"});
		$("#f-window-minimize").css({"display": "inline-block"});
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Minimize a dialog modal window to normal size. ?>
function js_fluid_minimize() {
	try {
		$("#editing-dialog").css({"max-width": "", "width": "", "margin": ""});
		$("#fluid-modal").removeClass("f-modal-padding");
		$("#fluid-modal").css({"overflow-y": "auto"});

		if(document.getElementById('product-create-innerhtml') != null)
			$("#product-create-innerhtml").css({"max-height": "60vh"}); //order-innerhtml serial-innerhtml f-multi-scroll-div

		if(document.getElementById('f-multi-scroll-div') != null)
			$("#f-multi-scroll-div").css({"max-height": "60vh"});

		if(document.getElementById('order-innerhtml') != null)
			$("#order-innerhtml").css({"max-height": "60vh"});

		if(document.getElementById('serial-innerhtml') != null)
			$("#serial-innerhtml").css({"max-height": "60vh"});

		$("#f-window-minimize").css({"display": "none"});
		$("#f-window-maximize").css({"display": "inline-block"});
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Maximize a scan dialog modal window to full screen. ?>
function js_fluid_scan_maximize() {
	try {
		$("#f-stock-dialog").css({"max-width": "100%", "width": "100%", "margin": "0px"});
		$("#fluid-modal").removeClass("f-modal-padding");
		document.getElementById("fluid-modal").className += " f-modal-padding";
		$("#f-stock-scroll-div").css({"max-height": "100%"});
		//$("#f-modal-body").css({"height": "100vh"});
		//$("#f-scan-footer").css({"position": "fixed", "bottom" : "0px", "width" : "100%"});
		$("#f-window-scan-maximize").css({"display": "none"});
		$("#f-window-scan-minimize").css({"display": "inline-block"});
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Minimize a scan dialog modal window to normal size. ?>
function js_fluid_scan_minimize() {
	try {
		$("#f-stock-dialog").css({"max-width": "", "width": "", "margin": ""});
		$("#fluid-modal").removeClass("f-modal-padding");

		$("#f-stock-scroll-div").css({"max-height": "60vh"});
		//$("#f-modal-body").css({"height": ""});
		//$("#f-scan-footer").css({"position": "", "bottom" : "", "width" : ""});
		$("#f-window-scan-minimize").css({"display": "none"});
		$("#f-window-scan-maximize").css({"display": "inline-block"});
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // For downloading searching and downloading images automatically. ?>
function js_image_downloader() {
	try {
		var FluidData = {};
			FluidData.items = FluidVariables.v_selection.p_selection;
			FluidData.options = document.getElementById("f-downloader-options").options[document.getElementById("f-downloader-options").selectedIndex].value;
			FluidData.region = document.getElementById("f-downloader-region").options[document.getElementById("f-downloader-region").selectedIndex].value;
			FluidData.override = document.getElementById("f-downloader-override").options[document.getElementById("f-downloader-override").selectedIndex].value;
			FluidData.editortype = document.getElementById("f-downloader-editor").options[document.getElementById("f-downloader-editor").selectedIndex].value;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_image_downloader&data=" + data}));

		js_fluid_ajax_fdom(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Used to override the <tr> clicks on clicking the product edit button. ?>
function js_cancel_event(evt) {
	try {
		// The window.event may not work in firefox. Chrome only. Need to pass event variable into this function for it to work in firefox.
		var e = (typeof evt != 'undefined') ? evt : window.event;
		e.cancelBubble = true;
		//e.stopPropagation();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_category_add_update(data) {
	// Removing the no items or no child categories HTML dom element.
	js_html_remove_element('cat-parent-empty-' + data['c_parent_id']);

	// Append the new category to the bottom of a category list.
	if(data['mode'] == 'add') {
		if(data['parent'] == true) {
			var div = document.getElementById('fluid-category-listing'); // Appending a parent category.
		}
		else
			var div = document.getElementById('fluid-category-listing-childs-' + data['c_parent_id']); // Appending a child category.

		if(typeof div != "undefined" && div != null)
			div.innerHTML = div.innerHTML + data['html'];

		js_sortable_categories();
	}
	else if(data['mode'] == 'edit') {
		var html_array = JSON.parse(Base64.decode(data['html']));
		var enable_data = data['enable'];

		// Update the innerhtml of the cat data.
		for(var key in html_array) {
			// Update the hidden data display notification.
			if(enable_data == 0)
				document.getElementById('category-badge-select-lock-' + key).style.display = "block";
			else
				document.getElementById('category-badge-select-lock-' + key).style.display = "none";

			// Some weird bug in javascript, if I call these merged together with data['parent'] child out of order, then the padding doesn't work correctly. That is why I am using the code twice :(
			if(data['parent'] == true) {
				document.getElementById('category-span-open-' + key).innerHTML = html_array[key];
				// Used to compensate for padding adjustments to keep text aligned correctly. We have 2, one for open stack and one for closed stack.
				document.getElementsByName('dropdown-cat-id-' + key)[0].style.paddingLeft = '10px';
			}
			else if(data['parent'] == false) {
				document.getElementById('category-span-open-' + key).innerHTML = html_array[key];
				document.getElementById('category-span-closed-' + key).innerHTML = html_array[key];

				// Used to compensate for padding adjustments to keep text aligned correctly. We have 2, one for open stack and one for closed stack.
				document.getElementsByName('dropdown-cat-id-' + key)[0].style.paddingLeft = '10px';
				document.getElementsByName('dropdown-cat-id-' + key)[1].style.paddingLeft = '10px';

				// If the child category is moved to a different parent category, lets move it's DOM.
				if(data['c_parent_id'] != data['c_parent_id_prev']) {
					var old_ul = document.getElementById('category-list-div-' + key);
					var new_parent = document.getElementById('fluid-category-listing-childs-' + data['c_parent_id']);

					if(typeof new_parent != "undefined" && new_parent != null)
						new_parent.appendChild(old_ul);
					else {
						js_html_remove_element('category-list-div-' + key);

						var array_tmp = [parseInt(key)];
						js_select_clear_p_selection_category(Base64.encode(JSON.stringify(array_tmp)));
					}
					// Updating the hidden parent data. What is this really used for again??
					//document.getElementById('category-list-div-' + data['c_parent_id']).innerHTML = data['return_last'];
				}
			}
		}

		js_sortable_categories();
	}
}

function js_category_create_and_edit(data64) {
	var data_obj = JSON.parse(Base64.decode(data64));
	var parent_mode = data_obj['parent'];

	if(data_obj['mode'] == 'edit')
		FluidVariables.v_category.c_id = data_obj['id'];

	FluidVariables.v_category.c_status = Base64.encode(document.getElementById("category-status").options[document.getElementById("category-status").selectedIndex].value);

	// Only grab the parent id if we are in child mode.
	if(typeof parent_mode != "undefined" && parent_mode == false)
		FluidVariables.v_category.c_parent_id = Base64.encode(document.getElementById("parent_id").options[document.getElementById("parent_id").selectedIndex].value);

	FluidVariables.v_category.c_name = Base64.encode(document.getElementById('category-name').value);
	FluidVariables.v_category.c_weight = Base64.encode(document.getElementById('category-weight').value);
	FluidVariables.v_category.c_google_cat_id = Base64.encode(document.getElementById('category-google').value);

	FluidVariables.v_category.c_seo = Base64.encode(document.getElementById('category-seo-textarea').value);
	FluidVariables.v_category.c_keywords = Base64.encode(document.getElementById('category-keywords-textarea').value);

	// Iterate through the images and prep for saving.
	try {
		var i = 1;
		imageDropzone.files.forEach(function(file) {
			FluidVariables.v_category.c_imageorder[i] = {name: file.name, size: file.size , xhr: file.xhr['response']};
		    i++;
		});
	}
	catch(err) {
		js_debug_error(err);
	}

	<?php // --> Reset the formula data. ?>
	FluidVariables.v_category.c_formula_status = null;
	FluidVariables.v_category.c_formula_math = null;

	// Only try to process filters if we are in child mode.
	if(typeof parent_mode != "undefined" && parent_mode == false) {
		var filters_data = Base64.encode(JSON.stringify(js_filters_prep()));

		if(data_obj['f_mode'] != "manufacturers") {
			FluidVariables.v_category.c_formula_status = Base64.encode(document.getElementById("formula-status").options[document.getElementById("formula-status").selectedIndex].value);
			FluidVariables.v_category.c_formula_math = Base64.encode(document.getElementById('formula-math').value);
		}
	}

	var data = Base64.encode(JSON.stringify(FluidVariables.v_category));

	// Pass the product selection data so selected products stay selected during a category refresh.
	var selection_data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

	var data_send_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_category_create_and_edit&page_num=" + FluidVariables.f_page_num + "&mode=" + data_obj['mode_filter']  + "&modeedit=" + data_obj['mode'] + "&data=" + data + "&filters=" + filters_data + "&selection=" + selection_data + "&parent=" + data_obj['parent']}));

	js_fluid_ajax(data_send_obj);
}

function js_category_create_filter(c_id, mode) {
	try {
		var cat_filter_name = document.getElementById('category-filter-name');

		if(cat_filter_name.value.length < 1) {
			js_debug_error("Enter a filter category name before adding to the filter category list.");
		}
		else {
			var filters_data = Base64.encode(JSON.stringify(js_filters_prep()));

			var data_send_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_category_create_filter&filterdata=" + filters_data + "&mode=" + mode + "&filter=" + Base64.encode(cat_filter_name.value) + "&category=" + c_id}));

			js_fluid_ajax(data_send_obj);

			cat_filter_name.value = "";
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_create_category() {
	try {
		var cat_filter_name = document.getElementById('category-filter-name');

		if(cat_filter_name.value.length < 1) {
			js_debug_error("Enter a filter category name before adding to the filter category list.");
		}
		else {
			var FluidData = {};
			FluidData.f_link_data = js_product_category_prep();
			FluidData.f_cat_name = cat_filter_name.value;
			FluidData.f_selection = FluidVariables.v_selection;

			var data = base64EncodingUTF8(JSON.stringify(FluidData));

			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_create_category&data=" + data}));

			cat_filter_name.value = "";

			js_fluid_ajax(data_obj);
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_create_category_menu() {
	try {
		var cat_filter_name = document.getElementById('category-filter-name');

		if(cat_filter_name.value.length < 1) {
			js_debug_error("Enter a filter category name before adding to the filter category list.");
		}
		else {
			var FluidData = {};
			FluidData.f_link_data = js_product_category_prep();
			FluidData.f_cat_name = cat_filter_name.value;
			FluidData.f_selection = FluidVariables.v_selection;

			var data = base64EncodingUTF8(JSON.stringify(FluidData));

			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_create_category_menu&data=" + data}));

			cat_filter_name.value = "";

			js_fluid_ajax(data_obj);
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_linking_reload() {
	try {

	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_reload_category(f_link_data) {
	try {
		var FluidData = {};
		FluidData.f_link_data = f_link_data;
		FluidData.f_link_refresh = true;
		FluidData.f_selection = FluidVariables.v_selection;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_create_category&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_category_create_sub_filter() {
	try {
		if($('#category-filter-select').has('option').length > 0) {
			var cat_filter_id = document.getElementById("category-filter-select").options[document.getElementById("category-filter-select").selectedIndex].value
			var table_cat_new = document.getElementById('filters-cat-' + cat_filter_id);

			var cat_filter_keyword = document.getElementById('category-filter-keyword');

			if(cat_filter_keyword.value.length < 1) {
				js_debug_error("Enter a filter keyword before adding to the filter list.");
			}
			else {
				var rows = table_cat_new.getElementsByTagName("tbody")[0].getElementsByTagName("tr").length;

				var table_ref = table_cat_new.getElementsByTagName('tbody')[0];
				var rand_64 = Base64.encode(Math.floor((Math.random() * 1000000000) + 1));

				// Insert a row at the end of the table.
				var new_row = table_ref.insertRow(table_ref.rows.length);
				new_row.id = "cf-tr-" + rows;
				new_row.innerHTML = "<td id='cf-" + rows + "' style='text-align:center;'><span style='font-size: 16px;' class='glyphicon glyphicon-move moverow' aria-hidden='true'></span></td><td>" + cat_filter_keyword.value + "</td><td style='display:none;'>" + rand_64 + "</td><td style='display:none;'>" + cat_filter_keyword.value + "</td><td style='text-align:center;'><button type='button' class='btn btn-primary' onClick='js_html_remove_element(\"cf-tr-" + rows + "\"); js_category_filter_sub_update_rows(\"" + cat_filter_id + "\"); js_category_filter_sortable();'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></span> Remove</button></td>";

				cat_filter_keyword.value = "";

				js_category_filter_sub_update_rows(cat_filter_id);
				js_category_filter_sortable();
			}
		}
		else
			js_debug_error("Please create a filter category first before creating a sub filter.");
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_category_refresh_select() {
	try {
		var filters_data = Base64.encode(JSON.stringify(js_filters_prep()));

		var data_send_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_category_create_filter&filterdata=" + filters_data}));

		js_fluid_ajax(data_send_obj);

	}
	catch(err) {
		js_debug_error(err);
	}
}

// Enable sorting on the filter category.
function js_category_filter_sortable() {
	// Helper object that keeps the format of the table rows when dragging and dropping to there original size.
	var fix_helper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	$("#filters-cat-new-div").sortable({
		cursor: 'move',
		helper : 'clone', // Prevents the firing of the onClick event on the category stack.
		opacity: 0.5
	});

	var filter_subs = document.getElementsByName('filters-cat-new-div-name');

	// Run through all the sub filters and enable them for sorting.
	for(var x=0; x < filter_subs.length; x++)
		js_category_sub_filter_sortable(filter_subs[x].getAttribute('id'));

}

// Enable sorting on the sub filter categories.
function js_category_sub_filter_sortable(id) {
	try {
		$("#" + id + " tbody").sortable({
			cursor: 'move',
			opacity: 0.5,
			handle: '.moverow'
		});
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Checks to see if the category filter list needs to switch to display the filter list or show no filters created.
function js_category_filter_update_rows() {
	try {
		var ul_filters = document.getElementsByName('filter-list-div-ul-name');

		if(ul_filters.length < 1) {
			document.getElementById('filters-cat-new-div').style.display = "none";
			document.getElementById('filters-cat-none-div').style.display = "block";
		}
		else if(ul_filters.length > 0) {
			document.getElementById('filters-cat-new-div').style.display = "block";
			document.getElementById('filters-cat-none-div').style.display = "none";
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Checks to see if the filter list needs to switch to display the filter list or show no filters created.
function js_category_filter_sub_update_rows(id) {
	try {
		var table_cat_new = document.getElementById('filters-cat-' + id);
		var rows = table_cat_new.getElementsByTagName("tbody")[0].getElementsByTagName("tr").length;

		if(rows < 1)
			document.getElementById('filters-cat-none-' + id).style.display = "block";
		else if(rows > 0)
			document.getElementById('filters-cat-none-' + id).style.display = "none";
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Delete's a category.
function js_category_delete(c_id, mode, parent) {
	var data = Base64.encode(c_id);

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_category_delete&data=" + data + "&mode=" + mode + "&parent=" + parent}));
	js_fluid_ajax(data_obj);
}

function js_category_stack(c_id, url, data, element, event, f_selection, f_quantity) {
	// When clicking on the category block, if it's not on the dropdown menu, then open the stack. If the user clicks on the dropdown menu name, the dropdown menu opens instead via bootstrap.js and prevent the stack from opening.
	if(event.target.id != 'dropdown-cat') {
		if(document.getElementById('category-span-open-' + c_id).style.display == 'none' ) {
			var selection_data = Base64.encode(JSON.stringify(f_selection));
			var data_obj = Base64.encode(JSON.stringify({serverurl: url, dataobj: data + "&selection=" + selection_data + "&f_quantity=" + f_quantity}));

			js_fluid_ajax(data_obj, element);
		}
		else {
			js_category_stack_close(c_id);
		}
	}
}

function js_category_stack_close(c_id) {
	document.getElementById('category-div-' + c_id).innerHTML = "";
	document.getElementById('category-span-open-' + c_id).style.display = "none";
	document.getElementById('category-span-closed-' + c_id).style.display = "block";
}

function js_category_stack_open(data) {
	try {
		document.getElementById(Base64.decode(data['div'])).innerHTML = Base64.decode(data['innerHTML']);
		document.getElementById('category-span-open-' + Base64.decode(data['cid'])).style.display = "block";
		document.getElementById('category-span-closed-' + Base64.decode(data['cid'])).style.display = "none";
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Refresh and clear out the FluidVariables.v_selection object.
function js_clear_fluid_selection() {
	try {
		// Clears the product selection.
		for(var key in FluidVariables.v_selection.p_selection)
			delete FluidVariables.v_selection.p_selection[key];

		// Clears the category product count selection.
		for(var key in FluidVariables.v_selection.c_selection)
			delete FluidVariables.v_selection.c_selection[key];

		FluidVariables.v_import_items_found = {};
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_clear_fluid_refund_selection() {
	try {
		FluidVariables.v_r_total = parseFloat(0.00);
		FluidVariables.v_r_total_complete = parseFloat(0.00);
		FluidVariables.v_r_total_tax = parseFloat(0.00);
		FluidVariables.v_r_refund_tax_toggle == true

		for(var key in FluidVariables.v_r_selection)
			delete FluidVariables.v_r_selection[key];
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Refresh and clear out the FluidVariables.v_category object.
function js_clear_fluid_category() {
	try {
		FluidVariables.v_category.f_id = "";
		FluidVariables.v_category.c_id = "";
		FluidVariables.v_category.c_parent_id = "";
		FluidVariables.v_category.c_status = "";
		FluidVariables.v_category.c_name = "";
		FluidVariables.v_category.c_weight = "";
		FluidVariables.v_category.c_google_cat_id = "";
		FluidVariables.v_category.c_seo = "";
		FluidVariables.v_category.c_keywords = "";

		// Clears product image object.
		for(var key in FluidVariables.v_category.c_images)
			delete FluidVariables.v_category.c_images[key];

		// Clears out the product image order object.
		for(var key in FluidVariables.v_category.c_imageorder)
			delete FluidVariables.v_category.c_imageorder[key];
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Refresh and clear out the FluidVariables.v_product object.
function js_clear_fluid_product() {
	try {
		FluidVariables.v_product.p_id = "";
		FluidVariables.v_product.p_status = "";
		FluidVariables.v_product.p_zero_status = "";
		FluidVariables.v_product.p_instore = "";
		FluidVariables.v_product.p_arrivaltype = "";
		FluidVariables.v_product.p_freeship = "";
		FluidVariables.v_product.p_trending = "";
		FluidVariables.v_product.p_preorder = "";
		FluidVariables.v_product.p_rental = "";
		FluidVariables.v_product.p_special_order = "";
		FluidVariables.v_product.p_namenum = "";
		FluidVariables.v_product.p_showalways = "";
		FluidVariables.v_product.p_rebate_claim = "";
		FluidVariables.v_product.p_component = "";
		FluidVariables.v_product.p_component_data = "";
		FluidVariables.v_product.p_component_html = "";
		FluidVariables.v_product.p_stock_end = "";
		FluidVariables.v_product.p_manufacturer = "";
		FluidVariables.v_product.p_category = "";
		FluidVariables.v_product.p_price = "";
		FluidVariables.v_product.p_cost = "";
		FluidVariables.v_product.p_cost_real = "";
		FluidVariables.v_product.p_cost_real_old = "";
		FluidVariables.v_product.p_price_discount = "";
		FluidVariables.v_product.p_discount_date_end = "";
		FluidVariables.v_product.p_discount_date_start = "";
		FluidVariables.v_product.p_newarrivalenddate = "";
		FluidVariables.v_product.p_stock = "";
		FluidVariables.v_product.p_stock_old = "";
		FluidVariables.v_product.p_buyqty = "";
		FluidVariables.v_product.p_name = "";
		FluidVariables.v_product.p_barcode = "";
		FluidVariables.v_product.p_mfg_number = "";
		FluidVariables.v_product.p_description = "";
		FluidVariables.v_product.p_details = "";
		FluidVariables.v_product.p_specs = "";
		FluidVariables.v_product.p_inthebox = "";
		FluidVariables.v_product.p_seo = "";
		FluidVariables.v_product.p_keywords = "";
		FluidVariables.v_product.p_c_filters = "";
		FluidVariables.v_product.p_c_linking = "";
		FluidVariables.v_product.p_m_filters = "";
		FluidVariables.v_product.p_length = "";
		FluidVariables.v_product.p_width = "";
		FluidVariables.v_product.p_height = "";
		FluidVariables.v_product.p_weight = "";
		FluidVariables.v_product.p_formula_status = "";
		FluidVariables.v_product.p_formula_operation = "";
		FluidVariables.v_product.p_formula_math = "";
		FluidVariables.v_product.p_formula_application = "";
		FluidVariables.v_product.p_formula_discount_date_end = "";
		FluidVariables.v_product.p_date_hide = "";
		FluidVariables.v_product.p_formula_discount_date_start = "";
		FluidVariables.v_product.p_formula_item_html = "";
		FluidVariables.v_product.p_formula_item_faux_html = "";
		FluidVariables.v_product.p_formula_items_data = "";
		FluidVariables.v_product.p_formula_items_faux_data = "";
		FluidVariables.v_product.p_formula_flip = "";
		FluidVariables.v_product.p_formula_message = "";
		FluidVariables.v_product.p_formula_message_display = "";

		// Clears product image object.
		for(var key in FluidVariables.v_product.p_images)
			delete FluidVariables.v_product.p_images[key];

		// Clears out the product image order object.
		for(var key in FluidVariables.v_product.p_imageorder)
			delete FluidVariables.v_product.p_imageorder[key];
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_debug(data) {
	<?php
	if($_SERVER['SERVER_NAME'] == "local.leosadmin.com") {
	?>
		if(data)
			console.log(data);
		else
			console.log(FluidVariables);
	<?php
	}
	?>
}

function js_debug_error(err) {
	js_debug(err);

	// Reset loading bars and cursors before displaying the message if needed.
	document.getElementById("progress-bar-loading").style.display = "none";
	$("#progress-bar-loading").animate({width:"0%"});
	document.getElementById("content-div").disabled = false;
	document.getElementById("loading-overlay").style.display = "none";
	document.body.style.cursor = "default";
	document.getElementById('modal-error-msg-div').innerHTML = err;
	//document.getElementById('modal-error-msg-div').innerHTML = err.name + "<br>" + err.message + "<br><br>" + err.stack;

	if($('#f_scan_code').is(":visible")) {
		document.getElementById('modal-error-msg-div').innerHTML = "Scanning error, please try again.";
		js_scan_init("f_scan_code");
	}
	else
		js_modal_show('#fluid-error-modal');
}

// Delete all files from the image temp folder.
function js_delete_image_temp() {
	// Clear out image working temp folder.
	try {
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_delete_image_temp"}));
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_category_prep() {
	var ul_filters = document.getElementsByName('filter-list-div-ul-name');
	var cat_filters = {};

	// Iterate through the filters and prep them for sending to the server.
	for(var x=0; x < ul_filters.length; x++) {
		cat_filters[x] = {};

		cat_filters[x].filter_name = ul_filters[x].getAttribute('title');
		cat_filters[x].filter_order = x;
		cat_filters[x].filter_id = ul_filters[x].getAttribute('filter-id');
		cat_filters[x].sub_filters = {};

		var tmp_table = document.getElementById('filters-cat-list-' + ul_filters[x].getAttribute('data-subid'));

		for(var i = 0; i < tmp_table.length; i++) {
			cat_filters[x].sub_filters[i] = {};
			cat_filters[x].sub_filters[i].p_id = tmp_table[i].getAttribute('data-id');
			<?php
			/*
			//cat_filters[x].sub_filters[i].p_catid = tmp_table[i].getAttribute('data-pcatid');
			//cat_filters[x].sub_filters[i].p_mfgid = tmp_table[i].getAttribute('data-pmfgid');
			//cat_filters[x].sub_filters[i].p_dataname = tmp_table[i].getAttribute('data-name');
			//cat_filters[x].sub_filters[i].p_enable = tmp_table[i].getAttribute('data-penable');
			//cat_filters[x].sub_filters[i].value = tmp_table[i].value
			*/
			?>
		}
	}

	return cat_filters;
}

function js_filters_prep() {
	var ul_filters = document.getElementsByName('filter-list-div-ul-name');
	var cat_filters = {};

	// Iterate through the filters and prep them for sending to the server.
	for(var x=0; x < ul_filters.length; x++) {
		cat_filters[x] = {};

		cat_filters[x].filter_name = ul_filters[x].getAttribute('title');
		cat_filters[x].filter_order = x;
		cat_filters[x].filter_id = ul_filters[x].getAttribute('filter-id');
		cat_filters[x].sub_filters = {};

		var tmp_table = document.getElementById('filters-cat-' + ul_filters[x].getAttribute('filter-id'));

		for(var i = 0, row; row = tmp_table.rows[i]; i++) {
			cat_filters[x].sub_filters[i] = {};
			cat_filters[x].sub_filters[i].sub_name = Base64.encode(row.cells[3].innerHTML);
			cat_filters[x].sub_filters[i].sub_id = row.cells[2].innerHTML;
		}
	}

	return cat_filters;
}

function js_filter_rename(id, value) {
	try {
		document.getElementsByName('filter-raw-name-' + id )[0].innerHTML = value;
		document.getElementsByName('filter-raw-name-' + id )[1].innerHTML = value;
		document.getElementsByName('dropdown-filter-rename-input-' + id)[0].value = value;
		document.getElementsByName('dropdown-filter-rename-input-' + id)[1].value = value;
		document.getElementById('filters-list-div-' + id).setAttribute('title', Base64.encode(value));

		js_filter_select_reload();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_category_rename(id, value) {
	try {
		document.getElementsByName('filter-raw-name-' + id )[0].innerHTML = value;
		document.getElementsByName('filter-raw-name-' + id )[1].innerHTML = value;
		document.getElementsByName('dropdown-filter-rename-input-' + id)[0].value = value;
		document.getElementsByName('dropdown-filter-rename-input-' + id)[1].value = value;
		document.getElementById('filters-list-div-' + id).setAttribute('title', Base64.encode(value));
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_filter_rename_blur(id) {
	try {
		if(document.getElementById('filter-span-open-' + id).style.display == 'none')
			var array_int = 0;
		else
			var array_int = 1;

		document.getElementsByName('dropdown-filter-rename-input-' + id)[array_int].style.display="inline";
		document.getElementsByName('dropdown-filter-rename-input-' + id)[array_int].focus();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_filter_select_reload() {
	try {
		var filters_data = Base64.encode(JSON.stringify(js_filters_prep()));

		var data_send_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_filter_select_reload&filterdata=" + filters_data}));

		js_fluid_ajax(data_send_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_filter_stack(id) {
	if(event.target.id != 'dropdown-filter' && event.target.id != 'dropdown-filter-rename-input') {
		if(document.getElementById('filter-span-open-' + id).style.display == 'none' ) {
			document.getElementById('filter-span-open-' + id).style.display = "block";
			document.getElementById('filter-span-closed-' + id).style.display = "none";
			document.getElementById('filter-list-div-' + id + '-data').style.display = "block";
			document.getElementById('filter-div-' + id).style.display = "none";
		}
		else {
			document.getElementById('filter-span-open-' + id).style.display = "none";
			document.getElementById('filter-span-closed-' + id).style.display = "block";
			document.getElementById('filter-list-div-' + id + '-data').style.display = "none";
			document.getElementById('filter-div-' + id).style.display = "block";
		}
	}
}

function js_fluid_load_items(f_page_num, mode, dmode) {
	try {
		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_load_items&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_ajax_hidden(data_obj_tmp, element, debug) {
	var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
	var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

	<?php
		//if($_SERVER['SERVER_NAME'] == "local.leoscamera.com") {
		?>
			//js_debug(data_obj.serverurl + "?" + data_obj.dataobj);
		<?php
		//}
	?>

	$.ajax({
		url: http + data_obj.serverurl,
		type: 'POST',
		data: data_obj.dataobj,

		error: function(jqXHR, textStatus, errorThrown){
			//js_debug_error(errorThrown);
		},
		success: function(f_data){
			if(debug == true) {
				setTimeout(function() {js_loading_stop();}, 1000);
				var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
				win.document.body.innerHTML = f_data;
			}


			setTimeout(function() {js_fluid_ajax_process(f_data, element, true);}, 500);
		},
		timeout: 60000<?php // sets timeout to 60 seconds ?>
	});
}

function js_fluid_ajax_no_timeout(data_obj_tmp, element, debug) {
	try {
		// Enable the loading screen. Prevents keyboard and mouse input and also greys the screen for loading and displays the loading cursor.
		js_loading_start();

		var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
		var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

		<?php
			if($_SERVER['SERVER_NAME'] == "local.leosadmin.com")
				echo 'js_debug(data_obj.serverurl + "?" + data_obj.dataobj);';
		?>

		$.ajax({
			url: http + data_obj.serverurl,
			type: 'POST',
			data: data_obj.dataobj,
<?php
/*
			headers: { "Connection": "close" },

			beforeSend: function(request) {
				request.setRequestHeader("Connection", "close");
			},
*/
?>
			error: function(jqXHR, textStatus, errorThrown){
				<?php // will fire when timeout is reached ?>
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				js_loading_stop();
				js_debug_error(errorThrown);
			},
			success: function(f_data){
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				if(debug == true) {
					setTimeout(function() {js_loading_stop();}, 1000);
					var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
					win.document.body.innerHTML = f_data;
				}


				setTimeout(function() {js_fluid_ajax_process(f_data, element);}, 500);
			},
			timeout: 0<?php // 0 = no timeout.?>
		});
	}
	catch (err) {
		js_debug_error(err);
	}
}

function js_fluid_ajax(data_obj_tmp, element, debug) {
	try {
		// Enable the loading screen. Prevents keyboard and mouse input and also greys the screen for loading and displays the loading cursor.
		js_loading_start();

		var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
		var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

		<?php
			if($_SERVER['SERVER_NAME'] == "local.leosadmin.com")
				echo 'js_debug(data_obj.serverurl + "?" + data_obj.dataobj);';
		?>

		$.ajax({
			url: http + data_obj.serverurl,
			type: 'POST',
			data: data_obj.dataobj,
<?php
/*
			headers: { "Connection": "close" },

			beforeSend: function(request) {
				request.setRequestHeader("Connection", "close");
			},
*/
?>
			error: function(jqXHR, textStatus, errorThrown){
				<?php // will fire when timeout is reached ?>
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				js_loading_stop();
				js_debug_error(errorThrown);
			},
			success: function(f_data){
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				if(debug == true) {
					setTimeout(function() {js_loading_stop();}, 1000);
					var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
					win.document.body.innerHTML = f_data;
				}


				setTimeout(function() {js_fluid_ajax_process(f_data, element);}, 500);
			},
			timeout: 60000<?php // sets timeout to 60 seconds ?>
		});
	}
	catch (err) {
		js_debug_error(err);
	}
}

function js_fluid_ajax_fdom(data_obj_tmp, element, debug) {
	try {
		// Enable the loading screen. Prevents keyboard and mouse input and also greys the screen for loading and displays the loading cursor.
		js_loading_start();

		var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
		var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

		js_debug(data_obj.serverurl + "?" + data_obj.dataobj);

		$.ajax({
			url: http + data_obj.serverurl,
			type: 'POST',
			data: data_obj.dataobj,
<?php
/*
			headers: { "Connection": "close" },

			beforeSend: function(request) {
				request.setRequestHeader("Connection", "close");
			},
*/
?>
			error: function(jqXHR, textStatus, errorThrown){
				<?php // will fire when timeout is reached ?>
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				js_loading_stop();
				js_debug_error(errorThrown);
			},
			success: function(f_data){
				$("#progress-bar-loading").stop();

				$("#progress-bar-loading").animate({
					width: "100%"
				}, 200);

				if(debug == true) {
					setTimeout(function() {js_loading_stop();}, 1000);
					var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
					win.document.body.innerHTML = f_data;
				}


				setTimeout(function() {js_fluid_ajax_process(f_data, element);}, 500);
			}
		});

		<?php
		/*
		var data_obj = JSON.parse(Base64.decode(data_obj_tmp));

		var http = "http://";
		var fluidXH;

		if (window.ActiveXObject) {
			fluidXH = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest) {
			fluidXH = new XMLHttpRequest();
		}

		$("#progress-bar-loading").animate({
			width: "75%"
		}, 7000);

		js_debug(http + data_obj.serverurl + "?" + data_obj.dataobj);

		fluidXH = window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("Microsoft.XMLHTTP");
		fluidXH.open('POST',http + data_obj.serverurl, true);
		fluidXH.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		fluidXH.onreadystatechange = function(){
			if(fluidXH.readyState == 4) {
				if(fluidXH.status == 200) {
					$("#progress-bar-loading").stop();

					$("#progress-bar-loading").animate({
						width: "100%"
					}, 200);

					if(debug == true) {
						setTimeout(function() {js_loading_stop();}, 1000);
						var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
						win.document.body.innerHTML = fluidXH.responseText;
					}
					else
						setTimeout(function() {js_fluid_ajax_process(fluidXH.responseText, element);}, 500);
				}
				else {
					js_debug_error(fluidXH.responseURL + " : " + fluidXH.statusText);
				}
			}
		}

		fluidXH.onerror = function(e) {
			js_debug_error(fluidXH.responseURL + " : " + fluidXH.statusText);
		};

		fluidXH.send(data_obj.dataobj);
		*/
		?>
	}
	catch (err) {
		js_debug_error(err);
	}
}

function js_fluid_ajax_process(data, element) {
	try {
		var arrayData = JSON.parse(data);
		js_loading_stop();

		if(arrayData['error'] == 1 && typeof arrayData['error'] != 'undefined') {
			js_debug_error(Base64.decode(arrayData['error_message']));
		}
		else {
			if(arrayData['breadcrumbs'])
				document.getElementById("breadcrumbs").innerHTML = Base64.decode(arrayData['breadcrumbs']);

			if(arrayData['navbarright'])
				document.getElementById("navbar-menu-right").innerHTML = Base64.decode(arrayData['navbarright']);

			if(arrayData['navbarsearch'])
				document.getElementById("navbar-menu-search").innerHTML = Base64.decode(arrayData['navbarsearch']);

			if(arrayData['innerhtml'])
				document.getElementById(element).innerHTML = Base64.decode(arrayData['innerhtml']);

			if(arrayData['js_execute'] == 1) {
				<?php
					if($_SERVER['SERVER_NAME'] == "local.leosadmin.com")
						echo 'console.log(arrayData["js_callbackFunction"]);';
				?>

				if(arrayData['js_data'] == 1) {
					if(arrayData['js_div'] == 1)
						window[arrayData['js_callbackFunction']](arrayData['js_callbackData'], arrayData['js_divData']);
					else
						window[arrayData['js_callbackFunction']](Base64.decode(arrayData['js_callbackData']));
				}
				else
					window[arrayData['js_callbackFunction']]();
			}

			// Execute commands and send data to functions.
			if(arrayData['js_execute_array'] == 1) {
				var data_array = JSON.parse(Base64.decode(arrayData['js_execute_functions']));

				try {
					for (i = 0; i < data_array.length; i++) {
						<?php
							if($_SERVER['SERVER_NAME'] == "local.leosadmin.com")
								echo 'console.log(data_array[i]["function"]);';
						?>

						if(typeof data_array[i]['data'] != 'undefined')
							window[data_array[i]['function']](JSON.parse(Base64.decode(data_array[i]['data'])));
						else
							window[data_array[i]['function']](arrayData);

						<?php //window[data_array[i]['function']](JSON.parse(Base64.decode(data_array[i]['data']))); ?>
					}
				}
				catch(err) {
					js_debug_error(err);
				}
			}
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_html_style_hide(data) {
	try {
		document.getElementById(data['div_id_hide']).style.display = "none";
		js_update_select_pickers(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_html_style_show(data) {
	try {
		document.getElementById(data['div_id_hide']).style.display = "inline-block";
		js_update_select_pickers(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Insert some innerHTML into a element.
function js_html_insert_element(data) {
	try {
		document.getElementById(Base64.decode(data['parent'])).innerHTML = Base64.decode(data['innerHTML']);
		js_update_select_pickers(); // Update any select pickers in case any are in the new innerHTML data.
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Removes a html element from the page.
function js_html_remove_element(data) {
	try {
		var element = document.getElementById(data);

		if(typeof element != "undefined" && element != null)
			element.parentNode.removeChild(element);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_image_dropzone_destroy() {
	imageDropzone.destroy();
}

function js_load_datetime_picker(div_id) {
	$('#' + div_id).datetimepicker({
		showTodayButton: true,
		showClear: true,
		calendarWeeks: true,
		format: "YYYY-MM-DD HH:mm:ss"
	});
}

function js_load_image_dropzone(data, mode) {
	// Check to see if we have any old images to process and prepare the data.
	if(typeof data['image_data'] != 'undefined') {
		var image_tmp = JSON.parse(Base64.decode(data['image_data']));

		if(image_tmp['imgzone'] != null) {
			var image_zone = JSON.parse(Base64.decode(image_tmp['imgzone']));
		}
	}

	Dropzone = require("enyo-dropzone");
	Dropzone.autoDiscover = false;
	// Get the template HTML and remove it from the doument.
	var previewNode = document.querySelector("#template");
	previewNode.id = "";
	var previewTemplate = previewNode.parentNode.innerHTML;
	previewNode.parentNode.removeChild(previewNode);

	imageDropzone = new Dropzone(document.body, { // Make the whole body a dropzone.
		url: "<?php echo FLUID_LOADER; ?>?load=true&function=php_file_processing&f_session_id=" + data['f_session_id'],
		thumbnailWidth: 80,
		thumbnailHeight: 80,
		parallelUploads: 1,
		previewTemplate: previewTemplate,
		maxFilesize: 30,
		maxFiles : 30,
		autoQueue: false, // Make sure the files are not queued until manually added.
		previewsContainer: "#previews", // Define the container to display the previews.
		clickable: ".fileinput-button", // Define the element that should be used as click trigger to select files.
		init: function() {
			// Process old images and load them into the zone if we are editing a product.
			if(typeof data['image_data'] != 'undefined') {
				myDropzone = this;

				// Processing the old images and adding them into the drop zone.
				for(var key in image_zone) {
					var mockFile = { name: image_zone[key]['oldname'], size: image_zone[key]['size'], rand: image_zone[key]['rand'], xhr: image_zone[key]['xhr'] };

					// Store the full data into the image object of the FluidVariable object.
					if(mode == "product")
						FluidVariables.v_product.p_images[key] = JSON.parse(Base64.decode(image_zone[key]['xhr']['response']));
					else if(mode == "category")
						FluidVariables.v_category.c_images[key] = JSON.parse(Base64.decode(image_zone[key]['xhr']['response']));
					//else if(mode == "multi")
						//FluidVariables.v_multi_editor.items[data['p_id']]['data_obj'].p_images_tmp[key] = JSON.parse(Base64.decode(image_zone[key]['xhr']['response']));

					myDropzone.emit("addedfile", mockFile);
					myDropzone.createThumbnailFromUrl(mockFile, "<?php echo WWW_IMAGES_TEMP; ?>" + Base64.decode(data['f_session_id']) + "/" + image_zone[key]['name']);
					myDropzone.emit("success", mockFile, image_zone[key]['xhr']['response']);
					myDropzone.emit("complete", mockFile);
					myDropzone.options.maxFiles = myDropzone.options.maxFiles - 1; // Adjust the max files accordingly.
					myDropzone.files.push(mockFile); // Add the file to the dropzone file list.

					// Attach a sortable to each file as required with code to execute when the file gets moved around on the order list.
					$("#previews").sortable({
						cursor: 'move',
						opacity: 0.5,
						stop: function () {
							var newQueue = [];

							$('#previews .file-row [data-dz-hiddendata]').each(function (count, el) {
								var data_array = JSON.parse(el.innerHTML);

								myDropzone.files.forEach(function(mockFile) {
								   if (mockFile.name === data_array['data']['name'] && mockFile.size === data_array['data']['size']) {
										newQueue.push(mockFile);
								   }
								});
							});

							myDropzone.files = newQueue;
						}
					});
				}
			}
		}
	});

	imageDropzone.on("addedfile", function(file) {
		$("#previews").sortable({
			cursor: 'move',
			opacity: 0.5,
			stop: function () {
				var newQueue = [];

				$('#previews .file-row [data-dz-hiddendata]').each(function (count, el) {
					var data_array = JSON.parse(el.innerHTML);

					imageDropzone.files.forEach(function(file) {
					   if (file.name === data_array['data']['name'] && file.size === data_array['data']['size']) {
							newQueue.push(file);
					   }
					});
				});

				imageDropzone.files = newQueue;
			}
		});

		// Check for duplicate files and prevent queuing to the list.
		if(this.files.length) {
			var _i, _len;
			// -1 to exclude current file.
			for(_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
				if(this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
					this.removeFile(file);
				}
			}
		}

		// Hookup the start button.
		file.previewElement.querySelector(".start").onclick = function() { imageDropzone.enqueueFile(file); };
	});

	// Update the total progress bar.
	imageDropzone.on("totaluploadprogress", function(progress) {
		document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
	});

	imageDropzone.on("sending", function(file) {
		// Show the total progress bar when upload starts.
		document.querySelector("#total-progress").style.opacity = "1";

		// Disable the start button.
		file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
	});

	// Hide the total progress bar when nothing's uploading anymore.
	imageDropzone.on("queuecomplete", function(progress) {
		document.querySelector("#total-progress").style.opacity = "0";
	});

	// Upload a success, store the image into the FluidVariable object.
	imageDropzone.on("success", function(file,response) {
		data_return = JSON.parse(Base64.decode(response));

		if(mode == "product")
			FluidVariables.v_product.p_images[data_return.file['rand']] = data_return;
		else if(mode == "category")
			FluidVariables.v_category.c_images[data_return.file['rand']] = data_return;
		else if(mode == "multi")
			FluidVariables.v_multi_editor.items[data['p_id']]['data_obj'].p_images_tmp[data_return.file['rand']] = data_return;
	});

	imageDropzone.on("removedfile", function(file) {
		try {
			// If there was a xhr ajax response, means the file was uploaded to the server. Lets delete it off the server now.
			if(typeof file.xhr != 'undefined') {
				if(file.xhr.response) {
					var data_return = JSON.parse(Base64.decode(file.xhr.response));
					//var file = Base64.encode(data_return.file['fullpath']);
					var file = Base64.encode(data_return.file['image']);

					// Remove the element from the array.
					if(mode == "product")
						delete FluidVariables.v_product.p_images[data_return.file['rand']];
					else if(mode == "category")
						delete FluidVariables.v_category.c_images[data_return.file['rand']];
					else if(mode == "multi" && FluidVariables.v_multi_editor.imageDropZoneDelete == null)
						delete FluidVariables.v_multi_editor.items[data['p_id']]['data_obj'].p_images_tmp[data_return.file['rand']];

					// Do not remove images off the server if in multi item editing mode. That gets processed afterwards.
					if(mode != "multi") {
						var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_delete_file&data=" + file}));
						js_fluid_ajax(data_obj);
					}
				}
			}
		} catch(err) {
			// Caught the reference error. No server response, file was never uploaded, just queued.
			js_debug_error(err);
		}

	});

	// Setup the buttons for all transfers. The "add files" button does not need to be setup because the config `clickable` has already been specified.
	document.querySelector("#actions .start").onclick = function() {
		imageDropzone.enqueueFiles(imageDropzone.getFilesWithStatus(Dropzone.ADDED));
	};
	document.querySelector("#actions .cancel").onclick = function() {
		imageDropzone.removeAllFiles(true);
	};

	Dropzone.discover();
}

// Used to load php code from fluid.loader.php.
function js_load_php_fluid_loader(url, data) {
	var data_obj = Base64.encode(JSON.stringify({serverurl: url, dataobj: data + "&data=" + Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection))}));
	js_fluid_ajax(data_obj);
}

// Loading screen.
function js_loading_start() {
	document.getElementById("progress-bar-loading").style.display = "block";
	document.getElementById("loading-overlay").style.display = "block";
	document.getElementById("content-div").disabled = true;
	document.body.style.cursor = "wait";

	//$(":input").prop("disabled","true");
}

// Stop loading screen.
function js_loading_stop() {
	document.getElementById("progress-bar-loading").style.display = "none";
	$("#progress-bar-loading").animate({width:"0%"});
	document.getElementById("content-div").disabled = false;
	document.getElementById("loading-overlay").style.display = "none";
	document.body.style.cursor = "default";

	//$(":input").prop("disabled",false);
}

// Load a modal with html data.
function js_modal(data) {
	document.getElementById('fluid-modal').innerHTML = Base64.decode(data['modal_html']);
	js_update_select_pickers();
}

// The preparation for the category editor modal.
function js_modal_category_create_and_edit(data) {
	try {
		// Clears, empties and refresh the FluidVariables.
		js_clear_fluid_category();

		document.getElementById('fluid-modal').innerHTML = Base64.decode(data['modal_html']);
		document.getElementById('category-create-information-div').innerHTML = Base64.decode(data['info_html']);

		// Only load the filters if in child mode.
		if(typeof data['parent'] != "undefined" && Base64.decode(data['parent']) == false) {
			document.getElementById('category-create-filters-div').innerHTML = Base64.decode(data['filters_html']);

			if(data['mode'] != "manufacturers")
				document.getElementById('category-create-formula-div').innerHTML = Base64.decode(data['formula_html']);
		}

		document.getElementById('category-create-image-div').innerHTML = Base64.decode(data['image_html']);

		// Check to see if we have any old images to process and prepare the data.
		if(typeof data['image_data'] != 'undefined') {
			var image_tmp = JSON.parse(Base64.decode(data['image_data']));
			var image_zone = JSON.parse(Base64.decode(image_tmp['imgzone']));
		}

		FluidVariables.v_category.f_id = data['f_session_id'];

		js_load_image_dropzone(data, "category");

		js_update_select_pickers();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_modal_confirm(modal_parent, message, footer) {
	js_modal_hide(modal_parent);

	document.getElementById("modal-override-div").innerHTML = message;

	document.getElementById("modal-confirm-footer").innerHTML = footer;

	js_modal_show("#fluid-confirm-modal");
}

function js_modal_hide(modal_id) {
	$(modal_id).modal('hide');
}

// The preparation for the multi item editor modal.
function js_modal_multi_item_editor(data) {
	try {
		var data_obj = JSON.parse(Base64.decode(data['data_array']));
		var item_data_obj = JSON.parse(Base64.decode(data['item_data_array']));

		for(var key in item_data_obj)
			item_data_obj[key] = JSON.parse(item_data_obj[key]);

		// Wipe clear the multi items if neccessary before entering multi editing mode.
		for(var key in FluidVariables.v_multi_editor.items)
			delete FluidVariables.v_multi_editor.items[key];

		// Set some multi editing data.
		FluidVariables.v_multi_editor.footer_back_html = data['footer_back_html'];
		FluidVariables.v_multi_editor.footer_save_html = data['footer_save_html'];
		FluidVariables.v_multi_editor.item_list_html = data['item_list_html'];
		FluidVariables.v_multi_editor.tabs_html = data['tabs_html'];
		FluidVariables.v_multi_editor.item_tabs_html = data['item_tabs_html'];
		FluidVariables.v_multi_editor.image_html = data['image_html'];
		FluidVariables.v_multi_editor.editor_html = data['editor_html'];
		FluidVariables.v_multi_editor.link_html = data['link_html'];
		FluidVariables.v_multi_editor.component_html = data['component_html'];
		FluidVariables.v_multi_editor.formula_html = data['formula_html'];

		// Set some multi editing item data.
		for(var key in data_obj) {
			FluidVariables.v_multi_editor.items[key] = {};
			FluidVariables.v_multi_editor.items[key]['p_id'] = data_obj[key]['p_id'];
			FluidVariables.v_multi_editor.items[key]['p_fullname'] = data_obj[key]['p_fullname'];
			FluidVariables.v_multi_editor.items[key]['image_data'] = data_obj[key]['image_array'];
			FluidVariables.v_multi_editor.items[key]['f_session_id'] = data_obj[key]['f_session_id'];
			FluidVariables.v_multi_editor.items[key]['update'] = false;

			// Create the data object and image object for the item.
			js_clear_fluid_product(); // Need to clear the object we are copying. In this case, a blank item object.
			FluidVariables.v_multi_editor.items[key]['data_obj'] = (JSON.parse(JSON.stringify(FluidVariables.v_product)));

			// Flag the category and manufacturer id that each item belongs to. This is used to compare in the fluid.loader.php if the item being updated has changed manufacturer or category, and if so, it aids the system to reset the sort orders for the items.
			FluidVariables.v_multi_editor.items[key]['prev_c_id'] = item_data_obj[key].p_catid;
			FluidVariables.v_multi_editor.items[key]['prev_m_id'] = item_data_obj[key].p_mfgid;

			// Set the items data into the object.
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_id = item_data_obj[key].p_id;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_status = item_data_obj[key].p_enable;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_zero_status = item_data_obj[key].p_zero_status;


			if(item_data_obj[key].p_trending == null || item_data_obj[key].p_trending == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_trending = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_trending = item_data_obj[key].p_trending;

			if(item_data_obj[key].p_instore == null || item_data_obj[key].p_instore == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_instore = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_instore = item_data_obj[key].p_instore;

			if(item_data_obj[key].p_arrivaltype == null || item_data_obj[key].p_arrivaltype == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_arrivaltype = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_arrivaltype = item_data_obj[key].p_arrivaltype;

			if(item_data_obj[key].p_freeship == null || item_data_obj[key].p_freeship == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_freeship = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_freeship = item_data_obj[key].p_freeship;

			if(item_data_obj[key].p_preorder == null || item_data_obj[key].p_preorder == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_preorder = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_preorder = item_data_obj[key].p_preorder;

			if(item_data_obj[key].p_rental == null || item_data_obj[key].p_rental == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_rental = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_rental = item_data_obj[key].p_rental;

			if(item_data_obj[key].p_special_order == null || item_data_obj[key].p_special_order == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_special_order = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_special_order = item_data_obj[key].p_special_order;

			if(item_data_obj[key].p_namenum == null || item_data_obj[key].p_namenum == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_namenum = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_namenum = item_data_obj[key].p_namenum;

			if(item_data_obj[key].p_showalways == null || item_data_obj[key].p_showalways == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_showalways = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_showalways = item_data_obj[key].p_showalways;

			if(item_data_obj[key].p_rebate_claim == null || item_data_obj[key].p_rebate_claim == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_rebate_claim = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_rebate_claim = item_data_obj[key].p_rebate_claim;

			if(item_data_obj[key].p_component == null || item_data_obj[key].p_component == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_component = Base64.encode("0");
			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_component = item_data_obj[key].p_component;

			FluidVariables.v_multi_editor.items[key]['data_obj'].p_component_data = item_data_obj[key].p_component_data;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_component_html = item_data_obj[key].p_component_html;

			if(item_data_obj[key].p_stock_end == null || item_data_obj[key].p_stock_end == "")
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_stock_end = Base64.encode("0");

			else
				FluidVariables.v_multi_editor.items[key]['data_obj'].p_stock_end = item_data_obj[key].p_stock_end;

			FluidVariables.v_multi_editor.items[key]['data_obj'].p_manufacturer = item_data_obj[key].p_mfgid;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_category = item_data_obj[key].p_catid;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_price = item_data_obj[key].p_price;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_cost = item_data_obj[key].p_cost;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_cost_real = item_data_obj[key].p_cost_real;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_cost_real_old = item_data_obj[key].p_cost_real;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_price_discount = item_data_obj[key].p_price_discount;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_discount_date_end = item_data_obj[key].p_discount_date_end;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_discount_date_start = item_data_obj[key].p_discount_date_start;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_newarrivalenddate = item_data_obj[key].p_newarrivalenddate;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_stock = item_data_obj[key].p_stock;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_stock_old = item_data_obj[key].p_stock;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_buyqty = item_data_obj[key].p_buyqty;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_name = item_data_obj[key].p_name;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_barcode = item_data_obj[key].p_mfgcode;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_mfg_number = item_data_obj[key].p_mfg_number;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_description = item_data_obj[key].p_desc;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_details = item_data_obj[key].p_details;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_specs = item_data_obj[key].p_specs;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_inthebox = item_data_obj[key].p_inthebox;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_seo = item_data_obj[key].p_seo;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_keywords = item_data_obj[key].p_keywords;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_c_filters = item_data_obj[key].p_c_filters;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_c_linking = item_data_obj[key].p_c_linking;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_m_filters = item_data_obj[key].p_m_filters;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_length = item_data_obj[key].p_length;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_width = item_data_obj[key].p_width;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_height = item_data_obj[key].p_height;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_weight = item_data_obj[key].p_weight;

			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_status = item_data_obj[key].p_formula_status;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_operation = item_data_obj[key].p_formula_operation;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_math = item_data_obj[key].p_formula_math;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_application = item_data_obj[key].p_formula_application;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_discount_date_end = item_data_obj[key].p_formula_discount_date_end;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_date_hide = item_data_obj[key].p_date_hide;

			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_discount_date_start = item_data_obj[key].p_formula_discount_date_start;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_item_html = item_data_obj[key].p_formula_item_html;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_item_faux_html = item_data_obj[key].p_formula_item_faux_html;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_items_data = item_data_obj[key].p_formula_items_data;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_items_faux_data = item_data_obj[key].p_formula_items_faux_data;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_flip = item_data_obj[key].p_formula_flip;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_message_display = item_data_obj[key].p_formula_message_display;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_formula_message = item_data_obj[key].p_formula_message;
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_category_items_data = item_data_obj[key].p_category_items_data;
			
			// Create a duplicate p_images as a tmp for image processing.
			FluidVariables.v_multi_editor.items[key]['data_obj'].p_images_tmp = {};

			// Check to see if we have any old images to process and prepare the data.
			if(typeof data_obj[key]['image_array'] != 'undefined') {
				var image_tmp = JSON.parse(Base64.decode(data_obj[key]['image_array']));

				if(image_tmp['imgzone'] != null) {
					var image_zone = JSON.parse(Base64.decode(image_tmp['imgzone']));

					for(var key_img in image_zone) {
						var obj_temp = JSON.parse(Base64.decode(image_zone[key_img]['xhr']['response']));

						FluidVariables.v_multi_editor.items[key]['data_obj'].p_images[obj_temp.file.rand] = JSON.parse(Base64.decode(image_zone[key_img]['xhr']['response']));
						
						// Store a duplicate of the image data into the obj.
						FluidVariables.v_multi_editor.items[key]['data_obj'].p_images_tmp[obj_temp.file.rand] = JSON.parse(Base64.decode(image_zone[key_img]['xhr']['response']));
					}
				}
			}
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_banners_refresh(dataObj) {
	try {
		// Clears, empties and refresh the FluidVariables.
		js_clear_fluid_product();

		js_fluid_ajax(dataObj, "content-div");
	}
	catch(err) {
		js_debug_error(err);
	}
}

// The preparation for the banners creator and editor modal.
function js_modal_banners_create_and_edit(data) {
	try {
		// Clears, empties and refresh the FluidVariables.
		js_clear_fluid_product();

		document.getElementById('fluid-modal').innerHTML = Base64.decode(data['modal_html']);
		document.getElementById('product-create-information-div').innerHTML = Base64.decode(data['info_html']);
		document.getElementById('product-create-image-div').innerHTML = Base64.decode(data['image_html']);

		$('#product-status').selectpicker('refresh');
		<?php
		if($detect->isMobile() && !$detect->isTablet())
			echo "$('#product-status').selectpicker('mobile');";
		else
			echo "$('#product-status').selectpicker();";
		?>
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_banners_uploader() {
	<?php
		// --> Note to self, when in custom.css, input[type="file"] is set to display none, it breaks this file uploader.
	?>

	'use strict';

	// Initialize the jQuery File Upload widget:
	$('#fileuploader').fileupload({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: 'fluid.fileuploader.php'
	});

	// Enable iframe cross-domain access via redirect option:
	$('#fileuploader').fileupload(
		'option',
		'redirect',
		window.location.href.replace(
			/\/[^\/]*$/,
			'/cors/result.html?%s'
		)
	);

	// Load existing files:
	$('#fileuploader').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileuploader').fileupload('option', 'url'),
		dataType: 'json',
		context: $('#fileuploader')[0]
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done')
			.call(this, $.Event('done'), {result: result});
	});
}

// The preparation for the product creator and editor modal.
function js_modal_product_create_and_edit(data) {
	try {
		// Clears, empties and refresh the FluidVariables.
		js_clear_fluid_product();

		document.getElementById('fluid-modal').innerHTML = Base64.decode(data['modal_html']);
		document.getElementById('product-create-information-div').innerHTML = Base64.decode(data['info_html']);
		document.getElementById('product-create-image-div').innerHTML = Base64.decode(data['image_html']);

		js_load_datetime_picker("datetimepicker-arrival");
		js_load_datetime_picker("datetimepicker");
		js_load_datetime_picker("datetimepicker-start");
		js_load_image_dropzone(data, "product");

		js_update_select_pickers();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_modal_show(modal_id) {
	$(modal_id).modal('show');

	<?php
	// No need to enable dragging in mobile touch mode.
	if(!$detect->isMobile()) {
	?>
		$('.modal-dialog').draggable({
			handle: ".fluid-panel-drag"
		});
	<?php
	}
	?>
}

function js_modal_toggle(modal_id) {
	$(modal_id).modal('toggle');
}

// Load the html data from FluidVariables.v_multi_editor into the div for multi item editing.
function js_multi_item_editor(p_id) {
	try {
		// Prevent item editor loading if the item remove button was clicked on instead.
		if(event.target.id != 'multi-item-list-remove') {

			document.getElementById('multi-item-editor-tabs-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.tabs_html);
			document.getElementById('multi-item-editor-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.item_tabs_html);
			document.getElementById('product-create-information-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.editor_html);
			document.getElementById('product-create-image-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.image_html);
			document.getElementById('product-link-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.link_html);

			document.getElementById('product-component-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.component_html);
			document.getElementById('component-list-select').innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_component_html);

			document.getElementById('product-math-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.formula_html);

			// Swap out the footer with a back button.
			document.getElementById('multi-item-modal-footer').innerHTML = Base64.decode(FluidVariables.v_multi_editor.footer_back_html);
			// Update the onClick command so when the user clicks back, data is saved.
			document.getElementById('multi-item-button-back').onclick = function() { js_multi_item_update(p_id); }

			document.getElementById('product-status').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_status);
			document.getElementById('product-zero-status').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_zero_status);
			document.getElementById('product-trending').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_trending);
			document.getElementById('product-instore').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_instore);
			document.getElementById('product-arrivaltype').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_arrivaltype);
			document.getElementById('product-freeship').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_freeship);
			document.getElementById('product-preorder').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_preorder);
			document.getElementById('product-rental').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_rental);
			document.getElementById('product-special-order').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_special_order);
			document.getElementById('product-namenum').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_namenum);
			document.getElementById('product-alwaysshow').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_showalways);
			document.getElementById('product-rebate-claim').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_rebate_claim);
			document.getElementById('product-component').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_component);
			document.getElementById('product-stock-end').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock_end);

			document.getElementById("product-id").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_id);

			// --> Generate the category item linking.
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_category_items_data != null && FluidVariables.v_multi_editor.items[p_id].data_obj.p_category_items_data != "")
				js_product_reload_category(JSON.parse(Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_category_items_data)));

			// Set and trigger the onchange events on the manufacturers to set the right option filters.
			var m_sel = document.getElementById("product-manufacturer");
			var m_opts = m_sel.options;
			var m_name = null;
			for(var m_opt, m = 0; m_opt = m_opts[m]; m++) {
				if(m_opt.value == Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_manufacturer)) {
					m_sel.selectedIndex = m;
					m_name = m_sel[m].text;
					break;
				}
			}

			// Set and trigger the onchange events on the categories to set the right option filters.
			var c_sel = document.getElementById("product-category");
			var c_opts = c_sel.options;
			for(var c_opt, c = 0; c_opt = c_opts[c]; c++) {
				if(c_opt.value == Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_category)) {
					c_sel.selectedIndex = c;
					break;
				}
			}

			<?php // --> Process the product category linking. ?>
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_linking.length > 0) {
				var c_linking = document.getElementById('product-category-linking');
				var p_c_linking = JSON.parse(Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_linking));
				var c_found_array = [];
				var c = 0;

				for(var i = 0; i < c_linking.length; i++) {
					for(var key in p_c_linking) {
						if(key == c_linking.options[i].value) {
							c_found_array[c] = c_linking.options[i].value;
							c++;
						}
					}
				}

				$(c_linking).selectpicker('val', c_found_array);
			}

			<?php // --> Have to trigger both onChanges after setting the category and manufacturer first. ?>
			m_sel.onchange();
			c_sel.onchange();

			document.getElementById("product-price").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_price);
			document.getElementById("product-cost-average").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost);
			document.getElementById("product-cost").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost_real);
			document.getElementById("product-cost-old").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost_real_old);
			document.getElementById("product-price-discount").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_price_discount);
			document.getElementById("product-discount-price-end-date").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_discount_date_end);
			document.getElementById("product-discount-price-start-date").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_discount_date_start);
			document.getElementById("product-stock").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock);
			document.getElementById("product-stock-old").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock_old);
			document.getElementById("product-buyqty").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_buyqty);
			document.getElementById("product-name").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_name);
			document.getElementById("product-barcode").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_barcode);
			document.getElementById("product-mfg-number").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_mfg_number);
			document.getElementById("product-description").innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_description);
			document.getElementById("product-details").innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_details);
			document.getElementById("product-specifications").innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_specs);
			document.getElementById("product-inthebox").innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_inthebox);
			document.getElementById("product-seo-textarea").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_seo);
			document.getElementById("product-keywords-textarea").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_keywords);
			document.getElementById("product-arrival-end-date").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_newarrivalenddate);
			document.getElementById("product-length").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_length);
			document.getElementById("product-width").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_width);
			document.getElementById("product-height").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_height);
			document.getElementById("product-weight").value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_weight);

			document.getElementById('formula-status').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_status);
			document.getElementById('formula-message-display').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_message_display);
			document.getElementById('formula-flip').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_flip);
			document.getElementById('formula-message').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_message);

			//document.getElementById('formula-operation').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_operation);
			var fo_sel = document.getElementById("formula-operation");
			var fo_opts = fo_sel.options;
			for(var fo_opt, fo = 0; fo_opt = fo_opts[fo]; fo++) {
				if(fo_opt.value == Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_operation)) {
					fo_sel.selectedIndex = fo;
					break;
				}
			}

			document.getElementById('formula-math').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_math);

			//document.getElementById('formula-application').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_application);
			var fa_sel = document.getElementById("formula-application");
			var fa_opts = fa_sel.options;
			for(var fa_opt, fa = 0; fa_opt = fa_opts[fa]; fa++) {
				if(fa_opt.value == Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_application)) {
					fa_sel.selectedIndex = fa;
					break;
				}
			}

			document.getElementById('formula-discount-price-start-date').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_discount_date_start);
			document.getElementById('formula-discount-price-end-date').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_discount_date_end);
			document.getElementById('product-date-hide').value = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_date_hide);

			$('#formula-item-list').selectpicker('destroy');
			document.getElementById('f-formula-item-list-div').innerHTML = '';
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_html != '') {
				document.getElementById('f-formula-item-list-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_html);
			}
			else {
				<?php
					$f_item_formula_selector_default = base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK);
				?>
				document.getElementById('f-formula-item-list-div').innerHTML = Base64.decode('<?php echo $f_item_formula_selector_default; ?>');
			}

			$('#formula-item-list-faux').selectpicker('destroy');
			document.getElementById('f-formula-item-list-div-faux').innerHTML = '';
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_faux_html != '') {
				document.getElementById('f-formula-item-list-div-faux').innerHTML = Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_faux_html);
			}
			else {
				<?php
					$f_item_formula_selector_default_faux = base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK_FAUX);
				?>
				document.getElementById('f-formula-item-list-div-faux').innerHTML = Base64.decode('<?php echo $f_item_formula_selector_default_faux; ?>');
			}

			var var_f_header_html = m_name + " " + Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_name) + "<div style='display: inline-block; padding-left: 10px; font-style: oblique; font-weight: 600; font-size: 10px;'>upc: " + Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_barcode) + "</div>" + "<div style='display: inline-block; padding-left: 10px; font-style: oblique; font-weight: 600; font-size: 10px;'>code: " + Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_mfg_number) + "</div>";

			document.getElementById('f-header-multi-item-div').innerHTML = var_f_header_html;

			<?php // --> Process the category filters. ?>
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_filters.length > 0) {
				var c_filters = document.getElementById('product-category-filters');
				var p_c_filters = JSON.parse(Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_filters));
				var c_found_array = [];
				var c = 0;

				for(var i = 0; i < c_filters.length; i++) {
					for(var key in p_c_filters) {
						if(key == JSON.parse(Base64.decode(c_filters.options[i].value)).sub_id) {
							c_found_array[c] = c_filters.options[i].value;
							c++;
						}
					}
				}

				$(c_filters).selectpicker('val', c_found_array);
			}

			// Process the manufacturer filters.
			if(FluidVariables.v_multi_editor.items[p_id].data_obj.p_m_filters.length > 0) {
				var m_filters = document.getElementById('product-manufacturer-filters');
				var p_m_filters = JSON.parse(Base64.decode(FluidVariables.v_multi_editor.items[p_id].data_obj.p_m_filters));
				var m_found_array = [];
				var m = 0;

				for(var i = 0; i < m_filters.length; i++) {
					for(var key in p_m_filters) {
						if(key == JSON.parse(Base64.decode(m_filters.options[i].value)).sub_id) {
							m_found_array[m] = m_filters.options[i].value;
							m++;
						}
					}
				}

				$(m_filters).selectpicker('val', m_found_array);
			}

			// Load the date time pickers.
			js_load_datetime_picker("datetimepicker-arrival");
			js_load_datetime_picker("datetimepicker");
			js_load_datetime_picker("datetimepicker-start");

			js_load_datetime_picker("datetimepicker-formula-start");
			js_load_datetime_picker("datetimepicker-formula-end");
			js_load_datetime_picker("datetimepicker-date-hide");

			js_update_select_pickers();

			// Load the image uploader.
			js_load_image_dropzone(FluidVariables.v_multi_editor.items[p_id], "multi");
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Builds the innerHTML and item listing in the multi item listing save modal confirm window.
function js_multi_item_list_modal_confirm() {
	try {
		var item_list_html = "";
		for(var key in FluidVariables.v_multi_editor.items) {
			// Only show items which are being updated with any changes in the list.
			if(FluidVariables.v_multi_editor.items[key]['update'] == true)
				item_list_html = item_list_html + Base64.decode(FluidVariables.v_multi_editor.items[key]['p_fullname']) + '<br>';
		}

		document.getElementById('multi-item-selection-list').innerHTML = item_list_html;
	}
	catch(err){
		js_debug_error(err);
	}
}

// Load the item list.
function js_multi_item_load_list() {
	try {
		// Destroy image dropzones. Have them delete the images only when the modal is discarded or item changes are saved in continue ->.
		// Bug in the imageDropzone in multi item edit. If adding a image but not uploading, and saving or going back to the list, js error.
		FluidVariables.v_multi_editor.imageDropZoneDelete = false;
		js_image_dropzone_destroy();
		FluidVariables.v_multi_editor.imageDropZoneDelete = null;

		document.getElementById('multi-item-editor-tabs-div').innerHTML = "";
		document.getElementById('multi-item-editor-div').innerHTML = Base64.decode(FluidVariables.v_multi_editor.item_list_html);
		document.getElementById('multi-item-modal-footer').innerHTML = Base64.decode(FluidVariables.v_multi_editor.footer_save_html);

		document.getElementById('f-header-multi-item-div').innerHTML = "Multiple item editor";

		document.getElementById('f-multi-scroll-div').scrollTop = FluidVariables.v_multi_item_scroll;

		// Now check to see if the continue button needs to be enabled or not.
		for(var key in FluidVariables.v_multi_editor.items) {
			if(FluidVariables.v_multi_editor.items[key]['update'] == true)
				document.getElementById('multi-item-continue-button').disabled = false;
		}
	}
	catch(err){
		js_debug_error(err);
	}
}

// Remove a item from the multi item editor list from editing.
function js_multi_item_remove(p_id) {
	try {
		// Disable the continue save changes button.
		document.getElementById('multi-item-continue-button').disabled = true;

		// Remove the item from the multi item object list.
		delete FluidVariables.v_multi_editor.items[p_id];

		// Remove item from the innerHTML of the list.
		js_html_remove_element('multi-item-button-' + p_id);

		// Update the item html list in the multi item obj. -> FluidVariables.v_multi_editor.item_list_html
		FluidVariables.v_multi_editor.item_list_html = Base64.encode(document.getElementById('multi-item-editor-div').innerHTML);

		// Now check to see if the continue button needs to be enabled or not.
		for(var key in FluidVariables.v_multi_editor.items) {
			if(FluidVariables.v_multi_editor.items[key]['update'] == true) {
				document.getElementById('multi-item-continue-button').disabled = false;
			}
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_multi_item_update_clear() {
	try {
	// Empty out the keys in -> FluidVariables.v_multi_editor.items. Is this needed? As the loaded wipes it out when loading the multi item editor.
	for(var key in FluidVariables.v_multi_editor.items)
		delete FluidVariables.v_multi_editor.items[key];
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Updates and save all the multi item items in the editor into the database.
function js_multi_item_save(mode) {
	try {
		var selection_obj = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));
		var multi_item_obj = base64EncodingUTF8(JSON.stringify(FluidVariables.v_multi_editor.items));

		var FluidData = {};
		FluidData.f_search_input = FluidVariables.f_search_input;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = base64EncodingUTF8(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_multi_item_update&selection_obj=" + selection_obj + "&multi_item_obj=" + multi_item_obj + "&page_num=" + FluidVariables.f_page_num + "&mode=" + mode + "&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

// When returning from the item editor to the list view of the multi item editor. Lets update the item list objects and innerHTML.
function js_multi_item_update(p_id) {
	try {
		// Update the item obj -> FluidVariables.v_multi_editor.items[p_id]['data_obj']
		FluidVariables.v_multi_editor.items[p_id]['update'] = true;
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_status = Base64.encode(document.getElementById('product-status').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_zero_status = Base64.encode(document.getElementById('product-zero-status').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_trending = Base64.encode(document.getElementById('product-trending').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_instore = Base64.encode(document.getElementById('product-instore').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_arrivaltype = Base64.encode(document.getElementById('product-arrivaltype').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_freeship = Base64.encode(document.getElementById('product-freeship').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_preorder = Base64.encode(document.getElementById('product-preorder').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_rental = Base64.encode(document.getElementById('product-rental').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_special_order = Base64.encode(document.getElementById('product-special-order').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_namenum = Base64.encode(document.getElementById('product-namenum').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_showalways = Base64.encode(document.getElementById('product-alwaysshow').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_rebate_claim = Base64.encode(document.getElementById('product-rebate-claim').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_component = Base64.encode(document.getElementById('product-component').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock_end = Base64.encode(document.getElementById('product-stock-end').value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_price = Base64.encode(document.getElementById("product-price").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost = Base64.encode(document.getElementById("product-cost-average").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost_real = Base64.encode(document.getElementById("product-cost").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_cost_real_old = Base64.encode(document.getElementById("product-cost-old").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_price_discount = Base64.encode(document.getElementById("product-price-discount").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_discount_date_end = Base64.encode(document.getElementById("product-discount-price-end-date").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_discount_date_start = Base64.encode(document.getElementById("product-discount-price-start-date").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock = Base64.encode(document.getElementById("product-stock").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_stock_old = Base64.encode(document.getElementById("product-stock-old").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_buyqty = Base64.encode(document.getElementById("product-buyqty").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_name = Base64.encode(document.getElementById("product-name").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_barcode = Base64.encode(document.getElementById("product-barcode").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_mfg_number = Base64.encode(document.getElementById("product-mfg-number").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_description = base64EncodingUTF8(document.getElementById("product-description").innerHTML);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_details = base64EncodingUTF8(document.getElementById("product-details").innerHTML);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_specs = base64EncodingUTF8(document.getElementById("product-specifications").innerHTML);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_inthebox = base64EncodingUTF8(document.getElementById("product-inthebox").innerHTML);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_seo = Base64.encode(document.getElementById("product-seo-textarea").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_keywords = Base64.encode(document.getElementById("product-keywords-textarea").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_newarrivalenddate = Base64.encode(document.getElementById("product-arrival-end-date").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_category = Base64.encode(document.getElementById("product-category").options[document.getElementById("product-category").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_manufacturer = Base64.encode(document.getElementById("product-manufacturer").options[document.getElementById("product-manufacturer").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_length = Base64.encode(document.getElementById("product-length").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_width = Base64.encode(document.getElementById("product-width").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_height = Base64.encode(document.getElementById("product-height").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_weight = Base64.encode(document.getElementById("product-weight").value);

		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_status = Base64.encode(document.getElementById("formula-status").options[document.getElementById("formula-status").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_operation = Base64.encode(document.getElementById("formula-operation").options[document.getElementById("formula-operation").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_math = Base64.encode(document.getElementById("formula-math").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_application = Base64.encode(document.getElementById("formula-application").options[document.getElementById("formula-application").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_discount_date_end = Base64.encode(document.getElementById("formula-discount-price-end-date").value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_date_hide = Base64.encode(document.getElementById("product-date-hide").value);

		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_discount_date_start = Base64.encode(document.getElementById("formula-discount-price-start-date").value);
		$('#formula-item-list').selectpicker('destroy');
		$('#formula-item-list-faux').selectpicker('destroy');
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_html = Base64.encode(document.getElementById("f-formula-item-list-div").innerHTML);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_item_faux_html = Base64.encode(document.getElementById("f-formula-item-list-div-faux").innerHTML);

		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_flip = Base64.encode(document.getElementById("formula-flip").options[document.getElementById("formula-flip").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_message_display = Base64.encode(document.getElementById("formula-message-display").options[document.getElementById("formula-message-display").selectedIndex].value);
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_message = Base64.encode(document.getElementById("formula-message").value);

		<?php // --> Process the category product linking items. ?>
		var f_link_data = JSON.stringify(js_product_category_prep());

		if(f_link_data != "{}") {
			FluidVariables.v_multi_editor.items[p_id].data_obj.p_category_items_data = Base64.encode(f_link_data);
		}
		else {
			FluidVariables.v_multi_editor.items[p_id].data_obj.p_category_items_data = "";
		}

		FluidVariables.v_multi_editor.items[p_id].data_obj.p_component_html = Base64.encode(document.getElementById("component-list-select").innerHTML);

		<?php // --> Process the component item list. ?>
		var component_items = document.getElementById('component-list-select');
		var component_items_obj = {};

		for (var i=0; i < component_items.length; i++) {
			<?php //component_items_obj[i] = component_items.options[i].value; ?>
			component_items_obj[i] = {"p_id" : component_items.options[i].getAttribute("data-id"), "p_catid" : component_items.options[i].getAttribute("data-pcatid"), "p_mfgid" : component_items.options[i].getAttribute("data-pmfgid"), "p_mfgcode" : component_items.options[i].value, "p_quantity" : component_items.options[i].getAttribute("data-pquantity")};
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_component_data = Base64.encode(JSON.stringify(component_items_obj));

		<?php // --> Process the formula item list. ?>
		var f_filters = document.getElementById('formula-item-list');
		var f_filters_obj = {};

		for (var i=0; i < f_filters.length; i++) {
			f_filters_obj[i] = f_filters.options[i].value;
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_items_data = Base64.encode(JSON.stringify(f_filters_obj));

		var fx_filters = document.getElementById('formula-item-list-faux');
		var fx_filters_obj = {};

		for (var i=0; i < fx_filters.length; i++) {
			fx_filters_obj[i] = fx_filters.options[i].value;
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_formula_items_faux_data = Base64.encode(JSON.stringify(fx_filters_obj));

		<?php // --> Process the product category linking. ?>
		var c_linking = document.getElementById('product-category-linking');
		var c_linking_obj = {};

		for (var i = 0; i < c_linking.length; i++) {
			if(c_linking.options[i].selected) {
				var tmp_data = c_linking.options[i].value;
				c_linking_obj[tmp_data] = c_linking.options[i].value;
			}
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_linking = Base64.encode(JSON.stringify(c_linking_obj));

		<?php // --> Process the category filters. ?>
		var c_filters = document.getElementById('product-category-filters');
		var c_filters_obj = {};

		for (var i = 0; i < c_filters.length; i++) {
			if(c_filters.options[i].selected) {
				var tmp_data = JSON.parse(Base64.decode(c_filters.options[i].value));
				c_filters_obj[tmp_data.sub_id] = Base64.encode(c_filters.options[i].value);
			}
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_c_filters = Base64.encode(JSON.stringify(c_filters_obj));

		<?php // --> Process the manufacturer filters. ?>
		var m_filters = document.getElementById('product-manufacturer-filters');
		var m_filters_obj = {};

		for (var i = 0; i < m_filters.length; i++) {
			if(m_filters.options[i].selected) {
				var tmp_data = JSON.parse(Base64.decode(m_filters.options[i].value));
				m_filters_obj[tmp_data.sub_id] = Base64.encode(m_filters.options[i].value);
			}
		}
		FluidVariables.v_multi_editor.items[p_id].data_obj.p_m_filters = Base64.encode(JSON.stringify(m_filters_obj));

		<?php // --> Process the image sortorder and rebuild the data. ?>
		var i = 1;
		var image_temp_obj = {};
		var img_src = '<?php echo WWW_SITE . WWW_FILES . IMG_NO_IMAGE;?>';
		imageDropzone.files.forEach(function(file) {
			if(typeof file.xhr != "undefined") {
				FluidVariables.v_multi_editor.items[p_id].data_obj.p_imageorder[i] = {name: file.name, size: file.size };

				var xhr_response_data = JSON.parse(Base64.decode(file.xhr['response']));

				<?php // --> Rebuild the image_data -> FluidVariables.v_multi_editor.items[p_id].image_data ?>
				<?php // --> It should consist of: {0} object->xhr->response ?>
				image_temp_obj[xhr_response_data.file.image] = {};

				<?php // --> fullpath: -> temp file ?>
				<?php // --> name: -> temp file name ?>
				<?php // --> oldname: ?>
				<?php // --> size: image size. ?>
				image_temp_obj[xhr_response_data.file.image].fullpath = xhr_response_data.file.fullpath;
				image_temp_obj[xhr_response_data.file.image].name = xhr_response_data.file.image;
				image_temp_obj[xhr_response_data.file.image].oldname = xhr_response_data.file.name;
				image_temp_obj[xhr_response_data.file.image].size = xhr_response_data.file.size;
				image_temp_obj[xhr_response_data.file.image].xhr = {};
				image_temp_obj[xhr_response_data.file.image].xhr['response'] = file.xhr['response'];

				if(i == 1)
					img_src = '<?php echo WWW_SITE . WWW_IMAGES_TEMP;?>' + Base64.decode(FluidVariables.v_multi_editor.items[p_id].f_session_id) + '/' + xhr_response_data.file.image;

				i++;
			}
		});

		FluidVariables.v_multi_editor.items[p_id].image_data = Base64.encode(JSON.stringify({imgzone: Base64.encode(JSON.stringify(image_temp_obj))}));

		<?php // --> Copy over the image temp data into the proper image data obj. ?>
		FluidVariables.v_multi_editor.items[p_id]['data_obj'].p_images = FluidVariables.v_multi_editor.items[p_id]['data_obj'].p_images_tmp;

		<?php // --> Temporary hold the new item innerHTML data before we switch the old innerHTML list over. ?>
		var new_html_data = document.getElementById('product-manufacturer').options[document.getElementById('product-manufacturer').selectedIndex].getAttribute('data-name') + " " + document.getElementById("product-name").value + "<div><div style='display: inline-block; font-size: 10px; font-style: oblique; font-weight: 600;'>upc: " + document.getElementById("product-barcode").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>code: " + document.getElementById("product-mfg-number").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>cost: " + document.getElementById("product-cost").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>price: " + document.getElementById("product-price").value + "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; color: red; font-weight: 600;'>price disc: " + document.getElementById("product-price-discount").value + "</div></div>";

		<?php // --> Store the updated full name into the obj listing, for building the item list in the confirmation save menu. -> ?>
		FluidVariables.v_multi_editor.items[p_id]['p_fullname'] = Base64.encode(document.getElementById('product-manufacturer').options[document.getElementById('product-manufacturer').selectedIndex].getAttribute('data-name') + " " + document.getElementById("product-name").value);

		<?php // --> Switch the html back to the list. It is loading the old data, but then we will update it immediately. ?>
		js_multi_item_load_list();

		<?php // --> Update the item html list in the multi item obj. -> FluidVariables.v_multi_editor.item_list_html ?>
		document.getElementById('multi-item-editor-' + p_id).innerHTML = new_html_data;

		<?php // --> Update the image in the listing. ?>
		document.getElementById('multi-item-img-' + p_id).src = img_src;

		<?php // --> Update the item in the list to have a new background color to show changes were made. ?>
		document.getElementById('multi-item-button-' + p_id).style.backgroundColor = '#92FFA6';

		<?php // --> Then save the updated item list to the object. ?>
		FluidVariables.v_multi_editor.item_list_html = Base64.encode(document.getElementById('multi-item-editor-div').innerHTML);

		<?php // --> Enable the continue button to allow saving changes. ?>
		document.getElementById('multi-item-continue-button').disabled = false;

		document.getElementById('f-header-multi-item-div').innerHTML = "Multiple item editor";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_copy(mode) {
	var c_id = Base64.encode(document.getElementById("product-category-copy").options[document.getElementById("product-category-copy").selectedIndex].value);

	var data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_copy&data=" + data + "&c_id=" + c_id + "&page_num=" + FluidVariables.f_page_num + "&mode=" + mode + "&f_selection=" + FluidVariables.v_select_option}));
	js_fluid_ajax(data_obj);
}

function base64EncodingUTF8(str) {
	var encoded = new TextEncoderLite('utf-8').encode(str);
	var b64Encoded = base64js.fromByteArray(encoded);

	return b64Encoded;
}

function js_banners_create_and_edit(mode) {
	try {
		if(mode == 'edit')
			FluidVariables.v_product.p_id = Base64.encode(document.getElementById("product-id").value);

		FluidVariables.v_product.p_status = Base64.encode(document.getElementById("product-status").options[document.getElementById("product-status").selectedIndex].value);
		FluidVariables.v_product.p_name = Base64.encode(document.getElementById("banner-title").value);
		FluidVariables.v_product.p_details = Base64.encode(document.getElementById("banner-timer").value);

		<?php //Base64.encode(document.getElementById("product-description").innerHTML); ?>
		FluidVariables.v_product.p_description = base64EncodingUTF8(document.getElementById("banner-html").innerHTML);

		var data = base64EncodingUTF8(JSON.stringify(FluidVariables.v_product));
		var selection_data = base64EncodingUTF8(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data_obj = base64EncodingUTF8(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BANNER;?>", dataobj: "load=true&function=php_banners_create_and_edit&mode=" + mode + "&data=" + data + "&selection=" + selection_data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_create_and_edit(mode, mode_filter) {
	try {
		if(mode == 'edit')
			FluidVariables.v_product.p_id = Base64.encode(document.getElementById("product-id").value);

		FluidVariables.v_product.p_status = Base64.encode(document.getElementById("product-status").options[document.getElementById("product-status").selectedIndex].value);
		FluidVariables.v_product.p_zero_status = Base64.encode(document.getElementById("product-zero-status").options[document.getElementById("product-zero-status").selectedIndex].value);
		FluidVariables.v_product.p_trending = Base64.encode(document.getElementById("product-trending").options[document.getElementById("product-trending").selectedIndex].value);
		FluidVariables.v_product.p_instore = Base64.encode(document.getElementById("product-instore").options[document.getElementById("product-instore").selectedIndex].value);
		FluidVariables.v_product.p_arrivaltype = Base64.encode(document.getElementById("product-arrivaltype").options[document.getElementById("product-arrivaltype").selectedIndex].value);
		FluidVariables.v_product.p_freeship = Base64.encode(document.getElementById("product-freeship").options[document.getElementById("product-freeship").selectedIndex].value);
		FluidVariables.v_product.p_preorder = Base64.encode(document.getElementById("product-preorder").options[document.getElementById("product-preorder").selectedIndex].value);
		FluidVariables.v_product.p_rental = Base64.encode(document.getElementById("product-rental").options[document.getElementById("product-rental").selectedIndex].value);
		FluidVariables.v_product.p_special_order = Base64.encode(document.getElementById("product-special-order").options[document.getElementById("product-special-order").selectedIndex].value);
		FluidVariables.v_product.p_namenum = Base64.encode(document.getElementById("product-namenum").options[document.getElementById("product-namenum").selectedIndex].value);
		FluidVariables.v_product.p_showalways = Base64.encode(document.getElementById("product-alwaysshow").options[document.getElementById("product-alwaysshow").selectedIndex].value);
		FluidVariables.v_product.p_rebate_claim = Base64.encode(document.getElementById("product-rebate-claim").options[document.getElementById("product-rebate-claim").selectedIndex].value);
		FluidVariables.v_product.p_component = Base64.encode(document.getElementById("product-component").options[document.getElementById("product-component").selectedIndex].value);
		FluidVariables.v_product.p_stock_end = Base64.encode(document.getElementById("product-stock-end").options[document.getElementById("product-stock-end").selectedIndex].value);
		FluidVariables.v_product.p_category = Base64.encode(document.getElementById("product-category").options[document.getElementById("product-category").selectedIndex].value);
		FluidVariables.v_product.p_manufacturer = Base64.encode(document.getElementById("product-manufacturer").options[document.getElementById("product-manufacturer").selectedIndex].value);
		FluidVariables.v_product.p_price = Base64.encode(document.getElementById("product-price").value);
		FluidVariables.v_product.p_cost = Base64.encode(document.getElementById("product-cost-average").value);
		FluidVariables.v_product.p_cost_real = Base64.encode(document.getElementById("product-cost").value);
		FluidVariables.v_product.p_cost_real_old = Base64.encode(document.getElementById("product-cost-old").value);
		FluidVariables.v_product.p_price_discount = Base64.encode(document.getElementById("product-price-discount").value);
		FluidVariables.v_product.p_discount_date_end = Base64.encode(document.getElementById("product-discount-price-end-date").value);
		FluidVariables.v_product.p_discount_date_start = Base64.encode(document.getElementById("product-discount-price-start-date").value);
		FluidVariables.v_product.p_stock = Base64.encode(document.getElementById("product-stock").value);
		FluidVariables.v_product.p_stock_old = Base64.encode(document.getElementById("product-stock-old").value);
		FluidVariables.v_product.p_buyqty = Base64.encode(document.getElementById("product-buyqty").value);
		FluidVariables.v_product.p_name = Base64.encode(document.getElementById("product-name").value);
		FluidVariables.v_product.p_barcode = Base64.encode(document.getElementById("product-barcode").value);
		FluidVariables.v_product.p_mfg_number = Base64.encode(document.getElementById("product-mfg-number").value);

		<?php //Base64.encode(document.getElementById("product-description").innerHTML); ?>
		FluidVariables.v_product.p_description = base64EncodingUTF8(document.getElementById("product-description").innerHTML);

		FluidVariables.v_product.p_details = base64EncodingUTF8(document.getElementById("product-details").innerHTML);
		FluidVariables.v_product.p_specs = base64EncodingUTF8(document.getElementById("product-specifications").innerHTML);
		FluidVariables.v_product.p_inthebox = base64EncodingUTF8(document.getElementById("product-inthebox").innerHTML);
		FluidVariables.v_product.p_seo = Base64.encode(document.getElementById("product-seo-textarea").value);
		FluidVariables.v_product.p_keywords = Base64.encode(document.getElementById("product-keywords-textarea").value);
		FluidVariables.v_product.p_newarrivalenddate = Base64.encode(document.getElementById("product-arrival-end-date").value);
		FluidVariables.v_product.p_length = Base64.encode(document.getElementById("product-length").value);
		FluidVariables.v_product.p_width = Base64.encode(document.getElementById("product-width").value);
		FluidVariables.v_product.p_height = Base64.encode(document.getElementById("product-height").value);
		FluidVariables.v_product.p_weight = Base64.encode(document.getElementById("product-weight").value);

		FluidVariables.v_product.p_c_linking = {};
		FluidVariables.v_product.p_component_data = {};

		// Process the category filters.
		var c_filters = document.getElementById('product-category-filters');
		var c_filters_array = [];

		for (var i = 0; i < c_filters.length; i++) {
			if(c_filters.options[i].selected)
				c_filters_array.push(Base64.decode(c_filters.options[i].value));
		}
		FluidVariables.v_product.p_c_filters = Base64.encode(JSON.stringify(c_filters_array));

		// Process the manufacturer filters.
		var m_filters = document.getElementById('product-manufacturer-filters');
		var m_filters_array = [];

		for (var i = 0; i < m_filters.length; i++) {
			if(m_filters.options[i].selected)
				m_filters_array.push(Base64.decode(m_filters.options[i].value));
		}
		FluidVariables.v_product.p_m_filters = Base64.encode(JSON.stringify(m_filters_array));

		try {
			var i = 1;
			imageDropzone.files.forEach(function(file) {
				FluidVariables.v_product.p_imageorder[i] = {name: file.name, size: file.size };
				i++;
			});
		}
		catch(err) {
			js_debug_error(err);
		}

		var data = base64EncodingUTF8(JSON.stringify(FluidVariables.v_product));
		var selection_data = base64EncodingUTF8(JSON.stringify(FluidVariables.v_selection.p_selection));

		if(mode_filter == "import")
			var data_obj = base64EncodingUTF8(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_create_and_edit&page_num=" + FluidVariables.f_page_num + "&mode=" + mode + "&data=" + data + "&selection=" + selection_data + "&scan=" + mode_filter}));
		else
			var data_obj = base64EncodingUTF8(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_create_and_edit&page_num=" + FluidVariables.f_page_num + "&mode=" + mode + "&data=" + data + "&selection=" + selection_data + "&modefilter=" + mode_filter}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_banner_delete(mode) {
	var data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BANNER;?>", dataobj: "load=true&function=php_banners_delete&data=" + data + "&mode=" + mode}));
	js_fluid_ajax(data_obj);
}

function js_product_delete(mode) {
	var data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_delete&data=" + data + "&page_num=" + FluidVariables.f_page_num + "&mode=" + mode}));
	js_fluid_ajax(data_obj);
}

function js_product_move(mode) {
	var c_id = Base64.encode(document.getElementById("product-category-move").options[document.getElementById("product-category-move").selectedIndex].value);

	var data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_product_move&data=" + data + "&c_id=" + c_id + "&page_num=" + FluidVariables.f_page_num + "&mode=" + mode + "&f_selection=" + FluidVariables.v_select_option}));
	js_fluid_ajax(data_obj);
}

<?php // Selects a product. ?>
function js_product_select(p_id, p_catid, p_enable) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable > 0) {
				if(p_enable == 2) {
					document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISCONTINUED_ITEMS; ?>";
				}
				else {
					document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "transparent";
				}
			}
			else {
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";
			}

			// Adjust the category count of selected products.
			FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] - 1;

			// Adjust and or remove the category count of the selected products and reset background of colour of the category header if required.
			if(FluidVariables.v_selection.c_selection[p_catid] < 1) {
				delete FluidVariables.v_selection.c_selection[p_catid];
				document.getElementById('category-a-' + p_catid).style.backgroundColor = "transparent";

				document.getElementById('category-badge-select-count-' + p_catid).style.display = "none";
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = "";
			}
			else
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}
		else {
			FluidVariables.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable};

			// Adjust the category count of the selected products.
			if (typeof FluidVariables.v_selection.c_selection[p_catid] != "undefined")
				FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] + 1;
			else
				FluidVariables.v_selection.c_selection[p_catid] = 1;

			document.getElementById('category-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";

			document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "italic";

			document.getElementById('category-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
		}

		js_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_banners_select(p_id, p_catid, p_enable) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable > 0)
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "transparent";
			else
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";

			// Adjust the category count of selected products.
			FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] - 1;

			// Adjust and or remove the category count of the selected products and reset background of colour of the category header if required.
			if(FluidVariables.v_selection.c_selection[p_catid] < 1) {
				delete FluidVariables.v_selection.c_selection[p_catid];
				document.getElementById('category-a-' + p_catid).style.backgroundColor = "transparent";

				document.getElementById('category-badge-select-count-' + p_catid).style.display = "none";
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = "";
			}
			else
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}
		else {
			FluidVariables.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable};

			// Adjust the category count of the selected products.
			if (typeof FluidVariables.v_selection.c_selection[p_catid] != "undefined")
				FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] + 1;
			else
				FluidVariables.v_selection.c_selection[p_catid] = 1;

			document.getElementById('category-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";

			document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "italic";

			document.getElementById('category-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
		}

		js_update_action_banners();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_formula_links_items_process(f_quantity) {
	try {
		var FluidData = {};
			FluidData.f_items = FluidSelector.f_items;
			FluidData.f_selector = FluidSelector;
			FluidData.f_formula_list = FluidSelector.f_formula_list;
			FluidData.f_formula_list_div = FluidSelector.f_formula_list_div;
			FluidData.f_quantity = f_quantity;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_html_formula_links_items_builder&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_accounts_select(p_id, p_catid, p_enable) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable > 0)
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "transparent";
			else
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";

			// Adjust the category count of selected products.
			FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] - 1;

			// Adjust and or remove the category count of the selected products and reset background of colour of the category header if required.
			if(FluidVariables.v_selection.c_selection[p_catid] < 1) {
				delete FluidVariables.v_selection.c_selection[p_catid];
				document.getElementById('category-a-' + p_catid).style.backgroundColor = "transparent";

				document.getElementById('category-badge-select-count-' + p_catid).style.display = "none";
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = "";
			}
			else
				document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}
		else {
			FluidVariables.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable};

			// Adjust the category count of the selected products.
			if (typeof FluidVariables.v_selection.c_selection[p_catid] != "undefined")
				FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] + 1;
			else
				FluidVariables.v_selection.c_selection[p_catid] = 1;

			document.getElementById('category-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";

			document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "italic";

			document.getElementById('category-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
		}

		js_update_action_accounts();
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Selects all products in the chosen category.
function js_product_select_all(p_catid, data) {
	try {
		var data_array = JSON.parse(Base64.decode(data));
		var i = 0;
		var i_exist = 0;
		for(var key in data_array) {
			if(FluidVariables.v_selection.p_selection[key] != null)
				i_exist++;

			FluidVariables.v_selection.p_selection[key] = {"p_id" : key, "p_catid" : p_catid, "p_enable" : data_array[key]};
			document.getElementById('p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('p_id_tr_' + key).style.fontStyle = "italic";
			document.getElementById('p_id_' + key).checked = true;

			i++;
		}

		if(i > 0) {
			document.getElementById('category-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";

			// Adjust the category count of the selected products.
			if(FluidVariables.v_selection.c_selection[p_catid] != null) {
				FluidVariables.v_selection.c_selection[p_catid] = (FluidVariables.v_selection.c_selection[p_catid] + i) - i_exist;
			}
			else
				FluidVariables.v_selection.c_selection[p_catid] = i;

			document.getElementById('category-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-badge-select-count-' + p_catid).innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}

		js_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_product_switch_filters(mode, id, data) {
	try {
		var data_array = JSON.parse(Base64.decode(data));

		document.getElementById('product-' + mode + '-filters-div').innerHTML = Base64.decode(data_array[id]['innerHTML']);

		js_update_select_pickers(); // Update the select pickers.
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Refreshes the category header and item selection counts for them. php_multi_item_update() and php_product_create_and_edit() draw on this after completing there operations.
function js_refresh_category(data) {
	try {
		js_loading_start();

		var selection = JSON.parse(Base64.decode(data.selection));
		var c_selection = JSON.parse(Base64.decode(data.c_selection));

		// Need to update the selection data in the variables, then refresh the categories with the data.
		for(var key in selection) {

			// If the category this item is in is displayed on the screen, lets keep it in the item selection.
			if(typeof document.getElementById('category-a-' + selection[key]['p_catid']) != "undefined" && document.getElementById('category-a-' + selection[key]['p_catid']) != null) {
				if(FluidVariables.v_selection.p_selection[key] != null) {
					FluidVariables.v_selection.p_selection[key]['p_catid'] = selection[key]['p_catid'];
					FluidVariables.v_selection.p_selection[key]['p_enable'] = selection[key]['p_enable'];
				}
			}
			else
				delete FluidVariables.v_selection.p_selection[key]; // If the category / manufacturer this item is in is not on the display, lets remove it from the item selection.
		}

		// Search and remove or update existing selection of items in categories.
		for(var key in FluidVariables.v_selection.c_selection) {
			if(!c_selection.hasOwnProperty(key)) {
				delete FluidVariables.v_selection.c_selection[key];

				if(typeof document.getElementById('category-a-' + key) != "undefined" && document.getElementById('category-a-' + key) != null) {
					document.getElementById('category-a-' + key).style.backgroundColor = "transparent";

					document.getElementById('category-badge-select-count-' + key).style.display = "none";
					document.getElementById('category-badge-select-count-' + key).innerHTML = "";
				}
			}
			else {
				if(typeof document.getElementById('category-a-' + key) != "undefined" && document.getElementById('category-a-' + key) != null) {
					FluidVariables.v_selection.c_selection[key] = c_selection[key];
					document.getElementById('category-badge-select-count-' + key).innerHTML = FluidVariables.v_selection.c_selection[key] + " selected";
				}
			}
		}

		// Add new selection of items in categories.
		for(var key in c_selection) {
			if(typeof document.getElementById('category-a-' + key) != "undefined" && document.getElementById('category-a-' + key) != null) {
				if(!FluidVariables.v_selection.c_selection.hasOwnProperty(key)) {
					FluidVariables.v_selection.c_selection[key] = c_selection[key];

					document.getElementById('category-badge-select-count-' + key).style.display = "block";
					document.getElementById('category-badge-select-count-' + key).innerHTML = FluidVariables.v_selection.c_selection[key] + " selected";
					document.getElementById('category-a-' + key).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
				}
			}
		}

		js_update_action_menu();

		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refresh_category_products(data) {
	try {
		js_loading_start();

		for(var key in data['categories']) {
			if(typeof document.getElementById('category-a-' + key) != "undefined" && document.getElementById('category-a-' + key) != null) {
				// Refresh the badge counter on the category product count.
				//document.getElementById('category-badge-count-stock-' + key).innerHTML = data['categories'][key]['product_stock'];
				if(document.getElementById('category-badge-count-' + key) != null && document.getElementById('category-badge-count-' + key) != "undefined")
					document.getElementById('category-badge-count-' + key).innerHTML = data['categories'][key]['product_count'];

				// Refresh the product listing only on categories stacks that are open.
				if((document.getElementById('category-span-open-' + key).currentStyle ? document.getElementById('category-span-open-' + key).currentStyle.display : getComputedStyle(document.getElementById('category-span-open-' + key), null).display) === 'block')
					document.getElementById('category-div-' + key).innerHTML = data['products'][key];
			}
		}

		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

// This flag controls if sortable is enabled on categories of manufacturers parents or childs.
function js_reset_sort_prevent(reset_flag) {
	try {
		FluidVariables.v_sort_prevent = reset_flag;
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Peforms a search pagination on the item list. ?>
function js_pagination_search(f_page_num, mode) {
	try {
		if(mode == null) {
			mode = "items";
		}

		FluidVariables.f_page_num = f_page_num;

		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_search&search_input=" + Base64.encode(FluidVariables.f_search_input) + "&mode=" + mode + "&data_search=" + data}));
		js_fluid_ajax(data_obj, "content-div");
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Perform a search. ?>
function js_search(mode, search_input) {
	try {
		FluidVariables.f_page_num = 1;
		FluidVariables.f_search_input = search_input;

		var FluidData = {};
		FluidData.f_page_num = FluidVariables.f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_search&search_input=" + Base64.encode(search_input) + "&mode=" + mode + "&data_search=" + data}));
		js_fluid_ajax(data_obj, "content-div");
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Clears all products out of the selection arrays. ?>
function js_select_clear_p_selection(id_abbr) {
	try {
		// Clears the product selection.
		for(var key in FluidVariables.v_selection.p_selection)
			delete FluidVariables.v_selection.p_selection[key];

		// Clears the category item count selection and set the background colour back to default.
		for(var key in FluidVariables.v_selection.c_selection) {
			if(typeof document.getElementById('category-a-' + key) != "undefined" && document.getElementById('category-a-' + key) != null) {
				document.getElementById('category-a-' + key).style.backgroundColor = "transparent";
				document.getElementById('category-badge-select-count-' + key).style.display = "none";
				document.getElementById('category-badge-select-count-' + key).innerHTML = "";
			}
			delete FluidVariables.v_selection.c_selection[key];
		}

		js_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Clears all products of a object of categories chosen from the selection arrays.
function js_select_clear_p_selection_category(data64) {
	try {
		var data = JSON.parse(Base64.decode(data64));

		// Clears the product selection.
		for(var key in FluidVariables.v_selection.p_selection) {
			// If in item mode or if the key matches, remove the item from the selection.
			if(FluidVariables.v_selection.p_selection[key]['p_catid'] in data || data == "items" || data == FluidVariables.v_selection.p_selection[key]['p_catid']) {
				if(typeof document.getElementById('p_id_tr_' + key) != "undefined" && document.getElementById('p_id_tr_' + key) != null) {
					if(FluidVariables.v_selection.p_selection[key]['p_enable'] > 0) {
						if(FluidVariables.v_selection.p_selection[key]['p_enable'] == 2) {
							document.getElementById('p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_DISCONTINUED_ITEMS; ?>";
						}
						else {
							document.getElementById('p_id_tr_' + key).style.backgroundColor = "transparent";
						}
					}
					else {
						document.getElementById('p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";
					}

					document.getElementById('p_id_tr_' + key).style.fontStyle = "normal";
					document.getElementById('p_id_' + key).checked = false;
				}

				delete FluidVariables.v_selection.p_selection[key];
			}
		}

		// Update the headers in item, category and manufacturer modes.
		for(var key in data) {
			if(typeof document.getElementById('category-a-' + data[key]) != "undefined" && document.getElementById('category-a-' + data[key]) != null) {
				document.getElementById('category-a-' + data[key]).style.backgroundColor = "transparent";
				document.getElementById('category-badge-select-count-' + data[key]).style.display = "none";
				document.getElementById('category-badge-select-count-' + data[key]).innerHTML = "";
			}

			// Clears the category product count selection and set the background colour back to default.
			delete FluidVariables.v_selection.c_selection[data[key]];
		}

		js_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_banners(data) {
	try {
		<?php // Helper object that keeps the format of the table rows when dragging and dropping to there original size. ?>
		var fix_helper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};

		for(var key in data['categories']) {
			//console.log(key);
			//var k_data = JSON.parse(Base64.decode(key));
			var k_data = key;

			var $sortable1 = $(Base64.decode(data['categories'][key]['div']) + " tbody").sortable({
				cursor: 'move',
				opacity: 0.5,
				helper: fix_helper,
				handle: '.moverow',
				placeholder: 'ui-placeholder',
				start: function (event, ui) {
					$(this).attr('data-previndex', ui.item.index()); <?php // Keep track of the old index in the sort table. ?>

					ui.placeholder.html("<td colspan='4' style='width:" + document.getElementById(ui.item[0].id).offsetWidth + "px; height:" + document.getElementById(ui.item[0].id).offsetHeight + "px;'></td>")
				},
				stop: function (event, ui) {
					<?php // If the position on the index table changes, then proceed and update the sort order. ?>
					if(ui.item.index() != $(this).attr('data-previndex'))
						js_sortable_banners_update(ui.item.index(), document.getElementById(ui.item[0].id + "_td").innerHTML);
				},
				//connectWith: ".fsortable-" + k_data['c.c_id'],
				items: ".sorting-initialize" <?php // only insert element with class sorting-initialize. ?>
			});

			$sortable1.find(".f-td").one("mouseenter",function(){
			  var f_tr_id = $(this).data("move");
			  $("#" + f_tr_id).addClass("sorting-initialize");
			  $sortable1.sortable('refresh');
			});
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_banners_update(newpos, data) {
	try {
		var url = "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BANNER;?>";
		var selection_data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));
		var data_obj = Base64.encode(JSON.stringify({serverurl: url, dataobj: "load=true&function=php_sortable_banners_update&data=" + data + "&newpos=" + newpos + "&selection=" + selection_data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_categories() {
	try {
		$("#fluid-category-listing").sortable({
			cursor: 'move',
			opacity: 0.5,
			helper : 'clone', // Prevents the firing of the onClick event on the category stack.
			handle: '.moveparent',
			start: function (event, ui) {
				$(this).attr('data-prev-cat-index', ui.item.index()); // Keep track of the old index in the sort table.
			},
			stop: function (event, ui) {
				// If the position on the index table changes, then proceed and update the sort order.
				if(ui.item.index() != $(this).attr('data-prev-cat-index')) {
					// If a search result is shown in categories or manufacturers, then prevent resorting as it will break the parent sort orders. This happens because not all categories are returned back on the results.
					if(FluidVariables.v_sort_prevent == true) {
						js_debug_error("Parent categories can not be sorted during a search result set.");
						$(this).sortable('cancel');
					}
					else
						js_sortable_categories_update(ui.item.index(), document.getElementById(ui.item[0].id + "-data").innerHTML);
				}
			}
		});

		var cats_childs = document.getElementsByName('fluid-category-listing-childs');

		// Run through all the childs and set them up for sorting.
		for(var x=0; x < cats_childs.length; x++) {
			$("#" + cats_childs[x].getAttribute("id")).sortable({
				cursor: 'move',
				opacity: 0.5,
				helper : 'clone', // Prevents the firing of the onClick event on the category stack.
				handle: '.movecategory',
				start: function (event, ui) {
					$(this).attr('data-prev-cat-index', ui.item.index()); // Keep track of the old index in the sort table.
				},
				stop: function (event, ui) {
					// If the position on the index table changes, then proceed and update the sort order.
					if(ui.item.index() != $(this).attr('data-prev-cat-index')) {
						// If a search result is shown in categories or manufacturers, then prevent resorting as it will break the child sort orders. This happens because not all categories are returned back on the results.
						if(FluidVariables.v_sort_prevent == true) {
							js_debug_error("child categories can not be sorted during a search result set.");
							$(this).sortable('cancel');
						}
						else
							js_sortable_categories_update(ui.item.index(), document.getElementById(ui.item[0].id + "-data").innerHTML);
					}
				}
			});

			// The parent category has no childs, lets load the data-crumb data which contains a div element saying there are no childs in this parent.
			if(cats_childs[x].innerHTML == "")
				js_html_insert_element(JSON.parse(Base64.decode(cats_childs[x].getAttribute("data-crumb"))));
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_categories_update(newpos, data) {
	try {
		var url = "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>";
		var data_obj = Base64.encode(JSON.stringify({serverurl: url, dataobj: "load=true&function=php_sortable_categories_update&data=" + data + "&newpos=" + newpos}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_products(data) {
	try {
		<?php // Helper object that keeps the format of the table rows when dragging and dropping to there original size. ?>
		var fix_helper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};

		for(var key in data['categories']) {
			//console.log(key);
			//var k_data = JSON.parse(Base64.decode(key));
			var k_data = key;

			var $sortable1 = $(Base64.decode(data['categories'][key]['div']) + " tbody").sortable({
				cursor: 'move',
				opacity: 0.5,
				helper: fix_helper,
				handle: '.moverow',
				placeholder: 'ui-placeholder',
				start: function (event, ui) {
					$(this).attr('data-previndex', ui.item.index()); <?php // Keep track of the old index in the sort table. ?>

					ui.placeholder.html("<td colspan='8' style='width:" + document.getElementById(ui.item[0].id).offsetWidth + "px; height:" + document.getElementById(ui.item[0].id).offsetHeight + "px;'></td>")
				},
				stop: function (event, ui) {
					<?php // If the position on the index table changes, then proceed and update the sort order. ?>
					if(ui.item.index() != $(this).attr('data-previndex'))
						js_sortable_products_update(ui.item.index(), document.getElementById(ui.item[0].id + "_td").innerHTML);
				},
				connectWith: ".fsortable-" + k_data['c.c_id'],
				items: ".sorting-initialize" <?php // only insert element with class sorting-initialize. ?>
			});

			$sortable1.find(".f-td").one("mouseenter",function(){
			  var f_tr_id = $(this).data("move");
			  $("#" + f_tr_id).addClass("sorting-initialize");
			  $sortable1.sortable('refresh');
			});

			<?php
			/*
			$(Base64.decode(data['categories'][key]['div']) + " tbody").sortable({
				cursor: 'move',
				opacity: 0.5,
				helper: fix_helper,
				handle: '.moverow',
				placeholder: 'ui-placeholder',
				start: function (event, ui) {
					$(this).attr('data-previndex', ui.item.index()); // Keep track of the old index in the sort table.

					ui.placeholder.html("<td colspan='8' style='width:" + document.getElementById(ui.item[0].id).offsetWidth + "px; height:" + document.getElementById(ui.item[0].id).offsetHeight + "px;'></td>")
				},
				stop: function (event, ui) {
					// If the position on the index table changes, then proceed and update the sort order.
					if(ui.item.index() != $(this).attr('data-previndex'))
						js_sortable_products_update(ui.item.index(), document.getElementById(ui.item[0].id + "_td").innerHTML);
				}
			});
			*/
			?>
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sortable_products_update(newpos, data) {
	try {
		var url = "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>";
		var selection_data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));
		var data_obj = Base64.encode(JSON.stringify({serverurl: url, dataobj: "load=true&function=php_sortable_products_update&data=" + data + "&newpos=" + newpos + "&selection=" + selection_data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sub_filter_rename(id, value) {
	try {
		document.getElementsByName('sub-filter-raw-name-' + id )[0].innerHTML = value;
		document.getElementsByName('dropdown-sub-filter-rename-input-' + id)[0].value = value;
		document.getElementById('cf-td-' + id).innerHTML = value;
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sub_filter_rename_blur(id) {
	try {
		document.getElementsByName('dropdown-sub-filter-rename-input-' + id)[0].style.display="inline";
		document.getElementsByName('dropdown-sub-filter-rename-input-' + id)[0].focus();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_update_action_banners() {
	// Count all the objects in p_selection, if greater than 0, then enable the move and copy buttons. If 0, then disable the move button.
	if(Object.keys(FluidVariables.v_selection.p_selection).length > 0) {
		document.getElementById('li-delete').className = "";
		<?php //document.getElementById('li-editmulti').className = "";?>

		var pural = "";
		if(Object.keys(FluidVariables.v_selection.p_selection).length > 1)
			pural = "s";

		document.getElementById('li-delete-html').innerHTML = "Delete banner" + pural;
		<?php //document.getElementById('li-editmulti-html').innerHTML = "Edit item" + pural;?>
	}
	else {
		document.getElementById('li-delete').className = "disabled";
		document.getElementById('li-editmulti').className = "disabled";

		document.getElementById('li-delete-html').innerHTML = "Delete banner";
		<?php //document.getElementById('li-editmulti-html').innerHTML = "Edit item";?>
	}
}

function js_update_action_accounts() {
	// Count all the objects in p_selection, if greater than 0, then enable the move and copy buttons. If 0, then disable the move button.
	if(Object.keys(FluidVariables.v_selection.p_selection).length > 0) {
		var pural = "";
		if(Object.keys(FluidVariables.v_selection.p_selection).length > 1)
			pural = "s";

		document.getElementById('li-emails').className = "";
		document.getElementById('li-emails-html').innerHTML = "Send email" + pural;

	}
	else {
		document.getElementById('li-emails').className = "disabled";
		document.getElementById('li-emails-html').innerHTML = "Send email";
	}
}

function js_update_action_menu() {
	// Count all the objects in p_selection, if greater than 0, then enable the move and copy buttons. If 0, then disable the move button.
	if(Object.keys(FluidVariables.v_selection.p_selection).length > 0) {
		document.getElementById('li-move').className = "";
		document.getElementById('li-copy').className = "";
		document.getElementById('li-delete').className = "";
		document.getElementById('li-attribute').className = "";
		document.getElementById('li-download-images').className = "";
		document.getElementById('li-barcode').className = "";
		document.getElementById('li-export').className = "";
		document.getElementById('li-export-google').className = "";
		document.getElementById('li-editmulti').className = "";

		var pural = "";
		if(Object.keys(FluidVariables.v_selection.p_selection).length > 1)
			pural = "s";

		document.getElementById('li-move-html').innerHTML = "Move item" + pural;
		document.getElementById('li-copy-html').innerHTML = "Copy item" + pural;
		document.getElementById('li-delete-html').innerHTML = "Delete item" + pural;
		document.getElementById('li-attribute-html').innerHTML = "Set attribute" + pural;
		document.getElementById('li-download-images-html').innerHTML = "Item downloader";
		//document.getElementById('li-export-html').innerHTML = "Export selected";
		document.getElementById('li-editmulti-html').innerHTML = "Edit item" + pural;
		document.getElementById('li-barcode-html').innerHTML = "Print barcode" + pural;
	}
	else {
		document.getElementById('li-move').className = "disabled";
		document.getElementById('li-copy').className = "disabled";
		document.getElementById('li-delete').className = "disabled";
		document.getElementById('li-attribute').className = "disabled";
		document.getElementById('li-download-images').className = "disabled";
		document.getElementById('li-barcode').className = "disabled";
		document.getElementById('li-export').className = "disabled";
		document.getElementById('li-export-google').className = "disabled";

		document.getElementById('li-editmulti').className = "disabled";

		document.getElementById('li-move-html').innerHTML = "Move item";
		document.getElementById('li-copy-html').innerHTML = "Copy item";
		document.getElementById('li-delete-html').innerHTML = "Delete item";
		document.getElementById('li-attribute-html').innerHTML = "Set attribute";
		document.getElementById('li-download-images-html').innerHTML = "Item downloader";
		//document.getElementById('li-export-html').innerHTML = "Export selected";
		document.getElementById('li-editmulti-html').innerHTML = "Edit item";
		document.getElementById('li-barcode-html').innerHTML = "Print barcode";
	}
}

<?php // Block reveal animation function. This can block reveal any div. Array of divs can be passed with parameters. ?>
function js_fluid_block_animate(data64) {
	var data = JSON.parse(Base64.decode(data64));
	var f_delay_tmp = 0;

	var f_float_delay = 0;

	for(var key in data) {
		f_delay_tmp = parseInt(f_delay_tmp) + parseInt(data[key]['delay']);
		f_float_delay =  parseInt(f_float_delay) + parseInt(data[key]['delay']);

		if(data[key]['id'] != null) {
			var eletmp = document.getElementById(Base64.decode(data[key]['id']));

			if(eletmp != null) {
				if($('#' + Base64.decode(data[key]['id'])).css('display') != 'none' || $('#' + Base64.decode(data[key]['id'])).css('display') != null) {
					var rev = new RevealFx(document.querySelector('#' + Base64.decode(data[key]['id'])), {
						revealSettings : {
							bgcolor: data[key]['colour'],
							delay: f_delay_tmp,
							direction: 'lr',
							onStart: function(contentEl, revealerEl) {
								anime.remove(contentEl);
								contentEl.style.opacity = 0;
							},
							onCover: function(contentEl, revealerEl) {
								anime({
									targets: contentEl,
									duration: 500,
									delay: 50,
									easing: 'easeOutBounce',
									translateX: [-40,0],
									opacity: {
										value: [0,1],
										duration: 300,
										easing: 'linear'
									}
								});
							},
						}
					});
					rev.reveal();
				}
			}
		}
	}
}

<?php // Send the browser to a defined url. ?>
function js_redirect_url(data) {
	try {
		js_loading_start();
		window.location.href = Base64.decode(data['url']);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_columns() {
	try {
		var FluidData = {};

		var data = Base64.encode(JSON.stringify(FluidData));
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_columns&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_columns_save() {
	try {
		var FluidData = {};
			FluidData.f_columns_array = $('#f-columns-select').selectpicker('val');

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_columns_save&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_columns_set(data) {
	try {

		for(var key in data) {
			var t_cells = document.getElementsByName(key);

			for(var x=0; x < t_cells.length; x++) {
				t_cells[x].style.display = data[key]['data'];
			}
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_filter() {
	try {
		var FluidData = {};

		var data = Base64.encode(JSON.stringify(FluidData));
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_item_filters&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_filters_save() {
	try {
		var FluidData = {};
			FluidData.f_filters_array = $('#f-item-filters-select').selectpicker('val');

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_fluid_item_filters_save&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_filters_pagination_items(f_page_num, mode) {
	try {
		if(mode == null) {
			mode = "items";
		}

		FluidVariables.f_page_num = f_page_num;

		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_search&search_input=" + Base64.encode('') + "&mode=" + mode + "&data_search=" + data}));
		js_fluid_ajax(data_obj, "content-div");
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_mode() {
	try {
		FluidVariables.f_page_num = 0;

		js_fluid_ajax("<?php echo base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_items&mode=items")));?>", "content-div");
	}
	catch(err) {
		js_debug_error(err);
	}
}

</script>
