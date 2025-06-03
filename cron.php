<?php
file_put_contents(__DIR__ . '/cron_log.txt', date('Y-m-d H:i:s') . " - cron.php ran\n", FILE_APPEND);
require_once 'functions.php';
sendXKCDUpdatesToSubscribers();