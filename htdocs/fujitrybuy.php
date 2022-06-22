<?php
// fujitrybuy.php
// Michael Rajotte - 2018 Mai
// Custom page.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

function php_main_fujitrybuy() {
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
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-21150353-5"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', '');

			  <?php
			  if(isset($_SESSION['u_oauth_id']))
				echo "gtag('set', {'user_id': '" . $_SESSION['u_oauth_id'] . "'});";
			  ?>
			</script>
		*/
		?>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags --> ?>
		<meta name="description" content="Fuji Try and Buy program">
		<meta name="keywords" content="Fuji Try and Buy program Vancouver British Columbia Canada">

		<title>Leos Camera Supply - Fuji Try and Buy program</title>

	<?php
	php_load_pre_header();
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-index.css');?>">
	</head>

	<body>

	<?php
	php_load_header();

	$fluid->php_db_begin();
	?>

	<div id="fb-root"></div>

<style>
	.fluid-box-special {
		margin-top: 10px;
		max-width: 1000px;
		margin-left: auto;
		background-color: white;
		margin-right: auto;
		padding: 5px;
		text-align: center;
	}

	@media (min-width: 768px) {
		.fluid-box-special {
			margin-top: 20px;
			padding: 20px;
		}
	}
</style>

<div style='background-color: #f3f1f2;'>
	<div style='width: 100%; padding: 10px;'>
		<div id="breadcrumbs" style="padding-top: 10px; padding-left: 10px; display: table-cell; vertical-align: middle;"><a onmouseover="JavaScript:this.style.cursor='pointer';" href="<?php echo $_SESSION['fluid_uri'];?>" onclick="js_loading_start();">Home</a> / Fuji Try and Buy</div>

		<div id="banner-id-fuji-try-buy-innerhtml" class='fluid-box-shadow-transparent fluid-box-special'>
			<div><img class="img-responsive" src="<?php echo $_SESSION['fluid_uri'];?>uploads/fuji_try_buy_logo.jpg" style="margin: auto;"></div>
			<div><img class="img-responsive" src="<?php echo $_SESSION['fluid_uri'];?>uploads/fuji_x_items.jpg" style="margin: auto;"></div>

			<div class='' style='max-width: 1000px; margin: 0px 10px 0px 10px;'>
				<div class='alert alert-info'><h3 style='padding-top: 0px; margin-top: 0px;'>FUJIFILM Try and Buy Program</h3><p style='text-align: left;'>As of January 31, 2021, the Fuji Try and Buy program with Leo's is no longer available. In the near future, Leo's is going to be significantly expanding our RENTAL FLEET to include more Fuji items (lenses, bodies, accessories etc).</p></div>
				<p style='text-align: left; max-width: none; margin-top: 10px;'>Please call us at 604-685-5331 or email <a href="mailto:info@leoscamera.com">info@leoscamera.com</a> for any questions.</p>
			</div>
		</div>
	</div>

		<div>
			<div class="about-border"></div>
		</div>

</div>

<div>
	<div class="row row-about-leos">
		<div class='f-logo-about' id='f-logo-about'>
			<div style='display: inline-block; padding-right: 20px;'><img style='height: 65px;' src='files/camera_small.png'></img></div>
			<div style='display: inline-block; height: 100px; padding-top: 15px; vertical-align: bottom;'><span class='icon-leos-logo-rotate' style='font-size: 65px; color: red;'></span></div>
			<div style='display: inline-block; padding-left: 20px;'><img style='height: 65px; transform: scaleX(-1); -webkit-transform: scaleX(-1); -o-transform: scaleX(-1); -moz-transform: scaleX(-1); filter: FlipH; -ms-filter: "FlipH";' src='files/camera_small.png'></img></div>
		</div>

		<div>
			<p class="about-leos-paragraph">Hours: Monday to Friday 10:00am - 4:00pm</p>
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

</div>



	<?php
	$fluid->php_db_commit();
	require_once("footer.php");
	?>

	</body>
	</html>
<?php
}
?>
