<?php
$GLOBALS['organizrPages'][] = 'plugin_ipRegistration';
function get_page_ipRegistration($Organizr)
{
    if (!$Organizr) {
        $Organizr = new Organizr();
    }
    if ((!$Organizr->hasDB())) {
        return false;
    }
    if (!$Organizr->qualifyRequest(14, true)) {
        return false;
    }
    $plugin = new Plugin();
    return '';
}