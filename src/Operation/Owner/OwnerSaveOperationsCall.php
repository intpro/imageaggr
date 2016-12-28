<?php

namespace Interpro\ImageAggr\Operation\Owner;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\SaveOperation as SaveOperationInterface;
use Interpro\ImageAggr\Contracts\Operation\Owner\OwnerSaveOperationsCall as OwnerSaveOperationsCallInterface;

class OwnerSaveOperationsCall implements OwnerSaveOperationsCallInterface
{
    private $saveImage;

    public function __construct(SaveOperationInterface $saveImage)
    {
        $this->saveImage = $saveImage;
    }

    public function execute(ARef $aRef, array $user_attrs = [])
    {
        $ownerType = $aRef->getType();
        $fields = $ownerType->getOwns()->getTyped('image');

        foreach($fields as $imageField)
        {
            $image_name = $imageField->getName();

            if(array_key_exists($image_name, $user_attrs))//Только если есть что сохранять для этой картинки
            {
                $this->saveImage->execute($aRef, $imageField, $user_attrs[$image_name]);
            }
        }
    }

}
