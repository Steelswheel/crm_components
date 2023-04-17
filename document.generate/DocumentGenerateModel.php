<?php

namespace Components\Vaganov\DocumentGenerate;

use Bitrix\Main\SystemException;
use Table\DocumentGenerateTable;

class DocumentGenerateModel
{

    public function rule()
    {
        //
    }

    public function query($arSort, $arFilter, $limit = false, $offset = false)
    {
        $payRes = DocumentGenerateTable::getList([
            "select" => [
                "ID",
                "UF_NAME",
                "UF_NAME_EN",
                "UF_DESCRIPTION",
                "UF_FIELDS",
                "UF_IS_HTML",
                "UF_HTML",
                "UF_FILE",
            ],
            "filter" => $arFilter,
            'count_total' => true,
            'limit' => $limit,
            'offset' => $offset,
            'order' => $arSort
        ]);

        return $payRes;
    }

    public function orderList($arSort, $arFilter, $limit = false, $offset = false)
    {

        $res = $this->query($arSort, $arFilter, $limit, $offset);
        $rows = [];

        $rules = DocumentGenerate::allRuleUser();

        while ($item = $res->fetch()){

            if( $item['UF_FILE']){

                $item['UF_FILE'] = $this->getFileById($item['UF_FILE']);
            }else{
                $item['UF_FILE'] = null;
            }
            $item['rule'] = $rules;

            $rows[] = $item;
        }

        return [
            'ROWS' => $rows,
            'NAV_OBJECT' => $res,
            'ROWS_COUNT' => $res->getCount()
        ];
    }


    public function getFileById($fileId){
        $f = \CFile::GetByID($fileId)->arResult[0];
        return [
            'name' => $f['FILE_NAME'],
            'src' => \CFile::GetPath($fileId)
        ];
    }

    public function getFileByName($nameEn){
        $doc = DocumentGenerateTable::getList([
            'filter' => ['UF_NAME_EN' => $nameEn]
        ])->fetch();

        if($doc && $doc['UF_FILE']){
            return $this->getFileById($doc['UF_FILE']);
        }
        return false;
    }

    public function update($doc){

        DocumentGenerate::rule(DocumentGenerate::$RULE_EDIT_DOC);

        if(!DocumentGenerate::rule(DocumentGenerate::$RULE_EDIT_FIELDS_DOC,false)){
            unset($doc['UF_NAME_EN']);
            unset($doc['UF_FIELDS']);
        }

        if((int)$doc['ID'] === 0){
            DocumentGenerateTable::add($doc);
            return [
                'row' => 'new'
            ];
        }



        if($doc['UF_FILE'] && $doc['UF_FILE']['tmp']){
            $file = \CFile::MakeFileArray($doc['UF_FILE']['tmp']);
            $file["name"] = $doc['UF_FILE']['name'];
            $file["MODULE_ID"] = "main";
            $doc['UF_FILE'] = \CFile::SaveFile($file,"document_generateTable");
        }else{
            unset($doc['UF_FILE']);
        }

        DocumentGenerateTable::update($doc['ID'],$doc);

        $res = (new DocumentGenerateModel())->orderList([], ['ID' => $doc['ID']]);

        return [
            'row' => $res['ROWS'][0]
        ];
    }
}