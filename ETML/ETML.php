<?php


namespace ETML;

require_once __DIR__ . '/Email.php';
require_once __DIR__ . '/Template.php';

class ETML
{
	const VERSION	= '0.1';

	public static function getTemplate(string $name = '')
	{
		if(empty($name))
		{
			return new Template($name);
		}

		return Template::get($name);
	}

	public static function getEmail(string $name = '')
	{
		return new Email();
	}
}

function dj($var)
{
	die("<pre>" . print_r($var, true) . "</pre>");
}