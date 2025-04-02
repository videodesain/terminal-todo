import { createRouter, createWebHistory } from 'vue-router'
import TaskList from '../components/Tasks/List.vue'
import TaskCreate from '../components/Tasks/Create.vue'
import TaskShow from '../components/Tasks/Show.vue'

const routes = [
  {
    path: '/',
    redirect: '/tasks'
  },
  {
    path: '/tasks',
    name: 'tasks.index',
    component: TaskList
  },
  {
    path: '/tasks/create',
    name: 'tasks.create',
    component: TaskCreate
  },
  {
    path: '/tasks/:id',
    name: 'tasks.show',
    component: TaskShow,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router 