<?php

namespace Otus\Autoservice\Service;

use Bitrix\Catalog\ProductTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

/**
 * Заявки на закупку (HL «OtusPurchaseRequest») — аналог смарт-процесса для коробки.
 */
class PurchaseRequestService
{
    public const BUILD_ID = 'OTUS_PURCHASE_V5_BLOCK4_20261123';

    public const HLBLOCK_NAME = 'OtusPurchaseRequest';

    /** Ожидает согласования (ручные заявки). */
    public const STATUS_NEW = 'NEW';
    /** Согласовано — закупщик может принять на склад. */
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_DONE = 'DONE';
    public const STATUS_REJECTED = 'REJECTED';

    private const OPTION_PURCHASER_USERS = 'purchase_notify_user_ids';
    private const OPTION_APPROVER_USERS = 'purchase_approver_user_ids';
    private const AUTO_QTY = 10;

    public function createAutoRequest(int $productId, int $quantity = self::AUTO_QTY): Result
    {
        $result = new Result();

        if ($productId <= 0) {
            return $result->addError(new \Bitrix\Main\Error('Не указан товар'));
        }

        if ($this->hasOpenRequestForProduct($productId)) {
            return $result->addError(new \Bitrix\Main\Error('По товару уже есть открытая заявка'));
        }

        global $USER;
        $userId = (is_object($USER) && $USER->IsAuthorized()) ? (int)$USER->GetID() : 1;

        $add = $this->addElement([
            'UF_PRODUCT_ID' => $productId,
            'UF_QUANTITY' => (float)$quantity,
            'UF_STATUS' => self::STATUS_APPROVED,
            'UF_IS_AUTO' => $this->hlBool(true),
            'UF_CREATED_BY' => $userId,
            'UF_REJECT_REASON' => '',
        ]);

        if (!$add->isSuccess()) {
            return $add;
        }

        $requestId = (int)$add->getData()['ID'];
        $productName = $this->resolveProductName($productId);
        $message = sprintf(
            'Автозаявка #%d: закупить %d шт. «%s» (товар #%d). Остаток на складе = 0.',
            $requestId,
            $quantity,
            $productName,
            $productId
        );

        $this->notifyPurchasers($message);
        $this->appendStockLog('Создана заявка на закупку #' . $requestId . ' для товара #' . $productId);

        $result->setData(['ID' => $requestId]);

        return $result;
    }

    public function createManualRequest(int $productId, float $quantity = 10): Result
    {
        $result = new Result();

        if ($productId <= 0) {
            return $result->addError(new \Bitrix\Main\Error('Не указан товар'));
        }

        if ($quantity <= 0) {
            return $result->addError(new \Bitrix\Main\Error('Количество должно быть больше 0'));
        }

        global $USER;
        $userId = (is_object($USER) && $USER->IsAuthorized()) ? (int)$USER->GetID() : 1;

        $add = $this->addElement([
            'UF_PRODUCT_ID' => $productId,
            'UF_QUANTITY' => $quantity,
            'UF_STATUS' => self::STATUS_NEW,
            'UF_IS_AUTO' => $this->hlBool(false),
            'UF_CREATED_BY' => $userId,
            'UF_REJECT_REASON' => '',
        ]);

        if (!$add->isSuccess()) {
            return $add;
        }

        $requestId = (int)$add->getData()['ID'];
        $productName = $this->resolveProductName($productId);
        $message = sprintf(
            'Ручная заявка #%d: закупить %s шт. «%s» (товар #%d).',
            $requestId,
            $this->formatQty($quantity),
            $productName,
            $productId
        );

        $this->notifyApprovers($message . ' Требуется согласование.');
        $this->appendStockLog('Создана ручная заявка #' . $requestId . ' для товара #' . $productId);

        return $result->setData(['ID' => $requestId]);
    }

    public function approveRequest(int $requestId): Result
    {
        $result = new Result();
        $row = $this->getRequestRow($requestId);
        if ($row === null) {
            return $result->addError(new \Bitrix\Main\Error('Заявка не найдена'));
        }

        if ((string)$row['UF_STATUS'] !== self::STATUS_NEW) {
            return $result->addError(new \Bitrix\Main\Error('Заявка не ожидает согласования'));
        }

        $update = $this->updateElement($requestId, ['UF_STATUS' => self::STATUS_APPROVED]);
        if (!$update->isSuccess()) {
            return $update;
        }

        $productId = (int)$row['UF_PRODUCT_ID'];
        $productName = $this->resolveProductName($productId);
        $qty = (float)$row['UF_QUANTITY'];

        $this->notifyPurchasers(sprintf(
            'Заявка #%d согласована: закупить %s шт. «%s» (товар #%d).',
            $requestId,
            $this->formatQty($qty),
            $productName,
            $productId
        ));

        $initiatorId = (int)$row['UF_CREATED_BY'];
        if ($initiatorId > 0) {
            $this->notifyUser(
                $initiatorId,
                sprintf('Заявка на закупку #%d согласована и передана закупщику.', $requestId)
            );
        }

        $this->appendStockLog('Заявка #' . $requestId . ' согласована');

        return $result->setData(['ID' => $requestId]);
    }

    /**
     * @return list<array{ID: int, NAME: string, QUANTITY: float}>
     */
    public function listCatalogProductsForSelect(): array
    {
        if (!Loader::includeModule('catalog')) {
            return [];
        }

        $items = [];
        $res = ProductTable::getList([
            'select' => ['ID', 'QUANTITY'],
            'order' => ['ID' => 'ASC'],
        ]);
        while ($row = $res->fetch()) {
            $id = (int)$row['ID'];
            $items[] = [
                'ID' => $id,
                'NAME' => $this->resolveProductName($id),
                'QUANTITY' => (float)$row['QUANTITY'],
            ];
        }

        return $items;
    }

    public function completeRequest(int $requestId): Result
    {
        $result = new Result();
        $row = $this->getRequestRow($requestId);
        if ($row === null) {
            return $result->addError(new \Bitrix\Main\Error('Заявка не найдена'));
        }

        $status = (string)$row['UF_STATUS'];
        if ($status === self::STATUS_DONE || $status === self::STATUS_REJECTED) {
            return $result->addError(new \Bitrix\Main\Error('Заявка уже обработана'));
        }
        if ($status !== self::STATUS_APPROVED) {
            return $result->addError(new \Bitrix\Main\Error('Сначала согласуйте ручную заявку'));
        }

        $productId = (int)$row['UF_PRODUCT_ID'];
        $quantity = (float)$row['UF_QUANTITY'];

        if (!Loader::includeModule('catalog')) {
            return $result->addError(new \Bitrix\Main\Error('Модуль catalog не подключён'));
        }

        $product = ProductTable::getById($productId)->fetch();
        if (!$product) {
            return $result->addError(new \Bitrix\Main\Error('Товар не найден'));
        }

        $newQty = (float)$product['QUANTITY'] + $quantity;
        ProductTable::update($productId, ['QUANTITY' => $newQty]);

        $update = $this->updateElement($requestId, ['UF_STATUS' => self::STATUS_DONE]);
        if (!$update->isSuccess()) {
            return $update;
        }

        $initiatorId = (int)$row['UF_CREATED_BY'];
        if ($initiatorId > 0) {
            $this->notifyUser(
                $initiatorId,
                sprintf(
                    'Заявка на закупку #%d выполнена. На склад добавлено %s шт. товара #%d (остаток %s).',
                    $requestId,
                    $this->formatQty($quantity),
                    $productId,
                    $this->formatQty($newQty)
                )
            );
        }

        $this->appendStockLog(sprintf(
            'Заявка #%d выполнена: товар #%d +%s (итого %s)',
            $requestId,
            $productId,
            $this->formatQty($quantity),
            $this->formatQty($newQty)
        ));

        $result->setData(['ID' => $requestId, 'QUANTITY' => $newQty]);

        return $result;
    }

    public function rejectRequest(int $requestId, string $reason): Result
    {
        $result = new Result();
        $row = $this->getRequestRow($requestId);
        if ($row === null) {
            return $result->addError(new \Bitrix\Main\Error('Заявка не найдена'));
        }

        $status = (string)$row['UF_STATUS'];
        if ($status === self::STATUS_DONE || $status === self::STATUS_REJECTED) {
            return $result->addError(new \Bitrix\Main\Error('Заявка уже обработана'));
        }

        $reason = trim($reason);
        if ($reason === '') {
            return $result->addError(new \Bitrix\Main\Error('Укажите причину отказа'));
        }

        $update = $this->updateElement($requestId, [
            'UF_STATUS' => self::STATUS_REJECTED,
            'UF_REJECT_REASON' => $reason,
        ]);
        if (!$update->isSuccess()) {
            return $update;
        }

        $initiatorId = (int)$row['UF_CREATED_BY'];
        if ($initiatorId > 0) {
            $stage = $status === self::STATUS_NEW ? 'отклонена при согласовании' : 'отклонена закупщиком';
            $this->notifyUser(
                $initiatorId,
                sprintf('Заявка на закупку #%d %s. Причина: %s', $requestId, $stage, $reason)
            );
        }

        $this->appendStockLog(sprintf('Заявка #%d отклонена: %s', $requestId, $reason));

        return $result->setData(['ID' => $requestId]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOpenRequests(): array
    {
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return [];
        }

        $rows = [];
        $res = $dataClass::getList([
            'filter' => ['=UF_STATUS' => self::STATUS_APPROVED],
            'order' => ['ID' => 'DESC'],
        ]);
        while ($item = $res->fetch()) {
            $productId = (int)$item['UF_PRODUCT_ID'];
            $rows[] = [
                'ID' => (int)$item['ID'],
                'PRODUCT_ID' => $productId,
                'PRODUCT_NAME' => $this->resolveProductName($productId),
                'QUANTITY' => (float)$item['UF_QUANTITY'],
                'IS_AUTO' => $this->isAutoFlag($item['UF_IS_AUTO'] ?? null),
                'CREATED_BY' => (int)$item['UF_CREATED_BY'],
            ];
        }

        return $rows;
    }

    /**
     * Ручные заявки, ожидающие согласования.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPendingApprovalRequests(): array
    {
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return [];
        }

        $rows = [];
        $res = $dataClass::getList([
            'filter' => ['=UF_STATUS' => self::STATUS_NEW],
            'order' => ['ID' => 'DESC'],
        ]);
        while ($item = $res->fetch()) {
            $productId = (int)$item['UF_PRODUCT_ID'];
            $rows[] = [
                'ID' => (int)$item['ID'],
                'PRODUCT_ID' => $productId,
                'PRODUCT_NAME' => $this->resolveProductName($productId),
                'QUANTITY' => (float)$item['UF_QUANTITY'],
                'CREATED_BY' => (int)$item['UF_CREATED_BY'],
                'IS_AUTO' => $this->isAutoFlag($item['UF_IS_AUTO'] ?? null),
            ];
        }

        return $rows;
    }

    /**
     * @return array{pending_manual: int, purchaser_queue: int, status_new: int, status_approved: int}
     */
    public function getQueueDiagnostics(): array
    {
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return [
                'pending_manual' => 0,
                'purchaser_queue' => 0,
                'status_new' => 0,
                'status_approved' => 0,
            ];
        }

        $newCount = 0;
        $approvedCount = 0;
        $res = $dataClass::getList([
            'filter' => [
                'LOGIC' => 'OR',
                ['=UF_STATUS' => self::STATUS_NEW],
                ['=UF_STATUS' => self::STATUS_APPROVED],
            ],
            'select' => ['ID', 'UF_STATUS', 'UF_IS_AUTO'],
        ]);
        while ($row = $res->fetch()) {
            $status = (string)$row['UF_STATUS'];
            if ($status === self::STATUS_NEW) {
                $newCount++;
            }
            if ($status === self::STATUS_APPROVED) {
                $approvedCount++;
            }
        }

        return [
            'pending_manual' => count($this->getPendingApprovalRequests()),
            'purchaser_queue' => count($this->getOpenRequests()),
            'status_new' => $newCount,
            'status_approved' => $approvedCount,
        ];
    }

    private function hasOpenRequestForProduct(int $productId): bool
    {
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return false;
        }

        $row = $dataClass::getList([
            'filter' => [
                '=UF_PRODUCT_ID' => $productId,
                [
                    'LOGIC' => 'OR',
                    ['=UF_STATUS' => self::STATUS_NEW],
                    ['=UF_STATUS' => self::STATUS_APPROVED],
                ],
            ],
            'limit' => 1,
            'select' => ['ID'],
        ])->fetch();

        return is_array($row);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function addElement(array $fields): Result
    {
        $result = new Result();
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return $result->addError(new \Bitrix\Main\Error('HL OtusPurchaseRequest не найден. Выполните миграцию.'));
        }

        $add = $dataClass::add($fields);
        if (!$add->isSuccess()) {
            return $result->addErrors($add->getErrors());
        }

        return $result->setData(['ID' => $add->getId()]);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function updateElement(int $id, array $fields): Result
    {
        $result = new Result();
        $dataClass = $this->getDataClass();
        if ($dataClass === null) {
            return $result->addError(new \Bitrix\Main\Error('HL OtusPurchaseRequest не найден'));
        }

        $update = $dataClass::update($id, $fields);
        if (!$update->isSuccess()) {
            return $result->addErrors($update->getErrors());
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getRequestRow(int $requestId): ?array
    {
        $dataClass = $this->getDataClass();
        if ($dataClass === null || $requestId <= 0) {
            return null;
        }

        $row = $dataClass::getById($requestId)->fetch();

        return is_array($row) ? $row : null;
    }

    private function getDataClass(): ?string
    {
        if (!Loader::includeModule('highloadblock')) {
            return null;
        }

        $hl = HighloadBlockTable::getList([
            'filter' => ['=NAME' => self::HLBLOCK_NAME],
            'limit' => 1,
        ])->fetch();
        if (!$hl) {
            return null;
        }

        $entity = HighloadBlockTable::compileEntity($hl);

        return $entity->getDataClass();
    }

    private function resolveProductName(int $productId): string
    {
        if ($productId <= 0 || !Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
            return 'Товар #' . $productId;
        }

        $product = ProductTable::getById($productId)->fetch();
        if (!$product || (int)$product['ID'] <= 0) {
            return 'Товар #' . $productId;
        }

        $element = \CIBlockElement::GetByID((int)$product['ID'])->Fetch();

        return is_array($element) ? (string)$element['NAME'] : ('Товар #' . $productId);
    }

    private function notifyPurchasers(string $message): void
    {
        foreach ($this->getPurchaserUserIds() as $userId) {
            $this->notifyUser($userId, $message);
        }
    }

    private function notifyApprovers(string $message): void
    {
        foreach ($this->getApproverUserIds() as $userId) {
            $this->notifyUser($userId, $message);
        }
    }

    /**
     * @return int[]
     */
    private function getPurchaserUserIds(): array
    {
        $raw = (string)Option::get('otus.autoservice', self::OPTION_PURCHASER_USERS, '1');
        $ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $raw)));

        return $ids !== [] ? $ids : [1];
    }

    /**
     * @return int[]
     */
    private function getApproverUserIds(): array
    {
        $raw = (string)Option::get('otus.autoservice', self::OPTION_APPROVER_USERS, '');
        if ($raw === '') {
            return $this->getPurchaserUserIds();
        }

        $ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $raw)));

        return $ids !== [] ? $ids : $this->getPurchaserUserIds();
    }

    private function notifyUser(int $userId, string $message): void
    {
        if ($userId <= 0 || !Loader::includeModule('im')) {
            return;
        }

        \CIMNotify::Add([
            'TO_USER_ID' => $userId,
            'FROM_USER_ID' => 0,
            'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
            'NOTIFY_MODULE' => 'otus.autoservice',
            'NOTIFY_MESSAGE' => $message,
        ]);
    }

    private function appendStockLog(string $message): void
    {
        (new CatalogStockService())->appendLog($message);
    }

    private function hlBool(bool $isAuto): int
    {
        return $isAuto ? 1 : 0;
    }

    private function isAutoFlag(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === false || $value === 0 || $value === '0' || $value === 'N' || $value === 'n') {
            return false;
        }

        return $value === 'Y' || $value === 'y' || $value === true || $value === 1 || $value === '1';
    }

    private function formatQty(float $qty): string
    {
        if (abs($qty - round($qty)) < 0.0001) {
            return (string)(int)round($qty);
        }

        return rtrim(rtrim(sprintf('%.4f', $qty), '0'), '.');
    }
}