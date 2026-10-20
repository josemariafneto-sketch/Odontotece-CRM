<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Cliente extends Model{
    use HasFactory;
    protected $fillable = ['nome','cpf','telefone','email','rua','numero','bairro','cidade','loja_id','tipo','cep','cnpj','rg','inscricao_estadual','razao_social','estado','cro','universidade','ano_formacao','escolaridade'];
} ?>
