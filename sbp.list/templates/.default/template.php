<?php
use Bitrix\Main\Loader;

/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

Loader::IncludeModule('crm');
$this->addExternalCss('/style.css');

global $APPLICATION;
global $USER;

CJSCore::Init(['ajax', 'window']);
?>

<div class="sbp-list">
    <div class="sbp-list-tabs">
        <div class="sbp-list-tab" data-target="item-1" data-active="true">
            Реестр СБП
        </div>
        <div class="sbp-list-tab" data-target="item-2" data-active="false">
            Созданные СБП
        </div>
    </div>
    <div class="sbp-list-grid">
        <div class="sbp-list-tab-item" data-id="item-1" data-active="true">
            <div class="sbp-list-grid-wrap">
                <?php
                    $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                        'FILTER_ID' => $arResult['GRID']['ID'],
                        'GRID_ID' => $arResult['GRID']['ID'],
                        'FILTER' => $arResult['GRID']['FILTER'],
                        'ENABLE_LIVE_SEARCH' => false,
                        'ENABLE_LABEL' => true
                    ]);
                ?>
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
        <div class="sbp-list-tab-item" data-id="item-2" data-active="false">
            <div data-vue-component="sbp.list_qrs"></div>
        </div>
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

    echo "<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    class Handler {
        constructor() {
            this.tabs = document.querySelectorAll('.sbp-list-tab');
        }

        addTabs() {
            let self = this;

            self.tabs.forEach(tab => {
                tab.addEventListener('click', event => {
                    self.tabs.forEach(t => t.dataset.active = false);

                    event.target.dataset.active = true;

                    let items = document.querySelectorAll('.sbp-list-tab-item');

                    items.forEach(item => item.dataset.active = false);

                    let target = document.querySelector('.sbp-list-tab-item[data-id=\"' + event.target.dataset.target + '\"]');

                    target.dataset.active = true;
                });
            });
        }
    }

    let handler = new Handler;

    handler.addTabs();

    BX.ready(function () {
        BX.addCustomEvent('onAjaxSuccess', () => {
            let handler = new Handler;

            handler.addTabs();
        });
    });
</script>";
?>