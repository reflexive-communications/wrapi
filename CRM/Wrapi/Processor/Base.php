<?php

/**
 * Base IO Processor
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
abstract class CRM_Wrapi_Processor_Base
{
    /**
     * Parse input
     *
     * @return mixed
     */
    abstract public function input();

    /**
     * Detect content-type
     *
     * @return string
     */
    public static function detectContentType(): string
    {
        // If content-type is set use it
        if (isset($_SERVER['CONTENT_TYPE'])) {
            switch ($_SERVER['CONTENT_TYPE']) {
                case 'application/json':
                case 'application/javascript':
                    return CRM_Wrapi_Processor_JSON::class;
                case 'text/xml':
                case 'application/xml':
                    return CRM_Wrapi_Processor_XML::class;
                default:
                    return CRM_Wrapi_Processor_UrlEncodedForm::class;
            }
        }

        // Fallback to URL encoded
        return CRM_Wrapi_Processor_UrlEncodedForm::class;
    }

    /**
     * Perform basic input sanitization
     *
     * @param mixed $input Input to sanitize
     *
     * @return array|string
     *
     * @throws CRM_Core_Exception
     */
    public function sanitize($input)
    {
        $sanitized = [];

        // Input is array --> sanitize values and keys
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                // Sanitize key
                $key = CRM_Utils_String::stripSpaces($key);
                $key = self::sanitizeString($key);

                // Sanitize value
                if (is_array($value)) {
                    // Array --> recurse
                    $value = $this->sanitize($value);
                } elseif (is_int($value)) {
                    // Integer
                    $value = CRM_Utils_Type::validate($value, 'Int');
                } elseif (is_float($value)) {
                    // Float
                    $value = CRM_Utils_Type::validate($value, 'Float');
                } elseif (is_bool($value)) {
                    // Boolean
                    $value = CRM_Utils_Type::validate($value, 'Boolean');
                } else {
                    // Nothing else --> string
                    $value = self::sanitizeString($value);
                }

                $sanitized[$key] = $value;
            }
        } elseif (is_int($input)) {
            // Input is single integer
            $sanitized = CRM_Utils_Type::validate($input, 'Int');
        } elseif (is_float($input)) {
            // Input is single float
            $sanitized = CRM_Utils_Type::validate($input, 'Float');
        } elseif (is_bool($input)) {
            // Input is single boolean
            $sanitized = CRM_Utils_Type::validate($input, 'Boolean');
        } else {
            // Input is single string
            $sanitized = self::sanitizeString($input);
        }

        return $sanitized;
    }

    /**
     * Sanitize string
     *
     * @param $value
     *
     * @return string
     *
     * @throws CRM_Core_Exception
     */
    public static function sanitizeString($value)
    {
        // Strip whitespace
        $value = CRM_Utils_String::stripSpaces($value);
        // Escape string
        $value = CRM_Utils_Type::escape($value, 'String');
        // Remove XSS
        $value = CRM_Utils_String::purifyHTML($value);

        return $value;
    }

    /**
     * Return output to client in JSON format
     *
     * @param mixed $result Result to output
     */
    public function output($result): void
    {
        header('Content-Type: application/json');
        echo json_encode($result);
        CRM_Utils_System::civiExit();
    }

    /**
     * Process request
     *
     * @return array|string
     *
     * @throws CRM_Core_Exception
     */
    public function processInput()
    {
        // Parse request data
        $request_data = $this->input();

        // Check if required params (keys, action) supplied and valid strings
        $this->validateKeyInputs($request_data);

        return $request_data;
    }

    /**
     * Validate input
     *
     * @param $value
     * @param string $type
     * @param string $name
     * @param bool $required
     *
     * @throws CRM_Core_Exception
     */
    public static function validateInput($value, string $type, string $name, bool $required = true): void
    {
        // If required input --> check if empty
        if ($required && empty($value)) {
            throw new CRM_Core_Exception(sprintf('Missing parameter: %1', $name));
        }

        switch ($type) {
            case 'string':
                $valid = CRM_Utils_Rule::string($value);
                break;
            case 'email':
                $valid = CRM_Utils_Rule::email($value);
                break;
            case 'id':
                $valid = CRM_Utils_Rule::positiveInteger($value);
                break;
            default:
                throw new CRM_Core_Exception('Not supported type');
        }

        if (!$valid) {
            throw new CRM_Core_Exception(sprintf('%1 is not type of: %2', $name, $type));
        }
    }

    /**
     * Validate inputs (keys, action)
     *
     * @param mixed $request_params Request data
     *
     * @throws CRM_Core_Exception
     */
    protected function validateKeyInputs($request_params): void
    {
        // Get parameters
        $site_key = $request_params['site_key'] ?? "";
        $user_key = $request_params['user_key'] ?? "";
        $selector = $request_params['selector'] ?? "";

        // Check if supplied
        if (empty($site_key)) {
            throw new CRM_Core_Exception('Site key missing');
        }
        if (empty($user_key)) {
            throw new CRM_Core_Exception('User key missing');
        }
        if (empty($selector)) {
            throw new CRM_Core_Exception('Selector missing');
        }

        // Check if string
        if (!CRM_Utils_Rule::string($site_key)) {
            throw new CRM_Core_Exception('Site key not a string');
        }
        if (!CRM_Utils_Rule::string($user_key)) {
            throw new CRM_Core_Exception('User key not a string');
        }
        if (!CRM_Utils_Rule::string($selector)) {
            throw new CRM_Core_Exception('Selector not a string');
        }
    }
}
