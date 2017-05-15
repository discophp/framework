<?php
namespace Disco\html;

/**
 * This is the Form class.
 * You can build crazy dynamic forms with ease.
*/
Class Form {


    /**
     * @var string The model to use to build the form.
    */
    public $from;


    /**
     * @var string The condition used on the model.
    */
    public $where;


    /**
     * @var boolean Should the fields contain no data?
    */
    public $blank = false;


    /**
     * @var array Fields to omit from the form.
    */
    public $without=array();


    /**
     * @var array Only fields that should be allowed.
    */
    public $with=array();


    /**
     * @var array \Closure functions to be applied to specific fields.
    */
    public $force=array();


    /**
     * @var \Closure A default \Closure function to be called on each field.
    */
    public $defaultForce;


    /**
     * @var string A string containing %1\$s & %2\$s for field name, field value respectvly.
    */
    public $wrap;


    /**
     * @var null|array The html properties to appply to the created form element.
    */
    public $formProps;


    /**
     * @var array The properties to apply to a specific field.
    */
    public $props=array();


    /**
     * @var array The properties to be applied to every field.
    */
    public $defaultProps=array();


    /**
     * @var string String that replaces the default submit button.
    */
    public $submitButton;


    /**
     * @var boolean Is the form using a CSRF token?
    */
    public $useCSRFToken = false;


    /**
     * @var \Disco\App Reference to our app instance {@link \Disco\classes\App}.
    */
    private $app;



    public function __construct(){
        $this->app = app();
    }//_construct



    /**
     * Reset all local object properties to there default values;
     *
     * 
     * @return void
    */
    private final function reset(){
        $this->from = null;
        $this->where = null;
        $this->blank = false;
        $this->without = array();
        $this->with = array();
        $this->force = array();
        $this->defaultForce = null;
        $this->wrap = null;
        $this->formProps = null;
        $this->props = array();
        $this->defaultProps = array();
        $this->submitButton = null;
        $this->useCSRFToken = false;
    }//reset



    /**
     * Return a CSRF token.
     *
     *
     * @return string
    */
    public function token(){
        return \Data::getCSRFToken();
    }//token



    /**
     * Specify that the form should include/check for a CSRF token.
     *
     *
     * @return self 
    */
    public function withToken(){
        $this->useCSRFToken = true;
        return $this;
    }//withToken



    /**
     * Does the passed CSRF token match the CSRF Session token? Check done using a 
     * timing safe comparision.
     *
     *
     * @return boolean
    */
    public function validToken(){
        return \Data::validateCSRFToken();
    }//validToken



    /**
     * Build the form using the specified $model.
     *
     *
     * @param string $model The model to use.
     *
     * @return self 
    */
    public function from($model){
        $this->from = $model;
        return $this;
    }//from



    /**
     * The condition used on the model.
     *
     *
     * @param mixed $where The condition.
     *
     * @return self 
    */
    public function where($where){
        $this->where = $where;
        return $this;
    }//where



    /**
     * Make the form contain no set data.
     *
     *
     * @return self 
    */
    public function blank(){
        $this->blank = true;
        return $this;
    }//blank



    /**
     * Wrap each input in a specific user specified string
     * containing identifies %1\$s and %2\$s for the 
     * field name and field value respectively.
     *
     *
     * @param string $wrap The input wrapper.
     *
     * @return self
    */
    public function wrap($wrap){
        $this->wrap = $wrap;
        return $this;
    }//wrap



    /**
     * Html properties to be applied to the generated form.
     *
     *
     * @param array $props The properties.
     *
     * @return self
    */
    public function formProps($props){
        $this->formProps = $props;
        return $this;
    }//formProps


    /**
     * Specify either an array to be used as properties on a specific field or a \Closure function to be called.
     * If $props is an array then those are the default properties to be used on all fields.
     *
     *
     * @param string|array $props The field to apply $action to, or an array to be applied to all fields.
     * @param array|\Closure $action The properties of $props or the \Closure function to be called.
     *
     * @return self
    */
    public function props($props,$action=null){
        if(is_array($props)){
            $this->defaultProps = $props;
        }//if
        else {
            $this->props[$props] = $action;
        }//el
        return $this;
    }//props



    /**
     * Fields not to be included in the form. Accepts arguments via func_get_args(),
     * pass in an array or any number of field names, or a comma delimited string of fields.
     *
     *
     * @return self
    */
    public function without(){

        $args = func_get_args();
        if(!isset($args[1])){
            if(!is_array($args[0])){
                $without = explode(',',$args[0]);
            }//elif
            else {
                $without = $args[0];
            }//el
        }//if
        else {
            $without = $args;
        }//el

        $this->without = $without;

        return $this;

    }//without



    /**
     * Replacement for default submit button.
     *
     *
     * @param string $b The button html.
     *
     * @return self
    */
    public function submitButton($b){
        $this->submitButton = $b;
        return $this;
    }//submitButton



    /**
     * Fields to be included in the form. Accepts arguments via func_get_args(),
     * pass in an array or any number of field names, or a comma delimited string of fields.
     *
     *
     * @return self
    */
    public function with(){

        $args = func_get_args();
        if(!isset($args[1])){
            if(!is_array($args[0])){
                $with = explode(',',$args[0]);
            }//elif
            else {
                $with = $args[0];
            }//el
        }//if
        else {
            $with = $args;
        }//el

        $this->with = $with;

        return $this;

    }//with



    /**
     * Force using a custom \Closure to handle building whatever the field should represent.
     * The $action will be passed arguments $k,$v representing the field name and the field value.
     * If no $k is passed then the \Closure function will become the default function used to build
     * each field.
     *
     *
     * @param string|\Closure $k The field name or the default \Closure function to force.
     * @param null|\Closure $action The custom function to build the field.
     *
     * @return self
    */
    public function force($k,$action=null){
        if($k instanceof \Closure){
            $this->defaultForce = $k;
        }//if
        else {
            $this->force[$k] = $action;
        }//el
        return $this;
    }//force



    /**
     * Build the form. If $fields is passed then it will be used as the data to build the form around.
     *
     *
     * @param null|array $fields The data that should be used to build the form if a model is not being used to
     * generate the form.
     *
     * @return string
    */
    public function make($fields=null){

        $primaryKeys = array();

        if($fields){
            if(!is_array($fields)){
                $fields = array($fields);
            }//if
        }//if
        else if(!$this->where){
            $m = $this->app->with($this->from);
            $columns = $m->columns();
            $fields = array();
            foreach($columns as $k=>$col){
                $fields[$col['Field']] = ''; 
            }//foreach
        }//elif
        else {
            $m = $this->app->with($this->from);
            $fields = $m->select('*')->where($this->where)->limit(1)->first();
            $primaryKeys = $m->ids;
            if(!is_array($primaryKeys)){
                $primaryKeys = array($primaryKeys);
            }//if
        }//el

        $form = '';

        if($this->useCSRFToken){
            $form .= \Data::getCSRFTokenInput();
        }//if

        foreach($fields as $k=>$v){

            if(!empty($this->with) && !in_array($k,$this->with)){
                continue;
            }//if

            if(!empty($this->without) && in_array($k,$this->without)){
                continue;
            }//if

            if($this->blank){
                $v = null;
            }//if

            if(isset($this->force[$k])){
                $i = $this->force[$k]($k,$v);
            }//if
            else if($this->defaultForce){
                $i = call_user_func_array($this->defaultForce,array($k,$v));
            }//if
            else {

                $opts = array('name'=>$k,'value'=>$v,'type'=>'text');

                if(is_numeric($v)){
                    $opts['type'] = 'number';
                }//if

                if($this->defaultProps){
                    $opts = array_merge($opts,$this->defaultProps);
                }//if

                if(isset($this->props[$k])){
                    if($this->props[$k] instanceof \Closure){
                        $opts = array_merge($opts,$this->props[$k]($k,$v));
                    }//if
                    else {
                        $opts = array_merge($opts,$this->props[$k]);
                    }//el
                }//if

                if(in_array($k,$primaryKeys)){
                    $opts['type'] = 'hidden';
                    $form .= $this->app['Html']->input($opts);
                    continue;
                }//if

                $i = $this->app['Html']->input($opts);

            }//el

            if($this->wrap){
                $form .= sprintf($this->wrap,$k,$i);
            }//if
            else {
                $form .= $this->app['Html']->label($k.$i);
            }//el

        }//foreach

        if($this->submitButton){
            $form .= $this->submitButton;
        }//if
        else {
            $form .= $this->app['Html']->input(array('type'=>'submit','value'=>'send'));
        }//el

        if(is_array($this->formProps)){
            $p = $this->formProps;
            $this->reset();
            return $this->app['Html']->form($p,$form);
        }//if

        $this->reset();
        return $this->app['Html']->form($form);
    }//make



    /**
     * Post a form. If $postKey is not passed then it is assumed that all $_POST data is to be used.
     * Updates the models table with a condition formed by the primary keys posted with the form, or
     * the condition specified in the where() method. 
     *
     *
     * @param string $postKey The post value containing data to be used.
     *
     * @return boolean
    */
    public function post($postKey=null){

        if($postKey){
            $data = $this->app['Data']->post($postKey);
        }//if
        else {
            $data = $this->app['Data']->post()->all();
        }//el

        $real = array();
        $primaryKeys = array();

        $columns = $this->app->with($this->from)->columns();


        if($this->useCSRFToken){
            if(!\Data::validateCSRFToken()){
                return false;
            }//if
        }//if

        foreach($columns as $k=>$column){
            if(isset($data[$column['Field']])){

                $v = $data[$column['Field']];

                if($column['Key'] == 'PRI'){
                    $primaryKeys[$column['Field']] = $v;
                }//if
                else {
                    if($v=='' && $column['Null']=='YES'){
                        $v = null;
                    }//if
                    $real[$column['Field']] = $v; 
                }//el
            }//if
        }//foreach

        if($this->where){
            $primaryKeys = $this->where;
        }//el
        else if(count($primaryKeys)==0){
            $res = $this->app->with($this->from)->insert($real);
            $this->reset();
            return $res;
        }//if

        $res = $this->app->with($this->from)->update($real)->where($primaryKeys)->finalize();
        $this->reset();
        return $res;

    }//post



    /**
     * Build a select menu from a set of $data with a specified $name, set the selected
     * option in the select menu if there is a match found between $selectedValue and option_value.
     *
     * If passing a \mysqli_result it must contain fields named as:
     *  - option_value 
     *  - option_text
     *
     *
     * @param array|\mysqli_result $data The data to be used to build the select menu.
     * @param string $name The name of the select menu.
     * @param mixed $selectedValue The option_value to set as selected.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
    */
    public function selectMenu($data, $name, $selectedValue = null){

        $options = '';

        if($data instanceof \PDOStatement){

            while($row = $data->fetch()){
                $opts = array('value'=>$row['option_value']);
                if($row['option_value'] == $selectedValue){
                    $opts['selected'] = 'selected';
                }//if
                $options .= $this->app['Html']->option($opts,$row['option_text']); 
            }//while
            
        }//if
        else if(is_array($data)){

            foreach($data as $value=>$text){
                $opts = array('value'=>$value);
                if($value  == $selectedValue){
                    $opts['selected'] = 'selected';
                }//if
                $options .= $this->app['Html']->option($opts,$text); 
            }//foreach

        }//el
        else {
            throw new \InvalidArgumentException('Data must be an instance of a \PDOStatement or an array');
        }//el

        return $this->app['Html']->select(array('name'=>$name),$options);

        
    }//selectMenu



    /**
     * Build a radio button group from a set of $data with a specified $name, set the selected
     * radio button in the group if there is a match found between $selectedValue and button_value.
     *
     * If passing a \mysqli_result it must contain fields named as:
     *  - button_value 
     *  - button_text
     *
     *
     * @param array|\mysqli_result $data The data to be used to build the radio button group.
     * @param string $name The name of the radio button group.
     * @param mixed $selectedValue The option_value to set as checked.
     *
     * @return string
    */
    public function radioButtons($data,$name,$selectedValue=null){

        $buttons = '';

        if($data instanceof \PDOStatement){

            while($row = $data->fetch()){
                $opts = array('name'=>$name,'value'=>$row['button_value'],'type'=>'radio');
                if($row['button_value'] == $selectedValue){
                    $opts['checked'] = 'checked';
                }//if

                $button = $this->app['Html']->input($opts);
                $buttons .= $this->app['Html']->label($row['button_text'].$button);

            }//while

        }//if
        else if(is_array($data)){

            foreach($data as $k=>$v){
                $opts = array('name'=>$name,'value'=>$k,'type'=>'radio');
                if($k == $selectedValue){
                    $opts['checked'] = 'checked';
                }//if

                $button = $this->app['Html']->input($opts);
                $buttons .= $this->app['Html']->label($v.$button);

            }//foreach

        }//elif
        else {

        }//el

        return $buttons;

    }//radioButtons


}//Form
