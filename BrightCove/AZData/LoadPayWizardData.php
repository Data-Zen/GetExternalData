<?php


date_default_timezone_set('UTC');//or change to whatever timezone you want

include './BrightCove/credentials/BrightCoveCredentials.php';
$connect = pg_connect($BrightCoveModifyCredentials);



$sql = "
/*
drop table if exists SubscriptionRevenue_stg;

create table SubscriptionRevenue_stg
(
csn BIGINT ENCODE lzo,
paymenttype VARCHAR(10000) ENCODE lzo,
accountname VARCHAR(10000) ENCODE lzo,
paymentaccount VARCHAR(10000) ENCODE lzo,
customeraccountid bigint ENCODE lzo,
dt VARCHAR(10000) ENCODE lzo,
paymentstatus VARCHAR(10000) ENCODE lzo,
paymentid bigint ENCODE lzo,
refundpaymentid bigint ENCODE lzo,
authcode VARCHAR(10000) ENCODE lzo,
paymentamount float encode BYTEDICT,
subscontractid bigint encode lzo
);

*/
truncate table SubscriptionRevenue_stg;

copy SubscriptionRevenue_stg
from 's3://$S3bucketName/paywizarddata.csv' with 
credentials 'aws_access_key_id=$S3accessKey;aws_secret_access_key=$S3secretKey' 
csv
IGNOREHEADER 3
$S3Region;


GRANT SELECT ON TABLE public.SubscriptionRevenue TO GROUP readonly;

delete from  SubscriptionRevenue
where paymentid in (select paymentid from subscriptionrevenue_stg where paymentid is not null);
insert into SubscriptionRevenue
SELECT csn
       , paymenttype
       , accountname
       , paymentaccount
       , customeraccountid
       ,to_date(dt, 'DD/MM/YY hh:mi:ss')::DATETIME
       , paymentstatus
       , paymentid
       , refundpaymentid
       , authcode
       , paymentamount
       , subscontractid
 FROM dev.public.subscriptionrevenue_stg
 where paymentid not in (select paymentid from subscriptionrevenue)
 ;


";



echo "\n*******StartQuery\n".$sql."\n*******EndQuery\n";
$rec = pg_query($connect,$sql);

$rowsaffected=pg_affected_rows($rec);
echo "Rows affected $rowsaffected \n\n";



?>