<?php

namespace Interpro\ImageAggr\Executors;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Executor\AUpdateExecutor;
use Interpro\Core\Contracts\Mediator\UpdateMediator;
use Interpro\Core\Contracts\Ref\ARef as ARefInterface;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesUpdateExecutor implements AUpdateExecutor
{
    private $updateMediator;

    public function __construct(UpdateMediator $updateMediator)
    {
        $this->updateMediator = $updateMediator;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'modimages';
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param array $values
     *
     * @return void
     */
    public function update(ARefInterface $ref, array $values)
    {
        $type = $ref->getType();
        $type_name = $type->getName();
        $mode = $type->getMode();

        if($type_name !== 'modimages' or $mode !== TypeMode::MODE_A)
        {
            throw new ImageAggrException('Модификатор предназначен для изменения блока modimages(A), передано: '.$type_name.'('.$mode.')!');
        }

        //[[[
        DB::beginTransaction();

        $owns = $type->getOwns();

        foreach($owns as $own_name => $own)
        {
            $family = $own->getFieldTypeFamily();
            $mode = $own->getMode();

            if(array_key_exists($own_name, $values))
            {
                $value = $values[$own_name];

                if($mode === TypeMode::MODE_B)
                {
                    $updater = $this->updateMediator->getBUpdateExecutor($family);
                    $updater->update($ref, $own, $value);
                }
                elseif($mode === TypeMode::MODE_C)
                {
                    $updater = $this->updateMediator->getCUpdateExecutor($family);
                    $updater->update($ref, $own, $value);
                }
            }
        }

        DB::commit();
        //]]]
    }
}
