<?php
    use Bitrix\Main\Application;

    $request = Application::getInstance()->getContext()->getRequest();

    CJSCore::Init(['ajax', 'window']);


    if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
        echo "<script src='/local/components/vaganov/dist/main.bundle.js'></script>";
    } else {
        $str = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/local/components/vaganov/dist/main.html");
        $re = '/<head>(.+)<\/head>.+(<script .+><\/script>)/m';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $link = $matches[0][1];
        $script = $matches[0][2];
        echo "$link$script";
    }
?>

<div data-vue-component='sber.info_sber-info'></div>