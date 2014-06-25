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
}