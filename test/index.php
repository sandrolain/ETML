<?php

require_once '../ETML/ETML.php';

// $tpl = new \ETFW\Template();
// $tpl->load()->parse();

$email = new \ETML\Email(file_get_contents('./mail.htm'), 'base');

$email->setSubject('Test email layout');

echo $email->build();