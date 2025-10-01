<?php

use WeCozaClients\Helpers\ViewHelpers;

$client = is_array($client ?? null) ? $client : array();
$branchClients = is_array($branchClients ?? null) ? $branchClients : array();
$parentClient = is_array($parentClient ?? null) ? $parentClient : null;

$baseUrl = get_permalink();
if (!$baseUrl) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = $requestUri ? strtok($requestUri, '?') : '/';
    $baseUrl = home_url($path);
}

$statusBadgeMap = array(
    'Active Client' => 'badge-phoenix-primary',
    'Lead' => 'badge-phoenix-warning',
    'Cold Call' => 'badge-phoenix-secondary',
    'Lost Client' => 'badge-phoenix-danger',
);

$statusLabel = $client['client_status'] ?? '';
$statusClass = $statusBadgeMap[$statusLabel] ?? 'badge-phoenix-secondary';

$addressParts = array_filter(array(
    $client['client_street_address'] ?? '',
    $client['client_suburb'] ?? '',
    $client['client_town'] ?? '',
    $client['client_postal_code'] ?? '',
));

$formatListValue = function ($value) {
    if (is_array($value)) {
        return wp_json_encode($value);
    }

    return (string) $value;
};

$renderDataList = function ($items) use ($formatListValue) {
    if (empty($items) || !is_array($items)) {
        return '<p class="text-muted mb-0">' . esc_html__('No records captured.', 'wecoza-clients') . '</p>';
    }

    $output = '<ul class="list-unstyled mb-0 small">';
    foreach ($items as $item) {
        $output .= '<li class="mb-1">' . esc_html($formatListValue($item)) . '</li>';
    }
    $output .= '</ul>';

    return $output;
};
?>
<div class="wecoza-single-client">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 mb-3">
                <div>
                    <h2 class="h4 mb-1"><?php echo esc_html($client['client_name'] ?? __('Client', 'wecoza-clients')); ?></h2>
                    <?php if (!empty($client['company_registration_nr'])) : ?>
                        <p class="text-muted mb-0 fs-10"><?php echo esc_html__('Company Registration #', 'wecoza-clients'); ?>: <?php echo esc_html($client['company_registration_nr']); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($statusLabel) : ?>
                    <span class="badge badge-phoenix fs-10 <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($statusLabel); ?></span>
                <?php endif; ?>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('Communication Type', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html($client['client_communication'] ?? ''); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('SETA', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html($client['seta'] ?? ''); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('Financial Year End', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html(ViewHelpers::formatDate($client['financial_year_end'] ?? '')); ?></p>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('BBBEE Verification Date', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html(ViewHelpers::formatDate($client['bbbee_verification_date'] ?? '')); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('Class Restarts', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html(ViewHelpers::formatDate($client['class_restarts'] ?? '')); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <p class="text-uppercase text-muted fs-10 mb-2"><?php esc_html_e('Class Stops', 'wecoza-clients'); ?></p>
                        <p class="fw-semibold mb-0"><?php echo esc_html(ViewHelpers::formatDate($client['class_stops'] ?? '')); ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($client['quotes'])) : ?>
                <div class="mt-3">
                    <a href="<?php echo esc_url($client['quotes']); ?>" class="btn btn-link p-0" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Download Quotes', 'wecoza-clients'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="h6 mb-0"><?php esc_html_e('Primary Contact', 'wecoza-clients'); ?></h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <?php if (!empty($client['contact_person'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Name', 'wecoza-clients'); ?></dt>
                            <dd class="col-7 fw-semibold"><?php echo esc_html($client['contact_person']); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['contact_person_email'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Email', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><a href="mailto:<?php echo esc_attr($client['contact_person_email']); ?>"><?php echo esc_html($client['contact_person_email']); ?></a></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['contact_person_cellphone'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Cellphone', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html(ViewHelpers::formatPhone($client['contact_person_cellphone'])); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['contact_person_tel'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Telephone', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html(ViewHelpers::formatPhone($client['contact_person_tel'])); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="h6 mb-0"><?php esc_html_e('Address', 'wecoza-clients'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($addressParts)) : ?>
                        <address class="mb-0 small">
                            <?php echo esc_html(implode(', ', $addressParts)); ?>
                        </address>
                    <?php else : ?>
                        <p class="text-muted mb-0"><?php esc_html_e('No address captured.', 'wecoza-clients'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h3 class="h6 mb-0"><?php esc_html_e('Key Dates', 'wecoza-clients'); ?></h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <?php if (!empty($client['created_at'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Created', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html(ViewHelpers::formatDate($client['created_at'])); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['updated_at'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Last Updated', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html(ViewHelpers::formatDate($client['updated_at'])); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['created_by'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Created By (ID)', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html((string) $client['created_by']); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($client['updated_by'])) : ?>
                            <dt class="col-5 text-muted"><?php esc_html_e('Updated By (ID)', 'wecoza-clients'); ?></dt>
                            <dd class="col-7"><?php echo esc_html((string) $client['updated_by']); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if ($parentClient) : ?>
                <?php
                $parentLink = isset($parentClient['id']) ? add_query_arg(array('client_id' => (int) $parentClient['id']), $baseUrl) : '';
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h3 class="h6 mb-0"><?php esc_html_e('Parent Client', 'wecoza-clients'); ?></h3>
                    </div>
                    <div class="card-body small">
                        <p class="fw-semibold mb-1"><?php echo esc_html($parentClient['client_name'] ?? ''); ?></p>
                        <?php if (!empty($parentClient['company_registration_nr'])) : ?>
                            <p class="text-muted mb-2"><?php esc_html_e('Registration #', 'wecoza-clients'); ?>: <?php echo esc_html($parentClient['company_registration_nr']); ?></p>
                        <?php endif; ?>
                        <?php if ($parentLink) : ?>
                            <a class="btn btn-phoenix-secondary btn-sm" href="<?php echo esc_url($parentLink); ?>"><?php esc_html_e('View Parent', 'wecoza-clients'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($branchClients)) : ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0"><?php esc_html_e('Branch Clients', 'wecoza-clients'); ?></h3>
                <span class="badge bg-light text-dark"><?php echo esc_html(count($branchClients)); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><?php esc_html_e('Client', 'wecoza-clients'); ?></th>
                                <th scope="col"><?php esc_html_e('Registration #', 'wecoza-clients'); ?></th>
                                <th scope="col"><?php esc_html_e('Status', 'wecoza-clients'); ?></th>
                                <th scope="col" class="text-end"><?php esc_html_e('Actions', 'wecoza-clients'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($branchClients as $branch) : ?>
                                <?php
                                $branchStatus = $branch['client_status'] ?? '';
                                $branchClass = $statusBadgeMap[$branchStatus] ?? 'badge-phoenix-secondary';
                                $branchLink = isset($branch['id']) ? add_query_arg(array('client_id' => (int) $branch['id']), $baseUrl) : '';
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold"><?php echo esc_html($branch['client_name'] ?? ''); ?></span>
                                        <?php if (!empty($branch['client_town'])) : ?>
                                            <div class="text-muted fs-10"><?php echo esc_html($branch['client_town']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($branch['company_registration_nr'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($branchStatus) : ?>
                                            <span class="badge badge-phoenix fs-10 <?php echo esc_attr($branchClass); ?>"><?php echo esc_html($branchStatus); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($branchLink) : ?>
                                            <a class="btn btn-phoenix-secondary btn-sm" href="<?php echo esc_url($branchLink); ?>"><?php esc_html_e('View Branch', 'wecoza-clients'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php echo ViewHelpers::renderAlert(__('No branch clients linked.', 'wecoza-clients'), 'info', false); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
