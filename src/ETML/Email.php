<?php

namespace ETML;

class Email
{
	const MIME				= 'message/rfc822';

	protected $template;
	protected $code			= '';
	protected $vars			= [];
	protected $tagsDefProps	= [];
	protected $idsDefProps	= [];

	public function __construct(string $code = '', string $template = '')
	{
		$this->setCode($code);

		if(!empty($template))
		{
			$this->setTemplate($template);
		}
	}

	public function setCode(string $code)
	{
		$this->code	= $code;

		return $this;
	}

	// --------------------------------
	// Template Methods

	public function setTemplate(string $template)
	{
		$this->template = Template::get($template);

		return $this;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	// --------------------------------
	// Variables Methods

	public function setSubject(string $value)
	{
		return $this->setVariable('subject', $value);
	}

	public function setBody(string $value)
	{
		return $this->setVariable('body', $value);
	}

	public function setFullname(string $value)
	{
		return $this->setVariable('fullname', $value);
	}

	public function setFirstname(string $value)
	{
		return $this->setVariable('firstname', $value);
	}

	public function setAddress(string $value)
	{
		return $this->setVariable('address', $value);
	}

	public function setVariablesList(array $vars)
	{
		foreach($vars as $name => $value)
		{
			if(!is_string($name))
			{
				throw new Exception("Variable name must be a string", 204);
			}

			$this->setVariable($name, $value);
		}

		return $this;
	}

	public function setVariable(string $name, $value)
	{
		if(empty($name))
		{
			throw new Exception("Empty variable name to define", 201);
		}

		/*
		Instead of limiting the type of variable that can be passed to the method,
		I cast the variable to the string type, so that it can also accept variables
		derived from calculations such as integers or string convertible objects.
		*/
		$this->vars[$name] = (string)$value;

		return $this;
	}

	public function getVariable(string $name)
	{
		if(empty($name))
		{
			throw new Exception("Empty variable name to obtain", 202);
		}

		return $this->vars[$name];
	}

	// --------------------------------
	// Default Properties Methods

	public function setTagPropsList(string $tagName, array $props)
	{
		if(empty($tagName))
		{
			throw new Exception("Empty tag name", 211);
		}

		foreach($props as $name => $value)
		{
			if(!is_string($name))
			{
				throw new Exception("Tag Property name must be a string", 212);
			}

			$this->setTagProp($tagName, $name, $value);
		}

		return $this;
	}

	public function setTagProp(string $tagName, string $propName, $propValue)
	{
		if(!isset($this->tagsDefProps[$tagName]))
		{
			$this->tagsDefProps[$tagName] = [];
		}

		if(empty($tagName))
		{
			throw new Exception("Empty tag name", 205);
		}

		if(empty($propName))
		{
			throw new Exception("Empty property name", 206);
		}

		$this->tagsDefProps[$tagName][$propName] = (string)$propValue;

		return $this;
	}

	public function getTagProps(string $tagName)
	{
		if(empty($tagName))
		{
			throw new Exception("Empty tag name", 207);
		}

		if(isset($this->tagsDefProps[$tagName]))
		{
			return $this->tagsDefProps[$tagName];
		}

		return [];
	}

	public function setIdPropsList(string $idName, array $props)
	{
		if(empty($idName))
		{
			throw new Exception("Empty id name", 213);
		}

		foreach($props as $name => $value)
		{
			if(!is_string($name))
			{
				throw new Exception("Id Property name must be a string", 214);
			}

			$this->setIdProp($idName, $name, $value);
		}

		return $this;
	}

	public function setIdProp(string $idName, string $propName, $propValue)
	{
		if(!isset($this->idsDefProps[$idName]))
		{
			$this->idsDefProps[$idName] = [];
		}

		if(empty($idName))
		{
			throw new Exception("Empty id name", 208);
		}

		if(empty($propName))
		{
			throw new Exception("Empty property name", 209);
		}

		$this->idsDefProps[$idName][$propName] = (string)$propValue;

		return $this;
	}

	public function getIdProps(string $idName)
	{
		if(empty($idName))
		{
			throw new Exception("Empty id name", 210);
		}

		if(isset($this->idsDefProps[$idName]))
		{
			return $this->idsDefProps[$idName];
		}

		return [];
	}


	// --------------------------------
	// Build Methods

	public function buildHTML()
	{
		if(!$this->template)
		{
			throw new Exception("Template not defined", 203);
		}

		$tpl		= $this->template;
		$code		= $this->code;
		$vars		= $this->vars;

		$tags		= $tpl->getTags();
		$tags		= array_map(function($tag) { return preg_quote($tag); }, $tags);
		$tagsStr	= implode('|', $tags);

		$tagsRegExp	= '{(?:<(' . $tagsStr . ')(\s+[^>]*)?>((?:(?:(?!<\\1[^>]*>|</\\1>).)++|<\\1[^>]*>(?1)</\\1>)*)</\\1>|<(' . $tagsStr . ')(\s+[^>]*)?/>)}si';

		$usedTags	= [];

		do
		{
			if(preg_match_all($tagsRegExp, $code, $matches, PREG_SET_ORDER))
			{
				foreach($matches as $row)
				{
					$tagName	= $row[1] ?: $row[4];

					$usedTags[]	= $tagName;

					// obtain the tag code
					$tagCode	= $tpl->getTagCode($tagName);

					// tag attributes string
					$attrsStr	= !empty($row[2]) ? $row[2] : (!empty($row[5]) ? $row[5] : NULL);
					$attrsList	= $this->parseAttributesList($attrsStr);
					
					$tagProps	= $this->getTagProps($tagName);

					if(!empty($attrsList['id']))
					{
						$idProps	= $this->getIdProps($attrsList['id']);
						$tagProps	= array_merge($tagProps, $idProps);
					}

					$attrsList	= array_merge($tagProps, $attrsList);

					// Add the children to the attributes list
					$attrsList['children']	= $row[3] ?: '';

					// replace attributes occurrences
					$tagCode	= $this->replacePropsList($tagCode, $attrsList);

					$tagCode	= trim($tagCode);

					// replace source tags with new code
					$code		= str_replace($row[0], $tagCode, $code);
				}
			}
		}
		while(count($matches) > 0);

		// --------------------------------
		// obtain used styles

		$usedTags	= array_unique($usedTags);

		$usedStyles = [];

		foreach($usedTags as $tagName)
		{
			$usedStyles[] = $tpl->getTagStyle($tagName);
		}
		
		if(isset($vars['styles']))
		{
			$usedStyles[]	= $vars['styles'];
		}

		$usedStyles			= implode("\n", $usedStyles);

		// Remove CSS comments
		$usedStyles			= preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!' , '', $usedStyles);

		// Minify CSS code
		$usedStyles			= preg_replace('/\s+/', ' ', $usedStyles);

		$vars['styles']		= '<style type="text/css">' . $usedStyles . '</style>';

		// --------------------------------

		// replace code variables
		$code	= $this->replacePropsList($code, $vars, '%', '%');

		// clean remaining code variables
		$code	= $this->replaceNotFoundProps($code);

		// clean remaining code props
		$code	= $this->replaceNotFoundProps($code, '%', '%');

		// minify html code
		$code	= $this->cleanHTML($code);

		return $code;
	}

	public function sendEML()
	{
		header("Content-Type: " . self::MIME);

		echo $this->buildHTML();

		exit;
	}

	protected function parseAttributesList($attrsStr)
	{
		if(!empty($attrsStr))
		{
			// parse tag attributes

			$el = new \SimpleXMLElement("<element {$attrsStr} />");

			$attrs = get_object_vars($el);

			return isset($attrs['@attributes']) ? $attrs['@attributes'] : [];
		}

		return [];
	}

	protected function replacePropsList(string $code, array $list, string $op = '{', string $cl = '}')
	{
		$op	= preg_quote($op);
		$cl	= preg_quote($cl);

		foreach($list as $key => $value)
		{
			$key	= preg_quote($key);
			$code	= preg_replace("@{$op}{$key}(\\:[^{$cl}]*)?{$cl}@si", $value, $code);
		}

		return $code;
	}

	protected function replaceNotFoundProps(string $code, string $op = '{', string $cl = '}')
	{
		$op	= preg_quote($op);
		$cl	= preg_quote($cl);

		// replace not found occurrences with default one
		$code = preg_replace("@{$op}[a-z0-9_\\-]+(\\:([^{$cl}]*))?{$cl}@si", "\\2", $code);

		return $code;
	}

	protected function cleanHTML($html)
	{
		$search = array(
			'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
			'/[^\S ]+\</s',     // strip whitespaces before tags, except space
			'/(\s)+/s',         // shorten multiple whitespace sequences
			//'/<!--(.|\s)*?-->/' // Remove HTML comments
		);
	
		$replace = array(
			'>',
			'<',
			'\\1',
			//''
		);
	
		return preg_replace($search, $replace, $html);
	}
}