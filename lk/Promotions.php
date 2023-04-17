<?php
namespace Components\Vaganov\Lk;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class Promotions
{
    public function getItem($id)
    {
        Helper::includeHlTable('sber_promotion');

        $item = \SberPromotionTable::getList([
            'select' => ['*', 'UF_*'],
            'filter' => ['ID' => $id]
        ])->fetch();

        $users = Helper::getUsers(53);
        $user = explode(' ', $users[$item['UF_ASSIGNED_BY_ID']]);

        $item['UF_DATE_CREATE'] = (new \DateTime($item['UF_DATE_CREATE']))->format('d.m.Y');
        $item['UF_PUBLICATION_DATE'] = $item['UF_PUBLICATION_DATE'] ? (new \DateTime($item['UF_PUBLICATION_DATE']))->format('d.m.Y') : '';
        $item['UF_ASSIGNED_BY_ID'] = $user[0] . ' ' . trim(mb_substr($user[1], 0, 1)) . '. ' . trim(mb_substr($user[2], 0, 1)) . '.';

        if (!empty($item['UF_IMAGE'])) {
            $fileArr = \CFile::GetFileArray($item['UF_IMAGE'][0]);

            $item['UF_IMAGE'] = [
                'url' => $fileArr['SRC'],
                'name' => $fileArr['FILE_NAME']
            ];
        }

        return $item;
    }

    public function deletePromotionsItem($id)
    {
        Helper::includeHlTable('sber_promotion');

        $item = \SberPromotionTable::getList([
            'select' => ['*', 'UF_*'],
            'filter' => ['ID' => $id]
        ])->fetch();

        if (!empty($item['UF_IMAGE'])) {
            \CFile::Delete($item['UF_IMAGE'][0]);
        }

        \SberPromotionTable::delete((int)$id);

        return true;
    }

    public function changePublicationDate($id, $date)
    {
        Helper::includeHlTable('sber_promotion');

        \SberPromotionTable::update((int)$id, ['UF_PUBLICATION_DATE' => $date]);

        return true;
    }

    public function deleteImage($id, $fileId)
    {
        $fileArr = \CFile::GetFileArray($fileId);

        \CFile::delete($fileId);

        unset($fileArr['SRC']);

        Helper::includeHlTable('sber_promotion');

        \SberPromotionTable::update((int)$id, ['UF_IMAGE' => '']);

        return true;
    }

    public function updatePromotionsItem($id, $text, $title)
    {
        Helper::includeHlTable('sber_promotion');

        \SberPromotionTable::update((int)$id, ['UF_TEXT' => $text, 'UF_TITLE' => $title]);

        return true;
    }

    public function addPromotionsItem($text, $title)
    {
        global $USER;
        Helper::includeHlTable('sber_promotion');

        $res = \SberPromotionTable::add([
            'UF_DATE_CREATE' => (new \DateTime())->format('d.m.Y H:i:s'),
            'UF_IS_ACTIVE' => 'N',
            'UF_TEXT' => $text,
            'UF_TITLE' => $title,
            'UF_ASSIGNED_BY_ID' => $USER->GetID()
        ]);

        $item = \SberPromotionTable::getList([
            'select' => ['*', 'UF_*'],
            'filter' => ['ID' => $res->getId()]
        ])->fetch();

        $users = Helper::getUsers(53);
        $user = explode(' ', $users[$item['UF_ASSIGNED_BY_ID']]);

        $item['UF_DATE_CREATE'] = (new \DateTime($item['UF_DATE_CREATE']))->format('d.m.Y');
        $item['UF_PUBLICATION_DATE'] = $item['UF_PUBLICATION_DATE'] ? (new \DateTime($item['UF_PUBLICATION_DATE']))->format('d.m.Y') : '';
        $item['UF_ASSIGNED_BY_ID'] = $user[0] . ' ' . trim(mb_substr($user[1], 0, 1)) . '. ' . ($user[2] ? trim(mb_substr($user[2], 0, 1)) . '.' : '');

        if (!empty($item['UF_IMAGE'])) {
            $fileArr = \CFile::GetFileArray($item['UF_IMAGE'][0]);

            $item['UF_IMAGE'] = [
                'url' => $fileArr['SRC'],
                'name' => $fileArr['FILE_NAME']
            ];
        }

        return $item;
    }

    public function addImage($id)
    {
        $fileData = Helper::uploadAnything('sber_promotion', true);

        if ($fileData) {
            Helper::includeHlTable('sber_promotion');

            \SberPromotionTable::update((int)$id, ['UF_IMAGE' => [$fileData['id']]]);
        }

        return $fileData;
    }

    public function changePromotionsItemState($id, $state)
    {
        Helper::includeHlTable('sber_promotion');

        if ($state === 'Y') {
            $state = 'N';

            $data = [
                'UF_IS_ACTIVE' => $state
            ];
        } else {
            $state = 'Y';

            $data = [
                'UF_IS_ACTIVE' => $state,
                'UF_PUBLICATION_DATE' => (new \DateTime())->format('d.m.Y H:i:s')
            ];
        }

        \SberPromotionTable::update((int)$id, $data);

        return $state;
    }

    public function getItems($currentPage = 1, $pageSize = false)
    {
        $gridId = 'sber-promotions';

        if ($pageSize) {
            \CUserOptions::SetOption('myGrid', $gridId, [
                'pageSize' => $pageSize
            ]);
        }

        $options = \CUserOptions::GetOption('myGrid', $gridId);
        $pageSize = isset($options['pageSize']) ? (int)$options['pageSize'] : 10;

        $nav = new \Bitrix\Main\UI\PageNavigation($gridId);
        $nav->setPageSize($pageSize);
        $currentPage = (int)$currentPage > 0 ? $currentPage : 1 ;
        $nav->setCurrentPage($currentPage);

        $limit = $nav->getLimit();
        $offset = $nav->getOffset();

        $res = $this->getPromotions($offset, $limit);

        return [
            'columns' => [
                ['attribute' => 'UF_DATE_CREATE', 'label' => 'Дата создания'],
                ['attribute' => 'UF_PUBLICATION_DATE','label' => 'Дата публикации'],
                ['attribute' => 'UF_ASSIGNED_BY_ID', 'label' => 'Автор'],
                ['attribute' => 'UF_IS_ACTIVE', 'label' => 'Активна'],
                ['attribute' => 'UF_TITLE','label' => 'Заголовок'],
                ['attribute' => 'UF_TEXT','label' => 'Текст'],
                ['attribute' => 'UF_IMAGE', 'label' => 'Картинка'],
                ['attribute' => 'default', 'label' => '']
            ],
            'rows' => $res['ROWS'],
            'pageSize' => (int)$pageSize,
            'currentPage' => (int)$currentPage,
            'total' => (int)$res['ROWS_COUNT']
        ];
    }

    public function getPromotions($offset, $limit)
    {
        Helper::includeHlTable('sber_promotion');

        $res = \SberPromotionTable::getList([
            'order' => ['UF_DATE_CREATE' => 'DESC'],
            'select' => ['*', 'UF_*'],
            'offset' => $offset,
            'limit' => $limit
        ])->fetchAll();

        $count = \SberPromotionTable::getList([
            'select' => ['CNT'],
            'runtime' => [
                new ExpressionField('CNT', 'COUNT(*)')
            ]
        ])->fetch();

        $result = [
            'ROWS' => [],
            'ROWS_COUNT' => (int)$count['CNT']
        ];

        $users = Helper::getUsers(53);

        foreach ($res as $item) {
            $user = explode(' ', $users[$item['UF_ASSIGNED_BY_ID']]);

            $item['UF_DATE_CREATE'] = (new \DateTime($item['UF_DATE_CREATE']))->format('d.m.Y');
            $item['UF_PUBLICATION_DATE'] = $item['UF_PUBLICATION_DATE'] ? (new \DateTime($item['UF_PUBLICATION_DATE']))->format('d.m.Y') : '';
            $item['UF_ASSIGNED_BY_ID'] = $user[0] . ' ' . trim(mb_substr($user[1], 0, 1)) . '. ' . ($user[2] ? trim(mb_substr($user[2], 0, 1)) . '.' : '');

            if (!empty($item['UF_IMAGE'])) {
                $fileArr = \CFile::GetFileArray($item['UF_IMAGE'][0]);

                $item['UF_IMAGE'] = [
                    'id' => $fileArr['ID'],
                    'url' => $fileArr['SRC'],
                    'name' => $fileArr['FILE_NAME']
                ];
            }

            $result['ROWS'][] = $item;
        }

        return $result;
    }
}