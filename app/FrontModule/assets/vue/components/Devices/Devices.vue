<script>
import { onMounted, ref } from 'vue'
import MainService from '../../services/MainService'
import dayjs from 'dayjs'; //https://day.js.org/docs/en/display/format

export default {
	setup () {

		const items = ref(null)

		onMounted(()=> {
			getDevices();
		})

		
		const format_date = (value) => {
			const date = dayjs(value);
			// Then specify how you want your dates to be formatted
			return date.format('D.M.YYYY HH:mm:ss');
		}

		const getDevices = () => {
			MainService.getDevices()
				.then(response => {
					//console.log(response.data)
					items.value = response.data
				})
				.catch((error) => {
					console.log(error);
				});
		}
	
		return { items, format_date }
	}
}
</script>

<template>
	<div v-if="items != null" v-for="item in items" :key="item.id" class="device">
		<div class="row px-2 text-secondary device-head" >
			<div class="col-4 col-md-3">Zariadenie <br />[{{ item.id }}]</div>
			<div class="col-12 col-md-4">Popis</div>
			<div class="col-6 col-md-2">Prvé prihlásenie</div>
			<div class="col-6 col-md-2">Posledné prihlásenie</div>
			
		</div>

		<div class="row my-0 px-2 bg-primary text-white">
			<div class="col-4 col-md-3 ">
				<b>
					<RouterLink :to="'device/' + item.id" class="text-white">
						{{ item.name }}
					</RouterLink>
				</b>
				<a 
					v-if="item.problem_mark"
					href="#"
					data-toggle="tooltip"
					data-placement="top"
					:title="'Zariadenie má problém s prihlásením. Posledné neúspešné prihlásenie: ' + item.last_bad_login + '.'"
				>
					<i class="text-warning fas fa-exclamation-triangle"></i>
				</a>
				<a 
					v-if="item.config_data != null"
					href="#" 
					data-toggle="tooltip" 
					data-placement="top" 
					title="Pre zariadenie čaká zmena konfigurácie" 
				>
					<i class="text-warning fas fa-share-square"></i>
				</a>
			</div>
			<div class="col-12 col-md-4"><i>{{ item.desc }}</i></div>
			<div class="col-6 col-md-2">{{ format_date(item.first_login) }}</div>
			<div class="col-6 col-md-2">{{ format_date(item.last_login) }}</div>
			<div class="col-12 col-md-1 text-white d-flex justify-content-end">
				<a :href="'device/edit/' + item.id" class="text-warning" title="Edit">
					<i class="fa-solid fa-pencil"></i>
				</a>
			</div>
		</div>
		<!-- End device / Start sensors-->
		
		<div v-if="Object.keys(item.sensors).length" class="row pl-4 pr-1 py-2">
			<div class="col-12">
				<div class="row text-secondary sensor-head">
					<div class="col-6 col-md-2"><small>(id)</small>Senzor</div>
					<div class="col-5 col-md-2">Stav</div>
					<div class="col-1">Typ</div>
					<div class="col-6 col-md-1">Faktor</div>
					<div class="col-6 col-md-2">Interval</div>
					<div class="col-12 col-md-2">Popis</div>
				</div>
				<div 
					v-for="(sensor, k, index) in item.sensors" :key="sensor.id" 
					class="row"
					:class="index % 2 ? 'bg-light' : 'sensor-odd'"
				>
					<div class="col-6 col-md-2"><b>
						<a :href="'sensor/show/' + sensor.id" >
							<small>({{ k }})</small>{{sensor.name}}</a>
						<a 
							v-if="sensor.warningIcon > 0"
							href="#" data-toggle="tooltip" data-placement="top"
							:title="'Senzor nedodáva data. Posledné data: ' + format_date(sensor.last_data_time) + '.'"
							>
							<i 
								class="fas fa-exclamation-triangle"
								:class="sensor.warningIcon == 1 ? 'text-danger' : 'text-warning'"
							></i>
						</a>
					</b></div>
					<div class="col-5 col-md-2" v-if="sensor.last_out_value !== null">
						{{ sensor.last_out_value }} {{ sensor.unit }}
						<a 
							v-if="sensor.warn_max_fired"
							href="#" data-toggle="tooltip" data-placement="top" 
							:title="'Od ' + sensor.warn_max_fired +' je hodnota nad limitom.'"
						>
							<i class="text-danger fas fa-arrow-circle-up"></i>
						</a>
						<a 
							v-if="sensor.warn_min_fired"
							href="#" data-toggle="tooltip" data-placement="top" 
							:title="'Od ' + sensor.warn_min_fired + ' je hodnota pod limitem.'"
						>
							<i class="text-danger fas fa-arrow-circle-down"></i>
						</a>
					</div>
					<div class="col-5 col-md-2" v-else>
							- [{{ sensor.unit }}]
					</div>
					<div class="col-1">
						<a href="#" data-toggle="tooltip" data-placement="top" 
							:title="sensor.dc_desc">#{{ sensor.device_class }}</a>
					</div>
					<div class="col-6 col-md-1">
						<span v-if="sensor.preprocess_data == 1">
							x {{ sensor.preprocess_factor }}
						</span>
					</div>
					<div class="col-6 col-md-2">{{ sensor.msg_rate }}, {{ sensor.display_nodata_interval }}</div>
					<div class="col-12 col-md-2">
						<a 
							v-if="sensor.warn_max"
							href="#" data-toggle="tooltip" data-placement="top" 
							title="Senzor má nastavené posielanie varovaní pri prekročení horného limitu."
						>
							<i class="fas fa-sort-amount-up"></i>
						</a>
						<a
							v-if="sensor.warn_min"
							href="#" data-toggle="tooltip" data-placement="top"
							title="Senzor má nastavené posielanie varovaní pri prekročení spodného limitu."
						>
							<i class="fas fa-sort-amount-down"></i>
						</a>
						<i>{{ sensor.desc }}</i>
					</div>
					<div class="col-6 col-md-2">
						<a href="../chart/sensorstat/show/{$sensor['id']}/?current=1"
							class="text-warning pe-2"
							title="Štatistika"
						>
							<i class="fa-solid fa-chart-simple"></i>
						</a>
						<a href="../chart/sensor/show/{$sensor['id']}/?current=1"
							class="text-warning pe-2"
							title="Graf"
						>
							<i class="fa-solid fa-chart-line"></i>
						</a> 
						<a :href="'sensor/edit/' + sensor.id" class="text-warning" title="Edit">
							<i class="fa-solid fa-pencil"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>


<style lang="scss" scoped>
.device {
	border-bottom: 1px solid  #aaa;
	padding-bottom: 1rem;
}
.device:last-child {
	border-bottom: 0;
	padding-bottom: 0;
}
.device-head {
	background-color: #eee;
}
.sensor-head {
	background-color: #eee;
}
.sensor-odd {
	background-color: #ddd;
}
</style>