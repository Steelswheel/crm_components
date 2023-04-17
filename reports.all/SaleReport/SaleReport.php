<?php

namespace Components\Vaganov\ReportsAll\SaleReport;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;
use Bitrix\Voximplant\StatisticTable;
use Bitrix\Crm\ActivityTable;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Entity\Query;
use Vaganov\Helper;

Loader::includeModule('voximplant');
Loader::IncludeModule('crm');

class SaleReport
{
    /**
     * Класс для создания отчета отдела продаж
     */

    public function __construct()
    {

    }

    public function run($startDate, $endDate, $inputValue, $inputManagerId, $isInput)
    {
        return $this->saleReportAction($startDate, $endDate, $inputValue, $inputManagerId, $isInput);
    }

    public function getReportSaleHeader()
    {
        /**
         * Формируем заголовок таблицы
         */

        $header[] = [
            [
                'value' => 'ОТДЕЛ',
                'rowspan' => 3,
                'class' => 'yellow'
            ],
            [
                'value' => 'МЕНЕДЖЕР',
                'rowspan' => 3,
                'class' => 'yellow'
            ],
            [
                'value' => 'ВОРОНКА ПРОДАЖ',
                'colspan' => 6,
                'class' => 'blue'
            ],
            [
                'value' => 'KPI',
                'colspan' => 2,
                'class' => 'green'
            ],
            [
                'value' => 'ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ',
                'colspan' => 5,
                'class' => 'red'
            ],
            [
                'value' => 'РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ',
                'colspan' => 3,
                'class' => 'purple'
            ]
        ];

        $header[] = [
            [
                'value' => 'ЗАВЕДЕНО ЗАЯВОК',
                'class' => 'blue',
                'rowspan' => 2
            ],
            [
                'value' => 'РЕШЕНИЕ КЗ',
                'class' => 'blue',
                'colspan' => 3
            ],
            [
                'value' => 'ОФОРМЛЕНО КД',
                'class' => 'blue',
                'rowspan' => 2
            ],
            [
                'value' => 'СДАНО<br>ПФР / СОЦ',
                'class' => 'blue',
                'rowspan' => 2
            ],
            [
                'value' => 'Заведено и одобрено заявок',
                'colspan' => 2,
                'class' => 'green'
            ],
            [
                'value' => 'Звонки лидам',
                'colspan' => 3,
                'class' => 'red'
            ],
            [
                'value' => 'Отправлено КП (Email)',
                'rowspan' => 2,
                'class' => 'red'
            ],
            [
                'value' => 'Новый партнер (одобрен СБ)',
                'rowspan' => 2,
                'class' => 'red'
            ],
            [
                'value' => 'Звонки действующим партнерам',
                'colspan' => 3,
                'class' => 'purple'
            ]
        ];

        $header[] = [
            [
                'value' => 'ОДОБРЕНО',
                'class' => 'blue'
            ],
            [
                'value' => 'ОТКАЗАНО',
                'class' => 'blue'
            ],
            [
                'value' => 'НА ДОРАБОТКУ',
                'class' => 'blue'
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
                'value' => 'Кол-во исходящих звонков (попыток)',
                'class' => 'red'
            ],
            [
                'value' => 'Кол-во соединений (дозвоны - входящие и исходящие)',
                'class' => 'red'
            ],
            [
                'value' => 'Время разговора (входящие и исходящие)',
                'class' => 'red'
            ],
            [
                'value' => 'Кол-во исходящих звонков (попыток)',
                'class' => 'purple'
            ],
            [
                'value' => 'Кол-во соединений (дозвоны - входящие и исходящие)',
                'class' => 'purple'
            ],
            [
                'value' => 'Время разговора (входящие и исходящие)',
                'class' => 'purple'
            ]
        ];

        return $header;
    }

    public function getReportSaleUsers()
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
                        if (!empty($d[$departData[$res['UF_DEPARTMENT'][0]]])) {
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
            if (!empty($d[$departData[$depart]]) && count($d[$departData[$depart]]) > 1) {
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

    public function setPlanKPI($startDate, $endDate, $inputValue, $inputManagerId)
    {
        $entity = Helper::includeHlTable('raff_manager_kpi');
        $entityClass = $entity->getDataClass();

        $query = new Query($entity);
        $query
            ->setSelect([
                'ID'
            ])
            ->setFilter([
                'UF_MANAGER_ID' => $inputManagerId,
                'UF_KPI_ID' => 1,//Заведено заявок, 2 - оформлено ДЗ, 3 - привлечено партнеров
                '><UF_RELEVANT_DATE' => [$startDate, $endDate]
            ])
            ->exec();

        $kpi = false;

        foreach ($query->fetchAll() as $value) {
            $kpi = $value['ID'];
        }

        unset($query);

        if (!$kpi) {
            $result = $entityClass::add([
                'UF_MANAGER_ID' => $inputManagerId,
                'UF_KPI_VALUE' => $inputValue,
                'UF_RELEVANT_DATE' => $startDate,
                'UF_KPI_ID' => 1
            ]);
        } else {
            $result = $entityClass::update($kpi, [
                'UF_KPI_VALUE' => $inputValue
            ]);
        }

        if (!$result->isSuccess()) {
            return $errors = $result->getErrorMessages();
        } else {
            return $kpi;
        }
    }

    public function getPlanKPI($startDate, $endDate)
    {
        /**
         * Получаем KPI, установленный за определенный период (плановый).
         * Берется минимальное значение по дате актуальности, большее или равное конечной дате.
         */

        $entity = Helper::includeHlTable('raff_manager_kpi');

        $query = new Query($entity);
        $query
            ->setSelect([
                'ID',
                'UF_MANAGER_ID',
                'UF_KPI_VALUE',
                'UF_RELEVANT_DATE'
            ])
            ->setFilter([
                'UF_KPI_ID' => 1,//Заведено заявок, 2 - оформлено ДЗ, 3 - привлечено партнеров
                '><UF_RELEVANT_DATE' => [$startDate, $endDate]
            ])
            ->exec();

        $arPlan = [];

        foreach ($query->fetchAll() as $item) {
            if (empty($item['UF_KPI_VALUE'])) {
                $item['UF_KPI_VALUE'] = 0;
            }

            $arPlan[$item['UF_MANAGER_ID']] = $item['UF_KPI_VALUE'];
        }

        unset($query);

        return $arPlan;
    }

    public function getCallsData($startDate, $endDate)
    {
        /**
         * Получаем по звонкам от контактов и от лидов (параметр $type),
         * совершенных за определенный период.
         * Параметр $all = true формирует данные и по входящим,
         * и по исходящим звонкам.
         */

        $query = new Query(StatisticTable::getEntity());

        $query
            ->registerRuntimeField('LEAD', [
                'data_type' => 'Bitrix\Crm\LeadTable',
                'reference' => [
                    '=this.CRM_ENTITY_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CRM_ENTITY_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'PORTAL_USER_ID',
                'LEAD_TITLE' => 'LEAD.TITLE',
                'LEAD_NAME' => 'LEAD.NAME',
                'LEAD_SECOND_NAME' => 'LEAD.SECOND_NAME',
                'LEAD_LAST_NAME' => 'LEAD.LAST_NAME',
                'CALL_START_DATE',
                'CALL_DURATION',
                'INCOMING',
                'CALL_FAILED_CODE',
                'CRM_ENTITY_TYPE',
                'CRM_ENTITY_ID'
            ])
            ->setFilter([
                'CRM_ENTITY_TYPE' => ['LEAD', 'CONTACT'],
                '><CALL_START_DATE' => [$startDate, $endDate],
            ])
            ->exec();

        $result = [];
        $leadExternalCalls = [];
        $leadConnections = [];
        $contactExternalCalls = [];
        $contactConnections = [];

        foreach ($query->fetchAll() as $call) {
            $result[] = $call;
        }

        unset($query);

        foreach ($result as $key => $value) {
            if ($value['CRM_ENTITY_TYPE'] === 'LEAD') {
                if ((int)$value['INCOMING'] === 1) {
                    $leadExternalCalls[$value['PORTAL_USER_ID']][] = $value;

                    if ((int)$value['CALL_DURATION'] >= 10 && (int)$value['CALL_FAILED_CODE'] === 200) {
                        $leadConnections[$value['PORTAL_USER_ID']][] = $value;
                    }
                } else {
                    if ((int)$value['CALL_DURATION'] >= 10 && (int)$value['CALL_FAILED_CODE'] === 200) {
                        $leadConnections[$value['PORTAL_USER_ID']][] = $value;
                    }
                }
            } else {
                if ((int)$value['INCOMING'] === 1) {
                    $contactExternalCalls[$value['PORTAL_USER_ID']][] = $value;

                    if ((int)$value['CALL_DURATION'] >= 10 && (int)$value['CALL_FAILED_CODE'] === 200) {
                        $contactConnections[$value['PORTAL_USER_ID']][] = $value;
                    }
                } else {
                    if ((int)$value['CALL_DURATION'] >= 10 && (int)$value['CALL_FAILED_CODE'] === 200) {
                        $contactConnections[$value['PORTAL_USER_ID']][] = $value;
                    }
                }
            }
        }

        return [
            'LEAD_EXTERNAL_CALLS' => $leadExternalCalls,
            'LEAD_CONNECTIONS' => $leadConnections,
            'CONTACT_EXTERNAL_CALLS' => $contactExternalCalls,
            'CONTACT_CONNECTIONS' => $contactConnections
        ];
    }

    public function checkNewEdp($startDate, $endDate)
    {
        /**
         * Проверяем, был ли переход ЭДП со 2 на 3 стадию в указанный период
         */

        $result = [];

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'DEAL_HISTORY.CREATED_TIME']
            ])
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'TIME',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'STAGE_ID'
            ])
            ->setFilter([
                'DEAL_HISTORY.CATEGORY_ID' => 10,
                'DEAL_HISTORY.STAGE_ID' => 'C10:6',
                '><TIME' => [$startDate, $endDate]
            ])
            ->exec();

        $data = $query->fetchAll();

        unset($query);

        foreach ($data as $d) {
            $result[$d['ASSIGNED_BY_ID']][] = $d;
        }

        return $result;
    }

    public function getPartnersRequests($startDate, $endDate)
    {
        /**
         * Получает данные по заявкам от партнеров.
         * Забираем все заявки, перешедшие на 5 стадию
         * за определенный период в последний раз.
         */

        $query = new Query(DealTable::getEntity());

        $query
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
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['max(%s)', 'DEAL_HISTORY.CREATED_TIME']
            ])
            ->setSelect([
                'EDP' => 'DEAL.ID',
                'EDP_NAME' => 'EDP_CONTACT.NAME',
                'EDP_SECOND_NAME' => 'EDP_CONTACT.SECOND_NAME',
                'EDP_LAST_NAME' => 'EDP_CONTACT.LAST_NAME',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'ID',
                'ASSIGNED_BY_ID',
                'TIME',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923'
            ])
            ->setFilter([
                'DEAL.CATEGORY_ID' => 10,
                '><TIME' => [$startDate, $endDate],
                'DEAL_HISTORY.STAGE_ID' => 'C8:20'
            ])
            ->exec();

        $allRequests = [];

        foreach ($query->fetchAll() as $r) {
            if ($r['ASSIGNED_BY_ID'] === '34') {
                $allRequests['45'][] = $r;
            } else {
                $allRequests[$r['ASSIGNED_BY_ID']][] = $r;
            }
        }

        unset($query);

        return $allRequests;
    }

    public function getCallsModal($item)
    {
        $result = [];
        $count = 1;

        foreach ($item as $i) {
            $data = [
                'count' => $count,
                'type' => (int)$i['INCOMING'] === 1 ? 'Исходящий' : 'Входящий',
                'icon' => (int)$i['INCOMING'] === 1 ? 'icon-call icon-outgoing-call' : 'icon-call icon-incoming-call',
                'date' => (new \DateTime($i['CALL_START_DATE']))->format('d.m.Y'),
                'duration' => Helper::getFormattedTime($i['CALL_DURATION']),
                'link' => $i['CRM_ENTITY_TYPE'] === 'LEAD' ? '/crm/lead/details/' . $i['CRM_ENTITY_ID'] . '/' : '/crm/contact/details/' . $i['CRM_ENTITY_ID'] . '/',
                'name' => $i['CRM_ENTITY_TYPE'] === 'LEAD' ? $i['LEAD_TITLE'] : $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME']
            ];

            $result[] = $data;

            $count++;
        }

        return $result;
    }

    public function getRopsInfo() {
        $mainSaleDepart = \Vaganov\Helper::getDepart(['ID' => ['53']]);
        $saleDeparts = \Vaganov\Helper::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $departsIds = array_map(function($i) {
            return $i['ID'];
        }, $saleDeparts);

        $departsIds[] = 53;
        $departsHead = [];

        foreach ($saleDeparts as $item) {
            if ($item['UF_HEAD']) {
                $departsHead[$item['ID']] = $item['UF_HEAD'];
            }
        }

        $by = 'last_name';
        $order = 'asc';

        $dbRes = \CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'UF_DEPARTMENT' => $departsIds,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        $users = [];

        while ($item = $dbRes->Fetch()) {
            $head =  isset($departsHead[$item['UF_DEPARTMENT'][0]])
                ? $departsHead[$item['UF_DEPARTMENT'][0]]
                : null;

            if ($departsHead[$item['UF_DEPARTMENT'][0]] === '45') {
                $head = null;
            }

            if ($departsHead[$item['UF_DEPARTMENT'][0]] === $item['ID']) {
                $head = null;
            }

            $users[] = [
                'id' => $item['ID'],
                'head' => $head
            ];

        }

        return $users;
    }

    public function getLeadSendedMails($startDate, $endDate)
    {
        /**
         * Получает из таблицы ActivityTable самые первые письма,
         * отправленные лидам за определенный период. Самые первые выбраны потому,
         * что идентифицировать конкретно что за письмо - невозможно.
         */

        $query = new Query(ActivityTable::getEntity());
        $query
            ->registerRuntimeField('LEAD', [
                'data_type' => 'Bitrix\Crm\LeadTable',
                'reference' => [
                    '=this.OWNER_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('FIRST_DATE', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'CREATED']
            ])
            ->setSelect([
                'LEAD_ID' => 'LEAD.ID',
                'TITLE' => 'LEAD.TITLE',
                'LAST_NAME' => 'LEAD.LAST_NAME',
                'NAME' => 'LEAD.NAME',
                'SECOND_NAME' => 'LEAD.SECOND_NAME',
                'OWNER_ID',
                'FIRST_DATE',
                'MANAGER' => 'LEAD.ASSIGNED_BY_ID',
                'STATUS_ID' => 'LEAD.STATUS_ID'
            ])
            ->setFilter([
                '><FIRST_DATE' => [$startDate, $endDate],
                'OWNER_TYPE_ID' => 1,//Лид, 2 - сделка, 3 - контакт, 4 - компания
                'PROVIDER_TYPE_ID' => 'EMAIL',
                'DIRECTION' => 2//Исходящие
            ])
            ->exec();

        $mails = [];
        $users = $this->getRopsInfo();

        foreach ($query->fetchAll() as $mail) {
            foreach ($users as $user) {
                if ((int)$user['id'] === (int)$mail['MANAGER']) {
                    if (!empty($user['head'])) {
                        $commentQuery = new Query(TimelineTable::getEntity());
                        $commentQuery
                            ->registerRuntimeField('BIND', [
                                'data_type' => 'Bitrix\Crm\Timeline\Entity\TimelineBindingTable',
                                'reference' => [
                                    '=this.ID' => 'ref.OWNER_ID',
                                ],
                            ])
                            ->setSelect([
                                'CREATED',
                                'ENTITY_ID' => 'BIND.ENTITY_ID',
                                'AUTHOR_ID',
                                'COMMENT'
                            ])
                            ->setFilter([
                                'ENTITY_ID' => $mail['LEAD_ID'],
                                'AUTHOR_ID' => $user['head'],
                                '!=COMMENT' => null
                            ])
                            ->exec();

                        foreach ($commentQuery->fetchAll() as $comment) {
                            $mail['COMMENT'][] = $comment['CREATED'] . ' ' . $comment['COMMENT'];
                        }
                    } else {
                        $commentQuery = new Query(TimelineTable::getEntity());
                        $commentQuery
                            ->registerRuntimeField('BIND', [
                                'data_type' => 'Bitrix\Crm\Timeline\Entity\TimelineBindingTable',
                                'reference' => [
                                    '=this.ID' => 'ref.OWNER_ID',
                                ],
                            ])
                            ->setSelect([
                                'CREATED',
                                'ENTITY_ID' => 'BIND.ENTITY_ID',
                                'AUTHOR_ID',
                                'COMMENT'
                            ])
                            ->setFilter([
                                'ENTITY_ID' => $mail['LEAD_ID'],
                                'AUTHOR_ID' => 618,
                                '!=COMMENT' => null
                            ])
                            ->exec();

                        foreach ($commentQuery->fetchAll() as $comment) {
                            $mail['COMMENT'][] = $comment['CREATED'] . ' ' . $comment['COMMENT'];
                        }
                    }
                }
            }

            $mails[$mail['MANAGER']][] = $mail;
        }

        unset($query);

        return $mails;
    }

    public function getMailsModal($item)
    {
        /**
         * Формирует данные по письмам для модального окна.
         */

        $result = [];
        $count = 1;

        foreach ($item as $i) {
            $date = (new \DateTime($i['FIRST_DATE']))->format('d.m.Y');
            $name = empty($i['NAME']) ? $i['TITLE'] : $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME'];

            switch($i['STATUS_ID']) {
                case 'NEW':
                    $status = 'Не обработан';
                    break;
                case 'JUNK':
                    $status = 'Некачественный лид';
                    break;
                case 'CONVERTED':
                    $status = 'Партнер';
                    break;
                default:
                    $status = 'В работе';
                    break;
            }

            $result[] = [
                'count' => $count,
                'date' => $date,
                'link' => '/crm/lead/details/' . $i['OWNER_ID'] . '/',
                'name' => $name,
                'status' => $status
            ];

            $count++;
        }

        return $result;
    }

    public function getEdpModal($item)
    {
        $result = [];
        $count = 1;
        $stages = \CCrmDeal::GetStages(10);

        foreach ($item as $i) {
            $data = [
                'id' => $i['ID'],
                'count' => $count,
                'date' => (new \DateTime($i['TIME']))->format('d.m.Y'),
                'fio' => $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME'],
                'link' => '/b/edp/?deal_id=' . $i['ID'] . '/',
                'stage' => $stages[$i['STAGE_ID']]['NAME']
            ];

            $result[] = $data;

            $count++;
        }

        return $result;
    }

    public function getEdzModal($item)
    {
        $result = [];
        $count = 1;
        $stages = \CCrmDeal::GetStages(8);

        foreach ($item as $i) {
            $data = [
                'id' => $i['ID'],
                'count' => $count,
                'date' => (new \DateTime($i['TIME']))->format('d.m.Y'),
                'borrower_fio' => $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME'],
                'borrower_link' => '/b/edz/?deal_id=' . $i['ID'] . '&show',
                'stage' => $stages[$i['STAGE_ID']]['NAME'],
                'partner_fio' => $i['EDP_LAST_NAME'] . ' ' . $i['EDP_NAME'] . ' ' . $i['EDP_SECOND_NAME'],
                'partner_link' => '/b/edp/?deal_id=' . $i['EDP'] . '/'
            ];

            $result[] = $data;

            $count++;
        }

        return $result;
    }

    public function getPfrModal($item)
    {
        $result = [];
        $count = 1;
        $stages = \CCrmDeal::GetStages(8);

        foreach ($item as $i) {
            $data = [
                'id' => $i['ID'],
                'count' => $count,
                'date' => (new \DateTime($i['TIME']))->format('d.m.Y'),
                'borrower_fio' => $i['LAST_NAME'] . ' ' . $i['NAME'] . ' ' . $i['SECOND_NAME'],
                'borrower_link' => '/b/edz/?deal_id=' . $i['ID'] . '&show',
                'stage' => $stages[$i['STAGE_ID']]['NAME'],
                'partner_fio' => $i['EDP_LAST_NAME'] . ' ' . $i['EDP_NAME'] . ' ' . $i['EDP_SECOND_NAME'],
                'partner_link' => '/b/edp/?deal_id=' . $i['EDP'] . '/',
                'prf_date' => $i['DATE_PFR_SEND'] ? (new \DateTime($i['DATE_PFR_SEND']))->format('d.m.Y') : '',
                'rsk_date' => $i['DATE_RB_SEND'] ? (new \DateTime($i['DATE_RB_SEND']))->format('d.m.Y') : ''
            ];

            $result[] = $data;

            $count++;
        }

        return $result;
    }

    public function getDealsInfo($startDate, $endDate, $stage)
    {
        /**
         * Возвращает данные по сделкам МСК/РСК,
         * у которых переход на стадию был в заданный период
         */

        $query = new Query(DealTable::getEntity());

        $query
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
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'DEAL_HISTORY.CREATED_TIME']
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
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'SB_DECISION' => 'UF_CRM_1509357927',
                'KZ_DECISION' => 'UF_CRM_1509358050'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                [
                    'LOGIC' => 'OR',
                    '!=MSK_SUM' => null,
                    '!=RSK_SUM' => null,
                ],
                'CATEGORY_ID' => 8,
                'DEAL_HISTORY.CATEGORY_ID' => 8,
                '><TIME' => [$startDate, $endDate],
                'DEAL_HISTORY.STAGE_ID' => $stage
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

        return $result;
    }

    public function getApprovedDeals($startDate, $endDate)
    {
        /**
         * Одобренные сделки по МСК/РСК
         */

        $query = new Query(DealTable::getEntity());

        $query
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
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'DEAL_HISTORY.CREATED_TIME']
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
                'PROGRAM' => 'UF_CRM_1518969192',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                [
                    'LOGIC' => 'OR',
                    '!=MSK_SUM' => null,
                    '!=RSK_SUM' => null
                ],
                'CATEGORY_ID' => 8,
                'DEAL_HISTORY.CATEGORY_ID' => 8,
                'DEAL_HISTORY.STAGE_ID' => 'C8:20',
                '><DEAL_HISTORY.CREATED_TIME' => [$startDate, $endDate]
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

        return $result;
    }

    public function getRefusedDeals($startDate, $endDate)
    {
        /**
         * Отклоненные сделки по МСК/РСК
         */

        $query = new Query(DealTable::getEntity());

        $query
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
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'DEAL_HISTORY.CREATED_TIME']
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
                'PROGRAM' => 'UF_CRM_1518969192',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'SB_DECISION' => 'UF_CRM_1509357927',
                'KZ_DECISION' => 'UF_CRM_1509358050',
                'HISTORY_STAGE_ID' => 'DEAL_HISTORY.STAGE_ID'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                'DEAL_HISTORY.CATEGORY_ID' => 8,
                '><DEAL_HISTORY.CREATED_TIME' => [$startDate, $endDate],
                [
                    'LOGIC' => 'OR',
                    [
                        'SB_DECISION' => 160,
                        'HISTORY_STAGE_ID' => 'C8:EXECUTING',
                        '><TIME' => [$startDate, $endDate]
                    ],
                    [
                        'KZ_DECISION' => 441,
                        'HISTORY_STAGE_ID' => 'C8:21',
                        '><TIME' => [$startDate, $endDate]
                    ]
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

        return $result;
    }

    public function getRevisionDeals($startDate, $endDate)
    {
        /**
         * Отправленные на доработку сделки по МСК/РСК
         */

        $query = new Query(DealTable::getEntity());

        $query
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
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->registerRuntimeField('DEAL_HISTORY', [
                'data_type' => 'Bitrix\Crm\History\Entity\DealStageHistoryTable',
                'reference' => [
                    '=this.ID' => 'ref.OWNER_ID',
                ],
            ])
            ->registerRuntimeField('TIME', [
                'data_type' => 'dateTime',
                'expression' => ['min(%s)', 'DEAL_HISTORY.CREATED_TIME']
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
                'PROGRAM' => 'UF_CRM_1518969192',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923',
                'SB_DECISION' => 'UF_CRM_1509357927',
                'KZ_DECISION' => 'UF_CRM_1509358050',
                'HISTORY_STAGE_ID' => 'DEAL_HISTORY.STAGE_ID'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                'DEAL_HISTORY.CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    [
                        'SB_DECISION' => 161,
                        'HISTORY_STAGE_ID' => 'C8:EXECUTING',
                        '><TIME' => [$startDate, $endDate]
                    ],
                    [
                        'KZ_DECISION' => 466,
                        'HISTORY_STAGE_ID' => 'C8:21',
                        '><TIME' => [$startDate, $endDate]
                    ]
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

        return $result;
    }

    public function getResultDeals($startDate, $endDate)
    {
        /**
         * Берем все ЭДЗ, у которых последний переход по стадиям был в выбранный период,
         * при этом на 25 стадии есть прикрепленные расписки и даты
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
                'PROGRAM' => 'UF_CRM_1518969192',
                'CATEGORY_ID',
                'STAGE_ID',
                'MSK_SUM' => 'UF_CRM_1584337896',
                'RSK_SUM' => 'UF_CRM_1584337923'
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

        foreach ($query->fetchAll() as $deal) {
            $deal['TIME'] = !empty($deal['DATE_PFR_SEND']) ? $deal['DATE_PFR_SEND'] : $deal['DATE_RB_SEND'];

            if (!empty($deal['MSK_SUM']) && !empty($deal['RSK_SUM'])) {
                if (!empty($deal['DATE_PFR_SEND']) && !empty($deal['DATE_RB_SEND'])) {
                    if (new \DateTime($deal['DATE_PFR_SEND']) > new \DateTime($deal['DATE_RB_SEND'])) {
                        $biggerDate = new \DateTime($deal['DATE_PFR_SEND']);
                    } else {
                        $biggerDate = new \DateTime($deal['DATE_RB_SEND']);
                    }

                    if (new \DateTime($startDate) <= $biggerDate && $biggerDate <= new \DateTime($endDate)) {
                        if ($deal['ASSIGNED_BY_ID'] === '34') {
                            $result['45'][] = $deal;
                        } else {
                            $result[$deal['ASSIGNED_BY_ID']][] = $deal;
                        }
                    }
                }
            } else {
                if ($deal['ASSIGNED_BY_ID'] === '34') {
                    $result['45'][] = $deal;
                } else {
                    $result[$deal['ASSIGNED_BY_ID']][] = $deal;
                }
            }
        }

        return $result;
    }

    public function getReportSaleBody($startDate, $endDate)
    {
        /**
         * Формирует тело таблицы отчета.
         */

        $users = $this->getReportSaleUsers();

        $planKpi = $this->getPlanKPI($startDate, $endDate);

        $factKpi = $this->getPartnersRequests($startDate, $endDate);

        $newEPDcheckedBySB = $this->checkNewEdp($startDate, $endDate);

        $callsData = $this->getCallsData($startDate, $endDate);

        $leadExternalCalls = $callsData['LEAD_EXTERNAL_CALLS'];
        $leadConnectionsData = $callsData['LEAD_CONNECTIONS'];
        $contactExternalCalls = $callsData['CONTACT_EXTERNAL_CALLS'];
        $contactConnectionsData = $callsData['CONTACT_CONNECTIONS'];

        $leadSendedMails = $this->getLeadSendedMails($startDate, $endDate);

        $newDeals = $this->getDealsInfo($startDate, $endDate, 'C8:EXECUTING');
        $approvedDeals = $this->getApprovedDeals($startDate, $endDate);
        $refusedDeals = $this->getRefusedDeals($startDate, $endDate);
        $revisionDeals = $this->getRevisionDeals($startDate, $endDate);
        $preparedDocs = $this->getDealsInfo($startDate, $endDate, 'C8:1');
        $resultDeals = $this->getResultDeals($startDate, $endDate);

        $total = [];
        $companyTotal = [];

        $body = [];

        foreach ($users as $depart => $departUsers) {
            $isFirst = true;

            foreach ($departUsers as $key => $value) {
                if ($isFirst) {
                    $city = [
                        'value' => $depart,
                        'rowspan' => count($departUsers),
                        'class' => 'sale-report-table-city pl-1 pr-1'
                    ];

                    $isFirst = false;
                } else {
                    $city = ['value' => null];
                }

                $conversionNewCount = !empty($newDeals[$value['ID']]) ? count($newDeals[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_NEW'] += $conversionNewCount;
                $conversionNewData = $this->getEdzModal($newDeals[$value['ID']]);

                $conversionApprovedCount = !empty($approvedDeals[$value['ID']]) ? count($approvedDeals[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_APPROVED'] += $conversionApprovedCount;
                $conversionApprovedData = $this->getEdzModal($approvedDeals[$value['ID']]);

                $conversionRefusedCount = !empty($refusedDeals[$value['ID']]) ? count($refusedDeals[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_REFUSED'] += $conversionRefusedCount;
                $conversionRefusedData = $this->getEdzModal($refusedDeals[$value['ID']]);

                $conversionRevisionCount = !empty($revisionDeals[$value['ID']]) ? count($revisionDeals[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_REVISION'] += $conversionRevisionCount;
                $conversionRevisionData = $this->getEdzModal($revisionDeals[$value['ID']]);

                $conversionDocsCount = !empty($preparedDocs[$value['ID']]) ? count($preparedDocs[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_DOCS'] += $conversionDocsCount;
                $conversionDocsData = $this->getEdzModal($preparedDocs[$value['ID']]);

                $conversionResultCount = !empty($resultDeals[$value['ID']]) ? count($resultDeals[$value['ID']]) : 0;
                $total[$depart]['CONVERSION_RESULT'] += $conversionResultCount;
                $conversionResultData = $this->getPfrModal($resultDeals[$value['ID']]);

                //KPI - Заведено и одобрено заявок - План
                $planKpiCount = 0;

                if (!is_null($planKpi[$value['ID']])) {
                    $planKpiCount = $planKpi[$value['ID']];
                }

                $total[$depart]['PLAN_KPI_TOTAL'] += (int)$planKpiCount;

                //KPI - Заведено и одобрено заявок - Факт
                $factKpiCount = 0;

                if (!empty($factKpi) && !empty($factKpi[$value['ID']])) {
                    $factKpiCount = count($factKpi[$value['ID']]);
                }

                $total[$depart]['FACT_KPI_TOTAL'] += $factKpiCount;

                $factKpiData = $this->getEdzModal($factKpi[$value['ID']]);

                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во исходящих звонков (попыток)
                $leadExternalCallsCount = 0;

                if (!is_null($leadExternalCalls[$value['ID']])) {
                    $leadExternalCallsCount = count($leadExternalCalls[$value['ID']]);
                }

                $total[$depart]['LEAD_TRY_CALLS_TOTAL'] += $leadExternalCallsCount;

                $leadExternalCallsData = $this->getCallsModal($leadExternalCalls[$value['ID']]);

                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во соединений (дозвоны - входящие и исходящие)
                $leadConnectionsCount = 0;

                if (!empty($leadConnectionsData[$value['ID']])) {
                    $leadConnectionsCount = count($leadConnectionsData[$value['ID']]);
                }

                $total[$depart]['LEAD_CONNECTIONS_TOTAL'] += $leadConnectionsCount;

                $leadAllCallsData = $this->getCallsModal($leadConnectionsData[$value['ID']]);

                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Время разговора min. (входящие и исходящие)
                $leadCallsDuration = 0;

                foreach ($leadConnectionsData[$value['ID']] as $item) {
                    $leadCallsDuration += (int)$item['CALL_DURATION'];
                }

                $total[$depart]['LEAD_CONNECTIONS_DURATION_TOTAL'] += $leadCallsDuration;

                $leadCallsDuration = Helper::getFormattedTime($leadCallsDuration);

                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Отправлено КП (Email)
                $leadSendedMailCount = 0;

                if (!empty($leadSendedMails[$value['ID']])) {
                    $leadSendedMailCount = count($leadSendedMails[$value['ID']]);
                }

                $total[$depart]['LEAD_SENDED_MAIL_TOTAL'] += $leadSendedMailCount;

                $leadSendedMailsData = $this->getMailsModal($leadSendedMails[$value['ID']]);

                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Новый партнер (одобрен СБ)
                $newEPDcheckedBySBCount = 0;

                if (!empty($newEPDcheckedBySB[$value['ID']])) {
                    $newEPDcheckedBySBCount = count($newEPDcheckedBySB[$value['ID']]);
                }

                $total[$depart]['NEW_EDP_CHECKED_BY_SB_TOTAL'] += $newEPDcheckedBySBCount;

                $newEPDcheckedBySBData = $this->getEdpModal($newEPDcheckedBySB[$value['ID']]);

                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во исходящих звонков (попыток)
                $contactExternalCallsCount = 0;

                if (!is_null($contactExternalCalls[$value['ID']])) {
                    $contactExternalCallsCount = count($contactExternalCalls[$value['ID']]);
                }

                $total[$depart]['CONTACT_TRY_CALLS_TOTAL'] += $contactExternalCallsCount;

                $contactExternalCallsData = $this->getCallsModal($contactExternalCalls[$value['ID']]);

                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во соединений (дозвоны - входящие и исходящие)
                $contactConnectionsCount = empty($contactConnectionsData[$value['ID']]) ? 0 : count($contactConnectionsData[$value['ID']]);
                $total[$depart]['CONTACT_CONNECTIONS_TOTAL'] +=  $contactConnectionsCount;

                $contactAllCallsData = $this->getCallsModal($contactConnectionsData[$value['ID']]);

                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Время разговора min. (входящие и исходящие)
                $contactCallsDuration = 0;

                foreach ($contactConnectionsData[$value['ID']] as $item) {
                    $contactCallsDuration += (int)$item['CALL_DURATION'];
                }

                $total[$depart]['CONTACT_CONNECTIONS_DURATION_TOTAL'] += $contactCallsDuration;

                $contactCallsDuration = Helper::getFormattedTime($contactCallsDuration);

                $body[] = [
                    'class' => 'sale-report-row',
                    'value' => [
                        //ОТДЕЛ
                        $city,
                        //МЕНЕДЖЕР
                        [
                            'value' => $value['NAME'],
                            'class' => 'sale-report-name'
                        ],
                        //ВОРОНКА ПРОДАЖ - Заведено заявок
                        [
                            'value' => $conversionNewCount,
                            'class' => 'openModal edz',
                            'data' => $conversionNewData,
                            'title' => 'ВОРОНКА ПРОДАЖ - ЗАВЕДЕНО ЗАЯВОК'
                        ],
                        //ВОРОНКА ПРОДАЖ - ОДОБРЕНО
                        [
                            'value' => $conversionApprovedCount,
                            'class' => 'openModal edz',
                            'data' => $conversionApprovedData,
                            'title' => 'ВОРОНКА ПРОДАЖ - ОДОБРЕНО'
                        ],
                        //ВОРОНКА ПРОДАЖ - ОТКАЗАНО
                        [
                            'value' => $conversionRefusedCount,
                            'class' => 'openModal edz',
                            'data' => $conversionRefusedData,
                            'title' => 'ВОРОНКА ПРОДАЖ - ОТКАЗАНО'
                        ],
                        //ВОРОНКА ПРОДАЖ - НА ДОРАБОТКУ
                        [
                            'value' => $conversionRevisionCount,
                            'class' => 'openModal edz',
                            'data' => $conversionRevisionData,
                            'title' => 'ВОРОНКА ПРОДАЖ - НА ДОРАБОТКУ'
                        ],
                        //ВОРОНКА ПРОДАЖ - ГОТОВО КД
                        [
                            'value' => $conversionDocsCount,
                            'class' => 'openModal edz',
                            'data' => $conversionDocsData,
                            'title' => 'ВОРОНКА ПРОДАЖ - ГОТОВО КД'
                        ],
                        //ВОРОНКА ПРОДАЖ - СДАНО ПФР/СОЦ
                        [
                            'value' => $conversionResultCount,
                            'class' => 'openModal pfr',
                            'data' => $conversionResultData,
                            'title' => 'ВОРОНКА ПРОДАЖ - СДАНО ПФР/СОЦ'
                        ],
                        //KPI - Заведено и одобрено заявок - План
                        [
                            'value' => $planKpiCount,
                            'class' => 'sale-report-input',
                            'input' => true,
                            'manager-id' => $value['ID']
                        ],
                        //KPI - Заведено и одобрено заявок - Факт
                        [
                            'value' => $factKpiCount,
                            'class' => 'openModal edz',
                            'data' => $factKpiData,
                            'title' => 'Заведено и одобрено заявок - Факт'
                        ],
                        //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во исходящих звонков (попыток)
                        [
                            'value' => $leadExternalCallsCount,
                            'class' => 'openModal calls',
                            'data' => $leadExternalCallsData,
                            'title' => 'Звонки лидам - кол-во исходящих звонков (попыток)'
                        ],
                        //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во соединений (дозвоны - входящие и исходящие)
                        [
                            'value' => $leadConnectionsCount,
                            'class' => 'openModal calls',
                            'data' => $leadAllCallsData,
                            'title' => 'Звонки лидам - кол-во соединений (дозвоны - входящие и исходящие)'
                        ],
                        //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Время разговора min. (входящие и исходящие)
                        ['value' => $leadCallsDuration],
                        //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Отправлено КП (Email)
                        [
                            'value' => $leadSendedMailCount,
                            'class' => 'openModal mails',
                            'data' => $leadSendedMailsData,
                            'title' => 'Отправлено КП (Email)'
                        ],
                        //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Новый партнер (одобрен СБ)
                        [
                            'value' => $newEPDcheckedBySBCount,
                            'class' => 'openModal edp',
                            'data' => $newEPDcheckedBySBData,
                            'title' =>'Новый партнер (одобрен СБ)'
                        ],
                        //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во исходящих звонков (попыток)
                        [
                            'value' => $contactExternalCallsCount,
                            'class' => 'openModal calls',
                            'data' => $contactExternalCallsData,
                            'title' =>  'Звонки действующим партнерам - кол-во исходящих звонков (попыток)'
                        ],
                        //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во соединений (дозвоны - входящие и исходящие)
                        [
                            'value' => $contactConnectionsCount,
                            'class' => 'openModal calls',
                            'data' => $contactAllCallsData,
                            'title' => 'Звонки действующим партнерам - Кол-во соединений (дозвоны - входящие и исходящие)'
                        ],
                        //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Время разговора min. (входящие и исходящие)
                        ['value' => $contactCallsDuration]
                    ]
                ];
            }

            $companyTotal['CONVERSION_NEW'] += $total[$depart]['CONVERSION_NEW'];
            $companyTotal['CONVERSION_APPROVED'] += $total[$depart]['CONVERSION_APPROVED'];
            $companyTotal['CONVERSION_REFUSED'] += $total[$depart]['CONVERSION_REFUSED'];
            $companyTotal['CONVERSION_REVISION'] += $total[$depart]['CONVERSION_REVISION'];
            $companyTotal['CONVERSION_DOCS'] += $total[$depart]['CONVERSION_DOCS'];
            $companyTotal['CONVERSION_RESULT'] += $total[$depart]['CONVERSION_RESULT'];

            $companyTotal['PLAN_KPI'] += $total[$depart]['PLAN_KPI_TOTAL'];
            $companyTotal['FACT_KPI'] += $total[$depart]['FACT_KPI_TOTAL'];
            $companyTotal['LEAD_TRY_CALLS'] += $total[$depart]['LEAD_TRY_CALLS_TOTAL'];
            $companyTotal['LEAD_CONNECTIONS'] += $total[$depart]['LEAD_CONNECTIONS_TOTAL'];
            $companyTotal['LEAD_CONNECTIONS_DURATION'] += $total[$depart]['LEAD_CONNECTIONS_DURATION_TOTAL'];
            $companyTotal['LEAD_SENDED_MAIL'] += $total[$depart]['LEAD_SENDED_MAIL_TOTAL'];
            $companyTotal['NEW_EDP_CHECKED_BY_SB'] += $total[$depart]['NEW_EDP_CHECKED_BY_SB_TOTAL'];
            $companyTotal['CONTACT_TRY_CALLS'] += $total[$depart]['CONTACT_TRY_CALLS_TOTAL'];
            $companyTotal['CONTACT_CONNECTIONS'] += $total[$depart]['CONTACT_CONNECTIONS_TOTAL'];
            $companyTotal['CONTACT_CONNECTIONS_DURATION'] += $total[$depart]['CONTACT_CONNECTIONS_DURATION_TOTAL'];

            $total[$depart]['LEAD_CONNECTIONS_DURATION_TOTAL'] = Helper::getFormattedTime($total[$depart]['LEAD_CONNECTIONS_DURATION_TOTAL']);
            $total[$depart]['CONTACT_CONNECTIONS_DURATION_TOTAL'] = Helper::getFormattedTime($total[$depart]['CONTACT_CONNECTIONS_DURATION_TOTAL']);

            $body[] = [
                'class' => 'sale-report-total-row',
                'value' => [
                    [
                        'value' => 'ИТОГО:',
                        'colspan' => 2,
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - Заведено заявок
                    [
                        'value' => $total[$depart]['CONVERSION_NEW'],
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - ОДОБРЕНО
                    [
                        'value' => $total[$depart]['CONVERSION_APPROVED'],
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - ОТКАЗАНО
                    [
                        'value' => $total[$depart]['CONVERSION_REFUSED'],
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - НА ДОРАБОТКУ
                    [
                        'value' => $total[$depart]['CONVERSION_REVISION'],
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - ГОТОВО КД
                    [
                        'value' => $total[$depart]['CONVERSION_DOCS'],
                        'class' => 'light-blue'
                    ],
                    //ВОРОНКА ПРОДАЖ - СДАНО ПФР/СОЦ
                    [
                        'value' => $total[$depart]['CONVERSION_RESULT'],
                        'class' => 'light-blue'
                    ],
                    //KPI - Заведено и одобрено заявок - План
                    [
                        'value' => $total[$depart]['PLAN_KPI_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //KPI - Заведено и одобрено заявок - Факт
                    [
                        'value' => $total[$depart]['FACT_KPI_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во исходящих звонков (попыток)
                    [
                        'value' => $total[$depart]['LEAD_TRY_CALLS_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во соединений (дозвоны - входящие и исходящие)
                    [
                        'value' => $total[$depart]['LEAD_CONNECTIONS_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Время разговора min. (входящие и исходящие)
                    [
                        'value' => $total[$depart]['LEAD_CONNECTIONS_DURATION_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Отправлено КП (Email)
                    [
                        'value' => $total[$depart]['LEAD_SENDED_MAIL_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Новый партнер (одобрен СБ)
                    [
                        'value' => $total[$depart]['NEW_EDP_CHECKED_BY_SB_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во исходящих звонков (попыток)
                    [
                        'value' => $total[$depart]['CONTACT_TRY_CALLS_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во соединений (дозвоны - входящие и исходящие)
                    [
                        'value' => $total[$depart]['CONTACT_CONNECTIONS_TOTAL'],
                        'class' => 'light-blue'
                    ],
                    //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Время разговора min. (входящие и исходящие)
                    [
                        'value' => $total[$depart]['CONTACT_CONNECTIONS_DURATION_TOTAL'],
                        'class' => 'light-blue'
                    ]
                ]
            ];
        }

        $companyTotal['LEAD_CONNECTIONS_DURATION'] = Helper::getFormattedTime($companyTotal['LEAD_CONNECTIONS_DURATION']);
        $companyTotal['CONTACT_CONNECTIONS_DURATION'] = Helper::getFormattedTime($companyTotal['CONTACT_CONNECTIONS_DURATION']);

        $body[] = [
            'class' => 'sale-report-total-company-row',
            'value' => [
                [
                    'value' => 'ИТОГО (КОМПАНИЯ):',
                    'colspan' => 2,
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - Заведено заявок
                [
                    'value' => $companyTotal['CONVERSION_NEW'],
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - ОДОБРЕНО
                [
                    'value' => $companyTotal['CONVERSION_APPROVED'],
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - ОТКАЗАНО
                [
                    'value' => $companyTotal['CONVERSION_REFUSED'],
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - НА ДОРАБОТКУ
                [
                    'value' => $companyTotal['CONVERSION_REVISION'],
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - ГОТОВО КД
                [
                    'value' => $companyTotal['CONVERSION_DOCS'],
                    'class' => 'orange'
                ],
                //ВОРОНКА ПРОДАЖ - СДАНО ПФР/СОЦ
                [
                    'value' => $companyTotal['CONVERSION_RESULT'],
                    'class' => 'orange'
                ],
                //KPI - Заведено и одобрено заявок - План
                [
                    'value' => $companyTotal['PLAN_KPI'],
                    'class' => 'orange'
                ],
                //KPI - Заведено и одобрено заявок - Факт
                [
                    'value' => $companyTotal['FACT_KPI'],
                    'class' => 'orange'
                ],
                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во исходящих звонков (попыток)
                [
                    'value' => $companyTotal['LEAD_TRY_CALLS'],
                    'class' => 'orange'
                ],
                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Кол-во соединений (дозвоны - входящие и исходящие)
                [
                    'value' => $companyTotal['LEAD_CONNECTIONS'],
                    'class' => 'orange'
                ],
                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Звонки лидам - Время разговора min. (входящие и исходящие)
                [
                    'value' => $companyTotal['LEAD_CONNECTIONS_DURATION'],
                    'class' => 'orange'
                ],
                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Отправлено КП (Email)
                [
                    'value' => $companyTotal['LEAD_SENDED_MAIL'],
                    'class' => 'orange'
                ],
                //ПРИВЛЕЧЕНИЕ НОВЫХ ПАРТНЕРОВ - Новый партнер (одобрен СБ)
                [
                    'value' => $companyTotal['NEW_EDP_CHECKED_BY_SB'],
                    'class' => 'orange'
                ],
                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во исходящих звонков (попыток)
                [
                    'value' => $companyTotal['CONTACT_TRY_CALLS'],
                    'class' => 'orange'
                ],
                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Кол-во соединений (дозвоны - входящие и исходящие)
                [
                    'value' => $companyTotal['CONTACT_CONNECTIONS'],
                    'class' => 'orange'
                ],
                //РАБОТА С ДЕЙСТВУЮЩИМИ ПАРТНЕРАМИ - Звонки действующим партнерам - Время разговора min. (входящие и исходящие)
                [
                    'value' => $companyTotal['CONTACT_CONNECTIONS_DURATION'],
                    'class' => 'orange'
                ]
            ]
        ];

        return $body;
    }

    public function saleReportAction($startDate, $endDate, $inputValue, $inputManagerId, $isInput)
    {
        /**
         * Возвращает отчет полностью.
         */

        global $USER;

        $startTimestamp = strtotime($startDate . ' 00:00:00');
        $endTimestamp = strtotime($endDate . ' 23:59:59');

        $startDate = date('d.m.Y H:i:s', $startTimestamp);
        $endDate = date('d.m.Y H:i:s', $endTimestamp);

        if (!$isInput) {
            return [
                'title' => 'ОТЧЕТ ОТДЕЛА ЗАЙМОВ',
                'table' => [
                    'head' => $this->getReportSaleHeader(),
                    'body' => $this->getReportSaleBody($startDate, $endDate)
                ],
                'isAdmin' => $USER->IsAdmin()
            ];
        } else {
            if ($USER->IsAdmin()) {
                return $this->setPlanKPI($startDate, $endDate, $inputValue, $inputManagerId);
            } else {
                return false;
            }
        }
    }
}