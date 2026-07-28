<?

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("ДЗ #2: Отладка и логирование");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

?>
    <h1 class="mb-3"><? $APPLICATION->ShowTitle() ?></h1>

    <h4 class="mb-3">Пояснительная записка</h4>
    <div class="mb-3">
        <p>Часть 1: при обращении к <code>writelog.php</code> в файл <code>local/logs/log_custom.log</code>
            записываются текущие дата и время через метод <code>App\Debug\Log::addLog()</code>.</p>
        <p>Часть 2: класс <code>App\Debug\Log</code> подключен как системный логгер в
            <code>local/.settings_extra.php</code> и переопределяет метод <code>write()</code>,
            добавляя префикс <strong>OTUS</strong> перед записью в файл <code>local/logs/exceptions.log</code>.</p>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-success text-white">
            Файлы проекта: Часть 1 - Logger
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item list-group-item-action">
                <a href="/local/logs/log_custom.log"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    local/logs/log_custom.log
                </span>
                    <span class="badge bg-success">
                    Файл лога из п1 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/homeworks/homework2/writelog.php"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    writelog.php
                </span>
                    <span class="badge bg-secondary">
                    Добавление в лог из п1 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/homeworks/homework2/clearlog.php"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    clearlog.php
                </span>
                    <span class="badge bg-warning">
                    Очистить лог из п1 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Debug/Log.php&lang=ru"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    Файл с классом кастомного логгера
                </span>
                    <span class="badge bg-primary">
                    класс логгера в админке
                </span>
                </a>
            </li>

        </ul>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-success text-white">
            Файлы проекта: Часть 2 - Exception
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item list-group-item-action">
                <a href="/local/logs/exceptions.log"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    local/logs/exceptions.log
                </span>
                    <span class="badge bg-primary">
                    Файл лога из п2 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/homeworks/homework2/writeexception.php"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    writeexception.php
                </span>
                    <span class="badge bg-success">
                    Добавление в лог из п2 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/homeworks/homework2/clearexception.php"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    clearexception.php
                </span>
                    <span class="badge bg-secondary">
                    Очистить лог из п2 ДЗ
                </span>
                </a>
            </li>
            <li class="list-group-item list-group-item-action">
                <a href="/bitrix/admin/fileman_file_view.php?path=/local/.settings_extra.php&lang=ru"
                   class="d-flex justify-content-between align-items-center">
                <span>
                    Файл с настройками системного логгера
                </span>
                    <span class="badge bg-warning">
                    local/.settings_extra.php
                </span>
                </a>
            </li>
        </ul>
    </div>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
