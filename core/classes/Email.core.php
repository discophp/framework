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
     *      holds json from ../.mail.config.php
    */
    private $settings;


    private $delay=null;


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
        if(is_file(\Disco::$path.'/.mail.config.php')){
            $this->settings=require(\Disco::$path.'/.mail.config.php');
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
        $this->settings['DEFAULT']="SSL";
    }//useSSL



    /**
     *      Use TLS protocol to send email 
     *
     *
     *      @return void
    */
    public function useTLS(){
        $this->settings['DEFAULT']="TLS";
    }//useSSL



    /**
     *      Use SMTP protocol to send email
     *
     *
     *      @return void
    */
    public function useSMTP(){
        $this->settings['DEFAULT']="SMTP";
    }//useSMTP



    /**
     *      Push a Email job onto the Queue with a specified delay (in seconds)
     *
     *
     *      @param numeric $s the seconds to send the email after
     *      @return object $this
    */
    public function delay($s){
        $this->delay = $s;
        return $this;
    }//delay


    /**
    *       Send an email through a specified account
    *
    *
    *       @param string   $key
    *       @param mixed    $toEmail
    *       @param string   $subject
    *       @param string   $body
    *       @param array    $attach
    *
    *       @return boolean
    */
    public function send($key,$toEmail,$subject,$body,$attach=null){

        if($this->delay!=null){
            $d = $this->delay;
            $this->delay = null;
            $body = htmlentities($body);
            \Queue::push('Email@send',$d,Array($key,$toEmail,$subject,$body,$attch));
            return true;
        }//if

        if(!isset($this->settings[$key])){
            $me = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
            $trace = Array();
            $e = debug_backtrace();
            foreach($e as $err){
                if(isset($err['file']) && isset($err['function']) && $err['file']==$me && $err['function']=='send'){
                    $trace['args']=$err['args'];
                    $trace['line']=$err['line'];
                    $trace['file']=$err['file'];
                }//if
            }//foreach
            $msg = "Email::Error account does not exist  - {$trace['args'][0]} @ line {$trace['line']} in File: {$trace['file']} ";

            TRIGGER_ERROR($msg,E_USER_ERROR);

        }//if

        \Swift_Preferences::getInstance()->setCharset('iso-8859-2');

        //Create the message
        $message = \Swift_Message::newInstance();
         
        $message->setSubject($subject);
        $message->setFrom($this->settings[$key]['EMAIL']);

        if($_SERVER['APP_MODE']=='DEV'){
            $toEmail = $this->settings['DEV_MODE_SEND_TO'];
        }//if

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
            $type = $this->settings['DEFAULT'];
            $server = $this->settings[$type]['HOST'];
            $port = $this->settings[$type]['PORT'];

            $type = strtolower($type);
            if($type=='smtp')
                $type='';

            $transport = \Swift_SmtpTransport::newInstance($server,$port,$type);
            $transport->setUsername($this->settings[$key]['EMAIL']);
            $transport->setPassword($this->settings[$key]['PASSWORD']);
            
            // Create the Mailer using the Transport
            $mailer = \Swift_Mailer::newInstance($transport);
            
            // Send the message
            $result = $mailer->send($message);

            $mailer->getTransport()->stop();

            return $result;
        }//try
        catch(\Swift_TransportException $e){
            TRIGGER_ERROR('Email::Error send failed '.$e,E_USER_WARNING);
            return false;
        }//catch
        catch(\Exception $e){
            TRIGGER_ERROR('Email::Error send failed '.$e,E_USER_WARNING);
            return false;
        }//catch

    }//sendEmail


}//BaseEmail



?>
