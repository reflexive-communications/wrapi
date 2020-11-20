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
class CRM_Wrapi_Form_Route extends CRM_Wrapi_Form_Base
{
    /**
     * Form in edit mode? (or add)
     *
     * @var bool
     */
    protected bool $editMode;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        parent::preProcess();
        // Get current settings
//        $this->config = CRM_Wrapi_ConfigManager::loadConfig();
//
//        // Get route ID from request
//        $this->id = CRM_Utils_Request::retrieve('id', 'Positive');

        // Valid ID found --> edit mode
        if (!is_null($this->id)) {
            $this->editMode = true;
        } elseif (CRM_Utils_Rule::positiveInteger($this->_submitValues['id'])) {
            // No ID in request but valid ID in form --> use it
            $this->id = $this->_submitValues['id'];
            $this->editMode = true;
        } else {
            // No valid ID in form or request--> add mode
            $this->editMode = false;
        }
    }

    /**
     * Set default values
     *
     * @return array|NULL
     */
    public function setDefaultValues()
    {
        // Add mode
        if (!$this->editMode) {
            return null;
        }

        // Edit mode, set defaults to route data
        $route = $this->getRoute($this->id);

        $this->_defaults['name'] = $route['name'];
        $this->_defaults['action'] = $route['action'];
        $this->_defaults['handler_class'] = $route['handler'];

        return $this->_defaults;
    }

    /**
     * Build form
     *
     * @throws CRM_Core_Exception
     */
    public function buildQuickForm()
    {
        // Add form elements
        $this->add('hidden', 'id', $this->id);
        $this->add('text', 'name', ts('Route Name'), [], true);
        $this->add('text', 'action', ts('Action'), [], true);
        $this->add('text', 'handler_class', ts('Handler Class'), [], true);
        $this->addButtons(
            [
                [
                    'type' => 'cancel',
                    'name' => ts('Cancel'),
                ],
                [
                    'type' => 'done',
                    'name' => ts('Save'),
                    'isDefault' => true,
                ],
            ]
        );

        // Set title
        if ($this->editMode) {
            $this->setTitle(ts('Edit route'));
        } else {
            $this->setTitle(ts('Add new route'));
        }

        // Export edit mode to template
        $this->assign('edit_mode',$this->editMode);

        parent::buildQuickForm();
    }

    /**
     * Add form validation rules
     */
    public function addRules()
    {
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateTextFields']);
        $this->addFormRule(
            ['CRM_Wrapi_Form_Route', 'validateAction'],
            ['config' => $this->config, 'id' => $this->id,]
        );
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateHandler']);
    }

    /**
     * Validate text fields
     *
     * @param $values
     *
     * @return array|bool
     */
    protected function validateTextFields($values)
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
    protected function validateAction($values, $files, $options)
    {
        // Loop through existing routes
        foreach ($options['config']['routing_table'] as $id => $route) {

            // Skip self-checking
            if ($id == $options['id']) {
                continue;
            }

            // Duplicate found
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
    protected function validateHandler($values)
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
        // Assembly route data
        $route = [
            'name' => $this->_submitValues['name'],
            'action' => $this->_submitValues['action'],
            'handler' => $this->_submitValues['handler_class'],
        ];

        if ($this->editMode) {
            // Update
            $this->config['routing_table'][$this->id] = $route;
        } else {
            // Add
            $this->config['routing_table'][$this->config['next_id']] = $route;
            $this->config['next_id']++;
        }

        // Save
        if (!CRM_Wrapi_ConfigManager::saveConfig($this->config)) {
            CRM_Core_Session::setStatus(ts('Error while saving changes.'), 'WrAPI', 'error');
        };

        // Show success & redirect back to main
        if ($this->editMode) {
            $status = ts('Route updated.');
        } else {
            $status = ts('New route added.');
        }
        CRM_Core_Session::setStatus($status, '', 'success', ['expires' => 5000,]);
    }
}
