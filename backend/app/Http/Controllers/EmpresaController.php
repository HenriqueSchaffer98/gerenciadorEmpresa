<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        return EmpresaResource::collection(Empresa::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string',
            'cnpj' => 'required|string|unique:empresas',
            'endereco' => 'required|string',
        ]);

        $empresa = Empresa::create($validated);

        return new EmpresaResource($empresa);
    }

    public function show($id)
    {
        $empresa = Empresa::with(['funcionarios', 'clientes'])->findOrFail($id);
        return new EmpresaResource($empresa);
    }

    public function update(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'string',
            'cnpj' => 'string|unique:empresas,cnpj,' . $id,
            'endereco' => 'string',
            'funcionarios' => 'array',
            'funcionarios.*' => 'exists:funcionarios,id',
            'clientes' => 'array',
            'clientes.*' => 'exists:clientes,id',
        ]);

        $empresa->update($request->only(['nome', 'cnpj', 'endereco']));

        if ($request->has('funcionarios')) {
            $empresa->funcionarios()->sync($request->funcionarios);
        }

        if ($request->has('clientes')) {
            $empresa->clientes()->sync($request->clientes);
        }

        return new EmpresaResource($empresa->load(['funcionarios', 'clientes']));
    }

    public function destroy($id)
    {
        Empresa::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
