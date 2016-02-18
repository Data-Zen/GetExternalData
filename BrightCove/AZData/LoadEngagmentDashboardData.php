<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "

drop table if exists EngagmentDashboardData;

create table EngagmentDashboardData
(
id BIGINT ENCODE lzo,
video_id BIGINT ENCODE lzo,
video_title VARCHAR(10000) ENCODE lzo,
video_duration BIGINT ENCODE lzo,
video_created_at datetime ENCODE lzo,
video_view BIGINT ENCODE lzo,
video_view_amount_second BIGINT ENCODE lzo,
video_peak_ccu BIGINT ENCODE lzo,
video_average_ccu FLOAT ENCODE BYTEDICT,
type INT ENCODE LZO,
bc_video_id BIGINT ENCODE LZO,
reference_id VARCHAR(10000) ENCODE lzo,
user_id BIGINT ENCODE lzo,
user_username VARCHAR(10000) ENCODE lzo,
team_id INT ENCODE LZO,
team_name VARCHAR(10000) ENCODE lzo,
team_title VARCHAR(10000) ENCODE lzo,
league_id VARCHAR(10000) ENCODE lzo,
league_name VARCHAR(10000) ENCODE lzo,
league_title VARCHAR(10000) ENCODE lzo,
date datetime ENCODE lzo,
created_at datetime ENCODE lzo,
updated_at datetime ENCODE lzo,
category_id INT ENCODE LZO,
category_name VARCHAR(10000) ENCODE lzo,
category_title VARCHAR(10000) ENCODE lzo
);


copy EngagmentDashboardData
from 's3://$S3bucketNameGFLDailyDumps/analytics_video_play.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 1
$S3Region;




GRANT SELECT ON TABLE public.EngagmentDashboardData TO GROUP readonly;


drop table if exists  public.engagmentdashboarddata_rollup;
CREATE TABLE public.engagmentdashboarddata_rollup
(
	id BIGINT ENCODE lzo,
	video_id BIGINT ENCODE lzo,
	video_title VARCHAR(10000) ENCODE lzo,
	video_duration BIGINT ENCODE lzo,
	video_created_at TIMESTAMP ENCODE lzo,
	video_view BIGINT ENCODE lzo,
	video_view_amount_second BIGINT ENCODE lzo,
	video_peak_ccu BIGINT ENCODE lzo,
	video_average_ccu DOUBLE PRECISION ENCODE bytedict,
	type INTEGER ENCODE lzo,
	bc_video_id BIGINT ENCODE lzo,
	reference_id VARCHAR(10000) ENCODE lzo,
	user_id BIGINT ENCODE lzo,
	user_username VARCHAR(10000) ENCODE lzo,
	team_id INTEGER ENCODE lzo,
	team_name VARCHAR(10000) ENCODE lzo,
	team_title VARCHAR(10000) ENCODE lzo,
	league_id VARCHAR(10000) ENCODE lzo,
	league_name VARCHAR(10000) ENCODE lzo,
	league_title VARCHAR(10000) ENCODE lzo,
	date TIMESTAMP ENCODE lzo,
	created_at TIMESTAMP ENCODE lzo,
	updated_at TIMESTAMP ENCODE lzo,
	category_id INTEGER ENCODE lzo,
	category_name VARCHAR(10000) ENCODE lzo,
	category_title VARCHAR(10000) ENCODE lzo,
	ACCCU   BIGINT ENCODE lzo,
	Video_type VARCHAR(10000) ENCODE lzo
)
DISTSTYLE EVEN;

GRANT SELECT ON TABLE public.engagmentdashboarddata_rollup TO GROUP readonly;

INSERT INTO engagmentdashboarddata_rollup
SELECT max(id)
                , video_id
                , max(video_title)
                , max(video_duration)
                , max(video_created_at)
                , sum(video_view)
                , sum(video_view_amount_second)
                , max(video_peak_ccu)
                , avg(video_average_ccu)
                , type
                , max(bc_video_id)
                , reference_id
                , max(user_id)
                , max(user_username)
                , max(team_id)
                , max(team_name)
                , max(team_title)
                , max(league_id)
                , max(league_name)
                , max(league_title)
                , max(DATE)
                , min(created_at)
                , max(updated_at)
                , max(category_id)
                , max(category_name)
                , max(category_title)
                , CASE 
                                WHEN max(video_duration) > 0 and type =1
                                                THEN CASE 
                                                                                WHEN sum(video_view_amount_second) / (max(video_duration) / 1000) < 1
                                                                                                THEN 1
                                                                                ELSE sum(video_view_amount_second) / (max(video_duration) / 1000)
                                                                                END
                                ELSE NULL
                                END
                , CASE 
                                WHEN type = 1
                                                THEN 'CH'
                                WHEN type = 3
                                                THEN 'SV'
                                ELSE 'Other'
                                END
FROM PUBLIC.engagmentdashboarddata
GROUP BY video_id
                , type
                , reference_id
 ;

";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>