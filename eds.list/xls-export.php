<?php
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;

Loader::IncludeModule('crm');

ini_set('memory_limit', '768M');

function getSavingsBalance()
{
    global $USER;
    // Авторизаемся под админам если запускаем из консоли
    if (!$USER->GetID()) {
        $USER->Authorize(1);
    }

    $allDeals = CCrmDeal::GetListEx(
        [],
        [
            'CATEGORY_ID' => '14'
        ],
        false,
        false,
        [
            'ID'
        ]
    );

    $ids = [];

    while ($deal = $allDeals->Fetch()) {
        $ids[] = $deal['ID'];
    }

    \Vaganov\Helper::includeHlTable('money_orders');

    $transactions = \MoneyOrdersTable::getList([
        'select' => [
            'UF_PAYMENT',
            'UF_SUM',
            'UF_DEAL_ID'
        ],
        'order' => [
            'ID' => 'ASC'
        ],
        'filter' => [
            'UF_DEAL_ID' => $ids,
            '!=UF_HANDLER' => ['interest_payment', 'in_entranceKpk']
        ]
    ])->fetchAll();

    $deals = [];

    foreach ($transactions as $transaction) {
        if ($transaction['UF_PAYMENT'] === 'IN') {
            $deals[$transaction['UF_DEAL_ID']]['IN'] += $transaction['UF_SUM'] * 100;
        } else {
            $deals[$transaction['UF_DEAL_ID']]['OUT'] += $transaction['UF_SUM'] * 100;
        }
    }

    foreach ($deals as $key => $value) {
        $deals[$key]['BALANCE'] = ($value['IN'] - $value['OUT']) / 100;
    }

    return $deals;
}

function prettyMonth($num)
{
    $sum = $num;

    $monthesPostfixes = ['месяц', 'месяца', 'месяцев'];

    $sum = $sum % 100;

    //Если больше 19, делим его без остатка ещё раз, уже на 10
    if ($sum > 19) {
        $sum = $sum % 10;
    }

    //В зависимости от того, какие числа остались, возвращаем значения
    switch ($sum)
    {
        case 1:
            $postfix = $monthesPostfixes[0];
            break;
        case 2:
        case 3:
        case 4:
            $postfix = $monthesPostfixes[1];
            break;
        default:
            $postfix = $monthesPostfixes[2];
            break;
    }

    return $num . ' ' . $postfix;
}

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
    'DATE_CREATE' => ['col' => 'A', 'type' => 'str'],
    'DATE_NUMBER_DS' => ['col' => 'B', 'type' => 'number_ds'],
    'MANAGER' => ['col' => 'C', 'type' => 'manager'],
    'PARTNER' => ['col' => 'D', 'type' => 'partner'],
    'CLIENT' => ['col' => 'E', 'type' => 'client'],
    'INTEREST_RATE' => ['col' => 'F', 'type' => 'interest_rate'],
    'CONTRACT_PERIOD' => ['col' => 'G', 'type' => 'contract_period'],
    'PAYMENT_OF_INTEREST' => ['col' => 'H', 'type' => 'payment_of_interest'],
    'SUM' => ['col' => 'I', 'type' => 'summ'],
    'UF_SAVINGS_DEPOSIT_END_DATE' => ['col' => 'J', 'type' => 'str']
];

$template = $_SERVER['DOCUMENT_ROOT'] . '/local/xlsx/eds_template.xlsx';
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$sharedData = new \PhpOffice\PhpSpreadsheet\Shared\Date();

$spread = $reader->load($template);

$worksheet = $spread->getSheetByName('');

$arFilter = [
    'CATEGORY_ID' => '14'
];

if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $value) {
        if (in_array($key, ['MARKER', 'PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND']))
            continue;

        $arr = explode(',', $value);
        $arFilter[$key] = (count($arr) > 1) ? $arr : $value;
    }
}

$rowAll = 2;

global $USER;
// Авторизаемся под админам если запускаем из консоли
if (!$USER->GetID()) {
    $USER->Authorize(1);
}

$arDeals = CCrmDeal::GetListEx(['CONTACT_FULL_NAME' => 'asc'], $arFilter, false, false, ['*', 'UF_*']);

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
$curSheet = false;

while ($deal = $aRes->GetNext()) {
    $partConts[$deal['ID']] = trim(trim($deal['LAST_NAME'] . ' ' . mb_substr($deal['NAME'], 0, 1)) . '. ' . mb_substr($deal['SECOND_NAME'], 0, 1)) . '.';
}

$allDealsBalance = getSavingsBalance();

foreach ($dealList as $deal) {
    $curSheet = $worksheet;
    $rowNum = $rowAll;
    $rowAll++;

    foreach ($arColumnDescript as $field => $descript) {
        if ($field === 'DATE_NUMBER_DS') {
            $deal[$field] = ($deal['UF_EDS_CONTRACT_DATE'] && $deal['UF_EDS_CONTRACT_NUMBER']) ? $deal['UF_EDS_CONTRACT_DATE'] . ' ' . $deal['UF_EDS_CONTRACT_NUMBER'] : '-';
        }

        if ($field === 'INTEREST_RATE') {
            $deal[$field] = ($deal['UF_CONTRACTUAL_INTEREST_RATE']) ? $deal['UF_CONTRACTUAL_INTEREST_RATE'] . ' %' : '-';
        }

        if ($field === 'CONTRACT_PERIOD') {
            if (isset($deal['UF_CONTRACT_PERIOD'])) {
                if ($deal['UF_CONTRACT_PERIOD'] === '0') {
                    $deal[$field] = 'Бессрочно';
                } else {
                    $deal[$field] = prettyMonth($deal['UF_CONTRACT_PERIOD']);
                }
            } else {
                $deal[$field] = '-';
            }
        }

        if ($field === 'PAYMENT_OF_INTEREST') {
            $interests = [
                '750' => 'Ежемесячно',
                '751' => 'В конце срока',
                '763' => 'Не выплачиваются'
            ];

            $deal[$field] = ($deal['UF_INTEREST_PAYMENT']) ? $interests[$deal['UF_INTEREST_PAYMENT']] : '-';
        }

        if ($field === 'SUM') {
            $deal[$field] = $allDealsBalance[$deal['ID']]['BALANCE'];
        }

        if ($field === 'CLIENT') {
            $deal[$field] = trim(trim($deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']) . ' ' . $deal['CONTACT_SECOND_NAME']);
        }

        if ($field === 'CLIENT') {
            $deal[$field] = trim(trim($deal['CONTACT_LAST_NAME'] . ' ' . $deal['CONTACT_NAME']) . ' ' . $deal['CONTACT_SECOND_NAME']);
        }

        if ($field === 'MANAGER') {
            $deal[$field] = trim(trim($deal['ASSIGNED_BY_LAST_NAME'] . ' ' . mb_substr($deal['ASSIGNED_BY_NAME'], 0, 1)) . '.' . mb_substr($deal['ASSIGNED_BY_SECOND_NAME'], 0, 1)) . '.';
        }

        if ($field === 'PARTNER' and !empty($deal[PART_ZAIM])) {
            $deal[$field] = $partConts[$deal[PART_ZAIM]];
        }

        if (!empty($deal[$field])) {
            if ($descript['type'] === 'date') {
                $val = $sharedData::PHPToExcel(strtotime($deal[$field]) + 3 * 60 * 60);
            } else if ($descript['type'] === 'summ') {
                $val = round($deal[$field],2);
            } else {
                $val = $deal[$field];
            }

            if ($curSheet) {
                $curSheet->setCellValue($descript['col'] . $rowNum, $val);
            }
        }
    }
}

$newfile = 'БАЗА СБЕРЕЖЕНИЙ ' . date('d.m.Y') . '(CRM).xlsx';

$writer = new Xlsx($spread);
$writer->save($newfile);

giveFile($newfile);
unlink($newfile);