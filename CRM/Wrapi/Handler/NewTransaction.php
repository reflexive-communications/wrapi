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
        return [
            'contact_type' => $this->requestData['contact_type'],
            'first_name' => $this->requestData['first_name'],
            'last_name' => $this->requestData['last_name'],
            'preferred_language' => $this->requestData['preferred_language'],
        ];
    }

    /**
     * Parse contribution data from request
     *
     * @return array Contribution data
     */
    protected function parseContributionData(): array
    {
        return [
            'subject' => $this->requestData['subject'],
            'total_amount' => $this->requestData['total_amount'],
            'receive_date' => $this->requestData['receive_date'],
            'payment_instrument_id' => $this->requestData['payment_instrument_id'],
            'payment_transaction_id' => $this->requestData['payment_transaction_id'],
            'contribution_status_id' => $this->requestData['contribution_status_id'],
        ];
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
        $contact_data = array(
            'email' => 'email',

            'contact_type' => "Individual",
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'preferred_language' => 'hu_HU',

            'financial_type_id' => "Adomány",
//            (sima adomány esetén üres esetleg lehet pénzadomány, vagy maga a Menü / süti vagy karácsonyi csomag neve kerül ide és akkor lehet ez alapján dől el a címke is)
            'subject' => 'termék neve',
            'total_amount' => '3000',
            'receive_date' => '2020-10-01',
//            (utalás esetén: EFT)
            'payment_instrument_id' => " OTP Simple Pay ",
//            (csak simplePay esetén lesz értéke, ha nem kell nem adom át, de ez alapján visszakereshető a tranzakció a SimpleAdmin oldalán is)
            'payment_transaction_id' => "123412341234",
//            (utalás esetén: Pending, illetve akkor SimplePay esetén ide adom a banki választ, ilyeneket: COMPLETE / ABORTED TRANSACTION / CARD_NOTAUTHORIZED)
            'contribution_status_id' => " Completed ",
        );

        $contact_id = $this->processContactData()['id'];

        return $contact_id;

    }

    /**
     * Process contact data
     *
     * @return array Contact data
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    protected function processContactData(): array
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

        return $contact_data_stored;
    }
}
