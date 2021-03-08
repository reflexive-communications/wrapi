<?php

use Civi\API\Exception\UnauthorizedException;

/**
 * New Transaction Handler
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Handler_NewTransaction extends CRM_Wrapi_Handler_Base
{
    /**
     * Required options
     */
    public static function requiredOptions(): array
    {
        return ['financial_type_id'];
    }

    /**
     * Return request parameter rules
     *
     * @return array Input rules
     */
    protected function inputRules(): array
    {
        return [
            'email' => [
                'type' => 'email',
                'name' => 'Email address',
                'required' => true,
            ],
            'contact_type' => [
                'type' => 'string',
                'name' => 'Contact Type',
                'default' => 'Individual',
            ],
            'first_name' => [
                'type' => 'string',
                'name' => 'First name',
            ],
            'last_name' => [
                'type' => 'string',
                'name' => 'Last name',
            ],
            'preferred_language' => [
                'type' => 'string',
                'name' => 'Preferred language',
            ],
            'subject' => [
                'type' => 'string',
                'name' => 'Subject',
            ],
            'total_amount' => [
                'type' => 'string',
                'name' => 'Total amount',
                'required' => true,
            ],
            'receive_date' => [
                'type' => 'string',
                'name' => 'Received date',
                'required' => true,
            ],
            'payment_instrument' => [
                'type' => 'string',
                'name' => 'Payment instrument',
                'required' => true,
            ],
            'payment_transaction_id' => [
                'type' => 'string',
                'name' => 'Payment transaction ID',
                'required' => true,
            ],
            'contribution_status' => [
                'type' => 'string',
                'name' => 'Contribution status',
                'required' => true,
            ],
        ];
    }

    /**
     * Parse request data
     * Separate mixed input fields per CiviCRM entity
     * Relevant (and Civi compatible named) fields will be grouped together
     *
     * @return array Parsed data
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    protected function parseData(): array
    {
        $parsed_data = [];

        // Contact entity
        $contact_mapping = [
            'contact_type' => 'contact_type',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'preferred_language' => 'preferred_language',
        ];
        $contact_data = $this->mapFieldsString($this->requestData, $contact_mapping);

        // Email entity
        $email_mapping = [
            'email' => 'email',
        ];
        $email_data = $this->mapFieldsString($this->requestData, $email_mapping);
        // Currently use default
        // Maybe later it will be added to the request
        $email_data['location_type_id'] = CRM_Wrapi_Actions_Get::defaultLocationTypeID() ?? 1;

        // Contribution entity
        $contribution_mapping = [
            'total_amount' => 'total_amount',
            'receive_date' => 'receive_date',
            'payment_instrument' => 'payment_instrument_id:name',
            'payment_transaction_id' => 'trxn_id',
            'subject' => 'source',
        ];
        $contribution_data = $this->mapFieldsString($this->requestData, $contribution_mapping);
        $contribution_data['financial_type_id'] = $this->options['financial_type_id'];
        switch (strtolower($this->requestData['contribution_status'])) {
            case 'complete':
            case 'completed':
                $contribution_data['contribution_status_id:name'] = 'Completed';
                break;
            case 'pending':
                $contribution_data['contribution_status_id:name'] = 'Pending';
                break;
            default:
                $contribution_data['contribution_status_id:name'] = 'Failed';
                $contribution_data['cancel_reason'] = $this->requestData['contribution_status'];
                break;
        }

        $parsed_data['contact'] = $contact_data;
        $parsed_data['email'] = $email_data;
        $parsed_data['contribution'] = $contribution_data;

        return $parsed_data;
    }

    /**
     * Process Request
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    protected function process()
    {
        $data = $this->parseData();

        // Save contact
        $contact_id = $this->saveContactByEmail($data['email']['email'], $data);

        // Add contribution
        $contribution_id = CRM_Wrapi_Actions_Create::contribution($contact_id, $data['contribution']);
        $this->debug(sprintf('Contribution added (ID: %s)', $contribution_id));

        $this->logRequestProcessed();

        return CRM_Wrapi_Handler_Base::REQUEST_PROCESSED;
    }
}
