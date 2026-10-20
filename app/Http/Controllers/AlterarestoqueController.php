<?php
namespace App\Http\Controllers;

use App\Models\Alterarestoque;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Anuncio;

class AlterarestoqueController extends Controller
{
   function save(Request $request)
   {
   
      $anuncios_id = $request->produto_idMqtd;
      $anuncio = Anuncio::find($anuncios_id);
      
      if ($request->tipo == "Entrada") {         
         $anuncio->quantidade = $anuncio->quantidade + $request->quantidade;
      } else {
         $anuncio->quantidade = $anuncio->quantidade - $request->quantidade;
      }
      $anuncio->save();

      $entidade = Alterarestoque::create([
         'quantidade' => $request->quantidade,
         'tipo' => $request->tipo,
         'motivo' => $request->motivo,
         'anuncios_id' => $anuncios_id
      ]);

      $msg = ['valor' => trans("Operação realizada com sucesso!"), 'tipo' => 'success'];

      //retornar para a lista de produtos   
      $anucioController = new AnuncioController();
      return $anucioController->list($msg);
   }

} ?>