<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;

class LocationsModel {

    protected $table = 'public.locations';

    protected $sitesModel;

    public function __construct() {
        $this->sitesModel = new SitesModel();
    }

    public function validate(array $data, $id = null) {
        $errors = array();

        $provinceOptions = \WeCozaClients\config('app')['province_options'] ?? array();
        $provinceOptions = array_map('strtolower', $provinceOptions);

        if (empty($data['street_address'])) {
            $errors['street_address'] = __('Street address is required.', 'wecoza-clients');
        } elseif (strlen($data['street_address']) > 200) {
            $errors['street_address'] = __('Street address must not exceed 200 characters.', 'wecoza-clients');
        }

        if (empty($data['suburb'])) {
            $errors['suburb'] = __('Suburb is required.', 'wecoza-clients');
        } elseif (strlen($data['suburb']) > 50) {
            $errors['suburb'] = __('Suburb must not exceed 50 characters.', 'wecoza-clients');
        }

        if (empty($data['town'])) {
            $errors['town'] = __('Town is required.', 'wecoza-clients');
        } elseif (strlen($data['town']) > 50) {
            $errors['town'] = __('Town must not exceed 50 characters.', 'wecoza-clients');
        }

        if (empty($data['province'])) {
            $errors['province'] = __('Province is required.', 'wecoza-clients');
        } elseif (strlen($data['province']) > 50) {
            $errors['province'] = __('Province must not exceed 50 characters.', 'wecoza-clients');
        } elseif ($provinceOptions && !in_array(strtolower($data['province']), $provinceOptions, true)) {
            $errors['province'] = __('Please select a valid province.', 'wecoza-clients');
        }

        if (empty($data['postal_code'])) {
            $errors['postal_code'] = __('Postal code is required.', 'wecoza-clients');
        } elseif (strlen($data['postal_code']) > 10) {
            $errors['postal_code'] = __('Postal code must not exceed 10 characters.', 'wecoza-clients');
        }

        $longitude = $this->normalizeCoordinate($data['longitude']);
        $latitude = $this->normalizeCoordinate($data['latitude']);

        if ($longitude === null) {
            $errors['longitude'] = __('Please provide a valid longitude.', 'wecoza-clients');
        } elseif ($longitude < -180 || $longitude > 180) {
            $errors['longitude'] = __('Longitude must be between -180 and 180.', 'wecoza-clients');
        }

        if ($latitude === null) {
            $errors['latitude'] = __('Please provide a valid latitude.', 'wecoza-clients');
        } elseif ($latitude < -90 || $latitude > 90) {
            $errors['latitude'] = __('Latitude must be between -90 and 90.', 'wecoza-clients');
        }

        if (empty($errors) && $this->locationExists($data['street_address'], $data['suburb'], $data['town'], $data['province'], $data['postal_code'], $id)) {
            $errors['general'] = __('This location already exists.', 'wecoza-clients');
        }

        return $errors;
    }

    public function create(array $data) {
        $longitude = $this->normalizeCoordinate($data['longitude']);
        $latitude = $this->normalizeCoordinate($data['latitude']);

        $payload = array(
            'street_address' => $data['street_address'],
            'suburb' => $data['suburb'],
            'town' => $data['town'],
            'province' => $data['province'],
            'postal_code' => $data['postal_code'],
            'longitude' => $longitude,
            'latitude' => $latitude,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );

        $locationId = DatabaseService::insert($this->table, $payload);

        if (!$locationId) {
            return false;
        }

        $this->sitesModel->refreshLocationCache();

        return DatabaseService::getRow(
            'SELECT location_id, street_address, suburb, town, province, postal_code, longitude, latitude FROM public.locations WHERE location_id = :id',
            array(':id' => (int) $locationId)
        );
    }

    protected function locationExists($streetAddress, $suburb, $town, $province, $postalCode, $excludeId = null) {
        $sql = 'SELECT location_id FROM public.locations WHERE LOWER(street_address) = LOWER(:street_address) AND LOWER(suburb) = LOWER(:suburb) AND LOWER(town) = LOWER(:town) AND LOWER(province) = LOWER(:province) AND postal_code = :postal';
        $params = array(
            ':street_address' => $streetAddress,
            ':suburb' => $suburb,
            ':town' => $town,
            ':province' => $province,
            ':postal' => $postalCode,
        );
        if (!empty($excludeId)) {
            $sql .= ' AND location_id <> :exclude_id';
            $params[':exclude_id'] = (int) $excludeId;
        }
        $sql .= ' LIMIT 1';
        $row = DatabaseService::getRow($sql, $params);

        return !empty($row);
    }

    public function checkDuplicates($streetAddress, $suburb, $town) {
        $conditions = array();
        $params = array();
        
        // Build flexible search conditions - check both town and suburb for any search term
        if (!empty($town)) {
            $conditions[] = '(LOWER(town) LIKE LOWER(:town_search) OR LOWER(suburb) LIKE LOWER(:town_search_suburb))';
            $params[':town_search'] = '%' . $town . '%';
            $params[':town_search_suburb'] = '%' . $town . '%';
        }
        
        if (!empty($suburb)) {
            $conditions[] = '(LOWER(suburb) LIKE LOWER(:suburb_search) OR LOWER(town) LIKE LOWER(:suburb_search_town))';
            $params[':suburb_search'] = '%' . $suburb . '%';
            $params[':suburb_search_town'] = '%' . $suburb . '%';
        }
        
        if (!empty($streetAddress)) {
            // Add exact match first, then LIKE as fallback
            $conditions[] = '(LOWER(street_address) = LOWER(:street_address_exact) OR LOWER(street_address) LIKE LOWER(:street_address_like))';
            $params[':street_address_exact'] = trim($streetAddress);
            $params[':street_address_like'] = '%' . trim($streetAddress) . '%';
        }
        
        if (empty($conditions)) {
            return array();
        }
        
        $sql = 'SELECT location_id, street_address, suburb, town, province, postal_code FROM public.locations WHERE ' . implode(' OR ', $conditions) . ' ORDER BY street_address, suburb, town LIMIT 10';
        
        // Debug: Log the SQL and params for troubleshooting
        // error_log('Duplicate check SQL: ' . $sql);
        // error_log('Duplicate check params: ' . print_r($params, true));
        
        $results = DatabaseService::getAll($sql, $params);
        
        // Debug: Log the results
        // error_log('Duplicate check results: ' . print_r($results, true));
        
        return $results;
    }

    protected function normalizeCoordinate($value) {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    public function getAll(array $params = array()) {
        $limit = isset($params["limit"]) ? max(1, (int) $params["limit"]) : 10;
        $offset = isset($params["offset"]) ? max(0, (int) $params["offset"]) : 0;
        $search = isset($params["search"]) ? trim((string) $params["search"]) : '';

        $where = array();
        $bind = array(
            ':limit' => $limit,
            ':offset' => $offset,
        );

        if ($search !== '') {
            $where[] = '(street_address ILIKE :s OR suburb ILIKE :s OR town ILIKE :s OR province ILIKE :s OR postal_code ILIKE :s)';
            $bind[':s'] = '%' . $search . '%';
        }

        $sql = 'SELECT location_id, street_address, suburb, town, province, postal_code FROM public.locations'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY province, town, suburb, street_address'
             . ' LIMIT :limit OFFSET :offset';

        return DatabaseService::getAll($sql, $bind) ?: array();
    }

    public function count(array $params = array()) {
        $search = isset($params["search"]) ? trim((string) $params["search"]) : '';
        $where = array();
        $bind = array();

        if ($search !== '') {
            $where[] = '(street_address ILIKE :s OR suburb ILIKE :s OR town ILIKE :s OR province ILIKE :s OR postal_code ILIKE :s)';
            $bind[':s'] = '%' . $search . '%';
        }

        $sql = 'SELECT COUNT(*) AS cnt FROM public.locations' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
        $row = DatabaseService::getRow($sql, $bind);
        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }

    public function getById($id) {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $sql = 'SELECT location_id, street_address, suburb, town, province, postal_code, longitude, latitude FROM public.locations WHERE location_id = :id LIMIT 1';
        $row = DatabaseService::getRow($sql, array(':id' => $id));
        if (!$row) {
            return null;
        }
        // Cast types
        $row['location_id'] = (int) $row['location_id'];
        $row['longitude'] = isset($row['longitude']) ? (float) $row['longitude'] : null;
        $row['latitude'] = isset($row['latitude']) ? (float) $row['latitude'] : null;
        return $row;
    }

    public function update($id, array $data) {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $longitude = $this->normalizeCoordinate($data['longitude']);
        $latitude = $this->normalizeCoordinate($data['latitude']);

        $payload = array(
            'street_address' => $data['street_address'],
            'suburb' => $data['suburb'],
            'town' => $data['town'],
            'province' => $data['province'],
            'postal_code' => $data['postal_code'],
            'longitude' => $longitude,
            'latitude' => $latitude,
            'updated_at' => current_time('mysql'),
        );

        $where = 'location_id = :id';
        $params = array(':id' => $id);
        $result = DatabaseService::update($this->table, $payload, $where, $params);

        if ($result === false) {
            return false;
        }

        $this->sitesModel->refreshLocationCache();

        return $this->getById($id);
    }
}
