<?php

/**
 * WrAPI Route Form controller
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Form_Route extends CRM_Core_Form
{
    /**
     * Current WrAPI config
     *
     * @var array
     */
    protected array $config;

    /**
     * Preprocess form
     */
    public function preProcess()
    {
        // Get current settings
        $this->config = CRM_Wrapi_ConfigManager::loadConfig();
    }

    /**
     * Build form
     *
     * @throws CRM_Core_Exception
     */
    public function buildQuickForm()
    {
        // Add form elements
        $this->add('text', 'name', ts('Route Name'), [], true);
        $this->add('text', 'action', ts('Action'), [], true);
        $this->add('text', 'handler_class', ts('Handler Class'), [], true);
        $this->addButtons(
            [
                [
                    'type' => 'done',
                    'name' => ts('Save'),
                    'isDefault' => true,
                ],
            ]
        );

        // Back to main form
        $main_form_url = CRM_Utils_System::url('civicrm/wrapi/main');
        $this->assign('main_form_url', $main_form_url);

        parent::buildQuickForm();
    }

    /**
     * Add form validation rules
     */
    public function addRules()
    {
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateTextFields']);
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateAction'], $this->config);
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateHandler']);
    }

    /**
     * Validate text fields
     *
     * @param $values
     *
     * @return array|bool
     */
    public function validateTextFields($values)
    {
        $errors = [];
        $name = $values['name'] ?? "";
        $action = $values['action'] ?? "";
        $handler_class = $values['handler_class'] ?? "";

        // Validate
        if (empty(CRM_Utils_String::stripSpaces($name))) {
            $errors['name'] = ts('Do not leave this field empty!');
        }
        if (empty(CRM_Utils_String::stripSpaces($action))) {
            $errors['action'] = ts('Do not leave this field empty!');
        }
        if (empty(CRM_Utils_String::stripSpaces($handler_class))) {
            $errors['handler_class'] = ts('Do not leave this field empty!');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate action
     *
     * @param $values
     * @param $files
     * @param $options
     *
     * @return bool
     */
    public function validateAction($values, $files, $options)
    {
        // Loop through existing routes
        foreach ($options['routing_table'] as $route) {
            if ($route['action'] == $values['action']) {
                $errors['action'] = ts(
                    'There is already a route with this action: %1',
                    ['1' => $values['action'],]
                );

                return $errors;
            }
        }

        return true;
    }

    /**
     * Validate handler
     *
     * @param $values
     *
     * @return bool
     */
    public function validateHandler($values)
    {
        if (!class_exists($values['handler_class'])) {
            $errors['handler_class'] = ts('Handler: %1 does not exists!', ['1' => $values['handler_class'],]);

            return $errors;
        }

        return true;
    }

    /**
     * Post process form
     */
    public function postProcess()
    {
        // Assembly route
        $route = [
            'id' => $this->config['next_id'],
            'name' => $this->_submitValues['name'],
            'action' => $this->_submitValues['action'],
            'handler' => $this->_submitValues['handler_class'],
        ];

        // Update configs
        $this->config['routing_table'][] = $route;
        $this->config['next_id']++;

        // Save
        if (!CRM_Wrapi_ConfigManager::saveConfig($this->config)) {
            CRM_Core_Session::setStatus('Error while saving changes.', 'WrAPI', 'error');
        };

        // Show success & redirect back to main
        CRM_Core_Session::setStatus(ts('New route added'), '', 'success', ['expires' => 5000,]);
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/wrapi/main'));
    }
}
