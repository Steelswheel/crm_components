<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;

class WorkSchedule extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [];
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function setUserDataAction($name, $phone, $email, $site = '')
    {
        global $USER;

        \Vaganov\Helper::includeHlTable('presentation_data');

        $data = \PresentationDataTable::getList([
            'select' => ['*'],
            'filter' => ['UF_USER_ID' => $USER->GetID()]
        ])->Fetch();

        \PresentationDataTable::update($data['ID'], [
            'UF_NAME' => $name,
            'UF_PHONE' => $phone,
            'UF_EMAIL' => $email,
            'UF_SITE' => $site ? $site : $data['UF_SITE']
        ]);

        return [
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'site' => $site
        ];
    }

    public function getUserDataAction()
    {
        global $USER;

        \Vaganov\Helper::includeHlTable('presentation_data');

        $data = \PresentationDataTable::getList([
            'select' => ['*'],
            'filter' => ['UF_USER_ID' => $USER->GetID()]
        ])->Fetch();

        if (empty($data)) {
            $id = $USER->GetID();
            $name = $USER->GetFirstName() . ' ' . $USER->GetLastName();
            $email = $USER->GetEmail();
            $phone = (\Vaganov\Helper::getUsersFullInfo(53))[$USER->GetID()]['WORK_PHONE'];
            $site = 'example.com';

            \PresentationDataTable::add([
                'UF_USER_ID' => $id,
                'UF_NAME' => $name,
                'UF_PHONE' => $phone,
                'UF_EMAIL' => $email,
                'UF_SITE' => $site
            ]);

            return [
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'site' => $site
            ];
        } else {
            return [
                'email' => $data['UF_EMAIL'],
                'name' => $data['UF_NAME'],
                'phone' => $data['UF_PHONE'],
                'site' => $data['UF_SITE']
            ];
        }
    }

    public function getWorkScheduleAction()
    {
        global $USER;

        \Vaganov\Helper::includeHlTable('work_schedule');

        $data = \WorkScheduleTable::getList(
            [ 'select' => ['*'],
                'filter' => ['ID' => 1]
            ])->Fetch();

        $data['isAdmin'] = $USER->IsAdmin() || in_array($USER->GetID(), ['418', '698']);

        return $data;
    }

    public function updateWorkScheduleAction($text)
    {
        global $USER;

        if ($USER->IsAdmin() || in_array($USER->GetID(), ['418', '698'])) {
            \Vaganov\Helper::includeHlTable('work_schedule');

            \WorkScheduleTable::update(1, [
                'UF_TEXT' => $text
            ]);
        }

        return true;
    }

    public function loanProgramAction()
    {
        global $USER;

        $data['isAdmin'] = $USER->IsAdmin();

        \Vaganov\Helper::includeHlTable('work_schedule');

        $res = \WorkScheduleTable::getList([
            'select' => ['*'],
            'filter' => ['UF_TITLE_EN' => 'loanProgramNew']
        ])->Fetch();

        $data['list'] = json_decode($res['UF_TEXT'], 1);

        return $data;
    }

    public function updateLoanProgramAction($loanProgram)
    {
        global $USER;

        if ($USER->IsAdmin()) {
            \Vaganov\Helper::includeHlTable('work_schedule');

            $res = \WorkScheduleTable::getList([
                'select' => ['*'],
                'filter' => ['UF_TITLE_EN' => 'loanProgramNew']
            ])->Fetch();

            \WorkScheduleTable::update($res['ID'], [
                'UF_TEXT' => $loanProgram
            ]);
        }

        return true;
    }

    public function savingsProgramAction()
    {
        global $USER;

        $data['isAdmin'] = $USER->IsAdmin();

        \Vaganov\Helper::includeHlTable('work_schedule');

        $res = \WorkScheduleTable::getList([
            'select' => ['*'],
            'filter' => ['UF_TITLE_EN' => 'SavingsProgram']
        ])->Fetch();

        $data['list'] = json_decode($res['UF_TEXT'], 1);

        return $data;
    }

    public function updateSavingsProgramAction($savingsProgram)
    {
        global $USER;

        if ($USER->IsAdmin()) {
            \Vaganov\Helper::includeHlTable('work_schedule');

            \WorkScheduleTable::update(3, [
                'UF_TEXT' => $savingsProgram
            ]);
        }

        return true;
    }
}