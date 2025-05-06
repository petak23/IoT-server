<script setup>
import { reactive, computed, ref } from 'vue'
import MainService from '../../services/MainService'

const props = defineProps({
	defaultValues: {
		type: Object,
		default: () => ({})
	}
})

function generateToken(length = 40) {
	const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
	return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
}

const form = reactive({
	name: props.defaultValues.name || '',
	password: props.defaultValues.password || '',
	description: props.defaultValues.description || '',
	json_token: props.defaultValues.json_token || generateToken(),
	monitoring: props.defaultValues.monitoring || false
})

const validName = computed(() => /^[0-9A-Za-z]*$/.test(form.name))

const loading = ref(false)
const responseMessage = ref('')
const responseOk = ref(false)

async function handleSubmit() {
	if (!validName.value) {
		responseMessage.value = 'Meno obsahuje nepovolené znaky.'
		responseOk.value = false
		return
	}

	loading.value = true
	responseMessage.value = ''
	responseOk.value = false

	try {
		const response = await MainService.postDeviceEdit(form)

		const data = response.data
		responseOk.value = data.status === 200
		responseMessage.value = data.message || 'Neznáma odpoveď od servera.'
	} catch (error) {
		console.error(error)
		responseOk.value = false
		if (error.response?.data?.message) {
			responseMessage.value = error.response.data.message
		} else {
			responseMessage.value = 'Chyba spojenia alebo server neodpovedá.'
		}
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<b-form @submit.prevent="handleSubmit">
		<!-- Meno -->
		<b-form-group label="Meno" label-for="name" description="Len znaky 0-9, A-Z, a-z">
			<b-form-input
				id="name"
				v-model="form.name"
				type="text"
				:state="validName"
				pattern="[0-9A-Za-z]+"
				required
				placeholder="Zadajte meno"
			/>
			<b-form-invalid-feedback>
				Meno môže obsahovať len znaky 0-9, A-Z, a-z.
			</b-form-invalid-feedback>
		</b-form-group>

		<!-- Heslo -->
		<b-form-group label="Heslo" label-for="password">
			<b-form-input
				id="password"
				v-model="form.password"
				type="password"
				required
				placeholder="Zadajte heslo"
			/>
		</b-form-group>

		<!-- Popis -->
		<b-form-group label="Popis" label-for="description">
			<b-form-textarea
				id="description"
				v-model="form.description"
				placeholder="Zadajte popis"
				rows="3"
			/>
		</b-form-group>

		<!-- JSON Token -->
		<b-form-group label="JSON Token" label-for="jsonToken">
			<b-form-input
				id="jsonToken"
				v-model="form.json_token"
				readonly
				plaintext
			/>
		</b-form-group>

		<!-- Monitoring -->
		<b-form-group>
			<b-form-checkbox v-model="form.monitoring" name="monitoring">
				Monitoring
			</b-form-checkbox>
		</b-form-group>

		<!-- Odozva -->
		<div
			v-if="responseMessage"
			class="alert mt-2"
			:class="{ 'alert-success': responseOk, 'alert-danger': !responseOk }"
		>
			{{ responseMessage }}
		</div>

		<!-- Tlačidlo -->
		<b-button type="submit" variant="primary" :disabled="loading">
			{{ loading ? 'Odosielam...' : 'Odoslať' }}
		</b-button>
	</b-form>
</template>
