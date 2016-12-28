<?php

namespace Interpro\ImageAggr\Executors;

use Interpro\Core\Contracts\Executor\BUpdateExecutor;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\ImageAggr\Contracts\Operation\SaveOperation;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;

class UpdateExecutor implements BUpdateExecutor
{
    private $saveOperation;
    private $settingsSet;

    public function __construct(SaveOperation $saveOperation, ImageSettingsSet $settingsSet)
    {
        $this->saveOperation = $saveOperation;
        $this->settingsSet = $settingsSet;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $own
     * @param mixed $user_attrs
     *
     * @return void
     */
    public function update(ARef $ref, OwnField $own, $user_attrs)
    {
        if(!is_array($user_attrs))
        {
            $user_attrs = [];
        }

        $imageSetting = $this->settingsSet->getImage($own);

        $this->saveOperation->execute($ref, $imageSetting, $user_attrs);
    }
}
