<?php

echo "\nStart Timer \n";
$start = microtime(true); 

date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);

$tables = 'bc_videos, zencoder, users';
#$string = preg_replace('/\.$/', '', $string); //Remove dot at end if exists
$array = explode(', ', $tables); //split string into array seperated by ', '
foreach($array as $tablename) //loop over values
{
       echo "\n\n****************************************************Backuping up ".$tablename."****************************************************\n\n"; 


       $sql = "drop table if exists ".$tablename."_backup_15;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_14 RENAME TO ".$tablename."_backup_15;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_13 RENAME TO ".$tablename."_backup_14;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_12 RENAME TO ".$tablename."_backup_13;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_11 RENAME TO ".$tablename."_backup_12;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_10 RENAME TO ".$tablename."_backup_11;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_9 RENAME TO ".$tablename."_backup_10;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_8 RENAME TO ".$tablename."_backup_9;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);


       $sql = "ALTER TABLE ".$tablename."_backup_7 RENAME TO ".$tablename."_backup_8;";
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
$end = round((microtime(true) - $start),2);
echo "\nelapsed time: $end seconds \n";

?>