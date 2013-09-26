<?php
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
/*
##########################################################################
Loaded 7 Automation Script
Author	Ravish Sharma
Date	28-August-2013
##########################################################################
*/
//*********************** CONFIGURATION VARIABLES	**********************

if (File_exists(__DIR__ . '/config.php')) {
  @require(__DIR__ . '/config.php');
}

$table_prefix = 'lc_';
define('CATEGORIES',  $table_prefix . "categories");
define('CATEGORIES_DESCRIPTION',  $table_prefix . "categories_description");
define('PRODUCTS',  $table_prefix . "products");
define('PRODUCTS_PRICES',  $table_prefix . "products_pricing");
define('PRODUCTS_CATEGORIES',  $table_prefix . "products_to_categories");
define('PRODUCTS_DESCRIPTIONS',  $table_prefix . "products_description");
define('IMAGES',  $table_prefix . "products_images");
define('IMAGES_LINKS',  $table_prefix . "products_images_groups");
define('TAX_DESCRIPTIONS',  $table_prefix . "tax_class");

$PassKey="02446";//This variable is used for authentication of xml file

//**** Size of products_model in products table ****
// set this to the size of your model number field in the db.  We check to make sure all models are no longer than this value.
global $modelsize;
$modelsize = 64;

//*******************	 END CONFIGURATION VARIABLES	******************************

//***********************	 START INITIALIZATION	********************
global $filelayout, $filelayout_count, $filelayout_sql, $langcode, $fileheaders, $tax_id;

// these are the fields that will be defaulted to the current values in the database if they are not found in the incoming file
global $default_these;
$default_these = array(
	'v_products_image',
	'v_categories_id',
	'v_products_price',
	'v_products_quantity',
	'v_products_weight',
	'v_date_avail',
	'v_instock',
	'v_tax_class_title',
	'v_manufacturers_name',
	'v_manufacturers_id',
	'v_products_dim_type',
	'v_products_length',
	'v_products_width',
	'v_products_height',
	'v_products_thumbnail'
	);

// making connection with the database.
	$dbhandle = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD)
    or die("Unable to connect to MySQL");
    $db_selected = mysql_select_db(DB_DATABASE,$dbhandle);	
    
//Get the tax class id
	$tax = $_GET["tax"];
	$query = "SELECT tax_class_id FROM  ".TAX_DESCRIPTIONS." where tax_class_id = '".$tax."'";
	
	$result = mysql_query($query);
	$tax_id = @mysql_result($result,0);
	
	if($tax_id == ""){
		echo "Check tax class";
		exit(0);
	}
    
//************************* END INITIALIZATION	**************************
if(isset($_POST['XML_INPUT_VALUE']))
{
	$arr_xml = xml2php($_POST['XML_INPUT_VALUE']);
	requestType($arr_xml,$PassKey);
}else{
	echo "Xml request data needed to process this file";
	die();
}

function xml2php($xml_content) 
{
	$xml_parser = xml_parser_create();
	xml_parse_into_struct($xml_parser, $xml_content, $arr_vals);
	if(xml_get_error_code($xml_parser)!=false)
	{
		$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>";
		$xmlstr.="Error : ".xml_error_string(xml_get_error_code($xml_parser))." At Line No :  ".xml_get_current_line_number($xml_parser);
		$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
		output_xml($xmlstr);
		exit;
	}
	xml_parser_free($xml_parser);
	return $arr_vals;
}

function requestType($array_haystack,$PassKey) 
{
	if ((!empty($array_haystack)) AND (is_array($array_haystack))) 
	{
		foreach ($array_haystack as $xml_key => $xml_value) 
		{
			//for Ping
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="ping")
			{
				$type="Checking for test database connection";
				$cat=strtolower($xml_value["value"]);
			}
			//For Product listing
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="getproducts")
			{
				$type="Display Product Listing";
				$cat=strtolower($xml_value["value"]);
			}

			//For Product import
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="productsimport")
			{
				$type="Import product to database";
				$cat=strtolower($xml_value["value"]);
			}
			
			// Added for SKU prefix
			if(strtolower($xml_value["tag"])=="skuprefix")
			{
				$skuPrefixVal = strtoupper($xml_value["value"]);
			}
			
			if(strtolower($xml_value["tag"])=="passkey")
			{
				$entered_key=strtolower($xml_value["value"]);
				break;
			}
		}
	}

	//This section checks if entered key in xml file is valid
	if($entered_key!=$PassKey)
	{
		echo "<br>Error...invalid Key";
		exit;
	}
	switch($cat)
	{
		case "ping":
				{
				Ping();
				break;
				}
		case "getproducts":
				{
				GetProd($skuPrefixVal);
				break;		
				}
		case "productsimport":
				{
				ImportProduct($array_haystack);
				break;		
				}
	}
}

function output_xml($content) 
{
	header("Content-Type: application/xml; charset=ISO-8859-1");
	header("Expires: Mon, 26 Jul 2013 05:00:00 GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	print $content;
	
}

function GetProd($skuPrefixVal)
{
	$likeSql = "";
	if(isset($skuPrefixVal) && $skuPrefixVal != "")
		$likeSql = " and p.products_model like '".$skuPrefixVal."%'";

	$xml_str = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
		
	$sql_prod = "SELECT p.products_model as v_products_model, i.image as v_products_image, p.products_price as v_products_price, p.products_quantity as v_products_quantity, p.products_status as status
	FROM ".PRODUCTS." AS p LEFT JOIN ".IMAGES." AS i ON i.products_id = p.products_id WHERE p.products_status = 1 ".$likeSql;
	
	$result = mysql_query($sql_prod);
	
	if($result === FALSE) {
		die(mysql_error()); // TODO: better error handling
	}

	$xml_str .= "<xmlPopulate>\n";
	$xml_str .= "<xmlGetProductsResponse>\n";
	$count=0;
	$v_products_image = "&amp;";
	
	 while ($rowprod=mysql_fetch_array($result))
	{//echo "test";
		$v_products_image = "&amp;";
		if($rowprod['v_products_image'] != ""){
			$v_products_image = $rowprod['v_products_image'];
		}
		
		$xml_str .= "<xmlProduct>\n<v_products_model><![CDATA[".$rowprod['v_products_model']."]]></v_products_model>\n";
		$xml_str .= "<v_products_image><![CDATA[".$v_products_image."]]></v_products_image>\n";
		$xml_str .= "<v_products_quantity><![CDATA[".$rowprod['v_products_quantity']."]]></v_products_quantity>\n";
		$xml_str .= "<v_products_status><![CDATA[".$rowprod['status']."]]></v_products_status>\n";
		$xml_str .= "<v_products_price><![CDATA[".$rowprod['v_products_price']."]]></v_products_price>\n";
		$xml_str .= "</xmlProduct>\n";
		
		$count++;
	}
	$xml_str.="</xmlGetProductsResponse>\n</xmlPopulate>";
	output_xml($xml_str);
}

function Ping()
{
	$xml_str = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
	$xml_str.="<xmlPopulateResponce>\n";
	$Ping_res=mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);

	if(!$Ping_res)
		$xml_str.="<heartbeat>error</heartbeat>\n";
	else
		$xml_str.="<heartbeat>alive</heartbeat>\n";
	$xml_str.="</xmlPopulateResponce>";
	
	output_xml($xml_str);
}

function ImportProduct($array_haystack) 
{
	$itemArr=array();
	
	global $xmlstr;
	global $AllowedTags;
	$AllowedTags=array();
	$AllTags=array("v_products_model","v_products_image","v_products_name_1","v_products_description_1","v_products_price",
	"v_products_weight","v_products_quantity","v_manufacturers_name","v_categories_name_1","v_categories_name_2",
	"v_categories_name_3","v_status","v_products_thumbnail","v_products_length","v_products_width","v_products_height", "v_products_msrp");

	foreach($array_haystack as $xml_key => $xml_value) 
	{
		if(in_array(strtolower($xml_value["tag"]),$AllTags))
		{
			if(!in_array(strtolower($xml_value["tag"]), $AllowedTags))
			{
				$AllowedTags[]=strtolower($xml_value["tag"]);
			}

		}
	}
	
	$SizeArr=sizeof($AllowedTags);
	if(!$AllowedTags |$SizeArr<=0)
	{
		$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>";
		$xmlstr.="Error";
		$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
		output_xml($xmlstr);
		exit;
	}

	if(in_array("v_products_model",$AllowedTags)==false)
	{
		$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>";
		$xmlstr.="Error-Missing Model Number";
		$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
		output_xml($xmlstr);
		exit;
	}
	
	// make single dimensional array for all the XML values.
	if ((!empty($array_haystack)) AND (is_array($array_haystack))) 
	{
		$i=0;
		foreach($array_haystack as $xml_key => $xml_value) 
		{
			if(in_array(strtolower($xml_value["tag"]),$AllowedTags))
			{
				if($i != 0 && strtolower($xml_value["tag"]) == 'v_products_model')
				{
					$itemArr[$i]='zzzzzPRODUCTBREAKzzzzz';
					$i++;
					$itemArr[$i]=$xml_value["value"];
					$i++;
				}else{
					$itemArr[$i]=$xml_value["value"];
					$i++;
				}
			}
		}
	}

	// make 2D array for all the XML values with product wise information.
	$i=0; $j=0;
	foreach($itemArr as $xml_value) 
	{
		if( $xml_value != 'zzzzzPRODUCTBREAKzzzzz')
		{
			$arrnew[$i][$j] = $xml_value;
			$j++;
			continue;
		}
		$j = 0;
		$i++;
	}
	
	$lll = 0;
	global $filelayout;
	$filelayout = array();
	foreach( $AllowedTags as $header )
	{
		$cleanheader = str_replace( '"', '', $header);
		$filelayout[$cleanheader] = $lll++;
	}

	$newreaded = "";
	$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>\n";
	for($v=0;$v<sizeof($arrnew);$v++)
	{
		$TempArr=array();
		foreach($arrnew[$v] as $bb)
		{
			$TempArr[]=$bb;
		}
		walk($TempArr);
	}
	$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
	output_xml($xmlstr);
}

function walk( $items ) {
	global $filelayout, $filelayout_count, $modelsize;
	global $langcode, $default_these;
    global $epdlanguage_id, $v_products_id1;
	global $separator, $max_categories ;
	global $AllowedTags;
	global $xmlstr;
	global $tax_id;
	global $date;
	$date = date('Y-m-d H:i:s');
	
	$xmlstr.="<xmlProductImport>";
	
	$totalTags = count($items);
	
	// make sure all non-set things are set to '';
	// and strip the quotes from the start and end of the stings.
	// escape any special chars for the database.
	foreach( $filelayout as $key=> $value){
		$i = $filelayout[$key];
		if (isset($items[$i]) == false) {
			$items[$i]='';
		} else {
			$items[$i] = $items[$i];
			// Check to see if either of the magic_quotes are turned on or off;
			// And apply filtering accordingly.
			if (function_exists('ini_get')) {
				//echo "Getting ready to check magic quotes<br>";
				if (ini_get('magic_quotes_runtime') == 1){
					// The magic_quotes_runtime are on, so lets account for them
					// now any remaining doubled double quotes should be converted to one doublequote
					$items[$i] = str_replace('"',"\"",$items[$i]);
					$items[$i] = str_replace('\"\"',"\"",$items[$i]);

					// now replace all singlequoates into doubled singlequates for database
					$items[$i] = str_replace("\'","''",$items[$i]);
				} else { // no magic_quotes are on
					// now any remaining doubled double quotes should be converted to one doublequote
					$items[$i] = str_replace('""',"\"",$items[$i]);
					// now replace all singlequoates into doubled singlequates for database
					$items[$i] = str_replace("\'","'",$items[$i]);
					$items[$i] = str_replace("'","''",$items[$i]);
				}
			}
		}
	}
	
	// this is an important loop.  What it does is go thru all the fields in the incoming file and set the internal vars.
	// Internal vars not set here are either set in the loop above for existing records, or not set at all (null values)
	// the array values are handled separatly, although they will set variables in this loop, we won't use them.
	foreach( $filelayout as $key => $value ){
		$$key = $items[ $value ];
		//echo $$key;
	}
	
	if (strlen($v_products_model) > $modelsize ){
		echo "<font color='red'>" . strlen($v_products_model) . $v_products_model . "... ERROR! - Too many characters in the model number.<br>
		12 is the maximum on a standard OSC install.<br>
		Your maximum product_model length is set to $modelsize<br>
		You can either shorten your model numbers or increase the size of the field in the database.</font>";
		die();
	}

	if ($v_products_model != "") {
		//Comment below line if client want MSRP module on his store
		$v_products_msrp = $v_products_price;
		
		//products_model exists!
		$xmlstr.="  <v_products_model>".$v_products_model."</v_products_model>\n";
	
		$selectSkus = "SELECT products_id FROM ".PRODUCTS." where products_model = '".$v_products_model."'";
		$resultSku = mysql_query($selectSkus);
		$products_id_image = 0;
		if($totalTags > 9){
			if ($resultSku == "" || mysql_num_rows($resultSku) == 0){
				//New product section
				$xmlstr.="<v_status>NEW</v_status>\n";
				/*
				//Get product ID
				$sql = "SHOW TABLE STATUS LIKE '".PRODUCTS."' ";
				$result = mysql_query($sql);
				$row =  mysql_fetch_array($result);
				$max_products_id = $row['Auto_increment'];
				if (!is_numeric($max_products_id) ){
					$max_products_id=1;
				}
				$productid = $max_products_id;
				$products_id_image = $productid; */
				// insert product details
				$query = "INSERT INTO ".PRODUCTS." (`products_model`, `products_weight`, `products_weight_class`,
				 `products_price`, `products_quantity`, `products_date_added`, `products_last_modified`, `products_status`,
				 `products_tax_class_id`, `has_children`) VALUES
				('".$v_products_model."','".$v_products_weight."', 4,
				 '".$v_products_msrp."', ".$v_products_quantity.", now(), now(), 1, 1, 0)";
				$myresult = mysql_query($query);
				$productid = mysql_insert_id();
				$products_id_image = $productid;
				$product_name = $v_products_name_1;
				$product_url = $name = str_replace(' ', '_', $product_name);
				$product_url = strtolower($product_url);

				if($v_manufacturers_name != "")
					$product_name = $v_manufacturers_name." - ".$v_products_name_1;
				$products_keyword = $product_url."_".$v_products_model;
				$query = "INSERT INTO ".PRODUCTS_DESCRIPTIONS." (`products_id`, `language_id`, `products_name`, 
				`products_description`, `products_keyword`, `products_tags`, `products_url`,
				`products_meta_title`, `products_meta_keywords`, `products_meta_description`) VALUES
				(".$productid.", 1, '".$product_name."', 
				'".$v_products_description_1."', '".$products_keyword."', '', '".$product_url."',
				'".$product_name."', '".$product_name."', '".$v_products_description_short."')";
				//echo "<br/><br/>".$query;
			
				$myresult = mysql_query($query);
			
				//Get priceID
/*				$sql = "SHOW TABLE STATUS LIKE '".PRODUCTS_PRICES."' ";
				$result = mysql_query($sql);
				$row =  mysql_fetch_array($result);
				$max_priceid = $row['Auto_increment'];
				if (!is_numeric($max_priceid) ){
					$max_priceid=1;
				}
				$priceid = $max_priceid; */
				//insert product pricing details
//				$query = "INSERT INTO ".PRODUCTS_PRICES." (`products_id`,`price`, `percentage_discount`, `lower_limit`, `usergroup_id`) VALUES
//					(".$productid.",".$v_products_price.", 0, 1, 0)";
				//echo "<br/><br/>".$query;
//				mysql_query($query);

				//Create Product Category Mapping
				categoryLookup(trim($v_categories_name_1),trim($v_categories_name_2),trim($v_categories_name_3),$productid);
			} else {
				$rowSku =  mysql_fetch_array($resultSku);
				$update_pid = $rowSku['products_id'];
				$products_id_image = $update_pid;
				
				$xmlstr.=" <v_status>UPDATE</v_status>\n";

				$query = "update ".PRODUCTS." 
				set products_quantity = ".$v_products_quantity.", products_price = '".$v_products_msrp."', 
				products_tax_class_id = 1 where products_id = ".$update_pid." ";
				//echo "<br/><br/>".$query; 
				mysql_query($query);
				
				$product_name = $v_products_name_1;
				if($v_manufacturers_name != "")
					$product_name = $v_manufacturers_name." - ".$v_products_name_1;
				
				$query = "update ".PRODUCTS_DESCRIPTIONS." 
				set products_name = '".$product_name."', products_meta_description = '".$v_products_description_short."', 
				products_description =  '".$v_products_description_1."' 
				products_meta_title =  '".$product_name."' 
				where products_id = ".$update_pid." ";
				//echo "<br/><br/>".$query;
				mysql_query($query);
				
//				$query = "update ".PRODUCTS_PRICES." set price = ".$v_products_price." where products_id = ".$update_pid." ";
				//echo "<br/><br/>".$query;
//				mysql_query($query);
				
				//Update Category Mapping
				categoryLookup($v_categories_name_1,$v_categories_name_2,$v_categories_name_3,$update_pid);
			}
		}else{ // in case of Partial updates
			$rowSku =  mysql_fetch_array($resultSku);
			$update_pid = $rowSku['products_id'];
			$products_id_image = $update_pid;
			$xmlstr.=" <v_status>UPDATE</v_status>\n";
			
			//Update Product Attributs
			$query = "update ".PRODUCTS." set products_quantity = ".$v_products_quantity.", products_price = '".$v_products_msrp."' where products_id = ".$update_pid." ";
			//echo "<br/><br/>".$query; 
			mysql_query($query);

			//$query = "update ".PRODUCTS_PRICES." set price = ".$v_products_price." where products_id = ".$update_pid." ";
			//echo "<br/><br/>".$query;
			//mysql_query($query);
		}// End of number of Tags IF condition
		
		//Download image to CS-Cart
		if($v_products_image != "" && $products_id_image > 0){
			$imageidP = 0;
			$selectImage = "SELECT id FROM ".IMAGES." where products_id = '".$products_id_image."'";
			$imageID_result = mysql_query($selectImage);
			if ($imageID_result == "" || mysql_num_rows($imageID_result)==0){
				//Get imageidP
				$sql = "SHOW TABLE STATUS LIKE '".IMAGES."' ";
				$result = mysql_query($sql);
				$row =  mysql_fetch_array($result);
				$max_imageidP = $row['Auto_increment'];
				if (!is_numeric($max_imageidP) ){
					$max_imageidP=1;
				}
				$imageidP = $max_imageidP;
				$query = "INSERT INTO ".IMAGES." (`id`, `products_id`, `image`, `default_flag`, `sort_order`, `date_added`) VALUES
				(".$imageidP.", ".$productid.", '".basename($v_products_image)."', 1, 0, , now())";
				//echo "<br/><br/>".$query;
				mysql_query($query);

				//$query = "INSERT INTO ".IMAGES_LINKS." (`image_id`, `object_id`, `object_type`, `type`, `detailed_id`) VALUES
				//(".$imageidP.", ".$productid.", 'product', 'M',".$imageidP.")";
				//echo "<br/><br/>".$query;
				//mysql_query($query);
			}else{
				$row =  mysql_fetch_array($imageID_result);
				$imageidP = $row['id'];
				$query = "update ".IMAGES." set image = '".basename($v_products_image)."' where id = ".$imageidP." ";
				//echo "<br/><br/>".$query;
				mysql_query($query);
			}
			
			$imageDir = $imageidP;
			if(strlen($imageidP) > 3)
				$imageDir = substr($imageidP, 0, -3);

			$thumbnanilsPath = "images/products/thumbnails/???/";
			$product_infoPath = "images/products/product_info/???/";
			$popupPath = "images/products/popup/???/";
			$originalsPath = "images/products/originals/???/";
			$miniPath = "images/products/mini/???/";
			$largePath = "images/products/large/???/";
			
			$thumbnanilsPath = str_replace("???", $imageDir, $thumbnanilsPath);
			$product_infoPath = str_replace("???", $imageDir, $product_infoPath);
			$popupPath = str_replace("???", $imageDir, $popupPath);
			$originalsPath = str_replace("???", $imageDir, $originalsPath);
			$miniPath = str_replace("???", $imageDir, $miniPath);
			$largePath = str_replace("???", $imageDir, $largePath);
			
			$dirs = array_filter(glob('images/products/thumbnails/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/thumbnails/'.$imageDir.'/', 0777, true);
				chmod('images/products/thumbnails/'.$imageDir.'/', 0777);
				
				mkdir('images/products/thumbnails/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/thumbnails/'.$imageDir.'/180/', 0777);
			}

			$dirs = array_filter(glob('images/products/product_info/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/product_info/'.$imageDir.'/', 0777, true);
				chmod('images/products/product_info/'.$imageDir.'/', 0777);
				
				mkdir('images/products/product_info/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/product_info/'.$imageDir.'/180/', 0777);
			}

			$dirs = array_filter(glob('images/products/popup/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/popup/'.$imageDir.'/', 0777, true);
				chmod('images/products/popup/'.$imageDir.'/', 0777);
				
				mkdir('images/products/popup/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/popup/'.$imageDir.'/180/', 0777);
			}

			$dirs = array_filter(glob('images/products/originals/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/originals/'.$imageDir.'/', 0777, true);
				chmod('images/products/originals/'.$imageDir.'/', 0777);
				
				mkdir('images/products/originals/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/originals/'.$imageDir.'/180/', 0777);
			}

			$dirs = array_filter(glob('images/products/mini/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/mini/'.$imageDir.'/', 0777, true);
				chmod('images/products/mini/'.$imageDir.'/', 0777);
				
				mkdir('images/products/mini/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/mini/'.$imageDir.'/180/', 0777);
			}

			$dirs = array_filter(glob('images/products/large/'.$imageDir.'/'), 'is_dir');
			//print_r( $dirs);
			$size = sizeof($dirs);
			$lastDir = (int)$size - 1;
			if($lastDir < 0){
				$lastDir = 0;
				mkdir('images/products/large/'.$imageDir.'/', 0777, true);
				chmod('images/products/large/'.$imageDir.'/', 0777);
				
				mkdir('images/products/large/'.$imageDir.'/180/', 0777, true);
				chmod('images/products/large/'.$imageDir.'/180/', 0777);
			}

			$location = $thumbnanilsPath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
			
			$location = $product_infoPath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
			
			$location = $popupPath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
			
			$location = $originalsPath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
			
			$location = $miniPath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
			
			$location = $largePath.basename($v_products_image);
			$file = file_get_contents($v_products_image);
			file_put_contents($location,$file);
		}
	} else {
		// this record was missing the product_model
		$xmlstr.=" <v_status>Missing Model Number</v_status>\n";
	}
	$xmlstr.="</xmlProductImport>";
// end of row insertion code
}

//Categories			//Category Relation			//Product Category Relation
function categoryLookup($categories_1, $categories_2, $categories_3, $products_id){
	
	$categories_1_ID = 0;
	$categories_2_ID = 0;
	$categories_3_ID = 0;
	
	if($categories_1 != ""){
		$query = "SELECT cat.categories_id FROM ".CATEGORIES." cat LEFT JOIN ".CATEGORIES_DESCRIPTION." des 
		ON cat.categories_id = des.categories_id where des.categories_name = '".$categories_1."' and cat.parent_id = 0";
		$result = mysql_query($query);
		if($result == "" || mysql_num_rows($result)==0){
			
			$query = "INSERT INTO ".CATEGORIES." (`parent_id`, `sort_order`, `date_added`) VALUES (0, 10, now())";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			$categories_1_ID = mysql_insert_id();
			
			$query = "INSERT INTO ".CATEGORIES_DESCRIPTION." (`categories_id`, `language_id`, `categories_name`) 
			VALUES ( ".$categories_1_ID.", 1, '".$categories_1."')";
			//echo $query;
			mysql_query($query);
			
			//$query = "update ".CATEGORIES." set id_path = ".$categories_1_ID." where categories_id = ".$categories_1_ID." ";
			//echo $query;
			//mysql_query($query);
		}else{
			$categories_1_ID = @mysql_result($result, 0);
		}
	}
	
	if($categories_2 != "" && $categories_1_ID > 0){
		$query = "SELECT cat.categories_id FROM ".CATEGORIES." cat LEFT JOIN ".CATEGORIES_DESCRIPTION." des 
		ON cat.categories_id = des.categories_id where des.categories_name = '".$categories_2."' and cat.parent_id = ".$categories_1_ID." ";
		$result = mysql_query($query);
		if($result == "" || mysql_num_rows($result)==0){
			$query = "INSERT INTO ".CATEGORIES." ( `parent_id`, `sort_order`, `date_added`) VALUES (".$categories_1_ID.", 20, now())";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			$categories_2_ID = mysql_insert_id();
			
			$query = "INSERT INTO ".CATEGORIES_DESCRIPTION." (`categories_id`, `language_id`, `categories_name`)  
			VALUES ( ".$categories_2_ID.", 1, '".$categories_2."')";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			
			//create id path for this category
			//$strPathVal = $categories_1_ID."/".$categories_2_ID;
			//$query = "update ".CATEGORIES." set id_path = '".$strPathVal."' where categories_id = ".$categories_2_ID." ";
			//mysql_query($query);
		}else{
			$categories_2_ID = @mysql_result($result, 0);
		}
	}
	
	if($categories_3 != "" && $categories_2_ID > 0){
		$query = "SELECT cat.categories_id FROM ".CATEGORIES." cat LEFT JOIN ".CATEGORIES_DESCRIPTION." des 
		ON cat.categories_id = des.categories_id where des.categories_name = '".$categories_3."' and cat.parent_id = ".$categories_2_ID." ";
		$result = mysql_query($query);
		if($result == "" || mysql_num_rows($result)==0){
			$query = "INSERT INTO ".CATEGORIES." ( `parent_id`, `sort_order`, `date_added`) VALUES (".$categories_2_ID.", 30, now())";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			$categories_3_ID = mysql_insert_id();
			
			$query = "INSERT INTO ".CATEGORIES_DESCRIPTION." (`categories_id`, `language_id`, `categories_name`) 
			VALUES ( ".$categories_3_ID.", 1, '".$categories_3."')";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			
			//create id path for this category
			//$strPathVal = $categories_2_ID."/".$categories_3_ID;
			//$query = "update ".CATEGORIES." set id_path = '".$strPathVal."' where categories_id = ".$categories_2_ID." ";
			//mysql_query($query);
		}else{
			$categories_3_ID = @mysql_result($result, 0);
		}
	}
	
	//Product Category Relation
	if($categories_1_ID > 0){
		$query = "delete from ".PRODUCTS_CATEGORIES." where products_id = ".$products_id."";
		mysql_query($query);
		
		if($categories_3_ID > 0){
			//get product count
			//$query = "SELECT product_count as v_product_count FROM ".CATEGORIES." where categories_id = '".$categories_3_ID."' ";
			//$result = mysql_query($query);
			//$rowprod=mysql_fetch_array($result);
			//$productCount = $rowprod['v_product_count'];
			//increment product count
			//$productCount++;
			$query = "INSERT INTO ".PRODUCTS_CATEGORIES." (`categories_id`, `products_id`) VALUES (".$categories_3_ID.", ".$products_id.")";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			//update product count
			//$query = "update ".CATEGORIES." set product_count = '".$productCount."' where categories_id = ".$categories_3_ID." ";
			mysql_query($query);
		} else if($categories_2_ID > 0){
			//get product count
			//$query = "SELECT product_count as v_product_count FROM ".CATEGORIES." where categories_id = '".$categories_2_ID."' ";
			//$result = mysql_query($query);
			//$rowprod=mysql_fetch_array($result);
			//$productCount = $rowprod['v_product_count'];
			//increment product count
			//$productCount++;
			$query = "INSERT INTO ".PRODUCTS_CATEGORIES." (`categories_id`, `products_id`) 
				VALUES (".$categories_2_ID.", ".$products_id.")";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			//update product count
			//$query = "update ".CATEGORIES." set product_count = '".$productCount."' where categories_id = ".$categories_2_ID." ";
			//mysql_query($query);
		} else if($categories_1_ID > 0){
			//get product count
			//$query = "SELECT product_count as v_product_count FROM ".CATEGORIES." where categories_id = '".$categories_1_ID."' ";
			//$result = mysql_query($query);
			//$rowprod=mysql_fetch_array($result);
			//$productCount = $rowprod['v_product_count'];
			//increment product count
			//$productCount++;
			$query = "INSERT INTO ".PRODUCTS_CATEGORIES." (`categories_id`, `products_id`) VALUES (".$categories_1_ID.", ".$products_id.")";
			//echo "<br/><br/>".$query;
			mysql_query($query);
			//update product count
			//$query = "update ".CATEGORIES." set product_count = '".$strPathVal."' where categories_id = ".$categories_1_ID." ";
			//mysql_query($query);
		}
	}
}
?>
