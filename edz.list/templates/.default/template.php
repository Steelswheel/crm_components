<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

Loader::IncludeModule('crm');
$this->addExternalCss('/style.css');

global $APPLICATION;
global $USER;

$show_settings = false;

if ($USER->isAdmin()) {
    $show_settings = true;
}

CJSCore::Init(['ajax', 'window']);
?>

<div class="edz-list">
    <div class="edz-list-quick-filter">
        <?php $stageNum = 0 ?>
        <?php foreach ($arResult['MSK_STAGES'] as $stageId => $stageValue) { ?>
            <?php
                if ($stageId === 'C8:LOSE' || $stageId === 'C8:WON')
                    continue;

                if($arResult['FILTER_DATA']['STAGE_ID'] && is_array($arResult['FILTER_DATA']['STAGE_ID'])){
                    $activeClass = in_array($stageId, $arResult['FILTER_DATA']['STAGE_ID']) ? 'edz-list-quick-filter-item-active' : '';
                }


                $stageName = preg_replace('/^\d+ - /m', '', $stageValue['NAME']);
                $stageNum++;
            ?>
                <div class="edz-list-quick-filter-item <?= $activeClass ?>" data-stage-num="<?= $stageNum ?>" data-stage-id="<?= $stageId ?>">
                    <span class="edz-list-quick-filter-item-num">
                        <?= $stageNum ?>
                    </span>
                    <span class="edz-list-quick-filter-item-count">
                        <?= $arResult['DEALS_STAGES_COUNT'][$stageId] ?>
                    </span>
                    <span class="edz-list-quick-filter-item-label">
                        <?= $stageName ?>
                    </span>
                </div>
        <?php } ?>
    </div>
    <div class="edz-list-grid">
        <div class="edz-list-grid-wrap">
            <?php
                $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                    'FILTER_ID' => $arResult['GRID']['ID'],
                    'GRID_ID' => $arResult['GRID']['ID'],
                    'FILTER' => $arResult['GRID']['FILTER'],
                    'ENABLE_LIVE_SEARCH' => false,
                    'ENABLE_LABEL' => true
                ]);
            ?>
            <div class="edz-list-buttons">
                <a href="/b/edz/?deal_id=" target="_blank" class="edz-list-button">
                    Новая заявка
                </a>
                <?php if ($USER->isAdmin() || (int)$USER->GetID() === 104) { ?>
                    <a href="#" class="edz-list-button" data-role="xls-download" data-filter='<?= json_encode($arResult['FILTER_ARRAY']) ?>'>
                        Выгрузить в XLS
                    </a>
                <?php } ?>
            </div>
        </div>
        <?php

            $APPLICATION->IncludeComponent(
                'bitrix:main.ui.grid',
                '',
                [
                    'GRID_ID' => $arResult['GRID']['ID'],
                    'COLUMNS' => $arResult['GRID']['COLUMNS'],
                    'ROWS' => $arResult['GRID']['ROWS'],
                    'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                    'SHOW_ROW_CHECKBOXES' => false,
                    'SHOW_GRID_SETTINGS_MENU' => false,
                    'SHOW_PAGINATION' => true,
                    'SHOW_PAGESIZE' => true,
                    'PAGE_SIZES' => [
                        ['NAME' => '10', 'VALUE' => '10'],
                        ['NAME' => '20', 'VALUE' => '20'],
                        ['NAME' => '50', 'VALUE' => '50'],
                        ['NAME' => '100', 'VALUE' => '100']
                    ],
                    'SHOW_SELECTED_COUNTER' => false,
                    'SHOW_TOTAL_COUNTER' => true,
                    'ACTION_PANEL' => [],
                    'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
                    'ALLOW_COLUMNS_SORT' => false,
                    'ALLOW_COLUMNS_RESIZE' => true,
                    'AJAX_MODE' => 'Y',
                    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
                    'SHOW_ROW_ACTIONS_MENU' => true,
                    'SHOW_NAVIGATION_PANEL' => true,
                    'SHOW_ACTION_PANEL' => true,
                    'ALLOW_HORIZONTAL_SCROLL' => true,
                    'ALLOW_CONTEXT_MENU' => true,
                    'ALLOW_SORT' => true,
                    'ALLOW_PIN_HEADER' => true,
                    'AJAX_OPTION_HISTORY' => 'N'
                ]
            );
        ?>
    </div>
</div>

<?php
    if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
        echo "<script src='/local/components/vaganov/dist/main.bundle.js'></script>";
    } else {
        $str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/local/components/vaganov/dist/main.html');
        $re = '/<head>(.+)<\/head>.+(<script .+><\/script>)/m';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $vueLink = $matches[0][1];
        $vueScript = $matches[0][2];
        echo "$vueLink";
        echo "$vueScript";
    }
?>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    class Handler {
        constructor() {
            this.ths = Array.from(document.querySelector('#edz-list_table').querySelectorAll('th'));
            this.solutionHiddenIndexes = [];
            this.financeHiddenIndexes = [];
            this.solutionState = false;
            this.financeState = false;
            this.emails = document.querySelector('#edz-list_table').querySelectorAll('[data-role="partner-widget-email"]');
            this.phones = document.querySelector('#edz-list_table').querySelectorAll('[data-role="partner-widget-phone"]');
        }

        openContacts() {
            let wrappers = document.querySelectorAll('.partner-widget-contacts-wrapper');

            wrappers.forEach(wrapper => {
                let icon = wrapper.querySelector('.partner-widget-icon');

                icon.removeEventListener('click', this.openContactsHandler);
                icon.addEventListener('click', this.openContactsHandler);
            });

            document.removeEventListener('click', this.openContactsDocumentHandler);
            document.addEventListener('click', this.openContactsDocumentHandler);
        }

        openContactsDocumentHandler(event) {
            let wrappers = document.querySelectorAll('.partner-widget-contacts-wrapper');

            wrappers.forEach(wrapper => {
                let icon = wrapper.querySelector('.partner-widget-icon');
                let block = wrapper.querySelector('div');

                if (event.target === icon || wrapper.contains(event.target)) {
                    return false;
                } else {
                    block.dataset.active = 'false';
                }
            });
        }

        openContactsHandler(event) {
            let wrappers = document.querySelectorAll('.partner-widget-contacts-wrapper');

            wrappers.forEach(wrapper => {
                let block = wrapper.querySelector('div');

                if (block !== event.currentTarget.nextElementSibling) {
                    block.dataset.active = 'false';
                }
            });

            let block = event.currentTarget.nextElementSibling;

            block.dataset.active = block.dataset.active === 'false';
        }

        setPhones() {
            if (this.phones.length > 0) {
                this.phones.forEach(phone => {
                    phone.removeEventListener('click', this.phonesHandler);
                    phone.addEventListener('click', this.phonesHandler);
                });
            }
        }

        phonesHandler(event) {
            if (typeof(BXIM) !== 'undefined') {
                BXIM.phoneTo(
                    event.currentTarget.href,
                    {
                        'ENTITY_TYPE_NAME': 'CONTACT',
                        'ENTITY_ID': event.currentTarget.dataset.contact,
                        'AUTO_FOLD': true
                    }
                );

                return BX.PreventDefault(event);
            }
        }

        setEmails() {
            this.emails.forEach(email => {
                email.removeEventListener('click', this.emailsHandler);
                email.addEventListener('click', this.emailsHandler);
            });
        }

        emailsHandler(event) {
            event.preventDefault();
            let contactId = event.currentTarget.dataset.contact;
            let email = event.currentTarget.href;

            let url = `/bitrix/components/bitrix/crm.activity.planner/slider.php?site_id=s1&sessid=${BX.bitrix_sessid()}&context=contact-${contactId}&ajax_action=ACTIVITY_EDIT&activity_id=0&TYPE_ID=4&OWNER_ID=${contactId}&OWNER_TYPE=CONTACT&OWNER_PSID=0&FROM_ACTIVITY_ID=2&MESSAGE_TYPE=&__post_data_hash=${new Date().getTime()}&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER`;

            BX.SidePanel.Instance.open(url, {
                requestMethod: 'post',
                requestParams: {
                    COMMUNICATIONS: [{
                        OWNER_TYPE: 'CONTACT',
                        OWNER_ID: contactId,
                        TYPE: 'EMAIL',
                        VALUE: email
                    }]
                }
            });
        }

        addLinksToRows() {
            let tableRows = document.querySelector('#edz-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    let links = row.querySelectorAll('a[data-role="partner-widget-link"]');

                    if (links.length > 0) {
                        links.forEach(link => {
                            link.removeEventListener('click', this.addLinksToRowsIframeOpenHandler);
                            link.addEventListener('click', this.addLinksToRowsIframeOpenHandler);
                        });
                    }

                    row.removeEventListener('click', this.addLinksToRowsAddActiveClass);
                    row.addEventListener('click', this.addLinksToRowsAddActiveClass);
                });
            }
        }

        addLinksToRowsIframeOpenHandler(event) {
            event.preventDefault();

            BX.ready(function () {
                BX.SidePanel.Instance.open(event.currentTarget.href);
            });
        }

        addLinksToRowsAddActiveClass(event) {
            let tableRows = document.querySelector('#edz-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    row.classList.remove('edz-list-active-row');
                });

                if (event.currentTarget.classList.contains('edz-list-active-row')) {
                    event.currentTarget.classList.remove('edz-list-active-row');
                } else {
                    event.currentTarget.classList.add('edz-list-active-row');
                }
            }
        }

        getXls() {
            let button = document.querySelector('[data-role="xls-download"]');

            if (button) {
                button.removeEventListener('click', this.getXlsHandler);
                button.addEventListener('click', this.getXlsHandler);
            }
        }

        getXlsHandler(event) {
            event.preventDefault();

            let getStr = '';

            let filterData = JSON.parse(event.currentTarget.dataset.filter);

            for (let key in filterData) {
                if (filterData[key] === '') {
                    continue;
                }

                let data = '';

                if (typeof filterData[key] === 'object') {
                    let arr = filterData[key];

                    for (let i in arr) {
                        if (data.length > 0) {
                            data += ',';
                        }

                        data += arr[i];
                    }
                } else {
                    data = filterData[key];
                }

                if (getStr.length > 0) {
                    getStr += '&';
                }

                getStr += key + '=' + data;
            }

            let url = '/local/components/vaganov/edz.list/xls-export.php';

            if (getStr.length > 0) {
                url += '?' + getStr;
            }

            window.open(url);
        }

        openOrHideNote() {
            let cells = document.querySelector('#edz-list_table').querySelectorAll('.main-grid-cell-content');

            if (cells) {
                cells.forEach(cell => {
                    cell.dataset.open = 'false';

                    cell.removeEventListener('click', this.openOrHideNoteHandler);
                    cell.addEventListener('click', this.openOrHideNoteHandler);
                });
            }
        }

        openOrHideNoteHandler(event) {
            if (event.currentTarget.dataset.open === 'true') {
                event.currentTarget.dataset.open = 'false';
            } else {
                event.currentTarget.dataset.open = 'true';
            }
        }

        setQuickFilter() {
            function initFilter() {
                BX.addCustomEvent('BX.Main.Filter:apply', () => {
                    let filterManager = BX.Main.filterManager.getById('edz-list');
                    let filterFields = filterManager.getFilterFieldsValues();
                    let ids = Object.values(filterFields['STAGE_ID']);

                    document.querySelectorAll('.edz-list-quick-filter-item').forEach(el => {
                        el.classList.remove('edz-list-quick-filter-item-active');
                    });

                    ids.forEach(id => {
                        let el = document.querySelector('.edz-list-quick-filter-item[data-stage-id="' + id + '"]');

                        if (el) {
                            el.classList.add('edz-list-quick-filter-item-active');
                        }
                    });

                    BX.ajax.runComponentAction('vaganov:edz.list', 'reloadQuickFilter', {
                        mode: 'class'
                    }).then(function (response) {
                        let data = response.data;

                        let stages = Object.keys(data);
                        let filterItems = document.querySelectorAll('.edz-list-quick-filter-item');

                        if (stages.length > 0) {
                            filterItems.forEach(item => {
                                if (stages.includes(item.dataset.stageId)) {
                                    item.querySelector('.edz-list-quick-filter-item-count').innerHTML = data[item.dataset.stageId];
                                } else {
                                    item.querySelector('.edz-list-quick-filter-item-count').innerHTML = 0;
                                }
                            });
                        } else {
                            filterItems.forEach(item => {
                                item.querySelector('.edz-list-quick-filter-item-count').innerHTML = 0;
                            });
                        }
                    });
                });
            }

            window.onload = initFilter;

            const elWrapperFilter = document.querySelector('.edz-list-quick-filter');
            let wrapperWidth = elWrapperFilter.offsetWidth;
            const elItems = document.querySelectorAll('.edz-list-quick-filter-item');
            const elLength = elItems.length;
            const marginWidth = 10;
            let itemWidth = (wrapperWidth - (elLength * marginWidth)) / elLength;
            const animateTime = 200;

            let setItemsEqual = (stageNum) => {
                let currentWidth = itemWidth;

                if (stageNum) {
                    let elCurrent = elWrapperFilter.querySelector('[data-stage-num="' + stageNum + '"]');
                    currentWidth = (wrapperWidth - elCurrent.scrollWidth - (elLength * marginWidth)) / (elLength - 1);
                }

                elItems.forEach(item => {
                    if (item.dataset.stageNum === stageNum) {
                        item.style.width = item.scrollWidth + 'px';
                        item.setAttribute('active', '');
                        item.querySelector('.edz-list-quick-filter-item-label').style.opacity = 100;
                    } else {
                        item.removeAttribute('active');
                        item.style.width = currentWidth + 'px';

                        setTimeout(function() {
                            if (!item.hasAttribute('active')) {
                                item.querySelector('.edz-list-quick-filter-item-label').style.opacity = 0;
                            }
                        }, animateTime);
                    }
                })
            }

            setItemsEqual();

            elItems.forEach(item => {
                item.addEventListener('click', () => {
                    let filterManager = BX.Main.filterManager.getById('edz-list');
                    let filterApi = filterManager.getApi();
                    let filterFields = filterManager.getFilterFieldsValues();
                    filterFields['STAGE_ID'] = {0: item.dataset.stageId}
                    filterApi.setFields(filterFields);
                    filterApi.apply()
                })
            })

            window.addEventListener('resize', () => {
                wrapperWidth = elWrapperFilter.offsetWidth;
                itemWidth = (wrapperWidth - (elLength * marginWidth)) / elLength;
                setItemsEqual();
            })


            elItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    setItemsEqual(item.dataset.stageNum);
                });
                item.addEventListener('mouseleave', () => {
                    setItemsEqual();
                });
            })
        }

        setHeaders() {
            const headers = document.querySelector('#edz-list_table').querySelectorAll('.main-grid-head-title');

            const headText = <?= json_encode([
                Loc::getMessage('EDZ_LIST_REQUEST_DATE_TIME_SHORT'),
                Loc::getMessage('EDZ_LIST_MANAGER'),
                Loc::getMessage('EDZ_LIST_PARTNER'),
                Loc::getMessage('EDZ_LIST_BORROWER'),
                Loc::getMessage('EDZ_LIST_STAGE'),
                Loc::getMessage('EDZ_LIST_CONTRACT_DATE_NUMBER'),
                Loc::getMessage('EDZ_LIST_SUM'),
                Loc::getMessage('EDZ_LIST_PAYMENTS'),
                Loc::getMessage('EDZ_LIST_PAY'),
                Loc::getMessage('EDZ_LIST_LOAN_REPAYMENT'),
                Loc::getMessage('EDZ_LIST_TASKS_ALL'),
                Loc::getMessage('EDZ_LIST_NOTE'),
                Loc::getMessage('EDZ_LIST_SOLUTION'),
                Loc::getMessage('EDZ_LIST_TRANCHE'),
                Loc::getMessage('EDZ_LIST_EMPLOYEE_COMMENT')
            ]) ?>;

            for (let i = 0; i < headers.length; i++) {
                headers[i].innerHTML = '';
                headers[i].insertAdjacentHTML('afterbegin', headText[i]);
            }

            let elGroup = {
                'name': 'КОМИТЕТ ПО ЗАЙМАМ',
                'items': [
                    {
                        label: 'EDZ_LIST_SOLUTION',
                        width: '100px'
                    },
                    {
                        label: 'EDZ_LIST_TRANCHE',
                        width: '49px'
                    },
                    {
                        label: 'EDZ_LIST_EMPLOYEE_COMMENT',
                        width: '166px'
                    }
                ]
            }

            groupStart(elGroup);

            function groupStart(elGroup) {
                let elMain = document.querySelector(`th[data-name=${elGroup.items[0].label}]`);

                if (!elMain.dataset.group) {
                    elMain.dataset.group = 1;

                    let htmlBottom = '';

                    elGroup.items.forEach((cell, index) => {
                        let el = document.querySelector(`th[data-name=${cell.label}]`);
                        let elName = document.querySelector(`th[data-name=${cell.label}] .main-grid-head-title`);

                        htmlBottom += `<div style="width: ${cell.width}">${elName.innerHTML}</div>`;

                        if (index !== 0) {
                            el.remove();
                        }
                    });

                    elMain.innerHTML = `<div class='edzGroupCrutch'>
                    <div class='edzGroupCrutch--top'>${elGroup.name}</div>
                    <div class='edzGroupCrutch--bottom'>${htmlBottom}</div></div>`;

                    elMain.colSpan = elGroup.items.length;
                }
            }

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 0);
        }

        setListVisible() {
            document.querySelector('.edz-list').style.visibility = 'visible';
        }

        setHeaderColumnsWidth() {
            let row = document.querySelectorAll('#edz-list_table thead tr th');

            row.forEach(i => {
                if (i.dataset.name) {
                    let w = (i.offsetWidth - 1) + 'px';
                    let column = document.querySelector(`.main-grid-fixed-bar.main-grid-fixed-top th[data-name='${i.dataset.name}']`);

                    column.style.width = w;
                    column.childNodes[0].style.width = w;
                }
            });
        }
    }

    let handler = new Handler;

    handler.setHeaders();
    handler.setQuickFilter();
    handler.openContacts();
    handler.getXls();
    handler.addLinksToRows();
    handler.openOrHideNote();
    handler.setPhones();
    handler.setEmails();

    BX.ready(function () {
        BX.addCustomEvent('BX.Main.grid:paramsUpdated', function() {
            window.mountedComponentVue();

            handler.setHeaderColumnsWidth();
        });

        BX.addCustomEvent("Grid::headerPinned", () => {
            let handler = new Handler;

            handler.setHeaderColumnsWidth();
        });

        BX.addCustomEvent('onAjaxSuccess', () => {
            let handler = new Handler;

            handler.setHeaders();
            handler.getXls();
            handler.openContacts();
            handler.addLinksToRows();
            handler.openOrHideNote();
            handler.setPhones();
            handler.setEmails();
            handler.setHeaderColumnsWidth();
        });
    });

    handler.setListVisible();

    function addComment(id) {
        let commentBlock = document.querySelector('#edz-list_table').querySelector(`tr[data-id="${id}"]`).querySelector('[data-role="edz-list-note"]');
        let comment = commentBlock.dataset.comment;

        if (comment === null) {
            comment = '';
        }

        let content = '<div class="edz-list-add-comment"><h2 class="edz-list-popup-title">Добавить примечание</h2><div class="edz-list-add-comment-all-comments">' + comment + '</div><textarea class="edz-list-add-comment-textarea"></textarea></div>';
        let popupId = 'edz-list-popup-comments' + Math.floor(Math.random() * 99999) + id;

        let popup = new BX.PopupWindow(popupId, null, {
            content: content,
            closeIcon: {
                right: '10px',
                top: '10px'
            },
            lightShadow : true,
            overlay: {
                backgroundColor: 'black',
                opacity: '80'
            },
            zIndex: 0,
            offsetLeft: 0,
            offsetTop: 0,
            draggable: false,
            darkMode: false,
            autoHide: true,
            buttons: [
                new BX.PopupWindowButton({
                    text: 'Сохранить',
                    className: 'edz-list-popup-add-button',
                    events: {
                        click: function(event) {
                            const textarea = document.querySelector('#' + popupId).querySelector('.edz-list-add-comment-textarea');
                            let comments = document.querySelector('#' + popupId).querySelector('.edz-list-add-comment-all-comments');

                            if (textarea.value.length > 0) {
                                event.target.classList.add('ui-btn', 'ui-btn-clock');

                                BX.ajax.runComponentAction('vaganov:edz.list', 'addComment', {
                                    mode: 'class',
                                    data: {
                                        text: textarea.value,
                                        dealId: id
                                    }
                                }).then(function (response) {
                                    let str = response.data;

                                    event.target.classList.remove('ui-btn', 'ui-btn-clock');

                                    comments.innerHTML += str;
                                    commentBlock.innerHTML += str;

                                    commentBlock.dataset.comment += str;

                                    textarea.value = '';
                                    textarea.innerHTML = '';
                                });
                            }

                            return false;
                        }
                    }
                })
            ]
        });

        popup.show();
    }

    function getTask(id, manager) {
        let data = new FormData();
        data.append('deal_id', id);

        fetch('/local/ajax/get_tasks.php', {
            method: 'POST',
            body: data
        })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            let content = '';

            if (data !== null) {
                content = '<table class="edz-list-popup-table"><thead><tr><th>ID задачи</th><th>Название задачи</th><th>Постановщик</th><th>Ответственный</th><th>Дата создания</th><th>Статус</th><th>Крайний срок</th></tr></thead><tbody>';

                data.forEach(task => {
                    content += '<tr><td class="edz-list-popup-table-link"><a target="_blank" href="/company/personal/user/' + manager + '/tasks/task/view/' + task[0] + '/">' + task[0] + '</a></td><td class="edz-list-popup-table-task">' + task[1] + '</td><td>' + task[2] + '</td><td>' + task[3] + '</td><td>' + task[4] + '</td><td>' + task[5] + '</td><td>' + task[7] + '</td></tr>';
                });
                content += '</tbody></table>';
            } else {
                content = '<div class="edz-list-popup-title">Нет задач</div>';
            }

            let popup = new BX.PopupWindow('edz-list-popup-' + Math.floor(Math.random() * 99999) + id, null, {
                content: content,
                closeIcon: {
                    right: '10px',
                    top: '10px'
                },
                lightShadow : true,
                overlay: {
                    backgroundColor: 'black', opacity: '80'
                },
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: false,
                darkMode: false,
                autoHide: true,
                buttons: [
                    new BX.PopupWindowButton({
                        text: 'Добавить задачу',
                        className: 'edz-list-popup-table-add-button',
                        events: {
                            click: function() {
                                let url = '/company/personal/user/' + manager + '/tasks/task/edit/0/?UF_CRM_TASK=D_' + id + '&TITLE=ЗАЙМ%20' + id + '%3A%20&TAGS=crm';
                                window.open(url, '_blank');
                            }
                        }
                    })
                ]
            });

            popup.show();
        });
    }
</script>