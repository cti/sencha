<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Form extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = json_encode($this->model->getName());

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $items = array();
        foreach($this->model->getProperties() as $property) {

            $item = array(
                'name' => $property->getName(), 
                'allowBlank' => !!$property->getRequired(),
                'fieldLabel' => $property->getComment(),
            );

            switch($property->getJavascriptType()) {
                case 'date': 
                    $item['xtype'] = 'datefield';
                    break;
                case 'numeric': 
                    $item['xtype'] = 'numberfield';
                    break;
                default:
                    $item['xtype'] = 'textfield';
                    break;
            }

            if($property->getJavascriptType() == 'numeric') {
              $item['xtype'] = 'numberfield';
            } 

            $items[] = $item;
        }
        $items = json_encode($items);
        return <<<COFFEE
Ext.define 'Generated.Form.$class',

  extend: 'Ext.form.Panel'
  bodyPadding: 10
  monitorValid:true

  items: $items
COFFEE;

    }
}