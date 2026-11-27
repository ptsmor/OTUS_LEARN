<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Service\GarageService;
use Otus\Autoservice\Service\HighloadDirectory;
use Otus\Autoservice\Service\VehicleDealService;

Loc::loadMessages(__FILE__);

class OtusContactGarageComponent extends CBitrixComponent implements Controllerable
{
    public function configureActions(): array
    {
        return [
            'addVehicle' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
            'getVehicleDeals' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function onPrepareComponentParams($arParams): array
    {
        $arParams['CONTACT_ID'] = (int)($arParams['CONTACT_ID'] ?? 0);

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!\Bitrix\Main\Loader::includeModule('otus.autoservice') || !\Bitrix\Main\Loader::includeModule('crm')) {
            ShowError(Loc::getMessage('OTUS_AUTO_GARAGE_MODULE_REQUIRED'));

            return;
        }

        $contactId = (int)$this->arParams['CONTACT_ID'];
        $garage = new GarageService();

        if (!$garage->canViewGarage($contactId)) {
            ShowError(Loc::getMessage('OTUS_AUTO_GARAGE_ACCESS_DENIED'));

            return;
        }

        $this->arResult['CONTACT_ID'] = $contactId;
        $this->arResult['CAN_EDIT'] = $garage->canEditGarage($contactId);
        $this->arResult['VEHICLES'] = $garage->getVehiclesByContact($contactId);
        $this->arResult['BRANDS'] = HighloadDirectory::getBrandList();
        $this->arResult['MODELS_BY_BRAND'] = HighloadDirectory::getModelsGroupedByBrand();
        $this->arResult['DIRECTORY_READY'] = HighloadDirectory::isReady();

        $this->includeComponentTemplate();
    }

    protected function listKeysSignedParameters(): array
    {
        return ['CONTACT_ID'];
    }

    public function addVehicleAction(int $contactId, int $brandId, int $modelId, string $plateNumber, int $year = 0, string $color = '', int $mileage = 0): array
    {
        if (
            !\Bitrix\Main\Loader::includeModule('otus.autoservice')
            || !\Bitrix\Main\Loader::includeModule('crm')
            || !\Bitrix\Main\Loader::includeModule('highloadblock')
        ) {
            return [
                'success' => false,
                'errors' => [Loc::getMessage('OTUS_AUTO_GARAGE_MODULE_REQUIRED')],
            ];
        }

        $garage = new GarageService();
        $result = $garage->addVehicle($contactId, [
            'BRAND_ID' => $brandId,
            'MODEL_ID' => $modelId,
            'PLATE_NUMBER' => trim($plateNumber),
            'YEAR' => $year,
            'COLOR' => trim($color),
            'MILEAGE' => max(0, $mileage),
        ]);

        if (!$result->isSuccess()) {
            return [
                'success' => false,
                'errors' => array_map(static fn ($e) => $e->getMessage(), $result->getErrors()),
            ];
        }

        return [
            'success' => true,
            'id' => $result->getData()['ID'],
            'vehicles' => $garage->getVehiclesByContact($contactId),
        ];
    }

    public function getVehicleDealsAction(int $vehicleId, int $contactId): array
    {
        if (
            !\Bitrix\Main\Loader::includeModule('otus.autoservice')
            || !\Bitrix\Main\Loader::includeModule('crm')
        ) {
            return ['success' => false, 'errors' => [Loc::getMessage('OTUS_AUTO_GARAGE_MODULE_REQUIRED')]];
        }

        $garage = new GarageService();
        if (!$garage->canViewGarage($contactId)) {
            return ['success' => false, 'errors' => [Loc::getMessage('OTUS_AUTO_GARAGE_ACCESS_DENIED')]];
        }

        $dealService = new VehicleDealService();

        return [
            'success' => true,
            'deals' => $dealService->getDealsByVehicle($vehicleId, $contactId),
        ];
    }
}
