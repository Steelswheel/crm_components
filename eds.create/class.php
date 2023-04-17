<?php
namespace Components\Vaganov\EdsCreate;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Vaganov\Helper;

class EdsCreateClass extends \CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function uploadAction($id = 0)
    {
        global $USER;

        $root = $_SERVER['DOCUMENT_ROOT'];
        $dir = 'upload/eds-excel';

        if (!is_dir($root . '/' . $dir)) {
            mkdir($root . '/' . $dir,0777, true);
        }

        $pathNameTmp = $_FILES['file']['tmp_name'];
        $pathName = $_FILES['file']['name'];
        $nameExt = end(explode('.', $pathName));

        $upload = move_uploaded_file($pathNameTmp, $root. '/' . $dir . '/' . 'create' . '.' . $nameExt);

        $tmp = $dir . '/' . 'create' . '.' . $nameExt;

        $_FILES['file']['tmp_name']  = $root . '/' . $tmp;

        return [
            'file' => $_FILES,
            'user_id' =>  $USER->GetID(),
            'tmp' => $root.'/'.$tmp,
            'url' => '/'.$tmp,
            'upload' => $upload,
            'id' => (int)$id
        ];
    }

    public function parseAction($file)
    {
        return (new EdsCreateParser($file))->run();
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}