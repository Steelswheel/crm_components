<?php
namespace Components\Vaganov\Lk;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;

Loader::IncludeModule('crm');

class Lk extends \CBitrixComponent implements Controllerable
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    public function changeSortAction($id, $sort)
    {
        return (new Instructions())->changeSort($id, $sort);
    }

    public function getInstructionsAction()
    {
        return (new Instructions())->getInstructions();
    }

    public function setAuthorAction($id, $user_id)
    {
        return (new Instructions())->setAuthor($id, $user_id);
    }

    public function changeInstructionStateAction($id, $state)
    {
        return (new Instructions())->changeInstructionState($id, $state);
    }

    public function addInstructionAction($text, $title)
    {
        return (new Instructions())->addInstruction($text, $title);
    }

    public function deleteInstructionAction($id)
    {
        return (new Instructions())->deleteInstruction($id);
    }

    public function changeInstructionAction($id, $text, $title)
    {
        return (new Instructions())->changeInstruction($id, $text, $title);
    }

    public function addImageInstructionsAction()
    {
        return (new Instructions())->addImage();
    }



    public function updateRowAction($id)
    {
        return (new News())->getItem($id);
    }

    public function addNewsItemAction($text, $title)
    {
        return (new News())->addNewsItem($text, $title);
    }

    public function addImageNewsAction($id)
    {
        return (new News())->addImage($id);
    }

    public function deleteNewsImageAction($id, $fileId)
    {
        return (new News())->deleteImage($id, $fileId);
    }

    public function rowsAction($currentPage, $pageSize)
    {
        return (new News())->getItems($currentPage, $pageSize);
    }

    public function deleteNewsItemAction($id)
    {
        return (new News())->deleteNewsItem($id);
    }

    public function changeNewsPublicationDateAction($id, $date)
    {
        return (new News())->changePublicationDate($id, $date);
    }

    public function updateNewsItemAction($id, $text, $title)
    {
        return (new News())->updateNewsItem($id, $text, $title);
    }

    public function changeNewsItemStateAction($id, $state)
    {
        return (new News())->changeNewsItemState($id, $state);
    }



    public function promotionsRowsAction($currentPage, $pageSize)
    {
        return (new Promotions())->getItems($currentPage, $pageSize);
    }

    public function promotionsUpdateRowAction($id)
    {
        return (new Promotions())->getItem($id);
    }

    public function addPromotionsItemAction($text, $title)
    {
        return (new Promotions())->addPromotionsItem($text, $title);
    }

    public function addImagePromotionsAction($id)
    {
        return (new Promotions())->addImage($id);
    }

    public function deletePromotionsImageAction($id, $fileId)
    {
        return (new Promotions())->deleteImage($id, $fileId);
    }

    public function deletePromotionsItemAction($id)
    {
        return (new Promotions())->deletePromotionsItem($id);
    }

    public function changePromotionsPublicationDateAction($id, $date)
    {
        return (new Promotions())->changePublicationDate($id, $date);
    }

    public function updatePromotionsItemAction($id, $text, $title)
    {
        return (new Promotions())->updatePromotionsItem($id, $text, $title);
    }

    public function changePromotionsItemStateAction($id, $state)
    {
        return (new Promotions())->changePromotionsItemState($id, $state);
    }


    public function sberNewsRowsAction($currentPage, $pageSize)
    {
        return (new SberNews())->getItems($currentPage, $pageSize);
    }

    public function sberNewsUpdateRowAction($id)
    {
        return (new SberNews())->getItem($id);
    }

    public function addSberNewsItemAction($text, $title)
    {
        return (new SberNews())->addSberNewsItem($text, $title);
    }

    public function addImageSberNewsAction($id)
    {
        return (new SberNews())->addImage($id);
    }

    public function deleteSberNewsImageAction($id, $fileId)
    {
        return (new SberNews())->deleteImage($id, $fileId);
    }

    public function deleteSberNewsItemAction($id)
    {
        return (new SberNews())->deleteSberNewsItem($id);
    }

    public function changeSberNewsPublicationDateAction($id, $date)
    {
        return (new SberNews())->changePublicationDate($id, $date);
    }

    public function updateSberNewsItemAction($id, $text, $title)
    {
        return (new SberNews())->updateSberNewsItem($id, $text, $title);
    }

    public function changeSberNewsItemStateAction($id, $state)
    {
        return (new SberNews())->changeSberNewsItemState($id, $state);
    }


    function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}