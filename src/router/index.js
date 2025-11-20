import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import QuizPlayerView from '../views/QuizPlayerView.vue'
import AuthView from "@/views/AuthView.vue";
import UserProfileView from "@/views/UserProfileView.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomeView
        },
        {
            path: '/login',
            name: 'login',
            component: AuthView
        },
        {
            path: '/play',
            name: 'play',
            component: QuizPlayerView
        },
        {
            path: '/profile',
            name: 'profile',
            component: UserProfileView
        }
    ]
})

export default router