<?php
// fluid.loader.php
// Michael Rajotte - 2016 June

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

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

function php_category_create_and_edit() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));
		$mode_edit = $_REQUEST['modeedit'];
		$filters = $_REQUEST['filters'];
		$selection = $_REQUEST['selection'];
		$execute_functions = Array();
		$filter_querys = NULL; // For updated the filters on items as required.
		$prev_parent_id = NULL;


		if(isset($_REQUEST['mode'])) {
			$mode = $_REQUEST['mode'];
		}
		else {
			$mode = NULL;
		}

		if(isset($_REQUEST['page_num'])) {
			$f_page_num = $_REQUEST['page_num'];
		}
		else {
			$f_page_num = 1;
		}

		if($f_page_num < 1) {
			$f_page_num = 1;
		}

		$fluid_mode = new Fluid_Mode($mode);

		$image_array_delete = Array();

		if(isset($data->f_id))
			$_SESSION['fluid_admin'] = base64_decode($data->f_id);

		if(isset($_REQUEST['parent']))
			$parent_flag = filter_var($_REQUEST['parent'], FILTER_VALIDATE_BOOLEAN);

		// Prep the filters. Only do this in child mode.
		if($mode_edit == "edit" && $parent_flag == FALSE) {
			$fluid->php_db_query("SELECT " . $fluid_mode->id . ", " . $fluid_mode->X . "_parent_id, " . $fluid_mode->images . ", " . $fluid_mode->filters . " FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->id . " = '" . $fluid->php_escape_string($data->c_id) . "'");

			if(isset($fluid->db_array)) {
				// Record the original images, so we can process them after the queries.
				$image_array_delete[] = $fluid->db_array[0][$fluid_mode->images];

				// Record the previous category so we can refresh it.
				$cat_refresh[$fluid->db_array[0][$fluid_mode->id]] = $fluid->db_array[0][$fluid_mode->id];

				// Record the previous parent category in case we need to refresh.
				$prev_parent_id = $fluid->db_array[0][$fluid_mode->X . "_parent_id"];

				// Check for filter changes and update items filters where required.
				// The only thing we need to check for is if the filter_id and sub_id do not exists. If they are gone, then the product with those need to be updated to remove those ids.
				if(isset($fluid->db_array[0][$fluid_mode->filters])) {
					if($filters != $fluid->db_array[0][$fluid_mode->filters]) {

						// Convert the new filters object into a array.
						$new_filters = (array)json_decode(base64_decode($filters));
						$new_array_tmp = NULL;
						foreach($new_filters as $new_key => $new_tmp) {
							$new_array_tmp[$new_key] = (array)$new_tmp;
							$new_array_tmp[$new_key]['sub_filters'] = (array)$new_array_tmp[$new_key]['sub_filters'];
						}
						$new_filters = $new_array_tmp;

						// Convert the older filters object into a array.
						$old_filters = (array)json_decode(base64_decode($fluid->db_array[0][$fluid_mode->filters]));
						$old_array_tmp = NULL;
						foreach($old_filters as $old_key => $old_tmp) {
							$old_array_tmp[$old_key] = (array)$old_tmp;
							$old_array_tmp[$old_key]['sub_filters'] = (array)$old_array_tmp[$old_key]['sub_filters'];
						}
						$old_filters = $old_array_tmp;

						/*
						JSON Filter structure in item database.
						{"NDQxNDY4ODIz": {"sub_id": "NDQxNDY4ODIz", "filter_id": "OTI0NzI5ODI1", "category_id": 3}, "Nzk5MjU2Mzk=": {"sub_id": "Nzk5MjU2Mzk=", "filter_id": "NzM3MTg2OTgz", "category_id": 3}}

						{"111312952": {"sub_id": "MTExMzEyOTUy", "filter_id": "NzM3MTg2OTgz", "category_id": 3}, "909054495": {"sub_id": "OTA5MDU0NDk1", "filter_id": "OTI0NzI5ODI1", "category_id": 3}}

						SELECT JSON_EXTRACT(p_c_filters, "$.Nzk5MjU2Mzk=.filter_id") FROM `products`
						SELECT JSON_EXTRACT(p_c_filters, "$.111312952.filter_id") FROM `products`

						UPDATE `products` SET p_c_filters = JSON_REMOVE(p_c_filters, "$.MTExMzEyOTUy")
						UPDATE `products` SET p_c_filters = JSON_REMOVE(p_c_filters, '$.903593067')
						*/

						// Search for missing filters and sub filters.
						if(isset($old_filters)) {
							foreach($old_filters as $key_old => $old_data) {

								// Scan through all filter_id, if found, flag as found. Missing filters do not get flagged.
								if(isset($new_filters))
									foreach($new_filters as $key_new => $data_new_tmp)
										if($old_data['filter_id'] == $data_new_tmp['filter_id'])
											$old_filters[$key_old]['found'] = TRUE;

								// Remove the old sub_filter objects.
								unset($old_filters[$key_old]['sub_filters']);

								// Scan through all sub_id, if any missing, flag for deletion.
								foreach($old_data['sub_filters'] as $sub_key_old => $old_sub_tmp) {
									// Add the old sub filter as arrays to complete the conversion.
									$old_filters[$key_old]['sub_filters'][$sub_key_old] = (array)$old_sub_tmp;

									// Scan the new sub_filters and if found, flag as found. Missing sub_filters do not get flagged.
									if(isset($new_filters))
										foreach($new_filters as $key_new => $data_new_tmp)
											foreach($data_new_tmp['sub_filters'] as $sub_key_new => $new_sub_tmp)
												if($old_sub_tmp->sub_id == $new_sub_tmp->sub_id)
													$old_filters[$key_old]['sub_filters'][$sub_key_old]['found'] = TRUE;

									// Need to add " that escape the json id, as it is base64encoded in the json data. A base64 encoded string can sometimes end with a = which can break the query when using JSON_REMOVE. So manually have to add the " as \" while escaping the string.
									if(!isset($old_filters[$key_old]['sub_filters'][$sub_key_old]['found']))
										$filter_querys[] = "UPDATE `" . TABLE_PRODUCTS . "` SET " . $fluid_mode->prodfilters . " = JSON_REMOVE(" . $fluid_mode->prodfilters . ", '$." . $fluid->php_escape_string("\"" . $old_filters[$key_old]['sub_filters'][$sub_key_old]['sub_id'] . "\"") . "')";
								}
							}
						}
					}
				}
			}
		}

		// Re-arrange the picture order based on the queue list order.
		$tmporder = Array();
		$image_array_copy = Array();

		foreach($data->c_imageorder as $order) {
			$f_rand = json_decode(base64_decode($order->xhr))->file->rand;

			foreach($data->c_images as $image) {
				$image->file->fullpath = FOLDER_IMAGES . $image->file->image;

				// The temp path data is not required. Remove it from the object.
				unset($image->file->tempfullpath);

				if($order->name == $image->file->name && $order->size == $image->file->size && $f_rand == $image->file->rand) {
					// Record the new images only, so we can process them after the queries.
					$image_array_copy[$image->file->rand] = $image;

					// Prepare a new image list for the query.
					$tmporder{$image->file->rand} = $image;
				}
			}
		}

		$data->c_images = $tmporder;

		// Process some of the variables which could break the sql queries if they are blank.
		$c_name = !empty($data->c_name) ? "'" . $fluid->php_escape_string(base64_decode($data->c_name)) . "'" : "''";
		$c_seo = !empty($data->c_seo) ? "'" . $fluid->php_escape_string(base64_decode($data->c_seo)) . "'" : "''";
		$c_desc = !empty($data->c_desc) ? "'" . $fluid->php_escape_string(base64_decode($data->c_desc)) . "'" : "''";
		$c_weight = !empty($data->c_weight) ? "'" . $fluid->php_escape_string(base64_decode($data->c_weight)) . "'" : "'0'";
		$c_keywords = !empty($data->c_keywords) ? "'" . $fluid->php_escape_string(base64_decode($data->c_keywords)) . "'" : "''";
		$c_google_cat_id = !empty($data->c_google_cat_id) ? "'" . $fluid->php_escape_string(base64_decode($data->c_google_cat_id)) . "'" : "NULL";

		if($fluid_mode->mode != "manufacturers") {
			$c_formula_status = !empty($data->c_formula_status) ? "'" . $fluid->php_escape_string(base64_decode($data->c_formula_status)) . "'" : "'0'";
			$c_formula_math = !empty($data->c_formula_math) ? "'" . $fluid->php_escape_string(base64_decode($data->c_formula_math)) . "'" : "NULL";
		}

		// Setting the flags to empty in parent mode.
		if($parent_flag == TRUE)
			$c_filters = "'" . $fluid->php_escape_string(base64_encode("{}")) . "'";
		else
			$c_filters = !empty($filters) ? "'" . $fluid->php_escape_string($filters) . "'" : "NULL";

		$c_parent_id = !empty($data->c_parent_id) ? "'" . $fluid->php_escape_string(base64_decode($data->c_parent_id)) . "'" : "NULL";

		$func_counter = 0;
		if($mode_edit == "add") {
			if($parent_flag == TRUE)
				$count_where = "IS NULL";
			else
				$count_where = "= " . $c_parent_id;

			// Get the sort order.
			$fluid->php_db_query("SELECT COUNT(" . $fluid_mode->sortorder . ") AS tmp_" . $fluid_mode->X . "_category_count FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id " . $count_where);
			$sort_order = $fluid->db_array[0]['tmp_' . $fluid_mode->X . '_category_count'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.

			// Get a id for inserting.
			$fluid->php_db_query("SELECT " . $fluid_mode->id . " FROM " . $fluid_mode->table . " ORDER BY " . $fluid_mode->id . " DESC LIMIT 1");
			if(isset($fluid->db_array))
				$c_id_tmp = $fluid->db_array[0][$fluid_mode->id] + 1;
			else
				$c_id_tmp = 1; // No categories found, let's set the id to 1.

			if($fluid_mode->mode != "manufacturers")
				$fluid->php_db_query("INSERT INTO " . $fluid_mode->table . " (" . $fluid_mode->X . "_parent_id, " . $fluid_mode->enable . ", " . $fluid_mode->id . ", " . $fluid_mode->sortorder . ", " . $fluid_mode->filters . ", " . $fluid_mode->name . ", " . $fluid_mode->weight . ", " . $fluid_mode->google_cat_id . ", " . $fluid_mode->keywords . ", " . $fluid_mode->seo . ", ". $fluid_mode->desc . ", " . $fluid_mode->images . ", " . $fluid_mode->formula_status . ", " . $fluid_mode->formula_math . ") VALUES (" . $c_parent_id . ", '" . $fluid->php_escape_string(base64_decode($data->c_status)) . "', '" . $c_id_tmp . "', '" . $sort_order . "', " . $c_filters . ", " . $c_name . ", " . $c_weight . ", " . $c_google_cat_id . ", " . $c_keywords . ", " . $c_seo . ", " . $c_desc . ", '" . $fluid->php_escape_string(base64_encode(json_encode($data->c_images))) . "', " . $c_formula_status . ", " . $c_formula_math . ")");
			else
				$fluid->php_db_query("INSERT INTO " . $fluid_mode->table . " (" . $fluid_mode->X . "_parent_id, " . $fluid_mode->enable . ", " . $fluid_mode->id . ", " . $fluid_mode->sortorder . ", " . $fluid_mode->filters . ", " . $fluid_mode->name . ", " . $fluid_mode->weight . ", " . $fluid_mode->google_cat_id . ", " . $fluid_mode->keywords . ", " . $fluid_mode->seo . ", ". $fluid_mode->desc . ", " . $fluid_mode->images . ") VALUES (" . $c_parent_id . ", '" . $fluid->php_escape_string(base64_decode($data->c_status)) . "', '" . $c_id_tmp . "', '" . $sort_order . "', " . $c_filters . ", " . $c_name . ", " . $c_weight . ", " . $c_google_cat_id . ", " . $c_keywords . ", " . $c_seo . ", " . $c_desc . ", '" . $fluid->php_escape_string(base64_encode(json_encode($data->c_images))) . "')");

			// Must move this above the html query below as that needs the data to exist in the database. So start the commit earlier. Do not commit this after.
			$fluid->php_db_commit();

			$c_return['c_id'] = $c_id_tmp;
			$c_return['c_parent_id'] = base64_decode($data->c_parent_id);
			$c_return['mode'] = $mode_edit;
			$c_return['enable'] = base64_decode($data->c_status);
			$c_return['html'] = php_html_categories($c_id_tmp, $fluid_mode->mode)['html'];
			$c_return['parent'] = $parent_flag;

			$execute_functions[$func_counter]['function'] = "js_category_add_update";
			$execute_functions[$func_counter]['data'] = base64_encode(json_encode($c_return));
			$func_counter++;
		}
		else if($mode_edit == "edit") {
			$sort_order = NULL;

			// Checking to see if the child has been changed to a different parent category. If it has, we need to get the last sort order.
			// Only for child categories.
			if($parent_flag == FALSE) {
				$fluid->php_db_query("SELECT " . $fluid_mode->X . "_parent_id FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->id . " = '" . $fluid->php_escape_string($data->c_id) . "'");

				if(isset($fluid->db_array)) {
					if($fluid->db_array[0][$fluid_mode->X . "_parent_id"] != base64_decode($data->c_parent_id)) {
						// We have a change, lets get the new sort order.
						$fluid->php_db_query("SELECT COUNT(" . $fluid_mode->sortorder . ") AS tmp_" . $fluid_mode->X . "_category_count FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id = " . $c_parent_id);

						// Since we do not use 0 in sort order, we must add 1 to the sort_order count.
						$sort_order = ", `" . $fluid_mode->sortorder . "` = '" . ($fluid->db_array[0]['tmp_' . $fluid_mode->X . '_category_count'] + 1) . "'";
					}
				}
			}

			// The update query.
			if($fluid_mode->mode != "manufacturers")
				$fluid->php_db_query("UPDATE " . $fluid_mode->table . " SET `" . $fluid_mode->X . "_parent_id` = " . $c_parent_id . ", `" . $fluid_mode->enable . "` = '" . $fluid->php_escape_string(base64_decode($data->c_status)) . "', `" . $fluid_mode->formula_status . "` = " . $c_formula_status . ", `" . $fluid_mode->formula_math . "` = " . $c_formula_math . ", `" . $fluid_mode->filters . "` = " . $c_filters . ", `" . $fluid_mode->weight . "` = " . $c_weight . ", `" . $fluid_mode->google_cat_id . "` = " . $c_google_cat_id . ", `" . $fluid_mode->keywords . "` = " . $c_keywords . ", `" . $fluid_mode->name . "` = " . $c_name . ", `" . $fluid_mode->seo . "` = " . $c_seo . ", `" . $fluid_mode->images . "` = '" . $fluid->php_escape_string(base64_encode(json_encode($data->c_images))) . "'" . $sort_order . " WHERE " . $fluid_mode->id . " = '" . $fluid->php_escape_string($data->c_id) . "'");
			else
				$fluid->php_db_query("UPDATE " . $fluid_mode->table . " SET `" . $fluid_mode->X . "_parent_id` = " . $c_parent_id . ", `" . $fluid_mode->enable . "` = '" . $fluid->php_escape_string(base64_decode($data->c_status)) . "', `" . $fluid_mode->filters . "` = " . $c_filters . ", `" . $fluid_mode->weight . "` = " . $c_weight . ", `" . $fluid_mode->google_cat_id . "` = " . $c_google_cat_id . ", `" . $fluid_mode->keywords . "` = " . $c_keywords . ", `" . $fluid_mode->name . "` = " . $c_name . ", `" . $fluid_mode->seo . "` = " . $c_seo . ", `" . $fluid_mode->images . "` = '" . $fluid->php_escape_string(base64_encode(json_encode($data->c_images))) . "'" . $sort_order . " WHERE " . $fluid_mode->id . " = '" . $fluid->php_escape_string($data->c_id) . "'");

			// Added at the end of the update query. It makes sure we set the filters to NULL if all are removed from a item.
			// This only gets executed if in child mode when required.
			if($parent_flag == FALSE) {
				$filter_querys[] = "UPDATE `" . TABLE_PRODUCTS . "` SET " . $fluid_mode->prodfilters . " = NULL WHERE " . $fluid_mode->prodfilters . " = '{}'";

				// Run the updates on the filters for the items, and update them or remove as required.
				if(isset($filter_querys) && $parent_flag == FALSE)
					foreach($filter_querys as $query)
						$fluid->php_db_query($query);
			}

			// Must move this above the html query below as that needs the data to exist in the database. So start the commit earlier. Do not commit this after.
			$fluid->php_db_commit();

			$c_return['c_id'] = $data->c_id;
			$c_return['c_parent_id'] = base64_decode($data->c_parent_id);
			$c_return['c_parent_id_prev'] = $prev_parent_id;
			$c_return['mode'] = $mode_edit;
			$c_return['enable'] = base64_decode($data->c_status);
			$data_array = php_html_categories($data->c_id, $fluid_mode->mode);
			$c_return['html'] = base64_encode(json_encode($data_array['action_html']));
			//$c_return['return_last'] = $data_array['return_last']; // Is this really used? It is commented out in fluid.js.php->js_category_add_update()
			$c_return['parent'] = $parent_flag;

			$execute_functions[$func_counter]['function'] = "js_category_add_update";
			$execute_functions[$func_counter]['data'] = base64_encode(json_encode($c_return));
			$func_counter++;

			// Only refresh the category products if there was a filter update change.
			if(isset($filter_querys) && $parent_flag == FALSE) {
				$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $_REQUEST['selection'] . "&page_num=" . $f_page_num . "&mode=" . $fluid_mode->mode)));
				$execute_functions[$func_counter]['function'] = "js_fluid_ajax";
				$execute_functions[$func_counter]['data'] = base64_encode(json_encode($temp_data));
				$func_counter++;
			}

		}

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[$func_counter]['function'] = "js_image_dropzone_destroy";
		$execute_functions[$func_counter]['data'] = base64_encode(json_encode(""));
		$func_counter++;

		$execute_functions[$func_counter]['function'] = "js_modal_hide";
		$execute_functions[$func_counter]['data'] = base64_encode(json_encode("#fluid-modal"));


		// Process the images after the queries are successful.
		// Delete the original images.
		/*
		foreach($image_array_delete as $value)
			foreach(json_decode(base64_decode($value)) as $img)
				unlink(FOLDER_IMAGES . $img->file->image);

		// Copy over the new and old from the working temp folder to the main image folder.
		foreach($image_array_copy as $img) {
			if(is_file(FOLDER_IMAGES_TEMP . $img->file->image))
				copy(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image, FOLDER_IMAGES . $img->file->image);
		}
		*/

		foreach($image_array_delete as $value) {
			$value_img = json_decode(base64_decode($value));
			if(!empty($value_img)) {
				foreach(json_decode(base64_decode($value)) as $img) {
					if(is_file(FOLDER_IMAGES . $img->file->image))
						unlink(FOLDER_IMAGES . $img->file->image);
				}
			}
		}

		// Copy over the new and old from the working temp folder to the main image folder.
		foreach($image_array_copy as $img) {
			if(is_file(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image))
				copy(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image, FOLDER_IMAGES . $img->file->image);
		}

		// Copy over the new and old from the working temp folder to the main image folder.
		//foreach($image_array_copy as $img)
			//copy(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image, FOLDER_IMAGES . $img->file->image);

		php_delete_image_temp();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_category_create_filter() {
	try {
		$filter_data = $_REQUEST['filterdata'];
		$filters_html = NULL;
		$filters_html_select = NULL;

		// If creating a new filter, lets add it to the end of the object before refreshing the list.
		if(isset($_REQUEST['filter'])) {
			$new_filter = $_REQUEST['filter'];

			$filter_data = (array)json_decode(base64_decode($filter_data));
			$obj_array['filter_name'] = $new_filter;

			if(isset($filter_data['count']))
				$obj_array['filter_order'] = $filter_data['count'];
			else
				$obj_array['filter_order'] = 0;

			$obj_array['filter_id'] = base64_encode(rand(0, 1000000000));
			$obj_array['sub_filters'] = NULL;

			$filter_data[count($filter_data)] = (object)$obj_array;
			$filter_data = (object)$filter_data;
			$filter_data = base64_encode(json_encode($filter_data));
		}

		$filters_html = php_html_categories_filters($filter_data)['innerHTML'];
		$filters_html_select = php_html_categories_filters_select($filter_data)['innerHTML'];

		$execute_functions[0]['function'] = "js_html_insert_element";
		$execute_functions[0]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($filters_html), "parent" => base64_encode("filters-cat-new-div"))));
		$execute_functions[1]['function'] = "js_html_insert_element";
		$execute_functions[1]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($filters_html_select), "parent" => base64_encode("category-filter-select-div"))));
		$execute_functions[2]['function'] = "js_category_filter_update_rows";
		$execute_functions[2]['data'] = base64_encode(json_encode(""));
		$execute_functions[3]['function'] = "js_category_filter_sortable";
		$execute_functions[3]['data'] = base64_encode(json_encode(""));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_product_create_category($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$filters_html = NULL;
		$filters_html_select = NULL;
		$filter_data = NULL;

		// Used in the multi item editor for loading a item for editing.
		if(isset($f_data->f_link_data) && isset($f_data->f_link_refresh)) {
			$filter_data = base64_encode(json_encode($f_data->f_link_data));

			$filters_html = php_html_products_categories($filter_data)['innerHTML'];
		}
		else if(isset($f_data->f_link_data)) {
			// If creating a new filter, lets add it to the end of the object before refreshing the list.
			$filter_data = (array)$f_data->f_link_data;
			$obj_array['filter_name'] = base64_encode($f_data->f_cat_name);

			if(isset($filter_data['count']))
				$obj_array['filter_order'] = $filter_data['count'];
			else
				$obj_array['filter_order'] = 0;

			$obj_array['filter_id'] = base64_encode(rand(0, 1000000000));
			$obj_array['sub_filters'] = NULL;

			$filter_data[count($filter_data)] = (object)$obj_array;
			$filter_data = (object)$filter_data;

			$filter_data = base64_encode(json_encode($filter_data));

			$filters_html = php_html_products_categories($filter_data)['innerHTML'];
		}

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($filters_html), "parent" => base64_encode("filters-cat-new-div"))));

		$execute_functions[]['function'] = "js_category_filter_update_rows";
		$execute_functions[]['function'] = "js_category_filter_sortable";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_product_create_category_menu($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['data'])) {
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		}
		else if(isset($data)) {
			$f_data = (object)json_decode(base64_decode($data));
		}
		else {
			$f_data = NULL;
		}

		$filters_html = NULL;
		$filters_html_select = NULL;
		$filter_data = NULL;

		// Used in the multi item editor for loading a item for editing.
		if(isset($f_data->f_link_data) && isset($f_data->f_link_refresh)) {
			$filter_data = base64_encode(json_encode($f_data->f_link_data));

			$filters_html = php_html_products_categories($filter_data)['innerHTML'];
		}
		else if(isset($f_data->f_link_data)) {
			// If creating a new filter, lets add it to the end of the object before refreshing the list.
			$filter_data = (array)$f_data->f_link_data;
			$obj_array['filter_name'] = base64_encode($f_data->f_cat_name);

			if(isset($filter_data['count']))
				$obj_array['filter_order'] = $filter_data['count'];
			else
				$obj_array['filter_order'] = 0;

			$obj_array['filter_id'] = base64_encode(rand(0, 1000000000));
			$obj_array['sub_filters'] = NULL;

			$filter_data[count($filter_data)] = (object)$obj_array;
			$filter_data = (object)$filter_data;

			$filter_data = base64_encode(json_encode($filter_data));

			$filters_html = php_html_products_categories($filter_data)['innerHTML'];
		}

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($filters_html), "parent" => base64_encode("filters-cat-new-div"))));

		$execute_functions[]['function'] = "js_category_filter_update_rows";
		$execute_functions[]['function'] = "js_category_filter_sortable";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_product_linking_editor() {
	try {
		$fluid = new Fluid();

		$f_link_html = php_html_item_link_editor_menu(); // --> Load a blank state item linking editor.

		$modal = "<div class='modal-dialog f-dialog' role='document'>
					<div class='modal-content'>

						<div class='panel-default'>
						  <div class='panel-heading'>Item downloader</div>
						</div>

						<div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

						  <div class='panel panel-default' style='padding: 0px 10px 0px 10px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; min-height: 40vh; overflow-y: scroll;'>

						  " . $f_link_html . "
						  </div>
						</div>";

						$modal .= "<div class='modal-footer'>
						  <div style='float:right;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div>
						</div>

					</div>
				</div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']  = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']  = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_html_item_link_editor_menu() {
	$html = "<div style='margin-top:15px;'>";

		$html .= "<div class='alert alert-info' role='alert'>";
			$html .= "<div style='font-weight: 600;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Item linking editor. Create categories and add items to these categories. They will then show up on the item page under the created category heading as a item slider. This is good for linking accessories to items, or similar items together, etc.</div>";
		$html .= "</div>";

		$html .= "<div style='margin-top:15px;'>";
			$html .= "<div class='well'>";
				$html .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:150px !important;'>Category name</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Category name\" aria-describedby=\"basic-addon1\" id='category-filter-name'>
				</div>";
				$html .= "<div class=\"input-group\" style='padding-top:15px;'><button type='button' class='btn btn-success' onClick='js_product_create_category_menu();' >Create category</button></div>";
			$html .= "</div>"; // well end

			$filter_block_list = "none";
			$filter_block_none = "block";

			$fluid = new Fluid();
			//$fluid->php_db_query("SELECT * FROM " . TABLE_PRODUCT_LINKING_MENU . " ORDER BY l_id");

			if(isset($fluid->db_array)) {
				$filter_block_list = "block";
				$filter_block_none = "none";

			}

			$html .= "<div id='filters-cat-new-div' class='list-group' style='display:" . $filter_block_list . ";'>";


			$html .= "</div>"; // fluid-filter-listing


			$html .= "<div id='filters-cat-none-div' class='table-responsive panel panel-default' style='display:" . $filter_block_none . ";'>";
				$html .= "<table class='table table-hover' id='filters-cat-none'>";
					$html .= "<tbody>";
					$html .= "<tr id='cat-new-tr-hide'><td>No categories created yet.</td></tr>";
					$html .= "</tbody>";
				$html .= "</table>";
			$html .= "</div>"; // table-responsive

		$html .= "</div>"; // filters_html

	$html .= "</div>";

	return $html;
}

// Returns back a formatted html list of the category filters for product linking.
function php_html_products_categories($filters) {
	try {
		/*
		// Filter object structure.
			stdClass Object
			(
				[0] => stdClass Object
					(
						[filter_name] => UmVzb2x1dGlvbg==
						[filter_id] => NDg1MjEyODEw
						[filter_order] => 0
						[sub_filters] => stdClass Object
							(
								[0] => stdClass Object
									(
										[p_id] => 610
										[p_catid] => 219
										[p_mfgid] => 7
										[p_dataname] => Manfrotto 264 Rounded 16mm Lighting Stud M10
										[p_enable] => 1
										[value] => NzE5ODIxMTU3MTgy
									)

								[1] => stdClass Object
									(
										[p_id] => 611
										[p_catid] => 219
										[p_mfgid] => 7
										[p_dataname] => Manfrotto 275 Mini Spring Clamp
										[p_enable] => 1
										[value] => NzE5ODIxMjg4MzM2
									)

							)

					)
			)
		*/

		$filters_html = NULL;
		$filter_array = NULL;
		$filters_obj = json_decode(base64_decode($filters));

		$i = 0;

		if(isset($filters_obj)) {
			foreach($filters_obj as $key => $filt_obj) {
				$filter_array[$key]['filter_name'] = base64_decode($filt_obj->filter_name);
				$filter_array[$key]['filter_order'] = $filt_obj->filter_order;
				$filter_array[$key]['filter_id'] = $filt_obj->filter_id;
				$filter_array[$key]['sub_filters_obj'] = $filt_obj->sub_filters;

				// Need to update the $i position if we are adding a individual filter category to the list.
				if(isset($filt_obj->count))
					$i = $filt_obj->count;
			}

			if(isset($filter_array)) {
				foreach($filter_array as $kd => $data_value) {
					$filters_html .= "<ul style='list-style: none; padding-left:0px;' filter-id='" . $data_value['filter_id'] . "' data-subid='" . $i . "' name='filter-list-div-ul-name' title='" . base64_encode($data_value['filter_name']) . "' id='filters-list-div-" . $i . "'><li>";

						$filters_html .=  "<div id='filters-a-" . $i . "' style='height: 40px;' onClick='js_filter_stack(\"" . $i . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='list-group-item'>";

							$action_button = "<input class='fluid-form-filter' id='dropdown-filter-rename-input' name='dropdown-filter-rename-input-" . $i . "' style='display:none;' type=\"text\" placeholder=\"Filter category name\" aria-describedby=\"basic-addon1\" value=\"" . htmlspecialchars($data_value['filter_name']) . "\" onkeydown = 'if(event.keyCode == 13){ this.style.display=\"none\"; js_product_category_rename(\"" . $i . "\", this.value); }' onBlur='this.style.display=\"none\"; js_product_category_rename(\"" . $i . "\", this.value);'></input><div id='dropdown-filter' name='dropdown-filter-id-" . $i . "' class='dropdown' style='display:inline;'>
								<a id='dropdown-filter' class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'><div style='display:inline;' id='dropdown-filter' name='filter-raw-name-" . $i . "'>" . $data_value['filter_name'] . "</div> <span id='dropdown-filter' class='caret'></span>
								</a>
								  <ul id='dropdown-filter' class='dropdown-menu' aria-labelledby='dropdownMenu1'>
									<li id='dropdown-filter'><a id='dropdown-filter' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_filter_rename_blur(\"" . $i . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Rename</a></li>
									<li id='dropdown-filter'><a id='dropdown-filter' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_html_remove_element(\"filters-list-div-" . $i . "\"); js_category_filter_update_rows(); js_category_filter_sortable();'><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span> Delete</a></li>
								  </ul>
								  </div>";

							$filters_html .= " <span id='filter-span-closed-" . $i . "' class=\"glyphicon glyphicon-chevron-right\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
							$filters_html .= " <span id='filter-span-open-" . $i . "' style='display:none;' class=\"glyphicon glyphicon-chevron-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";

						$filters_html .= "</div>"; // filters-a-$i

							$data_html = "<div id='filter-div-block-" . $i . "' style='display:inline;'>";
							//$data_html .= "<div id='filters-cat-new-div-" . $i . "' name='filters-cat-new-div-name' class='table-responsive panel panel-default'>";
							//$data_html .= "<table class='table table-hover' id='filters-cat-" . $data_value['filter_id'] . "'>";

							$data_html .= "<div id='filters-cat-new-div-" . $i . "' style='margin: 10px 10px 10px 10px;'>";

								$data_html .= "<div id='f-item-list-div-" . $i . "' style='display: inline;'>";
								$select_empty_html = "<select id='filters-cat-list-" . $i . "' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"></select>";

									if(isset($data_value['sub_filters_obj'])) {
										// --> This will get rebuilt to create a HTML <select> menu of the item list.
										$f_tmp_data = (object)Array("f_item_editor" => TRUE, "f_selector" => (object)Array("v_selection" => (object)Array("p_selection" => $data_value['sub_filters_obj'])), "f_formula_list" => "filters-cat-list-" . $i);

										$data_html .= php_html_formula_links_items_builder($f_tmp_data);
									}
									else
										$data_html .= $select_empty_html;

								$data_html .= "</div>"; // --> f-item-list-div-$i.

								// --> Add position: absolute to have this div button dropdown to overlay a modal. however you get some scroll bugs.
								$data_html .= "<div style='display: inline-block; padding-left: 3px;'>
									<div class=\"btn-group\">
									  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-container=\"#fluid-modal\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
										<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
									  </button>
									  <ul class=\"dropdown-menu dropdown-menu-right\" style='top: -410%;'>
										<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"filters-cat-list-" . $i . "\", \"f-item-list-div-" . $i . "\", \"items\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
										<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"filters-cat-list-" . $i . "\", \"f-item-list-div-" . $i . "\", \"categories\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
										<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"filters-cat-list-" . $i . "\", \"f-item-list-div-" . $i . "\", \"manufacturers\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
										<li role=\"separator\" class=\"divider\"></li>
										<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"f-item-list-div-" . $i . "\").innerHTML = Base64.decode(\"" . base64_encode($select_empty_html) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
									  </ul>
									</div>
								</div>";

							$data_html .= "</div>";


							$data_html .= "</div>"; // filters-cat-new-div
							$data_html .= "</div>"; // filter-div-block-$i
						$filters_html .= "<div id='filter-list-div-" . $i . "-data' style='display:none;'>" . $data_html . "</div>";
						$filters_html .= "<div id='filter-div-" . $i . "'></div>";

					$filters_html .= "</li></ul>";

					$i++;
				}
			}
		}

		$data['innerHTML'] = $filters_html;
		$data['count'] = $i;

		return $data;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_category_delete() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		$c_id = $fluid->php_escape_string(base64_decode($_REQUEST['data']));
		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		if(isset($_REQUEST['parent']))
			$parent_flag = filter_var($_REQUEST['parent'], FILTER_VALIDATE_BOOLEAN);
		else
			$parent_flag = FALSE;

		// Parent mode, lets delete all products from all childs in this parent category.
		if($parent_flag == TRUE)
			$fluid->php_db_query("SELECT p.p_id, p.p_catid, p.p_mfgid, p.p_images, m.m_id, m.m_parent_id, c.c_id, c.c_parent_id FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id WHERE " . $fluid_mode->X . "_parent_id = '" . $c_id . "'");
		else
			$fluid->php_db_query("SELECT p_id, p_catid, p_mfgid, p_images FROM " . TABLE_PRODUCTS . " WHERE " . $fluid_mode->p_catmfg_id . " = '" . $c_id . "'"); // Select the products and process images for deletion. This is selecting from a individual child category.

		// Delete the images off the server.
		$catid_array = Array();
		$mfgid_array = Array();
		$delete_array = Array();

		$component_delete = NULL;
		if(isset($fluid->db_array)) {
			$component_delete = "IN (";
			$i = 0;
			foreach($fluid->db_array as $value) {
				if($i > 0) {
					$component_delete .= ", ";
				}

				$catid_array[$value['p_catid']] = $value['p_catid']; // Record which categories sort orders to resort.
				$mfgid_array[$value['p_mfgid']] = $value['p_mfgid']; // Record which manufacturer sort orders to resort.
				$delete_array[] = $value['p_images']; // For later deletion of the images if the queries dont fail.

				$component_delete .= $value['p_id'];

				$i++;
			}
			$component_delete .= ")";
		}

		if(isset($component_delete)) {
			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_COMPONENT . " WHERE cp_master_id " . $component_delete);
			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_COMPONENT . " WHERE cp_p_id " . $component_delete);
		}

		// Delete the items that belong in the categories / manufacturers that are getting removed.
		if($parent_flag == TRUE) {
			$where_in_delete_items = "IN (" . $c_id;

			if($fluid_mode->mode == "manufacturers") {
				foreach($mfgid_array as $key => $mfgid_l) {
					$where_in_delete_items .= "," . $mfgid_l;
				}
			}
			else {
				foreach($catid_array as $key => $catid_l) {
					$where_in_delete_items .= "," . $catid_l;
				}
			}

			$where_in_delete_items = $fluid_mode->p_catmfg_id . " " . $where_in_delete_items . ")";

			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCTS . " WHERE " . $where_in_delete_items);

			// Select the child categories and or child manufacturers and process images for deletion.
			$fluid->php_db_query("SELECT " . $fluid_mode->images . ", " . $fluid_mode->X . "_parent_id, " . $fluid_mode->id . " FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id = '" . $c_id . "' OR " . $fluid_mode->id . " = '" . $c_id . "'");
			$num_deleted_items = $fluid->db_affected_rows; // How many items were deleted. Keep track to decide if we should rebuild sort orders of categories or manufacturers.
		}
		else {
			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCTS . " WHERE " . $fluid_mode->p_catmfg_id . " = '" . $c_id . "'");
			$num_deleted_items = $fluid->db_affected_rows; // How many items were deleted. Keep track to decide if we should rebuild sort orders of categories or manufacturers.

			// Select the category or manufacturer and process images for deletion.
			$fluid->php_db_query("SELECT " . $fluid_mode->images . ", " . $fluid_mode->X . "_parent_id, " . $fluid_mode->id . " FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->id . " = '" . $c_id . "'");
		}

		// Delete the images off the server.
		$delete_array_catman = Array();
		$parent_id = NULL;
		$where_in_delete_cat = NULL;
		$selection_cat_remove = NULL; // Used to pass to js_select_clear_p_selection_category() to update the item selection if any items were removed.
		$i_child = 0;
		foreach($fluid->db_array as $value) {
			$delete_array_catman[] = $value[$fluid_mode->images]; // For later deletion of the images if the queries dont fail.
			$selection_cat_remove[] = (int)$value[$fluid_mode->id];

			// This is only required when deleting chlids and not parents.
			if($parent_flag == FALSE)
				$parent_id = $value[$fluid_mode->X . '_parent_id']; // This gets rewrote over many times but it is ok, as it will be the same value every time.

			// Only required when deleting a parent, need to record all the childs that we are deleting as well.
			if($parent_flag == TRUE) {
				if($i_child > 0)
					$where_in_delete_cat .= ",";

				$where_in_delete_cat .= $value[$fluid_mode->id];

				$i_child++;
			}
		}

		// Delete the selected category or manufacturer.
		if($parent_flag == TRUE) {
			$where_in_delete_cat = $fluid_mode->id . " IN (" . $where_in_delete_cat . ")";
			$fluid->php_db_query("DELETE FROM " . $fluid_mode->table . " WHERE " . $where_in_delete_cat); // Deleting a parent category and all its childs.
		}
		else
			$fluid->php_db_query("DELETE FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->id . " = '" . $c_id . "'");	// Deleting a single child category.

		// --> Product has been deleted. Now to remove all references to it from the product category linking.
		$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_CATEGORY_LINKING . " WHERE l_c_id = '" . $c_id . "'");

		// Rebuild the sortorder in the category or manufacturer tables.
		$rand = rand(10000, 99999);

		$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`" . $fluid_mode->id . "` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

		$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD " . $fluid_mode->sortorder . " INT auto_increment primary key NOT NULL;");

		// Copy data into the table which then gives them a new sortorder at the end of the table with auto increment.
		if($parent_flag == TRUE)
			$parent_where = "IS NULL";
		else
			$parent_where = "= " . $fluid->php_escape_string($parent_id);

		$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (" . $fluid_mode->id . ") SELECT " . $fluid_mode->id . " FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id " . $fluid->php_escape_string($parent_where));

		// Merge data from temp_table_sort into temp_table_rand via p_id.
		$fluid->php_db_query("UPDATE " . $fluid_mode->table . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest." . $fluid_mode->sortorder . " = src." . $fluid_mode->sortorder . " WHERE dest." . $fluid_mode->id . " = src." . $fluid_mode->id);

		// Drop the temporary table.
		$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);

		// ONLY REBUILD IF A ITEM IS DELETED / REMOVED FROM A CATEGORY OR MANUFACTURER.
		if($num_deleted_items > 0) {
			// Now to rebuild the item database category and manufacturer sort orders.

			// Select the items and process images for deletion.
			$fluid->php_db_query("SELECT c_id, c_parent_id FROM " . TABLE_CATEGORIES);
			$catid_array = Array();
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					$catid_array[$value['c_id']]['c_id'] = $value['c_id'];
					//$catid_array[$value['c_id']]['c_parent_id'] = $value['c_parent_id'];
				}
			}

			$fluid->php_db_query("SELECT m_id, m_parent_id FROM " . TABLE_MANUFACTURERS);
			$mfgid_array = Array();
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					$mfgid_array[$value['m_id']]['m_id'] = $value['m_id'];
					//$mfgid_array[$value['m_id']]['m_parent_id'] = $value['m_parent_id'];
				}
			}

			// Rebuild the p_sortorder in each affected category item.
			foreach($catid_array as $key => $cat_temp) {
				$rand = rand(10000, 99999);

				$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_catid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

				$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder INT auto_increment primary key NOT NULL;");

				// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
				$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_catid) SELECT p_id, p_catid FROM " . TABLE_PRODUCTS . " WHERE p_catid = '" . $fluid->php_escape_string($cat_temp['c_id']) . "' ORDER BY p_sortorder ASC");

				// Merge data from temp_table_sort into temp_table_rand via p_id.
				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder = src.p_sortorder, dest.p_catid = '" .$fluid->php_escape_string($cat_temp['c_id']) . "' WHERE dest.p_id = src.p_id");

				// Drop the temporary table.
				$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
			}

			// Rebuild the p_sortorder_mfg in each affected manufacturer item.
			foreach($mfgid_array as $key => $cat_temp) {
				$rand = rand(10000, 99999);

				$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_mfgid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

				$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder_mfg INT auto_increment primary key NOT NULL;");

				// Copy data into the table which then gives them a new p_sortorder_mfg at the end of the table with auto increment.
				$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_mfgid) SELECT p_id, p_mfgid FROM " . TABLE_PRODUCTS . " WHERE p_mfgid = '" . $fluid->php_escape_string($cat_temp['m_id']) . "' ORDER BY p_sortorder_mfg ASC");

				// Merge data from temp_table_sort into temp_table_rand via p_id.
				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder_mfg = src.p_sortorder_mfg, dest.p_mfgid = '" .$fluid->php_escape_string($cat_temp['m_id']) . "' WHERE dest.p_id = src.p_id");

				// Drop the temporary table.
				$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
			}
		}

		// Need to execute the js_select_clear_p_selection_category first in the return stack.
		$execute_functions[0]['function'] = "js_select_clear_p_selection_category";
		$execute_functions[0]['data'] = base64_encode(json_encode(base64_encode(json_encode($selection_cat_remove))));
		$execute_functions[1]['function'] = "js_html_remove_element";
		$execute_functions[1]['data'] = base64_encode(json_encode('category-list-div-' . $c_id));
		$execute_functions[2]['function'] = "js_sortable_categories";
		$execute_functions[2]['data'] = base64_encode(json_encode(""));
		$execute_functions[3]['function'] = "js_modal_hide";
		$execute_functions[3]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		if(isset($delete_array)) {
			foreach($delete_array as $value) {
				// Delete the product images off the server.
				foreach(json_decode(base64_decode($value)) as $key => $img)
					unlink(FOLDER_IMAGES . $img->file->image);
			}
		}

		if(isset($delete_array_catman)) {
			foreach($delete_array_catman as $value) {
				// Delete the category or manufacturer images off the server.
				foreach(json_decode(base64_decode($value)) as $key => $img)
					unlink(FOLDER_IMAGES . $img->file->image);
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

// Clear the image temp folder. Used in multi item editing.
function php_delete_image_temp() {
	$fluid = new Fluid();

	try {
		return $fluid->php_delete_image_temp();
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// This can only delete files in the image folders only. Could be a potentinal vulnerability. However it is only available in admin mode, so the threat is not that high.
function php_delete_file() {
	$fluid = new Fluid();

	try {
		return $fluid->php_delete_file(base64_decode($_REQUEST['data']));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_file_processing() {
	$fluid = new Fluid ();

	if(isset($_REQUEST['f_session_id']))
		$_SESSION['fluid_admin'] = base64_decode($_REQUEST['f_session_id']);

	return base64_encode(json_encode($fluid->php_process_file_uploads($_FILES)));
}

function php_filter_select_reload() {
	try {
		$filter_data = $_REQUEST['filterdata'];
		$filters_html_select = NULL;
		$filters_html_select = php_html_categories_filters_select($filter_data)['innerHTML'];

		$execute_functions[0]['function'] = "js_html_insert_element";
		$execute_functions[0]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($filters_html_select), "parent" => base64_encode("category-filter-select-div"))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// This returns a array of a category or categories.
function php_html_categories($c_id = NULL, $mode = NULL, $query_where = NULL) {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		$fluid_mode = new Fluid_Mode($mode);

		if($c_id)
			$where = "WHERE " . $fluid_mode->X_id . " = '" . $fluid->php_escape_string($c_id) . "' ";
		else if(isset($query_where))
			$where = "WHERE " . $fluid->php_escape_string($query_where);
		else
			$where = NULL;

		if($mode == "manufacturers") {
			$mode_where = "mfgid";
		}
		else {
			$mode_where = "catid";
		}

		// --> 12 seconds.
		//$fluid->php_db_query("SELECT " . $fluid_mode->X . ".*, (SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id) AS product_count FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");

		// --> 22 seconds.
		//$fluid->php_db_query("SELECT " . $fluid_mode->X . ".*, (SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id) AS product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id AND p.p_stock > 0) AS product_stock FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");

		// --> 32 seconds.
		if(isset($_SESSION['f_show_data'])) {
			if($_SESSION['f_show_data'] == TRUE)
				$fluid->php_db_query("SELECT " . $fluid_mode->X . ".*, (SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id) AS product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id AND p.p_stock > 0) AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p WHERE p.p_" . $mode_where . "=" . $fluid_mode->X . "." . $fluid_mode->X . "_id AND p.p_stock > 0) AS product_value FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");
			else // --> Fastest.
				$fluid->php_db_query("SELECT " . $fluid_mode->X . ".* FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");
		}
		else // --> Fastest.
			$fluid->php_db_query("SELECT " . $fluid_mode->X . ".* FROM " . $fluid_mode->table . " " . $fluid_mode->X . " " . $where . " ORDER BY " . $fluid_mode->X . "_sortorder ASC");

		$cats_array = NULL;

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $value) {
				// This is a root category.
				if(!isset($value[$fluid_mode->X . '_parent_id']))
					$cats_array['root']['childs'][$value[$fluid_mode->id]] = $value;
				else
					$cats_array[$value[$fluid_mode->X . '_parent_id']]['childs'][$value[$fluid_mode->id]] = $value; // Has a parent so this is a child.
			}
		}

		$return = "";
		$tmp_array = Array();
		$action_array = Array();

		if(isset($cats_array)) {
			if(isset($cats_array['root']['childs'])) {
				// Loop through the parents and build them.
				foreach($cats_array['root']['childs'] as $parent_id => $parent) {
					$edit_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor&data=" . base64_encode(json_encode($parent[$fluid_mode->id])) . "&parent=true&mode=" . $mode)));

						$delete_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_delete&data=" . base64_encode(json_encode(array("c_id" => base64_encode($parent[$fluid_mode->id]), "c_name" => base64_encode($parent[$fluid_mode->name]), "mode" => base64_encode($mode), "parent_flag" => true))))));
						$action_button = "<div id='dropdown-cat' name='dropdown-cat-id-" . $parent[$fluid_mode->id] . "' class='dropdown' style='display:inline;'>
						<a id='dropdown-cat' class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>" . $parent[$fluid_mode->name] . " <span id='dropdown-cat' class='caret'></span>
						</a>
						  <ul id='dropdown-cat' class='dropdown-menu' aria-labelledby='dropdownMenu1'>
							<li id='dropdown-cat'><a id='dropdown-cat' onClick='js_fluid_ajax(\"" . $edit_category_link . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Edit</a></li>
							<li id='dropdown-cat'><a id='dropdown-cat' onClick='js_fluid_ajax(\"" . $delete_category_link . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span> Delete</a></li>
						  </ul>
						  </div>";
						$action_array[$parent[$fluid_mode->id]] = $action_button;

					// If the category is disabled, lets load the lock badge.
					if($parent[$fluid_mode->enable] == 0)
						$disable_style = "block";
					else
						$disable_style = "none";

					$eye = "<span id='category-badge-select-lock-" . $parent[$fluid_mode->id] . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					// Encoded data of a empty div. Stored into a data attribute which the javascript code can grab when required.
					$data_crumb['parent'] = base64_encode("fluid-category-listing-childs-" . $parent[$fluid_mode->id]);
					$data_crumb['innerHTML'] = base64_encode("<div id='cat-parent-empty-" . $parent[$fluid_mode->id] . "'>No child " . strtolower($fluid_mode->breadcrumb) . " in this parent " . $fluid_mode->mode_name_real . ".</div>");

					$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $parent[$fluid_mode->id] . "'><li><div onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='list-group-item moveparent' style='border: 1px solid #BBBBBB; background-color: #DDDDDD;'><span id='category-span-open-" . $parent[$fluid_mode->id] . "' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>" . $eye . "</div><div name='fluid-category-listing-childs' data-crumb='" . base64_encode(json_encode($data_crumb)) . " ' id='fluid-category-listing-childs-" . $parent[$fluid_mode->id] . "' style='padding: 5px 5px 5px 5px; border: 1px solid #DDDDDD; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;'>";

					$parent[$fluid_mode->name] = base64_encode($parent[$fluid_mode->name]);
					$parent[$fluid_mode->seo] = base64_encode($parent[$fluid_mode->seo]);
					$parent[$fluid_mode->desc] = base64_encode($parent[$fluid_mode->desc]);
					$parent['mode'] = $mode;

					// This hidden div is making our sortables +1 higher in the sorting update in php_sortable_categories_update(); if it is put after the first <ul> in parent. It needs to be before the first <ul>. $return_last solves the problem as we merge it after.
					// This is used by js_sortable_categories() and js_sortable_categories_update() for passing some data.
					$return_last = "<div id='category-list-div-" . $parent[$fluid_mode->id] . "-data' style='display:none;'>" . base64_encode(json_encode($parent)) . "</div>";

					$data_tmp = php_html_categories_child($cats_array, $parent_id, $fluid_mode);
					// In case anyone wonders if the index keys overlap? According to php.net - "The + operator returns the right-hand array appended to the left-hand array; for keys that exist in both arrays, the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored.
					$action_array = $action_array + $data_tmp['action_array'];
					$return .= $data_tmp['html'];
					$return .= "</div>" . $return_last . "</li></ul>";
				}
			}
			else {
				// At the moment, only php_category_create_and_edit() uses this while in edit mode to refresh a updated category.
				foreach($cats_array as $parent_id => $parent) {
					$data_tmp = php_html_categories_child($cats_array, $parent_id, $fluid_mode);
					// In case anyone wonders if the index keys overlap? According to php.net - "The + operator returns the right-hand array appended to the left-hand array; for keys that exist in both arrays, the elements from the left-hand array will be used, and the matching elements from the right-hand array will be ignored.
					$action_array = $action_array + $data_tmp['action_array'];
					$return .= $data_tmp['html'];
					$data['return_last'] = base64_encode(json_encode($parent));
				}

			}
		}

		$data['html'] = $return;
		$data['action_html'] = $action_array;
		$data['error'] = $fluid->db_error;

		$fluid->php_db_commit();

		return $data;
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_html_categories_child($cats_array, $parent_id, $fluid_mode) {
	$return = NULL;
	$action_array = Array();
	if(isset($cats_array[$parent_id]['childs'])) {
		// Loop through childs and build them.
		foreach($cats_array[$parent_id]['childs'] as $c_id => $value) {
			$temp_url = $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER;
			$mode_array = base64_encode(json_encode(Array($fluid_mode->X_id => $value[$fluid_mode->id], "mode" => $fluid_mode->mode)));

			$temp_data = "load=true&function=php_load_category_products&data=" . $mode_array;
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $value[$fluid_mode->id] . "'><li>"; //list-style: none; removes bullet poins on <ul> lists. <ul> and <li> needed for sortable to work properly with nested divs in the orders I want it to be. padding-left needs to be reset as well to remove indents.
				$return .= "<div id='category-a-" . $value[$fluid_mode->id] . "' style='height: 40px;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_category_stack(\"" . $value[$fluid_mode->id] . "\", \"" . $temp_url . "\", \"" . $temp_data . "\", \"category-div-" . $value[$fluid_mode->id] . "\", event, FluidVariables.v_selection.p_selection);' class='list-group-item movecategory'>";

					if(isset($_SESSION['f_show_data'])) {
						if($_SESSION['f_show_data'] == TRUE) {
							if(empty($value['product_value']))
								$value['product_value'] = 0;

							if(empty($value['product_stock']))
								$value['product_stock'] = 0;

							if(empty($value['product_count']))
								$value['product_count'] = 0;
						}
					}

					if(isset($value['product_value']))
						$return .= "<span id='category-badge-count-value-" . $value[$fluid_mode->id] . "' class='badge badge-hide' style='min-width: 140px;'>" . HTML_CURRENCY . number_format($value['product_value'], 2, '.', ',') . " <div style='display: inline-block; font-size: 10px;'>(cost value)</div></span>";

					if(isset($value['product_stock']))
						$return .= "<span id='category-badge-count-stock-" . $value[$fluid_mode->id] . "' class='badge badge-hide' style='min-width: 88px;'>" . $value['product_stock'] . " stock</span>";

					if(isset($value['product_count']))
						$return .= "<span id='category-badge-count-" . $value[$fluid_mode->id] . "' class='badge' style='min-width: 75px;'>" . $value['product_count'] . " items</span>";

					$return .= "<span id='category-badge-select-count-" . $value[$fluid_mode->id] . "' class='badge' style='display:none;'></span>";

					// If the category is disabled, lets load the lock badge.
					if($value[$fluid_mode->enable] == 0)
						$disable_style = "block";
					else
						$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-" . $value[$fluid_mode->id] . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$edit_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_creator_editor&data=" . base64_encode(json_encode($value[$fluid_mode->id])) . "&mode=" . $fluid_mode->mode)));

					$delete_category_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_load_category_delete&data=" . base64_encode(json_encode(array("c_id" => base64_encode($value[$fluid_mode->id]), "c_name" => base64_encode($value[$fluid_mode->name]), "mode" => base64_encode($fluid_mode->mode), "parent_flag" => false))))));
					$action_button = "<div id='dropdown-cat' name='dropdown-cat-id-" . $value[$fluid_mode->id] . "' class='dropdown' style='display:inline;'>
					<a id='dropdown-cat' class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>" . $value[$fluid_mode->name] . " <span id='dropdown-cat' class='caret'></span>
					</a>
					  <ul id='dropdown-cat' class='dropdown-menu' aria-labelledby='dropdownMenu1'>
						<li id='dropdown-cat'><a id='dropdown-cat' onClick='js_fluid_ajax(\"" . $edit_category_link . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Edit</a></li>
						<li id='dropdown-cat' class='disabled'><a id='dropdown-cat' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\"></span> Filter assignment</a></li>
						<li id='dropdown-cat'><a id='dropdown-cat' onClick='js_fluid_ajax(\"" . $delete_category_link . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span> Delete</a></li>
					  </ul>
					  </div>";
					$action_array[$value[$fluid_mode->id]] = $action_button;

					$return .= " <span id='category-span-closed-" . $value[$fluid_mode->id] . "' class=\"glyphicon glyphicon-expand\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
					$return .= " <span id='category-span-open-" . $value[$fluid_mode->id] . "' style='display:none;' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
				$return .= "</div>";

				// Need to encode these as they can break json arrays.
				// These values could probably be removed. I have to double check. I think only the id and sort order is required. This would save some bandwidth and increase speeds.
				$value[$fluid_mode->name] = base64_encode($value[$fluid_mode->name]);
				$value[$fluid_mode->seo] = base64_encode($value[$fluid_mode->seo]);
				$value[$fluid_mode->desc] = base64_encode($value[$fluid_mode->desc]);
				$value['mode'] = $fluid_mode->mode;

				// This is used by js_sortable_categories() and js_sortable_categories_update() for passing some data.
				$return .= "<div id='category-list-div-" . $value[$fluid_mode->id] . "-data' style='display:none;'>" . base64_encode(json_encode($value)) . "</div>";

				$return .= "<div id='category-div-" . $value[$fluid_mode->id] . "'></div>";
			$return .= "</li></ul>";
			$tmp_array['categories'][$value[$fluid_mode->id]]['div'] = base64_encode("#category-list-div-" . $value[$fluid_mode->id]);
		}
	}
	else
		$return .= "<div id='cat-parent-empty-" . $parent_id . "'>No child " . strtolower($fluid_mode->breadcrumb) . " in this parent " . $fluid_mode->mode_name_real . ".</div>";

	$data_return['action_array'] = $action_array;
	$data_return['html'] = $return;

	return $data_return;
}

// Returns back a formatted html list of the category filters.
function php_html_categories_filters($filters) {
	try {
		/*
		// Filter object structure.
			stdClass Object
			(
				[0] => stdClass Object
					(
						[filter_name] => UmVzb2x1dGlvbg==
						[filter_id] => NDg1MjEyODEw
						[filter_order] => 0
						[sub_filters] => stdClass Object
							(
								[0] => stdClass Object
									(
										[sub_name] => NEs=
										[sub_id] => ODYzNTkyMzU5
									)

								[1] => stdClass Object
									(
										[sub_name] => MTA4MFA=
										[sub_id] => NTUyMjA3NzAy
									)

							)

					)
			)
		*/

		$filters_html = NULL;
		$filter_array = NULL;
		$filters_obj = json_decode(base64_decode($filters));
		$filter_block_list = "none";
		$filter_block_none = "block";

		$i = 0;

		if(isset($filters_obj)) {
			//PHP Warning:  get_object_vars() expects parameter 1 to be object, array given in /var/www/local/fluid/admin/fluid.loader.php on line 476, referer: http://local.leosadmin.com/index.php
			if(count(get_object_vars(json_decode(base64_decode($filters)))) > 0) {
				$filter_block_list = "block";
				$filter_block_none = "none";
			}

			foreach($filters_obj as $key => $filt_obj) {
				$filter_array[$key]['filter_name'] = base64_decode($filt_obj->filter_name);
				$filter_array[$key]['filter_order'] = $filt_obj->filter_order;
				$filter_array[$key]['filter_id'] = $filt_obj->filter_id;
				$filter_array[$key]['sub_filters_obj'] = $filt_obj->sub_filters;

				// Need to update the $i position if we are adding a individual filter category to the list.
				if(isset($filt_obj->count))
					$i = $filt_obj->count;
			}

			if(isset($filter_array)) {
				foreach($filter_array as $kd => $data_value) {
					$filters_html .= "<ul style='list-style: none; padding-left:0px;' filter-id='" . $data_value['filter_id'] . "' data-subid='" . $i . "' name='filter-list-div-ul-name' title='" . base64_encode($data_value['filter_name']) . "' id='filters-list-div-" . $i . "'><li>";

						$filters_html .=  "<div id='filters-a-" . $i . "' style='height: 40px;' onClick='js_filter_stack(\"" . $i . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\" class='list-group-item'>";

							$action_button = "<input class='fluid-form-filter' id='dropdown-filter-rename-input' name='dropdown-filter-rename-input-" . $i . "' style='display:none;' type=\"text\" placeholder=\"Filter category name\" aria-describedby=\"basic-addon1\" value=\"" . htmlspecialchars($data_value['filter_name']) . "\" onkeydown = 'if(event.keyCode == 13){ this.style.display=\"none\"; js_filter_rename(\"" . $i . "\", this.value); }' onBlur='this.style.display=\"none\"; js_filter_rename(\"" . $i . "\", this.value);'></input><div id='dropdown-filter' name='dropdown-filter-id-" . $i . "' class='dropdown' style='display:inline;'>
								<a id='dropdown-filter' class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'><div style='display:inline;' id='dropdown-filter' name='filter-raw-name-" . $i . "'>" . $data_value['filter_name'] . "</div> <span id='dropdown-filter' class='caret'></span>
								</a>
								  <ul id='dropdown-filter' class='dropdown-menu' aria-labelledby='dropdownMenu1'>
									<li id='dropdown-filter'><a id='dropdown-filter' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_filter_rename_blur(\"" . $i . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Rename</a></li>
									<li id='dropdown-filter'><a id='dropdown-filter' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_html_remove_element(\"filters-list-div-" . $i . "\"); js_category_filter_update_rows(); js_category_filter_sortable(); js_category_refresh_select();'><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span> Delete</a></li>
								  </ul>
								  </div>";

							$filters_html .= " <span id='filter-span-closed-" . $i . "' class=\"glyphicon glyphicon-chevron-right\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";
							$filters_html .= " <span id='filter-span-open-" . $i . "' style='display:none;' class=\"glyphicon glyphicon-chevron-down\" aria-hidden=\"true\" style='padding-right:5px;'> " . $action_button . "</span>";

						$filters_html .= "</div>"; // filters-a-$i

							$data_html = "<div id='filter-div-block-" . $i . "' style='display:inline;'>";
							$data_html .= "<div id='filters-cat-new-div-" . $i . "' name='filters-cat-new-div-name' class='table-responsive panel panel-default' style='display:" . $filter_block_list . ";'>";
							$data_html .= "<table class='table table-hover' id='filters-cat-" . $data_value['filter_id'] . "'>";
							$data_html .= "<tbody>";
							$sub_filter_found = "block";
								if(isset($data_value['sub_filters_obj'])) {
									foreach($data_value['sub_filters_obj'] as $key => $filter) {

										$sub_action_button = "<input class='fluid-form-filter' id='dropdown-sub-filter-rename-input' name='dropdown-sub-filter-rename-input-" . $i . "-" . $key . "' style='display:none;' type=\"text\" placeholder=\"Sub filter name\" aria-describedby=\"basic-addon1\" value=\"" . base64_decode($filter->sub_name) . "\" onkeydown = 'if(event.keyCode == 13){ this.style.display=\"none\"; js_sub_filter_rename(\"" . $i . "-" . $key . "\", this.value); }' onBlur='this.style.display=\"none\"; js_sub_filter_rename(\"" . $i . "-" . $key . "\", this.value);'></input><div id='dropdown-sub-filter' name='dropdown-sub-filter-id-" . $i . "-" . $key . "' class='dropdown' style='display:inline;'>
										<a id='dropdown-sub-filter' class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'><div style='display:inline;' id='dropdown-filter' name='sub-filter-raw-name-" . $i . "-" . $key . "'>" . base64_decode($filter->sub_name) . "</div> <span id='dropdown-sub-filter' class='caret'></span>
										</a>
										  <ul id='dropdown-sub-filter' class='dropdown-menu' aria-labelledby='dropdownMenu1'>
											<li id='dropdown-sub-filter'><a id='dropdown-filter' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_sub_filter_rename_blur(\"" . $i . "-" . $key . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Rename</a></li>
										  </ul>
										  </div>";

										$sub_filter_found = "none";

										$data_html .= "<tr id='cf-tr-" . $i . "-" . $key . "'><td id='cf-tr-" . $i . "-" . $key . "' style='text-align:center;'><span style='font-size: 16px;' class='glyphicon glyphicon-move moverow' aria-hidden='true'></span></td><td>" . $sub_action_button .  "</td><td style='display:none;'>" . $filter->sub_id . "</td><td id='cf-td-" . $i . "-" . $key . "' style='display:none;'>" . base64_decode($filter->sub_name) . "</td><td style='text-align:center;'><button type='button' class='btn btn-primary' onClick='js_html_remove_element(\"cf-tr-" . $i . "-" . $key . "\"); js_category_sub_filter_sortable(\"" . $i . "\"); js_category_filter_sub_update_rows(\"" . $data_value['filter_id'] . "\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></span> Remove</button></td></tr>";
									}
								}
							$data_html .= "</tbody>";
							$data_html .= "</table>"; // filters-cat-
							$data_html .= "<div id='filters-cat-none-" . $data_value['filter_id'] . "' style='margin: 10px 10px 10px 10px; display: ". $sub_filter_found . ";'>No filters exist for this category.</div>";
							$data_html .= "</div>"; // filters-cat-new-div
							$data_html .= "</div>"; // filter-div-block-$i
						$filters_html .= "<div id='filter-list-div-" . $i . "-data' style='display:none;'>" . $data_html . "</div>";
						$filters_html .= "<div id='filter-div-" . $i . "'></div>";

					$filters_html .= "</li></ul>";

					$i++;
				}
			}
		}

		$data['innerHTML'] = $filters_html;
		$data['count'] = $i;

		return $data;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_html_categories_filters_select($filters) {
	try {
		$filters_html = NULL;
		$filter_array = NULL;
		$filters_obj = json_decode(base64_decode($filters));

		$filters_html .= "<select id='category-filter-select' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-width=\"50%\" data-live-search=\"true\">";

		$i = 0;
		if(isset($filters_obj)) {
			foreach($filters_obj as $key => $filt_obj) {
				$filter_array[$key]['filter_name'] = base64_decode($filt_obj->filter_name);
				$filter_array[$key]['filter_order'] = $filt_obj->filter_order;
				$filter_array[$key]['filter_id'] = $filt_obj->filter_id;
			}


			if(isset($filter_array)) {
				foreach($filter_array as $kd => $data_value) {
					$filters_html .= "<option value='" . htmlspecialchars($data_value['filter_id']) . "'>" . $data_value['filter_name'] . "</option>";
					$i++;
				}

			}
		}

		$filters_html .= "</select>";

		$data['innerHTML'] = $filters_html;
		$data['count'] = $i;

		return $data;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// This takes a array of product data and displays them in a neatly formatted html table.
function php_html_items($data_array, $selection_array = NULL, $mode = NULL) {
	$fluid_mode = new Fluid_Mode($mode);

	// Used for keeping track which items are already selected.
	$tmp_selection_array = Array();
	if(isset($selection_array)) {
		foreach(json_decode(base64_decode($selection_array)) as $product) {
			$tmp_selection_array[$product->p_id] = $product->p_id;
		}
	}

	// Used for passing the products in this category to the select all products js functions.
	$tmp_product_array = Array();
	$tmp_product_stock_array = Array();
	$tmp_product_no_stock_array = Array();
	$tmp_product_enabled_array = Array();
	$tmp_product_disabled_array = Array();
	$tmp_product_price_array = Array();
	$tmp_product_noprice_array = Array();
	$tmp_product_nodimensions_array = Array();
	$tmp_product_noweight_array = Array();
	$tmp_product_noready_array = Array();
	$tmp_product_ready_array = Array();
	$tmp_product_images_array = Array();
	$tmp_product_noimages_array = Array();
	$tmp_product_c_filters_array = Array();
	$tmp_product_no_c_filters_array = Array();
	$tmp_product_no_key_words = Array();
	$tmp_product_key_words = Array();
	$tmp_product_trending = Array();
	$tmp_product_trending_not = Array();

	$p_catmfgid = NULL;

	if(isset($data_array)) {
		if(isset($data_array[0]) && $fluid_mode->mode != "items")
			$p_catmfgid = (int)$data_array[0][$fluid_mode->p_catmfg_id];
		else
			$p_catmfgid = $fluid_mode->mode;

		foreach($data_array as $value) {
			if($value['p_component'] == TRUE) {
				$fluid_stock = new Fluid();
				$value['p_stock'] = $fluid_stock->php_process_stock($value);
			}

			if($value['p_trending'] > 0) {
				$tmp_product_trending[$value['p_id']] = $value['p_enable'];
			}
			else {
				$tmp_product_trending_not[$value['p_id']] = $value['p_enable'];
			}

			if(strlen(trim($value['p_keywords'])) > 0)
				$tmp_product_key_words[$value['p_id']] = $value['p_enable'];
			else
				$tmp_product_no_key_words[$value['p_id']] = $value['p_enable'];

			$tmp_product_array[$value['p_id']] = $value['p_enable'];

			if($value['p_stock'] > 0)
				$tmp_product_stock_array[$value['p_id']] = $value['p_enable'];
			else
				$tmp_product_no_stock_array[$value['p_id']] = $value['p_enable'];

			if($value['p_enable'] > 0)
				$tmp_product_enabled_array[$value['p_id']] = $value['p_enable'];

			if($value['p_enable'] < 1)
				$tmp_product_disabled_array[$value['p_id']] = $value['p_enable'];

			if($value['p_price'] > 0)
				$tmp_product_price_array[$value['p_id']] = $value['p_enable'];

			if($value['p_price'] <= 0)
				$tmp_product_noprice_array[$value['p_id']] = $value['p_enable'];

			if($value['p_length'] <= 0 || $value['p_width'] <= 0 || $value['p_height'] <= 0)
				$tmp_product_nodimensions_array[$value['p_id']] = $value['p_enable'];

			if($value['p_weight'] <= 0)
				$tmp_product_noweight_array[$value['p_id']] = $value['p_enable'];

			if($value['p_weight'] <= 0 || $value['p_length'] <= 0 || $value['p_width'] <= 0 || $value['p_height'] <= 0 || $value['p_price'] <= 0 || empty($value['p_weight']) || empty($value['p_height']))
				$tmp_product_noready_array[$value['p_id']] = $value['p_enable'];
			else
				$tmp_product_ready_array[$value['p_id']] = $value['p_enable'];

			$img_tmp = json_decode(base64_decode($value['p_images']));

			if(!empty($img_tmp)) {
				if(is_array($img_tmp)) {
					if(count($img_tmp) > 0) {
						$tmp_product_images_array[$value['p_id']] = $value['p_enable'];
					}
					else {
						$tmp_product_noimages_array[$value['p_id']] = $value['p_enable'];
					}
				}
				else {
					$tmp_product_noimages_array[$value['p_id']] = $value['p_enable'];
				}
			}
			else {
				$tmp_product_noimages_array[$value['p_id']] = $value['p_enable'];
			}

			$c_filters = json_decode($value['p_c_filters']);

			if(!empty($c_filters)) {
				if(count($c_filters) > 0)
					$tmp_product_c_filters_array[$value['p_id']] = $value['p_enable'];
				else
					$tmp_product_no_c_filters_array[$value['p_id']] = $value['p_enable'];
			}
			else
				$tmp_product_no_c_filters_array[$value['p_id']] = $value['p_enable'];
		}
	}
	$return = "<div class='table-responsive panel panel-default'>";


	$select_button = "<div class='dropdown'>
	<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
      Select <span class='caret'></span>
    </a>
	  <ul class='dropdown-menu' aria-labelledby='dropdownMenu1'>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_trending)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all trending</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_trending_not)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all not trending</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_images_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/images</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noimages_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/no images</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_enabled_array)) . "\");'
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_stock_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all in stock</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_no_stock_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no stock</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_enabled_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all enabled</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_disabled_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all disabled</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_price_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/price</a></li>

		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_key_words)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all with keywords</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_no_key_words)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no keywords</a></li>

		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noprice_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no price</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_nodimensions_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no dimensions</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noweight_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all no weight</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_c_filters_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/c filters</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_no_c_filters_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all w/no c filters</a></li>
		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_noready_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all not ready</a></li>

		<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_ready_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all ready</a></li>
		<li><a onClick='js_select_clear_p_selection_category(\"" . base64_encode(json_encode(array($p_catmfgid))) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-minus\" aria-hidden=\"true\"></span> Un-select all</a></li>
	  </ul>
	  </div>";

	$return .= "<table class='table table-condensed table-hover' id='cat-" . $p_catmfgid . "'>";

	if(count($data_array) == 0) {
		$return .= "<tr><td>" . $fluid_mode->msg_no_products . "</td></tr>";
	}
	else {
		$return .= "<thead>";
		$return .= "<tr style='font-weight: bold;'>";

		if($fluid_mode->mode != "items") {
			$return .= "<td></td>";
		}

		$return .= "<td name='f-cell-select' style='text-align:center; min-width: 60px; display:" . $_SESSION['f_admin_columns']['f-cell-select']['data'] . ";'>" . $select_button . "</td><td style='display: none; text-align:center; min-width: 75px;'></td><td name='f-cell-image' style='display:" . $_SESSION['f_admin_columns']['f-cell-image']['data'] . ";'>Image</td><td name='f-cell-manufacturer' style='text-align: center; display:" . $_SESSION['f_admin_columns']['f-cell-manufacturer']['data'] . ";'>" . $fluid_mode->mode_name . "</td>";

		if($fluid_mode->mode == "items") {
			$return .= "<td name='f-cell-category' style='text-align: center; display:" . $_SESSION['f_admin_columns']['f-cell-category']['data'] . ";'>" . $fluid_mode->mode_name_real_cap . "</td>";
		}

		$return .= "<td name='f-cell-name' style='display:" . $_SESSION['f_admin_columns']['f-cell-name']['data'] . ";'>Item</td><td name='f-cell-upc' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-upc']['data'] . ";'>UPC/EAN</td><td name='f-cell-code' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-code']['data'] . ";'>Mfg Code</td><td name='f-cell-length' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-length']['data'] . ";'>Length (cm)</td><td name='f-cell-width' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-width']['data'] . ";'>Width (cm)</td><td name='f-cell-height' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-height']['data'] . ";'>Height (cm)</td><td name='f-cell-weight' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-weight']['data'] . ";'>Weight (kg)</td><td name='f-cell-stock' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-stock']['data'] . ";'>Stock</td><td name='f-cell-costavg' style='text-align:right; display:" . $_SESSION['f_admin_columns']['f-cell-costavg']['data'] . ";'>Cost Avg</td><td name='f-cell-cost' style='text-align:right; display:" . $_SESSION['f_admin_columns']['f-cell-cost']['data'] . ";'>Cost</td><td name='f-cell-margin' style='text-align:right; display:" . $_SESSION['f_admin_columns']['f-cell-margin']['data'] . ";'>Margin</td><td name='f-cell-price' style='text-align:right; display:" . $_SESSION['f_admin_columns']['f-cell-price']['data'] . ";'>Price</td><td name='f-cell-discountstart' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-discountstart']['data'] . ";'>Discount Start</td><td name='f-cell-discountend' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-discountend']['data'] . ";'>Discount End</td><td name='f-cell-discountprice' style='text-align:center; display:" . $_SESSION['f_admin_columns']['f-cell-discountprice']['data'] . ";'>Discount Price</td>";
		//$return .= "<td style='text-align:center;'>Edit</td>";
		$return .= "</tr>";

		$return .= "</thead>";
		$return .= "<tbody class='fsortable-" . $p_catmfgid . "'>";
		foreach($data_array as $value) {
			$p_old_stock = $value['p_stock'];
			if($value['p_component'] == TRUE) {
				$fluid_stock = new Fluid();
				$value['p_stock'] = $fluid_stock->php_process_stock($value);
			}

			// Used for quick editing feature.
			$f_tmp_cat_id = $value['p_catid'];
			if($fluid_mode->mode == "manufacturers") {
				$f_tmp_cat_id = $value['p_mfgid'];
			}
			else if($fluid_mode->mode == "items") {
				$f_tmp_cat_id = "items";
			}

			if(in_array($value['p_id'], $tmp_selection_array)) {
				if($value['p_enable'] == 0)
					$style_line_through = "text-decoration: line-through; ";
				else
					$style_line_through = "text-decoration: none; ";

				$style = "style='" . $style_line_through . "font-style: italic; vertical-align: middle; background-color: " . COLOUR_SELECTED_ITEMS . ";'";
				$checked = "checked";
			}
			else if($value['p_enable'] == 0) {
				$style = "style='text-decoration: line-through; vertical-align: middle; background-color: " . COLOUR_DISABLED_ITEMS . ";'";
				$checked = "";
			}
			else if($value['p_enable'] == 2) {
				$line_through_d = "text-decoration: line-through; ";
				if($value['p_stock'] > 0) {
					$line_through_d = NULL;
				}
				
				$style = "style='" . $line_through_d . "vertical-align: middle; background-color: " . COLOUR_DISCONTINUED_ITEMS . ";'";
				$checked = "";
			}
			else {
				$style = "style='background-color: transparent; vertical-align: middle;'";
				$checked = "";
			}

			$return .= "<tr class='ui-state-default' id='p_id_tr_" . $value['p_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='document.getElementById(\"p_id_" . $value['p_id'] . "\").click();'>";

			// This is used for resorting the order of items in category modes.
			if($fluid_mode->mode != "items") {
				$return .= "<td class='f-td' data-move='p_id_tr_" . $value['p_id'] . "' style='text-align:center; vertical-align: middle;'><span style='font-size: 16px;' class='glyphicon glyphicon-move moverow' aria-hidden='true'></span></td>";
			}

			if($value['p_enable'] > 0) {
				$style_eye = " style='text-decoration: none; font-size:12px; display:none; vertical-align: middle;' ";
			}
			else {
				$style_eye = " style='text-decoration: none !important; font-size:12px; display:block; vertical-align: middle;' ";
			}

			$p_c_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
			if(isset($value['p_c_filters'])) {
				if($value['p_c_filters'] == '{}')
					$p_c_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_c</span>";
				else
					$p_c_filters = NULL;
			}

			$p_m_filters = "<span style='color: #FF0000;' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
			if(isset($value['p_m_filters'])) {
				if($value['p_m_filters'] == '{}')
					$p_m_filters = "<span style='color: #FF0000' class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\">_m</span>";
				else
					$p_m_filters = NULL;
			}

			$p_trending = NULL;
			if(isset($value['p_trending'])) {
				if($value['p_trending'] == 1)
					$p_trending = "<span style='color: #00FF1F;' class=\"glyphicon glyphicon-fire\" aria-hidden=\"true\">_t</span>";
				else
					$p_trending = NULL;
			}

			$p_preorder = NULL;
			if(isset($value['p_preorder'])) {
				if($value['p_preorder'] == 1)
					$p_preorder = "<span style='color: #0081FF' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_p</span>";
				else
					$p_preorder = NULL;
			}

			$p_rebate_claim = NULL;
			if(isset($value['p_rebate_claim'])) {
				if($value['p_rebate_claim'] == 1)
					$p_rebate_claim = "<span style='color: #5EDF40' class=\"glyphicon glyphicon-usd\" aria-hidden=\"true\">_r</span>";
				else
					$p_rebate_claim = NULL;
			}

			$p_component = NULL;
			if(isset($value['p_component'])) {
				if($value['p_component'] == 1)
					$p_component = "<span style='color: #00FF40' class=\"glyphicon glyphicon-compressed\" aria-hidden=\"true\">_cp</span>";
				else
					$p_component = NULL;
			}

			$p_stock_end = NULL;
			if(isset($value['p_stock_end'])) {
				if($value['p_stock_end'] == 1)
					$p_stock_end = "<span style='color: #00EFFF;' class=\"glyphicon glyphicon-cloud\" aria-hidden=\"true\">_e</span>";
				else
					$p_stock_end = NULL;
			}

			$p_showalways = NULL;
			if(isset($value['p_showalways'])) {
				if($value['p_showalways'] == 1)
					$p_showalways = "<span style='color: #0081FF' class=\"glyphicon glyphicon-flash\" aria-hidden=\"true\">_a</span>";
				else
					$p_showalways = NULL;
			}

			$p_namenum = NULL;
			if(isset($value['p_namenum'])) {
				if($value['p_namenum'] == 1)
					$p_namenum = "<span style='color: #FFE900' class=\"glyphicon glyphicon-paperclip\" aria-hidden=\"true\">_#</span>";
				else
					$p_namenum = NULL;
			}

			$p_discontinued = NULL;
			if(isset($value['p_enable'])) {
				if($value['p_enable'] == 2)
					$p_discontinued= "<span style='color: #5BC0DE' class=\"glyphicon glyphicon-certificate\" aria-hidden=\"true\">_d</span>";
				else
					$p_discontinued = NULL;
			}

			$p_rental = NULL;
			if(isset($value['p_rental'])) {
				if($value['p_rental'] == 1)
					$p_rental= "<span style='color: #DEC35B' class=\"glyphicon glyphicon-gift\" aria-hidden=\"true\">_r</span>";
				else
					$p_rental = NULL;
			}

			$p_special_order = NULL;
			if(isset($value['p_special_order'])) {
				if($value['p_special_order'] == 1)
					$p_special_order= "<span style='color: #DE815B' class=\"glyphicon glyphicon-star\" aria-hidden=\"true\">_s</span>";
				else
					$p_special_order = NULL;
			}

			$return .= "<td name='f-cell-select' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-select']['data'] . ";'><span " . $style_eye . " id='p_id_tr_" . $value['p_id'] . "_eye' class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\"></span> " . $p_c_filters . " " . $p_m_filters . " " . $p_trending . " " . $p_preorder . " " . $p_rebate_claim . " " . $p_component . " " . $p_stock_end . " " . $p_showalways . " " . $p_namenum . " " . $p_discontinued . " " . $p_rental . " " . $p_special_order . "</td>";

			if($fluid_mode->mode != "items")
				$p_catmfgid = $value[$fluid_mode->p_catmfg_id];
			else
				$p_catmfgid = $fluid_mode->mode;

			// Hide this column. It contains data used for sortable product updates.
			$return .= "<td class='f-td' style='display: none;' id='p_id_tr_" . $value['p_id'] . "_td'>" . base64_encode(json_encode(Array('p_id' => $value['p_id'], $fluid_mode->id => $p_catmfgid, 'p_sortorder' . $fluid_mode->sort_order => $value['p_sortorder' . $fluid_mode->sort_order], 'mode' => $mode))) . "</td>";

			// Hide this column. It contains data used for cell selection.
			$return .= "<td class='f-td' style='display: none; text-align:center; vertical-align: middle;'><input id='p_id_" . $value['p_id'] . "' onClick='js_cancel_event(event); js_product_select(\"" . $value['p_id'] . "\", \"" . $p_catmfgid . "\", \"" . $value['p_enable'] . "\");' type=\"checkbox\" " . $checked . "></td>";

			$fluid = new Fluid ();
			$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
			$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
			$p_images = $fluid->php_process_images($value['p_images']);
			$width_height_admin = $fluid->php_process_image_resize($p_images[0], "80", "80", $f_img_name);
			$f_image_html = "<img class='img-responsive' src='" . $_SESSION['fluid_uri'] . $width_height_admin['image'] . "' alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"/></img>";
			$return .= "<td name='f-cell-image' class='f-td' style='vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-image']['data'] . ";'>" . $f_image_html . "</td>";

			if($fluid_mode->mode == "manufacturers")
				$return .= "<td name='f-cell-manufacturer' class='f-td' style='vertical-align: middle; text-align: center; display:" . $_SESSION['f_admin_columns']['f-cell-manufacturer']['data'] . ";'>" . $value['c_name'] . "</td>";
			else
				$return .= "<td name='f-cell-manufacturer' class='f-td' style='vertical-align: middle; text-align: center; display:" . $_SESSION['f_admin_columns']['f-cell-manufacturer']['data'] . ";'>" . $value['m_name'] . "</td>";

			if($fluid_mode->mode == "items")
				$return .= "<td name='f-cell-category' class='f-td' style='vertical-align: middle; text-align: center; display:" . $_SESSION['f_admin_columns']['f-cell-category']['data'] . ";'>" . $value['c_name'] . "</td>";

			if(empty($value['p_weight'])) {
				$value['p_weight'] = 0.00;
			}

			if(empty($value['p_height'])) {
				$value['p_height'] = 0.00;
			}

			if(empty($value['p_length'])) {
				$value['p_length'] = 0.00;
			}

			if(empty($value['p_width'])) {
				$value['p_width'] = 0.00;
			}

			$return .= "<td data-id='" . $value['p_id'] . "' data-type='p_name' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_name' data-name='Name' data-values='" . base64_encode($value['p_name']) . "' name='f-cell-name' class='f-td' style='vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-name']['data'] . ";'>" . $value['p_name'] . "</td><td data-id='" . $value['p_id'] . "' data-type='p_mfgcode' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_mfgcode' data-name='UPC/EAN' data-values='" . base64_encode($value['p_mfgcode']) . "' name='f-cell-upc' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-upc']['data'] . ";'>" . $value['p_mfgcode'] . "</td><td data-id='" . $value['p_id'] . "' data-type='p_mfg_number' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_mfg_number' data-name='Mfg Code' data-values='" . base64_encode($value['p_mfg_number']) . "' name='f-cell-code' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-code']['data'] . ";'>" . $value['p_mfg_number'] . "</td><td data-id='" . $value['p_id'] . "' data-type='length' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_length' data-name='Length' data-values='" . base64_encode($value['p_length']) . "' name='f-cell-length' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-length']['data'] . ";'>" . number_format($value['p_length'], 2, '.', '') . "</td><td data-id='" . $value['p_id'] . "' data-type='width' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_width' data-name='Width' data-values='" . base64_encode($value['p_width']) . "' name='f-cell-width' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-width']['data'] . ";'>" . number_format($value['p_width'], 2, '.', '') . "</td><td data-id='" . $value['p_id'] . "' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-type='height' data-fmode='" . $fluid_mode->mode . "' data-column='p_height' data-name='Height' data-values='" . base64_encode($value['p_height']) . "' name='f-cell-height' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-height']['data'] . ";'>" . number_format($value['p_height'], 2, '.', '') . "</td><td data-id='" . $value['p_id'] . "' data-type='weight' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_weight' data-name='Weight' data-values='" . base64_encode($value['p_weight']) . "' name='f-cell-weight' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-weight']['data'] . ";'>" . number_format($value['p_weight'], 2, '.', '') . "</td><td data-id='" . $value['p_id'] . "' data-type='stock' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_stock' data-name='Stock' data-values='" . base64_encode($p_old_stock) . "' name='f-cell-stock' id='p_td_id_stock_" . $value['p_id'] . "' class='f-td' style='text-align:center; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-stock']['data'] . ";'>";

			if($value['p_component'] == TRUE) {
				$return .= "<strike>" . $p_old_stock . "</strike><br><div class='f-tooltip'><span style=\"color: #00FF40\" class=\"glyphicon glyphicon-compressed\" aria-hidden=\"true\"></span> " . $value['p_stock'] . "<span class='f-tooltiptext'>Component Stock</span></div></td>";
			}
			else {
				$return .= $value['p_stock'] . "</td>";
			}

			if($value['p_price_discount'] && ((strtotime($value['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($value['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($value['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $value['p_discount_date_end'] == NULL) || ($value['p_discount_date_start'] == NULL && $value['p_discount_date_end'] == NULL) ) ) {

				$f_cost_adjust = $value['p_cost'] - ($value['p_price'] - $value['p_price_discount']);
				$f_cost_adjust_real = $value['p_cost_real'] - ($value['p_price'] - $value['p_price_discount']);
				$f_profit = $value['p_price_discount'] - $f_cost_adjust;
				$f_margin = ($f_profit / $value['p_price']) * 100;

				$f_profit_org = $value['p_price'] - $value['p_cost'];
				$f_margin_org = ($f_profit_org / $value['p_price']) * 100;

				$return .= "<td name='f-cell-costavg' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-costavg']['data'] . ";'><div style='font-style: italic; text-decoration: line-through;'>" . number_format($value['p_cost'], 2, '.', ',') . "</div><div style='color: red;'>" . number_format($f_cost_adjust, 2, '.', ',') . "</div></td>";

				$return .= "<td data-id='" . $value['p_id'] . "' data-type='p_cost_reset' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_cost' data-name='Cost' data-values='" . base64_encode($value['p_cost_real']) . "' name='f-cell-cost' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-cost']['data'] . ";'><div style='font-style: italic; text-decoration: line-through;'>" . number_format($value['p_cost_real'], 2, '.', ',') . "</div><div style='color: red;'>" . number_format($f_cost_adjust_real, 2, '.', ',') . "</div></td>";

				$return .= "<td name='f-cell-margin' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-margin']['data'] . ";'><div style='font-style: italic; text-decoration: line-through;'>" . number_format($f_margin_org, 2, '.', ',') . "%</div><div style='color: red;'>" . number_format($f_margin, 2, '.', ',') . "%</div></td>";

				$return .= "<td data-id='" . $value['p_id'] . "' data-type='price' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_price' data-name='Price' data-values='" . base64_encode($value['p_price']) . "' name='f-cell-price' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-price']['data'] . ";'><div data-id='" . $value['p_id'] . "' data-type='price' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_price' data-name='Price' data-values='" . base64_encode($value['p_price']) . "' style='font-style: italic; text-decoration: line-through;'>" . number_format($value['p_price'], 2, '.', ',') . "</div><div data-id='" . $value['p_id'] . "' data-type='price' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_price' data-name='Price' data-values='" . base64_encode($value['p_price']) . "' style='color: red;'>" . number_format($value['p_price_discount'], 2, '.', ',') . "</div></td>";
			}
			else {
				$f_profit = $value['p_price'] - $value['p_cost'];
				if($f_profit > 0 && $value['p_price'] > 0) {
					$f_margin = ($f_profit / $value['p_price']) * 100;
				}
				else {
					$f_margin = 0;
				}

				$return .= "<td name='f-cell-costavg' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-costavg']['data'] . ";'>" . number_format($value['p_cost'], 2, '.', ',') . "</td>";

				$return .= "<td data-id='" . $value['p_id'] . "' data-type='p_cost_reset' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_cost' data-name='Cost' data-values='" . base64_encode($value['p_cost_real']) . "' name='f-cell-cost' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-cost']['data'] . ";'>" . number_format($value['p_cost_real'], 2, '.', ',') . "</td>";

				$return .= "<td name='f-cell-margin' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-margin']['data'] . ";'>" . number_format($f_margin, 2, '.', ',') . "%</td>";

				$return .= "<td data-id='" . $value['p_id'] . "' data-type='price' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_price' data-name='Price' data-values='" . base64_encode($value['p_price']) . "' name='f-cell-price' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-price']['data'] . ";'>" . number_format($value['p_price'], 2, '.', ',') . "</td>";
			}

			$return .= "<td data-id='" . $value['p_id'] . "' data-type='discount_date_start' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_discount_date_start' data-name='Discount Start' data-values='" . base64_encode($value['p_discount_date_start']) . "' name='f-cell-discountstart' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-discountstart']['data'] . ";'>" . $value['p_discount_date_start'] . "</td>";

			$return .= "<td data-id='" . $value['p_id'] . "' data-type='discount_date_end' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_discount_date_end' data-name='Discount End' data-values='" . base64_encode($value['p_discount_date_end']) . "' name='Discount End' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-discountend']['data'] . ";'>" . $value['p_discount_date_end'] . "</td>";

			$return .= "<td data-id='" . $value['p_id'] . "' data-type='price_discount' data-editor='f_quickedit' data-catid='" . $f_tmp_cat_id . "' data-fmode='" . $fluid_mode->mode . "' data-column='p_price_discount' data-name='Price Discount' data-values='" . base64_encode($value['p_price_discount']) . "' name='f-cell-discountprice' class='f-td' style='text-align:right; vertical-align: middle; display:" . $_SESSION['f_admin_columns']['f-cell-discountprice']['data'] . ";'>";
				if($value['p_price_discount'] != '') {
			 		$return .= number_format($value['p_price_discount'], 2, '.', ',');
				}
			$return .= "</td>";

			$return .= "</tr>";
		}
		$return .= "</tbody>";
	}

	$return .= "</table>";
	$return .= "</div>";

	return $return;
}

function php_html_items_editor($data, $manufacturer_data, $category_data, $editor, $mode = NULL, $multi_mode = FALSE) {
	$fluid = new Fluid ();

	try {
		$output = "<div style='margin-top:15px;'>";

			// Product id
			if($editor || $multi_mode == TRUE) {
				$output .= "<input id='product-id' type='hidden' value='" . $data['p_id'] . "'>";
			}

			// Status
			$output .= "<div class=\"input-group\">";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";

				$output .= "<select id='product-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='FluidVariables.v_product.p_status =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_enable'] == 1)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
					$output .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_enable'] == 0)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
					$output .= "<span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_enable'] == 2)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='2' style='background: #5BC0DE; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</span>\">";
					$output .= "<span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</option>";

				$output .= "</select>";
			$output .= "</div>";

			// ----------------------------------------------------------------------
			// Manufacturers.
			$man_filters_array_tmp = NULL; // Manufacturer filters.
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

							if($editor) {
								if($data['p_mfgid'] == $value['m_id'])
									$output_man .= " selected";
							}
							else if(!$editor) {
								if($i == 0)
									$output_man .= " selected";

							}

							$output_man .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['m_name'] . "</option>";
							//$output_man .= "><img src='" . $_SESSION['fluid_uri'] . $fluid->php_process_images($value['m_images'])[0] . "' style='float:left; padding-right:3px; width: 20px; height: 20px;'></img> " . $value['m_name'] . "</option>";

							$man_filters_array_tmp[$value['m_id']] = (array)json_decode(base64_decode($value['m_filters']));
							$i++;
						}
						$output_man .= "</optgroup>";
					}
				}
			}

			// Process the filters that are saved with this item before we match them up with the filters in the category.
			$p_m_filters_array = NULL;
			if(isset($data['p_m_filters'])) {
				foreach(json_decode($data['p_m_filters']) as $key => $filter_data) {
					$tmp_data = (array)($filter_data);
					$p_m_filters_array[$tmp_data['filter_id']] = $tmp_data;
				}
			}

			// Manufacturer filters. Merge the html after.
			$output_filter = NULL;
			$output_filter .= "<div class=\"input-group\" style='margin-top: 5px;'>";
			$output_filter .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Manufacturer Filters</div></span>";

				$output_filter .= "<div class='input-group' id='product-manufacturer-filters-div' style='display:inline;'>";
				$filter_array = NULL; // Filter selection html.
				$i = 0;
				if(isset($man_filters_array_tmp)) {
					foreach($man_filters_array_tmp as $key => $manufacturer) {
						$filter_array[$key]['innerHTML'] = "<select id='product-manufacturer-filters' class=\"form-control selectpicker show-menu-arrow show-tick\" multiple  data-selected-text-format=\"count > 3\" data-container=\"#fluid-modal\" data-live-search=\"true\" data-size=\"10\" data-width=\"50%\">";
						foreach($manufacturer as $filter_key => $filter) {
							$filter_array[$key]['innerHTML'] .= "<optgroup label='" . htmlspecialchars(base64_decode($filter->filter_name)) . "' data-max-options='1'>";

							foreach((array)$filter->sub_filters as $sub_key => $sub_filter) {
								$selected_filter = NULL;

								if(isset($p_m_filters_array[$filter->filter_id]['category_id']) && isset($p_m_filters_array[$filter->filter_id]['sub_id']) && isset($p_m_filters_array[$filter->filter_id]['filter_id']))
									if($p_m_filters_array[$filter->filter_id]['category_id'] == $key && $p_m_filters_array[$filter->filter_id]['filter_id'] == $filter->filter_id && $p_m_filters_array[$filter->filter_id]['sub_id'] == $sub_filter->sub_id)
										$selected_filter = " selected";

								$filter_array[$key]['innerHTML'] .= "<option value='" . htmlspecialchars(base64_encode(json_encode(Array('category_id' => $key, 'filter_id' => $filter->filter_id, 'sub_id' => $sub_filter->sub_id)))) . "'" . $selected_filter . ">" . htmlspecialchars(base64_decode($sub_filter->sub_name)) . "</option>";
							}

							$filter_array[$key]['innerHTML'] .= "</optgroup>";
						}
						$filter_array[$key]['innerHTML'] .= "</select>";

						// Output the proper filters into the filter list based on the selected category. Default is the first one if creating a new item.
						if($editor && $key == $data['p_mfgid'])
							$output_filter .= $filter_array[$key]['innerHTML'];
						else if($i == 0 && !$editor)
							$output_filter .= $filter_array[$key]['innerHTML'];

						$i++;

						$filter_array[$key]['innerHTML'] = base64_encode($filter_array[$key]['innerHTML']);
					}
				}
				$output_filter .= "</div>"; // product-manufacturer-filters-div
			$output_filter .= "</div>";

			// This block of code needs to run after the filter selection is generated, so the filter array can be passed to the client after a manufacturer change, to reload the manufacturer filters.
			$output .= "<div class=\"input-group\" style='margin-top: 5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Manufacturer</div></span>";

				$output .= "<select id='product-manufacturer' class=\"form-control selectpicker show-menu-arrow show-tick\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='js_product_switch_filters(\"manufacturer\", this.options[this.selectedIndex].value, \"" . base64_encode(json_encode($filter_array)) . "\");'>"; // Merge the filter array data to the js client call.

				$output .= $output_man; // Merge the manufacturer selection into the html.
				$output .="</select>";
			$output .= "</div>";

			$output .= $output_filter; // Merge the filter selection into the html.
			// ----------------------------------------------------------------------

			// ----------------------------------------------------------------------
			// Categories.
			$cat_filters_array_tmp = NULL; // Category filters.
			$output_cat = NULL; // Category list.
			$output_category_link = NULL; // Category Product linking list.
			$i = 0;
			if(isset($category_data)) {
				foreach($category_data as $parent) {
					if(isset($parent['childs'])) {
						$output_cat .= "<optgroup label='" . $parent['parent']['c_name'] . "'>";
						$output_category_link .= "<optgroup label='" . $parent['parent']['c_name'] . "'>";

						foreach($parent['childs'] as $value) {
							$width_height = $fluid->php_process_image_resize($fluid->php_process_images($value['c_image'])[0], "20", "20");

							$output_cat .= "<option value='" . $value['c_id'] . "'";
							$output_cat .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "\"";

							$output_category_link .= "<option value='" . $value['c_id'] . "'";

							if($editor) {
								if($data['p_catid'] == $value['c_id'])
									$output_cat .= " selected";

								// Insert code here to scan array data of product_category_linking data.
							}
							else if(!$editor) {
								if($i == 0)
									$output_cat .= " selected";
							}

							$output_cat .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value['c_name'] . "</option>";
							$output_category_link .= ">" . $value['c_name'] . "</option>";

							$cat_filters_array_tmp[$value['c_id']] = (array)json_decode(base64_decode($value['c_filters']));

							$i++;
						}
						$output_cat .= "</optgroup>";
						$output_category_link .= "</optgroup>";

					}
				}
			}

			// Process the filters that are saved with this item before we match them up with the filters in the category.
			$p_c_filters_array = NULL;
			if(isset($data['p_c_filters'])) {
				foreach(json_decode($data['p_c_filters']) as $key => $filter_data) {
					$tmp_data = (array)($filter_data);
					$p_c_filters_array[$tmp_data['filter_id']] = $tmp_data;
				}
			}

			// Category filters. Merge the html after.
			$output_filter = NULL;
			$output_filter .= "<div class=\"input-group\" style='margin-top: 5px;'>";
			$output_filter .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Category Filters</div></span>";

				$output_filter .= "<div class='input-group' id='product-category-filters-div' style='display:inline;'>";
				$filter_array = NULL; // Filter selection html.
				$i = 0;
				if(isset($cat_filters_array_tmp)) {
					foreach($cat_filters_array_tmp as $key => $category) {
						$filter_array[$key]['innerHTML'] = "<select id='product-category-filters' class=\"form-control selectpicker show-menu-arrow show-tick\" multiple data-selected-text-format=\"count > 3\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";
						foreach($category as $filter_key => $filter) {
							$filter_array[$key]['innerHTML'] .= "<optgroup label='" . htmlspecialchars(base64_decode($filter->filter_name)) . "' data-max-options='1'>";

							foreach((array)$filter->sub_filters as $sub_key => $sub_filter) {
								$selected_filter = NULL;

								if(isset($p_c_filters_array[$filter->filter_id]['category_id']) && isset($p_c_filters_array[$filter->filter_id]['sub_id']) && isset($p_c_filters_array[$filter->filter_id]['filter_id']))
									if($p_c_filters_array[$filter->filter_id]['category_id'] == $key && $p_c_filters_array[$filter->filter_id]['filter_id'] == $filter->filter_id && $p_c_filters_array[$filter->filter_id]['sub_id'] == $sub_filter->sub_id)
										$selected_filter = " selected";

								$filter_array[$key]['innerHTML'] .= "<option value='" . htmlspecialchars(base64_encode(json_encode(Array('category_id' => $key, 'filter_id' => $filter->filter_id, 'sub_id' => $sub_filter->sub_id)))) . "'" . $selected_filter . ">" . htmlspecialchars(base64_decode($sub_filter->sub_name)) . "</option>";
							}

							$filter_array[$key]['innerHTML'] .= "</optgroup>";
						}
						$filter_array[$key]['innerHTML'] .= "</select>";

						// Output the proper filters into the filter list based on the selected category. Default is the first one if creating a new item.
						if($editor && $key == $data['p_catid'])
							$output_filter .= $filter_array[$key]['innerHTML'];
						else if($i == 0 && !$editor)
							$output_filter .= $filter_array[$key]['innerHTML'];

						$i++;

						$filter_array[$key]['innerHTML'] = base64_encode($filter_array[$key]['innerHTML']);
					}
				}
				$output_filter .= "</div>"; // product-category-filters-div
			$output_filter .= "</div>";

			// This block of code needs to run after the filter selection is generated, so the filter array can be passed to the client after a category change, to reload the category filters.
			$output .= "<div class=\"input-group\" style='margin-top: 5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Category</div></span>";

				$output .= "<select id='product-category' class=\"form-control selectpicker show-menu-arrow show-tick\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='js_product_switch_filters(\"category\", this.options[this.selectedIndex].value, \"" . base64_encode(json_encode($filter_array)) . "\");'>"; // Merge the filter array data to the js client call.

				$output .= $output_cat; // Merge the category selection into the html.
				$output .="</select>";
			$output .= "</div>";

			$output .= $output_filter; // Merge the filter selection into the html.
			// ----------------------------------------------------------------------

			// Product Category Linking. Only available at the moment in the multi item editor.
			if($editor == TRUE || $multi_mode == TRUE) {
				$output .= "<div class=\"input-group\" style='margin-top: 5px;'>";
				$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Category Linking</div></span>";

					$output .= "<select id='product-category-linking' class=\"form-control selectpicker show-menu-arrow show-tick\" multiple data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>"; // Merge the filter array data to the js client call.

					$output .= $output_category_link; // Merge the product category linking data into the html.
					$output .="</select>";
				$output .= "</div>";
			}
			// ----------------------------------------------------------------------

			// Product name.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Item name</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Item name.\" aria-describedby=\"basic-addon1\" id='product-name'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_name']) . "\"";
				  $output .= ">
				</div>";

			// Barcode.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Barcode</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Item barcode.\" aria-describedby=\"basic-addon1\" id='product-barcode'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_mfgcode']) . "\"";
				  $output .= ">
				</div>";

			// MFG Number.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Mfg Number</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Item mfg code.\" aria-describedby=\"basic-addon1\" id='product-mfg-number'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_mfg_number']) . "\"";
				  $output .= ">
				</div>";

			// New Arrival Section End Date.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Arrival End</div></span>
				  <div class='input-group date' id='datetimepicker-arrival'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-arrival-end-date'";
					if($editor) {
						if($data['p_newarrivalenddate'])
							$output .= " value=\"" . htmlspecialchars($data['p_newarrivalenddate']) . "\"";
					}
				  $output .= "><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
				</div>";

				// Arrival type.
				// --> 1 == Show date and count down timer. 0 == No timer, show estimated date. ie: October 2018.
				$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
				$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Arrival Type</div></span>";

					$output .= "<select id='product-arrivaltype' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_arrivaltype =(this.options[this.selectedIndex].value);'>";

						if($editor == TRUE || $multi_mode == TRUE) {
							if(isset($data['p_arrivaltype'])) {
								if($data['p_arrivaltype'] == 1)
									$arrivaltype_selected = "selected";
								else
									$arrivaltype_selected = "";
							}
							else if(empty($data['p_arrivaltype']))
								$arrivaltype_selected = "selected";
							else if($data['p_arrivaltype'] == NULL)
								$arrivaltype_selected = "selected";
							else if($data['p_arrivaltype'] == "")
								$arrivaltype_selected = "selected";
							else
								$arrivaltype_selected = "";
						}
						else
							$arrivaltype_selected = "selected";

						$output .= "<option " . $arrivaltype_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-time' aria-hidden='true'></span> Display as date</span>\"";
						$output .= "><span class='glyphicon glyphicon-time' aria-hidden='true'></span> Display as date</option>";

						if($editor == TRUE || $multi_mode == TRUE) {
							if(isset($data['p_arrivaltype'])) {
								if($data['p_arrivaltype'] != 1 && !empty($data['p_arrivaltype']) && $data['p_arrivaltype'] != '' && $data['p_arrivaltype'] != NULL)
									$arrivaltype_selected = "selected";
								else
									$arrivaltype_selected = "";
							}
							else
								$arrivaltype_selected = "";
						}
						else
							$arrivaltype_selected = "";

						$output .= "<option " . $arrivaltype_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Display as estimated date</span>\">";
						$output .= "<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span> Display as estimated date</option>";

					$output .= "</select>";
				$output .= "</div>";
				$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Set how you want the arrival date to be display. You can display as as a date, or as a estimated arrival date without showing a day. For example: (Estimated arrival: January 2000)</div>";

			// In store pickup only item flag.
			// --> 0 == Both. 1 == In store pickup only. 2 == Shipped only.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Shipping Methods</div></span>";

				$output .= "<select id='product-instore' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_instore =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_instore'])) {
							if($data['p_instore'] != 0 && !empty($data['p_instore']) && $data['p_instore'] != '' && $data['p_instore'] != NULL)
								$pickup_selected = "selected";
							else
								$pickup_selected = "";
						}
						else
							$pickup_selected = "";
					}
					else
						$pickup_selected = "";

					$output .= "<option " . $pickup_selected . " value='0' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> Ship & Pickup</span>\"";
					$output .= "><span class='glyphicon glyphicon-cloud' aria-hidden='true'></span> Ship & Pickup</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_instore'])) {
							if($data['p_instore'] == 0)
								$pickup_selected = "selected";
							else
								$pickup_selected = "";
						}
						else if(empty($data['p_instore']))
							$pickup_selected = "selected";
						else if($data['p_instore'] == NULL)
							$pickup_selected = "selected";
						else if($data['p_instore'] == "")
							$pickup_selected = "selected";
						else
							$pickup_selected = "";
					}
					else
						$pickup_selected = "selected";

					$output .= "<option " . $pickup_selected . " value='1' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-home' aria-hidden='true'></span> Pickup only</span>\">";
					$output .= "<span class='glyphicon glyphicon-home' aria-hidden='true'></span> Pickup only</option>";

					$output .= "<option " . $pickup_selected . " value='2' style='background: #4F8CD9; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-plane' aria-hidden='true'></span> Shipped only</span>\">";
					$output .= "<span class='glyphicon glyphicon-plane' aria-hidden='true'></span> Shipped only</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Set how this item can be shipped to a customer.</div>";

			// Free shipping flag.
			// --> 1 == Allow free shipping. 0 == Free shipping not allowed.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Allow free shipping</div></span>";

				$output .= "<select id='product-freeship' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_freeship =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_freeship'])) {
							if($data['p_freeship'] == 1)
								$freeship_selected = "selected";
							else
								$freeship_selected = "";
						}
						else if(empty($data['p_freeship']))
							$freeship_selected = "selected";
						else if($data['p_freeship'] == NULL)
							$freeship_selected = "selected";
						else if($data['p_freeship'] == "")
							$freeship_selected = "selected";
						else
							$freeship_selected = "";
					}
					else
						$freeship_selected = "selected";

					$output .= "<option " . $freeship_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Allow free shipping</span>\"";
					$output .= "><span class='glyphicon glyphicon-check' aria-hidden='true'></span> Allow free shipping</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_freeship'])) {
							if($data['p_freeship'] != 1 && !empty($data['p_freeship']) && $data['p_freeship'] != '' && $data['p_freeship'] != NULL)
								$freeship_selected = "selected";
							else
								$freeship_selected = "";
						}
						else
							$freeship_selected = "";
					}
					else
						$freeship_selected = "";

					$output .= "<option " . $freeship_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No free shipping</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No free shipping</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Set if you want to allow this item to be shipped free. This also takes into effect the free shipping formula settings in the settings menu.</div>";

			// Override zero stock listing status.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Override Listing</div></span>";

				$output .= "<select id='product-alwaysshow' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_showalways =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_showalways'])) {
							if($data['p_showalways'] != 0 && !empty($data['p_showalways']) && $data['p_showalways'] != '' && $data['p_showalways'] != NULL)
								$sa_selected = "selected";
							else
								$sa_selected = "";
						}
						else
							$sa_selected = "";
					}
					else
						$sa_selected = "";

					$output .= "<option " . $sa_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_showalways'])) {
							if($data['p_showalways'] == 0)
								$sa_selected = "selected";
							else
								$sa_selected = "";
						}
						else if(empty($data['p_showalways']))
							$sa_selected = "selected";
						else if($data['p_showalways'] == NULL)
							$sa_selected = "selected";
						else if($data['p_showalways'] == "")
							$sa_selected = "selected";
						else
							$sa_selected = "";
					}
					else
						$sa_selected = "selected";

					$output .= "<option " . $sa_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Always shows the item on the site.</div>";

			// Trending status
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Trending</div></span>";

				$output .= "<select id='product-trending' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_trending =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_trending'])) {
							if($data['p_trending'] != 0 && !empty($data['p_trending']) && $data['p_trending'] != '' && $data['p_trending'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_trending'])) {
							if($data['p_trending'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_trending']))
							$t_selected = "selected";
						else if($data['p_trending'] == NULL)
							$t_selected = "selected";
						else if($data['p_trending'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";

			// Allow preorder status.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Allow Preorders</div></span>";

				$output .= "<select id='product-preorder' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_preorder =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_preorder'])) {
							if($data['p_preorder'] != 0 && !empty($data['p_preorder']) && $data['p_preorder'] != '' && $data['p_preorder'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_preorder'])) {
							if($data['p_preorder'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_preorder']))
							$t_selected = "selected";
						else if($data['p_preorder'] == NULL)
							$t_selected = "selected";
						else if($data['p_preorder'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";

			// Allow special order flag.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Special Order</div></span>";

				$output .= "<select id='product-special-order' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_special_order =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_special_order'])) {
							if($data['p_special_order'] != 0 && !empty($data['p_special_order']) && $data['p_special_order'] != '' && $data['p_special_order'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_special_order'])) {
							if($data['p_special_order'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_special_order']))
							$t_selected = "selected";
						else if($data['p_special_order'] == NULL)
							$t_selected = "selected";
						else if($data['p_special_order'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";

			// Allow rental flag.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Rental Flag</div></span>";

				$output .= "<select id='product-rental' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_rental =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_rental'])) {
							if($data['p_rental'] != 0 && !empty($data['p_rental']) && $data['p_rental'] != '' && $data['p_rental'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_rental'])) {
							if($data['p_rental'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_rental']))
							$t_selected = "selected";
						else if($data['p_rental'] == NULL)
							$t_selected = "selected";
						else if($data['p_rental'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";

			// Link product number with product name.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Link Name/Mfg #</div></span>";

				$output .= "<select id='product-namenum' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_namenum =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_namenum'])) {
							if($data['p_namenum'] != 0 && !empty($data['p_namenum']) && $data['p_namenum'] != '' && $data['p_namenum'] != NULL)
								$name_selected = "selected";
							else
								$name_selected = "";
						}
						else
							$name_selected = "";
					}
					else
						$name_selected = "";

					$output .= "<option " . $name_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_namenum'])) {
							if($data['p_namenum'] == 0)
								$name_selected = "selected";
							else
								$name_selected = "";
						}
						else if(empty($data['p_namenum']))
							$name_selected = "selected";
						else if($data['p_namenum'] == NULL)
							$name_selected = "selected";
						else if($data['p_namenum'] == "")
							$name_selected = "selected";
						else
							$name_selected = "";
					}
					else
						$name_selected = "selected";

					$output .= "<option " . $name_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Displays the items name and mfg number together.</div>";

			// date hide.
			if($multi_mode == TRUE) {
				$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Hide Date</div></span>
				  <div class='input-group date' id='datetimepicker-date-hide'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-date-hide'";
					if($editor) {
						if($data['p_date_hide'])
							$output .= " value=\"" . htmlspecialchars($data['p_date_hide']) . "\"";
					}
				  $output .= "><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
				</div>";

				$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> How long to show a product when it's stock is zero. This is updated on a item sale automatically, which gets set to 1 week in the future before the item will hide from the site until it has stock again. This does not apply when items always shown (Settings Menu) is enabled. <b>This only applies when the Show only in stock setting (Settings Menu) is enabled.</b></div>";
			}

			// Zero stock status // --> What to do when a item reaches zero stock.
			$output .= "<div class=\"input-group\" style='padding-top: 5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Zero Status</div></span>";

				$output .= "<select id='product-zero-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\" onchange='FluidVariables.v_product.p_zero_status =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_zero_status'] == 1)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "selected";

					$output .= "<option " . $selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
					$output .= "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_zero_status'] == 0)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "";

					$output .= "<option " . $selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
					$output .= "<span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if($data['p_zero_status'] == 2)
							$selected = "selected";
						else
							$selected = "";
					}
					else
						$selected = "";

					$output .= "<option " . $selected . " value='2' style='background: #5BC0DE; color: #fff;' data-content=\"<span class='label label-info' style='font-size:12px;'><span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</span>\">";
					$output .= "<span class='glyphicon glyphicon-certificate' aria-hidden='true'></span> Discontinued</option>";

				$output .= "</select>";
			$output .= "</div>";

			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Zero stock status. When stock reaches zero, how should the item display. This only affects displaying on the site. The real status itself does not change. <b>This will override Override Listing and Date Hide settings.</b></div>";


			// -- Panel for Price, stock, buy quantity and cost --
			$output .= "<div class=\"panel panel-default\" style='width: 70%; margin-top:20px;'>
				  <div class=\"panel-heading\">
					Price, stock and buy quantity
				  </div>
			  <div class=\"panel-body\">";

			// Product price.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Price</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Product price.\" aria-describedby=\"basic-addon1\" id='product-price'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_price']) . "\"";
				  $output .= ">
				</div>";

			// Discount price.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Discount Price</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Leave blank for no discount.\" aria-describedby=\"basic-addon1\" id='product-price-discount'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_price_discount']) . "\"";
				  $output .= ">
				</div>";

			// Mark item as a rebate claim.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:90px !important;'>Rebate / Claim</div></span>";

				$output .= "<select id='product-rebate-claim' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_rebate_claim =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_rebate_claim'])) {
							if($data['p_rebate_claim'] != 0 && !empty($data['p_rebate_claim']) && $data['p_rebate_claim'] != '' && $data['p_rebate_claim'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_rebate_claim'])) {
							if($data['p_rebate_claim'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_rebate_claim']))
							$t_selected = "selected";
						else if($data['p_rebate_claim'] == NULL)
							$t_selected = "selected";
						else if($data['p_rebate_claim'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Only applies with price discount.</div>";

			// Discount price start date picker.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Discount Start</div></span>
				  <div class='input-group date' id='datetimepicker-start'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-discount-price-start-date'";
					if($editor) {
						if($data['p_discount_date_start'])
							$output .= " value=\"" . htmlspecialchars($data['p_discount_date_start']) . "\"";
					}
				  $output .= "><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
				</div>";

			// Discount price end date picker.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Discount End</div></span>
				  <div class='input-group date' id='datetimepicker'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='product-discount-price-end-date'";
					if($editor) {
						if($data['p_discount_date_end'])
							$output .= " value=\"" . htmlspecialchars($data['p_discount_date_end']) . "\"";
					}
				  $output .= "><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
				</div>";

			// End discount after stock levels reach zero?
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:5px; height: 20px; width:90px !important; font-size: 88%;'>End at zero stock</div></span>";

				$output .= "<select id='product-stock-end' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_stock_end =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_stock_end'])) {
							if($data['p_stock_end'] != 0 && !empty($data['p_stock_end']) && $data['p_stock_end'] != '' && $data['p_stock_end'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_stock_end'])) {
							if($data['p_stock_end'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_stock_end']))
							$t_selected = "selected";
						else if($data['p_stock_end'] == NULL)
							$t_selected = "selected";
						else if($data['p_stock_end'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='display: inline-block; padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> When stock reaches zero, the discount will end.</div>";

			// Mark as a component item. Item is built with a combination of other items defined in the component tab.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:90px !important; font-size: 88%;'>Component Item</div></span>";

				$output .= "<select id='product-component' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_product.p_component =(this.options[this.selectedIndex].value);'>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_component'])) {
							if($data['p_component'] != 0 && !empty($data['p_component']) && $data['p_component'] != '' && $data['p_component'] != NULL)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else
							$t_selected = "";
					}
					else
						$t_selected = "";

					$output .= "<option " . $t_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					if($editor == TRUE || $multi_mode == TRUE) {
						if(isset($data['p_component'])) {
							if($data['p_component'] == 0)
								$t_selected = "selected";
							else
								$t_selected = "";
						}
						else if(empty($data['p_component']))
							$t_selected = "selected";
						else if($data['p_component'] == NULL)
							$t_selected = "selected";
						else if($data['p_component'] == "")
							$t_selected = "selected";
						else
							$t_selected = "";
					}
					else
						$t_selected = "selected";

					$output .= "<option " . $t_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select>";
			$output .= "</div>";
			$output .= "<div style='padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> The item stock is built using components of other items that are set in the component builder and plus any stock set on this item is added to the stock total during stock checks.</div>";

			// Product stock.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Stock</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Leave blank to set stock to zero.\" aria-describedby=\"basic-addon1\" id='product-stock'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_stock']) . "\"";
				  $output .= ">

				<input type=\"hidden\" id='product-stock-old'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_stock']) . "\"";
				  $output .= ">
				</div>";

			// Product max buy qty.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Max buy qty</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Max buy qty.\" aria-describedby=\"basic-addon1\" id='product-buyqty' value='1'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_buyqty']) . "\"";
				  $output .= ">
				</div>";

			// Product cost average
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Cost Average</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Product cost avg.\" aria-describedby=\"basic-addon1\" disabled id='product-cost-average'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_cost']) . "\"";
				  $output .= ">
				</div>";

			// Product cost.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Cost</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Product cost.\" aria-describedby=\"basic-addon1\" id='product-cost'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_cost_real']) . "\"";
				  $output .= ">

				<input type=\"hidden\" id='product-cost-old'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_cost_real']) . "\"";
				  $output .= ">
				</div>";

			 $output .= "</div>"; // body-end

			$output .= "</div>"; // panel well

			// -- Panel for product dimensions and weight --
			$output .= "<div class=\"panel panel-default\" style='width: 70%; margin-top:20px;'>
				  <div class=\"panel-heading\">
					Dimensions and weight
				  </div>
			  <div class=\"panel-body\">";

			// Product length.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Length (cm)</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Length (cm).\" aria-describedby=\"basic-addon1\" id='product-length'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_length']) . "\"";
				  $output .= ">
				</div>";

			// Product width.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Width (cm)</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Width (cm).\" aria-describedby=\"basic-addon1\" id='product-width'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_width']) . "\"";
				  $output .= ">
				</div>";

			// Product height.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Height (cm)</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Height (cm).\" aria-describedby=\"basic-addon1\" id='product-height'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_height']) . "\"";
				  $output .= ">
				</div>";

			// Product width.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:90px !important;'>Weight (kg)</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Weight (kg).\" aria-describedby=\"basic-addon1\" id='product-weight'";
					if($editor)
						$output .= " value=\"" . htmlspecialchars($data['p_weight']) . "\"";
				  $output .= ">
				</div>";

			 $output .= "</div>"; // body-end
			$output .= "</div>"; // panel well

				// Description.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>Description</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='desc-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#product-description\").fluidnote({height:200, focus: true}); document.getElementById(\"desc-button-save\").style.display=\"inline-block\"; document.getElementById(\"desc-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='desc-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#product-description\").fluidnote(\"destroy\"); document.getElementById(\"desc-button-edit\").style.display=\"inline-block\"; document.getElementById(\"desc-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
						$output .= "<div class='fluid-editor-wsyg' id='product-description'>";
						if($editor)
							$output .=  utf8_decode($data['p_desc']); //htmlentities($data['p_desc'],ENT_QUOTES | ENT_IGNORE,'UTF-8',false);
						$output .= "</div>";
					$output .= "</div>
				</div>";

				// Product details.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>Details</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='details-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#product-details\").fluidnote({height:200, focus: true}); document.getElementById(\"details-button-save\").style.display=\"inline-block\"; document.getElementById(\"details-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='details-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#product-details\").fluidnote(\"destroy\"); document.getElementById(\"details-button-edit\").style.display=\"inline-block\"; document.getElementById(\"details-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
						$output .= "<div class='fluid-editor-wsyg' id='product-details'>";
						if($editor)
							$output .= htmlentities($data['p_details'],ENT_NOQUOTES,'UTF-8',false);
						$output .= "</div>";
					$output .= "</div>
				</div>";

				// Specifications.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>Specifications</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='specs-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#product-specifications\").fluidnote({height:200, focus: true}); document.getElementById(\"specs-button-save\").style.display=\"inline-block\"; document.getElementById(\"specs-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='specs-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#product-specifications\").fluidnote(\"destroy\"); document.getElementById(\"specs-button-edit\").style.display=\"inline-block\"; document.getElementById(\"specs-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
						$output .= "<div class='fluid-editor-wsyg' id='product-specifications'>";
						if($editor)
							$output .= htmlentities($data['p_specs'],ENT_NOQUOTES,'UTF-8',false);
						$output .= "</div>";
					$output .= "</div>
				</div>";

				// What's in the box.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>What's in the box</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='box-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#product-inthebox\").fluidnote({height:200, focus: true}); document.getElementById(\"box-button-save\").style.display=\"inline-block\"; document.getElementById(\"box-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='box-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#product-inthebox\").fluidnote(\"destroy\"); document.getElementById(\"box-button-edit\").style.display=\"inline-block\"; document.getElementById(\"box-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
						$output .= "<div class='fluid-editor-wsyg' id='product-inthebox'>";
						if($editor)
							$output .= htmlentities($data['p_inthebox'],ENT_NOQUOTES,'UTF-8',false);
						$output .= "</div>";
					$output .= "</div>
				</div>";

				// SEO.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>SEO</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='seo-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='document.getElementById(\"product-seo\").value=document.getElementById(\"product-seo-textarea\").value; document.getElementById(\"product-seo\").style.display=\"block\"; document.getElementById(\"product-seo-textarea\").style.display=\"none\"; $(\"#product-seo\").focus(); document.getElementById(\"seo-button-save\").style.display=\"inline-block\"; document.getElementById(\"seo-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='seo-button-save' class='btn btn-primary' style='display:none;' onclick='document.getElementById(\"product-seo-textarea\").value=document.getElementById(\"product-seo\").value; document.getElementById(\"product-seo\").style.display=\"none\"; document.getElementById(\"product-seo-textarea\").style.display=\"block\"; document.getElementById(\"seo-button-edit\").style.display=\"inline-block\"; document.getElementById(\"seo-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border: 0px; padding: 0px;'>";
						$output .= "<textarea class='form-control' id='product-seo-textarea' rows='8' style='color: #555555; cursor:not-allowed; padding: 6px 12px; width:100%; resize: none; border: 0px; border-radius: 0px; -webkit-box-shadow: none; box-shadow: none;' disabled>";
						if($editor)
							$output .= htmlspecialchars($data['p_seo']);
						$output .= "</textarea>";

						$output .= "<textarea class='form-control' rows='8' id='product-seo' style='display:none; resize: vertical; border: none;'></textarea>";

					$output .= "</div>
				</div>";

				// Search keywords.
				$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
				  <div class=\"panel-heading\">
					<div style='display:inline-block;'><h5 style='font-weight:bold;'>Search Keywords</h5></div>
					<div style='float:right; display:inline-block;'>
						<button id='keywords-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='document.getElementById(\"product-keywords\").value=document.getElementById(\"product-keywords-textarea\").value; document.getElementById(\"product-keywords\").style.display=\"block\"; document.getElementById(\"product-keywords-textarea\").style.display=\"none\"; $(\"#product-keywords\").focus(); document.getElementById(\"keywords-button-save\").style.display=\"inline-block\"; document.getElementById(\"keywords-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
						<button id='keywords-button-save' class='btn btn-primary' style='display:none;' onclick='document.getElementById(\"product-keywords-textarea\").value=document.getElementById(\"product-keywords\").value; document.getElementById(\"product-keywords\").style.display=\"none\"; document.getElementById(\"product-keywords-textarea\").style.display=\"block\"; document.getElementById(\"keywords-button-edit\").style.display=\"inline-block\"; document.getElementById(\"keywords-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
					</div>
				 </div>
					<div class=\"panel-body\" style='border: 0px; padding: 0px;'>";
						$output .= "<textarea class='form-control' id='product-keywords-textarea' rows='8' style='color: #555555; cursor:not-allowed; padding: 6px 12px; width:100%; resize: none; border: 0px; border-radius: 0px; -webkit-box-shadow: none; box-shadow: none;' disabled>";
						if($editor)
							$output .= htmlspecialchars($data['p_keywords']);
						$output .= "</textarea>";

						$output .= "<textarea class='form-control' rows='8' id='product-keywords' style='display:none; resize: vertical; border: none;'></textarea>";

					$output .= "</div>
				</div>";
			$output .= "</div>"; // well end

			$image_array = NULL;

			if($editor) {
				$image_array = php_process_item_images_editor($data['p_images']);
			}

		$return_data_array['html'] = base64_encode($output);
		$return_data_array['image_array'] = $image_array;
		$return_data_array['p_id'] = $data['p_id'];
		//$return_data_array['fluid_error'] = $fluid->db_error;

		return $return_data_array;
	}
	catch (Exception $err) {
		//restore_error_handler();
		//$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_categories() {
	try {
		$return = "<div id='fluid-category-listing' class='list-group'>";

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		if(isset($_REQUEST['f_show_data']))
			$_SESSION['f_show_data'] = TRUE;
		else
			$_SESSION['f_show_data'] = FALSE;

		$data = php_html_categories(NULL, $mode, NULL); // Fetch the category listing.

		$return .= $data['html'];

		$return .= "</div>";
		$fluid_mode = new Fluid_Mode($mode);

		$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
		$breadcrumbs .= "<li class='active'>" . $fluid_mode->breadcrumb . "</li>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[0]['function'] = "js_clear_fluid_selection";
		$execute_functions[0]['data'] = base64_encode(json_encode(""));

		$execute_functions[1]['function'] = "js_sortable_categories";
		$execute_functions[1]['data'] = base64_encode(json_encode(""));

		$execute_functions[2]['function'] = "js_html_style_show";
		$execute_functions[2]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

		return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input($mode)), "navbarright" => base64_encode(php_html_navbar_right($mode)), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($data['error']), "error_message" => base64_encode($data['error'])));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_load_category_delete() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();
		$data_array = json_decode(base64_decode($_REQUEST['data']));
		$c_id = $fluid->php_escape_string(base64_decode($data_array->c_id));
		$c_name = base64_decode($data_array->c_name);
		$mode = base64_decode($data_array->mode);
		$fluid_mode = new Fluid_Mode($mode);
		$parent_flag = $data_array->parent_flag;

		// Warning Message
		$output = "<div style='margin-bottom:20px;'>";
		$output .= "<div class='alert alert-danger' role='alert'>Are you sure you want to delete the " . $fluid_mode->mode_name_real . ": " . $c_name . "?";

			/*
				Rebuild this query to scan for child categories if in parent mode, and then get all items from a single query in parent mode of all the childs to display here.
			*/

			//$parent_flag = filter_var($_REQUEST['parent'], FILTER_VALIDATE_BOOLEAN);
			if($parent_flag == TRUE)
				$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_mfgcode, p.p_images, m.m_id, m.m_name, m.m_parent_id, c.c_id, c.c_name, c.c_parent_id FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id WHERE " . $fluid_mode->X . "_parent_id = '" . $c_id . "'");
			else
				$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_mfgcode, p.p_images, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE " . $fluid_mode->p_catmfg_id . " = '" . $c_id . "'");

			$output_files = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
			$i = 0;
			$parent_mode_count = 0;
			$prev_key = NULL;
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					if($parent_flag == TRUE) {
						if($prev_key != $value[$fluid_mode->id]) {
							if($parent_mode_count > 0) {
								$output_files .= "</ul>";
								$parent_mode_count = 0;
							}

							$output_files .= "<ul><h3 class='panel-title'>" . $value[$fluid_mode->name] . "</h3>";
						}

						$output_files .= "<li>";
					}

					// Process the image.
					$p_images = $fluid->php_process_images($value['p_images']);
					$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

					$output_files .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";

					if($parent_flag == TRUE) {
						$prev_key = $value[$fluid_mode->id];
						$output_files .= "</li>";
					}

					$i++;
					$parent_mode_count++;
				}
			}

			if($parent_flag == TRUE)
				$output_files .= "</ul>"; // well in the loop.

			$output_files .= "</div>";

		if($i > 0)
			$output .= "<br><br>WARNING: The items listed below in this " . $fluid_mode->mode_name_real . " will also be DELETED.</div>" . $output_files;
		else
			$output .= "</div>"; //alert-danger div

		$output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-danger' role='alert'>Are you sure?</div>");
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_category_delete(\"" . $c_id . "\", \"" . $mode . "\", \"" . $parent_flag . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Category Deletion</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;
				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_category_creator_editor() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		$data_return = Array();
		$data_return['mode_filter'] = $fluid_mode->mode;
		if(isset($_REQUEST['parent']))
			$data_return['parent'] = $_REQUEST['parent'];
		else
			$data_return['parent'] = false;

		$c_id = NULL;

		// Editing mode.
		if(isset($_REQUEST['data'])) {
			$editor = TRUE;
			$c_id = json_decode(base64_decode($_REQUEST['data']));
			$fluid->php_db_query("SELECT * FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->id . " = '" . $fluid->php_escape_string($c_id) . "'");
			$data = $fluid->db_array[0];
			$modal_title = $data[$fluid_mode->name];
			$data_return['id'] = $c_id;
			$data_return['mode'] = "edit";
			$data_return['f_mode'] = $fluid_mode->mode;

			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_category_create_and_edit(\"" . base64_encode(json_encode($data_return)) . "\");' ><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save Changes</button></div>";
		}
		else { // Creation mode.
			// Checking to see if any manufacturers or categories exist if trying to create a child while no parents exist.
			if(!isset($_REQUEST['parent'])) {
				$fluid->php_db_query("SELECT " . $fluid_mode->id . " FROM ". $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id IS NOT NULL ORDER BY " . $fluid_mode->id . " ASC");
				if(isset($fluid->db_array)) {
					if(count($fluid->db_array) == 0)
						$fluid->db_error = "ERROR: There are no parent " . strtolower($fluid_mode->breadcrumb) . ". Please create a parent " . $fluid_mode->mode_name_real . " first before trying to add a child " . $fluid_mode->mode_name_real . ".";
				}
				else
					$fluid->db_error = "ERROR: There are no parent " . strtolower($fluid_mode->breadcrumb) . ". Please create a parent " . $fluid_mode->mode_name_real . " first before trying to add a child " . $fluid_mode->mode_name_real . ".";

				if(count($fluid->db_error) > 0) {
					$fluid->php_db_commit();
					return json_encode(array("js_execute_array" => 0, "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
					exit(0);
				}
			}

			$editor = FALSE;
			$modal_title = $fluid_mode->mode_name_real_cap;

			if(isset($_REQUEST['parent']))
				$modal_title .= " Parent";

			$modal_title .= " Creator";
			$data_return['mode'] = "add";
			$data_return['f_mode'] = $fluid_mode->mode;

			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-primary' onClick='js_category_create_and_edit(\"" . base64_encode(json_encode($data_return)) . "\");' ><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Add";

			if(isset($_REQUEST['parent']))
				$modal_footer_confirm_button_html .= " Parent";

			$modal_footer_confirm_button_html .= " Category</button></div>";
		}

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>" . $modal_title . "</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='categorycreatetabs'>
						<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#categorycreateinformation' data-target='#categorycreateinformation' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Information</a></li>";


					if(!isset($_REQUEST['parent']))
						$modal .= "<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#categorycreatefilters' data-target='#categorycreatefilters' data-toggle='tab'><span class='glyphicon glyphicon-list'></span> Filters</a></li>";

						$modal .= "<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#categorycreateimages' data-target='#categorycreateimages' data-toggle='tab'><span class='glyphicon glyphicon-picture'></span> Images</a></li>";

					if(!isset($_REQUEST['parent']) && $fluid_mode->mode != "manufacturers")
						$modal .= "<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#categoryformula' data-target='#categoryformula' data-toggle='tab'><span class='glyphicon glyphicon-equalizer'></span> Formula</a></li>";

					$modal .= "</ul>


				<div id='category-create-innerhtml' class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
					<div id='categorycreateevents' class='tab-content'>
						<div id='categorycreateinformation' class='tab-pane fade in active'>
							<div id='category-create-information-div' style='margin-left:10px; margin-right: 10px;'></div>
						</div>";

					if(!isset($_REQUEST['parent']))	{
						$modal .= "<div id='categorycreatefilters' class='tab-pane fade in'>
							<div id='category-create-filters-div' style='margin-left:10px; margin-right: 10px;'></div>
						</div>";
					}

						$modal .= "<div id='categorycreateimages' class='tab-pane fade in'>
							<div id='category-create-image-div' style='margin-right: 10px; margin-left:10px; margin-right: 10px;'></div>
						</div>";
					if(!isset($_REQUEST['parent']) && $fluid_mode->mode != "manufacturers")	{
						$modal .= "<div id='categoryformula' class='tab-pane fade in'>
							<div id='category-create-formula-div' style='margin-left:10px; margin-right: 10px;'></div>
						</div>";
					}

					$modal .= "</div>

				</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal' onClick='js_image_dropzone_destroy();'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>" . $modal_footer_confirm_button_html . "
			  </div>

			</div>
		  </div>";

	$output = "<div style='margin-top:15px;'>";

		// Category id
		if($editor) {
			$output .= "<input id='category-id' type='hidden' value='" . $data[$fluid_mode->id] . "'>";
		}

		// Status
		$output .= "<div class=\"input-group\">";
		$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";

			$output .= "<select id='category-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\" onchange='FluidVariables.v_category.c_status =(this.options[this.selectedIndex].value);'>";

				if($editor == TRUE) {
					if($data[$fluid_mode->enable] == 1)
						$selected = "selected";
					else
						$selected = "";
				}
				else
					$selected = "selected";

				$output .= "<option " . $selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</span>\"";
				$output .= "><span class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> Enabled</option>";

				if($editor == TRUE) {
					if($data[$fluid_mode->enable] == 0)
						$selected = " selected";
					else
						$selected = "";
				}
				else
					$selected = "";

				$output .= "<option " . $selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-ban-circle' aria-hidden='true'></span> Disabled</span>\">";

				$output .= "<span class='glyphicon glyphicon-ban-circle' aria-hidden='true'></span> Disabled</option>";
			$output .= "</select>";
		$output .= "</div>";

		// Parent category selector.
		if(!isset($_REQUEST['parent'])) {
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; width:120px !important;'>Parent " . $fluid_mode->mode_name_real . "</div></span>";
				$output .= "<select id='parent_id' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-live-search=\"true\" data-size=\"10\" data-width=\"50%\" onchange='FluidVariables.v_category.c_parent_id =(this.options[this.selectedIndex].value);'>";

			$fluid->php_db_query("SELECT * FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id IS NULL");
			$i = 0;
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					$p_images = $fluid->php_process_images($value[$fluid_mode->images]);
					$f_img_name = str_replace(" ", "_", $value[$fluid_mode->name] . "_" . $value[$fluid_mode->id]);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "20", "20", $f_img_name);

					$output .= "<option value='" . $value[$fluid_mode->id] . "'";
					$output .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "\"";

					if($editor) {
						if($data[$fluid_mode->X . '_parent_id'] == $value[$fluid_mode->id])
							$output .= " selected";
					}
					else if(!$editor) {
						if($i == 0)
							$output .= " selected";
					}

					$output .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "</option>";

					$i++;
				}
			}
				$output .= "</select>";
			$output .= "</div>";
		}

		// Category name.
		$output .= "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>" . $fluid_mode->mode_name_real_cap . " name</div></span>
			  <input type=\"text\" class=\"form-control\" placeholder=\"" . $fluid_mode->mode_name_real_cap . " name\" aria-describedby=\"basic-addon1\" id='category-name'";
				if($editor)
					$output .= " value=\"" . htmlspecialchars($data[$fluid_mode->name]) . "\"";
			  $output .= ">
			</div>";

		// Google category id
		$output .= "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Google Cat ID #</div></span>
			  <input type=\"text\" class=\"form-control\" placeholder=\"Google Product Taxonomy #. Example: 1380\" aria-describedby=\"basic-addon1\" id='category-google'";
				if($editor)
					$output .= " value=\"" . $data[$fluid_mode->google_cat_id] . "\"";
			  $output .= ">
			</div>";

		// Search weight score.
		$output .= "<div class=\"input-group\" style='padding-top:5px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Search weight</div></span>
			  <input type=\"text\" class=\"form-control\" placeholder=\"Search Weight\" aria-describedby=\"basic-addon1\" id='category-weight'";
				if($editor)
					$output .= " value=\"" . $data[$fluid_mode->weight] . "\"";
			  $output .= ">
			</div>";

			// Keywords
			$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
			  <div class=\"panel-heading\">
				<div style='display:inline-block;'><h5 style='font-weight:bold;'>Keywords</h5></div>
				<div style='float:right; display:inline-block;'>
					<button id='keywords-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='document.getElementById(\"category-keywords\").value=document.getElementById(\"category-keywords-textarea\").value; document.getElementById(\"category-keywords\").style.display=\"block\"; document.getElementById(\"category-keywords-textarea\").style.display=\"none\"; $(\"#category-keywords\").focus(); document.getElementById(\"keywords-button-save\").style.display=\"inline-block\"; document.getElementById(\"keywords-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
					<button id='keywords-button-save' class='btn btn-primary' style='display:none;' onclick='document.getElementById(\"category-keywords-textarea\").value=document.getElementById(\"category-keywords\").value; document.getElementById(\"category-keywords\").style.display=\"none\"; document.getElementById(\"category-keywords-textarea\").style.display=\"block\"; document.getElementById(\"keywords-button-edit\").style.display=\"inline-block\"; document.getElementById(\"keywords-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
				</div>
			 </div>
				<div class=\"panel-body\" style='border: 0px; padding: 0px;'>";
					$output .= "<textarea class='form-control' id='category-keywords-textarea' rows='8' style='color: #555555; cursor:not-allowed; padding: 6px 12px; width:100%; resize: none; border: 0px; border-radius: 0px; -webkit-box-shadow: none; box-shadow: none;' disabled>";
					if($editor)
						$output .= $data[$fluid_mode->keywords];
					$output .= "</textarea>";

					$output .= "<textarea class='form-control' rows='8' id='category-keywords' style='display:none; resize: vertical; border: none;'></textarea>";
				$output .= "</div>
			</div>";

			// SEO.
			$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
			  <div class=\"panel-heading\">
				<div style='display:inline-block;'><h5 style='font-weight:bold;'>SEO</h5></div>
				<div style='float:right; display:inline-block;'>
					<button id='seo-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='document.getElementById(\"category-seo\").value=document.getElementById(\"category-seo-textarea\").value; document.getElementById(\"category-seo\").style.display=\"block\"; document.getElementById(\"category-seo-textarea\").style.display=\"none\"; $(\"#category-seo\").focus(); document.getElementById(\"seo-button-save\").style.display=\"inline-block\"; document.getElementById(\"seo-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
					<button id='seo-button-save' class='btn btn-primary' style='display:none;' onclick='document.getElementById(\"category-seo-textarea\").value=document.getElementById(\"category-seo\").value; document.getElementById(\"category-seo\").style.display=\"none\"; document.getElementById(\"category-seo-textarea\").style.display=\"block\"; document.getElementById(\"seo-button-edit\").style.display=\"inline-block\"; document.getElementById(\"seo-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
				</div>
			 </div>
				<div class=\"panel-body\" style='border: 0px; padding: 0px;'>";
					$output .= "<textarea class='form-control' id='category-seo-textarea' rows='8' style='color: #555555; cursor:not-allowed; padding: 6px 12px; width:100%; resize: none; border: 0px; border-radius: 0px; -webkit-box-shadow: none; box-shadow: none;' disabled>";
					if($editor)
						$output .= $data[$fluid_mode->seo];
					$output .= "</textarea>";

					$output .= "<textarea class='form-control' rows='8' id='category-seo' style='display:none; resize: vertical; border: none;'></textarea>";
				$output .= "</div>
			</div>";

		$output .= "</div>"; // well end

		$formula_html = NULL;

		if(!isset($_REQUEST['parent']) && $fluid_mode->mode != "manufacturers") {
			$formula_html = "<div style='margin-top:15px;'>";

				$formula_html .= "<div class='alert alert-danger' role='alert'>";
					$formula_html .= "<div style='font-weight: 600;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> WARNING: For advanced users only. Use at your own risk!</div>";
				$formula_html .= "</div>";

				// --> Status
				$formula_html .= "<div class=\"input-group\">";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";

					$formula_html .= "<select id='formula-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

						if($data[$fluid_mode->formula_status] == 1)
							$f_selected = " selected";
						else
							$f_selected = NULL;

						$formula_html .= "<option " . $f_selected . " value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
						$formula_html .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Enabled</option>";

						if($data[$fluid_mode->formula_status] == 0)
							$f_selected = " selected";
						else
							$f_selected = NULL;

						$formula_html .= "<option " . $f_selected . " value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
						$formula_html .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Disabled</option>";

					$formula_html .= "</select>";
				$formula_html .= "</div>";

				// --> Operation
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>";

				// --> Formula
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px; width: 100%;'>
					  <span class=\"input-group-addon\"><div style='width:120px !important;'>Formula</div></span>
					  <input type=\"text\" class=\"form-control\" placeholder=\"ex: [QTY] = 10; [TOTAL_PRICE] * 0.10 (Gives you a 10% discount)\" aria-describedby=\"basic-addon1\" id='formula-math' value='" . $data[$fluid_mode->formula_math] . "'>
					</div>";

				// Discount price start date picker.
				/*
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>
					  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Formula Start</div></span>
					  <div class='input-group date' id='datetimepicker-formula-start'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='formula-discount-price-start-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
					</div>";

				// Discount price end date picker.
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>
					  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Formula End</div></span>
					  <div class='input-group date' id='datetimepicker-formula-end'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='formula-discount-price-end-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
					</div>";
				*/

				// --> Item list.
				/*
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Item List</div></span>";

					$formula_html .= "<div id='f-formula-item-list-div' style='display: inline;'>" . FORMULA_HTML_ITEM_SELECT_BLANK . "</div>";

					$formula_html .= "<div style='display: inline-block; padding-left: 3px;'>
						<div class=\"btn-group\">
						  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
							<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
						  </button>
						  <ul class=\"dropdown-menu dropdown-menu-right\">
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"items\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"categories\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"manufacturers\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
							<li role=\"separator\" class=\"divider\"></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"f-formula-item-list-div\").innerHTML = Base64.decode(\"" . base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
						  </ul>
						</div>
					</div>";
				$formula_html .= "</div>";
				$formula_html .= "<div style='padding-top: 10px; padding-left: 3px; padding-bottom: 15px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Add items to the list which may or may not trigger the OPERATION.</div>";
				*/
				/*
				// --> Item list faux.
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Item List Faux</div></span>";

					$formula_html .= "<div id='f-formula-item-list-div-faux' style='display: inline;'>" . FORMULA_HTML_ITEM_SELECT_BLANK_FAUX . "</div>";

					$formula_html .= "<div style='display: inline-block; padding-left: 3px;'>
						<div class=\"btn-group\">
						  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
							<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
						  </button>
						  <ul class=\"dropdown-menu dropdown-menu-right\">
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"items\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"categories\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"manufacturers\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
							<li role=\"separator\" class=\"divider\"></li>
							<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"f-formula-item-list-div-faux\").innerHTML = Base64.decode(\"" . base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK_FAUX) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
						  </ul>
						</div>
					</div>";
				$formula_html .= "</div>";
				$formula_html .= "<div style='padding-top: 10px; padding-left: 3px; padding-bottom: 15px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Add items to the faux list which may or may not trigger the OPERATION.</div>";

				// --> Flip formula 8 items.
				$formula_html .= "<div class=\"input-group\" style='padding-top: 10px;'>";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 90%;'>Flip OPTION 8 items</div></span>";

					$formula_html .= "<select id='formula-flip' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

						$formula_html .= "<option selected value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-minus' aria-hidden='true'></span> No</span>\"";
						$formula_html .= "><span class='glyphicon glyphicon-minus' aria-hidden='true'></span> No</option>";

						$formula_html .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-random' aria-hidden='true'></span> Yes</span>\">";
						$formula_html .= "<span class='glyphicon glyphicon-random' aria-hidden='true'></span> Yes</option>";

					$formula_html .= "</select>";
				$formula_html .= "</div><div style='padding-top: 5px; padding-bottom: 10px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> If yes, then the bundle order is flipped and the main item is shown first in the bundle. <b>This only affects Option #8.</b></div>";

				// --> Discount apply to? Cart or item?
				$formula_html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Application</div></span>";

					$formula_html .= "<select id='formula-application' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

						$formula_html .= "<option value='" . FORMULA_ITEM . "'>Apply to item</option>";
						$formula_html .= "<option value='" . FORMULA_CART . "' disabled>Apply to cart</option>";

					$formula_html .= "</select>";
				$formula_html .= "</div><div style='padding-top: 5px; padding-bottom: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Formula result either applies to the item or the cart total.</div>";

				// --> Formula message enable
				$formula_html .= "<div class=\"input-group\" style='padding-top: 10px;'>";
				$formula_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Message enabled</div></span>";

					$formula_html .= "<select id='formula-message-display' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

						$formula_html .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
						$formula_html .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Enabled</option>";

						$formula_html .= "<option selected value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
						$formula_html .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Disabled</option>";

					$formula_html .= "</select>";
				$formula_html .= "</div><div style='padding-bottom: 5px; padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Display the formula message on the item page and listing?</div>";

				// --> Formula message
				$formula_html .= "<div class=\"input-group\" style='padding-top:10px;'>
					  <span class=\"input-group-addon\"><div style='width:120px !important;'>Formula message</div></span>
					  <input type=\"text\" class=\"form-control\" placeholder=\"Ex: $100 off this product if...?\" aria-describedby=\"basic-addon1\" id='formula-message'>
					</div>";
				*/
				$formula_html .= "<div class='well' style='margin-top: 20px; padding-top: 8px;'>";
					$formula_html .= "<div style='font-weight: 600;'>Formula variable options:</div>";
					$formula_html .= "<div style='padding-top: 5px;'>[QTY] [TOTAL_PRICE] </div>";
					$formula_html .= "<div style='padding-top: 5px;'>Example: [QTY] = 10; [TOTAL_PRICE] * 0.10</div>";
					$formula_html .= "<div style='padding-top: 5px;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Note: The above formula will create a 10% discount coupon off the total price of the items from this category. There has to be least 10 items from this category in the cart.</div>";
				$formula_html .= "</div>";

			$formula_html .= "</div>";
		}

		$filters_html = NULL;
		if(!isset($_REQUEST['parent'])) {
			$filters_html = "<div style='margin-top:15px;'>";
				$filters_html .= "<div class='well'>";
					$filters_html .= "<div class=\"input-group\" style='padding-top:5px;'>
					  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:150px !important;'>Filter category name</div></span>
					  <input type=\"text\" class=\"form-control\" placeholder=\"filter category name\" aria-describedby=\"basic-addon1\" id='category-filter-name'>
					</div>";
					$filters_html .= "<div class=\"input-group\" style='padding-top:15px;'><button type='button' class='btn btn-success' onClick='js_category_create_filter(\"" . $c_id . "\", \"" . $fluid_mode->mode . "\");' >Create filter category</button></div>";
				$filters_html .= "</div>"; // well end

				$filters_html .= "<div class='well'>";

				$filters_html .= "<div class=\"input-group\">";
					$filters_html .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Filter category</div></span>";

						$filters_html .= "<div class='input-group' style='display:inline;' id='category-filter-select-div'>";
						if(!isset($data[$fluid_mode->filters]))
							$filters_html .= php_html_categories_filters_select(NULL)['innerHTML'];
						else
							$filters_html .= php_html_categories_filters_select($data[$fluid_mode->filters])['innerHTML'];
						$filters_html .= "</div>";
					$filters_html .= "</div>";

					$filters_html .= "<div class=\"input-group\" style='padding-top:5px;'>
					  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:100px !important;'>Filter keyword</div></span>
					  <input type=\"text\" class=\"form-control\" placeholder=\"filter keyword\" aria-describedby=\"basic-addon1\" id='category-filter-keyword'>
					</div>";
					$filters_html .= "<div class=\"input-group\" style='padding-top:15px;'><button type='button' class='btn btn-success' onClick='js_category_create_sub_filter();' >Create filter</button></div>";
				$filters_html .= "</div>"; // well end

				$filter_block_list = "none";
				$filter_block_none = "block";

				if($editor == TRUE) {
					$filters_obj = json_decode(base64_decode($data[$fluid_mode->filters]));

					if(isset($filters_obj)) {
						if(count(get_object_vars(json_decode(base64_decode($data[$fluid_mode->filters])))) > 0) {
							$filter_block_list = "block";
							$filter_block_none = "none";
						}
					}
				}

				$filters_html .= "<div id='filters-cat-new-div' class='list-group' style='display:" . $filter_block_list . ";'>";


				// Filter list.
				if($editor == TRUE) {
					$filters_html .= php_html_categories_filters($data[$fluid_mode->filters])['innerHTML'];
				}

				$filters_html .= "</div>"; // fluid-filter-listing


				$filters_html .= "<div id='filters-cat-none-div' class='table-responsive panel panel-default' style='display:" . $filter_block_none . ";'>";
					$filters_html .= "<table class='table table-hover' id='filters-cat-none'>";
						$filters_html .= "<tbody>";
						$filters_html .= "<tr id='cat-new-tr-hide'><td>No filters created yet.</td></tr>";
						$filters_html .= "</tbody>";
					$filters_html .= "</table>";
				$filters_html .= "</div>"; // table-responsive

			$filters_html .= "</div>"; // filters_html
		}

		if(isset($_REQUEST['parent']))
			$parent_flag = $_REQUEST['parent'];
		else
			$parent_flag = NULL;

		// If in edit mode, scan for existing images so we can feed back to the browser to fill into the image uploader drop zone.
		if($editor) {
			$tmp_array = Array();

			if(isset($data[$fluid_mode->images])) {
				$f_img_tmp_data = json_decode(base64_decode($data[$fluid_mode->images]));
				if(isset($f_img_tmp_data)) {
					//foreach(json_decode(base64_decode($data[$fluid_mode->images])) as $key => $img) {
					foreach($f_img_tmp_data as $key => $img) {
						$obj['name'] = $img->file->image;
						$obj['oldname'] = $img->file->name;
						$obj['size'] = $img->file->size;
						$obj['rand'] = $img->file->rand;

						// The editor image dropzone delete's images from the full path if discarding the edit change. So we need to make a copy of the image into the temp folder and adjust the full path to it to prevent the original from getting deleted if the user discards there changes.
						set_error_handler(function($errno, $errstr, $errfile, $errline) {
							throw new Exception($errstr . " on line " . $errline . " in file " . $errfile);
						});

						if(!file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']))
							mkdir(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']);

						try {
							copy(FOLDER_IMAGES . $img->file->image, FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image);
							$obj['fullpath'] =  FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image;
							restore_error_handler();

							// Adjust the full path in the $img object as it gets saved into the xhr response, which is used for temporary image deletion.
							$img_tmp = $img;
							$img_tmp->file->tempfullpath = $img_tmp->file->fullpath;
							$img_tmp->file->fullpath = $obj['fullpath'];

							$obj['xhr']['response'] = base64_encode(json_encode($img_tmp));

							$tmp_array[] = $obj;
						}
						catch (Exception $err) {
							restore_error_handler();
						}
					}
				}
			}
			$image_array = Array("imgzone" => base64_encode(json_encode($tmp_array)));

			$execute_functions[0]['function'] = "js_modal_category_create_and_edit";
			$execute_functions[0]['data'] = base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "info_html" => base64_encode($output), "formula_html" => base64_encode($formula_html), "mode" => $fluid_mode->mode, "filters_html" => base64_encode($filters_html), "image_html" => base64_encode(HTML_IMAGE_DROPZONE), "image_data" => base64_encode(json_encode($image_array)), "parent" => base64_encode($parent_flag))));
		}
		else {
			$execute_functions[0]['function'] = "js_modal_category_create_and_edit";
			$execute_functions[0]['data'] = base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "info_html" => base64_encode($output),  "formula_html" => base64_encode($formula_html), "mode" => $fluid_mode->mode, "filters_html" => base64_encode($filters_html), "image_html" => base64_encode(HTML_IMAGE_DROPZONE), "parent" => base64_encode($parent_flag))));
		}

		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));
		$execute_functions[2]['function'] = "js_category_filter_sortable";
		$execute_functions[2]['data'] = base64_encode(json_encode(""));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		restore_error_handler();
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_category_products() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();
		$data = json_decode(base64_decode($_REQUEST['data']));
		$fluid_mode = new Fluid_Mode($data->mode);

		if($data->mode == "manufacturers")
			$fluid->php_db_query("SELECT p.*, c.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE p.p_mfgid = '" . $fluid->php_escape_string($data->{$fluid_mode->X_id}) . "' ORDER BY p_mfgid ASC, p_sortorder_mfg ASC");
		else
			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id WHERE p.p_catid = '" . $fluid->php_escape_string($data->{$fluid_mode->X_id}) . "' ORDER BY p_catid ASC, p_sortorder ASC");

		/*
		$tmp_array = Array();
		if(isset($fluid->db_array))
			foreach($fluid->db_array as $value)
				$tmp_array[] = $value;
		*/

		if(isset($_REQUEST['selection']))
			$selection_data = $_REQUEST['selection'];
		else
			$selection_data = NULL;

		if(isset($fluid->db_array))
			$return = php_html_items($fluid->db_array, $selection_data, $data->mode);
		else
			$return = php_html_items(NULL, $selection_data, $data->mode);


		$execute_functions[0]['function'] = "js_category_stack_open";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("div" => base64_encode("category-div-" . $data->{$fluid_mode->X_id}), "innerHTML" => base64_encode($return), "cid" => base64_encode($data->{$fluid_mode->X_id}))));

		$sort_return['categories'][$_REQUEST['data']]['div'] = base64_encode("#cat-" . $data->{$fluid_mode->X_id});
		$execute_functions[1]['function'] = "js_sortable_products";
		$execute_functions[1]['data'] = base64_encode(json_encode($sort_return));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_items($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		if(isset($f_data->f_page_num)) {
			$f_page = $f_data->f_page_num;
			$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
		}
		else {
			$f_page = 0;
			$f_start = 0;
		}

		$f_tmp_count = 0;
		$f_stock_cost = 0;
		$f_stock_count = 0;

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else if(isset($f_data->mode))
			$mode = $f_data->mode;
		else
			$mode = "items";

		$fluid->php_db_begin();

		if(isset($f_data->query_count)) {
			$f_query_count = $f_data->query_count;
		}
		else {
			$f_query_filter = "WHERE p.p_stock > 0";

			if($_SESSION['f_admin_item_filters_enabled'] == TRUE && isset($_SESSION['f_admin_item_filters_query'])) {
				$f_query_filter = "WHERE " . $_SESSION['f_admin_item_filters_query'];

				$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p " . $f_query_filter;
			}
			else {
				$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p";
			}
		}

		$fluid->php_db_query($f_query_count);

		if(isset($fluid->db_array)) {
			$f_tmp_count = $fluid->db_array[0]['tmp_c_product_count'];
			$f_stock_cost = $fluid->db_array[0]['tmp_stock_value'];
			$f_stock_count = $fluid->db_array[0]['product_stock'];
		}

		if(isset($f_data->query)) {
			$f_query = $f_data->query;
		}
		else {
			$f_query_filter_main = NULL;
			if($_SESSION['f_admin_item_filters_enabled'] == TRUE && isset($_SESSION['f_admin_item_filters_query'])) {
				$f_query_filter_main = "WHERE " . $_SESSION['f_admin_item_filters_query'] . " ";
			}

			$f_query = "SELECT p.*, c.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $f_query_filter_main . "ORDER BY p_id ASC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;
		}

		$fluid->php_db_query($f_query);

		$fluid->php_db_commit();

		$tmp_array = Array();
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $value)
				$tmp_array[] = $value;
		}

		$fluid_mode = new Fluid_Mode($mode);

		$tmp_selection_array = NULL;
		if(isset($f_data->f_selection)) {
			$selection_data = base64_encode(json_encode($f_data->f_selection->p_selection));

			if(isset($f_data->f_selection->p_selection))
				foreach($f_data->f_selection->p_selection as $product) {
					$tmp_selection_array[$product->p_id] = $product->p_id;
				}
		}
		else
			$selection_data = NULL;

		if(isset($f_data->pagination_function))
			$f_pagination_function = $f_data->pagination_function;
		else
			$f_pagination_function = "js_fluid_load_items";

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $fluid_mode->mode . "'><li>";
				if(isset($tmp_selection_array))
					$return .= "<div id='category-a-" . $fluid_mode->mode . "' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
				else
					$return .= "<div id='category-a-" . $fluid_mode->mode . "' style='height: 40px;' class='list-group-item'>";

					$return .= "<span id='category-badge-count-cost-" . $fluid_mode->mode . "' class='badge badge-hide'>Stock value: " . HTML_CURRENCY . number_format($f_stock_cost, 2, '.', ',') . "</span>";
					$return .= "<span id='category-badge-count-stock-" . $fluid_mode->mode . "' class='badge badge-hide'>Stock: " . $f_stock_count . "</span>";

					$return .= "<span id='category-badge-count-" . $fluid_mode->mode . "' class='badge'>Items: " . $f_tmp_count . "</span>";

					if(isset($tmp_selection_array))
						$return .= "<span id='category-badge-select-count-" . $fluid_mode->mode . "' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$return .= "<span id='category-badge-select-count-" . $fluid_mode->mode . "' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-" . $fluid_mode->mode . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-" . $fluid_mode->mode . "' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Items</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode) . "</div>";

			$return .= "<div id='category-div-" . $fluid_mode->mode . "'>";
			$return .= php_html_items($tmp_array, $selection_data, $mode);
			$return .= "</div>";

		$return .= "</div>";

		$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode) . "</div>";

		if(isset($f_data->f_refresh)) {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("content-div"), "innerHTML" => base64_encode($return))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else {
			$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
			$breadcrumbs .= "<li class='active'>Items</li>";

			// Follow up functions to execute on a server response back to the user.
			if(empty($f_data->f_keep_selection)) {
				$execute_functions[0]['function'] = "js_clear_fluid_selection";
				$execute_functions[0]['data'] = base64_encode(json_encode(""));
			}
			else {
				$execute_functions[0]['function'] = "js_update_action_menu";
				$execute_functions[0]['data'] = base64_encode(json_encode(""));
			}

			$execute_functions[1]['function'] = "js_html_style_show";
			$execute_functions[1]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input($mode)), "navbarright" => base64_encode(php_html_navbar_right($mode)), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// A powerful multi item editor.
function php_load_multi_item_editor() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		// Error checking and getting manufacturer and category data.
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
				$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";
		}
		else
			$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";

		$fluid->php_db_query("SELECT * FROM ". TABLE_CATEGORIES . " ORDER BY c_sortorder ASC");
		if(isset($fluid->db_array)) {
			if(count($fluid->db_array) > 0) {
				foreach($fluid->db_array as $key => $value) {
					if($value['c_parent_id'] == NULL) {
						$category_data[$value['c_id']]['parent'] = $value;
					}
					else {
						$category_data[$value['c_parent_id']]['childs'][] = $value;
					}
				}
			}
			else {
				$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";
			}
		}
		else {
			$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";
		}

		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$where_p_c_linking = "WHERE l_p_id IN (";
		$where_p_component_linking = "WHERE cp_master_id IN (";

		$i = 0;
		foreach($data as $product) {
			if($i != 0) {
				$where .= ", ";
				$where_p_c_linking .= ", ";
				$where_p_component_linking .= ", ";
			}

			$where .= $fluid->php_escape_string($product->p_id);
			$where_p_c_linking .= $fluid->php_escape_string($product->p_id);
			$where_p_component_linking .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";
		$where_p_c_linking .= ")";
		$where_p_component_linking .= ")";

		$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'><div id='f-header-multi-item-div' style='display: inline-block;'>Multiple item editor</div><div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

				<div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

					<div id='multi-item-editor-tabs-div'>
					</div>

					<div id='f-multi-scroll-div' class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div>
							<div id='multi-item-editor-div' style='margin-left:10px; margin-right: 10px;'>";

							$tabs_html = "
							<ul style='padding-left: 15px;' class='nav nav-tabs' id='productcreatetabs'>
								<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateinformation' data-target='#productcreateinformation' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Information</a></li>
								<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateimages' data-target='#productcreateimages' data-toggle='tab'><span class='glyphicon glyphicon-picture'></span> Images</a></li>
								<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcomponent' data-target='#productcomponent' data-toggle='tab'><span class='glyphicon glyphicon-compressed'></span> Component Builder</a></li>
								<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productlink' data-target='#productlink' data-toggle='tab'><span class='glyphicon glyphicon-link'></span> Product Linking</a></li>
								<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productmath' data-target='#productmath' data-toggle='tab'><span class='glyphicon glyphicon-equalizer'></span> Formula Links</a></li>
							</ul>";

							$item_tabs_html = "
							<div id='productcreateevents' class='tab-content'>
								<div id='productcreateinformation' class='tab-pane fade in active'>
									<div id='product-create-information-div'></div>
								</div>

								<div id='productcreateimages' class='tab-pane fade in'>
									<div id='product-create-image-div'></div>
								</div>

								<div id='productcomponent' class='tab-pane fade in'>
									<div id='product-component-div'></div>
								</div>

								<div id='productlink' class='tab-pane fade in'>
									<div id='product-link-div'></div>
								</div>

								<div id='productmath' class='tab-pane fade in'>
									<div id='product-math-div'></div>
								</div>
							</div>";

							$item_list_html = "<div class='list-group'>";

							// Build a item selection list for the confirmation modal.
							$selection_output_html = "<div id='multi-item-selection-list' class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
							//$selection_items = NULL;
							$item_list_object = NULL;
							if(isset($fluid->db_array)) {
								foreach($fluid->db_array as $value) {
									// Process the image.
									$p_images = $fluid->php_process_images($value['p_images']);
									$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
									$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

									$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

									$item_list_html .= "<button id='multi-item-button-" . $value['p_id']. "' data-id='" . $value['p_id'] . "' name='p_item' data-name='p_item' type='button' onClick='FluidVariables.v_multi_item_scroll = document.getElementById(\"f-multi-scroll-div\").scrollTop; js_multi_item_editor(\"" . $value['p_id'] . "\"); document.getElementById(\"f-multi-scroll-div\").scrollTop = 0;' class='list-group-item'><img id='multi-item-img-" . $value['p_id'] . "' src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;''></img> <div style='display: inline-block; margin-top: 10px;' id='multi-item-editor-" . $value['p_id'] . "'>" . $value['m_name'] . " " . $value['p_name'] . " <div><div style='display: inline-block; font-size: 10px; font-style: oblique; font-weight: 600;'>upc: " . $value['p_mfgcode'] . "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>code: " . $value['p_mfg_number'] . "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>cost: " . $value['p_cost'] . "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; font-weight: 600;'>price: " . $value['p_price'] . "</div><div style='display: inline-block; padding-left: 10px; font-size: 10px; font-style: oblique; color: red; font-weight: 600;'>price disc: " . $value['p_price_discount'] . "</div></div></div> <span id='multi-item-list-remove' class=\"list-group-item glyphicon glyphicon-remove\" aria-hidden=\"true\" style='border-radius: 4px; float:right !important;' onClick='js_multi_item_remove(\"" . $value['p_id'] . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"; this.style.backgroundColor=\"#FF4024\";' onmouseout='this.style.backgroundColor=\"transparent\";'></span></button>";

									//$selection_items .= $value['m_name'] . " " . $value['p_name'] . "<br>";
									$value['p_c_linking'] = NULL;
									$item_list_object[$value['p_id']] = $value;
								}
							}

							$selection_output_html .= "</div>";
							$selection_output_html .= "</div>";

							$item_list_html .= "</div>";

							$modal .= $item_list_html;
							$modal .= "</div>
						</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer' id='multi-item-modal-footer'>";

			  $confirm_message = base64_encode("<div class='alert alert-warning' role='alert'>Are you sure you want to make changes to the selected items?</div>" . $selection_output_html);
			  $confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" onClick='js_multi_item_save(\"" . $fluid_mode->mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

			  $footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal' onClick='js_delete_image_temp();'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>
				<div style='float:right;'><button id='multi-item-continue-button' disabled type='button' class='btn btn-success' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\")); js_multi_item_list_modal_confirm();' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>";

			  $footer_back_html = "<div style='float:left;'></button> <button type='button' onClick='js_multi_item_load_list();' class='btn btn-danger'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel changes</button></div> <div style='float:right;'><button id='multi-item-button-back' type='button' onClick='js_multi_item_update();' class='btn btn-success'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save changes</div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$item_editor = php_html_items_editor(NULL, $manufacturer_data, $category_data, FALSE, $fluid_mode->mode, TRUE);
		$f_formula_html = base64_encode(php_html_item_formula_links_editor()); // --> Load a blank state item formula links editor.
		$f_link_html = base64_encode(php_html_item_link_editor()); // --> Load a blank state item linking editor.
		$f_component_html = base64_encode(php_html_component_editor()); // Load a blank state of the component editor.

		$editor_data = NULL;
		if(count($fluid->db_error) < 1) {
			foreach($fluid->db_array as $value) {
				$editor_data[$value['p_id']]['p_id'] = $value['p_id'];
				$editor_data[$value['p_id']]['p_fullname'] = base64_encode($value['m_name'] . " " . $value['p_name']);
				$editor_data[$value['p_id']]['f_session_id'] = base64_encode($_SESSION['fluid_admin']);
				$editor_data[$value['p_id']]['image_array'] = base64_encode(json_encode(php_process_item_images_editor($value['p_images'])));
			}
		}

		// --> The product category linking data queries need to go after the $editor_data loop above.
		$fluid->php_db_query("SELECT * FROM " . TABLE_PRODUCT_CATEGORY_LINKING . " " . $where_p_c_linking);
		$f_pc_data = NULL;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $pc_link_data) {
				$f_pc_data[$pc_link_data['l_p_id']][$pc_link_data['l_c_id']] = $pc_link_data['l_c_id'];
			}
		}

		// Add linking data to the item list.
		if(isset($f_pc_data)) {
			foreach($f_pc_data as $f_key => $f_data_tmp) {
				if(isset($item_list_object[$f_key])) {
					$item_list_object[$f_key]['p_c_linking'] = json_encode($f_data_tmp);
				}
			}
		}


		// --> The product component data queries need to go after the $editor_data loop above.
		$fluid->php_db_query("SELECT * FROM " . TABLE_PRODUCT_COMPONENT . " " . $where_p_component_linking);
		$f_pc_component_data = NULL;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $pcomponent_data) {
				$f_pc_component_data[$pcomponent_data['cp_master_id']][] = (object)Array("cp_id" => $pcomponent_data['cp_id'], "p_id" => $pcomponent_data['cp_p_id'], "p_quantity" => $pcomponent_data['cp_p_stock']);
			}
		}

		// Add component data to the item list.
		if(isset($f_pc_component_data)) {
			foreach($f_pc_component_data as $f_key => $f_data_tmp) {
				if(isset($item_list_object[$f_key])) {
					$f_component_tmp_data = (object)Array("f_item_editor" => TRUE, "f_selector" => (object)Array("v_selection" => (object)Array("p_selection" => $f_data_tmp)), "f_formula_list" => "component-list-select");

					$component_data_tmp = php_html_formula_links_items_builder($f_component_tmp_data);

					$item_list_object[$f_key]['p_component_html'] = $component_data_tmp;
					$item_list_object[$f_key]['p_component_data'] = json_encode($f_component_tmp_data);
				}
			}
		}

		// --> Re-encode the aray of data.
		$items_data = NULL;
		foreach($item_list_object as $f_key => $f_data_tmp) {

			if(empty($f_data_tmp['p_component_html'])) {
				$select_empty_html = "<select id='component-list-select' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"></select>";

				$f_data_tmp['p_component_html'] = base64_encode($select_empty_html);
			}

			if(empty($f_data['p_component_data'])) {
				$f_data_tmp['p_component_data'] = "";
			}

			$items_data[$f_key] = $fluid->php_encode_array($f_data_tmp);
		}

		$fluid->php_db_commit();

		$execute_functions[1]['function'] = "js_modal_multi_item_editor";
		$execute_functions[1]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal), "item_tabs_html" => base64_encode($item_tabs_html), "tabs_html" => base64_encode($tabs_html), "footer_save_html" => base64_encode($footer_save_html), "footer_back_html" => base64_encode($footer_back_html), "item_list_html" => base64_encode($item_list_html), "editor_html" => $item_editor['html'], "link_html" => $f_link_html, "item_data_array" => base64_encode(json_encode($items_data)), "data_array" => base64_encode(json_encode($editor_data)), "component_html" => $f_component_html, "formula_html" => $f_formula_html, "image_html" => base64_encode(HTML_IMAGE_DROPZONE))));

		$execute_functions[2]['function'] = "js_modal_show";
		$execute_functions[2]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Again, do we really need this here for multi item edit?

		return php_fluid_error($err);
	}
}

// --> Loads html data for a component editor.
function php_html_component_editor() {

	$html = "<div style='margin-top:15px;'>";

		$html .= "<div class='alert alert-info' role='alert'>";
			$html .= "<div style='font-weight: 600;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Component editor. When the component item flag is set to yes in the item editor, you add other items here in this component editor. These items are used to build the item you are currently editing. Stock levels of the items added to this component are used to calculate it's stock plus any stock on the main item is also included. Scanning the code for this item during regular stock operations will result in the subtracting and or adding stock numbers just the main item only. Components when used to make a item need to be scanned out when removed from inventory.</div>";
		$html .= "</div>";

		$html .= "<div id='component_html' style='margin-top:15px;'>";

		$data_html = "<div id='component-div' style='display: inline;'>";
		$select_empty_html = "<select id='component-list-select' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"></select>";

		$data_html .= $select_empty_html;
		$data_html .= "</div>"; // --> f-item-list-div-$i.

			// --> Add position: absolute to have this div button dropdown to overlay a modal. however you get some scroll bugs.
			$data_html .= "<div style='display: inline-block; padding-left: 3px;'>
				<div class=\"btn-group\">
				  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-container=\"#fluid-modal\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
					<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
				  </button>
				  <ul class=\"dropdown-menu dropdown-menu-right\" style='top: -410%;'>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"component-list-select\", \"component-div\", \"items\", \"1\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"fcomponent-list-select\", \"component-div\", \"categories\", \"1\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"component-list-select\", \"component-div\", \"manufacturers\", \"1\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
					<li role=\"separator\" class=\"divider\"></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"component-div\").innerHTML = Base64.decode(\"" . base64_encode($select_empty_html) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
				  </ul>
				</div>
			</div>";


		$html .= $data_html;

		$html .= "</div>"; // filters_html

	$html .= "</div>";

	return $html;
}

// --> Loads html data for a link editor. Creating accessories etc.
function php_html_item_link_editor() {

	$html = "<div style='margin-top:15px;'>";

		$html .= "<div class='alert alert-info' role='alert'>";
			$html .= "<div style='font-weight: 600;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Item linking editor. Create categories and add items to these categories. They will then show up on the item page under the created category heading as a item slider. This is good for linking accessories to items, or similar items together, etc.</div>";
		$html .= "</div>";

		$html .= "<div style='margin-top:15px;'>";
			$html .= "<div class='well'>";
				$html .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:150px !important;'>Category name</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Category name\" aria-describedby=\"basic-addon1\" id='category-filter-name'>
				</div>";
				$html .= "<div class=\"input-group\" style='padding-top:15px;'><button type='button' class='btn btn-success' onClick='js_product_create_category();' >Create category</button></div>";
			$html .= "</div>"; // well end

			$filter_block_list = "none";
			$filter_block_none = "block";

			$html .= "<div id='filters-cat-new-div' class='list-group' style='display:" . $filter_block_list . ";'>";


			$html .= "</div>"; // fluid-filter-listing


			$html .= "<div id='filters-cat-none-div' class='table-responsive panel panel-default' style='display:" . $filter_block_none . ";'>";
				$html .= "<table class='table table-hover' id='filters-cat-none'>";
					$html .= "<tbody>";
					$html .= "<tr id='cat-new-tr-hide'><td>No categories created yet.</td></tr>";
					$html .= "</tbody>";
				$html .= "</table>";
			$html .= "</div>"; // table-responsive

		$html .= "</div>"; // filters_html

	$html .= "</div>";

	return $html;
}

// --> Loads html data for a item formula link editor.
function php_html_item_formula_links_editor() {

	$html = "<div style='margin-top:15px;'>";

		$html .= "<div class='alert alert-danger' role='alert'>";
			$html .= "<div style='font-weight: 600;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> WARNING: For advanced users only. Use at your own risk!</div>";
		$html .= "</div>";

		// --> Status
		$html .= "<div class=\"input-group\">";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";

			$html .= "<select id='formula-status' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
				$html .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Enabled</option>";

				$html .= "<option selected value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
				$html .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Disabled</option>";

			$html .= "</select>";
		$html .= "</div>";

		// --> Operation
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>";
		$html .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Operation</div></span>";

			$html .= "<select id='formula-operation' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html .= "<option value='" . FORMULA_OPTION_1 . "'>Option #1: If at least one of the items are in the cart. Faux List ignored.</option>";
				$html .= "<option value='" . FORMULA_OPTION_2 . "'>Option #2: Enable Faux List. Item List Ignored for Formula operation.</option>";
				$html .= "<option value='" . FORMULA_OPTION_3 . "'>Option #3: Enable Faux List. Item List enabled for Formula operation.</option>";
				$html .= "<option value='" . FORMULA_OPTION_4 . "' disabled>Option #4: If none of the selected items are in the cart.</option>";
				$html .= "<option value='" . FORMULA_OPTION_5 . "' disabled>Option #5: If any item is in the cart (Item List ignored).</option>";
				$html .= "<option value='" . FORMULA_OPTION_6 . "' disabled>Option #6: If no other item is in the cart (Item List ignored).</option>";
				$html .= "<option value='" . FORMULA_OPTION_7 . "'>Option #7: Do nothing, ignore formula and item list. Messages will display if enabled.</option>";
				$html .= "<option value='" . FORMULA_OPTION_8 . "'>Option #8: If at least one of the items are in the cart. Faux List ignored. Applies formula as a promotion coupon item, creates bundle on site.</option>";
				$html .= "<option value='" . FORMULA_OPTION_9 . "'>Option #9: If at least one of the items are in the cart. Faux List ignored. Applies formula as a promotion coupon item. Item discount price ignored.</option>";
				$html .= "<option value='" . FORMULA_OPTION_10 . "'>Option #10: If at least one of the items are in the cart. Faux List ignored. Applies formula as a promotion coupon item. Item discount price ignored. Original item is hidden from the site.</option>";

			$html .= "</select>";
		$html .= "</div>";

		$html .= "<div class='well' style='margin-top: 20px; padding-top: 8px;'>";
			//$html .= "<div style='font-weight: 600;'>Option descriptions:</div>";
			$html .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600;'>Option #1:</div> When this (MASTER_ITEM) is added to the cart any (ITEM_LIST_ITEMS) will trigger the (FORMULA) when added to the cart. When you have multiples of the (MASTER_ITEM) in the cart and only 1 of the (ITEM_LIST_ITEMS) in the cart, the (FORMULA) applies to only 1 of the (MASTER_ITEM) while the extra (MASTER_ITEM) will not be affected by the (FORMULA). <div style='display: inline-block; font-size: 80%'><div style='display: inline-block; font-weight: 600; padding-top: 5px;'>Note:</div> Any items in the (ITEM_LIST_ITEMS) which have Option 2 enabled and this (MASTER_ITEM) is in the (ITEM_LIST_ITEMS_FAUX), then this (MASTER_ITEM) will not have it's (FORMULA) applied if they are in the cart at the same time as the (MASTER_ITEM), unless the (ITEM_LIST_ITEMS_FAUX) has a different (ITEM_LIST_ITEMS) compared to this (MASTER_ITEM), and or if the (ITEM_LIST_ITEMS) has more quantities of it's items in the cart.</div></div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #2:</div> When this (MASTER_ITEM) is in the cart, only one item in this (FAUX_ITEM_LIST_ITEMS) will have its (FORMULA) applied if it is in the cart with this (MASTER_ITEM), unless the qty's increase on this (MASTER_ITEM). The (MASTER_ITEM) formula will apply if it is not empty, and it's message will show if enabled. (ITEM_LIST_ITEMS) will be ignored. </div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #3:</div> When this (MASTER_ITEM) is in the cart, only one item in this (FAUX_ITEM_LIST_ITEMS) will have its (FORMULA) applied if it is in the cart with this (MASTER_ITEM), unless the qty's increase on this (MASTER_ITEM). The (MASTER_ITEM) formula will apply if it is not empty, and it's message will show if enabled. (ITEM_LIST_ITEMS) will take into account for enabling the (FORMULA) on this (MASTER_ITEM). </div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #7:</div> The (FORMULA), (ITEM_LIST_ITEMS) AND (ITEM_LIST_ITEMS_FAUX) are ignored. The (FORMULA_MESSAGE) will be displayed if both the (STATUS) and (MESSAGE_ENABLED) is enabled.</div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #8:</div> The (FORMULA_MESSAGE) & (ITEM_LIST_ITEMS_FAUX) are ignored. This creates a virtual bundle item on the site and adds a coupon to the cart based on the formula.</div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #9:</div> The (FORMULA_MESSAGE) & (ITEM_LIST_ITEMS_FAUX) are ignored. This creates a virtual bundle item on the site and adds a coupon to the cart based on the formula. Any discount price on the item is ignored and is displayed in the cart at it's regular price. The coupon is based 1 coupon per bundle. Multiple items need multiple matching items.</div>";

			$html .= "<div style='border-top: 1px solid #8a8a8a; margin-top: 10px; padding-top: 10px;'><div style='display: inline-block; font-weight: 600;'>Option #10:</div> The (FORMULA_MESSAGE) & (ITEM_LIST_ITEMS_FAUX) are ignored. No virtual bundle is listed on the site. This adds a coupon to the cart based on the formula if one of the any items from the (ITEM_LIST) and this item are both in the cart. Any discount price on the item is ignored and is displayed in the cart at it's regular price. The coupon is based 1 coupon per bundle. Multiple items need multiple matching items. Works like OPTION #9 but without showing a bundle on the site. The items have to be added to the cart manually.</div>";

			$html .= "<div style='padding-top: 20px; font-weight: 600; font-style: italic; font-size: 80%;'>Legend:</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(MASTER_ITEM) --> The item you are editing, which the formula will apply itself to.</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(ITEM_LIST_ITEMS) --> The items that are in the item list. They may or may not trigger the formula onto the (MASTER_ITEM) depending on which operation is selected.</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(ITEM_LIST_ITEMS_FAUX) --> The items that are in the Item List Faux. They may or may not trigger the formula onto the (MASTER_ITEM) depending on which operation is selected.</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(FORMULA) --> The formula in the formula input field that is applied to the (MASTER_ITEM).</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(STATUS) --> The status option, which enables or disabled the entire formula links.</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(MESSAGE_ENABLED) --> The message enabled option, which displays or does not display the (FORMULA_MESSAGE).</div>";
			$html .= "<div style='padding-top: 5px; font-style: italic; font-size: 80%;'>(FORMULA_MESSAGE) --> The message which may or not be displayed along with the (MASTER ITEM) if enabled.</div>";
		$html .= "</div>";

		// --> Formula
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>
			  <span class=\"input-group-addon\"><div style='width:120px !important;'>Formula</div></span>
			  <input type=\"text\" class=\"form-control\" placeholder=\"ex: [PRICE] - 100\" aria-describedby=\"basic-addon1\" id='formula-math'>
			</div>";

		// Discount price start date picker.
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Formula Start</div></span>
			  <div class='input-group date' id='datetimepicker-formula-start'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='formula-discount-price-start-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
			</div>";

		// Discount price end date picker.
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>
			  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Formula End</div></span>
			  <div class='input-group date' id='datetimepicker-formula-end'><input type=\"text\" style='border-radius: 0px;' class=\"form-control\" placeholder=\"Leave blank for none.\" aria-describedby=\"basic-addon1\" id='formula-discount-price-end-date'><span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span></div>
			</div>";

		// --> Item list.
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Item List</div></span>";

			$html .= "<div id='f-formula-item-list-div' style='display: inline;'>" . FORMULA_HTML_ITEM_SELECT_BLANK . "</div>";

			$html .= "<div style='display: inline-block; padding-left: 3px;'>
				<div class=\"btn-group\">
				  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
					<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
				  </button>
				  <ul class=\"dropdown-menu dropdown-menu-right\">
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"items\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"categories\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list\", \"f-formula-item-list-div\", \"manufacturers\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
					<li role=\"separator\" class=\"divider\"></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"f-formula-item-list-div\").innerHTML = Base64.decode(\"" . base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
				  </ul>
				</div>
			</div>";
		$html .= "</div>";
		$html .= "<div style='padding-top: 10px; padding-left: 3px; padding-bottom: 15px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Add items to the list which may or may not trigger the OPERATION.</div>";

		// --> Item list faux.
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Item List Faux</div></span>";

			$html .= "<div id='f-formula-item-list-div-faux' style='display: inline;'>" . FORMULA_HTML_ITEM_SELECT_BLANK_FAUX . "</div>";

			$html .= "<div style='display: inline-block; padding-left: 3px;'>
				<div class=\"btn-group\">
				  <button type=\"button\" class=\"btn btn-success dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
					<span class='glyphicon glyphicon-edit' aria-hidden='true'></span> Edit Item List <span class=\"caret\"></span>
				  </button>
				  <ul class=\"dropdown-menu dropdown-menu-right\">
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"items\");'><span class=\"glyphicon glyphicon-list-alt\"></span> Item mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"categories\");'><span class=\"glyphicon glyphicon-th-large\"></span> Category mode</a></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_load_item_selector(\"js_fluid_formula_links_items_process\", \"formula-item-list-faux\", \"f-formula-item-list-div-faux\", \"manufacturers\");'><span class=\"glyphicon glyphicon-th-list\"></span> Manufacturer mode</a></li>
					<li role=\"separator\" class=\"divider\"></li>
					<li><a onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"f-formula-item-list-div-faux\").innerHTML = Base64.decode(\"" . base64_encode(FORMULA_HTML_ITEM_SELECT_BLANK_FAUX) . "\"); js_update_select_pickers();'><span class=\"glyphicon glyphicon-remove\"></span> Clear item list</a></li>
				  </ul>
				</div>
			</div>";
		$html .= "</div>";
		$html .= "<div style='padding-top: 10px; padding-left: 3px; padding-bottom: 15px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Add items to the faux list which may or may not trigger the OPERATION.</div>";

		// --> Flip formula 8 items.
		$html .= "<div class=\"input-group\" style='padding-top: 10px;'>";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important; font-size: 90%;'>Flip OPTION 8 items</div></span>";

			$html .= "<select id='formula-flip' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html .= "<option selected value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-minus' aria-hidden='true'></span> No</span>\"";
				$html .= "><span class='glyphicon glyphicon-minus' aria-hidden='true'></span> No</option>";

				$html .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-random' aria-hidden='true'></span> Yes</span>\">";
				$html .= "<span class='glyphicon glyphicon-random' aria-hidden='true'></span> Yes</option>";

			$html .= "</select>";
		$html .= "</div><div style='padding-top: 5px; padding-bottom: 10px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> If yes, then the bundle order is flipped and the main item is shown first in the bundle. <b>This only affects Option #8 and Option #9.</b></div>";

		// --> Discount apply to? Cart or item?
		$html .= "<div class=\"input-group\" style='padding-top: 5px;'>";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Application</div></span>";

			$html .= "<select id='formula-application' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html .= "<option value='" . FORMULA_ITEM . "'>Apply to item</option>";
				$html .= "<option value='" . FORMULA_CART . "' disabled>Apply to cart</option>";

			$html .= "</select>";
		$html .= "</div><div style='padding-top: 5px; padding-bottom: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Formula result either applies to the item or the cart total.</div>";

		// --> Formula message enable
		$html .= "<div class=\"input-group\" style='padding-top: 10px;'>";
		$html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Message enabled</div></span>";

			$html .= "<select id='formula-message-display' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">";

				$html .= "<option value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span> Enabled</span>\"";
				$html .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Enabled</option>";

				$html .= "<option selected value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span> Disabled</span>\">";
				$html .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Disabled</option>";

			$html .= "</select>";
		$html .= "</div><div style='padding-bottom: 5px; padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Display the formula message on the item page and listing?</div>";

		// --> Formula message
		$html .= "<div class=\"input-group\" style='padding-top:10px;'>
			  <span class=\"input-group-addon\"><div style='width:120px !important;'>Formula message</div></span>
			  <input type=\"text\" class=\"form-control\" placeholder=\"Ex: $100 off this product if...?\" aria-describedby=\"basic-addon1\" id='formula-message'>
			</div>";

		$html .= "<div class='well' style='margin-top: 20px; padding-top: 8px;'>";
			$html .= "<div style='font-weight: 600;'>Formula variable options:</div>";
			$html .= "<div style='padding-top: 5px;'>[PRICE] [DISCOUNT_PRICE] [STOCK] [COST] [LENGTH] [WIDTH] [HEIGHT] [WEIGHT]</div>";
			$html .= "<div style='padding-top: 5px;'>Example: [PRICE]*sin([STOCK])*cos([WEIGHT]) / sqrt([DISCOUNT_PRICE]) + [COST]</div>";
			$html .= "<div style='padding-top: 5px;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Note: If [DISCOUNT_PRICE] is 0 or NULL, [PRICE] will be defaulted to during the operation. It is recommended to always use [DISCOUNT_PRICE] over [PRICE] for this very reason.</div>";
		$html .= "</div>";

	$html .= "</div>";

	/*
		formula-status
		formula-operation
		formula-math
		formula-application
		formula-discount-price-start-date
		formula-discount-price-end-date
		formula-item-list
		formula-item-list-faux
		formula-flip
		formula-message-display
		formula-message

		p_formula_status
		p_formula_operation
		p_formula_math
		p_formula_application
		p_formula_discount_date_end
		p_formula_discount_date_start
		p_formula_item_html
		p_formula_items_data
		p_formula_item_faux_html
		p_formula_items_faux_data
		p_formula_flip
		p_formula_message_display
		p_formula_message
	*/

	return $html;
}

function php_html_formula_links_items_builder($data = NULL) {
	try {
		$fluid = new Fluid ();

		if(isset($data)) {
			$f_data = $data;
		}
		else {
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		}

		$i = 0;



		if(isset($f_data->f_selector->v_selection->p_selection)) {
			$fluid->php_db_begin();

			$f_where = "WHERE p_id IN (";

			$f_item_array = NULL;
			foreach($f_data->f_selector->v_selection->p_selection as $f_item) {
				if($i != 0) {
					$f_where .= ", ";
				}

				$f_where .= $fluid->php_escape_string($f_item->p_id);

				$f_item_array[$f_item->p_id] = (array)$f_item;
				$i++;
			}
			$f_where .= ")";

			// --> Found items, lets query them now.
			if($i > 0) {
				$fluid->php_db_query("SELECT p.p_id, p.p_catid, p.p_mfgid, p.p_enable, p.p_name, p.p_images, p.p_mfgcode, p.p_mfg_number, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $f_where);
			}

			$fluid->php_db_commit();

			$html = "<select id='" . $f_data->f_formula_list . "' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"8\" data-width=\"80%\">";

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);
					$p_images = $fluid->php_process_images($value['p_images']);
					$width_height = $fluid->php_process_image_resize($p_images[0], "20", "20", $f_img_name);

					$ft_name = preg_replace('/[^\/\A-Za-z0-9^\-_.,\(\) ]/', '', $value['m_name'] . " " . $value['p_name']);
					if(strlen($ft_name) > 80) {
						$ft_name = substr($ft_name, 0, 80) . "...";
					}
					else {
						$ft_name = $ft_name;
					}

					$p_quantity = 1;
					$p_quantity_text = NULL;
					if(isset($f_item_array[$value['p_id']]['p_quantity'])) {
						$p_quantity = $f_item_array[$value['p_id']]['p_quantity'];
						$p_quantity_text = " - component qty: " . $f_item_array[$value['p_id']]['p_quantity'];
					}

					$html .= "<option data-id='" . $value['p_id'] . "' data-penable = '" . $value['p_enable'] . "' data-pcatid='" . $value['p_catid'] . "' data-pmfgid='" . $value['p_mfgid'] . "' data-pquantity='" . $p_quantity . "' data-name=\"" . $ft_name . "\" value='" . base64_encode($value['p_mfgcode']) . "'";
					$html .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $ft_name . "<div style='display: inline-block; padding-left: 8px; font-size: 10px; font-style: oblique; font-weight: 600;'>upc: " . $value['p_mfgcode'] . $p_quantity_text . "</div>\"";

					$html .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $ft_name . "</option>";
				}
			}

			$html .= "</select>";
		}

		// --> Didn't find any items, send back a blank <select>
		if(isset($f_data->f_item_editor)) {
			return $html;
		}
		else {
			if($i == 0) {
				$html = FORMULA_HTML_ITEM_SELECT_BLANK;
			}

			// --> replace innerhtml with new <select> -->  f-formula-item-list-div
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode($f_data->f_formula_list_div), "innerHTML" => base64_encode($html))));

			$execute_functions[]['function'] = "js_update_select_pickers";

			$execute_functions[]['function'] = "js_modal_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-main-modal"));

			$execute_functions[]['function'] = "js_modal_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_load_product_copy() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		// Categories or Manufacturer
		$output = "Select a " . $fluid_mode->mode_name_real . " to copy the selected items into:";
		$output .= "<div style='margin-bottom:20px;'>";

			$output .= "<select id='product-category-copy' class=\"form-control selectpicker\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"100%\" onchange='FluidVariables.v_product.p_category =(this.options[this.selectedIndex].value);'>";

			$fluid->php_db_query("SELECT * FROM ". $fluid_mode->table . " ORDER BY " . $fluid_mode->sortorder . " ASC");

			foreach($fluid->db_array as $key => $value) {
				if($value[$fluid_mode->X . '_parent_id'] == NULL)
					$data[$value[$fluid_mode->X . '_id']]['parent'] = $value;
				else
					$data[$value[$fluid_mode->X . '_parent_id']]['childs'][] = $value;
			}

			$i = 0;
			foreach($data as $parent) {
				if(isset($parent['childs'])) {
					$output .= "<optgroup label='" . $parent['parent'][$fluid_mode->name] . "'>";

					foreach($parent['childs'] as $value) {
						$p_images = $fluid->php_process_images($value[$fluid_mode->images]);
						$f_img_name = str_replace(" ", "_", $value[$fluid_mode->name] . "_" . $value[$fluid_mode->id]);
						$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

						$width_height = $fluid->php_process_image_resize($p_images[0], "20", "20", $f_img_name);

						$output .= "<option data-name=\"" . $value[$fluid_mode->name] . "\" value='" . $value[$fluid_mode->id] . "'";
						$output .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "\"";

						if($i == 0)
							$output .= " selected";

						$output .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "</option>";

						$i++;
					}
					$output .= "</optgroup>";
				}
			}
			$output .="</select>";
		$output .= "</div>";

		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

			$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

			$selection_output = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";

			foreach($fluid->db_array as $value) {
				// Process the image.
				$p_images = $fluid->php_process_images($value['p_images']);
				$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

				$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

				$selection_output .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";
			}

			$selection_output .= "</div>";

		$selection_output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-warning' role='alert'>Are you sure you want to copy the selected items?</div>" . $selection_output);
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_product_copy(\"" . $fluid_mode->mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Product Copier</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;

				$modal .= "
				<div class=\"input-group\" style='padding-left: 0px; padding-top: 0px; padding-bottom: 30px;'>
					<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:180px !important;'>Item selection after operation:</div></span>

					<select id='f-selection-operation' class=\"form-control selectpicker show-menu-arrow show-tick\" data-size=\"10\" data-container=\"#fluid-modal\" data-width=\"50%\">
						<option value='0'>Unselect</option>
						<option value='1'>Keep selected</option>
					</select>
				</div>";

				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' onClick='FluidVariables.v_select_option = document.getElementById(\"f-selection-operation\").options[document.getElementById(\"f-selection-operation\").selectedIndex].value; js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_product_creator_editor() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		// Error checking and getting manufacturer and category data.
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
				$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";
		}
		else
			$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";

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
				$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";
		}
		else
			$fluid->db_error = "ERROR: There are no categories. Please create a category first before trying to add a item.";

		// We didn't find any child categories or manufacturers, return a error.
		if(count($fluid->db_error) > 0) {
			$fluid->php_db_commit();
			return json_encode(array("js_execute_array" => 0, "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
			exit(0);
		}

		// Editor mode.
		if(isset($_REQUEST['data'])) {
			$editor = TRUE;
			$p_id = json_decode(base64_decode($_REQUEST['data']));

			$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE p.p_id = '" . $fluid->php_escape_string($p_id) . "'");

			$data = $fluid->db_array[0];

			$modal_title = $data['m_name'] . " " . $data['p_name'];
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_product_create_and_edit(\"edit\", \"" . $mode . "\");' ><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save Changes</button></div>";
		}
		else { // Creation mode.
			$editor = FALSE;
			$data = NULL;
			$modal_title = "Item Creator";
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_product_create_and_edit(\"add\", \"" . $mode . "\");' ><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Add Product</button></div>";
		}

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div style='display: inline-block; width: 100%;' class='panel-heading'>" . $modal_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='productcreatetabs'>
						<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateinformation' data-target='#productcreateinformation' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Information</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#productcreateimages' data-target='#productcreateimages' data-toggle='tab'><span class='glyphicon glyphicon-picture'></span> Images</a></li>
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
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal' onClick='js_image_dropzone_destroy();'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>" . $modal_footer_confirm_button_html . "
			  </div>

			</div>
		  </div>";

		$fluid->php_db_commit();

		if(count($fluid->db_error) < 1)
			$data_array = php_html_items_editor($data, $manufacturer_data, $category_data, $editor, $mode);
		else
			$data_array['html'] = NULL;

		$execute_functions[0]['function'] = "js_modal_product_create_and_edit";

		if($data_array['image_array'] != NULL)
			$execute_functions[0]['data'] = base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "info_html" => $data_array['html'], "image_html" => base64_encode(HTML_IMAGE_DROPZONE), "image_data" => base64_encode(json_encode($data_array['image_array'])))));
		else
			$execute_functions[0]['data'] = base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "info_html" => $data_array['html'], "image_html" => base64_encode(HTML_IMAGE_DROPZONE))));

		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		restore_error_handler();
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_product_delete() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();
		$data = json_decode(base64_decode($_REQUEST['data']));
		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		// Warning Message
		$output = "<div style='margin-bottom:20px;'>";
		$output .= "<div class='alert alert-danger' role='alert'>Are you sure you want to delete the selected products?</div>";

		// Generate query to load data of the selected items.
		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";
			$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

			$output .= "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
			foreach($fluid->db_array as $value) {
				// Process the image.
				$p_images = $fluid->php_process_images($value['p_images']);
				$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

				$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

				$output .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";
			}

			$output .= "</div>";

		$output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-danger' role='alert'>Are you sure?</div>");
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_product_delete(\"" . $mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Product Deletion</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;
				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_load_product_move() {
	$fluid = new Fluid ();
	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		// Categories or Manufacturer.
		$output = "Select a " . $fluid_mode->mode_name_real . " to move the selected items into:";
		$output .= "<div style='margin-bottom:20px;'>";

			$output .= "<select id='product-category-move' class=\"form-control selectpicker\" data-live-search=\"true\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"100%\" onchange='FluidVariables.v_product.p_category =(this.options[this.selectedIndex].value);'>";

			$fluid->php_db_query("SELECT * FROM ". $fluid_mode->table . " ORDER BY " . $fluid_mode->sortorder . " ASC");

			foreach($fluid->db_array as $key => $value) {
				if($value[$fluid_mode->X . '_parent_id'] == NULL)
					$data[$value[$fluid_mode->X . '_id']]['parent'] = $value;
				else
					$data[$value[$fluid_mode->X . '_parent_id']]['childs'][] = $value;
			}

			$i = 0;
			foreach($data as $parent) {
				if(isset($parent['childs'])) {
					$output .= "<optgroup label='" . $parent['parent'][$fluid_mode->name] . "'>";

					foreach($parent['childs'] as $value) {
						$p_images = $fluid->php_process_images($value[$fluid_mode->images]);
						$f_img_name = str_replace(" ", "_", $value[$fluid_mode->name] . "_" . $value[$fluid_mode->id]);
						$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

						$width_height = $fluid->php_process_image_resize($p_images[0], "20", "20", $f_img_name);

						$output .= "<option data-name=\"" . $value[$fluid_mode->name] . "\" value='" . $value[$fluid_mode->id] . "'";
						$output .= " data-content=\"<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "\"";

						if($i == 0)
							$output .= " selected";

						$output .= "><img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='float:left; padding-right:3px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;'></img> " . $value[$fluid_mode->name] . "</option>";

						$i++;
					}
					$output .= "</optgroup>";
				}
			}
			$output .="</select>";
		$output .= "</div>";

		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

			$selection_output = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";

			foreach($fluid->db_array as $value) {
				// Process the image.
				$p_images = $fluid->php_process_images($value['p_images']);
				$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

				$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

				$selection_output .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";
			}

			$selection_output .= "</div>";

		$selection_output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-warning' role='alert'>Are you sure you want to move the selected items?</div>" . $selection_output);
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_product_move(\"" . $mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Product Mover</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;

				$modal .= "
				<div class=\"input-group\" style='padding-left: 0px; padding-top: 0px; padding-bottom: 30px;'>
					<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:180px !important;'>Item selection after operation:</div></span>

					<select id='f-selection-operation' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">
						<option value='0'>Unselect</option>
						<option value='1'>Keep selected</option>
					</select>
				</div>";

				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-success' onClick='FluidVariables.v_select_option = document.getElementById(\"f-selection-operation\").options[document.getElementById(\"f-selection-operation\").selectedIndex].value; js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[0]['function'] = "js_modal";
		$execute_functions[0]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));
		$execute_functions[1]['function'] = "js_modal_show";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

// Updates items from the all powerful multi item editor. Muwahahaha!
function php_multi_item_update() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		$selection = $_REQUEST['selection_obj']; // The list of items we have selected for multi item editing.
		$selection_tmp = $fluid->php_object_to_array(json_decode(base64_decode($selection)));

		$item_obj = json_decode(base64_decode($_REQUEST['multi_item_obj'])); // The item data.

		if(isset($_REQUEST['page_num'])) {
			$f_page_num = $_REQUEST['page_num'];
		}
		else {
			$f_page_num = 1;
		}

		if($f_page_num < 1) {
			$f_page_num = 1;
		}

		$cat_refresh = Array();
		$case_start = " SET";
		$case = NULL;
		$sort_array = NULL; // Keeping track which categories and manufacturers need there items to be re-sorted.
		$p_c_linking_array = NULL; // Keeping track of which product category linking to update.
		$p_component_array = NULL; // Keeping track of the component items for updating.
		$where = "WHERE p_id IN (";
		$where_p_c_linking = "WHERE l_p_id IN (";
		$where_component_linking = "WHERE cp_master_id IN (";

		$i = 0;
		$image_array_copy = Array(); // For storing the data of images that need to be copied from the temporary image folder to the main image folder.

		foreach($item_obj as $key => $item) {			
			// To help with reloading session id data for removing / updating images if a timeout were to occur.
			if(isset($item->f_session_id)) {
				$_SESSION['fluid_admin'] = base64_decode($item->f_session_id);
			}

			// Only update items that have saved changes.
			if($item->update > 0) {
				$tmp_array = (array)$item->data_obj;
				$cat_refresh[base64_decode($tmp_array["p_" . $fluid_mode->mode_name_real])] = base64_decode($tmp_array["p_" . $fluid_mode->mode_name_real]);

				if($i != 0) {
					$where .= ", ";
					$where_p_c_linking .= ", ";
					$where_component_linking .= ", ";
				}

				$where .= $fluid->php_escape_string($item->p_id);
				$where_p_c_linking .= $fluid->php_escape_string($item->p_id);
				$where_component_linking .= $fluid->php_escape_string($item->p_id);

				// Process some of the variables which could break the sql queries if they are blank.
				$p_price = !empty($item->data_obj->p_price) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_price)) . "'" : "NULL";
				$p_cost_real = !empty($item->data_obj->p_cost_real) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_cost_real)) . "'" : "NULL";
				$p_cost_real_old = !empty($item->data_obj->p_cost_real_old) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_cost_real_old)) . "'" : "NULL";
				$p_stock = !empty($item->data_obj->p_stock) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_stock)) . "'" : "NULL";
				$p_stock_old = !empty($item->data_obj->p_stock_old) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_stock_old)) . "'" : "NULL";
				$p_cost_old = !empty($item->data_obj->p_cost) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_cost)) . "'" : "NULL";

				$p_buyqty = !empty($item->data_obj->p_buyqty) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_buyqty)) . "'" : "'1'";

				$p_cost_average = $fluid->php_calculate_cost_average(Array("old_cost" => base64_decode($item->data_obj->p_cost_real_old), "old_stock" => base64_decode($item->data_obj->p_stock_old), "old_cost_avg" => base64_decode($item->data_obj->p_cost)), Array("new_cost" => base64_decode($item->data_obj->p_cost_real), "new_stock" => base64_decode($item->data_obj->p_stock)));
				$p_cost = !empty($p_cost_average) ? "'" . $fluid->php_escape_string($p_cost_average) . "'" : "NULL";

				$p_price_discount = !empty($item->data_obj->p_price_discount) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_price_discount)) . "'" : "NULL";
				$p_discount_date_start = !empty($item->data_obj->p_discount_date_start) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_discount_date_start)) . "'" : "NULL";
				$product_arrival_end_date = !empty($item->data_obj->p_newarrivalenddate) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_newarrivalenddate)) . "'" : "NULL";
				$p_trending = !empty($item->data_obj->p_trending) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_trending)) . "'" : "NULL";
				$p_instore = !empty($item->data_obj->p_instore) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_instore)) . "'" : "'0'";
				$p_arrivaltype = !empty($item->data_obj->p_arrivaltype) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_arrivaltype)) . "'" : "'0'";

				$p_freeship = !empty($item->data_obj->p_freeship) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_freeship)) . "'" : "'1'";

				$p_preorder = !empty($item->data_obj->p_preorder) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_preorder)) . "'" : "NULL";
				$p_rental = !empty($item->data_obj->p_rental) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_rental)) . "'" : "'0'";
				$p_special_order = !empty($item->data_obj->p_special_order) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_special_order)) . "'" : "'0'";

				$p_namenum = !empty($item->data_obj->p_namenum) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_namenum)) . "'" : "NULL";
				$p_showalways = !empty($item->data_obj->p_showalways) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_showalways)) . "'" : "NULL";
				$p_rebate_claim = !empty($item->data_obj->p_rebate_claim) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_rebate_claim)) . "'" : "NULL";
				$p_component = !empty($item->data_obj->p_component) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_component)) . "'" : "NULL";
				$p_stock_end = !empty($item->data_obj->p_stock_end) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_stock_end)) . "'" : "NULL";

				$p_discount_date_end = !empty($item->data_obj->p_discount_date_end) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_discount_date_end)) . "'" : "NULL";
				// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
				if(isset($item->data_obj->p_stock_end) && isset($item->data_obj->p_stock)) {
					if(base64_decode($item->data_obj->p_stock_end) == 1 && base64_decode($item->data_obj->p_stock) < 1) {
						$f_date_end = strtotime(base64_decode($item->data_obj->p_discount_date_end));

						if($f_date_end > strtotime(date("Y-m-d H:i:s"))) {
							$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";
						}
						else if(empty(base64_decode($item->data_obj->p_discount_date_end))) {
							$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";
						}
					}
				}

				$p_mfg_number = !empty($item->data_obj->p_mfg_number) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_mfg_number)) . "'" : "NULL";
				$p_formula_discount_date_end = !empty($item->data_obj->p_formula_discount_date_end) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_discount_date_end)) . "'" : "NULL";
				$p_formula_discount_date_start = !empty($item->data_obj->p_formula_discount_date_start) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_discount_date_start)) . "'" : "NULL";

				$p_date_hide = !empty($item->data_obj->p_date_hide) ? "'" . $fluid->php_escape_string(base64_decode($item->data_obj->p_date_hide)) . "'" : "NULL";

				// Lets build ourselves a fancy update query.
				$case_temp = "WHEN (`p_id`) = ('" . $fluid->php_escape_string($item->p_id) . "') THEN";

				//p_enable, p_c_filters, p_m_filters, p_newarrivalenddate, p_mfgid, p_mfgcode, p_catid, p_name, p_desc, p_details, p_specs, p_inthebox, p_seo, p_keywords, p_price, p_price_discount, p_discount_date_end, p_stock, p_buyqty, p_images
				$case['p_enable'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_status)) . "'";
				$case['p_zero_status'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_zero_status)) . "'";

				// Process the manufacturer filters.
				$p_m_filters_tmp = NULL;
				if(isset($item->data_obj->p_m_filters)) {
					$m_tmp_64 = base64_decode($item->data_obj->p_m_filters);

					if(!empty($m_tmp_64)) {
						foreach(json_decode(base64_decode($item->data_obj->p_m_filters)) as $key_f => $m_filter_tmp) {
							if(is_object($m_filter_tmp)) {
								$m_tmp = $m_filter_tmp;
							}
							else {
								$m_tmp = json_decode(base64_decode(base64_decode($m_filter_tmp)));
							}

							$p_m_filters_tmp[$m_tmp->sub_id] = $m_tmp;
						}
					}
				}

				$p_m_filters_tmp = (object)$p_m_filters_tmp;
				$p_m_filters = !empty((array)$p_m_filters_tmp) ? "'" . $fluid->php_escape_string(json_encode($p_m_filters_tmp)) . "'" : "NULL";
				$case['p_m_filters'][] = $case_temp . " " . $p_m_filters;

				// Process the category filters.
				$p_c_filters_tmp = NULL;
				if(isset($item->data_obj->p_m_filters)) {
					$c_tmp_64 = base64_decode($item->data_obj->p_c_filters);

					if(!empty($c_tmp_64)) {
						foreach(json_decode(base64_decode($item->data_obj->p_c_filters)) as $key_f => $c_filter_tmp) {
							if(is_object($c_filter_tmp)) {
								$c_tmp = $c_filter_tmp;
							}
							else {
								$c_tmp = json_decode(base64_decode(base64_decode($c_filter_tmp)));
							}

							$p_c_filters_tmp[$c_tmp->sub_id] = $c_tmp;
						}
					}
				}

				$p_c_filters_tmp = (object)$p_c_filters_tmp;
				$p_c_filters = !empty((array)$p_c_filters_tmp) ? "'" . $fluid->php_escape_string(json_encode($p_c_filters_tmp)) . "'" : "NULL";
				$case['p_c_filters'][]= $case_temp . " " . $p_c_filters;

				// Process the product category linking.
				if(isset($item->data_obj->p_c_linking)) {
					$p_c_64 = base64_decode($item->data_obj->p_c_linking);
					if(!empty($p_c_64)) {
						foreach(json_decode(base64_decode($item->data_obj->p_c_linking)) as $key_f => $p_c_l_tmp) {
							$p_c_linking_array[$item->p_id][$key_f] = $p_c_l_tmp;
						}
					}
				}

				// Process the component items.
				if(isset($item->data_obj->p_component_data)) {
					$component_64 = base64_decode($item->data_obj->p_component_data);
					if(!empty($component_64)) {
						foreach(json_decode(base64_decode($item->data_obj->p_component_data)) as $key_f => $component_tmp) {
							$p_component_array[$item->p_id][$key_f] = $component_tmp;
						}
					}
				}

				$case['p_newarrivalenddate'][] = $case_temp . " " . $product_arrival_end_date;
				$case['p_mfgid'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_manufacturer)) . "'";
				$case['p_mfgcode'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_barcode)) . "'";
				$case['p_mfg_number'][] = $case_temp . " " . $p_mfg_number;
				$case['p_catid'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_category)) . "'";
				$case['p_name'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_name)) . "'";
				$case['p_desc'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_description)) . "'";
				$case['p_details'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_details)) . "'";
				$case['p_specs'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_specs)) . "'";
				$case['p_inthebox'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_inthebox)) . "'";
				$case['p_seo'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_seo)) . "'";
				$case['p_keywords'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_keywords)) . "'";
				$case['p_price'][] = $case_temp . " " . $p_price;
				$case['p_cost'][] = $case_temp . " " . $p_cost;
				$case['p_cost_real'][] = $case_temp . " " . $p_cost_real;
				$case['p_price_discount'][] = $case_temp . " " . $p_price_discount;
				$case['p_discount_date_end'][] = $case_temp . " " . $p_discount_date_end;
				$case['p_discount_date_start'][] = $case_temp . " " . $p_discount_date_start;

				$case['p_stock'][] = $case_temp . " " . $p_stock;
				$case['p_buyqty'][] = $case_temp . " " . $p_buyqty;
				$case['p_length'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_length)) . "'";
				$case['p_width'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_width)) . "'";
				$case['p_height'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_height)) . "'";
				$case['p_weight'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_weight)) . "'";
				$case['p_trending'][] = $case_temp . " " . $p_trending;
				$case['p_instore'][] = $case_temp . " " . $p_instore;
				$case['p_arrivaltype'][] = $case_temp . " " . $p_arrivaltype;
				$case['p_freeship'][] = $case_temp . " " . $p_freeship;
				$case['p_preorder'][] = $case_temp . " " . $p_preorder;
				$case['p_rental'][] = $case_temp . " " . $p_rental;
				$case['p_special_order'][] = $case_temp . " " . $p_special_order;
				$case['p_namenum'][] = $case_temp . " " . $p_namenum;
				$case['p_showalways'][] = $case_temp . " " . $p_showalways;
				$case['p_rebate_claim'][] = $case_temp . " " . $p_rebate_claim;
				$case['p_component'][] = $case_temp . " " . $p_component;
				$case['p_stock_end'][] = $case_temp . " " . $p_stock_end;
				$case['p_date_hide'][] = $case_temp . " " . $p_date_hide;

				$case['p_formula_discount_date_start'][] = $case_temp . " " . $p_formula_discount_date_start;
				$case['p_formula_discount_date_end'][] = $case_temp . " " . $p_formula_discount_date_end;
				$case['p_formula_status'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_status)) . "'";
				$case['p_formula_operation'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_operation)) . "'";
				$case['p_formula_math'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_math)) . "'";
				$case['p_formula_application'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_application)) . "'";
				$case['p_formula_item_html'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_item_html)) . "'";
				$case['p_formula_items_data'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_items_data)) . "'";
				$case['p_formula_item_faux_html'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_item_faux_html)) . "'";
				$case['p_formula_items_faux_data'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_items_faux_data)) . "'";
				$case['p_formula_flip'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_flip)) . "'";
				$case['p_formula_message_display'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_message_display)) . "'";
				$case['p_formula_message'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_formula_message)) . "'";
				$case['p_category_items_data'][] = $case_temp . " '" . $fluid->php_escape_string(base64_decode($item->data_obj->p_category_items_data)) . "'";

				// 1. Detect if a change in category or manufacturer has happened. Perhaps get this variable stored in the passed JS object?
				// A change in category, time to start recording and prepping for a sort updates.
				if($item->prev_c_id != $item->data_obj->p_category) {
					$sort_array['p_catid'][base64_decode($item->prev_c_id)]['id'] = base64_decode($item->prev_c_id);
					$sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['id'] = base64_decode($item->data_obj->p_category);

					// Need to update the data in the selection of items so we can feed it back to the client.
					$selection_tmp[base64_decode($item->data_obj->p_id)]['p_enable'] = base64_decode($item->data_obj->p_status);

					if($fluid_mode->mode != "manufacturers")
						$selection_tmp[base64_decode($item->data_obj->p_id)]['p_catid'] = base64_decode($item->data_obj->p_category);

					// 2. Count the sort order required for the new category the item is being moved into. Need to make sure counting doesnt overlap another item being edited and changed category as well.
					// Count total not found, lets do a product count.
					if(!isset($sort_array['p_catid'][base64_decode($item->prev_c_id)]['count'])) {
						$fluid->php_db_query("SELECT p.p_sortorder AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_catid=" . $fluid->php_escape_string(base64_decode($item->prev_c_id)) . " ORDER BY p.p_sortorder DESC LIMIT 1");

						if(isset($fluid->db_array[0]['tmp_c_product_count']))
							$sort_array['p_catid'][base64_decode($item->prev_c_id)]['count'] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
						else
							$sort_array['p_catid'][base64_decode($item->prev_c_id)]['count'] = 1;
					}
					else
						$sort_array['p_catid'][base64_decode($item->prev_c_id)]['count']++;

					// Count total not found, lets do a product count.
					if(!isset($sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['count'])) {
						$fluid->php_db_query("SELECT p.p_sortorder AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_catid=" . $fluid->php_escape_string(base64_decode($item->data_obj->p_category)) . " ORDER BY p.p_sortorder DESC LIMIT 1");

						if(isset($fluid->db_array[0]['tmp_c_product_count']))
							$sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['count'] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
						else
							$sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['count'] = 1;
					}
					else
						$sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['count']++;

					// 3. Update the sort orders into the case query.
					// Updated temporary sort item number for this item's new category. It will get updated in the refresh after.
					$case['p_sortorder'][] = $case_temp . " '" . $fluid->php_escape_string($sort_array['p_catid'][base64_decode($item->data_obj->p_category)]['count']) . "'";
				}

				// A change in manufacturers.
				if($item->prev_m_id != $item->data_obj->p_manufacturer) {
					$sort_array['p_mfgid'][base64_decode($item->prev_m_id)]['id'] = base64_decode($item->prev_m_id);
					$sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['id'] = base64_decode($item->data_obj->p_manufacturer);

					if($fluid_mode->mode == "manufacturers")
						$selection_tmp[base64_decode($item->data_obj->p_id)]['p_catid'] = base64_decode($item->data_obj->p_manufacturer);

					// Count total not found, lets do a product count.
					if(!isset($sort_array['p_mfgid'][base64_decode($item->prev_m_id)]['count'])) {
						$fluid->php_db_query("SELECT p.p_sortorder_mfg AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_mfgid=" . $fluid->php_escape_string(base64_decode($item->prev_m_id)) . " ORDER BY p.p_sortorder_mfg DESC LIMIT 1");

						if(isset($fluid->db_array[0]['tmp_c_product_count']))
							$sort_array['p_mfgid'][base64_decode($item->prev_m_id)]['count'] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
						else
							$sort_array['p_mfgid'][base64_decode($item->prev_m_id)]['count'] = 1;
					}
					else
						$sort_array['p_mfgid'][base64_decode($item->prev_m_id)]['count']++;

					// Count total not found, lets do a product count.
					if(!isset($sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['count'])) {
						$fluid->php_db_query("SELECT p.p_sortorder_mfg AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_mfgid=" . $fluid->php_escape_string(base64_decode($item->data_obj->p_manufacturer)) . " ORDER BY p.p_sortorder_mfg DESC LIMIT 1");

						if(isset($fluid->db_array[0]['tmp_c_product_count']))
							$sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['count'] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
						else
							$sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['count'] = 1;
					}
					else
						$sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['count']++;

					// Updated temporary sort item number for this item's new manufacturer. It will get updated in the refresh after.
					$case['p_sortorder_mfg'][] = $case_temp . " '" . $fluid->php_escape_string($sort_array['p_mfgid'][base64_decode($item->data_obj->p_manufacturer)]['count']) . "'";

				}

				// Re-arrange the picture order based on the queue list order.
				$p_images = Array();
				
				// When doing copy/paste of item data, sometimes the p_imageorder wont be built if the user never entered the item editor. So nothing is going to change. We just rebuild the image data as is.
				if(empty((array)$item->data_obj->p_imageorder)) {
					foreach($item->data_obj->p_images as $image) {
						$image->file->fullpath = FOLDER_IMAGES . $image->file->image;

						// The temp path data is not required. Remove it from the object.
						unset($image->file->tempfullpath);

						// Record the new images only, so we can process them after the queries.
						$image_array_copy[$image->file->rand] = $image;

						// Prepare a new image list for the query.
						$p_images{$image->file->rand} = $image;
					}
				}
				else {
					// Re-order the image data.
					foreach($item->data_obj->p_imageorder as $order) {
						foreach($item->data_obj->p_images as $image) {
							$image->file->fullpath = FOLDER_IMAGES . $image->file->image;

							// The temp path data is not required. Remove it from the object.
							unset($image->file->tempfullpath);

							if($order->name == $image->file->name && $order->size == $image->file->size) {
								// Record the new images only, so we can process them after the queries.
								$image_array_copy[$image->file->rand] = $image;

								// Prepare a new image list for the query.
								$p_images{$image->file->rand} = $image;
							}
						}
					}
				}
				$case['p_images'][] = $case_temp . " '" . $fluid->php_escape_string(base64_encode(json_encode($p_images))) . "'";

				$i++;
			}
		}

		// Check that the new images actually exist before moving. If they dont, remove the query from the update.
		$f_b_img_error = FALSE;
		foreach($image_array_copy as $img) {
			if(!file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image)) {
				$f_b_img_error = TRUE;

				// Remove the image update. No changes to images will happen.
				unset($case['p_images']);

				break;
			}
		}

		$where .= ")";
		$where_p_c_linking .= ")";
		$where_component_linking .= ")";

		// Re-encode the item selections.
		$selection = base64_encode(json_encode($selection_tmp));

		// Record the original images, so we can process them after the queries. Also process the old categories or manufacturers so we can refresh them.
		$image_array_delete = Array();
		$fluid->php_db_query("SELECT " . $fluid_mode->p_catmfg_id . ", p_images FROM " . TABLE_PRODUCTS . " " . $where);
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $data) {
				$image_array_delete[] = $data['p_images'];
				$cat_refresh[$data[$fluid_mode->p_catmfg_id]] = $data[$fluid_mode->p_catmfg_id];
			}
		}

		// Run through the case array and build a proper case update query.
		$query = "UPDATE " . TABLE_PRODUCTS . " SET";
		$i = 0;
		foreach($case as $key => $case_data) {
			if($i > 0)
				$query .= ",\n";

			$query .= " `" . $key . "` = CASE";

			foreach($case_data as $key_d => $data)
				$query .= " " . $data;

			$query .= " END";

			$i++;
		}

		// 4. Run the case update query.
		$fluid->php_db_query($query . " " . $where);

		// 4.5. Update the product category linking table.
		// Remove the existing links based on l_p_id.
		// Then re-insert new ones from $p_c_linking_array[$item->p_id][$key_f] = $p_c_l_tmp;
		if(isset($where_p_c_linking)) {
			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_CATEGORY_LINKING . " " . $where_p_c_linking);
		}

		if(isset($p_c_linking_array)) {
			$p_c_l_query = "INSERT INTO " . TABLE_PRODUCT_CATEGORY_LINKING . " (l_p_id, l_c_id) VALUES ";

			$i = 0;

			foreach($p_c_linking_array as $p_key => $p_data) {
				foreach($p_data as $c_key => $p_c_data) {
					if($i > 0)
						$p_c_l_query .= ", ";

					$p_c_l_query .= "('" . $fluid->php_escape_string($p_key) . "', '" . $fluid->php_escape_string($c_key) . "')";

					$i++;
				}
			}

			$fluid->php_db_query($p_c_l_query);
		}

		// 4.6 Update the component tables.
		// Remove existing components based on the main item id.
		// Then re-insert new ones from the $p_component_array[$item->p_id][$key_f] data.
		// --> {"0":{"p_id":"81","p_catid":"1","p_mfgid":"5","p_mfgcode":"NjE5NjU5MDY2Nzcy","p_quantity":"3"}}
		if(isset($where_component_linking)) {
			$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_COMPONENT . " " . $where_component_linking);
		}

		if(isset($p_component_array)) {
			$p_comp_query = "INSERT INTO " . TABLE_PRODUCT_COMPONENT . " (cp_master_id, cp_p_id, cp_p_stock) VALUES ";

			$i = 0;

			foreach($p_component_array as $p_key => $p_data) {
				foreach($p_data as $p_tmp) {
					if($i > 0) {
						$p_comp_query .= ", ";
					}

					$p_comp_query .= "('" . $fluid->php_escape_string($p_key) . "', '" . $fluid->php_escape_string($p_tmp->p_id) . "', '" . $fluid->php_escape_string($p_tmp->p_quantity) . "')";

					$i++;
				}
			}

			$fluid->php_db_query($p_comp_query);
		}

		// 5. Rebuild sort orders of the old categories and manufacturers. Even if the sort order counts are too high, it is ok as this will rebuild them. Run this after the case update query.
		// There were some items that changed manufacturer or category, time to rebuild the sort orders of the affected categories and manufacturers.
		if(isset($sort_array)) {
			// Categories changed, lets do a re-build.
			if(isset($sort_array['p_catid'])) {
				foreach($sort_array['p_catid'] as $key => $cat) {
					$rand = rand(100, 999999999999);

					$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_catid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

					$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder INT auto_increment primary key NOT NULL;");

					// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
					$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_catid) SELECT p_id, p_catid FROM " . TABLE_PRODUCTS . " WHERE p_catid = '" . $fluid->php_escape_string($cat['id']) . "' ORDER BY p_sortorder ASC");

					// Merge data from temp_table_sort into TABLE_PRODUCTS via p_id.
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder = src.p_sortorder WHERE dest.p_id = src.p_id");

					// Drop the temporary table.
					$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
				}
			}

			// Manufacturers changed, lets do a re-build.
			if(isset($sort_array['p_mfgid'])) {
				foreach($sort_array['p_mfgid'] as $key => $cat) {
					$rand = rand(100, 999999999999);

					$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_mfgid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

					$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder_mfg INT auto_increment primary key NOT NULL;");

					// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
					$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_mfgid) SELECT p_id, p_mfgid FROM " . TABLE_PRODUCTS . " WHERE p_mfgid = '" . $fluid->php_escape_string($cat['id']) . "' ORDER BY p_sortorder_mfg ASC");

					// Merge data from temp_table_sort into TABLE_PRODUCTS via p_id.
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder_mfg = src.p_sortorder_mfg WHERE dest.p_id = src.p_id");

					// Drop the temporary table.
					$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
				}
			}
		}


		$fluid->php_db_commit();

		$iFunc = 0;
		$execute_functions[$iFunc]['function'] = "js_modal_hide";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode("#fluid-confirm-modal"));
		$iFunc++;

		// Only run this if there was a category or manufacturer change.
		// No need to update when in item mode or if no category or manufacturer was changed on any of the items.
		$f_search_data = NULL;
		if(isset($sort_array) && $fluid_mode->mode != "items") {
			// 1. Run a check, if in category mode and no changes to categories. It means only manufacturers was changed. No need to run this or the code below either.
			$selection_data = json_decode(base64_decode($selection));
			$c_selection = NULL;

			// Create a new c_selection category selection item count that gets sent back to the client to update FluidVariables.v_selection.c_selection.
			foreach($selection_data as $key => $tmp_data)
				if(isset($c_selection[$tmp_data->p_catid]))
					$c_selection[$tmp_data->p_catid] = $c_selection[$tmp_data->p_catid] + 1;
				else
					$c_selection[$tmp_data->p_catid] = 1;

			$data_return = Array();
			$data_return['cat_refresh'] = $cat_refresh;
			$data_return['selection'] = $selection;
			$data_return['c_selection'] = base64_encode(json_encode($c_selection));

			$execute_functions[$iFunc]['function'] = "js_refresh_category";
			$execute_functions[$iFunc]['data'] = base64_encode(json_encode($data_return));
			$iFunc++;
		}

		if($fluid_mode->mode == "items") {
			if(isset($_REQUEST['data'])) {
				$f_search_data = json_decode(base64_decode($_REQUEST['data']));
			}

			if(isset($f_search_data->f_search_input)) {
				if($f_search_data->f_search_input != '') {
					$f_search_data_input = base64_encode($f_search_data->f_search_input);
				}
			}
		}

		if(isset($f_search_data_input)) {
			$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $selection . "&page_num=" . $f_page_num . "&mode=" . $fluid_mode->mode . "&f_search_data=" . $f_search_data_input)));
		}
		else {
			$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $selection . "&page_num=" . $f_page_num . "&mode=" . $fluid_mode->mode)));
		}

		$execute_functions[$iFunc]['function'] = "js_fluid_ajax";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode($temp_data));
		$iFunc++;

		$execute_functions[$iFunc]['function'] = "js_multi_item_update_clear";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode(""));
		$iFunc++;

		// Process the images after the queries are successful.
		// Delete the original images.
		if($f_b_img_error == FALSE) {
			foreach($image_array_delete as $value) {
				$value_img = json_decode(base64_decode($value));
				if(!empty($value_img)) {
					foreach(json_decode(base64_decode($value)) as $img) {
						if(is_file(FOLDER_IMAGES . $img->file->image))
							unlink(FOLDER_IMAGES . $img->file->image);
					}
				}
			}

			// Copy over the new and old from the working temp folder to the main image folder.
			foreach($image_array_copy as $img)
				copy(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image, FOLDER_IMAGES . $img->file->image);
		}

		// Clear out the temporary image folder.
		php_delete_image_temp();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err->getMessage());
	}
}

// Handles image loading processing for php_html_items_editor and php_load_multi_item_editor.
function php_process_item_images_editor($image_json_data) {
	try {
		// Scan for existing images so we can feed back to the browser to fill into the image uploader drop zone.
		$tmp_array = Array();
		$f_image_data = json_decode(base64_decode($image_json_data));

		if(!empty($f_image_data)) {
			foreach($f_image_data as $key => $img) {
				$obj['name'] = $img->file->image;
				$obj['oldname'] = $img->file->name;
				$obj['size'] = $img->file->size;
				$obj['rand'] = $img->file->rand;

				// The editor image dropzone delete's images from the full path if discarding the edit change. So we need to make a copy of the image into the temp folder and adjust the full path to it to prevent the original from getting deleted if the user discards there changes.
				/*
				set_error_handler(function($errno, $errstr, $errfile, $errline) {
					throw new Exception($errstr . " on line " . $errline . " in file " . $errfile);
				});
				*/
				
				if(!file_exists(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'])) {
					mkdir(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin']);
				}

				copy(FOLDER_IMAGES . $img->file->image, FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image);
				$obj['fullpath'] =  FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image;
				restore_error_handler();

				// Adjust the full path in the $img object as it gets saved into the xhr response, which is used for temporary image deletion.
				$img_tmp = $img;
				$img_tmp->file->tempfullpath = $img_tmp->file->fullpath;
				$img_tmp->file->fullpath = $obj['fullpath'];

				$obj['xhr']['response'] = base64_encode(json_encode($img_tmp));

				$tmp_array[] = $obj;
			}
		}
		return Array("imgzone" => base64_encode(json_encode($tmp_array)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_product_copy() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));
		$c_id = base64_decode($_REQUEST['c_id']);

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		if(isset($_REQUEST['page_num']))
			$f_page_num = $_REQUEST['page_num'];
		else
			$f_page_num = 1;

		if($f_page_num < 1)
			$f_page_num = 1;

		// Generate query to copy the selected products into a temporary table to operate on.
		$cat_refresh[$c_id] = $c_id; // Refresh the new category afterwards.
		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			$cat_refresh[$product->p_catid] = $product->p_catid; // The old categories that get refreshed.

			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";
		$rand = rand(10000, 99999);

		// Copying the products we want to copy into a temp table.
		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " AS SELECT * FROM " . TABLE_PRODUCTS . " " . $where);

		// Select the products we are copying from the temporary table.
		$fluid->php_db_query("SELECT p_id, " . $fluid_mode->p_catmfg_id . ", " . $fluid_mode->p_catmfg_id_opp . ", p_images FROM temp_table_" . $rand);

		// Build a update query for the temporary table.
		$case['p_images'] = " CASE ";
		$case[$fluid_mode->p_catmfg_id] = " CASE ";
		$where = "WHERE p_id IN (";
		$img_copy_array = Array();
		$sort_array = Array();
		$i = 0;
		foreach($fluid->db_array as $value) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($value['p_id']);

			// Generate new names for the images.
			$tmp_array = Array();
			foreach(json_decode(base64_decode($value['p_images'])) as $key => $img) {
				// Generate a new random key name identifier for a image.
				$rand_name = substr(str_shuffle(md5(time())),0,30);

				// Replace all instances of the key with the new random key identifier for the images.
				$tmp_array[$rand_name] = json_decode(str_replace($key, $rand_name, json_encode($img)));

				// Keep a copy of the name of the new images in the img (object), we will process them after all the queries execute properly.
				$img->file->{"new_image_key"} = $rand_name;
				$img_copy_array[] = base64_encode(json_encode($img));
			}

			$case['p_images'] .= "\n";
			$case['p_images'] .= "WHEN (`p_id`, `p_images`) = ('" . $fluid->php_escape_string($value['p_id']) . "', '" . $fluid->php_escape_string($value['p_images']) . "') THEN '" . base64_encode(json_encode($tmp_array)) . "'";

			$case[$fluid_mode->p_catmfg_id] .= "\n";
			$case[$fluid_mode->p_catmfg_id] .= "WHEN (`p_id`, `" . $fluid_mode->p_catmfg_id . "`) = ('" . $fluid->php_escape_string($value['p_id']) . "', '" . $fluid->php_escape_string($value[$fluid_mode->p_catmfg_id]) . "') THEN '" . $fluid->php_escape_string($c_id) . "'";

			// Record catid and mfgid's that we need to reset the sort orders for the copied items.
			$sort_array[$value['p_id']][$fluid_mode->p_catmfg_id] = $fluid->php_escape_string($c_id);
			$sort_array[$value['p_id']][$fluid_mode->p_catmfg_id_opp] = $value[$fluid_mode->p_catmfg_id_opp];

			$i++;
		}
		$case['p_images'] .= "\nEND";
		$case[$fluid_mode->p_catmfg_id] .= "\nEND";
		$where .= ")";

		// Update the temporary table. Make sure to set the p_id to NULL.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET `p_images` = " . $case['p_images'] . ", `" . $fluid_mode->p_catmfg_id . "` = " . $case[$fluid_mode->p_catmfg_id] . " " . $where);

		$sort_data = Array();
		$sort_data['p_catid'] = Array();
		$sort_data['p_mfgid'] = Array();
		// Now set the sort orders for each copied product. A bit slower but no choice :(
		foreach($sort_array as $key => $value_data) {
			if(!isset($sort_data['p_catid'][$value_data['p_catid']])) {
				// Get the p_sortorder
				$fluid->php_db_query("SELECT p.p_sortorder AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_catid=" . $value_data['p_catid'] . " ORDER BY p.p_sortorder DESC LIMIT 1");
				if(isset($fluid->db_array[0]['tmp_c_product_count']))
					$sort_data['p_catid'][$value_data['p_catid']] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
				else
					$sort_data['p_catid'][$value_data['p_catid']] = 1;
			}
			else
				$sort_data['p_catid'][$value_data['p_catid']]++;


			if(!isset($sort_data['p_mfgid'][$value['p_mfgid']])) {
				// Get the p_sortorder_mfg
				$fluid->php_db_query("SELECT p.p_sortorder_mfg AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_mfgid=" . $value_data['p_mfgid'] . " ORDER BY p.p_sortorder_mfg DESC LIMIT 1");

				if(isset($fluid->db_array[0]['tmp_c_product_count_mfg']))
					$sort_data['p_mfgid'][$value_data['p_mfgid']] = $fluid->db_array[0]['tmp_c_product_count'] + 1;
				else
					$sort_data['p_mfgid'][$value_data['p_mfgid']] = 1;
			}
			else
				$sort_data['p_mfgid'][$value_data['p_mfgid']]++;

			// The filters get reset on copied items.
			$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET `p_sortorder` = " . $sort_data['p_catid'][$value_data['p_catid']] . ", `p_sortorder_mfg` = " . $sort_data['p_mfgid'][$value_data['p_mfgid']] . ", `p_c_filters` = NULL, `p_m_filters` = NULL WHERE p_id='" . $key . "'");
		}

		// Update id's to NULL in temp table.
		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " CHANGE `p_id` `p_id` INT( 11 ) NULL DEFAULT NULL");
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET `p_id` = NULL");

		// Insert / merge the temp table into the original.
		$fluid->php_db_query("INSERT INTO " . TABLE_PRODUCTS . " SELECT * FROM temp_table_" . $rand);

		// Drop the temporary table.
		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

		//$execute_functions[0]['function'] = "js_select_clear_p_selection";
		//$execute_functions[0]['data'] = base64_encode(json_encode("p_id_"));

		$iFunc = 0;
		$execute_functions[$iFunc]['function'] = "js_modal_hide";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode("#fluid-modal"));
		$iFunc++;

		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $_REQUEST['data'] . "&page_num=" . $f_page_num . "&mode=" . $mode)));
		$execute_functions[$iFunc]['function'] = "js_fluid_ajax";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode($temp_data));
		$iFunc++;

		$fluid->php_db_commit();

		// Copy the images for the copied product after the queries have executed.
		foreach($img_copy_array as $value) {
			foreach(json_decode(base64_decode($value)) as $key => $img) {
				copy(FOLDER_IMAGES . $img->image, FOLDER_IMAGES . $img->new_image_key . "." . $img->extension);
			}
		}

		// Determine if we need to unselect or keep our items selected after the move.
		if(isset($_REQUEST['f_selection'])) {
			if($_REQUEST['f_selection'] == 0) {
				$execute_functions[$iFunc]['function'] = "js_select_clear_p_selection";
				$execute_functions[$iFunc]['data'] = base64_encode(json_encode("p_id_"));
				$iFunc++;
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_product_create_and_edit() {
	$fluid = new Fluid ();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));

		$mode = $_REQUEST['mode'];

		if(isset($_REQUEST['modefilter']))
			$mode_filter = $_REQUEST['modefilter'];
		else
			$mode_filter = NULL;

		$fluid_mode = new Fluid_Mode($mode_filter);

		if(isset($_REQUEST['page_num']))
			$f_page_num = $_REQUEST['page_num'];
		else
			$f_page_num = 1;

		if($f_page_num < 1)
			$f_page_num = 1;

		// Follow up functions to execute on a server response back to the user.
		$iFunc = 0;
		$execute_functions[$iFunc]['function'] = "js_image_dropzone_destroy";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode(""));
		$iFunc++;

		$execute_functions[$iFunc]['function'] = "js_modal_hide";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode("#fluid-modal"));
		$iFunc++;

		$image_array_delete = Array();
		$prev_data = Array();

		if($mode == "edit") {
			$fluid->php_db_query("SELECT p_catid, p_stock, p_cost, p_cost_real, p_mfgid, p_images FROM " . TABLE_PRODUCTS . " WHERE p_id = '" . $fluid->php_escape_string(base64_decode($data->p_id)) . "'");

			// Record the original images, so we can process them after the queries.
			if(isset($fluid->db_array))
				$image_array_delete[] = $fluid->db_array[0]['p_images'];

			// Record the previous category so we can refresh it.
			$cat_refresh[$fluid->db_array[0][$fluid_mode->p_catmfg_id]] = $fluid->db_array[0][$fluid_mode->p_catmfg_id];

			// Keep track of the previous p_catid and p_mfgid in case the item is moved to another category or manufacturer, then we need to resort the p_sortorder or p_sortorder_mfg of all items in the responding previous category or manufacturer.
			$prev_data['p_catid'] = $fluid->db_array[0]['p_catid'];
			$prev_data['p_mfgid'] = $fluid->db_array[0]['p_mfgid'];
			$prev_data['old_cost'] = $fluid->db_array[0]['p_cost_real'];
			$prev_data['old_stock'] = $fluid->db_array[0]['p_stock'];
			$prev_data['old_cost_avg'] = $fluid->db_array[0]['p_cost'];
		}

		// Re-arrange the picture order based on the queue list order.
		$tmporder = Array();
		$image_array_copy = Array();

		foreach($data->p_imageorder as $order) {
			foreach($data->p_images as $image) {
				$image->file->fullpath = FOLDER_IMAGES . $image->file->image;

				// The temp path data is not required. Remove it from the object.
				unset($image->file->tempfullpath);

				if($order->name == $image->file->name && $order->size == $image->file->size) {
					// Record the new images only, so we can process them after the queries.
					$image_array_copy[$image->file->rand] = $image;

					// Prepare a new image list for the query.
					$tmporder{$image->file->rand} = $image;
				}
			}
		}
		$data->p_images = $tmporder;

		// Process some of the variables which could break the sql queries if they are blank.
		$p_stock = !empty($data->p_stock) ? "'" . $fluid->php_escape_string(base64_decode($data->p_stock)) . "'" : "NULL";
		$p_price = !empty($data->p_price) ? "'" . $fluid->php_escape_string(base64_decode($data->p_price)) . "'" : "NULL";

		$p_cost_real = !empty($data->p_cost_real) ? "'" . $fluid->php_escape_string(base64_decode($data->p_cost_real)) . "'" : "NULL";
		$p_cost = !empty($data->p_cost) ? "'" . $fluid->php_escape_string(base64_decode($data->p_cost)) . "'" : "NULL";

		$p_buyqty = !empty($data->p_buyqty) ? "'" . $fluid->php_escape_string(base64_decode($data->p_buyqty)) . "'" : "'1'";

		if($mode == "edit") {
			$p_cost_average = $fluid->php_calculate_cost_average($prev_data, Array("new_cost" => base64_decode($data->p_cost_real), "new_stock" => base64_decode($data->p_stock)));
			$p_cost = !empty($p_cost_average) ? "'" . $fluid->php_escape_string($p_cost_average) . "'" : "NULL";
		}
		else
			$p_cost = $p_cost_real;

		$p_trending = !empty($data->p_trending) ? "'" . $fluid->php_escape_string(base64_decode($data->p_trending)) . "'" : "NULL";
		$p_instore = !empty($data->p_instore) ? "'" . $fluid->php_escape_string(base64_decode($data->p_instore)) . "'" : "'0'";
		$p_arrivaltype = !empty($data->p_arrivaltype) ? "'" . $fluid->php_escape_string(base64_decode($data->p_arrivaltype)) . "'" : "'0'";
		$p_freeship = !empty($data->p_freeship) ? "'" . $fluid->php_escape_string(base64_decode($data->p_freeship)) . "'" : "'1'";
		$p_preorder = !empty($data->p_preorder) ? "'" . $fluid->php_escape_string(base64_decode($data->p_preorder)) . "'" : "NULL";
		$p_rental = !empty($data->p_rental) ? "'" . $fluid->php_escape_string(base64_decode($data->p_rental)) . "'" : "'0'";
		$p_special_order = !empty($data->p_special_order) ? "'" . $fluid->php_escape_string(base64_decode($data->p_special_order)) . "'" : "'0'";

		$p_namenum = !empty($data->p_namenum) ? "'" . $fluid->php_escape_string(base64_decode($data->p_namenum)) . "'" : "'0'";
		$p_showalways = !empty($data->p_showalways) ? "'" . $fluid->php_escape_string(base64_decode($data->p_showalways)) . "'" : "NULL";

		$p_rebate_claim = !empty($data->p_rebate_claim) ? "'" . $fluid->php_escape_string(base64_decode($data->p_rebate_claim)) . "'" : "NULL";
		$p_component = !empty($data->p_component) ? "'" . $fluid->php_escape_string(base64_decode($data->p_component)) . "'" : "NULL";
		$p_stock_end = !empty($data->p_stock_end) ? "'" . $fluid->php_escape_string(base64_decode($data->p_stock_end)) . "'" : "NULL";

		$p_price_discount = !empty($data->p_price_discount) ? "'" . $fluid->php_escape_string(base64_decode($data->p_price_discount)) . "'" : "NULL";

		$p_discount_date_end = !empty($data->p_discount_date_end) ? "'" . $fluid->php_escape_string(base64_decode($data->p_discount_date_end)) . "'" : "NULL";
		$p_discount_date_start = !empty($data->p_discount_date_start) ? "'" . $fluid->php_escape_string(base64_decode($data->p_discount_date_start)) . "'" : "NULL";

		$product_arrival_end_date = !empty($data->p_newarrivalenddate) ? "'" . $fluid->php_escape_string(base64_decode($data->p_newarrivalenddate)) . "'" : "NULL";
		$p_mfg_number = !empty($data->p_mfg_number) ? "'" . $fluid->php_escape_string(base64_decode($data->p_mfg_number)) . "'" : "NULL";

		// Process the category filters.
		$p_c_filters_tmp = NULL;
		foreach(json_decode(base64_decode($data->p_c_filters)) as $key_f => $c_filter_tmp) {
			$c_tmp = json_decode($c_filter_tmp);
			$p_c_filters_tmp[$c_tmp->sub_id] = $c_tmp;
		}

		$p_c_filters_tmp = (object)$p_c_filters_tmp;
		$p_c_filters = !empty((array)$p_c_filters_tmp) ? "'" . $fluid->php_escape_string(json_encode($p_c_filters_tmp)) . "'" : "NULL";

		// Process the manufacturer filters.
		$p_m_filters_tmp = NULL;
		foreach(json_decode(base64_decode($data->p_m_filters)) as $key_f => $m_filter_tmp) {
			$m_tmp = json_decode($m_filter_tmp);
			$p_m_filters_tmp[$m_tmp->sub_id] = $m_tmp;
		}

		$p_m_filters_tmp = (object)$p_m_filters_tmp;
		$p_m_filters = !empty((array)$p_m_filters_tmp) ? "'" . $fluid->php_escape_string(json_encode($p_m_filters_tmp)) . "'" : "NULL";

		// Which categories / manufacturer needs to be refreshed.
		if($fluid_mode->mode == "manufacturers")
			$cat_refresh[$fluid->php_escape_string(base64_decode($data->p_manufacturer))] = $fluid->php_escape_string(base64_decode($data->p_manufacturer));
		else
			$cat_refresh[$fluid->php_escape_string(base64_decode($data->p_category))] = $fluid->php_escape_string(base64_decode($data->p_category));

		if($mode == "add") {
			// Get the p_sortorder
			$fluid->php_db_query("SELECT p.p_sortorder AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_catid=" . $fluid->php_escape_string(base64_decode($data->p_category)) . " ORDER BY p.p_sortorder DESC LIMIT 1");

			if(isset($fluid->db_array[0]['tmp_c_product_count']))
				$sort_order = $fluid->db_array[0]['tmp_c_product_count'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.
			else
				$sort_order = 1;

			// Get the p_sortorder_mfg
			$fluid->php_db_query("SELECT p.p_sortorder_mfg AS tmp_c_product_count_mfg FROM " . TABLE_PRODUCTS . " p WHERE p.p_mfgid=" . $fluid->php_escape_string(base64_decode($data->p_manufacturer)) . " ORDER BY p.p_sortorder_mfg DESC LIMIT 1");

			if(isset($fluid->db_array[0]['tmp_c_product_count_mfg']))
				$sort_order_mfg = $fluid->db_array[0]['tmp_c_product_count_mfg'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.
			else
				$sort_order_mfg = 1;

			$fluid->php_db_query("INSERT INTO " . TABLE_PRODUCTS . " (p_enable, p_cost, p_cost_real, p_instore, p_arrivaltype, p_freeship, p_trending, p_preorder, p_special_order, p_rental, p_namenum, p_showalways, p_rebate_claim, p_component, p_stock_end, p_c_filters, p_m_filters, p_sortorder_mfg, p_sortorder, p_newarrivalenddate, p_mfgid, p_mfgcode, p_mfg_number, p_catid, p_name, p_desc, p_details, p_specs, p_inthebox, p_seo, p_keywords, p_ratingdata, p_rating, p_price, p_price_discount, p_discount_date_start, p_discount_date_end, p_stock, p_buyqty, p_images, p_length, p_width, p_height, p_weight, p_formula_operation, p_formula_math, p_formula_item_html, p_formula_items_data, p_formula_item_faux_html, p_formula_items_faux_data, p_formula_application, p_formula_message, p_date_hide, p_category_items_data) VALUES ('" . $fluid->php_escape_string(base64_decode($data->p_status)) . "', " . $p_cost . ", " . $p_cost_real . ", " . $p_instore . ", " . $p_arrivaltype . ", " . $p_freeship . ", " . $p_trending . ", " . $p_preorder . ", " . $p_special_order . ", " . $p_rental . ", " . $p_namenum . ", " . $p_showalways . ", " . $p_rebate_claim . ", " . $p_component . ", " . $p_stock_end . ", " . $p_c_filters . ", " . $p_m_filters . ", '" . $sort_order_mfg . "', '" . $sort_order . "', " . $product_arrival_end_date . ", '" . $fluid->php_escape_string(base64_decode($data->p_manufacturer)) . "', '" . $fluid->php_escape_string(base64_decode($data->p_barcode)) . "', " . $p_mfg_number . ", '" . $fluid->php_escape_string(base64_decode($data->p_category)) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_name))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_description))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_details))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_specs))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_inthebox))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_seo))) . "', '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_keywords))) . "', '0;0;0;0;0', NULL, " . $p_price . ", " . $p_price_discount . ", " . $p_discount_date_start . ", " . $p_discount_date_end . ", " . $p_stock . ", " . $p_buyqty . ", '" . $fluid->php_escape_string(base64_encode(json_encode($data->p_images))) . "', '" . $fluid->php_escape_string(base64_decode($data->p_length)) . "', '" . $fluid->php_escape_string(base64_decode($data->p_width)) . "', '" . $fluid->php_escape_string(base64_decode($data->p_height)) . "', '" . $fluid->php_escape_string(base64_decode($data->p_weight)) . "', '', '', '', '', '', '', '', '', NULL, '')");
		}
		else if($mode == "edit") {
			/*
				// ************************************
					--> NOTE: This edit mode on a single item is depreciated and not used. All editing is done in the multi item editor. If you try to enable this again, you need to fix the edit columns as many new columns have been added to the database since this was last used.
				// ************************************
			*/
			// Need to count the p_sortorder and p_sortorder_mfg and adjust them and move to the end if the item is moved into a different category or different manufacturer.
			$p_sortorder_query = "";
			if(base64_decode($data->p_category) != $prev_data['p_catid']) {
				$fluid->php_db_query("SELECT p.p_sortorder AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p WHERE p.p_catid=" . $fluid->php_escape_string(base64_decode($data->p_category)) . " ORDER BY p.p_sortorder DESC LIMIT 1");

				if(isset($fluid->db_array[0]['tmp_c_product_count']))
					$sort_order = $fluid->db_array[0]['tmp_c_product_count'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.
				else
					$sort_order = 1;

				$p_sortorder_query = ", `p_sortorder` = '" . $sort_order . "'";
			}

			$p_sortorder_mfg_query = "";
			if(base64_decode($data->p_manufacturer) != $prev_data['p_mfgid']) {
				// Get the p_sortorder_mfg
				$fluid->php_db_query("SELECT p.p_sortorder_mfg AS tmp_c_product_count_mfg FROM " . TABLE_PRODUCTS . " p WHERE p.p_mfgid=" . $fluid->php_escape_string(base64_decode($data->p_manufacturer)) . " ORDER BY p.p_sortorder_mfg DESC LIMIT 1");

				if(isset($fluid->db_array[0]['tmp_c_product_count_mfg']))
					$sort_order_mfg = $fluid->db_array[0]['tmp_c_product_count_mfg'] + 1; // Since we do not use 0 in sort order, we must add 1 to the sort_order count.
				else
					$sort_order_mfg = 1;

				$p_sortorder_mfg_query = ", `p_sortorder_mfg` = '" . $sort_order_mfg . "'";
			}

			// The update query.
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_enable` = '" . $fluid->php_escape_string(base64_decode($data->p_status)) . "', `p_m_filters` = " . $p_m_filters . ", `p_c_filters` = " . $p_c_filters . ", `p_newarrivalenddate` = " . $product_arrival_end_date . ", `p_mfgid` = '" . $fluid->php_escape_string(base64_decode($data->p_manufacturer)) . "', `p_mfg_number` = " . $p_mfg_number . ", `p_mfgcode` = '" . $fluid->php_escape_string(base64_decode($data->p_barcode)) . "', `p_catid` = '" . $fluid->php_escape_string(base64_decode($data->p_category)) . "', `p_name` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_name))) . "', `p_desc` = '" . $fluid->php_escape_string(base64_decode($data->p_description)) . "', `p_details` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_details))) . "', `p_specs` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_specs))) . "', `p_inthebox` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_inthebox))) . "', `p_seo` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_seo))) . "', `p_keywords` = '" . $fluid->php_escape_string(utf8_decode(base64_decode($data->p_keywords))) . "', `p_price` = " . $p_price . ", `p_cost_real` = " . $p_cost_real . ", `p_cost` = " . $p_cost . ", `p_price_discount` = " . $p_price_discount . ", `p_discount_date_start` = " . $p_discount_date_start . ", `p_discount_date_end` = " . $p_discount_date_end . ", `p_stock` = " . $p_stock . ", `p_buyqty` = '" . $fluid->php_escape_string(base64_decode($data->p_buyqty)) . "', `p_length` = '" . $fluid->php_escape_string(base64_decode($data->p_length)) . "', `p_width` = '" . $fluid->php_escape_string(base64_decode($data->p_width)) . "', `p_height` = '" . $fluid->php_escape_string(base64_decode($data->p_height)) . "', `p_weight` = '" . $fluid->php_escape_string(base64_decode($data->p_weight)) . "', `p_freeship` = " . $p_freeship . ", `p_instore` = " . $p_instore . ", `p_arrivaltype` = " . $p_arrivaltype . ", `p_trending` = " . $p_trending . ", `p_special_order` = " . $p_special_order . ",  `p_rental` = " . $p_rental . ", `p_preorder` = " . $p_preorder . ", `p_namenum` = " . $p_namenum . ", `p_showalways` = " . $p_showalways . ", `p_rebate_claim` = " . $p_rebate_claim . ", `p_component` = " . $p_component . ", `p_stock_end` = " . $p_stock_end . ", `p_images` = '" . $fluid->php_escape_string(base64_encode(json_encode($data->p_images))) . "'" . $p_sortorder_query . $p_sortorder_mfg_query . " WHERE p_id = '" . $fluid->php_escape_string(base64_decode($data->p_id)) . "'");

			// Rebuild the p_sortorder of the previous category it belonged to.
			if(base64_decode($data->p_category) != $prev_data['p_catid']) {
				$rand = rand(10000, 99999);

				$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_catid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

				$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder INT auto_increment primary key NOT NULL;");

				// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
				$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_catid) SELECT p_id, p_catid FROM " . TABLE_PRODUCTS . " WHERE p_catid = '" . $prev_data['p_catid'] . "' ORDER BY p_sortorder ASC");

				// Merge data from temp_table_sort into temp_table_rand via p_id.
				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder = src.p_sortorder WHERE dest.p_id = src.p_id");

				// Drop the temporary table.
				$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
			}

			// Rebuild the p_sortorder_mfg of the previous manufacturer it belonged to.
			if(base64_decode($data->p_manufacturer) != $prev_data['p_mfgid']) {
				$rand = rand(10000, 99999);

				$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_mfgid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

				$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder_mfg INT auto_increment primary key NOT NULL;");

				// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
				$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_mfgid) SELECT p_id, p_mfgid FROM " . TABLE_PRODUCTS . " WHERE p_mfgid = '" . $prev_data['p_mfgid'] . "' ORDER BY p_sortorder_mfg ASC");

				// Merge data from temp_table_sort into temp_table_rand via p_id.
				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder_mfg = src.p_sortorder_mfg WHERE dest.p_id = src.p_id");

				// Drop the temporary table.
				$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
			}

			$selection = $_REQUEST['selection']; // The list of items we have selected for multi item editing.
			$selection_tmp = $fluid->php_object_to_array(json_decode(base64_decode($selection)));

			// This always runs at the moment. Could save some bandwidth if this only runs if there was a manufacturer or category change while in those modes.
			if(count($selection_tmp) > 0 && $fluid_mode->mode != "items" && empty($_REQUEST['scan'])) {
				// Need to update the data in the selection of items so we can feed it back to the client.
				$selection_tmp[base64_decode($data->p_id)]['p_enable'] = base64_decode($data->p_status);

				if($fluid_mode->mode != "manufacturers")
					$selection_tmp[base64_decode($data->p_id)]['p_catid'] = base64_decode($data->p_category);
				else if($fluid_mode->mode == "manufacturers")
					$selection_tmp[base64_decode($data->p_id)]['p_catid'] = base64_decode($data->p_manufacturer);

				$selection = base64_encode(json_encode($selection_tmp));

				$selection_data = json_decode(base64_decode($selection));
				$c_selection = NULL;

				// Create a new c_selection category selection item count that gets sent back to the client to update FluidVariables.v_selection.c_selection.
				foreach($selection_data as $key => $tmp_data)
					if(isset($c_selection[$tmp_data->p_catid]))
						$c_selection[$tmp_data->p_catid] = $c_selection[$tmp_data->p_catid] + 1;
					else
						$c_selection[$tmp_data->p_catid] = 1;

				$data_return = Array();
				//$data_return['cat_refresh'] = $cat_refresh;
				$data_return['selection'] = $selection;
				$data_return['c_selection'] = base64_encode(json_encode($c_selection));

				$execute_functions[$iFunc]['function'] = "js_refresh_category";
				$execute_functions[$iFunc]['data'] = base64_encode(json_encode($data_return));
				$iFunc++;
			}
		}

		// If the item edit happened in the import editor, then lets rescan the items to reload them.
		if(isset($_REQUEST['scan'])) {
			$execute_functions[]['function'] = "js_fluid_scan_items";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));
		}
		else {
			// Refresh the category products.
			$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $_REQUEST['selection'] . "&page_num=" . $f_page_num . "&mode=" . $mode_filter)));
			$execute_functions[$iFunc]['function'] = "js_fluid_ajax";
			$execute_functions[$iFunc]['data'] = base64_encode(json_encode($temp_data));
			$iFunc++;
		}

		// Commit the MySQL database changes.
		$fluid->php_db_commit();

		// Process the images after the queries are successful.
		// Delete the original images.
		foreach($image_array_delete as $value)
			foreach(json_decode(base64_decode($value)) as $img)
				unlink(FOLDER_IMAGES . $img->file->image);

		// Copy over the new and old from the working temp folder to the main image folder.
		foreach($image_array_copy as $img)
			copy(FOLDER_IMAGES_TEMP . $_SESSION['fluid_admin'] . "/" . $img->file->image, FOLDER_IMAGES . $img->file->image);

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err->getMessage());
	}
}

function php_product_delete() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		if(isset($_REQUEST['page_num']))
			$f_page_num = $_REQUEST['page_num'];
		else
			$f_page_num = 1;

		if($f_page_num < 1)
			$f_page_num = 1;

		// $cat_refresh is sent back to fluid.js for refresh the displayed listings. Category or Manufacturer depending on which is being viewed.
		$cat_refresh = Array();
		$where = "WHERE p_id IN (";
		$where_p_c_l = "WHERE l_p_id IN (";
		$where_component = "WHERE cp_master_id IN (";
		$where_component_id = "WHERE cp_p_id IN (";

		$i = 0;
		foreach($data as $product) {
			$cat_refresh[$product->p_catid] = $product->p_catid;

			if($i != 0) {
				$where .= ", ";
				$where_p_c_l .= ",";
				$where_component .= ",";
				$where_component_id .= ",";
			}

			$where .= $fluid->php_escape_string($product->p_id);
			$where_p_c_l .= $fluid->php_escape_string($product->p_id);
			$where_component .= $fluid->php_escape_string($product->p_id);
			$where_component_id .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";
		$where_p_c_l .= ")";
		$where_component .= ")";
		$where_component_id .= ")";

		// Select the products and process images for deletion.
		$fluid->php_db_query("SELECT p_id, p_catid, p_mfgid, p_images FROM " . TABLE_PRODUCTS . " " . $where);

		$catid_array = Array();
		$mfgid_array = Array();
		$delete_array = Array();
		foreach($fluid->db_array as $value) {
			$catid_array[$value['p_catid']] = $value['p_catid']; // Record which categories sort orders to resort.
			$mfgid_array[$value['p_catid']] = $value['p_mfgid']; // Record which manufacturer sort orders to resort.
			$delete_array[] = $value['p_images']; // For later deletion of the images if the queries dont fail.
		}

		// Delete the selected items.
		$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCTS . " " . $where);

		// Delete the select items product category linking references.
		$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_CATEGORY_LINKING . " " . $where_p_c_l);

		// Delete the components.
		$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_COMPONENT . " " . $where_component);
		$fluid->php_db_query("DELETE FROM " . TABLE_PRODUCT_COMPONENT . " " . $where_component_id);

		// Rebuild the p_sortorder in each affected category.
		foreach($catid_array as $cat_temp) {
			$rand = rand(10000, 99999);

			$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_catid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder INT auto_increment primary key NOT NULL;");

			// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
			$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_catid) SELECT p_id, p_catid FROM " . TABLE_PRODUCTS . " WHERE p_catid = '" . $fluid->php_escape_string($cat_temp) . "' ORDER BY p_sortorder ASC");

			// Merge data from temp_table_sort into temp_table_rand via p_id.
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder = src.p_sortorder, dest.p_catid = '" .$fluid->php_escape_string($cat_temp) . "' WHERE dest.p_id = src.p_id");

			// Drop the temporary table.
			$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
		}

		// Rebuild the p_sortorder_mfg in each affected manufacturer.
		foreach($mfgid_array as $cat_temp) {
			$rand = rand(10000, 99999);

			$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`p_mfgid` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder_mfg INT auto_increment primary key NOT NULL;");

			// Copy data into the table which then gives them a new p_sortorder_mfg at the end of the table with auto increment.
			$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, p_mfgid) SELECT p_id, p_mfgid FROM " . TABLE_PRODUCTS . " WHERE p_mfgid = '" . $fluid->php_escape_string($cat_temp) . "' ORDER BY p_sortorder_mfg ASC");

			// Merge data from temp_table_sort into temp_table_rand via p_id.
			$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder_mfg = src.p_sortorder_mfg, dest.p_mfgid = '" .$fluid->php_escape_string($cat_temp) . "' WHERE dest.p_id = src.p_id");

			// Drop the temporary table.
			$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
		}

		$execute_functions[0]['function'] = "js_select_clear_p_selection";
		$execute_functions[0]['data'] = base64_encode(json_encode("p_id_"));
		$execute_functions[1]['function'] = "js_modal_hide";
		$execute_functions[1]['data'] = base64_encode(json_encode("#fluid-modal"));

		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&page_num=" . $f_page_num . "&mode=" . $mode)));
		$execute_functions[2]['function'] = "js_fluid_ajax";
		$execute_functions[2]['data'] = base64_encode(json_encode($temp_data));

		$fluid->php_db_commit();

		// If no errors on the queries, time to delete the images.
		foreach($delete_array as $value) {
			$value_img = json_decode(base64_decode($value));
			if(!empty($value_img)) {
				// Delete the images off the server.
				foreach(json_decode(base64_decode($value)) as $key => $img) {
					unlink(FOLDER_IMAGES . $img->file->image);
				}
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_product_move() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		$data = json_decode(base64_decode($_REQUEST['data']));
		$selection_tmp = $fluid->php_object_to_array(json_decode(base64_decode($_REQUEST['data']))); // Need to convert the item selection to a array for re-processing the selection in javascript.

		$c_id = base64_decode($_REQUEST['c_id']);

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$fluid_mode = new Fluid_Mode($mode);

		if(isset($_REQUEST['page_num']))
			$f_page_num = $_REQUEST['page_num'];
		else
			$f_page_num = 1;

		if($f_page_num < 1)
			$f_page_num = 1;

		// Generate query to move the selected products into another category or manufacturer.
		$cat_refresh[$c_id] = $c_id; // Refresh the new category afterwards.
		$case = " CASE ";
		$where = "WHERE p_id IN (";
		$i = 0;

		foreach($data as $product) {
			$cat_refresh[$product->p_catid] = $product->p_catid; // The old categories that get refreshed.
			$selection_tmp[$product->p_id]['p_catid'] = $c_id; // For updating the FluidVariables selection with the new categories.

			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$case .= "\n";

			$case .= "WHEN (`p_id`, `" . $fluid_mode->p_catmfg_id . "`) = ('" . $fluid->php_escape_string($product->p_id) . "', '" . $fluid->php_escape_string($product->p_catid) . "') THEN '" . $fluid->php_escape_string($c_id) . "'";

			$i++;
		}
		$case .= "\nEND";
		$where .= ")";

		$rand = rand(10000, 99999);
		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " AS SELECT p_id, " . $fluid_mode->p_catmfg_id . ", p_sortorder" . $fluid_mode->sort_order . ", p_c_filters, p_m_filters FROM " . TABLE_PRODUCTS . " WHERE " . $fluid_mode->p_catmfg_id . " = '" . $fluid->php_escape_string($c_id) . "'");

		// Set auto increment temporary to accept 0 as a value, else queries fail as we do not use 0 in the sortorder.
		$fluid->php_db_query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
		$fluid->php_db_query("ALTER TABLE temp_table_" . $rand . " MODIFY COLUMN p_sortorder" . $fluid_mode->sort_order . " INT auto_increment primary key NOT NULL;");

		// Copy the new data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
		$fluid->php_db_query("INSERT INTO temp_table_" . $rand . " (p_id, " . $fluid_mode->p_catmfg_id . ") SELECT p_id, " . $fluid_mode->p_catmfg_id . " FROM " . TABLE_PRODUCTS . " " . $where);

		// Reset the p_c_filters and p_m_filters on the items we are moving to NULL as required.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET " . $fluid_mode->prodfilters . " = NULL " . $where);

		// Then update the p_catid and update the main table with the temp table data.
		// Filters for items that get moved get reset to NULL.
		$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_" . $rand . ") src SET dest.p_sortorder" . $fluid_mode->sort_order . " = src.p_sortorder" . $fluid_mode->sort_order . ", dest." . $fluid_mode->p_catmfg_id . " = '" .$fluid->php_escape_string($c_id) . "', dest.p_c_filters = src.p_c_filters, dest.p_m_filters = src.p_m_filters WHERE dest.p_id = src.p_id");

		// Drop the temporary table.
		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

		// Now reset the p_sortorder in the original categories that require it.
		foreach($cat_refresh as $cat_temp) {
			if($cat_temp != $c_id) {
				$rand = rand(10000, 99999);

				$fluid->php_db_query("CREATE TEMPORARY TABLE IF NOT EXISTS `temp_table_sort_" . $rand . "` (`p_id` int(11) NOT NULL,`" . $fluid_mode->p_catmfg_id . "` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

				$fluid->php_db_query("ALTER TABLE temp_table_sort_" . $rand . " ADD p_sortorder" . $fluid_mode->sort_order . " INT auto_increment primary key NOT NULL;");

				// Copy data into the table which then gives them a new p_sortorder at the end of the table with auto increment.
				$fluid->php_db_query("INSERT INTO temp_table_sort_" . $rand . " (p_id, " . $fluid_mode->p_catmfg_id . ") SELECT p_id, " . $fluid_mode->p_catmfg_id . " FROM " . TABLE_PRODUCTS . " WHERE " . $fluid_mode->p_catmfg_id . " = '" . $fluid->php_escape_string($cat_temp) . "' ORDER BY p_sortorder" . $fluid_mode->sort_order . " ASC");

				// Merge data from temp_table_sort into temp_table_rand via p_id.
				$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_sort_" . $rand . ") src SET dest.p_sortorder" . $fluid_mode->sort_order . " = src.p_sortorder" . $fluid_mode->sort_order . ", dest." . $fluid_mode->p_catmfg_id . " = '" .$fluid->php_escape_string($cat_temp) . "' WHERE dest.p_id = src.p_id");

				// Drop the temporary table.
				$fluid->php_db_query("DROP TABLE temp_table_sort_" . $rand);
			}
		}

		//$execute_functions[0]['function'] = "js_select_clear_p_selection";
		//$execute_functions[0]['data'] = base64_encode(json_encode("p_id_"));
		$iFunc = 0;
		$execute_functions[$iFunc]['function'] = "js_modal_hide";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode("#fluid-modal"));
		$iFunc++;

		// Re-encode the item selections.
		$selection = base64_encode(json_encode($selection_tmp));
		if($fluid_mode->mode != "items") {
			$selection_data = json_decode(base64_decode($selection));
			$c_selection = NULL;

			// Create a new c_selection category selection item count that gets sent back to the client to update FluidVariables.v_selection.c_selection.
			foreach($selection_data as $key => $tmp_data)
				if(isset($c_selection[$tmp_data->p_catid]))
					$c_selection[$tmp_data->p_catid] = $c_selection[$tmp_data->p_catid] + 1;
				else
					$c_selection[$tmp_data->p_catid] = 1;

			$data_return = Array();
			$data_return['cat_refresh'] = $cat_refresh;
			$data_return['selection'] = $selection;
			$data_return['c_selection'] = base64_encode(json_encode($c_selection));

			$execute_functions[$iFunc]['function'] = "js_refresh_category";
			$execute_functions[$iFunc]['data'] = base64_encode(json_encode($data_return));
			$iFunc++;
		}

		$temp_data = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_LOADER, "dataobj" => "load=true&function=php_refresh_category_products&data=" . base64_encode(json_encode($cat_refresh)) . "&selection=" . $selection . "&page_num=" . $f_page_num . "&mode=" . $mode)));
		$execute_functions[$iFunc]['function'] = "js_fluid_ajax";
		$execute_functions[$iFunc]['data'] = base64_encode(json_encode($temp_data));
		$iFunc++;

		// Determine if we need to unselect or keep our items selected after the move.
		if(isset($_REQUEST['f_selection'])) {
			if($_REQUEST['f_selection'] == 0) {
				$execute_functions[$iFunc]['function'] = "js_select_clear_p_selection";
				$execute_functions[$iFunc]['data'] = base64_encode(json_encode("p_id_"));
				$iFunc++;
			}
		}

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_refresh_category_products() {
	$fluid = new Fluid ();

	try {
		$data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		if($mode == "manufacturers") {
			$p_catmfg_id = "p.p_mfgid";
			$cp_id = "m_id";
		}
		else {
			$p_catmfg_id = "p.p_catid";
			$cp_id = "c_id";
		}

		if(isset($_REQUEST['f_search_data'])) {
			$f_search_data = base64_decode($_REQUEST['f_search_data']);
		}

		// --> We are in item mode and on a page listing with search results, lets redo the search and to this page again.
		if($mode == "items" && isset($f_search_data)) {
			if(isset($_REQUEST['page_num'])) {
				$f_page_num = $_REQUEST['page_num'];
			}
			else {
				$f_page_num = 1;
			}

			$execute_functions[0]['function'] = "js_pagination_search";
			$execute_functions[0]['data'] = base64_encode(json_encode($f_page_num));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else if($mode == "items") {
			if(isset($_REQUEST['page_num'])) {
				$f_page_num = $_REQUEST['page_num'];
			}
			else {
				$f_page_num = 1;
			}

			$execute_functions[0]['function'] = "js_fluid_item_filters_pagination_items";
			$execute_functions[0]['data'] = base64_encode(json_encode($f_page_num));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else {
			$fluid->php_db_begin();

			$fluid_mode = new Fluid_Mode($mode);

			// Build the query and also preload the return data.
			$data_return = Array();
			$sort_return = Array();
			$tmp_array = Array();
			$where = "WHERE p." . $fluid_mode->p_catmfg_id . " IN (";
			$i = 0;

			foreach($data as $c_id) {
				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($c_id);

				// No need to return back category / manufacturer listing id's while in item mode.
				if($mode != "items") {
					$data_return['categories'][$c_id]['c_id'] = $c_id;
					$data_return['categories'][$c_id]['product_count'] = 0;
					$tmp_array[$c_id] = NULL;
				}

				$i++;
			}
			$where .= ")";

			// In item mode, we order by ASC, so we select all products as we dump all into the table for viewing.
			if($mode == "items") {
				if(isset($_REQUEST['page_num']))
					$f_start = ($_REQUEST['page_num'] - 1) * FLUID_ADMIN_LISTING_LIMIT;
				else
					$f_start = 0;

				$count = "(SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p)";
				$order_by = "p.p_id ASC";
				$where = NULL;
				$where_count = NULL;
				$limit = " LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;
			}
			else {
				$order_by = "p." . $fluid_mode->p_catmfg_id . " ASC, p.p_sortorder" . $fluid_mode->sort_order . " ASC";
				$where_count = " WHERE p." . $fluid_mode->p_catmfg_id . "=" . $fluid_mode->X_id;
				$limit = NULL;
			}

			$fluid->php_db_query("SELECT c.*, p.*, m.*, (SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " p" . $where_count . ") AS tmp_c_product_count FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p.p_mfgid = m.m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c.c_id " . $where . " ORDER BY " . $order_by . $limit);

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $value) {
					if($mode != "items") {
						$key = $value[$fluid_mode->id];
					}
					else {
						$key = $fluid_mode->mode;
					}

					$data_return['categories'][$key]['c_id'] = $key;
					$data_return['categories'][$key]['product_count'] = $value['tmp_c_product_count'];
					$sort_return['categories'][$key]['div'] = base64_encode("#cat-" . $key);

					// Return the mode, to help js_refresh_category_products to apply changes to the correct .innerHTML's.
					$data_return['categories'][$key]['mode'] = base64_encode($fluid_mode->mode);

					$tmp_array[$key][] = $value;
				}
			}

			// Get the formatted html for the product listings.
			foreach($tmp_array as $key => $value) {
				if(isset($_REQUEST['selection'])) {
					$selection_data = $_REQUEST['selection'];
				}
				else {
					$selection_data = NULL;
				}

				$data_return['products'][$key] = php_html_items($value, $selection_data, $mode);
			}

			$execute_functions[0]['function'] = "js_refresh_category_products";
			$execute_functions[0]['data'] = base64_encode(json_encode($data_return));

			if($fluid_mode->mode != "items") {
				$execute_functions[1]['function'] = "js_sortable_products";
				$execute_functions[1]['data'] = base64_encode(json_encode($sort_return));
			}

			$fluid->php_db_commit();

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

// Perform a search.
function php_search() {
	$fluid = new Fluid ();

	try {
		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		if($mode == "orders") {
			$fluid->php_db_begin();

			$query = NULL;
			$query = trim(base64_decode($_REQUEST['search_input']));

			if (mb_strlen($query)===0){
				// no need for empty search right?
				//return false;
			}

			$query = $fluid->php_limit_chars($query);

			// Weighing scores
			$scoreFullTitle = 6;
			$scoreTitleKeyword = 5;
			$scoreFullSummary = 5;
			$scoreSummaryKeyword = 4;
			$scoreFullDocument = 4;
			$scorePhoneKeyword = 4;
			$scoreDocumentKeyword = 3;
			$scoreCategoryKeyword = 2;
			$scoreUrlKeyword = 1;

			$keywords = $fluid->php_filter_search_keys($query);
			$escQuery = $fluid->php_escape_string($query);
			$titleSQL = array();
			$sumSQL = array();
			$docSQL = array();
			//$categorySQL = array();
			$phoneSQL = array();
			$urlSQL = array();
			$keywordsSQL = array();

			// Matching full occurences
			if (count($keywords) > 1){
				$titleSQL[] = "if (s_order_number LIKE '%".$escQuery."%',{$scoreFullTitle},0)";
				$sumSQL[] = "if (s_address_name LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
				$docSQL[] = "if (s_u_email LIKE '%".$escQuery."%',{$scoreFullDocument},0)";
				$phoneSQL[] = "if (s_address_phonenumber LIKE '%".$escQuery."%',{$scorePhoneKeyword},0)";
				$keywordsSQL[] = "if (s_address_street LIKE '%".$escQuery."%',{$scoreTitleKeyword},0)";
			}

			// Matching Keywords
			foreach($keywords as $key){
				$titleSQL[] = "if (s_order_number LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreTitleKeyword},0)";
				$sumSQL[] = "if (s_address_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreSummaryKeyword},0)";
				$docSQL[] = "if (s_u_email LIKE '%". $fluid->php_escape_string($key)."%',{$scoreDocumentKeyword},0)";
				$phoneSQL[] = "if (s_address_phonenumber LIKE '%". $fluid->php_escape_string($key)."%',{$scorePhoneKeyword},0)";

				$urlSQL[] = "if (s_address_city LIKE '%". $fluid->php_escape_string($key)."%',{$scoreUrlKeyword},0)";
				$keywordsSQL[] = "if (s_address_street LIKE '%". $fluid->php_escape_string($key)."%',{$scoreSummaryKeyword},0)";
				/*
				$categorySQL[] = "if ((
				SELECT count(categories.c_id)
				FROM categories
				JOIN post_category ON post_category.tag_id = category.tag_id
				WHERE post_category.post_id = p.post_id
				AND category.name = '". $fluid->php_escape_string($key)."'
							) > 0,{$scoreCategoryKeyword},0)";
				*/
			}

			// Just incase it is empty, then add 0.
			if(empty($titleSQL))
				$titleSQL[] = 0;
			if(empty($sumSQL))
				$sumSQL[] = 0;
			if(empty($docSQL))
				$docSQL[] = 0;
			if(empty($urlSQL))
				$urlSQL[] = 0;
			if(empty($phoneSQL))
				$phoneSQL[] = 0;
			if(empty($tagSQL))
				$tagSQL[] = 0;
			if(empty($keywordsSQL))
				$keywordsSQL[] = 0;

			// Set up the sort order.
			$sort_by = "relevance DESC";

			$order_by = "ORDER BY " . $sort_by;

			/*
			$sql = "SELECT p.*, m.*, c.*,
					(
						(-- Title score
						".implode(" + ", $titleSQL)."
						)+
						(-- Summary
						".implode(" + ", $sumSQL)."
						)+
						(-- document
						".implode(" + ", $docSQL)."
						)+
						(-- url
						".implode(" + ", $urlSQL)."
						)
					) as relevance
					FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id
					WHERE p.p_enable = '1' AND c.c_enable = 1
					HAVING relevance > 0
					" . $order_by;
			*/
			$query_search = "
	(
						(-- Title score
						".implode(" + ", $titleSQL)."
						)+
						(-- Keywords
						".implode(" + ", $keywordsSQL)."
						)+
						(-- Summary
						".implode(" + ", $sumSQL)."
						)+
						(-- phone
						".implode(" + ", $phoneSQL)."
						)+
						(-- document
						".implode(" + ", $docSQL)."
						)+
						(-- url
						".implode(" + ", $urlSQL)."
						)
					) as relevance
					FROM " . TABLE_SALES . " s
					";

			if(isset($_REQUEST['data_search']))
				$f_data = (array)json_decode(base64_decode($_REQUEST['data_search']));
			else
				$f_data = NULL;

			if(isset($f_data['f_page_num'])) {
				$f_page = $f_data['f_page_num'];
				$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
			}
			else {
				$f_page = 0;
				$f_start = 0;
			}

			$fluid->php_db_commit();

			$f_query_count = "SELECT COUNT(*) AS tmp_c_order_count FROM (SELECT s.*, " . $query_search . " HAVING relevance > 0) as o";

			$f_data['query_count'] = $f_query_count;

			$f_query = "SELECT s.*, " . $query_search . " HAVING relevance > 0 " . $order_by . " LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

			$f_data['query'] = $f_query;
			$f_data['mode'] = "orders";
			$f_data['pagination_function'] = "js_pagination_search";

			$f_data_send = base64_encode(json_encode($f_data));

			$_REQUEST = NULL; // --> Reset $_REQUEST to null as we are loading the fluid.orders module and we do not want it to process the $_REQUEST.
			require_once(FLUID_ORDERS_ADMIN);
			return php_load_orders($f_data_send);
		}
		else if($mode == "accounts") {
			$fluid->php_db_begin();

			$query = NULL;
			$query = trim(base64_decode($_REQUEST['search_input']));

			if (mb_strlen($query)===0){
				// no need for empty search right?
				//return false;
			}

			$query = $fluid->php_limit_chars($query);

			// Weighing scores
			$scoreFullTitle = 6;
			$scoreTitleKeyword = 5;
			$scoreFullSummary = 5;
			$scoreSummaryKeyword = 4;
			$scoreFullDocument = 4;
			$scorePhoneKeyword = 4;
			$scoreDocumentKeyword = 3;
			$scoreCategoryKeyword = 2;
			$scoreUrlKeyword = 1;

			$keywords = $fluid->php_filter_search_keys($query);
			$escQuery = $fluid->php_escape_string($query);
			$titleSQL = array();
			$sumSQL = array();
			$docSQL = array();
			//$categorySQL = array();
			$phoneSQL = array();
			$urlSQL = array();
			$keywordsSQL = array();

			// Matching full occurences
			if (count($keywords) > 1){
				$titleSQL[] = "if (u_email LIKE '%".$escQuery."%',{$scoreFullTitle},0)";
				$sumSQL[] = "if (u_first_name LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
				$docSQL[] = "if (u_last_name LIKE '%".$escQuery."%',{$scoreFullDocument},0)";
				//$phoneSQL[] = "if (u_oauth_provider LIKE '%".$escQuery."%',{$scorePhoneKeyword},0)";
				//$keywordsSQL[] = "if (s_address_street LIKE '%".$escQuery."%',{$scoreTitleKeyword},0)";
			}

			// Matching Keywords
			foreach($keywords as $key){
				$titleSQL[] = "if (u_email LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreTitleKeyword},0)";
				$sumSQL[] = "if (u_first_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreSummaryKeyword},0)";
				$docSQL[] = "if (u_last_name LIKE '%". $fluid->php_escape_string($key)."%',{$scoreDocumentKeyword},0)";
				//$phoneSQL[] = "if (s_address_phonenumber LIKE '%". $fluid->php_escape_string($key)."%',{$scorePhoneKeyword},0)";

				//$urlSQL[] = "if (s_address_city LIKE '%". $fluid->php_escape_string($key)."%',{$scoreUrlKeyword},0)";
				//$keywordsSQL[] = "if (s_address_street LIKE '%". $fluid->php_escape_string($key)."%',{$scoreSummaryKeyword},0)";
			}

			// Just incase it is empty, then add 0.
			if(empty($titleSQL))
				$titleSQL[] = 0;
			if(empty($sumSQL))
				$sumSQL[] = 0;
			if(empty($docSQL))
				$docSQL[] = 0;
			if(empty($urlSQL))
				$urlSQL[] = 0;
			if(empty($phoneSQL))
				$phoneSQL[] = 0;
			if(empty($tagSQL))
				$tagSQL[] = 0;
			if(empty($keywordsSQL))
				$keywordsSQL[] = 0;

			// Set up the sort order.
			$sort_by = "relevance DESC";

			$order_by = "ORDER BY " . $sort_by;

			$query_search = "
	(
						(-- Title score
						".implode(" + ", $titleSQL)."
						)+
						(-- Summary
						".implode(" + ", $sumSQL)."
						)+
						(-- document
						".implode(" + ", $docSQL)."
						)
					) as relevance
					FROM " . TABLE_USERS . " s
					";

			if(isset($_REQUEST['data_search']))
				$f_data = (array)json_decode(base64_decode($_REQUEST['data_search']));
			else
				$f_data = NULL;

			if(isset($f_data['f_page_num'])) {
				$f_page = $f_data['f_page_num'];
				$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
			}
			else {
				$f_page = 0;
				$f_start = 0;
			}

			// Lets get a item count and determine where we start and where we end on items to show.
			//$fluid->php_db_query("SELECT COUNT(s.s_id) AS total, " . $query_search . " GROUP BY relevance HAVING relevance > 0");

			//$fluid->php_db_begin();

			//$search_input = $fluid->php_escape_string(base64_decode($_REQUEST['search_input']));

			$fluid->php_db_commit();

			$f_query_count = "SELECT COUNT(*) AS tmp_u_count FROM (SELECT s.*, " . $query_search . " HAVING relevance > 0) as o";

			$f_data['query_count'] = $f_query_count;

			$f_query = "SELECT s.*, " . $query_search . " HAVING relevance > 0 " . $order_by . " LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

			$f_data['query'] = $f_query;
			$f_data['mode'] = "accounts";
			$f_data['pagination_function'] = "js_pagination_search";

			$f_data_send = base64_encode(json_encode($f_data));

			$_REQUEST = NULL; // --> Reset $_REQUEST to null as we are loading the fluid.account module and we do not want it to process the $_REQUEST.
			require_once(FLUID_ACCOUNT_ADMIN);
			return php_load_accounts($f_data_send);
		}
		else {
			$fluid_mode = new Fluid_Mode($mode);
			$return_data = NULL;

			// We are in item mode, return back a nicely formatted html item page.
			if($fluid_mode->mode == "items") {
				if(isset($_REQUEST['data_search']))
					$f_data = (array)json_decode(base64_decode($_REQUEST['data_search']));
				else
					$f_data = NULL;

				if(isset($f_data['f_page_num'])) {
					$f_page = $f_data['f_page_num'];
					$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
				}
				else {
					$f_page = 0;
					$f_start = 0;
				}

				$fluid->php_db_begin();

				$search_input = $fluid->php_escape_string(rtrim(trim(base64_decode($_REQUEST['search_input']))));

				$fluid->php_db_commit();

				if(strlen($search_input) < 1) {
					// --> Do nothing, load regular item page query.
				}
				else {
					if($_SESSION['f_admin_item_filters_enabled'] == TRUE && isset($_SESSION['f_admin_item_filters_query'])) {
						$f_query_filter = "WHERE " . $_SESSION['f_admin_item_filters_query'];

						$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p " . $f_query_filter;


						/*
						$f_query_filter = "WHERE " . $_SESSION['f_admin_item_filters_query'] . " AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')";

						$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p " . $f_query_filter . ") AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p " . $f_query_filter;
						*/

						$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE " . $_SESSION['f_admin_item_filters_query'] . " AND (p.p_stock > 0) AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')) AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE " . $_SESSION['f_admin_item_filters_query'] . " AND (p.p_stock > 0) AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')) AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE " . $_SESSION['f_admin_item_filters_query'] . " AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')";
					}
					else {
						$f_query_count = "SELECT COUNT(*) AS tmp_c_product_count, (SELECT SUM(p.p_stock) FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_stock > 0) AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')) AS product_stock, (SELECT SUM(p.p_stock * p.p_cost_real) FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_stock > 0) AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')) AS tmp_stock_value FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%')";
					}

					$f_data['query_count'] = $f_query_count;

					if($_SESSION['f_admin_item_filters_enabled'] == TRUE && isset($_SESSION['f_admin_item_filters_query'])) {
						$f_query = "SELECT p.*, c.*, m.*, IF(`p_name` LIKE '%" . $search_input . "%',  20, IF(`p_name` LIKE '%" . $search_input . "%', 10, 0)) + IF(`p_mfgcode` LIKE '%" . $search_input . "%', 15,  IF(`p_mfgcode` LIKE '%" . $search_input . "%', 8, 0)) + IF(`p_mfg_number` LIKE '%" . $search_input . "%', 8,  0), IF(`c_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`p_details` LIKE '%" . $search_input . "%', 2, 0) +  IF(`p_keywords` LIKE '%" . $search_input . "%', 4,  0) + IF(`p_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_desc` LIKE '%" . $search_input . "%', 1,  0) AS `weight` FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE " . $_SESSION['f_admin_item_filters_query'] . " AND (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%') ORDER BY `weight` DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;
					}
					else {
						$f_query = "SELECT p.*, c.*, m.*, IF(`p_name` LIKE '%" . $search_input . "%',  20, IF(`p_name` LIKE '%" . $search_input . "%', 10, 0)) + IF(`p_mfgcode` LIKE '%" . $search_input . "%', 15,  IF(`p_mfgcode` LIKE '%" . $search_input . "%', 8, 0)) + IF(`p_mfg_number` LIKE '%" . $search_input . "%', 8,  0), IF(`c_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`p_details` LIKE '%" . $search_input . "%', 2, 0) +  IF(`p_keywords` LIKE '%" . $search_input . "%', 4,  0) + IF(`p_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_desc` LIKE '%" . $search_input . "%', 1,  0) AS `weight` FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR p.p_mfg_number LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%' OR p.p_details LIKE '%" . $search_input . "%') ORDER BY `weight` DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;
					}

					$f_data['query'] = $f_query;
					$f_data['pagination_function'] = "js_pagination_search";
				}

				$f_data['mode'] = "items";
				$f_data_send = base64_encode(json_encode($f_data));

				return php_load_items($f_data_send);
			}
			// Category or manufacturer mode searching.
			else {
				$return_data = "<div id='fluid-category-listing' class='list-group'>";

				if(!empty(base64_decode($_REQUEST['search_input']))) {
					$fluid->php_db_begin();
					$search_input = $fluid->php_escape_string(rtrim(trim(base64_decode($_REQUEST['search_input']))));

					// Scan for items and categories
					$fluid->php_db_query("SELECT p.*, c.*, m.*, IF(`c_name` LIKE '%" . $search_input . "%',  20, IF(`c_name` LIKE '%" . $search_input . "%', 10, 0)) + IF(`p_name` LIKE '%" . $search_input . "%', 15,  IF(`p_name` LIKE '%" . $search_input . "%', 8, 0)) + IF(`p_mfgcode` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_name` LIKE '%" . $search_input . "%', 5,  0) + IF(`m_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`c_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_seo` LIKE '%" . $search_input . "%', 3,  0) + IF(`p_keywords` LIKE '%" . $search_input . "%', 4,  0) + IF(`p_desc` LIKE '%" . $search_input . "%', 1,  0) +  IF(`m_desc` LIKE '%" . $search_input . "%', 1,  0) +  IF(`c_desc` LIKE '%" . $search_input . "%', 1,  0) AS `weight` FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_CATEGORIES . " c on p_catid = c_id INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id WHERE (p.p_name LIKE '%" . $search_input . "%' OR p.p_mfgcode LIKE '%" . $search_input . "%' OR m.m_name LIKE '%" . $search_input . "%' OR c.c_name LIKE '%" . $search_input . "' OR p.p_desc LIKE '%" . $search_input . "%' OR p.p_seo LIKE '%" . $search_input . "%' OR p.p_keywords LIKE '%" . $search_input . "%') ORDER BY `weight` DESC");

					$tmp_array = NULL;

					if(isset($fluid->db_array))
						foreach($fluid->db_array as $value) {
							$tmp_array[$value[$fluid_mode->id]]['id'] = $value[$fluid_mode->id];
							$tmp_array[$value[$fluid_mode->id]]['parent_id'] = $value[$fluid_mode->X . "_parent_id"];
						}

					// Scan for categories / manufacturers only. Picks up empty categories / manufacturers.
					$fluid->php_db_query("SELECT " . $fluid_mode->X_id . ", " . $fluid_mode->X . "_parent_id, IF(`" . $fluid_mode->name . "` LIKE '%" . $search_input . "%',  20, IF(`" . $fluid_mode->name . "` LIKE '%" . $search_input . "%', 10, 0)) + IF(`" . $fluid_mode->seo . "` LIKE '%" . $search_input . "%', 5,  0) + IF(`" . $fluid_mode->desc . "` LIKE '%" . $search_input . "%', 3,  0) AS `weight` FROM " . $fluid_mode->table . " " . $fluid_mode->X . " WHERE (" . $fluid_mode->X . "." . $fluid_mode->name . " LIKE '%" . $search_input . "' OR " . $fluid_mode->X . "." . $fluid_mode->seo . " LIKE '%" . $search_input . "%' OR " . $fluid_mode->X . "." . $fluid_mode->desc . " LIKE '%" . $search_input . "%') ORDER BY `weight` DESC");

					if(isset($fluid->db_array))
						foreach($fluid->db_array as $value) {
							$tmp_array[$value[$fluid_mode->id]]['id'] = $value[$fluid_mode->id];
							$tmp_array[$value[$fluid_mode->id]]['parent_id'] = $value[$fluid_mode->X . "_parent_id"];
					}

					$where_query = NULL;
					$where_query_parent = NULL;
					if(isset($tmp_array)) {
						$where_query = $fluid_mode->id . " IN (";
						$iIn = 0;
						$iParent = 0;
						foreach($tmp_array as $key => $value) {
							if($iIn > 0)
								$where_query .= ",";

							$where_query .= $value['id'];

							if(!isset($value['parent_id'])) {
								if($iParent > 0)
									$where_query_parent .= ",";
								else
									$where_query_parent = " OR " . $fluid_mode->X . "_parent_id IN (";

								$where_query_parent .= $value['id'];

								$iParent++;
							}
							else if(isset($value['parent_id']))
								$where_query .= "," . $value['parent_id'];

							$iIn++;
						}
						$where_query .= ")";

						if(isset($where_query_parent)) {
							$where_query_parent .= ")";
							$where_query .= $where_query_parent;
						}
					}

					$fluid->php_db_commit();

					if(!$where_query)
						$return_data .= "No results found.";
					else {
						$data = php_html_categories(NULL, $mode, $where_query); // Fetch the categories / manufacturers listings.

						$return_data .= $data['html'];
					}

					// Make sure sorting is disabled since we are returning back a limited data set.
					$execute_functions[0]['function'] = "js_reset_sort_prevent";
					$execute_functions[0]['data'] = base64_encode(json_encode(true));
				}
				else {
					// Search query was blank, fetch all categories / manufacturers.
					$data = php_html_categories(NULL, $mode, NULL);
					$return_data .= $data['html'];

					// Make sure sorting is enabled since we are returning all data.
					$execute_functions[0]['function'] = "js_reset_sort_prevent";
					$execute_functions[0]['data'] = base64_encode(json_encode(false));
				}

				$return_data .= "</div>";
			}

			if($mode != "items") {
				// Follow up functions to execute on a server response back to the user.
				$execute_functions[1]['function'] = "js_clear_fluid_selection";
				$execute_functions[1]['data'] = base64_encode(json_encode(""));

				$execute_functions[2]['function'] = "js_clear_fluid_category";
				$execute_functions[2]['data'] = base64_encode(json_encode(""));

				$execute_functions[3]['function'] = "js_update_action_menu";
				$execute_functions[3]['data'] = base64_encode(json_encode(""));

				$execute_functions[4]['function'] = "js_sortable_categories";
				$execute_functions[4]['data'] = base64_encode(json_encode(""));
			}
		}

		if($mode != "items")
			return json_encode(array("innerhtml" => base64_encode($return_data), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_sortable_categories_update() {
	$fluid = new Fluid ();

	try {
		$data = json_decode(base64_decode($_REQUEST['data']));
		$new_pos = $_REQUEST['newpos'] + 1; // based 0 value.

		$fluid_mode = new Fluid_Mode($data->mode);

		$fluid->php_db_begin();

		if(empty($data->{$fluid_mode->X . "_parent_id"}))
			$where = "IS NULL";
		else
			$where = "= " . $fluid->php_escape_string($data->{$fluid_mode->X . "_parent_id"});

		$rand = rand(10000, 99999);

		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " AS SELECT " . $fluid_mode->X . "_id, " . $fluid_mode->X . "_sortorder FROM " . $fluid_mode->table . " WHERE " . $fluid_mode->X . "_parent_id " . $where);

		// Get the old previous position.
		$fluid->php_db_query("SELECT " . $fluid_mode->X . "_sortorder FROM temp_table_" . $rand . " WHERE " . $fluid_mode->X . "_id = '" . $fluid->php_escape_string($data->{$fluid_mode->id}) . "'");
		$prev_pos = $fluid->db_array[0][$fluid_mode->sortorder];

		// Re-organise the order of the other items in the table and make a slot for the item we are trying to move.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET " . $fluid_mode->X . "_sortorder = " . $fluid_mode->X . "_sortorder - 1 WHERE " . $fluid_mode->X . "_sortorder > '" . $fluid->php_escape_string($prev_pos) . "'");

		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET " . $fluid_mode->X . "_sortorder = " . $fluid_mode->X . "_sortorder + 1 WHERE " . $fluid_mode->X . "_sortorder >= '" . $fluid->php_escape_string($new_pos) . "'");

		// Set the order on the category we are trying to set.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET " . $fluid_mode->X . "_sortorder = '" . $fluid->php_escape_string($new_pos) . "' WHERE " . $fluid_mode->X . "_id = '" . $fluid->php_escape_string($data->{$fluid_mode->id}) . "'");

		// Merge the data back into the table.
		$fluid->php_db_query("UPDATE " . $fluid_mode->table . " dest, (SELECT * FROM temp_table_" . $rand . ") src SET dest." . $fluid_mode->X . "_sortorder = src." . $fluid_mode->X . "_sortorder WHERE dest." . $fluid_mode->X . "_id = src." . $fluid_mode->X . "_id");

		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

		$fluid->php_db_commit();

		return json_encode(array("error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_sortable_products_update() {
	$fluid = new Fluid ();

	try {
		// --> TODO: Need to fix this reordering, and base it off the fluid.banner.php -> php_sortable_banners_update().
		$data = json_decode(base64_decode($_REQUEST['data']));
		$new_pos = $_REQUEST['newpos'] + 1; // based 0 value.
		$fluid_mode = new Fluid_Mode($data->mode);

		$fluid->php_db_begin();

		$rand = rand(10000, 99999);
		$fluid->php_db_query("CREATE TEMPORARY TABLE temp_table_" . $rand . " AS SELECT p_id, p_sortorder" . $fluid_mode->sort_order . " FROM " . TABLE_PRODUCTS . " WHERE " . $fluid_mode->p_catmfg_id . " = '" . $data->{$fluid_mode->id} . "'");

		// Get the old previous position.
		$fluid->php_db_query("SELECT p_sortorder" . $fluid_mode->sort_order . " FROM temp_table_" . $rand . " WHERE p_id = '" . $fluid->php_escape_string($data->p_id) . "'");
		$prev_pos = $fluid->db_array[0]['p_sortorder' . $fluid_mode->sort_order];

		// Re-organise the order of the other items in the table and make a slot for the item we are trying to move.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET p_sortorder" . $fluid_mode->sort_order . " = p_sortorder" . $fluid_mode->sort_order . " - 1 WHERE p_sortorder" . $fluid_mode->sort_order . " > '" . $fluid->php_escape_string($prev_pos) . "'");

		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET p_sortorder" . $fluid_mode->sort_order . " = p_sortorder" . $fluid_mode->sort_order . " + 1 WHERE p_sortorder" . $fluid_mode->sort_order . " >= '" . $fluid->php_escape_string($new_pos) . "'");

		// Set the order on the item we are trying to set.
		$fluid->php_db_query("UPDATE temp_table_" . $rand . " SET p_sortorder" . $fluid_mode->sort_order . " = '" . $fluid->php_escape_string($new_pos) . "' WHERE p_id = '" . $fluid->php_escape_string($data->p_id) . "'");

		// Merge the data back into the table.
		$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " dest, (SELECT * FROM temp_table_" . $rand . ") src SET dest.p_sortorder" . $fluid_mode->sort_order . " = src.p_sortorder" . $fluid_mode->sort_order . " WHERE dest.p_id = src.p_id");

		$fluid->php_db_query("DROP TABLE temp_table_" . $rand);

		$fluid->php_db_commit();

		return json_encode(array("error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_fluid_scan() {
	try {
		$fluid = new Fluid ();
		$f_scan = json_decode(base64_decode($_REQUEST['data']), TRUE);

		$html_edit = NULL;

		if(isset($f_scan['s_code']) && strlen($f_scan['s_code']) > 0) {
			$fluid->php_db_begin();

			$p_in = "WHERE p_mfgcode IN ('" . $fluid->php_escape_string($f_scan['s_code']) . "'";

			if(isset($f_scan['s_scan'])) {
				foreach($f_scan['s_scan'] as $s_key => $s_data) {
					$p_in .= ", ";
					$p_in .= "'" . $s_data['p_mfgcode'] . "'";
				}
			}

			$p_in .= ");";

			// Change query to select all in the s_code and IN WHERE and rebuild the display html and return back?
			$fluid->php_db_query("SELECT p.*, m.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $p_in);

			$fluid->php_db_commit();

			if(isset($fluid->db_array)) {
				$html_edit = "<div id='fluid-cart-scroll-edit'>";

				foreach($fluid->db_array as $key => $data) {
					//$data['p_stock'] = $fluid->php_process_stock($data); // --> should be fluid_stock = new Fluid(), otherwise fluid->db_array can get messed up.

					// Process the image.
					$p_images = $fluid->php_process_images($data['p_images']);
					$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);
					if($f_scan['s_code'] == $data['p_mfgcode']) {
						$f_flash_style = " class='f-scan-animated'";
					}
					else {
						$f_flash_style = NULL;
					}

					// Item exists, we are not really adjusting anything. Just sending back some HTML to redraw the list of items.
					if(isset($f_scan['s_scan'][$data['p_mfgcode']])) {
						$p_stock = (int)$f_scan['s_scan'][$data['p_mfgcode']]['p_stock'];
						$p_adj = $f_scan['s_scan'][$data['p_mfgcode']]['p_adj'];
						$p_stock_adj = (int)$f_scan['s_scan'][$data['p_mfgcode']]['p_stock_adj'];
					}
					else {
						// Item doesn't exists, lets build some data.
						$p_stock = (int)$data['p_stock'];

						if(isset($f_scan['f_mode'])) {
							if($f_scan['f_mode'] == "minus") {
								if($p_stock > 0) {
									$p_adj = -1;
									$p_stock_adj = $p_stock - 1;
								}
								else {
									$p_adj = 0;
									$p_stock_adj = $p_stock;
								}
							}
							else if($f_scan['f_mode'] == "plus") {
								$p_adj = 1;
								$p_stock_adj = $p_stock + $p_adj;
							}
							else {
								$p_adj = 0;
								$p_stock_adj = $p_stock;
							}
						}
						else {
							$p_adj = 1;
							$p_stock_adj = $p_stock + $p_adj;
						}

						//$p_adj = 0;
						//$p_stock_adj = $p_stock;
					}

					$f_scan['s_scan'][$data['p_mfgcode']] = Array('p_id' => $data['p_id'], 'p_mfgcode' => $data['p_mfgcode'], 'p_stock' => $p_stock, 'p_stock_adj' => $p_stock_adj, 'p_adj' => $p_adj);

					$confirm_message_item = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";
					$confirm_message_item .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img>";
					$confirm_message_item .= $data['m_name'] . " " . $data['p_name'];
					$confirm_message_item .= "</div>";

					$p_price = $data['p_price'];
					$p_price_discount = NULL;
					if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) ) ) {
						$p_price_discount = $data['p_price_discount'];
					}

					$html_edit .= "<div class='fluid-cart' name='fluid-cart-editor-items' id='fluid-cart-editor-item-" . $data['p_id'] . "' data-id='" . $data['p_id'] . "' data-price='" . base64_encode($p_price) . "'>";
						$html_edit .= "<div class='divTable' id='section-" . $data['p_mfgcode'] . "'>";
							$html_edit .= "<div class='divTableBody'>";
								$html_edit .= "<div id='f-scan-row-" . $data['p_mfgcode'] . "'" . $f_flash_style . " style='display: table-row;'>";

									$html_edit .= "<div class='divTableCell divTablePadding f-scan-image-min' style='vertical-align:middle;'>";
										$html_edit .= "<div class='f-scan-trash-btn' style='margin-top: 0px; margin-bottom: 0px; display: inline-block; vertical-align: middle;'>";
											$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Remove this item from the list and cancel its stock updates?</div>";
											$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='this.blur(); document.getElementById(\"fluid-cart-scroll-edit\").removeChild(document.getElementById(\"fluid-cart-editor-item-" . $data['p_id'] . "\")); delete FluidVariables.s_scan[\"" . $data['p_mfgcode'] . "\"]; js_scan_init(\"f_scan_code\"); js_scan_set_save_all_button(); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");
											$html_edit .= "<button type='button' class='btn btn-danger' aria-haspopup='true' aria-expanded='false' style='float:left;' onClick='this.blur(); $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete . $confirm_message_item) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
										$html_edit .= "</div>";
										$html_edit .= "<img class='f-scan-image-show' src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"></img>";
									$html_edit .= "</div>";

									$html_edit .= "<div class='divTableCell divTablePadding' style='vertical-align:middle;'>";
										$html_edit .= "<div style='display: table-row; style='width: 100%;'>";
										$html_edit .= "<div style='display: table-cell; width: 100%; vertical-align: middle;'>" . $data['m_name'] . " " . $data['p_name'] . " - " . $data['p_mfgcode'] . "</div>";
											
											$html_edit .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; padding-right: 5px;'>";
												$html_edit .= "<div style='font-weight: 600; margin: auto; text-align: center;'>Price</div>";
											
												if(isset($p_price_discount)) {
													$html_edit .= "<div style='font-style: italic; text-decoration: line-through;'>" . number_format($p_price, 2, '.', ',') .  "</div>";
													$html_edit .= "<div style='color: red;'>" . number_format($p_price_discount, 2, '.', ',') .  "</div>";
												}
												else {
													$html_edit .= "<div>" . number_format($p_price, 2, '.', ',') .  "</div>";
												}
											$html_edit .= "</div>";

										$html_edit .= "</div>";
									$html_edit .= "</div>";

								$html_edit .= "<div class='divTableCell divTablePadding' style='vertical-align:middle; width: 55px;'>";
										$html_edit .= "<div style='display: inline-block;'><div class='f-scan-stock-qty' style='margin-bottom: 0px; display: inline-block; text-align: center;'><div>Adjustment</div><input class='fluid-cart-qty pull-left' style='margin-bottom: 0px;' type='text' value='" . $p_adj . "' disabled id='fluid-cart-editor-qty-adj-" . $data['p_id'] . "'></div></div>";
									$html_edit .= "</div>";

									$html_edit .= "<div class='divTableCell divTablePadding f-scan-buttons-adjust' style='vertical-align:middle; text-align: right; padding: 3px 0px 3px 0px;'>";
										$html_edit .= "<div style='display: inline-block; padding-top: 5px; padding-bottom: 5px; width: 100%;'>";
											$html_edit .= "<div style='margin-bottom: 0px; width: 100%;'>";
												$html_edit .= "<div style='margin-bottom: 0px; display: inline-block; width: 100%;'>";
													$html_edit .= "<div style='width: 100%;'>";
														$html_edit .= "<div style='vertical-align: bottom; display: inline-block; padding-right: 10px;'><button class='btn btn-warning' onClick='js_fluid_scan_decrease_num(\"" . $data['p_id'] . "\", \"" . $data['p_mfgcode'] . "\"); this.blur();'><span class='glyphicon glyphicon-minus'></span></button></div>";
														$html_edit .= "<div class='f-scan-stock-qty' style='vertical-align: middle; margin-bottom: 0px; display: inline-block; text-align: center;'><div>Stock</div><input class='fluid-cart-qty pull-left' style='margin-bottom: 0px;' type='text' step='1' min='0' value='" . $p_stock_adj . "' disabled id='fluid-cart-editor-qty-" . $data['p_id'] . "'></div>";
														$html_edit .= "<div style='margin-bottom: 0px; vertical-align: bottom; display:inline-block; padding-left: 10px;' class='f-scan-padding'><button  class='btn btn-warning' onClick='js_fluid_scan_increase_num(\"" . $data['p_id'] . "\", \"" . $data['p_mfgcode'] . "\"); this.blur();'><span class='glyphicon glyphicon-plus'></span></button></div>";

														$confirm_message = "<div class='alert alert-danger' role='alert'>Update this item?</div>";
														$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" onClick='this.blur(); js_scan_update(\"" . base64_encode($data['p_mfgcode']) . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

														$html_edit .= "<div style='vertical-align: bottom; display: inline-block;'><button type='button' class='btn btn-success' aria-haspopup='true' aria-expanded='false' onClick='this.blur(); $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message . $confirm_message_item) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class='glyphicon glyphicon-check' aria-hidden='true'></span></button></div>";

													$html_edit .= "</div>";
												$html_edit .= "</div>";
											$html_edit .= "</div>";

										$html_edit .= "</div>";
									$html_edit .= "</div>";

								$html_edit .= "</div>"; // --> divTableRow

							$html_edit .= "</div>";
						$html_edit .= "</div>";
					$html_edit .= "</div>";
				}
				$html_edit .= "</div>";
			}
		}
		// --> Need to re-enable typing.
		$execute_functions[]['function'] = "js_scan_init";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("f_scan_code"));

		// --> Set the scan data.
		$execute_functions[]['function'] = "js_fluid_scan_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_scan['s_scan']));

		if(isset($html_edit)) {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode($html_edit), "parent" => base64_encode("f-scan-holder"))));
		}

		$execute_functions[]['function'] = "js_scan_set_save_all_button";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_scan_scroll";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_scan['s_code']));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$execute_functions[]['function'] = "js_scan_init";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("f_scan_code"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
}

function php_scan_history($f_data) {
	$f_html = NULL;
	if(isset($f_data))
		foreach($f_data as $key => $data)
			$f_html .= base64_decode($data->p_html);

	return $f_html;
}

function php_scanning_items_modal() {
	try {
		$fluid = new Fluid();

		$detect = new Mobile_Detect;

		if($detect->isTablet()) {
			$f_large_buttons = " fluid-admin-search-btn-large";
			$f_large_btn_width = " fluid-admin-search-btn-large fluid-admin-search-btn-large-width";
		}
		else {
			$f_large_buttons = NULL;
			$f_large_btn_width = NULL;
		}

		if(isset($_REQUEST['fmode']))
			$f_mode = $_REQUEST['fmode'];
		else
			$f_mode = "plus";

		if($f_mode != "plus" && $f_mode != "minus" && $f_mode != "none")
			$f_mode = "plus";

		if($f_mode == "plus")
			$f_title = " <span class='glyphicon glyphicon-plus' aria-hidden='true' style='font-size: 10px;'></span>";
		else if($f_mode == "minus")
			$f_title = " <span class='glyphicon glyphicon-minus' aria-hidden='true' style='font-size: 10px;'></span>";
		else
			$f_title = NULL;

		$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Close the window and cancel all item changes?</div>";
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_scan_clear(false);'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

		$confirm_message_save = "<div class='alert alert-danger' role='alert'>Update all item changes?</div>";
		$confirm_footer_save = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" onClick='this.blur(); js_scan_update_all();'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

		$modal = "
		<div class='modal-dialog f-dialog' id='f-stock-dialog' role='document'>
			<div id='scan-edit-dialog' class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Stock adjustment" . $f_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-close' class=\"fa fa-window-close\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='this.blur(); $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete) . "\"), Base64.decode(\"" . $confirm_footer . "\"));' style='padding-right: 40px;'></i><i id='f-window-scan-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_scan_maximize();'></i><i id='f-window-scan-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_scan_minimize();'></i></div></div>
				</div>

				<div id='f-modal-body' class='modal-body' style='padding: 0px;'>

					<div id='f-stock-scroll-div' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:65vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div class='f-stock-div'>

							<div class='f-stock-holder-div'>";

							$f_html = "<div><div class='list-group'>";

									$f_html .= "<div class=\"input-group\" style='padding-top: 10px;'>
												<span class=\"input-group-addon f-mobile-span-scan\"><div style='width:120px !important;'>Item code: </div></span>
												  <input id=\"f_scan_code\" type=\"text\" class=\"form-control" . $f_large_buttons . "\" style='width: 80%;' placeholder=\"scan the item code\">
												  <div style='display: flex; padding-left: 10px; padding-right: 10px;'><button id='f_scan_btn' type='button' class='btn" . $f_large_btn_width . " btn-default' onClick='js_fluid_scan(\"" . $f_mode . "\"); this.blur();'><span class=\"glyphicon glyphicon-barcode\" aria-hidden=\"true\"></span> <div class='f-scan-btn-text'>Scan</div></button></div>
												</div>";

							$f_html .= "</div></div>";

							$f_html .= "<div id='f-scan-holder'>Please scan some items.</div>";

							$modal .= $f_html;

							$f_html = "<div style='padding-top: 20px; width: 100%; border-bottom: 0px solid #00D520;'></div><div style='padding-top: 20px; width: 100%; border-bottom: 1px solid black;' id='f-scan-history-log'>Scan Save History</div><div id='f-scan-history'>";

							$f_scan_history = NULL;
							if(isset($_REQUEST['data'])) {
								$f_scan_history = json_decode(base64_decode($_REQUEST['data']));
								if(!empty($f_scan_history))
									$f_html .= php_scan_history($f_scan_history);
								else
									$f_html .= "no scan history";
							}
							else
								$f_html .= "no scan history";

							$f_html .= "</div>";

							$modal .= $f_html;

							$modal .= "</div>
						</div>
					</div>

				</div>";

				$modal .= "<div id='f-scan-footer' class='modal-footer'>";

				$confirm_message_delete = "<div class='alert alert-danger' role='alert'>Cancel all item changes?</div>";
				$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_scan_clear(false); document.getElementById(\"f-scan-holder\").innerHTML = \"Please scan some items.\"; this.blur(); js_scan_init(\"f_scan_code\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Yes</button>");

				$footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' onClick='this.blur(); $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_delete) . "\"), Base64.decode(\"" . $confirm_footer . "\"));'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div><div style='float:right;'><button id='fluid_scan_save_all_btn' type='button' class='btn btn-success' onClick='this.blur(); if(Object.keys(FluidVariables.s_scan).length > 0) { $(document).off(\"keypress\"); js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . base64_encode($confirm_message_save) . "\"), Base64.decode(\"" . $confirm_footer_save . "\")); }'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save All</button></div>";

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

		$execute_functions[]['function'] = "js_scan_clear";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(false));

		$execute_functions[]['function'] = "js_scan_init";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("f_scan_code"));

		$execute_functions[]['function'] = "js_scan_set_save_all_button";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_scan_scroll_top";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_scan_update() {
	try {
		$fluid = new Fluid();
		$fluid->php_db_begin();

		$f_scan = json_decode(base64_decode($_REQUEST['data']), TRUE);

		$where = "WHERE p_id IN (";
		$i = 0;

		$f_scan_history = NULL;

		foreach($f_scan['s_scan'] as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product['p_id']);

			$i++;
		}
		$where .= ")";

		$fluid->php_db_query("SELECT p_id, p_component, p_catid, p_mfgcode, p_price_discount, p_discount_date_start, p_discount_date_end, p_price, p_cost, p_cost_real, p_stock, p_images, p_name, m_name, p_stock_end FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

		$cat_refresh = NULL;

		$where = NULL;

		if(isset($fluid->db_array)) {
			$c_set = "CASE";
			$c_set_stock = "CASE";
			$c_set_discount_date_end = "CASE";
			$i = 0;
			$c = 0;

			// Time to re-calculate the cost averages.
			foreach($fluid->db_array as $key => $db_data) {
				if($c == 0) {
					$where .= "WHERE p_id in (";
				}

				if($c != 0) {
					$where .= ", ";
				}

				$where .= $fluid->php_escape_string($db_data['p_id']);

				$c++;

				/*
				if($db_data['p_component'] == FALSE) {
					if($c == 0) {
						$where .= "WHERE p_id in (";
					}

					if($c != 0) {
						$where .= ", ";
					}

					$where .= $fluid->php_escape_string($db_data['p_id']);

					$c++;
				}
				else {
					$db_data['p_stock'] = $fluid->php_process_stock($db_data);
				}
				*/

				$cat_refresh[$db_data['p_catid']] = $db_data['p_catid'];
				$f_new_stock = $db_data['p_stock'] + $f_scan['s_scan'][$db_data['p_mfgcode']]['p_adj'];

				if($f_new_stock < 0) {
					$f_new_stock = 0;
				}

				$o_data['old_stock'] = $db_data['p_stock'];
				$o_data['old_cost'] = $db_data['p_cost_real'];
				$o_data['old_cost_avg'] = $db_data['p_cost'];
				$n_data['new_cost'] = $db_data['p_cost_real'];
				$n_data['new_stock'] = $f_new_stock;

				$fluid->db_array[$key]['p_cost'] = $fluid->php_calculate_cost_average($o_data, $n_data);

				/*
				if($db_data['p_component'] == TRUE) {
					$fluid->php_process_component_stock($db_data, Array("old_stock" => $o_data['old_stock'], "new_stock" => $n_data['new_stock']));
				}
				*/

				// Process the image.
				$p_images = $fluid->php_process_images($db_data['p_images']);
				$f_img_name = str_replace(" ", "_", $db_data['m_name'] . "_" . $db_data['p_name'] . "_" . $db_data['p_mfgcode']);
				$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

				$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

				if($db_data['p_price_discount'] && ((strtotime($db_data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($db_data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($db_data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $db_data['p_discount_date_end'] == NULL) || ($db_data['p_discount_date_start'] == NULL && $db_data['p_discount_date_end'] == NULL) ) ) {
					$p_price = $db_data['p_price_discount'];
				}
				else {
					$p_price = $db_data['p_price'];
				}

					$html_edit = "<div class='fluid-cart' style='background-color: #dfdfdf; font-style: italic;' name='fluid-cart-editor-items-history' id='fluid-cart-history-item-" . $db_data['p_id'] . "' data-id='" . $db_data['p_id'] . "' data-price='" . base64_encode($p_price) . "'>";
						$html_edit .= "<div class='divTable' id='history-section-" . $db_data['p_mfgcode'] . "'>";
							$html_edit .= "<div class='divTableBody'>";
								$html_edit .= "<div id='f-scan-row-history-" . $db_data['p_mfgcode'] . "' style='display: table-row;'>";

									$html_edit .= "<div class='divTableCell divTablePadding f-scan-image-min' style='vertical-align:middle;'>";
										$html_edit .= "<img class='f-scan-image-show' src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $db_data['m_name'] . " " . $db_data['p_name']) . "\"></img>";
									$html_edit .= "</div>";

									$html_edit .= "<div class='divTableCell divTablePadding' style='vertical-align:middle;'>";
										$html_edit .= "<div style='display: table-row; style='width: 100%;'>";
										$html_edit .= "<div style='display: table-cell; width: 100%; vertical-align: middle;'>" . $db_data['m_name'] . " " . $db_data['p_name'] . " - " . $db_data['p_mfgcode'] . "</div>";
										$html_edit .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; padding-right: 5px;'><div style='font-weight: 600; margin: auto; text-align: center;'>Price</div><div>" . number_format($p_price, 2, '.', ',') .  "</div></div>";
										$html_edit .= "</div>";
									$html_edit .= "</div>";

								$html_edit .= "<div class='divTableCell divTablePadding' style='margin-left: 10px; vertical-align:middle; width: 55px;'>";
										$html_edit .= "<div style='display: inline-block;'><div class='f-scan-stock-qty' style='margin-bottom: 0px; display: inline-block; text-align: center;'><div>Adjustment</div><input class='fluid-cart-qty pull-left' style='margin-bottom: 0px;' type='text' value='" . $f_scan['s_scan'][$db_data['p_mfgcode']]['p_adj'] . "' disabled id='fluid-cart-history-qty-adj-" . $db_data['p_id'] . "'></div></div>";
									$html_edit .= "</div>";

									$html_edit .= "<div class='divTableCell divTablePadding f-scan-buttons-adjust' style='vertical-align:middle; text-align: right; padding: 3px 0px 3px 0px; width: 15%;'>";

										$tz = SERVER_TIMEZONE;
										$timestamp = time();
										$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
										$dt->setTimestamp($timestamp); //adjust the object to correct timestamp

										$html_edit .= $dt->format('m-d-Y H:i:s');
									$html_edit .= "</div>";

								$html_edit .= "</div>"; // --> divTableRow

							$html_edit .= "</div>";
						$html_edit .= "</div>";
					$html_edit .= "</div>";

				$f_scan_history[] = Array("p_id" => $db_data['p_id'], "p_mfgcode" => $db_data['p_mfgcode'], "p_qty" => $f_scan['s_scan'][$db_data['p_mfgcode']]['p_adj'], "p_html" => base64_encode($html_edit));

				$p_avg_cost = !empty($fluid->db_array[$key]['p_cost']) ? "'" . $fluid->php_escape_string($fluid->db_array[$key]['p_cost']) . "'" : "NULL";

				$c_set .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_avg_cost . ")";
				$c_set_stock .= " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN ('" . $fluid->php_escape_string($f_new_stock) . "')";

				// --> Must check the stock levels and if p_stock_end is set to true, if so, we need to reset the end discount date to end the discount if the items stock is set to zero.
				$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (p_discount_date_end)";
				if(isset($db_data['p_stock_end']) && isset($f_new_stock)) {
					if($db_data['p_stock_end']  == 1 && $f_new_stock < 1) {
						$f_date_end = strtotime($db_data['p_discount_date_end']);

						if($f_date_end > strtotime(date("Y-m-d H:i:s"))) {
							$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

							$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
						}
						else if(empty($db_data['p_discount_date_end'])) {
							$p_discount_date_end = "'" . date("Y-m-d H:i:s") . "'";

							$c_set_discount_date_end_tmp = " WHEN (`p_id`) = ('" . $db_data['p_id'] . "') THEN (" . $p_discount_date_end . ")";
						}
					}
				}

				$c_set_discount_date_end .= $c_set_discount_date_end_tmp;

				$i++;
			}

			if(isset($where)) {
				$where .= ")";

				$f_update_query = "UPDATE " . TABLE_PRODUCTS . " SET `p_cost` = " . $c_set . " END, `p_discount_date_end` = " . $c_set_discount_date_end . " END, `p_stock` = " . $c_set_stock . " END " . $where;

				$fluid->php_db_query($f_update_query);

				$fluid->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('ADMIN: scan update', '" . $fluid->php_escape_string(serialize(print_r($f_update_query, TRUE))) . "')");
			}
		}

		$fluid->php_db_commit();

		$execute_functions[]['function'] = "js_fluid_scan_history_data_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_scan_history));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-confirm-modal"));

		if($f_scan['s_all'] == FALSE) {
			$execute_functions[]['function'] = "js_scan_cleanup";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_scan['s_scan']));

			// --> Need to re-enable typing.
			$execute_functions[]['function'] = "js_scan_init";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("f_scan_code"));

			$execute_functions[]['function'] = "js_scan_set_save_all_button";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

			$execute_functions[]['function'] = "js_modal_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));
		}
		else {
			$execute_functions[]['function'] = "js_scan_clear";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(true));

			// --> Need to re-enable typing.
			$execute_functions[]['function'] = "js_scan_init";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("f_scan_code"));

			$execute_functions[]['function'] = "js_scan_set_save_all_button";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("innerHTML" => base64_encode(""), "parent" => base64_encode("fluid-cart-scroll-edit"))));

			$execute_functions[]['function'] = "js_modal_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

			$execute_functions[]['function'] = "js_scan_scroll_top";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_load_search_images() {
	$fluid = new Fluid();

	try {
		$fluid->php_db_begin();

		// p_enable, p_stock, p_price, p_price_discount, p_discount_date_end, p_newarrivalenddate, p_buyqty
		if(isset($_REQUEST['mode']))
			$mode = $_REQUEST['mode'];
		else
			$mode = NULL;

		$modal = "<div class='modal-dialog f-dialog' role='document'>
					<div class='modal-content'>

						<div class='panel-default'>
						  <div class='panel-heading'>Item downloader</div>
						</div>

					  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

						<div class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; min-height: 40vh; overflow-y: scroll;'>
							<div>
									<div style='margin-left:10px; margin-right: 10px;'>";
									$modal .= "<div class=\"input-group\">";
										$modal .= "<div class='alert alert-danger' role='alert'>You are about to download images and other data from the internet for the selected items. This process can be slow and could take some time and it is possible that the wrong image and data could be assigned to a item. Previous data will be replaced. Proceed with CAUTION!</div>";
										$modal .= "</div>";
										$modal .= "<div style='padding-bottom: 20px;'>Link override set to TRUE will grab all links on the initial search scan, and break the loop when it finds the first link. This can be used to find some troublesome problems on amazon. Default is FALSE which works 99% of the time.</div>";
									$modal .= "</div>
							";

			$modal .= "
							<div class=\"input-group\" style='padding-left: 20px;'>
								<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Options:</div></span>

								<select id='f-downloader-options' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">
									<option value='0'>All data</option>
									<option value='1'>Images only</option>
									<option value='2'>Dimensions and weight only</option>
									<option value='3'>Dimensions only</option>
									<option value='4'>Weight only</option>
									<option value='5'>Details only</option>
									<option value='6'>Description only</option>
									<option value='7'>Details and Description only</option>
									<option value='8'>All but image</option>
									<option value='9'>Product name only</option>
									<option value='10'>All but name</option>
									<option value='11'>Specs (Henrys)</option>
									<option value='12'>Keywords (Henrys)</option>
									<option value='13'>In the Box (Henrys)</option>
									<option value='14'>All but description (Henrys)</option>
								</select>
							</div>

							<div class=\"input-group\" style='padding-left: 20px; padding-top: 20px;'>
								<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Region:</div></span>

								<select id='f-downloader-region' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">
									<option value='0'>Amazon USA</option>
									<option value='1'>Amazon Canada</option>
									<option value='2'>Miller Canada (Images)</option>
									<option value='3'>Miller Australia (Overview & Specs)</option>
									<option value='4'>Henrys</option>
									<option value='5'>Panasonic</option>
								</select>
							</div>

							<div class=\"input-group\" style='padding-left: 20px; padding-top: 20px;'>
								<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Search type:</div></span>

								<select id='f-downloader-editor' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">
									<option value='0'>All - Manufacturer + UPC</option>
									<option value='1'>UPC Only</option>
									<option value='2'>Manufacturer + MFG Code</option>
								</select>
							</div>

							<div class=\"input-group\" style='padding-left: 20px; padding-top: 20px; padding-bottom: 30px;'>
								<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:100px !important;'>Link override:</div></span>

								<select id='f-downloader-override' class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\">
									<option value='0'>FALSE</option>
									<option value='1'>TRUE</option>
								</select>
							</div>

						</div>
					</div>
				</div>";

		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

			$selection_output = "<div class='well' style='max-height: 20vh !important; overflow-y: scroll;'>";

			foreach($fluid->db_array as $value) {
				// Ignoring items that have images already.
				//if(empty($value['p_images']) || $value['p_images'] == '' || $value['p_images'] == "W10=") {
					// Process the image.
					$p_images = $fluid->php_process_images($value['p_images']);
					$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

					$selection_output .= "<img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img> " . $value['m_name'] . " " . $value['p_name'] . "<br>";
				//}
			}

			$selection_output .= "</div>";

		$selection_output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-warning' role='alert'>Are you sure you want to make changes to the selected items?</div>" . $selection_output);
		$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\" onClick='js_image_downloader(\"" . $mode . "\");'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");

			  $modal .= "<div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>
				<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			  </div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']  = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']  = base64_encode(json_encode("#fluid-modal"));

		$fluid->php_db_commit();

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_image_downloader() {
	try {
		$fluid = new Fluid();

		$fluid->php_db_begin();
		// Generate query to load data of the selected items.
		$data = json_decode(base64_decode($_REQUEST['data']));

		$where = "WHERE p_id IN (";
		$i = 0;
		foreach($data->items as $product) {
			if($i != 0)
				$where .= ", ";

			$where .= $fluid->php_escape_string($product->p_id);

			$i++;
		}
		$where .= ")";

		$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_mfgcode, p.p_images, p.p_mfg_number, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);
		$fluid->php_db_commit();

		$f_items = NULL;
		foreach($fluid->db_array as $value) {
			// Ignoring items that have images already.
			//if(empty($value['p_images']) || $value['p_images'] == '' || $value['p_images'] == "W10=") {
				$f_items[] = $value;
			//}
		}

		$f_fail = 0;
		if(isset($f_items)) {
			require_once('fdom.php');

			$i = 0;
			foreach($f_items as $item) {

				if($data->editortype == 0)
					$f_keywords = str_replace(" ", "+", $item['m_name']) . "+" . $item['p_mfgcode']; // --> Search by Manufacturer name and UPC.
				else if($data->editortype == 2)
					$f_keywords = str_replace(" ", "+", $item['m_name']) . "+" . $item['p_mfg_number']; // --> Search by Manufacturer name and model number..
				else
					$f_keywords = $item['p_mfgcode']; // --> Search by UPC only.

				$f_miller_aus = NULL;

				if($data->region == 0)
					$url = "https://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=" . $f_keywords;
				else if($data->region == 2) {
					$f_keywords = $item['p_mfg_number'];
					$url = "http://www.millercanada.com/advanced_search_result.php?search=1&keywords=" . $f_keywords . "&submit=Search";
				}
				else if($data->region == 3) {
					$f_keywords = $item['p_mfg_number'];
					$url = "https://www.millertripods.com/en/catalogsearch/result/?q=" . $f_keywords;
					$f_miller_aus = $item['p_mfg_number'];
				}
				else if($data->region == 4) {
					//$f_keywords = "%23" . $item['p_mfgcode'];
					if($data->editortype == 0)
						$f_keywords = $item['m_name'] . "%20" . $item['p_mfgcode']; // --> Search by Manufacturer name and UPC.
					else if($data->editortype == 2)
						$f_keywords = $item['m_name'] . "%20" . $item['p_mfg_number']; // --> Search by Manufacturer name and item model number..
					else
						$f_keywords = $item['p_mfgcode']; // --> Search by UPC only.

					$url = "https://www.henrys.com/Search/" . $f_keywords . ".aspx";
				}
				else if($data->region == 5) {
					$f_keywords = $item['p_mfg_number'];
					$url = "http://shop.panasonic.com/search?q=" . $f_keywords;
				}
				else
					$url = "https://www.amazon.ca/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=" . $f_keywords;

				if($i > 0 && $data->region != 2)
					sleep(rand(15,25)); // A timer to help stop making things look like bots.

				$f_image_data = php_search_amazon($url, $data->options, $data->region, $data->override, $f_miller_aus);

				$fluid->php_db_begin();
				// Found some product name.
				if(isset($f_image_data['f_name'])) {
					// Remove the manufacturer name from the string on the first instance only.
					$f_pos = strpos($f_image_data['f_name'], $item['m_name']);
					if($f_pos !== FALSE)
						$f_name = substr_replace($f_image_data['f_name'], "", $f_pos, strlen($item['m_name']));
					else
						$f_name = $f_image_data['f_name'];

					$f_name = trim($f_name);

					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_name` = '" . $fluid->php_escape_string($f_name) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");

				}

				// Found some bullet points.
				if(isset($f_image_data['f_bullets'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_details` = '" . $fluid->php_escape_string($f_image_data['f_bullets']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Found some bullet points.
				if(isset($f_image_data['f_description'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_desc` = '" . $fluid->php_escape_string($f_image_data['f_description']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Found some keywords.
				if(isset($f_image_data['f_keywords'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_keywords` = '" . $fluid->php_escape_string($f_image_data['f_keywords']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Found some whats in the box.
				if(isset($f_image_data['f_inthebox'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_inthebox` = '" . $fluid->php_escape_string($f_image_data['f_inthebox']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Found some dimensions.
				if(isset($f_image_data['f_dimensions'])) {
					if(isset($f_image_data['f_dimensions']['length']) && isset($f_image_data['f_dimensions']['width']) && isset($f_image_data['f_dimensions']['height'])) {
						$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_length` = '" . $fluid->php_escape_string($f_image_data['f_dimensions']['length']) . "', `p_width` = '" . $fluid->php_escape_string($f_image_data['f_dimensions']['width']) . "', `p_height` = '" . $fluid->php_escape_string($f_image_data['f_dimensions']['height']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
					}
				}

				// Found some specs.
				if(isset($f_image_data['f_specs'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_specs` = '" . $fluid->php_escape_string($f_image_data['f_specs']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Found some weight.
				if(isset($f_image_data['f_weight'])) {
					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_weight` = '" . $fluid->php_escape_string($f_image_data['f_weight']) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}

				// Didn't a image find on amazon, search on adorama now.
				/*
				if(empty($f_image_data['image']) && $data->options < 2) {
					$url = "https://www.adorama.com/searchsite/default.aspx?searchinfo=" . $item['m_name'] . "+" . $item['p_mfgcode'];
					$f_image_data['image'] = php_search_adorama($url);
				}
				*/

				// We have a image, lets save it into the database.
				if(isset($f_image_data['image']) && ($data->options < 2 || $data->options == 10)) {
					// --> First lets delete the old images if any exist.
					$f_old_img = json_decode(base64_decode($item['p_images']));
					if(isset($f_old_img)) {
						foreach($f_old_img as $key => $img) {
							if(is_file(FOLDER_IMAGES . $img->file->image))
								unlink(FOLDER_IMAGES . $img->file->image);
						}
					}

					$fluid->php_db_query("UPDATE " . TABLE_PRODUCTS . " SET `p_images` = '" . $fluid->php_escape_string(base64_encode(json_encode($f_image_data['image']))) . "' WHERE p_id = '" . $fluid->php_escape_string($item['p_id']) . "'");
				}
				else
					$fluid->php_debug('no image for: ' . $item['m_name'] . " " . $item['p_mfgcode'] . " - " . $item['p_name'] , TRUE);

				if($f_image_data['f_found'] == FALSE)
					$f_fail++;
				else
					$f_fail = 0;

				$i++;

				$fluid->php_debug($i . " of " . count($f_items) . " downloaded.", TRUE);
				$fluid->php_db_commit();
				// Backup. If failed 15 in a row, drop out of the scanning loop. High chance captcha's stopped us and detected us as a bot.
				if($f_fail > 30 && !$data->region == 4)
					break;
			}
		}

		$modal = "<div class='modal-dialog f-dialog' role='document'>
					<div class='modal-content'>

						<div class='panel-default'>
						  <div class='panel-heading'>Image downloader complete</div>
						</div>

					  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

						<div class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; overflow-y: scroll;'>
							<div>
									<div style='margin-left:10px; margin-right: 10px;'>";
										$modal .= "<div class='alert alert-warning' role='alert'>Process complete. It is recommended to check the images and text data of items you just downloaded.</div>";
									$modal .= "</div>
							";

			$modal .= "</div>
					</div>
				</div>";

			  $modal .= "<div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>
				<div style='float:right;'><button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Ok</button></div>
			  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data']  = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback();
		return php_fluid_error($err);
	}
}

function php_fluid_columns($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$modal = "
		<div class='modal-dialog f-dialog' id='f-column-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Hide/Unhide Columns</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Hide/Unhide Columns:</div>
									<div style='padding-bottom: 10px;'>Select which columns you want to display in item table listings.</div>
								</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";

								$html = "<div style='padding-top: 5px;'>";
									$html .= "<div class=\"input-group\">";
									$html .= "<span class=\"input-group-addon\"><div class='f-notification-text' style='padding-top:3px; height: 20px;'>Columns Selection</div></span>";

										$html .= "<select id='f-columns-select' class='selectpicker' data-container='#fluid-modal' data-size='10' multiple data-actions-box='true' data-selected-text-format='count' title='Select your columns.' data-width='70%' data-live-search='true'>";

											foreach($_SESSION['f_admin_columns'] as $f_key => $f_columns) {
												$selected = NULL;

												switch ($f_columns['data']) {
													case "table-cell":
														$selected = "selected";
													break;
												}

												$html .= "<option value='" . base64_encode(json_encode(Array("cell" => $f_key, "data" => $f_columns['data']))) . "' " . $selected . ">" . $f_columns['column_name'] . "</option>";

											}

										$html .= "</select>";

									$html .= "</div>";
								$html .= "</div>";

							$modal .= $html;

							$modal .= "</div>
						</div>
					</div>

				</div>

				<div class='modal-footer'>
					<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>

					<div style='float:right;'><button onClick='js_fluid_columns_save();' type='button' class='btn btn-success'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>
				</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_columns_save($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$r_data = NULL;
		foreach($f_data->f_columns_array as $f_key => $c_data) {

			$tmp_data = json_decode(base64_decode($c_data));

			$r_data[$tmp_data->cell] = Array("data" => "table-cell", "cell" => $tmp_data->cell);
		}

		foreach($_SESSION['f_admin_columns'] as $f_key => $f_columns) {
			// Show the column as it was selected.
			if(isset($r_data[$f_key])) {
				$_SESSION['f_admin_columns'][$f_key]['data'] = $r_data[$f_key]['data'];
			}
			else {
				// Hide the column as it was deselected.
				$_SESSION['f_admin_columns'][$f_key]['data'] = "none";
			}
		}

		$execute_functions[]['function'] = "js_fluid_columns_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($_SESSION['f_admin_columns']));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_item_filters($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$modal = "
		<div class='modal-dialog f-dialog' id='f-column-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Item Filter</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Filter items:</div>
									<div style='padding-bottom: 10px;'>Select which filters you want to used to display only certain items. Please note that if you select no filters, then all items will display and selecting all filters provides the same results.</div>
								</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";

								$html = "<div style='padding-top: 5px;'>";
									$html .= "<div class=\"input-group\">";
									$html .= "<span class=\"input-group-addon\"><div class='f-notification-text' style='padding-top:3px; height: 20px;'>Item Filter Selection</div></span>";

										$html .= "<select id='f-item-filters-select' class='selectpicker' data-container='#fluid-modal' data-size='10' multiple data-actions-box='true' data-selected-text-format='count' title='Select your filters.' data-width='70%' data-live-search='true'>";

											foreach($_SESSION['f_admin_item_filters'] as $f_key => $f_filter) {
												$selected = NULL;

												switch ($f_filter['data']) {
													case TRUE:
														$selected = "selected";
													break;
												}

												$html .= "<option value='" . base64_encode(json_encode(Array("f_key" => $f_key, "data" => $f_filter['data']))) . "' " . $selected . ">" . $f_filter['filter_name'] . "</option>";

											}

										$html .= "</select>";

									$html .= "</div>";
								$html .= "</div>";

							$modal .= $html;

							$modal .= "</div>
						</div>
					</div>

				</div>

				<div class='modal-footer'>
					<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>

					<div style='float:right;'><button onClick='js_fluid_item_filters_save();' type='button' class='btn btn-success'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>
				</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_item_filters_save($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$r_data = NULL;

		foreach($f_data->f_filters_array as $f_key => $c_data) {
			$tmp_data = json_decode(base64_decode($c_data));

			$r_data[$tmp_data->f_key] = Array("data" => TRUE, "filter" => $tmp_data->f_key);

		}

		// Reset all filters to active. Query's will ignore the filters if all active.
		$_SESSION['f_admin_item_filters_enabled'] = FALSE;
		$f_query_tmp = NULL;
		$f_prev_column = NULL;
		$i = 0;
		foreach($_SESSION['f_admin_item_filters'] as $f_key => $f_filters) {
			// Show items based on this filter as it was selected.
			if(isset($r_data[$f_key])) {
				$_SESSION['f_admin_item_filters'][$f_key]['data'] = $r_data[$f_key]['data'];

				if($i > 0) {
					if($f_prev_column == $_SESSION['f_admin_item_filters'][$f_key]['column']) {
						$f_query_tmp .= " OR ";
					}
					else {
						$f_query_tmp .= ") AND (";
					}
				}
				else if($i == 0) {
					$f_query_tmp = "(";
				}


				$f_query_tmp .= $_SESSION['f_admin_item_filters'][$f_key]['query'];

				$f_prev_column = $_SESSION['f_admin_item_filters'][$f_key]['column'];

				$i++;
			}
			else {
				// We will be not using this filter as it was deselected.
				$_SESSION['f_admin_item_filters'][$f_key]['data'] = FALSE;
				$_SESSION['f_admin_item_filters_enabled'] = TRUE; // This tells the query system that we will be using some filters now.
			}
		}

		$f_query_tmp .= ")";

		// Looks like all filters were deselected. Lets show all items now.
		if($i == 0) {
			$_SESSION['f_admin_item_filters_enabled'] = FALSE;
		}

		$_SESSION['f_admin_item_filters_query'] = $f_query_tmp;

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_fluid_item_mode";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}
?>
