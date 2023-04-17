<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class EdsList extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function reloadQuickFilterAction()
    {
        $filterOption = new Bitrix\Main\UI\Filter\Options('eds-list');
        $filterData = $filterOption->getFilter([]);

        $arFilter = [
            'CATEGORY_ID' => 14
        ];

        if (empty($filterData)) {
            $arStages = array_filter(CCrmDeal::GetStageNames(14), function ($key) {
                return ($key !== 'C14:LOSE');
            }, ARRAY_FILTER_USE_KEY);

            $arFilter['STAGE_ID'] = array_keys($arStages);
        } else {
            if (!empty($filterData['DATE_CREATE_from'])) {
                $arFilter['>=DATE_CREATE'] = $filterData['DATE_CREATE_from'];
            }

            if (!empty($filterData['DATE_CREATE_to'])) {
                $arFilter['<=DATE_CREATE'] = $filterData['DATE_CREATE_to'];
            }

            if (!empty($filterData['UF_SAVINGS_DEPOSIT_END_DATE_from'])) {
                $arFilter['>=UF_SAVINGS_DEPOSIT_END_DATE'] = $filterData['UF_SAVINGS_DEPOSIT_END_DATE_from'];
            }

            if (!empty($filterData['UF_SAVINGS_DEPOSIT_END_DATE_to'])) {
                $arFilter['<=UF_SAVINGS_DEPOSIT_END_DATE'] = $filterData['UF_SAVINGS_DEPOSIT_END_DATE_to'];
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
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_days':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_month':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_datesel':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_quarter':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_year':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_from':
                    case 'UF_SAVINGS_DEPOSIT_END_DATE_to':
                        break;
                    case 'EDS_LIST_CONTRIBUTOR':
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
                    case 'EDS_LIST_PARTNER':
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
                            $arFilter[PART_ZAIM] = $filter;
                        } else {
                            $arFilter[PART_ZAIM] = false;
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

    public function getPartnersInfo()
    {
        $result = [];

        $db_res = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10
            ],
            false,
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_ID',
                PARTNER_STATUS,
                'UF_PARTNER_REGISTER_ADDRESS_DADATA',
                IS_AGENT,
                MANAGING_PART_ID,
                'UF_IS_RELIABLE_PARTNER'
            ],
            []
        );

        while ($partner = $db_res->Fetch()) {
            $result[$partner['CONTACT_ID']] = $partner;
        }

        return $result;
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

    public function getFilter($stages) {
        $arFilter = [
            'CATEGORY_ID' => 14,
            'STAGE_ID' => array_keys($stages)
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

        if (!empty($filterData['UF_SAVINGS_DEPOSIT_END_DATE_from'])) {
            $arFilter['>=UF_SAVINGS_DEPOSIT_END_DATE'] = $filterData['UF_SAVINGS_DEPOSIT_END_DATE_from'];
        }

        if (!empty($filterData['UF_SAVINGS_DEPOSIT_END_DATE_to'])) {
            $arFilter['<=UF_SAVINGS_DEPOSIT_END_DATE'] = $filterData['UF_SAVINGS_DEPOSIT_END_DATE_to'];
        }

        $savingsFilter = [];

        if (!empty($filterData['UF_DATE_DOC_from'])) {
            $savingsFilter['>=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_from'];
        }

        if (!empty($filterData['UF_DATE_DOC_to'])) {
            $savingsFilter['<=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_to'];
        }

        if (!empty($savingsFilter)) {
            $savingsFilter['UF_HANDLER'] = ['balance_replenishment', 'partial_withdrawal', 'interest_payment', 'cash_payment'];

            \Vaganov\Helper::includeHlTable('money_orders');

            $transactions = \MoneyOrdersTable::getList([
                'select' => [
                    'UF_DEAL_ID'
                ],
                'order' => [
                    'ID' => 'ASC'
                ],
                'filter' => $savingsFilter
            ])->fetchAll();

            $IDs = [];

            foreach ($transactions as $transaction) {
                $IDs[] = $transaction['UF_DEAL_ID'];
            }

            $arFilter['ID'] = $IDs;
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
                case 'UF_SAVINGS_DEPOSIT_END_DATE_days':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_month':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_datesel':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_quarter':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_year':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_from':
                case 'UF_SAVINGS_DEPOSIT_END_DATE_to':
                case 'UF_DATE_DOC_days':
                case 'UF_DATE_DOC_month':
                case 'UF_DATE_DOC_datesel':
                case 'UF_DATE_DOC_quarter':
                case 'UF_DATE_DOC_year':
                case 'UF_DATE_DOC_from':
                case 'UF_DATE_DOC_to':
                    break;
                case 'EDS_LIST_CONTRIBUTOR':
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
                case 'EDS_LIST_PARTNER':
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
                        $arFilter[PART_ZAIM] = $filter;
                    } else {
                        $arFilter[PART_ZAIM] = false;
                    }

                    break;
                default:
                    $arFilter[$k] = $v;
                    break;
            }
        }

        return $arFilter;
    }

    public function getManagingInfo($partners)
    {
        $managingPartIDs = [];

        foreach ($partners as $key => $value) {
            if (!empty($value[MANAGING_PART_ID])) {
                $managingPartIDs[] = $value[MANAGING_PART_ID];
            }
        }

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

        return $edpInfo;
    }

    public function getMailPhones($IDs)
    {
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

    public function getPartnersBirthdays($IDs, $deals)
    {
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

    public function getSavingsBalance()
    {
        $allDeals = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 14
            ],
            false,
            false,
            [
                'ID'
            ]
        );

        $ids = [];

        while ($deal = $allDeals->Fetch()) {
            $ids[] = $deal['ID'];
        }

        \Vaganov\Helper::includeHlTable('money_orders');

        $transactions = \MoneyOrdersTable::getList([
            'select' => [
                'UF_PAYMENT',
                'UF_SUM',
                'UF_DEAL_ID'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => [
                'UF_DEAL_ID' => $ids,
                '!=UF_HANDLER' => ['interest_payment', 'in_entranceKpk']
            ]
        ])->fetchAll();

        $deals = [];

        foreach ($transactions as $transaction) {
            if ($transaction['UF_PAYMENT'] === 'IN') {
                $deals[$transaction['UF_DEAL_ID']]['IN'] += (int)$transaction['UF_SUM'];
            } else {
                $deals[$transaction['UF_DEAL_ID']]['OUT'] += (int)$transaction['UF_SUM'];
            }
        }

        foreach ($deals as $key => $value) {
            $deals[$key]['BALANCE'] = $value['IN'] - $value['OUT'];
        }

        return $deals;
    }

    public function getFinancialAccounting($arDeals)
    {
        function filterTransactions($value, $filterData) {
            $result = [];

            if (!empty($filterData['UF_DATE_DOC_from']) || !empty($filterData['UF_DATE_DOC_to'])) {
                $date = new \DateTime($value['DATE']);

                if (!empty($filterData['UF_DATE_DOC_from'])) {
                    $date_from = new \DateTime($filterData['UF_DATE_DOC_from']);

                    if ($date >= $date_from) {
                        if (!empty($filterData['UF_DATE_DOC_to'])) {
                            $date_to = new \DateTime($filterData['UF_DATE_DOC_to']);

                            if ($date <= $date_to) {
                                $result['DATE'] = $value['DATE'];
                                $result['SUM'] = number_format($value['SUM'], 2, ',', ' ');
                            }
                        } else {
                            $result['DATE'] = $value['DATE'];
                            $result['SUM'] = number_format($value['SUM'], 2, ',', ' ');
                        }
                    }
                } else {
                    $date_to = new \DateTime($filterData['UF_DATE_DOC_to']);

                    if ($date <= $date_to) {
                        $result['DATE'] = $value['DATE'];
                        $result['SUM'] = number_format($value['SUM'], 2, ',', ' ');
                    }
                }
            } else {
                $result['DATE'] = $value['DATE'];
                $result['SUM'] = number_format($value['SUM'], 2, ',', ' ');
            }

            return $result;
        }

        $IDs = [];

        foreach ($arDeals as $deal) {
            $IDs[] = $deal['ID'];
        }

        $filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['GRID']['ID']);
        $filterData = $filterOption->getFilter([]);

        \Vaganov\Helper::includeHlTable('money_orders');

        //получаем все транзакции на пополнение баланса по сделкам
        $in_transactions = \MoneyOrdersTable::getList([
            'select' => [
                'ID',
                'UF_DEAL_ID',
                'UF_SUM',
                'UF_DATE_DOC'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => [
                'UF_PAYMENT' => 'IN',
                'UF_DEAL_ID' => $IDs,
                'UF_HANDLER' => 'balance_replenishment'
            ]
        ])->fetchAll();

        $balance_replenishment = [];

        foreach ($in_transactions as $transaction) {
            $balance_replenishment[$transaction['UF_DEAL_ID']][] = [
                'DATE' => (new \DateTime($transaction['UF_DATE_DOC']))->format('d.m.Y'),
                'SUM' => $transaction['UF_SUM']
            ];
        }

        $first_transactions = [];

        foreach ($balance_replenishment as $key => $value) {
            $transaction = array_shift($balance_replenishment[$key]);

            $first_transactions[$key] = filterTransactions($transaction, $filterData);
        }

        $balance_replenishment_result = [];

        foreach ($balance_replenishment as $key => $value) {
            foreach ($value as $item) {
                $balance_replenishment_result[$key][] = filterTransactions($item, $filterData);
            }
        }

        $partial_withdrawal_filter = [
            'UF_PAYMENT' => 'OUT',
            'UF_DEAL_ID' => $IDs,
            'UF_HANDLER' => 'partial_withdrawal'
        ];

        if (!empty($filterData['UF_DATE_DOC_from'])) {
            $partial_withdrawal_filter['>=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_from'];
        }

        if (!empty($filterData['UF_DATE_DOC_to'])) {
            $partial_withdrawal_filter['<=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_to'];
        }

        //получаем все транзакции на частичное снятие по сделкам
        $partial_withdrawal_transactions = \MoneyOrdersTable::getList([
            'select' => [
                'ID',
                'UF_DEAL_ID',
                'UF_SUM',
                'UF_DATE_DOC'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $partial_withdrawal_filter
        ])->fetchAll();

        $partial_withdrawal_result = [];

        foreach ($partial_withdrawal_transactions as $transaction) {
            $partial_withdrawal_result[$transaction['UF_DEAL_ID']][] = [
                'DATE' => !empty($transaction['UF_DATE_DOC']) ? (new \DateTime($transaction['UF_DATE_DOC']))->format('d.m.Y') : '',
                'SUM' => number_format($transaction['UF_SUM'], 2, ',', ' ')
            ];
        }

        $interests_transactions_filter = [
            'UF_PAYMENT' => 'OUT',
            'UF_DEAL_ID' => $IDs,
            'UF_HANDLER' => 'interest_payment'
        ];

        if (!empty($filterData['UF_DATE_DOC_from'])) {
            $interests_transactions_filter['>=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_from'];
        }

        if (!empty($filterData['UF_DATE_DOC_to'])) {
            $interests_transactions_filter['<=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_to'];
        }

        //получаем все транзакции на выплату процентов по сделкам
        $interests_transactions = \MoneyOrdersTable::getList([
            'select' => [
                'ID',
                'UF_DEAL_ID',
                'UF_SUM',
                'UF_DATE_DOC'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $interests_transactions_filter
        ])->fetchAll();

        $interests_result = [];

        foreach ($interests_transactions as $transaction) {
            $interests_result[$transaction['UF_DEAL_ID']][] = [
                'DATE' => !empty($transaction['UF_DATE_DOC']) ? (new \DateTime($transaction['UF_DATE_DOC']))->format('d.m.Y') : '',
                'SUM' => number_format($transaction['UF_SUM'], 2, ',', ' ')
            ];
        }

        $payments_transactions_filter = [
            'UF_PAYMENT' => 'OUT',
            'UF_DEAL_ID' => $IDs,
            'UF_HANDLER' => 'cash_payment'
        ];

        if (!empty($filterData['UF_DATE_DOC_from'])) {
            $payments_transactions_filter['>=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_from'];
        }

        if (!empty($filterData['UF_DATE_DOC_to'])) {
            $payments_transactions_filter['<=UF_DATE_DOC'] = $filterData['UF_DATE_DOC_to'];
        }

        //получаем все транзакции на выплату вклада по сделкам
        $payments_transactions = \MoneyOrdersTable::getList([
            'select' => [
                'ID',
                'UF_DEAL_ID',
                'UF_SUM',
                'UF_DATE_DOC'
            ],
            'order' => [
                'ID' => 'ASC'
            ],
            'filter' => $payments_transactions_filter
        ])->fetchAll();

        $payments_result = [];

        foreach ($payments_transactions as $transaction) {
            $payments_result[$transaction['UF_DEAL_ID']][] = [
                'DATE' => !empty($transaction['UF_DATE_DOC']) ? (new \DateTime($transaction['UF_DATE_DOC']))->format('d.m.Y') : '',
                'SUM' => number_format($transaction['UF_SUM'], 2, ',', ' ')
            ];
        }

        return [
            'FIRST_TRANSACTION' => $first_transactions,
            'BALANCE_REPLENISHMENT' => $balance_replenishment_result,
            'INTEREST_PAYMENT' => $interests_result,
            'PARTIAL_WITHDRAWAL' => $partial_withdrawal_result,
            'PAYMENTS' => $payments_result
        ];
    }

    function executeComponent()
    {
        $managers = Helper::getUsers(53);

        $allStages = CCrmDeal::GetStageNames(14);

        $stages = array_filter($allStages, function ($key) {
            return ($key !== 'C14:LOSE' && $key !== 'C14:WON');
        }, ARRAY_FILTER_USE_KEY);

        $stagesWithArchive = array_filter($allStages, function ($key) {
            return ($key !== 'C14:LOSE');
        }, ARRAY_FILTER_USE_KEY);

        $interests = [
            '750' => 'Ежемесячно',
            '751' => 'В конце срока',
            '763' => 'Не выплачиваются'
        ];

        $this->arResult = [
            'GRID' => [
                'ID' => 'eds-list',
                'COLUMNS' => [
                    [
                        'id' => 'EDS_LIST_DATE_TIME',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_DATE_TIME'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false,
                        'sort' => 'DATE_CREATE'
                    ],
                    [
                        'id' => 'EDS_LIST_MANAGER',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_MANAGER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_PARTNER',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_PARTNER'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_CONTRIBUTOR',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_CONTRIBUTOR'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_STAGE',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_STAGE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_DATE_NUMBER_DS',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_DATE_NUMBER_DS'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_CONDITIONS',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_CONDITIONS'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_PAYMENT_OF_INTEREST',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_PAYMENT_OF_INTEREST'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_CONTRACT_AMOUNT',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_CONTRACT_AMOUNT'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_SUM',
                        'class' => 'eds-list-header eds-list-header-biege',
                        'name' => Loc::getMessage('EDS_LIST_SUM'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_LIST_DEPOSIT_END_DATE',
                        'class' => 'eds-list-header',
                        'name' => Loc::getMessage('EDS_LIST_DEPOSIT_END_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_BALANCE_DEPOSIT_SUM_DATE',
                        'class' => 'eds-list-header eds-list-header-green edsGroup1-1',
                        'name' => Loc::getMessage('EDS_BALANCE_DEPOSIT_SUM_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_BALANCE_REPLENISHMENT_SUM_DATE',
                        'class' => 'eds-list-header eds-list-header-green edsGroup1-2',
                        'name' => Loc::getMessage('EDS_BALANCE_REPLENISHMENT_SUM_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_BALANCE_PARTIAL_WITHDRAWAL_SUM_DATE',
                        'class' => 'eds-list-header eds-list-header-green edsGroup1-3',
                        'name' => Loc::getMessage('EDS_BALANCE_PARTIAL_WITHDRAWAL_SUM_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_BALANCE_INTEREST_PAYMENT_SUM_DATE',
                        'class' => 'eds-list-header eds-list-header-green edsGroup1-4',
                        'name' => Loc::getMessage('EDS_BALANCE_INTEREST_PAYMENT_SUM_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ],
                    [
                        'id' => 'EDS_BALANCE_DEPOSIT_PAYMENT_SUM_DATE',
                        'class' => 'eds-list-header eds-list-header-green edsGroup1-5',
                        'name' => Loc::getMessage('EDS_BALANCE_DEPOSIT_PAYMENT_SUM_DATE'),
                        'default' => true,
                        'editable' => false,
                        'resizeable' => false
                    ]
                ],
                'FILTER' => [
                    [
                        'id' => 'DATE_CREATE',
                        'name' => Loc::getMessage('FILTER_DATE_CREATE'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'UF_DATE_DOC',
                        'name' => Loc::getMessage('FILTER_UF_DATE_DOC'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'UF_SAVINGS_DEPOSIT_END_DATE',
                        'name' => Loc::getMessage('FILTER_DEPOSIT_END'),
                        'type' => 'date',
                        'default' => true
                    ],
                    [
                        'id' => 'ASSIGNED_BY_ID',
                        'name' => Loc::getMessage('EDS_LIST_MANAGER'),
                        'type' => 'list',
                        'items' => $managers,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ],
                    [
                        'id' => 'EDS_LIST_PARTNER',
                        'name' => Loc::getMessage('EDS_LIST_PARTNER'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'EDS_LIST_CONTRIBUTOR',
                        'name' => Loc::getMessage('EDS_LIST_CONTRIBUTOR'),
                        'type' => 'text',
                        'default' => true
                    ],
                    [
                        'id' => 'STAGE_ID',
                        'name' => Loc::getMessage('EDS_LIST_STAGE'),
                        'type' => 'list',
                        'items' => $allStages,
                        'default' => true,
                        'params' => ['multiple' => 'Y']
                    ]
                ]
            ]
        ];

        $this->arResult['STAGES'] = $stagesWithArchive;

        $this->arResult['DEALS_STAGES_COUNT'] = [];

        foreach ($stagesWithArchive as $stageKey => $stageValue) {
            $this->arResult['DEALS_STAGES_COUNT'][$stageKey] = 0;
        }

        $arFilter = $this->getFilter($stages);
        $this->arResult['FILTER_ARRAY'] = $arFilter;

        $grid_options = new CGridOptions($this->arResult['GRID']['ID']);

        $sortOption = $grid_options->GetSorting();

        if (!$sortOption['sort']) {
            $sortOption['sort'] = ['DATE_CREATE' => 'DESC'];
        }

        $db_res = CCrmDeal::GetListEx(
            $sortOption['sort'],
            $arFilter,
            false,
            false,
            [
                PART_ZAIM,
                'STAGE_ID',
                'DATE_CREATE',
                'CONTACT_NAME',
                'ASSIGNED_BY_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_SECOND_NAME',
                'ASSIGNED_BY_LAST_NAME',
                'ASSIGNED_BY_SECOND_NAME',
                'UF_EDS_CONTRACT_DATE',
                'UF_EDS_CONTRACT_NUMBER',
                'UF_CONTRACTUAL_INTEREST_RATE',
                'UF_INTEREST_PAYMENT',
                'UF_CONTRACT_PERIOD',
                'UF_SAVINGS_DEPOSIT_END_DATE',
                'UF_CONTRACT_AMOUNT',
                'UF_SAVINGS_PROGRAM_NAME'
            ],
            []
        );

        $this->setPageNavigation($db_res);

        $arDeals = [];
        $ufContactIDs = [];
        $IDs = [];

        while ($deal = $db_res->Fetch()) {
            $IDs[] = $deal['ID'];
            $arDeals[] = $deal;
            $ufContactIDs[] = $deal[PART_ZAIM];
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
                'OWNER_ID' => $IDs,
                'CATEGORY_ID' => 14
            ]
        ]);

        $arStageChangeDates = [];

        while ($element = $dealStageHisRes->fetch()) {
            $arStageChangeDates[$element['OWNER_ID']][$element['STAGE_ID']] = (new DateTime($element['CREATED_TIME']))->format('d.m.Y H:i');
        }

        if (in_array('ASSIGNED_BY_ID', array_keys($this->arResult['FILTER_DATA']))) {
            foreach ($arDeals as $deal) {
                $this->arResult['DEALS_STAGES_COUNT'][$deal['STAGE_ID']]++;
            }
        } else {
            $allDeals = CCrmDeal::GetListEx(
                [],
                [
                    'CATEGORY_ID' => 14
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

        $partners = $this->getPartnersInfo();
        $edpInfo = $this->getManagingInfo($partners);

        $managingPartIDs = [];

        foreach ($partners as $key => $value) {
            if (!empty($value[MANAGING_PART_ID])) {
                $managingPartIDs[] = $value[MANAGING_PART_ID];
            }
        }

        $managingContactData = $this->getMailPhones($managingPartIDs);
        $contactData = $this->getMailPhones($ufContactIDs);

        $arDeals = $this->getPartnersBirthdays($ufContactIDs, $arDeals);
        $allDealsBalance = $this->getSavingsBalance();

        $financeAccouting = $this->getFinancialAccounting($arDeals);

//        $depositSum = 0;
//
//        foreach ($allDealsBalance as $item) {
//            if (!empty($item['BALANCE'])) {
//                $depositSum += (int)$item['BALANCE'];
//            }
//        }

        $depositSum = 0;

        foreach ($arDeals as $deal) {
            $contributor = $deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']  . ' ' . $deal['CONTACT_SECOND_NAME'];

            $partnerWidget = [];
            $partnerWidget['is_partner_reestr'] = false;
            $partnerWidget['phones'] = $contactData['PHONES'];
            $partnerWidget['emails'] = $contactData['EMAILS'];
            $partnerWidget['managing_phones'] = $managingContactData['PHONES'];
            $partnerWidget['managing_emails'] = $managingContactData['EMAILS'];
            $partnerWidget['status'] = $partners[$deal[PART_ZAIM]][PARTNER_STATUS];

            $dadata = json_decode($partners[$deal[PART_ZAIM]]['UF_PARTNER_REGISTER_ADDRESS_DADATA'], JSON_OBJECT_AS_ARRAY);

            if (!empty($dadata['timezone'])) {
                $currentDate = new DateTime();
                $currentDateTimestamp = $currentDate->getTimestamp() - $currentDate->getOffset();

                if (!empty($dadata['timezone'])) {
                    $timezoneH = (int)mb_substr($dadata['timezone'], 3);
                    $partnerWidget['time'] =  date('H:i', $currentDateTimestamp + ($timezoneH * 3600));
                }
            }

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

            $time = (new DateTime($deal['DATE_CREATE']))->format('d.m.Y H:i');

            $contractDateNumber = ($deal['UF_EDS_CONTRACT_DATE'] && $deal['UF_EDS_CONTRACT_NUMBER']) ? $deal['UF_EDS_CONTRACT_DATE'] . ' ' . $deal['UF_EDS_CONTRACT_NUMBER'] : '-';
            $interestRate = ($deal['UF_CONTRACTUAL_INTEREST_RATE']) ? $deal['UF_CONTRACTUAL_INTEREST_RATE'] . ' %' : '0';
            $paymentOfInterest = ($deal['UF_INTEREST_PAYMENT']) ? $interests[$deal['UF_INTEREST_PAYMENT']] : '-';

            if ($deal['UF_SAVINGS_DEPOSIT_END_DATE']) {
                $contractEndDate = (new DateTime($deal['UF_SAVINGS_DEPOSIT_END_DATE']))->format('d.m.Y');
            } else {
                $contractEndDate = '-';
            }

            $period = '';

            if (isset($deal['UF_CONTRACT_PERIOD'])) {
                if ($deal['UF_CONTRACT_PERIOD'] === '0') {
                    $period = 'Бессрочно';
                } else {
                    $period = $deal['UF_CONTRACT_PERIOD'] . ' мес';
                }
            }

            $depositSum += $allDealsBalance[$deal['ID']]['BALANCE'];

            $balance_replenishments = $financeAccouting['BALANCE_REPLENISHMENT'][$deal['ID']];

            $balance_component = '';

            if (!empty($balance_replenishments)) {
                $last_balance_replenishment = $balance_replenishments[count($balance_replenishments) - 1];

                if (!empty($last_balance_replenishment)) {
                    $balance_component = '<div data-vue-component="eds.list_payments" data-last-payment="' . str_replace('"', "'", json_encode($last_balance_replenishment)) . '" data-payments="' . str_replace('"', "'", json_encode($balance_replenishments)) . '"><div>';
                }
            }

            $partial_withdrawals = $financeAccouting['PARTIAL_WITHDRAWAL'][$deal['ID']];

            $partial_component = '';

            if (!empty($partial_withdrawals)) {
                $last_partial_withdrawal = $partial_withdrawals[count($partial_withdrawals) - 1];

                if (!empty($last_partial_withdrawal)) {
                    $last_partial_withdrawal = str_replace('"', "'", json_encode($last_partial_withdrawal));
                    $partial_withdrawals = str_replace('"', "'", json_encode($partial_withdrawals));

                    $partial_component = '<div data-vue-component="eds.list_payments" data-last-payment="' . $last_partial_withdrawal . '" data-payments="' . $partial_withdrawals . '"><div>';
                }
            }

            $interest_payments = $financeAccouting['INTEREST_PAYMENT'][$deal['ID']];

            $interest_component = '';

            if (!empty($interest_payments)) {
                $last_interest_payment = $interest_payments[count($interest_payments) - 1];

                if (!empty($last_interest_payment)) {
                    $last_interest_payment = str_replace('"', "'", json_encode($last_interest_payment));
                    $interest_payments = str_replace('"', "'", json_encode($interest_payments));

                    $interest_component = '<div data-vue-component="eds.list_payments" data-last-payment="' . $last_interest_payment . '" data-payments="' . $interest_payments . '"><div>';
                }
            }

            $deposit_payments = $financeAccouting['PAYMENTS'][$deal['ID']];

            $deposit_component = '';

            if (!empty($deposit_payments)) {
                $last_deposit_payment = $deposit_payments[count($deposit_payments) - 1];

                if (!empty($last_deposit_payment)) {
                    $last_deposit_payment = str_replace('"', "'", json_encode($last_deposit_payment));
                    $deposit_payments = str_replace('"', "'", json_encode($deposit_payments));

                    $deposit_component = '<div data-vue-component="eds.list_payments" data-last-payment="' . $last_deposit_payment . '" data-payments="' . $deposit_payments . '"><div>';
                }
            }

            $this->arResult['GRID']['ROWS'][] = [
                'id' => $deal['ID'],
                'data' => [
                    'EDS_LIST_DATE_TIME' => '<div class="eds-list-flex-content"><a href="/b/eds/?deal_id=' . $deal['ID'] . '" target="_blank">' . $time . '</a>' . '</div>',
                    'EDS_LIST_MANAGER' => trim($deal['ASSIGNED_BY_LAST_NAME']) . ' ' . trim($deal['ASSIGNED_BY_NAME']) . ' ' . trim($deal['ASSIGNED_BY_SECOND_NAME']),
                    'EDS_LIST_PARTNER' => \Vaganov\PartnerWidget::partnerWrite($partnerWidget),
                    'EDS_LIST_CONTRIBUTOR' => '<a href="/b/eds/?deal_id=' . $deal['ID'] . '" data-role="slider-link">' . $contributor . '</a>',
                    'EDS_LIST_STAGE' => $stagesWithArchive[$deal['STAGE_ID']] . '<br>(' . $arStageChangeDates[$deal['ID']][$deal['STAGE_ID']] . ')',
                    'EDS_LIST_DATE_NUMBER_DS' => $contractDateNumber,
                    'EDS_LIST_CONDITIONS' => '<div style="line-height: 100%">' . $deal['UF_SAVINGS_PROGRAM_NAME'] . '<div class="d-flex mt-2 justify-content-between"><div>' . $interestRate . '</div><div>' . $period . '</div></div></div>',
                    'EDS_LIST_PAYMENT_OF_INTEREST' => $paymentOfInterest,
                    'EDS_LIST_CONTRACT_AMOUNT' => number_format($deal['UF_CONTRACT_AMOUNT'], 2, ',', ' ') ? : '-',
                    'EDS_LIST_SUM' => number_format($allDealsBalance[$deal['ID']]['BALANCE'], 2, ',', ' ') ? : '-',
                    'EDS_LIST_DEPOSIT_END_DATE' => $contractEndDate,
                    'EDS_BALANCE_DEPOSIT_SUM_DATE' => '<div style="width: 82px">' . $financeAccouting['FIRST_TRANSACTION'][$deal['ID']]['SUM'] . '<br>' . $financeAccouting['FIRST_TRANSACTION'][$deal['ID']]['DATE'] . '</div>',
                    'EDS_BALANCE_REPLENISHMENT_SUM_DATE' => '<div style="width: 82px">' . $balance_component . '</div>',
                    'EDS_BALANCE_PARTIAL_WITHDRAWAL_SUM_DATE' => '<div style="width: 80px">' . $partial_component . '</div>',
                    'EDS_BALANCE_INTEREST_PAYMENT_SUM_DATE' => '<div style="width: 81px">' . $interest_component . '</div>',
                    'EDS_BALANCE_DEPOSIT_PAYMENT_SUM_DATE' => '<div style="width: 81px">' . $deposit_component . '</div>'
                ],
                'actions' => [
                    [
                        'ICONCLASS' => 'edit',
                        'ONCLICK' => 'window.open("/b/eds/?deal_id=' . $deal['ID'] . '", "_blank");',
                        'DEFAULT' => true
                    ],
                ],
                'editable' => false
            ];
        }

        $this->arResult['GRID']['ROWS'][] = [
            'id' => 'all-count',
            'data' => [
                'EDS_LIST_DATE_TIME' => '',
                'EDS_LIST_DATE_NUMBER_DS' => '',
                'EDS_LIST_MANAGER' => '',
                'EDS_LIST_PARTNER' => '',
                'EDS_LIST_CONTRIBUTOR' => '',
                'EDS_LIST_INTEREST_RATE' => '',
                'EDS_LIST_CONTRACT_PERIOD' => '',
                'EDS_LIST_PAYMENT_OF_INTEREST' => '<b>ИТОГО:</b>',
                'EDS_LIST_SUM' => number_format($depositSum, 2, ',', ' '),
                'EDS_LIST_DEPOSIT_END_DATE' => ''
            ],
            'actions' => [],
            'editable' => false
        ];

        $this->includeComponentTemplate();
    }
}