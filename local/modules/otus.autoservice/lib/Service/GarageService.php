<?php

namespace Otus\Autoservice\Service;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Otus\Autoservice\VehicleTable;

Loc::loadMessages(__FILE__);

/**
 * Операции с гаражом контакта.
 */
class GarageService
{
    public function canViewGarage(int $contactId): bool
    {
        if ($contactId <= 0 || !Loader::includeModule('crm')) {
            return false;
        }

        $perms = \CCrmPerms::GetCurrentUserPermissions();
        if (!\CCrmContact::CheckReadPermission($contactId, $perms)) {
            return false;
        }

        return true;
    }

    public function canEditGarage(int $contactId): bool
    {
        if ($contactId <= 0) {
            return false;
        }

        if (!Loader::includeModule('crm')) {
            return false;
        }

        global $USER;
        if (is_object($USER) && $USER->IsAdmin() && $this->canViewGarage($contactId)) {
            return true;
        }

        $perms = \CCrmPerms::GetCurrentUserPermissions();
        if (\CCrmContact::CheckUpdatePermission($contactId, $perms)) {
            return true;
        }

        $itemPerms = Container::getInstance()->getUserPermissions()->item();

        return $itemPerms->canUpdate(\CCrmOwnerType::Contact, $contactId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVehiclesByContact(int $contactId): array
    {
        $list = [];
        $rows = VehicleTable::getList([
            'filter' => ['=CONTACT_ID' => $contactId],
            'order' => ['ID' => 'DESC'],
        ]);

        while ($vehicle = $rows->fetch()) {
            $vehicle['BRAND_NAME'] = HighloadDirectory::getBrandName((int)$vehicle['BRAND_ID']);
            $vehicle['MODEL_NAME'] = HighloadDirectory::getModelName((int)$vehicle['MODEL_ID']);
            $vehicle['TITLE'] = trim($vehicle['BRAND_NAME'] . ' ' . $vehicle['MODEL_NAME']);
            $list[] = $vehicle;
        }

        return $list;
    }

    public function addVehicle(int $contactId, array $fields): Result
    {
        $result = new Result();
        if (!$this->canEditGarage($contactId)) {
            $result->addError(new Error(Loc::getMessage('OTUS_AUTO_GARAGE_ACCESS_DENIED')));

            return $result;
        }

        $storageError = $this->ensureVehicleStorage();
        if ($storageError !== null) {
            $result->addError($storageError);

            return $result;
        }

        $brandId = (int)($fields['BRAND_ID'] ?? 0);
        $modelId = (int)($fields['MODEL_ID'] ?? 0);
        if (!HighloadDirectory::isModelOfBrand($modelId, $brandId)) {
            $result->addError(new Error(Loc::getMessage('OTUS_AUTO_GARAGE_MODEL_BRAND_MISMATCH')));

            return $result;
        }

        global $USER;
        $year = (int)($fields['YEAR'] ?? 0);

        $saveFields = [
            'CONTACT_ID' => $contactId,
            'BRAND_ID' => $brandId,
            'MODEL_ID' => $modelId,
            'PLATE_NUMBER' => trim((string)($fields['PLATE_NUMBER'] ?? '')),
            'COLOR' => trim((string)($fields['COLOR'] ?? '')),
            'MILEAGE' => max(0, (int)($fields['MILEAGE'] ?? 0)),
            'DATE_CREATE' => new DateTime(),
            'CREATED_BY' => is_object($USER) ? (int)$USER->GetID() : 0,
        ];
        if ($year > 0) {
            $saveFields['YEAR'] = $year;
        }

        $addResult = VehicleTable::add($saveFields);
        if (!$addResult->isSuccess()) {
            $messages = $addResult->getErrorMessages();
            foreach ($messages as $message) {
                $result->addError(new Error($message));
            }
            AddMessage2Log(
                'addVehicle failed: contact=' . $contactId . '; ' . implode('; ', $messages),
                'otus.autoservice'
            );
        } else {
            $result->setData(['ID' => $addResult->getId()]);
        }

        return $result;
    }

    private function ensureVehicleStorage(): ?Error
    {
        $connection = Application::getConnection();
        $entity = VehicleTable::getEntity();
        $tableName = $entity->getDBTableName();

        if ($connection->isTableExists($tableName)) {
            return null;
        }

        try {
            $entity->createDbTable();
        } catch (\Throwable $exception) {
            return new Error(
                Loc::getMessage('OTUS_AUTO_GARAGE_NO_TABLE', ['#MSG#' => $exception->getMessage()])
            );
        }

        return $connection->isTableExists($tableName)
            ? null
            : new Error(Loc::getMessage('OTUS_AUTO_GARAGE_NO_TABLE', ['#MSG#' => 'createDbTable']));
    }

    public function getVehicleTitle(int $vehicleId): string
    {
        $vehicle = VehicleTable::getByPrimary($vehicleId)->fetch();
        if (!$vehicle) {
            return '';
        }

        $brand = HighloadDirectory::getBrandName((int)$vehicle['BRAND_ID']);
        $model = HighloadDirectory::getModelName((int)$vehicle['MODEL_ID']);
        $plate = (string)$vehicle['PLATE_NUMBER'];

        return trim(sprintf('%s %s — %s', $brand, $model, $plate));
    }

    /**
     * Контакт, если нужно проверить «своих» клиентов (механик).
     */
    public function getContactAssignedUserId(int $contactId): int
    {
        $factory = Container::getInstance()->getFactory(\CCrmOwnerType::Contact);
        if ($factory === null) {
            return 0;
        }

        $item = $factory->getItem($contactId);
        if ($item === null) {
            return 0;
        }

        return (int)$item->getAssignedById();
    }
}
