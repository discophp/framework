<?php

Class PersonRecordTest extends \Disco\classes\Record {

    protected $model = 'PersonModelTest';

    protected $fieldDefinitions = Array(
        'person_id' => Array(
            'null' => false,
            'type' => 'int',
            'length' => 11,
        ),
        'name' => Array(
            'null' => false,
            'type' => 'varchar',
            'length' => 120,
        ),
        'age' => Array(
            'null' => true,
            'type' => 'int',
            'length' => 11,
        ),
    );

    protected $autoIncrementField = 'person_id';

}//PersonRecordTest
