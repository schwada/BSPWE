<?php
$local_ip = getHostByName(getHostName());
$hosts = [
    "/etc/dnsmasq.d/users.hosts.dat" => "/etc/dnsmasq.d/users.hosts",
    "/etc/dnsmasq.d/priority.hosts.dat" => "/etc/dnsmasq.d/priority.hosts",
];

function processFile($unprocessed_name, $destination_name, $local_ip) {
    $unprocessed_file = fopen($unprocessed_name, "r");
    $destination_file = fopen($destination_name, "w");
    if (flock($unprocessed_file, LOCK_EX) && flock($destination_file, LOCK_EX)) {
        while (!feof($unprocessed_file)) {
            $line = fgets($unprocessed_file);
            fwrite($destination_file, str_replace("XXX", $local_ip, $line));
        }
        // conf test should be done here
        flock($unprocessed_file, LOCK_UN);
        flock($destination_file, LOCK_UN);
    } else {
        throw new Exception("COULD_NOT_GET_FILE_ACCESS");
    }
    fclose($unprocessed_file);
    fclose($destination_file);
}
foreach ($hosts as $unprocessed_name => $destination_name) {
    processFile($unprocessed_name, $destination_name, $local_ip);
}

$returnValue = null;
$output = array();
exec("dnsmasq --test", $output, $returnValue);
if($returnValue == 0) {
    $pidFile = '/var/run/dnsmasq.pid';
    if (file_exists($pidFile)) {
        $pid = trim(file_get_contents($pidFile));
        $result = posix_kill($pid, SIGHUP);
        if ($result) {
            echo "Successfully sent HUP signal to dnsmasq (PID: $pid)\n";
        } else {
            echo "Failed to send HUP signal to dnsmasq (PID: $pid)\n";
        }
    } else {
        echo "Unable to find PID file: $pidFile\n";
    }
}

