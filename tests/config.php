<?php
return [
    'id' => 'sortable-behaviour-test',
    'basePath' => 'tests/app',
    'components' => [
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=codeception_ordering',
            'username' => 'codeception',
            'password' => 'codeception',
        ],
    ],
];