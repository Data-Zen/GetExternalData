<?php




echo "\n\n*******Running FinalProcessing.php*************";
include './BrightCove/credentials/BrightCoveCredentials.php';
$debug=1;
/*first log in*/
$connect = pg_connect($BrightCoveModifyCredentials);
$sql="

UPDATE zencoder
SET azbroadcaster=trim(lower(replace(replace(azbroadcaster,'-',''),'_','')));

UPDATE bc_videos
SET azbroadcaster=trim(lower(replace(replace(azbroadcaster,'-',''),'_','')));

drop table if exists public.bc_videos_rollup_backup;
CREATE TABLE public.bc_videos_rollup_backup
(
  bc_account BIGINT ENCODE lzo,
  bc_account_name VARCHAR(10000) ENCODE lzo,
  bc_bytes_delivered DOUBLE PRECISION ENCODE bytedict,
  bc_engagement_score DOUBLE PRECISION ENCODE bytedict,
  bc_play_rate DOUBLE PRECISION ENCODE bytedict,
  bc_video BIGINT ENCODE lzo,
  bc_video_duration INTEGER ENCODE delta32k,
  bc_video_engagement_1 DOUBLE PRECISION ENCODE bytedict,
  bc_video_engagement_100 DOUBLE PRECISION ENCODE bytedict,
  bc_video_engagement_25 DOUBLE PRECISION ENCODE bytedict,
  bc_video_engagement_50 DOUBLE PRECISION ENCODE bytedict,
  bc_video_engagement_75 DOUBLE PRECISION ENCODE bytedict,
  bc_video_impression BIGINT ENCODE lzo,
  bc_video_name VARCHAR(10000) ENCODE lzo,
  bc_video_percent_viewed DOUBLE PRECISION ENCODE bytedict,
  bc_video_seconds_viewed BIGINT ENCODE lzo,
  bc_video_view INTEGER ENCODE lzo,
  bc_video_reference_id VARCHAR(10000) ENCODE lzo DISTKEY,
  bc_videoname VARCHAR(10000) ENCODE lzo,
  bc_videotags VARCHAR(65535) ENCODE lzo,
  bc_dt DATE ENCODE lzo,
  bc_azvideoid BIGINT ENCODE lzo,
  bc_azvideotype VARCHAR(100) ENCODE lzo,
  bc_azbroadcaster VARCHAR(10000) ENCODE lzo
)
SORTKEY
(
  bc_video_reference_id
);



GRANT ALL
                ON TABLE PUBLIC.bc_videos_rollup_backup
                TO
GROUP admin_group;

GRANT SELECT
                ON TABLE PUBLIC.bc_videos_rollup_backup
                TO
GROUP readonly;

insert into  bc_videos_rollup_backup
select *  from bc_videos_rollup;
---Start Video Rollup
DELETE
FROM bc_videos_rollup
WHERE bc_video_reference_id IN (
    SELECT DISTINCT bc_video_reference_id
    FROM bc_videos_rollup
    WHERE bc_dt >= (
        SELECT max(bc_dt) - 90
        FROM bc_videos_rollup
        )
      AND bc_video_reference_id IS NOT NULL
    );


INSERT into bc_videos_rollup
select * from 
(
SELECT max(nvl(account, 0))
  ,max(nvl(account_name, '')) bc_account_name
  ,sum(nvl(bytes_delivered, 0)) bc_bytes_delivered
  ,max(nvl(engagement_score, 0)) bc_engagement_score
  ,max(nvl(play_rate, 0)) bc_play_rate
  ,max(nvl(video, 0)) bc_video
  ,max(nvl(video_duration, 0)) bc_video_duration
  ,max(nvl(video_engagement_1, 0)) bc_video_engagement_1
  ,max(nvl(video_engagement_100, 0)) bc_video_engagement_100
  ,max(nvl(video_engagement_25, 0)) bc_video_engagement_25
  ,max(nvl(video_engagement_50, 0)) bc_video_engagement_50
  ,max(nvl(video_engagement_75, 0)) bc_video_engagement_75
  ,sum(nvl(video_impression, 0)) bc_video_impression
  ,max(nvl(video_name, '')) bc_video_name
  ,max(nvl(video_percent_viewed, 0)) bc_video_percent_viewed
  ,sum(nvl(video_seconds_viewed, 0)) bc_video_seconds_viewed
  ,sum(nvl(video_view, 0)) bc_video_view
  ,video_reference_id bc_video_reference_id
  ,max(nvl(videoname, '')) bc_videoname
  ,max(nvl(videotags, '')) bc_videotags
  ,min(nvl(dt, '2001-01-01')) bc_dt
  ,--should be min, but due to so many BC issues this was changed
  max(nvl(azvideoid, 0)) bc_azvideoid
  ,max(nvl(azvideotype, '')) bc_azvideotype
  ,max(nvl(azbroadcaster, '')) bc_azbroadcaster
FROM bc_videos b
WHERE NOT EXISTS (
    SELECT 1
    FROM bc_videos_rollup br
    WHERE br.bc_video_reference_id = b.video_reference_id
    )
   and video_reference_id IS NOT NULL
  AND video_seconds_viewed < 4000000000 --Get rid of outliers
  group by video_reference_id
)
where bc_azvideotype ='CH'
;
      

INSERT INTO bc_videos_rollup
SELECT *
FROM (
SELECT *
FROM bc_videos b
where 
   video_reference_id IS NOT NULL
  AND video_seconds_viewed < 4000000000 --Get rid of outliers
  and azvideotype <>'CH'
)   b
WHERE NOT EXISTS (
    SELECT 1
    FROM bc_videos_rollup br
    WHERE br.bc_video_reference_id = b.video_reference_id and b.dt=bc_dt
    );
  


DELETE
FROM bc_videos_rollup
WHERE bc_video_reference_id IS NULL
  AND bc_dt >= (
    SELECT max(bc_dt) - 90
    FROM bc_videos_rollup
    );

INSERT INTO bc_videos_rollup
SELECT max(nvl(account, 0))
  ,max(nvl(account_name, '')) bc_account_name
  ,sum(nvl(bytes_delivered, 0)) bc_bytes_delivered
  ,max(nvl(engagement_score, 0)) bc_engagement_score
  ,max(nvl(play_rate, 0)) bc_play_rate
  ,max(nvl(video, 0)) bc_video
  ,max(nvl(video_duration, 0)) bc_video_duration
  ,max(nvl(video_engagement_1, 0)) bc_video_engagement_1
  ,max(nvl(video_engagement_100, 0)) bc_video_engagement_100
  ,max(nvl(video_engagement_25, 0)) bc_video_engagement_25
  ,max(nvl(video_engagement_50, 0)) bc_video_engagement_50
  ,max(nvl(video_engagement_75, 0)) bc_video_engagement_75
  ,sum(nvl(video_impression, 0)) bc_video_impression
  ,NULL bc_video_name
  ,max(nvl(video_percent_viewed, 0)) bc_video_percent_viewed
  ,sum(nvl(video_seconds_viewed, 0)) bc_video_seconds_viewed
  ,sum(nvl(video_view, 0)) bc_video_view
  ,video_reference_id bc_video_reference_id
  ,max(nvl(videoname, '')) bc_videoname
  ,max(nvl(videotags, '')) bc_videotags
  ,dt bc_dt
  ,max(nvl(azvideoid, 0)) bc_azvideoid
  ,max(nvl(azvideotype, '')) bc_azvideotype
  ,max(nvl(azbroadcaster, '')) bc_azbroadcaster
FROM bc_videos b
WHERE NOT EXISTS (
    SELECT 1
    FROM bc_videos_rollup br
    WHERE br.bc_dt = b.dt
      AND video_reference_id IS NULL
    )
  AND video_reference_id IS NULL
  AND video_seconds_viewed < 4000000000 --Get rid of outliers
GROUP BY video_reference_id
  ,dt;
---End Video Rollup

DELETE
FROM zencoder_rollup
WHERE zc_video_reference_id IN
    (SELECT DISTINCT zc_video_reference_id
     FROM zencoder_rollup
     WHERE zc_created_at >=
         (SELECT max(zc_created_at)-30
          FROM zencoder_rollup)
       AND zc_video_reference_id IS NOT NULL);

 --delete from zencoder_rollup where zc_created_at >= (select max(zc_created_at)-30 from  zencoder_rollup );
INSERT INTO dev.PUBLIC.zencoder_rollup (
                zc_audio_bitrate_in_kbps
                , zc_audio_codec
                , zc_audio_sample_rate
                , zc_audio_tracks
                , zc_channels
                , zc_created_at
                , zc_duration_in_ms
                , zc_error_class
                , zc_error_message
                , zc_file_size_bytes
                , zc_finished_at
                , zc_format
                , zc_frame_rate
                , zc_height
                , zc_id
                , zc_md5_checksum
                , zc_privacy
                , zc_state
                , zc_test
                , zc_updated_at
                , zc_video_bitrate_in_kbps
                , zc_video_codec
                , zc_width
                , zc_total_bitrate_in_kbps
                , zc_outputurl
                , zc_azvideoid
                , zc_azvideotype
                , zc_azbroadcaster
                , zc_video_reference_id
                , zc_inputurl
                , zc_sourcelatitude
                , zc_sourcelongitude
                , zc_sourcelocation
                , zc_destinationlatitude
                , zc_destinationlongitude
                , zc_destinationlocation
                , zc_duration_in_minutes
                , zc_duration_in_hours
                , zc_sourcelocation_country_cd
                , zc_sourcelocation_country
                )
SELECT max(nvl(audio_bitrate_in_kbps, 0))
                , max(nvl(audio_codec, ''))
                , max(nvl(audio_sample_rate, 0))
                , max(nvl(audio_tracks, ''))
                , max(nvl(channels, 0))
                , min(nvl(created_at, '2001-01-01'))
                , sum(nvl(duration_in_ms, 0))
                , max(nvl(error_class, ''))
                , max(nvl(error_message, ''))
                , max(nvl(file_size_bytes, 0))
                , max(nvl(finished_at, '2001-01-01'))
                , max(nvl(format, ''))
                , max(nvl(frame_rate, 0))
                , max(nvl(height, 0))
                , max(nvl(id, 0))
                , max(nvl(md5_checksum, ''))
                , max(nvl(privacy, ''))
                , max(nvl(STATE, ''))
                , max(nvl(test, ''))
                , max(nvl(updated_at, '2001-01-01'))
                , max(nvl(video_bitrate_in_kbps, 0))
                , max(nvl(video_codec, ''))
                , max(nvl(width, 0))
                , max(nvl(total_bitrate_in_kbps, 0))
                , max(nvl(outputurl, ''))
                , max(nvl(azvideoid, 0))
                , max(nvl(azvideotype, ''))
                , max(nvl(azbroadcaster, ''))
                , video_reference_id
                , max(nvl(inputurl, ''))
                , max(nvl(sourcelatitude, 0))
                , max(nvl(sourcelongitude, 0))
                , max(nvl(sourcelocation, ''))
                , max(nvl(destinationlatitude, 0))
                , max(nvl(destinationlongitude, 0))
                , max(nvl(destinationlocation, ''))
                , sum(nvl(duration_in_minutes, 0))
                , sum(nvl(duration_in_hours, 0))
                , max(nvl(sourcelocation_country_cd, ''))
                , max(nvl(sourcelocation_country, ''))
FROM zencoder zb
WHERE NOT EXISTS (
                                SELECT 1
                                FROM zencoder_rollup zr
                                WHERE zb.video_reference_id = zr.zc_video_reference_id
                                )
                AND video_reference_id IS NOT NULL
                AND nvl(duration_in_hours, 0) <= 30 --Prevent Erronous Data
                AND finished_at IS NOT NULL
        and state  not in ('processing','pending')
GROUP BY video_reference_id;


/*

DROP TABLE IF EXISTS PUBLIC.broadcaster_details_rollup;
                CREATE TABLE PUBLIC.broadcaster_details_rollup (
                                b_id_user BIGINT ENCODE lzo
                                , b_username VARCHAR(10000) ENCODE lzo DISTKEY
                                , b_email VARCHAR(10000) ENCODE lzo
                                , b_user_status VARCHAR(10000) ENCODE lzo
                                , b_channel_status VARCHAR(10000) ENCODE lzo
                                , b_role VARCHAR(10000) ENCODE lzo
                                , b_package VARCHAR(10000) ENCODE lzo
                                , b_package_abbrev VARCHAR(10) ENCODE lzo
                                , b_team VARCHAR(10000) ENCODE lzo
                                , b_organization VARCHAR(10000) ENCODE lzo
                                , b_user_date_created VARCHAR(10000) ENCODE lzo
                                , b_channel_date_created VARCHAR(10000) ENCODE lzo
                                , b_channel_time_created VARCHAR(10000) ENCODE lzo
                                , b_channel_date_updated VARCHAR(10000) ENCODE lzo
                                , b_followers_count BIGINT ENCODE lzo
                                , b_unfollowers_count BIGINT ENCODE lzo
                                , b_month VARCHAR(10000) ENCODE lzo
                                , b_week VARCHAR(10000) ENCODE lzo
                                , b_channel_name VARCHAR(10000) ENCODE lzo
                                , b_last_broadcasted_date VARCHAR(10000) ENCODE lzo
                                , b_channel_frozen_date VARCHAR(10000) ENCODE lzo
                                , b_analytics_ignore SMALLINT
                                , b_rank INT ENCODE lzo
                                , b_azubuteam VARCHAR(10000) ENCODE lzo
                                ) SORTKEY (b_username);



GRANT ALL
                ON TABLE PUBLIC.broadcaster_details_rollup
                TO
GROUP admin_group;

GRANT SELECT
                ON TABLE PUBLIC.broadcaster_details_rollup
                TO
GROUP readonly;



INSERT INTO dev.PUBLIC.broadcaster_details_rollup
SELECT max(id_user) id_user
                , a.username
                , max(email) email
                , max(user_status) user_status
                , max(channel_status) channel_status
                , max(ROLE) ROLE
                , max(package) package
                , max(package_abbrev) package_abbrev
                , CASE 
                                WHEN max(nvl(team, '')) <> ''
                                                THEN max(lower(team))
                                WHEN max(b.azubuteam) IS NOT NULL
                                                THEN max(lower(b.azubuteam))
                                 when lower(max(channel_status)) = 'active' then a.username
                                END team
                , max(organization) organization
                , max(user_date_created) user_date_created
                , max(channel_date_created) channel_date_created
                , max(channel_time_created) channel_time_created
                , max(channel_date_updated) channel_date_updated
                , max(followers_count) followers_count
                , max(unfollowers_count) unfollowers_count
                , max(MONTH) AS MONTH
                , max(week) AS week
                , max(channel_name) channel_name
                , max(last_broadcasted_date) last_broadcasted_date
                , max(channel_frozen_date) channel_frozen_date
                , max(analytics_ignore) analytics_ignore
                , NULL
                , CASE 
                                WHEN max(lower(b.azubuteam)) IS NULL and lower(max(channel_status)) ='active'
                                                THEN a.username
                                ELSE max(lower(azubuteam))
                                END azubuteam
FROM broadcaster_details a
LEFT JOIN broadcaster_details_azubuteams b
                ON a.username = b.username --where not exists (Select 1 from broadcaster_details_rollup br where br.b_username=b.username)
GROUP BY a.username;



ALTER TABLE broadcaster_details_rollup ADD b_team_rank INT encode lzo;


UPDATE broadcaster_details_rollup
SET b_rank = NULL;



UPDATE broadcaster_details_rollup
SET b_rank = rr.rank
FROM (
                SELECT bc_azbroadcaster
                                , rank() OVER (
                                                ORDER BY sum(nvl(bc_video_seconds_viewed, 0)) DESC
                                                                , sum(nvl(bc_video_view, 0)) DESC
                                                ) AS rank
                FROM bc_videos_rollup
                WHERE bc_dt >= dateadd(d, - 7, (
                                                                SELECT max(bc_dt)
                                                                FROM bc_videos_rollup
                                                                )::DATE)
                                AND bc_video_reference_id IN (
                                                SELECT DISTINCT zc_video_reference_id
                                                FROM zencoder_rollup
                                                WHERE zc_finished_at >= dateadd(d, - 7, (
                                                                                                SELECT max(zc_finished_at)
                                                                                                FROM zencoder_rollup
                                                                                                )::DATE)
                                                )
                GROUP BY bc_azbroadcaster
                ) rr
WHERE broadcaster_details_rollup.b_username = rr.bc_azbroadcaster;


UPDATE broadcaster_details_rollup
SET b_rank = 9999
WHERE b_rank IS NULL;


UPDATE broadcaster_details_rollup
SET b_team_rank = br.rank
FROM (
                SELECT b_team
                                , rank() OVER (
                                                ORDER BY min(b_rank) ASC
                                                ) AS rank
                FROM broadcaster_details_rollup
                GROUP BY b_team
                ) br
WHERE broadcaster_details_rollup.b_team = br.b_team;




UPDATE broadcaster_details_rollup
SET b_team_rank = 9999
WHERE b_team_rank IS NULL;



DELETE
FROM broadcaster_Top_40
WHERE dt = getdate()::date;


INSERT INTO broadcaster_Top_40 WITH st AS
  ( SELECT getdate()::DATE AS stdt ) ,
                                    current_ranking AS
  ( SELECT b_azubuteam ,
           rank() OVER (
                        ORDER BY sum(nvl(bc_video_seconds_viewed, 0)) DESC , sum(nvl(bc_video_view, 0)) DESC ) AS rank ,
                  max(st.stdt) stdt
   FROM bc_videos_rollup a
   INNER JOIN PUBLIC.broadcaster_details_rollup b ON bc_azbroadcaster = b_username
   INNER JOIN st ON 1 = 1
   WHERE bc_dt BETWEEN dateadd(d, - 7, st.stdt) AND st.stdt
   GROUP BY b_azubuteam )
SELECT b_azubuteam ,
       rank ,
       stdt ,
       nvl(WeeksOnTop40 + 1, 1) WeeksOnTop40
FROM
  ( SELECT * ,
     ( SELECT WeeksOnTop40
      FROM broadcaster_Top_40 bt
      WHERE bt.dt =
          ( SELECT max(dt)
           FROM broadcaster_Top_40 )
        AND bt.azbroadcaster = current_ranking.b_azubuteam ) WeeksOnTop40
   FROM current_ranking )
WHERE rank <= 40
  AND datepart(dow, stdt) = 4 --Only do this on Thursday
ORDER BY 2;
*/




 
";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



$sql="
select case when (select count(1) from bc_videos_rollup) >= (select count(1) from bc_videos_rollup_backup) then 'All Is Good' else 'PROBLEM 'end ISTHEREAPROBLEM
";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);

while ($row = pg_fetch_row($rec)) 
    {
                  $ISTHEREAPROBLEM = $row[0];
                  if ($ISTHEREAPROBLEM=="PROBLEM")
                  {

                    $sql="
                    DROP TABLE IF EXISTS bc_videos_rollup_problemtable;
                    alter table bc_videos_rollup
                    rename to bc_videos_rollup_problemtable;
                    alter table bc_videos_rollup_backup
                    rename to bc_videos_rollup;

                    ";

                    if ($debug==1)
                    {
                    echo "\nPROBLEM OCCURED REVERTING bc_videos_rollup to backup\n*******StartQuery\n".$sql."\n*******EndQuery\n";
                    }
                    $rec = pg_query($connect,$sql);

                  }
   }







?>