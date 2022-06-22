<?php
// fluid.export.js.php
// Michael Rajotte - 2017 Novembre
?>

<script>
function js_fluid_export(f_mode) {
	try {
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", "<?php echo FLUID_EXPORT_ADMIN; ?>?load=true&function=php_fluid_export");
		form.setAttribute("target", "formresult");
		var hiddenField = document.createElement("input");

		hiddenField.setAttribute("name", "data");
		hiddenField.setAttribute("value", base64EncodingUTF8(JSON.stringify(FluidVariables.v_selection.p_selection)));
		form.appendChild(hiddenField);

		var f_mode_field = document.createElement("input");
		f_mode_field.setAttribute("name", "f_mode");
		f_mode_field.setAttribute("value", f_mode);
		form.appendChild(f_mode_field);
		
		form.style.display = "none";
		document.body.appendChild(form);
		
		form.submit();

		$(form).remove();
	}
	catch(err) {
		js_debug_error(err);
	}
}
</script>
