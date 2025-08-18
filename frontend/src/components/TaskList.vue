<template>
  <div class="task-list-container">
    <h2>Lista de Tarefas</h2>

    <!-- Filtros, Botão de Exportação, Botão de Log Out e Botão de Filtrar -->
    <div class="controls">
      <div class="filters">
        <select v-model="selectedStatus">
          <option value="">Todos os Status</option>
          <option v-for="status in statusOptions" :key="status.value" :value="status.value">
            {{ status.label }}
          </option>
        </select>
        <select v-model="selectedPriority">
          <option value="">Todas as Prioridades</option>
          <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
            {{ priority.label }}
          </option>
        </select>
        <button @click="fetchTasks" class="filter-btn">Filtrar</button>
      </div>
      <div class="buttons">
        <button @click="exportTasks" class="export-btn">Exportar para CSV</button>
        <button @click="logout" class="logout-btn">Log Out</button>
      </div>
    </div>

    <!-- Formulário para Criar/Editar Tarefa -->
    <div class="task-form">
      <h3>{{ isEditing ? 'Editar Tarefa' : 'Criar Nova Tarefa' }}</h3>
      <ValidationObserver ref="observer" v-slot="{ invalid }">
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="title">Título:</label>
            <ValidationProvider v-slot="{ errors }" name="title" rules="required">
              <input
                type="text"
                v-model="newTask.title"
                id="title"
                :class="{ 'is-invalid': errors.length > 0 }"
                required
              />
              <span v-if="errors.length > 0" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="description">Descrição:</label>
            <textarea
              v-model="newTask.description"
              id="description"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="status">Status:</label>
            <ValidationProvider v-slot="{ errors }" name="status" rules="required">
              <select
                v-model="newTask.status"
                id="status"
                :class="{ 'is-invalid': errors.length > 0 }"
                required
              >
                <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
              <span v-if="errors.length > 0" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="priority">Prioridade:</label>
            <ValidationProvider v-slot="{ errors }" name="priority" rules="required">
              <select
                v-model="newTask.priority"
                id="priority"
                :class="{ 'is-invalid': errors.length > 0 }"
                required
              >
                <option v-for="priority in priorityOptions" :key="priority.value" :value="priority.value">
                  {{ priority.label }}
                </option>
              </select>
              <span v-if="errors.length > 0" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="due_date">Data de Vencimento:</label>
            <input
              type="date"
              v-model="newTask.due_date"
              id="due_date"
            />
          </div>
          <button type="submit" v-if="!isEditing" :disabled="invalid">Adicionar Tarefa</button>
          <button type="button" @click="handleUpdate" v-if="isEditing" class="save-btn">Salvar</button>
          <button type="button" @click="cancelEdit" v-if="isEditing" class="cancel-btn">Cancelar</button>
        </form>
      </ValidationObserver>
      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="success" class="success">{{ success }}</p>
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
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="task in tasks" :key="task.id">
          <td>{{ task.title }}</td>
          <td>{{ task.description }}</td>
          <td>{{ formatStatus(task.status) }}</td>
          <td>{{ formatPriority(task.priority) }}</td>
          <td>{{ formatDate(task.due_date) }}</td>
          <td>
            <button @click="editTask(task)" class="edit-btn">Editar</button>
            <button @click="deleteTask(task.id)" class="delete-btn">Excluir</button>
          </td>
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
      allTasks: [], // Novo array para armazenar todas as tarefas sem filtragem
      selectedStatus: '',
      selectedPriority: '',
      error: null,
      success: null,
      newTask: {
        title: '',
        description: '',
        status: 'pending',
        priority: 'medium',
        due_date: '',
      },
      editTaskData: null,
      isEditing: false,
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
  methods: {
    async fetchTasks() {
      try {
        console.log('Parâmetros de filtragem:', { status: this.selectedStatus, priority: this.selectedPriority }); // Depuração dos parâmetros
        const params = {};
        if (this.selectedStatus) params.status = this.selectedStatus; // Só adiciona se não for vazio
        if (this.selectedPriority) params.priority = this.selectedPriority; // Só adiciona se não for vazio
        const response = await api.get('/tasks', { params });
        console.log('Resposta bruta da API:', response.data); // Depuração da resposta bruta
        this.allTasks = response.data; // Armazena todas as tarefas
        this.applyFilters(); // Aplica os filtros localmente
        this.error = null;
        this.success = null;
      } catch (error) {
        if (error.response?.status === 401) {
          this.error = 'Sessão expirada. Faça login novamente.';
          localStorage.removeItem('token');
          router.push('/login');
        } else {
          this.error = error.response?.data?.error || 'Erro ao carregar tarefas';
          this.tasks = [];
          console.error('Erro ao carregar tarefas:', error.response?.data || error); // Depuração do erro
        }
      }
    },
    applyFilters() {
      let filteredTasks = [...this.allTasks]; // Cria uma cópia das tarefas originais
      console.log('Tarefas antes do filtro:', filteredTasks); // Depuração antes do filtro
      if (this.selectedStatus) {
        filteredTasks = filteredTasks.filter(task => task.status === this.selectedStatus);
      }
      if (this.selectedPriority) {
        filteredTasks = filteredTasks.filter(task => task.priority === this.selectedPriority);
      }
      console.log('Tarefas após o filtro:', filteredTasks); // Depuração após o filtro
      this.tasks = filteredTasks; // Atualiza a lista exibida
    },
    async handleSubmit() {
      const isValid = await this.$refs.observer.validate();
      if (!isValid) return;

      if (this.isEditing) {
        await this.handleUpdate();
      } else {
        await this.createTask();
      }
    },
    async createTask() {
      try {
        console.log('Dados enviados:', this.newTask); // Depuração dos dados enviados
        const response = await api.post('/tasks', this.newTask);
        this.allTasks.push(response.data); // Adiciona à lista original
        this.applyFilters(); // Reaplica os filtros
        this.error = null;
        this.success = 'Tarefa criada com sucesso!';
        alert('Tarefa adicionada com sucesso!'); // Alerta de sucesso
        this.newTask = { title: '', description: '', status: 'pending', priority: 'medium', due_date: '' };
        setTimeout(() => (this.success = null), 3000);
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao criar tarefa';
        console.error('Erro ao criar tarefa:', error.response?.data || error); // Detalhes do erro no console
      }
    },
    editTask(task) {
      this.editTaskData = { ...task };
      this.newTask = {
        ...task,
        due_date: this.formatDateForInput(task.due_date), // Formata a data para o input
      };
      this.isEditing = true;
      this.error = null;
      this.success = null;
    },
    async handleUpdate() {
      if (!this.editTaskData) return;
      try {
        const payload = { ...this.newTask };
        if (payload.due_date) {
          payload.due_date = new Date(payload.due_date).toISOString().split('T')[0]; // Converte para yyyy-MM-dd
        }
        const response = await api.put(`/tasks/${this.editTaskData.id}`, payload);
        const index = this.allTasks.findIndex(t => t.id === this.editTaskData.id);
        if (index !== -1) this.allTasks[index] = response.data; // Atualiza a lista original
        this.applyFilters(); // Reaplica os filtros
        this.error = null;
        this.success = 'Tarefa atualizada com sucesso!';
        alert('Tarefa atualizada com sucesso!'); // Alerta de sucesso ao salvar
        this.isEditing = false;
        this.newTask = { title: '', description: '', status: 'pending', priority: 'medium', due_date: '' };
        this.editTaskData = null;
        setTimeout(() => (this.success = null), 3000);
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao atualizar tarefa';
        console.error('Erro ao atualizar tarefa:', error.response?.data || error);
      }
    },
    cancelEdit() {
      this.isEditing = false;
      this.newTask = { title: '', description: '', status: 'pending', priority: 'medium', due_date: '' };
      this.editTaskData = null;
      this.error = null;
      this.success = null;
    },
    async deleteTask(id) {
      if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
        try {
          await api.delete(`/tasks/${id}`);
          this.allTasks = this.allTasks.filter(task => task.id !== id); // Atualiza a lista original
          this.applyFilters(); // Reaplica os filtros
          this.error = null;
          this.success = 'Tarefa excluída com sucesso!';
          setTimeout(() => (this.success = null), 3000);
        } catch (error) {
          this.error = error.response?.data?.error || 'Erro ao excluir tarefa';
          console.error('Erro ao excluir tarefa:', error.response?.data || error);
        }
      }
    },
    async exportTasks() {
      try {
        const response = await api.get('/tasks/export', {
          params: {
            status: this.selectedStatus,
            priority: this.selectedPriority,
          },
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `tasks_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        this.error = null;
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao exportar tarefas';
        console.error('Erro ao exportar tarefas:', error.response?.data || error);
      }
    },
    formatDate(date) {
      if (!date) return '';
      return new Date(date).toISOString().split('T')[0]; // Converte para yyyy-MM-dd
    },
    formatDateForInput(date) {
      if (!date) return '';
      return new Date(date).toISOString().split('T')[0]; // Garante o formato yyyy-MM-dd para o input
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
    logout() {
      localStorage.removeItem('token');
      router.push('/login');
    },
  },
};
</script>

<style scoped>
/* (Mantém os estilos existentes) */
.task-list-container {
  max-width: 900px;
  margin: 20px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Títulos */
h2,
h3 {
  color: #333;
  margin-bottom: 15px;
  font-weight: 600;
}
h3 {
  font-size: 1.5em;
  margin-bottom: 10px;
}

/* Controles (Filtros e Botões) */
.controls {
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.filters {
  display: flex;
  gap: 10px;
  align-items: center;
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

.buttons {
  display: flex;
  gap: 10px;
}

.filter-btn {
  padding: 10px 20px;
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.filter-btn:hover {
  background-color: #5a6268;
}

.export-btn {
  padding: 10px 20px;
  background-color: #17a2b8;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.export-btn:hover {
  background-color: #138496;
}

.logout-btn {
  padding: 10px 20px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.logout-btn:hover {
  background-color: #c82333;
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

.form-group .is-invalid {
  border-color: #dc3545;
}

.form-group .text-danger {
  color: #dc3545;
  font-size: 0.9em;
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
  margin-right: 10px;
}

button:hover {
  background-color: #0056b3;
}

.save-btn {
  background-color: #28a745;
}

.save-btn:hover {
  background-color: #218838;
}

.cancel-btn {
  background-color: #dc3545;
}

.cancel-btn:hover {
  background-color: #c82333;
}

/* Botões de Ações na Tabela */
.edit-btn {
  background-color: #28a745;
  margin-right: 5px;
}

.edit-btn:hover {
  background-color: #218838;
}

.delete-btn {
  background-color: #dc3545;
}

.delete-btn:hover {
  background-color: #c82333;
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

/* Mensagens */
.error {
  color: #dc3545;
  margin-top: 10px;
  padding: 8px;
  background-color: #f8d7da;
  border-radius: 4px;
}

.success {
  color: #28a745;
  margin-top: 10px;
  padding: 8px;
  background-color: #d4edda;
  border-radius: 4px;
}

/* Responsividade */
@media (max-width: 600px) {
  .task-list-container {
    margin: 10px;
    padding: 10px;
  }

  .controls {
    flex-direction: column;
    align-items: flex-start;
  }

  .filters {
    flex-direction: column;
    gap: 5px;
  }

  .buttons {
    flex-direction: column;
    width: 100%;
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

  .task-table td button {
    padding: 6px 10px;
    font-size: 0.9em;
  }
}
</style>