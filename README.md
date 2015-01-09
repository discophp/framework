[![Build
Status](https://travis-ci.org/discophp/framework.svg?branch=master)](https://travis-ci.org/discophp/framework)
[![test Stable
Version](https://poser.pugx.org/discophp/framework/v/stable.svg)](https://packagist.org/packages/discophp/framework)
[![Total
Downloads](https://poser.pugx.org/discophp/framework/downloads.svg)](https://packagist.org/packages/discophp/framework)
[![Latest Unstable
Version](https://poser.pugx.org/discophp/framework/v/unstable.svg)](https://packagist.org/packages/discophp/framework)
[![License](https://poser.pugx.org/discophp/framework/license.svg)](https://packagist.org/packages/discophp/framework)

<h1>Disco PHP Framework</h1>


<p>This is the core code that powers the Disco PHP framework.</p>

<p>
For the application project wrapper head over to: 
<a href='http://github.com/discophp/project'>github.com/discophp/project</a>
</p>


<h2>What Disco?</h2>

<p>The Disco PHP Framework is empowered by proven software design principles paired with modern tactics enabled by new
native PHP Class manipulations available in PHP 5.4.0 and above.</p>

<p>At its core is a powerful dependency injection (DI) and Inversion of Control (IoC) container 
which is built on top of the <a href='https://github.com/fabpot/Pimple'>Pimple Container</a>.
The framework is a MVC esque (Model View Controller) segregated framework.</p>

<h4>RESTful Routing like it should be</h4>

<p>Let the Disco <a href='http://discophp.com/docs/Router'>Router</a> class dispell the pains of rewrite rules,
filtering, variable extraction, handling HTTPS, and authenticated browsing.</p>

<h4>Enjoy Facades and Inversion of Control</h4>

<p>With Discos default <a href='http://discophp.com/docs/IoC-facades'>Facades and the Disco inversion of control container</a> development has looked cleaner or been more maintainable.</p>

<p>Register services in the container</p>

```php
    App::make('service',function(){
        return new class;
    });

    //OR

    App::make('service','namespace/class');

```

<p>Registering Factory services</p>

```php
    App::as_factory('factory','factory');
```

<p>Registering protected services</p>

```php
    App::as_protected('rand',function(){
        return rand();
    });
```


<h4>Work with a service from the container, even if its not registered</h4>

```php
    App::with('class')->method($arg);
```


<h4>Dependency Injection</h4>

<p>When services are instianted from the container if their constructors specify other classes as paramaters, Disco 
will resolve those classes out of the container and pass their refrences to the constructor as arguements</p>

```php
    public function __construct(SomeClass $c1,SomeClass2 $c2){
        $this->c1 = $c1;
        $this->c2 = $c2;
    }
```


<b>Default Facades shipped with Disco</b>

<ul>
    <li><a href='http://discophp.com/docs/Cache'>Cache</a></li>
    <li><a href='http://discophp.com/docs/Crypt'>Crypt</a></li>
    <li><a href='http://discophp.com/docs/Database'>DB</a></li>
    <li><a href='http://discophp.com/docs/Email'>Email</a></li>
    <li><a href='http://discophp.com/docs/Event'>Event</a></li>
    <li><a href='http://discophp.com/docs/Form'>Form</a></li>
    <li><a href='http://discophp.com/docs/Html'>Html</a></li>
    <li><a href='http://discophp.com/docs/Model'>Model</a></li>
    <li><a href='http://discophp.com/docs/Queue'>Queue</a></li>
    <li><a href='http://discophp.com/docs/Session'>Session</a></li>
    <li><a href='http://discophp.com/docs/Template'>Template</a></li>
    <li><a href='http://discophp.com/docs/View'>View</a></li>
</ul>

<h5>Disco uses Composer for Dependency Management and autoloading, so relax, this will be easy</h5>

<p>Diso leverages <a href='http://getcomposer.org'>Composer</a> for maintaining youre applications library
dependencies and Class autoloading. If you used composer before then you will know how easy this makes life, if not
you're in for a real treat.</p>

<p>Learn about <a href='http://discophp.com/docs/install'>installing the Disco PHP Framework using composer</a></p>

<h6>Development level and Production level configuration files for distrubuted development and ease of
maintanance</h6> 

<p>You'll enjoy how easy it is to <a href='http://discophp.com/docs/config'>configure Disco</a> and the ability to
differentiate configuration based on the applications environment.</p>


