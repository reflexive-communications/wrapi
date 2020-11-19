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
class CRM_Wrapi_Form_Main extends CRM_Core_Form
{
    /**
     * Current WrAPI config
     *
     * @var array
     */
    protected array $config;

    /**
     * This virtual function is used to set the default values of various form elements.
     *
     * @return array|NULL
     *   reference to the array of default values
     */
    public function setDefaultValues()
    {
        if (!$this->_defaults) {
            $this->_defaults = [];
            $this->_defaults['enable_debug'] = $this->config['config']['debug'] ? 1 : 0;
        }

        return $this->_defaults;
    }

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        $this->config = CRM_Wrapi_ConfigManager::loadConfig();
    }

    /**
     * Build form
     */
    public function buildQuickForm()
    {
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

        // Add handler button
        $add_route_url = CRM_Utils_System::url('civicrm/wrapi/route');
        $this->assign('add_route_url', $add_route_url);

        // Export routes to template
        $this->assign('routes', $this->config['routing_table']);

        parent::buildQuickForm();
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
     * @param $values
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
     */
    public function postProcess()
    {
        // If setting has changed
        if ($this->_submitValues['enable_debug'] xor $this->config['config']['debug']) {

            $this->config['config']['debug'] = (bool)$this->_submitValues['enable_debug'];

            // Save
            if (!CRM_Wrapi_ConfigManager::saveConfig($this->config)) {
                throw new CRM_Core_Exception('Error while saving changes.');
            };
        }

        // Show success even there is no change --> don't confuse users
        CRM_Core_Session::setStatus(ts('Settings updated'), '', 'success', ['expires' => 5000,]);
    }
}
