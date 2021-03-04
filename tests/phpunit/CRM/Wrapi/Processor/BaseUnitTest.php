<?php

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_Wrapi_Processor_BaseUnitTest extends \PHPUnit\Framework\TestCase {

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp(): void {
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Detect content-type.
   * If not set, it returns default.
   * If set with handled value it returns the relevant class string.
   * If set with unknown value, it returns default.
   */
  public function testDetectContentType() {
    // not set.
    $result = CRM_Wrapi_Processor_Base::detectContentType();
    $this->assertEquals("CRM_Wrapi_Processor_UrlEncodedForm", $result, "Invalid class reurned.");
    $testData = [
      "application/json" => "CRM_Wrapi_Processor_JSON",
      "application/javascript" => "CRM_Wrapi_Processor_JSON",
      "text/xml" => "CRM_Wrapi_Processor_XML",
      "application/xml" => "CRM_Wrapi_Processor_XML",
      "text/html" => "CRM_Wrapi_Processor_UrlEncodedForm",
      "something/other/string" => "CRM_Wrapi_Processor_UrlEncodedForm",
    ];
    foreach($testData as $k => $v) {
      $_SERVER["CONTENT_TYPE"] = $k;
      $result = CRM_Wrapi_Processor_Base::detectContentType();
      $this->assertEquals($v, $result, "Invalid class returned.");
    }
  }
  /**
   * Sanitize string.
   */
  public function testSanitizeString() {
    $testData = [
      "this_is_kept_as_it_is" => "this_is_kept_as_it_is",
      "\"first_and_last_removed\"" => "first_and_last_removed",
      "'first_and_last_also_removed'" => "first_and_last_also_removed",
      "'middle_one'_is_kept'" => "middle_one'_is_kept",
      "without_html_<a href=\"site.com\" target=\"_blank\">link</a>_tags" => "without_html_link_tags",
    ];
    foreach($testData as $k => $v) {
      $result = CRM_Wrapi_Processor_Base::sanitizeString($k);
      $this->assertEquals($v, $result, "Invalid sanitized string returned.");
    }
  }
  public function testSanitize() {
    $testDataBasic = [
      "this_is_kept_as_it_is" => "this_is_kept_as_it_is",
      "\"first_and_last_removed\"" => "first_and_last_removed",
      "'first_and_last_also_removed'" => "first_and_last_also_removed",
      "'middle_one'_is_kept'" => "middle_one'_is_kept",
      "without_html_<a href=\"site.com\" target=\"_blank\">link</a>_tags" => "without_html_link_tags",
      3 => 3,
      0 => 0,
      -1 => -1,
      1.1 => 1.1,
      -0.3 => -0.3,
      true => true,
      false => false,
    ];
    $testDataArray = [
      [
        "input" => [],
        "expected" => null,
      ],
      [
        "input" => ["key" => 3.14],
        "expected" => ["key" => 3.14],
      ],
      [
        "input" => ["'key'" => 3.14],
        "expected" => ["key" => 3.14],
      ],
      [
        "input" => ["'constants'" => ["'pi'" => 3.14]],
        "expected" => ["constants" => ["pi" => 3.14]],
      ],
    ];
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    foreach($testDataBasic as $k => $v) {
      $result = $stub->sanitize($k);
      $this->assertEquals($v, $result, "Invalid sanitized value returned.");
    }
    foreach($testDataArray as $v) {
      $result = $stub->sanitize($v["input"]);
      $this->assertEquals($v["expected"], $result, "Invalid sanitized object returned.");
    }
  }
  public function testProcessInputInputsMissingSiteKey() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["user_key" => "test_user_key", "selector" => "test_selector"]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Site key missing", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsInvalidSiteKey() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["site_key" => 123, "user_key" => "test_user_key", "selector" => "test_selector"]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Site key not a string", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsMissingUserKey() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["site_key" => "test_site_key", "selector" => "test_selector"]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("User key missing", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsInvalidUserKey() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["site_key" => "test_site_key", "user_key" => 123, "selector" => "test_selector"]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("User key not a string", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsMissingSelector() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["site_key" => "test_site_key", "user_key" => "test_user_key"]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Selector missing", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsInvalidSelector() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $stub->method("input")
        ->willReturn(["site_key" => "test_site_key", "user_key" => "test_user_key", "selector" => 123]);
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Selector not a string", "Invalid exception message.");
    $this->assertEmpty($stub->processInput());
  }
  public function testProcessInputInputsValidData() {
    $stub = $this->getMockForAbstractClass('CRM_Wrapi_Processor_Base');
    $requestData = ["site_key" => "test_site_key", "user_key" => "test_user_key", "selector" => "test_selector"];
    $stub->method("input")
        ->willReturn($requestData);
    try {
      $this->assertEquals($requestData, $stub->processInput(), "Invalid processed input.");
    } catch (Exception $e) {
      $this->fail("Exception shouldn't be thrown for valid data.");
    }
  }
}
