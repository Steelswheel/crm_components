<?php


namespace Components\Vaganov\ReportsAll\PartnerMap;


use Dompdf\Exception;

trait PartnerMapApi
{


    public function PartnerMapSaveAction($data){
        global $USER;
        if($USER->GetID() !== '42'){
            throw new \ErrorException('НЕТ ПРАВ', 422);
        }

        global $USER;
        $path = $_SERVER["DOCUMENT_ROOT"]."/local/components/vaganov/partner.map/data/";
        $name = $USER->GetID().".json";
        file_put_contents($path.$name,$data);
        return [$data];

    }

    public function PartnerMapDownloadAction($file){

        $command = $_SERVER["DOCUMENT_ROOT"] . "/local/components/vaganov/reports.all/PartnerMap/updateRegister.php";

        $res = shell_exec("ps aux | grep updateRegisterTitle");
        $len = count(explode("\n", $res));
        if($len === 3){
            exec("php $command $file> /dev/null 2>&1 &");
            return ['start',$file];
        }

        throw new Exception('Процесс уже запущен',423);

    }

    function PartnerMapLoadAction(){
        return [
            "partners" => PartnerMap::partners(),
            "regionCode" => PartnerMap::regionCode(),
            "shareholders" => PartnerMap::registerShareholders(),
            "authorizes" => PartnerMap::authorizes(),
        ];
    }


    public function PartnerMapRegisterAction(){
        global $USER;
        if(!($USER->GetID() === '502' || $USER->GetID() === '104' || $USER->IsAdmin())){
            throw new \ErrorException('НЕТ ПРАВ', 423);
        }

        $registerShareholderExcel = new RegisterShareholderExcel();

        return $registerShareholderExcel->excel();


    }



}