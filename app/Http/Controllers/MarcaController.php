<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Marca;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class MarcaController extends Controller
{
    protected $marca;

    public function __construct(Marca $marca, Request $request)
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
        // $marcas = Marca::all();
        $marcas = $this->marca->with('modelos')->get(); 
        return response($marcas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMarcaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMarcaRequest $request)
    {
        // $marca = Marca::create($request->all());
        // nome
        // imagem
        $request->validate($this->marca->rules(), $this->marca->feedback());

        // dd($request->nome);
        // dd($request->get('nome'));
        // dd($request->file('imagem'));
        // dd($request->imagem);

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        return response($marca, 201);
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
        if ($marca === null ){
            return response(['erro' => 'Recurso pesquisado não existe'], 404);
        }
        return response($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMarcaRequest  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMarcaRequest $request, $id)
    {
        // $marca->update($request->all());
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response(['erro' => 'Ímpossivel realizar a atualização. o recurso solicitado não existe.', 404]);
        }

        if ($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            foreach($marca->rules() as $input => $regra) {

                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }
        } else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        if ($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }
        
        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens', 'public');

        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        // dd($marca->getAttributes());
        $marca->save();

        $marca->update([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        return response($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, UpdateMarcaRequest $request)
    {
        $marca = $this->marca->find($id);
        
        if ($marca === null) {
            return response(['erro' => 'Ímpossivel realizar a exclusão. o recurso solicitado não existe.'], 404);
        }

        if ($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $marca->delete();
        return response(['msg' => 'A Marca foi deletada'], 200);
    }
}
