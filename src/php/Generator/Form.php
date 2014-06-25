<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Form extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = $this->model->getName();

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

            if($property->getBehaviour()) {
                $item['readOnly'] = true;
                $item['disabled'] = true;
            }

            if($property->getJavascriptType() == 'numeric') {
              $item['xtype'] = 'numberfield';
            } 

            $items[] = $item;
        }
        $items = json_encode($items);

        $pk_getter = array();
        foreach($pk as $key) {
          $pk_getter[] = $key . ": @" . $key ;
        }
        $pk_getter = implode(', ', $pk_getter);


        return <<<COFFEE
Ext.define 'Generated.Form.$class',

  extend: 'Ext.form.Panel'
  bodyPadding: 10
  monitorValid: true
  border: false

  getPk: -> $pk_getter

  items: $items

  getBottomToolbar: ->
    [
      text:'Save'
      handler: => 
        pk = if @modelExists() then @getPk() else {}
        Storage.save '$name', pk, @getForm().getValues(), (response) => @up('window').close() if response.success
      '->'
      text:'Close'
      handler: => @up('window').close()
    ]

  modelExists: -> !Ext.Array.contains(Ext.Object.getValues(@getPk()), undefined)

  initComponent: ->
    @bbar = @getBottomToolbar()
    @callParent arguments

    if @modelExists()
      Storage.getModel '$name', @getPk(), (response) => @getForm().loadRecord Ext.create 'Model.$class', response.data
COFFEE;

    }
}