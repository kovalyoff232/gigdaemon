<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTimeEntryRequest;
use App\Http\Requests\Api\V1\UpdateTimeEntryRequest;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\Api\V1\StoreManualTimeEntryRequest;

class TimeEntryController extends Controller
{
    // Получить все записи времени для конкретного проекта
    public function index(Project $project)
    {
        // Убедимся, что пользователь имеет доступ к проекту
        $this->authorize('view', $project);

        return $project->timeEntries()->orderBy('start_time', 'desc')->get();
    }
    
    // Запустить новый таймер
    public function start(StoreTimeEntryRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        $existing = TimeEntry::where('user_id', auth()->id())->whereNull('end_time')->first();
        if ($existing) {
            return response()->json(['message' => 'У вас уже есть активный таймер.'], Response::HTTP_CONFLICT);
        }

        $timeEntry = $project->timeEntries()->create([
            'user_id' => auth()->id(),
            // === ИЗМЕНЕНИЕ ЗДЕСЬ ===
            // Мы явно добавляем ID клиента при создании записи времени.
            'client_id' => $project->client_id, 
            'start_time' => now(),
            'description' => $request->validated('description'),
        ]);

        return response()->json($timeEntry, Response::HTTP_CREATED);
    }
	
	
	public function storeManual(StoreManualTimeEntryRequest $request, \App\Models\Project $project)
{
    $this->authorize('view', $project);

    $validated = $request->validated();
    
    $entry = $project->timeEntries()->create([
        'user_id' => auth()->id(),
        'client_id' => $project->client_id,
        'description' => $validated['description'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
    ]);

    return response()->json($entry, \Illuminate\Http\Response::HTTP_CREATED);
}
	

    // Остановить активный таймер
    public function stop(TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        if ($timeEntry->end_time !== null) {
            return response()->json(['message' => 'Этот таймер уже остановлен.'], Response::HTTP_BAD_REQUEST);
        }

        $timeEntry->update(['end_time' => now()]);

        // ИЗМЕНЕНИЕ: Мы принудительно обновляем модель из базы данных
        // и возвращаем ее. Теперь в ней гарантированно есть все, включая 'duration'.
        return response()->json($timeEntry->refresh());
    }

    // Обновить существующую запись
    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        $timeEntry->update($request->validated());

        return response()->json($timeEntry);
    }

    // Удалить запись
    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);
        
        $timeEntry->delete();

        return response()->noContent();
    }
	
	
	public function getActive(Request $request)
    {
        // Просто ищем запись текущего пользователя, у которой нет времени окончания
        return $request->user()
            ->timeEntries()
            ->whereNull('end_time')
            ->with('project') // и сразу подгружаем проект, чтобы знать, над чем идет работа
            ->first();
    }
}