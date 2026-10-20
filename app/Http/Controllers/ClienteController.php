<?php
namespace App\Http\Controllers;
use App\Models\Cliente;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    function list($msg=null){
        $filter = new Cliente();
        $filter-> nome = "";
        $filter-> tipo = "";
        if(isset($_GET['nome'])){
            $filter-> nome = $_GET['nome'];
        }
        if(isset($_GET['tipo'])){
            $filter-> tipo = $_GET['tipo'];
        }
        $lista = Cliente::where('nome','like',"%"."%")->orderBy('nome', 'asc')->paginate(10);
        return view('frente/cliente/list',['lista'=>$lista,'filtro'=>$filter,'msg' => $msg]);
    }
    function new(){
        return view('frente/cliente/form',['entidade'=>new Cliente()]);
    }
    function search(Request $request){
        $filter = new Cliente();
        if(isset($_GET['nome'])){
            $filter-> nome = $_GET['nome'];
        }
        if(isset($_GET['tipo'])){
            $filter-> tipo = $_GET['tipo'];
        }
        $lista = Cliente::where('nome','like',"%".$filter->nome."%")->
        where('tipo','like',"%".$filter->tipo."%")->
        orderBy('nome', 'asc')->paginate(10);
        return view('frente/cliente/list',['lista'=>$lista,'filtro'=>$filter]);
    }
    function save(Request $request){
        if ($request -> id){
            $ent= Cliente::find ($request->id);
            $ent-> nome=$request-> nome;
            $ent-> cpf=$request-> cpf;
            $ent-> telefone=$request-> telefone;
            $ent-> email=$request-> email;
            $ent-> rua=$request-> rua;
            $ent-> numero=$request-> numero;
            $ent-> bairro=$request-> bairro;
            $ent-> cidade=$request-> cidade;
            $ent-> loja_id=$request-> loja_id;
            $ent-> tipo=$request-> tipo;
            $ent-> cep=$request-> cep;
            $ent-> cnpj=$request-> cnpj;
            $ent-> rg=$request-> rg;
            $ent-> inscricao_estadual=$request-> inscricao_estadual;
            $ent-> razao_social=$request-> razao_social;
            $ent-> estado=$request-> estado;
            $ent-> cro=$request-> cro;
            $ent-> universidade=$request-> universidade;
            $ent-> ano_formacao=$request-> ano_formacao;
            $ent-> escolaridade=$request-> escolaridade;
            $ent-> save();
        } else {
            $entidade = Cliente::create ([
                'nome'=>$request-> nome,
                'cpf'=>$request-> cpf,
                'telefone'=>$request-> telefone,
                'email'=>$request-> email,
                'rua'=>$request-> rua,
                'numero'=>$request-> numero,
                'bairro'=>$request-> bairro,
                'cidade'=>$request-> cidade,
                'loja_id'=>$request-> loja_id,
                'tipo'=>$request-> tipo,
                'cep'=>$request-> cep,
                'cnpj'=>$request-> cnpj,
                'rg'=>$request-> rg,
                'inscricao_estadual'=>$request-> inscricao_estadual,
                'razao_social'=>$request-> razao_social,
                'estado'=>$request-> estado,
                'cro'=>$request-> cro,
                'universidade'=>$request-> universidade,
                'ano_formacao'=>$request-> ano_formacao,
                'escolaridade'=>$request-> escolaridade ]);
        }
        $msg = ['valor'=>trans("Operação realizada com sucesso!"),'tipo'=>'success'];
        return $this->list($msg);
    }
    function  delete($id){
        try {
            $entidade = Cliente::find($id);
            if ($entidade){
                $entidade->delete();
                $msg = ['valor'=>trans("Operação realizada com sucesso!"),'tipo'=>'success'];
            } else {
                $msg = ['valor'=>trans("Operação realizada com sucesso!"),'tipo'=>'success'];
            }
        }catch (QueryException $exp ){
            $msg = ['valor'=>$exp->getMessage(),'tipo'=>'primary'];
        }
        return $this->list($msg);
    }
    function  edit($id){
        $entidade = Cliente::find($id);
        return view('frente/cliente/form',['entidade'=>$entidade]);
    }




} ?>
