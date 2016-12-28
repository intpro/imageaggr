<?php

namespace Interpro\ImageAggr\Executors;

use Interpro\Core\Contracts\Executor\BInitializer;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\ImageAggr\Contracts\Operation\InitOperation;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;

class Initializer implements BInitializer
{
    private $operation;
    private $settingsSet;

    public function __construct(InitOperation $operation, ImageSettingsSet $settingsSet)
    {
        $this->operation = $operation;
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
    public function init(ARef $ref, OwnField $own, $user_attrs = null)
    {
        if(!is_array($user_attrs))
        {
            $user_attrs = [];
        }

        $imageSetting = $this->settingsSet->getImage($own);

        $this->operation->execute($ref, $imageSetting, $user_attrs);
    }
}
