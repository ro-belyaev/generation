<?php
error_reporting(E_ALL);
ini_set("error_log","error.log");

set_include_path('.');

require_once "exceptions.php";
require_once "config.php";
require_once "scanner.php";
require_once "testpage.php";
require_once "ex.php";
require_once "testres.php";
require_once "mytests.php";
require_once "realtests.php";

class Tester {
    private $sc, $tp;
    public function __construct(Scanner $sc, TestPage $tp) {
        $this->sc = $sc;
        $this->tp = $tp;
    }

    public function test() {
        $this->sc->prepare();
        $this->tp->prepare();
        $r = $this->sc->run($this->tp->url);
        $this->sc->clean();
        return new TestRes($this->tp->getlog(),
                           $this->tp->name,
                           $this->tp->url,
                           $r->log,
                           $r->state,
                           $r->ans,
                           $r->realtime,
                           $r->usertime,
                           $r->systime,
                           $r->cmd);
    }
}

function preparetmp() {
    $cmd = "touch tmp/tmp && rm -Rf tmp/* && mkdir tmp/scanners && cp -R ../scanners/* tmp/scanners/ 2>&1 && echo ok";
    echo "$cmd\n";
    if (trim($r = ex($cmd)) !== 'ok') {
        die("error prepare tmp\n");
    }
}

function runtests($testpages, $scanners) {
    //    var_dump($testpages);
    preparetmp();
    $ind = '';
    $resd = "results/" . date("Y-m-d_H-i-s", time());
    if (mkdir($resd) === false) die("error mkdir '$resd'");
    $t = '';
    foreach ($scanners as $sc) $t .= $sc->name() . "\n";
    if (false === file_put_contents("$resd/scanners.txt", $t)) die("error writing '$resd\scanners.txt'\n");
    foreach ($testpages as $ti => $tp) {
        $ti = "$ti";
        $ind .= "$tp->url\t$tp->name\n\n";
        foreach ($scanners as $sc) {
            $resf = "$resd/${ti}_" . $sc->name() . ".result";
            echo "Run " . get_class($sc) . " on " . $tp->name . "..\n";
            $tester = new Tester($sc, $tp);
            $res = $tester->test();
            if (false === file_put_contents($resf, gzcompress(serialize($res)))) die("error writing '$resf'\n");
            
        }
    }
    if (false === file_put_contents("$resd/index.txt", $ind)) die("error writing '$resd/index.txt'");
    echo "\n\n\nAll done.\n$resd\n";
}


function superdie($s = "nomsg") {
    echo "SUPERDIE $s";
    posix_kill(0, SIGKILL);
    die("superdie error\n");
}

function run1test_($sc, $tp, $ti, $resd) {
    pcntl_exec($_SERVER['_'], array("run1test.php", base64_encode(serialize($sc)), base64_encode(serialize($tp)), "$ti", $resd));
    superdie("exec error");
}

function run1test($sc, $tp, $ti, $resd) {
    echo "INFLATER =" . memory_get_usage() . "\n";
    $resf = "$resd/${ti}_" . $sc->name() . ".result";
    echo "Run " . get_class($sc) . " on " . $tp->name . "..\n";
    try {
        $tester = new Tester($sc, $tp);
        $res = $tester->test();
    } catch (Exception $e) {
        superdie("exc $e");
    }
    echo "Run ok\n";
//    file_put_contents("${resf}.txt", print_r($res, true));
    if (false === file_put_contents("$resf.tmp", gzcompress(serialize($res)))) {
        superdie("error writing '$resf'");
    }
    if (false === rename("$resf.tmp", "$resf")) {
        superdie("error renaming to '$resf'");
    }
    
}

function mlog($s) {
    file_put_contents("mlog.log", date("D M j G:i:s T Y") . "\t$s\n", FILE_APPEND);
}

function runtests_multi(&$testpages, &$scanners, $thrc, $resd = null) {
    echo "BEGIN =" . memory_get_usage() . "\n";
    ini_set('memory_limit', 30000000000);
    preparetmp();
    if (!$resd) {
        $resd = "results/" . date("Y-m-d_H-i-s", time());
        if (mkdir($resd) === false) die("error mkdir '$resd'");
        $cont = false;
    } else {
        $cont = true;
    }
    $t = '';

    foreach ($scanners as $sc) $t .= $sc->name() . "\n";
    $ind = '';
    foreach ($testpages as $ti => $tp) {
        $ind .= "$tp->url\t$tp->name\n\n";
    }
    //        if (false === file_put_contents("$resd/index.txt", $ind)) die("error writing '$resd/index.txt'");

    if ($cont) {
        if (file_get_contents("$resd/scanners.txt") !== $t) die("$resd/scanners.txt doesn't match\n");
        if (file_get_contents("$resd/index.txt") !== $ind) die("$resd/index.txt doesn't match\n");
    } else {
        if (false === file_put_contents("$resd/scanners.txt", $t)) die("error writing '$resd/scanners.txt'\n");
        if (false === file_put_contents("$resd/index.txt", $ind)) die("error writing '$resd/index.txt'");
    }
    $testlocks = array();
    $batch = array();
    $bi = 0;
    foreach ($testpages as $ti => $tp) {
        $ti = "$ti";
        $testlocks[$ti] = 0;
        foreach ($scanners as $sc) {
            $resf = "$resd/${ti}_" . $sc->name() . ".result";
            if (!file_exists($resf)) {
                $batch[$bi] = array("tp" => $tp, "sc" => $sc, "ti" => $ti, "state" => "before", "pid" => 0, "bi" => $bi);
                $bi++;
            } else {
                echo "SKIP $resf\n";
            }
            //            var_dump($t);
        }
    }
    echo "INITED =" . memory_get_usage() . "\n";

    $t_running = 0;

    $blinks = array();
    
    echo "sort batch.. ";
    uasort($batch, function($f1, $f2) {
            $p1 = get_class($f1["sc"]) === "ZeroScanner";
            $p2 = get_class($f2["sc"]) === "ZeroScanner";
            if ($p1 < $p2) return -1;
            if ($p1 > $p2) return 1;
            if ($f1["sc"]->weight() < $f2["sc"]->weight()) return -1;
            if ($f1["sc"]->weight() > $f2["sc"]->weight()) return 1;
            return $f1["ti"] - $f2["ti"];
        });
    echo "ok\n";

    mlog("while true");
    for ($ch = 0; ;++$ch) {
        if ($ch % 500 == 0) {
            $mins = 10000000000;
            foreach ($batch as &$b) {
                $mins = min($mins, $b["sc"]->weight());
            }
            unset($b);
        }
        mlog("while.. t_running=$t_running");
        $thf = true;
        echo "t_running = $t_running\n\n";
        echo "mins = $mins\n";
        if ($thf && $t_running < $thrc && !($t_running <= $thrc - $mins)) {
            echo "\n\nSKIPPPPPPP HERE!\n-\n-\n-\n-\n";
        }
        while ($thf && $t_running <= $thrc - $mins) {
            mlog("foreach batch t_running=$t_running");
            $thf = false;
            $frc = 0;
            foreach ($batch as $bi => &$b) {
                ++$frc;
                if ($b["state"]=="before" && $testlocks[$b["ti"]] == 0 && ($t_running == 0 || ($t_running + $b["sc"]->weight()) <= $thrc)) {
                    mlog("fork $bi $frc..");
                    if ($t_running + $b["sc"]->weight() > $thrc) {
                        echo "no choice, run " . get_class($b["sc"]) . " with weight " . $b["sc"]->weight() . "\n";
                    }
                    $b["state"] = "running";
                    echo "main-fork..\n";
                    $pid = pcntl_fork();
                    if ($pid < 0) {
                        superdie("cant fork");
                    } elseif ($pid == 0) {
                        echo "AFTER FORK =" . memory_get_usage() . "\n";
                        $sc = $b["sc"];
                        $tp = $b["tp"];
                        $ti = $b["ti"];
                        //                        register_shutdown_function('run1test', $sc, $tp, $ti, $resd);
                        
                        run1test_($sc, $tp, $ti, $resd);
                        exit();
                    } else {
                        mlog("forked");
                        $testlocks[$b["ti"]] = $b["pid"] = $pid;
                        $t_running += $b["sc"]->weight();
                        $blinks[$pid] = &$b;
                        echo "thr $pid forked (" . $b["ti"] . ", " . get_class($b["sc"]) . ")\n";
                        $thf = true;
                        if ($t_running <= $thrc - $mins) {
                            echo "sleep..\n";
                            sleep(0.1);
                        }
                        break;
                    }
                }
            }
            mlog("foreach end");
            unset($b);
            echo "t_running = $t_running\n\n";
            echo "mins = $mins\n";
            if ($thf && $t_running < $thrc && !($t_running <= $thrc - $mins)) {
                echo "\n\nSKIPPPPPPP HER2E!\n-\n-\n-\n-\n";
            }
        }
        mlog("while.. end");
        if ($t_running == 0) {
            break;
        }
        echo "main-wait..\n";
        $w = pcntl_wait($status);
        mlog("waiten");
        if (!pcntl_wifexited($status)) {
            superdie("thread $w aborted\n");
        }
        
        unset($status);
        echo "thr $w ended\n";
        $t_running -= $blinks[$w]["sc"]->weight();
        $testlocks[$blinks[$w]["ti"]] = 0;
        $blinks[$w]["state"] = "done";
        $blinks[$w]["pid"] = 0;
        $bi = $blinks[$w]["bi"];
        unset($blinks[$w]);
        unset($batch[$bi]);
        mlog("unset bi $bi");
        /*
        foreach ($batch as &$b) {
            if ($b["pid"] === $w) {
                $b["state"] = "done";
                $b["pid"] = 0;
            }
        }
        unset($b);
        foreach ($testlocks as $ti => &$tl) {
            if ($tl === $w) {
                $tl = 0;
                echo "Test $ti unlocked\n";
            }
        }
        unset($tl);
        */
        echo "main-prd..\n";

    }

    echo "\n\n\nAll done.\n$resd\n";


}

function unman_list(&$testpages) {
    $ind = '';
    foreach ($testpages as $ti => $tp) {
        $ind .= "$tp->url\n";
    }
    return $ind;
}

function unman_prepare(&$testpages) {
    echo "unmanaged prepare..\n";
    foreach ($testpages as $ti => $tp) {
        echo "$ti\n";
        $tp->prepare();
    }
    echo "done\n";
}


?>