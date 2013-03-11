<?php

if (dirname(__FILE__) !== getcwd()) {
    die("wrong dir\n");
}

echo "Will clear tests dir, sleep..\n";
sleep(5);
echo "begin..\n";

foreach (scandir("tests") as $f) {
    if (preg_match('%^.*\.(php|log|txt)$%', $f)) {
        unlink("tests/$f");
    }
}
echo "done\n";

?>