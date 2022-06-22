<?php
// fluid.barcode.php
// Michael Rajotte - 2018 Mars

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

// Loads the barcode modal. Allows the user to decide if they want to input qty's onto the labels or not.
function php_barcode_modal($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$fluid = new Fluid();

		$f_selection = NULL;
		$i_total = 0;

		if(empty($f_data->f_selection))
			throw new Exception("Error. No items selected.");
		else
			$f_selection = json_decode(base64_decode($f_data->f_selection));

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Barcode Label Generating</div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>
							<div style='margin-left:10px; margin-right: 10px;'>
								<div class='alert alert-danger' role='alert' style='padding-bottom: 5px;'>
									<div style='font-weight: 600;'>Instructions:</div>
									<div style='padding-bottom: 10px;'>Leave the QTY input column empty if you wish to not print any quantities onto the labels. Entering a number into the QTY input will add that quantity number onto the label. Increase the COPIES field if you want extra copies generated.</div>
								</div>
							</div>

							<div style='margin-left:10px; margin-right: 10px; padding-top: 10px;'>";


							if(isset($f_selection)) {
								$fluid->php_db_begin();

								$where = "WHERE p_id IN (";
								$i = 0;
								foreach($f_selection as $product) {
									if($i != 0)
										$where .= ", ";

									$where .= $fluid->php_escape_string($product->p_id);

									$i++;
								}
								$where .= ")";

								$fluid->php_db_query("SELECT p.p_id, p.p_name, p.p_images, p.p_mfgcode, p.p_stock, m.m_name FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m on p_mfgid = m_id " . $where);

								$fluid->php_db_commit();

								if(isset($fluid->db_array)) {
									$f_html = "<table class='table table-condensed table-striped'>";

									$f_html .= "<thead>";
										$f_html .= "<tr>";
											$f_html .= "<td></td><td style='text-align: center;'>Item</td><td style='text-align: center;'>Code</td><td>Description</td><td style='text-align: center;'><div class='dropdown'><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Qty <span class=\"caret\"></span></a><ul class=\"dropdown-menu\" aria-labelledby=\"dropdownBarCodeQty\"><li><a onClick='js_fluid_barcode_qty_match_all();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Match stock of item</a></li><li><a onClick='js_fluid_barcode_qty_match_instock();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Match in stock only</a></li><li><a onClick='js_fluid_barcode_qty_match_reset_all();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Reset all to default</a></li></ul></div></td><td style='text-align: center;'><div class='dropdown'><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Copies <span class=\"caret\"></span></a><ul class=\"dropdown-menu\" aria-labelledby=\"dropdownBarCodeCopies\"><li><a onClick='js_fluid_barcode_stock_match_all();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Match stock of item</a></li><li><a onClick='js_fluid_barcode_stock_match_instock();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Match in stock only</a></li><li><a onClick='js_fluid_barcode_stock_match_reset_all();' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Reset all to default</a></li></ul></div></td><td>Serial # Edit</td>";
										$f_html .= "</tr>";

									$f_html .= "</thead>";

									$f_html .= "<tbody id='f-barcode-table-body'>";
									
									$f_stock_data = NULL;
									$i = 0;
									foreach($fluid->db_array as $value) {
										// Process the image.
										$p_images = $fluid->php_process_images($value['p_images']);
										$f_img_name = str_replace(" ", "_", $value['m_name'] . "_" . $value['p_name'] . "_" . $value['p_mfgcode']);
										$f_img_name = preg_replace('/[^A-Za-z0-9\-_]/', '', $f_img_name);

										$width_height = $fluid->php_process_image_resize($p_images[0], "60", "60", $f_img_name);

										$f_html .= "<tr>";
											// Used for keeping track of items.
											$f_html .= "<td style='display: none;'><input type='text' name='f-hidden-barcode-code' id='f-hidden-barcode-code_" . $i . "' value='" . $value['p_mfgcode'] . "'/><input type='text' name='f-hidden-barcode-id' id='f-hidden-barcode-id_" . $i . "' value='" . $value['p_id'] . "'/><input type='text' name='f-hidden-barcode-counter' id='f-hidden-barcode-counter_" . $i . "' value='" . $i . "'/></td>";

											// Buttons for editing.
											$f_html .= "<td style='vertical-align: middle; min-width: 94px;'><button class='btn btn-danger' onClick='js_fluid_barcode_remove(this);'><span class='glyphicon glyphicon-trash'></span></button> <button class='btn btn-success' id='f-barcode-copy-btn_" . $i . "' onClick='js_fluid_barcode_copy(this);'><span class='glyphicon glyphicon-plus'></span></button></td>";

											$f_html .= "<td style='vertical-align: middle; text-align: center;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image']  . "' style='padding: 5px; max-width: 120px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt=alt=\"" . str_replace('"', '', $value['m_name'] . " " . $value['p_name']) . "\"></img></td>";

											$f_html .= "<td style='vertical-align: middle; text-align: center;'>" . $value['p_mfgcode'] . "</td>";
											// <div style='font-size: 10px;'>Body # - 1234567890</div><input style='display: none;' type='text' value='0123456789'/></div><div style='font-size: 10px;'>Lens # - 0987654321</div><input style='display: none;' type='text' value='0987654321'/></div>
											$f_html .= "<td style='vertical-align: middle;'>" . $value['p_name'] . "<div id='f-barcode-serials_" . $i . "' style='font-size: 10px;'></div></td>";
											$f_html .= "<td style='vertical-align: middle; text-align: center;'><input style='max-width: 80px; padding-left: 10px;' type='number' min='0' step='1' value='' name='f-barcode-qty' id='f-barcode-qty_" . $i . "'/></td>";
											$f_html .= "<td style='vertical-align: middle; text-align: center;'><input style='max-width: 80px; padding-left: 10px;' type='number' min='1' step='1' value='1' name='f-barcode-copies' id='f-barcode-copies_" . $i . "'/></td>";

											// Buttons for serial number editing.
											$f_html .= "<td style='vertical-align: middle; min-width: 94px;'><button class='btn btn-info' onClick='js_fluid_barcode_serial_add(this);'><span class='glyphicon glyphicon-plus'></span></button> <button class='btn btn-info' onClick='js_fluid_barcode_serial_editor(this);'><span class='glyphicon glyphicon-edit'></span></button></td>";
										$f_html .= "</tr>";
										
										$f_stock_data[$i] = $value['p_stock'];
										
										$i++;
									}

									$i_total = $i;

									$f_html .= "</tbody>";
									$f_html .= "</table>";

									$modal .= $f_html;
								}
								else {
									throw new Exception("Error. No items selected.");
								}
							}
							else {
								throw new Exception("Error. No items selected.");
							}

							$modal .= "</div>
						</div>
					</div>

				</div>

				<div class='modal-footer'>
					<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>

					<div style='float:right;'><button onClick='js_fluid_barcode();' type='button' class='btn btn-success'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Generate Barcodes</button></div>
				</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_fluid_barcode_set_counter";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($i_total));

		$execute_functions[]['function'] = "js_fluid_barcode_stock_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_stock_data));
		
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

function php_fluid_barcode() {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($f_data)) {
			$fluid = new Fluid();
			$fluid->php_db_begin();

			$where = "WHERE p.p_id IN (";
			$i = 0;

			$f_data_array = NULL;
			foreach($f_data as $item) {
				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($item->p_id);

				$i++;
			}
			$where .= ")";

			$fluid->php_db_query("SELECT p.*, m.*, c.* FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_MANUFACTURERS . " m ON p_mfgid = m_id INNER JOIN " . TABLE_CATEGORIES . " c on p.p_catid = c_id " . $where);

			$fluid->php_db_commit();

			$f_barcodes = NULL;
			$f_barcodes_rdy = NULL;

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $f_item) {
					$f_barcodes[$f_item['p_id']] = $f_item;
				}
			}
			

			$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
			//$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
			//$output = $generator->getBarcode('081231723897', $generator::TYPE_CODE_128, 2, 100);

			foreach($f_data as $f_data_item) {
				if(isset($f_barcodes[$f_data_item->p_id])) {
					if(strlen($f_barcodes[$f_data_item->p_id]['p_name']) > 20) {
						$fontsize = "9";
						$paddingtop = "3";
						$paddingtop2 = "0";
						$paddingtop3 = "0";
						$paddingTopSerial = "10";
					}
					else {
						$fontsize = "12";
						$paddingtop = "3";
						$paddingtop2 = "0";
						$paddingtop3 = "0";
						$paddingTopSerial = "10";
					}


					$f_code = "<table cellspacing='0' cellpadding='0' border='0' style='width: 100%; margin: 0px; padding: 0px;'>";
						$f_code .= "<tr>";
						$f_code .= "<td valign='middle' style='width:30%; max-height: 30px; padding-top:0px; padding-right:0px; padding-bottom: 0px;' valign=middle>";
							// --> Need to fix this, if no image is found for a manufacturer, then the No Image logo needs to be sized correctly to fit on the printed pdf page.
							$m_images = $fluid->php_process_images($f_barcodes[$f_data_item->p_id]['m_images']);
							$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "65", "30");

							if($m_img_width_height['height'] > 30)
								$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "60", "30");

							if($m_img_width_height['height'] > 30)
								$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "50", "30");

							if($m_img_width_height['height'] > 30)
								$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "40", "30");

							if($m_img_width_height['height'] > 30)
								$m_img_width_height = $fluid->php_process_image_resize($m_images[0], "30", "30");

							$path =  '../htdocs/' . $m_img_width_height['image'];
							
						    $type = pathinfo($path, PATHINFO_EXTENSION);
						    $data = file_get_contents($path);
						    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
								
							//$f_code .= "<img src='" . $_SESSION['fluid_uri'] . $m_img_width_height['image'] . "' align=left style='padding-bottom:0px; padding-right:0px;'>";
							$f_code .= "<img src='" . $base64 . "' align=left style='padding-bottom:0px; padding-right:0px;'>";

						$f_code .= "</td>";

						$f_code .= "<td valign='middle' style='width:70%; height: 30px; padding-left:3px; padding-right:6px; padding-bottom: 0px; padding-top:0px; font-size:" . $fontsize . "px; font-weight: bold;'>";
							if(strlen($f_barcodes[$f_data_item->p_id]['p_name']) > 64)
								$f_code .= substr($f_barcodes[$f_data_item->p_id]['p_name'], 0, 61) . "...";
							else
								$f_code .= $f_barcodes[$f_data_item->p_id]['p_name'];
						$f_code .= "</td>";
						$f_code .= "</tr>";

						$f_code .= "<tr>";
						$f_code .= "<td colspan='2' style='width:100%; padding-top:" . $paddingtop . "px; font-size:" . $fontsize . "px;'>";
							$f_code .= "<div style='text-align: center; display: inline-block;'>";
							//$f_code .= $generator->getBarcode($f_barcodes[$f_data_item->p_id]['p_mfgcode'], $generator::TYPE_CODE_128, 1, 50);
							$f_code .= '<img style="width: 140px; height: 25px;" src="data:image/png;base64,' . base64_encode($generator->getBarcode($f_barcodes[$f_data_item->p_id]['p_mfgcode'], $generator::TYPE_CODE_128, 1, 25)) . '">';
							$f_code .= "</div>";
							$f_code .= "<div style='font-size: 14px; font-weight: 800; text-align: center;'>" . $f_barcodes[$f_data_item->p_id]['p_mfgcode'] . "</div>";


									if(isset($f_data_item->p_qty))
										if(is_numeric($f_data_item->p_qty) == TRUE)
											if($f_data_item->p_qty > 0)
												$f_code .= "<div style='font-size: 12px; font-weight: 800; text-align: left; margin: 0px; padding: 0px;'>Qty: " . $f_data_item->p_qty . "</div>";

						$f_code .= "</td>";
						$f_code .= "</tr>";

					$f_code .= "</table>";

					$f_barcodes_rdy[$f_data_item->x_sort]['code'] = $f_code;

					if(isset($f_data_item->s_names) && isset($f_data_item->s_serials)) {
						foreach($f_data_item->s_names as $s_key => $s_serials) {
							$s_html = NULL;

							if(strlen($f_data_item->s_serials) > 20) {
								$fontsize = "9";
								$paddingtop = "3";
								$paddingtop2 = "0";
								$paddingtop3 = "0";
								$paddingTopSerial = "10";
							}
							else {
								$fontsize = "12";
								$paddingtop = "3";
								$paddingtop2 = "0";
								$paddingtop3 = "0";
								$paddingTopSerial = "10";
							}

							$s_html = "<table cellspacing='0' cellpadding='0' border='0' style='width: 100%; margin: 0px; padding: 0px;'>";
								$s_html .= "<tr>";
								//$s_html .= "<td valign='middle' style='width:30%; max-height: 30px; padding-top:0px; padding-right:0px; padding-bottom: 0px;' valign=middle>";
								//$s_html .= "</td>";

								$s_html .= "<td valign='middle' style='text-align: center; width:100%; height: 30px; padding-left:3px; padding-right:6px; padding-bottom: 0px; padding-top:0px; font-size:" . $fontsize . "px; font-weight: bold;'>";
									if(strlen($s_serials) > 64)
										$s_html .= substr($s_serials, 0, 61) . "...";
									else
										$s_html .= $s_serials;
								$s_html .= "</td>";
								$s_html .= "</tr>";

								$s_html .= "<tr>";
								$s_html .= "<td colspan='2' style='width:100%; padding-top:" . $paddingtop . "px; font-size:" . $fontsize . "px;'>";
									$s_html .= "<div style='text-align: center; display: inline-block;'>";
									//$s_html .= $generator->getBarcode($f_barcodes[$f_data_item->p_id]['p_mfgcode'], $generator::TYPE_CODE_128, 1, 50);
									$s_html .= '<img style="width: 140px; height: 25px;" src="data:image/png;base64,' . base64_encode($generator->getBarcode($f_data_item->s_serials->{$s_key}, $generator::TYPE_CODE_128, 1, 25)) . '">';
									$s_html .= "</div>";
									$s_html .= "<div style='font-size: 14px; font-weight: 800; text-align: center;'>" . $f_data_item->s_serials->{$s_key} . "</div>";

								$s_html .= "</td>";
								$s_html .= "</tr>";

							$s_html .= "</table>";

							$f_barcodes_rdy[$f_data_item->x_sort]['serials'][] = $s_html;
						}
					}
				}
			}

			// **************************************** ----------------------- *************************************
			//													TODO
			//
			// Need to set the page size and page margins editable in the settings editor for custom sizes.
			//
			//
			// **************************************** ----------------------- *************************************
			//$pageSize = array(26, 54);
			$pageSize = array(25.4, 54.1);
			$pageMargins = array(2, 1, 2, 1);

			if(isset($f_barcodes_rdy)) {
				ob_start();
				$html2pdf = new HTML2PDF('L', $pageSize, 'en', true, 'UTF-8', $pageMargins);				
								
				$html2pdf->pdf->SetDisplayMode('fullpage');

				
				foreach($f_data as $item) {
					if(isset($f_barcodes_rdy[$item->x_sort])) {
						for($x = 1; $x <= $item->p_copies; $x++) {
							try {								
								$html2pdf->writeHTML("<page>" .  $f_barcodes_rdy[$item->x_sort]['code'] . "</page>");

								// --> Process the serial numbers.
								if(isset($f_barcodes_rdy[$item->x_sort]['serials']))
									foreach($f_barcodes_rdy[$item->x_sort]['serials'] as $s_serials)
										$html2pdf->writeHTML("<page>" .  $s_serials . "</page>");
							}
							catch(HTML2PDF_exception $e) {
								ob_end_flush();
								echo $e;
							}
						}
					}
				}

				$html2pdf->Output('barcode.pdf');

				ob_end_flush();
			}
			
			/*
			header("Content-Type: text/csv");
			header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
			header("Content-Transfer-Encoding: binary\n");
			header('Content-Disposition: attachment; filename="downloaded.csv"');
			php_fluid_output_csv($f_csv);
			*/

			//return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Error exporting data.");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_barcode_serial_modal() {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		if($f_data['s_mode'] == "create")
			$s_title = "creator";
		else
			$s_title = "editor";

		$f_html = NULL;

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Serial number " . $s_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div id='serial-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>";

							if($f_data['s_mode'] == "create") {
								$f_html = "
								<div class=\"alert alert-danger\" role=\"alert\" style='margin-left:10px; margin-right: 10px;'>

									<div style='font-weight: 600; padding-bottom: 5px;'>Enter a serial number:</div>
									<div>Scan the serial number on the box of the item or manually enter it yourself.</div>
								</div>

								<div style='margin-left:10px; margin-right: 10px; padding-top: 10px; padding-bottom: 30px;'>

									<div class='list-group'>

											<div class=\"input-group\" style='padding-top: 10px;'>
												<span class=\"input-group-addon\"><div style='width:120px !important;'>Name</div></span>
												<input id=\"f-barcode-name-input\" type=\"text\" class=\"form-control\" placeholder=\"name\">
											</div>

											<div class=\"input-group\" style='padding-top: 10px;'>
												<span class=\"input-group-addon\"><div style='width:120px !important;'>Serial number</div></span>
												<input id=\"f-barcode-serial-input\" type=\"text\" class=\"form-control\" placeholder=\"Serial number\">
											</div>
									</div>
								</div>";
							}
							else {
								$f_html = "
								<div class=\"alert alert-danger\" role=\"alert\" style='margin-left:10px; margin-right: 10px;'>

									<div style='font-weight: 600; padding-bottom: 5px;'>Serial number removal:</div>
									<div>Click on the red delete button to remove a serial number, then click save to update the changes.</div>
								</div>

								<div style='margin-left:10px; margin-right: 10px; padding-top: 10px; padding-bottom: 30px;'>";

								// --> 1. Have onClick on the red button to delete the dom, but store the key id into a fluid global variable. Have the fluid global variable wipe itself everytime this modal loads.

								if(isset($f_data['s_selection'])) {
									if(isset($f_data['s_selection']['s_serials'])) {
										foreach($f_data['s_selection']['s_serials'] as $s_key => $s_data) {
											$f_html .= "<div id='s_serial_div_" . $s_key . "' class='list-group'>";
												$f_html .= "<div class=\"input-group\" style='padding-top: 10px;'>";

													$f_html .= "<div style='display: table-cell; vertical-align: middle;'>";
														$f_html .= "<div style='display: inline-block; padding-right: 15px;'><button class='btn btn-danger btn-sm' onClick='js_html_remove_element(\"s_serial_div_" . $s_key . "\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></button></div>";
													$f_html .= "</div>"; // table-cell

													$f_html .= "<div style='display: table-cell; vertical-align: middle;'>";
														$f_html .= "<div>";
															if(isset($f_data['s_selection']['s_names'][$s_key]))
																$f_html .= "<div><input name='f-barcode-serial-edit-name' type=\"text\" style='margin-bottom: 5px;' class=\"form-control\" placeholder=\"Name\" value=\"" . $f_data['s_selection']['s_names'][$s_key] . "\" disabled></div>";

															$f_html .= "<div><input name='f-barcode-serial-edit-serial' type=\"text\" class=\"form-control\" placeholder=\"Serial number\" value=\"" . $s_data . "\" disabled></div>";
														$f_html .= "</div>";
													$f_html .= "</div>"; // table-cell

												$f_html .= "</div>";
											$f_html .= "</div>";
										}
									}
								}
								$f_html .= "</div>";
							}

							$modal .= $f_html;
						$modal .= "</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer'>";

			  $temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . base64_encode($f_data['s_id']) . "&f_tab=serial")));

			  $footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' onClick='js_modal_hide(\"#fluid-modal-overflow\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>";

			  if($f_data['s_mode'] == "create")
				$footer_save_html .= "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_fluid_barcode_serial_save(\"" . $f_data['s_id'] . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>";
 			  else
				$footer_save_html .= "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_fluid_barcode_serial_update(\"" . $f_data['s_id'] . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("parent" => base64_encode("fluid-modal-overflow"), "innerHTML" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-overflow"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}
?>
