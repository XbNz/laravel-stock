<?php

return [
    \Domain\Browser\PythonUndetectedChrome::class => [
        'path_to_python_executable' => env('PATH_TO_PYTHON_EXECUTABLE', 'python3'),
        'path_to_python_undetected_chrome' => env('PATH_TO_PYTHON_UNDETECTED_CHROME_SCRIPT'),
    ]
];
