<?php

use Illuminate\Support\Facades\Route;

Route::post('/artisan/{command}', 'ArtisanController@runArtisan');
