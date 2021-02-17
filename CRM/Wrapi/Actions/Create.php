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
     * @return int ID of created entity
     *
     * @throws CRM_Core_Exception
     */
    protected static function parseResults(Civi\Api4\Generic\Result $results, string $action): int
    {
        // Get entity ID from results
        $id = $results->first()['id'];

        // If there is a valid ID --> successful insert
        if (is_null($id) || $id < 1) {
            throw new CRM_Core_Exception(sprintf('Failed to %s', $action));
        }

        return (int)$id;
    }

    /**
     * Add email to contact
     *
     * @deprecated
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

        return self::parseResults($results, 'add email to contact');
    }

    /**
     * Add new generic entity
     *
     * @param string $entity Name of entity
     * @param array $values Entity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int ID of created entity
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function entity(string $entity, array $values = [], bool $check_permissions = false): int
    {
        $results = civicrm_api4(
            $entity,
            'create',
            [
                'values' => $values,
                'checkPermissions' => $check_permissions,
            ]
        );

        return self::parseResults($results, sprintf('create new %s', $entity));
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
        return self::entity('Contact', $values, $check_permissions);
    }

    /**
     * Add email to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Email data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Email ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function email(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        $values['contact_id'] = $contact_id;

        return self::entity('Email', $values, $check_permissions);
    }

    /**
     * Add phone to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Phone data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Phone ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function phone(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        $values['contact_id'] = $contact_id;

        return self::entity('Phone', $values, $check_permissions);
    }

    /**
     * Add relationship to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Relationship data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Relationship ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function relationship(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        $values['contact_id_a'] = $contact_id;

        return self::entity('Relationship', $values, $check_permissions);
    }

    /**
     * Add contribution to contact
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
        $values['contact_id'] = $contact_id;

        return self::entity('Contribution', $values, $check_permissions);
    }
}
