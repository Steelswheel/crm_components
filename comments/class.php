<?php
namespace Components\Vaganov;

use Bitrix\Crm\DealTable;
use Bitrix\Crm\Observer\Entity\ObserverTable;
use \Vaganov\Helper;
use Bitrix\Main\Loader;
use Vaganov\Notification;
use Bitrix\Main\Entity\Query;
use Bitrix\Crm\Timeline\CommentEntry;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Engine\Contract\Controllerable;

Loader::IncludeModule('crm');
Loader::includeModule('disk');

class Comments extends \CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [];
    }

    public function deleteCommentAction($id)
    {
        TimelineTable::delete($id);
    }

    public function setCommentVisibilityToPartnerAction($id, $value)
    {
        Helper::includeHlTable('ext_crm_timeline');

        $comment_id = \ExtCrmTimelineTable::getList([
            'select' => ['ID'],
            'filter' => ['UF_TIMELINE_ID' => $id]
        ])->fetch();

        if (!empty($comment_id)) {
            \ExtCrmTimelineTable::update($comment_id['ID'], ['UF_SHOW_TO_PARTNER' => $value === 'true']);
        } else {
            \ExtCrmTimelineTable::add([
                'UF_TIMELINE_ID' => $id,
                'UF_FROM_PARTNER' => false,
                'UF_SHOW_TO_PARTNER' => $value === 'true'
            ]);
        }

        return $value;
    }

    public function addCommentAction($data, $dealId)
    {
        $deal = DealTable::getList([
            'select' => [
                'ID',
                'STAGE_ID',
                'ASSIGNED_BY_ID'
            ],
            'filter' => [
                'ID' => $dealId
            ]
        ])->fetch();

        if ($deal) {
            global $USER;

            $data = json_decode($data, 1);

            $commentData = [
                'AUTHOR_ID' => $USER->GetID(), //Идентификатор автора комментариев
                'TEXT' => $data['text'], //Текст комментария
                'BINDINGS' => [
                    [
                        'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal, //ID типа сущности
                        'ENTITY_ID' => (int)$dealId //ID сущности (cделки, лида и т.д.)
                    ]
                ]
            ];

            if (!empty($data['files'])) {
                $commentData['SETTINGS'] = [
                    'HAS_FILES' => 'Y'
                ];

                $commentData['FILES'] = $data['files'];
            } else {
                $commentData['SETTINGS'] = [
                    'HAS_FILES' => 'N'
                ];
            }

            $entryID = CommentEntry::create($commentData);

            if (!empty($entryID)) {
                $comment = $dealId . ' - ' . (new \DateTime())->format('d.m.Y') . ' - ' . $data['text'] . ' (' . $USER->GetLastName() . ") \n\r";

                file_put_contents(__DIR__ . '/log/log.txt', $comment, FILE_APPEND | LOCK_EX);

                Helper::includeHlTable('ext_crm_timeline');

                \ExtCrmTimelineTable::add([
                    'UF_TIMELINE_ID' => $entryID,
                    'UF_FROM_PARTNER' => $data['from_partner'],
                    'UF_SHOW_TO_PARTNER' => $data['show_to_partner']
                ]);

                Loader::IncludeModule('pull');

                RegisterModuleDependences(
                    'pull',
                    'OnPullCommentsModule',
                    'add_comment',
                    'CPullComments',
                    'OnPullCommentsModule'
                );

                \CPullStack::AddByUser($USER->GetID(), [
                    'module_id' => 'pull_comments',
                    'command' => 'add_comment',
                    'params' => []
                ]);

                $observers = ObserverTable::getList([
                    'select' => [
                        'USER_ID'
                    ],
                    'filter' => [
                        'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
                        'ENTITY_ID' => $dealId
                    ]
                ])->fetchAll();

                Helper::includeHlTable('stage_id_settings');

                $settingsRes = \StageIdSettingsTable::getList([
                    'select' => [
                        'UF_ALLOWED_USERS',
                        'UF_ALLOWED_USERS_NO_ALARM'
                    ],
                    'filter' => ['UF_STAGE_ID' => $deal['STAGE_ID']]
                ])->fetch();

                $no_alarm = array_map(function ($item) {
                    return (int)$item;
                }, $settingsRes['UF_ALLOWED_USERS_NO_ALARM']);

                foreach ($settingsRes['UF_ALLOWED_USERS'] as $user) {
                    if (!in_array((int)$user, $no_alarm)) {
                        Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $user);
                    }
                }

                foreach ($observers as $observer) {
                    if (!in_array((int)$observer['USER_ID'], $no_alarm)) {
                        Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $observer['USER_ID']);
                    }
                }

                $observers_ids = array_map(function ($item) {
                    return $item['USER_ID'];
                }, $observers);

                if ($USER->GetID() !== $deal['ASSIGNED_BY_ID'] && !in_array((int)$USER->GetID(), $observers_ids)) {
                    Notification::send("Добавлен комментарий по сделке [URL=https://crm.kooperatiff.ru/b/edz/?deal_id={$dealId}&show]{$dealId}[/URL]", $deal['ASSIGNED_BY_ID']);
                }

                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function getCommentsAction($dealId)
    {
        $result = [];

        $deal = DealTable::getList([
            'select' => [
                'ID',
                'COMMENTS'
            ],
            'filter' => [
                'ID' => $dealId
            ]
        ])->fetch();

        if ($deal) {
            $entity = Helper::includeHlTable('ext_crm_timeline');

            $query = new Query(TimelineTable::getEntity());

            $query
                ->registerRuntimeField('EXT', [
                    'data_type' => $entity,
                    'reference' => [
                        '=this.ID' => 'ref.UF_TIMELINE_ID',
                    ],
                ])
                ->registerRuntimeField('BIND', [
                    'data_type' => 'Bitrix\Crm\Timeline\Entity\TimelineBindingTable',
                    'reference' => [
                        '=this.ID' => 'ref.OWNER_ID',
                    ],
                ])
                ->setSelect([
                    'ID',
                    'COMMENT',
                    'AUTHOR_ID',
                    'CREATED',
                    'FROM_PARTNER' => 'EXT.UF_FROM_PARTNER',
                    'SHOW_TO_PARTNER' => 'EXT.UF_SHOW_TO_PARTNER'
                ])
                ->setOrder([
                    'ID' => 'DESC'
                ])
                ->setFilter([
                    'TYPE_ID' => 7,
                    'BIND.ENTITY_ID' => $dealId,
                    'BIND.ENTITY_TYPE_ID' => 2,
                    '!=COMMENT' => null
                ])
                ->exec();

            $users = Helper::getUsersFullInfo(53, false);

            foreach ($query->fetchAll() as $item) {
                $author = in_array($item['AUTHOR_ID'], $users) ? $users[$item['AUTHOR_ID']] : \CUser::GetByID($item['AUTHOR_ID'])->Fetch();

                $fileFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields(\Bitrix\Crm\Timeline\CommentController::UF_FIELD_NAME, $item['ID']);
                $name = trim(trim($author['NAME']) . ' ' . trim($author['LAST_NAME']));
                $photo = $author['PERSONAL_PHOTO'] ? \CFile::GetPath($author['PERSONAL_PHOTO']) : '';

                $result[] = [
                    'ID' => $item['ID'],
                    'AUTHOR_ID' => $item['AUTHOR_ID'],
                    'AUTHOR_NAME' => $name,
                    'AUTHOR_PHOTO' => $photo,
                    'TEXT' => $item['COMMENT'],
                    'DATE' => (new \DateTime($item['CREATED']))->format('d.m.Y H:i'),
                    'FILES' => !empty($fileFields['UF_CRM_COMMENT_FILES']['VALUE']) ? $this->getFilesInfo($fileFields['UF_CRM_COMMENT_FILES']['VALUE']) : [],
                    'FROM_PARTNER' => $item['FROM_PARTNER'] === '1',
                    'SHOW_TO_PARTNER' => $item['SHOW_TO_PARTNER'] === '1'
                ];
            }
        }

        return $result;
    }

    private function getFilesInfo($file_ids)
    {
        $query = new Query(ObjectTable::getEntity());

        $query
            ->registerRuntimeField('ATTACHED_OBJECT', [
                'data_type' => 'Bitrix\Disk\Internals\AttachedObjectTable',
                'reference' => [
                    '=this.ID' => 'ref.OBJECT_ID',
                ],
            ])
            ->setSelect([
                'ATTACHED_ID' => 'ATTACHED_OBJECT.ID',
                'NAME'
            ])
            ->setFilter([
                'ATTACHED_ID' => $file_ids
            ])
            ->exec();

        return $query->fetchAll();
    }
}