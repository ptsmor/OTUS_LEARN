<?php

namespace Otus\Autoservice;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Автомобиль клиента (гараж).
 */
class VehicleTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_autoservice_vehicle';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new IntegerField('CONTACT_ID'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_CONTACT_ID'))
                ->configureRequired(true),

            (new IntegerField('BRAND_ID'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_BRAND_ID'))
                ->configureRequired(true),

            (new IntegerField('MODEL_ID'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_MODEL_ID'))
                ->configureRequired(true),

            (new StringField('PLATE_NUMBER'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_PLATE'))
                ->configureRequired(true)
                ->addValidator(new LengthValidator(1, 20)),

            (new IntegerField('YEAR'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_YEAR')),

            (new StringField('COLOR'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_COLOR'))
                ->addValidator(new LengthValidator(null, 50)),

            (new IntegerField('MILEAGE'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_MILEAGE'))
                ->configureDefaultValue(0),

            (new DatetimeField('DATE_CREATE'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_DATE_CREATE')),

            (new IntegerField('CREATED_BY'))
                ->configureTitle(Loc::getMessage('OTUS_AUTO_VEHICLE_CREATED_BY')),
        ];
    }
}
