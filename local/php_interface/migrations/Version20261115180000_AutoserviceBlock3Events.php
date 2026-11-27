<?php

namespace Sprint\Migration;

/**
 * Блок 3: OnEpilog — UI выбора авто в сделке.
 */
class Version20261115180000_AutoserviceBlock3Events extends Version
{
    protected $author = 'admin';

    protected $description = 'Автосервис: событие OnEpilog для UF_VEHICLE_ID в сделке';

    protected $moduleVersion = '5.16.0';

    public function up(): void
    {
        if (!\Bitrix\Main\Loader::includeModule('main')) {
            return;
        }

        $em = \Bitrix\Main\EventManager::getInstance();
        $em->registerEventHandler(
            'main',
            'OnEpilog',
            'otus.autoservice',
            '\\Otus\\Autoservice\\Event\\DealVehicleUiHandler',
            'onEpilog'
        );
    }

    public function down(): void
    {
        if (!\Bitrix\Main\Loader::includeModule('main')) {
            return;
        }

        $em = \Bitrix\Main\EventManager::getInstance();
        $em->unRegisterEventHandler(
            'main',
            'OnEpilog',
            'otus.autoservice',
            '\\Otus\\Autoservice\\Event\\DealVehicleUiHandler',
            'onEpilog'
        );
    }
}