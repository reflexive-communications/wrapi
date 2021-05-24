<?php

use Civi\API\Exception\NotImplementedException;
use Civi\API\Exception\UnauthorizedException;

/**
 * Base Handler
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
abstract class CRM_Wrapi_Handler_Base
{
    /**
     * Request processed message
     */
    public const REQUEST_PROCESSED = 'Request processed';

    /**
     * Request Data
     *
     * @var null|array
     */
    protected $requestData;

    /**
     * Logging Level
     *
     * @var int
     */
    protected $logLevel;

    /**
     * Required permissions
     *
     * @var array
     */
    protected $permissions;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * File logger
     *
     * @var Log_file
     */
    protected $logger;

    /**
     * CRM_Wrapi_Handler_Base constructor.
     *
     * @param array|null $request_data Request data
     * @param int $logging_level Logging level
     * @param array $permissions Required permissions
     * @param array $options Options
     * @param Log_file $file_logger File logger
     */
    public function __construct(
        ?array $request_data,
        int $logging_level,
        array $permissions,
        array $options,
        Log_file $file_logger
    ) {
        $this->requestData = $request_data;
        $this->logLevel = $logging_level;
        $this->permissions = $permissions;
        $this->options = $options;
        $this->logger = $file_logger;
    }

    /**
     * Process Request
     */
    abstract protected function process();

    /**
     * Return request parameter rules
     *
     * @return array Input rules
     *
     * Properties:
     *   - type:     Type of field (string | email | int | id | float | bool | date | datetime | list)
     *   - name:     Name of field
     *   - required: Is required field (true | false)
     *   - default:  Default value
     *   - elements: Definition for list elements (only for list type)
     */
    abstract protected function inputRules(): array;

    /**
     * Handle request
     *
     * @return mixed
     *
     * @throws CRM_Core_Exception
     */
    public function run()
    {
        // Check permissions
        $this->checkPermissions();

        // Log incoming request according to logging level
        $this->logIncomingRequest();

        // Validate request data
        $this->validate($this->requestData, $this->inputRules());

        // Process request
        return $this->process();
    }

    /**
     * Check if current user (based on user-key) has required permissions
     *
     * @throws CRM_Core_Exception
     */
    protected function checkPermissions()
    {
        foreach ($this->permissions as $permission) {
            if (!CRM_Core_Permission::check($permission)) {
                throw new CRM_Core_Exception(sprintf('Required permission missing: %s', $permission));
            }
        }
    }

    /**
     * Log incoming request
     */
    protected function logIncomingRequest()
    {
        $message = sprintf('Request received  Selector: %s', $this->requestData['selector']);

        // Log request received
        $this->info($message);

        // Also log request data for debug
        $data = $this->requestData;

        // Exclude sensitive data from logging
        unset($data['site_key']);
        unset($data['user_key']);
        // Already logged
        unset($data['selector']);

        $message .= ' Data: '.serialize($data);
        $this->debug($message);
    }

    /**
     * Validate Request Data
     *
     * @param mixed $data Data to validate
     * @param array $rules Validation rules
     *
     * @throws CRM_Core_Exception
     */
    protected function validate($data, array $rules): void
    {
        // Loop through input rules
        foreach ($rules as $field => $rule) {
            // Get rule details
            $type = $rule['type'] ?? "";
            $name = $rule['name'] ?? "";
            $required = (bool)($rule['required'] ?? false);
            $allowed_values = $rule['values'] ?? [];
            $elements = $rule['elements'] ?? [];

            // Validate input fields

            // Field is a list
            if ($type == "list") {
                // Check child elements (recursively)
                foreach ($data[$field] as $item) {
                    $this->validate($item, $elements);
                }
            } else {
                if (is_array($data)) {
                    $value = $data[$field];
                } else {
                    $value = $data;
                }
                CRM_Wrapi_Processor_Base::validateInput($value, $type, $name, $required, $allowed_values);
            }
        }
    }

    /**
     * Set default values
     *
     * Loop through input rules, and checks the received data,
     * if there is default specified and data is not set then set value to default
     *
     * @param mixed $data Data to check
     * @param array $rules Validation rules
     *
     * @return mixed Data with defaults
     *
     * @throws CRM_Core_Exception
     */
    protected function setDefaultValues($data, array $rules)
    {
        $data_with_defaults = null;

        // Loop through input rules
        foreach ($rules as $field => $rule) {
            // Get rule details
            $type = $rule['type'] ?? "";
            $elements = $rule['elements'] ?? [];
            $default = $rule['default'] ?? null;

            // Field is a list
            if ($type == "list") {
                // If list is empty --> check for default --> if there is a default, use it
                if (!isset($data[$field])) {
                    if (isset($default)) {
                        $data_with_defaults[$field] = $default;
                    }
                } else {
                    // List not empty --> loop through elements, and recurse into children
                    foreach ($data[$field] as $item) {
                        $data_with_defaults[$field] = $this->setDefaultValues($item, $elements);
                    }
                }
                continue;
            }

            // Data is an array
            if (is_array($data)) {
                if (!isset($data[$field])) {
                    // Data not set, if there is a default --> use it
                    // If there is no default --> then skip this field
                    if (isset($default)) {
                        $data_with_defaults[$field] = $default;
                    }
                } else {
                    // Data set --> copy value
                    $data_with_defaults[$field] = $data[$field];
                }
                continue;
            }

            // Data not a list, not an array --> primitive type
            if (!isset($data)) {
                if (isset($default)) {
                    $data_with_defaults = $default;
                }
            } else {
                $data_with_defaults = $data;
            }
        }

        return $data_with_defaults;
    }

    /**
     * Log request processed
     */
    protected function logRequestProcessed()
    {
        $message = sprintf('Request processed Selector: %s', $this->requestData['selector']);
        $this->info($message);
    }

    /**
     * Write debug message to log if current log level is DEBUG
     *
     * @param string $message Message to log
     */
    protected function debug(string $message)
    {
        if ($this->logLevel >= PEAR_LOG_DEBUG) {
            // Message to log
            $log = "${_SERVER['REMOTE_ADDR']} ${message}";
            $this->logger->debug($log);
        }
    }

    /**
     * Write info message to log if current log level at least INFO
     *
     * @param string $message Message to log
     */
    protected function info(string $message)
    {
        if ($this->logLevel >= PEAR_LOG_INFO) {
            // Message to log
            $log = "${_SERVER['REMOTE_ADDR']} ${message}";
            $this->logger->info($log);
        }
    }

    /**
     * Write error message to log if current log level at least ERROR
     *
     * @param string $message Message to log
     */
    protected function err(string $message)
    {
        if ($this->logLevel >= PEAR_LOG_ERR) {
            // Message to log
            $log = "${_SERVER['REMOTE_ADDR']} ${message}";
            $this->logger->err($log);
        }
    }

    /**
     * Map fields in input to fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     *
     * @return array Mapped data
     */
    protected function mapFieldsString(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                $mapped_data[$field_mapped] = $value;
            }
        }

        return $mapped_data;
    }

    /**
     * Map fields in input to integer fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     *
     * @return array Mapped data
     */
    protected function mapFieldsInteger(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                $mapped_data[$field_mapped] = (int)$value;
            }
        }

        return $mapped_data;
    }

    /**
     * Map fields in input to float fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     *
     * @return array Mapped data
     */
    protected function mapFieldsFloat(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                $mapped_data[$field_mapped] = (float)$value;
            }
        }

        return $mapped_data;
    }

    /**
     * Map Bool fields in input to fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     *
     * @return array Mapped data
     */
    protected function mapFieldsBool(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];
            if (isset($value)) {
                $true_values = [true, 1, 'Yes', 'yes'];

                if (in_array($value, $true_values, true)) {
                    $mapped_data[$field_mapped] = 1;
                } else {
                    $mapped_data[$field_mapped] = 0;
                }
            }
        }

        return $mapped_data;
    }

    /**
     * Map ISO8601 DateTime fields in input to MySQL DateTime fields specified by mapping
     *
     * @param array $request_data Input data
     * @param array $mapping Mapping rules
     *  format: [
     *      'input_field_1_name => 'mapped_field_name_1,
     *      'input_field_2_name => 'mapped_field_name_2,
     *  ]
     *
     * @return array Mapped data
     */
    protected function mapFieldsDateTimeISO8601(array $request_data, array $mapping): array
    {
        $mapped_data = [];

        // Loop through mapping
        foreach ($mapping as $field_in_request => $field_mapped) {
            $value = $request_data[$field_in_request];

            if (isset($value)) {
                // Parse ISO8601 Date
                $iso8601_date = DateTime::createFromFormat("Y-m-d\TH:i:s.uP", $value);
                // Convert to MySQL Date
                $mapped_data[$field_mapped] = $iso8601_date->format("Y-m-d H:i:s");
            }
        }

        return $mapped_data;
    }

    /**
     * Save contact
     *
     * Look up contact by external ID
     * If already present in DB --> update contact
     * If not --> create new contact
     *
     * @param string|null $external_id External ID
     * @param array $record Contact data
     *   This array is structured by entities. All relevant fields for each entity (contact, email, phone, address,
     *   relationship) must be in separate arrays and indexed by the entity name. Not all entity have to be in the array,
     *   say we don't want to save any address, simple don't include data. Also not all fields for a given entity is
     *   required to be present in the array, only fields that we want to save. However there are required fields
     *   for each entity (e.g. contact_id, location_type_id for email), but it is not checked here, it should be
     *   enforced earlier in the validate phase, or leave it to \Civi\Api4 to complain :)
     *
     *   Note:
     *     - There is no need to include contact ID for Email, Phone, etc entities (though they are required by Api4).
     *       That will be handled by the method.
     *     - Currently it is not possible to set different types of email, phone, address (main, work, home) in one call.
     *       If you need to save more emails, you have to call this method again.
     *     - It is possible to give several relationship in one call, in fact relationship data have to be given in array. See example.
     *
     *   Example:
     *     $record = [
     *       'contact' => [
     *         'first_name' => 'John',
     *         'last_name' => 'Doe',
     *         'contact_type' => 'Individual',
     *         'external_identifier' => '1234',
     *         'job_title' => 'Big Boss',
     *       ],
     *       'email' => [
     *         'email' => 'john@big.corp',
     *         'location_type_id' => '1',
     *       ],
     *       'relationship' => [
     *           [
     *             'contact_id_b' => '145',
     *             'relationship_type_id' => '1',
     *           ],
     *           [
     *             'contact_id_b' => '111',
     *             'relationship_type_id' => '2',
     *           ],
     *         ],
     *       ];
     *
     * @return int Contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveContactByExternalID(?string $external_id, array $record): int
    {
        if (empty($external_id)) {
            throw new CRM_Core_Exception('External ID missing');
        }

        $contact_id = CRM_Wrapi_Actions_Get::contactIDFromExternalID($external_id);

        return $this->saveContact($contact_id, $record);
    }

    /**
     * Save contact
     *
     * Look up contact by email
     * If already present in DB --> update contact
     * If not --> create new contact
     *
     * @param string|null $email Email address
     * @param array $record Contact data. For details @see saveContactByExternalID
     *
     * @return int Contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveContactByEmail(?string $email, array $record): int
    {
        if (empty($email)) {
            throw new CRM_Core_Exception('Email address missing');
        }

        $contact_id = CRM_Wrapi_Actions_Get::contactIDFromEmail($email);

        return $this->saveContact($contact_id, $record);
    }

    /**
     * Save contact with related entities
     *
     * If a valid contact ID is supplied then update that contact
     * If not then create new contact
     *
     * @param int|null $contact_id Contact ID
     * @param array $record Contact data. For details @see saveContactByExternalID
     *
     * @return int Contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveContact(?int $contact_id, array $record): int
    {
        // Save Contact entity
        // If contact ID is null here, it is not a problem, a contact will be created
        // If contact ID is not null it won't change (hopefully :))
        $contact_id = $this->saveContactEntity($contact_id, $record['contact']);

        // Now there must be a contact ID
        if (is_null($contact_id)) {
            throw new CRM_Core_Exception('Failed to retrieve contact');
        }

        // Save related entities
        $this->saveEmailEntity($contact_id, $record['email']);
        $this->savePhoneEntity($contact_id, $record['phone']);
        $this->saveAddressEntity($contact_id, $record['address']);

        foreach ($record['relationship'] as $relationship_data) {
            $this->saveRelationshipEntity($contact_id, $relationship_data);
        }

        return $contact_id;
    }

    /**
     * Save Contact entity
     *
     * If a valid contact ID is supplied then update that contact
     * If not then create new contact
     *
     * @param int|null $contact_id Contact ID
     * @param array|null $contact_data Contact data
     *   This contains the fields to save, it should be in a format which can be fed to civicrm_api4() calls.
     *   See Api Explorer v4
     *
     *   Example:
     *   $contact_data = [
     *     'first_name' => 'Janos',
     *     'postal_greeting_id' => '1,
     *     'preferred_language' => 'hu_HU',
     *   ];
     *
     * @return int|null Created contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    protected function saveContactEntity(?int $contact_id, ?array $contact_data): ?int
    {
        if (empty($contact_data)) {
            return null;
        }

        // Create or update
        if (is_null($contact_id)) {
            // Null contact ID --> create new
            $contact_id = CRM_Wrapi_Actions_Create::contact($contact_data);
            $this->debug(sprintf('Contact created (Contact ID: %s)', $contact_id));
        } else {
            // Valid contact ID --> update present
            CRM_Wrapi_Actions_Update::contact($contact_id, $contact_data);
            $this->debug(sprintf('Contact updated (Contact ID: %s)', $contact_id));
        }

        return $contact_id;
    }

    /**
     * Save Email entity
     *
     * If an email address is already present for a contact and location type pair then update that address
     * If not present then add new email
     *
     * @param int $contact_id Contact ID
     * @param array|null $email_data Email data
     *   This contains the fields to save, it should be in a format which can be fed to civicrm_api4() calls.
     *   See Api Explorer v4
     *
     *   Example:
     *   $email_data = [
     *     'location_type_id' => 1,
     *     'is_primary' => 0,
     *     'signature_text' => 'Regards',
     *   ];
     *
     * @return int|null Created Email ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveEmailEntity(int $contact_id, ?array $email_data): ?int
    {
        if (empty($email_data)) {
            return null;
        }

        // Check for previous email
        $email_id = CRM_Wrapi_Actions_Get::emailID($contact_id, $email_data['location_type_id']);

        // Create or update
        if (is_null($email_id)) {
            // Null email ID --> create new
            $email_id = CRM_Wrapi_Actions_Create::email($contact_id, $email_data);
            $this->debug(sprintf('Email added (Contact ID: %s Email ID: %s)', $contact_id, $email_id));
        } else {
            // Valid email ID --> update present
            CRM_Wrapi_Actions_Update::email($email_id, $email_data);
            $this->debug(sprintf('Email updated (Email ID: %s)', $email_id));
        }

        return $email_id;
    }

    /**
     * Save Phone entity
     *
     * If a phone number is already present for a contact and location type pair then update that number
     * If not present then add new number
     *
     * @param int $contact_id Contact ID
     * @param array|null $phone_data Phone data
     *   This contains the fields to save, it should be in a format which can be fed to civicrm_api4() calls.
     *   See Api Explorer v4
     *
     *   Example:
     *   $phone_data = [
     *     'location_type_id' => 1,
     *     'mobile_provider_id' => 3,
     *     'phone' => '+3611234567',
     *   ];
     *
     * @return int|null Created Phone ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function savePhoneEntity(int $contact_id, ?array $phone_data): ?int
    {
        if (empty($phone_data)) {
            return null;
        }

        // Check for previous phone
        $phone_id = CRM_Wrapi_Actions_Get::phoneID($contact_id, $phone_data['location_type_id']);

        // Create or update
        if (is_null($phone_id)) {
            // Null phone ID --> create new
            $phone_id = CRM_Wrapi_Actions_Create::phone($contact_id, $phone_data);
            $this->debug(sprintf('Phone added (Contact ID: %s Phone ID: %s)', $contact_id, $phone_id));
        } else {
            // Valid phone ID --> update present
            CRM_Wrapi_Actions_Update::phone($phone_id, $phone_data);
            $this->debug(sprintf('Phone updated (Phone ID: %s)', $phone_id));
        }

        return $phone_id;
    }

    /**
     * Save Address entity
     *
     * If an address is already present for a contact and location type pair then update that address
     * If not present then add new address
     *
     * @param int $contact_id Contact ID
     * @param array|null $address_data Address data
     *   This contains the fields to save, it should be in a format which can be fed to civicrm_api4() calls.
     *   See Api Explorer v4
     *
     *   Example:
     *   $address_data = [
     *     'location_type_id' => 1,
     *     'street_number' => '25',
     *     'city' => 'Budapest',
     *   ];
     *
     * @return int|null Created Address ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveAddressEntity(int $contact_id, ?array $address_data): ?int
    {
        if (empty($address_data)) {
            return null;
        }

        // Check for previous address
        $address_id = CRM_Wrapi_Actions_Get::addressID($contact_id, $address_data['location_type_id']);

        // Create or update
        if (is_null($address_id)) {
            // Null address ID --> create new
            $address_id = CRM_Wrapi_Actions_Create::address($contact_id, $address_data);
            $this->debug(sprintf('Address added (Contact ID: %s Address ID: %s)', $contact_id, $address_id));
        } else {
            // Valid address ID --> update present
            CRM_Wrapi_Actions_Update::address($address_id, $address_data);
            $this->debug(sprintf('Address updated (Address ID: %s)', $address_id));
        }

        return $address_id;
    }

    /**
     * Save Relationship entity
     *
     * If a relationship type is already present between the contacts then update that relationship
     * If not present then add new relationship
     *
     * @param int $contact_id Contact ID
     * @param array|null $relationship_data Relationship data
     *   This contains the fields to save, it should be in a format which can be fed to civicrm_api4() calls.
     *   See Api Explorer v4
     *
     *   Example:
     *   $relationship_data = [
     *     'contact_id_b' => 11,
     *     'relationship_type_id' => 3,
     *     'start_date' => '2021-03-05',
     *     'is_active' => 1,
     *   ];
     *
     * @return int|null Created relationship ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     * @throws UnauthorizedException
     */
    protected function saveRelationshipEntity(int $contact_id, ?array $relationship_data): ?int
    {
        if (empty($relationship_data)) {
            return null;
        }

        // Check for previous relationship
        $relationship_id = CRM_Wrapi_Actions_Get::relationshipID(
            $contact_id,
            $relationship_data['contact_id_b'],
            $relationship_data['relationship_type_id']
        );

        // Create or update
        if (is_null($relationship_id)) {
            // Null relationship ID --> create new
            $relationship_id = CRM_Wrapi_Actions_Create::relationship($contact_id, $relationship_data);
            $this->debug(
                sprintf('Relationship added (Contact ID: %s Relation ID: %s)', $contact_id, $relationship_id)
            );
        } else {
            // Valid relationship ID --> update present
            CRM_Wrapi_Actions_Update::relationship($relationship_id, $relationship_data);
            $this->debug(sprintf('Relationship updated (Relation ID: %s)', $relationship_id));
        }

        return $relationship_id;
    }
}
