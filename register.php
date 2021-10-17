<?php
$GLOBALS['organizrPages'][] = 'plugin_registerIP';
function get_page_registerIP($Organizr)
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