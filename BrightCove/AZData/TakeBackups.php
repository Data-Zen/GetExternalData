<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);

$tables = 'bc_videos, zencoder, users';
#$string = preg_replace('/\.$/', '', $string); //Remove dot at end if exists
$array = explode(', ', $tables); //split string into array seperated by ', '
foreach($array as $tablename) //loop over values
{
       echo "\n\n****************************************************Backuping up ".$tablename."****************************************************\n\n"; 


       $sql = "drop table if exists ".$tablename."_backup_7;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_6 RENAME TO ".$tablename."_backup_7;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_5 RENAME TO ".$tablename."_backup_6;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_4 RENAME TO ".$tablename."_backup_5;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_3 RENAME TO ".$tablename."_backup_4;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_2 RENAME TO ".$tablename."_backup_3;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_1 RENAME TO ".$tablename."_backup_2;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "select * into ".$tablename."_backup_1 from ".$tablename.";";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);
       $rowsaffected=pg_affected_rows($rec);
       echo "Rows affected $rowsaffected \n\n";

       echo "\n\n****************************************************Completed Backup of ".$tablename."**************************************************** \n\n";
}


?>