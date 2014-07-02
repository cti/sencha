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
  initComponent: ->
    @cellEditing = new Ext.grid.plugin.CellEditing
      clicksToEdit: 1

    @plugins = [@cellEditing]

    @store =
      proxy: 'memory'
      model: 'Model.$className'

$masterDataStoresConfig
$loadStoresCode
    @columns = @getColumnsConfiguration()

    @tbar = [
      text: 'Добавить'
      name: 'add'
      disabled: true
      handler: =>
$addHandlerCode
    ,
      text: 'Удалить'
      name: 'delete'
      disabled: true
      handler: =>
        selected = @getSelection()[0]
        return unless selected
        @store.remove selected
    ]
    @callParent arguments
    @on 'selectionchange', (self, selection) =>
      (@down '[name=delete]').setDisabled !!selection.length


  initByRecord: (record) ->
    @record = record
$conditionDefinition
    Storage.filter '$name', condition, (response) =>
        @store.loadData response.data
        (@down '[name=add]').enable()

  getColumnsConfiguration: ->
$columnsDefinition



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
        /**
         * When we create editor in window, we need to disable some of column editors
         */
        $removeColumnsEditorFillCode = array();
        foreach($this->model->getOutReferences() as $reference) {
            $model = $this->schema->getModel($reference->getDestination());
            $removeColumnsEditorFillCode[] = "if owner instanceof Window." . $model->getClassName() . "
      removeColumnsEditor.push 'id_" . $model->getName() . "'
";
        }
        $removeColumnsEditorFillCode = implode("    else ", $removeColumnsEditorFillCode);

        $createdColumns = array();
        $columnsCode = array();
        foreach($this->model->getOutReferences() as $reference) {
            $model = $this->schema->getModel($reference->getDestination());
            $name = $model->getName();
            $createdColumns[] = 'id_' . $name;
            $columnsCode[] = "      header: '" . $model->getComment() . "'
      dataIndex: 'id_$name'
      flex: 2
      editor: new Ext.form.field.ComboBox
        store: @masterDataStores.$name
        valueField: 'id_$name'
        displayField: 'name'
        queryMode: 'local'
      renderer: (v) =>
        record = @masterDataStores.$name.findRecord 'id_$name', v
        if record then record.data.name else ''";
        }
        foreach($this->model->getProperties() as $property) {
            if ($property->getBehaviour('log')) {
                continue;
            }
            $xtype = 'textfield';
            switch ($property->getType()) {
                case 'integer':
                    $xtype = 'numberfield';
                    break;
            }
            if (!in_array($property->getName(), $createdColumns)) {
                $columnsCode[] = "
      header: '" . $property->getComment() . "'
      flex: 1
      dataIndex: '" . $property->getName() . "'
      editor:
        xtype: '$xtype'
        allowBlank: " . ($property->getRequired() ? "false" : "true") . "
                ";
            }
        }
        $code = "    columns = [\n" . implode("\n    ,\n", $columnsCode) . "
    ]

    owner = @parentWindow
    removeColumnsEditor = []
    $removeColumnsEditorFillCode
    for column in columns
        delete column.editor if Ext.Array.indexOf(removeColumnsEditor, column.dataIndex) isnt -1
    columns

";
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