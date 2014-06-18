Ext.define 'Cti.Panel'

  extend: 'Ext.panel.Panel'
  border: false
  layout: 'fit'

  initComponent: ->
    @bbar = toolbar = Ext.create 'Ext.toolbar.Toolbar'
    @callParent arguments
    # toolbar.hide()

  setContent: (content) ->
    @removeAll()
    @add content
    @doLayout()

  updateToolbar: (toolbar) ->
    @addDocked toolbar
    for item in @getDockedItems()
      @removeDocked item unless item is toolbar 
