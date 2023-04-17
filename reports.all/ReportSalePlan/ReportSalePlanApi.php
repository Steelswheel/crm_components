<?php


namespace Components\Vaganov\ReportsAll\ReportSalePlan;

use Bitrix\Crm\DealTable;

trait ReportSalePlanApi
{
    public function setDealPlanStateAction($id, $state)
    {
        \Bitrix\Main\Loader::IncludeModule('crm');

        global $USER;

        /*if (!$USER->GetID()) {
            $USER->Authorize(1);
        }*/

        $oDeal = new \CCrmDeal(false);

        if ($state === 'true') {

            $arFields = [
                'UF_INCLUDING_DATE_TO_SALE_PLAN' => (new \DateTime())->format('d.m.Y'),
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN' => null
            ];

            $oDeal->Update($id, $arFields);
        } else {
            $arFields = [
                'UF_INCLUDING_DATE_TO_SALE_PLAN' => null,
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN' => (new \DateTime())->format('d.m.Y')
            ];

            $oDeal->Update($id, $arFields);
        }


        return (new \DateTime())->format('d.m.Y');
    }

    public function salePlanReportAction($month){
        global $USER;
        $salePlan = new SalePlan($month);

        $plan = $salePlan->getPlanSales();

        return [
            'rules' => [
                'admin' => $USER->IsAdmin() || in_array($USER->GetID(), ['418', '622', '47']),
                'zp' =>  $USER->IsAdmin() || $USER->GetID() == '104'
            ],
            'departs' => $salePlan->getReportUsers(),
            'reports' => [
                [
                    'name' => 'dealsInProcess',
                    'values' => $salePlan->getDealsInProcess()
                ],
                [
                    'name' => 'UF_DEALS_IN_PROCESS',
                    'one' => $plan['UF_DEALS_IN_PROCESS'] ?: []
                ],
                [
                    'name' => 'UF_ADDITIONAL_SALES',
                    'one' => $plan['UF_ADDITIONAL_SALES'] ?: []
                ],
                [
                    'name' => 'UF_PLAN',
                    'one' => $plan['UF_PLAN'] ?: []
                ],
                [
                    'name' => 'factDeals',
                    'values' => $salePlan->getFactDeals()
                ],
                [
                    'name' => 'percent',
                    'values' => []
                ],
                [
                    'name' => 'loanRepaymentDeals',
                    'values' => $salePlan->getLoanRepaymentDeals()
                ],
            ],
        ];
    }

    public function salePlanFixPercentAction($deals,$percent){

        \Bitrix\Main\Loader::IncludeModule('crm');

        $dealsIds = explode(',',$deals);

        $dealsAr = DealTable::getList([
            'select' => ['ID', 'UF_BONUS_PLAN_PERCENT'],
            'filter' => ['ID' => $dealsIds]
        ])->fetchAll();

        foreach ($dealsAr as $item){
            $a = DealTable::update($item['ID'],['UF_BONUS_PLAN_PERCENT' => $percent]);

            if(!$a->isSuccess()){
                return ['11'];
            }
        }

        return [$deals,$percent];
    }

    public function salePlanPayAction($dealId,$month){

        \Bitrix\Main\Loader::IncludeModule('crm');

        $month = "01.".$month;
        DealTable::update($dealId,[
            'UF_BONUS_PAIDED_DATE' => $month
        ]);

        return $month;
    }

    public function salePlanReportExcelAction($month,$users){

        $usersAr = explode(',',$users);
        return (new ReportExcel($month, $usersAr))->getFile();

    }


    public function salePlanSetPlanAction($month, $value, $userId, $inputType )
    {

        global $USER;

        if ($USER->IsAdmin()) {
            return (new SalePlan($month))->setPlanSales($value, $userId, $inputType);
        } else {
            return false;
        }

    }

    /*public function setDealPlanStateAction($id, $state)
    {
        \Bitrix\Main\Loader::IncludeModule('crm');

        global $USER;

        if (!$USER->GetID()) {
            $USER->Authorize(1);
        }

        $oDeal = new \CCrmDeal();

        if ($state === 'true') {
            $arFields = [
                'UF_INCLUDING_DATE_TO_SALE_PLAN' => (new DateTime())->format('d.m.Y'),
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN' => null
            ];

            $oDeal->Update($id, $arFields);
        } else {
            $arFields = [
                'UF_INCLUDING_DATE_TO_SALE_PLAN' => null,
                'UF_NOT_INCLUDING_DATE_TO_SALE_PLAN' => (new DateTime())->format('d.m.Y')
            ];

            $oDeal->Update($id, $arFields);
        }

        return (new DateTime())->format('d.m.Y');
    }*/

}