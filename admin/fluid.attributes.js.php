<?php
// fluid.attributes.js.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.
?>

<script>

// Makes quick changes to items via the set attribute system.
function js_set_attribute(mode) {
	var attrib_id = parseInt(document.getElementById("attribute-select").options[document.getElementById("attribute-select").selectedIndex].value);
	var data = Base64.encode(JSON.stringify(FluidVariables.v_selection.p_selection));
	var data_send = {};
		data_send.mode = {};
		data_send.data = {};
		data_send.type = {};

	switch (attrib_id) {
		case 0:
			data_send.mode = Base64.encode("p_enable");
			data_send.data = Base64.encode(document.getElementById("product-status").options[document.getElementById("product-status").selectedIndex].value);
			data_send.type = "enable";
			break;
		case 1:
			data_send.mode = Base64.encode("p_stock");
			data_send.data = Base64.encode(document.getElementById("product-stock").value);
			data_send.type = "stock";
			break;
		case 2:
			data_send.mode = Base64.encode("p_price");
			data_send.data = Base64.encode(document.getElementById("product-price").value);
			data_send.type = "price";
			break;
		case 3:
			data_send.mode = Base64.encode("p_price_discount");
			data_send.data = Base64.encode(document.getElementById("product-price-discount").value);
			data_send.type = "price_discount";
			break;
		case 4:
			data_send.mode = Base64.encode("p_discount_date_end");
			data_send.data = Base64.encode(document.getElementById("product-discount-price-end-date").value);
			data_send.type = "discount_date_end";
			break;
		case 5:
			data_send.mode = Base64.encode("p_newarrivalenddate");
			data_send.data = Base64.encode(document.getElementById("product-arrival-end-date").value);
			data_send.type = "new_arrival_end_date";
			break;
		case 6:
			data_send.mode = Base64.encode("p_buyqty");
			data_send.data = Base64.encode(document.getElementById("product-buyqty").value);
			data_send.type = "buyqty";
			break;
		case 7:
			data_send.mode = Base64.encode("p_length");
			data_send.data = Base64.encode(document.getElementById("product-length").value);
			data_send.type = "length";
			break;
		case 8:
			data_send.mode = Base64.encode("p_width");
			data_send.data = Base64.encode(document.getElementById("product-width").value);
			data_send.type = "width";
			break;
		case 9:
			data_send.mode = Base64.encode("p_height");
			data_send.data = Base64.encode(document.getElementById("product-height").value);
			data_send.type = "height";
			break;
		case 10:
			data_send.mode = Base64.encode("p_weight");
			data_send.data = Base64.encode(document.getElementById("product-weight-attrib").value);
			data_send.type = "weight";
			break
		case 11:
			data_send.mode = Base64.encode("p_trending");
			data_send.data = Base64.encode(document.getElementById("product-trending").value);
			data_send.type = "trending";
			break;
		case 12:
			data_send.mode = Base64.encode("p_cost_real");
			data_send.data = Base64.encode(document.getElementById("product-cost").value);
			data_send.type = "cost";
			break;
		case 13:
			data_send.mode = Base64.encode("p_discount_date_start");
			data_send.data = Base64.encode(document.getElementById("product-discount-price-start-date").value);

			data_send.type = "discount_date_start";
			break;
		case 14:
			data_send.mode = Base64.encode("p_preorder");
			data_send.data = Base64.encode(document.getElementById("product-preorder").value);
			data_send.type = "preorder";
			break;
		case 15:
			data_send.mode = Base64.encode("p_rebate_claim");
			data_send.data = Base64.encode(document.getElementById("product-rebate-claim").value);
			data_send.type = "rebate_claim";
			break;
		case 16:
			data_send.mode = Base64.encode("p_showalways");
			data_send.data = Base64.encode(document.getElementById("product-alwaysshow").value);
			data_send.type = "showalways";
			break;
		case 17:
			data_send.mode = Base64.encode("p_namenum");
			data_send.data = Base64.encode(document.getElementById("product-namenum").value);
			data_send.type = "namenum";
			break;
		case 18:
			data_send.mode = Base64.encode("p_price");
			data_send.data = Base64.encode(document.getElementById("product-floor-plus").value);
			data_send.type = "floor_plus";
			break;
		case 19:
			data_send.mode = Base64.encode("p_price");
			data_send.data = Base64.encode(document.getElementById("product-floor-minus").value);
			data_send.type = "floor_minus";
			break;
		case 20:
			data_send.mode = Base64.encode("p_keywords");
			data_send.data = Base64.encode(document.getElementById("product-keywords").value);
			data_send.type = "keywords";
			break;
		case 21:
			data_send.mode = Base64.encode("p_keywords");
			data_send.data = Base64.encode(document.getElementById("product-keywords-create").options[document.getElementById("product-keywords-create").selectedIndex].value);
			data_send.type = "product_keywords_create";
			break;
		case 22:
			data_send.mode = Base64.encode("");
			data_send.data = Base64.encode(document.getElementById("product-namenum-merge").options[document.getElementById("product-namenum-merge").selectedIndex].value);
			data_send.type = "namenum-merge";
			break;
		case 23:
			data_send.mode = Base64.encode("p_stock_end");
			data_send.data = Base64.encode(document.getElementById("product-stock-end").options[document.getElementById("product-stock-end").selectedIndex].value);
			data_send.type = "p_stock_end";
			break;
		case 24:
			data_send.mode = Base64.encode("p_cost");
			data_send.data = Base64.encode(document.getElementById("product-cost-reset").options[document.getElementById("product-cost-reset").selectedIndex].value);
			data_send.type = "p_cost_reset";
			break;
		case 25:
			data_send.mode = Base64.encode("p_instore");
			data_send.data = Base64.encode(document.getElementById("product-instore").value);
			data_send.type = "p_instore";
			break;
		case 26:
			data_send.mode = Base64.encode("p_rental");
			data_send.data = Base64.encode(document.getElementById("product-rental").value);
			data_send.type = "rental";
			break;
		case 27:
			data_send.mode = Base64.encode("p_special_order");
			data_send.data = Base64.encode(document.getElementById("product-special-order").value);
			data_send.type = "special_order";
			break;
		case 28:
			data_send.mode = Base64.encode("p_zero_status");
			data_send.data = Base64.encode(document.getElementById("product-zero-status").options[document.getElementById("product-zero-status").selectedIndex].value);
			data_send.type = "p_zero_status";
			break;
		case 29:
			data_send.mode = Base64.encode("p_freeship");
			data_send.data = Base64.encode(document.getElementById("product-freeship").options[document.getElementById("product-freeship").selectedIndex].value);
			data_send.type = "p_freeship";
			break;
		case 30:
			data_send.mode = Base64.encode("p_arrivaltype");
			data_send.data = Base64.encode(document.getElementById("product-arrivaltype").value);
			data_send.type = "p_arrivaltype";
			break;
		case 31:
			data_send.mode = Base64.encode("p_keywords");
			data_send.data = Base64.encode(document.getElementById("product-keywords-merge").value);
			data_send.type = "product_keywords_merge";
			break;		
		case 32:
			data_send.mode = Base64.encode("p_component");
			data_send.data = Base64.encode(document.getElementById("product-component").value);
			data_send.type = "component";
			break;																					
	}

	data_send.f_search_input = FluidVariables.f_search_input;
	var data_send_obj = Base64.encode(JSON.stringify(data_send));

	var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ATTRIBUTES_ADMIN;?>", dataobj: "load=true&function=php_set_attribute&data=" + data + "&data_send_obj=" + data_send_obj + "&page_num=" + FluidVariables.f_page_num + "&mode=" + mode}));
	
	js_fluid_ajax(data_obj);
}

</script>
