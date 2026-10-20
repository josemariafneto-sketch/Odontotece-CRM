<?php
namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Compra;
use App\Models\Custo;
use App\Models\Fornecedor;
use App\Models\Itens_compra;
use App\Models\Projeto;
use App\Models\Reparticao;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{

    //funcos para entrada do produto no estoque
    function listentrada($msg = null)
    {
        $status = "Finalizada";
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
        where('compras.status', '!=', $status)->
        orderBy('compras.id', 'desc')->
        select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status')->paginate(10);
        // $lista = Compra::where('nome','like',"%"."%")->orderBy('id', 'desc')->paginate(10);
        return view('estoque/listentrada', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg]);
    }

    function confirmarentrada($id, $msg = null)
    {
        $compra = Compra::find($id);
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();
        $itens_compra = Itens_compra::join('anuncios', 'anuncios.id', '=', 'produto_id')->
        where('compra_id', '=', $compra->id)->orderBy('itens_compras.id', 'desc')->
        select('itens_compras.id as id', 'anuncios.titulo as titulo', 'itens_compras.quantidade', 'itens_compras.valor_unitario', 'itens_compras.status')->paginate(100);
        return view('estoque/form', ['entidade' => $compra, 'fornecedores' => $fornecedores, 'msg' => $msg, 'produtos' => $produtos, 'lista' => $itens_compra]);
    }

    function gerenciarserie($id, $msg = null)
    {
        $compra = Compra::find($id);
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();
        $itens_compra = Itens_compra::join('anuncios', 'anuncios.id', '=', 'produto_id')->
        where('compra_id', '=', $compra->id)->orderBy('itens_compras.id', 'desc')->
        select('itens_compras.id as id', 'anuncios.titulo as titulo', 'itens_compras.quantidade', 'itens_compras.valor_unitario', 'itens_compras.status')->paginate(100);
        return view('compra/gerenciarseries', ['entidade' => $compra, 'fornecedores' => $fornecedores, 'msg' => $msg, 'produtos' => $produtos, 'lista' => $itens_compra]);
    }


    //funcoes para CRUD compra
    function list($msg = null)
    {
        // dd(5);
        $filter = "";
        $inicio_data = null;
        $final_data = null;
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        if (isset($_GET['inicio_data'])) {
            if ($_GET['inicio_data'] != "") {
                $inicio_data = $_GET['inicio_data'];
            }
        }
        if (isset($_GET['final_data'])) {
            if ($_GET['final_data'] != "") {
                $final_data = $_GET['final_data'];
            }
        }


        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '>=', $inicio_data)->
            where('compras.data_emissao', '<=', $final_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else if (isset($inicio_data)) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '>=', $inicio_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else if (isset($final_data)) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '<=', $final_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        }
        return view('compra/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'inicio_data' => $inicio_data, 'final_data' => $final_data]);
    }

    function new()
    {
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();
        $compra = Compra::create();
        $compra->total_compra = 0;
        $compra->data_emissao = Carbon::now();
        $compra->status = 'Processando';
        $compra->save();
        $itens_compra = Itens_compra::join('anuncios', 'anuncios.id', '=', 'produto_id')->
        where('compra_id', '=', $compra->id)->orderBy('itens_compras.id', 'desc')->
        select('itens_compras.id as id', 'anuncios.titulo as titulo', 'itens_compras.quantidade', 'itens_compras.valor_unitario')->paginate(100);


        return view('compra/form', ['entidade' => $compra,'custos' => Custo::all(), 'fornecedores' => $fornecedores, 'produtos' => $produtos, 'lista' => $itens_compra]);
    }

    function search(Request $request)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = $request->query('inicio_data');
        $final_data = $request->query('final_data');

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '>=', $inicio_data)->
            where('compras.data_emissao', '<=', $final_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else if (isset($inicio_data)) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '>=', $inicio_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else if (isset($final_data)) {
            $lista = Compra::join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
            where('compras.data_emissao', '<=', $final_data)->
            orderBy('compras.id', 'desc')->
            select('compras.id as id', 'fornecedors.nome as nome_fornecedor', 'numero_nota', 'data_emissao', 'status', 'total_compra')->paginate(10);
        } else {
            return $this->list();
        }
        $msg = null;// ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return view('compra/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'inicio_data' => $inicio_data, 'final_data' => $final_data]);
    }

    function save(Request $request)
    {
        // dd($request);
        if ($request->id) {
            $ent = Compra::find($request->id);
            $ent->fornecedor_id = $request->fornecedor_id;
            $ent->chave = $request->chave;
            $ent->numero_nota = $request->numero_nota;
            $ent->serie = $request->serie;
            $ent->data_emissao = $request->data_emissao;
            $ent->data_saida = $request->data_saida;
            $ent->data_entrada = $request->data_entrada;
            $ent->protocolo_autorizacao = $request->protocolo_autorizacao;
            $ent->tipo_operacao = $request->tipo_operacao;
            $ent->natureza_operacao = $request->natureza_operacao;
            //   $ent->status = "Processando";
            $ent->save();
            // dd($request);
        } else {
            $entidade = Compra::create([
                'fornecedor_id' => $request->fornecedor_id,
                'chave' => $request->chave,
                'numero_nota' => $request->numero_nota,
                'serie' => $request->serie,
                'data_emissao' => $request->data_emissao,
                'data_saida' => $request->data_saida,
                'data_entrada' => $request->data_entrada,
                'protocolo_autorizacao' => $request->protocolo_autorizacao,
                'tipo_operacao' => $request->tipo_operacao,
                'natureza_operacao' => $request->natureza_operacao,
                'status' => "Processando"]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($msg);
    }


    function deleteItemCompra($id)
    {
        try {
            $entidade = Itens_compra::find($id);
            $idCompra = $entidade -> compra_id;
            // dd($entidade);
            if ($entidade) {
                $compra = Compra::find($idCompra);
                $compra->total_compra = $compra->total_compra - ($entidade->quantidade * $entidade->valor_unitario);
                $compra->save();
                $entidade->delete();
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => "Erro ao deletar o ítem. ", 'tipo' => 'primary'];
        }
        return $this->edit($idCompra);
    }


    function delete($id)
    {
        try {
            $entidade = Compra::find($id);
            // dd($entidade);
            if ($entidade) {
                if (!($entidade->status === "Finalizada")) {
                    //deletando os itens da compra
                    $lista = Itens_compra::where('compra_id', '=', $id)->orderBy('id', 'desc')->paginate(10);
                    foreach ($lista as $ent) {
                        $ent->delete();
                    }
                    $entidade->delete();
                    $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
                } else {
                    $msg = ['valor' => trans("Operação não realizada. A compra já foi finalizada!"), 'tipo' => 'primary'];
                }
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => "Compra não pode ser deletada pois existe número de série associado a um ítem ", 'tipo' => 'primary'];
        }
        return $this->list($msg);
    }

    function edit($id)
    {
        $compra = Compra::find($id);
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();
        $itens_compra = Itens_compra::join('anuncios', 'anuncios.id', '=', 'produto_id')->
        where('compra_id', '=', $compra->id)->orderBy('itens_compras.id', 'desc')->
        select('itens_compras.id as id', 'anuncios.titulo as titulo', 'itens_compras.quantidade', 'itens_compras.valor_unitario')->paginate(100);
        if (!($compra->status == "Finalizada")) {
            return view('compra/form', ['entidade' => $compra, 'custos' => Custo::all(),'fornecedores' => $fornecedores, 'produtos' => $produtos, 'lista' => $itens_compra]);
        } else {
            $msg = ['valor' => trans("Operação não realizada. A compra já foi finalizada!"), 'tipo' => 'primary'];
            return $this->list($msg);
        }
    }

} ?>
