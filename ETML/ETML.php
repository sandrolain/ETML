<?php

namespace ETML;

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