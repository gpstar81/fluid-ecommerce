<?php
session_start();

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");

$fluid_header = new Fluid();
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>Administrator Panel</title>

	<link rel="icon" type="image/png"  href="files/leos-logo.png" />
	<!-- Bootstrap Core CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/bootstrap.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/bootstrap-select.min.css');?>">

	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/custom.css');?>">

	<?php
	$detect = new Mobile_Detect;

	//if($detect->isTablet())
		//echo '<link rel="stylesheet" type="text/css" href="css/tablet.css">';
	?>

	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/dropzone.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/bootstrap-datetimepicker.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/font-awesome.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/fluid.animate.css');?>">
	<link href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fluidnote/fluidnote.css');?>" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/leos-logo.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/fluid.print.css');?>" media="print">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/jquery.fileupload.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/jquery.fileupload-ui.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/blueimp-gallery.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'css/fluid.pos.css');?>">

</head>

<body id='f-body-admin'>

<?php
require_once("fluid.header.js.php");

// Load SMS first as header.php calls a function within fluid.sms.php
require_once("fluid.sms.js.php");
require_once("fluid.sms.php");

require_once("header.php");
require_once("fluid.js.php");
require_once("fluid.pos.js.php");
require_once("fluid.selector.js.php");
require_once("fluid.selector.php");
require_once("fluid.account.js.php");
require_once("fluid.logs.js.php");
require_once("fluid.export.js.php");
require_once("fluid.import.js.php");
require_once("fluid.settings.js.php");
require_once("fluid.settings.php");
require_once("fluid.attributes.js.php");
require_once("fluid.attributes.php");
require_once("fluid.feedback.js.php");
require_once("fluid.orders.js.php");
require_once("fluid.barcode.js.php");
//require_once("fluid.barcode.php");
?>

<?php
/*
<style>
body.iosBugFixCaret.modal-open { position: fixed !important; width: 100%; }
</style>

<script>
	$(document).ready(function() {
		<?php
		// Detect ios 11_0_x affected
		// NEED TO BE UPDATED if new versions are affected
		?>
		var ua = navigator.userAgent,
		iOS = /iPad|iPhone|iPod/.test(ua),
		iOS11 = /OS 11_0_1|OS 11_0_2|OS 11_0_3|OS 11_1/.test(ua);

		<?php // ios 11 bug caret position ?>
		if ( iOS && iOS11 ) {
			<?php // Add CSS class to body ?>
			$("body").addClass("iosBugFixCaret");
		}
	});
</script>
*/
?>
<?php
if(isset($_SESSION['u_id_admin'])) {
?>
<div id='fluid-blur-wrap'>

	<div id='fluid-admin-div'>
		<nav style='margin-bottom:0px; border-radius: 0 0 0 0;' class="navbar navbar-default f-editor-nav">
		  <div style='padding: 0 0 0 0; margin: 0 0 0 0;' class="container-fluid">
			<div style='padding: 0 0 0 0; margin: 0 0 0 0;' class="nav navbar-nav navbar-left f-breadcrumbs" style='height: 50px;'>
				<ol style='padding: 0 0 0 0; margin: 0 0 0 0;' id='breadcrumbs' style='background-color: transparent;' class="breadcrumb">
					<li class='active'><a href="index.php">Home</a></li>
				</ol>
			</div>
			<div id='navbar-menu-right' style='margin: 0 0 0 0; float: right !important;' class="nav navbar-nav navbar-right f-action-menu-right"></div>
			<div id='navbar-menu-search' style='padding: 0 0 0 0; margin: 0 0 0 0;' class="nav navbar-nav navbar-right"></div>
		  </div><!-- /.container-fluid -->
		</nav>


		<div id='fluid-main-wrap' style='margin-left:0px; margin-right: 0px; border-top:0px; border-radius: 0 0 0 0; border-bottom: 0px;' class="panel panel-default">
		  <div class="panel-body">
			<div id='content-div' class='f-content-margin'>
				<div>Welcome to the administration control panel.</div>
				<div style='font-weight: 600; padding-top: 15px;'>Navigating</div>
				<div style='padding-top: 10px;'>POS: Point of sale terminal. Process in store sales, prints to receipt printer and operates a cash drawer.</div>
				<div style='padding-top: 5px;'>Orders: All web orders and web order processing such as shipping information, packing, serial numbers, refunds, etc.</div>
				<div style='padding-top: 5px;'>Items: Lists all the items in the product database, with the ability to edit, delete, create, disable, stock level scanning adjustment, etc.</div>
				<div style='padding-top: 5px;'>Categories: Lists all the categories and items that are contained within them. You can edit, delete, create, disable, sort items and categories and also set category filters.</div>
				<div style='padding-top: 5px;'>Manufacturers: Lists all the manufacturers and items that are contained within them. You can edit, delete, create, disable, sort items and manufacturers and also set manufacturer filters.</div>
				<div style='padding-top: 5px;'>Banners: Coming soon after launch. You will be able to edit the html of the banners in this section.</div>
				<div style='padding-top: 5px;'>Import: You can import csv files into a staging table, scan and match up existing items, and then import and or merge into the product database.</div>
				<div style='padding-top: 5px;'>Logs: View the database log system.</div>
				<div style='padding-top: 5px;'>SMS: SMS Text messaging system.</div>
				<div style='padding-top: 5px;'>Settings: Site wide settings such as token keys, opening and closing of the online store, create boxes for shipping etc.</div>

				<div style='font-weight: 600; padding-top: 30px;'>Need help? <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></div>
				<div style='padding-top:5px'><a href='help/Processing a order.pdf' target='_blank'>How to process a order.</a></div>
				<div style='padding-top:5px;'><a href='help/Scanning & updating stock.pdf' target='_blank'>Scanning and updating stock.</a></div>


			</div>
		  </div>
		</div>

		<div style='margin-left:10px; margin-right: 10px; margin-bottom:15px; display: none;' class="progress navbar-fixed-bottom">
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-success" id='progress-bar-loading' style="width:0%;"></div>
			</div>
		</div>

		<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
			<div class="slides"></div>
			<h3 class="title"></h3>
			<a class="prev"><</a>
			<a class="next">></a>
			<a class="close">X</a>
			<a class="play-pause"></a>
			<ol class="indicator"></ol>
		</div>
	</div> <?php // --> fluid-admin-div ?>

	<div id='fluid-pos-div' style='display: none; width: 100%;'></div>

</div> <?php // fluid-blur-wrap ?>

<?php
echo HTML_MODAL; // Load the modal container.
echo HTML_MODAL_OVERFLOW; // The overflow modal container.
echo HTML_MODAL_MSG; // A second modal. Used for messages or first stage confirmations.
echo HTML_ERROR_MODAL; // Load the error modal container.
echo HTML_CONFIRM_MODAL; // Load the confirm modal container.
echo HTML_MODAL_FLUID;
echo HTML_ADMIN_RIGHT_CLICK_MUTLI_ITEM_EDITOR_MENU;
echo HTML_ADMIN_RIGHT_CLICK_EDITOR_MENU;

require_once("footer.php");
?>


<div id='fluid-print-div'></div>
<?php
}
else {
?>
<style>
.wrapper {
	margin-top: 0px;
	margin-bottom: 0px;
}

.form-signin {
  max-width: 420px;
  padding: 30px 38px 66px;
  margin: 0 auto;
  background-color: #eee;
  border: 3px dotted rgba(0,0,0,0.1);
  }

.form-signin-heading {
  text-align:center;
  margin-bottom: 30px;
  margin-top: 10px;
}

.form-control {
  position: relative;
  font-size: 16px;
  height: auto;
  padding: 10px;
}

input[type="text"] {
  margin-bottom: 0px;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

input[type="password"] {
  margin-bottom: 20px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.colorgraph {
  height: 7px;
  border-top: 0;
  background: #c4e17f;
  border-radius: 5px;
  background-image: -webkit-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
  background-image: -moz-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
  background-image: -o-linear-gradient(left, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
  background-image: linear-gradient(to right, #c4e17f, #c4e17f 12.5%, #f7fdca 12.5%, #f7fdca 25%, #fecf71 25%, #fecf71 37.5%, #f0776c 37.5%, #f0776c 50%, #db9dbe 50%, #db9dbe 62.5%, #c49cde 62.5%, #c49cde 75%, #669ae1 75%, #669ae1 87.5%, #62c2e4 87.5%, #62c2e4);
}

.logo-resize-login {
	width: 80px;
}

</style>

	<div class="container" style='display: table; width: 100%;'>
		<div class="wrapper" style='display: table-cell; height: 100vh; vertical-align: middle;'>
			<form action="" method="post" name="Login_Form" class="form-signin">
				<div style='width: 100%; text-align: center;'><img class="leos-logo logo-resize-login" alt="Leos Camera Logo" src="files/leos-logo.png"></div>
				<h3 class="form-signin-heading">Login</h3>
				  <?php //<hr class="colorgraph"><br>?>

				  <input type="text" class="form-control" name="f_email" placeholder="Name" required="" autofocus="" />
				  <input style='margin-top: 10px;' type="password" class="form-control" name="f_password" placeholder="Password" required=""/>

				  <button class="btn btn-lg btn-primary btn-block"  name="f_login_admin" value="Login" type="Submit"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Login</button>

				  <?php
					if(isset($_REQUEST['f_login_admin'])) {
						if(isset($_SESSION['f_login_spam']))
							echo "<div style='text-align: center; margin-top: 25px; color: red; font-weight: 600;'><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i> Please stop spamming. Try again later.</div>";
						else
							echo "<div style='text-align: center; margin-top: 25px; color: red; font-weight: 600;'><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i> Incorrect login. Please try again.</div>";
					}
				  ?>
			</form>
		</div>
	</div>
<?php
}
?>
</body>
</html>
