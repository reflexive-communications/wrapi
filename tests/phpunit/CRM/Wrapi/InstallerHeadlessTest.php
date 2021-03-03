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
class CRM_Wrapi_InstallerHeadlessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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
  public function testInstall() {
    $installer = new CRM_Wrapi_Installer("wrapi", ".");
    try {
      $this->assertEmpty($installer->install());
    } catch (Exception $e) {
      $this->fail("Should not throw exception.");
    }
  }
  public function testUninstall() {
    $installer = new CRM_Wrapi_Installer("wrapi", ".");
    $this->assertEmpty($installer->install());
    try {
      $this->assertEmpty($installer->uninstall());
    } catch (Exception $e) {
      $this->fail("Should not throw exception.");
    }
  }
}
