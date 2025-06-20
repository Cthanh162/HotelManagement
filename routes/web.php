<?php

use App\Http\Controllers\PandoraController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
Route::get('/', [PandoraController::class, 'index']);

if (Config::get('app.debug')) {
    Route::get('/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
}

if (Config::get('pandora.api_doc.display_swagger_ui')) {
    Route::get('/swagger-ui', function () {
        return view('swagger.index');
    });
}

if (Config::get('pandora.api_doc.display_redoc')) {
    Route::get('/redoc', function () {
        return view('openapi-spec.redoc');
    });
}

if (Config::get('pandora.api_doc.display_swagger_ui')) {
    Route::get('/swagger-ui', function () {
        return view('openapi-spec.swagger');
    });
}
Route::get('/video/{filename}', function ($filename) {
    $path = 'rooms/videos/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(storage_path('app/public/' . $path), [
        'Content-Type' => 'video/mp4',
        'Access-Control-Allow-Origin' => '*', // CORS Header
        'Access-Control-Expose-Headers' => 'Content-Type',
    ]);
});
