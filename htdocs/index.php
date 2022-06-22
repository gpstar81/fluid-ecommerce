<?php
// index.php
// Michael Rajotte - 2016 Jun
// Index page.

/*
require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;
*/

php_main_index();

function php_main_index() {
	require_once("header.php");

	$detect = new Mobile_Detect;

	// Create a new fluid class module.
	$fluid = new Fluid ();

	// --> A item list for tracking and passing to Google ga anayltics.
	$f_item_list = NULL;
	?>

	<!DOCTYPE html>

	<html lang="en">
	<head>
		<?php
		/*
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
		*/
		?>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags --> ?>
		<meta name="description" content="Shop Digital Cameras, 35MM Camera Equipment, Photography, Photo Printers, Authorized Dealer Canon, Sony, Nikon, FujiFilm, Olympus, Panasonic, Kodak">
		<meta name="keywords" content="Digital Cameras,Camcorders,Camera Accessories">

		<title>Leos Camera Supply Digital Cameras, Photography, Camcorders</title>

	<?php

	php_load_pre_header();
	?>
	<link rel="stylesheet" type="text/css" href="css/fluid-index.css">
	</head>

	<body>

	<?php
	php_load_header();

	//$fluid->php_db_begin();
	?>

	<div id="fb-root"></div>

	<div class="container-fluid">

	<?php/*
	if(FREE_SHIPPING_FORMULA_ENABLED == TRUE) {
		$f_shipping_value = "ON ORDERS";
		if(FREE_SHIPPING_CART_TOTAL_STEP_1 > 0) {
			$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_1 . " *";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_2 > 0) {
			$f_shipping_value .= " ORDERS OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_2 . " *";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_3 > 0) {
			$f_shipping_value .= " ORDERS OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_3 . " *";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_4 > 0) {
			$f_shipping_value .= " ORDERS OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_4 . " *";
		}
		else if(FREE_SHIPPING_CART_TOTAL_STEP_5 > 0) {
			$f_shipping_value .= " ORDERS OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_5 . " *";
		}
		
	?>
		<div class="f-free-shipping-index">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				  <div class="header-free-shipping-container-mobile" onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Shipping Policy</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_SHIPPING_POLICY); ?>"); js_modal_show("#fluid-main-modal");'>
					<img src='<?php echo $_SESSION['fluid_uri'];?>files/header-canadian-flag.png' style='margin-bottom: 1px;'></img><h5 class="header-free-shipping-headline">FREE SHIPPING</h5><?php echo $f_shipping_value;?> <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
				  </div>
			</div>
		</div>
	<?php
	}
	*/
	?>
	
	<?php
	/*
	if(FLUID_BANNERS_ENABLED == TRUE) {
	?>
		<div class="row f-banners-div">
			<div class="col-sm-12" style='padding: 0px;'>

		<?php
			$fluid->php_db_query("SELECT * FROM ". TABLE_BANNERS . " WHERE b_enable = 1 ORDER BY b_sortorder ASC");
			$f_banners = NULL;
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $data) {
					$f_banners[] = $data;
				}
			}

			if(isset($f_banners)) {
		?>

			<div class="swiper-container swiper-container-banners">
				<div class="swiper-wrapper">
					<?php
					$f_banners_count = 0;
						foreach($f_banners as $banners) {
							echo "<div class=\"swiper-slide swiper-slide-banners\" data-swiper-autoplay=\"" . $banners['b_timer'] . "\">";
								echo base64_decode($banners['b_html']);
							echo "</div>";

							$f_banners_count++;
						}
					?>

				</div>
				<!-- Add Arrows -->
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
			</div>

			<div class="swiper-pagination swiper-pagination-banners" style='margin-left: 5px; margin-right: 5px; position: static;'></div>

			<style>
			.swiper-container-banners {
				width: 100%;
				margin: auto;
			}

			@media (min-width: 768px) {
				.swiper-container-banners {
					max-height: 400px;
					min-height: 400px;
					height: 400px;
					border-bottom: 1px solid #161616;
				}
			}

			@media (min-width: 1440px) {
				.swiper-container-banners {
					max-height: 500px;
					min-height: 500px;
					height: 500px;
					border-bottom: 1px solid #161616;
				}
			}

			.swiper-pagination-bullet {
				margin-left: 5px;
				margin-right: 5px;
			}

			.swiper-slide-banners {
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

			<script>
				var swiper_banners = new Swiper('.swiper-container-banners', {
					pagination: '.swiper-pagination-banners',
					nextButton: '.swiper-button-next',
					prevButton: '.swiper-button-prev',
					paginationClickable: true,
					slidesPerView: 1,
					spaceBetween: 10,
					<?php
					if($f_banners_count > 1)
						echo "loop: true,";
					else
						echo "loop: false,";
					?>
					autoplay: 2000
				});
			</script>

<?php // ****** --> End banner code here. <-- ******
		}
		*/
?>


			</div> <?php // col-12 for banners ?>
		</div> <?php // row for banners ?>
	<?php
	}
	?>
	</div> <?php // container-fluid ?>

	<?php
	if(FLUID_CATEGORIES_ENABLED == TRUE && FLUID_CATEGORIES_POSITION == "TOP") {
		php_fluid_categories();
	}
	?>

<?php
	// --> Need to set this up here.
	$f_trending_class = "f-trending-div";

	// --> Swiper deals  products to display.
if(FLUID_BLACK_FRIDAY == TRUE) {
		$filter_where = NULL;
		// --> Only show products that have stock or a arrival date or discount date ending in the future.
		$s_date = date("Y-m-d 00:00:00");
		$filter_where .= " AND ((p_stock > 0 AND p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0)) AND ((p_discount_date_start <= '" . $s_date . "' AND p_discount_date_end >= '" . $s_date . "' AND p.p_price_discount IS NOT NULL) OR (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p.p_price_discount IS NOT NULL) OR (p_discount_date_start IS NULL AND p_discount_date_end >= '" . $s_date . "' AND p.p_price_discount IS NOT NULL)) ";

		$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $filter_where . "HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");

			$f_items_sale_html = NULL;
			$f_enough_sale_items = FALSE;
				if(isset($fluid->db_array)) {
					shuffle($fluid->db_array);
					$i_count = 0;
					foreach($fluid->db_array as $data) {
						if($i_count == 4)
							$f_enough_sale_items = TRUE;

						if($i_count == 15)
							break;

						// --> Add items for the google ga tracking.
						if(empty($f_item_list[$data['p_id']]))
							$f_item_list[$data['p_id']] = Array("p_id" => $data['p_id'], "p_mfgcode" => $data['p_mfgcode'], "p_mfg_number" => $data['p_mfg_number'], "p_mfgid" => $data['p_mfgid'], "p_name" => $data['p_name'], "p_price" => $data['p_price'], "m_name" => $data['m_name'], "p_catid" => $data['p_catid'], "m_id" => $data['m_id'], "p_mfgid" => $data['p_mfgid'], "c_name" => $data['c_name'], "p_position" => count($f_item_list));

						$f_items_sale_html .= "<div class=\"swiper-slide swiper-slide-products\">";
							$f_items_sale_html .= "<div class=\"trending-product\">";

								if(empty($data['p_mfgcode']))
									$ft_mfgcode = $data['p_id'];
								else
									$ft_mfgcode = $data['p_mfgcode'];

								/*
								if(empty($data['p_name']))
									$ft_name = $data['p_id'];
								else
									$ft_name = $data['m_name'] . " " . $data['p_name'];
								*/
								if(empty($data['p_name']))
									$ft_name = $data['p_id'];
								else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
									$ft_name = $data['p_name'];
								else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
									$ft_name = $data['p_name'];
								else
									$ft_name = $data['p_mfg_number'] . " " . $data['p_name'];

								//$f_items_sale_html .= "<a style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'>";
									$f_items_sale_html .= "<div class=\"thumbnail trending-product-thumbnail\">";
										$f_items_sale_html .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
										//$f_items_sale_html .= "<div class=\"badge-new\"></div>";
										//$f_items_sale_html .= "<div class=\"badge-sale\"></div>";
										//$f_items_sale_html .= "<div style='min-height: 32px;'></div>"; // Temp place holder when no new or sale badge to keep things aligned properly.
										$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
										$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

										$p_images = $fluid->php_process_images($data['p_images']);
										$width_height_l = $fluid->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
										$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

										$f_items_sale_html .= "<div class=\"caption\">";
											$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data['m_name']) . "</h6></a>";

											$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-name\">";
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
											$f_items_sale_html .= $ft_name . "</h6></a>";

											$f_items_sale_html .= "<div class=\"trending-product-heading-price-container\">";
											if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
											}
											else
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";

												$f_items_sale_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
												if(FLUID_STORE_OPEN == FALSE)
													$f_disabled_style = "disabled";
												else
													$f_disabled_style = NULL;

												$f_items_sale_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";


											$f_items_sale_html .= "</div>";
										$f_items_sale_html .= "</div>";
										$f_items_sale_html .= "</div></div>"; // empty div delete.
									$f_items_sale_html .= "</div>";
								//$f_items_sale_html .= "</a>";
							$f_items_sale_html .= "</div>";
						$f_items_sale_html .= "</div>";

						$i_count++;
					}

				}
	?>

	<?php
	if(isset($f_items_sale_html) && $f_enough_sale_items == TRUE) {
	?>
	<style>
	.swiper-container-products-sale {
		width: 100%;
		margin: auto;
	}

	.swiper-slide-products-sale {
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

	<div class="container-fluid" style='padding-top: 15px; padding-bottom: 15px;'>
		<div class="row">

				<div class="col-sm-12">
					<div class='<?php $f_trending_class_bf = "f-trending-bf-div"; echo $f_trending_class_bf;?>'><?php echo base64_decode(FLUID_BLACK_FRIDAY_MESSAGE_HEADER); ?></div>
				</div>

			<div class="swiper-container swiper-container-products-sale">
				<div class="swiper-wrapper">
					<?php echo $f_items_sale_html; ?>
				</div>
				<div class="swiper-pagination swiper-pagination-products-sale" style='position: static;'></div>
				<!-- Add Arrows -->
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
			</div>

			<div style='text-align: center; padding-top: 10px;'>
				<style>
				.btn-banner-black-friday {
					background-color: #202020f2;
					border-color: black;
				}
				</style>

				<?php
					echo base64_decode(FLUID_BLACK_FRIDAY_BUTTON);
				?>
			</div>
		</div>
	</div>

	<script>
		var swiper = new Swiper('.swiper-container-products-sale', {
			pagination: '.swiper-pagination-products-sale',
			nextButton: '.swiper-button-next',
			prevButton: '.swiper-button-prev',
			paginationClickable: true,
			slidesPerView: 5,
			spaceBetween: 50,
			loop: true,
			autoplay: 12000,
			breakpoints: {
				1024: {
					slidesPerView: 4,
					spaceBetween: 10
				},
				768: {
					slidesPerView: 4,
					spaceBetween: 5
				},
				640: {
					slidesPerView: 3,
					spaceBetween: 10
				},
				550: {
					slidesPerView: 2,
					spaceBetween: 10
				},
				320: {
					slidesPerView: 1,
					spaceBetween: 10
				}
			}
		});
	</script>
	<?php
	}
}
?>


	<?php
	if(FLUID_CATEGORIES_ENABLED == TRUE && FLUID_CATEGORIES_POSITION == "BELOW_BLACKFRIDAY") {
		php_fluid_categories();
	}
	?>
	
<?php
if(FLUID_DISPLAY_FORMULA_DEAL_SLIDER == TRUE) {
	// --> Swiper whats bundles products to display.
	$filter_where = NULL;
	$s_date = date("Y-m-d 00:00:00");
	
	
	// This includes all deals that are not ending.
	$filter_where .= " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

	$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";


	$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

	$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";
	
	
	/*
	// Includes only deals that are ending.
	$filter_where .= " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

	$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";


	$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

	$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";
	*/
	
	// --> Only displays items in bundle slider if a formula message is applied.
	$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL AND p.p_formula_status > 0" . $filter_where . "HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");

		$f_items_bundle_html = NULL;
		$f_enough_bundles_items = FALSE;
		$f_bundle_reg = TRUE;
		$f_bundle_form_8 = TRUE;
		$f_items_bundle_array = NULL;

			if(isset($fluid->db_array)) {
				$fluid_match = new Fluid();
				$fluid_match->php_db_begin();

				shuffle($fluid->db_array);
				$i_count = 0;
				$i_count_reg = 0;
				$i_count_form_8 = 0;
				foreach($fluid->db_array as $data) {
					if($data['p_formula_items_data'] != '')
						$f_match_items = (array)json_decode($data['p_formula_items_data']);

					if(isset($f_match_items) && strlen($data['p_formula_math']) > 0) {
						// --> If a item is discontinued, we do not show it unless it has stock. All other items will be shown. Also the formula option has to be a math operation and not just a message display only.
						if($f_bundle_reg == TRUE && ($data['p_enable'] < 2 || ($data['p_enable'] == 2 && $data['p_stock'] > 0)) && $data['p_formula_operation'] != FORMULA_OPTION_7 && $data['p_formula_operation'] != FORMULA_OPTION_8) {
							if($i_count == 4)
								$f_enough_bundles_items = TRUE;

							// --> Add items for the google ga tracking.
							if(empty($f_item_list[$data['p_id']]))
								$f_item_list[$data['p_id']] = Array("p_id" => $data['p_id'], "p_mfgcode" => $data['p_mfgcode'], "p_mfg_number" => $data['p_mfg_number'], "p_mfgid" => $data['p_mfgid'], "p_name" => $data['p_name'], "p_price" => $data['p_price'], "m_name" => $data['m_name'], "p_catid" => $data['p_catid'], "m_id" => $data['m_id'], "p_mfgid" => $data['p_mfgid'], "c_name" => $data['c_name'], "p_position" => count($f_item_list));

							$f_items_bundle_html_tmp = "<div class=\"swiper-slide swiper-slide-products\">";
								$f_items_bundle_html_tmp .= "<div class=\"trending-product\">";

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

									//$f_items_bundle_html_tmp .= "<a style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'>";
										$f_items_bundle_html_tmp .= "<div class=\"thumbnail trending-product-thumbnail\">";
											$f_items_bundle_html_tmp .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
											//$f_items_bundle_html_tmp .= "<div class=\"badge-new\"></div>";
											//$f_items_bundle_html_tmp .= "<div class=\"badge-sale\"></div>";
											//$f_items_bundle_html_tmp .= "<div style='min-height: 32px;'></div>"; // Temp place holder when no new or sale badge to keep things aligned properly.
											$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
											$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

											$p_images = $fluid->php_process_images($data['p_images']);
											$width_height_l = $fluid->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
											$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image-bundles' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

											$f_items_bundle_html_tmp .= "<div class=\"caption\">";
												$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><div><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data['m_name']) . "</h6>";

												$f_items_bundle_html_tmp .= "<h6 class=\"trending-product-heading-name\">";

												if(strlen($ft_name) > 50)
													$ft_name = substr($ft_name, 0, 50) . '...';

												$f_items_bundle_html_tmp .= $ft_name . "</h6></div></a>";


												$f_items_bundle_html_tmp_pre = "<div class=\"trending-product-heading-price-container\">";
												if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
													//$f_items_bundle_html_tmp .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
													$f_items_bundle_html_tmp_pre .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";

													$p_price = $data['p_price_discount'];
												}
												else  {
													$f_items_bundle_html_tmp_pre .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";

													$p_price = $data['p_price'];
												}

												// --> Apply the math formula.
												$f_formula = $data['p_formula_math'];

												$f_vars = FORMULA_VARIABLES;

												$f_vars_data = Array($data['p_price'], $p_price, $data['p_stock'], $data['p_cost'], $data['p_length'], $data['p_width'], $data['p_height'], $data['p_height']);

												$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

												$parser = new StdMathParser();

												$AST = $parser->parse($f_formula);

												// --> Evaluate the expression.
												$evaluator = new Evaluator();

												$value = $AST->accept($evaluator);

												if($value <= 0)
													$value = $data['p_price'];

												$f_savings = $p_price - $value;

												$f_items_bundle_html_tmp .= "<div style='font-size: 80%; padding-top: 10px; padding-bottom: 10px; text-align: left; font-weight: 600; font-style: italic;'><div style='display: inline-block; width: 100%;'><div style='display: inline-block; float:left; padding-right: 3px;'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div>" . $data['p_formula_message'] . "</div></div>";
												$f_items_bundle_html_tmp .= $f_items_bundle_html_tmp_pre;
												$f_items_bundle_html_tmp .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($value, 2, '.', ',') . " *</span>";

												$f_items_bundle_html_tmp .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
												if(FLUID_STORE_OPEN == FALSE)
													$f_disabled_style = "disabled";
												else
													$f_disabled_style = NULL;

												$f_items_bundle_html_tmp .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;' onClick='FluidMenu.button.obj_div = this; FluidMenu.button.obj_div_id=\"" . $data['p_id'] . "\";'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";

												$f_items_bundle_html_tmp .= "</div>";

											  $f_items_bundle_html_tmp .= "</div>";

											$f_items_bundle_html_tmp .= "</div>";


										  $f_items_bundle_html_tmp .= "</div>"; // empty div delete.
										$f_items_bundle_html_tmp .= "</div>";
									//$f_items_bundle_html_tmp .= "</a>";

								$f_items_bundle_html_tmp .= "</div>";
							$f_items_bundle_html_tmp .= "</div>";

							$i_count++;

							//$i_count_reg++;
							//if($i_count_reg == 7)
								//$f_bundle_reg = FALSE;

							$f_items_bundle_array[] = $f_items_bundle_html_tmp;

						}
						// --> This one will show bundle items that are not in stock.
						else if($f_bundle_form_8 == TRUE && ($data['p_enable'] < 2) && $data['p_formula_operation'] == FORMULA_OPTION_8) {
							$f = 0;
							$f_bundle_select = NULL;
							foreach($f_match_items as $f_match_tmp) {
								if($f > 0)
									$f_bundle_select .= ", ";

								$f_bundle_select .= "'" . $fluid->php_escape_string(base64_decode($f_match_tmp)) . "'";

								$f++;
							}

							if(isset($f_bundle_select)) {
								$fluid_match->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id
								WHERE p.p_enable > '0' AND c.c_enable = 1 AND p_mfgcode IN (" . $f_bundle_select . ") HAVING p_zero_status_tmp > 0 ORDER BY p_mfgcode ASC");

								$f_bundle_items = NULL;
								if(isset($fluid_match->db_array)) {
									foreach($fluid_match->db_array as $f_b_items) {
										$f_bundle_items[$f_b_items['p_mfgcode']] = $f_b_items;
									}
								}

								if(isset($f_bundle_items)) {
									foreach($f_bundle_items as $f_bundle_item_tmp) {
										if($i_count == 4)
											$f_enough_bundles_items = TRUE;

										// --> Need to copy over the data on each loop to prevent data merging issues.
										$data_tmp = $data;

										$f_items_bundle_html_tmp = "<div class=\"swiper-slide swiper-slide-products\">";
											$f_items_bundle_html_tmp .= "<div class=\"trending-product\">";
												$f_p_link = "_" . $data_tmp['p_id'];

												// --> Check to see if we need to flip bundle order items around.
												if($data_tmp['p_formula_flip'] == 1) {
													if(empty($data_tmp['p_mfgcode']))
														$ft_mfgcode = $data_tmp['p_id'];
													else
														$ft_mfgcode = $data_tmp['p_mfgcode'];

													$f_bundle_item_tmp['p_name'] = $data_tmp['p_name'] . " w/" . $f_bundle_item_tmp['p_name'];

													if(empty($f_bundle_item_tmp['p_name']))
														$ft_name = $data_tmp['p_id'];
													else if(empty($data_tmp['p_mfg_number']) || $data_tmp['p_namenum'] == FALSE)
														$ft_name = $f_bundle_item_tmp['p_name'];
													else if(empty($data_tmp['p_mfg_number']) && $data_tmp['p_namenum'] == TRUE)
														$ft_name = $f_bundle_item_tmp['p_name'];
													else
														$ft_name = $data_tmp['p_mfg_number'] . " " . $f_bundle_item_tmp['p_name'];
												}
												else {
													if(empty($f_bundle_item_tmp['p_mfgcode']))
														$ft_mfgcode = $f_bundle_item_tmp['p_id'];
													else
														$ft_mfgcode = $f_bundle_item_tmp['p_mfgcode'];

													$f_bundle_item_tmp['p_name'] .= " w/" . $data_tmp['p_name'];

													if(empty($f_bundle_item_tmp['p_name']))
														$ft_name = $f_bundle_item_tmp['p_id'];
													else if(empty($f_bundle_item_tmp['p_mfg_number']) || $f_bundle_item_tmp['p_namenum'] == FALSE)
														$ft_name = $f_bundle_item_tmp['p_name'];
													else if(empty($f_bundle_item_tmp['p_mfg_number']) && $f_bundle_item_tmp['p_namenum'] == TRUE)
														$ft_name = $f_bundle_item_tmp['p_name'];
													else
														$ft_name = $f_bundle_item_tmp['p_mfg_number'] . " " . $f_bundle_item_tmp['p_name'];
												}

												$f_items_bundle_html_tmp .= "<div class=\"thumbnail trending-product-thumbnail\">";
												$f_items_bundle_html_tmp .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";

												$f_img_name = str_replace(" ", "_", $f_bundle_item_tmp['m_name'] . "_" . $f_bundle_item_tmp['p_name'] . "_" . $f_bundle_item_tmp['p_mfgcode']);
												$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

												$p_images = $fluid->php_process_images($f_bundle_item_tmp['p_images']);
												$width_height_l = $fluid->php_process_image_resize($p_images[0], "75", "75", $f_img_name);

												$f_img_name_bund = str_replace(" ", "_", $data_tmp['m_name'] . "_" . $data_tmp['p_name'] . "_" . $data_tmp['p_mfgcode']);
												$f_img_name_bund = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name_bund);

												$p_images_bund = $fluid->php_process_images($data_tmp['p_images']);
												$width_height_l_bund = $fluid->php_process_image_resize($p_images_bund[0], "75", "75", $f_img_name_bund);

												// --> Check to see if we need to flip bundle order items around.
												if($data_tmp['p_formula_flip'] == 1) {
													$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='display: inline-block; width: 45%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image-bundles' src='" . $_SESSION['fluid_uri'] . $width_height_l_bund['image'] . "' alt=\"" . str_replace('"', '', $data_tmp['m_name'] . " " . $data_tmp['p_name']) . "\"/></img></a>";

													$f_items_bundle_html_tmp .= " <div style='display: inline-block;'>+</div> <a class='f-a-link-default' style='display: inline-block; width: 45%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image-bundles' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $f_bundle_item_tmp['m_name'] . " " . $f_bundle_item_tmp['p_name']) . "\"/></img></a>";

													$f_items_bundle_html_tmp .= "<div class=\"caption\">";
														$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><div><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data_tmp['m_name']) . "</h6>";
												}
												else {
													$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='display: inline-block; width: 45%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image-bundles' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $f_bundle_item_tmp['m_name'] . " " . $f_bundle_item_tmp['p_name']) . "\"/></img></a>";

													$f_items_bundle_html_tmp .= " <div style='display: inline-block;'>+</div> <a class='f-a-link-default' style='display: inline-block; width: 45%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image-bundles' src='" . $_SESSION['fluid_uri'] . $width_height_l_bund['image'] . "' alt=\"" . str_replace('"', '', $data_tmp['m_name'] . " " . $data_tmp['p_name']) . "\"/></img></a>";

													$f_items_bundle_html_tmp .= "<div class=\"caption\">";
														$f_items_bundle_html_tmp .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $f_bundle_item_tmp['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><div><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($f_bundle_item_tmp['m_name']) . "</h6>";
												}

															$f_items_bundle_html_tmp .= "<h6 class=\"trending-product-heading-name\">";

															if(strlen($ft_name) > 150)
																$ft_name = substr($ft_name, 0, 150) . '...';

															$f_items_bundle_html_tmp .= $ft_name . "</h6></div></a>";


															$f_items_bundle_html_tmp_pre = "<div class=\"trending-product-heading-price-container\">";


															// --> Check to see which prices we should be using from each bundle item.
															if($data_tmp['p_price_discount'] && ((strtotime($data_tmp['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data_tmp['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data_tmp['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data_tmp['p_discount_date_end'] == NULL) || ($data_tmp['p_discount_date_start'] == NULL && $data_tmp['p_discount_date_end'] == NULL)))
																$f_data_tmp_price = $data_tmp['p_price_discount'];
															else
																$f_data_tmp_price = $data_tmp['p_price'];

															// --> Check to see which prices we should be using from each bundle item.
															if($f_bundle_item_tmp['p_price_discount'] && ((strtotime($f_bundle_item_tmp['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($f_bundle_item_tmp['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($f_bundle_item_tmp['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $f_bundle_item_tmp['p_discount_date_end'] == NULL) || ($f_bundle_item_tmp['p_discount_date_start'] == NULL && $f_bundle_item_tmp['p_discount_date_end'] == NULL)))
																$f_data_bundle_tmp_price = $f_bundle_item_tmp['p_price_discount'];
															else
																$f_data_bundle_tmp_price = $f_bundle_item_tmp['p_price'];

															//$data_tmp['p_price'] = $data_tmp['p_price'] + $data_tmp['p_bundle_item']['p_price'];
															//$f_price_org = $f_data_tmp_price + $f_data_bundle_tmp_price;
															$f_price_org = $data_tmp['p_price'] + $f_bundle_item_tmp['p_price'];

															// --> Apply the new pricing to the bundle item now.
															$data_tmp['p_price'] = $f_data_tmp_price + $f_data_bundle_tmp_price;

															// --> Apply the math formula.
															$f_formula = $data_tmp['p_formula_math'];

															$f_vars = FORMULA_VARIABLES;

															$f_vars_data = Array($data_tmp['p_price'], $data_tmp['p_price'], $data_tmp['p_stock'], $data_tmp['p_cost'], $data_tmp['p_length'], $data_tmp['p_width'], $data_tmp['p_height'], $data_tmp['p_height']);

															$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

															$parser = new StdMathParser();

															$AST = $parser->parse($f_formula);

															// --> Evaluate the expression.
															$evaluator = new Evaluator();

															$value = $AST->accept($evaluator);

															if($value <= 0)
																$value = $data_tmp['p_price'];

															$value = $data_tmp['p_price'] - $value;
															$value = -1 * abs($value);

															$data_tmp['p_price_discount'] = $data_tmp['p_price'] + $value;
															//$data_tmp['p_price_discount'] = $value;
															$f_savings = $f_price_org - $data_tmp['p_price_discount'];

															$f_items_bundle_html_tmp_pre .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($f_price_org, 2, '.', ',') . "</span>";

															$f_items_bundle_html_tmp .= "<div style='font-size: 80%; padding-top: 10px; padding-bottom: 10px; text-align: left; font-weight: 600; font-style: italic;'><div style='display: inline-block; width: 100%;'><div style='display: inline-block; float:left; padding-right: 3px;'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div></div></div>";
															$f_items_bundle_html_tmp .= $f_items_bundle_html_tmp_pre;
															$f_items_bundle_html_tmp .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($f_price_org - $f_savings, 2, '.', ',') . " *</span>";
															$f_items_bundle_html_tmp .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
															if(FLUID_STORE_OPEN == FALSE)
																$f_disabled_style = "disabled";
															else
																$f_disabled_style = NULL;

															$f_items_bundle_html_tmp .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "' id='fluid-button-" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;' onClick='FluidMenu.button.obj_div = this; FluidMenu.button.obj_div_id=\"" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "\";'><button name='fluid-cart-btn-" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "' id='fluid-cart-btn-" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data_tmp['p_id'] . "_" . $f_bundle_item_tmp['p_id'] ."\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";

															$f_items_bundle_html_tmp .= "</div>";

														  $f_items_bundle_html_tmp .= "</div>";

														$f_items_bundle_html_tmp .= "</div>";


													  $f_items_bundle_html_tmp .= "</div>"; // empty div delete.
													$f_items_bundle_html_tmp .= "</div>";
												//$f_items_bundle_html_tmp .= "</a>";

											$f_items_bundle_html_tmp .= "</div>";
										$f_items_bundle_html_tmp .= "</div>";

										$i_count++;

										//$i_count_form_8++;

										$f_items_bundle_array[] = $f_items_bundle_html_tmp;
										//if($i_count_form_8 == 7)
											//$f_bundle_form_8 = FALSE;
									}
								}
							}
						}

						//if($i_count == 15)
							//break;
					}
				}
				$fluid_match->php_db_commit();
			}

			if(isset($f_items_bundle_array)) {
				shuffle($f_items_bundle_array);

				$i_count = 0;
				foreach($f_items_bundle_array as $f_tmp_bundles) {
					if($i_count == 17)
						break;

					$f_items_bundle_html .= $f_tmp_bundles;

					$i_count++;
				}				
			}
?>

	<?php

	if(isset($f_items_bundle_html) && $f_enough_bundles_items == TRUE) {
	?>
	<style>
	.swiper-container-products-bundles {
		width: 100%;
		margin: auto;
	}

	.swiper-slide-products-bundles {
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

	<div class="container-fluid" style='padding-top: 15px; padding-bottom: 15px;'>
		<div class="row">

				<div class="col-sm-12">
					<div class='<?php echo $f_trending_class; $f_trending_class = 'f-trending-div-font';?>'><?php echo base64_decode(FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER); ?>
					</div>
				</div>

			<div class="swiper-container swiper-container-products-bundles">
				<div class="swiper-wrapper">
					<?php echo $f_items_bundle_html; ?>
				</div>
				<div class="swiper-pagination swiper-pagination-products-bundles" style='position: static;'></div>
				<!-- Add Arrows -->
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
			</div>

			<div style='text-align: center; padding-top: 10px;'>
				<?php
					echo base64_decode(FLUID_FORMULA_BUTTON);
				?>
			</div>
		</div>
	</div>

	<script>
		var swiper = new Swiper('.swiper-container-products-bundles', {
			pagination: '.swiper-pagination-products-bundles',
			nextButton: '.swiper-button-next',
			prevButton: '.swiper-button-prev',
			paginationClickable: true,
			slidesPerView: 5,
			spaceBetween: 50,
			loop: true,
			autoplay: 12000,
			breakpoints: {
				1024: {
					slidesPerView: 4,
					spaceBetween: 10
				},
				768: {
					slidesPerView: 4,
					spaceBetween: 5
				},
				640: {
					slidesPerView: 3,
					spaceBetween: 10
				},
				550: {
					slidesPerView: 2,
					spaceBetween: 10
				},
				320: {
					slidesPerView: 1,
					spaceBetween: 10
				}
			}
		});
	</script>
<?php
	}
}
?>

<?php
if(FLUID_CATEGORIES_ENABLED == TRUE && FLUID_CATEGORIES_POSITION == "BELOW_FORMULA") {
	php_fluid_categories();
}
?>

<?php
if(FLUID_DISPLAY_DEAL_SLIDER == TRUE) {
	// --> Swiper deals  products to display.
	$filter_where = NULL;
	// --> Only show products that have stock or a arrival date or discount date ending in the future.
	$s_date = date("Y-m-d 00:00:00");
	$filter_where .= " AND ((p_stock > 0 AND p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0)) AND ((p_discount_date_start <= '" . $s_date . "' AND p_discount_date_end >= '" . $s_date . "' AND p.p_price_discount IS NOT NULL) OR (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p.p_price_discount IS NOT NULL) OR (p_discount_date_start IS NULL AND p_discount_date_end >= '" . $s_date . "' AND p.p_price_discount IS NOT NULL)) ";

	$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $filter_where . "HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");

		$f_items_sale_html = NULL;
		$f_enough_sale_items = FALSE;
			if(isset($fluid->db_array)) {
				shuffle($fluid->db_array);
				$i_count = 0;
				foreach($fluid->db_array as $data) {
					// --> If a item is discontinued, we do not show it unless it has stock. All other items will be shown.
					if($data['p_enable'] < 2 || ($data['p_enable'] == 2 && $data['p_stock'] > 0)) {
						if($i_count == 4)
							$f_enough_sale_items = TRUE;

						if($i_count == 15)
							break;

						// --> Add items for the google ga tracking.
						if(empty($f_item_list[$data['p_id']]))
							$f_item_list[$data['p_id']] = Array("p_id" => $data['p_id'], "p_mfgcode" => $data['p_mfgcode'], "p_mfg_number" => $data['p_mfg_number'], "p_mfgid" => $data['p_mfgid'], "p_name" => $data['p_name'], "p_price" => $data['p_price'], "m_name" => $data['m_name'], "p_catid" => $data['p_catid'], "m_id" => $data['m_id'], "p_mfgid" => $data['p_mfgid'], "c_name" => $data['c_name'], "p_position" => count($f_item_list));

						$f_items_sale_html .= "<div class=\"swiper-slide swiper-slide-products\">";
							$f_items_sale_html .= "<div class=\"trending-product\">";

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

								//$f_items_sale_html .= "<a style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'>";
									$f_items_sale_html .= "<div class=\"thumbnail trending-product-thumbnail\">";
										$f_items_sale_html .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
										//$f_items_sale_html .= "<div class=\"badge-new\"></div>";
										//$f_items_sale_html .= "<div class=\"badge-sale\"></div>";
										//$f_items_sale_html .= "<div style='min-height: 32px;'></div>"; // Temp place holder when no new or sale badge to keep things aligned properly.
										$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
										$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

										$p_images = $fluid->php_process_images($data['p_images']);
										$width_height_l = $fluid->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
										$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

										$f_items_sale_html .= "<div class=\"caption\">";
											$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data['m_name']) . "</h6></a>";

											$f_items_sale_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-name\">";
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
											$f_items_sale_html .= $ft_name . "</h6></a>";

											$f_items_sale_html .= "<div class=\"trending-product-heading-price-container\">";
											if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
											}
											else
												$f_items_sale_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";

												$f_items_sale_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
												if(FLUID_STORE_OPEN == FALSE)
													$f_disabled_style = "disabled";
												else
													$f_disabled_style = NULL;

												$f_items_sale_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";


											$f_items_sale_html .= "</div>";
										$f_items_sale_html .= "</div>";
										$f_items_sale_html .= "</div></div>"; // empty div delete.
									$f_items_sale_html .= "</div>";
								//$f_items_sale_html .= "</a>";
							$f_items_sale_html .= "</div>";
						$f_items_sale_html .= "</div>";

						$i_count++;
					}
				}

			}
?>

<?php
if(isset($f_items_sale_html) && $f_enough_sale_items == TRUE) {
?>
<style>
.swiper-container-products-sale {
    width: 100%;
    margin: auto;
}

.swiper-slide-products-sale {
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

<div class="container-fluid" style='padding-top: 15px; padding-bottom: 15px;'>
	<div class="row">

			<div class="col-sm-12">
				<div class='<?php echo $f_trending_class; $f_trending_class = 'f-trending-div-font';?>'><?php echo base64_decode(FLUID_DEAL_SLIDER_MESSAGE_HEADER);?></div>
			</div>

		<div class="swiper-container swiper-container-products-sale">
			<div class="swiper-wrapper">
				<?php echo $f_items_sale_html; ?>
			</div>
			<div class="swiper-pagination swiper-pagination-products-sale" style='position: static;'></div>
			<!-- Add Arrows -->
			<div class="swiper-button-next"></div>
			<div class="swiper-button-prev"></div>
		</div>

		<div style='text-align: center; padding-top: 10px;'>
			<?php
				echo base64_decode(FLUID_DEAL_BUTTON);
			?>
		</div>
    </div>
</div>

<script>
    var swiper = new Swiper('.swiper-container-products-sale', {
        pagination: '.swiper-pagination-products-sale',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
        slidesPerView: 5,
        spaceBetween: 50,
        loop: true,
        autoplay: 12000,
        breakpoints: {
            1024: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 4,
                spaceBetween: 5
            },
            640: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            550: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 10
            }
        }
    });
</script>
<?php
	}
}
?>

<?php
if(FLUID_CATEGORIES_ENABLED == TRUE && FLUID_CATEGORIES_POSITION == "BELOW_DEAL") {
	php_fluid_categories();
}
?>

<?php
	// --> Swiper whats trending products to display.
if(FLUID_DISPLAY_TRENDING_SLIDER == TRUE) {
	$filter_where = NULL;
	// --> Only show products that have stock or a arrival date or discount date ending in the future.
	if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == TRUE) {
		$s_date = date("Y-m-d 00:00:00");
		$filter_where .= " AND ((p_stock > 0 AND p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0 OR (p_newarrivalenddate >= '" . $s_date . "' OR (p_discount_date_start <= '" . $s_date . "' AND p_discount_date_end >= '" . $s_date . "' AND p_price_discount IS NOT NULL) OR (p_discount_date_start IS NULL AND p_discount_date_end >= '" . $s_date . "' AND p.p_price_discount IS NOT NULL) ) OR (p_date_hide > '" . $s_date . "') ) ";
	}
	else
		$filter_where .= " AND ((p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0) ";

	$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_trending > 0 AND p.p_price IS NOT NULL " . $filter_where . "HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");

		$f_items_html = NULL;
			if(isset($fluid->db_array)) {
				shuffle($fluid->db_array);
				$i_count = 0;
				foreach($fluid->db_array as $data) {
					// --> If a item is discontinued, we do not show it unless it has stock. All other items will be shown.
					if($data['p_enable'] < 2 || ($data['p_enable'] == 2 && $data['p_stock'] > 0)) {
						if($i_count == 15)
							break;

						// --> Add items for the google ga tracking.
						if(empty($f_item_list[$data['p_id']]))
							$f_item_list[$data['p_id']] = Array("p_id" => $data['p_id'], "p_mfgcode" => $data['p_mfgcode'], "p_mfg_number" => $data['p_mfg_number'], "p_mfgid" => $data['p_mfgid'], "p_name" => $data['p_name'], "p_price" => $data['p_price'], "m_name" => $data['m_name'], "p_catid" => $data['p_catid'], "m_id" => $data['m_id'], "p_mfgid" => $data['p_mfgid'], "c_name" => $data['c_name'], "p_position" => count($f_item_list));

						$f_items_html .= "<div class=\"swiper-slide swiper-slide-products\">";
							$f_items_html .= "<div class=\"trending-product\">";

								if(empty($data['p_mfgcode']))
									$ft_mfgcode = $data['p_id'];
								else
									$ft_mfgcode = $data['p_mfgcode'];

								/*
								if(empty($data['p_name']))
									$ft_name = $data['p_id'];
								else
									$ft_name = $data['m_name'] . " " . $data['p_name'];
								*/

								if(empty($data['p_name']))
									$ft_name = $data['p_id'];
								else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
									$ft_name = $data['p_name'];
								else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
									$ft_name = $data['p_name'];
								else
									$ft_name = $data['p_mfg_number'] . " " . $data['p_name'];

								//$f_items_html .= "<a style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'>";
									$f_items_html .= "<div class=\"thumbnail trending-product-thumbnail\">";
										$f_items_html .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
										//$f_items_html .= "<div class=\"badge-new\"></div>";
										//$f_items_html .= "<div class=\"badge-sale\"></div>";
										//$f_items_html .= "<div style='min-height: 32px;'></div>"; // Temp place holder when no new or sale badge to keep things aligned properly.
										$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
										$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

										$p_images = $fluid->php_process_images($data['p_images']);
										$width_height_l = $fluid->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
										$f_items_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

										$f_items_html .= "<div class=\"caption\">";
											$f_items_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-manufacturer\">" . $fluid->php_clean_string($data['m_name']) . "</h6></a>";

											$f_items_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-name\">";
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
											$f_items_html .= $ft_name . "</h6></a>";

											$f_items_html .= "<div class=\"trending-product-heading-price-container\">";
											if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
												$f_items_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
												$f_items_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
											}
											else
												$f_items_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";

												$f_items_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
												if(FLUID_STORE_OPEN == FALSE)
													$f_disabled_style = "disabled";
												else
													$f_disabled_style = NULL;

												$f_items_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";

											$f_items_html .= "</div>";
										$f_items_html .= "</div>";
										$f_items_html .= "</div></div>"; // empty div delete.
									$f_items_html .= "</div>";
								//$f_items_html .= "</a>";
							$f_items_html .= "</div>";
						$f_items_html .= "</div>";

						$i_count++;
					}
				}

			}
?>

	<?php
	if(isset($f_items_html)) {
	?>
	<style>
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
	</style>

	<div class="container-fluid" style='padding-top: 15px; padding-bottom: 15px;'>
		<div class="row">

				<div class="col-sm-12">
					<div class='<?php echo $f_trending_class; $f_trending_class = 'f-trending-div-font';?>'><?php echo base64_decode(FLUID_TRENDING_SLIDER_MESSAGE_HEADER);?></div>
				</div>

			<div class="swiper-container swiper-container-products">
				<div class="swiper-wrapper">
					<?php echo $f_items_html; ?>
				</div>
				<div class="swiper-pagination swiper-pagination-products" style='position: static;'></div>
				<!-- Add Arrows -->
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
			</div>

		</div>
	</div>

	<script>
		var swiper = new Swiper('.swiper-container-products', {
			pagination: '.swiper-pagination-products',
			nextButton: '.swiper-button-next',
			prevButton: '.swiper-button-prev',
			paginationClickable: true,
			slidesPerView: 5,
			spaceBetween: 50,
			loop: true,
			autoplay: 12000,
			breakpoints: {
				1024: {
					slidesPerView: 4,
					spaceBetween: 10
				},
				768: {
					slidesPerView: 4,
					spaceBetween: 5
				},
				640: {
					slidesPerView: 3,
					spaceBetween: 10
				},
				550: {
					slidesPerView: 2,
					spaceBetween: 10
				},
				320: {
					slidesPerView: 1,
					spaceBetween: 10
				}
			}
		});
	</script>
<?php
	}
}
?>

<?php
if(FLUID_CATEGORIES_ENABLED == TRUE && FLUID_CATEGORIES_POSITION == "BELOW_TRENDING") {
	php_fluid_categories();
}
?>


	<div id='f-container-social-media' class="container-fluid container-about-leos about-stay-connected" style='border-top: 1px solid rgba(0, 0, 0, .15);'>
		<div>
			<div>
				<h4>Stay Connected</h4>
			</div>

			<div style='padding-bottom: 15px;'>
				<div class='col-sm-1'></div>
				<div class='col-sm-10'>
					<div style='display: inline-block; text-align: center; width: 100%;'>
						<div style='display: inline-block; padding: 5px;'>
							<a href="https://www.facebook.com/LeosVancouver" target="_blank"><div class='fa fa-4x fa-facebook-official' style='color: #3B5998;'></div></a>
						</div>
						<div style='display: inline-block; padding: 5px;'>
							<a href="https://twitter.com/LeosCamera" target="_blank"><div class='fa fa-4x fa-twitter-square' style='color: #2EAEF7;'></div></a>
						</div>

						<div style='display: inline-block; padding: 5px;'>
							<a href="https://www.youtube.com/c/LeosCameraSupplyTV" target="_blank"><div class='fa fa-4x fa-youtube-square' style='color: red;'></div></a>
						</div>
						<div style='display: inline-block; padding: 5px;'>
							<a href="https://www.instagram.com/leoscamerasupply/" target="_blank"><div class='fa fa-4x fa-instagram' style='color: black;'></div></a>
						</div>
					</div>
				</div>
				<div class='col-sm-1'></div>
			</div>
		</div>
	</div> <!-- container end -->

	<script>
		<?php // --> Google tracking ?>
		function js_ga_listings(data) {
			var f_found = false;
			if(data != null) {
				$.each(data, function(key, value) {
					if(value != null) {
						<?php
						if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
						?>
							ga('ec:addImpression', {
							  'id': value['p_mfgcode'],
							  'name': value['m_name'] + " " + value['p_name'],
							  'category': value['c_name'],
							  'brand': value['m_name'],
							  'variant': value['p_mfgcode'],
							  'list': document.title,
							  'position': value['p_position']
							});
						<?php
						}
						?>

						f_found = true;
					}
				}
				);
			}

			<?php
			if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			?>
				if(f_found == true) {
					 ga('send', 'pageview');
				}
			<?php
			}
			?>
		}

		var Fluid_ga_items = JSON.parse(Base64.decode("<?php echo base64_encode(json_encode($f_item_list));?>"));

		js_ga_listings(Fluid_ga_items);
	</script>

	<link href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/jquery.socialfeed.css');?>" rel="stylesheet" type="text/css">
    <script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/codebird.js');?>"></script>
    <!-- doT.js for rendering templates -->
    <script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/doT.min.js');?>"></script>
    <!-- Moment.js for showing "time ago" -->
    <script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/moment.min.js');?>"></script>
    <!-- Social-feed js -->
    <script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/jquery.socialfeed.js');?>"></script>

<script>
	var f_social_media_loaded = false;

	var updateFeed = function() {
		$('.social-feed-container').socialfeed({
			facebook: {
				accounts: ['<?php echo FACEBOOK_NAME; ?>'],
				access_token: '<?php echo FACEBOOK_APPTOKEN; ?>',
				<?php
				if($detect->isMobile())
					echo "limit: 3,";
				else
					echo "limit: 5,";
				?>
			},
			twitter: {
				accounts: ['<?php echo TWITTER_NAME; ?>'],
				consumer_key: '<?php echo TWITTER_CONSUMER_KEY; ?>', <?php // make sure to have your app read-only ?>

				consumer_secret: '<?php echo TWITTER_SECRET_KEY; ?>', <?php // make sure to have your app read-only ?>

				<?php
				if($detect->isMobile())
					echo "limit: 3,";
				else
					echo "limit: 5,";
				?>
			},
			instagram: {
				accounts: ['<?php echo INSTAGRAM_ACCOUNT; ?>'],
				access_token: '<?php echo INSTAGRAM_ACCESS_TOKEN; ?>',
				<?php
				if($detect->isMobile())
					echo "limit: 3,";
				else
					echo "limit: 5,";
				?>
			},
			youtube: {
				feed_url: '<?php echo $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/js/youtube.feed.php"; ?>',
				picture: '<?php echo YOUTUBE_USER_LOGO; ?>',
				<?php
				if($detect->isMobile())
					echo "limit: 3,";
				else
					echo "limit: 5,";
				?>
			 },

			media_min_width: 100,
			length: 200,
			show_media: true,
			template : "css/social.media.template.html"
		});
	};

    $(document).ready(function() {
		if(f_social_media_loaded == false && ($('#f-container-social-media').visible(true) == true || $('#f-logo-about').visible(true) == true)) {
			f_social_media_loaded = true;
			updateFeed();
		}

		<?php
		// --> Turn this into a timer which refresh periodically ?? Perhaps not.
		/*
        $('#button-update').click(function() {
            //first, get rid of old data/posts.
            $('.social-feed-container').html('');

            //then load new posts
            updateFeed();
        });
        */
        ?>

    });

    $(window).scroll(function(){
		if(f_social_media_loaded == false && ($('#f-container-social-media').visible(true) == true || $('#f-logo-about').visible(true) == true)) {
			f_social_media_loaded = true;
			updateFeed();
		}
    });

    </script>


<style>
.swiper-container-social {
    width: 100%;
    margin: auto;
    background-color: #F3F1F2;
}

.swiper-slide-social {
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

.social-feed-container {
	height: 460px;
	padding-top: 5px;
	padding-bottom: 5px;
}

.fluid-feed-element {
	min-height: 420px;
	max-height: 420px;
}
</style>


<div class='container-about-leos f-wrapper-bottom-div-social'>
	<div class='css-overlay-social-media' id='f-loading-overlay-social-media'>
		<div style='opacity: 1.0;'>
			<div style='display:table; margin: 0 auto;'><div style='font-size: 18px;'><i class="fa fa-refresh fa-spin-fluid fa-4x fa-fw"></i></div><span class="sr-only">Loading...</span></div>
		</div>
	</div>

    <div class="swiper-container swiper-container-social">

		<div class="swiper-wrapper social-feed-container">


		</div>

        <!-- Add Pagination -->
        <div class="swiper-pagination swiper-pagination-social"></div>
        <!-- Add Arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

	<div class="row row-about-leos">
		<div class='f-logo-about' id='f-logo-about'>
			<div style='display: inline-block; padding-right: 20px;'><img style='height: 65px;' src='files/camera_small.png'></img></div>
			<div style='display: inline-block; height: 100px; padding-top: 15px; vertical-align: bottom;'><span class='icon-leos-logo-rotate' style='font-size: 65px; color: red;'></span></div>
			<div style='display: inline-block; padding-left: 20px;'><img style='height: 65px; transform: scaleX(-1); -webkit-transform: scaleX(-1); -o-transform: scaleX(-1); -moz-transform: scaleX(-1); filter: FlipH; -ms-filter: "FlipH";' src='files/camera_small.png'></img></div>
		</div>

		<div style='padding: 15px 5px 0px 5px;'>
			<p>Since 1955, Leo's Camera has been serving the professional, student and serious photographer. Our reputation, helpful and professional staff stand alone, above all, to give you the helpful guidance rarely seen in today's retail environment.</p>
			<p style='padding-top: 10px;'>Our professional expertise and knowledge can help you achieve the highest quality in all types of photography. Add this to our very competitive prices and you can't go wrong.</p>
		</div>

		<div>
			<div class="about-border"></div>
		</div>

		<div>
			<p class="about-leos-paragraph">Hours: Monday to Friday 10:00am - 4:30pm</p>
		</div>

		<div>
			<p class="about-leos-paragraph"><a href='https://goo.gl/maps/1yy12Y9Lnky' target='_blank'>1055 Granville St, Vancouver, BC, CANADA V6Z1L4</a></p>
			<p class="about-leos-phone-number"><a href='tel:+16046855331'>604-685-5331</a></p>
			<p class="about-leos-phone-number"><a href="mailto:info@leoscamera.com">info@leoscamera.com</a></p>
		</div>

<script type="text/javascript">

	$(document).ready(function() {
		initialize_map();
	});

  function initialize_map() {
    var latlng = new google.maps.LatLng(49.278781, -123.123779);

    var myOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);

	var contentString = '<div id="content">'+
		'<div id="siteNotice">'+
		'</div>'+
		'<span class="icon-leos-logo-rotate" style="font-size: 35px; color: red;"></span>'+
		'<div id="bodyContent" style="padding-top: 5px;">'+
		'<div style="font-size: 10px">1055 Granville Street</div><div style="font-size: 10px">Vancouver, BC</div><div style="font-size: 10px">Canada, V6Z1L4</div><div style="font-size: 10px">Ph: 604-685-5331</div><div style="font-size: 10px">Fax: 604-685-5648</div><div style="font-size: 10px">www.leoscamera.com</div>'+
		'</div>';

	var infowindow = new google.maps.InfoWindow({
		content: contentString,
		maxWidth: 140
	});

	var marker = new google.maps.Marker({
      position: latlng,
      map: map,
      title:"Leo's Camera Supply Ltd."
	});

	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map,marker);
	});
  }
</script>

		<div style='padding-top: 20px;'>
			<div id="map_canvas" class='f-map-canvas'></div>
		</div>
	</div>

<script>
    var appendNumber = 0;
    var swiper_social = new Swiper('.swiper-container-social', {
        pagination: '.swiper-pagination-social',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
        slidesPerView: 4,
        spaceBetween: 10,
        breakpoints: {
            1400: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            767: {
                slidesPerView: 1,
                spaceBetween: 5
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 5
            },
            550: {
                slidesPerView: 1,
                spaceBetween: 5
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 5
            }
        }
    });

    function js_social_append(dtcreate) {
        ++appendNumber;
        swiper_social.appendSlide('<div id="social-media-' + appendNumber + '" dt-create="' + dtcreate + '" style="max-height: 600px;" class="swiper-slide"></div>');
        swiper_social.update();
	}
</script>



	<?php
	$fluid->php_db_commit();
	require_once("footer.php");
	?>

	</body>
	</html>
<?php
}

function php_fluid_categories() {
	$fluid = new Fluid();
	$fluid->php_db_begin();
 ?>
 <div id="product-categories" class="fluid-category-row fluid-cat-mobile">
	 <?php //<div class="col-sm-12"><h4>free shipping</h4></div>?>
	 <?php
	 // --> Bring this back to have the grey box background around the category buttons. Also change col-md-12 back to col-md-10 in the first <div> below. Also need comment out #product-categories -> background-image in fluid.index.css
	 //<div class="col-sm-0 col-md-1"></div>
	 ?>
	 <div>
		 <div class="col-sm-12 col-md-12" style='margin-bottom: 20px;'>
			 <div>
				 <div class="col-md-12 col-lg-12 f-background-white-style" style='padding-bottom: 10px;'>
					 <div>
					 <?php

						 $fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " WHERE c_enable = 1 AND c_parent_id IS NULL ORDER BY c_sortorder ASC");
						 $f_cat_array = NULL;
						 if(isset($fluid->db_array)) {
							 foreach($fluid->db_array as $data) {

								 $c_images = $fluid->php_process_images($data['c_image']);
								 $width_height_l = $fluid->php_process_image_resize($c_images[0], "160", "160");

								 $f_cat_array .= "<div class='col-xs-6 col-sm-4 col-md-3 col-lg-2 f-cat-padding'>";
									 $f_cat_array .="<div class='thumbnail leos-category-container'>";
										 $f_cat_array .= "<a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $data['c_id'] . "/" . $fluid->php_clean_string($data['c_name']) . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='thumbnail leos-category-thumbnail' onClick='js_loading_start();'><img class='f-index-img' src='" . $width_height_l['image'] . "' alt='" . $data['c_name'] . "'></a>";
										 $f_cat_array .= "<a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $data['c_id'] . "/" . $fluid->php_clean_string($data['c_name']) . "\" style='padding-top: 10px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='btn leos-category-button f-cat-index-btn' role='button' onClick='js_loading_start();'>" . $data['c_name'] . "</a>";
									 $f_cat_array .= "</div>";
								 $f_cat_array .= "</div>";

							 }
						 }

						 // --> Rentals
						 //$c_images = $fluid->php_process_images(NULL);
						 $width_height_l = $fluid->php_process_image_resize("files/leos_rentals_cart.png", "160", "160", NULL, TRUE);

						 $f_cat_array .= "<div class='col-xs-6 col-sm-4 col-md-3 col-lg-2 f-cat-padding'>";
							 $f_cat_array .="<div class='thumbnail leos-category-container'>";
								 $f_cat_array .= "<a href=\"" . $_SESSION['fluid_uri'] . "Rentals\" target='_blank' onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='thumbnail leos-category-thumbnail'><img class='f-index-img' src='" . $width_height_l['image'] . "' alt='Rentals'></a>";
								 $f_cat_array .= "<a href=\"" . $_SESSION['fluid_uri'] . "Rentals\" target='_blank' style='padding-top: 10px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='btn leos-category-button f-cat-index-btn' role='button'>Rentals</a>";
							 $f_cat_array .= "</div>";
						 $f_cat_array .= "</div>";


						 echo $f_cat_array;
					 ?>
					 </div>
				 </div>
			 </div>
		 </div>
	 </div> <!-- row end -->
 </div>
 <?php	
 	$fluid->php_db_commit();
}
?>
