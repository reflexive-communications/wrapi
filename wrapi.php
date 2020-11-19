<?php

require_once 'wrapi.civix.php';

use CRM_Wrapi_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function wrapi_civicrm_config(&$config)
{
    _wrapi_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function wrapi_civicrm_xmlMenu(&$files)
{
    _wrapi_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function wrapi_civicrm_install()
{
    _wrapi_civix_civicrm_install();

    $installer = _wrapi_civix_upgrader();
    $installer->install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function wrapi_civicrm_postInstall()
{
    _wrapi_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function wrapi_civicrm_uninstall()
{
    _wrapi_civix_civicrm_uninstall();

    $installer = _wrapi_civix_upgrader();
    $installer->uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function wrapi_civicrm_enable()
{
    _wrapi_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function wrapi_civicrm_disable()
{
    _wrapi_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function wrapi_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _wrapi_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function wrapi_civicrm_managed(&$entities)
{
    _wrapi_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function wrapi_civicrm_caseTypes(&$caseTypes)
{
    _wrapi_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function wrapi_civicrm_angularModules(&$angularModules)
{
    _wrapi_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function wrapi_civicrm_alterSettingsFolders(&$metaDataFolders = null)
{
    _wrapi_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function wrapi_civicrm_entityTypes(&$entityTypes)
{
    _wrapi_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function wrapi_civicrm_themes(&$themes)
{
    _wrapi_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function wrapi_civicrm_navigationMenu(&$menu)
{
    _wrapi_civix_insert_navigation_menu(
        $menu,
        'Administer',
        [
            'label' => E::ts('WrAPI'),
            'name' => 'wrapi_main',
            'url' => 'civicrm/wrapi/main',
            'permission' => 'administer CiviCRM',
            'separator' => 2,
            'active' => 1,
        ]
    );
    _wrapi_civix_navigationMenu($menu);
}
