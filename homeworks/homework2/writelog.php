<?php

use App\Debug\Log;
use Bitrix\Main\Localization\Loc;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$APPLICATION->SetTitle('Добавление в лог');

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/local/App/Debug/Log.php');

Log::addLog(Loc::getMessage('LOG_PAGE_OPENED'));
?>
    <ul class="list-group">
        <li class="list-group-item">
            <a href="/local/logs/log_custom.log">Файл лога</a>,
            в лог добавлено: <?= htmlspecialcharsbx(Loc::getMessage('LOG_PAGE_OPENED')) ?>
        </li>
    </ul>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
