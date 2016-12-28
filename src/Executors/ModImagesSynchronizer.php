<?php

namespace Interpro\ImageAggr\Executors;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Executor\ASynchronizer as ASynchronizerInterface;
use Interpro\Core\Contracts\Mediator\SyncMediator;
use Interpro\Core\Contracts\Taxonomy\Types\AType;
use Interpro\Core\Ref\ARef;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesSynchronizer implements ASynchronizerInterface
{
    private $syncMediator;

    public function __construct(SyncMediator $syncMediator)
    {
        $this->syncMediator = $syncMediator;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'modimages';
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\AType $type
     *
     * @return void
     */
    public function sync(AType $type)
    {
        $name = $type->getName();
        $mode = $type->getMode();

        if($name !== 'modimages' or $mode !== TypeMode::MODE_A)
        {
            throw new ImageAggrException('Синхронизатор предназначен для блока modimages(A), передано: '.$name.'('.$mode.')!');
        }

        //[[[
        DB::beginTransaction();

        $synchronizer = $this->syncMediator->getOwnSynchronizer('imageaggr');

        $aRef = new ARef($type, 0);

        $images = $type->getOwns('image');

        foreach($images as $image)
        {
            $synchronizer->sync($aRef, $image);
        }

        DB::commit();
        //]]]
    }
}
