<?php
// fluid.selector.js.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.
?>

<script>
var FluidSelector = {};
	FluidSelector.f_items = {};
	FluidSelector.f_return_function = "";
	FluidSelector.f_formula_list = "";
	FluidSelector.f_formula_list_div = "";

	FluidSelector.v_selection = {};
		FluidSelector.v_selection.p_selection = {};
		FluidSelector.v_selection.c_selection = {};

	FluidSelector.f_page_num = 1;
	FluidSelector.f_search_input = "";

function js_fluid_load_item_selector(f_return_function, f_formula_list, f_formula_list_div, mode, f_quantity) {
	try {
		js_loading_start();

		FluidSelector.v_selection = null

		FluidSelector.v_selection = {};
			FluidSelector.v_selection.p_selection = {};
			FluidSelector.v_selection.c_selection = {};

		for (var i = 0; i < $('select#' + f_formula_list + ' option').length; i++) {
			if(FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")] == null)
				FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")] = {"p_id" : null, "p_catid" : null, "p_enable" : null};

			FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_id = $('select#' + f_formula_list + ' option')[i].getAttribute("data-id");

			if(f_quantity == true) {
				FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_quantity = $('select#' + f_formula_list + ' option')[i].getAttribute("data-pquantity");
			}

			if(mode == "manufacturers") {
				FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_cat = $('select#' + f_formula_list + ' option')[i].getAttribute("data-pmfgid");

				if(FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pmfgid")] == null)
					FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pmfgid")] = 1;
				else
					FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pmfgid")]++;
			}
			else if(mode == "categories") {
				FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_cat = $('select#' + f_formula_list + ' option')[i].getAttribute("data-pcatid");

				if(FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pcatid")] == null)
					FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pcatid")] = 1;
				else
					FluidSelector.v_selection.c_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-pcatid")]++;
			}
			else {
				FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_catid = "items";

				if(FluidSelector.v_selection.c_selection["items"] == null)
					FluidSelector.v_selection.c_selection["items"] = 1;
				else
					FluidSelector.v_selection.c_selection["items"]++;
			}


			FluidSelector.v_selection.p_selection[$('select#' + f_formula_list + ' option')[i].getAttribute("data-id")].p_enable = $('select#' + f_formula_list + ' option')[i].getAttribute("data-penable");
		}

		FluidSelector.f_return_function = f_return_function;
		FluidSelector.f_formula_list = f_formula_list;
		FluidSelector.f_formula_list_div = f_formula_list_div;

		FluidSelector.f_page_num = 1;

		var FluidData = {};
			FluidData.f_items = FluidSelector.f_items; <?php // --> Not used? ?>
			FluidData.mode = mode;
			FluidData.f_selection = FluidSelector.v_selection;
			FluidData.f_quantity = f_quantity;
			//FluidData.f_manufacturer = f_m.options[f_m.selectedIndex].value;
			//FluidData.f_category = f_c.options[f_c.selectedIndex].value;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SELECTOR_ADMIN;?>", dataobj: "load=true&function=php_fluid_load_item_selector&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_loading_stop();
		js_debug_error(err);
	}
}

function js_fluid_load_items_selector(f_page_num, mode, dmode, f_quantity) {
	try {
		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidSelector.v_selection;
		FluidData.f_refresh = true;
		FluidData.mode = mode;
		FluidData.f_quantity = f_quantity;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SELECTOR_ADMIN;?>", dataobj: "load=true&function=php_fluid_load_item_selector&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Runs the return back function, letting whichever process to do what it wants with the selected data. ?>
function js_fluid_item_selector_save() {
	try {
		window[FluidSelector.f_return_function]();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_pagination_search_selector(f_page_num, mode, dmode) {
	try {
		FluidSelector.f_page_num = f_page_num;

		var FluidData = {};
			FluidData.f_page_num = f_page_num;
			FluidData.f_selection = FluidSelector.v_selection;
			FluidData.f_refresh = true;
			FluidData.mode = mode;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SELECTOR_ADMIN;?>", dataobj: "load=true&function=php_search_selector&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Perform a search.
function js_search_selector(mode, search_input, f_quantity) {
	try {
		var FluidData = {};
			FluidData.f_items = FluidSelector.f_items;
			FluidData.search_input = search_input;
			FluidData.mode = mode;
			FluidData.f_page_num = FluidSelector.f_page_num;
			FluidData.f_selection = FluidSelector.v_selection;
			FluidData.f_refresh = true;
			FluidData.f_quantity = f_quantity;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SELECTOR_ADMIN;?>", dataobj: "load=true&function=php_search_selector&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_selector(p_id, p_catid, p_enable, p_quantity) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidSelector.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidSelector.v_selection.p_selection[p_id];

			document.getElementById('selector_p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable == 1) {
				document.getElementById('selector_p_id_tr_' + p_id).style.backgroundColor = "transparent";
			}
			else if(p_enable == 2) {
				document.getElementById('selector_p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISCONTINUED_ITEMS; ?>";
			}
			else {
				document.getElementById('selector_p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";
			}

			// Adjust the category count of selected products.
			FluidSelector.v_selection.c_selection[p_catid] = FluidSelector.v_selection.c_selection[p_catid] - 1;

			// Adjust and or remove the category count of the selected products and reset background of colour of the category header if required.
			if(FluidSelector.v_selection.c_selection[p_catid] < 1) {
				delete FluidSelector.v_selection.c_selection[p_catid];
				document.getElementById('category-selector-a-' + p_catid).style.backgroundColor = "transparent";

				document.getElementById('category-selector-badge-select-count-' + p_catid).style.display = "none";
				document.getElementById('category-selector-badge-select-count-' + p_catid).innerHTML = "";
			}
			else {
				document.getElementById('category-selector-badge-select-count-' + p_catid).innerHTML = FluidSelector.v_selection.c_selection[p_catid] + " selected";
			}
		}
		else {
			if(p_quantity == true) {
				var p_component_quantity = document.getElementById("p_td_id_component_quantity_spinner_" + p_id).value;
				FluidSelector.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable, "p_quantity" : p_component_quantity};
			}
			else {
				FluidSelector.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable};
			}

			// Adjust the category count of the selected products.
			if(typeof FluidSelector.v_selection.c_selection[p_catid] != "undefined") {
				FluidSelector.v_selection.c_selection[p_catid] = FluidSelector.v_selection.c_selection[p_catid] + 1;
			}
			else {
				FluidSelector.v_selection.c_selection[p_catid] = 1;
			}

			document.getElementById('category-selector-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-selector-badge-select-count-' + p_catid).innerHTML = FluidSelector.v_selection.c_selection[p_catid] + " selected";

			document.getElementById('selector_p_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('selector_p_id_tr_' + p_id).style.fontStyle = "italic";

			document.getElementById('category-selector-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_item_selector_quantity_update(p_id, p_quantity) {
	try {
		if(FluidSelector.v_selection.p_selection.hasOwnProperty(p_id)) {
			FluidSelector.v_selection.p_selection[p_id]['p_quantity'] = p_quantity;
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Selects all products in the chosen category.
function js_selector_select_all(p_catid, data) {
	try {
		var data_array = JSON.parse(Base64.decode(data));
		var i = 0;
		var i_exist = 0;
		for(var key in data_array) {
			if(FluidSelector.v_selection.p_selection[key] != null)
				i_exist++;

			FluidSelector.v_selection.p_selection[key] = {"p_id" : key, "p_catid" : p_catid, "p_enable" : data_array[key]};
			document.getElementById('selector_p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('selector_p_id_tr_' + key).style.fontStyle = "italic";
			document.getElementById('selector_p_id_' + key).checked = true;

			i++;
		}

		if(i > 0) {
			document.getElementById('category-selector-a-' + p_catid).style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";

			// Adjust the category count of the selected products.
			if(FluidSelector.v_selection.c_selection[p_catid] != null) {
				FluidSelector.v_selection.c_selection[p_catid] = (FluidSelector.v_selection.c_selection[p_catid] + i) - i_exist;
			}
			else
				FluidSelector.v_selection.c_selection[p_catid] = i;

			document.getElementById('category-selector-badge-select-count-' + p_catid).style.display = "block";
			document.getElementById('category-selector-badge-select-count-' + p_catid).innerHTML = FluidSelector.v_selection.c_selection[p_catid] + " selected";
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Clears all products of a object of categories chosen from the selection arrays.
function js_select_clear_p_selection_category_selector(data64) {
	try {
		var data = JSON.parse(Base64.decode(data64));

		// Clears the product selection.
		for(var key in FluidSelector.v_selection.p_selection) {
			// If in item mode or if the key matches, remove the item from the selection.
			if(FluidSelector.v_selection.p_selection[key]['p_catid'] in data || data == "items" || data == FluidSelector.v_selection.p_selection[key]['p_catid']) {
				if(typeof document.getElementById('selector_p_id_tr_' + key) != "undefined" && document.getElementById('selector_p_id_tr_' + key) != null) {
					if(FluidSelector.v_selection.p_selection[key]['p_enable'] == 1) {
						document.getElementById('selector_p_id_tr_' + key).style.backgroundColor = "transparent";
					}
					else if(FluidSelector.v_selection.p_selection[key]['p_enable'] == 2) {
						document.getElementById('selector_p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_DISCONTINUED_ITEMS; ?>";
					}
					else {
						document.getElementById('selector_p_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_DISABLED_ITEMS; ?>";
					}

					document.getElementById('selector_p_id_tr_' + key).style.fontStyle = "normal";
					document.getElementById('selector_p_id_' + key).checked = false;
				}

				delete FluidSelector.v_selection.p_selection[key];
			}
		}

		// Update the headers in item, category and manufacturer modes.
		for(var key in data) {
			if(typeof document.getElementById('category-selector-a-' + data[key]) != "undefined" && document.getElementById('category-selector-a-' + data[key]) != null) {
				document.getElementById('category-selector-a-' + data[key]).style.backgroundColor = "transparent";
				document.getElementById('category-selector-badge-select-count-' + data[key]).style.display = "none";
				document.getElementById('category-selector-badge-select-count-' + data[key]).innerHTML = "";
			}

			// Clears the category product count selection and set the background colour back to default.
			delete FluidSelector.v_selection.c_selection[data[key]];
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

</script>
