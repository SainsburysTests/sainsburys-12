<?php
namespace Sainsburys;

use Sunra\PhpSimple\HtmlDomParser;

/*
*
* class Scraper
*
* This class retrieves informations on Sainsbury's from a specified url (defaults to the Sainsbury's test page)
*
* Is possible to specify a set of flags(options) to control the Scraper behaviour:
* useFileGet = if true use PHP file_get_contents instead of CURL
* textErrors = if true display errors as plain text instead of json
* plain = if true use plain json instead of human readable
* descriptionText = if true get the product description from the page text instead of the meta tags
*
*/
class Scraper {
  private $url=NULL;
  private $html='';
  private $useFileGet=false;
  private $textErrors=false;
  private $plain=false;
  private $descriptionText=false;
  private $products=array();
  private $total=0;
  private $generateHtml = true;

  function __construct($useFileGet=false,$textErrors=false,$plain=false,$descriptionText=false,$url=NULL) {
    $this->useFileGet = $useFileGet;
    $this->textErrors = $textErrors;
    $this->plain = $plain;
    $this->descriptionText = $descriptionText;
    $this->url = $url;
  }
  /*
  *
  * Setter/Getter for html, description, products and total
  *
  */
  public function setURL($url) {
    $this->url=$url;
  }  
  public function getURL() {
    return $this->url;
  }  
  public function getHtml() {
    return $this->html;
  }  
  public function setHtml($html) {
    $this->html=$html;
  }  
  public function getProducts() {
    return $this->products;
  }  
  public function getTotal() {
    return $this->total;
  }  
  /*
  *
  * function setGenerateHtml($genHtml)
  *
  * This method turns on ond off the html generation on the scrape method
  * 
  * $genHtml(boolean) -> if false the scrape method will use the existing html property instead of generating one
  *
  */
  public function setGenerateHtml($genHtml) {
    $this->generateHtml = $genHtml;
  }  
  /*
  *
  * function setOptions($options)
  *
  * This method set the options using an array as parameter
  * 
  * $options(array) -> an array containing the new settings for the options:
  * useFileGet = if true use PHP file_get_contents instead of CURL
  * textErrors = if true display errors as plain text instead of json
  * plain = if true use plain json instead of human readable
  * descriptionText = if true get the product description from the page text instead of the meta tags
  *
  */
  public function setOptions($options) {
    $this->useFileGet = $options['useFileGet'];
    $this->textErrors = $options['textErrors'];
    $this->plain = $options['plain'];
    $this->descriptionText = $options['descriptionText'];
  }
  /*
  *
  * function getOptions()
  *
  * This method returns an array containing the current settings of the options:
  * useFileGet = if true use PHP file_get_contents instead of CURL
  * textErrors = if true display errors as plain text instead of json
  * plain = if true use plain json instead of human readable
  * descriptionText = if true get the product description from the page text instead of the meta tags
  *
  */
  public function getOptions() {
    return array(
      'useFileGet'=> $this->useFileGet,
      'textErrors'=> $this->textErrors,
      'plain'=> $this->plain,
      'descriptionText'=> $this->descriptionText 
    );
  }
  /*
  *
  * function generateHtml($useFileGet)
  *
  * This method retrieves the html content of the URL specified in the url property using URLHelper
  *
  */
  public function generateHtml() {
    $this->html = URLHelper::getHTML($this->url,$this->useFileGet);
  }
  /*
  *
  * function parseHTML()
  *
  * This method retrieves the products details from the html property
  * It also gets additional informations from the product URL using the Product class
  *
  */
  public function parseHTML(){
    $products = array();
    $total = 0;
    if($this->html!=''){
      $dom = HtmlDomParser::str_get_html($this->html);
      if($dom->find('div.product', 0) !== NULL){
        foreach($dom->find('div.product') as $product) {
          // get title
          $titleLink = $product->find('div.productInfo h3 a', 0);
          if($titleLink !== NULL){
            $item['title'] = trim($titleLink->plaintext);
            $item['productUrl'] = trim($titleLink->href);
            // Unit prce and total
            $unitPriceElement = $product->find('p.pricePerUnit', 0);
            if($unitPriceElement !== NULL){
              $unitPriceString = trim($unitPriceElement->plaintext);
              preg_match('!\d+(?:\.\d+)?!', $unitPriceString, $matches);
              $item['unitPrice'] = floatval($matches[0]);
              $total+=$item['unitPrice'];
            } else {
              $item['unitPrice'] = '';
            }
            $products[] = $item;
          }
        }      
      }
      // clean up memory
      $dom->clear();
      unset($dom);      
    }
    //setting total
    $this->total = $total;
    //setting products
    foreach($products as $p) {
      $productObj = new Product($p['productUrl']);
      $productObj->generateHtml($this->useFileGet);
      $productObj->parseHTML($this->descriptionText);
      $product = array();
      $product['title'] = $p['title'];
      $product['size'] = $productObj->getSize();
      $product['unit_price'] = $p['unitPrice'];
      $product['description'] = $productObj->getDescription();
      $this->products[] = $product;
    }
  }
  /*
  *
  * function scrape()
  *
  * This method performs the scrape of the page and returns the final json array
  *
  */
  public function scrape() {
    if($this->generateHtml) {
      try {
        $this->generateHtml();
      } catch (\Exception $e) {
        return $this->returnError($e->getMessage());
      }
    }
    try {
      $this->parseHTML();
    } catch (\Exception $e) {
      return $this->returnError($e->getMessage());
    }
    return $this->getProductsJson();
  }
  /*
  *
  * function getProductsJson()
  *
  * This private method builds and returns the final json array from the products and total properties
  *
  */
  private function getProductsJson() {
    $returnArray = array(
      'results' => $this->products,
      'total' => $this->total
    );
    return json_encode($returnArray, $this->plain ? JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  }
  /*
  *
  * function returnError($error)
  *
  * This private method returns a specified error either as plain text or as json array
  *
  */
  private function returnError($error) {
    if($this->textErrors) {
      return $error;
    }
    $errorArray = array(
      'success'=>'false',
      'error'=>$error
    );
    return json_encode($errorArray, $this->plain ? JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  }
}



