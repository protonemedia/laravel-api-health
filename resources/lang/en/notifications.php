<?php

return [

    'checker_failed_subject'   => 'Failed checker at :application_name',
    'checker_failed_type'      => 'One of your checker has failed: :checker_type.',
    'checker_failed_at'        => 'The first time it failed was at :failed_at. So far the total number of failures (in succession) is :failure_count.',
    'checker_failed_exception' => 'The exception message: :exception_message',

    'checker_recovered_subject'   => 'Recovered checker at :application_name',
    'checker_recovered_type'      => 'One of your checker has recovered: :checker_type.',
    'checker_recovered_failed_at' => 'It was in a failed state since :failed_at.',
    'checker_recovered_exception' => 'The previous exception message at the time it failed: :exception_message',

];
