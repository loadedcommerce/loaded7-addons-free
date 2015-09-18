<?php
//ini_set("display_errors", 1);
//error_reporting(E_ERROR);

//*********************** CONFIGURATION VARIABLES	*******************
include("includes/config.php");

$table_prefix = DB_TABLE_PREFIX;
define('CATEGORIES',  $table_prefix . "categories");
define('CATEGORIES_DESCRIPTION',  $table_prefix . "categories_description");
define('PRODUCTS_TO_CATEGORIES',  $table_prefix . "products_to_categories");
define('MANUFACTURERS',  $table_prefix . "manufacturers");
define('MANUFACTURERS_INFO',  $table_prefix . "manufacturers_info");
define('PRODUCTS',  $table_prefix . "products");
define('PRODUCT_ATTRIBUTES',  $table_prefix . "product_attributes");
define('PRODUCTS_DESCRIPTION',  $table_prefix . "products_description");
define('PRODUCTS_IMAGES',  $table_prefix . "products_images");
define('LANGUAGES',  $table_prefix . "languages");
define('TAX_CLASS',  $table_prefix . "tax_class");

$PassKey="02446";//This variable is used for authentication of xml file

global $usercount;
global $userdata;
$usercount=0;

$dbhandle = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD)
or die("Unable to connect to MySQL");
$db_selected = mysql_select_db(DB_DATABASE,$dbhandle);	
		
//************************* END INITIALIZATION	**************************
if(isset($_POST['XML_INPUT_VALUE']))
{
	$arr_xml = xml2php($_POST['XML_INPUT_VALUE']);
	requestType($arr_xml,$PassKey);
} else{
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
				ImportProduct($_POST['XML_INPUT_VALUE']);
				break;		
				}
	}

}

function output_xml($content) 
{
	header("Content-Type: application/xml; charset=ISO-8859-1");
	header("Expires: Mon, 01 Jan 2014 05:00:00 GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	print $content;
	
}

function GetProd($skuPrefixVal){
	$likeSql = "";
	if(isset($skuPrefixVal) && $skuPrefixVal != "") {
		$likeSql = "where p.products_model like '$skuPrefixVal%'";
	}
	$xml_str = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";	
	$sql_prod = "select p.products_model as products_model, i.image as products_image, p.products_price as products_price, p.products_quantity as  products_quantity, p.products_status as products_status   
				from ".PRODUCTS." as p left join ".PRODUCTS_IMAGES." as i on p.products_id = i.products_id  ".$likeSql;
	$result = mysql_query($sql_prod);
	$xml_str .= "<xmlPopulate>\n";
	$xml_str .= "<xmlGetProductsResponse>\n";
	$count=0;
	$v_products_image = "&amp;";
	
	if($result != null){
		while ($rowprod=mysql_fetch_array($result)){
			$v_products_image = "&amp;";
			if($rowprod['products_image'] != ""){
				$v_products_image = $rowprod['products_image'];
			}			
			$xml_str .= "<xmlProduct>\n<v_products_model><![CDATA[".$rowprod['products_model']."]]></v_products_model>\n";
			$xml_str .= "<v_products_image><![CDATA[".$v_products_image."]]></v_products_image>\n";
			$xml_str .= "<v_products_quantity><![CDATA[".$rowprod['products_quantity']."]]></v_products_quantity>\n";
			$xml_str .= "<v_products_status><![CDATA[".$rowprod['products_status']."]]></v_products_status>\n";
			$xml_str .= "<v_products_price><![CDATA[".$rowprod['products_price']."]]></v_products_price>\n";
			$xml_str .= "</xmlProduct>\n";
			$count++;
		}
	}
	$xml_str .="</xmlGetProductsResponse>\n</xmlPopulate>";	
	output_xml($xml_str);	
}

function Ping(){
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

function ImportProduct($array_haystack) {
	global $usercount;
	global $userdata;
	if (!($xml_parser = xml_parser_create())) die("Couldn't create parser.");
	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler( $xml_parser, "characterDataHandler");
	xml_parse($xml_parser, $array_haystack);
	xml_parser_free($xml_parser);
	
	//Get the language id defaul language code=en_US	
	$language = $_GET["language"];
	$query = "select languages_id from ".LANGUAGES." where code = '".$language."'";
	$result = mysql_query($query);
	$lang = mysql_result($result,0);
	if($lang == ""){
		echo "Check language";
		exit(0);
	}
	
	//Get the tax class id tax=Taxabloe Gods
	$tax = $_GET["tax"];
	$query = "select tax_class_id from ".TAX_CLASS." where tax_class_title = '".$tax."'";
	$result = mysql_query($query);
	$tax_class_id = mysql_result($result,0);
	if($tax_class_id == ""){
		echo "Check tax class";
		exit(0);
	}
	// updates, msrp and add manufacture in title 
	// send true in market url if want to update.
	$msrpUpdate = "";
	$manfInTitle = "";
	if(isset($_GET["msrp"]))
		$msrpUpdate = $_GET["msrp"];
	if(isset($_GET["manuftitle"]))
		$manfInTitle = $_GET["manuftitle"];
		
	$selectSkus = "select products_id, products_model from ".PRODUCTS;
	$result = mysql_query($selectSkus);
	$skus = array();
	// Creating the array of "products_id" with "sku" as index
	while($sku_arr = mysql_fetch_array($result)){
		$skus[mysql_real_escape_string($sku_arr["products_model"])]=$sku_arr["products_id"] ; //products_model is sku
	}
	
	$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>\n";
	//iterating through all the skus that came in the xml request
	for($i = 0; $i < $usercount; $i++){
		$xmlstr.="<xmlProductImport>";
		$userdata[$i]["tax_class_id"] = $tax_class_id;	
		if(strcasecmp($userdata[$i]["status"], "InActive") == 0) 
			$userdata[$i]["status"] = 0;
		elseif(strcasecmp($userdata[$i]["status"], "Active") == 0) 
			$userdata[$i]["status"] = 1;	
	
		$xmlstr.="<v_products_model>" . $userdata[$i]["sku"] . "</v_products_model>\n"; 
		$image = "";
		
		if(isset($userdata[$i]["image"])){
			if($userdata[$i]["image"] != ""){
				//Calculate image location
				$productPath = "images/products/product_info/".basename($userdata[$i]["image"]);	
				$thumbnanilsPath = "images/products/thumbnails/".basename($userdata[$i]["image"]);
				$popupPath = "images/products/popup/".basename($userdata[$i]["image"]);
				$originalsPath = "images/products/originals/".basename($userdata[$i]["image"]);
				$miniPath = "images/products/mini/".basename($userdata[$i]["image"]);
				$largePath = "images/products/large/".basename($userdata[$i]["image"]);
						
				$file = file_get_contents($userdata[$i]["image"]);
				file_put_contents($productPath,$file);
				file_put_contents($thumbnanilsPath,$file);
				file_put_contents($popupPath,$file);
				file_put_contents($originalsPath,$file);
				file_put_contents($miniPath,$file);
				file_put_contents($largePath,$file);
				$image = basename($userdata[$i]["image"]);
			}
		}
		$cat1 = "";
		$cat2 = "";
		$cat3 = "";
		if(isset($userdata[$i]["categories_1"]))
			$cat1 = trim($userdata[$i]["categories_1"]);
		if(isset($userdata[$i]["categories_2"]))
			$cat2 = trim($userdata[$i]["categories_2"]);
		if(isset($userdata[$i]["categories_3"]))
			$cat3 = trim($userdata[$i]["categories_3"]);
			
		if($userdata[$i]["sku"] != '') {
			//If sku already exists in the DB then its an update else its a new product				
				
			if(array_key_exists($userdata[$i]["sku"],$skus))  {
				$xmlstr.="<v_status>UPDATE</v_status>\n";
				$update_pid =  $skus[$userdata[$i]["sku"]];
				// full update
				if($userdata[$i]["tag_count"] > 9){
					if($manfInTitle == 'yes'){
						if($userdata[$i]["manufacturer"] != '')
							$userdata[$i]["name"] = $userdata[$i]["manufacturer"]." - ". $userdata[$i]["name"];
					}	
					$mnfid = 0;
					if($userdata[$i]["manufacturer"] != '')
						$mnfid = getManufacturer($userdata[$i]["manufacturer"], $lang);									
					if($userdata[$i]["weight"] == ''){
						$userdata[$i]["weight"] = 0;
					}
					
					//Update Products Table					
					$query = "update ".PRODUCTS." set products_quantity = ".$userdata[$i]["quantity"]."
					, manufacturers_id = ".$mnfid."
					, products_price = ".$userdata[$i]["price"]."
					, products_cost = ".$userdata[$i]["cost"]."
					, products_weight = '".$userdata[$i]["weight"]."'
					, products_status = ".$userdata[$i]["status"];
					
					if($msrpUpdate == 'yes')
						$query .= " ,products_msrp=".$userdata[$i]["v_products_msrp"];
					$query .= " ,products_last_modified = CURRENT_TIMESTAMP where products_id = ".$update_pid;
					mysql_query($query);	
					
					//get Product Url
					$productURL = getProductKeyword($userdata[$i]["name"], $update_pid);
					
					//Update Products_descriptions
					$query = "update ".PRODUCTS_DESCRIPTION." set products_name = '".$userdata[$i]["name"]."'
					, products_description = '".$userdata[$i]["description"]."' , products_keyword = '".$productURL."' where products_id = ".$update_pid;
					mysql_query($query);
					
					// map manufacture					
					$query = "update ".PRODUCT_ATTRIBUTES." set value = ".$mnfid." where id = 2 and products_id = ".$update_pid;
					mysql_query($query);	
					
					//Update image
					$query = "update ".PRODUCTS_IMAGES." set image = '".$image."' where products_id = ".$update_pid;
					mysql_query($query);
					
					//Category Mapping
					categoryLookup($cat1, $cat2, $cat3, $update_pid, $lang);
				}else{					
					$query = "update ".PRODUCTS." set products_quantity = ".$userdata[$i]["quantity"]."
					, products_price = ".$userdata[$i]["price"]."
					, products_cost = ".$userdata[$i]["cost"]."
					, products_status = ".$userdata[$i]["status"];
					
					if($msrpUpdate == 'yes')
						$query .= " ,products_msrp=".$userdata[$i]["v_products_msrp"];
					$query .= " ,products_last_modified = CURRENT_TIMESTAMP where products_id = ".$update_pid;
					mysql_query($query);	
					
						
					if($image != ""){
						$query = "update ".PRODUCTS_IMAGES." set image = '".$image."' where products_id = ".$update_pid;
						mysql_query($query);
					}
				}
			}else{
				//New product section
				$xmlstr.="<v_status>NEW</v_status>\n";
				if($manfInTitle == 'yes'){
					if($userdata[$i]["manufacturer"] != '')
						$userdata[$i]["name"] = $userdata[$i]["manufacturer"]." - ". $userdata[$i]["name"];
				}
				
				if($userdata[$i]["weight"] == ''){
					$userdata[$i]["weight"] = 0;
				}
					
				$mnfid = 0;
				if($userdata[$i]["manufacturer"] != '')
					$mnfid = getManufacturer($userdata[$i]["manufacturer"], $lang);	
				
				//Insert products details
				$msrp = 0;
				if($msrpUpdate == 'yes')
					$msrp = $userdata[$i]["v_products_msrp"];
				$query = "insert into ".PRODUCTS."(parent_id, products_quantity, products_price, products_cost, products_msrp, products_model, products_sku, products_date_added, products_last_modified, products_weight, products_weight_class, products_status, products_tax_class_id, manufacturers_id) values(0, ".$userdata[$i]["quantity"].",".$userdata[$i]["price"].", ".$userdata[$i]["cost"].", ".$msrp.", '".$userdata[$i]["sku"]."', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '".$userdata[$i]["weight"]."',4,".$userdata[$i]["status"].", ".$userdata[$i]["tax_class_id"].", ".$mnfid.")";
				mysql_query($query);
				
				$product_id = mysql_insert_id();
				
				$productURL = getProductKeyword($userdata[$i]["name"], $product_id);
				
				//Insert product title and descriptions
				$query = "insert into ".PRODUCTS_DESCRIPTION." (products_id, language_id, products_name, products_description , products_keyword, products_tags, products_url) values(".$product_id.", ".$lang.", '".$userdata[$i]["name"]."','".$userdata[$i]["description"]."','".$productURL."', '', '')";
				mysql_query($query);
				
				// map manufacture
				$query = "insert into ".PRODUCT_ATTRIBUTES." (id, products_id, languages_id, value) values (2, ".$product_id.", ".$lang.", ".$mnfid.")";
				mysql_query($query);
				
				//Insert Image
				$query = "insert into ".PRODUCTS_IMAGES." (products_id, image, default_flag, sort_order, date_added) values(".$product_id.", '".$image."', 1, 0, CURRENT_TIMESTAMP)";
				mysql_query($query);
			}
			
			if($product_id > 0){
				//Category Mapping
				categoryLookup($cat1, $cat2, $cat3, $product_id, $lang);
			}
		}
		$xmlstr.="</xmlProductImport>";
	}	
	$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
	output_xml($xmlstr);
}

function getManufacturer($manufacturer, $languageId){
	$manufacturerid = -1;
	$query = "select manufacturers_id from ".MANUFACTURERS." where manufacturers_name = '".$manufacturer."'";
	$result = mysql_query($query);
	if(mysql_num_rows($result)==0){
		$query = "insert into ".MANUFACTURERS." (manufacturers_name, manufacturers_image, date_added, last_modified) values ('".$manufacturer."', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
		mysql_query($query);
		$query = "select max(manufacturers_id) from ".MANUFACTURERS;
		$result = mysql_query($query);
		$manufacturerid = mysql_result($result, 0);	
		$query = "insert into ".MANUFACTURERS_INFO." (manufacturers_id, languages_id, manufacturers_url, url_clicked) values (".$manufacturerid.",".$languageId.", '', 0)";
		mysql_query($query);
	}else
		$manufacturerid = mysql_result($result, 0);
	return $manufacturerid;	
}
function getProductKeyword($pname ,$pid){
	$post_name = trim(strtolower(str_replace('&reg;',' ',(trim($pname)."-".$pid))));
	$post_name = trim(strtolower(str_replace('&quot;',' ',trim($post_name))));
	$post_name = trim(strtolower(str_replace('&',' ',trim($post_name))));
	$post_name = trim(strtolower(str_replace('\'','',$post_name)));
	$post_name = trim(strtolower(str_replace('/',' ',$post_name)));
	$post_name = trim(strtolower(str_replace('"','',$post_name)));
	$post_name = trim(strtolower(str_replace('+','',$post_name)));
	$post_name = trim(strtolower(str_replace('(','',$post_name)));
	$post_name = trim(strtolower(str_replace(')','',$post_name)));
	$post_name = trim(strtolower(str_replace('   ','-',$post_name)));
	$post_name = trim(strtolower(str_replace('  ','-',$post_name)));
	$post_name = trim(strtolower(str_replace(' ','-',$post_name)));
	$post_name = trim(strtolower(str_replace('- -','-',$post_name)));
	$post_name = trim(strtolower(str_replace('---','-',$post_name)));
	$post_name = trim(strtolower(str_replace('--','-',$post_name)));
	$post_name = trim(strtolower(str_replace('--','-',$post_name)));
	$post_name = trim(strtolower(str_replace('- -','-',$post_name)));
	$post_name = trim(strtolower(str_replace('.','',$post_name)));
	$post_name = trim(strtolower(str_replace(',','',$post_name)));
	$post_name = trim(strtolower(str_replace(' ','',$post_name)));
	return $post_name;
}	
	
// xml parser function
function startElementHandler ($parser,$name,$attrib) {
	global $state;
	$state = $name;
	global $tag ;
	if($name==strtoupper("xmlproduct")) { @$usercount++; $tag = 0;}
}
// xml parser function
function endElementHandler ($parser,$name) {
	global $usercount;
	global $userdata;
	global $state;
	global $tag;
	$state='';
	if($name==strtoupper("xmlproduct")) { 
		$userdata[$usercount]["tag_count"] = $tag;
		$usercount++;}
}
// xml parser function
function characterDataHandler ($parser, $data) {
	global $usercount;
	global $userdata;
	global $state;
	global $tag;
	if (!$state) {return;}
	$data=@mysql_escape_string($data);
	if ($state==strtoupper("v_products_model")) { $userdata[$usercount]["sku"] = $data; $tag++;}
	if ($state==strtoupper("v_products_price")) { $userdata[$usercount]["price"] = $data; $tag++;}
	if ($state==strtoupper("v_products_quantity")) { $userdata[$usercount]["quantity"] = $data; $tag++;}
	if ($state==strtoupper("v_products_msrp")) { $userdata[$usercount]["v_products_msrp"] = $data; $tag++;}
	if ($state==strtoupper("v_products_cost")) { $userdata[$usercount]["cost"] = $data; $tag++;}
	if ($state==strtoupper("v_status")) { $userdata[$usercount]["status"] = $data; $tag++;}
	if ($state==strtoupper("v_products_image")) { $userdata[$usercount]["image"] = $data; $tag++;}
	if ($state==strtoupper("v_products_name_1")) { $userdata[$usercount]["name"] = $data; $tag++;}
	if ($state==strtoupper("v_products_description_1")) { $userdata[$usercount]["description"] = $data; $tag++;}
	if ($state==strtoupper("v_products_weight")) { $userdata[$usercount]["weight"] = $data; $tag++;}
	if ($state==strtoupper("v_manufacturers_id")) {$userdata[$usercount]["v_manufacturers_id"] = $data; $tag++;}
	if ($state==strtoupper("v_manufacturers_name")) {$userdata[$usercount]["manufacturer"] = $data; $tag++;}
	if ($state==strtoupper("v_products_upc")) {$userdata[$usercount]["v_products_upc"] = $data; $tag++;}
	if ($state==strtoupper("v_products_thumbnail")) {$userdata[$usercount]["small_image"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_1")) {$userdata[$usercount]["categories_1"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_2")) {$userdata[$usercount]["categories_2"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_3")) {$userdata[$usercount]["categories_3"] = $data; $tag++;}
	if ($state==strtoupper("v_products_length")) {$userdata[$usercount]["length"] = $data; $tag++;}
	if ($state==strtoupper("v_products_width")) {$userdata[$usercount]["width"] = $data; $tag++;}
	if ($state==strtoupper("v_products_height")) {$userdata[$usercount]["height"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_id")) {$userdata[$usercount]["v_dropshipper_id"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_prefix")) {$userdata[$usercount]["v_dropshipper_prefix"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_name")) {$userdata[$usercount]["v_dropshipper_name"] = $data; $tag++;}
}

//Categories, Category Description	and Product Category Mapping
function categoryLookup($categories_1, $categories_2, $categories_3, $product_id, $lang){
	$categories_1_ID = 0;
	$categories_2_ID = 0;
	$categories_3_ID = 0;
	
	if($categories_1 != ""){
		$query = "SELECT cd.categories_id FROM ".CATEGORIES_DESCRIPTION." as cd, ".CATEGORIES." as c where c.categories_id = cd.categories_id and cd.categories_name = '".$categories_1 ."' and c.parent_id = 0";
		$result = mysql_query($query);		
		if(mysql_num_rows($result)==0){
			$query = "insert into ".CATEGORIES." (categories_image, parent_id, categories_mode, categories_status, categories_visibility_nav, categories_visibility_box, date_added, last_modified,categories_custom_url) values('','0','category',1,1,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,'')";
			mysql_query($query);
			$categories_1_ID = mysql_insert_id();
						
			$query = "insert into ".CATEGORIES_DESCRIPTION." (categories_id, language_id, categories_name, categories_menu_name,categories_tags) values(".$categories_1_ID.",".$lang.", '".$categories_1."', '".$categories_1."', 'tags')";
			mysql_query($query);
		}else{
			$categories_1_ID = mysql_result($result, 0);
		}
	}


	if($categories_2 != "" && $categories_1_ID > 0){
		$query = "SELECT cd.categories_id FROM ".CATEGORIES_DESCRIPTION." as cd, ".CATEGORIES." as c where c.categories_id = cd.categories_id and cd.categories_name = '".$categories_2 ."' and c.parent_id = ".$categories_1_ID;
		$result = mysql_query($query);		
		if(mysql_num_rows($result)==0){
			$query = "insert into ".CATEGORIES." (categories_image, parent_id, categories_mode, categories_status, categories_visibility_nav, categories_visibility_box, date_added, last_modified,categories_custom_url) values('',".$categories_1_ID.",'category',1,1,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,'')";
			mysql_query($query);
			$categories_2_ID = mysql_insert_id();
			
			$query = "insert into ".CATEGORIES_DESCRIPTION." (categories_id, language_id, categories_name, categories_menu_name,categories_tags) values(".$categories_2_ID.",".$lang.", '".$categories_2."', '".$categories_2."', 'tags')";
			mysql_query($query);
		}else{
			$categories_2_ID = mysql_result($result, 0);
		}
	}

	if($categories_3 != "" && $categories_2_ID > 0){
		$query = "SELECT cd.categories_id FROM ".CATEGORIES_DESCRIPTION." as cd, ".CATEGORIES." as c where c.categories_id = cd.categories_id and cd.categories_name = '".$categories_3 ."' and c.parent_id = ".$categories_2_ID;
		$result = mysql_query($query);
		if(mysql_num_rows($result)==0){
			$query = "insert into ".CATEGORIES." (categories_image, parent_id, categories_mode, categories_status, categories_visibility_nav, categories_visibility_box, date_added, last_modified,categories_custom_url) values('',".$categories_2_ID.",'category',1,1,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,'')";
			mysql_query($query);
			$categories_3_ID = mysql_insert_id();
			
			$query = "insert into ".CATEGORIES_DESCRIPTION." (categories_id, language_id, categories_name, categories_menu_name,categories_tags) values(".$categories_3_ID.",".$lang.", '".$categories_3."', '".$categories_3."', 'tags')";
			mysql_query($query);
		}else{
			$categories_3_ID = mysql_result($result, 0);
		}
	}
	
	if($categories_1_ID > 0){
		$query = "delete from ".PRODUCTS_TO_CATEGORIES." where products_id = ".$product_id;
		mysql_query($query);
		if($categories_3_ID > 0){
			$query = "INSERT INTO ".PRODUCTS_TO_CATEGORIES." (`categories_id`,`products_id`) VALUES (".$categories_3_ID.",".$product_id.")";
			mysql_query($query);
		}else if($categories_2_ID > 0){
			$query = "INSERT INTO ".PRODUCTS_TO_CATEGORIES." (`categories_id`,`products_id`) VALUES (".$categories_2_ID.",".$product_id.")";
			mysql_query($query);
		}else{
			$query = "INSERT INTO ".PRODUCTS_TO_CATEGORIES." (`categories_id`,`products_id`) VALUES (".$categories_1_ID.",".$product_id.")";
			mysql_query($query);
		}
	}
}
		
mysql_close($dbhandle);
?>
