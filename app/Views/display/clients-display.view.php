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
        'branch_clients' => 0,
    )
);

$showSearch = !empty($atts['show_search']);
$showFilters = !empty($atts['show_filters']);
$showExport = !empty($atts['show_export']);

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
    'Cold Call' => 'badge-phoenix-secondary',
    'Lost Client' => 'badge-phoenix-danger',
);
?>
<div class="wecoza-clients-display">
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Total Clients', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold mb-0"><?php echo esc_html((int)$stats['total_clients']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Active', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold text-success mb-0"><?php echo esc_html((int)$stats['active_clients']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Leads', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold text-warning mb-0"><?php echo esc_html((int)$stats['leads']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Cold Calls', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold text-secondary mb-0"><?php echo esc_html((int)$stats['cold_calls']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Lost', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold text-danger mb-0"><?php echo esc_html((int)$stats['lost_clients']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body py-3">
                    <p class="text-uppercase text-muted fs-10 mb-1"><?php esc_html_e('Branches', 'wecoza-clients'); ?></p>
                    <p class="fs-4 fw-semibold text-info mb-0"><?php echo esc_html((int)$stats['branch_clients']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($showSearch || $showFilters) : ?>
        <form method="get" class="row g-3 align-items-end mb-4">
            <?php foreach ($currentArgs as $key => $value) : ?>
                <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
            <?php endforeach; ?>
            <input type="hidden" name="client_page" value="1">

            <?php if ($showSearch) : ?>
                <div class="col-md-4">
                    <label for="client_search" class="form-label"><?php esc_html_e('Search', 'wecoza-clients'); ?></label>
                    <input type="text" id="client_search" name="client_search" class="form-control form-control-sm" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search clients…', 'wecoza-clients'); ?>">
                </div>
            <?php endif; ?>

            <?php if ($showFilters) : ?>
                <div class="col-md-3">
                    <label for="client_status" class="form-label"><?php esc_html_e('Status', 'wecoza-clients'); ?></label>
                    <select id="client_status" name="client_status" class="form-select form-select-sm">
                        <option value=""><?php esc_html_e('All Statuses', 'wecoza-clients'); ?></option>
                        <?php foreach ((array) $status_options as $statusValue => $statusLabel) : ?>
                            <option value="<?php echo esc_attr($statusValue); ?>" <?php selected($status, $statusValue); ?>><?php echo esc_html($statusLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="client_seta" class="form-label"><?php esc_html_e('SETA', 'wecoza-clients'); ?></label>
                    <select id="client_seta" name="client_seta" class="form-select form-select-sm">
                        <option value=""><?php esc_html_e('All SETAs', 'wecoza-clients'); ?></option>
                        <?php foreach ((array) $seta_options as $setaValue) : ?>
                            <option value="<?php echo esc_attr($setaValue); ?>" <?php selected($seta, $setaValue); ?>><?php echo esc_html($setaValue); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-2 ms-md-auto text-md-end">
                <button type="submit" class="btn btn-phoenix-primary btn-sm w-100"><?php esc_html_e('Apply', 'wecoza-clients'); ?></button>
            </div>

            <?php if ($showExport) : ?>
                <div class="col-md-2 text-md-end">
                    <button type="button" class="btn btn-phoenix-secondary btn-sm w-100" id="wecoza-clients-export"><?php esc_html_e('Export CSV', 'wecoza-clients'); ?></button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <?php if (!empty($clients)) : ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col"><?php esc_html_e('Client', 'wecoza-clients'); ?></th>
                        <th scope="col"><?php esc_html_e('Registration #', 'wecoza-clients'); ?></th>
                        <th scope="col"><?php esc_html_e('Contact', 'wecoza-clients'); ?></th>
                        <th scope="col"><?php esc_html_e('Status', 'wecoza-clients'); ?></th>
                        <th scope="col"><?php esc_html_e('SETA', 'wecoza-clients'); ?></th>
                        <th scope="col" class="text-end"><?php esc_html_e('Actions', 'wecoza-clients'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client) : ?>
                        <?php
                        $clientId = isset($client['id']) ? (int) $client['id'] : 0;
                        $statusLabel = $client['client_status'] ?? '';
                        $statusClass = $statusBadgeMap[$statusLabel] ?? 'badge-phoenix-secondary';
                        $viewLink = $clientId ? add_query_arg(array_merge($paginationArgs, array('client_id' => $clientId)), $baseUrl) : '';
                        ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?php echo esc_html($client['client_name'] ?? ''); ?></span>
                                <?php if (!empty($client['client_town'])) : ?>
                                    <div class="text-muted fs-10"><?php echo esc_html($client['client_town']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($client['company_registration_nr'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($client['contact_person'])) : ?>
                                    <div class="fw-semibold"><?php echo esc_html($client['contact_person']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($client['contact_person_email'])) : ?>
                                    <a href="mailto:<?php echo esc_attr($client['contact_person_email']); ?>" class="text-decoration-none fs-10"><?php echo esc_html($client['contact_person_email']); ?></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($statusLabel) : ?>
                                    <span class="badge badge-phoenix fs-10 <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($statusLabel); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($client['seta'] ?? ''); ?></td>
                            <td class="text-end">
                                <?php if ($viewLink) : ?>
                                    <a href="<?php echo esc_url($viewLink); ?>" class="btn btn-phoenix-secondary btn-sm"><?php esc_html_e('View', 'wecoza-clients'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <?php echo ViewHelpers::renderPagination($page, $totalPages, $baseUrl, $paginationArgs); ?>
        </div>
    <?php else : ?>
        <?php echo ViewHelpers::renderAlert(__('No clients found.', 'wecoza-clients'), 'warning', false); ?>
    <?php endif; ?>
</div>
