<?php


Route::get('oauth2/login', 'OAuth2Controller@index')->name('oauth2.login');
Route::get('oauth2/success', 'OAuth2Controller@success')->name('oauth2.success');