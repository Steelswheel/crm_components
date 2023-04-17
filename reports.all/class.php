<?php
ini_set('max_execution_time', '300000000');
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Components\Vaganov\ReportsAll\CbReport\CbReportApi;
use Components\Vaganov\ReportsAll\PartnerMap\PartnerMapApi;
use Components\Vaganov\ReportsAll\PayOnApplications\PayOnApplicationsApi;
use Components\Vaganov\ReportsAll\ReportSalePlan\ReportSalePlanApi;
use Components\Vaganov\ReportsAll\SaleDkp\SaleDkpMap;
use Components\Vaganov\ReportsAll\SaleReport\SaleReportApi;
use Components\Vaganov\ReportsAll\SavingsReport\SavingsReportApi;
use Components\Vaganov\ReportsAll\CustomReport\CustomReportApi;

class reportsAll extends CBitrixComponent implements Controllerable
{
    use PayOnApplicationsApi;
    use ReportSalePlanApi;
    use PartnerMapApi;
    use SaleReportApi;
    use SaleDkpMap;
    use SavingsReportApi;
    use CbReportApi;
    use CustomReportApi;

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
}