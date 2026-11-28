<?php

/**
 * Заявки к исполнению (закупщик).
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Доступ только администратору (на демо — закупщик)');
}

if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
    die('Установите модуль otus.autoservice');
}

require __DIR__ . '/include/layout.php';

$hlExists = \Bitrix\Main\Loader::includeModule('highloadblock')
    && (bool)\Bitrix\Highloadblock\HighloadBlockTable::getList([
        'filter' => ['=NAME' => 'OtusPurchaseRequest'],
        'limit' => 1,
    ])->fetch();

$service = new \Otus\Autoservice\Service\PurchaseRequestService();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'complete' && $requestId > 0) {
        $result = $service->completeRequest($requestId);
        $message = $result->isSuccess()
            ? 'Заявка №' . $requestId . ' выполнена — остаток на складе обновлён.'
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

$requests = $service->getOpenRequests();
$diag = $service->getQueueDiagnostics();
$debug = autoservice_tool_debug() ? htmlspecialcharsbx('Модуль otus.autoservice 0.1.0') : null;

autoservice_tool_header(
    'Заявки к исполнению',
    'requests',
    'Согласованные и автоматические заявки. «Принять на склад» увеличивает остаток позиции в каталоге (услуга или запчасть).'
);

if (!$hlExists) {
    autoservice_tool_alert('err', 'Справочник заявок не найден. Выполните миграцию OtusPurchaseRequest.');
}

if ($message !== '') {
    $type = str_contains($message, 'отклонена') ? 'info' : 'ok';
    autoservice_tool_alert($type, $message);
}

echo '<div class="as-stats">';
echo '<div class="as-stat">К исполнению<strong>' . (int)$diag['purchaser_queue'] . '</strong></div>';
echo '<div class="as-stat">На согласовании<strong>' . (int)$diag['pending_manual'] . '</strong></div>';
echo '</div>';

if ($requests === []) {
    echo '<p style="margin-top:20px;color:#828b95">Нет открытых заявок.</p>';
    if (autoservice_tool_debug()) {
        echo '<p class="as-lead">Тест: <a href="stock_sync.php?debug=1&amp;force_request=23">force_request=23</a></p>';
    } else {
        echo '<p class="as-lead">Автозаявка появится после обновления остатков, когда по позиции на складе станет 0 единиц.</p>';
    }
} else {
    echo '<table class="as-table" style="margin-top:16px">';
    echo '<tr><th>№</th><th>Позиция</th><th>Кол-во</th><th>Тип</th><th>Действия</th></tr>';
    foreach ($requests as $row) {
        echo '<tr>';
        echo '<td>' . (int)$row['ID'] . '</td>';
        echo '<td>' . htmlspecialcharsbx($row['PRODUCT_NAME']) . '</td>';
        echo '<td>' . htmlspecialcharsbx((string)$row['QUANTITY']) . '</td>';
        echo '<td><span class="as-badge' . ($row['IS_AUTO'] ? ' as-badge--auto' : '') . '">'
            . ($row['IS_AUTO'] ? 'автоматическая' : 'ручная') . '</span></td>';
        echo '<td>';
        echo '<form method="post" style="display:inline;">';
        echo bitrix_sessid_post();
        echo '<input type="hidden" name="request_id" value="' . (int)$row['ID'] . '">';
        echo '<input type="hidden" name="action" value="complete">';
        echo '<button type="submit" class="as-btn">Принять на склад</button>';
        echo '</form> ';
        echo '<form method="post" style="display:inline;margin-left:8px;">';
        echo bitrix_sessid_post();
        echo '<input type="hidden" name="request_id" value="' . (int)$row['ID'] . '">';
        echo '<input type="hidden" name="action" value="reject">';
        echo '<input name="reject_reason" placeholder="Причина" required class="as-input" style="width:130px">';
        echo ' <button type="submit" class="as-btn as-btn--danger">Отклонить</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

autoservice_tool_footer($debug);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';