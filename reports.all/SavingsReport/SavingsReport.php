<?php
namespace Components\Vaganov\ReportsAll\SavingsReport;

use Bitrix\Main\Loader;
use Vaganov\Helper;
use Bitrix\Crm\LeadTable;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Voximplant\StatisticTable;

Loader::IncludeModule('crm');
Loader::includeModule('voximplant');

class SavingsReport
{
    public static function getUsers() {
        $by = 'last_name';
        $order = 'asc';

        $dbRes = \CUser::GetList(
            $by,
            $order,
            [
                'UF_DEPARTMENT' => 254,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        $users = [];

        $ros = Helper::getROS();

        while ($item = $dbRes->Fetch()) {
            if ($item['ID'] !== $ros) {
                $users[$item['ID']] = trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']);
            }
        }

        return $users;
    }

    private function getExternalCalls($ids, $dateRange)
    {
        $query = new Query(StatisticTable::getEntity());

        $query
            ->registerRuntimeField('LEAD', [
                'data_type' => 'Bitrix\Crm\LeadTable',
                'reference' => [
                    '=this.CRM_ENTITY_ID' => 'ref.ID',
                ]
            ])
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CRM_ENTITY_ID' => 'ref.ID',
                ]
            ])
            ->setSelect([
                'ID',
                'CONTACT_ID' => 'CONTACT.ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'PORTAL_USER_ID',
                'LEAD_TITLE' => 'LEAD.TITLE',
                'LEAD_NAME' => 'LEAD.NAME',
                'LEAD_SECOND_NAME' => 'LEAD.SECOND_NAME',
                'LEAD_LAST_NAME' => 'LEAD.LAST_NAME',
                'LEAD_ID' => 'LEAD.ID',
                'CALL_START_DATE',
                'CALL_DURATION',
                'INCOMING',
                'CALL_FAILED_CODE',
                'CRM_ENTITY_TYPE'
            ])
            ->setFilter([
                'PORTAL_USER_ID' => $ids,
                'CRM_ENTITY_TYPE' => ['LEAD', 'CONTACT'],
                '><CALL_START_DATE' => $dateRange,
                'INCOMING' => 1 //1 - исходящие, 2 - входящие
            ])
            ->exec();

        $attempts = [];
        $success_calls = [];

        $calls = $query->fetchAll();
        $lead_ids = [];
        $contact_ids = [];

        foreach ($calls as $call) {
            if ($call['CRM_ENTITY_TYPE'] === 'LEAD') {
                if (!in_array($call['LEAD_ID'], $lead_ids)) {
                    $attempts[$call['PORTAL_USER_ID']][] = [
                        'LINK' => '/crm/lead/details/' . $call['LEAD_ID'] . '/',
                        'DATE' => (new \DateTime($call['CALL_START_DATE']))->format('d.m.Y'),
                        'CALL_DURATION' => $call['CALL_DURATION'],
                        'NAME' => !empty($call['LEAD_TITLE']) ? $call['LEAD_TITLE'] : trim($call['LEAD_LAST_NAME'] . ' ' . trim($call['LEAD_NAME'] . ' ' . $call['LEAD_SECOND_NAME']))
                    ];

                    if ((int)$call['CALL_DURATION'] >= 10 && (int)$call['CALL_FAILED_CODE'] === 200) {
                        $success_calls[$call['PORTAL_USER_ID']][] = [
                            'LINK' => '/crm/lead/details/' . $call['LEAD_ID'] . '/',
                            'DATE' => (new \DateTime($call['CALL_START_DATE']))->format('d.m.Y'),
                            'CALL_DURATION' => $call['CALL_DURATION'],
                            'NAME' => !empty($call['LEAD_TITLE']) ? $call['LEAD_TITLE'] : trim($call['LEAD_LAST_NAME'] . ' ' . trim($call['LEAD_NAME'] . ' ' . $call['LEAD_SECOND_NAME']))
                        ];
                    }

                    $lead_ids[] = $call['LEAD_ID'];
                }
            } else {
                if (!in_array($call['CONTACT_ID'], $contact_ids)) {
                    $attempts[$call['PORTAL_USER_ID']][] = [
                        'LINK' => '/crm/lead/details/' . $call['LEAD_ID'] . '/',
                        'DATE' => (new \DateTime($call['CALL_START_DATE']))->format('d.m.Y'),
                        'CALL_DURATION' => $call['CALL_DURATION'],
                        'NAME' => trim($call['LAST_NAME'] . ' ' . trim($call['NAME'] . ' ' . $call['SECOND_NAME']))
                    ];

                    if ((int)$call['CALL_DURATION'] >= 10 && (int)$call['CALL_FAILED_CODE'] === 200) {
                        $success_calls[$call['PORTAL_USER_ID']][] = [
                            'LINK' => '/crm/contact/details/' . $call['CONTACT_ID'] . '/',
                            'DATE' => (new \DateTime($call['CALL_START_DATE']))->format('d.m.Y'),
                            'CALL_DURATION' => $call['CALL_DURATION'],
                            'NAME' => trim($call['LAST_NAME'] . ' ' . trim($call['NAME'] . ' ' . $call['SECOND_NAME']))
                        ];
                    }

                    $contact_ids[] = $call['CONTACT_ID'];
                }
            }
        }

        return [
            'ATTEMPTS' => $attempts,
            'SUCCESS_CALLS' => $success_calls
        ];
    }

    private function getLeads($ids, $dateRange)
    {
        $query = new Query(LeadTable::getEntity());

        $query
            ->registerRuntimeField('HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\LeadStatusHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID'
                ]
            ])
            ->setSelect([
                'ID',
                'TITLE',
                'NAME',
                'SECOND_NAME',
                'LAST_NAME',
                'ASSIGNED_BY_ID',
                'HISTORY_STATUS_ID' => 'HISTORY.STATUS_ID',
                'DATE' => 'HISTORY.CREATED_DATE'
            ])
            ->setFilter([
                'HISTORY_STATUS_ID' => [4, 5],
                'ASSIGNED_BY_ID' => $ids,
                '><HISTORY.CREATED_DATE' => $dateRange
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $lead) {
            $result[$lead['ASSIGNED_BY_ID']][] = [
                'HISTORY_STATUS_ID' => $lead['HISTORY_STATUS_ID'],
                'ID' => $lead['ID'],
                'TITLE' => !empty($lead['TITLE']) ? $lead['TITLE'] : trim($lead['LAST_NAME'] . ' ' . trim($lead['NAME'] . ' ' . $lead['SECOND_NAME'])),
                'DATE' => (new \DateTime($lead['DATE']))->format('d.m.Y')
            ];
        }

        return $result;
    }

    private function getDeals($ids, $dateRange)
    {
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
                'DATE_CREATE',
                'ASSIGNED_BY_ID',
                'UF_BONUS_PLAN_PERCENT',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'UF_CONTRACT_AMOUNT'
            ])
            ->setFilter([
                'CATEGORY_ID' => 14,
                'ASSIGNED_BY_ID' => $ids,
                '><DATE_CREATE' => $dateRange
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            $result[$deal['ASSIGNED_BY_ID']][] = [
                'DATE' => (new \DateTime($deal['DATE_CREATE']))->format('d.m.Y'),
                'FIO' => trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME'] . ' ' . $deal['SECOND_NAME'])),
                'UF_CONTRACT_AMOUNT' => $deal['UF_CONTRACT_AMOUNT']
            ];
        }

        return $result;
    }

    public function run($startDate, $endDate)
    {
        global $USER;

        $result = [
            'table' => [],
            'isAdmin' => $USER->isAdmin() || in_array((int)$USER->GetID(), [618, 640, 42, 687]),
            'ros' => Helper::getROS()
        ];

        $managers = $this->getUsers();
        $calls = $this->getExternalCalls(array_keys($managers), [$startDate, $endDate]);
        $leads = $this->getLeads(array_keys($managers), [$startDate, $endDate]);
        $deals = $this->getDeals(array_keys($managers), [$startDate, $endDate]);

        foreach ($managers as $id => $name) {
            $appointments = array_values(array_filter($leads[$id], function ($item) {
                return $item['HISTORY_STATUS_ID'] === '4';
            }));

            $meetings = array_values(array_filter($leads[$id], function ($item) {
                return $item['HISTORY_STATUS_ID'] === '5';
            }));

            $eds = $deals[$id];
            $eds_sum = 0;

            foreach ($eds as &$deal) {
                $deal['SUM'] = number_format($deal['UF_CONTRACT_AMOUNT'], 2, ',', ' ');

                $eds_sum += (float)$deal['UF_CONTRACT_AMOUNT'];
            }

            $all_calls = array_merge($calls['ATTEMPTS'][$id], $calls['SUCCESS_CALLS'][$id], []);
            $call_duration = 0;

            foreach ($all_calls as $call) {
                $call_duration += $call['CALL_DURATION'];
            }

            foreach ($calls['ATTEMPTS'][$id] as &$call) {
                $call['CALL_DURATION'] = Helper::getFormattedTime($call['CALL_DURATION']);
            }

            foreach ($calls['SUCCESS_CALLS'][$id] as &$call) {
                $call['CALL_DURATION'] = Helper::getFormattedTime($call['CALL_DURATION']);
            }

            $result['table'][] = [
                'MANAGER' => [
                    'value' => $name
                ],
                'ATTEMPTS' => [
                    'value' => count($calls['ATTEMPTS'][$id]),
                    'modal' => [
                        'type' => 'calls',
                        'title' => 'Кол-во набранных',
                        'content' => $calls['ATTEMPTS'][$id]
                    ]
                ],
                'SUCCESS_CALLS' => [
                    'value' => count($calls['SUCCESS_CALLS'][$id]),
                    'modal' => [
                        'type' => 'calls',
                        'title' => 'Кол-во дозвонов',
                        'content' => $calls['SUCCESS_CALLS'][$id]
                    ]
                ],
                'CALLS_TIME' => [
                    'value' => Helper::getFormattedTime($call_duration)
                ],
                'APPOINTMENTS' => [
                    'value' => count($appointments),
                    'modal' => [
                        'type' => 'meetings',
                        'title' => 'Назначенные встречи',
                        'content' => $appointments
                    ]
                ],
                'MEETINGS' => [
                    'value' => count($meetings),
                    'modal' => [
                        'type' => 'meetings',
                        'title' => 'Проведенные встречи',
                        'content' => $meetings
                    ]
                ],
                'CONTRACTS_COUNT' => [
                    'value' => count($eds),
                    'modal' => [
                        'type' => 'contracts',
                        'title' => 'Кол-во контрактов',
                        'content' => $eds
                    ]
                ],
                'CONTRACTS_SUM' => [
                    'value' => number_format($eds_sum, 2, ',', ' ')
                ]
            ];
        }

        return $result;
    }
}