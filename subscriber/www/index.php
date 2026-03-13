<?php

use App\SubscriberKernel;

require_once dirname(__DIR__, 2).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new SubscriberKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
