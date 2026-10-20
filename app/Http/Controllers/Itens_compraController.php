<?php
namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Custo;
use App\Models\Fornecedor;
use App\Models\Itens_compra;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compra;

class Itens_compraController extends Controller
{
    function list($compra_id, $msg = null)
    {
        $lista = Itens_compra::where('compra_id', '=', $compra_id)->orderBy('id', 'desc')->paginate(10);
        $compra = compra::find($compra_id);
        return view('itens_compra/list', ['lista' => $lista, 'custos' => Custo::all(),'compra' => $compra, 'msg' => $msg]);
    }

    function save(Request $request, $compra_id)
    {
        if (!isset($compra_id)) {
            //aqui salvar a compra
        } else {
            //aqui pegar id da compra
            // $compra_id = $request->compra_id;
        }
        if ($request->id) {
            $ent = Itens_compra::find($request->id);
            $ent->compra_id = $request->compra_id;
            $ent->produto_id = $request->produto_id;
            $ent->quantidade = $request->quantidade;
            $ent->valor_unitario = $request->valor_unitario;
            $ent->icms = $request->icms;
            $ent->ipi = $request->ipi;
            $ent->frete = $request->frete;
            $ent->icms_pago_fabricante = $request->icms_pago_fabricante;
            $ent->cartao_12x = $request->cartao_12x;
            $ent->comissao_vendedor = $request->comissao_vendedor;
            $ent->margem_lucro = $request->margem_lucro;
            $ent->custo_total = $request->custo_total;
            $ent->entrega_instalacao = $request->entrega_instalacao;
            $ent->valor_venda = $request->valor_venda;
            $ent->compra_id = $compra_id;
            $ent->save();
        } else {
            $entidade = Itens_compra::create([
                'compra_id' => $request->compra_id,
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade,
                'valor_unitario' => $request->valor_unitario,
                'icms' => $request->icms,
                'ipi' => $request->ipi,
                'frete' => $request->frete,
                'icms_pago_fabricante' => $request->icms_pago_fabrica,
                'cartao_12x' => $request->cartao_12x,
                'comissao_vendedor' => $request->comissao_vendedor,
                'margem_lucro' => $request->margem_lucro,
                'simples' => $request->simples,
                'custo_total' => $request->custo_totalModal,
                'entrega_instalacao' => $request->entrega_instalacao,
                'valor_venda' => $request->preco_venda_estimadoM,
                'preco_venda_sugerido' => $request->preco_venda_sugeridoM,
                'status' => 'Aguardando entrada',
            ]);
        }
        //atualizar os dados do produto. apenas na entrada do produto.
        //  $this->atualizaDadosProduto($request);
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];


        //levando os dados para o form da compra novamente.
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $produtos = Anuncio::orderBy('titulo', 'asc')->get();
        $compra = Compra::find($request->compra_id);
        $compra->total_compra = $compra->total_compra + ($request->quantidade * $request->valor_unitario);

        //aqui colocar os dados da compra vindo pelo modal

        $compra->fornecedor_id = $request->fornecedor_idM;
        $compra->chave = $request->chaveM;
        $compra->numero_nota = $request->numero_notaM;
        $compra->serie = $request->serieM;
        $compra->data_emissao = $request->data_emissaoM;
        $compra->data_saida = $request->data_saidaM;
        $compra->data_entrada = $request->data_entradaM;
        $compra->protocolo_autorizacao = $request->protocolo_autorizacaoM;
        $compra->tipo_operacao = $request->tipo_operacaoM;
        $compra->natureza_operacao = $request->natureza_operacaoM;
        $compra->save();

        $itens_compra = Itens_compra::join('anuncios', 'anuncios.id', '=', 'produto_id')->
        where('compra_id', '=', $request->compra_id)->orderBy('itens_compras.id', 'desc')->
        select('itens_compras.id as id', 'anuncios.titulo as titulo', 'itens_compras.quantidade', 'itens_compras.valor_unitario')->paginate(100);

        return view('compra/form', ['entidade' => $compra,'custos' => Custo::all(), 'fornecedores' => $fornecedores, 'produtos' => $produtos, 'lista' => $itens_compra]);

    }

    function confirmarentrada($id)
    {
        $compraControle = new CompraController();
        //verificar se ja nao foi feito a entrada do produto.
        $ent = Itens_compra::find($id);
        if ($ent->status != 'Incluído no inventário') {
            $ent->status = 'Incluído no inventário';
            $ent->save();
            $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];

            //atualizar dados do produto
            $this->atualizaDadosProduto($ent->id);

            //verifica se existe algum item que esta aguandado a entrada, se nao
            //setar a compra como finalizada
            if(!$this->possueItemAguardadoEntrada($ent->compra_id)){
                $msg = ['valor' => trans("Operação realizada com sucesso! Da compra selecionada não existe mais item a ser incluído no estoque."), 'tipo' => 'success'];
                $compra = Compra::find($ent->compra_id);
                $compra->status = 'Finalizada';
                $compra->save();
                return $compraControle->listentrada($msg);
            }

            $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        } else {
            $msg = ['valor' => trans("Operação não realizada. O item já foi incluído no estoque."), 'tipo' => 'primary'];
        }
        return $compraControle->confirmarentrada($ent->compra_id,$msg);
    }

    function possueItemAguardadoEntrada($compra_id)
    {
        $itens_compra = Itens_compra::
        where('compra_id', '=', $compra_id)->get();
        foreach ($itens_compra as $ent)
        {
              if ($ent->status != 'Incluído no inventário') {
                  return true;
              }
        }
        return false;

    }

    function atualizaDadosProduto($item_compra_id)
    {
        $item_compra = Itens_compra::find($item_compra_id);
        $anuncio = Anuncio::find($item_compra->produto_id);

        $anuncio->custo_total = $item_compra->custo_total;
        $anuncio->preco_compra = $item_compra->valor_unitario;
        $anuncio->preco = $item_compra->valor_venda;
        $anuncio->margem_lucro = $item_compra->margem_lucro;
        $anuncio->entrega_instalacao = $item_compra->entrega_instalacao;
        $anuncio->comissao_vendedor = $item_compra->comissao_vendedor;
        $anuncio->cartao_12x = $item_compra->cartao_12x;
        $anuncio->simples = $item_compra->simples;
        $anuncio->icms = $item_compra->icms;
        $anuncio->icms_pago_fabrica = $item_compra->icms_pago_fabricante;
        $anuncio->ipi = $item_compra->ipi;
        $anuncio->frete = $item_compra->frete;
        $anuncio->quantidade =  $anuncio->quantidade+$item_compra->quantidade;
        $anuncio->preco_venda_sugerido = $item_compra->preco_venda_sugerido;
        $anuncio->ativo = true;
        $anuncio->save();
    }

    function delete($id)
    {
        $compra_id = 0;
        try {
            $entidade = Itens_compra::find($id);
            if ($entidade) {
                $compra_id = $entidade->compra_id;
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($compra_id, $msg);
    }

    function edit($id)
    {
        $entidade = Itens_compra::find($id);
        $compra_id = $entidade->compra_id;
        $compra = Compra::find($compra_id);
        return view('itens_compra/form', ['entidade' => $entidade, 'compra' => $compra]);
    }

   
} ?>
