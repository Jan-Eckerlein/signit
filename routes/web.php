<?php

use App\Http\Requests\SignDocumentRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'api'], function () {
    Route::get('/test-me', function () {
        return response()->json(['message' => 'Hello from Laravel!']);
    });

    Route::post('/sign-document', function (SignDocumentRequest $request) {
        $validated = $request->validated();
        return response()->json(['message' => 'Hello from Laravel!', 'data' => $validated]);
    });
});