<?php
namespace Worker;

use App\Hosting\Status;
use App\Models\Hosting;
use Exception;

class HttpManager {

    public static function refreshServer(): bool {
        $returnValue = null;
        $output = array();
        exec("nginx -test", $output, $returnValue);
        if($returnValue == 0) {
            $pidFile = '/var/run/nginx/nginx.pid';
            if (file_exists($pidFile)) {
                $pid = trim(file_get_contents($pidFile));
                $result = posix_kill($pid, SIGHUP);
                if ($result) {
                    // echo "Successfully sent HUP signal to nginx (PID: $pid)\n";
                    return true;
                }
                //echo "Failed to send HUP signal to nginx (PID: $pid)\n";
            }
            return false;
        }
    }


    // method to create ftp access? 

    function deleteHttpFile($args): bool {
        $domain = $args["domain"];
        $tld = $args["tld"];
        $destination_name = "/etc/nginx/http.d/$domain.$tld.conf";
        if (file_exists($destination_name)) {
            if (unlink($destination_name)) {
                HttpManager::refreshServer();
                return true;
            } else {
                throw new Exception("COULD_NOT_DELETE_FILES");
            }
        }
        HttpManager::refreshServer();
        return false;
    }


    public static function createHttpFile($args): bool {
        // make folder?
        // create ftp 
        $domain = $args["domain"];
        $tld = $args["tld"];
$template = <<<EOD
server {
	listen 80;
	listen [::]:80;
	server_name $domain.$tld *.$domain.$tld;
	root /var/www/$domain$tld;
	index index.html;
}
EOD;
        $destination_name = "/etc/nginx/http.d/$domain.conf";
        $destination_file = fopen($destination_name, "w");
        if (flock($destination_file, LOCK_EX)) {
            fwrite($destination_file, $template);
            // probably should test config
            // also should probably backup before writing
            flock($destination_file, LOCK_UN);
        } else {
            throw new Exception("COULD_NOT_GET_FILE_ACCESS");
            return false;
        }
        fclose($destination_file);
        // should probably check if something exists there already
        mkdir("/var/www/$domain".$tld);
        chown("/var/www/$domain".$tld, "ftp");
        chgrp("/var/www/$domain".$tld, "ftp");
        $ftppassword = self::generatepassword();
        FtpManager::addRecord($domain.$tld,$ftppassword);
        HttpManager::refreshServer();
        Hosting::where("domain", $domain)->update([
            "status" => Status::Running->value,
            "ftp" => $ftppassword
        ]);
        return true;
    }

    public static function generatepassword(): string {
        $randomInt = random_int(0, pow(36, 5) - 1);
        $randomString = base_convert($randomInt, 10, 36);
        $randomString = str_pad($randomString, 5, '0', STR_PAD_LEFT);
        $randomString = substr($randomString, 0, 5);
        return $randomString;
    }

}
