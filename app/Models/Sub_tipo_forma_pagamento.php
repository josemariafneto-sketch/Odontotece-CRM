<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Sub_tipo_forma_pagamento extends Model{
  use HasFactory;
  protected $fillable = ['descricao','forma_pagamento_id'];
} ?>
