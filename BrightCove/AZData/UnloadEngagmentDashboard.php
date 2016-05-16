<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want
include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);

/*

select \'id\',\'video_id\',\'video_title\',\'video_duration\',\'video_created_at\',\'video_view\',\'video_view_amount_second\',\'video_peak_ccu\',\'video_average_ccu\'
,\'old_video_average_ccu\',\'type\',\'bc_video_id\',\'reference_id\',\'user_id\',\'user_username\',\'team_id\',\'team_name\',\'league_id\',\'league_name\',
\'league_title\',\'date\',\'created_at\',\'updated_at\',\'category_id\',\'category_name\',\'category_title\'
union 
*/

$sql = "

unload ('

with NewEngagmentDashboard as (
SELECT id
                , bc_azvideoid AS video_id
                , bc_video_name AS video_title
                , zc_duration_in_ms AS video_duration
                , zc_created_at AS video_created_at
                , bc_video_view AS video_view
                , bc_video_seconds_viewed AS video_view_amount_second
                , video_peak_ccu
                , CASE 
                                WHEN video_duration > 0
                                                AND type = 1
                                                THEN CASE 
                                                                                WHEN video_view_amount_second / (video_duration / 1000) < 1
                                                                                                THEN 1
                                                                                ELSE video_view_amount_second / (video_duration / 1000)
                                                                                END
                                ELSE NULL
                                END AS video_average_ccu
                , video_average_ccu AS old_video_average_ccu
                , CASE 
                                WHEN bc_azvideotype = \'CH\'
                                                THEN 1
                                WHEN bc_azvideotype = \'SV\'
                                                THEN 3
                                END AS type
                , bc_video AS bc_video_id
                , bc_video_reference_id AS reference_id
                , b_id_user AS user_id
                , user_username
                , team_id
                , team_name
                , league_id
                , league_name
                , league_title --Directly from engagmentdashboarddata_rollup so that names all match up
                , bc_dt AS DATE
                , zc_created_at AS created_at
                , zc_updated_at AS updated_at
                , category_id
                , category_name
                , category_title --Directly from engagmentdashboarddata_rollup because we havent parsed videotags yet
FROM bc_videos_rollup
JOIN zencoder_rollup
                ON bc_azvideoid = zc_azvideoid
JOIN users_rollup
                ON b_username = bc_azbroadcaster
JOIN engagmentdashboarddata_rollup
                ON reference_id = bc_video_reference_id
--WHERE zc_duration_in_minutes >= 10
)
SELECT id
       , video_id
       , video_title
       , video_duration
       , video_created_at
       , video_view
       , video_view_amount_second
       , case when nvl(video_peak_ccu,0) < video_average_ccu then (video_average_ccu*1.25)::int else video_peak_ccu end video_peak_ccu
       , case when created_at >= \'5/10/2016\' then video_average_ccu else old_video_average_ccu end video_average_ccu
       , old_video_average_ccu
       , type
       , bc_video_id
       , reference_id
       , user_id
       , user_username
       , team_id
       , team_name
       , league_id
       , league_name
       , league_title
       , date
       , created_at
       , updated_at
       , category_id
       , category_name
       , category_title
 FROM NewEngagmentDashboard












')

to 's3://$S3bucketNameExtractFolder/new_engagment_dashboard'
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
delimiter ',' addquotes parallel off ALLOWOVERWRITE;


";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>