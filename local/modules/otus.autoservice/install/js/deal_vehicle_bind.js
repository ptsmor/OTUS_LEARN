(function () {
    'use strict';

    /** Сборка: OTUS_DEAL_VEHICLE_BIND_V3_20261120 */
    const ACTION_LIST = 'otus:autoservice.dealgarage.listVehiclesForContact';
    const ACTION_DEAL_CONTACT = 'otus:autoservice.dealgarage.resolveDealContact';
    const BUILD = 'V3_20261120';

    let cachedContactId = 0;

    function findVehicleInput() {
        return document.querySelector('input[name="UF_VEHICLE_ID"]')
            || document.querySelector('input[name*="UF_VEHICLE_ID"]')
            || document.querySelector('[data-cid="UF_VEHICLE_ID"] input')
            || document.querySelector('[data-name="UF_VEHICLE_ID"] input')
            || document.querySelector('.ui-ctl[data-name="UF_VEHICLE_ID"] input');
    }

    function parseContactId(value) {
        const id = parseInt(String(value || ''), 10);
        return id > 0 ? id : 0;
    }

    function readContactFromSelectorUi() {
        const selectorItems = document.querySelectorAll(
            '.ui-entity-selector-item[data-entity-id],'
            + '.main-ui-square-item[data-id],'
            + '[data-cid="CLIENT"] [data-entity-id],'
            + '[data-cid="CLIENT"] [data-id]'
        );
        for (let i = 0; i < selectorItems.length; i++) {
            const node = selectorItems[i];
            const typeHint = (node.getAttribute('data-entity-type-id')
                || node.getAttribute('data-type')
                || node.getAttribute('data-entity-type')
                || '').toString();
            if (typeHint && typeHint !== '3' && typeHint !== 'CONTACT' && typeHint !== 'contact') {
                continue;
            }
            const id = parseContactId(
                node.getAttribute('data-entity-id')
                || node.getAttribute('data-id')
                || node.getAttribute('data-item-id')
            );
            if (id > 0) {
                return id;
            }
        }
        return 0;
    }

    function findContactIdFromEditor() {
        try {
            if (typeof BX === 'undefined' || !BX.Crm || !BX.Crm.EntityEditor) {
                return 0;
            }

            const editor = BX.Crm.EntityEditor.getDefault();
            if (!editor) {
                return 0;
            }

            if (typeof editor.getControlById === 'function') {
                const clientControl = editor.getControlById('CLIENT');
                if (clientControl) {
                    if (typeof clientControl.getContactIds === 'function') {
                        const ids = clientControl.getContactIds();
                        if (ids && ids.length) {
                            return parseContactId(ids[0]);
                        }
                    }
                    if (typeof clientControl.getItems === 'function') {
                        const items = clientControl.getItems();
                        if (items && items.length) {
                            for (let i = 0; i < items.length; i++) {
                                const item = items[i];
                                const typeId = parseInt(item.entityTypeId || item.ENTITY_TYPE_ID || item.typeId, 10);
                                if (typeId === 3 || typeId === 0) {
                                    const id = parseContactId(item.entityId || item.ENTITY_ID || item.id);
                                    if (id > 0) {
                                        return id;
                                    }
                                }
                            }
                        }
                    }
                    if (typeof clientControl.getEntityList === 'function') {
                        const list = clientControl.getEntityList();
                        if (list && list.length) {
                            for (let j = 0; j < list.length; j++) {
                                const ent = list[j];
                                if (parseInt(ent.typeId || ent.entityTypeId, 10) === 3) {
                                    const cid = parseContactId(ent.id || ent.entityId);
                                    if (cid > 0) {
                                        return cid;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (typeof editor.getModel === 'function') {
                const model = editor.getModel();
                if (model && typeof model.getField === 'function') {
                    const direct = model.getField('CONTACT_ID');
                    if (direct && typeof direct.getValue === 'function') {
                        const id = parseContactId(direct.getValue());
                        if (id > 0) {
                            return id;
                        }
                    }

                    const clientField = model.getField('CLIENT');
                    if (clientField && typeof clientField.getValue === 'function') {
                        const data = clientField.getValue();
                        if (data) {
                            if (Array.isArray(data.CONTACTS)) {
                                for (let k = 0; k < data.CONTACTS.length; k++) {
                                    const cid = parseContactId(data.CONTACTS[k].id || data.CONTACTS[k].ID);
                                    if (cid > 0) {
                                        return cid;
                                    }
                                }
                            }
                            if (data.CONTACT_DATA && typeof data.CONTACT_DATA === 'object') {
                                const keys = Object.keys(data.CONTACT_DATA);
                                for (let m = 0; m < keys.length; m++) {
                                    const cid = parseContactId(keys[m]);
                                    if (cid > 0) {
                                        return cid;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (e) {
            // ignore
        }

        return 0;
    }

    function findContactIdFromDom() {
        const urlMatch = window.location.href.match(/contact_id=(\d+)/i);
        if (urlMatch) {
            return parseContactId(urlMatch[1]);
        }

        const fromSelector = readContactFromSelectorUi();
        if (fromSelector > 0) {
            return fromSelector;
        }

        const selectors = [
            'a[href*="/crm/contact/details/"]',
            'a[href*="/crm/type/3/details/"]',
            'a[href*="/crm/type/contact/details/"]',
        ];
        for (let s = 0; s < selectors.length; s++) {
            const links = document.querySelectorAll(selectors[s]);
            for (let i = 0; i < links.length; i++) {
                const href = links[i].getAttribute('href') || '';
                const m = href.match(/\/details\/(\d+)/i);
                if (m) {
                    return parseContactId(m[1]);
                }
            }
        }

        const clientRoot = document.querySelector('[data-cid="CLIENT"]')
            || document.querySelector('.crm-entity-widget-content-block-client');
        if (clientRoot) {
            const innerLinks = clientRoot.querySelectorAll('a[href*="/details/"]');
            for (let j = 0; j < innerLinks.length; j++) {
                const href = innerLinks[j].getAttribute('href') || '';
                const m2 = href.match(/\/details\/(\d+)/i);
                if (m2) {
                    return parseContactId(m2[1]);
                }
            }
        }

        const hidden = document.querySelector('input[name="CONTACT_ID"]')
            || document.querySelector('input[name="contact_id"]');
        if (hidden && hidden.value) {
            return parseContactId(hidden.value);
        }

        return findContactIdFromEditor();
    }

    function refreshContactCache() {
        const id = findContactIdFromDom();
        if (id > 0) {
            cachedContactId = id;
        }
    }

    function bindCrmEditorEvents() {
        if (typeof BX === 'undefined') {
            return;
        }

        const attach = function (editor) {
            if (!editor || editor.__otusDealGarageBound) {
                return;
            }
            editor.__otusDealGarageBound = true;

            if (typeof BX.addCustomEvent === 'function') {
                BX.addCustomEvent(editor, 'onInit', refreshContactCache);
                BX.addCustomEvent(editor, 'onControlChange', function (controlId) {
                    if (controlId === 'CLIENT' || controlId === 'CONTACT_ID') {
                        setTimeout(refreshContactCache, 50);
                    }
                });
                BX.addCustomEvent(editor, 'onAfterSave', refreshContactCache);
            }

            if (typeof editor.getControlById === 'function') {
                const clientControl = editor.getControlById('CLIENT');
                if (clientControl && typeof BX.addCustomEvent === 'function') {
                    BX.addCustomEvent(clientControl, 'onChange', function () {
                        setTimeout(refreshContactCache, 50);
                    });
                }
            }
        };

        if (BX.Crm && BX.Crm.EntityEditor) {
            attach(BX.Crm.EntityEditor.getDefault());
        }

        if (typeof BX.addCustomEvent === 'function') {
            BX.addCustomEvent(window, 'BX.Crm.EntityEditor:onInitialize', function (editor) {
                attach(editor);
                refreshContactCache();
            });
        }
    }

    function getDealIdFromUrl() {
        const m = window.location.href.match(/\/crm\/deal\/details\/(\d+)/i);
        if (!m) {
            return 0;
        }
        const id = parseContactId(m[1]);
        return id > 0 ? id : 0;
    }

    function resolveFromDealAjax(callback) {
        const dealId = getDealIdFromUrl();
        if (dealId > 0 && BX && BX.ajax && BX.ajax.runAction) {
            BX.ajax.runAction(ACTION_DEAL_CONTACT, { data: { dealId: dealId } })
                .then(function (response) {
                    const id = response.data && response.data.contactId
                        ? parseContactId(response.data.contactId)
                        : 0;
                    if (id > 0) {
                        cachedContactId = id;
                    }
                    callback(id);
                })
                .catch(function () {
                    callback(0);
                });
            return;
        }
        callback(0);
    }

    function resolveContactId(callback) {
        function attempt(attemptsLeft) {
            refreshContactCache();
            let contactId = cachedContactId || findContactIdFromDom();
            if (contactId > 0) {
                callback(contactId);
                return;
            }
            if (attemptsLeft <= 0) {
                resolveFromDealAjax(callback);
                return;
            }
            setTimeout(function () {
                attempt(attemptsLeft - 1);
            }, 100);
        }
        attempt(12);
    }

    function getHelperParent(vehicleInput) {
        return vehicleInput.closest('.ui-entity-editor-content-block')
            || vehicleInput.closest('.crm-entity-widget-content-block')
            || vehicleInput.closest('[data-cid="UF_VEHICLE_ID"]')
            || vehicleInput.parentNode;
    }

    function ensureHelper(vehicleInput) {
        const wrapId = 'otus-deal-vehicle-helper';
        const parent = getHelperParent(vehicleInput);
        if (!parent) {
            return null;
        }

        let wrap = parent.querySelector('#' + wrapId);
        const orphan = document.getElementById(wrapId);
        if (orphan && orphan !== wrap) {
            orphan.remove();
            wrap = null;
        }

        if (wrap) {
            const oldBtn = wrap.querySelector('button[data-otus-build]');
            if (oldBtn && oldBtn.getAttribute('data-otus-build') !== BUILD) {
                wrap.remove();
                wrap = null;
            }
        }

        if (wrap) {
            return wrap;
        }

        wrap = document.createElement('div');
        wrap.id = wrapId;
        wrap.style.marginTop = '8px';
        wrap.style.display = 'flex';
        wrap.style.flexWrap = 'wrap';
        wrap.style.gap = '8px';
        wrap.style.alignItems = 'center';

        const hint = document.createElement('span');
        hint.style.fontSize = '12px';
        hint.style.color = '#828b95';
        hint.setAttribute('data-otus-contact-hint', '1');
        hint.textContent = '';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ui-btn ui-btn-light-border ui-btn-sm';
        btn.textContent = 'Из гаража клиента';
        btn.setAttribute('data-otus-build', BUILD);

        const select = document.createElement('select');
        select.style.minWidth = '280px';
        select.style.display = 'none';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = '— выберите автомобиль —';
        select.appendChild(opt0);

        function updateHint() {
            refreshContactCache();
            if (cachedContactId > 0) {
                hint.textContent = 'Контакт #' + cachedContactId;
            } else {
                hint.textContent = 'Укажите клиента в блоке «Клиент»';
            }
        }

        btn.addEventListener('click', function () {
            resolveContactId(function (contactId) {
                if (!contactId) {
                    alert('Не найден контакт. Выберите клиента в блоке «Клиент» над полем авто '
                        + 'и подождите секунду, затем нажмите снова.');
                    return;
                }
                updateHint();

                btn.disabled = true;
                BX.ajax.runAction(ACTION_LIST, { data: { contactId: contactId } })
                    .then(function (response) {
                        btn.disabled = false;
                        if (!response.data || !response.data.success) {
                            const err = (response.data && response.data.errors) || ['Не удалось загрузить гараж'];
                            alert(err.join('\n'));
                            return;
                        }

                        while (select.options.length > 1) {
                            select.remove(1);
                        }

                        const vehicles = response.data.vehicles || [];
                        if (!vehicles.length) {
                            alert('У контакта нет автомобилей в гараже (вкладка «Гараж» у контакта).');
                            select.style.display = 'none';
                            return;
                        }

                        vehicles.forEach(function (v) {
                            const opt = document.createElement('option');
                            opt.value = String(v.ID);
                            opt.textContent = v.LABEL;
                            select.appendChild(opt);
                        });

                        select.style.display = 'inline-block';
                    })
                    .catch(function () {
                        btn.disabled = false;
                        alert('Ошибка запроса гаража. Проверьте модуль и сбросьте кеш.');
                    });
            });
        });

        select.addEventListener('change', function () {
            const id = parseContactId(select.value);
            if (id > 0 && vehicleInput) {
                vehicleInput.value = String(id);
                vehicleInput.dispatchEvent(new Event('change', { bubbles: true }));
                vehicleInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        wrap.appendChild(hint);
        wrap.appendChild(btn);
        wrap.appendChild(select);
        parent.appendChild(wrap);
        updateHint();

        return wrap;
    }

    function init() {
        const vehicleInput = findVehicleInput();
        if (!vehicleInput) {
            return;
        }
        ensureHelper(vehicleInput);
        refreshContactCache();
    }

    function watchDom() {
        if (typeof MutationObserver === 'undefined') {
            return;
        }
        let debounceTimer = null;
        const observer = new MutationObserver(function (mutations) {
            for (let i = 0; i < mutations.length; i++) {
                const t = mutations[i].target;
                if (t && t.closest && t.closest('[data-cid="CLIENT"]')) {
                    setTimeout(refreshContactCache, 80);
                    break;
                }
            }
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(init, 300);
        });
        observer.observe(document.body, { childList: true, subtree: true, attributes: true });
    }

    if (window.BX && BX.ready) {
        BX.ready(function () {
            bindCrmEditorEvents();
            init();
            watchDom();
            setInterval(function () {
                bindCrmEditorEvents();
                refreshContactCache();
            }, 1000);
            let tries = 0;
            const timer = setInterval(function () {
                tries++;
                init();
                if (tries > 60) {
                    clearInterval(timer);
                }
            }, 500);
        });
    }
})();