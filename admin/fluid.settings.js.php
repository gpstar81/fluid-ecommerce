<?php
// fluid.settings.js.php
// Michael Rajotte - 2017 Novembre
?>

<script>

<?php // Saves store settings. ?>
function js_fluid_save_settings() {
	try {
		var FluidData = {};
			FluidData.boxes = FluidVariables.b_boxes;
			FluidData.taxes = FluidVariables.t_taxes;

			FluidData.fs_moneris = document.getElementById("fs-moneris").options[document.getElementById("fs-moneris").selectedIndex].value;
			FluidData.f_moneris_api_key = document.getElementById("f-moneris-api-key").value;
			FluidData.f_moneris_api_key_sandbox = document.getElementById("f-moneris-api-key-sandbox").value;
			FluidData.f_moneris_store_id = document.getElementById("f-moneris-store-id").value;
			FluidData.f_moneris_store_id_sandbox = document.getElementById("f-moneris-store-id-sandbox").value;

			FluidData.fs_authorize = document.getElementById("fs-authorize").options[document.getElementById("fs-authorize").selectedIndex].value;
			FluidData.f_authorize_login_id = document.getElementById("f-authorize-login-id").value;
			FluidData.f_authorize_login_id_sandbox = document.getElementById("f-authorize-login-id-sandbox").value;
			FluidData.f_authorize_api_key = document.getElementById("f-authorize-api-key").value;
			FluidData.f_authorize_api_key_sandbox = document.getElementById("f-authorize-api-key-sandbox").value;

			FluidData.fs_paypal = document.getElementById("fs-paypal").options[document.getElementById("fs-paypal").selectedIndex].value;
			FluidData.f_paypal_client_id = document.getElementById("f-paypal-client-id").value;
			FluidData.f_paypal_client_id_sandbox = document.getElementById("f-paypal-client-id-sandbox").value;
			FluidData.f_paypal_secret = document.getElementById("f-paypal-secret").value;
			FluidData.f_paypal_secret_sandbox = document.getElementById("f-paypal-secret-sandbox").value;

			FluidData.store = document.getElementById("fs-status").options[document.getElementById("fs-status").selectedIndex].value;
			FluidData.f_closed_message = Base64.encode(document.getElementById("f-closed-message").value);
			FluidData.f_closed_message_old = document.getElementById("f-closed-message-old").value;

			FluidData.fs_store_message_enabled = document.getElementById("fs-store-message-enabled").options[document.getElementById("fs-store-message-enabled").selectedIndex].value;
			FluidData.fs_store_message = Base64.encode(document.getElementById("fs-store-message").value);

			FluidData.fs_store_message_enabled_modal = document.getElementById("fs-store-message-enabled-modal").options[document.getElementById("fs-store-message-enabled-modal").selectedIndex].value;
			FluidData.fs_store_message_modal = Base64.encode(document.getElementById("fs-store-message-modal").value);

			FluidData.item_status = document.getElementById("fs-item-status").options[document.getElementById("fs-item-status").selectedIndex].value;
			FluidData.f_checkout_sandbox = document.getElementById("fs-checkout-sandbox").options[document.getElementById("fs-checkout-sandbox").selectedIndex].value;
			FluidData.fs_hash_key = document.getElementById("fs-hash-key").value;
			FluidData.fs_currency_key = base64EncodingUTF8(document.getElementById("fs-currency-key").value);
			FluidData.fs_currency_icon = document.getElementById("fs-currency-icon").value;
			FluidData.fs_currency_code = document.getElementById("fs-currency-code").value;
			FluidData.fs_max_listings = document.getElementById("fs-max-listings").value;
			FluidData.fs_max_images = document.getElementById("fs-max-images").value;
			FluidData.fs_navbar_menu = document.getElementById("fs-navbar-menu").options[document.getElementById("fs-navbar-menu").selectedIndex].value;
			FluidData.fs_navbar_pinned = document.getElementById("fs-navbar-pinned").options[document.getElementById("fs-navbar-pinned").selectedIndex].value;
			FluidData.fs_filters_pinned = document.getElementById("fs-filters-pinned").options[document.getElementById("fs-filters-pinned").selectedIndex].value;
			FluidData.fs_navbar_pinned_mobile = document.getElementById("fs-navbar-pinned-mobile").options[document.getElementById("fs-navbar-pinned-mobile").selectedIndex].value;
			FluidData.fs_filters_pinned_mobile = document.getElementById("fs-filters-pinned-mobile").options[document.getElementById("fs-filters-pinned-mobile").selectedIndex].value;

			FluidData.fs_preorders = document.getElementById("fs-preorders").options[document.getElementById("fs-preorders").selectedIndex].value;
			FluidData.fs_purchase_out_of_stock = document.getElementById("fs-purchase-out-of-stock").options[document.getElementById("fs-purchase-out-of-stock").selectedIndex].value;

			FluidData.fs_deal_timer = document.getElementById("fs-deal-timer").options[document.getElementById("fs-deal-timer").selectedIndex].value;
			
			FluidData.fs_slogan_enabled = document.getElementById("fs-slogan-enabled").options[document.getElementById("fs-slogan-enabled").selectedIndex].value;
			FluidData.fs_slogan = document.getElementById("fs-slogan").value;
			FluidData.fs_debug_enabled = document.getElementById("fs-debug-enabled").options[document.getElementById("fs-debug-enabled").selectedIndex].value;
			FluidData.fs_debug_log = document.getElementById("fs-debug-log").value;

			FluidData.fs_free_shipping = document.getElementById("fs-free-shipping").options[document.getElementById("fs-free-shipping").selectedIndex].value;
			FluidData.fs_free_shipping_oversized = document.getElementById("fs-free-shipping-oversized").options[document.getElementById("fs-free-shipping-oversized").selectedIndex].value;
			FluidData.fs_free_shipping_special = document.getElementById("fs-free-shipping-special").options[document.getElementById("fs-free-shipping-special").selectedIndex].value;
			FluidData.fs_free_shipping_stock = document.getElementById("fs-free-shipping-stock").options[document.getElementById("fs-free-shipping-stock").selectedIndex].value;

			FluidData.fs_shipping_non_billing = document.getElementById("fs-shipping-non-billing").options[document.getElementById("fs-shipping-non-billing").selectedIndex].value;
			FluidData.fs_instore_pickup = document.getElementById("fs-instore-pickup").options[document.getElementById("fs-instore-pickup").selectedIndex].value;
			FluidData.fs_instore_pickup_payment = document.getElementById("fs-instore-pickup-payment").options[document.getElementById("fs-instore-pickup-payment").selectedIndex].value;

			FluidData.fs_postal_code = document.getElementById("fs-postal-code").value;
			FluidData.fs_split_shipping = document.getElementById("fs-split-shipping").options[document.getElementById("fs-split-shipping").selectedIndex].value;

			FluidData.fs_canadapost = document.getElementById("fs-canadapost").options[document.getElementById("fs-canadapost").selectedIndex].value;
			FluidData.fs_canadapost_signature = document.getElementById("fs-canadapost-signature").options[document.getElementById("fs-canadapost-signature").selectedIndex].value;
			FluidData.fs_canadapost_username = document.getElementById("fs-canadapost-username").value;
			FluidData.fs_canadapost_password = document.getElementById("fs-canadapost-password").value;
			FluidData.fs_canadapost_customer_number = document.getElementById("fs-canadapost-customer-number").value;

			FluidData.fs_fedex = document.getElementById("fs-fedex").options[document.getElementById("fs-fedex").selectedIndex].value;
			FluidData.fs_fedex_signature = document.getElementById("fs-fedex-signature").options[document.getElementById("fs-fedex-signature").selectedIndex].value;
			FluidData.fs_fedex_account = document.getElementById("fs-fedex-account").value;
			FluidData.fs_fedex_meter = document.getElementById("fs-fedex-meter").value;
			FluidData.fs_fedex_key = document.getElementById("fs-fedex-key").value;
			FluidData.fs_fedex_password = document.getElementById("fs-fedex-password").value;
			FluidData.fs_fedex_person = document.getElementById("fs-fedex-person").value;
			FluidData.fs_fedex_company = document.getElementById("fs-fedex-company").value;
			FluidData.fs_fedex_phone = document.getElementById("fs-fedex-phone").value;
			FluidData.fs_fedex_street = document.getElementById("fs-fedex-street").value;
			FluidData.fs_fedex_city = document.getElementById("fs-fedex-city").value;
			FluidData.fs_fedex_province = document.getElementById("fs-fedex-province").value;
			FluidData.fs_fedex_postalcode = document.getElementById("fs-fedex-postalcode").value;
			FluidData.fs_fedex_country = document.getElementById("fs-fedex-country").value;

			FluidData.fs_margin_1 = document.getElementById("fs-margin-1").options[document.getElementById("fs-margin-1").selectedIndex].value;
			FluidData.fs_margin_2 = document.getElementById("fs-margin-2").options[document.getElementById("fs-margin-2").selectedIndex].value;
			FluidData.fs_margin_3 = document.getElementById("fs-margin-3").options[document.getElementById("fs-margin-3").selectedIndex].value;
			FluidData.fs_margin_4 = document.getElementById("fs-margin-4").options[document.getElementById("fs-margin-4").selectedIndex].value;
			FluidData.fs_margin_5 = document.getElementById("fs-margin-5").options[document.getElementById("fs-margin-5").selectedIndex].value;
			FluidData.fs_value_1 = document.getElementById("fs-value-1").options[document.getElementById("fs-value-1").selectedIndex].value;
			FluidData.fs_value_2 = document.getElementById("fs-value-2").options[document.getElementById("fs-value-2").selectedIndex].value;
			FluidData.fs_value_3 = document.getElementById("fs-value-3").options[document.getElementById("fs-value-3").selectedIndex].value;
			FluidData.fs_value_4 = document.getElementById("fs-value-4").options[document.getElementById("fs-value-4").selectedIndex].value;
			FluidData.fs_value_5 = document.getElementById("fs-value-5").options[document.getElementById("fs-value-5").selectedIndex].value;

			FluidData.fs_slider_trending = document.getElementById("fs-slider-trending").options[document.getElementById("fs-slider-trending").selectedIndex].value;
			FluidData.fs_slider_trending_text = Base64.encode(document.getElementById("fs-slider-trending-text").value);
			FluidData.fs_slider_deal = document.getElementById("fs-slider-deal").options[document.getElementById("fs-slider-deal").selectedIndex].value;
			FluidData.fs_slider_deal_text = Base64.encode(document.getElementById("fs-slider-deal-text").value);
			FluidData.fs_slider_deal_button = Base64.encode(document.getElementById("fs-slider-deal-button").value);
			FluidData.fs_slider_formula = document.getElementById("fs-slider-formula").options[document.getElementById("fs-slider-formula").selectedIndex].value;
			FluidData.fs_slider_formula_text = Base64.encode(document.getElementById("fs-slider-formula-text").value);
			FluidData.fs_slider_formula_button = Base64.encode(document.getElementById("fs-slider-formula-button").value);
			FluidData.fs_slider_blackfriday = document.getElementById("fs-slider-blackfriday").options[document.getElementById("fs-slider-blackfriday").selectedIndex].value;
			FluidData.fs_slider_blackfriday_text = Base64.encode(document.getElementById("fs-slider-blackfriday-text").value);
			FluidData.fs_slider_blackfriday_button = Base64.encode(document.getElementById("fs-slider-blackfriday-button").value);

			FluidData.fs_category_position = document.getElementById("fs-category-position").options[document.getElementById("fs-category-position").selectedIndex].value;

			FluidData.fs_banners_enabled = document.getElementById("fs-banners-enabled").options[document.getElementById("fs-banners-enabled").selectedIndex].value;
			FluidData.fs_categories_enabled = document.getElementById("fs-categories-enabled").options[document.getElementById("fs-categories-enabled").selectedIndex].value;
			FluidData.fs_infinite_enabled = document.getElementById("fs-infinite-enabled").options[document.getElementById("fs-infinite-enabled").selectedIndex].value;

			FluidData.fs_search_relevance = document.getElementById("fs-search-relevance").value;

			FluidData.fs_feedback_enabled = document.getElementById("fs-feedback-enabled").options[document.getElementById("fs-feedback-enabled").selectedIndex].value;
			FluidData.fs_feedback_timer = document.getElementById("fs-feedback-timer").value;

			FluidData.fs_additional_savings_merge = document.getElementById("fs-additional-savings-merge").options[document.getElementById("fs-additional-savings-merge").selectedIndex].value;

			FluidData.fs_sms_status = document.getElementById("fs-sms-status").options[document.getElementById("fs-sms-status").selectedIndex].value;
			FluidData.fs_sms_sid = document.getElementById("fs-sms-sid").value;
			FluidData.fs_sms_token = document.getElementById("fs-sms-token").value;
			FluidData.fs_sms_number = document.getElementById("fs-sms-number").value;

			FluidData.fs_menu_alt_768 = document.getElementById("fs-menu-alt-768").value;
			FluidData.fs_menu_alt_992 = document.getElementById("fs-menu-alt-992").value;
			FluidData.fs_menu_alt_1200 = document.getElementById("fs-menu-alt-1200").value;
			FluidData.fs_menu_alt_1600 = document.getElementById("fs-menu-alt-1600").value;

			var c_filters = document.getElementById('fs-shipping-provinces-exclusion');
			var c_filters_obj = {};

			for (var i = 0; i < c_filters.length; i++) {
				if(c_filters.options[i].selected) {
					c_filters_obj[i] = c_filters.options[i].value;
				}
			}
			FluidData.fs_p_exclusions = c_filters_obj;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SETTINGS_ADMIN;?>", dataobj: "load=true&function=php_fluid_save_set&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_barcode_generate() {
	try {
		var FluidData = {};
			FluidData.f_barcode_type = document.getElementById("f_barcode_type").options[document.getElementById("f_barcode_type").selectedIndex].value
			FluidData.f_barcode_width = document.getElementById("f_barcode_width").value;
			FluidData.f_barcode_height = document.getElementById("f_barcode_height").value;
			FluidData.f_text_spacing = document.getElementById("f_text_spacing").value;
			FluidData.f_font_size = document.getElementById("f_font_size").value;
			FluidData.f_font_weight = document.getElementById("f_font_weight").value;
			FluidData.f_barcode = document.getElementById("f_barcode_enter").value;

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SETTINGS_ADMIN;?>", dataobj: "load=true&function=php_generate_barcode_html&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_sitemap_generate() {
	try {
		var FluidData = {};

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SETTINGS_ADMIN;?>", dataobj: "load=true&function=php_generate_sitemap&data=" + data}));

		js_fluid_ajax_no_timeout(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_rebuild_livesearch_cache() {
	try {
		var FluidData = {};

		var data = Base64.encode(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SETTINGS_ADMIN;?>", dataobj: "load=true&function=php_rebuild_livesearch_cache&data=" + data}));

		js_fluid_ajax_no_timeout(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Append to the box table ?>
function js_fluid_box_create() {
	try {
		var i_key = 0;
		for(var key in FluidVariables.b_boxes) {
			i_key = key;
		}

		i_key++;

		var f_confirm_message_delete = Base64.encode("<div class='alert alert-danger' role='alert'>Remove this shipping box?</div>" + document.getElementById('f-box-name').value);
		var f_confirm_footer = Base64.encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='document.getElementById(\"f-box-tbody\").removeChild(document.getElementById(\"b-box-" + i_key + "\")); delete FluidVariables.b_boxes[\"" + i_key + "\"]; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

		var f_modal = "<?php echo base64_encode("#fluid-modal"); ?>";

		FluidVariables.b_boxes[i_key] = {};
		FluidVariables.b_boxes[i_key]['b_empty_weight'] = document.getElementById('f-box-empty-weight').value;
		FluidVariables.b_boxes[i_key]['b_id'] = i_key;
		FluidVariables.b_boxes[i_key]['b_inner_depth'] = document.getElementById('f-box-inner-depth').value;
		FluidVariables.b_boxes[i_key]['b_inner_length'] = document.getElementById('f-box-inner-length').value;
		FluidVariables.b_boxes[i_key]['b_inner_width'] = document.getElementById('f-box-inner-width').value;
		FluidVariables.b_boxes[i_key]['b_max_weight'] = document.getElementById('f-box-max-weight').value;
		FluidVariables.b_boxes[i_key]['b_name'] = document.getElementById('f-box-name').value;
		FluidVariables.b_boxes[i_key]['b_outer_depth'] = document.getElementById('f-box-outer-depth').value;
		FluidVariables.b_boxes[i_key]['b_outer_length'] = document.getElementById('f-box-outer-length').value;
		FluidVariables.b_boxes[i_key]['b_outer_width'] = document.getElementById('f-box-outer-width').value;

		$("#f-ship-box-table").find('tbody')
			.append($("<tr id='b-box-" + i_key + "'>")
				.append($("<td id='b-trash-" + i_key + "' class='f-align-middle-td'>")

					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-name').value)
					)

				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-outer-width').value)
					)

				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-outer-length').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-outer-depth').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-empty-weight').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-inner-width').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-inner-length').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-inner-depth').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-box-max-weight').value)
					)
			);

		document.getElementById('b-trash-' + i_key).innerHTML = "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='js_modal_confirm(Base64.decode(\"" + f_modal + "\"), Base64.decode(\"" + f_confirm_message_delete + "\"), Base64.decode(\"" + f_confirm_footer + "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";

		js_modal_show("#fluid-modal");
	}
	catch(err){
		js_debug_error(err);
	}
}

<?php // Clears out the input boxes for box creation. ?>
function js_clear_box_input() {
	try {
		document.getElementById('f-box-name').value = "";
		document.getElementById('f-box-outer-width').value = "";
		document.getElementById('f-box-outer-length').value = "";
		document.getElementById('f-box-outer-depth').value = "";
		document.getElementById('f-box-empty-weight').value = "";
		document.getElementById('f-box-inner-width').value = "";
		document.getElementById('f-box-inner-length').value = "";
		document.getElementById('f-box-inner-depth').value = "";
		document.getElementById('f-box-max-weight').value = "";
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Saves the shipping boxes information to editing. ?>
function js_fluid_boxes_data_set(data) {
	try {
		FluidVariables.b_boxes = data;
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Append to the tax table ?>
function js_fluid_tax_create() {
	try {
		var i_key = 0;
		for(var key in FluidVariables.t_boxes) {
			i_key = key;
		}

		i_key++;

		var f_confirm_message_delete = Base64.encode("<div class='alert alert-danger' role='alert'>Remove this tax?</div>" + document.getElementById('f-tax-name').value);
		var f_confirm_footer = Base64.encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='document.getElementById(\"f-tax-tbody\").removeChild(document.getElementById(\"t-tax-" + i_key + "\")); delete FluidVariables.t_taxes[\"" + i_key + "\"]; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

		var f_modal = "<?php echo base64_encode("#fluid-modal"); ?>";

		FluidVariables.t_taxes[i_key] = {};
		FluidVariables.t_taxes[i_key]['t_id'] = i_key;
		FluidVariables.t_taxes[i_key]['t_name'] = document.getElementById('f-tax-name').value;
		FluidVariables.t_taxes[i_key]['t_region'] = document.getElementById('f-tax-region').value;
		FluidVariables.t_taxes[i_key]['t_country'] = document.getElementById('f-tax-country').value;
		FluidVariables.t_taxes[i_key]['t_math'] = document.getElementById('f-tax-math').value;

		$("#f-tax-table").find('tbody')
			.append($("<tr id='t-tax-" + i_key + "'>")
				.append($("<td id='t-trash-" + i_key + "' class='f-align-middle-td'>")

					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-tax-name').value)
					)

				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-tax-region').value)
					)

				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-tax-country').value)
					)
				.append($("<td class='f-align-middle-td'>")
						.text(document.getElementById('f-tax-math').value)
					)
			);

		document.getElementById('t-trash-' + i_key).innerHTML = "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='js_modal_confirm(Base64.decode(\"" + f_modal + "\"), Base64.decode(\"" + f_confirm_message_delete + "\"), Base64.decode(\"" + f_confirm_footer + "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";

		js_modal_show("#fluid-modal");
	}
	catch(err){
		js_debug_error(err);
	}
}

<?php // Clears out the input boxes for tax creation. ?>
function js_clear_tax_input() {
	try {
		document.getElementById('f-tax-name').value = "";
		document.getElementById('f-tax-region').value = "";
		document.getElementById('f-tax-country').value = "";
		document.getElementById('f-tax-math').value = "";
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // Saves the taxes information to editing. ?>
function js_fluid_taxes_data_set(data) {
	try {
		FluidVariables.t_taxes = data;
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
