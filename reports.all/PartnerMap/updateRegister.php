<?php
cli_set_process_title("updateRegisterTitle");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(dirname(dirname(dirname(dirname(__DIR__))))));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


CModule::IncludeModule('pull');


if(isset($argv[1])){

    try {
        (new \Components\Vaganov\ReportsAll\PartnerMap\RegisterShareholderParse())->parse($argv[1]);

        CPullStack::AddShared(Array(
            'module_id' => 'downloadRegisterPay',
            'command' => 'download',
            'params' => [
                'success' => 'Реестр загружен',

            ],
        ));

    } catch (Exception $e) {
        CPullStack::AddShared(Array(
            'module_id' => 'downloadRegisterPay',
            'command' => 'download',
            'params' => [
                'error' => $e->getMessage(),
                'file' =>  $argv[1]
            ],
        ));
    }




}else{
    CPullStack::AddShared(Array(
        'module_id' => 'downloadRegisterPay',
        'command' => 'download',
        'params' => [
            'error' => 'Ошибка файла',
            'file' => $argv[1]
        ],
    ));
}



