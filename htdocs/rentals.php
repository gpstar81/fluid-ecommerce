<?php
// rentals.php
// Michael Rajotte - 2018 Mai
// Custom page.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

function php_main_rentals() {
	require_once("header.php");

	$detect = new Mobile_Detect;

	// Create a fluid class module.
	$fluid = new Fluid ();

	// --> A item list for tracking and passing to Google ga anayltics.
	$f_item_list = NULL;
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
			  gtag('js', Date());

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
		<meta name="description" content="Fuji Try and Buy program">
		<meta name="keywords" content="Fuji Try and Buy program Vancouver British Columbia Canada">

		<title>Leos Camera Supply - Rentals</title>

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
	}

	@media (min-width: 768px) {
		.fluid-box-special {
			margin-top: 20px;
		}
	}

	.f-body-rental {
		width: 100%;
	}

	.f-table-rental-holder {
		max-width: 1000px;
	}

	@media (min-width: 768px) {
		.f-body-rental {
			width: 100%; padding: 10px;
		}

		.f-table-rental-holder {
			max-width: 1000px;
			margin: 0px 10px 0px 10px;
		}
	}

</style>

<div style='background-color: #f3f1f2;'>
	<div class='f-body-rental'>
		<div id="breadcrumbs" style="padding-top: 10px; padding-left: 10px; display: table-cell; vertical-align: middle;"><a onmouseover="JavaScript:this.style.cursor='pointer';" href="<?php echo $_SESSION['fluid_uri'];?>" onclick="js_loading_start();">Home</a> / Rentals</div>

		<div id="banner-id-fuji-try-buy-innerhtml" class='fluid-box-shadow-transparent fluid-box-special'>

			<div class='f-table-rental-holder'>

			<?php
			php_rentals_html();
			?>

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
    var latlng = google.maps.LatLng(49.278781, -123.123779);

    var myOptions = {
      zoom: 15,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = google.maps.Map(document.getElementById("map_canvas"),
        myOptions);

	var contentString = '<div id="content">'+
		'<div id="siteNotice">'+
		'</div>'+
		'<span class="icon-leos-logo-rotate" style="font-size: 35px; color: red;"></span>'+
		'<div id="bodyContent" style="padding-top: 5px;">'+
		'<div style="font-size: 10px">1055 Granville Street</div><div style="font-size: 10px">Vancouver, BC</div><div style="font-size: 10px">Canada, V6Z1L4</div><div style="font-size: 10px">Ph: 604-685-5331</div><div style="font-size: 10px">Fax: 604-685-5648</div><div style="font-size: 10px">www.leoscamera.com</div>'+
		'</div>';

	var infowindow = google.maps.InfoWindow({
		content: contentString,
		maxWidth: 140
	});

	var marker = google.maps.Marker({
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

function php_rentals_html() {
	?>
<style type="text/css">

	table.tableizer-table {
		font-size: 10px;
		font-family: Arial, Helvetica, sans-serif;
	}
	.tableizer-table td {

	}
	.tableizer-table th {
		font-weight: bold;
	}

	.f-per-day {
		font-weight: bold;
		text-align: right;
	}

	.f-per-weekly {
		font-weight: bold;
		text-align: right;
	}

	.f-per-replacement {
		display: none;
		font-weight: bold;
		text-align: right;
	}

	.f-per-deposit {
		font-weight: bold;
		text-align: right;
	}

	.f-per-acc {
		display: none;
		font-weight: bold;
		text-align: right;
	}

	.f-right-money {
		text-align: right;
	}

	.f-right-money-hide {
		display: none;
	}

	.f-right-money-replace {
		display: none;
	}

	.f-rental-title {
		font-weight: bold;
	}

	.f-rental-title-line {
		font-weight: bold;
	}

	.f-title-rental-table {
		font-size: 10px;
		margin-bottom: 10px;
	}

	.f-rental-logo {
		font-size: 65px;
	}

	.f-rental-logo-div {
		margin-top: 10px;
		margin-bottom: 10px;
		text-align: center;
	}

	@media (min-width: 768px) {
		table.tableizer-table {
			font-size: 12px;
			font-family: Arial, Helvetica, sans-serif;
		}
		.tableizer-table td {

		}
		.tableizer-table th {
			font-weight: bold;
		}

		.f-per-day {
			font-weight: bold;
			min-width: 100px;
			text-align: right;
		}

		.f-per-weekly {
			font-weight: bold;
			min-width: 100px;
			text-align: right;
		}

		.f-per-replacement {
			display: table-cell;
			font-weight: bold;
			min-width: 100px;
			text-align: right;
		}

		.f-per-deposit {
			display: table-cell;
			font-weight: bold;
			min-width: 100px;
			text-align: right;
		}

		.f-per-acc {
			display: none;
			font-weight: bold;
			min-width: 100px;
			text-align: right;
		}

		.f-right-money {
			text-align: right;
		}

		.f-right-money-hide {
			display: none;
			text-align: right;
		}

		.f-right-money-replace {
			display: table-cell;
			text-align: right;
		}

		.f-rental-title {
			font-weight: bold;
		}

		.f-rental-title-line {
			font-weight: bold;
		}

		.f-title-rental-table {
			font-size: 12px;
			margin-bottom: 20px;
		}

		.f-rental-logo {
			font-size: 105px;
		}

		.f-rental-logo-div {
			margin-top: 20px;
			margin-bottom: 25px;
			text-align: center;
		}
	}

</style>

<div class='f-rental-logo-div'><span class="icon-leos-logo-rotate f-rental-logo" style="color: red;"></span></div>

<table class='f-title-rental-table'>
<thead><tr class="tableizer-firstrow"><th>Terms and Conditions of Leo's Camera Supply Rentals</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr></thead><tbody>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>Availability:</td></tr>
 <tr><td colspan="6">Product availability is not guaranteed. To help ensure availability, please book your rental as far in advance as possible (maximum 4 weeks ahead).</td></tr>
 <tr><td colspan="6">To aid the staff of Leo's & the renting public, please inform us ASAP of any change in your bookings. This allows us to re-circulate the equipment.</td></tr>
 <tr><td colspan="6">Overdue rentals are subject to full daily charge (see late & overdue Rentals).</td></tr>
 <tr><td colspan="6">Please call the store to make a booking. We can not guarantee bookings made via email!</td></tr>
 <tr><td colspan="6">If a RENTER has a two "NO-SHOW" rental bookings, rental reservation privileges will be suspended.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>Deposits & Payment:</td></tr>
 <tr><td colspan="6">All rentals are prepaid in full at time of pickup. (Visa, M/C, Debit or Cash)</td></tr>
 <tr><td colspan="6">Rental Deposits can be made by Visa, M/C, Bank Draft or cash (if paid via credit card, card holder must be present at the time of deposit). (Debit not accepted.)</td></tr>
 <tr><td colspan="6">Valid Picture ID is required for all rentals. If the renter is from within B.C. (Picture ID with current local address, i.e., B.C. Drivers License or BCID is acceptable) then a lower deposit applies. (Passport will only accept as proof of ID with recent Government issue letters with local address).</td></tr>
 <tr><td colspan="6">If renter is from out of province (out of country) Leo's will request a deposit based on the full replacement value of the equipment to be rented! (see below for additional information.)</td></tr>
 <tr><td colspan="6">Leo's Cameras may request a deposit equal to the replacement value of said equipment at their discretion unless prior arrangements have been made. If renter is from out of province or can not provide adequate proof or residence, replacement value will be required.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>Rental Periods and Rates:</td></tr>
 <tr><td colspan="6">Daily rental rates apply to all rentals up to (4) days (except weekends & holidays.)</td></tr>
 <tr><td colspan="6">One day rentals start anytime during store operating hours one day and end before 4:00pm the following business days.</td></tr>
 <tr><td colspan="6">Weekly rental rates (7) days are based on (4) billable days.</td></tr>
 <tr><td colspan="6">Monthly rental rates (30) days are based on (12) billable days.</td></tr>
 <tr><td colspan="6">Weekend rentals start at 9:00am Saturday and must be returned before 12:00pm on the following Monday, to be considered a (1) day rental. (Rentals picked up on Friday will be charged a (2) day rate!)</td></tr>
 <tr><td colspan="6">Holiday weekends (Please enquire with Leo's staff as to period and rates for holiday weekends).</td></tr>
 <tr><td colspan="6">Minimum charge on any rental contract is $10.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>Liability:</td></tr>
 <tr><td colspan="6">The Renter agrees to rent the equipment listed in the rental agreement for the term and rate specified. (see late or overdue rentals)</td></tr>
 <tr><td colspan="6">The Renter agrees that all rented equipment remains the sole property of Leo's Camera Supply Ltd.</td></tr>
 <tr><td colspan="6">The Renter is responsible for any loss or damage to the equipment, due to negligence, theft, improper installation and or operation of the equipment, excluding mechanical failure due to normal wear and tear. In the event of loss or damage of the equipment, the renter is liable for the cost of repair or replacement required, which is to be determined at the discretion of Leo's Camera. (Renters are encouraged for their own protection, to obtain adequate insurance coverage. Insurance information must be forwarded to Leo's no later than 48 hrs prior to the rental date.)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">The Renter acknowledges that Leo's Camera Supply Ltd. Has no specific knowledge of the Renter's requirements and in selecting the equipment the Renter has relied on his/her own judgement. Software required for editing purposes is the sole responsibility of the renter. We are not responsible for incompatibilities that may occur.</td></tr>
 <tr><td colspan="6">Leo's Camera Supply Ltd. Is not responsible for improper use or insufficient knowledge by the Renter in the use of its rental equipment. (Instruction manuals are available for rental equipment upon request).</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Batteries (other than rechargeables, which are supplied with some products) are the responsibility of the renter. It is suggested that the renter ensures they have additional batteries on hand.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>Booking Fees:</td></tr>
 <tr><td colspan="6">There will be a non-refundable $50.00 + tax booking fee on the following rental criteria:</td></tr>
 <tr><td colspan="6">(A). If there are 5 or more items being rented,</td></tr>
 <tr><td colspan="6">or</td></tr>
 <tr><td colspan="6">(B). If the subtotal of the rental fee's come to $400.00 or higher.</td></tr>
 <tr><td colspan="6">Payment of the booking fee is due upon reservation of the rental. At the time of pickup, the $50.00 fee will be deducted off the total rental fee's owing and the renter will pay the balance. If the rental is cancelled or a non-show, the booking fee is non refundable and will be forfeited. Booking fees are determined at the discretion of Leo's Camera Supply.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Late or overdue rentals</td></tr>
 <tr><td colspan="6">All rentals are due back on the date specified and agreed to on the rental contract. If the renter requires an extension of their rental and gives Leo's adequate warning, Leo's will extend to the renter the weekly or monthly discounts if applicable. (rental equipment must be available for this service to be extended)</td></tr>
 <tr><td colspan="6">If the renter extends their rental without adequate notice or does not inform Leo's of their changes, the renter will be charged out at the full daily rate. This means that the renter will not receive any discount applicable, will charge out each day of the extends, including weekend. Also, if there is another rental that have to cancel due to the late return of that equipment, the rental also is reponsible to the lost of rental on top of the late charges.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title-line'>"Try Before You Buy" Program:</td></tr>
 <tr><td colspan="6">Thinking of purchasing gear in our rental pool? We will apply (1) days rental towards the purchase of the same or equivalent gear within a (2)week period of rental completion.</td></tr>
 <tr><td colspan="6">Special Note:</td></tr>
 <tr><td colspan="6">Don't see it in our rental pool? Ask us and we may add it. If it's used and Leo's owns it, we will rent it!</td></tr>
 </thead>
 </table>

<table class="table table-condensed table-responsive tableizer-table">
	<?php
	/*
<thead><tr class="tableizer-firstrow"><th>Terms and Conditions of Leo's Camera Supply Rentals</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr></thead><tbody>
 <tr><td colspan="6">Availability:</td></tr>
 <tr><td colspan="6">Product availability is not guaranteed. To help ensure availability, please book your rental as far in advance as possible (maximum 4 weeks ahead).</td></tr>
 <tr><td colspan="6">To aid the staff of Leo's & the renting public, please inform us ASAP of any change in your bookings. This allows us to re-circulate the equipment.</td></tr>
 <tr><td colspan="6">Overdue rentals are subject to full daily charge (see late & overdue Rentals).</td></tr>
 <tr><td colspan="6">Please call the store to make a booking. We can not guarantee bookings made via email!</td></tr>
 <tr><td colspan="6">If a RENTER has a two "NO-SHOW" rental bookings, rental reservation privileges will be suspended.</td></tr>
 <tr><td colspan="6">Deposits & Payment:</td></tr>
 <tr><td colspan="6">All rentals are prepaid in full at time of pickup. (Visa, M/C, Debit or Cash)</td></tr>
 <tr><td colspan="6">Rental Deposits can be made by Visa, M/C, Bank Draft or cash (if paid via credit card, card holder must be present at the time of deposit). (Debit not accepted.)</td></tr>
 <tr><td colspan="6">Valid Picture ID is required for all rentals. If the renter is from within B.C. (Picture ID with current local address, i.e., B.C. Drivers License or BCID is acceptable) then a lower deposit applies. (Passport will only accept as proof of ID with recent Government issue letters with local address).</td></tr>
 <tr><td colspan="6">If renter is from out of province (out of country) Leo's will request a deposit based on the full replacement value of the equipment to be rented! (see below for additional information.)</td></tr>
 <tr><td colspan="6">Leo's Cameras may request a deposit equal to the replacement value of said equipment at their discretion unless prior arrangements have been made. If renter is from out of province or can not provide adequate proof or residence, replacement value will be required.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Rental Periods and Rates:</td></tr>
 <tr><td colspan="6">Daily rental rates apply to all rentals up to (4) days (except weekends & holidays.)</td></tr>
 <tr><td colspan="6">One day rentals start anytime during store operating hours one day and end before 4:00pm the following business days.</td></tr>
 <tr><td colspan="6">Weekly rental rates (7) days are based on (4) billable days.</td></tr>
 <tr><td colspan="6">Monthly rental rates (30) days are based on (12) billable days.</td></tr>
 <tr><td colspan="6">Weekend rentals start at 9:00am Saturday and must be returned before 12:00pm on the following Monday, to be considered a (1) day rental. (Rentals picked up on Friday will be charged a (2) day rate!)</td></tr>
 <tr><td colspan="6">Holiday weekends (Please enquire with Leo's staff as to period and rates for holiday weekends).</td></tr>
 <tr><td colspan="6">Minimum charge on any rental contract is $10.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Liability:</td></tr>
 <tr><td colspan="6">The Renter agrees to rent the equipment listed in the rental agreement for the term and rate specified. (see late or overdue rentals)</td></tr>
 <tr><td colspan="6">The Renter agrees that all rented equipment remains the sole property of Leo's Camera Supply Ltd.</td></tr>
 <tr><td colspan="6">The renter is responsible for any loss or damage to the equipment, due to negligence, theft, improper installation and or operation of the equipment, excluding mechanical failure due to normal wear and tear. In the event of loss or damage of the equipment, the renter is liable for the cost of repair or replacement required, which is to be determined at the discretion of Leo's Camera. (Renters are encouraged for their own protection, to obtain adequate insurance coverage. Insurance information must be forwarded to Leo's no later than 48 hrs prior to the rental date.)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">The Renter acknowledges that Leo's Camera Supply Ltd. Has no specific knowledge of the Renter's requirements and in selecting the equipment the Renter has relied on his/her own judgement. Software required for editing purposes is the sole responsibility of the renter. We are not responsible for incompatibilities that may occur.</td></tr>
 <tr><td colspan="6">Leo's Camera Supply Ltd. Is not responsible for improper use or insufficient knowledge by the Renter in the use of its rental equipment. (Instruction manuals are available for rental equipment upon request).</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Batteries (other than rechargeables, which are supplied with some products) are the responsibility of the renter. It is suggested that the renter ensures they have additional batteries on hand.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Booking Fees:</td></tr>
 <tr><td colspan="6">There will be a non-refundable $50.00 + tax booking fee on the following rental criteria:</td></tr>
 <tr><td colspan="6">(A). If there are 5 or more items being rented,</td></tr>
 <tr><td colspan="6">or</td></tr>
 <tr><td colspan="6">(B). If the subtotal of the rental fee's come to $400.00 or higher.</td></tr>
 <tr><td colspan="6">Payment of the booking fee is due upon reservation of the rental. At the time of pickup, the $50.00 fee will be deducted off the total rental fee's owing and the renter will pay the balance. If the rental is cancelled or a non-show, the booking fee is non refundable and will be forfeited. Booking fees are determined at the discretion of Leo's Camera Supply.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Late or overdue rentals</td></tr>
 <tr><td colspan="6">All rentals are due back on the date specified and agreed to on the rental contract. If the renter requires an extension of their rental and gives Leo's adequate warning, Leo's will extend to the renter the weekly or monthly discounts if applicable. (rental equipment must be available for this service to be extended)</td></tr>
 <tr><td colspan="6">If the renter extends their rental without adequate notice or does not inform Leo's of their changes, the renter will be charged out at the full daily rate. This means that the renter will not receive any discount applicable, will charge out each day of the extends, including weekend. Also, if there is another rental that have to cancel due to the late return of that equipment, the rental also is reponsible to the lost of rental on top of the late charges.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">"Try Before You Buy" Program:</td></tr>
 <tr><td colspan="6">Thinking of purchasing gear in our rental pool? We will apply (1) days rental towards the purchase of the same or equivalent gear within a (2)week period of rental completion.</td></tr>
 <tr><td colspan="6">Special Note:</td></tr>
 <tr><td colspan="6">Don't see it in our rental pool? Ask us and we may add it. If it's used and Leo's owns it, we will rent it!</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Store Hours:</td></tr>
 <tr><td colspan="6">Mon-Sat: 9:00AM - 5:00PM</td></tr>
 <tr><td colspan="6">Sundays & Holidays - CLOSED</td></tr>
 <tr><td colspan="6">Ph: 604-685-5331</td></tr>
 <tr><td colspan="6">Fax: 604-685-5648</td></tr>
 <tr><td colspan="6">Website: www.leoscamera.com</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Table of Contents</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Terms & Conditions</td><td>------------------</td><td>Page 2</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Digital SLR & Mirrorless Cameras</td><td>------------------</td><td>Page 5</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">Camera, Lenses and Accessories</td></tr>
 <tr><td colspan="6">-Canon Digital SLR, Lenses, Flashes & Accessories</td></tr>
 <tr><td colspan="6">-Sigma, Lens Adapters for Canon</td><td>------------------</td><td>Page 6</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Sigma for Nikon</td><td>------------------</td><td>Page 6</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Flashes for Nikon</td><td>------------------</td><td>Page 7</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Fujifilm X-Series Camera, Lenses and Adapters</td></tr>
 <tr><td colspan="6">-Zeiss Touit for Fujifilm X-Series</td></tr>
 <tr><td colspan="6">-Panasonic (MFT) (micro four thirds) Camera, Lenses and Adapters</td></tr>
 <tr><td colspan="6">-Pentax Digital SLR, Lenses and Accessories</td><td>------------------</td><td>Page 8</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Analog Film Camera and Accessories</td></tr>
 <tr><td colspan="6">SLR & RangeFinder Cameras and Lenses</td></tr>
 <tr><td colspan="6">-Nikon Film Cameras</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Digital and Analog Medium Format Cameras, Lenses and Accessories</td></tr>
 <tr><td colspan="6">Fujifilm GFX 50s</td></tr>
 <tr><td colspan="6">Pentax 645 Lenses, Pentax 6X7</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Video Equipment (HD)</td></tr>
 <tr><td colspan="6">Prosumer / Professional Video Camera (HD)</td><td>------------------</td><td>Page 9</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Panasonic</td></tr>
 <tr><td colspan="6">Consumer Video Cameras (HD) (Tape)</td></tr>
 <tr><td colspan="6">-Canon</td></tr>
 <tr><td colspan="6">Water Proof POV Camera(HD) </td></tr>
 <tr><td colspan="6">-Go Pro</td></tr>
 <tr><td colspan="6">360° POV Camera (HD)</td></tr>
 <tr><td colspan="6">-Ricoh Theta</td></tr>
 <tr><td colspan="6">Standard Definition Video Cameras 3-Chip</td></tr>
 <tr><td colspan="6">-Canon</td></tr>
 <tr><td colspan="6">Standard Definition Video Cameras Analog</td></tr>
 <tr><td colspan="6">-Canon</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Accessories For DSLR with Video, Prosumer or Professional Video Cameras</td></tr>
 <tr><td colspan="6">Matte Boxes, Follow Focus, 15mm LW Rails, Shoulder Rigs</td><td>Page 10</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Vocas Matte Boxes & Follow Focus Units (for video & DSLR cameras)</td></tr>
 <tr><td colspan="6">-Vocas PL (Cine) Lens Adapters for Panasonic MFT (micro 4/3) & Sony 'E' mount cameras.</td></tr>
 <tr><td colspan="6">-Vocas 15mm LW (Light Weight) rails, shoulder mount rigs & accessories (for video & hdslr)</td></tr>
 <tr><td colspan="6">System Filters 4x4 & 4x5.65 </td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Portable Monitors & Cables</td></tr>
 <tr><td colspan="6">-Ikan Portable Monitors (HD) (for video & HDSLR cameras)</td></tr>
 <tr><td colspan="6">Remote Camera Controllers</td></tr>
 <tr><td colspan="6">-Remote Camera Controllers (for LANC, Panasonic & Sony EX video cameras)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Digital Media, Storage, Readers & Accessories</td></tr>
 <tr><td colspan="6">Digital Media Storage</td></tr>
 <tr><td colspan="6">-CF and SD Cards</td></tr>
 <tr><td colspan="6">Card Readers</td></tr>
 <tr><td colspan="6">-Sandisk Firewire CF Reader, Delkin USB 3.0 Universal Card Reader</td></tr>
 <tr><td colspan="6">Monitor & Printer Calibrator</td></tr>
 <tr><td colspan="6">-X-Rite Colormunki Photo</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Tripods, Dollies, Monopods, QTVR & Gigapan (Film & Video)</td></tr>
 <tr><td colspan="6">-Miller, Manfrotto (Tripods, Dollies & Monopods)</td><td>------------------</td><td>Page 11</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Manfrotto QTVR Tripod System</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Camera Stabilizers / Portable Track Systems & Jib Arms</td></tr>
 <tr><td colspan="6">-Hollywood Lite Stabilizers</td></tr>
 <tr><td colspan="6">-Micro Dolly Portable Track (Dolly) Systems</td></tr>
 <tr><td colspan="6">-Manfrotto Fig Rig</td></tr>
 <tr><td colspan="6">-Digital Juice Slyder</td></tr>
 <tr><td colspan="6">-Cambo Jib Arm Package</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Audio Equipment</td></tr>
 <tr><td colspan="6">Wireless Mic Systems</td><td>------------------</td><td>Page 12</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Lectrosonics UHF Wireless</td></tr>
 <tr><td colspan="6">Sennheiser Evolution G3 Wireless System</td></tr>
 <tr><td colspan="6">-BEC Wireless Mounting Brackets</td></tr>
 <tr><td colspan="6">Wireless IFB Systems</td></tr>
 <tr><td colspan="6">-Lectrosonics UHF IFB (Interruptible fold back)</td></tr>
 <tr><td colspan="6">Hardwired Mic Systems</td></tr>
 <tr><td colspan="6">-Rode Video Mic (1/8" mini jack)</td></tr>
 <tr><td colspan="6">-Sennheisser Shotgun Mics (XLR)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Audio Accessories</td></tr>
 <tr><td colspan="6">-Microphone Booms</td></tr>
 <tr><td colspan="6">-XLR Audio Cables</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Battery Systems & Power Adapters (for Video, HDSLR's & Lighting)</td></tr>
 <tr><td colspan="6">-Anton Bauer Battery Systems. (Battery Belts, Gold Link Batteries & Chargers)</td><td>------------------</td><td>Page 13</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-NRG 12V Adapters</td></tr>
 <tr><td colspan="6">-Battery Systems & Power Adapters for HDSLR Cameras</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Lighting</td></tr>
 <tr><td colspan="6">Continuous Light Source (3200°K / 120V)</td><td>------------------</td><td>Page 14</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Lowel (Tungsten)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Continuous Light Source (5600°K / 120V / 12V)</td></tr>
 <tr><td colspan="6">-Litepanels LED</td></tr>
 <tr><td colspan="6">Continuous Light Source 3000°k - 5600°k (120V/12V-28V)</td></tr>
 <tr><td colspan="6">-Fiilex LED</td></tr>
 <tr><td colspan="6">Continuous Light Source (5500°K / 120V)</td></tr>
 <tr><td colspan="6">-Lowel (soft light / fluorescent)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Studio Flash Systems (5500°K / 120V)</td><td>------------------</td><td>Page 15</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Elinchrom ELC Pro HD Monolights</td></tr>
 <tr><td colspan="6">-Aurora Unilever Pro Monolights</td></tr>
 <tr><td colspan="6">Studio & Lighting Accessories</td></tr>
 <tr><td colspan="6">-Aurora softboxes & connectors</td></tr>
 <tr><td colspan="6">-Booth Product Shooting Tent</td></tr>
 <tr><td colspan="6">-Photoflex Umbrellas</td></tr>
 <tr><td colspan="6">-Photoflex (softboxes & reflectors)</td></tr>
 <tr><td colspan="6">-Portable Umbrella Kit (for shoe mounted flashes)</td></tr>
 <tr><td colspan="6">-Gary Fong Flash Diffusers</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Backgrounds & Background Kit Supports</td></tr>
 <tr><td colspan="6">-Cameron Portable Chromakey Backgrounds</td></tr>
 <tr><td colspan="6">-Manfrotto Background Stands</td></tr>
 <tr><td colspan="6">-Manfrotto Backlight Stands</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Light Meters & Misc. Accessories</td></tr>
 <tr><td colspan="6">-Pocket Wizard Radio Slaves (Basic & TTL) (EOS)</td></tr>
 <tr><td colspan="6">-Sekonic Meters</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Audio Visual Equipment</td></tr>
 <tr><td colspan="6">-Bantam Overhead Projectors</td><td>------------------</td><td>Page 16</td><td>&nbsp;</td></tr>
 <tr><td colspan="6">-Dalite Projection Screens</td></tr>
 <tr><td colspan="6">-Epson LCD Digital Multimedia Projector (720P HDTV Compatible)</td></tr>
 <tr><td colspan="6">-Kodak Carousel Style 35mm Slide Projector</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Movie Film Equipment</td></tr>
 <tr><td colspan="6">-16mm Sound Film Projectors</td></tr>
 <tr><td colspan="6">-Super 8 Film Cameras</td></tr>
 <tr><td colspan="6">-Press Tape Film Slicer (8mm, Super 8 & 16mm)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Vintage & Contemporary Prop Equipment</td></tr>
 <tr><td colspan="6">-Non Working Period Pieces</td></tr>
 <tr><td colspan="6">-Early Press Cameras</td></tr>
 <tr><td colspan="6">-Cameras from 1960's & 1970's</td></tr>
 <tr><td colspan="6">-Early Misc Cameras</td></tr>
 <tr><td colspan="6">-Audio Recorders (various vintages)</td></tr>
 <tr><td colspan="6">-Early 16mm (News) Cameras</td></tr>
 <tr><td colspan="6">-16mm (News & Documentary) Cameras</td></tr>
 <tr><td colspan="6">-Broadcast ENG (News & Documentary) Video Cameras</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 */
 ?>


 <tbody>
 <tr><td colspan="6" class='f-rental-title'>DIGITAL SLR & MIRRORLESS CAMERAS</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CAMERAS, LENSES & ACCESSORIES</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CANON EOS</td></tr>
 <tr><td colspan="6">The Canon EOS system, gives you the user, unparalleled quality and versatility. This is done through an exceptionally fast and quiet focusing, as well as, the unique lens designs available only to the Canon EOS system.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CANON DIGITAL SLR</td></tr>
 <tr><td class='f-rental-title-line'>DSLR WITH HD VIDEO</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EOS 5DsR (50.6 Mega Pixel) (Full Frame Sensor)</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money'>$1,000.00</td>	<td class='f-right-money'>$3,500.00</td>	<td class='f-right-money-replace'>$2,500.00</td>	<td class='f-right-money-hide'>$1,250.00</td></tr>
 <tr><td colspan="6">The EOS 5DsR offers the potential for even greater sharpness and fine detail for specialized situations. It features the same Canon designed and manufactured 50.6 Megapixel sensor, with the low-pass filter* (LPF) effect cancelled to provide even more fine edge sharpness and detail for critical subjects such as detailed landscapes, and other situations where getting the sharpest subject detail is a priority. *The possibility of moiré and color artifacts is greater due to the LPF cancellation function.</td></tr>
 <tr><td colspan="6">Includes: 32GB CF Card, (2)Li-ion Battery, Safe Sync, charger, software & card reader</td></tr>
 <tr><td>EOS 5D Mark III (22.3 Mega Pixel) (Full Frame Sensor)</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$800.00</td>	<td class='f-right-money'>$2,500.00</td>	<td class='f-right-money-replace'>$1,800.00</td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td colspan="6">Includes: 32GB CF Card, (2)Li-ion Battery, Safe Sync, charger, software & card reader</td></tr>
 <tr><td>EOS 5D Mark II (21.1 Mega Pixel) (Full Frame Sensor)</td>	<td class='f-right-money'>$75.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money'>$1,300.00</td>	<td class='f-right-money-replace'>$800.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Includes: 8GB CF Card, (2)Li-ion Battery, Safe Sync, charger, software & card reader</td></tr>
 <tr><td>EOS 7D MKII (20.2 Mega Pixel) APS-C Sensor)</td>	<td class='f-right-money'>$150.00</td>	<td class='f-right-money'>$600.00</td>	<td class='f-right-money'>$1,800.00</td>	<td class='f-right-money-replace'>$1,200.00</td>	<td class='f-right-money-hide'>$700.00</td></tr>
 <tr><td colspan="6">Includes: 16GB CF Card, (2)Li-ion Battery, Safe Sync, charger, software & card reader</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CANON LENSES</td></tr>
 <tr><td class='f-rental-title-line'>CANON EF LENSES (Full Frame, APS-C or Film Cameras)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EF 14mm F2.8 'L' USM</td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$2,400.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 24mm F1.4 'L' MKII USM</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 35mm F1.4 'L' USM</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 50mm F1.4 USM</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$450.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>EF 85mm F1.8 USM</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$550.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>EF 100mm F2.8 'L' USM IS  MACRO (image stabilized)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EF 135mm F2 'L' USM</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 400mm F5.6 'L' USM</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$1,400.00</td>	<td class='f-right-money-replace'>$700.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>EF 16-35mm F2.8 'L' USM MKII</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$1,700.00</td>	<td class='f-right-money-replace'>$1,000.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 24-70mm F2.8 'L' MKII USM</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$2,000.00</td>	<td class='f-right-money-replace'>$1,000.00</td>	<td class='f-right-money-hide'>$700.00</td></tr>
 <tr><td>EF 70-200mm F4 'L' IS USM (image stabilized)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EF 70-200mm F2.8 'L' MKII IS USM (image stabilized)</td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$2,200.00 </td>	<td class='f-right-money-replace'>$1,100.00 </td>	<td class='f-right-money-hide'>$600.00</td></tr>
 <tr><td>EF 70-200mm F2.8 'L' IS USM (image stabilized)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EF 100-400mm F4.5-5.6 'L' IS USM MKII (image stabilized)</td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$2,200.00 </td>	<td class='f-right-money-replace'>$1,100.00 </td>	<td class='f-right-money-hide'>$600.00</td></tr>
 <tr><td>EF 100-400mm F4.5-5.6 'L' IS USM (image stabilized)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>EF 1.4X II Tele converter (enquire for lens compatability)</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>EF 2X II Tele converter (enquire for lens compatability)</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$450.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>CANON SPECIALITY LENSES (Full Frame, APS-C or Film Cameras)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>TS-E 17mm F4 'L' (Tilt/Shift) (Manual Focus)</td>	<td class='f-right-money'>$45.00 </td>	<td class='f-right-money'>$180.00 </td>	<td class='f-right-money'>$2,500.00 </td>	<td class='f-right-money-replace'>$1,500.00 </td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td>TS-E 24mm F3.5 II 'L' (Tilt/Shift) (Manual Focus)</td>	<td class='f-right-money'>$45.00 </td>	<td class='f-right-money'>$180.00 </td>	<td class='f-right-money'>$2,200.00 </td>	<td class='f-right-money-replace'>$1,500.00 </td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td>TS-E 45mm f2.8 (Tilt/Shift) (Manual Focus)</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$1,500.00</td>	<td class='f-right-money-replace'>$700.00</td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>TS-E 90mm F2.8 (Tilt/Shift) (Manual Focus)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>MP-E 65mm F2.8 Macro (1-5x magnification) (Manual Focus)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>SIGMA LENSES FOR ALL CANON EOS SYSTEM CAMERAS (Full Frame, APS-C or Film Cameras)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EX 8mm F3.5 Fisheye (Circular Image on Full Frame) (Full Fisheye effect on APS-C)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 15mm F2.8 fisheye</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$750.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>24mm f/1.4 DG HSM ART</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>35mm f1.4 DG HSM ART</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>50mm f/1.4 DG HSM ART</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>EX 85mm F1.4 DG HSM</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 105mm f/2.8 DG OS HSM Macro(1:1)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$550.00 </td>	<td class='f-right-money-replace'>$1800.00 </td>	<td class='f-right-money-hide'>$1000.00</td></tr>
 <tr><td>135mm f/1.8 DG HSM ART</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>EX 150mm F2.8 HSM Macro (1:1) DG</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$800.00 </td>	<td class='f-right-money-replace'>$450.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 800mm F5.6 APO HSM</td>	<td class='f-right-money'>$125.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money'>$6,500.00 </td>	<td class='f-right-money-replace'>$3,000.00 </td>	<td class='f-right-money-hide'>$2,000.00</td></tr>
 <tr><td>EX 15-30mm F3.5-4.5 DG</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$700.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>EX 24-70mm f/2.8 IF DG HSM</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>EX 50-500mm F4-6.3 DG OS HSM (image stabilized)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,800.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EX 70-200mm F2.8 APO DG OS HSM (image stabilized)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>EX 120-300mm F2.8 AF APO EX DG OS HSM (image stabilized)</td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money'>$2,400.00 </td>	<td class='f-right-money-replace'>$1,400.00 </td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td>EX 1.4X APO Teleconverter (enquire for lens compatability)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>EX 2X APO Teleconverter (enquire for lens compatability)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$350.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>NEW Sigma Mount Converter MC-11 (Adapt Canon Mount to Sony FE system)</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$330.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
  <tr><td>Sigma USB Dock for Canon Mount Sigma ART or Sport Lenses</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money-replace'>$80.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>SIGMA LENSES FOR EOS DIGITAL (APS-C ONLY)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EX 10mm F2.8 DC Fisheye (16mm equivalent)(Full Frame fisheye for APS-C Cameras)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 10-20mm F3.5 HSM DC (16-35mm equivalent)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$700.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 18-35mm F1.8 DC HSM (28.8-56mm equivalent)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$800.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 18-200mm F3.5-6.3 DC (28-320mm equivalent)</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$450.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>Canon ETTL II Flashes & Accessories for Canon EOS (Film & Digital)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Canon MR-14EX Ring Flash</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$325.00 </td>	<td class='f-right-money-hide'>$275.00</td></tr>
 <tr><td colspan="6">Includes: Marco Adapter Rings, Case Note: flashes require (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Canon 600 EX-RT Flash</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$325.00 </td>	<td class='f-right-money-hide'>$275.00</td></tr>
 <tr><td colspan="6">Includes: Flash stand (for remote wireless placement & pouch). Note: flashes require (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Canon 580 EXII Flash</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$550.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">Includes: Sto-fen flash diffuser, flash stand (for remote wireless placement & pouch). Note: flashes require (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Canon 430 EXII Flash</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$350.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Includes: Sto-fen flash diffuser, flash stand (for remote wireless placement & pouch). Note: flashes require (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Canon ST-E3-RT Speedlite Transmitter</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$100.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Two-way wireless transmission up to 98.4 feet, among up to five groups or fifteen individual 600EX-RT Speedlites. Does not work with any older models such as 580EX II</td></tr>
 <tr><td>Canon ST-E2 IR ETTL II Flash Controller</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$100.00</td>	<td class='f-right-money-hide'>$85.00</td></tr>
 <tr><td colspan="6">Allows wireless control of multiple EX Series Flashes) Note: As the Canon wireless TTL system works via IR transmission, system is limited to short range, line of sight operation.</td></tr>
 <tr><td>Canon Off Camera Shoe Cable 3 w/Flash Bracket</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$75.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>Misc. Canon EOS Accessories (Film & Digital)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Kenko AF Extension Tubes (set of 3)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money-replace'>$50.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Full metering & exposure compatability with all EOS film & Digital cameras</td></tr>
 <tr><td>Canon RS-60E3 Remote (for Elan 7, 60D & All Rebel Digital Cameras)</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money-replace'>$30.00 </td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td>Canon RS-80N3 Remote (for all other Canon models we rent) (Film & Digital)</td>	<td class='f-right-money'>$7.00 </td>	<td class='f-right-money'>$28.00 </td>	<td class='f-right-money'>$75.00 </td>	<td class='f-right-money-replace'>$40.00 </td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td>Canon TC-80N3 Timer Remote (for all other Canon models we rent) (Film & Digital)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money-replace'>$125.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <?php
 /*
 <tr><td class='f-rental-title-line'>LENS ADAPTERS (For Canon EOS Mount)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Novoflex Nikon -> Canon EOS Lens Adapter</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Use your existing Nikon 'F' mount lenses or the Zeiss 'F' mount lenses on any Canon EOS Camera. (This is an ideal option when shooting HD video on the EOS HDSLR bodies.)</td></tr>
 <tr><td>Novoflex Nikon G -> Canon EOS Lens Adapter</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$275.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Allows the use of the Nikon 'G' type (Electronic Iris) lenses on the Canon EOS bodies.</td></tr>
 <tr><td>Novoflex Leica R - Canon EOS Lens Adapter</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Allows the use of most Leica 'R' mount lenses on the Canon EOS Bodies.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 */
 ?>
 <tr><td colspan="6" class='f-rental-title'>NIKON</td></tr>
 <tr><td class='f-rental-title-line'>NIKON CAMERA, LENSES AND ACCESSORIES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Nikon D2X (12.5 MegaPixel) (APS-C)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$750.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">This professional body can be used either in its normal mode and shoot @ 5fps or can be swiched to a high speed 8fps mode @ 6.8MP (flexible indeed)</td></tr>
 <tr><td colspan="6">Includes: 4.0GB CF Card, (2) Li-Ion batteries, charger, software & card reader.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>SIGMA LENSES FOR NIKON MOUNT CAMERAS (Full Frame, APS-C or Film Cameras)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EX 8mm F4 (Nikon) (circular image on full frame) (Full fisheye effect on APS-C Digital Cameras) * </td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$900.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 15mm f2.8 Fisheye (mechanical iris) * </td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$750.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>35mm f1.4 DG HSM ART</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>EX 50mm f1.4 HSM</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>Nikon AF-S Micro NIKKOR 60mm f/2.8G ED</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$750.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td>EX 85mm F1.4 DG HSM</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 105 f2.8 Macro (1:1) (mechanical iris) * </td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$550.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>EX 12-24mm F4.5-5.6 II DG HSM</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>EX 15-30mm f3.5-4.5 (mechanical iris) * </td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$700.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>EX 24-70mm f/2.8 IF DG HSM</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td>EX 50-500mm F4-6.3 DG OS HSM (image stabilized)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,800.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td><b>NEW</b> Nikon AF-S 70-200mm f2.8G II ED</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$2,300.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EX 80-400mm F4.5-5.6 OS HSM (image stabilized)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>EX 1.4x APO Teleconverter</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>EX 2X APO Teleconverter</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Sigma USB Dock for Nikon Mount Sigma Lenses</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money-replace'>$40.00 </td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td colspan="6">* These lenses are not fully compatible (will not auto focus) with Nikon cameras that require 'G' series lenses. However with their mechanical aperatures these lenses are fully compatible with older Nikon film & digital bodies. (Also ideal for use with HDSLR cameras)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>SIGMA LENSES FOR NIKON MOUNT DIGITAL CAMERAS (APS-C ONLY)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>EX 10mm F2.8 DC Fisheye (16mm equivalent)(Full Frame fisheye for APS-C Cameras)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$700.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>EX 10-20mm F3.5 HSM DC (16-35mm Equivalent)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$750.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>EX 17-50mm F2.8 DC OS HSM (28-75mm equivalent) (image stabilized)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$800.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Note: Sigma HSM DC Lenses are compatible with all Nikon APS-C Cameras!</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>TTL & iTTL FLASHES FOR NIKON MOUNT CAMERAS (FILM & DIGITAL)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Metz 58-AF1 Flash (Fully iTTL compatible with all Nikon Digital models)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Includes: Sto-fen flash diffuser, flash stand (for remote wireless placement) & pouch.</td></tr>
 <tr><td colspan="6">Note: Flash requires (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Zeikos I-TTL Flash Cord w/Bracket (Compatible with ALL Nikon Digital Bodies)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$125.00</td>	<td class='f-right-money-replace'>$75.00</td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>MISC ACCESSORIES FOR NIKON CAMERAS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Nikon MC-30 Wired Remote</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money-replace'>$20.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td class='f-rental-title-line'>TTL & iTTL FLASHES FOR NIKON MOUNT CAMERAS (FILM & DIGITAL)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Metz 58-AF1 Flash (Fully iTTL compatible with all Nikon Digital models)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Includes: Sto-fen flash diffuser, flash stand (for remote wireless placement) & pouch.</td></tr>
 <tr><td colspan="6">Note: Flash requires (4) 'AA' batteries (not included)</td></tr>
 <tr><td>Zeikos I-TTL Flash Cord w/Bracket (Compatible with ALL Nikon Digital Bodies)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$125.00</td>	<td class='f-right-money-replace'>$75.00</td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>MISC ACCESSORIES FOR NIKON CAMERAS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Nikon MC-30 Wired Remote</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money-replace'>$20.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>FUJIFILM X-SERIES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>FUJI Fujinon XF LENSES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">Designed especially for the Fujifilm X-Series, the FUJINON XF Lens series promises enhanced resolution and light volume in image edge areas as well as reduced chromatic aberration for exceptional image quality.</td></tr>
 <tr><td>XF 14mm F2.8 R</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>XF 18mm F2 R</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>XF 23mm F1.4 R</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$1,000.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>XF 35mm F1.4 R</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>XF 56mm F1.2 R</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$450.00</td></tr>
 <tr><td>XF 10-24 F4 R OIS</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,100.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$450.00</td></tr>
 <tr><td>XF 18-135 F3.5-5.6 R LM OIS WR</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>XF 55-200mm F3.5-4.8 R LM OIS</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$350.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>Zeiss Touit for Fujifilm X-Mount</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">The autofocus capability of the Touit lenses makes them the dream partners for the Fujifilm X Series cameras. Thanks to extremely low distortion and outstanding absorption of stray light , they can reveal the full potentials the FujifilmX-Trans CMOS sensor.</td></tr>
 <tr><td>Touit 2.8/12</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,250.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>Touit 1.8/32</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$900.00</td>	<td class='f-right-money-replace'>$700.00</td>	<td class='f-right-money-hide'>$450.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>FUJI Shoe Mount Flash EF-42</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$350.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td><td> </td></tr>
 <?php
 /*
 <tr><td class='f-rental-title-line'>LENS ADAPTERS FOR FUJIFILM X SERIES CAMERA</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Novoflex Fuji X to Leica M</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Novoflex Fuji X to Nikon</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 */
 ?>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>PANASONIC LUMIX</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>Micro Four Thirds Manual Focus Voigtländer Nokton LENSES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>PRO KIT Voigtländer 'Nokton' Bundle, With 10.5mm, 17.5mm, 25mm and 42.5mm F/0.95</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$480.00</td>	<td class='f-right-money'>$4,500.00</td>	<td class='f-right-money-replace'>$2,500.00</td>	<td class='f-right-money-hide'>$1,500.00</td></tr>
 <tr><td>Voigtländer Nokton 10.5mm f/0.95 Lens for Micro 4/3 Cameras</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$160.00</td>	<td class='f-right-money'>$1,400.00</td>	<td class='f-right-money-replace'>$800.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>Voigtländer Nokton 17.5mm f/0.95 Lens for Micro 4/3 Cameras</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$1,200.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>Voigtländer Nokton 25mm f/0.95 Type II Lens for Micro 4/3 Cameras</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$1,200.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td>Voigtländer Nokton 42.5mm f/0.95 Lens for Micro 4/3 Cameras</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$1,200.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>PANASONIC Micro Four Thirds Auto Focus LENSES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Leica DG Macro-Elmarit 45mm f/2.8 ASPH.  (Micro 4/3) (90mm equivalent) (image stabilized)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$1,000.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
  <tr><td>Leica DG Vario-Elmarit 8-18mm f/2.8-4 ASPH</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$1,500.00</td>	<td class='f-right-money-replace'>$1000.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>
  <tr><td>Panasonic G Vario 45-200mm f/4-5.6 OIS</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$1,500.00</td>	<td class='f-right-money-replace'>$1000.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>

 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>PANASONIC LUMIX FULL FRAME (L SYSTEM)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td><b>New</b> Panasonic DC-S1</td>	<td class='f-right-money'>$175.00</td>	<td class='f-right-money'>$700.00</td>	<td class='f-right-money'>$3,500.00</td>	<td class='f-right-money-replace'>$2400.00</td>	<td class='f-right-money-hide'>$1750.00</td></tr>
 <tr><td><b>New</b> Panasonic Lumix S 24-105mm F4 Macro OIS</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$1,800.00</td>	<td class='f-right-money-replace'>$1200.00</td>	<td class='f-right-money-hide'>$900.00</td></tr>
 <tr><td><b>New</b> Panasonic DMW=-XLR1 XLR Microphone Adaptor</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$350.00</td>	<td class='f-right-money-hide'>$250.00</td></tr>
  
 <tr><td colspan="6">&nbsp;</td></tr>
 <?php
 /*
 <tr><td class='f-rental-title-line'>LENS ADAPTERS (For MFT (Micro Four Thirds) cameras) (still & video)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Novoflex Leica 'M' -> MFT Lens Adapter</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">(Allows the use of Leica M mount lenses on MFT cameras)</td></tr>
 <tr><td>Voightlander Nikon F -> MFT Lens Adapter</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">(Allows the use of manual diaphram Nikon mount lenses on MFT cameras)</td></tr>
 <tr><td>Panasonic (DMW-MA1) 4/3 -> MFT micro 4/3 lens adapter *</td>	<td class='f-right-money'>$12.00</td>	<td class='f-right-money'>$48.00</td>	<td class='f-right-money'>$275.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">* Due to the weight of many of the lenses used, it is recommended that 15mm LW rails & lens supports are used to avoid possible damage to camera. (see page 10)</td></tr>
 */
 ?>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>ANALOG FILM CAMERA AND ACCESSORIES </td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>SLR AND RANGEFINDER CAMERAS & LENSES (35MM)</td></tr>
 <tr><td class='f-rental-title-line'>NIKON FILM BODIES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Nikon F100 Body Only (Film)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <?php
 /*
 <tr><td colspan="6">Leica 'M' Lens Adapters</td></tr>
 <tr><td>Novoflex Leica M - Micro 4/3 Adapter</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 */
 ?>
 <tr><td colspan="6" class='f-rental-title'>DIGITAL AND ANALOG MEDIUM FORMAT CAMERAS, LENSES & ACCESSORIES</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <?php
 /*
 <tr><td class='f-rental-title-line'>Fujifilm GFX 50s</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">The FUJIFILM GFX 50S medium format mirrorless digital camera combines an extraordinary sensor, processor and design with FUJINON lenses to give you the ultimate photography experience. The innovative GFX system uses a FUJIFILM exclusive 43.8 x 32.9mm, 51.4MP CMOS sensor with approximately 1.7x the area of a 35mm sensor. The GFX 50S 51.4MP sensor shows its true potential when making large format prints, capturing majestic landscape photos or reproducing historical documents. The camera is supported by GF lenses that encapsulate the design philosophy of FUJINON lenses</td></tr>

<tr><td><b>NEW</b> Fujifilm GFX 50s Body (51.4MP CMOS Sensor - 43.8 x 32.9 mm)</td><td class='f-right-money'>$320.00</td><td class='f-right-money'>$1,300.00</td><td class='f-right-money'>$8,000.00</td><td class='f-right-money-replace'>$5,500.00</td><td class='f-right-money-hide'>N/A</td></tr>
*/
?>
<?php //<tr><td>Fujifilm GF 23mm F4 R LM WR</td><td class='f-right-money'>$75.00</td><td class='f-right-money'>$300.00</td><td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$2,000.00</td><td class='f-right-money-hide'>N/A</td></tr> ?>
<tr><td>Fujifilm GF 45mm F2.8 R WR</td><td class='f-right-money'>$50.00</td><td class='f-right-money'>$200.00</td><td class='f-right-money'>$2,000.00</td><td class='f-right-money-replace'>$1,350.00</td><td class='f-right-money-hide'>N/A</td></tr>
<?php //<tr><td>Fujifilm GF 63mm F2.8 R WR</td><td class='f-right-money'>$50.00</td><td class='f-right-money'>$200.00</td><td class='f-right-money'>$1,800.00</td><td class='f-right-money-replace'>$1,200.00</td><td class='f-right-money-hide'>N/A</td></tr>?>
<tr><td>Fujifilm GF 110mm F3 R LM WR</td><td class='f-right-money'>$75.00</td><td class='f-right-money'>$300.00</td><td class='f-right-money'>$3,500.00</td><td class='f-right-money-replace'>$2,300.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>Fujifilm GF 120mm F4 R LM OIS WR Macro</td><td class='f-right-money'>$75.00</td><td class='f-right-money'>$300.00</td><td class='f-right-money'>$3,300.00</td><td class='f-right-money-replace'>$2,200.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>Fujifilm GF 250mm F4 R LM OIS WR</td><td class='f-right-money'>$100.00</td><td class='f-right-money'>$400.00</td><td class='f-right-money'>$4,130.00</td><td class='f-right-money-replace'>$2,700.00</td><td class='f-right-money-hide'>N/A</td></tr>
<?php //<tr><td><b>NEW</b> Fujifilm GF 32-64mm F4 R LM WR</td><td class='f-right-money'>$75.00</td><td class='f-right-money'>$300.00</td><td class='f-right-money'>$2,800.00</td><td class='f-right-money-replace'>$1,800.00</td><td class='f-right-money-hide'>N/A</td></tr>?>
<tr><td>Fujifilm 1.4X TC WR Teleconverter (only for GF 250mm)</td><td class='f-right-money'>$25.00</td><td class='f-right-money'>$100.00</td><td class='f-right-money'>$1,000.00</td><td class='f-right-money-replace'>$600.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>Fujifilm MCEX-18G WR Macro Extension Tube</td><td class='f-right-money'>$20.00</td><td class='f-right-money'>$80.00</td><td class='f-right-money'>$400.00</td><td class='f-right-money-replace'>$275.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>Fujifilm MCEX-45G WR Macro Extension Tube</td><td class='f-right-money'>$20.00</td><td class='f-right-money'>$80.00</td><td class='f-right-money'>$400.00</td><td class='f-right-money-replace'>$275.00</td><td class='f-right-money-hide'>N/A</td></tr>

<?php
/*
<tr><td colspan="6">&nbsp;</td></tr>
<tr><td colspan="6" class='f-rental-title-line'>Travel Kits</td></tr>
<tr><td>GFX 50s body with two lens <b>(Any two)</b></td><td class='f-right-money'>$425.00</td><td class='f-right-money'>$1,700.00</td><td class='f-right-money'>$16,000.00</td><td class='f-right-money-replace'>$10,500.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>GFX 50s body with one <b>Group 1</b> lens</td><td class='f-right-money'>$375.00</td><td class='f-right-money'>$1,500.00</td><td class='f-right-money'>$13,000.00</td><td class='f-right-money-replace'>$8,500.00</td><td class='f-right-money-hide'>N/A</td></tr>
<tr><td>GFX 50s body with one <b>Group 2</b> lens</td><td class='f-right-money'>$350.00</td><td class='f-right-money'>$1,400.00</td><td class='f-right-money'>$10,000.00</td><td class='f-right-money-replace'>$6,500.00</td><td class='f-right-money-hide'>N/A</td></tr>

<tr><td colspan="6">Includes: Body, choice of one/two lens, 32GB SDHC, Travel Case. </td></tr>
*/
?>
<tr><td colspan="6">&nbsp;</td></tr>
<tr><td colspan="6">&nbsp;</td></tr>



 <tr><td colspan="6" class='f-rental-title'>PENTAX 645 LENSES & ACCESSORIES</td></tr>
 <tr><td colspan="6">With the addition of the Pentax 645D or 645Z to the digital medium formatt game. Not to mention a resurgance of interest in medium formatt film cameras, Leo's has added a selection of the Pentax 645 lenses to our rental fleet.</td></tr>
 <tr><td colspan="6">Note: When Pentax 645 lenses are used on the 645D or 645Z digital body, multiply focal length by 1.3x for the effective focal length!</td></tr>
 <tr><td class='f-rental-title-line'>PENTAX 645 LENSES (Film & Digital)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Pentax HD DA645 28-45mm f/4.5 ED AW SR</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$6,000.00</td>	<td class='f-right-money-replace'>$3,500.00</td>	<td class='f-right-money-hide'>$2,000.00</td></tr>
 <tr><td>Pentax 90mm f/2.8 D FA 645 Macro ED AW SR</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$5,500.00</td>	<td class='f-right-money-replace'>$3,000.00</td>	<td class='f-right-money-hide'>$1,750.00</td></tr>
 <tr><td>645-A 150mm F3.5 (manual focus)</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>645-A 300mm F4 ED-IF * (manual focus)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$2,000.00</td>	<td class='f-right-money-replace'>$1,000.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td>645-FA 80-160mm F4.5 (auto focus)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$2,000.00</td>	<td class='f-right-money-replace'>$1,000.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Note: Pentax 645 lenses are supplied with UV protective filters & hoods</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>PENTAX 67II SYSTEM (6X7)</td></tr>
 <tr><td colspan="6">The camera which looks like a Pentax K1000 on steroids, yet is no more difficult to operate than a regular 35mm. Great for available light photography. Compatible with both 120 & 220 roll films.</td></tr>
 <tr><td class='f-rental-title-line'>PENTAX 67II SYSTEM (6X7)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>67II Body w/AE metered prism</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td>SMC-P 45mm f4</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>SMC-P 55mm f4</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>SMC-P 75mm f2.8</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$105.00</td></tr>
 <tr><td>SMC-P 75mm f4.5 Shift Lens</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td>SMC-P 105mm f2.4</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>SMC-P 165mm f2.8</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>165mm f4 Leaf Shutter</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>PENTAX 67II TRAVELERS KIT</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>67II (3) Lens outfit</td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$240.00 </td>	<td class='f-right-money'>$4,500.00 </td>	<td class='f-right-money-replace'>$2,000.00 </td>	<td class='f-right-money-hide'>$550.00</td></tr>
 <tr><td colspan="6">Includes: Body, AE Prism, 55mm f4, 105mm f2.4, 165mm f2.8 and travel case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>VIDEO EQUIPMENT (HD)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Leo's Cameras has listened to the needs of our video shooters and have greatly increased the variety of gear that we can now supply. We rent cameras from Canon, Panasonic and GoPro. To accompany these cameras we are able to supply an ever increasing array of accessories including editing decks, Lighting, audio equipment and a variety of support equipment.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CONSUMER VIDEO CAMERAS (HD - SD CARD)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Panasonic HCX1000</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money'>$320.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td colspan="6">Panasonic's HC-X1000 4K DCI/Ultra HD/Full HD Camcorder can shoots and records cinema 4K at a true 24p, and UHD at broadcast compatible frame rates, so it fits smoothly into your existing broadcast workflow. The HC-X1000 features a 1/2.3" MOS sensor that is always shooting at 4K resolution, and uses its two built-in Venus processing engines to scale the 4K image for Full HD delivery.</td></tr>
 <tr><td colspan="6">Includes: (1) 32GB Sandisk SDHC HD Video Card, (2) Li-Ion batteries, charger.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CONSUMER VIDEO CAMERAS (HD - HDV Tape)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Canon Vixia HV40 (HDV) (1-Chip)</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$800.00</td>	<td class='f-right-money-replace'>$400.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Compact fully featured HDV camera with 10x optically stabilized lens, manual exposure & manual audio controls. Camera can record in full 1080x1920 resolution in 24P & 30P frame rates. This camera supports external microphones input as well as LANC camera controllers & headphone monitoring. Records to mini DV Tapes (not included).</td></tr>
 <tr><td colspan="6">Note: Recorded video is downloaded to computer via firewire. Clients need to ensure computers compatability!</td></tr>
 <tr><td colspan="6">Includes: (2) Li-Ion batteries, AC Adapter, remote, Analog & Digital Output cables, firewire & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Century Precision Optics (43mm thread) (Compatible with both Canon Vixia HV40)</td></tr>
 <tr><td>#0HD-65CV-43 .3x Ultra Fisheye (43mm)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>#0HD-FEWA-43 .65x Wide Angle Converter (zoom through) (43mm)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$400.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>WATER PROOF POV CAMERA (HD)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>GoPro HERO 4 BLACK EDITION POV Camera (HD)(1-Chip)</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$650.00</td>	<td class='f-right-money-replace'>$400.00</td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td colspan="6">HERO 4 Black has 2x more powerful processor that delivers super slow motion at 240 frames per second. Incredible high-resolution 4K30 and 2.7K601 video combines with 1080p120 and 720p240 slow motion to enable stunning, immersive footage of you and your world. ProtuneTM settings for both photos and video unlock manual control of Color, ISO Limit, Exposure and more. Waterproof to 131' (40m) with 12MP photos at 30 frames per second and improved audio.</td></tr>
 <tr><td colspan="6">Includes: Waterproof housing, (2) Li-Ion batteries, (2) chargers (12V Cigarette & 120V AC Charger), 32GB Micro SD memory card, WiFi Remote, helmet mount, head strap, chest harness, rollbar mount, seatpost mount, tripod mount, card reader and case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>360° POV CAMERA (HD)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td>Replacement Value Only **</td><td>&nbsp;</td></tr>
 <tr><td>Ricoh Theta V Spherical 360° VR Camera</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$550.00</td></tr>
 <tr><td colspan="6">Ricoh Theta V features two 12MP sensors and a twin-lens optical system that captures two wide-angle images and automatically stitches them into one complete spherical image. It allows you to capture comprehensive 360° 4K videos and 12MP photos, as well as live stream 360° footage in 4K</td></tr>
 <tr><td colspan="6">Includes: USB Cable, tripod extension</td></tr>
 <tr><td colspan="6">(** Since the Theta V have two dome shape lenses on both side of the camera. Handle with extremly caution is needed. Any scratch of any size on the camera, customer have to pay for a camera for replacement. )</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>VIDEO CAMERAS (SD) (ANALOG/DIGITAL)</td></tr>
 <tr><td colspan="6">Have older 8mm or Hi-8mm tapes & need to convert them to a digital format? This camera gives the user the ability to convert their older 8mm & Hi-8mm tapes without the need for an extra Analog-Digital converter (it's built in!).</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sony DCR-TRV315 (Digital 8)</td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">One of Sony's premium 'Digital-8' camcorder, the TRV-315 is feature packed! Add as a option, an external microphone (wired or wireless) & have the ability of monitoring the sound via standard 3.5mm headphone jack. The camera also offers a LANC control part thus allowing for remote wired camera operation. </td></tr>
 <tr><td colspan="6">Note: Recorded video is downloaded to computer via firewire. Clients need to ensure computers compatability!</td></tr>
 <tr><td colspan="6">Includes: (1) NP-F970 Li-Ion battery, charger, remote, analog & digital cables, manual & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>VIDEO CAMERAS (SD) (ANALOG)</td></tr>
 <tr><td colspan="6">Don't need the ability to edit on the computer? This easy to use camera may be for you, and if you change your mind you can rent our Sony Digital 8 camera which acts as an analog to digital converter.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Canon ES 5000 (Hi-8)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">The Canon ES-5000 was one of their last fully featured Hi-8mm camcorders. With its 20X optically stabilized lens, mic input, headphone jack & LANC control ports this camcorder offered many capabilites not normally found in a analog camera of this size.</td></tr>
 <tr><td colspan="6">Includes: (1) BP-9xx Li-Ion battery, charger, DC coupler, remote, analog video cables, manual & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>ACCESSORIES FOR DSLR WITH VIDEO, PROSUMER / PROFESSIONAL VIDEO CAMERAS</td></tr>
 <tr><td colspan="6" class='f-rental-title'>VIDEO PRODUCTION ACCESSORIES</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>MATTE BOXES, FOLLOW FOCUS, 15MM LW RAILS & SHOULDER RIGS FOR VIDEO AND DSLR CAMERAS</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Vocas DSLR Shoulder Rig</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">Premium quality modular shoulder rig based on the industry standard 15mm LW rod system. Suitable for all DSLR's without battery pack attached!</td></tr>
 <tr><td>Vocas MB-255 Premium CF 4X4 Mattebox Kit</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$900.00 </td>	<td class='f-right-money-replace'>$450.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Premium quality 4x4 carbon fiber mattebox w (2) filter stages ((1) fixed & (1) rotating). Compatible with lenses up to 114mm diameter (such as the Zeiss CP2 cine lenses). Mattebox also includes internal eyebrows, french flag & adjustable height 15mm LW bars adapter.</td></tr>
 <tr><td>Vocas MFC-1 Universal Follow Focus Kit</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$1,200.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$400.00 </td></tr>
 <tr><td colspan="6">This highly adjustable premium quality follow focus is compatible with all 15mm LW rail systems. Kit includes the MFC-1 follow focus with M0.8/46 tooth drive gear (this is the standard drive gear pitch & tooth configuration for most PL mount cine lenses) & (2) flexible lens gears suitable for lenses up to 100mm outside diameter. Ideal for use with HDSLR camera packages as well as the Sony NEX-FS100 & Panasonic HMC-AF100 large sensor video cameras.</td></tr>
 <tr><td colspan="6">Note: requires the use of 15mm LW rails for mounting.</td></tr>
 <tr><td>Vocas 15mm LW DV Pro Rails (#0350-0600)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$700.00</td>	<td class='f-right-money-replace'>$350.00</td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td colspan="6">Precision machined 15mm LW rails (350mm long) with large adjustable platform suitable for use with mid-sized cameras such as the Sony EX models, Panasonics AG-AF100, HMC-150P, AC-160P & Canons XF series of cameras.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>SYSTEM FILTERS (4X4 & 4X5.65) FOR HDSLR & HD VIDEO CAMERAS</td></tr>
 <tr><td colspan="6">Leos's only rents premium quality 4x4 (Glass System) filters from Schneider & Formatt. These filters are manufactured to the highest quality using only white water glass. Therefore no loss of image quality regardless of the recording medium.</td></tr>
 <tr><td colspan="6">4x4 (Glass Filter Kits)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Kit #1 (Essential Kit) (Schneider Circular True Pol / Formatt ND 0.6 STD / Formatt ND 0.9 STD / Format ND 0.6 Grad SE / Format Supermist Black 1/8)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>Kit #2 (ND Kit) Formatt ND 0.3 STD / ND 0.6 STD / ND 0.3 SE Grad / Schneider ND 0.6 SE Grad / ND 0.9 SE Grad</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>Kit #3 (Colour Grad Kit) Formatt Cool Blue Grad #2 / Sunset Grad #2 / Skyfire Grad #2/Tabac Grad #2/Twilight Grad #2</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>Kit #4 (Special Effects Kit) Formatt 4 Point Star/Warm Supermist Black 1/4 /Supermist Clear 1/4 /Low Contrast #4 /Cool Day For Night</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>PORTABLE MONITORS & CABLES</td></tr>
 <tr><td class='f-rental-title-line'>HD MONITORS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Ikan VX7e (7" HD-SDI LCD Monitor)</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$900.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Improved version of the VX-7 HD-SDI LCD monitor. All the features of the original VX-7 with the addition of 'false colour' as well as 'peaking' (full colour & monochrome) & 'Blue Screen'.</td></tr>
 <tr><td colspan="6">Includes: Monitor / (3) BNC-RCA Adapters / Component Cable / HDMI-HDMI cable / HDMI-Mini HDMI cable / AC Adapter / LP-E6 Battery Plate & charger / (2) LP-E6 batteries / 4-Pin XLR-PT power cable / Manual / Ball Head mount / Clamp Mount / Case.</td></tr>
 <tr><td colspan="6">Note: Monitors can be powered from professional battery systems that utilize a 'power tap' connection via supplied 4-Pin XLR-PT cable. (see page 16)</td></tr>
 <tr><td>20' HDMI -> HDMI Mini Cable (Ideal for use with cambo boom)</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money-replace'>$30.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td>20' Component Video Cable (ideal for use with Cambo Video Boom)</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money-replace'>$30.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>REMOTE CAMERA CONTROLLERS AND LCD MAGNIFIER</td></tr>
 <tr><td colspan="6">Allows precise control of focus & zoom as well as the cameras record functions without having to touch the camera. Great when operating from either a tripod or any sort of stabilized situation or remote camera operation! (capabilities of controller may vary based on camera).</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Manfrotto #521 Lanc Controller (Canon / Sony) (not compatible with Sony EX Cameras)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>20' Lanc Extension Cable (for Canon & Sony) (not compatible with Sony EX Cameras)</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money-replace'>$20.00</td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td>Varizoom EX-1 / EX3 Controller (Sony)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$175.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Varizoom EX-1 / EX3 20' Ext. Cable (Sony)</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money-replace'>$20.00</td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td>Varizoom 20' Extension Cable for Panasonic Controller</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money-replace'>$30.00</td>	<td class='f-right-money-hide'>$15.00</td></tr>
 <tr><td colspan="6">(supports record, zoom, focus & aperture controls of the varizoom controller)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>DIGITAL MEDIA, STORAGE, READERS & ACCESSORIES</td></tr>
 <tr><td class='f-rental-title-line'>DIGITAL MEDIA STORAGE</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sandisk Extreme IV 8GB Compact Flash Cards (45 MB/sec)</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money-replace'>$30.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td>Sandisk Extreme 16GB Compact Flash Cards (60 MB/sec)</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$60.00</td>	<td class='f-right-money-hide'>$40.00</td></tr>
 <tr><td>Sandisk Extreme Pro 32GB SDHC Cards (45 MB/sec)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money-replace'>$100.00</td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>CARD READERS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sandisk Extreme CF Firewire Card Reader</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$40.00</td>	<td class='f-right-money-hide'>$40.00</td></tr>
 <tr><td colspan="6">The Sandisk Extreme CF firewire card reader is still the card reader of choice for serious shoters. With transfer speeds of up to 45 MB/sec without any of the 'bottle necks' associated with USB, this firewire 400/800 reader is a must when downloading large cords.</td></tr>
 <tr><td colspan="6">Includes: 6 to 9 pin cable, 9 to 9 pin/cable & case</td></tr>
 <tr><td colspan="6">*note: your computer must have either a 6pin or 9pin firewire port for this reader to function.</td></tr>
 <tr><td>Delkin USB 3.0 Universal Card Reader</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money-replace'>$10.00</td>	<td class='f-right-money-hide'>$5.00</td></tr>
 <tr><td colspan="6">Card reader supports CF (Type I, II & UDMA), SD (SD, SDHC & SDXC), Micro SD, Memory Stick (MS, MSDUO, MS Pro, MS Pro Duo & MS Micro) & XD Card formats. The Delkin USB 3.0 Card is not only highly flexible due to card compatability but also very fast (capable of speeds up to 5 Gb/sec.)</td></tr>
 <tr><td colspan="6">Includes: USB 3.0 cable & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>MONITOR & PRINTER CALIBRATOR</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">The simple way to color calibrate your camera, display, projector and printer with a broad range of options and control to ensure the color you see is exactly the color you will get. Designed specifically for photographers, the X-Rite ColorMunki Photo Color Management Solution provides display, projector and RGB/CMYK printer profiling in an easy-to-use all-in-one integrated solution with a streamlined interface, enabling photographers to easily, quickly and affordably match colors from display to print. The ColorMunki Photo software provides particular accuracy when profiling for FleshTones along with providing excellent grey balance for neutral Black & White output.</td></tr>
 <tr><td>X-Rite Colormunki Photo</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$700.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td colspan="6">Includes: ColorMunki Spectrophotometer, USB Cable, Protection Case with Monitor Holder, Software</td></tr>
 <tr><td colspan="6">System Requirements: MacOS X 10.7.x or Higher, Microsoft Windows 7® 32 or 64 bit or Higher</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>DATACOLOR SPYDER LENSCAL</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money-replace'>$50.00</td>	<td class='f-right-money-hide'>$40.00</td></tr>
 <tr><td colspan="6">Spyderlenscal™ provides a fast, reliable method of measuring the focus performance on your camera and lens combinations. It allows photographers to obtain razor-sharp focusing or check to see that their lenses are working at their peak performance. This device is compact, lightweight and durable, with integrated level and tripod mount</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>TRIPODS, DOLLIES & MONOPODS FOR VIDEO</td></tr>
 <tr><td colspan="6">Leo's is able to supply a variety of tripods for almost all your needs. From a basic still tripod to heavier duty units capable of supporting most still & video needs. In addition, Leo's also supplies high quality true fluid heads by Miller Australia for the discerning user.</td></tr>
 <tr><td colspan="6" class='f-rental-title'>MILLER FLUID HEADS & TRIPODS</td></tr>
 <tr><td colspan="6">Miller's 'True' fluid head tripod systems offer the performance & reliability required by the descriminating operator! With their superior quality components including diseperate metal clutches & sealed counterbalance springs, Miller heads will operate flawlessly under (or out of) load from -30 to +40 degrees celcius (total operating range -40 to +65 degrees celcius). Match up with high quality & highly reliable tripods & you have a system for almost any situation!</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Miller DS-10 (CAT 850) w/Two Stage LW Aluminum Tripod (11lb capacity)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$1,600.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">Compact, lightweight fluid head capable of supporting loads up to 11 lbs, utilizing a single continuous drag clutch (w/no neutral setting) on the pan & tilt. Counterbalance had (2) settings for loads up to 11lbs.</td></tr>
 <tr><td colspan="6">Includes: DS-10 head, 2- stage LW Alu Tripod, mid-level adjustable spreader, (3) rubber feet, (2) camera plates, ((1) w/dv carriage & (1) w/ 1/4 & 3/8" screws) & padded case.</td></tr>
 <tr><td>Miller (CAT 1870) Compass 12 w/DV Solo CF (2-stage) Tripod (22lb capacity)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$1,000.00 </td>	<td class='f-right-money-hide'>$700.00</td></tr>
 <tr><td colspan="6">The Compass 12 is a addition to the Miller line-up. With (3) drag settings plus a neutral on both the pan & tilt along with (4) counter balances for cameras from 4.4-22lbs this system is the ideal match for the descriminating HDSLR or Prosumer Video User!</td></tr>
 <tr><td colspan="6">Includes: Compass 12 head, Solo DV CF 2-Stage Tripod, Pan-handle, (2) camera plates ((1) w/dv carriage & (1) w 1/4 & 3/8" screws) & padded case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>MANFROTTO FLUID EFFECT HEADS & TRIPODS</td></tr>
 <tr><td>Manfrotto #128LP // O55CL Video Package</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$175.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Manfrotto #501 // 745XB Video Package (with leveling column)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>TRIPOD DOLLIES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Manfrotto #181B Dolly</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$175.00</td>	<td class='f-right-money-hide'>$125.00</td></tr>
 <tr><td colspan="6">Easy to set up dolly, is ideal for prosumer weight cameras mounted on tripods with either 'single spike' or 'double spike' legs.</td></tr>
 <tr><td>Miller (CAT 481) Dolly</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money'>$1,000.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td colspan="6">This premium quality dolly is compatible with all 'double spike' video tripods (supports loads up to 220lbs). With it's large rubberized wheels, free wheel over a variety of low friction surfaces.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>MONOPODS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Manfrotto #679 Monopod</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money-replace'>$30.00</td>	<td class='f-right-money-hide'>$15.00</td></tr>
 <tr><td>Miller Solopod (CF Monopod)</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$32.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money-replace'>$125.00</td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>Miller Solopod (CF Monopod) w/Solopod quick release</td>	<td class='f-right-money'>$12.00</td>	<td class='f-right-money'>$48.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$200.00</td>	<td class='f-right-money-hide'>$125.00</td></tr>
 <tr><td>Portamount Pneumaticly dampened camera support (monopod)</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$700.00</td>	<td class='f-right-money-replace'>$450.00</td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6"> -Vertically dampened camera support ideal for helicopter or land based useage (24-36" working range)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>TRIPODS FOR PHOTO</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sirui M3204 Carbon Fiber Tripod with Sirui K-30 Ball Head</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$800.00</td>	<td class='f-right-money-replace'>$400.00</td>	<td class='f-right-money-hide'>$275.00</td></tr>
 <tr><td>Gitzo G2271M // 055CL Photo Package (Pan Tile Head)</td>	<td class='f-right-money'>$12.00</td>	<td class='f-right-money'>$48.00</td>	<td class='f-right-money'>$350.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>Manfrotto #468RC2 // 055CLB Photo Package (Ball Head w/QR)</td>	<td class='f-right-money'>$12.00</td>	<td class='f-right-money'>$48.00</td>	<td class='f-right-money'>$350.00</td>	<td class='f-right-money-replace'>$200.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Manfrotto #468 // 055XB Photo Package (Ball Head)</td>	<td class='f-right-money'>$12.00</td>	<td class='f-right-money'>$48.00</td>	<td class='f-right-money'>$350.00</td>	<td class='f-right-money-replace'>$200.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Jobu Gimbal / 755XB</td>	<td class='f-right-money'>$35.00</td>	<td class='f-right-money'>$140.00</td>	<td class='f-right-money'>$700.00</td>	<td class='f-right-money-replace'>$300.00</td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Manfrotto</td></tr>
 <tr><td>Manfrotto #302 PLUS / 755B QTVR (cylindrical) Panoramic Photo Package</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$900.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Note: The included #755B Tripod includes a leveling center column to ease setup.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CAMERA STABILIZERS, SLIDERS, PORTABLE TRACK SYSTEMS & CAMERA CRANES</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CAMERA STABILIZERS</td></tr>
 <tr><td colspan="6">Hollywood Lite Stabilizers are designed primarily to eliminate unwanted motions while working with all types of moving shots. These stabilizers allow you to, walk/run/go up and down stairs & even travel on all uneven terrain.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Hollywood Lite VS-1 (Supports up to 3 LB)</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$150.00</td></tr>
 <tr><td>ABC Clip 'N' Go (Supports up to 6 LB)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$250.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Manfrotto #595B Fig Rig</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$450.00</td>	<td class='f-right-money-replace'>$225.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">This unique & award winning system becomes literally part of the body to produce smooth & steady travelling shots. Mount a variety of accessories (from microphones & lights) yet maintain the ease of use of a small camera.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CAMERA DOLLIES AND SLIDERS</td></tr>
 <tr><td>Micro Dolly Portable Track System</td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money'>$2,500.00 </td>	<td class='f-right-money-replace'>$1,500.00 </td>	<td class='f-right-money-hide'>$800.00</td></tr>
 <tr><td colspan="6">This highly portable track and dolly system weighs less than 10lbs yet sets up in less than 2 minutes into a 13 foot long section of track. Now includes the push bar for easier (2) person operation). (Requires the use of a tripod utilizing dual spiked feet).</td></tr>
 <tr><td colspan="6">(Note: This track system is not suitable for use with the cambo V40 boom)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Slider</td></tr>
 <tr><td>iFootage Shark Slider S1</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$800.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td colspan="6">The Japan imported silent bearings last about five times longer than standard bearings, even with constant use over prolonged periods of time. Also imported, the synchronous belts provide high precision and torque, with excellent performance even in high load situations. The carbon fibre tubes and the three wheel locking system, make it almost impossible to be bent out of shape when the manufacturer load limit is respected. By far more robust than a traditional slider, Shark Slider S1 benefits from increased resistance and can be used under any weather conditions.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Crane</td></tr>
 <tr><td>iFootage Minicrane M5 with Wild Bull Tripod & Panoramic Unit</td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">The Minicrane M5 is the industry standard lightweight camera crane system. Fitted with a two arm design for maximum stability and built with aircraft grade aluminium, it's as robust and lightweight as it is functional. It weighs only 1.5kg, becoming so easy to carry around that it will change how you feel about jibs. Unlike its sibling Minicrane M1, the M5 allows an extra 2kg load boosting its maximum load to 7kg.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Cambo V40 Camera Boom w/PT-90 Pan Tilt Head</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money'>$1,200.00</td>	<td class='f-right-money'>$5,000.00</td>	<td class='f-right-money-replace'>$3,000.00</td>	<td class='f-right-money-hide'>$2,000.00</td></tr>
 <tr><td colspan="6">This easy to assemble camera boom has 10 1/2" reach and a motorized Pan & Tilt head that supports cameras up to 16 lbs. Motorized heads is powered either by AC or via optional 12V Battery Belt (requires 4 Pin XLR connection)</td></tr>
 <tr><td colspan="6">Includes: V40 boom, P90 Pan Tilt Head w/controller, Miller HD CF Tripod, Transport Cases & Counterweights.</td></tr>
 <tr><td>Cambo V40 Boom w/o PT-90 Head (supplied with 100m bowl, bowl adapter for flat based heads)</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$800.00</td>	<td class='f-right-money'>$2,500.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$1,000.00</td></tr>
 <tr><td colspan="6">Need the reach without the extra cost or capability of the PT-90 pan tilt head.</td></tr>
 <tr><td colspan="6">Includes: V40 boom, 100mm bowl adapter, counterweights, Miller HD CF Tripod & Cases.</td></tr>
 <tr><td colspan="6">** Recommeded Accessories</td></tr>
 <tr><td colspan="6">(A) Lanc style camera controllers (control zoom, focus, rec functions & on some models exposure control) (specify camera model) (see page 10)</td></tr>
 <tr><td colspan="6">(B) Portable monitors (SD or HD) (see page 10)</td></tr>
 <tr><td colspan="6">(C) 12V/13.2V Battery Belt for DC operation of PT-90 head. (see page 13)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>AUDIO EQUIPMENT</td></tr>
 <tr><td colspan="6" class='f-rental-title'>WIRELESS MIC SYSTEMS</td></tr>
 <tr><td class='f-rental-title-line'>LECTROSONICS WIRELESS UHF AUDIO SYSTEMS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">When reliability and consistancy is a priority, Lectrosonics wireless microphones systems are the answer. Lectrosonics is the solution to any cinematic or broadcast project. When booking your Lectrosonics packages, please specify whether you require a belt pack transmitter with a lavalier mic or a plug on transmitter, (converts your hand held mic to wireless operation).</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Lectrosonics UHF Wireless UCR100/UM100 ** (enquire for available blocks)</td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$700.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Frequency Agile (256 Channels)(100m/w Transmitter).</td></tr>
 <tr><td>Lectrosonics UHF Wireless UCR411/UM (enquire for available blocks)</td>	<td class='f-right-money'>$75.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$1,500.00 </td>	<td class='f-right-money-hide'>$1,000.00</td></tr>
 <tr><td colspan="6">The Lectro 411 Digital Hybrid Receiver is their premium performance unit. This versatile receiver incorporates the same smart diversity technology & built in spectrum analyzer as its little brother but also is suitable for rack mount & quad box operations for the professional sound mixer. Various transmitter options are available to meet any discriminating users needs.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Sennheiser G3</td></tr>
 <tr><td colspan="6">Sennheiser G3 system is the latest addition to Sennheiser's Evolution family of wireless microphones. The UHF wireless microphone system features 1,680 tunable frequencies, 21 frequency banks and 12 frequency presets per bank.</td></tr>
 <tr><td>Sennheiser Evolution ew 112-p G3 Camera-Mount Wireless Microphone System</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td colspan="6">This package includes the EK 100 G3 Receiver, SK 100 G3 Bodypack Transmitter and ME 2 lavalier microphone</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>BEC DV Camera bracket w/Lectrosonics Wireless Receiver Bucket</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">A practical method of attaching your Lectrosonic wireless receiver to a small or midsized camcorder (specify receiver model for correct receiver bracket). (Mounting brackets available for Lectrosonics UCR100 // UCR201 & UCR411 receivers)</td></tr>
 <tr><td colspan="6">** Specify what style of cable you require from receiver to camera (XLR, mini, RCA, 1/4" phono or stereo mini)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>WIRELESS IFB SYSTEMS (INTERRUPTIBLE FOLD BACK)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">Lectrosonics IFB systems are designed for use in broadcast & motion picture productions for high quality monaural audio for crew communications, program audio monitoring & talent queing.</td></tr>
 <tr><td>Lectrosonics T1/R1a UHF IFB (Receiver/Transmitter Pkg) *(available in Block 27/28)</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$1,000.00</td>	<td class='f-right-money-replace'>$500.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td>Lectrosonics Extra R1a IFB Receivers *</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$200.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">*NOTE: For hygenic reasons Lectro R1a receivers are not supplied w/earpieces (customers must supply their own)!</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>HARDWIRED MIC SYSTEMS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sennheiser K6 / ME66 XLR Shotgun microphone</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Mid length general purpose (powered) shotgun microphone suitable for wired or wireless applications (when matched up with Lectrosonics Plug-on transmitter). (Includes 1 Cable.)</td></tr>
 <tr><td>Sennheiser MKE 600 XLR Shotgun microphone</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">The MKE 600 from Sennheiser is a shotgun microphone designed for use with a camcorder or video DSLR. (Includes 1 Cable.)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Rode Video Mic Pro</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td colspan="6">The Rode Video Mic Pro is a true shotgun microphone with a 1/2" condensor capsule providing broadcast quality audio for a variety of devices that use the 1/8" (3.5mm) minijack connector!</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Gitzo #557/11510N 8' mic boom w/shockmount</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Includes: 25" XLR Cable & Case **</td></tr>
 <tr><td>Rode SM-3 Shockmount (hotshoe style)</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$95.00 </td>	<td class='f-right-money-replace'>$50.00 </td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td colspan="6">Basket style shock mount suitable for most shotgun mics to conventional camera shoe mount.</td></tr>
 <tr><td colspan="6">** A simple alternative to wireless microphones when combining with shotgun microphone for those hard to record situations.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>3-PIN XLR AUDIO CABLES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>12ft XLR Microphone Cable</td>	<td class='f-right-money'>$2.00 </td>	<td class='f-right-money'>$8.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money-replace'>$10.00 </td>	<td class='f-right-money-hide'>$5.00</td></tr>
 <tr><td>24ft XLR Microphone Cable</td>	<td class='f-right-money'>$2.00 </td>	<td class='f-right-money'>$8.00 </td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money-replace'>$12.00 </td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td>50ft XLR Microphone Cable</td>	<td class='f-right-money'>$2.00</td>	<td class='f-right-money'>$8.00</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money-replace'>$12.00</td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td>XLR 'Y' Adapter Cable ***</td>	<td class='f-right-money'>$2.00 </td>	<td class='f-right-money'>$8.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money-replace'>$10.00 </td>	<td class='f-right-money-hide'>$5.00</td></tr>
 <tr><td colspan="6">*** Splits a single mono audio source equally onto 2 channels.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>BATTERY SYSTEMS & POWER ADAPTERS (FOR VIDEO, HDSLR'S & 12V LIGHTING)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>ANTON BAUER</td></tr>
 <tr><td colspan="6">Anton Bauer has been the premier battery and charger system of choice for some of the world's top videographers. From the first one piece video camera to the latest in digital video and high definition. Anton Bauer innovations have kept the industry at the cutting edge of power solutions. Combining such inovations as intelligent chargers, quick change batteries & innovative battery compositions.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Anton Bauer Gold Mount System</td></tr>
 <tr><td colspan="6">The Anton Bauer 'Gold Mount' system (including: batteries, chargers, power adapters & modular belts) is the professional choice. Almost any situation where the need for flexability meets the requirements demanded by high power consumption, Anton Bauer has the solution! With their high capacity, high voltage (13.2V / 14.4V) batteries and the flexability to interface with a wide variety of cameras, monitors, lights & many other accessories, this system is king!</td></tr>
 <tr><td class='f-rental-title-line'>ANTON BAUER GOLD MOUNT BATTERIES & CHARGER PACKAGES</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Anton Bauer Dual Charger & (2) Dionic 90 (90wH/14.4V) Gold Link Batteries</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,600.00 </td>	<td class='f-right-money-replace'>$800.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">Includes: Anton Bauer 2401  Dual Position simultaneous charger (100-240V) w/(2) Dionic 90 Li-Ion Batteries (90 w/h) & Travel case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Anton Bauer Dionic 90 (90 wH/14.4V) Li-Ion Battery</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$450.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td>Anton Bauer #QR-XLH (7 1/4V Gold Mount Power Adapter)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Camera mounted power adapter for use on the Canon XL-1/XL-1S / XL-H1 & XL-H1S with 'Power-Tap' for powering a wide variety of accessories.</td></tr>
 <tr><td colspan="6">Note: batteries & charger not included.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Anton Bauer #QR-XL1 (7 1/4V Gold Mount Power Adapter)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Gold mount power adapter suitable for any Canon camera using the BP-9xx series of batteries. Includes 'Power-Tap' to allow the powering of a wide variety of accessories. Mounts to any 15mm LW rod system.</td></tr>
 <tr><td colspan="6">Note: batteries & charger not included.</td></tr>
 <tr><td>Anton Bauer #QR-VBG (7 1/4V Gold Mount Power Adapter)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Gold mount power adapter suitable for Panasonic cameras using the VBG series of batteries (AG-AF100 / AG-HMC160, etc). Includes 'Power-Tap' to allow the powering of a wide variety of accessories. Mounts to any 15mm LW Rod system.</td></tr>
 <tr><td colspan="6">Note: batteries & charger not included.</td></tr>
 <tr><td>Anton Bauer #QR-DSLR (7 1/4V Gold Mount Power Adapter)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Gold mount power adapter suitable for Canon HDSLR cameras using the LP-E6 series of battery (EOS 5DMK II, EOS7D & EOS 60D). Adapter includes 'Power Tap' to allow the powering of a wide variety of accessories. Mounts to any 15mm LW rod system.</td></tr>
 <tr><td colspan="6">Note: batteries & charger not included.</td></tr>
 <tr><td>Anton Bauer Snap-on 30/13 Modular Power Belt</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">This unique modular belt allows the user to power devices in either the 13V or 30V modes via either 4-Pin XLR or 2-Pin Amphenol connectors (but not simultaneously). Requires (2) 13.2V/14.4V Gold Mount Batteries for operation.</td></tr>
 <tr><td colspan="6">Note: Batteries & Charger not included.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>ANTON BAUER 12V/13.2V 4-PIN XLR BATTERY BELT & POWER ADAPTERS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Anton Bauer 13.2V (55 wh) 4-Pin XLR Battery Belt</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$700.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Includes: charger & 5->4 pin xlr power cable adapter.</td></tr>
 <tr><td>NRG Canon XL/XH/XF (BP-900) Series Cameras to 4 Pin (12V/13.2V) XLR Adapter</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money-replace'>$20.00 </td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td colspan="6">Note: 4-pin XLR battery belt & charger not included.</td></tr>
 <tr><td>NRG Canon L1/L2 to 4 Pin (12V/13.2V) XLR adapter</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money-replace'>$20.00 </td>	<td class='f-right-money-hide'>$10.00</td></tr>
 <tr><td colspan="6">Note: 4-pin XLR battery belt & charger not included.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>BATTERY SYSTEMS & POWER ADAPTERS FOR HDSLR CAMERAS</td></tr>
 <tr><td>Canon Battery Charger Kit (for EOS 5D MKII, EOS 5D MKIII, EOS 7D & EOS 60D)</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$250.00 </td>	<td class='f-right-money-hide'>$125.00</td></tr>
 <tr><td colspan="6">Note: charger provides simultaneous charging capabilities for 'chipped' & 'non-chipped' LP-E6 style batteries as well as 12V car charging capability.</td></tr>
 <tr><td colspan="6">Includes: (3) LP-E6 Li-Ion batteries, Dual position charger with AC/DC charging capabilities & case.</td></tr>
 <tr><td>Anton Bauer #QR-DSLR (7 1/4V Gold Mount Power Adapter)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Gold mount power adapter suitable for Canon HDSLR cameras using the LP-E6 series of battery (EOS 5DMK II, EOS 5DMK III, EOS7D & EOS 60D). Adapter includes 'Power Tap' to allow the powering of a wide variety of accessories. Mounts to any 15mm LW rod system.</td></tr>
 <tr><td colspan="6">Note: batteries & charger not included.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>LIGHTING</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CONTINUOUS LIGHT SOURCES (3200°K)(120V)</td></tr>
 <tr><td colspan="6">Quality light sources that are ideal for all uses; still, film, video, as well as, digital photography. These easy to use lights include broad beam, focusable as well as dedicated soft lights. Add a variety of Light modifiers to customize your particular situation.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>LOWEL</td></tr>
 <tr><td colspan="6">Our most popular tungsten balanced portable lighting option. Ideal for studio or location shoots of all types.</td></tr>
 <tr><td class='f-rental-title-line'>LIGHTING KITS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Lowel Provisions Kit (GO-92LBZ) **</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$750.00</td>	<td class='f-right-money-replace'>$400.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Includes: (2) Prolights, (2) barndoors, (2) stands, (2) umbrellas, (2) gel frames, (2) spare bulbs, (4) power cables & soft case.</td></tr>
 <tr><td colspan="6">** (note) A variety of lighting gels for colour correction & effect are available for sale, please enquire.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>CONTINUOUS LIGHT SOURCES (5600°k)(120V)(13.2V)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Litepanel Ringlite-Mini (26FC@10FT)</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$160.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$2,000.00</td>	<td class='f-right-money-hide'>$1,500.00</td></tr>
 <tr><td colspan="6">Achieve a beautiful catch light in a models eye or provide a soft enveloping light on a close-up subject. This daylight balanced ringlight works equally well on still or video cameras & unlike many ringlights, will mount to almost any camera & lens combination.</td></tr>
 <tr><td colspan="6">Includes: Ringlite-mini (spot), AC Adapter, 4 pin XLR (12V-14.4V) DC Battery Adapter cable, Anton Bauer Goldlink battery adapter, Filter pack, Adapter for 15mm LW or 15mm studio support rods & 15mm LW rail system & case.</td></tr>
 <tr><td colspan="6">**Note: 12V/13.2V Battery Belt or Anton Bauer Gold Link batteries & charger not included (see page 13)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Fiilex LED LIGHTS</td></tr>
 <tr><td colspan="6">Fiilex LED is a powerhouse in portable and compact lighting. This open-faced LED light fixture gives you intense specular output that allows you to shape or soften your light as necessary for the specific job at hand. The Fiilex boasts high functionality by harnessing Dynamic Color Tuning Technology, allowing you to move freely between different temperature light sources and take on the diverse world of existing ambient light. For image makers that demand portable lighting but aren't willing to sacrifice quality, the Fiilex can tackle a multitude of situations without the limiting drawbacks of conventional lighting technology</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Fiilex P180 Travel Kit (2 x P180)</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$1,500.00</td>	<td class='f-right-money-replace'>$800.00</td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">Includes: (2) P180, Power Tap Cable, Barndoor, diffusion dome, Fresnel lens, Stand</td></tr>
 <tr><td>NEW Fiilex K201 Travel Kit (2-P360)</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$240.00</td>	<td class='f-right-money'>$1,800.00</td>	<td class='f-right-money-replace'>$900.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Includes: (2) P360, (2) Barndoors, (2) diffusion domes, (2) Fresnel lenses, (2) Stands & Case</td></tr>
 <tr><td>NEW Fiilex K202 Travel Kit (2-P360EX)</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$240.00</td>	<td class='f-right-money'>$1,800.00</td>	<td class='f-right-money-replace'>$900.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Includes: (2) P360, (2) Barndoors, (2) diffusion domes, (2) Fresnel lenses, (2) Stands & Case</td></tr>
 <tr><td>NEW Fiilex K301 Travel Kit (3-P360)</td>	<td class='f-right-money'>$75.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money'>$2,500.00</td>	<td class='f-right-money-replace'>$1,250.00</td>	<td class='f-right-money-hide'>$650.00</td></tr>
 <tr><td colspan="6">Includes: (3) P360, (3) Barndoors, (2) diffusion domes, (2) Fresnel lenses, (3) Stands & Case</td></tr>
 <tr><td>Fiilex K302 Travel Kit (3-P360EX)</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$400.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>
 <tr><td colspan="6">Includes: (3) P360EX, (3) Barndoors, (2) diffusion domes, (2) Fresnel lenses, (3) Stands & Case</td></tr>
 <tr><td>Fiilex K303 Travel Kit (3-P360) With 5 Inches Fresnel</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$400.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>
 <tr><td colspan="6">Includes: (3) P360, (3) Barndoors, (3) 5" Fresnel lenses (P2Q), (3) Stands & Case</td></tr>
 <tr><td colspan="6">Includes: (2) P360EX, (2) P180, (2) Power Tap Cables, (4) Barndoors, (3) Diffusion domes, (3) Fresnel lenses, (3) Stands, Vipad & Case</td></tr>
 <tr><td>Fiilex P200  (w/t Fibre optics)</td>	<td class='f-right-money'>$45.00</td>	<td class='f-right-money'>$180.00</td>	<td class='f-right-money'>$1,200.00</td>	<td class='f-right-money-replace'>$600.00</td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Includes: P200, Fibre Adapter, 5' fibre glow, colour blender, Barndoor, Diffusion dome, Fresnel lens, Stand</td></tr>
 <tr><td>Fiilex P360</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$160.00</td>	<td class='f-right-money'>$1,100.00</td>	<td class='f-right-money-replace'>$550.00</td>	<td class='f-right-money-hide'>$275.00</td></tr>
 <tr><td colspan="6">Includes: P360, Barndoors, diffusion dome, Fresnel lens, Stand</td></tr>
 <tr><td>Fiilex Q 500-DC (2800°k - 6500°k)</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$400.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>
 <tr><td colspan="6">Includes: (1) Q500-DC, (1) AC Power Supply, (1) 5" Fresnel Lens, (1) Barndoors, (1) diffusion dome, Case & Stand</td></tr>
 <tr><td>Fiilex K164 Matrix (2800°k - 6500°k)</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$400.00</td>	<td class='f-right-money'>$3,000.00</td>	<td class='f-right-money-replace'>$1,500.00</td>	<td class='f-right-money-hide'>$750.00</td></tr>
 <tr><td colspan="6">Includes: (1) M1-DC, (1) AC Power Supply, (1) Fresnel Lens, (1) Barndoors, Case</td></tr>
 <tr><td>Fiilex P100 (On Camera Light)</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td colspan="6">Includes: P100, Rechargable Battery</td></tr>
 <tr><td>Fiilex 8" Fresnel Lens Kit</td>	<td class='f-right-money'>$30.00</td>	<td class='f-right-money'>$120.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td colspan="6">Includes: (1) Barndoors, (1) 8" Fresnel lens, Case. (For Fiilex Q series light)</td></tr>
 <tr><td>Fiilex 35" Parabolic Umberlla</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$500.00</td>	<td class='f-right-money-replace'>$250.00</td>	<td class='f-right-money-hide'>$175.00</td></tr>
 <tr><td colspan="6">Includes: Connector and Diffusion Panels. </td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>LOWEL CASELITES</td></tr>
 <tr><td colspan="6">These lights provides an even, soft,  daylight balanced light source that produces next to no heat with a minimal power draw. This style of light is ideal for situations where a photographer needs to balance to daylight yet creating heat is not an option. This dimmable light source (by individual tube) is also flicker free, making it suitable for film or digital in the still, motion picture or video mediums.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Lowel Caselite 2 (CF-92DA) (2X55W)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$1,200.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$350.00</td></tr>
 <tr><td colspan="6">Includes: barndoors, stand, spare bulb & case.</td></tr>
 <tr><td>Lowel Caselite 4 (CF-94DA) (4X55W)</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$1,600.00</td>	<td class='f-right-money-replace'>$800.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Includes: barndoors, stand, spare bulb & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>STUDIO FLASH SYSTEMS (5500°K)(120V)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>Self Contained Studio Flash</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Elinchrom ELC Pro HD 500 w/s Flash Head </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$1,000.00 </td>	<td class='f-right-money-replace'>$500.00 </td>	<td class='f-right-money-hide'>$400.00</td></tr>
 <tr><td colspan="6">Intelligent pre-flash detector, 8 f-stop range, Action-freezing flash durations of 1/2,330 @ full power (t0.5) and as short as 1/5,000 sec. at 53W/s. Up to 20 flashes per second</td></tr>
 <tr><td colspan="6">Includes: Sync cord, glass dome, protective cap, umbrella, reflector (6.3"/90°), stands, safesync, case</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Aurora Unilever Pro 300 w/s Flash Head</td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$160.00 </td>	<td class='f-right-money'>$800.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$250.00</td></tr>
 <tr><td colspan="6">Includes:  300 w/s head, 8" reflectors, umbrellas, stands, cases & cables</td></tr>
 <tr><td>Aurora Unilever Pro 1800 w/s Travel kit *</td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money'>$3,000.00 </td>	<td class='f-right-money-replace'>$1,500.00 </td>	<td class='f-right-money-hide'>$1,000.00</td></tr>
 <tr><td colspan="6">Includes: (3) 600 w/s heads, (3) 8" reflectors, (3) umbrellas, (3) stands, cases & cables</td></tr>
 <tr><td colspan="6">Accessories for Aurora Monolights (also compatible w/Photogenic)</td></tr>
 <tr><td>Aurora Unilever Snoot w/grid</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$60.00</td>	<td class='f-right-money-hide'>$30.00</td></tr>
 <tr><td>Aurora Unilever 40° Grids (for 8' Reflectors)</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$60.00</td>	<td class='f-right-money-hide'>$30.00</td></tr>
 <tr><td>Aurora Unilever 430mm Soft Reflector (Beauty Dish)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Aurora 48" Octobox w/Connector</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Aurora 36x48" Softbox w/Connector</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td>Aurora 12x50" Stripdome w/Connector</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>STUDIO AND LIGHTING ACCESSORIES</td></tr>
 <tr><td colspan="6">Softboxes and Accessories</td></tr>
 <tr><td>Aurora 48" Octobox w/ring</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">** Directional softbox (suitable for strobes only) (specify speedring)</td></tr>
 <tr><td>Aurora 36x48" Softbox w/ring</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">** Directional softbox (suitable for strobes only) (specify speedring)</td></tr>
 <tr><td>Photoflex Silverdome NXT Soft box(24"X32")</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td colspan="6">**Dedicated directional soft box (suitable for strobes or constant light sources.) (specify speedring)</td></tr>
 <tr><td>Photoflex Whitedome NXT Soft box (24"X32")</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">**Omni directional soft box (suitable for strobes or constant light sources.) Convertible from omnidirectional to directional. (specify speedring).</td></tr>
 <tr><td>Photoflex Octodome NXT Soft box (3')</td>	<td class='f-right-money'>$15.00</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$300.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Dedicated directional softbox (suitable for strobes and some constant light sources.) (specify speedring).</td></tr>
 <tr><td>Octoconnector for Elinchrom Strobes</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$75.00</td>	<td class='f-right-money-replace'>$40.00</td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td colspan="6">-Rings for Hot Lights (Lowel Tota)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Misc. Lighting Accessories</td></tr>
 <tr><td>Photoflex 42" 5 in 1 multidisc (1 Disc, 5 finishes)</td>	<td class='f-right-money'>$5.00</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$150.00</td>	<td class='f-right-money-replace'>$75.00</td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Finshes Include: Translucent, White, Silver, Gold & Soft Gold.</td></tr>
 <tr><td>Photoflex Telescopic Litedisc Arm w/stand</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$70.00</td>	<td class='f-right-money-hide'>$40.00</td></tr>
 <tr><td colspan="6">Support youir Litedisc where you want it, fits on most Light Stands w/ 5/8" spool. (Holds discs from 12" to 52").</td></tr>
 <tr><td>Photoflex 45" Convertible Umbrella</td>	<td class='f-right-money'>$2.00 </td>	<td class='f-right-money'>$8.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money-replace'>$30.00 </td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td colspan="6">Converts from a soft bounce umbrella to a shoot through.</td></tr>
 <tr><td>Booth Digital Photo Box</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$125.00 </td>	<td class='f-right-money-replace'>$65.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">28"x28"x28" Product shooting tent with integral backgrounds</td></tr>
 <tr><td>Portable Umbrella Kit for shoe mounted Flashes</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td colspan="6">Includes: 7' Lightstand, tiltable shoe mount, 30" convertible umbrella, water weight & case.</td></tr>
 <tr><td>Gary Fong Universal Light Sphere Kit</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$75.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Velcro mounted flash diffusers to fit most camera mount flashes. (set of 2)</td></tr>
 <tr><td colspan="6">Includes: amber & chrome dome accessories</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>BACKGROUNDS & BACKGROUND KIT SUPPORTS</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Cameron Chromakey Background Kit (Blue/Green)</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$125.00</td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">These are very compact backgrounds (50"x60") come complete with stand, case & reversible fabric. Ideal for corporate photos & situations where full length photo's are not necessary!</td></tr>
 <tr><td>Manfrotto #012 Backlight Stand</td>	<td class='f-right-money'>$2.00 </td>	<td class='f-right-money'>$8.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money-replace'>$30.00 </td>	<td class='f-right-money-hide'>$20.00</td></tr>
 <tr><td>Manfrotto Portable Background Stand Kit</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Expands up to 10' Wide & includes (2) stand, crossbar, case.</td></tr>
 <tr><td>Miller Portable Background Stand Kit</td>	<td class='f-right-money'>$12.00 </td>	<td class='f-right-money'>$48.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Includes: Expandable crossbar up to 10', (2) 12' stands & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>LIGHT METERS &  WIRELESS TRIGGER ACCESSORIES</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Sekonic L-358 Flash Meter (w/RT Module)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">A premium meter with dual ISO settings, cine speeds and the ability to interface with Pocket Wizard radio slaves.</td></tr>
 <tr><td colspan="6">Includes: Pocket Wizard Module (RT-32)</td></tr>
 <tr><td colspan="6">Note: Wireless flash triggering is not possible if being used in conjunction with the Pocket Wizard TTL units in the TTL mode, (however it is possible if units are set to 'basic' mode).</td></tr>
 <tr><td>Sekonic L-358 Flash Meter</td>	<td class='f-right-money'>$12.00 </td>	<td class='f-right-money'>$48.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$175.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Premium meter with Dual ISO settings & cine speeds</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Pocket Wizard Plus II Radio Slave Set</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$350.00 </td>	<td class='f-right-money-replace'>$225.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td colspan="6">Save yourself the hassle of tripping over cables & the worry of accidental damage to your digital camera from electrical surge. Go wireless and enjoy the freedom. (will also interface with some Sekonic meters to truly go wireless).</td></tr>
 <tr><td colspan="6">Includes: (2) Transceivers & Adapter Cable. (specify cable types required)</td></tr>
 <tr><td>Pocket Wizard Plus II - Extra Transceiver</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td colspan="6">Includes: Adapter Cable (specifiy cable type required)</td></tr>
 <tr><td>-Pocket Wizard Cables (Flash) (1 Included with system rental) (compatible w/Plus II, Multimax & Flex TTS systems)</td>	<td class='f-right-money'>$12.00 </td>	<td class='f-right-money'>$48.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$175.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">choices: 1/4" phono Jack, 1/8? Mini jack (3.5mm), HH (household), PCI (male pc), micro to mini jack & PC female adapter.</td></tr>
 <tr><td>-Pocket Wizard Cables (Camera) (1 Included with system rental) (compatible w/plus, plus II & multimax units only!)</td>	<td class='f-right-money'>$5.00 </td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$75.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">choices: Canon N3 (pre-Trigger), Canon E3 (also fits Pentax Digital SLR's)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">Pocket Wizard FlexTT5 (For Canon EOS)</td></tr>
 <tr><td>Pocket Wizard Flex TT5 (TTL) Radio Slave Set (Canon EOS)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$300.00 </td></tr>
 <tr><td colspan="6">Includes: (2) Transceivers, (1) RF Shield, case & usb cables for customization</td></tr>
 <tr><td>Extra Pocket Wizard Flex TT5 Transceiver (For Canon EOS)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$175.00 </td>	<td class='f-right-money-hide'>$125.00 </td></tr>
 <tr><td colspan="6">Includes: (1) RF Shield & USB cables for customization.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>MTC Triggersmart Ifrared receiver , light intensity and sound reciever remote trigger system.</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$125.00</td></tr>
 <tr><td colspan="6">TriggerSmart is a unique motion-capture system. It is designed to capture images using a number of different ways to trigger your camera: sound, light, infrared beam breaking and movement. TriggerSmart can be used with a variety of stills and video cameras, including Canon digital and film SLR cameras.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>AUDIO VISUAL EQUIPMENT</td></tr>
 <tr><td class='f-rental-title-line'>DIGITAL PROJECTION</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Epson PowerLite TW100 LCD Multimedia Projector</td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money'>$2,000.00 </td>	<td class='f-right-money-replace'>$1,200.00 </td>	<td class='f-right-money-hide'>$1,000.00</td></tr>
 <tr><td colspan="6">Features: native 16:9 LCD's; True 720p HDTV resolution; 800:1 contrast ratio for sharp, clear images; Superior Color Palette; Quiet operation for uninterrupted viewing.</td></tr>
 <tr><td colspan="6">Includes: DVI to HDMI, DVI to Mini Display, RCA, S Video, VGA Cables & case.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>35MM SLIDE PROJECTORS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Singer Caramate 35mm Projector (Kodak carousel compatible AF projector)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td colspan="6">Includes 80 Tray, remote, zoom lens, spare bulb & case</td></tr>
 <tr><td colspan="6"> </td></tr>
 <tr><td class='f-rental-title-line'>OVERHEAD PROJECTORS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Bantam Overhead Projector</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$300.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>PROJECTION SCREENS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">Projection screens suitable for projections from 8 mm, 16mm movie or front projection video.</td></tr>
 <tr><td>Da-lite 50X50 (matte white) (floor standing)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$75.00 </td>	<td class='f-right-money-hide'>$40.00</td></tr>
 <tr><td>Da-lite 60X60 (matte white) (floor standing)</td>	<td class='f-right-money'>$12.00 </td>	<td class='f-right-money'>$48.00 </td>	<td class='f-right-money'>$175.00 </td>	<td class='f-right-money-replace'>$85.00 </td>	<td class='f-right-money-hide'>$45.00</td></tr>
 <tr><td>Da-lite 70X70 (matte white) (floor standing)</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>MOVIE (MOTION PICTURE) EQUIPMENT</td></tr>
 <tr><td class='f-rental-title-line'>SUPER 8 CAMERAS</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td colspan="6">Super 8 cameras are suitable for independent documentaries, music videos or nostalgic imaging.</td></tr>
 <tr><td>Canon 1014XL-S </td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$600.00 </td>	<td class='f-right-money-replace'>$400.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>PROJECTORS (8MM, SUPER 8MM, 16MM)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Bell & Howell #1592 - 16mm Projector (Optical Sound)</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$350.00 </td>	<td class='f-right-money-hide'>$200.00</td></tr>
 <tr><td>Bell & Howell Autoload Super 8 Projector Design 483</td>	<td class='f-right-money'>$35.00 </td>	<td class='f-right-money'>$140.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Includes take up reel. Extra DSW Bulb. (Variable speed setting. Great for transferring footage to video)</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td class='f-rental-title-line'>EDITING EQUIPMENT (8mm, Super 8mm & 16mm)</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Craig Editor w/ Rewinds (Regular 8)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>Craig Editor w/ Rewinds (Super 8)</td>	<td class='f-right-money'>$25.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>Craig Editor w/ Rewinds (16mm)</td>	<td class='f-right-money'>$25.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money'>$250.00</td>	<td class='f-right-money-replace'>$150.00</td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td colspan="6" class='f-rental-title'>VINTAGE PROP EQUIPMENT</td></tr>
 <tr><td colspan="6">Looking for vintage camera, movie or audio equipment? Leo's may have what you need. This gear is primarily non functional but will add to the vintage feel of your ad, commerical or feature. Looking for something specific that you don't see listed? Give us a call. After 50 years in one location we may have what you need hidden away!</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Early Press Cameras</td>	<td class='f-right-money'>$30.00 </td>	<td class='f-right-money'>$120.00 </td>	<td class='f-right-money'>$500.00 </td>	<td class='f-right-money-replace'>$300.00 </td>	<td class='f-right-money-hide'>$150.00</td></tr>
 <tr><td>Graflex 4x5 Press Camera with flash gun.</td>	<td class='f-right-money'>$20.00 </td>	<td class='f-right-money'>$80.00 </td>	<td class='f-right-money'>$400.00 </td>	<td class='f-right-money-replace'>$200.00 </td>	<td class='f-right-money-hide'>$100.00</td></tr>
 <tr><td colspan="6">Graflex 2 1/4X3 3/4 Press Camera.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Cameras & Lenses from 1960's & 1970's</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$150.00 </td>	<td class='f-right-money-hide'>$75.00</td></tr>
 <tr><td>Mamiyaflex Twin Lens (1952)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money-replace'>$50.00 </td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td>Ricohflex Super Twin Lens (1955)</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Yashica A Twin Lens (1959-1960)</td></tr>
 <tr><td>Fairly inexpensive twin lens medium format camera for amatuer photographers.</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Nikon F (Chrome) body w/50mm Lens.</td></tr>
 <tr><td colspan="6">The primary camera used by press photographers through out the late 60's and 70's.</td></tr>
 <tr><td>(Time, Life, National Geographic & AP).</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$150.00 </td>	<td class='f-right-money-replace'>$100.00 </td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Nikkor 85-250mm F4 Lens</td></tr>
 <tr><td colspan="6">-Nikons first zoom lens, widely adapoted by press & sports photographers of the late 60's & early 70's.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Polaroid Cameras</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$50.00</td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td colspan="6">Polaroid 120 Pathfinder (1961-1965)</td></tr>
 <tr><td>Professional quality polaroid often used by event & night club photographers</td>	<td class='f-right-money'>$10.00</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$100.00</td>	<td class='f-right-money-replace'>$50.00</td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td colspan="6">Polaroid Model 80A Highlander (1957-1959)</td></tr>
 <tr><td colspan="6">Polaroids first smaller sized camera. Fairly expensive camera for the home market.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Early Misc Cameras</td>	<td class='f-right-money'>$10.00 </td>	<td class='f-right-money'>$40.00 </td>	<td class='f-right-money'>$100.00 </td>	<td class='f-right-money-replace'>$50.00 </td>	<td class='f-right-money-hide'>$25.00</td></tr>
 <tr><td colspan="6">Kodak Brownie Hawk eye with flash gun. (1950-1961)</td></tr>
 <tr><td colspan="6">(Mass market family camera)</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Audio Recorders (Various Vintages)</td>	<td class='f-right-money'>$50.00 </td>	<td class='f-right-money'>$200.00 </td>	<td class='f-right-money'>$1,500.00 </td>	<td class='f-right-money-replace'>$750.00 </td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Nagra SN Mini reel to reel**</td></tr>
 <tr><td>**James Bond 007 Type.</td>	<td class='f-right-money'>$20.00</td>	<td class='f-right-money'>$80.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money-replace'>$100.00</td>	<td class='f-right-money-hide'>$50.00</td></tr>
 <tr><td colspan="6">Uher (Reel to Reel) w/mic & case.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>Early 16mm (News) Cameras</td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$240.00 </td>	<td class='f-right-money'>$1,200.00 </td>	<td class='f-right-money-replace'>$600.00 </td>	<td class='f-right-money-hide'>$300.00</td></tr>
 <tr><td colspan="6">Auricon Pro 600 Special 16mm Camera Kit. (1952-1955)</td></tr>
 <tr><td colspan="6">Includes: Len(s), magazine, sound mixer, wooden tripod and case.</td></tr>
 <tr><td colspan="6">Camera primarly used by news crews.</td></tr>
 <tr><td>&nbsp;</td>	<td class='f-per-day'>Per Day</td><td class='f-per-weekly'>Weekly</td><td class='f-per-replacement'>Replacement</td><td class='f-per-deposit'>Deposit</td><td class='f-per-acc'>On Acc</td></tr>
 <tr><td>16mm (News & Documentary) Cameras</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$160.00</td>	<td class='f-right-money'>$350.00</td>	<td class='f-right-money-replace'>$350.00</td></tr>
 <tr><td colspan="6">Kodak 16mm Cine Special K100 turret kit. (1955-1965)</td></tr>
 <tr><td>Includes: (3)Lenses & Tripod.</td>	<td class='f-right-money'>$40.00</td>	<td class='f-right-money'>$160.00</td>	<td class='f-right-money'>$350.00</td>	<td class='f-right-money-replace'>$350.00</td></tr>
 <tr><td colspan="6">Bolex 16mm Non-Reflex 16mm Kit.</td></tr>
 <tr><td>Includes: (3)Lenses & Bolex Tripod.</td>	<td class='f-right-money'>$60.00</td>	<td class='f-right-money'>$240.00</td>	<td class='f-right-money'>$600.00</td>	<td class='f-right-money-replace'>$600.00</td></tr>
 <tr><td colspan="6">Bolex Rex 3 16mm camera w/dive housing.</td></tr>
 <tr><td>Includes: Rex 3 camera, 10mm lens and dive housing.</td>	<td class='f-right-money'>$15.00 </td>	<td class='f-right-money'>$60.00 </td>	<td class='f-right-money'>$250.00 </td>	<td class='f-right-money-replace'>$250.00</td></tr>
 <tr><td colspan="6">Wooden tripods.</td></tr>
 <tr><td colspan="6">&nbsp;</td></tr>
 <tr><td>Broadcast ENG (News & Documentary) Video Cameras</td>	<td class='f-right-money'>$50.00</td>	<td class='f-right-money'>$200.00</td>	<td class='f-right-money'>$1,500.00</td>	<td class='f-right-money-replace'>$1,000.00</td>	<td class='f-right-money-hide'>$500.00</td></tr>
 <tr><td colspan="6">Sony ED Beta ENG Style Video Package (1980-2000)</td></tr>
 <tr><td colspan="6">Commonly used by smaller TV stations & documentary shooters. Includes: ED Betacam, Case & Tripod</td></tr>





</tbody></table>
	<?php
}
?>
