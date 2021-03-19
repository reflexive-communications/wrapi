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
    protected $editMode;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        parent::preProcess();

        // Adding or editing?
        if (!is_null($this->id)) {
            // Valid ID --> edit
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
        // Form now submitted --> no need to set defaults
        if ($this->isSubmitted()) {
            return null;
        }

        // Add mode
        if (!$this->editMode) {
            $this->_defaults['route_enabled'] = 1;
            $this->_defaults['log_level'] = PEAR_LOG_ERR;
            $this->_defaults['permissions'] = 'access CiviCRM';

            return $this->_defaults;
        }

        // Edit mode, set defaults to route data
        $route = $this->getRoute($this->id);

        // Valid ID but no route found --> switch to add mode
        if (empty($route)) {
            $this->_defaults['route_enabled'] = 1;

            return $this->_defaults;
        }

        // Set defaults
        $this->_defaults['name'] = $route['name'];
        $this->_defaults['selector'] = $route['selector'];
        $this->_defaults['handler_class'] = $route['handler'];
        $this->_defaults['route_enabled'] = $route['enabled'] ? 1 : 0;
        $this->_defaults['log_level'] = $route['log'];
        $this->_defaults['permissions'] = $route['perms'];

        // Convert options array to string
        $options = [];
        foreach ($route['opts'] as $key => $value) {
            $options[] = "${key}=${value}";
        }
        $this->_defaults['options'] = implode(PHP_EOL, $options);

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
        $this->add('hidden', 'id', $this->id);
        $this->add('hidden', 'route_enabled');
        $this->add('text', 'name', ts('Route Name'), [], true);
        $this->add('text', 'selector', ts('Selector'), [], true);
        $this->add('text', 'handler_class', ts('Handler Class'), [], true);
        $this->add('textarea', 'options', ts('Options'), ['rows' => 5]);
        $this->addSelect(
            'permissions',
            [
                'label' => ts('Permissions'),
                'options' => CRM_Core_Permission::basicPermissions(),
                'entity' => '',
                'multiple' => true,
            ]
        );
        $this->addRadio(
            'log_level',
            'Logging level',
            [
                PEAR_LOG_NONE => 'No logging',
                PEAR_LOG_DEBUG => 'Debug',
                PEAR_LOG_INFO => 'Info',
                PEAR_LOG_ERR => 'Error',
            ],
            [],
            null,
            true
        );
        $this->addButtons(
            [
                [
                    'type' => 'done',
                    'name' => ts('Save'),
                    'isDefault' => true,
                ],
                [
                    'type' => 'cancel',
                    'name' => ts('Cancel'),
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
        $this->assign('edit_mode', $this->editMode);
    }

    /**
     * Add form validation rules
     */
    public function addRules()
    {
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateTextFields']);
        $this->addFormRule(
            ['CRM_Wrapi_Form_Route', 'validateSelector'],
            ['config' => $this->config, 'id' => $this->id,]
        );
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateHandlerAndOptions']);
        $this->addFormRule(['CRM_Wrapi_Form_Route', 'validateLogLevel']);
    }

    /**
     * Validate text fields
     *
     * @param array $values Submitted values
     *
     * @return array|bool
     */
    protected function validateTextFields($values)
    {
        $errors = [];
        $name = $values['name'] ?? "";
        $selector = $values['selector'] ?? "";
        $handler_class = $values['handler_class'] ?? "";

        // Validate
        if (empty(CRM_Utils_String::stripSpaces($name))) {
            $errors['name'] = ts('Do not leave this field empty!');
        }
        if (empty(CRM_Utils_String::stripSpaces($selector))) {
            $errors['selector'] = ts('Do not leave this field empty!');
        }
        if (empty(CRM_Utils_String::stripSpaces($handler_class))) {
            $errors['handler_class'] = ts('Do not leave this field empty!');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate selector
     *
     * @param array $values Submitted values
     * @param array $files Uploaded files
     * @param array $options Options to pass to function
     *
     * @return array|bool
     */
    protected function validateSelector($values, $files, $options)
    {
        $errors = [];

        // Loop through existing routes
        foreach ($options['config']['routing_table'] as $id => $route) {

            // Skip self-checking
            if ($id == $options['id']) {
                continue;
            }

            // Duplicate found
            if ($route['selector'] == $values['selector']) {
                $errors['selector'] = ts(
                    'There is already a route with this selector: %1',
                    ['1' => $values['selector'],]
                );

                return $errors;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate handler and options
     *
     * @param array $values Submitted values
     *
     * @return array|bool
     */
    protected function validateHandlerAndOptions($values)
    {
        $handler = $values['handler_class'];

        // Check if handler class exists
        if (!class_exists($handler)) {
            $errors['handler_class'] = ts('Handler: %1 does not exists!', ['1' => $handler,]);

            return $errors;
        }

        // Check if handler is an actual wrAPI handler class
        if (!is_subclass_of($handler, CRM_Wrapi_Handler_Base::class)) {
            $errors['handler_class'] = ts('Handler: %1 is not a wrAPI handler!', ['1' => $handler,]);

            return $errors;
        }

        // Handler is a valid wrAPI handler --> check for required options (if they are set)
        if (method_exists($handler, 'requiredOptions')) {
            $required_options = $handler::requiredOptions();

            // Loop through required options
            $missing_options = [];
            foreach ($required_options as $option) {
                $pattern = "/^$option=\S+$/m";

                // Check for required option
                if (!preg_match($pattern, $values['options'])) {
                    $missing_options[] = $option;
                }
            }

            // There are missing options
            if (!empty($missing_options)) {
                $errors['options'] = ts('Required option(s): %1 missing!', ['1' => implode(', ', $missing_options)]);

                return $errors;
            }
        }

        return true;
    }

    /**
     * Validate Logging Level
     *
     * @param array $values Submitted values
     *
     * @return array|bool
     */
    protected function validateLogLevel($values)
    {
        $log_level = (int)$values['log_level'];

        if (!CRM_Utils_Rule::positiveInteger($log_level) || $log_level < PEAR_LOG_NONE || $log_level > PEAR_LOG_DEBUG) {
            $errors['log_level'] = ts('Not valid logging level');

            return $errors;
        }

        return true;
    }

    /**
     * Compose route data from submitted values
     *
     * @return array Route data
     *
     * @throws CRM_Core_Exception
     */
    protected function composeRouteData()
    {
        // Sanitize input
        $name = CRM_Wrapi_Processor_Base::sanitizeString($this->_submitValues['name']);
        $selector = CRM_Wrapi_Processor_Base::sanitizeString($this->_submitValues['selector']);
        $handler = CRM_Wrapi_Processor_Base::sanitizeString($this->_submitValues['handler_class']);

        // Parse permissions
        $permissions = [];
        foreach ($this->_submitValues['permissions'] as $item) {
            array_push($permissions, CRM_Wrapi_Processor_Base::sanitizeString($item));
        }

        // Convert options string to array
        $options = [];
        $lines = explode(PHP_EOL, $this->_submitValues['options']);
        foreach ($lines as $line) {
            // Parse lines
            $expression = explode('=', $line);
            $key = $expression[0];
            $value = $expression[1];

            // Skip empty
            if (empty($key) || empty($value)) {
                continue;
            }

            // Validate key
            $key = CRM_Wrapi_Processor_Base::sanitizeString($key);

            // Validate value according to apparent type
            if (CRM_Utils_Rule::numeric($value)) {
                $value = (int)$value;
            } elseif (CRM_Utils_Rule::boolean($value)) {
                $value = (bool)$value;
            } else {
                $value = CRM_Wrapi_Processor_Base::sanitizeString($value);
            }

            $options[$key] = $value;
        }

        return [
            'name' => $name,
            'selector' => $selector,
            'handler' => $handler,
            'enabled' => ($this->_submitValues['route_enabled'] == 1),
            'log' => (int)$this->_submitValues['log_level'],
            'perms' => $permissions,
            'opts' => $options,
        ];
    }

    /**
     * Post process form
     *
     * @throws CRM_Core_Exception
     */
    public function postProcess()
    {
        $route = $this->composeRouteData();

        if ($this->editMode) {
            // Update
            $this->config['routing_table'][$this->id] = $route;
        } else {
            // Add
            $this->config['routing_table'][$this->config['next_id']] = $route;
            $this->config['next_id']++;
        }

        // Save
        if (!$this->configManager->saveConfig($this->config)) {
            CRM_Core_Session::setStatus(ts('Error while saving changes'), 'WrAPI', 'error');

            return;
        }

        // Show success
        if ($this->editMode) {
            $status = ts('Route updated');
        } else {
            $status = ts('New route added');
        }
        CRM_Core_Session::setStatus($status, '', 'success', ['expires' => 5000,]);
    }
}
