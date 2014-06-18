<?php

namespace Cti\Sencha\Coffee;

class Dependency
{
    /**
     * @inject
     * @var Source
     */
    public $source;

    public function getList($script)
    {
        if(isset($this->hash[$script])) {
            return array();
        }
        $this->hash[$script] = true;
        $result = array();
        if(!file_exists($script)) {
            throw new \Exception(sprintf('File %s not found', $script));
        }
        $contents = file_get_contents($script);
        $result[] = $script;
        foreach($this->getScriptDependencies($contents) as $class) {
            if(strpos($class, 'Ext.') !== 0) {
                $file = $this->source->getClassFile($class);
                if(!in_array($file, $result)) {
                    $result[] = $file;
                }
                foreach($this->getList($file) as $dependency) {
                    if(!in_array($dependency, $result)) {
                        $result[] = $dependency;
                    }
                }
            }
        }
        return $result;
    }

    protected function getScriptDependencies($text)
    {
        $result = array();

        $pregs = array(
            "/new\s+([A-Za-z0-9.]+)/",
            "/extends\s+([A-Za-z0-9.]+)/",
            "/extend\s*:\s*['\"]([a-zA-Z0-9._]+)['\"]/",
            "/defaultClass\s*:\s*['\"]([a-zA-Z0-9._]+)['\"]/",
            "/viewportClass\s*:\s*['\"]([a-zA-Z0-9._]+)['\"]/",
            "/Ext.create ['\"]([a-zA-Z0-9.]+)['\"]/",
            "/Cti.launch ['\"]([a-zA-Z0-9.]+)['\"]/",
            "/Cti.bootstrap ['\"]([a-zA-Z0-9.]+)['\"]/",
        );
        foreach($pregs as $preg) {
            preg_match_all($preg, $text, $answer);
            $result = array_merge($result, $answer[1]);
        }

        $pregs = array(
            "/Ext.require ['\"]([a-zA-Z0-9.]+)['\"]/",
            "/Ext.syncRequire ['\"]([a-zA-Z0-9.]+)['\"]/",
            "/requires\s*:\s*\[['\"a-zA-Z0-9.,\s]+\]/",
            "/mixins\s*:\s*[\[{][^\[\]}{]+[\]}]/",
        );
        foreach ($pregs as $preg) {
            preg_match_all($preg, $text, $list);
            foreach($list[0] as $cls) {
                preg_match_all("/['\"]([a-zA-Z0-9.]*)['\"]/", $cls, $match);
                $result = array_merge($result, $match[1]);
            }
        }

        return $result;
    }
}