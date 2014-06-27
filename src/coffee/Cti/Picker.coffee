Ext.define 'Cti.Picker',
  xtype: 'ctipicker'
  extend: 'Ext.form.field.ComboBox'
  queryMode: 'local'
  minChars: 0
  enableKeyEvents: true
  directFn: Storage.getList
  anyMatch: true
  errorMessage: 'Value not found'
  initComponent: ->
    window.pik = this
    @store =
      autoDestroy: true
      fields: [@valueField, @displayField]
      proxy: 'memory'
    @callParent arguments
    @on 'afterrender', =>
      @load()

  setLoadingState: (key) ->
    if key
      @getPicker().setHeight 100
      @getPicker().setLoading true
    else
      @getPicker().setLoading false

  load: ->
    @setLoadingState true
    @directFn @model, (records) =>
      @store.loadData records.data
      if @getValue()
        @setValue @getValue()
        @validate()

  validator: (value) ->
    return true if value is "" and @allowBlank is true
    existingRecord = this.findRecord @valueField, @getValue()
    return @errorMessage unless existingRecord
    true

