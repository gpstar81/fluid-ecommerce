<?php
// Michael Rajotte - 2012 / 2017 / 2018 / 2019
// fedex.php
// Fluid required file for integration with the FedEx api.

/*
<s1:address location="https://wsbeta.fedex.com:443/web-services/ship"/>

change it to

<s1:address location="https://ws.fedex.com:443/web-services/ship"/>
*/

require_once("../3rd-party-src/fedex-api/library/fedex-common.php");
	
function php_fedex($a_data, $signature = FALSE) {
	$path_to_wsdl =  "../3rd-party-src/fedex-api/wsdl/RateService_v13.wsdl";

	ini_set("soap.wsdl_cache_enabled", "0");
	$a_data['fedex_client'] = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
	
	//$html = NULL;

	//$foundRate = 0;
	//$boxWeight = 0.250; //grams

	$a_data['found_rate'] = 0;
	
	//Remove whitespace and - from postal/zip code for US or CA orders.
	if($a_data['a_country_iso3116'] == "CA" || $a_data['a_country_iso3116'] == "US") {
		$a_data['a_postalcode'] = str_replace('-', '', $a_data['a_postalcode']);
		$a_data['a_postalcode'] = preg_replace('/\s+/', '', $a_data['a_postalcode']);
	}
	
	//$i = 0;
	$rates = NULL;
	$rates = getFedexRates($a_data, $rates, FALSE, $signature); // --> Get non insured rates.
	$rates = getFedexRates($a_data, $rates, TRUE, $signature); // --> Get insured rates.

	return $rates;
}

function getFedexRates($a_data, $f_rates, $insured = FALSE, $signature = FALSE) {
	$foundRate = 0;
	$html = NULL;
	
	//global $fedex_client;
	
	$request['WebAuthenticationDetail'] = array(
		'UserCredential' =>array(
			'Key' => $a_data['fedex_key'], 
			'Password' => $a_data['fedex_password']
		)
	); 
	$request['ClientDetail'] = array(
		'AccountNumber' => $a_data['fedex_account'], 
		'MeterNumber' => $a_data['fedex_meter']
	);
	$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v13 using PHP ***');
	$request['Version'] = array(
		'ServiceId' => 'crs', 
		'Major' => '13', 
		'Intermediate' => '0', 
		'Minor' => '0'
	);
	$request['ReturnTransitAndCommit'] = true;
	$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
	$request['RequestedShipment']['ShipTimestamp'] = date('c');
	//$request['RequestedShipment']['ServiceType'] = $rateType; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, INTERNATIONAL_PRIORITY, FEDEX_2_DAY, FEDEX_EXPRESS_SAVER, INTERNATIONAL_ECONOMY, INTERNATIONAL_FIRST, FIRST_OVERNIGHT
	//$request['RequestedShipment']['ServiceType'] = array('PRIORITY_OVERNIGHT', 'FEDEX_GROUND', 'FEDEX_2_DAY', 'FIRST_OVERNIGHT', 'FEDEX_EXPRESS_SAVER');
	$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	if($insured == TRUE)
		$request['RequestedShipment']['TotalInsuredValue']=array('Amount'=>$a_data['f_package_values'],'Currency'=>'CAD');
		
	$request['RequestedShipment']['Shipper'] = addShipper($a_data);
	$request['RequestedShipment']['Recipient'] = addRecipient($a_data);
	$request['RequestedShipment']['ShippingChargesPayment'] = addShippingChargesPayment($a_data);
	$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
	$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
	$request['RequestedShipment']['PackageCount'] = $a_data['f_package_count']; // '1'

	//$request['RequestedShipment']['RequestedPackageLineItems'] = addPackageLineItem1($a_data);

	$packages_line_array = NULL;

	/*
	Note: Signature required only works for Canadian ground shipments only. US addresses have all signature options available.
	https://www.fedex.com/us/developer/WebHelp/ws/2015/html/WebServicesHelp/WSDVG/2_Rate_Services.htm
	
	*/
	$i = 1;
	foreach($a_data['f_ship_data'] as $key => $data) {
		if($insured == FALSE) {
			if($signature == TRUE) {
				$packages_line_array[] = array(
					'SequenceNumber' => $i,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => $data['weight'],
						'Units' => 'KG'
					),
					'Dimensions' => array(
						'Length' => $data['length'],
						'Width' => $data['width'],
						'Height' => $data['height'],
						'Units' => 'CM'
					),
					'SpecialServicesRequested' => array('SpecialServiceTypes' => 'SIGNATURE_OPTION', 'SignatureOptionDetail' => array('OptionType' => 'DIRECT'))
				);
			}
			else {
				$packages_line_array[] = array(
					'SequenceNumber' => $i,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => $data['weight'],
						'Units' => 'KG'
					),
					'Dimensions' => array(
						'Length' => $data['length'],
						'Width' => $data['width'],
						'Height' => $data['height'],
						'Units' => 'CM'
					)
				);
			}
		}
		else {
			if($signature == TRUE) {
				$packages_line_array[] = array(
					'SequenceNumber' => $i,
					'GroupPackageCount' => 1,
					'InsuredValue' => array(
						'Amount' => $data['price'],
						'Currency' => 'CAD'
					),
					'Weight' => array(
						'Value' => $data['weight'],
						'Units' => 'KG'
					),
					'Dimensions' => array(
						'Length' => $data['length'],
						'Width' => $data['width'],
						'Height' => $data['height'],
						'Units' => 'CM'
					),
					'SpecialServicesRequested' => array('SpecialServiceTypes' => 'SIGNATURE_OPTION', 'SignatureOptionDetail' => array('OptionType' => 'DIRECT'))
				);
			}
			else {
				$packages_line_array[] = array(
					'SequenceNumber' => $i,
					'GroupPackageCount' => 1,
					'InsuredValue' => array(
						'Amount' => $data['price'],
						'Currency' => 'CAD'
					),
					'Weight' => array(
						'Value' => $data['weight'],
						'Units' => 'KG'
					),
					'Dimensions' => array(
						'Length' => $data['length'],
						'Width' => $data['width'],
						'Height' => $data['height'],
						'Units' => 'CM'
					)
				);
			}
		}
		
		$i++;
	}
	
	$request['RequestedShipment']['RequestedPackageLineItems'] = $packages_line_array;

	try {
		if(setEndpoint('changeEndpoint')) {
			$newLocation = $a_data['fedex_client']->__setLocation(setEndpoint('endpoint'));
		}
		
		$response = $a_data['fedex_client']->getRates($request);
		
		if($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {  	
			if(isset($response->RateReplyDetails)) {
				foreach($response->RateReplyDetails as $fedex_rates) {
					if(isset($fedex_rates->ServiceType)) {
						if($signature == TRUE) {
							if(isset($fedex_rates->SignatureOption)) {
								if($fedex_rates->SignatureOption == 'DIRECT' || $fedex_rates->SignatureOption == 'INDIRECT' || $fedex_rates->SignatureOption == 'ADULT') {
									$f_signature = TRUE;
								}
								else {
									$f_signature = FALSE;
								}
							}
						}
						else {
							$f_signature = TRUE;
						}
						
						if($f_signature == TRUE) {
							$f_ship_type = php_fedex_print_ship_type($fedex_rates->ServiceType);
			
							if(isset($fedex_rates->RatedShipmentDetails)) {
								foreach($fedex_rates->RatedShipmentDetails as $f_details) {
									
									if(isset($f_details->ShipmentRateDetail->Surcharges)) {
										foreach($f_details->ShipmentRateDetail->Surcharges as $surcharges) {
											if(isset($surcharges->SurchargeType)) {
												if($surcharges->SurchargeType == 'INSURED_VALUE') {
													//$f_ship_type .= " (Insured)";
													$f_ship_type = $f_ship_type = php_fedex_print_ship_type($fedex_rates->ServiceType) . " (Insured)";
													$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['insured_value'] = $a_data['f_package_values'];
													$fedex_rates->insured_value = $a_data['f_package_values'];
												}
											}
										}

										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['surcharges'] = $f_details->ShipmentRateDetail->Surcharges;
									}
									
									$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['ship_type'] = $f_ship_type;
									$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['price'] = $f_details->ShipmentRateDetail->TotalNetCharge->Amount;
									
									if(isset($fedex_rates->DeliveryTimestamp)) {
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date_stamp'] = strtotime($fedex_rates->DeliveryTimestamp);
									}
									else {
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date_stamp'] = NULL;
									}

									if(isset($fedex_rates->TransitTime)) {
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date'] = filterTransitTime($fedex_rates->TransitTime);
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date_html'] = filterTransitTime($fedex_rates->TransitTime);
									}
									else if(isset($fedex_rates->DeliveryTimestamp)) {
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date'] = fedex_filterDeliveryDate($fedex_rates->DeliveryTimestamp);
									}
									else {
										$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['delivery_date'] = "";
									}
									
									$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['f_package'] = $a_data['f_ship_data'];
									$f_rates[$f_ship_type][$f_details->ShipmentRateDetail->RateType]['s_data'] = $fedex_rates;

								}
							}
						}
					}
				}
			}
		}
	} 
	catch (SoapFault $exception) {
	   return $f_rates;
	}
	
	return $f_rates;
}

function fedex_filterDeliveryDate($timestamp) {
	if($timestamp > 0) {
		return date("D M d, Y g:i A", strtotime($timestamp));
	}
	else {
		return NULL;
	}
}

function filterTransitTime($transitTime) {
	if($transitTime == "ONE_DAY") {
		return "1 Business Day";
	}
	else if($transitTime == "TWO_DAYS") {
		return "2 Business Days";
	}
	else if($transitTime == "THREE_DAYS") {
		return "3 Business Days";
	}
	else if($transitTime == "FOUR_DAYS") {
		return "4 Business Days";
	}
	else if($transitTime == "FIVE_DAYS") {
		return "5 Business Days";
	}
	else if($transitTime == "SIX_DAYS") {
		return "6 Business Days";
	}
	else if($transitTime == "SEVEN_DAYS") {
		return "7 Business Days";
	}
	else if($transitTime == "EIGHT_DAYS") {
		return "8 Business Days";
	}
	else if($transitTime == "NINE_DAYS") {
		return "9 Business Days";
	}
	else if($transitTime == "TEN_DAYS") {
		return "10 Business Days";
	}
	else {
		return "10+ Business Days";
	}
}

function php_fedex_print_ship_type($serviceType) {
//STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, INTERNATIONAL_PRIORITY, FEDEX_2_DAY, FEDEX_EXPRESS_SAVER, INTERNATIONAL_ECONOMY, INTERNATIONAL_FIRST, FIRST_OVERNIGHT
	if($serviceType == "STANDARD_OVERNIGHT") {
		return "Standard Overnight";
	}
	else if($serviceType == "PRIORITY_OVERNIGHT") {
		return "Priority Overnight";
	}
	else if($serviceType == "FEDEX_GROUND") {
		return "FedEx Ground";
	}
	else if($serviceType == "INTERNATIONAL_PRIORITY") {
		return "International Priority";
	}
	else if($serviceType == "FEDEX_2_DAY") {
		return "FedEx 2 Day";
	}
	else if($serviceType == "FEDEX_EXPRESS_SAVER") {
		return "FedEx Express Saver";
	}
	else if($serviceType == "INTERNATIONAL_ECONOMY") {
		return "International Economy";	
	}
	else if($serviceType == "INTERNATIONAL_FIRST") {
		return "International First";	
	}
	else if($serviceType == "FIRST_OVERNIGHT") {
		return "First Overnight";
	}
	else {
		return $serviceType;
	}
}

function addShipper($a_data){
	$shipper = array(
		'Contact' => array(
			'PersonName' => $a_data['fedex_person_name'],
			'CompanyName' => $a_data['fedex_company_name'],
			'PhoneNumber' => $a_data['fedex_phone_number']),
		'Address' => array(
			'StreetLines' => $a_data['fedex_street'],
			'City' => $a_data['fedex_city'],
			'StateOrProvinceCode' => $a_data['fedex_province'],
			'PostalCode' => $a_data['fedex_postal_code'],
			'CountryCode' => $a_data['fedex_country'])
	);
	
	return $shipper;
}
function addRecipient($a_data){
	//global $countryCode, $postalCodeDest, $province;
	
	if($a_data['a_country_iso3116'] == "CA" || $a_data['a_country_iso3116'] == "US") {
		$recipient = array(
			'Contact' => array(
				'PersonName' => $a_data['a_name'],
				'CompanyName' => 'Company Name',
				'PhoneNumber' => $a_data['a_phonenumber']
			),
			'Address' => array(
				//'StreetLines' => 'One Blue Jays Way',
				//'City' => 'Toronto',
				'StateOrProvinceCode' => $a_data['a_province_code'],
				'PostalCode' => $a_data['a_postalcode'],
				'CountryCode' => $a_data['a_country_iso3116'],
				'Residential' => true)
		);
		return $recipient;
		
	} 
	else {
		$recipient = array(
			'Contact' => array(
				'PersonName' => $a_data['a_name'],
				'CompanyName' => 'Company Name',
				'PhoneNumber' => $a_data['a_phonenumber']
			),
			'Address' => array(
				//'StreetLines' => 'One Blue Jays Way',
				//'City' => 'Toronto',
				//'StateOrProvinceCode' => $province,
				'PostalCode' => $a_data['a_postalcode'],
				'CountryCode' => $a_data['a_country_iso3116'],
				'Residential' => true)
		);
		
		return $recipient;
	}
	    
}
function addShippingChargesPayment($a_data) {
	$shippingChargesPayment = array(
		'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
		'Payor' => array(
			'ResponsibleParty' => array(
			'AccountNumber' => $a_data['fedex_account'],
			'CountryCode' => 'CA')
		)
	);
	
	return $shippingChargesPayment;
}
function addLabelSpecification() {
	$labelSpecification = array(
		'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
		'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
		'LabelStockType' => 'PAPER_7X4.75');
		
	return $labelSpecification;
}
function addSpecialServices() {
	$specialServices = array(
		'SpecialServiceTypes' => array('COD'),
		'CodDetail' => array(
			'CodCollectionAmount' => array('Currency' => 'USD', 'Amount' => 150),
			'CollectionType' => 'ANY')// ANY, GUARANTEED_FUNDS
	);
	
	return $specialServices; 
}
function addPackageLineItem1($a_data){
	//global $weightS, $widthS, $lengthS, $heightS;
	
	$packageLineItem = array(
		'SequenceNumber'=>1,
		'GroupPackageCount'=>1,
		'Weight' => array(
			'Value' => $a_data['f_ship_data']['weight'],
			'Units' => 'KG'
		),
		'Dimensions' => array(
			'Length' => $a_data['f_ship_data']['length'],
			'Width' => $a_data['f_ship_data']['width'],
			'Height' => $a_data['f_ship_data']['height'],
			'Units' => 'CM'
		)
	);
	
	return $packageLineItem;
}

// Delete?
function fedex_getProvinceCode() {
	global $postalCodeDest;
	
	switch( strtoupper(substr($postalCodeDest, 0, 1))) {
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
