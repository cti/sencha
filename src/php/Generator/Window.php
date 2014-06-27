<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Window extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $title = $this->model->getComment();

        $pk_getter = array();
        foreach($this->model->getPk() as $key) {
          $pk_getter[] = $key . ": @" . $key ;
        }
        $pk_getter = implode(', ', $pk_getter);

        return <<<COFFEE
Ext.define 'Generated.Window.$class',

  extend: 'Ext.window.Window'
  modal: true
  draggable: false
  resizable: false
  closable: false
  layout: 'fit'
  getPk: -> $pk_getter

  closeOnEsc: true

  title: '$title'

  initComponent: ->
    @items = [Ext.create 'Form.$class', @getPk()]
    @callParent arguments
    @show()

    @on 'close', => @grid.loadData()
COFFEE;

    }
}