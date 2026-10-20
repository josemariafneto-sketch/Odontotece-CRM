<?php
namespace App\Http\Controllers;

use App\Models\TipoAnuncio;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Type_produtoController extends Controller
{
    function list($msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = TipoAnuncio::where('descricao', 'like', "%" . "%")->orderBy('id', 'desc')->paginate(10);
        return view('type_produto/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg]);
    }

    function new()
    {
        return view('type_produto/form', ['entidade' => new TipoAnuncio()]);
    }

    function search(Request $request)
    {
        $filter = $request->query('filtro');
        $lista = TipoAnuncio::where('descricao', 'like', "%" . $request->filtro . "%")->orderBy('id', 'desc')->paginate(10);
        return view('type_produto/list', ['lista' => $lista, 'filtro' => $request->filtro])->with('filter', $filter);
    }

    function save(Request $request)
    {
        if ($request->id) {
            $ent = TipoAnuncio::find($request->id);
            $ent->descricao = $request->descricao;
            $ent->save();
        } else {
            $entidade = TipoAnuncio::create([
                'descricao' => $request->descricao]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($msg);
    }

    function delete($id)
    {
        try {
            $entidade = TipoAnuncio::find($id);
            if ($entidade) {
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($msg);
    }

    function edit($id)
    {
        $entidade = TipoAnuncio::find($id);
        return view('type_produto/form', ['entidade' => $entidade]);
    }

} ?>
