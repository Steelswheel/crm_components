<?php
namespace Components\Vaganov\ReportsAll\CbReport;

include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;

Loader::IncludeModule('crm');

class ReportExcel
{
    private array $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    private $arColumnDescriptFirst = [
        'ASSIGNED' => ['col' => 'A', 'type' => 'str'],
        'NUMBER' => ['col' => 'B', 'type' => 'int'],
        'FIO_CONTACT' => ['col' => 'C', 'type' => 'str'],
        'UF_BORROWER_PASSPORT_SER' => ['col' => 'D', 'type' => 'str'],
        'UF_BORROWER_PASSPORT_NUMBER' => ['col' => 'E', 'type' => 'str'],

        'REGISTER_PLACE' => ['col' => 'F', 'type' => 'str'],
        'FACT_PLACE' => ['col' => 'G', 'type' => 'str'],

        'UF_ENTER_KPK_DATE' => ['col' => 'H', 'type' => 'date'],
        'UF_EXIT_KPK_DATE' => ['col' => 'I', 'type' => 'date'],

        'PAYMENT_PFR_DATA' => ['col' => 'J', 'type' => 'date'], //  Поступление  денежных средств (МСК) от УФК (ОПФР)
        'PAYMENT_PFR_SUM' => ['col' => 'K', 'type' => 'int'],
        'UF_CASH_INFLOW_MSK_DEPART' => ['col' => 'L', 'type' => 'str'],
        'UF_CASH_INFLOW_MSK_INN' => ['col' => 'M', 'type' => 'str'],

        // Внесение  Заемщиком паевого взноса
        'UF_BORROWER_PAY_PAYMENTS_1C_DATE' => ['col' => 'N', 'type' => 'date'],
        'UF_BORROWER_PAY_PAYMENTS_1C_SUM' => ['col' => 'O', 'type' => 'int'],

        // Договор займа, заключенный с Заемщиком -
        'UF_DATA_DZ' => ['col' => 'Q', 'type' => 'date'], //  Дата
        'OPPORTUNITY' => ['col' => 'R', 'type' => 'int'], //  Сумма, руб.
        'UF_CONTRACTUAL_INTEREST_RATE' => ['col' => 'S', 'type' => 'str'], //  Годовая % ставка по договору займа
        'UF_EDZ_CONTRACT_PERIOD' => ['col' => 'T', 'type' => 'str'], //  Срок, на который заключен договор займа, мес
        'UF_LOAN_SECURITY' => ['col' => 'U', 'type' => 'str'], //  Вид обеспечения по договору займа
        // Сведения о выдаче займа
        'UF_LOAN_INFORMATION_1C_DATE' => ['col' => 'V', 'type' => 'date'], //  Дата
        'UF_LOAN_INFORMATION_1C_SUM' => ['col' => 'W', 'type' => 'int'], //  Сумма безналичных денежных средств, руб. (указывается общая сумма).

        // Оплата заемщиком процентов по договору займа
        'UF_BORROWER_LOAN_PERCENTS_DATE' => ['col' => 'X', 'type' => 'date'], //  Дата
        'UF_BORROWER_LOAN_PERCENTS_SUM' => ['col' => 'Y', 'type' => 'int'], //  Сумма безналичных денежных средств, руб. (указывается общая сумма).


        'UF_DKP_NUMBER' => ['col' => 'AA', 'type' => 'int'],
        'UF_DKP_DATE' => ['col' => 'AB', 'type' => 'date'],
        'SELLER_FIO' => ['col' => 'AC', 'type' => 'str'],
        'SELLER_PASSPORT' => ['col' => 'AD', 'type' => 'str'],
        'SELLER_ADDRESS' => ['col' => 'AE', 'type' => 'str'],
        'SELLER_FACT_ADDRESS' => ['col' => 'AF', 'type' => 'str'],
        'SELLER_INTRODUCTION_INTO_KPK_DATE' => ['col' => 'AG', 'type' => 'str'],
        'SELLER_EXIT_FROM_KPK_DATE' => ['col' => 'AH', 'type' => 'str'],
        'UF_OWNERSHIP_DATE' => ['col' => 'AI', 'type' => 'str'],
        'UF_CRM_1518964843' => ['col' => 'AJ', 'type' => 'str'],
        'UF_DKP_SUMMA' => ['col' => 'AK', 'type' => 'str'],
        'UF_SHARE_OF_ACQUIRED_PROPERTY' => ['col' => 'AL', 'type' => 'summ'],
        'UF_DKP_CADAS_NUMBER' => ['col' => 'AM', 'type' => 'str'],
        'UF_DKP_CADAS_PRICE' => ['col' => 'AN', 'type' => 'summ'],
        'UF_DKP_OBJECT_AREA' => ['col' => 'AO', 'type' => 'str'],
        'UF_SELLER_REPRESENTATIVE_FIO' => ['col' => 'AP', 'type' => 'str'],
        'UF_BUYER_REPRESENTATIVE_FIO' => ['col' => 'AQ', 'type' => 'str'],
        'DOC_NAME' => ['col' => 'AR', 'type' => 'str'],
        'UF_DATE_OF_TRANSFER_OF_OWNERSHIP' => ['col' => 'AS', 'type' => 'str'],
        'UF_HOMEOWNERS_FIO' => ['col' => 'AT', 'type' => 'str'],
        'FINAL_DATE' => ['col' => 'AU', 'type' => 'str'],
        'PARTNER' => ['col' => 'AY', 'type' => 'str'],
        'PARTNER_1' => ['col' => 'AZ', 'type' => 'str'],
        'PARTNER_2' => ['col' => 'BA', 'type' => 'str'],
        'UF_FAMILY_SHARES' => ['col' => 'BD', 'type' => 'summ'],
        'UF_CRM_1567573226' => ['col' => 'BF', 'type' => 'date']
    ];

    private $arColumnDescriptSecond = [
        'NUMBER' => ['col' => 'A', 'type' => 'int'],
        'FIO_CONTACT' => ['col' => 'B', 'type' => 'str'],
        'PASSPORT' => ['col' => 'C', 'type' => 'str'],
        'CONTACT_INN_NUMBER' => ['col' => 'D', 'type' => 'int'],
        'REGISTER_PLACE' => ['col' => 'E', 'type' => 'str']
    ];

    public function getFile()
    {
        $arColumnDescriptFirst = $this->arColumnDescriptFirst;

        $arColumnDescriptSecond = $this->arColumnDescriptSecond;

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/cb.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spread = $reader->load($template);

        $curSheet = $spread->getSheetByName('Приложение 1');

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setOrder([
                'DATE_CREATE' => 'DESC'
            ])
            ->setSelect([
                '*',
                'UF_*',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'UF_BORROWER_PASSPORT_SER' => 'CONTACT.UF_BORROWER_PASSPORT_SER',
                'UF_BORROWER_PASSPORT_NUMBER' => 'CONTACT.UF_BORROWER_PASSPORT_NUMBER',
                'REGISTER_PLACE' => 'CONTACT.UF_CRM_1507616063',
                'FACT_PLACE' => 'CONTACT.UF_CRM_1505380458',
                'CONTACT_LAST_NAME' => 'CONTACT.LAST_NAME',
                'CONTACT_NAME' => 'CONTACT.NAME',
                'CONTACT_SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'UF_BORROWER_KEM_VIDAN' => 'CONTACT.UF_BORROWER_KEM_VIDAN',
                'UF_BORROWER_DATE' => 'CONTACT.UF_BORROWER_DATE',
                'UF_BORROWER_KOD' => 'CONTACT.UF_BORROWER_KOD',
                'UF_BORROWER_BIRTH_PLACE' => 'CONTACT.UF_BORROWER_BIRTH_PLACE',
                'CONTACT_INN_NUMBER' => 'CONTACT.UF_CRM_1557910731414',
                'UF_BORROWER_PAY_PAYMENTS_1C',
                'UF_DATA_DZ',
                'OPPORTUNITY',
                'UF_CONTRACTUAL_INTEREST_RATE',
                'UF_EDZ_CONTRACT_PERIOD',
                'UF_LOAN_SECURITY',
                'UF_BORROWER_LOAN_PERCENTS',
                'UF_BORROWER_PAY_PAYMENTS_1C',
                'UF_LOAN_INFORMATION_1C',
            ])
            ->setFilter(['ID' => $this->ids])
            ->exec();

        $managers = \Vaganov\Helper::getUsers(53, false);

        $arDeals = $query->fetchAll();

        $dealList = [];

        $sellerFio = [];
        $sellerPassport = [];
        $sellerAddress = [];
        $dealsPartZime = [];
        $partners = [];

        foreach ($arDeals as $deal) {
            $dealList[] = $deal;

            $dealsPartZime[] = $deal[PART_ZAIM];

            \Vaganov\Helper::includeHlTable('edz_seller_passport');

            foreach ($deal['UF_PASSPORT_SELLER'] as $item) {

                $rsData = \edzSellerPassportTable::getList([
                    'select' => ['*'],
                    'filter' => ['ID' => $item]
                ])->Fetch();

                $rsData['UF_DATE'] = $rsData['UF_DATE'] ? date('d.m.Y', strtotime($rsData['UF_DATE'])) : '';

                $sellerFio[$deal['ID']][] = $rsData['UF_FIO'];

                $sellerPassport[$deal['ID']][] = "серия: {$rsData['UF_SER']}; Номер: {$rsData['UF_NUMBER']}; ". "Кем выдан: {$rsData['UF_KEM_VIDAN']}; Дата: {$rsData['UF_DATE']}; Код: {$rsData['UF_KOD']};";

                $sellerAddress[$deal['ID']][] = $rsData['UF_ADDRESS'];
            }
        }

        $resPartners = \CCrmDeal::GetListEx(
            [],
            ['CONTACT_ID' => $dealsPartZime],
            false,
            false,
            ['*', 'UF_*']
        );

        while ($i = $resPartners->Fetch()) {
            $partners[$i['CONTACT_ID']] = $i;
        }

        $rowNum = 5;
        $count = 1;

        foreach ($dealList as $deal) {

            $dealManyLines = [];


            $deal['NUMBER'] = $count;

            $deal['UF_BORROWER_PASSPORT_SER'] = str_replace(' ', '', $deal['UF_BORROWER_PASSPORT_SER']);

            $deal['FIO_CONTACT'] = trim(trim($deal['CONTACT_LAST_NAME']) . ' ' . trim($deal['CONTACT_NAME']) . ' ' . trim($deal['CONTACT_SECOND_NAME']));

            $deal['ASSIGNED'] = $managers[$deal['ASSIGNED_BY_ID']] ? : $deal['ASSIGNED_BY_ID'];

            $deal['SELLER_FIO'] = implode(",\n", $sellerFio[$deal['ID']]);

            $deal['SELLER_PASSPORT'] = implode(",\n", $sellerPassport[$deal['ID']]);

            $deal['SELLER_ADDRESS'] = implode(",\n", $sellerAddress[$deal['ID']]);
            $deal['SELLER_FACT_ADDRESS'] = implode(",\n", $sellerAddress[$deal['ID']]);

            $deal['SELLER_INTRODUCTION_INTO_KPK_DATE'] = 'Нет';
            $deal['SELLER_EXIT_FROM_KPK_DATE'] = 'Нет';

            $deal['DOC_NAME'] = 'ДКП';

            $deal['PARTNER'] = trim($partners[$deal[PART_ZAIM]]['CONTACT_LAST_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_SECOND_NAME']);

            $deal['PARTNER_1'] = '-';

            $deal['PARTNER_2'] = trim($partners[$deal[PART_ZAIM]]['CONTACT_LAST_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_SECOND_NAME']);

            if (!empty($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES'])) {
                $deal['FINAL_DATE'] = (new \DateTime($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES']))->format('d.m.Y');
            } else if (!empty($deal['UF_CRM_1567573226'])) {
                $deal['FINAL_DATE'] = (new \DateTime($deal['UF_CRM_1567573226']))->modify('+180 days')->format('d.m.Y');
            } else {
                $deal['FINAL_DATE'] = '-';
            }


            $deal['UF_OWNERSHIP_DATE'] =  $this->getValueJsonHelper($deal['UF_OWNERSHIP_DATE'],['fio','date']);
            $deal['UF_SELLER_REPRESENTATIVE_FIO'] = $this->getValueJsonHelper($deal['UF_SELLER_REPRESENTATIVE_FIO'],['value']);
            $deal['UF_BUYER_REPRESENTATIVE_FIO'] = $this->getValueJsonHelper($deal['UF_BUYER_REPRESENTATIVE_FIO'],['value']);
            $deal['UF_HOMEOWNERS_FIO'] = $this->getValueJsonHelper($deal['UF_HOMEOWNERS_FIO'],['value']);

            //$deal['UF_BORROWER_PAY_PAYMENTS_1C_DATE'] = $this->getValueJsonHelper($deal['UF_BORROWER_PAY_PAYMENTS_1C'],['date']);
            $dealManyLines['UF_BORROWER_PAY_PAYMENTS_1C_DATE'] = $this->getValueJsonLineHelper($deal['UF_BORROWER_PAY_PAYMENTS_1C'],['date']);

            //$deal['UF_BORROWER_PAY_PAYMENTS_1C_SUM'] = $this->getValueJsonHelper($deal['UF_BORROWER_PAY_PAYMENTS_1C'],['sum']);
            $dealManyLines['UF_BORROWER_PAY_PAYMENTS_1C_SUM'] = $this->getValueJsonLineHelper($deal['UF_BORROWER_PAY_PAYMENTS_1C'],['sum']);


            $dealManyLines['UF_BORROWER_LOAN_PERCENTS_DATE'] = $this->getValueJsonLineHelper($deal['UF_BORROWER_LOAN_PERCENTS'],['date']);
            $dealManyLines['UF_BORROWER_LOAN_PERCENTS_SUM'] = $this->getValueJsonLineHelper($deal['UF_BORROWER_LOAN_PERCENTS'],['sum']);

            $dealManyLines['UF_LOAN_INFORMATION_1C_DATE'] = $this->getValueJsonLineHelper($deal['UF_LOAN_INFORMATION_1C'],['date']);
            $dealManyLines['UF_LOAN_INFORMATION_1C_SUM'] = $this->getValueJsonLineHelper($deal['UF_LOAN_INFORMATION_1C'],['sum']);


            if(count($dealManyLines) > 0){
                $countAr = array_map(function($i){return count($i);},$dealManyLines);
                $max = max($countAr);

                for($i = 0; $i < $max ; $i++){
                    $dealAddData = [];
                    foreach ($dealManyLines as $field => $value){
                        $dealAddData[$field] = $value[$i] ?: '';
                    }
                    if($i === 0){
                        $this->setRowData($curSheet, array_merge($deal,$dealAddData), $rowNum, $this->arColumnDescriptFirst);
                    }else{
                        $this->setRowData($curSheet, $dealAddData, $rowNum, $this->arColumnDescriptFirst);
                    }
                    $rowNum++;
                }
                $rowNum--;

            }else{
                $this->setRowData($curSheet, $deal, $rowNum, $this->arColumnDescriptFirst);
            }

            $count++;
            $rowNum++;
        }

        unset($count, $rowNum);

        $curSheet = $spread->getSheetByName('Приложение 3');

        $count = 1;
        $rowNum = 3;

        foreach ($dealList as $deal) {
            foreach ($arColumnDescriptSecond as $field => $descript) {
                if ($field === 'NUMBER') {
                    $deal[$field] = $count;
                }

                if ($field === 'FIO_CONTACT') {
                    $deal[$field] = trim(trim($deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']) . ' ' . $deal['CONTACT_SECOND_NAME']);
                }

                if ($field === 'PASSPORT') {
                    $deal[$field] = 'Серия: ' . $deal['UF_BORROWER_PASSPORT_SER'] . '; Номер: ' . $deal['UF_BORROWER_PASSPORT_NUMBER'] . '; Кем выдан: ' . $deal['UF_BORROWER_KEM_VIDAN'] . '; Дата: ' . $deal['UF_BORROWER_DATE'] . '; Код: ' . $deal['UF_BORROWER_KOD'] . '; Место рождения: ' . $deal['UF_BORROWER_BIRTH_PLACE'];
                }

                if (!empty($deal[$field])) {
                    if ($descript['type'] === 'date') {
                        $val = new \DateTime($deal[$field]);

                        $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($val);
                        $curSheet->getCell($descript['col'] . $rowNum)->setValueExplicit($excelDateValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_ISO_DATE);
                        $curSheet->getStyle($descript['col'] . $rowNum)
                            ->getNumberFormat()
                            ->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
                            );
                    } else if ($descript['type'] === 'int' || $descript['type'] === 'summ') {
                        $curSheet->getCell($descript['col'] . $rowNum)->setValueExplicit(floatval($deal[$field]), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $curSheet->getStyle($descript['col'] . $rowNum)
                            ->getNumberFormat()
                            ->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00
                            );
                    } else {
                        $curSheet->setCellValue($descript['col'] . $rowNum, $deal[$field]);
                    }
                }
            }

            $count++;
            $rowNum++;
        }

        $curSheet = $spread->getSheetByName('Приложение 1');

        $newfile = $_SERVER['DOCUMENT_ROOT'] . '/upload/ЦБ.xlsx';

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        return '/upload/ЦБ.xlsx';
    }

    /* HELPERS */


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


    private function getValueJsonLineHelper($value, $fieldsAr, $separatorInLine = ' '){
        $data = json_decode($value, 1);
        if (!empty($data)) {
            $arr = [];
            foreach ($data as $item) {
                $lineAr = [];
                foreach ($fieldsAr as $itemField){
                    $lineAr[] =  $item[$itemField];
                }
                $arr[] = implode($lineAr,$separatorInLine);
            }
            return $arr;
        }
        return '';
    }

    private function getValueJsonHelper($value, $fieldsAr, $separatorInLine = ' ', $separatorForTheNext = ",\n"){
        $lineAr = $this->getValueJsonLineHelper($value, $fieldsAr, $separatorInLine);
        if ($lineAr) {
            return implode($separatorForTheNext, $lineAr);
        }
        return '';
    }
}