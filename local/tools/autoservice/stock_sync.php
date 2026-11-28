<?php

/**
 * Синхронизация остатков запчастей с внешнего склада (random.org по ТЗ).
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Доступ только администратору');
}

if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
    die('Установите модуль otus.autoservice');
}

require __DIR__ . '/include/layout.php';

$service = new \Otus\Autoservice\Service\CatalogStockService();
$debug = autoservice_tool_debug();
$diag = $debug ? $service->getBuildDiagnostics() : [];

$purchaseReady = class_exists(\Otus\Autoservice\Service\PurchaseRequestService::class)
    && \Bitrix\Main\Loader::includeModule('highloadblock')
    && (bool)\Bitrix\Highloadblock\HighloadBlockTable::getList([
        'filter' => ['=NAME' => 'OtusPurchaseRequest'],
        'limit' => 1,
    ])->fetch();

$forceProductId = $debug ? (int)($_GET['force_request'] ?? 0) : 0;
$forceMessage = '';
if ($forceProductId > 0) {
    if (!$purchaseReady) {
        $forceMessage = 'HL OtusPurchaseRequest не найден';
    } else {
        $purchase = new \Otus\Autoservice\Service\PurchaseRequestService();
        $forceResult = $purchase->createAutoRequest($forceProductId, 10);
        if ($forceResult->isSuccess()) {
            $forceMessage = 'OK: заявка #' . (int)$forceResult->getData()['ID'] . ' для товара #' . $forceProductId;
        } else {
            $forceMessage = 'Ошибка: ' . implode('; ', array_map(static fn ($e) => $e->getMessage(), $forceResult->getErrors()));
        }
        $service->appendLog('Тест force_request: ' . $forceMessage);
    }
}

$count = $service->syncAllCatalogProducts();
$runReport = $service->getLastRunReport();

$openCount = 0;
$pendingManual = 0;
if ($purchaseReady) {
    $purchaseSvc = new \Otus\Autoservice\Service\PurchaseRequestService();
    $openCount = count($purchaseSvc->getOpenRequests());
    $pendingManual = count($purchaseSvc->getPendingApprovalRequests());
}

$debugHtml = null;
if ($debug) {
    $lines = ['diag: ' . json_encode($diag, JSON_UNESCAPED_UNICODE)];
    if (class_exists(\Otus\Autoservice\Service\PurchaseRequestService::class)) {
        $lines[] = 'PurchaseRequestService: ' . \Otus\Autoservice\Service\PurchaseRequestService::BUILD_ID;
    }
    if ($forceMessage !== '') {
        $lines[] = 'force_request: ' . $forceMessage;
    }
    $logLines = array_filter(explode("\n", trim($service->getLog())));
    $lines[] = 'log tail: ' . implode(' | ', array_slice($logLines, -5));
    $debugHtml = htmlspecialcharsbx(implode("\n", $lines));
}

autoservice_tool_header(
    'Остатки на складе',
    'stock',
    'По регламенту данные приходят с внешнего склада поставщика. При нулевом остатке система создаёт заявку на закупку 10 единиц.'
);

autoservice_tool_alert('ok', 'Обновлено позиций: ' . (int)$count . '. ' . date('d.m.Y H:i'));

echo '<div class="as-stats">';
echo '<div class="as-stat">Позиций в каталоге<strong>' . (int)$count . '</strong></div>';
echo '<div class="as-stat">К закупщику<strong>' . (int)$openCount . '</strong></div>';
echo '<div class="as-stat">На согласовании<strong>' . (int)$pendingManual . '</strong></div>';
echo '</div>';

echo '<p style="margin-top:12px"><a href="stock_sync.php' . ($debug ? '?debug=1' : '') . '" class="as-btn">Обновить данные склада</a></p>';

$catalogNames = [];
if ($purchaseReady) {
    foreach ((new \Otus\Autoservice\Service\PurchaseRequestService())->listCatalogProductsForSelect() as $p) {
        $catalogNames[(int)$p['ID']] = $p['NAME'];
    }
}

echo '<h2 style="font-size:16px;margin:20px 0 10px">Отчёт обновления</h2>';
echo '<table class="as-table"><tr><th>Позиция каталога</th><th>Было → стало</th></tr>';
foreach ($runReport as $line) {
    if (preg_match('/^===/u', $line) && !$debug) {
        continue;
    }
    if (!preg_match('/Товар #(\d+):\s*(.+)/u', $line, $m)) {
        continue;
    }
    $pid = (int)$m[1];
    $delta = $m[2];
    $productName = $catalogNames[$pid] ?? ('Позиция #' . $pid);
    $hl = strpos($line, 'ЗАЯВКА') !== false || strpos($line, 'заявк') !== false;
    echo '<tr' . ($hl ? ' style="background:#e3f2fd"' : '') . '>';
    echo '<td>' . htmlspecialcharsbx($productName) . '</td>';
    echo '<td>' . htmlspecialcharsbx($delta);
    if (preg_match('/→\s*0\s*$/', $delta)) {
        echo ' · <strong>создана автозаявка</strong>';
    }
    echo '</td></tr>';
}
echo '</table>';

if ($debug) {
    echo '<h2 style="font-size:14px;margin-top:20px">Полный отчёт (debug)</h2>';
    echo '<label style="display:block;white-space:pre-wrap;font-family:monospace;font-size:12px">'
        . htmlspecialcharsbx(implode("\n", $runReport)) . '</label>';
    echo '<p><a href="?debug=1&amp;force_request=23">Тест: автозаявка для #23</a></p>';
}

autoservice_tool_footer($debugHtml);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';