<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/jquery-3.1.1.min.js');?>"></script>
<!-- jQueryUI (necessary for .sortable) -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/jquery-ui.min.js');?>"></script>

<!-- Bootstrap transition and collapse plugins -->
<script type="text/javascript" src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/transition.js');?>"></script>
<script type="text/javascript" src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/collapse.js');?>"></script>
<!-- Moment for bootstrap datetime picker -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/moment-with-locales.min.js');?>"></script>
<!-- Bootstrap -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/bootstrap.js');?>"></script>

<!-- Carousel swipe -->
<?php //<script src="js/jquery.mobile.custom.min.js"></script>?>
<!-- Nav scroll -->
<?php //<script src="js/scrolling-nav.js"></script>?>
<!-- Bootstrap select -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/bootstrap-select.min.js');?>"></script>
<!-- Drop zone -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/dropzone.js');?>"></script>
<!-- Bootstrap datetime picker -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/bootstrap-datetimepicker.js');?>"></script>

<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fluidnote/fluidnote.js');?>"></script>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/jquery-fileupload.min.js');?>"></script>

<?php // Required for unicode base64. ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/textencoder.js');?>"></script>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/base64js.min.js');?>"></script>

<?php // <!-- Load block animation --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/anime.min.js');?>"></script>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fluid.animate.js');?>"></script>


<?php
// --> File uploader
?>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.ui.widget.js');?>"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/tmpl.min.js');?>"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/load-image.all.min.js');?>"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/canvas-to-blob.min.js');?>"></script>
<!-- blueimp Gallery script -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.blueimp-gallery.min.js');?>"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.iframe-transport.js');?>"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload.js');?>"></script>
<!-- The File Upload processing plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-process.js');?>"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-image.js');?>"></script>
<!-- The File Upload audio preview plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-audio.js');?>"></script>
<!-- The File Upload video preview plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-video.js');?>"></script>
<!-- The File Upload validation plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-validate.js');?>"></script>
<!-- The File Upload user interface plugin -->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/fileuploader/jquery.fileupload-ui.js');?>"></script>

<?php // <!-- base64 encoding library --> ?>
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/base64.min.js');?>"></script>

<!-- File downloader-->
<script src="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT_ADMIN, 'js/jquery.fileDownload.js');?>"></script>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input style="margin-left:40px;" type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
