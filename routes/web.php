<?php

use Illuminate\Support\Facades\Route;

// Frontend: 1 trang duy nhất (SPA đơn giản), toàn bộ dữ liệu lấy qua /api/*
Route::get('/', function () {
    return view('app');
});
