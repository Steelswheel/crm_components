<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\DealTable;
use Bitrix\Crm\Observer\Entity\ObserverTable;
use Bitrix\Crm\Timeline\CommentEntry;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vaganov\Helper;
use Bitrix\Main\Entity\Query;
use Vaganov\Notification;

Loader::IncludeModule('crm');

class EdzList extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function addCommentAction($dealId, $text) {
        $deal = DealTable::getList([
            'select' => [
                'ID',
                'STAGE_ID',
                'ASSIGNED_BY_ID'
            ],
            'filter' => [
                'ID' => $dealId
            ]
        ])->fetch();

        if ($deal) {
            global $USER;

            $commentData = [
                'AUTHOR_ID' => $USER->GetID(), //Идентификатор автора комментариев
                'TEXT' => $text, //Текст комментария
                'BINDINGS' => [
                    [
                        'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal, //ID типа сущности
                        'ENTITY_ID' => (int)$dealId //ID сущности (cделки, лида и т.д.)
                    ]
                ]
            ];

            if (!empty($data['files'])) {
                $commentData['SETTINGS'] = [
                    'HAS_FILES' => 'Y'
                ];

                $commentData['FILES'] = $data['files'];
            } else {
                $commentData['SETTINGS'] = [
                    'HAS_FILES' => 'N'
                ];
            }

            $entryID = CommentEntry::create($commentData);

            if (!empty($entryID)) {
                Helper::includeHlTable('ext_crm_timeline');

                \ExtCrmTimelineTable::add([
                    'UF_TIMELINE_ID' => $entryID,
                    'UF_FROM_PARTNER' => false,
                    'UF_SHOW_TO_PARTNER' => false
                ]);

                $observers = ObserverTable::getList([
                    'select' => [
                        'USER_ID'
                    ],
                    'filter' => [
                        'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
                        'ENTITY_ID' => $dealId
                    ]
                ])->fetchAll();

                Helper::includeHlTable('stage_id_settings');

                $settingsRes = \StageIdSettingsTable::getList([
                    'select' => [
                        'UF_ALLOWED_USERS',
                        'UF_ALLOWED_USERS_NO_ALARM'
                    ],
                    'filter' => ['UF_STAGE_ID' => $deal['STAGE_ID']]
                ])->fetch();

                $no_alarm = array_map(function ($item) {
                    return (int)$item;
                }, $settingsRes['UF_ALLOWED_USERS_NO_ALARM']);

                foreach ($settingsRes['UF_ALLOWED_USERS'] as $user) {
                    if (!in_array((int)$user, $no_alarm)) {
                        Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $user);
                    }
                }

                foreach ($observers as $observer) {
                    if (!in_array((int)$observer['USER_ID'], $no_alarm)) {
                        Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $observer['USER_ID']);
                    }
                }

                $observers_ids = array_map(function ($item) {
                    return $item['USER_ID'];
                }, $observers);

                if ($USER->GetID() !== $deal['ASSIGNED_BY_ID'] && !in_array((int)$USER->GetID(), $observers_ids)) {
                    Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $deal['ASSIGNED_BY_ID']);
                }

                return (new \DateTime())->format('d.m.Y') . ' - ' . $text . ' (' . $USER->GetLastName(). ")\n";
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function reloadQuickFilterAction() {
        $filterOption = new Bitrix\Main\UI\Filter\Options('edz-list');
        $filterData = $filterOption->getFilter([]);

        $arFilter = [
            'CATEGORY_ID' => 8
        ];

        if (empty($filterData)) {
            $arDealStages = array_filter(CCrmDeal::GetStageNames(8), function ($key) {
                return ($key !== 'C8:LOSE' && $key !== 'C8:WON');
            }, ARRAY_FILTER_USE_KEY);

            $arFilter['STAGE_ID'] = array_keys($arDealStages);
        } else {
            if (!empty($filterData['DATE_CREATE_from'])) {
                $arFilter['>=DATE_CREATE'] = $filterData['DATE_CREATE_from'];
            }

            if (!empty($filterData['DATE_CREATE_to'])) {
                $arFilter['<=DATE_CREATE'] = $filterData['DATE_CREATE_to'];
            }

            foreach ($filterData as $k => $v) {
                switch($k) {
                    case 'PRESET_ID':
                    case 'FILTER_ID':
                    case 'FILTER_APPLIED':
                    case 'FIND':
                    case 'DATE_CREATE_days':
                    case 'DATE_CREATE_month':
                    case 'DATE_CREATE_datesel':
                    case 'DATE_CREATE_quarter':
                    case 'DATE_CREATE_year':
                    case 'DATE_CREATE_from':
                    case 'DATE_CREATE_to':
                    case 'STAGE_ID':
                        break;
                    case 'EDZ_LIST_PARTNER':
                        $filter = [];

                        $filterString = '%' . trim($v) . '%';

                        $arStrings = explode(' ', trim($v));

                        if (!empty($arStrings[0])) {
                            $contFilter['LAST_NAME'] = '%' . $arStrings[0] . '%';

                            if (!empty($arStrings[1])) {
                                $contFilter['NAME'] = '%' . $arStrings[1] . '%';
                            }

                            if (!empty($arStrings[2])) {
                                $contFilter['SECOND_NAME'] = '%' . $arStrings[2] . '%';
                            }

                            $filter = [];

                            $cont = CCrmContact::GetList([], $contFilter, ['ID']);

                            while ($res = $cont->GetNext()) {
                                $filter[] = $res['ID'];
                            }
                        }

                        if (empty($filter)) {
                            $filter[] = '-1';
                        }

                        $field = PART_ZAIM;

                        if (!empty($filter)) {
                            $arFilter[$field] = $filter;
                        } else {
                            $arFilter[$field] = false;
                        }
                        break;
                    case 'EDZ_LIST_BORROWER':
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

                            $filter = [];

                            $cont = CCrmContact::GetList([], $contFilter, ['ID']);

                            while ($res = $cont->GetNext()) {
                                $filter[] = $res['ID'];
                            }
                        }

                        if (empty($filter)) {
                            $filter[] = '-1';
                        }

                        $field = 'CONTACT_ID';

                        if (!empty($filter)) {
                            $arFilter[$field] = $filter;
                        } else {
                            $arFilter[$field] = false;
                        }
                        break;
                    default:
                        $arFilter[$k] = $v;
                        break;
                }
            }
        }

        $db_res = CCrmDeal::GetListEx(
            [],
            $arFilter,
            false,
            false,
            [
                'STAGE_ID'
            ],
            []
        );

        $deals = [];
        $arDealsCount = [];

        while ($deal = $db_res->Fetch()) {
            $deals[] = $deal['STAGE_ID'];
            $arDealsCount[$deal['STAGE_ID']] = 0;
        }

        foreach ($deals as $deal) {
            $arDealsCount[$deal]++;
        }

        return $arDealsCount;
    }

    public function getFilter($arDealStages) {
        $arFilter = [
            'CATEGORY_ID' => 8,
            'STAGE_ID' => array_keys($arDealStages)
        ];

        $filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['GRID']['ID']);

        $filterData = $filterOption->getFilter([]);

        $this->arResult['FILTER_DATA'] = $filterData;

        if (!empty($filterData['DATE_CREATE_from'])) {
            $arFilter['>=DATE_CREATE'] = $filterData['DATE_CREATE_from'];
        }

        if (!empty($filterData['DATE_CREATE_to'])) {
            $arFilter['<=DATE_CREATE'] = $filterData['DATE_CREATE_to'];
        }

        foreach ($filterData as $k => $v) {
            switch($k) {
                case 'PRESET_ID':
                case 'FILTER_ID':
                case 'FILTER_APPLIED':
                case 'FIND':
                case 'DATE_CREATE_days':
                case 'DATE_CREATE_month':
                case 'DATE_CREATE_datesel':
                case 'DATE_CREATE_quarter':
                case 'DATE_CREATE_year':
                case 'DATE_CREATE_from':
                case 'DATE_CREATE_to':
                    break;
                case 'EDZ_LIST_PARTNER':
                    $filter = [];

                    $filterString = '%' . trim($v) . '%';

                    $arStrings = explode(' ', trim($v));

                    if (!empty($arStrings[0])) {
                        $contFilter['LAST_NAME'] = '%' . $arStrings[0] . '%';

                        if (!empty($arStrings[1])) {
                            $contFilter['NAME'] = '%' . $arStrings[1] . '%';
                        }

                        if (!empty($arStrings[2])) {
                            $contFilter['SECOND_NAME'] = '%' . $arStrings[2] . '%';
                        }

                        $filter = [];

                        $cont = CCrmContact::GetList([], $contFilter, ['ID']);

                        while ($res = $cont->GetNext()) {
                            $filter[] = $res['ID'];
                        }
                    }

                    if (empty($filter)) {
                        $filter[] = '-1';
                    }

                    $field = PART_ZAIM;

                    if (!empty($filter)) {
                        $arFilter[$field] = $filter;
                    } else {
                        $arFilter[$field] = false;
                    }
                    break;
                case 'EDZ_LIST_BORROWER':
                    $filter = [];

                    $arStrings = explode(' ', trim($v));

                    if (!empty($arStrings[0])) {
                        $contFilter['LAST_NAME'] = '%' . $arStrings[0] . '%';
                        $contFilterTreeFace['UF_GUARANTOR_LAST_NAME'] = '%' . $arStrings[0] . '%';

                        if (!empty($arStrings[1])) {
                            $contFilter['NAME'] = '%' . $arStrings[1] . '%';
                            $contFilterTreeFace['UF_GUARANTOR_NAME'] = '%' . $arStrings[1] . '%';
                        }

                        if (!empty($arStrings[2])) {
                            $contFilter['SECOND_NAME'] = '%' . $arStrings[2] . '%';
                            $contFilterTreeFace['UF_GUARANTOR_SECOND_NAME'] = '%' . $arStrings[2] . '%';
                        }

                        $filter = [];

                        $cont = CCrmContact::GetList([], $contFilter, ['ID']);
                        $contTreeFace = CCrmDeal::GetList([], $contFilterTreeFace, ['ID','CONTACT_ID']);

                        while ($res = $cont->GetNext()) {
                            $filter[] = $res['ID'];
                        }
                        while ($res = $contTreeFace->GetNext()) {
                            $filter[] = $res['CONTACT_ID'];
                        }
                    }

                    if (empty($filter)) {
                        $filter[] = '-1';
                    }

                    $field = 'CONTACT_ID';

                    if (!empty($filter)) {
                        $arFilter[$field] = $filter;
                    } else {
                        $arFilter[$field] = false;
                    }
                    break;
                default:
                    $arFilter[$k] = $v;
                    break;
            }
        }

        return $arFilter;
    }

    public function getPartners() {
        $partnersQuery = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10
            ],
            false,
            false,
            [
                'ID',
                PARTNER_STATUS,
                MANAGING_PART_ID,
                IS_AGENT,
                'CONTACT_ID',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_LAST_NAME',
                'CATEGORY_ID',
                'UF_PARTNER_REGISTER_ADDRESS_DADATA',
                'UF_IS_RELIABLE_PARTNER',
                'UF_IS_SALE_PARTNER',
            ]
        );

        $partners = [];

        while ($partner = $partnersQuery->Fetch()) {
            $partners[$partner['CONTACT_ID']] = $partner;
        }

        return $partners;
    }

    public function getTasksAll($dealIds){
        Loader::includeModule("tasks");
        $statusList = array(
            "-1" => "Просрочена",
            "-2" => "Не просмотрена",
            "-3" => "Ждет выполнения", //х з что за статус. В описании ничего нет.
            "2" => "Ждет выполнения",
            "3" => "Выполняется",
            "4" => "Ожидает подтверждения",
            "5" => "Завершена",
            "6" => "Отложена",
        );

        $data = [];
        foreach ($dealIds as $dealId){
            $arFilter = array(
                "UF_CRM_TASK" => 'D_' . $dealId,
                "!STATUS" => ["5"]
            );

            $taskList = CTasks::GetList([], $arFilter, ["*", "UF_*"]);
            $taskAr = [];
            while ($task = $taskList->GetNext()) {
                 /* $data[$dealId] = [
                    'ID' =>  $task["ID"],
                    'TITLE' => $task["TITLE"],
                    'FIO' => $task["CREATED_BY_LAST_NAME"] . " " . $task["CREATED_BY_NAME"] . " " . $task["CREATED_BY_SECOND_NAME"],
                    'RESPONSIBLE' => $task["RESPONSIBLE_LAST_NAME"] . " " . $task["RESPONSIBLE_NAME"] . " " . $task["RESPONSIBLE_SECOND_NAME"],
                    'CREATED_DATE' => $task["CREATED_DATE"],
                    'STATUS' => $statusList[$task["STATUS"]],
                    'DEADLINE' => ($task["DEADLINE"])?$task["DEADLINE"]:"-",
                ];*/
                $taskAr[] = " <div class='taskAllItem'><a href='/company/personal/user/{$task["RESPONSIBLE_ID"]}/tasks/task/view/{$task["ID"]}/'>
                   ".$task["TITLE"]."</a></div>";
            }
            $data[$dealId] = implode('',$taskAr);


        }

        return $data;
    }

    public function getMailPhones($IDs) {
        $phoneMailQuery = new Query(Bitrix\Crm\FieldMultiTable::getEntity());

        $phoneMailQuery
            ->setSelect([
                'ELEMENT_ID',
                'TYPE_ID',
                'VALUE'
            ])
            ->setFilter([
                'ELEMENT_ID' => $IDs,
                'ENTITY_ID' => 'CONTACT',
                'TYPE_ID' => ['PHONE', 'EMAIL']
            ])
            ->exec();

        $phones = [];
        $emails = [];

        foreach ($phoneMailQuery->fetchAll() as $item) {
            if ($item['TYPE_ID'] === 'PHONE') {
                $phones[$item['ELEMENT_ID']][] = $item['VALUE'];
            }

            if ($item['TYPE_ID'] === 'EMAIL') {
                $emails[$item['ELEMENT_ID']][] = $item['VALUE'];
            }
        }

        return [
            'PHONES' => $phones,
            'EMAILS' => $emails
        ];
    }

    public function getPartnersBirthdays($IDs, $deals) {
        $cont = CCrmContact::GetList([], ['ID' => $IDs], ['ID', 'UF_DATE_OF_BIRTH']);

        $birthdays = [];

        while ($contact = $cont->Fetch()) {
            if (!empty($contact['UF_DATE_OF_BIRTH'])) {
                $today = (new DateTime())->format('d.m');
                $date = (new DateTime($contact['UF_DATE_OF_BIRTH']))->format('d.m');

                if ($date === $today) {
                    $birthdays[$contact['ID']] = true;
                }
            }
        }

        foreach ($deals as &$deal) {
            if ($birthdays[$deal[PART_ZAIM]]) {
                $deal['IS_BIRTHDAY_TODAY'] = true;
            }
        }

        return $deals;
    }

    public function getPayments($deal)
    {
        $result = [];

        if (!empty($deal['UF_TRANCHE_1_DATA']) && !empty($deal['UF_TRANCHE_1_SUM'])) {
            $result[] = [
                'SUM' => $deal['UF_TRANCHE_1_SUM'],
                'DATE' => $deal['UF_TRANCHE_1_DATA'],
                'DESCRIPTION' => '1-ТРАНШ: '
            ];
        }

        if (!empty($deal['UF_TRANCHE_2_DATA']) && !empty($deal['UF_TRANCHE_2_SUM'])) {
            $result[] = [
                'SUM' => $deal['UF_TRANCHE_2_SUM'],
                'DATE' => $deal['UF_TRANCHE_2_DATA'],
                'DESCRIPTION' => '2-ТРАНШ: '
            ];
        }

        if (!empty($deal['UF_TRANCHE_3_DATA']) && !empty($deal['UF_TRANCHE_3_SUM'])) {
            $result[] = [
                'SUM' => $deal['UF_TRANCHE_3_SUM'],
                'DATE' => $deal['UF_TRANCHE_3_DATA'],
                'DESCRIPTION' => '3-ТРАНШ: '
            ];
        }

        if (!empty($deal['UF_TRANCHE_4_DATA']) && !empty($deal['UF_TRANCHE_4_SUM'])) {
            $result[] = [
                'SUM' => $deal['UF_TRANCHE_4_SUM'],
                'DATE' => $deal['UF_TRANCHE_4_DATA'],
                'DESCRIPTION' => '4-ТРАНШ: '
            ];
        }

        return $result;
    }

    public function getLoanRepayments($deal)
    {
        $result = [];

        //'МСК ДАТА' => 'UF_CRM_1567499237'
        //'МСК СУММА' => 'UF_CRM_1567499259'
        //'РСК ДАТА' => 'UF_CRM_1567499436'
        //'РСК СУММА' => 'UF_CRM_1567499470'
        //'СОБСТВЕННЫЕ СРЕДСТВА ДАТА' => 'UF_CRM_1567492654'
        //'СОБСТВЕННЫЕ СРЕДСТВА СУММА' => 'UF_CRM_1567492772'

        if (!empty($deal['UF_CRM_1567499237']) && !empty($deal['UF_CRM_1567499259'])) {
            $result[] = [
                'SUM' => $deal['UF_CRM_1567499259'],
                'DATE' => $deal['UF_CRM_1567499237'],
                'DESCRIPTION' => 'МСК: '
            ];
        }

        if (!empty($deal['UF_CRM_1567499436']) && !empty($deal['UF_CRM_1567499470'])) {
            $result[] = [
                'SUM' => $deal['UF_CRM_1567499470'],
                'DATE' => $deal['UF_CRM_1567499436'],
                'DESCRIPTION' => 'РСК: '
            ];
        }

        if (!empty($deal['UF_CRM_1567492654']) && !empty($deal['UF_CRM_1567492772'])) {
            $result[] = [
                'SUM' => $deal['UF_CRM_1567492772'],
                'DATE' => $deal['UF_CRM_1567492654'],
                'DESCRIPTION' => 'СОБСТВЕННЫЕ СРЕДСТВА: '
            ];
        }

        return $result;
    }

    public function getPayPayments($deal)
    {
        $result = [];

        if (!empty($deal[COMISSION_DATA]) && !empty($deal[COMISSION_SUM])) {
            $result[] = [
                'SUM' => $deal[COMISSION_SUM],
                'DATE' => $deal[COMISSION_DATA],
                'DESCRIPTION' => 'ЗАЧИСЛЕНИЕ ПАЯ: '
            ];
        }

        //для старых сделок

        if (!empty($deal[CONTRIBUTIONS_1_DATA]) && !empty($deal[CONTRIBUTIONS_1_SUM])) {
            $result[] = [
                'SUM' => $deal[CONTRIBUTIONS_1_SUM],
                'DATE' => $deal[CONTRIBUTIONS_1_DATA],
                'DESCRIPTION' => 'ЗАЧИСЛЕНИЕ ВКЛАДА И ПАЯ 1-ТРАНШ: '
            ];
        }

        if (!empty($deal[CONTRIBUTIONS_2_DATA]) && !empty($deal[CONTRIBUTIONS_2_SUM])) {
            $result[] = [
                'SUM' => $deal[CONTRIBUTIONS_2_SUM],
                'DATE' => $deal[CONTRIBUTIONS_2_DATA],
                'DESCRIPTION' => 'ЗАЧИСЛЕНИЕ ВКЛАДА И ПАЯ 2-ТРАНШ: '
            ];
        }

        if (!empty($deal[CONTRIBUTIONS_3_DATA]) && !empty($deal[CONTRIBUTIONS_3_SUM])) {
            $result[] = [
                'SUM' => $deal[CONTRIBUTIONS_3_SUM],
                'DATE' => $deal[CONTRIBUTIONS_3_DATA],
                'DESCRIPTION' => 'ЗАЧИСЛЕНИЕ ВКЛАДА И ПАЯ 3-ТРАНШ: '
            ];
        }

        return $result;
    }

    private function getComments($ids)
    {
        $entity = Helper::includeHlTable('ext_crm_timeline');

        $query = new Query(TimelineTable::getEntity());

        $query
            ->registerRuntimeField('EXT', [
                'data_type' => $entity,
                'reference' => [
                    '=this.ID' => 'ref.UF_TIMELINE_ID',
                ],
            ])
            ->registerRuntimeField('BIND', [
                'data_type' => 'Bitrix\Crm\Timeline\Entity\TimelineBindingTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->setSelect([
                'ID',
                'COMMENT',
                'AUTHOR_ID',
                'CREATED',
                'ENTITY_ID' => 'BIND.ENTITY_ID',
                'FROM_PARTNER' => 'EXT.UF_FROM_PARTNER',
                'SHOW_TO_PARTNER' => 'EXT.UF_SHOW_TO_PARTNER'
            ])
            ->setOrder([
                'ID' => 'DESC'
            ])
            ->setFilter([
                'TYPE_ID' => 7,
                'ENTITY_ID' => $ids,
                'BIND.ENTITY_TYPE_ID' => 2,
                '!=COMMENT' => null
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $comment) {
            $result[$comment['ENTITY_ID']][] = (new \DateTime($comment['CREATED']))->format('d.m.Y') . ' - ' . $comment['COMMENT'] . ' (' . (\CUser::GetByID($comment['AUTHOR_ID'])->Fetch())['LAST_NAME'] . ")\n";
        }

        return $result;
    }

    function executeComponent()
    {
        $users = Helper::getUsers(53);

        $arDealStages = CCrmDeal::GetStageNames(8);

        $aliasesClass = new \CUserFieldEnum;
        $loan_decision = [];

        foreach ($aliasesClass->GetList(['SORT'], ['ID' => [440, 466, 442, 443, 444]])->arResult as $item) {
            $loan_decision[$item['ID']] = $item['VALUE'];
        }

        $certType = [
            '493' => 'МСК',
            '494' => 'РСК'
        ];

        $this->arResult = [
            'GRID' => [
                'ID' => 'edz-list',
                'COLUMNS' => [
                    //РЕЕСТР ЗАЙМОВ
                    [
                        'id' => 'EDZ_LIST_REQUEST_DATE_TIME',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_REQUEST_DATE_TIME_SHORT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'DATE_CREATE',
                    ],
                    [
                        'id' => 'EDZ_LIST_MANAGER',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_MANAGER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_PARTNER',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_PARTNER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_BORROWER',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_BORROWER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_STAGE',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_STAGE_SHORT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'UF_DATE_LAST_STAGE',
                    ],
                    [
                        'id' => 'EDZ_LIST_CONTRACT_DATE_NUMBER',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_CONTRACT_DATE_NUMBER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_SUM',
                        'class' => 'edz-list-header edz-list-header-biege',
                        'name' => Loc::getMessage('EDZ_LIST_SUM_PROGRAM'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_PAYMENTS',
                        'class' => 'edz-list-header edz-list-header-biege',
                        'name' => Loc::getMessage('EDZ_LIST_PAYMENTS'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_PAY',
                        'class' => 'edz-list-header edz-list-header-biege',
                        'name' => Loc::getMessage('EDZ_LIST_PAY'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_LOAN_REPAYMENT',
                        'class' => 'edz-list-header edz-list-header-biege',
                        'name' => Loc::getMessage('EDZ_LIST_LOAN_REPAYMENT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_TASKS_ALL',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_TASKS_ALL'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_NOTE',
                        'class' => 'edz-list-header',
                        'name' => Loc::getMessage('EDZ_LIST_NOTE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_SOLUTION',
                        'class' => 'edz-list-header edz-list-header-green',
                        'name' => Loc::getMessage('EDZ_LIST_SOLUTION'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_TRANCHE',
                        'class' => 'edz-list-header edz-list-header-green',
                        'name' => Loc::getMessage('EDZ_LIST_TRANCHE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDZ_LIST_EMPLOYEE_COMMENT',
                        'class' => 'edz-list-header edz-list-header-green',
                        'name' => Loc::getMessage('EDZ_LIST_EMPLOYEE_COMMENT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ],
                'FILTER' => [
                    [
                        'id' => 'DATE_CREATE',
                        'name' => Loc::getMessage('EDZ_LIST_REQUEST_DATE_TIME'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => NUMBER_DZ,
                        'name' => Loc::getMessage('EDZ_LIST_CONTRACT_NUMBER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => DATE_DZ,
                        'name' => Loc::getMessage('EDZ_LIST_CONTRACT_DATE'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'ASSIGNED_BY_ID',
                        'name' => Loc::getMessage('EDZ_LIST_MANAGER'),
                        'type' => 'list',
                        'items' => $users,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => 'STAGE_ID',
                        'name' => Loc::getMessage('EDZ_LIST_STAGE'),
                        'type' => 'list',
                        'items' => $arDealStages,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => 'EDZ_LIST_PARTNER',
                        'name' => Loc::getMessage('EDZ_LIST_PARTNER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'EDZ_LIST_BORROWER',
                        'name' => Loc::getMessage('EDZ_LIST_BORROWER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => USED_CERTIFICATES,
                        'name' => Loc::getMessage('EDZ_LIST_USED_CERTIFICATES'),
                        'type' => 'list',
                        'items' => $certType,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ]
                ]
            ]
        ];

        $this->arResult['MSK_STAGES'] = CCrmDeal::GetStages(8);

        $arDealStages = array_filter($arDealStages, function ($key) {
            return ($key !== 'C8:LOSE' && $key !== 'C8:WON');
        }, ARRAY_FILTER_USE_KEY);

        $arFilter = $this->getFilter($arDealStages);
        $this->arResult['FILTER_ARRAY'] = $arFilter;

        $partners = $this->getPartners();

        $grid_options = new CGridOptions($this->arResult['GRID']['ID']);

        $sortOption = $grid_options->GetSorting();

        if (!$sortOption['sort']) {
            $sortOption['sort'] = ['DATE_CREATE' => 'DESC'];
        }

        $deals = CCrmDeal::GetListEx(
            $sortOption['sort'],
            $arFilter,
            false,
            false,
            [
                COMISSION_DATA,
                COMISSION_SUM,
                MEMBERSHIP_FEE_DATA,
                MEMBERSHIP_FEE_SUM,
                DOU_DATA_FACT,
                DOU_SUM_FACT,
                CONTRIBUTIONS_1_DATA,
                CONTRIBUTIONS_1_SUM,
                CONTRIBUTIONS_2_DATA,
                CONTRIBUTIONS_2_SUM,
                CONTRIBUTIONS_3_DATA,
                CONTRIBUTIONS_3_SUM,
                'UF_CRM_1567492654',
                'UF_CRM_1567492772',
                'UF_CRM_1567499237',
                'UF_CRM_1567499259',
                'UF_CRM_1567499436',
                'UF_CRM_1567499470',
                'UF_TRANCHE_1_DATA',
                'UF_TRANCHE_1_SUM',
                'UF_TRANCHE_2_DATA',
                'UF_TRANCHE_2_SUM',
                'UF_TRANCHE_3_DATA',
                'UF_TRANCHE_3_SUM',
                'UF_TRANCHE_4_DATA',
                'UF_TRANCHE_4_SUM',
                PART_ZAIM,
                'CATEGORY_ID',
                'ID',
                'DATE_CREATE',
                'UF_NUMBER_DZ',
                'UF_DATA_DZ',
                'ASSIGNED_BY_ID',
                'CONTACT_LAST_NAME',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'OPPORTUNITY',
                'LOAN_PROGRAM',
                'STAGE_ID',
                'COMMENTS',
                LOAN_DECISION_KZ,
                TRANSHEE_LPR,
                LOAN_COMMENT_KZ,
                LOAN_PROGRAM,
                'CONTACT_ID',
                'UF_THIRD_PARTY_CONTRIBUTION',
                'UF_THIRD_PARTY_CONTRIBUTION_SELLER',
                'UF_GUARANTOR_LAST_NAME',
                'UF_GUARANTOR_NAME',
                'UF_GUARANTOR_SECOND_NAME',
                'UF_LOAN_DECISION_DATE',
                'UF_TRANSACTION_CATEGORY',

            ]
        );

        $this->arResult['DEALS_STAGES_COUNT'] = [];

        foreach ($this->arResult['MSK_STAGES'] as $stageKey => $stageValue) {
            $this->arResult['DEALS_STAGES_COUNT'][$stageKey] = 0;
        }

        if (in_array('ASSIGNED_BY_ID', array_keys($this->arResult['FILTER_DATA']))) {
            while ($deal = $deals->GetNext()) {
                $this->arResult['DEALS_STAGES_COUNT'][$deal['STAGE_ID']]++;
            }
        } else {
            $allDeals = CCrmDeal::GetListEx(
                [],
                [
                    'CATEGORY_ID' => 8,
                    'STAGE_ID' => array_keys($this->arResult['MSK_STAGES'])
                ],
                false,
                false,
                ['STAGE_ID']
            );

            while ($deal = $allDeals->Fetch()) {
                $this->arResult['DEALS_STAGES_COUNT'][$deal['STAGE_ID']]++;
            }

            unset($allDeals);
        }

        $grid_options = new Bitrix\Main\Grid\Options($this->arResult['GRID']['ID']);
        $nav_params = $grid_options->GetNavParams();
        $deals->NavStart(isset($nav_params['nPageSize']) ? $nav_params['nPageSize'] : 10);
        $deals->bShowAll = true;

        $this->arResult['NAV_OBJECT'] = $deals;
        $this->arResult['ROWS_COUNT'] = $deals->SelectedRowsCount();

        $arDealId = [];
        $arDeals = [];
        $ufContactIDs = [];
        $contactIDs = [];

        while ($deal = $deals->GetNext()) {
            $arDeals[] = $deal;
            $arDealId[] = $deal['ID'];
            $ufContactIDs[] = $deal[PART_ZAIM];
            $contactIDs[] = $deal['CONTACT_ID'];
        }

        $dealStageHisRes = \Bitrix\Crm\History\Entity\DealStageHistoryTable::getList([
            'select' => [
                'ID',
                'OWNER_ID',
                'CREATED_TIME',
                'STAGE_ID'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => [
                'OWNER_ID' => $arDealId,
                'CATEGORY_ID' => 8
            ]
        ]);

        $arStageChangeDates = [];

        while ($element = $dealStageHisRes->fetch()) {
            $arStageChangeDates[$element['OWNER_ID']][$element['STAGE_ID']] = (new DateTime($element['CREATED_TIME']))->format('d.m.Y H:i');
        }

        $arDeals = $this->getPartnersBirthdays($ufContactIDs, $arDeals);

        $managingPartIDs = [];

        foreach ($partners as $key => $value) {
            if (!empty($value[MANAGING_PART_ID])) {
                $managingPartIDs[] = $value[MANAGING_PART_ID];
            }
        }

        $managingContactData = $this->getMailPhones($managingPartIDs);

        $res = CCrmDeal::GetListEx(
            [],
            [
                'CONTACT_ID' => $managingPartIDs,
                'CATEGORY_ID' => 10
            ],
            false,
            false,
            [
                'ID',
                'CONTACT_ID',
                'CONTACT_LAST_NAME'
            ],
            []
        );

        $edpInfo = [];

        while($r = $res->Fetch()) {
            $edpInfo[$r['CONTACT_ID']] = $r;
        }

        $contactData = $this->getMailPhones($ufContactIDs);
        $taskAll = $this->getTasksAll($arDealId);

        $dealIDs = [];

        foreach ($arDeals as $deal) {
            $dealIDs[] = $deal['ID'];
        }

        $comments = $this->getComments($dealIDs);

        foreach ($arDeals as $deal) {
            $payments = $this->getPayments($deal);

            $payments_sum = 0;

            if (!empty($payments)) {
                foreach ($payments as &$payment) {
                    $payments_sum += $payment['SUM'];
                    $payment['SUM'] = number_format($payment['SUM'], 2, ',', ' ');
                }
            }

            $payments_sum = number_format($payments_sum, 2, ',', ' ');

            $loan_repayments = $this->getLoanRepayments($deal);

            $loan_repayments_sum = 0;

            if (!empty($loan_repayments)) {
                foreach ($loan_repayments as &$payment) {
                    $loan_repayments_sum += $payment['SUM'];
                    $payment['SUM'] = number_format($payment['SUM'], 2, ',', ' ');
                }
            }

            $loan_repayments_sum = number_format($loan_repayments_sum, 2, ',', ' ');

            $pay_payments = $this->getPayPayments($deal);

            $pay_payments_sum = 0;

            if (!empty($pay_payments)) {
                foreach ($pay_payments as &$payment) {
                    $pay_payments_sum += (float)$payment['SUM'];
                    $payment['SUM'] = number_format($payment['SUM'], 2, ',', ' ');
                }
            }

            $pay_payments_sum = number_format($pay_payments_sum, 2, ',', ' ');

            $partnerWidget = [];
            $partnerWidget['is_partner_reestr'] = false;
            $partnerWidget['phones'] = $contactData['PHONES'];
            $partnerWidget['emails'] = $contactData['EMAILS'];
            $partnerWidget['managing_phones'] = $managingContactData['PHONES'];
            $partnerWidget['managing_emails'] = $managingContactData['EMAILS'];


            $partnerWidget['status'] = $partners[$deal[PART_ZAIM]][PARTNER_STATUS];
            $time = explode(' ', (new DateTime($deal['DATE_CREATE']))->format('d.m.Y H:i:s'));

            $dadata = json_decode($partners[$deal[PART_ZAIM]]['UF_PARTNER_REGISTER_ADDRESS_DADATA'], JSON_OBJECT_AS_ARRAY);

            if (!empty($dadata['timezone'])) {
                $currentDate = new DateTime();
                $currentDateTimestamp = $currentDate->getTimestamp() - $currentDate->getOffset();

                if (!empty($dadata['timezone'])) {
                    $timezoneH = (int)mb_substr($dadata['timezone'], 3);
                    $partnerWidget['time'] =  date('H:i', $currentDateTimestamp + ($timezoneH * 3600));
                }
            }

            $partnerWidget['is_sale_partner'] = !empty($partners[$deal[PART_ZAIM]]['UF_IS_SALE_PARTNER']);
            $partnerWidget['birthday'] = $deal['IS_BIRTHDAY_TODAY'];
            $partnerWidget['id'] = $partners[$deal[PART_ZAIM]]['ID'];
            $partnerWidget['is_agent'] = $partners[$deal[PART_ZAIM]][IS_AGENT] === '1';
            $partnerWidget['name'] = trim(trim($partners[$deal[PART_ZAIM]]['CONTACT_LAST_NAME'] . ' ' . $partners[$deal[PART_ZAIM]]['CONTACT_NAME']) . ' ' . $partners[$deal[PART_ZAIM]]['CONTACT_SECOND_NAME']);

            $partnerWidget['reliable_icon'] = $partners[$deal[PART_ZAIM]]['UF_IS_RELIABLE_PARTNER'] === '1';

            if ($partnerWidget['is_agent']) {
                $MANAGING_PART_ID = $partners[$deal[PART_ZAIM]][MANAGING_PART_ID];
                $partnerWidget['edp_id'] = $edpInfo[$MANAGING_PART_ID]['ID'];
                $partnerWidget['managing_partner'] = trim($edpInfo[$MANAGING_PART_ID]['CONTACT_LAST_NAME']);
                $partnerWidget['contact_id'] = $deal[PART_ZAIM];
                $partnerWidget['managing_contact_id'] = $MANAGING_PART_ID;
            } else {
                $PART_ZAIM = $partners[$deal[PART_ZAIM]]['CONTACT_ID'];
                $partnerWidget['edp_id'] = false;
                $partnerWidget['managing_partner'] = false;
                $partnerWidget['contact_id'] = $PART_ZAIM;
                $partnerWidget['managing_contact_id'] = false;
            }

            $link = '';
            $stage = '';

            if ((int)$deal['CATEGORY_ID'] === 8) {
                $link = '/b/edz/?deal_id=' . $deal['ID'] . '&show';
                $stage = !empty($this->arResult['MSK_STAGES'][$deal['STAGE_ID']]['NAME']) ? $this->arResult['MSK_STAGES'][$deal['STAGE_ID']]['NAME'] . '<br>(' . $arStageChangeDates[$deal['ID']][$deal['STAGE_ID']] . ')' : '';
            }

            $threeFace = '';

            if ($deal['UF_THIRD_PARTY_CONTRIBUTION'] === '1') {
                $threeFace = "<br>(3-e Лицо) {$deal['UF_GUARANTOR_LAST_NAME']} {$deal['UF_GUARANTOR_NAME']} {$deal['UF_GUARANTOR_SECOND_NAME']}";
            }

            if ($deal['UF_THIRD_PARTY_CONTRIBUTION_SELLER'] === '1') {
                $threeFace = "<br>(сберегатель) {$deal['UF_GUARANTOR_LAST_NAME']} {$deal['UF_GUARANTOR_NAME']} {$deal['UF_GUARANTOR_SECOND_NAME']}";
            }

            $decision_date = !empty($deal['UF_LOAN_DECISION_DATE']) ? (new \DateTime($deal['UF_LOAN_DECISION_DATE']))->format('d.m.Y H:i:s') : '';

            $k1 = $deal['UF_TRANSACTION_CATEGORY']
                ? "<span class='bg-danger text-white mr-1 px-1'>{$deal['UF_TRANSACTION_CATEGORY']}</span>"
                : "";

            $dealComments = $comments[$deal['ID']];

            if (!empty($dealComments)) {
                $c = '';

                foreach ($dealComments as $comment) {
                    $c .= $comment;
                }

                $c .= $deal['COMMENTS'];
            } else {
                $c = $deal['COMMENTS'];
            }

            $this->arResult['GRID']['ROWS'][] = [
                'id' => $deal['ID'],
                'data' => [
                    'EDZ_LIST_REQUEST_DATE_TIME' => '<div class="edz-list-flex-content"><a href="/b/edz/?deal_id=' . $deal['ID'] . '&show" data-role="partner-widget-link">' . $time[0] . '<br>' . $time[1] . '</a>' . '</div>',
                    'EDZ_LIST_MANAGER' => $users[$deal['ASSIGNED_BY_ID']],
                    'EDZ_LIST_PARTNER' => \Vaganov\PartnerWidget::partnerWrite($partnerWidget),
                    'EDZ_LIST_BORROWER' => $k1.$deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME'] . ' ' . $deal['CONTACT_SECOND_NAME'] . $threeFace,
                    'EDZ_LIST_STAGE' => $stage,
                    'EDZ_LIST_CONTRACT_DATE_NUMBER' => $deal['UF_NUMBER_DZ'] . ' ' . $deal['UF_DATA_DZ'],
                    'EDZ_LIST_SUM' => (!empty($deal[LOAN_PROGRAM]) && $deal[LOAN_PROGRAM] !== '1') ? number_format($deal['OPPORTUNITY'], 2, ',', ' ') . '<br>' . '(' . $deal[LOAN_PROGRAM] . ')' : number_format($deal['OPPORTUNITY'], 2, ',', ' '),
                    'EDZ_LIST_PAYMENTS' => !empty($payments) ? '<div data-vue-component="edz.list_payments" data-sum="' . $payments_sum . '" data-payments="' . str_replace('"', "'", json_encode($payments)) . '"></div>' : '',
                    'EDZ_LIST_PAY' => !empty($pay_payments) ? '<div data-vue-component="edz.list_payments" data-sum="' . $pay_payments_sum . '" data-payments="' . str_replace('"', "'", json_encode($pay_payments)) . '"></div>' : '',
                    'EDZ_LIST_LOAN_REPAYMENT' => !empty($loan_repayments) ? '<div data-vue-component="edz.list_payments" data-sum="' . $loan_repayments_sum . '" data-payments="' . str_replace('"', "'", json_encode($loan_repayments)) . '"></div>' : '',
                    'EDZ_LIST_TASKS_ALL'=> $taskAll[$deal['ID']],
                    'EDZ_LIST_NOTE' => '<div data-role="edz-list-note" data-comment="' . $c . '" data-open="false">' . $c . '</div>',
                    'EDZ_LIST_SOLUTION' => '<div>' . $loan_decision[$deal[LOAN_DECISION_KZ]] . '<br>' . $decision_date . '</div>',
                    'EDZ_LIST_TRANCHE' => '<div>' . $deal[TRANSHEE_LPR] . '</div>',
                    'EDZ_LIST_EMPLOYEE_COMMENT' => '<div class="edz-list-note" data-role="edz-list-note" data-open="false">' . $deal[LOAN_COMMENT_KZ] . '</div>'
                ],
                'actions' => [
                    [
                        'ICONCLASS' => 'icon-profile',
                        'TEXT' => Loc::getMessage('EDZ_LIST_EDP'),
                        'ONCLICK' => 'window.open("' . $link . '", "_blank");',
                        'DEFAULT' => true
                    ],
                    [
                        'ICONCLASS' => 'icon-note',
                        'TEXT' => Loc::getMessage('EDZ_LIST_ADD_COMMENT'),
                        'ONCLICK' => 'addComment(' . $deal['ID'] . ');',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-deal',
                        'TEXT' => Loc::getMessage('EDZ_LIST_DEAL'),
                        'ONCLICK' => 'BX.SidePanel.Instance.open("/crm/deal/details/' . $deal['ID'] . '/");',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-finance',
                        'TEXT' => Loc::getMessage('EDZ_LIST_FINANCIAL_RECORDS'),
                        'ONCLICK' => 'window.open("/finance_reestr/?deal_id=' . $deal['ID'] . '&show", "_blank");',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-list',
                        'TEXT' => Loc::getMessage('EDZ_LIST_TASK_LIST'),
                        'ONCLICK' => 'getTask(' . $deal['ID'] . ', ' . $deal['ASSIGNED_BY_ID'] . ')',
                        'DEFAULT' => false
                    ]
                ],
                'editable' => false,
                'link' => $link
            ];
        }

        $this->includeComponentTemplate();
    }
}