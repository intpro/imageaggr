<?php

namespace Interpro\ImageAggr\Executors;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Executor\AInitializer;
use Interpro\Core\Contracts\Mediator\InitMediator;
use Interpro\Core\Contracts\Taxonomy\Types\AType;
use Interpro\Core\Ref\ARef;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesInitializer implements AInitializer
{
    private $initMediator;

    public function __construct(InitMediator $initMediator)
    {
        $this->initMediator = $initMediator;
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
     * @param array $defaults
     *
     * @return \Interpro\Core\Contracts\Ref\ARef
     */
    public function init(AType $type, array $defaults = [])
    {
        $type_name = $type->getName();
        $mode = $type->getMode();

        if($type_name !== 'modimages' or $mode !== TypeMode::MODE_A)
        {
            throw new ImageAggrException('Инициализатор предназначен для инициализации блока modimages(A), передано: '.$type_name.'('.$mode.')!');
        }

        //[[[
        //У блока modimages нет своей записи с предопределенными полями и нет полей ссылок, инициализируем только собственные поля

        DB::beginTransaction();

        $Aref = new ARef($type, 0);
        //----------------------------------------------------------------------
        $owns = $type->getOwns();

        foreach($owns as $own_name => $own)
        {
            $family = $own->getFieldTypeFamily();
            $mode = $own->getMode();

            if(array_key_exists($own_name, $defaults))
            {
                $value = $defaults[$own_name];
            }
            else
            {
                $value = null;
            }

            if($mode === TypeMode::MODE_B)
            {
                $initializer = $this->initMediator->getBInitializer($family);
                $initializer->init($Aref, $own, $value);
            }
            elseif($mode === TypeMode::MODE_C)
            {
                $initializer = $this->initMediator->getCInitializer($family);
                $initializer->init($Aref, $own, $value);
            }
        }

        DB::commit();
        //]]]

        return $Aref;
    }
}
