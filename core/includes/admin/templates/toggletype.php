<?php
$newType = new nqvSessionType(['name' => nqv::getVars(1)]);
if(is_object($newType)) nqv::getSession()->setType($newType->get('id'));
$referer = nqv::getReferer();
header('location:/admin');
exit;