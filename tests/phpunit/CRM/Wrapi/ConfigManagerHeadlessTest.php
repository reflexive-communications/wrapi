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
class CRM_Wrapi_ConfigManagerHeadlessTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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

  /**
   * Constructor test.
   * The config has to be the default.
   */
  public function testConstructor() {
    $config = new CRM_Wrapi_ConfigManager();
    $this->assertEquals("wrapi_config", $config::CONFIG_NAME, "Invalid config name.");
    $this->assertNotEmpty($config::DEFAULT_CONFIG[$config::CONFIG_NAME], "Missing configuration.");
    $this->assertNotEmpty($config::DEFAULT_CONFIG[$config::CONFIG_NAME]["next_id"], "Missing next_id.");
    $this->assertEquals(1, $config::DEFAULT_CONFIG[$config::CONFIG_NAME]["next_id"], "Invalid next_id.");
    $this->assertEmpty($config::DEFAULT_CONFIG[$config::CONFIG_NAME]["routing_table"], "Invalid routing_table.");
    $this->assertNotEmpty($config::DEFAULT_CONFIG[$config::CONFIG_NAME]["config"], "Missing config.");
    $this->assertEquals(false, $config::DEFAULT_CONFIG[$config::CONFIG_NAME]["config"]["debug"], "Invalid debug.");
  }
  public function testLoadConfigWithoutCreate() {
    $manager = new CRM_Wrapi_ConfigManager();
    $origConfig = $manager->getAllConfig();
    $manager->loadConfig();
    $this->assertEquals($origConfig, $manager->getAllConfig(), "Loaded config supposed to be the original.");
  }
  public function testCreateThenRemoveConfig() {
    $manager = new CRM_Wrapi_ConfigManager();
    $origConfig = $manager->getAllConfig();
    $result = $manager->createConfig();
    $this->assertTrue($result, "createConfig should return true.");
    $this->assertEquals($origConfig, $manager->getAllConfig(), "Create config should keep the orig values.");
    $result = $manager->removeConfig();
    $this->assertTrue($result, "removeConfig should return true.");
    $this->assertEmpty($manager->getAllConfig(), "After config removal, empty array should be returned.");
  }
  public function testLoadConfigWithCreate() {
    $manager = new CRM_Wrapi_ConfigManager();
    $origConfig = $manager->getAllConfig();
    $this->assertTrue($manager->createConfig(), "createConfig should return true.");
    $manager->loadConfig();
    $this->assertEquals($origConfig, $manager->getAllConfig(), "Load config should set the orig values.");
  }
  public function testSaveConfig() {
    $newConfig = [
      'next_id' => 1,
      'routing_table' => [],
      'config' => [
        'debug' => true,
      ],
    ];
    $manager = new CRM_Wrapi_ConfigManager();
    $result = $manager->createConfig();
    $this->assertTrue($result, "createConfig should return true.");
    $result = $manager->saveConfig($newConfig);
    $this->assertTrue($result, "saveConfig should return true.");
    $this->assertEquals($newConfig, $manager->getAllConfig(), "Save config should update the config values.");

  }
  public function testGetDebugModeForDefault() {
    $manager = new CRM_Wrapi_ConfigManager();
    $this->assertEquals(false, $manager->getDebugMode(), "Invalid debug.");
  }
  public function testGetRoutingTableForDefault() {
    $manager = new CRM_Wrapi_ConfigManager();
    $this->assertEmpty($manager->getRoutingTable(), "Invalid routing_table.");
  }
  public function testGetAllConfigForDefault() {
    $manager = new CRM_Wrapi_ConfigManager();
    $config = $manager->getAllConfig();
    $this->assertNotEmpty($config["next_id"], "Missing next_id.");
    $this->assertEquals(1, $config["next_id"], "Invalid next_id.");
    $this->assertEmpty($config["routing_table"], "Invalid routing_table.");
    $this->assertNotEmpty($config["config"], "Missing config.");
    $this->assertEquals(false, $config["config"]["debug"], "Invalid debug.");
  }
}
