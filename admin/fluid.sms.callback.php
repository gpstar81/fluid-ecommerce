<?php
// fluid.sms.callback.php
// Michael Rajotte - 2018 Aout
// Callback SMS data from the Twilio api.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

/*
// Example callback data set from Twilio api.
Array
(
    [SmsSid] => SMd41cd6a0ad1e060d226f21ebbb40953d
    [SmsStatus] => sent
    [MessageStatus] => sent
    [To] => +17029081880
    [MessageSid] => SMd41cd6a0ad1e060d226f21ebbb40953d
    [AccountSid] => AC0b523d9f288d8c5954b6764b5ba65b06
    [From] => +17023235443
    [ApiVersion] => 2010-04-01
)
*/
   $fluid = new Fluid();
   $fluid->php_db_begin();

   // Update last message snippet, store in address book table.
   //$data_query = mysql_query("UPDATE `address_book` SET snippet = '" . $_REQUEST['Body'] . "' WHERE phone_number = '" . $_REQUEST['To'] . "'");
   $fluid->php_db_query("UPDATE `" . TABLE_SMS . "` SET sms_status = '" . $fluid->php_escape_string($_REQUEST['MessageStatus']) . "' WHERE sms_message_id = '" . $fluid->php_escape_string($_REQUEST['SmsSid']) . "'");

   $fluid->php_db_commit();
?>
