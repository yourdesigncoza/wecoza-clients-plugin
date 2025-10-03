<?php

use WeCozaClients\Models\ClientsModel;
use WeCozaClients\Models\SitesModel;

if (!defined('ABSPATH')) {
    exit;
}

$optionKey = 'wecoza_clients_head_sites_cache_built';

if (get_option($optionKey)) {
    return;
}

require_once dirname(__DIR__, 1) . '/bootstrap.php';

$clientsModel = new ClientsModel();
$sitesModel = new SitesModel();

$clients = $clientsModel->getForDropdown();
$clientIds = array();

foreach ($clients as $client) {
    if (!empty($client['id'])) {
        $clientIds[] = (int) $client['id'];
    }
}

$sitesModel->refreshHeadSiteCache($clientIds);

update_option($optionKey, current_time('mysql'), false);
