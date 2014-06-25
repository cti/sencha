Ext.define 'Cti.Panel'

  extend: 'Ext.panel.Panel'
  border: false
  layout: 'fit'

  initComponent: ->
    @callParent arguments

  setContent: (content) ->
    @removeAll()
    @add content
    @doLayout()

