<?php
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;

Loader::IncludeModule('crm');

ini_set('memory_limit', '768M');

// Отдаёт файл пользователю
function giveFile($filepath = '')
{
    if (!file_exists($filepath)) {
        return false;
    }

    $fileinfo = CFile::MakeFileArray($filepath);

    if (empty($fileinfo)) {
        return false;
    }

    header('Content-Description: File Transfer');
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileinfo['name'] . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $fileinfo['size']);
    readfile($filepath);

    return true;
}

$arColumnDescript = [
    'ID' => ['col' => 'A', 'type' => 'int'],
    'ASSIGNED' => ['col' => 'B', 'type' => 'assigned'],
    PART_ZAIM => ['col' => 'C', 'type' => 'part'],
    'STATUS' => ['col' => 'E', 'type' => 'status'],
    DATE_DZ => ['col' => 'F', 'type' => 'date'],
    NUMBER_DZ => ['col' => 'G', 'type' => 'int'],
    'FIO_CONTACT' => ['col' => 'I', 'type' => 'str'],
    LOAN_PROGRAM => ['col' => 'J', 'type' => 'int'],
    SUMMA => ['col' => 'K', 'type' => 'summ'],
    TRANSFER_OF_TRANCHE_1 => ['col' => 'Q', 'type' => 'summ'],
    TRANSFER_OF_TRANCHE_2 => ['col' => 'R', 'type' => 'summ'],
    TRANSFER_OF_TRANCHE_3 => ['col' => 'S', 'type' => 'summ'],
    TRANSFER_OF_TRANCHE_4 => ['col' => 'T', 'type' => 'summ'],
    'UF_TRANCHE_1_DATA' => ['col' => 'U', 'type' => 'date'],
    'UF_TRANCHE_1_SUM' => ['col' => 'V', 'type' => 'summ'],
    'UF_TRANCHE_2_DATA' => ['col' => 'W', 'type' => 'date'],
    'UF_TRANCHE_2_SUM' => ['col' => 'X', 'type' => 'summ'],
    'UF_TRANCHE_3_DATA' => ['col' => 'Y', 'type' => 'date'],
    'UF_TRANCHE_3_SUM' => ['col' => 'Z', 'type' => 'summ'],
    'UF_TRANCHE_4_DATA' => ['col' => 'AA', 'type' => 'date'],
    'UF_TRANCHE_4_SUM' => ['col' => 'AB', 'type' => 'summ'],
    BONUS_PARTY => ['col' => 'AD', 'type' => 'summ'],
    TRANSACTION_COMMISION => ['col' => 'AE', 'type' => 'summ'],
    AMOUNT_DZ => ['col' => 'AF', 'type' => 'summ'],
    COMISSION_DATA => ['col' => 'AG', 'type' => 'date'],
    COMISSION_SUM => ['col' => 'AH', 'type' => 'summ'],
    MEMBERSHIP_FEE => ['col' => 'AI', 'type' => 'summ'],
    MEMBERSHIP_FEE_DATA => ['col' => 'AJ', 'type' => 'date'],
    MEMBERSHIP_FEE_SUM => ['col' => 'AK', 'type' => 'summ'],
    DOU_SUMM => ['col' => 'AL', 'type' => 'summ'],
    DOU_DATA_FACT => ['col' => 'AM', 'type' => 'date'],
    DOU_SUM_FACT => ['col' => 'AN', 'type' => 'summ'],
    AMOUNT_DV => ['col' => 'AQ', 'type' => 'summ'],
    AMOUNT_OF_CONTRIBUTION => ['col' => 'AR', 'type' => 'summ'],
    CONTRIBUTIONS_1_DATA => ['col' => 'AT', 'type' => 'date'],
    CONTRIBUTIONS_1_SUM => ['col' => 'AU', 'type' => 'summ'],
    CONTRIBUTIONS_2_DATA => ['col' => 'AV', 'type' => 'date'],
    CONTRIBUTIONS_2_SUM => ['col' => 'AW', 'type' => 'summ'],
    CONTRIBUTIONS_3_DATA => ['col' => 'AX', 'type' => 'date'],
    CONTRIBUTIONS_3_SUM => ['col' => 'AY', 'type' => 'summ'],
    CONTRIBUTIONS_4_DATA => ['col' => 'AX', 'type' => 'date'],
    CONTRIBUTIONS_4_SUM => ['col' => 'AY', 'type' => 'summ'],
    'DATE_PFR_PLAN' => ['col' => 'BB', 'type' => 'date'],
    DATE_PFR_SEND => ['col' => 'BC', 'type' => 'date'],
    PAYMENT_PFR_DATA => ['col' => 'BF', 'type' => 'date'],
    PAYMENT_PFR_SUM => ['col' => 'BG', 'type' => 'summ'],
    PAYMENT_REGION_DATA => ['col' => 'BI', 'type' => 'date'],
    PAYMENT_REGION_SUM => ['col' => 'BJ', 'type' => 'summ'],
    DEBT_RESTRICT_DATA => ['col' => 'BK', 'type' => 'date'],
    DEBT_RESTRICT_SUM => ['col' => 'BL', 'type' => 'summ'],
    REFOUND_CONTRIB_DATA => ['col' => 'BN', 'type' => 'date'],
    REFOUND_CONTRIB_SUM => ['col' => 'BO', 'type' => 'summ'],
    LAST_SETTLEMENT_DATA => ['col' => 'BP', 'type' => 'date'],
    DOC_SCAN_CRM_DATA => ['col' => 'BR', 'type' => 'date'],
    DOC_ORIGINAL_DATA => ['col' => 'BT', 'type' => 'date'],
    DEPOSIT_WITHDRAW_DATA => ['col' => 'BV', 'type' => 'date'],
    DEAL_CLOSE_DATE => ['col' => 'BW', 'type' => 'date']
];

$template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/zaim_template.xlsx';
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$sharedData = new \PhpOffice\PhpSpreadsheet\Shared\Date();

$spread = $reader->load($template);

$worksheet = $spread->getSheetByName('');

$arFilter = [
    'CATEGORY_ID' => '8',
];

if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $value) {
        if (in_array($key, ['MARKER', 'PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND']))
            continue;

        $arr = explode(',', $value);
        $arFilter[$key] = (count($arr) > 1) ? $arr : $value;
    }
}

$arFilter[] = [
    'LOGIC' => 'OR',
    '>UF_TRANCHE_1_SUM' => 0,
    '>UF_TRANCHE_2_SUM' => 0,
    '>UF_TRANCHE_3_SUM' => 0,
    '>UF_TRANCHE_4_SUM' => 0
];

$rowAll= 6;

$arDeals = CCrmDeal::GetListEx(['UF_DATA_DZ' => 'asc', 'CONTACT_FULL_NAME' => 'asc'], $arFilter, false, false, ['*', 'UF_*']);
$dealList = [];

while ($deal = $arDeals->Fetch()) {
    $dealList[] = $deal;

    if (!empty($deal[PART_ZAIM])) {
        $arPartner[] = $deal[PART_ZAIM];
    }
}

$partConts = [];
//Получаем данные по партнерам для ФИО
$arFilter = [];
$arFilter['ID'] = $arPartner;
$aRes = \CCrmContact::GetList([], $arFilter, ['NAME', 'SECOND_NAME', 'LAST_NAME']);

$rowNum = 0;

while ($deal = $aRes->GetNext()) {
    $partConts[$deal['ID']] = trim(trim($deal['LAST_NAME'] . ' ' . mb_substr($deal['NAME'], 0, 1)) . '. ' . mb_substr($deal['SECOND_NAME'], 0, 1)) . '.';
}

foreach ($dealList as $deal) {
    // исключаем тестовые сделки
    if ((int)$deal['ID'] === 7530)
        continue;

    $rowNum = $rowAll;
    $rowAll++;

    foreach ($arColumnDescript as $field => $descript) {
        if ($field === 'FIO_CONTACT') {
            $deal[$field] = trim(trim($deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']) . ' ' . $deal['CONTACT_SECOND_NAME']);
        }

        if ($field === 'ASSIGNED') {
            $deal[$field] = trim(trim($deal['ASSIGNED_BY_LAST_NAME'] . ' ' . mb_substr($deal['ASSIGNED_BY_NAME'], 0, 1)) . '.' . mb_substr($deal['ASSIGNED_BY_SECOND_NAME'], 0, 1)) . '.';
        }

        if ($field === PART_ZAIM and !empty($deal[PART_ZAIM])) {
            $deal[$field] = $partConts[$deal[PART_ZAIM]];
        }

        if ($field === 'DATE_PFR_PLAN') {
            $lastdate = findLastDate([$deal['UF_TRANCHE_1_DATA'], $deal['UF_TRANCHE_2_DATA'], $deal['UF_TRANCHE_3_DATA'], $deal['UF_TRANCHE_4_DATA']]);

            if ($lastdate != 0) {
                $deal[$field] = add_days_to_date($lastdate, 40);
            }
        }

        if ($field === FIN_COMMENT) {
            $deal[$field] = 'Комментарий CRM:' . $deal[COMMENTS] . '; Комментарий XLS:' . $deal[$field];
        }

        if ($field === 'STATUS') {
            $deal[$field] = (in_array($deal['STAGE_ID'], ['C8:WON', 'C8:LOSE'])) ? 'З' : '';
        }

        if ($field === TRANSFER_OF_TRANCHE_4) {
            $deal[$field] += $deal[TRANSFER_OF_TRANCHE_5];
        }

        if ($field === CONTRIBUTIONS_3_SUM) {
            $deal[$field] += $deal[CONTRIBUTIONS_4_SUM];
        }

        if (!empty($deal[$field])) {
            if ($descript['type'] === 'date') {
                $val = $sharedData::PHPToExcel(strtotime($deal[$field]) + 3 * 60 * 60);
            } else if ($descript['type'] === 'summ') {
                $val = round($deal[$field],2);
            } else {
                $val = $deal[$field];
            }

            $worksheet->setCellValue($descript['col'] . $rowNum, $val);
        }
    }

    if (in_array('495', $deal[USED_CERTIFICATES])) {
        $worksheet->setCellValue('BX'. $rowNum, 'Да');
    }

    $worksheet->setCellValue('AC' . $rowNum, "=V$rowNum+X$rowNum+Z$rowNum+AB$rowNum");
    $worksheet->setCellValue('L' . $rowNum, "=AC$rowNum-BG$rowNum-BJ$rowNum-BL$rowNum");
    $worksheet->setCellValue('M' . $rowNum, "=AZ$rowNum-BO$rowNum");
    $worksheet->setCellValue('N' . $rowNum, "=AO$rowNum");
    $worksheet->setCellValue('O' . $rowNum, "=IF((L$rowNum-M$rowNum-N$rowNum)<0,0,L$rowNum-M$rowNum-N$rowNum)");
    $worksheet->setCellValue('AO' . $rowNum, "=AH$rowNum+AK$rowNum+AN$rowNum");
    $worksheet->setCellValue('AP' . $rowNum, "=IF(V$rowNum<>0,AE$rowNum-AH$rowNum-AK$rowNum-AN$rowNum,0)");
    $worksheet->setCellValue('AS' . $rowNum, "=AQ$rowNum+AR$rowNum");
    $worksheet->setCellValue('AZ' . $rowNum, "=AU$rowNum+AW$rowNum+AY$rowNum");
    $worksheet->setCellValue('BA' . $rowNum, "=IF((X$rowNum+Z$rowNum+AB$rowNum+V$rowNum-AH$rowNum)<AS$rowNum,(X$rowNum+Z$rowNum+AB$rowNum)-AZ$rowNum,AS$rowNum-AZ$rowNum)");
    $worksheet->setCellValue('BE' . $rowNum, "=IF(ISBLANK(BC$rowNum),\"\",BC$rowNum+45)");
    $worksheet->setCellValue('BM' . $rowNum, "=AC$rowNum-BG$rowNum-BJ$rowNum-BL$rowNum");
    $worksheet->setCellValue('BQ' . $rowNum, "=IF(BP$rowNum<>0,BP$rowNum+10,\" \")");
    $worksheet->setCellValue('BS' . $rowNum, "=IF(N(BP$rowNum)>0,N(BP$rowNum)+14,\" \")");
    $worksheet->setCellValue('BU' . $rowNum, "=IF(N(BP$rowNum)>0,N(BP$rowNum)+30,\" \")");
}

$worksheet->setCellValue('L' . ($rowAll + 1), "=SUM(L6:L$rowAll)");
$worksheet->setCellValue('M' . ($rowAll + 1), "=SUM(M6:M$rowAll)");

$newfile = 'БАЗА МК ' . date('d.m.Y') . '(CRM).xlsx';

$writer = new Xlsx($spread);
$writer->save($newfile);

giveFile($newfile);
unlink($newfile);