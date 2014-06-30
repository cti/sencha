<?php

namespace Cti\Sencha\Direct;

use Storage\Master;

class Storage
{
    function getList($model, Master $master)
    {
        $data = array();

        foreach($master->getRepository($model)->findAll() as $entity) {
            $data[] = $entity->asArray();
        }

        return array(
            'data' => $data
        );
    }

    function getModel(Master $master, $model, $pk) {
        return array(
            'data' => $master->getRepository($model)->findByPk(get_object_vars($pk))->asArray()
        );
    }

    function remove(Master $master, $model, $pk)
    {
        $pk = get_object_vars($pk);
        $master->getRepository($model)->findByPk($pk)->delete();
        $master->getDatabase()->commit();
        return array(
            'success' => true
        );
    }

    function save(Master $master, $model, $pk, $data)
    {
        $pk = get_object_vars($pk);
        $data = get_object_vars($data->$model);
        $repository = $master->getRepository($model);
        if(count($pk)) {
            $model = $repository->findByPk($pk);
            $model->merge($data);
        } else {
            $model = $repository->create($data);
        }
        $model->save();
        $master->getDatabase()->commit();
        return array(
            'success' => true
        );
    }
}