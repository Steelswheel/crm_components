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

CJSCore::Init(['ajax', 'window']);

?>

<div class="eds-list">
    <div class="eds-list-grid">
        <div class="eds-list-quick-filter">
            <?php
                $stageNum = 0;

                foreach ($arResult['STAGES'] as $stageId => $stageValue) {
                    if ($stageId === 'C14:LOSE')
                        continue;

                    $activeClass = '';

                    if (!empty($arResult['FILTER_DATA']['STAGE_ID'])) {
                        $activeClass = in_array($stageId, $arResult['FILTER_DATA']['STAGE_ID'])
                            ? 'eds-list-quick-filter-item-active'
                            : '';
                    }

                    $stageName = preg_replace('/^\d+ - /m', '', $stageValue);
                    $stageNum++;
            ?>
                <div class="eds-list-quick-filter-item <?= $activeClass ?>" data-stage-num="<?= $stageNum ?>" data-stage-id="<?= $stageId ?>">
                    <span class="eds-list-quick-filter-item-num">
                        <?= $stageNum ?>
                    </span>
                    <span class="eds-list-quick-filter-item-count">
                        <?= $arResult['DEALS_STAGES_COUNT'][$stageId] ?>
                    </span>
                    <span class="eds-list-quick-filter-item-label">
                        <?= $stageName ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="eds-list-wrap">
            <?php
                $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                    'FILTER_ID' => $arResult['GRID']['ID'],
                    'GRID_ID' => $arResult['GRID']['ID'],
                    'FILTER' => $arResult['GRID']['FILTER'],
                    'ENABLE_LIVE_SEARCH' => false,
                    'ENABLE_LABEL' => true
                ]);
            ?>

            <div class="d-flex">
                <a href="/b/eds/?deal_id=&amp;show" target="_blank" class="eds-list-button">
                    Новая заявка
                </a>
                <?php if ($USER->isAdmin() || (int)$USER->GetID() === 104) { ?>
                    <a href="#" class="eds-list-button ml-2" data-role="xls-download" data-filter='<?= json_encode($arResult['FILTER_ARRAY']) ?>'>
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
                    'SHOW_ROW_CHECKBOXES' => false,
                    'SHOW_GRID_SETTINGS_MENU' => false,
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
                    'SHOW_ROW_ACTIONS_MENU' => false,
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
            this.ths = Array.from(document.querySelector('#eds-list_table').querySelectorAll('th'));
            this.hiddenIndexes = [];
            this.state = false;
            this.emails = document.querySelector('#eds-list_table').querySelectorAll('[data-role="partner-widget-email"]');
            this.phones = document.querySelector('#eds-list_table').querySelectorAll('[data-role="partner-widget-phone"]');
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

            let url = '/local/components/vaganov/eds.list/xls-export.php';

            if (getStr.length > 0) {
                url += '?' + getStr;
            }

            window.open(url);
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

        setQuickFilter() {
            function initFilter() {
                BX.addCustomEvent('BX.Main.Filter:apply', () => {
                    let filterManager = BX.Main.filterManager.getById('eds-list');
                    let filterFields = filterManager.getFilterFieldsValues();
                    let ids = Object.values(filterFields['STAGE_ID']);

                    document.querySelectorAll('.eds-list-quick-filter-item').forEach(el => {
                        el.classList.remove('eds-list-quick-filter-item-active');
                    });

                    ids.forEach(id => {
                        let el = document.querySelector('.eds-list-quick-filter-item[data-stage-id="' + id + '"]');

                        if (el) {
                            el.classList.add('eds-list-quick-filter-item-active');
                        }
                    });

                    BX.ajax.runComponentAction('vaganov:eds.list', 'reloadQuickFilter', {
                        mode: 'class'
                    }).then(function (response) {
                        let data = response.data;

                        let stages = Object.keys(data);
                        let filterItems = document.querySelectorAll('.eds-list-quick-filter-item');

                        if (stages.length > 0) {
                            filterItems.forEach(item => {
                                if (stages.includes(item.dataset.stageId)) {
                                    item.querySelector('.eds-list-quick-filter-item-count').innerHTML = data[item.dataset.stageId];
                                } else {
                                    item.querySelector('.eds-list-quick-filter-item-count').innerHTML = 0;
                                }
                            });
                        } else {
                            filterItems.forEach(item => {
                                item.querySelector('.eds-list-quick-filter-item-count').innerHTML = 0;
                            });
                        }
                    });
                });
            }

            window.onload = initFilter;

            const elWrapperFilter = document.querySelector('.eds-list-quick-filter');
            const elItems = document.querySelectorAll('.eds-list-quick-filter-item');
            const elLength = elItems.length;
            const marginWidth = 10;
            let wrapperWidth = elWrapperFilter.offsetWidth;
            let itemWidth = (wrapperWidth - (elLength * marginWidth)) / elLength;

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
                    } else {
                        item.removeAttribute('active');
                        item.style.width = currentWidth + 'px';
                    }
                });
            }

            setItemsEqual();

            elItems.forEach(item => {
                item.addEventListener('click', () => {
                    let filterManager = BX.Main.filterManager.getById('eds-list');
                    let filterApi = filterManager.getApi();
                    let filterFields = filterManager.getFilterFieldsValues();

                    filterFields['STAGE_ID'] = {
                        0: item.dataset.stageId
                    };

                    filterApi.setFields(filterFields);
                    filterApi.apply();
                });
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
            setTimeout(() => {
                const headers = document.querySelector('.eds-list').querySelectorAll('.main-grid-head-title');

                const headText = <?= json_encode([
                    Loc::getMessage('EDS_LIST_DATE_TIME'),
                    Loc::getMessage('EDS_LIST_MANAGER'),
                    Loc::getMessage('EDS_LIST_PARTNER'),
                    Loc::getMessage('EDS_LIST_CONTRIBUTOR'),
                    Loc::getMessage('EDS_LIST_STAGE'),
                    Loc::getMessage('EDS_LIST_DATE_NUMBER_DS'),
                    Loc::getMessage('EDS_LIST_CONDITIONS'),
                    Loc::getMessage('EDS_LIST_PAYMENT_OF_INTEREST'),
                    Loc::getMessage('EDS_LIST_CONTRACT_AMOUNT'),
                    Loc::getMessage('EDS_LIST_SUM'),
                    Loc::getMessage('EDS_LIST_DEPOSIT_END_DATE'),
                    Loc::getMessage('EDS_BALANCE_DEPOSIT_SUM_DATE'),
                    Loc::getMessage('EDS_BALANCE_REPLENISHMENT_SUM_DATE'),
                    Loc::getMessage('EDS_BALANCE_PARTIAL_WITHDRAWAL_SUM_DATE'),
                    Loc::getMessage('EDS_BALANCE_INTEREST_PAYMENT_SUM_DATE'),
                    Loc::getMessage('EDS_BALANCE_DEPOSIT_PAYMENT_SUM_DATE')
                ]) ?>;

                for (let i = 0; i < headers.length; i++) {
                    headers[i].innerHTML = '';
                    headers[i].insertAdjacentHTML('afterbegin', headText[i]);
                }

                window.dispatchEvent(new Event('resize'));

                this.setListVisible();

                let elGroup = {
                    'name': 'БАЛАНС',
                    'items': [
                        {
                            label: 'EDS_BALANCE_DEPOSIT_SUM_DATE',
                            width: '87px'
                        },
                        {
                            label: 'EDS_BALANCE_REPLENISHMENT_SUM_DATE',
                            width: '87px'
                        },
                        {
                            label: 'EDS_BALANCE_PARTIAL_WITHDRAWAL_SUM_DATE',
                            width: '87px'
                        },
                        {
                            label: 'EDS_BALANCE_INTEREST_PAYMENT_SUM_DATE',
                            width: '87px'
                        },
                        {
                            label: 'EDS_BALANCE_DEPOSIT_PAYMENT_SUM_DATE',
                            width: '87px'
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

                        elMain.innerHTML = `<div class='edsGroupCrutch'>
                    <div class='edsGroupCrutch--top'>${elGroup.name}</div>
                    <div class='edsGroupCrutch--bottom'>${htmlBottom}</div></div>`;

                        elMain.colSpan = elGroup.items.length;
                    }
                }

            }, 0);

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 0);
        }

        setListVisible() {
            document.querySelector('.eds-list').style.visibility = 'visible';
        }

        addLinksToRows() {
            let tableRows = document.querySelector('#eds-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    let links = row.querySelectorAll('a[data-role="slider-link"]');
                    let widgetLinks = row.querySelectorAll('a[data-role="partner-widget-link"]');

                    if (widgetLinks.length > 0) {
                        widgetLinks.forEach(link => {
                            link.removeEventListener('click', this.addLinksToRowsIframeOpenHandler);
                            link.addEventListener('click', this.addLinksToRowsIframeOpenHandler);
                        });
                    }

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

            BX.SidePanel.Instance.open(event.currentTarget.href);
        }

        addLinksToRowsAddActiveClass(event) {
            let tableRows = document.querySelector('#eds-list_table').querySelectorAll('.main-grid-row');

            if (tableRows) {
                tableRows.forEach(row => {
                    row.classList.remove('eds-list-active-row');
                });

                if (event.currentTarget.classList.contains('eds-list-active-row')) {
                    event.currentTarget.classList.remove('eds-list-active-row');
                } else {
                    event.currentTarget.classList.add('eds-list-active-row');
                }
            }
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
    handler.addLinksToRows();
    handler.setQuickFilter();
    handler.openContacts();
    handler.setPhones();
    handler.setEmails();
    handler.getXls();

    BX.ready(function () {
        BX.addCustomEvent('BX.Main.grid:paramsUpdated', () => {
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
            handler.addLinksToRows();
            handler.openContacts();
            handler.setPhones();
            handler.setEmails();
            handler.getXls();
            handler.setHeaderColumnsWidth();
        });

        BX.addCustomEvent('BX.Main.grid:paramsUpdated', () => {
            let handler = new Handler;

            handler.setHeaders();
            handler.getXls();
        });
    });
</script>