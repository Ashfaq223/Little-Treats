<?php

/*
 * Single entry point for every page:
 * hardened session + security helpers, database connection, general helpers.
 */

require_once __DIR__ . "/security.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/helpers.php";
