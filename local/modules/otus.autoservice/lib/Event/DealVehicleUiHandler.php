<?php

namespace Otus\Autoservice\Event;

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;

/**
 * Подсказка выбора авто из гаража на карточке сделки (UF_VEHICLE_ID).
 */
class DealVehicleUiHandler
{
    public static function onEpilog(): void
    {
        if (!Loader::includeModule('otus.autoservice') || !Loader::includeModule('crm')) {
            return;
        }

        $request = Application::getInstance()->getContext()->getRequest();
        $uri = (string)$request->getRequestedPage();
        if (stripos($uri, '/crm/deal/details') === false) {
            return;
        }

        $docRoot = Application::getDocumentRoot();
        $jsPath = '/local/modules/otus.autoservice/install/js/deal_vehicle_bind.js';
        if (!is_file($docRoot . $jsPath)) {
            return;
        }

        Asset::getInstance()->addJs($jsPath);
        \CJSCore::Init(['ajax']);
    }
}