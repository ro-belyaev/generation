<?php

if (false === file_put_contents("tests/allowed.txt", '%%')) {
    die("error writing tests/allowed.txt\n");
} else {
    echo "ok\n";
}
?>