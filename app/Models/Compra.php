<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Compra extends Model{
  use HasFactory;
  protected $fillable = ['fornecedor_id','total_compra','chave','numero_nota','serie','data_emissao','data_saida','data_entrada','protocolo_autorizacao','tipo_operacao','natureza_operacao','status'];
} ?>
