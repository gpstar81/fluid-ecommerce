<?php
// fluid.import.php
// Michael Rajotte - 2017 Avril - 2018 Janvier
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

function php_staging_merge_confirm() {
	try {
		$fluid = new Fluid();

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Data merge</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Warning:</div>
									<div style='padding-bottom: 20px;'>You are about to merge data from the import staging area into the main database tables. It is highly suggested you double check your data before confirming the processing. Scanning the import staging data with the scan feature is helpful to find duplicate items. It is also recommended to make a backup of the database before proceeding.</div>
								</div>

								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Warning:</div>
									<div style='padding-bottom: 20px;'>p_date_discount_end is not modified if p_stock_end is set to TRUE and stock is set to 0 or is already at 0. If you have items in the database or are inserting new ones and have the p_stock_end flag set, the discount date ends will not be reset. You will need to manually go in and modify these. Otherwise the discounts may still show on items with zero stock.</div>
								</div>

								<div class='alert alert-warning' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Warning:</div>
									<div style='padding-bottom: 20px;'>It is highly suggested to set the online store to closed while updating data in the database. You can do this under the Settings section.</div>
								</div>

								<div style='font-weight: 600; padding-bottom: 5px;'>Database backup:</div>
								<div style='padding-bottom: 20px;'>It is recommended to make a backup of your database before proceeding.</div>

								<div style='font-weight: 600; padding-bottom: 5px;'>Importing tips:</div>
								<div style='padding-bottom: 20px;'>
									<div>You must set which columns you want to merge in the select dropdowns at the top of the staging table. p_mfgcode must always be set. Any other columns not set will be ignored during the merging process. </div>
									<div>When p_mfgcode is set. Existing items with the same p_mfgcode will get updated. When a p_mfgcode is not matched, then the item will be created and inserted into the selected category and manufacturer.</div>
									<div>It is highly recommended to choose a temporary empty hidden category and manufacturer when importing new data. This way you can go over the data one last time before moving the items into the proper categories and manufacturers. If you do not have a temporary category and or manufacturer created, then cancel the process and create them. Make sure to set them as hidden and all temporary sub categories and manufacturers as hidden. This only applies to items which are new and do not exist in the database. Existing found items will ignore this and be updated instead and retain there existing category and manufacturer unless selected in the header select columns. It is highly unlikely you will ever need to set the category and manufacturer settings during a data merge for existing items as it can easily be done within the item editor.</div>
								</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";

								/*
									--> Load categories and manufactuers in a <select> dropdown.
									--> A category and manufacturer must be selected. (hide p_catmfgid and p_catid) from the <select> headers on the import staging table columns.
									--> Then proceed with merge and updating.
								*/

								$fluid->php_db_begin();

								// Load the manufacturers
								$fluid->php_db_query("SELECT * FROM ". TABLE_MANUFACTURERS . " ORDER BY m_sortorder ASC");
								if(isset($fluid->db_array)) {
									if(count($fluid->db_array) > 0) {
										foreach($fluid->db_array as $key => $value) {
											if($value['m_parent_id'] == NULL)
												$manufacturer_data[$value['m_id']]['parent'] = $value;
											else
												$manufacturer_data[$value['m_parent_id']]['childs'][] = $value;
										}
									}
									else
										$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";
								}
								else
									$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";

								// Load the categories.
								$fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " ORDER BY c_sortorder ASC");
								if(isset($fluid->db_array)) {
									if(count($fluid->db_array) > 0) {
										foreach($fluid->db_array as $key => $value) {
											if($value['c_parent_id'] == NULL)
												$category_data[$value['c_id']]['parent'] = $value;
											else
												$category_data[$value['c_parent_id']]['childs'][] = $value;
										}
									}
									else
										$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";
								}
								else
									$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";

								$fluid->php_db_commit();

								$output_man = NULL;
								$i = 0;
								if(isset($manufacturer_data)) {
									foreach($manufacturer_data as $parent) {
										if(isset($parent['childs'])) {
											$output_man .= "<optgroup label='" . $parent['parent']['m_name'] . "'>";

											foreach($parent['childs'] as $value) {
												$width_height = $fluid->php_process_image_resize($fluid->php_process_images($value['m_images'])[0], "20", "20");

												$output_man .= "<option data-name=\"" . $value['m_name'] . "\" value='" . $value['m_id'] . "'";
												$output_man .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['m_name'] . "\"";

												$output_man .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['m_name'] . "</option>";

												$i++;
											}

											$output_man .= "</optgroup>";
										}
									}
								}

								$output_cat = NULL;
								$i = 0;
								if(isset($category_data)) {
									foreach($category_data as $parent) {
										if(isset($parent['childs'])) {
											$output_cat .= "<optgroup label='" . $parent['parent']['c_name'] . "'>";

											foreach($parent['childs'] as $value) {
												$width_height = $fluid->php_process_image_resize($fluid->php_process_images($value['c_image'])[0], "20", "20");

												$output_cat .= "<option value='" . $value['c_id'] . "'";
												$output_cat .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "\"";

												$output_cat .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "</option>";

												$i++;
											}

											$output_cat .= "</optgroup>";
										}
									}
								}

								$modal .= "<div style='font-weight: 600; padding-bottom: 5px;'>Select a manufacturer and category for new items:</div>";
								$modal .= "<div style='padding: 0px 0px 20px 0px;'>";
									$modal .= "<div class=\"input-group\" style='margin-top: 5px;'>";
									$modal .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Manufacturer</div></span>";

										$modal .= "<select id='fluid-staging-product-manufacturer' class=\"form-control selectpicker show-menu-arrow show-tick dropup\" data-live-search=\"true\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onChange='js_staging_merge_button_check();'>"; // Merge the filter array data to the js client call.
										$modal .= "<option value='none'>Nothing selected</option>";
										$modal .= $output_man; // Merge the manufacturer selection into the html.
										$modal .="</select>";
									$modal .= "</div>";

									$modal .= "<div class=\"input-group\" style='margin-top: 5px;'>";
									$modal .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Category</div></span>";

										$modal .= "<select id='fluid-staging-product-category' class=\"form-control selectpicker show-menu-arrow show-tick dropup\" data-live-search=\"true\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onChange='js_staging_merge_button_check();'>"; // Merge the filter array data to the js client call.
										$modal .= "<option value='none'>Nothing selected</option>";
										$modal .= $output_cat; // Merge the category selection into the html.
										$modal .="</select>";
									$modal .= "</div>";
								$modal .= "</div>";

							$modal .= "</div>
						</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer'>";

			  $footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div><div style='float:right;'><button id='fluid-staging-confirm-button' type='button' class='btn btn-success disabled' disabled onClick='if(FluidVariables.f_staging_prevent_import == false){ js_staging_merge_data(); }'><span class=\"glyphicon glyphicon-transfer\" aria-hidden=\"true\"></span> Merge data</button></div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_staging_merge_button_check";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Generates uploader CSV modal to select and prep a CSV for uploading.
function php_load_import_uploader() {
	try {
		$fluid = new Fluid();

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>CSV Uploader</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Warning:</div>
									<div style='padding-bottom: 20px;'>Importing a CSV will delete and replace the existing import staging area data.</div>
								</div>

								<div style='font-weight: 600; padding-bottom: 5px;'>Instructions:</div>
								<div>When importing a CSV file, do not have text enclosed with \". The first line in the csv file will be used for the column names in the staging table. Do not have a column name of <div style='display: inline-block; font-weight: 600; color: red;'>f_default_import_select_ignore</div> or <div style='display: inline-block; font-weight: 600; color: red;'>fluid_import_id</div> as a column name as these are pre-defined names used for handling queries. Data in this first column is not imported into the table, but only used for column names in the table. For example.</div>
								<div style='display: table; padding:10px 10px 20px 10px;'>
									<div style='display: table-row;'>
										<div style='display: table-cell; border-top: 1px solid black; border-left: 1px solid black; border-bottom: 1px solid black; padding: 5px;'>column_1</div><div style='display: table-cell; border-top: 1px solid black; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black; padding: 5px;'>column_2</div><div style='display: table-cell; border-top: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px;'>column_3</div><div style='display: table-cell; padding: 5px; color: red;'>header line 1. data not imported. Used for column table names only.</div>
									</div>
									<div style='display: table-row'>
										<div style='display: table-cell; padding: 5px; border-left: 1px solid black; border-bottom: 1px solid black;'>data 1</div><div style='display: table-cell; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px;'>data 2</div><div style='display: table-cell; padding: 5px; border-bottom: 1px solid black; border-right: 1px solid black; '>data 3</div><div style='display: table-cell; padding: 5px;'>data rows</div>
									</div>
								</div>

								<div style='font-weight: 600; padding-bottom: 5px;'>Action Menu:</div>
								<div style='padding-bottom: 20px;'>To scan the product database for matching items from your CSV file, the p_mfgcode select filter must be set first to enable scanning. Merging data is similar to product scanning, you will need to define the p_mfgcode before merging can be allowed.</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";

							$f_html = "<div class='list-group'>";

								$f_html .= "<form id=\"f-csv-form\" action=\"uploads.csv.php\" method=\"POST\">";
									$f_html .= "<label class='btn btn-success fileinput-button' style='padding: 6px 12px;'><input type=\"file\" id=\"f_csv_file_select\" name=\"f_csv_file_select\"/ onChange=\"$('#f_import_file_selected').html($(this).val());\"><i class='glyphicon glyphicon-folder-open'></i> Add File</label> <span id=\"f_import_file_selected\"></span>";
									$f_html .= "<div class=\"input-group\" style='padding-top: 10px;'>
												<span class=\"input-group-addon\"><div style='width:120px !important;'>Delimiter</div></span>
												  <input id=\"f_delimiter\" type=\"text\" class=\"form-control\" placeholder=\"Delimiter\" value=\";\">
												</div>";
									$f_html .= '<div class="input-group" style="padding-top: 10px;">
												<span class="input-group-addon"><div style="width:120px !important;">Text Delimiter</div></span>
												  <input id="f_text_delimiter" type="text" class="form-control" placeholder="Delimiter" value=\'"\'>
												</div>';
									$f_html .= "<input id=\"f_delimiter_hide\" type=\"hidden\" class=\"form-control\" value=\"" . base64_encode(json_encode(Array("delim" => ";", "delim_text" => "\""))) . "\">";

									$f_html .= "<div style='padding-top: 10px;'><button class='btn btn-primary' type=\"submit\" name='f_upload_button' id=\"f_upload_button\"><i class=\"glyphicon glyphicon-upload\"></i> Upload</button></div>";
								$f_html .= "</form>";

							$f_html .= "</div>";

							$modal .= $f_html;
							$modal .= "</div>
						</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer'>";

			  $footer_save_html = "<div style='float:right;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_fluid_init_uploader";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#f-csv-form"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Loads the CSV importing staging area.
function php_load_staging($data = NULL) {
	$fluid = new Fluid();
	try {
		$selection_data = NULL;

		$mode = "import";
		$f_scan = NULL;
		$f_refresh = NULL;
		$f_found_items = NULL;

		$fluid->php_db_begin();
		$fluid->php_db_query("SELECT * FROM " . TABLE_IMPORT_STAGING);
		$fluid->php_db_commit();

		if(isset($_REQUEST['data'])) {
			$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

			if(!empty($f_data['f_scan'])) {
				$f_scan = $f_data['f_import'];
			}
			else {
				$f_refresh = TRUE;
			}

			if(isset($f_data['s_selection'])) {
				$selection_data = json_decode(base64_decode($f_data['s_selection']));
			}

			if(isset($fluid->db_array) && ($f_data['f_column'] || $f_refresh == TRUE)) {
				if(empty($f_data['f_manufacturer'])) {
					$m_id = NULL;
				}
				else if($f_data['f_manufacturer'] == 'none') {					
					$m_id = NULL;
				}
				else {
					$m_id = $f_data['f_manufacturer'];
				}

				$f_import_staging = php_html_import_staging($fluid->db_array, $selection_data, $mode, $f_scan, $f_refresh, $f_data['f_column'], $m_id);
				$return = $f_import_staging['html'];
				$f_found_items = $f_import_staging['f_found_array'];
			}
			else {
				$return = "<tr><td>No data in staging</td></tr>";
			}
		}
		else {
			$return = "<div id='fluid-category-listing' class='list-group'>";
				$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-import'><li>";
					$return .= "<div id='category-a-import' style='height: 40px;' class='list-group-item'>";
						if(isset($fluid->db_array))
							$f_count = count($fluid->db_array);
						else
							$f_count = 0;

						$return .= "<span id='category-badge-count-import' class='badge'>" . $f_count. "</span>";
						$return .= "<span id='category-badge-select-count-import' class='badge' style='display:none;'></span>";

						$disable_style = "none";

						$return .= "<span id='category-badge-select-lock-import' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

						$return .= " <span id='category-span-open-import' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Import staging</div></span>";
					$return .= "</div>";
				$return .= "</li></ul>";

				$return .= "<div id='category-div-import'>";


			$return .= "<div class='table panel panel-default'>";

				$return .= "<table class='table table-hover' id='cat-import'>";

				$return_data = NULL;

					if(!isset($fluid->db_array))
						$return .= "<tr><td>No data in staging</td></tr>";
					else {
						$f_import_staging = php_html_import_staging($fluid->db_array, $selection_data, $mode, NULL, NULL, NULL, NULL);
						$return .= $f_import_staging['html'];
						$f_found_items = $f_import_staging['f_found_array'];
					}
					$return .= "</table>";
					$return .= "</div>";

				$return .= "</div>";

			$return .= "</div>";

			$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
			$breadcrumbs .= "<li class='active'>Import staging</li>";
		}

		if(empty($f_scan) && empty($f_refresh)) {
			$execute_functions[]['function'] = "js_clear_fluid_selection";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

			$execute_functions[]['function'] = "js_staging_items_found";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_found_items));

			$execute_functions[]['function'] = "js_html_style_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input($mode)), "navbarright" => base64_encode(php_html_navbar_right($mode)), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
		else {
			if(empty($return)) {
				return json_encode(array("error" => count(1), "error_message" => base64_encode("No matches were found.")));
			}
			else {
				$execute_functions[]['function'] = "js_staging_items_found";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_found_items));

				$execute_functions[]['function'] = "js_update_action_menu_import";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

				if(isset($fluid->db_array))
					$f_element = "tbody-import";
				else
					$f_element = "cat-import";

				$execute_functions[]['function'] = "js_html_insert_element";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode($f_element), "innerHTML" => base64_encode($return))));

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
			}
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// Remove a row or rows from the import staging table.
function php_staging_row_remove() {
	try {
		if(isset($_REQUEST['data'])) {
			$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

			if(isset($f_data['s_selection'])) {
				$f_import_data = json_decode(base64_decode($f_data['s_selection']));

				$fluid = new Fluid();
				$fluid->php_db_begin();
				$where = "WHERE fluid_import_id IN (";
				$i = 0;

				foreach($f_import_data as $import_row) {
					if($i != 0)
						$where .= ", ";

					$where .= $fluid->php_escape_string($import_row->p_id);

					$i++;
				}
				$where .= ")";

				$fluid->php_db_query("DELETE FROM " . TABLE_IMPORT_STAGING . " " . $where);

				$fluid->php_db_commit();

				$execute_functions[]['function'] = "js_clear_fluid_selection";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

				$execute_functions[]['function'] = "js_staging_select_all";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

				/*
				 // --> This will return a no matches found if there are no matches and not refresh the page. Better to just refresh it.
				if(isset($f_data['f_import']['p_mfgcode'])) {
					$execute_functions[]['function'] = "js_fluid_scan_items";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));
				}
				else {
					$execute_functions[]['function'] = "js_fluid_import_staging_refresh";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));
				}
				*/

				$execute_functions[]['function'] = "js_fluid_import_staging_refresh";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
			}
			else {
				return json_encode(array("error" => count(1), "error_message" => base64_encode("No rows were removed.")));
			}
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// Merge and update data from the staging table into the main product tables.
function php_staging_merge_data() {
	try {
		$fluid = new Fluid();
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		if(isset($f_data)) {
			if(isset($f_data['f_import']['p_mfgcode'])) {
				$fluid->php_db_begin();

				$f_columns = "SET ";
				$f_insert = "(";
				$f_insert_src = NULL;
				$f_insert_main = "(p_catid, p_mfgid,";

				if(isset($f_data['f_import']['p_cost_real']))
					$f_insert_main .= " p_cost,";

				$i = 0;
				$f_stock_update = FALSE;
				foreach($f_data['f_import'] as $f_import) {
					//if($f_import['f_column'] != "p_mfgcode") {
						if($i > 0) {
							$f_columns .= ",";
							$f_insert .= ", ";
							$f_insert_main .= ", ";
							$f_insert_src .= ", ";
						}

						$f_columns .= "dest." . $f_import['f_column'] . " = src." . $f_import['i_column'];
						$f_insert .= $f_import['f_column'];
						$f_insert_main .= $f_import['f_column'];
						$f_insert_src .= $f_import['i_column'];

						// Check to see if p_stock is getting updated, if so we will need to update the cost averages after.
						if($f_import['f_column'] == 'p_stock')
							$f_stock_update = TRUE;

						$i++;
					//}
				}
				$f_insert .= ")";
				$f_insert_main .= ")";

				$rand = rand(10000, 99999);
				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " AS SELECT * FROM " . TABLE_PRODUCTS);
				$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
				$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " MODIFY COLUMN p_id INT auto_increment primary key NOT NULL;");

				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_staging_" . $rand . " AS SELECT * FROM " . TABLE_IMPORT_STAGING);

				// Stock is getting updated, lets reset the cost average on UPDATES.
				if($f_stock_update == TRUE) {
					//$fluid->php_db_query("CREATE TEMPORARY TABLE temp_cost_" . $rand . " AS SELECT p_id, p_cost, p_cost_real, p_stock FROM " . TABLE_PRODUCTS . " );
					if(isset($f_data['f_import']['p_cost_real']))
						$fluid->php_db_query("SELECT main.p_id, main.p_cost, main.p_cost_real, main.p_stock, src." . $f_data['f_import']['p_stock']['i_column'] . ", src." . $f_data['f_import']['p_cost_real']['i_column'] . " FROM temp_table_" . $rand . " main, temp_table_staging_" . $rand . " src WHERE " . $f_data['f_import']['p_mfgcode']['f_column'] . " = " . $f_data['f_import']['p_mfgcode']['i_column']);
					else
						$fluid->php_db_query("SELECT main.p_id, main.p_cost, main.p_cost_real, main.p_stock, src." . $f_data['f_import']['p_stock']['i_column'] . " FROM temp_table_" . $rand . " main, temp_table_staging_" . $rand . " src WHERE " . $f_data['f_import']['p_mfgcode']['f_column'] . " = " . $f_data['f_import']['p_mfgcode']['i_column']);

					if(isset($fluid->db_array)) {
						$f_case = NULL;
						$f_where = NULL;
						$i = 0;
						foreach($fluid->db_array as $s_data) {
							$o_data['old_cost'] = $s_data['p_cost_real'];
							$o_data['old_stock'] = $s_data['p_stock'];
							$o_data['old_cost_avg'] = $s_data['p_cost'];

							$n_data['new_stock'] = $s_data[$f_data['f_import']['p_stock']['i_column']];

							if(isset($f_data['f_import']['p_cost_real']))
								$n_data['new_cost'] = $s_data[$f_data['f_import']['p_cost_real']['i_column']];
							else
								$n_data['new_cost'] = $s_data['p_cost_real'];

							$p_cost_average = $fluid->php_calculate_cost_average($o_data, $n_data);
							$p_cost = !empty($p_cost_average) ? "'" . $fluid->php_escape_string($p_cost_average) . "'" : "NULL";

							if($i > 0)
								$f_where .= ", ";

							$f_case .= " WHEN (`p_id`) = ('" . $s_data['p_id'] . "') THEN " . $p_cost;
							$f_where .= $s_data['p_id'];

							$i++;
						}

						$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET `p_cost` = CASE" . $f_case . " END WHERE p_id IN (" . $f_where . ")");
					}
				}

				// Update existing data if required.
				$fluid->php_db_query("UPDATE temp_table_" . $rand . " dest, (SELECT * FROM temp_table_staging_" . $rand . ") src " . $f_columns . " WHERE dest." . $f_data['f_import']['p_mfgcode']['f_column'] . " = src." . $f_data['f_import']['p_mfgcode']['i_column']);

				// Remove the updated rows from the temp staging table and prepare for inserts.
				$fluid->php_db_query("DELETE FROM t1 USING temp_table_staging_" . $rand . " t1 INNER JOIN temp_table_" . $rand . " t2 ON (t1." . $f_data['f_import']['p_mfgcode']['i_column'] . " = t2." . $f_data['f_import']['p_mfgcode']['f_column'] . ")");
				// Modify temp staging table and set the default selected category and manufacturer.
				// ALTER TABLE `temp_table_72918` ADD `test1` INT NOT NULL DEFAULT '1' AFTER `p_weight`;
				$fluid->php_db_query("ALTER TABLE temp_table_staging_" . $rand . " ADD p_mfgid_" . $rand . " INT NOT NULL DEFAULT '" . $fluid->php_escape_string($f_data['f_manufacturer']) . "'");
				$fluid->php_db_query("ALTER TABLE temp_table_staging_" . $rand . " ADD p_catid_" . $rand . " INT NOT NULL DEFAULT '" . $fluid->php_escape_string($f_data['f_category']) . "'");

				// ---> Manufacturer <----
				// Insert new data as required. Reset mfg and cat sort orders.
				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_mfg_" . $rand . " AS SELECT p_id, p_mfgid, p_sortorder_mfg FROM temp_table_" . $rand . " WHERE p_mfgid = '" . $fluid->php_escape_string($f_data['f_manufacturer']) . "'");
				// Set auto increment temporary to accept 0 as a value, else queries fail as we do not use 0 in the sortorder.
				$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
				$fluid->php_db_query("ALTER TABLE temp_table_mfg_" . $rand . " MODIFY COLUMN p_sortorder_mfg INT auto_increment primary key NOT NULL;");

				// Need to make duplicate of table as can only access a temp table once per query. It is accessed twice in a query below.
				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_mfg_duplicate_" . $rand . " AS SELECT * FROM temp_table_mfg_" . $rand);
				$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
				$fluid->php_db_query("ALTER TABLE temp_table_mfg_duplicate_" . $rand . " MODIFY COLUMN p_sortorder_mfg INT auto_increment primary key NOT NULL;");

				// ---> Category <----
				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_cat_" . $rand . " AS SELECT p_id, p_catid, p_sortorder FROM temp_table_" . $rand . " WHERE p_catid = '" . $fluid->php_escape_string($f_data['f_category']) . "'");
				$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
				$fluid->php_db_query("ALTER TABLE temp_table_cat_" . $rand . " MODIFY COLUMN p_sortorder INT auto_increment primary key NOT NULL;");

				// Need to make duplicate of table as can only access a temp table once per query. It is accessed twice in a query below.
				$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_cat_duplicate_" . $rand . " AS SELECT * FROM temp_table_cat_" . $rand);
				$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
				$fluid->php_db_query("ALTER TABLE temp_table_cat_duplicate_" . $rand . " MODIFY COLUMN p_sortorder INT auto_increment primary key NOT NULL;");

				// --> temp_table_rand setup again.
				// Set the cost averages on new items now that were imported in that had a cost update or cost data.
				if(isset($f_data['f_import']['p_cost_real'])) {
					$fluid->php_db_query("ALTER TABLE temp_table_staging_" . $rand . " ADD `p_cost_" . $rand . "` FLOAT NULL DEFAULT NULL");
					$fluid->php_db_query("UPDATE temp_table_staging_" . $rand . " SET p_cost_" . $rand . " = " . $f_data['f_import']['p_cost_real']['i_column']);
				}

				// Update the f_insert_src
				$f_insert_src_main = "p_catid_" . $rand . ", p_mfgid_" . $rand . ",";

				if(isset($f_data['f_import']['p_cost_real']))
					$f_insert_src_main .= "p_cost_" . $rand . ",";

				$f_insert_src_main .= " " . $f_insert_src;

				// Insert new data into the temp_table_rand
				$fluid->php_db_query("INSERT INTO temp_table_" . $rand . " " . $f_insert_main . " SELECT " . $f_insert_src_main . " FROM temp_table_staging_" . $rand);

				// --> Merge data new data from temp_table_rand into the manufacturer and cat to get new id's.
				$fluid->php_db_query("INSERT INTO temp_table_mfg_" . $rand . " (p_id, p_mfgid) SELECT p_id, p_mfgid FROM temp_table_" . $rand . " WHERE p_mfgid = '" . $fluid->php_escape_string($f_data['f_manufacturer']) . "' AND p_id NOT IN (SELECT DISTINCT p_id FROM temp_table_mfg_duplicate_" . $rand . ")");

				$fluid->php_db_query("INSERT INTO temp_table_cat_" . $rand . " (p_id, p_catid) SELECT p_id, p_catid FROM temp_table_" . $rand . " WHERE p_catid = '" . $fluid->php_escape_string($f_data['f_category']) . "' AND p_id NOT IN (SELECT DISTINCT p_id FROM temp_table_cat_duplicate_" . $rand . ")");

				// --> Update temp product table with the new sort order id's.
				$fluid->php_db_query("UPDATE temp_table_" . $rand . " dest, (SELECT * FROM temp_table_mfg_" . $rand . ") src SET dest.p_sortorder_mfg = src.p_sortorder_mfg WHERE dest.p_id = src.p_id");

				$fluid->php_db_query("UPDATE temp_table_" . $rand . " dest, (SELECT * FROM temp_table_cat_" . $rand . ") src SET dest.p_sortorder = src.p_sortorder WHERE dest.p_id = src.p_id");

				// --> Merge temp table rand into the original product table.
				$fluid->php_db_query("INSERT IGNORE INTO " . TABLE_PRODUCTS . " SELECT * FROM temp_table_" . $rand);
				$fluid->php_db_query("REPLACE INTO " . TABLE_PRODUCTS . " SELECT * FROM temp_table_" . $rand);

				// --> Not really required but what the hell....
				$fluid->php_db_query("DROP TABLE temp_table_mfg_" . $rand);
				$fluid->php_db_query("DROP TABLE temp_table_mfg_duplicate_" . $rand);
				$fluid->php_db_query("DROP TABLE temp_table_cat_" . $rand);
				$fluid->php_db_query("DROP TABLE temp_table_cat_duplicate_" . $rand);
				$fluid->php_db_query("DROP TABLE temp_table_staging_" . $rand);
				$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

				$fluid->php_db_commit();

				$modal = "
				<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
					<div class='modal-content'>

						<div class='panel-default'>
						  <div class='panel-heading'>Data merge notification</div>
						</div>

						<div class='modal-body' style='padding: 0px;'>

							<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; overflow-y: scroll;'>
								<div style='padding-top: 15px;'>
									<div style='margin-left:10px; margin-right: 10px;'>
										<div class='alert alert-warning' role='alert' style='padding-bottom: 5px;'>
											<div style='font-weight: 600;'>Warning:</div>
											<div style='padding-bottom: 20px;'>Data merging complete. It might be wise to double check your data to see if you need to revert back to a backup. Also note to re-open your online store if you closed it during the data merging.</div>
										</div>
									</div>
								</div>
							</div>

						</div>";

					  $modal .= "<div class='modal-footer'>";

					  $footer_save_html = "<div style='float:right;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div>";

					  $modal .= $footer_save_html;

					  $modal .= "</div>

					</div>
				  </div>";

				//$execute_functions[]['function'] = "js_modal_hide";
  				//end($execute_functions);
  				//$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));
				
				$execute_functions[]['function'] = "js_fluid_import_staging_refresh";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));
				
				$execute_functions[]['function'] = "js_modal";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

				$execute_functions[]['function'] = "js_modal_show";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));


				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
			}
			else {
				return php_fluid_error("There was a problem merging your data. Please try again.");
			}
		}
		else {
			return php_fluid_error("There was a problem merging your data. Please try again.");
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}


// Generates HTML data from the import staging table.
function php_html_import_staging($data_array = NULL, $selection_array = NULL, $mode = NULL, $f_scan = NULL, $f_refresh = NULL, $f_column_search = NULL, $f_mid = NULL) {
	try {
		$fluid = new Fluid();
		$return = NULL;

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();

		if(isset($selection_array))
			foreach($selection_array as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		$p_catmfgid = $mode;

		$i = 0;
		$t_count = 0;

		// --> Prepare for item matching if required.
		$f_match = TRUE;
		$f_scan_array = NULL;
		$tmp_item_found_array = Array();

		if(isset($f_scan) && isset($f_column_search)) {
			if(isset($f_scan[$f_column_search])) {
				$fluid_scan = new Fluid();
				$fluid_scan->php_db_begin();

				$f_scan_query = "SELECT i." . $fluid_scan->php_escape_string($f_scan[$f_column_search]['i_column']) . ", i.fluid_import_id, p.*, c.*, m.*  FROM " . TABLE_IMPORT_STAGING . " i, " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE p." . $fluid_scan->php_escape_string($f_scan[$f_column_search]['f_column']) . " = i." . $fluid_scan->php_escape_string($f_scan[$f_column_search]['i_column']);

				if(isset($f_mid))
					$f_scan_query .= " AND p.p_mfgid = '" . $fluid_scan->php_escape_string($f_mid) . "'";

				$fluid_scan->php_db_query($f_scan_query);

				$fluid_scan->php_db_commit();

				if(isset($fluid_scan->db_array)) {
					foreach($fluid_scan->db_array as $tmp_data) {
						$tmp_item_found_array[$tmp_data['fluid_import_id']] = $tmp_data['fluid_import_id'];

						$f_tmp = $tmp_data;
						unset($f_tmp[$f_scan[$f_column_search]['i_column']]);

						$f_scan_array[$tmp_data[$f_scan[$f_column_search]['i_column']]][] = $f_tmp;
					}

					$f_match = TRUE;
				}
				else
					$f_match = NULL;
			}
		}

		if(empty($f_match)) {
			return NULL;
		}
		else {
			foreach($data_array as $value) {

				if(in_array($value['fluid_import_id'], $tmp_selection_array)) {
					$d_colour = " data-colour='transparent';";
					$style = "style='font-style: italic; background-color: " . COLOUR_SELECTED_ITEMS . ";' " . $d_colour;
				}
				else {
					$style = "style='background-color: transparent;' data-colour='transparent'";
				}

				if($i == 0) {
					if(empty($f_scan_array) && empty($f_refresh)) {
						$return .= "<thead>";

						$fluid->php_db_begin();
						$fluid->php_db_query("SELECT column_name FROM information_schema.columns WHERE table_name='" . TABLE_PRODUCTS . "' AND table_schema='" . DB_DATABASE . "'");
						$fluid->php_db_commit();

						$f_select_opt = NULL;
						$f_select_opt[] = "<option value='f_default_import_select_ignore'></option>";
						foreach($fluid->db_array as $c_key => $c_name) {
							// Hide a few important columns that we dont want to have the ability to update in the staging importer.
							if($c_name['column_name'] != "p_sortorder" && $c_name['column_name'] != "p_sortorder_mfg" && $c_name['column_name'] != "p_id" && $c_name['column_name'] != "p_mfgid" && $c_name['column_name'] != "p_catid" && $c_name['column_name'] != "p_c_filters" && $c_name['column_name'] != "p_m_filters" && $c_name['column_name'] != "p_images" && $c_name['column_name'] != "p_cost") {
								$f_select_opt[] = "<option value='" . htmlspecialchars($c_name['column_name']) . "'>" . $c_name['column_name'] . "</option>";
							}
						}

						$f_select_s = NULL;
						$return_b = "<tr id='i_id_tr_header_" . $value['fluid_import_id'] . "' style='font-weight: bold;'>";
						foreach($value as $key => $f_import) {
							$f_select_s .= "<td><select data-column='" . $key . "' name='f_import_select' id='f_import_select_" . $t_count . "' class=\"form-control selectpicker\" data-live-search=\"true\" data-size=\"10\" data-width=\"100%\" onchange='js_staging_column_set();'>";
							foreach($f_select_opt as $f_option)
								$f_select_s .= $f_option;

							$f_select_s .= "</select></td>";

							$return_b .= "<td>" . $key . "</td>";

							$t_count++;
						}
						$return_b .= "</tr>";

						$return .= "<tr>" . $f_select_s . "</tr>" . $return_b;

						$return .= "</thead>";
					}
					$return .= "<tbody id='tbody-import'>";
				}

				$return .= "<tr id='i_id_tr_" . $value['fluid_import_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\"' onClick='js_cancel_event(event); js_staging_select(\"" . $value['fluid_import_id'] . "\", \"" . $p_catmfgid . "\");'>";

				foreach($value as $key => $f_import) {
					$return .= "<td>" . $f_import . "</td>";
				}

				$return .= "</tr>";

				$t_count = count($value);
				// Matching products in database exist, lets show them.
				if(isset($f_scan_array)) {
					if(isset($f_scan_array[$value[$f_scan[$f_column_search]['i_column']]])) {
						//$f_scan_array[$value[$f_scan[$f_column_search]['i_column']]]
						$return .= "<tr>";
							$return .= "<td colspan='" . $t_count . "'>";
								// --> Contains hidden data used by the selection. It is used to find the p_id when we switch over to the item module.
								$return .= "<table class='table' id='i_id_table_scan_" . $value['fluid_import_id'] . "' " . $style . " data-pid='" . $f_scan_array[$value[$f_scan[$f_column_search]['i_column']]][0]['p_id'] . "'>";

								$return .= "<thead>";
								$return .= "<tr style='font-weight: bold;'>";

								$return .= "<td style='display: none;'></td><td style='width:20px;'></td><td>Image</td><td>Manufacturer</td>";

								$return .= "<td>Category</td>";

								// Editing modal dialog option. Removed: 7 Mai 2018.
								//$return .= "<td>Item</td><td style='text-align:center;'>Length (cm)</td><td style='text-align:center;'>Width (cm)</td><td style='text-align:center;'>Height (cm)</td><td style='text-align:center;'>Weight (kg)</td><td style='text-align:center;'>Stock</td><td style='text-align:right;'>Cost Avg</td><td style='text-align:right;'>Cost</td><td style='text-align:right;'>Price</td><td style='text-align:center;'>Edit</td></tr>";
								$return .= "<td>Item</td><td style='text-align:center;'>Length (cm)</td><td style='text-align:center;'>Width (cm)</td><td style='text-align:center;'>Height (cm)</td><td style='text-align:center;'>Weight (kg)</td><td style='text-align:center;'>Stock</td><td style='text-align:right;'>Cost Avg</td><td style='text-align:right;'>Cost</td><td style='text-align:right;'>Price</td></tr>";

								$return .= "</thead>";

								$return .= "<tbody>";

								$style = "style='background-color: transparent; vertical-align: middle;'";
								$checked = "";

								foreach($f_scan_array[$value[$f_scan[$f_column_search]['i_column']]] as $f_scan_view) {
									$return .= "<tr>";

									if($f_scan_view['p_enable'] > 0)
										$style_eye = " style='text-decoration: none; font-size:12px; display:none; vertical-align: middle;' ";
									else
										$style_eye = " style='text-decoration: none !important; font-size:12px; display:block; vertical-align: middle;' ";

									$p_c_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
									if(isset($f_scan_view['p_c_filters'])) {
										if($f_scan_view['p_c_filters'] == '{}')
											$p_c_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
										else
											$p_c_filters = NULL;
									}

									$p_m_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
									if(isset($f_scan_view['p_m_filters'])) {
										if($f_scan_view['p_m_filters'] == '{}')
											$p_m_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
										else
											$p_m_filters = NULL;
									}

									$p_trending = NULL;
									if(isset($f_scan_view['p_trending'])) {
										if($f_scan_view['p_trending'] == 1)
											$p_trending = "<span style='color: #00FF1F;' class=\"glyphicon glyphicon-fire\" aria-hidden=\"true\">_t</span>";
										else
											$p_trending = NULL;
									}

									$p_preorder = NULL;
									if(isset($f_scan_view['p_preorder'])) {
										if($f_scan_view['p_preorder'] == 1)
											$p_preorder = "<span style='color: #0081FF;' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_p</span>";
										else
											$p_preorder = NULL;
									}

									$p_namenum = NULL;
									if(isset($f_scan_view['p_namenum'])) {
										if($f_scan_view['p_namenum'] == 1)
											$p_namenum = "<span style='color: #0081FF;' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_p</span>";
										else
											$p_namenum = NULL;
									}

									$p_rebate_claim = NULL;
									if(isset($f_scan_view['p_rebate_claim'])) {
										if($f_scan_view['p_rebate_claim'] == 1)
											$p_rebate_claim = "<span style='color: #00FF40;' class=\"glyphicon glyphicon-usd\" aria-hidden=\"true\">_p</span>";
										else
											$p_rebate_claim = NULL;
									}

									$p_stock_end = NULL;
									if(isset($f_scan_view['p_stock_end'])) {
										if($f_scan_view['p_stock_end'] == 1)
											$p_stock_end = "<span style='color: #00EFFF;' class=\"glyphicon glyphicon-cloud\" aria-hidden=\"true\">_e</span>";
										else
											$p_stock_end = NULL;
									}

									$p_showalways = NULL;
									if(isset($f_scan_view['p_showalways'])) {
										if($f_scan_view['p_showalways'] == 1)
											$p_preorder = "<span style='color: #0081FF;' class=\"glyphicon glyphicon-eye-flash\" aria-hidden=\"true\">_a</span>";
										else
											$p_preorder = NULL;
									}

									$p_discontinued = NULL;
									if(isset($f_scan_view['p_enable'])) {
										if($f_scan_view['p_enable'] == 2)
											$p_discontinued= "<span style='color: #5BC0DE' class=\"glyphicon glyphicon-certificate\" aria-hidden=\"true\">_d</span>";
										else
											$p_discontinued = NULL;
									}

									$p_rental = NULL;
									if(isset($f_scan_view['p_rental'])) {
										if($f_scan_view['p_rental'] == 1)
											$p_rental= "<span style='color: #DEC35B' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_r</span>";
										else
											$p_rental = NULL;
									}

									$p_special_order = NULL;
									if(isset($f_scan_view['p_special_order'])) {
										if($f_scan_view['p_special_order'] == 1)
											$p_special_order= "<span style='color: #DE815B' class=\"glyphicon glyphicon-star\" aria-hidden=\"true\">_s</span>";
										else
											$p_special_order = NULL;
									}

									$return .= "<td class='f-td' style='text-align:center; vertical-align: middle;'><span " . $style_eye . " id='p_id_tr_" . $f_scan_view['p_id'] . "_eye' class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\"></span> " . $p_c_filters . " " . $p_m_filters . " " . $p_trending . " " . $p_preorder . " " . $p_rebate_claim . " " . $p_stock_end . " " . $p_showalways . " " . $p_namenum . " " . $p_discontinued . " " . $p_rental . " " . $p_special_order . "</td>";

									$f_img_name = str_replace(" ", "_", $f_scan_view['m_name'] . "_" . $f_scan_view['p_name'] . "_" . $f_scan_view['p_mfgcode']);
									$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

									$p_images = $fluid->php_process_images($f_scan_view['p_images']);
									$width_height_admin = $fluid->php_process_image_resize($p_images[0], "80", "80", $f_img_name);
									$f_image_html = "<img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $width_height_admin['image'] . "' alt=\"" . str_replace('"', '', $f_scan_view['m_name'] . " " . $f_scan_view['p_name']) . "\"/></img>";
									$return .= "<td class='f-td' style='vertical-align: middle;'>" . $f_image_html . "</td>";

									$return .= "<td class='f-td' style='vertical-align: middle;'>" . $f_scan_view['m_name'] . "</td>";

									$return .= "<td class='f-td' style='vertical-align: middle;'>" . $f_scan_view['c_name'] . "</td>";

									$return .= "<td class='f-td' style='vertical-align: middle;'>" . $f_scan_view['p_name'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $f_scan_view['p_length'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $f_scan_view['p_width'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $f_scan_view['p_height'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $f_scan_view['p_weight'] . "</td><td class='f-td' style='text-align:center; vertical-align: middle;'>" . $f_scan_view['p_stock'] . "</td>";

									$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($f_scan_view['p_cost'], 2, '.', ',') . "</td>";
									$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($f_scan_view['p_cost_real'], 2, '.', ',') . "</td>";

									//if($f_scan_view['p_price_discount'] && strtotime($f_scan_view['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($f_scan_view['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s')))
									if($f_scan_view['p_price_discount'] && ((strtotime($f_scan_view['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($f_scan_view['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($f_scan_view['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $f_scan_view['p_discount_date_end'] == NULL) || ($f_scan_view['p_discount_date_start'] == NULL && $f_scan_view['p_discount_date_end'] == NULL) ))
										$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'><div style='font-style: italic; text-decoration: line-through;'>" . number_format($f_scan_view['p_price'], 2, '.', ',') . "</div><div style='color: red;'>" . number_format($f_scan_view['p_price_discount'], 2, '.', ',') . "</div></td>";
									else
										$return .= "<td class='f-td' style='text-align:right; vertical-align: middle;'>" . number_format($f_scan_view['p_price'], 2, '.', ',') . "</td>";

									/*
									// Editing modal dialog option. Removed: 7 Mai 2018.
									$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_product_creator_editor&data=" . base64_encode(json_encode($f_scan_view['p_id'])) . "&mode=" . $mode)));
									$return .= "<td class='f-td' style='text-align:center; vertical-align: middle;'><button type='button' class='btn btn-primary' onClick='js_cancel_event(event); js_fluid_ajax(\"" . $temp_url . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> <div class='f-btn-text'>Edit</div></button></td>";
									*/

									$return .= "</tr>";
								}
									$return .= "</tbody>";
								$return .= "</table>";
							$return .= "</td>";
						$return .= "</tr>";
					}
				}

				$i++;
			}

			if($i > 0)
				$return .= "</tbody>";

			return Array("html" => $return, "f_found_array" => $tmp_item_found_array);
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_load_scan_menu() {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else
			throw new Exception("Error: There was a problem loading your data. Please try again.");

		$fluid = new Fluid();

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Data scanning</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Warning:</div>
									<div style='padding-bottom: 20px;'>Items only selected when after you scan and matched and displayed will only transfer over when you switch to the item module. Please note that items which do not exist in the item database will not transfer over to selection list when switching over to the item module. It is HIGHLY SUGGESTED to make your selections after scanning for the matching items. Doing a TABLE REFRESH to remove the matching items will also not transfer over any selections you make afterwards.</div>
								</div>

								<div style='font-weight: 600; padding-bottom: 5px;'>Selection tips:</div>
								<div style='padding-bottom: 20px;'>
									<div>When not searching by the upc/ean code, you might get unexpected results as items can share the same p_mfg_number item code. You can narrow down your selection results by selection based on manufacturer.</div>
								</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";

								/*
									--> Load categories and manufactuers in a <select> dropdown.
									--> A category and manufacturer must be selected. (hide p_catmfgid and p_catid) from the <select> headers on the import staging table columns.
									--> Then proceed with merge and updating.
								*/

								$fluid->php_db_begin();

								// Load the manufacturers
								$fluid->php_db_query("SELECT * FROM ". TABLE_MANUFACTURERS . " ORDER BY m_sortorder ASC");
								if(isset($fluid->db_array)) {
									if(count($fluid->db_array) > 0) {
										foreach($fluid->db_array as $key => $value) {
											if($value['m_parent_id'] == NULL)
												$manufacturer_data[$value['m_id']]['parent'] = $value;
											else
												$manufacturer_data[$value['m_parent_id']]['childs'][] = $value;
										}
									}
									else
										$fluid->db_error = "ERROR: There are no manufacturers. Please create a manufacturer first before trying to merge data.";
								}
								else
									$fluid->db_error = "ERROR: There are no manufacturers. Please create a manufacturer first before trying to merge data.";

								// Load the categories.
								/*
								$fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " ORDER BY c_sortorder ASC");
								if(isset($fluid->db_array)) {
									if(count($fluid->db_array) > 0) {
										foreach($fluid->db_array as $key => $value) {
											if($value['c_parent_id'] == NULL)
												$category_data[$value['c_id']]['parent'] = $value;
											else
												$category_data[$value['c_parent_id']]['childs'][] = $value;
										}
									}
									else
										$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";
								}
								else
									$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to merge data.";
								*/
								$fluid->php_db_commit();

								$output_man = NULL;
								$i = 0;
								if(isset($manufacturer_data)) {
									foreach($manufacturer_data as $parent) {
										if(isset($parent['childs'])) {
											$output_man .= "<optgroup label='" . $parent['parent']['m_name'] . "'>";

											foreach($parent['childs'] as $value) {
												$width_height = $fluid->php_process_image_resize($fluid->php_process_images($value['m_images'])[0], "20", "20");

												$output_man .= "<option data-name=\"" . $value['m_name'] . "\" value='" . $value['m_id'] . "'";
												$output_man .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['m_name'] . "\"";

												$output_man .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['m_name'] . "</option>";

												$i++;
											}

											$output_man .= "</optgroup>";
										}
									}
								}

								/*
								$output_cat = NULL;
								$i = 0;
								if(isset($category_data)) {
									foreach($category_data as $parent) {
										if(isset($parent['childs'])) {
											$output_cat .= "<optgroup label='" . $parent['parent']['c_name'] . "'>";

											foreach($parent['childs'] as $value) {
												$width_height = $fluid->php_process_image_resize($fluid->php_process_images($value['c_image'])[0], "20", "20");

												$output_cat .= "<option value='" . $value['c_id'] . "'";
												$output_cat .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "\"";

												$output_cat .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "</option>";

												$i++;
											}

											$output_cat .= "</optgroup>";
										}
									}
								}
								*/
								$modal .= "<div style='font-weight: 600; padding-bottom: 5px;'>Select a manufacturer and category for new items:</div>";
								$modal .= "<div style='padding: 0px 0px 20px 0px;'>";
									$modal .= "<div class=\"input-group\" style='margin-top: 5px;'>";
									$modal .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Manufacturer</div></span>";

										$modal .= "<select id='fluid-staging-product-manufacturer' class=\"form-control selectpicker show-menu-arrow show-tick dropup\" data-live-search=\"true\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\">"; // Merge the filter array data to the js client call.
										$modal .= "<option value='none'>Nothing selected</option>";
										$modal .= $output_man; // Merge the manufacturer selection into the html.
										$modal .="</select>";
									$modal .= "</div>";
									/*
									$modal .= "<div class=\"input-group\" style='margin-top: 5px;'>";
									$modal .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Category</div></span>";

										$modal .= "<select id='fluid-staging-product-category' class=\"form-control selectpicker show-menu-arrow show-tick dropup\" data-live-search=\"true\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onChange='js_staging_merge_button_check();'>"; // Merge the filter array data to the js client call.
										$modal .= "<option value='none'>Nothing selected</option>";
										$modal .= $output_cat; // Merge the category selection into the html.
										$modal .="</select>";
									$modal .= "</div>";
									*/
								$modal .= "</div>";

							$modal .= "</div>
						</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer'>";

			  $footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div><div style='float:right;'><button id='fluid-staging-confirm-button' type='button' class='btn btn-success' onClick='js_staging_search(\"" . $f_data->f_column . "\");'><span class=\"glyphicon glyphicon-search\" aria-hidden=\"true\"></span> Search</button></div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}
?>
