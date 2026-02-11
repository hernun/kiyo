<?php
$newType = new nqvSessionType(['name' => nqv::getVars(1)]);
if(is_object($newType)) nqv::getSession()->setType($newType->get('id'));
$referer = nqv::getReferer();
if(implode(nqv::getVars()) !== $referer) {
    header('location:/admin/' . $referer);
    exit;
}