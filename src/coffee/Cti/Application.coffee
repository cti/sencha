Ext.define 'Cti.Application',

  classTokens: {}
  dynamic: {}
  tokenClasses: {}

  viewportClass: 'Cti.Viewport'
  defaultClass: 'Cti.Welcome'

  requires: ['Cti.Picker']

  constructor: (config) ->

    for name, cls of Ext.ClassManager.classes
      if cls.prototype and cls.prototype.token
        @registerToken cls.prototype.token, name

    Ext.apply @, config
    Ext.History.on 'change', (url) => @processToken url
    @viewport = Ext.create @viewportClass
    @panel = @viewport.panel

    if Ext.History.currentToken
      @processToken Ext.History.currentToken
    else if @defaultClass
      @launch @defaultClass

    return @

  registerToken: (token, cls) ->
    if token.indexOf(':') != -1
      @dynamic[cls] = params: [], basis : []
      items = token.split '/'
      for v, k in items
        if Ext.String.startsWith v, ':'
          @dynamic[cls].params[k] = v.substr 1
        else
          @dynamic[cls].basis[k] = v
    else
      @tokenClasses[token] = cls
      @classTokens[cls] = token

  createInstance: (cls, cfg) ->
    cfg = cfg || {}
    if cls != @defaultClass
      cfg = Ext.applyIf cfg, tools: [id:'close', handler: -> Ext.History.back()]
    Ext.create cls, cfg

  processToken: (token) ->
    if @tokenClasses[token]
      @panel.setContent @createInstance @tokenClasses[token]

    else
      chain = token.split '/'
      for cls, dynamic of @dynamic

        found = true
        for v,k in dynamic.basis
          found = false if v and chain[k] != v 
            
        if found
          cfg = {}
          for v,k in dynamic.params
            cfg[v] = chain[k] if k
          return @panel.setContent @createInstance cls, cfg

      unless token
        return @panel.setContent @createInstance @defaultClass

      alert 'No token processing: ' + token


  launch: (cls, cfg) ->
    if @classTokens[cls]
      Ext.History.add @classTokens[cls]
    else if @dynamic[cls]
      chain = []
      chain[k] = cfg[v] for v, k in @dynamic[cls].params
      chain[k] = v for v, k in @dynamic[cls].basis
      Ext.History.add chain.join '/'
    else
      @panel.setContent @createInstance cls, cfg