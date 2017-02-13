<?php

namespace Interpro\ImageAggr\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Interpro\ImageAggr\Contracts\CommandAgents\OperationsAgent;
use Interpro\ImageAggr\Contracts\Settings\PathResolver;

class ImageOperationController extends Controller
{
    private $operationsAgent;
    private $pathResolver;

    public function __construct(OperationsAgent $operationsAgent, PathResolver $pathResolver)
    {
        $this->operationsAgent = $operationsAgent;
        $this->pathResolver = $pathResolver;
    }

    public function testpage()
    {
        return view('imagetest');
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
                return ['error'=>true, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');

            if($request->has('entity_id'))
            {
                $entity_id = (int) $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $this->operationsAgent->$operation($entity_name, $entity_id, $image_name);

            return ['error'=>false]; //какой-то item мимо экстрактора
        }
        catch(\Exception $e)
        {
            return ['error'=>true, $e->getMessage()];
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
                return ['error'=>true, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');
            $crop_name = $request->input('crop_name');
            $x = $request->input('x');
            $y = $request->input('y');

            if($request->has('entity_id'))
            {
                $entity_id = (int) $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $this->operationsAgent->crop($entity_name, $entity_id, $image_name, $crop_name, ['x' => $x, 'y' => $y]);

            return ['error'=>false];
        }
        catch(\Exception $e)
        {
            return ['error'=>true, $e->getMessage()];
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
                    'image_file' => 'required|image|max:5120',
                ]
            );

            if($validator->fails()){
                return ['error'=>true, 'error'=>$validator->errors()->setFormat(':message<br>')->all()];
            }

            $entity_name = $request->input('entity_name');
            $image_name = $request->input('image_name');
            $image_file = $request->file('image_file');

            if($request->has('entity_id'))
            {
                $entity_id = (int) $request->input('entity_id');
            }
            else
            {
                $entity_id = 0;
            }

            $ext = $image_file->guessClientExtension();
            $resize_file_path = $this->pathResolver->getResizeTmpPath().'/'.$entity_name.'_'.$entity_id.'_'.$image_name.'_preview'.'.'.$ext.'?'.rand(1, 1000);

            $this->operationsAgent->upload($entity_name, $entity_id, $image_name, $image_file);

            return ['error'=>false, 'preview' => $resize_file_path];
        }
        catch(\Exception $e)
        {
            return ['error'=>true, $e->getMessage()];
        }
    }
}
