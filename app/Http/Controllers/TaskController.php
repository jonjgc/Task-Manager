<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendTaskNotification;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for the authenticated user's company.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tasks = Task::where('company_id', $user->company_id)->get();
        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        // Dispatch the notification job
        SendTaskNotification::dispatch($task);

        return response()->json($task, 201);
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        if ($task->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Unauthorized: Task does not belong to your company'], 403);
        }

        return response()->json($task, 200);
    }

    /**
     * Update the specified task in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        if ($task->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Unauthorized: Task does not belong to your company'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the task is being marked as completed
        $isNowCompleted = $request->status === 'completed' && $task->status !== 'completed';

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        // Dispatch notification if task is newly completed
        if ($isNowCompleted) {
            SendTaskNotification::dispatch($task);
        }

        return response()->json($task, 200);
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        if ($task->company_id !== auth()->user()->company_id) {
            return response()->json(['error' => 'Unauthorized: Task does not belong to your company'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}