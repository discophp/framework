<?php


class BaseEmail {
    private $settings;

    public function __construct(){
        if(is_file('../.mail.settings.json')){
            $this->settings=json_decode(file_get_contents('../.mail.settings.json'));
        }//if
    }//construct

    public function useSSL(){
        $this->settings->{'DEFAULT'}="SSL";
    }//useSSL

    public function useTLS(){
        $this->settings->{'DEFAULT'}="TLS";
    }//useSSL

    public function useSMTP(){
        $this->settings->{'DEFAULT'}="SMTP";
    }//useSMTP

    public function send($account,$toEmail,$subject,$body,$attach=null){

        // Approach 1: Change the global setting (suggested)
        Swift_Preferences::getInstance()->setCharset('iso-8859-2');

        //Create the message
        $message = Swift_Message::newInstance();
         
        // Give the message a subject
        $message->setSubject($subject);
         
        // Set the From address with an associative array
        $message->setFrom($account);
         
        // Set the To addresses with an array
        if(!is_array($toEmail))
            $message->setTo(array($toEmail));
        else 
            $message->setTo($toEmail);

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
            $type = $this->settings->{'DEFAULT'};
            $server = $this->settings->{$type}->{'HOST'};
            $port = $this->settings->{$type}->{'PORT'};

            $type = strtolower($type);

            $transport = Swift_SmtpTransport::newInstance($server,$port,$type);
            $transport->setUsername($this->settings->{$account}->{'USER'});
            $transport->setPassword($this->settings->{$account}->{'PASSWORD'});
            
            // Create the Mailer using the Transport
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


}//BaseEmail



?>
