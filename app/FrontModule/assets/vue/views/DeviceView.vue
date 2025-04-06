<script setup>
import { RouterLink } from 'vue-router';

import Device from '../components/Devices/Device.vue'

const props = defineProps(["id"]);
</script>

<template>
	<device 
		:id_device="props.id"
	/>

	<div class="row px-2 py-3">
		<div class="col-12">
			<RouterLink to="/devices" class="btn btn-primary">
				Späť na zoznam zariadení <i class="fa-solid fa-rotate-left"></i>
			</RouterLink>
		</div>
	</div>
</template>