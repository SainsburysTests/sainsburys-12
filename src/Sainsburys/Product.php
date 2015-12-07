<?php
namespace Sainsburys;

use Sunra\PhpSimple\HtmlDomParser;

/*
*
* class Product
*
* This class retrieves informations on the product from the product page specified in the constructor
*
*/
class Product {
  public $productUrl = NULL;
  private $html = '';
  private $description = '';
  function __construct($productUrl) {
    $this->productUrl = $productUrl;
  }
  /*
  *
  * Setter/Getter for html and description
  *
  */
  public function getHtml() {
    return $this->html;
  } 
  public function setHtml() {
    return $this->html;
  }  
  public function getDescription() {
    return $this->description;
  }
  /*
  *
  * function getSize()
  *
  * This method returns the filesize of the html property as string
  *
  */
  public function getSize() {
    return URLHelper::getFileSize($this->html);
  }
  /*
  *
  * function generateHtml($useFileGet)
  *
  * This method retrieves the html content using URLHelper
  * 
  * $useFileGet(boolean) -> if true Tells URLHelper to PHP file_get_contents instead of CURL
  *
  */
  public function generateHtml($useFileGet) {
    $this->html = URLHelper::getHTML($this->productUrl,$useFileGet);
  }
  /*
  *
  * function parseHTML($descriptionText)
  *
  * This method retrieves the description of the product from the html property
  * 
  * $descriptionText(boolean) -> if true the method gets the product description from the page text instead of the meta tags
  *
  */
  public function parseHTML($descriptionText){
    $description = '';
    if($this->html!=''){
      $dom = HtmlDomParser::str_get_html($this->html);
      if($descriptionText) {
        foreach($dom->find('h3.productDataItemHeader') as $productHeader) {
          if(trim($productHeader->plaintext) == 'Description'){
           $descriptionElement = $productHeader->next_sibling()->first_child();
           $description = trim($descriptionElement->plaintext);
          }
        }
      } else {
        $metaDescription = $dom->find("meta[name='description']", 0);
        if($metaDescription !== NULL){
          $description = $metaDescription->content;
        }
      }
      // clean up memory
      $dom->clear();
      unset($dom);
    }
    $this->description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
  }
}