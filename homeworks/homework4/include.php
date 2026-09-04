<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/App/autoload.php';

Loc::loadMessages(__FILE__);

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

/**
 * Преобразует ORM-объект визита в массив для шаблона.
 *
 * Свойства инфоблока читаются через геттеры связанных объектов (fetchCollection),
 * т.к. в flat-SELECT через JOIN они недоступны.
 *
 * @param object $visit ORM-объект PatientVisitTable
 *
 * @return array<string, mixed>
 */
function mapPatientVisit(object $visit): array
{
    $doctor = $visit->getDoctor();
    $procedure = $visit->getProcedure();

    $doctorName = '';

    if ($doctor !== null)
    {
        $doctorName = trim(implode(' ', array_filter([
            (string)($doctor->getLastName()?->getValue() ?? ''),
            (string)($doctor->getFirstName()?->getValue() ?? ''),
            (string)($doctor->getMiddleName()?->getValue() ?? ''),
        ])));

        if ($doctorName === '')
        {
            $doctorName = (string)($doctor->getName() ?? '');
        }
    }

    $procedureName = $procedure !== null ? (string)($procedure->getName() ?? '') : '';

    return [
        'ID' => (int)$visit->getId(),
        'PATIENT_NAME' => (string)$visit->getPatientName(),
        'VISITS_COUNT' => (int)$visit->getVisitsCount(),
        'DOCTOR_FULL_NAME' => $doctorName,
        'PROCEDURE_NAME' => $procedureName,
    ];
}
