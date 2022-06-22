<?php
// fluid.pos.js.php
// Michael Rajotte - 2017 Octobre
// A point of sale for Fluid.

?>

<script>
FluidPOS = {};
FluidPOS.s_scan = [];
FluidPOS.s_scan_buffer = null;
FluidPOS.s_subtotal = 0;
FluidPOS.s_tax = 0;

<?php // Sets the scan data object. ?>
function js_pos_scan_data_set(data) {
	try {
		FluidPOS.s_scan = FluidPOS.s_scan.concat(data);
		
		console.log(FluidPOS);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_pos_update_totals() {
	try {
		var f_rows = document.getElementsByName('fluid-cart-editor-items');
		var f_subtotal = 0;

		for(var x=0; x < f_rows.length; x++) {
			f_subtotal = parseFloat(f_rows[x].getAttribute('data-price')) + parseFloat(f_subtotal);
		}
		
		FluidPOS.s_subtotal = f_subtotal;
		
		document.getElementById('f-pos-subtotal').innerHTML = "Subtotal: <i class='<?php echo HTML_CURRENCY_GLYPHICON; ?>'></i> " + parseFloat(FluidPOS.s_subtotal);
		document.getElementById('f-pos-tax').innerHTML = "Tax: <i class='<?php echo HTML_CURRENCY_GLYPHICON; ?>'></i> " + parseFloat(FluidPOS.s_tax);
		document.getElementById('f-pos-total').innerHTML = "Total: <i class='<?php echo HTML_CURRENCY_GLYPHICON; ?>'></i> " + parseFloat(FluidPOS.s_tax + FluidPOS.s_subtotal);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_pos_scan_init(data) {
	try {
		js_pos_scan_clear_buffer();

		$(document).off("keypress");
		
		$(document).on("keypress", function (e) {
			<?php // 13 enter. e.keyCode != 13 ?>

			<?php //if(e.keyCode != 13 && $('#' + data).is(":focus") == false) { ?>
			if(e.keyCode != 13) {
				if(FluidPOS.s_scan_buffer == null)
					FluidPOS.s_scan_buffer = e.key;
				else
					FluidPOS.s_scan_buffer += e.key;
			}

			if(e.keyCode == 13) {
				<?php // If enter was detected, lets go scan and find the item. ?>
				js_pos_scan_execute();
			}
		});
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_pos_scan_execute() {
	try {
		console.log(FluidPOS.s_scan_buffer);
		
		<?php
		/*
		if(FluidPOS.s_scan[FluidPOS.s_scan_buffer] != null) {
			js_pos_scan_scroll(FluidPOS.s_scan_buffer);

			js_pos_scan_increase_num(FluidPOS.s_scan[FluidPOS.s_scan_buffer]['p_id'], FluidPOS.s_scan[FluidPOS.s_scan_buffer]['p_mfgcode']);

			for(var key in FluidVariables.s_scan) {
				var s_element = document.getElementById('f-scan-row-' + key);

				if(s_element != undefined) {
					s_element.classList.remove("f-scan-animated");
					void s_element.offsetWidth;
				}
			}				

			var f_element = document.getElementById('f-pos-scan-row-' + FluidPOS.s_scan_buffer);
			<?php
			
				//f_element.className = f_element.className.replace( /(?:^|\s)f-scan-animated(?!\S)/g , '' );
				//f_element.className += "f-scan-animated";
			
			?>

			f_element.classList.remove("f-scan-animated");
			void f_element.offsetWidth;
			f_element.classList.add("f-scan-animated");
			
			js_pos_scan_clear_buffer();
		}
		else {
		*/
		?>
			$(document).off("keypress");

			<?php // Lets disable all key pressing ?>
			$(document).on("keypress", function (e) {
				e.stopPropagation();
			});

			var FluidData = {};
				FluidData.s_code = FluidPOS.s_scan_buffer;
				FluidData.s_scan = FluidVariables.s_scan;
				
			var data = Base64.encode(JSON.stringify(FluidData));

			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_POS;?>", dataobj: "load=true&function=php_pos_scan&data=" + data}));

			js_pos_scan_clear_buffer();
			
			js_fluid_ajax(data_obj);
		<?php //} ?>
	}
	catch(err) {
		js_pos_scan_init(); <?php // --> If scanning failed, reset. ?>
		js_debug_error(err);
	}
}

function js_pos_scan_clear_buffer() {
	try {
		FluidPOS.s_scan_buffer = null;
	}
	catch(err) {
		js_debug_error(err);
	}		
}

<?php // Reset the flashing of rows to no flashing. ?>
function js_pos_scan_reset_css() {
	try {
		var f_rows = document.getElementsByName('f-scan-row');
	
		for(var x=0; x < f_rows.length; x++)
			f_rows[x].classList.remove("f-scan-animated");
		
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Scroll to the last scanned item. ?>
function js_pos_scan_scroll(data) {
	try {
		if(document.getElementById(data) != null) {
			var topPos = document.getElementById(data).offsetTop;

			$('#f-stock-scroll-div').animate({
				scrollTop: topPos
			}, "slow");
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}	

function js_pos_scan_append(data) {
	try {
		document.getElementById(Base64.decode(data['parent'])).innerHTML += Base64.decode(data['innerHTML']);
		js_update_select_pickers(); // Update any select pickers in case any are in the new innerHTML data.
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // NOT USED YET. Decrease the quantity of a item in the cart. ?>
function js_pos_scan_decrease_num(p_id, p_mfgcode) {
	try {
		var element = $('#fluid-cart-editor-qty-' + p_id);
		var el_adj = $('#fluid-cart-editor-qty-adj-' + p_id);
		
		var v = element.val()-1;

		if(v >= element.attr('min')) {
			element.val(v);
			FluidPOS.s_scan[p_mfgcode]['p_stock_adj'] = v;
			FluidPOS.s_scan[p_mfgcode]['p_adj'] = FluidPOS.s_scan[p_mfgcode]['p_stock_adj'] - FluidPOS.s_scan[p_mfgcode]['p_stock']
			el_adj.val(FluidPOS.s_scan[p_mfgcode]['p_adj']);
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}
	
<?php // NOT USED YET. Increase the quantity of a item in the cart. ?>
function js_pos_scan_increase_num(p_id, p_mfgcode) {
	try {
		var element = $('#fluid-cart-editor-qty-' + p_id);
		var el_adj = $('#fluid-cart-editor-qty-adj-' + p_id);

		var v = element.val()*1+1;

		element.val(v);

		FluidPOS.s_scan[p_mfgcode]['p_stock_adj'] = v;
		FluidPOS.s_scan[p_mfgcode]['p_adj'] = FluidPOS.s_scan[p_mfgcode]['p_stock_adj'] - FluidPOS.s_scan[p_mfgcode]['p_stock']
		el_adj.val(FluidPOS.s_scan[p_mfgcode]['p_adj']);
	}
	catch(err) {
		js_debug_error(err);
	}
}	

<?php // NOT USED YET. Clear out temporary scan data. ?>
function js_pos_scan_clear(f_update) {
	try {
		for(var key in FluidPOS.s_scan) {
			<?php
			/*
			if(f_update == true) {
				if(document.getElementById('p_td_id_stock_' + FluidVariables.s_scan[key]['p_id']) != undefined)
					document.getElementById('p_td_id_stock_' + FluidVariables.s_scan[key]['p_id']).innerHTML = FluidVariables.s_scan[key]['p_stock_adj'];
			}
			*/
			?>
			delete FluidPOS.s_scan[key];
		}
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_pos_set_body_colour() {
	try {
		document.body.style.backgroundColor = "#565656";
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
