<?php
// fluid.sms.js.php
// Michael Rajotte - 2017 Novembre
?>

<script>
var	FluidSMS = {};
	FluidSMS.panel_html = null;
	FluidSMS.sms_load_more_multiplier = 0;
	FluidSMS.f_data_tmp = null;
	FluidSMS.f_check_timer = null;
	FluidSMS.f_unread_count = 0;
	FluidSMS.f_file = null;

<?php
if($_SERVER['SERVER_NAME'] != "local.leosadmin.com") {
?>
$(document).ready(function() {
	FluidSMS.f_check_timer = setInterval(function(){js_sms_timer_refresh()},15000); // 15 seconds.
});
<?php
}
?>

function js_sms_timer_refresh() {
	try {
		var FluidData = {};

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_timer_check&data=" + data}));

		js_fluid_ajax_hidden(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_refresh_count(data) {
	try {
		if(parseInt(data['count']) > FluidSMS.f_unread_count) {
			// Need to find a way to trigger this with a user interaction for mobile devices, just once... hmmm??
			//var audio = new Audio('js/sms-notification.mp3');
			//audio.play();
		}

		FluidSMS.f_unread_count = parseInt(data['count']);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_panel_load(page, f_data) {
	try {
		FluidSMS.sms_load_more_multiplier = 0;

		var FluidData = {};
			FluidData.f_page = page;
			FluidData.f_data = f_data;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_panel_load&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_panel_load_reset(f_data) {
	try {
		FluidSMS.sms_load_more_multiplier = 0;

		var FluidData = {};
		FluidData.f_page = 1;
		FluidData.f_data = f_data;
		//FluidData.f_page_num = f_page_num;
		//FluidData.f_selection = FluidVariables.v_selection;
		//FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_panel_load&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_panel_reload(data) {
	try {
		FluidSMS.sms_load_more_multiplier = 0;

		var f_data = JSON.parse(Base64.decode(data));

		var FluidData = {};
		FluidData.mode = 'sms';
		FluidData.f_page = f_data['f_page'];
		FluidData.f_sms_search = f_data['f_sms_search'];
		//FluidData.f_page_num = f_page_num;
		//FluidData.f_selection = FluidVariables.v_selection;
		//FluidData.f_refresh = true;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		if(f_data['f_sms_search'] != null) {
			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_search&data=" + data}));
		}
		else {
			var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_panel_reload&data=" + data}));
		}

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_name_edit() {
	try {
		document.getElementById('sms-display-number-div').style.display = "none";
		document.getElementById('f-sms-name-edit-button').style.display = "none";
		document.getElementById('f-sms-name-text').style.display = "none";
		document.getElementById('f-sms-name-edit').style.display = "inline-block";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_name_edit_cancel() {
	try {
		document.getElementById('sms-display-number-div').style.display = "inline-block";
		document.getElementById('f-sms-name-edit-button').style.display = "inline-block";
		document.getElementById('f-sms-name-text').style.display = "inline-block";
		document.getElementById('f-sms-name-edit').style.display = "none";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_name_edit_save(sms_cid, f_data) {
	try {
		var FluidData = {};
			FluidData.f_client_id = sms_cid;
			FluidData.f_name = document.getElementById('f-sms-name-input-edit').value;
			FluidData.f_data64 = f_data;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_name_edit&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_sms_preset_messages_load() {
	try {
		var FluidData = {};
		//FluidSMS.sms_load_more_multiplier = 0;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_preset_messages_load&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_fluid_preset_update_message() {
	try {
		var sel = document.getElementById('presetmessages');
		var selected = sel.options[sel.selectedIndex];

		var FluidData = {};
			FluidData.f_id = selected.getAttribute('data-id');
			FluidData.f_message = document.getElementById('messageeditor').value;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_preset_messages_update&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_load_sms(f_data) {
	try {
		var FluidData = {};
		FluidData.f_data = f_data;
		FluidData.multiplier = FluidSMS.sms_load_more_multiplier;

		FluidSMS.f_data_tmp = f_data;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_load&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_load_more(mode, addsub) {
	try {
		if(addsub == 0) {
			if(FluidSMS.sms_load_more_multiplier > 0)
				FluidSMS.sms_load_more_multiplier--;
			else
				FluidSMS.sms_load_more_multiplier = 0;
		}
		else
			FluidSMS.sms_load_more_multiplier++;

		js_sms_load_sms(FluidSMS.f_data_tmp);
		document.getElementById('sms_scroll').scrollTop = 0;
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_search(mode) {
	try {
		$(document).off("keydown");
		$(document).off("keypress");

		var FluidData = {};
			FluidData.f_sms_search = document.getElementById('sms_search_sms').value;
			FluidData.mode = mode;
			FluidData.f_page = 1;

		document.getElementById('sms_search_sms').value = "";

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_search&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_set_unread(c_id, f_mode, f_data) {
	try {
		var FluidData = {};
			FluidData.f_client_id = c_id;
			FluidData.f_mode = f_mode;
			FluidData.f_data64 = f_data;

		FluidSMS.sms_load_more_multiplier = 0;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_set_unread&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_panel_reset_css() {
	try {
		document.getElementById('sms-innerhtml').className.replace( '/(?:^|\s)fluid-sms-message-innerhtml(?!\S)/g' , '' );
		document.getElementById('sms-innerhtml').className.replace( '/(?:^|\s)fluid-sms-innerhtml(?!\S)/g' , '' );

		document.getElementById('sms-innerhtml').className += " fluid-sms-innerhtml";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_message_set(f_data) {
	try {
		if(f_data['f_team'] == 'team') {
			FluidSMS.panel_html = document.getElementById('sms-panel-body').innerHTML;
			document.getElementById('sms-panel-body').innerHTML = Base64.decode(f_data['html']);
			document.getElementById('f-footer-sms').style.display = "none";

			document.getElementById('sms-panel-body').className += " fluid-sms-message-innerhtml";
		}
		else {
			FluidSMS.panel_html = document.getElementById('sms-panel-div').innerHTML;
			document.getElementById('sms-panel-div').innerHTML = Base64.decode(f_data['html']);
			document.getElementById('f-footer-sms').style.display = "none";

			document.getElementById('sms-innerhtml').className.replace( '/(?:^|\s)fluid-sms-innerhtml(?!\S)/g' , '' );
			document.getElementById('sms-innerhtml').className.replace( '/(?:^|\s)fluid-sms-message-innerhtml(?!\S)/g' , '' );

			document.getElementById('sms-innerhtml').className += " fluid-sms-message-innerhtml";
		}

		<?php // Detect a enter key on the input box of the name editor in the sms window. ?>
		var input = document.getElementById("f-sms-name-input-edit");

		input.addEventListener("keyup", function(event) {

		  event.preventDefault();
		  <?php // Number 13 is the "Enter" key on the keyboard. ?>
		  if (event.keyCode === 13) {
		    document.getElementById("f-sms-name-edit-save-btn").click();
		  }
		});

		<?php
		/*
		if(f_data['f_swiper_array'] != null) {
			var f_swiper = JSON.parse(Base64.decode(f_data['f_swiper_array']));

			for(var i = 0; i < f_swiper.length; i++) {
				var swiper = new Swiper('.swiper-container-sms-' + f_swiper[i], {
					slidesPerView: 1,
					spaceBetween: 50,
					autoResize: true,
					resizeReInit: true
				});
			}
		}
		*/
		?>
		js_update_select_pickers();

		js_sms_init_uploader("#f-csv-form");
		/*
		document.getElementById('sms-panel-div').innerHTML=XhLoadSms.responseText;
		document.getElementById('smspopup_phone_number').value = phonenumber;
		document.getElementById('smspopup_client_id').value = id;
		*/
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_init_uploader(f_data, idname, mode, company) {
	$(f_data).fileUpload({
		uploadData    : { }, <?php // Append POST data to the upload. ?>
		beforeSubmit  : function(data, textStatus, jqXHR){ FluidSMS.f_file = data['response']; return true; }, <?php // access the data returned by the upload return false to stop the submit ajax call ?>
		before		  : function(){
				js_loading_start();
			},
		success       : function(data, textStatus, jqXHR){
				js_send_sms_message();
			}, <?php // Callback for the submit success ajax call ?>
		error 	      : function(jqXHR, textStatus, errorThrown){ js_loading_stop(); console.log(errorThrown); }, <?php // Callback if an error happens with your upload call or the submit call ?>
		complete      : function(jqXHR, textStatus){ } <?php // Callback on completion ?>
	});
}

function js_send_sms(idname, mode, company) {
	try {
		//var fileSelect = document.getElementById(idname);
		//var files = fileSelect.files;

		//js_sms_init_uploader("#f-csv-form");
		//js_sms_init_uploader('#f-csv-form', idname, mode, company);

		//document.getElementById('f-csv-form').submit();
		$('#f_upload_button').click();
		//document.getElementById("sms_image_button_" + mode).value = "";
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_send_sms_message() {
	try {
		//var fileSelect = document.getElementById(idname);
		//var files = fileSelect.files;

		//console.log(fileSelect);
		//console.log(idname);
		//document.getElementById("sms_image_button_" + mode).value = "";

		var FluidData = {};
			FluidData.phone_number = document.getElementById('smspopup_phone_number').value;
			FluidData.id = document.getElementById('smspopup_client_id').value;
			FluidData.company = 0;
			FluidData.mode = 0;
			FluidData.sms_data = Base64.encode(document.getElementById('sendsms-message').value);
			FluidData.sms_load_more_multiplier = FluidSMS.sms_load_more_multiplier;
			FluidData.f_data_tmp = FluidSMS.f_data_tmp;
			FluidData.f_files = FluidSMS.f_file;

		$('#f_import_file_selected').attr('placeholder', 'Choose image');
		document.getElementById('f_csv_file_select').value = "";

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_send&data=" + data}));

		//js_loading_stop();
		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_create_message() {
	try {
		$(document).off("keydown");
		$(document).off("keypress");

		var FluidData = {};
			FluidData.phone_number = document.getElementById('f_sms_number_create').innerHTML;

		var data = base64EncodingUTF8(JSON.stringify(FluidData));

		var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_SMS_ADMIN;?>", dataobj: "load=true&function=php_sms_create_message&data=" + data}));

		js_fluid_ajax(data_obj);
	}
	catch(err) {
		js_debug_error(err);
	}
}

function js_sms_number_input_init() {
	try {
		$(document).off("keydown");
		$(document).off("keypress");

		var dials = $(".dials ol li");
	    var index;
	    var number = $(".number");
	    var total;

	    dials.click(function(){

	        index = dials.index(this);

	        if(index == 9){

	            number.append("*");

	        }else if(index == 10){

	            number.append("0");

	        }else if(index == 11){

	            number.append("#");

	        }else if(index == 12){

	            number.empty();

	        }else if(index == 13){
	            total = number.text();
	            total = total.slice(0,-1);
	            number.empty().append(total);

	        }else if(index == 14){
				js_sms_create_message();
	        }else{ number.append(index+1); }
	    });

		$(document).on("keypress", function (e) {
	        switch(e.which){
				case 43:
					number.append("+");
					break;

	            case 48:
	                number.append("0");
	                break;

	            case 49:
	                number.append("1");
	                break;

	            case 50:
	                number.append("2");
	                break;

	            case 51:
	                number.append("3");
	                break;

	            case 52:
	                number.append("4");
	                break;

	            case 53:
	                number.append("5");
	                break;

	            case 54:
	                number.append("6");
	                break;

	            case 55:
	                number.append("7");
	                break;

	            case 56:
	                number.append("8");
	                break;

	            case 57:
	                number.append("9");
	                break;
/*
	            case 8:
	                total = number.text();
	                total = total.slice(0,-1);
	                number.empty().append(total);
	                break;

	            case 27:
	                number.empty();
	                break;
*/
	            case 42:
	                number.append("*");
	                break;

	            case 35:
	                number.append("#");
	                break;

	            case 13:
	                js_sms_create_message();
	                break;

	            default: return;
	        }

	        e.preventDefault();
	    });
	}
	catch(err) {
		js_debug_error(err);
	}
}

function limitText(limitField, limitCount, limitNum) {
	/*
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
	*/
}

function limitTextTeam(limitField, limitCount, limitNum) {
	/*
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
	*/
}
</script>
