<script setup>
import { onMounted, ref, watch} from 'vue'
import { RouterLink } from 'vue-router';
import MainService from '../services/MainService'
import Device from '../components/Devices/Device.vue'

const props = defineProps({
	id: { type: Number, default: 0 }
})

const device = ref(null)
const error_message = ref("")

const setError = (message) => {
	error_message.value = message
	device.value = null
}

const getDevice = () => {
	MainService.getDevice(props.id)
		.then(response => {
			if (response.data.status == 200) device.value = response.data
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
	if (props.id > 0) getDevice()
	else setError("Chybné id: [" + props.id + "] zariadenia.")
})

onMounted(()=> {
	if (props.id > 0) getDevice()
	else setError("Chybné id: [" + props.id + "] zariadenia.")
})
</script>

<template>
	<device 
		v-if="device != null"
		:id_device="props.id"
		:device="device"
	/>
	<div v-else class="row px-2 py-3">
		<div class="col-12 alert alert-danger" role="alert" v-html="error_message"></div>
	</div>

	<div class="row px-2 py-3">
		<div class="col-12">
			<RouterLink to="/devices" class="btn btn-primary">
				Späť na zoznam zariadení <i class="fa-solid fa-rotate-left"></i>
			</RouterLink>
		</div>
	</div>
</template>