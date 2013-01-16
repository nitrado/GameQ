<?php
/**
 * This is a example script how to query many servers in a short time.
 * It needs your own implementation for the handleReults() Method.
 */
ini_set('display_errors', 'on');
error_reporting(E_ALL);
require '../GameQ.php';
$serversPerChild = 50;

// Put the servers in this array
$servers = array(
    0 => array('type' => 'dayz', 'host' => '127.0.0.1:2302'),
    1 => array('type' => 'minecraft', 'host' => '127.0.0.1:25565'),
);

// Implement your result function here
function handleResults($i, $results) {
    echo "Implement me!" . PHP_EOL;
    //Example: file_put_contents('result_' . $i, serialize($result));
}

$forksNeeded = ceil(count($servers) / $serversPerChild);
$pids = array();
for ($i = 0; $i < $forksNeeded; $i++) {
    $pids[$i] = pcntl_fork();
    if (!$pids[$i]) {
        echo "Child querying part $i" . PHP_EOL;
        $myServers = array_slice($servers, $i * $serversPerChild, $serversPerChild);
        $gameq = new GameQ();
        $gameq->setFilter('normalise');
        $gameq->addServers($myServers);
        $result = $gameq->requestData();
        $online = $offline = 0;
        foreach ($result as $res) {
            if ($res['gq_online']) {
                $online++;
            } else {
                $offline++;
            }
        }
        handleResults($i, $results);
        echo "$online/" . count($result) . " online" . PHP_EOL;
        exit(0);
    }
}

for ($i=0; $i < $forksNeeded; $i++) {
    echo "Parent waiting for child $i to die" . PHP_EOL;
    pcntl_waitpid($pids[$i], $status, WUNTRACED);
}
echo "Parent done" . PHP_EOL;
exit(0);
