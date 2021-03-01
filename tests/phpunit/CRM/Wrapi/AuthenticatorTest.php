<?php

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_Wrapi_AuthenticatorTest extends \PHPUnit\Framework\TestCase {

  const API_KEY = "myapikey";

  /**
  * @param $object
  * @param string $method
  * @param array $parameters
  * @return mixed
  * @throws \Exception
  * based on this article: https://www.omadoyeabraham.com/2019-06-03/testing-private-protected-methods
  */
  private function callMethod($object, string $method , array $parameters = []) {
    try {
      $className = get_class($object);
      $reflection = new \ReflectionClass($className);
    } catch (\ReflectionException $e) {
     throw new \Exception($e->getMessage());
    }

    $method = $reflection->getMethod($method);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }
  /**
   * It creates a contact with an api key using the cv command.
   */
  private function createContact() {
    cv('api Contact.create contact_type="Individual" first_name="Bobin" last_name="McGyver" api_key="'.self::API_KEY.'"');
  }
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
   * Authenticate user-key test.
   * If contact is not found with the given user key or cms user is not found for the contact
   * it throws exception.
   */
  public function testAuthenticateUserKeyEmptyUserDataWithoutDebugFlag() {
    $a = new CRM_Wrapi_Authenticator(false);
    // empty user key -> exception from DAO
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("getFieldValue failed", "Invalid exception message.");
    $this->assertEmpty($this->callMethod($a, "authenticateUserKey", [""]));
  }
  public function testAuthenticateUserKeyInvalidUserDataWithoutDebugFlag() {
    $a = new CRM_Wrapi_Authenticator(false);
    // something random string as user key -> exception
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Failed to authenticate key", "Invalid exception message.");
    $this->assertEmpty($this->callMethod($a, "authenticateUserKey", ["random-string"]));
  }
  /**
   * Authenticate user-key test.
   * Valid contact data.
   */
  public function testAuthenticateUserKeyValidUserDataWithoutDebugFlag() {
    $a = new CRM_Wrapi_Authenticator(false);
    // valid user key shouldn't throw exception.
    // a valid user has to be created and its api-key value has to be set.
    try {
      $this->createContact();
    } catch (Exception $e) {
      $this->fail("Contact creation failed.");
    }
    $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
    $this->expectExceptionMessage("Failed to authenticate key", "Invalid exception message.");
    $this->assertEmpty($this->callMethod($a, "authenticateUserKey", [self::API_KEY]));
  }
  /**
   * Authenticate request test.
   */
  public function testAuthenticate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
}
