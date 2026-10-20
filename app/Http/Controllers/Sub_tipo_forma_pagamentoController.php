<?php
namespace App\Http\Controllers;

use App\Models\Sub_tipo_forma_pagamento;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Forma_pagamento;

class Sub_tipo_forma_pagamentoController extends Controller
{
    function list($forma_pagamento_id, $msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Sub_tipo_forma_pagamento::where('forma_pagamento_id', '=', $forma_pagamento_id)->orderBy('id', 'desc')->paginate(10);
        $forma_pagamento = forma_pagamento::find($forma_pagamento_id);
        return view('sub_tipo_forma_pagamento/list', ['lista' => $lista, 'filtro' => $filter, 'forma_pagamento' => $forma_pagamento, 'msg' => $msg]);
    }

    function new($forma_pagamento_id)
    {
        $forma_pagamento = Forma_pagamento::find($forma_pagamento_id);
        return view('sub_tipo_forma_pagamento/form', ['entidade' => new Sub_tipo_forma_pagamento(), 'forma_pagamento' => $forma_pagamento]);
    }

    function search(Request $request)
    {
        $forma_pagamento = Forma_pagamento::find($forma_pagamento_id);
        $filter = $request->query('filtro');
        $lista = Sub_tipo_forma_pagamento::where('descricao', 'like', "%" . $request->filtro . "%")->orderBy('id', 'desc')->paginate(10);
        return view('sub_tipo_forma_pagamento/list', ['lista' => $lista, 'filtro' => $request->filtro, 'forma_pagamento' => $forma_pagamento])->with('filter', $filter);
    }

    function save(Request $request)
    {
        $forma_pagamento_id = $request->forma_pagamento_id;
        if ($request->id) {
            $ent = Sub_tipo_forma_pagamento::find($request->id);
            $ent->descricao = $request->descricao;
            $ent->forma_pagamento_id = $forma_pagamento_id;
            $ent->save();
        } else {
            $entidade = Sub_tipo_forma_pagamento::create([
                'descricao' => $request->descricao,
                'forma_pagamento_id' => $forma_pagamento_id]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($forma_pagamento_id, $msg);
    }

    function delete($id)
    {
        $forma_pagamento_id = 0;
        try {
            $entidade = Sub_tipo_forma_pagamento::find($id);
            if ($entidade) {
                $forma_pagamento_id = $entidade->forma_pagamento_id;
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($forma_pagamento_id, $msg);
    }

    function edit($id)
    {
        $entidade = Sub_tipo_forma_pagamento::find($id);
        $forma_pagamento_id = $entidade->forma_pagamento_id;
        $forma_pagamento = Forma_pagamento::find($forma_pagamento_id);
        return view('sub_tipo_forma_pagamento/form', ['entidade' => $entidade, 'forma_pagamento' => $forma_pagamento]);
    }

} ?>
