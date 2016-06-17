<?php

			$outputurl="http://azubu-video-prod.s3.amazonaws.com/video289520CHRevo1t.mp4";
            $AZURL=substr($outputurl, strpos($outputurl,"/video")+strlen("/video"));
            echo "$AZURL :AZURL\n";
            if (strpos($AZURL,"_") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"_"));}
            if (strpos($AZURL,"/") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"/"));}
            if (strpos($AZURL,".") >0  ) {$AZURL=substr($AZURL, 0,strpos($AZURL,"."));}
            $AZVideoID=substr($AZURL, 0,strpos($AZURL,"CH"));
            echo "$AZVideoID :AZVideoID\n";
            $AZVideoType="CH";
            echo "$AZVideoType :AZVideoType\n";
            $AZBroadcaster=substr($AZURL, strpos($AZURL,"CH")+2);
            echo "$AZBroadcaster :AZBroadcaster\n";




?>            