<?php
// fluid.import.js.php
// Michael Rajotte - 2017 Avril - 2018 Janvier
// Loads ajax php code.
?>

<script>
var FluidImport = {};
	FluidImport.f_column = null;
	FluidImport.f_manufacturer = "none";
<?php
/*
Ajax file uploader for CSV. It is easy to use. Instructions as follows:

$('#myform').fileUpload();
You can pass the following parameters to it:

$('#myform').fileUpload({
	uploadData    : { 'extra_data' : 'id_of_<input>_div' }, // Append POST data to the upload. Second value must be the id of the input div.
	submitData    : { 'moar_extra_data' : 'blah' }, // Append POST data to the form submit
	uploadOptions : { dataType : 'json' }, // Customise the parameters passed to the $.ajax() call on uploads. You can use any of the normal $.ajax() params
	submitOptions : { dataType : 'json' }, // Customise the parameters passed to the $.ajax() call on the form submit. You can use any of the normal $.ajax() params
	before	      : function(){}, // Run stuff before the upload happens
	beforeSubmit  : function(uploadData){ console.log(uploadData); return true; }, // access the data returned by the upload return false to stop the submit ajax call
	success       : function(data, textStatus, jqXHR){ console.log(data); }, // Callback for the submit success ajax call
	error 	      : function(jqXHR, textStatus, errorThrown){ console.log(jqXHR); }, // Callback if an error happens with your upload call or the submit call
	complete      : function(jqXHR, textStatus){ console.log(jqXHR); } // Callback on completion
});
*/
?>
function js_fluid_init_uploader(f_data) {
	$(f_data).fileUpload({
		uploadData    : { 'f_delimiter_import' : 'f_delimiter_hide'}, <?php // Append POST data to the upload. ?>
		beforeSubmit  : function(uploadData){ return true; }, <?php // access the data returned by the upload return false to stop the submit ajax call ?>
		before		  : function(){
			var FluidData = {};
				FluidData.delim = document.getElementById('f_delimiter').value;
				FluidData.delim_text = document.getElementById('f_text_delimiter').value;
				
				document.getElementById('f_delimiter_hide').value = Base64.encode(JSON.stringify(FluidData));
			},
		success       : function(data, textStatus, jqXHR){
			js_modal_toggle('#fluid-modal');
			<?php
			$temp_url_import = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_IMPORT_ADMIN, "dataobj" => "load=true&function=php_load_staging&mode=import")));?>
			js_fluid_ajax("<?php echo $temp_url_import; ?>", "content-div");
			}, <?php // Callback for the submit success ajax call ?>
		error 	      : function(jqXHR, textStatus, errorThrown){ console.log(errorThrown); }, <?php // Callback if an error happens with your upload call or the submit call ?>
		complete      : function(jqXHR, textStatus){ } <?php // Callback on completion ?>
	});
}
	
function js_update_action_menu_import() {
	// Count all the objects in p_selection, if greater than 0, then enable the move and copy buttons. If 0, then disable the move button.
	if(Object.keys(FluidVariables.v_selection.p_selection).length > 0) {
		document.getElementById('li-remove-row').className = "";
		document.getElementById('li-select-items').className = "";

		//document.getElementById('li-remove-column').className = "";
			
		var pural = "";				
		if(Object.keys(FluidVariables.v_selection.p_selection).length > 1)
			pural = "s";
				
		document.getElementById('li-remove-row-html').innerHTML = "Remove row" + pural;
		document.getElementById('li-remove-column-html').innerHTML = "Remove column" + pural;
	}
	else {
		document.getElementById('li-remove-row').className = "disabled";
		document.getElementById('li-remove-column').className = "disabled";
		document.getElementById('li-select-items').className = "disabled";
		
		document.getElementById('li-remove-row-html').innerHTML = "Remove row";
		document.getElementById('li-remove-column-html').innerHTML = "Remove column";
	}		
}

// Selects all products in the chosen category.
function js_staging_select_all() {
	try {
		var i = 0;
		var i_exist = 0;
		var p_catid = "import";
		
		for(var key in FluidVariables.v_import_items_found) {
			if(FluidVariables.v_selection.p_selection[key] != null)
				i_exist++;
				
			FluidVariables.v_selection.p_selection[key] = {"p_id" : key, "p_catid" : p_catid, "p_enable" : key};
			document.getElementById('i_id_tr_' + key).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('i_id_tr_' + key).style.fontStyle = "italic";

			var f_scan_id = document.getElementById('i_id_table_scan_' + key);

			if(f_scan_id != null) {
				f_scan_id.style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
				f_scan_id.style.fontStyle = "italic";
				FluidVariables.v_selection.p_selection[key]['f_pid'] = f_scan_id.getAttribute('data-pid');
			}				
			
			i++;
		}

		if(i > 0) {
			document.getElementById('category-a-import').style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";						

			// Adjust the category count of the selected products.
			if(FluidVariables.v_selection.c_selection[p_catid] != null) {
				FluidVariables.v_selection.c_selection[p_catid] = (FluidVariables.v_selection.c_selection[p_catid] + i) - i_exist;
			}
			else
				FluidVariables.v_selection.c_selection[p_catid] = i;

			document.getElementById('category-badge-select-count-import').style.display = "block";
			document.getElementById('category-badge-select-count-import').innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}
		else {
			delete FluidVariables.v_selection.c_selection[p_catid];
			document.getElementById('category-a-import').style.backgroundColor = "transparent";

			document.getElementById('category-badge-select-count-import').style.display = "none";
			document.getElementById('category-badge-select-count-import').innerHTML = "";
		}
		
		js_update_action_menu_import();
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Selects a row in the import staging.
function js_staging_select(p_id, p_catid, p_enable) {
	try {
		var f_scan_id = document.getElementById('i_id_table_scan_' + p_id);

		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('i_id_tr_' + p_id).style.fontStyle = "normal";
			if(f_scan_id != null)
				f_scan_id.style.fontStyle = "normal";
				
			if(p_enable > 0) {
				document.getElementById('i_id_tr_' + p_id).style.backgroundColor = "transparent";

				if(f_scan_id != null)
					f_scan_id.style.backgroundColor = "transparent";
			}
			else {
				document.getElementById('i_id_tr_' + p_id).style.backgroundColor = document.getElementById('i_id_tr_' + p_id).getAttribute("data-colour");

				if(f_scan_id != null)
					f_scan_id.style.backgroundColor = f_scan_id.getAttribute("data-colour");
			}

			// Adjust the category count of selected products.
			FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] - 1;
			
			// Adjust and or remove the category count of the selected products and reset background of colour of the category header if required.
			if(FluidVariables.v_selection.c_selection[p_catid] < 1) {
				delete FluidVariables.v_selection.c_selection[p_catid];
				document.getElementById('category-a-import').style.backgroundColor = "transparent";

				document.getElementById('category-badge-select-count-import').style.display = "none";
				document.getElementById('category-badge-select-count-import').innerHTML = "";
			}
			else
				document.getElementById('category-badge-select-count-import').innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";
		}
		else {
			FluidVariables.v_selection.p_selection[p_id] = {"p_id" : p_id, "p_catid" : p_catid, "p_enable" : p_enable};
			
			// Adjust the category count of the selected products.
			if (typeof FluidVariables.v_selection.c_selection[p_catid] != "undefined")
				FluidVariables.v_selection.c_selection[p_catid] = FluidVariables.v_selection.c_selection[p_catid] + 1;
			else
				FluidVariables.v_selection.c_selection[p_catid] = 1;

			document.getElementById('category-badge-select-count-import').style.display = "block";
			document.getElementById('category-badge-select-count-import').innerHTML = FluidVariables.v_selection.c_selection[p_catid] + " selected";

			document.getElementById('i_id_tr_' + p_id).style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
			document.getElementById('i_id_tr_' + p_id).style.fontStyle = "italic";

			if(f_scan_id != null) {
				f_scan_id.style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
				f_scan_id.style.fontStyle = "italic";
				FluidVariables.v_selection.p_selection[p_id]['f_pid'] = f_scan_id.getAttribute('data-pid');
			}
			
			document.getElementById('category-a-import').style.backgroundColor = "<?php echo COLOUR_SELECTED_CATEGORY; ?>";
		}
			
		js_update_action_menu_import();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_merge_data_confirm() {
	try {
		var FluidData = {};
			FluidData.f_import = FluidVariables.f_import;
			//FluidData.s_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_staging_merge_confirm&data=" + data}));
				
		js_fluid_ajax_fdom(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_import_staging_refresh() {
	try {
		var FluidData = {};
			//FluidData.f_scan = FluidVariables.f_import;
			FluidData.s_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));
			FluidData.f_column = FluidImport.f_column;
			FluidData.f_manufacturer = FluidImport.f_manufacturer;
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_load_staging&data=" + data}));
				
		js_fluid_ajax_fdom(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}	

function js_fluid_import_remove_rows() {
	try {
		var FluidData = {};
			FluidData.f_import = FluidVariables.f_import;
			FluidData.s_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_staging_row_remove&data=" + data}));
				
		js_fluid_ajax_fdom(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_staging_items_found(data) {
	try {
		FluidVariables.v_import_items_found = {};

		if(data != null)
			FluidVariables.v_import_items_found = data;
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_staging_merge_data() {
	try {
		FluidVariables.f_staging_prevent_import = true;
		var f_m = document.getElementById('fluid-staging-product-manufacturer');
		var f_c = document.getElementById('fluid-staging-product-category');

		//js_modal_hide('#fluid-modal');
		
		var FluidData = {};
			FluidData.f_import = FluidVariables.f_import;
			FluidData.f_manufacturer = f_m.options[f_m.selectedIndex].value;
			FluidData.f_category = f_c.options[f_c.selectedIndex].value;

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_staging_merge_data&data=" + data}));
				
		js_fluid_ajax(data_obj);			
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_staging_merge_button_check() {
	try {
		var f_m = document.getElementById('fluid-staging-product-manufacturer');
		var f_c = document.getElementById('fluid-staging-product-category');
		var f_btn = document.getElementById('fluid-staging-confirm-button');
		
		if(f_m != null && f_c != null) {
			if(f_m.options[f_m.selectedIndex].value != "none" && f_c.options[f_c.selectedIndex].value != "none") {
				f_btn.className = f_btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
				f_btn.disabled = false;
				FluidVariables.f_staging_prevent_import = false;
			}
			else {
				f_btn.className += " disabled";
				f_btn.disabled = true;
				FluidVariables.f_staging_prevent_import = true;
			}
		}
		else {
			f_btn.className += " disabled";
			f_btn.disabled = true;
			FluidVariables.f_staging_prevent_import = true;
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_staging_column_set() {
	for(var key in FluidVariables.f_import)
		delete FluidVariables.f_import[key];
			
	var f_columns = document.getElementsByName('f_import_select');

	for(var x=0; x < f_columns.length; x++) {
		if(f_columns[x].options[f_columns[x].selectedIndex].value != "f_default_import_select_ignore") {
			var f_value = f_columns[x].options[f_columns[x].selectedIndex].value;
			FluidVariables.f_import[f_value] = {"f_column" : f_value, "i_column" : $(f_columns[x]).data("column"), "select_id" : f_columns[x].getAttribute('id')};
		}
	}

	for(var x=0; x < f_columns.length; x++) {
		var f_options = f_columns[x].options;
		
		for(var opt=0; opt < f_options.length; opt++) {
			if(FluidVariables.f_import[f_options[opt].value] != null) {
				if(FluidVariables.f_import[f_options[opt].value]['select_id'] == f_columns[x].getAttribute('id'))
					f_options[opt].disabled = false;
				else
					f_options[opt].disabled = true;
			}
			else {
				f_options[opt].disabled = false;
			}
		}
	}

	//console.log(FluidVariables.f_import);
	<?php // Update the action menu for Scanning for items ?>
	if(FluidVariables.f_import['p_mfgcode'] != null) {
		document.getElementById('li-scan-items').className = "";		
		document.getElementById('li-merge-data').className = "";
		document.getElementById('li-select-row').className = "";

		if(FluidVariables.f_import['p_mfg_number'] != null)
			document.getElementById('li-scan-items-mfg-number').className = "";
		else
			document.getElementById('li-scan-items-mfg-number').className = "disabled";
	}
	else if(FluidVariables.f_import['p_mfg_number'] != null) {
		document.getElementById('li-scan-items-mfg-number').className = "";		
		document.getElementById('li-merge-data').className = "";
		document.getElementById('li-select-row').className = "";

		if(FluidVariables.f_import['p_mfgcode'] != null)
			document.getElementById('li-scan-items').className = "";
		else
			document.getElementById('li-scan-items').className = "disabled";		
	}
	else {
		document.getElementById('li-scan-items').className = "disabled";
		document.getElementById('li-scan-items-mfg-number').className = "disabled";
		document.getElementById('li-merge-data').className = "disabled";
		document.getElementById('li-select-row').className = "disabled";
	}

	js_update_select_pickers();
}

function js_fluid_scan_items(f_column, f_manufacturer) {
	try {
		FluidImport.f_column = f_column;
		FluidImport.f_manufacturer = f_manufacturer;
		
		var FluidData = {};
			FluidData.f_import = FluidVariables.f_import;
			FluidData.f_scan = true;
			FluidData.f_column = f_column
			FluidData.f_manufacturer = f_manufacturer;
			FluidData.s_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_load_staging&data=" + data}));
				
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_scan_items_menu(f_column) {
	try {
		FluidImport.f_column = f_column;
		
		var FluidData = {};
			FluidData.f_column = f_column

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_IMPORT_ADMIN;?>", dataobj: "load=true&function=php_load_scan_menu&data=" + data}));
				
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_staging_search(f_column) {
	try {
		var f_m = document.getElementById('fluid-staging-product-manufacturer');
		f_manufacturer = f_m.options[f_m.selectedIndex].value;
		js_modal_hide('#fluid-modal');
		js_fluid_scan_items(f_column, f_manufacturer);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_switch_to_item_mode() {
	try {
		<?php
		// --> Rebuild the item selection
		?>
		FluidItemSelection = {};
		
		for(var key in FluidVariables.v_selection.p_selection)
			if(FluidVariables.v_selection.p_selection[key]['f_pid'] != null)
				FluidItemSelection[FluidVariables.v_selection.p_selection[key]['f_pid']] = {"p_id" : FluidVariables.v_selection.p_selection[key]['f_pid'], "p_catid" : "items", "p_enable" : null};
						
		for(var key in FluidVariables.v_selection.p_selection)
			delete FluidVariables.v_selection.p_selection[key];

		FluidVariables.v_selection.p_selection = FluidItemSelection;

		if(FluidVariables.v_selection.c_selection["import"])
			delete FluidVariables.v_selection.c_selection["import"];

		FluidVariables.v_selection.c_selection["items"] = Object.keys(FluidVariables.v_selection.p_selection).length;
						
		var FluidData = {};
		FluidData.f_page_num = 1;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_keep_selection = true;
		FluidData.mode = "items";

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOADER;?>", dataobj: "load=true&function=php_load_items&data=" + data}));
			
		js_fluid_ajax(data_obj, 'content-div');
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
