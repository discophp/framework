<?php

Class DataTest extends PHPUnit_Framework_TestCase {


    public function setUp(){

        $this->stream = \App::instance()->config['PATH'].'test/asset/mock-php-input-stream.txt';

        $_POST['post_test'] = 'post_value';
        $_GET['get_test'] = 'get_value';

    }//setUp


    public function testPut(){

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $data = new \Disco\classes\Data($this->stream);
        $this->assertEquals('put_value',$data->put('put_test'));

        $data->put()->set('test','test');
        $this->assertEquals('test',$data->put('test'));

    }//testData

    public function testPost(){

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $data = new \Disco\classes\Data($this->stream);
        $this->assertEquals('post_value',$data->post('post_test'));

        $data->post()->set('test','test');
        $this->assertEquals('test',$data->post('test'));

    }//testPost

    public function testGet(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $data = new \Disco\classes\Data($this->stream);
        $this->assertEquals('get_value',$data->get('get_test'));

        $data->get()->set('test','test');
        $this->assertEquals('test',$data->get('test'));

    }//testPost

    public function testDelete(){

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $data = new \Disco\classes\Data($this->stream);
        $this->assertEquals('delete_value',trim($data->delete('delete_test')));

        $data->delete()->set('test','test');
        $this->assertEquals('test',$data->delete('test'));


    }//testData

    public function testWhere(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['get_test'] = 'getValue';
        $data = new \Disco\classes\Data($this->stream);
        $this->assertEquals('getValue',$data->get()->where('get_test','alpha'));

    }//testWhere



}//DataTest

