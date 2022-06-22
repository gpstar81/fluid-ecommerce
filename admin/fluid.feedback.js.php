<?php
// fluid.feedback.js.php
// Michael Rajotte - 2018 Janvier
?>

<script>

function js_fluid_load_feedback(f_page_num, mode, dmode) {
	try {
		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_FEEDBACK_ADMIN;?>", dataobj: "load=true&function=php_load_feedback&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_feedback_select(p_id, p_catid, p_enable) {
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
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
