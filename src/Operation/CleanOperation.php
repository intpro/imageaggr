<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\CleanOperation as CleanOperationInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

class CleanOperation extends Operation implements CleanOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting)
    {
        $this->checkOwner($aRef);

        $this->deleteAllFiles($aRef, $imageSetting);

        $this->dbAgent->imageToDb($aRef, $imageSetting, ['link' => '']);

        $this->makeSameImageResizes($aRef, $imageSetting, '');
        $this->makeSameImageCrops($aRef, $imageSetting, '');
    }
}
