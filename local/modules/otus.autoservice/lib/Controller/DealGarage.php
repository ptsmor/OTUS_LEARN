<?php

namespace Otus\Autoservice\Controller;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Otus\Autoservice\Service\GarageService;

/**
 * AJAX: автомобили контакта для поля сделки UF_VEHICLE_ID.
 */
class DealGarage extends Controller
{
    protected function getDefaultPreFilters(): array
    {
        return [
            new ActionFilter\Authentication(),
            new ActionFilter\Csrf(),
        ];
    }

    public function listVehiclesForContactAction(int $contactId): array
    {
        if ($contactId <= 0) {
            return ['success' => false, 'errors' => ['Не указан контакт']];
        }

        if (!Loader::includeModule('otus.autoservice') || !Loader::includeModule('crm')) {
            return ['success' => false, 'errors' => ['Модуль otus.autoservice или crm не подключён']];
        }

        $garage = new GarageService();
        if (!$garage->canViewGarage($contactId)) {
            return ['success' => false, 'errors' => ['Нет прав на просмотр гаража контакта']];
        }

        $vehicles = [];
        foreach ($garage->getVehiclesByContact($contactId) as $vehicle) {
            $title = trim((string)($vehicle['TITLE'] ?? ''));
            $plate = trim((string)($vehicle['PLATE_NUMBER'] ?? ''));
            $label = $title;
            if ($plate !== '') {
                $label .= $label !== '' ? ' — ' . $plate : $plate;
            }
            $label .= ' [ID ' . (int)$vehicle['ID'] . ']';

            $vehicles[] = [
                'ID' => (int)$vehicle['ID'],
                'LABEL' => $label,
                'PLATE' => $plate,
            ];
        }

        return ['success' => true, 'vehicles' => $vehicles];
    }

    /**
     * Контакт, привязанный к сохранённой сделке (когда в карточке нет ссылки в DOM).
     */
    public function resolveDealContactAction(int $dealId): array
    {
        if ($dealId <= 0) {
            return ['success' => false, 'contactId' => 0, 'errors' => ['Сделка ещё не сохранена']];
        }

        if (!Loader::includeModule('crm')) {
            return ['success' => false, 'contactId' => 0, 'errors' => ['Модуль crm не подключён']];
        }

        $contactId = 0;
        if (class_exists(\Bitrix\Crm\Binding\DealContactTable::class)) {
            $ids = \Bitrix\Crm\Binding\DealContactTable::getDealContactIDs($dealId);
            if (is_array($ids) && $ids !== []) {
                $contactId = (int)$ids[0];
            }
        }

        if ($contactId <= 0) {
            $deal = \CCrmDeal::GetByID($dealId, false);
            if (is_array($deal)) {
                $contactId = (int)($deal['CONTACT_ID'] ?? 0);
            }
        }

        if ($contactId <= 0) {
            return ['success' => false, 'contactId' => 0, 'errors' => ['У сделки нет контакта']];
        }

        if (Loader::includeModule('otus.autoservice')) {
            $garage = new GarageService();
            if (!$garage->canViewGarage($contactId)) {
                return ['success' => false, 'contactId' => $contactId, 'errors' => ['Нет прав на контакт']];
            }
        }

        return ['success' => true, 'contactId' => $contactId];
    }
}