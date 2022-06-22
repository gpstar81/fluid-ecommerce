<?php
// Keep track of the previous page. Checkout uses this for returning back out of the checkout to the previous page on the site for browsing etc.
if(isset($_SESSION['current_page']))
	if($_SESSION['current_page'] != 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}")
		$_SESSION['previous_page'] = $_SESSION['current_page'];

$_SESSION['current_page'] = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

// 5 Global variables for handling the cart dropdown.
$fluid_cart_html = NULL;
$fluid_cart_num_items = NULL;
$fluid_cart_html_editor = NULL;
$fluid_cart_html_ship = NULL;
$fluid_cart_html_ship_select = NULL;
$fluid_cart_html_animate = NULL;
$fluid_cart_error = NULL;

function php_load_header($cart = FALSE) {
	require_once(FLUID_ACCOUNT);
	require_once(FLUID_CART);

	$detect = new Mobile_Detect;

	// Load the persistence cart if required.
	php_cart_persistence();

	// Not logged in, but found a FLUID COOKIE. Lets see if we can auto log the user in.
	if(isset($_COOKIE[FLUID_COOKIE]) && isset($_SESSION['u_id']) == FALSE)
		php_fluid_login_cookie();

	$fluid_header = new Fluid ();
	$fluid_header->php_debug($_SESSION);
	//$fluid_header->php_debug($_COOKIE);	
?>

<?php // <!-- Google api platform --> ?>
<script src="https://apis.google.com/js/platform.js" async defer></script>

<?php // <!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/jquery-3.1.1.min.js');?>"></script>

<?php // <!-- jQuery ui 1.12.1 (shake effect for adding to cart) --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/jquery-ui.min.js');?>"></script>

<?php // <!-- jQuery visible plugin. Check if a dom is visible in the viewport range. --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/jquery.visible.min.js');?>"></script>

<?php // <!-- bootstrap --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/bootstrap.min.js');?>"></script>
<?php
if($cart == TRUE) {
?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/bootstrap-select.min.js');?>"></script>
<?php
}
else if($_SERVER['REQUEST_URI'] == "/account" || $_SERVER['REQUEST_URI'] == "/account/orders" || $_SERVER['REQUEST_URI'] == "/account/address") {
?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/bootstrap-select.min.js');?>"></script>
<?php
}
?>

<?php // <!-- bootstrap validator for bootstrap 3 --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/validator.min.js');?>"></script>

<?php // <!-- format numbers --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/numeral.min.js');?>"></script>

<?php // <!-- animation library --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/mo.min.js');?>"></script>

<?php // <!-- Load the Facebook SDK --> ?>
<script type="text/javascript" src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/facebook.js');?>"></script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places" async defer></script>

<?php // <!-- Load block animation --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/anime.min.js');?>"></script>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/fluid.animate.js');?>"></script>

<?php // <!-- Swiper --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/swiper.min.js');?>"></script>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/swiper.jquery.min.js');?>"></script>

<?php // <!-- base64 encoding library --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'js/base64.min.js');?>"></script>

<?php
/*
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-21150353-1']);
  _gaq.push(['_trackPageview']);

  (function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
*/

if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MTMBX6P"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<!-- Google Analytics -->
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-21150353-5', 'auto');
	ga('send', 'pageview');
	ga('require', 'ec');
	</script>
	<!-- End Google Analytics -->
<?php
}
?>

<script>
	$(document).ready(function() {
		<?php
			// --> Run any autoload button clicks.
			if(isset($_GET['fb'])) {
				$fluid_btn_load = base64_decode($_GET['fb']);
			?>
				if(document.getElementById('<?php echo $fluid_btn_load;?>') != null)
					document.getElementById('<?php echo $fluid_btn_load;?>').click();

			<?php
			}
		?>

		FluidMenu.cart['cart_badge_div'] = document.getElementById('fluid-cart-badge');
		FluidMenu.cart['cart_badge_dropdown_div'] = document.getElementById('fluid-cart-badge-dropdown');
		FluidMenu.cart['cart_badge_mobile_div'] = document.getElementById('fluid-cart-badge-mobile');

		FluidMenu.cart['fluid-cart-dropdown'] = document.getElementById('fluid-cart-dropdown');
		FluidMenu.cart['fluid-cart-dropdown-nav'] = document.getElementById('fluid-cart-dropdown-nav');
		FluidMenu.cart['fluid-cart-dropdown-mobile'] = document.getElementById('fluid-cart-dropdown-mobile');
		FluidMenu.cart['fluid-cart-totals'] = document.getElementById('fluid-cart-totals');

		FluidHTML['cart_badge_div'] = document.getElementById('fluid-cart-badge');
		FluidHTML['cart_badge_dropdown_div'] = document.getElementById('fluid-cart-badge-dropdown');
		FluidHTML['cart_badge_mobile_div'] = document.getElementById('fluid-cart-badge-mobile');

		FluidHTML['fluid-cart-dropdown'] = document.getElementById('fluid-cart-dropdown');
		FluidHTML['fluid-cart-dropdown-nav'] = document.getElementById('fluid-cart-dropdown-nav');
		FluidHTML['fluid-cart-dropdown-mobile'] = document.getElementById('fluid-cart-dropdown-mobile');

		FluidMenu.f_overlay = document.getElementById("loading-overlay");

		<?php
		// Detect ios 11_0_x affected
		// NEED TO BE UPDATED if new versions are affected
		?>
		var ua = navigator.userAgent,
		iOS = /iPad|iPhone|iPod/.test(ua),
		iOS11 = /OS 11_0_1|OS 11_0_2|OS 11_0_3|OS 11_1/.test(ua);

		<?php // ios 11 bug caret position ?>
		<?php //if ( iOS && iOS11 ) { ?>
		if(iOS) {
			<?php // Add CSS class to body ?>
			$("body").addClass("iosBugFixCaret");
		}

		<?php
		// --> Feedback is set on a second timer.
		if($cart == FALSE) {
			if(empty($_SESSION['f_feedback'])) {
				if(FLUID_FEEDBACK_ENABLE == TRUE && isset($_SESSION['previous_page']) && isset($_SESSION['current_page'])) {
					if($_SESSION['previous_page'] != $_SESSION['current_page']) {
						if($_SESSION['previous_page'] == 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/checkout') {
							echo "js_fluid_feedback_load();";
						}
						else if(FLUID_FEEDBACK_ENABLE == TRUE) {
							//echo "clearTimeout(FluidTimer);";
							//echo "FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, " . FLUID_FEEDBACK_TIMER_LENGTH . ");";
						}
					}
					else if(FLUID_FEEDBACK_ENABLE == TRUE) {
						//echo "clearTimeout(FluidTimer);";
						//echo "FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, " . FLUID_FEEDBACK_TIMER_LENGTH . ");";
					}
				}
				else if(FLUID_FEEDBACK_ENABLE == TRUE) {
					//echo "clearTimeout(FluidTimer);";
					//echo "FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, " . FLUID_FEEDBACK_TIMER_LENGTH . ");";
				}
			}
		}


		if(FLUID_STORE_MESSAGE_MODAL_ENABLED == TRUE) {
			if(empty($_SESSION['f_modal_status'])) {
				$_SESSION['f_modal_status'] = true;

				echo "js_fluid_modal_status_load();";
			}
		}

		?>
	});
</script>

<script>
	var FluidMenu = {};
		FluidMenu.account = {}; <?php // --> Is this used? ?>
		FluidMenu.cart = {};
		FluidMenu.button = {};
			FluidMenu.button.obj = null;
			FluidMenu.button.id = null;
			FluidMenu.button.obj_div = null;
			FluidMenu.button.obj_div_id = null;
		FluidMenu.f_overlay = null;

	var	FluidTemp = {}; <?php // Temp variable used for various things. ?>
		FluidTemp.ajax_loading = false;
		FluidTemp.f_feedback = false;

	var FluidTimer;

	<?php // FluidTime is used for real time live searches. ?>
	var FluidTime = {};
		FluidTime.f_search_suggestion_time = 0; <?php // Currently used for the real time search suggestions to prevent old result sets overwriting the latest ones. ?>
		FluidTime.f_results = {};
		FluidTime.f_results_map = [];
		FluidTime.f_width = {};
		FluidTime.f_search_timeout;
		FluidTime.f_ajax_hidden;
		FluidTime.f_mobile = false;

	var FluidHTML = {};
	var Fluid_ga_cart = {};

	<?php
	if($cart == FALSE && FLUID_FEEDBACK_ENABLE == TRUE && empty($_SESSION['f_feedback'])) {
	?>
		if(FluidTemp.f_feedback == false) {
			$(window).scroll(function() {
				<?php
					// --> Feedback is set on a 60 second timer.
					if($cart == FALSE && FLUID_FEEDBACK_ENABLE == TRUE && empty($_SESSION['f_feedback'])) {
						//echo "clearTimeout(FluidTimer);";
						//echo "FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, " . FLUID_FEEDBACK_TIMER_LENGTH . ");";
					}
				?>
			});

			$(document).on('click touchstart', function () {
				<?php
					// --> Feedback is set on a 60 second timer.
					if($cart == FALSE && FLUID_FEEDBACK_ENABLE == TRUE && empty($_SESSION['f_feedback'])) {
						//echo "clearTimeout(FluidTimer);";
						//echo "FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, " . FLUID_FEEDBACK_TIMER_LENGTH . ");";
					}
				?>
			});
		}
	<?php
	}
	?>

	$('body,html').on("click scroll wheel DOMMouseScroll mousewheel keyup touchstart touchmove touchend touchcancel", function(e){
		<?php // Keep dropdown menu's from closing if they are clicked from inside. ?>
		$('.fluid-stay-open').click(function(e) {
			if(e.target.name != "fluid_toggle_close") {
				e.stopPropagation();
			}
		});

		$('#fluid-search-input').click(function(e) {
		    e.stopPropagation();
		});

		if(js_viewport_size()['width'] < 768) {
			$('.fluid-dropdown-parent').on({
				"shown.bs.dropdown": function() { this.closable = false; },
				"click":             function() { this.closable = true; },
				"hide.bs.dropdown":  function() { return this.closable; }
			});
		}

		<?php
		if($detect->isMobile()) {
		?>
			$('body').on('shown.bs.modal', function(){
				$("#fluid-blur-wrap").css({"touch-action":"none"}); <?php // chrome version 56 ignores touchmove and touchstart e.preventDefault. So must assign touch-action instead. ?>

			});

			$('body').on('hidden.bs.modal', function(){
				$("#fluid-blur-wrap").css({"touch-action":"auto"}); <?php // chrome version 56 ignores touchmove and touchstart e.preventDefault. So must assign touch-action instead. ?>
			});
		<?php
		}
		?>

	});

	<?php // --> Returns TRUE if a modal is currently open on the screen. ?>
	function js_modal_open() {
		return $('.modal.in').length > 0;
	}

	<?php // --> Fixes bug in ios where it reloads the page when doing the back state to prevent the loading screen from appearing which was the last state. ?>
	$(window).bind("pageshow", function(event) {
		if (event.originalEvent.persisted) {
			window.location.reload()
		}
	});

	<?php
	if($cart == FALSE) {
	?>
		</script>
		<?php
			echo php_load_full_scripts();
		?>
		<script>
	<?php
	}
	?>

	<?php

	if(FLUID_STORE_MESSAGE_MODAL_ENABLED == TRUE) {
	?>
	function js_fluid_modal_status_load() {
		try {
			document.getElementById('fluid-modal').innerHTML = Base64.decode('<?php echo base64_encode(HTML_SUPPORT_DIALOG);?>');
			document.getElementById('store-message-modal').innerHTML = Base64.decode('<?php echo FLUID_STORE_MESSAGE_MODAL;?>');

			js_modal_show('#fluid-modal');
			<?php // --> If a modal is open, do nothing.?>
			if(js_modal_open()) {

			}
			else {
				document.getElementById('fluid-modal').innerHTML = Base64.decode('<?php echo base64_encode(HTML_SUPPORT_DIALOG);?>');
				document.getElementById('store-message-modal').innerHTML = Base64.decode('<?php echo FLUID_STORE_MESSAGE_MODAL;?>');
				js_modal_show('#fluid-modal');
			}
		}
		catch(err) {
			js_debug_error(err);
		}
	}
	<?php
	}
	?>

	<?php
	// --> Enable the feedback module?
	if(FLUID_FEEDBACK_ENABLE == TRUE) {
	?>
		function js_fluid_feedback_load() {
			try {
				<?php // --> If a modal is open, lets reset the timer and not open the feedback. ?>
				if(js_modal_open()) {
					//clearTimeout(FluidTimer);
					//FluidTimer = setTimeout(function() { js_fluid_feedback_load(); }, <?php echo FLUID_FEEDBACK_TIMER_LENGTH; ?>);
				}
				else {
					document.getElementById('fluid-modal').innerHTML = Base64.decode('<?php echo base64_encode(HTML_FEEDBACK_DIALOG);?>');
					$('#fluid_form_feedback').validator();
					js_modal_show('#fluid-modal');
				}
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_feedback_send() {
			$('#fluid_form_feedback').validator().on('submit', function (e) {
			  if(e.keyCode == 13)
					e.isDefaultPrevented();

			  if (e.isDefaultPrevented()) {
			  } else {
				e.isDefaultPrevented();
				e.preventDefault(e);

				$('#fluid_form_feedback').validator('destroy');

				try {
					//clearTimeout(FluidTimer);
					FluidTemp.f_feedback = true;

					var FluidFeedback = {};
						FluidFeedback.f_reason = document.getElementById('f-feedback-reason').options[document.getElementById('f-feedback-reason').selectedIndex].value;
						FluidFeedback.f_comment = document.getElementById('f-feedback-comment').value;
						FluidFeedback.f_exit = document.getElementById('f-feedback-exit').value;
						FluidFeedback.f_find = document.getElementById('f-feedback-find').options[document.getElementById('f-feedback-find').selectedIndex].value;
						FluidFeedback.f_likely = document.getElementById('f-feedback-likely').options[document.getElementById('f-feedback-likely').selectedIndex].value;
						FluidFeedback.f_rate = document.getElementById('f-feedback-rate').options[document.getElementById('f-feedback-rate').selectedIndex].value;
						FluidFeedback.f_extra = document.getElementById('f-feedback-extra').value;

					var data = Base64.encode(JSON.stringify(FluidFeedback));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_FEEDBACK;?>", dataobj: "load_func=true&fluid_function=php_fluid_feedback_record&data=" + data}));

					$('#fluid_form_feedback').validator('destroy');

					js_fluid_ajax(data_obj);
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_feedback_none() {
			try {
				//clearTimeout(FluidTimer);
				FluidTemp.f_feedback = true;

				var FluidFeedback = {};
					FluidFeedback.f_feedback = true;

				var data = Base64.encode(JSON.stringify(FluidFeedback));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_FEEDBACK;?>", dataobj: "load_func=true&fluid_function=php_fluid_feedback_none&data=" + data}));

				$('#fluid_form_feedback').validator('destroy');

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}
	<?php
	}
	?>

	function js_viewport_size() {
		var e = window, a = 'inner';
		if (!('innerWidth' in window )) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
	}

	function base64EncodingUTF8(str) {
		return Base64.encode(str);
	}

	function js_debug(data) {
		<?php
		if($_SERVER['SERVER_NAME'] == "local.leoscamera.com") {
		?>
			if(data)
				console.log(data);
			else
				console.log(FluidVariables);
		<?php
		}
		?>
	}

	function js_debug_error(err) {
		<?php // Output error to console log. ?>
		js_debug(err);
		document.body.style.cursor = "default";
		document.getElementById('modal-error-msg-div').innerHTML = err;

		<?php // Show the error message. ?>
		js_modal_show('#fluid-error-modal');
	}

	function js_modal_hide(modal_id) {
		$(modal_id).modal('hide');
	}

	function js_modal_show(modal_id) {
		$(modal_id).modal({backdrop: 'static', keyboard: false});
		$('.header-menu-toggle').parent().removeClass('open'); <?php // Closes the header login drop down menu. ?>
	}

	function js_modal_show_data(data) {
		$(Base64.decode(data['modal_id'])).modal({backdrop: 'static', keyboard: false});
		$('.header-menu-toggle').parent().removeClass('open'); <?php // Closes the header login drop down menu. ?>
	}

	function js_close_toggle_menus() {
		js_fluid_process_search_suggestions_force_close();

		$('[data-toggle="dropdown"]').parent().removeClass('open'); <?php // Closes any opened bootstrap dropdowns. ?>

		<?php
		if($cart == FALSE) {
		?>
			js_fluid_nav_toggle_close();
		<?php
		}
		?>
	}

	function js_fluid_ajax_hidden_search(data_obj_tmp, element, debug) {
		var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
		var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

		<?php
			if($_SERVER['SERVER_NAME'] == "local.leoscamera.com") {
			?>
				js_debug(data_obj.serverurl + "?" + data_obj.dataobj);
			<?php
			}
		?>

		FluidTime.f_ajax_hidden = $.ajax({
			url: http + data_obj.serverurl,
			type: 'POST',
			data: data_obj.dataobj,

			error: function(jqXHR, textStatus, errorThrown){
				//js_debug_error(errorThrown);
			},
			success: function(f_data){
				if(debug == true) {
					setTimeout(function() {js_loading_stop();}, 1000);
					var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=780, height=200, top="+(screen.height-400)+", left="+(screen.width-840));
					win.document.body.innerHTML = f_data;
				}


				setTimeout(function() {js_fluid_ajax_process(f_data, element, true);}, 0);
			},
			timeout: 60000<?php // sets timeout to 60 seconds ?>
		});
	}

	function js_fluid_ajax(data_obj_tmp, debug) {
		try {
			<?php // Enable the loading screen. Prevents keyboard and mouse input and also greys the screen for loading and displays the loading cursor. ?>
			<?php //js_debug(data_obj.serverurl + "?" + data_obj.dataobj); ?>
			js_loading_start();

			var data_obj = JSON.parse(Base64.decode(data_obj_tmp));
			$('select').blur();
			var http = "<?php echo $_SERVER['REQUEST_SCHEME'];?>://";

			<?php
			if($_SERVER['SERVER_NAME'] == "local.leoscamera.com") {
			?>
				js_debug(data_obj.serverurl + "?" + data_obj.dataobj);
			<?php
			}
			?>

			$.ajax({
				url: http + data_obj.serverurl,
				type: 'POST',
				data: data_obj.dataobj,
				statusCode: {
					500: function() {
						console.log('500: server error');
						js_redirect_url({url:'<?php echo base64_encode($_SESSION['current_page']);?>'});
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					<?php // will fire when timeout is reached ?>
			        if(textStatus==="timeout") {
						js_loading_stop();
						js_debug_error("Connection timeout");
					}
					else {
						js_loading_stop();
						js_fluid_ajax(data_obj_tmp, debug);
					}
				},
				success: function(f_data){
					js_fluid_ajax_process(f_data);
					<?php //setTimeout(function() {js_fluid_ajax_process(f_data);}, 500); ?>
				},
				timeout: 600000 <?php // sets timeout to 10 minutes, 600 seconds. ?>
			});
		}
		catch (err) {
			<?php // --> Remove or fix this debug error. It can cause empty error msgs when connection fails. A good way to test is disconnect from the internet after the site has loaded and try to ajax load something. ?>
			<?php //js_debug_error(err); ?>
			js_loading_stop();
			js_debug_error("session timeout");
		}
	}

	function js_fluid_ajax_process(data) {
		try {
			js_loading_stop();
			var arrayData = JSON.parse(data);
			if(arrayData['error'] == 1 && typeof arrayData['error'] != 'undefined') {
				js_debug_error(Base64.decode(arrayData['error_message']));
			}
			else {
				<?php // Execute commands and send data to functions. ?>
				if(arrayData['js_execute_array'] == 1) {
					var data_array = JSON.parse(Base64.decode(arrayData['js_execute_functions']));
					try {
						for (i = 0; i < data_array.length; i++) {
							<?php
							if($_SERVER['SERVER_NAME'] == "local.leoscamera.com") {
							?>
								console.log(data_array[i]['function']);
							<?php
							}
							?>

							<?php //window[data_array[i]['function']](JSON.parse(Base64.decode(data_array[i]['data']))); ?>
							if(typeof data_array[i]['data'] != 'undefined')
								window[data_array[i]['function']](JSON.parse(Base64.decode(data_array[i]['data'])));
							else
								window[data_array[i]['function']](arrayData);
						}
					}
					catch(err) {
						js_debug_error(err);
					}
				}
			}
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	<?php // Check if a DOM element has a requested class. Returns true or false. ?>
	function js_has_class( elem, klass ) {
		return (" " + elem.className + " " ).indexOf( " "+klass+" " ) > -1;
	}

	<?php // Fluid shipping estimator ?>
	function js_fluid_ship_editor() {
		<?php
		if($cart == FALSE) {
		?>
			if($('#fluid-cart-dropdown').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html_ship'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html'] });

			if($('#fluid-cart-dropdown-nav').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html_ship'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html'] });

			if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html_ship'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html'] });
		<?php
		}
		?>
	}

	<?php // Fluid shipping estimator selector ?>
	function js_fluid_ship_select() {
		<?php
		if($cart == FALSE) {
		?>
			if($('#fluid-cart-dropdown').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html_ship_select'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html'] });

			if($('#fluid-cart-dropdown-nav').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html_ship_select'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html'] });

			if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html_ship_select'] });
			else
				js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html'] });
		<?php
		}
		?>
	}

	<?php // Fluid cart editor. ?>
	function js_fluid_cart_editor() {
		<?php
		if($cart == TRUE) {
		?>
			FluidMenu.cart['html'] = Base64.encode(FluidMenu.cart['fluid-cart-dropdown'].innerHTML);
		<?php
		}
		?>

		if($('#fluid-cart-dropdown').css('display') != 'none')
			FluidMenu.cart['fluid-cart-dropdown'].innerHTML = Base64.decode(FluidMenu.cart['html_editor']);
			<?php //js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html_editor'] }); ?>
		<?php
		/* // --> Speed improvements on item listing pages.
		//else
			//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html'] });
		*/
		?>

		<?php
		if($cart == FALSE) {
		?>
			if($('#fluid-cart-dropdown-nav').css('display') != 'none')
				FluidMenu.cart['fluid-cart-dropdown-nav'].innerHTML = Base64.decode(FluidMenu.cart['html_editor']);
				<?php //js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html_editor'] }); ?>
		<?php
		/* // --> Speed improvements on item listing pages.
			//else
				//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html'] });
		*/
		?>
			if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
				FluidMenu.cart['fluid-cart-dropdown-mobile'].innerHTML = Base64.decode(FluidMenu.cart['html_editor']);
				<?php //js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html_editor'] }); ?>
		<?php
		/*
			//else
			//	js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html'] });
		*/
		?>
		<?php
		}
		?>
	}

	<?php // Fluid cart editor cancel. Revert back to show the cart. ?>
	function js_fluid_cart_editor_cancel(b_animate) {
		FluidMenu.cart['fluid-cart-dropdown'].innerHTML = "";
		<?php //js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : Base64.encode("") }); ?>

		if($('#fluid-cart-dropdown').css('display') != 'none') {
			FluidMenu.cart['fluid-cart-dropdown'].innerHTML = Base64.decode(FluidMenu.cart['html']);
			<?php //js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html'] }); ?>
			<?php
				if($cart == TRUE) {
					?>
						if(FluidMenu.paypal == true)
							js_fluid_paypal_button_render();

							if(document.getElementById('fluid-cart-totals') != null)
								document.getElementById('fluid-cart-totals').innerHTML = Base64.decode(FluidMenu.cart['totals_html']);
					<?php
				}
			?>
		}

		<?php
		if($cart == FALSE) {
		?>
			<?php //if($('#fluid-cart-dropdown-nav').css('display') != 'none') ?>
			<?php	//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : Base64.encode("") }); ?>

			<?php //if($('#fluid-cart-dropdown-mobile').css('display') != 'none') ?>
			<?php	//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : Base64.encode("") }); ?>

			FluidMenu.cart['fluid-cart-dropdown-nav'].innerHTML = Base64.decode(FluidMenu.cart['html']);
			FluidMenu.cart['fluid-cart-dropdown-mobile'].innerHTML = Base64.decode(FluidMenu.cart['html']);
			<?php
				//if($('#fluid-cart-dropdown-nav').css('display') != 'none')
					//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html'] });


				//if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
					//js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html'] });
			?>
		<?php
		}
		?>

		if(b_animate == true) {
			<?php
			if($cart == TRUE) {
			?>
				<?php //document.getElementById('fluid-cart-totals').innerHTML = Base64.decode(FluidMenu.cart['totals_html']); ?>
				FluidMenu.cart['fluid-cart-totals'].innerHTML = Base64.decode(FluidMenu.cart['totals_html']);
			<?php
			}
			?>
			js_fluid_block_animate(null);
		}
	}

	<?php // This re-calculates the cart total while editing it for display purposes only. ?>
	function js_fluid_cart_editor_refresh() {
		try {
			var fluid_cart_items = document.getElementsByName("fluid-cart-editor-items");

			var fluid_cart_num_items = 0;
			var fluid_cart_total = 0;

			for(var x=0; x < fluid_cart_items.length; x++) {
				var id_tmp = fluid_cart_items[x].getAttribute('data-id');
				var id_key = fluid_cart_items[x].getAttribute('data-key');
				var price_tmp = Base64.decode(fluid_cart_items[x].getAttribute('data-price'));

				fluid_cart_num_items = parseInt(fluid_cart_num_items) + parseInt(document.getElementById('fluid-cart-editor-qty-' + id_key).value);
				fluid_cart_total = fluid_cart_total + (parseInt(document.getElementById('fluid-cart-editor-qty-' + id_key).value) * price_tmp);
			}

			var fluid_cart_total_format = numeral(fluid_cart_total);
			document.getElementById('fluid_cart_total_editor_div').innerHTML = fluid_cart_total_format.format('0,0.00');
			document.getElementById('fluid_cart_num_items_editor').innerHTML = fluid_cart_num_items;
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	<?php // Load a cart accessory modal if required. ?>
	function js_fluid_cart_accessories(data) {
		<?php // --> If a modal is open, Do nothing ?>
		if(js_modal_open()) {
			<?php // --> Do nothing. ?>
		}
		else {
			document.getElementById('fluid-modal').innerHTML = Base64.decode(data['html']);

			if(data['data'] != null) {
				var f_slides = data['i_max'];
				var f_slides_sm = 1;

				if(f_slides > 3) {
					f_slides = 3;
				}

				if(f_slides > 1) {
					f_slides_sm = 2;
				}

				if(f_slides > 1) {
					for(var x=0; x < data['data'].length; x++) {
						var swiper_modal = new Swiper('.swiper-container-modal-' +  data['data'][x], {
							pagination: '.swiper-pagination-modal-' + data['data'][x],
							nextButton: '.swiper-button-next',
							prevButton: '.swiper-button-prev',
							paginationClickable: true,
							slidesPerView: f_slides,
							spaceBetween: 5,
							loop: true,
							autoplay: 12000,
							observer: true, <?php // --> Forces it to render correctly while hidden. ?>
							observeParents: true, <?php // --> Forces it to render correctly while hidden. ?>
							breakpoints: {
								1024: {
									slidesPerView: f_slides,
									spaceBetween: 5
								},
								768: {
									slidesPerView: f_slides,
									spaceBetween: 5
								},
								640: {
									slidesPerView: f_slides,
									spaceBetween: 10
								},
								550: {
									slidesPerView: f_slides_sm,
									spaceBetween: 10
								},
								320: {
									slidesPerView: 1,
									spaceBetween: 10
								}
							}
						});
					}
				}

				js_modal_show("#fluid-modal");
			}

		}
	}

	<?php // Reset the fluid menu cart data. ?>
	function js_fluid_cart_reset(data) {
		FluidMenu.cart['html'] = data['html'];
		FluidMenu.cart['num_items'] = data['num_items'];
		FluidMenu.cart['html_editor'] = data['html_editor'];
		FluidMenu.cart['html_ship'] = data['html_ship'];
		FluidMenu.cart['html_ship_select'] = data['html_ship_select'];

		<?php
		if($cart == FALSE) {
		?>
			FluidMenu.cart['cart_badge_div'].innerHTML = Base64.decode(data['num_items']);
			FluidMenu.cart['cart_badge_dropdown_div'].innerHTML = Base64.decode(data['num_items']);
			FluidMenu.cart['cart_badge_mobile_div'].innerHTML = Base64.decode(data['num_items']);
		<?php
		}
		?>
	}

	<?php // Saves and updates the cart changes. ?>
	function js_fluid_cart_save() {
		try {
			js_loading_start();

			var FluidCart = {};
				FluidCart.f_items = {};

			<?php // --> A bit of a speed increase by not searching the entire dom document for items, but in a dom element instead. ?>
			if($('#fluid-cart-dropdown').css('display') != 'none')
				var childs = FluidMenu.cart['fluid-cart-dropdown'].getElementsByClassName("fluid-cart-editor-items");
			else if($('#fluid-cart-dropdown-nav').css('display') != 'none')
				var childs = FluidMenu.cart['fluid-cart-dropdown-nav'].getElementsByClassName("fluid-cart-editor-items");
			else if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
				var childs = FluidMenu.cart['fluid-cart-dropdown-mobile'].getElementsByClassName("fluid-cart-editor-items");
			else
				var childs = null;

			<?php // --> A bit faster than search for dom elements. ?>
			if(childs != null) {
				for(i = 0 , j = childs.length; i < j ; i++ ) {
					var f_child = childs[i].getElementsByClassName("fluid-cart-editor-qty");

					if(f_child[0] != null) {
						var id_tmp = childs[i].getAttribute('data-id');
						var id_key = childs[i].getAttribute('data-key')
						var cart_tmp = {};

						cart_tmp.p_id = id_tmp;
						cart_tmp.id_key = id_key;
						cart_tmp.p_qty = f_child[0].value;

						FluidCart.f_items[id_key] = cart_tmp;
					}
				}
			}
			else {
				<?php // --> Revert back to the old code which is a bit slower. ?>
				var fluid_cart_items = document.getElementsByName("fluid-cart-editor-items");

				for(var x=0; x < fluid_cart_items.length; x++) {
					var id_tmp = fluid_cart_items[x].getAttribute('data-id');
					var id_key = fluid_cart_items[x].getAttribute('data-key');
					var cart_tmp = {};

					cart_tmp.p_id = id_tmp;
					cart_tmp.p_qty = parseInt(document.getElementById('fluid-cart-editor-qty-' + id_key).value);
					cart_tmp.id_key = id_key;

					FluidCart.f_items[id_key] = cart_tmp;
				}
			}

			<?php
			if($cart == TRUE) {
			?>
				FluidCart['a_id'] = FluidMenu.shipping.a_id;
				FluidCart['f_refresh_shipping'] = FluidTemp.f_refresh_shipping;
			<?php
			}
			?>

			<?php
			if($cart == TRUE) {
			?>
				var data_tmp = FluidCart;
					data_tmp.f_checkout_id = FluidTemp.f_checkout_id;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&checkout=true&fluid_function=php_cart_update&data=" + data}));

				FluidTemp.f_refresh_shipping = false;
			<?php
			}
			else {
			?>
				var data = Base64.encode(JSON.stringify(FluidCart));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_cart_update&data=" + data}));
			<?php
			}
			?>

			js_fluid_ajax(data_obj);
		}
		catch(err) {
			js_loading_stop();
			js_debug_error(err);
		}
	}

	<?php // Block reveal animation function. This can block reveal any div. Array of divs can be passed with parameters. ?>
	function js_fluid_block_animate(data64) {
		if(data64 != undefined || data64 != null)
			FluidTemp.animate_array = data64;

		if(FluidTemp.animate_array != null) {
			var data = JSON.parse(Base64.decode(FluidTemp.animate_array));
			var f_delay_tmp = 0;

			var f_float_delay = 0;

			for(var key in data) {
				f_delay_tmp = parseInt(f_delay_tmp) + parseInt(data[key]['delay']);
				f_float_delay =  parseInt(f_float_delay) + parseInt(data[key]['delay']);

				<?php
				/*
					--> Block revealer content --> contentEl, needs to have css of
						flex: none;
						display: inline-block;
						text-align: right;

					for the content to display on the right inside the divs.... wwwwwweeeeeeeeeeeeeeeeeeee.....
				*/
				?>
				if(data[key]['id'] != null) {
					var eletmp = document.getElementById(Base64.decode(data[key]['id']));
					<?php //var incorrectIsVisible = window.getComputedStyle($('#' + Base64.decode(data[key]['id'])), null).getPropertyValue('display'); ?>

					if(eletmp != null) {
						if($('#' + Base64.decode(data[key]['id'])).css('display') != 'none' || $('#' + Base64.decode(data[key]['id'])).css('display') != null) {
							var rev = new RevealFx(document.querySelector('#' + Base64.decode(data[key]['id'])), {
								revealSettings : {
									bgcolor: data[key]['colour'],
									delay: f_delay_tmp,
									direction: 'lr',
									onStart: function(contentEl, revealerEl) {
										anime.remove(contentEl);
										contentEl.style.opacity = 0;
										//contentEl.id = "content-" + key;
									},
									onCover: function(contentEl, revealerEl) {
										anime({
											targets: contentEl,
											duration: 500,
											delay: 50,
											easing: 'easeOutBounce',
											translateX: [-40,0],
											opacity: {
												value: [0,1],
												duration: 300,
												easing: 'linear'
											}
										});

										<?php
										/*
										var fdiv = contentEl;
										var offset = $(fdiv).offset()
										var width = $(fdiv).width();
										var height = $(fdiv).height();

										var centerX = offset.left + width / 2;
										var centerY = offset.top + height / 2;

										burst1
											.tune({ x: centerX, y: centerY })
											.generate()
											.replay();
										*/
										?>
									},
								}
							});
							rev.reveal();
						}
					}
				}
			}
		}
	}

	<?php // Increase the quantity of a item in the cart. ?>
	function js_fluid_cart_decrease_num(qty_element) {
		var element = $('#' + qty_element);

		var v = element.val()-1;

		if(v >= element.attr('min'))
			element.val(v)
	}

	<?php // Increase the quantity of a item in the cart. ?>
	function js_fluid_cart_increase_num(qty_element) {
		var element = $('#' + qty_element);

		var v = element.val()*1+1;

		if(v <= element.attr('max'))
			element.val(v);
	}

	<?php // Loading screen. ?>
	function js_loading_start() {
		FluidTemp.ajax_loading = true;

		if(FluidMenu.f_overlay == null) {
			document.getElementById("loading-overlay").style.display = "block";
			FluidMenu.f_overlay = document.getElementById("loading-overlay");
		}
		else
			FluidMenu.f_overlay.style.display = "block";

		<?php
			//if(!$detect->isMobile())
				//echo "$(document.body).addClass('fluid-blur');";
		?>

		document.body.style.cursor = "wait";

		$(":input").prop("disabled", true);

		disableScroll();
	}

	<?php // Stop loading screen. ?>
	function js_loading_stop() {
		FluidTemp.ajax_loading = false;

		if(FluidMenu.f_overlay == null) {
			document.getElementById("loading-overlay").style.display = "none";
			FluidMenu.f_overlay = document.getElementById("loading-overlay");
		}
		else
			FluidMenu.f_overlay.style.display = "none";

		<?php
			//if(!$detect->isMobile())
				//echo "$(document.body).removeClass('fluid-blur');";
		?>

		document.body.style.cursor = "default";

		$(":input").prop("disabled", false);

		enableScroll();
	}

<?php // left: 37, up: 38, right: 39, down: 40, ?>
<?php // spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36 ?>
var keys = {37: 1, 38: 1, 39: 1, 40: 1};

function preventDefault(e) {
  e = e || window.event;
  if (e.preventDefault)
      e.preventDefault();
  e.returnValue = false;
}

function preventDefaultForScrollKeys(e) {
    if (keys[e.keyCode]) {
        preventDefault(e);
        return false;
    }
}

<?php
/*
function touchstart(e) {
    e.preventDefault();
}

function touchmove(e) {
    e.preventDefault();
}
*/
?>

function disableScroll() {
  if (window.addEventListener)
      window.addEventListener('DOMMouseScroll', preventDefault, false);

	window.onwheel = preventDefault;
	window.onmousewheel = document.onmousewheel = preventDefault;
	window.ontouchmove  = preventDefault;
	document.onkeydown  = preventDefaultForScrollKeys;

	<?php
	//document.addEventListener('touchstart', this.touchstart);
	//document.addEventListener('touchmove', this.touchmove);
	?>

	$("body").css({"touch-action":"none"}); <?php // chrome version 56 ignores touchmove and touchstart e.preventDefault. So must assign touch-action instead. ?>
}

function enableScroll() {
    if (window.removeEventListener)
        window.removeEventListener('DOMMouseScroll', preventDefault, false);

    window.onmousewheel = document.onmousewheel = null;
    window.onwheel = null;
    window.ontouchmove = null;
    document.onkeydown = null;

	<?php
	//document.removeEventListener('touchstart', this.touchstart);
	//document.removeEventListener('touchmove', this.touchmove);
	?>

	$("body").css({"touch-action":"auto"}); <?php // chrome version 56 ignores touchmove and touchstart e.preventDefault. So must assign touch-action instead. ?>
}

	<?php // Update any select pickers in case any are in the new innerHTML data. ?>
	function js_fluid_update_selectpicker() {
		try {
			<?php //$('select').selectpicker(); //$('.selectpicker').selectpicker(); ?>
			//$('select').selectpicker('refresh');
			<?php
			if($cart == TRUE) {
				if($detect->isMobile() && !$detect->isTablet())
					echo "$('select').selectpicker('mobile');";
				else
					echo "$('select').selectpicker();";
			}
			else if($_SERVER['REQUEST_URI'] == "/account" || $_SERVER['REQUEST_URI'] == "/account/orders" || $_SERVER['REQUEST_URI'] == "/account/address") {
				if($detect->isMobile() && !$detect->isTablet())
					echo "$('select').selectpicker('mobile');";
				else
					echo "$('select').selectpicker();";
			}
			?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	<?php // Insert some innerHTML into a element. ?>
	function js_html_insert(data) {
		try {
			document.getElementById(Base64.decode(data['div_id'])).innerHTML = Base64.decode(data['html']);
			js_fluid_update_selectpicker(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	function js_html_style_display(data) {
		try {
			document.getElementById(Base64.decode(data['div_id'])).style.display = Base64.decode(data['div_style']);
			js_fluid_update_selectpicker(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	function js_html_style_hide(data) {
		try {
			document.getElementById(Base64.decode(data['div_id_hide'])).style.display = "none";
			js_fluid_update_selectpicker(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	function js_html_remove_class(data) {
		try {
			document.getElementById(Base64.decode(data['div_id'])).className.replace( '/(?:^|\s)' + Base64.decode(data['class']) + '(?!\S)/g' , '' );
			js_fluid_update_selectpicker(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	function js_html_add_class(data) {
		try {
			document.getElementById(Base64.decode(data['div_id'])).className += " " + Base64.decode(data['class']);
			js_fluid_update_selectpicker(); <?php // Update any select pickers in case any are in the new innerHTML data. ?>
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	<?php // Send the browser to a defined url. ?>
	function js_redirect_url(data) {
		try {
			js_loading_start();
			window.location.href = Base64.decode(data['url']);
		}
		catch(err) {
			js_debug_error(err);
		}
	}
</script>

<?php
	if($cart == FALSE) {
		echo php_load_auto_scroll();

		?>
		<script>

			function initialize_map_header() {
				var latlng = new google.maps.LatLng(49.278781, -123.123779);

				var myOptions = {
				  zoom: 15,
				  center: latlng,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				};

				var f_header_map = new google.maps.Map(document.getElementById("map_canvas_header"),
					myOptions);

				var contentString = '<div id=\"content\">'+
					'<div id=\"siteNotice\">'+
					'</div>'+
					'<span class=\"icon-leos-logo-rotate\" style=\"font-size: 35px; color: red;\"></span>'+
					'<div id=\"bodyContent\" style=\"padding-top: 5px;\">'+
					'<div style=\"font-size: 10px\">1055 Granville Street</div><div style=\"font-size: 10px\">Vancouver, BC</div><div style=\"font-size: 10px\">Canada, V6Z1L4</div><div style=\"font-size: 10px\">Ph: 604-685-5331</div><div style=\"font-size: 10px\">Fax: 604-685-5648</div><div style=\"font-size: 10px\">www.leoscamera.com</div>'+
					'</div>';

				var infowindow = new google.maps.InfoWindow({
					content: contentString,
					maxWidth: 140
				});

				var marker = new google.maps.Marker({
				  position: latlng,
				  map: f_header_map,
				  title:"Leo's Camera Supply Ltd."
				});

				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(f_header_map,marker);
				});
			}

			function js_fluid_contact_us() {
				try {
					document.getElementById('modal-fluid-div').innerHTML = Base64.decode("<?php echo base64_encode(FLUID_HEADER_SEARCH_SPECIAL);?>");

					initialize_map_header();

					document.getElementById('modal-fluid-header-div').innerHTML = "Contact Us";
					js_modal_show("#fluid-main-modal");

					setTimeout(function() { initialize_map_header(); }, 1000);
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_search_suggestions(f_search) {
				try {
					<?php
					/*
					if(FluidTime.f_search_suggestion_time + 200 < Date.now()) { ?>
						if(FluidTime.f_search_timeout) {
							clearTimeout(FluidTime.f_search_timeout);
						}

						if(FluidTime.f_ajax_hidden) {
							FluidTime.f_ajax_hidden.abort();
						}

						FluidTime.f_search_timeout = setTimeout(function(){ js_fluid_search_suggestions_search(f_search); }, 0);
					}
					*/
					?>
					FluidTime.f_search_timeout = setTimeout(function(){ js_fluid_search_suggestions_search(f_search); }, 0);
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_search_keyup() {
				FluidTime.f_mobile = false;
				$('#fluid-search-input').keyup();
			}

			function js_fluid_search_keyup_mobile() {
				FluidTime.f_mobile = true;
				$('#fluid-search-input-navbar').keyup();
			}

			function js_fluid_search_overlay_open() {
				document.getElementById('fluid-search-blur-wrap').style.display = "block";
			}

			function js_fluid_search_overlay_closed() {
				document.getElementById('fluid-search-blur-wrap').style.display = "none";
			}

			function js_fluid_search_suggestions_has_focus(f_search) {
				try {
					if(f_search.length > 0) {
						if(FluidTime.f_mobile == true) {
							$('#fluid-search-input-navbar').focus();

							if($("#f-search-dropdown-mobile").hasClass("open") == false) {
								$('#f-search-suggestions-trigger-mobile').dropdown('toggle');

								$('#fluid-search-input-mobile').focus();

								js_fluid_search_overlay_open();
							}
						}
						else {
							document.getElementById('f-live-search-control').style.maxWidth = FluidTime.f_width[f_search];

							if($("#f-search-dropdown").hasClass("open") == false) {
								$('#f-search-suggestions-trigger').dropdown('toggle');

								$('#fluid-search-input').focus();

								js_fluid_search_overlay_open();
							}
						}
					}
					else {
						js_fluid_process_search_suggestions_force_close();
					}
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_process_search_suggestions_force_close() {
				try {
					if(FluidTime.f_mobile == true) {
						if($("#f-search-dropdown-mobile").hasClass("open") == true) {
							$('#f-search-suggestions-trigger-mobile').click();
						}
					}
					else {
						if($("#f-search-dropdown").hasClass("open") == true) {
							$('#f-search-suggestions-trigger').click();
						}
					}
					js_fluid_search_overlay_closed();
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_process_search_suggestions(f_data) {
				try {
					var f_input;

					if(FluidTime.f_mobile == true) {
						f_input = document.getElementById("fluid-search-input-navbar");
					}
					else {
						f_input = document.getElementById("fluid-search-input");
					}

					if(f_input.value.length > 0) {
						if(f_data["total"] < 1) {
							js_fluid_process_search_suggestions_force_close();
						}
						else {
							<?php // When we reached 5 stored searched results, remove the first element from the object. This helps with spam hits on the server and overall memory usage. ?>
							if(FluidTime.f_results != null) {
								if(Object.keys(FluidTime.f_results).length > 5) {
									delete FluidTime.f_results[FluidTime.f_results_map[0]];
									delete FluidTime.f_width[FluidTime.f_results_map[0]];
									FluidTime.f_results_map.shift();
								}
							}

							FluidTime.f_results[f_data["keywords"]] = f_data["html"];
							FluidTime.f_results_map[FluidTime.f_results_map.length] = f_data["keywords"];
							FluidTime.f_width[f_data["keywords"]] = f_data["width"];

							var f_html_holder;

							if(FluidTime.f_mobile == true) {
								f_html_holder = document.getElementById("f-live-search-mobile");
							}
							else {
								f_html_holder = document.getElementById("f-live-search");
							}

							f_html_holder.innerHTML = Base64.decode(FluidTime.f_results[FluidTime.f_results_map[FluidTime.f_results_map.length - 1]]);

							js_fluid_search_suggestions_has_focus(f_data["keywords"]);
						}
					}
					else {
						js_fluid_process_search_suggestions_force_close();
					}
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_search_suggestions_search(f_search) {
				try {
					FluidTime.f_search_suggestion_time = Date.now();

					if(f_search.length == 0) {
					  document.getElementById("f-live-search").innerHTML="";
					  document.getElementById("f-live-search").style.border="0px";

					  document.getElementById("f-live-search-mobile").innerHTML="";
					  document.getElementById("f-live-search-mobile").style.border="0px";

					  js_fluid_process_search_suggestions_force_close();
					}
					else {
						if(FluidTime.f_results[Base64.encode(f_search)] != null) {
							if(FluidTime.f_mobile == true) {
								document.getElementById("f-live-search-mobile").innerHTML = Base64.decode(FluidTime.f_results[Base64.encode(f_search)]);
							}
							else {
								document.getElementById("f-live-search").innerHTML = Base64.decode(FluidTime.f_results[Base64.encode(f_search)]);
							}

							js_fluid_search_suggestions_has_focus(f_search);
						}
						else {
							var FluidData = {};
								FluidData.f_search_input = f_search;
								FluidData.f_search_time = Math.floor(Date.now() / 1000);
								FluidData.f_mobile = FluidTime.f_mobile;

							var data = base64EncodingUTF8(JSON.stringify(FluidData));

							var data_obj = base64EncodingUTF8(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SEARCH_SUGGESTIONS;?>", dataobj: "load_func=true&fluid_function=php_search_suggestions&data=" + data}));

							js_fluid_ajax_hidden_search(data_obj);
						}
					}
				}
				catch(err) {
					js_debug_error(err);
				}
			}

			function js_fluid_search(f_element_name) {
				try {
					if(FluidTime.f_search_timeout) {
						clearTimeout(FluidTime.f_search_timeout);
					}

					if(FluidTime.f_ajax_hidden) {
						FluidTime.f_ajax_hidden.abort();
					}

					js_fluid_process_search_suggestions_force_close();

					if(document.getElementById(f_element_name).value == "hours" || document.getElementById(f_element_name).value == "Hours" || document.getElementById(f_element_name).value == "HOURS" || document.getElementById(f_element_name).value == " hours" || document.getElementById(f_element_name).value == " Hours" || document.getElementById(f_element_name).value == " HOURS" || document.getElementById(f_element_name).value == "hours " || document.getElementById(f_element_name).value == "Hours " || document.getElementById(f_element_name).value == "HOURS " || document.getElementById(f_element_name).value == "address" || document.getElementById(f_element_name).value == "Address" || document.getElementById(f_element_name).value == "ADDRESS" || document.getElementById(f_element_name).value == " address" || document.getElementById(f_element_name).value == " Address" || document.getElementById(f_element_name).value == " ADDRESS" || document.getElementById(f_element_name).value == "address " || document.getElementById(f_element_name).value == "Address " || document.getElementById(f_element_name).value == "ADDRESS") {

						document.getElementById(f_element_name).value = "";

						js_fluid_contact_us();

					}
					else if(document.getElementById(f_element_name).value == "") {

					}
					else if(document.getElementById(f_element_name).value == "rent" || document.getElementById(f_element_name).value == "Rent" || document.getElementById(f_element_name).value == "RENT" || document.getElementById(f_element_name).value == "rentals" || document.getElementById(f_element_name).value == "Rentals" || document.getElementById(f_element_name).value == "RENTALS") {
						<?php
							echo "js_redirect_url({url:Base64.encode(\"" .$_SESSION['fluid_uri'] . "Rentals\")});";
						?>
					}
					else {
						<?php
						echo "js_redirect_url({url:Base64.encode(\"" .$_SESSION['fluid_uri'] . FLUID_SEARCH_LISTING_REWRITE . "?f_search=\" + encodeURIComponent(document.getElementById(f_element_name).value))});";
						?>
					}
				}
				catch(err) {
					js_debug_error(err);
				}
			}
		</script>
		<?php
	}
?>

<script type="text/javascript">

	function js_navbar() {
		var f_viewport_size = js_viewport_size()['width'];
		var f_navbar = document.getElementById('fluid-navbar-icons-div');
		var f_navbar_header = document.getElementById('fluid-navigation-bar');

		<?php
			$f_detect = TRUE;
			$f_ipad = FALSE;
			$f_ios_version = 0;

			if($detect->isiOS()) {
				$f_ios_version = explode('_', $detect->version('iPad'))[0];

				if($detect->isTablet()) {
					$f_detect = TRUE;
					if(isset($f_ios_version)) {
						if($f_ios_version < 11)
							$f_ipad = TRUE; // --> Need to disable the search button on the navbar because of a onBlue bug when keyboard is brought up on older iOS devices.
						else
							$f_ipad = FALSE; // --> iOS version 11 or higher, fixes the scrolling to top onBlur bug when the keyboard is brought up. So lets enable the search button on the navbar for iOS devices 11 or higher.
					}
					else
						$f_ipad = TRUE;;

				}
			}
			else
				$f_detect = TRUE;
		?>

		if(!$('#fluid-header-top-logo').visible(true) || $('#fluid-header-top-logo').css('display') == 'none') {
			f_navbar.className = f_navbar.className.replace( /(?:^|\s)fluid-navbar-icons-hide(?!\S)/g , '' );
			f_navbar.className = f_navbar.className.replace( /(?:^|\s)fluid-navbar-icons(?!\S)/g , '' );

			f_navbar.className += " fluid-navbar-icons";

			<?php
			if($cart == FALSE) {
			?>
				$('.navbar-inverse').css({'background-color' : 'black' , 'transition' : '0.0s ease-in-out'});

				<?php
				if($f_detect == TRUE) {
				?>
					<?php
					if($f_ipad == FALSE) {
					?>
						var f_navbarsearch = document.getElementById('fluid-navbar-search-icon');
						f_navbarsearch.style.display = "inline-block";
					<?php
					}
					?>

					<?php
					//if(FLUID_NAVBAR_PIN == TRUE) {
					if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) {
					?>
						f_navbar_header.className = f_navbar_header.className.replace( /(?:^|\s)fluid-navbar-fixed-position(?!\S)/g , '' );
						f_navbar_header.className += " fluid-navbar-fixed-position";

						$('#fluid-nav-div-hidden').css({'margin-top' : '55px'});
					<?php
					}
					?>
				<?php
				}
				?>

			<?php
			}
			else if($cart == TRUE) {
			?>
				if(js_viewport_size()['width'] < 768) {
					$('.navbar-inverse').css({'background-color' : 'black' , 'transition' : '0.0s ease-in-out'});
				}
			<?php
			}
			?>
		}
		else {
			f_navbar.className = f_navbar.className.replace( /(?:^|\s)fluid-navbar-icons(?!\S)/g , '' )
			f_navbar.className = f_navbar.className.replace( /(?:^|\s)fluid-navbar-icons-hide(?!\S)/g , '' )
			f_navbar.className += " fluid-navbar-icons-hide";

			<?php
			if($cart == FALSE) {
			?>
				<?php
				if($f_detect == TRUE) {
				?>
					<?php
					if($f_ipad == FALSE) {
					?>
						var f_navbarsearch = document.getElementById('fluid-navbar-search-icon');
						f_navbarsearch.style.display = "none";
					<?php
					}
					?>

					<?php
					//if(FLUID_NAVBAR_PIN == TRUE) {
					if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) {
					?>
						f_navbar_header.className = f_navbar_header.className.replace( /(?:^|\s)fluid-navbar-fixed-position(?!\S)/g , '' );
						$('#fluid-nav-div-hidden').css({'margin-top' : '0px'});
					<?php
					}
					?>
				<?php
				}
				?>

			<?php
			}
			?>

			$('.navbar-inverse').css({'background-color' : '#282828' , 'transition' : '0.0s ease-in-out'});

			if(f_viewport_size > 767) {
				<?php
				// Lets flip the search bar back as required. Only on resolutions 768px or higher. mobile phones we do not need to.
				if($cart == FALSE) {
				?>
					js_fluid_navbar_search_flip_primary();
				<?php
				}
				?>
				js_close_toggle_menus();
			}
		}
	}

<?php
if($cart == TRUE) {
?>
<?php
/*
	var fluid_navbar_pinned = false;

		$(document).ready(function() {
			if(js_viewport_size()['width'] < 768 && fluid_navbar_pinned == false) {
				$('.navbar').scrollToFixed({
					zIndex: 1100
				});

				fluid_navbar_pinned = true;
				js_navbar();
			}

			$(window).resize(function(){
				if(js_viewport_size()['width'] < 768 && fluid_navbar_pinned == false) {
					$('.navbar').scrollToFixed({
						zIndex: 1100
					});

					fluid_navbar_pinned = true;
					js_navbar();
				}
				else if(js_viewport_size()['width'] > 767 && fluid_navbar_pinned == true) {
					$('.navbar').trigger('detach.ScrollToFixed');
					fluid_navbar_pinned = false;

					$('.navbar-inverse').css({'background-color' : '#656565' , 'transition' : '0.0s ease-in-out'});
				}
			});
		});
*/
?>
<?php
}
else if($cart == FALSE) {
?>
	<?php
	/*
	$(document).ready(function() {
		$('.navbar').scrollToFixed({
			zIndex: 1100
		});
	});
	*/
	?>
<?php
}
?>

</script>

<div id="fluid-blur-wrap">


<div class="container-fluid header-container">

<?php
if($cart == FALSE) {
	if(FLUID_STORE_OPEN == FALSE) {
		?>
			<div class="row" style='background-color: red;'>
				<div style='padding: 5px 0px 5px 0px; display: inline-block; width: 100%; text-align: center; margin: auto; font-weight: 500; color:white;'><?php echo base64_decode(FLUID_STORE_CLOSED_MESSAGE); ?></div>
			</div>
		<?php
	}

	if(FLUID_PAYMENT_SANDBOX == TRUE) {
		?>
			<div class="row" style='background-color: green;'>
				<div style='padding: 5px 0px 5px 0px; display: inline-block; width: 100%; text-align: center; margin: auto; font-weight: 500; color:white;'>Checkout sandbox mode enabled</div>
			</div>
		<?php
	}

	if(FLUID_STORE_MESSAGE_ENABLED == TRUE) {
		?>
			<div class="row" style='background-color: red;'>
				<div style='padding: 5px 0px 5px 0px; display: inline-block; width: 100%; text-align: center; margin: auto; font-weight: 500; color:white;'><?php echo base64_decode(FLUID_STORE_MESSAGE); ?></div>
			</div>
		<?php
	}
?>
  <div class="row header-top-bar">
    <div class="col-lg-4 col-md-4 col-sm-3 col-xs-7">
      <div class="header-social-contact-icon">
        <a href="https://www.facebook.com/LeosVancouver" target="_blank">
         <div class='fa fa-facebook-official' style='color: #3B5998;'></div>
        </a>
      </div>
      <div class="header-social-contact-icon">
        <a href="https://twitter.com/LeosCamera" target="_blank">
          <div class='fa fa-twitter-square' style='color: #2EAEF7;'></div>
        </a>
      </div>
      <div class="header-social-contact-icon">
        <a href="https://www.youtube.com/c/LeosCameraSupplyTV" target="_blank">
          <div class='fa fa-youtube-square' style='color: red;'></div>
        </a>
      </div>
      <div class="header-social-contact-icon">
        <a href="https://www.instagram.com/leoscamerasupply/" target="_blank">
          <div class='fa fa-instagram' style='color: black;'></div>
        </a>
      </div>
    </div>


    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-0">
		<?php
		if(FREE_SHIPPING_FORMULA_ENABLED == TRUE) {
			$f_shipping_value = "ON ORDERS";
			if(FREE_SHIPPING_CART_TOTAL_STEP_1 > 0) {
				$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_1 . " *";
			}
			else if(FREE_SHIPPING_CART_TOTAL_STEP_2 > 0) {
				$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_2 . " *";
			}
			else if(FREE_SHIPPING_CART_TOTAL_STEP_3 > 0) {
				$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_3 . " *";
			}
			else if(FREE_SHIPPING_CART_TOTAL_STEP_4 > 0) {
				$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_4 . " *";
			}
			else if(FREE_SHIPPING_CART_TOTAL_STEP_5 > 0) {
				$f_shipping_value .= " OVER $" . FREE_SHIPPING_CART_TOTAL_STEP_5 . " *";
			}
		?>
			<div class="header-free-shipping-container">
		      <div class="header-free-shipping-div" onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-text").innerHTML = "Close"; document.getElementById("modal-fluid-header-div").innerHTML = "<div style=\"font-weight: 500;\">Shipping Policy</div>"; document.getElementById("modal-fluid-div").innerHTML = Base64.decode("<?php echo base64_encode(HTML_SHIPPING_POLICY); ?>"); js_modal_show("#fluid-main-modal");'>
		        <img src='<?php echo $_SESSION['fluid_uri'];?>files/header-canadian-flag.png' style='margin-bottom: 1px;'></img><h5 class="header-free-shipping-headline">FREE SHIPPING</h5><?php echo $f_shipping_value; if($detect->isMobile()) { echo " <i class=\"fa fa-hand-pointer-o\" aria-hidden=\"true\"></i>"; }?>
		      </div>
			</div>
		<?php
		}
		?>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-3 col-xs-5">
      <div class="row header-link-container">
        <div class="header-block-phone-contact" style="text-align: right;">

			<?php
				$f_order_close = "<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='$(\"#fluid_form_order_check\").validator(\"destroy\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> <div id=\"fluid-modal-close-button-text\" style=\"display: inline-block;\">Cancel</div></button>";

				$f_order_search = "<button type=\"button\" class=\"btn btn-success\" onClick='document.getElementById(\"f-order-check-error\").innerHTML = \"\"; js_fluid_single_order_lookup();'><span class=\"glyphicon glyphicon-search\" aria-hidden=\"true\"></span> <div id=\"fluid-modal-close-button-text\" style=\"display: inline-block;\">Search</div></button>";
			?>
			<div class='header-email-text'>
				<div style='display: inline-block;'>
			        <a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-msg").innerHTML = Base64.decode("<?php echo base64_encode($f_order_search);?>"); document.getElementById("fluid-modal-back-button-msg").innerHTML = Base64.decode("<?php echo base64_encode($f_order_close);?>"); document.getElementById("modal-fluid-header-div-msg").innerHTML = "<div style=\"font-weight: 500;\">Order Status Lookup</div>"; document.getElementById("modal-fluid-div-msg").innerHTML = Base64.decode("<?php echo base64_encode(HTML_ORDER_CHECK); ?>"); document.getElementById("f-order-check-error").innerHTML = ""; $("#fluid_form_order_check").validator("update"); js_modal_show("#fluid-main-modal-msg");'><div class='fa fa-truck' style='color: #696969'></div> Order Status</a>
				</div>

				<div class="f-header-contact-padding">|</div>

				<div class="header-link-phone-number" style='display: inline-block;'><a href='tel:+16046855331'><div class='fa fa-phone' style='color: #696969'></div> 604-685-5331</a></div>

				<div class="f-header-contact-padding">|</div>

				<a class="" style='display: inline-block;' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_contact_us();'><div class='fa fa-question-circle-o' style='color: #696969;'></div> Contact Us</a>
			</div>

			<div class='header-email-mobile'>
				<div class="header-social-contact-icon" onmouseover="JavaScript:this.style.cursor='pointer';" onClick='document.getElementById("fluid-modal-close-button-msg").innerHTML = Base64.decode("<?php echo base64_encode($f_order_search);?>"); document.getElementById("fluid-modal-back-button-msg").innerHTML = Base64.decode("<?php echo base64_encode($f_order_close);?>"); document.getElementById("modal-fluid-header-div-msg").innerHTML = "<div style=\"font-weight: 500;\">Order Status Lookup</div>"; document.getElementById("modal-fluid-div-msg").innerHTML = Base64.decode("<?php echo base64_encode(HTML_ORDER_CHECK); ?>"); document.getElementById("f-order-check-error").innerHTML = ""; $("#fluid_form_order_check").validator("update"); js_modal_show("#fluid-main-modal-msg");'>
			        <div class='fa fa-truck' style='color: #696969'></div>
				</div>

				<div class="header-social-contact-icon">
			        <a href='tel:+16046855331'><div class='fa fa-phone-square' style='color: #696969'></div></a>
				</div>

				<div class="header-social-contact-icon">
			        <a onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_contact_us();'><div class='fa fa-question-circle-o' style='color: #696969;'></div></a>
				</div>
			</div>

        </div>
      </div>
    </div>

  </div>

<?php
}
?>

<?php
	// Load the cart data.
	if($cart == TRUE)
		$fluid_preload_data = php_html_cart(NULL, FALSE, TRUE);
	else
		$fluid_preload_data = php_html_cart(NULL, FALSE, FALSE, NULL, NULL, TRUE);

	global $fluid_cart_html;
	global $fluid_cart_num_items;
	global $fluid_cart_html_editor;
	global $fluid_cart_html_ship;
	global $fluid_cart_html_ship_select;
	global $fluid_cart_html_animate;
	global $fluid_cart_error;

	$fluid_cart_html = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[0]->data))->html);
	$fluid_cart_num_items = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[1]->data))->html);
	$fluid_cart_html_editor = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[2]->data))->html);
	$fluid_cart_html_ship = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[6]->data))->html);
	$fluid_cart_html_ship_select = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[7]->data))->html);
	$fluid_gs_cart_track = NULL;

	if($cart == FALSE) {
		$fluid_cart_html_animate = json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[8]->data));
		$fluid_gs_cart_track = json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[9]->data));
	}

	if($cart == TRUE)
		$fluid_cart_error = json_decode(base64_decode(json_decode(base64_decode(json_decode($fluid_preload_data)->js_execute_functions))[8]->data));
?>

<script>
	FluidMenu.cart['html'] = "<?php echo base64_encode($fluid_cart_html); ?>";
	FluidMenu.cart['num_items'] = "<?php echo base64_encode($fluid_cart_num_items); ?>";
	FluidMenu.cart['html_editor'] = "<?php echo base64_encode($fluid_cart_html_editor); ?>";
	FluidMenu.cart['html_ship'] = "<?php echo base64_encode($fluid_cart_html_ship); ?>";
	FluidMenu.cart['html_ship_select'] = "<?php echo base64_encode($fluid_cart_html_ship_select); ?>";

	<?php
	if($cart == FALSE) {
	?>
		FluidTemp.animate_array = "<?php echo($fluid_cart_html_animate);?>";
	<?php
		if(isset($fluid_gs_cart_track)) {
			?>
			Fluid_ga_cart = JSON.parse(Base64.decode("<?php echo base64_encode(json_encode($fluid_gs_cart_track));?>"));
			<?php
			/*
			foreach($fluid_gs_cart_track as $fg_key => $fg_cart) {
				?>
				Fluid_ga_cart[<?php echo $fg_key;?>] = {};
				<?php

				foreach($fg_cart as $fg_key_tmp => $fg_data) {
					?>
						Fluid_ga_cart[<?php echo $fg_key;?>]["<?php echo $fg_key_tmp;?>"] = "<?php echo $fg_data;?>";
					<?php
				}
			}
			*/
		}
	}

	if($cart == TRUE) {
	?>
		FluidTemp.f_stock_error = <?php echo $fluid_cart_error; ?>;
	<?php
	}
	?>
</script>

<?php
// --> Desktop > 767px
if($cart == TRUE) {
		if(FLUID_PAYMENT_SANDBOX == TRUE) {
		?>
			<div class="row" style='background-color: green;'>
				<div style='padding: 5px 0px 5px 0px; display: inline-block; width: 100%; text-align: center; margin: auto; font-weight: 500; color:white;'>Checkout sandbox mode enabled</div>
			</div>
		<?php
	}
?>
	 <div id="fluid-header-top-logo" class="row vcenter fluid-checkout-header" style="padding: 10px 0 10px 0;">
		<div class='fluid-cart-content' style='display: table;'>

			<div style="display: table-cell; text-align: left; width: 50px; vertical-align: middle;" onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_keep_shopping();'>
				<span style="font-size: 34px; padding-bottom: 5px;" class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
			</div>

			<div class="header-logo" style='display: table-cell; text-align: left; vertical-align: middle;'>
				<span class="icon-leos-logo-rotate" style='font-size: 65px; color: red;'></span>
			</div>

			<div style='display: table-cell; vertical-align: middle; height: 100%;'>
				<div class='pull-right'>
					<div style='display: table-cell; vertical-align: middle; padding-left: 10px; font-size: 24px;'>Secure Checkout</div>
					<div style='display: table-cell; vertical-align: middle; padding-left: 10px; font-size: 24px;'><span class="fa fa-lock fa-2x" aria-hidden="true"></span></div>
				</div>
			</div>

		</div>
	</div>
<?php
}
else if($cart == FALSE) {
?>
  <div id="fluid-header-top-logo" class="row vcenter" style="padding: 10px 0 8px 0;">

	<?php
		if(FLUID_NAVBAR_CART_MENU == TRUE) {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-4\">";
		}
		else {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-4 f-logo-zoom\">";
		}
	?>

      <div class="header-logo" onmouseover="JavaScript:this.style.cursor='pointer';" <?php if(FLUID_SLOGAN_ENABLED == FALSE) { echo " style='padding: 10px;'"; }?>>
		<div class='f-camera-special-right'><img style='height: 60px; vertical-align: bottom;' src='<?php echo $_SESSION['fluid_uri'];?>files/camera_small.png'></div>
		<div style='display: inline-block;' class='f-logo-special-header'>
			<a href="<?php echo $_SESSION['fluid_uri']; ?>" onClick='js_loading_start();'><span class="icon-leos-logo-rotate" style='font-size: 65px; color: red;'></span></a>
			<?php
			// --> Lets see if the slogan is enabled. If so, turn it on.
			if(FLUID_SLOGAN_ENABLED == TRUE) {
			?>
				<div class='header-title-slogan'><?php echo FLUID_SLOGAN;?></div>
			<?php
			}
			?>
		</div>
		<div class='f-camera-special-left'><img style='height: 60px; vertical-align: bottom; transform: scaleX(-1); -webkit-transform: scaleX(-1); -o-transform: scaleX(-1); -moz-transform: scaleX(-1); filter: FlipH; -ms-filter: "FlipH";' src='<?php echo $_SESSION['fluid_uri'];?>files/camera_small.png'></img></div>
      </div>
    </div>

	<?php
		if(FLUID_NAVBAR_CART_MENU == TRUE) {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-5\">";
		}
		else {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-4\" style='padding-left: 0px; padding-right: 0px;'>";
		}
	?>
      <div id="custom-search-input" class="custom-search-input-navbar">
          <div class="input-group col-md-12" style='height: 50px;'>

			<?php
		  	if(FLUID_LIVE_SEARCH_ENABLED == TRUE) {
			?>
				<input id='fluid-search-input' type="text" style='height: 50px; font-size: 18px; padding-left: 15px;' class="form-control input-lg form-control-custom-search-input-navbar" placeholder="Search" onFocus='FluidTime.f_mobile = false;' onClick="FluidTime.f_mobile = false; js_fluid_search_keyup();" onKeyUp="js_fluid_search_suggestions(this.value);" onKeyDown="if(event.keyCode == 13)$('#fluid-search-button').click();"/>
		  	<?php
	  		}
			else {
			?>
				<input id='fluid-search-input' type="text" style='height: 50px; font-size: 18px; padding-left: 15px;' class="form-control input-lg form-control-custom-search-input-navbar" placeholder="Search" onKeyDown="if(event.keyCode == 13)$('#fluid-search-button').click();"/>
			<?php
			}
			?>
			  <span class="input-group-btn fluid-btn">
				  <div id='fluid-search-button' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_search("fluid-search-input");'>
					  <i class="glyphicon glyphicon-search" style='padding-bottom: 5px;'></i>
				  </div>
			  </span>
          </div>
      </div>

    </div>
		<?php
		if(isset($_SESSION['u_id'])) {
			$fluid_account_html = "
			<div style=\"padding: 10px 15px 10px 15px; width: 100%; margin: 0 auto;\">
				<div style=\"text-align:center;\">";

					if(isset($_SESSION['u_picture']))
						$fluid_account_html .= "<img src='" . $_SESSION['u_picture'] . "' style='width:" . $_SESSION['u_picture_width'] . "px; height:" . $_SESSION['u_picture_height'] . "px;'>";
					else
						$fluid_account_html .= "<i class=\"fa fa-user\" style=\"font-size: 80px;\"></i>";

				$fluid_account_html .= "</div>
				<div style=\"margin-top: 10px; text-align:center;\"><a class=\"btn btn-danger\" onClick='js_fluid_logout();'><span class=\"glyphicon glyphicon-log-out\" aria-hidden=\"true\"></span> Sign out</a></div>
			</div>";

			/*
			$fluid_account_html .= "<div class=\"text-center\" style='width: 100%; padding: 15px 10px 15px 10px; border-top: 1px solid black;'>
				<a name=\"fluid_toggle_close\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_redirect_url({url:\"" . base64_encode($_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/address') . "\"});'><div style=\"padding: 5px; display: inline-block;\"><span class=\"fa fa-id-badge fa-2x\" aria-hidden=\"true\"></span><div>My Account</div></div></a>
				<div style=\"padding: 5px; display: inline-block;\"><a name=\"fluid_toggle_close\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_redirect_url({url:\"" . base64_encode($_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/orders') . "\"});'><span class=\"fa fa-shopping-bag fa-2x\" aria-hidden=\"true\"></span><div>My Orders</div></a></div>
			</div>";
			*/
			$fluid_account_html .= "<div class=\"text-center\" style='width: 100%; padding: 15px 10px 15px 10px; border-top: 1px solid black;'>
				<a name=\"fluid_toggle_close\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/address' . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_loading_start();'><div style=\"padding: 5px; display: inline-block;\"><span class=\"fa fa-id-badge fa-2x\" aria-hidden=\"true\"></span><div>My Account</div></div></a>
				<div style=\"padding: 5px; display: inline-block;\"><a name=\"fluid_toggle_close\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ACCOUNT_REWRITE . '/orders' . "\" onClick='js_loading_start();'><span class=\"fa fa-shopping-bag fa-2x\" aria-hidden=\"true\"></span><div>My Orders</div></a></div>
			</div>";
		}
		else {
			// FLUID_ACCOUNT
			$fluid_facebook_login_init = php_facebook_button();
			$fluid_google_login_init = php_google_button();

			// Load the javascript code to handle facebook and google logins. --> FLUID_ACCOUNT
			echo $fluid_facebook_login_init["script"];
			echo $fluid_google_login_init["script"];

			$fluid_account_html = "
			<div style=\"padding: 10px 15px 10px 15px;\">
					<div>Login via</div>
					<div style=\"padding-top: 10px;\">";
						$fluid_account_html .= $fluid_facebook_login_init["html"]; // --> FLUID_ACCOUNT
						$fluid_account_html .= $fluid_google_login_init["html"]; // --> FLUID_ACCOUNT

			$fluid_account_html .= "
			</div>
				<div style=\"padding-top: 10px;\">or</div>
					<form id=\"fluid_form_login\" data-toggle=\"validator\" role=\"form\" onsubmit=\"js_fluid_login();\" accept-charset=\"UTF-8\">
						<div class=\"form-group has-feedback\">
							 <label class=\"sr-only\">Email address</label>
							 <input type=\"email\" class=\"form-control\" id=\"fluid_email_login\" placeholder=\"Email address\" maxlength=\"50\" data-error=\"Invalid email address.\" required>
							 <span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
							 <div class=\"help-block with-errors\"></div>
						</div>

						<div class=\"form-group has-feedback\">
							 <label class=\"sr-only\">Password</label>
							 <input type=\"password\" class=\"form-control\" id=\"fluid_password_login\" placeholder=\"Password\" maxlength=\"100\" placeholder=\"Password\" data-minlength=\"6\" required>
							 <div class=\"help-block text-right\"><a href=\"#\" name=\"fluid_toggle_close\" data-toggle=\"modal\" data-target=\"#fluid-forgot-modal\" onClick='js_modal_hide(\"#fluid-login-modal\"); js_modal_hide(\"#fluid-checkout-guest-modal\"); js_close_toggle_menus(); js_fluid_forgot_password_clear();'>Forgot your password ?</a></div>
						</div>
						<div class=\"form-group\">
							 <button type=\"submit\" class=\"btn btn-primary btn-block\"><span class=\"glyphicon glyphicon-log-in\" aria-hidden=\"true\"></span> Log in</button>
						</div>
						<div class=\"checkbox\">
							<label><input type=\"checkbox\" id=\"fluid_remember_me\"><span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span></label></input><div style='display: inline-block; margin-bottom: 0px;'> keep me logged-in</div>
						</div>
						<input type='hidden' id='fluid-checkout-login' value='0'></input>
					</form>
			</div>

			<div id='fluid-div-signup' class=\"text-center\" style='width: 100%; padding: 15px 10px 15px 10px; border-top: 1px solid black;'>
				New here ? <a href=\"#\" name=\"fluid_toggle_close\" style='font-weight: 400;' onClick='document.getElementById(\"fluid-modal-register-div-body\").innerHTML = Base64.decode(FluidMenu.account[\"register_html\"]); js_fluid_register_clear(); js_modal_show(\"#fluid-register-modal\");'>Create Account</a>
			</div>";
		}

		if(empty($_SESSION['u_id'])) {
			$f_register_button = base64_encode("<button type=\"button\" class=\"btn btn-info\" onClick='$(\"#fluid_button_register\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> <div style='display: inline-block;'>Create Account</div></button>");

			$f_signup_modal_html = "New here ? <a href=\"#\" name=\"fluid_toggle_close\" style='font-weight: 400;' onClick='js_close_toggle_menus(); document.getElementById(\"modal-login-div\"]).innerHTML = \"\"; document.getElementById(\"fluid-modal-register-div-body\").innerHTML = Base64.decode(FluidMenu.account[\"register_html\"]); js_fluid_register_clear(); js_modal_show(\"#fluid-register-modal\");'>Create Account</a>";

			$f_guest_back_button = "<button type=\"button\" class=\"btn btn-warning\" onClick='document.getElementById(\"fluid-account-dropdown\").innerHTML = \"\"; document.getElementById(\"fluid-account-dropdown-nav\").innerHTML = \"\"; document.getElementById(\"modal-checkout-fluid-div\").innerHTML = Base64.decode(FluidMenu.account[\"html\"]); document.getElementById(\"fluid-div-signup\").innerHTML = Base64.decode(FluidMenu.account[\"signup_checkout_plus_mobile_html\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"none\"; fluid_facebook_checkout = 1; fluid_google_checkout = 1; document.getElementById(\"fluid-checkout-login\").value = \"1\"; document.getElementById(\"fluid-checkout-guest-back-button\").innerHTML = Base64.decode(FluidMenu.account[\"f_mobile_back_button_signup\"]); document.getElementById(\"fluid-continue-as-guest\").style.display=\"block\"; document.getElementById(\"fluid-guest-container\").style.display=\"none\"; document.getElementById(\"fluid-guest-container\").innerHTML = \"\"; js_fluid_login();'><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Back</button>";

			$f_mobile_back_button_signup = "<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button>";

			$f_signup_checkout_plus_mobile_html = "New here ? <a href=\"#\" name=\"fluid_toggle_close\" style='font-weight: 400;' onClick='js_close_toggle_menus(); document.getElementById(\"fluid-modal-register-div-body\").innerHTML = \"\"; document.getElementById(\"modal-checkout-fluid-div\").innerHTML = Base64.decode(FluidMenu.account[\"register_html\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"block\"; document.getElementById(\"fluid-checkout-guest-back-button\").innerHTML = Base64.decode(\"" . base64_encode($f_guest_back_button) . "\"); document.getElementById(\"fluid-continue-as-guest\").style.display=\"none\"; document.getElementById(\"fluid-guest-container\").style.display=\"block\"; document.getElementById(\"fluid-guest-container\").innerHTML = Base64.decode(\"" . $f_register_button . "\"); js_fluid_register_clear();'>Create Account</a>";

			$f_mobile_back_button = "<button type=\"button\" class=\"btn btn-warning\" onClick='js_close_toggle_menus(); document.getElementById(\"fluid-account-dropdown\").innerHTML = \"\"; document.getElementById(\"modal-login-div-header\").style.display = \"none\"; document.getElementById(\"fluid-account-dropdown-nav\").innerHTML = \"\"; document.getElementById(\"modal-login-div\").innerHTML = Base64.decode(FluidMenu.account[\"html\"]); js_fluid_login(); document.getElementById(\"fluid-div-signup\").innerHTML = Base64.decode(FluidMenu.account[\"signup_mobile_html\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"none\"; document.getElementById(\"fluid-login-back-button\").style.display = \"none\"; document.getElementById(\"f-login-pull-right-div\").style.display=\"block\"; document.getElementById(\"fluid-modal-trigger-button-login\").style.display=\"none\"; document.getElementById(\"fluid-modal-trigger-button-login\").innerHTML = \"\"; fluid_facebook_checkout = 0; fluid_google_checkout = 0;'><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Back</button>";

			$f_signup_mobile_html = "New here ? <a href=\"#\" name=\"fluid_toggle_close\" style='font-weight: 400;' onClick='js_close_toggle_menus(); document.getElementById(\"fluid-modal-register-div-body\").innerHTML = \"\"; document.getElementById(\"modal-login-div-header\").style.display = \"block\"; document.getElementById(\"fluid-login-back-button\").innerHTML= Base64.decode(FluidMenu.account[\"f_mobile_back_button\"]); document.getElementById(\"fluid-login-back-button\").style.display = \"block\"; document.getElementById(\"modal-login-div\").innerHTML = Base64.decode(FluidMenu.account[\"register_html\"]); document.getElementById(\"modal-checkout-div-header\").style.display = \"block\"; document.getElementById(\"f-login-pull-right-div\").style.display=\"none\"; document.getElementById(\"fluid-modal-trigger-button-login\").style.display=\"block\"; document.getElementById(\"fluid-modal-trigger-button-login\").innerHTML = Base64.decode(\"" . $f_register_button . "\"); js_fluid_register_clear();'>Create Account</a>";
		}
		?>

	<script>
		FluidMenu.account['html'] = "<?php echo base64_encode($fluid_account_html); ?>";
		FluidMenu.account['register_html'] = "<?php echo base64_encode(HTML_REGISTER); ?>";
		<?php
		if(empty($_SESSION['u_id'])) {
		?>
			FluidMenu.account['signup_modal_html'] = "<?php echo base64_encode($f_signup_modal_html); ?>";
			FluidMenu.account['signup_checkout_plus_mobile_html'] = "<?php echo base64_encode($f_signup_checkout_plus_mobile_html); ?>";
			FluidMenu.account['f_mobile_back_button_signup'] = "<?php echo base64_encode($f_mobile_back_button_signup); ?>";
			FluidMenu.account['f_mobile_back_button'] = "<?php echo base64_encode($f_mobile_back_button); ?>";
			FluidMenu.account['signup_mobile_html'] = "<?php echo base64_encode($f_signup_mobile_html); ?>";
		<?php
		}
		?>
		<?php //FluidMenu.account['div'] = "modal-login-div";?> <?php // --> The inner div of the modal or dropdown which gets replaced depending on resolution and device. Used when doing signup. ?>
	</script>

	<?php
		if(FLUID_NAVBAR_CART_MENU == TRUE) {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-3\">";
			$f_class = "fluid-header-top-buttons";
		}
		else {
			echo "<div class=\"col-lg-4 col-md-4 col-sm-4\">";
			$f_class = "fluid-header-top-buttons-display";
		}
	?>
      <div id='fluid-header-top-buttons-div' class="row header-login-cart-container <?php echo $f_class; ?>">
        <div class="header-block-login-cart">
          <div id="fluid-account-parent" class="header-login-container dropdown fluid-dropdown-parent">
			<a class="dropdown-toggle header-menu-toggle" style='text-decoration: none;' href="#" data-toggle="dropdown" onClick="<?php if(empty($_SESSION['u_id'])) { ?>document.getElementById('modal-login-div').innerHTML = ''; document.getElementById('modal-checkout-fluid-div').innerHTML = ''; <?php } ?> document.getElementById('fluid-account-dropdown-nav').innerHTML = ''; document.getElementById('fluid-account-dropdown').innerHTML = Base64.decode(FluidMenu.account['html']); <?php if(empty($_SESSION['u_id'])) echo "js_fluid_login(); fluid_facebook_checkout = 0; fluid_google_checkout = 0;"; ?>">
				<?php
				$style_header_avatar = NULL;
				if(isset($_SESSION['u_id']) && isset($_SESSION['u_picture']))
					echo "<div class='header-login-logo'><div class='vcenter'><img src='" . $_SESSION['u_picture'] . " width='50px' height='50px'></img></div></div>";
				else if(isset($_SESSION['u_id']))
					echo "<div class='header-login-no-logo fa fa-user' style='font-size: 40px; color: #333333;'></div>";
				else
					echo "<div class='header-login-logo fa fa-camera-retro' style='font-size: 40px;'></div>";
				?>

				<div class="header-login">
				<?php
				if(isset($_SESSION['u_id']))
					echo "<span class='header-login-line-1' id='fluid_header_hello'><div class='f-hello'>Hello</div><div style='display: inline-block;'>" . utf8_decode(substr($_SESSION['u_first_name'], 0, 12)) . "</div></span>";
				else
					echo "<span class='header-login-line-1' id='fluid_header_hello'>Log In</span>";
				?>

				  <span class="header-login-line-2">My Account</span>
				</div>
				<span class="glyphicon glyphicon-triangle-bottom header-login-arrow" aria-hidden="true"></span>
			</a>

            <div id="fluid-account-dropdown" class="dropdown-menu fluid-dropdown-menu-arrow dropdown-menu-right fluid-stay-open fluid-account-box">

            </div><?php // <!-- /.dropdown-menu --> ?>
          </div><?php // <!-- /.header-login-container --> ?>
        </div><?php // <!-- /.header-align-block --> ?>

        <div id="fluid-cart-premier" class="header-block-login-cart">
          <div id="fluid-cart-parent" class="dropdown fluid-dropdown-parent">
			<?php //<a id="fluid-cart-dom" class="dropdown-toggle header-cart-toggle" style='text-decoration: none;' href="#" data-toggle="dropdown" onClick="js_fluid_cart_dropdown('fluid-cart-parent', '0');">?>
			<a id="fluid-cart-dom" class="dropdown-toggle header-cart-toggle-top" style='text-decoration: none;' href="#" data-toggle="dropdown" onClick="$('.header-cart-toggle-mobile').parent().removeClass('open'); $('.header-cart-toggle-nav').parent().removeClass('open');  document.getElementById('fluid-cart-dropdown-nav').innerHTML = ''; document.getElementById('fluid-cart-dropdown-mobile').innerHTML = ''; document.getElementById('fluid-cart-dropdown').innerHTML = Base64.decode(FluidMenu.cart['html']); document.getElementById('fluid-cart-badge').innerHTML = Base64.decode(FluidMenu.cart['num_items']); js_fluid_block_animate(null);">
				<div id="header-cart-desktop" class="header-cart-icon"><div id="header-cart-icon-menu" class="fa fa-3x fa-shopping-cart" style=""></div><span class="badge" style="position: relative; margin-left: -20px; margin-top: -63px;" id="fluid-cart-badge" ><?php echo $fluid_cart_num_items; ?></span></div>
				<div class="header-cart">
				  <span class="header-cart-line-1"></span>
				  <span class="header-cart-line-2">My Cart</span>
				</div><!-- /.header-cart -->
				<span class="glyphicon glyphicon-triangle-bottom header-login-arrow" aria-hidden="true"></span>
            </a>
            <div id="fluid-cart-dropdown" class="dropdown-menu fluid-dropdown-menu-arrow dropdown-menu-right fluid-stay-open" style="z-index: 1150; padding:17px;">
            <?php
				//echo $fluid_cart_html;
            ?>
            </div><?php // <!-- /.dropdown-menu --> ?>

          </div><?php // <!-- /.header-cart-container --> ?>
        </div><?php //<!-- /.header-align-block --> ?>

      </div>
    </div>
  </div>

  <div class='col-lg-2 col-md-1 col-xs-0'></div>
  <div class='col-lg-8 col-md-10 col-xs-12' style='margin-top: -15px;'>
  	<div id='f-live-search-control' style='max-width: 900px; margin: auto;'>
	  <div id='f-search-dropdown' class='dropdown f-search-drop-trigger'>
	  	<button id='f-search-suggestions-trigger' name='fluid_toggle_close' href="#" class="dropdown-toggle" data-trigger=".f-search-drop-trigger" data-toggle="dropdown" style='display: none;'></button>
		<div id="fluid-search-suggestions-dropdown" class="dropdown-menu fluid-dropdown-menu-arrow-centre dropdown-menu-right fluid-search-suggestions-box" style='width: 100%; border-color: darkgrey;'>
			<div id='f-live-search'></div>
		</div>
	  </div>
	 </div>
  </div>
  <div class='col-lg-2 col-md-1 col-xs-0'></div>

  <?php
  // This bootstrap hide.bs code needs to be below the f-search-dropdown, otherwise it does not fire.
  ?>
  <script>
  $('#f-search-dropdown').on({
	  "hide.bs.dropdown":  function(event) {
		  js_fluid_search_overlay_closed();
	  },
	  "hidden.bs.dropdown": function (event) {
		  js_fluid_search_overlay_closed();
	  }
  });
  </script>

<?php
}
?>


</div><?php // <!-- /.container-fluid --> ?>

<?php
// --> Mobile < 768px
if($cart == TRUE) {
?>
	<nav class="navbar navbar-fluid navbar-inverse navbar-inverse-fluid fluid-cart-navbar" role="navigation" style="z-index: 1100;">
	  <div class="container nav-menu-size">
		<?php //<!-- Brand and toggle get grouped for better mobile display --> ?>
		<div class="navbar-header">
			<?php
			// These buttons are for mobile only.
			?>
				<div class="pull-left fluid-mobile-nav-button" style="display: table; color: white; border: 0px solid red; min-height: 55px;" onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_keep_shopping();'>

					<div style="display: table-cell; vertical-align: middle; padding-left: 5px; min-height: 100%;">
						<div style="display: table-cell; vertical-align: middle;">
							<span style="font-size: 24px; padding-bottom: 5px;" class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
						</div>

						<div style="display: table-cell; vertical-align: middle; padding-left: 5px;">
							<span class="icon-leos-logo" style=" font-size: 36px; color: red;"></span>
						</div>

						<?php
						/*
						<div style="display: table-cell; vertical-align: middle; padding-left: 3px; font-size: 24px;">
							Checkout <span class="fa fa-lock" aria-hidden="true"></span>
						</div>
						*/
						?>
					</div>

						<div style='width: 100%; display: table-cell; vertical-align: middle; padding-right: 5px;'>
							<div class='pull-right'>
								<div style='display: table-cell; vertical-align: middle; padding-left: 10px; font-size: 20px;'>Secure Checkout</div>
								<div style='display: table-cell; vertical-align: middle; padding-left: 10px; font-size: 16px;'><span class="fa fa-lock fa-2x" aria-hidden="true"></span></div>
							</div>
						</div>
				</div>

			<?php
			/*
			<div id="fluid-cart-parent-button" class="dropdown fluid-dropdown-parent">
				<button type="button" class="navbar-toggle collapsed navbar-menu-button header-cart-toggle-mobile fluid-mobile-nav-button" data-toggle="dropdown" aria-expanded="false" onClick="document.getElementById('fluid-cart-dropdown').innerHTML = ''; document.getElementById('fluid-cart-dropdown-mobile').innerHTML = Base64.decode(FluidMenu.cart['html']);">
				<span id="fluid-cart-mobile-span" style='font-size: 24px;' class="glyphicon glyphicon-shopping-cart glyphicon-inverse" aria-hidden="true"></span>
				</button>

				<div id="fluid-cart-dropdown-mobile" class="dropdown-menu dropdown-menu-right fluid-stay-open fluid-cart-dropdown-mobile" style="z-index: 1150; margin-top: 55px; padding:17px;">
				<?php
					//echo $fluid_cart_html;
				?>
				</div><!-- /.dropdown-menu -->
			</div>
			*/
			?>
		</div>
	  </div><?php // <!-- /.container-fluid --> ?>
	</nav>

	<div class="fluid-cart-navbar-large"></div>

<?php
}
else if($cart == FALSE) {
?>
<?php
/*
	<div id="back-top-div" class='back-top-div'>
		<p id="back-top" class='back-top'>
			<a onmouseover="JavaScript:this.style.cursor='pointer';" style='margin: auto;'><span></span></a>
		</p>
	</div>

	<div class='back-top-div'>
		<p id="fluid-auto-scroll-down" class='fluid-auto-scroll-down'>
			<a onmouseover="JavaScript:this.style.cursor='pointer';" style='float: right; margin-right: 10px;'><span id="fluid-up" class='fluid-auto-scroll-start'></span><span id="fluid-stop" class='fluid-auto-scroll-stop'></span></a>
		</p>
	</div>
*/
?>

	<div id="back-top-div" class='back-top-div'>
		<p id="back-top" class='back-top'>
			<a onmouseover="JavaScript:this.style.cursor='pointer';" style='float: right; margin-right: 10px;'><span></span></a>
		</p>
	</div>

	<nav id="fluid-navigation-bar" class="navbar navbar-fluid navbar-inverse-fluid" role="navigation" style='z-index: 1100; width: 100%; height: 57px; margin: 0px; padding: 0px;'>
<div class="navbar-perspective" style='padding: 0px; margin: 0px; width: 100%;'>

<div id='fluid-navbar-primary-div' class="navbar-primary navbar-inverse-fluid" style='width: 100%; padding: 0px; margin: 0px;'>

		<?php
		if(FLUID_NAVBAR_CART_MENU == TRUE) {
			$nav_menu_size = "nav-menu-size";
		}
		else {
			$nav_menu_size = "nav-menu-size-padding";
		}
		?>

	  <div class="container <?php echo $nav_menu_size; ?> f-class-header-container" style='text-align: center;'>
		<?php //<!-- Brand and toggle get grouped for better mobile display --> ?>
		<div class="navbar-header">
			<?php
			// These buttons are for mobile only.
			?>

			<?php // Start of cart button ?>
			<button id='fluid-cart-mobile-button' type="button" class="navbar-toggle collapsed navbar-menu-button header-cart-toggle-mobile fluid-mobile-nav-button" onClick="$('.header-cart-toggle-top').parent().removeClass('open'); $('.header-cart-toggle-nav').parent().removeClass('open'); document.getElementById('fluid-cart-dropdown-nav').innerHTML = ''; document.getElementById('fluid-cart-dropdown').innerHTML = ''; document.getElementById('fluid-cart-dropdown-mobile').innerHTML = Base64.decode(FluidMenu.cart['html']); js_fluid_block_animate(null); js_fluid_nav_toggle_actives();">
			<div id="fluid-cart-mobile-div"><span id="fluid-cart-mobile-span" class="glyphicon glyphicon-shopping-cart glyphicon-inverse" aria-hidden="true"></span><span class="badge" style="position: relative; margin-left: -15px; margin-top: -33px;" id="fluid-cart-badge-mobile" ><?php echo $fluid_cart_num_items; ?></span></div>
			</button>

			<div id="fluid-cart-parent-button" class="dropdown fluid-dropdown-parent">
				<a id='fluid-cart-mobile-dropdown-a' href="#" data-toggle="dropdown" aria-expanded="false" style='display: none !important;'>Dropdown</a>

				<div id="fluid-cart-dropdown-mobile" class="dropdown-menu fluid-dropdown-menu-arrow-mobile dropdown-menu-right fluid-stay-open fluid-cart-dropdown-mobile" style="z-index: 1150; margin-top: 55px; padding:17px;">
				<?php
					//echo $fluid_cart_html;
				?>
				</div><?php // <!-- /.dropdown-menu -->?>
			</div>
			<?php // End of cart button ?>

			<div id="fluid-account-parent-button" class="fluid-dropdown-parent">
				<button type="button" class="navbar-toggle navbar-menu-button dropdown fluid-mobile-nav-button" data-toggle="modal" data-target="#fluid-login-modal" data-backdrop="static" data-keyboard="false" aria-expanded="false" onClick="js_close_toggle_menus(); <?php if(empty($_SESSION['u_id'])) { ?> document.getElementById('modal-login-div-header').style.display = 'none';  document.getElementById('modal-checkout-fluid-div').innerHTML = ''; <?php } ?> document.getElementById('fluid-account-dropdown').innerHTML = ''; document.getElementById('fluid-account-dropdown-nav').innerHTML = ''; document.getElementById('modal-login-div').innerHTML = Base64.decode(FluidMenu.account['html']); <?php if(empty($_SESSION['u_id'])) echo " js_fluid_login(); document.getElementById('fluid-div-signup').innerHTML = Base64.decode(FluidMenu.account['signup_mobile_html']); document.getElementById('modal-checkout-div-header').style.display = 'none'; document.getElementById('fluid-login-back-button').style.display = 'none'; fluid_facebook_checkout = 0; fluid_google_checkout = 0;"; ?>">
				<span class="glyphicon glyphicon-user glyphicon-inverse" aria-hidden="true"></span>
				</button>
			</div>

			<button type="button" class="navbar-toggle collapsed navbar-menu-button fluid-mobile-nav-button" data-toggle="collapse"  aria-expanded="false" onclick="js_fluid_navbar_search_flip_search(); js_close_categories(); js_fluid_nav_toggle_close();">
			  <span class="glyphicon glyphicon-search glyphicon-inverse" aria-hidden="true"></span>
			</button>

			<button type="button" id='fluid-mobile-nav-menu-button' class="navbar-toggle collapsed navbar-menu-button pull-left fluid-mobile-nav-button" aria-expanded="false" onClick="$('.header-cart-toggle-mobile').parent().removeClass('open'); js_open_categories();">
			 <?php // <!--<span class="sr-only">Toggle navigation</span>
				// <span class="icon-bar"></span>--> ?>
			  <span class="glyphicon glyphicon-align-justify glyphicon-inverse" aria-hidden="true"></span>
			</button>
		</div>

<script>
	function js_fluid_nav_toggle_actives() {
		var parent = $('#fluid-cart-parent-button');
		var child = $('#fluid-cart-mobile-dropdown-a');
		var f_button = document.getElementById('fluid-cart-mobile-button');

		<?php // Closes the dropdown toggle ?>
		if(parent.hasClass('open')) {
			document.getElementById('fluid-cart-parent-button').className = document.getElementById('fluid-cart-parent-button').className.replace( /(?:^|\s)open(?!\S)/g , '' );

			f_button.className = f_button.className.replace( /(?:^|\s)fluid-mobile-nav-button-open(?!\S)/g , '' );
		}
		else  {
			<?php // opens the toggle dropdown ?>
			js_close_categories();

			f_button.className += " fluid-mobile-nav-button-open";
			child.dropdown('toggle');
		}
	}

	function js_fluid_nav_toggle_close() {
		<?php // Closes mobile navbar dropdowns ?>
		var f_button = document.getElementById('fluid-cart-mobile-button');
		f_button.className = f_button.className.replace( /(?:^|\s)fluid-mobile-nav-button-open(?!\S)/g , '' );

		document.getElementById('fluid-cart-parent-button').className = document.getElementById('fluid-cart-parent-button').className.replace( /(?:^|\s)open(?!\S)/g , '' );
	}

	function js_fluid_navbar_search_flip_search() {
		document.getElementById('fluid-search-input-navbar').value = "";

		var f_navbar = document.getElementById('fluid-navigation-bar');
		f_navbar.className = f_navbar.className.replace( /(?:^|\s)navbar-rotate-primary(?!\S)/g , '' );
		f_navbar.className = f_navbar.className.replace( /(?:^|\s)navbar-rotate-tertiary(?!\S)/g , '' );

		f_navbar.className += " navbar-rotate-tertiary";

		$('#fluid-search-input-navbar').focus();
	}

	function js_fluid_navbar_search_flip_primary() {
		js_fluid_process_search_suggestions_force_close();
		document.getElementById('fluid-search-input-navbar').value = "";

		var f_navbar = document.getElementById('fluid-navigation-bar');
		f_navbar.className = f_navbar.className.replace( /(?:^|\s)navbar-rotate-primary(?!\S)/g , '' );
		f_navbar.className = f_navbar.className.replace( /(?:^|\s)navbar-rotate-tertiary(?!\S)/g , '' );
		f_navbar.className += " navbar-rotate-primary";

		$('#fluid-search-input-navbar').blur();
	}

	function js_open_categories() {
		var fluid_collapse = document.getElementsByName("fluid-collapse-div");
		var f_button = document.getElementById('fluid-mobile-nav-menu-button');

		if($("#categories").is(":visible") == false) {
			js_close_toggle_menus();

			$("#categories").collapse('show');
			f_button.className += " fluid-mobile-nav-button-open";

			for(var x=0; x < fluid_collapse.length; x++) {
				fluid_collapse[x].className = fluid_collapse[x].className.replace( /(?:^|\s)fluid-collapse-auto(?!\S)/g , '' )
				fluid_collapse[x].className += " fluid-collapse-show";
			}
		}
		else {
			$("#categories").collapse('hide');
			f_button.className = f_button.className.replace( /(?:^|\s)fluid-mobile-nav-button-open(?!\S)/g , '' );

			for(var x=0; x < fluid_collapse.length; x++) {
				fluid_collapse[x].className = fluid_collapse[x].className.replace( /(?:^|\s)fluid-collapse-show(?!\S)/g , '' )
				fluid_collapse[x].className += " fluid-collapse-auto";
			}

		}
	}

	function js_close_categories() {
		var fluid_collapse = document.getElementsByName("fluid-collapse-div");
		var f_button = document.getElementById('fluid-mobile-nav-menu-button');

		if($("#categories").is(":visible") == true) {
			$("#categories").collapse('hide');
			f_button.className = f_button.className.replace( /(?:^|\s)fluid-mobile-nav-button-open(?!\S)/g , '' );

			for(var x=0; x < fluid_collapse.length; x++) {
				fluid_collapse[x].className = fluid_collapse[x].className.replace( /(?:^|\s)fluid-collapse-show(?!\S)/g , '' )
				fluid_collapse[x].className += " fluid-collapse-auto";
			}
		}
	}

	function js_fluid_single_order_lookup() {
		try {
			$('#fluid_form_order_check').validator('update');

			if($('#fluid_form_order_check').validator('validate').has('.has-error').length) {
				<?php // --> Some errors, missing data. Do nothing. ?>
			}
			else {
				var data_tmp = {};
					data_tmp.f_email = document.getElementById('fluid-order-email').value;
					data_tmp.f_order_id = document.getElementById('fluid-order-number').value;

				var data = Base64.encode(JSON.stringify(data_tmp));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&checkout=true&fluid_function=php_html_view_single_order_lookup&data=" + data}));

				js_fluid_ajax(data_obj);
			}
		}
		catch(err) {
			js_debug_error(err);
		}
	}
</script>

<div name='fluid-collapse-div' class='row fluid-collapse-auto fluid-text-align-left'>

	<?php
	if(FLUID_NAVBAR_CART_MENU == TRUE) {
		echo "<div class='col-md-2 col-sm-2'></div>";
		echo "<div class='col-md-8 col-sm-8 fluid-collapse-auto' name='fluid-collapse-div' style='padding: 0px;'>";
	}
	else {
		echo "<div class='col-md-0 col-sm-0'></div>";
		echo "<div class='col-md-12 col-sm-12 fluid-collapse-auto' name='fluid-collapse-div' style='padding: 0px;'>";
	}
	?>
		<div name='fluid-collapse-div' class='fluid-collapse-div-width' style='max-height: 90vh; margin: 0px; padding: 0px; display: inline-block; overflow-y: auto;'>
		<ul style='min-height: 57px; padding: 0px; width: 100%;' class="fluid-nav-dropdown fluid-nav-box-shadow collapse navbar-collapse nav navbar-nav fluid-navbar-nav fluid-mobile-navbar fluid-background-nav" id="categories" onClick="$('.header-cart-toggle-top').parent().removeClass('open'); $('.header-menu-toggle').parent().removeClass('open'); $('.header-cart-toggle-nav').parent().removeClass('open'); $('.header-cart-toggle-mobile').parent().removeClass('open');">
			<?php
				$fluid_header->php_db_begin();

				// Load the categories.
				$fluid_header->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " WHERE c_enable = 1 ORDER BY c_sortorder ASC");
				$category_data_raw = NULL;
				if(isset($fluid_header->db_array)) {
					if(count($fluid_header->db_array) > 0) {
						foreach($fluid_header->db_array as $key => $value) {
							if($value['c_parent_id'] == NULL)
								$category_data_raw[$value['c_id']]['parent'] = $value;
							else
								$category_data_raw[$value['c_parent_id']]['childs'][] = $value;
						}
					}
				}

				$i = 0;
				$output_cat = NULL;

				$category_data = NULL;
				$category_more_768 = NULL;
				$category_more_992 = NULL;
				$category_more_1200 = NULL;
				$category_more_1600 = NULL;

				// Resort the categories into the proper order.
				foreach($category_data_raw as $parent) {
					if(isset($parent['parent'])) {
						if(isset($category_data[$parent['parent']['c_sortorder']]))
							$category_data[] = $parent; // Make a new key if this already exists.
						else
							$category_data[$parent['parent']['c_sortorder']] = $parent;
					}
				}

				ksort($category_data);
				$i = 0;
				$f_class_hide = NULL;

				if(FLUID_NAVBAR_CART_MENU == TRUE) {
					$f_class_navbar_a = "fluid-category-navbar";
				}
				else {
					$f_class_navbar_a = "fluid-category-navbar-a";
				}

				if(isset($category_data)) {
					foreach($category_data as $parent) {
						/*
						if(FLUID_NAVBAR_CART_MENU == TRUE) {
							if($i >= FLUID_MENU_SIZE_1600) {
								$f_class_hide = " f-cat-hide-header-1600";
							}
							else if($i >= FLUID_MENU_SIZE_1200) {
								$f_class_hide = " f-cat-hide-header-1200";
							}
							else if($i >= FLUID_MENU_SIZE_992) {
								$f_class_hide = " f-cat-hide-header-992";
							}
							else if($i >= FLUID_MENU_SIZE_768) {
								$f_class_hide = " f-cat-hide-header-768";
							}
							else {
								$f_class_hide = NULL;
							}
						}
						else {
							if($i >= FLUID_MENU_SIZE_ALT_1600) {
								$f_class_hide = " f-cat-hide-header-1600";
							}
							else if($i >= FLUID_MENU_SIZE_ALT_1200) {
								$f_class_hide = " f-cat-hide-header-1200";
							}
							else if($i >= FLUID_MENU_SIZE_ALT_992) {
								$f_class_hide = " f-cat-hide-header-992";
							}
							else if($i >= FLUID_MENU_SIZE_ALT_768) {
								$f_class_hide = " f-cat-hide-header-768";
							}
							else {
								$f_class_hide = NULL;
							}

						}
						*/
						if($i >= FLUID_MENU_SIZE_ALT_1600) {
							$f_class_hide = " f-cat-hide-header-1600";
						}
						else if($i >= FLUID_MENU_SIZE_ALT_1200) {
							$f_class_hide = " f-cat-hide-header-1200";
						}
						else if($i >= FLUID_MENU_SIZE_ALT_992) {
							$f_class_hide = " f-cat-hide-header-992";
						}
						else if($i >= FLUID_MENU_SIZE_ALT_768) {
							$f_class_hide = " f-cat-hide-header-768";
						}
						else {
							$f_class_hide = NULL;
						}

						$output_cat_temp = "<li class='dropdown header-dropdown nav-pinned-fluid-text" . $f_class_hide . "'>";
							$output_cat_temp .= "<a href='#' class='dropdown-toggle " . $f_class_navbar_a . "' data-toggle='dropdown'><div style='margin-top: 3px;'>" . $parent['parent']['c_name'] . " <span class='caret'></span></div></a>";

							$output_cat_temp .= "<div class='dropdown-menu header-dropdown-menu fluid-dropdown-overflow'>";
								$output_cat_temp .= "<ul class='nav-list list-inline fluid-nav-list'>";

								$c_images = $fluid_header->php_process_images($parent['parent']['c_image']);
								$c_img = $fluid_header->php_process_image_resize($c_images[0], "80", "80");

								// Navbar links with images.
								//$output_cat_temp .= "<li class='header-li'><a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $parent['parent']['c_id'] . "/" . $fluid_header->php_clean_string($parent['parent']['c_name']) . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_loading_start();'><img class='img-responsive header-li-image' src='" . $_SESSION['fluid_uri'] . $c_img['image'] . "' style='width: " . $c_img['width'] . "px; height: " . $c_img['height'] . "px;'><div class='header-li-category-name'>" . $parent['parent']['c_name'] . "</div></a></li>";

								// Navbar links without images.
								$output_cat_temp .= "<li class='header-li'><a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $parent['parent']['c_id'] . "/" . $fluid_header->php_clean_string($parent['parent']['c_name']) . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_loading_start();'><div class='header-li-category-name'><span class=\"f-circle\"></span> " . $parent['parent']['c_name'] . " (All)</div></a></li>";

								if(isset($parent['childs'])) {
									foreach($parent['childs'] as $value) {
										$c_images = $fluid_header->php_process_images($value['c_image']);
										$c_img = $fluid_header->php_process_image_resize($c_images[0], "80", "80");

										// Navbar links with images.
										//$output_cat_temp .= "<li class='header-li'><a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $value['c_id'] . "/" . $fluid_header->php_clean_string($value['c_name']) . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_loading_start();'><img class='img-responsive header-li-image' src='" . $_SESSION['fluid_uri'] . $c_img['image'] . "' style='width: " . $c_img['width'] . "px; height: " . $c_img['height'] . "px;'><div class='header-li-category-name'>" . $value['c_name'] . "</div></a></li>";

										// Navbar links without images.
										$output_cat_temp_inner = "<li class='header-li'><a href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $value['c_id'] . "/" . $fluid_header->php_clean_string($value['c_name']) . "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_loading_start();'><div class='header-li-category-name'><span class=\"f-circle\"></span> " . $value['c_name'] . "</div></a></li>";
										$output_cat_temp .= $output_cat_temp_inner;

										if(FLUID_NAVBAR_CART_MENU == TRUE) {
											if($i >= FLUID_MENU_SIZE_1600)
												$category_more_1600 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_1200)
												$category_more_1200 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_992)
												$category_more_992 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_768)
												$category_more_768 .= $output_cat_temp_inner;
										}
										else {
											if($i >= FLUID_MENU_SIZE_ALT_1600)
												$category_more_1600 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_ALT_1200)
												$category_more_1200 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_ALT_992)
												$category_more_992 .= $output_cat_temp_inner;
											else if($i >= FLUID_MENU_SIZE_ALT_768)
												$category_more_768 .= $output_cat_temp_inner;
										}
									}
								}// childs

								$output_cat_temp .= "</ul>"; // nav-list
							$output_cat_temp .= "</div>"; // dropdown-menu
						$output_cat_temp .= "</li>"; // dropdown

						$output_cat .= $output_cat_temp;

						$i++;
					}

					// --> Rentals
					$output_cat .= "<li class='dropdown header-dropdown nav-pinned-fluid-text" . $f_class_hide . "'>";
						$output_cat .= "<a href=\"" . $_SESSION['fluid_uri'] . "Rentals\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='dropdown-toggle " . $f_class_navbar_a  . "'><div style='margin-top: 3px;'>Rentals <span class='caret'></span></div></a>";
					$output_cat .= "</li>"; // dropdown

					// --> More button
					if(FLUID_NAVBAR_CART_MENU == TRUE)
						$f_class_more_hide = "f-class-more";
					else
						$f_class_more_hide = "f-class-more-hide";

					$category_data_temp = "<li class='dropdown header-dropdown nav-pinned-fluid-text " . $f_class_more_hide . "'>";
						$category_data_temp .= "<a href='#' class='dropdown-toggle " . $f_class_navbar_a .  "' data-toggle='dropdown'><div style='margin-top: 3px;'>More <span class='caret'></span></div></a>";

						$category_data_temp .= "<div class='dropdown-menu header-dropdown-menu fluid-dropdown-overflow'>";
							$category_data_temp .= "<ul class='nav-list list-inline fluid-nav-list'>";
							$category_data_temp .= "<div class='f-cat-hide-header-768-h'>" . $category_more_768 . "</div><div class='f-cat-hide-header-992-h'>" . $category_more_992 . "</div><div class='f-cat-hide-header-1200-h'>" . $category_more_1200 . "</div><div class='f-cat-hide-header-1600-h'>" . $category_more_1600 . "</div>";
							$category_data_temp .= "<li class='header-li'><a href=\"" . $_SESSION['fluid_uri'] . "Rentals\" onmouseover=\"JavaScript:this.style.cursor='pointer';\"><div class='header-li-category-name'><span class=\"f-circle\"></span> Rentals</div></a></li>";
							$category_data_temp .= "</ul>"; // nav-list
						$category_data_temp .= "</div>"; // dropdown-menu
					$category_data_temp .= "</li>"; // dropdown

					$output_cat .= $category_data_temp;
				}

				echo $output_cat;

				$fluid_header->php_db_commit();
			?>
		</ul>
		</div>
 </div>

		<?php
		if(FLUID_NAVBAR_CART_MENU == TRUE) {
			echo "<div class='col-md-2 col-sm-2 fluid-collapse-auto' name='fluid-collapse-div' style='padding: 0px; margin: -1px 0px 0px 0px;'>";
			$f_class_nav = "class='fluid-navbar-icons-hide'";
		}
		else {
			echo "<div class='col-md-0 col-sm-0 fluid-collapse-auto' name='fluid-collapse-div' style='padding: 0px; margin: -1px 0px 0px 0px;'>";
			$f_class_nav = "class='fluid-navbar-icons-hide-mode'";
		}
		?>

		<?php
		// These buttons are for desktop navbar only.
		?>
		<div id='fluid-navbar-icons-div' style='padding: 0px; float:right;' <?php echo $f_class_nav; ?>>
			<ul style='padding: 0px;' class="collapse navbar-collapse nav navbar-nav navbar-right fluid-navbar-nav fluid-mobile-navbar fluid-dropdown-parent fluid-background-nav" id="fluid-cart">
				<li id="fluid-cart-parent-pinned" class="login-cart-search-hide dropdown fluid-dropdown-parent">
					<a href="#" data-toggle="dropdown" class='dropdown-toggle header-cart-toggle-nav fluid-header-right-icons' onClick="js_fluid_nav_toggle_close(); $('.header-menu-toggle').parent().removeClass('open'); $('.header-cart-toggle-mobile').parent().removeClass('open'); $('.header-cart-toggle-top').parent().removeClass('open'); document.getElementById('fluid-cart-dropdown').innerHTML = ''; document.getElementById('fluid-cart-dropdown-mobile').innerHTML = '';  document.getElementById('fluid-cart-dropdown-nav').innerHTML = Base64.decode(FluidMenu.cart['html']); js_fluid_block_animate(null);"><span style='font-size: 22px;' class="glyphicon glyphicon-shopping-cart glyphicon-inverse" aria-hidden="true"></span><span class="badge" style="position: relative; margin-left: -15px; margin-top: -33px;" id="fluid-cart-badge-dropdown" ><?php echo $fluid_cart_num_items; ?></span></a>

					<div id="fluid-cart-dropdown-nav" class="dropdown-menu fluid-dropdown-menu-arrow-navbar dropdown-menu-right fluid-stay-open" style="z-index: 1150; padding:17px;">
					<?php
						//echo $fluid_cart_html;
					?>
					</div><?php // <!-- /.dropdown-menu --> ?>
				</li>
			</ul>

			<ul style='padding: 0px; float:right;' class="collapse navbar-collapse nav navbar-nav navbar-right fluid-navbar-nav fluid-mobile-navbar fluid-dropdown-parent fluid-background-nav" id="fluid-account">
				<?php
				//if($detect->isiOS() && $detect->isTablet()) {
				if($f_ipad == TRUE) {
				?>
				<li id="fluid-account-parent-pinned" class="login-cart-search-hide dropdown fluid-dropdown-parent header-menu-toggle">
					<a href="#" data-toggle='modal' data-backdrop="static" data-keyboard="false" data-target='#fluid-login-modal' class='dropdown-toggle fluid-header-right-icons' onClick="js_close_toggle_menus(); $('.header-cart-toggle-top').parent().removeClass('open'); document.getElementById('fluid-account-dropdown').innerHTML = ''; document.getElementById('fluid-account-dropdown-nav').innerHTML = '';  document.getElementById('modal-login-div').innerHTML = Base64.decode(FluidMenu.account['html']); <?php if(empty($_SESSION['u_id'])) { echo " document.getElementById('modal-checkout-fluid-div').innerHTML = ''; js_fluid_login(); document.getElementById('fluid-div-signup').innerHTML = Base64.decode(FluidMenu.account['signup_mobile_html']); document.getElementById('modal-checkout-div-header').style.display = 'none'; document.getElementById('fluid-login-back-button').style.display = 'none'; fluid_facebook_checkout = 0; fluid_google_checkout = 0;"; } ?>"><span class="glyphicon glyphicon-user glyphicon-inverse header-font-icon" aria-hidden="false"></span></a>
					<div id="fluid-account-dropdown-nav" class="dropdown-menu fluid-dropdown-menu-arrow-navbar-account dropdown-menu-right fluid-stay-open fluid-account-box">
					</div><?php // <!-- /.dropdown-menu -->?>
				</li>
				<?php
				}
				else {
				?>
				<li id="fluid-account-parent-pinned" class="login-cart-search-hide dropdown fluid-dropdown-parent header-menu-toggle">
					<a href="#" data-toggle="dropdown" class='dropdown-toggle fluid-header-right-icons' onClick="$('.header-cart-toggle-top').parent().removeClass('open'); document.getElementById('fluid-account-dropdown').innerHTML = ''; document.getElementById('modal-login-div').innerHTML = ''; document.getElementById('fluid-account-dropdown-nav').innerHTML = Base64.decode(FluidMenu.account['html']); <?php if(empty($_SESSION['u_id'])) { echo " document.getElementById('modal-checkout-fluid-div').innerHTML = ''; js_fluid_login(); fluid_facebook_checkout = 0; fluid_google_checkout = 0;"; } ?>"><span class="glyphicon glyphicon-user glyphicon-inverse header-font-icon" aria-hidden="true"></span></a>
					<div id="fluid-account-dropdown-nav" class="dropdown-menu fluid-dropdown-menu-arrow-navbar-account dropdown-menu-right fluid-stay-open fluid-account-box">
					</div><?php // <!-- /.dropdown-menu -->?>
				</li>
				<?php
				}
				?>
			</ul>


			<ul style='padding: 0px; float:right;' class="collapse navbar-collapse nav navbar-nav navbar-right fluid-navbar-nav fluid-mobile-navbar fluid-dropdown-parent fluid-background-nav">
				<li id="fluid-navbar-search-icon" style='display: none;' class="login-cart-search-hide dropdown fluid-dropdown-parent header-menu-toggle">
					<a href="javascript:void(0);" class='dropdown-toggle fluid-header-right-icons' onclick="js_fluid_navbar_search_flip_search();"><span class="glyphicon glyphicon-search header-font-icon" aria-hidden="true"></span></a>
				</li>
			</ul>
		</div> <?php // --> id='fluid-navbar-icons-div' class='fluid-navbar-icons-hide' ?>
 </div>
</div>

	  </div><?php // <!-- /.container-fluid --> ?>

</div> <?php // navbar-primary ?>

	<div class="navbar-secondary" style='width: 100%; padding: 0px; margin: 0px;'>
		<?php
		/*
		<a href="javascript:void(0);" onclick="$('#fluid-navigation-bar').attr('class','navbar navbar-rotate-primary')">Face 2</a>
		<a href="javascript:void(0);" onclick="$('#fluid-navigation-bar').attr('class','navbar navbar-rotate-tertiary')">Face 3</a>
		*/
		?>
	</div>

	<div class="navbar-tertiary" style='width: 100%; padding: 0px; margin: 0px;'>
			<div style="display: inline-block; vertical-align: middle; width: 100%; padding: 0px; margin: -1px 0px 0px 0px; height: 57px;">

				<ul style='padding: 0px 5px 0px 5px; display: inline-block; float: left; margin: 0px;' class="nav navbar-nav fluid-background-nav">
					<li class="login-cart-search-hide dropdown fluid-dropdown-parent header-menu-toggle">
						<a style='padding: 15px 10px 15px 10px;' href="javascript:void(0);" onclick="js_fluid_navbar_search_flip_primary();"><span style='font-size: 22px; color: white;' class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span></a>
					</li>
				</ul>

				<ul class='fluid-navbar-search-ul' style='display: inline-block; padding: 10px 0px 0px 0px;' id="fluid-navbar-search">
						<div style='display: inline-block; width: 100%; margin-top: 1px;'>
						  <div class="custom-search-input-navbar">
							  <div class="input-group">
								<?php
								if(FLUID_LIVE_SEARCH_ENABLED == TRUE) {
								?>
								  <input id='fluid-search-input-navbar' type="text" class="form-control input-lg form-control-custom-search-input-navbar" placeholder="Search" onFocus='FluidTime.f_mobile = true;' onClick="FluidTime.f_mobile = true; js_fluid_search_keyup_mobile();" onKeyUp="FluidTime.f_mobile = true; js_fluid_search_suggestions(this.value);" onKeyDown = "if(event.keyCode == 13)$('#fluid-search-button-navbar').click();" style='height: 33px;'/>
								<?php
								}
								else {
								?>
								  <input id='fluid-search-input-navbar' type="text" class="form-control input-lg form-control-custom-search-input-navbar" placeholder="Search" onKeyDown = "if(event.keyCode == 13)$('#fluid-search-button-navbar').click();" style='height: 33px;'/>
								<?php
								}
								?>
								  <span class="input-group-btn fluid-btn">
									  <div id='fluid-search-button-navbar' onmouseover="JavaScript:this.style.cursor='pointer';" onClick='js_fluid_search("fluid-search-input-navbar");'>
										  <i class="glyphicon glyphicon-search" style='padding-bottom: 5px;'></i>
									  </div>
								  </span>
							  </div>
						  </div>
						</div>
				</ul>

			</div>
	</div>

</div> <?php // navbar-persepective ?>
	</nav>

	<div id='f-live-search-control-mobile' style='width: 95%; margin: auto;'>
	  <div id='f-search-dropdown-mobile' class='dropdown f-search-drop-trigger'>
	  	<button id='f-search-suggestions-trigger-mobile' name='fluid_toggle_close' href="#" class="dropdown-toggle" data-trigger=".f-search-drop-trigger" data-toggle="dropdown" style='display: none;'></button>
		<div id="fluid-search-suggestions-dropdown-mobile" class="dropdown-menu fluid-dropdown-menu-arrow-centre dropdown-menu-right fluid-search-suggestions-box" style='width: 100%; border-color: darkgrey;'>
			<div id='f-live-search-mobile'></div>
		</div>
	  </div>
	 </div>

<div id='fluid-nav-div-hidden'></div>

<style>
	.navbar-fixed-bottom {
	  background: transparent;
	}

	.navbar-perspective {
	  width: 100%;
	  height: 100%;
	  position: relative;
	  padding: 0px;
	  margin: 0px;
<?php
/*
  -webkit-perspective: 1000px;
  -moz-perspective: 1000px;
  perspective: 1000px;
  -webkit-perspective-origin: 50% 0;
  -moz-perspective-origin: 50% 0;
  perspective-origin: 50% 0;
*/
?>
	}

	.navbar-perspective > div {
	  border: 1px solid #282828;
	  height: 57px;
	  position: absolute;
	  width: 100%;
	  padding: 0px;
	  margin: 0px;
	  text-align: justify;
	  -webkit-backface-visibility: hidden;
	  -moz-backface-visibility: hidden;
	  backface-visibility: hidden;

<?php
// Is iOS device but not a iPad, then we need transform origin for the navbar rotation to  -12 for iPhones for some odd bug in Safari. This only for iOS < 11.0
if($detect->isiOS() && !$detect->isTablet() && $f_ios_version < 11) {
?>
	  -webkit-transform-origin: 25px 25px -12px;
	  -moz-transform-origin: 25px 25px -12px;
	  -ms-transform-origin: 25px 25px -12px;
	  transform-origin: 25px 25px -12px;
<?php
}
else {
?>
	  -webkit-transform-origin: 25px 25px -25px;
	  -moz-transform-origin: 25px 25px -25px;
	  -ms-transform-origin: 25px 25px -25px;
	  transform-origin: 25px 25px -25px;
<?php
}
?>
	  -webkit-transition: all 0.5s;
	  -o-transition: all 0.5s;
	  transition: all 0.5s;
	}
	.navbar-primary {
	  z-index: 4;
	}

	.navbar .navbar-secondary {
	  background-color: #404040;
	  z-index: 3;
	  -webkit-transform: rotateX(-90deg);
	  -ms-transform: rotateX(-90deg);
	  -o-transform: rotateX(-90deg);
	  transform: rotateX(-90deg);
	}

	.navbar .navbar-tertiary {
	  background-color: #282828;
	  z-index: 2;
	  -webkit-transform: rotateX(-180deg);
	  -ms-transform: rotateX(-180deg);
	  -o-transform: rotateX(-180deg);
	  transform: rotateX(-180deg);
	}

	.navbar-rotate-secondary .navbar-primary {
	  -webkit-transform: rotateX(180deg);
	  -ms-transform: rotateX(180deg);
	  -o-transform: rotateX(180deg);
	  transform: rotateX(180deg);
	}

	.navbar-rotate-secondary .navbar-secondary {
	  -webkit-transform: rotateX(0deg);
	  -ms-transform: rotateX(0deg);
	  -o-transform: rotateX(0deg);
	  transform: rotateX(0deg);
	}

	.navbar-rotate-tertiary .navbar-primary {
	  -webkit-transform: rotateX(180deg);
	  -ms-transform: rotateX(180deg);
	  -o-transform: rotateX(180deg);
	  transform: rotateX(180deg);
	}
	.navbar-rotate-tertiary .navbar-secondary {
	  -webkit-transform: rotateX(90deg);
	  -ms-transform: rotateX(90deg);
	  -o-transform: rotateX(90deg);
	  transform: rotateX(90deg);
	}
	.navbar-rotate-tertiary .navbar-tertiary {
	  -webkit-transform: rotateX(0deg);
	  -ms-transform: rotateX(0deg);
	  -o-transform: rotateX(0deg);
	  transform: rotateX(0deg);
	}
</style>

<script>
	<?php //$('.login-cart-search-hide').css({'display' : 'none'}); ?>
	<?php //js_navbar(); ?>
	$(window).scroll(function() {
		js_navbar();
	});
</script>
	<?php
	if($cart == FALSE)
		php_google_maps_autofill_ship(); // --> fluid.account.php

	// Not logged in, lets pre load a registration modal.
	if(isset($_SESSION['u_id']) == FALSE) {
	?>
	<style>
		.form-gap {
			padding-top: 70px;
		}
	</style>

	<script>
		function js_fluid_register_clear() {
			document.getElementById('fluid_first_name_register').value = "";
			document.getElementById('fluid_last_name_register').value = "";
			document.getElementById('fluid_email_register').value = "";
			document.getElementById('fluid_email_again_register').value = "";
			document.getElementById('fluid_password_register').value = "";
			document.getElementById('fluid_password_again_register').value = "";

			$('#fluid_form_register').validator('validate');
			$('#fluid_form_register')[0].reset();
		}

		function js_fluid_forgot_password_clear() {
			document.getElementById("fluid-reset-forget-div").style.display = "none";
			document.getElementById("fluid_email_forgot").value = "";
			document.getElementById("forget-hide-id").style.display = "block";

			$('#fluid_form_forgot_password').validator('validate');
			$('#fluid_form_forgot_password')[0].reset();
		}

		function js_fluid_code_validator() {
			$('#fluid_form_security_code').validator('validate');
			$('#fluid_form_security_code')[0].reset();
		}

		function js_fluid_password_validator() {
			$('#fluid_form_password_reset').validator('validate');
			$('#fluid_form_password_reset')[0].reset();
		}

		function js_fluid_register() {
			$('#fluid_form_register').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				<?php // handle the invalid form... ?>
			  } else {
				<?php // everything looks good! ?>
				e.preventDefault(e);

				try {
					var FluidRegister = {};
						FluidRegister.u_first_name = document.getElementById('fluid_first_name_register').value;
						FluidRegister.u_last_name = document.getElementById('fluid_last_name_register').value;
						FluidRegister.u_email = document.getElementById('fluid_email_register').value;
						FluidRegister.u_password = document.getElementById('fluid_password_register').value;

					var data = Base64.encode(JSON.stringify(FluidRegister));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_register&data=" + data}));
					<?php //$('#fluid_form_register').validator('destroy'); ?>
					js_fluid_ajax(data_obj);
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_reset_password() {
			$('#fluid_form_forgot_password').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				<?php // handle the invalid form... ?>
			  } else {
				<?php // everything looks good! ?>
				e.preventDefault(e);

				try {
					var FluidReset = {};
						FluidReset.u_email = document.getElementById('fluid_email_forgot').value;

					var data = Base64.encode(JSON.stringify(FluidReset));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_send_reset_token&data=" + data}));

					js_fluid_ajax(data_obj);

				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_security_code() {
			$('#fluid_form_security_code').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				<?php // handle the invalid form... ?>
			  } else {
				<?php // everything looks good! ?>
				e.preventDefault(e);

				try {
					var FluidReset = {};
						FluidReset.u_token_reset = document.getElementById('fluid_email_security_code').value;
						FluidReset.u_email = document.getElementById('fluid_security_code_email').value;

					var data = Base64.encode(JSON.stringify(FluidReset));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_send_reset_password&data=" + data}));

					js_fluid_ajax(data_obj);

				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_password_reset() {
			$('#fluid_form_password_reset').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				<?php // handle the invalid form... ?>
			  } else {
				<?php // everything looks good! ?>
				e.preventDefault(e);

				try {
					var FluidReset = {};
						FluidReset.u_token_reset = document.getElementById('u_token_reset').value;
						FluidReset.u_email = document.getElementById('u_email').value;
						FluidReset.u_password = document.getElementById('fluid_password_reset').value;

					var data = Base64.encode(JSON.stringify(FluidReset));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_password_reset&data=" + data}));

					js_fluid_ajax(data_obj);

				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

	</script>
<?php
	}
?>

<?php
}
?>
	<script>
	function js_cancel_event(evt) {
		try {
			var e = (typeof evt != 'undefined') ? evt : window.event;
			e.cancelBubble = true;
			e.stopPropagation();
		}
		catch(err) {
			js_debug_error(err);
		}
	}

	function js_gs_product_click(data) {
		js_loading_start();
		var value = JSON.parse(Base64.decode(data));

		<?php
		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
		?>
		  ga('ec:addProduct', {
				'id': value['p_mfgcode'],
				'name': value['m_name'] + " " + value['p_name'],
				'category': value['c_name'],
				'brand': value['m_name'],
				'variant': value['p_mfgcode'],
				'price': value['p_price'],
				'quantity': value['p_qty'],
				'position': value['position']
		  });
		  ga('ec:setAction', 'click', {list: document.title});

		  // Send click with an event, then send user to product page.
		  ga('send', 'event', 'UX', 'click', 'Results', {
			hitCallback: function() {
			  document.location = value['url'];
			}
		  });
		 <?php
		}
		else {
		?>
			document.location = value['url'];
		<?php
		}
		?>
	}

	function js_ga_cart(data) {
		<?php
		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
		?>
			var gs_add = {};
			var gs_remove = {};

			$.each(data, function(key, value) {
				if(Fluid_ga_cart[key] == null) {
					Fluid_ga_cart[key] = value;

					gs_add[key] = value;
				}
				else {
					if(Fluid_ga_cart[key]['p_qty'] != value['p_qty']) {
						var f_old_qty = Fluid_ga_cart[key]['p_qty'];
						Fluid_ga_cart[key]['p_qty'] = value['p_qty'];

						if(f_old_qty > value['p_qty'])
							gs_remove[key] = value;
						else
							gs_add[key] = value;
					}
				}
			}
			);


			$.each(Fluid_ga_cart, function(key, value) {
				if(data == null) {
					gs_remove[key] = value;
					delete Fluid_ga_cart[key];
				}
				else if(Fluid_ga_cart[key]['p_qty'] < 1 || data[key] == null)
					delete Fluid_ga_cart[key];
			}
			);


			var f_add = false;
			$.each(gs_add, function(key, value) {
				ga('ec:addProduct', {
					'id': value['p_mfgcode'],
					'name': value['m_name'] + " " + value['p_name'],
					'category': value['c_name'],
					'brand': value['m_name'],
					'variant': value['p_mfgcode'],
					'price': value['p_price'],
					'quantity': value['p_qty']
				});

				f_add = true;
			}
			);

			var f_remove = false;
			$.each(gs_remove, function(key, value) {
				ga('ec:addProduct', {
					'id': value['p_mfgcode'],
					'name': value['m_name'] + " " + value['p_name'],
					'category': value['c_name'],
					'brand': value['m_name'],
					'variant': value['p_mfgcode'],
					'price': value['p_price'],
					'quantity': value['p_qty']
				});

				f_remove = true;
			}
			);

			if(f_add == true) {
				ga('ec:setAction', 'add', {list: document.title});
				ga('send', 'event', 'UX', 'click', 'add to cart');
			}

			if(f_remove == true) {
				ga('ec:setAction', 'remove', {list: document.title});
				ga('send', 'event', 'UX', 'click', 'remove from cart');
			}
		<?php
		}
		?>
	}

	function js_fluid_add_to_cart(btn, p_id) {
		try {
			var FluidData = {};
				FluidData.p_id = p_id;
				FluidData.p_qty = document.getElementById("fluid-cart-qty-" + p_id).options[document.getElementById("fluid-cart-qty-" + p_id).selectedIndex].value;
				FluidData.button_id = btn.id;

			<?php
			// --> We store the btn object directly into memory for performance increase. Searching the document for a div to load is intensive, especially on large documents.
			// --> There is still a bit of a performance hit in js_fluid_add_to_cart_animation when searching for the btn_div. Need to find a way to pass this info somehow without doing a dom search.
			?>
			FluidMenu.button.obj = btn;
			FluidMenu.button.id = btn.id;

			<?php
			if($cart == TRUE) {
			?>
				FluidData.f_checkout_id = FluidTemp.f_checkout_id;
				FluidData['a_id'] = FluidMenu.shipping.a_id;
				FluidData['f_refresh_shipping'] = FluidTemp.f_refresh_shipping;
				FluidData['checkout'] = 1;
				FluidData['f_paypal'] = FluidMenu.paypal;

				FluidTemp.f_refresh_shipping = false;
			<?php
			}
			?>
			var data = Base64.encode(JSON.stringify(FluidData));

			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_add_to_cart&data=" + data}));

			js_fluid_ajax(data_obj);
		}
		catch (err) {
			js_debug_error(err);
		}
	}

	function js_fluid_add_to_cart_animation(data) {
		try {
			var btn = null;
			var btn_div = null;
			<?php //var btn_div = $('#fluid-button-' + Base64.decode(data['p_id'])); ?>

			<?php // --> Speed improvements ?>
			if(FluidMenu.button.id != null && FluidMenu.button.obj != null)
				if(FluidMenu.button.id == Base64.decode(data['button_id']))
					btn = FluidMenu.button.obj;

			if(FluidMenu.button.obj_div != null && FluidMenu.button.obj_div_id != null)
				if(FluidMenu.button.obj_div_id = Base64.decode(data['button_id']))
					btn_div = $(FluidMenu.button.obj_div);

			if(btn_div == null)
				btn_div = $('#fluid-button-' + Base64.decode(data['p_id']));

			<?php // --> Reset to null so mulitple button data does not overlap. ?>
			FluidMenu.button.obj = null;
			FluidMenu.button.id = null;
			FluidMenu.button.obj_div = null
			FluidMenu.button.obj_div_id = null;

			if(btn == null) {
				var f_btn_check = document.getElementsByName(Base64.decode(data['button_id']));

				for(var x=0; x < f_btn_check.length; x++) {
					if($(f_btn_check[x]).visible(true)) {
						btn = f_btn_check[x];
						break;
					}
				}
			}

			if(btn == null)
				btn = document.getElementById(Base64.decode(data['button_id']));

			var btn_innerHTML = btn.innerHTML;

			var f_viewport_size = js_viewport_size()['width'];

			const COLORS = {
				RED:      '#FD5061',
				YELLOW:   '#FFCEA5',
				BLACK:    '#29363B',
				WHITE:    'white',
				VINOUS:   '#A50710'
			}
			var a_radius = 150;

			if(f_viewport_size < 400)
				a_radius = 100;

			const burst1 = new mojs.Burst({
					left: 16, top: 12,
					count:          16,
					radius:         { 50: a_radius },
					children: {
					shape:        'line',
					stroke:       [ 'white', '#FFE217', '#FC46AD', '#D0D202', '#B8E986', '#D0D202' ],
					scale:        1,
					scaleX:       { 1 : 0 },
					// pathScale:    'rand(.5, 1.25)',
					degreeShift:  'rand(-90, 90)',
					radius:       'rand(20, 40)',
					// duration:     200,
					delay:        'rand(0, 150)',
					isForce3d:    true
				}
			});
			burst1.el.style.zIndex = 100000;

			const burst2 = new mojs.Burst({
					left: 16, top: 12,
					count:          4,
					radius:         0,
					angle:         45,
					children: {
					shape:        'line',
					stroke:       '#4ACAD9',
					scale:        1,
					scaleX:       { 1 : 0 },
					radius:       100,
					duration:     450,
					isForce3d:    true,
					easing:       'cubic.inout'
				}
			});
			burst2.el.style.zIndex = 100000;

			const burst3 = new mojs.Burst({
				left: 16, top: 12,
				count: 		9,
				degree: 	0,
				radius: 	{80:250},
				angle:   -90,
				children: {
					top: 			[ 0, 45, 0 ],
					left: 		[ -25, 0, 25 ],
					shape: 		'line',
					fill: 		'#C0C1C3',
					radius: 	{60:0},
					scale: 		1,
					stroke: 	'#988ADE',
					degreeShift:  'rand(-10, 10)',
					opacity:  0.6,
					duration: 650,
					easing: 	mojs.easing.bezier(0.1, 1, 0.3, 1)
				},
			});
			burst3.el.style.zIndex = 100000;

			const burst4 = 	new mojs.Burst({
				left: 16, top: 12,
				count: 	12,
				radius: {60:90},
				degree: -90,
				angle: 	135,
				children: {
					shape: 				'line',
					radius: 			{30:0},
					scale: 				1,
					stroke: 			'#988ADE',
					strokeWidth: 	{2:1},
					duration: 		600,
					delay: 				200,
					easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
				},
			});
			burst4.el.style.zIndex = 100000;

			const burst5 =	new mojs.Burst({
				left: 16, top: 12,
				radius: 			{40:110},
				count: 				20,
				children: {
					shape: 			'line',
					fill : 			'white',
					radius: 		{ 12: 0 },
					scale: 			1,
					stroke: 		'#988ADE',
					strokeWidth: 2,
					duration: 	1500,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				},
			});
			burst5.el.style.zIndex = 100000;

			const ring1 = new mojs.Shape({
				left: 16, top: 12,
				radius: 			50,
				scale: 				{ 0 : 1 },
				fill: 				'transparent',
				stroke: 			'#988ADE',
				strokeWidth: 	{15:0},
				opacity: 			0.6,
				duration: 		750,
				easing: 			mojs.easing.bezier(0, 1, 0.5, 1)
			});
			ring1.el.style.zIndex = 100000;

			var scaleCurve6 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');
			const shake1 = new mojs.Tween({
				duration : 800,
				easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
				onUpdate: function(progress) {
					var scaleProgress = scaleCurve6(progress);
					btn.style.WebkitTransform = btn.style.transform = 'scale3d(' + progress + ',' + progress + ',1)';
				}
			});

			const shake2 = new mojs.Tween({
				duration: 1200,
				onUpdate: function(progress) {
					var elasticOutProgress = mojs.easing.elastic.out(progress);
					btn.style.WebkitTransform = btn.style.transform = 'translate3d(' + -75*(1-elasticOutProgress) + '%,0,0)';
				}
			});

			var offset = $(btn).offset()
			var width = $(btn).width();
			var height = $(btn).height();

			var centerX = offset.left + width / 2;
			var centerY = offset.top + height / 2;

			<?php // --> Fisrt burst and shake. ?>
			burst1
				.tune({ x: centerX, y: centerY })
				.generate()
				.replay();

			if(f_viewport_size > 399) {
				burst2
					.tune({ x: centerX, y: centerY })
					.generate()
					.replay();
			}

			shake2
				.replay();

			var btn_height = $('#' + Base64.decode(data['button_id'])).height();
			btn.innerHTML = "<div style='margin: 0px 0px 0px 0px; padding: 0px 0px 0px 0px; min-height: " + btn_height + "px;'></div>";
			btn.className += " btn-fluid-round";
			btn.className += " disabled";
			btn.className = btn.className.replace( /(?:^|\s)btn-block(?!\S)/g , '' )

		<?php
		if($cart == FALSE) {
		?>
			if($('#fluid-header-top-logo').visible(true) && f_viewport_size  > 767) {
				<?php
				if(FLUID_NAVBAR_CART_MENU == TRUE)
					echo "if(f_viewport_size  > 991) {";
				else
					echo "if(f_viewport_size  > 767) {";
				?>
					var cart = $('#header-cart-desktop');
					var f_width = 25;
					var f_height = 25;
					<?php
					if(FLUID_NAVBAR_CART_MENU == TRUE) {
					?>
						var f_top_offset = cart.offset().top - 5;
						var f_left_offset = cart.offset().left + 25;
					<?php
					}
					else {
					?>
						if(f_viewport_size  > 991) {
							var f_top_offset = cart.offset().top - 5;
							var f_left_offset = cart.offset().left + 25;
						}
						else {
							var f_top_offset = cart.offset().top - 5;
							var f_left_offset = cart.offset().left;
						}
					<?php
					}
					?>
				}
				else {
					var cart = $('#fluid-cart');
					var f_width = 15;
					var f_height = 15;
					var f_top_offset = cart.offset().top + 10;
					var f_left_offset = cart.offset().left + 25;
				}
			}
			else {
				if(f_viewport_size > 767) {
					<?php
					if(FLUID_NAVBAR_CART_MENU == TRUE && (($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE))) {
					?>
						var cart = $('#fluid-cart');
						var f_top_offset = cart.offset().top + 10;
						var f_left_offset = cart.offset().left + 25;
					<?php
					}
					else {
					?>
						var cart = $('#fluid-cart');
						var scrollTop  = $(window).scrollTop();
						var elementOffset = btn_div.offset().top;
						var distance = (elementOffset - scrollTop);

						var f_top_offset = elementOffset - distance - 40;
						var f_left_offset = f_viewport_size - 40;
					<?php
					}
					?>
				}
				else {
					<?php
					if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) {
					?>
						var cart = $('#fluid-cart-mobile-div');
						var f_top_offset = cart.offset().top + 0;
						var f_left_offset = cart.offset().left + 35;
					<?php
					}
					else {
					?>
						if($('#fluid-header-top-logo').visible(true)) {
							var cart = $('#fluid-cart-mobile-div');
							var f_top_offset = cart.offset().top + 0;
							var f_left_offset = cart.offset().left + 35;
						}
						else {
							var cart = $('#fluid-cart-mobile-div');
							var scrollTop  = $(window).scrollTop();
							var elementOffset = btn_div.offset().top;
							var distance  = (elementOffset - scrollTop);

							var f_top_offset = elementOffset - distance - 40;
							var f_left_offset = f_viewport_size - 10;
						}
					<?php
					}
					?>

				}

				var f_width = 15;
				var f_height = 15;
			}
		<?php
		}
		?>

			<?php //var btn_div = $('#fluid-button-' + Base64.decode(data['p_id'])); ?>

			if(btn_div) {
				setTimeout(function () {
				<?php
				if($cart == FALSE) {
				//if((($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) && $cart == FALSE) {
				?>
					<?php
					// Rotate the navbar back if required to the primary face
					?>
					js_fluid_navbar_search_flip_primary();

					var btn_clone = btn_div.clone();

					btn_clone.offset({
						top: centerY,
						left: centerX,
					})
						.css({
							'width' : '20',
							'opacity': '0.7',
							'position': 'absolute',
							'z-index': '190000'
					})
						.appendTo($('body'))
						.animate({
							'width' : f_width,
							'height' : f_height,
							'top': f_top_offset,
							'left': f_left_offset,
					}, {
						duration: 1000,
						step: function(now, fx) {

						}

					}, 500, 'linear');
				<?php
				}
				?>
					<?php
					// Tone down the effects on non mobile for now. --> Burst #2
						if(!$detect->isMobile()) {
						?>
							setTimeout(function () {
								shake2
									.replay();

								burst5
									.tune({ x: centerX, y: centerY })
									.generate()
									.replay();

							}, 300);
						<?php
						}
					?>
					setTimeout(function () {
					<?php
					// Tone down the effects on non mobile for now. --> Burst #3
						if(!$detect->isMobile()) {
					?>
							shake2
								.replay();

							burst3
								.tune({ x: centerX, y: centerY })
								.generate()
								.replay();

							if(screen.width > 340) {
								burst4
									.tune({ x: centerX, y: centerY })
									.generate()
									.replay();
							}
					<?php
						}
					?>

					<?php // --> Shakes the cart when the item is dropped in via the animation.
					if($cart == FALSE) {
					?>
							cart.effect("shake", {
								times: 2
							}, 200);
					<?php
					}
					?>

						<?php
						if($cart == FALSE) {
						?>
							FluidMenu.cart['html'] = data['cart_html'];
							FluidMenu.cart['num_items'] = data['cart_num_items'];
							FluidMenu.cart['html_editor'] = data['cart_html_editor'];
							FluidMenu.cart['html_ship'] = data['cart_html_ship'];
							FluidMenu.cart['html_ship_select'] = data['cart_html_ship_select'];

							FluidHTML['cart_badge_div'].innerHTML = Base64.decode(data['cart_num_items']);
							FluidHTML['cart_badge_dropdown_div'].innerHTML = Base64.decode(data['cart_num_items']);
							FluidHTML['cart_badge_mobile_div'].innerHTML = Base64.decode(data['cart_num_items']);

							FluidHTML['fluid-cart-dropdown'].innerHTML = "";
							if($('#fluid-cart-dropdown').css('display') != 'none')
								FluidHTML['fluid-cart-dropdown'].innerHTML = Base64.decode(FluidMenu.cart['html']);

							FluidHTML['fluid-cart-dropdown-nav'].innerHTML = "";
							if($('#fluid-cart-dropdown-nav').css('display') != 'none')
								FluidHTML['fluid-cart-dropdown-nav'].innerHTML = Base64.decode(FluidMenu.cart['html']);

							FluidHTML['fluid-cart-dropdown-mobile'].innerHTML = "";
							if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
								FluidHTML['fluid-cart-dropdown-mobile'].innerHTML = Base64.decode(FluidMenu.cart['html']);

							FluidTemp.animate_array = null;
							js_fluid_block_animate(data['f_animate']);

							<?php
							/*
							js_html_insert({div_id : data['cart_badge_div'], html : data['cart_num_items'] });
							js_html_insert({div_id : data['cart_badge_dropdown_div'], html : data['cart_num_items'] });
							js_html_insert({div_id : data['cart_badge_mobile_div'], html : data['cart_num_items'] });

							FluidMenu.cart['html'] = data['cart_html'];
							FluidMenu.cart['num_items'] = data['cart_num_items'];
							FluidMenu.cart['html_editor'] = data['cart_html_editor'];
							FluidMenu.cart['html_ship'] = data['cart_html_ship'];
							FluidMenu.cart['html_ship_select'] = data['cart_html_ship_select'];

							js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : Base64.encode("") });

							if($('#fluid-cart-dropdown').css('display') != 'none')
								js_html_insert({div_id : Base64.encode('fluid-cart-dropdown'), html : FluidMenu.cart['html'] });

							js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : Base64.encode("") });

							js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : Base64.encode("") });

							if($('#fluid-cart-dropdown-nav').css('display') != 'none')
								js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-nav'), html : FluidMenu.cart['html'] });

							if($('#fluid-cart-dropdown-mobile').css('display') != 'none')
								js_html_insert({div_id : Base64.encode('fluid-cart-dropdown-mobile'), html : FluidMenu.cart['html'] });

							FluidTemp.animate_array = null;
							js_fluid_block_animate(data['f_animate']);
							*/
							?>
						<?php
						}
						?>
					}, 1000);

				<?php
				if($cart == FALSE) {
				//if((($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) && $cart == FALSE) {
				?>
					btn_clone.animate({
						'width': 0,
						'height': 0
					}, function () {
						$(this).detach()
					});
				<?php
				}
				?>

					btn.className += " btn-fluid-transparent";

					setTimeout(function () {
						btn.className = btn.className.replace( /(?:^|\s)btn-fluid-round(?!\S)/g , '' )
						btn.className = btn.className.replace( /(?:^|\s)btn-fluid-transparent(?!\S)/g , '' )

						<?php
						if($cart == FALSE) {
						?>
						btn.className += " btn-fluid-round-return";
						<?php
						}
						else {
						?>
						btn.className += " btn-fluid-round-return-cart";
						<?php
						}
						?>
					}, <?php if($cart == TRUE && $detect->isMobile()){echo "400";}else{ echo "1200";} ?>);

					setTimeout(function () {
						var f_particles = document.querySelectorAll('[data-name="mojs-shape"]');

						<?php // Run through all the particles and delete the doms ?>
						for(var x=0; x < f_particles.length; x++)
							f_particles[x].parentNode.removeChild(f_particles[x]);

						<?php
						if($cart == FALSE) {
						?>
						btn.className = btn.className.replace( /(?:^|\s)btn-fluid-round-return(?!\S)/g , '' )
						<?php
						}
						else {
						?>
						btn.className = btn.className.replace( /(?:^|\s)btn-fluid-round-return-cart(?!\S)/g , '' )
						<?php
						}
						?>
						btn.className = btn.className.replace( /(?:^|\s)disabled(?!\S)/g , '' )

						<?php
						if($cart == FALSE) {
						?>
						btn.className += " btn-block";
						<?php
						}
						?>
						btn.innerHTML = btn_innerHTML;
					}, <?php if($cart == TRUE && $detect->isMobile()){echo "900";}else{ echo "1700";} ?>);

				}, 500);
			}

			<?php
			//if($cart == TRUE) {

				//FluidTemp.f_refresh_shipping= true;
				//js_fluid_cart_save();

			//}
			?>
		}
		catch (err) {
			js_debug_error(err);
		}
	}
	</script>
<?php
	php_load_scripts();
?>

<div id='fluid-search-blur-wrap' class='search-loading-overlay'></div>

<?php
}

function php_load_pre_header() {
	$fluid_header = new Fluid();
?>
	<meta name="google-signin-client_id" content="<?php echo GOOGLE_CLIENT_LOGIN_ID; ?>">
	<meta name="format-detection" content="telephone=no">

	<link rel="icon" type="image/png"  href="<?php echo $_SESSION['fluid_uri']; ?>files/leos-logo.png" />

	<!-- Bootstrap Core CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/bootstrap.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/bootstrap-select.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-header.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-footer.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/font-awesome.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/bootstrap.checkbox.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/leos-logo.css');?>">
	<?php /*<link rel="stylesheet" type="text/css" href="<?php echo FOLDER_ROOT; ?>css/roboto.css"> */?>
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/opensans.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid.animate.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/swiper.min.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-account.css');?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-slider.css');?>">

	<?php
		if(FLUID_SAVINGS_TIMER_HIDE == TRUE) {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-hide-timer.css');?>">
		<?php
		}
	?>

	<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '781003482326879');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=781003482326879&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
<?php
}

function php_load_scripts() {

	$detect = new Mobile_Detect;
?>
<script>
	$(document).ready(function() {
		js_fluid_update_selectpicker(); <?php // Update the select pickers with bootstrap-select ?>
	});
</script>
<?php
}

function php_load_auto_scroll() {
?>
<script>
	var checkScrollSpeed = (function(settings){
		settings = settings || {};

		var lastPos, newPos, timer, delta,
			delay = settings.delay || 50; <?php // in "ms" (higher means lower fidelity ) ?>

		function clear() {
		  lastPos = null;
		  delta = 0;
		}

		clear();

		return function(){
		  newPos = window.scrollY;
		  if ( lastPos != null ){ <?php // && newPos < maxScroll ?>
			delta = newPos -  lastPos;
		  }
		  lastPos = newPos;
		  clearTimeout(timer);
		  timer = setTimeout(clear, delay);
		  return delta;
		};
	})();

	$(window).scroll(function(e) {
		clearTimeout($.data(this, 'scrollTimerEnd'));

		if($(this).scrollTop() < 100)
			$('#back-top').fadeOut();

		$.data(this, 'scrollTimerStart', setTimeout(function() {
			var scrollSpeed = checkScrollSpeed();

			<?php // fade in #back-top ?>
			if(scrollSpeed < -25 || scrollSpeed > 145) {
				if($(this).scrollTop() > 100) {
					$('#back-top').fadeIn();
				}
				else {
					$('#back-top').fadeOut();
				}
			}
		}, 900));

		<?php
		/*
		$.data(this, 'scrollTimerStart', setTimeout(function() {
			var scrollSpeed = checkScrollSpeed();

			if(scrollSpeed > 5 && fluid_scroll == false)
				$('#fluid-auto-scroll-down').fadeIn();

			<?php // fade in #back-top ?>
			if(scrollSpeed < -25) {
				if($(this).scrollTop() > 100) {
					$('#back-top').fadeIn();
				}
				else {
					$('#back-top').fadeOut();
				}
			}
		}, 1300));
		*/
		?>

		$.data(this, 'scrollTimerEnd', setTimeout(function() {
			$('#back-top').fadeOut();

			<?php //var scrollSpeed = checkScrollSpeed(); ?>

			<?php //js_fade_out_top(); ?>

			<?php //fluid_scroll = false;?>
			<?php //$('#fluid-auto-scroll-down').fadeOut(); ?>
		}, 3600));
	});

	<?php // var fluid_scroll = false; ?>

	$(document).ready(function(){
		$(function () {
			<?php // Scroll body to 0px on click. ?>
			$('#back-top a').click(function () {

				$('body,html').animate({
					scrollTop: 0
				}, 800);
				return false;
			});

			<?php // Scroll body to bottom on click. ?>
			<?php
			/*
			$('#fluid-auto-scroll-down a').click(function () {
				var scrollAmount = document.body['clientHeight'];
				/// Stop the scrolling.
				if(fluid_scroll == true) {
					js_fluid_auto_scroll_stop();
				}
				else {
					fluid_scroll = true;
					scrollTo('footer_fluid');
					document.getElementById('fluid-up').style.display = "none";
					document.getElementById('fluid-stop').style.display = "block";
				}
			});
			?>
			*/
			?>
		});
	});

 	<?php // Stop the scrolling if the user tries to scroll manually. ?>
 	$('body,html').on("scroll wheel DOMMouseScroll mousewheel keyup touchmove", function(){
 	   js_fluid_auto_scroll_stop();
 	});

 	function js_fade_out_top() {
		var scrollSpeed = checkScrollSpeed();

		<?php // fade in #back-top ?>
		if(scrollSpeed < -25 || scrollSpeed > 145) {
			if($(this).scrollTop() > 100) {
				$('#back-top').fadeIn();
			}
			else {
				$('#back-top').fadeOut();
			}
		}
	}
</script>
<?php
}

function php_load_full_scripts() {
	$detect = new Mobile_Detect;
?>
<script>
	function scrollTo(hash) {
		var pixels_per_second = 200;
		distance = Math.abs($(document.body).scrollTop( ) - $('#' + hash).offset( ).top);
		scroll_duration = (distance / pixels_per_second) * 1000;

		$(document.body).animate({ 'scrollTop':   $('#' + hash).offset().top }, scroll_duration);
	}

	function js_fluid_auto_scroll_stop() {
		<?php //fluid_scroll = false; ?>
		<?php //document.getElementById('fluid-up').style.display = "block"; ?>
		<?php //document.getElementById('fluid-stop').style.display = "none"; ?>
		$('body,html').stop();
	}

	function js_fluid_scroll_to_top() {
		<?php // Stop any auto scrolling first. ?>
		js_fluid_auto_scroll_stop();

		$('body,html').animate({
			scrollTop: 0
		}, 800);
	}

	function js_fluid_scroll_to_dom(f_dom) {
		<?php // Stop any auto scrolling first. ?>
		js_fluid_auto_scroll_stop();

		$('body,html').animate({
			scrollTop: $('#' + f_dom).offset().top
		}, 800);
	}

	function js_fluid_account_dropdown_open() {
		var dropdown_account_menu = document.getElementById("fluid-account-dropdown");
		<?php //dropdown-menu dropdown-menu-right fluid-stay-open fluid-account-box fluid-account-box-scale ?>
		dropdown_account_menu.className += " fluid-account-box-scale";
		dropdown_account_menu.className = dropdown_account_menu.className.replace( /(?:^|\s)dropdown-menu(?!\S)/g , '' );
		dropdown_account_menu.className = dropdown_account_menu.className.replace( /(?:^|\s)dropdown-menu-right(?!\S)/g , '' );
		dropdown_account_menu.className = dropdown_account_menu.className.replace( /(?:^|\s)fluid-stay-open(?!\S)/g , '' );

		document.getElementById("modal-login-div").appendChild(dropdown_account_menu);

		document.getElementById("fluid-account-dropdown").style.display = "block";
	}

	<?php // Detach and reattach the login dropdown menu as required. ?>
	function js_fluid_account_dropdown(parent_id) {
		$('.fluid-account-dropdown').parent().removeClass('fluid-account-box-scale');

		$('.header-cart-toggle').parent().removeClass('open'); // Closes the header cart drop down menu.
		<?php //$('.header-menu-toggle').parent().removeClass('open'); // Closes the header cart drop down menu. ?>

		$('.fluid-mobile-navbar').collapse('hide');

		var dropdown_account_menu = document.getElementById("fluid-account-dropdown");

		dropdown_account_menu.className += " dropdown-menu";
		dropdown_account_menu.className += " dropdown-menu-right";
		dropdown_account_menu.className += " fluid-stay-open";
		dropdown_account_menu.className = dropdown_account_menu.className.replace( /(?:^|\s)fluid-account-box-scale(?!\S)/g , '' );

		if(js_viewport_size()['width'] < 768) {
			document.getElementById("modal-login-div").appendChild(dropdown_account_menu); <?php // Move the login dropdown to the pinned nav bar. ?>
		}
		else {
			if($('#fluid-account-parent-pinned').css('display') == 'none')
				document.getElementById("fluid-account-parent").appendChild(dropdown_account_menu);
			else
				document.getElementById("fluid-account-parent-pinned").appendChild(dropdown_account_menu);
		}

		$('.fluid-account-dropdown').dropdown('toggle')
	}

	<?php
	if(isset($_SESSION['u_id']) == FALSE) {
	?>
		function js_fluid_login() {
			$('#fluid_form_login').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				<?php // handle the invalid form... ?>
			  } else {
				<?php // everything looks good! ?>
				e.preventDefault(e);

				try {
					var FluidLogin = {};
						FluidLogin.u_email = document.getElementById('fluid_email_login').value;
						FluidLogin.u_password = document.getElementById('fluid_password_login').value;
						FluidLogin.u_checkout = document.getElementById('fluid-checkout-login').value;

						if(document.getElementById("fluid_remember_me").checked)
							FluidLogin.u_remember_me = true;

					var data = Base64.encode(JSON.stringify(FluidLogin));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_login&oauth_provider=<?php echo OAUTH_FLUID; ?>&data=" + data}));

					js_fluid_ajax(data_obj);
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}
	<?php
	}
	?>

	<?php
	if(isset($_SESSION['u_id'])) {
	?>
		function js_fluid_logout() {
			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_logout"}));

			js_fluid_ajax(data_obj);
		}
	<?php
	}
	?>
</script>
<?php
}
?>
