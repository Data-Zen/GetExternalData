<?php




echo "\n\n*******Running FinalUserProcessing.php*************";
include './BrightCove/credentials/BrightCoveCredentials.php';
$debug=1;
/*first log in*/
$connect = pg_connect($BrightCoveModifyCredentials);
$sql="



DROP TABLE IF EXISTS PUBLIC.users_rollup;
/*
                CREATE TABLE PUBLIC.users_rollup (
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
                            
                                , b_followers_count BIGINT ENCODE lzo
                                , b_unfollowers_count BIGINT ENCODE lzo
                                , b_channel_name VARCHAR(10000) ENCODE lzo
                                , b_last_broadcasted_date VARCHAR(10000) ENCODE lzo
                                , b_channel_frozen_date VARCHAR(10000) ENCODE lzo
                                , b_analytics_ignore SMALLINT
                                , b_rank INT ENCODE lzo
                                , b_azubuteam VARCHAR(10000) ENCODE lzo
                                ) SORTKEY (b_username);


*/

SELECT id_user b_id_user
       , username b_username 
       , email b_email
       , user_status b_user_status
       , channel_status b_channel_status
       , role b_role
       , package b_package
       , package_abbrev b_package_abbrev
       , lower(team) b_team
       , league_name b_organization
       , user_date_created b_user_date_created
       , channel_date_created b_channel_date_created
       , followers_count b_followers_count
       , unfollowers_count b_unfollowers_count
       , channel_name b_channel_name
       , last_broadcasted_date b_last_broadcasted_date
       , channel_frozen_date b_channel_frozen_date
       , analytics_ignore b_analytics_ignore
        ,9999 b_rank
        ,case when nvl(team,'') = '' then 'NoTeam' else team end b_azubuteam
        into users_rollup
 FROM dev.public.users 
 ;
GRANT ALL
                ON TABLE PUBLIC.users_rollup
                TO
GROUP admin_group;

GRANT SELECT
                ON TABLE PUBLIC.users_rollup
                TO
GROUP readonly;
/*


INSERT INTO dev.PUBLIC.users_rollup
select max(id_user) id_user
    ,a.username
    ,max(email) email
    ,max(user_status) user_status
    ,max(channel_status) channel_status
    ,max(ROLE) ROLE
    ,max(package) package
    ,max(package_abbrev) package_abbrev
    ,max(lower(team)) team
    ,max(league_name) organization
    ,max(user_date_created) user_date_created
    ,max(channel_date_created) channel_date_created
    ,max(followers_count) followers_count
    ,max(unfollowers_count) unfollowers_count
    ,max(channel_name) channel_name
    ,max(last_broadcasted_date) last_broadcasted_date
    ,max(channel_frozen_date) channel_frozen_date
    ,max(analytics_ignore) analytics_ignore
    ,NULL
    ,max(lower(team)) azubuteam
FROM users a
GROUP BY a.username;
*/
/*
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
                , max(league_name) organization
                , max(user_date_created) user_date_created
                , max(channel_date_created) channel_date_created
                , max(followers_count) followers_count
                , max(unfollowers_count) unfollowers_count
            
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
FROM users a
LEFT JOIN broadcaster_details_azubuteams b
                ON a.username = b.username --where not exists (Select 1 from users_rollup br where br.b_username=b.username)
GROUP BY a.username;
*/


ALTER TABLE users_rollup ADD b_team_rank INT encode lzo;


UPDATE users_rollup
SET b_rank = NULL;



UPDATE users_rollup
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
WHERE users_rollup.b_username = rr.bc_azbroadcaster;


UPDATE users_rollup
SET b_rank = 9999
WHERE b_rank IS NULL;


UPDATE users_rollup
SET b_team_rank = br.rank
FROM (
                SELECT b_team
                                , rank() OVER (
                                                ORDER BY min(b_rank) ASC
                                                ) AS rank
                FROM users_rollup
                GROUP BY b_team
                ) br
WHERE users_rollup.b_team = br.b_team;




UPDATE users_rollup
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
   INNER JOIN PUBLIC.users_rollup b ON bc_azbroadcaster = b_username
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






 
";
if ($debug==1)
{
echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
}
$rec = pg_query($connect,$sql);
$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";









?>