<?php
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;

Loader::IncludeModule('crm');

class ReportExcel
{
    private array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getFile()
    {
        $arColumnDescriptFirst = [
            'orderCreateDate' => ['col' => 'A', 'type' => 'str'],
            'operationDateTime' => ['col' => 'B', 'type' => 'str'],
            'deal_name' => ['col' => 'C', 'type' => 'str'],
            'operationSum' => ['col' => 'D', 'type' => 'int']
        ];

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/qr.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spread = $reader->load($template);

        $curSheet = $spread->getSheetByName('QR');

        $rowNum = 2;

        foreach ($this->data as $deal) {
            foreach ($arColumnDescriptFirst as $field => $descript) {
                if (!empty($deal[$field])) {
                    $val = $deal[$field];

                    $curSheet->setCellValue($descript['col'] . $rowNum, $val);
                }
            }

            $rowNum++;
        }

        $newfile = $_SERVER['DOCUMENT_ROOT'] . '/upload/qr/QR.xlsx';

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        return '/upload/qr/QR.xlsx';
    }
}