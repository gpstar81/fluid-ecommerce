<?php
// fluid.logs.js.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.
?>

<script>
var FluidLogs = {};

FluidLogs.f_page_num = 1;
FluidLogs.f_search_input = "";
FluidLogs.d_mode = null;

function js_fluid_logs_delete_cleanup() {
	try {
		js_clear_fluid_selection();

		js_fluid_logs_load(FluidLogs.f_page_num, "logs", FluidLogs.d_mode);
		
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_logs_load(f_page_num, mode, dmode) {
	try {
		FluidLogs.d_mode = dmode;
		FluidLogs.f_page_num = f_page_num;
		
		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.d_mode = dmode;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOGS_ADMIN;?>", dataobj: "load=true&function=php_logs_load&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_logs_delete() {
	try {
		var FluidData = {};
		FluidData.f_page_num = FluidLogs.f_page_num;
		FluidData.d_mode = FluidLogs.d_mode;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOGS_ADMIN;?>", dataobj: "load=true&function=php_logs_delete&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_logs_view_data(l_ud) {
	try {
		var FluidData = {};
		FluidData.l_ud = l_ud;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_LOGS_ADMIN;?>", dataobj: "load=true&function=php_logs_data_view&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_logs_select(p_id, p_catid, p_enable) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable == 1)
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
			
		js_logs_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_logs_update_action_menu() {
	// Count all the objects in p_selection, if greater than 0, then enable the move and copy buttons. If 0, then disable the move button.
	if(Object.keys(FluidVariables.v_selection.p_selection).length > 0) {
		var pural = "";				
		if(Object.keys(FluidVariables.v_selection.p_selection).length > 1)
			pural = "s";
						
		document.getElementById('li-log-delete').className = "";
		document.getElementById('li-log-delete-html').innerHTML = "Delete log" + pural;
				
	}
	else {
		document.getElementById('li-log-delete').className = "disabled";
		document.getElementById('li-log-delete-html').innerHTML = "Delete log";
	}
}		

</script>
