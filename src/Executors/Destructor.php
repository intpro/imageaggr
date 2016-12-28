<?php

namespace Interpro\ImageAggr\Executors;

use Interpro\Core\Contracts\Executor\BDestructor;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\Owner\OwnerDeleteOperationsCall;

class Destructor implements BDestructor
{
    private $operation;

    public function __construct(OwnerDeleteOperationsCall $operation)
    {
        $this->operation = $operation;
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
     *
     * @return void
     */
    public function delete(ARef $ref)
    {
        $this->operation->execute($ref);
    }
}
