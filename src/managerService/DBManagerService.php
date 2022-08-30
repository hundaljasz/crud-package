<?php

namespace briza\manager\managerService;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use briza\manager\managerService\baseManager;
use Exception;
use Illuminate\Validation\ValidationException;

abstract class DBManagerService extends baseManager
{

    abstract protected function validate();
    // abstract protected function prepareData(Request $request);

    public function checkModelExist($model)
    {
        $Model = 'App\\Models\\' . $model;
        if (class_exists($Model)) {
            return true;
        }
        return false;
    }

    public function store($request)
    {
        if(!$this->checkModelExist(static::$model)) {
            return static::errorReturn('unable to find '. static::$model .' model.');
        }
        $rules = $this->validate();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if($request->ajax()) {
                return static::errorReturn($validator->errors());
            } else {
                return back()
                ->withErrors($validator)
                ->withInput();
            }
        }
        return static::insert($request, static::$model,static::$imageDirectory,static::$imagePrefix);
    }

    public function deleteImage($id = null) {
        if($id != null) {
            return static::deletefile($id);
        } else {
            return static::errorReturn('Please select an image to delete.');
        }
    }

    public function delete($id)
    {
        if($id != null) {
            if(!$this->checkModelExist(static::$model)) {
                return static::errorReturn('unable to find '. static::$model .' model.');
            }
            return static::deleteRecord($id,static::$model);
        } else {
            return static::errorReturn('Please Select a record to delete data.');
        }
    }

    public function uploadImage($request,$id) {
        return static::uploadImages($request,static::$imageDirectory,static::$imagePrefix,static::$model,$id);
    }

    public function update($request,$id)
    {
        if($id != null) {
            if(!$this->checkModelExist(static::$model)) {
                return static::errorReturn('unable to find '. static::$model .' model.');
            }
            $rules = $this->validate();
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                if($request->ajax()) {
                    return response($validator->errors(),500);
                } else {
                    return back()
                    ->withErrors($validator)
                    ->withInput();
                }
            }
            return static::updateRecord($request,$id,static::$model,static::$imageDirectory,static::$imagePrefix);
        } else {
            return static::errorReturn('Please provide an id to update the record.');
        }
    }
}
