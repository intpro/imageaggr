<?php

namespace Interpro\ImageAggr\Operation\Owner;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\InitOperation as InitOperationInterface;
use Interpro\ImageAggr\Contracts\Operation\Owner\OwnerInitOperationsCall as OwnerInitOperationsCallInterface;

class OwnerInitOperationsCall implements OwnerInitOperationsCallInterface
{
    private $initImage;

    public function __construct(InitOperationInterface $initImage)
    {
        $this->initImage = $initImage;
    }

    public function execute(ARef $aRef, array $user_attrs = [])
    {
        $ownerType = $aRef->getType();
        $fields = $ownerType->getOwns()->getTyped('image');

        foreach($fields as $imageField)
        {
            $image_name = $imageField->getName();

            if(array_key_exists($image_name, $user_attrs))
            {
                $image_attrs = $user_attrs[$image_name];
            }
            else
            {
                $image_attrs = [];
            }

            $this->initImage->execute($aRef, $imageField, $image_attrs);
        }
    }

}
