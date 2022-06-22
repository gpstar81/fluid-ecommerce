<?php
// fluid.orders.js.php
// Michael Rajotte - 2018 Janvier
// Loads ajax php code.
?>

<script>

function js_fluid_load_orders(f_page_num, mode, dmode) {
	try {
		var FluidData = {};
		FluidData.f_page_num = f_page_num;

		if(dmode != '')
			FluidData.d_mode = dmode;
			
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_load_orders&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}
	
function js_update_order(s_id) {
	try {
		var FluidData = {};
			FluidData.s_id = document.getElementById("s-data-" + s_id).value;
			FluidData.s_status = document.getElementById("s-id-status-" + s_id).options[document.getElementById("s-id-status-" + s_id).selectedIndex].value;
			FluidData.s_tracking = document.getElementById("s-tracking-" + s_id).value;
			FluidData.s_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_update_order&data=" + data}));
				
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_update_order_rows(f_data) {
	try {
		var div_id = Base64.decode(f_data['id']);

		document.getElementById(div_id).style.background = Base64.decode(f_data['s_colour']);
		document.getElementById(div_id).setAttribute('data-colour', Base64.decode(f_data['d_colour']));
	}
	catch(err) {
		js_debug_error(err);
	}
}

// Selects a order.
function js_order_select(p_id, p_catid, p_enable) {
	try {
		// Exists in the object, lets remove it now.
		if(FluidVariables.v_selection.p_selection.hasOwnProperty(p_id)) {
			delete FluidVariables.v_selection.p_selection[p_id];

			document.getElementById('p_id_tr_' + p_id).style.fontStyle = "normal";
			if(p_enable > 0)
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = "transparent";
			else
				document.getElementById('p_id_tr_' + p_id).style.backgroundColor = document.getElementById('p_id_tr_' + p_id).getAttribute("data-colour");

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
			
		//js_update_action_menu();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_confirm() {
	try {
		var FluidRefund = {};
			FluidRefund.rf_items = FluidVariables.v_r_selection;			
			FluidRefund.rf_id = document.getElementById('f-s-order-number-id').value;
			FluidRefund.rf_sid = document.getElementById('f-s-id').value;				
			FluidRefund.rf_total = FluidVariables.v_r_total_complete;
			FluidRefund.tax_toggle = FluidVariables.v_r_refund_tax_toggle;

		var data = Base64.encode(JSON.stringify(FluidRefund));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_refund_confirm&data=" + data}));
		js_fluid_ajax(data_obj); 
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_unlock() {
	try {
		document.getElementById('rv-refund-input').disabled = false;
		document.getElementById('f-refund-button-unlock').style.display = 'none';
		document.getElementById('f-refund-button-lock').style.display = 'block';
		document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2);
		document.getElementById('f-refund-item-holder-div').style.backgroundColor = '#e9e9e9';
		document.getElementById('f-refund-item-holder-div').style.cursor = 'not-allowed';
		
		<?php // --> Unselect all items as we are switching to manual mode and reset the total to zero. ?>
		for(var key in FluidVariables.v_r_selection) {
			var f_row = document.getElementById('row-rid-' + key);
			
			delete FluidVariables.v_r_selection[key];

			f_row.style.fontStyle = "normal";
			f_row.style.backgroundColor = "transparent";
		}

		FluidVariables.v_r_total = parseFloat(0.00);
		FluidVariables.v_r_manual_input = parseFloat(0.00);
		FluidVariables.v_r_total_tax = parseFloat(0.00);
		FluidVariables.v_r_total_complete = parseFloat(0.00);			

		document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2);
		
		document.getElementById('f-refund-button').disabled = true;
					
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_lock() {
	try {
		document.getElementById('rv-refund-input').disabled = true;
		document.getElementById('f-refund-button-unlock').style.display = 'block';
		document.getElementById('f-refund-button-lock').style.display = 'none';
		document.getElementById('f-refund-item-holder-div').style.backgroundColor = 'transparent';
		document.getElementById('f-refund-item-holder-div').style.cursor = 'default';
		
		<?php // --> Switching to selection mode, reset everything to zero. ?>
		FluidVariables.v_r_total = parseFloat(0.00);
		FluidVariables.v_r_manual_input = parseFloat(0.00);
		FluidVariables.v_r_total_tax = parseFloat(0.00);
		FluidVariables.v_r_total_complete = parseFloat(0.00);
					
		document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2) + " (estimated)";

		document.getElementById('f-refund-button').disabled = true;
					
	}
	catch(err) {
		js_debug_error(err);
	}
}	

function js_refund() {
	try {
		var FluidRefund = {};
			FluidRefund.rf_items = FluidVariables.v_r_selection;			
			FluidRefund.rf_id = document.getElementById('f-s-order-number-id').value;
			FluidRefund.rf_sid = document.getElementById('f-s-id').value;
			FluidRefund.rf_total = document.getElementById('f-refund-id-total').value;

		var data = Base64.encode(JSON.stringify(FluidRefund));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_refund&data=" + data}));
		js_fluid_ajax(data_obj); 
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_toggle_taxes() {
	try {		
		if(FluidVariables.v_r_refund_tax_toggle == true) {
			FluidVariables.v_r_refund_tax_toggle = false;
			$("#f-refund-button-no-tax-div").html("$ With Tax");

		}
		else {
			FluidVariables.v_r_refund_tax_toggle = true;
			$("#f-refund-button-no-tax-div").html("$ Without Tax");
		}

		js_refund_calculate_price();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_calculate_price() {
	try {
		if(FluidVariables.v_r_refund_tax_toggle == true) {
			js_refund_no_tax();
		}
		else {
			js_refund_with_tax();
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_no_tax() {
	try {
		FluidVariables.v_r_total_complete = parseFloat(FluidVariables.v_r_total)
		
		if(FluidVariables.v_r_total_complete < 0 || Object.keys(FluidVariables.v_r_selection).length < 1) {
			FluidVariables.v_r_total_complete = parseFloat(0.00);
			document.getElementById('f-refund-button').disabled = true;
		}
		else {
			document.getElementById('f-refund-button').disabled = false;
		}
		
		document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2) + " (estimated)";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_with_tax() {
	try {
		FluidVariables.v_r_total_complete = parseFloat(FluidVariables.v_r_total) + parseFloat(FluidVariables.v_r_total_tax);

		if(FluidVariables.v_r_total_complete < 0 || Object.keys(FluidVariables.v_r_selection).length < 1) {
			FluidVariables.v_r_total_complete = parseFloat(0.00);
			document.getElementById('f-refund-button').disabled = true;
		}
		else {
			document.getElementById('f-refund-button').disabled = false;
		}

		document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2) + " (estimated)";
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Selects a product for refunding. ?>
function js_refund_p_select(s_id) {
	try {
		if(document.getElementById('f-refund-button-unlock').style.display == 'block') {
			var f_row = document.getElementById('row-rid-' + s_id);
			
			<?php // Exists, so lets unselect it. ?>
			if(FluidVariables.v_r_selection.hasOwnProperty(s_id)) {
				delete FluidVariables.v_r_selection[s_id];
				
				f_row.style.fontStyle = "normal";
				f_row.style.backgroundColor = "transparent";

				<?php // Calculate the totals and insert / update the input.?>
				FluidVariables.v_r_total = parseFloat(FluidVariables.v_r_total) - parseFloat(document.getElementById('f-rv-div-' + s_id).getAttribute("data-total"));
				FluidVariables.v_r_total_tax = parseFloat(FluidVariables.v_r_total_tax) - parseFloat(document.getElementById('f-rv-div-' + s_id).getAttribute("data-tax"));
			}
			else {
				<?php // Lets select it. ?>
				FluidVariables.v_r_selection[s_id] = {"s_id" : s_id};

				f_row.style.backgroundColor = "<?php echo COLOUR_SELECTED_ITEMS; ?>";
				f_row.style.fontStyle = "italic";

				<?php // Calculate the totals and insert / update the input.?>
				FluidVariables.v_r_total = parseFloat(FluidVariables.v_r_total) + parseFloat(document.getElementById('f-rv-div-' + s_id).getAttribute("data-total"));
				FluidVariables.v_r_total_tax = parseFloat(FluidVariables.v_r_total_tax) + parseFloat(document.getElementById('f-rv-div-' + s_id).getAttribute("data-tax"));
			}
			
			<?php
			/*
			FluidVariables.v_r_total_complete = parseFloat(FluidVariables.v_r_total) + parseFloat(FluidVariables.v_r_total_tax);

			if(FluidVariables.v_r_total_complete < 0 || Object.keys(FluidVariables.v_r_selection).length < 1) {
				FluidVariables.v_r_total_complete = parseFloat(0.00);
				document.getElementById('f-refund-button').disabled = true;
			}
			else {
				document.getElementById('f-refund-button').disabled = false;
			}

			<?php // --> (Math.floor(FluidVariables.v_r_total * 100) / 100).toFixed(2); // FluidVariables.v_r_total.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0] ?>
			document.getElementById('rv-refund-input').value = FluidVariables.v_r_total_complete.toFixed(2) + " (estimated)";
			*/
			?>
			js_refund_calculate_price();
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_refund_manual_input() {
	try {
		var f_refund_input = parseFloat(document.getElementById('rv-refund-input').value);

		FluidVariables.v_r_manual_input = parseFloat(f_refund_input);
		
		FluidVariables.v_r_total = parseFloat(FluidVariables.v_r_manual_input);
		
		FluidVariables.v_r_total_complete = parseFloat(FluidVariables.v_r_total);

		if(FluidVariables.v_r_total_complete > 0) {
			document.getElementById('f-refund-button').disabled = false;
		}
		else {
			FluidVariables.v_r_total_complete = parseFloat(0.00);
			document.getElementById('f-refund-button').disabled = true;				
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Clear out serial temporary serial numbers ?>
function js_serial_clear() {
	try {
		for(var key in FluidVariables.s_serial)
			delete FluidVariables.s_serial[key];
	}
	catch(err) {
		js_debug_error(err);
	}
}	

function js_serial_modal(si_id, s_id, s_mode) {
	try {
		js_serial_clear();
		
		var FluidData = {};
			FluidData.si_id = si_id;
			FluidData.s_id = s_id;
			FluidData.s_mode = s_mode;

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_serial_modal&data=" + data}));
		
		js_modal_hide('#fluid-modal');
		js_fluid_ajax(data_obj);			
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_serial_save(si_id, s_id) {
	try {
		var FluidData = {};
			FluidData.si_id = si_id;
			FluidData.s_id = s_id;
			FluidData.s_number = document.getElementById('f_serial_input').value;

		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_serial_save&data=" + data}));

		js_modal_hide('#fluid-modal');		
		js_fluid_ajax(data_obj);	
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_serial_update(si_id, s_id) {
	try {
		var FluidData = {};
			FluidData.si_id = si_id;
			FluidData.s_id = s_id;
			FluidData.s_serials = FluidVariables.s_serial;
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_serial_update&data=" + data}));

		js_serial_clear();
		
		js_modal_hide('#fluid-modal');		
		js_fluid_ajax(data_obj);	
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Sends a email update to the user on there order status ?>
function js_email_order_status(s_id) {
	try {
		var FluidData = {};
			FluidData.s_id = s_id;
			FluidData.s_status = document.getElementById("s-id-status-" + s_id).options[document.getElementById("s-id-status-" + s_id).selectedIndex].value;
			FluidData.s_tracking = document.getElementById("s-tracking-" + s_id).value;
			FluidData.s_shipping_type = document.getElementById("s-shipping-type-" + s_id).value;
			FluidData.s_email = document.getElementById("s-email-" + s_id).value;
			FluidData.s_order_number = document.getElementById("s-order-number-" + s_id).value;
			FluidData.s_name = document.getElementById("s-name-" + s_id).value;
			FluidData.s_date = document.getElementById("s-date-" + s_id).value;
			FluidData.s_email_message = Base64.encode(document.getElementById("f-email-message").innerHTML);
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_fluid_email_order_status&data=" + data}));

		js_fluid_ajax(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Sends a email update to the user on there order status ?>
function js_email_order_status_confirm(s_id) {
	try {
		var FluidData = {};
			FluidData.s_id = s_id;
			FluidData.s_status = document.getElementById("s-id-status-" + s_id).options[document.getElementById("s-id-status-" + s_id).selectedIndex].value;
			FluidData.s_tracking = document.getElementById("s-tracking-" + s_id).value;
			FluidData.s_shipping_type = document.getElementById("s-shipping-type-" + s_id).value;
			FluidData.s_email = document.getElementById("s-email-" + s_id).value;
			FluidData.s_order_number = document.getElementById("s-order-number-" + s_id).value;
			FluidData.s_name = document.getElementById("s-name-" + s_id).value;
			FluidData.s_date = document.getElementById("s-date-" + s_id).value;
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ORDERS_ADMIN;?>", dataobj: "load=true&function=php_fluid_email_order_status_confirm&data=" + data}));

		js_fluid_ajax(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_order_email_editor(s_id, s_title) {
	try {
		var FluidData = {};
			FluidData.s_modal = "fluid-modal-overflow";
			FluidData.s_email = document.getElementById("s-email-" + s_id).value;
			FluidData.s_email_title = s_title;
			FluidData.s_email_message = Base64.encode(document.getElementById("f-email-message").innerHTML);
			
			console.log(FluidData.s_email);
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT_ADMIN;?>", dataobj: "load=true&function=php_load_email_creator&data=" + data}));
			
		js_fluid_ajax(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
