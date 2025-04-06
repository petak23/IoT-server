import { createRouter, createWebHistory } from 'vue-router'
import HomePageView from '../views/HomePageView.vue'
import UserView from '../views/UserView.vue'
import DevicesView from '../views/DevicesView.vue'
import DeviceView from '../views/DeviceView.vue'
import UnitsView from '../views/UnitsView.vue'
import { useMainStore } from '../store/main'

const routes = [
	{
		path: '/',
		name: 'Domov',
		component: HomePageView
	},
	{
		path: '/user',
		name: 'Môj účet',
		component: UserView
	},
	{
		path: '/devices',
		name: 'Zariadenia',
		component: DevicesView
	},
	{
		path: '/device/:id',
		name: 'Zariadenie',
		props: true,
		component: DeviceView
	},
	{
		path: '/units',
		name: 'Jednotky',
		component: UnitsView
		// route level code-splitting
		// this generates a separate chunk (About.[hash].js) for this route
		// which is lazy-loaded when the route is visited.
		//component: () => import('../views/UnitsView.vue')
	}
]

const basePath = document.getElementById('app').dataset.basePath

const router = createRouter({
	history: createWebHistory(basePath.substring(1)+"/front/"),
	routes
})

router.beforeEach((to) => {
	const store = useMainStore()
})

export default router
