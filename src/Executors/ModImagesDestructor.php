<?php

namespace Interpro\ImageAggr\Executors;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Executor\ADestructor;
use Interpro\Core\Contracts\Mediator\DestructMediator;
use Interpro\Core\Contracts\Mediator\RefConsistMediator;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesDestructor implements ADestructor
{
    private $refConsistMediator;
    private $destructMediator;

    public function __construct(RefConsistMediator $refConsistMediator, DestructMediator $destructMediator)
    {
        $this->refConsistMediator = $refConsistMediator;
        $this->destructMediator = $destructMediator;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'modimages';
    }

    private function deleteOwns(ARef $ref)
    {
        $type = $ref->getType();

        //Внешние поля
        $families = [];
        $owns = $type->getOwns();

        foreach($owns as $ownField)
        {
            $own_f_f = $ownField->getFieldTypeFamily();

            if(!in_array($own_f_f, $families))
            {
                continue;
            }

            $families[] = $own_f_f;

            if($ownField->getMode() === TypeMode::MODE_B)
            {
                $destructor = $this->destructMediator->getBDestructor($own_f_f);
                $destructor->delete($ref);
            }
            elseif($ownField->getMode() === TypeMode::MODE_C)
            {
                $destructor = $this->destructMediator->getCDestructor($own_f_f);
                $destructor->delete($ref);
            }
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return void
     */
    public function delete(ARef $ref)
    {
        $type = $ref->getType();
        $name = $type->getName();
        $mode = $type->getMode();

        if($name !== 'modimages' or $mode !== TypeMode::MODE_A)
        {
            throw new ImageAggrException('Деструктор предназначен для удаления блока modimages(A), передано: '.$name.'('.$mode.')!');
        }

        //[[[
        DB::beginTransaction();

        //Удаление внешних собственных полей
        $this->deleteOwns($ref);

        //У блока modimages нет своей записи, так что просто удаляем поля
        //Сообщение ссылающимся, об удалении сущности
        $this->refConsistMediator->notify($ref);

        DB::commit();
        //]]]
    }
}
