<?php

namespace Interpro\ImageAggr\Executors;

use Interpro\Core\Contracts\Executor\RefConsistExecutor as RefConsistExecutorInterface;
use Interpro\Core\Contracts\Ref\ARef;

class ModImagesRefConsistExecutor implements RefConsistExecutorInterface
{
    /**
     * @return string
     */
    public function getFamily()
    {
        return 'modimages';
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return void
     */
    public function execute(ARef $ref)
    {
        //Ссылок на сущьности А типа в этом типе нет
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return bool
     */
    public function exist(ARef $ref)
    {
        $name = $ref->getType()->getName();

        if($name === 'modimages')
        {
            return true;
        }

        return false;
    }
}
