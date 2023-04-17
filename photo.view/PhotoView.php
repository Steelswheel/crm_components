<?php
namespace Components\Vaganov\PhotoView;

class PhotoView
{
    public static function getImageIds($ids)
    {
        $images = [];

        foreach ($ids as $itemFileId) {
            $f = \CFile::GetByID($itemFileId)->arResult[0];

            $src = \CFile::GetPath($itemFileId);

            if (\CFile::IsImage($src)) {
                $delAndDes = json_decode($f['DESCRIPTION'],1) ?: ['del' => 0, 'des' => ''];

                $image = \CFile::ResizeImageGet($itemFileId, ['width' => 200, 'height' => 200], BX_RESIZE_IMAGE_EXACT, true);

                $img['get_src'] = \CFile::GetPath($itemFileId);
                $img['img'] = $image['src'];
                $img['del'] = $delAndDes['del'];
                $img['des'] = $delAndDes['des'];
                $img['src'] = $src;

                $images[] = $img;

            }
        }

        return $images;
    }
}