<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Model extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = json_encode($this->model->getName());

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $fields = array();
        foreach($this->model->getProperties() as $property) {
            $fields[] = array('name' => $property->getName(), 'type' => $property->getJavascriptType());
        }
        $fields = json_encode($fields);

        $pk_getter = array();
        foreach($pk as $key) {
          $pk_getter[] = $key . ": @get('" . $key . "')";
        }
        $pk_getter = implode(', ', $pk_getter);

        return <<<COFFEE
Ext.define 'Generated.Model.$class',

  extend: 'Ext.data.Model'
  name: $name
  idProperty: $idProperty
  getPk: -> $pk_getter

  fields: $fields
COFFEE;

    }
}