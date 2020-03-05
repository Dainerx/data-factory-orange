<?php


$arr = [];


$arr["2019-06-12"] = 999;
$arr["2019-02-27"] = 2;
$arr["2019-02-12"] = 1;
$arr["2019-04-12"] = 4;

krsort($arr, SORT_DESC);
$a = array_reverse($arr);
var_dump($a);
