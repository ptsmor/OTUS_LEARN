<?php

use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Ошибка для exception');

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/local/App/Debug/Log.php');
?>
<ul class="list-group">
    <li class="list-group-item">
        <a href="/local/logs/exceptions.log">Файл лога</a>
    </li>
</ul>
<?php

throw new RuntimeException(Loc::getMessage('LOG_EXCEPTION_MESSAGE'));
