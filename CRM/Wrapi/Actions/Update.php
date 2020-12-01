<?php

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\Result;

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
     * Check if create operation succeeded
     *
     * @param Result $results
     * @param string $action
     *
     * @return array
     *
     * @throws CRM_Core_Exception
     */
    protected static function checkSuccess(Civi\Api4\Generic\Result $results, string $action): array
    {
        $data = $results->first();

        if (is_null($data)) {
            throw new CRM_Core_Exception(sprintf('Failed to %s', $action));
        }

        return $data;
    }

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
    public static function contact(int $contact_id, array $values = [], bool $check_permissions = false): array
    {
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        // Remove contact ID from values
        unset($values['id']);

        $results = civicrm_api4(
            'Contact',
            'update',
            [
                'where' => [
                    ['id', '=', $contact_id],
                ],
                'values' => $values,
                'limit' => 1,
                'checkPermissions' => $check_permissions,
            ]
        );

        return self::checkSuccess($results, 'update contact');
    }
}
