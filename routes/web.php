<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index']);

Route::get('put', function () {
    Storage::disk('google')->put('images/test.txt', 'Hello World');
    return 'File was saved to Google Drive';
});

Route::get('get', function () {
    $files = Storage::disk('google')->allFiles();
    $urls = [];
    foreach ($files as $file) {
        $urls[] = Storage::disk('google')->url('1712750073.png');
    }

    dd($urls);
});

Route::get('get-fileinfo', function() {
    // there can be duplicate file names!
    $filename = 'images/cooking.png';

    // $rawData = Storage::disk('google')->get($filename); // raw content
    $file = Storage::disk('google')->getAdapter()->getMetadata($filename); // array with file info

    return response($file, 200)
        ->header('ContentType', $file->mimeType())
        ->header('Content-Disposition', "attachment; filename=$filename");
});
