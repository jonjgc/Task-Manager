<template>
  <div class="login-wrapper">
    <div class="login-container">
      <h2>{{ isRegister ? 'Registrar' : 'Login' }}</h2>
      <ValidationObserver ref="observer" v-slot="{ invalid }">
        <form @submit.prevent="isRegister ? handleRegister() : handleLogin()">

          <div v-if="isRegister" class="form-group">
            <label for="name">Nome:</label>
            <ValidationProvider name="name" :rules="{ required: true, max: 255 }" v-slot="{ errors }">
              <input
                type="text"
                v-model="credentials.name"
                id="name"
                :class="{ 'is-invalid': errors.length > 0 }"
              />
              <span v-if="errors[0]" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-group">
            <label for="email">E-mail:</label>
            <ValidationProvider name="email" :rules="{ required: true, email: true, max: 255 }" v-slot="{ errors }">
              <input
                type="email"
                v-model="credentials.email"
                id="email"
                :class="{ 'is-invalid': errors.length > 0 }"
              />
              <span v-if="errors[0]" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-group">
            <label for="password">Senha:</label>
            <ValidationProvider name="password" :rules="{ required: true, min: 6 }" v-slot="{ errors }">
              <input
                type="password"
                v-model="credentials.password"
                id="password"
                :class="{ 'is-invalid': errors.length > 0 }"
              />
              <span v-if="errors[0]" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div v-if="isRegister" class="form-group">
            <label for="company_name">Nome da Empresa:</label>
            <ValidationProvider name="company_name" :rules="{ required: true, max: 255 }" v-slot="{ errors }">
              <input
                type="text"
                v-model="credentials.company_name"
                id="company_name"
                :class="{ 'is-invalid': errors.length > 0 }"
              />
              <span v-if="errors[0]" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div v-if="isRegister" class="form-group">
            <label for="company_identifier">Identificador da Empresa:</label>
            <ValidationProvider name="company_identifier" :rules="{ required: true, max: 255 }" v-slot="{ errors }">
              <input
                type="text"
                v-model="credentials.company_identifier"
                id="company_identifier"
                :class="{ 'is-invalid': errors.length > 0 }"
              />
              <span v-if="errors[0]" class="text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <button type="submit" :disabled="invalid">{{ isRegister ? 'Registrar' : 'Entrar' }}</button>

          <p v-if="error" class="error">{{ error }}</p>

          <p class="toggle-link">
            {{ isRegister ? 'Já tem uma conta?' : 'Não tem uma conta?' }}
            <a href="#" @click.prevent="toggleForm">{{ isRegister ? 'Faça Login' : 'Registre-se' }}</a>
          </p>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ValidationObserver, ValidationProvider, extend } from "vee-validate";

// Regras de validação
extend("max", {
  validate(value, { length }) {
    return value.length <= length;
  },
  params: ["length"],
  message: "O campo deve ter no máximo {length} caracteres.",
});

extend("required", {
  validate(value) {
    return !!value;
  },
  message: "Este campo é obrigatório.",
});

extend("email", {
  validate(value) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(value);
  },
  message: "Digite um e-mail válido.",
});

extend("min", {
  validate(value, { length }) {
    return value.length >= length;
  },
  params: ["length"],
  message: "O campo deve ter no mínimo {length} caracteres.",
});

export default {
  name: "LoginPage",
  components: {
    ValidationObserver,
    ValidationProvider,
  },
  data() {
    return {
      credentials: {
        name: "",
        email: "",
        password: "",
        company_name: "",
        company_identifier: "",
      },
      error: null,
      isRegister: false,
      api: axios.create({
        baseURL: "http://localhost:8000/api", // ajuste a URL da sua API
      }),
    };
  },
  methods: {
    toggleForm() {
      this.isRegister = !this.isRegister;
      this.credentials = { name: "", email: "", password: "", company_name: "", company_identifier: "" };
      this.error = null;
    },
    async handleLogin() {
      const isValid = await this.$refs.observer.validate();
      if (!isValid) return;

      try {
        const response = await this.api.post("/login", {
          email: this.credentials.email,
          password: this.credentials.password,
        });
        // supondo que a API retorne { token, user: { name, company_name } }
        localStorage.setItem("token", response.data.token);
        localStorage.setItem("user", JSON.stringify(response.data.user));

        this.error = null;
        this.$router.push("/tasks");
      } catch (error) {
        this.error = error.response?.data?.error || "Erro ao fazer login";
        console.error("Erro ao fazer login:", error.response?.data || error);
      }
    },
    async handleRegister() {
      const isValid = await this.$refs.observer.validate();
      if (!isValid) return;

      try {
        const response = await this.api.post("/register", this.credentials);
        // supondo que a API retorne { token, user: { name, company_name } }
        localStorage.setItem("token", response.data.token);
        localStorage.setItem("user", JSON.stringify(response.data.user));

        this.error = null;
        this.$router.push("/tasks");
      } catch (error) {
        this.error = error.response?.data?.error || "Erro ao registrar";
        console.error("Erro ao registrar:", error.response?.data || error);
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
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 20px;
}

.login-container {
  max-width: 400px;
  width: 100%;
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
  margin: 0 auto;
  display: block;
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

.toggle-link {
  text-align: center;
  margin-top: 10px;
}

.toggle-link a {
  color: #007bff;
  text-decoration: none;
  margin-left: 5px;
}

.toggle-link a:hover {
  text-decoration: underline;
}
</style>