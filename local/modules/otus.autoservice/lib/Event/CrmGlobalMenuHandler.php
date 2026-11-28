<?php

namespace Otus\Autoservice\Event;

/**
 * Пункт «Склад и закупки» в левом меню CRM (переход на local/tools/autoservice).
 */
class CrmGlobalMenuHandler
{
    public static function onBuildGlobalMenu(array &$aGlobalMenu, array &$aModuleMenu): void
    {
        global $USER;
        if (!is_object($USER) || !$USER->IsAuthorized() || !$USER->IsAdmin()) {
            return;
        }

        $base = '/local/tools/autoservice/';
        $aModuleMenu[] = [
            'parent_menu' => 'global_menu_crm',
            'section' => 'otus_autoservice_tools',
            'sort' => 850,
            'text' => 'Склад и закупки',
            'title' => 'Учёт услуг и запчастей: заявки и остатки',
            'icon' => 'crm-sales-menu-icon',
            'page_icon' => 'crm_page_icon',
            'items_id' => 'menu_otus_autoservice_tools',
            'items' => [
                [
                    'text' => 'Новая заявка',
                    'url' => $base . 'purchase_manual.php',
                    'more_url' => [],
                ],
                [
                    'text' => 'Согласование',
                    'url' => $base . 'purchase_approve.php',
                    'more_url' => [],
                ],
                [
                    'text' => 'К закупщику',
                    'url' => $base . 'purchase_requests.php',
                    'more_url' => [],
                ],
                [
                    'text' => 'Остатки склада',
                    'url' => $base . 'stock_sync.php',
                    'more_url' => [],
                ],
            ],
        ];
    }
}