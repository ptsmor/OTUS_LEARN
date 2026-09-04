<?php

namespace App\Clinic\Models;

use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Localization\Loc;

/**
 * ORM-модель элементов инфоблока «Процедуры».
 *
 * Связь с таблицей {@see \App\Clinic\PatientVisitTable}:
 * patient_visits.PROCEDURE_ID = procedures.ID (через ReferenceField).
 *
 * Поля элемента (генерируются Bitrix по API-коду infoblock):
 * - ID, NAME
 */
class ProcedureTable extends ElementProceduresTable
{
    /** @var string Символьный код инфоблока */
    public const IBLOCK_CODE = 'procedures';

    /**
     * Возвращает символьный код инфоблока.
     *
     * @return string
     */
    public static function getIblockCode(): string
    {
        return self::IBLOCK_CODE;
    }

    /**
     * Возвращает ID инфоблока по символьному коду.
     *
     * @return int
     */
    public static function getIblockId(): int
    {
        static $iblockId = null;

        if ($iblockId !== null)
        {
            return $iblockId;
        }

        Loc::loadMessages(__FILE__);

        $iblock = IblockTable::getList([
            'filter' => ['=CODE' => self::IBLOCK_CODE, '=ACTIVE' => 'Y'],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (!$iblock)
        {
            throw new \RuntimeException(Loc::getMessage('PROCEDURE_TABLE_IBLOCK_NOT_FOUND'));
        }

        $iblockId = (int)$iblock['ID'];

        return $iblockId;
    }
}
