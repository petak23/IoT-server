<script setup>
import { onMounted, ref } from 'vue'
import MainService from '../../services/MainService'
import dayjs from 'dayjs'; //https://day.js.org/docs/en/display/format

const items = ref(null)

const format_date = (value) => {
	const date = dayjs(value);
	// Then specify how you want your dates to be formatted
	return date.format('D.M.YYYY HH:mm:ss');
}

const getDevices = () => {
	MainService.getDevices()
		.then(response => {
			console.log(response.data)
			items.value = response.data
		})
		.catch((error) => {
			console.log(error);
		})
}

onMounted(()=> {
	getDevices();
})
</script>

<template v-if="items != null">
	<div v-for="item in items" :key="item.id" class="col">
		<div class="card h-100 text-bg-dark border-warning">
			<h5 class="card-header">
				{{ item.name }}
			</h5>
			<div class="card-body">
				<p class="card-text"><small>
					{{ item.desc }}<br />
					<span v-if="item.sensors.length">
						Posledné meranie:<br />{{ format_date(Object.values(item.sensors)[0].last_data_time) }}
					</span>
					<span v-else class="text-danger">Nemám hodnoty o meraniach!</span>
				</small></p>
				<h6 class="card-title" v-if="item.sensors.length">Posledné hodnoty zo senzorov:</h6>
				<ul class="list-group list-group-flush" v-if="item.sensors.length">
					<li 
						v-for="sen in item.sensors"
						:key="sen.id"
						class="list-group-item text-bg-dark"
					>
						( {{ format_date(sen.last_data_time) }} ) {{ sen.last_out_value.toFixed(2) }} {{ sen.value_unit }}
					</li>
				</ul>
			</div>
			<div class="card-footer">
				<div class="d-flex justify-content-end pt-2">
					<RouterLink :to="'device/' + item.id" class="btn btn-outline-info">
						Viac info <i class="fa-solid fa-angles-right"></i>
					</RouterLink>
				</div>
			</div>
		</div>
	</div>
</template>