<?php




namespace Components\Vaganov\ReportsAll\ReportSalePlan;

use Bitrix\Crm\DealTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class SalePlan
{
    public $startDate;
    public $endDate;

    public function __construct($month)
    {
        $date = new \DateTime('01.' . $month);
        $this->startDate = $date->modify('first day of this month')->format('d.m.Y');
        $this->endDate = $date->modify('last day of this month')->format('d.m.Y');
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
                    } else if (in_array($res['ID'], ['34', '701'])) {
                        continue;
                    } else {
                        $name = $res['LAST_NAME'] . ' ' . mb_substr($res['NAME'], 0, 1) . '.';
                    }

                    if ($res['ID'] === $depart['UF_HEAD']) {

                        if ( $d[$departData[$res['UF_DEPARTMENT'][0]]] && count($d[$departData[$res['UF_DEPARTMENT'][0]]]) > 0) {
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

        $dd = [];
        foreach ($d as $name => $users){
            $dd[] = [
                'name' => $name,
                'users' => $users,
                'usersIds' => array_map(function($i){return $i['ID'];},$users),

            ];
        }

        return $dd;
    }

    public function getFactDeals()
    {
        /**
         * Берем все ЭДЗ, у которых на 18 стадии есть прикрепленные расписки и даты.
         */

        $startDate = $this->startDate;
        $endDate = $this->endDate;

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
                'UF_IS_PASS_CONFIRMED',
                'UF_BONUS_PLAN_PERCENT',
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
                $deal['ASSIGNED_BY_ID'] = '45';
            }

            $deal['userId'] = $deal['ASSIGNED_BY_ID'];

            $result[] = $deal;
        }

        unset($query);

        return $this->getDataForModal($result);
    }

    private function getCurrentDeals()
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;

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
                'UF_BONUS_PAIDED_DATE',
                'UF_IS_BONUS_NOT_PAIDED'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                'UF_IS_BONUS_NOT_PAIDED' => null,
                [
                    'LOGIC' => 'OR',
                    [
                        [
                            'LOGIC' => 'OR',
                            '><PAYMENT_PFR_DATA' => [$startDate, $endDate],
                            '><PAYMENT_REGION_DATA' => [$startDate, $endDate]
                        ],
                        [
                            'LOGIC' => 'OR',
                            ['><UF_BONUS_PAIDED_DATE' => [$startDate, $endDate]],
                            ['UF_BONUS_PAIDED_DATE' => null]
                        ]
                    ],
                    [
                        [
                            'LOGIC' => 'OR',
                            '!=PAYMENT_PFR_DATA' => null,
                            '!=PAYMENT_REGION_DATA' => null
                        ],
                        '><UF_BONUS_PAIDED_DATE' => [$startDate, $endDate]
                    ]
                ]
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            if (empty($deal['UF_BONUS_PAIDED_DATE'])) {
                $deal['WITHOUT_BONUS'] = true;
            }

            $deal['IS_CURRENT'] = true;

            if ($deal['ASSIGNED_BY_ID'] === '34') {
                $deal['ASSIGNED_BY_ID'] = '45';
                $result[] = $deal;
            } else {
                $result[] = $deal;
            }
        }

        return $result;
    }

    private function getDealsWithoutBonus()
    {
        $startDate = $this->startDate;

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
                'UF_BONUS_PAIDED_DATE',
                'UF_IS_BONUS_NOT_PAIDED'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                'UF_BONUS_PAIDED_DATE' => null,
                'UF_IS_BONUS_NOT_PAIDED' => null,
                [
                    'LOGIC' => 'OR',
                    '!=PAYMENT_PFR_DATA' => null,
                    '!=PAYMENT_REGION_DATA' => null
                ],
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            if (!empty($deal['PAYMENT_PFR_DATA']) && !empty($deal['PAYMENT_REGION_DATA'])) {
                if (new \DateTime($deal['PAYMENT_PFR_DATA']) > new \DateTime($deal['PAYMENT_REGION_DATA'])) {
                    $biggerDate = new \DateTime($deal['PAYMENT_PFR_DATA']);
                } else {
                    $biggerDate = new \DateTime($deal['PAYMENT_REGION_DATA']);
                }

                if ($biggerDate < new \DateTime($startDate)) {
                    $deal['WITHOUT_BONUS'] = true;
                    $deal['IS_CURRENT'] = false;

                    if ($deal['ASSIGNED_BY_ID'] === '34') {
                        $deal['ASSIGNED_BY_ID'] = '45';
                        $result[] = $deal;
                    } else {
                        $result[] = $deal;
                    }
                }
            } else {
                if (!empty($deal['PAYMENT_PFR_DATA'])) {
                    if (new \DateTime($deal['PAYMENT_PFR_DATA']) < new \DateTime($startDate)) {
                        $deal['WITHOUT_BONUS'] = true;
                        $deal['IS_CURRENT'] = false;

                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $deal['ASSIGNED_BY_ID'] = '45';
                            $result[] = $deal;
                        } else {
                            $result[] = $deal;
                        }
                    }
                }

                if (!empty($deal['PAYMENT_REGION_DATA'])) {
                    if (new \DateTime($deal['PAYMENT_REGION_DATA']) < new \DateTime($startDate)) {
                        $deal['WITHOUT_BONUS'] = true;
                        $deal['IS_CURRENT'] = false;

                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $deal['ASSIGNED_BY_ID'] = '45';
                            $result[] = $deal;
                        } else {
                            $result[] = $deal;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getLoanRepaymentDeals()
    {
        $current_deals = $this->getCurrentDeals();
        $deals_without_bonus = $this->getDealsWithoutBonus();

        $result = array_merge($current_deals, $deals_without_bonus, []);

        return $this->getDataForModal($result);
    }

    public function getPlanSales()
    {
        /**
         * Получает записи из инфоблока "План продаж"
         */

        $startDate = $this->startDate;
        $endDate = $this->endDate;

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

            $result['UF_PLAN'][] = [
                'value' => $item['UF_PLAN'],
                'userId' => $item['UF_USER_ID'],
            ];
            $result['UF_DEALS_IN_PROCESS'][] = [
                'value' => $item['UF_DEALS_IN_PROCESS'],
                'userId' => $item['UF_USER_ID'],
            ];
            $result['UF_ADDITIONAL_SALES'][] = [
                'value' => $item['UF_ADDITIONAL_SALES'],
                'userId' => $item['UF_USER_ID'],
            ];
        }

        unset($query);

        return $result;
    }

    private function getDataForModal($item)
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
                'UF_IS_PASS_CONFIRMED' => $i['UF_IS_PASS_CONFIRMED'] === '1',
                'dep' => $i['SUMMA'] - ($i['PAYMENT_PFR_SUM'] + $i['PAYMENT_REGION_SUM'] + $i['DEBT_RESTRICT_SUM']),
                'is_current' => $i['IS_CURRENT'],
                'without_bonus' => !empty($i['WITHOUT_BONUS'])
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

    public function setPlanSales($inputValue, $inputManagerId, $type)
    {
        /**
         * Добавляет/обновлет запись в инфоблоке "План продаж"
         */

        global $USER;

        $startDate = $this->startDate;
        $endDate = $this->endDate;


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


}