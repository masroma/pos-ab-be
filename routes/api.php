<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//group route with prefix "admin"
Route::prefix('admin')->group(function () {

    //route login
    Route::post('/login', [App\Http\Controllers\Api\LoginController::class, 'index', ['as' => 'admin']]);

    //group route with middleware "auth:api_admin"
    Route::group(['middleware' => 'auth:api_admin'], function() {
        Route::get('dashboard', App\Http\Controllers\Api\DashboardController::class)->name('dashboard');

        Route::post('/updateprofile', [App\Http\Controllers\Api\LoginController::class, 'updateProfile', ['as' => 'admin']]);

        Route::post('/updatepassword', [App\Http\Controllers\Api\LoginController::class, 'updatePassword', ['as' => 'admin']]);
        //data user
        Route::get('/user', [App\Http\Controllers\Api\LoginController::class, 'getUser', ['as' => 'admin']]);

        //refresh token JWT
        Route::get('/refresh', [App\Http\Controllers\Api\LoginController::class, 'refreshToken', ['as' => 'admin']]);

        //logout
        Route::post('/logout', [App\Http\Controllers\Api\LoginController::class, 'logout', ['as' => 'admin']]);

        Route::get('/permissions', \App\Http\Controllers\Api\PermissionController::class)->name('permissions.index');

        Route::resource('/roles', \App\Http\Controllers\Api\RoleController::class, ['as' => 'apps']);

        Route::resource('/users', \App\Http\Controllers\Api\UserController::class, ['as' => 'apps'])
        ->middleware('permission:users.index|users.create|users.edit|users.delete');

        //route resource categories
        Route::resource('/categories', \App\Http\Controllers\Api\CategoryController::class, ['as' => 'apps'])->middleware('permission:categories.index|categories.create|categories.edit|categories.delete');  

        Route::resource('/products', \App\Http\Controllers\Api\ProductController::class, ['as' => 'apps'])
        ->middleware('permission:products.index|products.create|products.edit|products.delete');

        Route::get('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index'])->name('apps.transactions.index');

        //route transaction searchProduct
        Route::post('/transactions/searchProduct', [\App\Http\Controllers\Api\TransactionController::class, 'searchProduct'])->name('apps.transactions.searchProduct');
        
        //route transaction addToCart
        Route::post('/transactions/addToCart', [\App\Http\Controllers\Api\TransactionController::class, 'addToCart'])->name('apps.transactions.addToCart');

        //route transaction destroyCart
        Route::post('/transactions/destroyCart', [\App\Http\Controllers\Api\TransactionController::class, 'destroyCart'])->name('apps.transactions.destroyCart');

        //route transaction store
        Route::post('/transactions/store', [\App\Http\Controllers\Api\TransactionController::class, 'store'])->name('apps.transactions.store');

        //route transaction print
        Route::get('/transactions/print', [\App\Http\Controllers\Api\TransactionController::class, 'print'])->name('apps.transactions.print');
    
    });

});
