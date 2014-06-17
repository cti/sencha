Ext.define 'Cti',
  statics: 
    bootstrap: (defaultClass) ->
      Cti.application = Ext.create 'Cti.Application', defaultClass: defaultClass
    launch: (cls, cfg) ->
      Cti.application.launch cls, cfg