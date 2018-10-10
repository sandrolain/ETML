<?php

spl_autoload_register(function($className)
{
	$className = trim($className, '\\');
	
	// Leave if class should not be handled by this autoloader
	if(substr($className, 0, 4) != 'ETML')
	{
		return;
	}

	$s = DIRECTORY_SEPARATOR;

	$classPath = __DIR__ . $s . str_replace('\\', $s, $className) . '.php';

	require($classPath);
	
});
