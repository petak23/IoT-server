import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useMainStore = defineStore('main', () => {
  /*const count = ref(0)
  const doubleCount = computed(() => count.value * 2)
  function increment() {
    count.value++
  }*/
	const baseUrl = ref(document.getElementById('app').dataset.baseUrl)

	const apiPath = computed(() => baseUrl.value + "api/") // Cesta k API

	const appName = ref("")  // Meno aplikácie

	const links = ref([]) // Pole odkazov

	const dataRetentionDays = ref(0)
			
	const minYear = ref(2000)

	const user = ref(null)

	const main_menu = ref([
		/*{
			view_in_main_menu: true,
			path: '/',
			name: 'Domov',
			component: HomePageView
		},
		{
			view_in_main_menu: true,
			path: '/user',
			name: 'Môj účet',
			component: UserView
		},
		{
			view_in_main_menu: true,
			path: '/devices',
			name: 'Zariadenia',
			component: DevicesView
		},
		{
			view_in_main_menu: false,
			path: '/device/:id',
			name: 'Zariadenie',
			props: true,
			component: DeviceView
		},
		{
			view_in_main_menu: true,
			path: '/units',
			name: 'Jednotky',
			component: UnitsView
		}*/
	])

  return { baseUrl, apiPath, appName, links, dataRetentionDays, minYear, user, main_menu }
})
