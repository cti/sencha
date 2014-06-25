<?php

namespace Cti\Sencha\Generator;

use Cti\Di\Reflection;

abstract class Generator
{
    public $model;

    public function getFinalCode()
    {
        $class = $this->model->getClassName();
        $entity = $this->getEntity();

        return <<<COFFEE
# Create file $entity/$class.coffee in you project coffee source for override
Ext.define '$entity.$class',
  extend: 'Generated.$entity.$class'
COFFEE;
    }

    public function getEntity()
    {
        return Reflection::getReflectionClass(get_class($this))->getShortName();
    }

    public function getGeneratedCode()
    {

    }
}