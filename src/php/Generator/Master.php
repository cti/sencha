<?php

namespace Cti\Sencha\Generator;

use Cti\Storage\Schema;

class Master
{
    /**
     * @inject
     * @var Schema
     */
    public $schema;

    function getGeneratedCode()
    {
        $list = array();

        foreach($this->schema->getModels() as $model) {
            if(!$model->hasBehaviour('link')) {
                $list[] ="splashClass: 'Grid." . $model->getClassName() . "'";
            }
        }
        $list = implode(PHP_EOL . '    ,' . PHP_EOL . '      ', $list);

        return <<<COFFEE
Ext.define 'Generated.Master'

  extend: 'Cti.Splash'
  title: 'Доступные модели'

  token: '/_master'
  
  initComponent: ->
    @list = [
      $list
    ]
    @callParent arguments        
COFFEE;
    }

    function getFinalCode()
    {
        return <<<COFFEE
# Create file Master.coffee in you project coffee source for override
Ext.define 'Master',
  extend: 'Generated.Master'
COFFEE;
    }
}