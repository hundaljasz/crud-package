<?php

namespace briza\manager\managerService;

use Exception;
use briza\manager\Models\image;
use briza\manager\repository\CrudRepo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class baseManager {

    public static function errorReturn($message)
    {
        return [
            'status' => false,
            'type' => 'fail',
            'id' => '',
            'message' => $message,
        ];
    }

    public static function insert($request, $model=null, $imageDirectory = null,$imagePrefix = null) {
        if($model != null) {
            $newRequest = $request->all();
            $repoObject = new CrudRepo();
            $message = $repoObject->store($model, $newRequest);
            if($message['status']) {
                static::uploadImages($request,$imageDirectory,$imagePrefix,$model,$message['id']);
                return $message;
            }
            return $message;
            
        }
        return static::errorReturn('Please Select a model to insert data.');
    }

    public static function generateFileName($name,$prefix){
        if($prefix = null){
            $prefix = 'image';
        }
        $name = str_replace([' ', '&', '|'], ['', '_and_', '_or_'], $name);
        $name = $prefix.time().rand(9,99999).$name;
        return strtolower($name);
    }

    public static function singleFile($file, $path, $prefix = null,$model = null,$id = null){
        $fileName = static::generateFileName($file->getClientOriginalName(),$prefix);
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
                $repoObject = new CrudRepo();
                $id         = $repoObject->storeImage($data);
                return [
                    'status' => true,
                    'type' => 'success',
                    'content' => 'file stored at :- '.$fileNewPath.' with filename :-' . $fileName,
                    'fileName' => $fileName,
                    'id'       => $id,
                    'message' => 'file uploaded successfully!',
                ];
            } catch (\Exception $th) {
                return static::errorReturn($th->getMessage().' with code '.$th->getCode().' at line no. :-  '.$th->getLine());
            }
        }
        return static::errorReturn('invalid file');
    }

    public static function multipleFile($files,$path, $name = null,$model = null,$id = null){
        $myFiles = [];
        foreach ($files as $key => $file) {
            $imageData   = static::singleFile($file,$path,$name,$model,$id);
            if($imageData['status']) {
                $myFiles[$key] = $imageData['id'];
            }
        }
        return array_filter($myFiles);
    }

    public static function uploadImages($request,$imageDirectory,$imagePrefix,$model,$id)
    {
        $imageData = array();
        $images    = $request->file();
        foreach ($images as $key => $value) {
            $imageField = $key;
            if($imageDirectory != null) {
                if(isset($request->$imageField)) {
                    if(is_array($request->$imageField)) {
                        $imageData = static::multipleFile($request->$imageField, $imageDirectory, $imagePrefix,$model,$id);
                    } else {
                        $imageData = static::singleFile($request->$imageField, $imageDirectory, $imagePrefix,$model,$id);
                        if($imageData['status']) {
                            $imageData = [$imageData['id']];
                        }
                    }
                }
            }
        }
        return $imageData;
    }

    public static function deletefile($id = null) {
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
                return static::errorReturn('image does not exist.');
            }
        } else {
            return static::errorReturn('No Data Available');
        }
    }

    public function deleteRecord($id, $model = null)
    {
        $Model = 'App\\Models\\' . $model;
        $data = $this->getRecord($id,$model);
        if($data['type'] == 'success') {
            $repoObject = new CrudRepo();
            $message = $repoObject->delete($id,$Model);
            if($message['status'] == 'success') {
                $checkImagesRecord = image::where('related_table_id',$message['id'])->where('table',$model)->get();
                if($checkImagesRecord->count() > 0) {
                    foreach ($checkImagesRecord as $image) {
                        static::deletefile($image->id);
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
    }

    public function getRecord($id,$model = null)
    {
        $Model = 'App\\Models\\' . $model;
        if (class_exists($Model)) {
            try {
                $data  = $Model::find($id);
                if(!empty($data)) {
                    return [
                        'status' => true,
                        'type' => 'success',
                        'data' => $data,
                        'message' => 'Record Found successfully'
                    ];
                } else {
                    return static::errorReturn('unable to find a record with id = '.$id);
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
            return static::errorReturn('unable to find '. $Model .' model.');
        }
    }

    public static function updateRecord($request,$id = null, $model=null, $imageDirectory = null,$imagePrefix = null)
    {
        $Model = 'App\\Models\\' . $model;
        if($id != null) {
            $checkRecord  = $Model::find($id);
            if(!empty($checkRecord)) {
                static::uploadImages($request,$imageDirectory,$imagePrefix,$model,$id);
                $newRequest = $request->except('_token','_method');
                $repoObject = new CrudRepo();
                return $repoObject->update($model, $newRequest,$id);
            } else {
                return static::errorReturn('No record found with id '. $id .'.');
            }
        } else {
            return static::errorReturn('Please provide an id to update the record.');
        }
    }
}