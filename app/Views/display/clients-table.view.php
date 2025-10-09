<?php

use WeCozaClients\Helpers\ViewHelpers;

$baseUrl = get_permalink();
if (!$baseUrl) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = $requestUri ? strtok($requestUri, '?') : '/';
    $baseUrl = home_url($path);
}

$stats = wp_parse_args(
    is_array($stats ?? null) ? $stats : array(),
    array(
        'total_clients' => 0,
        'active_clients' => 0,
        'leads' => 0,
        'cold_calls' => 0,
        'lost_clients' => 0,
    )
);

$currentArgs = array();
if (!empty($_GET) && is_array($_GET)) {
    foreach ($_GET as $key => $value) {
        if (in_array($key, array('client_search', 'client_status', 'client_seta', 'client_page'), true)) {
            continue;
        }
        $currentArgs[$key] = sanitize_text_field(is_array($value) ? implode(',', $value) : $value);
    }
}

$paginationArgs = array_filter(
    array(
        'client_search' => $search,
        'client_status' => $status,
        'client_seta' => $seta,
    ),
    function ($value) {
        return $value !== '' && $value !== null;
    }
);

$statusBadgeMap = array(
    'Active Client' => 'badge-phoenix-primary',
    'Lead' => 'badge-phoenix-warning',
    'Cold Call' => 'badge-phoenix-info',
    'Lost Client' => 'badge-phoenix-danger',
);

// Build edit URL - using capture clients shortcode with edit mode
// This should point to a WordPress page containing [wecoza_capture_clients] shortcode
$editUrl = site_url('/client-management', is_ssl() ? 'https' : 'http');
?>

<div class="card shadow-none border my-3" data-component-card="data-component-card">
    <div class="card-header p-3 border-bottom">
        <div class="row g-3 justify-content-between align-items-center mb-3">
            <div class="col-12 col-md">
                <h4 class="text-body mb-0" data-anchor="data-anchor" id="clients-table-header">
                    Clients Management
                    <i class="bi bi-people ms-2"></i>
                </h4>
            </div>
            
            <?php if (!empty($atts['show_search'])): ?>
            <div class="search-box col-auto">
                <form class="position-relative" method="GET" id="clients-search-form">
                    <?php foreach ($currentArgs as $key => $value): ?>
                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                    <?php endforeach; ?>
                    <input class="form-control search-input search form-control-sm" type="search" name="client_search" id="client_search" value="<?php echo esc_attr($search); ?>" placeholder="Search clients... (Press Enter)" aria-label="Search" title="Type your search query and press Enter to search">
                    <svg class="svg-inline--fa fa-magnifying-glass search-box-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"></path></svg>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshClients()">
                        Refresh
                        <i class="bi bi-arrow-clockwise ms-1"></i>
                    </button>
                    <?php if (!empty($atts['show_export'])): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportClients()">
                        Export
                        <i class="bi bi-download ms-1"></i>
                    </button>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(site_url('/client-management', is_ssl() ? 'https' : 'http')); ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add New Client
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Summary strip -->
        <div class="col-12">
            <div class="scrollbar">
                <div class="row g-0 flex-nowrap">
                    <div class="col-auto border-end pe-4">
                        <h6 class="text-body-tertiary">Total Clients: <?php echo (int) $stats['total_clients']; ?></h6>
                    </div>
                    <div class="col-auto px-4 border-end">
                        <h6 class="text-body-tertiary">Active: <?php echo (int) $stats['active_clients']; ?></h6>
                    </div>
                    <div class="col-auto px-4 border-end">
                        <h6 class="text-body-tertiary">Leads: <?php echo (int) $stats['leads']; ?></h6>
                    </div>
                    <div class="col-auto px-4 border-end">
                        <h6 class="text-body-tertiary">Cold Calls: <?php echo (int) $stats['cold_calls']; ?></h6>
                    </div>
                    <div class="col-auto px-4">
                        <h6 class="text-body-tertiary">Lost: <?php echo (int) $stats['lost_clients']; ?></h6>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($atts['show_filters'])): ?>
        <!-- Filters -->
        <div class="row g-3 mt-2">
            <div class="col-12 col-md-4">
                <form method="GET" id="clients-filter-form">
                    <?php foreach ($currentArgs as $key => $value): ?>
                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                    <?php endforeach; ?>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Status</span>
                        <select class="form-select" name="client_status" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <?php foreach ($status_options as $option): ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php selected($status, $option); ?>><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-4">
                <form method="GET" id="clients-seta-filter-form">
                    <?php foreach ($currentArgs as $key => $value): ?>
                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                    <?php endforeach; ?>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">SETA</span>
                        <select class="form-select" name="client_seta" onchange="this.form.submit()">
                            <option value="">All SETAs</option>
                            <?php foreach ($seta_options as $option): ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php selected($seta, $option); ?>><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="card-body p-4 py-2">
        <div class="table-responsive">
            <?php if (!empty($search)): ?>
                <span id="clients-search-status" class="badge badge-phoenix badge-phoenix-primary mb-2">
                    Searching for: <?php echo esc_html($search); ?>
                </span>
            <?php endif; ?>
            
            <table id="clients-table" class="table table-hover table-sm fs-9 mb-0 overflow-hidden">
                <thead class="border-bottom">
                    <tr>
                        <th scope="col" class="border-0 ps-4" data-sortable="true" data-sort-key="id" data-sort-type="numeric">
                            ID
                            <i class="bi bi-hash ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0" data-sortable="true" data-sort-key="client_name" data-sort-type="text">
                            Client Name
                            <i class="bi bi-person-badge ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0" data-sortable="true" data-sort-key="company_registration_nr" data-sort-type="text">
                            Company Reg
                            <i class="bi bi-building ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0" data-sortable="true" data-sort-key="seta" data-sort-type="text">
                            SETA
                            <i class="bi bi-mortarboard ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0" data-sortable="true" data-sort-key="client_status" data-sort-type="text">
                            Status
                            <i class="bi bi-shield-check ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0" data-sortable="true" data-sort-key="created_at" data-sort-type="date">
                            Created
                            <i class="bi bi-calendar-date ms-1"></i>
                            <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                        </th>
                        <th scope="col" class="border-0 pe-4" data-sortable="false">
                            Actions
                            <i class="bi bi-gear ms-1"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <?php 
                            $statusClass = $statusBadgeMap[$client['client_status']] ?? 'badge-phoenix-secondary';
                            $createdDate = !empty($client['created_at']) ? date('M j, Y', strtotime($client['created_at'])) : '';
                            $editLink = add_query_arg(['mode' => 'update', 'client_id' => $client['id']], $editUrl);
                            ?>
                            <tr data-client-id="<?php echo (int) $client['id']; ?>" data-client-name="<?php echo esc_attr($client['client_name']); ?>">
                                <td class="py-2 align-middle text-center fs-8 white-space-nowrap">
                                    <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                        #<?php echo (int) $client['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-medium">
                                        <?php echo esc_html($client['client_name']); ?>
                                    </span>
                                    <?php if (!empty($client['main_client_id'])): ?>
                                        <small class="text-muted d-block">(Branch of ID: <?php echo (int) $client['main_client_id']; ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo esc_html($client['company_registration_nr'] ?: 'N/A'); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($client['seta'])): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?php echo esc_html($client['seta']); ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo esc_attr($statusClass); ?>">
                                        <?php echo esc_html($client['client_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($createdDate): ?>
                                        <span class="text-nowrap" title="<?php echo esc_attr($client['created_at']); ?>">
                                            <?php echo esc_html($createdDate); ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-link text-body btn-sm dropdown-toggle" style="text-decoration: none;" type="button" id="dropdownMenuButton<?php echo (int) $client['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo (int) $client['id']; ?>">
                                            <li>
                                                <a class="dropdown-item" href="<?php echo esc_url($editLink); ?>">
                                                    Edit Client
                                                    <i class="bi bi-pencil ms-2"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?php echo esc_url(add_query_arg('client_id', $client['id'], site_url('/client-management', is_ssl() ? 'https' : 'http'))); ?>">
                                                    View Details
                                                    <i class="bi bi-eye ms-2"></i>
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="deleteClient(<?php echo (int) $client['id']; ?>, '<?php echo esc_js($client['client_name']); ?>')">
                                                    Delete Client
                                                    <i class="bi bi-trash ms-2"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    <?php if (!empty($search)): ?>
                                        No clients found matching "<?php echo esc_html($search); ?>"
                                    <?php else: ?>
                                        No clients found
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-body-tertiary py-2" id="clients-pagination-container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination-info">
                <small class="text-muted">
                    Showing <?php echo (int) ((($page - 1) * $atts['per_page']) + 1); ?> to <?php echo (int) min($page * $atts['per_page'], $total); ?> 
                    of <?php echo (int) $total; ?> clients
                </small>
            </div>
            <nav aria-label="Clients pagination">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $prevPage = $page > 1 ? $page - 1 : 1;
                    $nextPage = $page < $totalPages ? $page + 1 : $totalPages;
                    
                    $prevUrl = add_query_arg(array_merge($paginationArgs, array('client_page' => $prevPage)), $baseUrl);
                    $nextUrl = add_query_arg(array_merge($paginationArgs, array('client_page' => $nextPage)), $baseUrl);
                    ?>
                    
                    <li class="page-item <?php echo $page === 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url($prevUrl); ?>" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                        </a>
                    </li>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if ($start > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg(array_merge($paginationArgs, array('client_page' => 1)), $baseUrl)) . '">1</a></li>';
                        if ($start > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        $pageUrl = add_query_arg(array_merge($paginationArgs, array('client_page' => $i)), $baseUrl);
                        echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '">';
                        if ($i === $page) {
                            echo '<span class="page-link">' . $i . '</span>';
                        } else {
                            echo '<a class="page-link" href="' . esc_url($pageUrl) . '">' . $i . '</a>';
                        }
                        echo '</li>';
                    }
                    
                    if ($end < $totalPages) {
                        if ($end < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg(array_merge($paginationArgs, array('client_page' => $totalPages)), $baseUrl)) . '">' . $totalPages . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?php echo $page === $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url($nextUrl); ?>" aria-label="Next">
                            <span aria-hidden="true">»</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function refreshClients() {
    window.location.reload();
}

function exportClients() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    
    const nonce = document.createElement('input');
    nonce.type = 'hidden';
    nonce.name = 'nonce';
    nonce.value = '<?php echo wp_create_nonce('wecoza_clients_ajax'); ?>';
    
    const action = document.createElement('input');
    action.type = 'hidden';
    action.name = 'action';
    action.value = 'export_clients';
    
    form.appendChild(nonce);
    form.appendChild(action);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function deleteClient(clientId, clientName) {
    if (confirm('Are you sure you want to delete "' + clientName + '"? This action cannot be undone.')) {
        // Implement delete functionality via AJAX
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'delete_client',
                client_id: clientId,
                nonce: '<?php echo wp_create_nonce('wecoza_clients_ajax'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete client'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the client.');
        });
    }
}
</script>
