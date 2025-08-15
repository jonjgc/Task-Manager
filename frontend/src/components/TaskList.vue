<template>
  <div class="task-list-container">
    <h2>Lista de Tarefas</h2>

    <!-- Filtros -->
    <div class="filters">
      <select v-model="selectedStatus" @change="fetchTasks">
        <option value="">Todos os Status</option>
        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
          {{ status.label }}
        </option>
      </select>
      <select v-model="selectedPriority" @change="fetchTasks">
        <option value="">Todas as Prioridades</option>
        <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
          {{ priority.label }}
        </option>
      </select>
    </div>

    <!-- Mensagem de Erro -->
    <p v-if="error" class="error">{{ error }}</p>

    <!-- Lista de Tarefas -->
    <table v-if="tasks.length > 0" class="task-table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Descrição</th>
          <th>Status</th>
          <th>Prioridade</th>
          <th>Data de Vencimento</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="task in tasks" :key="task.id">
          <td>{{ task.title }}</td>
          <td>{{ task.description }}</td>
          <td>{{ formatStatus(task.status) }}</td>
          <td>{{ formatPriority(task.priority) }}</td>
          <td>{{ task.due_date }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else-if="!error">Nenhuma tarefa encontrada.</p>
  </div>
</template>

<script>
import api from '../utils/auth';
import router from '../router'; // Importa o router para redirecionamento

export default {
  name: 'TaskList',
  data() {
    return {
      tasks: [],
      selectedStatus: '',
      selectedPriority: '',
      error: null,
      statusOptions: [
        { value: 'pending', label: 'Pendente' },
        { value: 'in_progress', label: 'Em Andamento' },
        { value: 'completed', label: 'Concluída' },
      ],
      priorityOptions: [
        { value: 'low', label: 'Baixa' },
        { value: 'medium', label: 'Média' },
        { value: 'high', label: 'Alta' },
      ],
    };
  },
  mounted() {
    this.fetchTasks();
  },
  watch: {
    // Recarrega as tarefas quando os filtros mudam
    selectedStatus() {
      this.fetchTasks();
    },
    selectedPriority() {
      this.fetchTasks();
    },
  },
  methods: {
    async fetchTasks() {
      try {
        const response = await api.get('/tasks', {
          params: {
            status: this.selectedStatus,
            priority: this.selectedPriority,
          },
        });
        this.tasks = response.data; // Ajuste se a API retornar { data: [...] }
        this.error = null;
      } catch (error) {
        if (error.response?.status === 401) {
          // Token inválido ou expirado
          this.error = 'Sessão expirada. Faça login novamente.';
          localStorage.removeItem('token'); // Remove token inválido
          router.push('/login'); // Redireciona para login
        } else {
          this.error = error.response?.data?.error || 'Erro ao carregar tarefas';
          this.tasks = [];
        }
      }
    },
    formatStatus(status) {
      const statuses = {
        pending: 'Pendente',
        in_progress: 'Em Andamento',
        completed: 'Concluída',
      };
      return statuses[status] || status;
    },
    formatPriority(priority) {
      const priorities = {
        low: 'Baixa',
        medium: 'Média',
        high: 'Alta',
      };
      return priorities[priority] || priority;
    },
  },
};
</script>

<style scoped>
.task-list-container {
  max-width: 800px;
  margin: 20px auto;
  padding: 20px;
}

.filters {
  margin-bottom: 20px;
}

.filters select {
  padding: 5px;
  margin-right: 10px;
}

.task-table {
  width: 100%;
  border-collapse: collapse;
}

.task-table th,
.task-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.task-table th {
  background-color: #f2f2f2;
}

.error {
  color: red;
  margin-bottom: 10px;
}
</style>