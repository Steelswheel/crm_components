<?php


namespace Components\Vaganov\ReportsAll\PayOnApplications;


class PayOnApplications
{
    /**
     * @return array
     */



    public function getBeforeTable(){
        return ['Подразделение','#','Менеджер'];
    }

    public function getProgram(){
        return [
            ['id' => '1', 'name' => '1. ЗАЙМ ИПОТЕЧНЫЙ С ЗАЛОГОМ'],
            ['id' => '2', 'name' => '2. ЗАЙМ ИПОТЕЧНЫЙ ЭКОНОМНЫЙ'],
            ['id' => '3', 'name' => '3. ЗАЙМ НА СТРОИТЕЛЬСТВО БЕЗ ЗАЛОГА'],
            ['id' => '4', 'name' => '4. ЗАЙМ ИПОТЕЧНЫЙ РСК'],
        ];
    }

    public function reportFinHead(){
        $program = array_map(function($i){return $i['name'];},$this->getProgram());
        $before = $this->getBeforeTable();

        $header[] = array_merge(
            array_map(function($i){return ['value' => $i, 'rowspan' => 2];},$before),
            array_map(function($i){
                return [
                    'value' => $i,
                    'colspan' => 2,
                    'style' => [
                        'border-left-width' => '3px',
                        'border-right-width' => '3px',
                    ],

                ];},$program)
        );

        $header[] = array_merge(
            array_map(function(){return ['value' => null];},$before),
            array_map(function($i){
                return [
                    'value' => ($i % 2 === 0) ? 'кол-во ДЗ' : 'ДОХОД',
                    'style' => [
                        'border-left-width' =>  ($i % 2 === 0) ? '3px' : '1px',
                        'border-right-width' => ($i % 2 === 0) ? '1px' : '3px',
                    ]
                ];},
                array_keys(array_merge($program,$program)))
        );

        return $header;
    }



    public function PayOnApplicationsTableAction($date = "2021-03-01"){

        $dateMonth = $date;
        $dateStart = $dateMonth." 00:00:00";
        $dateEnd = date("Y-m-t", strtotime($dateMonth))." 23:59:59";


        $table = [
            'title' => 'Начисление бонусов',
            'excel' => [
                'width' => [20, 5, 20, 8, 8, 8, 8, 8, 8, 8],
                'height' => [30, 30],
            ],
            'table' => [
                'head' => $this->reportFinHead(),
                'body' => $this->reportFin($dateStart,$dateEnd),
            ],
        ];
        return $table;
    }

    public function reportData($dateStart,$dateEnd){
        GLOBAL $DB;

        $sql = "
        select d.id, concat(c.LAST_NAME,' ',c.NAME,' ',c.SECOND_NAME) c_name , d.ASSIGNED_BY_ID,u.LAST_NAME, uf.UF_CRM_1581549726083,uf.UF_CRM_1518969192 program, uf.UF_CRM_1567493378 sum
        from b_crm_deal d
        left join b_uts_crm_deal uf on uf.VALUE_ID = d.ID
        left join b_user u on u.ID = d.ASSIGNED_BY_ID
        left join b_crm_contact c on c.ID = d.CONTACT_ID
        where d.CATEGORY_ID = 8 
            and (uf.UF_CRM_1518967556 between '$dateStart' and '$dateEnd'
                or (uf.UF_CRM_1518967556 is null and uf.UF_CRM_1584934425 between '$dateStart' and '$dateEnd')
                or (uf.UF_CRM_1518967556 is null) )
        order by u.LAST_NAME, uf.UF_CRM_1518969192
        ";

        $res = $DB->Query($sql);
        $arData = [];
        while ($item = $res->Fetch()){
            $arData[] = $item;
        }
        return $arData;
    }

    public function reportFinHelpTable($dz,$userItem){
        if (!array_values($dz)){
            return null;
        }
        $h = [[['value' => 'Клиент'],['value' => 'Сумма']]];
        foreach ($dz as $itemTableHelp){
            $b[] = [
                ['value' => $itemTableHelp['c_name']],
                ['value' => $itemTableHelp['sum']]
            ];
        }
        return [
            'excel' => ['width' => [30]],
            'title' => $userItem['NAME'],
            'table' =>
                ['head' => $h, 'body' => $b],

        ];
    }

    public function reportFin($dateStart,$dateEnd){
        $arData = $this->reportData($dateStart,$dateEnd);
        $usersDepart = $this->departUser();
        $program = $this->getProgram();


        $table = [];
        foreach ($usersDepart as $keyD => $departItem){

            $allSum = [];


            foreach ($departItem['users'] as $keyU => $userItem){
                $tableRow = [];

                if ($keyU === 0){
                    $tableRow[] = ['value' => $departItem['depart'], 'rowspan' => count($departItem['users']) + 1];
                }else{
                    $tableRow[] = ['value' => null];
                }


                $tableRow[] = ['value' => $keyU + 1];
                $tableRow[] = ['value' => $userItem['NAME']];

                // $usersDepart[$keyD]['users'][$keyU]['data'] = $userData;

                foreach ($program as $itemProgram){


                    $dz = array_filter($arData, function($i) use ($userItem,$itemProgram){
                        return $i['ASSIGNED_BY_ID'] === $userItem['ID']
                            and $i['program'] === $itemProgram['id'];
                    });
                    $dzSum = array_sum(array_map(function($i){ return $i['sum'];},$dz));

                    $tableRow[] = [
                        'value' => count($dz),
                        'table' => $this->reportFinHelpTable($dz,$userItem),
                        'style' => ['border-left-width' => '3px']
                    ];
                    $tableRow[] = [
                        'value' => $dzSum,
                        'style' => ['border-right-width' => '3px']
                    ];
                    // Итоги по отделу
                    $allSum['count'.$itemProgram['id']]+= count($dz);
                    $allSum['sum'.$itemProgram['id']]+= $dzSum;
                }
                $table[] = $tableRow;

            }

            // Итоги по отделу
            $rowSum = [];
            $rowSum[] = ['value' => null];
            $rowSum[] = ['value' => 'Итоги по отделу', 'colspan' => 2,  'style' => ['background' => '#dcdcdc']];
            foreach (array_values($allSum) as $key => $itemAllSum){
                $rowSum[] = [
                    'value' => $itemAllSum,
                    'style' => [
                        'background' => '#dcdcdc' ,
                        'border-left-width' =>  ($key % 2 === 0) ? '3px' : '1px',
                        'border-right-width' => ($key % 2 === 0) ? '1px' : '3px',
                    ]
                ];
            }
            $table[] = $rowSum;
        }

        return $table;
    }

    public function departUser(){
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

        $arFilter = [
            "IBLOCK_ID" => 5,
            "ID" => $departList,
        ];
        $departInfo = \CIBlockSection::GetList(array(),$arFilter,false,["ID", "NAME", "UF_SHORT_NAME"]);
        while ($row = $departInfo->GetNext())
        {
            if (!empty($row["UF_SHORT_NAME"]))
                $departData[$row["ID"]] = $row["UF_SHORT_NAME"];
        }

        foreach ($departList as $depart) {
            $userData = \Bitrix\Intranet\Util::GetDepartmentEmployees([
                'DEPARTMENTS' => [
                    $depart
                ],
                'RECURSIVE' => 'Y',
                'ACTIVE' => 'Y',
                'SELECT' => [
                    'LAST_NAME',
                    'NAME',
                    'UF_SORT',
                    'UF_MANAGER_TYPE'
                ]
            ]);
            $users = [];
            $i = 1;
            while ($res = $userData->Fetch()) {
                if ($res["ID"] == "34") continue;
                //если не указана сортировка, то порядок определим по ID
                $sort = empty($res["UF_SORT"]) ? $res["ID"] : $res["UF_SORT"];
                $findDeparts[$res["ID"]] = $depart;
                $users[] = [
                    "ID" => $res["ID"],
                    "NAME" => $res["LAST_NAME"],
                    "s" => (int)$sort
                ];
            }
            usort($users, function ($a,$b){return $a['s'] - $b['s'];});
            $usersDepart[] = [
                "depart_id" => $depart,
                "depart" => $departData[$depart],
                "users" => $users,

            ];
        }
        return $usersDepart;
    }



}