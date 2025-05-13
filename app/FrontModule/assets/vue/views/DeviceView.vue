<script setup>
import { onMounted, ref, watch} from 'vue'
import { RouterLink } from 'vue-router';
import MainService from '../services/MainService'
import Device from '../components/Devices/Device.vue'
import DeviceEdit from '../components/Devices/Device_edit.vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const props = defineProps({
	id: { type: Number, default: 0 },
	edit: { type: Boolean, default: false},
})

const device_one = ref(null)
const error_message = ref("")

const setError = (message) => {
	error_message.value = message
	//device_one.value = null
	console.error(error_message.value)
}

const  getDevice = async (id) => {
	await MainService.getDevice(id)
		.then(response => {
			if (response.data.status == 200) {
				console.log(response.data)
				device_one.value = response.data
				console.log(device_one.value);
				
			}
			else {
				setError(response.data.message)
				console.log(response.data)
			}
		})
		.catch((error) => {
			setError(error)
			console.error(error)
		})
}

watch(() => props.id, () => {
	if (props.id > 0) getDevice(props.id)
	else setError("Chybné id: [" + props.id + "] zariadenia.(1)")
})

watch(() => route.params.id, (newId) => {
	if (newId > 0) getDevice(newId)
	else setError("Chybné id: [" + newId + "] zariadenia.(3)")
})

onMounted(()=> {
	if (props.id > 0) getDevice(props.id)
	else if (route.params.id > 0) getDevice(route.params.id) 
	else setError("Chybné id: [" + props.id + "] zariadenia.(2)")
})
</script>

<template>
	<device v-if="device_one != null && !props.edit" :device_item="device_one" />
	<div class="col-12" v-else-if="device_one != null && props.edit">
		<h2>Editácia zariadenia: {{ device_one.name }}</h2>
		<device-edit 
			:default-values="device_one"
		/>
	</div>
	<div v-else-if="error_message != ''" class="row px-2 py-3">
		<div class="col-12 alert alert-danger" role="alert">{{ error_message }}</div>
	</div>
	<div v-else class="col-12 p-5">
		<div class="spinner-border text-secondary" role="status">
			<span class="visually-hidden">Loading...</span>
		</div>
	</div>

	<div class="row py-3">
		<div class="col-12">
			<RouterLink to="/devices" class="btn btn-outline-primary btn-sm">
				Späť na zoznam zariadení <i class="fa-solid fa-rotate-left"></i>
			</RouterLink>
		</div>
	</div>
</template>