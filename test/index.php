<?php

require_once '../ETML/ETML.php';

// $tpl = new \ETFW\Template();
// $tpl->load()->parse();

$email = new \ETML\Email(file_get_contents('./mail.xml'), 'base');

$email->setSubject('Prova email');

echo $email->build();