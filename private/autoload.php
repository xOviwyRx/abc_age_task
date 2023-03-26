<?php
spl_autoload_register(function ($classname) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/private/' . str_replace("\\", "/", $classname) . '.php';
});
