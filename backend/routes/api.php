<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ClienteController;

Route::apiResource('empresas', EmpresaController::class);
Route::apiResource('funcionarios', FuncionarioController::class);
Route::apiResource('clientes', ClienteController::class);
