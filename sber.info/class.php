<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;


class SberInfo extends CBitrixComponent implements Controllerable
{

    public function configureActions()
    {

    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }


}