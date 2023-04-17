<?php
namespace Components\Vaganov\EdsCreate;

include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use Bitrix\Main\Loader;
use Components\Vaganov\EdsShow\EdsModel;
use Vaganov\Helper;

ini_set('max_execution_time', '300000');

class EdsCreateParser
{
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function run()
    {
        Loader::IncludeModule('crm');

        ini_set('memory_limit', '768M');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);

        $spread = $reader->load($this->file);

        $worksheet = $spread->getSheetByName('asd');

        $all_payments = $this->prepareData($worksheet);

        $result = [];

        $allCount = count($all_payments);
        $count = 0;

        foreach ($all_payments as $payment) {
            $class = new EdsModel();

            $result[] = $class->update($payment);

            $class->getMONTHLY_INTEREST_PAYMENTS();

            Helper::includeHlTable('money_orders');

            if (!empty($payment['TRANSFER_OF_FUNDS_SUM'] && !empty($payment['TRANSFER_OF_FUNDS_DATE']))) {
                $purpose = 'За ' . (new \DateTime($payment['TRANSFER_OF_FUNDS_DATE']))->format('d.m.Y') . ' ' . $payment['LAST_NAME'] . ' ' . $payment['NAME'] . ' ' . $payment['SECOND_NAME'] . '; взнос по договору сбережений ' . $payment['UF_EDS_CONTRACT_NUMBER'] . ' от ' . $payment['UF_EDS_CONTRACT_DATE'];

                $paymentData = [
                    'UF_SUM' => $payment['TRANSFER_OF_FUNDS_SUM'],
                    'UF_PAYMENT' => 'IN',
                    'UF_DATE_IN' => (new \DateTime($payment['TRANSFER_OF_FUNDS_DATE']))->format('d.m.Y H:i:s'),
                    'UF_HANDLER' => 'balance_replenishment',
                    'UF_DATE_PROCESSING' => (new \DateTime($payment['TRANSFER_OF_FUNDS_DATE']))->format('d.m.Y H:i:s'),
                    'UF_DEAL_ID' => $class->dealId,
                    'UF_DATE_DOC' => (new \DateTime($payment['TRANSFER_OF_FUNDS_DATE']))->format('d.m.Y H:i:s'),
                    'UF_PURPOSE_OF_PAYMENT' => $purpose
                ];

                \MoneyOrdersTable::ADD($paymentData);
            }

            if (!empty($payment['BALANCE_REPLENISHMENT_DATE']) && !empty($payment['BALANCE_REPLENISHMENT_SUM'])) {
                $purpose = 'За ' . (new \DateTime($payment['BALANCE_REPLENISHMENT_DATE']))->format('d.m.Y') . ' ' . $payment['LAST_NAME'] . ' ' . $payment['NAME'] . ' ' . $payment['SECOND_NAME'] . '; пополнение баланса по договору сбережений' . $payment['UF_EDS_CONTRACT_NUMBER'] . ' от ' . $payment['UF_EDS_CONTRACT_DATE'];

                $paymentData = [
                    'UF_SUM' => $payment['BALANCE_REPLENISHMENT_SUM'],
                    'UF_PAYMENT' => 'IN',
                    'UF_DATE_IN' => (new \DateTime($payment['BALANCE_REPLENISHMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_HANDLER' => 'balance_replenishment',
                    'UF_DATE_PROCESSING' => (new \DateTime($payment['BALANCE_REPLENISHMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_DEAL_ID' => $class->dealId,
                    'UF_DATE_DOC' => (new \DateTime($payment['BALANCE_REPLENISHMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_PURPOSE_OF_PAYMENT' => $purpose
                ];

                \MoneyOrdersTable::ADD($paymentData);
            }

            if (!empty($payment['PARTIAL_WITHDRAWAL_DATE']) && !empty($payment['PARTIAL_WITHDRAWAL_SUM'])) {
                $purpose = 'За ' . (new \DateTime($payment['PARTIAL_WITHDRAWAL_DATE']))->format('d.m.Y') . ' ' . $payment['LAST_NAME'] . ' ' . $payment['NAME'] . ' ' . $payment['SECOND_NAME'] . '; частичное снятие по договору сбережений ' . $payment['UF_EDS_CONTRACT_NUMBER'] . ' от ' . $payment['UF_EDS_CONTRACT_DATE'];

                $paymentData = [
                    'UF_SUM' => $payment['PARTIAL_WITHDRAWAL_SUM'],
                    'UF_PAYMENT' => 'OUT',
                    'UF_DATE_IN' => (new \DateTime($payment['PARTIAL_WITHDRAWAL_DATE']))->format('d.m.Y H:i:s'),
                    'UF_HANDLER' => 'partial_withdrawal',
                    'UF_DATE_PROCESSING' => (new \DateTime($payment['PARTIAL_WITHDRAWAL_DATE']))->format('d.m.Y H:i:s'),
                    'UF_DEAL_ID' => $class->dealId,
                    'UF_DATE_DOC' => (new \DateTime($payment['PARTIAL_WITHDRAWAL_DATE']))->format('d.m.Y H:i:s'),
                    'UF_PURPOSE_OF_PAYMENT' => $purpose
                ];

                \MoneyOrdersTable::ADD($paymentData);
            }

            if (!empty($payment['SAVINGS_PAYMENT_DATE']) && !empty($payment['SAVINGS_PAYMENT_SUM'])) {
                $purpose = 'За ' . (new \DateTime($payment['SAVINGS_PAYMENT_DATE']))->format('d.m.Y') . ' ' . $payment['LAST_NAME'] . ' ' . $payment['NAME'] . ' ' . $payment['SECOND_NAME'] . '; выплата дс по договору сбережений ' . $payment['UF_EDS_CONTRACT_NUMBER'] . ' от ' . $payment['UF_EDS_CONTRACT_DATE'];

                $paymentData = [
                    'UF_SUM' => $payment['SAVINGS_PAYMENT_SUM'],
                    'UF_PAYMENT' => 'OUT',
                    'UF_DATE_IN' => (new \DateTime($payment['SAVINGS_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_HANDLER' => 'cash_payment',
                    'UF_DATE_PROCESSING' => (new \DateTime($payment['SAVINGS_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_DEAL_ID' => $class->dealId,
                    'UF_DATE_DOC' => (new \DateTime($payment['SAVINGS_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_PURPOSE_OF_PAYMENT' => $purpose
                ];

                \MoneyOrdersTable::ADD($paymentData);
            }

            if (!empty($payment['INTEREST_PAYMENT_DATE']) && !empty($payment['INTEREST_PAYMENT_SUM'])) {
                $purpose = 'За ' . (new \DateTime($payment['INTEREST_PAYMENT_DATE']))->format('d.m.Y') . ' ' . $payment['LAST_NAME'] . ' ' . $payment['NAME'] . ' ' . $payment['SECOND_NAME'] . '; выплата процентов по договору сбережений ' . $payment['UF_EDS_CONTRACT_NUMBER'] . ' от ' . $payment['UF_EDS_CONTRACT_DATE'];

                $paymentData = [
                    'UF_KPK' => $payment['KPK_WORK'],
                    'UF_SUM' => $payment['INTEREST_PAYMENT_SUM'],
                    'UF_PAYMENT' => 'OUT',
                    'UF_DATE_IN' => (new \DateTime($payment['INTEREST_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_HANDLER' => 'interest_payment',
                    'UF_DATE_PROCESSING' => (new \DateTime($payment['INTEREST_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_DEAL_ID' => $class->dealId,
                    'UF_DATE_DOC' => (new \DateTime($payment['INTEREST_PAYMENT_DATE']))->format('d.m.Y H:i:s'),
                    'UF_PURPOSE_OF_PAYMENT' => $purpose,
                    'UF_STATUS' => 'success'
                ];

                \MoneyOrdersTable::ADD($paymentData);
            }

            $count++;

            echo round(($count * 100 / $allCount)) . '%' . PHP_EOL;
        }

        return $result;
    }

    private function prepareData($spreadsheet) {
        $sharedData = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $cells = $spreadsheet->getCellCollection();

        $arr = [];

        $data = [
            'MANAGER' => 'A',
            'CONTRACT_DATE' => 'C',
            'CONTRACT_NUMBER' => 'D',
            'CONTACT_FIO' => 'E',
            'FIRST_PAYMENT_DATE' => 'F',//первое зачисление
            'FIRST_PAYMENT_SUM' => 'G',//первое зачисление
            'INTEREST_RATE' => 'H',//процентная ставка
            'INTEREST_PAYMENT_EVERY_MONTH' => 'I',
            'INTEREST_PAYMENT_IN_THE_END' => 'J',
            'TERM_OF_CONTRACT' => 'K',//длительность вклада в днях
            'CONTRACT_DATE_END' => 'L',//дата окончания контракта
            'UF_BORROWER_PASSPORT_SER' => 'M',
            'UF_BORROWER_PASSPORT_NUMBER' => 'N',
            'UF_BORROWER_DATE' => 'O',
            'UF_BORROWER_KEM_VIDAN' => 'P',
            'UF_BORROWER_KOD' => 'Q',
            'UF_CRM_1507616063' => 'R',//регистрация
            'UF_CRM_1557910731414' => 'S',//ИНН
            'EMAIL' => 'T',
            'PHONE' => 'U',
            'BALANCE_REPLENISHMENT_DATE' => 'V',
            'BALANCE_REPLENISHMENT_SUM' => 'W',
            'PARTIAL_WITHDRAWAL_DATE' => 'X',
            'PARTIAL_WITHDRAWAL_SUM' => 'Y',
            'SAVINGS_PAYMENT_DATE' => 'Z',
            'SAVINGS_PAYMENT_SUM' => 'AA',
            'INTEREST_PAYMENT_DATE' => 'AB',
            'INTEREST_PAYMENT_SUM' => 'AC',
            'UF_CONTRACT_AMOUNT' => 'AD'
        ];

        for ($row = 5; $row <= $cells->getHighestRow(); $row++) {
            $item = [];

            foreach ($data as $key => $value) {
                $cell = $cells->get($value . $row);

                if ($cell) {
                    if ($value === 'C' || $value === 'F' || $value === 'O' || $value === 'V' || $value === 'X' || $value === 'Z' || $value === 'AB') {
                        if (!empty($cells->get($value . $row)->getValue())) {
                            $item[$key] = date('d.m.Y', $sharedData::excelToTimestamp($cells->get($value . $row)->getValue()));
                        }
                    } else if ($value === 'I') {
                        $cellValue = $cells->get($value . $row)->getValue();

                        if ($cellValue) {
                            $item['INTEREST_PAYMENT'] = '750';//ежемесячно
                        }
                    } else if ($value === 'J') {
                        $cellValue = $cells->get($value . $row)->getValue();

                        if ($cellValue) {
                            $item['INTEREST_PAYMENT'] = '751';//в конце срока
                        }
                    } else if ($value === 'K') {
                        $cellValue = $cells->get($value . $row)->getValue();

                        if ($cellValue) {
                            switch ($cellValue) {
                                case 90:
                                    $item['CONTRACT_PERIOD'] = '1';
                                    break;
                                case 180:
                                    $item['CONTRACT_PERIOD'] = '2';
                                    break;
                                case 365:
                                case 360:
                                    $item['CONTRACT_PERIOD'] = '3';
                                    break;
                                case 730:
                                    $item['CONTRACT_PERIOD'] = '4';
                                    break;
                            }
                        }
                    } else if ($value === 'L') {
                        if (!empty($cells->get($value . $row)->getValue())) {
                            $item['CONTRACT_DATE_END'] = date('d.m.Y', $sharedData::excelToTimestamp($cells->get($value . $row)->getCalculatedValue()));
                        }
                    } else if ($value === 'G' || $value === 'W' || $value === 'AC' || $value === 'AA') {
                        if (!empty($cells->get($value . $row)->getValue())) {
                            $item[$key] = $cells->get($value . $row)->getCalculatedValue();
                        }
                    } else {
                        if (!empty($cells->get($value . $row)->getValue())) {
                            $item[$key] = trim($cells->get($value . $row)->getValue());
                        }
                    }
                }
            }

            if ($item['CONTACT_FIO']) {
                $arr[] = $item;
            }
        }

        $preparedData = [];

        foreach ($arr as $item) {
            $fio = explode(' ', $item['CONTACT_FIO']);

            if (count($fio) > 3) {
                $last_name = $fio[0] . ' ' . $fio[1];
                $name = $fio[2];
                $second_name = $fio[3];
            } else {
                $last_name = $fio[0];
                $name = $fio[1];
                $second_name = $fio[2];
            }

            $preparedData[] = [
                'STAGE_ID' => 'C14:CURRENT_CONTRACT',
                'UF_CONTRACT_AMOUNT' => (int)$item['UF_CONTRACT_AMOUNT'],
                'ASSIGNED_BY_ID' => (int)$item['MANAGER'],
                'UF_EDS_CONTRACT_DATE' => $item['CONTRACT_DATE'],
                'UF_EDS_CONTRACT_NUMBER' => $item['CONTRACT_NUMBER'],
                'LAST_NAME' => $last_name,
                'NAME' => $name,
                'SECOND_NAME' => $second_name,
                'TRANSFER_OF_FUNDS_DATE' => $item['FIRST_PAYMENT_DATE'],
                'TRANSFER_OF_FUNDS_SUM' => $item['FIRST_PAYMENT_SUM'],
                'UF_CONTRACTUAL_INTEREST_RATE' => (int)$item['INTEREST_RATE'],
                'UF_INTEREST_PAYMENT' => $item['INTEREST_PAYMENT'],
                'UF_CONTRACT_PERIOD' => $item['UF_CONTRACT_PERIOD'],
                'UF_SAVINGS_DEPOSIT_END_DATE' => $item['CONTRACT_DATE_END'],
                'UF_BORROWER_PASSPORT_SER' => $item['UF_BORROWER_PASSPORT_SER'],
                'UF_BORROWER_PASSPORT_NUMBER' => $item['UF_BORROWER_PASSPORT_NUMBER'],
                'UF_BORROWER_DATE' => $item['UF_BORROWER_DATE'],
                'UF_BORROWER_KEM_VIDAN' => $item['UF_BORROWER_KEM_VIDAN'],
                'UF_BORROWER_KOD' => $item['UF_BORROWER_KOD'],
                'REGISTRATION_PLACE' => $item['UF_CRM_1507616063'],
                'CONTACT_INN_NUMBER' => (int)$item['UF_CRM_1557910731414'],
                'EMAIL' => $item['EMAIL'],
                'PHONE' => $item['PHONE'],
                'BALANCE_REPLENISHMENT_DATE' => $item['BALANCE_REPLENISHMENT_DATE'],
                'BALANCE_REPLENISHMENT_SUM' => $item['BALANCE_REPLENISHMENT_SUM'],
                'PARTIAL_WITHDRAWAL_DATE' => $item['PARTIAL_WITHDRAWAL_DATE'],
                'PARTIAL_WITHDRAWAL_SUM' => $item['PARTIAL_WITHDRAWAL_SUM'],
                'SAVINGS_PAYMENT_DATE' => $item['SAVINGS_PAYMENT_DATE'],
                'SAVINGS_PAYMENT_SUM' => $item['SAVINGS_PAYMENT_SUM'],
                'INTEREST_PAYMENT_DATE' => $item['INTEREST_PAYMENT_DATE'],
                'INTEREST_PAYMENT_SUM' => $item['INTEREST_PAYMENT_SUM']
            ];
        }

        return $preparedData;
    }
}