<?php
// fluid.settings.php
// Michael Rajotte - 2017 Novembre

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

use Snipe\BanBuilder\CensorWords;

if(empty($_SESSION['fluid_admin'])) {
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);
}

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load'])) {
	if(function_exists($_REQUEST['function'])) {
		echo call_user_func($_REQUEST['function']);
	}
	else {
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");
	}
}

// --> Store setting editor modal. Edit store opening or closed, shipping boxes information etc.
function php_fluid_load_option_set() {
	try {
		$fluid = new Fluid();

		// Navbar settings html.

		// Navbar pin
		$navbar_html = "<div style='display: table; width: 100%;'>";
			$navbar_html .= "<div class='well' style='margin-top: 20px;'>";
				$navbar_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

				$navbar_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Navbar controls: Enable accounts and cart in the navbar?</div>";
				$navbar_html .= "<div style='padding-top: 5px;'>";
					$navbar_html .= "<div class=\"input-group\">";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Navbar controls</div></span>";
						$navbar_html .= "<select id='fs-navbar-menu' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

							if(FLUID_NAVBAR_CART_MENU == TRUE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
							$navbar_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

							if(FLUID_NAVBAR_CART_MENU == FALSE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
							$navbar_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

						$navbar_html .= "</select>";
					$navbar_html .= "</div>";
				$navbar_html .= "</div>";
				$navbar_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> When enabled, the shopping cart and account menu will appear on the navbar in tablet and desktop mode. Disabling this will make more categories fill the entire navbar, but when enabled then much less categories can fit on the navbar.</div>";

				// Navbar pin
				$navbar_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Navbar: Pin the navbar to the top while scrolling?</div>";
				$navbar_html .= "<div style='padding-top: 5px;'>";
					$navbar_html .= "<div class=\"input-group\">";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Navbar pinned</div></span>";
						$navbar_html .= "<select id='fs-navbar-pinned' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

							if(FLUID_NAVBAR_PIN == TRUE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
							$navbar_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

							if(FLUID_NAVBAR_PIN == FALSE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
							$navbar_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

						$navbar_html .= "</select>";
					$navbar_html .= "</div>";
				$navbar_html .= "</div>";

				// Navbar pin mobile
				$navbar_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Mobile Navbar: Pin the navbar to the top while scrolling for mobile devices?</div>";
				$navbar_html .= "<div style='padding-top: 5px;'>";
					$navbar_html .= "<div class=\"input-group\">";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 90%;'>Navbar pinned mobile</div></span>";
						$navbar_html .= "<select id='fs-navbar-pinned-mobile' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

							if(FLUID_NAVBAR_PIN_MOBILE == TRUE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
							$navbar_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

							if(FLUID_NAVBAR_PIN_MOBILE == FALSE)
								$selected = "selected";
							else
								$selected = NULL;

							$navbar_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
							$navbar_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

						$navbar_html .= "</select>";
					$navbar_html .= "</div>";
				$navbar_html .= "</div>";


				$navbar_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Navbar category adjustment: Set how many categories are displayed on the navbar depending on the screen resolution in pixels. This helps you adjust to fit them onto the navbar properly. When the max number is reached during a given resolution, they will be merged into a MORE dropdown menu to fit onto the navbar properly.</div>";
				// Navbar category adjustment.
				$navbar_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>768px</div></span>";
					$navbar_html .= "<input id=\"fs-menu-alt-768\" type=\"number\" class=\"form-control\" placeholder=\"Example: 5\" value=\"" . FLUID_MENU_SIZE_ALT_768 . "\">";
				$navbar_html .= "</div>";
				$navbar_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>992px</div></span>";
					$navbar_html .= "<input id=\"fs-menu-alt-992\" type=\"number\" class=\"form-control\" placeholder=\"Example: 5\" value=\"" . FLUID_MENU_SIZE_ALT_992 . "\">";
				$navbar_html .= "</div>";

				$navbar_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>1200px</div></span>";
					$navbar_html .= "<input id=\"fs-menu-alt-1200\" type=\"number\" class=\"form-control\" placeholder=\"Example: 5\" value=\"" . FLUID_MENU_SIZE_ALT_1200 . "\">";
				$navbar_html .= "</div>";

				$navbar_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
					$navbar_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>1600px</div></span>";
					$navbar_html .= "<input id=\"fs-menu-alt-1600\" type=\"number\" class=\"form-control\" placeholder=\"Example: 5\" value=\"" . FLUID_MENU_SIZE_ALT_1600 . "\">";
				$navbar_html .= "</div>";


				$navbar_html .= "</div>";
			$navbar_html .= "</div>";
		$navbar_html .= "</div>";

		// Lets build the slider settings html data now.
		$sms_html = "<div style='display: table; width: 100%;'>";
			$sms_html .= "<div class='well' style='margin-top: 20px;'>";
				$sms_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$sms_html .= "<div style='padding: 5px 20px 5px 20px;'>";
						$sms_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>SMS System configuration</div>";
					$sms_html .= "</div>";

					$sms_html .= "<div style='padding: 5px 20px 20px 20px;'>";
						$sms_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Twilio SMS System</div>";

						// SMS Status
						$sms_html .= "<div style='padding-top: 5px;'>";
							$sms_html .= "<div class=\"input-group\">";
							$sms_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>SMS Status</div></span>";
								$sms_html .= "<select id='fs-sms-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(TWILIO_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sms_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sms_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(TWILIO_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sms_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sms_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$sms_html .= "</select>";
							$sms_html .= "</div>";
						$sms_html .= "</div>";

						$sms_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sms_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Twilio SID</div></span>";
							$sms_html .= "<input id=\"fs-sms-sid\" type=\"text\" class=\"form-control\" placeholder=\"Twilio account sid #\" value=\"" . htmlspecialchars(TWILIO_ACCOUNT_SID) . "\">";
						$sms_html .= "</div>";

						$sms_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sms_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Twilio Token</div></span>";
							$sms_html .= "<input id=\"fs-sms-token\" type=\"text\" class=\"form-control\" placeholder=\"Twilio auth token\" value=\"" . htmlspecialchars(TWILIO_AUTH_TOKEN) . "\">";
						$sms_html .= "</div>";

						$sms_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sms_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Phone Number</div></span>";
							$sms_html .= "<input id=\"fs-sms-number\" type=\"text\" class=\"form-control\" placeholder=\"Phone Number ex: +15555555555\" value=\"" . htmlspecialchars(TWILIO_NUMBER) . "\">";
						$sms_html .= "</div>";

					$sms_html .= "</div>";
				$sms_html .= "</div>";
			$sms_html .= "</div>";
		$sms_html .= "</div>";

		// Lets build the slider settings html data now.
		$slide_html = "<div style='display: table; width: 100%;'>";
			$slide_html .= "<div class='well' style='margin-top: 20px;'>";
				$slide_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$slide_html .= "<div style='padding: 5px 20px 5px 20px;'>";
						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Index page slider configuration</div>";
					$slide_html .= "</div>";

					$slide_html .= "<div style='padding: 5px 20px 20px 20px;'>";
						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Trending Slider</div>";

						// Trending Slider
						$slide_html .= "<div style='padding-top: 5px;'>";
							$slide_html .= "<div class=\"input-group\">";
							$slide_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Trending Slider</div></span>";
								$slide_html .= "<select id='fs-slider-trending' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_DISPLAY_TRENDING_SLIDER == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$slide_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_DISPLAY_TRENDING_SLIDER == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$slide_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$slide_html .= "</select>";
							$slide_html .= "</div>";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Trending text</div></span>";
							$slide_html .= "<input id=\"fs-slider-trending-text\" type=\"text\" class=\"form-control\" placeholder=\"Trending deal slider header text\" value=\"" . htmlspecialchars(base64_decode(FLUID_TRENDING_SLIDER_MESSAGE_HEADER)) . "\">";
						$slide_html .= "</div>";


						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Deal Slider</div>";
						// Deal slider enabled
						$slide_html .= "<div style='padding-top: 5px;'>";
							$slide_html .= "<div class=\"input-group\">";
							$slide_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Deal Slider</div></span>";
								$slide_html .= "<select id='fs-slider-deal' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_DISPLAY_DEAL_SLIDER == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$slide_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_DISPLAY_DEAL_SLIDER == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$slide_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled.</option>";

								$slide_html .= "</select>";
							$slide_html .= "</div>";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Deal text</div></span>";
							$slide_html .= "<input id=\"fs-slider-deal-text\" type=\"text\" class=\"form-control\" placeholder=\"Deal header text\" value=\"" . htmlspecialchars(base64_decode(FLUID_DEAL_SLIDER_MESSAGE_HEADER)) . "\">";
						$slide_html .= "</div>";


						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Deal button</div></span>";
							$slide_html .= "<input id=\"fs-slider-deal-button\" type=\"text\" class=\"form-control\" placeholder=\"Deal button html\" value=\"" . htmlspecialchars(base64_decode(FLUID_DEAL_BUTTON)) . "\">";
						$slide_html .= "</div>";

						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Formula Slider</div>";
						// Formula slider enabled
						$slide_html .= "<div style='padding-top: 5px;'>";
							$slide_html .= "<div class=\"input-group\">";
							$slide_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Formula Slider</div></span>";
								$slide_html .= "<select id='fs-slider-formula' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_DISPLAY_FORMULA_DEAL_SLIDER == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$slide_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_DISPLAY_FORMULA_DEAL_SLIDER == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$slide_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled.</option>";

								$slide_html .= "</select>";
							$slide_html .= "</div>";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Formula text</div></span>";
							$slide_html .= "<input id=\"fs-slider-formula-text\" type=\"text\" class=\"form-control\" placeholder=\"Formula bundle header text\" value=\"" . htmlspecialchars(base64_decode(FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER)) . "\">";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Formula button</div></span>";
							$slide_html .= "<input id=\"fs-slider-formula-button\" type=\"text\" class=\"form-control\" placeholder=\"Formula button html\" value=\"" . htmlspecialchars(base64_decode(FLUID_FORMULA_BUTTON)) . "\">";
						$slide_html .= "</div>";

						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Black Friday Slider</div>";
						// Black friday slider enabled
						$slide_html .= "<div style='padding-top: 5px;'>";
							$slide_html .= "<div class=\"input-group\">";
							$slide_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Black Friday</div></span>";
								$slide_html .= "<select id='fs-slider-blackfriday' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_BLACK_FRIDAY == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$slide_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_BLACK_FRIDAY == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$slide_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$slide_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled.</option>";

								$slide_html .= "</select>";
							$slide_html .= "</div>";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Black Friday text</div></span>";
							$slide_html .= "<input id=\"fs-slider-blackfriday-text\" type=\"text\" class=\"form-control\" placeholder=\"Black Friday slider header text\" value=\"" . htmlspecialchars(base64_decode(FLUID_BLACK_FRIDAY_MESSAGE_HEADER)) . "\">";
						$slide_html .= "</div>";

						$slide_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$slide_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Black Friday button</div></span>";
							$slide_html .= "<input id=\"fs-slider-blackfriday-button\" type=\"text\" class=\"form-control\" placeholder=\"Black Friday Button html\" value=\"" . htmlspecialchars(base64_decode(FLUID_BLACK_FRIDAY_BUTTON)) . "\">";
						$slide_html .= "</div>";
					$slide_html .= "</div>";

					$slide_html .= "<div style='padding: 5px 20px 20px 20px;'>";
						$slide_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Categories Box Position</div>";

						// Categories position
						$slide_html .= "<div style='padding-top: 5px;'>";
							$slide_html .= "<div class=\"input-group\">";
							$slide_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:140px !important;'>Category Box Position</div></span>";
								$slide_html .= "<select id='fs-category-position' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_CATEGORIES_POSITION == "TOP") {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$slide_html .= "<option " . $selected . " value='TOP'>Top of page.</option>";

									if(FLUID_CATEGORIES_POSITION == "BELOW_TRENDING") {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$slide_html .= "<option " . $selected . " value='BELOW_TRENDING'>Below Trending Slider</option>";

									if(FLUID_CATEGORIES_POSITION == "BELOW_DEAL") {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$slide_html .= "<option " . $selected . " value='BELOW_DEAL'>Below Deal Slider</option>";

									if(FLUID_CATEGORIES_POSITION == "BELOW_FORMULA") {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$slide_html .= "<option " . $selected . " value='BELOW_FORMULA'>Below Formula Slider</option>";

									if(FLUID_CATEGORIES_POSITION == "BELOW_BLACKFRIDAY") {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$slide_html .= "<option " . $selected . " value='BELOW_BLACKFRIDAY'>Below Black Friday Slider</option>";

								$slide_html .= "</select>";
							$slide_html .= "</div>";
							$slide_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This sets the position of the category data on the index home page.</div>";

						$slide_html .= "</div>";
					$slide_html .= "</div>";

				$slide_html .= "</div>";
			$slide_html .= "</div>";
		$slide_html .= "</div>";

		// Lets build the payment settings html data now.
		$p_html = "<div style='display: table; width: 100%;'>";
			$p_html .= "<div class='well' style='margin-top: 20px;'>";
				$p_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$p_html .= "<div style='padding: 5px 20px 20px 20px;'>";

						$p_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Set the sandbox to Yes for testing the payment settings in the checkout in sandbox mode by using the test payment servers.</div>";
						// Checkout sandbox
						$p_html .= "<div style='padding-top: 5px;'>";
							$p_html .= "<div class=\"input-group\">";
							$p_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Checkout Sandbox</div></span>";
								$p_html .= "<select id='fs-checkout-sandbox' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_PAYMENT_SANDBOX == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$p_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FLUID_PAYMENT_SANDBOX == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$p_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$p_html .= "</select>";
							$p_html .= "</div>";
						$p_html .= "</div>";

						$p_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 20px; padding-bottom: 10px;'>Moneris Settings</div>";

						// Moneris Enabled
						$p_html .= "<div style='padding-top: 5px;'>";
							$p_html .= "<div class=\"input-group\">";
							$p_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Moneris</div></span>";
								$p_html .= "<select id='fs-moneris' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(MONERIS_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$p_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(MONERIS_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$p_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$p_html .= "</select>";
							$p_html .= "</div>";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Api Key</div></span>";
							$p_html .= "<input id=\"f-moneris-api-key\" type=\"text\" class=\"form-control\" placeholder=\"Moneris api key.\" value=\"" . MONERIS_API_KEY . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Store id</div></span>";
							$p_html .= "<input id=\"f-moneris-store-id\" type=\"text\" class=\"form-control\" placeholder=\"Moneris store id.\" value=\"" . MONERIS_STORE_ID . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Api Key (sandbox)</div></span>";
							$p_html .= "<input id=\"f-moneris-api-key-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"Moneris api key (sandbox).\" value=\"" . MONERIS_API_KEY_SANDBOX . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Store id (sandbox)</div></span>";
							$p_html .= "<input id=\"f-moneris-store-id-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"Moneris store id (sandbox).\" value=\"" . MONERIS_STORE_ID_SANDBOX . "\">";
						$p_html .= "</div>";


						$p_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Authorize.net Settings</div>";
						// Authorise.net
						$p_html .= "<div style='padding-top: 5px;'>";
							$p_html .= "<div class=\"input-group\">";
							$p_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Authorize.net</div></span>";
								$p_html .= "<select id='fs-authorize' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(AUTH_NET_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$p_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(AUTH_NET_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$p_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$p_html .= "</select>";
							$p_html .= "</div>";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Login ID</div></span>";
							$p_html .= "<input id=\"f-authorize-login-id\" type=\"text\" class=\"form-control\" placeholder=\"Authorize.net login id.\" value=\"" . AUTH_NET_LOGIN_ID . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>API Key</div></span>";
							$p_html .= "<input id=\"f-authorize-api-key\" type=\"text\" class=\"form-control\" placeholder=\"Authorize.net api key.\" value=\"" . AUTH_NET_API_KEY . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Login ID (sandbox)</div></span>";
							$p_html .= "<input id=\"f-authorize-login-id-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"Authorize.net login id (sandbox).\" value=\"" . AUTH_NET_SANDBOX_LOGIN_ID . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>API Key (sandbox)</div></span>";
							$p_html .= "<input id=\"f-authorize-api-key-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"Authorize.net api key (sandbox).\" value=\"" . AUTH_NET_SANDBOX_API_KEY . "\">";
						$p_html .= "</div>";


						$p_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'><i class=\"fa fa-paypal\" aria-hidden=\"true\"></i> PayPal Settings</div>";
						// PayPal enabled
						$p_html .= "<div style='padding-top: 5px;'>";
							$p_html .= "<div class=\"input-group\">";
							$p_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>PayPal</div></span>";
								$p_html .= "<select id='fs-paypal' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(PAYPAL_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$p_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(PAYPAL_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$p_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$p_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled.</option>";

								$p_html .= "</select>";
							$p_html .= "</div>";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Client ID</div></span>";
							$p_html .= "<input id=\"f-paypal-client-id\" type=\"text\" class=\"form-control\" placeholder=\"PayPal client id.\" value=\"" . PAYPAL_CLIENT_ID . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Secret</div></span>";
							$p_html .= "<input id=\"f-paypal-secret\" type=\"text\" class=\"form-control\" placeholder=\"PayPal secret.\" value=\"" . PAYPAL_SECRET . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Client ID (sandbox)</div></span>";
							$p_html .= "<input id=\"f-paypal-client-id-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"PayPal client id (sandbox).\" value=\"" . PAYPAL_CLIENT_ID_SANDBOX . "\">";
						$p_html .= "</div>";

						$p_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$p_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Secret (sandbox)</div></span>";
							$p_html .= "<input id=\"f-paypal-secret-sandbox\" type=\"text\" class=\"form-control\" placeholder=\"PayPal secret (sandbox).\" value=\"" . PAYPAL_SECRET_SANDBOX . "\">";
						$p_html .= "</div>";

					$p_html .= "</div>";
				$p_html .= "</div>";
			$p_html .= "</div>";
		$p_html .= "</div>";

		// Lets build the shipping settings html data now.
		$sp_html = "<div style='display: table; width: 100%;'>";
			$sp_html .= "<div class='well' style='margin-top: 20px;'>";
				$sp_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$sp_html .= "<div style='padding: 5px 20px 20px 20px;'>";

						$sp_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 5px; padding-bottom: 10px;'>Adjust and set various shipping options and limits.</div>";

							// Free shipping formula
							$sp_html .= "<div class=\"input-group\">";
								$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Free shipping</div></span>";
								$sp_html .= "<select id='fs-free-shipping' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FREE_SHIPPING_FORMULA_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FREE_SHIPPING_FORMULA_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";

							// --> Free oversized shipping?
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 82%;'>Free oversized shipping</div></span>";
								$sp_html .= "<select id='fs-free-shipping-oversized' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FREE_SHIPPING_OVERSIZED_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FREE_SHIPPING_OVERSIZED_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Oversized items are any items that do not fit in the pre-defined Shipping Boxes which are set under the Shipping Boxes tab.</div>";

							// --> Free shipping for special order items?
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 72%;'>Special order free shipping</div></span>";
								$sp_html .= "<select id='fs-free-shipping-special' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FREE_SHIPPING_SPECIAL_ENABLED == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FREE_SHIPPING_SPECIAL_ENABLED == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Allow special order items to qualify for free shipping if the item does not have enough quantity in stock?</div>";

							// --> Free shipping for items with not enough stock?
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 62%;'>Free shipping not enough stock?</div></span>";
								$sp_html .= "<select id='fs-free-shipping-stock' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FREE_SHIPPING_NOT_ENOUGH_STOCK == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FREE_SHIPPING_NOT_ENOUGH_STOCK == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Allow free shipping on items if the quantity order does not have enough quantity in stock?</div>";

							// --> Split shipments.
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Split shipments</div></span>";
								$sp_html .= "<select id='fs-split-shipping' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_SPLIT_SHIPPING == TRUE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FLUID_SPLIT_SHIPPING == FALSE) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Allow customers the option to split shipments? This happens when some items may be ready to ship and other items may not be in stock.</div>";

							// --> Ship to non billing address?
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 75%;'>Ship to non billing address</div></span>";
								$sp_html .= "<select id='fs-shipping-non-billing' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_SHIP_NON_BILLING == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes</option>";

									if(FLUID_SHIP_NON_BILLING == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Allow shipping to non billing addresses? When set to yes, the billing address option becomes available in the credit card payment section of the checkout. If set to no, this option is hidden and the text on the shipping card will also mention the billing address.</div>";

							// --> Canadian provinces / territories free shipping exclusions.
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 75%;'>Free shipping exclusions</div></span>";
								$sp_html .= "<select id='fs-shipping-provinces-exclusion' class=\"form-control selectpicker show-menu-arrow show-tick\" multiple data-selected-text-format=\"count > 2\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

										$selected = NULL;
										$p_list = explode(";", base64_decode(FLUID_PROVINCES_EXCLUSIONS));

										$p_array = Array("AB" => NULL, "BC" => NULL, "MB" => NULL, "NB" => NULL, "NL" => NULL, "NS" => NULL, "ON" => NULL, "PE" => NULL, "QC" => NULL, "SK" => NULL, "NT,NU" => NULL, "YT" => NULL);

										foreach($p_list as $p_data) {
											$p_array[$p_data] = " selected";
										}

										$sp_html .= "<option value=\"AB\"" . $p_array['AB'] . ">Alberta</option>";
										$sp_html .= "<option value=\"BC\"" . $p_array['BC'] . ">British Columbia</option>";
										$sp_html .= "<option value=\"MB\"" . $p_array['MB'] . ">Manitoba</option>";
										$sp_html .= "<option value=\"NB\"" . $p_array['NB'] . ">New Brunswick</option>";
										$sp_html .= "<option value=\"NL\"" . $p_array['NL'] . ">Newfoundland and Labrador</option>";
										$sp_html .= "<option value=\"NS\"" . $p_array['NS'] . ">Nova Scotia</option>";
										$sp_html .= "<option value=\"ON\"" . $p_array['ON'] . ">Ontario</option>";
										$sp_html .= "<option value=\"PE\"" . $p_array['PE'] . ">Prince Edward Island</option>";
										$sp_html .= "<option value=\"QC\"" . $p_array['QC'] . ">Quebec</option>";
										$sp_html .= "<option value=\"SK\"" . $p_array['SK'] . ">Saskatchewan</option>";
										$sp_html .= "<option value=\"NT,NU\"" . $p_array['NT,NU'] . ">Northwest Territories and Nunavut</option>";
										$sp_html .= "<option value=\"YT\"" . $p_array['YT'] . ">Yukon</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
							$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Select which Canadian provinces or territories to exclude it from free shipping.</div>";

							$sp_html .= "<div style='padding-top: 10px; padding-left: 3px; font-size: 80%; font-weight: 600;'>Free shipping formula status. Enable the free shipping formula in the checkout. Note: The step values work in sequence of there order. Example: Step 1 is compared and checked first, then followed by Step 2 and so on. If step 1 value is $100 and margin at 40%, it means any order with $100 value or less with a 40% margin will be offered free shipping. Finally with Step 5, any value over the $ value and margin % will automatically be free shipping</div>";
							// Step 1
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 1 Margin %</div></span>";
								$sp_html .= "<select id='fs-margin-1' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"100\">";
									for($i = 0; $i <= 100; $i++) {
										if(FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . $i . "%</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 1 Value " . HTML_CURRENCY . "</div></span>";
								$sp_html .= "<select id='fs-value-1' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"2000\">";
									for($i = 0; $i <= 10000; $i = $i+5) {
										// Check if the number is even.
										if($i % 2 == 0) {
											$fv = $i - 1;
											if($fv > 0) {
												if(FREE_SHIPPING_CART_TOTAL_STEP_1 == $fv) {
													$selected = "selected";
												}
												else {
													$selected = NULL;
												}

												$sp_html .= "<option " . $selected . " value='" . $fv . "'>" . HTML_CURRENCY . $fv . "</option>";
											}
										}

										if(FREE_SHIPPING_CART_TOTAL_STEP_1 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . HTML_CURRENCY . $i . "</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							// Step 2
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 2 Margin %</div></span>";
								$sp_html .= "<select id='fs-margin-2' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"' virtualScroll=\"100\">";
									for($i = 0; $i <= 100; $i++) {
										if(FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . $i . "%</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 2 Value " . HTML_CURRENCY . "</div></span>";
								$sp_html .= "<select id='fs-value-2' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"2000\">";
									for($i = 0; $i <= 10000; $i = $i+5) {
										if($i % 2 == 0) {
											$fv = $i - 1;
											if($fv > 0) {
												if(FREE_SHIPPING_CART_TOTAL_STEP_2 == $fv) {
													$selected = "selected";
												}
												else {
													$selected = NULL;
												}

												$sp_html .= "<option " . $selected . " value='" . $fv . "'>" . HTML_CURRENCY . $fv . "</option>";
											}
										}

										if(FREE_SHIPPING_CART_TOTAL_STEP_2 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . HTML_CURRENCY . $i . "</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							// Step 3
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 3 Margin %</div></span>";
								$sp_html .= "<select id='fs-margin-3' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"100\">";
									for($i = 0; $i <= 100; $i++) {
										if(FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . $i . "%</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 3 Value " . HTML_CURRENCY . "</div></span>";
								$sp_html .= "<select id='fs-value-3' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"2000\">";
									for($i = 0; $i <= 10000; $i = $i+5) {
										if($i % 2 == 0) {
											$fv = $i - 1;
											if($fv > 0) {
												if(FREE_SHIPPING_CART_TOTAL_STEP_3 == $fv) {
													$selected = "selected";
												}
												else {
													$selected = NULL;
												}

												$sp_html .= "<option " . $selected . " value='" . $fv . "'>" . HTML_CURRENCY . $fv . "</option>";
											}
										}

										if(FREE_SHIPPING_CART_TOTAL_STEP_3 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . HTML_CURRENCY . $i . "</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							// Step 4
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 4 Margin %</div></span>";
								$sp_html .= "<select id='fs-margin-4' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"100\">";
									for($i = 0; $i <= 100; $i++) {
										if(FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . $i . "%</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 4 Value " . HTML_CURRENCY . "</div></span>";
								$sp_html .= "<select id='fs-value-4' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"2000\">";
									for($i = 0; $i <= 10000; $i = $i+5) {
										if($i % 2 == 0) {
											$fv = $i - 1;
											if($fv > 0) {
												if(FREE_SHIPPING_CART_TOTAL_STEP_4 == $fv) {
													$selected = "selected";
												}
												else {
													$selected = NULL;
												}

												$sp_html .= "<option " . $selected . " value='" . $fv . "'>" . HTML_CURRENCY . $fv . "</option>";
											}
										}

										if(FREE_SHIPPING_CART_TOTAL_STEP_4 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . HTML_CURRENCY . $i . "</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							// Step 5
							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 5 Margin %</div></span>";
								$sp_html .= "<select id='fs-margin-5' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"100\">";
									for($i = 0; $i <= 100; $i++) {
										if(FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . $i . "%</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

							$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Step 5 Value " . HTML_CURRENCY . "</div></span>";
								$sp_html .= "<select id='fs-value-5' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" virtualScroll=\"2000\">";
									for($i = 0; $i <= 10000; $i = $i+5) {
										if($i % 2 == 0) {
											$fv = $i - 1;
											if($fv > 0) {
												if(FREE_SHIPPING_CART_TOTAL_STEP_5 == $fv) {
													$selected = "selected";
												}
												else {
													$selected = NULL;
												}

												$sp_html .= "<option " . $selected . " value='" . $fv . "'>" . HTML_CURRENCY . $fv . "</option>";
											}
										}

										if(FREE_SHIPPING_CART_TOTAL_STEP_5 == $i) {
											$selected = "selected";
										}
										else {
											$selected = NULL;
										}

										$sp_html .= "<option " . $selected . " value='" . $i . "'>" . HTML_CURRENCY . $i . "</option>";
									}
								$sp_html .= "</select>";
							$sp_html .= "</div>";

						$sp_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>In Store Pickup status</div>";
						// In store pickup
						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>In store pickup</div></span>";
								$sp_html .= "<select id='fs-instore-pickup' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(ENABLE_IN_STORE_PICKUP == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(ENABLE_IN_STORE_PICKUP == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";

						$sp_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>In Store Pickup Payment</div>";
						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Payment on pickup</div></span>";
								$sp_html .= "<select id='fs-instore-pickup-payment' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(ENABLE_IN_STORE_PICKUP_PAYMENT == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";
						$sp_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> When enabled, customers can place orders without paying only when the In Store Pickup option is selected. Customers will need to pay for the order when they arrive in store to pickup the order.</div>";


						$sp_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Store postal code. Required for Canada Post and Fedex shipping api.</div>";
						// Store postal code
						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Store Postal Code</div></span>";
							$sp_html .= "<input id=\"fs-postal-code\" type=\"text\" class=\"form-control\" placeholder=\"Example: V6Z1B4 closed.\" value=\"" . FLUID_ORIGIN_POSTAL_CODE . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div style='padding-top: 20px; font-weight: 600; font-style: italic; padding-bottom: 10px;'>Canada Post Settings</div>";

						// Canada Post Enabled
						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:140px !important;'>Canada Post</div></span>";
								$sp_html .= "<select id='fs-canadapost' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(ENABLE_CANADAPOST == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(ENABLE_CANADAPOST == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:140px !important;'>User name</div></span>";
							$sp_html .= "<input id=\"fs-canadapost-username\" type=\"text\" class=\"form-control\" placeholder=\"Canada Post username.\" value=\"" . CANADA_POST_USERNAME . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:140px !important;'>Password</div></span>";
							$sp_html .= "<input id=\"fs-canadapost-password\" type=\"text\" class=\"form-control\" placeholder=\"Canada Post password.\" value=\"" . CANADA_POST_PASSWORD . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:140px !important;'>Customer number</div></span>";
							$sp_html .= "<input id=\"fs-canadapost-customer-number\" type=\"text\" class=\"form-control\" placeholder=\"Canada Post Customer Number.\" value=\"" . CANADA_POST_CUSTOMER_NUMBER. "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:140px !important;'>Canada Post signature</div></span>";
								$sp_html .= "<select id='fs-canadapost-signature' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(CANADA_POST_SIGNATURE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Signature required\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Signature required</option>";

									if(CANADA_POST_SIGNATURE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Signature not required\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Signature not required</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";


						$sp_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>FedEx Settings</div>";
						// FedEx settings.
						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>FedEx</div></span>";
								$sp_html .= "<select id='fs-fedex' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(ENABLE_FEDEX == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(ENABLE_FEDEX == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled.</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Account #</div></span>";
							$sp_html .= "<input id=\"fs-fedex-account\" type=\"text\" class=\"form-control\" placeholder=\"FedEx account number.\" value=\"" . FEDEX_ACCOUNT . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Meter #</div></span>";
							$sp_html .= "<input id=\"fs-fedex-meter\" type=\"text\" class=\"form-control\" placeholder=\"FedEx meter number.\" value=\"" . FEDEX_METER . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Key</div></span>";
							$sp_html .= "<input id=\"fs-fedex-key\" type=\"text\" class=\"form-control\" placeholder=\"FedEx key.\" value=\"" . FEDEX_KEY . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Password</div></span>";
							$sp_html .= "<input id=\"fs-fedex-password\" type=\"text\" class=\"form-control\" placeholder=\"FedEx password.\" value=\"" . FEDEX_PASSWORD . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Person Name</div></span>";
							$sp_html .= "<input id=\"fs-fedex-person\" type=\"text\" class=\"form-control\" placeholder=\"Shipping person name.\" value=\"" . FEDEX_PERSON . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Company Name</div></span>";
							$sp_html .= "<input id=\"fs-fedex-company\" type=\"text\" class=\"form-control\" placeholder=\"Shipping company name.\" value=\"" . FEDEX_COMPANY . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Phone #</div></span>";
							$sp_html .= "<input id=\"fs-fedex-phone\" type=\"text\" class=\"form-control\" placeholder=\"Shipping phone number.\" value=\"" . FEDEX_PHONE . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Street</div></span>";
							$sp_html .= "<input id=\"fs-fedex-street\" type=\"text\" class=\"form-control\" placeholder=\"Shipping street address.\" value=\"" . FEDEX_STREET . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>City</div></span>";
							$sp_html .= "<input id=\"fs-fedex-city\" type=\"text\" class=\"form-control\" placeholder=\"Shipping city.\" value=\"" . FEDEX_CITY . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Province</div></span>";
							$sp_html .= "<input id=\"fs-fedex-province\" type=\"text\" class=\"form-control\" placeholder=\"Shipping city.\" value=\"" . FEDEX_PROVINCE . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Postal Code</div></span>";
							$sp_html .= "<input id=\"fs-fedex-postalcode\" type=\"text\" class=\"form-control\" placeholder=\"Shipping postal code.\" value=\"" . FEDEX_POSTAL_CODE . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$sp_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Country</div></span>";
							$sp_html .= "<input id=\"fs-fedex-country\" type=\"text\" class=\"form-control\" placeholder=\"Shipping country.\" value=\"" . FEDEX_COUNTRY_CODE . "\">";
						$sp_html .= "</div>";

						$sp_html .= "<div style='padding-top: 5px;'>";
							$sp_html .= "<div class=\"input-group\">";
							$sp_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>FedEx signature</div></span>";
								$sp_html .= "<select id='fs-fedex-signature' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FEDEX_SIGNATURE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Signature required\"";
									$sp_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Signature required</option>";

									if(FEDEX_SIGNATURE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$sp_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Signature not required\"";
									$sp_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Signature not required</option>";

								$sp_html .= "</select>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";

					$sp_html .= "</div>";
				$sp_html .= "</div>";
			$sp_html .= "</div>";
		$sp_html .= "</div>";

		// Lets build the store status html data now.
		$s_html = "<div style='display: table; width: 100%;'>";
			$s_html .= "<div class='well' style='margin-top: 20px;'>";
				$s_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$s_html .= "<div style='padding: 5px 20px 20px 20px;'>";
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Store status</div>";

						// Store status.
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Store status</div></span>";
								$s_html .= "<select id='fs-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_STORE_OPEN == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Open\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Open</option>";

									if(FLUID_STORE_OPEN == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Closed\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Closed</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Closed message</div></span>";
							$s_html .= "<input id=\"f-closed-message\" type=\"text\" class=\"form-control\" placeholder=\"Message is displayed at top of site when store is closed.\" value=\"" . base64_decode(FLUID_STORE_CLOSED_MESSAGE) . "\">";
							$s_html .= "<input id=\"f-closed-message-old\" type=\"hidden\" class=\"form-control\" placeholder=\"Message is displayed at top of site when store is closed.\" value=\"" . FLUID_STORE_CLOSED_MESSAGE . "\">";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> The closed message will be displayed at the top of every page in the header. The checkout will be disabled.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Store message</div>";
						// Store Message.
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Store message</div></span>";
								$s_html .= "<select id='fs-store-message-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_STORE_MESSAGE_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_STORE_MESSAGE_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Store message</div></span>";
							$s_html .= "<input id=\"fs-store-message\" type=\"text\" class=\"form-control\" placeholder=\"Message is displayed at top of site when enabled.\" value=\"" . base64_decode(FLUID_STORE_MESSAGE) . "\">";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> The store message will be displayed at the top of every page in the header except in the checkout.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Store message modal</div>";
						// Store Modal.
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Store Modal</div></span>";
								$s_html .= "<select id='fs-store-message-enabled-modal' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_STORE_MESSAGE_MODAL_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_STORE_MESSAGE_MODAL_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Store message</div></span>";
							$s_html .= "<input id=\"fs-store-message-modal\" type=\"text\" class=\"form-control\" placeholder=\"This html message is displayed in a popup window when people visit the website. You can use HTML code in here for more advanced messages. Use the File uploader in the banner section to upload images.\" value=\"" . base64_decode(FLUID_STORE_MESSAGE_MODAL) . "\">";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> The store modal message will be displayed when somebody visits the website. This html message is displayed in a popup window when people visit the website. You can use HTML code in here for more advanced messages. Use the File uploader in the banner section to upload images.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Store slogan. The slogan appears under the logo in the header.</div>";
						// Store slogan.
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Enable slogan</div></span>";
								$s_html .= "<select id='fs-slogan-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_SLOGAN_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_SLOGAN_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Slogan</div></span>";
							$s_html .= "<input id=\"fs-slogan\" type=\"text\" class=\"form-control\" placeholder=\"Slogan to display under the logo in the header.\" value=\"" . FLUID_SLOGAN . "\">";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Show only in stock items (plus new arrivals and discounted items)</div>";

						// Item listing options.
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Item display</div></span>";
								$s_html .= "<select id='fs-item-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 1) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes, show in stock only\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Yes, show in stock items only.</option>";

									if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 0) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No, show all\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No, show all items.</option>";

									if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 2) {
										$selected = "selected";
									}
									else {
										$selected = NULL;
									}

									$s_html .= "<option " . $selected . " value='2' data-content=\"<span style='color: blue;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No, show all (except discontinued zero stock)\"";
									$s_html .= "><span style='color: blue;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> No, show all items (except discontinued zero stock).</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Sitemap generator. Generates a sitemap.xml into the root website folder.</div>";
						// Generate sitemap
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\" style='border-right: 1px solid #ccc; border-radius: 4px;'><div style='width:120px !important;'>Sitemap generator</div></span>";
							$s_html .= "<div style='margin-left: 10px;'><button class='btn btn-primary' onClick='js_fluid_sitemap_generate();'>Sitemap Generator</button><div id='f-sitemap-area' style='margin-left: 10px; display: inline-block;'></div></div>";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Live Search Cache. Rebuilds the live search suggestions database cache.</div>";
						$s_html .= "<div style='font-style: italic; color: red;'>* Please note, this can take a very long time to process (10 or more minutes). Please wait until it finishes.</div>";
						// Rebuild live search cache.
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\" style='border-right: 1px solid #ccc; border-radius: 4px;'><div style='width:120px !important;'>Live Search Cache</div></span>";
							$s_html .= "<div style='margin-left: 10px;'><button class='btn btn-primary' onClick='js_fluid_rebuild_livesearch_cache();'>Rebuild Cache</button><div id='f-livesearchcache-area' style='margin-left: 10px; display: inline-block;'></div></div>";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Password hash. A randomised key for encrypting user passwords. Note: Changing this will force all users to reset there passwords.</div>";
						// Password Hash Key
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Password hash</div></span>";
							$s_html .= "<input id=\"fs-hash-key\" type=\"text\" class=\"form-control\" placeholder=\"Example: 2Kfjkl349jkKJ3flA3fsd\" value=\"" . HASH_KEY . "\">";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Currency. Set the store displayed currency and symbols. Currency code is set by the font awesome icon classes --> For the font awesome icon, refer to http://fontawesome.io/icons/ for more information.</div>";
						// Currency key
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Currency key</div></span>";
							$s_html .= "<input id=\"fs-currency-key\" type=\"text\" class=\"form-control\" placeholder=\"Example: " . HTML_CURRENCY . "\" value=\"" . HTML_CURRENCY . "\">";
						$s_html .= "</div>";
						// Currency icon
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Currency icon <i class='" . HTML_CURRENCY_GLYPHICON . "' aria-hidden='true'></i></div></span>";
							$s_html .= "<input id=\"fs-currency-icon\" type=\"text\" class=\"form-control\" placeholder=\"Example: fa fa-usd\" value=\"" . HTML_CURRENCY_GLYPHICON . "\">";
						$s_html .= "</div>";
						// Currency code
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Currency code</div></span>";
							$s_html .= "<input id=\"fs-currency-code\" type=\"text\" class=\"form-control\" placeholder=\"Example: CAD\" value=\"" . STORE_CURRENCY . "\">";
						$s_html .= "</div>";

						// Banner control
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Banner settings: Enable or disable the banner system?</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Banner system</div></span>";
								$s_html .= "<select id='fs-banners-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_BANNERS_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_BANNERS_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This will hide the store banner system.</div>";

						// Category control
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Category settings: Enable or disable the categories from displaying on the index page?</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Category system</div></span>";
								$s_html .= "<select id='fs-categories-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_CATEGORIES_ENABLED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_CATEGORIES_ENABLED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This will hide the categories from displaying on the index page, however categories will still show on the navbar.</div>";

						// Item listing style
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Infinite Scrolling: Enable or disable the infinite scrolling on the item listing page?</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Infinite Scrolling</div></span>";
								$s_html .= "<select id='fs-infinite-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_LISTING_INFINITE_SCROLLING == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_LISTING_INFINITE_SCROLLING == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> When disabled, item listing reverts to pagination style listings.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Max listings: The number of item listings to show on the item listing pages and for the autoloader loader to load at a time.</div>";
						// Max listings
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Max listings</div></span>";
							$s_html .= "<input id=\"fs-max-listings\" type=\"number\" class=\"form-control\" placeholder=\"Example: 30\" value=\"" . VAR_LISTING_MAX . "\">";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Search relevance: A multiplier which controls the sensitvity of the site search results.</div>";
						// Search relevance
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Search Relevance</div></span>";
							$s_html .= "<input id=\"fs-search-relevance\" type=\"number\" class=\"form-control\" placeholder=\"Example: 2.0\" value=\"" . number_format(FLUID_SEARCH_RELEVANCE, 1, '.', '') . "\">";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Controls the sensitivity of the site search results. Between 1.5 and 2.0 is a good number to start with.</div>";

						// Item filter options pin
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Filter pin: Pin the filter options to the top while scrolling in the item listing?</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Filter pin</div></span>";
								$s_html .= "<select id='fs-filters-pinned' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_LISTING_FILTERS_PINNED == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_LISTING_FILTERS_PINNED == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						// Item filter options pin mobile
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Mobile Filter pin: Pin the filter options to the top while scrolling in the item listing for mobile devices?</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Filter pin mobile</div></span>";
								$s_html .= "<select id='fs-filters-pinned-mobile' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_LISTING_FILTERS_PINNED_MOBILE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_LISTING_FILTERS_PINNED_MOBILE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						// Pre-orders
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Allow Preorders: Allow people to purchase preorder items.</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Allow preorders</div></span>";
								$s_html .= "<select id='fs-preorders' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_PREORDER == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_PREORDER == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This will override the purchase out of stock items settings.</div>";

						// Purchase out of stock items
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Allow purchase out of stock items: Allow people to purchase items that are not in stock.</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 90%;'>Purchase out of stock</div></span>";
								$s_html .= "<select id='fs-purchase-out-of-stock' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This will not affect the preorders or special order items.</div>";

						// Hide deal timers.
						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Hide the timer which shows when a deal ends.</div>";
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 90%;'>Hide deal timer</div></span>";
								$s_html .= "<select id='fs-deal-timer' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_SAVINGS_TIMER_HIDE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Hidden\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Hidden</option>";

									if(FLUID_SAVINGS_TIMER_HIDE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Shown\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Shown</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This will hide the countdown timer for when a deal ends on a item.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Feedback System.</div>";
						// Feedback
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Feedback</div></span>";
								$s_html .= "<select id='fs-feedback-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_FEEDBACK_ENABLE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(FLUID_FEEDBACK_ENABLE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Feedback Timer</div></span>";
							$s_html .= "<input id=\"fs-feedback-timer\" type=\"text\" class=\"form-control\" placeholder=\"Ex: 60000\" value=\"" . FLUID_FEEDBACK_TIMER_LENGTH . "\">";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Feedback timer in milliseconds. 60000 = 60 seconds. After this amount of inactivty, the feedback modal will appear to users.</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Additional Savings Merge.</div>";
						//
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 80%;'>Additional Savings Merge</div></span>";
								$s_html .= "<select id='fs-additional-savings-merge' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Merged\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Merged</option>";

									if(FLUID_ADDITIONAL_SAVINGS_MERGE == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Not Merged\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Not Merged</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";
						$s_html .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Merge the additional savings into the instant savings? Or un-merge into it's own additional savings display on item listings and item pages?</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Max item images: Max number of images to show for a item on the item page. Additional images will be ignored. Set the image order in the multi item editor.</div>";
						// Max images
						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Max images</div></span>";
							$s_html .= "<input id=\"fs-max-images\" type=\"number\" class=\"form-control\" placeholder=\"Example: 5\" value=\"" . FLUID_ITEM_PAGE_MAX_IMAGES . "\">";
						$s_html .= "</div>";

						$s_html .= "<div style='font-weight: 600; font-style: italic; padding-top: 25px; padding-bottom: 10px;'>Enable debugging.</div>";
						// Debugging
						$s_html .= "<div style='padding-top: 5px;'>";
							$s_html .= "<div class=\"input-group\">";
							$s_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Debugging</div></span>";
								$s_html .= "<select id='fs-debug-enabled' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

									if(ENABLE_LOG == TRUE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='1' data-content=\"<span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled\"";
									$s_html .= "><span style='color: green;' class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

									if(ENABLE_LOG == FALSE)
										$selected = "selected";
									else
										$selected = NULL;

									$s_html .= "<option " . $selected . " value='0' data-content=\"<span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled\"";
									$s_html .= "><span style='color: red;' class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> Disabled</option>";

								$s_html .= "</select>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						$s_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
							$s_html .= "<span class=\"input-group-addon\"><div style='width:120px !important;'>Log location</div></span>";
							$s_html .= "<input id=\"fs-debug-log\" type=\"text\" class=\"form-control\" placeholder=\"Ex: /var/log/fluid.debug.log.\" value=\"" . DEBUG_LOG . "\">";
						$s_html .= "</div>";

					$s_html .= "</div>";
				$s_html .= "</div>";
			$s_html .= "</div>";
		$s_html .= "</div>";

		$modal_boxes = "<div class='modal-dialog f-dialog' id='boxes-editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Shipping box creation</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div id='boxes-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; padding-left: 30px; padding-right: 30px; margin-bottom: 0px; min-height: 400px; max-height:70vh; overflow-y: scroll;'>";

					// Name
					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Name</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Box name\" aria-describedby=\"basic-addon1\" id='f-box-name'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Outer width (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Outer width (mm)\" aria-describedby=\"basic-addon1\" id='f-box-outer-width'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Outer length (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Outer length (mm)\" aria-describedby=\"basic-addon1\" id='f-box-outer-length'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Outer depth (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Outer depth (mm)\" aria-describedby=\"basic-addon1\" id='f-box-outer-depth'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Empty weight (g)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Empty weight (g)\" aria-describedby=\"basic-addon1\" id='f-box-empty-weight'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Inner width (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Inner width (mm)\" aria-describedby=\"basic-addon1\" id='f-box-inner-width'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Inner length (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Inner length (mm)\" aria-describedby=\"basic-addon1\" id='f-box-inner-length'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Inner depth (mm)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Inner depth (mm)\" aria-describedby=\"basic-addon1\" id='f-box-inner-depth'>
						</div>";

					$modal_boxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Max weight(g)</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Max weight (g)\" aria-describedby=\"basic-addon1\" id='f-box-max-weight'>
						</div>";

				$modal_boxes .= "</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal' onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_fluid_box_create(); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div></div>
			  </div>
			</div>
		  </div>";

		// Shipping boxes.
		$fluid->php_db_begin();
		$fluid->php_db_query("SELECT * FROM " . TABLE_SHIPPING_BOXES . " ORDER BY b_id ASC");

		$b_html = "<div style='display: table; width: 100%;'>";
			$b_html .= "<div style='margin-top: 20px;'>";
				$b_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$b_html .= "<div style='padding: 5px 10px 20px 10px;'>";
						$b_html .= "<div style='padding-bottom: 20px;'>";

						$b_html .= "<table id='f-ship-box-table' class='table table-hover;' style='font-size: 12px;'>";
							$b_html .= "<thead>";
							$b_html .= "<tr>";
								$b_html .= "<td class='f-align-middle-td'><button class='btn btn-primary' onClick='document.getElementById(\"fluid-modal-msg\").innerHTML = Base64.decode(\"" . base64_encode($modal_boxes) . "\"); js_clear_box_input(); js_modal_hide(\"#fluid-modal\"); js_modal_show(\"#fluid-modal-msg\");'><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span></button></td>"; // Delete button.
								$b_html .= "<td class='f-align-middle-td'>Name</td>";
								$b_html .= "<td class='f-align-middle-td'>Outer width (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Outer length (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Outer depth (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Empty weight (g)</td>";
								$b_html .= "<td class='f-align-middle-td'>Inner width (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Inner length (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Inner depth (mm)</td>";
								$b_html .= "<td class='f-align-middle-td'>Max weight (g)</td>";
								//$b_html .= "<td class='f-align-middle-td'></td>"; // Edit button.
							$b_html .= "</tr>";
							$b_html .= "</thead>";

						$f_box_data = NULL;
						$b_html .= "<tbody id='f-box-tbody'>";
						if(isset($fluid->db_array)) {
							foreach($fluid->db_array as $b_box) {
								$f_box_data[$b_box['b_id']] = $b_box;

								$b_html .= "<tr id='b-box-" . $b_box['b_id'] . "'>";
									$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Remove this shipping box?</div>";
									$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='document.getElementById(\"f-box-tbody\").removeChild(document.getElementById(\"b-box-" . $b_box['b_id'] . "\")); delete FluidVariables.b_boxes[\"" . $b_box['b_id'] . "\"]; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");
									$html_edit = "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete . $b_box['b_name']) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";

									$b_html .= "<td class='f-align-middle-td'>" . $html_edit . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_name'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_outer_width'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_outer_length'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_outer_depth'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_empty_weight'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_inner_width'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_inner_length'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_inner_depth'] . "</td>";
									$b_html .= "<td class='f-align-middle-td'>" . $b_box['b_max_weight'] . "</td>";
									//$b_html .= "<td class='f-align-middle-td'><button class='btn btn-default'><span class='glyphicon glyphicon-edit'></span></button></td>";
								$b_html .= "</tr>";

							}
							$b_html .= "</tbody>";
						}
						else {
							$b_html .= "<tr id='f-box-none'><td colspan='10'>No boxes defined</td></tr></tbody>";
						}
						$b_html .= "</table>";
						$b_html .= "</div>";
					$b_html .= "</div>";
				$b_html .= "</div>";
			$b_html .= "</div>";
		$b_html .= "</div>";

		$modal_taxes = "<div class='modal-dialog f-dialog' id='boxes-editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Tax creation</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div id='boxes-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; padding-left: 30px; padding-right: 30px; margin-bottom: 0px; min-height: 400px; max-height:70vh; overflow-y: scroll;'>";

					// Name
					$modal_taxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Name</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Example: GST\" aria-describedby=\"basic-addon1\" id='f-tax-name'>
						</div>";
					$modal_taxes .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> This name is what will show up on store receipts and on the checkout page.</div>";

					$modal_taxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Region</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Example: BC\" aria-describedby=\"basic-addon1\" id='f-tax-region'>
						</div>";
					$modal_taxes .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Use the ISO region code. For example: British Columbia would be BC, Ontario will be ON. Also note, if this is left empty and if country is left empty, the tax will apply to everybody.</div>";

					$modal_taxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Country</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Example: CA\" aria-describedby=\"basic-addon1\" id='f-tax-country'>
						</div>";
					$modal_taxes .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Leave this country and region empty if you wish for the tax to apply to everybody.</div>";

					$modal_taxes .= "<div class=\"input-group\" style='padding-top:5px;'>
						  <span class=\"input-group-addon\" ><div style='width:100px !important;'>Formula</div></span>
						  <input type=\"text\" class=\"form-control\" placeholder=\"Example: [f_item] * 0.05\" aria-describedby=\"basic-addon1\" id='f-tax-math'>
						</div>";
					$modal_taxes .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> To have the tax applied to each item, please use [f_item] in your formula. For example, if you want 5% tax applied to each item in the checkout, you enter: [f_item] * 0.05</div>";

				$modal_taxes .= "</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal' onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_fluid_tax_create(); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div></div>
			  </div>
			</div>
		  </div>";

		// Taxes.
		$fluid->php_db_begin();
		$fluid->php_db_query("SELECT * FROM " . TABLE_TAXES . " ORDER BY t_id ASC");

		$tax_html = "<div style='display: table; width: 100%;'>";
			$tax_html .= "<div style='margin-top: 20px;'>";
				$tax_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

					$tax_html .= "<div style='padding: 5px 10px 20px 10px;'>";
						$tax_html .= "<div style='padding-bottom: 20px;'>";

						$tax_html .= "<table id='f-tax-table' class='table table-hover;' style='font-size: 12px;'>";
							$tax_html .= "<thead>";
							$tax_html .= "<tr>";
								$tax_html .= "<td class='f-align-middle-td'><button class='btn btn-primary' onClick='document.getElementById(\"fluid-modal-msg\").innerHTML = Base64.decode(\"" . base64_encode($modal_taxes) . "\"); js_clear_tax_input(); js_modal_hide(\"#fluid-modal\"); js_modal_show(\"#fluid-modal-msg\");'><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span></button></td>"; // Delete button.
								$tax_html .= "<td class='f-align-middle-td'>Name</td>";
								$tax_html .= "<td class='f-align-middle-td'>Region</td>";
								$tax_html .= "<td class='f-align-middle-td'>Country</td>";
								$tax_html .= "<td class='f-align-middle-td'>Formula</td>";
							$tax_html .= "</tr>";
							$tax_html .= "</thead>";

						$f_tax_data = NULL;
						$tax_html .= "<tbody id='f-tax-tbody'>";
						if(isset($fluid->db_array)) {
							foreach($fluid->db_array as $t_tax) {
								$f_tax_data[$t_tax['t_id']] = $t_tax;

								$tax_html .= "<tr id='t-tax-" . $t_tax['t_id'] . "'>";
									$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Remove this tax?</div>";
									$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='document.getElementById(\"f-tax-tbody\").removeChild(document.getElementById(\"t-tax-" . $t_tax['t_id'] . "\")); delete FluidVariables.t_taxes[\"" . $t_tax['t_id'] . "\"]; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");
									$html_edit = "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete . $t_tax['t_name']) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";

									$tax_html .= "<td class='f-align-middle-td'>" . $html_edit . "</td>";
									$tax_html .= "<td class='f-align-middle-td'>" . $t_tax['t_name'] . "</td>";
									$tax_html .= "<td class='f-align-middle-td'>" . $t_tax['t_region'] . "</td>";
									$tax_html .= "<td class='f-align-middle-td'>" . $t_tax['t_country'] . "</td>";
									$tax_html .= "<td class='f-align-middle-td'>" . $t_tax['t_math'] . "</td>";
								$tax_html .= "</tr>";

							}
							$tax_html .= "</tbody>";
						}
						else {
							$tax_html .= "<tr id='f-tax-none'><td colspan='10'>No taxes defined</td></tr></tbody>";
						}
						$tax_html .= "</table>";
						$tax_html .= "</div>";
					$tax_html .= "</div>";
				$tax_html .= "</div>";
			$tax_html .= "</div>";
		$tax_html .= "</div>";

		$g_html = "
			<div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 20px;'>
					<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Barcode Type:</div></span>
					<select id='f_barcode_type' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\">
						<option value='C128'>TYPE_CODE_128</option>
						<option value='UPCA'>TYPE_UPC_A</option>
						<option value='UPCE'>TYPE_UPC_E</option>
						<option value='EAN13'>TYPE_EAN_13</option>
						<option value='EAN2'>TYPE_EAN_2</option>
						<option value='EAN5'>TYPE_EAN_5</option>
						<option value='EAN8'>TYPE_EAN_8</option>
						<option value='C128A'>TYPE_CODE_128_A</option>
						<option value='C128B'>TYPE_CODE_128_B</option>
						<option value='C128C'>TYPE_CODE_128_C</option>
						<option value='CODE11'>TYPE_CODE_11</option>
						<option value='C39'>TYPE_CODE_39</option>
						<option value='C39+'>TYPE_CODE_39_CHECKSUM</option>
						<option value='C39E'>TYPE_CODE_39E</option>
						<option value='C39E+'>TYPE_CODE_39E_CHECKSUM</option>
						<option value='C93'>TYPE_CODE_93</option>
						<option value='S25'>TYPE_STANDARD_2_5</option>
						<option value='S25+'>TYPE_STANDARD_2_5_CHECKSUM</option>
						<option value='I25'>TYPE_INTERLEAVED_2_5</option>
						<option value='I25+'>TYPE_INTERLEAVED_2_5_CHECKSUM</option>
						<option value='MSI'>TYPE_MSI</option>
						<option value='MSI+'>TYPE_MSI_CHECKSUM</option>
						<option value='POSTNET'>TYPE_POSTNET</option>
						<option value='PLANET>TYPE_PLANET</option>
						<option value='RMS4CC'>TYPE_RMS4CC</option>
						<option value='KIX'>TYPE_KIX</option>
						<option value='IMB'>TYPE_IMB</option>
						<option value='CODABAR'>TYPE_CODABAR</option>
						<option value='PHARMA'>TYPE_PHARMA_CODE</option>
						<option value='PHARMA2T'>TYPE_PHARMA_CODE_TWO_TRACKS</option>
					</select>
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Width Factor:</div></span>
				    <input id=\"f_barcode_width\" type=\"text\" class=\"form-control\" placeholder=\"Width Factor\" value=\"2\">
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Height (Pixels):</div></span>
				    <input id=\"f_barcode_height\" type=\"text\" class=\"form-control\" placeholder=\"Height (Pixels)\" value=\"100\">
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Text Spacing:</div></span>
				    <input id=\"f_text_spacing\" type=\"text\" class=\"form-control\" placeholder=\"Text Spacing\" value=\"6\">
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Font Size:</div></span>
				    <input id=\"f_font_size\" type=\"text\" class=\"form-control\" placeholder=\"Font Size\" value=\"14\">
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Font Weight:</div></span>
				    <input id=\"f_font_weight\" type=\"text\" class=\"form-control\" placeholder=\"Font Weight\" value=\"500\">
				</div>

				<div class=\"input-group\" style='padding-left: 20px; padding-top: 10px; padding-bottom: 30px;'>
					<span class=\"input-group-addon\"><div style='width:100px !important;'>Barcode:</div></span>
				    <input id=\"f_barcode_enter\" type=\"text\" class=\"form-control\" placeholder=\"Enter barcode\" value=\"\">
				</div>

				<div style='padding-left: 20px; display: inline-block;'><a class='btn btn-primary' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"f-barcode-area\").innerHTML;' href=\"javascript:window.print();\" name='f_barcode_print_button' id=\"f_btn_barcode_print\"><span class=\"glyphicon glyphicon-print\" aria-hidden=\"true\"></span> Print</a></div>
				<div style='padding-left: 20px; display: inline-block; float: right; padding-right: 20px;'><button class='btn btn-primary' onClick='js_barcode_generate();' id='f_btn_barcode_generate'><span class=\"glyphicon glyphicon-barcode\" aria-hidden=\"true\"></span> Generate</button></div>

				<div id='f-barcode-area' style='padding-bottom: 30px;'></div>
			</div>";

		$fluid->php_db_commit();

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Settings<div style='display: inline-block; float: right;'><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='ordertabs'>
						<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-store-status' data-target='#f-store-status' data-toggle='tab'><span class='glyphicon glyphicon-list-alt'></span> Settings</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-navbar-settings' data-target='#f-navbar-settings' data-toggle='tab'><span class='glyphicon glyphicon-object-align-vertical'></span> Navbar</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-slider-settings' data-target='#f-slider-settings' data-toggle='tab'><span class='glyphicon glyphicon-resize-horizontal'></span> Sliders</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-payment-settings' data-target='#f-payment-settings' data-toggle='tab'><span class='" . HTML_CURRENCY_GLYPHICON . "'></span> Payments</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-taxes-settings' data-target='#f-taxes-settings' data-toggle='tab'><span class='" . HTML_CURRENCY_GLYPHICON . "'></span> Taxes</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-shipping-settings' data-target='#f-shipping-settings' data-toggle='tab'><span class='fa fa-truck'></span> Shipping</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-boxes-shipping' data-target='#f-boxes-shipping' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Boxes</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-sms-settings' data-target='#f-sms-settings' data-toggle='tab'><span class='glyphicon glyphicon-phone'></span> SMS</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#f-generator' data-target='#f-generator' data-toggle='tab'><span class='glyphicon glyphicon-barcode'></span> Barcodes</a></li>
					</ul>


				<div id='order-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:70vh; overflow-y: scroll;'>
					<div id='orders-div' class='tab-content'>";

						$modal .= "<div id='f-store-status' class='tab-pane fade in active'>
							<div id='f-store-status-div' style='margin-left:10px; margin-right: 10px;'>" . $s_html . "</div>
						</div>

						<div id='f-navbar-settings' class='tab-pane fade in'>
							<div id='f-navbar-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $navbar_html . "</div>
						</div>

						<div id='f-slider-settings' class='tab-pane fade in'>
							<div id='f-slider-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $slide_html . "</div>
						</div>

						<div id='f-payment-settings' class='tab-pane fade in'>
							<div id='f-payment-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $p_html . "</div>
						</div>

						<div id='f-taxes-settings' class='tab-pane fade in'>
							<div id='f-taxes-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $tax_html . "</div>
						</div>

						<div id='f-shipping-settings' class='tab-pane fade in'>
							<div id='f-shipping-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $sp_html . "</div>
						</div>

						<div id='f-boxes-shipping' class='tab-pane fade in'>
							<div id='f-fluid-modal-msgboxes-shipping-div' style='margin-right: 10px; margin-left:10px;'>" . $b_html . "</div>
						</div>

						<div id='f-sms-settings' class='tab-pane fade in'>
							<div id='f-fluid-modal-sms-settings-div' style='margin-right: 10px; margin-left:10px;'>" . $sms_html . "</div>
						</div>

						<div id='f-generator' class='tab-pane fade in'>
							<div id='f-generator-div' style='margin-right: 10px; margin-left:10px;'>" . $g_html . "</div>
						</div>
					</div>
				</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' onClick='js_fluid_save_settings();'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div></div>
			  </div>
			</div>
		  </div>";


		/*
		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($modal_boxes))));
		*/

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal"), "innerHTML" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_fluid_boxes_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_box_data));

		$execute_functions[]['function'] = "js_fluid_taxes_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_tax_data));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// --> Generates and saves a sitemap.xml into the website root folder.
function php_generate_sitemap($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		$fluid = new Fluid();

		$fluid->php_db_begin();

		$f_sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

		$f_sitemap .= "<url>
		<loc>" . $_SESSION['fluid_uri'] . "</loc>
		<priority>1.00</priority>
		</url>";

		$fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " WHERE c_enable = 1 ORDER BY c_sortorder ASC");

		$l_query = NULL;
		$category_data_raw = NULL;
		if(isset($fluid->db_array)) {
			if(count($fluid->db_array) > 0) {
				$i = 0;
				$l_query = "p.p_catid IN (";
				foreach($fluid->db_array as $key => $value) {
					if($i > 0)
						$l_query .= ", ";

					if($value['c_parent_id'] == NULL)
						$category_data_raw[$value['c_id']]['parent'] = $value;
					else
						$category_data_raw[$value['c_parent_id']]['childs'][] = $value;

					$l_query .= "'" . $value['c_id'] . "'";

					$i++;
				}
				$l_query .= ")";
			}
		}

		$category_data = NULL;
		// Resort the categories into the proper order.
		foreach($category_data_raw as $parent) {
			if(isset($parent['parent'])) {
				if(isset($category_data[$parent['parent']['c_sortorder']]))
					$category_data[] = $parent; // Make a new key if this already exists.
				else
					$category_data[$parent['parent']['c_sortorder']] = $parent;
			}
		}

		$f_sitemap_childs = NULL;
		if(isset($category_data)) {
			foreach($category_data as $parent) {
				$f_sitemap .= "<url>
				<loc>" . WWW_SITE . FLUID_ITEM_LISTING_REWRITE . "/" . $parent['parent']['c_id'] . "/" . $fluid->php_clean_string($parent['parent']['c_name']) . "</loc>
				<priority>0.85</priority>
				</url>";

				if(isset($parent['childs'])) {
					foreach($parent['childs'] as $value) {
						$f_sitemap_childs .= "<url>
						<loc>" . WWW_SITE . FLUID_ITEM_LISTING_REWRITE . "/" . $value['c_id'] . "/" . $fluid->php_clean_string($value['c_name']) . "</loc>
						<priority>0.80</priority>
						</url>";
					}
				}
			}
		}

		$f_sitemap .= $f_sitemap_childs;

		// --> Only show products that have stock or a arrival date or discount date ending in the future.
		if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == TRUE) {
			$s_date = date("Y-m-d 00:00:00");
			$filter_where .= " AND ((p_stock > 0 AND p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0 OR (p_newarrivalenddate >= '" . $s_date . "' OR p_discount_date_end >= '" . $s_date . "') OR (p_date_hide > '" . $s_date . "'))";
		}
		else {
			$filter_where .= " AND ((p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0)";
		}

		//$fluid->php_db_query("SELECT p.*, m.*, c.*, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c ON p.p_catid = c_id WHERE " . $l_query . " AND p.p_price IS NOT NULL AND p.p_enable > 0 AND c.c_enable = 1" . $filter_where ." GROUP BY p.p_id ORDER BY p_stock ASC, p.p_sortorder DESC");
		$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c ON p.p_catid = c_id WHERE " . $l_query . " AND p.p_price IS NOT NULL AND p.p_enable > 0 AND c.c_enable = 1" . $filter_where ." GROUP BY p.p_id ORDER BY p_stock ASC, p.p_sortorder DESC");

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $data) {
				if(empty($data['p_mfgcode']))
					$ft_mfgcode = $data['p_id'];
				else
					$ft_mfgcode = $data['p_mfgcode'];

				if(empty($data['p_name']))
					$ft_name = $data['p_id'];
				else
					$ft_name = $data['m_name'] . " " . $data['p_name'];

				$f_sitemap .= "<url>
				<loc>" . WWW_SITE . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "</loc>
				<priority>0.69</priority>
				</url>";

				$t++;
			}
		}

		$f_sitemap .= "</urlset>";

		$fluid->php_db_commit();

		$path_to_file = FOLDER_ROOT . 'sitemap.xml';

		file_put_contents($path_to_file, $f_sitemap);

		$f_sitemap_message = "<div class='alert alert-success' role='alert' style='padding: 5px; margin: 0px;'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> sitemap.xml has been saved.</div>";

		$execute_functions = NULL;
		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-sitemap-area"), "innerHTML" => base64_encode($f_sitemap_message))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => 0, "error_message" => base64_encode("Error generating the sitemap. Please try again.")));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// --> Rebuilds the live search suggestion cache tables.
function php_rebuild_livesearch_cache($data = NULL) {
	try {
		$censor = new CensorWords;
        $langs = array('en-us', 'en-uk', 'fr');
        $badwords = $censor->setDictionary($langs);
        $censor->setReplaceChar("*");

        $fluid = new Fluid();
        $fluid->php_db_begin();

        $fluid->php_db_query("SELECT DISTINCT(l_query), COUNT(*) AS f_count FROM " . TABLE_LOGS . " WHERE l_type = 'search' GROUP BY l_query ORDER BY f_count DESC");

        $f_array = NULL;
        $i = 0;
        if(isset($fluid->db_array)) {
            foreach($fluid->db_array as $f_result) {
                $c_string = $censor->censorString(trim($f_result['l_query']), TRUE);
                $c_filtered = (!empty($c_string['matched'])) ? str_replace($c_string['matched'], "", $c_string['orig']) : $c_string['orig'];
                $c_filtered_trim = trim($c_filtered);
				$c_filtered_trim = preg_replace('/[^a-zA-Z0-9\s]/','', $c_filtered_trim); // Remove all special characters. keeping letters and numbers and spaces only.

                if(strlen($c_filtered_trim) < 40 && strlen($c_filtered_trim) > 3) {
                    $tmp_obj = (object) Array('f_search_input' => $c_filtered_trim);
                    $f_count_result = php_search_suggestion_process($tmp_obj);

                    $f_array[] = Array("count" => $f_count_result, "f_group" => $f_result['f_count'], "query" => $c_filtered_trim);

                    $i++;
                }
            }
        }

        if(isset($f_array)) {
            // Clear the table.
            $fluid->php_db_query("TRUNCATE TABLE `" . TABLE_LIVE_SEARCH_CACHE . "`");

            // Now rebuild the search suggestions.
            $f = 0;
            $f_query = NULL;
            foreach($f_array as $f_search) {
                if($f == 100) {
                    $f_query = "INSERT INTO " . TABLE_LIVE_SEARCH_CACHE . " (lv_search, lv_hits, lv_group) VALUES " . $f_query . ";";

                    $fluid->php_db_query($f_query);

                    $f = 0;
                    $f_query = NULL;
                }

                if($f > 0) {
                    $f_query .= ", ";
                }

                $f_query .= "('" . $fluid->php_escape_string($f_search['query']) . "', '" . $fluid->php_escape_string($f_search['count']) . "', '" . $fluid->php_escape_string($f_search['f_group']) . "')";

                $f++;
            }

            if(isset($f_query)) {
                $f_query = "INSERT INTO " . TABLE_LIVE_SEARCH_CACHE . " (lv_search, lv_hits, lv_group) VALUES " . $f_query . ";";

                $fluid->php_db_query($f_query);
            }
        }

        $fluid->php_db_commit();

		$f_sitemap_message = "<div class='alert alert-success' role='alert' style='padding: 5px; margin: 0px;'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> The live search cache has been rebuilt succesfully.</div>";

		$execute_functions = NULL;
		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-livesearchcache-area"), "innerHTML" => base64_encode($f_sitemap_message))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => 0, "error_message" => base64_encode("Error rebuilding live search cache. Please try again.")));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Scans product database.
function php_search_suggestion_process($f_data = NULL) {
    try {
        $fluid = new Fluid();

        $fluid->php_db_begin();

        $query = NULL;

        $query = $fluid->php_limit_chars(trim($f_data->f_search_input));

        // Weighing scores
        $scoreFullUPC = 50; // 200
        $scoreFullTitle = 3; // 50
        $scoreFullTitleKeyword = 3; // 50
        $scoreStock = 0;
        $scoreTitleKeyword = 3;
        $scoreFullSummary = 4;
        $scoreSummaryKeyword = 3;
        $scoreFullDocument = 2;
        $scoreDocumentKeyword = 4;
        $scoreCategoryKeyword = 4;
        $scoreUrlKeyword = 2;
        $score0 = 0;
        $score1 = 1;
        $score2 = 2;
        $score3 = 3;
        $score4 = 4;
        $score5 = 5;
        $score6 = 6;
        $score7 = 7;
        $score8 = 8;
        $score9 = 9;
        $score10 = 10;

        $m_keywords = $fluid->php_escape_string($query);
        $keywords = $fluid->php_filter_search_keys($query, FALSE);
        $escQuery = $fluid->php_escape_string($query);
        $titleSQL = array();
        $sumSQL = array();
        $docSQL = array();
        //$categorySQL = array();
        $urlSQL = array();
        $keywordsSQL = array();
        $stockSQL = array();

        // Matching full occurences
        if(count($keywords) > 0) {
            $upcSQL[] = "if (p_mfgcode LIKE '%".$escQuery."%',{$scoreFullUPC},0)";

            //if(strlen($escQuery) > 6)
                $titleSQL[] = "if (p_name LIKE '%".$escQuery."%',{$scoreFullTitle},0)";

            $sumSQL[] = "if (m_name LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
            $keywordsSQL[] = "if (p_keywords LIKE '%".$escQuery."%',{$scoreFullTitleKeyword},0)";
            $stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";
        }

        if(strlen($m_keywords) > 0) {
            $urlSQL[] = "if (c_keywords LIKE'%".$m_keywords."%',{$score9},0)";
            $docSQL[] = "if (c_name LIKE '%".$m_keywords."%',{$scoreFullDocument},0)";
        }

        // Matching Keywords
		$i = 0;
		foreach($keywords as $key) {
			//if(strlen($key) > 2) {
				$sumSQL[] = "if (m_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreSummaryKeyword} + (c_search_weight / 2),0)";
				if($i == 0) {
					$c_name_score = $score3;
                }
				else {
					$c_name_score = $score2;
                }

				$docSQL[] = "if (c_name LIKE '%". $fluid->php_escape_string($key)."%',{$c_name_score} + (c_search_weight / 2),0)";
				if($i == 0) {
					$c_keywords_score = $score3;
                }
				else if($i == 1) {
					$c_keywords_score = $score2;
                }
				else if($i == 2) {
					$c_keywords_score = $score2;
                }
				else {
					$c_keywords_score = $score1;
                }

				$urlSQL[] = "if (c_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$keywordsSQL[] = "if (p_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";

				$i++;
			//}
		}

		// Just incase it is empty, then add 0.
		if(empty($upcSQL)) {
			$upcSQL[] = 0;
        }
		if(empty($titleSQL)) {
			$titleSQL[] = 0;
        }
		if(empty($sumSQL)) {
			$sumSQL[] = 0;
        }
		if(empty($docSQL)) {
			$docSQL[] = 0;
        }
		if(empty($urlSQL)) {
			$urlSQL[] = 0;
        }
		if(empty($tagSQL)) {
			$tagSQL[] = 0;
        }
		if(empty($keywordsSQL)) {
			$keywordsSQL[] = 0;
        }
		if(empty($stockSQL)) {
			$stockSQL[] = 0;
        }

        $item_page = 0;
        $item_start = 0;

        // Set up the sort order.
        $sort_by = "relevance DESC, c_search_weight DESC";

        $order_by = "ORDER BY " . $sort_by . " LIMIT " . $item_start . "," . FLUID_LISTING_MAX_SEARCH_SUGGESTIONS;

        $query_search = "
                (
                    (-- UPC/EAN score
                    ".implode(" + ", $upcSQL)."
                    )+
                    (-- item name
                    ".implode(" + ", $titleSQL)."
                    )+
                    (-- item keywords
                    ".implode(" + ", $keywordsSQL)."
                    )+
                    (-- manufacturer name
                    ".implode(" + ", $sumSQL)."
                    )+
                    (-- category name
                    ".implode(" + ", $docSQL)."
                    )+
                    (-- category keywords
                    ".implode(" + ", $urlSQL)."
                    )+
                    (-- -p_stock
                    ".implode(" + ", $stockSQL)."
                    )
                ) as relevance
                FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id
                WHERE p.p_enable > '0' AND c.c_enable = 1
                ";

        // --> Only show products that have stock or a arrival date or discount date ending in the future.
		$query_search_stock = NULL;
		$query_search_zero_stock = NULL;
		if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == TRUE) {
			$s_date = date("Y-m-d 00:00:00");
			$query_search_stock = " AND (p_stock > 0 OR p_showalways > 0 OR (p_newarrivalenddate >= '" . $s_date . "' OR p_discount_date_end >= '" . $s_date . "') OR (p_date_hide > '" . $s_date . "'))";
		}
		else {
			// --> Since we are showing all products in or not in stock. We need to filter out zero stock items that are set to hide when out of stock.
			$query_search_zero_stock = " AND p_zero_status_tmp > 0";
		}

		$query_search .= $query_search_stock;

        $fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, " . $query_search . " GROUP BY relevance, p_zero_status_tmp HAVING relevance > 0 AND p_zero_status_tmp > 0");

		$fluid->php_db_commit();

        $html = NULL;

        $last_id = 0;

        $bool_found_items = FALSE;
        $i_item_count = 0;
        $total_items = 0;
        $f_total_bundles = 0;
        if(isset($fluid->db_array)) {
            $sort_col = array();
            foreach ($fluid->db_array as $key=> $row) {
                $sort_col[$key] = $row['relevance'];
            }

            array_multisort($sort_col, SORT_DESC, $fluid->db_array);

            $highest_relevance = $fluid->db_array[0]['relevance'] / FLUID_SEARCH_RELEVANCE;

            foreach($fluid->db_array as $f_key => $data) {
                if($data['relevance'] <= $highest_relevance) {
                    unset($fluid->db_array[$f_key]);
                }
            }

            $total_items = 0;
            if(isset($fluid->db_array)) {
                foreach($fluid->db_array as $f_tmp_array) {
                    $total_items = $total_items + $f_tmp_array['total'];
                }
            }
            else {
                $total_items = $i_item_count;
            }
        }

		return $total_items;

    }
    catch (Exception $err) {
        return $err;
    }
}

// --> Generates barcode data html for the barcode generator in the settings menu.
function php_generate_barcode_html($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));
		$barcode = NULL;

		if(isset($f_data->f_barcode)) {
			$barcode = "<div style='text-align: center;'>";
			//$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
			//$output .= $generator->getBarcode('081231723897', $generator::TYPE_CODE_128, 2, 100);
			$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
			$barcode .= '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($f_data->f_barcode, $f_data->f_barcode_type, $f_data->f_barcode_width,$f_data->f_barcode_height)) . '">';
			$barcode .= "</div>";

			$barcode .= "<div style='text-align: center; letter-spacing: " . $f_data->f_text_spacing . "px; font-weight: " . $f_data->f_font_weight . "; font-size: " . $f_data->f_font_size . "px;'>" . $f_data->f_barcode . "</div>";
		}

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-barcode-area"), "innerHTML" => base64_encode($barcode))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => 0, "error_message" => base64_encode("Error generating a barcode. Check your settings and try again.")));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// --> Save store settings such as opening and closing, shipping boxes information etc.
function php_fluid_save_set() {
	try {
		$fluid = new Fluid();
		$f_settings = json_decode(base64_decode($_REQUEST['data']), TRUE);

		// NOTE: Checking to make sure both Moneris and Authorize.net are not enabled at the same time.
		$payment_check_array = Array("moneris" => FALSE, "auth_net" => FALSE);

		if(isset($f_settings['fs_moneris'])) {
			if(isset($f_settings['fs_moneris'])) {
				if($f_settings['fs_moneris'] == TRUE && MONERIS_ENABLED == FALSE) {
					$payment_check_array['moneris'] = TRUE;
				}
				else if($f_settings['fs_moneris'] == FALSE && MONERIS_ENABLED == TRUE) {
					$payment_check_array['moneris'] = FALSE;
				}
				else if(MONERIS_ENABLED == TRUE) {
					$payment_check_array['moneris'] = TRUE;
				}
			}
		}
		else if(MONERIS_ENABLED == TRUE) {
			$payment_check_array['moneris'] = TRUE;
		}

		if(isset($f_settings['fs_authorize'])) {
			if(isset($f_settings['fs_authorize'])) {
				if($f_settings['fs_authorize'] == TRUE && AUTH_NET_ENABLED == FALSE) {
					$payment_check_array['auth_net'] = TRUE;
				}
				else if($f_settings['fs_authorize'] == FALSE && AUTH_NET_ENABLED == TRUE) {
					$payment_check_array['auth_net'] = FALSE;
				}
				else if(AUTH_NET_ENABLED == TRUE) {
					$payment_check_array['auth_net'] = TRUE;
				}
			}
		}
		else {
			if(AUTH_NET_ENABLED == TRUE) {
				$payment_check_array['auth_net'] = TRUE;
			}
		}

		if($payment_check_array['moneris'] == TRUE && $payment_check_array['auth_net'] == TRUE) {
			throw new Exception("Error: Moneris and Authorize.net can not both be enabled at the same time. Please disable one of them.");
		}

		$path_to_file = FOLDER_FLUID . 'fluid.db.php';
		$file_contents = file_get_contents($path_to_file);

		if(isset($f_settings['store']) || isset($f_settings['item_status'])) {
			if(isset($f_settings['store'])) {
				if($f_settings['store'] == TRUE && FLUID_STORE_OPEN == FALSE)
					$file_contents = str_replace("define('FLUID_STORE_OPEN', FALSE);","define('FLUID_STORE_OPEN', TRUE);",$file_contents);
				else if($f_settings['store'] == FALSE && FLUID_STORE_OPEN == TRUE)
					$file_contents = str_replace("define('FLUID_STORE_OPEN', TRUE);","define('FLUID_STORE_OPEN', FALSE);",$file_contents);
			}

			if(isset($f_settings['item_status'])) {
				if($f_settings['item_status'] != FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY) {
					$file_contents = str_replace("define('FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY', " . FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY . ");","define('FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY', " . $f_settings['item_status'] . ");",$file_contents);
				}
			}
		}

		if(isset($f_settings['fs_slogan_enabled'])) {
			if(isset($f_settings['fs_slogan_enabled'])) {
				if($f_settings['fs_slogan_enabled'] == TRUE && FLUID_SLOGAN_ENABLED == FALSE)
					$file_contents = str_replace("define('FLUID_SLOGAN_ENABLED', FALSE);","define('FLUID_SLOGAN_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_slogan_enabled'] == FALSE && FLUID_SLOGAN_ENABLED == TRUE)
					$file_contents = str_replace("define('FLUID_SLOGAN_ENABLED', TRUE);","define('FLUID_SLOGAN_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_slogan'])) {
			if($f_settings['fs_slogan'] != FLUID_SLOGAN)
				$file_contents = str_replace("define('FLUID_SLOGAN', '" . FLUID_SLOGAN . "');","define('FLUID_SLOGAN', '" . $f_settings['fs_slogan'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_navbar_menu'])) {
			if(isset($f_settings['fs_navbar_menu'])) {
				if($f_settings['fs_navbar_menu'] == TRUE && FLUID_NAVBAR_CART_MENU == FALSE)
					$file_contents = str_replace("define('FLUID_NAVBAR_CART_MENU', FALSE);","define('FLUID_NAVBAR_CART_MENU', TRUE);",$file_contents);
				else if($f_settings['fs_navbar_menu'] == FALSE && FLUID_NAVBAR_CART_MENU == TRUE)
					$file_contents = str_replace("define('FLUID_NAVBAR_CART_MENU', TRUE);","define('FLUID_NAVBAR_CART_MENU', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_navbar_pinned'])) {
			if(isset($f_settings['fs_navbar_pinned'])) {
				if($f_settings['fs_navbar_pinned'] == TRUE && FLUID_NAVBAR_PIN == FALSE)
					$file_contents = str_replace("define('FLUID_NAVBAR_PIN', FALSE);","define('FLUID_NAVBAR_PIN', TRUE);",$file_contents);
				else if($f_settings['fs_navbar_pinned'] == FALSE && FLUID_NAVBAR_PIN == TRUE)
					$file_contents = str_replace("define('FLUID_NAVBAR_PIN', TRUE);","define('FLUID_NAVBAR_PIN', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_navbar_pinned_mobile'])) {
			if(isset($f_settings['fs_navbar_pinned_mobile'])) {
				if($f_settings['fs_navbar_pinned_mobile'] == TRUE && FLUID_NAVBAR_PIN_MOBILE == FALSE)
					$file_contents = str_replace("define('FLUID_NAVBAR_PIN_MOBILE', FALSE);","define('FLUID_NAVBAR_PIN_MOBILE', TRUE);",$file_contents);
				else if($f_settings['fs_navbar_pinned_mobile'] == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)
					$file_contents = str_replace("define('FLUID_NAVBAR_PIN_MOBILE', TRUE);","define('FLUID_NAVBAR_PIN_MOBILE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_filters_pinned'])) {
			if(isset($f_settings['fs_filters_pinned'])) {
				if($f_settings['fs_filters_pinned'] == TRUE && FLUID_LISTING_FILTERS_PINNED == FALSE)
					$file_contents = str_replace("define('FLUID_LISTING_FILTERS_PINNED', FALSE);","define('FLUID_LISTING_FILTERS_PINNED', TRUE);",$file_contents);
				else if($f_settings['fs_filters_pinned'] == FALSE && FLUID_LISTING_FILTERS_PINNED == TRUE)
					$file_contents = str_replace("define('FLUID_LISTING_FILTERS_PINNED', TRUE);","define('FLUID_LISTING_FILTERS_PINNED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_filters_pinned_mobile'])) {
			if(isset($f_settings['fs_filters_pinned_mobile'])) {
				if($f_settings['fs_filters_pinned_mobile'] == TRUE && FLUID_LISTING_FILTERS_PINNED_MOBILE == FALSE)
					$file_contents = str_replace("define('FLUID_LISTING_FILTERS_PINNED_MOBILE', FALSE);","define('FLUID_LISTING_FILTERS_PINNED_MOBILE', TRUE);",$file_contents);
				else if($f_settings['fs_filters_pinned_mobile'] == FALSE && FLUID_LISTING_FILTERS_PINNED_MOBILE == TRUE)
					$file_contents = str_replace("define('FLUID_LISTING_FILTERS_PINNED_MOBILE', TRUE);","define('FLUID_LISTING_FILTERS_PINNED_MOBILE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_preorders'])) {
			if(isset($f_settings['fs_preorders'])) {
				if($f_settings['fs_preorders'] == TRUE && FLUID_PREORDER == FALSE)
					$file_contents = str_replace("define('FLUID_PREORDER', FALSE);","define('FLUID_PREORDER', TRUE);",$file_contents);
				else if($f_settings['fs_preorders'] == FALSE && FLUID_PREORDER == TRUE)
					$file_contents = str_replace("define('FLUID_PREORDER', TRUE);","define('FLUID_PREORDER', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_purchase_out_of_stock'])) {
			if(isset($f_settings['fs_purchase_out_of_stock'])) {
				if($f_settings['fs_purchase_out_of_stock'] == TRUE && FLUID_PURCHASE_OUT_OF_STOCK == FALSE)
					$file_contents = str_replace("define('FLUID_PURCHASE_OUT_OF_STOCK', FALSE);","define('FLUID_PURCHASE_OUT_OF_STOCK', TRUE);",$file_contents);
				else if($f_settings['fs_purchase_out_of_stock'] == FALSE && FLUID_PURCHASE_OUT_OF_STOCK == TRUE)
					$file_contents = str_replace("define('FLUID_PURCHASE_OUT_OF_STOCK', TRUE);","define('FLUID_PURCHASE_OUT_OF_STOCK', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_deal_timer'])) {
			if(isset($f_settings['fs_deal_timer'])) {
				if($f_settings['fs_deal_timer'] == TRUE && FLUID_SAVINGS_TIMER_HIDE == FALSE)
					$file_contents = str_replace("define('FLUID_SAVINGS_TIMER_HIDE', FALSE);","define('FLUID_SAVINGS_TIMER_HIDE', TRUE);",$file_contents);
				else if($f_settings['fs_deal_timer'] == FALSE && FLUID_SAVINGS_TIMER_HIDE == TRUE)
					$file_contents = str_replace("define('FLUID_SAVINGS_TIMER_HIDE', TRUE);","define('FLUID_SAVINGS_TIMER_HIDE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_debug_enabled'])) {
			if(isset($f_settings['fs_debug_enabled'])) {
				if($f_settings['fs_debug_enabled'] == TRUE && ENABLE_LOG == FALSE)
					$file_contents = str_replace("define('ENABLE_LOG', FALSE);","define('ENABLE_LOG', TRUE);",$file_contents);
				else if($f_settings['fs_debug_enabled'] == FALSE && ENABLE_LOG == TRUE)
					$file_contents = str_replace("define('ENABLE_LOG', TRUE);","define('ENABLE_LOG', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_debug_log'])) {
			if($f_settings['fs_debug_log'] != DEBUG_LOG)
				$file_contents = str_replace("define('DEBUG_LOG', '" . DEBUG_LOG . "');","define('DEBUG_LOG', '" . $f_settings['fs_debug_log'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_canadapost'])) {
			if(isset($f_settings['fs_canadapost'])) {
				if($f_settings['fs_canadapost'] == TRUE && ENABLE_CANADAPOST == FALSE)
					$file_contents = str_replace("define('ENABLE_CANADAPOST', FALSE);","define('ENABLE_CANADAPOST', TRUE);",$file_contents);
				else if($f_settings['fs_canadapost'] == FALSE && ENABLE_CANADAPOST == TRUE)
					$file_contents = str_replace("define('ENABLE_CANADAPOST', TRUE);","define('ENABLE_CANADAPOST', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_canadapost_signature'])) {
			if(isset($f_settings['fs_canadapost_signature'])) {
				if($f_settings['fs_canadapost_signature'] == TRUE && CANADA_POST_SIGNATURE == FALSE)
					$file_contents = str_replace("define('CANADA_POST_SIGNATURE', FALSE);","define('CANADA_POST_SIGNATURE', TRUE);",$file_contents);
				else if($f_settings['fs_canadapost_signature'] == FALSE && CANADA_POST_SIGNATURE == TRUE)
					$file_contents = str_replace("define('CANADA_POST_SIGNATURE', TRUE);","define('CANADA_POST_SIGNATURE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_canadapost_username'])) {
			if($f_settings['fs_canadapost_username'] != CANADA_POST_USERNAME)
				$file_contents = str_replace("define('CANADA_POST_USERNAME', '" . CANADA_POST_USERNAME . "');","define('CANADA_POST_USERNAME', '" . $f_settings['fs_canadapost_username'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_canadapost_password'])) {
			if($f_settings['fs_canadapost_password'] != CANADA_POST_PASSWORD)
				$file_contents = str_replace("define('CANADA_POST_PASSWORD', '" . CANADA_POST_PASSWORD . "');","define('CANADA_POST_PASSWORD', '" . $f_settings['fs_canadapost_password'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_canadapost_customer_number'])) {
			if($f_settings['fs_canadapost_customer_number'] != CANADA_POST_CUSTOMER_NUMBER)
				$file_contents = str_replace("define('CANADA_POST_CUSTOMER_NUMBER', '" . CANADA_POST_CUSTOMER_NUMBER . "');","define('CANADA_POST_CUSTOMER_NUMBER', '" . $f_settings['fs_canadapost_customer_number'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex'])) {
			if(isset($f_settings['fs_fedex'])) {
				if($f_settings['fs_fedex'] == TRUE && ENABLE_FEDEX == FALSE)
					$file_contents = str_replace("define('ENABLE_FEDEX', FALSE);","define('ENABLE_FEDEX', TRUE);",$file_contents);
				else if($f_settings['fs_fedex'] == FALSE && ENABLE_FEDEX == TRUE)
					$file_contents = str_replace("define('ENABLE_FEDEX', TRUE);","define('ENABLE_FEDEX', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_fedex_signature'])) {
			if(isset($f_settings['fs_fedex_signature'])) {
				if($f_settings['fs_fedex_signature'] == TRUE && FEDEX_SIGNATURE == FALSE)
					$file_contents = str_replace("define('FEDEX_SIGNATURE', FALSE);","define('FEDEX_SIGNATURE', TRUE);",$file_contents);
				else if($f_settings['fs_fedex_signature'] == FALSE && FEDEX_SIGNATURE == TRUE)
					$file_contents = str_replace("define('FEDEX_SIGNATURE', TRUE);","define('FEDEX_SIGNATURE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_fedex_account'])) {
			if($f_settings['fs_fedex_account'] != FEDEX_ACCOUNT)
				$file_contents = str_replace("define('FEDEX_ACCOUNT', '" . FEDEX_ACCOUNT . "');","define('FEDEX_ACCOUNT', '" . $f_settings['fs_fedex_account'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_meter'])) {
			if($f_settings['fs_fedex_meter'] != FEDEX_METER)
				$file_contents = str_replace("define('FEDEX_METER', '" . FEDEX_METER . "');","define('FEDEX_METER', '" . $f_settings['fs_fedex_meter'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_key'])) {
			if($f_settings['fs_fedex_key'] != FEDEX_KEY)
				$file_contents = str_replace("define('FEDEX_KEY', '" . FEDEX_KEY . "');","define('FEDEX_KEY', '" . $f_settings['fs_fedex_key'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_password'])) {
			if($f_settings['fs_fedex_password'] != FEDEX_PASSWORD)
				$file_contents = str_replace("define('FEDEX_PASSWORD', '" . FEDEX_PASSWORD . "');","define('FEDEX_PASSWORD', '" . $f_settings['fs_fedex_password'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_person'])) {
			if($f_settings['fs_fedex_person'] != FEDEX_PERSON)
				$file_contents = str_replace("define('FEDEX_PERSON', '" . FEDEX_PERSON . "');","define('FEDEX_PERSON', '" . $f_settings['fs_fedex_person'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_company'])) {
			if($f_settings['fs_fedex_company'] != FEDEX_COMPANY)
				$file_contents = str_replace("define('FEDEX_COMPANY', '" . FEDEX_COMPANY . "');","define('FEDEX_COMPANY', '" . $f_settings['fs_fedex_company'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_phone'])) {
			if($f_settings['fs_fedex_phone'] != FEDEX_PHONE)
				$file_contents = str_replace("define('FEDEX_PHONE', '" . FEDEX_PHONE . "');","define('FEDEX_PHONE', '" . $f_settings['fs_fedex_phone'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_street'])) {
			if($f_settings['fs_fedex_street'] != FEDEX_STREET)
				$file_contents = str_replace("define('FEDEX_STREET', '" . FEDEX_STREET . "');","define('FEDEX_STREET', '" . $f_settings['fs_fedex_street'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_city'])) {
			if($f_settings['fs_fedex_city'] != FEDEX_CITY)
				$file_contents = str_replace("define('FEDEX_CITY', '" . FEDEX_CITY . "');","define('FEDEX_CITY', '" . $f_settings['fs_fedex_city'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_province'])) {
			if($f_settings['fs_fedex_province'] != FEDEX_PROVINCE)
				$file_contents = str_replace("define('FEDEX_PROVINCE', '" . FEDEX_PROVINCE . "');","define('FEDEX_PROVINCE', '" . $f_settings['fs_fedex_province'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_postalcode'])) {
			if($f_settings['fs_fedex_postalcode'] != FEDEX_POSTAL_CODE)
				$file_contents = str_replace("define('FEDEX_POSTAL_CODE', '" . FEDEX_POSTAL_CODE . "');","define('FEDEX_POSTAL_CODE', '" . $f_settings['fs_fedex_postalcode'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_fedex_country'])) {
			if($f_settings['fs_fedex_country'] != FEDEX_COUNTRY_CODE)
				$file_contents = str_replace("define('FEDEX_COUNTRY_CODE', '" . FEDEX_COUNTRY_CODE . "');","define('FEDEX_COUNTRY_CODE', '" . $f_settings['fs_fedex_country'] . "');",$file_contents);
		}


		if(isset($f_settings['fs_authorize'])) {
			if(isset($f_settings['fs_authorize'])) {
				if($f_settings['fs_authorize'] == TRUE && AUTH_NET_ENABLED == FALSE)
					$file_contents = str_replace("define('AUTH_NET_ENABLED', FALSE);","define('AUTH_NET_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_authorize'] == FALSE && AUTH_NET_ENABLED == TRUE)
					$file_contents = str_replace("define('AUTH_NET_ENABLED', TRUE);","define('AUTH_NET_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['f_authorize_api_key'])) {
			if($f_settings['f_authorize_api_key'] != AUTH_NET_API_KEY)
				$file_contents = str_replace("define('AUTH_NET_API_KEY', '" . AUTH_NET_API_KEY . "');","define('AUTH_NET_API_KEY', '" . $f_settings['f_authorize_api_key'] . "');",$file_contents);
		}

		if(isset($f_settings['f_authorize_api_key_sandbox'])) {
			if($f_settings['f_authorize_api_key_sandbox'] != AUTH_NET_SANDBOX_API_KEY)
				$file_contents = str_replace("define('AUTH_NET_SANDBOX_API_KEY', '" . AUTH_NET_SANDBOX_API_KEY . "');","define('AUTH_NET_SANDBOX_API_KEY', '" . $f_settings['f_authorize_api_key_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['f_authorize_login_id'])) {
			if($f_settings['f_authorize_login_id'] != AUTH_NET_LOGIN_ID)
				$file_contents = str_replace("define('AUTH_NET_LOGIN_ID', '" . AUTH_NET_LOGIN_ID . "');","define('AUTH_NET_LOGIN_ID', '" . $f_settings['f_authorize_login_id'] . "');",$file_contents);
		}

		if(isset($f_settings['f_authorize_login_id_sandbox'])) {
			if($f_settings['f_authorize_login_id_sandbox'] != AUTH_NET_SANDBOX_LOGIN_ID)
				$file_contents = str_replace("define('AUTH_NET_SANDBOX_LOGIN_ID', '" . AUTH_NET_SANDBOX_LOGIN_ID . "');","define('AUTH_NET_SANDBOX_LOGIN_ID', '" . $f_settings['f_authorize_login_id_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_moneris'])) {
			if(isset($f_settings['fs_moneris'])) {
				if($f_settings['fs_moneris'] == TRUE && MONERIS_ENABLED == FALSE)
					$file_contents = str_replace("define('MONERIS_ENABLED', FALSE);","define('MONERIS_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_moneris'] == FALSE && MONERIS_ENABLED == TRUE)
					$file_contents = str_replace("define('MONERIS_ENABLED', TRUE);","define('MONERIS_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['f_moneris_api_key'])) {
			if($f_settings['f_moneris_api_key'] != MONERIS_API_KEY)
				$file_contents = str_replace("define('MONERIS_API_KEY', '" . MONERIS_API_KEY . "');","define('MONERIS_API_KEY', '" . $f_settings['f_moneris_api_key'] . "');",$file_contents);
		}

		if(isset($f_settings['f_moneris_api_key_sandbox'])) {
			if($f_settings['f_moneris_api_key_sandbox'] != MONERIS_API_KEY_SANDBOX)
				$file_contents = str_replace("define('MONERIS_API_KEY_SANDBOX', '" . MONERIS_API_KEY_SANDBOX . "');","define('MONERIS_API_KEY_SANDBOX', '" . $f_settings['f_moneris_api_key_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['f_moneris_store_id'])) {
			if($f_settings['f_moneris_store_id'] != MONERIS_STORE_ID)
				$file_contents = str_replace("define('MONERIS_STORE_ID', '" . MONERIS_STORE_ID . "');","define('MONERIS_STORE_ID', '" . $f_settings['f_moneris_store_id'] . "');",$file_contents);
		}

		if(isset($f_settings['f_moneris_store_id_sandbox'])) {
			if($f_settings['f_moneris_store_id_sandbox'] != MONERIS_STORE_ID_SANDBOX)
				$file_contents = str_replace("define('MONERIS_STORE_ID_SANDBOX', '" . MONERIS_STORE_ID_SANDBOX . "');","define('MONERIS_STORE_ID_SANDBOX', '" . $f_settings['f_moneris_store_id_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_paypal'])) {
			if(isset($f_settings['fs_paypal'])) {
				if($f_settings['fs_paypal'] == TRUE && PAYPAL_ENABLED == FALSE)
					$file_contents = str_replace("define('PAYPAL_ENABLED', FALSE);","define('PAYPAL_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_paypal'] == FALSE && PAYPAL_ENABLED == TRUE)
					$file_contents = str_replace("define('PAYPAL_ENABLED', TRUE);","define('PAYPAL_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['f_paypal_client_id'])) {
			if($f_settings['f_paypal_client_id'] != PAYPAL_CLIENT_ID)
				$file_contents = str_replace("define('PAYPAL_CLIENT_ID','" . PAYPAL_CLIENT_ID . "');","define('PAYPAL_CLIENT_ID','" . $f_settings['f_paypal_client_id'] . "');",$file_contents);
		}

		if(isset($f_settings['f_paypal_client_id_sandbox'])) {
			if($f_settings['f_paypal_client_id_sandbox'] != PAYPAL_CLIENT_ID_SANDBOX)
				$file_contents = str_replace("define('PAYPAL_CLIENT_ID_SANDBOX','" . PAYPAL_CLIENT_ID_SANDBOX . "');","define('PAYPAL_CLIENT_ID_SANDBOX','" . $f_settings['f_paypal_client_id_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['f_paypal_secret'])) {
			if($f_settings['f_paypal_secret'] != PAYPAL_SECRET)
				$file_contents = str_replace("define('PAYPAL_SECRET','" . PAYPAL_SECRET . "');","define('PAYPAL_SECRET','" . $f_settings['f_paypal_secret'] . "');",$file_contents);
		}

		if(isset($f_settings['f_paypal_secret_sandbox'])) {
			if($f_settings['f_paypal_secret_sandbox'] != PAYPAL_SECRET_SANDBOX)
				$file_contents = str_replace("define('PAYPAL_SECRET_SANDBOX','" . PAYPAL_SECRET_SANDBOX . "');","define('PAYPAL_SECRET_SANDBOX','" . $f_settings['f_paypal_secret_sandbox'] . "');",$file_contents);
		}

		if(isset($f_settings['f_checkout_sandbox'])) {
			if(isset($f_settings['f_checkout_sandbox'])) {
				if($f_settings['f_checkout_sandbox'] == TRUE && FLUID_PAYMENT_SANDBOX == FALSE)
					$file_contents = str_replace("define('FLUID_PAYMENT_SANDBOX', FALSE);","define('FLUID_PAYMENT_SANDBOX', TRUE);",$file_contents);
				else if($f_settings['f_checkout_sandbox'] == FALSE && FLUID_PAYMENT_SANDBOX == TRUE)
					$file_contents = str_replace("define('FLUID_PAYMENT_SANDBOX', TRUE);","define('FLUID_PAYMENT_SANDBOX', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_instore_pickup'])) {
			if(isset($f_settings['fs_instore_pickup'])) {
				if($f_settings['fs_instore_pickup'] == TRUE && ENABLE_IN_STORE_PICKUP == FALSE)
					$file_contents = str_replace("define('ENABLE_IN_STORE_PICKUP', FALSE);","define('ENABLE_IN_STORE_PICKUP', TRUE);",$file_contents);
				else if($f_settings['fs_instore_pickup'] == FALSE && ENABLE_IN_STORE_PICKUP == TRUE)
					$file_contents = str_replace("define('ENABLE_IN_STORE_PICKUP', TRUE);","define('ENABLE_IN_STORE_PICKUP', FALSE);",$file_contents);
			}
		}
		
		if(isset($f_settings['fs_instore_pickup_payment'])) {
			if(isset($f_settings['fs_instore_pickup_payment'])) {
				if($f_settings['fs_instore_pickup_payment'] == TRUE && ENABLE_IN_STORE_PICKUP_PAYMENT == FALSE)
					$file_contents = str_replace("define('ENABLE_IN_STORE_PICKUP_PAYMENT', FALSE);","define('ENABLE_IN_STORE_PICKUP_PAYMENT', TRUE);",$file_contents);
				else if($f_settings['fs_instore_pickup_payment'] == FALSE && ENABLE_IN_STORE_PICKUP_PAYMENT == TRUE)
					$file_contents = str_replace("define('ENABLE_IN_STORE_PICKUP_PAYMENT', TRUE);","define('ENABLE_IN_STORE_PICKUP_PAYMENT', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_free_shipping'])) {
			if(isset($f_settings['fs_free_shipping'])) {
				if($f_settings['fs_free_shipping'] == TRUE && FREE_SHIPPING_FORMULA_ENABLED == FALSE)
					$file_contents = str_replace("define('FREE_SHIPPING_FORMULA_ENABLED', FALSE);","define('FREE_SHIPPING_FORMULA_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_free_shipping'] == FALSE && FREE_SHIPPING_FORMULA_ENABLED == TRUE)
					$file_contents = str_replace("define('FREE_SHIPPING_FORMULA_ENABLED', TRUE);","define('FREE_SHIPPING_FORMULA_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_split_shipping'])) {
			if(isset($f_settings['fs_split_shipping'])) {
				if($f_settings['fs_split_shipping'] == TRUE && FLUID_SPLIT_SHIPPING == FALSE)
					$file_contents = str_replace("define('FLUID_SPLIT_SHIPPING', FALSE);","define('FLUID_SPLIT_SHIPPING', TRUE);",$file_contents);
				else if($f_settings['fs_split_shipping'] == FALSE && FLUID_SPLIT_SHIPPING == TRUE)
					$file_contents = str_replace("define('FLUID_SPLIT_SHIPPING', TRUE);","define('FLUID_SPLIT_SHIPPING', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_margin_1'])) {
			if(isset($f_settings['fs_margin_1']))
				if($f_settings['fs_margin_1'] != FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1)
					$file_contents = str_replace("define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1', " . FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1 . ");","define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_1', " . $f_settings['fs_margin_1'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_value_1'])) {
			if(isset($f_settings['fs_value_1']))
				if($f_settings['fs_value_1'] != FREE_SHIPPING_CART_TOTAL_STEP_1)
					$file_contents = str_replace("define('FREE_SHIPPING_CART_TOTAL_STEP_1', " . FREE_SHIPPING_CART_TOTAL_STEP_1 . ");","define('FREE_SHIPPING_CART_TOTAL_STEP_1', " . $f_settings['fs_value_1'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_margin_2'])) {
			if(isset($f_settings['fs_margin_2']))
				if($f_settings['fs_margin_2'] != FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2)
					$file_contents = str_replace("define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2', " . FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2 . ");","define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_2', " . $f_settings['fs_margin_2'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_value_2'])) {
			if(isset($f_settings['fs_value_2']))
				if($f_settings['fs_value_2'] != FREE_SHIPPING_CART_TOTAL_STEP_2)
					$file_contents = str_replace("define('FREE_SHIPPING_CART_TOTAL_STEP_2', " . FREE_SHIPPING_CART_TOTAL_STEP_2 . ");","define('FREE_SHIPPING_CART_TOTAL_STEP_2', " . $f_settings['fs_value_2'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_margin_3'])) {
			if(isset($f_settings['fs_margin_3']))
				if($f_settings['fs_margin_3'] != FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3)
					$file_contents = str_replace("define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3', " . FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3 . ");","define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_3', " . $f_settings['fs_margin_3'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_value_3'])) {
			if(isset($f_settings['fs_value_3']))
				if($f_settings['fs_value_3'] != FREE_SHIPPING_CART_TOTAL_STEP_3)
					$file_contents = str_replace("define('FREE_SHIPPING_CART_TOTAL_STEP_3', " . FREE_SHIPPING_CART_TOTAL_STEP_3 . ");","define('FREE_SHIPPING_CART_TOTAL_STEP_3', " . $f_settings['fs_value_3'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_margin_4'])) {
			if(isset($f_settings['fs_margin_4']))
				if($f_settings['fs_margin_4'] != FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4)
					$file_contents = str_replace("define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4', " . FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4 . ");","define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_4', " . $f_settings['fs_margin_4'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_value_4'])) {
			if(isset($f_settings['fs_value_4']))
				if($f_settings['fs_value_4'] != FREE_SHIPPING_CART_TOTAL_STEP_4)
					$file_contents = str_replace("define('FREE_SHIPPING_CART_TOTAL_STEP_4', " . FREE_SHIPPING_CART_TOTAL_STEP_4 . ");","define('FREE_SHIPPING_CART_TOTAL_STEP_4', " . $f_settings['fs_value_4'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_margin_5'])) {
			if(isset($f_settings['fs_margin_5']))
				if($f_settings['fs_margin_5'] != FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5)
					$file_contents = str_replace("define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5', " . FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5 . ");","define('FREE_SHIPPING_MARGIN_PERCENTAGE_STEP_5', " . $f_settings['fs_margin_5'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_value_5'])) {
			if(isset($f_settings['fs_value_5']))
				if($f_settings['fs_value_5'] != FREE_SHIPPING_CART_TOTAL_STEP_5)
					$file_contents = str_replace("define('FREE_SHIPPING_CART_TOTAL_STEP_5', " . FREE_SHIPPING_CART_TOTAL_STEP_5 . ");","define('FREE_SHIPPING_CART_TOTAL_STEP_5', " . $f_settings['fs_value_5'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_postal_code'])) {
			if($f_settings['fs_postal_code'] != FLUID_ORIGIN_POSTAL_CODE)
				$file_contents = str_replace("define('FLUID_ORIGIN_POSTAL_CODE', '" . FLUID_ORIGIN_POSTAL_CODE . "');","define('FLUID_ORIGIN_POSTAL_CODE', '" . $f_settings['fs_postal_code'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_hash_key'])) {
			if($f_settings['fs_hash_key'] != HASH_KEY)
				$file_contents = str_replace("define('HASH_KEY', '" . HASH_KEY . "');","define('HASH_KEY', '" . $f_settings['fs_hash_key'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_currency_key'])) {
			if(base64_decode($f_settings['fs_currency_key']) != HTML_CURRENCY)
				$file_contents = str_replace("define('HTML_CURRENCY', '" . HTML_CURRENCY . "');","define('HTML_CURRENCY', '" . base64_decode($f_settings['fs_currency_key']) . "');",$file_contents);
		}

		if(isset($f_settings['fs_currency_icon'])) {
			if($f_settings['fs_currency_icon'] != HTML_CURRENCY_GLYPHICON)
				$file_contents = str_replace("define('HTML_CURRENCY_GLYPHICON', '" . HTML_CURRENCY_GLYPHICON . "');","define('HTML_CURRENCY_GLYPHICON', '" . $f_settings['fs_currency_icon'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_currency_code'])) {
			if($f_settings['fs_currency_code'] != STORE_CURRENCY)
				$file_contents = str_replace("define('STORE_CURRENCY', '" . STORE_CURRENCY . "');","define('STORE_CURRENCY', '" . $f_settings['fs_currency_code'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_max_listings'])) {
			if($f_settings['fs_max_listings'] != VAR_LISTING_MAX)
				$file_contents = str_replace("define('VAR_LISTING_MAX', " . VAR_LISTING_MAX . ");","define('VAR_LISTING_MAX', " . $f_settings['fs_max_listings'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_max_images'])) {
			if($f_settings['fs_max_images'] != FLUID_ITEM_PAGE_MAX_IMAGES)
				$file_contents = str_replace("define('FLUID_ITEM_PAGE_MAX_IMAGES', " . FLUID_ITEM_PAGE_MAX_IMAGES . ");","define('FLUID_ITEM_PAGE_MAX_IMAGES', " . $f_settings['fs_max_images'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_feedback_timer'])) {
			if($f_settings['fs_feedback_timer'] != FLUID_FEEDBACK_TIMER_LENGTH)
				$file_contents = str_replace("define('FLUID_FEEDBACK_TIMER_LENGTH', " . number_format(FLUID_FEEDBACK_TIMER_LENGTH, 0, '', '') . ");","define('FLUID_FEEDBACK_TIMER_LENGTH', " . number_format($f_settings['fs_feedback_timer'], 0, '', '') . ");",$file_contents);
		}

		if(isset($f_settings['fs_search_relevance'])) {
			if($f_settings['fs_search_relevance'] != FLUID_SEARCH_RELEVANCE)
				$file_contents = str_replace("define('FLUID_SEARCH_RELEVANCE', " . number_format(FLUID_SEARCH_RELEVANCE, 1, '.', '') . ");","define('FLUID_SEARCH_RELEVANCE', " . number_format($f_settings['fs_search_relevance'], 1, '.', '') . ");",$file_contents);
		}

		if(isset($f_settings['fs_feedback_enabled'])) {
			if(isset($f_settings['fs_feedback_enabled'])) {
				if($f_settings['fs_feedback_enabled'] == TRUE && FLUID_FEEDBACK_ENABLE == FALSE)
					$file_contents = str_replace("define('FLUID_FEEDBACK_ENABLE', FALSE);","define('FLUID_FEEDBACK_ENABLE', TRUE);",$file_contents);
				else if($f_settings['fs_feedback_enabled'] == FALSE && FLUID_FEEDBACK_ENABLE == TRUE)
					$file_contents = str_replace("define('FLUID_FEEDBACK_ENABLE', TRUE);","define('FLUID_FEEDBACK_ENABLE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_additional_savings_merge'])) {
			if(isset($f_settings['fs_additional_savings_merge'])) {
				if($f_settings['fs_additional_savings_merge'] == TRUE && FLUID_ADDITIONAL_SAVINGS_MERGE == FALSE)
					$file_contents = str_replace("define('FLUID_ADDITIONAL_SAVINGS_MERGE', FALSE);","define('FLUID_ADDITIONAL_SAVINGS_MERGE', TRUE);",$file_contents);
				else if($f_settings['fs_additional_savings_merge'] == FALSE && FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
					$file_contents = str_replace("define('FLUID_ADDITIONAL_SAVINGS_MERGE', TRUE);","define('FLUID_ADDITIONAL_SAVINGS_MERGE', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['f_closed_message']) && $f_settings['f_closed_message_old']) {
			if($f_settings['f_closed_message'] != FLUID_STORE_CLOSED_MESSAGE)
				$file_contents = str_replace("define('FLUID_STORE_CLOSED_MESSAGE', '" . $f_settings['f_closed_message_old'] . "');","define('FLUID_STORE_CLOSED_MESSAGE', '" . $f_settings['f_closed_message'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_trending'])) {
			if(isset($f_settings['fs_slider_trending'])) {
				if($f_settings['fs_slider_trending'] == TRUE && FLUID_DISPLAY_TRENDING_SLIDER == FALSE)
					$file_contents = str_replace("define('FLUID_DISPLAY_TRENDING_SLIDER', FALSE);","define('FLUID_DISPLAY_TRENDING_SLIDER', TRUE);",$file_contents);
				else if($f_settings['fs_slider_trending'] == FALSE && FLUID_DISPLAY_TRENDING_SLIDER == TRUE)
					$file_contents = str_replace("define('FLUID_DISPLAY_TRENDING_SLIDER', TRUE);","define('FLUID_DISPLAY_TRENDING_SLIDER', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_slider_trending_text'])) {
			if($f_settings['fs_slider_trending_text'] != FLUID_TRENDING_SLIDER_MESSAGE_HEADER)
				$file_contents = str_replace("define('FLUID_TRENDING_SLIDER_MESSAGE_HEADER', '" . FLUID_TRENDING_SLIDER_MESSAGE_HEADER . "');","define('FLUID_TRENDING_SLIDER_MESSAGE_HEADER', '" . $f_settings['fs_slider_trending_text'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_deal'])) {
			if(isset($f_settings['fs_slider_deal'])) {
				if($f_settings['fs_slider_deal'] == TRUE && FLUID_DISPLAY_DEAL_SLIDER == FALSE)
					$file_contents = str_replace("define('FLUID_DISPLAY_DEAL_SLIDER', FALSE);","define('FLUID_DISPLAY_DEAL_SLIDER', TRUE);",$file_contents);
				else if($f_settings['fs_slider_deal'] == FALSE && FLUID_DISPLAY_DEAL_SLIDER == TRUE)
					$file_contents = str_replace("define('FLUID_DISPLAY_DEAL_SLIDER', TRUE);","define('FLUID_DISPLAY_DEAL_SLIDER', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_slider_deal_text'])) {
			if($f_settings['fs_slider_deal_text'] != FLUID_DEAL_SLIDER_MESSAGE_HEADER)
				$file_contents = str_replace("define('FLUID_DEAL_SLIDER_MESSAGE_HEADER', '" . FLUID_DEAL_SLIDER_MESSAGE_HEADER . "');","define('FLUID_DEAL_SLIDER_MESSAGE_HEADER', '" . $f_settings['fs_slider_deal_text'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_deal_button'])) {
			if($f_settings['fs_slider_deal_button'] != FLUID_DEAL_BUTTON)
				$file_contents = str_replace("define('FLUID_DEAL_BUTTON', '" . FLUID_DEAL_BUTTON . "');","define('FLUID_DEAL_BUTTON', '" . $f_settings['fs_slider_deal_button'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_formula'])) {
			if(isset($f_settings['fs_slider_formula'])) {
				if($f_settings['fs_slider_formula'] == TRUE && FLUID_DISPLAY_FORMULA_DEAL_SLIDER == FALSE)
					$file_contents = str_replace("define('FLUID_DISPLAY_FORMULA_DEAL_SLIDER', FALSE);","define('FLUID_DISPLAY_FORMULA_DEAL_SLIDER', TRUE);",$file_contents);
				else if($f_settings['fs_slider_formula'] == FALSE && FLUID_DISPLAY_FORMULA_DEAL_SLIDER == TRUE)
					$file_contents = str_replace("define('FLUID_DISPLAY_FORMULA_DEAL_SLIDER', TRUE);","define('FLUID_DISPLAY_FORMULA_DEAL_SLIDER', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_slider_formula_text'])) {
			if($f_settings['fs_slider_formula_text'] != FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER)
				$file_contents = str_replace("define('FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER', '" . FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER . "');","define('FLUID_FORMULA_DEAL_SLIDER_MESSAGE_HEADER', '" . $f_settings['fs_slider_formula_text'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_formula_button'])) {
			if($f_settings['fs_slider_formula_button'] != FLUID_FORMULA_BUTTON)
				$file_contents = str_replace("define('FLUID_FORMULA_BUTTON', '" . FLUID_FORMULA_BUTTON . "');","define('FLUID_FORMULA_BUTTON', '" . $f_settings['fs_slider_formula_button'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_blackfriday'])) {
			if(isset($f_settings['fs_slider_blackfriday'])) {
				if($f_settings['fs_slider_blackfriday'] == TRUE && FLUID_BLACK_FRIDAY == FALSE)
					$file_contents = str_replace("define('FLUID_BLACK_FRIDAY', FALSE);","define('FLUID_BLACK_FRIDAY', TRUE);",$file_contents);
				else if($f_settings['fs_slider_blackfriday'] == FALSE && FLUID_BLACK_FRIDAY == TRUE)
					$file_contents = str_replace("define('FLUID_BLACK_FRIDAY', TRUE);","define('FLUID_BLACK_FRIDAY', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_slider_blackfriday_text'])) {
			if(base64_encode($f_settings['fs_slider_blackfriday_text']) != FLUID_BLACK_FRIDAY_MESSAGE_HEADER)
				$file_contents = str_replace("define('FLUID_BLACK_FRIDAY_MESSAGE_HEADER', '" . FLUID_BLACK_FRIDAY_MESSAGE_HEADER . "');","define('FLUID_BLACK_FRIDAY_MESSAGE_HEADER', '" . $f_settings['fs_slider_blackfriday_text'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_slider_blackfriday_button'])) {
			if(base64_encode($f_settings['fs_slider_blackfriday_button']) != FLUID_BLACK_FRIDAY_BUTTON)
				$file_contents = str_replace("define('FLUID_BLACK_FRIDAY_BUTTON', '" . FLUID_BLACK_FRIDAY_BUTTON . "');","define('FLUID_BLACK_FRIDAY_BUTTON', '" . $f_settings['fs_slider_blackfriday_button'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_category_position'])) {
			if($f_settings['fs_category_position'] != FLUID_CATEGORIES_POSITION)
				$file_contents = str_replace("define('FLUID_CATEGORIES_POSITION', '" . FLUID_CATEGORIES_POSITION . "');","define('FLUID_CATEGORIES_POSITION', '" . $f_settings['fs_category_position'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_store_message_enabled'])) {
			if(isset($f_settings['fs_store_message_enabled'])) {
				if($f_settings['fs_store_message_enabled'] == TRUE && FLUID_STORE_MESSAGE_ENABLED == FALSE)
					$file_contents = str_replace("define('FLUID_STORE_MESSAGE_ENABLED', FALSE);","define('FLUID_STORE_MESSAGE_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_store_message_enabled'] == FALSE && FLUID_STORE_MESSAGE_ENABLED == TRUE)
					$file_contents = str_replace("define('FLUID_STORE_MESSAGE_ENABLED', TRUE);","define('FLUID_STORE_MESSAGE_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_store_message'])) {
			if(base64_encode($f_settings['fs_store_message']) != FLUID_STORE_MESSAGE)
				$file_contents = str_replace("define('FLUID_STORE_MESSAGE', '" . FLUID_STORE_MESSAGE . "');","define('FLUID_STORE_MESSAGE', '" . $f_settings['fs_store_message'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_store_message_enabled_modal'])) {
			if(isset($f_settings['fs_store_message_enabled_modal'])) {
				if($f_settings['fs_store_message_enabled_modal'] == TRUE && FLUID_STORE_MESSAGE_MODAL_ENABLED == FALSE)
					$file_contents = str_replace("define('FLUID_STORE_MESSAGE_MODAL_ENABLED', FALSE);","define('FLUID_STORE_MESSAGE_MODAL_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_store_message_enabled_modal'] == FALSE && FLUID_STORE_MESSAGE_MODAL_ENABLED == TRUE)
					$file_contents = str_replace("define('FLUID_STORE_MESSAGE_MODAL_ENABLED', TRUE);","define('FLUID_STORE_MESSAGE_MODAL_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_store_message_modal'])) {
			if(base64_encode($f_settings['fs_store_message_modal']) != FLUID_STORE_MESSAGE_MODAL)
				$file_contents = str_replace("define('FLUID_STORE_MESSAGE_MODAL', '" . FLUID_STORE_MESSAGE_MODAL . "');","define('FLUID_STORE_MESSAGE_MODAL', '" . $f_settings['fs_store_message_modal'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_free_shipping_oversized'])) {
			if(isset($f_settings['fs_free_shipping_oversized'])) {
				if($f_settings['fs_free_shipping_oversized'] == TRUE && FREE_SHIPPING_OVERSIZED_ENABLED == FALSE)
					$file_contents = str_replace("define('FREE_SHIPPING_OVERSIZED_ENABLED', FALSE);","define('FREE_SHIPPING_OVERSIZED_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_free_shipping_oversized'] == FALSE && FREE_SHIPPING_OVERSIZED_ENABLED == TRUE)
					$file_contents = str_replace("define('FREE_SHIPPING_OVERSIZED_ENABLED', TRUE);","define('FREE_SHIPPING_OVERSIZED_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_free_shipping_special'])) {
			if(isset($f_settings['fs_free_shipping_special'])) {
				if($f_settings['fs_free_shipping_special'] == TRUE && FREE_SHIPPING_SPECIAL_ENABLED == FALSE)
					$file_contents = str_replace("define('FREE_SHIPPING_SPECIAL_ENABLED', FALSE);","define('FREE_SHIPPING_SPECIAL_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_free_shipping_special'] == FALSE && FREE_SHIPPING_SPECIAL_ENABLED == TRUE)
					$file_contents = str_replace("define('FREE_SHIPPING_SPECIAL_ENABLED', TRUE);","define('FREE_SHIPPING_SPECIAL_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_free_shipping_stock'])) {
			if(isset($f_settings['fs_free_shipping_stock'])) {
				if($f_settings['fs_free_shipping_stock'] == TRUE && FREE_SHIPPING_NOT_ENOUGH_STOCK == FALSE)
					$file_contents = str_replace("define('FREE_SHIPPING_NOT_ENOUGH_STOCK', FALSE);","define('FREE_SHIPPING_NOT_ENOUGH_STOCK', TRUE);",$file_contents);
				else if($f_settings['fs_free_shipping_stock'] == FALSE && FREE_SHIPPING_NOT_ENOUGH_STOCK == TRUE)
					$file_contents = str_replace("define('FREE_SHIPPING_NOT_ENOUGH_STOCK', TRUE);","define('FREE_SHIPPING_NOT_ENOUGH_STOCK', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_shipping_non_billing'])) {
			if(isset($f_settings['fs_shipping_non_billing'])) {
				if($f_settings['fs_shipping_non_billing'] == TRUE && FLUID_SHIP_NON_BILLING == FALSE)
					$file_contents = str_replace("define('FLUID_SHIP_NON_BILLING', FALSE);","define('FLUID_SHIP_NON_BILLING', TRUE);",$file_contents);
				else if($f_settings['fs_shipping_non_billing'] == FALSE && FLUID_SHIP_NON_BILLING == TRUE)
					$file_contents = str_replace("define('FLUID_SHIP_NON_BILLING', TRUE);","define('FLUID_SHIP_NON_BILLING', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_banners_enabled'])) {
			if(isset($f_settings['fs_banners_enabled'])) {
				if($f_settings['fs_banners_enabled'] == TRUE && FLUID_BANNERS_ENABLED == FALSE)
					$file_contents = str_replace("define('FLUID_BANNERS_ENABLED', FALSE);","define('FLUID_BANNERS_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_banners_enabled'] == FALSE && FLUID_BANNERS_ENABLED == TRUE)
					$file_contents = str_replace("define('FLUID_BANNERS_ENABLED', TRUE);","define('FLUID_BANNERS_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_categories_enabled'])) {
			if(isset($f_settings['fs_categories_enabled'])) {
				if($f_settings['fs_categories_enabled'] == TRUE && FLUID_CATEGORIES_ENABLED == FALSE)
					$file_contents = str_replace("define('FLUID_CATEGORIES_ENABLED', FALSE);","define('FLUID_CATEGORIES_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_categories_enabled'] == FALSE && FLUID_CATEGORIES_ENABLED == TRUE)
					$file_contents = str_replace("define('FLUID_CATEGORIES_ENABLED', TRUE);","define('FLUID_CATEGORIES_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_infinite_enabled'])) {
			if(isset($f_settings['fs_infinite_enabled'])) {
				if($f_settings['fs_infinite_enabled'] == TRUE && FLUID_LISTING_INFINITE_SCROLLING == FALSE)
					$file_contents = str_replace("define('FLUID_LISTING_INFINITE_SCROLLING', FALSE);","define('FLUID_LISTING_INFINITE_SCROLLING', TRUE);",$file_contents);
				else if($f_settings['fs_infinite_enabled'] == FALSE && FLUID_LISTING_INFINITE_SCROLLING == TRUE)
					$file_contents = str_replace("define('FLUID_LISTING_INFINITE_SCROLLING', TRUE);","define('FLUID_LISTING_INFINITE_SCROLLING', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_p_exclusions'])) {
			$f_list = "";

			$i = 0;
			foreach($f_settings['fs_p_exclusions'] as $f_exclusions) {
				if($i > 0) {
					$f_list .= ";";
				}

				$f_list .= $f_exclusions;

				$i++;
			}

			$f_list = base64_encode($f_list);

			if($f_list != FLUID_PROVINCES_EXCLUSIONS) {
				$file_contents = str_replace("define('FLUID_PROVINCES_EXCLUSIONS', '" . FLUID_PROVINCES_EXCLUSIONS . "');","define('FLUID_PROVINCES_EXCLUSIONS', '" . $f_list . "');",$file_contents);
			}
		}

		if(isset($f_settings['fs_sms_status'])) {
			if(isset($f_settings['fs_sms_status'])) {
				if($f_settings['fs_sms_status'] == TRUE && TWILIO_ENABLED == FALSE)
					$file_contents = str_replace("define('TWILIO_ENABLED', FALSE);","define('TWILIO_ENABLED', TRUE);",$file_contents);
				else if($f_settings['fs_sms_status'] == FALSE && TWILIO_ENABLED == TRUE)
					$file_contents = str_replace("define('TWILIO_ENABLED', TRUE);","define('TWILIO_ENABLED', FALSE);",$file_contents);
			}
		}

		if(isset($f_settings['fs_sms_sid'])) {
			if($f_settings['fs_sms_sid'] != TWILIO_ACCOUNT_SID)
				$file_contents = str_replace("define('TWILIO_ACCOUNT_SID', '" . TWILIO_ACCOUNT_SID . "');","define('TWILIO_ACCOUNT_SID', '" . $f_settings['fs_sms_sid'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_sms_token'])) {
			if($f_settings['fs_sms_token'] != TWILIO_AUTH_TOKEN)
				$file_contents = str_replace("define('TWILIO_AUTH_TOKEN', '" . TWILIO_AUTH_TOKEN . "');","define('TWILIO_AUTH_TOKEN', '" . $f_settings['fs_sms_token'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_sms_number'])) {
			if($f_settings['fs_sms_number'] != TWILIO_NUMBER)
				$file_contents = str_replace("define('TWILIO_NUMBER', '" . TWILIO_NUMBER . "');","define('TWILIO_NUMBER', '" . $f_settings['fs_sms_number'] . "');",$file_contents);
		}

		if(isset($f_settings['fs_menu_alt_768'])) {
			if($f_settings['fs_menu_alt_768'] != FLUID_MENU_SIZE_ALT_768)
				$file_contents = str_replace("define('FLUID_MENU_SIZE_ALT_768', " . FLUID_MENU_SIZE_ALT_768 . ");","define('FLUID_MENU_SIZE_ALT_768', " . $f_settings['fs_menu_alt_768'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_menu_alt_992'])) {
			if($f_settings['fs_menu_alt_992'] != FLUID_MENU_SIZE_ALT_992)
				$file_contents = str_replace("define('FLUID_MENU_SIZE_ALT_992', " . FLUID_MENU_SIZE_ALT_992 . ");","define('FLUID_MENU_SIZE_ALT_992', " . $f_settings['fs_menu_alt_992'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_menu_alt_1200'])) {
			if($f_settings['fs_menu_alt_1200'] != FLUID_MENU_SIZE_ALT_1200)
				$file_contents = str_replace("define('FLUID_MENU_SIZE_ALT_1200', " . FLUID_MENU_SIZE_ALT_1200 . ");","define('FLUID_MENU_SIZE_ALT_1200', " . $f_settings['fs_menu_alt_1200'] . ");",$file_contents);
		}

		if(isset($f_settings['fs_menu_alt_1600'])) {
			if($f_settings['fs_menu_alt_1600'] != FLUID_MENU_SIZE_ALT_1600)
				$file_contents = str_replace("define('FLUID_MENU_SIZE_ALT_1600', " . FLUID_MENU_SIZE_ALT_1600 . ");","define('FLUID_MENU_SIZE_ALT_1600', " . $f_settings['fs_menu_alt_1600'] . ");",$file_contents);
		}

		file_put_contents($path_to_file, $file_contents);

		if(isset($f_settings['boxes'])) {
			$fluid->php_db_begin();

			$f_rand = rand(10000, 99999);

			$f_insert = "INSERT INTO " . TABLE_SHIPPING_BOXES . "_" . $f_rand . " (b_name, b_outer_width, b_outer_length, b_outer_depth, b_empty_weight, b_inner_width, b_inner_length, b_inner_depth, b_max_weight) VALUES ";
			$fi = 0;
			foreach($f_settings['boxes'] as $b_key => $b_data) {

				if($fi > 0)
					$f_insert .= ", ";

				$f_insert .= "('" . $fluid->php_escape_string($b_data['b_name']) . "', '" . $fluid->php_escape_string($b_data['b_outer_width']) . "', '" . $fluid->php_escape_string($b_data['b_outer_length']) . "', '" . $fluid->php_escape_string($b_data['b_outer_depth']) . "', '" . $fluid->php_escape_string($b_data['b_empty_weight']) . "', '" . $fluid->php_escape_string($b_data['b_inner_width']) . "', '" . $fluid->php_escape_string($b_data['b_inner_length']) . "', '" . $fluid->php_escape_string($b_data['b_inner_depth']) . "', '" . $fluid->php_escape_string($b_data['b_max_weight']) . "')";

				$fi++;
			}

			$f_insert .= ";";

			$f_create = "CREATE TABLE " . TABLE_SHIPPING_BOXES . "_" . $f_rand . " LIKE " . TABLE_SHIPPING_BOXES . ";";

			$fluid->php_db_query($f_create);
			$fluid->php_db_query($f_insert);
			$fluid->php_db_query("RENAME TABLE " . TABLE_SHIPPING_BOXES . " TO delete_" . $f_rand);
			$fluid->php_db_query("RENAME TABLE " . TABLE_SHIPPING_BOXES . "_" . $f_rand . " TO " . TABLE_SHIPPING_BOXES);
			$fluid->php_db_query("DROP TABLE delete_" . $f_rand);

			$fluid->php_db_commit();
		}

		if(isset($f_settings['taxes'])) {
			$fluid->php_db_begin();

			$f_rand = rand(10000, 99999);

			$f_insert = "INSERT INTO " . TABLE_TAXES . "_" . $f_rand . " (t_name, t_region, t_country, t_math) VALUES ";
			$fi = 0;
			foreach($f_settings['taxes'] as $t_key => $t_data) {

				if($fi > 0)
					$f_insert .= ", ";

				$f_insert .= "('" . $fluid->php_escape_string($t_data['t_name']) . "', '" . $fluid->php_escape_string($t_data['t_region']) . "', '" . $fluid->php_escape_string($t_data['t_country']) . "', '" . $fluid->php_escape_string($t_data['t_math']) . "')";

				$fi++;
			}

			$f_insert .= ";";

			$f_create = "CREATE TABLE " . TABLE_TAXES . "_" . $f_rand . " LIKE " . TABLE_TAXES . ";";

			$fluid->php_db_query($f_create);
			$fluid->php_db_query($f_insert);
			$fluid->php_db_query("RENAME TABLE " . TABLE_TAXES . " TO delete_" . $f_rand);
			$fluid->php_db_query("RENAME TABLE " . TABLE_TAXES . "_" . $f_rand . " TO " . TABLE_TAXES);
			$fluid->php_db_query("DROP TABLE delete_" . $f_rand);

			$fluid->php_db_commit();
		}

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_fluid_boxes_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_fluid_taxes_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

?>
