<?php

namespace Otus\Autoservice\Service;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

/**
 * Синхронизация остатков запчастей с внешним random.org (ТЗ).
 */
class CatalogStockService
{
    public const BUILD_ID = 'OTUS_STOCK_SYNC_V3_20261115_1745';

    private const OPTION_LOG = 'stock_sync_log';

    /** @var list<string> */
    private array $lastRunReport = [];

    public function syncAllCatalogProducts(): int
    {
        $this->lastRunReport = [];
        $this->lastRunReport[] = '=== Обновление остатков склада ===';

        if (!Loader::includeModule('catalog') || !Loader::includeModule('crm')) {
            $this->lastRunReport[] = 'ОШИБКА: не подключены catalog или crm';

            return 0;
        }

        $updated = 0;
        $products = ProductTable::getList([
            'select' => ['ID', 'QUANTITY'],
        ]);

        while ($product = $products->fetch()) {
            $productId = (int)$product['ID'];
            $quantity = $this->fetchRemoteQuantity($productId);
            if ($quantity === null) {
                $this->lastRunReport[] = "Товар #{$productId}: пропуск (ошибка random.org)";

                continue;
            }

            $oldQty = (float)$product['QUANTITY'];
            ProductTable::update($productId, ['QUANTITY' => $quantity]);
            $updated++;

            $line = sprintf('Товар #%d: %s → %s', $productId, $this->formatQty($oldQty), $this->formatQty($quantity));
            $this->appendLog($line);
            $this->lastRunReport[] = $line;

            if ($this->isZeroQuantity($quantity)) {
                $this->lastRunReport[] = $this->onOutOfStock($productId);
            }
        }

        $summary = sprintf('Обновлено позиций: %d', $updated);
        $this->appendLog($summary);
        $this->lastRunReport[] = $summary;

        return $updated;
    }

    /**
     * @return list<string>
     */
    public function getLastRunReport(): array
    {
        return $this->lastRunReport;
    }

    /**
     * @return array<string, string>
     */
    public function getBuildDiagnostics(): array
    {
        $stockFile = (new \ReflectionClass(self::class))->getFileName() ?: '—';
        $purchaseFile = class_exists(PurchaseRequestService::class)
            ? (new \ReflectionClass(PurchaseRequestService::class))->getFileName()
            : 'КЛАСС PurchaseRequestService НЕ НАЙДЕН';

        return [
            'BUILD_ID' => self::BUILD_ID,
            'CatalogStockService.php' => $stockFile,
            'PurchaseRequestService.php' => $purchaseFile,
            'PurchaseRequestService::BUILD' => class_exists(PurchaseRequestService::class)
                ? PurchaseRequestService::BUILD_ID
                : '—',
        ];
    }

    public function fetchRemoteQuantity(int $productId): ?float
    {
        $client = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
        $response = $client->get(
            'https://www.random.org/integers/?num=1&min=0&max=10&col=1&base=10&format=plain&rnd=new'
        );
        if ($response === false) {
            $this->appendLog('Ошибка запроса random.org для товара #' . $productId);

            return null;
        }

        $value = (int)trim($response);

        return (float)max(0, min(10, $value));
    }

    protected function onOutOfStock(int $productId): string
    {
        $this->appendLog('Нулевой остаток у товара #' . $productId . ' — создаём заявку на закупку…');

        if (!class_exists(PurchaseRequestService::class)) {
            $msg = 'ЗАЯВКА: класс PurchaseRequestService не найден (проверьте include.php)';
            $this->appendLog($msg);

            return $msg;
        }

        $purchase = new PurchaseRequestService();
        $result = $purchase->createAutoRequest($productId, 10);
        if ($result->isSuccess()) {
            $msg = 'ЗАЯВКА: создана #' . (int)$result->getData()['ID'] . ' для товара #' . $productId;
            $this->appendLog('Нулевой остаток у товара #' . $productId . ' — ' . $msg);

            return $msg;
        }

        $errors = array_map(static fn ($e) => $e->getMessage(), $result->getErrors());
        $msg = 'ЗАЯВКА: НЕ создана для #' . $productId . ' — ' . implode('; ', $errors);
        $this->appendLog('Нулевой остаток у товара #' . $productId . '. ' . $msg);

        return $msg;
    }

    public function appendLog(string $message): void
    {
        $prev = Option::get('otus.autoservice', self::OPTION_LOG, '');
        $line = date('Y-m-d H:i:s') . ' ' . $message;
        Option::set('otus.autoservice', self::OPTION_LOG, $prev . $line . PHP_EOL);
    }

    public function getLog(): string
    {
        return (string)Option::get('otus.autoservice', self::OPTION_LOG, '');
    }

    public function getModuleBuildMark(): string
    {
        return self::BUILD_ID;
    }

    private function formatQty(float $qty): string
    {
        if (abs($qty - round($qty)) < 0.0001) {
            return (string)(int)round($qty);
        }

        return rtrim(rtrim(sprintf('%.4f', $qty), '0'), '.');
    }

    private function isZeroQuantity(float $quantity): bool
    {
        return abs($quantity) < 0.0001;
    }
}