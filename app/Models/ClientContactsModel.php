<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;

/**
 * Data access for client_contact_persons table
 */
class ClientContactsModel {

    /**
     * Upsert the primary contact person for a client using client_id + email uniqueness
     *
     * @param int   $clientId
     * @param array $contactData
     * @return array|false
     */
    public function upsertPrimaryContact($clientId, array $contactData) {
        $clientId = (int) $clientId;
        $email = isset($contactData['email']) ? trim($contactData['email']) : '';

        if ($clientId <= 0 || $email === '') {
            return false;
        }

        $names = $this->resolveNames($contactData);

        $sql = 'INSERT INTO client_contact_persons (client_id, first_name, surname, email, cellphone_number, tel_number, "position")
                VALUES (:client_id, :first_name, :surname, :email, :cellphone_number, :tel_number, :position)
                ON CONFLICT (client_id, email) DO UPDATE SET
                    first_name = EXCLUDED.first_name,
                    surname = EXCLUDED.surname,
                    cellphone_number = EXCLUDED.cellphone_number,
                    tel_number = EXCLUDED.tel_number,
                    "position" = EXCLUDED."position"
                RETURNING contact_id, client_id, first_name, surname, email, cellphone_number, tel_number, "position"';

        return DatabaseService::getRow($sql, array(
            ':client_id' => $clientId,
            ':first_name' => $names['first_name'],
            ':surname' => $names['surname'],
            ':email' => $email,
            ':cellphone_number' => isset($contactData['cellphone']) ? trim($contactData['cellphone']) : null,
            ':tel_number' => isset($contactData['telephone']) ? trim($contactData['telephone']) : null,
            ':position' => $contactData['position'] ?? null,
        ));
    }

    /**
     * Fetch the primary (oldest) contact for a client
     */
    public function getPrimaryContact($clientId) {
        $results = $this->getPrimaryContacts(array($clientId));
        return $results ? reset($results) : null;
    }

    /**
     * Fetch the primary contacts for multiple clients
     *
     * @param array $clientIds
     * @return array<int,array>
     */
    public function getPrimaryContacts(array $clientIds) {
        $ids = array_values(array_unique(array_map('intval', array_filter($clientIds))));
        if (empty($ids)) {
            return array();
        }

        $placeholders = array();
        $params = array();

        foreach ($ids as $index => $id) {
            $key = ':id' . $index;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql = 'SELECT DISTINCT ON (client_id)
                    contact_id,
                    client_id,
                    first_name,
                    surname,
                    email,
                    cellphone_number,
                    tel_number,
                    "position"
                FROM client_contact_persons
                WHERE client_id IN (' . implode(',', $placeholders) . ')
                ORDER BY client_id, contact_id ASC';

        $rows = DatabaseService::getAll($sql, $params) ?: array();
        $map = array();

        foreach ($rows as $row) {
            $map[(int) $row['client_id']] = $row;
        }

        return $map;
    }

    /**
     * Determine first and surname from provided data
     */
    protected function resolveNames(array $contactData) {
        $first = isset($contactData['first_name']) ? trim($contactData['first_name']) : '';
        $surname = isset($contactData['surname']) ? trim($contactData['surname']) : '';

        if ($first === '' && $surname === '' && !empty($contactData['name'])) {
            $full = trim($contactData['name']);
            if ($full !== '') {
                $parts = preg_split('/\s+/', $full);
                $first = array_shift($parts);
                $surname = $parts ? implode(' ', $parts) : '';
            }
        }

        return array(
            'first_name' => $first ?: null,
            'surname' => $surname ?: null,
        );
    }
}
