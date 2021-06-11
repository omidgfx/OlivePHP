<?php

\Olive\Routing\Route::get('/', [\App\Controllers\Home::class, 'hello_world']);
\Olive\Routing\Route::get('/hom', [\App\Controllers\Home::class, 'hello_world']);
