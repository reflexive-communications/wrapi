<?php

use CRM_Wrapi_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Wrapi_AuthenticatorHeadlessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  const API_KEY = "myapikey";

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

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

  public function setUp(): void {
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }
  /**
   * It creates a contact with an api key using the cv command.
   */
  private function createContact() {
    cv('api Contact.create contact_type="Individual" first_name="Bobin" last_name="McGyver" api_key="'.self::API_KEY.'"');
  }

  /**
   * Example: Test that a version is returned.
   */
  public function testWellFormedVersion() {
    $this->assertNotEmpty(E::SHORT_NAME);
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', \CRM_Utils_System::version());
  }

  /**
   * Example: Test that we're using a fake CMS.
   */
  public function testWellFormedUF() {
    $this->assertEquals('UnitTests', CIVICRM_UF);
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

}
