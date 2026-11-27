<?php

namespace Otus\Autoservice\Agent;

use Otus\Autoservice\Service\CatalogStockService;

/**
 * Ежедневная синхронизация остатков запчастей.
 */
class StockSyncAgent
{
    public static function run(): string
    {
        if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
            return __METHOD__ . '();';
        }

        $service = new CatalogStockService();
        $service->syncAllCatalogProducts();

        return __METHOD__ . '();';
    }
}
