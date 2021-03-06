<?php
namespace Disco\classes;
/**
 * This file holds the Email class.
 */



/**
 * Email class.
 * Handle sending emails through different setup accounts.
 * This class relies on the settings set in your application mail config `app/config/mail.config.php`.
*/
class Email {


    /**
     * The key for the default account.
     */
    const DEFAULT_ACCOUNT_KEY = 'DEFAULT_ACCOUNT';


    /**
     * The key for the default server.
     */
    const DEFAULT_SERVER_KEY = 'DEFAULT_SERVER';


    /**
     * The key for the dev mode email address.
     */
    const DEV_MODE_SEND_TO_KEY = 'DEV_MODE_SEND_TO';


    /**
     * @var array Holds config data from `app/config/email.php`.
    */
    private $settings = Array();


    /**
     * @var int The delay to apply to the next email sent.
    */
    private $delay=null;


    /**
     * @var boolean Send emails as plain text only?
    */
    public $plainTextOnly=false;
    


    /**
     * Load our Email setting from `app/config/email.php` over riding any values set in `app/config/dev.email.php` if the 
     * application is in `DEV_MODE`. 
     */
    public function __construct(){

        if(is_file(\App::path() . '/app/config/email.php')){
            $this->settings = require(\App::path() . '/app/config/email.php');
        }//if

        if(\App::devMode()){
            $devSettings = \App::path() . '/app/config/dev.email.php';
            if(is_file($devSettings)){
                $this->settings = array_merge($this->settings, require $devSettings);
            }//if
        }//if

        $charset = $this->getSetting('CHARSET');
        if(!$charset){
            $charset = 'iso-8859-2';
        }//if

        \Swift_Preferences::getInstance()->setCharset($charset);

    }//construct



    /**
     * Get a email configuration setting.
     *
     *
     * @param string $key The key of the config.
     *
     * @return mixed
    */
    public function getSetting($key){

        if(!array_key_exists($key, $this->settings)){
            return false;
        }//if

        return $this->settings[$key];

    }//getSetting



    /**
     * Set a email configuration setting.
     *
     *
     * @param string $key The key of the config.
     * @param mixed $value The value of the config
     *
     * @return void
    */
    public function setSetting($key, $value){
        $this->settings[$key] = $value;
    }//setSetting



    /**
     * Set the account to send emails through.
     *
     *
     * @param string $key The account key.
     *
     * @return void
    */
    public function setCurrentAccount($key){
        $this->setSetting(self::DEFAULT_ACCOUNT_KEY, $key);
    }//account



    /**
     * Get the account currently configuration to send emails through.
     *
     *
     * @return array The account configuration.
    */
    public function getCurrentAccount(){
        return $this->getSetting($this->getSetting(self::DEFAULT_ACCOUNT_KEY));
    }//getCurrentAccount



    /**
     * Set the server configuration used to send emails through.
     *
     *
     * @param string $key The key of the server.
     *
     * @return void
    */
    public function setCurrentServer($key){
        $this->setSetting(self::DEFAULT_SERVER_KEY, $key);
    }//getCurrentServer



    /**
     * Get the server configuration currently being used to send emails through.
     *
     *
     * @return array The server confiuration.
    */
    public function getCurrentServer(){
        return $this->getSetting($this->getSetting(self::DEFAULT_SERVER_KEY));
    }//getCurrentServer



    /**
     * Should we send the emails as plain text only?
     *
     *
     * @param boolean $bool Plain text only? True, False.
     *
     * @return self
    */
    public function plainText($bool=true){
        $this->plainTextOnly = $bool;
        return $this;
    }//plainText



    /**
     * Push a Email job onto the Queue with a specified delay (in seconds).
     *
     *
     * @param int $s The seconds to send the email after.
     *
     * @return self 
    */
    public function delay($s){
        $this->delay = $s;
        return $this;
    }//delay



    /**
    * Send an email through the current active account.
    *
    *
    * @param string|array   $toEmail    The email addresses to send this email to. 
    * @param string         $subject    The subject line of this email. 
    * @param string         $body       The body of this email.
    * @param null|array     $attach     The attachments to include with this email.
    *
    * @return boolean Success?
    */
    public function send($toEmail, $subject, $body, $attach=null){

        if($this->delay != null){
            $d = $this->delay;
            $this->delay = null;
            $body = htmlentities($body);
            \App::with('Queue')->push('Email@send', $d, Array($toEmail, $subject, $body, $attach));
            return true;
        }//if

        $message = $this->getMessage($toEmail, $subject, $body);

        //attach attachments to message if any
        if($attach !== null){
            $message = $this->addAtachments($message, $attach);
        }//if
         
        return $this->sendMessage($message);

    }//send



    /**
     * Add attachments by absolute path to a \Swift_Message.
     *
     *
     * @param \Swift_Message $message The message to add the attachments to.
     * @param string|array $attach The aboslute paths of the attachments.
     *
     * @return \Swift_Message The message with the attachments
    */
    public function addAtachments(\Swift_Message $message, $attach){

        if(is_string($attach)){
            $attach = Array($attach);
        }//if 

        foreach($attach as $filePath){
            $message->attach(\Swift_Attachment::fromPath($filePath));
        }//foreach

        return $message;

    }//addAtachments



    /**
     * Get a \Swift_Message thats preloaded with the to, subject, and body.
     *
     *
     * @param string|array   $toEmail    The email addresses to send this email to. 
     * @param string         $subject    The subject line of this email. 
     * @param string         $body       The body of this email.
     *
     * @return \Swift_Message The message.
    */
    public function getMessage($toEmail,$subject,$body){

        //Create the message
        $message = \Swift_Message::newInstance();

        $account = $this->getCurrentAccount();
         
        $message->setSubject($subject);

        if(isset($account['ALIAS']) && $account['ALIAS']){
            $message->setFrom(Array($account['EMAIL'] => $account['ALIAS']));
        }//if
        else {
            $message->setFrom($account['EMAIL']);
        }//el

        if(\App::devMode() && $this->getSetting(self::DEV_MODE_SEND_TO_KEY)){
            $toEmail = $this->getSetting(self::DEV_MODE_SEND_TO_KEY);
        }//if

        if(!is_array($toEmail)){
            $toEmail = array($toEmail);
        }//if

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

        return $message;

    }//getMessage



    /**
     * Send a swiftmailer message.
     *
     * 
     * @param \Swift_Message $message The message to send.
     *
     *
     * @return boolean
     *
     * @throws \Disco\exceptions\Email
     * @throws \Swift_TransportException
     * @throws \Exception
    */
    public function sendMessage(\Swift_Message &$message){

        try {

            $serverConfig = $this->getCurrentServer();

            if($serverConfig === null){
                throw new \Disco\exceptions\Email("Email exception: Server defined by key {$this->settings[self::DEFAULT_SERVER_KEY]} does not exist.");
            }//if

            $serverConfig['PROTOCOL'] = strtolower($serverConfig['PROTOCOL']);

            if($serverConfig['PROTOCOL'] == 'smtp') {
                $serverConfig['PROTOCOL'] = '';
            }//if

            $account = $this->getCurrentAccount();

            if($account === null){
                throw new \Disco\exceptions\Email("Email exception: Account defined by key {$this->settings[self::DEFAULT_ACCOUNT_KEY]} does not exist.");
            }//if

            //Create the Transport
            $transport = \Swift_SmtpTransport::newInstance($serverConfig['HOST'], $serverConfig['PORT'], $serverConfig['PROTOCOL']);
            $transport->setUsername($account['EMAIL']);
            $transport->setPassword($account['PASSWORD']);
            
            // Create the Mailer using the Transport
            $mailer = \Swift_Mailer::newInstance($transport);
            
            // Send the message
            $result = $mailer->send($message);

            $mailer->getTransport()->stop();

            return $result;

        }//try
        catch(\Swift_TransportException $e){
            error_log('Email::Error Swift_TransportException '.$e->getMessage());
            throw $e;
        }//catch
        catch(\Exception $e){
            error_log('Email::Error send failed '.$e->getMessage());
            throw $e;
        }//catch

    }//sendMessage



}//Email
?>
