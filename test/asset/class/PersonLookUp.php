<?php
class PersonLookUp extends \Disco\classes\AbstractLookUp {

    protected $fields = Array(
        'person_id' => 'p.person_id',
        'name' => 'p.name',
        'age' => 'p.age',
        'email' => 'e.email',        
    );

    protected $searchableFields = Array('p.name','e.email');

    protected $Model = 'PersonModelTest';


    protected function preFetch(){
        $this->Model
            ->alias('p')
            ->ljoin('PersonEmailModelTest AS e','e.person_id=p.person_id');
    }//preFetch

}//PersonLookUp
