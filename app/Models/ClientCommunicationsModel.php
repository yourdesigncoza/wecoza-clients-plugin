<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;

/**
 * Data access for client_communications table
 */
class ClientCommunicationsModel {

    /**
     * Record a communication entry for a client
     */
    public function logCommunication($clientId, $type, $subject = null, $content = null, $userId = null) {
        $clientId = (int) $clientId;
        $type = trim((string) $type);

        if ($clientId <= 0 || $type === '') {
            return false;
        }

        if ($subject === null || $subject === '') {
            $subject = sprintf('Client communication: %s', $type);
        }

        if ($content === null || $content === '') {
            $content = sprintf('Communication type recorded as %s.', $type);
        }

        if ($userId === null) {
            $currentUser = function_exists('get_current_user_id') ? get_current_user_id() : null;
            $userId = $currentUser ?: null;
        }

        $sql = 'INSERT INTO client_communications (client_id, communication_type, subject, content, user_id)
                VALUES (:client_id, :communication_type, :subject, :content, :user_id)';

        $params = array(
            ':client_id' => $clientId,
            ':communication_type' => $type,
            ':subject' => $subject,
            ':content' => $content,
            ':user_id' => $userId,
        );

        return DatabaseService::query($sql, $params) !== false;
    }

    /**
     * Get latest communication for a single client
     */
    public function getLatestCommunication($clientId) {
        $clientId = (int) $clientId;
        if ($clientId <= 0) {
            return null;
        }

        $sql = 'SELECT communication_id, client_id, communication_type, subject, content, communication_date, user_id
                FROM client_communications
                WHERE client_id = :client_id
                ORDER BY communication_date DESC, communication_id DESC
                LIMIT 1';

        return DatabaseService::getRow($sql, array(':client_id' => $clientId));
    }

    /**
     * Get latest communications for multiple clients
     *
     * @param array $clientIds
     * @return array<int,array>
     */
    public function getLatestCommunications(array $clientIds) {
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
                    communication_id,
                    client_id,
                    communication_type,
                    subject,
                    content,
                    communication_date,
                    user_id
                FROM client_communications
                WHERE client_id IN (' . implode(',', $placeholders) . ')
                ORDER BY client_id, communication_date DESC, communication_id DESC';

        $rows = DatabaseService::getAll($sql, $params) ?: array();
        $map = array();

        foreach ($rows as $row) {
            $map[(int) $row['client_id']] = $row;
        }

        return $map;
    }

    /**
     * Convenience wrapper returning the latest communication type string
     */
    public function getLatestCommunicationType($clientId) {
        $latest = $this->getLatestCommunication($clientId);
        return $latest ? $latest['communication_type'] : null;
    }

    /**
     * Convenience wrapper returning map of client_id => [communication_type, communication_date]
     */
    public function getLatestCommunicationTypes(array $clientIds) {
        $latest = $this->getLatestCommunications($clientIds);
        $map = array();

        foreach ($latest as $clientId => $row) {
            $map[$clientId] = array(
                'communication_type' => $row['communication_type'],
                'communication_date' => $row['communication_date'],
            );
        }

        return $map;
    }
}
