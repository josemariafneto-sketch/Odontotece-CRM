<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnuncios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_adv', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_anuncio');
            $table->string('titulo');
            $table->text('descricao');
            $table->double('preco')->nullable()->default(0);
            $table->double('quantidade')->nullable()->default(0);
            $table->boolean('ativo')->nullable();
            $table->boolean('destaque')->nullable()->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();

            $table->double('altura')->nullable()->default(0);
            $table->double('comprimento')->nullable()->default(0);
            $table->double('largura')->nullable()->default(0);
            $table->double('peso')->nullable()->default(0);
            $table->double('margem_lucro')->nullable();
            $table->double('entrega_instalacao')->nullable();
            $table->double('comissao_vendedor')->nullable();
            $table->double('cartao_12x')->nullable();
            $table->double('simples')->nullable();
            $table->double('icms')->nullable();
            $table->double('icms_pago_fabrica')->nullable();
            $table->double('ipi')->nullable();
            $table->double('custo_total')->nullable();
            $table->double('preco_compra')->nullable();
            $table->double('frete')->nullable();
            $table->boolean('brinde')->nullable();
            $table->boolean('desconto_maximo')->nullable();
            $table->double('preco_venda_sugerido')->nullable()->default(0);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anuncios');
        Schema::dropIfExists('type_adv');
    }
}
