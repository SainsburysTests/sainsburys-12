<?php
class URLHelperTest extends PHPUnit_Framework_TestCase {
  private $simplePageURL;
  private $simplePage;
  private $invalidURL;
  private $forbiddenURL;

  public function setUp() {
    $this->simplePageURL = 'http://sainsburysdevtest.s3-website-us-east-1.amazonaws.com/simple.html';
    $this->invalidURL = 'test';
    $this->forbiddenURL = 'http://sainsburysdevtest.s3-website-us-east-1.amazonaws.com/invalid.html';
    $this->simplePage = '<!DOCTYPE html>'.PHP_EOL;
    $this->simplePage .= '<html>'.PHP_EOL;
    $this->simplePage .= '<head>'.PHP_EOL;
    $this->simplePage .= '<title>Test HTML File</title>'.PHP_EOL;
    $this->simplePage .= '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.PHP_EOL;
    $this->simplePage .= '</head>'.PHP_EOL;
    $this->simplePage .= '<body><p>Simple HTML file.</p></body>'.PHP_EOL;
    $this->simplePage .= '</html>';
  }

  public function testFileGetHTML() {
    $returnedSimplePage = Sainsburys\URLHelper::fileGetHTML($this->simplePageURL);
    $this->assertEquals(
      $this->simplePage, $returnedSimplePage
    );
  }
  public function testFileGetHTMLForbidden() {
    $this->setExpectedExceptionRegExp(
      'Exception', '/Get Content.*/'
    );
    $exceptionTest = Sainsburys\URLHelper::fileGetHTML($this->forbiddenURL);
  }
  public function testFileGetHTMLInvalid() {
    $this->setExpectedExceptionRegExp(
      'Exception', '/Get Content.*/'
    );
    $exceptionTest = Sainsburys\URLHelper::fileGetHTML($this->invalidURL);
  }
  public function testCurlGetHTML() {
    $returnedSimplePage = Sainsburys\URLHelper::curlGetHTML($this->simplePageURL);
    $this->assertEquals($this->simplePage, $returnedSimplePage);
  }
  public function testCurlGetHTMLForbidden() {
    $this->setExpectedExceptionRegExp(
      'Exception', '/CURL.*/'
    );
    $exceptionTest = Sainsburys\URLHelper::curlGetHTML($this->forbiddenURL);
  }
  public function testCurlGetHTMLInvalid() {
    $this->setExpectedExceptionRegExp(
      'Exception', '/CURL.*/'
    );
    $exceptionTest = Sainsburys\URLHelper::curlGetHTML($this->invalidURL);
  }
  public function testGetFileSize() {
    $returnedSize = Sainsburys\URLHelper::getFileSize("<html>Test</html>");
    $this->assertEquals('0.0kb', $returnedSize);
  }
  public function testGetFileSizeBytes() {
    $returnedSize = Sainsburys\URLHelper::getFileSize("<html>Test</html>",false);
    $this->assertEquals(17, $returnedSize);
  }
}