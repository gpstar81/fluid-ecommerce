<?php
// Michael Rajotte - 2016 June
// fluid.required.php
// Fluid required file.

	require_once(__DIR__ . "/../fluid.db.php");
	require_once(__DIR__ . "/fluid.compatibility.php");

	ini_set("date.timezone", SERVER_TIMEZONE); // --> Set the server timezone.

	define('FLUID_HTTP', "http://");

	// Google api id for Google login:
	require_once("3rd-party-src/google-api/Google_Client.php"); // requires php-curl
	require_once("3rd-party-src/google-api/contrib/Google_Oauth2Service.php");	// requires php-curl
	define('GOOGLE_WWW_FLUID_ACCOUNT', WWW_SITE . 'fluid.account.php?load_func=true&fluid_function=php_fluid_login');

	// Facebook api id for Facebook login:
	require_once("3rd-party-src/facebook-api/autoload.php");
	define('FACEBOOK_CLIENT_LOGIN_REDIRECT', WWW_SITE . 'fluid.account.php?load_func&fluid_function=php_fluid_login&oauth_provider=facebook');

	// Mobile detect library api. Used for detecting mobiles, tablets and other devices.
	require_once(__DIR__ . "/3rd-party-src/mobiledetect-api/Mobile_Detect.php");

	// Math equation class. Can convert strings into equations.
	require_once(__DIR__ . "/3rd-party-src/eos-class/eos.class.php");

	//escpos-php Epson printer api. --> https://github.com/mike42/escpos-php
	require_once(__DIR__ . "/3rd-party-src/escpos-api/vendor/autoload.php");

	// math-parser-api --> Used by the cart for evaluating math expressions safely.
	require_once(__DIR__ . "/3rd-party-src/math-parser-api/vendor/autoload.php");

	// Bad words api  --> Used by the live search logs.
	require_once(__DIR__ . "/3rd-party-src/badwords-api/vendor/autoload.php");

	// barcode generator.
	require_once(__DIR__ . '/3rd-party-src/php-barcode-generator/src/BarcodeGenerator.php');
	require_once(__DIR__ . '/3rd-party-src/php-barcode-generator/src/BarcodeGeneratorPNG.php');
	require_once(__DIR__ . '/3rd-party-src/php-barcode-generator/src/BarcodeGeneratorSVG.php');
	require_once(__DIR__ . '/3rd-party-src/php-barcode-generator/src/BarcodeGeneratorJPG.php');
	require_once(__DIR__ . '/3rd-party-src/php-barcode-generator/src/BarcodeGeneratorHTML.php');

	// libphonenumber library. Checks if phone number is valid.
	require_once(__DIR__ . '/3rd-party-src/libphonenumber/vendor/autoload.php');

	// Moneris api.
	define('MONERIS_API', __DIR__ . '/3rd-party-src/moneris-api/lib/Moneris.php');

	// PayPal
	require_once(__DIR__ . "/3rd-party-src/paypal-api/vendor/autoload.php");

	// Authorize.net api.
	require_once(__DIR__ . "/3rd-party-src/authorize-net-api/vendor/autoload.php");
	
	// Twilo api
	require_once(__DIR__ . "/3rd-party-src/twilo-api/Services/Twilio.php");

	//HTML2pdf api.
	require_once(__DIR__ . "/3rd-party-src/html2pdf-api/html2pdf.class.php");

	// Used in account and session oauth checking.
	define('OAUTH_FLUID', 'fluid');
	define('OAUTH_GOOGLE', 'google');
	define('OAUTH_FACEBOOK', 'facebook');

	// Table names in the database.
	define('TABLE_CATEGORIES', 'categories');
	define('TABLE_BANNERS', 'banners');
	define('TABLE_PRODUCTS', 'products');
	define('TABLE_PRODUCT_CATEGORY_LINKING', 'product_category_linking');
	define('TABLE_PRODUCT_COMPONENT', 'product_component');
	define('TABLE_MANUFACTURERS', 'manufacturers');
	define('TABLE_FILTER_KEYS_CATEGORIES', 'filter_keys_categories');
	define('TABLE_FILTER_KEYS_MANUFACTURERS', 'filter_keys_manufacturers');
	define('TABLE_USERS', 'users');
	define('TABLE_USERS_ADMIN', 'users_admin');
	define('TABLE_ADDRESS_BOOK', 'address_book');
	define('TABLE_CART_PERSISTENCE', 'cart_persistence');
	define('TABLE_TAXES', 'taxes');
	define('TABLE_SALES', 'sales');
	define('TABLE_SALES_ITEMS', 'sales_items');
	define('TABLE_SALES_TRANSACTIONS', 'sales_transactions');
	define('TABLE_IMPORT_STAGING', 'import_staging');
	define('TABLE_SHIPPING_BOXES', 'shipping_boxes');
	define('TABLE_LOGS', 'logs');
	define('TABLE_FEEDBACK', 'feedback');
	define('TABLE_SMS', 'sms_messages');
	define('TABLE_SMS_NUMBERS', 'sms_numbers');
	define('TABLE_LIVE_SEARCH_CACHE', 'live_search_cache'); // A table with manually updated cached suggestions. This should be refreshed daily in the settings menu.

	// Some php files.
	define('FLUID_LOADER', 'fluid.loader.php'); // The fluid loader for administration.
	define('FLUID_BANNER', 'fluid.banner.php'); // The fluid banner editor for administration.
	define('FLUID_POS', 'fluid.pos.php'); // The fluid pos system for administration.
	define('FLUID_ACCOUNT', 'fluid.account.php'); // Fluid account.
	define('FLUID_FEEDBACK_ADMIN', 'fluid.feedback.php'); // The fluid feedback system for administration.
	define('FLUID_ACCOUNT_ADMIN', 'fluid.account.php'); // The fluid account system for administration.
	define('FLUID_SELECTOR_ADMIN', 'fluid.selector.php'); // The fluid item selector for administration.
	define('FLUID_LOGS_ADMIN', 'fluid.logs.php'); // The log viewing system for administration.
	define('FLUID_EXPORT_ADMIN', 'fluid.export.php'); // The log viewing system for administration.
	define('FLUID_IMPORT_ADMIN', 'fluid.import.php'); // The import select system for administration.
	define('FLUID_SETTINGS_ADMIN', 'fluid.settings.php'); // The settings module for administration.
	define('FLUID_ATTRIBUTES_ADMIN', 'fluid.attributes.php'); // The attribute module for administration.
	define('FLUID_LOGIN_ADMIN', 'fluid.admin.login.php'); // The login and logout module for administration.
	define('FLUID_ORDERS_ADMIN', 'fluid.orders.php'); // The settings module for administration.
	define('FLUID_BARCODE_ADMIN', 'fluid.barcode.php'); // The barcode export module for administration. This is different from the Settings barcode generator. They are not related.
	define('FLUID_SMS_ADMIN', 'fluid.sms.php'); // The fluid SMS system.
	define('FLUID_SMS_CALLBACK', 'fluid.sms.callback.php'); // The callback SMS api.
	define('FLUID_SMS_RECEIVE', 'fluid.sms.receive.php'); // Receiving a SMS.
	define('FLUID_SMS_UPLOADS', 'fluid.sms.uploads.php'); // Handling of file uploads for SMS messages.

	define('FLUID_ITEM_LISTING', 'fluid.listing.php');
	define('FLUID_ITEM_VIEW', 'fluid.item.php');
	define('FLUID_CART', 'fluid.cart.php');
	define('FLUID_FEEDBACK', 'fluid.feedback.php'); // Feedback system.
	define('FLUID_SEARCH_SUGGESTIONS', 'fluid.search.suggestions.php'); // The search suggestions module.

	// Rewrite defines.
	define('FLUID_ACCOUNT_REWRITE', 'account');
	define('FLUID_CHECKOUT_REWRITE', 'checkout');
	define('FLUID_ITEM_LISTING_REWRITE', 'category');
	define('FLUID_ITEM_VIEW_REWRITE', 'product');
	define('FLUID_SEARCH_LISTING_REWRITE', 'search');

	// www locations.
	define('WWW_FILES', 'files/');
	// Alias /images /var/www/local/fluid/htdocs/images
	define('WWW_IMAGES', 'images/');
	define('WWW_IMAGES_TEMP', 'images/temp/');
	define('WWW_IMAGES_CACHED', 'images/cached/');

	// Images
	define('IMG_NO_IMAGE', 'no-image.png');
	define('FOLDER_FILES', 'files/');

	// Colours
	define('COLOUR_DISCONTINUED_ITEMS', '#4691A6');
	define('COLOUR_DISABLED_ITEMS', '#AFAFAF');
	define('COLOUR_SELECTED_ITEMS', '#FF9292');
	define('COLOUR_SELECTED_CATEGORY', '#B1C8DB');
	define('COLOUR_MULTI_ITEM_CHANGE', '#FF9292');
	define('COLOUR_ORDER_SHIPPED', '#7AB3FF');
	define('COLOUR_ORDER_ERROR', '#F32A2B');
	define('COLOUR_ORDER_CANCELLED', '#6E6C6B');
	define('COLOUR_ORDER_REFUND', '#C6B815');
	define('COLOUR_ORDER_PREORDER', '#15C61D');

	// Order statuses
	define('ORDER_STATUS_ERROR', "error");
	define('ORDER_STATUS_PROCESSING', "Processing");
	define('ORDER_STATUS_SHIPPED', "Shipped");
	define('ORDER_STATUS_PICKUP', "Ready for pickup");
	define('ORDER_STATUS_PREORDERED', "Pre ordered");
	define('ORDER_REFUND', "Refund");
	define('ORDER_CANCELLED', "Cancelled");

	// Cookies
	define('FLUID_COOKIE', 'FLUID_REMEMBER_ME');
	define('FLUID_COOKIE_CART_PERSISTENCE', 'FLUID_CART');
	define('FLUID_COOKIE_ADMIN', 'FLUID_REMEMBER_ME_ADMIN');

	// Error message
	define('FLUID_ERROR_CHECKOUT_SESSION', 'ERROR: Session checkout mismatch error.');

	// Administration pagination limit.
	define('FLUID_ADMIN_PAGINATION_LIMIT', 30); // --> Max amount of pagination pages to show before it's cut off.
	define('FLUID_ADMIN_LISTING_LIMIT', 30); // --> How many items to show in a list before pagination starts.

	// --> Formula Links
	define('FORMULA_CART', 'FORMULA_CART');
	define('FORMULA_ITEM', 'FORMULA_ITEM');
	define('FORMULA_OPTION_1', 'FORMULA_OPTION_1');
	define('FORMULA_OPTION_2', 'FORMULA_OPTION_2');
	define('FORMULA_OPTION_3', 'FORMULA_OPTION_3');
	define('FORMULA_OPTION_4', 'FORMULA_OPTION_4');
	define('FORMULA_OPTION_5', 'FORMULA_OPTION_5');
	define('FORMULA_OPTION_6', 'FORMULA_OPTION_6');
	define('FORMULA_OPTION_7', 'FORMULA_OPTION_7');
	define('FORMULA_OPTION_8', 'FORMULA_OPTION_8');
	define('FORMULA_OPTION_9', 'FORMULA_OPTION_9');
	define('FORMULA_OPTION_10', 'FORMULA_OPTION_10');

	define("FORMULA_HTML_ITEM_SELECT_BLANK", "<select id='formula-item-list' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"></select>");
	define("FORMULA_HTML_ITEM_SELECT_BLANK_FAUX", "<select id='formula-item-list-faux' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"></select>");
	define('FORMULA_VARIABLES', Array('[PRICE]', '[DISCOUNT_PRICE]', '[STOCK]', '[COST]', '[LENGTH]', '[WIDTH]', '[HEIGHT]', '[WEIGHT]'));

	define("FLUID_HEADER_SEARCH_SPECIAL", "
	<div class=\"row row-about-leos\">
		<div>
			<p class=\"about-leos-paragraph\">Hours: Monday to Friday 10:00am - 4:30pm</p>
		</div>

		<div style='padding-top: 20px;'>
			<div id=\"map_canvas_header\" class='f-map-header-canvas'></div>
		</div>
	</div>
	");

	define("FLUID_STORE_NAME", "Leos");
	define("FLUID_LISTING_MAX_SEARCH_SUGGESTIONS", 4); // Max amount of search suggestions returns.
?>
