<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itens_compra extends Model
{
    use HasFactory;
    protected $fillable = ['compra_id','produto_id','status','quantidade',
        'valor_unitario','icms','ipi','frete','icms_pago_fabricante','cartao_12x',
        'comissao_vendedor','margem_lucro','custo_total','entrega_instalacao',
        'valor_venda','preco_venda_sugerido','simples'];

} ?>
