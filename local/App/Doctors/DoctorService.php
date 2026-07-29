<?php

namespace App\Doctors;

use App\Doctors\Abstract\AbstractIblockService;
use Bitrix\Iblock\Elements\ElementDoctorsTable;
use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use CFile;
use CIBlockElement;

/**
 * Сервис работы с врачами (инфоблок doctors).
 */
class DoctorService extends AbstractIblockService
{
    private const IBLOCK_API_CODE = 'doctors';

    /**
     * Возвращает список активных врачей.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getList(): array
    {
        $this->ensureIblockModule();
        Loc::loadMessages(__FILE__);

        $collection = ElementDoctorsTable::getList([
            'select' => [
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'FIRST_NAME',
                'LAST_NAME',
                'MIDDLE_NAME',
            ],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['NAME' => 'ASC'],
        ])->fetchCollection();

        $result = [];

        foreach ($collection as $doctor)
        {
            $result[] = $this->mapDoctor($doctor);
        }

        return $result;
    }

    /**
     * Возвращает врача с привязанными процедурами.
     *
     * @param int $doctorId ID врача
     *
     * @return array{doctor: array<string, mixed>|null, procedures: array<int, array<string, mixed>>}
     */
    public function getWithProcedures(int $doctorId): array
    {
        $this->ensureIblockModule();

        if ($doctorId <= 0)
        {
            return ['doctor' => null, 'procedures' => []];
        }

        $collection = ElementDoctorsTable::getList([
            'select' => [
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'FIRST_NAME',
                'LAST_NAME',
                'MIDDLE_NAME',
            ],
            'filter' => [
                'ID' => $doctorId,
                'ACTIVE' => 'Y',
            ],
            'limit' => 1,
        ])->fetchCollection();

        $doctor = $collection->current();

        if ($doctor === null)
        {
            return ['doctor' => null, 'procedures' => []];
        }

        $doctorData = $this->mapDoctor($doctor);

        return [
            'doctor' => $doctorData,
            'procedures' => $this->loadProceduresByIds($doctorData['PROCEDURE_IDS']),
        ];
    }

    /**
     * Загружает процедуры по списку ID.
     *
     * @param array<int, int> $procedureIds ID процедур
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadProceduresByIds(array $procedureIds): array
    {
        if ($procedureIds === [])
        {
            return [];
        }

        $rows = ElementProceduresTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => [
                '@ID' => $procedureIds,
                'ACTIVE' => 'Y',
            ],
            'order' => ['NAME' => 'ASC'],
        ])->fetchAll();

        $result = [];

        foreach ($rows as $row)
        {
            $result[] = [
                'ID' => (int)$row['ID'],
                'NAME' => (string)$row['NAME'],
            ];
        }

        return $result;
    }

    /**
     * Возвращает варианты специализаций для формы.
     *
     * @return array<int, string>
     */
    public function getSpecializationOptions(): array
    {
        $this->ensureIblockModule();

        $property = PropertyTable::getList([
            'filter' => [
                '=IBLOCK.CODE' => self::IBLOCK_API_CODE,
                '=CODE' => 'SPEC_IDS',
                '=ACTIVE' => 'Y',
            ],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (!$property)
        {
            return [];
        }

        $rows = PropertyEnumerationTable::getList([
            'filter' => ['PROPERTY_ID' => (int)$property['ID']],
            'select' => ['ID', 'VALUE'],
            'order' => ['SORT' => 'ASC'],
        ])->fetchAll();

        $result = [];

        foreach ($rows as $row)
        {
            $result[(int)$row['ID']] = (string)$row['VALUE'];
        }

        return $result;
    }

    /**
     * Создаёт врача.
     *
     * @param array<string, mixed> $data Данные формы
     * @param array<string, mixed>|null $photoFile Файл фото
     *
     * @return int ID врача
     */
    public function create(array $data, ?array $photoFile = null): int
    {
        Loc::loadMessages(__FILE__);

        [$fields, $properties] = $this->prepareSaveData($data, $photoFile);

        return $this->addElementViaCiBlock($this->getIblockId(), $fields, $properties);
    }

    /**
     * Обновляет врача.
     *
     * @param int $doctorId ID врача
     * @param array<string, mixed> $data Данные формы
     * @param array<string, mixed>|null $photoFile Файл фото
     *
     * @return void
     */
    public function update(int $doctorId, array $data, ?array $photoFile = null): void
    {
        Loc::loadMessages(__FILE__);
        $this->ensureIblockModule();

        if ($doctorId <= 0)
        {
            throw new \RuntimeException(Loc::getMessage('DOCTOR_ID_REQUIRED'));
        }

        if ($this->getWithProcedures($doctorId)['doctor'] === null)
        {
            throw new \RuntimeException(Loc::getMessage('DOCTOR_NOT_FOUND'));
        }

        [$fields, $properties] = $this->prepareSaveData($data, $photoFile);
        $this->updateElementViaCiBlock($doctorId, $fields, $properties);
    }

    /**
     * Возвращает ID инфоблока врачей.
     *
     * @return int
     */
    private function getIblockId(): int
    {
        static $iblockId = null;

        if ($iblockId !== null)
        {
            return $iblockId;
        }

        $iblock = IblockTable::getList([
            'filter' => [
                '=CODE' => self::IBLOCK_API_CODE,
                '=ACTIVE' => 'Y',
            ],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (!$iblock)
        {
            throw new \RuntimeException(Loc::getMessage('DOCTOR_IBLOCK_NOT_FOUND'));
        }

        $iblockId = (int)$iblock['ID'];

        return $iblockId;
    }

    /**
     * Подготавливает поля и свойства для сохранения.
     *
     * @param array<string, mixed> $data Данные формы
     * @param array<string, mixed>|null $photoFile Файл фото
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function prepareSaveData(array $data, ?array $photoFile = null): array
    {
        $firstName = trim((string)($data['FIRST_NAME'] ?? ''));
        $lastName = trim((string)($data['LAST_NAME'] ?? ''));
        $middleName = trim((string)($data['MIDDLE_NAME'] ?? ''));

        if ($firstName === '' || $lastName === '')
        {
            throw new \RuntimeException(Loc::getMessage('DOCTOR_NAME_REQUIRED'));
        }

        $specializationIds = $this->normalizeMultiplePropertyValues($data['SPEC_IDS'] ?? []);

        if ($specializationIds === [])
        {
            throw new \RuntimeException(Loc::getMessage('DOCTOR_SPECIALIZATION_REQUIRED'));
        }

        $fields = [
            'NAME' => trim($lastName . ' ' . $firstName . ' ' . $middleName),
            'ACTIVE' => 'Y',
        ];

        if ($photoFile !== null && !empty($photoFile['tmp_name']))
        {
            $fields['PREVIEW_PICTURE'] = $photoFile;
        }

        $properties = [
            'FIRST_NAME' => $firstName,
            'LAST_NAME' => $lastName,
            'MIDDLE_NAME' => $middleName,
            'PROC_IDS' => $this->normalizeMultiplePropertyValues($data['PROC_IDS'] ?? []),
            'SPEC_IDS' => $specializationIds,
        ];

        return [$fields, $properties];
    }

    /**
     * Нормализует множественное значение свойства из POST-данных формы.
     *
     * @param mixed $value Значение из формы
     *
     * @return array<int, int>
     */
    private function normalizeMultiplePropertyValues(mixed $value): array
    {
        if (!is_array($value))
        {
            $value = [$value];
        }

        return array_values(array_filter(array_map('intval', $value)));
    }

    /**
     * Загружает все значения множественного свойства элемента.
     *
     * @param int $elementId ID элемента
     * @param string $propertyCode Код свойства
     *
     * @return array<int, int>
     */
    private function loadMultiplePropertyValues(int $elementId, string $propertyCode): array
    {
        $values = [];

        $propertyResult = CIBlockElement::GetProperty(
            $this->getIblockId(),
            $elementId,
            ['sort' => 'asc', 'value_id' => 'asc'],
            ['CODE' => $propertyCode]
        );

        while ($row = $propertyResult->Fetch())
        {
            if ($row['VALUE'] === '' || $row['VALUE'] === null)
            {
                continue;
            }

            $values[] = (int)$row['VALUE'];
        }

        return array_values(array_unique($values));
    }

    /**
     * Преобразует ORM-объект врача в массив для шаблона.
     *
     * @param object $doctor ORM-объект ElementDoctorsTable
     *
     * @return array<string, mixed>
     */
    private function mapDoctor(object $doctor): array
    {
        $firstName = (string)($doctor->getFirstName()?->getValue() ?? '');
        $lastName = (string)($doctor->getLastName()?->getValue() ?? '');
        $middleName = (string)($doctor->getMiddleName()?->getValue() ?? '');
        $elementId = (int)$doctor->getId();

        $procedureIds = $this->loadMultiplePropertyValues($elementId, 'PROC_IDS');
        $specializationIds = $this->loadMultiplePropertyValues($elementId, 'SPEC_IDS');

        $previewPictureId = (int)$doctor->getPreviewPicture();
        $previewPictureSrc = $previewPictureId > 0 ? CFile::GetPath($previewPictureId) : null;

        return [
            'ID' => $elementId,
            'FIRST_NAME' => $firstName,
            'LAST_NAME' => $lastName,
            'MIDDLE_NAME' => $middleName,
            'FULL_NAME' => trim($lastName . ' ' . $firstName . ' ' . $middleName),
            'PREVIEW_PICTURE_SRC' => $previewPictureSrc !== '' ? $previewPictureSrc : null,
            'PROCEDURE_IDS' => $procedureIds,
            'SPECIALIZATION_IDS' => $specializationIds,
        ];
    }
}
