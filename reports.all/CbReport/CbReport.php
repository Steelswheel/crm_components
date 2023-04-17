<?php
namespace Components\Vaganov\ReportsAll\CbReport;

use Bitrix\Crm\DealTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Vaganov\Helper;

Loader::IncludeModule('crm');

class CbReport extends \CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function getData($startDate, $endDate, $pageNumber, $limit)
    {
        $startDate = (new \DateTime($startDate))->format('d.m.Y');
        $endDate = (new \DateTime($endDate))->format('d.m.Y');

        $allStages = \CCrmDeal::GetStageNames(8);

        $stages = array_filter($allStages, function ($key) {
            return ($key !== 'C8:LOSE');
        }, ARRAY_FILTER_USE_KEY);

        $arFilter = [
            'CATEGORY_ID' => 8,
            'STAGE_ID' => array_keys($stages),
            [
                'LOGIC' => 'OR',
                '><UF_CRM_1567499237' => [$startDate, $endDate],
                '><UF_CRM_1567499436' => [$startDate, $endDate]
            ]
        ];

        $nav_query = new Query(DealTable::getEntity());

        $nav_query
            ->setOrder([
                'DATE_CREATE' => 'DESC'
            ])
            ->setSelect([
                'ID',
            ])
            ->setFilter($arFilter)
            ->exec();

        $page = 1;

        if (!empty($pageNumber)) {
            $page = $pageNumber;
        }

        $dealsNumbers = [];
        $dealIDs = [];

        $count = 1;

        foreach ($nav_query->fetchAll() as $deal) {
            $dealsNumbers[$deal['ID']] = $count;
            $dealIDs[] = (int)$deal['ID'];
            $count++;
        }

        $offset = ($page - 1) * $limit;
        $pages_count = (int)$count;

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
            ->setLimit($limit)
            ->setOffset($offset)
            ->setFilter($arFilter)
            ->exec();

        $arDeals = [];

        $sellerFio = [];
        $sellerPassport = [];
        $sellerAddress = [];

        $part_zaim = [];

        foreach ($query->fetchAll() as $deal) {
            $deal['NUMBER'] = $dealsNumbers[$deal['ID']];

            $part_zaim[] = $deal[PART_ZAIM];

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

            $data = json_decode($deal['UF_OWNERSHIP_DATE'], 1);

            if (!empty($data)) {
                $arr = [];

                foreach ($data as $item) {
                    $arr[] = $item['fio'] . ' ' . $item['date'];
                }

                $deal['UF_OWNERSHIP_DATE'] = implode(",\n", $arr);
            }

            unset($data);

            $data = json_decode($deal['UF_SELLER_REPRESENTATIVE_FIO'], 1);

            if (!empty($data)) {
                $arr = [];

                foreach ($data as $item) {
                    $arr[] = $item['value'];
                }

                $deal['UF_SELLER_REPRESENTATIVE_FIO'] = implode(",\n", $arr);
            }

            unset($data);

            $data = json_decode($deal['UF_BUYER_REPRESENTATIVE_FIO'], 1);

            if (!empty($data)) {
                $arr = [];

                foreach ($data as $item) {
                    $arr[] = $item['value'];
                }

                $deal['UF_BUYER_REPRESENTATIVE_FIO'] = implode(",\n", $arr);
            }

            unset($data);

            $data = json_decode($deal['UF_HOMEOWNERS_FIO'], 1);

            if (!empty($data)) {
                $arr = [];

                foreach ($data as $item) {
                    $arr[] = $item['value'];
                }

                $deal['UF_HOMEOWNERS_FIO'] = implode(",\n", $arr);
            }

            unset($data);

            if (!empty($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES'])) {
                $deal['FINAL_DATE'] = (new \DateTime($deal['UF_DATE_OF_DISTRIBUTION_OF_SHARES']))->format('d.m.Y');
            } else if (!empty($deal['UF_CRM_1567573226'])) {
                $deal['FINAL_DATE'] = (new \DateTime($deal['UF_CRM_1567573226']))->modify('+180 days')->format('d.m.Y');
            } else {
                $deal['FINAL_DATE'] = '-';
            }

            $arDeals[] = $deal;
        }

        $partners = $this->getPartnersInfo($part_zaim);

        $arResult = [
            'PAGES_COUNT' => $pages_count,
            'ROWS' => [],
            'IDs' => $dealIDs
        ];

        $managers = \Vaganov\Helper::getUsers(241, false);

        foreach ($arDeals as $deal) {
            $partner = $partners[$deal[PART_ZAIM]];

            $deal['UF_BORROWER_PASSPORT_SER'] = str_replace(' ', '', $deal['UF_BORROWER_PASSPORT_SER']);

            $deal['SIGNER_FIO'] = trim($partner['CONTACT_LAST_NAME']) . ' ' . trim($partner['CONTACT_NAME']) . ' ' . trim($partner['CONTACT_SECOND_NAME']);
            $deal['NFO_FIO'] = '-';
            $deal['NON_NFO_FIO'] = trim($partner['CONTACT_LAST_NAME']) . ' ' . trim($partner['CONTACT_NAME']) . ' ' . trim($partner['CONTACT_SECOND_NAME']);

            $contributor = $deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']  . ' ' . $deal['CONTACT_SECOND_NAME'];
            $passport = 'Серия: ' . $deal['UF_BORROWER_PASSPORT_SER'] . '; Номер: ' . $deal['UF_BORROWER_PASSPORT_NUMBER'] . '; Кем выдан: ' . $deal['UF_BORROWER_KEM_VIDAN'] . '; Дата: ' . $deal['UF_BORROWER_DATE'] . '; Код: ' . $deal['UF_BORROWER_KOD'] . '; Место рождения: ' . $deal['UF_BORROWER_BIRTH_PLACE'];

            $arResult['ROWS'][] = [
                'KPK_WORK' => 739,
                'NUMBER' => $deal['NUMBER'],
                'MANAGER' => $managers[$deal['ASSIGNED_BY_ID']],
                'ID' => $deal['ID'],
                'CB_LIST_CONTRIBUTOR' => $contributor,
                'CB_LIST_PASSPORT_SER' => $deal['UF_BORROWER_PASSPORT_SER'],
                'CB_LIST_PASSPORT_NUMBER' => $deal['UF_BORROWER_PASSPORT_NUMBER'],
                'CB_LIST_REGISTER_PLACE' => $deal['REGISTER_PLACE'],
                'CB_LIST_FACT_PLACE' => $deal['FACT_PLACE'],
                'CB_LIST_KPK_INTRO_DATE' => (new \DateTime($deal['UF_ENTER_KPK_DATE']))->format('d.m.Y'),
                'CB_LIST_KPK_EXIT_DATE' => (new \DateTime($deal['UF_EXIT_KPK_DATE']))->format('d.m.Y'),
                'CB_LIST_PDF_MONEY_DATE' => (new \DateTime($deal['UF_CRM_1567499237']))->format('d.m.Y'),
                'CB_LIST_PDF_MONEY_SUM' => $deal['UF_CRM_1567499259'],
                'CB_LIST_PFR_DEPART' => $deal['UF_CASH_INFLOW_MSK_DEPART'],
                'CB_LIST_PFR_INN' => $deal['UF_CASH_INFLOW_MSK_INN'],
                'CB_LIST_CONTRIBUTOR_PAY_DATE' => '',
                'CB_LIST_CONTRIBUTOR_PAY_SUM_SPOT' => '',
                'CB_LIST_CONTRIBUTOR_PAY_SUM_CASHLESS' => '',
                'CB_LIST_LOAN_CONTRACT_DATE' => '',
                'CB_LIST_LOAN_CONTRACT_SUM' => '',
                'CB_LIST_LOAN_CONTRACT_RATE' => '',
                'CB_LIST_LOAN_CONTRACT_PERIOD' => '',
                'CB_LIST_LOAN_CONTRACT_TYPE' => '',
                'CB_LIST_LOAN_ISSUANCE_DATE' => '',
                'CB_LIST_LOAN_ISSUANCE_SUM' => '',
                'CB_LIST_CONTRIBUTOR_PERCENT_DATE' => '',
                'CB_LIST_CONTRIBUTOR_PERCENT_SUM_CASHLESS' => '',
                'CB_LIST_CONTRIBUTOR_PERCENT_SUM_SPOT' => '',
                'CB_LIST_DKP_NUMBER' => $deal['UF_DKP_NUMBER'],
                'CB_LIST_DKP_DATE' => (new \DateTime($deal['UF_DKP_DATE']))->format('d.m.Y'),
                'CB_LIST_SELLER_NAME' => implode(",\n", $sellerFio[$deal['ID']]),
                'CB_LIST_SELLER_PASSPORT' => implode(",\n", $sellerPassport[$deal['ID']]),
                'CB_LIST_SELLER_RESISTER_PLACE' => implode(",\n", $sellerAddress[$deal['ID']]),
                'CB_LIST_SELLER_FACT_ADDRESS' => implode(",\n", $sellerAddress[$deal['ID']]),
                'CB_LIST_SELLER_ENTER_KPK_DATE' => 'Нет',
                'CB_LIST_SELLER_EXIT_KPK_DATE' => 'Нет',
                'CB_LIST_SELLER_OWNERSHIP_DATE' => $deal['UF_OWNERSHIP_DATE'],
                'CB_LIST_ESTATE_ADDRESS' => $deal['UF_CRM_1518964843'],
                'CB_LIST_DEAL_SUM' => $deal['UF_DKP_SUMMA'],
                'CB_LIST_SHARE' => $deal['UF_SHARE_OF_ACQUIRED_PROPERTY'],
                'CB_LIST_KADAS_NUMBER' => $deal['UF_DKP_CADAS_NUMBER'],
                'CB_LIST_KADAS_COST' => $deal['UF_DKP_CADAS_PRICE'],
                'CB_LIST_SQUARE' => $deal['UF_DKP_OBJECT_AREA'],
                'CB_LIST_SELLER_REPRESENTATIVE_FIO' => $deal['UF_SELLER_REPRESENTATIVE_FIO'],
                'CB_LIST_BUYER_REPRESENTATIVE_FIO' => $deal['UF_BUYER_REPRESENTATIVE_FIO'],
                'CB_LIST_DOC_NAME' => 'ДКП',
                'CB_LIST_TRANSFER_OF_OWNERSHIP_DATE' => $deal['UF_DATE_OF_TRANSFER_OF_OWNERSHIP'],
                'CB_LIST_HOMEOWNERS_FIO' => $deal['UF_HOMEOWNERS_FIO'],
                'CB_LIST_FINAL_DATE' => $deal['FINAL_DATE'],
                'CB_LIST_GROUND_DOC' => '',
                'CB_LIST_GROUND_ADDRESS' => '',
                'CB_LIST_GROUND_KADAS_NUMBER' => '',
                'CB_LIST_SIGNER_FIO' => $deal['SIGNER_FIO'],
                'CB_LIST_NFO_FIO' => $deal['NFO_FIO'],
                'CB_LIST_NON_NFO_FIO' => $deal['NON_NFO_FIO'],
                'CB_LIST_NFO_COMISSION_DATE' => '',
                'CB_LIST_NFO_COMISSION_RESULT' => '',
                'CB_LIST_FAMILY_SHARE' => $deal['UF_FAMILY_SHARES'],
                'CB_LIST_KPK_COMISSION' => '',
                'CB_LIST_REMOVAL_OF_ENCUMBRANCE_DATE' => empty($deal['UF_CRM_1567573226']) ? '' : (new \DateTime($deal['UF_CRM_1567573226']))->format('d.m.Y'),
                'CB_LIST_6001_CODE' => '',
                'CB_LIST_DUBIOUS_OPERATION' => '',
                'CB_LIST_SEND_MESSAGES_DATE' => '',
                'CB_LIST_OTHER_INFO' => '',
                'CB_LIST_3_NUMBER' => $deal['NUMBER'],
                'CB_LIST_3_PASSPORT' => $passport,
                'CB_LIST_3_INN' => $deal['CONTACT_INN_NUMBER'],
                'CB_LIST_3_REGISTER_PLACE' => $deal['REGISTER_PLACE'],
                'CB_LIST_3_CONTRACT_DATE' => '',
                'CB_LIST_3_IDENTIFY_DATE' => '',
                'CB_LIST_3_OPERATIONS_START_DATE' => '',
                'CB_LIST_3_RETRY_IDENTIFICATION_DATE' => '',
                'CB_LIST_3_RISK_LEVEL' => '',
                'CB_LIST_3_TARGET' => '',
                'CB_LIST_3_FACT_OPERATIONS' => '',
                'CB_LIST_3_REVISION_RESULT_EXTR' => '',
                'CB_LIST_3_REVISION_RESULT_PASS' => '',
                'CB_LIST_3_OTHER_INFO' => ''
            ];
        }

        return $arResult;
    }

    public function getPartnersInfo($ids)
    {
        $result = [];

        $db_res = \CCrmDeal::GetListEx(
            [],
            [
                'CATEGORY_ID' => 10,
                'CONTACT_ID' => $ids
            ],
            false,
            false,
            [
                'ID',
                'CONTACT_NAME',
                'CONTACT_LAST_NAME',
                'CONTACT_SECOND_NAME',
                'CONTACT_ID',
                PARTNER_STATUS,
                'UF_PARTNER_REGISTER_ADDRESS_DADATA',
                IS_AGENT,
                MANAGING_PART_ID
            ],
            []
        );

        while ($partner = $db_res->Fetch()) {
            $result[$partner['CONTACT_ID']] = $partner;
        }

        return $result;
    }

    public function checkPrepare()
    {
        \Vaganov\Helper::includeHlTable('cb_progress');

        $allRows = \CbProgressTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => ['*', 'UF_*']
        ])->fetchAll();

        if (!empty($allRows)) {
            $one_percent = 100 / count($allRows);

            $rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['TABLE_NAME' => 'cb_request_ids']])->fetch();

            $idStageIdSettings = $rsData['ID'];
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($idStageIdSettings)->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $done_deals = $entity_data_class::getList([
                'select' => ['ID']
            ])->fetchAll();

            return round(count($done_deals) * $one_percent);
        } else {
            return 0;
        }
    }

    public function checkCreated()
    {
        Helper::includeHlTable('cb_report_start');

        $start_download = \CbReportStartTable::getList([
            'select' => ['ID', 'UF_START']
        ])->fetch();

        return $start_download['UF_START'] === '0';
    }

    private function getDeals($ids) {
        $query = new Query(DealTable::getEntity());

        $query
            ->setOrder(['ID' => 'ASC'])
            ->registerRuntimeField('CONTACT', [
                'data_type' => 'Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.CONTACT_ID' => 'ref.ID',
                ],
            ])
            ->setSelect([
                'ID',
                'ASSIGNED_BY_ID',
                'NAME' => 'CONTACT.NAME',
                'SECOND_NAME' => 'CONTACT.SECOND_NAME',
                'LAST_NAME' => 'CONTACT.LAST_NAME',
                'LOAN_PROGRAM' => LOAN_PROGRAM,
                'APPLICATION_TO_JOIN_THE_KPK_FILE' => 'UF_CRM_1505362823',
                'ANKETA_ZAEM' => 'UF_CRM_1518964170',
                'DZ_FILE' => 'UF_CRM_1518958923',
                'PAYMENT_SCHEDILE' => 'UF_CRM_1571981836',
                'SUPPLEMENTARY_AGREEMENT_FILE' => 'UF_CRM_1518959163',
                'PROPERTY_INSPECTION_ACT' => 'UF_CRM_1571981704',
                'ASSESSMENT' => 'UF_CRM_1586858550',
                'OBLIGATION_TO_BUILD_SCAN' => 'UF_CRM_1529317417',
                'PVK' => 'UF_CRM_1507264529',
                'RELATIVE_EXPLAIN' => 'UF_CRM_1571981119',
                'CERTIFICATE_OF_MARRIAGE' => 'CONTACT.UF_CRM_1505378206',
                'CERTIFICATE_MK' => 'UF_CRM_1518947606',
                'PASSPORT_SCAN' => 'CONTACT.UF_CRM_1509368723',
                'PHOTO_OBJECT' => 'UF_CRM_1505362453',
                'REGISTERED_DKP' => 'UF_CRM_1518966873',
                'EXPERT_EGRN_REGISTERED' => 'UF_CRM_1558943852469',
                'RECEIPT_OF_SELLER' => 'UF_CRM_1535454008',
                'UF_1T_SELLER_TRANSPORT_AMOUNT',
                'UF_2T_SELLER_TRANSPORT_AMOUNT',
                'UF_3T_SELLER_TRANSPORT_AMOUNT',
                'UF_4T_SELLER_TRANSPORT_AMOUNT',
                'BUILDING_PERMIT' => 'UF_CRM_1518949828',
                'DOCUMENTS_FOR_LAND' => 'UF_CRM_1537258585',
                'BUILD_CONFIRM_DOC' => 'UF_CRM_1554790153130',
                'UF_1T_SCAN_OF_PAYMENT',
                'UF_2T_SCAN_OF_PAYMENT',
                'UF_3T_SCAN_OF_PAYMENT',
                'UF_4T_SCAN_OF_PAYMENT',
                'BIRTH_CERTIFICATE_OF_CHILDREN' => 'CONTACT.UF_CRM_1505306805',
                'UF_DOC_IMPROVEMENT'
            ])
            ->setFilter([
                'ID' => $ids
            ])
            ->exec();

        $res = $query->fetchAll();

        $deals = [];

        foreach ($res as $deal) {
            $deals[$deal['ID']] = $deal;
        }

        Helper::includeHlTable('money_orders');

        $allOrder = \MoneyOrdersTable::getList([
            'select' => [
                'ID',
                'UF_PAYMENT_ORDER_PDF',
                'UF_DEAL_ID'
            ],
            'order' => ['ID' => 'ASC'],
            'filter' => ['UF_DEAL_ID' => $ids]
        ])->fetchAll();

        foreach ($allOrder as $key => $item) {
            if ($item['UF_PAYMENT_ORDER_PDF'][0]) {
                $deals[$item['UF_DEAL_ID']]['UF_PAYMENT_ORDER_PDF_SRC'][] = $item['UF_PAYMENT_ORDER_PDF'][0];
            }
        }

        return $deals;
    }

    public function stopLoading()
    {
        Helper::includeHlTable('cb_report_start');

        $start_download = \CbReportStartTable::getList([
            'select' => ['ID', 'UF_START']
        ])->fetch();

        \CbReportStartTable::update($start_download['ID'], ['UF_START' => '0']);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cb_lock/is_loading.txt', 'false');

        \Vaganov\Helper::includeHlTable('cb_request_ids');

        $done_deals = \CbRequestIdsTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => ['ID']
        ])->fetchAll();

        foreach ($done_deals as $row) {
            \CbRequestIdsTable::delete($row['ID']);
        }

        \Vaganov\Helper::includeHlTable('cb_progress');

        $allRows = \CbProgressTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => ['*', 'UF_*']
        ])->fetchAll();

        //Очищаем таблицу
        foreach ($allRows as $row) {
            \CbProgressTable::delete($row['ID']);
        }

        return false;
    }

    public function prepareArchive($ids)
    {
        \Vaganov\Helper::includeHlTable('cb_request_ids');

        $done_deals = \CbRequestIdsTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => ['ID']
        ])->fetchAll();

        foreach ($done_deals as $row) {
            \CbRequestIdsTable::delete($row['ID']);
        }

        \Vaganov\Helper::includeHlTable('cb_progress');

        $allRows = \CbProgressTable::getList([
            'order' => ['ID' => 'ASC'],
            'select' => ['*', 'UF_*']
        ])->fetchAll();

        //Очищаем таблицу
        foreach ($allRows as $row) {
            \CbProgressTable::delete($row['ID']);
        }

        $deals = $this->getDeals(json_decode($ids));

        foreach ($deals as $deal) {
            $files = [];

            foreach ($deal as $key => $value) {
                if (in_array((int)$deal['LOAN_PROGRAM'], [2, 3, 4])) {
                    unset(
                        $deal['UF_1T_SELLER_TRANSPORT_AMOUNT'],
                        $deal['UF_2T_SELLER_TRANSPORT_AMOUNT'],
                        $deal['UF_3T_SELLER_TRANSPORT_AMOUNT'],
                        $deal['UF_4T_SELLER_TRANSPORT_AMOUNT']
                    );
                }

                if (is_array($value) && !empty($value)) {
                    foreach ($value as $item) {
                        $file = \CFile::GetFileArray($item);

                        if (!empty($file)) {
                            $description = json_decode($file['DESCRIPTION']);

                            if (empty($description) || $description->del === 0) {
                                $files[] = (int)$file['ID'];
                            }
                        }
                    }
                }
            }

            \CbProgressTable::add([
                'UF_DATE' => (new \DateTime())->format('d.m.Y H:i:s'),
                'UF_ARCHIVE_NAME' => 'Отчет.zip',
                'UF_PERCENT' => 0,
                'UF_DEAL_ID' => (int)$deal['ID'],
                'UF_ALL_FILES' => json_encode($files),
                'UF_PROCESSED_FILES' => json_encode([])
            ]);
        }

        Helper::includeHlTable('cb_report_start');

        $start_download = \CbReportStartTable::getList([
            'select' => ['ID', 'UF_START']
        ])->fetch();

        \CbReportStartTable::update($start_download['ID'], ['UF_START' => '1']);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cb_lock/is_loading.txt', 'true');

        return file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cb_lock/is_loading.txt');
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}