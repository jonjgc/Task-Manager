<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Mail\TaskCreatedMail;
use Illuminate\Support\Facades\Mail;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tasks = Task::where('user_id', $user->id)
            ->orWhere('company_id', $user->company_id)
            ->get();
        return response()->json($tasks, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create(array_merge($validated, [
            'user_id' => auth()->user()->id,
            'company_id' => auth()->user()->company_id,
        ]));

        // Enviar notificação por e-mail
        Mail::to(auth()->user()->email)->send(new TaskCreatedMail($task));

        return response()->json($task, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Verifica se o usuário tem permissão (pertence ao user_id ou company_id)
        $user = auth()->user();
        if ($task->user_id !== $user->id && $task->company_id !== $user->company_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Verifica se o usuário tem permissão
        $user = auth()->user();
        if ($task->user_id !== $user->id && $task->company_id !== $user->company_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Tarefa excluída com sucesso'], 200);
    }

    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        $tasks = Task::where('user_id', $user->id)
            ->orWhere('company_id', $user->company_id)
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->has('priority'), function ($query) use ($request) {
                return $query->where('priority', $request->priority);
            })
            ->get();

        $filename = 'tasks_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($tasks) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['ID', 'Título', 'Descrição', 'Status', 'Prioridade', 'Data de Vencimento', 'Criado em', 'Atualizado em']);

            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->status,
                    $task->priority,
                    $task->due_date,
                    $task->created_at,
                    $task->updated_at,
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
