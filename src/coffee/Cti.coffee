Ext.define 'Cti',
  statics: 
    start: (config) ->
      Cti.application = Ext.create 'Cti.Application', config
    launch: (cls, cfg) ->
      Cti.application.launch cls, cfg