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
     * @param Result $results
     * @param string $action
     *
     * @return int
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
     * @param array $values
     * @param bool $check_permissions
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
     * @param string $email
     * @param int $contact_id
     * @param string $type
     * @param bool $check_permissions
     *
     * @return mixed
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
    ) {
        CRM_Wrapi_Processor_Base::validateInput($email, 'email', 'Email address');
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = Email::create($check_permissions)
            ->addValue('contact_id', $contact_id)
            ->addValue('email', $email)
            ->addValue('location_type_id:name', $type)
            ->execute();

        return self::checkSuccess($results, 'add email to contact');
    }
}
