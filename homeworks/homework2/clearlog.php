<?php

use App\Debug\Log;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

Log::cleanLog();

LocalRedirect('/homeworks/homework2/');
