<?php
// fluid.search.suggestions.php
// Michael Rajotte - 2018 Novembre
// Search suggestion results.
require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;
//use Snipe\BanBuilder\CensorWords;

function php_search_suggestions($data = NULL) {
    try {
        // Needed to prevent the code to release the session so multiple requests can be made without stalling the ajax request.
        session_write_close();

        if(isset($_REQUEST['data'])) {
            $f_data = json_decode(base64_decode($_REQUEST['data']));
        }
        else if(isset($data)) {
            $f_data = (object)json_decode(base64_decode($data));
        }
        else {
            $f_data = NULL;
        }

        $f_search_results = NULL;

        if(isset($f_data->f_search_time)) {
            $f_time = intval($f_data->f_search_time);
        }
        else {
            $f_time = time();
        }

        $f_html = NULL;
        $f_totals = 0;
        $f_width = "900px";

        // Filter out blank searches.
        if(strlen(trim($f_data->f_search_input)) > 0) {
            // Scans products.
            $f_search_results = php_search_suggestion_process($f_data);

            // Scans categories, manufacturers and logs.
            $f_search_suggestions = php_search_suggestions_and_categories($f_data);

            if($f_search_results['total_items'] > 0) {
                $f_totals = $f_search_results['total_items'];

                if($f_search_suggestions['totals'] > 0) {
                    $f_html .= "<div class='row' style='padding-top: 5px; padding-bottom: 5px;'>";
                        $f_html .= "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12' style='border-right: 1px solid #cccccc;'>";
                            $f_html .= $f_search_results['html'];
                        $f_html .= "</div>";

                        $f_html .= "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-12'>";
                            $f_html .= $f_search_suggestions['c_html'] . $f_search_suggestions['s_html'];
                        $f_html .= "</div>";
                    $f_html .= "</div>";

                    $f_totals = $f_totals + $f_search_suggestions['totals'];
                }
                else {
                    $f_html = "<div class='row' style='padding-top: 5px; padding-bottom: 5px; margin-top: -15px;'>";
                        $f_html .= "<div style='max-width: 900px; margin: auto; padding: 0px 10px 0px 10px;'>";
                        $f_html .= $f_search_results['html'];
                        $f_html .= "</div>";
                    $f_html .= "</div>";
                }

            }
            else if($f_search_suggestions['totals'] > 0) {
                $f_html = "<div style='padding-top: 5px; padding-bottom: 5px;'>";
                    $f_html .= "<div style='max-width: 900px; margin: auto; padding: 0px 10px 0px 10px;'>";
                        $f_html .= $f_search_suggestions['c_html'] . $f_search_suggestions['s_html'];
                    $f_html .= "</div>";
                $f_html .= "</div>";

                $f_width = "500px";

                $f_totals = $f_totals + $f_search_suggestions['totals'];
            }
        }

        if($f_totals > 0) {
            $execute_functions[]['function'] = "js_fluid_process_search_suggestions";
            end($execute_functions);
            $execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("html" => base64_encode($f_html), "total" => $f_totals, "keywords" => base64_encode($f_data->f_search_input), "time" => $f_time, "width" => $f_width)));
        }
        else {
            $execute_functions[]['function'] = "js_fluid_process_search_suggestions_force_close";
        }

        return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
    }
    catch (Exception $err) {
        return $err;
    }
}

function php_highlight_words($f_keywords, $f_result) {
    $w1 = preg_replace("/\S*(". $f_keywords . ")\S*/i", "<fluid_search_tag>$1<fluid_search_tag>", $f_result);
    $w_explode = explode("<fluid_search_tag>", $w1);

    if(isset($w_explode[1])) {
        return str_replace($w_explode[1], "<b>" . $w_explode[1] . "</b>", $f_result);
    }
    else {
        return NULL;
    }
}

function php_encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

// Scans search logs, categories and manufacturers.
function php_search_suggestions_and_categories($f_data = NULL) {
    try {
        $fluid = new Fluid();

        $fluid->php_db_begin();

        $f_keywords = $fluid->php_limit_chars(trim($f_data->f_search_input));

        if($f_data->f_mobile == TRUE) {
            $f_max = 3;
        }
        else {
            $f_max = 6;
        }

        $fluid->php_db_query("SELECT * FROM " . TABLE_CATEGORIES . " WHERE c_enable = '1' AND c_name LIKE '%" . $fluid->php_escape_string($f_keywords) . "%' ORDER BY c_search_weight DESC LIMIT 0, " . $f_max);

        $c_html = NULL;
        $c_total = 0;
        if(isset($fluid->db_array)) {
            $f_style = NULL;

            if($f_data->f_mobile == TRUE) {
                $f_style = " margin-top: 5px; padding-top: 10px; border-top: 1px solid #cccccc;";
            }

            $c_html .= "<div style='display: table; margin-left: auto; margin-right: auto; width: 95%;" . $f_style . "'>";
                $c_html .= "<div style='display: table-cell; width: 80px;'>Categories</div>";

                $c_html .= "<div style='display: table-cell;'>";

                foreach($fluid->db_array as $f_result) {
                    $f_name = php_highlight_words($f_keywords, $f_result['c_name']);

                    if(isset($f_name)) {
                        $c_html .= "<div style='padding: 5px 5px 0px 15px;'><a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_LISTING_REWRITE . "/" . $f_result['c_id'] . "/" . $fluid->php_clean_string($f_result['c_name']) . "\" onClick='js_loading_start();' style='color: black'>" . $f_name . "</a></div>";

                        $c_total++;
                    }
                }

                $c_html .= "</div>";
            $c_html .= "</div>"; // row.

        }

        $fluid->php_db_query("SELECT DISTINCT(lv_search), lv_hits, lv_group FROM " . TABLE_LIVE_SEARCH_CACHE . " WHERE lv_search LIKE '%" . $fluid->php_escape_string($f_keywords) . "%' ORDER BY lv_hits DESC, lv_group DESC");

        $s_html = NULL;
        $s_total = NULL;
        if(isset($fluid->db_array)) {
            $f_style = NULL;

            if($c_total > 0 || $f_data->f_mobile == TRUE) {
                if($f_data->f_mobile == TRUE) {
                    $f_style = " margin-top: 20px; padding-top: 10px; border-top: 1px solid #cccccc; margin-bottom: 5px;'";
                }
                else if($c_total > 0) {
                    $f_style = " margin-top: 25px; padding-top: 15px; padding-bottom: 15px; border-top: 1px solid #cccccc;'";
                }
            }

            $s_html .= "<div style='display: table; width: 95%; margin-left: auto; margin-right: auto;" . $f_style . "'>";
                $s_html .= "<div style='display: table-cell; width: 80px;'>Suggestions</div>";

                $s_html .= "<div style='display: table-cell;'>";

                foreach($fluid->db_array as $f_array_data) {
                    $f_name = php_highlight_words($f_keywords, $f_array_data['lv_search']);

                    if(isset($f_name)) {
                        $onClick = "js_redirect_url({url:Base64.encode(\"" .$_SESSION['fluid_uri'] . FLUID_SEARCH_LISTING_REWRITE . "?f_search=" . php_encodeURIComponent($f_array_data['lv_search']) . "\")});";

                        $s_html .= "<div style='padding: 5px 5px 0px 15px;'><a onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='" . $onClick . "' style='color: black'>" . $f_name . "</a></div>";

                        $s_total++;
                    }

                    if($s_total == $f_max) {
                        break;
                    }
                }

                $s_html .= "</div>";
            $s_html .= "</div>"; // row.
        }

        /*
        $fluid->php_db_query("SELECT DISTINCT(l_query), COUNT(*) AS f_count FROM " . TABLE_LOGS . " WHERE l_type = 'search' AND l_query LIKE '%" . $fluid->php_escape_string($f_keywords) . "%' GROUP BY l_query ORDER BY f_count DESC LIMIT 0, 6");

        $s_html = NULL;
        $s_total = NULL;
        if(isset($fluid->db_array)) {
            $f_array = NULL;
            foreach($fluid->db_array as $f_result) {
                $tmp_obj = (object) Array('f_search_input' => trim($f_result['l_query']));

                $f_count_result = php_search_suggestion_process($tmp_obj, TRUE);
                //$f_count_result = 1;

                if($f_count_result > 0) {
                    $f_array[] = Array("count" => $f_count_result, "f_group" => $f_result['f_count'], "query" => trim($f_result['l_query']));
                }
            }

            $f_style = NULL;

            if(isset($f_array)) {
                usort($f_array, function($a, $b) { return $b['count'] <=> $a['count']; });

                $f_style = NULL;

                if($c_total > 0) {
                    $f_style = " margin-top: 25px; padding-top: 25px; border-top: 1px solid #cccccc; width: 95%;'";
                }

                $s_html .= "<div style='display: table;" . $f_style . "'>";
                    $s_html .= "<div style='display: table-cell; width: 80px;'>Suggestions</div>";

                    $s_html .= "<div style='display: table-cell;'>";

                    foreach($f_array as $f_key => $f_array_data) {
                        $f_name = php_highlight_words($f_keywords, $f_array_data['query']);

                        if(isset($f_name)) {
                            $onClick = "js_redirect_url({url:Base64.encode(\"" .$_SESSION['fluid_uri'] . FLUID_SEARCH_LISTING_REWRITE . "?f_search=" . php_encodeURIComponent($f_array_data['query']) . "\")});";

                            $s_html .= "<div style='padding: 5px 5px 0px 15px;'><a onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='" . $onClick . "' style='color: black'>" . $f_name . "</a></div>";

                            $s_total++;
                        }

                        if($s_total == FLUID_LISTING_MAX_SEARCH_SUGGESTIONS) {
                            break;
                        }
                    }

                    $s_html .= "</div>";
                $s_html .= "</div>"; // row.
            }
        }
        */

        $fluid->php_db_commit();

        return Array("c_html" => $c_html, "s_html" => $s_html, "totals" => $c_total + $s_total, "c_total" => $c_total, "s_total" => $s_total);
    }
    catch (Exception $err) {
        return $err;
    }
}

// Scans product database.
function php_search_suggestion_process($f_data = NULL, $f_do_count = FALSE) {
    try {
        $fluid = new Fluid();

        $fluid->php_db_begin();

        $query = NULL;

        $query = $fluid->php_limit_chars(trim($f_data->f_search_input));

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
        $keywords = $fluid->php_filter_search_keys($query, FALSE);
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

            //if(strlen($escQuery) > 6)
                $titleSQL[] = "if (p_name LIKE '%".$escQuery."%',{$scoreFullTitle},0)";

            $sumSQL[] = "if (m_name LIKE '%".$escQuery."%',{$scoreFullSummary},0)";
            $keywordsSQL[] = "if (p_keywords LIKE '%".$escQuery."%',{$scoreFullTitleKeyword},0)";
            $stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";
        }

        if(strlen($m_keywords) > 0) {
            $urlSQL[] = "if (c_keywords LIKE'%".$m_keywords."%',{$score9},0)";
            $docSQL[] = "if (c_name LIKE '%".$m_keywords."%',{$scoreFullDocument},0)";
        }

        // Matching Keywords
		$i = 0;
		foreach($keywords as $key) {
			//if(strlen($key) > 2) {
				$sumSQL[] = "if (m_name LIKE '%" . $fluid->php_escape_string($key) . "%',{$scoreSummaryKeyword} + (c_search_weight / 2),0)";
				if($i == 0) {
					$c_name_score = $score3;
                }
				else {
					$c_name_score = $score2;
                }

				$docSQL[] = "if (c_name LIKE '%". $fluid->php_escape_string($key)."%',{$c_name_score} + (c_search_weight / 2),0)";
				if($i == 0) {
					$c_keywords_score = $score3;
                }
				else if($i == 1) {
					$c_keywords_score = $score2;
                }
				else if($i == 2) {
					$c_keywords_score = $score2;
                }
				else {
					$c_keywords_score = $score1;
                }

				$urlSQL[] = "if (c_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$keywordsSQL[] = "if (p_keywords LIKE '%". $fluid->php_escape_string($key)."%',{$c_keywords_score} + (c_search_weight / 2),0)";
				$stockSQL[] = "if (p_stock > 0,{$scoreStock},0)";

				$i++;
			//}
		}

		// Just incase it is empty, then add 0.
		if(empty($upcSQL)) {
			$upcSQL[] = 0;
        }
		if(empty($titleSQL)) {
			$titleSQL[] = 0;
        }
		if(empty($sumSQL)) {
			$sumSQL[] = 0;
        }
		if(empty($docSQL)) {
			$docSQL[] = 0;
        }
		if(empty($urlSQL)) {
			$urlSQL[] = 0;
        }
		if(empty($tagSQL)) {
			$tagSQL[] = 0;
        }
		if(empty($keywordsSQL)) {
			$keywordsSQL[] = 0;
        }
		if(empty($stockSQL)) {
			$stockSQL[] = 0;
        }

        $item_page = 0;
        $item_start = 0;

        // Set up the sort order.
        $sort_by = "relevance DESC, c_search_weight DESC";

        if($f_data->f_mobile == TRUE) {
            $f_max = 2;
        }
        else {
            $f_max = FLUID_LISTING_MAX_SEARCH_SUGGESTIONS;
        }

        $order_by = "ORDER BY " . $sort_by . " LIMIT " . $item_start . "," . $f_max;

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

        // --> Only show products that have stock or a arrival date or discount date ending in the future.
        $query_search_stock = " p.p_enable > '0' AND c.c_enable = 1";

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
        if($f_do_count == TRUE) {
            $fluid->php_db_query("SELECT IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, " . $query_search . " GROUP BY relevance, p_zero_status_tmp HAVING relevance > 0 AND p_zero_status_tmp > 0 LIMIT 0, 1");

            $fluid->php_db_commit();

            if(isset($fluid->db_array)) {
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            $fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, " . $query_search . " GROUP BY relevance, p_zero_status_tmp HAVING relevance > 0 AND p_zero_status_tmp > 0");
        }
        */

        $fluid->php_db_query("SELECT COUNT(p.p_id) AS total, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, " . $query_search . " GROUP BY relevance, p_zero_status_tmp HAVING relevance > 0 AND p_zero_status_tmp > 0");

        $html = NULL;

        $last_id = 0;

        $bool_found_items = FALSE;
        $i_item_count = 0;
        $total_items = 0;
        $f_total_bundles = 0;
        if(isset($fluid->db_array)) {
            $sort_col = array();
            foreach ($fluid->db_array as $key=> $row) {
                $sort_col[$key] = $row['relevance'];
            }

            array_multisort($sort_col, SORT_DESC, $fluid->db_array);

            $highest_relevance = $fluid->db_array[0]['relevance'] / FLUID_SEARCH_RELEVANCE;

            foreach($fluid->db_array as $f_key => $data) {
                if($data['relevance'] <= $highest_relevance) {
                    unset($fluid->db_array[$f_key]);
                }
            }

            $total_items = 0;
            if(isset($fluid->db_array)) {
                foreach($fluid->db_array as $f_tmp_array) {
                    $total_items = $total_items + $f_tmp_array['total'];
                }
            }
            else {
                $total_items = $i_item_count;
            }

            if($f_do_count == TRUE) {
                $fluid->php_db_commit();

                return $total_items;
            }
            else {
                $fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status > 0) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp, IF(p.p_price_discount IS NULL OR p.p_price_discount < 1, p.p_price, p.p_price_discount) AS fluid_price_discount, IF(p.p_stock < 1,0,1) AS fluid_stock, IF(p.p_price_discount IS NULL,0,1) - (IFNULL(Sum(p.p_price_discount),0) / IFNULL(Sum(p.p_price),0)) AS fluid_discount_percent, " . $query_search . " GROUP BY p.p_id HAVING relevance >= " . $highest_relevance . $query_search_zero_stock . " " . $order_by);

                $f_items = NULL;
                $f_bundle_select = NULL;
                $f_bundle_count = 0;

                if(isset($fluid->db_array)) {
                    foreach($fluid->db_array as $data) {
                        if($data['p_stock'] < 1) {
                            $data['p_enable'] = $data['p_zero_status'];
                        }

                        // Scan the $data item, look for FORMULA_OPTION_8, then generate a promotional item as required.
                        if($data['p_formula_status'] == 1 && ($data['p_formula_operation'] == FORMULA_OPTION_8 || $data['p_formula_operation'] == FORMULA_OPTION_9)&& ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) )) {

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
                        $fluid->php_db_query("SELECT p.*, m.*, c.*, IF((p.p_stock < 1 AND p.p_zero_status != 1) OR (p.p_stock > 0), 1, 0) AS p_zero_status_tmp FROM products p INNER JOIN manufacturers m on p_mfgid = m_id INNER JOIN categories c on p.p_catid = c_id
                        WHERE p.p_enable > '0' AND c.c_enable = 1 AND p_mfgcode IN (" . $f_bundle_select . ") HAVING p_zero_status_tmp > 0 ORDER BY p_mfgcode ASC");

                        if(isset($fluid->db_array)) {
                            foreach($fluid->db_array as $f_b_items) {
                                $f_bundle_items[$f_b_items['p_mfgcode']] = $f_b_items;
                            }
                        }
                    }

                    $i_counter = 1;
                    $f_counter = 0;

                    $html .= "<div class='row fluid-dropdown-search-suggestions-div'>";
                    foreach($f_items as $f_data) {
                        if($f_counter == $f_max) {
                            break;
                        }

                        if($i_counter > 2) {
                            $i_counter = 1;
                            $html .= "</div><div class='row fluid-dropdown-search-suggestions-div'>";
                        }

                        $last_id = $data['p_sortorder'];
                        $bool_found_items = TRUE;
                        $i_item_count++;

                        $f_data['data']['position'] = $i_item_count + 1;

                        $html .= "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>";
                            $html .= php_html_item_suggestion_card($f_data['data']);
                        $html .= "</div>";

                        $i_counter++;
                        $f_counter++;

                        if(isset($f_data['bundle_items'])) {
                            foreach($f_data['bundle_items'] as $fb_item_key) {
                                if(isset($f_bundle_items[$fb_item_key])) {
                                    if($f_counter == $f_max - 1) {
                                        break;
                                    }

                                    $fb_item = NULL;
                                    $fb_item = $f_bundle_items[$fb_item_key];

                                    $f_org_tmp = $f_data['data'];

                                    // FORMULA_OPTION_9 makes the original item have no discount.
                                    if($f_org_tmp['p_formula_operation'] == FORMULA_OPTION_9) {
                                        $f_org_tmp['p_price_discount'] = "";
                                        $f_org_tmp['p_discount_date_end'] = "";
                                        $f_org_tmp['p_discount_date_start'] = "";
                                    }

                                    $fb_item['p_bundle_item'] = $f_org_tmp;

                                    // --> Lets see if we need to flip some data around.
                                    if($f_org_tmp['p_formula_flip'] == 1) {
                                        if($fb_item['m_id'] != $f_org_tmp['m_id'])
                                            $fb_item['p_name'] = $f_org_tmp['m_name'] . " " . $f_org_tmp['p_name'] . " w/" . $fb_item['m_name'] . " " . $fb_item['p_name'];
                                        else
                                            $fb_item['p_name'] = $f_org_tmp['p_name'] . " w/" . $fb_item['p_name'];
                                    }
                                    else {
                                        if($fb_item['m_id'] != $f_org_tmp['m_id'])
                                            $fb_item['p_name'] .= " w/" . $f_org_tmp['m_name'] . " " . $f_org_tmp['p_name'];
                                        else
                                            $fb_item['p_name'] .= " w/" . $f_org_tmp['p_name'];
                                    }

                                    $fb_item['p_images'] = base64_encode(json_encode((object) array_merge((array) json_decode(base64_decode($fb_item['p_images'])), (array) json_decode(base64_decode($f_org_tmp['p_images'])))));

                                    if($i_counter > 2) {
                                        $i_counter = 1;
                                        $html .= "</div><div class='row fluid-dropdown-search-suggestions-div'>";
                                    }

                                    $html .= "<div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>";
                                        $html .= php_html_item_suggestion_card($fb_item);
                                    $html .= "</div>";

                                    $i_counter++;
                                    $f_counter++;
                                    $f_total_bundles++;
                                }
                            }
                        }
                    } // foreach $f_data
                    $html .= "</div>";

                    if($total_items > 0) {
                        $html = "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><div style='display: table-cell; width: 100%; padding-top: 5px; padding-bottom: 10px;'>Possible matches</div></div>" . $html;
                    }
                }
                else {
                    $html .= "<div class='row row-product-not-found-container-search'>";
                        $html .= "<p class='product-not-found-text-search'>No products found.</p>";
                    $html .= "</div>";

                    $total_items = 0;
                }
            }
        }
        else {
            // Nothing found.
            $html = "<div>Nothing found.</div>";
        }

        $fluid->php_db_commit();

        if($f_do_count == TRUE) {
            return 0;
        }
        else {
            return Array("html" => $html, "total_items" => $total_items);
        }
    }
    catch (Exception $err) {
        return $err;
    }
}

function php_html_item_suggestion_card($data) {
    try {
        $fluid = new Fluid();

        $f_html = "<div style='margin: auto; max-width: 200px;'>";
            $p_images = $fluid->php_process_images($data['p_images']);

            if(empty($data['p_mfgcode'])) {
                $ft_mfgcode = $data['p_id'];
            }
            else {
                $ft_mfgcode = $data['p_mfgcode'];
            }

            if(empty($data['p_name'])) {
                $ft_name = $data['p_id'];
            }
            else {
                $ft_name = $data['m_name'] . " " . $data['p_name'];
            }

            $f_p_link = NULL;
            if(isset($data['p_bundle_item'])) {
                $f_p_link = "_" . $data['p_bundle_item']['p_id'];
            }

            // Google tracking data.
            $f_gs_data = $data;
    		$f_gs_data['url'] = $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name);
    		$f_gs_data = base64_encode(json_encode($f_gs_data));

    		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
    			$f_gs_on_click = "onClick='js_gs_product_click(\"" . $f_gs_data . "\"); return !ga.loaded;'";
            }
    		else {
    			$f_gs_on_click = "onClick='js_gs_product_click(\"" . $f_gs_data . "\");'";
            }

            // Image.
            $f_html .= "<div name='f-image-container-search-suggestions'>";
                $image_html_link = "<a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">";

                $f_img_length = "80";
                $f_img_height = "80";
                $f_img_style = NULL;
                $image_bundle_html = NULL;

                // We have a bundle, lets generate the second image.
                if(isset($data['p_bundle_item'])) {
                    $f_img_length = "50";
                    $f_img_height = "50";
                    $f_img_style = " style='display: inline-block;'";

                    $p_images_bundle = $fluid->php_process_images($data['p_bundle_item']['p_images']);

                    $f_img_name_bundle = str_replace(" ", "_", $data['p_bundle_item']['m_name'] . "_" . $data['p_bundle_item']['p_name'] . "_" . $data['p_bundle_item']['p_mfgcode']);
                    $f_img_name_bundle = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name_bundle);

                    $width_height_l_bundle = $fluid->php_process_image_resize($p_images_bundle[0], $f_img_length, $f_img_height, $f_img_name_bundle);

                    $image_bundle_html = "<img " .  $f_img_style . " src='" . $_SESSION['fluid_uri'] . $width_height_l_bundle['image'] . "' alt=\"" . str_replace('"', '', $data['p_bundle_item']['m_name'] . " " . $data['p_bundle_item']['p_name']) . "\"/></img>";
                }

                $f_img_name = str_replace(" ", "_", $data['m_name'] . "_" . $data['p_name'] . "_" . $data['p_mfgcode']);
                $f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

                $width_height_l = $fluid->php_process_image_resize($p_images[0], $f_img_length, $f_img_height, $f_img_name);

                $image_html = "<img " . $f_img_style . " src='" . $_SESSION['fluid_uri'] . $width_height_l['image'] . "' alt=\"" . str_replace('"', '', $data['m_name'] . " " . $data['p_name']) . "\"/></img>";

                // --> Merge the bundle image if required.
                if(isset($image_bundle_html)) {
                    if($data['p_bundle_item']['p_formula_flip'] == 1) {
                        $image_html = $image_html_link . $image_bundle_html . $image_html;
                    }
                    else {
                        $image_html = $image_html_link . $image_html . $image_bundle_html;
                    }
                }
                else {
                    $image_html = $image_html_link . $image_html . $image_bundle_html;
                }

                $image_html .= "</a>";
            $f_html .= $image_html . "</div>";

            // Name.
            $f_html .= "<div name='f-item-name-search-suggestions'>";

                if(empty($data['p_mfgcode'])) {
                    $ft_mfgcode = $data['p_id'];
                }
                else {
                    $ft_mfgcode = $data['p_mfgcode'];
                }

                if(empty($data['p_name'])) {
                    $ft_name = $data['p_id'];
                }
                else if(empty($data['p_mfg_number']) || $data['p_namenum'] == FALSE) {
                    $ft_name = utf8_encode($data['m_name'] . " " . $data['p_name']);
                }
                else if(empty($data['p_mfg_number']) && $data['p_namenum'] == TRUE) {
                    $ft_name = utf8_encode($data['m_name'] . " " . $data['p_name']);
                }
                else {
                    $ft_name = utf8_encode($data['m_name'] . " " . $data['p_mfg_number'] . " " . $data['p_name']);
                }

                // --> Lets see if we need to flip some data around.
                if(isset($data['p_bundle_item'])) {
                    if($data['p_bundle_item']['p_formula_flip'] == 1) {
                        if(empty($data['p_mfgcode'])) {
                            $ft_mfgcode = $data['p_bundle_item']['p_id'];
                        }
                        else {
                            $ft_mfgcode = $data['p_bundle_item']['p_mfgcode'];
                        }

                        $ft_name = utf8_encode($data['p_bundle_item']['m_name'] . " " . $data['p_name']);
                    }
                }

                $product_link_short = NULL;
                $f_product_link_vh_normal = NULL;

                // --> Make the name shorter?
                /*
                if(strlen($ft_name) > 40) {
                    $ft_name_short = substr($ft_name, 0, 40) . "...";
                }
                else {
                    $ft_name_short = $ft_name;
                }
                */

                $ft_name_short = $ft_name;

                $product_link_short = "<a class='f-product-link-short-vh-search' style='vertical-align: middle;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">" . $ft_name_short . "</a>";

                //$product_link = "<a onmouseover=\"JavaScript:this.style.cursor='pointer';\" href=\"" . $_SESSION['fluid_uri'] . FLUID_ITEM_VIEW_REWRITE . "/" . $data['p_id'] . $f_p_link . "/" . $fluid->php_clean_string($ft_mfgcode) . "/" . $fluid->php_clean_string($ft_name) . "\" " . $f_gs_on_click . ">" . $ft_name . "</a>";

                //$f_html .= $product_link . $product_link_short;
                $f_html .= $product_link_short;
            $f_html .= "</div>";

            // Price.
            $f_html .= "<div>";
                $f_html .= php_process_price($data);
            $f_html .= "</div>";

        $f_html .= "</div>";

        return $f_html;
    }
    catch (Exception $err) {
        return $err;
    }
}

function php_process_price($data = NULL) {
    try {
        $fluid = new Fluid();

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
            if($data['p_bundle_item']['p_price_discount'] && ((strtotime($data['p_bundle_item']['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_bundle_item']['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_bundle_item']['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_bundle_item']['p_discount_date_end'] == NULL) || ($data['p_bundle_item']['p_discount_date_start'] == NULL && $data['p_bundle_item']['p_discount_date_end'] == NULL))) {
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


            $return_tmp .= "<div>";
                $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($f_price_org, $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
            $return_tmp .= "</div>";

        }
        else if($data['p_formula_status'] == 1 && ($data['p_formula_operation'] != FORMULA_OPTION_8 && $data['p_formula_operation'] != FORMULA_OPTION_9) && ((strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_formula_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_formula_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_formula_discount_date_end'] == NULL) || ($data['p_formula_discount_date_start'] == NULL && $data['p_formula_discount_date_end'] == NULL) )) {
                $f_value_asterik = NULL;

                if(strlen($data['p_formula_math']) > 0) {
                    if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
                        $p_price = $data['p_price_discount'];
                    }
                    else {
                        $p_price = $data['p_price'];
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

                    if($f_value <= 0) {
                        $f_value = $p_price;
                    }

                    if($data['p_formula_message_display'] == 1 && $data['p_formula_message']) {
                        $f_value_asterik = "<div style='display: inline-block; font-weight: 600; padding-left: 3px;'>*</div>";
                    }

                    $return_tmp .= "<div>";
                        $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($f_value, 2, '.', ',') . $f_value_asterik . "</div>";
                    $return_tmp .= "</div>";
                }
                else if($data['p_price']) {
                    if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
                        $return_tmp .= "<div>";
                            $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
                        $return_tmp .= "</div>";
                    }
                    else {
                        $return_tmp .= "<div>";
                            $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
                        $return_tmp .= "</div>";
                    }
                }
        }
        else if($data['p_price_discount'] && ((strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && strtotime($data['p_discount_date_end']) > strtotime(date('Y-m-d H:i:s'))) || (strtotime($data['p_discount_date_start']) < strtotime(date('Y-m-d H:i:s')) && $data['p_discount_date_end'] == NULL) || ($data['p_discount_date_start'] == NULL && $data['p_discount_date_end'] == NULL) )) {
            if($data['p_price']) {
                $return_tmp .= "<div>";
                    $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
                $return_tmp .= "</div>";
            }
        }
        else {
            if($data['p_price']) {
                // --> If we are in vertical mode, made some adjustments to make the cards the same height.
                $return_tmp .= "<div>";
                    $return_tmp .= "<div class='price-final-value-listing-search' style='display: inline-block; text-align: center;'>" . utf8_encode(HTML_CURRENCY) . number_format($fluid->php_math_price($data['p_price'], $data['p_price_discount'], $data['p_discount_date_end'], $data['p_discount_date_start']), 2, '.', ',') . "</div>";
                $return_tmp .= "</div>";
            }
        }

        return $return_tmp;
    }
    catch (Exception $err) {
        return $err;
    }
}
?>
