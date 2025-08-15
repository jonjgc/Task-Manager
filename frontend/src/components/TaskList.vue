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

    <!-- Formulário para Criar Tarefa -->
    <div class="task-form">
      <h3>Criar Nova Tarefa</h3>
      <form @submit.prevent="createTask">
        <div class="form-group">
          <label for="title">Título:</label>
          <input type="text" v-model="newTask.title" id="title" required />
        </div>
        <div class="form-group">
          <label for="description">Descrição:</label>
          <textarea v-model="newTask.description" id="description"></textarea>
        </div>
        <div class="form-group">
          <label for="status">Status:</label>
          <select v-model="newTask.status" id="status" required>
            <option v-for="status in statusOptions" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="priority">Prioridade:</label>
          <select v-model="newTask.priority" id="priority" required>
            <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
              {{ priority.label }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="due_date">Data de Vencimento:</label>
          <input type="date" v-model="newTask.due_date" id="due_date" />
        </div>
        <button type="submit">Adicionar Tarefa</button>
      </form>
      <p v-if="error" class="error">{{ error }}</p>
    </div>

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
import router from '../router';

export default {
  name: 'TaskList',
  data() {
    return {
      tasks: [],
      selectedStatus: '',
      selectedPriority: '',
      error: null,
      newTask: {
        title: '',
        description: '',
        status: 'pending',
        priority: 'medium',
        due_date: '',
      },
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
        this.tasks = response.data;
        this.error = null;
      } catch (error) {
        if (error.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
          localStorage.removeItem('token');
          router.push('/login');
        } else {
          this.error = error.response?.data?.error || 'Erro ao carregar tarefas';
          this.tasks = [];
        }
      }
    },
    async createTask() {
      try {
        const response = await api.post('/tasks', this.newTask);
        this.tasks.push(response.data);
        this.error = null;
        this.newTask = { title: '', description: '', status: 'pending', priority: 'medium', due_date: '' }; // Limpa o formulário
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao criar tarefa';
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
/* Container Principal */
.task-list-container {
  max-width: 900px;
  margin: 20px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Títulos */
h2, h3 {
  color: #333;
  margin-bottom: 15px;
  font-weight: 600;
}
h3 {
  font-size: 1.5em;
  margin-bottom: 10px;
}

/* Filtros */
.filters {
  margin-bottom: 20px;
  display: flex;
  gap: 10px;
}

.filters select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  font-size: 1em;
  transition: border-color 0.3s ease;
}

.filters select:hover,
.filters select:focus {
  border-color: #007bff;
  outline: none;
}

/* Formulário de Tarefa */
.task-form {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1em;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

button {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #0056b3;
}

/* Tabela de Tarefas */
.task-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.task-table th,
.task-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
  vertical-align: middle;
}

.task-table th {
  background-color: #f2f2f2;
  color: #333;
  font-weight: 600;
}

.task-table tr:nth-child(even) {
  background-color: #f8f9fa;
}

.task-table tr:hover {
  background-color: #e9ecef;
  transition: background-color 0.3s ease;
}

/* Mensagens de Erro */
.error {
  color: #dc3545;
  margin-top: 10px;
  padding: 8px;
  background-color: #f8d7da;
  border-radius: 4px;
}

/* Responsividade */
@media (max-width: 600px) {
  .task-list-container {
    margin: 10px;
    padding: 10px;
  }

  .filters {
    flex-direction: column;
    gap: 5px;
  }

  .form-group input,
  .form-group textarea,
  .form-group select {
    font-size: 0.9em;
  }

  .task-table th,
  .task-table td {
    padding: 8px;
    font-size: 0.9em;
  }
}
</style>