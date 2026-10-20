<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Anuncio;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Forma_pagamento;
use App\Models\Fornecedor;
use App\Models\Itens_compra;
use App\Models\ItensVenda;
use App\Models\Notificacoes;
use App\Models\Projeto;
use App\Models\Reparticao;
use App\Models\Serie;
use App\Models\Sub_tipo_forma_pagamento;
use App\Models\User;
use App\Models\Vendas;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendasController extends Controller
{

    function listorcamentodeshboad($msg = null)
    {
        $filter = "";
        $inicio_data = null;
        $final_data = null;
        $status = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        if (isset($_GET['inicio_data'])) {
            if ($_GET['inicio_data'] !="") {
                $inicio_data = $_GET['inicio_data'];
            }
        }
        if (isset($_GET['final_data'])) {
            if ($_GET['final_data'] !="") {
                $final_data = $_GET['final_data'];
            }
        }
        if (isset($_GET['status'])) {
            if ($_GET['status'] !="") {
                $status = $_GET['status'];
            }
        }

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
            dd($status);
        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        } else {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
            //  dd($lista);
        }
        return view('orcamento/list', ['lista' => $lista,'status'=>$status, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);

    }

    function list($msg = null)
    {

        /*

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'itens_vendas.anuncio_id')
            ->join('tamanhos', 'tamanhos.id', '=', 'itens_vendas.tamanho')
            ->join('vendas', 'vendas.id', '=', 'itens_vendas.venda_id')->where('vendedor_id', '=', Auth::user()->id)
            ->orderBy('vendas.created_at', 'desc')
            ->select(['vendas.id_venda', 'destaque', 'itens_vendas.id', 'titulo', 'itens_vendas.quantidade', 'preco_item', 'tamanhos.descricao', 'data_pagamento', 'data_envio'])->get();
        return view("sales/lista", ['anuncios' => $lista, 'msg' => $msg]);
        */
        // dd(5);

        $filter = "";
        $inicio_data = null;
        $final_data = null;
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        if (isset($_GET['inicio_data'])) {
            if ($_GET['inicio_data'] !="") {
                $inicio_data = $_GET['inicio_data'];
            }
        }
        if (isset($_GET['final_data'])) {
            if ($_GET['final_data'] !="") {
                $final_data = $_GET['final_data'];
            }
        }

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '>=', $inicio_data)->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
            //  dd($lista);
        }
        return view('venda/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);

    }

    function searchdashboardorcamento(Request $request)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = $request->query('inicio_data');
        $final_data = $request->query('final_data');
        $status =  $request->query('status');

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            return $this->listorcamentodeshboad();
        }
        $msg = null;// ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return view('orcamento/list', ['lista' => $lista, 'status'=>$status,'filtro' => $filter, 'msg' => $msg, 'inicio_data' => $inicio_data, 'final_data' => $final_data]);
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
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '>=', $inicio_data)->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);


        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            return $this->list();
        }
        $msg = null;// ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return view('venda/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'inicio_data' => $inicio_data, 'final_data' => $final_data]);
    }

    function searchFrenteVenda(Request $request)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = $request->query('inicio_data');
        $final_data = $request->query('final_data');
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];


        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);


        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {

            return $this->listVendasFrente($msg);
        }
        return view('frente/venda', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);
    }

    function searchFrenteOrcamento(Request $request)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = $request->query('inicio_data');
        $final_data = $request->query('final_data');

        $status = $request->query('statusPesquisa');
        if (!isset($status)) {
            $status = "";
        }

        //dd($request);
        $msg = null;// ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);


        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('clientes.nome','like',"%".$filter."%")->
            where('vendas.status','like',"%".$status."%")->
            where('vendas.updated_at', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id','users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            //  where('clientes.nome', 'like', "%" . $filter . "%")->
            where('vendas.comprador_id', '=', Auth::user()->id)->
            where('vendas.tipo', '=', 'orcamento')->
            where('vendas.status','like',"%".$status."%")->
            where('clientes.nome','like',"%".$filter."%")->
            orderBy('vendas.updated_at', 'desc')->
            select('vendas.id','vendas.validade_orcamento', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status')->paginate(10);
        }


        return view('frente/index', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);
    }

    function delete($id)
    {
        try {
            $entidade = Vendas::find($id);
            //  dd($entidade);
            if ($entidade) {
                if (!($entidade->status === "Finalizada")) {
                    //deletando os itens da venda
                    $lista = ItensVenda::where('venda_id', '=', $id)->orderBy('id', 'asc')->get();
                    foreach ($lista as $ent) {
                        $ent->delete();
                    }
                    $entidade->delete();
                    $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];                   
                } else {
                    //deletando os itens da venda ja finailizada
                    $lista = ItensVenda::where('venda_id', '=', $id)->orderBy('id', 'asc')->get();
                    foreach ($lista as $ent) {
                        //aqui retornar a quantidade para o estoque
                        $anuncio = Anuncio::find($ent->anuncio_id);
                        $anuncio->quantidade =  $anuncio->quantidade+$ent->quantidade;
                        $anuncio->save();
                        //atulaziar os numeros de series
                        $listaNumeroSeries = Serie::where('itens_vendas_id', '=', $ent->id)->
                        where('status', '=', 'Vendido')->get();
                        foreach ($listaNumeroSeries as $serie) {
                            $serie->status = "No estoque";
                            $serie->itens_vendas_id = null;
                            $serie->save();
                        }
                        //deletar os itens
                        $ent->delete();
                    }
                    $entidade->delete();
                    $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
                }
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (\Illuminate\Database\QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($msg);
    }

    //funcao para entrada do produto no estoque
    function listsaida($msg = null)
    {
        $status = "Finalizada";
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
        where('vendas.tipo', '=', 'venda')->
        where('vendas.status', '!=', $status)->
        orderBy('vendas.id', 'desc')->
        select('vendas.id', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        return view('estoque/listsaida', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg]);
    }

    function confirmarsaida($id, $msg = null)
    {
        $venda = Vendas::find($id);
        //  dd($venda);
        $cliente = Cliente::find($venda->cliente_id);
        $itens_venda = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id', 'anuncios.titulo as titulo', 'itens_vendas.quantidade', 'itens_vendas.preco_item', 'itens_vendas.desconto', 'itens_vendas.status')->get();
        return view('estoque/formsaida', ['entidade' => $venda, 'cliente' => $cliente, 'msg' => $msg, 'lista' => $itens_venda]);
    }

    function send($id)
    {

        $anuncio = ItensVenda::join('anuncios', 'anuncios.id', '=', 'itens_vendas.anuncio_id')
            ->join('tamanhos', 'tamanhos.id', '=', 'itens_vendas.tamanho')
            ->join('vendas', 'vendas.id', '=', 'itens_vendas.venda_id')
            ->join('enderecos', 'enderecos.id', '=', 'vendas.endereco_id')
            ->join('users', 'users.id', '=', 'vendas.comprador_id')->where('vendedor_id', '=', Auth::user()->id)->where('itens_vendas.id', '=', $id)
            ->select(['destaque', 'itens_vendas.id as id', 'titulo',
                'itens_vendas.quantidade', 'preco_item'
                , 'tamanhos.descricao', 'data_pago', 'enderecos.rua', 'enderecos.bairro', 'enderecos.cidade', 'enderecos.cep', 'users.name', 'data_envio'])->first();
        //   dd($anuncio);

        return view('sales/form', ['obj' => $anuncio, 'id' => $id]);

    }

    function sendDo($id)
    {
        $msgret = ['valor' => "Operação realizada com sucesso!", 'tipo' => 'success'];
        try {
            $anu = ItensVenda::find($id);

            $venda = Vendas::find($anu->venda_id);
            $anuncio = Anuncio::find($anu->anuncio_id);

            $comprador = User::find($venda->comprador_id);

            $anu->data_envio = $date = date('Y-m-d H:i:s');
            $anu->save();
            $descricao = "O item " . $anuncio->titulo . ' do seu pedido ' . $venda->id_venda . ' foi enviado ';


            $notificaoComprador = new Notificacoes();


            $notificaoComprador->descricao = $descricao;
            $notificaoComprador->id_user = $venda->comprador_id;
            $notificaoComprador->id_venda = $id;
            $notificaoComprador->id_anuncio = $anu->anuncio_id;
            $notificaoComprador->save();

            Helper::sendEmail("Produtos enviados", $descricao, $comprador->email, $comprador->name);

        } catch (QueryException $exception) {
            $msgret = ['valor' => "Erro ao executar a operação", 'tipo' => 'danger'];
        }

        return $this->list($msgret);

    }

    function listIndexFrente($msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = "";
        if (isset($_GET['inicio_data'])) {
            $inicio_data = $_GET['inicio_data'];
        }
        $final_data = "";
        if (isset($_GET['final_data'])) {
            $final_data = $_GET['final_data'];
        }
        $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
        //  where('clientes.nome', 'like', "%" . $filter . "%")->
        where('vendas.comprador_id', '=', Auth::user()->id)->
        where('vendas.tipo', '=', 'orcamento')->
        where('clientes.nome','like',"%".$filter."%")->
        orderBy('vendas.updated_at', 'desc')->
        select('vendas.id','vendas.validade_orcamento', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status')->paginate(10);
        //  dd($lista);
        return view('frente/index', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);
    }

    function listVendasFrente($msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $inicio_data = "";
        if (isset($_GET['inicio_data'])) {
            $inicio_data = $_GET['inicio_data'];
        }
        $final_data = "";
        if (isset($_GET['final_data'])) {
            $final_data = $_GET['final_data'];
        }
        $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
        //  where('clientes.nome', 'like', "%" . $filter . "%")->
        where('vendas.comprador_id', '=', Auth::user()->id)->
        where('vendas.tipo', '=', 'venda')->
        where('clientes.nome','like',"%".$filter."%")->
        orderBy('vendas.data_venda', 'desc')->
        select('vendas.id', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        //  dd($lista);
        return view('frente/venda', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);
    }

    function deleteOrcamento($id)
    {
        try {
            $entidade = Vendas::find($id);
            if ($entidade) {
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (\Illuminate\Database\QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->listIndexFrente($msg);
    }

    function deleteOrcamentodashboard($id)
    {
        try {
            $entidade = Vendas::find($id);
            if ($entidade) {
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (\Illuminate\Database\QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->listorcamentodeshboad($msg);
    }

    function cancelar()
    {
        $anucio = new AnuncioController();
        $anucio->clearCarr();
        return $this->listIndexFrente();
    }

    function editOrcamento(Request $request, $id)
    {
        $venda = Vendas::find($id);
        $anucio = new AnuncioController();
        $anucio->clearCarr();
        $lista = ItensVenda::where('venda_id', '=', $venda->id)->orderBy('id', 'asc')->get();
        // dd($lista);
        foreach ($lista as $ent) {
            // dd($ent);
            $anucio->addSessionEditar($request, $ent->id);
        }

        session(['venda_id' => $venda->id]);
        session(['cliente_id' => $venda->cliente_id]);
        session(['forma_pagamento_id' => $venda->forma_pagamento_id]);
        session(['sub_tipo_forma_pagamento_id' => $venda->sub_tipo_forma_pagamento_id]);

        session(['frete' => $venda->frete]);
        session(['instalacao' => $venda->instalacao]);

        //chama tela de finalizar
        $checkOut = new CheckoutControler();
        return $checkOut->checkout();
    }

    function duplicarorcamento(Request $request, $id)
    {
        $vendaBanco = Vendas::find($id);
        $lista = ItensVenda::where('venda_id', '=', $vendaBanco->id)->orderBy('id', 'asc')->get();
        try {
            $vendaCont = new VendasController();
            $produtos = $lista;
            if (!empty($produtos)) {
                $saida = array();
                $total = 0;
                foreach ($produtos as $pd) {
                    $item = new ItensVenda();
                    $item->quantidade = $pd->quantidade;
                    $item->preco_item = $pd->preco_item;
                    $item->anuncio_id = $pd->anuncio_id;
                    $item->vendedor_id =  $pd->vendedor_id;
                    $item->desconto = $pd->desconto;
                    $item->lucro = $pd->lucro;
                    $item->custo = $pd->custo;
                    $item->preco_compra = $pd->preco_compra;
                    $total = $total + ($item->quantidade * ($item->preco_item-$item->desconto));
                    array_push($saida, $item);
                }

                DB::connection()->beginTransaction();
                $venda = new Vendas();
                $venda->id_venda = uniqid(date('HisYmd'));
                $venda->total = $total;
                $venda->valor = $total;
                $venda->comprador_id = Auth::user()->id;
                $venda->tipo = 'orcamento';
                $venda->status = 'Aguardando aprovação';
                $msg = ['valor' => trans("Orçamento duplicado com sucesso!"), 'tipo' => 'success'];

                $venda->cliente_id = $vendaBanco->cliente_id;
                $venda->forma_pagamento_id = $vendaBanco->forma_pagamento_id;
                $venda->sub_tipo_forma_pagamento_id = $vendaBanco->sub_tipo_forma_pagamento_id;
                $venda->endereco_id = 1;
                $venda->save();

                foreach ($saida as $iten) {
                    $iten->venda_id = $venda->id;
                    $iten->status = 'Em orçamento';
                    $iten->save();
                }
                DB::connection()->commit();
            }
        } catch
        (\Illuminate\Database\QueryException $exception) {
            dd($exception);
        }
        return $vendaCont->listIndexFrente($msg);
    }

    function salvarobservacao(Request $request)
    {
        $venda = Vendas::find($request->venda_idM);
        $venda->observacao = $request->observacaoM;
        $venda->save();
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->listVendasFrente($msg);
    }

    function salvardatavalidadeorcamento(Request $request)
    {
        $venda = Vendas::find($request->venda_idM);
        $venda->validade_orcamento = $request->validade_orcamentoM;
        $venda->save();
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->listIndexFrente($msg);
    }
    function alterastatusorcamento(Request $request)
    {
      //  dd($request);
        $venda = Vendas::find($request->venda_idModalStatus);
        $venda->status = $request->status;
        $venda->save();
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->listIndexFrente($msg);
    }



    function printvenda($id)
    {
        $venda = Vendas::find($id);
        $cliente = Cliente::find($venda->cliente_id);
        $formaPag = Forma_pagamento::find($venda->forma_pagamento_id);
        $subformaPag = Sub_tipo_forma_pagamento::find($venda->sub_tipo_forma_pagamento_id);

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id','itens_vendas.desconto as desconto', 'anuncios.id as produto_id', 'anuncios.titulo as titulo', 'itens_vendas.quantidade', 'itens_vendas.preco_item as valor_unitario')->get();

        return \PDF::loadView('relatorios/printvenda', compact('venda', 'cliente', 'lista', 'formaPag', 'subformaPag'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }

    function printdadosfinanceiroorcamento($id)
    {
        $venda = Vendas::find($id);
        $cliente = Cliente::find($venda->cliente_id);
        $formaPag = Forma_pagamento::find($venda->forma_pagamento_id);
        $subformaPag = Sub_tipo_forma_pagamento::find($venda->sub_tipo_forma_pagamento_id);

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id', 'anuncios.id as produto_id', 'anuncios.titulo as titulo', 'itens_vendas.quantidade', 
        'itens_vendas.preco_item as valor_unitario',
        'itens_vendas.lucro as lucro', 
        'itens_vendas.custo as custo', 
        'itens_vendas.preco_compra as preco_compra', 
        'itens_vendas.desconto as desconto')->get();

        return \PDF::loadView('relatorios/printdadosfinanceiroorcamento', compact('venda', 'cliente', 'lista', 'formaPag', 'subformaPag'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }

    function printdadosfinanceirovenda($id)
    {
        $venda = Vendas::find($id);
        $cliente = Cliente::find($venda->cliente_id);
        $formaPag = Forma_pagamento::find($venda->forma_pagamento_id);
        $subformaPag = Sub_tipo_forma_pagamento::find($venda->sub_tipo_forma_pagamento_id);

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id', 'anuncios.id as produto_id', 
        'anuncios.titulo as titulo', 'itens_vendas.quantidade', 
        'itens_vendas.lucro as lucro', 
        'itens_vendas.custo as custo', 
        'itens_vendas.preco_compra as preco_compra', 
        'itens_vendas.preco_item as valor_unitario', 
        'itens_vendas.desconto as desconto')->get();

        return \PDF::loadView('relatorios/printdadosfinanceirovenda', compact('venda', 'cliente', 'lista', 'formaPag', 'subformaPag'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }

    function printdadosfretevenda($id)
    {
        $venda = Vendas::find($id);
        $cliente = Cliente::find($venda->cliente_id);
        $formaPag = Forma_pagamento::find($venda->forma_pagamento_id);
        $subformaPag = Sub_tipo_forma_pagamento::find($venda->sub_tipo_forma_pagamento_id);

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id', 'anuncios.id as produto_id', 'anuncios.titulo as titulo', 'itens_vendas.quantidade', 'itens_vendas.preco_item as valor_unitario', 'itens_vendas.desconto as desconto')->get();

        return \PDF::loadView('relatorios/printdadosfretevenda', compact('venda', 'cliente', 'lista', 'formaPag', 'subformaPag'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }

    function printorcamento($id)
    {
        $venda = Vendas::find($id);
        $cliente = Cliente::find($venda->cliente_id);
        $formaPag = Forma_pagamento::find($venda->forma_pagamento_id);
        $subformaPag = Sub_tipo_forma_pagamento::find($venda->sub_tipo_forma_pagamento_id);

        $lista = ItensVenda::join('anuncios', 'anuncios.id', '=', 'anuncio_id')->
        where('venda_id', '=', $id)->orderBy('itens_vendas.id', 'desc')->
        select('itens_vendas.id as id', 'anuncios.id as produto_id', 'anuncios.titulo as titulo', 'itens_vendas.quantidade', 'itens_vendas.preco_item as valor_unitario', 'itens_vendas.desconto as desconto')->get();

        return \PDF::loadView('relatorios/printorcamento', compact('venda', 'cliente', 'lista', 'formaPag', 'subformaPag'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }


}
