<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreManualTimeEntryRequest;
use App\Http\Requests\Api\V1\StoreTimeEntryRequest;
use App\Http\Requests\Api\V1\UpdateTimeEntryRequest;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class TimeEntryController extends Controller
{
    private function clearDashboardCache(): void
    {
        if (auth()->check()) {
            Cache::forget("user:".auth()->id().":dashboard-summary");
        }
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        return $project->timeEntries()->orderBy('start_time', 'desc')->get();
    }
    
    public function start(StoreTimeEntryRequest $request, Project $project)
    {
        $this->authorize('view', $project);
        $existing = TimeEntry::where('user_id', auth()->id())->whereNull('end_time')->first();
        if ($existing) {
            return response()->json(['message' => 'У вас уже есть активный таймер.'], Response::HTTP_CONFLICT);
        }
        $timeEntry = $project->timeEntries()->create([
            'user_id' => auth()->id(),
            'client_id' => $project->client_id, 
            'start_time' => now(),
            'description' => $request->validated('description'),
        ]);
        $this->clearDashboardCache();
        return response()->json($timeEntry, Response::HTTP_CREATED);
    }
	
	public function storeManual(StoreManualTimeEntryRequest $request, Project $project)
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
        $this->clearDashboardCache();
        return response()->json($entry, Response::HTTP_CREATED);
    }

    public function stop(TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        if ($timeEntry->end_time !== null) {
            return response()->json(['message' => 'Этот таймер уже остановлен.'], Response::HTTP_BAD_REQUEST);
        }
        $timeEntry->update(['end_time' => now()]);
        $this->clearDashboardCache();
        return response()->json($timeEntry->refresh());
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        $timeEntry->update($request->validated());
        $this->clearDashboardCache();
        return response()->json($timeEntry);
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);
        $timeEntry->delete();
        $this->clearDashboardCache();
        return response()->noContent();
    }
	
	public function getActive(Request $request)
    {
        return $request->user()->timeEntries()->whereNull('end_time')->with('project')->first();
    }
}