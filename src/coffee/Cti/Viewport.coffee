Ext.define 'Cti.Viewport',

  extend: 'Ext.Viewport'
  layout: 'fit'

  initComponent: ->
    @items = [@panel = Ext.create 'Cti.Panel']
    @callParent arguments