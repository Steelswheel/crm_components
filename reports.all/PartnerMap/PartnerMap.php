<?php

namespace Components\Vaganov\ReportsAll\PartnerMap;
use Bitrix\Crm\DealTable;


require $_SERVER["DOCUMENT_ROOT"].'/local/composer/vendor/autoload.php';

class PartnerMap
{

    public static function authorizes(){
        $path = $_SERVER["DOCUMENT_ROOT"]."/local/components/vaganov/reports.all/PartnerMap/js/42.json";
        $data = file_get_contents($path);
        return json_decode($data,1);
    }

    public static function partners(){
        \CModule::IncludeModule("crm");

        $partners = DealTable::getList([
            "select" => [
                "ID",
                "CONTACT_ID",
                "NAME" => "CONTACT.NAME",
                "LAST_NAME" => "CONTACT.LAST_NAME",
                "SECOND_NAME" => "CONTACT.SECOND_NAME",
                "UF_PARTNER_REGISTER_ADDRESS_DADATA",
            ],
            "filter" => ["STAGE_ID" => ['C10:FINAL_INVOICE'] ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField(
                    'CONTACT',
                    \Bitrix\Crm\ContactTable::class,
                    ['=this.CONTACT_ID'  => 'ref.ID'],
                ),
            ],
        ])->fetchAll();

        $partners = array_map(function($i){
            $code = false;
            if($r = json_decode($i['UF_PARTNER_REGISTER_ADDRESS_DADATA'],1)){
                $code = $r['region_iso_code'];
            }

            return [
                'fio' => $i['LAST_NAME']." ".$i['NAME']." ".$i['SECOND_NAME'],
                'id' => $i['ID'],
                'code' => $code
            ];
        }, $partners);

        return $partners;
    }


    public static function registerShareholders(){
        $RegisterShareholder = \Table\RegisterShareholderTable::getList([
            'filter' => [
                '!UF_DATE_IN' => '',
                'UF_DATE_OUT' => '',
            ]
        ])->fetchAll();

        $RegisterShareholder = array_map(function($i){
            $i['code'] = $i['UF_ADDRESS_CODE'];
            return $i;

        },$RegisterShareholder);

        return $RegisterShareholder;
    }





    public static function regionCode(){
        return [
            "RU-CR" => "Республика Крым",
            "RU-MUR" => "Мурманская область",
            "RU-PSK" => "Псковская область",
            "RU-SMO" => "Смоленская область",
            "RU-BRY" => "Брянская область",
            "RU-KRS" => "Курская область",
            "RU-BEL" => "Белгородская область",
            "RU-VOR" => "Воронежская область",
            "RU-KC" => "Карачаево-Черкесская Республика",
            "RU-SE" => "Республика Северная Осетия — Алания",
            "RU-IN" => "Республика Ингушетия",
            "RU-DA" => "Республика Дагестан",
            "RU-KL" => "Республика Калмыкия",
            "RU-ORE" => "Оренбургская область",
            "RU-CHE" => "Челябинская область",
            "RU-OMS" => "Омская область",
            "RU-NVS" => "Новосибирская область",
            "RU-AL" => "Республика Алтай",
            "RU-ALT" => "Алтайский край",
            "RU-TY" => "Республика Тыва",
            "RU-AMU" => "Амурская область",
            "RU-YEV" => "Еврейская автономная область",
            "RU-PRI" => "Приморский край",
            "RU-MAG" => "Магаданская область",
            "RU-ARK" => "Архангельская область",
            "RU-SAK" => "Сахалинская область",
            "RU-KAM" => "Камчатский край",
            "RU-CHU" => "Чукотский автономный округ",
            "RU-KGD" => "Калининградская область",
            "RU-KLU" => "Калужская область",
            "RU-NGR" => "Новгородская область",
            "RU-TVE" => "Тверская область",
            "RU-MOS" => "Московская область",
            "RU-NEN" => "Ненецкий автономный округ",
            "RU-YAN" => "Ямало-Ненецкий автономный округ",
            "RU-KHA" => "Хабаровский край",
            "RU-KDA" => "Краснодарский край",
            "RU-STA" => "Ставропольский край",
            "RU-CE" => "Чеченская Республика",
            "RU-KB" => "Кабардино-Балкарская Республика  ",
            "RU-VGG" => "Волгоградская область",
            "RU-VLG" => "Вологодская область",
            "RU-ROS" => "Ростовская область",
            "RU-AST" => "Астраханская область",
            "RU-ORL" => "Орловская область",
            "RU-LIP" => "Липецкая область",
            "RU-TUL" => "Тульская область",
            "RU-RYA" => "Рязанская область",
            "RU-TAM" => "Тамбовская область",
            "RU-MO" => "Республика Мордовия",
            "RU-PNZ" => "Пензенская область",
            "RU-SAR" => "Саратовская область",
            "RU-ULY" => "Ульяновская область",
            "RU-SAM" => "Самарская область",
            "RU-KGN" => "Курганская область",
            "RU-BA" => "Республика Башкортостан",
            "RU-YAR" => "Ярославская область",
            "RU-KOS" => "Костромская область",
            "RU-IVA" => "Ивановская область",
            "RU-VLA" => "Владимирская область",
            "RU-NIZ" => "Нижегородская область",
            "RU-CU" => "Чувашская Республика",
            "RU-KIR" => "Кировская область",
            "RU-ME" => "Республика Марий Эл",
            "RU-TA" => "Республика Татарстан",
            "RU-UD" => "Удмуртская Республика",
            "RU-KO" => "Республика Коми",
            "RU-PER" => "Пермский край",
            "RU-BU" => "Республика Бурятия",
            "RU-ZAB" => "Забайкальский край",
            "RU-IRK" => "Иркутская область",
            "RU-TYU" => "Тюменская область",
            "RU-SVE" => "Свердловская область",
            "RU-KHM" => "Ханты-Мансийский автономный округ - Югра",
            "RU-KYA" => "Красноярский край",
            "RU-TOM" => "Томская область",
            "RU-KEM" => "Кемеровская область",
            "RU-KK" => "Республика Хакасия",
            "RU-AD" => "Республика Адыгея",
            "RU-SA" => "Республика Саха (Якутия)",
            "RU-LEN" => "Ленинградская область",
            "RU-KR" => "Республика Карелия",
            "RU-HR" => "Херсонская область",
            "RU-ZP" => "Запорожская область",
            "RU-DON" => "Донецкая Народная Республика",
            "RU-LUG" => "Луганская Народная Республика",
        ];
    }


}