<?php

use Civi\API\Exception\NotImplementedException;

/**
 * Common Update Actions
 *
 * Wrapper around APIv4
 *
 * @package  wrapi
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_Wrapi_Actions_Update
{
    /**
     * Update contact
     *
     * @param int $contact_id
     * @param array $values
     * @param bool $check_permissions
     *
     * @return int Contact ID
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function contact(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = civicrm_api4(
            'Contact',
            'update',
            [
                'where' => [
                    ['id', '=', ''],
                ],
                'values' => $values,
                'limit' => 1,
                'checkPermissions' => $check_permissions,
            ]
        );

        return CRM_Wrapi_Actions_Create::checkSuccess($results, 'update contact');
    }
}
