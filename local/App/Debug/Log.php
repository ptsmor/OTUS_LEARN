<?php

namespace App\Debug;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Localization\Loc;

/**
 * Пользовательский системный логгер с префиксом OTUS перед записью.
 */
class Log extends FileExceptionHandlerLog
{
    /**
     * Записывает сообщение в пользовательский лог-файл.
     *
     * @param string $message Текст сообщения
     * @param bool $clear Очистить файл перед записью
     * @param string $fileName Имя файла без расширения
     *
     * @return void
     */
    public static function addLog(string $message = '', bool $clear = false, string $fileName = 'log_custom'): void
    {
        Loc::loadMessages(__FILE__);

        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/' . $fileName . '.log';
        $logEntry = date('d.m.Y H:i:s') . "\n";

        if ($message !== '')
        {
            $logEntry .= $message . "\n";
        }

        $logEntry .= "---\n";

        $directory = dirname($logFile);

        if (!is_dir($directory))
        {
            mkdir($directory, 0755, true);
        }

        file_put_contents($logFile, $logEntry, $clear ? 0 : FILE_APPEND);
    }

    /**
     * Очищает пользовательский лог-файл.
     *
     * @param string $fileName Имя файла без расширения
     *
     * @return void
     */
    public static function cleanLog(string $fileName = 'log_custom'): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/' . $fileName . '.log';
        file_put_contents($logFile, '');
    }

    /**
     * Очищает файл системного лога исключений.
     *
     * @return void
     */
    public static function cleanExceptionLog(): void
    {
        $logFile = fopen($_SERVER['DOCUMENT_ROOT'] . '/local/logs/exceptions.log', 'w');
        fclose($logFile);
    }

    /**
     * Записывает исключение в системный лог с префиксом OTUS перед записью.
     *
     * @param \Throwable $exception Исключение
     * @param int $logType Тип записи лога
     *
     * @return void
     */
    public function write($exception, $logType): void
    {
        $text = ExceptionHandlerFormatter::format($exception);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);
        $message = 'OTUS - {date} - Host: {host} - {type} - ' . $text . "\n";

        $this->logger->log($logLevel, $message, $context);
    }
}
