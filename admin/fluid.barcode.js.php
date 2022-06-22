<?php
// fluid.barcode.js.php
// Michael Rajotte - 2018 Mars
?>

<script>
var FluidBarcodeCounter = 0;
var FluidBarcodeStock = null;

function js_fluid_barcode_modal() {
	try {
		var FluidData = {};
			FluidData.f_selection = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));

		FluidBarcodeCounter = 0;
		FluidBarcodeStock = null;
		
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BARCODE_ADMIN;?>", dataobj: "load=true&function=php_barcode_modal&data=" + data}));
				
		js_fluid_ajax_fdom(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode() {
	try {
		js_loading_start();
		
		var FluidData = {};
			FluidData.f_selection = {};
		
		var data_id = document.getElementsByName('f-hidden-barcode-counter');
		
		var b_found = false;
		
		<?php // Run through all the sub filters and enable them for sorting. ?>
		for(var x=0; x < data_id.length; x++) {
			FluidData.f_selection[x] = {};
			FluidData.f_selection[x]['x_id'] = data_id[x].value;
			FluidData.f_selection[x]['x_sort'] = x;
			FluidData.f_selection[x]['p_id'] = document.getElementById('f-hidden-barcode-id_' + data_id[x].value).value;
			FluidData.f_selection[x]['p_mfgcode'] = document.getElementById('f-hidden-barcode-code_' + data_id[x].value).value;
			FluidData.f_selection[x]['p_qty'] = document.getElementById('f-barcode-qty_' + data_id[x].value).value;
			FluidData.f_selection[x]['p_copies'] = document.getElementById('f-barcode-copies_' + data_id[x].value).value;
			
			var f_serials = document.getElementById('f-barcode-serials_' + data_id[x].value);
			FluidData.f_selection[x].s_names = {};
			FluidData.f_selection[x].s_serials = {};
			
			if(f_serials != null) {
				var data_names = document.getElementsByName('f-hidden-barcode-names_' + data_id[x].value);
				
				for(var i=0; i < data_names.length; i++) {
					FluidData.f_selection[x].s_names[i] = data_names[i].value;
				}
				
				var data_serials = document.getElementsByName('f-hidden-barcode-serials_' + data_id[x].value);
				
				for(var i=0; i < data_serials.length; i++) {
					FluidData.f_selection[x].s_serials[i] = data_serials[i].value;
				}
			}
					
			b_found = true;
		}
		
		js_loading_stop();

		if(b_found == true) {
			<?php //js_modal_hide('#fluid-modal'); ?>
			
			var form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", "<?php echo FLUID_BARCODE_ADMIN; ?>?load=true&function=php_fluid_barcode");
			form.setAttribute("target", "formresult");
			var hiddenField = document.createElement("input");

			hiddenField.setAttribute("name", "data");
			hiddenField.setAttribute("value", base64EncodingUTF8(JSON.stringify(FluidData.f_selection)));
			form.appendChild(hiddenField);
			
			form.style.display = "none";
			document.body.appendChild(form);
			
			form.submit();

			$(form).remove();
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets all copies to it's current stock levels ?>
function js_fluid_barcode_stock_match_all() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(document.getElementById('f-barcode-copies_' + i) != null) {
					document.getElementById('f-barcode-copies_' + i).value = FluidBarcodeStock[i];
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets the number of copies of only those in stock to match it's stock ?>
function js_fluid_barcode_stock_match_instock() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(FluidBarcodeStock[i] > 0) {
					if(document.getElementById('f-barcode-copies_' + i) != null) {
						document.getElementById('f-barcode-copies_' + i).value = FluidBarcodeStock[i];
					}
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Resets all back to default of 1 ?>
function js_fluid_barcode_stock_match_reset_all() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(document.getElementById('f-barcode-copies_' + i) != null) {
					document.getElementById('f-barcode-copies_' + i).value = 1;
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_stock_data(data) {
	try {
		FluidBarcodeStock = data;
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_set_counter(data) {
	try {
		FluidBarcodeCounter = parseInt(data);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets the qty to match the stock level ?>
function js_fluid_barcode_qty_match_all() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(document.getElementById('f-barcode-qty_' + i) != null) {
					document.getElementById('f-barcode-qty_' + i).value = FluidBarcodeStock[i];
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Sets the qty of only those in stock to match it's stock ?>
function js_fluid_barcode_qty_match_instock() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(FluidBarcodeStock[i] > 0) {
					if(document.getElementById('f-barcode-qty_' + i) != null) {
						document.getElementById('f-barcode-qty_' + i).value = FluidBarcodeStock[i];
					}
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Resets all qty back to default of 1 ?>
function js_fluid_barcode_qty_match_reset_all() {
	try {
		var i;
		
		js_loading_start();
		
		for(i = 0; i < FluidBarcodeStock.length; i++) {
			if(FluidBarcodeStock[i] != null) {
				if(document.getElementById('f-barcode-qty_' + i) != null) {
					document.getElementById('f-barcode-qty_' + i).value = "";
				}
			}
		}
		
		js_loading_stop();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_copy(f_node) {
	try {
		var f_clone = f_node.parentNode.parentNode.cloneNode(true);
		var oldId = null;
		
		$(f_clone).find("*[id]").each(function(){
			var tID = $(this).attr("id");
			
			var idArray = tID.split("_");
			var idArrayLength = idArray.length;
			oldId = idArray[idArrayLength-1];
			
			var newId = tID.replace(idArray[idArrayLength-1], FluidBarcodeCounter);
			$(this).attr('id', newId);
			
			if(idArray[0] == "f-hidden-barcode-counter") {
				$(this).val(FluidBarcodeCounter);
			}
			
			if(idArray[0] == "f-hidden-barcode-names" || idArray[0] == "f-hidden-barcode-serials") {
				var tIDName = $(this).attr("name");
			
				var idArrayName = tIDName.split("_");
				var idArrayLengthName = idArrayName.length;
				var newIdName = tIDName.replace(idArrayName[idArrayLengthName-1], FluidBarcodeCounter);
				$(this).attr('name', newIdName);	
			}
		});
        
		<?php // Copy over the stock data ?>
		if(oldId != null) {
			FluidBarcodeStock[FluidBarcodeCounter] = FluidBarcodeStock[oldId];
		}
		
        FluidBarcodeCounter = parseInt(FluidBarcodeCounter) + 1;

        var referenceNode = f_node.parentNode.parentNode;
        referenceNode.parentNode.insertBefore(f_clone, referenceNode.nextSibling);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_remove(f_node) {
	try {
		var f_clone = f_node.parentNode.parentNode.cloneNode(true);
		
		$(f_clone).find("*[id]").each(function(){
			var tID = $(this).attr("id");
			
			var idArray = tID.split("_");
			var idArrayLength = idArray.length;
			var oldId = idArray[idArrayLength-1];
			
			if(FluidBarcodeStock[oldId] != null) {
				<?php //delete FluidBarcodeStock[oldId]; ?>
				FluidBarcodeStock[oldId] = null;
			}
			
			<?php // Break the loop ?>
			return false;
		});
		
		f_node.parentNode.parentNode.parentNode.removeChild(f_node.parentNode.parentNode);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_serial_add(f_node) {
	try {
		var f_clone = f_node.parentNode.parentNode.cloneNode(true);
		var s_id = null;
		$(f_clone).find("*[id]").each(function(){
			var tID = $(this).attr("id");
			
			var idArray = tID.split("_");
			var idArrayLength = idArray.length;
			s_id = idArray[idArrayLength-1];
						
			if(s_id != null)
				return false;
		});
		
		var FluidData = {};
			FluidData.s_id = s_id;
			FluidData.s_mode = "create";
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BARCODE_ADMIN;?>", dataobj: "load=true&function=php_barcode_serial_modal&data=" + data}));
		
		js_fluid_ajax(data_obj);			
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_barcode_serial_save(s_id) {
	try {
		js_loading_start();
		
		var s_name = document.getElementById('f-barcode-name-input').value;
		var s_serial = document.getElementById('f-barcode-serial-input').value;
		
		if(s_name != null && s_serial != null) {
			if(s_name != "" && s_serial != "") {
				var s_innerhtml = document.getElementById('f-barcode-serials_' + s_id);
				
				if(s_innerhtml != null)
					s_innerhtml.innerHTML += "<div>" + s_name + " - " + s_serial + "<input id='f-hidden-barcode-names_" + s_id + "' name='f-hidden-barcode-names_" + s_id + "' style='display: none;' type='text' value='" + s_name + "'/><input id='f-hidden-barcode-serials_" + s_id + "' name='f-hidden-barcode-serials_" + s_id + "' style='display: none;' type='text' value='" + s_serial + "'/></div>";
			}
		}
		
		js_loading_stop();
		js_modal_hide("#fluid-modal-overflow");
		document.getElementById('fluid-modal-overflow').innerHTMl = "";
		js_modal_show("#fluid-modal");
	}
	catch(err) {
		js_loading_stop();
		js_debug_error(err);
	}
}

function js_fluid_barcode_serial_update(s_id) {
	try {
		js_loading_start();

		var data_names = document.getElementsByName('f-barcode-serial-edit-name');
		var data_serials = document.getElementsByName('f-barcode-serial-edit-serial');
		var s_names = {};
		var s_serials = {};
			
		for(var x=0; x < data_names.length; x++)
			s_names[x] = data_names[x].value;
			
		for(var x=0; x < data_serials.length; x++)
			s_serials[x] = data_serials[x].value;
		
		if(s_names != null && s_serials != null) {
			var s_innerhtml = document.getElementById('f-barcode-serials_' + s_id);
			
			if(s_innerhtml != null) {
				var f_html = "";
				
				for(var key in s_names) {
					f_html += "<div>" + s_names[key] + " - " + s_serials[key] + "<input id='f-hidden-barcode-names_" + s_id + "' name='f-hidden-barcode-names_" + s_id + "' style='display: none;' type='text' value='" + s_names[key] + "'/><input id='f-hidden-barcode-serials_" + s_id + "' name='f-hidden-barcode-serials_" + s_id + "' style='display: none;' type='text' value='" + s_serials[key] + "'/></div>";	
				}
				
				s_innerhtml.innerHTML = f_html;
			}
		}
		
		js_loading_stop();
		js_modal_hide("#fluid-modal-overflow");
		document.getElementById('fluid-modal-overflow').innerHTMl = "";
		js_modal_show("#fluid-modal");		
	}
	catch(err) {
		js_loading_stop();
		js_debug_error(err);
	}
}

function js_fluid_barcode_serial_editor(f_node) {
	try {
		var f_clone = f_node.parentNode.parentNode.cloneNode(true);
		var s_id = null;
		$(f_clone).find("*[id]").each(function(){
			var tID = $(this).attr("id");
			
			var idArray = tID.split("_");
			var idArrayLength = idArray.length;
			s_id = idArray[idArrayLength-1];
						
			if(s_id != null)
				return false;
		});
		
		var f_serials = document.getElementById('f-barcode-serials_' + s_id);
		var s_names = {};
		var s_serials = {};
		
		if(f_serials != null) {
			var data_names = document.getElementsByName('f-hidden-barcode-names_' + s_id);
			
			for(var x=0; x < data_names.length; x++)
				s_names[x] = data_names[x].value;
			
			var data_serials = document.getElementsByName('f-hidden-barcode-serials_' + s_id);
			
			for(var x=0; x < data_serials.length; x++)
				s_serials[x] = data_serials[x].value;
		}
		
		
		var FluidData = {};
			FluidData.s_id = s_id;
			FluidData.s_mode = "edit";
			FluidData.s_selection = {};
				FluidData.s_selection.s_names = s_names;
				FluidData.s_selection.s_serials = s_serials;
			
		var data = Base64.encode(JSON.stringify(FluidData));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_BARCODE_ADMIN;?>", dataobj: "load=true&function=php_barcode_serial_modal&data=" + data}));
		
		js_fluid_ajax(data_obj);	
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
