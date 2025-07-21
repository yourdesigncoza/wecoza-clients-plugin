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
        'current_classes',
        'stopped_classes',
        'deliveries',
        'collections',
        'cancellations',
        'class_restarts',
        'class_stops',
        'assessments',
        'progressions',
        'created_by',
        'updated_by'
    ];
    
    /**
     * JSONB fields
     *
     * @var array
     */
    protected $jsonFields = [
        'current_classes',
        'stopped_classes',
        'deliveries',
        'collections',
        'cancellations',
        'assessments',
        'progressions'
    ];
    
    /**
     * Date fields
     *
     * @var array
     */
    protected $dateFields = [
        'financial_year_end',
        'bbbee_verification_date',
        'class_restarts',
        'class_stops'
    ];
    
    /**
     * Get all clients
     *
     * @param array $params Query parameters
     * @return array
     */
    public function getAll($params = array()) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $bindings = array();
        
        // Add search filter
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $sql .= " AND (
                client_name ILIKE :search 
                OR company_registration_nr ILIKE :search2
                OR contact_person ILIKE :search3
                OR contact_person_email ILIKE :search4
                OR client_town ILIKE :search5
            )";
            $bindings[':search'] = $search;
            $bindings[':search2'] = $search;
            $bindings[':search3'] = $search;
            $bindings[':search4'] = $search;
            $bindings[':search5'] = $search;
        }
        
        // Add status filter
        if (!empty($params['status'])) {
            $sql .= " AND client_status = :status";
            $bindings[':status'] = $params['status'];
        }
        
        // Add SETA filter
        if (!empty($params['seta'])) {
            $sql .= " AND seta = :seta";
            $bindings[':seta'] = $params['seta'];
        }
        
        // Add branch filter
        if (isset($params['branch_of'])) {
            if ($params['branch_of'] === null) {
                $sql .= " AND branch_of IS NULL";
            } else {
                $sql .= " AND branch_of = :branch_of";
                $bindings[':branch_of'] = $params['branch_of'];
            }
        }
        
        // Add sorting
        $orderBy = !empty($params['order_by']) ? $params['order_by'] : 'client_name';
        $orderDir = !empty($params['order_dir']) && strtoupper($params['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
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
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $result = DatabaseService::getRow($sql, [':id' => $id]);
        
        if ($result) {
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
        $sql = "SELECT * FROM {$this->table} WHERE company_registration_nr = :reg_nr AND deleted_at IS NULL";
        $result = DatabaseService::getRow($sql, [':reg_nr' => $regNr]);
        
        if ($result) {
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
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        // Encode JSON fields
        $this->encodeJsonFields($data);
        
        // Add timestamps
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        
        // Add current user as creator
        if (!isset($data['created_by'])) {
            $data['created_by'] = get_current_user_id();
        }
        
        return DatabaseService::insert($this->table, $data);
    }
    
    /**
     * Update client
     *
     * @param int $id Client ID
     * @param array $data Client data
     * @return bool
     */
    public function update($id, $data) {
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        // Encode JSON fields
        $this->encodeJsonFields($data);
        
        // Update timestamp
        $data['updated_at'] = current_time('mysql');
        
        // Add current user as updater
        if (!isset($data['updated_by'])) {
            $data['updated_by'] = get_current_user_id();
        }
        
        $result = DatabaseService::update(
            $this->table,
            $data,
            'id = :id AND deleted_at IS NULL',
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
        
        $result = DatabaseService::update(
            $this->table,
            $data,
            'id = :id AND deleted_at IS NULL',
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
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        $bindings = array();
        
        // Add search filter
        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $sql .= " AND (
                client_name ILIKE :search 
                OR company_registration_nr ILIKE :search2
                OR contact_person ILIKE :search3
                OR contact_person_email ILIKE :search4
                OR client_town ILIKE :search5
            )";
            $bindings[':search'] = $search;
            $bindings[':search2'] = $search;
            $bindings[':search3'] = $search;
            $bindings[':search4'] = $search;
            $bindings[':search5'] = $search;
        }
        
        // Add status filter
        if (!empty($params['status'])) {
            $sql .= " AND client_status = :status";
            $bindings[':status'] = $params['status'];
        }
        
        // Add SETA filter
        if (!empty($params['seta'])) {
            $sql .= " AND seta = :seta";
            $bindings[':seta'] = $params['seta'];
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
        $sql = "SELECT * FROM client_statistics";
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
        $sql = "SELECT * FROM {$this->table} WHERE branch_of = :parent_id AND deleted_at IS NULL ORDER BY client_name";
        $results = DatabaseService::getAll($sql, [':parent_id' => $parentId]);
        
        if ($results) {
            foreach ($results as &$row) {
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
        $sql = "SELECT id, client_name, company_registration_nr FROM {$this->table} 
                WHERE deleted_at IS NULL AND branch_of IS NULL 
                ORDER BY client_name";
        
        return DatabaseService::getAll($sql) ?: array();
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