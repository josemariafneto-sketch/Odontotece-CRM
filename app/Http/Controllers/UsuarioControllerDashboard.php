<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Campanha;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Language;
use App\Models\Loja;
use App\Models\Tokens;
use App\Models\TypeUser;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class UsuarioControllerDashboard extends Controller
{
    function list($msg = null)
    {
        $filter = "";
        if (isset($_GET['filtro'])) {
            $filter = $_GET['filtro'];
        }
        $lista = User::where('name', 'like', "%" . "%")->orderBy('name', 'asc')->paginate(10);
        return view('usuario/list', ['lista' => $lista, 'filtro' => $filter, 'msg' => $msg]);
    }

    function new()
    {
        $user = new User();
        return view('usuario/form', ['usuario' => $user],);
    }


    function edit($id)
    {
        $user = User::find($id);
        return view('usuario/form', ['usuario' => $user]);
    }

    function valida(Request $request, $tipo)
    {
        $input = $request->validate([
            'email' => 'required|unique:users,email',
        ]);
        return $input;
    }

    function save(Request $request)
    {
        $logo = null;
        $usuario = new User();
        try {
            if ($request->id) {
                $input = $request->validate([
                    'email' => 'required|unique:users,email,' . $request->id,
                ]);
                $usuario = User::find($request->id);
                $usuario->name = $request->name;
                $usuario->password = bcrypt($request->password);
                $usuario->telefone = $request->telefone;
                $usuario->email = $request->email;
                $usuario->tipouser = $request->tipouser;
                $usuario->desconto = $request->desconto;
                $usuario->save();
            } else {
                $this->valida($request, false);
                $usuario = User::create([
                    'name' => $request->name,
                    'password' => bcrypt($request->password),
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'tipouser' => $request->tipouser,
                    'desconto' =>$request->desconto,
                ]);
            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];
        return $this->list($msg);
    }


    function search(Request $request)
    {
        $filter = $request->query('filtro');
        $lista = User::where('name', 'like', "%" . $request->filtro . "%")->
        orderBy('name', 'asc')->paginate(10);
        return view('usuario/list', ['lista' => $lista, 'msg' => null, 'filtro' => $request->filtro])->with('filter', $filter);
    }

    function delete($id)
    {
        try {
            $usuario = User::find($id);
            $loja_id = $usuario->loja_id;
            if ($usuario) {
                if ($usuario->type != 1) {
                    $usuario->delete();
                    $msg = ['valor' => trans('messport.sucesso'), 'tipo' => 'success'];
                } else {
                    $msg = ['valor' => trans('messport.root_user'), 'tipo' => 'primary'];
                }
            } else {
                $msg = null;

            }
        } catch (QueryException $exp) {
            $msg = ['valor' => $exp->getMessage(), 'tipo' => 'primary'];
        }
        return $this->list($loja_id, $msg);
    }


}
