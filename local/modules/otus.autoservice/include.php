<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('otus.autoservice', [
    'Otus\\Autoservice\\VehicleTable' => 'lib/VehicleTable.php',
    'Otus\\Autoservice\\Service\\GarageService' => 'lib/Service/GarageService.php',
    'Otus\\Autoservice\\Service\\VehicleDealService' => 'lib/Service/VehicleDealService.php',
    'Otus\\Autoservice\\Service\\CatalogStockService' => 'lib/Service/CatalogStockService.php',
    'Otus\\Autoservice\\Service\\PurchaseRequestService' => 'lib/Service/PurchaseRequestService.php',
    'Otus\\Autoservice\\Install\\ServiceDealCategoryInstaller' => 'lib/Install/ServiceDealCategoryInstaller.php',
    'Otus\\Autoservice\\Service\\HighloadDirectory' => 'lib/Service/HighloadDirectory.php',
    'Otus\\Autoservice\\Event\\CrmTabsHandler' => 'lib/Event/CrmTabsHandler.php',
    'Otus\\Autoservice\\Event\\CrmGlobalMenuHandler' => 'lib/Event/CrmGlobalMenuHandler.php',
    'Otus\\Autoservice\\Event\\DealGuardHandler' => 'lib/Event/DealGuardHandler.php',
    'Otus\\Autoservice\\Event\\DealVehicleUiHandler' => 'lib/Event/DealVehicleUiHandler.php',
    'Otus\\Autoservice\\Controller\\DealGarage' => 'lib/Controller/DealGarage.php',
    'Otus\\Autoservice\\Agent\\StockSyncAgent' => 'lib/Agent/StockSyncAgent.php',
]);
