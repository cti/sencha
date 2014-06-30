<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Window extends Generator
{
    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $title = $this->model->getComment();

        $name = $this->model->getName();

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
  closable: true
  layout: 'fit'
  getPk: -> $pk_getter

  title: '$title'

  initComponent: ->
    @bbar = @getBottomToolbar()
    form = Ext.create 'Form.$class', @getPk()
    @items = @getTabConfig form
    @callParent arguments
    @show()

    @on 'close', => @grid.loadData()

  getTabConfig: (form) ->
    xtype: 'tabpanel'
    items: [
      title: 'Форма'
      items: [form]
    ]

  getBottomToolbar: ->
    [
      text:'Save'
      handler: =>
        form = @down 'form'
        pk = if form.modelExists() then form.getPk() else {}
        Storage.save '$name', pk, form.getForm().getValues(), (response) => @close() if response.success
      '->'
      text:'Close'
      handler: => @close()
    ]

COFFEE;

    }
}