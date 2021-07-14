<?php
include_once "../../connection.php";
include_once "../../utilities.php";

$condition = "notification_time < (NOW() - INTERVAL 10 DAY)";
$insertResult = removeRecordFromTable('notification_data', $condition, $isFullCondition = true);

die;