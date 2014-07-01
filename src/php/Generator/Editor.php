<?php
namespace Cti\Sencha\Generator;

class Editor extends Generator
{
    /**
     * @var \Cti\Storage\Component\Model
     */
    public $model;

    /**
     * @var \Cti\Storage\Schema
     */
    public $schema;

    public function getGeneratedCode()
    {
        $className = $this->model->getClassName();
        $name = $this->model->getName();
        $masterDataStoresConfig = $this->getMasterDataStoresConfig();
        $loadStoresCode = $this->getLoadStoresCode();
        $columnsDefinition = $this->getColumnsDefinition();
        $addHandlerCode = $this->getAddHandlerCode();
        $conditionDefinition = $this->getConditionDefinition();

        return <<<COFFEE
Ext.define 'Generated.Editor.$className',
  extend: 'Ext.grid.Panel'
  requires: ['Model.$className']
  height: 250
  width: 250
  initComponent: ->
    @cellEditing = new Ext.grid.plugin.CellEditing
      clicksToEdit: 1

    @plugins = [@cellEditing]

    @store =
      proxy: 'memory'
      model: 'Model.$className'

$masterDataStoresConfig
$loadStoresCode
$columnsDefinition

    @tbar = [
      text: 'Добавить'
      handler: =>
$addHandlerCode
    ,
      text: 'Удалить'
      handler: =>
        selected = @getSelection()[0]
        return unless selected
        @store.remove selected
    ]
    @callParent arguments

  initByRecord: (record) ->
    @record = record
$conditionDefinition
    Storage.filter '$name', condition, (response) => @store.loadData response.data


COFFEE;

    }

    public function getMasterDataStoresConfig()
    {
        $code = "    @masterDataStores =\n";
        foreach($this->model->getOutReferences() as $reference) {
            $model = $this->schema->getModel($reference->getDestination());
            $code .= "      " . $model->getName() . ": Ext.create('Ext.data.Store',
        model: 'Model." . $model->getClassName() . "'
        proxy: 'memory'
      )
";
        }
        return $code;
    }

    public function getLoadStoresCode()
    {
        $code = "";
        foreach($this->model->getOutReferences() as $reference) {
            $code .= "    Storage.getList '" . $reference->getDestination() . "', (response) =>
      @masterDataStores." . $reference->getDestination() . ".loadData response.data

";
        }
        return $code;
    }

    public function getColumnsDefinition()
    {
        $columnsCode = array();
        foreach($this->model->getOutReferences() as $reference) {
            $model = $this->schema->getModel($reference->getDestination());
            $name = $model->getName();
            $columnsCode[] = "      header: '" . $model->getComment() . "'
      dataIndex: 'id_$name'
      editor: new Ext.form.field.ComboBox
        store: @masterDataStores.$name
        valueField: 'id_$name'
        displayField: 'name'
        queryMode: 'local'
      renderer: (v) =>
        record = @masterDataStores.$name.findRecord 'id_$name', v
        if record then record.data.name else ''";
        }
        $code = "    @columns = [\n" . implode("\n    ,\n", $columnsCode) . "\n    ]\n";
        return $code;
    }

    public function getAddHandlerCode()
    {
        /**
         * @var \Cti\Storage\Component\Reference[] $references
         */
        $references = array_values($this->model->getOutReferences());
        /**
         * @var \Cti\Storage\Component\Model[] $referencesModels
         */
        $referencesModels = array();
        $referencesModels[] = $this->schema->getModel($references[0]->getDestination());
        $referencesModels[] = $this->schema->getModel($references[1]->getDestination());
        $code = "        if @record instanceof Model." . $referencesModels[0]->getClassName() . "
          config =
            id_" . $referencesModels[0]->getName() . ": @record.data.id_" . $referencesModels[0]->getName() . "
        else if @record instanceof Model." . $referencesModels[1]->getClassName() . "
          config =
            id_" . $referencesModels[1]->getName() . ": @record.data.id_" . $referencesModels[1]->getName() . "
";
        $code .= "        @store.add Ext.create 'Model." . $this->model->getClassName(). "', config";
        return $code;

    }

    public function getConditionDefinition()
    {
        /**
         * @var \Cti\Storage\Component\Reference[] $references
         */
        $references = array_values($this->model->getOutReferences());
        /**
         * @var \Cti\Storage\Component\Model[] $referencesModels
         */
        $referencesModels = array();
        $referencesModels[] = $this->schema->getModel($references[0]->getDestination());
        $referencesModels[] = $this->schema->getModel($references[1]->getDestination());
        return "    if @record instanceof Model." . $referencesModels[0]->getClassName() . "
      condition =
        id_" . $referencesModels[0]->getName() . ": record.data.id_" . $referencesModels[0]->getName() . "
    else if @record instanceof Model." . $referencesModels[1]->getClassName() . "
      condition =
        id_" . $referencesModels[1]->getName() . ": record.data.id_" . $referencesModels[1]->getName() . "
";
    }
} 