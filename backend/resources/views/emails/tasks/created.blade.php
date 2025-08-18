@component('mail::message')
# Nova Tarefa Criada

Uma nova tarefa foi criada no sistema.

**Título:** {{ $task->title }}  
**Descrição:** {{ $task->description ?? 'Sem descrição' }}  
**Status:** {{ ucfirst($task->status) }}  
**Prioridade:** {{ ucfirst($task->priority) }}  
**Data de Vencimento:** {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'Sem prazo definido' }}

<!-- @component('mail::button', ['url' => config('app.url') . '/tasks'])
Ver Tarefas
@endcomponent -->

Obrigado,<br>
@endcomponent