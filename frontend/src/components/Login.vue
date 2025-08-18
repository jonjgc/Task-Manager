<template>
  <div class="login-wrapper">
    <div class="login-container">
      <h2>Login</h2>
      <ValidationObserver ref="observer" v-slot="{ invalid }">
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="email">E-mail:</label>
            <ValidationProvider v-slot="{ errors }" name="email" rules="required|email">
              <input
                type="email"
                v-model="credentials.email"
                id="email"
                :class="{ 'is-invalid': errors.length > 0 }"
                required
              />
              <span v-if="errors.length > 0" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="password">Senha:</label>
            <ValidationProvider v-slot="{ errors }" name="password" rules="required|min:6">
              <input
                type="password"
                v-model="credentials.password"
                id="password"
                :class="{ 'is-invalid': errors.length > 0 }"
                required
              />
              <span v-if="errors.length > 0" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <button type="submit" :disabled="invalid">Entrar</button>
          <p v-if="error" class="error">{{ error }}</p>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>

<script>
import api from '../utils/auth';
import router from '../router';

export default {
  name: 'LoginPage',
  data() {
    return {
      credentials: {
        email: '',
        password: '',
      },
      error: null,
    };
  },
  methods: {
    async handleLogin() {
      const isValid = await this.$refs.observer.validate();
      if (!isValid) return;

      try {
        const response = await api.post('/login', this.credentials);
        localStorage.setItem('token', response.data.token); // Supondo que o backend retorne um token
        this.error = null;
        router.push('/tasks'); // Redireciona para a lista de tarefas
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao fazer login';
        console.error('Erro ao fazer login:', error.response?.data || error);
      }
    },
  },
};
</script>

<style scoped>
/* Contêiner externo para centralização vertical e horizontal */
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh; /* Garante que ocupe toda a altura da viewport */
  background-color: #f5f5f5; /* Fundo opcional para contraste */
  padding: 20px;
}

.login-container {
  max-width: 400px;
  width: 100%; /* Garante que ocupe a largura máxima permitida */
  padding: 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #333;
  text-align: center;
  margin-bottom: 20px;
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

.form-group input {
  width: 90%;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1em;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.form-group .is-invalid {
  border-color: #dc3545;
}

.form-group .text-danger {
  color: #dc3545;
  font-size: 0.9em;
}

button {
  width: 40%;
  padding: 10px;
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

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error {
  color: #dc3545;
  margin-top: 10px;
  padding: 8px;
  background-color: #f8d7da;
  border-radius: 4px;
  text-align: center;
}
</style>