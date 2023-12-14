<?php
include "class.scssColorChanger.php";

$dir = __DIR__ . "/testcss";
$outputFile = __DIR__ . "/testcss/components/_colors.scss";
$outputFile = __DIR__ . "/_colors.scss";

$excludePath = [
    "_breakpoints.scss",
    "bootstrap",
    "fonts.scss",
    "_mixin.scss",
    "style.scss",
    "mobile.scss",
];
$generator = new Labkod\scssColorChanger($outputFile, $dir, $excludePath);
$generator->init();
print_r($generator->getFileList());
?>
