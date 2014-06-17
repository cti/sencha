Ext.define 'Cti.Viewport'

  extend: 'Ext.Viewport'
  layout: 'fit'

  setContent: (content) ->
    @removeAll()
    @add content
    @doLayout()