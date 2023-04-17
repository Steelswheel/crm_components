<?php
namespace Components\Vaganov\ReportsAll\CustomReport;

include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;

Loader::IncludeModule('crm');

class Excel
{
    public function getFile()
    {
        $arColumnDescriptFirst = [
            'FIO_CONTACT' => ['col' => 'A', 'type' => 'str'],
            'UF_NUMBER_DZ' => ['col' => 'B', 'type' => 'str'],
            'UF_DATA_DZ' => ['col' => 'C', 'type' => 'date'],
            'UF_CRM_1518964843' => ['col' => 'D', 'type' => 'str'],
            'UF_DKP_CADAS_NUMBER' => ['col' => 'E', 'type' => 'str']
        ];

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/custom.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spread = $reader->load($template);

        $curSheet = $spread->getSheetByName('custom');

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
                'CONTACT_INN_NUMBER' => 'CONTACT.UF_CRM_1557910731414'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                '><UF_DATA_DZ' => ['01.01.2022', '27.12.2022']
            ])
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
            foreach ($arColumnDescriptFirst as $field => $descript) {
                if ($field === 'NUMBER') {
                    $deal[$field] = $count;
                }

                if ($field === 'UF_BORROWER_PASSPORT_SER') {
                    $deal[$field] = str_replace(' ', '', $deal['UF_BORROWER_PASSPORT_SER']);
                }

                if ($field === 'FIO_CONTACT') {
                    $deal[$field] = trim(trim($deal['CONTACT_LAST_NAME']) . ' ' . trim($deal['CONTACT_NAME']) . ' ' . trim($deal['CONTACT_SECOND_NAME']));
                }

                if ($field === 'ASSIGNED') {
                    $deal[$field] = $managers[$deal['ASSIGNED_BY_ID']] ? : $deal['ASSIGNED_BY_ID'];
                }

                if ($field === 'SELLER_FIO') {
                    $deal[$field] = implode(",\n", $sellerFio[$deal['ID']]);
                }

                if ($field === 'SELLER_PASSPORT') {
                    $deal[$field] = implode(",\n", $sellerPassport[$deal['ID']]);
                }

                if ($field === 'SELLER_ADDRESS' || $field === 'SELLER_FACT_ADDRESS') {
                    $deal[$field] = implode(",\n", $sellerAddress[$deal['ID']]);
                }

                if ($field === 'SELLER_INTRODUCTION_INTO_KPK_DATE' || $field === 'SELLER_EXIT_FROM_KPK_DATE') {
                    $deal[$field] = 'Нет';
                }

                if ($field === 'DOC_NAME') {
                    $deal[$field] = 'ДКП';
                }

                if ($field === 'PARTNER') {
                    $deal[$field] = trim($partners[$deal[PART_ZAIM]]['CONTACT_LAST_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_SECOND_NAME']);
                }

                if ($field === 'PARTNER_1') {
                    $deal[$field] = '-';
                }

                if ($field === 'PARTNER_2') {
                    $deal[$field] = trim($partners[$deal[PART_ZAIM]]['CONTACT_LAST_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_NAME']) . ' ' . trim($partners[$deal[PART_ZAIM]]['CONTACT_SECOND_NAME']);
                }

                if ($field === 'FINAL_DATE') {
                    if (!empty($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES'])) {
                        $deal[$field] = (new \DateTime($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES']))->format('d.m.Y');
                    } else if (!empty($deal['UF_CRM_1567573226'])) {
                        $deal[$field] = (new \DateTime($deal['UF_CRM_1567573226']))->modify('+180 days')->format('d.m.Y');
                    } else {
                        $deal[$field] = '-';
                    }
                }

                if ($field === 'UF_OWNERSHIP_DATE') {
                    $data = json_decode($deal[$field], 1);

                    if (!empty($data)) {
                        $arr = [];

                        foreach ($data as $item) {
                            $arr[] = $item['fio'] . ' ' . $item['date'];
                        }

                        $deal[$field] = implode(",\n", $arr);
                    }
                }

                if ($field === 'UF_SELLER_REPRESENTATIVE_FIO' || $field === 'UF_BUYER_REPRESENTATIVE_FIO' || $field === 'UF_HOMEOWNERS_FIO') {
                    $data = json_decode($deal[$field], 1);

                    if (!empty($data)) {
                        $arr = [];

                        foreach ($data as $item) {
                            $arr[] = $item['value'];
                        }

                        $deal[$field] = implode(",\n", $arr);
                    }
                }

                if (!empty($deal[$field])) {
                    if ($descript['type'] === 'date') {
                        $val = new \DateTime($deal[$field]);

                        $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($val);
                        $curSheet->getCell($descript['col'] . $rowNum)->setValue($excelDateValue);
                        $curSheet->getStyle($descript['col'] . $rowNum)
                            ->getNumberFormat()
                            ->setFormatCode(
                                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY
                            );
                    } else if ($descript['type'] === 'int' || $descript['type'] === 'summ') {
                        $value = str_replace(' ', '', $deal[$field]);

                        $curSheet->getCell($descript['col'] . $rowNum)->setValueExplicit((float)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
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

        $newfile = $_SERVER['DOCUMENT_ROOT'] . '/upload/custom_report.xlsx';

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        return '/upload/custom_report.xlsx';
    }
}