<?php


namespace Components\Vaganov\ReportsAll\PayOnApplications;


trait PayOnApplicationsApi
{


    public function payOnApplicationsTableAction($date = "2021-03-01"){

        $dateMonth = $date;
        $dateStart = $dateMonth." 00:00:00";
        $dateEnd = date("Y-m-t", strtotime($dateMonth))." 23:59:59";

        $payOnApplications = new PayOnApplications();

        $table = [
            'title' => 'Начисление бонусов',
            'excel' => [
                'width' => [20, 5, 20, 8, 8, 8, 8, 8, 8, 8],
                'height' => [30, 30],
            ],
            'table' => [
                'head' => $payOnApplications->reportFinHead(),
                'body' => $payOnApplications->reportFin($dateStart,$dateEnd),
            ],
        ];
        return $table;
    }

}