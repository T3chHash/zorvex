<?php

return [
    'options' => 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin',
    'create' => <<<SQL
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        remark varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
        SQL,
];
