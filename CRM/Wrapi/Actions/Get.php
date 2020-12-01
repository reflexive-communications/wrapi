<?php

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Contact;
use Civi\Api4\Email;

/**
 * Common Get Actions
 *
 * Wrapper around APIv4
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Actions_Get
{
    /**
     * Get contact ID by email
     *
     * @param string $email Email address
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contact ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     * @throws CRM_Core_Exception
     */
    public static function contactIDFromEmail(string $email, bool $check_permissions = false): ?int
    {
        CRM_Wrapi_Processor_Base::validateInput($email, 'string', 'Email address');

        // Search DB
        $results = Email::get($check_permissions)
            ->addSelect('contact_id')
            ->addWhere('email', 'LIKE', $email)
            ->setLimit(1)
            ->execute();

        // Return contact ID
        return $results->first()['contact_id'];
    }

    /**
     * Retrieve contact data
     *
     * @param int $contact_id Contact ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array|null Contact data on success, null on fail
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public static function contactDataByID(int $contact_id, bool $check_permissions = false): ?array
    {
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = Contact::get($check_permissions)
            ->addSelect('*')
            ->addWhere('id', '=', $contact_id)
            ->setLimit(1)
            ->execute();

        return $results->first();
    }
}
