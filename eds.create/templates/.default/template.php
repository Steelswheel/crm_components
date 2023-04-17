<?php
use Bitrix\Main\Loader;

/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */

$this->addExternalCss('/style.css');

global $APPLICATION;
global $USER;

?>

<div class="eds-create">
    <div data-vue-component="eds.create_add-excel"></div>
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
