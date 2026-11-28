<?php

namespace Sprint\Migration;

/**
 * Справочники марок/моделей и поле сделки UF_VEHICLE_ID.
 */
class Version20261113193000_AutoserviceDirectories extends Version
{
    protected $author = 'admin';

    protected $description = 'Автосервис: HL марки и модели, UF_VEHICLE_ID у сделки';

    protected $moduleVersion = '5.16.0';

    public function up(): void
    {
        $helper = $this->getHelperManager();

        $helper->Hlblock()->saveHlblock([
            'NAME' => 'OtusAutoBrand',
            'TABLE_NAME' => 'otus_auto_brand',
            'LANG' => [
                'ru' => ['NAME' => 'Марки автомобилей (проект)'],
                'en' => ['NAME' => 'Car brands (project)'],
            ],
        ]);

        $brandHlId = $helper->Hlblock()->getHlblockIdIfExists('OtusAutoBrand');
        $helper->Hlblock()->saveField($brandHlId, [
            'FIELD_NAME' => 'UF_NAME',
            'USER_TYPE_ID' => 'string',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => ['ru' => 'Название марки'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Марка'],
        ]);

        $helper->Hlblock()->saveHlblock([
            'NAME' => 'OtusAutoModel',
            'TABLE_NAME' => 'otus_auto_model',
            'LANG' => [
                'ru' => ['NAME' => 'Модели автомобилей (проект)'],
                'en' => ['NAME' => 'Car models (project)'],
            ],
        ]);

        $modelHlId = $helper->Hlblock()->getHlblockIdIfExists('OtusAutoModel');
        $helper->Hlblock()->saveField($modelHlId, [
            'FIELD_NAME' => 'UF_NAME',
            'USER_TYPE_ID' => 'string',
            'MANDATORY' => 'Y',
            'EDIT_FORM_LABEL' => ['ru' => 'Модель'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Модель'],
        ]);
        $helper->Hlblock()->saveField($modelHlId, [
            'FIELD_NAME' => 'UF_BRAND',
            'USER_TYPE_ID' => 'hlblock',
            'MANDATORY' => 'Y',
            'SETTINGS' => [
                'HLBLOCK_ID' => $brandHlId,
                'HLFIELD_ID' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Марка'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Марка'],
        ]);

        $this->seedDirectories($helper);

        $helper->UserTypeEntity()->saveUserTypeEntity([
            'ENTITY_ID' => 'CRM_DEAL',
            'FIELD_NAME' => 'UF_VEHICLE_ID',
            'USER_TYPE_ID' => 'integer',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => ['ru' => 'Автомобиль (гараж)'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Автомобиль'],
            'LIST_FILTER_LABEL' => ['ru' => 'Автомобиль'],
        ]);
    }

    private function seedDirectories($helper): void
    {
        $brands = [
            'Toyota' => ['Camry', 'Corolla', 'RAV4'],
            'Lada' => ['Vesta', 'Granta'],
            'Hyundai' => ['Solaris', 'Tucson'],
            'Kia' => ['Rio', 'Sportage'],
        ];

        foreach ($brands as $brandName => $models) {
            $brandId = $helper->Hlblock()->addElement('OtusAutoBrand', ['UF_NAME' => $brandName]);
            foreach ($models as $modelName) {
                $helper->Hlblock()->addElement('OtusAutoModel', [
                    'UF_NAME' => $modelName,
                    'UF_BRAND' => $brandId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $helper = $this->getHelperManager();
        $helper->Hlblock()->deleteHlblockIfExists('OtusAutoModel');
        $helper->Hlblock()->deleteHlblockIfExists('OtusAutoBrand');
        $helper->UserTypeEntity()->deleteUserTypeEntityIfExists('CRM_DEAL', 'UF_VEHICLE_ID');
    }
}
