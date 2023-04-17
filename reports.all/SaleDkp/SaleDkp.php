<?php
namespace Components\Vaganov\ReportsAll\SaleDkp;

use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Type\DateTime;

Loader::IncludeModule('crm');

class SaleDkp
{
    /**
     * Класс для создания отчета
     */

    public function __construct() {}

    public function run($startDate = false, $endDate = false, $departments = false)
    {
        return $this->getData($startDate, $endDate, $departments);
    }

    public function getData($startDate = false, $endDate = false, $departments = false)
    {
        $users = $this->getUsers($departments);
        $result = [];
        $managers = [];

        foreach ($users as $department) {
            foreach ($department as $value) {
                $result['COUNT'][$value['DEPARTMENT']][$value['ID']]['WITH_DKP'] = 0;
                $result['COUNT'][$value['DEPARTMENT']][$value['ID']]['WITHOUT_DKP'] = 0;
                $result['DEALS'][$value['DEPARTMENT']][$value['ID']]['WITH_DKP'] = [];
                $result['DEALS'][$value['DEPARTMENT']][$value['ID']]['WITHOUT_DKP'] = [];
                $result['COUNT'][$value['DEPARTMENT']][$value['ID']]['NAME'] = $value['NAME'];

                $managers[] = $value['ID'];
            }
        }

        $result['m'] = $managers;

        $arFilter = [
            'CATEGORY_ID' => 8,
            'ASSIGNED_BY_ID' => $managers,
            '!=STAGE_ID' => ['C8:WON', 'C8:LOSE'],
            '!=UF_DKP_ALL_FAMILY' => null
        ];

        if (!empty($startDate) && !empty($endDate)) {
            $arFilter['>=UF_DKP_CHANGE_DATE_TIME'] = new \Bitrix\Main\Type\DateTime($startDate);
            $arFilter['<=UF_DKP_CHANGE_DATE_TIME'] = new \Bitrix\Main\Type\DateTime($endDate);
        }

        $db_res = \CCrmDeal::GetListEx(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'ASSIGNED_BY_ID',
                'UF_DKP_ALL_FAMILY',
                'DATE_CREATE',
                'CONTACT_LAST_NAME',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME'
            ],
            []
        );

        $deals = [];

        while ($deal = $db_res->Fetch()) {
            $deals[(int)$deal['ASSIGNED_BY_ID']][] = $deal;
        }

        $mainSaleDepart = self::getDepart(['ID' => [241]]);

        $departs = self::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $result['DEPARTMENTS'] = [];

        foreach ($departs as $depart) {
            if ($depart['ID'] !== '241') {
                $result['DEPARTMENTS'][$depart['ID']] = $depart['NAME'];
            }
        }

        foreach ($result['DEALS'] as $departmentId => $item) {
            foreach ($item as $key => $value) {
                foreach ($deals[$key] as $deal) {
                    $initials = $deal['CONTACT_LAST_NAME'] . ' ' . trim(mb_substr($deal['CONTACT_NAME'], 0, 1) . '. ' . mb_substr($deal['CONTACT_SECOND_NAME'], 0, 1) . '.');

                    $result['D'][] = $initials;

                    if ($deal['UF_DKP_ALL_FAMILY'] === 'Y') {
                        $result['DEALS'][$departmentId][$key]['WITH_DKP'][] = "<div><a class='chartjs-tooltip-link mt-1 mb-1' href='/b/edz/?deal_id=$deal[ID]&show' target='_blank'>$initials</a></div>";
                    } else {
                        $result['DEALS'][$departmentId][$key]['WITHOUT_DKP'][] = "<div><a class='chartjs-tooltip-link mt-1 mb-1' href='/b/edz/?deal_id=$deal[ID]&show' target='_blank'>$initials</a></div>";
                    }
                }
            }
        }

        foreach ($result['COUNT'] as $departmentId => $item) {
            foreach ($item as $key => $value) {
                foreach ($deals[$key] as $deal) {
                    if ($deal['UF_DKP_ALL_FAMILY'] === 'Y') {
                        $result['COUNT'][$departmentId][$key]['WITH_DKP']++;
                    } else {
                        $result['COUNT'][$departmentId][$key]['WITHOUT_DKP']++;
                    }
                }
            }
        }
        return $result;
    }

    public function setDate($dealId)
    {
        $result = DealTable::update($dealId, [
            'UF_DKP_CHANGE_DATE_TIME' => (new DateTime())->format('d.m.Y H:i')
        ]);

        return $result->getId();
    }

    public static function getDepart($arFilter) {
        $arFilter['IBLOCK_ID'] = 5;
        $arFilter['ACTIVE'] = 'Y';
        $arFilter['!=ID'] = 252;

        $dbRes = \CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            $arFilter,
            false,
            ['UF_HEAD']
        );

        $departs = [];

        while ($i = $dbRes->Fetch()) {
            $departs[] = [
                'ID' => $i['ID'],
                'NAME' => $i['NAME'],
                'LEFT_MARGIN' => $i['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $i['RIGHT_MARGIN'],
                'UF_HEAD' => $i['UF_HEAD'] ? : '',
            ];
        }

        return $departs;
    }

    public static function getUsers($departments = false) {
        $mainSaleDepart = self::getDepart(['ID' => [241]]);

        $saleDeparts = self::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        if (empty($departments)) {
            $departments = array_map(function($i) {
                return $i['ID'];
            }, $saleDeparts);
        }

        $by = 'id';
        $order = 'asc';

        $dbRes = \CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'UF_DEPARTMENT' => $departments,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        $departsHead = [];

        foreach ($saleDeparts as $item) {
            if ($item['UF_HEAD'] && $item['UF_HEAD'] !== '45') {
                $departsHead[] = $item['UF_HEAD'];
            }
        }

        $users = [];

        while ($item = $dbRes->Fetch()) {
            if (!in_array($item['ID'], $departsHead)) {
                if ($item['ID'] !== '701') {
                    $users[$item['UF_DEPARTMENT'][0]][] = [
                        'NAME' => trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']),
                        'ID' => $item['ID'],
                        'DEPARTMENT' => $item['UF_DEPARTMENT'][0]
                    ];
                }
            }
        }

        return $users;
    }
}