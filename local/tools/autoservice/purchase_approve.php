<?php

/**
 * Согласование ручных заявок.
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Доступ только администратору (на демо — согласующий)');
}

if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
    die('Установите модуль otus.autoservice');
}

require __DIR__ . '/include/layout.php';

$service = new \Otus\Autoservice\Service\PurchaseRequestService();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'approve' && $requestId > 0) {
        $result = $service->approveRequest($requestId);
        $message = $result->isSuccess()
            ? 'Заявка №' . $requestId . ' согласована и передана в отдел закупок.'
            : implode('; ', array_map(static fn ($e) => $e->getMessage(), $result->getErrors()));
    }

    if ($action === 'reject' && $requestId > 0) {
        $reason = trim((string)($_POST['reject_reason'] ?? ''));
        $result = $service->rejectRequest($requestId, $reason);
        $message = $result->isSuccess()
            ? 'Заявка №' . $requestId . ' отклонена.'
            : implode('; ', array_map(static fn ($e) => $e->getMessage(), $result->getErrors()));
    }
}

$requests = $service->getPendingApprovalRequests();
$diag = $service->getQueueDiagnostics();
$debug = autoservice_tool_debug()
    ? htmlspecialcharsbx(
        'Модуль otus.autoservice 0.1.0 | NEW=' . (int)$diag['status_new']
            . ' APPROVED=' . (int)$diag['status_approved']
    )
    : null;

autoservice_tool_header(
    'Согласование заявок',
    'approve',
    'Ручные заявки от мастеров. После согласования закупщик принимает товар на склад.'
);

if ($message !== '') {
    autoservice_tool_alert(str_contains($message, 'отклонена') ? 'info' : 'ok', $message);
}

echo '<div class="as-stats">';
echo '<div class="as-stat">На согласовании<strong>' . (int)$diag['pending_manual'] . '</strong></div>';
echo '<div class="as-stat">У закупщика<strong>' . (int)$diag['purchaser_queue'] . '</strong></div>';
echo '</div>';

if ($requests === []) {
    echo '<p style="margin-top:20px;color:#828b95">Нет заявок, ожидающих решения.</p>';
} else {
    echo '<table class="as-table" style="margin-top:16px">';
    echo '<tr><th>№</th><th>Позиция</th><th>Кол-во</th><th>Действия</th></tr>';
    foreach ($requests as $row) {
        echo '<tr>';
        echo '<td>' . (int)$row['ID'] . '</td>';
        echo '<td>' . htmlspecialcharsbx($row['PRODUCT_NAME']) . '</td>';
        echo '<td>' . htmlspecialcharsbx((string)$row['QUANTITY']) . '</td>';
        echo '<td>';
        echo '<form method="post" style="display:inline;">';
        echo bitrix_sessid_post();
        echo '<input type="hidden" name="request_id" value="' . (int)$row['ID'] . '">';
        echo '<input type="hidden" name="action" value="approve">';
        echo '<button type="submit" class="as-btn">Согласовать</button>';
        echo '</form> ';
        echo '<form method="post" style="display:inline;margin-left:8px;">';
        echo bitrix_sessid_post();
        echo '<input type="hidden" name="request_id" value="' . (int)$row['ID'] . '">';
        echo '<input type="hidden" name="action" value="reject">';
        echo '<input name="reject_reason" placeholder="Причина отказа" required class="as-input" style="width:160px">';
        echo ' <button type="submit" class="as-btn as-btn--danger">Отклонить</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

autoservice_tool_footer($debug);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';