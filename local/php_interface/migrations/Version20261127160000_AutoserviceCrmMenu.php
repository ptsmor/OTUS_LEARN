<?php

namespace Sprint\Migration;

/**
 * Пункт CRM «Склад и закупки» → local/tools/autoservice.
 */
class Version20261127160000_AutoserviceCrmMenu extends Version
{
    protected $author = 'admin';

    protected $description = 'Автосервис: меню CRM OnBuildGlobalMenu';

    protected $moduleVersion = '5.16.0';

    public function up(): void
    {
        if (!\Bitrix\Main\Loader::includeModule('main')) {
            return;
        }

        $em = \Bitrix\Main\EventManager::getInstance();
        $em->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            'otus.autoservice',
            '\\Otus\\Autoservice\\Event\\CrmGlobalMenuHandler',
            'onBuildGlobalMenu'
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
            'OnBuildGlobalMenu',
            'otus.autoservice',
            '\\Otus\\Autoservice\\Event\\CrmGlobalMenuHandler',
            'onBuildGlobalMenu'
        );
    }
}