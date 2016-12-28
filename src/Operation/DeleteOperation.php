<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\DeleteOperation as DeleteOperationInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

class DeleteOperation extends Operation implements DeleteOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting)
    {
        $this->checkOwner($aRef);

        $this->deleteAllFiles($aRef, $imageSetting);

        $this->dbAgent->deleteImage($aRef, $imageSetting);

        $resizes = $imageSetting->getResizes();
        foreach($resizes as $resizeSetting)
        {
            $this->dbAgent->deleteResize($aRef, $imageSetting, $resizeSetting);
        }

        $crops = $imageSetting->getCrops();
        foreach($crops as $cropSetting)
        {
            $this->dbAgent->deleteCrop($aRef, $imageSetting, $cropSetting);
        }
    }

}
