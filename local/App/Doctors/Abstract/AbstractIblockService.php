<?php

namespace App\Doctors\Abstract;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CIBlockElement;

/**
 * Базовый сервис сохранения элементов инфоблока через CIBlockElement.
 */
abstract class AbstractIblockService
{
    /**
     * Убирает HTML из сообщений Bitrix.
     *
     * @param string $message Исходное сообщение
     *
     * @return string
     */
    protected static function sanitizeBitrixMessage(string $message): string
    {
        $message = str_replace(['<br>', '<br/>', '<br />'], ' ', $message);
        $message = trim(strip_tags($message));

        return preg_replace('/\s+/u', ' ', $message) ?? $message;
    }

    /**
     * Подключает модуль iblock.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function ensureIblockModule(): void
    {
        Loc::loadMessages(__FILE__);

        if (!Loader::includeModule('iblock'))
        {
            throw new \RuntimeException(Loc::getMessage('IBLOCK_MODULE_ERROR'));
        }
    }

    /**
     * Приводит значения свойств к формату PROPERTY_VALUES при создании.
     *
     * @param array<string, mixed> $properties Значения свойств
     *
     * @return array<string, mixed>
     */
    protected function formatPropertyValuesForAdd(array $properties): array
    {
        $result = [];

        foreach ($properties as $code => $value)
        {
            if (!is_array($value) || !array_is_list($value))
            {
                $result[$code] = $value;

                continue;
            }

            if ($value === [])
            {
                $result[$code] = false;

                continue;
            }

            $multipleValue = [];

            foreach ($value as $index => $itemValue)
            {
                $multipleValue['n' . $index] = ['VALUE' => $itemValue];
            }

            $result[$code] = $multipleValue;
        }

        return $result;
    }

    /**
     * Приводит значения свойств к формату для обновления.
     *
     * @param array<string, mixed> $properties Значения свойств
     *
     * @return array<string, mixed>
     */
    protected function formatPropertyValuesForUpdate(array $properties): array
    {
        $result = [];

        foreach ($properties as $code => $value)
        {
            if (is_array($value) && array_is_list($value))
            {
                $result[$code] = $value === [] ? false : $value;

                continue;
            }

            $result[$code] = $value;
        }

        return $result;
    }

    /**
     * Создаёт элемент инфоблока.
     *
     * @param int $iblockId ID инфоблока
     * @param array<string, mixed> $fields Поля элемента
     * @param array<string, mixed> $properties Значения свойств
     *
     * @return int ID элемента
     */
    protected function addElementViaCiBlock(int $iblockId, array $fields, array $properties): int
    {
        $this->ensureIblockModule();

        $elementFields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $fields['NAME'],
            'ACTIVE' => $fields['ACTIVE'] ?? 'Y',
            'PROPERTY_VALUES' => $this->formatPropertyValuesForAdd($properties),
        ];

        if (!empty($fields['PREVIEW_PICTURE']))
        {
            $elementFields['PREVIEW_PICTURE'] = $fields['PREVIEW_PICTURE'];
        }

        $element = new CIBlockElement();
        $elementId = (int)$element->Add($elementFields);

        if ($elementId <= 0)
        {
            throw new \RuntimeException(
                self::sanitizeBitrixMessage($element->LAST_ERROR ?: Loc::getMessage('IBLOCK_ELEMENT_ADD_ERROR'))
            );
        }

        return $elementId;
    }

    /**
     * Обновляет элемент инфоблока.
     *
     * @param int $elementId ID элемента
     * @param array<string, mixed> $fields Поля элемента
     * @param array<string, mixed> $properties Значения свойств
     *
     * @return void
     */
    protected function updateElementViaCiBlock(
        int $elementId,
        array $fields,
        array $properties,
    ): void {
        $this->ensureIblockModule();

        $element = new CIBlockElement();
        $updateFields = $fields;

        if ($properties !== [])
        {
            $updateFields['PROPERTY_VALUES'] = $this->formatPropertyValuesForUpdate($properties);
        }

        if (!$element->Update($elementId, $updateFields))
        {
            throw new \RuntimeException(
                self::sanitizeBitrixMessage($element->LAST_ERROR ?: Loc::getMessage('IBLOCK_ELEMENT_UPDATE_ERROR'))
            );
        }
    }
}
