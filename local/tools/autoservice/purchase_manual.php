<?php

/**
 * Ручная заявка на закупку (менеджер / механик).
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Доступ только администратору (на демо — роль менеджера / механика)');
}

if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
    die('Установите модуль otus.autoservice');
}

require __DIR__ . '/include/layout.php';

$service = new \Otus\Autoservice\Service\PurchaseRequestService();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (float)str_replace(',', '.', (string)($_POST['quantity'] ?? '10'));

    $result = $service->createManualRequest($productId, $quantity);
    if ($result->isSuccess()) {
        $message = 'Заявка №' . (int)$result->getData()['ID']
            . ' отправлена на согласование руководителю закупок.';
    } else {
        $errors = array_map(static fn ($e) => $e->getMessage(), $result->getErrors());
    }
}

$products = $service->listCatalogProductsForSelect();
$debug = autoservice_tool_debug() ? 'Модуль otus.autoservice 0.1.0' : null;

autoservice_tool_header(
    'Новая заявка на закупку',
    'manual',
    'Заказ позиции из каталога (услуга или запчасть). Заявка сначала на согласование, после одобрения — закупщику.'
);

if ($message !== '') {
    autoservice_tool_alert('ok', $message);
}
if ($errors !== []) {
    autoservice_tool_alert('err', implode('; ', $errors));
}

echo '<form method="post" class="as-form">';
echo bitrix_sessid_post();
echo '<label>Позиция каталога<br><select name="product_id" required class="as-input">';
echo '<option value="">— выберите из каталога —</option>';
foreach ($products as $p) {
    $label = sprintf(
        '%s · на складе %s шт.',
        $p['NAME'],
        rtrim(rtrim(sprintf('%.2f', $p['QUANTITY']), '0'), '.')
    );
    echo '<option value="' . (int)$p['ID'] . '">' . htmlspecialcharsbx($label) . '</option>';
}
echo '</select></label>';
echo '<label>Количество для закупки<br><input type="text" name="quantity" value="10" class="as-input" size="8"></label>';
echo '<p><button type="submit" class="as-btn">Отправить на согласование</button></p>';
echo '</form>';

autoservice_tool_footer($debug);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';