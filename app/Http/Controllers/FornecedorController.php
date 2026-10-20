<?php
namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FornecedorController extends Controller
{
    function list($msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = Fornecedor::where('nome', 'like', "%" . "%")->orderBy('nome', 'asc')->paginate(10);
        return view('fornecedor/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg]);
    }

    function new()
    {
        return view('fornecedor/form', ['entidade' => new Fornecedor()]);
    }

    function search(Request $request)
    {
        $filter = $request->query('filtro');
        $lista = Fornecedor::where('nome', 'like', "%" . $request->filtro . "%")->orderBy('nome', 'asc')->paginate(10);
        return view('fornecedor/list', ['lista' => $lista, 'filtro' => $request->filtro])->with('filter', $filter);
    }

    function save(Request $request)
    {
        if ($request->id) {
            $ent = Fornecedor::find($request->id);
            $ent->nome = $request->nome;
            $ent->cnpj_cpf = $request->cnpj_cpf;
            $ent->escricao_estadual = $request->escricao_estadual;
            $ent->endereco = $request->endereco;
            $ent->save();
        } else {
            $entidade = Fornecedor::create([
                'nome' => $request->nome,
                'cnpj_cpf' => $request->cnpj_cpf,
                'escricao_estadual' => $request->escricao_estadual,
                'endereco' => $request->endereco]);
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($msg);
    }

    function delete($id)
    {
        try {
            $entidade = Fornecedor::find($id);
            if ($entidade) {
                $entidade->delete();
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            } else {
                $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
            }
        } catch (QueryException $exp) {
            //$exp->getMessage()
            $msg = ['valor' => "Fornecedor em uso, não pode ser deletado!", 'tipo' => 'warning'];
        }
        return $this->list($msg);
    }

    function edit($id)
    {
        $entidade = Fornecedor::find($id);
        return view('fornecedor/form', ['entidade' => $entidade]);
    }

} ?>
