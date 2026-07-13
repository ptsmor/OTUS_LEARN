<?php

use App\Debug\Log;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

Log::cleanExceptionLog();

LocalRedirect('/homeworks/homework2/');
