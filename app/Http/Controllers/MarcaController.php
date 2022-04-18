<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use  Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class MarcaController extends Controller
{

    public function __construct(Marca $marca)
    {
      $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $marcas = $this->marca->with('modelos')->get();
      return response()->json($marcas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $request->validate($this->marca->rules(), $this->marca->messages());

      $imagem = $request->file('imagem');
      $imagem_urn = $imagem->store('imagens', 'public');

      $marca = $this->marca->create([
          'nome' => $request->nome,
          'imagem' => $imagem_urn
      ]);
      return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null) {
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404);
        }
        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $marca = $this->marca->find($id);

      if($marca === null) {
        return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
      }

      $request->validate($marca->rules(), $marca->messages());

      // Remove a imagem antiga se o usuario enviar uma nova imagem
      if($request->file('imagem')) {
        Storage::disk('public')->delete($marca->imagem);
      }

      $imagem = $request->file('imagem');
      $imagem_urn = $imagem->store('imagens', 'public');

      //$marca = $this->marca->create();
      $marca->update([
        'nome' => $request->nome,
        'imagem' => $imagem_urn
      ]);

      return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $marca = $this->marca->find($id);
      if($marca === null) {
        return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'], 404);
      }

      Storage::disk('public')->delete($marca->imagem);

      $marca->delete();
      return response()->json(['msg' => "Marca removida com sucesso!"], 200);
    }
}
