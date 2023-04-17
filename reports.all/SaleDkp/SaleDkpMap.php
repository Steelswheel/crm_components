<?php


namespace Components\Vaganov\ReportsAll\SaleDkp;


use Components\Vaganov\ReportsAll\SaleDkp\SaleDkp;
use Dompdf\Exception;

trait SaleDkpMap
{

    public function saleDkpDkpAction($startDate = false, $endDate = false, $departments = false)
    {
        return (new SaleDkp())->run($startDate, $endDate, $departments);
    }

    public function saleDkpDateAction($dealId)
    {
        (new SaleDkp())->setDate($dealId);
    }

}