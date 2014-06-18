<?php

namespace Cti\Sencha;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Application\Warmer;
use Cti\Core\Module\Project;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @dependsOn Cti\Storage\Storage
 */
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

    /**
     * @return array
     */
    protected function getAvailableNamespaces()
    {
        return array('Coffee', 'Command', 'Generator');
    }

    public function boot(Application $application)
    {
        $application->getFenom()->addSource($this->getPath('resources fenom'));
    }

    public function warm(Application $application)
    {
        parent::warm($application);
        
        $fs = new Filesystem();
        $schema = $application->getStorage()->getSchema();

        foreach($schema->getModels() as $model) {

            $coffeeGenerator = $this->application->getManager()->create('Cti\Sencha\Generator\Model', array(
                'model' => $model
            ));

            $generatedSource = $coffeeGenerator->getGeneratedCode();
            $path = $this->application->getProject()->getPath('build coffee Model Generated ' . $model->getClassName() . '.coffee');
            $fs->dumpFile($path, $generatedSource);

            $modelSource = $coffeeGenerator->getModelCode();
            $path = $this->application->getProject()->getPath('build coffee Model ' . $model->getClassName() . '.coffee');
            $fs->dumpFile($path, $modelSource);
        }
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