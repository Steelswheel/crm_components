<?php
namespace Components\Vaganov;

class CPullComments
{
    public static function OnPullCommentsModule()
    {
        return [
            'MODULE_ID' => 'pull_comments',
            'USE' => ['PUBLIC_SECTION']
        ];
    }
}