<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

foreach ($arResult['VEHICLES'] as $vehicle): ?>
    <tr>
        <td><?= htmlspecialcharsbx($vehicle['BRAND_NAME']) ?></td>
        <td><?= htmlspecialcharsbx($vehicle['MODEL_NAME']) ?></td>
        <td><?= htmlspecialcharsbx($vehicle['PLATE_NUMBER']) ?></td>
        <td><?= (int)$vehicle['YEAR'] ?: '—' ?></td>
        <td><?= htmlspecialcharsbx((string)$vehicle['COLOR']) ?></td>
        <td><?= (int)$vehicle['MILEAGE'] ?></td>
        <td>
            <button
                type="button"
                class="ui-btn ui-btn-link"
                data-role="garage-history"
                data-vehicle-id="<?= (int)$vehicle['ID'] ?>"
                data-title="<?= htmlspecialcharsbx($vehicle['TITLE']) ?>"
            ><?= Loc::getMessage('OTUS_AUTO_GARAGE_HISTORY'); ?></button>
        </td>
    </tr>
<?php endforeach; ?>
<?php if (empty($arResult['VEHICLES'])): ?>
    <tr>
        <td colspan="7"><?= Loc::getMessage('OTUS_AUTO_GARAGE_EMPTY'); ?></td>
    </tr>
<?php endif; ?>