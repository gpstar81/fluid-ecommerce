<?php
// fluid.cart.php
// Michael Rajotte - 2016 Novembre
// The checkout flow starts at the following: php_man_fluid_cart() -> php_fluid_checkout_load()

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

// Add a item to the cart.
function php_add_to_cart($data) {
	try {
		if(isset($data->checkout)) {
			if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
				throw new Exception("session checkout mismatch error");
			else
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = TRUE;
		}

		$f_explode = explode("_", $data->p_id);

		// We have bundles, lets process them.
		if(count($f_explode) > 1) {
			$f_return = NULL;
			foreach($f_explode as $f_add) {
				$f_obj = $data;
				$f_obj->p_id = $f_add;

				$f_return = php_add_to_cart($f_obj);
			}

			return $f_return;
		}
		else {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_price IS NOT NULL AND p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1 AND p.p_id = '" . $fluid->php_escape_string($data->p_id) . "' ORDER BY p.p_id ASC LIMIT 1");

			$fluid->php_db_commit();

			if(isset($data->f_checkout_id)) {
				if(isset($fluid->db_array)) {
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_id'] = $fluid->db_array[0]['p_id'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_stock'] = $fluid->db_array[0]['p_stock'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_price_map'] = $fluid->db_array[0]['p_price'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_component'] = $fluid->db_array[0]['p_component'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_instore'] = $fluid->db_array[0]['p_instore'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_freeship'] = $fluid->db_array[0]['p_freeship'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_preorder'] = $fluid->db_array[0]['p_preorder'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_special_order'] = $fluid->db_array[0]['p_special_order'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_newarrivalenddate'] = $fluid->db_array[0]['p_newarrivalenddate'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_arrivaltype'] = $fluid->db_array[0]['p_arrivaltype'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['c_name'] = $fluid->db_array[0]['c_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['m_name'] = $fluid->db_array[0]['m_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_name'] = $fluid->db_array[0]['p_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_mfgcode'] = $fluid->db_array[0]['p_mfgcode'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_mfg_number'] = $fluid->db_array[0]['p_mfg_number'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_namenum'] = $fluid->db_array[0]['p_namenum'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_catid'] = $fluid->db_array[0]['p_catid'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['m_id'] = $fluid->db_array[0]['m_id'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_status'] = $fluid->db_array[0]['p_formula_status'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_operation'] = $fluid->db_array[0]['p_formula_operation'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_math'] = $fluid->db_array[0]['p_formula_math'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_application'] = $fluid->db_array[0]['p_formula_application'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_discount_date_end'] = $fluid->db_array[0]['p_formula_discount_date_end'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_discount_date_start'] = $fluid->db_array[0]['p_formula_discount_date_start'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_items_data'] = $fluid->db_array[0]['p_formula_items_data'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_formula_items_faux_data'] = $fluid->db_array[0]['p_formula_items_faux_data'];

					if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) ) ) {
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_price'] = $fluid->db_array[0]['p_price_discount'];

						// Rebate / Claim status --> Only apply the rebate claim if the item was on sale.
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_rebate_claim'] = $fluid->db_array[0]['p_rebate_claim'];
					}
					else {
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_price'] = $fluid->db_array[0]['p_price'];
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_rebate_claim'] = 0;
					}

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_cost'] = $fluid->db_array[0]['p_cost'];

					// Dimensions and weight of the product.
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_length'] = $fluid->db_array[0]['p_length'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_width'] = $fluid->db_array[0]['p_width'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_height'] = $fluid->db_array[0]['p_height'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_weight'] = $fluid->db_array[0]['p_weight'];

					// Process the image.
					$p_images = $fluid->php_process_images($fluid->db_array[0]['p_images']);
					$f_img_name = str_replace(" ", "_", $fluid->db_array[0]['m_name'] . "_" . $fluid->db_array[0]['p_name'] . "_" . $fluid->db_array[0]['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_image'] = $p_images[0];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_width_height'] = $width_height;

					// Max qty somebody can purchase.
					if($fluid->db_array[0]['p_buyqty'] < 1) {
						if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $fluid->db_array[0]['p_enable'] == 2) {
							if($fluid->db_array[0]['p_stock'] > 0)
								$p_buy_qty = $fluid->db_array[0]['p_stock'];
							else
								$p_buy_qty = 1;
						}
						else
							$p_buy_qty = 99;
					}
					else if($fluid->db_array[0]['p_enable'] == 2 && $fluid->db_array[0]['p_stock'] > 0) {
						$p_buy_qty = $fluid->db_array[0]['p_stock'];
					}
					else {
						$p_buy_qty = $fluid->db_array[0]['p_buyqty'];
					}

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_buyqty'] = $p_buy_qty;
					if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'])) {
						if($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] + $data->p_qty > $p_buy_qty) {
							$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] = $p_buy_qty;
						}
						else {
							$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] = $_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] + $data->p_qty;
						}
					}
					else {
						if($data->p_qty > $p_buy_qty) {
							$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] = $p_buy_qty;
						}
						else {
							$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$fluid->db_array[0]['p_id']]['p_qty'] = $data->p_qty;
						}
					}
				}

				$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = FALSE;

				return php_html_cart($data->p_id, TRUE, TRUE, $data->button_id, $data);
			}
			else {
				$f_link_data_array = NULL;

				if(isset($fluid->db_array[0])) {
					// --> Item has not been in our cart. Lets check for any accessories to send to the html cart to process a modal for the user.
					if(empty($_SESSION['fluid_cart'][$data->p_id]))
						$f_found_cart = FALSE;
					else
						$f_found_cart = TRUE;

					$_SESSION['fluid_cart'][$data->p_id]['p_id'] = $fluid->db_array[0]['p_id'];
					$_SESSION['fluid_cart'][$data->p_id]['p_stock'] = $fluid->db_array[0]['p_stock'];
					$_SESSION['fluid_cart'][$data->p_id]['p_price_map'] = $fluid->db_array[0]['p_price'];
					$_SESSION['fluid_cart'][$data->p_id]['p_component'] = $fluid->db_array[0]['p_component'];

					$_SESSION['fluid_cart'][$data->p_id]['p_instore'] = $fluid->db_array[0]['p_instore'];
					$_SESSION['fluid_cart'][$data->p_id]['p_freeship'] = $fluid->db_array[0]['p_freeship'];
					$_SESSION['fluid_cart'][$data->p_id]['p_preorder'] = $fluid->db_array[0]['p_preorder'];

					$_SESSION['fluid_cart'][$data->p_id]['p_special_order'] = $fluid->db_array[0]['p_special_order'];
					$_SESSION['fluid_cart'][$data->p_id]['p_newarrivalenddate']  = $fluid->db_array[0]['p_newarrivalenddate'];

					$_SESSION['fluid_cart'][$data->p_id]['p_arrivaltype'] = $fluid->db_array[0]['p_arrivaltype'];

					$_SESSION['fluid_cart'][$data->p_id]['m_name'] = $fluid->db_array[0]['m_name'];
					$_SESSION['fluid_cart'][$data->p_id]['c_name'] = $fluid->db_array[0]['c_name'];

					$_SESSION['fluid_cart'][$data->p_id]['p_name'] = $fluid->db_array[0]['p_name'];
					$_SESSION['fluid_cart'][$data->p_id]['p_mfgcode'] = $fluid->db_array[0]['p_mfgcode'];
					$_SESSION['fluid_cart'][$data->p_id]['p_mfg_number'] = $fluid->db_array[0]['p_mfg_number'];
					$_SESSION['fluid_cart'][$data->p_id]['p_namenum'] = $fluid->db_array[0]['p_namenum'];
					$_SESSION['fluid_cart'][$data->p_id]['p_catid'] = $fluid->db_array[0]['p_catid'];
					$_SESSION['fluid_cart'][$data->p_id]['m_id'] = $fluid->db_array[0]['m_id'];

					$_SESSION['fluid_cart'][$data->p_id]['p_formula_status'] = $fluid->db_array[0]['p_formula_status'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_operation'] = $fluid->db_array[0]['p_formula_operation'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_math'] = $fluid->db_array[0]['p_formula_math'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_application'] = $fluid->db_array[0]['p_formula_application'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_discount_date_end'] = $fluid->db_array[0]['p_formula_discount_date_end'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_discount_date_start'] = $fluid->db_array[0]['p_formula_discount_date_start'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_items_data'] = $fluid->db_array[0]['p_formula_items_data'];
					$_SESSION['fluid_cart'][$data->p_id]['p_formula_items_faux_data'] = $fluid->db_array[0]['p_formula_items_faux_data'];

					if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) ) ) {
						$_SESSION['fluid_cart'][$data->p_id]['p_price'] = $fluid->db_array[0]['p_price_discount'];

						// Rebate / Claim status --> Only apply the rebate.
						$_SESSION['fluid_cart'][$data->p_id]['p_rebate_claim'] = $fluid->db_array[0]['p_rebate_claim'];
					}
					else {
						$_SESSION['fluid_cart'][$data->p_id]['p_price'] = $fluid->db_array[0]['p_price'];
						$_SESSION['fluid_cart'][$data->p_id]['p_rebate_claim'] = 0;
					}

					$_SESSION['fluid_cart'][$data->p_id]['p_cost'] = $fluid->db_array[0]['p_cost'];

					// Dimensions and weight of the product.
					$_SESSION['fluid_cart'][$data->p_id]['p_length'] = $fluid->db_array[0]['p_length'];
					$_SESSION['fluid_cart'][$data->p_id]['p_width'] = $fluid->db_array[0]['p_width'];
					$_SESSION['fluid_cart'][$data->p_id]['p_height'] = $fluid->db_array[0]['p_height'];
					$_SESSION['fluid_cart'][$data->p_id]['p_weight'] = $fluid->db_array[0]['p_weight'];

					// Process the image.
					$p_images = $fluid->php_process_images($fluid->db_array[0]['p_images']);
					$f_img_name = str_replace(" ", "_", $fluid->db_array[0]['m_name'] . "_" . $fluid->db_array[0]['p_name'] . "_" . $fluid->db_array[0]['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					$_SESSION['fluid_cart'][$data->p_id]['p_image'] = $p_images[0];
					$_SESSION['fluid_cart'][$data->p_id]['p_width_height'] = $width_height;

					// Max qty somebody can purchase.
					if($fluid->db_array[0]['p_buyqty'] < 1) {
						if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $fluid->db_array[0]['p_enable'] == 2) {
							if($fluid->db_array[0]['p_stock'] > 0)
								$p_buy_qty = $fluid->db_array[0]['p_stock'];
							else
								$p_buy_qty = 1;
						}
						else
							$p_buy_qty = 99;
					}
					else if($fluid->db_array[0]['p_enable'] == 2 && $fluid->db_array[0]['p_stock'] > 0)
						$p_buy_qty = $fluid->db_array[0]['p_stock'];
					else
						$p_buy_qty = $fluid->db_array[0]['p_buyqty'];

					$_SESSION['fluid_cart'][$data->p_id]['p_buyqty'] = $p_buy_qty;
					if(isset($_SESSION['fluid_cart'][$data->p_id]['p_qty'])) {
						if($_SESSION['fluid_cart'][$data->p_id]['p_qty'] + $data->p_qty > $p_buy_qty)
							$_SESSION['fluid_cart'][$data->p_id]['p_qty'] = $p_buy_qty;
						else
							$_SESSION['fluid_cart'][$data->p_id]['p_qty'] = $_SESSION['fluid_cart'][$data->p_id]['p_qty'] + $data->p_qty;
					}
					else {
						if($data->p_qty > $p_buy_qty)
							$_SESSION['fluid_cart'][$data->p_id]['p_qty'] = $p_buy_qty;
						else
							$_SESSION['fluid_cart'][$data->p_id]['p_qty'] = $data->p_qty;
					}

					// --> Item has not been in our cart. Lets check for any accessories to send to the html cart to process a modal for the user.
					//if(empty($_SESSION['fluid_cart'][$data->p_id])) {
					if($f_found_cart == FALSE) {
						if(isset($fluid->db_array[0]['p_category_items_data'])) {
							$f_link_data = json_decode($fluid->db_array[0]['p_category_items_data']);

							// --> We have some accessory item linking. Lets grab the data now.
							if(count($f_link_data) > 0) {
								$f_link_data_array = php_html_accessory_modal($_SESSION['fluid_cart'][$data->p_id], $f_link_data);
							}
						}
					}
				}

				php_cart_persistence_update();

				return php_html_cart($data->p_id, FALSE, FALSE, $data->button_id, NULL, NULL, $f_link_data_array);
			}
		}
	}
	catch (Exception $err) {
		if(isset($data->checkout)) {
			if(isset($_SESSION['f_checkout'][$data->f_checkout_id]))
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = FALSE;
		}

		return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
	}
}

// --> Builds html for a popup modal prompting about accessories after adding a item to the cart. This is called by php_add_to_cart(); Then it is  handed over and processed by php_html_cart();
function php_html_accessory_modal($f_current_item = NULL, $f_link_data = NULL) {
	try {
		$fluid = new Fluid();
		$f_items_swiper_premier_html = NULL;
		$f_data_array = NULL;
		$i_max = 0;

		if(isset($f_current_item) && isset($f_link_data)) {
			$f_items_swiper_html = NULL;

			$where = "AND p_id IN (";
			$i = 0;

			$fluid_link = new Fluid();
			$fluid_link->php_db_begin();

			foreach($f_link_data as $f_data) {
				if(count($f_data->sub_filters) > 0) {

					foreach($f_data->sub_filters as $f_items) {
						if($i != 0)
							$where .= ", ";

						$where .= $fluid_link->php_escape_string($f_items->p_id);

						$i++;

					}
				}
			}

			$where .= ") ";

			if($i > 0) {
				//$fluid_link->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where . " HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");

				$fluid_link->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where . " ORDER BY p_sortorder ASC LIMIT 0,1000");


				if(isset($fluid_link->db_array)) {
					shuffle($fluid_link->db_array);

					foreach($fluid_link->db_array as $data) {
						if($i_max == 10)
							break;

						$f_items_swiper_html .= "<div class=\"swiper-slide swiper-slide-modal-" . $f_data->filter_order . "\">";
							$f_items_swiper_html .= "<div class=\"trending-product\">";

								if(empty($data['p_mfgcode']))
									$ft_mfgcode = $data['p_id'];
								else
									$ft_mfgcode = $data['p_mfgcode'];

								if(empty($data['p_name']))
									$ft_name = $data['p_id'];
								else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
									$ft_name = $data['p_name'];
								else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
									$ft_name = $data['p_name'];
								else
									$ft_name = $data['p_mfg_number'] . " " . $data['p_name'];

									$f_items_swiper_html .= "<div class=\"thumbnail trending-product-thumbnail\">";
										$f_items_swiper_html .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
										$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
										$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

										$p_images = $fluid_link->php_process_images($data['p_images']);
										$width_height_l = $fluid_link->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
										$f_items_swiper_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_link->php_clean_string($ft_mfgcode) . "/" . $fluid_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

										$f_items_swiper_html .= "<div class=\"caption\">";
											$f_items_swiper_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_link->php_clean_string($ft_mfgcode) . "/" . $fluid_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-manufacturer\">" . $fluid_link->php_clean_string($data['m_name']) . "</h6></a>";

											$f_items_swiper_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_link->php_clean_string($ft_mfgcode) . "/" . $fluid_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-name\">";
												if(empty($data['p_name']))
													$ft_name = $data['p_id'];
												else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
													$ft_name = $data['p_name'];
												else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
													$ft_name = $data['p_name'];
												else
													$ft_name = $data['p_mfg_number'] . " " . $data['p_name'];

												if(strlen($ft_name) > 50)
													$ft_name = substr($ft_name, 0, 50) . '...';
											$f_items_swiper_html .= $ft_name . "</h6></a>";

											$f_items_swiper_html .= "<div class=\"trending-product-heading-price-container\">";
											if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
												$f_items_swiper_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
												$f_items_swiper_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid_link->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
											}
											else {
												$f_items_swiper_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid_link->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
											}

												$f_items_swiper_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
												if(FLUID_STORE_OPEN == FALSE) {
													$f_disabled_style = "disabled";
												}
												else {
													$f_disabled_style = NULL;
												}

												$f_btn_mode = "";
												$cart_disabled = "onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'";
												$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-success btn-block'";
												$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to Cart";

												if($data['p_height'] <= 0 || $data['p_length'] <= 0 || $data['p_width'] <= 0 || $data['p_weight'] <= 0 || FLUID_STORE_OPEN == FALSE || FLUID_PURCHASE_OUT_OF_STOCK == FALSE) {
													if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
														// --> Do nothing. Preorders will be checked below.
													}
													else if($data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
														// --> // --> Item is in stock, but not available to be preordered and is not officially launched yet.
														$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-default btn-block'";
														$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
														$cart_disabled = "disabled";
													}
													else if($data['p_special_order'] == 1) {
														// --> Do nothing. Special orders will be checked below.
													}
													else if($data['p_stock'] > 0) {
														// --> Do nothing. Item is in stock.
													}
													else
														$cart_disabled = "disabled";

													//if(FLUID_PAYMENT_SANDBOX == FALSE)
														//$cart_disabled = "disabled";
												}
												else if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE && ($data['p_stock'] < 1 || $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
													if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
														//  --> Do nothing. Preorders will be checked below.
													}
													else if($data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
														// --> Item is in stock, but not available to be preordered and is not officially launched yet.
														$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-default btn-block'";
														$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
														$cart_disabled = "disabled";
													}
													else if($data['p_special_order'] == 1) {
														// --> Do nothing. Special orders will be checked below.
													}
													else if($data['p_stock'] > 0) {
														//  --> Do nothing. Preorders will be checked below.
													}
													else
														$cart_disabled = "disabled";
												}
												else if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE && $data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
													// --> Item is in stock, but not available to be preordered and is not officially launched yet.
													$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-default btn-block'";
													$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
													$cart_disabled = "disabled";
												}

												if($data['p_height'] <= 0 || $data['p_length'] <= 0 || $data['p_width'] <= 0 || $data['p_weight'] <= 0 || $data['p_price'] <= 0) {
													$f_cart_class = "href='tel:+16046855331' class='btn btn-md " . $f_btn_mode . " btn-primary btn-block'";
													$f_cart_message = "<i class='fa fa-phone' aria-hidden='true'></i> Call for more info";
													$cart_disabled = "disabled";
												}

												//if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
												if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
													$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-warning btn-block'";
													$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Preorder";

													if(FLUID_PREORDER == FALSE)
														$cart_disabled = "disabled";
												}
												else if($data['p_special_order'] == 1 && $data['p_stock'] < 1) {
													$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-info btn-block'";
													$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Special Order";
												}

												// --> Discontinued item and no longer in stock.
												if($data['p_stock'] < 1 && $data['p_enable'] == 2) {
													$cart_disabled = "disabled";
													$f_cart_class = "class='btn btn-md " . $f_btn_mode . " btn-default btn-block'";
													$f_cart_message = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Discontinued";
												}




												//$f_items_swiper_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";

												$f_items_swiper_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' " . $f_cart_class . " " . $cart_disabled . ">" . $f_cart_message . "</button></div></div>";

											$f_items_swiper_html .= "</div>";
										$f_items_swiper_html .= "</div>";
										$f_items_swiper_html .= "</div></div>"; // empty div delete.
									$f_items_swiper_html .= "</div>";
							$f_items_swiper_html .= "</div>";
						$f_items_swiper_html .= "</div>";

						$i_max++;
					}

					if(isset($f_items_swiper_html)) {
						if($i_max > 1) {
							$width = "100";
						}
						else {
							$width = "60";
						}

						$f_items_swiper_premier_html = "
							<style>
							.swiper-container-modal-" . $f_data->filter_order . " {
								width: 100%;
								margin: auto;
							}

							@media(min-width: 768px) {
								.swiper-container-modal-" . $f_data->filter_order . " {
									width: " . $width . "%;
									margin: auto;
								}
							}

							.swiper-slide-modal-" . $f_data->filter_order . " {
								text-align: center;
								display: -webkit-box;
								display: -ms-flexbox;
								display: -webkit-flex;
								display: flex;
								-webkit-box-pack: center;
								-ms-flex-pack: center;
								-webkit-justify-content: center;
								justify-content: center;
								-webkit-box-align: center;
								-ms-flex-align: center;
								-webkit-align-items: center;
								align-items: center;
							}
							</style>

							<div class=\"container-fluid\" style='padding-top: 15px; padding-bottom: 15px;'>
								<div class=\"row\">

										<div class=\"col-sm-12\">
											<div class='f-trending-div f-trending-div-font' style='margin-left: -5px;'>Recommended Accessories</div>
										</div>

									<div class=\"swiper-container swiper-container-modal-" . $f_data->filter_order . "\">
										<div class=\"swiper-wrapper\">
											" . $f_items_swiper_html . "
										</div>
										<div class=\"swiper-pagination swiper-pagination-modal-" . $f_data->filter_order . "\" style='position: static;'></div>
										<!-- Add Arrows -->
										<div class=\"swiper-button-next\"></div>
										<div class=\"swiper-button-prev\"></div>
									</div>

								</div>
							</div>";

							$f_data_array[] = $f_data->filter_order;
					}
				}
			}

			// --> Looks like we have some accessory linking data. Lets process the main item now.
			if(isset($f_items_swiper_premier_html)) {
				// Process the image.
				$f_img_name = str_replace(" ", "_", $f_current_item['m_name'] . "_" . $f_current_item['p_name'] . "_" . $f_current_item['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
				$width_height = $fluid_link->php_process_image_resize($f_current_item['p_image'], "60", "60", $f_img_name);

				$f_item_html = "<div class='container-fluid'><div class='row'><div class='col-sm-12'><div class='f-trending-div f-trending-div-font' style='margin-left: -5px;'>Item added to your cart</div></div></div></div>";

				$f_item_html .= "<div class='fluid-box-shadow-transparent' style='padding: 5px;'><div style='display: table;'>";

					$f_item_html .= "<div style='display: table-cell; vertical-align: middle;'><img class='img-responsive trending-product-image' style='text-align; left; display: inline-block;' src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' alt=\"" . str_replace('"', '', $f_current_item['m_name'] . " " . $f_current_item['p_name']) . "\"/></img></div>";

					$f_item_html .= "<div style='display: table-cell; vertical-align: middle;'><div style='display: inline;'>" . $f_current_item['m_name'] . " " . $f_current_item['p_name'] . "</div></div>";

				$f_item_html .= "</div></div>";

			}

			$fluid_link->php_db_commit();
		}

		if(isset($f_items_swiper_premier_html) && isset($f_item_html)) {
			if(FLUID_STORE_OPEN == FALSE)
				$html_e_button_checkout = "<button type='button' class='btn btn-success pull-right disabled' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button>";
			else if(empty($_SESSION['u_id']))
				$html_e_button_checkout = " <button type='button' class='btn btn-success pull-right' aria-haspopup='true' aria-expanded='false' onClick='js_close_toggle_menus(); document.getElementById(\"fluid-account-dropdown\").innerHTML = \"\"; document.getElementById(\"fluid-account-dropdown-nav\").innerHTML = \"\"; document.getElementById(\"modal-login-div\").innerHTML = \"\"; document.getElementById(\"modal-checkout-fluid-div\").innerHTML = Base64.decode(FluidMenu.account[\"html\"]); document.getElementById(\"fluid-div-signup\").innerHTML = Base64.decode(FluidMenu.account[\"signup_checkout_plus_mobile_html\"]); document.getElementById(\"fluid-checkout-guest-back-button\").innerHTML = Base64.decode(FluidMenu.account[\"f_mobile_back_button_signup\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"none\"; fluid_facebook_checkout = 1; fluid_google_checkout = 1; document.getElementById(\"fluid-checkout-login\").value = \"1\"; js_fluid_login(); js_modal_hide(\"#fluid-modal\"); js_modal_show(\"#fluid-checkout-guest-modal\");'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button>";
			else
				$html_e_button_checkout = " <a href=\"" . $_SESSION['fluid_uri'] . FLUID_CHECKOUT_REWRITE . "\" onClick='js_loading_start();'><button type='button' class='btn btn-success pull-right' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button></a>";

			$f_items_swiper_premier_html = "
			<div class=\"modal-dialog\">

				<div class=\"modal-content\">
					<div id='modal-fluid-div' class=\"modal-body\" style='max-height: 90vh; overflow-y: auto;'>
					" . $f_item_html . $f_items_swiper_premier_html . "
					</div>

					<div class=\"modal-footer\">
						<div class='pull-left'><button type=\"button\" class=\"btn btn-info\" onClick=\"js_modal_hide('#fluid-modal');\"><span class='glyphicon glyphicon-arrow-right' aria-hidden=\"true\"></span> <div style='display: inline-block;'>Keep Shopping</div></button></div>
						<div class='pull-right'>" . $html_e_button_checkout . "</div>
					</div>
				</div>

			</div>";
		}

		return Array("html" => $f_items_swiper_premier_html, "data" => $f_data_array, "i_max" => $i_max);
	}
	catch (Exception $err) {
		return json_encode(array("error" => 1, "error_message" => base64_encode("Error adding item to cart. Please try again.")));
	}
}

// Check for a cart persistence cookie.
function php_cart_cookie_check() {
	$cookie = isset($_COOKIE[FLUID_COOKIE_CART_PERSISTENCE]) ? $_COOKIE[FLUID_COOKIE_CART_PERSISTENCE] : '';

	if(!empty($cookie)) {
		list ($u_oauth_id, $token, $mac) = explode(':', $cookie);

		if(!hash_equals(hash_hmac('sha256', $u_oauth_id . ':' . $token, HASH_KEY), $mac))
			return false;
		else
			return true;
	}
	else
		return false;
}

// Create the cookie for a shopping cart.
function php_cart_cookie() {
	// Create a persistence cart cookie.
	if(function_exists('random_bytes')) {
		// PHP > 7.0
		$fluid_token = bin2hex(random_bytes(32));
	}
	else {
		// PHP < 7.2
		$fluid_token = bin2hex(mcrypt_create_iv(128, MCRYPT_DEV_URANDOM));
	}

	$cookie = base64_encode($_SERVER['REMOTE_ADDR']) . ':' . $fluid_token;
	$mac = hash_hmac('sha256', $cookie, HASH_KEY);
	$cookie .= ':' . $mac;

	setcookie(FLUID_COOKIE_CART_PERSISTENCE, $cookie, time() + (86400 * 30), "/"); // 30 days

	if(php_cart_cookie_check() == TRUE) {
		$_SESSION['fluid_cart_persistence'] = $fluid_token;
		return true; // cookie was set.
	}
	else
		return false; // cookie was blocked from being set.
}

// Loads the persistence based on cookie.
function php_cart_persistence() {
	// 1. Use the cart cookie to load from data base.
	if(php_cart_cookie_check() == FALSE) {
		php_cart_cookie();

		// If a cookie was loaded and saved, lets check and load a cart if we find one.
		if(isset($_SESSION['fluid_cart_persistence']))
			php_cart_persistence_load();
	}
	// 2. Loop through database data and php_cart_update to load the data.
	else if(php_cart_cookie_check() == TRUE && !isset($_SESSION['fluid_cart_persistence']))
		php_cart_persistence_load();
}

function php_cart_persistence_load() {
	$cookie = isset($_COOKIE[FLUID_COOKIE_CART_PERSISTENCE]) ? $_COOKIE[FLUID_COOKIE_CART_PERSISTENCE] : '';
	list ($u_oauth_id, $token, $mac) = explode(':', $cookie);

	$fluid_cart = new Fluid ();

	$fluid_cart->php_db_begin();

	$fluid_cart->php_db_query("SELECT * FROM " . TABLE_CART_PERSISTENCE . " WHERE cp_token = '" . $fluid_cart->php_escape_string($token) . "' LIMIT 1");

	$fluid_cart->php_db_commit();

	if(isset($fluid_cart->db_array)) {
		if(hash_equals($fluid_cart->db_array[0]['cp_token'], $token)) {
			$tmp_products = (object) NULL;
			$tmp_products->f_items = json_decode(base64_decode($fluid_cart->db_array[0]['cp_cart']));
			$_SESSION['fluid_cart_persistence'] = $fluid_cart->db_array[0]['cp_token'];

			php_cart_update($tmp_products, FALSE);
		}
	}
	else
		$_SESSION['fluid_cart_persistence'] = $token; // --> If the cookie didn't load a cart, then lets set that cookie into the session. It is safe to use, even if the user hacks it because this cookie id cart never exists in the database.
}

// Updates the persistence cart in the database.
function php_cart_persistence_update() {
	if(isset($_SESSION['fluid_cart_persistence'])) {
		$tmp_cart = NULL;

		if(isset($_SESSION['fluid_cart'])) {
			foreach($_SESSION['fluid_cart'] as $key => $data) {
				$tmp_cart[$key] = (object)[];
				$tmp_cart[$key]->p_qty = $data['p_qty'];
			}
		}
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("INSERT INTO " . TABLE_CART_PERSISTENCE . " (cp_token, cp_cart) VALUES ('" . $fluid->php_escape_string($_SESSION['fluid_cart_persistence']) . "', '" . $fluid->php_escape_string(base64_encode(json_encode($tmp_cart))) . "') ON DUPLICATE KEY UPDATE cp_cart='" . $fluid->php_escape_string(base64_encode(json_encode($tmp_cart))) . "'");

		$fluid->php_db_commit();

		$fluid_log = new Fluid();
		$fluid_log->php_db_begin();
		$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('cart update', '" . $fluid_log->php_escape_string(serialize(print_r($_SESSION, TRUE))) . "')");
		$fluid_log->php_db_commit();
	}
}

// Sub total for the cart.
function php_cart_sub_total($f_checkout_id = NULL, $f_cart = NULL) {
	$tmp_total = 0;
	$tmp_discount = 0;

	if(isset($f_checkout_id)) {
		foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $cart)
			$tmp_total = $tmp_total + ($cart['p_qty'] * $cart['p_price']);

		//$tmp_discount = php_discount_cart($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']);
	}
	else if(isset($f_cart)) {
		foreach($f_cart as $cart)
			$tmp_total = $tmp_total + ($cart['p_qty'] * $cart['p_price']);
	}
	else if(isset($_SESSION['fluid_cart'])) {
		foreach($_SESSION['fluid_cart'] as $cart)
			$tmp_total = $tmp_total + ($cart['p_qty'] * $cart['p_price']);

		//$tmp_discount = php_discount_cart($_SESSION['fluid_cart']);
	}

	return $tmp_total;
}

// --> Not used yet.
function php_discount_cart($f_cart_tmp) {
	// --> Can be used for vouchers or other things. Tax is applied first, then this discount is subtracted off the final total of tax and everything.

	return 0;
}

// Cost sub total for the cart.
function php_cart_cost_sub_total($f_checkout_id = NULL) {
	$tmp_total = 0;

	if(isset($f_checkout_id)) {
		foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $cart) {
			$t_cost = 0;

			if(isset($cart['p_price_map']))
				if($cart['p_price_map'] > $cart['p_price'])
					$t_cost = $cart['p_price_map'] - $cart['p_price'];

			$tmp_total = $tmp_total + ($cart['p_qty'] * ($cart['p_cost'] - $t_cost));
		}
	}
	else if(isset($_SESSION['fluid_cart'])) {
		foreach($_SESSION['fluid_cart'] as $cart) {
			$t_cost = 0;

			if(isset($cart['p_price_map']))
				if($cart['p_price_map'] > $cart['p_price'])
					$t_cost = $cart['p_price_map'] - $cart['p_price'];

			$tmp_total = $tmp_total + ($cart['p_qty'] * ($cart['p_cost'] - $t_cost));
		}
	}

	return $tmp_total;
}

// Updates the cart from any edits in the checkout cart if the user wishes to go back to shopping on the site. Keeps any changes from the checkout cart by overwriting the original cart.
function php_cart_keep_shopping($data) {
	try {
		// --> Disabled. If a session timeout happens during checkout and trying to go back causes exception to be thrown.
		//if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			//throw new Exception("session checkout mismatch error");

		unset($_SESSION['fluid_cart']);

		if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'])) {
			$_SESSION['fluid_cart'] = $_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'];
		}

		// --> Could cause a page loop on checkout if the checkout page errors and reloads. Then returning to shop will keep you stuck on a checkout loop.
		//if(isset($_SESSION['previous_page']))
			//$f_url = base64_encode($_SESSION['previous_page']);
		if(isset($_SESSION['fluid_uri'])) {
			$f_url = base64_encode($_SESSION['fluid_uri']);
		}
		else {
			$f_url = base64_encode(WWW_SITE);
		}

		$execute_functions[]['function'] = "js_redirect_url";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("url" => $f_url)));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Updates the saved cart changes.
function php_cart_update($data, $update_persistence = TRUE) {
	try {
		if(isset($_REQUEST['checkout'])) {
			if(empty($_SESSION['f_checkout'][$data->f_checkout_id])) {
				throw new Exception("session checkout mismatch error");
			}
		}

		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$where = NULL;
		$data_array = NULL;

		$where = "AND p_id IN (";
		$i = 0;
		if(isset($data->f_items)) {
			foreach($data->f_items as $key => $tmp) {
				if(isset($tmp->p_id)) {
					if($i > 0)
						$where .= ", ";

					$where .= "'" . $fluid->php_escape_string($tmp->p_id) . "'";

					// Check for duplicate items, that may have different id keys because of discounts, etc and merge them. php_html_cart will separate them again after when required.
					if(isset($data_array[$tmp->p_id])) {
						$data_array[$tmp->p_id] = $data_array[$tmp->p_id] + $tmp->p_qty;
					}
					else {
						$data_array[$tmp->p_id] = $tmp->p_qty;
					}

					$i++;
				}
			}
		}
		$where .= ")";

		if($data_array != NULL)
			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_price IS NOT NULL AND p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1 " . $where . " ORDER BY p.p_id ASC");

		$fluid->php_db_commit();

		if(isset($data->f_checkout_id)) {
			unset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart']);

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $item) {
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_id'] = $item['p_id'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_stock'] = $item['p_stock'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_price_map'] = $item['p_price'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_instore'] = $item['p_instore'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_component'] = $item['p_component'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_freeship'] = $item['p_freeship'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_preorder'] = $item['p_preorder'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_special_order'] = $item['p_special_order'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_newarrivalenddate'] = $item['p_newarrivalenddate'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_arrivaltype'] = $item['p_arrivaltype'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['c_name'] = $item['c_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['m_name'] = $item['m_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_name'] = $item['p_name'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_mfgcode'] = $item['p_mfgcode'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_mfg_number'] = $item['p_mfg_number'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_namenum'] = $item['p_namenum'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_catid'] = $item['p_catid'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['m_id'] = $item['m_id'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_status'] = $item['p_formula_status'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_operation'] = $item['p_formula_operation'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_math'] = $item['p_formula_math'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_application'] = $item['p_formula_application'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_discount_date_end'] = $item['p_formula_discount_date_end'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_discount_date_start'] = $item['p_formula_discount_date_start'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_items_data'] = $item['p_formula_items_data'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_formula_items_faux_data'] = $item['p_formula_items_faux_data'];

					if($item['p_price_discount'] && ((strtotime($item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($item['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $item['p_discount_date_end'] == NULL) || ($item['p_discount_date_start'] == NULL && $item['p_discount_date_end'] == NULL) ) ) {
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_price'] = $item['p_price_discount'];

						// Rebate / Claim status --> Only apply the rebate if a price discount.
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_rebate_claim'] = $item['p_rebate_claim'];
					}
					else {
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_price'] = $item['p_price'];
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_rebate_claim'] = 0;
					}

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_cost'] = $item['p_cost'];

					// Dimensions and weight of the product.
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_length'] = $item['p_length'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_width'] = $item['p_width'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_height'] = $item['p_height'];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_weight'] = $item['p_weight'];

					// Process the image.
					$p_images = $fluid->php_process_images($item['p_images']);
					$f_img_name = str_replace(" ", "_", $item['m_name'] . "_" . $item['p_name'] . "_" . $item['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_image'] = $p_images[0];
					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_width_height'] = $width_height;

					// Max qty somebody can purchase.
					if($item['p_buyqty'] < 1) {
						if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $item['p_enable'] == 2) {
							if($item['p_stock'] > 0)
								$p_buy_qty = $item['p_stock'];
							else
								$p_buy_qty = 1;
						}
						else
							$p_buy_qty = 99;
					}
					else if($item['p_enable'] == 2 && $item['p_stock'] > 0)
						$p_buy_qty = $item['p_stock'];
					else
						$p_buy_qty = $item['p_buyqty'];

					$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_buyqty'] = $p_buy_qty;

					// Set the quantity of the item.
					if($data_array[$item['p_id']] < 0)
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_qty'] = 1;
					else if($data_array[$item['p_id']] > $p_buy_qty)
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_qty'] = $p_buy_qty;
					else
						$_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'][$item['p_id']]['p_qty'] = $data_array[$item['p_id']];
				}
			}

			return php_html_cart(NULL, TRUE, TRUE, FALSE, $data);
		}
		else {
			unset($_SESSION['fluid_cart']);

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $item) {
					$_SESSION['fluid_cart'][$item['p_id']]['p_id'] = $item['p_id'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_stock'] = $item['p_stock'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_price_map'] = $item['p_price'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_instore'] = $item['p_instore'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_component'] = $item['p_component'];

					$_SESSION['fluid_cart'][$item['p_id']]['p_freeship'] = $item['p_freeship'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_preorder'] = $item['p_preorder'];

					$_SESSION['fluid_cart'][$item['p_id']]['p_special_order'] = $item['p_special_order'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_newarrivalenddate'] = $item['p_newarrivalenddate'];

					$_SESSION['fluid_cart'][$item['p_id']]['p_arrivaltype'] = $item['p_arrivaltype'];

					$_SESSION['fluid_cart'][$item['p_id']]['c_name'] = $item['c_name'];
					$_SESSION['fluid_cart'][$item['p_id']]['m_name'] = $item['m_name'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_name'] = $item['p_name'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_mfgcode'] = $item['p_mfgcode'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_mfg_number'] = $item['p_mfg_number'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_namenum'] = $item['p_namenum'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_catid'] = $item['p_catid'];
					$_SESSION['fluid_cart'][$item['p_id']]['m_id'] = $item['m_id'];

					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_status'] = $item['p_formula_status'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_operation'] = $item['p_formula_operation'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_math'] = $item['p_formula_math'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_application'] = $item['p_formula_application'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_discount_date_end'] = $item['p_formula_discount_date_end'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_discount_date_start'] = $item['p_formula_discount_date_start'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_items_data'] = $item['p_formula_items_data'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_formula_items_faux_data'] = $item['p_formula_items_faux_data'];

					if($item['p_price_discount'] && ((strtotime($item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($item['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $item['p_discount_date_end'] == NULL) || ($item['p_discount_date_start'] == NULL && $item['p_discount_date_end'] == NULL) ) ) {
						$_SESSION['fluid_cart'][$item['p_id']]['p_price'] = $item['p_price_discount'];

						// Rebate / Claim status --> Only apply the rebate claim when the item is on sale?
						$_SESSION['fluid_cart'][$item['p_id']]['p_rebate_claim'] = $item['p_rebate_claim'];
					}
					else {
						$_SESSION['fluid_cart'][$item['p_id']]['p_price'] = $item['p_price'];
						$_SESSION['fluid_cart'][$item['p_id']]['p_rebate_claim'] = 0;
					}

					$_SESSION['fluid_cart'][$item['p_id']]['p_cost'] = $item['p_cost'];

					// Dimensions and weight of the product.
					$_SESSION['fluid_cart'][$item['p_id']]['p_length'] = $item['p_length'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_width'] = $item['p_width'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_height'] = $item['p_height'];
					$_SESSION['fluid_cart'][$item['p_id']]['p_weight'] = $item['p_weight'];

					// Process the image.
					$p_images = $fluid->php_process_images($item['p_images']);
					$f_img_name = str_replace(" ", "_", $item['m_name'] . "_" . $item['p_name'] . "_" . $item['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					$_SESSION['fluid_cart'][$item['p_id']]['p_image'] = $p_images[0];
					$_SESSION['fluid_cart'][$item['p_id']]['p_width_height'] = $width_height;

					// Max qty somebody can purchase.
					if($item['p_buyqty'] < 1) {
						if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $item['p_enable'] == 2) {
							if($item['p_stock'] > 0)
								$p_buy_qty = $item['p_stock'];
							else
								$p_buy_qty = 1;
						}
						else
							$p_buy_qty = 99;
					}
					else if($item['p_enable'] == 2 && $item['p_stock'] > 0)
						$p_buy_qty = $item['p_stock'];
					else
						$p_buy_qty = $item['p_buyqty'];


					// Max qty somebody can purchase.
					$_SESSION['fluid_cart'][$item['p_id']]['p_buyqty'] = $p_buy_qty;

					if($data_array[$item['p_id']] < 0)
						$_SESSION['fluid_cart'][$item['p_id']]['p_qty'] = 1;
					else if($data_array[$item['p_id']] > $p_buy_qty)
						$_SESSION['fluid_cart'][$item['p_id']]['p_qty'] = $p_buy_qty;
					else
						$_SESSION['fluid_cart'][$item['p_id']]['p_qty'] = $data_array[$item['p_id']];
				}
			}

			if($update_persistence == TRUE)
				php_cart_persistence_update();

			if(isset($_REQUEST['checkout']))
				return php_html_cart(NULL, TRUE, TRUE, FALSE, $data);
			else
				return php_html_cart(NULL, TRUE);
		}
	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

function php_fluid_check_split_order($f_checkout_id) {
	try {
		if(FLUID_SPLIT_SHIPPING == FALSE) {
			return FALSE; // --> Split shipment feature disabled.
		}
		else if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'])) {
			// -->	Check stock.
			$fluid_stock = new Fluid ();
			$fluid_stock->php_db_begin();

			$f_tmp_stock = NULL;
			$where_stock = "WHERE p_id IN (";
			$i_stock = 0;

			foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $key_stock => $data_stock) {
				if($i_stock != 0) {
					$where_stock .= ", ";
				}

				$where_stock .= "'" . $fluid_stock->php_escape_string($data_stock['p_id']) . "'";

				$i_stock++;
			}

			$where_stock .= ")";

			$fluid_stock->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where_stock);

			if(isset($fluid_stock->db_array)) {
				foreach($fluid_stock->db_array as $s_key => $s_data) {
					$f_tmp_stock[$s_data['p_id']] = $s_data;
				}
			}

			$fluid_stock->php_db_commit();

			$f_none = 0;
			$f_none_check = FALSE;
			$f_found_stock = FALSE;
			$f_future = 0;
			foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $f_key_stock => $f_items_stock) {
				if(empty($f_items_stock['f_promo'])) {
					// --> Not enough stock, we should notify the user if they want to split the order shipment.
					if($fluid_stock->php_item_available($f_tmp_stock[$f_items_stock['p_id']]['p_newarrivalenddate']) == FALSE) {
						$f_future++;
					}
					else if($f_tmp_stock[$f_items_stock['p_id']]['p_stock'] < $f_items_stock['p_qty']) {
						// --> Some stock was found, just not enough qty to ship all at once. Ask to split.
						if($f_tmp_stock[$f_items_stock['p_id']]['p_stock'] > 0) {
							$f_found_stock = TRUE;
						}

						$f_none++;
						$f_none_check = TRUE;
					}
					else {
						$f_found_stock = TRUE;
						$f_none_check = TRUE;
					}
				}
			}

			if($f_none > 0 && $f_future < 1 && $f_found_stock == FALSE) { // --> No future items (preorders), but no stock also, so no need to split.
				return FALSE;
			}
			else if($f_future > 0 && $f_found_stock == FALSE) { // --> We have future items, and we have no stock items also. No need to split.
				return FALSE;
			}
			else if($f_found_stock == TRUE && $f_future > 0) { // --> We have future items, but also items in stock too. Ask to split.
				return TRUE;
			}
			else if($f_found_stock == TRUE && $f_none > 0) { // --> We found stock items, but also items not in stock. Ask to split.
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

// --> Check if any items are out of stock or have errors. Useful for when just before processing a payment, so we can stop it.
function php_fluid_check_stock_errors($f_checkout_id) {
	// We are allowing orders at all times on any thing.
	if(FLUID_PREORDER == TRUE && FLUID_PURCHASE_OUT_OF_STOCK == TRUE)
		return Array("result" => FALSE, "message" => "Can be purchased");

	// -->	Check stock. Prevent people from trying to hack there way from buying items with no stock left.
	$fluid_stock = new Fluid ();
	$fluid_stock->php_db_begin();

	$f_tmp_stock = NULL;
	$where_stock = "WHERE p_id IN (";
	$i_stock = 0;

	foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $key_stock => $data_stock) {
		if($i_stock != 0)
			$where_stock .= ", ";

		$where_stock .= "'" . $fluid_stock->php_escape_string($data_stock['p_id']) . "'";

		$i_stock++;
	}

	$where_stock .= ")";

	$fluid_stock->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where_stock);

	$fluid_stock->php_db_commit();

	if(isset($fluid_stock->db_array)) {
		$fluid_check = new Fluid();
		foreach($fluid_stock->db_array as $s_key => $s_data) {

			// Check the component stock.
			if($s_data['p_component'] == TRUE) {
				$s_data['p_stock'] = $fluid_check->php_process_stock($s_data);
			}

			$f_tmp_stock[$s_data['p_id']] = $s_data;
		}
	}


	foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $f_key_stock => $f_items_stock) {
		// We only want to check stock on real items and not virtual or promo items.
		if(isset($f_tmp_stock[$f_items_stock['p_id']])) {
			// --> Prevent people from buying items that do not have enough stock to fulfill the order.
			// Special Order items ['p_special_order'] set to 1 will be exempted, and allowed to purchased when out of stock is enabled.
			if($fluid_stock->php_item_available($f_tmp_stock[$f_items_stock['p_id']]['p_newarrivalenddate']) == TRUE && $f_tmp_stock[$f_items_stock['p_id']]['p_stock'] < $f_items_stock['p_qty'] && $f_tmp_stock[$f_items_stock['p_id']]['p_special_order'] == 0 && FLUID_PURCHASE_OUT_OF_STOCK == FALSE) {
				return Array("result" => TRUE, "message" => "Not enough stock on some items. Please adjust your quanties of items.");
				break;
			}

			// --> For preventing pre-ordered future items from being sold if the site wide preorder setting is turned off.
			if($fluid_stock->php_item_available($f_tmp_stock[$f_items_stock['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$f_items_stock['p_id']]['p_preorder'] == TRUE && FLUID_PREORDER == FALSE) {
				return Array("result" => TRUE, "message" => "Some pre-ordered items are not available yet for pre-order.");
				break;
			}

			// --> For preventing unreleased items from being sold.
			if($fluid_stock->php_item_available($f_tmp_stock[$f_items_stock['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$f_items_stock['p_id']]['p_preorder'] == FALSE) {
				return Array("result" => TRUE, "message" => "Some items are not available yet to be purchased.");
				break;
			}
		}
	}

	return Array("result" => FALSE, "message" => "Can be purchased");
}

// Currently not used.
// --> Return if any items dont meet enough stock to meet a order.
function php_fluid_check_stock_order($f_checkout_id) {
	// -->	Check stock. Prevent people from trying to hack there way from buying items with no stock left.
	$fluid_stock = new Fluid ();
	$fluid_stock->php_db_begin();

	$f_tmp_stock = NULL;
	$where_stock = "WHERE p_id IN (";
	$i_stock = 0;

	foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $key_stock => $data_stock) {
		if($i_stock != 0)
			$where_stock .= ", ";

		$where_stock .= "'" . $fluid_stock->php_escape_string($data_stock['p_id']) . "'";

		$i_stock++;
	}

	$where_stock .= ")";

	$fluid_stock->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where_stock);

	if(isset($fluid_stock->db_array))
		foreach($fluid_stock->db_array as $s_key => $s_data)
			$f_tmp_stock[$s_data['p_id']] = $s_data;

	$fluid_stock->php_db_commit();

	foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $f_key_stock => $f_items_stock) {
		// --> Not enough stock.
		if($f_tmp_stock[$f_items_stock['p_id']]['p_stock'] < $f_items_stock['p_qty']) {
			return TRUE;
			break;
		}
	}

	return FALSE;
}

function php_fluid_generate_order_id() {
	// Moneris might through a fit if the order length is less or greater than 20 characters. IT IS VERY IMPORTANT IS IS 20 CHARACTERS, NO MORE OR NO LESS BECAUSE OF MONERIS LIMITATIONS FOR SOME REASON!!
	$f_order_id = substr('fluid-' . substr(time(), 0, 3) . "-" . mt_rand(1000000000,9999999999), 0, 20);

	return $f_order_id;
}

// --> Create a PayPal payment.
function php_paypal_create() {
	try {
		$f_checkout_id = $_REQUEST['f_checkout_id'];

		if(empty($_SESSION['f_checkout'][$f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		if(empty($f_checkout_id) || empty($_SESSION['f_checkout'][$f_checkout_id]) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_email']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_street']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_postalcode']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_phonenumber']) || empty($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
			throw new Exception("There was a problem processing your order. Please try again.");

		if(!isset($_SESSION['f_checkout'][$f_checkout_id]['s_id']))
			throw new Exception("shipping id error");

		if($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['total'] < 0)
			throw new Exception("There was a problem processing your order. Please try again.");

		if(isset($_SESSION['f_checkout'][$f_checkout_id]['f_prevent_hack']))
			if($_SESSION['f_checkout'][$f_checkout_id]['f_prevent_hack'] == TRUE)
				throw new Exception("There was a problem processing your order. Please try again.");

		if(isset($_SESSION['fluid_cart']) == FALSE || FLUID_STORE_OPEN == FALSE) {
			header("Location: " . WWW_SITE);
			throw new Exception("There was a problem processing your order. Please try again.");
			exit(0);
		}

		if(FLUID_PAYMENT_SANDBOX == TRUE)
			$api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(PAYPAL_CLIENT_ID_SANDBOX, PAYPAL_SECRET_SANDBOX));
		else  {
			// --> https://github.com/paypal/PayPal-PHP-SDK/wiki/Going-Live
			$api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(PAYPAL_CLIENT_ID, PAYPAL_SECRET));
			$api_context->setConfig(Array('mode' => 'live'));
		}

		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		$f_item_array = NULL;
		foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $f_item) {
			$f_tmp_item = new Item();
			$f_tmp_item->setName($f_item['m_name'] . " " . $f_item['p_name'])
						->setCurrency(STORE_CURRENCY)
						->setQuantity($f_item['p_qty'])
						->setSku($f_item['p_mfgcode'])
						->setPrice($f_item['p_price']);

			$f_item_array[] = $f_tmp_item;
		}

		if(isset($f_item_array)) {
			$item_list = new ItemList();
			$item_list->setItems($f_item_array);
		}

		$details = new Details();
		$details->setShipping($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['shipping'])
			->setTax($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['tax_total'])
			->setSubtotal($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['sub_total']);

		$amount = new Amount();
		$amount->setCurrency(STORE_CURRENCY)
			->setTotal($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['total'])
			->setDetails($details);

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription("Leo's Camera Supply Purchase")
			->setInvoiceNumber(php_fluid_generate_order_id());

		if(isset($item_list))
			$transaction->setItemList($item_list);


		$redirect_urls = new RedirectUrls();
		$redirect_urls->setReturnUrl($_SESSION['fluid_uri'] . "fluid_cart.php?load_func=true&fluid_function=php_paypal_success&f_checkout_id=" . $f_checkout_id . "&success=true")
					  ->setCancelUrl($_SESSION['fluid_uri'] . "fluid_cart.php?load_func=true&fluid_function=php_paypal_success&f_checkout_id=" . $f_checkout_id . "&success=false");

		$payment = new Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setRedirectUrls($redirect_urls)
			->setTransactions(array($transaction));

		$request = clone $payment;

		$payment->create($api_context);

		$fluid_log = new Fluid();
		$fluid_log->php_db_begin();
		$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('paypal create', '" . $fluid_log->php_escape_string(serialize(print_r($payment, TRUE))) . "')");
		$fluid_log->php_db_commit();

		//$approval_url = $payment->getApprovalLink();

		return base64_encode($payment);
	}
	catch (Exception $err) {
		if(FLUID_PAYMENT_SANDBOX == TRUE) {
			return base64_encode(json_encode(Array("f_error" => TRUE, "f_error_message" => $err)));
		}
		else {
			return base64_encode(json_encode(Array("f_error" => TRUE)));
		}
	}
}

// --> Executes a PayPal payment.
function php_paypal_execute() {
	$fluid_log = new Fluid();

	try {
		$f_checkout_id = $_REQUEST['f_checkout_id'];

		if(empty($_SESSION['f_checkout'][$f_checkout_id])) {
			throw new Exception("session checkout mismatch error");
		}

		if(empty($f_checkout_id) || empty($_SESSION['f_checkout'][$f_checkout_id]) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_email']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_street']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_postalcode']) || empty($_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_phonenumber']) || empty($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'])) {
			throw new Exception("There was a problem processing your order. Please try again.");
		}

		if(!isset($_SESSION['f_checkout'][$f_checkout_id]['s_id'])) {
			throw new Exception("shipping id error");
		}

		if(isset($_SESSION['f_checkout'][$f_checkout_id]['f_prevent_hack']))
			if($_SESSION['f_checkout'][$f_checkout_id]['f_prevent_hack'] == TRUE)
				throw new Exception("There was a problem processing your order. Please try again.");

		if(isset($_SESSION['fluid_cart']) == FALSE || FLUID_STORE_OPEN == FALSE) {
			header("Location: " . WWW_SITE);
			throw new Exception("There was a problem processing your order. Please try again.");
			exit(0);
		}

		if(!isset($_SESSION['f_checkout'][$f_checkout_id]['s_id'])) {
			throw new Exception("shipping id error");
		}

		if($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['total'] < 0) {
			throw new Exception("There was a problem processing your order. Please try again.");
		}

		// --> Check for any stock problems before processing a payment. Useful for when people time out and try to pay.
		$f_stock_check = php_fluid_check_stock_errors($f_checkout_id);
		if($f_stock_check['result'] == TRUE)
			throw new Exception($f_stock_check['message']);

		if(FLUID_PAYMENT_SANDBOX == TRUE) {
			$api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(PAYPAL_CLIENT_ID_SANDBOX, PAYPAL_SECRET_SANDBOX));
		}
		else  {
			$api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(PAYPAL_CLIENT_ID, PAYPAL_SECRET));
			$api_context->setConfig(Array('mode' => 'live'));
		}

		$payment_id = $_REQUEST['paymentID'];
		$payment = Payment::get($payment_id, $api_context);

		$execution = new PaymentExecution();
		$execution->setPayerId($_REQUEST['payerID']);

		$transaction = new Transaction();
		$amount = new Amount();
		$details = new Details();

		$details->setShipping($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['shipping'])
				->setTax($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['tax_total'])
				->setSubtotal($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['sub_total']);

		$amount->setCurrency(STORE_CURRENCY);
		$amount->setTotal($_SESSION['f_checkout'][$f_checkout_id]['f_totals']['total']);
		$amount->setDetails($details);

		$transaction->setAmount($amount);

		$execution->addTransaction($transaction);

		// --> Now lets execute this payment.
		$result = $payment->execute($execution, $api_context);

		$payment = Payment::get($payment_id, $api_context);

		$transactions = $payment->getTransactions();
		$transaction_data = $transactions[0];
		$f_order_id = $transaction_data->invoice_number;

		// --> Just in case there was a issue with the order id's, lets generate another one.
		if(empty($f_order_id)) {
			$f_order_id = php_fluid_generate_order_id();
		}

		// 1. --> Check $payment->state == "approved" before recording the sale. Else throw a exception.
		// 2. --> I want to base64 encode and send $payment to php_place_order and record the order.
		// 3. --> Return back a custom json encoded array, with just a modal html receipt and etc.

		$fluid_log->php_db_begin();
		$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('paypal charge', '" . $fluid_log->php_escape_string(serialize(print_r($payment, TRUE))) . "')");
		$fluid_log->php_db_commit();

		if($payment->getState() == "approved") {
			// --> PayPal payment was approved. Lets record the sale and data into the database.
			// --> Send a object that has ->f_checkout_id, ->paypal_obj (the payment object) (base64 encode it), ->is_paypal = TRUE, ->f_order_id (the invoice number to extract from the PayPal object.
			$f_data = (Object) Array("f_checkout_id" => $f_checkout_id, "f_order_id" => $f_order_id, "paypal_object" => base64_encode(serialize($payment)), "is_paypal" => TRUE);

			$f_save_data = php_fluid_place_order($f_data);

			$f_return = base64_encode(json_encode(Array("f_data" => $f_save_data)));

			return $f_return;
		}
		else {
			throw new Exception("There was a error processing your payment.");
		}

	}
	catch (Exception $err) {
		return base64_encode(json_encode(Array("f_error" => TRUE, "f_error_message" => $err)));
	}
}

// Process the order.
function php_fluid_place_order($f_data = NULL) {
	require_once(MONERIS_API);
	// --> ENV_LIVE // --> use the live API server
	// --> ENV_STAGING // -->  use the API sandbox
	// --> ENV_TESTING // --> use the mock API

	$fluid_log = new Fluid();
	$fluid_log->php_db_begin();
	$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('placing order', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
	$fluid_log->php_db_commit();
	$f_error_message = "Order error, there was a problem processing the transaction.";
	
	$fluid_in_store_order = FALSE;
	if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
		if(isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id'])) {
			if($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id'] == 0) {
				$fluid_in_store_order = TRUE;
				$f_paypal = FALSE;
			}
		}
	}
		
	if($fluid_in_store_order == FALSE) {
		if(empty($f_data->is_paypal)) {
			$f_paypal = FALSE;
		}
		else {
			$f_paypal = TRUE;
		}
		
		if($f_paypal == FALSE) {
			if(FLUID_PAYMENT_SANDBOX == TRUE) {
				if(MONERIS_ENABLED == TRUE) {
					$moneris = Moneris::create(
						array(
							'api_key' => MONERIS_API_KEY_SANDBOX,
							'store_id' => MONERIS_STORE_ID_SANDBOX,
							'environment' => Moneris::ENV_TESTING,
							'require_cvd' => true,
							'require_avs' => true
						)
					);
				}
				else if(AUTH_NET_ENABLED == TRUE) {
					$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
					$merchantAuthentication->setName(AUTH_NET_SANDBOX_LOGIN_ID);
					$merchantAuthentication->setTransactionKey(AUTH_NET_SANDBOX_API_KEY);
				}
			}
			else {
				if(MONERIS_ENABLED == TRUE) {
					$moneris = Moneris::create(
						array(
							'api_key' => MONERIS_API_KEY,
							'store_id' => MONERIS_STORE_ID,
							'environment' => Moneris::ENV_LIVE,
							'require_cvd' => true,
							'require_avs' => true
						)
					);
				}
				else if(AUTH_NET_ENABLED == TRUE) {
					$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
					$merchantAuthentication->setName(AUTH_NET_LOGIN_ID);
					$merchantAuthentication->setTransactionKey(AUTH_NET_API_KEY);
				}
			}
		}
	}
	$result = NULL;
	$transaction = NULL;
	$f_approved = FALSE; // --> Transaction was charged flag.
	$f_order_saved = FALSE; // --> A flag we know if a order was saved into the database to prevent a rollback void.

	try {
		if($fluid_in_store_order == FALSE) {
			if(AUTH_NET_ENABLED == FALSE && MONERIS_ENABLED == FALSE && PAYPAL_ENABLED == FALSE) {
				throw new Exception("There is currently no payment processes enabled.");
			}
		}
		
		// --> If not a PayPal payment.
		if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
			if(empty($_SESSION['f_checkout'][$f_data->f_checkout_id])) {
				throw new Exception("session checkout mismatch error");
			}

			if(empty($f_data->f_checkout_id) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode']) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber']) || empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart']))
				throw new Exception("There was a problem processing your order. Please try again.");

			if(!isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id'])) {
				throw new Exception("shipping id error");
			}

			if(isset($_SESSION['fluid_cart']) == FALSE || FLUID_STORE_OPEN == FALSE) {
				header("Location: " . WWW_SITE);
				exit(0);
			}

			if(isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_prevent_hack'])) {
				if($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_prevent_hack'] == TRUE) {
					throw new Exception("There was a problem processing your order. Please try again.");
				}
			}

			if($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'] < 0) {
				throw new Exception("There was a problem processing your order. Please try again.");
			}
		}

		// We do not check for this at this stage in PayPal orders. For PayPal orders, this is checked in the PayPal functions here.
		if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
			// --> Check for any stock problems before processing a payment. Useful for when people time out and try to pay.
			$f_stock_check = php_fluid_check_stock_errors($f_data->f_checkout_id);
			if($f_stock_check['result'] == TRUE) {
				throw new Exception($f_stock_check['message']);
			}
		}

		// Fix some shipping rate data and process the separate tax total breakdowns.
		$f_rates[$_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id']] = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_rates'][$_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id']];

		// Get the tax break down totals.
		$t_taxes = php_fluid_taxes(Array("a_data" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address'], "f_cart" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'], "f_rates" => $f_rates));

		if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
			$f_order_id = php_fluid_generate_order_id();
		}
		else {
			$f_order_id = $f_data->f_order_id;
		}
				
		if($fluid_in_store_order == TRUE) {
			$f_address_payment = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address'];
		}
		else {
			$f_address_payment = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_payment_address'];
		}

		if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
			if($fluid_in_store_order == TRUE) {
				$f_card_num = "";
				$f_card_num = "";

				$f_exp_month = "";
				$f_exp_year = "";
			}
			else {
				$f_card_num = preg_replace('/\s+/', '', $f_data->payment->c_number);
				$f_card_num = str_replace('-', '', $f_card_num);

				$f_exp_month = preg_replace('/\s+/', '', $f_data->payment->c_exp_m);
				$f_exp_year = preg_replace('/\s+/', '', $f_data->payment->c_exp_y);
			}
			
			if($fluid_in_store_order == TRUE) {
				// Do Nothing
			}
			else {
				$f_address_street_array = explode(" ", $f_data->payment_address->a_street, 2);
				if(isset($f_address_street_array[0])) {
					$f_address_street_number = $f_address_street_array[0];
				}
				else {
					$f_address_street_number = "";
				}

				if(isset($f_address_street_array[1])) {
					$f_address_street = $f_address_street_array[1];
				}
				else {
					$f_address_street = "";
				}

				if($f_data->payment_address->a_country == "Canada") {
					$f_address_zip_code = preg_replace('/\s+/', '', $f_data->payment_address->a_postalcode);
					$f_address_zip_code = str_replace('-', '', $f_address_zip_code);
					$f_address_zip_code = strtoupper($f_address_zip_code);
				}
				else if($data->payment_address->a_country == "United States") {
					$f_address_zip_code = preg_replace('/\s+/', '', $f_data->payment_address->a_postalcode);
					$f_address_zip_code = preg_replace('/\D/', '', $f_address_zip_code);
				}
				else {
					$f_address_zip_code = $f_data->payment_address->a_postalcode;
				}
				
				$f_data->payment_address->a_postalcode = $f_address_zip_code;
				$f_address_payment = $f_address_payment = $f_data->payment_address;
			}
			
			if($fluid_in_store_order == FALSE) {
				// Quebec hack: Shipping to Quebec is going to require PayPal payments for now. Quebec postal codes start with: G H and J
				$quebec = substr($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode'], 0, 1);
				if(isset($quebec)) {
					if($quebec == "G" || $quebec == "H" || $quebec == "J") {
						throw new Exception("We currently only accept PayPal payments to orders being sent to Quebec. Please change your payment type to PayPal to continue.");
					}
				}
			}
			
			//$f_address_zip_code = preg_replace('/\s+/', '', $f_data->payment_address->a_postalcode);
			//$f_address_zip_code = str_replace('-', '', $f_address_zip_code);

			// 'MD' and 'merchantUrl' required for 3d secure.
			$f_3d_secure_md = substr('fluid-3d-' . substr(time(), 0, 3) . "-" . mt_rand(1000000000,9999999999), 0, 20);

			// For testing various result codes, visit here for amounts and cards to use.
			//https://developer.moneris.com/More/Testing/E-Fraud%20Simulator

			// set up $moneris like we did ^^ up there
			if($fluid_in_store_order == TRUE) {
				$params_test = Array();
			}
			else {
				$params_test = array(
					'cc_number' => $f_card_num,
					'order_id' => $f_order_id,
					'amount' => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'],
					'require_cvd' => true,
					'cvd' => $f_data->payment->c_cv,
					'require_avs' => true,
					'avs_street_number' => $f_address_street_number,
					'avs_street_name' => $f_address_street,
					'avs_zipcode' => $f_address_zip_code,
					'expiry_month' => $f_exp_month,
					'expiry_year' => $f_exp_year
					//'MD' => '123456790123fjlkfj3k',
					//'merchantUrl' => 'www.leoscamera.com'
				);
			}

			if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
				$verification_result = $moneris->verify($params_test);

				$verification_result->validate_response();
				$verification_result->avs_response();
				$verification_result->cvd_response();

				$fluid_log->php_db_begin();
				//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('verifying order', '" . $fluid_log->php_escape_string(serialize(print_r($verification_result, TRUE))) . "')");
				$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('verifying order', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
				$fluid_log->php_db_commit();

				$f_cvd_verify_passed == FALSE;
				$receipt_verify = $verification_result->transaction()->response()->receipt;

				// --> This is not used. Can remove later.
				if(isset($receipt_verify->CvdResultCode)) {
					if((string) $receipt_verify->CvdResultCode == "1M") {
						$f_cvd_verify_passed = TRUE;
					}
				}

				// --> For some odd reason, avs checking doesn't work in verifcation mode from moneris??
				//if($verification_result->was_successful() && $f_cvd_verify_passed == TRUE) {
				if($verification_result->was_successful()) {
					$f_record_sale = TRUE;
				}
				else {
					$f_record_sale = FALSE;

					//if($f_cvd_verify_passed == FALSE)
						//$f_error_message = "Transaction error, Credit Card 3 digit CV security code verification failed.";
					//else
						$f_error_message = $verification_result->error_message();
						$f_error_message .= " Please try using PayPal if you continue to have problems.";
				}
			}
			else if(AUTH_NET_ENABLED == TRUE || $fluid_in_store_order == TRUE) {
				// We are going to skip authorizations and do a straight charge with authorize.net.
				$f_record_sale = TRUE;
			}
		}
		else {
			// --> We did a PayPal transaction sale, so proceed.
			$f_record_sale = TRUE;
		}

		if($f_record_sale == TRUE) {
			// --> Need to generate a new order id since it can't be the same as the one used in the verification process above ^^. This is only for Moneris payments though. PayPal is a exemption to this.
			if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
				$f_order_id = php_fluid_generate_order_id(); // --> Moneris may throw a error if the order length is less or greater than 20 characters.
				
				if($fluid_in_store_order == TRUE) {
					$params = Array();
				}
				else {
					$params = array(
						'cc_number' => $f_card_num,
						'order_id' => $f_order_id,
						'amount' => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'],
						'require_cvd' => true,
						'cvd' => $f_data->payment->c_cv,
						'require_avs' => true,
						'avs_street_number' => $f_address_street_number,
						'avs_street_name' => $f_address_street,
						'avs_zipcode' => $f_address_zip_code,
						'expiry_month' => $f_exp_month,
						'expiry_year' => $f_exp_year
					);
				}

				if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
					$result = $moneris->purchase($params);

					$result->validate_response();
					$result->avs_response();
					$result->cvd_response();
					$transaction = $result->transaction();

					$fluid_log->php_db_begin();
					//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('charging order', '" . $fluid_log->php_escape_string(serialize(print_r($result, TRUE))) . "')");
					$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('charging order', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
					$fluid_log->php_db_commit();

					$f_cvd_passed == FALSE;
					$receipt_charge = $result->transaction()->response()->receipt;

					// --> This is not used. Can remove later.
					if(isset($receipt_charge->CvdResultCode)) {
						if((string) $receipt_charge->CvdResultCode == "1M") {
							$f_cvd_passed = TRUE;
						}
					}

					if($result->was_successful()) {
						$f_payment_proceed = TRUE;
					}
					else {
						$f_payment_proceed = FALSE;

						//if($f_cvd_passed == FALSE)
							//$f_error_message = "Transaction error, Credit Card 3 digit CV security code verification failed.";
						//else
							$f_error_message = $result->error_message();
							$f_error_message .= " Please try using PayPal if you continue to have problems.";

						$void_result = $moneris->void($result->transaction()); // --> The purchase failed. Time to void it.

						$fluid_log->php_db_begin();
						//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('voiding order', '" . $fluid_log->php_escape_string(serialize(print_r($result, TRUE))) . "')");
						$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('voiding order', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
						$fluid_log->php_db_commit();
					}
				}
				else if(AUTH_NET_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
					$fluid_proc = new Fluid();
					// Set the transaction's refId
					$refId = mb_strimwidth(FLUID_COMPANY_NAME . '-' . time(), 0, 20, "");

					// Create the payment data for a credit card
					$creditCard = new AnetAPI\CreditCardType();
					$creditCard->setCardNumber($params['cc_number']);
					$cc_expiry = $params['expiry_month'] . "-" . $params['expiry_year'];

					$creditCard->setExpirationDate($cc_expiry);

					if(isset($params['cvd'])) {
						$creditCard->setCardCode($params['cvd']);
					}

					$paymentOne = new AnetAPI\PaymentType();
					$paymentOne->setCreditCard($creditCard);

					// Create order information
					$order = new AnetAPI\OrderType();
					$invoiceNumber = $f_order_id;
					$order->setInvoiceNumber(mb_strimwidth($invoiceNumber, 0, 255, ""));
					$order->setDescription(mb_strimwidth(FLUID_COMPANY_NAME, 0, 255, ""));

					// Set the customer's Bill To address
					$customerAddress = new AnetAPI\CustomerAddressType();
					$address_use = FALSE;

					$auth_name = $f_address_payment->a_name;
					$auth_name_array = explode(" ", $auth_name);

					if(isset($name_array[0])) {
						$first_name = $auth_name_array[0];
					}

					if(isset($first_name)) {
						if(count($auth_name_array) > 1) {
							$last_name = $auth_name_array[count($auth_name_array) - 1];
						}
					}

					if(isset($first_name)) {
						$customerAddress->setFirstName(mb_strimwidth($first_name, 0, 50, ""));
						$address_use = TRUE;
					}

					if(isset($last_name)) {
						$customerAddress->setLastName(mb_strimwidth($last_name, 0, 50, ""));
						$address_use = TRUE;
					}

					if(isset($params['avs_street_number']) && isset($params['avs_street_name'])) {
						$address_card = $params['avs_street_number'] . " " . $params['avs_street_name'];

						$customerAddress->setAddress(mb_strimwidth($address_card, 0, 60, ""));
						$address_use = TRUE;
					}

					if(isset($f_address_payment->a_city)) {
						$customerAddress->setCity(mb_strimwidth($f_address_payment->a_city, 0, 40, ""));
						$address_use = TRUE;
					}

					if(isset($f_address_payment->a_country)) {
						$country_card = $fluid_proc->php_country_name_to_ISO3166($f_address_payment->a_country, 'US');
						if(isset($country_card)) {
							$customerAddress->setCountry(mb_strimwidth($country_card, 0, 60, ""));
							$address_use = TRUE;
						}

						if($country_card == "US") {
							// For US states, use the USPS two-character abbreviation for the state.
							$state_card = $fluid_proc->php_fluid_state_abbr($f_address_payment->a_province);
						}
						else {
							//$state_card = $fluid->php_fluid_state_abbr($f_address_payment->a_province);
							$state_card = $f_address_payment->a_province;
						}

						if(isset($state_card)) {
							$customerAddress->setState(mb_strimwidth($state_card, 0, 40, ""));
							$address_use = TRUE;
						}
					}

					if(isset($params['avs_zipcode'])) {
						$customerAddress->setZip(mb_strimwidth($params['avs_zipcode'], 0, 20, ""));

						$address_use = TRUE;
					}

					// Add values for transaction settings
					// Duplicate window allows requests to prevent duplicate billing. In this instance, up to 60 seconds.
					// https://developer.authorize.net/api/reference/features/payment_transactions.html#Transaction_Settings
					$duplicateWindowSetting = new AnetAPI\SettingType();
					$duplicateWindowSetting->setSettingName("duplicateWindow");
					$duplicateWindowSetting->setSettingValue("60");

					$amount = $params['amount'];
					// Create a TransactionRequestType object and add the previous objects to it
					$transactionRequestType = new AnetAPI\TransactionRequestType();
					$transactionRequestType->setTransactionType("authCaptureTransaction");
					$transactionRequestType->setAmount($amount);
					$transactionRequestType->setOrder($order);
					$transactionRequestType->setPayment($paymentOne);

					if($address_use == TRUE) {
						$transactionRequestType->setBillTo($customerAddress);
					}

					// Assemble the complete transaction request
					$request = new AnetAPI\CreateTransactionRequest();
					$request->setMerchantAuthentication($merchantAuthentication);
					$request->setRefId($refId);
					$request->setTransactionRequest($transactionRequestType);

					// Create the controller and get the response
					$controller = new AnetController\CreateTransactionController($request);

					if(FLUID_PAYMENT_SANDBOX == TRUE) {
						$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
					}
					else {
						$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
					}
					//echo "<pre>";
					//print_r($f_data);
					//echo "</pre>";

					//echo "<pre>";
					//print_r($response);
					//echo "</pre>";

					$f_auth_net_object_64 = base64_encode(json_encode($response->getTransactionResponse()));

					//echo "<pre>";
						//print_r($simpleXmlElem);
						//print_r(json_decode(base64_decode($f_auth_net_object_64)));
					//echo "</pre>";

					//exit(0);
/*
https://developer.authorize.net/hello_world/testing_guide.html
https://support.authorize.net/s/article/What-Are-the-Different-Address-Verification-Service-AVS-Response-Codes
370000000000002    American Express Test Card
6011000000000012   Discover Test Card
5424000000000015   MasterCard Test Card
4007000000027      Visa Test Card
4012888818888      Visa Test Card
4222222222222      Visa Error Test

American Express 	370000000000002
Discover	6011000000000012
JCB	3088000000000017
Diners Club/ Carte Blanche	38000000000006
Visa	4007000000027
 	4012888818888
 	4111111111111111
Mastercard	5424000000000015
 	2223000010309703
 	2223000010309711

$f_auth_net_object_64 ->
stdClass Object
(
    [responseCode] => 1
    [authCode] => MBR86L
    [avsResultCode] => Y
    [cvvResultCode] => P
    [cavvResultCode] => 2
    [transId] => 40064912861
    [refTransID] =>
    [transHash] =>
    [testRequest] => 0
    [accountNumber] => XXXX0027
    [accountType] => Visa
    [messages] => stdClass Object
        (
            [message] => Array
                (
                    [0] => stdClass Object
                        (
                            [code] => 1
                            [description] => This transaction has been approved.
                        )

                )

        )

    [transHashSha2] =>
    [networkTransId] => M9WST55GFT0UZN8XV7PRBLE
)
*/
					if($response != NULL) {
						// Check to see if the API request was successfully received and acted upon.
						if($response->getMessages()->getResultCode() == "Ok") {
							$tresponse = $response->getTransactionResponse();

							if($tresponse != null && $tresponse->getMessages() != null) {
								$f_payment_proceed = TRUE;
							}
							else {
								$f_error_message = "Transaction Failed \n";
								if($tresponse->getErrors() != null) {
									$f_error_message .=" Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
									$f_error_message .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
								}

								$f_error_message .= " Please try using PayPal if you continue to have problems.";

								$f_payment_proceed = FALSE;
							}
						}
						else {
							$tresponse = $response->getTransactionResponse();

							if($tresponse != null && $tresponse->getErrors() != null) {
								$f_error_message = " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
								$f_error_message .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
							}
							else {
								$f_error_message = " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
								$f_error_message .= " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
							}

							$f_error_message .= " Please try using PayPal if you continue to have problems.";

							$f_payment_proceed = FALSE;
						}
					}
					else {
						// Failed
						$f_payment_proceed = FALSE;

						$f_error_message = "Transaction Failed \n";
						$f_error_message .= " Please try using PayPal if you continue to have problems.";
					}
				}
				else if($fluid_in_store_order == TRUE) {
					$f_payment_proceed = TRUE;
				}
			}
			else {
				$f_payment_proceed = TRUE;
			}
						
			if($f_payment_proceed == TRUE) {
				$f_declined = FALSE;

				// Transaction declined.
				if(empty($f_data->is_paypal)) {
					if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
						if($transaction->response()->receipt->ResponseCode >= 50 || $transaction->response()->receipt->ResponseCode == "null" || $transaction->response()->receipt->ResponseCode == NULL) {
							$f_declined = TRUE;
						}
					}
				}

				if($f_declined == TRUE) {
					$execute_functions[]['function'] = "js_html_insert";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode("Transaction Declined, there was a problem with your payment information."))));

					$execute_functions[]['function'] = "js_modal_show_data";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));
				}
				else {
					// --> Transaction approved.
					$f_approved = TRUE;
					// Remove un-necessary data from the shipping rate array before storage into the database.
					foreach($f_rates as $f_rate_key => $f_rate_data) {
						unset($f_rates[$f_rate_key]['html']);
						unset($f_rates[$f_rate_key]['html_no_edit']);
						unset($f_rates[$f_rate_key]['html_selected']);
						unset($f_rates[$f_rate_key]['html_unselect']);
						unset($f_rates[$f_rate_key]['html_close']);
					}

					$fluid = new Fluid ();

					$fluid->php_db_begin();

					$tz = SERVER_TIMEZONE;
					$timestamp = time();
					$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
					$dt->setTimestamp($timestamp); //adjust the object to correct timestamp

					$f_sale_time = $dt->format('Y-m-d H:i:s'); //date('Y-m-d H:i:s'); // --> Current time.
					$f_u_id = !empty($_SESSION['u_id']) ? "'" . $fluid->php_escape_string($_SESSION['u_id']) . "'" : "NULL";
					$f_shipping_total = !empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['shipping']) ? "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['shipping']) . "'" : "NULL";

					$f_ship_split = 0;
					if(isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_ship_split']))
						if($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_ship_split'] == TRUE)
							$f_ship_split = 1;

					$f_tax_total = !empty($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['tax_total']) ? "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['tax_total']) . "'" : "NULL";
					$f_tax_json = !empty($t_taxes) ? "'" . $fluid->php_escape_string(json_encode($t_taxes)) . "'" : "NULL";

					$f_avs = "NULL";
					$f_cvd = "NULL";
					if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
						if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
							// --> If TRUE, then it failed.
							$receipt_charge = $result->transaction()->response()->receipt;

							$f_avs = "'1'";
							if(isset($receipt_charge->AvsResultCode)) {
								$f_failed_avs = FALSE;

								switch ($receipt_charge->AvsResultCode) {
									case 'A':
									case 'B':
									case 'C':
										$f_failed_avs = TRUE;
										break;
									case 'G':
									case 'I':
									case 'P':
									case 'S':
									case 'U':
									case 'X':
									case 'Z':
										$f_failed_avs = TRUE;
										break;
									case 'N':
										$f_failed_avs = TRUE;
										break;
									case 'R':
										$f_failed_avs = TRUE;
										break;
									default:
										$f_failed_avs = FALSE;
								}

								if($f_failed_avs == FALSE)
									$f_avs = "NULL";
							}

							$f_cvd = "'1'";
							if(isset($receipt_charge->CvdResultCode)) {
								// --> 1M = Passed. 1Y == Pased for AMEX and JCB cards. All other codes are either fails or doesn't participate or not processed.
								if((string) $receipt_charge->CvdResultCode == "1M" || (string) $receipt_charge->CvdResultCode == "1Y") {
									$f_cvd = "NULL";
								}
							}
						}
						else if(AUTH_NET_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
							// Check if it failed AVS
							// NOTE: Authorize.net control panel has settings to automatically decline cards that fail.
							$f_avs = "'1'";
							$f_auth_net_object = json_decode(base64_decode($f_auth_net_object_64));
							if(isset($f_auth_net_object->avsResultCode)) {
								$f_failed_avs = FALSE;

								switch ($f_auth_net_object->avsResultCode) {
									case 'A':
										$f_failed_avs = TRUE;
										break;
									case 'B':
										$f_failed_avs = TRUE;
										break;
									case 'E':
										$f_failed_avs = TRUE;
										break;
									case 'N':
										$f_failed_avs = TRUE;
										break;
									case 'R':
										$f_failed_avs = TRUE;
										break;
									case 'S':
										$f_failed_avs = TRUE;
										break;
									case 'U':
										$f_failed_avs = TRUE;
										break;
									case 'W':
										$f_failed_avs = TRUE;
										break;
									case 'Z':
										$f_failed_avs = TRUE;
										break;

									default:
										$f_failed_avs = FALSE;
								}

								if($f_failed_avs == FALSE) {
									$f_avs = "NULL";
								}
							}

							// Check if it failed CVD
							// NOTE: Authorize.net control panel has settings to automatically decline cards that fail.
							$f_cvd = "'1'";
							if(isset($f_auth_net_object->cvvResultCode)) {
								// If it does not have a failing code.
								if((string) $f_auth_net_object->cvvResultCode != "N") {
									$f_cvd = "NULL";
								}
							}
						}
						else if($fluid_in_store_order == TRUE) {
							$f_avs = "NULL";
							$f_cvd = "NULL";
						}
					}

					if(isset($_SERVER['REMOTE_ADDR'])) {
						$f_ip_address = $fluid->php_escape_string($_SERVER['REMOTE_ADDR']);
					}
					else {
						$f_ip_address = "";
					}

					if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
						$f_ip_forward = $fluid->php_escape_string($_SERVER['HTTP_X_FORWARDED_FOR']);
					}
					else {
						$f_ip_forward = "";
					}

					// Build a array of all the items, and update the stock with component stock if required.
					$s_items_64 = NULL;
					$fluid_stk_check = new Fluid();
					foreach($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'] as $s_key => $f_item) {
						if($f_item['p_component'] == TRUE) {
							$f_item['p_stock'] = $fluid_stk_check->php_process_stock($f_item);
						}

						$s_items_64[$s_key] = $f_item;
					}

					$f_sales_array = Array();
					$f_sales_array['s_order_number'] = "'" . $fluid->php_escape_string($f_order_id) . "'";
					$f_sales_array['s_sale_time'] = "'" . $fluid->php_escape_string($f_sale_time) . "'";
					$f_sales_array['s_u_id'] = $f_u_id;
					$f_sales_array['s_u_email'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']) . "'";
					$f_sales_array['s_cost_total'] = "'" .  $fluid->php_escape_string(round(php_cart_cost_sub_total($f_data->f_checkout_id), 2)) . "'";
					$f_sales_array['s_total'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total']) . "'";
					$f_sales_array['s_sub_total'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['sub_total']) . "'";
					$f_sales_array['s_shipping_total'] = $f_shipping_total;
					$f_sales_array['s_tax_total'] = $f_tax_total;
					$f_sales_array['s_taxes'] = $f_tax_json;
					$f_sales_array['s_address_name'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_name']) . "'";
					$f_sales_array['s_address_number'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number']) . "'";
					$f_sales_array['s_address_street'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']) . "'";
					$f_sales_array['s_address_city'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_city']) . "'";
					$f_sales_array['s_address_province'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_province']) . "'";
					$f_sales_array['s_address_postalcode'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode']) . "'";
					$f_sales_array['s_address_country'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_country']) . "'";
					$f_sales_array['s_address_phonenumber'] = "'" . $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber']) . "'";
					$f_sales_array['s_address_payment_64'] = "'" . $fluid->php_escape_string(base64_encode(json_encode($f_address_payment))) . "'";
					$f_sales_array['s_items_64'] = "'" . $fluid->php_escape_string(base64_encode(json_encode($s_items_64))) . "'";
					$f_sales_array['s_shipping_64'] = "'" . $fluid->php_escape_string(base64_encode(json_encode($f_rates))) . "'";
					$f_sales_array['s_status'] = '1';
					$f_sales_array['s_ship_split'] = "'" . $f_ship_split . "'";
					$f_sales_array['s_tracking'] = "NULL";
					$f_sales_array['s_ship_refund'] = "NULL";
					$f_sales_array['s_refund_total'] = "NULL";
					$f_sales_array['s_ip_address'] = "'" . $f_ip_address . "'";
					$f_sales_array['s_ip_address_forward'] = "'" . $f_ip_forward . "'";
					$f_sales_array['s_avs_failed'] = $f_avs;
					$f_sales_array['s_cvd_failed'] = $f_cvd;

					$f_transactions_array = Array();
					$f_transactions_array['st_s_order_number'] = "'" . $fluid->php_escape_string($f_order_id) . "'";

					// This is a Moneris transaction.
					if(empty($f_data->is_paypal) || $fluid_in_store_order == TRUE) {
						if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
							$f_transactions_array['st_s_transaction_serialize_64'] = "'" . $fluid->php_escape_string(base64_encode(serialize($transaction->response()->asXML()))) . "'";
						}
						else if(AUTH_NET_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
							$f_transactions_array['st_s_transaction_serialize_64'] = "'" . $fluid->php_escape_string($f_auth_net_object_64) . "'";
						}
						else {
							$f_transactions_array['st_s_transaction_serialize_64'] = "'" . $fluid->php_escape_string(base64_encode(json_encode(Array("IN_STORE_PICKUP" => "In Store pickup. The customer will pay in store.", "STATUS" => "Customer needs to pay during pickup.")))) . "'";	
						}

						// Let's build a clean fraud array data to send.
						$f_fraud_array = Array();
						$f_fraud_array['s_order_number'] = $fluid->php_escape_string($f_order_id);
						$f_fraud_array['s_sale_time'] = $fluid->php_escape_string($f_sale_time);
						$f_fraud_array['s_u_id'] = $f_u_id;
						$f_fraud_array['s_u_email'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']);
						$f_fraud_array['s_cost_total'] =  $fluid->php_escape_string(round(php_cart_cost_sub_total($f_data->f_checkout_id), 2));
						$f_fraud_array['s_total'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total']);
						$f_fraud_array['s_sub_total'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['sub_total']);
						$f_fraud_array['s_shipping_total'] = $f_shipping_total;
						$f_fraud_array['s_tax_total'] = $f_tax_total;
						$f_fraud_array['s_taxes'] = $f_tax_json;
						$f_fraud_array['s_address_name'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_name']);
						$f_fraud_array['s_address_number'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number']);
						$f_fraud_array['s_address_street'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']);
						$f_fraud_array['s_address_city'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_city']);
						$f_fraud_array['s_address_province'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_province']);
						$f_fraud_array['s_address_postalcode'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode']);
						$f_fraud_array['s_address_country'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_country']);
						$f_fraud_array['s_address_phonenumber'] = $fluid->php_escape_string($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber']);
						$f_fraud_array['s_address_payment_64'] = $fluid->php_escape_string(base64_encode(json_encode($f_address_payment)));
						$f_fraud_array['s_items_64'] = $fluid->php_escape_string(base64_encode(json_encode($s_items_64)));
						$f_fraud_array['s_shipping_64'] = $fluid->php_escape_string(base64_encode(json_encode($f_rates)));
						$f_fraud_array['s_status'] = '1';
						$f_fraud_array['s_ship_split'] = $f_ship_split;
						$f_fraud_array['s_tracking'] = "NULL";
						$f_fraud_array['s_ship_refund'] = "NULL";
						$f_fraud_array['s_refund_total'] = "NULL";
						$f_fraud_array['s_ip_address'] = $f_ip_address;
						$f_fraud_array['s_ip_address_forward'] = $f_ip_forward;
						$f_fraud_array['s_avs_failed'] = (int)str_replace("'", "", $f_avs);
						$f_fraud_array['s_cvd_failed'] = (int)str_replace("'", "", $f_cvd);

						if(MONERIS_ENABLED == TRUE && $fluid_in_store_order == FALSE) {
							// Lets do a fraud score check, if the fraud score is over 80%, then we must fail and void the transaction and tell the user to use PayPal instead.
							$f_fraud_check = new Fluid();
							$f_moneris_obj = NULL;

							// Process the shipping for fraud detection.
							$o_ship_fraud = NULL;
							if(isset($f_rates)) {
								foreach($f_rates as $t_ship_fraud) {
									$o_ship_fraud = $t_ship_fraud;
									break;
								}
							}

							// Lets build a moneris object.
							$simpleXmlElem = simplexml_load_string(unserialize(base64_decode($fluid->php_escape_string(base64_encode(serialize($transaction->response()->asXML()))))));

							if(isset($simpleXmlElem->receipt)) {
								foreach($simpleXmlElem->receipt as $key => $xml_data) {
									foreach($xml_data as $f_key => $f_data2) {
										$f_moneris_obj[$f_key] = $f_data2;
									}

									break;
								}
							}

							$f_fraud_data = $f_fraud_check->php_fluid_fraud_score($f_fraud_array, $f_moneris_obj, $o_ship_fraud, NULL);

							if($f_fraud_data['fraud_score'] >= 7) {
								$f_error_message = "Transaction Declined, there was a problem with your payment information. Please try using PayPal if you continue to have problems.";
								throw new Exception("Transaction Declined, there was a problem with your payment information. Please try using PayPal if you continue to have problems.");
							}
						}
						else if(AUTH_NET_ENABLED == TRUE || $fluid_in_store_order == TRUE) {
							// No need for fraud score checking. You can control avs and cvd security settings in your authorize.net control panel.
							// In store pickups, in this fashion will also be required to be charged in person when the customer picks up the order.
						}
					}
					else {
						// PayPal transaction.
						$f_transactions_array['st_s_transaction_serialize_64'] = "'" . $fluid->php_escape_string($f_data->paypal_object) . "'";
					}

					// --> Do a stock check.
					$f_tmp_stock = NULL;
					$where_stock = "WHERE p_id IN (";
					$i_stock = 0;

					foreach($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'] as $key_stock => $data_stock) {
						if($i_stock != 0) {
							$where_stock .= ", ";
						}

						$where_stock .= "'" . $fluid->php_escape_string($data_stock['p_id']) . "'";

						$i_stock++;
					}

					$where_stock .= ")";

					$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where_stock);
					$f_tmp_stock = NULL;

					if(isset($fluid->db_array)) {
						$fluid_stock = new Fluid();
						foreach($fluid->db_array as $stock_key => $stock_data) {
							// Component stock.
							if($stock_data['p_component'] == TRUE) {
								$stock_data['p_stock'] = $fluid_stock->php_process_stock($stock_data);
							}

							$f_tmp_stock[$stock_data['p_id']] = $stock_data;
						}
					}

					$f_enough_stock = TRUE;
					$f_some_in_stock = FALSE;
					if(isset($f_tmp_stock)) {
						foreach($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'] as $f_key_stock => $f_items_stock) {
							if(empty($f_items_stock['f_promo'])) {
								// --> Not enough stock.
								if($f_tmp_stock[$f_items_stock['p_id']]['p_stock'] < $f_items_stock['p_qty']) {
									$f_enough_stock = FALSE;
								}

								if($f_tmp_stock[$f_items_stock['p_id']]['p_stock'] > 0) {
									$f_some_in_stock = TRUE;
								}
							}
						}
					}
					// --> End of stock check.

					// --> Now record the sale.
					$f_columns = implode(", ", array_keys($f_sales_array));
					$f_values  = implode(", ", array_values($f_sales_array));
					$f_sales_query = "INSERT INTO " . TABLE_SALES . " (" . $f_columns . ") VALUES (" . $f_values . ");";

					$f_columns_trans = implode(", ", array_keys($f_transactions_array));
					$f_values_trans  = implode(", ", array_values($f_transactions_array));
					$f_sales_trans_query = "INSERT INTO " . TABLE_SALES_TRANSACTIONS . " (" . $f_columns_trans . ") VALUES (" . $f_values_trans . ");";

					$f_sales_items_query = "INSERT INTO " . TABLE_SALES_ITEMS . " (si_s_order_number, si_p_id, si_m_name, si_p_name, si_p_length, si_p_width, si_p_height, si_p_weight, si_p_price, si_p_cost, si_p_mfgcode, si_p_mfg_number, si_p_image, si_p_refund, si_serial_numbers, si_p_rebate_claim) VALUES";
					$f_items_stock_query = "UPDATE " . TABLE_PRODUCTS . " SET `p_date_hide` = NOW() + INTERVAL 7 DAY, `p_stock` = CASE";
					$f_items_p_in = NULL;
					$fi = 0;
					$fi_stock = 0;
					foreach($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'] as $f_items_key => $f_items_data) {
						// --> We do not want to update stock on promotional generated items as they are virtual and do not actually exist in the database. It is just a promo code.
						if(empty($f_items_data['f_promo'])) {
							if($fi > 0) {
								$f_items_p_in .= ", ";
							}

							$f_items_stock_query .= " WHEN (`p_id`) = ('" . $fluid->php_escape_string($f_items_data['p_id']) . "') THEN GREATEST(p_stock - " . $f_items_data['p_qty'] . ", 0)";

							$f_items_p_in .= "'" . $f_items_data['p_id'] . "'";

							$fi++;
						}

						if($fi_stock > 0) {
							$f_sales_items_query .= ",";
						}

						for($i = 0; $i < $f_items_data['p_qty']; $i++) {
							if($i > 0) {
								$f_sales_items_query .= ",";
							}

							$p_mfg_number = !empty($f_items_data['p_mfg_number']) ? "'" . $fluid->php_escape_string($f_items_data['p_mfg_number']) . "'" : "NULL";

							$f_sales_items_query .= " ('" . $fluid->php_escape_string($f_order_id) . "', '" . $fluid->php_escape_string($f_items_data['p_id']) . "', '" . $fluid->php_escape_string($f_items_data['m_name']) . "', '" . $fluid->php_escape_string($f_items_data['p_name']) . "', '" . $fluid->php_escape_string($f_items_data['p_length']) . "', '" . $fluid->php_escape_string($f_items_data['p_width']) . "', '" . $fluid->php_escape_string($f_items_data['p_height']) . "', '" . $fluid->php_escape_string($f_items_data['p_weight']) . "', '" . $fluid->php_escape_string($f_items_data['p_price']) . "', '" . $fluid->php_escape_string($f_items_data['p_cost']) . "', '" . $fluid->php_escape_string($f_items_data['p_mfgcode']) . "', " . $p_mfg_number . ", '" . $fluid->php_escape_string($f_items_data['p_image']) . "', NULL, NULL, '" . $fluid->php_escape_string($f_items_data['p_rebate_claim']) . "')";
						}

						$fi_stock++;
					}

					$f_sales_items_query .= ";";
					$f_items_stock_query .= " END WHERE p_id IN (" . $f_items_p_in . ")";

					// Record the sale.
					$fluid->php_db_query($f_sales_query);

					// Record the sale transaction.
					$fluid->php_db_query($f_sales_trans_query);

					// Update the stock numbers if not in sandbox mode.
					//if(FLUID_PAYMENT_SANDBOX == FALSE) {
						$fluid->php_db_query($f_items_stock_query);
					//}

					// Adds each item in a item sales table.
					$fluid->php_db_query($f_sales_items_query);

					// Need to update any discount date ends if required. This has to be after the stock update query.
					$fluid->php_db_query("SELECT p_id, p_component, p_stock, p_stock_end, p_discount_date_end FROM " . TABLE_PRODUCTS . " WHERE p_id IN (" . $f_items_p_in . ")");

					if(isset($fluid->db_array)) {
						if(isset($fluid->db_array)) {
							$c_set_date = "CASE";
							$i = 0;
							$fluid_s_check = new Fluid();
							foreach($fluid->db_array as $key => $db_data) {
								$c_set_date_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (p_discount_date_end)";

								if($db_data['p_component'] == TRUE) {
									$db_data['p_stock'] = $fluid_s_check->php_process_stock($db_data);
								}

								if(isset($db_data['p_stock_end']) && isset($db_data['p_stock'])) {
									if($db_data['p_stock_end'] == 1 && $db_data['p_stock'] < 1) {
										$f_date_end = strtotime($db_data['p_discount_date_end']);

										if($f_date_end > strtotime(date("Y-m-d H:i:s"))) {
											$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

											$c_set_date_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
										}
										else if(empty($db_data['p_discount_date_end'])) {
											$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

											$c_set_date_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
										}
									}
								}

								$c_set_date .= $c_set_date_tmp;
							}

							$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_discount_date_end` = " . $c_set_date . " END WHERE p_id IN (" . $f_items_p_in . ")");
						}
					}

					$fluid->php_db_commit();

					$f_order_saved = TRUE;

					$f_order_header = "Order # " . explode('-', $f_order_id)[2] . " confirmation.";
					$f_order = "<div>";
						$f_order .= "<div class='f-print-header-hide'>";
						$f_order .= "<div class='f-thank-you'>Thank you for your order</div>";
						$f_order .= "<div class='f-order-number'>Order number is: " . explode('-', $f_order_id)[2] . "</div>";
						$f_order .= "<div class='f-email-hide'>You will receive an email confirmation shortly at " . $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email'] . "</div>";
						$f_order .= "<div class='f-email-hide' style='font-weight: bold;'>Please check your spam folder if you do not see your confirmation email after a few minutes.</div>";

						$f_order .= "</div>";

						// --> This link href will have a unique code (email address base64 encoded + ordernumber without fluid in it) to open and view the receipt, which can be used for email links for non registered users as well.
						$f_order .= "<div class='f-extra-invoice' style='padding-bottom: 20px;'>";
							$f_order .= "<div class='f-thank-you'>Thank you for your order</div>";
							$f_order .= "<div>Order Number: " . explode('-', $f_order_id)[2] . "</div>";
							$f_order .= "<div>Order Date: " . $f_sale_time . "</div>";

							if($fluid_in_store_order == TRUE) {
								$s_address_payment = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address'];
							}
							else {
								$s_address_payment = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_payment_address'];
							}
							
							$sa_info_html = "<div>" . utf8_decode(($s_address_payment['a_name'])) . "</div>";

							$sa_info_html .= "<div>";
									if($s_address_payment['a_number'] != "")
										$sa_info_html .= utf8_decode($s_address_payment['a_number']) .  " - ";
									$sa_info_html .= utf8_decode($s_address_payment['a_street']);
							$sa_info_html .= "</div>";

							$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_city']) . " " . utf8_decode($s_address_payment['a_province']) . "</div>";
							$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_country']) . " " . utf8_decode($s_address_payment['a_postalcode']) . "</div>";
							$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_phonenumber']) . "</div>";

							if($fluid_in_store_order == FALSE) {
								$f_order .= "<div style='padding-top: 30px; padding-bottom: 30px;'>";
									
									if(empty($f_data->is_paypal)) {
										$f_order .= "<div style='display: inline-block; vertical-align: top;'>";
											$f_order .= "<div style='display: table;'>";
												$f_order .= "<div style='display: table-row; font-weight: 600;'>Billing information:</div>";
												$f_order .= "<div style='display: table-row;'> " . $sa_info_html . "</div>";
											$f_order .= "</div>";
										$f_order .= "</div>";
									}

									$sd_info_html = "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_name']) . "</div>";
									$sd_info_html .= "<div>";
											if($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number'] != "") {
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number']) .  " - ";
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']);
											}
											else {
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']);
											}
									$sd_info_html .= "</div>";

									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_city']) . " " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_province']) . "</div>";
									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_country']) . " " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode']) . "</div>";
									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber']) . "</div>";
									$sd_info_html .= "<div style='font-size: 12px; font-style: italic;'>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']) . "</div>";

									if(!empty($f_data->is_paypal)) {
										$f_padding_paypal = " padding-left: 0px;";
									}
									else {
										$f_padding_paypal = " padding-left: 80px;";
									}

									$f_order .= "<div style='display: inline-block; " . $f_padding_paypal . " vertical-align: top;'>";
										$f_order .= "<div style='display: table;'>";
											$f_order .= "<div style='display: table-row; font-weight: 600;'>Shipping information:</div>";
											$f_order .= "<div style='display: table-row;'> " . $sd_info_html . "</div>";
										$f_order .= "</div>";
									$f_order .= "</div>";
								$f_order .= "</div>";
							}
							else {
								$f_order .= "<div style='padding-top: 30px; padding-bottom: 30px;'>";
									
									if(empty($f_data->is_paypal)) {
										$f_order .= "<div style='display: inline-block; vertical-align: top;'>";
											$f_order .= "<div style='display: table;'>";
												$f_order .= "<div style='display: table-row; font-weight: 600;'>Customer information:</div>";
												$f_order .= "<div style='display: table-row;'> " . $sa_info_html . "</div>";
											$f_order .= "</div>";
										$f_order .= "</div>";
									}

									$sd_info_html = "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_name']) . "</div>";
									$sd_info_html .= "<div>";
											if($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number'] != "") {
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_number']) .  " - ";
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']);
											}
											else {
												$sd_info_html .= utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_street']);
											}
									$sd_info_html .= "</div>";

									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_city']) . " " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_province']) . "</div>";
									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_country']) . " " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_postalcode']) . "</div>";
									$sd_info_html .= "<div>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber']) . "</div>";
									$sd_info_html .= "<div style='font-size: 12px; font-style: italic;'>" . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']) . "</div>";

									$f_order .= "<div style='display: inline-block; padding-left: 0px; vertical-align: top;'>";
										$f_order .= "<div style='display: table;'>";
											$f_order .= "<div style='display: table-row; font-weight: 600;'>Shipping information:</div>";
											$f_order .= "<div style='display: table-row;'><div>In store pickup. You will be notified when your order is ready to be picked up.</div><div><small>* You must pay for your order in store during the pickup of your items.</small></div></div>";
										$f_order .= "</div>";
									$f_order .= "</div>";
								$f_order .= "</div>";
							}
						$f_order .= "</div>";

						$f_order_bottom = "
						<div class='f-social-checkout-div'>
							<div style='padding-top: 10px; font-weight: 400;'>Stay Connected</div>
							<div style='display: inline-block; padding: 5px;'>
								<a href=\"https://www.facebook.com/LeosVancouver\" target=\"_blank\"><div class='fa fa-3x fa-facebook-official' style='color: #3B5998;'></div></a>
							</div>
							<div style='display: inline-block; padding: 5px;'>
								<a href=\"https://twitter.com/LeosCamera\" target=\"_blank\"><div class='fa fa-3x fa-twitter-square' style='color: #2EAEF7;'></div></a>
							</div>

							<div style='display: inline-block; padding: 5px;'>
								<a href=\"https://www.youtube.com/c/LeosCameraSupplyTV\" target=\"_blank\"><div class='fa fa-3x fa-youtube-square' style='color: red;'></div></a>
							</div>
							<div style='display: inline-block; padding: 5px;'>
								<a href=\"https://www.instagram.com/leoscamerasupply/\" target=\"_blank\"><div class='fa fa-3x fa-instagram' style='color: black;'></div></a>
							</div>
						</div>";

						$f_checkout_data = Array("cart" => $_SESSION['f_checkout'][$f_data->f_checkout_id], "taxes" => $t_taxes);
						$f_detailed_data = php_fluid_order_detailed($f_checkout_data);

						$f_order_bottom .= "<div class='f-detailed-div'><a id='f-detailed-collapse-a' onClick='document.getElementById(\"f-detailed-div-expand-div\").innerHTML = Base64.decode(\"" . base64_encode($f_detailed_data['html']) . "\"); js_check_stack_status(this, \"collapsed\", \"f-detailed-chevron\"); js_fluid_block_animate(\"" . base64_encode(json_encode($f_detailed_data['f_animate_array'])) . "\");' data-toggle=\"collapse\" href=\"#f-row-receipt\" aria-expanded=\"false\" aria-controls=\"f-detailed-div\" class=\"collapsed\"><span id='f-detailed-chevron' class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span> Detailed Order Receipt</a></div>";
						$f_order_bottom .= "<div class='row collapse f-row-receipt-div-hide' id='f-row-receipt'>";
						// --> ** SHOW THE GENERATED CART ITEM LIST WITH PRICE/SHIPPING/TAX BREAKDOWN **
							$f_order_bottom .= "<div id='f-detailed-div-expand-div' style='padding: 0px 20px 0px 20px;'>";
								$f_order_bottom .= $f_detailed_data['html'];
							$f_order_bottom .= "</div>";
						$f_order_bottom .= "</div>";

						$f_order_bottom .= "<div id='f-refund-div' class='f-refund-detailed-div'><a id='f-return-collapse-a' onClick='' data-toggle=\"collapse\" href=\"#f-row-refund\" aria-expanded=\"false\" aria-controls=\"f-refund-div\" class=\"collapsed\" js_check_stack_status(this, \"collapsed\", \"f-detailed-chevron-refund\");><span id='f-detailed-chevron-refund' class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span> Return Policy</a></div>";
						$f_order_bottom .= "<div class='row collapse f-refund-div' id='f-row-refund'>";
							$f_order_bottom .= HTML_RETURN_POLICY;
						$f_order_bottom .= "</div>";

						$f_order_bottom .= "
							<div class='f-leos-info-div'>
								<div style='font-size: 18px; font-weight: 500; padding-bottom: 5px;'>Contact Us</div>
								<div><span class=\"icon-leos-logo-rotate\" style=\"font-size: 40px; color: red !important;\"></span></div>
								<div>Leo's Camera Supply</div>
								<div>1055 Granville Street</div>
								<div>Vancouver, British Columbia</div>
								<div>CANADA, V6Z1L4</div>
								<div>Phone #: 1-604-685-5331</div>
								<div>Email: sales@leoscamera.com</div>
							</div>
						";

					$f_order_bottom .= "</div>";

					$f_order_mid = "<div class='f-print-receipt'><a onClick='document.getElementById(\"fluid-print-div\").innerHTML = Base64.decode(\"" . base64_encode($f_order . $f_order_bottom) . "\");' document.getElementById(\"f-row-receipt\").style.height = \"100%\";' href=\"javascript:window.print();\"><span class='glyphicon glyphicon-print' aria-hidden='true'></span> Print Receipt</a></div>";

					$f_order = $f_order . $f_order_mid . $f_order_bottom;

					// --> Email a order receipt
					$f_email_message = "Thank you for shopping at leoscamera.com<br><br>";

					// --> Not enough stock to complete order right away.
					if($f_enough_stock == FALSE) {
						// --> Only display this if there are some items in stock.
						if($f_some_in_stock == TRUE) {
							if($f_ship_split == 1) {
								$f_email_message .= "Please note, not all items are currently in stock. As requested, we will ship or have ready for pickup all in stock items first while back ordered items will ship or ready for pickup separately as soon as they become available.<br><br>";
							}
							else {
								$f_email_message .= "Please note, not all items are currently in stock. As requested, your order will be ready to ship or pickup once all back ordered items are available.<br><br>";
							}
						}
						else {
							$f_email_message .= "Please note, not all items are currently in stock. Your order will ship once all back ordered items are available.<br><br>";
						}
					}
					else {
						$f_email_message .= "Your order should be processed and ready to ship shortly.<br><br>";
					}

					$f_email_message .= "Order Summary<br><br>";
					$f_email_message .= "Order number: " . explode('-', $f_order_id)[2] . "<br>";
					$f_email_message .= "Order Date: " . date("m-d-Y H:i");
					$f_email_message .= php_fluid_order_detailed_email($f_checkout_data);
					$f_email_message .= "<br><br>";

					// Prepare some extra information nthat we send to ourselves.
					$f_email_self_message = "Name: " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_name']);
					$f_email_self_message .= "<br>Phone Number: " . $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_phonenumber'];
					$f_email_self_message .= "<br>Email: " . utf8_decode($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email']);

					$f_email_data = addslashes(base64_encode(json_encode(Array("from" => "orders@leoscamera.com", "to" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address']['a_email'], "subject" => "Leo's Camera Supply " . $f_order_header, "message" => $f_email_message))));

					// Emails.
					// Lets send a copy to our order email address for safe keeping.
					$f_email_data_self = addslashes(base64_encode(json_encode(Array("from" => "orders@leoscamera.com", "to" => "orders@leoscamera.com", "subject" => "Leo's Camera Supply " . $f_order_header, "message" => $f_email_self_message . "<br><br>" . $f_email_message))));

					exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');

					// --> Send a copy of the order email to the orders account. Only does it on non testing orders.
					if($_SERVER['SERVER_NAME'] != "local.leoscamera.com") {
						exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data_self . '" > /dev/null &');
					}

					$execute_functions[]['function'] = "js_html_insert";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-header-div-checkout"), "html" => base64_encode($f_order_header))));

					$execute_functions[]['function'] = "js_html_insert";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div-checkout"), "html" => base64_encode($f_order))));

					$execute_functions[]['function'] = "js_modal_show_data";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-main-modal-checkout"))));

					$execute_functions[]['function'] = "js_ga_checkout";
					end($execute_functions);

					$f_ga_cart = $_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'];
					foreach($f_ga_cart as $f_ga_key => $f_ga_items) {
						if(isset($f_ga_items['p_cost']))
							unset($f_ga_cart[$f_ga_key]['p_cost']);
					}
					$f_ga_data['items'] = $f_ga_cart;

					$f_ga_ship = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['shipping'];
					if($f_ga_ship < 0)
						$f_ga_ship = 0;

					$f_ga_tax = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['tax_total'];
					if($f_ga_tax < 0)
						$f_ga_tax = 0;

					$f_ga_data['invoice'] = Array("id" => $f_order_id, "total" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'], "tax" => $f_ga_tax, "shipping" => $f_ga_ship);

					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_ga_data));

					unset($_SESSION['f_checkout']);
					unset($_SESSION['fluid_cart']);
				}
			}
			else {
				$fluid_log->php_db_begin();
				//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize(print_r($f_error_message, TRUE))) . "')");
				$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
				$fluid_log->php_db_commit();

				// --> Transaction failed.
				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode($f_error_message))));

				$execute_functions[]['function'] = "js_modal_show_data";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));
			}
		}
		else {
			$fluid_log->php_db_begin();
			//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize(print_r($f_error_message, TRUE))) . "')");
			$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
			$fluid_log->php_db_commit();

			// --> Transaction failed.
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode($f_error_message))));

			$execute_functions[]['function'] = "js_modal_show_data";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		// --> ******** TODO ********
		// --> ******** TODO ********
		// --> ******** TODO ********
			// --> 1. Need to build into php_fluid_error_cart() a database query to record the error so we can check. But the query must not be in a try / catch loop.? Maybe might not want to record this?
		// --> ******** TODO ********
		// --> ******** TODO ********
		// --> ******** TODO ********

		$fluid_log->php_db_begin();
		//$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize(print_r($err, TRUE))) . "')");
		$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('order error', '" . $fluid_log->php_escape_string(serialize($_SESSION)) . "')");
		$fluid_log->php_db_commit();

		// --> We did a charge possibly, lets check if it happened, if so, we need to void it as the order was never saved into the database.
		if(isset($result) && $f_order_saved == FALSE) {
			// Void the order if it was charged and approved.
			if($f_approved == TRUE) {
				$void = $moneris->void($result->transaction());
			}

			return php_fluid_error_cart($f_error_message . " Please try using PayPal if you continue to have problems.", TRUE, FLUID_CART, FALSE);
		}
		else {
			if($f_paypal == TRUE) {
				return php_fluid_error_cart("There was a error recording your order. Please email or call customer service to solve the problem. Please note your order payment was accepted, but we will need additional information to process your order.", TRUE, $_SERVER['SERVER_NAME']);
			}
			else {
				return php_fluid_error_cart($err->getMessage(), TRUE, FLUID_CART, FALSE);
			}
		}
	}
}

function php_fluid_order_detailed_email($f_checkout) {
	$fluid = new Fluid ();
	$html = NULL;

	if(isset($f_checkout)) {
		$html .= "<br>";

		foreach($f_checkout['cart']['fluid_cart'] as $key => $data) {
			$html .= "<br>" . $data['p_qty'] . " x (" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.) " . $data['m_name'] . " " . $data['p_name'];
		}

		$html .= "<br><br>";
		$html .= "Sub Total: " . HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['sub_total'], 2, ".", ",") . "<br>";

		if($f_checkout['cart']['f_totals']['shipping'] == 0) {
			if($f_checkout['cart']['f_rates'][$f_checkout['cart']['s_id']]['type'] == IN_STORE_PICKUP) {
				$in_store_html = NULL;
				if(ENABLE_IN_STORE_PICKUP_PAYMENT) {
					$in_store_html = ". You will be required to pay for your order in store when you pick up your items";
				}
				
				$html .= "In store pickup: You will be contacted once your order has been processed and ready for pickup" . $in_store_html . ".<br>";
			}
			else {
				$html .= "Shipping: FREE<br>";
			}
		}
		else {
			$html .= "Shipping: " . HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['shipping'], 2, ".", ",") . "<br>";
		}

		// Break down the taxes.
		if(isset($f_checkout['taxes'])) {
			foreach($f_checkout['taxes'] as $t_key => $t_data) {
				$tmp_total = 0;

				foreach($t_data['f_rates'] as $t_f_rates => $t_f_rates_data) {
					$tmp_total = round($tmp_total + $t_f_rates_data['t_total'], 2);
				}

				$tmp_total = round($t_data['p_total'] + $tmp_total, 2);

				$html .= $t_data['t_name'] . ": " . HTML_CURRENCY . " " . number_format($tmp_total, 2, '.', ',') . "<br>";
			}
		}

		$html .= "Total: " . HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['total'], 2, ".", ",");
	}

	return $html;
}

function php_fluid_order_detailed($f_checkout) {
	$fluid = new Fluid ();
	$html = NULL;
	$f_animate_id = NULL;
	/*
		$f_checkout
			['fluid_cart']
			['f_totals']['shipping']
			['f_totals']['tax_total']
			['taxes']
	*/

	if(isset($f_checkout)) {
		$html .= "<div name='fluid-cart-scroll' class='fluid-cart-no-scroll' style='max-width: 100%;'>";
		foreach($f_checkout['cart']['fluid_cart'] as $key => $data) {
			// Process the image.
			$width_height = $fluid->php_process_image_resize($data['p_image'], "60", "60");

			$html .= "<div class='fluid-cart'>";

			$html .= "<div class='divTable'>";
				$html .= "<div class='divTableBody'>";
					$html .= "<div class='divTableRow'>";
						$html .= "<div class='divTableCellOrders div-table-cell-print-image' style='vertical-align:middle; width: " . $width_height['width'] . "px;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px; min-width: 60px; max-width: 60px;' alt='Buy " . $data['m_name'] . " " . $data['p_name'] . "'></img></div>";
						$html .= "<div class='divTableCellOrders div-table-cell-print' style='vertical-align:middle; font-weight: 400;'>" . $data['m_name'] . " " . $data['p_name'];

						$html .= "<div style='padding-top: 1px; padding-bottom: 5px;'>";
						$html .= "<div style='display: inline-block; font-size: 9px;'>UPC # " . $data['p_mfgcode'] . "</div>";
							if(isset($data['p_mfg_number']))
								$html .= "<i class=\"fa fa-square\" style='font-size: 4px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 9px; font-weight: 300;'>MFR # " . $data['p_mfg_number'] . "</div>";
						$html .= "</div>";

						$html .= "<div style='padding-top: 2px;'><div class='pull-left' style='font-weight: 400;'>Qty: " . $data['p_qty'] . "</div><div class='pull-right' style='font-weight: 400;'>" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div>";

						$html .= "<div class='divTableCellOrders f-print-img-hide' style='vertical-align:middle; font-weight: 400; width: 100%;'><div style='display: inline-block; vertical-align:middle;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px; min-width: 60px; max-width: 60px;' alt='Buy " . $data['m_name'] . " " . $data['p_name'] . "'></img></div><div style='padding-left: 5px; vertical-align:middle; display: inline-block; width: 90%;'><div style='padding-top: 5px; width: 100%;'>" . $data['m_name'] . " " . $data['p_name'] . "</div>";

						$html .= "<div style='padding-top: 1px; padding-bottom: 5px;'>";
						$html .= "<div style='display: inline-block; font-size: 9px;'>UPC # " . $data['p_mfgcode'] . "</div>";
							if(isset($data['p_mfg_number']))
								$html .= "<i class=\"fa fa-square\" style='font-size: 4px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 9px; font-weight: 300;'>MFR # " . $data['p_mfg_number'] . "</div>";
						$html .= "</div>";

						$html .= "<div style='padding-top: 2px;'><div class='pull-left' style='font-weight: 400;'>Qty: " . $data['p_qty'] . "</div><div class='pull-right' style='font-weight: 400;'>" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div></div>";
					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";

			$html .= "</div>";
		}
		$html .= "</div>"; // fluid-cart-no-scroll


		$html .= "<div style='padding-top: 10px;'>";
			$html .= "<div class='divTable'>";
				$html .= "<div class='divTableBody'>";
					$html .= "<div id='fluid-cart-totals' class='divTableRowOrders pull-right fluid-cart-subtotal' style='font-weight: 400 !important;'>";

					$html .= "<div style='display: table;'>";

						$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row-order-detailed"), "delay" => 0, "colour" => "#0050FF");
						$html .= "<div name='fluid-sub-total-row-order-detailed' id='fluid-sub-total-row-order-detailed' style='text-align: right;'>"; // --> This div is used for animating.
							$html .= "<div style='display: table-row;'>";
								$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Sub Total: </div><div style='display: table-cell; text-align: right;'> " . HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['sub_total'], 2, ".", ",") . "</div>";
							$html .= "</div>";
						$html .= "</div>";

						// Shipping information
						$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row-order-detailed"), "delay" => 250, "colour" => "#5EFF00");

						if($f_checkout['cart']['f_totals']['shipping'] == 0)
							$f_shipping_total_html = "FREE";
						else
							$f_shipping_total_html = HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['shipping'], 2, ".", ",");

						$html .= "<div name='fluid-shipping-row-order-detailed' id='fluid-shipping-row-order-detailed' style='text-align: right;'>"; // --> This div is used for animating.
							$html .= "<div style='display: table-row;'>";
							if($f_checkout['cart']['f_rates'][$f_checkout['cart']['s_id']]['type'] == IN_STORE_PICKUP) {
								$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>In store pickup:</div><div style='display: table-cell; text-align: right;'>" . $f_shipping_total_html . " </div>";
							}
							else {
								$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Shipping:</div><div style='display: table-cell; text-align: right;'>" . $f_shipping_total_html . " </div>";
							}
							$html .= "</div>";
						$html .= "</div>";

						// Break down the taxes.
						if(isset($f_checkout['taxes'])) {
							foreach($f_checkout['taxes'] as $t_key => $t_data) {
								$tmp_total = 0;

								foreach($t_data['f_rates'] as $t_f_rates => $t_f_rates_data) {
									$tmp_total = round($tmp_total + $t_f_rates_data['t_total'], 2);
								}

								$tmp_total = round($t_data['p_total'] + $tmp_total, 2);

								$f_animate_id[] = Array("id" => base64_encode("fluid-tax-row-order-detailed-" . $t_key), "delay" => 250, "colour" => "#FF006B"); // --> id name for the animation div. This will be procssed by js_fluid_block_animate();
								$html .= "<div name='fluid-tax-row-order-detailed-" . $t_key . "' id='fluid-tax-row-order-detailed-" . $t_key . "' style='text-align: right;'>"; // --> This div is used for animating.
									$html .= "<div id='tax-" . $t_key . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_data['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " ". number_format($tmp_total, 2, '.', ',') . "</div></div>";
								$html .= "</div>";
							}
						}

						$f_total_final_price_html = HTML_CURRENCY . " " . number_format($f_checkout['cart']['f_totals']['total'], 2, ".", ",");

						$f_animate_id[] = Array("id" => base64_encode("fluid-total-row-order-detailed"), "delay" => 250, "colour" => "#FFD600");
						$html .= "<div name='fluid-total-row-order-detailed' id='fluid-total-row-order-detailed' style='text-align: right;'>"; // --> This div is used for animating.
							$html .= "<div style='display: table-row; text-align: right;'>";
								$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Total:</div><div id='fluid-total-cart-row' style='display: table-cell; text-align: right;'>" . $f_total_final_price_html . "</div>";
							$html .= "</div>";
						$html .= "</div>";

					$html .= "</div>"; //table


					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";

		$html .= "</div>";
	}

	return Array("html" => $html, "f_animate_array" => $f_animate_id);
}

// Calculate taxes on the items in the cart based on a selected shipping address in $a_data and all the shipping options.
function php_fluid_taxes($data) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		// Check if we are a in store pickup, if so, we are going to charge taxes from the store's jursidcition.
		foreach($data['f_rates'] as $t_ship) {
			$f_ship = $t_ship;
		}

		if($f_ship['type'] == IN_STORE_PICKUP) {
			$t_region = "BC";
			$t_country = "CA";
		}
		else {
			$t_region = $data['a_data']['a_province_code'];
			$t_country = $data['a_data']['a_country_iso3116'];
		}

		$fluid->php_db_query("SELECT * FROM " . TABLE_TAXES . " WHERE t_region = '" . $fluid->php_escape_string($t_region) . "' AND t_country = '" . $fluid->php_escape_string($t_country) . "' ORDER BY t_id");

		$taxes = NULL;

		if(isset($fluid->db_array)) {
			$eq = new eqEOS();

			foreach($fluid->db_array as $key => $t_data) {
				$taxes[$t_data['t_id']] = $t_data;
				$taxes[$t_data['t_id']]['p_total'] = 0;
				$taxes[$t_data['t_id']]['f_rates'] = NULL;

				// Item taxes per item. Other parts of fluid.cart use this to calculate a full tax total.
				foreach($data['f_cart'] as $p_key => $p_data) {
					$equation = str_replace("[f_item]", $p_data['p_qty'] * $p_data['p_price'], $t_data['t_math']);
					$taxes[$t_data['t_id']]['p_total'] = $taxes[$t_data['t_id']]['p_total'] + round($eq->solveIF($equation), 2);
				}

				// Taxes by sub total.
				/*
				$p_sub_total_tmp = php_cart_sub_total(NULL, $data['f_cart']) - php_discount_cart($data['f_cart']);
				$equation = str_replace("[f_item]", $p_sub_total_tmp, $t_data['t_math']);
				$taxes[$t_data['t_id']]['p_total'] =  round($eq->solveIF($equation), 2);
				*/

				// Shipping taxes.
				foreach($data['f_rates'] as $f_key => $f_data) {
					$equation = str_replace("[f_item]", $f_data['price'], $t_data['t_math']);
					$taxes[$t_data['t_id']]['f_rates'][$f_data['s_id']]['t_total'] = round($eq->solveIF($equation), 2);
				}
			}
		}

		$fluid->php_db_query("SELECT * FROM " . TABLE_TAXES . " WHERE t_region = '' AND t_country = '' ORDER BY t_id");

		// Now apply any general taxes that may apply to anybody.
		if(isset($fluid->db_array)) {
			$eq = new eqEOS();

			foreach($fluid->db_array as $key => $t_data) {
				$taxes[$t_data['t_id']] = $t_data;
				$taxes[$t_data['t_id']]['p_total'] = 0;
				$taxes[$t_data['t_id']]['f_rates'] = NULL;

				// Item taxes per item. Other parts of fluid.cart use this to calculate a full tax total.
				foreach($data['f_cart'] as $p_key => $p_data) {
					$equation = str_replace("[f_item]", $p_data['p_qty'] * $p_data['p_price'], $t_data['t_math']);
					$taxes[$t_data['t_id']]['p_total'] = $taxes[$t_data['t_id']]['p_total'] + round($eq->solveIF($equation), 2);
				}

				// Shipping taxes.
				foreach($data['f_rates'] as $f_key => $f_data) {
					$equation = str_replace("[f_item]", $f_data['price'], $t_data['t_math']);
					$taxes[$t_data['t_id']]['f_rates'][$f_data['s_id']]['t_total'] = round($eq->solveIF($equation), 2);
				}

			}
		}

		$fluid->php_db_commit();

		return $taxes;
	}
	catch (Exception $err) {
		return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
	}
}

// Generate a list of items for the shopping cart based on the $_SESSION cart variables.
function php_html_cart($p_id = NULL, $cart_reset = FALSE, $checkout = FALSE, $btn_id = NULL, $tmp_data = NULL, $ship_override = NULL, $f_accessory_array = NULL) {
	try {
		$fluid = new Fluid ();

		$f_cart_tmp = FALSE;
		$f_checkout_id = NULL;
		$f_stock_error = 0;

		if($checkout == TRUE) {
			if(isset($_SESSION['f_checkout'])) {
				foreach($_SESSION['f_checkout'] as $id_tmp => $id_data) {
					$f_checkout_id = $id_tmp;
					break;
				}
			}

			if(isset($f_checkout_id)) {
				if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'])) {
					$f_cart_tmp = $_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'];
				}
			}
		}
		else {
			if(isset($_SESSION['fluid_cart'])) {
				$f_cart_tmp = $_SESSION['fluid_cart'];
			}
		}

		if(!empty($f_cart_tmp)) {
			// --> Scan the cart and apply Formula Link hooks.
			$fl_item_formula = NULL;
			$f_faux_list = NULL;

			// --> Scan and record each Formula Link item.
			foreach($f_cart_tmp as $fl_key => $fl_item) {
				// Remove any promo coupons, as we will recalculate them on each cart load.
				if(isset($fl_item['f_promo'])) {
					unset($f_cart_tmp[$fl_key]);
				}
				else if($fl_item['p_formula_status'] == 1 && $fl_item['p_formula_operation'] != FORMULA_OPTION_7 && ((strtotime($fl_item['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fl_item['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fl_item['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fl_item['p_formula_discount_date_end'] == NULL) || ($fl_item['p_formula_discount_date_start'] == NULL && $fl_item['p_formula_discount_date_end'] == NULL)) ) {
					// --> Formula link is active, now apply the magic only if there is a math formula.
					if(isset($fl_item_formula[$fl_item['p_mfgcode']]['qty']))
						$fl_item_formula[$fl_item['p_mfgcode']]['qty'] = $fl_item_formula[$fl_item['p_mfgcode']]['qty'] + $fl_item['p_qty'];
					else
						$fl_item_formula[$fl_item['p_mfgcode']]['qty'] = $fl_item['p_qty'];

					$fl_item_formula[$fl_item['p_mfgcode']]['data'] = $fl_item;
					$fl_item_formula[$fl_item['p_mfgcode']]['p_id'] = $fl_item['p_id'];

					if($fl_item['p_formula_items_data'] != '') {
						$f_match_items = (array)json_decode($fl_item['p_formula_items_data']);

						if(count($f_match_items) < 1)
							$f_match_items = NULL;
					}

					if($fl_item['p_formula_items_faux_data'] != '') {
						$f_match_items_faux = (array)(json_decode($fl_item['p_formula_items_faux_data']));

						if(count($f_match_items_faux) < 1)
							$f_match_items_faux = NULL;
					}

					$fl_match_items = NULL;
					// --> Scan for matching items.
					if(isset($f_match_items)) {
						foreach($f_cart_tmp as $fm_key => $fm_item) {
							if(in_array(base64_encode($fm_item['p_mfgcode']), $f_match_items)) {
								if(isset($fl_match_items[$fm_item['p_mfgcode']])) {
									$fl_match_items[$fm_item['p_mfgcode']] = $fl_match_items[$fm_item] + $fm_item['p_qty'];
								}
								else {
									$fl_match_items[$fm_item['p_mfgcode']] = $fm_item['p_qty'];
								}
							}
						}
					}

					$fl_item_formula[$fl_item['p_mfgcode']]['items_match'] = $fl_match_items;

					$fl_match_items_faux = NULL;
					// --> Scan for matching items faux.
					if(isset($f_match_items_faux)) {
						foreach($f_cart_tmp as $fm_key => $fm_item) {
							if(in_array(base64_encode($fm_item['p_mfgcode']), $f_match_items_faux)) {
								if(isset($fl_match_items_faux[$fm_item['p_mfgcode']])) {
									$fl_match_items_faux[$fm_item['p_mfgcode']] = $fl_match_items_faux[$fm_item] + $fm_item['p_qty'];
								}
								else {
									$fl_match_items_faux[$fm_item['p_mfgcode']] = $fm_item['p_qty'];
								}
							}
						}
					}

					$fl_item_formula[$fl_item['p_mfgcode']]['items_match_faux'] = $fl_match_items_faux;

					if(isset($fl_match_items_faux)) {
						$f_faux_list[$fl_item['p_mfgcode']] = $fl_match_items_faux;
					}
				}
			}

			// --> Now lets run through the faux lists if any exist and remove items that may need to be removed before operations begin.
			$f_item_list = NULL;
			if(isset($f_faux_list) && isset($fl_item_formula)) {
				foreach($f_faux_list as $f_mfgcode => $f_list) {
					for($i = 1; $i <= $fl_item_formula[$f_mfgcode]['qty']; $i++) {
						foreach($f_list as $f_list_mfg_code => $f_list_qty) {
							for($x = 1; $x <= $f_list_qty; $x++) {
								$f_item_list[$f_mfgcode][$f_list_mfg_code] = $f_list_mfg_code;

								if($x > $f_list_qty)
									break;
							}

							if(count($f_item_list[$f_mfgcode]) == $fl_item_formula[$f_mfgcode]['qty'])
								break;

						}

						if(count($f_item_list[$f_mfgcode]) == $fl_item_formula[$f_mfgcode]['qty'])
							break;
					}
				}

				// --> Now remove items from the master formula list as required by the faux listings.
				foreach($f_faux_list as $f_mfgcode => $f_list) {
					if(isset($f_item_list[$f_mfgcode])) {
						foreach($f_list as $f_mfgcode_tmp => $f_qty) {
							if(isset($f_item_list[$f_mfgcode][$f_mfgcode_tmp])) {
								// --> Do nothing
							}
							else {
								if(isset($fl_item_formula[$f_mfgcode_tmp]))
									unset($fl_item_formula[$f_mfgcode_tmp]);
							}
						}
					}
				}
			}

			// --> Lets scan our categories and prepare for category formulas
			$f_cat_tmp = NULL;
			foreach($f_cart_tmp as $f_cats) {
				$f_cat_tmp[$f_cats['p_catid']]['items'][$f_cats['p_id']] = $f_cats;
			}

			if(isset($f_cat_tmp)) {
				$fluid->php_db_begin();

				$where = "WHERE c_id IN (";
				$i = 0;
				foreach($f_cat_tmp as $c_key => $cats) {
					if($i != 0)
						$where .= ", ";

					$where .= $fluid->php_escape_string($c_key);

					$i++;
				}
				$where .= ")";

				$fluid->php_db_query("SELECT c.* FROM " . TABLE_CATEGORIES . " c " . $where . " AND c.c_enable = 1 AND c.c_formula_status = 1 ORDER BY c.c_id ASC LIMIT 1");

				$fluid->php_db_commit();

				if(isset($fluid->db_array)) {
					$f_cat_formula_update = FALSE;
					foreach($fluid->db_array as $cat_tmp) {
						$c_form_tmp = explode(";", $cat_tmp['c_formula_math']);
						$c_qty = explode("=", $c_form_tmp[0]);
						$c_qty = preg_replace('/\s+/', '', $c_qty[1]);

						$f_total_price = NULL;
						$f_total_qty = NULL;

						foreach($f_cat_tmp[$cat_tmp['c_id']]['items'] as $f_tmp_items) {
							$f_total_price = $f_total_price + ($f_tmp_items['p_price'] * $f_tmp_items['p_qty']);
							$f_total_qty = $f_total_qty + $f_tmp_items['p_qty'];
						}

						// Qty exceeds or equals the amount required for this formula, lets create the coupon.
						if($f_total_qty >= $c_qty) {
							/*
							$f_vars = FORMULA_VARIABLES;
							$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

							$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

							$parser = new StdMathParser();

							$AST = $parser->parse($f_formula);

							// --> Evaluate the expression.
							$evaluator = new Evaluator();

							$value = $AST->accept($evaluator);
							*/
							$f_formula = str_replace("[TOTAL_PRICE]", (string)$f_total_price, (string)$c_form_tmp[1]);
							$parser = new StdMathParser();

							$AST = $parser->parse((string)$f_formula);

							$evaluator = new Evaluator();

							$value = $AST->accept($evaluator);

							$value = -1 * abs($value);

							// --> Lets create a promotional coupon.
							if(isset($value)) {
								$f_cat_formula_update = TRUE;
								$dh_tmp_item = NULL;

								$dh_tmp_item['p_width'] = 0.0001;
								$dh_tmp_item['p_height'] = 0.0001;
								$dh_tmp_item['p_length'] = 0.0001;
								$dh_tmp_item['p_weight'] = 0.0001;

								$dh_tmp_item['p_qty'] = 1;
								$dh_tmp_item['p_buyqty'] = 1;
								$dh_tmp_item['p_price_map'] = $value;
								$dh_tmp_item['p_stock'] = 1;
								$dh_tmp_item['p_price'] = $value;
								$dh_tmp_item['p_cost'] = $value;
								$dh_tmp_item['p_mfgcode'] = $cat_tmp['c_id'] . "-promo";
								$dh_tmp_item['p_mfg_number'] = $cat_tmp['c_id'] . "-promo";
								$dh_tmp_item['p_name'] = "PROMO";
								$dh_tmp_item['f_promo'] = TRUE;
								$dh_tmp_item['p_id'] = "promo-" . $cat_tmp['c_id'] . "-promo";
								$dh_tmp_item['p_namenum'] = 0;
								$dh_tmp_item['p_rebate_claim'] = 0;

								$dh_tmp_item['m_name'] = $cat_tmp['c_name'];

								// Process the image.
								$c_images = $fluid->php_process_images($cat_tmp['c_image']);
								$f_img_name = str_replace(" ", "_", $dh_tmp_item['m_name'] . "_" . $dh_tmp_item['p_name'] . "_" . $dh_tmp_item['p_mfgcode']);
								$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

								$width_height = $fluid->php_process_image_resize($c_images[0], "60", "60", $f_img_name);
								$dh_tmp_item['p_image'] = $c_images[0];//IMG_NO_IMAGE;
								$dh_tmp_item['p_width_height'] = $width_height;

								$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
							}
						}
					}

					if($f_cat_formula_update == TRUE) {
						// --> Save the updated cart into the $_SESSION.
						if(isset($f_checkout_id)) {
							if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
								$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
						}
						else
							if(isset($_SESSION['fluid_cart']))
								$_SESSION['fluid_cart'] = $f_cart_tmp;
					}
				}
			}

			// --> Lets rebuild the cart with special discount items and split them up as required.
			if(isset($fl_item_formula)) {
				// --> Now rebuild the cart with the new hook items.
				foreach($fl_item_formula as $f_key_number_tmp => $f_key_tmp) {
					switch($f_key_tmp['data']['p_formula_operation']) {
						// --> If at least one of the selected items are in the cart.
						case FORMULA_OPTION_1:
							// --> Remove all the hook items from the cart. (Bug, this will break if multiple items are saved in the cart which have formulas).
							//foreach($fl_item_formula as $f_key_tmp)
								//unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

							// --> Remove all hook items of this particular item from the cart.
							unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

							$dh_tmp_qty = $f_key_tmp['qty'];
							$dh_found_match = 0;
							if(isset($f_key_tmp['items_match'])) {
								foreach($f_key_tmp['items_match'] as $fm_num) {
									$dh_found_match = $dh_found_match + $fm_num;
								}
							}

							// Rebuild the cart and apply the math if required.
							if($dh_found_match > 0) {
								if($f_key_tmp['qty'] > $dh_found_match)
									$dh_tmp_qty = $dh_found_match;

								$dh_tmp_item = $f_key_tmp['data'];
								$dh_tmp_item['p_qty'] = $dh_tmp_qty;

								if(isset($dh_tmp_item['p_org_price']))
									$dh_tmp_item['p_price'] = $dh_tmp_item['p_org_price'];

								$dh_tmp_item['p_org_price'] = $dh_tmp_item['p_price'];

								if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
									// --> Apply the math formula.
									$f_formula = $f_key_tmp['data']['p_formula_math'];

									$f_vars = FORMULA_VARIABLES;
									$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

									$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

									$parser = new StdMathParser();

									$AST = $parser->parse($f_formula);

									// --> Evaluate the expression.
									$evaluator = new Evaluator();

									$value = $AST->accept($evaluator);
								}
								else
									$value = $dh_tmp_item['p_price'];

								if($value <= 0)
									$value = $dh_tmp_item['p_price'];

								$dh_tmp_item['p_price'] = $value;

								$f_cart_tmp[$dh_tmp_item['p_id'] . "-disc"] = $dh_tmp_item;

								// --> Now put any extra leftovers back at regular price.
								if($f_key_tmp['qty'] - $dh_tmp_qty > 0) {
									$dh_tmp_item['p_qty'] = $f_key_tmp['qty'] - $dh_tmp_qty;

									if(isset($dh_tmp_item['p_org_price']))
										$dh_tmp_item['p_price'] = $dh_tmp_item['p_org_price'];

									$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
								}
							}
							else {
								$f_cart_tmp[$f_key_tmp['data']['p_id']] = $f_key_tmp['data'];
							}

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else
								if(isset($_SESSION['fluid_cart']))
									$_SESSION['fluid_cart'] = $f_cart_tmp;
						break;

						case FORMULA_OPTION_2:
							// --> Option 2 just runs the formula on the item. It also ran faux operations above. Item list is ignored.
							// --> Remove all hook items of this particular item from the cart.
							unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

							$dh_tmp_qty = $f_key_tmp['qty'];

							$dh_tmp_item = $f_key_tmp['data'];
							$dh_tmp_item['p_qty'] = $dh_tmp_qty;

							if(isset($dh_tmp_item['p_org_price']))
								$dh_tmp_item['p_price'] = $dh_tmp_item['p_org_price'];

							$dh_tmp_item['p_org_price'] = $dh_tmp_item['p_price'];

							if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
							// --> Apply the math formula.
								$f_formula = $f_key_tmp['data']['p_formula_math'];

								$f_vars = FORMULA_VARIABLES;
								$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

								$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

								$parser = new StdMathParser();

								$AST = $parser->parse($f_formula);

								// --> Evaluate the expression.
								$evaluator = new Evaluator();

								$value = $AST->accept($evaluator);
							}
							else
								$value = $dh_tmp_item['p_price'];

							if($value <= 0)
								$value = $dh_tmp_item['p_price'];

							$dh_tmp_item['p_price'] = $value;

							$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else
								if(isset($_SESSION['fluid_cart']))
									$_SESSION['fluid_cart'] = $f_cart_tmp;
						break;

						case FORMULA_OPTION_3:
							// --> Option 3 just runs the formula on the item. It also ran faux operations above. Item list is taken into account when running the formula operation or not.

							// --> Remove all hook items of this particular item from the cart.
							unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

							$dh_tmp_qty = $f_key_tmp['qty'];
							$dh_found_match = 0;
							if(isset($f_key_tmp['items_match'])) {
								foreach($f_key_tmp['items_match'] as $fm_num)
									$dh_found_match = $dh_found_match + $fm_num;
							}

							// Rebuild the cart and apply the math if required.
							if($dh_found_match > 0) {
								if($f_key_tmp['qty'] > $dh_found_match)
									$dh_tmp_qty = $dh_found_match;

								$dh_tmp_item = $f_key_tmp['data'];
								$dh_tmp_item['p_qty'] = $dh_tmp_qty;

								if(isset($dh_tmp_item['p_org_price']))
									$dh_tmp_item['p_price'] = $dh_tmp_item['p_org_price'];

								$dh_tmp_item['p_org_price'] = $dh_tmp_item['p_price'];

								if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
									// --> Apply the math formula.
									$f_formula = $f_key_tmp['data']['p_formula_math'];

									$f_vars = FORMULA_VARIABLES;
									$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

									$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

									$parser = new StdMathParser();

									$AST = $parser->parse($f_formula);

									// --> Evaluate the expression.
									$evaluator = new Evaluator();

									$value = $AST->accept($evaluator);
								}
								else
									$value = $dh_tmp_item['p_price'];

								if($value <= 0)
									$value = $dh_tmp_item['p_price'];

								$dh_tmp_item['p_price'] = $value;

								$f_cart_tmp[$dh_tmp_item['p_id'] . "-disc"] = $dh_tmp_item;

								// --> Now put any extra leftovers back at regular price.
								if($f_key_tmp['qty'] - $dh_tmp_qty > 0) {
									$dh_tmp_item['p_qty'] = $f_key_tmp['qty'] - $dh_tmp_qty;

									if(isset($dh_tmp_item['p_org_price']))
										$dh_tmp_item['p_price'] = $dh_tmp_item['p_org_price'];

									$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
								}
							}
							else {
								$f_cart_tmp[$f_key_tmp['data']['p_id']] = $f_key_tmp['data'];
							}

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else
								if(isset($_SESSION['fluid_cart']))
									$_SESSION['fluid_cart'] = $f_cart_tmp;
						break;

						case FORMULA_OPTION_7:
							// --> We do nothing. Displaying a message on item pages only.
						break;

						case FORMULA_OPTION_8:
							// --> Option 8. Creates a promotion coupon into the cart and shows bundles on the site.
							// --> Before all the case statements, all promotion codes are removed from the cart, they'll get calculated and re-added each time the cart is displayed.
							// --> Check to make sure taxes are calculated correctly.
							// --> Check to make sure sales are recorded corrected and with taxes saved per item right, and refunds check that.
							// --> Check error log, promo-id-promo not found when loading checkout.
							// --> Have bundle items generate, and when add to cart, it adds the 2 items into the cart together. (Need to build a check if our of stock items not shown, then the bundle doesnt show.
							// --> Modify the bundles to show these and not show the lens by itself with message when FORMULA_OPTION_8 is selected.

							$dh_tmp_qty = $f_key_tmp['qty'];
							$dh_found_match = 0;
							if(isset($f_key_tmp['items_match'])) {
								foreach($f_key_tmp['items_match'] as $fm_num)
									$dh_found_match = $dh_found_match + $fm_num;
							}

							if($dh_found_match > 0) {
								$dh_tmp_item = $f_key_tmp['data'];

								if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
									// --> Apply the math formula.
									$f_formula = $f_key_tmp['data']['p_formula_math'];

									$f_vars = FORMULA_VARIABLES;
									$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

									$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

									$parser = new StdMathParser();

									$AST = $parser->parse($f_formula);

									// --> Evaluate the expression.
									$evaluator = new Evaluator();

									$value = $AST->accept($evaluator);

									$value = $dh_tmp_item['p_price_map'] - $value;
									$value = -1 * abs($value);
								}

								// --> Lets create a promotional coupon.
								if(isset($value)) {
									if($dh_found_match > $f_key_tmp['qty']) {
										$dh_tmp_qty = $f_key_tmp['qty'];
									}
									else {
										$dh_tmp_qty = $dh_found_match;
									}

									$dh_tmp_item['p_image'] = IMG_NO_IMAGE;
									$dh_tmp_item['p_width'] = 0.0001;
									$dh_tmp_item['p_height'] = 0.0001;
									$dh_tmp_item['p_length'] = 0.0001;
									$dh_tmp_item['p_weight'] = 0.0001;

									$dh_tmp_item['p_qty'] = $dh_tmp_qty;
									$dh_tmp_item['p_price_map'] = $value;
									$dh_tmp_item['p_stock'] = $dh_tmp_qty;
									$dh_tmp_item['p_price'] = $value;
									$dh_tmp_item['p_cost'] = $value;
									$dh_tmp_item['p_mfgcode'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_mfg_number'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_name'] = $f_key_tmp['data']['p_name'] . " PROMO";
									$dh_tmp_item['f_promo'] = TRUE;
									$dh_tmp_item['p_id'] = "promo-" . $dh_tmp_item['p_id'] . "-promo";

									$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
								}
							}

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else
								if(isset($_SESSION['fluid_cart']))
									$_SESSION['fluid_cart'] = $f_cart_tmp;
						break;

						case FORMULA_OPTION_9:
							// --> Option 9. Creates a promotion coupon into the cart. Similar to FORMULA_OPTION_8 creates a bundle and it forces the items to be at it's original price if it is already discounted.
							$dh_tmp_qty = $f_key_tmp['qty'];
							$dh_found_match = 0;

							if(isset($f_key_tmp['items_match'])) {
								foreach($f_key_tmp['items_match'] as $fm_num_key => $fm_num) {
									// Need to make a temp variable and have this execute if the math parser parses the item and makes a promo coupon. ?
									$fm_num = $fl_item_formula[$f_key_number_tmp]['items_match'][$fm_num_key];

								//echo $fm_num . "<br>";
									$dh_found_match = $dh_found_match + $fm_num;

								}
							}

							if($dh_found_match > 0) {
								$dh_tmp_item = $f_key_tmp['data'];

								if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
									// --> Apply the math formula.
									$f_formula = $f_key_tmp['data']['p_formula_math'];

									$f_vars = FORMULA_VARIABLES;
									$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

									$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

									$parser = new StdMathParser();

									$AST = $parser->parse($f_formula);

									// --> Evaluate the expression.
									$evaluator = new Evaluator();

									$value = $AST->accept($evaluator);

									$value = $dh_tmp_item['p_price_map'] - $value;
									$value = -1 * abs($value);
								}

								// --> Lets create a promotional coupon.
								if(isset($value)) {
									if($dh_found_match > $f_key_tmp['qty']) {
										$dh_tmp_qty = $f_key_tmp['qty'];
									}
									else {
										$dh_tmp_qty = $dh_found_match;
									}

									foreach($fl_item_formula as $f_key_form_adj => $f_key_tmp_adj) {
										if($f_key_tmp_adj['p_id'] != $f_key_tmp['p_id']) {
											switch($f_key_tmp_adj['data']['p_formula_operation']) {
												case FORMULA_OPTION_9:
													if(isset($f_key_tmp['items_match'])) {
														foreach($f_key_tmp['items_match'] as $fm_num_key_tmp => $fm_num_tmp) {
															if(isset($f_key_tmp_adj['items_match'][$fm_num_key_tmp])) {
																//$f_key_tmp_adj['items_match'][$fm_num_key_tmp]
																if($f_key_tmp_adj['items_match'][$fm_num_key_tmp] - $dh_tmp_qty < 0) {
																	$fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] = 0;
																}
																else {
																	//echo "test";
																	$fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] = $fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] - $dh_tmp_qty;
																}
															}

														}
													}
												break;
											}
										}
									}

									$dh_tmp_item['p_image'] = IMG_NO_IMAGE;
									$dh_tmp_item['p_width'] = 0.0001;
									$dh_tmp_item['p_height'] = 0.0001;
									$dh_tmp_item['p_length'] = 0.0001;
									$dh_tmp_item['p_weight'] = 0.0001;

									$dh_tmp_item['p_qty'] = $dh_tmp_qty;
									$dh_tmp_item['p_price_map'] = $value;
									$dh_tmp_item['p_stock'] = $dh_tmp_qty;
									$dh_tmp_item['p_price'] = $value;
									$dh_tmp_item['p_cost'] = $value;
									$dh_tmp_item['p_mfgcode'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_mfg_number'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_name'] = $f_key_tmp['data']['p_name'] . " PROMO";
									$dh_tmp_item['f_promo'] = TRUE;
									$dh_tmp_item['p_id'] = "promo-" . $dh_tmp_item['p_id'] . "-promo";

									// Check if this original item has a valid discount price, and if we need to force regular price on this item.
									if(isset($f_key_tmp['data']['p_price_map'])) {
										if($f_key_tmp['data']['p_price_map'] > $f_key_tmp['data']['p_price']) {
											// Means we have a valid discount price currently activated, time to modify if required now.
											$f_form_item = $f_key_tmp['data'];

											// --> Remove all hook items of this particular item from the cart.
											unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

											if($f_form_item['p_qty'] > $dh_tmp_item['p_qty']) {
												$f_form_item['p_qty'] = $dh_tmp_item['p_qty'];
											}

											$f_tmp_price = $f_form_item['p_price'];
											$f_form_item['p_price'] = $f_form_item['p_price_map'];

											$f_cart_tmp[$f_form_item['p_id'] . "-disc"] = $f_form_item;
											$f_cart_tmp[$f_form_item['p_id'] . "-disc"]['disc_item'] = TRUE;
											$f_cart_tmp[$f_form_item['p_id'] . "-disc"]['old_p_id'] = $f_form_item['p_id'];

											// --> Now put any extra leftovers back at regular price.
											if($f_key_tmp['qty'] - $dh_tmp_qty > 0) {
												$f_form_item['p_qty'] = $f_key_tmp['qty'] - $dh_tmp_qty;

												// Reset the prices back to how they original were.
												$f_form_item['p_price'] = $f_tmp_price;

												/*
												if(isset($f_form_item['p_org_price'])) {
													$f_form_item['p_price'] = $f_form_item['p_org_price'];
												}
												*/

												// Add the item back to cart.
												$f_cart_tmp[$f_form_item['p_id']] = $f_form_item;
											}

										}
									}

									// Add the promo coupon(s) now.
									$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
								}
							}

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else {
								if(isset($_SESSION['fluid_cart'])) {
									$_SESSION['fluid_cart'] = $f_cart_tmp;
								}
							}
						break;

						case FORMULA_OPTION_10:
							// --> Option 10. Creates a promotion coupon into the cart. Similar to FORMULA_OPTION_8 and FORMULA_OPTION_9, except FORMULA_OPTION_10 doesn't display bundles on the website. Discounts are ignored with this.
							$dh_tmp_qty = $f_key_tmp['qty'];
							$dh_found_match = 0;

							if(isset($f_key_tmp['items_match'])) {
								foreach($f_key_tmp['items_match'] as $fm_num_key => $fm_num) {
									// Need to make a temp variable and have this execute if the math parser parses the item and makes a promo coupon. ?
									$fm_num = $fl_item_formula[$f_key_number_tmp]['items_match'][$fm_num_key];

								//echo $fm_num . "<br>";
									$dh_found_match = $dh_found_match + $fm_num;

								}
							}

							if($dh_found_match > 0) {
								$dh_tmp_item = $f_key_tmp['data'];

								if(strlen($f_key_tmp['data']['p_formula_math']) > 0) {
									// --> Apply the math formula.
									$f_formula = $f_key_tmp['data']['p_formula_math'];

									$f_vars = FORMULA_VARIABLES;
									$f_vars_data = Array($dh_tmp_item['p_price_map'], $dh_tmp_item['p_price'], $dh_tmp_item['p_stock'], $dh_tmp_item['p_cost'], $dh_tmp_item['p_length'], $dh_tmp_item['p_width'], $dh_tmp_item['p_height'], $dh_tmp_item['p_height']);

									$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

									$parser = new StdMathParser();

									$AST = $parser->parse($f_formula);

									// --> Evaluate the expression.
									$evaluator = new Evaluator();

									$value = $AST->accept($evaluator);

									$value = $dh_tmp_item['p_price_map'] - $value;
									$value = -1 * abs($value);
								}

								// --> Lets create a promotional coupon.
								if(isset($value)) {
									if($dh_found_match > $f_key_tmp['qty']) {
										$dh_tmp_qty = $f_key_tmp['qty'];
									}
									else {
										$dh_tmp_qty = $dh_found_match;
									}

									foreach($fl_item_formula as $f_key_form_adj => $f_key_tmp_adj) {
										if($f_key_tmp_adj['p_id'] != $f_key_tmp['p_id']) {
											switch($f_key_tmp_adj['data']['p_formula_operation']) {
												case FORMULA_OPTION_10:
													if(isset($f_key_tmp['items_match'])) {
														foreach($f_key_tmp['items_match'] as $fm_num_key_tmp => $fm_num_tmp) {
															if(isset($f_key_tmp_adj['items_match'][$fm_num_key_tmp])) {
																//$f_key_tmp_adj['items_match'][$fm_num_key_tmp]
																if($f_key_tmp_adj['items_match'][$fm_num_key_tmp] - $dh_tmp_qty < 0) {
																	$fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] = 0;
																}
																else {
																	//echo "test";
																	$fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] = $fl_item_formula[$f_key_form_adj]['items_match'][$fm_num_key_tmp] - $dh_tmp_qty;
																}
															}

														}
													}
												break;
											}
										}
									}

									$dh_tmp_item['p_image'] = IMG_NO_IMAGE;
									$dh_tmp_item['p_width'] = 0.0001;
									$dh_tmp_item['p_height'] = 0.0001;
									$dh_tmp_item['p_length'] = 0.0001;
									$dh_tmp_item['p_weight'] = 0.0001;

									$dh_tmp_item['p_qty'] = $dh_tmp_qty;
									$dh_tmp_item['p_price_map'] = $value;
									$dh_tmp_item['p_stock'] = $dh_tmp_qty;
									$dh_tmp_item['p_price'] = $value;
									$dh_tmp_item['p_cost'] = $value;
									$dh_tmp_item['p_mfgcode'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_mfg_number'] = $dh_tmp_item['p_id'] . "-promo";
									$dh_tmp_item['p_name'] = $f_key_tmp['data']['p_name'] . " PROMO";
									$dh_tmp_item['f_promo'] = TRUE;
									$dh_tmp_item['p_id'] = "promo-" . $dh_tmp_item['p_id'] . "-promo";

									// Check if this original item has a valid discount price, and if we need to force regular price on this item.
									if(isset($f_key_tmp['data']['p_price_map'])) {
										if($f_key_tmp['data']['p_price_map'] > $f_key_tmp['data']['p_price']) {
											// Means we have a valid discount price currently activated, time to modify if required now.
											$f_form_item = $f_key_tmp['data'];

											// --> Remove all hook items of this particular item from the cart.
											unset($f_cart_tmp[$f_key_tmp['data']['p_id']]);

											if($f_form_item['p_qty'] > $dh_tmp_item['p_qty']) {
												$f_form_item['p_qty'] = $dh_tmp_item['p_qty'];
											}

											$f_tmp_price = $f_form_item['p_price'];
											$f_form_item['p_price'] = $f_form_item['p_price_map'];

											$f_cart_tmp[$f_form_item['p_id'] . "-disc"] = $f_form_item;
											$f_cart_tmp[$f_form_item['p_id'] . "-disc"]['disc_item'] = TRUE;
											$f_cart_tmp[$f_form_item['p_id'] . "-disc"]['old_p_id'] = $f_form_item['p_id'];

											// --> Now put any extra leftovers back at regular price.
											if($f_key_tmp['qty'] - $dh_tmp_qty > 0) {
												$f_form_item['p_qty'] = $f_key_tmp['qty'] - $dh_tmp_qty;

												// Reset the prices back to how they original were.
												$f_form_item['p_price'] = $f_tmp_price;

												/*
												if(isset($f_form_item['p_org_price'])) {
													$f_form_item['p_price'] = $f_form_item['p_org_price'];
												}
												*/

												// Add the item back to cart.
												$f_cart_tmp[$f_form_item['p_id']] = $f_form_item;
											}

										}
									}

									// Add the promo coupon(s) now.
									$f_cart_tmp[$dh_tmp_item['p_id']] = $dh_tmp_item;
								}
							}

							// --> Save the updated cart into the $_SESSION.
							if(isset($f_checkout_id)) {
								if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']))
									$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $f_cart_tmp;
							}
							else {
								if(isset($_SESSION['fluid_cart'])) {
									$_SESSION['fluid_cart'] = $f_cart_tmp;
								}
							}
						break;
					}
				}
			}

			if($checkout == FALSE)
				$scroll_css = "fluid-cart-scroll";
			else
				$scroll_css = "fluid-cart-no-scroll";

			$html = "<div name='fluid-cart-scroll' class='" . $scroll_css . "'>";
			$html_edit = "<div id='fluid-cart-scroll-edit' class='" . $scroll_css . "'>";

			$fluid_cart_num_items = 0;

			// --> Lets check the stock of items or release date, and see if we are allowed to buy them or not.
			if($checkout == TRUE) {
				$fluid->php_db_begin();

				$f_tmp_stock = NULL;
				$where = "WHERE p_id IN (";
				$i_stock = 0;

				foreach($f_cart_tmp as $key_stock => $data_stock) {
					if($i_stock != 0)
						$where .= ", ";

					$where .= "'" . $fluid->php_escape_string($data_stock['p_id']) . "'";

					$i_stock++;
				}

				$where .= ")";

				$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

				if(isset($fluid->db_array)) {
					foreach($fluid->db_array as $s_key => $s_data) {
						$f_tmp_stock[$s_data['p_id']] = $s_data;
					}
				}

				// Now to check component stock.
				if(isset($f_tmp_stock)) {
					$fluid_check_stock = new Fluid();
					foreach($f_tmp_stock as $s_key => $s_stock) {
						if($s_stock['p_component'] == TRUE) {
							$f_tmp_stock[$s_key]['p_stock'] = $fluid_check_stock->php_process_stock($s_stock);
						}
					}
				}

				$fluid->php_db_commit();
			}

			foreach($f_cart_tmp as $key => $data) {
				// Process the image.
				$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
				$width_height = $fluid->php_process_image_resize($data['p_image'], "60", "60", $f_img_name);

				if($checkout == FALSE) {
					if(empty($data['p_mfgcode']))
						$ft_mfgcode = $data['p_id'];
					else
						$ft_mfgcode = $data['p_mfgcode'];

					if(empty($data['p_name']))
						$ft_name = $data['p_id'];
					else
						$ft_name = $data['m_name'] . " " . $data['p_name'];

					// --> If it is a promo item coupon in the cart, no need to have a item link.
					if(isset($data['f_promo']))
						$html .= "<div class='fluid-cart' style='color: #ff2424; font-style: italic;'>";
					else
						$html .= "<div class='fluid-cart'><a href='" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "' onClick='js_loading_start();'>";
				}
				else
					// --> If it is a promo item coupon in the cart, no need to have a item link.
					if(isset($data['f_promo']))
						$html .= "<div class='fluid-cart' style='color: #ff2424; font-style: italic;'>";
					else
						$html .= "<div class='fluid-cart'>";

					$html .= "<div class='divTable'>";
						$html .= "<div class='divTableBody'>";
							$html .= "<div class='divTableRow'>";
								$html .= "<div class='divTableCell' style='vertical-align:middle; width: " . $data['p_width_height']['width'] . "px; min-width: 80px; max-width: 80px;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img></div>";
								$html .= "<div class='divTableCell' style='vertical-align:middle; font-size: 14px; width: 100%;'>";

								// --> For showing a message in the cart if a item doesn't have enough stock.
								if($checkout == TRUE && empty($data['f_promo'])) {
									if($f_tmp_stock[$data['p_id']]['p_stock'] < $data['p_qty']) {
										if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $f_tmp_stock[$data['p_id']]['p_enable'] == 2) {
											if($f_stock_error != 1) {
												// If the item is a future preorder item but preorders purchases are disabled. Then disable the purchase.
												if($fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$data['p_id']]['p_preorder'] == TRUE && FLUID_PREORDER == FALSE) {
													$f_stock_error = 1;
												}
												// If it's a item thats not special order and not pre-order, then we can't purchase it because too many qty's in the cart.
												else if($f_tmp_stock[$data['p_id']]['p_stock'] < $data['p_qty'] && $f_tmp_stock[$data['p_id']]['p_special_order'] != 1 && $f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE) {
													$f_stock_error = 1;
												}
											}

											// Discontinued item.
											if($f_tmp_stock[$data['p_id']]['p_enable'] == 2) {
												if($f_tmp_stock[$data['p_id']]['p_stock'] > 0) {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Discontinued item. " . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Please edit your cart.</div>";
												}
												else {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>Discontinued item. " . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Please edit your cart.</div>";
												}
											}
											// If it's a item thats not special order and not pre-order, then we can't purchase it because too many qty's in the cart.
											else if($f_tmp_stock[$data['p_id']]['p_special_order'] != 1 && ($f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE || $fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == TRUE)) {
												$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Please edit your cart.</div>";
											}
											// Special order item, leave a message about availability.
											else if($f_tmp_stock[$data['p_id']]['p_special_order'] == 1 && ($f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE || ($f_tmp_stock[$data['p_id']]['p_preorder'] == TRUE && $fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == TRUE))) {
												if($f_tmp_stock[$data['p_id']]['p_stock'] > 0) {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Special order item. Contact us for availability.</div>";
												}
												else {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>Special order item. Contact us for availability.</div>";
												}
											}
										}
										else {
											// Regular items. Not special order and not preorders.
											if($f_tmp_stock[$data['p_id']]['p_special_order'] != 1 && $f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE) {
												$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock.</div>";
											}
											// Special order item, leave a message about availability.
											else if($f_tmp_stock[$data['p_id']]['p_special_order'] == 1 && $f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE) {
												if($f_tmp_stock[$data['p_id']]['p_stock'] > 0) {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock. Special order item. Contact us for availability.</div>";
												}
												else {
													$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>Special order item. Contact us for availability.</div>";
												}
											}
											/*
											if(($fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$data['p_id']]['p_preorder'] == TRUE && FLUID_PREORDER == FALSE) || $f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE)
												$html .= "<div name='f-cart-no-stock' style='vertical-align:middle; font-size: 12px; color: red;'>" . $f_tmp_stock[$data['p_id']]['p_stock'] . " in stock.</div>";
											*/
										}
									}
								}

								// --> For preventing future items from being sold unless they are pre-ordered items.
								if($checkout == TRUE && empty($data['f_promo'])) {
									if($fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$data['p_id']]['p_preorder'] == FALSE) {
										if($f_stock_error != 1) {
											$f_stock_error = 1;
										}
										$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>This item can not be purchased yet. Please edit your cart.</div>";
									}
									else if($fluid->php_item_available($f_tmp_stock[$data['p_id']]['p_newarrivalenddate']) == FALSE && $f_tmp_stock[$data['p_id']]['p_preorder'] == TRUE && FLUID_PREORDER == TRUE) {
										if($f_tmp_stock[$data['p_id']]['p_arrivaltype'] == 1) {
											$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>Estimated availability: " . date("Y-m-d", strtotime($f_tmp_stock[$data['p_id']]['p_newarrivalenddate'])) . "</div>";
										}
										else {
											$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>Estimated availability: " . date("F Y", strtotime($f_tmp_stock[$data['p_id']]['p_newarrivalenddate'])) . "</div>";
										}
									}
								}

								// --> For showing a message in cart for in store pickup items only.
								if($checkout == TRUE && $data['p_instore'] == 1) {
									$html .= "<div style='vertical-align:middle; font-size: 12px; color: red;'>This item is only available for in store pickup only.</div>";
								}

								if(empty($data['p_name'])) {
									$ft_name = $data['p_id'];
								}
								else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE) {
									$ft_name = $data['m_name'] . " " . $data['p_name'];
								}
								else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE) {
									$ft_name = $data['m_name'] . " " . $data['p_name'];
								}
								else {
									$ft_name = $data['m_name'] . " " . $data['p_mfg_number'] . " " . $data['p_name'];
								}

								$p_discount_html = NULL;
								$p_left_padding = NULL;
								if(isset($data['p_price_map'])) {
									if($data['p_price_map'] > $data['p_price']) {
										$p_discount_html = "<div style='text-decoration: line-through; color: red; font-style: italic;'>" . HTML_CURRENCY . " " . number_format($data['p_price_map'], 2, ".", ",") . " ea.</div>";
										$p_left_padding = " padding-top: 7px;";
									}
								}

								$html .= $ft_name . "<div style='padding-top: 5px;'><div><div class='pull-left' style='" . $p_left_padding . "'>Qty: " . $data['p_qty'] . "</div><div class='pull-right'>" . $p_discount_html . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div></div>";
							$html .= "</div>"; // divTableRow
						$html .= "</div>";
					$html .= "</div>";

				if($checkout == FALSE)
					$html .= "</a></div>";
				else
					$html .= "</div>";

				$html_edit .= "<div class='fluid-cart fluid-cart-editor-items' name='fluid-cart-editor-items' id='fluid-cart-editor-item-" . $key . "' data-key='" . $key . "' data-id='" . $data['p_id'] . "' data-price='" . base64_encode($data['p_price']) . "'>";
					$html_edit .= "<div class='divTable'>";
						$html_edit .= "<div class='divTableBody'>";
							$html_edit .= "<div class='divTableRow'>";
								$html_edit .= "<div class='divTableCell' style='vertical-align:middle; width: " . $data['p_width_height']['width'] . "px; min-width: 80px; max-width: 80px;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px;;' alt=alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img></div>";

								$html_edit .= "<div class='divTableCell' style='vertical-align:middle;'>" . $ft_name;
									$html_edit .= "<div style='padding-top: 5px; padding-bottom: 5px;'>";
										$html_edit .= "<div class='pull-left' style='margin-bottom: 0px; width: 100%;'>";
											$html_edit .= "<div class='fluid-cart-editor-box'>";
												$html_edit .= "<div style='margin-bottom: 0px; display: inline-block; padding-right: 10px;'><button style='float:left;' class='btn btn-warning' onClick='js_fluid_cart_decrease_num(\"fluid-cart-editor-qty-" . $key . "\"); js_fluid_cart_editor_refresh();'><span class='glyphicon glyphicon-minus'></span></button></div>";
												if($data['p_buyqty'] < 1)
													$p_buyqty = 10;
												else
													$p_buyqty = $data['p_buyqty'];

												$html_edit .= "<div style='margin-bottom: 0px; display: inline-block; width: 30px;'><input class='fluid-cart-qty pull-left fluid-cart-editor-qty' style='margin-bottom: 0px;' type='text' step='1' min='1' max='" . $p_buyqty . "' value='" . $data['p_qty'] . "' disabled id='fluid-cart-editor-qty-" . $key . "'></div>";
												$html_edit .= "<div style='margin-bottom: 0px; display:inline-block; padding-left: 10px;'><button style='float:left;' class='btn btn-warning' onClick='js_fluid_cart_increase_num(\"fluid-cart-editor-qty-" . $key . "\"); js_fluid_cart_editor_refresh();'><span class='glyphicon glyphicon-plus'></span></button></div>";
												$html_edit .= "<div class='pull-right' style='margin-top: 0px; margin-bottom: 0px; display:inline-block;'><button type='button' class='btn btn-danger pull-right' aria-haspopup='true' aria-expanded='false' onClick='document.getElementById(\"fluid-cart-scroll-edit\").removeChild(document.getElementById(\"fluid-cart-editor-item-" . $key . "\")); js_fluid_cart_editor_refresh();'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></div>";
											$html_edit .= "</div>";
										$html_edit .= "</div>";
									$html_edit .= "</div>";
								$html_edit .= "</div>";

							$html_edit .= "</div>";
						$html_edit .= "</div>";
					$html_edit .= "</div>";
				$html_edit .= "</div>";

				$fluid_cart_num_items = $fluid_cart_num_items + $data['p_qty'];
			}
			$html .= "</div>"; // fluid-cart-scroll
			$html_edit .= "</div>"; // fluid-cart-scroll-edit

			$fluid_cart_total = php_cart_sub_total($f_checkout_id);

			$html_e = "<div style='padding-top: 10px;'>";
				$html_e .= "<div class='divTable'>";
					$html_e .= "<div class='divTableBody'>";
						$html_e .= "<div id='fluid-cart-totals' class='divTableRow pull-right fluid-cart-subtotal' style='font-size: 14px; font-weight: 400 !important;'>";

						$html_e_price = "<div style='display: table;'>";

							$f_animate_id = NULL;
							$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row"), "delay" => 0, "colour" => "#0050FF");
							$html_e_price .= "<div name='fluid-sub-total-row' id='fluid-sub-total-row' style='text-align: right;'>"; // --> This div is used for animating.
								$html_e_price .= "<div style='display: table-row;'>";
									$html_e_price .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Sub Total: </div><div style='display: table-cell; text-align: right;'> " . HTML_CURRENCY . " " . number_format($fluid_cart_total, 2, ".", ",") . "</div>";
								$html_e_price .= "</div>";
							$html_e_price .= "</div>";

							$f_ship_price = "-";
							$html_tax = NULL;
							$t_taxes_price = NULL;
							$f_shipping_price = NULL;

							// We have a shipping address, lets get a estimate on shipping and return the cheapest shipping price.
							if(isset($_SESSION['f_address_tmp_ship_select'])) {
								if($ship_override == TRUE && isset($_SESSION['fluid_shipping_tmp'])) {
									$fluid_shipping = $_SESSION['fluid_shipping_tmp'];
									$fluid = new Fluid ();
									//$fluid->php_debug("session data", TRUE);
								}
								else {
									// Calculate shipping and packing information.
									$fluid_shipping = php_fluid_shipping($_SESSION['f_address_tmp_ship'][$_SESSION['f_address_tmp_ship_select']], NULL, TRUE);
									$_SESSION['fluid_shipping_tmp'] = $fluid_shipping; // Store into the session for later.
								}

								$f_shipping = $fluid_shipping['fluid_rates'];

								$f_ship_price = "<div style='display: inline-block; font-style: italic; color: red;'>address error</div>";
								// Store the box packing information in a $_SESSION variable so we can record and notify the shipping department how to pack the boxes.
								//$_SESSION['f_checkout'][$data->f_checkout_id]['f_packages'] = $fluid_shipping['fluid_packing'];

								// Calculate taxes.
								$t_taxes = php_fluid_taxes(Array("a_data" => $_SESSION['f_address_tmp_ship'][$_SESSION['f_address_tmp_ship_select']], "f_cart" => $_SESSION['fluid_cart'], "f_rates" => $fluid_shipping['fluid_rates']));

								// Resort the shipping data from cheapest to most expensive.
								$f_sort = array();
								foreach($f_shipping as $k => $v)
									$f_sort[$k] = $v['price'];

								$f_ship_lowest = $f_shipping;

								$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row"), "delay" => 250, "colour" => "#5EFF00");
																// Load the cheapest shipping option and calculate the tax and final totals.
								foreach($f_ship_lowest as $k => $f_lowest) {
									// Skip the first shipping item in the array. This will be the pickup in store option. Never default load this one.
									if($k != 0) {
										$f_ship_price = HTML_CURRENCY . " " .  number_format($f_lowest['price'], 2, ".", ",");
										$f_shipping_price = number_format($f_lowest['price'], 2, ".", ",");

										// Calculate the taxes for the currently selected shipping and address settings.
										if(isset($t_taxes)) {
											foreach($t_taxes as $t_key => $t_data) {
												$tmp_total = round($t_data['p_total'] + $t_data['f_rates'][$f_lowest['s_id']]['t_total'], 2);
												$t_taxes_price = round($t_taxes_price + $tmp_total, 2);

												// Keep track of the current tax totals for the selected shipping and address settings.
												//$_SESSION['f_totals']['taxes'][$t_data][$t_key] = Array("t_id" => $t_key, "name" => $t_data, "total" => $tmp_total);

												$f_animate_id[] = Array("id" => base64_encode("fluid-tax-row-" . $t_key), "delay" => 250, "colour" => "#FF006B"); // --> id name for the animation div. This will be procssed by js_fluid_block_animate();
												$html_tax .= "<div name='fluid-tax-row-" . $t_key . "' id='fluid-tax-row-" . $t_key . "' style='text-align: right;'>"; // --> This div is used for animating.
													$html_tax .= "<div id='tax-" . $t_key . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_data['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($tmp_total, 2, '.', ',') . "</div></div>";
												$html_tax .= "</div>";
											}
										}

										break;
									}
								}

								$html_e_price .= "<div name='fluid-shipping-row' id='fluid-shipping-row' style='text-align: right;'>"; // --> This div is used for animating.
									$html_e_price .= "<div style='display: table-row;'>";
										$html_e_price .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Shipping:</div><div style='display: table-cell; text-align: right;'> " . $f_ship_price . " </div>";
									$html_e_price .= "</div>";
								$html_e_price .= "</div>";

								if(isset($html_tax))
									$html_e_price .= $html_tax;

								if(isset($f_shipping_price)) {
									$fluid_cart_final_total = round($fluid_cart_total + $f_shipping_price + $t_taxes_price, 2);
									$f_total_final_price_html = HTML_CURRENCY . " " . number_format($fluid_cart_final_total, 2, ".", ",");

									$f_animate_id[] = Array("id" => base64_encode("fluid-total-row"), "delay" => 250, "colour" => "#FFD600");
									$html_e_price .= "<div name='fluid-total-row' id='fluid-total-row' style='text-align: right;'>"; // --> This div is used for animating.
										$html_e_price .= "<div style='display: table-row; text-align: right;'>";
											$html_e_price .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Total:</div><div id='fluid-total-cart-row' style='display: table-cell; text-align: right;'>" . $f_total_final_price_html . "</div>";
										$html_e_price .= "</div>";
									$html_e_price .= "</div>";
								}
							}

						$html_e_price .= "</div>"; //table

						$html_e_price_editor = "<div style='display: inline-block; font-weight: 300;'>Sub Total: </div><div style='display: inline-block; padding-left: 5px; padding-right: 5px;'> " . HTML_CURRENCY . "</div><div id='fluid_cart_total_editor_div' style='display:inline-block;'>" . number_format($fluid_cart_total, 2, ".", ",") . "</div>";

						$html_e_p1 = "</div>";
					$html_e_p1 .= "</div>";
				$html_e_p1 .= "</div>";
				$html_e_p1 .= "<div class='divTable'>";
					$html_e_p1 .= "<div class='divTableBody'>";
						$html_e_p1 .= "<div class='divTableRow'>";
							//$html_e_button_checkout = "<div class='divTableCell' style='border-bottom: 0px; text-align:center;'><button type='button' class='btn btn-warning pull-left fluid-cart-edit-bottom-button' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_cart_editor();'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Cart</button>";
							$f_ship_select_html = NULL;
							$html_e_button_checkout = NULL;

							if($checkout == FALSE) {
								require_once(FLUID_ACCOUNT);

								$f_ship_address = php_fluid_address_ship(); // --> fluid.account.php

								$detect = new Mobile_Detect;
								// --> Temporary hide the shipping quotation module in the cart section, until performance improvements can be made.
								/*
								if($detect->isiOS()) {
								 // --> Do nothing. Have to disable inputs on fixed elements in ios due to a bug with ios scrolling to the top of a page automatically :(
								}
								else {
									if($f_ship_address['total_addresses'] == 0)
										$html_e_button_checkout = "<div class='divTableCell' style='border-bottom: 0px; text-align:center;'><button type='button' class='btn btn-default pull-left fluid-cart-edit-bottom-button' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_ship_editor(); js_google_maps_init_auto_complete_ship();'><span class='fa fa-truck' aria-hidden='true'></span> Get shipping</button>";
									else {
										$html_e_button_checkout = "<div class='divTableCell' style='border-bottom: 0px; text-align:center;'><button type='button' class='btn btn-default pull-left fluid-cart-edit-bottom-button' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_ship_select();'><span class='fa fa-truck' aria-hidden='true'></span> Shipping addresses</button>";

										$f_ship_select_html = $f_ship_address['html'];
									}
								}
								*/

								if(FLUID_STORE_OPEN == FALSE)
									$html_e_button_checkout .= "<button type='button' class='btn btn-success pull-right disabled' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button>";
								else if(empty($_SESSION['u_id']))
									$html_e_button_checkout .= " <button type='button' class='btn btn-success pull-right' aria-haspopup='true' aria-expanded='false' onClick='js_close_toggle_menus(); document.getElementById(\"fluid-account-dropdown\").innerHTML = \"\"; document.getElementById(\"fluid-account-dropdown-nav\").innerHTML = \"\"; document.getElementById(\"modal-login-div\").innerHTML = \"\"; document.getElementById(\"modal-checkout-fluid-div\").innerHTML = Base64.decode(FluidMenu.account[\"html\"]); document.getElementById(\"fluid-div-signup\").innerHTML = Base64.decode(FluidMenu.account[\"signup_checkout_plus_mobile_html\"]); document.getElementById(\"fluid-checkout-guest-back-button\").innerHTML = Base64.decode(FluidMenu.account[\"f_mobile_back_button_signup\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"none\"; fluid_facebook_checkout = 1; fluid_google_checkout = 1; document.getElementById(\"fluid-checkout-login\").value = \"1\"; js_fluid_login(); js_modal_show(\"#fluid-checkout-guest-modal\");'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button>";
								else
									$html_e_button_checkout .= " <a href=\"" . $_SESSION['fluid_uri'] . FLUID_CHECKOUT_REWRITE . "\" onClick='js_loading_start();'><button type='button' class='btn btn-success pull-right' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-shopping-cart' aria-hidden='true'></span> Checkout</button></a>";

								$html_e_button_checkout .= "</div>";
							}
							else if($checkout == TRUE) {
								$html_e_button_checkout = "<div id=\"paypal-button\" class='paypal-button-lg'></div><div id='fluid-button-place-order-div-lg'><button type='button' id='fluid-place-order-button-lg' class='btn btn-lg btn-block fluid-btn-grey fluid-cart-order-button disabled' aria-haspopup='true' aria-expanded='false' onClick='if(FluidTemp.cart_edit == 0) { js_fluid_confirm_order(); }'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Confirm Order</button></div>"; // --> Confirm order button in 768px > mode

								if(isset($tmp_data->f_paypal))
									if($tmp_data->f_paypal == TRUE)
										$html_e_button_checkout = "<div id=\"paypal-button\" class='paypal-button-lg'></div><div id='fluid-button-place-order-div-lg' style='display: none;'><button type='button' id='fluid-place-order-button-lg' class='btn btn-lg btn-block fluid-btn-grey fluid-cart-order-button disabled' aria-haspopup='true' aria-expanded='false' onClick='if(FluidTemp.cart_edit == 0) { js_fluid_confirm_order(); }'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Confirm Order</button></div>"; // --> Confirm order button in 768px > mode
							}

							if($checkout == FALSE) {
								$f_save_cart = "onClick='js_fluid_cart_save();'";
								$f_cart_cancel = "onClick='js_fluid_cart_editor_cancel(true);'";
							}
							else {
								$f_save_cart = "onClick='FluidTemp.f_refresh_shipping=true; js_fluid_check_order_button(); js_fluid_cart_save();'";
								$f_cart_cancel = "onClick='js_fluid_cart_editor_cancel(true); js_fluid_check_order_button();'";
							}

							$html_e_button_save = "<div class='divTableCell' style='border-bottom: 0px; text-align:center;'><button type='button' class='btn btn-danger pull-left' aria-haspopup='true' aria-expanded='false' " . $f_cart_cancel . "><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Cancel</button> <button type='button' class='btn btn-primary pull-right' aria-haspopup='true' aria-expanded='false' " . $f_save_cart . "><span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Update Cart</button></div>";
						$html_e_p2 = "</div>";
					$html_e_p2 .= "</div>";
				$html_e_p2 .= "</div>";
			$html_e_p2 .= "</div>";

			$html_s = "<div>";
				$html_s .= "<div class='divTable'>";
					$html_s .= "<div class='divTableBody'>";
						$html_s .= "<div class='divTableRow'>";

							$html_s_num_items = "<div style='display: table; width:100%; height: 100%; vertical-align: middle; padding-bottom: 10px;' class='divTableCell fluid-cart-header'>";
								$html_s_num_items .= "<div style='display: table-cell; vertical-align: middle;'>You have " . $fluid_cart_num_items . " items in your cart.</div>";

								if($checkout == TRUE)
									$f_edit_click = "onClick='js_fluid_cart_set_div(); js_fluid_cart_editor();'";
								else
									$f_edit_click = "onClick='js_fluid_cart_editor();'";

									$html_s_num_items .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; float:right;'><button type='button' class='btn btn-default pull-right fluid-cart-edit-top-button' aria-haspopup='true' aria-expanded='false' " . $f_edit_click . "><span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit</button></div>";

							$html_s_num_items .= "</div>";

							$html_s_num_items_editor = "<div class='divTableCell fluid-cart-header'>You have <div id='fluid_cart_num_items_editor' style='display: inline-block;'>" . $fluid_cart_num_items . "</div> items in your cart.</div>";
						$html_s_p2 = "</div>";
					$html_s_p2 .= "</div>";
				$html_s_p2 .= "</div>";
			$html_s_p2 .= "</div>";

			$html = "<div id='fluid-cart-display'>" . $html_s . $html_s_num_items . $html_s_p2 . $html . $html_e;
			if($checkout == FALSE)
				$html .= $html_e_price;
			$html .= $html_e_p1 . $html_e_button_checkout . $html_e_p2 . "</div>";

			$html_editor = "<div id='fluid-cart-editor'>" . $html_s . $html_s_num_items_editor . $html_s_p2 . $html_edit . $html_e . $html_e_price_editor . $html_e_p1 . $html_e_button_save . $html_e_p2 . "</div>";

			$html_ship = "<div id='fluid-cart-ship' style='min-width: 280px;'>";
				$html_ship .= "<div>";
					$html_ship .= "<div style='display: table; width:100%; height: 100%; vertical-align: middle; padding-bottom: 10px;' class='fluid-cart-header'>";
						$html_ship .= "<div style='display: table-cell; vertical-align: middle;'><span class='fa fa-truck' aria-hidden='true'></span> Shipping address estimator</div>";
						//$html_ship .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; float:right;'><button type='button' class='btn btn-default pull-right fluid-cart-edit-top-button' aria-haspopup='true' aria-expanded='false'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span></button></div>";
					$html_ship .= "</div>";
				$html_ship .= "</div>";

				$html_ship .= "<div style='display: table; width:100%; height: 100%; vertical-align: middle; padding-bottom: 0px;'>";
					$html_ship .= "<div style='display: table-cell; vertical-align: middle;'>";
						$html_ship .= HTML_CART_SHIP_ESTIMATOR;
					$html_ship .=  "</div>";
			$html_ship .="</div>";
		}
		else {
			$html = "Your cart is empty.";
			$html_editor = "Nothing to edit.";
			$html_ship = "Nothing to ship.";
			$f_ship_select_html = NULL;
			$f_animate_id[] = Array("id" => NULL, "delay" => NULl, "colour" => NULL);
			$fluid_cart_num_items = 0;
		}

		// When adding a item to the cart, we execute everything via the animation function.
		if(isset($p_id) && $cart_reset == FALSE) {
			$execute_functions[]['function'] = "js_fluid_add_to_cart_animation";
			end($execute_functions);

			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("p_id" => base64_encode($p_id), "button_id" => base64_encode($btn_id), "cart_html_editor" => base64_encode($html_editor), "cart_html_ship" => base64_encode($html_ship), "cart_html_ship_select" => base64_encode($f_ship_select_html), "cart_div" => base64_encode("fluid-cart-dropdown"), "cart_div_nav" => base64_encode("fluid-cart-dropdown-nav"), "cart_div_mobile" => base64_encode("fluid-cart-dropdown-mobile"), "cart_html" => base64_encode($html), "cart_badge_div" => base64_encode("fluid-cart-badge"), "cart_badge_dropdown_div" => base64_encode("fluid-cart-badge-dropdown"), "cart_badge_mobile_div" => base64_encode("fluid-cart-badge-mobile"), "f_animate" => base64_encode(json_encode($f_animate_id)), "cart_num_items" => base64_encode($fluid_cart_num_items))));
		}
		else if($cart_reset == TRUE) {
			$execute_functions[]['function'] = "js_fluid_cart_reset";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("html" => base64_encode($html), "html_editor" => base64_encode($html_editor), "html_ship" => base64_encode($html_ship), "html_ship_select" => base64_encode($f_ship_select_html), "num_items" => base64_encode($fluid_cart_num_items))));

			$execute_functions[]['function'] = "js_fluid_cart_editor_cancel"; // --> header.php
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(false));
		}
		else {
			// When a page is refreshed, things are manually inserted into the cart dropdown via the header.php during loading of the header page.
			// Note ! --> These sequences of the order are very important!
			// This is never run via ajax, only on page loads in php. Do not need to worry about performance.

			// 0
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-dropdown"), "html" => base64_encode($html))));

			// 1
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-badge"), "html" => base64_encode($fluid_cart_num_items))));

			// 2
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-dropdown"), "html" => base64_encode($html_editor))));

			// 3
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-badge-dropdown"), "html" => base64_encode($fluid_cart_num_items))));

			// 4
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-badge-mobile"), "html" => base64_encode($fluid_cart_num_items))));

			// 5
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-dropdown-nav"), "html" => base64_encode($html))));

			// 6
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-dropdown"), "html" => base64_encode($html_ship))));

			// 7
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-dropdown"), "html" => base64_encode($f_ship_select_html))));
		}

		if($checkout == FALSE) {
			if(isset($f_animate_id) && empty($p_id)) {
				$execute_functions[]['function'] = "js_fluid_block_animate";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));
			}
		}

		// 8 (checkout)
		if($checkout == TRUE) {
			$execute_functions[]['function'] = "js_fluid_check_stock";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_stock_error));
		}

		$f_cart_refresh_animation = FALSE;
		// This is executed when a cart edit is saved during the checkout and we have a shipping address selected for refreshing.
		if($checkout == TRUE && isset($tmp_data)) {
			if($tmp_data->f_refresh_shipping == TRUE && isset($tmp_data->a_id)) {
				$execute_functions[]['function'] = "js_fluid_checkout_address_select";
				end($execute_functions);

				// Executes when we are in the checkout process but have a shipping address selected. We want to play the particle animation after the shipping has refreshed.
				if(isset($p_id)) {
					//$execute_functions[]['function'] = "js_fluid_add_to_cart_animation";
					//end($execute_functions);
					//$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("p_id" => base64_encode($p_id), "button_id" => base64_encode($btn_id))));
					$fs_data = Array("a_id" => $tmp_data->a_id, "a_add_to_cart" => base64_encode(json_encode(Array("p_id" => base64_encode($p_id), "button_id" => base64_encode($btn_id)))));
				}
				else
					$fs_data = Array("a_id" => $tmp_data->a_id, "a_add_to_cart" => NULL);

				$f_cart_refresh_animation = TRUE;
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fs_data));
			}
		}

		// This will execute when we are in the checkout process but with no shipping address selected for refreshing.
		if($cart_reset == TRUE && $f_cart_refresh_animation == FALSE) {
			if(isset($p_id)) {
				$execute_functions[]['function'] = "js_fluid_add_to_cart_animation";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("p_id" => base64_encode($p_id), "button_id" => base64_encode($btn_id))));
			}
		}

		// This is executed when in the checkout process only but with no shipping address and a empty cart. --> php_fluid_checkout_load() is not executed so we have to run this.
		if($checkout == TRUE && $fluid_cart_num_items == 0 && empty($tmp_data->a_id)) {
			$execute_functions[]['function'] = "js_fluid_cart_status";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(1);

			$execute_functions[]['function'] = "js_fluid_check_order_button";
		}
		else if($checkout == TRUE && $fluid_cart_num_items > 0 && empty($tmp_data->a_id)) {
			// This is executed when in the checkout process only but with no shipping address but with items in the cart. --> php_fluid_checkout_load() is not executed so we have to run this.
			$execute_functions[]['function'] = "js_fluid_cart_status";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(0);

			$execute_functions[]['function'] = "js_fluid_check_order_button";
		}

		// Google tracking information.
		if($checkout == FALSE) {
			$execute_functions[]['function'] = "js_ga_cart";
			end($execute_functions);

			$f_gs_cart = NULL;
			if(isset($_SESSION['fluid_cart'])) {
				$f_gs_cart = $_SESSION['fluid_cart'];

				foreach($f_gs_cart as $f_gs_key => $f_gs_data) {
					if(isset($f_gs_data['p_cost']))
						unset($f_gs_cart[$f_gs_key]['p_cost']);
				}
			}

			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_gs_cart));
		}

		// This is executed when not in the cart and adding a item to cart by clicking on a button. The $f_accessory_array is proccessed in php_add_to_cart() and php_html_accessory_modal().
		if(isset($p_id) && $cart_reset == FALSE && $checkout == FALSE && isset($f_accessory_array)) {
			$execute_functions[]['function'] = "js_fluid_cart_accessories";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("html" => base64_encode($f_accessory_array['html']), "data" => $f_accessory_array['data'], 'i_max' => $f_accessory_array['i_max'])));
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
	}
}

function php_html_checkout_address_creator($data = NULL) {
	try {
		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		$html = HTML_ADDRESS_CREATE_FORM;

		$f_back_button = "<button type=\"button\" class=\"btn btn-warning\" onClick='js_fluid_checkout_address();'><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Back</button>";

		$f_trigger_button = "<button type=\"button\" class=\"btn btn-info\" onClick='$(\"#f-address-submit-button\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Create Address</button>";

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-back-button"), "html" => base64_encode($f_back_button))));

		$execute_functions[]['function'] = "js_html_style_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => base64_encode("fluid-modal-close-button"))));

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "html" => base64_encode($f_trigger_button))));

		$execute_functions[]['function'] = "js_html_style_display";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "div_style" => base64_encode("inline-block"))));

		$execute_functions[]['function'] = "js_google_maps_init_auto_complete";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

function php_html_checkout_payment_input($data = NULL) {
	try {
		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		// Must be fluid_form_address for the validator to work correctly from fluid.account.php --> js_google_maps_fill_in_address().
		$html = "
			<form id=\"fluid_form_address\" data-toggle=\"validator\" data-focus=\"false\" role=\"form\" onsubmit=\"js_fluid_payment_checkout_create();\" autocomplete=\"on\">
					<div class=\"form-group has-feedback\">
						<label for=\"fluid-cc-name\" class=\"cols-sm-2 control-label\">Name on card</label>
						<input type=\"text\" class=\"form-control\" name=\"fluid-cc-name\" id=\"fluid-cc-name\" placeholder=\"Enter the name on the credit card\" required autocomplete=\"cc-name\"";

						if(isset($data->payment->c_name))
							$html .= " value=\"" . utf8_decode(htmlspecialchars($data->payment->c_name)) . "\"";

						$html .=">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group has-feedback\">
						<label for=\"fluid-cc-number\" class=\"cols-sm-2 control-label\">Credit Card Number</label>
						<input type=\"text\" class=\"form-control\" name=\"fluid-cc-number\" id=\"fluid-cc-number\" placeholder=\"Enter your credit card number\" required autocomplete=\"cc-number\"";

						if(isset($data->payment->c_number))
							$html .= " value=\"" . utf8_decode(htmlspecialchars($data->payment->c_number)) . "\"";

						$html .= ">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group has-feedback\">
						<label for=\"fluid-cc-exp-mm\" class=\"cols-sm-2 control-label\">Expiry Month</label>
						<input type=\"text\" class=\"form-control\" name=\"fluid-cc-exp-mm\" id=\"fluid-cc-exp-mm\" placeholder=\"MM\" maxlength=\"2\" required autocomplete=\"cc-exp-mm\"";

						if(isset($data->payment->c_exp_m))
							$html .= " value=\"" . utf8_decode(htmlspecialchars($data->payment->c_exp_m)) . "\"";

						$html .= ">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group has-feedback\">
						<label for=\"fluid-cc-exp-yy\" class=\"cols-sm-2 control-label\">Expiry Year</label>
						<input type=\"text\" class=\"form-control\" name=\"fluid-cc-exp-yy\" id=\"fluid-cc-exp-yy\" placeholder=\"YY\" maxlength=\"2\" required autocomplete=\"cc-exp-yy\"";

						if(isset($data->payment->c_exp_y))
							$html .= " value=\"" . utf8_decode(htmlspecialchars($data->payment->c_exp_y)) . "\"";

						$html .= ">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group has-feedback\">
						<label for=\"fluid-cc-cv\" class=\"cols-sm-2 control-label\">CV Code</label>
						<input type=\"text\" class=\"form-control\" name=\"fluid-cc-cv\" id=\"fluid-cc-cv\" placeholder=\"3 digit code on rear of your card.\" required autocomplete=\"cc-csc\"";

						if(isset($data->payment->c_cv))
							$html .= " value=\"" . utf8_decode(htmlspecialchars($data->payment->c_cv)) . "\"";

						$html .= ">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>";

					if(FLUID_SHIP_NON_BILLING == FALSE)
						$f_ship_billing = " display: none;";
					else
						$f_ship_billing = NULL;

					$html .= "<div style='display: table; width: 100%;" . $f_ship_billing . "'>
						<div style='display: table-cell;'>Billing Address</div>

						<div style='display: table-cell; text-align: right;'>
							<div class=\"checkbox\">
								<label><input type=\"checkbox\" id=\"fluid-credit-billing-same\" onclick='js_fluid_payment_billing_check();'";

							$f_address_display = "none;";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_billing_same'])) {
								if($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_billing_same'] == 1) {
									$html .= " checked";
									$f_address_display = "none;";
								}
								else
									$f_address_display = "block;";
							}
							else
								$html .= " checked";

								$html .= "><span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span></label></input><div style='display: inline-block; margin-bottom: 0px;'> same as shipping</div>
							</div>
						</div>
					</div>

					<div id='fluid-credit-address-hidden' style='display: " . $f_address_display . "'>
						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-name\" class=\"cols-sm-2 control-label\">Your Name</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-name\" id=\"fluid-address-name\" placeholder=\"Enter your Name\" autocomplete=\"name\" required autocomplete=\"name\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_name']))
								$html .= utf8_decode(htmlspecialchars_decode($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_name']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_name']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-apt-number\" class=\"cols-sm-2 control-label\">Apartment or Room Number</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-apt-number\" id=\"fluid-address-apt-number\" placeholder=\"Enter Apartment or Room number (Optional)\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_number']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_number']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_number']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-street\" class=\"cols-sm-2 control-label\">Street</label>
							<input type=\"text\" name=\"fluid-address-street\" class=\"form-control\" name=\"fluid-address-street\" id=\"fluid-address-street\" 		onFocus='js_google_maps_geo_locate();' placeholder=\"Enter your street\" required autocomplete=\"billing street-address\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_street']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_street']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_street']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-city\" class=\"cols-sm-2 control-label\">City</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-city\" id=\"fluid-address-city\" placeholder=\"Enter your City\" required autocomplete=\"billing locality\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_street']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_street']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_city']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div id='f-province-address' class=\"form-group has-feedback\">
							<label for=\"fluid-address-province\" class=\"cols-sm-2 control-label\">Province</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-province\" id=\"fluid-address-province\" placeholder=\"Enter your province\" required autocomplete=\"billing region\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_province']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_province']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_province']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div id='f-country-address' class=\"form-group has-feedback\">
							<label for=\"fluid-address-country\" class=\"cols-sm-2 control-label\">Country</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-country\" id=\"fluid-address-country\" placeholder=\"Enter your Country\" required autocomplete=\"billing country\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_country']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_country']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_country']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-postal-code\" class=\"cols-sm-2 control-label\">Postal Code</label>
							<input type=\"text\" class=\"form-control\" name=\"fluid-address-postal-code\" id=\"fluid-address-postal-code\" placeholder=\"Enter your Postal Code\" required autocomplete=\"billing postal-code\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_postalcode']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_postalcode']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_postalcode']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>

						<div class=\"form-group has-feedback\">
							<label for=\"fluid-address-phone-number\" class=\"cols-sm-2 control-label\">Phone Number</label>
							<input type=\"text\" min=\"0\" inputmode=\"numeric\" class=\"form-control\" name=\"fluid-address-phone-number\" id=\"fluid-address-phone-number\" placeholder=\"Enter your Phone Number\" required autocomplete=\"tel\" value='";

							if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_phonenumber']))
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_phonenumber']));
							else
								$html .= utf8_decode(htmlspecialchars($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_phonenumber']));

							$html .= "'>
							<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						</div>
					</div>

					<div class=\"form-group\" style='display: none; padding-top: 40px;'>
						<button style='display: none;' id='f-payment-button-submit' type=\"submit\" class=\"btn btn-info btn-block\"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Confirm</button>
					</div>

				</form>
		";

		$f_back_button = "<button type=\"button\" class=\"btn btn-warning\" onClick='js_fluid_payment_checkout_select();'><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Cancel</button>";

		$f_trigger_button = "<button type=\"button\" class=\"btn btn-info\" onClick='$(\"#f-payment-button-submit\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Add Credit Card</button>";

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-back-button"), "html" => base64_encode($f_back_button))));

		$execute_functions[]['function'] = "js_html_style_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => base64_encode("fluid-modal-close-button"))));

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "html" => base64_encode($f_trigger_button))));

		$execute_functions[]['function'] = "js_html_style_display";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "div_style" => base64_encode("inline-block"))));


		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

		$execute_functions[]['function'] = "js_google_maps_init_auto_complete";
		$execute_functions[]['function'] = "js_fluid_payment_validator_init";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}


function php_html_checkout_payment_select($data = NULL) {
	try {
		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		/*
			--> 2 big large touch buttons / icons. Square style, with Credit Card on left, Paypal on right.
				--> Clicking on credit card button does ajax load to bring up credit card information form to get from user input. Then a brief card info gets put into the Payment Methods box. The card is checked and charged when you click the Place Order button. Card data is never saved for security purposes.
				--> Clicking on paypal button closes the modal and puts paypal info into the Payment Methods box. When you click Place Order, then PayPal api kicks in to do token process and to accept the order.
		*/

		$html = "<div style='display: table; width: 100%;'>";

		if(MONERIS_ENABLED == TRUE || AUTH_NET_ENABLED == TRUE) {
			$html .= "
				<div style='display: table-cell; text-align: center;'>
					<div class='f-payment-card'>
						<div class='fluid-account-box-special well fluid-box-shadow-small-well fluid-div-highlight' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='FluidMenu.paypal = false; js_fluid_payment_checkout_input();'>
							<i class='fa fa-credit-card fa-3x' aria-hidden='true'></i><div>Credit Card</div>
						</div>
					</div>
				</div>
			";
		}

		if(PAYPAL_ENABLED == TRUE) {
			$html .= "
				<div style='display: table-cell; text-align: center;'>
					<div class='f-payment-card'>
						<div class='fluid-account-box-special well fluid-box-shadow-small-well fluid-div-highlight' onClick='FluidMenu.paypal = true; js_fluid_payment_checkout_create_send(true);' onmouseover=\"JavaScript:this.style.cursor='pointer';\" style='margin-bottom: 5px;'>
							<i class='fa fa-paypal fa-3x' aria-hidden='true'></i><div>PayPal</div>
						</div>
					</div>
				</div>
			";
		}

		if(AUTH_NET_ENABLED == FALSE && MONERIS_ENABLED == FALSE && PAYPAL_ENABLED == FALSE) {
			$html .= "
				<div style='display: table-cell; text-align: center;'>
					There is currently no payment processes enabled.
				</div>
			";
		}
		$html .= "</div>";

		/*
			<div style='display: table; width: 100%;'>

				<div style='display: table-cell; text-align: center;'>
					<div class='f-payment-card'>
						<div class='fluid-account-box-special well fluid-box-shadow-small-well fluid-div-highlight' onmouseover=\"JavaScript:this.style.cursor='not-allowed';\" style='margin-bottom: 5px;'>
							<i class='fa fa-android fa-3x' aria-hidden='true' style='color: #737373'></i><div style='color: #737373'>Android Pay</div>
						</div>
						<div style='font-size: 10px;'>coming soon</div>
					</div>
				</div>

				<div style='display: table-cell; text-align: center;'>
					<div class='f-payment-card'>
						<div class='fluid-account-box-special well fluid-box-shadow-small-well fluid-div-highlight' onmouseover=\"JavaScript:this.style.cursor='not-allowed';\" style='margin-bottom: 5px;'>
							<i class='fa fa-apple fa-3x' aria-hidden='true' style='color: #737373'></i><div style='color: #737373'>Apple Pay</div>
						</div>
						<div style='font-size: 10px;'>coming soon</div>
					</div>
				</div>

			</div>
		*/

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-back-button"), "html" => base64_encode(""))));

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-header-div"), "html" => base64_encode("<i class=\"fa fa-credit-card-alt\"></i> Payment Methods"))));

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

		$execute_functions[]['function'] = "js_html_style_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => base64_encode("fluid-modal-trigger-button"))));

		$execute_functions[]['function'] = "js_html_style_display";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-close-button"), "div_style" => base64_encode("inline-block"))));

		$execute_functions[]['function'] = "js_modal_show_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-main-modal"))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));

	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

function php_fluid_shipping_method_flags($f_checkout_id) {
	$f_ship_flags = Array("f_both" => 0, "f_pickup_only" => 0, "f_ship_only" => 0);

	if(isset($f_checkout_id)) {
		foreach($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] as $cart) {
			//$tmp_total = $tmp_total + ($cart['p_qty'] * $cart['p_price']);
			if($cart['p_instore'] == 0) {
				$f_ship_flags['f_both']++;
			}
			else if($cart['p_instore'] == 1) {
				$f_ship_flags['f_pickup_only']++;
			}
			else if($cart['p_instore'] == 2) {
				$f_ship_flags['f_pickup_only']++;
			}
		}
	}

	return $f_ship_flags;
}

function php_fluid_allow_free_shipping($f_cart_tmp = NULL, $a_data) {
	$f_free = TRUE;
	$fluid = new Fluid();

	if(isset($f_cart_tmp)) {
		foreach($f_cart_tmp as $cart) {
			if($cart['p_freeship'] == 0) {
				$f_free = FALSE;
				break;
			}

			// Allow free shipping on preorder items.
			if($fluid->php_item_available($cart['p_newarrivalenddate']) == FALSE && $cart['p_preorder'] == TRUE && FLUID_PREORDER == TRUE) {
				$f_free = TRUE;
				break;
			}

			// Allow free shipping or not for special order items if they are not in stock.
			if(FREE_SHIPPING_SPECIAL_ENABLED == FALSE) {
				if($cart['p_special_order'] == 1 && $cart['p_qty'] > $cart['p_stock']) {
					$f_free = FALSE;
					break;
				}
			}

			if(FREE_SHIPPING_NOT_ENOUGH_STOCK == FALSE) {
				if($cart['p_qty'] > $cart['p_stock']) {
					$f_free = FALSE;
					break;
				}
			}
		}
	}


	if(isset($a_data['a_country_iso3116'])) {
		if($a_data['a_country_iso3116'] == "CA") {
			if(isset($a_data['a_province_code'])) {
				$p_list = explode(";", base64_decode(FLUID_PROVINCES_EXCLUSIONS));

				$p_array = Array("AB" => NULL, "BC" => NULL, "MB" => NULL, "NB" => NULL, "NL" => NULL, "NS" => NULL, "ON" => NULL, "PE" => NULL, "QC" => NULL, "SK" => NULL, "NT,NU" => NULL, "YT" => NULL);

				foreach($p_list as $p_data) {
					$p_array[$p_data] = $p_data;
				}

				if($a_data['a_province_code'] == $p_array[$a_data['a_province_code']]) {
					$f_free = FALSE;
				}
			}
		}
	}

	return $f_free;
}

function php_fluid_shipping($f_data, $f_checkout_id, $f_override = FALSE) {
	$f_packages = php_fluid_shipping_boxes($f_checkout_id, $f_override);

	$detect = new Mobile_Detect;
	if($detect->isMobile() || $detect->isTablet()) {
		$f_mobile_touch = "Touch";
	}
	else {
		$f_mobile_touch = "Click";
	}

	$fluid_rates = NULL;
	$s_id = 0;

	$f_ship_flags = php_fluid_shipping_method_flags($f_checkout_id);

	if(ENABLE_IN_STORE_PICKUP == TRUE && $f_ship_flags['f_ship_only'] < 1) {
		//	--> In store pickup option.
		$rate_top_height_html = "<div style='display: table; min-height: 58px; width: 100%;'>";
		$rate_top_auto_html = "<div style='display: table; width: 100%;'>";

			$rate_top_html = "<div class='divTableBody'>";

				$rate_logo_html = "<div class='leos-width' style='display: table-cell; vertical-align:middle; text-align: left;'>";
					$rate_logo_html .= "<img class='fluid-ship-logo-large' src='" . $_SESSION['fluid_uri'] . "files/leos-logo-ship-large.png'></img>";
					$rate_logo_html .= "<img class='fluid-ship-logo-medium' src='" . $_SESSION['fluid_uri'] . "files/leos-logo-ship-small.png'></img>";
					$rate_logo_html .= "<img class='fluid-ship-logo-small' src='" . $_SESSION['fluid_uri'] . "files/leos-logo-ship-small.png'></img>";
				$rate_logo_html .= "</div>"; // cell

				$rate_body_html = "<div style='display: table-cell; vertical-align:middle; text-align: left;'>";
					$rate_body_html .= "<div style='padding-left: 5px;'>" . IN_STORE_PICKUP . " - FREE</div>";
						$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>1055 Granville St. Vancouver BC, V6Z1L4";
						$rate_body_html .= "</div>";
						
						if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
							$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>* You must pay for your order in store at time of pickup</div>";
						}

				$rate_body_html .= "</div>"; //cell

				$rate_edit_html = "<div style='display: table-cell; text-align: right;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>";
				$rate_selected_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>Selected</div> <i class='fa fa-check' aria-hidden='true'></i></div>";
				$rate_unselect_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>" . $f_mobile_touch . " to select</div> <span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";

			$rate_end_html = "</div>"; // divTableBody
		$rate_end_html .= "</div>"; // table

		$rate_html = $rate_top_auto_html . $rate_top_html . $rate_logo_html . $rate_body_html . $rate_edit_html . $rate_end_html;
		$rate_no_edit_html = $rate_top_height_html . $rate_top_html . $rate_logo_html . $rate_body_html;

		$rates = Array("ship_type" => IN_STORE_PICKUP, "price" => 0.00, "delivery_date_stamp" => NULL, "delivery_date" => NULL, "transit_time" => NULL, "f_package" => $f_packages);

		$fluid_rates[] = Array("s_id" => $s_id, "price" => 0.00, "free" => Array("free" => TRUE, "price" => 0.00, "tax" => 0.00, "total" => 0.00), "type" => IN_STORE_PICKUP, "data" => $rates, "html" => $rate_html, "html_no_edit" => $rate_no_edit_html, "html_selected" => $rate_selected_html, "html_unselect" => $rate_unselect_html, "html_close" => $rate_end_html);

		//	--> In store pickup option EOF.
	}

	$s_id++;

	if(ENABLE_CANADAPOST == TRUE && $f_ship_flags['f_pickup_only'] < 1) {
		// Ughh Canada Post, why can't you just accept multiple packages into your API at once like FedEx does, grrrrrr :( ....
		$canada_post = php_fluid_shipping_canada_post($f_data, $f_packages);

		if(isset($canada_post)) {
			$f_cart_sub_total = php_packages_value($f_packages);
			foreach($canada_post as $key => $rates_sub) {
				if(count($rates_sub) == count($f_packages)) {
					$rates = Array("ship_type" => NULL, "price" => NULL, "delivery_date_stamp" => NULL, "delivery_date" => NULL, "transit_time" => NULL, "f_package" => NULL, "options" => NULL, "s_data" => NULL);

					$f_rate_cov_total = 0;

					foreach($rates_sub as $rates_data) {
						$rates['ship_type'] = $key;
						$rates['price'] = $rates['price'] + $rates_data['price'];
						$rates['f_package'][] = $rates_data['f_package'];
						if(isset($rates_data['delivery_date_stamp'])) {
							$rates['delivery_date_stamp'] = $rates_data['delivery_date_stamp'];
							$rates['delivery_date'] = $rates_data['delivery_date'];
							$rates['transit_time'] = $rates_data['transit_time'];
						}

						// Check our shipping options.
						if(isset($rates_data['options'])) {
							foreach($rates_data['options'] as $f_options) {
								if(isset($f_options->{'option-code'})) {
									// --> Check if we have insured option, then add all the insured costs to display the total value of the order that is insured.
									if($f_options->{'option-code'} == 'COV') {
										if(isset($rates['options-code'][$f_options->{'option-code'}]['cov-value'])) {
											$rates['options-code'][$f_options->{'option-code'}]['cov-value'] = $rates['options-code'][$f_options->{'option-code'}]['cov-value'] + $f_options->{'cov-value'};
											$f_rate_cov_total = $rates['options-code'][$f_options->{'option-code'}]['cov-value'];
										}
										else {
											$rates['options-code'][$f_options->{'option-code'}]['cov-value'] = $f_options->{'cov-value'};
											$f_rate_cov_total = $f_options->{'cov-value'};
										}
									}

									// --> Check if we have signature option.
									if($f_options->{'option-code'} == 'SO') {
										$rates['options-code'][$f_options->{'option-code'}]['option-code'] = $f_options->{'option-code'};
									}
								}
							}
						}

						$rates['s_data'] = $rates_data['s_data'];
					}

					// --> Do not show shipping options that have insured values greater than the sub total of values of the items.
					if($f_rate_cov_total <= $f_cart_sub_total) {
						$f_rate_html = php_html_canada_post_shipping($rates, $key);

						// We have a insured option, lets flag it.
						if($f_rate_cov_total > 0) {
							$f_insured = TRUE;
						}
						else {
							$f_insured = FALSE;
						}

						$fluid_rates[] = Array("s_id" => $s_id, "insured" => $f_insured, "price" => $rates['price'], "type" => "Canada Post", "data" => $rates, "html" => $f_rate_html['rate_html'], "html_no_edit" => $f_rate_html['rate_no_edit_html'], "html_selected" => $f_rate_html['rate_selected_html'], "html_unselect" => $f_rate_html['rate_unselect_html'], "html_close" => $f_rate_html['rate_end_html']);

						$s_id++;
					}
				}
			}
		}
	}

	if(ENABLE_FEDEX == TRUE && $f_ship_flags['f_pickup_only'] < 1) {
		$fedex = php_fluid_shipping_fedex($f_data, $f_packages);

		if(isset($fedex)) {
			foreach($fedex as $key => $rates_sub) {

				foreach($rates_sub as $key_type => $rates_data) {
					// PAYOR_ACCOUNT_SHIPMENT --> Cheapest, our rate.
					// PAYOR_ACCOUNT_PACKAGE --> Ground shipping only.
					// RATED_ACCOUNT_SHIPMENT
					// PAYOR_LIST_SHIPMENT
					// PAYOR_LIST_PACKAGE --> Ground shipping only.
					// RATED_LIST_SHIPMENT --> List prices.

					/*
					echo "<pre>";
						print_r($rates_data['ship_type']);
						print_r(" : ");
						print_r($rates_data['price']);
						print_r(" --> ");
						print_r($key_type);
						print_r("<br>");
					echo "</pre>";
					*/

					if($key_type == "PAYOR_ACCOUNT_SHIPMENT") {
						$f_rate_html = php_html_fedex_shipping($rates_data, $key);

						if(isset($rates_data['insured_value'])) {
							$f_insured = TRUE;
						}
						else {
							$f_insured = FALSE;
						}

						$fluid_rates[] = Array("s_id" => $s_id, "insured" => $f_insured, "price" => $rates_data['price'], "type" => "FedEx", "data" => $rates_data, "html" => $f_rate_html['rate_html'], "html_no_edit" => $f_rate_html['rate_no_edit_html'], "html_selected" => $f_rate_html['rate_selected_html'], "html_unselect" => $f_rate_html['rate_unselect_html'], "html_close" => $f_rate_html['rate_end_html']);

						$s_id++;
					}
					else if($key_type == "PAYOR_LIST_PACKAGE") {
						// If we didn't find a ground shipment option above, it may exist as a PAYOR_LIST_PACKAGE type.
						$f_rate_html = php_html_fedex_shipping($rates_data, $key);

						if(isset($rates_data['insured_value'])) {
							$f_insured = TRUE;
						}
						else {
							$f_insured = FALSE;
						}

						$fluid_rates[] = Array("s_id" => $s_id, "insured" => $f_insured, "price" => $rates_data['price'], "type" => "FedEx", "data" => $rates_data, "html" => $f_rate_html['rate_html'], "html_no_edit" => $f_rate_html['rate_no_edit_html'], "html_selected" => $f_rate_html['rate_selected_html'], "html_unselect" => $f_rate_html['rate_unselect_html'], "html_close" => $f_rate_html['rate_end_html']);

						$s_id++;
					}
				}
			}
		}
	}

	return Array("fluid_rates" => $fluid_rates, "fluid_packing" => $f_packages);
}

// --> Generate html for FedEx shipping cards.
function php_html_fedex_shipping($rates_data, $key) {
	$detect = new Mobile_Detect;

	if($detect->isMobile() || $detect->isTablet()) {
		$f_mobile_touch = "Touch";
		$f_glyph = "glyphicon glyphicon-hand-up";
	}
	else {
		$f_mobile_touch = "Click";
		$f_glyph = "glyphicon glyphicon-plus";
	}

	$rate_top_height_html = "<div style='display: table; min-height: 58px; width: 100%;'>";
	$rate_top_auto_html = "<div style='display: table; width: 100%;'>";

		$rate_top_html = "<div class='divTableBody'>";

			$rate_logo_html = "<div class='fedex-width' style='display: table-cell; vertical-align:middle; text-align: left;'>";
				$rate_logo_html .= "<img class='fluid-ship-logo-large' src='" . $_SESSION['fluid_uri'] . "files/fedex.png'></img>";
				$rate_logo_html .= "<img class='fluid-ship-logo-medium' src='" . $_SESSION['fluid_uri'] . "files/fedexmedium.png'></img>";
				$rate_logo_html .= "<img class='fluid-ship-logo-small' src='" . $_SESSION['fluid_uri'] . "files/fedexsmall.png'></img>";
			$rate_logo_html .= "</div>"; // cell

			$rate_body_html = "<div style='display: table-cell; vertical-align:middle; text-align: left;'>";

				$rate_body_html .= "<div id='f-card-price-div' style='padding-left: 5px;'>" . $key . " - ";
				if($rates_data['price'] == 0)
					$rate_body_html .= "FREE";
				else
					$rate_body_html .= HTML_CURRENCY .  number_format($rates_data['price'], 2, '.', ',');

				$rate_body_html .= "</div>";

				if(isset($rates_data['delivery_date'])) {
					$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";

					if(isset($rates_data['delivery_date_html']))
						$rate_body_html .= "Estimated delivery: " . $rates_data['delivery_date_html'];
					else {
						$date1 = new DateTime(date("Y-m-d"));
						$date2 = new DateTime(date("Y-m-d", strtotime($rates_data['delivery_date'])));
						$diff = $date2->diff($date1)->format("%a");
						$rate_body_html .= "Estimated delivery: " . $diff . " day";

						if(abs($diff) > 1)
							$rate_body_html .= "s";
					}

					$rate_body_html .= "</div>";
				}

				// --> Check if we have insurance on this shipping option. Display the insured value if available.
				if(isset($rates_data['insured_value'])) {
					$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";
						$rate_body_html .= "Insured value: " . HTML_CURRENCY . number_format($rates_data['insured_value'], 2, '.', ',');
					$rate_body_html .= "</div>";
				}

				// --> Check what type of signature option there is on this shipment type.
				if(isset($rates_data['s_data']->SignatureOption)) {
					if($rates_data['s_data']->SignatureOption == "DIRECT" || $rates_data['s_data']->SignatureOption == "INDIRECT" || $rates_data['s_data']->SignatureOption == "ADULT") {
						$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";
							$rate_body_html .= "Signature upon delivery required.";
						$rate_body_html .= "</div>";
					}
				}

			$rate_body_html .= "</div>"; //cell

			$rate_edit_html = "<div style='display: table-cell; text-align: right;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>";
			$rate_selected_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>Selected</div> <i class='fa fa-check' aria-hidden='true'></i></div>";
			$rate_unselect_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>" . $f_mobile_touch . " to select</div> <span class='" . $f_glyph . "' aria-hidden='true'></span></div>";

		$rate_end_html = "</div>"; // divTableBody
	$rate_end_html .= "</div>"; // table

	$rate_html = $rate_top_auto_html . $rate_top_html . $rate_logo_html . $rate_body_html . $rate_edit_html . $rate_end_html;
	$rate_no_edit_html = $rate_top_height_html . $rate_top_html . $rate_logo_html . $rate_body_html;

	return Array("rate_html" => $rate_html, "rate_no_edit_html" => $rate_no_edit_html, "rate_selected_html" => $rate_selected_html, "rate_unselect_html" => $rate_unselect_html, "rate_end_html" => $rate_end_html);
}

// --> Generate html for Canada Post shipping cards.
function php_html_canada_post_shipping($rates, $key) {
	$detect = new Mobile_Detect;

	if($detect->isMobile() || $detect->isTablet()) {
		$f_mobile_touch = "Touch";
		$f_glyph = "glyphicon glyphicon-hand-up";
	}
	else {
		$f_mobile_touch = "Click";
		$f_glyph = "glyphicon glyphicon-plus";
	}

	$rate_top_height_html = "<div style='display: table; min-height: 58px; width: 100%;'>";
	$rate_top_auto_html = "<div style='display: table; width: 100%;'>";

		$rate_top_html = "<div class='divTableBody'>";

			$rate_logo_html = "<div class='canadapost-width' style='display: table-cell; vertical-align:middle; text-align: left;'>";
				$rate_logo_html .= "<img class='fluid-ship-logo-large' src='" . $_SESSION['fluid_uri'] . "files/canadapost.png'></img>";
				$rate_logo_html .= "<img class='fluid-ship-logo-medium' src='" . $_SESSION['fluid_uri'] . "files/canadaposticon.png'></img>";
				$rate_logo_html .= "<img class='fluid-ship-logo-small' src='" . $_SESSION['fluid_uri'] . "files/canadaposticon.png'></img>";
			$rate_logo_html .= "</div>"; // cell

			$rate_body_html = "<div style='display: table-cell; vertical-align:middle; text-align: left;'>";

				$rate_body_html .= "<div id='f-card-price-div' style='padding-left: 5px;'>" . $key . " - ";
				if($rates['price'] == 0)
					$rate_body_html .= "FREE";
				else
					$rate_body_html .= HTML_CURRENCY . number_format($rates['price'], 2, '.', ',');

				$rate_body_html .=  "</div>";

				if(isset($rates['delivery_date'])) {
					$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";

					$date1 = new DateTime(date("Y-m-d"));
					$date2 = new DateTime(date("Y-m-d", strtotime($rates['delivery_date'])));
					$diff = $date2->diff($date1)->format("%a");
					$rate_body_html .= "Estimated delivery: " . $diff . " day";

					if(abs($diff) > 1)
						$rate_body_html .= "s";

					/*
					$rate_body_html .= $rates['delivery_date'];

					if(isset($rates['transit_time'])) {
						$rate_body_html .= " (";
						$rate_body_html .= $rates['transit_time'];

						if($rates['transit_time'] > 1)
							$rate_body_html .= " Business Days)";
						else
							$rate_body_html .= " Business Day)";
					}
					*/
					$rate_body_html .= "</div>";
				}

			// Check our shipping options.
			if(isset($rates['options-code'])) {
				foreach($rates['options-code'] as $f_options) {
					// --> Check if we have insurance on this shipping option. Display the insured value if available.
					if(isset($f_options['cov-value'])) {
						$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";
							$rate_body_html .= "Insured value: " . HTML_CURRENCY . number_format($f_options['cov-value'], 2, '.', ',');
						$rate_body_html .= "</div>";
					}

					// --> Check if the shipment has the signature option.
					if(isset($f_options['option-code'])) {
						if($f_options['option-code'] == "SO") {
							$rate_body_html .= "<div style='font-size: 10px; padding-left: 5px;'>";
								$rate_body_html .= "Signature upon delivery required.";
							$rate_body_html .= "</div>";
						}
					}
				}
			}

			$rate_body_html .= "</div>"; //cell

			$rate_edit_html = "<div style='display: table-cell; text-align: right;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>";
			$rate_selected_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>Selected</div> <i class='fa fa-check' aria-hidden='true'></i></div>";
			$rate_unselect_html = "<div style='display: table-cell; text-align: right; vertical-align:middle; font-style: italic;'><div class='fluid-desktop'>" . $f_mobile_touch . " to select</div> <span class='" . $f_glyph. "' aria-hidden='true'></span></div>";

		$rate_end_html = "</div>"; // divTableBody
	$rate_end_html .= "</div>"; // table

	$rate_html = $rate_top_auto_html . $rate_top_html . $rate_logo_html . $rate_body_html . $rate_edit_html . $rate_end_html;
	$rate_no_edit_html = $rate_top_height_html . $rate_top_html . $rate_logo_html . $rate_body_html;

	return Array("rate_html" => $rate_html, "rate_no_edit_html" => $rate_no_edit_html, "rate_selected_html" => $rate_selected_html, "rate_unselect_html" => $rate_unselect_html, "rate_end_html" => $rate_end_html);
}

function php_fluid_shipping_canada_post($f_data, $f_packages) {
	require_once("../3rd-party-src/canadapost-api/canadapost.php");

	// **************************---------------_+++++++++++++++++++++----------->>>>>>>>>>>>>>>>
	// Need to fix issue when selecting Australia, with 2 or more parcels with current cart setup, no shipping options as each of the shipping packages have different shipping needs.
	// Perhaps if none can be issued together, force fedex only? or force a minimum box size instead to get it up to at least expedited parcel range on box 2 and more.?
	// --^ This above issue is fixed if reset the max box girth settings to 1.5m. However, now when setting the current cart to 1 item each, nothing shows up.
	// **************************---------------_+++++++++++++++++++++----------->>>>>>>>>>>>>>>>

	$f_rates = NULL;

	if(isset($f_packages)) {
		if(isset($f_data->f_checkout_id)) {
			$f_data['f_cart_total'] = php_cart_sub_total($f_data->f_checkout_id);
		}
		else {
			$f_data['f_cart_total'] = php_cart_sub_total();
		}

		$f_data['f_package_count'] = count($f_packages);
		$f_data['a_postalcode_origin'] = FLUID_ORIGIN_POSTAL_CODE;

		$f_ship_data = Array("packages" => $f_packages, "username" => CANADA_POST_USERNAME, "password" => CANADA_POST_PASSWORD, "customer_number" => CANADA_POST_CUSTOMER_NUMBER);

		$f_rates = php_canada_post_soap($f_data, $f_ship_data, CANADA_POST_SIGNATURE); // soap api.
	}

	return $f_rates;
}

function php_fluid_shipping_fedex($f_data, $f_packages) {
	require_once("../3rd-party-src/fedex-api/fedex.php");
	$f_rates = NULL;

	$fluid = new Fluid ();

	if(isset($f_packages)) {
		if($f_data['a_country_iso3116'] == "CA") {
			 $f_data['a_province_code'] = $fluid->php_fluid_provincial_code($f_data['a_postalcode']);
		}
		else if($f_data['a_country_iso3116'] == "US") {
			$f_data['a_province_code'] = $fluid->php_fluid_state_abbr($f_data['a_province']);
		}

		// FedEx SOAP allows to send multi item packages into the request.
		$f_data['f_ship_data'] = $f_packages;

		if(isset($f_data->f_checkout_id))
			$f_data['f_cart_total'] = php_cart_sub_total($f_data->f_checkout_id);
		else
			$f_data['f_cart_total'] = php_cart_sub_total();

		$f_data['f_package_count'] = count($f_data['f_ship_data']);
		$f_data['f_package_values'] = php_packages_value($f_packages);

		$f_data['fedex_key'] = FEDEX_KEY;
		$f_data['fedex_password'] = FEDEX_PASSWORD;
		$f_data['fedex_account'] = FEDEX_ACCOUNT;
		$f_data['fedex_meter'] = FEDEX_METER;
		$f_data['fedex_person_name'] = FEDEX_PERSON;
		$f_data['fedex_company_name'] = FEDEX_COMPANY;
		$f_data['fedex_phone_number'] = FEDEX_PHONE;
		$f_data['fedex_street'] = FEDEX_STREET;
		$f_data['fedex_city'] = FEDEX_CITY;
		$f_data['fedex_province'] = FEDEX_PROVINCE;
		$f_data['fedex_postal_code'] = FEDEX_POSTAL_CODE;
		$f_data['fedex_country'] = FEDEX_COUNTRY_CODE;

		$f_rates = php_fedex($f_data, FEDEX_SIGNATURE);
	}

	return $f_rates;
}

function php_packages_value($f_packages) {
	$total = 0;

	if(isset($f_packages)) {
		foreach($f_packages as $packages) {
			if(isset($packages['price'])) {
				$total = $total + $packages['price'];
			}
		}
	}

	return $total;
}

// Pack our items into boxes.
function php_fluid_shipping_boxes($f_checkout_id, $f_override = FALSE) {
	/*
		Canada Post REST and SOAP api can only accept 1 package per api call :( grrrrrr...
		Canada Post also limits each package insured value up to 5000 :(
		Canada Post limits per package:
		Length, width or height: 1.5 m
		Length + Girth*: 3 m
		Girth = (height x 2) + (width x 2)
		Weight: 30 kg
	*/

	// *--------------------------------------------------------->>
	// 3D Boxpacker algorithm to pack the boxes.
	// *--------------------------------------------------------->>
	$f_packages = NULL;

	if(isset($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'])) {
		require_once ("../fluid.box.php");
		$fluid_boxes = new Fluid();
		$fluid_boxes->php_db_begin();
		$fluid_boxes->php_db_query("SELECT * FROM " . TABLE_SHIPPING_BOXES . " ORDER BY b_id ASC");
		$fluid_boxes->php_db_commit();

		if(isset($fluid_boxes->db_array)) {
			$fluid = new DVDoug\BoxPacker\FluidShipping ($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'], $fluid_boxes->db_array);
			$f_packages = $fluid->php_fluid_packed_boxes();
		}
	}
	else if($f_override == TRUE) {
		require_once ("../fluid.box.php");
		$fluid_boxes = new Fluid();
		$fluid_boxes->php_db_begin();
		$fluid_boxes->php_db_query("SELECT * FROM " . TABLE_SHIPPING_BOXES . " ORDER BY b_id ASC");
		$fluid_boxes->php_db_commit();

		if(isset($fluid_boxes->db_array)) {
			$fluid = new DVDoug\BoxPacker\FluidShipping ($_SESSION['fluid_cart'], $fluid_boxes->db_array);
			$f_packages = $fluid->php_fluid_packed_boxes();
		}
	}

	return $f_packages;
}

// Selects the selected address for getting a shipping quote in the cart.
function php_fluid_address_ship_select($data) {
	try {
		$_SESSION['f_address_tmp_ship_select'] = base64_decode($data);

		return php_html_cart(NULL, TRUE);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// The almighty powerful fluid checkout load. This is the starting point for the checkout system, everything is passed through here and processed. Muwahahhahahaahahaawahaa!!
function php_fluid_checkout_load($data = NULL, $f_onload = NULL) {
	try {
		$fluid = new Fluid ();
		$detect = new Mobile_Detect;

		if($detect->isMobile()) {
			$f_glyph = "glyphicon glyphicon-hand-up";
			$f_touch = "Touch";
		}
		else {
			$f_glyph = "glyphicon glyphicon-plus";
			$f_touch = "Click";
		}

		if(FLUID_SHIP_NON_BILLING == FALSE)
			$f_ship_message_header = "shipping & billing address";
		else
			$f_ship_message_header = "shipping address";

		$html = "<div style='text-align: center;'><span class='" . $f_glyph . "'></span><div>" . $f_touch . " to add a " . $f_ship_message_header . "</div></div>";
		$a_id = NULL;
		$a_data = NULL;
		$f_cart_empty = 0;

		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = TRUE;

		if(isset($_SESSION['u_id']) || isset($data)) {
			if(isset($_SESSION['u_id'])) {
				$fluid->php_db_begin();

				// A little bit of extra security.
				if(isset($data->a_id)) {
					$f_where = "a_id = '" . base64_decode($data->a_id) . "'";

					if(isset($_SESSION['u_id']))
						$f_where .= " AND a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'";
					else
						$f_where .= " AND a_u_id IS NULL";
				}
				else if(isset($_SESSION['u_id']))
					$f_where = "a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'";

				$fluid->php_db_query("SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE " . $f_where . " ORDER BY a_default DESC LIMIT 1");

				$fluid->php_db_commit();

				if(isset($fluid->db_array)) {
					foreach($fluid->db_array as $key => $f_data) {
						$a_id = base64_encode($f_data['a_id']);

						$_SESSION['f_checkout'][$data->f_checkout_id]['a_id'] = $a_id; // --> Reset the selected shipping address id.

						// Need to utf8_encode data, incase of special characters and accents.
						// Do not need to anymore, as they are encoded during address creation. So instead, we just decode them later when required.
						foreach($f_data as $f_key => $aa_data)
							$a_data[$f_key] = $aa_data;

						//$a_data = $f_data;

						break;
					}
				}
			}
			else {
				if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list']))
					if(isset($data->a_id))
						if($_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list'][base64_decode($data->a_id)]) {
							$a_data = $_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list'][base64_decode($data->a_id)];
							$a_id = $data->a_id;
							$_SESSION['f_checkout'][$data->f_checkout_id]['a_id'] = $a_id; // --> Reset the selected shipping address id.
						}
			}
		}

		$f_ga_data = NULL;
		if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart']))
			$f_ga_data['items'] = $_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'];

		$f_ga_data['step'] = 1;
		$f_ga_step['step_option'] = "Checkout Step 1";
		$f_ga_step['data'] = "Checkout Step 1";

		$f_shipping_check = Array("valid" => TRUE, "error" => FALSE, "rates" => NULL, "free_id" => NULL);
		// If a address is selected, lets generate some last bits of data for it.
		if(isset($a_data)) {
			// Check to see if we need to update the $_SESSION address payment if required when the billing address is suppose to be the same as the shipping address.
			if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address'])) {
				if($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']['a_billing_same'] == 1) {
					unset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']);
					unset($_SESSION['f_checkout'][$data->f_checkout_id]['a_id']);

					// --> ** Should utf8 encoding be removed? On this payment address part? It may not be required ??
					$_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address'] = Array("a_name" => utf8_encode($a_data['a_name']), "a_number" => utf8_encode($a_data['a_number']), "a_street" => utf8_encode($a_data['a_street']), "a_city" => utf8_encode($a_data['a_city']), "a_province" => utf8_encode($a_data['a_province']), "a_country" => utf8_encode($a_data['a_country']), "a_postalcode" => utf8_encode($a_data['a_postalcode']), "a_phonenumber" => utf8_encode($a_data['a_phonenumber']), "a_billing_same" => 1);
				}
			}

			// Process extra shipping information that is required for php_fluid_shipping().
			$a_data['a_country_iso3116'] = $fluid->php_country_name_to_ISO3166($a_data['a_country'], 'US');

			// --> Detect and set Canada shipping only temporary.
			if($a_data['a_country_iso3116'] != "CA")
				$f_shipping_check = $f_shipping_check = Array("valid" => FALSE, "error" => FALSE, "rates" => NULL, "free_id" => NULL);

			if($a_data['a_country_iso3116'] == "CA")
				 $a_data['a_province_code'] = $fluid->php_fluid_provincial_code($a_data['a_postalcode']);
			else if($a_data['a_country_iso3116'] == "US")
				$a_data['a_province_code'] = $fluid->php_fluid_state_abbr($a_data['a_province']);
			else
				$a_data['a_province_code'] = NULL;

			// Generate some HTML of the selected address to display on the checkout page.
			if(FLUID_SHIP_NON_BILLING == FALSE) {
				$f_ship_message = "Shipping & Billing address:";
			}
			else {
				$f_ship_message = "Shipping to:";
			}

			$html = "
				<div style='display: table; width: 100%; font-weight: 400;'>
					<div style='display: table-cell; color: rgba(0, 0, 0, 0.3);'>" . $f_ship_message  . "</div>
					<div style='display: table-cell; text-align: right;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>
				</div>

				<div style='display: table; width: 100%;'>
					<div style='display: table-cell; padding-right: 5px;' class='fa fa-check' aria-hidden='true'></div>
					<div style='display: table-cell;'>";


					$html .= utf8_decode($a_data['a_name']);
					$html .= ", ";

					if($a_data['a_number'] != "")
						$html .= utf8_decode($a_data['a_number']) . " - ";

					$html .= utf8_decode($a_data['a_street']) . " ";
					$html .= utf8_decode($a_data['a_city']);
					$html .= ", " . utf8_decode($a_data['a_province']) . " ";
					$html .= utf8_decode($a_data['a_country']) . " ";
					$html .= utf8_decode($a_data['a_postalcode']);
			$html .= "</div>
				</div>

				<div id='f-shipping-error-div' style='display: none;'>
					<div style='display: table; width: 100%; font-weight: 400;'>
						<div style='display: table-cell; color: rgba(236, 40, 40, 0.8);'><i class='fa fa-meh-o'></i> We currently only ship to Canada at this time.</div>
					</div>
				</div>
			";
		}

		$execute_functions[]['function'] = "js_html_insert";

		$f_shipping_modal_html = NULL;
		// Only load shipping address via ajax requests. Since it can take some time to get a response back from the shipping companies servers.
		if(isset($a_data) && isset($data) && isset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart']) && empty($f_onload)) {
			// Calculate shipping and packing information.
			$fluid_shipping = php_fluid_shipping($a_data, $data->f_checkout_id);
			$f_shipping = $fluid_shipping['fluid_rates'];
			$f_shipping_check['rates'] = $fluid_shipping['fluid_rates'];

			// Store the box packing information in a $_SESSION variable so we can record and notify the shipping department how to pack the boxes.
			//$_SESSION['f_checkout'][$data->f_checkout_id]['f_packages'] = $fluid_shipping['fluid_packing']; // --> No longer needed. Packing information is stored into the shipping data, as packing information can be different depending on shipping options like insurance, etc.

			// Calculate taxes.
			$t_taxes = php_fluid_taxes(Array("a_data" => $a_data, "f_cart" => $_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'], "f_rates" => $fluid_shipping['fluid_rates']));

			// Resort the shipping data from cheapest to most expensive.
			$f_sort = array();
			foreach($f_shipping as $k => $v)
				$f_sort[$k] = $v['price'];

			$f_ship_lowest = $f_shipping;

			// Store the cheapest shipping rates into the $_SESSION variable as it will be selected as default.
			$_SESSION['f_checkout'][$data->f_checkout_id]['f_rates'] = $f_ship_lowest;

			array_multisort($f_sort, SORT_ASC, $f_ship_lowest);

			$f_shipping_html = NULL;
			$f_shipping_price = NULL;
			$t_taxes_html = NULL;
			$t_taxes_price = NULL;
			$s_data = NULL;
			$f_animate_id = NULL; // --> Data that is processed by js_fluid_block_animate();

			// Checkout totals displayed ici.
			$fluid_cart_subtotal = round(php_cart_sub_total($data->f_checkout_id), 2);

			$fluid_discount = php_discount_cart($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart']);

			// Used for calculating the price / shipping difference to see if the order qualifies for free shipping.
			$fluid_cart_cost_subtotal = round(php_cart_cost_sub_total($data->f_checkout_id), 2);

			$html_e_price_checkout = "<div style='display: table;'>";
				$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row"), "delay" => 0, "colour" => "#0050FF");
				$html_e_price_checkout .= "<div id='fluid-sub-total-row' style='text-align: right;'>"; // --> This div is used for animating.
					$html_e_price_checkout .= "<div style='display: table-row; text-align:right;'>";
						$html_e_price_checkout .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Sub Total: </div><div style='display: table-cell; text-align: right;'> " . HTML_CURRENCY . " " . number_format($fluid_cart_subtotal, 2, ".", ",") . "</div>";
					$html_e_price_checkout .= "</div>";
				$html_e_price_checkout .= "</div>";

				if(isset($fluid_discount)) {
					if($fluid_discount > 0) {
						$f_animate_id[] = Array("id" => base64_encode("fluid-checkout-discount-row"), "delay" => 250, "colour" => "#FF0028");
						$html_e_price_checkout .= "<div id='fluid-checkout-discount-row' style='text-align: right;'>"; // --> This div is used for animating.
							$html_e_price_checkout .= "<div style='display: table-row; text-align: right;'>";
								$html_e_price_checkout .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Discount:</div><div id='fluid-checkout-total-discount' style='display: table-cell; text-align: right;'></div>";
							$html_e_price_checkout .= "</div>";
						$html_e_price_checkout .= "</div>";
					}
				}

				$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row"), "delay" => 250, "colour" => "#5EFF00");
				$html_e_price_checkout .= "<div id='fluid-shipping-row' style='text-align: right;'>"; // --> This div is used for animating.
					$html_e_price_checkout .= "<div style='display: table-row; text-align: right;'>";
						$html_e_price_checkout .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Shipping:</div><div id='fluid-checkout-shipping-price' style='display: table-cell; text-align: right;'></div>";
					$html_e_price_checkout .= "</div>";
				$html_e_price_checkout .= "</div>";

				$html_e_price_checkout .= "<div id='fluid-tax-container'>";

				$f_ship_flags = php_fluid_shipping_method_flags($data->f_checkout_id);
				// Load the cheapest shipping option and calculate the tax and final totals. This is going to be the default shipping option.
				foreach($f_ship_lowest as $k => $f_lowest) {
					// Skip the first shipping item in the array. This will be the pickup in store option. Never default load this one.
					if( ((ENABLE_CANADAPOST == TRUE || ENABLE_FEDEX == TRUE) && $k != 0 && $f_ship_flags['f_pickup_only'] < 1) || (ENABLE_CANADAPOST == FALSE && ENABLE_FEDEX == FALSE && ENABLE_IN_STORE_PICKUP == TRUE) || (ENABLE_IN_STORE_PICKUP == TRUE && $f_ship_flags['f_pickup_only'] > 0)) {
						// --> Calculate the order cost / shipping price ratio to determine if this cheapest shipping option qualifies for free shipping or not.
						$f_tax_ship = NULL;
						// --> Calculate the tax on the shipping to add to the free shipping cost calculator.
						if(isset($t_taxes)) {
							foreach($t_taxes as $t_key => $t_data) {
								$f_tax_ship = $f_tax_ship + $t_data['f_rates'][$f_lowest['s_id']]['t_total'];
							}
						}

						$f_cost = $fluid_cart_cost_subtotal + $f_lowest['price'] + $f_tax_ship;
						$f_profit = $fluid_cart_subtotal - $f_cost - $fluid_discount;
						$f_margin = ($f_profit / $fluid_cart_subtotal) * 100;

						$f_ga_data['step'] = 2;
						$f_ga_data['step_option'] = $f_lowest['type'];
						$f_ga_data['data'] = $f_lowest['type'];

						$f_free_shipping = FALSE;

						if(FREE_SHIPPING_FORMULA_ENABLED == TRUE && php_fluid_allow_free_shipping($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'], $a_data) == TRUE) {
							if($fluid_cart_subtotal < FREE_SHIPPING_CART_TOTAL_STEP_1 && $f_margin > FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1) {
								$f_free_shipping = TRUE;
							}
							else if($fluid_cart_subtotal < FREE_SHIPPING_CART_TOTAL_STEP_2 && $fluid_cart_subtotal >= FREE_SHIPPING_CART_TOTAL_STEP_1 && $f_margin > FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2) {
								$f_free_shipping = TRUE;
							}
							else if($fluid_cart_subtotal < FREE_SHIPPING_CART_TOTAL_STEP_3 && $fluid_cart_subtotal >= FREE_SHIPPING_CART_TOTAL_STEP_2 && $f_margin > FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3) {
								$f_free_shipping = TRUE;
							}
							else if($fluid_cart_subtotal < FREE_SHIPPING_CART_TOTAL_STEP_4 && $fluid_cart_subtotal >= FREE_SHIPPING_CART_TOTAL_STEP_3 && $f_margin > FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4) {
								$f_free_shipping = TRUE;
							}
							else if($fluid_cart_subtotal >= FREE_SHIPPING_CART_TOTAL_STEP_5 && $f_margin > FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5) {
								$f_free_shipping = TRUE;
							}
							else {
								$f_free_shipping = FALSE;
							}
						}

						// Disable free shipping for FedEx.
						if($f_lowest['type'] == "FedEx") {
							$f_free_shipping = FALSE;
						}

						// --> Oversized items do not qualify for free shipping, so lets see if any oversized items in the cart.
						if(FREE_SHIPPING_OVERSIZED_ENABLED == FALSE && $f_free_shipping = TRUE) {
							$fluid->php_db_begin();
							$fluid->php_db_query("SELECT * FROM " . TABLE_SHIPPING_BOXES . " ORDER BY b_id ASC");
							$fluid->php_db_commit();

							$f_boxes_array = NULL;
							if(isset($fluid->db_array)) {
								foreach($fluid->db_array as $f_boxes_data) {
									$f_boxes_array[] = $f_boxes_data['b_name'];
								}

								foreach($f_ship_lowest[$k]['data']['f_package'] as $f_package_data) {
									if(!in_array($f_package_data['f_box_type'], $f_boxes_array)) {
										$f_free_shipping = FALSE;
										break;
									}
								}
							}
							else {
								$f_free_shippping = FALSE; // --> Everything is oversized as no shipping boxes are pre-defined.
							}
						}

						// --> If order qualifies for free shipping, lets make the necessary changes.
						if($f_free_shipping == TRUE && $k > 0) {
							// --> Update the taxes.
							if(isset($t_taxes)) {
								foreach($t_taxes as $t_key => $t_data) {
									$t_taxes[$t_key]['f_rates'][$f_lowest['s_id']]['t_total'] = 0.00;
								}
							}

							// --> Update the shipping cost.
							$f_ship_lowest[$k]['free'] = Array("free" => TRUE, "price" => $f_lowest['price'], "tax" => $f_tax_ship, "total" => $f_lowest['price'] + $f_tax_ship);
							$f_ship_lowest[$k]['price'] = 0.00;
							$f_ship_lowest[$k]['data']['price'] = 0.00;

							$f_shipping[$f_lowest['s_id']]['free'] = Array("free" => TRUE, "price" => $f_lowest['price'], "tax" => $f_tax_ship, "total" => $f_lowest['price'] + $f_tax_ship);
							$f_shipping[$f_lowest['s_id']]['price'] = 0.00;
							$f_shipping[$f_lowest['s_id']]['data']['price'] = 0.00;

							if($f_lowest['type'] == "Canada Post") {
								$f_card_html = php_html_canada_post_shipping($f_ship_lowest[$k]['data'], $f_lowest['data']['ship_type']);
							}
							else if($f_lowest['type'] == "FedEx") {
								$f_card_html = php_html_fedex_shipping($f_ship_lowest[$k]['data'], $f_lowest['data']['ship_type']);
							}

							// Only need to update f_shipping only.
							$f_shipping[$f_lowest['s_id']]['html'] = $f_card_html['rate_html'];
							$f_shipping[$f_lowest['s_id']]['html_no_edit'] = $f_card_html['rate_no_edit_html'];
							$f_shipping[$f_lowest['s_id']]['html_selected'] = $f_card_html['rate_selected_html'];
							$f_shipping[$f_lowest['s_id']]['html_unselect'] = $f_card_html['rate_unselect_html'];
							$f_shipping[$f_lowest['s_id']]['html_close'] = $f_card_html['rate_end_html'];

							// Reset the cheapest shipping option into the $_SESSION variable.
							$_SESSION['f_checkout'][$data->f_checkout_id]['f_rates'] = $f_shipping;

							$f_shipping_check['free_id'] = $f_lowest['s_id'];
							$f_shipping_check['rates'][$f_lowest['s_id']]['html'] = $f_card_html['rate_html'];
						}

						// Then we want to default to show only the cheapest shipping option on the loading card as default.
						$f_shipping_html = $f_shipping[$f_lowest['s_id']]['html'];
						$f_shipping_price = $f_ship_lowest[$k]['price'];

						if($f_ship_lowest[$k]['price'] == 0) {
							$f_price_ship_html = "FREE";
						}
						else {
							$f_price_ship_html = HTML_CURRENCY . " " . number_format($f_ship_lowest[$k]['price'], 2, ".", ",");
						}

						$s_data = Array("s_id" => base64_encode($f_lowest['s_id']), "s_price" => $f_price_ship_html);

						// Update the selected shipping.
						$_SESSION['f_checkout'][$data->f_checkout_id]['s_id'] = $f_lowest['s_id'];

						// Clear out old tax totals in the $_SESSION variable.
						if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['taxes'])) {
							unset($_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['taxes']);
						}

						// Calculate the taxes for the currently selected shipping and address settings.
						if(isset($t_taxes)) {
							foreach($t_taxes as $t_key => $t_data) {
								$tmp_total = round($t_data['p_total'] + $t_data['f_rates'][$f_lowest['s_id']]['t_total'], 2);
								$t_taxes_price = round($t_taxes_price + $tmp_total, 2);

								// Keep track of the current tax totals for the selected shipping and address settings.
								//$_SESSION['f_totals']['taxes'][$t_data][$t_key] = Array("t_id" => $t_key, "name" => $t_data, "total" => $tmp_total);

								$f_animate_id[] = Array("id" => base64_encode("fluid-tax-row-" . $t_key), "delay" => 250, "colour" => "#FF006B"); // --> id name for the animation div. This will be procssed by js_fluid_block_animate();
								$html_e_price_checkout .= "<div name='fluid-taxes-div' id='fluid-tax-row-" . $t_key . "' style='text-align: right;'>"; // --> This div is used for animating.
									$html_e_price_checkout .= "<div id='tax-" . $t_key . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_data['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($tmp_total, 2, '.', ',') . "</div></div>";
								$html_e_price_checkout .= "</div>";
							}
						}

						break;
					}
				}
				$html_e_price_checkout .= "</div>"; // fluid-tax-container

				$f_animate_id[] = Array("id" => base64_encode("fluid-checkout-row"), "delay" => 250, "colour" => "#FFD600");
				$html_e_price_checkout .= "<div id='fluid-checkout-row' style='text-align: right;'>"; // --> This div is used for animating.
					$html_e_price_checkout .= "<div style='display: table-row; text-align: right;'>";
						$html_e_price_checkout .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Total:</div><div id='fluid-checkout-total' style='display: table-cell; text-align: right;'></div>";
					$html_e_price_checkout .= "</div>";
				$html_e_price_checkout .= "</div>";
			$html_e_price_checkout .= "</div>"; //table

			// Build the modal html data set.
			$f_canada_post = NULL;
			$f_fedex = NULL;
			$f_instore = NULL;
			foreach($f_shipping as $key => $f_data) {
				/*
					--> Modify $java, to insert new tax data, and total price when a new shipping is selected, send the data to js_fluid_checkout_shipping_select();
				*/
				$java_taxes = NULL;
				$t_taxes_price_loop = 0;
				if(isset($t_taxes)) {
					foreach($t_taxes as $t_key => $t_data) {
						$tmp_total = round($t_data['p_total'] + $t_data['f_rates'][$f_data['s_id']]['t_total'], 2);
						$t_taxes_price_loop = round($t_taxes_price_loop + $tmp_total, 2);
					}
				}

				$java_fluid_cart_total = round($fluid_cart_subtotal + $f_data['price'] + $t_taxes_price_loop, 2);
				$java_total_price_html = HTML_CURRENCY . " " . number_format($java_fluid_cart_total, 2, ".", ",");

				if($f_data['price'] == 0)
					$f_price_ship_html = "FREE";
				else
					$f_price_ship_html = HTML_CURRENCY . " " . number_format($f_data['price'], 2, ".", ",");

				$java = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_html_insert({div_id : \"" . base64_encode('fluid-shipping-div') . "\", html : \"" . base64_encode($f_data['html']) . "\" }); $(function () { $(\"#fluid-shipping-modal\").modal(\"toggle\"); }); FluidGa[\"step\"] = 2; FluidGa[\"step_option\"] = \"" . $f_data['type'] . "\"; FluidGa[\"data\"] = \"" . $f_data['type'] . "\"; FluidGa[\"step2\"] = \"" . $f_data['type'] . "\"; js_ga_checkout_steps(FluidGa); js_fluid_checkout_shipping_select(\"" . base64_encode(json_encode(Array("s_id" => base64_encode($f_data['s_id']), "s_price" => $f_price_ship_html))) . "\"); js_fluid_refresh_totals();'";

				$f_html = "<div id='fluid-shipping-method-" . $f_data['s_id'] . "' name='fluid-shipping-method' data-id='" . base64_encode($f_data['s_id']) . "' class='well fluid-box-shadow-small-well fluid-div-highlight fluid-shipping-options' style='display: table; width: 100%;'" . $java . ">";
				$f_selected_html = "<div id='fluid-shipping-method-selected-" . $f_data['s_id'] . "' name='fluid-shipping-method-selected' data-id='" . base64_encode($f_data['s_id']) . "' class='well fluid-box-shadow-small-well fluid-div-highlight fluid-shipping-options' style='display: table; width: 100%; background-color: rgba(45,255,93,0.5);'" . $java . ">";

					$f_html .= $f_data['html_no_edit'];
					$f_selected_html .= $f_data['html_no_edit'];

					$f_html .= $f_data['html_unselect'];
					$f_selected_html .= $f_data['html_selected'];

					$f_html .= $f_data['html_close'];
					$f_selected_html .= $f_data['html_close'];
				$f_html .= "</div>";
				$f_selected_html .= "</div>";

				if($f_data['type'] == "Canada Post")
					$f_canada_post .= $f_html . $f_selected_html;
				else if($f_data['type'] == "FedEx")
					$f_fedex .= $f_html . $f_selected_html;
				else if($f_data['type'] == IN_STORE_PICKUP)
					$f_instore .= $f_html . $f_selected_html;
			}

			$f_shipping_modal_html = $f_instore . $f_canada_post . $f_fedex;

			if($s_data != NULL) {
				// --> If no shipping is enabled, we are doing to default to in stock pickup.
				//if($s_data == NULL)
					//$s_data = Array("s_id" => base64_encode(0), "s_price" => "FREE");

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-shipping-div"), "html" => base64_encode($f_shipping_modal_html))));

				/*
					// --> Fluid Cart total html data.
				*/
				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-totals"), "html" => base64_encode($html_e_price_checkout))));

				$execute_functions[]['function'] = "js_fluid_checkout_shipping_select";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($s_data))));

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-shipping-price"), "html" => base64_encode($s_data['s_price']))));

				$execute_functions[]['function'] = "js_html_style_display";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-shipping-box-div"), "div_style" => base64_encode("block"))));

				$execute_functions[]['function'] = "js_html_style_display";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-payment-box-div"), "div_style" => base64_encode("block"))));

				$fluid_cart_total = round(($fluid_cart_subtotal + $f_shipping_price + $t_taxes_price) - $fluid_discount, 2);
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['total'] = $fluid_cart_total;
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['discount'] = $fluid_discount;
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['sub_total'] = $fluid_cart_subtotal;
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['shipping'] = $f_shipping_price;
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['tax_total'] = $t_taxes_price;

				if(isset($fluid_discount)) {
					if($fluid_discount > 0) {
						$f_discount_price_html = HTML_CURRENCY . " " . number_format($fluid_discount, 2, ".", ",");
						$execute_functions[]['function'] = "js_html_insert";
						end($execute_functions);
						$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-total-discount"), "html" => base64_encode($f_discount_price_html))));
					}
				}

				$f_total_price_html = HTML_CURRENCY . " " . number_format($fluid_cart_total, 2, ".", ",");
				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-total"), "html" => base64_encode($f_total_price_html))));

				$execute_functions[]['function'] = "js_html_save_checkout_totals_div";

				$execute_functions[]['function'] = "js_fluid_block_animate";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));
			}
			else {
				$f_shipping_check = Array("valid" => TRUE, "error" => TRUE, "rates" => NULL, "free_id" => NULL);

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-cart-totals"), "html" => base64_encode(""))));

				$f_shipping_html = "<div style='text-align: center;'><span class='glyphicon glyphicon-asterisk'></span><div>Add a shipping address first</div></div>";

				$execute_functions[]['function'] = "js_html_save_checkout_totals_div";
			}
		}
		else {
			//if(empty($_SESSION['fluid_cart']))
			if(empty($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'])) {
				$f_shipping_html = "<div style='text-align: center;'><span class='glyphicon glyphicon-shopping-cart'></span><div>Your cart is empty.</div></div>";
				$f_cart_empty = 1;
			}
			else
				$f_shipping_html = "<div style='text-align: center;'><span class='glyphicon glyphicon-asterisk'></span><div>Add a shipping address first</div></div>";

				$execute_functions[]['function'] = "js_html_save_checkout_totals_div";
		}

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-shipping-div"), "html" => base64_encode($f_shipping_html))));

		// Update / store the currently selected shipping address into the session.
		if(empty($a_data))
			$a_data = Array("a_name" => "", "a_number" => "", "a_street" => "", "a_city" => "", "a_province" => "", "a_country" => "", "a_postalcode" => "", "a_phonenumber" => "");

		$_SESSION['f_checkout'][$data->f_checkout_id]['f_address'] = $a_data;

		$execute_functions[]['function'] = "js_fluid_payment_temp_address_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($a_data))));

		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'])) {
			/*
			$execute_functions[]['function'] = "js_html_style_display";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-shipping-box-div"), "div_style" => base64_encode("none"))));

			$execute_functions[]['function'] = "js_html_style_display";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-payment-box-div"), "div_style" => base64_encode("none"))));
			*/
		}

		$execute_functions[]['function'] = "js_fluid_cart_status";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode($f_cart_empty);

		$execute_functions[]['function'] = "js_fluid_shipping_check";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_shipping_check));

		if(isset($data->f_paypal))
			if($data->f_paypal == TRUE)
				$execute_functions[]['function'] = "js_fluid_paypal_button_render";
			else
				$execute_functions[]['function'] = "js_fluid_paypal_button_remove";
		else
			$execute_functions[]['function'] = "js_fluid_paypal_button_remove";

		$execute_functions[]['function'] = "js_ga_set_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_ga_data));

		$execute_functions[]['function'] = "js_fluid_check_order_button";

		if(!empty($data->f_add_to_cart)) {
			$execute_functions[]['function'] = "js_fluid_add_to_cart_animation";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = $data->f_add_to_cart; // --> The data has been already encoded via the php_html_cart function().
		}

		$execute_functions[]['function'] = "js_fluid_split_order_card";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(php_fluid_check_split_order($data->f_checkout_id)));

		$fluid_log = new Fluid();
		$fluid_log->php_db_begin();
		$fluid_log->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('shipping query', '" . $fluid_log->php_escape_string(serialize(print_r($_SESSION, TRUE))) . "')");
		$fluid_log->php_db_commit();

		$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = FALSE;
		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "a_id" => $a_id, "a_data" => base64_encode(json_encode($a_data)), "div_id" => base64_encode("fluid-checkout-address-shipping-div"), "html" => base64_encode($html)));
	}
	catch (Exception $err) {
		if(isset($_SESSION['f_checkout'][$data->f_checkout_id]))
			$_SESSION['f_checkout'][$data->f_checkout_id]['f_prevent_hack'] = FALSE;

		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

// --> Get the type of credit card
function php_fluid_credit_card_type($str, $format = 'string') {
	if (empty($str)) {
		return false;
	}

	$matchingPatterns = [
		'Visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
		'MasterCard' => '/^5[1-5][0-9]{14}$/',
		'Amex' => '/^3[47][0-9]{13}$/',
		'Diners' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
		'Discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
		'Jcb' => '/^(?:2131|1800|35\d{3})\d{11}$/',
		'Credit Card' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/'
	];

	$ctr = 1;
	foreach ($matchingPatterns as $key=>$pattern) {
		if (preg_match($pattern, $str)) {
			return $format == 'string' ? $key : $ctr;
		}
		$ctr++;
	}
}

function php_fluid_payment_save($data = NULL) {
	try {
		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		/*
			--> Build a credit card check here, to see if the card number is valid and if it is a visa, american express, master card, etc.
		*/
		if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart']))
			$f_ga_data['items'] = $_SESSION['f_checkout'][$data->f_checkout_id]['fluid_cart'];

		$f_ga_data['step'] = 3;

		if(isset($data->f_paypal)) {
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address'] = Array("a_name" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_name'], "a_number" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_number'], "a_street" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_street'], "a_city" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_city'], "a_province" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_province'], "a_country" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_country'], "a_postalcode" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_postalcode'], "a_phonenumber" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_address']['a_phonenumber'], "a_billing_same" => TRUE);

				$html = "
					<div style='display: table; width: 100%; font-weight: 400;'>
						<div style='display: table-cell;'><div class='fa fa-check' aria-hidden='true'></div><img style='margin: 5px 5px 5px 5px;' src='/files/paypal-logo.png'></img></div>
						<div style='display: table-cell; text-align: right; vertical-align: top;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>
					</div>
				";

				$f_ga_data['step_option'] = "PayPal";
				$f_ga_data['data'] = "PayPal";

				$execute_functions[]['function'] = "js_fluid_paypal_button_render";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("total" => $_SESSION['f_checkout'][$data->f_checkout_id]['f_totals']['total'], "currency" => STORE_CURRENCY)));

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-payment-inner-box-div"), "html" => base64_encode($html))));
		}
		else {
			$f_card_num = preg_replace('/\s+/', '', $data->payment->c_number);
			$f_card_num = str_replace('-', '', $f_card_num);
			$f_card_type = php_fluid_credit_card_type($f_card_num);
			$f_html = NULL;
			$f_cc_error_html = NULL;
			$f_cc_valid = TRUE;

			$detect = new Mobile_Detect;

			if($detect->isMobile()) {
				$f_glyph = "glyphicon glyphicon-hand-up";
				$f_touch = "Touch";
			}
			else {
				$f_glyph = "glyphicon glyphicon-plus";
				$f_touch = "Click";
			}

			switch($f_card_type) {
				case "Visa":
					$f_html = $f_card_type . " <div class='fa fa-cc-visa'></div>";
					$f_ga_data['step_option'] = "Visa";
					$f_ga_data['data'] = "Visa";
					break;
				case "MasterCard":
					$f_html = $f_card_type . " <div class='fa fa-cc-mastercard'></div>";
					$f_ga_data['step_option'] = "MasterCard";
					$f_ga_data['data'] = "MasterCard";
					break;
				case "Amex":
					//$f_html = $f_card_type . " <div class='fa fa-cc-amex'></div>";
					$f_html = "<div style='text-align: center;'><span class='" . $f_glyph . "'></span><div>" . $f_touch . " to select a payment method</div></div>";
					$f_cc_error_html = "Sorry, we only accept Visa or MasterCard for credit cards at this moment. We also accept American Express via PayPal.";
					$f_cc_valid = FALSE;
					$f_ga_data['step_option'] = "Amex";
					$f_ga_data['data'] = "Amex";
					break;
				default:
					$f_cc_valid = FALSE;
					$f_html = "<div style='text-align: center;'><span class='" . $f_glyph . "'></span><div>" . $f_touch . " to select a payment method</div></div>";
					$f_cc_error_html = "Sorry, we only accept Visa or MasterCard for credit cards at this moment. We also accept American Express via PayPal.";
					$f_ga_data['step_option'] = "Unknown Credit Card Type";
					$f_ga_data['data'] = "Unknown Credit Card Type";
			}

			if($f_cc_valid == TRUE) {
				if($data->payment_address->a_country == "Canada") {
					$f_postal_code = preg_replace('/\s+/', '', $data->payment_address->a_postalcode);
					$f_postal_code = str_replace('-', '', $f_postal_code);
					$f_postal_code = strtoupper($f_postal_code);
				}
				else if($data->payment_address->a_country == "United States") {
					$f_postal_code = preg_replace('/\s+/', '', $data->payment_address->a_postalcode);
					$f_postal_code = preg_replace('/\D/', '', $data->payment_address->a_postalcode);
				}
				else {
					$f_postal_code = $data->payment_address->a_postalcode;
				}

				$_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address'] = Array("a_name" => $data->payment_address->a_name, "a_number" => $data->payment_address->a_number, "a_street" => $data->payment_address->a_street, "a_city" => $data->payment_address->a_city, "a_province" => $data->payment_address->a_province, "a_country" => $data->payment_address->a_country, "a_postalcode" => $data->payment_address->a_postalcode, "a_phonenumber" => $data->payment_address->a_phonenumber, "a_billing_same" => $data->payment_address->a_billing_same);

				$html = "
					<div style='display: table; width: 100%; font-weight: 400;'>
						<div style='display: table-cell;'><div class='fa fa-check' aria-hidden='true'></div> " . $f_html . "</div>
						<div style='display: table-cell; text-align: right;' class='glyphicon glyphicon-edit' aria-hidden='true'></div>
					</div>

					<div style='display: table; width: 100%;'>
						<div style='display: table-cell; padding-left: 5px; display: inline-block; color: rgba(0, 0, 0, 0.3);'>### " . substr($f_card_num, -3) . "</div>
						<div style='display: table-cell;'>Exp: " . $data->payment->c_exp_m . "/" . $data->payment->c_exp_y . "</div>
					</div>
				";

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-payment-inner-box-div"), "html" => base64_encode($html))));
			}
			else {
				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-payment-inner-box-div"), "html" => base64_encode($f_html))));

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode($f_cc_error_html))));

				$execute_functions[]['function'] = "js_fluid_payment_empty";

				if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']))
					unset($_SESSION['f_checkout'][$data->f_checkout_id]['f_payment_address']);

				$execute_functions[]['function'] = "js_modal_show_data";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));
			}

			$execute_functions[]['function'] = "js_fluid_paypal_button_remove";
		}

		$execute_functions[]['function'] = "js_ga_set_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_ga_data));

		$execute_functions[]['function'] = "js_fluid_check_order_button";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

// --> Refresh the totals.
function php_fluid_refresh_totals($f_data = NULL) {
	try {
		if(empty($_SESSION['f_checkout'][$f_data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");
		else if(!isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id']))
			throw new Exception("shipping id error");

		$f_animate_id = NULL;
		$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row"), "delay" => 0, "colour" => "#0050FF");
		$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row"), "delay" => 250, "colour" => "#5EFF00");

		// Update the selected shipping.
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['s_id'] = base64_decode($f_data->shipping->s_id);

		// Update the shipping split option.
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['s_ship_split'] = $f_data->ship_split;

		$f_rates[base64_decode($f_data->shipping->s_id)] = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_rates'][base64_decode($f_data->shipping->s_id)];
		$t_taxes = php_fluid_taxes(Array("a_data" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address'], "f_cart" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart'], "f_rates" => $f_rates));

		$f_tax_total = NULL;
		$f_tax_id = NULL;
		$tax_html_temp = NULL;

		if(isset($t_taxes)) {
			foreach($t_taxes as $t_tax) {
				$t_tmp_total = NULL;

				$t_tmp_total = $t_tax['p_total'];

				$f_animate_id[] = Array("id" => base64_encode("fluid-tax-row-" . $t_tax['t_id']), "delay" => 250, "colour" => "#FF006B");

				if(isset($t_tax['f_rates']))
					foreach($t_tax['f_rates'] as $s_tax)
						$t_tmp_total = $t_tmp_total + $s_tax['t_total'];

				$f_tax_total = $f_tax_total + round($t_tmp_total, 2);

				$f_tax_id[] = Array("id" => "fluid-tax-row-" . $t_tax['t_id'], "t_name" => $t_tax['t_name'], "t_total" => round($t_tmp_total, 2));

				$tax_html_temp .= "<div name='fluid-taxes-div' id='fluid-tax-row-" . $t_tax['t_id'] . "' style='text-align: right;'>"; // --> This div is used for animating.
					$tax_html_temp .= "<div id='tax-" . $t_tax['t_id'] . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_tax['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($t_tmp_total, 2, '.', ',') . "</div></div>";
				$tax_html_temp .= "</div>";
			}

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-tax-container"), "html" => base64_encode($tax_html_temp))));
		}
		else {
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-tax-container"), "html" => base64_encode(""))));
		}

		$fluid_discount = php_discount_cart($_SESSION['f_checkout'][$f_data->f_checkout_id]['fluid_cart']);

		if($fluid_discount > 0)
			$f_animate_id[] = Array("id" => base64_encode("fluid-checkout-discount-row"), "delay" => 250, "colour" => "#FF0028");

		$f_animate_id[] = Array("id" => base64_encode("fluid-checkout-row"), "delay" => 250, "colour" => "#FFD600");

		if(isset($f_data->f_checkout_id))
			$fluid_cart_subtotal = round(php_cart_sub_total($f_data->f_checkout_id), 2);
		else
			$fluid_cart_subtotal = round(php_cart_sub_total(), 2);

		$f_tax_total = round($f_tax_total, 2);

		$f_shipping_price = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_rates'][base64_decode($f_data->shipping->s_id)]['price'];
		$fluid_cart_total = round(($fluid_cart_subtotal + $f_shipping_price + $f_tax_total) - $fluid_discount, 2);

		$_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'] = $fluid_cart_total;
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['discount'] = $fluid_discount;
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['sub_total'] = $fluid_cart_subtotal;
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['shipping'] = $f_shipping_price;
		$_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['tax_total'] = $f_tax_total;

		if($f_shipping_price == 0)
			$f_total_price_html = "FREE";
		else
			$f_total_price_html = HTML_CURRENCY . " " . number_format($f_shipping_price, 2, ".", ",");

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-shipping-price"), "html" => base64_encode($f_total_price_html))));

		/*
		if(isset($f_tax_id)) {
			foreach($f_tax_id as $f_tax) {
				$tax_html_temp = "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $f_tax['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($f_tax['t_total'], 2, '.', ',') . "</div>";

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode($f_tax['id']), "html" => base64_encode($tax_html_temp))));
			}
		}
		*/

		if(isset($fluid_discount)) {
			if($fluid_discount > 0) {
				$f_total_discount_html = HTML_CURRENCY . " " . number_format($fluid_discount, 2, ".", ",");
				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-total-discount"), "html" => base64_encode($f_total_discount_html))));
			}
		}

		$f_total_price_html = HTML_CURRENCY . " " . number_format($fluid_cart_total, 2, ".", ",");
		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-checkout-total"), "html" => base64_encode($f_total_price_html))));

		$execute_functions[]['function'] = "js_fluid_block_animate";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));

		if($f_data->f_paypal == TRUE) {
			$execute_functions[]['function'] = "js_fluid_paypal_button_render";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("total" => $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_totals']['total'], "currency" => STORE_CURRENCY)));
		}
		else
			$execute_functions[]['function'] = "js_fluid_paypal_button_remove";

		$execute_functions[]['function'] = "js_fluid_check_order_button";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		//return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
		return php_fluid_error($err, TRUE, FLUID_CART);
	}
}

function php_main_fluid_cart() {

	if(isset($_SESSION['fluid_cart']) == FALSE || FLUID_STORE_OPEN == FALSE) {
		header("Location: " . WWW_SITE);
		exit(0);
	}

	// Clear any old checkout processes. Prevents various hacking attempts.
	if(isset($_SESSION['f_checkout']))
		unset($_SESSION['f_checkout']);

	require_once("header.php");

	require_once(FLUID_ACCOUNT);
	require_once(FLUID_CART);

	$f_checkout_id = base64_encode(uniqid('fluid_', true));
	$_SESSION['f_checkout'][$f_checkout_id]['f_checkout_id'] = $f_checkout_id;
	$_SESSION['f_checkout'][$f_checkout_id]['fluid_cart'] = $_SESSION['fluid_cart'];

	// --> Set the shipping split option.
	$_SESSION['f_checkout'][$f_checkout_id]['s_ship_split'] = FALSE;

	$fluid = new Fluid ();
?>
	<!DOCTYPE html>

	<html lang="en">
	<head>
		<?php
		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			//$_SESSION['u_oauth_id']
		?>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-21150353-5"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-21150353-5');

			  <?php
			  if(isset($_SESSION['u_oauth_id']))
				echo "gtag('set', {'user_id': '" . $_SESSION['u_oauth_id'] . "'});";
			  ?>
			</script>
		<?php
		}
		?>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags --> ?>
		<title>Leos Camera Supply - Secured Checkout</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-cart.css');?>" media="screen, projection">
		<link rel="stylesheet" type="text/css" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-cart-print.css');?>" media="print">

	<?php
	php_load_pre_header();

	$detect = new Mobile_Detect;

	// If not touch devices, then keep the scroll bar when modals are open.
	if(!$detect->isMobile()) {
		echo "
			<style>
				body {
					padding-right: 0px !important;
				}

				.modal-open {
					overflow-y: auto !important;
				}
			</style>
		";
	}
	?>

	<script src="https://www.paypalobjects.com/api/checkout.js"></script>

	</head>

	<body>

	<div class='css-loading-overlay' id='loading-overlay'>
		<div style='position: absolute; top: 50%; width: 100%; margin:0 auto; opacity: 1.0;'>
			<div style='display:table; margin: 0 auto;'><h3><i class="fa fa-refresh fa-spin-fluid fa-4x fa-fw"></i></h3><span class="sr-only">Loading...</span></div>
		</div>
	</div>

	<?php
	echo HTML_ERROR_MODAL;
	echo HTML_MODAL_FLUID;
	echo HTML_MODAL_FLUID_SHIPPING;
	echo HTML_CONFIRM_MODAL;
	echo HTML_MODAL_FLUID_CHECKOUT_THANK_YOU;
	echo HTML_MODAL_FLUID_CHECKOUT;
	echo HTML_MODAL;

	/*
		--> Create a $_SESESION['checkout'] and do $_SESSION['checkout']++ when the cart page loads.
			if the checkout variable is 1, things are ok. IF it is greater than 1, then it means you have a checkout already open (most likely another tab) and prevent make it take you back to the main page or not load.
			when not on a cart page on the site, the checkout variable is set to 0. when adding to cart, check this variable, if it's set to 1, dont add to cart from non checkout page and vice versa.
	*/
	php_load_header(TRUE); // Load minimized cart version.
	php_google_maps_autofill($f_checkout_id); // --> fluid.account.php
	?>

	<script>
		FluidGa = {};
		FluidGa.items = JSON.parse(Base64.decode("<?php echo base64_encode(json_encode($_SESSION['f_checkout'][$f_checkout_id]['fluid_cart']));?>"));
		FluidGa.step = 1;
		FluidGa.prev_step = 1;
		FluidGa.step_option = "Checkout Step 1";
		FluidGa.data = "Checkout Step 1";
		FluidGa.step2 = null
		FluidGa.step3 = null;

		FluidMenu.shipping = {};
		FluidMenu.payment = {};
		FluidMenu.payment_address = {};
		FluidMenu.cart['totals_html'] = "<?php echo base64_encode(""); ?>";
		FluidMenu.paypal = false;
		FluidMenu.ship_split = false;

		FluidTemp.f_checkout_id = "<?php echo $f_checkout_id; ?>";
		FluidTemp.tmp = "checkout";
		FluidTemp.payment = false;
		FluidTemp.shipping = false;
		FluidTemp.f_refresh_shipping = false;
		FluidTemp.tmp_address_modal = "#fluid-main-modal";
		FluidTemp.tmp_modal = "#fluid-main-modal";
		FluidTemp.prevent_order = true;
		FluidTemp.detailed_stack = 0;
		FluidTemp.cart_empty = 0;
		FluidTemp.cart_edit = 0;

		FluidTemp.shipping_check = {};
		FluidTemp.shipping_check['valid'] = false; <?php // --> Used to set a flag whether there is a problem with the shipping address. At this point, it is used to make sure Canada is set as shipping only for the moment. ?>
		FluidTemp.shipping_check['error'] = false; <?php // --> Used when a shipping address has errors in the input preventing getting a shipping quotation. ?>

		<?php // --> A little functionality which adds performance increase on mobile devices by remove the fluid-flash css when a modal is open. ?>
		$(window).on('shown.bs.modal', function() {
			//$(".fluid-flash").removeClass("fluid-flash");
			$(".fluid-flash").removeClass('fluid-flash').addClass('fluid-flash-holder');
			$(".fluid-btn-success-pulse").removeClass('fluid-btn-success-pulse').addClass('fluid-btn-success-pulse-holder');
		});

		<?php // --> Then it brings the fluid-flash back after a modal is closed. ?>
		$(window).on('hidden.bs.modal', function() {
			$(".fluid-flash-holder").removeClass('fluid-flash-holder').addClass('fluid-flash');
			$(".fluid-btn-success-pulse-holder").removeClass('fluid-btn-success-pulse-holder').addClass('fluid-btn-success-pulse');
		});

		function js_ga_set_data(data) {
			FluidGa['prev_step'] = FluidGa['step'];
			FluidGa['items']  = data['items'];
			FluidGa['step'] = data['step'];
			FluidGa['step_option'] = data['step_option'];
			FluidGa['data'] = data['data'];

			if(data['step'] == 2)
				FluidGa['step2'] = data['step_option'];

			if(data['step'] == 3)
				FluidGa['step3'] = data['step_option'];
		}

		function js_ga_checkout_steps(data) {
			<?php
			if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			?>
				ga('set', 'currencyCode', '<?php echo STORE_CURRENCY; ?>');

				if(data['items'] != null) {
					$.each(data['items'], function(key, value) {

						ga('ec:addProduct', {
						  'id': value['p_mfgcode'],
						  'name': value['m_name'] + " " + value['p_name'],
						  'category': value['c_name'],
						  'brand': value['m_name'],
						  'variant': value['p_mfgcode'],
						  'price': value['p_price'],
						  'quantity': value['p_qty']
						});
					}
					);

					ga('ec:setAction','checkout', {'step': data['step']});

					ga('send', 'pageview');
				}
			<?php
			}
			?>
		}

		function js_ga_checkout(data) {
			<?php
			if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			?>
				ga('set', 'currencyCode', '<?php echo STORE_CURRENCY; ?>');

				$.each(data['items'], function(key, value) {
					ga('ec:addProduct', {
					  'id': value['p_mfgcode'],
					  'name': value['m_name'] + " " + value['p_name'],
					  'category': value['c_name'],
					  'brand': value['m_name'],
					  'variant': value['p_mfgcode'],
					  'price': value['p_price'],
					  'quantity': value['p_qty']
					});
				}
				);

				ga('ec:setAction', 'purchase', {
				  id: data['invoice']['id'],
				  affiliation: '<?php echo FLUID_STORE_NAME; ?>',
				  revenue: data['invoice']['total'],
				  tax: data['invoice']['tax'],
				  shipping: data['invoice']['shipping']
				});

				ga('send', 'pageview');
			<?php
			}
			?>
		}

		function js_fluid_cart_status(data) {
			FluidTemp.cart_empty = data;
		}

		<?php // --> Used to set a flag whether there is a problem with the shipping address. At this point, it is used to make sure Canada is set as shipping only for the moment. ?>
		function js_fluid_shipping_check(data) {
			FluidTemp.shipping_check = data;

			if(FluidTemp.shipping_check['error'] == true) {
				FluidMenu.shipping.s_id = null;
				js_debug_error("Error: There is a problem with your shipping address. Please verify and correct any errors in the address.");
			}
		}

		function js_check_stack_status(element, f_class, f_chevron) {
			var f_chevron_element = document.getElementById(f_chevron);

			if((' ' + element.className + ' ').indexOf(' ' + f_class + ' ') > -1) {
				f_chevron_element.className = f_chevron_element.className.replace( /(?:^|\s)glyphicon-chevron-right(?!\S)/g , '' )
				f_chevron_element.className = f_chevron_element.className.replace( /(?:^|\s)glyphicon-chevron-down(?!\S)/g , '' )
				f_chevron_element.className += " glyphicon-chevron-down";
			}
			else {
				f_chevron_element.className = f_chevron_element.className.replace( /(?:^|\s)glyphicon-chevron-right(?!\S)/g , '' )
				f_chevron_element.className = f_chevron_element.className.replace( /(?:^|\s)glyphicon-chevron-down(?!\S)/g , '' )
				f_chevron_element.className += " glyphicon-chevron-right";
			}
		}

		function js_html_save_checkout_totals_div() {
			if(document.getElementById('fluid-cart-totals') != null)
				FluidMenu.cart['totals_html'] = Base64.encode(document.getElementById('fluid-cart-totals').innerHTML);
			else
				FluidMenu.cart['totals_html'] = "<?php echo base64_encode(""); ?>";
		}

		function js_fluid_check_stock(f_data) {
			FluidTemp.f_stock_error = f_data;
		}

		function js_fluid_cart_set_div() {
			try {
				var f_address_div = document.getElementById('fluid-checkout-address-shipping-div');
				var f_ship_box_div = document.getElementById('fluid-shipping-box-div');
				var f_ship_div = document.getElementById('fluid-shipping-div');
				var f_payment_box_div = document.getElementById('fluid-payment-box-div');
				var f_payment_inner_div = document.getElementById('fluid-payment-inner-box-div');
				var f_arrow_1 = document.getElementById('f-arrow-hide-1');
				var f_arrow_2 = document.getElementById('f-arrow-hide-2');
				var f_address_container_div = document.getElementById('fluid-card-left-address-div');

				var f_paypal_mobile_div = document.getElementById('paypal-button-nav');
				f_paypal_mobile_div.style.display = "none";

				f_address_container_div.className += " fluid-blur-class";
				f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
				f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

				f_ship_box_div.className += " fluid-blur-class";
				f_ship_div.className = f_ship_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

				f_payment_box_div.className += " fluid-blur-class";
				f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
				f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

				f_arrow_1.className += " fluid-blur-class";
				f_arrow_2.className += " fluid-blur-class";

				FluidTemp.cart_edit = 1; <?php // A flag to tell we are editing the cart in the checkout mode. ?>

				var f_btn = document.getElementById('fluid-place-order-button');
				var f_btn_lg = document.getElementById('fluid-place-order-button-lg');

				f_btn.className += " fluid-btn-grey disabled";
				f_btn.className = f_btn.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
				f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );

				f_btn.disabled = true;

				if(f_btn_lg != null) {
					f_btn_lg.className += " fluid-btn-grey disabled";
					f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
					f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );

					f_btn_lg.disabled = true;
				}

				FluidTemp.prevent_order = true;
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_ship_split(f_split) {
			try {
				FluidMenu.ship_split = f_split;

				var f_split_true = document.getElementById('f-split-true');
				var f_split_false = document.getElementById('f-split-false');

				if(FluidMenu.ship_split == false) {
					f_split_true.className = f_split_true.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
					f_split_true.className = f_split_true.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
					f_split_true.className += " fluid-div-select";

					f_split_false.className = f_split_false.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
					f_split_false.className = f_split_false.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
					f_split_false.className += " fluid-div-orange-highlight";

					document.getElementById('f-split-check-false').style.display = "inline-block";
					document.getElementById('f-split-remove-false').style.display = "none";
					document.getElementById('f-split-check-true').style.display = "none";
					document.getElementById('f-split-remove-true').style.display = "inline-block";
				}
				else {
					f_split_false.className = f_split_false.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
					f_split_false.className = f_split_false.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
					f_split_false.className += " fluid-div-select";

					f_split_true.className = f_split_true.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
					f_split_true.className = f_split_true.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
					f_split_true.className += " fluid-div-orange-highlight";

					document.getElementById('f-split-check-false').style.display = "none";
					document.getElementById('f-split-remove-false').style.display = "inline-block";
					document.getElementById('f-split-check-true').style.display = "inline-block";
					document.getElementById('f-split-remove-true').style.display = "none";
				}
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_split_order_card(f_split) {
			try {
				var f_div_stock = document.getElementsByName('f-cart-no-stock');

				if(f_split == false) {
					<?php
					/*
					for(var x=0; x < f_div_stock.length; x++) {
						f_div_stock[x].style.display = "none";
					}
					*/
					?>
					document.getElementById('f-split-ship-card').style.display = "none";
					js_fluid_ship_split(f_split);
					FluidMenu.ship_split = null;
				}
				else {
					<?php
					/*
					for(var x=0; x < f_div_stock.length; x++) {
						f_div_stock[x].style.display = "block";
					}
					*/
					?>
					document.getElementById('f-split-ship-card').style.display = "block";

					if(FluidMenu.ship_split == null) {
						FluidMenu.ship_split = false;
						js_fluid_ship_split(false);
					}
				}
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_check_order_button() {
			try {
				<?php // Make sure the we set the flag for editing the cart to 0. Used when controlling when modals are opened. We dont want to open modals while in cart editing mode. ?>
				FluidTemp.cart_edit = 0;
				var f_step = 1;

				var f_paypal_mobile_div = document.getElementById('paypal-button-nav');
				f_paypal_mobile_div.style.display = "block";

				var f_btn = document.getElementById('fluid-place-order-button');
				var f_btn_lg = document.getElementById('fluid-place-order-button-lg');

				var f_address_div = document.getElementById('fluid-checkout-address-shipping-div');
				var f_ship_box_div = document.getElementById('fluid-shipping-box-div');
				var f_ship_div = document.getElementById('fluid-shipping-div');
				var f_payment_box_div = document.getElementById('fluid-payment-box-div');
				var f_payment_inner_div = document.getElementById('fluid-payment-inner-box-div');
				var f_arrow_1 = document.getElementById('f-arrow-hide-1');
				var f_arrow_2 = document.getElementById('f-arrow-hide-2');
				var f_address_container_div = document.getElementById('fluid-card-left-address-div');

				var f_shipping_error_div = document.getElementById('f-shipping-error-div');
				var f_cart_box_div = document.getElementById('fluid-cart-dropdown');

				if(FluidTemp.cart_empty == 1) {
					f_address_container_div.className += " fluid-blur-class";
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );

					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

					f_ship_box_div.className += " fluid-blur-class";
					f_ship_div.className = f_ship_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

					f_payment_box_div.className += " fluid-blur-class";
					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );

					f_arrow_1.className += " fluid-blur-class";
					f_arrow_2.className += " fluid-blur-class";

					f_cart_box_div.className += " fluid-flash";

					FluidTemp.prevent_order = true;
				}
				else if(FluidMenu.shipping['a_id'] != null && FluidTemp.shipping_check['error'] == false) {
					f_step = 2;
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );

					f_address_container_div.className = f_address_container_div.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
					f_address_div.className = f_address_div.className += ' fluid-div-highlight';

					f_ship_div.className = f_ship_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_ship_box_div.className = f_ship_box_div.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
					f_ship_div.className = f_ship_div.className += ' fluid-div-highlight';

					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_payment_box_div.className = f_payment_box_div.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
					f_payment_inner_div.className += ' fluid-div-highlight';

					if(FluidMenu.payment['c_number'] == null)
						f_payment_inner_div.className += " fluid-flash";
					else {
						f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
						f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
					}

					f_arrow_1.className = f_arrow_1.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
					f_arrow_2.className = f_arrow_2.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );

					f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );

					if(FluidTemp.shipping_check['valid'] == false && FluidMenu.shipping['s_id'] != "MA==")
						f_shipping_error_div.style.display = "block";
					else
						f_shipping_error_div.style.display = "none";
				}
				else {
					f_step = 1;
					f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_address_container_div.className = f_address_container_div.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
					f_address_div.className = f_address_div.className += ' fluid-div-highlight';
					f_address_div.className += " fluid-flash";

					f_ship_div.className = f_ship_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_ship_box_div.className += " fluid-blur-class";

					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
					f_payment_box_div.className += " fluid-blur-class";

					f_arrow_1.className += " fluid-blur-class";
					f_arrow_2.className += " fluid-blur-class";

					f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
					f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
				}

				if(FluidMenu.payment['c_number'] != null && FluidMenu.shipping['s_id'] != null && FluidTemp.f_stock_error != 1 && FluidTemp.cart_empty != 1) {
					<?php // --> Temporary, if in store pickup selected, we do not care about shipping address. ?>
					<?php
					if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
					?>
					if(FluidMenu.shipping['s_id'] == "MA==") {
						f_step = 3;
						f_btn.className = f_btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
						f_btn.className += " btn-success fluid-btn-success-pulse";

						f_btn.disabled = false;

						if(f_btn_lg != null) {
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
							f_btn_lg.className += " btn-success fluid-btn-success-pulse";

							f_btn_lg.disabled = false;
						}

						FluidTemp.prevent_order = false;

						f_shipping_error_div.style.display = "none";
						
						if(FluidMenu.paypal == true) {
							js_fluid_paypal_button_remove();
						}
						
						f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
						f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
						f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
						f_payment_box_div.className += " fluid-blur-class";
						f_arrow_2.className += " fluid-blur-class";

						<?php
						/*
						if(FluidMenu.paypal == true)
							if(document.getElementById('paypal-button') != null && document.getElementById('paypal-button-nav') != null)
								if(document.getElementById('paypal-button').innerHTML == '' || document.getElementById('paypal-button-nav').innerHTML)
									js_fluid_paypal_button_render();
						*/
						?>
					}
					else if(FluidTemp.shipping_check['valid'] == true) {
						f_step = 3;
						f_btn.className = f_btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
						f_btn.className += " btn-success fluid-btn-success-pulse";

						f_btn.disabled = false;

						if(f_btn_lg != null) {
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
							f_btn_lg.className += " btn-success fluid-btn-success-pulse";

							f_btn_lg.disabled = false;
						}

						FluidTemp.prevent_order = false;

						f_shipping_error_div.style.display = "none";
						
						if(FluidMenu.paypal == true) {
							if(document.getElementById('paypal-button') != null && document.getElementById('paypal-button-nav') != null) {
								if(document.getElementById('paypal-button').innerHTML == '' || document.getElementById('paypal-button-nav').innerHTML) {
									js_fluid_paypal_button_render();
								}
							}
						}
					}
					<?php
					}
					else {
					?>
					if(FluidMenu.payment['c_number'] != null && FluidMenu.shipping['s_id'] != null && FluidTemp.f_stock_error != 1 && FluidTemp.cart_empty != 1) {
						<?php // --> Temporary, if in store pickup selected, we do not care about shipping address. ?>
						if(FluidMenu.shipping['s_id'] == "MA==" || FluidTemp.shipping_check['valid'] == true) {
							f_step = 3;
							f_btn.className = f_btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
							f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
							f_btn.className += " btn-success fluid-btn-success-pulse";

							f_btn.disabled = false;

							if(f_btn_lg != null) {
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
								f_btn_lg.className += " btn-success fluid-btn-success-pulse";

								f_btn_lg.disabled = false;
							}

							FluidTemp.prevent_order = false;

							f_shipping_error_div.style.display = "none";

							if(FluidMenu.paypal == true) {
								if(document.getElementById('paypal-button') != null && document.getElementById('paypal-button-nav') != null) {
									if(document.getElementById('paypal-button').innerHTML == '' || document.getElementById('paypal-button-nav').innerHTML) {
										js_fluid_paypal_button_render();
									}
								}
							}
						}
					}
					<?php
					}
					?>
					else {
						<?php // --> Disable the confirm order button. Same code as in the below else statement. ?>
						f_btn.className += " fluid-btn-grey disabled";
						f_btn.className = f_btn.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

						f_btn.disabled = true;

						if(f_btn_lg != null) {
							f_btn_lg.className += " fluid-btn-grey disabled";
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

							f_btn_lg.disabled = true;
						}

						FluidTemp.prevent_order = true;

						if(FluidTemp.shipping_check['valid'] == false) {
							f_shipping_error_div.style.display = "block";

							f_address_div.className = f_address_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
							f_address_container_div.className = f_address_container_div.className.replace( /(?:^|\s)fluid-blur-class(?!\S)/g , '' );
							f_address_div.className = f_address_div.className += ' fluid-div-highlight';
							f_address_div.className += " fluid-flash";

							f_arrow_1.className += " fluid-blur-class";
							f_arrow_2.className += " fluid-blur-class";

							f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
							f_cart_box_div.className = f_cart_box_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
						}

						if(FluidMenu.paypal == true) {
							js_fluid_paypal_button_remove();
						}
					}
				}
				else {
					<?php
					if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
					?>
					if(FluidMenu.shipping['s_id'] != null && FluidTemp.f_stock_error != 1 && FluidTemp.cart_empty != 1) {
						<?php // --> Temporary, if in store pickup selected, we do not care about shipping address. ?>
						if(FluidMenu.shipping['s_id'] == "MA==") {
							f_step = 3;
							f_btn.className = f_btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
							f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
							f_btn.className += " btn-success fluid-btn-success-pulse";

							f_btn.disabled = false;

							if(f_btn_lg != null) {
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)disabled(?!\S)/g , '' );
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-grey(?!\S)/g , '' );
								f_btn_lg.className += " btn-success fluid-btn-success-pulse";

								f_btn_lg.disabled = false;
							}

							FluidTemp.prevent_order = false;

							f_shipping_error_div.style.display = "none";
							
							if(FluidMenu.paypal == true) {
								js_fluid_paypal_button_remove();
							}
							
							f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-div-highlight(?!\S)/g , '' );
							f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash(?!\S)/g , '' );
							f_payment_inner_div.className = f_payment_inner_div.className.replace( /(?:^|\s)fluid-flash-holder(?!\S)/g , '' );
							f_payment_box_div.className += " fluid-blur-class";
							f_arrow_2.className += " fluid-blur-class";
						}
						else {
							f_btn.className += " fluid-btn-grey disabled";
							f_btn.className = f_btn.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
							f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
							f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

							f_btn.disabled = true;

							if(f_btn_lg != null) {
								f_btn_lg.className += " fluid-btn-grey disabled";
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
								f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

								f_btn_lg.disabled = true;
							}

							FluidTemp.prevent_order = true;

							if(FluidMenu.paypal == true) {
								js_fluid_paypal_button_remove();
							}
						}
					}
					else {
						f_btn.className += " fluid-btn-grey disabled";
						f_btn.className = f_btn.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
						f_btn.className = f_btn.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

						f_btn.disabled = true;

						if(f_btn_lg != null) {
							f_btn_lg.className += " fluid-btn-grey disabled";
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)btn-success(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse(?!\S)/g , '' );
							f_btn_lg.className = f_btn_lg.className.replace( /(?:^|\s)fluid-btn-success-pulse-holder(?!\S)/g , '' );

							f_btn_lg.disabled = true;
						}

						FluidTemp.prevent_order = true;

						if(FluidMenu.paypal == true) {
							js_fluid_paypal_button_remove();
						}
					}
					<?php
					}
					?>
				}

				if(f_step == 3) {
					FluidGa['step'] = 3;
					FluidGa['step_option'] = FluidGa['step3'];
					js_ga_checkout_steps(FluidGa);
				}
				else if(f_step == 2 && FluidGa['prev_step'] != 2) {
					FluidGa['step'] = 2;
					FluidGa['step_option'] = FluidGa['step2'];
					js_ga_checkout_steps(FluidGa);
				}
				else if(f_step == 1 && FluidGa['prev_step'] != 1) {
					FluidGa['step'] = 1;
					FluidGa['step_option'] = "Checkout Step 1";
					js_ga_checkout_steps(FluidGa);
				}

				js_fluid_shipping_insure_check();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_shipping_insure_check() {
			try {
				if(FluidMenu['shipping']['s_id'] != null) {
					var s_id = Base64.decode(FluidMenu['shipping']['s_id']);

					<?php // --> YES for insurance. ?>
					var f_insure_yes_check = document.getElementById('f-insure-check-false');
					var f_insure_yes_uncheck = document.getElementById('f-insure-remove-false');
					var f_insure_true = document.getElementById('f-insure-true');

					<?php // --> NO for insurance. ?>
					var f_insure_no_check = document.getElementById('f-insure-check-true');
					var f_insure_no_uncheck = document.getElementById('f-insure-remove-true');
					var f_insure_false = document.getElementById('f-insure-false');

					if(s_id == 0) {
						document.getElementById('f-ship-insure-div').style.display = "none";

						f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
						f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
						f_insure_false.className += " fluid-div-select";

						f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
						f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
						f_insure_true.className += " fluid-div-orange-highlight";

						f_insure_yes_check.style.display = "none";
						f_insure_yes_uncheck.style.display = "block";

						f_insure_no_check.style.display = "block";
						f_insure_no_uncheck.style.display = "none";
					}
					else {
						for(var key in FluidTemp['shipping_check']['rates']) {
							if(s_id == key) {
								if(FluidTemp['shipping_check']['rates'][key]['insured'] == true) {
									f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
									f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
									f_insure_true.className += " fluid-div-select";

									f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
									f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
									f_insure_false.className += " fluid-div-orange-highlight";

									f_insure_yes_check.style.display = "block";
									f_insure_yes_uncheck.style.display = "none";

									f_insure_no_check.style.display = "none";
									f_insure_no_uncheck.style.display = "block";
								}
								else {
									f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
									f_insure_false.className = f_insure_false.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
									f_insure_false.className += " fluid-div-select";

									f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-orange-highlight(?!\S)/g , '' );
									f_insure_true.className = f_insure_true.className.replace( /(?:^|\s)fluid-div-select(?!\S)/g , '' );
									f_insure_true.className += " fluid-div-orange-highlight";

									f_insure_yes_check.style.display = "none";
									f_insure_yes_uncheck.style.display = "block";

									f_insure_no_check.style.display = "block";
									f_insure_no_uncheck.style.display = "none";
								}
							}
						}

						document.getElementById('f-ship-insure-div').style.display = "block";
					}
				}

			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_shipping_insure_select(f_insure) {
			try {
				var s_id = Base64.decode(FluidMenu['shipping']['s_id']);
				var s_tmp_id = null;
				var s_tmp_price = null;

				if(f_insure == true) {
					if(FluidTemp['shipping_check']['rates'][s_id] != null || FluidTemp['shipping_check']['rates'][s_id] != 'undefined') {
						if(FluidTemp['shipping_check']['rates'][s_id]['insured'] != true) {
							for(var key in FluidTemp['shipping_check']['rates']) {
								if(FluidTemp['shipping_check']['rates'][key]['insured'] == true) {
									if(s_tmp_price == null)
										s_tmp_price = parseFloat(FluidTemp['shipping_check']['rates'][key]['price']);

									if(s_tmp_price >= parseFloat(FluidTemp['shipping_check']['rates'][key]['price'])) {
										s_tmp_price = parseFloat(FluidTemp['shipping_check']['rates'][key]['price']);
										s_tmp_id = key;
									}
								}
							}

							if(s_tmp_id != null) {
								var s_64_key = Base64.encode(s_tmp_id);

								document.getElementById('fluid-shipping-div').innerHTML = FluidTemp['shipping_check']['rates'][s_tmp_id]['html'];

								var free_id = null;
								if(FluidTemp['shipping_check']['free_id'] != null || FluidTemp['shipping_check']['free_id'] != 'undefined')
									free_id = FluidTemp['shipping_check']['free_id'];

								if(s_tmp_id == free_id)
									js_fluid_checkout_shipping_select(Base64.encode('{"s_id":"' + s_64_key +'","s_price":"FREE"}'));
								else
									js_fluid_checkout_shipping_select(Base64.encode('{"s_id":"' + s_64_key +'","s_price":"$ ' + FluidTemp['shipping_check']['rates'][s_tmp_id]['price'] + '"}'));

								js_fluid_refresh_totals();
							}
						}
					}
				}
				else {
					if(FluidTemp['shipping_check']['rates'][s_id] != null || FluidTemp['shipping_check']['rates'][s_id] != 'undefined') {
						if(FluidTemp['shipping_check']['rates'][s_id]['insured'] != false) {
							for(var key in FluidTemp['shipping_check']['rates']) {
								if(FluidTemp['shipping_check']['rates'][key]['insured'] == false && key != 0) {
									if(s_tmp_price == null)
										s_tmp_price = parseFloat(FluidTemp['shipping_check']['rates'][key]['price']);

									if(s_tmp_price >= parseFloat(FluidTemp['shipping_check']['rates'][key]['price'])) {
										s_tmp_price = parseFloat(FluidTemp['shipping_check']['rates'][key]['price']);
										s_tmp_id = key;
									}
								}
							}

							if(s_tmp_id != null) {
								var s_64_key = Base64.encode(s_tmp_id);

								document.getElementById('fluid-shipping-div').innerHTML = FluidTemp['shipping_check']['rates'][s_tmp_id]['html'];

								var free_id = null;
								if(FluidTemp['shipping_check']['free_id'] != null || FluidTemp['shipping_check']['free_id'] != 'undefined')
									free_id = FluidTemp['shipping_check']['free_id'];

								if(s_tmp_id == free_id)
									js_fluid_checkout_shipping_select(Base64.encode('{"s_id":"' + s_64_key +'","s_price":"FREE"}'));
								else
									js_fluid_checkout_shipping_select(Base64.encode('{"s_id":"' + s_64_key +'","s_price":"$ ' + FluidTemp['shipping_check']['rates'][s_tmp_id]['price'] + '"}'));

								js_fluid_refresh_totals();
							}
						}
					}
				}
			}
			catch(err) {
				js_debug_error(err);
			}
		}
		function js_fluid_confirm_order() {
			try {
				if(FluidTemp.prevent_order == false) {
					var btn_footer = "<?php echo base64_encode("<div id='fluid-modal-back-button' class='pull-left'><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button></div> <div class='pull-right'><button type=\"button\" class=\"btn btn-success\" onClick='$(function () { $(\"#fluid-confirm-modal\").modal(\"toggle\"); }); js_fluid_place_order();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Place Order</button></div>");?>";

					document.getElementById('modal-confirm-msg-div').innerHTML = "Are you sure you want to place this order?";
					document.getElementById('modal-confirm-footer').innerHTML = Base64.decode(btn_footer);

					$('#fluid-confirm-modal').modal('show');
				}
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_keep_shopping() {
			try {
				var data_tmp = {};
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&checkout=true&fluid_function=php_cart_keep_shopping&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_place_order() {
			try {
				var f_data = {};
					f_data.payment = FluidMenu.payment;
					f_data.payment_address = FluidMenu.payment_address;
					f_data.shipping = FluidMenu.shipping;
					f_data.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(f_data));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_fluid_place_order&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_paypal_button_render() {
			var f_stop_ajax_loading = false;

			if(FluidTemp.ajax_loading == false) {
				js_loading_start();
				f_stop_ajax_loading = true;
			}

			js_fluid_paypal_button_mobile_render();
			js_fluid_paypal_button_large_render();

			if(f_stop_ajax_loading == true)
				js_loading_stop();

			<?php
			/*
			// Rendering 2 PayPal buttons at once, causes display issues for the desktop version not rendering properly in responsive mode in the cart box. If we delay the second rendering, it solves the issue. It creates a small render flash though :( I will try to solve this some more later.
				if($detect->isMobile() && !$detect->isTablet()) {
					?>


						setTimeout(function() {js_fluid_paypal_button_large_render();}, 2000);

						if(f_stop_ajax_loading == true)
							js_loading_stop();
					<?php
				}
				else {
					?>
						js_fluid_paypal_button_large_render();
						setTimeout(function() {js_fluid_paypal_button_mobile_render();}, 2000);

						if(f_stop_ajax_loading == true)
							js_loading_stop();
					<?php
				}
			*/
			?>
		}

		function js_fluid_paypal_button_mobile_render() {
			try {
				document.getElementById('paypal-button-nav').innerHTML = '';
				paypal.Button.render({
					<?php
					// --> client and sandbox env and or production is required, else errors when processing.
					?>
					env: '<?php if(FLUID_PAYMENT_SANDBOX == TRUE) { echo PAYPAL_ENVIRONMENT_SANDBOX; } else { echo PAYPAL_ENVIRONMENT; } ?>', <?php // --> 'production' or 'sandbox' ?>
					commit: true,

					style: {
						label: 'checkout',
						size:  'responsive',    // small | medium | large | responsive
						shape: 'rect',     // pill | rect
						color: 'blue',      // gold | blue | silver | black
						tagline: false
					},

					<?php // -->  Set up a getter to create a Payment ID using the payments api, on the server side: ?>
					payment: function() {
						if(FluidTemp.prevent_order == false) {
							 return new paypal.Promise(function(resolve, reject) {
											jQuery.post('<?php echo FLUID_CART;?>?load_func=true&fluid_function=php_paypal_create', { f_checkout_id: FluidTemp.f_checkout_id })
												.done(function(data) {
													var f_paypal = JSON.parse(Base64.decode(data));

													if(f_paypal.f_error != null) {
														<?php
														$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
														?>

														document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
														document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
														js_modal_show('#fluid-error-modal');
													}
													else
														resolve(f_paypal['id']);
												})
												.fail(function(err)  {
													reject(err);
												});
									});
						}
						else {
							<?php
							$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button></div>";
							?>

							document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
							document.getElementById('modal-error-msg-div').innerHTML = "Please fix the indicated flashing order details before proceeding with your PayPal payment.";
							js_modal_show('#fluid-error-modal');
						}
					},

					<?php // --> Pass a function to be called when the customer approves the payment, then call execute payment on the server. ?>
					onAuthorize: function(data) {
						<?php
							// --> At this point, the payment has been authorized, and you will need to call your back-end to complete the payment.
							// --> The server backend will now invoke the PayPal Payment Execute api to finalize the transaction.
						?>
						js_loading_start();


						jQuery.post('<?php echo FLUID_CART;?>?load_func=true&fluid_function=php_paypal_execute', { paymentID: data.paymentID, payerID: data.payerID, f_checkout_id: FluidTemp.f_checkout_id })
							.done(function(data) {
								js_loading_stop();

								var f_paypal = JSON.parse(Base64.decode(data));

								if(f_paypal.f_error != null) {
									<?php
									$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
									?>

									document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
									document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
									js_modal_show('#fluid-error-modal');
								}
								else {
									js_loading_stop();
									js_fluid_ajax_process(f_paypal.f_data);
								}
							})
							.fail(function(err)  {
								js_loading_stop();
								<?php
								$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
								?>

								document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
								document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
								js_modal_show('#fluid-error-modal');
							});
					},

					<?php // --> Pass a function to be called when the customer cancels the payment. ?>
					onCancel: function(data) {
						<?php //console.log('The payment was cancelled!'); ?>
						<?php //console.log('Payment ID = ', data.paymentID); ?>
					}
				}, '#paypal-button-nav');

				document.getElementById('fluid-place-order-button').style.display = "none";
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_paypal_button_large_render() {
			try {
				if(document.getElementById('paypal-button') != null) {
					document.getElementById('paypal-button').innerHTML = '';

					<?php /* console.log('<?php echo FLUID_CART;?>?load_func=true&fluid_function=php_paypal_create&f_checkout_id=' + FluidTemp.f_checkout_id); */?>

					paypal.Button.render({
						<?php
						// --> client and sandbox env and or production is required, else errors when processing.
						?>
						env: '<?php if(FLUID_PAYMENT_SANDBOX == TRUE) { echo PAYPAL_ENVIRONMENT_SANDBOX; } else { echo PAYPAL_ENVIRONMENT; } ?>', <?php // --> 'production' or 'sandbox' ?>
						commit: true,

						style: {
							label: 'checkout',
							size:  '<?php if($detect->isTablet()) { echo "responsive"; } else { echo "responsive"; } ?>',    // small | medium | large | responsive
							shape: 'rect',     // pill | rect
							color: 'blue',      // gold | blue | silver | black
							tagline: false
						},

						<?php // -->  Set up a getter to create a Payment ID using the payments api, on the server side: ?>
						payment: function() {
							if(FluidTemp.prevent_order == false) {
								return new paypal.Promise(function(resolve, reject) {
									jQuery.post('<?php echo FLUID_CART;?>?load_func=true&fluid_function=php_paypal_create', { f_checkout_id: FluidTemp.f_checkout_id })
										.done(function(data) {
											var f_paypal = JSON.parse(Base64.decode(data));

											if(f_paypal.f_error != null) {
												<?php
												$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
												?>

												document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
												document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
												js_modal_show('#fluid-error-modal');
											}
											else
												resolve(f_paypal['id']);
										})
										.fail(function(err)  {
											reject(err);
										});
								});
							}
							else {
								<?php
								$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button></div>";
								?>

								document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
								document.getElementById('modal-error-msg-div').innerHTML = "Please fix the indicated flashing order details before proceeding with your PayPal payment.";
								js_modal_show('#fluid-error-modal');
							}
						},

						<?php // --> Pass a function to be called when the customer approves the payment, then call execute payment on the server. ?>
						onAuthorize: function(data) {
							<?php
								// --> At this point, the payment has been authorized, and you will need to call your back-end to complete the payment.
								// --> The server backend will now invoke the PayPal Payment Execute api to finalize the transaction.
							?>
							js_loading_start();

							jQuery.post('<?php echo FLUID_CART;?>?load_func=true&fluid_function=php_paypal_execute', { paymentID: data.paymentID, payerID: data.payerID, f_checkout_id: FluidTemp.f_checkout_id })
								.done(function(data) {
									js_loading_stop();

									var f_paypal = JSON.parse(Base64.decode(data));

									if(f_paypal.f_error != null) {
										<?php
										$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
										?>

										document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
										document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
										js_modal_show('#fluid-error-modal');
									}
									else {
										js_loading_stop();
										js_fluid_ajax_process(f_paypal.f_data);
									}
								})
								.fail(function(err)  {
									js_loading_stop();
									<?php
									$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});'>Ok</button></div>";
									?>

									document.getElementById('error-modal-footer').innerHTML = Base64.decode('<?php echo base64_encode($redirect_html);?>');
									document.getElementById('modal-error-msg-div').innerHTML = "There was a problem processing your payment. Please try again.";
									js_modal_show('#fluid-error-modal');
								});
						},

						<?php // --> Pass a function to be called when the customer cancels the payment. ?>
						onCancel: function(data) {
							<?php //console.log('The payment was cancelled!'); ?>
							<?php //console.log('Payment ID = ', data.paymentID); ?>
						}

					}, '#paypal-button');
				}

				if(document.getElementById('fluid-button-place-order-div-lg') != null)
					document.getElementById('fluid-button-place-order-div-lg').style.display = "none";
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_paypal_button_remove() {
			try {
				if(document.getElementById('paypal-button') != null)
					document.getElementById('paypal-button').innerHTML = '';

				if(document.getElementById('fluid-button-place-order-div-lg') != null)
				document.getElementById('fluid-button-place-order-div-lg').style.display = "block";

				if(document.getElementById('paypal-button-nav') != null)
					document.getElementById('paypal-button-nav').innerHTML = '';

				if(document.getElementById('fluid-place-order-button') != null)
					document.getElementById('fluid-place-order-button').style.display = "block";
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_checkout_create_send(f_paypal) {
			try {
				if(f_paypal == true) {
						js_fluid_payment_empty();
						FluidMenu.payment['c_number'] = 'paypal';
						FluidMenu.payment_address['a_billing_same'] = true;
					<?php
					/*
						--> Unsafe to send credit card information to be saved in a $_SESSION variable. DO NOT DO THIS!! har har harrrrrr.
					*/
					?>
					var FluidTmpData = {};
						FluidTmpData.payment = FluidCard;
						FluidTmpData.payment_address = FluidData;
						FluidTmpData.f_checkout_id = FluidTemp.f_checkout_id;
						FluidTmpData.f_paypal = true;
				}
				else {
					var FluidCard = {};
						FluidCard.c_name = document.getElementById('fluid-cc-name').value;
						FluidCard.c_number = document.getElementById('fluid-cc-number').value;
						FluidCard.c_exp_m = document.getElementById('fluid-cc-exp-mm').value;
						FluidCard.c_exp_y = document.getElementById('fluid-cc-exp-yy').value;
						FluidCard.c_cv = document.getElementById('fluid-cc-cv').value;

						FluidMenu.payment = FluidCard;

					var FluidData = {};
						if(document.getElementById('fluid-credit-billing-same').checked)
							FluidData.a_billing_same = true;
						else
							FluidData.a_billing_same = false;

						FluidData.a_name = document.getElementById('fluid-address-name').value;
						FluidData.a_number = document.getElementById('fluid-address-apt-number').value;
						FluidData.a_street = document.getElementById('fluid-address-street').value;
						FluidData.a_city = document.getElementById('fluid-address-city').value;
						FluidData.a_province = document.getElementById('fluid-address-province').value;
						FluidData.a_country = document.getElementById('fluid-address-country').value;
						FluidData.a_postalcode = document.getElementById('fluid-address-postal-code').value;
						FluidData.a_phonenumber = document.getElementById('fluid-address-phone-number').value;

						FluidMenu.payment_address = FluidData;
					<?php
					/*
						--> Unsafe to send credit card information to be saved in a $_SESSION variable. DO NOT DO THIS!! har har harrrrrr.
					*/
					?>
					var FluidTmpData = {};
						FluidTmpData.payment = FluidCard;
						FluidTmpData.payment_address = FluidData;
						FluidTmpData.f_checkout_id = FluidTemp.f_checkout_id;
				}

				var data = Base64.encode(JSON.stringify(FluidTmpData));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_fluid_payment_save&data=" + data}));

				js_fluid_ajax(data_obj);

				$(function () {
					$(FluidTemp.tmp_modal).modal('toggle');
				});
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_checkout_create()	{
			$('#fluid_form_address').validator().on('submit', function (e) {
			  if(e.keyCode == 13)
					e.isDefaultPrevented();

			  if (e.isDefaultPrevented()) {
				// handle the invalid form...
			  } else {
				// everything looks good!
				e.isDefaultPrevented();
				e.preventDefault(e);

				try {
					js_fluid_payment_checkout_create_send(false);
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_payment_empty() {
			try {
				for(var key in FluidMenu.payment)
					delete FluidMenu.payment[key];

				for(var key in FluidMenu.payment_address)
					delete FluidMenu.payment_address[key];
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_checkout_input() {
			try {
				if(FluidMenu.payment['c_number'] != null)
					if(FluidMenu.payment['c_number'] == 'paypal')
						js_fluid_payment_empty();

				var FluidData = {};
					FluidData.payment = FluidMenu.payment;
					FluidData.payment_address = FluidMenu.payment_address;
					FluidData.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(FluidData));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_html_checkout_payment_input&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_checkout_select() {
			try {
				var data_tmp = {};
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_html_checkout_payment_select&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_temp_address_set(data64) {
			try {
				var data = JSON.parse(Base64.decode(data64));

				FluidTemp.a_name = data['a_name'];
				FluidTemp.a_number = data['a_number'];
				FluidTemp.a_street = data['a_street'];
				FluidTemp.a_city = decodeURIComponent(escape(data['a_city']));
				FluidTemp.a_province = data['a_province'];
				FluidTemp.a_country = data['a_country'];
				FluidTemp.a_postalcode = data['a_postalcode'];
				FluidTemp.a_phonenumber = data['a_phonenumber'];
			}
			catch(err) {
				FluidTemp.shipping_check['valid'] == false;
				FluidTemp.shipping_check['error'] == true;

				js_debug_error("Error: There is a problem with your shipping address. Please verify and correct any errors in the address.");
			}
		}

		function js_fluid_payment_validator_init() {
			try {
				$('#fluid_form_address').validator();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_payment_form_reset() {
			$('#fluid_form_address').validator('validate');
			$('#fluid_form_address')[0].reset();
		}

		function js_fluid_payment_billing_check() {
			if(document.getElementById('fluid-credit-billing-same').checked) {
				document.getElementById('fluid-credit-address-hidden').style.display = "none";

				document.getElementById('f-province-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_WORLD); ?>');
				document.getElementById('f-country-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_COUNTRY_WORLD); ?>');

				document.getElementById('fluid-address-name').value = FluidTemp.a_name;
				document.getElementById('fluid-address-apt-number').value = FluidTemp.a_number;
				document.getElementById('fluid-address-street').value = FluidTemp.a_street;
				document.getElementById('fluid-address-city').value = FluidTemp.a_city;
				document.getElementById('fluid-address-province').value = FluidTemp.a_province;
				document.getElementById('fluid-address-country').value = FluidTemp.a_country;
				document.getElementById('fluid-address-postal-code').value = FluidTemp.a_postalcode;
				document.getElementById('fluid-address-phone-number').value = FluidTemp.a_phonenumber;

				$('#fluid_form_address').validator('update');
				$('#fluid_form_address').validator('validate');

				js_fluid_update_selectpicker('refresh');
			}
			else {
				document.getElementById('fluid-address-name').value = "";
				document.getElementById('fluid-address-apt-number').value = "";
				document.getElementById('fluid-address-street').value = "";
				document.getElementById('fluid-address-city').value = "";
				document.getElementById('fluid-address-province').value = "";
				document.getElementById('fluid-address-country').value = "";
				document.getElementById('fluid-address-postal-code').value = "";
				document.getElementById('fluid-address-phone-number').value = "";

				document.getElementById('f-province-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_PROVINCE); ?>');
				document.getElementById('f-country-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_COUNTRY); ?>');

				document.getElementById('fluid-credit-address-hidden').style.display = "block";

				$('#fluid_form_address').validator('update');
				$('#fluid_form_address').validator('validate');

				js_fluid_update_selectpicker('refresh');
			}
		}

		function js_fluid_address_checkout_creator() {
			try {
				var data_tmp = {};
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_html_checkout_address_creator&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_checkout_address() {
			try {
				var data_tmp = FluidMenu.shipping;
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_address_book_checkout&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_checkout_address_select(data) {
			try {
				FluidMenu.shipping.a_id = data['a_id'];

				var data_tmp = FluidMenu.shipping;
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;
					data_tmp.f_add_to_cart = data['a_add_to_cart'];
					data_tmp.f_paypal = FluidMenu.paypal;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_fluid_checkout_load&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_checkout_shipping_select(data64) {
			try {
				var data = JSON.parse(Base64.decode(data64));

				if(data != null) {
					FluidMenu.shipping.s_id = data['s_id'];

					var shipping_methods = document.getElementsByName('fluid-shipping-method-selected');

					for(var x=0; x < shipping_methods.length; x++) {
						if(shipping_methods[x].getAttribute('data-id') == data['s_id'])
							shipping_methods[x].style.display = "block";
						else
							shipping_methods[x].style.display = "none";
					}

					var shipping_methods_unselect = document.getElementsByName('fluid-shipping-method');

					for(var x=0; x < shipping_methods_unselect.length; x++) {
						if(shipping_methods_unselect[x].getAttribute('data-id') == data['s_id'])
							shipping_methods_unselect[x].style.display = "none";
						else
							shipping_methods_unselect[x].style.display = "block";
					}
				}
				else
					js_debug_error("Error: There is a problem with your shipping address. Please verify and correct if needed.");
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_refresh_totals() {
			try {
				var f_data = {};
					f_data.payment = FluidMenu.payment;
					f_data.payment_address = FluidMenu.payment_address;
					f_data.shipping = FluidMenu.shipping;
					f_data.ship_split = FluidMenu.ship_split;
					f_data.f_checkout_id = FluidTemp.f_checkout_id;
					f_data.f_paypal = FluidMenu.paypal;

				var data = Base64.encode(JSON.stringify(f_data));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_fluid_refresh_totals&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}
	</script>

	<?php // --> Header ends. ?>

	<?php // --> Body stuff. ?>
	<div class="fluid-cart-content-background">
		<div class="fluid-cart-content">

			<div class="fluid-container-shipping" style="border: 0px solid blue; margin-top: 10px;">
				<div id='fluid-card-left-address-div'>
					<?php
					if(FLUID_SHIP_NON_BILLING == FALSE)
						$f_ship_message_header = "Shipping & Billing Address";
					else
						$f_ship_message_header = "Shipping Address";

				?>
				<div class='fluid-card-left-div' style="font-size: 18px; font-weight: 400;"><i class="fa fa-home" aria-hidden="true"></i> <?php echo $f_ship_message_header; ?></div>
					<?php
						$data_id = new stdClass();
						$data_id->f_checkout_id = $f_checkout_id;
						$data = json_decode(php_fluid_checkout_load($data_id, TRUE));

						//if(isset($data->a_id))
							//$f_address_flash = NULL;
						//else
							$f_address_flash = " fluid-flash";

						//if(isset($_SESSION['u_id']))
							$f_onclick = "onClick='if(FluidTemp.cart_empty == 0 && FluidTemp.cart_edit == 0) { js_fluid_checkout_address(); }'";
						//else
							//$f_onclick = "onClick='js_fluid_address_checkout_creator();'";

						echo "<div id='fluid-checkout-address-shipping-div' class='fluid-box-shadow fluid-box-address-shipping fluid-div-highlight" . $f_address_flash . "' " . $f_onclick . " onmouseover='JavaScript:this.style.cursor=\"pointer\";'>";

						echo utf8_encode(base64_decode($data->html));
					?>
				</div>

				<script>
					FluidTemp.a_name = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_name'];?>";
					FluidTemp.a_number = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_number'];?>";
					FluidTemp.a_street = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_street'];?>";
					FluidTemp.a_city = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_city'];?>";
					FluidTemp.a_province = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_province'];?>";
					FluidTemp.a_country = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_country'];?>";
					FluidTemp.a_postalcode = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_postalcode'];?>";
					FluidTemp.a_phonenumber = "<?php echo $_SESSION['f_checkout'][$f_checkout_id]['f_address']['a_phonenumber'];?>";
				</script>
				</div>

				<?php
				$f_display_modifier = " fluid-blur-class";
				$f_payment_flash = NULL;

				if(isset($data->a_id)) {
					// Load our default shipping address if available.
					echo "<script>";
							echo "FluidMenu.shipping.a_id = '" . $data->a_id . "';";
							echo "js_fluid_checkout_address_select({a_id : FluidMenu.shipping.a_id, a_add_to_cart : null});";
					echo "</script>";

					//$f_display_modifier = NULL;
					//$f_payment_flash = " fluid-flash";
				}
				?>

				<div id='f-arrow-hide-1' class='f-checkout-arrow <?php echo $f_display_modifier; ?>'>
					<div class="f-arrow-hide"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></div>
				</div>

				<div id='fluid-shipping-box-div' class='<?php echo $f_display_modifier; ?>'>
					<div style="font-size: 18px; font-weight: 400;"><i class="fa fa-truck" aria-hidden="true"></i> Shipping Method</div>
					<div id="fluid-shipping-div" class="fluid-box-shadow fluid-box-address-shipping" onmouseover="JavaScript:this.style.cursor='pointer';" onClick="if(FluidMenu.shipping['a_id'] != null && FluidTemp.shipping_check['error'] == false && FluidTemp.cart_empty == 0 && FluidTemp.cart_edit == 0) { js_modal_show('#fluid-shipping-modal'); }">
						<?php
						if($detect->isMobile()) {
							$f_glyph = "glyphicon glyphicon-hand-up";
							$f_touch = "Touch";
						}
						else {
							$f_glyph = "glyphicon glyphicon-plus";
							$f_touch = "Click";
						}

						echo "<div style='text-align: center;'><span class='glyphicon glyphicon-asterisk'></span><div>Add a shipping address first</div></div>";
						?>
					</div>

					<div id='f-ship-insure-div' style='display: none;'>
						<div id='f-arrow-hide-ship-insure' class='f-checkout-arrow'>
							<div class="f-arrow-hide"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></div>
						</div>

						<div style="font-size: 18px; font-weight: 400;"><i class="fa fa-lock" aria-hidden="true"></i> Insure Shipment?</div>

						<div id="fluid-shipping-insure-div" class="fluid-box-shadow fluid-box-address-shipping">
							<div style='display: table; width: 100%;'>
								<div style='display: table-cell; text-align: center;'>
									<div class='f-ship-split-card'>
										<div id='f-insure-true' class='fluid-account-box-special-cart well fluid-box-shadow-small-well fluid-div-select' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_shipping_insure_select(true)'>
											<div style='display: inline-block;'><i id='f-insure-check-false' class='fa fa-check' aria-hidden='true'></i><i id='f-insure-remove-false' class='fa fa-hand-pointer-o' aria-hidden='true' style='display: none;'></i></div><div>Yes</div>
										</div>
									</div>
								</div>

								<div style='display: table-cell; text-align: center;'>
									<div class='f-ship-split-card'>
										<div id='f-insure-false' class='fluid-account-box-special-cart well fluid-box-shadow-small-well fluid-div-orange-highlight' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_shipping_insure_select(false)'>
											<div style='display: inline-block;'><i id='f-insure-check-true' class='fa fa-check' aria-hidden='true' style='display: none;'></i><i id='f-insure-remove-true' class='fa fa-hand-pointer-o' aria-hidden='true'></i></div><div>No</div>
										</div>
									</div>
								</div>

							</div>
						</div>

					</div>

					<?php
						if(php_fluid_check_split_order($f_checkout_id) == TRUE) {
							$f_split_hide = "style='display: block;'";
						}
						else {
							$f_split_hide = "style='display: none;'";
						}
					?>

					<div id='f-split-ship-card' <?php echo $f_split_hide;?>>


					<?php
					// --> Check if we should
					//if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE || FLUID_PREORDER == TRUE) {
					// onmouseover="JavaScript:this.style.cursor='pointer';" onClick="FluidMenu.shipping['f_ship_split'] != null"
					?>
						<div id='f-arrow-hide-ship' class='f-checkout-arrow'>
							<div class="f-arrow-hide"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></div>
						</div>

						<div style="font-size: 18px; font-weight: 400;"><i class="fa fa-chain-broken" aria-hidden="true"></i> Split Shipment?</div>
						<div id="fluid-shipping-split-div" class="fluid-box-shadow fluid-box-address-shipping">

							<div style='display: table; width: 100%; font-weight: 400;'>
								<div style='display: table-cell; color: rgba(0, 0, 0, 0.7);'><i class='fa fa-exclamation-circle' aria-hidden='true'></i> Some items are not in stock </div>
							</div>

							<div style='display: table; width: 100%;'>
								<div style='display: table-cell; text-align: center;'>
									<div class='f-ship-split-card'>
										<div id='f-split-true' class='fluid-account-box-special-cart well fluid-box-shadow-small-well fluid-div-select' onmouseover="JavaScript:this.style.cursor='pointer';" onClick="if(FluidMenu.shipping['a_id'] != null && FluidTemp.shipping_check['error'] == false && FluidTemp.cart_empty == 0 && FluidTemp.cart_edit == 0) { js_fluid_ship_split(false); js_fluid_refresh_totals(); }">
											<div style='display: inline-block;'><i id='f-split-check-false' class='fa fa-check' aria-hidden='true'></i><i id='f-split-remove-false' class='fa fa-hand-pointer-o' aria-hidden='true' style='display: none;'></i></div><div>Ship all items together.</div>
										</div>
									</div>
								</div>

								<div style='display: table-cell; text-align: center;'>
									<div class='f-ship-split-card'>
										<div id='f-split-false' class='fluid-account-box-special-cart well fluid-box-shadow-small-well fluid-div-orange-highlight' onmouseover="JavaScript:this.style.cursor='pointer';" onClick="if(FluidMenu.shipping['a_id'] != null && FluidTemp.shipping_check['error'] == false && FluidTemp.cart_empty == 0 && FluidTemp.cart_edit == 0) { js_fluid_ship_split(true); js_fluid_refresh_totals(); }">
											<div style='display: inline-block;'><i id='f-split-check-true' class='fa fa-check' aria-hidden='true' style='display: none;'></i><i id='f-split-remove-true' class='fa fa-hand-pointer-o' aria-hidden='true'></i></div><div>Ship in stock items first.</div>
										</div>
									</div>
								</div>

							</div>

						</div>
					<?php
					//}
					?>
					</div>

				</div>

				<div id='f-arrow-hide-2' class='f-checkout-arrow <?php echo $f_display_modifier; ?>'>
					<div class="f-arrow-hide"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></div>
				</div>

				<div id='fluid-payment-box-div' class='f-payment-box-div <?php echo $f_display_modifier; ?>'>
					<div style="font-size: 18px; font-weight: 400;"><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Payment
					<div class='fluid-need-help-div-inline' style='padding-left: 5px;'>
						<img style='padding-right: 5px; margin-bottom: 2px;' src='/files/visa.png'></img> <img style='padding-right: 5px; margin-bottom: 2px;' src='/files/mastercard.png'></img> <img style='margin-bottom: 2px;' src='/files/paypal.png'></img>
					</div>
					</div>
					<?php //<div class="fluid-box-shadow fluid-box-address-shipping fluid-div-highlight">?>
						<?php
							$f_onclick = "onClick='if(FluidMenu.shipping[\"a_id\"] != null && FluidTemp.shipping_check[\"error\"] == false && FluidTemp.cart_empty == 0 && FluidTemp.cart_edit == 0) { ";
								if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE) {
									$f_onclick .= "if(FluidMenu.shipping[\"s_id\"] != \"MA==\") { js_fluid_payment_checkout_select(); } }'";
								}
								else {
									$f_onclick .= "js_fluid_payment_checkout_select(); }'";
								}

							echo "<div id='fluid-payment-inner-box-div' class='fluid-box-shadow fluid-box-address-shipping" . $f_payment_flash . "' " . $f_onclick . " onmouseover='JavaScript:this.style.cursor=\"pointer\";'>";

								echo "<div style='text-align: center;'><span class='" . $f_glyph . "'></span><div>" . $f_touch . " to select a payment method</div></div>";

							echo "</div>";
						/*
						<div style="display: table; width: 100%; font-weight: 400;">
							<div style="display: table-cell;"><div class="fa fa-check" aria-hidden="true"></div> Visa <div class="fa fa-cc-visa"></div></div>
							<div style="display: table-cell; text-align: right;" class="glyphicon glyphicon-edit" aria-hidden="true"></div>
						</div>

						<div style="display: table; width: 100%;">
							<div style="display: table-cell; padding-left: 5px; display: inline-block; color: rgba(0, 0, 0, 0.3);">### 436</div>
							<div style="display: table-cell;">Exp: 01/19</div>
						</div>
						*/
						?>
					<?php //</div> ?>
				</div>

			<?php
			// Need anything box used to go here
			?>

			</div> <?php // --> fluid-container-shipping ?>


			<div id="fluid-cart-box" class="fluid-cart-box">
				<div class='fluid-cart-box-inner'>
					<div><i class="fa fa-shopping-cart" aria-hidden="true"></i> Cart Total</div>
					<div id="fluid-cart-dropdown" class="fluid-cart-dropdown fluid-box-shadow">
					<?php
						global $fluid_cart_html;
						echo $fluid_cart_html;
					?>
					</div>

						<div>

					<div class='fluid-need-help-checkout'>
						<div style='display: table; width: 100%;'>
							<div style='display: table-row;'>
								<div style='display: table-cell;'><i class="fa fa-life-ring" aria-hidden="true"></i> Need help? </div>
							</div>

							<div style='display: table-row;'>
								<div style='display: table-cell; font-size: 14px; padding-top: 5px;'><a class='btn btn-info' style='max-width: 168px; width: 168px;' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("modal-fluid-checkout-header-div").innerHTML = "<div style=\"font-weight: 500;\">Terms & Conditions</div>"; document.getElementById("modal-fluid-checkout-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_TERMS_CONDITIONS); ?>"); js_modal_show("#fluid-checkout-main-modal");'><i class="fa fa-info-circle" aria-hidden="true" style='padding-right: 5px;'></i> Terms & Conditions</a></div>

								<div style='display: table-cell; text-align: right; padding-top: 5px; font-size: 14px;'><a class='btn btn-info' style='max-width: 168px; width: 168px;' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("modal-fluid-checkout-header-div").innerHTML = "<div style=\"font-weight: 500;\">Privacy & Security</div>"; document.getElementById("modal-fluid-checkout-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_PRIVACY_CONDITIONS); ?>"); js_modal_show("#fluid-checkout-main-modal");'><i class="fa fa-eye" aria-hidden="true" style='padding-right: 5px;'></i>Privacy & Security</a></div>
							</div>

							<div style='display: table-row;'>
								<div style='display: table-cell; font-size: 14px; padding-top: 10px;'><a class='btn btn-info' style='max-width: 168px; width: 168px;' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("modal-fluid-checkout-header-div").innerHTML = "<div style=\"font-weight: 500;\">Shipping Policy</div>"; document.getElementById("modal-fluid-checkout-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_SHIPPING_POLICY); ?>"); js_modal_show("#fluid-checkout-main-modal");'><i class="fa fa-truck" aria-hidden="true" style='padding-right: 5px;'></i> Shipping Policy</a></div>

								<div style='display: table-cell; text-align: right; padding-top: 5px; font-size: 14px;'><a class='btn btn-info' style='max-width: 168px; width: 168px;' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("modal-fluid-checkout-header-div").innerHTML = "<div style=\"font-weight: 500;\">Returns & Refunds</div>"; document.getElementById("modal-fluid-checkout-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_RETURN_POLICY); ?>"); js_modal_show("#fluid-checkout-main-modal");'><i class="fa fa-archive" aria-hidden="true" style='padding-right: 5px;'></i>Returns & Refunds</a></div>
							</div>



						</div>
					</div>
				</div>

			<div id='fluid-need-help-div' class='fluid-need-help-div' style='max-width: 360px; font-size: 10px; padding-top: 10px;'>
				<div>
				<i class="fa fa-question-circle-o" aria-hidden="true"></i> Need assistance? Call us at 1-604-685–5331, or send us a text message at 1-778-771-1002 and or send us an <a href='mailto:sales@leoscamera.com'>email</a>
				</div>
				<div style='padding-top: 5px;'>
					All prices listed are in Canadian dollars. We make every effort to ensure our prices are accurate. We do, however, reserve the right to advise you of any errors prior to processing your order. We apologize for any inconvenience this may cause.
				</div>
			</div>

			</div>
		</div>

		<div class="navbar-fixed-bottom fluid-cart-order-button-mobile" style="display: table-cell; text-align: center; background-color: white;">
			<button type="button" id="fluid-place-order-button" onClick="if(FluidTemp.cart_edit == 0) { js_fluid_confirm_order(); }" class="btn btn-lg fluid-btn-grey disabled" disabled style="width: 100%; margin-bottom: 0px; border-radius: 0px !important" aria-haspopup="true" aria-expanded="false"><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Confirm Order</button>
			<div id="paypal-button-nav" style='background-color: #009cde;'>
			</div>
		</div>

	</div>
	<?php // --> Body stuff ends. ?>


	<?php // Footer stuff. ?>

		<div id='f-sell-through-box' class='f-sell-through-box'>
					<div style='width: 80%; margin: auto; font-size: 18px; font-weight: 400; max-width: 1300px;'><i class="fa fa-gift" aria-hidden="true"></i> Need anything else?</div>
					<?php
						$fluid->php_db_begin();

						// --> Log the checkout session.
						$f_write = TRUE;

						// --> Search spam time. Set to 3 second intervals, more than 3 and it doesn't record to prevent database spamming.
						if(isset($_SESSION['fluid_search']['fluid_last_checkout_time'])) {
							if(time() - $_SESSION['fluid_search']['fluid_last_checkout_time'] < 5) {
								$_SESSION['fluid_search']['fluid_last_checkout_time'] = time();
								$f_write = FALSE;;
							}
							else
								$_SESSION['fluid_search']['fluid_last_checkout_time'] = time();
						}
						else
							$_SESSION['fluid_search']['fluid_last_checkout_time'] = time();

						if($f_write == TRUE)
							$fluid->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('checkout visit', '" . $fluid->php_escape_string(serialize(print_r($_SESSION, TRUE))) . "')");

						// --> Sell through products
						// --> 1. This can be modified at a later date to show products more related to whats in the user's cart. Perhaps basd on mfg_id or parent cat_id etc?
						$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_price IS NOT NULL AND p.p_enable > 0 AND c.c_enable = 1 AND p.p_trending > 0 AND p.p_stock > 0 AND p.p_weight > 0 AND p.p_length > 0 AND p.p_width > 0 AND p.p_height > 0 HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,100");

						$fluid->php_db_commit();

							$f_items_html = NULL;
								if(isset($fluid->db_array)) {
									shuffle($fluid->db_array);
									$i_count = 0;
									foreach($fluid->db_array as $data) {
										// --> If a item is discontinued, we do not show it unless it has stock. All other items will be shown.
										if($data['p_enable'] < 2 || ($data['p_enable'] == 2 && $data['p_stock'] > 0)) {
											if($fluid->php_item_available($data['p_newarrivalenddate']) == TRUE) {
												if($i_count > 20)
													break;

												$f_items_html .= "<div class=\"swiper-slide swiper-slide-products\">";

													$f_items_html .= "<div class=\"trending-product\" style='min-height: 280px; max-height: 280px;'>";
														$f_items_html .= "<div class=\"trending-product-thumbnail f-fix\">";
															$f_items_html .= "<div><div style='vertical-align: middle; text-align: center; align-items: center;'>";
															//$f_items_html .= "<div class=\"badge-new\"></div>";
															//$f_items_html .= "<div class=\"badge-sale\"></div>";
															//$f_items_html .= "<div style='min-height: 32px;'></div>"; // Temp place holder when no new or sale badge to keep things aligned properly.
															$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
															$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

															$p_images = $fluid->php_process_images($data['p_images']);
															$width_height_l = $fluid->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
															$f_items_html .= "<img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img>";

															$f_items_html .= "<div class=\"caption\">";
																$f_items_html .= "<h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data['m_name']) . "</h6>";
																$f_items_html .= "<h6 class=\"trending-product-heading-name\">";

																if(empty($data['p_name']))
																	$ft_name = $data['p_id'];
																else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
																	$ft_name = $data['p_name'];
																else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
																	$ft_name = $data['p_name'];
																else
																	$ft_name = $data['p_mfg_number'] . " " . $data['p_name'];

																if(strlen($fluid->php_clean_string($data['p_name'])) > 50)
																	$f_items_html .= substr($ft_name, 0, 50) . '...';
																else
																	$f_items_html .= $ft_name;

																$f_items_html .= "</h6>";
																//$f_items_html .= "<div class=\"trending-product-heading-price-container\">";

																//$f_items_html .= "</div>";
															$f_items_html .= "</div>";

															$f_items_html .= "</div></div>"; // empty div delete.

														$f_items_html .= "</div>";

													$f_items_html .= "<div class='f-bottom-fixed-sell'>";
														$f_items_html .= "<div>";
															if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) ) ) {
																$f_items_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
																$f_items_html .= "<span style='padding-left: 5px; padding-right: 5px;'>|</span>";
																$f_items_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
															}
															else
																$f_items_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
														$f_items_html .= "</div>";

														$f_items_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
														if(FLUID_STORE_OPEN == FALSE)
															$f_disabled_style = "disabled";
														else
															$f_disabled_style = NULL;

														$f_items_html .= "<div><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success' " . $f_disabled_style . " onClick='FluidTemp.f_refresh_shipping=true; js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div>";
													$f_items_html .= "</div>";

													$f_items_html .= "</div>";

												$f_items_html .= "</div>";

												$i_count++;
											}
										}
									}

								}
					?>

					<style>
					.trending-product-thumbnail {
						border: 0px solid black !important;
					}

					.swiper-container-products {
						width: 100%;
						margin: auto;
					}

					.swiper-slide-products {
						text-align: center;
						display: -webkit-box;
						display: -ms-flexbox;
						display: -webkit-flex;
						display: flex;
						-webkit-box-pack: center;
						-ms-flex-pack: center;
						-webkit-justify-content: center;
						justify-content: center;
						-webkit-box-align: center;
						-ms-flex-align: center;
						-webkit-align-items: center;
						align-items: center;
					}

					.swiper-pagination-bullet {
						margin: 0px 5px 0px 5px;
					}

					.f-swiper-pagination {
						position: static;
						width: 100%;
					}

					@media (min-width: 768px) {
						.f-swiper-pagination {
							width: 100%;
						}
					}
					</style>

					<div class='fluid-box-shadow f-sell-through-extra' style=''>
						<div class="swiper-container swiper-container-products">
							<div class="swiper-wrapper">
								<?php echo $f_items_html; ?>
							</div>

							<!-- Add Arrows -->
							<div class="swiper-button-next"></div>
							<div class="swiper-button-prev"></div>
						</div>
					</div>
					<div class="swiper-pagination swiper-pagination-products f-swiper-pagination"></div>

					<script>
						var swiper = new Swiper('.swiper-container-products', {
							pagination: '.swiper-pagination-products',
							nextButton: '.swiper-button-next',
							prevButton: '.swiper-button-prev',
							paginationClickable: true,
							slidesPerView: 5,
							spaceBetween: 10,
							loop: true,
							breakpoints: {
								1919: {
									slidesPerView: 4,
									spaceBetween: 10
								},
								1023: {
									slidesPerView: 3,
									spaceBetween: 10
								},
								767: {
									slidesPerView: 3,
									spaceBetween: 10
								},
								379: {
									slidesPerView: 1,
									spaceBetween: 10
								}
							}
						});
					</script>

				</div>

	<style>
	.f-footer-cart {
		display: none;
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
		overflow: hidden;
		width: 100%;
		padding-top: 25px;
		padding-bottom: 25px;
		background-color: #ababab;
		font-size: 12px;
		text-align: center;
	}

	@media (min-width: 768px) {
		.f-footer-cart {
			display: block;
			padding-top: 10px;
			padding-bottom: 10px;
			font-size: 10px;
		}
	}

	@media (min-width: 992px) {
		.f-footer-cart {
			display: block;
			padding-top: 15px;
			padding-bottom: 15px;
			font-size: 12px;
		}
	}

	</style>

	<footer class="footer f-footer-cart">
		<div id="footer_fluid">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div>© <?php echo date("Y"); ?> Leo's Camera Supply Ltd. 1055 Granville Street, Vancouver BC, CANADA V6Z1L4</div>
				</div>
			</div> <!-- row end -->
		</div> <!-- container end -->
	</footer>

	</div> <?php //Header fluid-blur-wrap ?>

	<script>
		js_ga_checkout_steps(FluidGa);
	</script>

	<!-- Nav scroll -->
	<script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/scrolling-nav.js');?>"></script> <!-- must be loaded after the html -->

	<?php
	echo "</div>";
	?>

	<div id='fluid-print-div'></div>
	</body>
	</html>
	<?php // Footer stuff ends.
}
?>
