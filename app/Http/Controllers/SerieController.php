<?php
namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\ItensVenda;
use App\Models\Serie;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Itens_compra;

class SerieController extends Controller
{

    function listsaida($itens_venda_id, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $itens_venda = ItensVenda::find($itens_venda_id);
        $lista = Serie::where('produto_id', '=', $itens_venda->anuncio_id)->
        where('status', '!=', 'Vendido')->
        orderBy('created_at', 'asc')->get();
        $produto = Anuncio::find($itens_venda->anuncio_id);
        return view('serie/listsaida', ['lista' => $lista, 'filtro' => $filter, 'produto' => $produto, 'itens_venda' => $itens_venda, 'msg' => $msg]);
    }

    function listGerenciar($itens_compra_id, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Serie::where('itens_compra_id', '=', $itens_compra_id)->orderBy('id', 'desc')->paginate(10);
        $itens_compra = itens_compra::find($itens_compra_id);
        $produto = Anuncio::find($itens_compra->produto_id);
        $botaovoltarcompras = true;
        return view('serie/list', ['lista' => $lista, 'botaovoltarcompras'=>$botaovoltarcompras,  'filtro' => $filter, 'produto' => $produto, 'itens_compra' => $itens_compra, 'msg' => $msg]);
    }

    function list($itens_compra_id, $msg = null,$botaovoltarcompras=null)
    {
     //   dd($request);
    //    dd($botaovoltarcompras);
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Serie::where('itens_compra_id', '=', $itens_compra_id)->orderBy('id', 'desc')->paginate(10);
        $itens_compra = itens_compra::find($itens_compra_id);
        $produto = Anuncio::find($itens_compra->produto_id);
        $botaovoltarcompras = false;
        return view('serie/list', ['lista' => $lista,'botaovoltarcompras'=>$botaovoltarcompras, 'filtro' => $filter, 'produto' => $produto, 'itens_compra' => $itens_compra, 'msg' => $msg]);
    }

    function new($itens_compra_id,$botaovoltarcompras=null)
    {
     //   dd($botaovoltarcompras);
        $itens_compra = Itens_compra::find($itens_compra_id);
        return view('serie/form', ['entidade' => new Serie(), 'botaovoltarcompras'=>$botaovoltarcompras, 'itens_compra' => $itens_compra]);
    }

    function search(Request $request, $itens_compra_id)
    {
        $itens_compra = Itens_compra::find($itens_compra_id);
        $filter = $request->query('filtro');
        $lista = Serie::where('nome', 'like', "%" . $request->filtro . "%")->orderBy('id', 'desc')->paginate(10);
        return view('serie/list', ['lista' => $lista, 'filtro' => $request->filtro, 'itens_compra' => $itens_compra])->with('filter', $filter);
    }

    function savesaida(Request $request)
    {
        $item_venda = ItensVenda::find($request->itens_venda_id);
        if ($request->serie) {
            foreach ($request->serie as $idSerie) {
              //  dd($request->itens_venda_id);
                $ent = Serie::find($idSerie);
                $ent->status = 'Vendido';
                $ent->itens_vendas_id = $request->itens_venda_id;
                $ent->save();
            }
            $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        }else{
            $msg = ['valor' => trans("Não existe número de série selecionada!"), 'tipo' => 'success'];
        }
        $venda = new VendasController();
        return $venda->confirmarsaida($item_venda->venda_id,$msg);
    }

    function savemanage(Request $request,$botaovoltarcompras=null)
    {
        $botaovoltarcompras = true;
        $itens_compra_id = $request->itens_compra_id;
        $itens_compra = Itens_compra::find($itens_compra_id);
        if ($request->id) {
            $ent = Serie::find($request->id);
            $ent->serie = $request->serie;
            $ent->produto_id = $itens_compra->produto_id;
            $ent->itens_compra_id = $itens_compra_id;
            $ent->save();
        } else {
            $entidade = Serie::create([
                'serie' => $request->serie,
                'produto_id' => $itens_compra->produto_id,
                'status' => 'No estoque',
                'itens_compra_id' => $itens_compra_id]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->listGerenciar($itens_compra_id, $msg);
    }


    function save(Request $request,$botaovoltarcompras=null)
    {
       // dd($botaovoltarcompras);
        $itens_compra_id = $request->itens_compra_id;
        $itens_compra = Itens_compra::find($itens_compra_id);
        if ($request->id) {
            $ent = Serie::find($request->id);
            $ent->serie = $request->serie;
            $ent->produto_id = $itens_compra->produto_id;
            $ent->itens_compra_id = $itens_compra_id;
            $ent->save();
        } else {
            $entidade = Serie::create([
                'serie' => $request->serie,
                'produto_id' => $itens_compra->produto_id,
                'status' => 'No estoque',
                'itens_compra_id' => $itens_compra_id]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($itens_compra_id, $msg,$botaovoltarcompras);
    }

    function deletemanage($id)
    {
        $itens_compra_id = 0;
        try {
            $entidade = Serie::find($id);
            if ($entidade) {
                $itens_compra_id = $entidade->itens_compra_id;
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->listGerenciar($itens_compra_id, $msg);
    }

    function delete($id)
    {
        $itens_compra_id = 0;
        try {
            $entidade = Serie::find($id);
            if ($entidade) {
                $itens_compra_id = $entidade->itens_compra_id;
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($itens_compra_id, $msg);
    }


    function edit($id)
    {
        $entidade = Serie::find($id);
        $itens_compra_id = $entidade->itens_compra_id;
        $itens_compra = Itens_compra::find($itens_compra_id);
        $botaovoltarcompras = false;
        return view('serie/form', ['entidade' => $entidade,'botaovoltarcompras'=>$botaovoltarcompras, 'itens_compra' => $itens_compra]);
    }

    function editmanage($id)
    {
        $entidade = Serie::find($id);
        $itens_compra_id = $entidade->itens_compra_id;
        $itens_compra = Itens_compra::find($itens_compra_id);
        $botaovoltarcompras = true;
        return view('serie/form', ['entidade' => $entidade,'botaovoltarcompras'=>$botaovoltarcompras, 'itens_compra' => $itens_compra]);
    }

} ?>
