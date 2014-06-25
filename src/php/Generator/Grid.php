<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Grid extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = $this->model->getName();
        $title = $this->model->getComment();

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $columns = array();
        $fields = array();

        $configuration = array();
        
        foreach($this->model->getProperties() as $property) {

            $fields[] = array(
                'name' => $property->getName(), 
                'type' => $property->getJavascriptType()
            );

            $item = array(
                'dataIndex' => $property->getName(),
                'header' => $property->getComment(),
            );
            $columns[] = $item;

            if(!$property->getBehaviour()) {
                $width = 80;
                if($property->getJavascriptType() == 'string') {
                    $width = 120;
                }
                $configuration[$property->getName()] = array(
                    'width' => $width
                );
            }

        }
        $fields = json_encode($fields);
        $columns = json_encode($columns);
        $configuration = json_encode($configuration);

        return <<<COFFEE
Ext.define 'Generated.Grid.$class',

  extend: 'Ext.grid.Panel'

  title: '$title'
  
  store: model: 'Model.$class'
  getAvailableColumns: -> $columns
  getColumnConfiguration: -> $configuration

  requires: ['Model.$class', 'Form.$class']

  initComponent: -> 
    configuration = @getColumnConfiguration()
    @columns = []
    Ext.Array.each @getAvailableColumns(), (column) =>
      if configuration[column.dataIndex]
        @columns.push Ext.apply column, configuration[column.dataIndex]

    @callParent arguments
    Storage.getList '$name', (response) => @store.loadData response.data

COFFEE;

    }
}