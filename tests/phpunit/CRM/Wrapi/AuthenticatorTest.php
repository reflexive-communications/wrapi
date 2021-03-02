<?php

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_Wrapi_AuthenticatorTest extends \PHPUnit\Framework\TestCase {

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
   * Check HTTP request method test.
   * For post method it shoudn't throw any exception.
   * For other methods, it should throw CRM_Core_Exception.
   */
  public function testCheckHTTPRequestMethod() {
    $invalidMethods = ["GET", "HEAD", "PUT"];
    foreach ($invalidMethods as $method) {
      $_SERVER["REQUEST_METHOD"] = $method;
      $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
      $this->expectExceptionMessage("Only POST method is allowed", "Invalid exception message.");
      $this->assertEmpty(CRM_Wrapi_Authenticator::checkHTTPRequestMethod());
    }
    $_SERVER["REQUEST_METHOD"] = "POST";
    try {
      $this->assertEmpty(CRM_Wrapi_Authenticator::checkHTTPRequestMethod());
    } catch (Exception $e) {
      $this->fail("It shouldn't throw exception.");
    }
  }
  /**
   * Authenticate site-key test.
   * If the CIVICRM_SITE_KEY is not defined, it throws exception.
   * If the CIVICRM_SITE_KEY is less than 8 char. it throws exception.
   * If the CIVICRM_SITE_KEY is different from the given site key, it throws exception.
   */
  public function testAuthenticateSiteKey() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  /**
   * Authenticate request test.
   */
  public function testAuthenticate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
}
