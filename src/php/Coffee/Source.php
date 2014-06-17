<?php

namespace Cti\Sencha\Coffee;

class Source
{
	public $sources = array();

    public function add($source)
    {
        if(!in_array($source, $this->sources)) {
            $this->sources[] = $source;
        }
        return $this;
    }


	function getClassFile($class)
	{
        $path = str_replace('.', DIRECTORY_SEPARATOR, $class) . '.coffee';
        foreach($this->sources as $location) {
            $filename = $location . DIRECTORY_SEPARATOR . $path;
            if(file_exists($filename)) {
                return $filename;
            }
        }
        throw new \Exception(sprintf("Source for %s not found", $class));
	}

    public function getLocalPath($filename)
    {
        foreach($this->sources as $location) {
            if(strpos($filename, $location) === 0) {
                return substr($filename, strlen($location)+1);
            }
        }
        throw new \Exception(sprintf('Invalid source search for %s', $filename));
    }
}