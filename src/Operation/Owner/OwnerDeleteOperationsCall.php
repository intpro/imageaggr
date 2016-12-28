<?php

namespace Interpro\ImageAggr\Operation\Owner;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\DeleteOperation as DeleteOperationInterface;
use Interpro\ImageAggr\Contracts\Operation\Owner\OwnerDeleteOperationsCall as OwnerDeleteOperationsCallInterface;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;

class OwnerDeleteOperationsCall implements OwnerDeleteOperationsCallInterface
{
    private $deleteImage;
    private $settingsSet;

    public function __construct(DeleteOperationInterface $deleteImage, ImageSettingsSet $settingsSet)
    {
        $this->deleteImage = $deleteImage;
        $this->settingsSet = $settingsSet;
    }

    public function execute(ARef $aRef)
    {
        $ownerType = $aRef->getType();
        $fields = $ownerType->getOwns()->getTyped('image');

        foreach($fields as $imageField)
        {
            $imageSetting = $this->settingsSet->getImage($imageField);
            $this->deleteImage->execute($aRef, $imageSetting);
        }
    }

}
