<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);


       #echo "\n\n****************************************************Backuping up ".$tablename."****************************************************\n\n"; 

/*
       $sql = "SELECT DISTINCT 'vacuum ' + \"schema\" + '.' + \"table\" + ' ; '  as resultcolumn
FROM   svv_table_info 
where schema not ilike 'pg_internal'

ORDER  BY \"size\" desc;";
       echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
       $rec = pg_query($connect,$sql);

       $rowsaffected=pg_affected_rows($rec);
       echo "Rows affected $rowsaffected \n\n";

       #echo "\n\n****************************************************Completed Backup of ".$tablename."**************************************************** \n\n";
#}
*/


/*
while ($row = pg_fetch_array($rec)) {
    $sql2=$row['resultcolumn'];
     
       echo "\n*******StartQuery\n".$sql2."\n*******EndQuery\n";
       $rec2 = pg_query($connect,$sql2);

}

*/

$sql = "
vacuum;
analyze;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);



?>