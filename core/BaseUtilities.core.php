<?php

class BaseUtilities {
    public $disco;
    public $emailRegExPattern = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/";

    public $mailServer = 'mail.server.com';
    public $mailPort = '25';


    public function __construct($disco=null){
    //public function __construct(){
        $this->disco=$disco;
    }//construct 



    public function decodeURL($inc){

        $inc = $this->disco->db->clean(urldecode($inc));
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
        $curTime=time();$timeSince=round($curTime-$cTime);$now;
        if(!($timeSince>60)){ $now=$timeSince; $now.=($now==1)?' second ago':' seconds ago'; return $now; }//endif
        elseif(!($timeSince>3600)){ $now=round(($timeSince/60)); $now.=($now==1)?' minute ago':' minutes ago'; return $now; }//end elseif
        elseif(!($timeSince>86400)){ $now=round((($timeSince/60)/60)); $now.=($now==1)?' hour ago':' hours ago'; return $now; }//end elseif
        elseif(!($timeSince>2592000)){ $now=round((($timeSince/60)/60)/24); $now.=($now==1)?' day ago':' days ago'; return $now; }//end elseif
        elseif(!($timeSince>31104000)){ $now=round(((($timeSince/60)/60)/24)/30); $now.=($now==1)?' month ago':' months ago'; return $now; }//end elseif
        else{ $now=round((((($timeSince/60)/60)/24)/30)/12); $now.=($now==1)?' year ago':'years ago'; return $now; }//end
    }//end buildTime()


    public function death($log=''){
        header('Location: '.$this->disco->tc->path.'404');
    }//death



    public function sendEmail($account,$toEmail,$subject,$body,$attach=null){
        require_once('../support_libraries/swiftmailer/swift_required.php');

        $emails = Array('user'=>Array(),'someotheruser'=>Array());
        $emails['user']['userName']='hello';
        $emails['user']['password']='world';
        $emails['user']['email']='hello@world.com';

        // Approach 1: Change the global setting (suggested)
        Swift_Preferences::getInstance()->setCharset('iso-8859-2');

        //Create the message
        $message = Swift_Message::newInstance();
         
        // Give the message a subject
        $message->setSubject($subject);
         
        // Set the From address with an associative array
        $message->setFrom($emails[$account]['email']);
         
        // Set the To addresses with an array
        $message->setTo(array($toEmail));

        //add the plain text verision to the email
        $message->addPart(strip_tags($body),'text/plain');

        // Give it a body
        $message->setBody($body,'text/html');

        //attach attachments to message if any
        if($attach!=null){
            for($i=0;$i<count($attach);$i++){
                $message->attach(Swift_Attachment::fromPath($attach[$i]));
            }//for
        }//if
         
        try {
            //Create the Transport
            //$transport = Swift_SmtpTransport::newInstance('smtp.sendgrid.net', 465,'ssl');
            $transport = Swift_SmtpTransport::newInstance($this->mailServer, $this->mailPort,'');
            $transport->setUsername($emails[$account]['userName']);
            $transport->setPassword($emails[$account]['password']);      
            //You could alternatively use a different transport such as Sendmail or Mail:
            
            // Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($transport);
            
            // Send the message
            $result = $mailer->send($message);

            $mailer->getTransport()->stop();

            return $result;
        }//try
        catch(Swift_TransportException $e){
            echo $e;
        }//catch
        catch(Exception $e){
            echo $e;
        }//catch

    }//sendEmail


}//Utilities


?>
