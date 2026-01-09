<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'login', 'password', 'nome', 'cpf', 'email', 'endereco', 'documento_path'
    ];

    protected $hidden = ['password'];

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'cliente_empresa');
    }
}
