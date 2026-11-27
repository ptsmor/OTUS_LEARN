<?php

namespace Sprint\Migration;

use Bitrix\Main\Config\Option;

/**
 * HL «Заявки на закупку» (хранение заявок, обработка в модуле otus.autoservice).
 */
class Version20261115143000_AutoservicePurchaseRequests extends Version
{
    protected $author = 'admin';

    protected $description = 'Автосервис: HL заявки на закупку OtusPurchaseRequest';

    protected $moduleVersion = '5.16.0';

    public function up(): void
    {
        $helper = $this->getHelperManager();

        $helper->Hlblock()->saveHlblock([
            'NAME' => 'OtusPurchaseRequest',
            'TABLE_NAME' => 'otus_purchase_request',
            'LANG' => [
                'ru' => ['NAME' => 'Заявки на закупку (автосервис)'],
                'en' => ['NAME' => 'Purchase requests (autoservice)'],
            ],
        ]);

        $hlId = $helper->Hlblock()->getHlblockIdIfExists('OtusPurchaseRequest');

        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_PRODUCT_ID',
            'USER_TYPE_ID' => 'integer',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => ['ru' => 'ID товара (каталог)'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Товар'],
        ]);
        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_QUANTITY',
            'USER_TYPE_ID' => 'double',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => ['ru' => 'Количество'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Кол-во'],
        ]);
        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_STATUS',
            'USER_TYPE_ID' => 'string',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => ['ru' => 'Статус (NEW|DONE|REJECTED)'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Статус'],
        ]);
        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_IS_AUTO',
            'USER_TYPE_ID' => 'boolean',
            'EDIT_FORM_LABEL' => ['ru' => 'Создана автоматически'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Авто'],
        ]);
        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_CREATED_BY',
            'USER_TYPE_ID' => 'integer',
            'EDIT_FORM_LABEL' => ['ru' => 'Кто создал (ID пользователя)'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Автор'],
        ]);
        $helper->Hlblock()->saveField($hlId, [
            'FIELD_NAME' => 'UF_REJECT_REASON',
            'USER_TYPE_ID' => 'string',
            'EDIT_FORM_LABEL' => ['ru' => 'Причина отказа'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Отказ'],
        ]);

        Option::set('otus.autoservice', 'purchase_hlblock_id', (string)$hlId);

        if (Option::get('otus.autoservice', 'purchase_notify_user_ids', '') === '') {
            Option::set('otus.autoservice', 'purchase_notify_user_ids', '1');
        }
    }

    public function down(): void
    {
        $helper = $this->getHelperManager();
        $helper->Hlblock()->deleteHlblockIfExists('OtusPurchaseRequest');
        Option::delete('otus.autoservice', ['name' => 'purchase_hlblock_id']);
    }
}