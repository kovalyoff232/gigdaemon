<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{
	use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
{

    return auth()->user()->clients()->with('projects')->get();
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
       
        $client = auth()->user()->clients()->create($request->validated());

        return response()->json($client, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
       
        $this->authorize('view', $client);

        return $client;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
       
        $this->authorize('update', $client);

        $client->update($request->validated());

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->noContent();
    }
}