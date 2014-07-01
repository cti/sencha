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

        $getDataCode = $this->getDataCode();
        $recordLoadedCode = $this->getRecordLoadedCode();
        $tabConfigModifyCode = $this->getTabConfigModifyCode();
        $requires = $this->getRequires();

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
$recordLoadedCode


  getTabConfig: (form) ->
    config =
      xtype: 'tabpanel'
      items: [
        title: 'Форма'
        items: [form]
      ]
$tabConfigModifyCode
    config

  getBottomToolbar: ->
    [
      text:'Save'
      handler: =>
        form = @down 'form'
        pk = if form.modelExists() then form.getPk() else {}
        Storage.save '$name', pk, @getData(), (response) => @close() if response.success
      '->'
      text:'Close'
      handler: => @close()
    ]

$getDataCode

COFFEE;

    }

    public function getDataCode()
    {
        $name = $this->model->getName();
        $code = <<<COFFEE
  getData: ->
    form = @down 'form'
    data =
      $name: form.getValues()\n
COFFEE;
        foreach($this->model->getLinks() as $link) {
            $code .= "      " . $link->getName() . ": []\n";
        }
        foreach($this->model->getLinks() as $link) {
            $code .= "    for record in @down('[name=" . $link->getName() . "_tab]').down('grid').store.getRange()
      data." . $link->getName() . ".push record.data
";
        }
        $code .= "    data\n";
        return $code;
    }

    public function getRecordLoadedCode()
    {
        $code = "    (@down 'form').on 'recordloaded', (record) =>\n";
        foreach($this->model->getLinks() as $link) {
            $code .= "      tab = @down '[name=" . $link->getName() . "_tab]'
      tab.items.each (item) ->
        item.initByRecord record
";
        return $code;
        }
    }

    public function getTabConfigModifyCode()
    {
        $code = "";
        foreach($this->model->getLinks() as $link) {
            $code .= "    config.items.push
      title: '" . $link->getComment() . "'
      name: '" . $link->getName() . "_tab'
      items: [
        Ext.create 'Editor." . $link->getClassName() . "'
      ]
";
        }
        return $code;
    }

    public function getRequires()
    {
        $requires = array();
        foreach($this->model->getLinks() as $link) {
            $requires[] = "Editor." . $link->getClassName();
        }
        if (!count($requires)) {
            return "";
        }
        return "requires:['" . implode("', '", $requires) . "']";
    }
}