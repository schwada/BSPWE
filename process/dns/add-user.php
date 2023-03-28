<?php
$local_ip = getHostByName(getHostName());
$unloaded_hostsfile = "/etc/dnsmasq.d/users.hosts.dat";
$active_hostsfile = "/etc/dnsmasq.d/users.hosts";
$newdomain = "www.poopoopeepee.cz";

function addHostsfileRecord($hostsfile, $hosts_record): void {
    $handle = fopen($hostsfile, "a");
    if (flock($handle, LOCK_EX)) {
        // probably should test config
        // also should probably backup before writing
        fwrite($handle, $hosts_record);
        flock($handle, LOCK_UN);
    } else {
        throw new Exception("COULD_NOT_GET_FILE_ACCESS");
        echo "could not get access";
    }
    fclose($handle);
}

function findHostfileRecord($hostsfile, $domain): bool {
    $found = false;
    $handle = fopen($hostsfile, 'r');
    if (flock($handle, LOCK_EX)) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/\s' . $domain . '\s/', $line)) {
                $found = true;
                break;
            }
        }
        flock($handle, LOCK_UN);
    } else {
        throw new Exception("COULD_NOT_GET_FILE_ACCESS");
    }
    fclose($handle);
    return $found;
}

function removeHostfileRecordByDomain($hostsfile, $domain): void {
    // read lines minus lines to delete 
    $handle = fopen($hostsfile, 'r+');
    $lines = array();
    if (flock($handle, LOCK_EX)) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/\s' . $domain . '\s/', $line)) continue;
            if (trim($line) == "") continue;
            $lines[] = $line;
        }
        ftruncate($handle, 0);
        rewind($handle);
        foreach ($lines as $line) fwrite($handle, $line);
        flock($handle, LOCK_UN);
    } else {
        throw new Exception("COULD_NOT_GET_FILE_ACCESS");
    }
    fclose($handle);
}

// if(findHostfileRecord($unloaded_hostsfile, $newdomain)) {
//     removeHostfileRecordByDomain($unloaded_hostsfile, $newdomain);
// } else {
//     addHostsfileRecord($unloaded_hostsfile ,"XXX $newdomain #user=peepee\n");
// }