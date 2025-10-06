<?php

use App\Exports\CategoriesExport;
use App\Http\Controllers\Client\LoginController;
use App\Http\Controllers\Client\RegistrationController;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::post('/test', function (Request $req) {
    return response()->json([
        'code' => 200,
        'message' => 'its work',
        'req_contebt' => $req->getContent()
    ]);
});
Route::get('/authTest',function(){
    return response()->json([
        'code' => 200,
         'message' => 'its work',
    ]);
})->middleware('auth:sanctum');

// Route::post('user',);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sunctum');

Route::post('registration', [RegistrationController::class, 'createUser']);
Route::post('login', [LoginController::class, 'login']);

Route::prefix('excel')->group(function(){
    Route::get('category/{categoryId}',function ($categoryId){
        $filename = 'categories'.$categoryId.'.xlsx';
        return Excel::download(new CategoriesExport($categoryId),$filename,\Maatwebsite\Excel\Excel::XLSX);
    });
    Route::post('product',function(Request $request){
        return response()->json([],200);
    });
});

Route::prefix('shpreed-excel')->group(function(){
    Route::get('category/{categoryId}',function ($categoryId){

    });
});
