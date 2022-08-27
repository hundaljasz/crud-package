<?php

namespace briza\manager\repository;
use briza\manager\Models\image;
use Illuminate\Database\QueryException;
use Exception;

class CrudRepo {
    public function store($model, $data) {
        try {
            $Model = 'App\\Models\\' . $model;
            if (class_exists($Model)) {
                $insert = $Model::create($data);
                return [
                    'status' => true,
                    'type' => 'success',
                    'id' => $insert->id,
                    'data' => $insert,
                    'message' => 'data saved successfully.'
                ];
            } else {
                throw new \Exception('unable to find '. $Model .' model.');
            }
        } catch (\Exception $th) {
            if($th instanceof QueryException) {
                return [
                    'status' => false,
                    'type' => 'fail',
                    'id' => '',
                    'message' => $th->getMessage().' code '.$th->getCode().' at line '.$th->getLine(),
                    'errorCode' => $th->errorInfo[1],
                ];
            } else {
                return [
                    'status' => false,
                    'type' => 'fail',
                    'id' => '',
                    'message' => $th->getMessage().' code '.$th->getCode().' at line '.$th->getLine(),
                ];
            }
        }
    }

    public function update($model, $data, $id) {
        try {
            $Model = 'App\\Models\\' . $model;
            if (class_exists($Model)) {
                $Model::find($id)->update($data);
                return [
                    'status' => true,
                    'type' => 'success',
                    'id' => $id,
                    'data' => $data,
                    'message' => 'data updated successfully.'
                ];
            } else {
                throw new \Exception('unable to find '. $Model .' model.');
            }
        } catch (\Exception $th) {
            if($th instanceof QueryException) {
                return [
                    'status' => false,
                    'type' => 'fail',
                    'id' => '',
                    'message' => $th->getMessage().' code '.$th->getCode().' at line '.$th->getLine(),
                    'errorCode' => $th->errorInfo[1],
                ];
            } else {
                return [
                    'status' => false,
                    'type' => 'fail',
                    'id' => '',
                    'message' => $th->getMessage().' code '.$th->getCode().' at line '.$th->getLine(),
                ];
            }
        }
    }

    public function storeImage($data) {
        try {
            $insert = image::create($data);
            return $insert->id;
        } catch (\Exception $th) {
            return $th;
        }
    }

    public function delete($id,$model)
    {
        try {
            $model::find($id)->delete();
            return [
                'status' => true,
                'type' => 'success',
                'id' => $id,
                'message' => 'Record deleted successfully with id '.$id
            ];
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
    }
}
