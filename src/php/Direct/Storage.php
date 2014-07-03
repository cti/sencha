<?php

namespace Cti\Sencha\Direct;

use Cti\Storage\Schema;
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

    function save(Schema $schema, Master $master, $modelName, $pk, $data)
    {
        $pk = get_object_vars($pk);
        $modelData = get_object_vars($data->$modelName);
        unset($data->$modelName);
        $repository = $master->getRepository($modelName);
        if(count($pk)) {
            $model = $repository->findByPk($pk);
            $model->merge($modelData);
        } else {
            $model = $repository->create($modelData);
        }
        $model->save();

        $this->saveLinks($schema, $master, $modelName, $pk, $data);

        $master->getDatabase()->commit();
        return array(
            'success' => true
        );
    }

    protected function saveLinks(Schema $schema, Master $master, $model, $pk, $data)
    {
        $data = get_object_vars($data);
        unset($pk['v_end']);
        $makeKey = function ($fields, $data) {
            $items = array();
            foreach($fields as $field) {
                $items[] = $data[$field];
            }
            return implode(':', $items);
        };

        $convertPkForModel = function ($linkName, $pk) use ($schema, $model) {
            $linkModel = $schema->getModel($linkName);
            $parentModel = $schema->getModel($model);
            $reference = $linkModel->getOutReference($parentModel->getName());
            $properties = $reference->getProperties();
            foreach($properties as $property) {
                foreach($pk as $key => $value) {
                    if ($key == $property->getForeignName()) {
                        unset($pk[$key]);
                        $pk[$property->getName()] = $value;
                    }
                }
            }
            return $pk;
        };

        foreach($data as $linkName => $records) {
            $linkPk = $schema->getModel($linkName)->getPk();
            unset($linkPk[array_search('v_end', $linkPk)]);

            $repository = $master->getRepository($linkName);
            $existingLinks = $repository->findAll($convertPkForModel($linkName, $pk));
            $hash = array();
            foreach($existingLinks as $existingLink) {
                $key = $makeKey($linkPk, $existingLink->asArray());
                $hash[$key] = $existingLink;
            }

            foreach($records as $record) {
                $record = get_object_vars($record);
                $key = $makeKey($linkPk, $record);
                if (isset($hash[$key])) {
                    $existingLink = $hash[$key];
                    $existingLink->merge($record);
                    unset($hash[$key]);
                } else {
                    $existingLink = $repository->create($record);
                }
                $existingLink->save();
            }

            foreach($hash as $key => $record) {
                $record->delete();
            }

        }
    }
};