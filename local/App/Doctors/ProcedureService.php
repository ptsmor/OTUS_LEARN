<?php

namespace App\Doctors;

use App\Doctors\Abstract\AbstractIblockService;
use Bitrix\Iblock\Elements\ElementProceduresTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Localization\Loc;

/**
 * Сервис работы с процедурами (инфоблок procedures).
 */
class ProcedureService extends AbstractIblockService
{
    private const IBLOCK_API_CODE = 'procedures';

    /**
     * Возвращает список активных процедур.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getList(): array
    {
        $this->ensureIblockModule();

        $rows = ElementProceduresTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => ['ACTIVE' => 'Y'],
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
     * Создаёт процедуру.
     *
     * @param string $name Название
     *
     * @return int ID процедуры
     */
    public function create(string $name): int
    {
        Loc::loadMessages(__FILE__);

        $name = trim($name);

        if ($name === '')
        {
            throw new \RuntimeException(Loc::getMessage('PROCEDURE_NAME_REQUIRED'));
        }

        return $this->addElementViaCiBlock($this->getIblockId(), [
            'NAME' => $name,
            'ACTIVE' => 'Y',
        ], []);
    }

    /**
     * Возвращает ID инфоблока процедур.
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
            throw new \RuntimeException(Loc::getMessage('PROCEDURE_IBLOCK_NOT_FOUND'));
        }

        $iblockId = (int)$iblock['ID'];

        return $iblockId;
    }
}
