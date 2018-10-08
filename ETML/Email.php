<?php

namespace ETML;

class Email
{
	const MIME				= 'message/rfc822';

	protected $props		= [];
	protected $template;
	protected $code			= '';

	public function __construct(string $code = '', string $template = '')
	{
		$this->code = $code;

		if(!empty($template))
		{
			$this->setTemplate($template);
		}
	}

	public function setCode(string $code)
	{
		$this->code	= $code;
	}

	public function setSubject(string $value)
	{
		return $this->setProp('subject', $value);
	}

	public function setBody(string $value)
	{
		return $this->setProp('body', $value);
	}

	public function setProp(string $name, string $value)
	{
		if(empty($name))
		{
			throw new \Exception("Empty prop name", 201);
		}

		$this->props[$name] = $value;

		return $this;
	}

	public function getProp(string $name)
	{
		if(empty($name))
		{
			throw new \Exception("Empty prop name", 202);
		}

		return $this->props[$name];
	}

	public function setTemplate(string $template)
	{
		$this->template = Template::get($template);

		return $this;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function build()
	{
		if(!$this->template)
		{
			throw new \Exception("Template not defined", 203);
		}

		$tpl		= $this->template;

		$code		= $this->code;

		$props		= $this->props;

		$tags		= $tpl->getTags();

		$tags		= array_map(function($tag)
		{
			return preg_quote($tag);

		}, $tags);

		$tagsStr = implode('|', $tags);

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
					$attrsStr	= $row[2] ?: $row[5];
					$attrsList	= [];

					if(!empty($attrsStr))
					{
						// parse tag attributes

						$el = new \SimpleXMLElement("<element {$attrsStr} />");

						$attrs = get_object_vars($el);

						$attrsList	= $attrs['@attributes'];
					}

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

		if(isset($props['styles']))
		{
			$usedStyles[]	= $props['styles'];
		}

		//dj($usedStyles);

		$usedStyles			= implode("\n", $usedStyles);
		$usedStyles			= preg_replace('/\s+/', ' ', $usedStyles);

		$props['styles']	= '<style type="text/css">' . $usedStyles . '</style>';

		// --------------------------------

		// replace code props
		$code	= $this->replacePropsList($code, $props, '%', '%');

		// clean remaining code variables
		$code	= $this->replaceNotFoundProps($code);

		// clean remaining code props
		$code	= $this->replaceNotFoundProps($code, '%', '%');

		// minify html code
		$code	= $this->cleanHTML($code);

		//die(htmlspecialchars($code));

		return $code;
	}

	public function send()
	{
		header("Content-Type: " . self::MIME);

		echo $this->build();
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