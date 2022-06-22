<?php
// Michael Rajotte - 2012 / 2017 / 2018 / 2019
// canadapost.php
// Fluid required file for integration with the Canada Post api.

function php_canada_post_soap($a_data, $f_data, $signature = FALSE) {
	$wsdl = '../3rd-party-src/canadapost-api/wsdl/rating.wsdl';

	//$hostName = 'ct.soa-gw.canadapost.ca';
	$hostName = 'soa-gw.canadapost.ca';
	//https://soa-gw.canadapost.ca/rs/ship/price
	
	// SOAP URI
	$location = 'https://' . $hostName . '/rs/soap/rating/v3';

	// SSL Options
	$opts = array('ssl' =>
		array(
			'verify_peer'=> false,
			'cafile' => '../3rd-party-src/canadapost-api/cacert.pem',
			'CN_match' => $hostName
		)
	);

	$ctx = stream_context_create($opts);	
	$client = new SoapClient($wsdl,array('location' => $location, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'stream_context' => $ctx));

	// Set WS Security UsernameToken
	$WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
	$usernameToken = new stdClass(); 
	$usernameToken->Username = new SoapVar($f_data['username'], XSD_STRING, null, null, null, $WSSENS);
	$usernameToken->Password = new SoapVar($f_data['password'], XSD_STRING, null, null, null, $WSSENS);
	$content = new stdClass(); 
	$content->UsernameToken = new SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, null, $WSSENS);
	$header = new SOAPHeader($WSSENS, 'Security', $content);
	$client->__setSoapHeaders($header); 
	
	// Parse Response
	$html = NULL;
	$f_rates = NULL;
	$f_num = 0;
	
	// Fix postal code format.
	if($a_data['a_country_iso3116'] == "CA" || $a_data['a_country_iso3116'] == "Canada" || $a_data['a_country_iso3116'] == "US" || $a_data['a_country_iso3116'] == "United States") {
		$a_data['a_postalcode'] = str_replace('-', '', $a_data['a_postalcode']);
		$a_data['a_postalcode'] = preg_replace('/\s+/', '', $a_data['a_postalcode']);
	}	

	try {
		foreach($f_data['packages'] as $data) {
			if($a_data['a_country_iso3116'] == "CA" || $a_data['a_country_iso3116'] == "Canada") {
				if($signature == TRUE) {
					// Canada with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'domestic' 				=> array(
										'postal-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'SO'))
							)
						)
					), NULL, NULL);
				}
				else {
					// Canada without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'domestic' 				=> array(
										'postal-code'			=> $a_data['a_postalcode']
									)
								)
							)
						)
					), NULL, NULL);	
				}
			}
			else if($a_data['a_country_iso3116'] == "US" || $a_data['a_country_iso3116'] == "United States") {
				if($signature == TRUE) {
					// United States with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'united-states' 				=> array(
										'zip-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'SO'))
							)
						)
					), NULL, NULL);	
				}
				else {
					// United States without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'united-states' 				=> array(
										'zip-code'			=> $a_data['a_postalcode']
									)
								)
							)
						)
					), NULL, NULL);
				}
			}
			else {
				if($signature == TRUE) {
					// International with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'international' 		=> array(
										'country-code'			=> $a_data['a_country_iso3116']
									)
								),
								'options'	=> array('option' => array('option-code' => 'SO'))
							)
						)
					), NULL, NULL);
				}
				else {
					// International without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'international' 		=> array(
										'country-code'			=> $a_data['a_country_iso3116']
									)
								)
							)
						)
					), NULL, NULL);
				}
			}

			$f_rates = php_result_data($result, $f_rates, $data, $f_num, $signature);
			
			$f_num++;
		}

		// Now get the insured options.
		foreach($f_data['packages'] as $data) {
			if($a_data['a_country_iso3116'] == "CA" || $a_data['a_country_iso3116'] == "Canada") {
				if($signature == TRUE) {
					// Canada insured with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'domestic' 				=> array(
										'postal-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'SO', 'option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
				else {
					// Canada insured without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'domestic' 				=> array(
										'postal-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
			}
			else if($a_data['a_country_iso3116'] == "US" || $a_data['a_country_iso3116'] == "United States") {
				if($signature == TRUE) {
					// United States insured with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'united-states' 				=> array(
										'zip-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
				else {
					// United States insured without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'united-states' 				=> array(
										'zip-code'			=> $a_data['a_postalcode']
									)
								),
								'options'	=> array('option' => array('option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
			}
			else {
				if($signature == TRUE) {
					// International insured with signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'international' 		=> array(
										'country-code'			=> $a_data['a_country_iso3116']
									)
								),
								'options'	=> array('option' => array('option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
				else {
					// International insured without signature.
					$result = $client->__soapCall('GetRates', array(
						'get-rates-request' => array(
							'locale'			=> 'EN',
							'mailing-scenario' 			=> array(
								'customer-number'			=> $f_data['customer_number'],
								'parcel-characteristics'	=> array(
									'weight'					=> number_format($data['weight'], 2, '.', ''),
									'dimensions'				=> array('length' => $data['length'], 'width' => $data['width'], 'height' => $data['height'])
								),
								'origin-postal-code'		=> $a_data['a_postalcode_origin'],
								'destination' 			=> array(
									'international' 		=> array(
										'country-code'			=> $a_data['a_country_iso3116']
									)
								),
								'options'	=> array('option' => array('option-code' => 'COV', 'option-amount' => number_format($data['price'] , 2, '.', '')))						
							)
						)
					), NULL, NULL);
				}
			}

			$f_rates = php_result_data($result, $f_rates, $data, $f_num, $signature);
			
			$f_num++;
		}
	} 
	catch(SoapFault $exception) {
		//$html .= 'Fault Code: ' . trim($exception->faultcode) . "<br>"; 
		//$html .= 'Fault Reason: ' . trim($exception->getMessage()) . "<br>"; 

		return $f_rates;
	}	
	
	return $f_rates;
}

function php_result_data($result, $f_rates, $data, $f_num, $signature = FALSE) {
	if(isset($result->{'price-quotes'})) {
		foreach($result->{'price-quotes'}->{'price-quote'} as $priceQuote) {
			$signature_found = FALSE;
			// If no expected delivery date, means we have no tracking number. We only want to use services with tracking numbers for security purposes.
			if(isset($priceQuote->{'service-standard'}->{'expected-delivery-date'})) {
				$f_service_name = $priceQuote->{'service-name'};

				// --> Check if we have insured option, then append insured onto the shipping name.
				if(isset($priceQuote->{'price-details'}->{'options'}->{'option'})) {
					foreach($priceQuote->{'price-details'}->{'options'}->{'option'} as $f_tmp_options) {
						if(isset($f_tmp_options->{'option-code'})) {
							if($f_tmp_options->{'option-code'} == 'COV') {
								$f_service_name = $priceQuote->{'service-name'} . " (Insured)";

								if($data['price'] <= 5000) {
									$f_tmp_value = number_format($data['price'] , 2, '.', '');
								}
								else {
									$f_tmp_value = number_format(5000, 2, '.', ''); // --> 5000 is the max value Canada Post allows for insuring a package.
								}
									
								$f_tmp_options->{'cov-value'} = $f_tmp_value;
							}
							
							if($f_tmp_options->{'option-code'} == 'SO') {
								$signature_found = TRUE;
							}
							else {
								$signature_found = FALSE;
							}
						}
					}
				}
				
				$f_record = FALSE;
				if($signature == FALSE) {
					$f_record = TRUE;
				}
				else if($signature == TRUE && $signature_found == TRUE) {
					// If we require signatures, then we record signatures.
					$f_record = TRUE; 
				}
				
				if($f_record == TRUE) {
					if(empty($f_rates[$f_num][$priceQuote->{'service-name'}])) {
						$f_rates[$f_service_name][$f_num] = Array("price" => NULL, "delivery_date_stamp" => NULL, "delivery_date" => NULL, "transit_time" => NULL, "f_package" => NULL, "options" => NULL); 
					}
					
					$f_rates[$f_service_name][$f_num]['f_package'] = $data;
					$f_rates[$f_service_name][$f_num]['price'] = $priceQuote->{'price-details'}->{'due'};

					if(isset($priceQuote->{'price-details'}->{'options'}->{'option'})) {
						$f_rates[$f_service_name][$f_num]['options'] = $priceQuote->{'price-details'}->{'options'}->{'option'};
					}
						
					if(isset($priceQuote->{'service-standard'}->{'expected-delivery-date'})) {
						$f_rates[$f_service_name][$f_num]['delivery_date_stamp'] = strtotime($priceQuote->{'service-standard'}->{'expected-delivery-date'});
						$f_rates[$f_service_name][$f_num]['delivery_date'] = cp_filterDeliveryDate($priceQuote->{'service-standard'}->{'expected-delivery-date'});
						$f_rates[$f_service_name][$f_num]['transit_time'] = $priceQuote->{'service-standard'}->{'expected-transit-time'};						
					}
					
					$f_rates[$f_service_name][$f_num]['s_data'] = $priceQuote;
				}
			}
		}
	}
	
	return $f_rates;
}

function cp_filterDeliveryDate($timestamp) {
	return date("D M d, Y", strtotime($timestamp));
}

// Delete?
function cp_getProvinceCode($postalCode) {
	switch( strtoupper(substr($postalCode, 0, 1))) {
		case "A": return "NL"; // Newfoundland and Labrador
		case "B": return "NS"; // Nova Scotia
		case "C": return "PE"; // Prince Edward Island
		case "E": return "NB"; // New Brunswick
		case "G": return "QC"; // Eastern Quebec
		case "H": return "QC"; // Metropolitan Montreal
		case "J": return "QC"; // Western Quebec
		case "K": return "ON"; // Eastern Ontario
		case "L": return "ON"; // Central Ontario
		case "M": return "ON"; // Metropolitan Toronto
		case "N": return "ON"; // Southwestern Ontario
		case "P": return "ON"; // Northern Ontario
		case "R": return "MB"; // Manitoba
		case "S": return "SK"; // Saskatchewan
		case "T": return "AB"; // Alberta
		case "V": return "BC"; // British Columbia
		case "X": return "NT,NU"; // Northwest Territories and Nunavut
		case "Y": return "YT"; // Yukon Territory

		default : return "";
	}
}
?>
