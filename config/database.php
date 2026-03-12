<?php

function getDB()
{
    $dbType = "mysql";
    $dbName = "cafeteria";
    $host = "localhost";
    $userName = "root";
    $password = "";

        try {
            $connection = new PDO("$dbType:host=$host;dbname=$dbName", $userName, $password);
            echo "Connected successfully";
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    return $connection;
}
?>