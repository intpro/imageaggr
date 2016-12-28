<?php

namespace Interpro\ImageAggr\Executors;

use Interpro\Core\Contracts\Ref\ARef;

use Interpro\Core\Contracts\Executor\OwnSynchronizer as OwnSynchronizerInterface;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Contracts\Operation\InitOperation;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Exception\ImageAggrException;

class Synchronizer implements OwnSynchronizerInterface
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
     *
     * @return void
     */
    public function sync(ARef $ref, OwnField $own)
    {
        $type = $own->getFieldType();
        $name = $type->getName();
        $mode = $type->getMode();

        if($name !== 'image' or $mode !== TypeMode::MODE_B)
        {
            throw new ImageAggrException('Синхронизатор предназначен для поля типа image(B), передано: '.$name.'('.$mode.')!');
        }

        $imageSetting = $this->settingsSet->getImage($own);

        //Инициализация в картинках работает как синхронизация всегда
        $this->operation->execute($ref, $imageSetting);

    }

}
