<?php

date_default_timezone_set('UTC');//or change to whatever timezone you want



if (!empty( $argv[1])) 
{ $a = $argv[1];}
else
{ $a=3;}

if (!empty( $argv[2])) 
{ $backfill = $argv[2];
	}
else
{ $backfill=0;}

if (!empty( $argv[3])) 
{ $daysback = $argv[3];
	}
else
{ $daysback=10;}




echo "\n\n backfill: $backfill \n\n"; 





include './BrightCove/credentials/BrightCoveCredentials.php';

$connect = pg_connect($BrightCoveReadOnlyCredentials);
if ($backfill ==0)
{
$sql= "select isnull(max(dt)-$daysback+$a,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)-$daysback+$a,getdate()-479))::date from public.bc_videos";

}
else
{
$sql= "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos";

}
echo "\n\nSQL ".$sql . "\n\n";
$result2 = pg_query($connect, $sql);

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }

$fromdateepoch = strtotime($fromdate." UTC");
$todateepoch = strtotime($todate." UTC");

if ($fromdateepoch > 0  and $todateepoch > 0) 
{
echo  "FromDate: " . $fromdate. "\n";
echo  "ToDate: " . $todate. "\n";
echo  "FromDateEpoch: " . $fromdateepoch. "\n";
echo  "ToDateEpoch: " . $todateepoch. "\n";
}
else
{
echo "Something was wrong with your dates, try one of the below formats:", "\n";
echo "now", "\n";
echo "10 September 2000", "\n";
echo "-1 day", "\n";
echo "-1 week", "\n";
echo "-1 week 2 days 4 hours 2 seconds", "\n";
echo "next Thursday", "\n";
echo "last Monday", "\n";
exit;
}

if(strtotime('+1 day') <strtotime($todate." UTC"))

{
echo "\n\n\n\n\nFUTURE \n\n\n\n\n";
exit("Was in the future already so exited procedure");
}
$limit="100000000";
echo "Limit:" . $limit . "\n";

echo "\nStart Timer \n";
$start = microtime(true); 
$start1 = microtime(true); 
include './BrightCove/getBCinclude_video.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_account.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_player.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_date.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_date_hour.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_destination_domain.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_destination_path.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_country.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_city.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_region.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_referrer_domain.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_source_type.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_search_terms.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_device_type.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_device_os.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/getBCinclude_video_multipledimensions.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 

#include './BrightCove/getBCinclude_account.php';

include './BrightCove/bcs3.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 

include './BrightCove/loadbcinclude_tags.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_video.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_account.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_player.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_date.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_date_hour.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_destination_domain.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_destination_path.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_country.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_city.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_region.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_referrer_domain.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_source_type.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_search_terms.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_device_type.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_device_os.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_video_country.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_video_device.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_video_destination.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_video_referrer.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";
$start1 = microtime(true); 
include './BrightCove/loadbcinclude_destination_domain_path.php';
$end1 = round((microtime(true) - $start1),2);
echo "\nelapsed time: $end1 seconds \n";

#include './BrightCove/loadbcinclude_video_source.php';
$end = round((microtime(true) - $start),2);
echo "\nelapsed time: $end seconds \n";
if ($rowsaffected=0 ){
exit (999);
}


?>

