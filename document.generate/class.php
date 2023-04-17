<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('highloadblock');
use Bitrix\Main\Engine\Contract\Controllerable;
use \Components\Vaganov\DocumentGenerate\DocumentGenerate;
use \Components\Vaganov\DocumentGenerate\DocumentGenerateModel;
class DocumentGenerateBitrix extends CBitrixComponent implements Controllerable
{

    public function configureActions()
    {
        return [];
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function updateAction($doc){
        $docAr = json_decode($doc,1);
        DocumentGenerate::rule(DocumentGenerate::$RULE_EDIT_DOC);
        return (new DocumentGenerateModel())->update($docAr);
    }

    public function rowsAction($currentPage = 1, $pageSize = false)
    {

        $gridId = 'document-generate';
        $arSort = ['ID' => 'DESC'];

        $arFilter = [];


        // PAGE
        if ($pageSize) {
            \CUserOptions::SetOption('myGrid', $gridId, [
                'pageSize' => $pageSize
            ]);
        }
        $filterOptions = new Bitrix\Main\UI\Filter\Options($gridId);
        $filterData = $filterOptions->getFilter([]);
        $options = \CUserOptions::GetOption('myGrid', $gridId);
        $pageSize = isset($options['pageSize']) ? (int)$options['pageSize'] : 20;


        $nav = new \Bitrix\Main\UI\PageNavigation($gridId);
        $nav->setPageSize($pageSize);
        $currentPage = (int)$currentPage > 0 ? $currentPage : 1 ;
        $nav->setCurrentPage($currentPage);

        $res = (new \Components\Vaganov\DocumentGenerate\DocumentGenerateModel())
            ->orderList($arSort, $arFilter, $nav->getLimit(), $nav->getOffset());

        if ($currentPage > 1 && count($res['ROWS']) === 0) {
            $currentPage = 1;
            $res = (new \Components\Vaganov\DocumentGenerate\DocumentGenerateModel())
                ->orderList($arSort, $arFilter, $nav->getLimit(), 0);
        }


        return [
            'columns' => [
                ['attribute' => 'ID','label' => 'ID', ],
                ['attribute' => 'UF_NAME','label' => 'Имя', ],
                ['attribute' => 'UF_DESCRIPTION','label' => 'Описание', ],
                //['attribute' => 'DOC','label' => 'Документ', ],
            ],
            'rows' => $res['ROWS'],
            'pageSize' => (int)$pageSize,
            'currentPage' => (int)$currentPage,
            'total' => (int)$res['ROWS_COUNT']
        ];
    }



}