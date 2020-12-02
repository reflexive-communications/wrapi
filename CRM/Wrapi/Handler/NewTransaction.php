<?php

use Civi\API\Exception\NotImplementedException;
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
            'payment_instrument_id' => [
                'type' => 'string',
                'name' => 'Payment instrument',
                'required' => true,
            ],
            'payment_transaction_id' => [
                'type' => 'string',
                'name' => 'Payment transaction ID',
                'required' => true,
            ],
            'contribution_status_id' => [
                'type' => 'string',
                'name' => 'Contribution ID',
                'required' => true,
            ],
        ];
    }

    /**
     * Parse contact data from request
     *
     * @return array Contact data
     */
    protected function parseContactData(): array
    {
        $data = [];
        if (!empty($this->requestData['contact_type'])) {
            $data['contact_type'] = $this->requestData['contact_type'];
        }
        if (!empty($this->requestData['first_name'])) {
            $data['first_name'] = $this->requestData['first_name'];
        }
        if (!empty($this->requestData['last_name'])) {
            $data['last_name'] = $this->requestData['last_name'];
        }
        if (!empty($this->requestData['preferred_language'])) {
            $data['preferred_language'] = $this->requestData['preferred_language'];
        }

        return $data;
    }

    /**
     * Parse contribution data from request
     *
     * @return array Contribution data
     */
    protected function parseContributionData(): array
    {
        $data = [];
        $data['total_amount'] = $this->requestData['total_amount'];
        $data['receive_date'] = $this->requestData['receive_date'];
        $data['payment_instrument_id'] = $this->requestData['payment_instrument_id'];
        $data['trxn_id'] = $this->requestData['payment_transaction_id'];

        if (!empty($this->requestData['subject'])) {
            $data['source'] = $this->requestData['subject'];
        }

        switch ($this->requestData['contribution_status_id']) {
            case 'COMPLETE':
            case 'Completed':
                $data['contribution_status_id'] = 'Completed';
                break;
            case 'Pending':
                $data['contribution_status_id'] = 'Pending';
                break;
            default:
                $data['contribution_status_id'] = 'Failed';
                $data['cancel_reason'] = $this->requestData['contribution_status_id'];
                break;
        }

        return $data;
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
        $contact_id = $this->processContactData();

        $this->addContribution($contact_id);

        $this->logRequestProcessed();

        return CRM_Wrapi_Handler_Base::REQUEST_PROCESSED;
    }

    /**
     * Process contact data
     *
     * @return int Contact data
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    protected function processContactData(): int
    {
        $contact_data_received = $this->parseContactData();

        // Lookup email
        $contact_id = CRM_Wrapi_Actions_Get::contactIDFromEmail($this->requestData['email']);

        // Email found --> retrieve stored contact data
        if (!is_null($contact_id)) {
            $contact_data_stored = CRM_Wrapi_Actions_Get::contactDataByID($contact_id);

            if (is_null($contact_data_stored)) {
                throw new CRM_Core_Exception('Contact data not found in DB');
            }

            // Compare received vs. stored contact data
            $data_changed = false;
            foreach ($contact_data_received as $property => $value) {

                // If received is not empty and different --> update info
                if (!empty($value) && $value != $contact_data_stored[$property]) {
                    $contact_data_stored[$property] = $value;
                    $data_changed = true;
                }
            }

            // If there is a change in data --> update contact
            if ($data_changed) {
                CRM_Wrapi_Actions_Update::contact($contact_id, $contact_data_stored);
                $this->debug(sprintf('Contact updated ID: %s', $contact_id));
            }
        } else {
            // Email not found --> create new contact & add email
            $contact_id = CRM_Wrapi_Actions_Create::contact($contact_data_received);
            $this->debug(sprintf('Contact created ID: %s', $contact_id));
            CRM_Wrapi_Actions_Create::emailToContact($this->requestData['email'], $contact_id);
            $this->debug(sprintf('Email added to contact ID: %s', $contact_id));
        }

        return $contact_id;
    }

    /**
     * Add new contribution
     *
     * @param int $contact_id Contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    protected function addContribution(int $contact_id)
    {
        $contribution_data = $this->parseContributionData();

        $contribution_id = CRM_Wrapi_Actions_Create::contribution($contact_id, $contribution_data);
        $this->debug(sprintf('Contribution added ID: %s', $contribution_id));
    }
}
