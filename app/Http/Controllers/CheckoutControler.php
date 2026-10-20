<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Helpers\PagSeguro;
use App\Models\Anuncio;
use App\Models\Cliente;
use App\Models\Endereco;
use App\Models\Forma_pagamento;
use App\Models\ItensVenda;
use App\Models\Notificacoes;
use App\Models\Projeto;
use App\Models\Sub_tipo_forma_pagamento;
use App\Models\TamanhoVenda;
use App\Models\User;
use App\Models\Vendas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutControler extends Controller
{


    function checkout($msg = null)
    {

        if (Auth::check()) {
            $produtos = session('produtos');
            if ($produtos) {
                $saida = [];
                foreach ($produtos as $pd) {
                    array_push($saida, $pd['id']);
                }

                $frete = Anuncio::whereIn('id', $saida)->select(DB::raw('sum(10) as t, id'))->groupBy('id')->get();
                $total = 0;
                foreach ($frete as $anc) {
                    $total = $total + 1;
                }
                session(['fretes' => $total]);

                $clientes = Cliente::all();
                $FormaPagamento = Forma_pagamento::all();
                $SubFormaPagamento = Sub_tipo_forma_pagamento::all();

                //mandando para a tela os orcamentos em aberto nos ultimos 30 dias para verificar se existe
                //cliente com orcamento
                // Converte a data de referência em um objeto DateTime
                $dateTime = new \DateTime();
                // Subtrai um mês da data de referência
                $dateTime->modify('-1 month');
                $data_ultimos_trinta_dias = $dateTime;
                // dd($data_ultimos_trinta_dias);
                $listaOrcamento = Vendas::join('clientes', 'clientes.id', '=', 'cliente_id')->
                where('vendas.updated_at', '>=', $data_ultimos_trinta_dias)->
                where('vendas.tipo', '=', 'orcamento')->
                orderBy('vendas.updated_at', 'desc')->
                select('vendas.id', 'vendas.cliente_id', 'clientes.nome as nome_cliente', 'clientes.telefone', 'vendas.updated_at as data', 'valor', 'status')->paginate(10);
                //   dd($listaOrcamento);
                return view('frente/shoping', ['clientes' => $clientes, 'listaOrcamento' => $listaOrcamento, 'FormaPagamento' => $FormaPagamento, 'SubFormaPagamento' => $SubFormaPagamento, 'msg' => $msg]);
            } else{
                return redirect()->route('index');
            }
        } else {

            session(['nextview' => '/checkout']);
            return view("auth/login");
        }
    }

    public function removerQntCarrinho($id)
    {

        if (session()->has('produtos')) {

            $produtos = session('produtos');
            $saida = array();
            foreach ($produtos as $pd) {
                if ($pd['id'] == $id) {
                    if ($pd['qtd'] > 0) {
                        $pd['qtd'] = $pd['qtd'] - 1;
                    } else {
                        $pd['qtd'] = 0;
                    }
                }
                if ($pd['qtd'] > 0) {
                    array_push($saida, $pd);
                }
            }

        }

        session(['produtos' => $saida]);
        // return back();
        return $this->checkout();
    }

    public function aplicardesconto($id, $desconto)
    {
        if (session()->has('produtos')) {

            $produtos = session('produtos');
            $saida = array();

            //aqui verifica desponto maximo permitido
            $usuario = User::find(Auth::user()->id);
            $descontEmPorcetagemVendedor = $usuario->desconto;
            $produto = Anuncio::find($id);
            /* retirando a logica do brinde
             if ($produto->brinde) {
                  $valorDescontMaximoPermitido = $produto->preco;
              } else {
                  $valorDescontMaximoPermitido = $produto->preco * $descontEmPorcetagem / 100;
              }*/
            //aplicando a logica do desconto maximo por produto e vendedor
            if (!isset($produto->desconto_maximo) || $produto->desconto_maximo == 0) {
                $valorDescontMaximoPermitido = $produto->preco * $descontEmPorcetagemVendedor / 100;
            } else {
                $valorDescontMaximoPermitido = $produto->preco * $produto->desconto_maximo / 100;
            }

            // dd($valorDescontMaximoPermitido);
            $msg = null;
            foreach ($produtos as $pd) {
                if ($pd['id'] == $id) {
                    if ($desconto <= $valorDescontMaximoPermitido) {
                        $pd['desconto'] = $desconto;
                    } else {
                        $pd['desconto'] = 0;
                        $msg = ['valor' => trans("O desconto não é aplicável para o valor atual!"), 'tipo' => 'warning'];

                    }
                }
                array_push($saida, $pd);
            }
        }
        session(['produtos' => $saida]);

        return $this->checkout($msg);
    }

    public function addQntCarrinho($id)
    {

        if (session()->has('produtos')) {

            $produtos = session('produtos');
            $saida = array();
            foreach ($produtos as $pd) {
                if ($pd['id'] == $id) {
                    if ($pd['qtd'] > 0) {
                        $pd['qtd'] = $pd['qtd'] + 1;
                    } else {
                        $pd['qtd'] = 0;
                    }
                }
                if ($pd['qtd'] > 0) {
                    array_push($saida, $pd);
                }
            }

        }

        session(['produtos' => $saida]);
        return $this->checkout();
    }

    public function create(Request $request)
    {
        $msg = "Não há produtos na carrinho de compras para finalizar o pedido!";
        $endereco = Endereco::find($request->enderecos);
        if (!$endereco) {
            return back()->withInput();
        }
        if ($endereco->cidade != "Sobral") {
            return back()->withInput();
        }

        try {
            if (session()->has('produtos')) {

                $produtos = session('produtos');
                $saida = array();
                $saidat = array();
                $total = 0;
                foreach ($produtos as $pd) {
                    $anc = Anuncio::find($pd['id']);
                    $item = new ItensVenda();
                    $item->quantidade = $pd['qtd'];
                    //$item->tamanho = $pd['tamanho'];
                    $item->preco_item = $anc->preco;
                    $item->anuncio_id = $anc->id;
                    $item->vendedor_id = $anc->user_id;
                    $total = $total + ($item->quantidade * $item->preco_item);
                    $item->tamanho = $pd['tamanho'];
                    array_push($saida, $item);

                }


                DB::connection()->beginTransaction();
                $venda = new Vendas();
                $venda->id_venda = uniqid(date('HisYmd'));
                $frete = $request->frete;

                $venda->total = $total + $frete;
                $venda->valor = $total;
                $venda->comprador_id = Auth::user()->id;
                $venda->endereco_id = $request->enderecos;

                $venda->save();

                foreach ($saida as $iten) {
                    $iten->venda_id = $venda->id;
                    $iten->save();

                    $notificaoComprador = new Notificacoes();
                    $notificaoComprador->descricao = 'Estamos aguardando seu pagamento, após a conclusão seu produto será separado e enviado';
                    $notificaoComprador->id_user = $venda->comprador_id;
                    $notificaoComprador->id_venda = $venda->id;
                    $notificaoComprador->id_anuncio = $iten->anuncio_id;
                    $notificaoComprador->save();

                    //   dd('salvou a notificacao');
                    $notificaovendedor = new Notificacoes();
                    $notificaovendedor->descricao = 'Um produto seu foi comprado, estamos aguardando o pagamento para que você possa envia-lo';
                    $notificaovendedor->id_user = $iten->vendedor_id;
                    $notificaovendedor->id_anuncio = $iten->anuncio_id;
                    $notificaovendedor->save();

                }
                DB::connection()->commit();
                $msg = 'Sua compra está sendo processada, em breve você receberá um email com a confirmação dos seus dados! EcoModa Agradece a preferência';

                $this->clearCarr();
                return $this->processaPagSeguro($venda);
                //
            }


            /*$link_de_pagamento = $PagSeguro->getPaymentLinkByTransactionCode($venda_realizada['transaction_code']);*/


        } catch (QueryException $exception) {
            $msgret = ['valor' => "Erro ao executar a operação", 'tipo' => 'danger'];
            dd($exception);
        }

        return view('frente.msg', ['msg_compra' => $msg]);

    }

    public function createOrcamento(Request $request)
    {
        $msg = ['valor' => trans("Não há produtos na carrinho de compras para finalizar o pedido!"), 'tipo' => 'success'];
        try {
            $vendaCont = new VendasController();
            if (session()->has('produtos')) {
                $produtos = session('produtos');
                if (!empty($produtos)) {
                    //verifica se existe venda_id, caso sim deve atualizar o orcamento
                    if (session()->has('venda_id')) {
                        $venda_id = session('venda_id');
                        $saida = array();
                        $total = 0;
                        foreach ($produtos as $pd) {
                            $anc = Anuncio::find($pd['id']);
                            $item = new ItensVenda();
                            $item->quantidade = $pd['qtd'];
                            $item->desconto = $pd['desconto'];
                            $item->preco_item = $anc->preco;
                            $item->anuncio_id = $anc->id;
                            $item->custo = $anc->custo_total;
                            $item->lucro = $anc->margem_lucro;
                            $item->preco_compra = $anc->preco_compra;
                            $item->vendedor_id = $anc->user_id;
                            $total = $total + ($item->quantidade * ($item->preco_item - $item->desconto));
                            array_push($saida, $item);
                        }

                        DB::connection()->beginTransaction();
                        $venda = Vendas::find($venda_id);
                        $frete = 0;//$request->frete;
                        $venda->total = $total;// + $request->freteModal+$request->instalacaoModal;
                        $venda->valor = $venda->total;
                        $venda->comprador_id = Auth::user()->id;
                        //novos dados da venda
                        $venda->cliente_id = $request->cliente_idM;
                        $venda->forma_pagamento_id = $request->forma_pagamento_idM;
                        $venda->sub_tipo_forma_pagamento_id = $request->sub_tipo_forma_pagamento_idM;
                        $venda->endereco_id = 1;// $request->enderecos;
                        // dd($request);
                        if ($request->botaoacionado == "Venda") {
                            $venda->tipo = 'venda';
                            $venda->status = 'Aguardando retirada';
                            $venda->data_venda = Carbon::now();
                            $msg = ['valor' => trans("Venda criada com sucesso!"), 'tipo' => 'success'];
                        } else if($request->botaoacionado == "Venda Direta"){
                            $venda->tipo = 'venda';
                            $venda->data_venda = Carbon::now();
                            $venda->status = 'Finalizada - Venda direta';
                            $msg = ['valor' => trans("Venda direta criada com sucesso!"), 'tipo' => 'success'];
                        }else{
                            $venda->tipo = 'orcamento';
                            $venda->status = 'Aguardando aprovação';
                            $msg = ['valor' => trans("Orçamento atualizado com sucesso!"), 'tipo' => 'success'];
                        }
                        $venda->frete = $request->freteModal;
                        $venda->instalacao = $request->instalacaoModal;
                        $venda->save();

                        //aqui deletado os produtos que tinha antes no banco
                        $lista = ItensVenda::where('venda_id', '=', $venda->id)->orderBy('id', 'asc')->get();
                        // dd($lista);
                        foreach ($lista as $ent) {
                            $ent->delete();
                        }

                        $lucroTotal = 0;
                        foreach ($saida as $iten) {
                            $iten->venda_id = $venda->id;
                            if ($request->botaoacionado == "Venda") {
                                $iten->status = 'Aguardando saída';
                            } else {
                                $iten->status = 'Em orçamento';
                            }
                            $iten->save();

                            //aqui calculando o lucro da venda
                            $produto = Anuncio::find($iten->anuncio_id);
                            $lucroTotal = $lucroTotal + $iten->quantidade * ($produto->margem_lucro - $iten->desconto);

                        }
                        $venda->lucro = $lucroTotal;
                        $venda->save();

                        DB::connection()->commit();
                    } else {
                        $saida = array();
                        $total = 0;
                        foreach ($produtos as $pd) {
                            $anc = Anuncio::find($pd['id']);
                            $item = new ItensVenda();
                            $item->quantidade = $pd['qtd'];
                            $item->desconto = $pd['desconto'];
                            $item->preco_item = $anc->preco;
                            $item->anuncio_id = $anc->id;
                            $item->vendedor_id = $anc->user_id;
                            $item->custo = $anc->custo_total;
                            $item->lucro = $anc->margem_lucro;
                            $item->preco_compra = $anc->preco_compra;
                            $total = $total + ($item->quantidade * ($item->preco_item - $item->desconto));
                            array_push($saida, $item);
                        }

                        DB::connection()->beginTransaction();
                        $venda = new Vendas();
                        $venda->id_venda = uniqid(date('HisYmd'));
                        $frete = 0;//$request->frete;
                        $venda->total = $total;// + $request->freteModal+$request->instalacaoModal;
                        $venda->valor = $venda->total;
                        $venda->comprador_id = Auth::user()->id;

                        //novos dados da venda
                        if ($request->botaoacionado == "Venda") {
                            $venda->tipo = 'venda';
                            $venda->data_venda = Carbon::now();
                            $venda->status = 'Aguardando retirada';
                            $msg = ['valor' => trans("Venda criada com sucesso!"), 'tipo' => 'success'];
                        } else if($request->botaoacionado == "Venda Direta"){
                            $venda->tipo = 'venda';
                            $venda->status = 'Finalizada - Venda Direta';
                            $venda->data_venda = Carbon::now();
                            $msg = ['valor' => trans("Venda direta criada com sucesso!"), 'tipo' => 'success'];
                        } else {
                            $venda->tipo = 'orcamento';
                            $venda->status = 'Aguardando aprovação';
                            $msg = ['valor' => trans("Orçamento atualizado com sucesso!"), 'tipo' => 'success'];
                        }

                        $venda->cliente_id = $request->cliente_idM;
                        $venda->forma_pagamento_id = $request->forma_pagamento_idM;
                        $venda->sub_tipo_forma_pagamento_id = $request->sub_tipo_forma_pagamento_idM;
                        $venda->endereco_id = 1;// $request->enderecos;

                        $venda->frete = $request->freteModal;
                        $venda->instalacao = $request->instalacaoModal;
                        $venda->save();
                        $lucroTotal = 0;
                        foreach ($saida as $iten) {
                            $iten->venda_id = $venda->id;
                            if ($request->botaoacionado == "Venda") {
                                $iten->status = 'Aguardando saída';
                            } else if($request->botaoacionado == "Venda Direta") {
                                $iten->status = 'Venda Direta';
                            }else {
                                $iten->status = 'Em orçamento';
                            }
                            $iten->save();
                            //aqui calculando o lucro da venda
                            $produto = Anuncio::find($iten->anuncio_id);
                            $lucroTotal = $lucroTotal + $iten->quantidade * ($produto->margem_lucro - $iten->desconto);
                        }
                        $venda->lucro = $lucroTotal;
                        $venda->save();
                        DB::connection()->commit();

                    }
                }
            }




            $this->clearCarr();
            if (($request->botaoacionado == "Venda")||($request->botaoacionado == "Venda Direta")){
                //aqui deve redirecionar para a lista de vendas.
                return $vendaCont->listVendasFrente($msg);
            } else {
                return $vendaCont->listIndexFrente($msg);
            }
        } catch (QueryException $exception) {
            dd($exception);
        }

        return $vendaCont->listIndexFrente($msg);
    }


    public function clearCarr()
    {
        session(['venda_id' => null]);
        session(['cliente_id' => null]);
        session(['forma_pagamento_id' => null]);
        session(['sub_tipo_forma_pagamento_id' => null]);
        session(['produtos' => array()]);
        return back();
    }

    public function returnPagSeguro(Request $request)
    {

        $msg = 'Seu pagamento ainda não foi confirmado! EcoModa Agradece a preferência';
        if ($request->has('transaction_id')) {
            $transactionid = $request->input('transaction_id');
            $vend = Vendas::whereNull('transaction_pag_seguro')->get();


            foreach ($vend as $v) {

                $tid = $this->posWProcessPagamento($v->id);
                $transactionidp = Vendas::find($v->id)->transaction_pag_seguro;
                if ($tid == null || $transactionidp == null) {
                    continue;
                }

                if ($tid == $transactionidp) {
                    $msg = 'Seu pagamento foi confirmado, em breve lhe enviaremos seu produto! EcoModa Agradece a preferência';
                    $itens = ItensVenda::where('venda_id', '=', $v->id)->get();
                    foreach ($itens as $i) {
                        $an = Anuncio::find($i->anuncio_id);
                        $not = new Notificacoes();
                        //dd($v);
                        $not->descricao = "Seu produto do pedido:" . $v->id_venda . " Teve o pagamento confirmado, envio ao comprador!";
                        $not->id_user = $an->user_id;
                        $not->id_anuncio = $an->id;
                        $not->id_venda = $v->id;
                        $not->save();
                        $us = User::find($not->id_user);
                        Helper::sendEmail(
                            "Confirmação de Pagamento do Pedido: " . $v->id_venda,
                            "O pagamento do pedido: " . $v->id_venda . " foi confimado pela operadora envie  o produto!",
                            $us->email
                        );
                    }
                    break;
                }
            }
        }

        return view('frente.msg', ['msg_compra' => $msg]);
    }

    public function posWProcessPagamento($id)
    {
        $PagSeguro = new PagSeguro();
        $response = $PagSeguro->getByReference("venda_{$id}");
        $venda = Vendas::find($id);

        if (isset($response->status)) {
            if ($response->status != $venda->status_pagseguro) {
                $texto_status = $PagSeguro->getStatusText($response->status);
                $texto_metodo = $PagSeguro->getPaymentMethodText($response->paymentMethod->type);
                $taxa = $response->feeAmount;
                $valor_liquido = $response->netAmount;

                if (isset($venda->data_pagamento) && !is_null($venda->data_pagamento)) {
                    $data_pagamento = $venda->data_pagamento;
                    $pagamento_identificado = true;
                } elseif ($response->status == 3 || $response->status == 4) {
                    $data_pagamento = date('Y-m-d');
                    $pagamento_identificado = true;
                }

                $venda->transaction_pag_seguro = $response->code;
                $venda->status_pagseguro = $response->status;
                $venda->txt_status_pagseguro = $texto_status;
                $venda->status_metodo = $response->paymentMethod->type;
                $venda->txt_status_metodo = $texto_metodo;
                $venda->taxa_operadora = $taxa;
                $venda->valor_liquido = $valor_liquido;
                $venda->data_pagamento = $data_pagamento;
                $venda->save();

                return $venda->transaction_pag_seguro;
                //$venda->setStatusPagSeguro($response->code, $response->status, $id_venda, $texto_status, $response->paymentMethod->type, $texto_metodo, $data_pagamento, $taxa, $valor_liquido);
                //$venda_row = $venda->getByChave($id_venda);
            }
        }
    }

    public function posProcessPagamento($id)
    {
        $PagSeguro = new PagSeguro();
        $response = $PagSeguro->getByReference("venda_{$id}");
        $venda = Vendas::find($id);
        if (isset($response->status)) {
            if ($response->status != $venda->status_pagseguro) {
                $texto_status = $PagSeguro->getStatusText($response->status);
                $texto_metodo = $PagSeguro->getPaymentMethodText($response->paymentMethod->type);
                $taxa = $response->feeAmount;
                $valor_liquido = $response->netAmount;

                if (isset($venda->data_pagamento) && !is_null($venda->data_pagamento)) {
                    $data_pagamento = $venda->data_pagamento;
                    $pagamento_identificado = true;
                } elseif ($response->status == 3 || $response->status == 4) {
                    $data_pagamento = date('Y-m-d');
                    $pagamento_identificado = true;
                }

                $venda->transaction_pag_seguro = $response->code;
                $venda->status_pagseguro = $response->status;
                $venda->txt_status_pagseguro = $texto_status;
                $venda->status_metodo = $response->paymentMethod->type;
                $venda->txt_status_metodo = $texto_metodo;
                $venda->taxa_operadora = $taxa;
                $venda->valor_liquido = $valor_liquido;
                $venda->data_pagamento = $data_pagamento;
                $venda->save();
                //$venda->setStatusPagSeguro($response->code, $response->status, $id_venda, $texto_status, $response->paymentMethod->type, $texto_metodo, $data_pagamento, $taxa, $valor_liquido);
                //$venda_row = $venda->getByChave($id_venda);
            }
        }


        $msg = 'Seu pagamento foi confirmado, em breve lhe enviaremos seu produto! EcoModa Agradece a preferência';
        return view('frente.msg', ['msg_compra' => $msg]);
    }

    public function processaPagSeguro($venda_realizada)
    {
        $PagSeguro = new PagSeguro();
        $descricao_venda_pagseguro = "ITEMS COMPRADOS NA ECOMODA";
        //= Vendas::find($id);
        $pessoa = User::find($venda_realizada->comprador_id);
        $endereco = Endereco::find($venda_realizada->endereco_id);

        // dd($venda_realizada->total);
        $dados_venda = array(
            'codigo' => "venda_{$venda_realizada->id}",
            'valor' => $venda_realizada->total,
            'quantidade' => $venda_realizada->quantidade,
            'descricao' => $descricao_venda_pagseguro,
            'nome' => $pessoa->nome,
            'email' => $pessoa->email,
            'telefone' => "88997141874",
            'codigo_pagseguro' => $venda_realizada->id_venda,
            'transaction_id' => '',
            'cep' => $endereco->cep,
        );

        $url_retorno = route('vendas.payment', $venda_realizada->id);
        ;//env('local')"/pagamento_sucesso.php?venda={$venda_realizada->id}";
        //  dd($url_retorno);
        $link_de_pagamento = $PagSeguro->generatePaymentLink($dados_venda, $url_retorno);
        $assunto = " Pedido n°" . $venda_realizada->id_venda;
        $msg = 'Recebemos seu pedido n° ' . $venda_realizada->id_venda . ', no valor de ' . $venda_realizada->total
            . "para mais detalhes acesse <a href='#'>aqui </a> ";
        /* enviando email para o cliente*/
        Helper::sendEmail($assunto, $msg, $pessoa->email);


        $itens = ItensVenda::join('anuncios', 'anuncios.id', '=', 'itens_vendas.anuncio_id')
            ->join('users', 'users.id', '=', 'anuncios.user_id')
            ->where('venda_id', '=', $venda_realizada->id)->get();

        return redirect($link_de_pagamento);
    }

    public function addEndereco(Request $request)
    {

        return view('frente/freteshop', ['obj' => new Endereco(), 'total' => 10]);
    }

    public function saveEndereco(Request $request)
    {

        $msgret = ['valor' => "Operação realizada com sucesso!", 'tipo' => 'success'];
        try {
            $endereco = new Endereco();
            if ($request->id_add) {
                $endereco = Endereco::find($request->id_add);
            }

            $endereco->recebedor = $request->recebedor;
            $endereco->cep = $request->cep;
            $endereco->estado = $request->estado;
            $endereco->cep = $request->cep;
            $endereco->cidade = $request->cidade;
            $endereco->bairro = $request->bairro;
            $endereco->rua = $request->rua;
            $endereco->numero = $request->numero;
            $endereco->complemento = $request->complemento;
            $endereco->informacoes = $request->informacoes;
            if ($request->principal) {
                $endereco->princial = $request->principal;
            } else {
                $endereco->princial = false;
            }


            $endereco->user_id = $request->id;
            $endereco->save();

        } catch (QueryException $exception) {
            $msgret = ['valor' => "Erro ao executar a operação", 'tipo' => 'danger'];
        }

        return redirect(route('finalizar', ['msg' => $msgret]));
    }

    //
}
