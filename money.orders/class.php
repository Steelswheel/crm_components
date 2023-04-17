<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;

class MoneyOrderClass extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    function parserAction($file)
    {
        return (new \Components\Vaganov\MoneyOrders\MoneyOrders($file))->run();
    }

    function setTrancheAction($docId, $dealId, $date, $sum, $setTranche)
    {
        return \Components\Vaganov\MoneyOrders\MoneyOrders::setTranche($docId, $dealId, $date, $sum, $setTranche);
    }

    function onSkipDocumentAction($docId, $isSkip)
    {
        return \Components\Vaganov\MoneyOrders\MoneyOrders::onSkipDocument($docId, $isSkip);
    }
}