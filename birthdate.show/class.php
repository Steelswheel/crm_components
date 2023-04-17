<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;


class BirthdateShow extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {

    }

    function getDepart($arFilter) {
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

    public function getBirthDayData() {
        $mainSaleDepart = $this->getDepart(['ID' => ['53']]);

        $saleDeparts = $this->getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $departsIds = array_map(function($i) {
            return $i['ID'];
        }, $saleDeparts);

        $departsIds[] = 53;

        $by = 'PERSONAL_BIRTHDAY';
        $order = 'asc';

        return \CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'UF_DEPARTMENT' => $departsIds,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );
    }

    public function getNextFiveBirthdays() {
        return [];
        $allBirthdays = [];
        $nextFiveBirthdays = [];
        $birthDayData = $this->getBirthDayData();

        while ($item = $birthDayData->Fetch()) {
            if (!empty($item['PERSONAL_BIRTHDAY'])) {
                $day = (new DateTime($item['PERSONAL_BIRTHDAY']))->format('d');
                $month = (new DateTime($item['PERSONAL_BIRTHDAY']))->format('m');

                $allBirthdays[$month][$day][] = [
                    'ID' => $item['ID'],
                    'NAME' => trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']),
                    'BIRTHDAY' => (new DateTime($item['PERSONAL_BIRTHDAY']))->format('d.m')
                ];
            }
        }

        ksort($allBirthdays);

        foreach ($allBirthdays as $k => $v) {
            ksort($allBirthdays[$k]);
        }

        $date = new DateTime();
        $dayNow = $date->format('d');
        $monthNow = $date->format('m');
        $yearNow = $date->format('Y');

        function pushItems($allBirthdays, &$nextFiveBirthdays, $monthNow, $dayNow) {
            foreach ($allBirthdays[$monthNow] as $day => $data) {
                if ($day > (int)$dayNow && !in_array($data, $nextFiveBirthdays) && count($nextFiveBirthdays) < 5) {
                    foreach ($data as $value) {
                        $nextFiveBirthdays[] = $value;
                    }
                }
            }
        }

        function pushItemsNextMonth($allBirthdays, &$nextFiveBirthdays, $monthNow) {
            foreach ($allBirthdays[$monthNow] as $day => $data) {
                if (!in_array($data, $nextFiveBirthdays) && count($nextFiveBirthdays) < 5) {
                    foreach ($data as $value) {
                        $nextFiveBirthdays[] = $value;
                    }
                }
            }
        }

        pushItems($allBirthdays, $nextFiveBirthdays, $monthNow, $dayNow);

        if (count($nextFiveBirthdays) < 5) {
            $count = 1;

            while (count($nextFiveBirthdays) < 5) {
                $countStr = '+' . $count . ' month';

                $monthNow = (new \DateTime('01.' . $monthNow . '.' . $yearNow))->modify($countStr)->format('m');

                pushItemsNextMonth($allBirthdays, $nextFiveBirthdays, $monthNow);

                $count++;
            }
        }

        return $nextFiveBirthdays;
    }

    public function getEmployeesData() {
        $data = [];
        $birthDayData = $this->getBirthDayData();

        while ($item = $birthDayData->Fetch()) {
            if (!empty($item['PERSONAL_BIRTHDAY'])) {
                $today = (new DateTime())->format('d.m');
                $date = (new DateTime($item['PERSONAL_BIRTHDAY']))->format('d.m');

                if ($date === $today) {
                    $data[$item['ID']]['ID'] = $item['ID'];
                    $data[$item['ID']]['NAME'] = trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']);
                }
            }
        }

        return $data;
    }

    public function getPartnersData() {
        $partnersQuery = CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10,
                '!=STAGE_ID' => ['C10:LOSE', 'C10:WON']
            ],
            false,
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_ID',
                'ASSIGNED_BY_ID'
            ]
        );

        $partners = [];
        $partnersData = [];
        $IDs = [];
        $partnerIDs = [];

        while ($partner = $partnersQuery->Fetch()) {
            $partnerIDs[] = $partner['ID'];
            $IDs[] = $partner['CONTACT_ID'];
            $partnersData[$partner['CONTACT_ID']]['ID'] = $partner['ID'];
            $partnersData[$partner['CONTACT_ID']]['NAME'] = trim($partner['CONTACT_LAST_NAME']) . ' ' . $partner['CONTACT_NAME'] . ' ' . $partner['CONTACT_SECOND_NAME'];
            $partnersData[$partner['CONTACT_ID']]['MANAGER'] = $partner['ASSIGNED_BY_ID'];
        }

        $cont = CCrmContact::GetList([], ['ID' => $IDs], ['ID', 'UF_DATE_OF_BIRTH']);
        $congratulationStates = $this->getCongratulationState($partnerIDs);

        while ($contact = $cont->Fetch()) {
            if (!empty($contact['UF_DATE_OF_BIRTH'])) {
                $today = (new DateTime())->format('d.m');
                $date = (new DateTime($contact['UF_DATE_OF_BIRTH']))->format('d.m');

                if ($date === $today) {
                    $partners[$contact['ID']]['ID'] = $partnersData[$contact['ID']]['ID'];
                    $partners[$contact['ID']]['NAME'] = $partnersData[$contact['ID']]['NAME'];
                    $partners[$contact['ID']]['STATE'] = $congratulationStates[$partnersData[$contact['ID']]['ID']];
                    $partners[$contact['ID']]['MANAGER'] = $partnersData[$contact['ID']]['MANAGER'];
                }
            }
        }

        return $partners;
    }

    public function getCongratulationState($IDs) {
        \Vaganov\Helper::includeHlTable('happy_birthday_to_partner');

        $items = HappyBirthdayToPartnerTable::getList([
            'select' => [
                '*',
                'UF_*'
            ],
            'filter' => [
                'UF_ID_PARTNER' => $IDs
            ]
        ])->FetchAll();

        $result = [];

        $currentDate = (new DateTime())->format('d.m.Y');

        foreach ($items as $item) {
            if (!empty($item)) {
                $lastDate = $item['UF_DATE']->format('d.m.Y');

                if ($lastDate === $currentDate) {
                    $result[$item['UF_ID_PARTNER']] = true;
                } else {
                    $result[$item['UF_ID_PARTNER']] = false;
                }
            } else {
                $result[$item['UF_ID_PARTNER']] = false;
            }
        }

        return $result;
    }

    public function partnersCongratulateAction($partnerId) {
        global $USER;
        \Vaganov\Helper::includeHlTable('happy_birthday_to_partner');

        $data = HappyBirthdayToPartnerTable::getList([
            'select' => [
                '*',
                'UF_*'
            ],
            'filter' => [
                'UF_ID_PARTNER' => $partnerId
            ]
        ])->Fetch();

        $currentDate = (new DateTime())->format('d.m.Y');

        if (!empty($data)) {
            $lastDate = (new DateTime($data['UF_DATE']))->format('d.m.Y');

            if ((new DateTime($data['UF_DATE']))->modify('+1 year')->format('d.m.Y') === $currentDate) {
                HappyBirthdayToPartnerTable::update($data['ID'], ['UF_DATE' => $currentDate]);

                return true;
            } else if ($lastDate === $currentDate) {
                return true;
            } else {
                return false;
            }
        } else {
            HappyBirthdayToPartnerTable::add([
                'UF_ID_PARTNER' => $partnerId,
                'UF_DATE' => $currentDate,
                'UF_ID_ASSIGNED' => $USER->GetID(),
            ]);

            return true;
        }
    }

    function executeComponent()
    {
        $this->arResult['EMPLOYEES'] = $this->getEmployeesData();
        $this->arResult['PARTNERS'] = $this->getPartnersData();
        $this->arResult['NEXT_BIRTHDAYS'] = $this->getNextFiveBirthdays();

        $this->includeComponentTemplate();
    }
}