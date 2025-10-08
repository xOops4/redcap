<?php

// Is this an External Module passthru request? If so, init based on context and delegate to EM framework
if (isset($_GET["__passthru"]) && $_GET["__passthru"] == "ExternalModules") {
    if (!defined("EM_ENDPOINT")) define("EM_ENDPOINT", true);

    /**
     * This is required to let REDCap know this is an ajax request,
     * and to avoid creating new CSRF tokens, which eventually cause
     * AJAX requests to fail after enough are made to cycle the
     * original CSRF token out of $_SESSION['redcap_csrf_token'].
     */
    if (isset($_GET["ajax"]) && $_GET["ajax"] == "1") {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
    }

	// NO AUTH?
	if (isset($_GET["NOAUTH"]) && !defined("NOAUTH")) {
		define('NOAUTH', true);
	}

    if (isset($_GET["pid"])) {
        require_once dirname(__FILE__) . "/Config/init_project.php";
    }
    else {
        require_once dirname(__FILE__) . "/Config/init_global.php";
    }
    require APP_PATH_EXTMOD . "index.php";
    // Exit (or return in case of testing)
    if (\ExternalModules\ExternalModules::isTesting()) {
        return;
    }
    else {
        exit;
    }
}

// Config for non-project pages
require_once dirname(__FILE__) . "/Config/init_global.php";

// Call the real page. This page is just a shell for the index file in the Home directory.
require_once APP_PATH_DOCROOT . "Home/index.php";