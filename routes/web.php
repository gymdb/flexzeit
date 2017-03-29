<?php

Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('loginTarget');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/student', 'HomeController@index')->name('student');
Route::get('/teacher', 'HomeController@index')->name('teacher');
