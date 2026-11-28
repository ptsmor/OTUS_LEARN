<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class otus_autoservice extends CModule
{
    public $MODULE_ID = 'otus.autoservice';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_AUTOSERVICE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_AUTOSERVICE_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('OTUS_AUTOSERVICE_PARTNER_NAME');
        $this->PARTNER_URI = '';
    }

    public function DoInstall(): bool
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        $this->InstallAgents();

        return true;
    }

    public function DoUninstall(): void
    {
        $this->UnInstallAgents();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB(): bool
    {
        Loader::includeModule($this->MODULE_ID);
        $connection = Application::getConnection();
        $entity = \Otus\Autoservice\VehicleTable::getEntity();

        if (!$connection->isTableExists($entity->getDBTableName())) {
            $entity->createDbTable();
        }

        return true;
    }

    public function UnInstallDB(): bool
    {
        Loader::includeModule($this->MODULE_ID);
        $connection = Application::getConnection();
        $entity = \Otus\Autoservice\VehicleTable::getEntity();

        if ($connection->isTableExists($entity->getDBTableName())) {
            $connection->dropTable($entity->getDBTableName());
        }

        return true;
    }

    public function InstallEvents(): void
    {
        $em = EventManager::getInstance();
        $em->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\CrmTabsHandler',
            'onTabsInit'
        );
        $em->registerEventHandler(
            'crm',
            'OnBeforeCrmDealAdd',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\DealGuardHandler',
            'onBeforeDealAdd'
        );
        $em->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\DealVehicleUiHandler',
            'onEpilog'
        );
        $em->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\CrmGlobalMenuHandler',
            'onBuildGlobalMenu'
        );
    }

    public function UnInstallEvents(): void
    {
        $em = EventManager::getInstance();
        $em->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\CrmTabsHandler',
            'onTabsInit'
        );
        $em->unRegisterEventHandler(
            'crm',
            'OnBeforeCrmDealAdd',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\DealGuardHandler',
            'onBeforeDealAdd'
        );
        $em->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\DealVehicleUiHandler',
            'onEpilog'
        );
        $em->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\\Otus\\Autoservice\\Event\\CrmGlobalMenuHandler',
            'onBuildGlobalMenu'
        );
    }

    public function InstallFiles(): bool
    {
        $source = Application::getDocumentRoot() . '/local/modules/' . $this->MODULE_ID . '/install/components';
        if (Directory::isDirectoryExists($source)) {
            CopyDirFiles(
                $source,
                Application::getDocumentRoot() . '/local/components',
                true,
                true
            );
        }

        return true;
    }

    public function UnInstallFiles(): bool
    {
        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/local/components/otus/contact.garage'
        );

        return true;
    }

    public function InstallAgents(): void
    {
        if (class_exists(\CAgent::class)) {
            \CAgent::AddAgent(
                '\\Otus\\Autoservice\\Agent\\StockSyncAgent::run();',
                $this->MODULE_ID,
                'N',
                86400,
                '',
                'Y',
                ConvertTimeStamp(time() + 300, 'FULL')
            );
        }
    }

    public function UnInstallAgents(): void
    {
        if (class_exists(\CAgent::class)) {
            \CAgent::RemoveModuleAgents($this->MODULE_ID);
        }
    }
}
