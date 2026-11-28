<?php

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteId = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', (string)$_REQUEST['site']), 0, 2) : '';
if ($siteId !== '') {
    define('SITE_ID', $siteId);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\Bitrix\Main\Loader::includeModule('crm') || !\CCrmSecurityHelper::IsAuthorized() || !check_bitrix_sessid()) {
    die();
}

global $APPLICATION;

header('Content-Type: text/html; charset=' . LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

$componentData = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : [];
$params = [];
if (isset($componentData['params']) && is_array($componentData['params'])) {
    $params = $componentData['params'];
}

$APPLICATION->IncludeComponent(
    'otus:contact.garage',
    '',
    [
        'CONTACT_ID' => (int)($params['CONTACT_ID'] ?? 0),
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
