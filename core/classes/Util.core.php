<?php

namespace Disco\classes;

class Util {

    public $emailRegExPattern = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/";

    public function decodeURL($inc){
        $inc = urldecode($inc);
        $inc = str_replace('-',' ',$inc);
        return $inc;
    }//decodeURL

    public function encodeURL($inc){
        $inc = str_replace(' ','-',$inc);
        $inc = urlencode($inc);
        return $inc;
    }//encodeURL

    public function cleanInput($inc){
        return htmlentities($inc);
    }//cleanInput

    public function buildTime($cTime) {
        if($cTime==0 || $cTime=='')
            return;
        $timeSince=abs(round(time()-$cTime));$now;
        if(!($timeSince>60)){ $now=$timeSince; $now.=($now==1)?' second ago':' seconds ago'; return $now; }//endif
        elseif(!($timeSince>3600)){ $now=round(($timeSince/60)); $now.=($now==1)?' minute ago':' minutes ago'; return $now; }//end elseif
        elseif(!($timeSince>86400)){ $now=round((($timeSince/60)/60)); $now.=($now==1)?' hour ago':' hours ago'; return $now; }//end elseif
        elseif(!($timeSince>2592000)){ $now=round((($timeSince/60)/60)/24); $now.=($now==1)?' day ago':' days ago'; return $now; }//end elseif
        elseif(!($timeSince>31104000)){ $now=round(((($timeSince/60)/60)/24)/30); $now.=($now==1)?' month ago':' months ago'; return $now; }//end elseif
        else{ $now=round((((($timeSince/60)/60)/24)/30)/12); $now.=($now==1)?' year ago':'years ago'; return $now; }//end
    }//end buildTime()


    public function death(){
        header('Location: /404');
    }//death

}//Utilities


?>
