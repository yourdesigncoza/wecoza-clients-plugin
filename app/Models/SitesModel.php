<?php

namespace WeCozaClients\Models;

use WeCozaClients\Services\Database\DatabaseService;

class SitesModel {

    protected const LOCATION_CACHE_TRANSIENT = 'wecoza_clients_location_cache';
    protected const HEAD_SITE_CACHE_TRANSIENT = 'wecoza_clients_head_sites_cache';

    protected $table = 'sites';

    protected $primaryKey = 'site_id';

    protected $locationsTable = 'public.locations';

    protected $locationsEnabled = null;

    protected static $locationCache = null;

    protected static $headSiteCache = null;

    protected function getLocationCacheKey() {
        return self::LOCATION_CACHE_TRANSIENT;
    }

    protected function ensureLocationCache() {
        if (self::$locationCache === null) {
            $cached = $this->loadLocationCache();
            if (!is_array($cached) || !isset($cached['hierarchy'], $cached['map'])) {
                $cached = $this->rebuildLocationCache();
            }

            if (!is_array($cached) || !isset($cached['hierarchy'], $cached['map'])) {
                $cached = $this->buildLocationCache();
            }

            self::$locationCache = $cached;
            $this->locationsEnabled = !empty($cached['hierarchy']) || !empty($cached['map']);
        }

        return self::$locationCache;
    }

    protected function loadLocationCache() {
        $key = $this->getLocationCacheKey();
        $cache = get_transient($key);
        if ($cache !== false) {
            return $cache;
        }

        $option = get_option($key, null);
        return is_array($option) ? $option : false;
    }

    protected function buildLocationCache() {
        return array(
            'hierarchy' => array(),
            'map' => array(),
        );
    }

    public function refreshLocationCache() {
        self::$locationCache = null;
        return $this->rebuildLocationCache();
    }

    public function clearLocationCache() {
        $key = $this->getLocationCacheKey();
        delete_transient($key);
        delete_option($key);
        self::$locationCache = array(
            'hierarchy' => array(),
            'map' => array(),
        );
        $this->locationsEnabled = false;
    }

    protected function persistLocationCache(array $cache) {
        $key = $this->getLocationCacheKey();
        set_transient($key, $cache, 0);
        update_option($key, $cache, false);
    }

    protected function fetchAllLocations() {
        $sql = 'SELECT location_id, suburb, town, province, postal_code, longitude, latitude, street_address FROM public.locations ORDER BY province, town, suburb, location_id';
        $rows = DatabaseService::getAll($sql) ?: array();

        return array_map(function ($row) {
            return array(
                'location_id' => isset($row['location_id']) ? (int) $row['location_id'] : 0,
                'suburb' => isset($row['suburb']) ? trim((string) $row['suburb']) : '',
                'town' => isset($row['town']) ? trim((string) $row['town']) : '',
                'province' => isset($row['province']) ? trim((string) $row['province']) : '',
                'postal_code' => isset($row['postal_code']) ? trim((string) $row['postal_code']) : '',
                'longitude' => isset($row['longitude']) ? (float) $row['longitude'] : null,
                'latitude' => isset($row['latitude']) ? (float) $row['latitude'] : null,
                'street_address' => isset($row['street_address']) ? trim((string) $row['street_address']) : '',
            );
        }, $rows);
    }

    public function rebuildLocationCache() {
        $rows = $this->fetchAllLocations();

        $hierarchy = array();
        $provinceIndex = array();
        $townIndex = array();
        $map = array();

        foreach ($rows as $row) {
            $id = $row['location_id'];
            if ($id <= 0) {
                continue;
            }

            $suburb = $row['suburb'];
            $town = $row['town'];
            $province = $row['province'];
            $postal = $row['postal_code'];
            $streetAddress = $row['street_address'];

            $map[$id] = array(
                'id' => $id,
                'suburb' => $suburb,
                'town' => $town,
                'province' => $province,
                'postal_code' => $postal,
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
                'street_address' => $streetAddress,
            );

            if ($province === '' || $town === '' || $suburb === '') {
                continue;
            }

            if (!isset($provinceIndex[$province])) {
                $provinceIndex[$province] = count($hierarchy);
                $hierarchy[] = array(
                    'name' => $province,
                    'towns' => array(),
                );
            }

            $provincePos = $provinceIndex[$province];

            if (!isset($townIndex[$province])) {
                $townIndex[$province] = array();
            }

            if (!isset($townIndex[$province][$town])) {
                $townIndex[$province][$town] = count($hierarchy[$provincePos]['towns']);
                $hierarchy[$provincePos]['towns'][] = array(
                    'name' => $town,
                    'suburbs' => array(),
                );
            }

            $townPos = $townIndex[$province][$town];

            $hierarchy[$provincePos]['towns'][$townPos]['suburbs'][] = array(
                'id' => $id,
                'name' => $suburb,
                'postal_code' => $postal,
                'street_address' => $streetAddress,
            );
        }

        $cache = array(
            'hierarchy' => $hierarchy,
            'map' => $map,
        );

        self::$locationCache = $cache;
        $this->persistLocationCache($cache);
        $this->locationsEnabled = !empty($map);

        return $cache;
    }

    protected function getHeadSiteCacheKey() {
        return self::HEAD_SITE_CACHE_TRANSIENT;
    }

    protected function ensureHeadSiteCache() {
        if (self::$headSiteCache === null) {
            $cached = $this->loadHeadSiteCache();
            if (!is_array($cached) || !isset($cached['map'])) {
                $cached = array(
                    'map' => array(),
                );
            }
            self::$headSiteCache = $cached;
        }

        return self::$headSiteCache;
    }

    protected function loadHeadSiteCache() {
        $key = $this->getHeadSiteCacheKey();
        $cache = get_transient($key);
        if ($cache !== false) {
            return $cache;
        }

        $option = get_option($key, null);
        return is_array($option) ? $option : false;
    }

    protected function persistHeadSiteCache(array $cache) {
        $key = $this->getHeadSiteCacheKey();
        set_transient($key, $cache, 0);
        update_option($key, $cache, false);
    }

    protected function primeHeadSiteCache(array $clientIds) {
        $clientIds = array_values(array_unique(array_filter(array_map('intval', $clientIds))));
        if (empty($clientIds)) {
            return $this->ensureHeadSiteCache();
        }

        $cache = $this->ensureHeadSiteCache();
        $map = $cache['map'];
        $missing = array();

        foreach ($clientIds as $clientId) {
            if (!array_key_exists($clientId, $map)) {
                $missing[] = $clientId;
            }
        }

        if ($missing) {
            $rows = $this->fetchHeadSitesFromDatabase($missing);
            if ($rows) {
                $rows = $this->hydrateLocationForSites($rows);
                $indexed = array();
                foreach ($rows as $row) {
                    $clientId = isset($row['client_id']) ? (int) $row['client_id'] : 0;
                    if ($clientId > 0 && !isset($indexed[$clientId])) {
                        $indexed[$clientId] = $row;
                    }
                }

                foreach ($missing as $clientId) {
                    if (isset($indexed[$clientId])) {
                        $cache['map'][$clientId] = $indexed[$clientId];
                    } elseif (!isset($cache['map'][$clientId])) {
                        $cache['map'][$clientId] = null;
                    }
                }
            } else {
                foreach ($missing as $clientId) {
                    if (!isset($cache['map'][$clientId])) {
                        $cache['map'][$clientId] = null;
                    }
                }
            }

            self::$headSiteCache = $cache;
            $this->persistHeadSiteCache($cache);
        }

        return self::$headSiteCache;
    }

    protected function fetchHeadSitesFromDatabase(array $clientIds) {
        $clientIds = array_values(array_unique(array_filter(array_map('intval', $clientIds))));
        if (empty($clientIds)) {
            return array();
        }

        $placeholders = array();
        $params = array();
        foreach ($clientIds as $index => $id) {
            $key = ':client' . $index;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql = 'SELECT s.site_id, s.client_id, s.site_name, s.address_line_1, s.address_line_2, s.address, s.place_id, s.created_at, s.updated_at
                FROM ' . $this->table . ' s
                WHERE s.parent_site_id IS NULL AND s.client_id IN (' . implode(', ', $placeholders) . ')
                ORDER BY s.client_id, s.site_id';

        return DatabaseService::getAll($sql, $params) ?: array();
    }

    public function refreshHeadSiteCache($clientIds = null) {
        if ($clientIds === null) {
            self::$headSiteCache = null;
            return $this->ensureHeadSiteCache();
        }

        $clientIds = array_values(array_unique(array_filter(array_map('intval', (array) $clientIds))));
        if (empty($clientIds)) {
            return $this->ensureHeadSiteCache();
        }

        $cache = $this->ensureHeadSiteCache();
        $rows = $this->fetchHeadSitesFromDatabase($clientIds);
        $rows = $rows ? $this->hydrateLocationForSites($rows) : array();
        $indexed = array();
        foreach ($rows as $row) {
            $clientId = isset($row['client_id']) ? (int) $row['client_id'] : 0;
            if ($clientId > 0) {
                $indexed[$clientId] = $row;
            }
        }

        foreach ($clientIds as $clientId) {
            $cache['map'][$clientId] = $indexed[$clientId] ?? null;
        }

        self::$headSiteCache = $cache;
        $this->persistHeadSiteCache($cache);

        return self::$headSiteCache;
    }

    public function clearHeadSiteCache($clientIds = null) {
        if ($clientIds === null) {
            $key = $this->getHeadSiteCacheKey();
            delete_transient($key);
            delete_option($key);
            self::$headSiteCache = array(
                'map' => array(),
            );
            return;
        }

        $clientIds = array_values(array_unique(array_filter(array_map('intval', (array) $clientIds))));
        if (empty($clientIds)) {
            return;
        }

        $cache = $this->ensureHeadSiteCache();
        foreach ($clientIds as $clientId) {
            unset($cache['map'][$clientId]);
        }

        self::$headSiteCache = $cache;
        $this->persistHeadSiteCache($cache);
    }

    public function getHeadSitesForClients(array $clientIds) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $clientIds))));
        if (empty($ids)) {
            return array();
        }
        $cache = $this->primeHeadSiteCache($ids);
        $map = array();
        foreach ($ids as $clientId) {
            if (!empty($cache['map'][$clientId])) {
                $map[$clientId] = $cache['map'][$clientId];
            }
        }

        return $map;
    }

    public function getHeadSite($clientId) {
        $clientId = (int) $clientId;
        if ($clientId <= 0) {
            return null;
        }
        $cache = $this->primeHeadSiteCache(array($clientId));

        return !empty($cache['map'][$clientId]) ? $cache['map'][$clientId] : null;
    }

    public function getSitesByClient($clientId) {
        $clientId = (int) $clientId;
        if ($clientId <= 0) {
            return array('head' => null, 'sub_sites' => array());
        }

        $sql = 'SELECT site_id, client_id, site_name, address_line_1, address_line_2, address, place_id, parent_site_id, created_at, updated_at
                FROM ' . $this->table . '
                WHERE client_id = :client_id
                ORDER BY parent_site_id NULLS FIRST, site_id';

        $rows = DatabaseService::getAll($sql, array(':client_id' => $clientId)) ?: array();
        if (!$rows) {
            return array('head' => null, 'sub_sites' => array());
        }

        $rows = $this->hydrateLocationForSites($rows);

        $head = null;
        $subs = array();
        foreach ($rows as $row) {
            if (empty($row['parent_site_id'])) {
                if ($head === null) {
                    $head = $row;
                }
                continue;
            }

            $subs[] = $row;
        }

        return array('head' => $head, 'sub_sites' => $subs);
    }

    public function saveHeadSite($clientId, array $data) {
        $clientId = (int) $clientId;
        if ($clientId <= 0) {
            return false;
        }

        $siteId = isset($data['site_id']) ? (int) $data['site_id'] : 0;

        $payload = $this->filterSitePayload($data);
        $payload['client_id'] = $clientId;
        $payload['parent_site_id'] = null;
        $payload['updated_at'] = current_time('mysql');

        if (empty($payload['site_name'])) {
            $payload['site_name'] = $data['site_name_fallback'] ?? ('Client ' . $clientId);
        }

        if (!empty($payload['address_line_1']) && !empty($payload['address_line_2'])) {
            $payload['address'] = $payload['address_line_1'] . ' ' . $payload['address_line_2'];
        } elseif (!empty($payload['address_line_1'])) {
            $payload['address'] = $payload['address_line_1'];
        }

        if ($siteId > 0) {
            $where = $this->primaryKey . ' = :id AND client_id = :client_id';
            $params = array(':id' => $siteId, ':client_id' => $clientId);
            $result = DatabaseService::update($this->table, $payload, $where, $params);
            if ($result === false) {
                return false;
            }

            $this->refreshHeadSiteCache(array($clientId));

            return $siteId;
        }

        $payload['created_at'] = current_time('mysql');
        $insert = DatabaseService::insert($this->table, $payload);
        if (!$insert) {
            return false;
        }

        $siteId = (int) $insert;
        $this->refreshHeadSiteCache(array($clientId));

        return $siteId;
    }

    public function validateHeadSite(array $data) {
        $errors = array();

        $name = trim((string) ($data['site_name'] ?? ''));
        $address1 = trim((string) ($data['address_line_1'] ?? ''));
        $placeId = isset($data['place_id']) ? (int) $data['place_id'] : 0;

        if ($name === '') {
            $errors['site_name'] = __('Site name is required.', 'wecoza-clients');
        }

        if ($address1 === '') {
            $errors['address_line_1'] = __('Site address line 1 is required.', 'wecoza-clients');
        }

        if ($placeId <= 0) {
            $errors['place_id'] = __('Please select a valid suburb for the site.', 'wecoza-clients');
        } elseif (!$this->locationsAvailable() || !$this->getLocationById($placeId)) {
            $errors['place_id'] = __('Selected suburb could not be resolved. Please choose another.', 'wecoza-clients');
        }

        return $errors;
    }

    public function getSiteById($siteId) {
        $siteId = (int) $siteId;
        if ($siteId <= 0) {
            return null;
        }

        $sql = 'SELECT site_id, client_id, site_name, address_line_1, address_line_2, address, place_id, parent_site_id, created_at, updated_at
                FROM ' . $this->table . '
                WHERE site_id = :id
                LIMIT 1';

        $row = DatabaseService::getRow($sql, array(':id' => $siteId));
        if (!$row) {
            return null;
        }

        $hydrated = $this->hydrateLocationForSites(array($row));
        return $hydrated ? $hydrated[0] : $row;
    }

    public function ensureSiteBelongsToClient($siteId, $clientId) {
        $site = $this->getSiteById($siteId);
        if (!$site) {
            return false;
        }

        return (int) $site['client_id'] === (int) $clientId;
    }

    public function hydrateClients(array &$clients) {
        if (empty($clients)) {
            return;
        }

        $single = false;
        if (isset($clients['id'])) {
            $clients = array($clients);
            $single = true;
        }

        $clientIds = array();
        foreach ($clients as $row) {
            if (!empty($row['id'])) {
                $clientIds[] = (int) $row['id'];
            }
        }

        $headSites = $this->getHeadSitesForClients($clientIds);

        foreach ($clients as &$row) {
            $clientId = (int) ($row['id'] ?? 0);
            if (!$clientId || !isset($headSites[$clientId])) {
                continue;
            }

            $site = $headSites[$clientId];
            $row['head_site'] = $site;
            $row['site_id'] = $site['site_id'];
            $row['site_name'] = $site['site_name'];
            $row['client_street_address'] = $site['address_line_1'];
            $row['client_address_line_2'] = $site['address_line_2'];
            $row['client_town_id'] = $site['place_id'];
            if (!empty($site['location'])) {
                $location = $site['location'];
                $row['client_suburb'] = $location['suburb'] ?? '';
                $row['client_postal_code'] = $location['postal_code'] ?? '';
                $row['client_province'] = $location['province'] ?? '';
                $row['client_town'] = $location['town'] ?? '';
                $row['client_location'] = $location;
            }
        }
        unset($row);

        if ($single) {
            $clients = reset($clients);
        }
    }

    protected function filterSitePayload(array $data) {
        $allowed = array('site_name', 'address_line_1', 'address_line_2', 'address', 'place_id');
        $result = array();

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($field === 'place_id') {
                    $value = $value ? (int) $value : null;
                }
                $result[$field] = $value === '' ? null : $value;
            }
        }

        return $result;
    }

    protected function hydrateLocationForSites(array $sites) {
        if (empty($sites) || !$this->locationsAvailable()) {
            return $sites;
        }

        $placeIds = array();
        foreach ($sites as $row) {
            if (!empty($row['place_id'])) {
                $placeIds[] = (int) $row['place_id'];
            }
        }

        $locations = $this->getLocationsByIds($placeIds);

        foreach ($sites as &$site) {
            $placeId = isset($site['place_id']) ? (int) $site['place_id'] : 0;
            if ($placeId && isset($locations[$placeId])) {
                $site['location'] = $locations[$placeId];
            } else {
                $site['location'] = null;
            }
        }
        unset($site);

        return $sites;
    }

    protected function locationsAvailable() {
        if ($this->locationsEnabled === null) {
            $cache = $this->ensureLocationCache();
            $this->locationsEnabled = !empty($cache['hierarchy']) || !empty($cache['map']);
        }

        return (bool) $this->locationsEnabled;
    }

    public function getLocationHierarchy($useCache = true) {
        if (!$useCache) {
            $cache = $this->refreshLocationCache();
            return $cache['hierarchy'];
        }

        $cache = $this->ensureLocationCache();
        return $cache['hierarchy'];
    }

    public function getLocationById($locationId) {
        $locationId = (int) $locationId;
        if ($locationId <= 0) {
            return null;
        }

        $locations = $this->getLocationsByIds(array($locationId));

        return isset($locations[$locationId]) ? $locations[$locationId] : null;
    }

    protected function getLocationsByIds(array $ids) {
        if (!$this->locationsAvailable()) {
            return array();
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return array();
        }

        $cache = $this->ensureLocationCache();
        $map = $cache['map'];

        $locations = array();
        $missing = array();

        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $locations[$id] = $map[$id];
            } else {
                $missing[] = $id;
            }
        }

        if ($missing) {
            $cache = $this->refreshLocationCache();
            $map = $cache['map'];

            foreach ($missing as $id) {
                if (isset($map[$id])) {
                    $locations[$id] = $map[$id];
                }
            }
        }

        return $locations;
    }
}
