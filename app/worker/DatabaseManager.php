<?php
namespace Worker;

use App\Hosting\Status;
use App\Models\Database;

class DatabaseManager {

    public static function createDatabase($args)
    {
        $password = self::generatepassword();
        $conn = $args['db']->getConnection();
        // $conn->statement("CREATE USER ".$args['domain'].$args['tld']." WITH PASSWORD '$password'");
        // $conn->statement("CREATE DATABASE ".$args['domain'].$args['tld']);
        // $conn->statement("GRANT ALL PRIVILEGES ON DATABASE ".$args['domain'].$args['tld']." TO ".$args['domain'].$args['tld']);
        $DBNAME = $args['domain']."_".$args['tld'];
        $DBMAINUSER = $args['domain'].$args['tld'];
        $conn->statement("CREATE ROLE $DBNAME NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOLOGIN;");
        $conn->statement("CREATE ROLE $DBMAINUSER NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT LOGIN ENCRYPTED PASSWORD '$password';");
        $conn->statement("GRANT $DBNAME TO $DBMAINUSER;");
        $conn->statement("CREATE DATABASE $DBNAME WITH OWNER=$DBMAINUSER;");
        $conn->statement("REVOKE ALL ON DATABASE $DBNAME FROM public;");
        $conn->statement("ALTER USER $DBMAINUSER SET search_path = $DBNAME;");
        Database::where("domain", $args['domain'])->update([
            "status" => Status::Running->value,
            "db" => $password
        ]);
    }

    public static function generatepassword(): string {
        $randomInt = random_int(0, pow(36, 5) - 1);
        $randomString = base_convert($randomInt, 10, 36);
        $randomString = str_pad($randomString, 5, '0', STR_PAD_LEFT);
        $randomString = substr($randomString, 0, 5);
        return $randomString;
    }
}