import { api } from 'src/boot/axios';

// Existing route guards
const loginRules = (to, from, next) => {
  const user_data = JSON.parse(sessionStorage.getItem("user"));

  if (!user_data) {
    if (to.path === '/auth/login') {
      next();
    } else {
      next({ path: '/auth/login' });
    }
  } else {
    if (to.path === '/auth/login') {
      if (!user_data.role) {
        next({ path: '/auth/login' });
      } else if (user_data.role === 'course-moderator') {
        next({ path: '/c/' });
      } else {
        next({ path: '/app' });
      }
    } else {
      if (!user_data.role) {
        next();
      } else {
        next();
      }
    }
  }
};

// Main routes array
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
  },

  {
    path: '/auth',
    beforeEnter: (to, from, next) => { loginRules(to, from, next) },
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '/auth/login', component: () => import('pages/LoginPage.vue') }
    ]
  },

  {
    path: '/app',
    meta: { role: 'student' },
    component: () => import('layouts/AppLayout.vue'),
    children: [
      { path: '', component: () => import('pages/NotesPage.vue') },

    ]
  },

  {
    path: '/c',
    meta: { role: 'course-moderator' },
    beforeEnter: (to, from, next) => { loginRules(to, from, next) },
    component: () => import('layouts/CourseAdminLayout.vue'),

  },

  {
    path: '/enroll-user',
    component: () => import('pages/BioEnroll.vue')
  },


  // Always leave this as the last one
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
];

export default routes;
