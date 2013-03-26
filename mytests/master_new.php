<?php
require_once "inc.php";
//mysql_connect($db_host, $db_login, $db_pass);

/*
echo "Clearing old files..\n";
foreach (scandir("tests") as $f) {
    if (preg_match('%\.(log|php)$%', $f)) {
        unlink("tests/$f");
    } 
}
*/


//echo "Generating tests..\n";
$num = 1;
$test_count = 0;
$step = 25;
$lst = array();
foreach ($confs as $conf) {
    foreach ($filters as $filter) {
        foreach ($processes as $process) {
            foreach ($outputs as $output) {
                foreach ($templates as $template) {
                    $gen = "new Test($conf, $filter, $process, $output, $template, \"$num.log\")";
//echo "\n\n------------------------------------------------\n";
                    //echo "$gen .. ";
                    try {
                        eval("\$test = $gen;");
                        if ($test->toohard()) {
                            //echo "too hard\n";
                        } else {
                            if (file_put_contents("./tests/$num.php", '<?php require_once "../inc.php"; $test = ' . $gen . '; $test->route(); ?>') === false) {
                                //                            var_dump(error_get_last());
                                die("error writing tests/$num.php\n");
                            }
                            /*     */
                            //echo "tests/$num.php written\n";
                            $lst[] = array("$num.php" . $test->link(), $gen);
                                
                            unset($test);
                            ++$num;
                        }
                    } catch (BadTest $e) {
                        //echo "bad test, skip: " . $e->getMessage() . "\n";
                    }
//echo "------------------------------------------------------------\n\n";
		    $test_count++;
		    if($test_count % $step == 0) {
			$query_check_state = "SELECT state FROM $table WHERE id=$id";
			$result = mysql_query($query_check_state, $connection);
			$cur_state = mysql_fetch_row($result)[0];
			if($cur_state == STATE_CANCEL) {
			    die("generation cancel");
			}
			$query_update_process = "UPDATE $table SET `process`=$test_count WHERE `id`=$id";
			mysql_query($query_update_process, $connection);
		    }
                }
            }
        }
    }
}


$query_check_state = "SELECT state FROM $table WHERE id=$id";
$result = mysql_query($query_check_state, $connection);
$cur_state = mysql_fetch_row($result)[0];
if($cur_state == STATE_CANCEL) {
    die("generation cancel");
}
$query_update_process = "UPDATE $table SET `process`=$test_count WHERE `id`=$id";
mysql_query($query_update_process, $connection);

//echo "Writing tests/list.php ..\n";

$t = "<html><head><title>mytests</title></head><body><table>";
foreach ($lst as $ln) {
    list($href, $gen) = $ln;
    $t .= "<tr><td><a href=" . htmlspecialchars($href) . ">" . htmlspecialchars($href) . "</a></td><td>" . htmlspecialchars($gen) . "</td></tr>";
}
$t .= "</table></body>";
if (file_put_contents("tests/list.php", $t) === false) {
    die("error writing tests/list.php\n");
}

//echo "Done\n";

//echo "Writing tests/index.txt ..\n";

$t = "";
foreach ($lst as $ln) {
    list($href, $gen) = $ln;
    $t .= "$href\t$gen\n\n";
}
if (file_put_contents("tests/index.txt", $t) === false) {
    die("error writing tests/index.txt\n");
}

//echo "Done\n";
            
?>
