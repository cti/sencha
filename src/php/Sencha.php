<?php

namespace Cti\Sencha;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Application\Warmer;
use Cti\Core\Module\Project;
use Cti\Di\Reflection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

        foreach($this->getClasses('Direct') as $class) {
            $this->application->getManager()->getConfiguration()->push('Cti\Direct\Service', 'list', $class);
        }
    }

    /**
     * @return array
     */
    protected function getAvailableNamespaces()
    {
        return array('Coffee', 'Command', 'Direct', 'Generator');
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

        $entities = array();
        foreach($this->getClasses('Generator') as $class) {
            $reflection = Reflection::getReflectionClass($class);
            if(!$reflection->isAbstract() && $reflection->isSubclassOf('Cti\\Sencha\\Generator\\Generator')) {
                $entities[] = $reflection->getShortName();
            }
        }

        foreach($schema->getModels() as $model) {
            if($model->hasBehaviour('link')) {
                continue;
            }
            foreach($entities as $entity) {
                $generator = $this->application->getManager()->create('Cti\Sencha\Generator\\' . $entity, array(
                    'model' => $model
                ));

                $path = $this->application->getProject()->getPath('build coffee Generated ' . $entity . ' ' . $model->getClassName() . '.coffee');
                $code = $generator->getGeneratedCode();
                if(!file_exists($path) || md5(file_get_contents($path)) != md5($code)) {
                    $fs->dumpFile($path, $code);
                }

                $path = $this->application->getProject()->getPath('build coffee ' . $entity . ' ' . $model->getClassName() . '.coffee');
                $code = $generator->getFinalCode();
                if(!file_exists($path) || md5(file_get_contents($path)) != md5($code)) {
                    $fs->dumpFile($path, $code);
                }
            }
            echo '- generate '. $model->getClassName() . PHP_EOL;
        }

        $generator = $this->application->getManager()->create('Cti\Sencha\Generator\\Master', array(
            'model' => $model
        ));

        $path = $this->application->getProject()->getPath('build coffee Generated Master.coffee');
        $code = $generator->getGeneratedCode();
        if(!file_exists($path) || md5(file_get_contents($path)) != md5($code)) {
            $fs->dumpFile($path, $code);
        }

        $path = $this->application->getProject()->getPath('build coffee Master.coffee');
        $code = $generator->getFinalCode();
        if(!file_exists($path) || md5(file_get_contents($path)) != md5($code)) {
            $fs->dumpFile($path, $code);
        }


        $finder = new Finder();

        $this->getCoffeeCompiler()->setDebug(true);

        $source = $application->getProject()->getPath('resources coffee');
        if(is_dir($source)) {
            $finder->files()->name("*.coffee")->in($source);
            foreach($finder as $file) {
                $script = substr($file, strlen($source)+1, -7);
                echo '- processing '. $script .'.coffee' . PHP_EOL;
                $this->getCoffeeCompiler()->build($script);
            }
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