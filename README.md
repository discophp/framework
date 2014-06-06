<h1>Disco PHP Framework</h1>


<p>This is the core code that powers the Disco PHP framework.</p>

<p>
For the application project wrapper head over to: 
<a href='http://github.com/discophp/project'>github.com/discophp/project</a>
</p>


<h2>What Disco?</h2>

<p>The Disco PHP Framework is empowered by proven software design principles paired with modern tactics enabled by new
native PHP Class manipulations available in PHP 5.4.0 and above.</p>

<p>At its core the Disco PHP Framework is a MVC (Model View Controller) segregated framework with all of its roots
branching outward from a concept called IoC (Inversion of Control). Working under these root principles Disco
allows for superior flexibility within your code, making it insanely extensible and modifiable, while keeping
coupling at an all time low.</p>

<h3>RESTful Routing like it should be</h3>

<p>Let the Disco <a href='http://discophp.com/docs/Router'>Router</a> class dispell the pains of rewrite rules,
filtering, variable extraction, handling HTTPS, and so much more.</p>

<h4>Enjoy Facades and Inversion of Control</h4>

<p>With Discos default <a href='http://discophp.com/docs/IoC-facades'>Facades and the Disco inversion of control container</a> development has never been quicker, or
less painful..</p>

<p>Resolve singleton obejcts from the container:</p>

```php
    Disco::with('YourSingletonClass')->method($arg);
```

<p>Factory style obejct creation from the container:</p>

```php
    Disco::factory('YourFactoryClass')->method($arg);
```

<p>Default Facades shipped with Disco</p>

<ul>
    <li><a href='http://discophp.com/docs/Cache'>Cache</a></li>
    <li><a href='http://discophp.com/docs/Crypt'>Crypt</a></li>
    <li><a href='http://discophp.com/docs/Database'>DB</a></li>
    <li><a href='http://discophp.com/docs/Email'>Email</a></li>
    <li><a href='http://discophp.com/docs/Event'>Event</a></li>
    <li><a href='http://discophp.com/docs/Model'>Model</a></li>
    <li><a href='http://discophp.com/docs/Queue'>Queue</a></li>
    <li><a href='http://discophp.com/docs/Session'>Session</a></li>
    <li><a href='http://discophp.com/docs/Template'>Template</a></li>
    <li><a href='http://discophp.com/docs/View'>View</a></li>
</ul>

<h5>Disco uses Composer for Dependency Management and Dependency Injection, so relax, this will be easy</h5>

<p>Diso leverages <a href='http://getcomposer.org'>Composer</a> for maintaining youre applications library
dependencies and Class autoloading. If you used composer before then you will know how easy this makes life, if not
you're in for a real treat.</p>

<p>Learn about <a href='http://discophp.com/docs/install'>installing the Disco PHP Framework using composer</a></p>

<h6>Development level and Production level configuration files for distrubuted development and ease of
maintanance</h6> 

<p>You'll enjoy how easy it is to <a href='http://discophp.com/docs/config'>configure Disco</a> and the ability to
differentiate configuration based on the applications environment.</p>


