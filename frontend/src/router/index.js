import Vue from 'vue';
import VueRouter from 'vue-router';
import LoginPage from '../components/Login.vue'; // Ajuste o nome da importação
import TaskList from '../components/TaskList.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/login',
    name: 'Login', // O nome da rota pode permanecer 'Login'
    component: LoginPage, // Use o novo nome do componente
  },
  {
    path: '/tasks',
    name: 'TaskList',
    component: TaskList,
    meta: { requiresAuth: true },
  },
  {
    path: '/',
    redirect: '/login',
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  if (to.matched.some(record => record.meta.requiresAuth) && !token) {
    next('/login');
  } else {
    next();
  }
});

export default router;