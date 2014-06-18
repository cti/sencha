Ext.define 'Cti.Viewport'

  extend: 'Ext.Viewport'
  layout: 'fit'

  setContent: (content) ->
    @items.removeAll()
    @items.add content
    @doLayout()