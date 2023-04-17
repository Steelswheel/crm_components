<?php
namespace Components\Vaganov\DocumentGenerate;

use Bitrix\Main\SystemException;
use Table\DocumentGenerateTable;

class DocumentGenerate
{
    public static $RULE_EDIT_DOC = 'edit';
    public static $RULE_EDIT_FIELDS_DOC = 'edit_fields';


    public static function rule($ruleName, $isError = true){
        global $USER;
        $rule = false;
        switch ($ruleName){
            case 'edit': $rule = in_array($USER->GetID(), ['502','698','104','42','618','640']); break;
            case 'edit_fields': $rule = in_array($USER->GetID(), ['618','640']); break;
        }

        if(!$rule && $isError){
            throw new SystemException("Нет прав" , 423);
        }

        return $rule;

    }
    public static function allRuleUser(){
        $rules = ['edit','edit_fields'];
        $rulesUser = [];
        foreach ($rules as $item){
            if(self::rule($item,false)){
                $rulesUser[] = $item;
            }

        }
        return $rulesUser;
    }


}