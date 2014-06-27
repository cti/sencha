<?php

namespace Cti\Sencha\Generator;

use Cti\Core\String;

class Grid extends Generator
{
    /**
     * @var \Cti\Storage\Component\Model
     */
    public $model;

    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = $this->model->getName();
        $title = $this->model->getComment();

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $columns = array();
        $fields = array();

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
            }

        }
        $fields = json_encode($fields);
        $columns = json_encode($columns);

        $configuration = $this->getColumnConfiguration();

        $additionalStoresPreConstructor = $this->getAdditionalStoresPreConstructor();
        $additionalStoresAfterConstructor = $this->getAdditionalStoresAfterConstructor();

        return <<<COFFEE
Ext.define 'Generated.Grid.$class',

  extend: 'Ext.grid.Panel'

  title: '$title'

  store: model: 'Model.$class'
  getAvailableColumns: -> $columns
  getColumnConfiguration: -> $configuration

  requires: ['Model.$class', 'Form.$class']

  getTopToolbar: -> [
    xtype:'textfield'
    emptyText:'Фильтр'
    width:220
    enableKeyEvents: true
    listeners: keyup: 
      buffer: 50
      fn: => @applyFilter()
  ,
    text:'Add'
    handler: => @onAddClick()
    '-'
    text:'Edit'
    disabled: true
    handler: => @onEditClick()
    '-'
    text:'Remove'
    disabled: true
    handler: => @onRemoveClick()
  ]

  applyFilter: -> 
    value = Ext.util.Format.lowercase @down('[xtype=textfield]').getValue()
    @store.clearFilter()
    if value
      @store.filterBy (r) -> 
        found = false
        for k, v of r.data
          found = found || Ext.util.Format.lowercase(v).indexOf(value) != -1
        found

  getSelected: -> @getSelectionModel().getSelection()[0]

  onSelectionChange: ->
    disabled = !@getSelectionModel().getSelection().length
    @down('[text=Edit]').setDisabled disabled
    @down('[text=Remove]').setDisabled disabled

  onAddClick: -> Ext.create 'Window.$class', grid: @
  onRemoveClick: -> Storage.remove '$name', @getSelected().getPk(), => @getSelected().store.remove @getSelected()
  onItemClick: ->
  onItemDblClick: -> @onEditClick()
  onEditClick: -> Ext.create 'Window.$class', Ext.apply @getSelected().getPk(), grid: @

  loadData: -> Storage.getList '$name', (response) => @store.loadData response.data
  
  initComponent: ->
$additionalStoresPreConstructor
    configuration = @getColumnConfiguration()
    @columns = []
    Ext.Array.each @getAvailableColumns(), (column) =>
      if configuration[column.dataIndex]
        @columns.push Ext.apply column, configuration[column.dataIndex]

    @tbar = @getTopToolbar()
    @callParent arguments
$additionalStoresAfterConstructor

    @on 'selectionchange', => @onSelectionChange()
    @on 'itemclick', => @onItemClick()
    @on 'itemdblclick', => @onItemDblClick()
    @on 'afterrender', =>
        @loadData()
    , single: true

COFFEE;

    }

    protected function getColumnConfiguration()
    {
        $configuration = array();
        /**
         * @var \Cti\Storage\Component\Reference[] $referenceByColumns
         */
        $referencesByColumns = array();
        foreach($this->model->getOutReferences() as $reference) {
            if (count($reference->getProperties()) == 1) {
                $properties = $reference->getProperties();
                $property = array_shift($properties);
                $referencesByColumns[$property->getName()] = $reference;
            }
        }
        foreach($this->model->getProperties() as $property) {
            if (!$property->getBehaviour()) {
                $width = 80;
                if ($property->getJavascriptType() == 'string') {
                    $width = 120;
                }
                $configuration[$property->getName()] = array(
                    'width' => $width
                );
            }
            // Need to add renderer, this column in reference
            if (isset($referencesByColumns[$property->getName()])) {
                $reference = $referencesByColumns[$property->getName()];
                if (empty($configuration[$property->getName()])) {
                    $configuration[$property->getName()] = array();
                }
                $configuration[$property->getName()]['width'] = 120;
                $configuration[$property->getName()]['renderer'] = " (v) =>
          record = @additionalStores.{$reference->getDestination()}.findRecord 'id_{$reference->getDestination()}', v
          \"#{v} #{record.data.name}\" or \"\"\n";
            }
        }
        $code = "";
        foreach($configuration as $columnName => $config) {
            $code .= "    $columnName:\n";
            foreach($config as $k => $v) {
                $code .= "      $k:" . (is_numeric($v) || $k == 'renderer' ? $v : '"'.$v.'"') . "\n";
            }
        }
        return "\n".$code;
    }

    protected function getAdditionalStoresPreConstructor()
    {
        $code = '';
        $references = $this->model->getOutReferences();
        if (count($references)) {
            $code .= "    @additionalStores = \n";
            foreach($references as $reference) {
                $code .= <<<COFFEE
      {$reference->getDestination()}: Ext.create('Ext.data.Store',
        model: 'Model.{$this->model->getClassName()}'
        proxy: 'memory'
      )\n
COFFEE;
            }
        }
        return $code;

    }

    protected function getAdditionalStoresAfterConstructor()
    {
        $code = "\n";
        $references = $this->model->getOutReferences();
        foreach($references as $reference) {
            $code .= <<<COFFEE
    Storage.getList '{$reference->getDestination()}', (records) =>
      @additionalStores.{$reference->getDestination()}.loadData records.data\n
COFFEE;
        }
        return $code;
    }

}