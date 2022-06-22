<?php
// fluid.item.php
// Michael Rajotte - 2016 Jun
// This page loads a item to view.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

function php_main_fluid_item() {
	require_once("header.php");
	
	// Create a new fluid class module.
	$fluid = new Fluid ();
	$fluid->php_db_begin();

	$fr_p_bundle = NULL;
	$fr_p_id = NULL;
	$fr_bundle_item = NULL;
	$f_p_link = NULL;
	$f_p_not_found = FALSE; // Used to trigger bundle items that should not be active.

	if(isset($_REQUEST['p_id'])) {
		$f_explode = explode("_", $_REQUEST['p_id']);
		//$fr_p_id = $_REQUEST['p_id'];
		if(count($f_explode) > 1) {
			$fr_p_id = $f_explode['0'];
			$fr_p_bundle = $f_explode['1'];
		}
		else
			$fr_p_id = $_REQUEST['p_id'];
	}

	$query_search_stock = " p.p_enable > '0' AND c.c_enable = 1 ";

	if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 2) {
		$query_search_stock = " ((p.p_enable = '1' AND c.c_enable = 1) OR (p.p_enable = '2' AND p.p_stock > 0 AND c.c_enable = 1))";
	}
	
	// --> Lets grab the bundle item first if required.
	if(isset($fr_p_bundle)) {
		$fluid->php_db_query("SELECT p.*, m.*, c.*, (SELECT c2.c_name FROM " . TABLE_CATEGORIES . " c2 WHERE c2.c_id = c.c_parent_id) AS c_parent_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_id = '" . $fluid->php_escape_string($fr_p_bundle) . "' AND " . $query_search_stock . " ORDER BY p_id ASC LIMIT 1");

		if(isset($fluid->db_array[0])) {
			if($fluid->db_array[0]['p_formula_status'] == 1 && ($fluid->db_array[0]['p_formula_operation'] == FORMULA_OPTION_8 || $fluid->db_array[0]['p_formula_operation'] == FORMULA_OPTION_9) && ((strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_formula_discount_date_start'] == NULL && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) )) {
				$f_p_link = "_" . $fluid->db_array[0]['p_id'];

				$fr_bundle_item = $fluid->db_array[0];

				if(isset($fluid->db_array[0]['p_images'])) {
					$p_images_bundle = $fluid->php_process_images($fluid->db_array[0]['p_images']);
				}
				else {
					$p_images_bundle[] = NULL;
				}
			}
			else {
				$f_p_not_found = TRUE;
			}
		}
	}

	//$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, (SELECT c2.c_name FROM " . TABLE_CATEGORIES . " c2 WHERE c2.c_id = c.c_parent_id) AS c_parent_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_id = '" . $fluid->php_escape_string($fr_p_id) . "' AND p.p_enable > 0 HAVING p_zero_status_tmp > 0 ORDER BY p_id ASC LIMIT 1");

	$fluid->php_db_query("SELECT p.*, m.*, c.*, (SELECT c2.c_name FROM " . TABLE_CATEGORIES . " c2 WHERE c2.c_id = c.c_parent_id) AS c_parent_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_id = '" . $fluid->php_escape_string($fr_p_id) . "' AND " . $query_search_stock . " ORDER BY p_id ASC LIMIT 1");

	$fluid->php_db_commit();

	if(isset($fluid->db_array[0]['p_stock'])) {
		$fluid_stock = new Fluid();
		$fluid->db_array[0]['p_stock'] = $fluid_stock->php_process_stock($fluid->db_array[0]);
	}

	if(isset($fluid->db_array[0]['p_images'])) {
		$p_images = $fluid->php_process_images($fluid->db_array[0]['p_images']);
	}
	else {
		$p_images[] = NULL;
	}

	if(isset($p_images_bundle)) {
		// --> Check to see if we are flipping the order of the bundled items.
		if($fr_bundle_item['p_formula_flip'] == 1) {
			$p_images = array_merge($p_images_bundle, $p_images);
		}
		else {
			$p_images = array_merge($p_images, $p_images_bundle);
		}
	}

	// --> The bundle item should not be bundled as the formula link is disabled. Lets prevent this bundle from loading.
	if(isset($fr_p_bundle)) {
		if(isset($fr_bundle_item['p_formula_status'])) {
			if($fr_bundle_item['p_formula_status'] < 1) {
				unset($fluid->db_array[0]);
			}
		}
	}

	$detect = new Mobile_Detect;
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

		$f_item_name = NULL;
		if(isset($fluid->db_array[0])) {
			$f_item_name = $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];

			if(isset($fr_bundle_item)) {
				// --> Check to see if we are flipping the order of the bundled items.
				if($fr_bundle_item['p_formula_flip'] == 1) {
					$f_item_name = $fr_bundle_item['m_name'] . " " . $fr_bundle_item['p_name'];
					if($fr_bundle_item['m_id'] == $fluid->db_array[0]['m_id'])
						$f_item_name .= " w/" . $fluid->db_array[0]['p_name'];
					else
						$f_item_name .= " w/" . $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];
				}
				else {
					if($fr_bundle_item['m_id'] == $fluid->db_array[0]['m_id'])
						$f_item_name .= " w/" . $fr_bundle_item['p_name'];
					else
						$f_item_name .= " w/" . $fr_bundle_item['m_name'] . " " . $fr_bundle_item['p_name'];
				}
			}
		}
		?>


		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags --> ?>
		<meta property="og:url"           content="<?php echo $_SESSION['current_page']; ?>" />
		<meta property="og:type"          content="product" />
		<meta property="og:title"         content="Leos Camera Supply" />
		<meta property="og:description"   content="<?php echo $f_item_name; ?>" />
		<?php
		$img_width_height_fb = $fluid->php_process_image_resize($p_images[0], "600", "600");
		?>
		<meta property="og:image"         content="<?php echo $_SESSION['fluid_uri'] . $img_width_height_fb['image']; ?>" />
		<meta property="fb:app_id"		  content="1655536804738049" />


		<?php if(isset($fluid->db_array[0]) && $f_p_not_found == FALSE) { if(strlen($fluid->db_array[0]['p_seo']) > 0) { echo $fluid->db_array[0]['p_seo']; } if(isset($fr_p_bundle)) { if(strlen($fr_bundle_item['p_seo']) > 0) { echo ". " . $fr_bundle_item['p_seo']; } } } ?>
		<?php
		if(isset($fluid->db_array[0]) && $f_p_not_found == FALSE) {

			if(isset($fr_p_bundle)) {
		?>
			<meta name="description" content="Buy <?php echo $f_item_name . " " . $fr_bundle_item['p_mfgcode'] . " - " . $fluid->db_array[0]['p_mfgcode'] . " " . $fr_bundle_item['p_mfg_number'] . " - " . $fluid->db_array[0]['p_mfg_number']; ?>">
			<meta name="keywords" content="<?php echo $f_item_name . " " . $fr_bundle_item['p_mfgcode'] . " - " . $fluid->db_array[0]['p_mfgcode'] . " " . $fr_bundle_item['p_mfg_number'] . " - " . $fluid->db_array[0]['p_mfg_number'];?>">
			<title>Leos Camera Supply <?php if(isset($fluid->db_array[0])) { echo $f_item_name; } ?></title>
		<?php
			}
			else {
		?>
			<meta name="description" content="Buy <?php echo $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'] . " " . $fluid->db_array[0]['p_mfgcode'] . " " . $fluid->db_array[0]['p_mfg_number']; ?>">
			<meta name="keywords" content="<?php echo $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'] . " " . $fluid->db_array[0]['p_mfgcode'] . " " . $fluid->db_array[0]['p_mfg_number'];?>">
			<title>Leos Camera Supply <?php if(isset($fluid->db_array[0])) { echo $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];} ?></title>
		<?php
			}
		}
		else {
		?>
			<title>Leos Camera Supply> - Product not found.</title>
		<?php
		}
		?>

	<?php
	php_load_pre_header();
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-item.css');?>">

	<?php
		if(isset($fluid->db_array[0]['p_id']) && $f_p_not_found == FALSE) {
	?>
		<link rel="stylesheet" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/photoswipe.css');?>">
		<link rel="stylesheet" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/photoswipe-default-skin/default-skin.css');?>">
		<script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/photoswipe.min.js');?>"></script>
		<script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/photoswipe-ui-default.min.js');?>"></script>
	<?php

		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			if(isset($fluid->db_array[0]['p_id'])) {
				?>
				<script>
					ga('ec:addProduct', {
						'id': '<?php echo $fluid->db_array[0]['p_mfgcode'];?>',
						'name': '<?php echo $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];?>',
						'category': '<?php echo $fluid->db_array[0]['c_name'];?>',
						'brand': '<?php echo $fluid->db_array[0]['m_name'];?>',
						'variant': '<?php echo $fluid->db_array[0]['p_mfgcode'];?>'
					});

					ga('ec:setAction', 'detail');

					ga('send', 'pageview');
				</script>
				<?php
			}
		}
	}
	?>
	</head>

	<body>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8&appId=1655536804738049";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<?php
	php_load_header();
	?>

	<?php
	if(isset($fluid->db_array[0]['p_id']) && $f_p_not_found == FALSE) {
		if($fluid->db_array[0]['p_stock'] < 1) {
			$fluid->db_array[0]['p_enable'] = $fluid->db_array[0]['p_zero_status'];
		}

		// --> Lets see if we need to flip some data around.
		if($fr_bundle_item['p_formula_flip'] == 1) {
			$fluid->db_array[0]['p_desc'] = $fr_bundle_item['p_desc'] . "<h2 class='product-features-heading'>" . $fluid->db_array[0]['p_name'] . "</h2>" . $fluid->db_array[0]['p_desc'];
			$fluid->db_array[0]['p_details'] = $fr_bundle_item['p_details'] . $fluid->db_array[0]['p_details'];
			$fluid->db_array[0]['p_specs'] = $fr_bundle_item['p_specs'] . "<h2 class='product-features-heading'>" . $fluid->db_array[0]['p_name'] . "</h2>" . $fluid->db_array[0]['p_specs'];
			$fluid->db_array[0]['p_inthebox'] = $fr_bundle_item['p_inthebox'] . $fluid->db_array[0]['p_inthebox'];
		}
		else {
			$fluid->db_array[0]['p_desc'] .= "<h2 class='product-features-heading'>" . $fr_bundle_item['p_name'] . "</h2>" . $fr_bundle_item['p_desc'];
			$fluid->db_array[0]['p_details'] .= $fr_bundle_item['p_details'];
			$fluid->db_array[0]['p_specs'] .=  "<h2 class='product-features-heading'>" . $fr_bundle_item['p_name'] . "</h2>" . $fr_bundle_item['p_specs'];
			$fluid->db_array[0]['p_inthebox'] .= $fr_bundle_item['p_inthebox'];
		}
	?>

	<div class="container-fluid">
		<div class="row f-row">
			<div id='f-container-row' class="col-md-7 col-lg-8 f-no-float f-padding-below" style=''>

				<div id='breadcrumbs' style='display: table-cell; vertical-align: middle; padding: 10px 0px 10px 10px; position: absolute; top: 0px;'>
				<?php
					echo "<a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . "\" onClick='js_loading_start();'>Home</a>";

					echo " / <a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $fluid->db_array[0]['c_parent_id'] . "/" . $fluid->php_clean_string($fluid->db_array[0]['c_parent_name']) . "\" onClick='js_loading_start();'>" . $fluid->db_array[0]['c_parent_name'] . "</a>";

					echo " / <a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $fluid->db_array[0]['c_id'] . "/" . $fluid->php_clean_string($fluid->db_array[0]['c_name']) . "\" onClick='js_loading_start();'>" . $fluid->db_array[0]['c_name'] . "</a>";
				?>
				</div>

				<div id="f-gallery" class="f-gallery">
					<?php
					$image_html = NULL;
					$image_indicator = NULL;
					$f_p_array = NULL;
					$img_count = 0;
					foreach($p_images as $key => $value) {
						$f_img_name = str_replace(" ", "_", $fluid->db_array[0]['m_name'] . "_" . $fluid->db_array[0]['p_name'] . "_" . $fluid->db_array[0]['p_mfgcode']);
						$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

						$img_width_height = $fluid->php_process_image_resize($value, "2000", "2000", $f_img_name );
						$img_width_height_m = $fluid->php_process_image_resize($value, "1200", "1200", $f_img_name);
						$img_width_height_s = $fluid->php_process_image_resize($value, "600", "400", $f_img_name);
						$img_width_height_sm = $fluid->php_process_image_resize($value, "400", "200", $f_img_name);
						$img_width_height_xs = $fluid->php_process_image_resize($value, "100", "100");

						$f_p_array[] = "f-image-" . $key;
						$i_key = $key + 1;

						if($i_key > 4)
							$f_style_hide = " f-li-hide";
						else
							$f_style_hide = NULL;

						if($img_width_height_xs['height'] != $img_width_height_xs['width'] && $img_width_height_xs['height'] < 120)
							$f_li_style = " style='display: inline-flex;'";
						else if($img_width_height_xs['height'] != $img_width_height_xs['width'] && $img_width_height_xs['height'] == 120)
							$f_li_style = " style='vertical-align: super;'";
						else
							$f_li_style = NULL;

						// Max display of thumbnails images for a item.
						if($img_count <= FLUID_ITEM_PAGE_MAX_IMAGES) {
							$image_indicator .= "<li class='f-thumb-li" . $f_style_hide . "'" . $f_li_style . "><a id='f-image-" . $key . "' onmouseover=\"JavaScript:this.style.cursor='pointer';\" data-index-key='" . $key . "' data-size='" . $img_width_height['width'] . "x" . $img_width_height['height'] . "' onClick='swiper.slideTo(" . $key . ", 0); document.getElementById(\"f-image-" . $key . "\").click();' data-med='" . $_SESSION['fluid_uri'] . $img_width_height_m['image'] . "' data-med-size='" . $img_width_height_m['width'] . "x" . $img_width_height_m['height'] . "'><img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $img_width_height_xs['image'] . "' alt=\"" . str_replace('"', '', $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name']) . "\" /></a></li>";
						}

						$image_html .= "<div class='swiper-slide swiper-slide-products' style='vertical-align: middle;'><div><a id='f-image-" . $key . "' href='" . $_SESSION['fluid_uri'] . $img_width_height['image'] . "' data-index-key='" . $key . "' data-size='" . $img_width_height['width'] . "x" . $img_width_height['height'] . "' data-med='" . $_SESSION['fluid_uri'] . $img_width_height_m['image'] . "' data-med-size='" . $img_width_height_m['width'] . "x" . $img_width_height_m['height'] . "'><div class='f-img-desktop'><img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $img_width_height_s['image'] . "' style='margin: auto;' alt=\"" . str_replace('"', '', $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name']) . "\" /></div><div class='f-img-mobile'><img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $img_width_height_sm['image'] . "' style='margin: auto;' alt=\"" . str_replace('"', '', $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name']) . "\" /></div></a></div></div>";

						$img_count++;
					}

					$f_data = NULL;
					$f_savings_large = NULL;
					$f_savings_mobile = NULL;
					$f_formula_message = NULL;
					$f_data .= "<div name='f-card-group-align' class='f-group'>";

					// --> Display the rental bag if the item is flagged as rental item.
					if($fluid->db_array[0]['p_rental'] == 1)
						$f_data .= "<div class=\"col-lg-12\" style='padding: 0px;'><a href='" . $_SESSION['fluid_uri'] . "Rentals'><img class='img-responsive f-rental-image-left' src='" . $_SESSION['fluid_uri'] . "files/rental_logo.jpg'></img></a></div>";

					if(isset($fr_p_bundle)) {
						//if($fr_bundle_item['p_formula_operation'] != FORMULA_OPTION_9) {
							// --> Check to see which prices we should be using from each bundle item.
							if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
								$f_data_tmp_price = $fluid->db_array[0]['p_price_discount'];
							}
							else {
								$f_data_tmp_price = $fluid->db_array[0]['p_price'];
							}

							// --> Check to see which prices we should be using from each bundle item.
							if($fr_bundle_item['p_price_discount'] && ((strtotime($fr_bundle_item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fr_bundle_item['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fr_bundle_item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fr_bundle_item['p_discount_date_end'] == NULL) || ($fr_bundle_item['p_discount_date_start'] == NULL && $fr_bundle_item['p_discount_date_end'] == NULL) )) {
								$f_data_bundle_tmp_price = $fr_bundle_item['p_price_discount'];
							}
							else {
								$f_data_bundle_tmp_price = $fr_bundle_item['p_price'];
							}
						//}
						//else {
							//$f_data_tmp_price = $fluid->db_array[0]['p_price'];
							//$f_data_bundle_tmp_price = $fr_bundle_item['p_price'];
						//}

						$f_price_org = $fluid->db_array[0]['p_price'] + $fr_bundle_item['p_price'];
						//$fr_bundle_item['p_price'] = $fr_bundle_item['p_price'] + $fluid->db_array[0]['p_price'];
						// --> Apply the new pricing to the bundle item now.
						$fr_bundle_item['p_price'] = $f_data_tmp_price + $f_data_bundle_tmp_price;

						// --> Apply the math formula.
						$f_formula = $fr_bundle_item['p_formula_math'];

						$f_vars = FORMULA_VARIABLES;

						$f_vars_data = Array($fr_bundle_item['p_price'], $fr_bundle_item['p_price'], $fr_bundle_item['p_stock'], $fr_bundle_item['p_cost'], $fr_bundle_item['p_length'], $fr_bundle_item['p_width'], $fr_bundle_item['p_height'], $fr_bundle_item['p_height']);

						$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

						$parser = new StdMathParser();

						$AST = $parser->parse($f_formula);

						// --> Evaluate the expression.
						$evaluator = new Evaluator();

						$f_value = $AST->accept($evaluator);
						$f_value = $fr_bundle_item['p_price'] - $f_value;
						$f_value = -1 * abs($f_value);

						$fr_bundle_item['p_price_discount'] = $fr_bundle_item['p_price'] + $f_value;

						$f_stock = base64_encode($fluid->php_process_stock_status($fr_bundle_item['p_instore'], $fr_bundle_item['p_stock'], $fr_bundle_item['p_enable'], $fr_bundle_item['p_newarrivalenddate'], $fr_bundle_item['p_preorder'], $fr_bundle_item['p_arrivaltype']));
						$f_stock_bundle = base64_encode($fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fr_bundle_item['p_arrivaltype']));

						$f_stock_zero = $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], 0, $fr_bundle_item['p_enable'], NULL, NULL);

						if($f_stock == $f_stock_bundle) {
							$f_stock_html = base64_decode($f_stock);
						}
						else {
							$f_stock_html = $f_stock_zero;
						}

						$f_data .= "<div>";
							$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $f_stock_html . "</div>";
						$f_data .= "</div>";

						$f_data .= "<div>";
							$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($fluid->php_math_price($f_price_org, $fr_bundle_item['p_price_discount'], NULL, NULL), 2, '.', ',') . "</div>";
						$f_data .= "</div>";

						$f_data .= "<div>";
							$f_data .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
							$f_data .= "<div class='price-instant-savings-value-listing f-price-pad-special'>" . HTML_CURRENCY . number_format($fluid->php_math_savings($f_price_org, $fr_bundle_item['p_price_discount'], NULL)['dollar'], 2, '.', ',') . "</div>";
							$f_data .= "<div class='price-instant-savings-value-listing f-price-pad-special-right'>Original: " . HTML_CURRENCY . number_format($f_price_org, 2, '.', ',') . "</div>";
						$f_data .= "</div>";
					}
					else if(empty($fr_p_bundle) && $fluid->db_array[0]['p_formula_status'] == 1 && ($fluid->db_array[0]['p_formula_operation'] != FORMULA_OPTION_8 && $fluid->db_array[0]['p_formula_operation'] != FORMULA_OPTION_9) && ((strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_formula_discount_date_start'] == NULL && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) )) {
						if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
						//if(isset($fluid->db_array[0]['p_discount_date_end'])) {
							$f_savings_large = "<div class='f-timer-savings'>";
							$f_savings_mobile = "<div class='f-timer-savings-mobile'>";
								$f_savings = "<div style='font-weight: 600; text-align: center;'>Instant savings end in</div>";
								$f_savings .= "<div class='clockdiv savings-countdown-div'>";
									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown days'></span>";
										$f_time = strtotime($fluid->db_array[0]['p_discount_date_end']) - strtotime(date('Y-m-d H:i:s'));
										if($f_time > 172800 || $f_time < 86400)
											$s_day = "DAYS";
										else
											$s_day = "DAY";

										$f_savings .= "<div class='smalltext'>" . $s_day . "</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown hours'></span>";
										$f_savings .= "<div class='smalltext'>HR</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown minutes'></span>";
										$f_savings .= "<div class='smalltext'>MIN</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown seconds'></span>";
										$f_savings .= "<div class='smalltext'>SEC</div>";
									$f_savings .= "</div>";

									// --> This must be the last child for the javascript code to pick up end times. Keep the div hidden.
									$f_savings .= "<div style='display:none;'>" . strtotime(date("F d Y H:i:s")) . "/" . strtotime(date("F d Y H:i:s", strtotime($fluid->db_array[0]['p_discount_date_end']))) . "</div>";
								$f_savings .= "</div>"; // --> clockdiv savings-countdown-div
							$f_savings .= "</div>";

							$f_savings_large .= $f_savings;
							$f_savings_mobile .= $f_savings;
						}
						$f_data .= $f_savings_mobile;
						$f_value_asterik = NULL;

						if(strlen($fluid->db_array[0]['p_formula_math']) > 0 && $fluid->db_array[0]['p_formula_operation'] != FORMULA_OPTION_10 && $fluid->db_array[0]['p_formula_operation'] != FORMULA_OPTION_7) {
							if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
								$p_price = $fluid->db_array[0]['p_price_discount'];

								if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
									$f_reg_discount = FALSE; // --> Set to TRUE if you want to display the Instant savings information when additional savings are available.
								else
									$f_reg_discount = TRUE;

								if($f_reg_discount == TRUE)
									$f_savings_text = "Additional Savings:";
								else
									$f_savings_text = "Instant Savings:";
							}
							else {
								$p_price = $fluid->db_array[0]['p_price'];
								$f_savings_text = "Instant Savings:";
								$f_reg_discount = FALSE;
							}

							// --> Apply the math formula.
							$f_formula = $fluid->db_array[0]['p_formula_math'];

							$f_vars = FORMULA_VARIABLES;

							$f_vars_data = Array($fluid->db_array[0]['p_price'], $p_price, $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_cost'], $fluid->db_array[0]['p_length'], $fluid->db_array[0]['p_width'], $fluid->db_array[0]['p_height'], $fluid->db_array[0]['p_height']);

							$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

							$parser = new StdMathParser();

							$AST = $parser->parse($f_formula);

							// --> Evaluate the expression.
							$evaluator = new Evaluator();

							$f_value = $AST->accept($evaluator);

							if($f_value <= 0)
								$f_value = $p_price;

							if($fluid->db_array[0]['p_formula_message_display'] == 1 && $fluid->db_array[0]['p_formula_message'])
								$f_value_asterik = "<div style='display: inline-block; font-weight: 600; padding-left: 3px;'>*</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($f_value, 2, '.', ',') . $f_value_asterik . "</div>";
							$f_data .= "</div>";

							if($f_reg_discount == TRUE) {
								$f_data .= "<div>";
									$f_data .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
									$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 10px; text-align: center; color: red;'>" . HTML_CURRENCY . number_format($fluid->php_math_savings($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
									$f_data .= "<div class='f-more-price'>";
										$f_data .= "<div class='price-instant-savings-listing price-instant-savings-listing-special' style='display: inline-block; text-align: center;'>Additional Savings:</div>";
										$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 10px; text-align: center; color: red;'>" . HTML_CURRENCY . number_format($p_price - $f_value, 2, '.', ',') . " *</div>";
									$f_data .= "</div>";

									$f_data .= "<div>";
										$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; text-decoration: line-through;'>Original: " . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price'], 2, '.', ',') . "</div>";
										$f_data .= "<div class='f-more-price'><div class='price-instant-savings-value-listing price-instant-savings-listing-special' style='display: inline-block; text-align: center; text-decoration: line-through;'>Savings Price: " . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price_discount'], 2, '.', ',') . "</div></div>";
									$f_data .= "</div>";
								$f_data .= "</div>";
							}
							else {
								$f_data .= "<div>";
									$f_data .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";

									if($f_reg_discount == TRUE)
										$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 10px; text-align: center; color: red;'>" . HTML_CURRENCY . number_format($fluid->php_math_savings($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
									else
										$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 10px; text-align: center; color: red;'>" . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price'] - $f_value, 2, '.', ',') . "</div>";

									$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 20px; text-align: center; text-decoration: line-through;'>Original: " . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price'], 2, '.', ',') . "</div>";
								$f_data .= "</div>";
							}

						}
						else if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
						//else if($fluid->db_array[0]['p_price'] && $fluid->db_array[0]['p_price_discount']) {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($fluid->php_math_price($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'], $fluid->db_array[0]['p_discount_date_start']), 2, '.', ',') . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
								$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 10px; text-align: center; color: red;'>" . HTML_CURRENCY . number_format($fluid->php_math_savings($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
								$f_data .= "<div class='price-instant-savings-value-listing' style='display: inline-block; padding-left: 20px; text-align: center; text-decoration: line-through;'>Original: " . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price'], 2, '.', ',') . "</div>";
							$f_data .= "</div>";
						}
						else if($fluid->db_array[0]['p_price']) {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								//$f_data .= "<div class='price-final-listing' style='display: inline-block; text-align: center;'>You Pay:</div>";
								$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($fluid->php_math_price($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'], $fluid->db_array[0]['p_discount_date_start']), 2, '.', ',') . "</div>";
							$f_data .= "</div>";
						}
						else {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";
						}

						if($fluid->db_array[0]['p_formula_message_display'] == 1 && $fluid->db_array[0]['p_formula_message']) {
							$f_align_message = "display: inline-block;";
							$f_align_message_div = "display: inline-block;";
							$f_formula_message = "<div style='font-size: 75%; padding-top: 12px;'>";
								if(isset($fluid->db_array[0]['p_formula_math'])) {
									if(isset($p_price) && isset($f_value)) {
										$f_savings = $p_price - $f_value;

										if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
											$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block;' class='f-float-special-price-formula'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div>";
										else
											$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'>";
									}
									else if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
										$f_savings = $fluid->db_array[0]['p_price'] - $fluid->db_array[0]['p_price_discount'];

										if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
											$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block;' class='f-float-special-price-formula'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div>";
										else
											$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'>";
									}
									else {
										$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block;'class='f-float-special-price-formula'><div style='display:inline-block;'>*</div></div>";
										//$f_formula_message .= "<div style='" . $f_align_message . " padding-right: 3px;'>" . $f_value_asterik . "</div>";
									}

									$f_formula_message .= "* ";
									$f_formula_message .= "<div style='display: inline-block;'>" . $fluid->db_array[0]['p_formula_message'] . "</div></div></div>";
								}
								else
									$f_formula_message .= "<div style='" . $f_align_message_div . "'>* " . $fluid->db_array[0]['p_formula_message'] . "</div></div>";
						}

					}
					else if($fluid->db_array[0]['p_price_discount'] && ((strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_discount_date_start'] == NULL && $fluid->db_array[0]['p_discount_date_end'] == NULL) )) {
						if(isset($fluid->db_array[0]['p_discount_date_end'])) {
							$f_savings_large = "<div class='f-timer-savings'>";
							$f_savings_mobile = "<div class='f-timer-savings-mobile'>";

								$f_savings = "<div style='font-weight: 600; text-align: center;'>Instant savings end in</div>";
								$f_savings .= "<div class='clockdiv savings-countdown-div'>";
									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown days'></span>";
										$f_time = strtotime($fluid->db_array[0]['p_discount_date_end']) - strtotime(date('Y-m-d H:i:s'));
										if($f_time > 172800 || $f_time < 86400)
											$s_day = "DAYS";
										else
											$s_day = "DAY";

										$f_savings .= "<div class='smalltext'>" . $s_day . "</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown hours'></span>";
										$f_savings .= "<div class='smalltext'>HR</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown minutes'></span>";
										$f_savings .= "<div class='smalltext'>MIN</div>";
									$f_savings .= "</div>";

									$f_savings .= "<div>";
										$f_savings .= "<span class='savings-countdown seconds'></span>";
										$f_savings .= "<div class='smalltext'>SEC</div>";
									$f_savings .= "</div>";

									// --> This must be the last child for the javascript code to pick up end times. Keep the div hidden.
									$f_savings .= "<div style='display:none;'>" . strtotime(date("F d Y H:i:s")) . "/" . strtotime(date("F d Y H:i:s", strtotime($fluid->db_array[0]['p_discount_date_end']))) . "</div>";
								$f_savings .= "</div>"; // --> clockdiv savings-countdown-div
							$f_savings .= "</div>";

							$f_savings_large .= $f_savings;
							$f_savings_mobile .= $f_savings;
						}

						$f_data .= $f_savings_mobile;

						if($fluid->db_array[0]['p_price']) {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($fluid->php_math_price($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'], $fluid->db_array[0]['p_discount_date_start']), 2, '.', ',') . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								$f_data .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
								$f_data .= "<div class='price-instant-savings-value-listing f-price-pad-special'>" . HTML_CURRENCY . number_format($fluid->php_math_savings($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
								$f_data .= "<div class='price-instant-savings-value-listing f-price-pad-special-right'>Original: " . HTML_CURRENCY . number_format($fluid->db_array[0]['p_price'], 2, '.', ',') . "</div>";
							$f_data .= "</div>";
						}
						else {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";
						}
					}
					else {
						if($fluid->db_array[0]['p_price']) {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";

							$f_data .= "<div>";
								//$f_data .= "<div class='price-final-listing' style='display: inline-block; text-align: center;'>You Pay:</div>";
								$f_data .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . HTML_CURRENCY . number_format($fluid->php_math_price($fluid->db_array[0]['p_price'], $fluid->db_array[0]['p_price_discount'], $fluid->db_array[0]['p_discount_date_end'], $fluid->db_array[0]['p_discount_date_start']), 2, '.', ',') . "</div>";
							$f_data .= "</div>";
						}
						else {
							$f_data .= "<div>";
								$f_data .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block;'>" . $fluid->php_process_stock_status($fluid->db_array[0]['p_instore'], $fluid->db_array[0]['p_stock'], $fluid->db_array[0]['p_enable'], $fluid->db_array[0]['p_newarrivalenddate'], $fluid->db_array[0]['p_preorder'], $fluid->db_array[0]['p_arrivaltype']) . "</div>";
							$f_data .= "</div>";
						}
					}

					// --> We have a active formula link. Lets see if we should display a formula link message.
					//if($fluid->db_array[0]['p_formula_status'] == 1 && $fluid->db_array[0]['p_formula_message_display'] == 1 && $fluid->db_array[0]['p_formula_message'] && ((strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fluid->db_array[0]['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fluid->db_array[0]['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) || ($fluid->db_array[0]['p_formula_discount_date_start'] == NULL && $fluid->db_array[0]['p_formula_discount_date_end'] == NULL) ))
						//$f_data .= "<div style='margin-top: 8px; font-size: 85%;'>" . $fluid->db_array[0]['p_formula_message'] . "</div>";
					if(isset($f_formula_message))
						$f_data .= $f_formula_message;

					$f_data .= "<div class=\"col-lg-12\" style='padding: 0px 0px 10px 0px;'>";
						$f_data .= "<span class=\"line-break f-hide-break-desktop\"></span>";
					$f_data .= "</div>";

					$f_data .= "<div name='f-buttons-class' class='f-buttons-class'>";
						$f_data .= "<div class='input-group f-qty-group'>";
							$f_data .= "<span class='input-group-addon input-group-addon-fluid' id='basic-addon1'>QTY</span>";
							$f_data .= "<select id='fluid-cart-qty-" . $fluid->db_array[0]['p_id'] . $f_p_link . "' class='btn-group bootstrap-select f-bootstrap form-control show-menu-arrow show-tick' data-size='5' id='QTY-input' style=''>";
								if(empty($fluid->db_array[0]['p_buyqty'])) {
									if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $fluid->db_array[0]['p_enable'] == 2) {
										if($fluid->db_array[0]['p_stock'] > 0)
											$p_qty = $fluid->db_array[0]['p_stock'];
										else
											$p_qty = 1;
									}
									else
										$p_qty = 99;
								}
								else if($fluid->db_array[0]['p_buyqty'] < 1) {
									if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $fluid->db_array[0]['p_enable'] == 2) {
										if($fluid->db_array[0]['p_stock'] > 0)
											$p_qty = $fluid->db_array[0]['p_stock'];
										else
											$p_qty = 1;
									}
									else
										$p_qty = 99;
								}
								else if($fluid->db_array[0]['p_buyqty'] > $fluid->db_array[0]['p_stock']) {
									if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $fluid->db_array[0]['p_enable'] == 2)
										$p_qty = $fluid->db_array[0]['p_stock'];
									else
										$p_qty = 99;
								}
								else
									$p_qty = $fluid->db_array[0]['p_buyqty'];

								if($p_qty == 0) {
									if($fluid->db_array[0]['p_enable'] == 2)
										$p_qty = $fluid->db_array[0]['p_stock'];
									else if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE)
										$p_qty = $fluid->db_array[0]['p_buyqty'];
									else
										$p_qty = 99;
								}

								if($fluid->db_array[0]['p_stock'] < 1 && $fluid->db_array[0]['p_enable'] == 2)
									$p_qty = 1;

								for($i = 1; $i <= $p_qty; $i++)
									$f_data .= "<option value='" . $i . "'>" . $i . "</option>";
							$f_data .= "</select>";
						$f_data .= "</div>";

						$cart_disabled = "onClick='js_fluid_add_to_cart(this, \"" . $fluid->db_array[0]['p_id'] . $f_p_link . "\");'";
						$f_cart_class = "class='btn btn-lg btn-success btn-block f-btn-max-width'";
						$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to Cart";
						$f_btn_mode = "";

						if($fluid->db_array[0]['p_height'] <= 0 || $fluid->db_array[0]['p_length'] <= 0 || $fluid->db_array[0]['p_width'] <= 0 || $fluid->db_array[0]['p_weight'] <= 0 || FLUID_STORE_OPEN == FALSE || FLUID_PURCHASE_OUT_OF_STOCK == FALSE) {

							if(($fluid->db_array[0]['p_preorder'] == TRUE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
								// --> Do nothing. Preorders will be checked below.
							}
							else if($fluid->db_array[0]['p_preorder'] == FALSE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE) {
								// --> Item is in stock, but not available to be preordered and is not officially launched yet.
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
								$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
								$cart_disabled = "disabled";
							}
							else if($fluid->db_array[0]['p_special_order'] == 1) {
								// --> Do nothing. Special orders will be checked below.
							}
							else if($fluid->db_array[0]['p_stock'] > 0) {
								// --> Do nothing. Item is in stock.
							}
							else
								$cart_disabled = "disabled";

							//if(FLUID_PAYMENT_SANDBOX == FALSE)
								//$cart_disabled = "disabled";
						}
						else if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE && ($fluid->db_array[0]['p_stock'] < 1 || $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE)) {

							if(($fluid->db_array[0]['p_preorder'] == TRUE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
								//  --> Do nothing. Preorders will be checked below.
							}
							else if($fluid->db_array[0]['p_preorder'] == FALSE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE) {
								// --> Item is in stock, but not available to be preordered and is not officially launched yet.
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
								$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
								$cart_disabled = "disabled";
							}
							else if($fluid->db_array[0]['p_special_order'] == 1) {
								// --> Do nothing. Special orders will be checked below.
							}
							else if($fluid->db_array[0]['p_stock'] > 0) {
								// --> Do nothing. Item is in stock.
							}
							else
								$cart_disabled = "disabled";
						}
						else if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE && $fluid->db_array[0]['p_preorder'] == FALSE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE) {
							// --> Item is in stock, but not available to be preordered and is not officially launched yet.
							$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
							$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
							$cart_disabled = "disabled";
						}

						if($fluid->db_array[0]['p_height'] <= 0 || $fluid->db_array[0]['p_length'] <= 0 || $fluid->db_array[0]['p_width'] <= 0 || $fluid->db_array[0]['p_weight'] <= 0 || $fluid->db_array[0]['p_price'] <= 0) {
							$f_cart_class = "href='tel:+16046855331' class='btn btn-lg " . $f_btn_mode . " btn-primary btn-block'";
							$f_cart_message = "<i class='fa fa-phone' aria-hidden='true'></i> Call for more info";
							$cart_disabled = "disabled";

						}

						//if($fluid->db_array[0]['p_stock'] < 1 && $fluid->db_array[0]['p_preorder'] == TRUE || ($fluid->db_array[0]['p_stock'] > 0 && $fluid->db_array[0]['p_preorder'] == TRUE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE)) {
						if(($fluid->db_array[0]['p_preorder'] == TRUE && $fluid->php_item_available($fluid->db_array[0]['p_newarrivalenddate']) == FALSE)) {
							$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-warning btn-block'";
							$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Preorder";

							if(FLUID_PREORDER == FALSE)
								$cart_disabled = "disabled";
						}
						else if($fluid->db_array[0]['p_special_order'] == 1 && $fluid->db_array[0]['p_stock'] < 1) {
							$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-info btn-block'";
							$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Special Order";
						}

						// --> Discontinued item and no longer in stock.
						if($fluid->db_array[0]['p_stock'] < 1 && $fluid->db_array[0]['p_enable'] == 2) {
							$cart_disabled = "disabled";
							$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
							$f_cart_message = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Discontinued";
						}

						$f_data .= "<div style='text-align: center;' name='fluid-button-" . $fluid->db_array[0]['p_id'] . "' id='fluid-button-" . $fluid->db_array[0]['p_id'] . "' class='f-btn-max-width-div' onClick='FluidMenu.button.obj_div = this; FluidMenu.button.obj_div_id=\"" . $fluid->db_array[0]['p_id'] . "\";'>";
							$f_data .= "<a name='fluid-cart-btn-" . $fluid->db_array[0]['p_id'] . $f_p_link . "' id='fluid-cart-btn-" . $fluid->db_array[0]['p_id'] . $f_p_link . "' " . $f_cart_class . " " . $cart_disabled . ">" . $f_cart_message . "</a>";
						$f_data .= "</div>";
					$f_data .= "</div>";	// f_buttons_class
				$f_data .= "</div>"; // $f_card_group_align

				if(isset($fluid->db_array[0]['m_images'])) {
					$m_images = $fluid->php_process_images($fluid->db_array[0]['m_images']);

					if($m_images[0] != FOLDER_FILES . IMG_NO_IMAGE) {
						$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "140", "140");
						$m_img_width_height_m = $fluid->php_process_image_resize($m_images[0], "100", "100");

					echo "<div>";
						echo "<div class='m-logo-item'>";
							echo "<img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $m_img_width_height['image'] . "' alt=\"" . str_replace('"', '', $fluid->db_array[0]['m_name']) . "\" />";
						echo "</div>";

						echo "<div class='m-logo-item-mobile'>";
							echo "<img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $m_img_width_height_m['image'] . "' alt=\"" . str_replace('"', '', $fluid->db_array[0]['m_name']) . "\" />";
						echo "</div>";

						echo $f_savings_large;
					echo "</div>";
					}
				}

				echo "<div id='swiper-container' class='swiper-container swiper-container-products'>";

					echo "<div class='swiper-wrapper'>";
						echo $image_html;
					echo "</div>";

					if($img_count == 1)
						$f_style_padding = " f-page-padding";
					else
						$f_style_padding = NULL;

					echo "<div class='swiper-pagination swiper-pagination-products" . $f_style_padding . "' style='position: static;'></div>";

				echo "</div>";


				if(!empty($image_indicator)) {
					if($img_count < 2)
						$f_img_hide_class = " f-thumb-hide";
					else
						$f_img_hide_class = NULL;

					echo "<div class='f-thumbs" . $f_img_hide_class . "'><ul class='carousel-indicators-leos'>";
						echo $image_indicator;
					echo "</ul></div>";
				}
				?>

			  </div>
			</div>	<!-- column end -->

			<div id='f-row-right' class="col-md-5 col-lg-4 product-info-container f-no-float-right">
				<div name='container-f-item'>

					<?php
					if(empty($fluid->db_array[0]['p_name']))
						$ft_name = $fluid->db_array[0]['p_id'];
					else if(isset($fr_p_bundle))
						$ft_name = $f_item_name;
					else if(empty($fluid->db_array[0]['p_mfg_number']) || $fluid->db_array[0]['p_namenum'] == FALSE)
						$ft_name = $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];
					else if(empty($fluid->db_array[0]['p_mfg_number']) && $fluid->db_array[0]['p_namenum'] == TRUE)
						$ft_name = $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'];
					else
						$ft_name = $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_mfg_number'] . " " . $fluid->db_array[0]['p_name'];
					?>

					<div class="row product-name-container">
						<div class="col-lg-12">
							<h1 class='fluid-item-name-div'><?php echo $ft_name; ?></h1>
							<div style='display: inline-block; font-size: 10px; font-weight: 300;'>UPC #
							<?php
								echo $fluid->db_array[0]['p_mfgcode'];

								if(isset($fr_p_bundle))
									echo " + " . $fr_bundle_item['p_mfgcode'];
							?>
							</div>
							<?php
								if(isset($fluid->db_array[0]['p_mfg_number']))
									echo "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 9px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $fluid->db_array[0]['p_mfg_number'];

								if(isset($fr_p_bundle))
									echo " + " . $fr_bundle_item['p_mfg_number'];

									echo "</div>";

							?>
						</div>
					</div>  <!-- product-name-container row end -->

					<div class="row price-container">

					<?php
					echo $f_data;

					// Bug in ios, facebook share button size when set to large doesn't always appear large. Sometimes OSX Safari?
					if($detect->isiOS())
						$f_icon_size = "small";
					else
						$f_icon_size = "large";

					$share_data = "<div class='f-social-share-div'>";
							$share_data .= "<div style='display: inline-block; vertical-align: top;'>";
								$share_data .= "<div class=\"fb-share-button\" data-href=\"https://developers.facebook.com/docs/plugins/\" data-layout=\"button\" data-size=\"" . $f_icon_size . "\" data-mobile-iframe=\"false\"><a class=\"fb-xfbml-parse-ignore\" target=\"_blank\" href=\"https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse\">Share</a></div>";
							$share_data .= "</div>";

							$share_data .= "<div style='display: inline-block; padding-left: 20px; vertical-align: top;'>";
								$share_data .= "<a href=\"" . $_SESSION['current_page'] . "\" class=\"twitter-share-button\" data-size=\"" . $f_icon_size . "\" data-text=\"" . $fluid->db_array[0]['m_name'] . " " . $fluid->db_array[0]['p_name'] . "\" data-hashtags=\"photography\" data-show-count=\"false\">Tweet</a><script async src=\"//platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>";
							$share_data .= "</div>";
					$share_data .= "</div>";

					echo $share_data;
					?>

					<?php if(!empty($fluid->db_array[0]['p_details']) || !empty($fluid->db_array[0]['p_inthebox'])) {
					?>
						<div class="col-lg-12" style='padding-top: 10px; padding-bottom: 10px;'>
							<span class="line-break"></span>
						</div>
					<?php
					}
					else
						echo "<div style='padding: 20px 0px 0px 15px;'></div>";
					?>
					</div>  <!-- price-container row end -->

					<?php
					if(!empty($fluid->db_array[0]['p_details'])) {
						if(strlen(strip_tags($fluid->db_array[0]['p_details'])) > 0) {
						?>
						<div id='specs-container-item' class="row specs-container">
							<div class="col-lg-12"><h5>PRODUCT DETAILS</h5></div>
							<div id='f-specs-side' class="col-lg-12">
								<?php
								//$string = strip_tags($fluid->db_array[0]['p_inthebox']);
								$d_array = explode("<li>", $fluid->db_array[0]['p_details']);
								$d_data = NULL;
								$d_i = 1;
								$d_break = FALSE;
								$d_tmp = "
								<ul>
								</ul>";

								foreach($d_array as $key => $d_string) {
									$d_string = str_replace("<ul>", "", $d_string);
									$d_string = str_replace("</ul>", "", $d_string);

									if($d_i > 3) {
										$d_data .= "</ul>";
										$d_break = TRUE;
										break;
									}

									if($d_string != "<ul>" && strlen($d_string) > 1) {
										if($d_i > 3)
											$d_i_hide = " class='f-li-hide-special'";
										else
											$d_i_hide = NULL;

										$d_i++;

										$d_data .= "<li" . $d_i_hide . ">" . $d_string;
									}
								}
								$d_data = "<ul>" . $d_data . "</ul>";
								$d_data_org = $fluid->db_array[0]['p_details'];

								/*
								foreach($d_array as $key => $d_string) {
									if($d_i > 3) {
										$d_data .= "</ul>";
										$d_break = TRUE;
										break;
									}

									if($d_string != "<ul>") {
										if($d_i > 3)
											$d_i_hide = " class='f-li-hide-special'";
										else
											$d_i_hide = NULL;

										$d_i++;

										$d_data .= "<li" . $d_i_hide . ">" . $d_string;
									}
									else
										$d_data .= $d_string;
								}
								*/

								if($d_break == TRUE) {
									$d_data .= "<div style='padding: 5px 0px 20px 30px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"f-specs-side\").innerHTML = Base64.decode(f_specs_more);'><a>Read more ...</a></div>";

									$d_data_org .= "<div style='padding: 5px 0px 20px 30px;'' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"f-specs-side\").innerHTML = Base64.decode(f_specs_less);'><a>Show less ...</a></div>";
								}

								?>
								<span><?php echo $d_data; ?></span>
							</div>
							<script>
								var f_specs_more = "<?php echo base64_encode($d_data_org); ?>";
								var f_specs_less = "<?php echo base64_encode($d_data); ?>";
							</script>
						</div>	<!-- specs-container row end -->
						<?php
						}
					} // Details
					?>

					<?php
					if(!empty($fluid->db_array[0]['p_inthebox'])) {
						if(strlen(strip_tags($fluid->db_array[0]['p_inthebox'])) > 0) {
						?>
						<div id='specs-container-item' class="row specs-container f-hide-mobile">
							<div class="col-lg-12"><h5>WHATS IN THE BOX</h5></div>
							<div id='f-inbox-side' class="col-lg-12">
								<?php

								//substr_count($fluid->db_array[0]['p_inthebox'], "<li>");

								$s_array = explode("<li>", $fluid->db_array[0]['p_inthebox']);
								$s_data = NULL;
								$s_i = 1;
								$s_break = FALSE;
								$s_tmp = "
								<ul>
								</ul>";

								foreach($s_array as $key => $s_string) {
									$s_string = str_replace("<ul>", "", $s_string);
									$s_string = str_replace("</ul>", "", $s_string);

									if($s_i > 3) {
										$s_data .= "</ul>";
										$s_break = TRUE;
										break;
									}

									if($s_string != "<ul>" && strlen($s_string) > 1) {
										if($s_i > 3)
											$s_i_hide = " class='f-li-hide-special'";
										else
											$s_i_hide = NULL;

										$s_i++;

										$s_data .= "<li" . $s_i_hide . ">" . $s_string;
									}
									//else
										//$s_data .= $s_string;
								}
								$s_data = "<ul>" . $s_data . "</ul>";
								$s_data_org = $fluid->db_array[0]['p_inthebox'];

								if($s_break == TRUE) {
									$s_data .= "<div style='padding: 5px 0px 20px 30px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"f-inbox-side\").innerHTML = Base64.decode(f_inbox_more);'><a>Read more ...</a></div>";

									$s_data_org .= "<div style='padding: 5px 0px 20px 30px;'' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"f-inbox-side\").innerHTML = Base64.decode(f_inbox_less);'><a>Show less ...</a></div>";
								}
								?>
								<span><?php echo $s_data; ?></span>
							</div>
							<script>
								var f_inbox_more = "<?php echo base64_encode($s_data_org); ?>";
								var f_inbox_less = "<?php echo base64_encode($s_data); ?>";
							</script>
						</div>	<!-- specs-container row end -->
						<?php
						}
					} // whats in the box
					?>
				</div>

			</div>	<!-- column end -->

		</div>	<!-- #row end -->

<script>
	var swiper = new Swiper('.swiper-container-products', {
		pagination: '.swiper-pagination-products',
		paginationClickable: true,
		slidesPerView: 1,
		spaceBetween: 50,
		autoResize: true,
		resizeReInit: true
	});
</script>

		<div id='fluid-breadcrumb-div-hidden'></div>
		<div id='fluid-filter-div-hidden' style='position: relative;'></div>

		<?php
			$f_bar_hide = 0;
			$b_desc = FALSE;
			$b_specs = FALSE;
			$b_inbox = FALSE;
			$b_link_data = FALSE;

			if(empty($fluid->db_array[0]['p_desc'])) {
				$b_desc = TRUE;
				$f_bar_hide++;
			}

			if(empty($fluid->db_array[0]['p_specs'])) {
				$b_specs = TRUE;
				$f_bar_hide++;
			}

			if(empty($fluid->db_array[0]['p_inthebox'])) {
				$b_inbox = TRUE;
				$f_bar_hide++;
			}

			if(isset($fluid->db_array[0]['p_category_items_data'])) {
				$f_link_data = json_decode($fluid->db_array[0]['p_category_items_data']);

				if(is_array($f_link_data)) {
					if(count($f_link_data) > 0) {
						$b_link_data = TRUE;
					}
					else {
						$f_bar_hide++;
					}
				}
				else {
					$f_bar_hide++;
				}
			}

			if($f_bar_hide > 3)
				$f_row_hide = " style='display: none !important; '";
			else if($f_bar_hide > 2)
				$f_row_hide = " style='border-top: 16px solid #347AB6;'";
			else
				$f_row_hide = NULL;

			$f_have_recommended_accessories = FALSE; // --> Used to reset the top margin for scrolling and having the menu bar stick.
			$f_limit = 0;
			$f_limit_rec = 0;
			if(isset($fluid->db_array[0]['p_category_items_data'])) {
				$f_link_data = json_decode($fluid->db_array[0]['p_category_items_data']);
				$f_trending_class = "f-trending-div";
				$f_swiper_array = NULL;
				$where = "AND p_id IN (";
				$where_recommended = $where;
				$i = 0;
				$f_proceed = false;

				if(is_array($f_link_data)) {
					if(count($f_link_data) > 0) {
						$f_proceed = true;
					}
				}

				if($f_proceed == TRUE) {
					$f_have_recommended_accessories = TRUE;
					$fluid_rec_link = new Fluid();
					$fluid_rec_link->php_db_begin();

					$i_tmp = 0;
					// We are only showing items from the first accessory category for recommended ones by request.
					foreach($f_link_data as $f_data) {
						if(count($f_data->sub_filters) > 0) {
							$f_items_swiper_html = NULL;

							foreach($f_data->sub_filters as $f_items) {
								if($i != 0) {
									$where .= ", ";

									if($i_tmp == 0) {
										$where_recommended .= ", ";
									}
								}

								$where .= $fluid_rec_link->php_escape_string($f_items->p_id);

								if($i_tmp == 0) {
									$where_recommended .= $fluid_rec_link->php_escape_string($f_items->p_id);
								}

								$i++;
							}
						}

						$i_tmp++;
					}

					$f_limit = $i;

					$where .= ") ";
					$where_recommended .= ") ";

					if($i > 0) {
						//$fluid_rec_link->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where . " HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");
						$fluid_rec_link->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where_recommended . " ORDER BY p_sortorder ASC LIMIT 0,1000");

						$fluid_rec_link->php_db_commit();

						if(isset($fluid_rec_link->db_array)) {
							shuffle($fluid_rec_link->db_array);
							$f_items_swiper_rec_html = NULL;
							?>

							<div class="row" style='background-color: #e8ecec; border-top: 1px solid rgba(0, 0, 0, .15);'>

							<?php
							foreach($fluid_rec_link->db_array as $data) {
								if($f_limit == 10)
									break;

								$f_items_swiper_rec_html .= "<div class=\"swiper-slide swiper-slide-rec\">";
									$f_items_swiper_rec_html .= "<div class=\"trending-product\">";

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

											$f_items_swiper_rec_html .= "<div class=\"thumbnail trending-product-thumbnail\">";
												$f_items_swiper_rec_html .= "<div style='display: block; min-height: 260px;'><div style='vertical-align: middle;'>";
												$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
												$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

												$p_images = $fluid_rec_link->php_process_images($data['p_images']);
												$width_height_l = $fluid_rec_link->php_process_image_resize($p_images[0], "250", "250", $f_img_name);
												$f_items_swiper_rec_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_rec_link->php_clean_string($ft_mfgcode) . "/" . $fluid_rec_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><img class='img-responsive trending-product-image' src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img></a>";

												$f_items_swiper_rec_html .= "<div class=\"caption\">";
													$f_items_swiper_rec_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_rec_link->php_clean_string($ft_mfgcode) . "/" . $fluid_rec_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-manufacturer\">" . $fluid_rec_link->php_clean_string($data['m_name']) . "</h6></a>";

													$f_items_swiper_rec_html .= "<a class='f-a-link-default' style='width: 100%;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid_rec_link->php_clean_string($ft_mfgcode) . "/" . $fluid_rec_link->php_clean_string($ft_name) . "\" onClick='js_loading_start();'><h6 class=\"trending-product-heading-name\">";
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
													$f_items_swiper_rec_html .= $ft_name . "</h6></a>";

													$f_items_swiper_rec_html .= "<div class=\"trending-product-heading-price-container\">";
													if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
														$f_items_swiper_rec_html .= "<span class=\"trending-product-heading-price price-old\">" . HTML_CURRENCY . number_format($data['p_price'], 2, '.', ',') . "</span>";
														$f_items_swiper_rec_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid_rec_link->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
													}
													else {
														$f_items_swiper_rec_html .= "<span class=\"trending-product-heading-price price-current\">" . HTML_CURRENCY . number_format($fluid_rec_link->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</span>";
													}

														$f_items_swiper_rec_html .= "<div style='display: none;'><select id='fluid-cart-qty-" . $data['p_id'] . "' class='btn-group bootstrap-select form-control show-menu-arrow show-tick' style='display: none;'><option value='1'>1</option></select></div>";
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


														//$f_items_swiper_rec_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' class='btn btn-success btn-block' " . $f_disabled_style . " onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . "\");'><span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to cart</button></div></div>";

														$f_items_swiper_rec_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' " . $f_cart_class . " " . $f_disabled_style . " " . $cart_disabled . ">" . $f_cart_message . "</button></div></div>";

													$f_items_swiper_rec_html .= "</div>";
												$f_items_swiper_rec_html .= "</div>";
												$f_items_swiper_rec_html .= "</div></div>"; // empty div delete.
											$f_items_swiper_rec_html .= "</div>";
									$f_items_swiper_rec_html .= "</div>";
								$f_items_swiper_rec_html .= "</div>";

								$f_limit_rec++;
							}

							if(isset($f_items_swiper_rec_html) && $f_limit_rec > 3) {
							?>
								<style>
								.swiper-container-rec {
									width: 100%;
									margin: auto;
								}

								.swiper-slide-rec {
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
										<script>
											function f_open_accessories() {
												$('.nav-pills a[href="#accessories"]').tab('show');
											}
										</script>

										<div class="col-sm-12">
											<div class='f-trending-div' style='display: inline-block;'>Recommended Accessories</div><div class='access-btn-hide'><button onmouseover='JavaScript:this.style.cursor="pointer"'; onClick='f_open_accessories(); js_fluid_scroll_to_dom("fluid-filter-div-hidden");' class='btn btn-info btn-sm' style='margin-bottom: 6px;'><i class="fa fa-shopping-cart" aria-hidden="true"></i> View More</button></div>
										</div>

										<div class="swiper-container swiper-container-rec">
											<div class="swiper-wrapper">
												<?php echo $f_items_swiper_rec_html; ?>
											</div>
											<div class="swiper-pagination swiper-pagination-rec" style='position: static;'></div>
											<!-- Add Arrows -->
											<div class="swiper-button-next"></div>
											<div class="swiper-button-prev"></div>
										</div>

									</div>
								</div>

								<script>
									var swiper_rec = new Swiper('.swiper-container-rec', {
										pagination: '.swiper-pagination-rec',
										nextButton: '.swiper-button-next',
										prevButton: '.swiper-button-prev',
										paginationClickable: true,
										slidesPerView: 5,
										spaceBetween: 50,
										loop: true,
										autoplay: 12000,
										observer: true, <?php // --> Forces it to render correctly while hidden. ?>
										observeParents: true, <?php // --> Forces it to render correctly while hidden.?>
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
							?>

							</div>
							<?php
						}
					}

				}
			}

			if($f_have_recommended_accessories == TRUE && $f_limit_rec > 3)
				$f_scroll_to_div = "fluid-filter-div-hidden";
			else
				$f_scroll_to_div = "specs-container-item";
		?>

		<div class="row"<?php echo $f_row_hide;?>>
			<div id="item-tab-menu" style='z-index: 900;'>
				<ul class="nav nav-pills nav-pills-fluid">
					<?php
						if(empty($fluid->db_array[0]['p_desc'])) {
							$desc_hide = "style='display: none !important;'";
						}
						else {
							if($f_bar_hide > 3)
								$desc_hide = "class='active' style='display: none !important;'";
							else
								$desc_hide = "class='active'";
						}
					?>
					<li <?php echo $desc_hide; ?>><a href="#overview" data-toggle="tab" onClick='js_fluid_scroll_to_dom("<?php echo $f_scroll_to_div;?>");'>Overview</a></li>

					<?php
						if(empty($fluid->db_array[0]['p_specs'])) {
							$details_hide = " style='display: none !important;'";
						}
						else {
							if(strlen(strip_tags($fluid->db_array[0]['p_specs'])) < 1)
								$details_hide = " style='display: none !important;'";
							else if($f_bar_hide > 1) {
								if($b_desc == TRUE)
									$details_hide = "class='active' style='display: none !important;'";
								else
									$details_hide = NULL;
							}
							else
								$details_hide = NULL;
						}

					?>
					<li <?php echo $details_hide; ?>><a href="#specs" data-toggle="tab" onClick='js_fluid_scroll_to_dom("<?php echo $f_scroll_to_div;?>");'>Specs</a></li>
					<?php
						if(empty($fluid->db_array[0]['p_inthebox']))
							$style_hide = " style='display: none !important;'";
						else {
							if($f_bar_hide > 1) {
								if($b_desc == TRUE && $b_specs == TRUE)
									$style_hide = "class='active' style='display: none !important;'";
								else
									$style_hide = NULL;
							}
							else
								$style_hide = NULL;
						}
					?>
					<li class='f-hide-desktop'<?php echo $style_hide; ?>><a href="#whatsinthebox" data-toggle="tab" onClick='js_fluid_scroll_to_dom("<?php echo $f_scroll_to_div;?>");'>Included</a></li>

					<?php
						if($b_link_data == FALSE || $f_limit <= 4)
							$style_hide = " style='display: none !important;'";
						else {
							//if($f_bar_hide > 1) {
								//if($b_desc == TRUE && $b_specs == TRUE)
									//$style_hide = "class='active' style='display: none !important;'";
							//}
							//else
								$style_hide = NULL;

						}
					?>
					<li <?php echo $style_hide; ?>><a id='f_accessories_id' href="#accessories" data-toggle="tab" onClick='js_fluid_scroll_to_dom("<?php echo $f_scroll_to_div;?>");'>Accessories</a></li>


				</ul>
			</div>

			<div id='tab-content' class="tab-content tab-content-fluid row-content-container">

				<div class="tab-pane fade in active" id="overview">
					<p><?php echo utf8_decode(utf8_encode($fluid->db_array[0]['p_desc'])); ?></p>
				</div>

				<div class="tab-pane fade" id="specs">
					<p><?php

						$f_specs = html_entity_decode(utf8_decode(utf8_encode($fluid->db_array[0]['p_specs'])));
						$f_specs = str_replace("<h2>", "<h2 class='product-features-heading'>", $f_specs);
						$f_specs = str_replace('class="table table-striped"', 'class="table table-striped" style="width: 100%; table-layout: fixed;"', $f_specs);

						echo $f_specs;

						/*
						if(isset($fluid->db_array[0]['p_specs'])) {
							$doc = new DOMDocument();
							$doc->loadHTML($fluid->db_array[0]['p_specs']);

							$f_data_tmp = $doc->getElementsByTagName('table');
							$f_html = NULL;
							for ($i = 0; $i < $f_data_tmp->length; $i++) {
								$f_table = $f_data_tmp->item($i);

								if($f_table->getAttribute('title') == 'Additional') {
									$patterns = array();
									$patterns[0] = '/<table[^>]*>/';
									$patterns[1] = '/<\/table>/';
									$replacements = array();
									$replacements[2] = '';
									$replacements[1] = '';

									$f_tmp = print_r($doc->saveXML($f_table), TRUE);
									$f_tmp = preg_replace($patterns, $replacements, $f_tmp);

									$patterns = array();
									$patterns[0] = '/<p[^>]*>/';
									$patterns[1] = '/<\/p>/';
									$replacements = array();
									$replacements[2] = '';
									$replacements[1] = '';
									$f_tmp = preg_replace($patterns, $replacements, $f_tmp);
									$f_tmp = trim($f_tmp);

									if(count($f_tmp) < 2)
										$f_table->parentNode->removeChild($f_table);
								}
							}

							$f_html = print_r($doc->saveXML(), TRUE);
							$f_html = str_replace("<h2>Additional Information</h2>", "", $f_html);

							$f_specs = html_entity_decode(utf8_decode(utf8_encode($f_html)));
							$f_specs = str_replace("<h2>", "<h2 class='product-features-heading'>", $f_specs);
							$f_specs = str_replace('class="table table-striped"', 'class="table table-striped" style="width: 100%; table-layout: fixed;"', $f_specs);

							echo $f_specs;
						}
						*/
						//echo utf8_decode(utf8_encode($fluid->db_array[0]['p_specs']));
					?></p>
				</div>

				<div class="tab-pane fade" id="whatsinthebox">
					<p><?php echo utf8_decode(utf8_encode($fluid->db_array[0]['p_inthebox'])); ?></p>
				</div>

				<div class="tab-pane fade" id="accessories">
					<p>
						<?php
							if(isset($fluid->db_array[0]['p_category_items_data'])) {
								$f_link_data = json_decode($fluid->db_array[0]['p_category_items_data']);
								$f_trending_class = "f-trending-div";
								$f_swiper_array = NULL;
								$f_proceed = false;

								if(is_array($f_link_data)) {
									if(count($f_link_data) > 0) {
										$f_proceed = true;
									}
								}

								if($f_proceed == TRUE) {
									$fluid_link = new Fluid();
									$fluid_link->php_db_begin();

									foreach($f_link_data as $f_data) {
										if(count($f_data->sub_filters) > 0) {
											$f_items_swiper_html = NULL;

											$where = "AND p_id IN (";
											$i = 0;
											foreach($f_data->sub_filters as $f_items) {
												if($i != 0)
													$where .= ", ";

												$where .= $fluid_link->php_escape_string($f_items->p_id);

												$i++;

											}
											$where .= ") ";

											if($i > 0) {
												//$fluid_link->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where . " HAVING p_zero_status_tmp > 0 ORDER BY p_sortorder ASC LIMIT 0,1000");
												$fluid_link->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND p.p_price IS NOT NULL " . $where . " ORDER BY p_sortorder ASC LIMIT 0,1000");

												if(isset($fluid_link->db_array)) {
													shuffle($fluid_link->db_array);
													$i_p_count = 0;
													foreach($fluid_link->db_array as $data) {
														$f_items_swiper_html .= "<div class=\"swiper-slide swiper-slide-" . $f_data->filter_order . "\">";
															$f_items_swiper_html .= "<div class=\"trending-product\">";

													$i_p_count++;

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
																				if(FLUID_STORE_OPEN == FALSE)
																					$f_disabled_style = "disabled";
																				else
																					$f_disabled_style = NULL;

																					$f_btn_mode = "";
																					$cart_disabled = "onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . $f_p_link . "\");'";
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
																						$f_cart_class = "href='tel:+16046855331' class='btn btn-lg " . $f_btn_mode . " btn-primary btn-block'";
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

																				$f_items_swiper_html .= "<div style='margin-top: 5px; text-align: center; width: 100%'><div name='fluid-button-" . $data['p_id'] . "' id='fluid-button-" . $data['p_id'] . "' style='width: 80%; max-width: 180px; display: inline-block;'><button name='fluid-cart-btn-" . $data['p_id'] . "' id='fluid-cart-btn-" . $data['p_id'] . "' " . $f_cart_class . " " . $f_disabled_style . " " . $cart_disabled . ">" . $f_cart_message . "</button></div></div>";

																			$f_items_swiper_html .= "</div>";
																		$f_items_swiper_html .= "</div>";
																		$f_items_swiper_html .= "</div></div>"; // empty div delete.
																	$f_items_swiper_html .= "</div>";
															$f_items_swiper_html .= "</div>";
														$f_items_swiper_html .= "</div>";
													}

													if(isset($f_items_swiper_html) && $i_p_count > 3) {
														?>
															<style>
															.swiper-container-<?php echo $f_data->filter_order; ?> {
																width: 100%;
																margin: auto;
															}

															.swiper-slide-<?php echo $f_data->filter_order; ?> {
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
																			<div class='<?php echo $f_trending_class; $f_trending_class = 'f-trending-div-font';?>'><?php echo base64_decode($f_data->filter_name);?></div>
																		</div>

																	<div class="swiper-container swiper-container-<?php echo $f_data->filter_order; ?>">
																		<div class="swiper-wrapper">
																			<?php echo $f_items_swiper_html; ?>
																		</div>
																		<div class="swiper-pagination swiper-pagination-<?php echo $f_data->filter_order; ?>" style='position: static;'></div>
																		<!-- Add Arrows -->
																		<div class="swiper-button-next"></div>
																		<div class="swiper-button-prev"></div>
																	</div>

																</div>
															</div>

															<script>
																var swiper_<?php echo $f_data->filter_order;?> = new Swiper('.swiper-container-<?php echo $f_data->filter_order; ?>', {
																	pagination: '.swiper-pagination-<?php echo $f_data->filter_order; ?>',
																	nextButton: '.swiper-button-next',
																	prevButton: '.swiper-button-prev',
																	paginationClickable: true,
																	slidesPerView: 5,
																	spaceBetween: 50,
																	loop: true,
																	autoplay: 12000,
																	observer: true, <?php // --> Forces it to render correctly while hidden. ?>
																	observeParents: true, <?php // --> Forces it to render correctly while hidden.?>
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
											}
										}
									}

									$fluid_link->php_db_commit();
								}
							}
						?>
					</p>
				</div>

			</div>

		</div>  <!-- tab row end -->

	</div> <!-- container end -->

	<?php
	$f_detect = TRUE;

	if($detect->isiOS()) {
		if($detect->isTablet())
			$f_detect = TRUE;
	}
	else
		$f_detect = TRUE;
	?>

	<style>
		.fluid-filter-fixed-position {
			position: fixed !important;
			<?php
			if(FLUID_NAVBAR_PIN == TRUE) {
				if($f_detect == TRUE)
					echo "top: 56px !important;";
				else
					echo "top: 0px !important;";
			}
			else
				echo "top: 0px !important;";
			?>
		}

		@media (max-width: 599px) {
			.fluid-filter-fixed-position {
				left: 0;
				right: 0;
				width: 94vw;
				margin: auto;
			}
		}

		@media (max-width: 767px) {
			.fluid-filter-fixed-position {
				left: 0;
				right: 0;
				width: auto;
				margin: auto;
			}
		}
	</style>

<script>
FluidListScroll = 0;
		$(function() {
			window.onresize = function(event) {
				swiper.update;
			}

			<?php
			if($detect->isMobile() && !$detect->isTablet()) {
				// Do nothing.
			}
			else {
			?>
				$(window).scroll(function() {
					var f_filters = document.getElementById('item-tab-menu');

					if(f_filters != null) {
						var f_filter_pos_y = $('#fluid-breadcrumb-div-hidden').offset().top - $(window).scrollTop();

						<?php
						if(FLUID_NAVBAR_PIN == TRUE) {
							if($f_detect == TRUE)
								if($f_have_recommended_accessories == TRUE && $f_limit > 3)
									echo "var f_fixed_pos = -358;";
								else
									echo "var f_fixed_pos = 57;";
							else
								echo "var f_fixed_pos = -57;";
						}
						else {
							if($f_detect == TRUE) {
								if($f_have_recommended_accessories == TRUE && $f_limit > 3)
									echo "var f_fixed_pos = -415;";
								else
									echo "var f_fixed_pos = 0;";
							}
							else
								echo "var f_fixed_pos = 0;";
						}
						?>

						if(f_filter_pos_y <= f_fixed_pos) {
							if(FluidListScroll == 0) {
								f_filters.className = f_filters.className.replace( /(?:^|\s)fluid-filter-fixed-position(?!\S)/g , '' );
								f_filters.className += " fluid-filter-fixed-position";

								var f_viewport_size = js_viewport_size()['width'];

								if(f_viewport_size < 768)
									if(f_viewport_size < 600)
										$('#fluid-filter-div-hidden').css({'margin-top' : '50px'});
									else
										$('#fluid-filter-div-hidden').css({'margin-top' : '50px'});
								else
									$('#fluid-filter-div-hidden').css({'margin-top' : '50px'});

								FluidListScroll = 1;
							}
						}
						else {
							if(FluidListScroll == 1) {
								f_filters.className = f_filters.className.replace( /(?:^|\s)fluid-filter-fixed-position(?!\S)/g , '' );
								$('#fluid-filter-div-hidden').css({'margin-top' : '0px'});

								FluidListScroll = 0;
							}
						}
					}
				});
			<?php
			}
			?>
		});
</script>

<script>
(function() {
		var initPhotoSwipeFromDOM = function(gallerySelector) {
			var parseThumbnailElements = function(el) {
			   <?php //var thumbElements = el.childNodes; ?>
				<?php

				echo "var thumbElements = [";

				$i = 0 ;
				foreach($f_p_array as $f_p_data) {

					if($i > 0)
						echo ', "' . $f_p_data . '"';
					else
						echo '"' . $f_p_data . '"';

					$i++;
				}

				echo "];";

			    ?>

			    var numNodes = thumbElements.length,
			        items = [],
			        el,
			        childElements,
			        thumbnailEl,
			        size,
			        item;

			    for(var i = 0; i < numNodes; i++) {
					el = document.getElementById(thumbElements[i]);
			        <?php //el = thumbElements[i]; ?>

			        <?php // include only element nodes  ?>
			        if(el.nodeType !== 1) {
			          continue;
			        }

			        childElements = el.children;

			        size = el.getAttribute('data-size').split('x');

			        <?php // create slide object ?>
			        item = {
						src: el.getAttribute('href'),
						w: parseInt(size[0], 10),
						h: parseInt(size[1], 10),
						author: el.getAttribute('data-author')
			        };

			        item.el = el; <?php // save link to element for getThumbBoundsFn ?>

			        if(childElements.length > 0) {
			          item.msrc = childElements[0].getAttribute('src'); <?php // thumbnail url ?>
			          if(childElements.length > 1) {
			              item.title = childElements[1].innerHTML; <?php // caption (contents of figure) ?>
			          }
			        }


					var mediumSrc = el.getAttribute('data-med');
		          	if(mediumSrc) {
		            	size = el.getAttribute('data-med-size').split('x');
		            	<?php // "medium-sized" image ?>
		            	item.m = {
		              		src: mediumSrc,
		              		w: parseInt(size[0], 10),
		              		h: parseInt(size[1], 10)
		            	};
		          	}
		          	<?php // original image ?>
		          	item.o = {
		          		src: item.src,
		          		w: item.w,
		          		h: item.h
		          	};

			        items.push(item);
			    }

			    return items;
			};

			<?php // find nearest parent element ?>
			var closest = function closest(el, fn) {
			    return el && ( fn(el) ? el : closest(el.parentNode, fn) );
			};

			var onThumbnailsClick = function(e) {
			    e = e || window.event;
			    e.preventDefault ? e.preventDefault() : e.returnValue = false;

			    var eTarget = e.target || e.srcElement;

			    var clickedListItem = closest(eTarget, function(el) {
			        return el.tagName === 'A';
			    });

			    if(!clickedListItem) {
			        return;
			    }

			    var clickedGallery = clickedListItem.parentNode;

				<?php
				/*
			    var childNodes = clickedListItem.parentNode.childNodes,
			        numChildNodes = childNodes.length,
			        nodeIndex = 0,
			        index;

			    for (var i = 0; i < numChildNodes; i++) {
			       if(childNodes[i].nodeType !== 1) {
			            continue;
			        }

			        if(childNodes[i] === clickedListItem) {
			            index = nodeIndex;
			            break;
			        }

			        nodeIndex++;
			    }
				*/
				?>
				var index = clickedListItem.getAttribute('data-index-key')

			    if(index >= 0) {
			        openPhotoSwipe( index, clickedGallery );
			    }
			    return false;
			};

			var photoswipeParseHash = function() {
				var hash = window.location.hash.substring(1),
			    params = {};

			    if(hash.length < 5) { // pid=1
			        return params;
			    }

			    var vars = hash.split('&');
			    for (var i = 0; i < vars.length; i++) {
			        if(!vars[i]) {
			            continue;
			        }
			        var pair = vars[i].split('=');
			        if(pair.length < 2) {
			            continue;
			        }
			        params[pair[0]] = pair[1];
			    }

			    if(params.gid) {
			    	params.gid = parseInt(params.gid, 10);
			    }

			    return params;
			};

			var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
			    var pswpElement = document.querySelectorAll('.pswp')[0],
			        gallery,
			        options,
			        items;

				items = parseThumbnailElements(galleryElement);

			    <?php // define options (if needed) ?>
			    options = {

			        galleryUID: galleryElement.getAttribute('data-pswp-uid'),

			        getThumbBoundsFn: function(index) {
			           <?php  // See Options->getThumbBoundsFn section of docs for more info ?>
			            var thumbnail = items[index].el.children[0],
			                pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
			                rect = thumbnail.getBoundingClientRect();

			            return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
			        },

			        addCaptionHTMLFn: function(item, captionEl, isFake) {
						if(!item.title) {
							captionEl.children[0].innerText = '';
							return false;
						}
						captionEl.children[0].innerHTML = item.title +  '<br/><small>Photo: ' + item.author + '</small>';
						return true;
			        },

			    };

			    options.bgOpacity = 0.95;
				options.shareEl = false;
				options.captionEl = false;

			    if(fromURL) {
			    	if(options.galleryPIDs) {
			    		<?php // parse real index when custom PIDs are used ?>
			    		<?php // http://photoswipe.com/documentation/faq.html#custom-pid-in-url ?>
			    		for(var j = 0; j < items.length; j++) {
			    			if(items[j].pid == index) {
			    				options.index = j;
			    				break;
			    			}
			    		}
				    } else {
				    	options.index = parseInt(index, 10) - 1;
				    }
			    } else {
			    	options.index = parseInt(index, 10);
			    }

			    <?php // exit if index not found ?>
			    if( isNaN(options.index) ) {
			    	return;
			    }

			    if(disableAnimation) {
			        options.showAnimationDuration = 0;
			    }

			    <?php // Pass data to PhotoSwipe and initialize it ?>
			    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);

			   <?php // see: http://photoswipe.com/documentation/responsive-images.html ?>
				var realViewportWidth,
				    useLargeImages = false,
				    firstResize = true,
				    imageSrcWillChange;

				gallery.listen('beforeResize', function() {

					var dpiRatio = window.devicePixelRatio ? window.devicePixelRatio : 1;
					dpiRatio = Math.min(dpiRatio, 2.5);
				    realViewportWidth = gallery.viewportSize.x * dpiRatio;

				    if(realViewportWidth >= 1200 || (!gallery.likelyTouchDevice && realViewportWidth > 800) || screen.width > 1200 ) {
				    	if(!useLargeImages) {
				    		useLargeImages = true;
				        	imageSrcWillChange = true;
				    	}

				    } else {
				    	if(useLargeImages) {
				    		useLargeImages = false;
				        	imageSrcWillChange = true;
				    	}
				    }

				    if(imageSrcWillChange && !firstResize) {
				        gallery.invalidateCurrItems();
				    }

				    if(firstResize) {
				        firstResize = false;
				    }

				    imageSrcWillChange = false;

				});

				gallery.listen('gettingData', function(index, item) {
				    if( useLargeImages ) {
				        item.src = item.o.src;
				        item.w = item.o.w;
				        item.h = item.o.h;
				    } else {
				        item.src = item.m.src;
				        item.w = item.m.w;
				        item.h = item.m.h;
				    }
				});

				gallery.listen('beforeChange', function() {
					swiper.slideTo(gallery.getCurrentIndex(), 0);
				});

				gallery.listen('close', function() {
					swiper.slideTo(gallery.getCurrentIndex(), 0);
				});

			    gallery.init();
			};

			<?php // select all gallery elements ?>

			<?php
			echo "var galleryElements = [";

			$i = 0 ;
			foreach($f_p_array as $f_p_data) {

				if($i > 0)
					echo ', "' . $f_p_data . '"';
				else
					echo '"' . $f_p_data . '"';

				$i++;
			}

			echo "];";

		    ?>

			var numNodes_g = galleryElements.length;
		    for(var i = 0; i < numNodes_g; i++) {
				el = document.getElementById(galleryElements[i]);
				el.setAttribute('data-pswp-uid', i+1);
				el.onclick = onThumbnailsClick;
			}

			<?php //var galleryElements = document.querySelectorAll( gallerySelector ); ?>
			/*
			for(var i = 0, l = galleryElements.length; i < l; i++) {
				galleryElements[i].setAttribute('data-pswp-uid', i+1);
				galleryElements[i].onclick = onThumbnailsClick;
			}
			*/

			<?php // Parse URL and open gallery if it contains #&pid=3&gid=1 ?>
			var hashData = photoswipeParseHash();
			if(hashData.pid && hashData.gid) {
				openPhotoSwipe( hashData.pid,  galleryElements[ hashData.gid - 1 ], true, true );
			}
		};

		initPhotoSwipeFromDOM('.f-gallery');
	})();
</script>




<div id="gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>

        <div class="pswp__scroll-wrap">

          <div class="pswp__container">
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
          </div>

          <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

				<div class="pswp__counter"></div>

				<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

				<button class="pswp__button pswp__button--share" title="Share"></button>

				<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

				<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

				<div class="pswp__preloader">
					<div class="pswp__preloader__icn">
					  <div class="pswp__preloader__cut">
					    <div class="pswp__preloader__donut"></div>
					  </div>
					</div>
				</div>
            </div>


			<!-- <div class="pswp__loading-indicator"><div class="pswp__loading-indicator__line"></div></div> -->

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
	            <div class="pswp__share-tooltip">
					<!-- <a href="#" class="pswp__share--facebook"></a>
					<a href="#" class="pswp__share--twitter"></a>
					<a href="#" class="pswp__share--pinterest"></a>
					<a href="#" download class="pswp__share--download"></a> -->
	            </div>
	        </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div class="pswp__caption">
              <div class="pswp__caption__center">
              </div>
            </div>
          </div>

        </div>

</div>


	<?php
	}
	// Product not found.
	else {
	?>
	<div class="container-fluid container-product-page-margin">
		<div class="row row-product-not-found-container">
			<p class="product-not-found-text">Product not found.</p>
		</div>
	</div>

	<?php
	}
	require_once("footer.php");
	?>

	</body>
	</html>
<?php
}
?>
