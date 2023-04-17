<?php
namespace Components\Vaganov\ReportsAll\CustomReport;

trait CustomReportApi {
    public function getCustomExcelAction()
    {
        return (new Excel())->getFile();
    }
}