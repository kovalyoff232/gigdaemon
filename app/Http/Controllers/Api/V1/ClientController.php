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
    // Мы говорим: "Дай мне всех клиентов, и СРАЗУ ЖЕ подгрузи для каждого из них все его проекты".
    // Это одна из самых важных техник оптимизации в Laravel.
    return auth()->user()->clients()->with('projects')->get();
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        // Валидация происходит автоматически в StoreClientRequest.
        // Мы просто создаем клиента с уже проверенными данными.
        $client = auth()->user()->clients()->create($request->validated());

        return response()->json($client, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        // Эта проверка ('view', $client) пока не работает, но мы ее скоро починим.
        // Она нужна, чтобы пользователь не мог посмотреть чужого клиента.
        $this->authorize('view', $client);

        return $client;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        // Проверка прав доступа
        $this->authorize('update', $client);

        // Валидация и обновление данных
        $client->update($request->validated());

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // Проверка прав доступа
        $this->authorize('delete', $client);

        $client->delete();

        // Успешный ответ без тела
        return response()->noContent();
    }
}