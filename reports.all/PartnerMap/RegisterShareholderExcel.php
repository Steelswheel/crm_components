<?php


namespace Components\Vaganov\ReportsAll\PartnerMap;


use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RegisterShareholderExcel
{

    private $arColumn = [
        'number_pp' => ['col' => 'A', 'type' => 'str'],
        'UF_NUMBER' => ['col' => 'B', 'type' => 'str'],
        'UF_FIO' => ['col' => 'C', 'type' => 'str'],
        'UF_PASSPORT' => ['col' => 'D', 'type' => 'str'],
        'UF_ADDRESS' => ['col' => 'E', 'type' => 'str'],
        'UF_DATE_IN' => ['col' => 'F', 'type' => 'date'],
        'UF_DATE_OUT' => ['col' => 'G', 'type' => 'date'],
        'plot' => ['col' => 'I', 'type' => 'str'],
        'regNumber' => ['col' => 'J', 'type' => 'str'],
        'ypFio' => ['col' => 'K', 'type' => 'str'],
        'passport' => ['col' => 'L', 'type' => 'str'],
    ];




    public function excel(){
        $RegisterShareholder = \Table\RegisterShareholderTable::getList([
            'filter' => [
                '!UF_DATE_IN' => '',
                'UF_DATE_OUT' => '',
            ]
        ])->fetchAll();

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/RegisterShareholderExcel.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spread = $reader->load($template);
        $curSheet = $spread->getSheetByName('Лист1');

        $authorizes = PartnerMap::authorizes();

        $rowNum = 4;
        foreach ($RegisterShareholder as $key => $data){

            $regionCode = $data['UF_ADDRESS_CODE'];
            $authorizesItemRes = array_filter($authorizes,function($i)use ($regionCode){
                return in_array($regionCode,$i['regions']);
            });
            $authorizesItem = array_values($authorizesItemRes)[0];

            $data['plot'] = $authorizesItem['plot'];

            $data['regNumber'] = $authorizesItem['number'];
            $data['ypFio'] = $authorizesItem['name'];
            $data['passport'] = $authorizesItem['passport'];

            $data['number_pp'] = $key + 1;


            $this->setRowData($curSheet, $data, $rowNum, $this->arColumn);
            $rowNum++;
        }


        $name = '/upload/user_u_f/Реестр пайщиков.xlsx';
        $newfile = $_SERVER['DOCUMENT_ROOT'] . $name;

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        return $name;

    }


    private function setRowData($curSheet,$dataRow,$rowNum,$descript){
        foreach ($dataRow as $field => $value){
            if(isset($descript[$field])){
                $this->setCellData($curSheet,$value,$rowNum,$descript[$field]);
            }
        }
    }

    private function setCellData($curSheet, $dataCel, $rowNum, $descript){

        if (!empty($dataCel)) {
            if ($descript['type'] === 'date') {
                $val = new \DateTime($dataCel);

                $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($val);
                $curSheet->getCell($descript['col'] . $rowNum)->setValue($excelDateValue);
                $curSheet->getStyle($descript['col'] . $rowNum)
                    ->getNumberFormat()
                    ->setFormatCode(
                        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY
                    );
            } else if ($descript['type'] === 'int' || $descript['type'] === 'summ') {
                $value = str_replace(' ', '', $dataCel);

                $curSheet->getCell($descript['col'] . $rowNum)->setValueExplicit((float)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $curSheet->getStyle($descript['col'] . $rowNum)
                    ->getNumberFormat()
                    ->setFormatCode(
                        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00
                    );
            } else {
                $curSheet->setCellValue($descript['col'] . $rowNum, $dataCel);
            }
        }

    }
}