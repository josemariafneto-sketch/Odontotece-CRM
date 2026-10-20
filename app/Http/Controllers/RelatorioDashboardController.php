<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Compra;
use App\Models\Itens_compra;
use App\Models\ItensVenda;
use App\Models\Serie;
use App\Models\Vendas;
use Illuminate\Http\Request;

class RelatorioDashboardController extends Controller
{
    function listVendasPorVendedor(Request $request, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }

        $nomecliente = "";
        if (isset($_GET['nomecliente'])) {
            $nomecliente = $_GET['nomecliente'];
        }

        $inicio_data = $request->query('inicio_data');
        $final_data = $request->query('final_data');

        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '>=', $inicio_data)->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);


        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return view('relatoriomenuprincipal/listVendaPorUsuario', ['lista' => $lista, 'nomecliente' => $nomecliente, 'filtro' => $filter, 'final_data' => $final_data, 'inicio_data' => $inicio_data]);
    }

    function printlistVendasPorVendedor(Request $request, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtroImpressao'])) {
            $filter = $_GET['filtroImpressao'];
        }

        $nomecliente = "";
        if (isset($_GET['nomeclienteImpressao'])) {
            $nomecliente = $_GET['nomeclienteImpressao'];
        }

        $inicio_data = $request->query('inicio');
        $final_data = $request->query('final');
        if ((isset($inicio_data)) and (isset($final_data))) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '>=', $inicio_data)->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);
        } else if (isset($inicio_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '>=', $inicio_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);


        } else if (isset($final_data)) {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            where('vendas.data_venda', '<=', $final_data)->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        } else {
            $lista = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
            join('users', 'users.id', '=', 'comprador_id')->
            where('vendas.tipo', '=', 'venda')->
            where('users.name', 'like', "%" . $filter . "%")->
            where('clientes.nome', 'like', "%" . $nomecliente . "%")->
            orderBy('vendas.id', 'desc')->
            select('vendas.id', 'users.name as vendedor', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.data_venda as data', 'valor', 'status', 'vendas.observacao as observacao')->paginate(10);

        }

        return \PDF::loadView('relatorios/printlistvendaporusuario', compact('lista', 'final_data', 'inicio_data'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }

    function listProdutoPorCompra(Request $request, $msg = null)
    {
        $filter = "";
        $produtoSelecionado = null;
        $listaSeries = null;
        if (isset($_GET['produto_id'])) {
            $filter = $_GET['produto_id'];
            $produtoSelecionado = Anuncio::find($filter);

            $listaSeries = Serie::where('produto_id', '=', $filter)->
            where('status', '!=', 'Vendido')->
            orderBy('created_at', 'asc')->get();
        } else {
            //lista vazia
            $listaSeries = Serie::where('produto_id', '=', -1)->
            where('status', '!=', 'Vendido')->
            orderBy('created_at', 'asc')->get();
        }
        //lista vazia
        $lista = Itens_compra::join('compras', 'compras.id', '=', 'compra_id')->
        join('anuncios', 'anuncios.id', '=', 'produto_id')->
        join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
        where('itens_compras.produto_id', '=', $filter)->
        orderBy('compras.created_at', 'desc')->
        select('compras.id', 'itens_compras.quantidade as quantidade', 'itens_compras.valor_unitario as valor_compra',
            'itens_compras.custo_total as valor_custo', 'itens_compras.valor_venda as valor_venda',
            'compras.data_emissao as data', 'compras.status as status', 'fornecedors.nome as nome_fornecedor')->paginate(10);

        $produtos = Anuncio::orderBy('titulo', 'asc')->get();

        return view('relatoriomenuprincipal/listProdutoPorCompra', ['lista' => $lista, 'listaSeries' => $listaSeries, 'produtoSelecionado' => $produtoSelecionado, 'produtos' => $produtos, 'filtro' => $filter, 'msg' => $msg]);
    }

    function pesquisarlistProdutoPorCompra(Request $request, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }


        $listaSeries = Serie::where('produto_id', '=', $request->produto_id)->
        where('status', '!=', 'Vendido')->
        orderBy('created_at', 'asc')->get();


        $lista = Itens_compra::join('compras', 'compras.id', '=', 'compra_id')->
        join('anuncios', 'anuncios.id', '=', 'produto_id')->
        join('fornecedors', 'fornecedors.id', '=', 'fornecedor_id')->
        where('itens_compras.produto_id', '=', $request->produto_id)->
        orderBy('compras.created_at', 'desc')->
        select('compras.id', 'itens_compras.quantidade as quantidade', 'itens_compras.valor_unitario as valor_compra',
            'itens_compras.custo_total as valor_custo', 'itens_compras.valor_venda as valor_venda',
            'compras.data_emissao as data', 'compras.status as status', 'fornecedors.nome as nome_fornecedor')->paginate(10);

        //  dd($lista);
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();

        $produtoSelecionado = Anuncio::find($request->produto_id);

        return view('relatoriomenuprincipal/listProdutoPorCompra', ['lista' => $lista, 'listaSeries' => $listaSeries,'filtro' => $filter, 'produtoSelecionado' => $produtoSelecionado, 'produtos' => $produtos, 'msg' => $msg]);
    }

    function printlistTodosProdutosEstoque( $msg = null)
    {

        $lista = Anuncio::where('ativo','=','1')->get();

        return \PDF::loadView('relatorios/printlistTodosProdutosEstoque', compact('lista'))
            // Se quiser que fique no formato a4 retrato: ->setPaper('a4', 'landscape')
            ->stream();
    }
}

?>
