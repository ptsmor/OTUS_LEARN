<?php

namespace Otus\Autoservice\Event;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Service\VehicleDealService;

/**
 * Запрет второй активной сделки по одному автомобилю.
 */
class DealGuardHandler
{
    private const FALLBACK_MESSAGE = 'По этому автомобилю уже есть незакрытая сделка. Завершите текущий ремонт перед созданием новой.';

    /**
     * @param array<string, mixed> $fields
     */
    public static function onBeforeDealAdd(array &$fields): bool
    {
        if (!Loader::includeModule('otus.autoservice') || !Loader::includeModule('crm')) {
            return true;
        }

        Loc::loadMessages(__FILE__);

        $vehicleId = (int)($fields['UF_VEHICLE_ID'] ?? 0);
        if ($vehicleId <= 0) {
            return true;
        }

        $dealService = new VehicleDealService();
        if (!$dealService->hasActiveDealForVehicle($vehicleId)) {
            return true;
        }

        $message = Loc::getMessage('OTUS_AUTO_DEAL_ACTIVE_EXISTS');
        if ($message === '' || $message === null) {
            $message = self::FALLBACK_MESSAGE;
        }

        // Для слайдера CRM: без RESULT_MESSAGE Битрикс показывает имя обработчика.
        $fields['RESULT_MESSAGE'] = $message;

        global $APPLICATION;
        if (is_object($APPLICATION)) {
            $APPLICATION->ThrowException($message);
        }

        return false;
    }
}