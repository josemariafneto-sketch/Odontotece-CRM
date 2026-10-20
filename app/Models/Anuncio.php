<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_anuncio','titulo','descricao','preco','quantidade','ativo','destaque',
        'user_id','type_id','altura','comprimento','largura','peso','color_id','hashtag',
        'margem_lucro','entrega_instalacao','comissao_vendedor','cartao_12x',
        'simples','icms','icms_pago_fabrica','ipi','custo_total','preco_compra','frete','brinde',
        'desconto_maximo','preco_venda_sugerido'
    ];

    protected $table='anuncios';
}
