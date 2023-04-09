<?php
namespace Worker;

use App\Hosting\Status;
use App\Models\Domain;
use Exception;

class DnsManager {

    const USERS_HOST = "/etc/dnsmasq.d/users.hosts.dat";
    const DEFAULT_HOSTS = [
        "/etc/dnsmasq.d/users.hosts.dat" => "/etc/dnsmasq.d/users.hosts",
        "/etc/dnsmasq.d/priority.hosts.dat" => "/etc/dnsmasq.d/priority.hosts",
    ];

    public static function processFiles() {
        foreach (self::DEFAULT_HOSTS as $unprocessed_name => $destination_name) {
            self::processFile($unprocessed_name, $destination_name);
        }
    }

    public static function processUserFiles() {
        self::processFile(self::USERS_HOST, self::DEFAULT_HOSTS[self::USERS_HOST]);
    }

    public static function refreshServer(): bool {
        $returnValue = null;
        $output = array();
        exec("dnsmasq --test", $output, $returnValue);
        if($returnValue == 0) {
            $pidFile = '/var/run/dnsmasq.pid';
            if (file_exists($pidFile)) {
                $pid = trim(file_get_contents($pidFile));
                $result = posix_kill($pid, SIGHUP);
                if ($result) {
                    return true;
                    // echo "Successfully sent HUP signal to dnsmasq (PID: $pid)\n";
                } else {
                    return false;
                    // echo "Failed to send HUP signal to dnsmasq (PID: $pid)\n";
                }
            } 
            // else { echo "Unable to find PID file: $pidFile\n"; }
        }
        return false;
    }

    public static function addHostsfileRecord($args): void {
        // check exitence ?
        $handle = fopen(self::USERS_HOST, "a");
        if (flock($handle, LOCK_EX)) {
            // probably should test config
            // also should probably backup before writing
            fwrite($handle, $args['hosts_record'] . "\n");
            flock($handle, LOCK_UN);
        } else {
            throw new Exception("COULD_NOT_GET_FILE_ACCESS");
            echo "could not get access";
        }
        Domain::where("domain", $args['domain'])->update(["status" => Status::Running->value]);
        self::processUserFiles();
        self::refreshServer();
    }
    
    public static function findHostfileRecordByDomain($args): bool {
        $found = false;
        $handle = fopen(self::USERS_HOST, 'r');
        if (flock($handle, LOCK_EX)) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/\s' . $args["domain"] . '\s/', $line)) {
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
    
    function removeHostfileRecordByDomain($args): void {
        // check exitence ?
        // read lines minus lines to delete 
        $handle = fopen(self::USERS_HOST, 'r+');
        $lines = array();
        if (flock($handle, LOCK_EX)) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/\s' . $args['domain'] . '\s/', $line)) continue;
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

    private static function processFile($unprocessed_name, $destination_name) {
        $local_ip = getHostByName(getHostName());
        $unprocessed_file = fopen($unprocessed_name, "r");
        $destination_file = fopen($destination_name, "w");
        if (flock($unprocessed_file, LOCK_EX) && flock($destination_file, LOCK_EX)) {
            while (!feof($unprocessed_file)) {
                $line = fgets($unprocessed_file);
                fwrite($destination_file, str_replace("XXX", $local_ip, $line));
            }
            // conf test should be done here
            // backup too -> "transactions"
            flock($unprocessed_file, LOCK_UN);
            flock($destination_file, LOCK_UN);
        } else {
            throw new Exception("COULD_NOT_GET_FILE_ACCESS");
        }
        fclose($unprocessed_file);
        fclose($destination_file);
    }
}
