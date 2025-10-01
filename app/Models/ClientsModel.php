<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;
use Exception;

/**
 * Clients Model for data management
 *
 * @package WeCozaClients
 * @since 1.0.0
 */
class ClientsModel {
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'clients';
    
    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Actual primary key column in database
     *
     * @var string
     */
    protected $resolvedPrimaryKey = 'id';

    /**
     * Determines if soft delete is supported
     *
     * @var bool
     */
    protected $softDeleteEnabled = true;


    /**
     * Determines if branch relationships are available
     *
     * @var bool
     */
    protected $branchColumnAvailable = true;

    /**
     * Candidate column mappings
     *
     * @var array
     */
    protected $columnCandidates = [
        'id' => ['id', 'client_id'],
        'client_name' => ['client_name'],
        'branch_of' => ['branch_of'],
        'company_registration_nr' => ['company_registration_nr', 'company_registration_number'],
        'client_street_address' => ['client_street_address', 'address_line'],
        'client_suburb' => ['client_suburb', 'suburb'],
        'client_town' => ['client_town', 'town', 'town_name', 'town_id'],
        'client_postal_code' => ['client_postal_code', 'postal_code'],
        'contact_person' => ['contact_person', 'contact_person_name'],
        'contact_person_email' => ['contact_person_email', 'contact_email'],
        'contact_person_cellphone' => ['contact_person_cellphone', 'contact_cellphone', 'contact_mobile'],
        'contact_person_tel' => ['contact_person_tel', 'contact_tel', 'contact_phone'],
        'client_communication' => ['client_communication', 'communication_type'],
        'seta' => ['seta'],
        'client_status' => ['client_status', 'status'],
        'financial_year_end' => ['financial_year_end'],
        'bbbee_verification_date' => ['bbbee_verification_date'],
        'quotes' => ['quotes'],
        'created_by' => ['created_by'],
        'updated_by' => ['updated_by'],
        'created_at' => ['created_at'],
        'updated_at' => ['updated_at'],
        'deleted_at' => ['deleted_at'],
    ];

    /**
     * Resolved column map
     *
     * @var array
     */
    protected $columnMap = [];
    
    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'client_name',
        'branch_of',
        'company_registration_nr',
        'client_street_address',
        'client_suburb',
        'client_town',
        'client_postal_code',
        'contact_person',
        'contact_person_email',
        'contact_person_cellphone',
        'contact_person_tel',
        'client_communication',
        'seta',
        'client_status',
        'financial_year_end',
        'bbbee_verification_date',
        'quotes',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * JSONB fields
     *
     * @var array
     */
    protected $jsonFields = [];
    
    /**
     * Date fields
     *
     * @var array
     */
    protected $dateFields = [
        'financial_year_end',
        'bbbee_verification_date'
    ];

    /**
     * Constructor
     */
    public function __construct() {
        foreach ($this->columnCandidates as $field => $candidates) {
            $this->columnMap[$field] = $this->resolveColumn($candidates);
        }

        $this->resolvedPrimaryKey = $this->columnMap['id'] ?: 'id';
        $this->softDeleteEnabled = !empty($this->columnMap['deleted_at']);
        $this->branchColumnAvailable = !empty($this->columnMap['branch_of']);

        // Filter fillable fields to those that exist in the schema
        $this->fillable = array_values(array_filter($this->fillable, function ($field) {
            return !empty($this->columnMap[$field] ?? null);
        }));

        // Filter JSON fields similarly
        $this->jsonFields = array_values(array_filter($this->jsonFields, function ($field) {
            return !empty($this->columnMap[$field] ?? null);
        }));

        // Filter date fields similarly
        $this->dateFields = array_values(array_filter($this->dateFields, function ($field) {
            return !empty($this->columnMap[$field] ?? null);
        }));
    }

    /**
     * Resolve first available column from candidates
     *
     * @param array $candidates
     * @return string|null
     */
    protected function resolveColumn($candidates) {
        foreach ((array) $candidates as $candidate) {
            if ($candidate && DatabaseService::tableHasColumn($this->table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get actual column name for field
     *
     * @param string $field Field name
     * @param string|null $fallback Fallback column
     * @return string|null
     */
    protected function getColumn($field, $fallback = null) {
        if (!empty($this->columnMap[$field])) {
            return $this->columnMap[$field];
        }

        return $fallback;
    }

    /**
     * Normalize database row to expected field names
     *
     * @param array $row Row data
     * @return array
     */
    protected function normalizeRow($row) {
        if (!is_array($row)) {
            return array();
        }

        $normalized = $row;

        foreach ($this->columnMap as $field => $column) {
            if ($column && array_key_exists($column, $row)) {
                $normalized[$field] = $row[$column];
            }
        }

        if (!isset($normalized['id']) && isset($row[$this->resolvedPrimaryKey])) {
            $normalized['id'] = $row[$this->resolvedPrimaryKey];
        }

        return $normalized;
    }

    /**
     * Prepare data for persistence by filtering and mapping columns
     *
     * @param array $data Raw data
     * @return array
     */
    protected function prepareDataForSave($data) {
        $prepared = array();

        foreach ($this->fillable as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $column = $this->getColumn($field);
            if (!$column) {
                continue;
            }

            $value = $data[$field];

            if (in_array($field, $this->dateFields, true) && $value === '') {
                $value = null;
            }

            if ($field === 'branch_of' && $value === '') {
                $value = null;
            }

            if (in_array($field, $this->jsonFields, true)) {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif ($value === '' || $value === null) {
                    $value = '[]';
                }
            }

            $prepared[$column] = $value;
        }

        return $prepared;
    }
    
    /**
     * Get all clients
     *
     * @param array $params Query parameters
     * @return array
     */
    public function getAll($params = array()) {
        $primaryKey = $this->resolvedPrimaryKey;
        $alias = 'c';
        $sql = "SELECT {$alias}.*, {$alias}.{$primaryKey} AS id FROM {$this->table} {$alias}";
        $bindings = array();
        $whereClauses = array();
        if ($this->softDeleteEnabled) {
            $deletedColumn = $this->getColumn('deleted_at');
            if ($deletedColumn) {
                $whereClauses[] = "{$alias}.{$deletedColumn} IS NULL";
            }
        }
        
        // Add search filter
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $searchClauses = array();
            $searchIndex = 0;

            foreach (['client_name', 'company_registration_nr', 'contact_person', 'contact_person_email', 'client_town'] as $field) {
                $column = $this->getColumn($field);
                if ($column) {
                    $placeholder = ':search' . $searchIndex++;
                    $searchClauses[] = "CAST({$alias}.{$column} AS TEXT) ILIKE {$placeholder}";
                    $bindings[$placeholder] = $search;
                }
            }

            if (!empty($searchClauses)) {
                $whereClauses[] = '(' . implode(' OR ', $searchClauses) . ')';
            }
        }
        
        // Add status filter
        if (!empty($params['status'])) {
            $statusColumn = $this->getColumn('client_status');
            if ($statusColumn) {
                $whereClauses[] = "{$alias}.{$statusColumn} = :status";
                $bindings[':status'] = $params['status'];
            }
        }
        
        // Add SETA filter
        if (!empty($params['seta'])) {
            $setaColumn = $this->getColumn('seta');
            if ($setaColumn) {
                $whereClauses[] = "{$alias}.{$setaColumn} = :seta";
                $bindings[':seta'] = $params['seta'];
            }
        }
        
        // Add branch filter
        $branchColumn = $this->getColumn('branch_of');
        if ($branchColumn && array_key_exists('branch_of', $params)) {
            if ($params['branch_of'] === null) {
                $whereClauses[] = "{$alias}.{$branchColumn} IS NULL";
            } else {
                $whereClauses[] = "{$alias}.{$branchColumn} = :branch_of";
                $bindings[':branch_of'] = $params['branch_of'];
            }
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
        
        // Add sorting
        $orderBy = !empty($params['order_by']) ? $params['order_by'] : 'client_name';
        $orderBy = preg_replace('/[^a-zA-Z0-9_]/', '', $orderBy) ?: 'client_name';
        $orderDir = !empty($params['order_dir']) && strtoupper($params['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
        $orderColumn = $this->getColumn($orderBy);
        if (!$orderColumn && DatabaseService::tableHasColumn($this->table, $orderBy)) {
            $orderColumn = $orderBy;
        }
        if (!$orderColumn) {
            $orderColumn = $this->getColumn('client_name', $primaryKey);
        }
        $sql .= " ORDER BY {$alias}.{$orderColumn} {$orderDir}";
        
        // Add pagination
        if (!empty($params['limit'])) {
            $sql .= " LIMIT :limit";
            $bindings[':limit'] = (int)$params['limit'];
            
            if (!empty($params['offset'])) {
                $sql .= " OFFSET :offset";
                $bindings[':offset'] = (int)$params['offset'];
            }
        }
        
        $results = DatabaseService::getAll($sql, $bindings);
        
        // Decode JSON fields
        if ($results) {
            foreach ($results as &$row) {
                $row = $this->normalizeRow($row);
                $this->decodeJsonFields($row);
            }
        }
        
        return $results ?: array();
    }
    
    /**
     * Get single client by ID
     *
     * @param int $id Client ID
     * @return array|false
     */
    public function getById($id) {
        $primaryKey = $this->resolvedPrimaryKey;
        $alias = 'c';
        $sql = "SELECT {$alias}.*, {$alias}.{$primaryKey} AS id FROM {$this->table} {$alias} WHERE {$alias}.{$primaryKey} = :id";
        $deletedColumn = $this->getColumn('deleted_at');
        if ($this->softDeleteEnabled && $deletedColumn) {
            $sql .= " AND {$alias}.{$deletedColumn} IS NULL";
        }

        $result = DatabaseService::getRow($sql, [':id' => $id]);
        
        if ($result) {
            $result = $this->normalizeRow($result);
            $this->decodeJsonFields($result);
        }
        return $result;
    }
    
    /**
     * Get client by company registration number
     *
     * @param string $regNr Registration number
     * @return array|false
     */
    public function getByRegistrationNumber($regNr) {
        $primaryKey = $this->resolvedPrimaryKey;
        $alias = 'c';
        $registrationColumn = $this->getColumn('company_registration_nr');
        if (!$registrationColumn) {
            return false;
        }

        $sql = "SELECT {$alias}.*, {$alias}.{$primaryKey} AS id FROM {$this->table} {$alias} WHERE {$alias}.{$registrationColumn} = :reg_nr";
        $deletedColumn = $this->getColumn('deleted_at');
        if ($this->softDeleteEnabled && $deletedColumn) {
            $sql .= " AND {$alias}.{$deletedColumn} IS NULL";
        }

        $result = DatabaseService::getRow($sql, [':reg_nr' => $regNr]);
        
        if ($result) {
            $result = $this->normalizeRow($result);
            $this->decodeJsonFields($result);
        }
        
        return $result;
    }
    
    /**
     * Create new client
     *
     * @param array $data Client data
     * @return int|false Client ID or false on failure
     */
    public function create($data) {
        // Add timestamps
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        
        // Add current user as creator
        if (!isset($data['created_by'])) {
            $data['created_by'] = get_current_user_id();
        }

        $prepared = $this->prepareDataForSave($data);

        if (empty($prepared)) {
            return false;
        }
        
        return DatabaseService::insert($this->table, $prepared);
    }
    
    /**
     * Update client
     *
     * @param int $id Client ID
     * @param array $data Client data
     * @return bool
     */
    public function update($id, $data) {
        // Update timestamp
        $data['updated_at'] = current_time('mysql');
        
        // Add current user as updater
        if (!isset($data['updated_by'])) {
            $data['updated_by'] = get_current_user_id();
        }

        $prepared = $this->prepareDataForSave($data);

        if (empty($prepared)) {
            return true;
        }

        $whereClause = $this->resolvedPrimaryKey . ' = :id';
        if ($this->softDeleteEnabled) {
            $whereClause .= ' AND deleted_at IS NULL';
        }

        $result = DatabaseService::update(
            $this->table,
            $prepared,
            $whereClause,
            [':id' => $id]
        );
        
        return $result !== false;
    }
    
    /**
     * Delete client (soft delete)
     *
     * @param int $id Client ID
     * @return bool
     */
    public function delete($id) {
        $data = [
            'deleted_at' => current_time('mysql'),
            'updated_by' => get_current_user_id()
        ];
        
        if ($this->softDeleteEnabled) {
            $updateData = array();

            $deletedAtColumn = $this->getColumn('deleted_at');
            if ($deletedAtColumn) {
                $updateData[$deletedAtColumn] = current_time('mysql');
            }

            $updatedByColumn = $this->getColumn('updated_by');
            if ($updatedByColumn) {
                $updateData[$updatedByColumn] = get_current_user_id();
            }

            $updatedAtColumn = $this->getColumn('updated_at');
            if ($updatedAtColumn) {
                $updateData[$updatedAtColumn] = current_time('mysql');
            }

            if (empty($updateData)) {
                // No columns to update, fall back to hard delete
                $result = DatabaseService::delete(
                    $this->table,
                    $this->resolvedPrimaryKey . ' = :id',
                    [':id' => $id]
                );

                return $result !== false;
            }

            $result = DatabaseService::update(
                $this->table,
                $updateData,
                $this->resolvedPrimaryKey . ' = :id',
                [':id' => $id]
            );

            return $result !== false;
        }

        $result = DatabaseService::delete(
            $this->table,
            $this->resolvedPrimaryKey . ' = :id',
            [':id' => $id]
        );

        return $result !== false;
    }
    
    /**
     * Get client count
     *
     * @param array $params Query parameters
     * @return int
     */
    public function count($params = array()) {
        $alias = 'c';
        $sql = "SELECT COUNT(*) FROM {$this->table} {$alias}";
        $bindings = array();
        $whereClauses = array();
        if ($this->softDeleteEnabled) {
            $deletedColumn = $this->getColumn('deleted_at');
            if ($deletedColumn) {
                $whereClauses[] = "{$alias}.{$deletedColumn} IS NULL";
            }
        }
        
        // Add search filter
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $searchClauses = array();
            $searchIndex = 0;

            foreach (['client_name', 'company_registration_nr', 'contact_person', 'contact_person_email', 'client_town'] as $field) {
                $column = $this->getColumn($field);
                if ($column) {
                    $placeholder = ':count_search' . $searchIndex++;
                    $searchClauses[] = "CAST({$alias}.{$column} AS TEXT) ILIKE {$placeholder}";
                    $bindings[$placeholder] = $search;
                }
            }

            if (!empty($searchClauses)) {
                $whereClauses[] = '(' . implode(' OR ', $searchClauses) . ')';
            }
        }
        
        // Add status filter
        if (!empty($params['status'])) {
            $statusColumn = $this->getColumn('client_status');
            if ($statusColumn) {
                $whereClauses[] = "{$alias}.{$statusColumn} = :status";
                $bindings[':status'] = $params['status'];
            }
        }
        
        // Add SETA filter
        if (!empty($params['seta'])) {
            $setaColumn = $this->getColumn('seta');
            if ($setaColumn) {
                $whereClauses[] = "{$alias}.{$setaColumn} = :seta";
                $bindings[':seta'] = $params['seta'];
            }
        }

        $branchColumn = $this->getColumn('branch_of');
        if ($branchColumn && array_key_exists('branch_of', $params)) {
            if ($params['branch_of'] === null) {
                $whereClauses[] = "{$alias}.{$branchColumn} IS NULL";
            } else {
                $whereClauses[] = "{$alias}.{$branchColumn} = :count_branch_of";
                $bindings[':count_branch_of'] = $params['branch_of'];
            }
        }

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
        
        $count = DatabaseService::getValue($sql, $bindings);
        return (int)$count;
    }
    
    /**
     * Get client statistics
     *
     * @return array
     */
    public function getStatistics() {
        $alias = 'c';
        $statusColumn = $this->getColumn('client_status');
        $branchColumn = $this->getColumn('branch_of');
        $deletedColumn = $this->getColumn('deleted_at');

        $selectParts = array(
            'COUNT(*) AS total_clients',
            $statusColumn ? "SUM(CASE WHEN {$alias}.{$statusColumn} = 'Active Client' THEN 1 ELSE 0 END) AS active_clients" : '0 AS active_clients',
            $statusColumn ? "SUM(CASE WHEN {$alias}.{$statusColumn} = 'Lead' THEN 1 ELSE 0 END) AS leads" : '0 AS leads',
            $statusColumn ? "SUM(CASE WHEN {$alias}.{$statusColumn} = 'Cold Call' THEN 1 ELSE 0 END) AS cold_calls" : '0 AS cold_calls',
            $statusColumn ? "SUM(CASE WHEN {$alias}.{$statusColumn} = 'Lost Client' THEN 1 ELSE 0 END) AS lost_clients" : '0 AS lost_clients',
            $branchColumn ? "SUM(CASE WHEN {$alias}.{$branchColumn} IS NOT NULL THEN 1 ELSE 0 END) AS branch_clients" : '0 AS branch_clients',
        );

        $sql = 'SELECT ' . implode(', ', $selectParts) . " FROM {$this->table} {$alias}";

        if ($this->softDeleteEnabled && $deletedColumn) {
            $sql .= " WHERE {$alias}.{$deletedColumn} IS NULL";
        }

        $result = DatabaseService::getRow($sql);

        return $result ?: array(
            'total_clients' => 0,
            'active_clients' => 0,
            'leads' => 0,
            'cold_calls' => 0,
            'lost_clients' => 0,
            'branch_clients' => 0
        );
    }
    
    /**
     * Get branch clients
     *
     * @param int $parentId Parent client ID
     * @return array
     */
    public function getBranchClients($parentId) {
        if (!$this->branchColumnAvailable) {
            return array();
        }

        $primaryKey = $this->resolvedPrimaryKey;
        $alias = 'c';
        $branchColumn = $this->getColumn('branch_of');
        $deletedColumn = $this->getColumn('deleted_at');
        $orderColumn = $this->getColumn('client_name', $primaryKey);

        $sql = "SELECT {$alias}.*, {$alias}.{$primaryKey} AS id FROM {$this->table} {$alias} WHERE {$alias}.{$branchColumn} = :parent_id";
        if ($this->softDeleteEnabled && $deletedColumn) {
            $sql .= " AND {$alias}.{$deletedColumn} IS NULL";
        }
        $sql .= " ORDER BY {$alias}.{$orderColumn}";

        $results = DatabaseService::getAll($sql, [':parent_id' => $parentId]);
        
        if ($results) {
            foreach ($results as &$row) {
                $row = $this->normalizeRow($row);
                $this->decodeJsonFields($row);
            }
        }
        
        return $results ?: array();
    }
    
    /**
     * Get clients for dropdown
     *
     * @return array
     */
    public function getForDropdown() {
        $primaryKey = $this->resolvedPrimaryKey;
        $alias = 'c';
        $nameColumn = $this->getColumn('client_name', $primaryKey);
        $registrationColumn = $this->getColumn('company_registration_nr');
        $branchColumn = $this->getColumn('branch_of');
        $deletedColumn = $this->getColumn('deleted_at');

        $selectParts = array(
            "{$alias}.{$primaryKey} AS id",
            "{$alias}.{$nameColumn} AS client_name",
        );

        if ($registrationColumn) {
            $selectParts[] = "{$alias}.{$registrationColumn} AS company_registration_nr";
        }

        $sql = 'SELECT ' . implode(', ', $selectParts) . " FROM {$this->table} {$alias}";
        $conditions = array();

        if ($this->softDeleteEnabled && $deletedColumn) {
            $conditions[] = "{$alias}.{$deletedColumn} IS NULL";
        }

        if ($this->branchColumnAvailable && $branchColumn) {
            $conditions[] = "{$alias}.{$branchColumn} IS NULL";
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY {$alias}.{$nameColumn}";
        
        $results = DatabaseService::getAll($sql) ?: array();

        if ($results) {
            foreach ($results as &$row) {
                $row = $this->normalizeRow($row);
            }
        }

        return $results;
    }
    
    /**
     * Validate client data
     *
     * @param array $data Client data
     * @param int|null $id Client ID (for updates)
     * @return array Validation errors
     */
    public function validate($data, $id = null) {
        $errors = array();
        $config = \WeCozaClients\config('app');
        $rules = $config['validation_rules'];
        
        foreach ($rules as $field => $fieldRules) {
            // Check required fields
            if (!empty($fieldRules['required']) && empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                continue;
            }
            
            // Skip validation if field is not set or empty (and not required)
            if (empty($data[$field])) {
                continue;
            }
            
            $value = $data[$field];
            
            // Check max length
            if (!empty($fieldRules['max_length']) && strlen($value) > $fieldRules['max_length']) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must not exceed ' . $fieldRules['max_length'] . ' characters.';
            }
            
            // Check email format
            if (!empty($fieldRules['email']) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Please provide a valid email address.';
            }
            
            // Check date format
            if (!empty($fieldRules['date'])) {
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $errors[$field] = 'Please provide a valid date.';
                }
            }
            
            // Check allowed values
            if (!empty($fieldRules['in']) && !in_array($value, $fieldRules['in'])) {
                $errors[$field] = 'Invalid value selected.';
            }
            
            // Check uniqueness (company registration number)
            if (!empty($fieldRules['unique']) && $field === 'company_registration_nr') {
                $existing = $this->getByRegistrationNumber($value);
                if ($existing && (!$id || $existing['id'] != $id)) {
                    $errors[$field] = 'This company registration number already exists.';
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Filter fillable fields
     *
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function filterFillable($data) {
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Encode JSON fields
     *
     * @param array &$data Data array
     */
    protected function encodeJsonFields(&$data) {
        foreach ($this->jsonFields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field])) {
                    $data[$field] = json_encode($data[$field]);
                } elseif (empty($data[$field])) {
                    $data[$field] = '[]';
                }
            }
        }
    }
    
    /**
     * Decode JSON fields
     *
     * @param array &$data Data array
     */
    protected function decodeJsonFields(&$data) {
        foreach ($this->jsonFields as $field) {
            if (isset($data[$field])) {
                $decoded = json_decode($data[$field], true);
                $data[$field] = is_array($decoded) ? $decoded : array();
            }
        }
    }
}