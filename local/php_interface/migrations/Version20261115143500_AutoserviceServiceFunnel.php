<?php

namespace Sprint\Migration;

use Otus\Autoservice\Install\ServiceDealCategoryInstaller;

/**
 * Направление CRM «Сервисное обслуживание» и стадии воронки.
 */
class Version20261115143500_AutoserviceServiceFunnel extends Version
{
    protected $author = 'admin';

    protected $description = 'Автосервис: воронка CRM «Сервисное обслуживание»';

    protected $moduleVersion = '5.16.0';

    public function up(): void
    {
        if (!\Bitrix\Main\Loader::includeModule('otus.autoservice')) {
            $this->outError('Установите модуль otus.autoservice перед миграцией воронки');

            return;
        }

        $installer = new ServiceDealCategoryInstaller();
        $categoryId = $installer->install();
        $this->outSuccess('Направление сделок ID=' . $categoryId . ' («' . ServiceDealCategoryInstaller::CATEGORY_NAME . '»)');

        $warning = $installer->getLastStageWarning();
        if ($warning !== null && $warning !== '') {
            $this->outWarning('Стадии воронки: ' . $warning);
            $this->out('Донастройте стадии вручную в CRM (см. README, раздел «Воронка вручную»).');
        }
    }

    public function down(): void
    {
        $this->out('Откат воронки вручную: CRM → Настройки → Направления сделок.');
    }
}