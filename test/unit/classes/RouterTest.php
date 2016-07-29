<?php

Class RouterTest extends PHPUnit_Framework_TestCase {

    public function testGet(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::get('/test',function() use(&$t) {
            $t = true;
        })->process();

        $this->assertTrue($t);
        $this->assertTrue(Router::routeMatch());

        Router::routeMatch(false);
        $this->assertFalse(Router::routeMatch());

        $t = false;

        $_SERVER['REQUEST_URI'] = '/bobby';

        Router::get('/bobby',function() use(&$t) {
            $t = true;
            return false;
        })->process();

        $this->assertTrue($t);
        $this->assertFalse(Router::routeMatch());

    }//testGet


    public function testPost(){

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::post('/test',function() use(&$t) {
            $t = true;
            return false;
        })->process();

        $this->assertTrue($t);

    }//testPost


    public function testPut(){

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::put('/test',function() use(&$t) {
            $t = true;
            return false;
        })->process();

        $this->assertTrue($t);

    }//testPut


    public function testDelete(){

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/test';
        $t = false;

        Router::delete('/test',function() use(&$t) {
            $t = true;
            return false;
        })->process();

        $this->assertTrue($t);

    }//testDelete


    public function testMulti(){

        $_SERVER['REQUEST_URI'] = '/test';

        $methods = Array('GET','PUT','DELETE','POST');
        $t = false;

        $action = function() use(&$t){
            $t = true;
            return false;
        };//action

        foreach($methods as $method){

            $_SERVER['REQUEST_METHOD'] = $method;

            $t = false;

            Router::multi('/test',Array(
                'get'   => $action,
                'post'  => $action,
                'put'   => $action,
                'delete'=> $action,
            ))->process();

            $this->assertTrue($t);

        }//foreach


    }//testMulti


    public function testWithVars(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/random';
        $t = false;

        Router::get('/test/{str}',function($str) use(&$t) {
            $t = ($str=='random');
            return false;
        })->where('str','alpha')->process();

        $this->assertTrue($t);


        $_SERVER['REQUEST_URI'] = '/test/random/test/509/true';
        $t = false;

        Router::get('/test/{str}/test/{int}/{boolean}',function($str,$int,$boolean) use(&$t) {
            $t = ($str=='random') && ($int==509) && ($boolean=='true');
            return false;
        })->where(Array('str'=>'alpha','int'=>'integer','boolean'=>'boolean'))->process();

        $this->assertTrue($t);


        $_SERVER['REQUEST_URI'] = '/product/3005-the-rig/reviews';
        $t = false;

        Router::get('/product/{id}-{name}/{type}',function($id,$name,$type) use(&$t) {
            $t = ($id==3005) && ($name=='the-rig') && ($type=='reviews');
            return false;
        })->where(Array('id'=>'integer','name'=>'alpha','type'=>'(reviews|details)'))->process();

        $this->assertTrue($t);

       
    }//testWithVars


    public function testController(){

        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        Router::get('/test','DiscoPhpUnitTestController@index')->process();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('success',$output);

        $_SERVER['REQUEST_URI'] = '/test/random';

        ob_start();
        Router::get('/test/{var}','DiscoPhpUnitTestController@withVar')->where('var','alpha')->process();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('random',$output);

    }//testController


    public function testFilter(){

        $_SERVER['REQUEST_URI'] = '/test/area';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $t = false;
        Router::filter('/test/{*}')->to(function() use(&$t){
            $t = true;
        })->process();

        $this->assertTrue($t);
        $this->assertFalse(Router::routeMatch());

    }//testFilter


    public function testFilterWithChildren(){

        $_SERVER['REQUEST_URI'] = '/test/area/55';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $t = false;
        $child = false;
        $id = 0;
        Router::filter('/test/{*}')->to(function() use(&$t){
            $t = true;
        })
        ->children(Array(
            'area/{id}' => Array(
                'type' => 'get',
                'action' => function($param) use(&$child,&$id){
                    $id = $param;
                    $child = true;
                    return false;
                },
                'where' => Array('id' => 'integer_positive')
            )
        ))
        ->process();

        $this->assertTrue($t);
        $this->assertTrue($child);
        $this->assertEquals(55,$id);
        $this->assertFalse(Router::routeMatch());

        $_SERVER['REQUEST_URI'] = '/double/testing/create';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $child = false;
        $action = '';
        Router::filter('/double/testing/{*}')
        ->children(Array(
            '{action}' => Array(
                'type' => 'post',
                'action' => function($param) use(&$child,&$action){
                    $action = $param;
                    $child = true;
                    return false;
                },
                'where' => Array('action' => 'alpha_numeric')
            )
        ))
        ->process();

        $this->assertTrue($child);
        $this->assertEquals('create',$action);
        $this->assertFalse(Router::routeMatch());

       
    }//testFilterWithChildren


    public function testChildren(){

        $_SERVER['REQUEST_URI'] = '/user/34093/create/post';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $parent = false;
        $child = false;
        $vars = Array();

        Router::get('/user/{id}',function($id) use(&$parent){
            $parent = true;
        })
        ->where(Array('id'=>'integer'))
        ->children(Array(
            '/create/{type}' => Array(
                'type' => 'get',
                'action' => function($id,$type) use(&$child,&$vars){
                    $child = true;
                    $vars['id'] = $id;
                    $vars['type'] = $type;
                    return false;
                },
                'where' => Array(
                    'type' => 'alpha'
                )
            )
        ))->process();

        $this->assertFalse($parent);
        $this->assertTrue($child);
        $this->assertEquals(34093,$vars['id']);
        $this->assertEquals('post',$vars['type']);


        $_SERVER['REQUEST_URI'] = '/user/34093/create/post/delete/55';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $parent = false;
        $child = false;
        $nestedChild = false;
        $vars = Array();

        Router::get('/user/{id}',function($id) use(&$parent){
            $parent = true;
            return false;
        })
        ->where(Array('id'=>'integer'))
        ->children(Array(
            '/create/{type}' => Array(
                'type' => 'get',
                'action' => function($id,$type) use(&$child){
                    $child = true;
                    return false;
                },
                'where' => Array(
                    'type' => 'alpha'
                ),
                'children' => Array(
                    '/delete/{post_id}' => Array(
                        'type' => 'get',
                        'action' => function($id,$type,$postId) use(&$nestedChild,&$vars){
                            $nestedChild = true;
                            $vars['id'] = $id;
                            $vars['type'] = $type;
                            $vars['post_id'] = $postId;
                            return false;
                        },
                        'where' => Array(
                            'post_id' => 'integer_positive'
                        )
                    )
                )
            )
        ))->process();

        $this->assertFalse($parent);
        $this->assertFalse($child);
        $this->assertTrue($nestedChild);
        $this->assertEquals(34093,$vars['id']);
        $this->assertEquals('post',$vars['type']);
        $this->assertEquals(55,$vars['post_id']);


    }//testChildren


    public function testProcessRouterArray(){

        $_SERVER['REQUEST_URI'] = '/test/55';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $t = false;

        $routes = Array(
            '/test/{id}' => Array(
                'type' => 'get',
                'action' => function() use(&$t){
                                $t = true;
                                return false;
                            },
                'where' => Array(
                    'id' => 'integer_positive',
                )
            )
        );

        Router::filter('/test/{*}')->to($routes)->process();

        $this->assertTrue($t);

        $t = false;

        $routes = Array(
            '/test/{*}' => Array(
                'type' => 'filter',
                'action' => $routes
            )
        );

        Router::processRouterArray($routes);

        $this->assertTrue($t);

    }//filterTest



}//RouterTest
