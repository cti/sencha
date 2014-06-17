<?php

namespace Cti\Sencha;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Application\Warmer;
use Cti\Core\Module\Project;

class Sencha extends Project implements Bootloader, Warmer
{
    /**
     * @inject
     * @var Application
     */
    public $application;

    /**
     * module namespace
     * @var string
     */
    public $prefix = 'Cti\\Sencha\\';

    /**
     * @inject
     * @var Project
     */
    protected $project;
    
    public function init(\Cti\Core\Module\Cache $cache)
    {
        parent::init($cache);
        $this->path = dirname(dirname(__DIR__));

        $source = $this->application->getManager()->get('Cti\\Sencha\\Coffee\\Source');
        $source->add($this->getPath('resources coffee'));
        $source->add($this->getPath('src coffee'));
        $source->add($this->project->getPath('src coffee'));
        $source->add($this->project->getPath('build coffee'));
        $source->add($this->project->getPath('resources coffee'));
    }

    public function boot(Application $application)
    {
        $application->getFenom()->addSource($this->getPath('resources fenom'));
    }

    public function warm(Application $application)
    {

    }

    public function createLayout()
    {
        return $this->application->getManager()->create('Cti\\Sencha\\Layout', array(
            'base' => $this->application->getWeb()->getUrl(),
            'direct' => $this->application->getDirect()->getUrl(),
        ));
    }

    public function getCoffeeCompiler()
    {
        return $this->application->getManager()->get('Cti\Sencha\Coffee\Compiler');
    }
}