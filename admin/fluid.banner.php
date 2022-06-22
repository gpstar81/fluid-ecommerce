<?php
// fluid.banner.php
// Michael Rajotte - 2017 Aout
// Loads ajax php code.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load']))
	if(function_exists($_REQUEST['function']))
		echo call_user_func($_REQUEST['function']);
	else
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");


function php_load_banners($data = NULL) {
	$fluid = new Fluid();
	try {
		$f_tmp_count = 0;
		if(isset($data)) {
			$tmp_array = $data['tmp_array'];
		}
		else {

			$fluid->php_db_begin();

			$fluid->php_db_query("SELECT COUNT(*) AS tmp_b_count FROM " . TABLE_BANNERS);

			if(isset($fluid->db_array))
				$f_tmp_count = $fluid->db_array[0]['tmp_b_count'];

			$fluid->php_db_query("SELECT * FROM " . TABLE_BANNERS . " ORDER BY b_sortorder ASC"); // --> May need to put a LIMIT on this in the future to not overload the system when loading?
			$tmp_array = Array();
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value)
					$tmp_array[] = $value;
			}
		}

		if(isset($_REQUEST['selection']))
			$selection_data = $_REQUEST['selection'];
		else
			$selection_data = NULL;

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-banners'><li>";
				$return .= "<div id='category-a-banners' style='height: 40px;' class='list-group-item'>";
					$return .= "<span id='category-badge-count-banners' class='badge'>" . $f_tmp_count . "</span>";
					$return .= "<span id='category-badge-select-count-banners' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-banners' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-banners' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Banners</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div id='category-div-banners'>";
			$return .= php_html_banners($tmp_array, $selection_data, "banners");
			$return .= "</div>";

		$return .= "</div>";

		$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
		$breadcrumbs .= "<li class='active'>Banners</li>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[]['function'] = "js_clear_fluid_selection";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_html_style_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

		$sort_return['categories'][base64_encode('banners')]['div'] = base64_encode("#cat-banners");
		$execute_functions[]['function'] = "js_sortable_banners";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($sort_return));

		if(!isset($data))
			$fluid->php_db_commit();

		return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input("banners")), "navbarright" => base64_encode(php_html_navbar_right("banners")), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// This takes a array of product data and displays them in a neatly formatted html table.
function php_html_banners($data_array, $selection_array = NULL, $mode = NULL) {
	$fluid = new Fluid();
	try {
		$fluid_mode = new Fluid_Mode($mode);

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach(json_decode(base64_decode($selection_array)) as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		// Used for passing the orders in to the select all products js functions.
		$tmp_product_array = Array();
		$p_catmfgid = NULL;

		if(isset($data_array)) {
			//if(isset($data_array[0]) && $fluid_mode->mode != "orders")
				//$p_catmfgid = (int)$data_array[0][$fluid_mode->p_catmfg_id];
			//else
				$p_catmfgid = $fluid_mode->mode;

			foreach($data_array as $value)
				$tmp_product_array[$value['b_id']] = 1;//$value['p_enable']; --> Perhaps use $value['s_status'] instead?
		}

		$return = "<div class='table-responsive panel panel-default'>";

		/*
		$select_button = "<div class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
		  Select <span class='caret'></span>
		</a>
		  <ul class='dropdown-menu' aria-labelledby='dropdownMenu1'>
			<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all</a></li>
			<li><a onClick='js_select_clear_p_selection_category(\"" . base64_encode(json_encode(array($p_catmfgid))) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-minus\" aria-hidden=\"true\"></span> Un-select all</a></li>
		  </ul>
		  </div>";
		*/

		$return .= "<table class='table table-hover' id='cat-" . $p_catmfgid . "'>";

		if(count($data_array) == 0)
			$return .= "<tr><td>" . $fluid_mode->msg_no_products . "</td></tr>";
		else {
			$return .= "<thead>";
			$return .= "<tr style='font-weight: bold;'>";

			//$return .= "<td style='text-align:center;'>" . $select_button . "</td>";

			$return .= "<td></td><td style='text-align:center;'>Status</td><td style='text-align:center;'>Name</td><td style='text-align:center;'>Timer Delay (ms)</td></tr>";
			$return .= "</thead>";
			$return .= "<tbody class='fsortable-front-banners'>";

			$return .= php_html_order_rows($data_array, $selection_array, $mode);

			$return .= "</tbody>";
		}

		$return .= "</table>";
		$return .= "</div>";

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Generates each individual row of data for orders.
function php_html_order_rows($data_array = NULL, $selection_array = NULL, $mode = NULL, $td_only = FALSE) {
	try {
		$fluid = new Fluid();
		$return = NULL;

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach(json_decode(base64_decode($selection_array)) as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		foreach($data_array as $value) {
			if(in_array($value['b_id'], $tmp_selection_array)) {
				$d_colour = " data-colour='transparent';";

				if($value['b_enable'] == 0)
					$d_colour = " data-colour='" . COLOUR_DISABLED_ITEMS . "';";
				else
					$d_colour = " data-colour='transparent';";

				$style = "style='font-style: italic; background-color: " . COLOUR_SELECTED_ITEMS . ";' " . $d_colour;
				$checked = "checked";
			}
			else {
				$d_colour = " data-colour='transparent';";
				$o_colour = "transparent";
				if($value['b_enable'] == 0) {
					$d_colour = " data-colour='" . COLOUR_DISABLED_ITEMS . "';";
					$o_colour = COLOUR_DISABLED_ITEMS;
				}
				else
					$d_colour = " data-colour='transparent';";

				$style = "style='background-color: " . $o_colour . ";' " . $d_colour;
				$checked = "";
			}

			$p_catmfgid = $mode;

			if($td_only == FALSE)
				$return .= "<tr id='p_id_tr_" . $value['b_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_cancel_event(event); js_banners_select(\"" . $value['b_id'] . "\", \"" . $p_catmfgid . "\", \"" . $value['b_enable'] . "\");'>";

			// Hide this column. It contains data used for sortable product updates.
			$return .= "<td class='f-td' style='display:none;' id='p_id_tr_" . $value['b_id'] . "_td'>" . base64_encode(json_encode(Array('b_id' => $value['b_id'], 'b_sortorder' => $value['b_sortorder']))) . "</td>";

			$return .= "<td class='f-td' data-move='p_id_tr_" . $value['b_id'] . "' style='text-align:center; vertical-align: middle;'><span style='font-size: 16px;' class='glyphicon glyphicon-move moverow' aria-hidden='true'></span></td>";

			$b_status = "<div style='color: red;'>Disabled</div>";
			if($value['b_enable'] == 1)
				$b_status = "<div style='color: green'>Active</div>";

			$return .= "<td id='bo-td-status-" . $value['b_id'] . "' style='text-align:center;'>" . $b_status . "</td><td style='text-align:center;'>" . $value['b_title'] . "</td><td style='text-align:center;'>" . $value['b_timer'] . "</td>";

			$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners_creator&data=" . json_encode(base64_encode($value['b_id'])))));

			$return .= "<td style='text-align:center;'><button type='button' class='btn btn-primary' onClick='js_cancel_event(event); js_fluid_ajax(\"" . $temp_url . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> <div class='f-btn-text'>Edit</div></button></td>";

			if($td_only == FALSE)
				$return .= "</tr>";
		}

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sortable_banners_update() {
	$fluid = new Fluid ();

	try {
		$data = json_decode(base64_decode($_REQUEST['data']));

		$new_pos = $_REQUEST['newpos'] + 1; // Auto increment does not accept zero.

		// --> Auto increment does not accept zero.
		if($new_pos < 1)
			$new_pos = 1;

		$fluid->php_db_begin();

		$rand = rand(10000, 99999);
		$rand_2 = rand(10000, 99999);

		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " (b_id int not null, b_sortorder int not null auto_increment, primary key (b_sortorder))");
		$fluid->php_db_query("INSERT INTO temp_table_" . $rand . " (b_id) SELECT b_id FROM " . TABLE_BANNERS);

		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " CHANGE `b_sortorder` `b_sortorder` INT(11) NOT NULL");
		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " DROP PRIMARY KEY, ADD PRIMARY KEY(`b_id`)");

		// --> Re-organise the order of the other items in the table and make a slot for the item we are trying to move.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET b_sortorder = b_sortorder - 1 WHERE b_sortorder > '" . $fluid->php_escape_string($data->b_sortorder) . "'");
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET b_sortorder = b_sortorder + 1 WHERE b_sortorder >= '" . $fluid->php_escape_string($new_pos) . "'");

		// --> Set the order on the item we are trying to set.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET b_sortorder = '" . $fluid->php_escape_string($new_pos) . "' WHERE b_id = '" . $fluid->php_escape_string($data->b_id) . "'");

		// --> Create a clean set of b_sortorder numbers.
		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand_2 . " (b_id int not null, b_sortorder int not null auto_increment, primary key (b_sortorder))");
		$fluid->php_db_query("INSERT INTO temp_table_" . $rand_2 . " (b_id) SELECT b_id FROM temp_table_" . $rand . " ORDER BY temp_table_" . $rand . ".b_sortorder ASC");

		// Merge the data back into the table.
		$fluid->php_db_query("UPDATE " . TABLE_BANNERS . " dest, (SELECT * FROM temp_table_" . $rand_2 . ") src SET dest.b_sortorder = src.b_sortorder WHERE dest.b_id = src.b_id");

		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);
		$fluid->php_db_query("DROP TABLE temp_table_" . $rand_2);

		$fluid->php_db_commit();

		// Refresh the banner listings.
		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners")));
		$execute_functions[]['function'] = "js_banners_refresh";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_data));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_banners_creator() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = "banners";

		$b_id = NULL;
		if(isset($_REQUEST['data']))
			$b_id = json_decode(base64_decode($_REQUEST['data']));

		// Editor mode.
		if(isset($b_id)) {
			$editor = TRUE;
			$fluid->php_db_query("SELECT * FROM " . TABLE_BANNERS . "  WHERE b_id = '" . $fluid->php_escape_string($b_id) . "'");
			$data = $fluid->db_array[0];

			$modal_title = $data['b_title'];
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_banners_create_and_edit(\"edit\", \"" . $mode . "\");' ><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save Changes</button></div>";
		}
		else { // Creation mode.
			$editor = FALSE;
			$data = NULL;
			$modal_title = "Banner Creator";
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-primary' onClick='js_banners_create_and_edit(\"add\", \"" . $mode . "\");' >Add Banner</button></div>";
		}

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div style='display: inline-block; width: 100%;' class='panel-heading'>" . $modal_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='productcreatetabs'>
						<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateinformation' data-target='#productcreateinformation' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Information</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateimages' data-target='#productcreateimages' data-toggle='tab'><span class='glyphicon glyphicon-picture'></span> File Uploader</a></li>
					</ul>


				<div id='product-create-innerhtml' class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>

					<div id='productcreateevents' class='tab-content'>
						<div id='productcreateinformation' class='tab-pane fade in active'>
							<div id='product-create-information-div' style='margin-left:10px; margin-right: 10px;'></div>
						</div>

						<div id='productcreateimages' class='tab-pane fade in'>
							<div id='product-create-image-div' style='margin-right: 10px; margin-left:10px;'></div>
						</div>
					</div>

				</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>" . $modal_footer_confirm_button_html . "
			  </div>

			</div>
		  </div>";

		$image_html = '
		<div style="padding-top: 20px;">
    <form id="fileuploader" action="" method="POST" enctype="multipart/form-data">
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start upload</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel upload</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" class="toggle">
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
    </div>


		';

		$fluid->php_db_commit();

		if(count($fluid->db_error) < 1)
			$data_array = php_html_banners_editor($data, $editor, $mode);
		else
			$data_array['html'] = NULL;

		$execute_functions[]['function'] = "js_modal_banners_create_and_edit";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']	= base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "info_html" => $data_array['html'], "image_html" => base64_encode($image_html))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_banners_uploader";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		restore_error_handler();
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_html_banners_editor($data, $editor, $mode = NULL, $multi_mode = FALSE) {
	$fluid = new Fluid ();

	try {
		$output = "<div style='margin-top:15px;'>";

			// Product id
			if($editor || $multi_mode == TRUE) {
				$output .= "<input id='product-id' type='hidden' value='" . $data['b_id'] . "'>";
			}

			// Status
			$output .= "<div class=\"input-group\">";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";

				$output .= "<select id='product-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='FluidVariables.v_product.p_status =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['b_enable'] == 1)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
					$output .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['b_enable'] == 0)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
					$output .= "<span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</option>";

				$output .= "</select>";
			$output .= "</div>";

			// Banner title.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Title</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Title\" aria-describedby=\"basic-addon1\" id='banner-title'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['b_title']) . "\"";
				  $output .= ">
				</div>";

			// Swiper timer
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Timer (ms)</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Timer in milliseconds (14000 = 14 seconds)\" aria-describedby=\"basic-addon1\" id='banner-timer'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['b_timer']) . "\"";
				  $output .= ">
				</div>";


			// Banner html.
			$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
			  <div class=\"panel-heading\">
				<div style='display:inline-block;'><h5 style='font-weight:bold;'>Banner HTML</h5></div>
				<div style='float:right; display:inline-block;'>
					<button id='desc-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#banner-html\").fluidnote({height:600, focus: true}); document.getElementById(\"desc-button-save\").style.display=\"inline-block\"; document.getElementById(\"desc-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
					<button id='desc-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#banner-html\").fluidnote(\"destroy\"); document.getElementById(\"desc-button-edit\").style.display=\"inline-block\"; document.getElementById(\"desc-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
				</div>
			 </div>
				<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
					$output .= "<div class='fluid-editor-wsyg' id='banner-html' style='min-height: 600px;'>";
					if($editor)
						$output .=  utf8_decode(base64_decode($data['b_html'])); //htmlentities($data['p_desc'],ENT_QUOTES | ENT_IGNORE,'UTF-8',false);
					$output .= "</div>";
				$output .= "</div>
			</div>";
			$output .= "</div>"; // well end

			$image_array = NULL;

		$return_data_array['html'] = base64_encode($output);
		//$return_data_array['image_array'] = $image_array;
		$return_data_array['b_id'] = $data['b_id'];

		return $return_data_array;
	}
	catch (Exception $err) {
		//restore_error_handler();
		//$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_banners_create_and_edit() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));

		$mode = $_REQUEST['mode'];

		$p_timer = !empty($data->p_details) ? "'" . $fluid->php_escape_string(base64_decode($data->p_details)) . "'" : "'0'";

		if($mode == "add") {
			// Get the p_sortorder
			$fluid->php_db_query("SELECT b.b_sortorder AS tmp_b_banner_count FROM " . TABLE_BANNERS . " b ORDER BY b.b_sortorder DESC LIMIT 1");

			if(isset($fluid->db_array[0]['tmp_b_banner_count']))
				$b_sortorder = $fluid->db_array[0]['tmp_b_banner_count'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.
			else
				$b_sortorder = 1;

			$fluid->php_db_query("INSERT INTO " . TABLE_BANNERS . " (b_enable, b_title, b_html, b_timer, b_sortorder) VALUES ('" . $fluid->php_escape_string(base64_decode($data->p_status)) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_name))) . "', '" . $fluid->php_escape_string($data->p_description) . "', " . $p_timer . ", '" . $b_sortorder . "')");
		}
		else if($mode == "edit") {
			// The update query.
			$fluid->php_db_query("UPDATE " . TABLE_BANNERS . " SET `b_enable` = '" . $fluid->php_escape_string(base64_decode($data->p_status)) . "', `b_title` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_name))) . "', `b_html` = '" . $fluid->php_escape_string($data->p_description) . "', `b_timer` = " . $p_timer . " WHERE b_id = '" . $fluid->php_escape_string(base64_decode($data->p_id)) . "'");
		}

		$fluid->php_db_commit();

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		// Refresh the banner listings.
		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners")));
		$execute_functions[]['function'] = "js_banners_refresh";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_data));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err->getMessage());
	}
}


function php_load_banners_delete() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = "banners";

		// Warning Message
		$output = "<div style='margin-bottom:20px;'>";
		$output .= "<div class='alert alert-danger' role='alert'>Are you sure you want to delete the selected banners?</div>";

		// Generate query to load data of the selected items.
		$where = "WHERE b_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";
			$fluid->php_db_query("SELECT * FROM " . TABLE_BANNERS . " " . $where);

			$output .= "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
			foreach($fluid->db_array as $value) {
				$output .= $value['b_title'] . "<br>";
			}

			$output .= "</div>";

		$output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-danger' role='alert'>Are you sure?</div>");
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_banner_delete(\"" . $mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Product Deletion</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;
				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-danger' onClick='js_modal_confirm(atob(\"" . base64_encode('#fluid-modal') . "\"), atob(\"" . $confirm_message . "\"), atob(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_banners_delete() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		// $cat_refresh is sent back to fluid.js for refresh the displayed listings. Category or Manufacturer depending on which is being viewed.
		$cat_refresh = Array();
		$where = "WHERE b_id IN (";
		$i = 0;
		foreach($data as $product) {
			$cat_refresh[$product->p_catid] = $product->p_catid;

			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		// Delete the selected items.
		$fluid->php_db_query("DELETE FROM " . TABLE_BANNERS . " " . $where);

		$rand = rand(10000, 99999);
		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " (b_id int not null, b_sortorder int not null auto_increment, primary key (b_sortorder))");
		$fluid->php_db_query("INSERT INTO temp_table_" . $rand . " (b_id) (SELECT b_id FROM " . TABLE_BANNERS . " ORDER BY b_sortorder ASC)");

		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " CHANGE `b_sortorder` `b_sortorder` INT(11) NOT NULL");
		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " DROP PRIMARY KEY, ADD PRIMARY KEY(`b_id`)");

		// Merge the data back into the table.
		$fluid->php_db_query("UPDATE " . TABLE_BANNERS . " dest, (SELECT * FROM temp_table_" . $rand . ") src SET dest.b_sortorder = src.b_sortorder WHERE dest.b_id = src.b_id");
		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

		//$execute_functions[]['function'] = "js_select_clear_p_selection";
		//end($execute_functions);
		//$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("p_id_"));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		// Refresh the banner listings.
		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners")));
		$execute_functions[]['function'] = "js_banners_refresh";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_data));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

?>
