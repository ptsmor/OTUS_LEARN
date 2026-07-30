<?php

namespace App\Clinic;

use App\Clinic\Models\DoctorTable;
use App\Clinic\Models\ProcedureTable;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * ORM-модель таблицы визитов пациентов клиники.
 */
class PatientVisitTable extends DataManager
{
    /**
     * Возвращает имя таблицы в БД.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'patient_visits';
    }

    /**
     * Возвращает карту полей сущности.
     *
     * @return array
     */
    public static function getMap(): array
    {
        Loc::loadMessages(__FILE__);

        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('PATIENT_VISIT_FIELD_ID'),
            ]),
            new StringField('PATIENT_NAME', [
                'required' => true,
                'title' => Loc::getMessage('PATIENT_VISIT_FIELD_PATIENT_NAME'),
            ]),
            new IntegerField('VISITS_COUNT', [
                'required' => true,
                'title' => Loc::getMessage('PATIENT_VISIT_FIELD_VISITS_COUNT'),
            ]),
            new IntegerField('DOCTOR_ID', [
                'required' => true,
                'title' => Loc::getMessage('PATIENT_VISIT_FIELD_DOCTOR_ID'),
            ]),
            new ReferenceField(
                'DOCTOR',
                DoctorTable::class,
                ['=this.DOCTOR_ID' => 'ref.ID']
            ),
            new IntegerField('PROCEDURE_ID', [
                'required' => true,
                'title' => Loc::getMessage('PATIENT_VISIT_FIELD_PROCEDURE_ID'),
            ]),
            new ReferenceField(
                'PROCEDURE',
                ProcedureTable::class,
                ['=this.PROCEDURE_ID' => 'ref.ID']
            ),
        ];
    }

    /**
     * Создаёт таблицу в БД, если её ещё нет.
     *
     * @return void
     */
    public static function ensureTableExists(): void
    {
        $connection = \Bitrix\Main\Application::getConnection();

        if (!$connection->isTableExists(static::getTableName()))
        {
            static::getEntity()->createDbTable();
        }
    }
}
