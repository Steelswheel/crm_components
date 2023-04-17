<?php
use Bitrix\Main\Loader;

/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

Loader::IncludeModule('crm');
$this->addExternalCss('/style.css');

global $APPLICATION;
global $USER;

CJSCore::Init(['ajax', 'window']);

?>

<div class="lk-settings">
    <div
        data-vue-component="lk_lk"
        data-is-admin="<?= $USER->isAdmin() ?>"
        data-user-id="<?= (int)$USER->GetID() ?>"
    ></div>
</div>

<?php
    if (isset($_SERVER["HTTP_X_FORWARDED_PORT"])) {
        echo "<script src='/local/components/vaganov/dist/main.bundle.js'></script>";
    } else {
        $str = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/local/components/vaganov/dist/main.html");
        $re = '/<head>(.+)<\/head>.+(<script .+><\/script>)/m';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $vueLink = $matches[0][1];
        $vueScript = $matches[0][2];
        echo "$vueLink";
        echo "$vueScript";
    }
?>