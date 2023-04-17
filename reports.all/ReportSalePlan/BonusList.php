<?php
namespace Components\Vaganov\ReportsAll\ReportSalePlan;

use Bitrix\Main\Loader;
use Vaganov\Helper;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;



Loader::IncludeModule('crm');

class BonusList
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function getExcelAction($month, $ids)
    {
        $ids = json_decode($ids, 1);

        return (new ReportExcel($month, $ids))->getFile();
    }

    public static function getUsers($id) {
        $mainSaleDepart = Helper::getDepart(['ID' => [$id]]);

        $saleDeparts = Helper::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $departsIds = array_map(function($i) {
            return $i['ID'];
        }, $saleDeparts);

        $by = 'last_name';
        $order = 'asc';

        $arFilter = [
            'UF_DEPARTMENT' => $departsIds,
            '!=EXTERNAL_AUTH_ID' => 'bot',
            'ACTIVE' => 'Y'
        ];

        $dbRes = \CUser::GetList(
            $by,
            $order,
            $arFilter,
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        $users = [];

        while ($item = $dbRes->Fetch()) {
            $users[$item['ID']] = [
                'NAME' => trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']),
                'WORK_POSITION' => $item['WORK_POSITION']
            ];
        }

        return $users;
    }

    private function getPlanDeals($startDate, $endDate, $IDs)
    {
        /**
         * Получает записи из инфоблока "План продаж"
         */

        $entity = Helper::includeHlTable('b_raff_sales_plan');

        $query = new Query($entity);
        $query
            ->setSelect([
                'UF_PLAN',
                'UF_USER_ID'
            ])
            ->setFilter([
                '><UF_MONTH' => [$startDate, $endDate],
                'UF_USER_ID' => $IDs
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $item) {
            if (empty($item['UF_PLAN'])) {
                $item['UF_PLAN'] = 0;
            }

            $result[$item['UF_USER_ID']] = (int)$item['UF_PLAN'];
        }

        unset($query);

        return $result;
    }

    private function getFactDeals($startDate, $endDate, $IDs)
    {
        $stages = \CCrmDeal::getStages(8);

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'STAGE_ID',
                'ASSIGNED_BY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'MSK_SUMM' => 'UF_CRM_1584337896',
                'RSK_SUMM' => 'UF_CRM_1584337923',
                'UF_BONUS_PAIDED',
                'UF_BONUS_PAIDED_DATE',
                'UF_IS_PASS_CONFIRMED'
            ])
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
                ],
                'ASSIGNED_BY_ID' => $IDs
            ])
            ->exec();

        $deals = [];

        foreach ($query->fetchAll() as $deal) {
            if (!empty($deal['MSK_SUMM']) && !empty($deal['RSK_SUMM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $sendDate = new \DateTime($deal['DATE_PFR_SEND']);
                        $deal['SEND_TYPE'] = 'ПФР';
                    } else {
                        $sendDate = new \DateTime($deal['DATE_RB_SEND']);
                        $deal['SEND_TYPE'] = 'РСК';
                    }

                    if (new \DateTime($startDate) <= $sendDate && $sendDate <= new \DateTime($endDate)) {
                        $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                        $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];

                        $deal['SEND_DATE'] = $sendDate->format('d.m.Y');

                        $deal['UF_BONUS_PAIDED'] = $deal['UF_BONUS_PAIDED'] === '1';
                        $deal['UF_IS_PASS_CONFIRMED'] = $deal['UF_IS_PASS_CONFIRMED'] === '1';

                        if (!empty($deal['UF_BONUS_PAIDED_DATE'])) {
                            $deal['UF_BONUS_PAIDED_DATE'] = (new \DateTime($deal['UF_BONUS_PAIDED_DATE']))->format('d.m.Y');
                        }

                        if (!empty($deal['PAYMENT_PFR_DATA']) && !empty($deal['PAYMENT_REGION_DATA'])) {
                            if (new \DateTime($deal['PAYMENT_PFR_DATA']) > new \DateTime($deal['PAYMENT_REGION_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        } else {
                            if (!empty($deal['PAYMENT_PFR_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        }

                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $deals['45'][] = $deal;
                        } else {
                            $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                        }
                    }
                }
            } else {
                $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];

                if (!empty($deal['DATE_PFR_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'ПФР';
                }
                if (!empty($deal['DATE_RB_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'РСК';
                }

                if (!empty($deal['PAYMENT_PFR_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'ПФР';
                }

                if (!empty($deal['PAYMENT_REGION_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'РСК';
                }

                $deal['UF_BONUS_PAIDED'] = $deal['UF_BONUS_PAIDED'] === '1';
                $deal['UF_IS_PASS_CONFIRMED'] = $deal['UF_IS_PASS_CONFIRMED'] === '1';

                if (!empty($deal['UF_BONUS_PAIDED_DATE'])) {
                    $deal['UF_BONUS_PAIDED_DATE'] = (new \DateTime($deal['UF_BONUS_PAIDED_DATE']))->format('d.m.Y');
                }

                if ($deal['ASSIGNED_BY_ID'] === '34') {
                    $deals['45'][] = $deal;
                } else {
                    $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                }
            }
        }

        return $deals;
    }

    public function getManagersData()
    {
        $users = $this->getUsers(241);
        $ro = Helper::getROPs();

        $IDs = [];
        $managers = [];

        foreach ($users as $key => $value) {
            if (!in_array((int)$key, $ro)) {
                if ((int)$key !== 34) {
                    $managers[$key] = $value;
                }

                $IDs[] = $key;
            }
        }

        return [
            'IDs' => $IDs,
            'managers' => $managers
        ];
    }

    public function getSalesAction($month)
    {
        $managersData = $this->getManagersData();

        $IDs = $managersData['IDs'];
        $managers = $managersData['managers'];

        $date = new \DateTime('01.' . $month);
        $startDate = $date->modify('first day of this month')->format('d.m.Y');
        $endDate = $date->modify('last day of this month')->format('d.m.Y');

        $planDeals = $this->getPlanDeals($startDate, $endDate, $IDs);
        $factDeals = $this->getFactDeals($startDate, $endDate, $IDs);

        $data = [];

        Helper::includeHlTable('bonus_check');

        $checkData = \BonusCheckTable::getList([
            'select' => [
                'UF_DATE_CREATE',
                'UF_SALES_PLAN_CHECK_DATE',
                'UF_USER_ID',
                'UF_MANAGER_ID'
            ],
            'filter' => [
                'UF_MANAGER_ID' => $IDs,
                '><UF_SALES_PLAN_CHECK_DATE' => [$startDate, $endDate]
            ],
        ])->fetchAll();

        $check = [];

        if (!empty($checkData)) {
            foreach ($checkData as $item) {
                $check[$item['UF_MANAGER_ID']] = $item;
            }
        }

        foreach ($managers as $key => $value) {
            $factDealsCount = count($factDeals[$key]);
            $planDealsCount = $planDeals[$key] ? : 0;

            $confirmedDeals = array_values(array_filter($factDeals[$key], function ($item) {
                return !empty($item['UF_IS_PASS_CONFIRMED']);
            }));

            $confirmedDealsCount = count($confirmedDeals);

            $percent = !empty($planDealsCount) ? ceil(($confirmedDealsCount * 100) / $planDealsCount) : 0;
            $status = '';

            if (!empty($check[$key])) {
                $users = Helper::getUsers(53);
                $manager_last_name = explode(' ', $users[$check[$key]['UF_USER_ID']])[0];

                $status = ['ПРОВЕРЕНО, ', $manager_last_name . ' ' . $check[$key]['UF_DATE_CREATE']];
            }

            $data[] = [
                'MANAGER_ID' => $key,
                'MANAGER' => $value['NAME'],
                'PLAN' => $planDealsCount,
                'FACT' => $factDealsCount,
                'CONFIRMED' => $confirmedDealsCount,
                'PERCENT' => $percent,
                'STATUS' => $status,
                'FACT_DEALS' => $factDeals[$key],
                'CONFIRMED_DEALS' => $confirmedDeals
            ];
        }

        return $data;
    }

    public function setConfirmAction($id)
    {
        DealTable::update($id, ['UF_IS_PASS_CONFIRMED' => '1']);

        return true;
    }

    public function setPercentAction($ids, $percent, $managerId, $month)
    {
        $ids = json_decode($ids);

        if (!empty($ids)) {
            $date = new \DateTime('01.' . $month);
            $startDate = $date->modify('first day of this month')->format('d.m.Y');

            global $USER;

            foreach ($ids as $id) {
                DealTable::update($id, ['UF_BONUS_PLAN_PERCENT' => (int)$percent]);
            }

            Helper::includeHlTable('bonus_check');

            \BonusCheckTable::add([
                'UF_DATE_CREATE' => (new \DateTime())->format('d.m.Y'),
                'UF_SALES_PLAN_CHECK_DATE' => $startDate,
                'UF_USER_ID' => (int)$USER->GetID(),
                'UF_MANAGER_ID' => (int)$managerId
            ]);

            return true;
        }

        return false;
    }

    public function setBonusPaidAction($ids, $managerId, $month)
    {
        $ids = json_decode($ids);

        if (!empty($ids)) {
            $date = new \DateTime('01.' . $month);
            $startDate = $date->modify('first day of this month')->format('d.m.Y');

            global $USER;

            foreach ($ids as $id) {
                DealTable::update($id, [
                    'UF_BONUS_PAIDED' => '1',
                    'UF_BONUS_PAIDED_DATE' => $startDate
                ]);
            }

            Helper::includeHlTable('bonus_check');

            \BonusCheckTable::add([
                'UF_DATE_CREATE' => (new \DateTime())->format('d.m.Y'),
                'UF_BONUS_CHECK_DATE' => $startDate,
                'UF_USER_ID' => (int)$USER->GetID(),
                'UF_MANAGER_ID' => (int)$managerId
            ]);

            return true;
        }

        return false;
    }

    public function getManagerBonus($manager_id, $percent)
    {
        $managers = ($this->getManagersData())['managers'];

        $work_position = trim(mb_strtolower($managers[$manager_id]['WORK_POSITION']));

        $priceV = $work_position === 'ведущий менеджер по сопровождению сделок'
            ? 'priceV1'
            : 'priceV2';

        $priceTable = [
            ['rate' => [0, 50], 'priceV1' => 500, 'priceV2' => 500],
            ['rate' => [50, 70], 'priceV1' => 1000, 'priceV2' => 1000],
            ['rate' => [70, 90], 'priceV1' => 1500, 'priceV2' => 1300],
            ['rate' => [90, 100], 'priceV1' => 1800, 'priceV2' => 1400],
            ['rate' => [100, 120], 'priceV1' => 2000, 'priceV2' => 1500],
            ['rate' => [120, 1000], 'priceV1' => 2500, 'priceV2' => 1500]
        ];

        foreach ($priceTable as $item) {
            if ($item['rate'][0] <= $percent && $percent < $item['rate'][1]) {
                return $item[$priceV];
            }
        }

        return 0;
    }

    public function getBonusDeals($startDate, $endDate, $IDs)
    {
        $managers = self::getUsers(241);
        $stages = \CCrmDeal::getStages(8);

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'UF_BONUS_PLAN_PERCENT',
                'STAGE_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'MSK_SUMM' => 'UF_CRM_1584337896',
                'RSK_SUMM' => 'UF_CRM_1584337923',
                'UF_BONUS_PAIDED',
                'UF_BONUS_PAIDED_DATE',
                'UF_TRANCHE_1_SUM',
                'UF_TRANCHE_2_SUM',
                'UF_TRANCHE_3_SUM',
                'UF_TRANCHE_4_SUM',
                DEBT_RESTRICT_SUM
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'ASSIGNED_BY_ID' => $IDs,
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '!=PAYMENT_PFR_DATA' => null,
                    '!=PAYMENT_REGION_DATA' => null
                ],
                '><UF_BONUS_PAIDED_DATE' => [$startDate, $endDate]
            ])
            ->exec();

        $deals = [];

        foreach ($query->fetchAll() as &$deal) {
            if (!empty($deal['MSK_SUMM']) && !empty($deal['RSK_SUMM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $sendDate = new \DateTime($deal['DATE_PFR_SEND']);
                        $deal['SEND_TYPE'] = 'ПФР';
                    } else {
                        $sendDate = new \DateTime($deal['DATE_RB_SEND']);
                        $deal['SEND_TYPE'] = 'РСК';
                    }

                    if (new \DateTime($startDate) <= $sendDate && $sendDate <= new \DateTime($endDate)) {
                        $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                        $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                        $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                        $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                        $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;
                        $deal['SEND_DATE'] = $sendDate->format('d.m.Y');
                        $deal['UF_BONUS_PAIDED'] = $deal['UF_BONUS_PAIDED'] === '1';

                        if (!empty($deal['UF_BONUS_PAIDED_DATE'])) {
                            $deal['UF_BONUS_PAIDED_DATE'] = (new \DateTime($deal['UF_BONUS_PAIDED_DATE']))->format('d.m.Y');
                        }

                        if (!empty($deal['PAYMENT_PFR_DATA']) && !empty($deal['PAYMENT_REGION_DATA'])) {
                            if (new \DateTime($deal['PAYMENT_PFR_DATA']) > new \DateTime($deal['PAYMENT_REGION_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        } else {
                            if (!empty($deal['PAYMENT_PFR_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        }

                        $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                        $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                        $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);

                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $deals['45'][] = $deal;
                        } else {
                            $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                        }
                    }
                }
            } else {
                $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;
                $deal['UF_BONUS_PAIDED'] = $deal['UF_BONUS_PAIDED'] === '1';

                if (!empty($deal['UF_BONUS_PAIDED_DATE'])) {
                    $deal['UF_BONUS_PAIDED_DATE'] = (new \DateTime($deal['UF_BONUS_PAIDED_DATE']))->format('d.m.Y');
                }

                if (!empty($deal['DATE_PFR_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'ПФР';
                }
                if (!empty($deal['DATE_RB_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'РСК';
                }

                if (!empty($deal['PAYMENT_PFR_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'ПФР';
                }

                if (!empty($deal['PAYMENT_REGION_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'РСК';
                }

                $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);

                if ($deal['ASSIGNED_BY_ID'] === '34') {
                    $deals['45'][] = $deal;
                } else {
                    $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                }
            }
        }

        unset($query);

        return $deals;
    }

    public function getNoBonusDeals($startDate, $endDate, $IDs)
    {
        $managers = self::getUsers(241);
        $stages = \CCrmDeal::getStages(8);

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'UF_BONUS_PLAN_PERCENT',
                'STAGE_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'MSK_SUMM' => 'UF_CRM_1584337896',
                'RSK_SUMM' => 'UF_CRM_1584337923',
                'UF_BONUS_PAIDED',
                'UF_BONUS_PAIDED_DATE',
                'UF_TRANCHE_1_SUM',
                'UF_TRANCHE_2_SUM',
                'UF_TRANCHE_3_SUM',
                'UF_TRANCHE_4_SUM',
                DEBT_RESTRICT_SUM
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'ASSIGNED_BY_ID' => $IDs,
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '><PAYMENT_PFR_DATA' => [$startDate, $endDate],
                    '><PAYMENT_REGION_DATA' => [$startDate, $endDate]
                ],
                'UF_BONUS_PAIDED_DATE' => null
            ])
            ->exec();

        $deals = [];

        foreach ($query->fetchAll() as &$deal) {
            if (!empty($deal['MSK_SUMM']) && !empty($deal['RSK_SUMM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $sendDate = new \DateTime($deal['DATE_PFR_SEND']);
                        $deal['SEND_TYPE'] = 'ПФР';
                    } else {
                        $sendDate = new \DateTime($deal['DATE_RB_SEND']);
                        $deal['SEND_TYPE'] = 'РСК';
                    }

                    if (new \DateTime($startDate) <= $sendDate && $sendDate <= new \DateTime($endDate)) {
                        $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                        $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                        $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                        $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                        $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;
                        $deal['SEND_DATE'] = $sendDate->format('d.m.Y');

                        if (!empty($deal['PAYMENT_PFR_DATA']) && !empty($deal['PAYMENT_REGION_DATA'])) {
                            if (new \DateTime($deal['PAYMENT_PFR_DATA']) > new \DateTime($deal['PAYMENT_REGION_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        } else {
                            if (!empty($deal['PAYMENT_PFR_DATA'])) {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'ПФР';
                            } else {
                                $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                                $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                                $deal['PAYMENT_TYPE'] = 'РСК';
                            }
                        }

                        $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                        $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                        $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);

                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $deals['45'][] = $deal;
                        } else {
                            $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                        }
                    }
                }
            } else {
                $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;

                if (!empty($deal['DATE_PFR_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'ПФР';
                }

                if (!empty($deal['DATE_RB_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'РСК';
                }

                if (!empty($deal['PAYMENT_PFR_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'ПФР';
                }

                if (!empty($deal['PAYMENT_REGION_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'РСК';
                }

                $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);

                if ($deal['ASSIGNED_BY_ID'] === '34') {
                    $deals['45'][] = $deal;
                } else {
                    $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                }
            }
        }

        unset($query);

        return $deals;
    }

    public function getNotPaidDeals($IDs)
    {
        $managers = self::getUsers(241);
        $stages = \CCrmDeal::getStages(8);

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
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'UF_BONUS_PLAN_PERCENT',
                'STAGE_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'MSK_SUMM' => 'UF_CRM_1584337896',
                'RSK_SUMM' => 'UF_CRM_1584337923',
                'UF_BONUS_PAIDED',
                'UF_BONUS_PAIDED_DATE',
                'UF_TRANCHE_1_SUM',
                'UF_TRANCHE_2_SUM',
                'UF_TRANCHE_3_SUM',
                'UF_TRANCHE_4_SUM',
                DEBT_RESTRICT_SUM,
                'UF_IS_BONUS_NOT_PAIDED'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'ASSIGNED_BY_ID' => $IDs,
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '!=PAYMENT_PFR_DATA' => null,
                    '!=PAYMENT_REGION_DATA' => null
                ],
                'UF_BONUS_PAIDED_DATE' => null,
                'UF_IS_BONUS_NOT_PAIDED' => null,
                'DEAL_HISTORY.STAGE_ID' => 'C8:15',
                '!=CONTACT_ID' => null
            ])
            ->exec();

        $deals = [];

        foreach ($query->fetchAll() as &$deal) {
            if (!empty($deal['MSK_SUMM']) && !empty($deal['RSK_SUMM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $sendDate = new \DateTime($deal['DATE_PFR_SEND']);
                        $deal['SEND_TYPE'] = 'ПФР';
                    } else {
                        $sendDate = new \DateTime($deal['DATE_RB_SEND']);
                        $deal['SEND_TYPE'] = 'РСК';
                    }

                    $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                    $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                    $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                    $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                    $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;
                    $deal['SEND_DATE'] = $sendDate->format('d.m.Y');

                    if (!empty($deal['PAYMENT_PFR_DATA']) && !empty($deal['PAYMENT_REGION_DATA'])) {
                        if (new \DateTime($deal['PAYMENT_PFR_DATA']) > new \DateTime($deal['PAYMENT_REGION_DATA'])) {
                            $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                            $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                            $deal['PAYMENT_TYPE'] = 'ПФР';
                        } else {
                            $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                            $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                            $deal['PAYMENT_TYPE'] = 'РСК';
                        }
                    } else {
                        if (!empty($deal['PAYMENT_PFR_DATA'])) {
                            $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                            $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                            $deal['PAYMENT_TYPE'] = 'ПФР';
                        } else {
                            $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                            $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                            $deal['PAYMENT_TYPE'] = 'РСК';
                        }
                    }

                    $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                    $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                    $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);
                    $deal['selected'] = false;

                    if ($deal['ASSIGNED_BY_ID'] === '34') {
                        $deals['45'][] = $deal;
                    } else {
                        $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                    }
                }
            } else {
                $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
                $deal['STAGE'] = $stages[$deal['STAGE_ID']]['NAME'];
                $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];
                $deal['BONUS'] = $this->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
                $deal['FORMATTED_BONUS'] = $deal['BONUS'] ? number_format($deal['BONUS'], 0, ',', ' ') : 0;

                if (!empty($deal['DATE_PFR_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'ПФР';
                }

                if (!empty($deal['DATE_RB_SEND'])) {
                    $deal['SEND_DATE'] = (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y');
                    $deal['SEND_TYPE'] = 'РСК';
                }

                if (!empty($deal['PAYMENT_PFR_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'ПФР';
                }

                if (!empty($deal['PAYMENT_REGION_DATA'])) {
                    $deal['PAYMENT_DATE'] = (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y');
                    $deal['PAYMENT_SUM'] = number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ');
                    $deal['PAYMENT_TYPE'] = 'РСК';
                }

                $tranche_all_summ = $deal['UF_TRANCHE_1_SUM'] + $deal['UF_TRANCHE_2_SUM'] + $deal['UF_TRANCHE_3_SUM'] + $deal['UF_TRANCHE_4_SUM'];
                $money_back = $deal['PAYMENT_PFR_SUM'] + $deal['PAYMENT_REGION_SUM'] + $deal[DEBT_RESTRICT_SUM];
                $deal['DEBIT'] = round($tranche_all_summ - $money_back, 2);
                $deal['selected'] = false;

                if ($deal['ASSIGNED_BY_ID'] === '34') {
                    $deals['45'][] = $deal;
                } else {
                    $deals[$deal['ASSIGNED_BY_ID']][] = $deal;
                }
            }
        }

        unset($query);

        return $deals;
    }

    public function getBonusesAction($month)
    {
        $managersData = $this->getManagersData();

        $IDs = $managersData['IDs'];
        $managers = $managersData['managers'];

        $date = new \DateTime('01.' . $month);
        $startDate = $date->modify('first day of this month')->format('d.m.Y');
        $endDate = $date->modify('last day of this month')->format('d.m.Y');

        $data = [];

        $noBonusDeals = $this->getNoBonusDeals($startDate, $endDate, $IDs);
        $bonusDeals = $this->getBonusDeals($startDate, $endDate, $IDs);
        $notPaidedDeals = $this->getNotPaidDeals($IDs);

        foreach ($managers as $key => $value) {
            $sum_bonus = 0;

            if (!empty($bonusDeals[$key])) {
                foreach ($bonusDeals[$key] as $deal) {
                    $sum_bonus += (int)$deal['BONUS'];
                }
            }

            $data[] = [
                'MANAGER_ID' => $key,
                'MANAGER' => $value['NAME'],
                'RESULT' => count($noBonusDeals[$key]),
                'BONUS' => $sum_bonus,
                'NOT_PAIDED' => count($notPaidedDeals[$key]),
                'BONUS_DEALS' => !empty($bonusDeals[$key]) ? $bonusDeals[$key] : [],
                'NO_BONUS_DEALS' => !empty($noBonusDeals[$key]) ? $noBonusDeals[$key] : [],
                'NOT_PAIDED_DEALS' => !empty($notPaidedDeals[$key]) ? $notPaidedDeals[$key] : []
            ];
        }

        return $data;
    }

}