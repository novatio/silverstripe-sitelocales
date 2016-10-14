<?php

/**
 * Fetches the name of the current module folder name.
 *
 * @return string
 **/
define('SITELOCALES_DIR',basename(dirname(__FILE__)));

/**
 * Init the allowed Site Locales
 */
SiteLocaleConfig::inst()->initiateAllowedSiteLocales();