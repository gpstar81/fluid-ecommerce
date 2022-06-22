<?php
require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.admin.login.php");

if(isset($_REQUEST['f_login_admin'])) {
	if(empty($_SESSION['f_spam_check'] = time()))
		$_SESSION['f_spam_check'];

	if(empty($_SESSION['f_counter']))
		$_SESSION['f_counter'] = 0;

	$_SESSION['f_counter']++;

	if($_SESSION['f_counter'] > 30) {
		if(time() > ($_SESSION['f_spam_check'] + 600)) {
			$_SESSION['f_counter'] = 1;
			unset($_SESSION['f_login_spam']);
			$f_login_array = Array("f_email" => $_REQUEST['f_email'], "f_password" => $_REQUEST['f_password']);

			php_fluid_login_admin($f_login_array);
		}
		else {
			$_SESSION['f_login_spam'] = TRUE;
		}
	}
	else {
		$f_login_array = Array("f_email" => $_REQUEST['f_email'], "f_password" => $_REQUEST['f_password']);

		php_fluid_login_admin($f_login_array);
	}
}
else if(isset($_COOKIE[FLUID_COOKIE_ADMIN]) && isset($_SESSION['u_id_admin']) == FALSE) {
	$_SESSION['f_counter'] = 0;

	php_fluid_login_admin_cookie();
}

if(isset($_SESSION['u_id_admin'])) {
$fluid_header = new Fluid();
?>

<!-- Navigation -->
<nav id='fluid-admin-navbar' class="navbar navbar-inverse navbar-fixed-top top-nav-collapse">
  <div class="container nav-menu-size">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand leos-brand-logo-small" href="index.php">
		    <img class="leos-logo logo-resize-f" alt="Leos Camera Logo" src="files/leos-logo.png">
	  </a>
    </div>

	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

    <!-- add search form -->
    <?php
    /*
	<form class="navbar-form navbar-left" style='min-width:30% !important;' role="search">
         <div class="input-group" style='min-width:100% !important;'>
             <input id='search-input' type="text" style='height:46px; font-size:16px;' class="form-control" placeholder="Search...">
				<span class="input-group-btn">
					<button id='search-button' type="submit" class="btn btn-default" style='height:46px;'>
						<span id='search-glyph' class="glyphicon glyphicon-search" style='font-size:16px;'></span>
					</button>
				</span>
         </div>
	</form>
	*/?>
      <ul class="nav navbar-nav navbar-right f-navbar-special">
		  <?php
			$temp_url_pos = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_POS, "dataobj" => "load=true&function=php_load_pos")));

			$temp_url_orders = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_load_orders")));
			$temp_url_categories = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories")));
			$temp_url_manufacturers = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_categories&mode=manufacturers")));
			$temp_url_items = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_items&mode=items")));
			$temp_url_import = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_IMPORT_ADMIN, "dataobj" => "load=true&function=php_load_staging&mode=import")));
			$temp_url_settings = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_SETTINGS_ADMIN, "dataobj" => "load=true&function=php_fluid_load_option_set")));
			$temp_url_banners = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners")));

			$temp_url_accounts = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT_ADMIN, "dataobj" => "load=true&function=php_load_accounts")));

			$temp_url_feedback = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_FEEDBACK_ADMIN, "dataobj" => "load=true&function=php_load_feedback")));

			$temp_url_logs = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGS_ADMIN, "dataobj" => "load=true&function=php_logs_load")));
			$temp_logout = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOGIN_ADMIN, "dataobj" => "adminaccount=true&function=php_fluid_admin_logout")));


if($_SESSION['u_access_admin'] == 'all') {
?>
		<?php /*<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_pos; ?>", "content-div");'><span class="glyphicon glyphicon-object-align-vertical"></span> POS</a></li>*/ ?>
		<?php //<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse");'><span class="glyphicon glyphicon-object-align-vertical"></span> POS</a></li>?>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0; js_fluid_ajax("<?php echo $temp_url_orders; ?>", "content-div");'><span class="glyphicon glyphicon-shopping-cart"></span> Orders</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_items; ?>", "content-div");'><span class="glyphicon glyphicon-list-alt"></span> Items</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax("<?php echo $temp_url_categories; ?>", "content-div");'><span class="glyphicon glyphicon-th-large"></span> Categories</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_reset_sort_prevent(false); js_fluid_ajax("<?php echo $temp_url_manufacturers; ?>", "content-div");'><span class="glyphicon glyphicon-th-list"></span> Manufacturers</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_banners; ?>", "content-div");'><span class="glyphicon glyphicon-blackboard"></span> Banners</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_accounts; ?>", "content-div");'><span class="glyphicon glyphicon-user"></span> Accounts</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_import; ?>", "content-div");'><span class="glyphicon glyphicon-transfer"></span> Import</a></li>
		<?php /* <li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_feedback; ?>", "content-div");'><span class="glyphicon glyphicon-comment"></span> Feedback</a></li>*/ ?>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); FluidVariables.f_page_num=0;js_fluid_ajax("<?php echo $temp_url_logs; ?>", "content-div");'><span class="glyphicon glyphicon-book"></span> Logs</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer'" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); js_sms_panel_load(1);' id='smspanelpopupmobile' name='smspanelpopupmobile'><span class='glyphicon glyphicon-phone'></span> SMS <span class='badge' id='sms-notification-icon'><?php echo php_sms_timer_check(base64_encode(json_encode(Array('f_load' => TRUE)))); ?></span></a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); js_fluid_ajax("<?php echo $temp_url_settings; ?>");'><span class="glyphicon glyphicon-wrench"></span> Settings</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='$("#bs-example-navbar-collapse-1").removeClass("in").addClass("collapse"); js_fluid_ajax("<?php echo $temp_logout; ?>");'><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
<?php
}
else {
?>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-object-align-vertical"></span> POS</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='FluidVariables.f_page_num=0; js_fluid_ajax("<?php echo $temp_url_orders; ?>", "content-div");'><span class="glyphicon glyphicon-shopping-cart"></span> Orders</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-list-alt"></span> Items</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-th-large"></span> Categories</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-th-list"></span> Manufacturers</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-blackboard"></span> Banners</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-user"></span> Accounts</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-transfer"></span> Import</a></li>
		<?php //<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-comment"></span> Feedback</a></li> ?>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-book"></span> Logs</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='not-allowed';" style='text-decoration: line-through;'><span class="glyphicon glyphicon-wrench"></span> Settings</a></li>
		<li><a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_ajax("<?php echo $temp_logout; ?>");'><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
<?php
}
?>

      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav><!-- Navigation End-->

<?php
//echo HTML_LOADING_OVERLAY;
?>

	<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-body" id='modal-error-div'></div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
		  </div>
		</div>
	  </div>
	</div>
<?php
}
?>
