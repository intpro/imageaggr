<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\CleanPhOperation as CleanPhOperationInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

class CleanPhOperation extends Operation implements CleanPhOperationInterface
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

        $original_file_path = $this->phAgent->getPh(
            $imageSetting->getWidth(),
            $imageSetting->getHeight(),
            $imageSetting->getColor()
        );

        $this->dbAgent->imageToDb($aRef, $imageSetting, ['link' => $original_file_path]);

        $this->makePhImageResizes($aRef, $imageSetting);
        $this->makePhImageCrops($aRef, $imageSetting);
    }
}
