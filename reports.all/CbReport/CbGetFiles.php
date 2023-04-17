<?php
namespace Components\Vaganov\ReportsAll\CbReport;

ini_set('memory_limit', '2048M');

use Vaganov\Helper;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Vaganov\Notification;

Loader::includeModule('crm');

class CbGetFiles extends \CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [];
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
                'UF_DOC_IMPROVEMENT',
                'UF_AKT_OF_TRANSFER'
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

    private function getFileName($key, $count = '')
    {
        $name = '';

        if (!empty($count)) {
            $count = '_' . $count;
        }

        switch ($key) {
            case 'UF_AKT_OF_TRANSFER':
                $name = 'Акт_проверки_объекта_недвижимости' . $count;
                break;
            case 'UF_PAYMENT_ORDER_PDF_SRC':
                $name = 'ПЛАТЕЖНОЕ ПОРУЧЕНИЕ' . $count;
                break;
            case 'APPLICATION_TO_JOIN_THE_KPK_FILE':
                $name = 'ЗАЯВЛЕНИЕ_НА_ВСТУПЛЕНИЕ_В_КПК' . $count;
                break;
            case 'ANKETA_ZAEM':
                $name = 'ЗАЯВЛЕНИЕ_НА_ПОЛУЧЕНИЕ_ЗАЙМА' . $count;
                break;
            case 'DZ_FILE':
                $name = 'ДОГОВОР_ЗАЙМА' . $count;
                break;
            case 'PAYMENT_SCHEDILE':
                $name = 'ГРАФИК_ПЛАТЕЖЕЙ' . $count;
                break;
            case 'SUPPLEMENTARY_AGREEMENT_FILE':
                $name = 'Дополнительное_соглашение_к_договору_займа' . $count;
                break;
            case 'PROPERTY_INSPECTION_ACT':
                $name = 'АКТ_ОБСЛЕДОВАНИЯ_НЕДВИЖИМОГО_ИМУЩЕСТВА' . $count;
                break;
            case 'ASSESSMENT':
                $name = 'ОЦЕНКА_ПЛАТЕЖЕСПОСОБНОСТИ' . $count;
                break;
            case 'OBLIGATION_TO_BUILD_SCAN':
                $name = 'РАСПИСКА_НА_СТРОЙКУ' . $count;
                break;
            case 'PVK':
                $name = 'АНКЕТА_ПВК' . $count;
                break;
            case 'RELATIVE_EXPLAIN':
                $name = 'ПОЯСНЕНИЕ_ПРИ_РОДСТВЕННОЙ_СДЕЛКЕ' . $count;
                break;
            case 'BIRTH_CERTIFICATE_OF_CHILDREN':
                $name = 'Свидетельство_о_рождении_детей' . $count;
                break;
            case 'CERTIFICATE_OF_MARRIAGE':
                $name = 'Свидетельство_о_заключении_расторжении_брака' . $count;
                break;
            case 'CERTIFICATE_MK':
                $name = 'Свидетельство_МСК' . $count;
                break;
            case 'PASSPORT_SCAN':
                $name = 'Паспорт_заемщика' . $count;
                break;
            case 'PHOTO_OBJECT':
                $name = 'Фото_объекта' . $count;
                break;
            case 'REGISTERED_DKP':
                $name = 'Зарегистрированный_ДКП' . $count;
                break;
            case 'UF_DOC_IMPROVEMENT':
                $name = 'Заключение_об_улучшении_жилищных_условий' . $count;
                break;
            case 'EXPERT_EGRN_REGISTERED':
                $name = 'ВЫПИСКА_ИЗ_ЕГРН_С_ЗАЛОГОМ' . $count;
                break;
            case 'RECEIPT_OF_SELLER':
                $name = 'Расписка_от_продавца_на_всю_сумму_по_ДКП' . $count;
                break;
            case 'UF_1T_SELLER_TRANSPORT_AMOUNT':
                $name = 'Чек_о_перечислении_суммы_1_транша' . $count;
                break;
            case 'UF_2T_SELLER_TRANSPORT_AMOUNT':
                $name = 'Чек_о_перечислении_суммы_2_транша' . $count;
                break;
            case 'UF_3T_SELLER_TRANSPORT_AMOUNT':
                $name = 'Чек_о_перечислении_суммы_3_транша' . $count;
                break;
            case 'UF_4T_SELLER_TRANSPORT_AMOUNT':
                $name = 'Чек_о_перечислении_всей_суммы_по_ДЗ' . $count;
                break;
            case 'BUILDING_PERMIT':
                $name = 'РАЗРЕШЕНИЕ_НА_СТРОИТЕЛЬСТВО' . $count;
                break;
            case 'DOCUMENTS_FOR_LAND':
                $name = 'ДОКУМЕНТЫ_НА_ЗЕМЕЛЬНЫЙ_УЧАСТОК' . $count;
                break;
            case 'BUILD_CONFIRM_DOC':
                $name = 'ДОКУМЕНТЫ_И_ФОТОГРАФИИ_ПОДТВЕРЖДАЮЩИЕ_СТРОИТЕЛЬСТВО' . $count;
                break;
            case 'UF_1T_SCAN_OF_PAYMENT':
                $name = 'ПЛАТЕЖНОЕ_ПОРУЧЕНИЕ_1' . $count;
                break;
            case 'UF_2T_SCAN_OF_PAYMENT':
                $name = 'ПЛАТЕЖНОЕ_ПОРУЧЕНИЕ_2' . $count;
                break;
            case 'UF_3T_SCAN_OF_PAYMENT':
                $name = 'ПЛАТЕЖНОЕ_ПОРУЧЕНИЕ_3' . $count;
                break;
            case 'UF_4T_SCAN_OF_PAYMENT':
                $name = 'ПЛАТЕЖНОЕ_ПОРУЧЕНИЕ_4' . $count;
                break;
        }

        return $name;
    }

    public function getManagers()
    {
        $mainSaleDepart = \Vaganov\Helper::getDepart(['ID' => [241]]);

        $saleDeparts = \Vaganov\Helper::getDepart([
            '>LEFT_MARGIN' => $mainSaleDepart[0]['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $mainSaleDepart[0]['RIGHT_MARGIN'],
        ]);

        $departsIds = array_map(function($i) {
            return $i['ID'];
        }, $saleDeparts);

        $by = 'last_name';
        $order = 'asc';

        $dbRes = \CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'Y',
                'UF_DEPARTMENT' => $departsIds,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        $users = [
            'ACTIVE' => [],
            'INACTIVE' => []
        ];

        while ($item = $dbRes->Fetch()) {
            $users['ACTIVE'][$item['ID']] = trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']);
        }

        unset($dbRes);

        $dbRes = \CUser::GetList(
            $by,
            $order,
            [
                'ACTIVE' => 'N',
                'UF_DEPARTMENT' => $departsIds,
                '!=EXTERNAL_AUTH_ID' => 'bot'
            ],
            ['SELECT' => ['UF_DEPARTMENT']]
        );

        while ($item = $dbRes->Fetch()) {
            $users['INACTIVE'][$item['ID']] = trim($item['LAST_NAME']) . ' ' . trim($item['NAME']) . ' ' . trim($item['SECOND_NAME']);
        }

        return $users;
    }

    private function getDealsDiff()
    {
        Helper::includeHlTable('cb_progress');

        $progress_res = \CbProgressTable::getList([
            'select' => [
                'ID',
                'UF_*'
            ]
        ])->fetchAll();

        $files_diff = [];
        $progress_deals = [];
        $table_ids = [];

        foreach ($progress_res as $deal) {
            $progress_deals[] = $deal['UF_DEAL_ID'];

            $diff = array_diff(json_decode($deal['UF_ALL_FILES'], 1), json_decode($deal['UF_PROCESSED_FILES'], 1));

            $diff = array_map(function ($id) {
                return (int)$id;
            }, $diff);

            $files_diff[$deal['UF_DEAL_ID']] = $diff;

            $table_ids[$deal['UF_DEAL_ID']] = $deal['ID'];
        }

        \Vaganov\Helper::includeHlTable('cb_request_ids');

        $done_res = \CbRequestIdsTable::getList([
            'select' => ['UF_DEAL_ID']
        ])->fetchAll();

        $done_deals = [];

        if (!empty($done_deals)) {
            foreach ($done_res as $deal) {
                $done_deals[] = $deal['UF_DEAL_ID'];
            }
        }

        return [
            'TABLE_IDS' => $table_ids,
            'DEALS' => array_diff($progress_deals, $done_res),
            'FILES_DIFF' => $files_diff
        ];
    }

    public function getFiles()
    {
        $this->deleteFiles($_SERVER['DOCUMENT_ROOT'] . '/upload/cb/tmp');

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/cb/tmp')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/cb/tmp');
        }

        Helper::includeHlTable('cb_progress');

        $diff = $this->getDealsDiff();

        $dealIDs = $diff['DEALS'];
        $table_ids = $diff['TABLE_IDS'];

        Helper::includeHlTable('cb_report_start');

        $start_download = \CbReportStartTable::getList([
            'select' => ['ID', 'UF_START']
        ])->fetch();

        if (!empty($dealIDs) && $start_download['UF_START'] === '1') {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cb_lock/is_loading.txt', 'false');

            $managers = $this->getManagers();

            $deals = $this->getDeals($dealIDs);

            foreach ($deals as $deal) {
                $manager_dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/cb/tmp/' . $deal['ASSIGNED_BY_ID'];

                if (!is_dir($manager_dir)) {
                    mkdir($manager_dir);
                }

                $deal_dir = $manager_dir . '/(' . $deal['ID'] . ') ' . $deal['LAST_NAME'] . ' ' . trim($deal['NAME'] . ' ' . $deal['SECOND_NAME']);

                if (!is_dir($deal_dir)) {
                    mkdir($deal_dir);
                }

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
                        $count = 1;

                        $image = [
                            'ID' => [],
                            'NAME' => $this->getFileName($key),
                            'DATA' => ''
                        ];

                        foreach ($value as $item) {
                            $file = \CFile::GetFileArray($item);

                            if (!empty($file)) {
                                $description = json_decode($file['DESCRIPTION']);

                                if (empty($description) || $description->del === 0) {
                                    $pathInfo = pathinfo($_SERVER['DOCUMENT_ROOT'] . $file['SRC']);

                                    if (count($value) > 1) {
                                        $name = $this->getFileName($key, $count) . '.' . $pathInfo['extension'];
                                    } else {
                                        $name = $this->getFileName($key) . '.' . $pathInfo['extension'];
                                    }

                                    $isImage = \CFile::IsImage($file['NAME'], $file['CONTENT_TYPE']);

                                    if (!$isImage && !in_array($pathInfo['extension'], ['jpg', 'jpeg', 'png', 'gif', 'tiff'])) {
                                        if (in_array((int)$file['ID'], $diff['FILES_DIFF'][$deal['ID']])) {
                                            copy($_SERVER['DOCUMENT_ROOT'] . $file['SRC'], $deal_dir . '/' . $name);
                                        }
                                    } else {
                                        $image['ID'][] = $file['ID'];
                                        $image['DATA'] .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . $file['SRC'] . '" />';
                                    }

                                    $count++;
                                }
                            }
                        }

                        if (!empty($image['DATA'])) {
                            if (file_exists($deal_dir . '/' . $image['NAME'] . '.pdf')) {
                                unlink($deal_dir . '/' . $image['NAME'] . '.pdf');
                            }

                            $pdf = new \Mpdf\Mpdf();

                            $pdf->WriteHTML($image['DATA']);

                            $pdf->Output($deal_dir . '/' . $image['NAME'] . '.pdf');

                            $files[] = ['ID' => $image['ID']];

                            unset($pdf);
                        }

                        unset($image);
                    }
                }

                $progress = [];

                foreach ($files as $file) {
                    if (is_array($file['ID'])) {
                        foreach ($file['ID'] as $id) {
                            if (in_array((int)$id, $diff['FILES_DIFF'][$deal['ID']])) {
                                $progress[] = (int)$id;
                            }
                        }
                    } else {
                        if (in_array((int)$file['ID'], $diff['FILES_DIFF'][$deal['ID']])) {
                            $progress[] = (int)$file['ID'];
                        }
                    }

                    $table_deal_id = $table_ids[$deal['ID']];

                    if (!empty($table_deal_id)) {
                        \CbProgressTable::update($table_deal_id, [
                            'UF_PROCESSED_FILES' => json_encode($progress)
                        ]);
                    }
                }

                unset($progress, $files);

                $rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter' => ['TABLE_NAME' => 'cb_request_ids']])->fetch();

                $idStageIdSettings = $rsData['ID'];
                $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($idStageIdSettings)->fetch();
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();

                $entity_data_class::add(['UF_DEAL_ID' => (int)$deal['ID']]);
            }

            Helper::includeHlTable('cb_report_start');

            $start_download = \CbReportStartTable::getList([
                'select' => ['ID', 'UF_START']
            ])->fetch();

            $pathdir = $_SERVER['DOCUMENT_ROOT'] . '/upload/cb/tmp';

            $objs = glob($pathdir . '/*');

            if ($objs && $start_download['UF_START'] === '1') {
                foreach($objs as $obj) {
                    $zip = new \ZipArchive();

                    $zip->open($obj . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                    $this->addFileRecursion($zip, $obj);

                    $zip->close();

                    $manager_id = str_replace($pathdir . '/', '', $obj);

                    if (!empty($managers['ACTIVE'][$manager_id])) {
                        Notification::send('Сделки менеджера: ' . $managers['ACTIVE'][$manager_id] . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', $manager_id);
                        Notification::send('Сделки менеджера: ' . $managers['ACTIVE'][$manager_id] . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', 104);
                        Notification::send('Сделки менеджера: ' . $managers['ACTIVE'][$manager_id] . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', 640);
                    } else {
                        if ((int)$manager_id === 645) {
                            $name = 'Петянкина Оксана Рашидовна';
                        } else if ((int)$manager_id === 488) {
                            $name = 'Усык Константин Владимирович';
                        } else if ($managers['INACTIVE'][$manager_id]) {
                            $name = $managers['INACTIVE'][$manager_id];
                        } else {
                            $name = $manager_id;
                        }

                        foreach (array_keys($managers['ACTIVE']) as $manager) {
                            Notification::send('Сделки менеджера: ' . $name . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', $manager);
                        }

                        Notification::send('Сделки менеджера: ' . $name . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', 104);
                        Notification::send('Сделки менеджера: ' . $name . ' [URL=https://crm.kooperatiff.ru/upload/cb/tmp/' . $manager_id . '.zip]Архив файлов для отчета ЦБ[/URL]', 640);
                    }

                    $this->deleteFiles($obj);
                }
            }

            \CbReportStartTable::update($start_download['ID'], ['UF_START' => '0']);

            return true;
        }

        return false;
    }

    private function addFileRecursion($zip, $dir, $start = '')
    {
        if (empty($start)) {
            $start = $dir;
        }

        if ($objs = glob($dir . '/*')) {
            foreach($objs as $obj) {
                if (is_dir($obj)) {
                    $this->addFileRecursion($zip, $obj, $start);
                } else {
                    $zip->addFile($obj, str_replace(dirname($start) . '/', '', $obj));
                }
            }
        }
    }

    private function deleteFiles($path)
    {
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $this->deleteFiles(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }
}