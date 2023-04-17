<?php

namespace Components\Vaganov\MoneyOrders;

use Bitrix\Crm\ContactTable;
use Bitrix\Crm\DealTable;
use Bitrix\Disk\Volume\Module\Voximplant;
use Bitrix\Main\Loader;
use ClientBankExchange\Parser;
use Models\EdzTable;
use Vaganov\Helper;


class MoneyOrders
{
    public $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function run()
    {



        $docAll = $this->parser();

        $dealAndDoc = [];



        foreach ($docAll as $docItem) {

            $DZ = $this->findNumberDZ($docItem['PurposeOfPayment']);
            $DV = $this->findNumberDV($docItem['PurposeOfPayment']);
            $fioPurposeOfPayment = $this->findFioForString($docItem['PurposeOfPayment']);
            $fioPayer = $this->findFioForString($docItem['payer']);
            $fioRecipient = $this->findFioForString($docItem['recipient']);
            $crmAr = [];

            if ($docItem['dealId'] && $data = $this->findById($docItem['dealId'])){
                $crmAr = $data;
            }else if ($DZ && $data = $this->findDealDZ($DZ)) {
                $crmAr = $data;
            } elseif ($DV && $data = $this->findDealDV($DV)) {
                $crmAr = $data;
            } elseif ($fioPurposeOfPayment && $data = $this->findContactForFIO($fioPurposeOfPayment)){
                $crmAr = $data;
            } elseif ($fioPurposeOfPayment && $data = $this->findTreeFace($fioPurposeOfPayment)){
                $crmAr = $data;
            } elseif ($fioPayer && $data = $this->findContactForFIO($fioPayer)){
                $crmAr = $data;
            } elseif ($fioRecipient && $data = $this->findContactForFIO($fioRecipient)){
                $crmAr = $data;
            }

            foreach ($crmAr as $key => $itemCrmAr){
                $crmAr[$key]['setTranche'] = $this->findSetTranche($itemCrmAr,$docItem);
            }


            $dealAndDoc[] = array_merge(
                $docItem,
                [
                    'setTranche' => '',
                    'DZ' => $DZ,
                    'DV' => $DV,
                    'DEAL_ID' => '',
                    'crmAr' => $crmAr,
                    'workingMethod' => ''
                ],
            );

        }


        return [
            'docs' => $dealAndDoc,
            'stages' => Helper::stages(),
        ];
    }

    public function MoneyOrdersTableAdd($doc,$dateIn){

        \Vaganov\Helper::includeHlTable('money_orders');

        $inn = ($doc['payment'] === 'IN') ? $doc['recipientInn'] : $doc['payerInn'];

        $doc['docId'] = $kpk.$doc['number'].$doc['date'];

        $docData = \MoneyOrdersTable::getList([
            'select' => ['*'],
            'filter' => ['UF_DOC_ID' => $doc['docId']],
        ])->fetch();

        if ($docData){
            $doc['isSkip'] = $docData['UF_IS_SKIP'] === '1';
            $doc['handler'] = $docData['UF_HANDLER'];
            $doc['dateProcessing'] = $docData['UF_DATE_PROCESSING'];
            $doc['dealId'] = $docData['UF_DEAL_ID'];
            $doc['status'] = $docData['UF_STATUS'];
        }else{
            \MoneyOrdersTable::ADD([
                'UF_PAYMENT' => $doc['payment'],
                'UF_DOC_ID' => $doc['docId'],
                'UF_NUMBER' => $doc['number'],
                'UF_KPK' => $kpk,
                'UF_DATE_IN' => $dateIn,
                'UF_DATE_PROCESSING' => '',
                'UF_DEAL_ID' => '',
                'UF_DATE_DOC' => $doc['date'],
                'UF_SUM' => $doc['sum'],
                'UF_PAYER' => $doc['payer'],
                'UF_PAYER_INN' => $doc['payerInn'],
                'UF_PAYER_BANK' => $doc['payerBank'],
                'UF_RECIPIENT' => $doc['recipient'],
                'UF_RECIPIENT_INN' => $doc['recipientInn'],
                'UF_RECIPIENT_BANK' => $doc['recipientBank'],
                'UF_PURPOSE_OF_PAYMENT' => $doc['PurposeOfPayment'],
                'UF_TYPE_OF_PAYMENT' => $doc['typeOfPayment'],
            ]);
        }



        return $doc;

    }

    public function parser()
    {

        $doc = [];

        $dateIn = date('d.m.Y H:i:s');


        $lines = file($this->fileName);
        if(strripos($lines[0],';') > -1){

            foreach ($lines as $item){
                $itemUTF8 = iconv('windows-1251//IGNORE', 'UTF-8//IGNORE', $item);

                $itemAr = explode(';',$itemUTF8);
                if(count($itemAr) > 11){
                    $itemDoc = [
                        'payment' => 'IN',
                        'number' => $itemAr[4],
                        'date' => date('d.m.Y', strtotime($itemAr[0])),
                        'sum' => $itemAr[7],
                        'payer' => $itemAr[5],
                        'payerInn' => '-',
                        'payerBank' => '-',
                        'recipient' => '-',
                        'recipientInn' => '-',
                        'recipientBank' => '-',
                        'PurposeOfPayment' => $itemAr[6],
                        'typeOfPayment' => '-',
                        'dateProcessing' => '',
                        'status' => '',
                        'isSkip' => false,
                        'handler' => '',
                        'dealId' => false
                    ];

                    $doc[] = $this->MoneyOrdersTableAdd($itemDoc,$dateIn);;
                }

            }


        }else{
            $p = new Parser($this->fileName);

            foreach ($p->documents as $d) {

                $inn = $d->{'ПлательщикИНН'};
                $payment = $inn === '' ? 'OUT' : 'IN';
                $itemDoc = [
                    'payment' => $payment,
                    'number' => $d->{'Номер'},
                    'date' => date('d.m.Y', strtotime($d->{'Дата'})),
                    'sum' => $d->{'Сумма'},
                    'payer' => $d->{'Плательщик'},
                    'payerInn' => $d->{'ПлательщикИНН'},
                    'payerBank' => $d->{'ПлательщикБанк1'},

                    'recipient' => $d->{'Получатель'},
                    'recipientInn' => $d->{'ПолучательИНН'},
                    'recipientBank' => $d->{'ПолучательБанк1'},

                    'PurposeOfPayment' => $d->{'НазначениеПлатежа'},
                    'typeOfPayment' => $d->{'ВидОплаты'},
                    'dateProcessing' => '',
                    'status' => '',
                    'isSkip' => false,
                    'handler' => '',
                    'dealId' => false
                ];

                $doc[] = $this->MoneyOrdersTableAdd($itemDoc,$dateIn);

            }

        }



        return $doc;
    }

    public function num($num)
    {
        $value = str_replace(',', '.', $num);
        $value = preg_replace("/(\d+)\.0+/", '$1', $value);
        return (float)$value;
    }

    public function findNumberDZ($purposeOfPayment)
    {
        $re = '/((т\/|t\/|)(дзп|дзс|дзсб)\S{2,15})\s/m';
        $purposeOfPayment = mb_strtolower($purposeOfPayment);
        $purposeOfPayment = str_replace(" ", '', $purposeOfPayment);
        preg_match_all($re, $purposeOfPayment, $matches, PREG_SET_ORDER, 0);

        if ($matches) {
            return trim(mb_strtoupper($matches[0][0]));
        }
        return false;
    }

    public function findFioForString($str){
        $re = '/[а-яА-ЯёЁ]{3,} [а-яА-ЯёЁ]{3,} [а-яА-ЯёЁ]{3,}/u';
        $str = mb_strtolower($str);

        $deleteWords = ['рублей','паевой','взнос','выплата','обязательного','паевого','взноса',
            'предоставление','денежных','средств','договору','займа','денежных','средств','паевой','взнос','обязательный'];
        foreach ($deleteWords as $item){
            $str = str_replace($item, '',$str);
        }
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            return mb_strtoupper($matches[0][0]);
        }
        return false;
    }

    public function findNumberDV($purposeOfPayment)
    {
        $re = '/(т\/|)(дв)(\/т|)(| |-| - | -|- )(\d+)/m';
        $purposeOfPayment = mb_strtolower($purposeOfPayment);
        $purposeOfPayment = str_replace(" ", '', $purposeOfPayment);
        preg_match_all($re, $purposeOfPayment, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            return mb_strtoupper($matches[0][0]);
        }
        return false;
    }


    public function findDealDZ($DZ)
    {
        $filter = [
            'UF_NUMBER_DZ' => $DZ
        ];
        return $this->findDeal($filter, 'ДЗ');
    }

    public function findDealDV($DV)
    {
        $filter = [
            'NUMBER_DV' => $DV //
        ];
        return $this->findDeal($filter, 'ДВ');
    }

    public function findContactForFIO($fio)
    {
        $fioAr = explode(' ', $fio);
        $filter = [
            "LAST_NAME" => $fioAr[0],
            "NAME" => $fioAr[1],
            "SECOND_NAME" => $fioAr[2],
        ];
        return $this->findDeal($filter, 'ФИО');
    }

    public function findTreeFace($fio)
    {
        $fioAr = explode(' ', $fio);
        $filter = [
            "UF_GUARANTOR_LAST_NAME" => $fioAr[0],
            "UF_GUARANTOR_NAME" => $fioAr[1],
            "UF_GUARANTOR_SECOND_NAME" => $fioAr[2],
        ];
        return $this->findDeal($filter, 'ФИО 3е лицо');
    }

    public function findById($id)
    {
        $filter = ["ID" => $id];
        return $this->findDeal($filter, 'ИД');
    }


    public function findDeal($where, $findInfo)
    {

        $where['STAGE_ID'] = [
            STAGE_EDZ_2_EXECUTING, STAGE_EDZ_3_ANALYTICS, STAGE_EDZ_4_DECISION, STAGE_EDZ_5_KPK_ENTRY, STAGE_EDZ_6_ENRTY_CONTROL, STAGE_EDZ_7_CREDIT_DOCS_PREPARE, STAGE_EDZ_8_CREDIT_DOCS_SIGNING, STAGE_EDZ_9_SUBMISSION_TO_MFC, STAGE_EDZ_10_1T_PAYMENT, STAGE_EDZ_11_COMISSION_PAYMENT,
            STAGE_EDZ_12_EXIT_FROM_MFC, STAGE_EDZ_13_2T_PAYMENT, STAGE_EDZ_15_2T_SELLER_SCHEDULE, STAGE_EDZ_14_2T_ENROLLMENT, STAGE_EDZ_16_3T_PAYMENT, STAGE_EDZ_18_3T_SELLER_SCHEDULE, STAGE_EDZ_17_3T_ENROLLMENT, STAGE_EDZ_19_4T_PAYMENT, STAGE_EDZ_20_4T_ENROLLMENT, STAGE_EDZ_22_APPLICATION_IN_PFR,
            STAGE_EDZ_23_PENDING_LOAN_REPAYMENT, STAGE_EDZ_24_PFR_REFUSAL, STAGE_EDZ_25_CALCULATION_RKO, STAGE_EDZ_26_EXCEPTION_STATEMENTS, STAGE_EDZ_27_PAY_PAYMENT, STAGE_EDZ_28_SELLER_SCHEDULE, STAGE_EDZ_29_ORIGINALS, STAGE_EDZ_30_WITHDRAWAL_OF_BAIL,
        ];
        \Bitrix\Main\Loader::includeModule('crm');
        // $dealDataRes = \CCrmDeal::GetListEx([], $where, false, false,  ['*','UF_*'], []);

        $data = \Models\EdzTable::getListAll([
            'select' => ['ID','*', 'UF_*', 'CONTACT.*'],
            'filter' => $where,
        ]);

        $dealExport = [];
        foreach ($data as $dealData) {
            $dealExport[] = [
                'DEAL_ID' => $dealData['ID'],
                'STAGE_ID' => $dealData['STAGE_ID'],
                'FIO' => $dealData['LAST_NAME'] . ' ' . $dealData['NAME'] . ' ' . $dealData['SECOND_NAME'],
                'FIO_GUARANTOR' => $dealData['UF_GUARANTOR_LAST_NAME']
                    ? $dealData['UF_GUARANTOR_LAST_NAME'] . ' ' . $dealData['UF_GUARANTOR_NAME'] . ' ' . $dealData['UF_GUARANTOR_SECOND_NAME']
                    : false,
                'findInfo' => $findInfo,
                'T1' => $this->num($dealData['TRANSFER_OF_TRANCHE_1']),
                'T2' => $this->num($dealData['TRANSFER_OF_TRANCHE_2']),
                'T3' => $this->num($dealData['TRANSFER_OF_TRANCHE_3']),
                'T4' => $this->num($dealData['TRANSFER_OF_TRANCHE_4']),
                'FTD1' => $this->num($dealData['UF_TRANCHE_1_DATA']),
                'FT1' => $this->num($dealData['UF_TRANCHE_1_SUM']),
                'FTD2' => $this->num($dealData['UF_TRANCHE_2_DATA']),
                'FT2' => $this->num($dealData['UF_TRANCHE_2_SUM']),
                'FTD3' => $this->num($dealData['UF_TRANCHE_3_DATA']),
                'FT3' => $this->num($dealData['UF_TRANCHE_3_SUM']),
                'FTD4' => $this->num($dealData['UF_TRANCHE_4_DATA']),
                'FT4' => $this->num($dealData['UF_TRANCHE_4_SUM']),

                "UF_CHECK_PAYMENT_ENTER_KPK_SUM" => $this->num($dealData['UF_CHECK_PAYMENT_ENTER_KPK_SUM']),
                "COMISSION_SUM" => $this->num($dealData['COMISSION_SUM']),
                'enrolledSum' => $this->num($dealData['CONTRIBUTIONS_1_SUM']) +
                    $this->num($dealData['CONTRIBUTIONS_2_SUM']) +
                    $this->num($dealData['CONTRIBUTIONS_3_SUM']),
                'FFT1' => $this->num($dealData['CONTRIBUTIONS_1_SUM']),
                'FFT2' => $this->num($dealData['CONTRIBUTIONS_2_SUM']),
                'FFT3' => $this->num($dealData['CONTRIBUTIONS_3_SUM']),

                'PAYMENT_PFR_SUM' => $this->num($dealData['PAYMENT_PFR_SUM']),


                'AMOUNT_DZ' => $this->num($dealData['AMOUNT_DZ']),
                'DOU_SUMM' => $this->num($dealData['DOU_SUMM']),
                'DOU_SUM_FACT' => $this->num($dealData['DOU_SUM_FACT']),



                'TRANSACTION_COMMISION' => $this->num($dealData['TRANSACTION_COMMISION']),
                'REFOUND_CONTRIB_SUM' => $this->num($dealData['REFOUND_CONTRIB_SUM']), // ВЫПЛАТА ВКЛАДА И ПАЯ
                'UF_SUMM_ADD_COSTS' => $this->num($dealData['UF_SUMM_ADD_COSTS']),
                'BONUS_PARTY_1_TRANCHE' => $this->num($dealData['BONUS_PARTY_1_TRANCHE']),
                'BONUS_PARTY' => $this->num($dealData['BONUS_PARTY']),
                'KPK_WORK' => $dealData['KPK_WORK'],



            ];
        }

        return $dealExport;
    }

    public function findSetTranche($deal, $doc)
    {
        $sumStr = (float)$doc['sum'];

        if ($doc['payment'] === 'OUT'){

            if ($sumStr === $deal['T1'] && !$deal['FT1']) {
                return 'T1';
            } elseif ($sumStr === $deal['T2'] && !$deal['FT2']) {
                return 'T2';
            } elseif ($sumStr === $deal['T3'] && !$deal['FT3']) {
                return 'T3';
            } elseif ($sumStr === $deal['T4'] && !$deal['FT4']) {
                return 'T4';
            } elseif (
                ($deal['T1'] && $deal['FT1']) &&
                ($deal['T2'] && $deal['FT2']) &&
                ($deal['T3'] && $deal['FT3']) &&
                ($deal['T4'] && $deal['FT4']) &&
                $deal['T2'] > 0 &&
                $sumStr >= $deal['T1'] &&
                !$deal['REFOUND_CONTRIB_SUM']
                /*$docItem['T2'] > 0 &&
                ($sumStr >= ($docItem['enrolledSum']) && !$docItem['REFOUND_CONTRIB_SUM']) &&
                ($docItem['enrolledSum']) > 0*/
            ) {
                return 'V';
            }
        }else{
            if ($sumStr < 500 && !$deal['UF_CHECK_PAYMENT_ENTER_KPK_SUM']) {
                return 'in_entranceKpk';
            }else if (!$deal['COMISSION_SUM']){
                return 'in_commission';
            }else if ($deal['T2'] && !$deal['FFT1']){
                return 'in_ff1';
            }else if ($deal['T3'] && !$deal['FFT2']){
                return 'in_ff2';
            }else if ($deal['T4'] && !$deal['FFT3']){
                return 'in_ff3';
            }else if (!$deal['PAYMENT_PFR_SUM']){
                return 'in_pfr';
            }
        }




        return '';
    }


    public static function setHandlerDoc($docId, $dealId, $setTranche){

        \Vaganov\Helper::includeHlTable('money_orders');
        $docData = \MoneyOrdersTable::getList([
            'select' => ['*'],
            'filter' => ['UF_DOC_ID' => $docId],
        ])->fetch();

        \MoneyOrdersTable::update($docData['ID'],[
            'UF_DEAL_ID' => $dealId,
            'UF_HANDLER' => $setTranche,
            'UF_DATE_PROCESSING' => date('d.m.Y H:i:s'),
            'UF_STATUS' => 'success',
        ]);
    }

    public static function setTranche($docId, $dealId, $date, $sum, $setTranche)
    {




        if ($setTranche === 'T1') {
            $r = EdzTable::update($dealId, [
                'UF_TRANCHE_1_SUM' => $sum,
                'UF_TRANCHE_1_DATA' => $date,
            ]);
        } elseif ($setTranche === 'T2') {
            $r = EdzTable::update($dealId, [
                'UF_TRANCHE_2_SUM' => $sum,
                'UF_TRANCHE_2_DATA' => $date,
            ]);
        } elseif ($setTranche === 'T3') {
            $r = EdzTable::update($dealId, [
                'UF_TRANCHE_3_SUM' => $sum,
                'UF_TRANCHE_3_DATA' => $date,
            ]);
        } elseif ($setTranche === 'T4') {
            $r = EdzTable::update($dealId, [
                'UF_TRANCHE_4_SUM' => $sum,
                'UF_TRANCHE_4_DATA' => $date,
            ], 1);
        } elseif ($setTranche === 'V') {
            Loader::includeModule('crm');
            $dataDeal = EdzTable::getListAll([
                'select' => ['*', 'CONTRIBUTIONS_1_SUM', 'CONTRIBUTIONS_2_SUM', 'CONTRIBUTIONS_3_SUM'],
                'filter' => ['ID' => $dealId]
            ])[0];

            $enrolledSum = $dataDeal['CONTRIBUTIONS_1_SUM'] + $dataDeal['CONTRIBUTIONS_2_SUM'] + $dataDeal['CONTRIBUTIONS_3_SUM'];
            $r = EdzTable::update($dealId, [
                'REFOUND_CONTRIB_SUM' => $enrolledSum,
                'REFOUND_CONTRIB_DATA' => $date,
            ]);


        }elseif ($setTranche === 'in_entranceKpk') {
            $r = EdzTable::update($dealId, [
                'UF_CHECK_PAYMENT_ENTER_KPK_SUM' => $sum,
                'UF_CHECK_PAYMENT_ENTER_KPK_DATE' => $date,
            ]);
        }elseif ($setTranche === 'in_commission') {


            $dataDeal = EdzTable::getListAll([
                'select' => ['*', 'AMOUNT_DZ', 'DOU_SUMM'],
                'filter' => ['ID' => $dealId]
            ])[0];

            $r = EdzTable::update($dealId, [
                'COMISSION_SUM' => $dataDeal['AMOUNT_DZ'],
                'DOU_SUM_FACT' => $dataDeal['DOU_SUMM'],
                'COMISSION_DATA' => $date,
                'DOU_DATA_FACT' => $date,
            ]);
        }elseif ($setTranche === 'in_ff1') {
            $r = EdzTable::update($dealId, [
                'CONTRIBUTIONS_1_SUM' => $sum,
                'CONTRIBUTIONS_1_DATA' => $date,
            ]);
        }elseif ($setTranche === 'in_ff2') {
            $r = EdzTable::update($dealId, [
                'CONTRIBUTIONS_2_SUM' => $sum,
                'CONTRIBUTIONS_2_DATA' => $date,
            ]);
        }elseif ($setTranche === 'in_ff3') {
            $r = EdzTable::update($dealId, [
                'CONTRIBUTIONS_3_SUM' => $sum,
                'CONTRIBUTIONS_3_DATA' => $date,
            ]);
        }elseif ($setTranche === 'in_pfr') {
            $r = EdzTable::update($dealId, [
                'PAYMENT_PFR_SUM' => $sum,
                'PAYMENT_PFR_DATA' => $date,
            ]);
        }

        (new \Components\Vaganov\EdzShow\AutoStages($dealId,[]))->run();

        if ($r){
            self::setHandlerDoc($docId, $dealId, $setTranche);
            return ['crm' => (new MoneyOrders(''))->findById($dealId)];
        }else{
            return ['crm' => [[]]];
        }


    }

    public static function onSkipDocument($docId,$isSkip){
        \Vaganov\Helper::includeHlTable('money_orders');
        $docData = \MoneyOrdersTable::getList([
            'select' => ['*'],
            'filter' => ['UF_DOC_ID' => $docId],
        ])->fetch();

        $r = \MoneyOrdersTable::update($docData['ID'],[
            'UF_IS_SKIP' => $isSkip,
        ]);

        return !!$r;
    }

}