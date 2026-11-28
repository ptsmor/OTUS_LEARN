<?php

namespace Otus\Autoservice\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Сделки, привязанные к автомобилю (поле UF_VEHICLE_ID у сделки).
 */
class VehicleDealService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDealsByVehicle(int $vehicleId, int $contactId): array
    {
        if ($vehicleId <= 0 || !Loader::includeModule('crm')) {
            return [];
        }

        if (!$this->isDealVehicleFieldAvailable()) {
            return [];
        }

        $deals = [];
        $dbRes = \CCrmDeal::GetListEx(
            ['DATE_CREATE' => 'DESC'],
            [
                'UF_VEHICLE_ID' => $vehicleId,
                'CHECK_PERMISSIONS' => 'Y',
            ],
            false,
            false,
            [
                'ID',
                'TITLE',
                'DATE_CREATE',
                'STAGE_ID',
                'OPPORTUNITY',
                'ASSIGNED_BY_ID',
            ]
        );

        $userNames = [];
        while ($deal = $dbRes->Fetch()) {
            $assignedId = (int)$deal['ASSIGNED_BY_ID'];
            if ($assignedId > 0 && !isset($userNames[$assignedId])) {
                $userNames[$assignedId] = $this->formatUserName($assignedId);
            }

            $deals[] = [
                'ID' => (int)$deal['ID'],
                'TITLE' => (string)$deal['TITLE'],
                'DATE_CREATE' => (string)$deal['DATE_CREATE'],
                'STAGE_ID' => (string)$deal['STAGE_ID'],
                'STAGE_NAME' => $this->resolveStageName((string)$deal['STAGE_ID']),
                'OPPORTUNITY' => $deal['OPPORTUNITY'],
                'ASSIGNED_BY_ID' => $assignedId,
                'ASSIGNED_NAME' => $userNames[$assignedId] ?? '',
                'PARTS' => $this->getDealProductRows((int)$deal['ID']),
            ];
        }

        return $deals;
    }

    private function isDealVehicleFieldAvailable(): bool
    {
        $userField = \CUserTypeEntity::GetList(
            [],
            ['ENTITY_ID' => 'CRM_DEAL', 'FIELD_NAME' => 'UF_VEHICLE_ID']
        )->Fetch();

        return is_array($userField);
    }

    public function hasActiveDealForVehicle(int $vehicleId, ?int $excludeDealId = null): bool
    {
        if ($vehicleId <= 0 || !Loader::includeModule('crm')) {
            return false;
        }

        if (!$this->isDealVehicleFieldAvailable()) {
            return false;
        }

        $filter = [
            'UF_VEHICLE_ID' => $vehicleId,
            'CLOSED' => 'N',
            'CHECK_PERMISSIONS' => 'Y',
        ];
        if ($excludeDealId !== null && $excludeDealId > 0) {
            $filter['!ID'] = $excludeDealId;
        }

        $row = \CCrmDeal::GetListEx(
            ['ID' => 'DESC'],
            $filter,
            false,
            ['nTopCount' => 1],
            ['ID']
        )->Fetch();

        return is_array($row);
    }

    private function resolveStageName(string $stageId): string
    {
        if ($stageId === '') {
            return '';
        }

        $dbRes = \CCrmStatus::GetList(
            ['SORT' => 'ASC'],
            ['STATUS_ID' => $stageId]
        );
        if (is_object($dbRes)) {
            $row = $dbRes->Fetch();
            if (is_array($row) && ($row['NAME'] ?? '') !== '') {
                return (string)$row['NAME'];
            }
        }

        return $stageId;
    }

    private function formatUserName(int $userId): string
    {
        $user = UserTable::getById($userId)->fetch();
        if (!$user) {
            return '';
        }

        return \CUser::FormatName(\CSite::GetNameFormat(), $user, true, false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDealProductRows(int $dealId): array
    {
        if ($dealId <= 0) {
            return [];
        }

        $rows = $this->loadProductRowsFromTable($dealId);
        if ($rows !== []) {
            return $rows;
        }

        if (method_exists(\CCrmDeal::class, 'LoadProductRows')) {
            $products = \CCrmDeal::LoadProductRows($dealId);
            if (is_array($products)) {
                foreach ($products as $product) {
                    if (is_array($product)) {
                        $rows[] = $this->mapProductRow($product);
                    }
                }
            }
            if ($rows !== []) {
                return $rows;
            }
        }

        try {
            $products = \CCrmProductRow::LoadRows(\CCrmOwnerType::Deal, $dealId, true);
            foreach ($products as $product) {
                if (is_array($product)) {
                    $rows[] = $this->mapProductRow($product);
                }
            }
        } catch (\Throwable $exception) {
            return [];
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $product
     * @return array<string, mixed>
     */
    private function mapProductRow(array $product): array
    {
        $name = (string)($product['PRODUCT_NAME'] ?? $product['ORIGINAL_PRODUCT_NAME'] ?? '');
        if ($name === '' && !empty($product['PRODUCT_ID'])) {
            $name = $this->resolveCatalogProductName((int)$product['PRODUCT_ID']);
        }

        return [
            'NAME' => $name,
            'QUANTITY' => (float)($product['QUANTITY'] ?? 0),
            'PRICE' => (float)($product['PRICE'] ?? $product['PRICE_ACCOUNT'] ?? 0),
        ];
    }

    private function loadProductRowsFromTable(int $dealId): array
    {
        if (!class_exists(\Bitrix\Crm\ProductRowTable::class)) {
            return [];
        }

        $rows = [];
        $ownerTypes = ['D', 2, 'DEAL'];
        foreach ($ownerTypes as $ownerType) {
            $res = \Bitrix\Crm\ProductRowTable::getList([
                'filter' => [
                    '=OWNER_ID' => $dealId,
                    '=OWNER_TYPE' => $ownerType,
                ],
                'select' => ['PRODUCT_ID', 'PRODUCT_NAME', 'QUANTITY', 'PRICE', 'PRICE_ACCOUNT'],
            ]);
            while ($product = $res->fetch()) {
                $rows[] = $this->mapProductRow($product);
            }
            if ($rows !== []) {
                break;
            }
        }

        return $rows;
    }

    private function resolveCatalogProductName(int $productId): string
    {
        if ($productId <= 0 || !Loader::includeModule('iblock')) {
            return 'Товар #' . $productId;
        }

        $element = \CIBlockElement::GetByID($productId)->Fetch();

        return is_array($element) ? (string)$element['NAME'] : ('Товар #' . $productId);
    }
}