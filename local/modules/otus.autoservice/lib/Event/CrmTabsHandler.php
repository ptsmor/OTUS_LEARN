<?php

namespace Otus\Autoservice\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Otus\Autoservice\Service\GarageService;

Loc::loadMessages(__FILE__);

/**
 * Вкладка «Гараж» в карточке контакта (событие crm::onEntityDetailsTabsInitialized).
 */
class CrmTabsHandler
{
    public static function onTabsInit(Event $event): ?EventResult
    {
        $params = $event->getParameters();
        $entityTypeId = (int)($params['entityTypeID'] ?? 0);
        $entityId = (int)($params['entityID'] ?? 0);
        $tabs = $params['tabs'] ?? [];

        if ($entityTypeId !== \CCrmOwnerType::Contact || $entityId <= 0 || !is_array($tabs)) {
            return null;
        }

        $garage = new GarageService();
        if (!$garage->canViewGarage($entityId)) {
            return null;
        }

        $tabs[] = [
            'id' => 'tab_otus_garage',
            'name' => Loc::getMessage('OTUS_AUTO_TAB_GARAGE'),
            'loader' => [
                'serviceUrl' => '/local/components/otus/contact.garage/lazyload.ajax.php?site='
                    . SITE_ID
                    . '&'
                    . bitrix_sessid_get(),
                'componentData' => [
                    'template' => '',
                    'params' => [
                        'CONTACT_ID' => $entityId,
                        'TAB_ID' => 'tab_otus_garage',
                    ],
                ],
            ],
        ];

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}
