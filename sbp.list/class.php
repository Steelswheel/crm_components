<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vaganov\Helper;

include 'ReportExcel.php';

Loader::IncludeModule('crm');

class SbpList extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function getFilter() {
        $filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['GRID']['ID']);
        $filterData = $filterOption->getFilter([]);

        $arFilter = [];

        if (!empty($filterData['UF_CREATE_DATE_from'])) {
            $arFilter['>=UF_CREATE_DATE'] = $filterData['UF_CREATE_DATE_from'];
        }

        if (!empty($filterData['UF_CREATE_DATE_to'])) {
            $arFilter['<=UF_CREATE_DATE'] = $filterData['UF_CREATE_DATE_to'];
        }

        foreach ($filterData as $k => $v) {
            switch ($k) {
                case 'PRESET_ID':
                case 'FILTER_ID':
                case 'FILTER_APPLIED':
                case 'FIND':
                case 'UF_CREATE_DATE_days':
                case 'UF_CREATE_DATE_month':
                case 'UF_CREATE_DATE_datesel':
                case 'UF_CREATE_DATE_quarter':
                case 'UF_CREATE_DATE_year':
                case 'UF_CREATE_DATE_from':
                case 'UF_CREATE_DATE_to':
                    break;
                default:
                    $arFilter[$k] = $v;
                    break;
            }
        }

        return $arFilter;
    }

    public function getExcelAction($data)
    {
        $arr = json_decode($data, 1);
        Helper::array_sort_by_column($arr, 'orderCreateDate');

        return (new ReportExcel($arr))->getFile();
    }

    public function getSbpDataAction($date)
    {
        $startDate = (new \DateTime($date))->format('d.m.Y 00:00:00');
        $endDate = (new \DateTime($date))->format('d.m.Y 23:59:59');

        $sbp = new \Bank\SbpApi();

        $sberData = $sbp->registry($startDate, $endDate);

        $result = [];

        $operationIds = [];

        if (!empty($sberData['registryData']['orderParams']['orderParam'])) {
            foreach ($sberData['registryData']['orderParams']['orderParam'] as $qr) {
                $operationIds[] = $qr['partnerOrderNumber'];
            }

            Helper::includeHlTable('sbp_creation');

            $opertaions = SbpCreationTable::getList([
                'order' => ['ID' => 'desc'],
                'select' => [
                    '*',
                    'UF_*'
                ],
                'filter' => ['ID' => $operationIds]
            ])->fetchAll();

            $deals = [];

            foreach ($opertaions as $operation) {
                $deal = \CCrmDeal::GetByID($operation['UF_DEAL_ID']);

                $name = trim($deal['CONTACT_LAST_NAME'] . ' ' . trim($deal['CONTACT_NAME'] . ' ' . $deal['CONTACT_SECOND_NAME']));

                $deals[$operation['ID']] = [
                    'ID' => $deal['ID'],
                    'NAME' => $name
                ];
            }

            foreach ($sberData['registryData']['orderParams']['orderParam'] as $qr) {
                $result[] = [
                    'orderCreateDate' => (new \DateTime($qr['orderCreateDate']))->format('d.m.Y H:i:s'),
                    'operationDateTime' => (new \DateTime($qr['orderOperationParams']['orderOperationParam'][0]['operationDateTime']))->format('d.m.Y H:i:s'),
                    'deal_id' => $deals[$qr['partnerOrderNumber']]['ID'],
                    'deal_name' => $deals[$qr['partnerOrderNumber']]['NAME'],
                    'operationSum' => number_format((int)$qr['orderOperationParams']['orderOperationParam'][0]['operationSum'] / 100, 2, ' руб. ', ' ') . ' коп.'
                ];
            }
        }

        return $result;
    }

    function executeComponent()
    {
        $this->arResult = [
            'GRID' => [
                'ID' => 'sbp-list',
                'COLUMNS' => [
                    [
                        'id' => 'SBP_LIST_DATE',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'UF_CREATE_DATE'
                    ],
                    [
                        'id' => 'SBP_LIST_DEAL',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_DEAL'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_SUM',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_SUM'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_STATUS',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_STATUS'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_PAYER_FIO',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_PAYER_FIO'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_DESCRIPTION',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_DESCRIPTION'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ],
                'FILTER' => [
                    [
                        'id' => 'UF_CREATE_DATE',
                        'name' => Loc::getMessage('SBP_LIST_DATE'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'UF_DEAL_ID',
                        'name' => Loc::getMessage('SBP_LIST_DEAL'),
                        'type' => 'string',
                        'default' => true
                    ]
                ]
            ],
            'GRID_2' => [
                'ID' => 'sbp-list-2',
                'COLUMNS' => [
                    [
                        'id' => 'SBP_LIST_2_CREATE_DATE',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_2_CREATE_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_2_OPERATION_DATE',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_2_OPERATION_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_2_DEAL',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_2_DEAL'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'SBP_LIST_2_SUM',
                        'class' => 'spb-list-header',
                        'name' => Loc::getMessage('SBP_LIST_2_SUM'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ]
            ]
        ];

        $arFilter = $this->getFilter();

        $gridOptions = new Bitrix\Main\Grid\Options($this->arResult['GRID']['ID']);

        $sortOption = $gridOptions->GetSorting();

        if (!$sortOption['sort']) {
            $sortOption['sort'] = ['UF_CREATE_DATE' => 'DESC'];
        }

        $arSort = $sortOption['sort'];

        $arNavParams = $gridOptions->GetNavParams();

        $nav = new \Bitrix\Main\UI\PageNavigation('application-payment');

        $nav->allowAllRecords(false)
            ->setPageSize($arNavParams['nPageSize'])
            ->initFromUri();

        Helper::includeHlTable('sbp_creation');

        $db_res = SbpCreationTable::getList([
            'select' => [
                '*',
                'UF_*'
            ],
            'filter' => $arFilter,
            'limit' => $nav->getLimit(),
            'offset' => $nav->getOffset(),
            'order' => $arSort
        ]);

        $this->arResult['NAV_OBJECT'] = $nav;
        $this->arResult['ROWS_COUNT'] = $db_res->getSelectedRowsCount();

        $QRs = $db_res->FetchAll();

        $IDs = [];

        foreach ($QRs as $qr) {
            $IDs[] = $qr['UF_DEAL_ID'];
        }

        $res = \CCrmDeal::GetListEx([], ['ID' => $IDs], false, false, ['ID', 'CONTACT_NAME', 'CONTACT_SECOND_NAME', 'CONTACT_LAST_NAME']);

        $fio = [];

        while ($deal = $res->Fetch()) {
            $fio[$deal['ID']] = trim($deal['CONTACT_LAST_NAME'] . ' ' . trim($deal['CONTACT_NAME'] . ' ' . $deal['CONTACT_SECOND_NAME']));
        }

        foreach ($QRs as $qr) {
            $status = '';

            switch ($qr['UF_ORDER_STATE']) {
                case 'CREATED':
                    $status = 'СОЗДАН';
                    break;
                case 'PAID':
                    $status = 'ОПЛАЧЕН';
                    break;
            }

            $this->arResult['GRID']['ROWS'][] = [
                'id' => $qr['ID'],
                'data' => [
                    'SBP_LIST_DATE' => $qr['UF_CREATE_DATE'],
                    'SBP_LIST_DEAL' => "<a target='_blank' href='/b/eds/?deal_id=$qr[UF_DEAL_ID]'>" . $fio[$qr['UF_DEAL_ID']] . '</a>',
                    'SBP_LIST_SUM' => number_format((int)$qr['UF_ORDER_SUM'] / 100, 2, ' руб. ', ' ') . ' коп.',
                    'SBP_LIST_STATUS' => $status,
                    'SBP_LIST_PAYER_FIO' => $qr['UF_CLIENT_NAME'],
                    'SBP_LIST_DESCRIPTION' => $qr['UF_DESCRIPTION']
                ],
                'actions' => [],
                'editable' => false
            ];
        }

        $this->includeComponentTemplate();
    }
}