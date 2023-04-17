<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Vaganov\Helper;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\ObjectTable;
use Vaganov\DesignWidget;
use Vaganov\Notification;

Loader::IncludeModule('crm');
Loader::IncludeModule('tasks');
Loader::includeModule('disk');
Loader::includeModule('mail');

CJSCore::Init(['ui.viewer']);

class DesignList extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [];
    }

    public function getTasks($arFilter)
    {
        $res = CTasks::GetList(
            [
                'CREATED_DATE' => 'DESC'
            ],
            $arFilter,
            [
                'ID',
                'RESPONSIBLE_ID',
                'CREATED_BY',
                'UF_CRM_TASK',
                'DESCRIPTION',
                'CREATED_DATE',
                'CLOSED_DATE',
                'STATUS',//5 - завершена и принята постановщиком
                'UF_MAQUETTE_FILE',
                'UF_MAQUETTES_SENDED',
                'UF_PARTNER_EMAIL',
                'UF_IS_FROM_PARTNER_TASK',
                'UF_PARTNER_FILES'
            ],
            [],
            []
        );

        $tasks_counts = [];
        $count = 1;

        $allTasks = [];

        while ($task = $res->Fetch()) {
            $allTasks[] = $task;
            $count++;
        }

        foreach ($allTasks as $task) {
            $count--;
            $tasks_counts[$task['ID']] = $count;
        }

        $this->setPageNavigation($res);

        $tasks = [];
        $storageFileIDs = [];

        while ($task = $res->Fetch()) {
            $task['COUNT'] = $tasks_counts[$task['ID']];
            $tasks[] = $task;

            if (!empty($task['UF_MAQUETTE_FILE'])) {
                foreach ($task['UF_MAQUETTE_FILE'] as $file) {
                    $storageFileIDs[] = $file;
                }
            }
        }

        if (!empty($storageFileIDs)) {
            $fileData = $this->getAttachedFiles($storageFileIDs);
            $files = $fileData['FILES'];
            $queryItems = $fileData['QUERY_ITEMS'];

            $data = [];

            foreach ($queryItems as $item) {
                $data[$item['TASK_ID']][] = [
                    'TASK_ID' => $item['TASK_ID'],
                    'OBJECT_ID' => $item['OBJECT_ID'],
                    'ATTACHED_ID' => $item['ATTACHED_ID'],
                    'FILE' => $files[$item['FILE_ID']],
                    'PATH' => Driver::getInstance()->getUrlManager()->getUrlToActionShowUfFile($item['ATTACHED_ID'])
                ];
            }

            foreach ($tasks as &$task) {
                if (!empty($data[(int)$task['ID']])) {
                    $task['FILE_DATA'] = $data[(int)$task['ID']];
                }
            }
        }

        return $tasks;
    }

    public function getAttachedFiles($ids)
    {
        $query = new Query(ObjectTable::getEntity());

        $query
            ->registerRuntimeField('ATTACHED_OBJECT', [
                'data_type' => 'Bitrix\Disk\Internals\AttachedObjectTable',
                'reference' => [
                    '=this.ID' => 'ref.OBJECT_ID',
                ],
            ])
            ->setSelect([
                'OBJECT_ID' => 'ATTACHED_OBJECT.OBJECT_ID',
                'ATTACHED_ID' => 'ATTACHED_OBJECT.ID',
                'TASK_ID' => 'ATTACHED_OBJECT.ENTITY_ID',
                'FILE_ID'
            ])
            ->setFilter([
                'ATTACHED_ID' => $ids
            ])
            ->exec();

        $fileIDs = [];

        foreach ($query->fetchAll() as $item) {
            $fileIDs[] = $item['FILE_ID'];
        }

        $arFilter = ['@ID' => implode(',', $fileIDs)];

        $files = [];

        $res = CFile::GetList([], $arFilter);

        while ($file = $res->GetNext()) {
            $files[$file['ID']] = $file;
        }

        return [
            'QUERY_ITEMS' => $query->fetchAll(),
            'FILES' => $files
        ];
    }

    public function sendEmailAction($taskID, $managerID)
    {
        global $USER;

        $oTask = CTaskItem::getInstance($taskID, $USER->GetID());
        $taskData = $oTask->getData();
        $taskFilesIDs = $taskData['UF_MAQUETTE_FILE'];
        $partnerEmail = $taskData['UF_PARTNER_EMAIL'];
        $bigFilesData = json_decode(htmlspecialchars_decode($taskData['UF_MAQUETTE_FILE_LINKS']), 1);

        $text = 'Здравствуйте. Ваши макеты готовы. По вопросам дизайна макетов: design@kooperatiff.ru';

        if (!empty($bigFilesData)) {
            $text .= '<br><br>К письму прикреплены файлы большого размера (перейдите по ссылке, чтобы скачать):<br>';

            foreach ($bigFilesData as $item) {
                $text .= '<a href="' . $item['LINK'] . '" target="_blank">' . $item['NAME'] . '<a><br>';
            }
        }

        $arMerge = [
            'CHARSET' => 'UTF-8',
            'CONTENT_TYPE' => 'html',
            'TO' => $partnerEmail,
            'BODY' => $text,
            'HEADER' => [
                'From' => 'design@kooperatiff.ru',
                'To' => $partnerEmail,
                'Subject' => 'Макет'
            ]
        ];

        if (!empty($taskFilesIDs)) {
            $attachments = [];

            $files = $this->getAttachedFiles($taskFilesIDs)['FILES'];

            $size = 0;

            foreach ($files as $file) {
                $size += (int)$file['FILE_SIZE'] / 1048576;

                if ($size < 14) {
                    $attachments[] = [
                        'NAME' => $file['ORIGINAL_NAME'],
                        'PATH' => \CFile::MakeFileArray($file['ID'])['tmp_name'],
                        'CONTENT_TYPE' => $file['CONTENT_TYPE']
                    ];
                }
            }

            $arMerge['ATTACHMENT'] = $attachments;
        }

        if (!empty($taskFilesIDs) || !empty($bigFilesData)) {
            $mailbox = Bitrix\Mail\MailboxTable::getList([
                'filter' => ['EMAIL' => 'design@kooperatiff.ru', 'ACTIVE' => 'Y']
            ])->fetch();

            $mailboxHelper = Mailbox::createInstance($mailbox['ID'], false);

            $successMessage = "Макеты по задаче [URL=/company/personal/user/{$managerID}/tasks/task/view/{$taskID}/] №{$taskID} [/URL] отправлены";

            if ($mailboxHelper) {
                $mailboxHelper->mail($arMerge);
                \Bitrix\Mail\Helper::syncOutgoingAgent($mailbox['ID']);

                Notification::send($successMessage, $managerID);
                Notification::send($successMessage, $USER->GetID());

                $oTask->update(['UF_MAQUETTES_SENDED' => true]);

                return 'Макеты отправлены';
            } else {
                Notification::send( 'Не удалось отправить макеты', $managerID);
                $oTask->update(['UF_MAQUETTES_SENDED' => false]);

                return 'Не удалось отправить макеты';
            }
        } else {
            Notification::send('К задаче не прикреплены файлы макетов!', $USER->GetID());

            return 'К задаче не прикреплены файлы макетов!';
        }
    }

    public function setPageNavigation($obj)
    {
        $grid_options = new Bitrix\Main\Grid\Options($this->arResult['GRID']['ID']);
        $nav_params = $grid_options->GetNavParams();
        $obj->NavStart(isset($nav_params['nPageSize']) ? $nav_params['nPageSize'] : 10);
        $this->arResult['ROWS_COUNT'] = $obj->SelectedRowsCount();
        $obj->bShowAll = true;
        $this->arResult['NAV_OBJECT'] = $obj;
    }

    public function getPartners()
    {
        $partnersQuery = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10
            ],
            'ID',
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_ID',
                'UF_EDP_KPK',
                'ASSIGNED_BY_ID'
            ]
        );

        $partners = [];

        while ($partner = $partnersQuery->Fetch()) {
            $partners[] = $partner;
        }

        return $partners;
    }

    public function getFilter()
    {
        $arFilter = [
            'RESPONSIBLE_ID' => 538
        ];

        $filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['GRID']['ID']);
        $filterData = $filterOption->getFilter([]);

        if (!empty($filterData['CREATED_DATE_from'])) {
            $arFilter['>=CREATED_DATE'] = $filterData['CREATED_DATE_from'];
        }

        if (!empty($filterData['CREATED_DATE_to'])) {
            $arFilter['<=CREATED_DATE'] = $filterData['CREATED_DATE_to'];
        }

        if (!empty($filterData['CLOSED_DATE_from'])) {
            $arFilter['>=CLOSED_DATE'] = $filterData['CLOSED_DATE_from'];
        }

        if (!empty($filterData['CLOSED_DATE_to'])) {
            $arFilter['<=CLOSED_DATE'] = $filterData['CLOSED_DATE_to'];
        }

        foreach ($filterData as $k => $v) {
            switch($k) {
                case 'PRESET_ID':
                case 'FILTER_ID':
                case 'FILTER_APPLIED':
                case 'FIND':
                case 'CREATED_DATE_days':
                case 'CREATED_DATE_month':
                case 'CREATED_DATE_datesel':
                case 'CREATED_DATE_quarter':
                case 'CREATED_DATE_year':
                case 'CREATED_DATE_from':
                case 'CREATED_DATE_to':
                case 'CREATED_DATE':
                case 'CLOSED_DATE_days':
                case 'CLOSED_DATE_month':
                case 'CLOSED_DATE_datesel':
                case 'CLOSED_DATE_quarter':
                case 'CLOSED_DATE_year':
                case 'CLOSED_DATE_from':
                case 'CLOSED_DATE_to':
                case 'CLOSED_DATE':
                    break;
                case 'PARTNER':
                    $filter = [];

                    $arStrings = explode(' ', trim($v));

                    if (!empty($arStrings[0])) {
                        $contFilter['LAST_NAME'] = '%' . $arStrings[0] . '%';

                        if (!empty($arStrings[1])) {
                            $contFilter['NAME'] = '%' . $arStrings[1] . '%';
                        }

                        if (!empty($arStrings[2])) {
                            $contFilter['SECOND_NAME'] = '%' . $arStrings[2] . '%';
                        }

                        $cont = CCrmContact::GetList([], $contFilter, ['ID']);
                        $ids = [];
                        $contactFilter = [];

                        while ($res = $cont->GetNext()) {
                            $contactFilter[] = 'C_' . $res['ID'];
                            $ids[] = $res['ID'];
                        }

                        $deals = CCrmDeal::GetListEx(
                            [],
                            [
                                'CATEGORY_ID' => 10,
                                'CONTACT_ID' => $ids
                            ],
                            false,
                            false,
                            [
                                'ID'
                            ],
                            []
                        );

                        $dealsFilter = [];

                        while ($res = $deals->GetNext()) {
                            $dealsFilter[] = 'D_' . $res['ID'];
                        }

                        $deals = CCrmDeal::GetListEx(
                            [],
                            [
                                'CATEGORY_ID' => [8, 13],
                                'UF_CRM_1540188759' => $ids
                            ],
                            false,
                            false,
                            [
                                'ID'
                            ],
                            []
                        );

                        while ($res = $deals->GetNext()) {
                            $dealsFilter[] = 'D_' . $res['ID'];
                        }

                        $filter = [
                            'LOGIC' => 'OR',
                            ['UF_CRM_TASK' => $contactFilter],
                            ['UF_CRM_TASK' => $dealsFilter]
                        ];
                    }

                    if (!empty($filter)) {
                        $arFilter[] = $filter;
                    } else {
                        $arFilter[] = false;
                    }
                    break;

                case 'IS_COMPLETED':
                    if ($v === 'Y') {
                        $arFilter['STATUS'] = 5;
                    } else {
                        $arFilter['!=STATUS'] = 5;
                    }
                    break;
                default:
                    $arFilter[$k] = $v;
                    break;
            }
        }

        return $arFilter;
    }

    public function addNewTaskAction($title, $type, $size, $contacts, $files = false, $email = false, $note = false, $id = false)
    {
        global $USER;

        $size = !empty($size) ? $size : 'нет';

        $partner_files = [];
        $partner_files_link = '';

        if (!empty($files)) {
            $files = json_decode($files, true);

            if (count($files) > 1) {
                $bytes = bin2hex(random_bytes(10));
                $arr_name = $_SERVER['DOCUMENT_ROOT'] . '/upload/lk_design/design_arr_' . $bytes . '.zip';

                $files_urls = array_map(function ($item) {
                    return $_SERVER['DOCUMENT_ROOT'] . $item['url'];
                }, $files);

                $arr = Helper::addFilesToZip($files_urls, $arr_name, true);

                if ($arr) {
                    $result = [
                        'url' => $arr,
                        'name' => 'design_arr_' . $bytes . '.zip'
                    ];
                } else {
                    return false;
                }
            } else {
                $result = [
                    'url' => $_SERVER['DOCUMENT_ROOT'] . $files[0]['url'],
                    'name' => $files[0]['name']
                ];
            }

            $makeFileArray = \CFile::MakeFileArray($result['url']);
            $makeFileArray['name'] = $result['name'];
            $makeFileArray['description'] = '';
            $makeFileArray['MODULE_ID'] = 'main';

            $file_id = \CFile::SaveFile($makeFileArray, 'lk_design');

            if ($file_id) {
                $partner_files[] = $file_id;
                $partner_files_link = '[URL=' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $result['url']) . ']' . $result['name'] . '[/URL]';
            }
        }

        $description = '1) Назначение макетов: ' . $type . PHP_EOL . '2) Размеры макетов: ' . $size . PHP_EOL . '3) Контакты для макетов: ' . $contacts . PHP_EOL . '4) Email для отправки макетов: ' . $email . PHP_EOL . '5) Файлы от партнера: ' . $partner_files_link . PHP_EOL . '6) Дополнительная информация: ' . $note . PHP_EOL;

        $date = new DateTime();

        $deadline = $this->getDeadline($date);

        $params = [
            'AUDITORS' => [42],
            'CREATED_BY' => $USER->GetID(),
            'RESPONSIBLE_ID' => 538,
            'TITLE' => $title,
            'DESCRIPTION' => $description,
            'TASK_CONTROL' => 'N',
            'DEADLINE' => $deadline,
            'UF_IS_FOR_MAQUETTES_TASK' => true,
            'UF_PARTNER_FILES' => $partner_files
        ];

        if (!empty($id)) {
            $link = ['D_' . $id];

            $params['UF_CRM_TASK'] = $link;
        }

        if (!empty($email)) {
            $params['UF_PARTNER_EMAIL'] = $email;
        }

        $status = \CTaskItem::add($params, 1);
        \Bitrix\Tasks\Integration\Report\Internals\TaskTable::update($status->getId(), ['UF_PARTNER_FILES' => $partner_files]);
        \Bitrix\Tasks\TaskTable::update($status->getId(), ['UF_PARTNER_FILES' => $partner_files]);

        if ($status) {
            return 'Задача успешно создана';
        } else {
            return 'Не удалось создать задачу';
        }
    }

    public function finishTaskAction($taskID)
    {
        global $USER;

        $oTask = CTaskItem::getInstance($taskID, $USER->GetID());
        $oTask->complete();

        return (int)($oTask->getData()['STATUS']) === 5 ? 'Завершена' : 'Не завершена';
    }

    public function getPartnersDataAction() {
        $partnersQuery = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10,
                '!=STAGE_ID' => ['C10:WON', 'C10:LOSE']
            ],
            'ID',
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_ID',
                'UF_EDP_KPK',
                'ASSIGNED_BY_ID'
            ]
        );

        $partners = [];

        while ($partner = $partnersQuery->Fetch()) {
            $partners[] = $partner;
        }

        $contactIDs = [];

        foreach ($partners as $partner) {
            $contactIDs[] = $partner['CONTACT_ID'];
        }

        $dbResMultiFields = CCrmFieldMulti::GetList(
            ['ID' => 'asc'],
            [
                'ENTITY_ID' => 'CONTACT',
                'ELEMENT_ID' =>  $contactIDs,
                'TYPE_ID' => 'EMAIL'
            ]
        );

        $emails = [];

        $result = [];

        while ($arMultiField = $dbResMultiFields->Fetch()) {
            $emails[$arMultiField['ELEMENT_ID']][] = $arMultiField['VALUE'];
        }

        foreach ($partners as $partner) {
            $result['names'][] = [
                'value' => $partner['CONTACT_LAST_NAME'] . ' ' . $partner['CONTACT_NAME'] . ' ' . $partner['CONTACT_SECOND_NAME']
            ];

            $result['emails'][] = [
                'EMAIL' => $emails[$partner['CONTACT_ID']],
                'NAME' => $partner['CONTACT_LAST_NAME'] . ' ' . $partner['CONTACT_NAME'] . ' ' . $partner['CONTACT_SECOND_NAME'],
                'ID' => $partner['ID']
            ];
        }

        return $result;
    }

    public function getDeadline($d) {
        $date = new DateTime($d);
        $dateNow = $date->format('d.m.Y H:i:s');
        $dateNow = date('d.m.Y H:i:s', (strtotime($dateNow) + \CTimeZone::GetOffset()));

        $workDayStart = (new DateTime($date->format('d.m.Y') . ' 09:00:00'))->format('d.m.Y H:i:s');
        $workDayEnd = (new DateTime($date->format('d.m.Y') . ' 18:00:00'))->format('d.m.Y H:i:s');

        if ($dateNow > $workDayEnd) {
            $nextDayTime = date('d.m.Y H:i:s', strtotime($workDayEnd . ' + 1 days'));

            if (\Vaganov\Helper::isHoliday($nextDayTime)) {
                $n = date('N', strtotime($nextDayTime));

                if ($n === '6') {
                    $deadline = date('d.m.Y H:i:s', (strtotime($nextDayTime . ' + 3 days')));
                } else {
                    $deadline = date('d.m.Y H:i:s', (strtotime($nextDayTime . ' + 2 days')));
                }
            } else {
                $deadline = date('d.m.Y H:i:s', (strtotime($nextDayTime. ' + 1 days')));
            }
        } else {
            if ($dateNow < $workDayStart) {
                $deadline = date('d.m.Y H:i:s', (strtotime($workDayStart. ' + 2 days')));
            } else {
                $deadline = date('d.m.Y H:i:s', (strtotime($dateNow. ' + 2 days')));
            }

            if (\Vaganov\Helper::isHoliday($deadline)) {
                $deadline = date('d.m.Y H:i:s', (strtotime($workDayEnd. ' + 2 days')));

                $n = date('N', strtotime($deadline));

                if ($n === '6') {
                    $deadline = date('d.m.Y H:i:s', (strtotime($deadline . ' + 2 days')));
                } else {
                    $deadline = date('d.m.Y H:i:s', (strtotime($deadline . ' + 1 days')));
                }
            }
        }

        if (\Vaganov\Helper::isHoliday($deadline)) {
            while (\Vaganov\Helper::isHoliday($deadline)) {
                $deadline = date('d.m.Y H:i:s', (strtotime($deadline . ' + 1 days')));
            }
        }

        return $deadline;
    }

    public function uploadAction()
    {
        return Helper::uploadAnything('lk_design', false);
    }

    public function executeComponent()
    {
        $users = Helper::getUsers(53);
        $usersFullInfo = Helper::getUsersFullInfo(53);
        $emails = [];

        foreach ($usersFullInfo as $user) {
            $emails[$user['ID']] = $user['EMAIL'];
        }

        $this->arResult = [
            'GRID' => [
                'ID' => 'design-list',
                'COLUMNS' => [
                    [
                        'id' => 'DESIGN_LIST_COUNT',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_COUNT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_TASK_CREATED_DATE',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_CREATED_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_CREATED_BY',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_CREATED_BY'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_PARTNER',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_PARTNER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_TASK_FILES',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_FILES'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_TASK_DESCRIPTION',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_DESCRIPTION'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_TASK_CLOSED_DATE',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_CLOSED_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_DONE_MAQUETTE',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_DONE_MAQUETTE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'DESIGN_LIST_MAQUETTES_SENDED',
                        'class' => 'design-list-header',
                        'name' => Loc::getMessage('DESIGN_LIST_MAQUETTES_SENDED'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ],
                'FILTER' => [
                    [
                        'id' => 'CREATED_DATE',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_CREATED_DATE'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'CREATED_BY',
                        'name' => Loc::getMessage('DESIGN_LIST_CREATED_BY'),
                        'type' => 'list',
                        'items' => $users,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => 'PARTNER',
                        'name' => Loc::getMessage('DESIGN_LIST_PARTNER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'CLOSED_DATE',
                        'name' => Loc::getMessage('DESIGN_LIST_TASK_CLOSED_DATE'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'IS_COMPLETED',
                        'name' => Loc::getMessage('DESIGN_LIST_IS_COMPLETED'),
                        'type' => 'checkbox',
                        'default' => true
                    ]
                ]
            ]
        ];

        $arFilter = $this->getFilter();
        $tasks = $this->getTasks($arFilter);
        $partners = $this->getPartners();

        foreach ($tasks as $task) {
            $created_date = (new DateTime($task['CREATED_DATE']))->format('d.m.Y H:i');

            if ((int)$task['STATUS'] === 5 && !empty($task['CLOSED_DATE'])) {
                $closed_date = (new DateTime($task['CLOSED_DATE']))->format('d.m.Y H:i');
            } else {
                $closed_date = '';
            }

            $link = '';
            $partnerName = '';

            if (!empty($task['UF_CRM_TASK'])) {
                foreach ($task['UF_CRM_TASK'] as $UF_CRM_TASK) {
                    if (strripos($UF_CRM_TASK,'D_') === 0) {
                        $id = mb_substr($UF_CRM_TASK, 2);

                        foreach ($partners as $partner) {
                            if ($partner['ID'] === $id) {
                                $partnerName = trim($partner['CONTACT_LAST_NAME']) . ' ' . trim($partner['CONTACT_NAME']) . ' ' . trim($partner['CONTACT_SECOND_NAME']);
                                $link = '<a class="design-list-partner-link" href="/b/edp/?deal_id=' . $partner['ID'] . '/">' . $partnerName . '</a>';
                            }
                        }

                        if (empty($partnerName)) {
                            $edz = CCrmDeal::GetListEx([], ['ID' => $id], false, false, ['ID', PART_ZAIM], [])->Fetch();

                            if (!empty($edz)) {
                                foreach ($partners as $partner) {
                                    if ($edz[PART_ZAIM] === $partner['CONTACT_ID']) {
                                        $partnerName = trim($partner['CONTACT_LAST_NAME']) . ' ' . trim($partner['CONTACT_NAME']) . ' ' . trim($partner['CONTACT_SECOND_NAME']);
                                        $link = '<a class="design-list-partner-link" href="/b/edp/?deal_id=' . $partner['ID'] . '/">' . $partnerName . '</a>';
                                    }
                                }
                            }
                        }
                    } else if (strripos($UF_CRM_TASK,'C_') === 0) {
                        $id = mb_substr($UF_CRM_TASK, 2);

                        foreach ($partners as $partner) {
                            if ($partner['CONTACT_ID'] === $id) {
                                $partnerName = trim($partner['CONTACT_LAST_NAME']) . ' ' . trim($partner['CONTACT_NAME']) . ' ' . trim($partner['CONTACT_SECOND_NAME']);
                                $link = '<a class="design-list-partner-link" href="/b/edp/?deal_id=' . $partner['ID'] . '/">' . $partnerName . '</a>';
                            }
                        }
                    } else {
                        $partnerName = 'Не партнер';
                    }
                }
            }

            $files = '';

            if (!empty($task['FILE_DATA'])) {
                $files = DesignWidget::renderFiles($task['FILE_DATA']);
            }

            $actions = [
                [
                    'ONCLICK' => 'window.open("/company/personal/user/538/tasks/task/view/' . $task['ID'] . '/", "_blank");',
                    'DEFAULT' => true
                ]
            ];

            if ((int)$task['STATUS'] !== 5) {
                $actions[] = [
                    'ICONCLASS' => 'icon-add-files',
                    'TEXT' => Loc::getMessage('DESIGN_LIST_ADD_FILES_TO_TASK'),
                    'ONCLICK' => 'addFilesToTask(' . $task['ID'] . ', ' . json_encode($partnerName) . ', ' . json_encode($users[$task['CREATED_BY']]) . ');',
                    'DEFAULT' => false
                ];

                if ($task['UF_MAQUETTES_SENDED'] !== '1') {
                    $actions[] = [
                        'ICONCLASS' => 'icon-email',
                        'TEXT' => Loc::getMessage('DESIGN_LIST_SEND_MAQUETTE_TO_PARTNER'),
                        'ONCLICK' => 'sendMaquette(' . $task['ID'] . ', ' . $task['CREATED_BY'] . ');',
                        'DEFAULT' => false
                    ];
                }

                $actions[] = [
                    'ICONCLASS' => 'icon-check',
                    'TEXT' => Loc::getMessage('DESIGN_LIST_FINISH_TASK'),
                    'ONCLICK' => 'finishTask(' . $task['ID'] . ');',
                    'DEFAULT' => false
                ];
            }

            $partner_files_link = '';

            if (!empty($task['UF_PARTNER_FILES'][0])) {
                $arr = \CFile::GetFileArray($task['UF_PARTNER_FILES'][0]);

                $partner_files_link = '<a href="' . $arr['SRC'] . '">' . $arr['FILE_NAME'] . '</a>';
                $task['DESCRIPTION'] = preg_replace('/\[URL=[\S\s]+\[\/URL]/m', $partner_files_link, $task['DESCRIPTION']);
            }

            $this->arResult['GRID']['ROWS'][] = [
                'id' => $task['ID'],
                'data' => [
                    'DESIGN_LIST_COUNT' => $task['COUNT'],
                    'DESIGN_LIST_TASK_CREATED_DATE' => '<a href="/company/personal/user/538/tasks/task/view/' . $task['ID'] . '/">' . $created_date . '</a>',
                    'DESIGN_LIST_CREATED_BY' => $task['UF_IS_FROM_PARTNER_TASK'] === '1' ? $partnerName : $users[$task['CREATED_BY']],
                    'DESIGN_LIST_PARTNER' => $link,
                    'DESIGN_LIST_TASK_FILES' => $partner_files_link,
                    'DESIGN_LIST_TASK_DESCRIPTION' => '<div class="design-list-text-wrap" data-role="text-wrap" data-hidden="true">' . $task['DESCRIPTION'] . '</div>',
                    'DESIGN_LIST_TASK_CLOSED_DATE' => $closed_date,
                    'DESIGN_LIST_DONE_MAQUETTE' => '<div class="design-list-files design-list-text-wrap" data-role="text-wrap" data-hidden="true">' . $files . '</div>',
                    'DESIGN_LIST_MAQUETTES_SENDED' => $task['UF_MAQUETTES_SENDED'] === '1' ? 'Да' : 'Нет'
                ],
                'actions' => $actions,
                'editable' => false
            ];
        }

        $this->includeComponentTemplate();
    }
}