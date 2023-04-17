<?php
namespace Components\Vaganov\SbpInfo;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;
use Vaganov\Helper;
use Bank\SbpApi;

Loader::IncludeModule('crm');

class ReportExcel
{
    private array $data;
    private string $start;
    private string $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;

        Helper::includeHlTable('sbp_creation');

        $operations = \SbpCreationTable::getList([
            'order' => ['ID' => 'asc'],
            'select' => [
                'ID',
                'UF_RQ_TM',
                'UF_ORDER_ID',
                'UF_ORDER_NUMBER',
                'UF_DESCRIPTION'
            ],
            'filter' => [
                'UF_ORDER_STATE' => 'PAID',
                '><UF_CREATE_DATE' => [$this->start = $start, $this->end]
            ]
        ])->fetchAll();

        if (!empty($operations)) {
            $sbp = new SbpApi();

            foreach ($operations as $operation) {
                $status = $sbp->status($operation['UF_ORDER_ID'], $operation['UF_ORDER_NUMBER']);

                $this->data[] = [
                    'id_qr' => $status['id_qr'],
                    'rq_tm' => (new \DateTime($operation['UF_RQ_TM']))->format('d.m.Y'),
                    'sbp_operation_id' => $status['sbp_operation_params']['sbp_operation_id'],
                    'sbp_masked_payer_id' => $status['sbp_operation_params']['sbp_masked_payer_id'],
                    'client_name' => $status['order_operation_params'][0]['client_name'],
                    'description' => $operation['UF_DESCRIPTION'],
                    'operation_sum' => round($status['order_operation_params'][0]['operation_sum'] / 100, 2),
                    'order_id' => $status['order_id']
                ];
            }
        }
    }

    public function getFile()
    {
        $arColumnDescriptFirst = [
            'id_qr' => ['col' => 'A'],
            'rq_tm' => ['col' => 'B'],
            'sbp_operation_id' => ['col' => 'C'],
            'sbp_masked_payer_id' => ['col' => 'D'],
            'client_name' => ['col' => 'E'],
            'description' => ['col' => 'F'],
            'operation_sum' => ['col' => 'G'],
            'order_id' => ['col' => 'H']
        ];

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/sbp.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spread = $reader->load($template);

        $curSheet = $spread->getSheetByName('SBP');

        $rowNum = 2;

        foreach ($this->data as $operation) {
            foreach ($arColumnDescriptFirst as $field => $descript) {
                if (!empty($operation[$field])) {
                    $val = $operation[$field];

                    $curSheet->setCellValue($descript['col'] . $rowNum, $val);
                }
            }

            $rowNum++;
        }

        $newfile = $_SERVER['DOCUMENT_ROOT'] . '/upload/sbp.xlsx';

        unlink($newfile);

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        $fileData = \CFile::MakeFileArray($newfile);
        $fileData['PATH'] = $newfile;

        return $fileData;
    }
}