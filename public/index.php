<?php


require_once('../classes/Control.class.php');

$ctrl = new Control();


$ctrl->tc->pushHTML('

    <div class="row collapse margin-top-large">
        <h2>About the plate</h2>
        <p class="label">Light</p>
        <p class="label">Extensible</p>
        <p class="label">Good code practices</p>

        <hr>

        <div class="small-12 columns">
            <h3>Comes packaged with my favorite CSS Framework <a href="http://foundation.zurb.com/" target="_blank">Foundation</a></h3>
            <p>If you haven\'t used Foundation yet, head over to there site. Your in for a treat.</p>
        </div>

        <div class="small-12 columns">
            <h3>It also Comes packaged with my favorite Email Framework <a href="http://foundation.zurb.com/" target="_blank">Swift Mailer</a></h3>
            <p>Send an email through the Boiler Plate like:</p>
            <code>$ctrl->util->sendEmail("user","EmailTo@example.com","Subject line","body message");</code>
            <p>You will need to go change your email settings in <span class="label">classes/Utilities.class.php</span></p>
        </div>

        <div class="small-12 columns">
            <h3>Structure of the Plate</h3>
            <pre>
├── backups
├── classes
│   ├── Control.class.php
│   ├── Database.class.php
│   ├── TemplateControl.class.php
│   └── Utilities.class.php
├── logs
├── public
│   ├── css
│   │   ├── css.css
│   │   ├── dataTables.css
│   │   └── foundation.min.css
│   ├── images
│   │   └── design
│   ├── index.php
│   └── scripts
│       ├── foundation.min.js
│       ├── jquery.dataTables.min.js
│       ├── js.js
│       └── modernizr.js
└── support_libraries
    └── swiftmailer

            </pre>

        </div>


        <div class="small-12 columns">
            <p>It comes with 4 default classes all of which can be extended, chopped up, and rebuilt to your liking</p>
            <hr>
            <dl class="tabs" data-tab>
                <dd class="active"><a href="#Control">Control</a></dd>
                <dd><a href="#TemplateControl">TemplateControl</a></dd>
                <dd><a href="#Database">Database</a></dd>
                <dd><a href="#Utilities">Utilities</a></dd>
            </dl>
            <div class="tabs-content">
                <div class="content active" id="Control">
                    <span class="label"><h4>classes/Control.class.php</h4></span>
                    <p>This is a Singleton Object</p>
                    <p>Every page is built through it</p>
                    <p>It is used like this</p>
                    <pre><code>
                    require_once("../classes/Control.class.php");

                    $ctrl = New Control();
                    
                    $ctrl->tc->pushHTML("<tag>Hello World</tag>");

                    $ctrl->tc->printPage();

                    </code></pre>
                    <p>And thats it, you just built an entire page. Before you say but WTF, let move to TemplateControl</p>
                </div>
                <div class="content" id="TemplateControl">
                    <span class="label"><h4>classes/TempalteControl.class.php</h4></span>
                    <p>This class provides all the functionality for putting togethor your HTML snippets and providing a default template across your site.</p>
                    <p>It is instantiated within the Control classes constructor and is stored in $ctrl->tc property for access.</p>
                    <p>You can do all sorts of things through it, it\'s really self explanitory though so you should just look at the code on github.</p>
                </div>
                <div class="content" id="Database">
                    <span class="label"><h4>classes/Database.class.php</h4></span>
                    <p>Go in <span class="label">classes/Database.class.php</span> and change the MYSQLi database connection string to your database.</p>
                    <p>It is instantiated within the Control classes constructor and is stored in $ctrl->db property for access.</p>
                    <p>A couple things it will do for you out of the box</p>
                    <ul>
                        <li>Execution of a query.</li>
                        <li>Execution of a Stored Procedure.</li>
                        <li>AES encryption & decryption</li>
                        <li>Password hashing</li>
                    </ul>
                    <p>Make a call to it simply:</p>
                    <pre><code>
                        $result = $ctrl->db->executeSP("CALL sp(5)");
                    </code></pre>

                </div>
                <div class="content" id="Utilities">
                    <span class="label"><h4>classes/Utilities.class.php</h4></span>
                    <p>This class is just as its name makes it sound. Functions that serve a purpose accross multiple pages and at random times get put in here.</p>
                    <p>Some default functions:</p>
                    <ul>
                        <li>Encode/Decode a URL (replacing - with " " and vice-versa) </li>
                        <li>Send an email</li>
                    </ul>
                </div>

            </div>

            </div>
        </div>
    </div>

    <hr>




');

$ctrl->tc->printPage();


