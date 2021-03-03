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
class CRM_Wrapi_FactoryHeadlessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  public function testCreateConfigManager() {
    $manager = CRM_Wrapi_Factory::createConfigManager();
    $config = $manager->getAllConfig();
    $this->assertNotEmpty($config["next_id"], "Missing next_id.");
    $this->assertEquals(1, $config["next_id"], "Invalid next_id.");
    $this->assertEmpty($config["routing_table"], "Invalid routing_table.");
    $this->assertNotEmpty($config["config"], "Missing config.");
    $this->assertEquals(false, $config["config"]["debug"], "Invalid debug.");
  }
  public function testCreateProcessor() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  public function testCreateAuthenticatorDebugTrue() {
    $authenticator = CRM_Wrapi_Factory::createAuthenticator(true);
    $this->assertNotEmpty($authenticator, "Empty authenticator.");
  }
  public function testCreateAuthenticatorDebugFalse() {
    $authenticator = CRM_Wrapi_Factory::createAuthenticator(false);
    $this->assertNotEmpty($authenticator, "Empty authenticator.");
  }
  public function testCreateRouter() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  public function testCreateHandler() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  public function testCheckClass() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  public function testCheckInstance() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
}
