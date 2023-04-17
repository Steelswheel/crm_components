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

<div class="edp-list">
    <div class="edp-list-grid">
        <div class="edp-list-quick-filter">
            <?php $stageNum = 0 ?>
            <?php foreach ($arResult['EDP_STAGES'] as $stageId => $stageValue) { ?>
                <?php
                    if ($stageId === 'C10:LOSE' || $stageId === 'C10:WON')
                        continue;


                    $activeClass = is_array($arResult['FILTER_DATA']['STAGE_ID']) && in_array($stageId, $arResult['FILTER_DATA']['STAGE_ID'])
                        ? 'edp-list-quick-filter-item-active'
                        : '';
                    $stageName = preg_replace('/^\d+ - /m', '', $stageValue);
                    $stageNum++;
                ?>

                <div class="edp-list-quick-filter-item <?= $activeClass ?>" data-stage-num="<?= $stageNum ?>" data-stage-id="<?= $stageId ?>">
                    <span class="edp-list-quick-filter-item-num">
                        <?= $stageNum ?>
                    </span>
                    <span class="edp-list-quick-filter-item-count">
                        <?= $arResult['DEALS_STAGES_COUNT'][$stageId] ?>
                    </span>
                    <span class="edp-list-quick-filter-item-label">
                        <?= $stageName ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="edp-list-wrap">
            <?php
                $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                    'FILTER_ID' => $arResult['GRID']['ID'],
                    'GRID_ID' => $arResult['GRID']['ID'],
                    'FILTER' => $arResult['GRID']['FILTER'],
                    'ENABLE_LIVE_SEARCH' => false,
                    'ENABLE_LABEL' => true
                ]);
            ?>

            <div class="d-flex flex-wrap align-items-center">
                <?php if ($USER->isAdmin() || $USER->GetID() === '702') { ?>
                    <div data-vue-component='edp.list_modal'></div>
                <?php } ?>

                <?php if ($show_settings) { ?>
                    <a href="/b/report/sales-plan/" class="edp-list-report m-2" target="_blank">
                        <?= Loc::getMessage('EDP_LIST_REPORT') ?>
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
                    'SHOW_ROW_CHECKBOXES' => false,
                    'SHOW_GRID_SETTINGS_MENU' => $show_settings,
                    'SHOW_PAGINATION' => true,
                    'SHOW_PAGESIZE' => true,
                    'PAGE_SIZES' => [
                        [
                            'NAME' => '10',
                            'VALUE' => '10'
                        ],
                        [
                            'NAME' => '20',
                            'VALUE' => '20'
                        ],
                        [
                            'NAME' => '50',
                            'VALUE' => '50'
                        ],
                        [
                            'NAME' => '100',
                            'VALUE' => '100'
                        ]
                    ],
                    'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
                    'NAV_OBJECT' => $arResult['NAV_OBJECT'],
                    'SHOW_SELECTED_COUNTER' => false,
                    'SHOW_TOTAL_COUNTER' => true,
                    'ACTION_PANEL' => [],
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

<script>
    function addComment(id) {
        let commentBlock = document.querySelector('#edp-list_table').querySelector(`tr[data-id="${id}"]`).querySelector('[data-role="add-comment"]');
        let comment = commentBlock.dataset.comment;

        if (comment === null) {
            comment = '';
        }

        comment = comment.split('\n').join('<br>');
        let content = '<div class="edp-list-add-comment"><h2 class="edp-list-popup-title">Добавить примечание</h2><div class="edp-list-add-comment-all-comments">' + comment + '</div><textarea class="edp-list-add-comment-textarea"></textarea></div>';
        let popupId = 'edp-list-popup-comments' + Math.floor(Math.random() * 99999) + id;

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
                    className: 'edp-list-popup-add-button',
                    events: {
                        click: function(event) {
                            const textarea = document.querySelector('#' + popupId).querySelector('.edp-list-add-comment-textarea');
                            let comments = document.querySelector('#' + popupId).querySelector('.edp-list-add-comment-all-comments');

                            if (textarea.value.length > 0) {
                                event.target.classList.add('ui-btn', 'ui-btn-clock');

                                BX.ajax.runComponentAction('vaganov:edp.list', 'addComment', {
                                    mode: 'class',
                                    data: {
                                        id: id,
                                        comment: textarea.value
                                    }
                                }).then(function (response) {
                                    let str = response.data.split('\n').join('<br>');

                                    event.target.classList.remove('ui-btn', 'ui-btn-clock');

                                    comments.innerHTML = str;
                                    commentBlock.innerHTML = str;

                                    commentBlock.dataset.comment = str;

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
                content = '<table class="edp-list-popup-table"><thead><tr><th>ID задачи</th><th>Название задачи</th><th>Постановщик</th><th>Ответственный</th><th>Дата создания</th><th>Статус</th><th>Крайний срок</th></tr></thead><tbody>';

                data.forEach(task => {
                    content += '<tr><td class="edp-list-popup-table-link"><a target="_blank" href="/company/personal/user/' + manager + '/tasks/task/view/' + task[0] + '/">' + task[0] + '</a></td><td class="edz-list-popup-table-task">' + task[1] + '</td><td>' + task[2] + '</td><td>' + task[3] + '</td><td>' + task[4] + '</td><td>' + task[5] + '</td><td>' + task[7] + '</td></tr>';
                });

                content += '</tbody></table>';
            } else {
                content = '<div class="edp-list-popup-title">Нет задач</div>';
            }

            let popup = new BX.PopupWindow('edp-list-popup-' + Math.floor(Math.random() * 99999) + id, null, {
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
                        text: 'Добавить задачу',
                        className: 'edp-list-popup-table-add-button',
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

    class Handler {
        constructor() {
            this.emails = document.querySelector('#edp-list_table').querySelectorAll('[data-role="partner-widget-email"]');
            this.phones = document.querySelector('#edp-list_table').querySelectorAll('[data-role="partner-widget-phone"]');
            this.deals = document.querySelector('#edp-list_table').querySelectorAll('[data-role="edp-list-deals-popup"]');
            this.links = document.querySelector('#edp-list_table').querySelectorAll('[data-role="partner-widget-link"]');
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

            if (block.dataset.active === 'false') {
                block.dataset.active = true;
            } else {
                block.dataset.active = false;
            }
        }

        highlightActiveRows() {
            let tableRows = document.querySelector('#edp-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    row.removeEventListener('click', this.addLinksToRowsAddActiveClass);
                    row.addEventListener('click', this.addLinksToRowsAddActiveClass);
                });
            }
        }

        addLinksToRowsAddActiveClass(event) {
            let tableRows = document.querySelector('#edp-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    row.classList.remove('edp-list-active-row');
                });

                if (event.currentTarget.classList.contains('edp-list-active-row')) {
                    event.currentTarget.classList.remove('edp-list-active-row');
                } else {
                    event.currentTarget.classList.add('edp-list-active-row');
                }
            }
        }

        setQuickFilter() {
            function initFilter() {
                BX.addCustomEvent('BX.Main.Filter:apply', () => {
                    let filterManager = BX.Main.filterManager.getById('edp-list');
                    let filterFields = filterManager.getFilterFieldsValues();
                    let ids = Object.values(filterFields['STAGE_ID']);

                    document.querySelectorAll('.edp-list-quick-filter-item').forEach(el => {
                        el.classList.remove('edp-list-quick-filter-item-active');
                    });

                    ids.forEach(id => {
                        let el = document.querySelector('.edp-list-quick-filter-item[data-stage-id="' + id + '"]');

                        if (el) {
                            el.classList.add('edp-list-quick-filter-item-active');
                        }
                    });

                    BX.ajax.runComponentAction('vaganov:edp.list', 'reloadQuickFilter', {
                        mode: 'class'
                    }).then(function ({data}) {
                        let stages = Object.keys(data);
                        let filterItems = document.querySelectorAll('.edp-list-quick-filter-item');

                        if (stages.length > 0) {
                            filterItems.forEach(item => {
                                if (stages.includes(item.dataset.stageId)) {
                                    item.querySelector('.edp-list-quick-filter-item-count').innerHTML = data[item.dataset.stageId];
                                } else {
                                    item.querySelector('.edp-list-quick-filter-item-count').innerHTML = 0;
                                }
                            });
                        } else {
                            filterItems.forEach(item => {
                                item.querySelector('.edp-list-quick-filter-item-count').innerHTML = 0;
                            });
                        }
                    });
                });
            }

            window.onload = initFilter;

            const elWrapperFilter = document.querySelector('.edp-list-quick-filter');
            let wrapperWidth = elWrapperFilter.offsetWidth;
            const elItems = document.querySelectorAll('.edp-list-quick-filter-item');
            const elLength = elItems.length;
            const marginWidth = 10;
            let itemWidth = (wrapperWidth - (elLength * marginWidth)) / elLength;

            let setItemsEqual = (stageNum) => {
                let currentWidth = itemWidth;

                if (stageNum) {
                    let elCurrent = elWrapperFilter.querySelector('[data-stage-num="' + stageNum + '"]');
                    currentWidth = (wrapperWidth - elCurrent.scrollWidth - (elLength * marginWidth)) / (elLength - 1);
                }

                elItems.forEach(item => {
                    if (item.dataset.stageNum === stageNum) {
                        if (item.clientWidth !== item.scrollWidth) {
                            item.style.width = item.scrollWidth + 'px';
                            item.setAttribute('active', '');
                        } else {
                            item.setAttribute('active', '');
                        }
                    } else {
                        item.removeAttribute('active');
                        item.style.width = (currentWidth - 6) + 'px';
                    }
                });
            }

            setItemsEqual();

            elItems.forEach(item => {
                item.addEventListener('click', () => {
                    let filterManager = BX.Main.filterManager.getById('edp-list')
                    let filterApi = filterManager.getApi();
                    let filterFields = filterManager.getFilterFieldsValues();
                    filterFields['STAGE_ID'] = {0: item.dataset.stageId};
                    filterApi.setFields(filterFields);
                    filterApi.apply()
                })
            });

            window.addEventListener('resize', () => {
                wrapperWidth = elWrapperFilter.offsetWidth;
                itemWidth = (wrapperWidth - (elLength * marginWidth)) / elLength;
                setItemsEqual()
            });

            elItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    setItemsEqual(item.dataset.stageNum)
                })

                item.addEventListener('mouseleave', () => {
                    setItemsEqual()
                })
            });
        }

        addLinks() {
            this.links.forEach(link => {
                link.removeEventListener('click', this.linksHandler);
                link.addEventListener('click', this.linksHandler);
            });
        }

        linksHandler(event) {
            event.preventDefault();

            BX.SidePanel.Instance.open(event.currentTarget.href);
        }

        setListVisible() {
            document.querySelector('.edp-list').style.visibility = 'visible';
        }

        setDeals() {
            if (this.deals.length > 0) {
                this.deals.forEach(deal => {
                    deal.removeEventListener('click', this.dealsHandler);
                    deal.addEventListener('click', this.dealsHandler);
                });
            }
        }

        dealsHandler(event) {
            event.preventDefault();

            let data = JSON.parse(event.currentTarget.dataset.deals);

            let content = '<table class="edp-list-popup-table"><thead><tr><th>ID</th><th>Партнер</th><th>Менеджер</th><th>Клиент</th><th>Дата создания</th><th>Стадия</th><th>Программа</th></tr></thead><tbody>';

            data.forEach(deal => {
                let link = '';

                if (deal.category === 8) {
                    link = '/b/edz/?deal_id=' + deal.id + '&show';
                }

                if (deal.manager === null) {
                    deal.manager = '-';
                }

                if (deal.program === null) {
                    deal.program = '-';
                }

                let style = '';

                if (deal.is_agent === 'yes') {
                    style = 'background-color: yellow';
                }

                content += '<tr><td style="' + style + '"><a target="_blank" href="'+ link + '">' + deal.id + '</a></td><td style="' + style + '">' + deal.partner_fio + '</td><td style="' + style + '">' + deal.manager  + '</td><td style="' + style + '">' + deal.last_name + ' ' + deal.name + ' ' + deal.second_name + '</td><td style="' + style + '">' + deal.date + '</td><td style="' + style + '">' + deal.stage + '</td><td style="' + style + '">' + deal.program + '</td></tr>';
            });

            content = content + '</tbody></table>';

            let popup = new BX.PopupWindow('edp-list-deals-popup-' + Math.floor(Math.random() * 99999) + event.currentTarget.dataset.id, null, {
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
                autoHide: true
            });

            popup.show();
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

        setHeaders() {
            const headers = document.querySelector('#edp-list_table').querySelectorAll('.main-grid-head-title');

            const headText = <?= json_encode([
                Loc::getMessage('EDP_LIST_DATE_TIME_ID_SHORT'),
                Loc::getMessage('EDP_LIST_MANAGER'),
                Loc::getMessage('EDP_LIST_PARTNER'),
                Loc::getMessage('EDP_LIST_REGION'),
                Loc::getMessage('EDP_LIST_LAST_REQUEST_DATE_SHORT'),
                Loc::getMessage('EDP_LIST_NUMBER_OF_LOANS_SHORT'),
                Loc::getMessage('EDP_LIST_STAGE'),
                Loc::getMessage('EDP_LIST_TASKS_ALL'),
                Loc::getMessage('EDP_LIST_NOTE_SHORT')
            ]) ?>;

            for (let i = 0; i < headers.length; i++) {
                headers[i].innerHTML = '';
                headers[i].insertAdjacentHTML('afterbegin', headText[i]);
            }

            window.dispatchEvent(new Event('resize'));
        }

        openOrHideText() {
            let text = document.querySelector('#edp-list_table').querySelectorAll('.edp-list-text-wrap');

            text.forEach(t => {
                t.removeEventListener('click', this.openOrHideTextHandler);
                t.addEventListener('click', this.openOrHideTextHandler);
            });
        }

        openOrHideTextHandler(event) {
            if (event.currentTarget.dataset.hidden === 'false') {
                event.currentTarget.dataset.hidden = true;
            } else {
                event.currentTarget.dataset.hidden = false;
            }
        }
    }

    let handler = new Handler;

    handler.setQuickFilter();
    handler.highlightActiveRows();
    handler.addLinks();
    handler.setHeaders();
    handler.setDeals();
    handler.setPhones();
    handler.setEmails();
    handler.openOrHideText();
    handler.setListVisible();
    handler.openContacts();

    BX.ready(function () {
        BX.addCustomEvent('onAjaxSuccess', () => {
            let handler = new Handler;

            handler.highlightActiveRows();
            handler.addLinks();
            handler.setHeaders();
            handler.setDeals();
            handler.setPhones();
            handler.setEmails();
            handler.openOrHideText();
            handler.setListVisible();
            handler.openContacts();
        });
    });
</script>