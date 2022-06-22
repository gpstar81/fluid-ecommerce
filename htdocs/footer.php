<footer class="footer">
 	<div id="footer_fluid" class="container-fluid">
		<div class="row" id="footer-content-container">
			<div class="col-sm-6 col-md-4">
				<div style='margin:auto;'>
					<h4>Contact Us</h4>
					<div class="row" id="footer-contact-us">
						<div class="col-md-2" style='padding-top:8px;'><span class='icon-leos-logo' style='font-size: 25px; color: red;'></span></div>
						<div class="col-md-10" style='padding-top:8px;'><a href="http://www.leoscamera.com">Leo's Camera Supply Ltd.</a></div>

						<div class="col-md-2"><span class="glyphicon glyphicon-map-marker"></span></div>
						<div class="col-md-10"><p><a href='https://goo.gl/maps/1yy12Y9Lnky' target='_blank'>1055 Granville Street Vancouver BC Canada V6Z1L4</a></p></div>


						<div class="col-md-2"><span class="glyphicon glyphicon-earphone"></span></div>
						<div class="col-md-10"><p><a href='tel:+16046855331'>604-685-5331</a></p></div>
                        
                        <?php
                        /*
                        <div class="col-md-2"><span class="glyphicon glyphicon-phone"></span></div>
                        <div class="col-md-10"><p><a href='sms:+17787711002'>778-771-1002</a></p></div>
                        */
                        ?>
						<div class="col-md-2"><span class="glyphicon glyphicon-envelope"></span></div>
						<div class="col-md-10"><p><a href="mailto:info@leoscamera.com">info@leoscamera.com</a></p></div>
					</div>

					<div class="row" id="footer-social-media">
						<div class="col-xs-3 col-sm-3 col-md-3"><a href="https://www.facebook.com/LeosVancouver" target="_blank"><div class='fa fa-4x fa-facebook-official'></div></a></div>
						<div class="col-xs-3 col-sm-3 col-md-3"><a href="https://twitter.com/LeosCamera" target="_blank"><div class='fa fa-4x fa-twitter-square'></div></a></div>
						<div class="col-xs-3 col-sm-3 col-md-3"><a href="https://www.youtube.com/c/LeosCameraSupplyTV" target="_blank"><div class='fa fa-4x fa-youtube-square'></div></a></div>
						<div class="col-xs-3 col-sm-3 col-md-3"><a href="https://www.instagram.com/leoscamerasupply/" target="_blank"><div class='fa fa-4x fa-instagram'></div></a></div>
						<?php
						/*
						<div class="col-md-12">
							<h4>Newsletter</h4>
							<p>Sign up for our Newsletter</p>
							<form class="form-inline">
								<div class="input-group newsletter-input-group">
									<input type="email" class="form-control newsletter-input" id="exampleInputEmail2" placeholder="email">
									<button class="btn btn-info newsletter-button" type="submit">Submit</button>
								</div>
							</form>
						</div>
						*/
						?>
					</div>  <!-- footer-social-media end -->
				</div>
			</div>
			<div class="col-sm-6 col-md-4">
				<div style='margin:auto; text-align: center;'>
					<h4>My Account</h4>
					<?php
						if(empty($_SESSION['u_id'])) {
							$onClickOrders = "data-toggle=\"modal\" data-target=\"#fluid-login-modal\" data-backdrop=\"static\" data-keyboard=\"false\" aria-expanded=\"false\" onClick=\"js_close_toggle_menus(); document.getElementById('modal-login-div-header').style.display = 'none';  document.getElementById('modal-checkout-fluid-div').innerHTML = ''; document.getElementById('fluid-account-dropdown').innerHTML = ''; document.getElementById('fluid-account-dropdown-nav').innerHTML = ''; document.getElementById('modal-login-div').innerHTML = Base64.decode(FluidMenu.account['html']); js_fluid_login(); document.getElementById('fluid-div-signup').innerHTML = Base64.decode(FluidMenu.account['signup_mobile_html']); document.getElementById('modal-checkout-div-header').style.display = 'none'; document.getElementById('fluid-login-back-button').style.display = 'none'; fluid_facebook_checkout = 0; fluid_google_checkout = 0;\";";

							$onClickAddress = $onClickOrders;
						}
						else {
							$onClickOrders = "href=\"" . $_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/orders' . "\" onClick='js_loading_start();'";
							$onClickAddress = "href=\"" . $_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/address' . "\" onClick='js_loading_start();'";
						}
					?>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" <?php echo $onClickOrders; ?>><i class="fa fa-id-badge" aria-hidden="true" style='padding-right: 5px;'></i> MY ORDERS</a>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" <?php echo $onClickAddress; ?>><i class="fa fa-shopping-bag" aria-hidden="true" style='padding-right: 5px;'></i> MY ADDRESSES</a>
				</div>
			</div>
			<div class="col-sm-6 col-md-4">
				<div style='margin:auto; text-align: center;'>
					<h4>Information</h4>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Terms & Conditions</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_TERMS_CONDITIONS); ?>"); js_modal_show("#fluid-main-modal");'><i class="fa fa-info-circle" aria-hidden="true" style='padding-right: 5px;'></i> TERMS & CONDITIONS</a>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Privacy & Security</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_PRIVACY_CONDITIONS); ?>"); js_modal_show("#fluid-main-modal");'><i class="fa fa-eye" aria-hidden="true" style='padding-right: 5px;'></i> PRIVACY & SECURITY</a>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Payment Options</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_PAYMENT_OPTIONS); ?>"); js_modal_show("#fluid-main-modal");'><i class="fa fa-credit-card" aria-hidden="true" style='padding-right: 5px;'></i> PAYMENT OPTIONS</a>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Returns & Refunds</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_RETURN_POLICY); ?>"); js_modal_show("#fluid-main-modal");'><i class="fa fa-archive" aria-hidden="true" style='padding-right: 5px;'></i> RETURNS & REFUNDS</a>
					<a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Shipping Policy</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_SHIPPING_POLICY); ?>"); js_modal_show("#fluid-main-modal");'><i class="fa fa-truck" aria-hidden="true" style='padding-right: 5px;'></i> SHIPPING POLICY</a>
                <?php /*
                <a href="<?php echo $_SESSION['fluid_uri'];?>Fuji-Try-and-Buy"><i class="fa fa-camera" aria-hidden="true" style='padding-right: 5px;'></i> FUJI TRY AND BUY PROGRAM</a>
                */
                ?>
				</div>
			</div>
			<?php
			/*
			<div class="col-sm-6 col-md-3">
				<h4>Customer Service</h4>
				<a href="http://www.leoscamera.com">CONTACT US</a>
				<a href="http://www.leoscamera.com">MANUFACTUERERS DIRECTORY</a>
				<a href="http://www.leoscamera.com">SITEMAP</a>
			</div>
			*/
			?>
			<div class="col-sm-12 col-md-12">
				<div><p class="footer-copyright">Copyright © <?php echo date("Y"); ?> Leo's Camera Supply Ltd.</p></div>
			</div>
		</div> <!-- row end -->
	</div> <!-- container end -->
</footer>


<!-- Nav scroll -->
<?php
$fluid = new Fluid();
?>
<script src="<?php echo $fluid->php_fluid_auto_version(FOLDER_ROOT, 'js/scrolling-nav.js');?>"></script> <!-- must be loaded after the html -->

</div> <?php //Header fluid-blur-wrap ?>

<div class='css-loading-overlay' id='loading-overlay'>
	<div style='position: absolute; top: 50%; width: 100%; margin:0 auto; opacity: 1.0;'>
		<div style='display:table; margin: 0 auto;'><h3><i class="fa fa-refresh fa-spin-fluid fa-4x fa-fw"></i></h3><span class="sr-only">Loading...</span></div>
	</div>
</div>

<?php
// fluid-helper-div --> Used by fluid.listing.php. A empty div to insert hidden data if required.
?>
<div id='fluid-helper-div'>
</div>

<?php
	echo HTML_MODAL_LOGIN;
	//echo HTML_MODAL_REGISTER;
	echo HTML_MODAL_REGISTER_EMPTY;
	echo HTML_MODAL_FORGET;
	echo HTML_ERROR_MODAL;
	echo HTML_MODAL_FLUID;
	echo HTML_MODAL_FLUID_MSG;
	echo HTML_MODAL;

	if(empty($_SESSION['u_id']))
		echo HTML_CHECKOUT_GUEST_MODAL_FLUID;
?>
