<?php
class ProductTest extends PHPUnit_Framework_TestCase {
  private $product;
  private $invalidProduct;
  private $invalidURL;

  public function setUp() {
    $this->invalidURL = 'test';
    $productURL = 'http://hiring-tests.s3-website-eu-west-1.amazonaws.com/2015_Developer_Scrape/sainsburys-apricot-ripe---ready-320g.html';
    $this->product = new Sainsburys\Product($productURL);
    $this->invalidProduct = new Sainsburys\Product('test');
  }

  public function testGenerateHtml() {
    $this->product->generateHtml(false);
    $this->assertContains(
      '<title>Sainsbury&#039;s Apricot Ripe &amp; Ready x5 | Sainsbury&#039;s</title>', $this->product->getHtml()
    );
    $this->setExpectedExceptionRegExp(
    'Exception', '/CURL.*/'
    );
    $this->invalidProduct->generateHtml(false);
  }
  public function testParseHTML() {
    $this->product->generateHtml(false);
    $this->product->parseHTML(false);
    $this->assertContains(
      'the same great quality, freshness and choice', $this->product->getDescription()
    );
  }
  public function testParseHTMLFromPageText() {
    $this->product->generateHtml(false);
    $this->product->parseHTML(true);
    $this->assertEquals(
      'Apricots', $this->product->getDescription()
    );
  }
} 