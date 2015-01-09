<?php
Class FormTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $this->Form = new \Disco\classes\Form();
    }//setUp

    public function testBasic(){

        $form = $this->Form
            ->formProps(Array('method'=>'POST'))
            ->props(Array('class'=>'test'))
            ->make(Array('email'=>'test@email.com'));

        $actual = '<form method="POST" ><label>email<input name="email" value="test@email.com" type="text" class="test" /></label><input type="submit" value="send" /></form>';

        $this->assertEquals($actual,$form);

        $form = $this->Form
            ->force(function($k,$v){
               return "$k $v";
            })
            ->force('name',function($k,$v){
                return "<div>$k $v</div>";
            })
            ->wrap("<div>%1\$s %2\$s</div>")
            ->submitButton('<div>Submit</div>')
            ->make(Array('email'=>'test@email.com','name'=>'Test Name'));

        $actual = '<form><div>email email test@email.com</div><div>name <div>name Test Name</div></div><div>Submit</div></form>';

        $this->assertEquals($actual,$form);

    }//testBasic

    public function testToken(){

        $form = $this->Form
            ->withToken()
            ->make(Array('name'=>'Test'));

        $this->assertContains(Form::token(),$form);

        $this->assertTrue(Form::validToken(Form::token()));

    }//testToken

    public function testFromModel(){

        $dbTest = new DBTest;
        $dbTest->setUp();

        $form = $this->Form->from('PersonModelTest')
            ->where(Array('person_id'=>1))
            ->make();
        $actual = '<form><input name="person_id" value="1" type="hidden" /><label>name<input name="name" value="Person One" type="text" /></label><label>age<input name="age" value="30" type="number" /></label><input type="submit" value="send" /></form>';
        $this->assertEquals($actual,$form);


        $form = $this->Form->from('PersonModelTest')
            ->where(Array('person_id'=>1))
            ->with(Array('name'))
            ->make();
        $actual = '<form><label>name<input name="name" value="Person One" type="text" /></label><input type="submit" value="send" /></form>';
        $this->assertEquals($actual,$form);


        $form = $this->Form->from('PersonModelTest')
            ->where(Array('person_id'=>1))
            ->without(Array('age'))
            ->make();
        $actual = '<form><input name="person_id" value="1" type="hidden" /><label>name<input name="name" value="Person One" type="text" /></label><input type="submit" value="send" /></form>';
        $this->assertEquals($actual,$form);


        $form = $this->Form->from('PersonModelTest')
            ->blank()
            ->make();
        $actual = '<form><label>person_id<input name="person_id" value="" type="text" /></label><label>name<input name="name" value="" type="text" /></label><label>age<input name="age" value="" type="text" /></label><input type="submit" value="send" /></form>';
        $this->assertEquals($actual,$form);


        //TEST INSERT
        $_POST['name'] = 'Test Person';
        $_POST['age'] = 15;
        $id = $this->Form->from('PersonModelTest')->post();
        $result = $dbTest->DB->query('SELECT name,age FROM discophp_test_person WHERE person_id=?',$id);
        $this->assertEquals(1,$result->num_rows);

        //TEST UPDATE 
        $_POST['person_id'] = $id;
        $_POST['name'] = 'Test Person1';
        $this->Form->from('PersonModelTest')->post();
        $row = $dbTest->DB->query('SELECT name FROM discophp_test_person WHERE person_id=?',$id)->fetch_assoc();
        $this->assertEquals('Test Person1',$row['name']);


        //TEST SELECT MENU
        $data = Array('r'=>'red','g'=>'green');
        $select = $this->Form->selectMenu($data,'color','g');
        $actual = '<select name="color" ><option value="r" >red</option><option value="g" selected="selected" >green</option></select>';
        $this->assertEquals($actual,$select);

        $data = $dbTest->DB->query('SELECT person_id AS option_value,name AS option_text FROM discophp_test_person ORDER BY person_id LIMIT 2');
        $select = $this->Form->selectMenu($data,'names');
        $actual = '<select name="names" ><option value="1" >Person One</option><option value="2" >Person Two</option></select>';
        $this->assertEquals($actual,$select);


        //TEST RADIO BUTTONS 
        $data = Array('r'=>'red','g'=>'green');
        $radio = $this->Form->radioButtons($data,'color','g');
        $actual = '<label>red<input name="color" value="r" type="radio" /></label><label>green<input name="color" value="g" type="radio" checked="checked" /></label>';
        $this->assertEquals($actual,$radio);

        $data = $dbTest->DB->query('SELECT person_id AS button_value,name AS button_text FROM discophp_test_person ORDER BY person_id LIMIT 2');
        $radio = $this->Form->radioButtons($data,'names');
        $actual = '<label>Person One<input name="names" value="1" type="radio" /></label><label>Person Two<input name="names" value="2" type="radio" /></label>';
        $this->assertEquals($actual,$radio);



        $dbTest->tearDown();

    }//testFromModel

}//CacheTest
