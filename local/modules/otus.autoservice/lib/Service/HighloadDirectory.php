<?php

namespace Otus\Autoservice\Service;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

/**
 * Справочники марок и моделей (highload-блоки).
 */
class HighloadDirectory
{
    public const BRAND_HL_NAME = 'OtusAutoBrand';
    public const MODEL_HL_NAME = 'OtusAutoModel';

    public static function isReady(): bool
    {
        return Loader::includeModule('highloadblock')
            && self::getBrandDataClass() !== null
            && self::getModelDataClass() !== null;
    }

    /**
     * @return array<int, string>
     */
    public static function getBrandList(): array
    {
        $dataClass = self::getBrandDataClass();
        if ($dataClass === null) {
            return [];
        }

        $result = [];
        $rows = $dataClass::getList([
            'select' => ['ID', 'UF_NAME'],
            'order' => ['UF_NAME' => 'ASC'],
        ]);
        while ($row = $rows->fetch()) {
            $result[(int)$row['ID']] = (string)$row['UF_NAME'];
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public static function getModelList(?int $brandId = null): array
    {
        $dataClass = self::getModelDataClass();
        if ($dataClass === null) {
            return [];
        }

        $filter = [];
        if ($brandId !== null && $brandId > 0) {
            $filter['=UF_BRAND'] = $brandId;
        }

        $result = [];
        $rows = $dataClass::getList([
            'select' => ['ID', 'UF_NAME', 'UF_BRAND'],
            'filter' => $filter,
            'order' => ['UF_NAME' => 'ASC'],
        ]);
        while ($row = $rows->fetch()) {
            $result[(int)$row['ID']] = (string)$row['UF_NAME'];
        }

        return $result;
    }

    public static function getBrandName(int $brandId): string
    {
        $dataClass = self::getBrandDataClass();
        if ($dataClass === null || $brandId <= 0) {
            return '';
        }

        $row = $dataClass::getById($brandId)->fetch();

        return $row ? (string)$row['UF_NAME'] : '';
    }

    /**
     * Модели, сгруппированные по ID марки (для зависимого списка в форме).
     *
     * @return array<int, array<int, string>>
     */
    public static function getModelsGroupedByBrand(): array
    {
        $dataClass = self::getModelDataClass();
        if ($dataClass === null) {
            return [];
        }

        $grouped = [];
        $rows = $dataClass::getList([
            'select' => ['ID', 'UF_NAME', 'UF_BRAND'],
            'order' => ['UF_NAME' => 'ASC'],
        ]);
        while ($row = $rows->fetch()) {
            $brandId = self::extractHlFieldId($row['UF_BRAND'] ?? null);
            if ($brandId <= 0) {
                continue;
            }
            $grouped[$brandId][(int)$row['ID']] = (string)$row['UF_NAME'];
        }

        return $grouped;
    }

    public static function isModelOfBrand(int $modelId, int $brandId): bool
    {
        if ($modelId <= 0 || $brandId <= 0) {
            return false;
        }

        $dataClass = self::getModelDataClass();
        if ($dataClass === null) {
            return false;
        }

        $row = $dataClass::getByPrimary($modelId)->fetch();
        if (!$row) {
            return false;
        }

        return self::extractHlFieldId($row['UF_BRAND'] ?? null) === $brandId;
    }

    /**
     * @param mixed $value
     */
    public static function extractHlFieldId($value): int
    {
        if (is_array($value)) {
            if (isset($value['VALUE'])) {
                return (int)$value['VALUE'];
            }
            if (isset($value['ID'])) {
                return (int)$value['ID'];
            }

            $first = reset($value);

            return is_scalar($first) ? (int)$first : 0;
        }

        return (int)$value;
    }

    public static function getModelName(int $modelId): string
    {
        $dataClass = self::getModelDataClass();
        if ($dataClass === null || $modelId <= 0) {
            return '';
        }

        $row = $dataClass::getById($modelId)->fetch();

        return $row ? (string)$row['UF_NAME'] : '';
    }

  /**
   * @return class-string<\Bitrix\Main\ORM\Data\DataManager>|null
   */
    private static function getBrandDataClass(): ?string
    {
        return self::resolveDataClass(self::BRAND_HL_NAME);
    }

  /**
   * @return class-string<\Bitrix\Main\ORM\Data\DataManager>|null
   */
    private static function getModelDataClass(): ?string
    {
        return self::resolveDataClass(self::MODEL_HL_NAME);
    }

  /**
   * @return class-string<\Bitrix\Main\ORM\Data\DataManager>|null
   */
    private static function resolveDataClass(string $name): ?string
    {
        if (!Loader::includeModule('highloadblock')) {
            return null;
        }

        $hl = HighloadBlockTable::getList([
            'filter' => ['=NAME' => $name],
            'limit' => 1,
        ])->fetch();
        if (!$hl) {
            return null;
        }

        $entity = HighloadBlockTable::compileEntity($hl);

        return $entity->getDataClass();
    }
}
