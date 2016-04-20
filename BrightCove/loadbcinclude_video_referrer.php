<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
//phpinfo();
/*
echo "CREDENTIALS \n\n\n\n";
echo $BrightCoveModifyCredentials;
echo "END CREDENTIALS  \n\n\n\n";
*/
$connect = pg_connect($BrightCoveModifyCredentials);
/*
$result2 = pg_query($connect, "select isnull(max(dt)+1,getdate()-479)::date maxdt, dateadd(day,1,isnull(max(dt)+1,getdate()-479))::date from public.bc_videos_referrer");

   while ($row = pg_fetch_array($result2)) {
     $fromdate= $row[0];
     $todate= $row[1];  //One More Day
   }
*/





















$sql = "

CREATE TABLE if not exists public.bc_videos_referrer  ( 
	video               	int8 NULL ENCODE LZO ,
	video_view          	int4 NULL ENCODE LZO,
	videoname           	varchar(10000) NULL ENCODE LZO,	
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_reference_id  	varchar(10000) NULL ENCODE LZO,	
	video_duration      	float8 NULL ENCODE bytedict,
	referrer_domain 		varchar(10000) NULL ENCODE LZO,	
	source_type varchar(10000) NULL ENCODE LZO,	
	dt                  	date NULL ENCODE LZO sortkey  distkey,
	azvideoid           	int8 NULL ENCODE LZO,
	azvideotype         	varchar(100) NULL ENCODE LZO,
	azbroadcaster       	varchar(10000) NULL ENCODE LZO 	
	)
DISTSTYLE KEY;

drop table if exists public.bc_videos_referrer_staging;
CREATE TABLE public.bc_videos_referrer_staging  ( 
	video               	int8 NULL ENCODE LZO ,
	video_view          	int4 NULL ENCODE LZO,
	videoname           	varchar(10000) NULL ENCODE LZO,	
	video_seconds_viewed	int8 NULL ENCODE LZO,
	video_reference_id  	varchar(10000) NULL ENCODE LZO,	
	video_duration      	float8 NULL ENCODE bytedict,
	referrer_domain 		varchar(10000) NULL ENCODE LZO,	
	source_type varchar(10000) NULL ENCODE LZO,	
	dt                  	date NULL ENCODE LZO sortkey  distkey,
	azvideoid           	int8 NULL ENCODE LZO,
	azvideotype         	varchar(100) NULL ENCODE LZO,
	azbroadcaster       	varchar(10000) NULL ENCODE LZO 	
	)
DISTSTYLE KEY;

copy public.bc_videos_referrer_staging
from 's3://$S3bucketName/bcoutput_video_referrer.json' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
json  'auto'
$S3Region;

/* Get rid of bad data */
--delete from bc_videos_referrer_staging where video is null;

/* Update Date */
update bc_videos_referrer_staging set dt = '$fromdate';




update bc_videos_referrer_staging
set azvideoid=
CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),1,charindex('VOD',substring(video_reference_id,6))-1  )::bigint

WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),1,charindex('CH',substring(video_reference_id,6))-1  )::bigint

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN substring(substring(video_reference_id,6),1,charindex('SV',substring(video_reference_id,6))-1  )::bigint

ELSE
NULL
END-- azvideoid
,azbroadcaster=CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),charindex('VOD',substring(video_reference_id,6))+3  )


WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN substring(substring(video_reference_id,6),charindex('CH',substring(video_reference_id,6))+2  )

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN substring(substring(video_reference_id,6),charindex('SV',substring(video_reference_id,6))+2  )

ELSE
NULL
END -- azbroadcaster 
,azvideotype=
CASE
WHEN charindex('VOD',substring(video_reference_id,6)) >0  
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('CH',substring(video_reference_id,6)) = 0 then 100000 else charindex('CH',substring(video_reference_id,6)) end ) 
        and ( charindex('VOD',substring(video_reference_id,6))  < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN  'VOD'

WHEN charindex('CH',substring(video_reference_id,6)) >0 
    and (charindex('CH',substring(video_reference_id,6)) < case when charindex('SV',substring(video_reference_id,6)) = 0 then 100000 else charindex('SV',substring(video_reference_id,6)) end ) 
THEN  'CH'

WHEN charindex('SV',substring(video_reference_id,6)) >0 
THEN 'SV'

ELSE
NULL
END 
where azvideoid is null;

/*  Delete existing data so that we can load clean data*/
delete from public.bc_videos_referrer
where dt in 
(select distinct dt  from public.bc_videos_referrer_staging b);


/* Load the final de-duped data */
insert into public.bc_videos_referrer
select distinct * from public.bc_videos_referrer_staging a
where not exists (select 1 from public.bc_videos_referrer b where a.dt=b.dt and a.video=b.video);


delete from public.bc_videos_referrer
	where video_seconds_viewed> 4000000000  --Get rid of outliers;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>