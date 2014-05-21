<?php

namespace Disco\classes;

/**
 *      This file holds the BaseEmail class
 */



/**
 *
 *      BaseEmail class.
 *      Handle sending emails through different setup accounts.
 *      This class relies on the settings set in .mail.settings.json
 *
*/
class Email {

    /**
     *      holds json from ../.mail.settings.json
    */
    private $settings;


    /**
     *      Send emails as plain text only?
    */
    public $plainTextOnly=false;
    
    /**
     *      get our email settings
     *
     *
     *      @return void
     */
    public function __construct(){
        if(is_file(\Disco::$path.'/.mail.settings.json')){
            $this->settings=json_decode(file_get_contents(\Disco::$path.'/.mail.settings.json'));
        }//if
    }//construct


    public function plainText($bool=true){
        $this->plainTextOnly=$bool;
    }//plainText


    /**
     *      Use SSL protocol to send email
     *
     *
     *      @return void
    */
    public function useSSL(){
        $this->settings->{'DEFAULT'}="SSL";
    }//useSSL



    /**
     *      Use TLS protocol to send email 
     *
     *
     *      @return void
    */
    public function useTLS(){
        $this->settings->{'DEFAULT'}="TLS";
    }//useSSL



    /**
     *      Use SMTP protocol to send email
     *
     *
     *      @return void
    */
    public function useSMTP(){
        $this->settings->{'DEFAULT'}="SMTP";
    }//useSMTP



    /**
    *       Send an email through a specified account
    *
    *
    *       @param string   $account
    *       @param mixed    $toEmail
    *       @param string   $subject
    *       @param string   $body
    *       @param array    $attach
    *
    *       @return boolean
    */
    public function send($account,$toEmail,$subject,$body,$attach=null){

        // Approach 1: Change the global setting (suggested)
        \Swift_Preferences::getInstance()->setCharset('iso-8859-2');

        //Create the message
        $message = \Swift_Message::newInstance();
         
        // Give the message a subject
        $message->setSubject($subject);
         
        // Set the From address with an associative array
        $message->setFrom($account);
         
        // Set the To addresses with an array
        if(!is_array($toEmail))
            $message->setTo(array($toEmail));
        else 
            $message->setTo($toEmail);

        if($this->plainTextOnly){
            $message->setBody($body,'text/plain');
        }//if
        else {
            //add the plain text verision to the email
            $message->addPart(strip_tags($body),'text/plain');

            // Give it a body
            $message->setBody($body,'text/html');
        }//el

        //attach attachments to message if any
        if($attach!=null){
            for($i=0;$i<count($attach);$i++){
                $message->attach(\Swift_Attachment::fromPath($attach[$i]));
            }//for
        }//if
         
        try {
            //Create the Transport
            $type = $this->settings->{'DEFAULT'};
            $server = $this->settings->{$type}->{'HOST'};
            $port = $this->settings->{$type}->{'PORT'};

            $type = strtolower($type);
            if($type=='smtp')
                $type='';

            $transport = \Swift_SmtpTransport::newInstance($server,$port,$type);
            $transport->setUsername($this->settings->{$account}->{'NAME'});
            $transport->setPassword($this->settings->{$account}->{'PASSWORD'});
            
            // Create the Mailer using the Transport
            $mailer = \Swift_Mailer::newInstance($transport);
            
            // Send the message
            $result = $mailer->send($message);

            $mailer->getTransport()->stop();

            return $result;
        }//try
        catch(\Swift_TransportException $e){
            echo $e;
        }//catch
        catch(\Exception $e){
            echo $e;
        }//catch

    }//sendEmail


}//BaseEmail



?>
