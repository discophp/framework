<?php

class DataModelTest extends PHPUnit_Framework_TestCase {


    public function testDataModel(){

        $v = new TestDataModel;

        $v->nullable = true;
        $v->truthy = null;
        $v['required'] = null;
        $v['string'] = 1;
        $v['string_minlen_maxlen'] = 'okkkkkk';
        $v->int = 'ok';
        $v->int_min_max = 6;
        $v->uint = -22;
        $v->float = 'ok';
        $v->ufloat = -22.234;
        $v->boolean = 'ok';
        $v->char = 'aaaaa';
        $v->array = 'string';
        $v->object = true;
        $v->object_instanceof = new \StdClass;
        $v->closure = $v;
        $v->in = 'option4';
        $v->notin = 'option2';

        $this->assertFalse($v->verify());

        $this->assertEquals(0, count(array_diff_key($v->getErrors(),$v->getAllDefinitions())));



        $v->nullable = null;
        $v->truthy = true;
        $v['required'] = 'ok';
        $v['string'] = 'string';
        $v['string_minlen_maxlen'] = 'ok';
        $v->int = 20394;
        $v->int_min_max = 1;
        $v->uint = 22;
        $v->float = 2309.33;
        $v->ufloat = 22.234;
        $v->boolean = true;
        $v->char = 'a';
        $v->array = Array(1,2,3,4,5);
        $v->object = new \StdClass;
        $v->object_instanceof = $v;
        $v->closure = function(){ };
        $v->in = 'option1';
        $v->notin = 'option1';

        $this->assertTrue($v->verify());

        $v = new SecondaryDataModel;

        $v->regexp = 'a';
        $v->method = 'handle ';

        $this->assertTrue($v->verify());
        $this->assertEquals('@handle',$v['method']);

        $v->regexp = 'abc';
        $v->method = null;

        $this->assertFalse($v->verify());
        $this->assertEquals(0, count(array_diff_key($v->getErrors(),$v->getAllDefinitions())));

        //cannot override defined definition
        try {
            $v->setDefinition('method',Array());
            $this->assertTrue(false);
        }//try
        catch(\Exception $e){
            $this->assertTrue(true);
        }//catch

        //cannot set field that is not defined in the definition
        try {
            $v->bad = 'foo';
            $this->assertTrue(false);
        }//try
        catch(\Exception $e){
            $this->assertTrue(true);
        }//catch

        //cannot set field that is not defined in the definition
        try {
            $v['bad'] = 'foo';
            $this->assertTrue(false);
        }//try
        catch(\Exception $e){
            $this->assertTrue(true);
        }//catch

        //cannot validate field that doesn't exist in definition
        try {
            $v->verifyData('bad');
            $this->assertTrue(false);
        }//try
        catch(\Exception $e){
            $this->assertTrue(true);
        }//catch


    }//testDataModel


}//DataModelTest


class SecondaryDataModel extends \Disco\classes\DataModel {

    protected $definition = Array(
        'regexp' => Array(
            'regexp' => '^[a-c]+$',
        ),
        'method' => Array(
            'method' => 'testExpr',
            'premassage' => 'preMassage',
            'postmassage' => 'postMassage',
        ),
    );

    public function testExpr($value){
        return is_string($value);
    }//testExpr

    public function preMassage($value){
        if(is_string($value)){
            return trim($value);
        }//if
        return $value;
    }//preMassage

    public function postMassage($value){
        return '@' . $value;
    }//postMassage

}

class TestDataModel extends \Disco\classes\DataModel {

    protected $definition = Array(
        'nullable' => Array(
            'type' => 'string',
            'nullable' => true,
        ),
        'truthy' => Array(
            'type' => 'string',
            'truthy' => true,
        ),
        'required' => Array(
            'type' => 'string',
            'required' => true,
            'error' => 'OK',
        ),
        'default' => Array(
            'type' => 'string',
            'default' => 'ok',
        ),
        'string' => Array(
            'type' => 'string',
        ),
        'string_minlen_maxlen' => Array(
            'type' => 'string',
            'minlen' => 1,
            'maxlen' => 5,
        ),
        'int' => Array(
            'type' => 'int',
        ),
        'int_min_max' => Array(
            'type' => 'int',
            'min' => 1,
            'max' => 5,
        ),
        'uint' => Array(
            'type' => 'uint',
        ),
        'float' => Array(
            'type' => 'float',
        ),
        'ufloat' => Array(
            'type' => 'ufloat',
        ),
        'boolean' => Array(
            'type' => 'boolean',
        ),
        'char' => Array(
            'type' => 'char',
        ),
        'array' => Array(
            'type' => 'array',
        ),
        'object' => Array(
            'type' => 'object',
        ),
        'object_instanceof' => Array(
            'type' => 'object',
            'instanceof' => '\Disco\classes\DataModel',
        ),
        'closure' => Array(
            'type' => 'closure',
        ),
        'in' => Array(
            'type' => 'string',
            'in' => Array('option1','option2','option3'),
        ),
        'notin' => Array(
            'type' => 'string',
            'notin' => Array('option2',),
        ),


    );

}
