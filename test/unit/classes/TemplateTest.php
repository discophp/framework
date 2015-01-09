<?php

Class TemplateTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Template = new \Disco\classes\Template;
    }//setUp

    public function testLive(){

        $data = Array('h1'=>'Test');
        $this->Template->live('<h1>{{$h1}}</h1>',$data);
        $t = View::instance()->html[0];
        $this->assertEquals('<h1>Test</h1>',$t);


        $t = $this->Template->buildLive('<h1>{{$h1}}</h1>',$data);
        $this->assertEquals('<h1>Test</h1>',$t);

    }//testLive


    public function testFiles(){

        $test = Array(
            'basic'=>Array('data'=>Array('h1'=>'Test'),'result'=>'<h1>Test</h1>'),
            'to-nest'=>Array('data'=>Array(),'result'=>'<h1>Nested</h1>'),
            'nested'=>Array('data'=>Array(),'result'=>'<h1>Nested</h1>'),
            'nested-struct'=>Array('data'=>Array('item'=>'test'),'result'=>'<li>test</li>'),
            'struct-container'=>Array('data'=>Array('data'=>Array(Array('item'=>'test1'),Array('item'=>'test2'))),'result'=>'<li>test1</li><li>test2</li>')
        );
        $assetDir = \App::instance()->config['PATH']."test/asset/template/discophp-unit-test-%1\$s.template.html";
        $dir = "app/template/discophp-unit-test-%1\$s.template.html";

        foreach($test as $f=>$d){

            $asset = sprintf($assetDir,$f);
            $file = sprintf($dir,$f);
            copy($asset,$file);

        }//foreach

        foreach($test as $f=>$d){

            $t = $this->Template->build('discophp-unit-test-'.$f,$d['data']);
            $t = str_replace("\n",'',$t);
            $this->assertEquals($t,$d['result']);

            $file = sprintf($dir,$f);
            unlink($file);

        }//foreach


    }//testFile


    public function testElse(){

        $t = '{{$d}}{{else \'success\'}}';
        $t = $this->Template->buildLive($t);
        $this->assertEquals('success',$t);

        $t = '{{$d}}{{else "success"}}';
        $t = $this->Template->buildLive($t);
        $this->assertEquals('success',$t);

        $t = '{{$d}}{{else $y}}';
        $t = $this->Template->buildLive($t,Array('y'=>'success'));
        $this->assertEquals('success',$t);

    }//testElse

}//TemplateTest
