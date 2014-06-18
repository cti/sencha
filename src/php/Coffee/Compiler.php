<?php

namespace Cti\Sencha\Coffee;

use Cti\Core\Module\Cache;
use Cti\Core\Module\Project;
use Cti\Sencha\Sencha;
use Symfony\Component\Filesystem\Filesystem;

class Compiler 
{
    /**
     * @inject
     * @var Dependency
     */
    public $dependency;

    /**
     * @inject
     * @var Source
     */    
    public $source;

    /**
     * @inject
     * @var Sencha
     */
    protected $sencha;

    /**
     * @inject
     * @var Project
     */
    protected $project;

    /**
     * @inject
     * @var Cache $cache
     */
    protected $cache;

    public $stats = array();

    public function init()
    {
        if($this->cache->exists(__CLASS__)) {
            $this->stats = $this->cache->get(__CLASS__);
        }
    }

    public function validate($script)
    {
        $filename = $this->project->getPath(sprintf('public js %s.js', $script));

        $valid = false;
        if(file_exists($filename) && isset($this->stats[$script])) {
            $valid = true;
            foreach ($this->stats[$script] as $dependency) {
                if(filemtime($dependency) >= filemtime($filename)) {
                    $valid = false;
                }
            }
        }
        if(!$valid) {
            $this->build($script);
        }
    }

    public function build($script)
    {
        $dependencies = array_merge(
            $this->dependency->getList($this->project->getPath("resources coffee $script.coffee")),
            $this->dependency->getList($this->sencha->getPath('src coffee Cti.coffee'))
        );

        $fs = new Filesystem;
        $result = ''; 

        $sourceList = array();

        foreach(array_reverse($dependencies) as $coffee) {

            $sourceList[] = $coffee;

            $local = $this->source->getLocalPath($coffee);
            $local = dirname($local) . DIRECTORY_SEPARATOR . basename($local, 'coffee') .'js';
            $javascript = $this->project->getPath(sprintf('build js %s', $local));

            if(!file_exists($javascript) || filemtime($coffee) >= filemtime($javascript)) {
                $code = \CoffeeScript\Compiler::compile(file_get_contents($coffee), array(
                    'filename' => $coffee,
                    'bare' => true,
                    'header' => false
                ));
                $fs->dumpFile($javascript, $code);

            } else {
                $code = file_get_contents($javascript);
            }

            $result .= $code . PHP_EOL;
        }

        $this->hash[$script] = $sourceList;
        $this->cache->set(__CLASS__, $this->hash);

        $filename = $this->project->getPath("public js $script.js");
        $fs->dumpFile($filename, $result);

        return $filename;
    }
}