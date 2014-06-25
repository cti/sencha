<!DOCTYPE html>
<html>
    <head>
        <title>{$title}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <base href="{$base}"/>
        <link rel="stylesheet" type="text/css" href="http://cdn.sencha.com/ext/beta/ext-5.0.0.736/build/packages/ext-theme-neptune/build/resources/ext-theme-neptune-all.css">
{foreach $styles as $style}
        <link rel="stylesheet" type="text/css" href="{$style}">
{/foreach}
        <script type="text/javascript" src="http://cdn.sencha.com/ext/beta/ext-5.0.0.736/build/ext-all.js"></script>
        <script type="text/javascript" src="{$direct}"></script>
        <script type="text/javascript" src="{$script}"></script>
    </head>
    <body></body> 
</html>
