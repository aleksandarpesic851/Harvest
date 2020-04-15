<?php 
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

    use Ozdemir\Datatables\DB\MySQL;
    use Ozdemir\Datatables\Datatables;

    $ini = parse_ini_file( $_SERVER['DOCUMENT_ROOT'] . "/app.ini");

    $config = [ 'host'     => $ini["db_host"],
                'port'     => $ini["db_port"],
                'username' => $ini["db_user"],
                'password' => $ini["db_password"],
                'database' => $ini["db_name"] ];

    $dt = new Datatables( new MySQL($config) );

    $dt->query("Select drug_name, synonym, id as DT_RowId from drugs");

    echo $dt->generate();
?>