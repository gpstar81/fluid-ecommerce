<?php
// Michael Rajotte - 2017 Avril
// fdom.php
// Process external url links and grab data.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");

$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";

function php_search_amazon($url, $options, $region, $override = FALSE, $f_miller_aus = NULL) {
	$f_cookie_file = "/var/www/f_cookie.txt";

	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	/*
	$fh = fopen('/var/www/html.txt', 'w') or die("can't open file");
	fwrite($fh, $html);
	fclose($fh);
    */

	curl_close($c);

	if($region == 2)
		$link = get_links_miller($html, $override);
	else if($region == 3)
		$link = get_links_miller_aus($html, $override, $f_miller_aus);
	else if($region == 4)
		$link = get_links_henrys($html, $override);
	else if($region == 5)
		$link = get_links_panasonic($html, $override);
	else
		$link = get_links_amazon($html, $override);

	$data_link = NULL;

	if(isset($link)) {
		// If we got a link on Canada, lets try to get data from USA.
		if($region == 1) {
			//$u_link = str_replace("amazon.com", "amazon.ca", $link);
			//$image_link = php_get_images_amazon($u_link, 0);

			// No USA data, lets get it from the original Canadian site now.
			//if($image_link == NULL)
				$data_link = php_get_images_amazon($link, $region);
		}
		else if($region == 2)
			$data_link = php_get_images_miller($link, $region);
		else if($region == 3)
			$data_link = php_get_images_miller_aus($link, $region);
		else if($region == 4)
			$data_link = php_get_images_henrys($link, $region);
		else if($region == 5)
			$data_link = php_get_images_panasonic($link, $region);
		else
			$data_link = php_get_images_amazon($link, $region);
	}

	$f_image_data = Array("image" => NULL, "f_bullets" => NULL, "f_description" => NULL, "f_inthebox" => NULL, "f_specs" => NULL, "f_name" => NULL, "f_found" => FALSE, "f_keywords" => NULL);

	if($data_link != NULL) {
		if($data_link['image'] != NULL && ($options < 2 || $options == 10)) {
			$f_img_count = 0;
			foreach($data_link['image'] as $f_img_key => $image_link) {
				if($f_img_count > 0)
					sleep(rand(1,4));

				if($region == 5) {
					$tmp_name = explode("/", $image_link);
					$org_name = end($tmp_name);
					//$extension_tmp = str_replace("?", $org_name);
					//$extension_tmp = str_replace("$", $extension_tmp);
					//$extension = end($extension_tmp);
					$extension = "jpg";
				}
				else {
					$tmp_name = explode("/", $image_link);
					$org_name = end($tmp_name);
					$extension_tmp = explode(".", $org_name);
					$extension = end($extension_tmp);
				}

				copy($image_link, FOLDER_IMAGES_TEMP . $org_name);

				$rand = substr(str_shuffle(md5(time())),0,30);

				$f_image['name'] = $org_name;
				$f_image['type'] = mime_content_type(FOLDER_IMAGES_TEMP . $org_name);
				$f_image['tmp_name'] = $org_name;
				$f_image['error'] = 0;
				$f_image['size'] = filesize(FOLDER_IMAGES_TEMP . $org_name);
				$f_image['extension'] = $extension;
				$f_image['rand'] = $rand;
				$f_image['fullpath'] = FOLDER_IMAGES . $rand . "." . $extension;
				$f_image['image'] = $rand . "." . $extension;

				$f_image['noerror'] = rename(FOLDER_IMAGES_TEMP . $org_name, FOLDER_IMAGES . $rand . "." . $extension);

				if(is_file(FOLDER_IMAGES . $rand . "." . $extension))
					$f_image_data['image'][$rand]['file'] = $f_image;

				$f_img_count++;
			}
		}

		if($data_link['f_bullets'] != NULL && ($options == 0 || $options == 5 || $options == 8 || $options == 7 || $options == 10))
			$f_image_data['f_bullets'] = $data_link['f_bullets'];

		if($data_link['f_description'] != NULL && ($options == 0 || $options == 6 || $options == 7 || $options == 8 || $options == 10)) {
			if($region != 4)
				$f_image_data['f_description'] = $data_link['f_description'];
			else {
				if($options != 14)
					$f_image_data['f_description'] = $data_link['f_description'];
			}
		}

		if($data_link['f_dimensions'] != NULL && ($options == 0 || $options == 2 || $options == 3 || $options == 8 || $options == 10))
			$f_image_data['f_dimensions'] = $data_link['f_dimensions'];

		if($data_link['f_weight'] != NULL && ($options == 0 || $options == 2 || $options == 4 || $options == 8 || $options == 10))
			$f_image_data['f_weight'] = $data_link['f_weight'];

		if($data_link['f_name'] != NULL && ($options == 0 || $options == 8 || $options == 9))
			$f_image_data['f_name'] = $data_link['f_name'];

		if($data_link['f_specs'] != NULL && ($options == 0 || $options == 10 || $options == 11 || $options == 14))
			$f_image_data['f_specs'] = $data_link['f_specs'];

		if(isset($data_link['f_keywords']) && ($options == 0 || $options == 10 || $options == 12 || $options == 14)) {
			if($data_link['f_keywords'] != NULL)
				$f_image_data['f_keywords'] = $data_link['f_keywords'];
		}

		if(isset($data_link['f_inbox']) && ($options == 0 || $options == 10 || $options == 13 || $options == 14)) {
			if($data_link['f_inbox'] != NULL)
				$f_image_data['f_inthebox'] = $data_link['f_inbox'];
		}

		$f_image_data['f_found'] = TRUE;
	}

	return $f_image_data;
}

function get_links_panasonic($html, $override = FALSE) {

    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
    $xml->validateOnParse = true;
	$xml->loadHTML($html);

	$div = NULL;
	if(isset($xml->getElementById('primary')->tagName))
		$div = $xml->getElementById('primary')->tagName;

	$link = NULL;

	if(empty($div)) {
		$i = 0;
		foreach($xml->getElementsByTagName('a') as $link) {
			$link = $link->getAttribute('href');
			break;
		}
	}

	return $link;
}

function get_links_henrys($html, $override = FALSE) {

    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
    $xml->validateOnParse = true;
	$xml->loadHTML($html);

	$div = NULL;
//	if(isset($xml->getElementById('fluid-listing-none')->tagName))
	//	$div = $xml->getElementById('fluid-listing-none')->tagName;

	$link = NULL;

	if(empty($div)) {
		if(isset($xml->getElementById('appProduct')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='appProduct']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('a') as $link) {
				$link = "https://www.henrys.com" . $link->getAttribute('href');
				break;
			}
		}

		/*
		$finder = new DomXPath($xml);
		$classname="fluid_prod_div";
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

		$tmp_dom = new DOMDocument();
		foreach ($nodes as $node) {
			$tmp_dom->appendChild($tmp_dom->importNode($node,true));

			if($override == FALSE)
				break;
		}

		//Loop through each <a> tag in the dom and add it to the link array
		foreach($tmp_dom->getElementsByTagName('a') as $link) {
			//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
			$link = $link->getAttribute('href');

			if($override == TRUE)
				if(isset($link))
					break;
		}
		*/
	}

	return $link;
}

function get_links_miller_aus($html, $override = FALSE, $f_miller_aus) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
    $xml->validateOnParse = true;
	$xml->loadHTML($html);

	/*
	$div = NULL;
	if(isset($xml->getElementById('fluid-listing-none')->tagName))
		$div = $xml->getElementById('fluid-listing-none')->tagName;
	*/

	$link = NULL;

	$finder = new DomXPath($xml);
	$classname="product-item-details";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	$image = NULL;
    foreach($tmp_dom->getElementsByTagName('p') as $t_link) {
		$f_link = str_replace("CAT:", "", $t_link->textContent);
		$f_link = str_replace(" ", "", $f_link);

		if($f_miller_aus == $f_link) {
			$finder_l = new DomXPath($xml);
			$classname="learn-more";
			$nodes_l = $finder_l->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

			$tmp_dom_l = new DOMDocument();
			foreach($nodes_l as $node_l) {
				$tmp_dom_l->appendChild($tmp_dom_l->importNode($node_l,true));

				if($override == FALSE)
					break;
			}

			//Loop through each <a> tag in the dom and add it to the link array
			foreach($tmp_dom_l->getElementsByTagName('a') as $s_link) {
				//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
				$link = $s_link->getAttribute('href');

				if($override == TRUE)
					if(isset($link))
						break;
			}

		}

		break;
    }

	return $link;
}

function php_get_images_henrys($url, $region) {

	$f_cookie_file = "/var/www/f_cookie.txt";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	sleep(rand(5, 10));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images_henrys($html, $region);
}

function php_get_images_panasonic($url, $region) {

	$f_cookie_file = "/var/www/f_cookie.txt";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	sleep(rand(5, 10));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images_panasonic($html, $region);
}

function get_links_miller($html, $override = FALSE) {

    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
    $xml->validateOnParse = true;
	$xml->loadHTML($html);

	$div = NULL;
	if(isset($xml->getElementById('fluid-listing-none')->tagName))
		$div = $xml->getElementById('fluid-listing-none')->tagName;

	$link = NULL;

	if(empty($div)) {
		$finder = new DomXPath($xml);
		$classname="fluid_prod_div";
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

		$tmp_dom = new DOMDocument();
		foreach ($nodes as $node) {
			$tmp_dom->appendChild($tmp_dom->importNode($node,true));

			if($override == FALSE)
				break;
		}

		//Loop through each <a> tag in the dom and add it to the link array
		foreach($tmp_dom->getElementsByTagName('a') as $link) {
			//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
			$link = $link->getAttribute('href');

			if($override == TRUE)
				if(isset($link))
					break;
		}
	}

	return $link;
}

function php_get_images_miller_aus($url, $region) {

	$f_cookie_file = "/var/www/f_cookie.txt";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	sleep(rand(5, 10));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images_miller_aus($html, $region);
}

function php_get_images_miller($url, $region) {

	$f_cookie_file = "/var/www/f_cookie.txt";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	//sleep(rand(1, 2));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images_miller($html, $region);
}

function php_process_images_miller($html, $region) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);
	$xml->validateOnParse = true;

    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$f_name = NULL;
	// Lets grab the product name.
	if(isset($xml->getElementById('productTitle')->tagName)) {
		$el = $xml->getElementById('productTitle');

		$f_name = $el->textContent;
	}

	$f_bullets = NULL;
	// Remove unncessary data before grabbing the bullet points.
	if(isset($xml->getElementById('replacementPartsFitmentBullet')->tagName)) {
		$xpath = new DOMXPath($xml);
		$nlist = $xpath->query("//*[@id='replacementPartsFitmentBullet']");
		$node = $nlist->item(0);
		$node->parentNode->removeChild($node);
	}

	// Now grab bullet points.
	if(isset($xml->getElementById('feature-bullets')->tagName)) {
		$finder = new DomXPath($xml);
		$el = $finder->query("//*[@id='feature-bullets']")->item(0);

		foreach($el->getElementsByTagName('li') as $link) {
		   $f_bullets .= "<li>" . trim($link->textContent) . "</li>";
		}

		if(isset($f_bullets))
			$f_bullets = "<ul>" . trim($f_bullets) . "</ul>";
	}

	$f_description = NULL;
	// Lets grab the product description
	$finder = new DomXPath($xml);
	$classname="a-row feature";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	if(isset($tmp_dom->getElementById('productDescription')->tagName)) {
		$finder = new DomXPath($tmp_dom);
		$el = $finder->query("//*[@id='productDescription']")->item(0);

		foreach($el->getElementsByTagName('p') as $link)
		   $f_description .= "<p>" . $link->textContent . "</p>";

		//if(isset($f_description))
			//$f_description = utf8_encode($f_description);
	}


	// Lets grab the item dimensions and weight.
	$nextDimensions = FALSE;
	$nextWeight = FALSE;
	$p_dimensions = NULL;
	$p_dimension_raw = NULL;
	$p_dimensions_key = NULL;
	$p_weight = NULL;
	$p_weight_raw = NULL;
	$p_weight_key = NULL;
	//$p_ship_key = NULL;

	if($region == 0)
		$f_canada = FALSE;
	else
		$f_canada = TRUE;

	if($f_canada == TRUE) {
		if(isset($xml->getElementById('detail_bullets_id')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='detail_bullets_id']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('li') as $link) {
				$f_tmp = $link->textContent;

				if(strpos($f_tmp, 'Product Dimensions') !== false) {

					$f_w = explode('Product Dimensions:', $f_tmp);

					if(isset($f_w[1])) {
						$f_w2 = explode(";", $f_w[1]);

						if(isset($f_w2[0])) {
							$f_w3 = explode(" x ", $f_w2[0]);

							if(isset($f_w3[0]) && isset($f_w3[1]) && isset($f_w3[2])) {
								$p_dimensions['length'] = trim($f_w3[0]);
								$p_dimensions['width'] = trim($f_w3[1]);
								$p_dimensions['height'] = str_replace(" cm", "", trim($f_w3[2]));
							}
						}
					}
				}

				if(strpos($f_tmp, 'Shipping Weight') !== false) {
					$f_w = explode('Shipping Weight:', $f_tmp);

					if(isset($f_w[1])) {
						if(strpos($f_w[1], ' Kg') !== false) {
							$f_w2 = explode(" Kg", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
						}
						else {
							$f_w2 = explode(" g", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
								$p_weight = $p_weight * 0.001;
						}
					}
				}


				$i++;
			}
		}
	}
	else {
		if(isset($xml->getElementById('productDetails_detailBullets_sections1')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='productDetails_detailBullets_sections1']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('th') as $link) {
				$string = preg_replace('/\s+/', '', $link->textContent);

				if($string == "ProductDimensions")
					$p_dimensions_key = $i;

				if($string == "ItemWeight")
					$p_weight_key = $i;

				//if($string == "ShippingWeight")
					//$p_ship_key = $i;

			   $i++;
			}

			if(isset($p_dimensions_key))
				$p_dimensions_raw = trim($el->getElementsByTagName('td')[$p_dimensions_key]->textContent);

			// Priortize ship weight vs item weight?
			//if(isset($p_ship_key))
				//$p_weight_raw = trim($el->getElementsByTagName('td')[$p_ship_key]->textContent);
			if(isset($p_weight_key))
				$p_weight_raw = trim($el->getElementsByTagName('td')[$p_weight_key]->textContent);

			if(isset($p_dimensions_raw)) {
				$p_dimensions_raw = str_replace("x ", "", $p_dimensions_raw);
				$p_dimensions_raw = explode(" ", $p_dimensions_raw);

				foreach($p_dimensions_raw as $con_key => $dimen) {
					if($con_key == 0)
						$p_dimensions['length'] = $dimen * 2.54;

					if($con_key == 1)
						$p_dimensions['width'] = $dimen * 2.54;

					if($con_key == 2)
						$p_dimensions['height'] = $dimen * 2.54;
				}
			}

			if(isset($p_weight_raw)) {
				$p_weight_raw = explode(" ", $p_weight_raw);

				if(isset($p_weight_raw[1])) {
					if($p_weight_raw[1] == "pounds")
						$p_weight = $p_weight_raw[0] * 0.453592;
					else if($p_weight_raw[1] == "ounces")
						$p_weight = $p_weight_raw[0] * 0.0283495;
				}
			}

		}
	}

	// Lets grab the images.
	$image = NULL;

	$finder = new DomXPath($xml);
	$classname="fluid_images";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	$image_grab = NULL;
	$image = NULL;
    foreach($tmp_dom->getElementsByTagName('a') as $link) {
			$image[] = $link->getAttribute('href');

    }

    return Array("image" => $image, "f_bullets" => $f_bullets, "f_description" => $f_description, "f_dimensions" => $p_dimensions, "f_weight" => $p_weight, "f_name" => $f_name);
}

function php_process_images_henrys($html, $region) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);
	$xml->validateOnParse = true;

    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$f_name = NULL;
	/*
	// Lets grab the product name.
	if(isset($xml->getElementById('productTitle')->tagName)) {
		$el = $xml->getElementById('productTitle');

		$f_name = $el->textContent;
	}
	*/
	$f_bullets = NULL;

	/*
	$finder = new DomXPath($xml);
	$classname="product attribute overview";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('ul') as $link) {
		//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
		//$link = $link->getAttribute('href');

		//$f_bullets = utf8_encode($link->nodeValue);
		$f_bullets = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));
		break;
	}
	*/

	// Lets get the description.
	/*
	$f_description = NULL;

	$finder = new DomXPath($xml);
	$classname="product attribute description";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('div') as $link) {
		$f_description = utf8_encode($link->nodeValue);
		//$f_description = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));

		break;
	}
	*/

	// Lets get the specifications.
	$f_table = NULL;

	$finder = new DomXPath($xml);
	$classname="product-spec-tab";
	//$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$el = $finder->query("//*[@id='specsTab']")->item(0);

	if(isset($el)) {
		foreach($el->getElementsByTagName('div') as $link) {
			$f_table = print_r($xml->saveXML($link), TRUE);
			$f_table = str_replace('<div class="product-spec-tab">', "", $f_table);
			$f_table = str_replace("</div>", "", $f_table);

			$f_table = htmlentities($f_table, null, 'utf-8');
			$f_table = str_replace("&nbsp;", "", $f_table);
			$f_table = html_entity_decode($f_table);

			$f_table = trim($f_table);

			$doc = new DOMDocument();
			$doc->loadHTML($f_table);

			$f_data_tmp = $doc->getElementsByTagName('table');
			$f_html = NULL;
			$f_info_removed = FALSE;
			$f_add = FALSE;
			$f_array = NULL;
			//for ($i = 0; $i < $f_data_tmp->length; $i++) {
				//$f_table = $f_data_tmp->item($i);
			foreach($f_data_tmp as $f_table) {

				if($f_table->getAttribute('title') == 'Additional') {
					$patterns = array();
					$patterns[0] = '/<table[^>]*>/';
					$patterns[1] = '/<\/table>/';
					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';

					$f_tmp = print_r($doc->saveXML($f_table), TRUE);
					$f_tmp = preg_replace($patterns, $replacements, $f_tmp);

					$patterns = array();
					$patterns[0] = '/<p[^>]*>/';
					$patterns[1] = '/<\/p>/';
					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';
					$f_tmp = preg_replace($patterns, $replacements, $f_tmp);
					$f_tmp = trim($f_tmp);

					if(strlen($f_table->nodeValue) < 1) {
						$f_table->parentNode->removeChild($f_table);
						$f_add = TRUE;
					}
				}
				else {
					$patterns = array();
					$patterns[0] = '/<table[^>]*>/';
					$patterns[1] = '/<\/table>/';
					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';

					$f_tmp = print_r($doc->saveXML($f_table), TRUE);
					$f_tmp = preg_replace($patterns, $replacements, $f_tmp);

					$patterns = array();
					$patterns[0] = '/<p[^>]*>/';
					$patterns[1] = '/<\/p>/';
					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';
					$f_tmp = preg_replace($patterns, $replacements, $f_tmp);
					$f_tmp = html_entity_decode($f_tmp);
					$f_tmp = strip_tags($f_tmp);
					$f_tmp = trim($f_tmp);

					if(strlen($f_table->nodeValue) < 1) {
						//echo "<pre>";
						//echo "</pre>";
						$f_array[] = $f_table->getAttribute('title');
						$f_table->parentNode->removeChild($f_table);
						$f_info_removed = TRUE;
					}

				}

			}

			$f_table = print_r($doc->saveXML(), TRUE);

			if($f_info_removed == TRUE) {
				foreach($f_array as $f_key) {
					$f_table = str_replace("<h2>" . $f_key . "</h2>", "", $f_table);
				}
			}

			if($f_add == TRUE)
				$f_table = str_replace("<h2>Additional Information</h2>", "", $f_table);

			$f_table = str_replace('table=""', "", $f_table);
		}
	}

	$f_inbox = NULL;

	$finder = new DomXPath($xml);
	$classname="what-include";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	//$el = $finder->query("//*[@id='specsTab']")->item(0);

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('ul') as $link) {
		$f_inbox = print_r($tmp_dom->saveXML($link), TRUE);
		//$f_description = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));

		break;
	}

	$f_keywords = NULL;

	$metas = $xml->getElementsByTagName('meta');

	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		//if($meta->getAttribute('name') == 'description')
			//$description = $meta->getAttribute('content');
		if($meta->getAttribute('name') == 'keywords')
			$f_keywords = $meta->getAttribute('content');
	}


	// Lets grab the product description
	$finder = new DomXPath($xml);
	$classname="spSection";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();

	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
	}

	$desc_array_tmp = NULL;
	foreach($tmp_dom->getElementsByTagName('div') as $link) {

		if(!empty($link->nodeValue)) {
			$f_tmp_name = strip_tags($link->nodeValue);
			$f_tmp_name = str_replace(" ", "", $f_tmp_name);
			$f_tmp_name = trim($f_tmp_name);
			$desc_array_tmp[base64_encode($f_tmp_name)] = print_r($tmp_dom->saveXML($link), TRUE);
		}
	}

	$desc_array = NULL;
	$f_id = 0;
	if(count($desc_array_tmp > 0)) {
		if(isset($desc_array_tmp)) {
			foreach($desc_array_tmp as $f_tmp) {
				if(!preg_match('/(<img[^>]+>)/i', $f_tmp) && !preg_match('/(<iframe[^>]+>)/i', $f_tmp)) {
					$patterns = array();
					$patterns[0] = '/<div[^>]*>/';
					$patterns[1] = '/<\/div>/';
					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';

					$f_tmp2 = preg_replace($patterns, $replacements, $f_tmp);
					$f_tmp2 = str_replace("<h1>", "<h2>", $f_tmp2);
					$f_tmp2 = str_replace("</h1>", "</h2>", $f_tmp2);
					$f_tmp2 = str_replace("<h2>", "<h2 id='fid_" . $f_id . "' class='product-features-heading'>", $f_tmp2);
					$f_tmp2 = str_replace("<p>", "<p class='product-features-paragraph'>", $f_tmp2);
					$f_tmp2 = preg_replace('/<h3[^>]*>([\s\S]*?)<\/h3[^>]*>/', '', $f_tmp2);

					$desc_array[base64_encode(strip_tags($f_tmp2))] = $f_tmp2;
					$f_id++;

					//echo $f_tmp2 . "<br><br>";
				}
			}
		}
	}

	$f_description = NULL;

	if(count($desc_array) > 0) {
		$i = 0;
		foreach($desc_array as $f_desc_tmp) {
			//if($i > 0)
				$f_description .= $f_desc_tmp;

			$i++;
		}
	}

	/*
	$finder = new DomXPath($tmp_dom);
	$el = $finder->query("//*[@id='productDescription']")->item(0);

	foreach($el->getElementsByTagName('p') as $link)
	   $f_description .= "<p>" . $link->textContent . "</p>";
	*/

	/*
	foreach($el->getElementsByTagName('div') as $link) {
		$f_table = print_r($xml->saveXML($link), TRUE);
		$f_table = str_replace('<div class="product-spec-tab">', "", $f_table);
		$f_table = str_replace("</div>", "", $f_table);

		$f_table = htmlentities($f_table, null, 'utf-8');
		$f_table = str_replace("&nbsp;", "", $f_table);
		$f_table = html_entity_decode($f_table);

		$f_table = trim($f_table);
		$f_table = utf8_encode($f_table);
	}
	*/

	/*
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('div') as $link) {
		//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
		//$link = $link->getAttribute('href');

	echo "<pre>";
		print_r($link);
	echo "</pre>";
		//$f_bullets = utf8_encode($link->nodeValue);
		$f_table = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));
		break;
	}
	*/

	/*
	$finder = new DomXPath($xml);
	$el = $finder->query("//*[@id='product-spec-tab']")->item(0);

	foreach($el->getElementsByTagName('table') as $link) {
	    $f_table = print_r($xml->saveXML($link), TRUE);
	}

	if(isset($f_table)) {
		$f_table = str_replace("<strong>", "", $f_table);
		$f_table = str_replace("</strong>", "", $f_table);
		$f_table = str_replace("<table>", '<table class="table table-striped">', $f_table);
	}
	*/

	/*
	$finder = new DomXPath($xml);
	$classname="product attribute description";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('div') as $link) {
		//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
		//$link = $link->getAttribute('href');

		$f_description = utf8_encode($link);

		break;
	}
	*/

	/*
	// Lets grab the product description
	$finder = new DomXPath($xml);
	$classname="a-row feature";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	if(isset($tmp_dom->getElementById('productDescription')->tagName)) {
		$finder = new DomXPath($tmp_dom);
		$el = $finder->query("//*[@id='productDescription']")->item(0);

		foreach($el->getElementsByTagName('p') as $link)
		   $f_description .= "<p>" . $link->textContent . "</p>";

		//if(isset($f_description))
			//$f_description = utf8_encode($f_description);
	}
	*/

	/*
	// Lets grab the item dimensions and weight.
	$nextDimensions = FALSE;
	$nextWeight = FALSE;
	$p_dimensions = NULL;
	$p_dimension_raw = NULL;
	$p_dimensions_key = NULL;
	$p_weight = NULL;
	$p_weight_raw = NULL;
	$p_weight_key = NULL;
	//$p_ship_key = NULL;

	if($region == 0)
		$f_canada = FALSE;
	else
		$f_canada = TRUE;

	if($f_canada == TRUE) {
		if(isset($xml->getElementById('detail_bullets_id')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='detail_bullets_id']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('li') as $link) {
				$f_tmp = $link->textContent;

				if(strpos($f_tmp, 'Product Dimensions') !== false) {

					$f_w = explode('Product Dimensions:', $f_tmp);

					if(isset($f_w[1])) {
						$f_w2 = explode(";", $f_w[1]);

						if(isset($f_w2[0])) {
							$f_w3 = explode(" x ", $f_w2[0]);

							if(isset($f_w3[0]) && isset($f_w3[1]) && isset($f_w3[2])) {
								$p_dimensions['length'] = trim($f_w3[0]);
								$p_dimensions['width'] = trim($f_w3[1]);
								$p_dimensions['height'] = str_replace(" cm", "", trim($f_w3[2]));
							}
						}
					}
				}

				if(strpos($f_tmp, 'Shipping Weight') !== false) {
					$f_w = explode('Shipping Weight:', $f_tmp);

					if(isset($f_w[1])) {
						if(strpos($f_w[1], ' Kg') !== false) {
							$f_w2 = explode(" Kg", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
						}
						else {
							$f_w2 = explode(" g", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
								$p_weight = $p_weight * 0.001;
						}
					}
				}


				$i++;
			}
		}
	}
	else {
		if(isset($xml->getElementById('productDetails_detailBullets_sections1')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='productDetails_detailBullets_sections1']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('th') as $link) {
				$string = preg_replace('/\s+/', '', $link->textContent);

				if($string == "ProductDimensions")
					$p_dimensions_key = $i;

				if($string == "ItemWeight")
					$p_weight_key = $i;

				//if($string == "ShippingWeight")
					//$p_ship_key = $i;

			   $i++;
			}

			if(isset($p_dimensions_key))
				$p_dimensions_raw = trim($el->getElementsByTagName('td')[$p_dimensions_key]->textContent);

			// Priortize ship weight vs item weight?
			//if(isset($p_ship_key))
				//$p_weight_raw = trim($el->getElementsByTagName('td')[$p_ship_key]->textContent);
			if(isset($p_weight_key))
				$p_weight_raw = trim($el->getElementsByTagName('td')[$p_weight_key]->textContent);

			if(isset($p_dimensions_raw)) {
				$p_dimensions_raw = str_replace("x ", "", $p_dimensions_raw);
				$p_dimensions_raw = explode(" ", $p_dimensions_raw);

				foreach($p_dimensions_raw as $con_key => $dimen) {
					if($con_key == 0)
						$p_dimensions['length'] = $dimen * 2.54;

					if($con_key == 1)
						$p_dimensions['width'] = $dimen * 2.54;

					if($con_key == 2)
						$p_dimensions['height'] = $dimen * 2.54;
				}
			}

			if(isset($p_weight_raw)) {
				$p_weight_raw = explode(" ", $p_weight_raw);

				if(isset($p_weight_raw[1])) {
					if($p_weight_raw[1] == "pounds")
						$p_weight = $p_weight_raw[0] * 0.453592;
					else if($p_weight_raw[1] == "ounces")
						$p_weight = $p_weight_raw[0] * 0.0283495;
				}
			}

		}
	}
	*/
	/*
	// Lets grab the images.
	$image = NULL;

	$finder = new DomXPath($xml);
	$classname="fluid_images";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	$image_grab = NULL;
	$image = NULL;
    foreach($tmp_dom->getElementsByTagName('a') as $link) {
			$image[] = $link->getAttribute('href');

    }
	*/

    return Array("image" => NULL, "f_bullets" => NULL, "f_description" => $f_description, "f_dimensions" => NULL, "f_weight" => NULL, "f_name" => NULL, "f_specs" => $f_table, "f_inbox" => $f_inbox, "f_keywords" => $f_keywords);
}

function php_process_images_miller_aus($html, $region) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);
	$xml->validateOnParse = true;

    // Load the url's contents into the DOM
	$xml->loadHTML($html);


	$f_name = NULL;
	/*
	// Lets grab the product name.
	if(isset($xml->getElementById('productTitle')->tagName)) {
		$el = $xml->getElementById('productTitle');

		$f_name = $el->textContent;
	}
	*/

	$f_bullets = NULL;

	$finder = new DomXPath($xml);
	$classname="product attribute overview";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('ul') as $link) {
		//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
		//$link = $link->getAttribute('href');

		//$f_bullets = utf8_encode($link->nodeValue);
		$f_bullets = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));
		break;
	}

	// Lets get the description.
	$f_description = NULL;

	$finder = new DomXPath($xml);
	$classname="product attribute description";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('div') as $link) {
		$f_description = utf8_encode($link->nodeValue);
		//$f_description = utf8_encode(print_r($tmp_dom->saveXML($link), TRUE));

		break;
	}

	// Lets get the specifications.
	$f_table = NULL;

	$finder = new DomXPath($xml);
	$el = $finder->query("//*[@id='specifications.tab']")->item(0);

	foreach($el->getElementsByTagName('table') as $link) {
	    $f_table = print_r($xml->saveXML($link), TRUE);
	}

	if(isset($f_table)) {
		$f_table = str_replace("<strong>", "", $f_table);
		$f_table = str_replace("</strong>", "", $f_table);
		$f_table = str_replace("<table>", '<table class="table table-striped">', $f_table);
	}

	/*
	$finder = new DomXPath($xml);
	$classname="product attribute description";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	//Loop through each <a> tag in the dom and add it to the link array
	foreach($tmp_dom->getElementsByTagName('div') as $link) {
		//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
		//$link = $link->getAttribute('href');

		$f_description = utf8_encode($link);

		break;
	}
	*/

	/*
	// Lets grab the product description
	$finder = new DomXPath($xml);
	$classname="a-row feature";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	if(isset($tmp_dom->getElementById('productDescription')->tagName)) {
		$finder = new DomXPath($tmp_dom);
		$el = $finder->query("//*[@id='productDescription']")->item(0);

		foreach($el->getElementsByTagName('p') as $link)
		   $f_description .= "<p>" . $link->textContent . "</p>";

		//if(isset($f_description))
			//$f_description = utf8_encode($f_description);
	}
	*/

	/*
	// Lets grab the item dimensions and weight.
	$nextDimensions = FALSE;
	$nextWeight = FALSE;
	$p_dimensions = NULL;
	$p_dimension_raw = NULL;
	$p_dimensions_key = NULL;
	$p_weight = NULL;
	$p_weight_raw = NULL;
	$p_weight_key = NULL;
	//$p_ship_key = NULL;

	if($region == 0)
		$f_canada = FALSE;
	else
		$f_canada = TRUE;

	if($f_canada == TRUE) {
		if(isset($xml->getElementById('detail_bullets_id')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='detail_bullets_id']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('li') as $link) {
				$f_tmp = $link->textContent;

				if(strpos($f_tmp, 'Product Dimensions') !== false) {

					$f_w = explode('Product Dimensions:', $f_tmp);

					if(isset($f_w[1])) {
						$f_w2 = explode(";", $f_w[1]);

						if(isset($f_w2[0])) {
							$f_w3 = explode(" x ", $f_w2[0]);

							if(isset($f_w3[0]) && isset($f_w3[1]) && isset($f_w3[2])) {
								$p_dimensions['length'] = trim($f_w3[0]);
								$p_dimensions['width'] = trim($f_w3[1]);
								$p_dimensions['height'] = str_replace(" cm", "", trim($f_w3[2]));
							}
						}
					}
				}

				if(strpos($f_tmp, 'Shipping Weight') !== false) {
					$f_w = explode('Shipping Weight:', $f_tmp);

					if(isset($f_w[1])) {
						if(strpos($f_w[1], ' Kg') !== false) {
							$f_w2 = explode(" Kg", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
						}
						else {
							$f_w2 = explode(" g", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
								$p_weight = $p_weight * 0.001;
						}
					}
				}


				$i++;
			}
		}
	}
	else {
		if(isset($xml->getElementById('productDetails_detailBullets_sections1')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='productDetails_detailBullets_sections1']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('th') as $link) {
				$string = preg_replace('/\s+/', '', $link->textContent);

				if($string == "ProductDimensions")
					$p_dimensions_key = $i;

				if($string == "ItemWeight")
					$p_weight_key = $i;

				//if($string == "ShippingWeight")
					//$p_ship_key = $i;

			   $i++;
			}

			if(isset($p_dimensions_key))
				$p_dimensions_raw = trim($el->getElementsByTagName('td')[$p_dimensions_key]->textContent);

			// Priortize ship weight vs item weight?
			//if(isset($p_ship_key))
				//$p_weight_raw = trim($el->getElementsByTagName('td')[$p_ship_key]->textContent);
			if(isset($p_weight_key))
				$p_weight_raw = trim($el->getElementsByTagName('td')[$p_weight_key]->textContent);

			if(isset($p_dimensions_raw)) {
				$p_dimensions_raw = str_replace("x ", "", $p_dimensions_raw);
				$p_dimensions_raw = explode(" ", $p_dimensions_raw);

				foreach($p_dimensions_raw as $con_key => $dimen) {
					if($con_key == 0)
						$p_dimensions['length'] = $dimen * 2.54;

					if($con_key == 1)
						$p_dimensions['width'] = $dimen * 2.54;

					if($con_key == 2)
						$p_dimensions['height'] = $dimen * 2.54;
				}
			}

			if(isset($p_weight_raw)) {
				$p_weight_raw = explode(" ", $p_weight_raw);

				if(isset($p_weight_raw[1])) {
					if($p_weight_raw[1] == "pounds")
						$p_weight = $p_weight_raw[0] * 0.453592;
					else if($p_weight_raw[1] == "ounces")
						$p_weight = $p_weight_raw[0] * 0.0283495;
				}
			}

		}
	}
	*/
	/*
	// Lets grab the images.
	$image = NULL;

	$finder = new DomXPath($xml);
	$classname="fluid_images";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	$image_grab = NULL;
	$image = NULL;
    foreach($tmp_dom->getElementsByTagName('a') as $link) {
			$image[] = $link->getAttribute('href');

    }
	*/

    return Array("image" => $image, "f_bullets" => $f_bullets, "f_description" => $f_description, "f_dimensions" => $p_dimensions, "f_weight" => $p_weight, "f_name" => $f_name, "f_specs" => $f_table);
}

function get_links_amazon($html, $override = FALSE) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
    $xml->validateOnParse = true;
	$xml->loadHTML($html);

	$div = NULL;
	if(isset($xml->getElementById('noResultsTitle')->tagName))
		$div = $xml->getElementById('noResultsTitle')->tagName;

	$link = NULL;

	if(empty($div)) {
		$finder = new DomXPath($xml);
		$classname="a-text-center";
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

		$tmp_dom = new DOMDocument();
		foreach ($nodes as $node) {
			$tmp_dom->appendChild($tmp_dom->importNode($node,true));

			if($override == FALSE)
				break;
		}

		//Loop through each <a> tag in the dom and add it to the link array
		foreach($tmp_dom->getElementsByTagName('a') as $link) {
			//$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
			$link = $link->getAttribute('href');

			if($override == TRUE)
				if(isset($link))
					break;
		}
	}

    return $link;
}

function php_get_images_amazon($url, $region) {
	$f_cookie_file = "/var/www/f_cookie.txt";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	sleep(rand(10,20));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images_amazon($html, $region);
}

function php_process_images_amazon($html, $region) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);
	$xml->validateOnParse = true;

    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$f_name = NULL;
	// Lets grab the product name.
	if(isset($xml->getElementById('productTitle')->tagName)) {
		$el = $xml->getElementById('productTitle');

		$f_name = $el->textContent;
	}

	$f_bullets = NULL;
	// Remove unncessary data before grabbing the bullet points.
	if(isset($xml->getElementById('replacementPartsFitmentBullet')->tagName)) {
		$xpath = new DOMXPath($xml);
		$nlist = $xpath->query("//*[@id='replacementPartsFitmentBullet']");
		$node = $nlist->item(0);
		$node->parentNode->removeChild($node);
	}

	// Now grab bullet points.
	if(isset($xml->getElementById('feature-bullets')->tagName)) {
		$finder = new DomXPath($xml);
		$el = $finder->query("//*[@id='feature-bullets']")->item(0);

		foreach($el->getElementsByTagName('li') as $link) {
		   $f_bullets .= "<li>" . trim($link->textContent) . "</li>";
		}

		if(isset($f_bullets))
			$f_bullets = "<ul>" . trim($f_bullets) . "</ul>";
	}

	$f_description = NULL;
	// Lets grab the product description
	$finder = new DomXPath($xml);
	$classname="a-row feature";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	if(isset($tmp_dom->getElementById('productDescription')->tagName)) {
		$finder = new DomXPath($tmp_dom);
		$el = $finder->query("//*[@id='productDescription']")->item(0);

		foreach($el->getElementsByTagName('p') as $link)
		   $f_description .= "<p>" . $link->textContent . "</p>";

		//if(isset($f_description))
			//$f_description = utf8_encode($f_description);
	}


	// Lets grab the item dimensions and weight.
	$nextDimensions = FALSE;
	$nextWeight = FALSE;
	$p_dimensions = NULL;
	$p_dimension_raw = NULL;
	$p_dimensions_key = NULL;
	$p_weight = NULL;
	$p_weight_raw = NULL;
	$p_weight_key = NULL;
	//$p_ship_key = NULL;

	if($region == 0)
		$f_canada = FALSE;
	else
		$f_canada = TRUE;

	if($f_canada == TRUE) {
		if(isset($xml->getElementById('detail_bullets_id')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='detail_bullets_id']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('li') as $link) {
				$f_tmp = $link->textContent;

				if(strpos($f_tmp, 'Product Dimensions') !== false) {

					$f_w = explode('Product Dimensions:', $f_tmp);

					if(isset($f_w[1])) {
						$f_w2 = explode(";", $f_w[1]);

						if(isset($f_w2[0])) {
							$f_w3 = explode(" x ", $f_w2[0]);

							if(isset($f_w3[0]) && isset($f_w3[1]) && isset($f_w3[2])) {
								$p_dimensions['length'] = trim($f_w3[0]);
								$p_dimensions['width'] = trim($f_w3[1]);
								$p_dimensions['height'] = str_replace(" cm", "", trim($f_w3[2]));
							}
						}
					}
				}

				if(strpos($f_tmp, 'Shipping Weight') !== false) {
					$f_w = explode('Shipping Weight:', $f_tmp);

					if(isset($f_w[1])) {
						if(strpos($f_w[1], ' Kg') !== false) {
							$f_w2 = explode(" Kg", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
						}
						else {
							$f_w2 = explode(" g", $f_w[1]);

							if(isset($f_w2[0]) && isset($f_w2[1]))
								$p_weight = trim($f_w2[0]);
								$p_weight = $p_weight * 0.001;
						}
					}
				}


				$i++;
			}
		}
	}
	else {
		if(isset($xml->getElementById('productDetails_detailBullets_sections1')->tagName)) {
			$finder = new DomXPath($xml);
			$el = $finder->query("//*[@id='productDetails_detailBullets_sections1']")->item(0);

			$i = 0;
			foreach($el->getElementsByTagName('th') as $link) {
				$string = preg_replace('/\s+/', '', $link->textContent);

				if($string == "ProductDimensions")
					$p_dimensions_key = $i;

				if($string == "ItemWeight")
					$p_weight_key = $i;

				//if($string == "ShippingWeight")
					//$p_ship_key = $i;

			   $i++;
			}

			if(isset($p_dimensions_key))
				$p_dimensions_raw = trim($el->getElementsByTagName('td')[$p_dimensions_key]->textContent);

			// Priortize ship weight vs item weight?
			//if(isset($p_ship_key))
				//$p_weight_raw = trim($el->getElementsByTagName('td')[$p_ship_key]->textContent);
			if(isset($p_weight_key))
				$p_weight_raw = trim($el->getElementsByTagName('td')[$p_weight_key]->textContent);

			if(isset($p_dimensions_raw)) {
				$p_dimensions_raw = str_replace("x ", "", $p_dimensions_raw);
				$p_dimensions_raw = explode(" ", $p_dimensions_raw);

				foreach($p_dimensions_raw as $con_key => $dimen) {
					if($con_key == 0)
						$p_dimensions['length'] = $dimen * 2.54;

					if($con_key == 1)
						$p_dimensions['width'] = $dimen * 2.54;

					if($con_key == 2)
						$p_dimensions['height'] = $dimen * 2.54;
				}
			}

			if(isset($p_weight_raw)) {
				$p_weight_raw = explode(" ", $p_weight_raw);

				if(isset($p_weight_raw[1])) {
					if($p_weight_raw[1] == "pounds")
						$p_weight = $p_weight_raw[0] * 0.453592;
					else if($p_weight_raw[1] == "ounces")
						$p_weight = $p_weight_raw[0] * 0.0283495;
				}
			}

		}
	}

	// Lets grab the image. (First default image).
	$image = NULL;

	/*
	$finder = new DomXPath($xml);
	$classname="imgTagWrapper";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

	$image_grab = NULL;
    foreach($tmp_dom->getElementsByTagName('img') as $link) {
       $image_grab = $link->getAttribute('data-a-dynamic-image');
    }

	if(isset($image_grab)) {
		$data = json_decode($image_grab);

		$f_key = NULL;
		if(isset($data)) {
			foreach($data as $key => $f_data) {
				$f_key = $key;
				break;
			}

			$f_image = explode("_", $f_key);

			if(count($f_image) > 1) {
				$image_tmp = $f_image[0];
				$image_tmp .= str_replace(".", "", end($f_image));
				$image[] = $image_tmp;
			}
			else
				$image[] = $f_key;
		}
	}
	*/
	// Lets grab the images.
	/*
	if(isset($xml->getElementById('main-image-container')->tagName)) {
		$finder = new DomXPath($xml);
		$el = $finder->query("//*[@id='main-image-container']")->item(0);

		foreach($el->getElementsByTagName('img') as $link) {
			echo "<pre>";
				print_r($link);
			echo "</pre>";

			if(!preg_match('/\b(\w*play-button-overlay\w*)\b/', $link->attributes->getNamedItem("src")->value)) {
				//$string = preg_replace('/\s+/', '', $link->textContent);
				$f_image = explode("_", $link->attributes->getNamedItem("src")->value);

				if(count($f_image) > 1) {
					$image_tmp = $f_image[0];
					$image_tmp .= str_replace(".", "", end($f_image));
					$image[] = $image_tmp;
				}
				else
					$image[] = $link->attributes->getNamedItem("src")->value;
			}
		}
	}
	*/

	$finder = new DomXPath($xml);
	// --> find all the script elements in the page
	$scripts = $finder->query("//script");

	foreach ($scripts as $s) {

		// see if there are any matches for var datePickerDate in the script node's contents
		//if(preg_match('#var data = "(.*?)"#', $s->nodeValue, $matches)) {
		//if(preg_match('/\b(\w*var data\w*)\b/', $s->nodeValue)) {
		if(preg_match('/\b(\w*colorImages\w*)\b/', $s->nodeValue)) {
			// the date itself (captured in brackets) is in $matches[1]
			// http://local.leosadmin.com/fluid.loader.php?load=true&function=php_image_downloader&data=eyJpdGVtcyI6eyIyMDkxIjp7InBfaWQiOiIyMDkxIiwicF9jYXRpZCI6IjEiLCJwX2VuYWJsZSI6IjEifX0sIm9wdGlvbnMiOiIxIiwicmVnaW9uIjoiMCIsIm92ZXJyaWRlIjoiMCIsImVkaXRvcnR5cGUiOiIxIn0=
			//http://local.leosadmin.com/fluid.loader.php?load=true&function=php_image_downloader&data=eyJpdGVtcyI6eyIyMDkzIjp7InBfaWQiOiIyMDkzIiwicF9jYXRpZCI6IjEiLCJwX2VuYWJsZSI6IjEifX0sIm9wdGlvbnMiOiIxIiwicmVnaW9uIjoiMCIsIm92ZXJyaWRlIjoiMCIsImVkaXRvcnR5cGUiOiIxIn0=

			$f_explode = explode("'colorImages': ", $s->nodeValue);

			if(isset($f_explode[1]))
				$f_explode_2 = explode("'colorToAsin':", $f_explode[1]);

			if(isset($f_explode_2[0]))
				$f_explode_3 = explode("{ 'initial': [", $f_explode_2[0]);

			//if(isset($f_explode_3[1]))
				//$f_explode_4 = explode("]},", $f_explode_3[1]);

			//if(isset($f_explode_4[0]))
				//$f_explode_5 = explode('"', $f_explode_4[0]);

			if(isset($f_explode_3[1]))
				$f_explode_5 = explode('{"hiRes":"', $f_explode_3[1]);


			//echo "<pre>";
				//print_r($f_explode_3[1]);
			//echo "</pre>";

			//$tmp = preg_replace('/[\[{\(].*[\]}\)]/U' , '', $name);
			//$tmp = preg_replace("/\[[^)]+\]/","",$tmp);
			//$tmp = '{"hiRes":null,"thumb":"https://images-na.ssl-images-amazon.com/images/I/41IJ9Q4nHcL._SS40_.jpg","large":"https://images-na.ssl-images-amazon.com/images/I/41IJ9Q4nHcL.jpg",:[220,355]}';

			$tmp = explode(',"main":', $f_explode_3[1]);

			$f_tmp = NULL;
			foreach($tmp as $key) {
				$f = explode('{"hiRes":', $key);

				if(isset($f[1])) {
					$f_tmp[]= '{"hiRes":' . $f[1] . "}";

				}
			}

			$f_data = NULL;
			foreach($f_tmp as $key) {
				$f_data[] = json_decode($key);
			}

			foreach($f_data as $key) {
				$link = NULL;

				if(!empty($key->hiRes))
					$link = $key->hiRes;
				else if(!empty($key->large))
					$link = $key->large;
				else if(!empty($key->thumb))
					$link = $key->thumb;

				if(preg_match('/\b(\w*.jpg\w*)\b/', $link)) {
					$f_image = explode("_", $link);

					if(count($f_image) > 1) {
						$image_tmp = $f_image[0];
						$image_tmp .= str_replace(".", "", end($f_image));
						$image[$image_tmp] = $image_tmp;
					}
					else
						$image[$link] = $link;
				}
			}

			/*
			echo "<pre>";
				print_r($tmp);
			echo "</pre>";

			$tmp = $tmp[0] . "}";

			$tmp = str_replace("}]},", "}", $tmp);
			$tmp = str_replace(":[", ',"', $tmp);
			$tmp = str_replace("]", '":""', $tmp);

			echo "<pre>";
				print_r($tmp);
			echo "</pre>";
			*/
			/*
						echo "<pre>";
							print_r(json_decode($tmp));
			//				print_r($f_explode_5);
						echo "</pre>";
			*/
			/*
			if(isset($f_explode_5)) {
				foreach($f_explode_5 as $f_tmp) {

					$f_explode_6 = explode('"', $f_tmp);

					if(isset($f_explode_6[0])) {
						if(preg_match('/\b(\w*.jpg\w*)\b/', $f_explode_6[0])) {
							$f_image = explode("_", $f_explode_6[0]);

							if(count($f_image) > 1) {
								$image_tmp = $f_image[0];
								$image_tmp .= str_replace(".", "", end($f_image));
								$image[$image_tmp] = $image_tmp;
							}
							else
								$image[$f_explode_6[0]] = $f_explode_6[0];
						}
						else {
							// --> Didn't find a high res image, lets grab the "large" image instead which will be smaller.
							$f_explode_7 = explode('"large":"', $f_tmp);

							if(isset($f_explode_7[1])) {
								$f_explode_8 = explode('"', $f_explode_7[1]);

								if(preg_match('/\b(\w*.jpg\w*)\b/', $f_explode_8[0])) {
									$f_image = explode("_", $f_explode_8[0]);

									if(count($f_image) > 1) {
										$image_tmp = $f_image[0];
										$image_tmp .= str_replace(".", "", end($f_image));
										$image[$image_tmp] = $image_tmp;
									}
									else
										$image[$f_explode_8[0]] = $f_explode_8[0];

								}
							}
						}
					}
				}
			}
*/
			break;
		}
	}

    return Array("image" => $image, "f_specs" => NULL, "f_bullets" => $f_bullets, "f_description" => $f_description, "f_dimensions" => $p_dimensions, "f_weight" => $p_weight, "f_name" => $f_name);
}

function php_process_images_panasonic($html, $region) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);
	$xml->validateOnParse = true;

    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$f_name = NULL;
	// Lets grab the product name.
	/*
	if(isset($xml->getElementById('productTitle')->tagName)) {
		$el = $xml->getElementById('productTitle');

		$f_name = $el->textContent;
	}
	*/
	$finder = new DomXPath($xml);
	$classname="pdp-prod-name";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
	foreach ($nodes as $node) {
		$f_name = $node->nodeValue;
		break;
	}

	$f_bullets = NULL;
	// Remove unncessary data before grabbing the bullet points.
	$finder = new DomXPath($xml);
	$classname="feature-content";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$f_points = NULL;
	foreach ($nodes as $node) {
		$f_points = $xml->saveHTML($node);
		$f_points = str_replace('<span class="feature-content">', '', $f_points);
		$f_points = str_replace("</span>", "", $f_points);
		break;
	}

	$f_bullets = $f_points;


	// http://local.leosadmin.com/fluid.loader.php?load=true&function=php_image_downloader&data=eyJpdGVtcyI6eyI4OTU2Ijp7InBfaWQiOiI4OTU2IiwicF9jYXRpZCI6IjExMCIsInBfZW5hYmxlIjoiMSJ9fSwib3B0aW9ucyI6IjAiLCJyZWdpb24iOiI1Iiwib3ZlcnJpZGUiOiIwIiwiZWRpdG9ydHlwZSI6IjAifQ==
		// local.leosadmin.com/fluid.loader.php?load=true&function=php_image_downloader&data=eyJpdGVtcyI6eyI5MDYwIjp7InBfaWQiOiI5MDYwIiwicF9jYXRpZCI6IjExMCIsInBfZW5hYmxlIjoiMSJ9fSwib3B0aW9ucyI6IjAiLCJyZWdpb24iOiI1Iiwib3ZlcnJpZGUiOiIwIiwiZWRpdG9ydHlwZSI6IjAifQ==


	// Lets grab the image. (First default image).
	$image = NULL;

	$finder = new DomXPath($xml);
	$el = $finder->query("//*[@id='pdp-aleternative-images']")->item(0);
	$image_grab = NULL;
	if(isset($el)) {
		foreach($el->getElementsByTagName('img') as $link) {
			$image_grab[] = $link->getAttribute('data-lgimg');
		}
	}

	foreach($image_grab as $fd_img) {
		$fd_img_ar = json_decode($fd_img);

		if(isset($fd_img_ar)) {
			if($fd_img_ar->is_video == 0) {
				if(isset($fd_img_ar->hires))
					$image[] = $fd_img_ar->hires;
				else if(isset($fd_img_ar->url))
					$image[] = $fd_img_ar->url;
			}
		}
	}

    return Array("image" => $image, "f_specs" => NULL, "f_bullets" => $f_bullets, "f_description" => NULL, "f_dimensions" => NULL, "f_weight" => NULL, "f_name" => $f_name);
}

function php_search_adorama($url) {
	//$url = "https://www.adorama.com/searchsite/default.aspx?searchinfo=16519247";
	//$url = "https://www.adorama.com/searchsite/default.aspx?searchinfo=manfrotto+186";
	//$rand_keys = array_rand($user_agents, 2);
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	//$user_agent = $user_agents[$rand_keys[0]];
	$f_cookie_file_adorama = "/var/www/f_cookie_ad.txt";

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_COOKIESESSION, true);
	curl_setopt($c, CURLOPT_COOKIEFILE, $f_cookie_file_adorama);
	curl_setopt($c, CURLOPT_COOKIEJAR, $f_cookie_file_adorama);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	$link = get_links($html);

	$image_link = NULL;

	// We were redirected to a product page.
	if($link == NULL) {
		$image_link = php_get_redirect_link($url);
		//$link = php_get_redirect_link($html);

		/*
		if($link != NULL)
			$link = "https://www.adorama.com" . $link;
		*/

		//$image_link = php_get_images($link);
	}
	else
		$image_link = php_get_images($link);

	$f_image_data = NULL;

	if($image_link != NULL) {
		$tmp_name = explode("/", $image_link);
		$org_name = end($tmp_name);
		$extension_tmp = explode(".", $org_name);
		$extension = end($extension_tmp);

		copy($image_link, FOLDER_IMAGES_TEMP . $org_name);

		$rand = substr(str_shuffle(md5(time())),0,30);

		$f_image['name'] = $org_name;
		$f_image['type'] = mime_content_type(FOLDER_IMAGES_TEMP . $org_name);
		$f_image['tmp_name'] = $org_name;
		$f_image['error'] = 0;
		$f_image['size'] = filesize(FOLDER_IMAGES_TEMP . $org_name);
		$f_image['extension'] = $extension;
		$f_image['rand'] = $rand;
		$f_image['fullpath'] = FOLDER_IMAGES . $rand . "." . $extension;
		$f_image['image'] = $rand . "." . $extension;
		$f_image['noerror'] = rename(FOLDER_IMAGES_TEMP . $org_name, FOLDER_IMAGES . $rand . "." . $extension);

		$f_image_data[$rand]['file'] = $f_image;
	}

	return $f_image_data;
}

function get_links($html) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$finder = new DomXPath($xml);
	$classname="item-img";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

    $link = NULL;

    //Loop through each <a> tag in the dom and add it to the link array
    foreach($tmp_dom->getElementsByTagName('a') as $link) {
        //$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
        $link = $link->getAttribute('href');
    }

    return $link;
}

function php_get_redirect_link($url) {
	/*
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();
	libxml_use_internal_errors(true);
    // Load the url's contents into the DOM
	$xml->loadHTML($html);

    $link = NULL;

    //Loop through each <a> tag in the dom and add it to the link array
    foreach($xml->getElementsByTagName('a') as $link) {
        //$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
        $link = $link->getAttribute('href');
    }

    return $link;
    */
	//$url = "https://www.adorama.com/searchsite/default.aspx?searchinfo=manfrotto+186";

	//$user_agent = "Googlebot/2.1 (+http://www.google.com/bot.html)";
	//$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	//$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0";
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	//$rand_keys = array_rand($user_agents, 2);
	//$user_agent = $user_agents[$rand_keys[0]];
	sleep(rand(10,20));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images($html);
}

function php_get_images($url) {
	//$url = "https://www.adorama.com/ifjxt2.html";
	//$url = "https://www.adorama.com/BG3357.html";
	//$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	//$user_agent = "Googlebot/2.1 (+http://www.google.com/bot.html)";

	//$rand_keys = array_rand($user_agents, 2);
	//$user_agent = $user_agents[$rand_keys[0]];
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30";
	sleep(rand(10,20));

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

	$html = curl_exec($c);

	if (curl_error($c))
		die(curl_error($c));

	// Get the status code
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	curl_close($c);

	return php_process_images($html);
}

function php_process_images($html) {
    // Create a new DOM Document to hold our webpage structure
    $xml = new DOMDocument();

	libxml_use_internal_errors(true);

    // Load the url's contents into the DOM
	$xml->loadHTML($html);

	$finder = new DomXPath($xml);
	$classname="largeImage productImage";
	$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

	$tmp_dom = new DOMDocument();
	foreach ($nodes as $node) {
		$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		break;
	}

    $image = NULL;

    //Loop through each <a> tag in the dom and add it to the link array
    foreach($tmp_dom->getElementsByTagName('img') as $link) {
       $image = urldecode($link->getAttribute('src'));
    }

    return $image;
}

?>
