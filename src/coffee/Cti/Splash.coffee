Ext.define 'Cti.Splash',
  extend: 'Ext.Panel'
  layout: 'fit'
  initComponent: ->
    @items = [
      @createView()
    ]
    @callParent arguments

  # Init list elements with titles and icons
  getReconfiguredList: ->
    for item in @list
      unless item.title
        cls = Ext.ClassManager.get item.cls
        item.title = cls.prototype.title
    @list

  createView: ->
    list = @getReconfiguredList()
    store = Ext.create 'Ext.data.Store',
      fields: ['title', 'cls', 'icon', 'config']
      proxy: 'memory'
    store.loadData list
    @dataView = Ext.create 'Ext.view.View',
      itemSelector: 'div.splash-item'
      overItemCls: 'splash-item-over'
      tpl: [
        '<tpl for=".">',
          '<div class="splash-item">{title}</div>',
        '</tpl>'
      ]
      store: store
      listeners:
        itemclick: (self, record) =>
          Cti.launch record.data.cls, (record.data.config or {})
          @afterItemClick record.data.cls, (record.data.config or {})
    @dataView

  afterItemClick: (cls, config) ->
    true