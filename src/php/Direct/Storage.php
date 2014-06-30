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

    public function filter($model, $condition, Master $master)
    {
        $data = array();
        foreach($master->getRepository($model)->findAll((array)$condition) as $entity) {
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
        $modelData = get_object_vars($data->$model);
        unset($data->$model);
        $repository = $master->getRepository($model);
        if(count($pk)) {
            $model = $repository->findByPk($pk);
            $model->merge($modelData);
        } else {
            $model = $repository->create($modelData);
        }
        $model->save();

        $this->saveLinks($master, $model, $pk, $data);

        $master->getDatabase()->commit();
        return array(
            'success' => true
        );
    }

    protected function saveLinks(Master $master, $model, $pk, $data)
    {
        // @todo No remove implemented
        foreach(get_object_vars($data) as $key => $links) {
            $repository = $master->getRepository($key);
            foreach($links as $linkData) {
                $link = $repository->findByPk((array)$linkData);
                if ($link) {
                    $link->merge($linkData);
                } else {
                    $link = $repository->create($linkData);
                }
                $link->save();
            }
        }
    }
}