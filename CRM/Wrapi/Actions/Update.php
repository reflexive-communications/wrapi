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
     * Check if update operation succeeded
     *
     * @param Result $results API call results
     * @param string $action Operation name (for logging & reporting)
     *
     * @return array Data
     *
     * @throws CRM_Core_Exception
     */
    protected static function parseResults(Civi\Api4\Generic\Result $results, string $action): array
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
     * @param int $contact_id Contact ID
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Contact data
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

        return self::parseResults($results, 'update contact');
    }
}
