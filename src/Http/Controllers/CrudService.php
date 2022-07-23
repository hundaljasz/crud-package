<?php

namespace Devjaskirat\crud\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Devjaskirat\crud\repository\CrudRepo;
use Devjaskirat\crud\Models\image;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Database\QueryException;

class CrudService extends Controller
{
    public function __construct(CrudRepo $repo)
    {
        $this->repo = $repo;
    }

    public function checkModelExist($model)
    {
        $Model = 'App\\Models\\' . $model;
        if (class_exists($Model)) {
            return true;
        }
        return false;
    }

    public function store(Request $request, $model=null, $imageDirectory = null, $imageField = null,$imagePrefix = null)
    {
        if($model != null) {
            if ($this->checkModelExist($model)) {
                $newRequest = $request->all();
                $message = $this->repo->store($model, $newRequest);
                if($message['status']) {
                    $this->uploadImages($request,$imageDirectory,$imageField,$imagePrefix,$model,$message['id']);
                    return $message;
                }
                return $message;
            } else {
                return $this->errorReturn('unable to find '. $model .' model.');
            }
        }
        return $this->errorReturn('Please Select a model to insert data.');
    }

    public function generateFileName($name,$prefix){
        if($prefix = null){
            $prefix = 'image';
        }
        $name = str_replace([' ', '&', '|'], ['', '_and_', '_or_'], $name);
        $name = $prefix.time().rand(9,99999).$name;
        return strtolower($name);
    }

    public function singleFile($file, $path, $prefix = null,$model = null,$id = null){
        $fileName = $this->generateFileName($file->getClientOriginalName(),$prefix);
          if($file->isValid()){
            try {
                $file->storeAs('public/uploads/'.$path, $fileName);
                $fileNewPath = 'storage/uploads/'.$path;
                $data        = [
                    'image'    => $fileName,
                    'related_table_id' => $id,
                    'table'    => $model,
                    'path'     => $fileNewPath,
                ];
                $id = $this->repo->storeImage($data);
                return [
                    'status' => true,
                    'type' => 'success',
                    'content' => 'file stored at :- '.$fileNewPath.' with filename :-' . $fileName,
                    'fileName' => $fileName,
                    'id'       => $id,
                    'message' => 'file uploaded successfully!',
                ];
            } catch (\Exception $th) {
                return $this->errorReturn($th->getMessage().' with code '.$th->getCode().' at line no. :-  '.$th->getLine());
            }
        }
        return $this->errorReturn('invalid file');
    }

    public function multipleFile($files,$path, $name = null,$model = null,$id = null){
        $myFiles = [];
        foreach ($files as $key => $file) {
            $imageData   = $this->singleFile($file,$path,$name,$model,$id);
            if($imageData['status']) {
                $myFiles[$key] = $imageData['id'];
            }
        }
        return array_filter($myFiles);
    }

    public function update(Request $request,$id = null, $model=null, $imageDirectory = null, $imageField = null,$imagePrefix = null)
    {
        if($model != null) {
            if ($this->checkModelExist($model)) {
                $Model = 'App\\Models\\' . $model;
                if($id != null) {
                    $checkRecord = $Model::find($id);
                    if(!empty($checkRecord)) {
                        $this->uploadImages($request,$imageDirectory,$imageField,$imagePrefix,$model,$id);
                        $newRequest = $request->except('_token','_method');
                        return $this->repo->update($model, $newRequest,$id);
                    } else {
                        return $this->errorReturn('No record found with id '. $id .'.');
                    }
                } else {
                    return $this->errorReturn('Please provide an id to update the record.');
                }
            } else {
                return $this->errorReturn('unable to find '. $model .' model.');
            }
        } else {
            return $this->errorReturn('Please Select a model to insert data.');
        }
    }

    public function deleteImage($id = null) {
        if($id != null) {
            $imageDetail = image::find($id);
            if(!empty($imageDetail)) {
                $image = str_replace('storage/','public/',$imageDetail->path.'/'.$imageDetail->image);
                if(Storage::exists($image)) {
                    Storage::delete($image);
                    $imageDetail->delete();
                    return [
                        'status' => true,
                        'type' => 'success',
                        'message' => 'image deleted successfully.',
                    ];
                } else {
                    return $this->errorReturn('image does not exist.');
                }
            } else {
                return $this->errorReturn('No Data Available');
            }
        } else {
            return $this->errorReturn('Please select an image to delete.');
        }
    }

    public function delete($id, $model = null,$newIdField = null)
    {
        if($id != null) {
            if ($this->checkModelExist($model)) {
                $Model = 'App\\Models\\' . $model;
                $data = $this->getRecord($id,$model);
                if($data['type'] == 'success') {
                    $message = $this->repo->delete($id,$Model,$newIdField);
                    if($message['status'] == 'success') {
                        $checkImagesRecord = image::where('related_table_id',$message['id'])->where('table',$model)->get();
                        if($checkImagesRecord->count() > 0) {
                            foreach ($checkImagesRecord as $image) {
                                $this->deleteImage($image->id);
                            }
                            image::where('related_table_id',$message['id'])->where('table',$model)->delete();
                            return $message;
                        } else {
                            return $message;
                        }
                    } else {
                        return $message;
                    }
                } else {
                    return $data;
                }
            } else {
                return $this->errorReturn('unable to find App\\Models\\'. $model .' model.');
            }
        } else {
            return $this->errorReturn('Please Select a record to delete data.');
        }
    }

    public function getRecord($id,$model = null,$newIdField = null)
    {
        if($id != null) {
            $Model = 'App\\Models\\' . $model;
            if (class_exists($Model)) {
                try {
                    if($newIdField != null) {
                        $data = $Model::where($newIdField,$id)->get();
                    } else {
                        $data  = $Model::find($id);
                    }
                    if(!empty($data)) {
                        return [
                            'status' => true,
                            'type' => 'success',
                            'data' => $data,
                            'message' => 'Record Found successfully'
                        ];
                    } else {
                        return $this->errorReturn('unable to find a record with id = '.$id);
                    }
                } catch (Exception $err){
                    if($err instanceof QueryException){
                        $errorCode = $err->errorInfo[1];
                        return [
                            'status' => false,
                            'type' => 'fail',
                            'data' => '',
                            'errorCode' => $errorCode,
                            'message' => $err->getMessage(),
                        ];
                    } else {
                        return [
                            'status' => false,
                            'type' => 'fail',
                            'data' => '',
                            'message' => $err->getMessage(),
                        ];
                    }
                }
            } else {
                return $this->errorReturn('unable to find '. $Model .' model.');
            }
        } else {
            return $this->errorReturn('Please Select a record to delete data.');
        }
    }

    public function uploadImages($request,$imageDirectory,$imageField,$imagePrefix,$model,$id)
    {
        $imageData = array();
        if($imageDirectory != null) {
            if(isset($request->$imageField)) {
                if(is_array($request->$imageField)) {
                    $imageData = $this->multipleFile($request->$imageField, $imageDirectory, $imagePrefix,$model,$id);
                } else {
                    $imageData = $this->singleFile($request->$imageField, $imageDirectory, $imagePrefix,$model,$id);
                    if($imageData['status']) {
                        $imageData = [$imageData['id']];
                    }
                }
            }
        }
        return $imageData;
    }

    public function errorReturn($message)
    {
        return [
            'status' => false,
            'type' => 'fail',
            'id' => '',
            'message' => $message,
        ];
    }
}