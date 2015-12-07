<?php
namespace Sainsburys;

/*
*
* class URLHelper
*
* This helper class contains static methods to perform operations on specified URLs 
*
*/
class URLHelper {
  /*
  *
  * function setGenerateHtml($genHtml)
  *
  * This method returns the default url (the Sainsbury's test page)
  *
  */
  public static function getDefaultUrl(){
    $default_url = "http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/5_products.html";
    return $default_url;
  }
  /*
  *
  * function getHTML($url,$useFileGet)
  *
  * This method retrieves and returns the content of a specified url
  * 
  * $url(string) -> the url to retrieve if set to NULL uses the default url
  * $useFileGet(boolean) -> if true use PHP file_get_contents instead of CURL
  *
  */
  public static function getHTML($url,$useFileGet=false){
    if($url===NULL) {
      $url = self::getDefaultUrl();
    }
    if($useFileGet) {
      return self::fileGetHTML($url);
    }
    return self::curlGetHTML($url);
  }
  /*
  *
  * function curlGetHTML($url)
  *
  * This method retrieves and returns the content of a specified url using CURL
  * 
  * $url(string) -> the url to retrieve
  *
  */
  public static function curlGetHTML($url){
    $error = false;
    $curl = curl_init($url);
    $curl_transfer_options = array(CURLOPT_USERAGENT=>"Sainsbury's Test Bot",
                                   CURLOPT_FAILONERROR=>true, // Detect HTTP Errors
                                   CURLOPT_FOLLOWLOCATION=>true, // Follow http 3xx redirects
                                   CURLOPT_RETURNTRANSFER=>1, // Returns the HTML from the url
                                   CURLOPT_COOKIEJAR=>"cookies.txt", // Location to write cookies to
                                   CURLOPT_COOKIEFILE=>"cookies.txt"); // Location to read cookies from

    curl_setopt_array($curl, $curl_transfer_options);
    $html = curl_exec( $curl );
    if(curl_errno($curl))
    {
      $error = curl_error($curl);
    }
    curl_close($curl);
    if ($error !== false) {
      throw new \Exception('CURL Error ('.$url.'): '.$error);
    } else {
      return mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
    }
  }
  /*
  *
  * function curlGetHTML($url)
  *
  * This method retrieves and returns the content of a specified url using PHP file_get_contents
  * 
  * $url(string) -> the url to retrieve
  *
  */
  public static function fileGetHTML($url){
    $html = @file_get_contents($url);
    if ($html === FALSE) {
      throw new \Exception('Get Content Error ('.$url.'): Failed to retrieve the content of the specified URL');
    } else {
      return mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
    }
  }
  /*
  *
  * function getFileSize($url)
  *
  * This method calculates and returns the size of a specified text file
  * 
  * $html(string) -> the text file to be measured
  * $$asString(boolean) -> if true return the size in kb as string otherwise return the size in bytes
  *
  */
  public function getFileSize($html, $asString = true) {
    $bytesSize = mb_strlen($html, '8bit');
    if($asString) {
      return number_format($bytesSize/1024,1)."kb";
    }
    return $bytesSize;
  }
}