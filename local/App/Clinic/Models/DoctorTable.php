<?php

namespace App\Clinic\Models;

use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Localization\Loc;

/**
 * ORM-модель элементов инфоблока «Врачи».
 *
 * Связь с таблицей {@see \App\Clinic\PatientVisitTable}:
 * patient_visits.DOCTOR_ID = doctors.ID (через ReferenceField).
 *
 * Поля элемента и свойства (генерируются Bitrix по API-коду infoblock):
 * - ID, NAME, PREVIEW_PICTURE
 * - LAST_NAME, FIRST_NAME, MIDDLE_NAME
 * - PROC_IDS, SPEC_IDS
 */
class DoctorTable extends ElementDoctorsTable
{
    /** @var string Символьный код инфоблока */
    public const IBLOCK_CODE = 'doctors';

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
            throw new \RuntimeException(Loc::getMessage('DOCTOR_TABLE_IBLOCK_NOT_FOUND'));
        }

        $iblockId = (int)$iblock['ID'];

        return $iblockId;
    }
}
