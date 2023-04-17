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

<div id="design-list" class="design-list">
    <h4>
        РЕЕСТР МАКЕТОВ
    </h4>
    <div class="design-list-wrapper">
        <div class="design-list-filter">
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
        <div data-vue-component="design.list_panel"></div>
    </div>
    <div class="design-list-grid">
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
    function finishTask(taskID) {
        BX.ajax.runComponentAction('vaganov:design.list', 'finishTask', {
            mode: 'class',
            data: {
                taskID: taskID
            }
        }).then((response) => {
            console.log(response);
            BX.Main.gridManager.reload('design-list');
        }, (error) => {
            console.log(error);
        });
    }

    function sendMaquette(taskID, managerID) {
        BX.ajax.runComponentAction('vaganov:design.list', 'sendEmail', {
            mode: 'class',
            data: {
                taskID: taskID,
                managerID: managerID
            }
        }).then((response) => {
            console.log(response);
            BX.Main.gridManager.reload('design-list');
        }, (error) => {
            console.log(error);
        });
    }

    function addFilesToTask(taskID, partner, manager) {
        let content = '<div data-role="status" data-visible="false"></div><form enctype="multipart/form-data" method="POST" class="design-list-popup-add-files-form"><label for="add-files-to-task"><div class="design-list-popup-add-files-button"><svg width="50px" height="50px" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg"><path d="M25 42c-9.4 0-17-7.6-17-17S15.6 8 25 8s17 7.6 17 17-7.6 17-17 17zm0-32c-8.3 0-15 6.7-15 15s6.7 15 15 15 15-6.7 15-15-6.7-15-15-15z"/><path d="M25 34.4l-9.7-9.7 1.4-1.4 8.3 8.3 8.3-8.3 1.4 1.4z"/><path d="M24 16h2v17h-2z"/></svg><span>Выберите файлы</span></div><input multiple required type="file" id="add-files-to-task" class="design-list-popup-add-files-form-input" name="files[]"></label><button class="design-list-button" type="submit">Загрузить</button></form>';
        let userID = <?= $USER->GetID() ?>;

        let popup = new BX.PopupWindow('design-list-popup-form-add-files-to-task-' + Math.floor(Math.random() * 99999) + '-' + userID, null, {
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
            events: {
                onPopupShow: function() {
                    const form = this.contentContainer.querySelector('.design-list-popup-add-files-form');
                    const status = this.contentContainer.querySelector('[data-role="status"]');
                    const fileInput = document.querySelector('input[type="file"]');
                    const submitButton = form.querySelector('button[type="submit"]');

                    fileInput.addEventListener('change', (event) => {
                        let count = event.currentTarget.files.length;
                        let span = event.currentTarget.closest('label').querySelector('span');

                        if (event.currentTarget.files.length > 0) {
                            if (count === 1) {
                                span.innerHTML = 'Выбран ' + count + ' файл';
                            } else if (count < 5) {
                                span.innerHTML = 'Выбрано ' + count + ' файла';
                            } else {
                                span.innerHTML = 'Выбрано ' + count + ' файлов';
                            }
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();

                        submitButton.classList.add('ui-btn', 'ui-btn-wait');

                        const formData = new FormData(form);
                        const bxFormData = new BX.ajax.FormData();

                        for (let [name, value] of formData) {
                            bxFormData.append(name, value);
                        }

                        bxFormData.append('task_id', taskID);
                        bxFormData.append('partner', partner);
                        bxFormData.append('manager', manager);

                        const ajaxPath = '/local/ajax/addFilesToTask.php';

                        bxFormData.send(
                            ajaxPath,
                            function(result) {
                                form.dataset.visible = false;
                                status.innerHTML = '';
                                status.innerHTML = result;
                                status.dataset.visible = true;
                                BX.Main.gridManager.reload('design-list');
                            },
                            null,
                            function(error) {
                                form.dataset.visible = false;
                                status.innerHTML = '';
                                status.innerHTML = error;
                                status.dataset.visible = true;
                                BX.Main.gridManager.reload('design-list');
                            }
                        );
                    });
                }
            }
        });

        popup.show();
    }

    class Handler {
        constructor() {
            this.links = document.querySelector('#design-list_table').querySelectorAll('.design-list-partner-link');
            this.headers = document.querySelector('#design-list_table').querySelectorAll('.main-grid-head-title');
            this.text = document.querySelector('#design-list_table').querySelectorAll('[data-role="text-wrap"]');
        }

        setHeaders() {
            const headText = <?= json_encode([
                Loc::getMessage('DESIGN_LIST_COUNT'),
                Loc::getMessage('DESIGN_LIST_TASK_CREATED_DATE'),
                Loc::getMessage('DESIGN_LIST_CREATED_BY'),
                Loc::getMessage('DESIGN_LIST_PARTNER'),
                Loc::getMessage('DESIGN_LIST_TASK_FILES'),
                Loc::getMessage('DESIGN_LIST_TASK_DESCRIPTION'),
                Loc::getMessage('DESIGN_LIST_TASK_CLOSED_DATE'),
                Loc::getMessage('DESIGN_LIST_DONE_MAQUETTE'),
                Loc::getMessage('DESIGN_LIST_MAQUETTES_SENDED')
            ]) ?>;

            for (let i = 0; i < this.headers.length; i++) {
                this.headers[i].innerHTML = '';
                this.headers[i].insertAdjacentHTML('afterbegin', headText[i]);
            }

            window.dispatchEvent(new Event('resize'));
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

        openOrHideText() {
            this.text.forEach(t => {
                t.removeEventListener('click', this.openOrHideTextHandler);
                t.addEventListener('click', this.openOrHideTextHandler);
            });
        }

        openOrHideTextHandler(event) {
            event.currentTarget.dataset.hidden = event.currentTarget.dataset.hidden === 'false';
        }

        setListVisible() {
            document.querySelector('.design-list').style.visibility = 'visible';
        }
    }

    let handler = new Handler;

    handler.addLinks();
    handler.setHeaders();
    handler.openOrHideText();
    handler.setListVisible();

    BX.ready(function () {
        BX.addCustomEvent('onAjaxSuccess', () => {
            let handler = new Handler;

            handler.addLinks();
            handler.setHeaders();
            handler.openOrHideText();
            handler.setListVisible();
        });
    });
</script>