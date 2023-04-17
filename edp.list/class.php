<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Vaganov\Helper;

Loader::IncludeModule('crm');

global $USER;

class EdpList extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function reloadQuickFilterAction() {
        $filterOption = new Bitrix\Main\UI\Filter\Options('edp-list');
        $filterData = $filterOption->getFilter([]);

        $arFilter = [
            'CATEGORY_ID' => 10
        ];

        if (empty($filterData)) {
            $arEdpStages = array_filter(CCrmDeal::GetStageNames(10), function ($key) {
                return ($key !== 'C10:LOSE' && $key !== 'C10:WON');
            }, ARRAY_FILTER_USE_KEY);

            $arFilter['STAGE_ID'] = array_keys($arEdpStages);
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
                    case 'EDP_LIST_PARTNER':
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

                        if (!empty($filter)) {
                            $arFilter['CONTACT_ID'] = $filter;
                        }

                        break;
                    default:
                        if (!empty($v)) {
                            $arFilter[$k] = $v;
                        }

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

    public function addCommentAction($id, $comment) {
        global $USER;

        $result = '';
        $flag = true;

        if (empty($id) || $id === 'new') {
            $flag = false;
        }

        if (empty(trim($comment))) {
            $flag = false;
        }

        if ($flag) {
            $arDeal = CCrmDeal::GetListEx(
                ['ID' => 'asc'],
                ['ID' => $id],
                false,
                false,
                ['ID', 'COMMENTS']
            )->Fetch();

            $comment = date('d.m.Y') . ' - ' . $comment . ' (' . $USER->GetLastName() . ")\n" . $arDeal['COMMENTS'];

            $arFields = [
                'COMMENTS' => $comment,
                'IS_REESTR' => 1
            ];

            $dealObj = new CCrmDeal();
            $updateResult = $dealObj->Update($arDeal['ID'], $arFields);

            if ($updateResult) {
                $result = $comment;
            } else {
                $result = $dealObj->LAST_ERROR;
            }
        }

        return $result;
    }

    public function getFilter($arEdpStages) {
        $arFilter = [
            'CATEGORY_ID' => 10,
            'STAGE_ID' => array_keys($arEdpStages)
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
                case 'UF_IS_EDA':
                    if ($v === 'Y') {
                        $arFilter[$k] = '1';
                    } else {
                        $arFilter[$k] = '0';
                    }
                    break;
                case 'UF_IS_REQUEST_CATEGORY':
                    $contactIdsRes = \Bitrix\Crm\ContactTable::getList([
                        'select' => ['ID'],
                        'filter' => [

                            'LOGIC' => 'OR',
                            [
                                '>UF_RICE_PROGRAM_K1_1' => '0'
                            ],
                            [
                                '>UF_RICE_PROGRAM_K1_1_2' => '0'
                            ]


                        ]
                    ])->fetchAll();

                    if($v === '1'){

                        if($contactIdsRes){
                            $contactIds = array_map(function($i){return $i['ID'];},$contactIdsRes);
                            $arFilter['CONTACT_ID'] = $contactIds;
                        }else{
                            $arFilter['CONTACT_ID'] = false;
                        }
                    }

                    if($v === '2'){

                        if($contactIdsRes){
                            $contactIds = array_map(function($i){return $i['ID'];},$contactIdsRes);
                            $arFilter['!CONTACT_ID'] = $contactIds;
                        }else{
                            $arFilter['CONTACT_ID'] = false;
                        }
                    }



                    break;
                case 'UF_IS_RELIABLE_PARTNER':
                    if($v === 'STOP'){
                        $arFilter[PARTNER_STATUS] = '544';
                    }
                    if($v === '1'){
                        $arFilter[$k] = $v;
                    }
                    break;
                case 'UF_IS_SALE_PARTNER':
                    if($v === 'Y'){
                        $arFilter['UF_IS_SALE_PARTNER'] = 1;
                    }else{
                        $arFilter['UF_IS_SALE_PARTNER'] = '0';
                    }


                    break;
                case 'EDP_LIST_PARTNER':
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

                    if (!empty($filter)) {
                        $arFilter['CONTACT_ID'] = $filter;
                    } else {
                        $arFilter['CONTACT_ID'] = false;
                    }
                    break;
                default:
                    $arFilter[$k] = $v;
                    break;
            }
        }

        return $arFilter;
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
                'ENTITY_ID' => 'CONTACT',
                'TYPE_ID' => ['PHONE', 'EMAIL'],
                'ELEMENT_ID' => $IDs
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

    public function getPartnersInfo($edpIDs, $managers) {
        $partnersDealsQuery = new Query(Bitrix\Crm\DealTable::getEntity());

        $partnersDealsQuery
            ->registerRuntimeField('DEAL', [
                'data_type' => 'Bitrix\Crm\DealTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.CONTACT_ID',
                ],
            ])
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'EDP' => 'DEAL.ID',
                'ID',
                'DATE_CREATE',
                'ASSIGNED_BY_ID',
                'STAGE_ID',
                'CATEGORY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'PROGRAM' => 'UF_CRM_1518969192'
            ])
            ->setFilter([
                'EDP' => $edpIDs,
                'DEAL.CATEGORY_ID' => 10,
                'CATEGORY_ID' => [8, 13]
            ])
            ->setOrder(['DATE_CREATE' => 'DESC'])
            ->setGroup('ID')
            ->exec();

        $edpDeals = [];

        foreach ($partnersDealsQuery->fetchAll() as $deal) {
            $deal['DATE'] = (new DateTime($deal['DATE_CREATE']))->format('d.m.Y H:i:s');
            $deal['DATE_CREATE'] = (new DateTime($deal['DATE_CREATE']))->format('d.m.Y');

            $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']];

            if (empty($edpDeals[$deal['EDP']]['LAST_DEAL'])) {
                $edpDeals[$deal['EDP']]['LAST_DEAL'] = $deal;
            } else {
                if ((int)$deal['ID'] > (int)$edpDeals[$deal['EDP']]['LAST_DEAL']['ID']) {
                    $edpDeals[$deal['EDP']]['LAST_DEAL'] = $deal;
                }
            }

            $edpDeals[$deal['EDP']]['ALL_DEALS'][] = $deal;
        }

        return $edpDeals;
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
            if ($birthdays[$deal['CONTACT_ID']]) {
                $deal['IS_BIRTHDAY_TODAY'] = true;
            }
        }

        return $deals;
    }

    public function setPageNavigation($obj) {
        $grid_options = new Bitrix\Main\Grid\Options($this->arResult['GRID']['ID']);
        $sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        $nav_params = $grid_options->GetNavParams();
        $obj->NavStart(isset($nav_params['nPageSize']) ? $nav_params['nPageSize'] : 10);
        $this->arResult['ROWS_COUNT'] = $obj->SelectedRowsCount();
        $obj->bShowAll = true;
        $this->arResult['NAV_OBJECT'] = $obj;
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

                $taskAr[] = " <div class='taskAllItem'><a href='/company/personal/user/{$task["RESPONSIBLE_ID"]}/tasks/task/view/{$task["ID"]}/'>
                   ".$task["TITLE"]."</a></div>";
            }
            $data[$dealId] = "<div class='edp-list-text-wrap'>".implode('',$taskAr)."</div>";
        }

        return $data;
    }

    public function getDealsInfo($arFilter) {
        $grid_options = new CGridOptions($this->arResult['GRID']['ID']);
        $sortOption = $grid_options->GetSorting();
        if(!$sortOption['sort']){
            $sortOption['sort'] = ['DATE_CREATE' => 'DESC'];
        }

        $db_res = CCrmDeal::GetListEx(
            $sortOption['sort'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'DATE_CREATE',
                'ASSIGNED_BY_ID',
                'ASSIGNED_BY_NAME',
                'ASSIGNED_BY_LAST_NAME',
                'ASSIGNED_BY_SECOND_NAME',
                'CONTACT_ID',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_LAST_NAME',
                'STAGE_ID',
                'UF_PARTNER_REGISTER_ADDRESS',
                'UF_PARTNER_REGISTER_ADDRESS_DADATA',
                'UF_IS_SALE_PARTNER',
                PARTNER_STATUS,
                MANAGING_PART_ID,
                IS_AGENT,
                'COMMENTS',
                'UF_EDP_KPK',
                'UF_IS_RELIABLE_PARTNER',
                'UF_IS_EDA'
            ],
            []
        );

        $this->arResult['DEALS_STAGES_COUNT'] = [];

        $stages = array_filter(CCrmDeal::GetStageNames(10), function ($key) {
            return ($key !== 'C10:LOSE' && $key !== 'C10:WON');
        }, ARRAY_FILTER_USE_KEY);

        foreach ($stages as $stageKey => $stageValue) {
            $this->arResult['DEALS_STAGES_COUNT'][$stageKey] = 0;
        }

        if (in_array('ASSIGNED_BY_ID', array_keys($this->arResult['FILTER_DATA']))) {
            while ($deal = $db_res->Fetch()) {
                $this->arResult['DEALS_STAGES_COUNT'][$deal['STAGE_ID']]++;
            }
        } else {
            $allDeals = CCrmDeal::GetListEx(
                [],
                [
                    'CATEGORY_ID' => 10
                ],
                false,
                false,
                [
                    'STAGE_ID'
                ]
            );

            while ($deal = $allDeals->Fetch()) {
                $this->arResult['DEALS_STAGES_COUNT'][$deal['STAGE_ID']]++;
            }

            unset($allDeals);
        }

        $this->setPageNavigation($db_res);

        $deals = [];
        $edpIDs = [];
        $contactIDs = [];
        $managingPartnersIDs = [];
        $manageContactID = [];

        while ($deal = $db_res->Fetch()) {
            $deals[] = $deal;
            $edpIDs[] = $deal['ID'];
            $contactIDs[] = $deal['CONTACT_ID'];
            $managingPartnersIDs[] = $deal[MANAGING_PART_ID];

            if ($deal[IS_AGENT] !== '1') {
                $manageContactID[] = $deal['CONTACT_ID'];
            }
        }

        $taskAll = $this->getTasksAll($edpIDs);

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
                'OWNER_ID' => $edpIDs,
                'CATEGORY_ID' => 10
            ]
        ]);

        $arStageChangeDates = [];

        while ($element = $dealStageHisRes->fetch()) {
            $arStageChangeDates[$element['OWNER_ID']][$element['STAGE_ID']] = (new DateTime($element['CREATED_TIME']))->format('d.m.Y H:i');
        }

        return [
            'DEALS' => $deals,
            'EDP_IDS' => $edpIDs,
            'CONTACTS_IDS' => $contactIDs,
            'MANAGING_PARTNERS_IDS' => $managingPartnersIDs,
            'DATES' => $arStageChangeDates,
            'TASK_ALL' => $taskAll,
            'MANAGING_PARTNERS_CONTACT_ID' => $manageContactID
        ];
    }

    function executeComponent()
    {
        global $USER;

        $managers = Helper::getUsers(53);

        $arEdpStages = CCrmDeal::GetStageNames(10);
        $arAllEdpStages = CCrmDeal::GetStageNames(10);

        $arEdpStages = array_filter($arEdpStages, function ($key) {
            return ($key !== 'C10:LOSE' && $key !== 'C10:WON');
        }, ARRAY_FILTER_USE_KEY);

        $partner_status = [
           //'487' => 'Партнер',
           //'488' => 'Надежный партнер',
           //'489' => 'VIP',
            '544' => 'STOP',
           // '744' => 'ИНДИВИДУАЛЬНЫЙ ТАРИФ',
           // '745' => 'БАЗОВЫЙ ТАРИФ',
        ];
        if ($USER->GetID() === '618') {
            $partner_status[''] = 'нет статуса';
        }

        Helper::includeHlTable('region_iso');

        $regionsRes = \RegionIsoTable::getList([
            'select' => [
                'UF_ISO',
                'UF_REGION'
            ]
        ])->fetchAll();

        $regions = [];

        foreach ($regionsRes as $region) {
            $regions[$region['UF_ISO']] = $region['UF_REGION'];
        }

        $this->arResult = [
            'GRID' => [
                'ID' => 'edp-list',
                'COLUMNS' => [
                    [
                        'id' => 'EDP_LIST_DATE_TIME',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_DATE_TIME'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'DATE_CREATE'
                    ],
                    [
                        'id' => 'EDP_LIST_MANAGER',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_MANAGER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDP_LIST_PARTNER',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_PARTNER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDP_LIST_REGION',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_REGION'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'UF_PARTNER_REGISTER_ADDRESS_DADATA'
                    ],
                    [
                        'id' => 'EDP_LIST_LAST_REQUEST_DATE',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_LAST_REQUEST_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDP_LIST_NUMBER_OF_LOANS',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_NUMBER_OF_LOANS'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDP_LIST_STAGE',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_STAGE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'UF_DATE_LAST_STAGE',
                    ],
                    [
                        'id' => 'EDP_LIST_TASKS_ALL',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_TASKS_ALL'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDP_LIST_NOTE',
                        'class' => 'edp-list-header',
                        'name' => Loc::getMessage('EDP_LIST_NOTE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ],
                'FILTER' => [
                    [
                        'id' => 'DATE_CREATE',
                        'name' => Loc::getMessage('EDP_LIST_DATE_CREATE'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'ASSIGNED_BY_ID',
                        'name' => Loc::getMessage('EDP_LIST_MANAGER'),
                        'type' => 'list',
                        'items' => $managers,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => REGION_ISO,
                        'name' => Loc::getMessage('EDP_LIST_REGION'),
                        'type' => 'list',
                        'items' => $regions,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => 'UF_IS_EDA',
                        'name' => Loc::getMessage('EDP_LIST_IS_EDA'),
                        'type' => 'checkbox',
                        'default' => true
                    ],
                    [
                        'id' => 'EDP_LIST_PARTNER',
                        'name' => Loc::getMessage('EDP_LIST_PARTNER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'STAGE_ID',
                        'name' => Loc::getMessage('EDP_LIST_STAGE'),
                        'type' => 'list',
                        'items' => $arAllEdpStages,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                   /* [
                        'id' => 'UF_EDP_KPK',
                        'name' => Loc::getMessage('PARTNERS_LIST_KPK'),
                        'type' => 'list',
                        'items' => $arKPKs,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],*/
                    [
                        'id' => 'UF_IS_SALE_PARTNER',
                        'name' => 'АКЦИЯ',
                        'type' => 'list',
                        'items' => [
                            'Y' => 'Да',
                            'N' => 'Нет',
                        ],
                        'default' => true,
                    ],
                    [
                        'id' => 'UF_IS_RELIABLE_PARTNER',
                        'name' => Loc::getMessage('UF_IS_RELIABLE_PARTNER'),
                        'type' => 'list',
                        'items' => [
                            '1' => 'НАДЕЖНЫЙ ПАРТНЕР',
                            'STOP' => 'СТОП',
                        ],
                        'default' => true,
                    ],
                    [
                        'id' => 'UF_IS_REQUEST_CATEGORY',
                        'name' => Loc::getMessage('UF_IS_REQUEST_CATEGORY'),
                        'type' => 'list',
                        'items' => [
                            '1' => 'К1',
                            '2' => 'К2',
                        ],
                        'default' => true,
                    ],
                ]
            ]
        ];

        $arFilter = $this->getFilter($arEdpStages);

        $this->arResult['EDP_STAGES'] = $arEdpStages;

        $allDealsData = $this->getDealsInfo($arFilter);

        $contactData = $this->getMailPhones($allDealsData['CONTACTS_IDS']);

        $managingContactData = $this->getMailPhones($allDealsData['MANAGING_PARTNERS_IDS']);
        $res = CCrmDeal::GetListEx([], ['CONTACT_ID' => $allDealsData['MANAGING_PARTNERS_IDS'], 'CATEGORY_ID' => 10], false, false, ['ID', 'CONTACT_ID', 'CONTACT_LAST_NAME'], []);

        $edpInfo = [];

        while($r = $res->Fetch()) {
            $edpInfo[$r['CONTACT_ID']] = $r;
        }

        $edpDeals = $this->getPartnersInfo($allDealsData['EDP_IDS'], $managers);

        $allDealsData['DEALS'] = $this->getPartnersBirthdays($allDealsData['CONTACTS_IDS'], $allDealsData['DEALS']);

        $res = CCrmDeal::GetListEx(
            [],
            [
                'UF_CRM_1595562385' => $allDealsData['MANAGING_PARTNERS_CONTACT_ID']
            ],
            false,
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_SECOND_NAME',
                'UF_CRM_1595562385'
            ],
            []
        );

        $agentsRes = [];
        $agentsIDs = [];
        $agents = [];

        while ($agent = $res->Fetch()) {
            $agentsRes[] = [
                'ID' =>  $agent['ID'],
                'NAME' => $agent['CONTACT_LAST_NAME'] . ' ' . $agent['CONTACT_NAME'] . ' ' . $agent['CONTACT_SECOND_NAME'],
                'UF_CRM_1595562385' => $agent['UF_CRM_1595562385']
            ];

            $agentsIDs[] = $agent['ID'];
        }

        $agentsDeals = $this->getPartnersInfo($agentsIDs, $managers);

        foreach ($agentsRes as $agent) {
            $agents[$agent['UF_CRM_1595562385']][] = [
                'ID' => $agent['ID'],
                'NAME' => $agent['NAME'],
                'DEALS' => $agentsDeals[$agent['ID']]['ALL_DEALS']
            ];
        }

        foreach ($allDealsData['DEALS'] as $deal) {
            $partnerWidget = [];
            $partnerWidget['is_partner_reestr'] = true;
            $partnerWidget['phones'] = $contactData['PHONES'];
            $partnerWidget['emails'] = $contactData['EMAILS'];
            $partnerWidget['managing_phones'] = $managingContactData['PHONES'];
            $partnerWidget['managing_emails'] = $managingContactData['EMAILS'];
            $partnerWidget['status'] = $deal[PARTNER_STATUS];
            $partnerWidget['birthday'] = $deal['IS_BIRTHDAY_TODAY'];
            $partnerWidget['is_sale_partner'] = !empty($deal['UF_IS_SALE_PARTNER']);

            $partnerWidget['is_agent'] = $deal[IS_AGENT] === '1';
            $partnerWidget['name'] = trim($deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME'] . ' ' . $deal['CONTACT_SECOND_NAME']);

            $partnerWidget['reliable_icon'] = $deal['UF_IS_RELIABLE_PARTNER'] === '1';

            if ($deal[IS_AGENT] === '1') {
                $edp = $edpInfo[$deal[MANAGING_PART_ID]];
                $partnerWidget['edp_id'] = $edp['ID'];
                $partnerWidget['managing_partner'] = trim($edp['CONTACT_LAST_NAME']);
                $partnerWidget['contact_id'] = $deal['CONTACT_ID'];
                $partnerWidget['managing_contact_id'] = $deal[MANAGING_PART_ID];
            } else {
                $partnerWidget['edp_id'] = false;
                $partnerWidget['managing_partner'] = false;
                $partnerWidget['contact_id'] = $deal['CONTACT_ID'];
                $partnerWidget['managing_contact_id'] = false;
                $partnerWidget['agents'] = $agents[$deal['CONTACT_ID']];
            }

            if (!empty($deal['UF_PARTNER_REGISTER_ADDRESS_DADATA'])) {
                $data = json_decode($deal['UF_PARTNER_REGISTER_ADDRESS_DADATA'], JSON_OBJECT_AS_ARRAY);

                $obl = $data['region_with_type'];
                $area = $data['area_with_type'];
                $settlement = $data['settlement_with_type'];
                $city = $data['city_with_type'];

                if (!empty($data['timezone'])) {
                    $currentDate = new DateTime();
                    $currentDateTimestamp = $currentDate->getTimestamp() - $currentDate->getOffset();

                    if (!empty($data['timezone'])) {
                        $timezoneH = (int)mb_substr($data['timezone'], 3);
                        $partnerWidget['time'] =  date('H:i', $currentDateTimestamp + ($timezoneH * 3600));
                    }
                }

                if (!empty($obl)) {
                    if (!empty($city)) {
                        $region = $obl . ',<br>' . $city;
                    } else {
                        $region = $obl;

                        if (!empty($area)) {
                            $region .= ',<br>' . $area;

                            if (!empty($settlement)) {
                                $region .= ',<br>' . $settlement;
                            }
                        }
                    }
                } else {
                    $region = $city;
                }
            } else {
               $region = !empty($deal['UF_PARTNER_REGISTER_ADDRESS']) ? $deal['UF_PARTNER_REGISTER_ADDRESS'] . ' (-)' : Loc::getMessage('EDP_LIST_EMPTY');
            }

            $region .= !empty($partnerTime) ? '<br><div data-role="partners-time" data-timezone="' . $data['timezone'] . '">(' . $partnerTime . ')</div>' : '';

            $itemDeals = [];
            $stages = CCrmDeal::GetStageNames(8);

            if (!empty($edpDeals[$deal['ID']]['ALL_DEALS'])) {
                foreach ($edpDeals[$deal['ID']]['ALL_DEALS'] as $edpDeal) {
                    $itemDeals[] = [
                        'partner_fio' => $deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME'] . ' ' . $deal['CONTACT_SECOND_NAME'],
                        'id' => $edpDeal['ID'],
                        'manager' => $edpDeal['MANAGER'],
                        'date' => $edpDeal['DATE'],
                        'stage' => $stages[$edpDeal['STAGE_ID']],
                        'category' => (int)$edpDeal['CATEGORY_ID'],
                        'name' => $edpDeal['NAME'],
                        'second_name' => $edpDeal['SECOND_NAME'],
                        'last_name' => $edpDeal['LAST_NAME'],
                        'program' => $edpDeal['PROGRAM']
                    ];
                }
            }

            if (!empty($agents[$deal['CONTACT_ID']])) {
                foreach ($agents[$deal['CONTACT_ID']] as $agent) {
                    if (!empty($agent['DEALS'])) {
                        foreach ($agent['DEALS'] as $d) {
                            $itemDeals[] = [
                                'is_agent' => 'yes',
                                'partner_fio' => $agent['NAME'] . ' (агент)',
                                'id' => $d['ID'],
                                'manager' => $d['MANAGER'],
                                'date' => $d['DATE'],
                                'stage' => $stages[$d['STAGE_ID']],
                                'category' => (int)$d['CATEGORY_ID'],
                                'name' => $d['NAME'],
                                'second_name' => $d['SECOND_NAME'],
                                'last_name' => $d['LAST_NAME'],
                                'program' => $d['PROGRAM']
                            ];
                        }
                    }
                }
            }

            \Vaganov\Helper::array_sort_by_column($itemDeals, 'id');

            $time = explode(' ', $deal['DATE_CREATE']);

            $deal['COMMENTS'] = str_replace('"', "'", $deal['COMMENTS']);

            $partnerWidget['is_eda'] = !!($deal['UF_IS_EDA'] && $deal['UF_IS_EDA'] !== '0');

            $this->arResult['GRID']['ROWS'][] = [
                'id' => $deal['ID'],
                'data' => [
                    'EDP_LIST_DATE_TIME' => '<div class="edp-list-flex-content">' . '<a href="/b/edp/?deal_id=' . $deal['ID'] . '" data-role="partners-link" target="_blank">' . $time[0] . '<br>' . $time[1] . '</a>' . '</div>',
                    'EDP_LIST_MANAGER' => '<div class="edp-list-text-wrap" data-hidden="true">' . trim($deal['ASSIGNED_BY_LAST_NAME']) . ' ' . trim($deal['ASSIGNED_BY_NAME']) . ' ' . trim($deal['ASSIGNED_BY_SECOND_NAME']) . '</div>',
                    'EDP_LIST_PARTNER' => \Vaganov\PartnerWidget::partnerWrite($partnerWidget),
                    'EDP_LIST_REGION' => '<div class="edp-list-text-wrap" data-hidden="true">' . $region . '</div>',
                    'EDP_LIST_LAST_REQUEST_DATE' => !empty($edpDeals[$deal['ID']]['LAST_DEAL']) ? '<div class="edp-list-text-wrap" data-hidden="true">' . $edpDeals[$deal['ID']]['LAST_DEAL']['DATE_CREATE'] . '<br>(' . $stages[$edpDeals[$deal['ID']]['LAST_DEAL']['STAGE_ID']] . ')' . '</div>' : '',
                    'EDP_LIST_NUMBER_OF_LOANS' => !empty($itemDeals) ? '<a href="#" class="edp-list-deals-popup" data-role="edp-list-deals-popup" data-id="' . $deal['ID'] . '" data-deals=\'' . json_encode($itemDeals, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . '\'>' . count($itemDeals) . '</a>' : 0,
                    'EDP_LIST_STAGE' => '<div class="edp-list-text-wrap" data-hidden="true">' . $arAllEdpStages[$deal['STAGE_ID']] . '<br>(' . $allDealsData['DATES'][$deal['ID']][$deal['STAGE_ID']] . ')</div>',
                    'EDP_LIST_TASKS_ALL' => $allDealsData['TASK_ALL'][$deal['ID']],
                    'EDP_LIST_NOTE' => '<div class="edp-list-text-wrap" data-comment="' . $deal['COMMENTS'] . '" data-role="add-comment" data-hidden="true">' . $deal['COMMENTS'] . '</div>'
                ],
                'actions' => [
                    [
                        'ICONCLASS' => 'edit',
                        'ONCLICK' => 'window.open("/b/edp/?deal_id=' . $deal['ID'] . '", "_blank");',
                        'DEFAULT' => true
                    ],
                    [
                        'ICONCLASS' => 'icon-note',
                        'TEXT' => Loc::getMessage('EDP_LIST_ACTION_ADD_COMMENT'),
                        'ONCLICK' => 'addComment(' . $deal['ID'] . ', ' . \Bitrix\Main\Web\Json::encode($deal['COMMENTS']) . ');',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-edit',
                        'TEXT' => Loc::getMessage('EDP_LIST_ACTION_EDIT'),
                        'ONCLICK' => 'window.open("/b/edp/?deal_id=' . $deal['ID'] . '", "_blank");',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-go',
                        'TEXT' => Loc::getMessage('EDP_LIST_ACTION_OPEN_DEAL'),
                        'ONCLICK' => 'window.open("/crm/deal/details/' . $deal['ID'] . '/", "_blank");',
                        'DEFAULT' => false
                    ],
                    [
                        'ICONCLASS' => 'icon-list',
                        'TEXT' => Loc::getMessage('EDP_LIST_ACTION_DEAL_TASKS'),
                        'ONCLICK' => 'getTask(' . $deal['ID'] . ',' . $deal['ASSIGNED_BY_ID'] . ')',
                        'DEFAULT' => false
                    ],
                ],
                'editable' => false
            ];
        }

        $this->includeComponentTemplate();
    }
}