<?php
namespace Components\Vaganov\ReportsAll\ReportSalePlan;

// include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
// require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';



use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;

Loader::IncludeModule('crm');

class ReportExcel
{
    private $ids;
    private string $startDate;
    private string $endDate;

    public function __construct($month, $ids)
    {
        global $USER;

        $managers = BonusList::getUsers(241);

        if (in_array((int)$USER->GetID(), array_keys($managers)) && !in_array((int)$USER->GetID(), \Vaganov\Helper::getROPs())) {
            $this->ids = $USER->GetID();
        } else {
            $this->ids = $ids;
        }

        $date = new \DateTime('01.' . $month);
        $this->startDate = $date->modify('first day of this month')->format('d.m.Y');
        $this->endDate = $date->modify('last day of this month')->format('d.m.Y');
    }

    private function getSalesDeals()
    {
        $managers = BonusList::getUsers(241);

        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'STAGE_ID',
                'ASSIGNED_BY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'UF_BONUS_PLAN_PERCENT',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'UF_IS_PASS_CONFIRMED'
            ])
            ->setFilter([
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '><DATE_PFR_SEND' => [$this->startDate, $this->endDate],
                    '><DATE_RB_SEND' => [$this->startDate, $this->endDate]
                ],
                'UF_IS_PASS_CONFIRMED' => '1',
                'ASSIGNED_BY_ID' => $this->ids
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
            $deal['FACT'] = $deal['UF_BONUS_PLAN_PERCENT'] ? : 0;
            $deal['BONUS'] = (new BonusList())->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT'] ? : 0);
            $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];

            if ($deal['ASSIGNED_BY_ID'] === '34') {
                $result['45'][] = $deal;
            } else {
                $result[$deal['ASSIGNED_BY_ID']][] = $deal;
            }
        }

        return $result;
    }

    private function getBonusDeals()
    {
        $managers = BonusList::getUsers(241);
        $query = new Query(DealTable::getEntity());

        $query
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'UF_BONUS_PLAN_PERCENT',
                'STAGE_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'DATE_PFR_SEND' => 'UF_CRM_1518967556',
                'PAYMENT_PFR_DATA' => 'UF_CRM_1567499237',
                'PAYMENT_PFR_SUM' => 'UF_CRM_1567499259',
                'DATE_RB_SEND' => 'UF_CRM_1584934425',
                'PAYMENT_REGION_DATA' => 'UF_CRM_1567499436',
                'PAYMENT_REGION_SUM' => 'UF_CRM_1567499470',
                'UF_BONUS_PAIDED_DATE'
            ])
            ->setGroup([
                'ID'
            ])
            ->setFilter([
                'ASSIGNED_BY_ID' => $this->ids,
                'CATEGORY_ID' => 8,
                [
                    'LOGIC' => 'OR',
                    '!=PAYMENT_PFR_DATA' => null,
                    '!=PAYMENT_REGION_DATA' => null
                ],
                '><UF_BONUS_PAIDED_DATE' => [$this->startDate, $this->endDate]
            ])
            ->exec();

        $result = [];

        foreach ($query->fetchAll() as $deal) {
            $deal['BONUS'] = (new BonusList())->getManagerBonus($deal['ASSIGNED_BY_ID'], $deal['UF_BONUS_PLAN_PERCENT']);
            $deal['FIO'] = trim($deal['LAST_NAME'] . ' ' . trim($deal['NAME']) . ' ' . trim($deal['SECOND_NAME']));
            $deal['DATE_PFR_SEND'] = $deal['DATE_PFR_SEND'] ? (new \DateTime($deal['DATE_PFR_SEND']))->format('d.m.Y') : '';
            $deal['PAYMENT_PFR_DATA'] = $deal['PAYMENT_PFR_DATA'] ? (new \DateTime($deal['PAYMENT_PFR_DATA']))->format('d.m.Y') : '';
            $deal['PAYMENT_PFR_SUM'] = $deal['PAYMENT_PFR_SUM'] ? number_format((float)$deal['PAYMENT_PFR_SUM'], 2, ',', ' ') : '';
            $deal['DATE_RB_SEND'] = $deal['DATE_RB_SEND'] ? (new \DateTime($deal['DATE_RB_SEND']))->format('d.m.Y') : '';
            $deal['PAYMENT_REGION_DATA'] = $deal['PAYMENT_REGION_DATA'] ? (new \DateTime($deal['PAYMENT_REGION_DATA']))->format('d.m.Y') : '';
            $deal['PAYMENT_REGION_SUM'] = $deal['PAYMENT_REGION_SUM'] ? number_format((float)$deal['PAYMENT_REGION_SUM'], 2, ',', ' ') : '';
            $deal['MANAGER'] = $managers[$deal['ASSIGNED_BY_ID']]['NAME'];

            if ((int)$deal['ASSIGNED_BY_ID'] === '34') {
                $result['45'][] = $deal;
            } else {
                $result[$deal['ASSIGNED_BY_ID']][] = $deal;
            }
        }

        return $result;
    }

    private function getData()
    {
        $salesDeals = $this->getSalesDeals();
        $bonusDeals = $this->getBonusDeals();

        $result = [];

        foreach ($salesDeals as $key => $value) {
            $result[$key]['SALES_DEALS'] = $value;
        }

        foreach ($bonusDeals as $key => $value) {
            $result[$key]['BONUS_DEALS'] = $value;
        }

        return $result;
    }

    public function getFile()
    {
        $salesDescript = [
            'MANAGER' => ['col' => 'B', 'type' => 'str'],
            'FACT' => ['col' => 'C', 'type' => 'str'],
            'FIO' => ['col' => 'D', 'type' => 'str'],
            'BONUS' => ['col' => 'E', 'type' => 'int']
        ];

        $bonusDescript = [
            'MANAGER' => ['col' => 'A', 'type' => 'str'],
            'FIO' => ['col' => 'B', 'type' => 'str'],
            'PAYMENT_PFR_DATA' => ['col' => 'C', 'type' => 'str'],
            'PAYMENT_PFR_SUM' => ['col' => 'D', 'type' => 'int'],
            'PAYMENT_REGION_DATA' => ['col' => 'E', 'type' => 'int'],
            'PAYMENT_REGION_SUM' => ['col' => 'F', 'type' => 'int'],
            'BONUS' => ['col' => 'G', 'type' => 'int']
        ];

        $template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/bonuses.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $data = $this->getData();

        $spread = $reader->load($template);

        $curSheet = $spread->setActiveSheetIndex(0);

        $rowNum = 3;

        foreach ($data as $managerId => $value) {
            foreach ($value['SALES_DEALS'] as $deal) {
                foreach ($salesDescript as $field => $descript) {
                    $curSheet->getStyle($descript['col'] . $rowNum)->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '00000000'],
                            ]
                        ],
                        'font' => [
                            'name' => 'Times New Roman',
                            'size' => 11
                        ]
                    ]);

                    $curSheet->setCellValue($descript['col'] . $rowNum, $deal[$field]);
                }

                $rowNum++;
            }

            $styleArray = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ]
                ],
                'font' => [
                    'bold' => true,
                    'name' => 'Times New Roman',
                    'size' => 11
                ],
                'alignment' => [
                    'horizontal' => 'left'
                ]
            ];

            $curSheet->getStyle('B' . $rowNum)->applyFromArray($styleArray);
            $curSheet->setCellValue('B' . $rowNum, 'Итого сделок:');
            $curSheet->mergeCells('B' . $rowNum . ':D' . $rowNum);

            $styleArray['alignment']['horizontal'] = 'center';
            $curSheet->getStyle('E' . $rowNum)->applyFromArray($styleArray);
            $curSheet->setCellValue('E' . $rowNum, count($value['SALES_DEALS']));

            $rowNum++;
        }

        $curSheet = $spread->setActiveSheetIndex(1);

        $rowNum = 3;

        foreach ($data as $managerId => $value) {
            if (!empty($value['BONUS_DEALS'])) {
                $sum = 0;

                foreach ($value['BONUS_DEALS'] as $deal) {
                    foreach ($bonusDescript as $field => $descript) {
                        $curSheet->getStyle($descript['col'] . $rowNum)->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '00000000']
                                ]
                            ],
                            'font' => [
                                'name' => 'Times New Roman',
                                'size' => 11
                            ]
                        ]);

                        $curSheet->setCellValue($descript['col'] . $rowNum, $deal[$field]);
                    }

                    $sum += !empty($deal['BONUS']) ? (int)$deal['BONUS'] : 0;
                    $rowNum++;
                }

                $styleArray = $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ]
                    ],
                    'font' => [
                        'bold' => true,
                        'name' => 'Times New Roman',
                        'size' => 11
                    ],
                    'alignment' => [
                        'horizontal' => 'left'
                    ]
                ];

                $curSheet->setCellValue('A' . $rowNum, 'Итого (до вычета НДФЛ):');
                $curSheet->getStyle('A' . $rowNum)->applyFromArray($styleArray);
                $curSheet->mergeCells('A' . $rowNum . ':F' . $rowNum);

                $curSheet->setCellValue('G' . $rowNum, $sum);

                $styleArray['alignment']['horizontal'] = 'center';
                $curSheet->getStyle('G' . $rowNum)->applyFromArray($styleArray);

                $rowNum++;
            }
        }

        $bytes = bin2hex(random_bytes(8));

        $newfile = $_SERVER['DOCUMENT_ROOT'] . '/upload/bonus/Bonus_' . $bytes . '.xlsx';

        $writer = new Xlsx($spread);
        $writer->save($newfile);

        return '/upload/bonus/Bonus_' . $bytes . '.xlsx';
    }
}