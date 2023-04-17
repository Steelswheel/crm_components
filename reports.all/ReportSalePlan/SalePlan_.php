<?php




namespace Components\Vaganov\ReportsAll\ReportSalePlan;

use Bitrix\Crm\DealTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class SalePlan__
{
    /**
     * Класс для создания отчета "План продаж"
     */

    public function __construct()
    {

    }

    public function run($month, $inputValue, $inputManagerId, $inputType, $isInput)
    {
        return $this->getReport($month, $inputValue, $inputManagerId, $inputType, $isInput);
    }

    public function getReportHeader()
    {
        /**
         * Формируем заголовок таблицы
         */

        $header[] = [
            [
                'value' => 'ОТДЕЛ',
                'rowspan' => 2,
                'class' => 'yellow'
            ],
            [
                'value' => 'МЕНЕДЖЕР',
                'rowspan' => 2,
                'class' => 'yellow'
            ],
            [
                'value' => 'СДЕЛКИ В ПРОЦЕССЕ (проверка СБ - подача заявления в пфр / соц)',
                'rowspan' => 2,
                'class' => 'green'
            ],
            [
                'value' => 'ПЛАН',
                'colspan' => 3,
                'class' => 'green'
            ],
            [
                'value' => 'СДАЧА В ПФР / СОЦ',
                'colspan' => 2,
                'class' => 'green'
            ],
            [
                'value' => 'ПОГАШЕНИЕ ЗАЙМА',
                'rowspan' => 2,
                'class' => 'green'
            ]
        ];

        $header[] = [
            [
                'value' => 'СДЕЛКИ В ПРОЦЕССЕ',
                'class' => 'green'
            ],
            [
                'value' => 'ПРОДАЖИ',
                'class' => 'green'
            ],
            [
                'value' => 'ПЛАН',
                'class' => 'green'
            ],
            [
                'value' => 'ФАКТ',
                'class' => 'green'
            ],
            [
                'value' => 'ПРОЦЕНТЫ',
                'class' => 'green'
            ]
        ];

        return $header;
    }

    public function getReportUsers()
    {
        $mainSaleDepart = \Vaganov\Helper::getDepart(['ID' => ['241']]);

        $saleDeparts = \Vaganov\Helper::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $departList = array_map(function($i) {
            return $i['ID'];
        }, $saleDeparts);

        $s = array_search(241, $departList);
        unset($departList[$s]);

        $departData = \CIntranetUtils::GetDepartmentsData($departList);

        $q = \Bitrix\Intranet\Util::GetDepartmentEmployees([
            'DEPARTMENTS' => $departList,
            'RECURSIVE' => 'Y',
            'ACTIVE' => 'Y',
            'SELECT' => [
                'LAST_NAME',
                'NAME',
                'UF_SORT',
                'UF_MANAGER_TYPE',
                'UF_DEPARTMENT'
            ]
        ]);

        $d = [];

        while ($res = $q->GetNext()) {
            foreach($saleDeparts as $depart) {
                if ($res['UF_DEPARTMENT'][0] === $depart['ID']) {
                    if ($res['ID'] === '45') {
                        $name = 'Савченко/Нуреева';
                    } else if ($res['ID'] === '34') {
                        continue;
                    } else {
                        $name = $res['LAST_NAME'] . ' ' . mb_substr($res['NAME'], 0, 1) . '.';
                    }

                    if ($res['ID'] === $depart['UF_HEAD']) {
                        if (count($d[$departData[$res['UF_DEPARTMENT'][0]]]) > 0) {
                            array_unshift($d[$departData[$res['UF_DEPARTMENT'][0]]], [
                                'ID' => $res['ID'],
                                'NAME' => $name
                            ]);
                        } else {
                            $d[$departData[$res['UF_DEPARTMENT'][0]]][] = [
                                'ID' => $res['ID'],
                                'NAME' => $name
                            ];
                        }
                    } else {
                        $d[$departData[$res['UF_DEPARTMENT'][0]]][] = [
                            'ID' => $res['ID'],
                            'NAME' => $name
                        ];
                    }
                }
            }
        }

        $d = array_replace(array_flip($departData), $d);

        foreach ($departList as $depart) {
            if (count($d[$departData[$depart]]) > 1) {
                ksort($d[$departData[$depart]]);
            }
        }

        foreach ($d as $key => &$value) {
            $nureevaIndex = array_search('34', array_column($value, 'ID'));

            if ($nureevaIndex) {
                $nureeva = array_splice($value, $nureevaIndex, 1);
                array_unshift($value, $nureeva[0]);
            }

            $savchenkoIndex = array_search('45', array_column($value, 'ID'));

            if ($savchenkoIndex) {
                $savchenko = array_splice($value, $savchenkoIndex, 1);
                array_unshift($value, $savchenko[0]);
            }

            $asmalovskiyIndex = array_search('47', array_column($value, 'ID'));

            if ($asmalovskiyIndex) {
                $asmalovskiy = array_splice($value, $asmalovskiyIndex, 1);
                array_unshift($value, $asmalovskiy[0]);
            }
        }

        return $d;
    }

    public function getPlanSales($startDate, $endDate)
    {
        /**
         * Получает записи из инфоблока "План продаж"
         */

        $entity = Helper::includeHlTable('b_raff_sales_plan');

        $query = new Query($entity);
        $query
            ->setSelect([
                'UF_ADDITIONAL_SALES',
                'UF_DEALS_IN_PROCESS',
                'UF_PLAN',
                'UF_USER_ID'
            ])
            ->setFilter([
                '><UF_MONTH' => [$startDate, $endDate]
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $item) {
            if (empty($item['UF_PLAN'])) {
                $item['UF_PLAN'] = 0;
            }

            $result[$item['UF_USER_ID']] = $item;
        }

        unset($query);

        return $result;
    }

    public function setPlanSales($startDate, $endDate, $inputValue, $inputManagerId, $type)
    {
        /**
         * Добавляет/обновлет запись в инфоблоке "План продаж"
         */

        global $USER;

        $users = \Vaganov\Helper::getUsersFullInfo(241);

        if ((in_array($USER->GetID(), array_keys($users))) || $USER->isAdmin()) {
            $user = $users[$USER->GetID()];
            $manager = $users[$inputManagerId];

            if (($user['UF_DEPARTMENT'][0] === $manager['UF_DEPARTMENT'][0]) || $USER->isAdmin()) {
                $entity = Helper::includeHlTable('b_raff_sales_plan');
                $entityClass = $entity->getDataClass();

                $query = new Query($entity);
                $query
                    ->setSelect([
                        'ID'
                    ])
                    ->setFilter([
                        'UF_USER_ID' => $inputManagerId,
                        '><UF_MONTH' => [$startDate, $endDate]
                    ])
                    ->exec();

                $id = false;

                foreach ($query->fetchAll() as $value) {
                    $id = $value['ID'];
                }

                unset($query);

                $result = [];

                if (!$id) {
                    switch ($type) {
                        case 'DEALS_IN_PROCESS':
                            $result = $entityClass::add([
                                'UF_USER_ID' => $inputManagerId,
                                'UF_DEALS_IN_PROCESS' => $inputValue,
                                'UF_MONTH' => $startDate
                            ]);
                            break;
                        case 'ADDITIONAL_SALES':
                            $result = $entityClass::add([
                                'UF_USER_ID' => $inputManagerId,
                                'UF_ADDITIONAL_SALES' => $inputValue,
                                'UF_MONTH' => $startDate
                            ]);
                            break;
                        case 'PLAN':
                            $result = $entityClass::add([
                                'UF_USER_ID' => $inputManagerId,
                                'UF_PLAN' => $inputValue,
                                'UF_MONTH' => $startDate
                            ]);
                            break;
                    }
                } else {
                    switch ($type) {
                        case 'DEALS_IN_PROCESS':
                            $result = $entityClass::update($id, [
                                'UF_DEALS_IN_PROCESS' => $inputValue
                            ]);
                            break;
                        case 'ADDITIONAL_SALES':
                            $result = $entityClass::update($id, [
                                'UF_ADDITIONAL_SALES' => $inputValue
                            ]);
                            break;
                        case 'PLAN':
                            $result = $entityClass::update($id, [
                                'UF_PLAN' => $inputValue
                            ]);
                            break;
                    }
                }

                if (!$result->isSuccess()) {
                    return $errors = $result->getErrorMessages();
                } else {
                    return $id;
                }
            }
        }

        return false;
    }

    public function getDealsInProcess()
    {
        /**
         * Берем все ЭДЗ по МСК/РСК, у которых текущая стадия со 2 по 25 включительно.
         * На 25 стадии нет прикрепленных расписок и дат.
         */

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['max(%s)', 'DEAL_HISTORY.CREATED_TIME']
            ])
            ->registerRuntimeField('DEAL', [
                'data_type' => 'Bitrix\Crm\DealTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.CONTACT_ID',
                ],
            ])
            ->registerRuntimeField('EDP_CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'EDP' => 'DEAL.ID',
                'EDP_NAME' => 'EDP_CONTACT.NAME',
                'EDP_SECOND_NAME' => 'EDP_CONTACT.SECOND_NAME',
                'EDP_LAST_NAME' => 'EDP_CONTACT.LAST_NAME',
                'ID',
                'ASSIGNED_BY_ID',
                'TIME',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'CATEGORY_ID',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'EDP_DADATA' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS_DADATA',
                'EDP_ADDRESS' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS',
                'UF_INCLUDING_DATE_TO_SALE_PLAN',
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                'STAGE_ID' => \Vaganov\Helper::getStagesFromTo('C8:EXECUTING', 'C8:13'),
                [
                    'LOGIC' => 'OR',
                    '!=MSK_SUM' => null,
                    '!=RSK_SUM' => null
                ],
                'DATE_PFR_SEND' => null,
                'DATE_RB_SEND' => null
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            $deal['TIME'] = (new DateTime($deal['TIME']))->format('d.m.Y');

            if ($deal['ASSIGNED_BY_ID'] === '34') {
                $result['45'][] = $deal;
            } else {
                $result[$deal['ASSIGNED_BY_ID']][] = $deal;
            }
        }

        unset($query);

        return $result;
    }

    public function getLoanRepaymentDeals($startDate, $endDate)
    {
        /**
         * Берем все ЭДЗ по дате зачисления
         */

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL', [
                'data_type' => 'Bitrix\Crm\DealTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.CONTACT_ID',
                ],
            ])
            ->registerRuntimeField('EDP_CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'EDP' => 'DEAL.ID',
                'EDP_NAME' => 'EDP_CONTACT.NAME',
                'EDP_SECOND_NAME' => 'EDP_CONTACT.SECOND_NAME',
                'EDP_LAST_NAME' => 'EDP_CONTACT.LAST_NAME',
                'ID',
                'ASSIGNED_BY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'EDP_DADATA' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS_DADATA',
                'EDP_ADDRESS' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS',
                'UF_INCLUDING_DATE_TO_SALE_PLAN',
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'UF_BONUS_PLAN_PERCENT',
                'UF_BONUS_PAIDED_DATE'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '><PAYMENT_PFR_DATA' => [$startDate, $endDate],
                    '><PAYMENT_REGION_DATA' => [$startDate, $endDate]
                ]
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            if ($deal['ASSIGNED_BY_ID'] === '34') {
                $result['45'][] = $deal;
            } else {
                $result[$deal['ASSIGNED_BY_ID']][] = $deal;
            }
        }

        unset($query);

        return $result;
    }

    public function getFactDeals($startDate, $endDate)
    {
        /**
         * Берем все ЭДЗ, у которых на 18 стадии есть прикрепленные расписки и даты.
         */

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL', [
                'data_type' => 'Bitrix\Crm\DealTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.CONTACT_ID',
                ],
            ])
            ->registerRuntimeField('EDP_CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.UF_CRM_1540188759' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'EDP' => 'DEAL.ID',
                'EDP_NAME' => 'EDP_CONTACT.NAME',
                'EDP_SECOND_NAME' => 'EDP_CONTACT.SECOND_NAME',
                'EDP_LAST_NAME' => 'EDP_CONTACT.LAST_NAME',
                'ID',
                'ASSIGNED_BY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'EDP_DADATA' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS_DADATA',
                'EDP_ADDRESS' => 'DEAL.UF_PARTNER_REGISTER_ADDRESS',
                'UF_INCLUDING_DATE_TO_SALE_PLAN',
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN',
                'UF_IS_PASS_CONFIRMED'
            ])
            ->setGroup(['ID'])
            ->setFilter([
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    [
                        '><DATE_PFR_SEND' => [$startDate, $endDate],
                        '!=UF_CRM_1518961392' => null,
                    ],
                    [
                        '><DATE_RB_SEND' => [$startDate, $endDate],
                        '!=UF_CRM_1575627757' => null
                    ]
                ]
            ])
            ->exec();

        $result = [];
        $resultCount = [];

        foreach ($query->fetchAll() as $deal) {
            if (!empty($deal['MSK_SUMM']) && !empty($deal['RSK_SUMM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $biggerDate = new \DateTime($deal['DATE_PFR_SEND']);
                    } else {
                        $biggerDate = new \DateTime($deal['DATE_RB_SEND']);
                    }

                    if (new \DateTime($startDate) <= $biggerDate && $biggerDate <= new \DateTime($endDate)) {
                        $deal['TIME'] = $biggerDate->format('d.m.Y');
                    }
                }
            } else {
                if (!empty($deal['MSK_SUM'])) {
                    $deal['TIME'] = (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y');
                    $deal['LOAN_TYPE'] = 'МСК';
                }

                if (!empty($deal['RSK_SUM'])) {
                    $deal['TIME'] = (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y');
                    $deal['LOAN_TYPE'] = 'РСК';
                }
            }

            if ($deal['ASSIGNED_BY_ID'] === '34') {
                $deal['ASSIGNED_BY_ID'] = '45';
            }


            $result[] = $deal;
            $resultCount[$deal['ASSIGNED_BY_ID']] = $resultCount[$deal['ASSIGNED_BY_ID']]
                ? $resultCount[$deal['ASSIGNED_BY_ID']] + 1
                : 1;

        }

        unset($query);

        return $this->getDataForModal($result);
    }

    public function getDataForModal($item)
    {
        $result = [];
        $stagesEdz = \CCrmDeal::GetStages(8);
        foreach ($item as $i) {
            $fio = $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME'];

            if (!empty($i['EDP_DADATA'])) {
                $dadata = json_decode($i['EDP_DADATA'], JSON_OBJECT_AS_ARRAY);

                $obl = $dadata['region_with_type'];
                $area = $dadata['area_with_type'];
                $settlement = $dadata['settlement_with_type'];
                $city = $dadata['city_with_type'];

                if (!empty($obl)) {
                    if (!empty($city)) {
                        $region = $obl . ', ' . $city;
                    } else {
                        $region = $obl;

                        if (!empty($area)) {
                            $region .= ', ' . $area;

                            if (!empty($settlement)) {
                                $region .= ', ' . $settlement;
                            }
                        }
                    }
                } else {
                    $region = $city;
                }
            } else {
                $region = $i['EDP_ADDRESS'];
            }

            $data = [
                'id' => $i['ID'],

                'userId' => $i['ASSIGNED_BY_ID'],
                'date' => $i['TIME'],
                'borrower_fio' => $fio,
                'borrower_link' => '/b/edz/?deal_id=' . $i['ID'] . '&show',
                'stage' => $stagesEdz[$i['STAGE_ID']]['NAME'],
                'partner_fio' => $i['EDP_LAST_NAME'] . ' ' . $i['EDP_NAME'] . ' ' . $i['EDP_SECOND_NAME'],
                'partner_link' => '/b/edp/?deal_id=' . $i['EDP'] . '/',
                'region' => $region,
                'prf_date' => $i['DATE_PFR_SEND'] ? (new \DateTime($i['DATE_PFR_SEND']))->format('d.m.Y') : '',
                'rsk_date' => $i['DATE_RB_SEND'] ? (new \DateTime($i['DATE_RB_SEND']))->format('d.m.Y') : '',
                'payment_pfr_date' => $i['PAYMENT_PFR_DATA'] ? (new \DateTime($i['PAYMENT_PFR_DATA']))->format('d.m.Y') : '',
                'payment_pfr_sum' => $i['PAYMENT_PFR_SUM'] ? number_format($i['PAYMENT_PFR_SUM'], 2, ',', ' ') : '',
                'payment_rsk_date' => $i['PAYMENT_REGION_DATA'] ? (new \DateTime($i['PAYMENT_REGION_DATA']))->format('d.m.Y') : '',
                'payment_rsk_sum' => $i['PAYMENT_REGION_SUM'] ? number_format($i['PAYMENT_REGION_SUM'], 2, ',', ' ') : '',
                'plan_percent' => $i['UF_BONUS_PLAN_PERCENT'] ? $i['UF_BONUS_PLAN_PERCENT'] : 0,
                'bonus_paided' => $i['UF_BONUS_PAIDED_DATE'] ? (new \DateTime($i['UF_BONUS_PAIDED_DATE']))->format('d.m.Y') : '',
                'dealStatus' => !empty($i['UF_INCLUDING_DATE_TO_SALE_PLAN']),
                'UF_IS_PASS_CONFIRMED' => $i['UF_IS_PASS_CONFIRMED'] === '1'
            ];

            if (!empty($i['UF_INCLUDING_DATE_TO_SALE_PLAN'])) {
                $data['dealStatus_date'] = (new DateTime($i['UF_INCLUDING_DATE_TO_SALE_PLAN']))->format('d.m.Y');
            } else {
                if (!empty($i['UF_NOT_INCLUDING_DATE_TO_SALE_PLAN'])) {
                    $data['dealStatus_date'] = (new DateTime($i['UF_NOT_INCLUDING_DATE_TO_SALE_PLAN']))->format('d.m.Y');
                } else {
                    $data['dealStatus_date'] = false;
                }
            }

            $result[] = $data;


        }

        return $result;
    }

    public function getReportBody($startDate, $endDate)
    {
        /**
         * Формирует тело таблицы отчета.
         */

        $users = $this->getReportUsers();

        $planSales = $this->getPlanSales($startDate, $endDate);
        $dealsInProcessMsk = $this->getDealsInProcess();
        $loan_repayment_deals = $this->getLoanRepaymentDeals($startDate, $endDate);
        $factDeals = $this->getFactDeals($startDate, $endDate);

        $body = [];
        $total = [];
        $companyTotal = [];

        foreach ($users as $depart => $departUsers) {
            $isFirst = true;

            foreach ($departUsers as $key => $value) {
                if ($isFirst) {
                    $city = [
                        'value' => $depart,
                        'rowspan' => count($departUsers),
                        'class' => 'sale-report-table-city'
                    ];

                    $isFirst = false;
                } else {
                    $city = ['value' => null];
                }

                $dealsInProcessMskCount = empty($dealsInProcessMsk[$value['ID']]) ? 0 : count($dealsInProcessMsk[$value['ID']]);
                $total[$depart]['DEALS_IN_PROCESS_MSK'] += (int)$dealsInProcessMskCount;
                $dealsInProcessMskData = $this->getDataForModal($dealsInProcessMsk[$value['ID']]);

                $managerPlanDealsInProcess = empty($planSales[$value['ID']]['UF_DEALS_IN_PROCESS']) ? 0 : $planSales[$value['ID']]['UF_DEALS_IN_PROCESS'];
                $total[$depart]['PLAN_DEALS_IN_PROCESS'] += (int)$managerPlanDealsInProcess;

                $managerAdditionalSales = empty($planSales[$value['ID']]['UF_ADDITIONAL_SALES']) ? 0 : $planSales[$value['ID']]['UF_ADDITIONAL_SALES'];
                $total[$depart]['ADDITIONAL_SALES'] += (int)$managerAdditionalSales;

                $managerPlanSales = empty($planSales[$value['ID']]['UF_PLAN']) ? 0 : $planSales[$value['ID']]['UF_PLAN'];
                $total[$depart]['PLAN_SALES'] += (int)$managerPlanSales;

                $managerFactDeals = $factDeals['count'][$value['ID']] ?: 0 ;
                $total[$depart]['FACT_DEALS'] += (int)$managerFactDeals;
                //$managerFactDealsData = $this->getDataForModal($factDeals[$value['ID']]);

                $loanRepaymentDealsCount = empty($loan_repayment_deals[$value['ID']]) ? 0 : count($loan_repayment_deals[$value['ID']]);
                $total[$depart]['LOAN_REPAYMENT_DEALS'] += (int)$loanRepaymentDealsCount;
                $loanRepaymentDealsData = $this->getDataForModal($loan_repayment_deals[$value['ID']]);

                $percents = !empty($managerPlanSales) ? ceil(($managerFactDeals * 100) / $managerPlanSales) : 0;

                $body[] = [
                    'class' => 'oper-sales-row',
                    'value' => [
                        //ОТДЕЛ
                        $city,
                        //МЕНЕДЖЕР
                        [
                            'value' => $value['NAME'],
                            'class' => 'sale-report-table-manager'
                        ],
                        //СДЕЛКИ В ПРОЦЕССЕ по МСК/РСК (со 2 по 25 стадию)
                        [
                            'value' => $dealsInProcessMskCount,
                            'class' => 'sale-report-table-process',
                            'data' => $dealsInProcessMskData,
                            'title' => 'СДЕЛКИ В ПРОЦЕССЕ (проверка СБ - подача заявления в пфр/соц)',
                            'modal' => 'dealsInProcess'
                        ],
                        //ПЛАН ПРОДАЖ - СДЕЛКИ В ПРОЦЕССЕ
                        [
                            'value' => $managerPlanDealsInProcess,
                            'input' => true,
                            'manager-id' => $value['ID'],
                            'type' => 'DEALS_IN_PROCESS'
                        ],
                        //ПЛАН ПРОДАЖ - ПРОДАЖИ
                        [
                            'value' => $managerAdditionalSales,
                            'input' => true,
                            'manager-id' => $value['ID'],
                            'type' => 'ADDITIONAL_SALES'
                        ],
                        //ПЛАН ПРОДАЖ - ПЛАН
                        [
                            'value' => $managerPlanSales,
                            'input' => true,
                            'manager-id' => $value['ID'],
                            'type' => 'PLAN',
                            'class' => 'sale-report-table-plan red-bold'
                        ],
                        //ФАКТ ПРОДАЖ - ФАКТ
                        [
                            'value' => $managerFactDeals,
                            'class' => 'bold',
                            'data' => ['userId' => $value['ID']],
                            'title' => 'СДАЧА В ПФР / СОЦ',
                            'modal' => 'factDeals',
                            'modalWidth' => '1100px'


                        ],
                        //ФАКТ ПРОДАЖ - ПРОЦЕНТЫ
                        [
                            'value' => $percents,
                            'class' => 'sale-report-table-plan bold'
                        ],
                        //ПОГАШЕНИЕ ЗАЙМА
                        [
                            'value' => $loanRepaymentDealsCount,
                            'class' => 'sale-report-table-process',
                            'data' => $loanRepaymentDealsData,
                            'title' => 'sale-report-table-plan ПОГАШЕНИЕ ЗАЙМА',
                            'modal' => 'loanRepaymentDeals',
                            'modalWidth' => '1300px'
                        ]
                    ]
                ];
            }

            $companyTotal['DEALS_IN_PROCESS_MSK'] += $total[$depart]['DEALS_IN_PROCESS_MSK'];
            $companyTotal['ADDITIONAL_SALES'] += $total[$depart]['ADDITIONAL_SALES'];
            $companyTotal['PLAN_DEALS_IN_PROCESS'] += $total[$depart]['PLAN_DEALS_IN_PROCESS'];
            $companyTotal['PLAN_SALES'] += $total[$depart]['PLAN_SALES'];
            $companyTotal['LOAN_REPAYMENT_DEALS'] += $total[$depart]['LOAN_REPAYMENT_DEALS'];
            $companyTotal['FACT_DEALS'] += $total[$depart]['FACT_DEALS'];

            $totalPercents = !empty($total[$depart]['PLAN_SALES']) ? ceil(($total[$depart]['FACT_DEALS'] * 100) / $total[$depart]['PLAN_SALES']) : 0;

            $body[] = [
                'class' => 'oper-sales-total-row',
                'value' => [
                    [
                        'value' => 'ИТОГО:',
                        'colspan' => 2,
                        'class' => 'light-blue'
                    ],
                    //СДЕЛКИ В ПРОЦЕССЕ по МСК/РСК (со 2 по 18 стадию)
                    [
                        'value' => $total[$depart]['DEALS_IN_PROCESS_MSK'],
                        'class' => 'light-blue'
                    ],
                    //ПЛАН ПРОДАЖ - СДЕЛКИ В ПРОЦЕССЕ
                    [
                        'value' => $total[$depart]['PLAN_DEALS_IN_PROCESS'],
                        'class' => 'light-blue'
                    ],
                    //ПЛАН ПРОДАЖ - ПРОДАЖИ
                    [
                        'value' => $total[$depart]['ADDITIONAL_SALES'],
                        'class' => 'light-blue'
                    ],
                    //ПЛАН ПРОДАЖ - ПЛАН
                    [
                        'value' => $total[$depart]['PLAN_SALES'],
                        'class' => 'light-blue red-bold'
                    ],
                    //ФАКТ ПРОДАЖ - ФАКТ
                    [
                        'value' => $total[$depart]['FACT_DEALS'],
                        'class' => 'light-blue'
                    ],
                    //ФАКТ ПРОДАЖ - ПРОЦЕНТЫ
                    [
                        'value' => $totalPercents,
                        'class' => 'light-blue'
                    ],
                    //ПОГАШЕНИЕ ЗАЙМА
                    [
                        'value' => $total[$depart]['LOAN_REPAYMENT_DEALS'],
                        'class' => 'light-blue'
                    ]
                ]
            ];
        }

        $companyTotalPercents = !empty($companyTotal['PLAN_SALES']) ? ceil(($companyTotal['FACT_DEALS'] * 100) / $companyTotal['PLAN_SALES']) : 0;

        $body[] = [
            'class' => 'oper-sales-total-company-row',
            'value' => [
                [
                    'value' => 'ИТОГО (КОМПАНИЯ):',
                    'colspan' => 2,
                    'class' => 'orange'
                ],
                //СДЕЛКИ В ПРОЦЕССЕ по МСК/РСК (со 2 по 18 стадию)
                [
                    'value' => $companyTotal['DEALS_IN_PROCESS_MSK'],
                    'class' => 'orange'
                ],
                //ПЛАН ПРОДАЖ - СДЕЛКИ В ПРОЦЕССЕ
                [
                    'value' => $companyTotal['PLAN_DEALS_IN_PROCESS'],
                    'class' => 'orange'
                ],
                //ПЛАН ПРОДАЖ - ПРОДАЖИ
                [
                    'value' => $companyTotal['ADDITIONAL_SALES'],
                    'class' => 'orange'
                ],
                //ПЛАН ПРОДАЖ - ПЛАН
                [
                    'value' => $companyTotal['PLAN_SALES'],
                    'class' => 'orange red-bold'
                ],
                //ФАКТ ПРОДАЖ - ФАКТ
                [
                    'value' => $companyTotal['FACT_DEALS'],
                    'class' => 'orange'
                ],
                //ФАКТ ПРОДАЖ - ПРОЦЕНТЫ
                [
                    'value' => $companyTotalPercents,
                    'class' => 'orange'
                ],
                //ПОГАШЕНИЕ ЗАЙМА
                [
                    'value' => $companyTotal['LOAN_REPAYMENT_DEALS'],
                    'class' => 'orange'
                ]
            ]
        ];

        return [
            'body' => $body,
            'values' => [
                'factDeals' => $factDeals['values']
            ]
        ];
    }

    public function getReport($month, $inputValue, $inputManagerId, $inputType, $isInput)
    {
        /**
         * Возвращает отчет полностью.
         */

        global $USER;

        $date = new \DateTime('01.' . $month);
        $startDate = $date->modify('first day of this month')->format('d.m.Y');
        $endDate = $date->modify('last day of this month')->format('d.m.Y');

        $startTimestamp = strtotime($startDate . ' 00:00:00');
        $endTimestamp = strtotime($endDate . ' 23:59:59');

        $startDate = date('d.m.Y H:i:s', $startTimestamp);
        $endDate = date('d.m.Y H:i:s', $endTimestamp);

        if (!$isInput) {

            $body = $this->getReportBody($startDate, $endDate);

            return [
                'dates' => [$startDate, $endDate],
                'title' => 'ПЛАН ПРОДАЖ',
                'table' => [
                    'head' => $this->getReportHeader(),
                    'body' => $body['body']
                ],
                'values' => $body['values'],
                'isAdmin' => $USER->IsAdmin()
            ];
        } else {
            if ($USER->IsAdmin()) {
                return $this->setPlanSales($startDate, $endDate, $inputValue, $inputManagerId, $inputType);
            } else {
                return false;
            }
        }
    }
}