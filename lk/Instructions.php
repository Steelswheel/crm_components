<?php
namespace Components\Vaganov\Lk;

use Bitrix\Main\Loader;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class Instructions
{
    public function changeSort($id, $sort)
    {
        Helper::includeHlTable('lk_instructions');

        \LkInstructionsTable::update($id, ['UF_SORT' => $sort]);

        return true;
    }

    public function getInstructions()
    {
        Helper::includeHlTable('lk_instructions');

        $res = \LkInstructionsTable::getList([
            'order' => ['UF_SORT' => 'ASC'],
            'select' => ['*', 'UF_*']
        ])->fetchAll();

        $users = Helper::getUsers(53);

        $result = [
            'ITEMS' => [],
            'USERS' => []
        ];

        foreach ($res as $item) {
            $item['UF_DATE_CREATE'] = (new \DateTime($item['UF_DATE_CREATE']))->format('d.m.Y');

            $user = explode(' ', $users[$item['UF_ASSIGNED_BY_ID']]);
            $item['AUTHOR'] = $user[0] . ' ' . trim(mb_substr($user[1], 0, 1)) . '. ' . trim(mb_substr($user[2], 0, 1)) . '.';

            $result['ITEMS'][] = $item;
        }

        foreach ($users as $key => $value) {
            $user = explode(' ', $value);
            $name = $user[0] . ' ' . trim(mb_substr($user[1], 0, 1)) . ($user[2] ? '. ' . trim(mb_substr($user[2], 0, 1)) . '.' : '.');

            $result['USERS'][] = [
                'label' => $name,
                'value' => $key
            ];
        }

        return $result;
    }

    public function setAuthor($id, $user_id)
    {
        Helper::includeHlTable('lk_instructions');

        \LkInstructionsTable::update($id, ['UF_ASSIGNED_BY_ID' => $user_id]);

        return true;
    }

    public function changeInstructionState($id, $state)
    {
        Helper::includeHlTable('lk_instructions');

        if ($state === 'Y') {
            $state = 'N';
        } else {
            $state = 'Y';
        }

        \LkInstructionsTable::update($id, ['UF_IS_ACTIVE' => $state]);

        return $state;
    }

    public function addInstruction($text, $title)
    {
        global $USER;
        Helper::includeHlTable('lk_instructions');

        $last_item = \LkInstructionsTable::getList([
            'order' => ['UF_SORT' => 'DESC'],
            'select' => ['UF_SORT']
        ])->fetch();

        if ($last_item && $last_item['UF_SORT']) {
            $sort = (int)$last_item['UF_SORT'] + 1;
        } else {
            $sort = 1;
        }

        \LkInstructionsTable::add([
            'UF_SORT' => $sort,
            'UF_DATE_CREATE' => (new \DateTime())->format('d.m.Y H:i:s'),
            'UF_IS_ACTIVE' => 'N',
            'UF_TEXT' => $text,
            'UF_TITLE' => $title,
            'UF_ASSIGNED_BY_ID' => $USER->GetID()
        ]);

        return true;
    }

    public function changeInstruction($id, $text, $title)
    {
        Helper::includeHlTable('lk_instructions');

        \LkInstructionsTable::update($id, [
            'UF_TEXT' => $text,
            'UF_TITLE' => $title
        ]);

        return true;
    }

    public function deleteInstruction($id)
    {
        Helper::includeHlTable('lk_instructions');

        \LkInstructionsTable::delete($id);

        return true;
    }

    public function addImage()
    {
        return '';
    }
}