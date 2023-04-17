<?php
namespace Components\Vaganov\PhotoView;

use Bitrix\Main\Engine\Contract\Controllerable;

class PhotoViewClass extends \CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function imgAction($ids)
    {
        $idsAr = explode(',', $ids);

        $images = PhotoView::getImageIds($idsAr);

        return ['img' => $images,];
    }
}