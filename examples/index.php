<?php

require_once __DIR__ . '/../src/autoload.php';

// $tpl = new \ETFW\Template();
// $tpl->load()->parse();

$email = new \ETML\Email(file_get_contents('./mail.htm'), 'base');

$email->setSubject('Test email layout');

$email->setTagProp('e-p', 'size', 18);

$email->setIdPropsList('secondary-text', [
	"size" => 14,
	'style' => 'color: #CC0000'
]);

echo $email->buildHTML();