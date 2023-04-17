<?php
use Bitrix\Main\Application;
global $APPLICATION;
?>

    <div data-vue-component="document.generate_bx-table"></div>

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