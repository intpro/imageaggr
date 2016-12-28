<?php

namespace Interpro\ImageAggr\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Interpro\ImageAggr\Contracts\CommandAgents\OperationsAgent;

class ImageOperationController extends Controller
{
    private $operationsAgent;

    public function __construct(OperationsAgent $operationsAgent)
    {
        $this->operationsAgent = $operationsAgent;
    }

    private function same(Request $request, $operation)
    {
        try
        {
            $validator = Validator::make(
                $request->all(),
                [
                    'entity_name' => 'required',
                    'image_name' => 'required',
                    'entity_id' => 'integer|min:0'
                ]
            );

            if($validator->fails()){
                return ['status'=>false, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');

            if($request->has('entity_id'))
            {
                $entity_id = $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $item = $this->operationsAgent->$operation($entity_name, $entity_id, $image_name);

            return ['status'=>true, 'item'=>$item]; //какой-то item мимо экстрактора
        }
        catch(\Exception $e)
        {
            return ['status'=>false, $e->getMessage()];
        }
    }

    public function clean(Request $request)
    {
        return $this->same($request, 'clean');
    }

    public function cleanToPh(Request $request)
    {
        return $this->same($request, 'cleanToPh');
    }

    public function refresh(Request $request)
    {
        return $this->same($request, 'refresh');
    }

    public function crop(Request $request)
    {
        try
        {
            $validator = Validator::make(
                $request->all(),
                [
                    'entity_name' => 'required',
                    'image_name' => 'required',
                    'entity_id' => 'integer|min:0',
                    'crop_name' => 'required',
                    'x' => 'required|integer',
                    'y' => 'required|integer'
                ]
            );

            if($validator->fails()){
                return ['status'=>false, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');
            $crop_name = $request->input('crop_name');
            $x = $request->input('x');
            $y = $request->input('y');

            if($request->has('entity_id'))
            {
                $entity_id = $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $item = $this->operationsAgent->crop($entity_name, $entity_id, $image_name, $crop_name, ['x' => $x, 'y' => $y]);

            return ['status'=>true, 'item'=>$item];
        }
        catch(\Exception $e)
        {
            return ['status'=>false, $e->getMessage()];
        }
    }

    public function upload(Request $request)
    {
        try
        {
            $validator = Validator::make(
                $request->all(),
                [
                    'entity_name' => 'required',
                    'image_name' => 'required',
                    'entity_id' => 'integer|min:0',
                    'crop_name' => 'required',
                    'image_file' => 'required|image|max:5120',
                ]
            );

            if($validator->fails()){
                return ['status'=>false, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');
            $image_file = $request->file('image_file');

            if($request->has('entity_id'))
            {
                $entity_id = $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $item = $this->operationsAgent->upload($entity_name, $entity_id, $image_name, $image_file);

            return ['status'=>true, 'item'=>$item];
        }
        catch(\Exception $e)
        {
            return ['status'=>false, $e->getMessage()];
        }
    }
}
