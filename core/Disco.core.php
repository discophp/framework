<?php

require_once('Controller.core.php');

Class Disco {
    public static function view(){
        global $disco;
        return $disco->view;
    }//view

    public static function template(){
        global $disco;
        return $disco->template;
    }//template

    public static function util(){
        global $disco;
        return $disco->util;
    }//util

    public static function model($name){
        global $disco;
        if(isset($disco->models[$name]))
            return $disco->models[$name];

        $path = "../model/{$name}.model.php";
        if(is_file($path)){
            require_once($path);
            $disco->models[$name]=new $name();
            return $disco->models[$name];
        }//if
    }//model

    public static function router(){
        return new BaseRouter();
    }//router

    public static function db(){
        global $disco;
        return $disco->db;
    }//db

    public static function useView($orgView){
        $viewPath = "../view/$orgView.view.php";
        if(file_exists($viewPath)){
            require_once($viewPath);
            $orgView = explode('/',$orgView);
            if(count($orgView)==1)
                $orgView=$orgView[0];
            else
                $orgView = $orgView[count($orgView)-1];

            global $disco;
            $disco->view = new $orgView();
            $disco->view->prepare();
        }//if

    }//useView

    public static function useRouter($orgRouter){
        $routerPath = "../router/$orgRouter.router.php";
        if(file_exists($routerPath))
            require_once($routerPath);
    }//useRouter


}//Disco

class Model {
    public static function m($name){
        return Disco::model($name);
    }//m
}//Model


//set the standard view
Disco::useView('Standard');

?>
