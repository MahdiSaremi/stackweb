<?php

use Illuminate\Support\Facades\Route;
use StackWeb\StackWeb;

Route::get('/', function () {
    return StackWeb::responsePage('Workbench::Test');
});
