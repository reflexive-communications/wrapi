<?php

/**
 * WrAPI Main Form controller
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Form_Main extends CRM_Wrapi_Form_Base
{
    /**
     * This virtual function is used to set the default values of various form elements.
     *
     * @return array|NULL
     *   reference to the array of default values
     */
    public function setDefaultValues()
    {
        $this->_defaults['enable_debug'] = $this->config['config']['debug'] ? 1 : 0;

        return $this->_defaults;
    }

    /**
     * Build form
     *
     * @throws CRM_Core_Exception
     */
    public function buildQuickForm()
    {
        parent::buildQuickForm();

        // Add form elements
        $this->addYesNo('enable_debug', 'Enable debug', false, true,);
        $this->addButtons(
            [
                [
                    'type' => 'submit',
                    'name' => ts('Update settings'),
                    'isDefault' => true,
                ],
            ]
        );

        // Add new route button
        $add_route_url = CRM_Utils_System::url('civicrm/wrapi/route');
        $this->assign('add_route_url', $add_route_url);

        // Get routes
        $routes = $this->configManager->getRoutingTable();

        // Add actions links
        foreach ($routes as $id => $route) {
            $actions = array_sum(array_keys($this->links()));

            // Remove update enable/disable link
            if ($route['enabled']) {
                $actions -= CRM_Core_Action::ENABLE;
            } else {
                $actions -= CRM_Core_Action::DISABLE;
            }

            $routes[$id]['actions'] = CRM_Core_Action::formLink(
                $this->links(),
                $actions,
                ['id' => $id],
                ts('more')
            );
        }

        // Export routes to template
        $this->assign('routes', $routes);

        // Add css, JS resources
        Civi::resources()->addStyleFile(CRM_Wrapi_ExtensionUtil::LONG_NAME, 'css/wrapi.css');
        Civi::resources()->addScriptFile(CRM_Wrapi_ExtensionUtil::LONG_NAME, 'js/Form/Main.js');
    }

    /**
     * Add form validation rules
     */
    public function addRules()
    {
        $this->addFormRule(['CRM_Wrapi_Form_Main', 'validateEnableDebug',]);
    }

    /**
     * Validate debug selector
     *
     * @param array $values Submitted values
     *
     * @return array|bool
     */
    public function validateEnableDebug($values)
    {
        $errors = [];
        $debug = $values['enable_debug'];

        if ($debug != "0" && $debug != "1") {
            $errors['enable_debug'] = ts('Please select yes or no');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Post process form
     *
     * @throws CRM_Core_Exception
     */
    public function postProcess()
    {
        // If setting has changed
        if ($this->_submitValues['enable_debug'] xor $this->config['config']['debug']) {

            $this->config['config']['debug'] = (bool)$this->_submitValues['enable_debug'];

            // Save
            if (!$this->configManager->saveConfig($this->config)) {
                throw new CRM_Core_Exception('Error while saving changes');
            }
        }

        // Show success even there is no change --> don't confuse users
        CRM_Core_Session::setStatus(ts('Settings updated'), '', 'success', ['expires' => 5000,]);
    }

    /**
     * Get action Links.
     *
     * @return array Action links
     */
    public function links(): array
    {
        return [
            CRM_Core_Action::UPDATE => [
                'name' => ts('Edit'),
                'url' => 'civicrm/wrapi/route',
                'qs' => 'id=%%id%%',
                'title' => ts('Edit route'),
                'class' => 'crm-popup wrapi-action',
            ],
            CRM_Core_Action::DISABLE => [
                'name' => ts('Disable'),
                'url' => 'civicrm/wrapi/route/actions',
                'qs' => 'action=disable&id=%%id%%',
                'title' => ts('Disable route'),
                'class' => 'wrapi-ajax-action',
            ],
            CRM_Core_Action::ENABLE => [
                'name' => ts('Enable'),
                'url' => 'civicrm/wrapi/route/actions',
                'qs' => 'action=enable&id=%%id%%',
                'title' => ts('Enable route'),
                'class' => 'wrapi-ajax-action',
            ],
            CRM_Core_Action::DELETE => [
                'name' => ts('Delete'),
                'url' => 'civicrm/wrapi/route/delete',
                'qs' => 'id=%%id%%',
                'title' => ts('Delete route'),
                'class' => 'crm-popup wrapi-action',
            ],
        ];
    }
}
