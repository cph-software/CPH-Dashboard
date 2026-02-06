<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-menu', function () {
    $menus = \App\Models\Menu::all();
    dd($menus->toArray());
});
