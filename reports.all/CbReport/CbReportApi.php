<?php
namespace Components\Vaganov\ReportsAll\CbReport;

trait CbReportApi {
    public function getDataAction($startDate, $endDate, $pageNumber, $limit)
    {
        return (new CbReport())->getData($startDate, $endDate, $pageNumber, $limit);
    }

    public function getExcelAction($ids)
    {
        return (new ReportExcel(json_decode($ids, 1)))->getFile();
    }

    public function checkPrepareAction()
    {
        return (new CbReport())->checkPrepare();
    }

    public function checkCreatedAction()
    {
        return (new CbReport())->checkCreated();
    }

    public function stopLoadingAction()
    {
        return (new CbReport())->stopLoading();
    }

    public function prepareArchiveAction($ids)
    {
        return (new CbReport())->prepareArchive($ids);
    }
}