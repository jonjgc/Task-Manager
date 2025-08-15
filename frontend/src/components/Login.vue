<template>
  <div class="login-container">
    <h2>{{ isRegistering ? 'Registrar' : 'Login' }}</h2>
    <form @submit.prevent="handleSubmit" v-if="!isAuthenticated">
      <div class="form-group" v-if="isRegistering">
        <label for="name">Nome:</label>
        <input type="text" v-model="name" id="name" v-validate="'required|min:3'" :class="{ 'error': errors.has('name') }" />
        <span v-show="errors.has('name')" class="error">{{ errors.first('name') }}</span>
      </div>
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" v-model="email" id="email" v-validate="'required|email'" :class="{ 'error': errors.has('email') }" />
        <span v-show="errors.has('email')" class="error">{{ errors.first('email') }}</span>
      </div>
      <div class="form-group">
        <label for="password">Senha:</label>
        <input type="password" v-model="password" id="password" v-validate="'required|min:6'" :class="{ 'error': errors.has('password') }" />
        <span v-show="errors.has('password')" class="error">{{ errors.first('password') }}</span>
      </div>
      <button type="submit" :disabled="errors.any()">{{ isRegistering ? 'Registrar' : 'Entrar' }}</button>
      <p v-if="error" class="error">{{ error }}</p>
      <p>
        {{ isRegistering ? 'Já tem conta?' : 'Não tem conta?' }}
        <a href="#" @click.prevent="toggleForm">{{ isRegistering ? 'Faça login' : 'Registre-se' }}</a>
      </p>
    </form>
    <div v-else>
      <p v-if="user">Bem-vindo, {{ user.name }} ({{ user.company?.name || 'Sem empresa' }})</p>
      <p v-else>Carregando usuário...</p>
      <button @click="logout">Sair</button>
    </div>
  </div>
</template>

<script>
import { login, register, logout } from '../utils/auth';
import router from '../router'; // Importado para redirecionamento

export default {
  name: 'LoginPage',
  data() {
    return {
      name: '',
      email: '',
      password: '',
      error: null,
      user: null,
      isRegistering: false,
    };
  },
  computed: {
    isAuthenticated() {
      return !!localStorage.getItem('token');
    },
  },
  mounted() {
    if (this.isAuthenticated) {
      this.loadUser();
    }
  },
  methods: {
    async handleSubmit() {
      this.$validator.validateAll().then(async (result) => {
        if (result) {
          try {
            if (this.isRegistering) {
              await register(this.name, this.email, this.password);
              this.error = null;
              this.toggleForm();
            } else {
              const response = await login(this.email, this.password);
              localStorage.setItem('token', response.token);
              this.user = response.user;
              this.error = null;
              this.$router.push('/tasks');
            }
          } catch (error) {
            this.error = error.response?.data?.error || 'Erro na operação';
          }
        }
      });
    },
    toggleForm() {
      this.isRegistering = !this.isRegistering;
      this.error = null;
      this.name = '';
      this.email = '';
      this.password = '';
      this.$validator.reset();
    },
    async loadUser() {
      try {
        const response = await login(this.user?.email || '', this.user?.password || ''); // Placeholder, ajustar conforme API
        this.user = response.user;
        this.error = null;
      } catch (error) {
        this.error = error.response?.data?.error || 'Erro ao carregar usuário';
        localStorage.removeItem('token'); // Remove token inválido
        router.push('/login'); // Redireciona para login
      }
    },
    logout,
  },
};
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  display: block;
  margin-bottom: 5px;
}
input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
button {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:hover {
  background-color: #0056b3;
}
.error {
  color: red;
  margin-top: 10px;
}
</style>