<script setup>
import { onMounted, ref } from 'vue'
import MainService from '../../services/MainService'
import dayjs from 'dayjs'; //https://day.js.org/docs/en/display/format
import Device_popover from './Device_popover.vue';

const items = ref(null)

onMounted(()=> {
	getDevices();
})

		
const format_date = (value) => {
	if (value == null) return "---"
	const date = dayjs(value);
	// Then specify how you want your dates to be formatted
	return date.format('D.M.YYYY HH:mm:ss')
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
</script>

<template v-if="items != null">
	<div v-for="item in items" :key="item.id" class="device">
		<div class="row px-2 text-secondary device-head" >
			<div class="col-4 col-md-3">Zariadenie</div>
			<div class="col-12 col-md-4">Popis</div>
			<div class="col-6 col-md-2">Prvé prihlásenie</div>
			<div class="col-6 col-md-2">Posledné prihlásenie</div>
			
		</div>

		<div class="row my-0 px-2 bg-primary text-white">
			<div class="col-4 col-md-3 ">
				<RouterLink :to="'device/' + item.id" class="text-white me-2"><b>{{ item.name }}</b></RouterLink>
				<Device_popover v-if="item.problem_mark"
					fa_icon="exclamation-triangle text-warning"
					:text="'Zariadenie má problém s prihlásením. Posledné neúspešné prihlásenie: ' + format_date(item.last_bad_login) + '.'"
				/>
				<Device_popover v-if="item.config_data != null"
					fa_icon="share-square text-warning"
					text="Pre zariadenie čaká zmena konfigurácie."
				/>
			</div>
			<div class="col-12 col-md-4"><i>{{ item.desc }}</i></div>
			<div class="col-6 col-md-2">{{ format_date(item.first_login) }}</div>
			<div class="col-6 col-md-2">{{ format_date(item.last_login) }}</div>
			<div class="col-12 col-md-1 text-white d-flex justify-content-end">
				<RouterLink :to="'edit/'+item.id" class="text-warning" role="button" title="Editácia zariadenia">
					<i class="fa-solid fa-pencil"></i>
				</RouterLink>
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
					<div class="col-6 col-md-2">
						<a :href="'sensor/show/' + sensor.id" ><!-- TODO link -->
							<small>({{ k }})</small><b>{{sensor.name}}</b>
						</a>
						<Device_popover v-if="sensor.warningIcon > 0"
							:fa_icon="'exclamation-triangle ' + sensor.warningIcon == 1 ? 'text-danger' : 'text-warning'"
							:text="'Senzor nedodáva data. Posledné data: ' + format_date(sensor.last_data_time) + '.'"
						/>
					</div>
					<div class="col-5 col-md-2" v-if="sensor.last_out_value !== null">
						{{ sensor.last_out_value }} {{ sensor.value_unit }}
						<Device_popover v-if="sensor.warn_max_fired"
							fa_icon="arrow-circle-up text-danger"
							:text="'Od ' + sensor.warn_max_fired +' je hodnota nad limitom.'"
						/>
						<Device_popover v-if="sensor.warn_min_fired"
							fa_icon="arrow-circle-down text-danger"
							:text="'Od ' + sensor.warn_min_fired +' je hodnota pod limitom.'"
						/>
					</div>
					<div class="col-5 col-md-2" v-else>
							--- [{{ sensor.value_unit }}]
					</div>
					<div class="col-1">
						<Device_popover
							:click_me="true"
							:text_to_target="'#' + sensor.id_device_classes"
							:text="sensor.dc_desc"
						/>
					</div>
					<div class="col-6 col-md-1">
						<span v-if="sensor.preprocess_data == 1">
							x {{ sensor.preprocess_factor }}
						</span>
					</div>
					<div class="col-6 col-md-2">{{ sensor.msg_rate }}, {{ sensor.display_nodata_interval }}</div>
					<div class="col-12 col-md-2">
						<Device_popover v-if="sensor.warn_max"
							fa_icon="sort-amount-up"
							text="Senzor má nastavené posielanie varovaní pri prekročení horného limitu."
						/>
						<Device_popover v-if="sensor.warn_min"
							fa_icon="sort-amount-down"
							text="Senzor má nastavené posielanie varovaní pri prekročení spodného limitu."
						/>
						<i>{{ sensor.desc }}</i>
					</div>
					<div class="col-6 col-md-2"><!-- TODO links -->
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
						<a href="'sensor/edit/' + sensor.id" 
							class="text-warning" title="Edit">
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