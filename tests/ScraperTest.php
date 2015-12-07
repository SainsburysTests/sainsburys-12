<?php
class ScraperTest extends PHPUnit_Framework_TestCase {
  private $scraper;
  private $scraperFileGet;
  private $invalidURL;
  private $testHtml;
  private $testHtmlWrongUrl;

  public function setUp() {
    $this->invalidURL = 'test';
    $this->scraper = new Sainsburys\Scraper();
    $this->scraperFileGet = new Sainsburys\Scraper(true);
    $this->testHtml = '<html><head></head><body>
      <div class="product">
        <div>
          <div class="productInfo">
              <h3><a href="http://sainsburysdevtest.s3-website-us-east-1.amazonaws.com/simple.html">Test 1</a></h3>
            <div>
                <p class="pricePerUnit">2.20</p>
            </div>
          </div>
        </div>
      </div>
      <div class="product">
        <div>
          <div class="productInfo">
              <h3><a href="http://sainsburysdevtest.s3-website-us-east-1.amazonaws.com/simple.html">Test 2</a></h3>
            <div>
                <p class="pricePerUnit">4.40</p>
            </div>
          </div>
        </div>
      </div>
    </body></html>';
    $this->testHtmlWrongUrl = '<html><head></head><body>
      <div class="product">
        <div>
          <div class="productInfo">
              <h3><a href="invalid">Test 1</a></h3>
            <div>
                <p class="pricePerUnit">2.20</p>
            </div>
          </div>
        </div>
      </div>
      <div class="product">
        <div>
          <div class="productInfo">
              <h3><a href="http://sainsburysdevtest.s3-website-us-east-1.amazonaws.com/invalid.html">Test 2</a></h3>
            <div>
                <p class="pricePerUnit">4.40</p>
            </div>
          </div>
        </div>
      </div>
    </body></html>';    
  }

  public function testGenerateHtml() {
    $this->scraper->generateHtml();
    $this->assertContains(
      '<title>Ripe &amp; ready | Sainsbury&#039;s</title>', $this->scraper->getHtml()
    );
    $this->scraper->setURL($this->invalidURL);
    $this->setExpectedExceptionRegExp(
      'Exception', '/CURL.*/'
    );
    $this->scraper->generateHtml();
  }
  public function testGenerateHtmlFileGet() {
    $this->scraperFileGet->generateHtml();
    $this->assertContains(
      '<title>Ripe &amp; ready | Sainsbury&#039;s</title>', $this->scraperFileGet->getHtml()
    );
    $this->scraperFileGet->setURL($this->invalidURL);
    $this->setExpectedExceptionRegExp(
      'Exception', '/Get Content.*/'
    );
    $this->scraperFileGet->generateHtml();
  }
  public function testParseHTML() {
    $expectedProducts=array(
      array(
        'title' => 'Test 1',
        'size' => '0.2kb',
        'unit_price' => 2.2,
        'description' => ''
      ),
      array(
        'title' => 'Test 2',
        'size' => '0.2kb',
        'unit_price' => 4.4,
        'description' => ''
      )
    );
    $this->scraper->setHtml($this->testHtml);
    $this->scraper->parseHTML();
    $this->assertEquals(
      $expectedProducts, $this->scraper->getProducts()
    );
    $this->assertEquals(
      6.6, $this->scraper->getTotal()
    );
  }
  public function testScrape() {
    $expectedProducts=array(
      array(
        'title' => 'Test 1',
        'size' => '0.2kb',
        'unit_price' => 2.2,
        'description' => ''
      ),
      array(
        'title' => 'Test 2',
        'size' => '0.2kb',
        'unit_price' => 4.4,
        'description' => ''
      )
    );
    $expectedResultArray = array(
      'results' => $expectedProducts,
      'total' => 6.6
    );
    $expectedResult = json_encode($expectedResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    
    $this->scraper->setHtml($this->testHtml);
    $this->scraper->setGenerateHtml(false);
    $this->assertEquals(
      $expectedResult, $this->scraper->scrape()
    );
  }
  public function testScrapeError() {
    $this->scraper->setGenerateHtml(false);
    $this->scraper->setOptions(array(
      'useFileGet'=> false,
      'textErrors'=> true,
      'plain'=> false,
      'descriptionText'=> false 
    ));
    $this->scraper->setHtml($this->testHtmlWrongUrl);
    $error = $this->scraper->scrape();
    $expectedResultArray = array(
      'success'=>'false',
      'error'=>$error
    );    
    $expectedResult = json_encode($expectedResultArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $this->scraper->setOptions(array(
      'useFileGet'=> false,
      'textErrors'=> false,
      'plain'=> false,
      'descriptionText'=> false 
    ));
    $this->assertEquals(
      $expectedResult, $this->scraper->scrape()
    );
  }
}
