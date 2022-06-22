<?php
// fluid.account.js.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.
?>

<script>

// The preparation for the email creator modal.
function js_modal_emails_create(data) {
	try {
		document.getElementById(data['modal']).innerHTML = Base64.decode(data['modal_html']);
		document.getElementById('email-create-information-div').innerHTML = Base64.decode(data['info_html']);
		document.getElementById('email-create-image-div').innerHTML = Base64.decode(data['image_html']);

		js_update_select_pickers();
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_load_accounts(f_page_num, mode, dmode) {
	try {
		var FluidData = {};
		FluidData.f_page_num = f_page_num;
		FluidData.f_selection = FluidVariables.v_selection;
		FluidData.f_refresh = true;
		
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT_ADMIN;?>", dataobj: "load=true&function=php_load_accounts&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_accounts_load_email_creator() {
	try {
		var FluidData = {};
			FluidData.f_email_list = FluidVariables.v_selection.p_selection;
			
		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT_ADMIN;?>", dataobj: "load=true&function=php_load_email_creator&data=" + data}));
			
		js_fluid_ajax(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_emails_send(modal) {
	try {
		var FluidEmails = {};
		FluidEmails.f_email_from = document.getElementById("email-from").value;
		FluidEmails.f_subject = document.getElementById("email-subject").value;
		FluidEmails.f_email = base64EncodingUTF8(document.getElementById("email-html").innerHTML);
		FluidEmails.f_accounts = FluidVariables.v_selection.p_selection;
		FluidEmails.f_html_email = document.getElementById("email-html-select").options[document.getElementById("email-html-select").selectedIndex].value;

		if(document.getElementById('email-recipient') != null)
			FluidEmails.f_recipient = document.getElementById('email-recipient').value;
					
		FluidEmails.f_modal = modal;
		FluidEmails.f_attach = {};
		var f_attach = document.getElementsByName('delete');

		for(var x=0; x < f_attach.length; x++) {

			if (f_attach[x].type == "checkbox") {
				if(f_attach[x].checked) {
					var tmp = f_attach[x].parentNode.parentNode;

					var f_element = tmp.getElementsByTagName("a");
					FluidEmails.f_attach[x] = f_element[0].getAttribute('download');
					
					f_element.innerHTML = "";
				}
			}
		}
		
		var data = base64EncodingUTF8(JSON.stringify(FluidEmails));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT_ADMIN;?>", dataobj: "load=true&function=php_send_email&data=" + data}));
			
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

<?php // --> Sends a email update to the user on there order status ?>
function js_emails_confirm(modal) {
	try {
		var FluidEmails = {};
		FluidEmails.f_subject = document.getElementById("email-subject").value;
		FluidEmails.f_email = base64EncodingUTF8(document.getElementById("email-html").innerHTML);
		FluidEmails.f_accounts = FluidVariables.v_selection.p_selection;
		
		if(modal != null)
			FluidEmails.f_modal = modal;
		
		if(document.getElementById('email-recipient') != null)
			FluidEmails.f_recipient = document.getElementById('email-recipient').value;
			
		FluidEmails.f_attach = {};
		var f_attach = document.getElementsByName('delete');

		for(var x=0; x < f_attach.length; x++) {

			if (f_attach[x].type == "checkbox") {
				if(f_attach[x].checked) {
					var tmp = f_attach[x].parentNode.parentNode;

					var f_element = tmp.getElementsByTagName("a");
					
					<?php //console.log(f_element[0].getAttribute('download')); ?>
					FluidEmails.f_attach[x] = f_element[0].getAttribute('download');
					
					f_element.innerHTML = "";
				}
			}
		}
		
		var data = Base64.encode(JSON.stringify(FluidEmails));
		
		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT_ADMIN;?>", dataobj: "load=true&function=php_email_confirm&data=" + data}));

		js_fluid_ajax(data_obj);		
	}
	catch(err) {
		js_debug_error(err);
	}
}		
</script>
