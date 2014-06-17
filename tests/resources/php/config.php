<?php

return array(

    'Cti\Core\Application\Generator' => array(
        'modules' => array(
            'alias' => 'Module\Greet'
        )
    ),

    'Cti\Core\Module\Project' => array(
        'path' => dirname(dirname(__DIR__)),
    ),

    'Cti\Di\Cache' => array(
        'debug' => true,
    ),

    'class' => array(
        'property' => 'value', 
        'property2' => 'value'
    )
);