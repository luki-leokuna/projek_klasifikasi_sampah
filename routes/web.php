<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SampahController;

Route::get('/', [SampahController::class, 'index']);
Route::post('/cek-sampah', [SampahController::class, 'cekSampah'])->name('cek.sampah');