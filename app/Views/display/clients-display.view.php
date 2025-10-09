<?php

// Include the new styled clients table
echo \WeCozaClients\view('display/clients-table', array(
    'clients' => $clients,
    'total' => $total,
    'page' => $page,
    'totalPages' => $totalPages,
    'search' => $search,
    'status' => $status,
    'seta' => $seta,
    'stats' => $stats,
    'seta_options' => $seta_options,
    'status_options' => $status_options,
    'atts' => $atts,
));
?>
