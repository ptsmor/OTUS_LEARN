<?php

namespace Otus\Autoservice\Install;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Направление сделок «Сервисное обслуживание» и стадии по ТЗ.
 */
class ServiceDealCategoryInstaller
{
    public const CATEGORY_NAME = 'Сервисное обслуживание';
    public const OPTION_KEY = 'service_deal_category_id';

    private const STAGES = [
        ['SUFFIX' => 'ACCEPT', 'NAME' => 'Приёмка', 'SORT' => 10, 'SEMANTICS' => 'P'],
        ['SUFFIX' => 'DIAG', 'NAME' => 'Диагностика', 'SORT' => 20, 'SEMANTICS' => 'P'],
        ['SUFFIX' => 'WAIT', 'NAME' => 'Ожидание запчастей', 'SORT' => 30, 'SEMANTICS' => 'P'],
        ['SUFFIX' => 'REPAIR', 'NAME' => 'Ремонт', 'SORT' => 40, 'SEMANTICS' => 'P'],
        ['SUFFIX' => 'CHECK', 'NAME' => 'Проверка', 'SORT' => 50, 'SEMANTICS' => 'P'],
        ['SUFFIX' => 'WON', 'NAME' => 'Завершено', 'SORT' => 60, 'SEMANTICS' => 'S'],
    ];

    public function install(): int
    {
        if (!Loader::includeModule('crm')) {
            return 0;
        }

        if (!class_exists(DealCategory::class)) {
            throw new \RuntimeException('Класс DealCategory недоступен — обновите модуль crm');
        }

        $existingId = $this->findCategoryIdByName(self::CATEGORY_NAME);
        if ($existingId === null) {
            $existingId = (int)Option::get('otus.autoservice', self::OPTION_KEY, 0);
            if ($existingId > 0 && !$this->categoryExists($existingId)) {
                $existingId = 0;
            }
        }

        if ($existingId > 0) {
            $this->safeInstallStages($existingId);
            Option::set('otus.autoservice', self::OPTION_KEY, (string)$existingId);

            return $existingId;
        }

        $categoryId = (int)DealCategory::add([
            'NAME' => self::CATEGORY_NAME,
            'SORT' => 200,
        ]);

        if ($categoryId <= 0) {
            throw new \RuntimeException('Не удалось создать направление сделок');
        }

        $this->safeInstallStages($categoryId);
        Option::set('otus.autoservice', self::OPTION_KEY, (string)$categoryId);

        return $categoryId;
    }

    /**
     * @return string|null Предупреждение, если стадии нужно поправить вручную
     */
    public function getLastStageWarning(): ?string
    {
        return $this->stageWarning;
    }

    private ?string $stageWarning = null;

    private function safeInstallStages(int $categoryId): void
    {
        try {
            $this->ensureCustomStages($categoryId);
        } catch (\Throwable $exception) {
            $this->stageWarning = $exception->getMessage();
        }
    }

    private function findCategoryIdByName(string $name): ?int
    {
        if (method_exists(DealCategory::class, 'resolveByName')) {
            $id = DealCategory::resolveByName($name);
            if (is_numeric($id) && (int)$id > 0) {
                return (int)$id;
            }
        }

        if (!method_exists(DealCategory::class, 'getList')) {
            return null;
        }

        $row = DealCategory::getList([
            'filter' => ['=NAME' => $name],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (is_array($row) && (int)$row['ID'] > 0) {
            return (int)$row['ID'];
        }

        return null;
    }

    private function categoryExists(int $categoryId): bool
    {
        if (method_exists(DealCategory::class, 'exists')) {
            return DealCategory::exists($categoryId);
        }

        if (method_exists(DealCategory::class, 'get')) {
            $category = DealCategory::get($categoryId);

            return is_array($category) && !empty($category);
        }

        return false;
    }

    private function ensureCustomStages(int $categoryId): void
    {
        try {
            if (method_exists(DealCategory::class, 'eraseStages')) {
                DealCategory::eraseStages($categoryId);
            }
        } catch (\Throwable $exception) {
            // Старые сборки: оставляем стадии по умолчанию, добавляем недостающие.
        }

        $this->installStages($categoryId);
    }

    private function installStages(int $categoryId): void
    {
        $entityId = $categoryId > 0 ? 'DEAL_STAGE_' . $categoryId : 'DEAL_STAGE';
        $prefix = 'DT' . $categoryId . '_';
        $status = new \CCrmStatus();

        foreach (self::STAGES as $stage) {
            $statusId = $prefix . $stage['SUFFIX'];
            if ($this->stageExists($entityId, $statusId, $status)) {
                continue;
            }

            $fields = [
                'ENTITY_ID' => $entityId,
                'STATUS_ID' => $statusId,
                'NAME' => $stage['NAME'],
                'SORT' => $stage['SORT'],
                'SEMANTICS' => $stage['SEMANTICS'],
            ];

            $result = $status->Add($fields);
            if ($result === false) {
                $error = (string)($status->LAST_ERROR ?? 'неизвестная ошибка');
                throw new \RuntimeException('Стадия «' . $stage['NAME'] . '»: ' . $error);
            }
        }
    }

    private function stageExists(string $entityId, string $statusId, \CCrmStatus $status): bool
    {
        $row = $status->GetStatus($entityId, $statusId);
        if (is_array($row) && $row !== []) {
            return true;
        }

        $dbRes = \CCrmStatus::GetList(
            ['SORT' => 'ASC'],
            ['ENTITY_ID' => $entityId, 'STATUS_ID' => $statusId]
        );

        return (bool)$dbRes->Fetch();
    }
}