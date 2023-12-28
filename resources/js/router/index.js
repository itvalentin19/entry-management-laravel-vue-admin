import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    {
      path: '/',
      component: () => import('../layouts/default.vue'),
      children: [
        {
          path: 'dashboard',
          component: () => import('../pages/dashboard.vue'),
        },
        {
          path: 'users',
          component: () => import('../pages/user/user-list.vue'),
        },
        {
          path: 'users/user',
          component: () => import('../pages/user/user-create-edit.vue'),
        },
        // {
        //   path: 'owners',
        //   component: () => import('../pages/owner/owner-list.vue'),
        // },
        // {
        //   path: 'owners/owner',
        //   component: () => import('../pages/owner/owner-create-edit.vue'),
        // },
        {
          path: 'entities',
          component: () => import('../pages/entity/entity-list.vue'),
        },
        {
          path: 'entities/entity',
          component: () => import('../pages/entity/entity-create-edit.vue'),
        },
        {
          path: 'entities/entity/:id',
          component: () => import('../pages/entity/entity-view.vue'),
        },
        {
          path: 'account-settings',
          component: () => import('../pages/account-settings.vue'),
        },
      ],
    },
    {
      path: '/',
      component: () => import('../layouts/blank.vue'),
      children: [
        {
          path: 'login',
          component: () => import('../pages/login.vue'),
        },
        {
          path: 'register',
          component: () => import('../pages/register.vue'),
        },
        {
          path: 'reset-password',
          component: () => import('../pages/reset-password.vue'),
        },
        {
          path: 'forgot-password',
          component: () => import('../pages/forgot-password.vue'),
        },
        {
          path: '/:pathMatch(.*)*',
          component: () => import('../pages/[...all].vue'),
        },
      ],
    },
  ],
})

export default router
