<?php
namespace Components\Vaganov\ReportsAll\SavingsReport;

trait SavingsReportApi
{
    public function reportSavingsAction($startDate, $endDate)
    {
        return (new SavingsReport())->run($startDate, $endDate);
    }
}