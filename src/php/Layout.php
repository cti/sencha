<?php

namespace Cti\Sencha;

use Build\Application;

class Layout
{
	/**
	 * @inject
	 * @var Application
	 */
	public $application;

	public $base;

	public $direct;

	public $template = 'extjs5cdn';

	public $title = 'ExtJS Application';

	public $script;

	function display()
	{
        $this->application->getSencha()->getCoffeeCompiler()->build($this->script);
        $this->application->getFenom()->display($this->template, array(
        	'base' => $this->base,
        	'direct' => $this->direct,
        	'script' => 'public/js/' . $this->script . '.js',
        	'title' => $this->title,
        ));

	}

	function setScript($script)
	{
		$this->script = $script;
		return $this;
	}

	function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}
}