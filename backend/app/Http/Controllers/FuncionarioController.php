<?php

namespace App\Http\Controllers;

use App\Http\Resources\FuncionarioResource;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FuncionarioController extends Controller
{
    public function index()
    {
        return FuncionarioResource::collection(Funcionario::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|unique:funcionarios',
            'password' => 'required|string|min:6',
            'nome' => 'required|string',
            'cpf' => 'required|string|unique:funcionarios',
            'email' => 'required|email',
            'endereco' => 'required|string',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'empresas' => 'array',
            'empresas.*' => 'exists:empresas,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('documento')) {
            $validated['documento_path'] = $request->file('documento')->store('documentos', 'public');
        }

        $funcionario = Funcionario::create($validated);

        if ($request->has('empresas')) {
            $funcionario->empresas()->sync($request->empresas);
        }

        return new FuncionarioResource($funcionario);
    }

    public function show($id)
    {
        $funcionario = Funcionario::with('empresas')->findOrFail($id);
        return new FuncionarioResource($funcionario);
    }

    public function update(Request $request, $id)
    {
        $funcionario = Funcionario::findOrFail($id);

        $validated = $request->validate([
            'login' => 'string|unique:funcionarios,login,' . $id,
            'password' => 'string|min:6',
            'nome' => 'string',
            'cpf' => 'string|unique:funcionarios,cpf,' . $id,
            'email' => 'email',
            'endereco' => 'string',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
            'empresas' => 'array',
            'empresas.*' => 'exists:empresas,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('documento')) {
            if ($funcionario->documento_path) {
                Storage::disk('public')->delete($funcionario->documento_path);
            }
            $validated['documento_path'] = $request->file('documento')->store('documentos', 'public');
        }

        $funcionario->update($validated);

        if ($request->has('empresas')) {
            $funcionario->empresas()->sync($request->empresas);
        }

        return new FuncionarioResource($funcionario->load('empresas'));
    }

    public function destroy($id)
    {
        $funcionario = Funcionario::findOrFail($id);
        
        if ($funcionario->documento_path) {
            Storage::disk('public')->delete($funcionario->documento_path);
        }
        
        $funcionario->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
