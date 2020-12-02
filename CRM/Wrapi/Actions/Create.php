<?php

use Civi\API\Exception\NotImplementedException;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Email;
use Civi\Api4\Generic\Result;

/**
 * Common Create Actions
 *
 * Wrapper around APIv4
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Actions_Create
{
    /**
     * Check if create operation succeeded
     *
     * @param Result $results API call results
     * @param string $action Operation name (for logging & reporting)
     *
     * @return int Entity ID
     *
     * @throws CRM_Core_Exception
     */
    protected static function checkSuccess(Civi\Api4\Generic\Result $results, string $action): int
    {
        $id = $results->first()['id'];

        if (is_null($id)) {
            throw new CRM_Core_Exception(sprintf('Failed to %s', $action));
        }

        return (int)$id;
    }

    /**
     * Create new contact
     *
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Contact ID
     *
     * @throws API_Exception
     * @throws NotImplementedException
     * @throws CRM_Core_Exception
     */
    public static function contact(array $values = [], bool $check_permissions = false): int
    {
        $results = civicrm_api4(
            'Contact',
            'create',
            [
                'values' => $values,
                'checkPermissions' => $check_permissions,
            ]
        );

        return self::checkSuccess($results, 'create new contact');
    }

    /**
     * Add email to contact
     *
     * @param string $email Email address
     * @param int $contact_id Contact ID
     * @param string $type Email address type
     *  'Home'
     *  'Work'
     *  'Main'
     *  'Billing'
     *  'Other'
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Email ID
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     * @throws CRM_Core_Exception
     */
    public static function emailToContact(
        string $email,
        int $contact_id,
        string $type = 'Home',
        bool $check_permissions = false
    ): int {
        CRM_Wrapi_Processor_Base::validateInput($email, 'string', 'Email address');
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = Email::create($check_permissions)
            ->addValue('contact_id', $contact_id)
            ->addValue('email', $email)
            ->addValue('location_type_id:name', $type)
            ->execute();

        return self::checkSuccess($results, 'add email to contact');
    }

    /**
     * Add new contribution
     *
     * @param int $contact_id Contact ID
     * @param array $values Contribution data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Contribution ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function contribution(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = civicrm_api4(
            'Contribution',
            'create',
            [
                'values' => $values,
                'checkPermissions' => $check_permissions,
            ]
        );

        return self::checkSuccess($results, 'add contribution to contact');
    }
}
