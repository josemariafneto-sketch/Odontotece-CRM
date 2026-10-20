<?php
 namespace App\Http\Controllers;
 use App\Models\Forma_pagamento;
 use Illuminate\Database\QueryException;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;

 class Forma_pagamentoController extends Controller
 {
 function list($msg=null){
 $filter = "";
 if(isset($_GET['filtro'])){
 $filter = $_GET['filtro'];
 }
     $lista = Forma_pagamento::where('descricao','like',"%"."%")->orderBy('descricao', 'asc')->paginate(10);
     return view('forma_pagamento/list',['lista'=>$lista,'filtro'=>$filter,'msg' => $msg]);
 }
 function new(){
     return view('forma_pagamento/form',['entidade'=>new Forma_pagamento()]);
 }
 function search(Request $request){
    $filter = $request->query('filtro');
    $lista = Forma_pagamento::where('descricao','like',"%".$request->filtro."%")->orderBy('descricao', 'asc')->paginate(10);
    return view('forma_pagamento/list',['lista'=>$lista,'filtro'=>$request->filtro])->with('filter', $filter);
 }
 function save(Request $request){
    if ($request -> id){
       $ent= Forma_pagamento::find ($request->id);
       $ent-> descricao=$request-> descricao;
       $ent-> save();
    } else {
        $entidade = Forma_pagamento::create ([
       'descricao'=>$request-> descricao ]);
    }
   $msg = ['valor'=>trans("Operação realizada com sucesso!"),'tipo'=>'success'];
    return $this->list($msg);
 }
 function  delete($id){
    try {
    $entidade = Forma_pagamento::find($id);
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
    $entidade = Forma_pagamento::find($id);
      return view('forma_pagamento/form',['entidade'=>$entidade]);
    }

 } ?>
