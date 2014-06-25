<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Grid extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = json_encode($this->model->getName());

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $columns = array();
        $fields = array();
        
        foreach($this->model->getProperties() as $property) {

            $fields[] = array(
                'name' => $property->getName(), 
                'type' => $property->getJavascriptType()
            );

            if($property->getBehaviour()) {
                continue;
            }

            $item = array(
                'dataIndex' => $property->getName(),
                'header' => $property->getComment(),
            );

            $columns[] = $item;
        }
        $fields = json_encode($fields);
        $columns = json_encode($columns);

        return <<<COFFEE
Ext.define 'Generated.Grid.$class',

  extend: 'Ext.grid.Panel'
  store: fields: $fields
  columns: $columns

  initComponent: -> 
    @callParent arguments
    Storage.getList '$name', (response) => @store.loadData response.data

COFFEE;

    }
}