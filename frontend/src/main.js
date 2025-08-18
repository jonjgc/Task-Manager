import Vue from 'vue';
import App from './App.vue';
import router from './router';
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate';
import { required } from 'vee-validate/dist/rules';
import { email } from 'vee-validate/dist/rules';
import { min } from 'vee-validate/dist/rules';

Vue.component('ValidationProvider', ValidationProvider);
Vue.component('ValidationObserver', ValidationObserver);

// Definir regras de validação
extend('required', {
  ...required,
  message: 'Este campo é obrigatório',
});

extend('email', {
  ...email,
  message: 'Este campo deve ser um e-mail válido',
});

extend('min', {
  ...min,
  message: 'Este campo deve ter pelo menos {length} caracteres',
});

Vue.config.productionTip = false;

new Vue({
  router,
  render: h => h(App),
}).$mount('#app');