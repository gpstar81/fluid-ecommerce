<?php
// fluid.sms.receive.php
// Michael Rajotte - 2018 Aout
// fluid.sms.receive.php
// Receiving a SMS from the Twilio api.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

/*
// Example received SMS from the Twilio api.
Array
(
    [ToCountry] => US
    [ToState] => NV
    [SmsMessageSid] => SM98c4faef13c1606087cac0104f2af2fe
    [NumMedia] => 0
    [ToCity] => LAS VEGAS
    [FromZip] =>
    [SmsSid] => SM98c4faef13c1606087cac0104f2af2fe
    [FromState] => NV
    [SmsStatus] => received
    [FromCity] =>
    [Body] => test message 18
    [FromCountry] => US
    [To] => +17023235443
    [ToZip] =>
    [MessageSid] => SM98c4faef13c1606087cac0104f2af2fe
    [AccountSid] => AC0b523d9f288d8c5954b6764b5ba65b06
    [From] => +17029081880
    [ApiVersion] => 2010-04-01
)
*/
    $fluid = new Fluid();
    $fluid->php_db_begin();

    // Insert number into address book database if not exist
    $fluid->php_db_query("INSERT IGNORE INTO `" . TABLE_SMS_NUMBERS . "` (smsnum_phonenumber, smsnum_snippet, smsnum_name, smsnum_unread) VALUES ('" . $fluid->php_escape_string($_REQUEST['From']) . "', '', '', '0')");

	$client_id = "";
	$client_name = "";
	$client_number = $_REQUEST['From'];
	$client_message = $_REQUEST['Body'];

	$fluid->php_db_query("SELECT smsnum_id, smsnum_name FROM `" . TABLE_SMS_NUMBERS . "` WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($_REQUEST['From']) . "' ORDER BY smsnum_id DESC");

    if(isset($fluid->db_array)) {
        foreach($fluid->db_array as $row2) {
    		$client_id = $row2['smsnum_id'];
    		$client_name = $row2['smsnum_name'];
    		break;
    	}
    }

    // If for some reason the phone number doesn't exist in our sms number tables, then just assign it to the zero user.
	if(strlen($client_id) < 1)
	   $client_id = 0; //$client_id = mysql_insert_id();

	$mediaurl = "";
    if(isset($_REQUEST['NumMedia'])) {
    	if($_REQUEST['NumMedia'] > 0) {
    		$path = "mms/" . $_REQUEST['MessageSid'];
    		$url = $_REQUEST['MediaUrl0'];
    		file_put_contents($path, file_get_contents($url));
    		$mediaurl = $_REQUEST['MessageSid'];
    	}
    }

   // Insert message into message database.
   $fluid->php_db_query("INSERT INTO `" . TABLE_SMS . "` (sms_num_id, sms_media_url, sms_account_id, sms_message_id, sms_status, sms_body, sms_from, sms_to) VALUES ('" . $fluid->php_escape_string($client_id) . "', '" . $fluid->php_escape_string($mediaurl) . "', '" . $fluid->php_escape_string($_REQUEST['AccountSid']) . "', '" . $fluid->php_escape_string($_REQUEST['MessageSid']) . "', '" . $fluid->php_escape_string($_REQUEST['SmsStatus']) . "', '" . $fluid->php_escape_string($_REQUEST['Body']) . "', '" . $fluid->php_escape_string($_REQUEST['From']) . "', '" . $fluid->php_escape_string($_REQUEST['To']) . "')");

   // Update last message snippet, store in address book table.
   $fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_snippet = '" . $fluid->php_escape_string($_REQUEST['Body']) . "', smsnum_unread = 1 WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($_REQUEST['From']) . "'");

   $fluid->php_db_commit();
?>
