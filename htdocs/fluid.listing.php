<?php
// fluid.listing.php
// Michael Rajotte - 2016 Jun
// Listing page.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

function php_listing_display_update($f_data) {
	try {
		if(empty($f_data))
			$_SESSION['fluid_listing_display'] = 0;
		else if(empty($f_data->mode))
			$_SESSION['fluid_listing_display'] = 0;
		else {
			if($f_data->mode != 0 && $f_data->mode != 1)
				$_SESSION['fluid_listing_display'] = 0;
			else
				$_SESSION['fluid_listing_display'] = $f_data->mode;
		}

		$f_search = json_decode(base64_decode($f_data->f_search));

		if($f_search->f_search == TRUE) {
			$f_tmp_input = "<input id='fluid-search-input-tmp' type='hidden' style='display: none;' value='" . base64_decode($f_search->f_keys) . "'/>";

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-helper-div"), "html" => base64_encode($f_tmp_input))));

			$execute_functions[]['function'] = "js_fluid_search";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("fluid-search-input-tmp"));
		}
		else {
			$execute_functions[]['function'] = "js_redirect_url";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("url" => $f_data->url)));
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_search($data_obj = NULL) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$query = NULL;

		if(isset($data_obj->f_search)) {
			$f_write = TRUE;
			// --> Search spam time. Set to 3 second intervals, more than 3 and it doesn't record to prevent database spamming.
			if(isset($_SESSION['fluid_search']['fluid_last_search_time'])) {
				if(time() - $_SESSION['fluid_search']['fluid_last_search_time'] < 3) {
					$_SESSION['fluid_search']['fluid_last_search_time'] = time();
					$f_write = FALSE;;
				}
				else
					$_SESSION['fluid_search']['fluid_last_search_time'] = time();
			}
			else
				$_SESSION['fluid_search']['fluid_last_search_time'] = time();

			if($data_obj->item_page > 1)
				$f_write = TRUE;

			if($f_write == TRUE) {
				$query = trim($data_obj->f_search);
				$f_save = TRUE;

				if(strpos($query, "SELECT") !== false) {
					$f_save = FALSE;
				}
				else if(strpos($query, "union select") !== false) {
					$f_save = FALSE;
				}

				if(strlen($query) > 100) {
					$f_save = FALSE;
				}

				if($f_save == TRUE) {
					$fluid->php_db_query("INSERT INTO " . TABLE_LOGS . " (l_type, l_query) VALUES ('search', '" . $fluid->php_escape_string($query) . "')");
				}
			}
		}

		$query = $fluid->php_limit_chars(trim($data_obj->f_search));

		//if (mb_strlen($query)===0){
			// no need for empty search right?
			//return false;
		//}

		// Weighing scores
		$scoreFullUPC = 50; // 200
		$scoreFullTitle = 3; // 50
		$scoreFullTitleKeyword = 3; // 50
		$scoreStock = 0;
		$scoreTitleKeyword = 3;
		$scoreFullSummary = 4;
		$scoreSummaryKeyword = 3;
		$scoreFullDocument = 2;
		$scoreDocumentKeyword = 4;
		$scoreCategoryKeyword = 4;
		$scoreUrlKeyword = 2;
		$score0 = 0;
		$score1 = 1;
		$score2 = 2;
		$score3 = 3;
		$score4 = 4;
		$score5 = 5;
		$score6 = 6;
		$score7 = 7;
		$score8 = 8;
		$score9 = 9;
		$score10 = 10;

		$m_keywords = $fluid->php_escape_string($query);
		$keywords = $fluid->php_filter_search_keys($query);
		$escQuery = $fluid->php_escape_string($query);
		$titleSQL = array();
		$sumSQL = array();
		$docSQL = array();
		//$categorySQL = array();
		$urlSQL = array();
		$keywordsSQL = array();
		$stockSQL = array();

		// Matching full occurences
		if(count($keywords) > 0) {
			$upcSQL[] = "if (p_mfgcode LIKE '%".$escQuery."%',{$scoreFullUPC},0)";

			if(strlen($escQuery) > 6)
				$titleSQL[] = "if (p_name LIKE '%".$escQuery."%',{$scoreFullTitle},0)";

			$sumSQL[] = "if (m_name LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
			$keywordsSQL[] = "if (p_keywords LIKE '%".$escQuery."%',{$scoreFullTitleKeyword},0)";
			$stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";
		}

		if(strlen($m_keywords) > 3) {
			$urlSQL[] = "if (c_keywords LIKE'%".$m_keywords."%',{$score9},0)";
			$docSQL[] = "if (c_name LIKE '%".$m_keywords."%',{$scoreFullDocument},0)";
		}

		// Matching Keywords
		$i = 0;
		foreach($keywords as $key) {
			if(strlen($key) > 2) {
				//$upcSQL[] = "if (p_mfgcode LIKE '%".$fluid->php_escape_string($key)."%',{$scoreFullUPC},0)";
				//$titleSQL[] = "if (p_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreTitleKeyword},0)";
				$sumSQL[] = "if (m_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreSummaryKeyword} + (c_search_weight / 2),0)";
				if($i == 0)
					$c_name_score = $score3;
				else
					$c_name_score = $score2;

				$docSQL[] = "if (c_name LIKE '%". $fluid->php_escape_string($key)."%',{$c_name_score} + (c_search_weight / 2),0)";
				if($i == 0)
					$c_keywords_score = $score3;
				else if($i == 1)
					$c_keywords_score = $score2;
				else if($i == 2)
					$c_keywords_score = $score2;
				else
					$c_keywords_score = $score1;

				$urlSQL[] = "if (c_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$keywordsSQL[] = "if (p_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";

				$i++;
			}
		}

		// Just incase it is empty, then add 0.
		if(empty($upcSQL))
			$upcSQL[] = 0;
		if(empty($titleSQL))
			$titleSQL[] = 0;
		if(empty($sumSQL))
			$sumSQL[] = 0;
		if(empty($docSQL))
			$docSQL[] = 0;
		if(empty($urlSQL))
			$urlSQL[] = 0;
		if(empty($tagSQL))
			$tagSQL[] = 0;
		if(empty($keywordsSQL))
			$keywordsSQL[] = 0;
		if(empty($stockSQL))
			$stockSQL[] = 0;

		if(isset($data_obj->item_page)) {
			$item_page = $data_obj->item_page;

			if(FLUID_LISTING_INFINITE_SCROLLING == FALSE) {
				if(isset($data_obj->reload))
					if($data_obj->reload == TRUE)
						$item_page--;
			}

			$item_start = ($item_page - 1) * VAR_LISTING_MAX;  // The first item to display on this page.
		}
		else {
			$item_page = 0;
			$item_start = 0; // If no item_page var is given, set start to 0.
		}

		// Set up the sort order.
		$sort_by = "relevance DESC, c_search_weight DESC";
		if(isset($data_obj->sort_by))
			$sort_by = php_sort_by($data_obj->sort_by);

		$order_by = "ORDER BY " . $sort_by . " LIMIT " . $item_start . "," . VAR_LISTING_MAX;

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$f_item_start = $item_start;
			if(isset($data_obj->reload)) {
				if($data_obj->reload == TRUE) {
					$f_end = $item_start + VAR_LISTING_MAX;
					$f_item_start = 0;
					$order_by = "ORDER BY " . $sort_by . " LIMIT " . $f_item_start . "," . $f_end;

				}
			}
		}

		$query_search = "
				(
					(-- UPC/EAN score
					".implode(" + ", $upcSQL)."
					)+
					(-- item name
					".implode(" + ", $titleSQL)."
					)+
					(-- item keywords
					".implode(" + ", $keywordsSQL)."
					)+
					(-- manufacturer name
					".implode(" + ", $sumSQL)."
					)+
					(-- category name
					".implode(" + ", $docSQL)."
					)+
					(-- category keywords
					".implode(" + ", $urlSQL)."
					)+
					(-- -p_stock
					".implode(" + ", $stockSQL)."
					)
				) as relevance
				FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id
				WHERE
				";

		$query_search_stock = " p.p_enable > '0' AND c.c_enable = 1";

		// --> Only show products that have stock or a arrival date or discount date ending in the future.
		$query_search_zero_stock = NULL;
		if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 1) {
			$s_date = date("Y-m-d 00:00:00");
			$query_search_stock .= " AND (p_stock > 0 OR p_showalways > 0 OR (p_newarrivalenddate >= '" . $s_date . "' OR p_discount_date_end >= '" . $s_date . "') OR (p_date_hide > '" . $s_date . "'))";
		}
		else {
			// --> Since we are showing all products in or not in stock. We need to filter out zero stock items that are set to hide when out of stock.
			if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 2) {
				$query_search_stock = " ((p.p_enable = '1' AND c.c_enable = 1) OR (p.p_enable = '2' AND p.p_stock > 0 AND c.c_enable = 1))";
			}

			$query_search_zero_stock = " AND p_zero_status_tmp > 0";
		}

		$query_search .= $query_search_stock;

		// Lets get a item count and determine where we start and where we end on items to show.
		/*
		$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, " . $query_search . " GROUP BY relevance HAVING relevance > 0");

		$total_items = 0;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $f_tmp_array)
				$total_items = $total_items + $f_tmp_array['total'];
		}
		else
			$total_items = 0;
		*/

		$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, " . $query_search . " GROUP BY relevance, p_zero_status_tmp HAVING relevance > 0 AND p_zero_status_tmp > 0");

		$html = NULL;

		if(isset($data_obj->last_id)) {
			$last_id = $data_obj->last_id;
		}
		else {
			$last_id = 0;
		}

		$bool_found_items = FALSE;
		$i_item_count = 0;
		$total_items = 0;
		$f_total_bundles = 0;
		if(isset($fluid->db_array)) {

			$sort_col = array();
			foreach ($fluid->db_array as $key=> $row)
				$sort_col[$key] = $row['relevance'];

			array_multisort($sort_col, SORT_DESC, $fluid->db_array);

			$highest_relevance = $fluid->db_array[0]['relevance'] / FLUID_SEARCH_RELEVANCE;

			foreach($fluid->db_array as $f_key => $data) {
				if($data['relevance'] <= $highest_relevance)
					unset($fluid->db_array[$f_key]);
			}

			$total_items = 0;
			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $f_tmp_array)
					$total_items = $total_items + $f_tmp_array['total'];
			}
			else
				$total_items = $i_item_count;

			$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent, " . $query_search . " GROUP BY p.p_id HAVING relevance >= " . $highest_relevance . $query_search_zero_stock . " " . $order_by);

			$f_items = NULL;
			$f_bundle_select = NULL;
			$f_bundle_count = 0;

			if(isset($fluid->db_array)) {
				// First check if the current returned items belongs to any bundles.
				$fluid_search = new Fluid();
				$fluid_search->php_db_begin();
				$f_tmp = 0;
				$f_search_bund = NULL;
				$f_search_bund_array = NULL;
				$f_search_data_array = NULL;

				//SELECT * FROM `products` WHERE `p_formula_items_data` LIKE '%NjE5NjU5MDY2Nzcy%' OR `p_formula_items_data` LIKE '%FdfdfdssdSDF%' ORDER BY `p_id` ASC
				foreach($fluid->db_array as $data) {
					if($f_tmp > 0) {
						$f_search_bund .= " OR ";
					}

					$f_search_bund .= "`p_formula_items_data` LIKE '%" . $fluid_search->php_escape_string(base64_encode($data['p_mfgcode'])) . "%'";

					$f_tmp++;
				}

				if(isset($f_search_bund)) {
					$fluid_search->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE p.p_enable > '0' AND c.c_enable = 1 AND (" . $f_search_bund . ") GROUP BY p.p_id ORDER BY p.p_id");

					if(isset($fluid_search->db_array)) {
						foreach($fluid_search->db_array as $b_data) {
							$f_search_bund_array[$b_data['p_id']] = $b_data;
							$f_search_bund_array[$b_data['p_id']]['f_delete_item'] = TRUE;
						}
					}
				}

				$fluid_search->php_db_commit();

				if(isset($f_search_bund_array)) {
					if(isset($fluid->db_array)) {
						foreach($fluid->db_array as $fs_data) {
							$f_search_data_array[$fs_data['p_id']] = $fs_data;
						}

						foreach($f_search_bund_array as $fs_data) {
							$f_search_data_array[$fs_data['p_id']] = $fs_data;
						}
					}
				}
				else {
					$f_search_data_array = $fluid->db_array;
				}

				foreach($f_search_data_array as $data) {
					//$last_id = $data['p_sortorder'];
					//$bool_found_items = TRUE;
					//$i_item_count++;

					//$data['position'] = $i_item_count + 1;
					if($data['p_stock'] < 1) {
						$data['p_enable'] = $data['p_zero_status'];
					}

					// Scan the $data item, look for FORMULA_OPTION_8, then generate a promotional item as required.
					if($data['p_formula_status'] == 1 && ($data['p_formula_operation'] == FORMULA_OPTION_8 || $data['p_formula_operation'] == FORMULA_OPTION_9) && ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) )) {

						$f_b_items = json_decode($data['p_formula_items_data']);

						$f_array = NULL;
						foreach($f_b_items as $f_idata) {
							$f_array[base64_decode($f_idata)] = base64_decode($f_idata);

							if(count($f_array) > 1 || $f_bundle_count > 0)
								$f_bundle_select .= ", ";

							$f_bundle_select .= "'" . $fluid->php_escape_string(base64_decode($f_idata)) . "'";

							$f_bundle_count++;
						}

						$f_items[] = Array("data" => $data, "bundle_items" => $f_array);
					}
					else {
						$f_items[] = Array("data" => $data);
					}
				}

				$f_bundle_items = NULL;
				// Any bundles, lets grab them.
				if(isset($f_bundle_select)) {
					//$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE p.p_enable > '0' AND c.c_enable = 1 AND p_mfgcode IN (" . $f_bundle_select . ") HAVING p_zero_status_tmp > 0 ORDER BY p_mfgcode ASC");
					$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE p.p_enable > '0' AND c.c_enable = 1 AND p_mfgcode IN (" . $f_bundle_select . ") ORDER BY p_mfgcode ASC");

					if(isset($fluid->db_array)) {
						foreach($fluid->db_array as $f_b_items) {
							$f_bundle_items[$f_b_items['p_mfgcode']] = $f_b_items;
						}
					}
				}

				foreach($f_items as $f_data) {
					$last_id = $data['p_sortorder'];
					$bool_found_items = TRUE;
					$i_item_count++;

					$f_data['data']['position'] = $i_item_count + 1;

					// Do not show this item.
					if(empty($f_data['data']['f_delete_item'])) {
						$html .= php_html_item_card($f_data['data']);
					}

					if(isset($f_data['bundle_items'])) {
						foreach($f_data['bundle_items'] as $fb_item_key) {
							if(isset($f_bundle_items[$fb_item_key])) {
								$fb_item = NULL;
								$fb_item = $f_bundle_items[$fb_item_key];

								//$i_item_count++;
								//$fb_item['position'] = $i_item_count + 1;
								//$fb_item['position']++;
								$f_org_tmp = $f_data['data'];

								// FORMULA_OPTION_9 makes the original items have ignore the regular discounts.
								if($f_org_tmp['p_formula_operation'] == FORMULA_OPTION_9) {
									$f_org_tmp['p_price_discount'] = "";
									$f_org_tmp['p_discount_date_end'] = "";
									$f_org_tmp['p_discount_date_start'] = "";


									if($fb_item['p_price_discount'] > 0 && ((strtotime($fb_item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($fb_item['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($fb_item['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $fb_item['p_discount_date_end'] == NULL) || ($fb_item['p_discount_date_start'] == NULL && $fb_item['p_discount_date_end'] == NULL))) {
										// Do nothing
									}
									else {
										$fb_item['p_price_discount'] = "";
									}
								}

								$fb_item['p_bundle_item'] = $f_org_tmp;

								$fb_item['p_discount_date_start'] = $fb_item['p_bundle_item']['p_formula_discount_date_start'];
								$fb_item['p_discount_date_end'] = $fb_item['p_bundle_item']['p_formula_discount_date_end'];

								// --> Lets see if we need to flip some data around.
								if($f_org_tmp['p_formula_flip'] == 1) {
									if($fb_item['m_id'] != $f_org_tmp['m_id']) {
										$fb_item['p_name'] = $f_org_tmp['m_name'] . " " . $f_org_tmp['p_name'] . " w/" . $fb_item['m_name'] . " " . $fb_item['p_name'];
									}
									else {
										$fb_item['p_name'] = $f_org_tmp['p_name'] . " w/" . $fb_item['p_name'];
									}
								}
								else {
									if($fb_item['m_id'] != $f_org_tmp['m_id']) {
										$fb_item['p_name'] .= " w/" . $f_org_tmp['m_name'] . " " . $f_org_tmp['p_name'];
									}
									else {
										$fb_item['p_name'] .= " w/" . $f_org_tmp['p_name'];
									}
								}

								$fb_item['p_images'] = base64_encode(json_encode((object) array_merge((array) json_decode(base64_decode($fb_item['p_images'])), (array) json_decode(base64_decode($f_org_tmp['p_images'])))));

								$html .= php_html_item_card($fb_item);

								$f_total_bundles++;
							}
						}
					}
					else if(isset($f_data['data']['f_delete_item'])) {
						$html .= php_html_item_card($f_data['data']);
					}
				}
			}
			else {
				$html .= "<div class='row row-product-not-found-container'>";
					$html .= "<p class='product-not-found-text'>No products found.</p>";
				$html .= "</div>";

				$total_items = 0;
			}

		}
		else {
			$html .= "<div class='row row-product-not-found-container'>";
				$html .= "<p class='product-not-found-text'>No products found.</p>";
			$html .= "</div>";

			$total_items = 0;
		}

		$fluid->php_db_commit();

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$f_item_start = $f_item_start + 1;
			$new_item_count = $i_item_count + $data_obj->item_count;
			if(isset($data_obj->reload))
				if($data_obj->reload == TRUE)
					$new_item_count = $i_item_count;

			$new_item_count_tmp = $new_item_count + $f_total_bundles;
			$total_items_tmp = $total_items + $f_total_bundles;

			$listing_counter_html = $new_item_count_tmp . " of " . $total_items_tmp;
		}
		else {
			$new_item_count = $i_item_count;

			$f_total_list = $item_start + $new_item_count;

			$f_item_start = $item_start + 1;

			$total_items_tmp = $f_total_bundles + $total_items;
			$f_total_list_tmp = $f_total_list + $f_total_bundles;

			//$listing_counter_html = $f_item_start + $f_total_bundles . " - " . $f_total_list + $f_total_bundles . " <div style='display: inline-block; font-size: 80%;'>of</div> " . $total_items + $f_total_bundles;

			$listing_counter_html = $f_item_start . " - " . $f_total_list_tmp . " <div style='display: inline-block; font-size: 80%;'>of</div> "  . $total_items_tmp;
		}

		$f_item_list = NULL;
		$f_ga_pos = $f_item_start;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $f_items_tmp) {
				$f_item_list[] = Array("p_id" => $f_items_tmp['p_id'], "p_mfgcode" => $f_items_tmp['p_mfgcode'], "p_mfg_number" => $f_items_tmp['p_mfg_number'], "p_mfgid" => $f_items_tmp['p_mfgid'], "p_name" => $f_items_tmp['p_name'], "p_price" => $f_items_tmp['p_price'], "m_name" => $f_items_tmp['m_name'], "p_catid" => $f_items_tmp['p_catid'], "m_id" => $f_items_tmp['m_id'], "p_mfgid" => $f_items_tmp['p_mfgid'], "c_name" => $f_items_tmp['c_name'], "p_position" => $f_ga_pos);

				$f_ga_pos++;
			}
		}

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$execute_functions[]['function'] = "js_html_insert";
			$execute_functions[]['function'] = "js_fluid_listing_update";

			return json_encode(array("html" => base64_encode(utf8_decode($html)), "listing_counter_html" => base64_encode($listing_counter_html), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("container-fluid-listing"), "item_count" => base64_encode($new_item_count), "item_page" => base64_encode($data_obj->item_page), "item_page_next" => base64_encode($data_obj->item_page + 1), "item_page_previous" => base64_encode($data_obj->item_page - 1), "item_start" => base64_encode($item_start), "total_items" => base64_encode($total_items), "bool_found_items" => $bool_found_items, "item_list" => base64_encode(json_encode($f_item_list)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
		else {
			$mode = "f-listing";
			$f_pagination_function = "js_fluid_listing_page_change";

			$f_pagination_html = "<div class='f-pagination'>" . $fluid->php_pagination($total_items, FLUID_ADMIN_PAGINATION_LIMIT, $item_page, $f_pagination_function, $mode, NULL, "FluidListing.item_page") . "</div>";

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("container-fluid-listing"), "html" => base64_encode($html))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("f-pagination-bottom"), "html" => base64_encode($f_pagination_html))));

			$execute_functions[]['function'] = "js_fluid_listing_update";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("listing_counter_html" => base64_encode($listing_counter_html), "div_id" => base64_encode("container-fluid-listing"), "item_count" => base64_encode($new_item_count), "item_page" => base64_encode($data_obj->item_page), "item_page_next" => base64_encode($data_obj->item_page + 1), "item_page_previous" => base64_encode($data_obj->item_page - 1), "item_start" => base64_encode($item_start), "total_items" => base64_encode($total_items), "bool_found_items" => $bool_found_items, "item_list" => base64_encode(json_encode($f_item_list)))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return $err;
	}
}

function php_sort_by($data, $sort_discount = NULL) {
	switch ($data) {
		case "relevance":
			$sort_by = "relevance DESC, c_search_weight DESC";
			break;
		case "featured":
			if(isset($sort_discount))
				$sort_by = "fluid_stock DESC, fluid_discount_percent DESC";
			else
				$sort_by = "fluid_stock DESC";
			break;
		case "price_low_high":
			if(isset($sort_discount))
				$sort_by = "fluid_price_discount ASC";
			else
				$sort_by = "p_price ASC";
			break;
		case "price_high_low":
			if(isset($sort_discount))
				$sort_by = "fluid_price_discount DESC";
			else
				$sort_by = "p_price DESC";
			break;
		case "brand_a_z":
			$sort_by = "m_name ASC";
			break;
		case "brand_z_a":
			$sort_by = "m_name DESC";
			break;
		case "deals":
			$sort_by = "(CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) DESC";

			$sort_by .= ", (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC";

			$sort_by .= ", (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC,(CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC";

			$sort_by .= ", (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END) DESC";

			$sort_by .= ", (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC";

			$sort_by .= ", (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC, (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) DESC, fluid_stock DESC";
			break;
		default:
			if(isset($sort_discount))
				$sort_by = "fluid_stock DESC, fluid_discount_percent DESC";
			else
				$sort_by = "fluid_stock DESC";
	}

	return $sort_by;
}

function php_category_data($cid_64) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("SELECT c.c_id, c.c_enable, c.c_name, c.c_parent_id, c.c_seo, (SELECT c2.c_name FROM " . TABLE_CATEGORIES . " c2 WHERE c2.c_id = c.c_parent_id) AS c_parent_name FROM " . TABLE_CATEGORIES . " c WHERE c.c_id = '" . $fluid->php_escape_string(base64_decode($cid_64)) . "' LIMIT 1");

		$cat_data = NULL;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $data) {
				$cat_data = $data;
			}
		}

		$fluid->php_db_commit();

		return $cat_data;
	}
	catch (Exception $err) {
		return $err;
	}
}

function php_html_filters($data_obj = NULL) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$return = "<div class='panel-group' id='accordion' role='tablist' aria-multiselectable='true'>";

		// Lets build the manufacturer filter listing. We are only showing manufacturers who are active and are within these categories.
		if(base64_decode($data_obj->cat_id) == "all" || (base64_decode($data_obj->cat_id) == "blackfriday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweekend" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweek" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "cybermonday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "boxingweek" && FLUID_BLACK_FRIDAY == TRUE)) {
			$filter_where = " AND ( (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END)";

			$filter_where = " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";

			$fluid->php_db_query("SELECT DISTINCT p.p_mfgid, m.m_name, m.m_sortorder FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1" . $filter_where . " ORDER BY m.m_sortorder ASC");
		}
		else if(base64_decode($data_obj->cat_id) == "bundles") {
			$filter_where = " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";

			$fluid->php_db_query("SELECT DISTINCT p.p_mfgid, m.m_name, m.m_sortorder FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1" . $filter_where . " ORDER BY m.m_sortorder ASC");
		}
		else if(base64_decode($data_obj->cat_id) == "sigma") {
			$filter_where .= " AND (p.p_mfg_number IN ('A1224DGHC', 'A1224DGHN', 'A1224DGHS', 'A14DGHC', 'A14DGHN', 'A14DGHS', 'A1835DCHC', 'A1835DCHN', 'A1835DCHS', 'A1835DCHP', 'A1835DCHAS', 'A20DGHC', 'A20DGHN', 'A20DGHS', 'A24DGHN', 'A24DGHC', 'A2435DGHN', 'A2435DGHS', 'AOS2470DGC', 'AOS2470DGN', 'AOS2470DGS', 'AOS24105C', 'AOS24105N', 'AOS24105S', 'AAF24105AS', 'A30DCHC', 'A30DCHN', 'A30DCHS', 'A30DCHP', 'A30DCHAS', 'A35DGC', 'A35DGN', 'A35DGS', 'A35DGP', 'A35DGAS', 'A50DGHC', 'A50DGHS', 'A50DGHN', 'A50DGHAS', 'A50100DCHC', 'A50100DCHN', 'A50100DCHS', 'A85DGHC', 'A85DGHN', 'COS1004DGC', 'COS1004DGN', 'COS1004DGS', 'SOS1203DGC', 'SOS1203DGN', 'SOS1203DGS', 'A135DGHC', 'A135DGHN', 'A135DGHS', 'COS1506DGC', 'COS1506DGN', 'COS1506DGS', 'SOS1506DGC', 'SOS1506DGN', 'SOS1506DGS'))";

			$fluid->php_db_query("SELECT DISTINCT p.p_mfgid, m.m_name, m.m_sortorder FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1" . $filter_where . " ORDER BY m.m_sortorder ASC");
		}
		else {
			$fluid->php_db_query("SELECT DISTINCT p.p_mfgid, m.m_name, m.m_sortorder FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE (c.c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "' OR c.c_parent_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "') AND p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1 ORDER BY m.m_sortorder ASC");
		}

		if(isset($fluid->db_array)) {
			$return .= "<div class='panel panel-default'>";
				$return .= "<div class='panel-heading' role='tab' style='border-bottom: 1px solid #DDDDDD;'>";
					$return .= "<div class='panel-title'>";
						$return .= "<div class='fluid-filter-title' role='button' data-parent='#accordion' href='#filter_brands' aria-expanded='true' aria-controls='filter_brands'>Brands</div>"; // To make it collapse, add data-toggle='collapse'
					$return .= "</div>";
				$return .= "</div>"; // panel-heading
												  //class='panel-collapse collapse in'
				$return .= "<div id='filter_brands' class='panel-collapse collapse in' role='tabpanel' aria-labelledby='filter_brands'>";
					$return .= "<div class='panel-body fluid-filter-box'>";

						$f_mfg_array = NULL;
						if(isset($data_obj->Filters_mfg)) {
							if(is_object($data_obj->Filters_mfg)) {
								foreach($data_obj->Filters_mfg as $f_mfg) {
									$f_mfg_array[base64_decode($f_mfg)] = base64_decode($f_mfg);
								}
							}
						}

						foreach($fluid->db_array as $data) {
							//$return .= "<div class='checkbox'><label><input type='checkbox' id='filter-brand-check' value='" . base64_encode($data['p_mfgid']) . "' onchange='js_filter_mfg_check(this);'></label>" . $data['m_name'] . "</div>";
							$return .= "<div class=\"checkbox\"><label class='filter-checkbox'>";

							if(isset($f_mfg_array[$data['p_mfgid']]))
								$f_checked = " checked";
							else
								$f_checked = NULL;

							$return .= "<input type=\"checkbox\" value='" . base64_encode($data['p_mfgid']) . "'" . $f_checked . " onchange='js_filter_mfg_check(this);'>";

							$return .= "<span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span><div class='filter-font'>" . $data['m_name'] . "</div></input></label></div>";
						}
					$return .= "</div>"; // panel-body
				$return .= "</div>";  // panel-collapse
			$return .= "</div>"; // panel panel-default
		}

		// Build specific category filters if they exist.
		$fluid->php_db_query("SELECT c.* FROM " . TABLE_CATEGORIES . " c WHERE c.c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "'");

		$filter_array = NULL;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $data) {
				$filters_obj = json_decode(base64_decode($data['c_filters']));

				foreach($filters_obj as $key => $filt_obj) {
					$filter_array[$key]['filter_name'] = base64_decode($filt_obj->filter_name);
					$filter_array[$key]['filter_order'] = $filt_obj->filter_order;
					$filter_array[$key]['filter_id'] = $filt_obj->filter_id;
					$filter_array[$key]['sub_filters_obj'] = $filt_obj->sub_filters;
				}
			}

		}

		if(isset($filter_array)) {
			$i = 0;
			$expanded = "false"; //$expanded = "true";
			$class = "fluid-filter-title";
			//$in = NULL;
			$in = " in"; // keep it expanded.
			foreach($filter_array as $key => $data_value) {
				// filter_name, filter_order, filter_id, sub_filter_obj
				$return .= "<div class='panel panel-default'>";

					$return .= "<div class='panel-heading' role='tab' style='border-bottom: 1px solid #DDDDDD;'>";
						$return .= "<div class='panel-title'>";
							$return .= "<div class='" . $class . "' role='button' data-parent='#accordion' href='#filter_" . $i . "' aria-expanded='" . $expanded . "' aria-controls='filter_" . $i . "'>" . $data_value['filter_name'] . "</div>"; // To make it collapse, add data-toggle='collapse'
						$return .= "</div>";
					$return .= "</div>"; // panel-heading

					$return .= "<div id='filter_" . $i . "' class='panel-collapse collapse" . $in . "' role='tabpanel' aria-labelledby='filter_" . $i . "'>";
						$return .= "<div class='panel-body fluid-filter-box'>";

						$f_filters_array = NULL;
						if(isset($data_obj->Filters))
							$f_filters_array = json_decode(json_encode($data_obj->Filters), TRUE);

							if(isset($data_value['sub_filters_obj'])) {
								foreach($data_value['sub_filters_obj'] as $sub_key => $value) {
									// sub_name, sub_id
									$return .= "<div class=\"checkbox\">";

									$f_filters_data = base64_encode(json_encode(Array('filter_id' => $data_value['filter_id'], 'sub_id' => $value->sub_id)));

									if(isset($f_filters_array[$f_filters_data]))
										$f_filters_checked = " checked";
									else
										$f_filters_checked = NULL;

									$return .= "<label class='filter-checkbox'><input type=\"checkbox\" id='filter-array-check' value='" . $f_filters_data . "'" . $f_filters_checked . " onchange='js_filter_check(this);'>";

									$return .= "<span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span><div class='filter-font'>" . base64_decode($value->sub_name) . "</div></label></div>";
								}
							}
						$return .= "</div>"; // panel-body
					$return .= "</div>";  // panel-collapse
				$return .= "</div>"; // panel panel-default

				$i++;
				$expanded = "false";
				$class .= " collapsed";
				//$in = NULL;
			}
		}

		// Lets build the price filter listing.
		$fluid->php_db_query("SELECT p.p_price FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE (c.c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "' OR c.c_parent_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "') AND p.p_enable > 0 AND c.c_enable = 1 AND m.m_enable = 1 ORDER BY p.p_price DESC");

		if(isset($fluid->db_array)) {
			$return .= "<div class='panel panel-default'>";
				$return .= "<div class='panel-heading' role='tab' style='border-bottom: 1px solid #DDDDDD;'>";
					$return .= "<div class='panel-title'>";
						$return .= "<div class='fluid-filter-title' role='button' data-parent='#accordion' href='#filter_price' aria-expanded='true' aria-controls='filter_price'>Price Range</div>"; // data-toggle='collapse'
					$return .= "</div>";
				$return .= "</div>"; // panel-heading

				$return .= "<div id='filter_price' class='panel-collapse collapse in' role='tabpanel' aria-labelledby='filter_price'>";
					$return .= "<div class='panel-body fluid-filter-box'>";
						$price_array = NULL;
						foreach($fluid->db_array as $data)
							$price_array[] = $data['p_price'];

						$groups = Array();
						$i = 0;
						$groups[] = reset($price_array);
						while($i < count($price_array)) {
							if(end($groups) - FLUID_LISTING_PRIX_JUMPS > $price_array[$i])
								$groups[] = $price_array[$i];

							$i++;
						}

						$f_price_array = NULL;
						if(isset($data_obj->Filters_price))
							$f_price_array = json_decode(json_encode($data_obj->Filters_price), TRUE);

						$groups = array_reverse($groups); // Reverse the array. A bit processor intensive, but it is ok on small arrays such as this..
						foreach($groups as $key => $data) {
							if($data < FLUID_LISTING_PRIX_JUMPS)
								$low_num = number_format(0.00, 2, ".", "");
							else {
								if(isset($groups[$key - 1]))
									$low_num = (($groups[$key - 1]) + 0.01);
								else
									$low_num = $groups[$key] + 0.01;
							}

							$return .= "<div class=\"checkbox\"><label class='filter-checkbox'>";

							$f_price_data = base64_encode(json_encode(Array("low" => $low_num, "high" => $data)));

							if(isset($f_price_array[$f_price_data]))
								$f_price_checked = " checked";
							else
								$f_price_checked = NULL;

							$return .= "<input type=\"checkbox\" id='filter-price-check' value='" . $f_price_data . "'" . $f_price_checked . " onchange='js_filter_price_check(this);'>";

							$return .= "<span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span><div class='filter-font'>" . utf8_encode(HTML_CURRENCY) . number_format($low_num, 2, ".", "") . " - " .  utf8_encode(HTML_CURRENCY) . number_format($data, 2, ".", "") . "</div></label></div>";
						}

					$return .= "</div>"; // panel-body
				$return .= "</div>";  // panel-collapse
			$return .= "</div>"; // panel panel-default
		}

		$return .= "</div>"; // panel-group

		$fluid->php_db_commit();

		return json_encode(array("html" => base64_encode(utf8_decode($return)), "error" => 0, "error_message" => base64_encode("none")));
	}
	catch (Exception $err) {
		return base64_encode($err);
	}
}

// HTML for the item cards.
function php_html_item_card($data) {
	$fluid = new Fluid ();
	$p_images = $fluid->php_process_images($data['p_images']);
	$return = NULL;

	if(empty($_SESSION['fluid_listing_display']))
		$_SESSION['fluid_listing_display'] = 0;

	// 0 --> default view, horizontal card.
	// 1 --> top down view vertical card.
	if($_SESSION['fluid_listing_display'] == 0) {
		$f_grid_class = "fluid-item-data fluid-item-grid-long";
		$f_split_image_class = "fluid-item-split-image-div";
		$f_split_class = "fluid-item-split-div";
		$f_split_image = "img-responsive";
		$f_buttons_class = "fluid-listing-item-buttons-h";
		$f_btn_container = "fluid-btn-container-width-h";
		$f_btn_container_s = $f_btn_container;
		$f_btn_mode = "fluid-btn-md";
		$f_item_extra = " fluid-item-extra-div-h";
		$f_clock_div = "fluid-clock-h";
		$f_button_container_qty = $f_btn_container . " fluid-qty-container";
		$f_details_class = "fluid-details-container";
		$f_card_class = "fluid-card-class-h";
		$f_card_info_class = "fluid-card-info-class-h";
		$f_card_group_align = "f-card-group-align f-card-group-align-box-shadow-transparent";
		$f_name_div = "fluid-item-name-div";
		$f_timer_savings = NULL;
		$f_savings_title = NULL;
		$f_clockdiv = NULL;
		$f_savings_countdown = NULL;
		$f_smalltext = NULL;
	}
	else {
		$f_grid_class = "fluid-item-grid";
		$f_split_image_class = "fluid-item-data fluid-item-split-image-div-v";
		$f_split_class = "";
		$f_split_image = "img-responsive fluid-item-split-image-vertical-div";
		$f_buttons_class = "fluid-listing-item-buttons";
		$f_btn_container = "fluid-btn-container-width";
		$f_btn_container_s = $f_btn_container . " fluid-btn-container-s";
		$f_btn_mode = "";
		$f_item_extra = "";
		$f_clock_div = "";
		$f_button_container_qty = $f_btn_container . " fluid-qty-container";
		$f_details_class = "fluid-details-container-hide";
		$f_card_class = "fluid-card-class";
		$f_card_info_class = "fluid-card-info-class";
		$f_card_group_align = "";
		$f_name_div = "fluid-item-name-div-vh";
		$f_timer_savings = " f-timer-savings-vertical";

		$f_savings_title = " f-savings-title-vh";
		$f_clockdiv = " clockdiv-vh savings-countdown-vh";
		$f_savings_countdown = " savings-countdown-vh";
		$f_smalltext = " smalltext-vh";
	}

		if(empty($data['p_mfgcode']))
			$ft_mfgcode = $data['p_id'];
		else
			$ft_mfgcode = $data['p_mfgcode'];

		if(empty($data['p_name']))
			$ft_name = $data['p_id'];
		else
			$ft_name = $data['m_name'] . " " . $data['p_name'];

		$f_p_link = NULL;
		if(isset($data['p_bundle_item'])) {
			$f_p_link = "_" . $data['p_bundle_item']['p_id'];
		}

		$fluid_stock = new Fluid();
		$data['p_stock'] = $fluid_stock->php_process_stock($data) + $data['p_stock'];

		// Google tracking data.
		$f_gs_data = $data;
		$f_gs_data['url'] = $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name);
		$f_gs_data = base64_encode(json_encode($f_gs_data));

		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com")
			$f_gs_on_click = "onClick='js_gs_product_click(\"" . $f_gs_data . "\"); return !ga.loaded;'";
		else
			$f_gs_on_click = "onClick='js_gs_product_click(\"" . $f_gs_data . "\");'";

		$return = "<div name='fluid-item-card' class='" . $f_grid_class . "'>";
		$return .= "<div class='fluid-item-listing-div fluid-box-shadow-transparent'>";

			$return .= "<div id='fluid-split-div-image' class='" . $f_split_image_class . "'>";
				// --> Countdown timer if required. Only displays in vertical default mode.
				if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
					$f_savings = NULL;
					if(isset($data['p_discount_date_end'])) {
						$f_savings .= "<div class='f-timer-savings" . $f_timer_savings . "'>";
							$f_savings .= "<div class='f-savings-title" . $f_savings_title . "'>Instant savings end in</div>";
							$f_savings .= "<div class='clockdiv savings-countdown-div" . $f_clockdiv . "' style='margin: 0px;'>";
								$f_savings .= "<div>";
									$f_savings .= "<span class='savings-countdown days" . $f_savings_countdown . "'></span>";
									$f_time = strtotime($data['p_discount_date_end']) - strtotime(date('Y-m-d H:i:s'));

									if($f_time > 172800 || $f_time < 86400)
										$s_day = "DAYS";
									else
										$s_day = "DAY";

									$f_savings .= "<div class='smalltext smalltext-day" . $f_smalltext . "'>" . $s_day . "</div>";
								$f_savings .= "</div>";

								$f_savings .= "<div>";
									$f_savings .= "<span class='savings-countdown hours" . $f_savings_countdown . "'></span>";
									$f_savings .= "<div class='smalltext" . $f_smalltext . "'>HR</div>";
								$f_savings .= "</div>";

								$f_savings .= "<div>";
									$f_savings .= "<span class='savings-countdown minutes" . $f_savings_countdown . "'></span>";
									$f_savings .= "<div class='smalltext" . $f_smalltext . "'>MIN</div>";
								$f_savings .= "</div>";

								$f_savings .= "<div>";
									$f_savings .= "<span class='savings-countdown seconds" . $f_savings_countdown . "'></span>";
									$f_savings .= "<div class='smalltext" . $f_smalltext . "'>SEC</div>";
								$f_savings .= "</div>";

								// This must be the last child for this for the javascript code to pick up end times. Keep the div hidden.
								$f_savings .= "<div style='display:none;'>" . strtotime(date("F d Y H:i:s")) . "/" . strtotime(date("F d Y H:i:s", strtotime($data['p_discount_date_end']))) . "</div>";
							$f_savings .= "</div>"; // clockdiv savings-countdown-div
						$f_savings .= "</div>";
					}

					$return .= $f_savings;
				}

				// --> Display the rental bag if the item is flagged as rental item. Only on the normal listing format though.
				if($data['p_rental'] == 1 && $_SESSION['fluid_listing_display'] == 0)
					$return .= "<a href='" . $_SESSION['fluid_uri'] . "Rentals'><img class='img-responsive f-rental-image' src='" . $_SESSION['fluid_uri'] . "files/rental_logo.jpg'></img></a>";

				// Image.
				$return .= "<div id='fluid-item-image-container-" . $data['p_id'] . "' class='fluid-image-center-vh'>" ;

					$image_html_link = "<a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">";

					$f_img_length = "250";
					$f_img_height = "250";
					$f_img_style = NULL;
					$image_bundle_html = NULL;

					// We have a bundle, lets generate the second image.
					if(isset($data['p_bundle_item']) && $_SESSION['fluid_listing_display'] == 0) {
						$f_img_length = "125";
						$f_img_height = "125";
						$f_img_style = " style='display: inline-block;'";

						$p_images_bundle = $fluid->php_process_images($data['p_bundle_item']['p_images']);

						$f_img_name_bundle = str_replace(" ", "_", $data['p_bundle_item']['m_name'] . "_" . $data['p_bundle_item']['p_name'] . "_" . $data['p_bundle_item']['p_mfgcode']);
						$f_img_name_bundle = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name_bundle);

						$width_height_l_bundle = $fluid->php_process_image_resize($p_images_bundle[0], $f_img_length, $f_img_height, $f_img_name_bundle);

						$image_bundle_html = "<img class='" . $f_split_image . "'" . $f_img_style . " src='" . $_SESSION['fluid_uri'] . $width_height_l_bundle['image'] . "' alt=\"" . str_replace('"', '', $data['p_bundle_item']['m_name'] . " " . $data['p_bundle_item']['p_name']) . "\"/></img>";
					}

					$f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
					$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

					$width_height_l = $fluid->php_process_image_resize($p_images[0], $f_img_length, $f_img_height, $f_img_name);

					$image_html = "<img class='" . $f_split_image . "'" . $f_img_style . " src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img>";

					// --> Merge the bundle image if required.
					if(isset($image_bundle_html))
						if($data['p_bundle_item']['p_formula_flip'] == 1)
							$image_html = $image_html_link . $image_bundle_html . $image_html;
						else
							$image_html = $image_html_link . $image_html . $image_bundle_html;
					else
						$image_html = $image_html_link . $image_html . $image_bundle_html;

				$return .= $image_html . "</a>";
				$return .= "</div>"; // <!-- fluid-item-image-container end -->
			$return .= "</div>";

			$return .= "<div id='fluid-split-div-info' class='" . $f_split_class . "'>";

				$return .= "<div id='f-card-info-div' class='" . $f_card_info_class . "'>";
					$return .= " <div class='" . $f_name_div . "'>";

						$fluid_utf8 = new Fluid();
						if(empty($data['p_mfgcode']))
							$ft_mfgcode = $data['p_id'];
						else
							$ft_mfgcode = $data['p_mfgcode'];

						if(empty($data['p_name']))
							$ft_name = $data['p_id'];
						else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE)
							$ft_name = $fluid_utf8->php_utf8_decoder_encoder($data['m_name']) . " " . $fluid_utf8->php_utf8_decoder_encoder($data['p_name']);
						else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE)
							$ft_name = $fluid_utf8->php_utf8_decoder_encoder($data['m_name']) . " " . $fluid_utf8->php_utf8_decoder_encoder($data['p_name']);
						else
							$ft_name = $fluid_utf8->php_utf8_decoder_encoder($data['m_name']) . " " . $data['p_mfg_number'] . " " . $fluid_utf8->php_utf8_decoder_encoder($data['p_name']);

						// --> Lets see if we need to flip some data around.
						if(isset($data['p_bundle_item'])) {
							if($data['p_bundle_item']['p_formula_flip'] == 1) {
								if(empty($data['p_mfgcode']))
									$ft_mfgcode = $data['p_bundle_item']['p_id'];
								else
									$ft_mfgcode = $data['p_bundle_item']['p_mfgcode'];

								$ft_name = $fluid_utf8->php_utf8_decoder_encoder($data['p_bundle_item']['m_name']) . " " . $fluid_utf8->php_utf8_decoder_encoder($data['p_name']);
							}
						}

						$product_link_short = NULL;
						$f_product_link_vh_normal = NULL;

						// --> If we are in vertical mode, made some adjustments to make the cards the same height.
						if($_SESSION['fluid_listing_display'] == 1) {
							if(strlen($ft_name) > 40)
								$ft_name_short = substr($ft_name, 0, 40) . "...";
							else
								$ft_name_short = $ft_name;

							$product_link_short = "<a class='f-product-link-short-vh' style='vertical-align: middle;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">" . $ft_name_short . "</a>";

							$f_product_link_vh_normal = "class='f-product-link-normal-vh' ";
						}

						$product_link = "<a " . $f_product_link_vh_normal . "onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">" . $ft_name . "</a>";

						$return .= "<div>" . $product_link . $product_link_short . "</div>";
					$return .= "</div>"; //<!-- column end -->

					if(isset($data['p_bundle_item'])) {
						// --> Lets see if we need to flip some data around.
						if($data['p_bundle_item']['p_formula_flip'] == 1)
							$f_html_mfg_code = $data['p_bundle_item']['p_mfgcode'] . " + " . $data['p_mfgcode'];
						else
							$f_html_mfg_code = $data['p_mfgcode'] . " + " . $data['p_bundle_item']['p_mfgcode'];
					}
					else
						$f_html_mfg_code = $data['p_mfgcode'];

					$return .= "<div class='fluid-item-listing-hide'><div style='display: inline-block; font-size: 10px; font-weight: 300;'>UPC # " . $f_html_mfg_code . "</div>";
						if(isset($data['p_mfg_number'])) {
							$f_html_mfg_number = $data['p_mfg_number'];

							if(isset($data['p_bundle_item']))
								if(isset($data['p_bundle_item']['p_mfg_number']))
									if($data['p_bundle_item']['p_formula_flip'] == 1)
										$f_html_mfg_number = $data['p_bundle_item']['p_mfg_number'] . " + " . $data['p_mfg_number'];
									else
										$f_html_mfg_number = $data['p_mfg_number'] . " + " . $data['p_bundle_item']['p_mfg_number'];

							$return .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $f_html_mfg_number . "</div>";
						}
					$return .= "</div>";

					$return .= "<div class='fluid-item-extra-div" . $f_item_extra . "'>";

						$return .= "<div class='" . $f_details_class . "'>";

							// --> Lets see if we need to flip some data around.
							if(isset($data['p_bundle_item'])) {
								if($data['p_bundle_item']['p_formula_flip'] == 1)
									$data['p_details'] = $data['p_bundle_item']['p_details'] . $data['p_details'];
								else
									$data['p_details'] .= $data['p_bundle_item']['p_details'];
							}

							// --> Lets split up the <li> details, so we do not overflow the cards with too much data.
							if(!empty($data['p_details'])) {
								$f_details_tmp = strip_tags($data['p_details']);

								if(strlen($f_details_tmp) > 500) {
									$d_array = explode("<li>", $data['p_details']);

									$d_array_tmp = NULL;
									foreach($d_array as $d_string_tmp) {
										$d_string_tmp = str_ireplace("<ul>", "", $d_string_tmp);
										$d_string_tmp = str_ireplace("</ul>", "", $d_string_tmp);
										$d_string_tmp = str_ireplace("<li>", "", $d_string_tmp);
										$d_string_tmp = str_ireplace("</li>", "", $d_string_tmp);
										$d_string_tmp = strip_tags($d_string_tmp);

										if(trim(strlen($d_string_tmp) > 0))
											$d_array_tmp[] = $d_string_tmp;
									}

									$d_string = NULL;
									$d_len = 0;
									foreach($d_array_tmp as $d_string_tmp) {
										if($d_len < 400) {
											if(trim(strip_tags($d_string_tmp)) != "") {
												$d_string .= "<li>" . $d_string_tmp . "</li>";
												$d_len = $d_len + strlen($d_string_tmp);
											}
										}
										else
											break;
									}

									$f_details_string = "<ul>" . $d_string . "</ul>";
								}
								else
									$f_details_string = $data['p_details'];


								$d_array = explode("<li>", $f_details_string);
								$d_data = NULL;
								$d_i = 1;
								foreach($d_array as $key => $d_string) {
									$d_string = str_ireplace("<ul>", "", $d_string);
									$d_string = str_ireplace("</ul>", "", $d_string);

									if($d_i > 8) {
										$d_data .= "</ul>";
										$d_break = TRUE;
										break;
									}

									if($d_string != "<ul class='fluid-ul-class'>" && strlen($d_string) > 1) {
										$d_i++;

										$d_data .= "<li>" . $d_string . "</li>";
									}
									//else
										//$s_data .= $s_string;
								}

								if(isset($d_data))
									$return .= "<ul class='fluid-ul-class'>" . $d_data . "</ul>";

								//$return .= utf8_encode($data['p_details']);
							}
						$return .= "</div>";

					$return .= "</div>"; // item-extra-div
				$return .= "</div>"; // f-card-info-div

					$f_formula_message = NULL;
					$return_tmp = NULL;

					// We have a bundle item. Lets display some data for it.
					if(isset($data['p_bundle_item'])) {
						// --> Check to see which prices we should be using from each bundle item.
						if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL))) {
							$f_data_tmp_price = $data['p_price_discount'];
						}
						else {
							$f_data_tmp_price = $data['p_price'];
						}

						// --> Check to see which prices we should be using from each bundle item.
						if($data['p_bundle_item']['p_price_discount'] > 0 && ((strtotime($data['p_bundle_item']['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_bundle_item']['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_bundle_item']['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_bundle_item']['p_discount_date_end'] == NULL) || ($data['p_bundle_item']['p_discount_date_start'] == NULL && $data['p_bundle_item']['p_discount_date_end'] == NULL))) {
							$f_data_bundle_tmp_price = $data['p_bundle_item']['p_price_discount'];
						}
						else {
							$f_data_bundle_tmp_price = $data['p_bundle_item']['p_price'];
						}

						//$data['p_price'] = $data['p_price'] + $data['p_bundle_item']['p_price'];
						$f_price_org = $data['p_price'] + $data['p_bundle_item']['p_price'];

						// --> Apply the new pricing to the bundle item now.
						$data['p_price'] = $f_data_tmp_price + $f_data_bundle_tmp_price;

						// --> Apply the math formula.
						$f_formula = $data['p_bundle_item']['p_formula_math'];

						$f_vars = FORMULA_VARIABLES;

						$f_vars_data = Array($data['p_price'], $data['p_price'], $data['p_stock'], $data['p_cost'], $data['p_length'], $data['p_width'], $data['p_height'], $data['p_height']);

						$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

						$parser = new StdMathParser();

						$AST = $parser->parse($f_formula);

						// --> Evaluate the expression.
						$evaluator = new Evaluator();

						$f_value = $AST->accept($evaluator);
						$f_value = $data['p_price'] - $f_value;
						$f_value = -1 * abs($f_value);

						$data['p_price_discount'] = $data['p_price'] + $f_value;

						$f_stock = base64_encode($fluid->php_process_stock_status($data['p_instore'], $data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']));
						$f_stock_bundle = base64_encode($fluid->php_process_stock_status($data['p_instore'], $data['p_bundle_item']['p_instore'], $data['p_bundle_item']['p_stock'], $data['p_bundle_item']['p_enable'], $data['p_bundle_item']['p_newarrivalenddate'], $data['p_bundle_item']['p_preorder'], $data['p_arrivaltype']));

						$f_stock_zero = $fluid->php_process_stock_status($data['p_instore'], $data['p_instore'], 0, $data['p_enable'], NULL, NULL);

						if($f_stock == $f_stock_bundle)
							$f_stock_html = base64_decode($f_stock);
						else
							$f_stock_html = $f_stock_zero;

						$return_tmp .= "<div style='padding-bottom: 5px;'>";
							$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block; padding-left: 0px !important;'>" . $f_stock_html . "</div>";
						$return_tmp .= "</div>";

						$return_tmp .= "<div>";
							$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
							$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_savings($f_price_org, $data['p_price_discount'], $data['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
						$return_tmp .= "</div>";

						$return_tmp .= "<div>";
							$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; text-decoration: line-through;'>Original: " . utf8_encode(HTML_CURRENCY) . number_format($f_price_org, 2, '.', ',') . "</div>";
						$return_tmp .= "</div>";

						$return_tmp .= "<div>";
							$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; color: red;'> Total Savings ";
							$return_tmp .= number_format((1 - ($data['p_price_discount'] / $f_price_org)) * 100, 0, '.', ',') . "%</div>";
						$return_tmp .= "</div>";

						if(($data['p_formula_message_display'] == 1 && $data['p_formula_message']) || ($data['p_bundle_item']['p_formula_message_display'] == 1 && $data['p_bundle_item']['p_formula_message'])) {
				            if($_SESSION['fluid_listing_display'] == 0) {
								$f_align_message_text = "right";
								$f_align_message_div = "display: block;";

								$f_formula_message = "<div style='font-size: 75%; text-align: " . $f_align_message_text . ";'>";
								$f_formula_message .= "<div style='" . $f_align_message_div . "'>* " . $data['p_bundle_item']['p_formula_message'] . "</div></div>";
							}
						}

						$return_tmp .= "<div>";
							$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center; font-weight: 600;'>You Pay:</div>";
							$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center; font-weight: 600;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($f_price_org, $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
						$return_tmp .= "</div>";

					}
					else if($data['p_formula_status'] == 1 && ($data['p_formula_operation'] != FORMULA_OPTION_8 && $data['p_formula_operation'] != FORMULA_OPTION_9) && ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) )) {
							$f_value_asterik = NULL;

							if(strlen($data['p_formula_math']) > 0 && $data['p_formula_operation'] != FORMULA_OPTION_10 && $data['p_formula_operation'] != FORMULA_OPTION_7) {
								$return_tmp .= "<div style='padding-bottom: 5px;'>";
									$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block; padding-left: 0px !important;'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
								$return_tmp .= "</div>";

								if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
									$p_price = $data['p_price_discount'];

									if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
										$f_reg_discount = FALSE; // --> Set to TRUE if you want to display the Instant savings information when additional savings are available.
									else
										$f_reg_discount = TRUE;

									if($f_reg_discount == TRUE) {
										$f_savings_text = "Additional Savings:";
										$f_padding_special = " padding-right: 10px;";
									}
									else {
										$f_savings_text = "Instant Savings:"; // --> Set to additional savings if you enable f_reg_discount back to TRUE.
										$f_padding_special = NULL;
									}

									// --> If in default horizontal view, make the savings box wider with additional savings.
									if($_SESSION['fluid_listing_display'] == 0)
										$f_card_class = "fluid-card-class-savings-h";
								}
								else {
									$p_price = $data['p_price'];
									$f_savings_text = "Instant Savings:";
									$f_padding_special = NULL;
									$f_reg_discount = FALSE;
								}

								// --> Apply the math formula.
								$f_formula = $data['p_formula_math'];

								$f_vars = FORMULA_VARIABLES;

								$f_vars_data = Array($data['p_price'], $p_price, $data['p_stock'], $data['p_cost'], $data['p_length'], $data['p_width'], $data['p_height'], $data['p_height']);

								$f_formula = str_replace($f_vars, $f_vars_data, $f_formula);

								$parser = new StdMathParser();

								$AST = $parser->parse($f_formula);

								// --> Evaluate the expression.
								$evaluator = new Evaluator();

								$f_value = $AST->accept($evaluator);

								if($f_value <= 0)
									$f_value = $p_price;

								if($data['p_formula_message_display'] == 1 && $data['p_formula_message']) {
									$f_value_asterik = "<div style='display: inline-block; font-weight: 600; padding-left: 3px;'></div>";
								}

								if($f_reg_discount == TRUE) {
									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
										$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;" . $f_padding_special . "'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_savings($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";
								}

								$return_tmp .= "<div>";
									$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>" . $f_savings_text . "</div>";
								if($f_reg_discount == TRUE)
									$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;'>" . utf8_encode(HTML_CURRENCY) . number_format($p_price - $f_value, 2, '.', ',') . " *</div>";
								else
									$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;'>" . utf8_encode(HTML_CURRENCY) . number_format($data['p_price'] - $f_value, 2, '.', ',') . "</div>";
								$return_tmp .= "</div>";

								$return_tmp .= "<div>";
									$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; text-decoration: line-through;" . $f_padding_special . "'>Original: " . utf8_encode(HTML_CURRENCY) . number_format($data['p_price'], 2, '.', ',') . "</div>";
								$return_tmp .= "</div>";

								if($f_reg_discount == TRUE) {
									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; text-decoration: line-through;" . $f_padding_special . "'>Savings Price: " . utf8_encode(HTML_CURRENCY) . number_format($data['p_price_discount'], 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";
								}

								$return_tmp .= "<div>";
									$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; color: red;" . $f_padding_special . "'> Total Savings ";
									$return_tmp .= number_format((1 - ($f_value / $data['p_price'])) * 100, 0, '.', ',') . "%</div>";
								$return_tmp .= "</div>";

								$return_tmp .= "<div>";
									$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center; font-weight: 600;'>You Pay:</div>";
									$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center; font-weight: 600;'>" . utf8_encode(HTML_CURRENCY) . number_format($f_value, 2, '.', ',') . $f_value_asterik . "</div>";
								$return_tmp .= "</div>";
							}
							else if($data['p_price']) {
								if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
									$return_tmp .= "<div style='padding-bottom: 5px;'>";
										$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block; padding-left: 0px !important;'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
									$return_tmp .= "</div>";

									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
										$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_savings($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";

									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; text-decoration: line-through;'>Original: " . utf8_encode(HTML_CURRENCY) . number_format($data['p_price'], 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";

									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; color: red;'> Total Savings ";
										$return_tmp .= number_format((1 - ($data['p_price_discount'] / $data['p_price'])) * 100, 0, '.', ',') . "%</div>";
									$return_tmp .= "</div>";

									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center; font-weight: 600;'>You Pay:</div>";
										$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center; font-weight: 600;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";
								}
								else {
									// --> If we are in vertical mode, made some adjustments to make the cards the same height.
									if($_SESSION['fluid_listing_display'] == 1) {
										$return_tmp .= "<div class='f-special-vh-card'>";
										$f_div_vh_special = "display: inline;";
									}
									else {
										$return_tmp .= "<div>";
										$f_div_vh_special = "display: inline-block;";
									}

										$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; padding-left: 0px !important; " . $f_div_vh_special . "'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
									$return_tmp .= "</div>";

									$return_tmp .= "<div>";
										$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center;'>You Pay:</div>";
										$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
									$return_tmp .= "</div>";
								}
							}

							if($data['p_formula_message_display'] == 1 && $data['p_formula_message']) {
								if($_SESSION['fluid_listing_display'] == 0) {

									if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE) {
										$f_align_message_text = "right";
									}
									else {
										$f_align_message_text = "left";
									}

									$f_align_message = "float: left;";
									$f_align_message_div = "display: block;";
								}
								else {
									$f_align_message_text = "center";
									$f_align_message = "display: inline-block;";
									$f_align_message_div = "display: inline-block;";
								}

								$f_formula_message = "<div style='font-size: 75%; text-align: " . $f_align_message_text . ";'>";

									if(isset($data['p_formula_math'])) {
										if(isset($p_price) && isset($f_value)) {
											$f_savings = $p_price - $f_value;

											if(FLUID_ADDITIONAL_SAVINGS_MERGE == FALSE)
												$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block; float:left; padding-right: 3px;'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div>";
											else
												$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'>";
										}
										else if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
											$f_savings = $data['p_price'] - $data['p_price_discount'];

											if(FLUID_ADDITIONAL_SAVINGS_MERGE == FALSE)
												$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block; float:left; padding-right: 3px;'><div style='display:inline-block;'>* Save </div><div style='display: inline-block; color: red; padding-left: 3px;'> " . HTML_CURRENCY . number_format($f_savings, 2, '.', ',') . ".</div></div>";
											else
												$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'>";
										}
										else {
											$f_formula_message .= "<div style='display: inline-block; font-weight: 600; font-style: italic; width: 100%;'><div style='display: inline-block; float:left; padding-right: 3px;'><div style='display:inline-block;'></div></div>";
										}

										if(FLUID_ADDITIONAL_SAVINGS_MERGE == TRUE)
											$f_formula_message .= " ";

										$f_formula_message .= $data['p_formula_message'] . "</div></div>";

										//$f_formula_message .= "<div style='" . $f_align_message . " padding-right: 3px;'>" . $f_value_asterik . "</div>";
									}
									else
										$f_formula_message .= "<div style='" . $f_align_message_div . "'>" . $data['p_formula_message'] . "</div></div>";
							}
					}
					else if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
						if($data['p_price']) {
							$return_tmp .= "<div style='padding-bottom: 5px;'>";
								$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; display: inline-block; padding-left: 0px !important;'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
							$return_tmp .= "</div>";

							$return_tmp .= "<div>";
								$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center;'>Instant Savings:</div>";
								$return_tmp .= "<div class='price-instant-savings-value-listing' style='display: inline-block; text-align: center; color: red;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_savings($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'])['dollar'], 2, '.', ',') . "</div>";
							$return_tmp .= "</div>";

							$return_tmp .= "<div>";
								$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; text-decoration: line-through;'>Original: " . utf8_encode(HTML_CURRENCY) . number_format($data['p_price'], 2, '.', ',') . "</div>";
							$return_tmp .= "</div>";

							$return_tmp .= "<div>";
								$return_tmp .= "<div class='price-instant-savings-listing' style='display: inline-block; text-align: center; color: red;'> Total Savings ";
								$return_tmp .= number_format((1 - ($data['p_price_discount'] / $data['p_price'])) * 100, 0, '.', ',') . "%</div>";
							$return_tmp .= "</div>";

							$return_tmp .= "<div>";
								$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center; font-weight: 600;'>You Pay:</div>";
								$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center; font-weight: 600;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
							$return_tmp .= "</div>";
						}
					}
					else {
						if($data['p_price']) {
							// --> If we are in vertical mode, made some adjustments to make the cards the same height.
							if($_SESSION['fluid_listing_display'] == 1) {
								$return_tmp .= "<div class='f-special-vh-card'>";
								$f_div_vh_special = "display: inline;";
							}
							else {
								$return_tmp .= "<div>";
								$f_div_vh_special = "display: inline-block;";
							}

								$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; padding-left: 0px !important; " . $f_div_vh_special . "'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
							$return_tmp .= "</div>";

							$return_tmp .= "<div>";
								$return_tmp .= "<div class='price-final-listing' style='display: inline-block; text-align: center;'>You Pay:</div>";
								$return_tmp .= "<div class='price-final-value-listing' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
							$return_tmp .= "</div>";
						}
						else {
							// --> If we are in vertical mode, made some adjustments to make the cards the same height.
							if($_SESSION['fluid_listing_display'] == 1) {
								$return_tmp .= "<div class='f-special-vh-card'>";
								$f_div_vh_special = "display: inline;";
							}
							else {
								$return_tmp .= "<div>";
								$f_div_vh_special = "display: inline-block;";
							}

								$return_tmp .= "<div class='stock-availability-value-listing' style='text-align: center; padding-left: 0px !important; " . $f_div_vh_special . "'>" . $fluid->php_process_stock_status($data['p_instore'], $data['p_stock'], $data['p_enable'], $data['p_newarrivalenddate'], $data['p_preorder'], $data['p_arrivaltype']) . "</div>";
							$return_tmp .= "</div>";
						}
					}

				$return .= "<div id='f-button-card-group' class='" . $f_card_class . "'>";
					$return .= "<div class='" . $f_card_group_align . "'>";

					$return .= $return_tmp;

					// --> We have a active formula link. Lets see if we should display a formula link message. Only shows during horizontal mode at the moment due to card sizes in vertical having spacing issues when this additional line is added.
					//if($_SESSION['fluid_listing_display'] == 0 && $data['p_formula_status'] == 1 && $data['p_formula_message_display'] == 1 && $data['p_formula_message'] && ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) ))
					if(isset($f_formula_message) && $_SESSION['fluid_listing_display'] != 1) {
						$return .= $f_formula_message;
					}

					$detect = new Mobile_Detect;
					if(empty($f_formula_message) && $_SESSION['fluid_listing_display'] == 1) {
						if($detect->isMobile() && $detect->isTablet() == FALSE) {
							// --> Do nothing.
						}
						else
							$return .= "<div style='padding-top: 15px;'></div>";
					}

						$return .= "<div class='" . $f_buttons_class ."'>";
							$return .= "<div class='input-group " . $f_button_container_qty . "'>";
								$return .= "<span class='input-group-addon input-group-addon-fluid' id='basic-addon1'>QTY</span>";
								$return .= "<select id='fluid-cart-qty-" . $data['p_id'] . $f_p_link . "' class='btn-group form-control bootstrap-select f-bootstrap show-menu-arrow show-tick' data-size='5' id='QTY-input' style=''>";
									if(empty($data['p_buyqty'])) {
										if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $data['p_enable'] == 2) {
											if($data['p_stock'] > 0)
												$p_qty = $data['p_stock'];
											else if($data['p_stock'] < 1 && $data['p_enable'] == 2)
												$p_qty = 0;
											else
												$p_qty = 1;
										}
										else
											$p_qty = 99;
									}
									else if($data['p_buyqty'] < 1) {
										if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $data['p_enable'] == 2) {
											if($data['p_stock'] > 0)
												$p_qty = $data['p_stock'];
											else if($data['p_stock'] < 1 && $data['p_enable'] == 2)
												$p_qty = 0;
											else
												$p_qty = 1;
										}
										else
											$p_qty = 99;
									}
									else if($data['p_buyqty'] > $data['p_stock']) {
										if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE || $data['p_enable'] == 2)
											$p_qty = $data['p_stock'];
										else if($data['p_stock'] < 1 && $data['p_enable'] == 2)
											$p_qty = 0;
										else
											$p_qty = 99;
									}
									else
										$p_qty = $data['p_buyqty'];

									if($p_qty == 0) {
										if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE)
											$p_qty = $data['p_buyqty'];
										else
											$p_qty = 99;
									}

									if($data['p_stock'] < 1 && $data['p_enable'] == 2)
										$p_qty = 1;

									//if($p_qty > 10)
										//$p_qty = 10;

									for($i = 1; $i <= $p_qty; $i++)
										$return .= "<option value='" . $i . "'>" . $i . "</option>";
								$return .= "</select>";
							$return .= "</div>";

							$return .= "<div style='text-align: center;' name='fluid-button-" . $data['p_id'] . $f_p_link . "' id='fluid-button-" . $data['p_id'] . $f_p_link . "' class='" . $f_btn_container_s. "' onClick='FluidMenu.button.obj_div = this; FluidMenu.button.obj_div_id=\"" . $data['p_id'] . "\";'>";

							$cart_disabled = "onClick='js_fluid_add_to_cart(this, \"" . $data['p_id'] . $f_p_link . "\");'";
							$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-success btn-block'";
							$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Add to Cart";

							if($data['p_height'] <= 0 || $data['p_length'] <= 0 || $data['p_width'] <= 0 || $data['p_weight'] <= 0 || FLUID_STORE_OPEN == FALSE || FLUID_PURCHASE_OUT_OF_STOCK == FALSE) {
								if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
									// --> Do nothing. Preorders will be checked below.
								}
								else if($data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
									// --> // --> Item is in stock, but not available to be preordered and is not officially launched yet.
									$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
									$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
									$cart_disabled = "disabled";
								}
								else if($data['p_special_order'] == 1) {
									// --> Do nothing. Special orders will be checked below.
								}
								else if($data['p_stock'] > 0) {
									// --> Do nothing. Item is in stock.
								}
								else
									$cart_disabled = "disabled";

								//if(FLUID_PAYMENT_SANDBOX == FALSE)
									//$cart_disabled = "disabled";
							}
							else if(FLUID_PURCHASE_OUT_OF_STOCK == FALSE && ($data['p_stock'] < 1 || $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
								if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) && FLUID_PREORDER == TRUE) {
									//  --> Do nothing. Preorders will be checked below.
								}
								else if($data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
									// --> Item is in stock, but not available to be preordered and is not officially launched yet.
									$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
									$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
									$cart_disabled = "disabled";
								}
								else if($data['p_special_order'] == 1) {
									// --> Do nothing. Special orders will be checked below.
								}
								else if($data['p_stock'] > 0) {
									//  --> Do nothing. Preorders will be checked below.
								}
								else
									$cart_disabled = "disabled";
							}
							else if(FLUID_PURCHASE_OUT_OF_STOCK == TRUE && $data['p_preorder'] == FALSE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE) {
								// --> Item is in stock, but not available to be preordered and is not officially launched yet.
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
								$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Coming soon";
								$cart_disabled = "disabled";
							}

							if($data['p_height'] <= 0 || $data['p_length'] <= 0 || $data['p_width'] <= 0 || $data['p_weight'] <= 0 || $data['p_price'] <= 0) {
								$f_cart_class = "href='tel:+16046855331' class='btn btn-lg " . $f_btn_mode . " btn-primary btn-block'";
								$f_cart_message = "<i class='fa fa-phone' aria-hidden='true'></i> Call for more info";
								$cart_disabled = "disabled";
							}

							//if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
							if(($data['p_preorder'] == TRUE && $fluid->php_item_available($data['p_newarrivalenddate']) == FALSE)) {
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-warning btn-block'";
								$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Preorder";

								if(FLUID_PREORDER == FALSE)
									$cart_disabled = "disabled";
							}
							else if($data['p_special_order'] == 1 && $data['p_stock'] < 1) {
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-info btn-block'";
								$f_cart_message = "<span class=\"glyphicon glyphicon-shopping-cart\" aria-hidden=\"true\"></span> Special Order";
							}

							// --> Discontinued item and no longer in stock.
							if($data['p_stock'] < 1 && $data['p_enable'] == 2) {
								$cart_disabled = "disabled";
								$f_cart_class = "class='btn btn-lg " . $f_btn_mode . " btn-default btn-block'";
								$f_cart_message = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> Discontinued";
							}

								$return .= "<a name='fluid-cart-btn-" . $data['p_id'] . $f_p_link . "' id='fluid-cart-btn-" . $data['p_id'] . $f_p_link . "' " . $f_cart_class . " " . $cart_disabled . ">" . $f_cart_message . "</a>";
							$return .= "</div>";
						$return .= "</div>";	// f_buttons_class
					$return .= "</div>"; // $f_card_group_align
				$return .= "</div>"; // f-button-card-group

			$return .= "</div>"; // split class.

		$return .= "</div>";
	$return .= "</div>";

	return $return;
}

function php_html_item_listing($data_obj = NULL) {
	try {
		$fluid = new Fluid ();

		if(isset($data_obj->last_id))
			$last_id = $data_obj->last_id;
		else
			$last_id = 0;

		$fluid->php_db_begin();

/*
		if(isset($data_obj))
			$_SESSION['fluid_listing'][$data_obj->f_token] = $data_obj;

		$data_obj = $_SESSION['fluid_listing'][$data_obj->f_token];
*/

		// {"MjI4MDAwODc=": {"sub_id": "MjI4MDAwODc=", "filter_id": "MjIxMzcyNTU5", "category_id": 1}, "MjM5MjgyMzQ4": {"sub_id": "MjM5MjgyMzQ4", "filter_id": "MzY3Nzg2NDc1", "category_id": 1}, "MzM0ODIwODc1": {"sub_id": "MzM0ODIwODc1", "filter_id": "MzM4MzQ5Njk0", "category_id": 1}, "NDM0OTExNDQ=": {"sub_id": "NDM0OTExNDQ=", "filter_id": "NTczOTMzNzA=", "category_id": 1}, "NDQ1NTY2Njgw": {"sub_id": "NDQ1NTY2Njgw", "filter_id": "MTQ1OTI1MDc1", "category_id": 1}, "ODU5MzA3ODIx": {"sub_id": "ODU5MzA3ODIx", "filter_id": "MTA4NDQwMTI3", "category_id": 1}}

		// JSON_EXTRACT(p_c_filters, "$.111312952.filter_id") FROM `products`
		// "NDQ1NTY2Njgw": {"sub_id": "NDQ1NTY2Njgw", "filter_id": "MTQ1OTI1MDc1", "category_id": 1},

		// All 3 work for searching data, but the last one works the best.
		// SELECT * FROM `products` WHERE JSON_EXTRACT(p_c_filters, "$.NDQ1NTY2Njgw.filter_id") = "MTQ1OTI1MDc1";
		// SELECT * FROM `products` WHERE JSON_SEARCH(p_c_filters, 'all', 'NDQ1NTY2Njgw') IS NOT NULL
		// SELECT * FROM `products` WHERE p_c_filters->"$.NDQ1NTY2Njgw.filter_id" = 'MTQ1OTI1MDc1'
		// SELECT * FROM `products` WHERE p_c_filters->'$.\"NDQ1NTY2Njgw\".filter_id' = 'MTQ1OTI1MDc1' <- best way.

		// Do a scan for linking items first.
		$fluid->php_db_query("SELECT * FROM " . TABLE_PRODUCT_CATEGORY_LINKING . " WHERE l_c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "'");

		$l_query = NULL;
		if(isset($fluid->db_array)) {
			$i = 0;
			$l_query = " OR p.p_id IN (";
			foreach($fluid->db_array as $lc_data) {
				if($i > 0)
					$l_query .= ", ";

				$l_query .= "'" . $lc_data['l_p_id'] . "'";

				$i++;
			}

			$l_query .= ")";
		}

		$filter_where = NULL;
		if(isset($data_obj->Filters)) {
			if(is_object($data_obj->Filters)) {
				$i = 0;
				foreach($data_obj->Filters as $key => $filter) {
					if($i == 0) {
						$filter_where .= " AND (";
					}
					else {
						$filter_where .= " AND ";
					}

					$f_data = json_decode(base64_decode($filter->filter_obj));
					$filter_where .= "p.p_c_filters->'$." . $fluid->php_escape_string("\"" . $f_data->sub_id . "\"") . ".filter_id' = '" . $fluid->php_escape_string($f_data->filter_id) . "'";

					$i++;
				}

				if($i > 0)
					$filter_where .= ")";
			}
		}

		$filter_where_mfg = NULL;
		if(isset($data_obj->Filters_mfg)) {
			if(is_object($data_obj->Filters_mfg)) {
				$i_mfg = 0;

			foreach($data_obj->Filters_mfg as $key => $filter) {
				if($i_mfg == 0)
					$filter_where_mfg .= " AND (";
				else
					$filter_where_mfg .= " OR ";

				$filter_where_mfg .= "p.p_mfgid = '" . $fluid->php_escape_string(base64_decode($filter)) . "'";

				$i_mfg++;
			}

			if($i_mfg > 0)
				$filter_where_mfg .= ")";
			}
		}

		$filter_where_price = NULL;
		if(isset($data_obj->Filters_price)) {
			if(is_object($data_obj->Filters_price)) {
				$i_price = 0;
				foreach($data_obj->Filters_price as $key => $filter) {
					$filter_tmp = json_decode(base64_decode($filter));

					if($i_price == 0)
						$filter_where_price .= " AND (";
					else
						$filter_where_price .= " OR ";

					$filter_where_price .= "p.p_price >= '" . $fluid->php_escape_string($filter_tmp->low) . "' AND p.p_price <= '" . $fluid->php_escape_string($filter_tmp->high) . "'";

					$i_price++;
				}

				if($i_price > 0)
					$filter_where_price .= ")";
			}
		}

		$query_search_stock = " p.p_enable > '0' AND c.c_enable = 1 ";
		$query_search_zero_stock = NULL;
		// --> Only show products that have stock or a arrival date or discount date ending in the future.
		if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 1) {
			$s_date = date("Y-m-d 00:00:00");
			$filter_where .= " AND ((p_stock > 0 AND p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0 OR (p_newarrivalenddate >= '" . $s_date . "' OR p_discount_date_end >= '" . $s_date . "') OR (p_date_hide > '" . $s_date . "'))";
		}
		else {
			$filter_where .= " AND ((p_weight > 0 AND p_height > 0 AND p_length > 0 AND p_width > 0) OR p_showalways > 0)";

			// --> Since we are showing all products in or not in stock. We need to filter out zero stock items that are set to hide when out of stock.
			if(FLUID_ITEM_LISTING_STOCK_AND_DISCOUNT_ONLY == 2) {
				$query_search_stock = " ((p.p_enable = '1' AND c.c_enable = 1) OR (p.p_enable = '2' AND p.p_stock > 0 AND c.c_enable = 1)) ";
			}

			$query_search_zero_stock = " AND p_zero_status_tmp > 0";
		}

		if(base64_decode($data_obj->cat_id) == "bundles") {
			$s_date = date("Y-m-d 00:00:00");

			$filter_where = " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";

			$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE " . $query_search_stock . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p_zero_status_tmp HAVING p_zero_status_tmp > 0");
		}
		else if(base64_decode($data_obj->cat_id) == "all" || (base64_decode($data_obj->cat_id) == "blackfriday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweekend" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweek" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "cybermonday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "boxingweek" && FLUID_BLACK_FRIDAY == TRUE)) {
			$s_date = date("Y-m-d 00:00:00");

			/*
			// This includes all deals without a ending.
			$filter_where = " AND ( (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END)";

			$filter_where = " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND  p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end IS NULL AND p_price_discount > 0) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end IS NULL) THEN 1 ELSE 0 END) )";
			*/

			// All deals that are ending.
			$filter_where = " AND ( (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0 AND p_stock > 0) THEN 1 ELSE 0 END)";

			$filter_where = " AND ( (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_stock > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND  p_formula_status > 0 AND p_stock > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_discount_date_start <= DATE(CURDATE()) AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END) OR (CASE WHEN (p_discount_date_start IS NULL AND p_discount_date_end >= DATE(CURDATE()) AND p_price_discount > 0) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_status > 0 AND p_formula_message_display > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END)";

			$filter_where .= " OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start <= DATE(CURDATE()) AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) OR (CASE WHEN (p_formula_math != '' AND p_formula_message_display < 1 AND p_formula_status > 0 AND p_formula_discount_date_start IS NULL AND p_formula_discount_date_end >= DATE(CURDATE())) THEN 1 ELSE 0 END) )";

			$p_stock_where = NULL;

			$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE " . $query_search_stock . $p_stock_where . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p_zero_status_tmp HAVING p_zero_status_tmp > 0");
		}
		else if(base64_decode($data_obj->cat_id) == "sigma") {
			$filter_where .= " AND (p.p_mfg_number IN ('A1224DGHC', 'A1224DGHN', 'A1224DGHS', 'A14DGHC', 'A14DGHN', 'A14DGHS', 'A1835DCHC', 'A1835DCHN', 'A1835DCHS', 'A1835DCHP', 'A1835DCHAS', 'A20DGHC', 'A20DGHN', 'A20DGHS', 'A24DGHN', 'A24DGHC', 'A2435DGHN', 'A2435DGHS', 'AOS2470DGC', 'AOS2470DGN', 'AOS2470DGS', 'AOS24105C', 'AOS24105N', 'AOS24105S', 'AAF24105AS', 'A30DCHC', 'A30DCHN', 'A30DCHS', 'A30DCHP', 'A30DCHAS', 'A35DGC', 'A35DGN', 'A35DGS', 'A35DGP', 'A35DGAS', 'A50DGHC', 'A50DGHS', 'A50DGHN', 'A50DGHAS', 'A50100DCHC', 'A50100DCHN', 'A50100DCHS', 'A85DGHC', 'A85DGHN', 'COS1004DGC', 'COS1004DGN', 'COS1004DGS', 'SOS1203DGC', 'SOS1203DGN', 'SOS1203DGS', 'A135DGHC', 'A135DGHN', 'A135DGHS', 'COS1506DGC', 'COS1506DGN', 'COS1506DGS', 'SOS1506DGC', 'SOS1506DGN', 'SOS1506DGS'))";

			$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE " . $query_search_stock . $p_stock_where . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p_zero_status_tmp HAVING p_zero_status_tmp > 0");
		}
		else {
			// Lets get a item count and determine where we start and where we end on items to show. This is for regular category listings.
			$fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE " . $query_search_stock . " AND (c.c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "' OR c.c_parent_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "'" . $l_query . ")" . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p_zero_status_tmp HAVING p_zero_status_tmp > 0");
		}

		$total_items = 0;
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $f_tmp_array)
				$total_items = $total_items + $f_tmp_array['total'];
		}
		else
			$total_items = 0;

		if(isset($data_obj->item_page)) {
			$item_page = $data_obj->item_page;

			if(FLUID_LISTING_INFINITE_SCROLLING == FALSE) {
				if(isset($data_obj->reload))
					if($data_obj->reload == TRUE)
						$item_page--;
			}

			$item_start = ($item_page - 1) * VAR_LISTING_MAX;  // The first item to display on this page.
		}
		else {
			$item_page = 0;
			$item_start = 0; // If no item_page var is given, set start to 0.
		}

		// Set up the sort order.
		//$sort_by = "p_sortorder ASC";
		$sort_by = "p_stock ASC";

		if(base64_decode($data_obj->cat_id) == "bundles" || (base64_decode($data_obj->cat_id) == "blackfriday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweekend" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweek" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "cybermonday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "boxingweek" && FLUID_BLACK_FRIDAY == TRUE) || base64_decode($data_obj->cat_id) == "all")
			$sort_discount = TRUE;
		else
			$sort_discount = NULL;

		if(isset($data_obj->sort_by)) {
			$sort_by = php_sort_by($data_obj->sort_by, $sort_discount);
		}

		$order_by = "ORDER BY " . $sort_by . " LIMIT " . $item_start . "," . VAR_LISTING_MAX;

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$f_item_start = $item_start;

			if(isset($data_obj->reload)) {
				if($data_obj->reload == TRUE) {
					$f_end = $item_start + VAR_LISTING_MAX;
					$f_item_start = 0;
					$order_by = "ORDER BY " . $sort_by . " LIMIT " . $f_item_start . "," . $f_end;
				}
			}
		}

		if(base64_decode($data_obj->cat_id) == "bundles" || (base64_decode($data_obj->cat_id) == "blackfriday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweekend" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweek" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "cybermonday" && FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "boxingweek" && FLUID_BLACK_FRIDAY == TRUE) || base64_decode($data_obj->cat_id) == "all" || base64_decode($data_obj->cat_id) == "sigma") {
			$p_stock_where = NULL;

			// Build a fluid_discount_savings math formula column, price-price-discount. used by the default order by.
			$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id WHERE p.p_price IS NOT NULL AND " . $query_search_stock . $p_stock_where . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p.p_id HAVING p_zero_status_tmp > 0 " . $order_by);
		}
		else {
			$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c ON p.p_catid = c_id WHERE (c.c_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "' OR c.c_parent_id = '" . $fluid->php_escape_string(base64_decode($data_obj->cat_id)) . "'" . $l_query . ") AND p.p_price IS NOT NULL AND " . $query_search_stock . $filter_where . $filter_where_mfg . $filter_where_price . " GROUP BY p.p_id HAVING p_zero_status_tmp > 0 " . $order_by);
		}

		$return = NULL;
		$bool_found_items = FALSE;
		$i_item_count = 0;
		$f_items = NULL;
		$f_bundle_select = NULL;
		$f_bundle_count = 0;
		$f_total_bundles = 0;

		if(isset($fluid->db_array)) {
			// First check if the current returned items belongs to any bundles.
			$fluid_search = new Fluid();
			$fluid_search->php_db_begin();
			$f_tmp = 0;
			$f_search_bund = NULL;
			$f_search_bund_array = NULL;
			$f_search_data_array = NULL;

			//SELECT * FROM `products` WHERE `p_formula_items_data` LIKE '%NjE5NjU5MDY2Nzcy%' OR `p_formula_items_data` LIKE '%FdfdfdssdSDF%' ORDER BY `p_id` ASC
			foreach($fluid->db_array as $data) {
				if($f_tmp > 0) {
					$f_search_bund .= " OR ";
				}

				$f_search_bund .= "`p_formula_items_data` LIKE '%" . $fluid_search->php_escape_string(base64_encode($data['p_mfgcode'])) . "%'";

				$f_tmp++;
			}

			if(isset($f_search_bund)) {
				$fluid_search->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE " . $query_search_stock . " AND (" . $f_search_bund . ") GROUP BY p.p_id ORDER BY p.p_id");

				if(isset($fluid_search->db_array)) {
					foreach($fluid_search->db_array as $b_data) {
						$f_search_bund_array[$b_data['p_id']] = $b_data;
						//$f_search_bund_array[$b_data['p_id']]['f_delete_item'] = TRUE; // This is causing certain items from not displaying when formula link #9 is used sometimes.
					}
				}
			}

			$fluid_search->php_db_commit();

			if(isset($f_search_bund_array)) {
				if(isset($fluid->db_array)) {
					foreach($fluid->db_array as $fs_data) {
						$f_search_data_array[$fs_data['p_id']] = $fs_data;
					}

					foreach($f_search_bund_array as $fs_data) {
						$f_search_data_array[$fs_data['p_id']] = $fs_data;
					}
				}
			}
			else {
				$f_search_data_array = $fluid->db_array;
			}

			foreach($f_search_data_array as $data) {
				if($data['p_stock'] < 1)
					$data['p_enable'] = $data['p_zero_status'];

				if($data['p_enable'] > 0) {
					// Scan the $data item, look for FORMULA_OPTION_8, then generate a promotional item as required.

					if($data['p_formula_status'] == 1 && ($data['p_formula_operation'] == FORMULA_OPTION_8 || $data['p_formula_operation'] == FORMULA_OPTION_9) && ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) )) {
						$f_b_items = json_decode($data['p_formula_items_data']);
						$f_array = NULL;
						foreach($f_b_items as $f_idata) {
							$f_array[base64_decode($f_idata)] = base64_decode($f_idata);

							if(count($f_array) > 1 || $f_bundle_count > 0)
								$f_bundle_select .= ", ";

							$f_bundle_select .= "'" . $fluid->php_escape_string(base64_decode($f_idata)) . "'";

							$f_bundle_count++;
						}

						$f_items[] = Array("data" => $data, "bundle_items" => $f_array);
					}
					else {
						$f_items[] = Array("data" => $data);
					}
				}
			}

			$f_bundle_items = NULL;
			// Any bundles, lets grab them.
			if(isset($f_bundle_select)) {
				//$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE p.p_enable > '0' AND c.c_enable = 1 AND p_mfgcode IN (" . $f_bundle_select . ") HAVING p_zero_status_tmp > 0 ORDER BY p_mfgcode ASC");
				$fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock < 1 AND p.p_showalways > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id WHERE " . $query_search_stock . " AND p_mfgcode IN (" . $f_bundle_select . ") ORDER BY p_mfgcode ASC");

				if(isset($fluid->db_array)) {
					foreach($fluid->db_array as $f_b_items) {
						$f_bundle_items[$f_b_items['p_mfgcode']] = $f_b_items;
					}
				}
			}

			foreach($f_items as $f_data) {
				$last_id = $data['p_sortorder'];
				$bool_found_items = TRUE;
				$i_item_count++;

				$f_data['data']['position'] = $i_item_count + 1;

				if(isset($f_data['bundle_items'])) {
					foreach($f_data['bundle_items'] as $fb_item_key) {
						if(isset($f_bundle_items[$fb_item_key])) {
							$fb_item = NULL;
							$fb_item = $f_bundle_items[$fb_item_key];

							$fb_item['p_bundle_item'] = $f_data['data'];

							$fb_item['p_discount_date_start'] = $fb_item['p_bundle_item']['p_formula_discount_date_start'];
							$fb_item['p_discount_date_end'] = $fb_item['p_bundle_item']['p_formula_discount_date_end'];

							// --> Lets see if we need to flip some data around.
							if($f_data['data']['p_formula_flip'] == 1) {
								if($fb_item['m_id'] != $f_data['data']['m_id'])
									$fb_item['p_name'] = $f_data['data']['p_name'] . " w/" . $fb_item['m_name'] . " " . $fb_item['p_name'];
								else
									$fb_item['p_name'] = $f_data['data']['p_name'] . " w/" . $fb_item['p_name'];
							}
							else {
								if($fb_item['m_id'] != $f_data['data']['m_id'])
									$fb_item['p_name'] .= " w/" . $f_data['data']['m_name'] . " " . $f_data['data']['p_name'];
								else
									$fb_item['p_name'] .= " w/" . $f_data['data']['p_name'];
							}

							$fb_item['p_images'] = base64_encode(json_encode((object) array_merge((array) json_decode(base64_decode($fb_item['p_images'])), (array) json_decode(base64_decode($f_data['data']['p_images'])))));

							$return .= php_html_item_card($fb_item);
							$f_total_bundles++;
						}
					}

					if(base64_decode($data_obj->cat_id) != "bundles" || (base64_decode($data_obj->cat_id) == "blackfriday" || FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweekend" || FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "blackfridayweek" || FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "cybermonday" || FLUID_BLACK_FRIDAY == TRUE) || (base64_decode($data_obj->cat_id) == "boxingweek" || FLUID_BLACK_FRIDAY == TRUE) || base64_decode($data_obj->cat_id) == "all")
						$return .= php_html_item_card($f_data['data']);
				}
				else {
					if(empty($f_data['data']['f_delete_item'])) {
						$return .= php_html_item_card($f_data['data']);
					}
				}
			}
		}
		else {
			$return .= "<div class='row row-product-not-found-container'>";
				$return .= "<p class='product-not-found-text'>No products found.</p>";
			$return .= "</div>";
		}

		$fluid->php_db_commit();

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$f_item_start = $f_item_start + 1;
			$new_item_count = $i_item_count + $data_obj->item_count;
			if(isset($data_obj->reload))
				if($data_obj->reload == TRUE)
					$new_item_count = $i_item_count;

			$total_items_tmp = $f_total_bundles + $total_items;
			$new_item_count_tmp = $new_item_count + $f_total_bundles;

			$listing_counter_html = $new_item_count_tmp . " of " . $total_items_tmp;
		}
		else {
			$new_item_count = $i_item_count;

			$f_total_list = $item_start + $new_item_count;

			$f_item_start = $item_start + 1;

			$total_items_tmp = $f_total_bundles + $total_items;
			$f_total_list_tmp = $f_total_list + $f_total_bundles;

			$listing_counter_html = $f_item_start . " - " . $f_total_list_tmp . " <div style='display: inline-block; font-size: 80%;'>of</div> "  . $total_items_tmp;
		}

		$f_item_list = NULL;
		$f_ga_pos = $f_item_start;
		// $f_item_list gets sent to js_ga_listings on page load to feed to google for product listings.
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $f_items_tmp) {
				$f_item_list[] = Array("p_id" => $f_items_tmp['p_id'], "p_mfgcode" => $f_items_tmp['p_mfgcode'], "p_mfg_number" => $f_items_tmp['p_mfg_number'], "p_mfgid" => $f_items_tmp['p_mfgid'], "p_name" => $f_items_tmp['p_name'], "p_price" => $f_items_tmp['p_price'], "m_name" => $f_items_tmp['m_name'], "p_catid" => $f_items_tmp['p_catid'], "m_id" => $f_items_tmp['m_id'], "p_mfgid" => $f_items_tmp['p_mfgid'], "c_name" => $f_items_tmp['c_name'], "p_position" => $f_ga_pos);

				$f_ga_pos++;
			}
		}

		if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
			$execute_functions[]['function'] = "js_html_insert";
			$execute_functions[]['function'] = "js_fluid_listing_update";

			return json_encode(array("html" => base64_encode(utf8_decode($return)), "listing_counter_html" => base64_encode($listing_counter_html), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("container-fluid-listing"), "item_count" => base64_encode($new_item_count), "item_page" => base64_encode($data_obj->item_page), "item_page_next" => base64_encode($data_obj->item_page + 1), "item_page_previous" => base64_encode($data_obj->item_page - 1), "item_start" => base64_encode($item_start), "total_items" => base64_encode($total_items), "bool_found_items" => $bool_found_items, "item_list" => base64_encode(json_encode($f_item_list)), "item_list" => base64_encode(json_encode($f_item_list)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
		else {
			$mode = "f-listing";
			$f_pagination_function = "js_fluid_listing_page_change";

			$f_pagination_html = "<div class='f-pagination'>" . $fluid->php_pagination($total_items, FLUID_ADMIN_PAGINATION_LIMIT, $item_page, $f_pagination_function, $mode, NULL, "FluidListing.item_page") . "</div>";

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("container-fluid-listing"), "html" => base64_encode($return))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("f-pagination-bottom"), "html" => base64_encode($f_pagination_html))));

			$execute_functions[]['function'] = "js_fluid_listing_update";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("listing_counter_html" => base64_encode($listing_counter_html), "div_id" => base64_encode("container-fluid-listing"), "item_count" => base64_encode($new_item_count), "item_page" => base64_encode($data_obj->item_page), "item_page_next" => base64_encode($data_obj->item_page + 1), "item_page_previous" => base64_encode($data_obj->item_page - 1), "item_start" => base64_encode($item_start), "total_items" => base64_encode($total_items), "bool_found_items" => $bool_found_items, "item_list" => base64_encode(json_encode($f_item_list)))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => 0, "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
	}
}

function php_main_fluid_listing($data_obj = NULL) {
	require_once("header.php");

	if(empty($_SESSION['fluid_listing_display']))
		$_SESSION['fluid_listing_display'] = 0;

	if(isset($_GET['cat_id'])) {
		if($_GET['cat_id'] == "bundles")
			$cat_data = Array("c_name" => "Deals & Bundles", "c_seo" => "<meta name=\"keywords\" content=\"Deals and deep discounts\">");
		else if($_GET['cat_id'] == "all")
			$cat_data = Array("c_name" => "All Deals", "c_seo" => "<meta name=\"keywords\" content=\"Deals and deep discounts\">");
		else if($_GET['cat_id'] == "blackfriday")
			$cat_data = Array("c_name" => "Black Friday Deals", "c_seo" => "<meta name=\"keywords\" content=\"Black Friday Deals and discounts\">");
		else if($_GET['cat_id'] == "blackfridayweekend")
			$cat_data = Array("c_name" => "Black Friday Weekend Deals", "c_seo" => "<meta name=\"keywords\" content=\"Black Friday Weekend Deals and discounts\">");
		else if($_GET['cat_id'] == "blackfridayweek")
			$cat_data = Array("c_name" => "Black Friday Week Deals", "c_seo" => "<meta name=\"keywords\" content=\"Black Friday Week Deals and discounts\">");
		else if($_GET['cat_id'] == "cybermonday")
			$cat_data = Array("c_name" => "Cyber Monday Deals", "c_seo" => "<meta name=\"keywords\" content=\"Cyber Monday Deals and discounts\">");
		else if($_GET['cat_id'] == "boxingweek")
			$cat_data = Array("c_name" => "Boxing Week Deals", "c_seo" => "<meta name=\"keywords\" content=\"Boxing Week Deals and discounts\">");
		else if($_GET['cat_id'] == "sigma")
			$cat_data = Array("c_name" => "Sigma Lenses", "c_seo" => "<meta name=\"keywords\" content=\"Sigma USB Dock qualifying lens deals\">");
		else
			$cat_data = php_category_data(base64_encode($_GET['cat_id']));
	}

	$fluid_header = new Fluid();
?>
	<!DOCTYPE html>

	<html lang="en">
	<head>
		<?php
		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			//$_SESSION['u_oauth_id']
		?>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-21150353-5"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-21150353-5');

			  <?php
			  if(isset($_SESSION['u_oauth_id']))
				echo "gtag('set', {'user_id': '" . $_SESSION['u_oauth_id'] . "'});";
			  ?>
			</script>
		<?php
		}
		?>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->?>
		<?php
		if(isset($cat_data))
			echo $cat_data['c_seo'];
		?>
		<title>Leos Camera Supply
		<?php
		if(isset($cat_data))
			echo $cat_data['c_name'];
		else if(isset($_REQUEST['f_search']))
			echo "Search results for " . $_REQUEST['f_search'];
		?>
		</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $fluid_header->php_fluid_auto_version(FOLDER_ROOT, 'css/fluid-listing.css');?>">
	<?php
	php_load_pre_header();
	?>
	</head>

	<body>
	<?php
	php_load_header();

	$detect = new Mobile_Detect;

	$f_detect = TRUE;

	if($detect->isiOS()) {
		if($detect->isTablet())
			$f_detect = TRUE;
	}
	else
		$f_detect = TRUE;
	?>

	<style>
		.fluid-filter-fixed-position {
			position: fixed !important;
			<?php
			if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE))
				echo "top: 56px !important;";
			else
				echo "top: 0px !important;";
			?>
		}

		@media (max-width: 599px) {
			.fluid-filter-fixed-position {
				left: 0;
				right: 0;
				width: 94vw;
				margin: auto;
			}
		}

		@media (max-width: 767px) {
			.fluid-filter-fixed-position {
				left: 0;
				right: 0;
				width: auto;
				margin: auto;
			}
		}

		<?php
		if($_SESSION['fluid_listing_display'] == 0) {
		?>
			.fa-list-stock {
				text-align: left;
			}

			@media (min-width: 768px) {
				.fa-list-stock {
					text-align: right;
				}
			}
		<?php
		}
		else {
		?>
			.fa-list-stock {
				text-align: center;
			}

			@media (min-width: 768px) {
				.fa-list-stock {
					text-align: center;
				}
			}
		<?php
		}
		?>
	</style>

	<script>
	var FluidListing = {};
		FluidListing.Filters = {};
		FluidListing.Filters_mfg = {};
		FluidListing.Filters_price = {};
		FluidListScroll = 0;

		<?php
		if(isset($_GET['cat_id']))
			echo 'FluidListing.cat_id = "' . base64_encode($_REQUEST["cat_id"]) . '";';
		else
			echo 'FluidListing.cat_id = null;';

		if(isset($_REQUEST['f_search']))
			echo 'FluidListing.f_search = "' . $_REQUEST['f_search'] . '";';
		?>

		var is_loading = false; <?php // initialize is_loading by false to accept new loading ?>
		var footer_height = 0;
		var doc_width = parseInt($(document).width());

		$(function() {
			$('.dropdown-accordion').on('click', 'div[data-toggle="collapse"]', function (event) {
				event.preventDefault();
				event.stopPropagation();
				$($(this).data('parent')).find('.panel-collapse.in').collapse('hide');
				$($(this).attr('href')).collapse('show');
			})

			<?php
			/*
			var btn = document.getElementById('fluid-filter-button');

			if(btn != null) {
				if(parseInt($(window).width()) > 599) {
					btn.className = btn.className.replace( /(?:^|\s)btn-xs(?!\S)/g , '' )
				}
				else {
					btn.className += " btn-sm";
				}
			}



			$(window).on('resize orientationChange', function(event) {
				var btn = document.getElementById('fluid-filter-button');

				if(btn != null) {
					if(parseInt($(window).width()) > 599) {
						btn.className = btn.className.replace( /(?:^|\s)btn-sm(?!\S)/g , '' )
					}
					else {
						btn.className += " btn-sm";
					}
				}

			});
			*/
			?>
			$(window).scroll(function() {
				<?php
				//if(FLUID_LISTING_FILTERS_PINNED == TRUE) {
				if(($detect->isMobile() == FALSE && FLUID_LISTING_FILTERS_PINNED == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_LISTING_FILTERS_PINNED == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_LISTING_FILTERS_PINNED_MOBILE == TRUE)) {
				?>
				var f_filters = document.getElementById('fluid_listing_counter_div');

					if(f_filters != null) {
						var f_filter_pos_y = $('#fluid-breadcrumb-div-hidden').offset().top - $(window).scrollTop();
						<?php
						//if(FLUID_NAVBAR_PIN == TRUE) {
						if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE))	 {
							if($f_detect == TRUE)
								echo "var f_fixed_pos = 28;";
							else
								echo "var f_fixed_pos = -28;";
						}
						else {
							if($f_detect == TRUE)
								echo "var f_fixed_pos = -28;";
							else
								echo "var f_fixed_pos = -28;";
						}
						?>

						if(f_filter_pos_y <= f_fixed_pos) {
							if(FluidListScroll == 0) {
								f_filters.className = f_filters.className.replace( /(?:^|\s)fluid-filter-fixed-position(?!\S)/g , '' );
								f_filters.className += " fluid-filter-fixed-position";

								var f_viewport_size = js_viewport_size()['width'];

								if(f_viewport_size < 768) {
									<?php
									//if(FLUID_NAVBAR_PIN == TRUE) {
									if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) {
									?>
										if(f_viewport_size < 600)
											$('#fluid-filter-div-hidden').css({'margin-top' : '100px'});
										else
											$('#fluid-filter-div-hidden').css({'margin-top' : '140px'});
									<?php
									}
									else {
									?>
										if(f_viewport_size < 600)
											$('#fluid-filter-div-hidden').css({'margin-top' : '100px'});
										else
											$('#fluid-filter-div-hidden').css({'margin-top' : '140px'});
									<?php
									}
									?>
								}
								else {
									<?php
									//if(FLUID_NAVBAR_PIN == TRUE) {
									if(($detect->isMobile() == FALSE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == TRUE && FLUID_NAVBAR_PIN == TRUE) || ($detect->isMobile() == TRUE && $detect->isTablet() == FALSE && FLUID_NAVBAR_PIN_MOBILE == TRUE)) {
									?>
										$('#fluid-filter-div-hidden').css({'margin-top' : '140px'});
									<?php
									}
									else {
									?>
										$('#fluid-filter-div-hidden').css({'margin-top' : '140px'});
									<?php
									}
									?>
								}

								FluidListScroll = 1;
							}
						}
						else {
							if(FluidListScroll == 1) {
								f_filters.className = f_filters.className.replace( /(?:^|\s)fluid-filter-fixed-position(?!\S)/g , '' );
								$('#fluid-filter-div-hidden').css({'margin-top' : '0px'});

								FluidListScroll = 0;
							}
						}
					}
				<?php
				}
				?>

				<?php
				if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
				?>
					footer_height = document.getElementById('footer_fluid').clientHeight + 100;
					if(parseInt($(window).scrollTop()) + parseInt($(window).height() + footer_height) > parseInt($(document).height()) && FluidListing.item_count_total < FluidListing.total_items && FluidListing.item_count > 0) {
						<?php // stop loading many times for the same page ?>
						if (is_loading == false) {
							<?php
							if(isset($_REQUEST['f_search'])) {
							?>
								history.pushState('', '', window.location.href.split(/[?#]/)[0] + '?f_search=' + FluidListing.f_search + '&fdata=' + Base64.encode(JSON.stringify(FluidListing)));
							<?php
							}
							else {
							?>
								history.pushState('', '', window.location.href.split(/[?#]/)[0] + '?fdata=' + Base64.encode(JSON.stringify(FluidListing)));
							<?php
							}
							?>

							<?php // set is_loading to true to refuse new loading ?>
							is_loading = true;

							<?php // Set the item page to load to be the next one. ?>
							var item_page = FluidListing.item_page;
							FluidListing.item_page = FluidListing.item_page_next;

							<?php // display the waiting loader ?>
							$('#fluid_loader').show();

							<?php // execute an ajax query to load more items. ?>
							$.ajax({
								url: '<?php echo $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING; ?>',
								type: 'POST',
								<?php
								if(isset($_REQUEST['f_search']))
									echo "data: {load_func:'1', data: Base64.encode(JSON.stringify(FluidListing)), fluid_function:'php_fluid_search'},";
								else
									echo "data: {load_func:'1', data: Base64.encode(JSON.stringify(FluidListing)), fluid_function:'php_html_item_listing'},";
								?>
								success:function(data){
									var data_obj = JSON.parse(data);

									$('#fluid_loader').hide();
									is_loading = false;

									if(data_obj['error'] > 0 && typeof data_obj['error'] != 'undefined') {
										FluidListing.item_page = item_page;
										js_debug_error(Base64.decode(data_obj['error_message']));
									}
									else if(data_obj['bool_found_items'] == true) {
										js_ga_listings(JSON.parse(Base64.decode(data_obj['item_list'])));
										<?php // append: add the new statements to the existing data ?>
										$('#container-fluid-listing').append(Base64.decode(data_obj['html']));
										<?php //document.getElementById('container-fluid-listing').innerHTML = Base64.decode(data_obj['html']); ?>

										<?php // Update the select pickers. ?>
										<?php //$('select').selectpicker(); ?>

										js_fluid_listing_update(data_obj);

									}
								}
							});
						}
				   }
			   <?php
				}
			   ?>
			});
		});

		function js_fluid_listing_page_change(f_page_num, mode, dmode) {
			try {
				<?php
				/*
				var FluidData = {};
				FluidData.f_page_num = f_page_num;
				FluidData.f_selection = FluidSelector.v_selection;
				FluidData.f_refresh = true;
				FluidData.mode = mode;
				*/
				?>
				FluidListing.item_page = f_page_num;

				var data = base64EncodingUTF8(JSON.stringify(FluidListing));

				<?php
				if(isset($_REQUEST['f_search'])) {
				?>
					var f_url = window.location.href.split(/[?#]/)[0] + '?f_search=' +  encodeURIComponent(FluidListing.f_search) + '&fdata=' + Base64.encode(JSON.stringify(FluidListing));
					<?php
					/*
					//history.pushState('', '', f_url);

					//var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ITEM_LISTING;?>", dataobj: "load_func=1&fluid_function=php_fluid_search&data=" + data}));
					//echo "data: {load_func:'1', data: Base64.encode(JSON.stringify(FluidListing)), fluid_function:'php_fluid_search'},";
					*/
					?>
				<?php
				}
				else {
				?>
					var f_url = window.location.href.split(/[?#]/)[0] + '?fdata=' + Base64.encode(JSON.stringify(FluidListing));
					<?php
					/*
					history.pushState('', '', f_url);

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ITEM_LISTING;?>", dataobj: "load_func=1&fluid_function=php_html_item_listing&data=" + data}));
					echo "data: {load_func:'1', data: Base64.encode(JSON.stringify(FluidListing)), fluid_function:'php_html_item_listing'},";
					*/
					?>
				<?php
				}
				?>

				<?php //js_fluid_ajax(data_obj); ?>
				js_redirect_url({url:Base64.encode(f_url)});
			}
			catch (err) {
				js_debug_error(err);
			}
		}

		function js_fluid_listing_data() {

		}

		<?php // --> Google tracking ?>
		function js_ga_listings(data) {
			var f_found = false;
			if(data != null) {
				$.each(data, function(key, value) {
					if(value != null) {
						<?php
						if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
						?>
							ga('ec:addImpression', {
							  'id': value['p_mfgcode'],
							  'name': value['m_name'] + " " + value['p_name'],
							  'category': value['c_name'],
							  'brand': value['m_name'],
							  'variant': value['p_mfgcode'],
							  'list': document.title,
							  'position': value['p_position']
							});
						<?php
						}
						?>

						f_found = true;
					}
				}
				);
			}

			<?php
			if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			?>
				if(f_found == true) {
					 ga('send', 'pageview');
				}
			<?php
			}
			?>
		}

		<?php // When a filter is checked, refresh the category as required. ?>
		function js_filter_check(filter_check) {
			try {
				if(filter_check.checked == true)
					FluidListing.Filters[filter_check.value] = {"filter_obj" : filter_check.value};
				else if(FluidListing.Filters.hasOwnProperty(filter_check.value))
					delete FluidListing.Filters[filter_check.value];

				js_fluid_listing_refresh();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		<?php // When the brand filter is checked, refresh as required. ?>
		function js_filter_mfg_check(mfg_check) {
			try {
				if(mfg_check.checked == true)
					FluidListing.Filters_mfg[mfg_check.value] = mfg_check.value;
				else
					delete FluidListing.Filters_mfg[mfg_check.value];

				js_fluid_listing_refresh();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		<?php // When the price filter is checked, refresh as required. ?>
		function js_filter_price_check(price_check) {
			try {
				if(price_check.checked == true)
					FluidListing.Filters_price[price_check.value] = price_check.value;
				else
					delete FluidListing.Filters_price[price_check.value];

				js_fluid_listing_refresh();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_list_display(f_mode, f_search) {
			try {
				var f_data = {};
					f_data.mode = f_mode;
					f_data.url = "<?php echo base64_encode($_SERVER['REQUEST_URI']); ?>";
					f_data.f_search = f_search;

				var data = Base64.encode(JSON.stringify(f_data));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . "/" .  FLUID_ITEM_LISTING;?>", dataobj: "load_func=true&fluid_function=php_listing_display_update&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}


		<?php // Refreshes the item listing. ?>
		function js_fluid_listing_refresh() {
			try {
				js_reset_fluid_listing();
				var data = Base64.encode(JSON.stringify(FluidListing));

				<?php
					if(isset($_REQUEST['f_search'])) {
						?>
						var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ITEM_LISTING;?>", dataobj: "load_func=true&fluid_function=php_fluid_search&data=" + data}));
						<?php
					}
					else {
						?>
						var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ITEM_LISTING;?>", dataobj: "load_func=true&fluid_function=php_html_item_listing&data=" + data}));
						<?php
					}
				?>

				<?php
				if(isset($_REQUEST['f_search'])) {
				?>
					history.pushState('', '', window.location.href.split(/[?#]/)[0] + '?f_search=' + FluidListing.f_search + '&fdata=' + Base64.encode(JSON.stringify(FluidListing)));
				<?php
				}
				else {
				?>
					history.pushState('', '', window.location.href.split(/[?#]/)[0] + '?fdata=' + Base64.encode(JSON.stringify(FluidListing)));
				<?php
				}
				?>

				js_fluid_ajax(data_obj);
				js_fluid_scroll_to_top();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		<?php // Updates the displayed item counter and other various data for listing items. ?>
		function js_fluid_listing_update(data_obj) {
			try {
				if(typeof document.getElementById('fluid_listing_counter') != "undefined" && document.getElementById('fluid_listing_counter') != null)
					document.getElementById('fluid_listing_counter').innerHTML = Base64.decode(data_obj['listing_counter_html']);

				FluidListing.item_page = parseInt(Base64.decode(data_obj['item_page']));
				FluidListing.item_page_next = parseInt(Base64.decode(data_obj['item_page_next']));
				FluidListing.item_page_previous = parseInt(Base64.decode(data_obj['item_page_previous']));
				FluidListing.item_start = parseInt(Base64.decode(data_obj['item_start']));
				FluidListing.total_items = parseInt(Base64.decode(data_obj['total_items']));
				FluidListing.item_count = parseInt(Base64.decode(data_obj['item_count']));
				FluidListing.item_count_total = parseInt(Base64.decode(data_obj['item_count']));

				<?php // Reset the countdown clock timers for special deals as required. ?>
				initializeCountdown();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		<?php // When the sort by is changed, reload the items. ?>
		function js_listing_sort_by(select) {
			try {
				FluidListing.sort_by = select.value;

				$('html, body').animate({ scrollTop: 0 }, 800);

				js_fluid_listing_refresh();
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		<?php // Reset some fluid listing values before a reload of items. ?>
		function js_reset_fluid_listing() {
			FluidListing.item_page = 1;
			FluidListing.item_page_next = 2;
			FluidListing.item_page_previous = 0;
			FluidListing.item_start = 0;
			FluidListing.item_count = 0;
			FluidListing.item_count_total = 0; // <- How many items we have displayed on the page.
		}
	</script>

	<div class="container-fluid container-search" style="margin-top: 0px; background-color: #f3f1f2;">

	<?php
	if(isset($cat_data) || isset($_REQUEST['f_search'])) {
	?>
		<div class="row row-product-container" style="display: table; margin-top: 30px;">
				<?php
					//Array ( [c_id] => 1 [c_name] => Mirrorless Cameras [c_parent_id] => 6 [c_parent_name] => Cameras
					// http://local.leoscamera.com/fluid.listing.php?cat_id=MQ==
					echo "<div id='breadcrumbs' style='display: table-cell; vertical-align: middle;'>";
					echo "<a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . "\" onClick='js_loading_start();'
					>Home</a>";

					if(isset($_REQUEST['f_search'])) {
						echo " / search results : " . $_REQUEST['f_search'] ."</div>";
					}
					else {
						if(isset($cat_data['c_parent_id'])) {
							$fluid = new Fluid ();

							echo " / <a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $cat_data['c_parent_id'] . "/" . $fluid->php_clean_string($cat_data['c_parent_name']) . "\" onClick='js_loading_start();'>" . $cat_data['c_parent_name'] . "</a>";
						}

						echo " / " . $cat_data['c_name'] . "</div>";
					}

					echo "<div style='display: table-cell; text-align: right; min-width: 100px;'><div style='display: inline-block;'>";

					if(empty($_SESSION['fluid_listing_display']))
						$_SESSION['fluid_listing_display'] = 0;

						$f_search = base64_encode(json_encode(Array("f_search" => FALSE, "f_keys" => NULL)));

						if(isset($_REQUEST['f_search'])) {
							//if($_REQUEST['f_search'] == TRUE) {
								$f_search = base64_encode(json_encode(Array("f_search" => TRUE, "f_keys" => base64_encode($_REQUEST['f_search']))));
							//}
						}
					?>
						  <div class="btn<?php if($_SESSION['fluid_listing_display'] == 0) { echo " fluid-btn-grey disabled"; } else { echo " btn-default"; } ?>" <?php if($_SESSION['fluid_listing_display'] == 1) { echo " onClick='js_fluid_list_display(0, \"" . $f_search . "\");'"; } ?> aria-label="Horizontal"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></div>

						  <div class="btn<?php if($_SESSION['fluid_listing_display'] == 1) { echo " fluid-btn-grey disabled"; } else { echo " btn-default"; } ?>" <?php if($_SESSION['fluid_listing_display'] == 0) { echo " onClick='js_fluid_list_display(1, \"" . $f_search . "\");'"; } ?> aria-label="Vertical"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span></div>
					<?php
					echo "</div></div>";

				?>
		</div>
		<div id='fluid-breadcrumb-div-hidden'></div>

		<div id='fluid-filter-div-hidden' style='position: relative;'></div>

		<div class="row row-product-container" style="margin-top: 30px;">
		<div id="fluid_loader_prepend" style="margin-bottom: 20px; width: 100%; display:none;"><div style="width: 50px; margin: 0 auto;"><i class="fa fa-refresh fa-spin-fluid fa-3x fa-fw"></i><span class="sr-only">Loading...</span></div></div>
				<?php
					if(empty($_REQUEST['f_search'])) {
						$tmp_cat_id = NULL;
						if(isset($_REQUEST['cat_id']))
							$tmp_cat_id = base64_encode($_REQUEST['cat_id']);

						$f_data = Array("total_items" => 0, "item_count" => 0, "item_page" => 1, "sort_by" => "featured", "Filters" => NULL, "Filters_mfg" => NULL, "Filters_price" => NULL);

						if($_REQUEST['fdata']) {
							$f_data_tmp = json_decode(base64_decode($_REQUEST['fdata']));

							if(isset($f_data_tmp->total_items))
								$f_data['total_items'] = $f_data_tmp->total_items;

							if(isset($f_data_tmp->item_count))
								$f_data['item_count'] = $f_data_tmp->item_count;

							if(isset($f_data_tmp->item_page))
								$f_data['item_page'] = $f_data_tmp->item_page + 1;

							if(isset($f_data_tmp->sort_by))
								$f_data['sort_by'] = $f_data_tmp->sort_by;

							if(isset($f_data_tmp->Filters))
								$f_data['Filters'] = $f_data_tmp->Filters;

							if(isset($f_data_tmp->Filters_mfg))
								$f_data['Filters_mfg'] = $f_data_tmp->Filters_mfg;

							if(isset($f_data_tmp->Filters_price))
								$f_data['Filters_price'] = $f_data_tmp->Filters_price;

							// Prep data and load the category specific filters as required.
							$data = (object)Array("cat_id" => $tmp_cat_id, "item_page" => $f_data['item_page'], "item_count" => $f_data['item_count'], "sort_by" => $f_data['sort_by'], "Filters" => $f_data['Filters'], "Filters_mfg" => $f_data['Filters_mfg'], "Filters_price" => $f_data['Filters_price'], "reload" => TRUE);
						}
						else {
							// Prep data and load the category specific filters as required.
							$data = (object)Array("cat_id" => $tmp_cat_id, "item_page" => $f_data['item_page'], "item_count" => $f_data['item_count'], "sort_by" => "featured");
						}

						$filter_html = json_decode(php_html_filters($data));

						// Get the item listing data and html.
						if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
							$data_html = json_decode(php_html_item_listing($data));
							$data_ga_list = json_decode(base64_decode($data_html->item_list));
						}
						else {
							$data_html_tmp = json_decode(php_html_item_listing($data));
							$data_tmp = json_decode(base64_decode($data_html_tmp->js_execute_functions));
							$data_func = json_decode(base64_decode($data_tmp[0]->data));
							$data_pagination = json_decode(base64_decode($data_tmp[1]->data));
							$data_html = json_decode(base64_decode($data_tmp[2]->data));

							// --> For Google tracking.
							$data_ga_list = json_decode(base64_decode($data_html->item_list));

							$data_html->html = $data_func->html;
							$data_html->pagination = $data_pagination->html;
						}

					}
					else if(isset($_REQUEST['f_search'])) {
						$data = (object)Array("f_search" => $_REQUEST['f_search'], "item_page" => 1, "item_count" => 0, "sort_by" => "relevance");

						$f_data = Array("total_items" => 0, "item_count" => 0, "item_page" => 1, "sort_by" => "relevance", "Filters" => NULL, "Filters_mfg" => NULL, "Filters_price" => NULL);

						if($_REQUEST['fdata']) {
							$f_data_tmp = json_decode(base64_decode($_REQUEST['fdata']));

							if(isset($f_data_tmp->total_items))
								$f_data['total_items'] = $f_data_tmp->total_items;

							if(isset($f_data_tmp->item_count))
								$f_data['item_count'] = $f_data_tmp->item_count;

							if(isset($f_data_tmp->item_page))
								$f_data['item_page'] = $f_data_tmp->item_page + 1;

							if(isset($f_data_tmp->sort_by))
								$f_data['sort_by'] = $f_data_tmp->sort_by;

							if(isset($f_data_tmp->Filters))
								$f_data['Filters'] = $f_data_tmp->Filters;

							if(isset($f_data_tmp->Filters_mfg))
								$f_data['Filters_mfg'] = $f_data_tmp->Filters_mfg;

							if(isset($f_data_tmp->Filters_price))
								$f_data['Filters_price'] = $f_data_tmp->Filters_price;

							// Prep data and load the category specific filters as required.
							$data = (object)Array("f_search" => $_REQUEST['f_search'], "item_page" => $f_data['item_page'], "item_count" => $f_data['item_count'], "sort_by" => $f_data['sort_by'], "Filters" => $f_data['Filters'], "Filters_mfg" => $f_data['Filters_mfg'], "Filters_price" => $f_data['Filters_price'], "reload" => TRUE);
						}
						else {
							// Prep data and load the category specific filters as required.
							$data = (object)Array("f_search" => $_REQUEST['f_search'], "item_page" => 1, "item_count" => 0, "sort_by" => "relevance");
						}

						// 1. -- > Need to implement this for search.
						//$filter_html = json_decode(php_html_filters($data)); // --> Tthe $data obj needs a cat_id

						// Get the item listing data and html.
						if(FLUID_LISTING_INFINITE_SCROLLING == TRUE) {
							$data_html = json_decode(php_fluid_search($data));
							$data_ga_list = json_decode(base64_decode($data_html->item_list));
						}
						else {
							// Get the item listing data and html.
							$data_html_tmp = json_decode(php_fluid_search($data));
							$data_tmp = json_decode(base64_decode($data_html_tmp->js_execute_functions));
							$data_func = json_decode(base64_decode($data_tmp[0]->data));
							$data_pagination = json_decode(base64_decode($data_tmp[1]->data));
							$data_html = json_decode(base64_decode($data_tmp[2]->data));

							// --> For Google tracking.
							$data_ga_list = json_decode(base64_decode($data_html->item_list));

							$data_html->html = $data_func->html;
							$data_html->pagination = $data_pagination->html;
						}
					}

				?>

			<?php
			if(isset($data_html->bool_found_items)) {
				if($data_html->bool_found_items) {
			?>
					<div class="row row-product-container fluid-listing-counter-div fluid-box-shadow-transparent fluid-listing-filter-div fluid-listing-filter-md" id="fluid_listing_counter_div" style='z-index: 1090;'>
						<div class="fluid-listing-counter-div-form">
							<?php
								echo "<div class='fluid-listing-counter-name'>";
									if(isset($cat_data))
										echo $cat_data['c_name'];
									else if(isset($_REQUEST['f_search']))
										echo "Search results";
								echo ":</div>";
								echo "<div id='fluid_listing_counter' class='fluid-listing-counter'>" . base64_decode($data_html->listing_counter_html) . "</div>";
							?>
						</div>
						<div class="fluid-listing-sort-div">
							<div class="fluid-listing-sort-by-div">Sort by:</div>
							<div style="display: inline-block;">
								<select class="btn-group bootstrap-select form-control bootstrap-select f-bootstrap f-sortby show-menu-arrow show-tick" data-style="btn btn-default btn-sm fluid-filter-select" data-width="auto" onchange="js_listing_sort_by(this);" style=''>
									<?php
									if(isset($f_data['sort_by']))
										$f_sort = $f_data['sort_by'];
									else
										$f_sort = NULL;

									if($f_sort == "featured")
										$f_featured = " selected";
									else
										$f_featured = NULL;

									if($f_sort == "price_low_high")
										$f_price_low = " selected";
									else
										$f_price_low = NULL;

									if($f_sort == "price_high_low")
										$f_price_high = " selected";
									else
										$f_price_high = NULL;

									if($f_sort == "brand_a_z")
										$f_brand_a_z = " selected";
									else
										$f_brand_a_z = NULL;

									if($f_sort == "brand_z_a")
										$f_brand_z_a = " selected";
									else
										$f_brand_z_a = NULL;

									if($f_sort == "deals")
										$f_deals = " selected";
									else
										$f_deals = NULL;

									if($f_sort == "relevance")
										$f_relevance = " selected";
									else
										$f_relevance = NULL;

									if(isset($_REQUEST['f_search']))
										echo "<option value=\"relevance\"" . $f_relevance . ">Relevance</option>";

									echo "<option value=\"featured\"" . $f_featured . ">In Stock</option>";
									if(isset($_GET['cat_id'])) {
										if($_GET['cat_id'] != "bundles" && $_GET['cat_id'] != "all" && $_GET['cat_id'] != "blackfriday" && $_GET['cat_id'] != "blackfridayweekend" && $_GET['cat_id'] != "blackfridayweek" && $_GET['cat_id'] != "cybermonday" || $_GET['cat_id'] != "boxingweek" || $_GET['cat_id'] != "sigma")
											echo "<option value=\"deals\"" . $f_deals . ">Deals</option>";
									}
									else
										echo "<option value=\"deals\"" . $f_deals . ">Deals</option>";

									echo "<option value=\"price_low_high\"" . $f_price_low . ">Price: Low to High</option>";
									echo "<option value=\"price_high_low\"" . $f_price_high . ">Price: High to Low</option>";
									echo "<option value=\"brand_a_z\"" . $f_brand_a_z . ">Brand: A to Z</option>";
									echo "<option value=\"brand_z_a\"" . $f_brand_z_a . ">Brand: Z to A</option>";
									?>
								</select>
							</div>

							<?php
							if(isset($cat_data)) {
							?>
								<div style="float: right; display: inline-block;" class="dropdown dropdown-accordion" data-accordion="#accordion">
									<button id="fluid-filter-button" class="btn btn-primary btn-fluid-listing dropdown-toggle" data-toggle="modal" data-target="#fluid-filters-modal" aria-expanded="false"><span class="glyphicon glyphicon-filter" aria-hidden="true"></span> Filters</button>
								</div>
							<?php
							}
							?>
						</div>

					</div>
			<?php
				}
			} // --> bool_found_items
			?>

			<?php
			if(isset($data_html)) {
				if(isset($_REQUEST['f_search']))
					$f_sort = "relevance";
				if(isset($f_data['sort_by']))
					$f_sort = $f_data['sort_by'];
				else
					$f_sort = "featured";

				echo "<script>";
				echo " FluidListing.sort_by = '"  . $f_sort . "';";

				if(isset($data_html->item_count))
					$item_count = base64_decode($data_html->item_count);
				else
					$item_count = 0;

				if(isset($data_html->item_page_previous))
					$item_page_previous = base64_decode($data_html->item_page_previous);
				else
					$item_page_previous = 0;

				if(isset($data_html->item_page))
					$item_page = base64_decode($data_html->item_page);
				else
					$item_page = 0;

				if(isset($data_html->item_page_next))
					$item_page_next = base64_decode($data_html->item_page_next);
				else
					$item_page_next = 0;

				if(isset($data_html->total_items))
					$total_items = base64_decode($data_html->total_items);
				else
					$total_items = 0;

				if(isset($data_html->item_start))
					$item_start = base64_decode($data_html->item_start);
				else
					$item_start = 0;

				echo " FluidListing.item_count_total = " . $item_count . "; FluidListing.item_count = " . $item_count . "; FluidListing.item_page_previous = " . $item_page_previous . "; FluidListing.item_page = " . $item_page . "; FluidListing.item_page_next = " . $item_page_next . "; FluidListing.total_items = " . $total_items . "; FluidListing.item_start = " . $item_start . ";";

				if(isset($f_data['Filters'])) {
					foreach($f_data['Filters'] as $f_key => $f_filter)
						echo "FluidListing.Filters['" . $f_key . "'] = {\"filter_obj\" : \"" . $f_key . "\"};";
				}

				if(isset($f_data['Filters_mfg'])) {
					foreach($f_data['Filters_mfg'] as $f_mfg)
						echo "FluidListing.Filters_mfg['" . $f_mfg . "'] = '" . $f_mfg . "';";
				}

				if(isset($f_data['Filters_price'])) {
					foreach($f_data['Filters_price'] as $f_price)
						echo "FluidListing.Filters_price['" . $f_price . "'] = '" . $f_price . "';";
				}

				if(isset($data_ga_list)) {
					?>
					var Fluid_ga_items = JSON.parse(Base64.decode("<?php echo base64_encode(json_encode($data_ga_list));?>"));

					js_ga_listings(Fluid_ga_items);
					<?php
				}
				echo "</script>";
			}
			?>

			<?php
				if($_SESSION['fluid_listing_display'] == 0)
					$f_class = "fluid-container-listing";
				else
					$f_class = "fluid-container-listing fluid-container-listing-padding";

			?>
			<div id="container-fluid-listing" class="<?php echo $f_class; ?>">
				<?php
					if(isset($data_html->html)) {
						echo base64_decode($data_html->html);
					}
					else {
						?>
						<div class="row row-product-container" style="margin-top: 30px;">
							<div class="row row-product-container"  style="margin-bottom: 20px;">
								<div id="container-fluid-listing" class="container-fluid">
									<div class="row row-product-not-found-container">
										<p class="product-not-found-text">
											<?php
											if(isset($_REQUEST['f_search'])) {
												if(isset($_REQUEST['f_search_keywords']))
													echo "No results found for: " . $_REQUEST['f_search_keywords'];
												else
													echo "No results found.";
											}
											else
												echo "Products not found.";
											?>
										</p>
									</div>
								</div>
							</div> <!-- column end -->
						</div> <!-- row end -->
						<?php
					}

				?>
			</div> <!-- column end -->

			<?php
				if(FLUID_LISTING_INFINITE_SCROLLING == FALSE)
					if(isset($data_html->pagination))
						echo "<div id='f-pagination-bottom'>" . base64_decode($data_html->pagination) . "</div>";
			?>
			<div id="fluid_loader" style="margin-bottom: 20px; width: 100%; display:none;"><div style="width: 50px; margin: 0 auto;"><i class="fa fa-refresh fa-spin-fluid fa-3x fa-fw"></i><span class="sr-only">Loading...</span></div></div>

		</div> <!-- row end -->

	<?php
	}
	else {
		?>
		<div class="row row-product-container" style="margin-top: 30px;">
			<div class="row row-product-container"  style="margin-bottom: 20px;">
				<div id="container-fluid-listing" class="container-fluid">
					<div class="row row-product-not-found-container">
						<p class="product-not-found-text">Category not found.</p>
					</div>
				</div>
			</div> <!-- column end -->
		</div> <!-- row end -->
		<?php
	}
	?>

	</div> <!-- container-search end -->

	<?php
	require_once("footer.php");

	if(isset($data_html->bool_found_items)) {
		if($data_html->bool_found_items)
		echo "
			<div id=\"fluid-filters-modal\" class=\"modal fade\" role=\"dialog\">
				<div class=\"modal-dialog\">

					<div class=\"modal-content\">

						<div class=\"modal-header filter-header\">
							<div class=\"modal-title filter-title\"><span class=\"glyphicon glyphicon-filter\" aria-hidden=\"true\"></span> Item Filters</div>
						</div>

						<div id='modal-filters-div' class=\"modal-body modal-filters-div\">";

						if(isset($filter_html->html))
							print_r(base64_decode($filter_html->html));

						echo "</div>

						<div class=\"modal-footer filter-header\">
							<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">Close</button>
						</div>
					</div>

				</div>
			</div>";
	}
	?>

	</body>
	</html>
<?php
}
?>
