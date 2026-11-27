<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
/** @var OtusContactGarageComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

$contactId = (int)$arResult['CONTACT_ID'];
$signedParams = $component->getSignedParameters();
$modelsByBrand = $arResult['MODELS_BY_BRAND'] ?? [];

/** @global CMain $APPLICATION */
$APPLICATION->SetAdditionalCSS($templateFolder . '/style.css');
?>

<div class="otus-garage" id="otus-garage-<?= $contactId ?>">
    <?php if (!$arResult['DIRECTORY_READY']): ?>
        <div class="ui-alert ui-alert-icon-warning ui-alert-warning">
            <span class="ui-alert-message">
                <?= Loc::getMessage('OTUS_AUTO_GARAGE_NO_HL'); ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if ($arResult['CAN_EDIT'] && $arResult['DIRECTORY_READY']): ?>
        <form class="otus-garage-form" data-role="garage-add-form">
            <h3><?= Loc::getMessage('OTUS_AUTO_GARAGE_ADD'); ?></h3>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_BRAND'); ?></label>
                <select name="brandId" data-role="brand-select" required>
                    <option value=""><?= Loc::getMessage('OTUS_AUTO_GARAGE_SELECT'); ?></option>
                    <?php foreach ($arResult['BRANDS'] as $id => $name): ?>
                        <option value="<?= (int)$id ?>"><?= htmlspecialcharsbx($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_MODEL'); ?></label>
                <select name="modelId" data-role="model-select" required disabled>
                    <option value=""><?= Loc::getMessage('OTUS_AUTO_GARAGE_SELECT_BRAND_FIRST'); ?></option>
                </select>
            </div>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_PLATE'); ?></label>
                <input type="text" name="plateNumber" maxlength="20" required>
            </div>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_YEAR'); ?></label>
                <input type="number" name="year" min="1980" max="2100">
            </div>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_COLOR'); ?></label>
                <input type="text" name="color" maxlength="50">
            </div>
            <div class="otus-garage-form-row">
                <label><?= Loc::getMessage('OTUS_AUTO_GARAGE_MILEAGE'); ?></label>
                <input type="number" name="mileage" min="0" value="0">
            </div>
            <button type="submit" class="ui-btn ui-btn-success ui-btn-sm"><?= Loc::getMessage('OTUS_AUTO_GARAGE_SAVE'); ?></button>
        </form>
    <?php endif; ?>

    <table class="otus-garage-table">
        <thead>
        <tr>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_BRAND'); ?></th>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_MODEL'); ?></th>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_PLATE'); ?></th>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_YEAR'); ?></th>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_COLOR'); ?></th>
            <th><?= Loc::getMessage('OTUS_AUTO_GARAGE_COL_MILEAGE'); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody data-role="garage-vehicle-list">
        <?php include __DIR__ . '/vehicle_rows.php'; ?>
        </tbody>
    </table>
</div>

<div id="otus-garage-modal-<?= $contactId ?>" class="otus-garage-modal" hidden>
    <div class="otus-garage-modal__backdrop" data-role="modal-close"></div>
    <div class="otus-garage-modal__content">
        <button type="button" class="otus-garage-modal__close" data-role="modal-close">&times;</button>
        <h3 id="otus-garage-modal-title-<?= $contactId ?>"></h3>
        <div id="otus-garage-modal-body-<?= $contactId ?>"></div>
    </div>
</div>

<script>
    BX.ready(function () {
        const root = document.getElementById('otus-garage-<?= $contactId ?>');
        if (!root) {
            return;
        }

        const contactId = <?= $contactId ?>;
        const signedParameters = '<?= CUtil::JSEscape($signedParams) ?>';
        const modelsByBrand = <?= Json::encode($modelsByBrand) ?>;
        const msgSelect = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_GARAGE_SELECT')) ?>';
        const msgSelectBrandFirst = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_GARAGE_SELECT_BRAND_FIRST')) ?>';
        const msgEmpty = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_GARAGE_EMPTY')) ?>';
        const msgHistory = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_GARAGE_HISTORY')) ?>';
        const msgParts = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_PARTS')) ?>';
        const msgPartsEmpty = '<?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_PARTS_EMPTY')) ?>';

        const brandSelect = root.querySelector('[data-role="brand-select"]');
        const modelSelect = root.querySelector('[data-role="model-select"]');
        const listBody = root.querySelector('[data-role="garage-vehicle-list"]');
        const modal = document.getElementById('otus-garage-modal-<?= $contactId ?>');
        const titleEl = document.getElementById('otus-garage-modal-title-<?= $contactId ?>');
        const bodyEl = document.getElementById('otus-garage-modal-body-<?= $contactId ?>');

        function closeHistoryModal() {
            if (modal) {
                modal.hidden = true;
            }
        }

        function ensureModalInBody() {
            if (modal && modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target.closest('[data-role="modal-close"]')) {
                    event.preventDefault();
                    closeHistoryModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal && !modal.hidden) {
                    closeHistoryModal();
                }
            });
        }

        function fillModels(brandId) {
            if (!modelSelect) {
                return;
            }
            modelSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            if (!brandId) {
                placeholder.textContent = msgSelectBrandFirst;
                modelSelect.appendChild(placeholder);
                modelSelect.disabled = true;
                return;
            }
            placeholder.textContent = msgSelect;
            modelSelect.appendChild(placeholder);
            const models = modelsByBrand[brandId] || modelsByBrand[String(brandId)] || {};
            Object.keys(models).forEach(function (modelId) {
                const option = document.createElement('option');
                option.value = modelId;
                option.textContent = models[modelId];
                modelSelect.appendChild(option);
            });
            modelSelect.disabled = Object.keys(models).length === 0;
        }

        if (brandSelect) {
            brandSelect.addEventListener('change', function () {
                fillModels(parseInt(brandSelect.value, 10) || 0);
            });
        }

        function escapeHtml(text) {
            return BX.util.htmlspecialchars(String(text || ''));
        }

        function renderVehicles(vehicles) {
            if (!listBody) {
                return;
            }
            if (!vehicles || !vehicles.length) {
                listBody.innerHTML = '<tr><td colspan="7">' + escapeHtml(msgEmpty) + '</td></tr>';
                return;
            }
            let html = '';
            vehicles.forEach(function (vehicle) {
                html += '<tr>'
                    + '<td>' + escapeHtml(vehicle.BRAND_NAME) + '</td>'
                    + '<td>' + escapeHtml(vehicle.MODEL_NAME) + '</td>'
                    + '<td>' + escapeHtml(vehicle.PLATE_NUMBER) + '</td>'
                    + '<td>' + (parseInt(vehicle.YEAR, 10) || '—') + '</td>'
                    + '<td>' + escapeHtml(vehicle.COLOR) + '</td>'
                    + '<td>' + (parseInt(vehicle.MILEAGE, 10) || 0) + '</td>'
                    + '<td><button type="button" class="ui-btn ui-btn-link" data-role="garage-history"'
                    + ' data-vehicle-id="' + parseInt(vehicle.ID, 10) + '"'
                    + ' data-title="' + escapeHtml(vehicle.TITLE) + '">' + escapeHtml(msgHistory) + '</button></td>'
                    + '</tr>';
            });
            listBody.innerHTML = html;
        }

        function showErrors(response) {
            const errors = [];
            if (response.data && response.data.errors) {
                errors.push.apply(errors, response.data.errors);
            }
            if (response.errors && response.errors.length) {
                response.errors.forEach(function (e) {
                    errors.push(e.message || String(e));
                });
            }
            if (!errors.length) {
                errors.push('Ошибка сохранения');
            }
            alert(errors.join('\n'));
        }

        const form = root.querySelector('[data-role="garage-add-form"]');
        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const data = new FormData(form);
                const brandId = parseInt(data.get('brandId'), 10);
                const modelId = parseInt(data.get('modelId'), 10);
                if (!brandId || !modelId) {
                    alert(msgSelectBrandFirst);
                    return;
                }
                BX.ajax.runComponentAction('otus:contact.garage', 'addVehicle', {
                    mode: 'class',
                    signedParameters: signedParameters,
                    data: {
                        contactId: contactId,
                        brandId: brandId,
                        modelId: modelId,
                        plateNumber: String(data.get('plateNumber') || ''),
                        year: parseInt(data.get('year') || 0, 10),
                        color: String(data.get('color') || ''),
                        mileage: parseInt(data.get('mileage') || 0, 10),
                    },
                }).then(function (response) {
                    if (response.status === 'success' && response.data && response.data.success) {
                        renderVehicles(response.data.vehicles || []);
                        form.reset();
                        fillModels(0);
                    } else {
                        showErrors(response);
                    }
                }).catch(function (response) {
                    showErrors(response || {});
                });
            });
        }

        function openVehicleHistory(vehicleId, title) {
            BX.ajax.runComponentAction('otus:contact.garage', 'getVehicleDeals', {
                mode: 'class',
                signedParameters: signedParameters,
                data: {vehicleId: vehicleId, contactId: contactId},
            }).then(function (response) {
                if (response.status === 'success' && response.data && response.data.success) {
                    showHistoryModal(title, response.data.deals || []);
                    return;
                }
                const errors = (response.data && response.data.errors) || (response.errors || []).map(function (e) {
                    return e.message || e;
                });
                alert(errors.length ? errors.join('\n') : 'Не удалось загрузить историю');
            }).catch(function (response) {
                const errors = response && response.errors ? response.errors.map(function (e) {
                    return e.message || e;
                }) : [];
                alert(errors.length ? errors.join('\n') : 'Не удалось загрузить историю');
            });
        }

        function showHistoryModal(title, deals) {
            ensureModalInBody();
            titleEl.textContent = title;
            if (!deals.length) {
                bodyEl.innerHTML = '<p><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_GARAGE_NO_DEALS')) ?></p>';
            } else {
                let html = '<table class="otus-garage-table"><thead><tr>'
                    + '<th><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_TITLE')) ?></th>'
                    + '<th><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_DATE')) ?></th>'
                    + '<th><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_STAGE')) ?></th>'
                    + '<th><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_ASSIGNED')) ?></th>'
                    + '<th><?= CUtil::JSEscape(Loc::getMessage('OTUS_AUTO_DEAL_SUM')) ?></th>'
                    + '</tr></thead><tbody>';
                deals.forEach(function (deal) {
                    html += '<tr>'
                        + '<td>' + escapeHtml(deal.TITLE) + '</td>'
                        + '<td>' + escapeHtml(deal.DATE_CREATE) + '</td>'
                        + '<td>' + escapeHtml(deal.STAGE_NAME || deal.STAGE_ID) + '</td>'
                        + '<td>' + escapeHtml(deal.ASSIGNED_NAME) + '</td>'
                        + '<td>' + (deal.OPPORTUNITY || '') + '</td>'
                        + '</tr>';
                    html += '<tr class="otus-garage-deal-parts-row"><td colspan="5">';
                    html += '<div class="otus-garage-deal-parts"><strong>' + escapeHtml(msgParts) + ':</strong> ';
                    if (deal.PARTS && deal.PARTS.length) {
                        html += '<ul class="otus-garage-deal-parts-list">';
                        deal.PARTS.forEach(function (part) {
                            html += '<li>' + escapeHtml(part.NAME)
                                + ' — ' + (part.QUANTITY || 0)
                                + ' × ' + (part.PRICE || 0) + '</li>';
                        });
                        html += '</ul>';
                    } else {
                        html += '<span>' + escapeHtml(msgPartsEmpty) + '</span>';
                    }
                    html += '</div></td></tr>';
                });
                html += '</tbody></table>';
                bodyEl.innerHTML = html;
            }
            modal.hidden = false;
        }

        root.addEventListener('click', function (event) {
            const historyBtn = event.target.closest('[data-role="garage-history"]');
            if (historyBtn) {
                event.preventDefault();
                openVehicleHistory(
                    parseInt(historyBtn.getAttribute('data-vehicle-id'), 10),
                    historyBtn.getAttribute('data-title') || ''
                );
                return;
            }
        });
    });
</script>