<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/App/autoload.php';

Loc::loadMessages(__FILE__);

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

/**
 * Возвращает текст сообщения об ошибке для отображения на странице.
 *
 * @param \Throwable $exception Исключение
 *
 * @return string
 */
function getPageErrorMessage(\Throwable $exception): string
{
    $message = str_replace(['<br>', '<br/>', '<br />'], ' ', $exception->getMessage());
    $message = trim(strip_tags($message));
    $message = preg_replace('/\s+/u', ' ', $message) ?? $message;

    return htmlspecialcharsbx($message);
}

/**
 * Возвращает текст сообщения об успехе.
 *
 * @param string $messageCode Код языковой фразы
 *
 * @return string
 */
function getPageSuccessMessage(string $messageCode): string
{
    return htmlspecialcharsbx(Loc::getMessage($messageCode));
}
