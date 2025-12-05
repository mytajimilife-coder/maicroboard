<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // mb1_write_free 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS mb1_write_free (
        `wr_id` int(11) NOT NULL AUTO_INCREMENT,
        `wr_subject` varchar(255) NOT NULL,
        `wr_content` longtext NOT NULL,
        `wr_name` varchar(50) NOT NULL,
        `wr_datetime` datetime NOT NULL,
        `wr_hit` int(11) NOT NULL DEFAULT 0,
        `wr_comment` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`wr_id`),
        KEY `wr_name` (`wr_name`),
        KEY `wr_datetime` (`wr_datetime`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // mb1_comment_free 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS mb1_comment_free (
        `co_id` int(11) NOT NULL AUTO_INCREMENT,
        `wr_id` int(11) NOT NULL,
        `co_content` text NOT NULL,
        `co_name` varchar(50) NOT NULL,
        `co_datetime` datetime NOT NULL,
        PRIMARY KEY (`co_id`),
        KEY `wr_id` (`wr_id`),
        KEY `co_name` (`co_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // mb1_board_file_free 테이블 생성
    $db->exec("CREATE TABLE IF NOT EXISTS mb1_board_file_free (
        `bf_no` int(11) NOT NULL AUTO_INCREMENT,
        `wr_id` int(11) NOT NULL,
        `bf_source` varchar(255) NOT NULL,
        `bf_file` varchar(255) NOT NULL,
        `bf_download` int(11) NOT NULL DEFAULT 0,
        `bf_content` text,
        `bf_filesize` int(11) NOT NULL DEFAULT 0,
        `bf_datetime` datetime NOT NULL,
        PRIMARY KEY (`bf_no`),
        KEY `wr_id` (`wr_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Free board tables created successfully.";

} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
