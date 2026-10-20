<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Serie extends Model{
  use HasFactory;
  protected $fillable = ['serie','produto_id','status','itens_compra_id','itens_vendas_id'];
} ?>
