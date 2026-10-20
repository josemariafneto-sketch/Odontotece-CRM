<?php
namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Fornecedor;
use App\Models\Itens_compra;
use App\Models\ItensVenda;
use App\Models\Vendas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compra;

class Itens_vendaController extends Controller
{

    function confirmarsaida($id)
    {
        $vendaControle = new VendasController();
        //verificar se ja nao foi feito a entrada do produto.
        $ent = ItensVenda::find($id);
        if ($ent->status != 'Saída realizada') {
            $ent->status = 'Saída realizada';
            $ent->save();
            $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];

            //atualizar dados do produto
            $this->atualizaDadosProduto($ent->id);

            //verifica se existe algum item que esta aguandado a saida, se nao
            //setar a venda como finalizada
            if(!$this->possueItemAguardadoSaida($ent->venda_id)){
                $msg = ['valor' => trans("Operação realizada com sucesso! Da venda selecionada não existe mais item a ser dado saída."), 'tipo' => 'success'];
                $venda = Vendas::find($ent->venda_id);
                $venda->status = 'Finalizada';
                $venda->save();
                return $vendaControle->listsaida($msg);
            }

            $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        } else {
            $msg = ['valor' => trans("Operação não realizada. O item já foi dada a saída."), 'tipo' => 'primary'];
        }
        //aqui confirmar saida
        return $vendaControle->confirmarsaida($ent->venda_id,$msg);
    }

    function atualizaDadosProduto($item_venda_id)
    {
        $item = ItensVenda::find($item_venda_id);
        $anuncio = Anuncio::find($item->anuncio_id);
        $anuncio->quantidade =  $anuncio->quantidade-$item->quantidade;
        $anuncio->save();
    }

    function possueItemAguardadoSaida($venda_id)
    {
        $itens = ItensVenda::
        where('venda_id', '=', $venda_id)->get();
        foreach ($itens as $ent)
        {
            if ($ent->status != 'Saída realizada') {
                return true;
            }
        }
        return false;

    }

} ?>
