<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

use App\Models\User;
use App\Models\Vendas;
use App\Models\Compra;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function home()
    {


        $dataAtual = Carbon::now();
        $umAnoAntes = $dataAtual->subYear();
        $dataAtual = Carbon::now();

        //retornando todos os usuarios, pois todos podem realizar vendas
        $vendedores = User::orderBy('name', 'asc')->get();
        //  $vendedores = User::where('tipouser', '=', "V")->orderBy('name', 'asc')->get();

        //dd($vendedores);
        //  $user = User::findOrFail($userId);
        $TodasVendasPorMes = Vendas::whereBetween('data_venda', [$umAnoAntes, $dataAtual])
            ->selectRaw('MONTH(data_venda) as mes, sum(total) as total_vendas, count(*) as quantidade, sum(lucro) as total_lucro')
            // ->where('comprador_id', 1)
          //  ->whereBetween('data_venda', [$umAnoAntes, $dataAtual])
            ->where('vendas.tipo', '=', 'venda')
            ->groupBy(Vendas::raw('MONTH(data_venda)'))
          //  ->orderBy('data_venda', 'asc')
            ->limit(12)
            ->get();



        $TodasComprasPorMes = Compra::whereBetween('created_at', [$umAnoAntes, $dataAtual])
            ->selectRaw('MONTH(data_emissao) as mes, sum(total_compra) as total, count(*) as quantidade')
            // ->where('comprador_id', 1)
            //  ->whereBetween('data_venda', [$umAnoAntes, $dataAtual])
            ->groupBy(Vendas::raw('MONTH(data_emissao)'))
          //  ->orderBy('data_emissao', 'asc')
            ->limit(12)
            ->get();

      //  dd($TodasComprasPorMes);

        //dd($TodasComprasPorMes);
        return view('dashboard', ['lista' => $vendedores, 'TodasVendasPorMes' => $TodasVendasPorMes, 'ComprasPorMes' => $TodasComprasPorMes]);
    }




}
