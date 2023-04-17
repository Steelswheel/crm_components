<?php

namespace Components\Vaganov\ReportsAll\SaleReport;

trait SaleReportApi
{

    public function saleReportAction($startDate, $endDate, $inputValue, $inputManagerId, $isInput = false)
    {
        return (new SaleReport())->run($startDate, $endDate, $inputValue, $inputManagerId, $isInput);
    }

}