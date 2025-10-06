<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;

class LocationsModel {

    protected $table = 'public.locations';

    protected $sitesModel;

    public function __construct() {
        $this->sitesModel = new SitesModel();
    }

    public function validate(array $data) {
        $errors = array();

        $provinceOptions = \WeCozaClients\config('app')['province_options'] ?? array();
        $provinceOptions = array_map('strtolower', $provinceOptions);

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

        if (empty($errors) && $this->locationExists($data['suburb'], $data['town'], $data['province'], $data['postal_code'])) {
            $errors['general'] = __('This location already exists.', 'wecoza-clients');
        }

        return $errors;
    }

    public function create(array $data) {
        $longitude = $this->normalizeCoordinate($data['longitude']);
        $latitude = $this->normalizeCoordinate($data['latitude']);

        $payload = array(
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
            'SELECT location_id, suburb, town, province, postal_code, longitude, latitude FROM public.locations WHERE location_id = :id',
            array(':id' => (int) $locationId)
        );
    }

    protected function locationExists($suburb, $town, $province, $postalCode) {
        $sql = 'SELECT location_id FROM public.locations WHERE LOWER(suburb) = LOWER(:suburb) AND LOWER(town) = LOWER(:town) AND LOWER(province) = LOWER(:province) AND postal_code = :postal LIMIT 1';
        $row = DatabaseService::getRow($sql, array(
            ':suburb' => $suburb,
            ':town' => $town,
            ':province' => $province,
            ':postal' => $postalCode,
        ));

        return !empty($row);
    }

    public function checkDuplicates($suburb, $town) {
        $conditions = array();
        $params = array();
        
        if (!empty($suburb)) {
            $conditions[] = 'LOWER(suburb) = LOWER(:suburb)';
            $params[':suburb'] = $suburb;
        }
        
        if (!empty($town)) {
            $conditions[] = 'LOWER(town) = LOWER(:town)';
            $params[':town'] = $town;
        }
        
        if (empty($conditions)) {
            return array();
        }
        
        $sql = 'SELECT location_id, suburb, town, province, postal_code FROM public.locations WHERE ' . implode(' OR ', $conditions) . ' ORDER BY suburb, town LIMIT 10';
        
        return DatabaseService::getAll($sql, $params);
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
}
