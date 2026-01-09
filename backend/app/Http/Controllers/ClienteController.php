<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    public function index()
    {
        return ClienteResource::collection(Cliente::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|unique:clientes',
            'password' => 'required|string|min:6',
            'nome' => 'required|string',
            'cpf' => 'required|string|unique:clientes',
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

        $cliente = Cliente::create($validated);

        if ($request->has('empresas')) {
            $cliente->empresas()->sync($request->empresas);
        }

        return new ClienteResource($cliente);
    }

    public function show($id)
    {
        $cliente = Cliente::with('empresas')->findOrFail($id);
        return new ClienteResource($cliente);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'login' => 'string|unique:clientes,login,' . $id,
            'password' => 'string|min:6',
            'nome' => 'string',
            'cpf' => 'string|unique:clientes,cpf,' . $id,
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
            if ($cliente->documento_path) {
                Storage::disk('public')->delete($cliente->documento_path);
            }
            $validated['documento_path'] = $request->file('documento')->store('documentos', 'public');
        }

        $cliente->update($validated);

        if ($request->has('empresas')) {
            $cliente->empresas()->sync($request->empresas);
        }

        return new ClienteResource($cliente->load('empresas'));
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        
        if ($cliente->documento_path) {
            Storage::disk('public')->delete($cliente->documento_path);
        }

        $cliente->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
