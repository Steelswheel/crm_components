<?php

namespace Components\Vaganov\ReportsAll\PartnerMap;

set_time_limit(0);
use mysql_xdevapi\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Table\RegisterShareholderTable;

require $_SERVER["DOCUMENT_ROOT"].'/local/composer/vendor/autoload.php';

class RegisterShareholderParse
{

    private function message($message,$error = ''){
        \CPullStack::AddShared(Array(
            'module_id' => 'downloadRegisterPay',
            'command' => 'download',
            'params' => [
                'loading' => $message,
                'error' => $error,
            ],
        ));
    }

    public function parse($name){

        $this->message('СТАРТ');

        $reader = new Xlsx();
        $spreadsheet = $reader->load($name);


        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $this->message('Обработано: '.count($sheetData)." - 0");
        echo "Количество : ".count($sheetData)."\n";


        $loaded = 0;
        foreach ($sheetData as $key => $row){




            if((int)$row[3] > 0){

                echo (int)$row[3]." ";

                $loaded++;
                $this->message("Обработано: ".count($sheetData)." - $loaded");


                $rowParse = $this->modelExcel($row);

                $this->addUpdate($rowParse);


            }



        }
        echo "END\n";




    }

    private function addressCode($address){

        if(!$address){
            return '';
        }

        try {
            $adr = \Vaganov\Helper::dadada('address',$address);
            return $adr['region_iso_code'];
        } catch (Exception $e) {
            return '';
        }

    }

    private function addUpdate($data){

        $res = RegisterShareholderTable::getList([
            'filter' => ['UF_NUMBER' => $data['UF_NUMBER']]
        ])->fetch();
        if($res){
            $r = RegisterShareholderTable::update($res['ID'],$data);

            if(!$r->isSuccess()){
                throw new \Exception("UPDATE ".print_r($data,1).print_r($r->getErrorMessages(),1));
            }
        }else{

            $data['UF_ADDRESS_CODE'] = $this->addressCode($data['UF_ADDRESS']);

            $r = RegisterShareholderTable::add($data);

            if(!$r->isSuccess()){
                throw new \Exception(print_r($data,1). print_r($data,1));
            }
        }


    }

    /***
     *
     *
     *  [0] =>              ыбывший
     *  [1] =>
     *  [2] =>
     *  [3] => UF_NUMBER    00050
     *  [4] =>              0
     *  [5] =>
     *  [6] =>
     *  [7] =>              Москва
     *  [8] => UF_FIO       Корниенкова Виктория Валерьевна
     *  [9] => UF_PASSPORT  Паспорт гражданина РФ, серия: 92 21 № 946246, выдан МВД по Республике Татарстан 13.07.2021 г.
     *  [10] =>             #NULL!
     *  [11] => UF_INN      165069338800
     *  [12] => UF_PHONE    89874062623
     *  [13] => UF_EMAIL    ludmila.mihailovna2014@yandex.ru
     *  [14] => UF_ADDRESS  РОССИЯ, Республика Татарстан, г. Набережные Челны, ул. наб. им. Габдуллы Тукая, д. 45/1, кв. 122
     *  [15] =>             РОССИЯ, Республика Татарстан, г. Набережные Челны, ул. наб. им. Габдуллы Тукая, д. 45/1, кв. 122
     *  [16] =>             #NULL!
     *  [17] => UF_DATE_IN  26.10.2021
     *  [18] =>             #NULL!
     *  [19] =>             #NULL!
     *  [20] => UF_DATE_OUT 29.11.2022
     *  [21] =>             #NULL!
     *  [22] =>             #NULL!
     *  [23] =>             Заявление пайщика
     *  [24] =>             #NULL!
     *
     * @param $row array
     *
     */
    private function modelExcel(array $row){
        if(count($row) !== 25){


            throw new \Exception("Ошибка чтения excel файла count(row) = ".count($row)." Ожидается 25",423);
        }
        return [
            "UF_NUMBER" =>    $row[3],
            "UF_FIO" =>       $row[8],
            "UF_PASSPORT" =>  $row[9],
            "UF_INN" =>       $row[11],
            "UF_PHONE" =>     $row[12],
            "UF_EMAIL" =>     $row[13],
            "UF_ADDRESS" =>   $row[14],
            "UF_DATE_IN" =>   $row[17] === '#NULL!' ? '' : $row[17],
            "UF_DATE_OUT" =>  $row[20] === '#NULL!' ? '' : $row[20],
        ];
    }






}