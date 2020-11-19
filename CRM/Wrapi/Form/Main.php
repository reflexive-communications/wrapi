<?php

use CRM_Wrapi_ExtensionUtil as E;

/**
 * WrAPI Main Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Wrapi_Form_Main extends CRM_Core_Form
{
    /**
     * Current WrAPI settings
     *
     * @var array|null
     */
    protected ?array $settings;

    /**
     * Enable debug
     *
     * @var bool
     */
    protected bool $debug;

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

            $this->_defaults['enable_debug'] = $this->debug ? 1 : 0;
        }

        return $this->_defaults;
    }

    /**
     * Preprocess form
     */
    public function preProcess()
    {
        // Get current settings
        $this->settings = Civi::settings()->get(CRM_Wrapi_Upgrader::EXTENSION_PREFIX);
        $this->debug = $this->settings['debug'] ?? false;
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
                    'name' => E::ts('Update settings'),
                    'isDefault' => true,
                ],
            ]
        );

        // Export form elements to template engine
//        $this->assign('enable_debug', $this->getRenderableElementNames());
        parent::buildQuickForm();
    }

    function addRules()
    {
        $this->addFormRule(
            [
                'CRM_Wrapi_Form_Main',
                'validateEnableDebug',
            ]
        );

    }

    public function validateEnableDebug($values)
    {
        $errors = [];

        $debug = $values['enable_debug'];
        if ($debug != "0" && $debug != "1") {
            $errors['enable_debug'] = ts('Please select yes or no');
        }

        return empty($errors) ? true : $errors;
    }

    public function postProcess()
    {
        if ($this->_submitValues['enable_debug'] xor $this->debug) {
            $this->settings['debug']=(bool)$this->_submitValues['enable_debug'];
            Civi::settings()->set(CRM_Wrapi_Upgrader::EXTENSION_PREFIX,$this->settings);
        }
    }

}
