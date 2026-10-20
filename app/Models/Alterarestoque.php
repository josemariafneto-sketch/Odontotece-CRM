<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alterarestoque extends Model
{
  use HasFactory;
  protected $fillable = ['quantidade', 'tipo', 'motivo', 'anuncios_id'];
} ?>