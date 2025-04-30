<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { BAccordion, BAccordionItem } from 'bootstrap-vue-next'

const props = defineProps({
	device: { type: Object, default: null }
})

const item = ref(null)

onMounted(()=> {
	if (props.device != null) item.value = props.device
})

watch(() => props.device, () => {
	item.value = props.device
});

const rssiComputed = computed(() => {
	let out = ""
	if (item.value.rssi > -50) out = "- výborná kvalita signálu."
	else if (item.value.rssi > -60 ) out = "- dobrá kvalita signálu, malo by fungovať aj posielanie súborov a logov."
	else if (item.value.rssi > -70 ) out = '<i class="text-warning fas fa-exclamation-triangle"></i> Omedzená kvalita signálu - data prejdú, ale pri súboroch a logoch očakávajte problémy.'
	else out = '<i class="text-danger fas fa-exclamation-triangle"></i> Zlá kvalita signálu - očakávajte problémy.'
	return out
})
</script>

<template>
	<div class="col-12 h1">
		<h1>Zariadenie ({{ item.id }}) - {{ item.name }}</h1>
	</div>

	<div class="row px-2">
		<div class="col-12">
			<h3>Konfigurácia zariadenia</h3>
			<i>Po stisku tlačítka sa zobrazí konfigurácia, ktorú je potrebné nastaviť do zariadenia v konfiguračnom portály zariadenia.</i>
		</div>
	</div>
	<div class="row px-2 pt-3">
		<div class="col-12"><!-- TODO over -->
			<BAccordion lazy>
				<BAccordionItem title="Zobraziť konfiguráciu">
					<div class="row px-2">
						<div class="col-12">
							<h3>Konfigurácia zariadenia</h3>
							<i>Túto konfiguráciu nastavte v zariadení.</i>
						</div>
					</div>
					<div class="row px-2 bg-light">
						<div class="col-12 col-md-2">RA URL:</div>
						<div class="col-12 col-md-10"><b>{{ item.url }}</b></div>
					</div>
					<div class="row px-2">
						<div class="col-12 col-md-2">RA device name:</div>
						<div class="col-12 col-md-10"><b>{{ item.name }}</b></div>
					</div>
					<div class="row px-2 bg-light">
						<div class="col-12 col-md-2">RA passphrase:</div>
						<div class="col-12 col-md-10"><b>{{ item.passphrase }}</b></div>
					</div>
				</BAccordionItem>
			</BAccordion>
		</div>
	</div>

	<div class="row px-2 pt-3">
		<div class="col-12">
			<h3>Informácie</h3>
		</div>
	</div>
	<div class="row px-2 bg-light">
		<div class="col-12 col-md-2">Popis:</div>
		<div class="col-12 col-md-10">{{ item.desc }}</div>
	</div>
	<div class="row px-2">
		<div class="col-12 col-md-2">Prevádzkovaná aplikácia:</div>
		<div class="col-12 col-md-10"><b>{{ item.app_name }}</b></div>
	</div>
	<div class="row px-2 bg-light">
		<div class="col-12 col-md-2">Prvné prihlásenie:</div>
		<div class="col-12 col-md-10">
			<i v-if="item.first_login == null" class="text-danger">
				Zariadenie sa ešte neprihlásilo cez rozhranie RatatoskrIoT, preto nebude vypísané v monitoringu.
			</i>
			<span v-else>{{ item.first_login }}</span>
		</div>
	</div>
	<div class="row px-2">
		<div class="col-12 col-md-2">Posledné prihlásenie:</div>
		<div class="col-12 col-md-10 text-danger" v-if="item.problem_mark">
			<b>
				<i class="text-danger fas fa-exclamation-triangle"></i>
				Zariadenie má problém s prihlásením. Posledné neúspešné prihlásenie: 
				{{ item.last_bad_login }}.
			</b>
		</div>
		<div class="col-12 col-md-10" v-else><b>{{ item.last_login }}</b></div>
	</div>

	<div class="row px-2  bg-light">
		<div class="col-12 col-md-2">Posledná komunikácia:</div>
		<div class="col-12 col-md-10"><b>{{ item.lastComm }}</b></div>
	</div>

	<div class="row px-2" v-if="item.uptime">
			<div class="col-12 col-md-2">Uptime:</div>
			<div class="col-12 col-md-10"><b>{{ item.uptime }}</b> (pri poslednej komunikácii)</div>
	</div>

	<div class="row px-2  bg-light" v-if="item.rssi">
			<div class="col-12 col-md-2">Sila WiFi signálu:</div>
			<div class="col-12 col-md-10">
				<b>{{ item.rssi }} dBm</b> (pri poslednom prihlásení)
				<span v-html="rssiComputed"></span>
			</div>
	</div>

	<div class="row px-2  ">
			<div class="col-12 col-md-2">Bezp. token pre JSON data:</div>
			<div class="col-12 col-md-10">{{ item.json_token }}</div>
	</div>

	<div class="row px-2 bg-light">
			<div class="col-12 col-md-2">Bezp. token pre galériu:</div>
			<div class="col-12 col-md-10">{{ item.blob_token }}</div>
	</div>

	<div class="row px-2">
			<div class="col-12 col-md-2">Kontrolovať v monitoringu:</div>
			<div class="col-12 col-md-10">{{ item.monitoring ==1 ? 'áno' : 'ne' }}</div>
	</div>

	<div class="row px-2 pt-3">
			<div class="col-12"><!-- TODO -->
					<a href="Device:edit $device['id']" class="btn btn-outline-primary btn-sm" role="button">Upraviť zariadenie</a>
					<a href="Device:sendconfig $device['id']" class="btn btn-outline-primary btn-sm" role="button">Poslať zmenu konfigurácie</a>
					<a href="Device:update $device['id']" class="btn btn-outline-primary btn-sm" role="button">Poslať OTA aktualizáciu aplikácie</a>
			</div>
	</div>

	<div class="px-2 pb-0 pt-4">
			<h3>OTA aktualizácia aplikácie</h3>
	</div>
	<div class="row pl-4 pr-1 py-0" v-if="item.updates">
		<div class="col-12">
			<div class="row text-secondary">
					<div class="col   col-md-1">ID</div>
					<div class="col-6 col-md-3">Z verzie</div>
					<div class="col-6 col-md-3">Nahrané</div>
					<div class="col-6 col-md-3">Stiahnuté</div>
					<div class="col   col-md-1">&nbsp;</div>
			</div>
			<div v-for="(upd, index) in item.updates" :key="upd.id" class="row" :class="index % 2 ? 'bg-light': ''">
					<div class="col   col-md-1">{$update['id']}</div>
					<div class="col-6 col-md-3">{$update['fromVersion']}</div>
					<div class="col-6 col-md-3">{$update['inserted']}</div>
					<div class="col-6 col-md-3">{$update['downloaded']}</div><!-- TODO -->
					<div class="col   col-md-1"><a href="Device:deleteupdate $device['id'], $update['id']">Zmazať</a></div>
			</div>
		</div>
	</div>
	<div class="row pl-4 pr-1 py-0" v-else>
		<div class="col-12">Aktualizácie ešte neexistujú.</div>
	</div>

	<div class="px-2 pb-0 pt-4" v-if="item.config_data">
		<h3>Zmena konfigurácie</h3>
		<i class="far fa-share-square text-danger"></i>&nbsp;Pre zariadenie čaká zmena konfigurácie:
		<div class="px-4 py-0">
			<i><pre>{{ item.config_data }}</pre></i>
		</div>
	</div>

	<div class="px-2 pb-0 pt-4">
		<h3>Senzory</h3>
	</div>
	<div class="row pl-4 pr-1 py-0" v-if="item.sensors">
		<div class="col-12">
			<div class="row text-secondary">
				<div class="col-6 col-md-2">Senzor</div>
				<div class="col-6 col-md-2">
					Stav
					<a 
						href="#" data-toggle="tooltip" data-placement="top" 
						title="Pre impulzné senzory denná suma (môže mať cca. minútu meškanie). Pre ostatné posledná zaslaná hodnota (ihneď)."
					>
						<i class="fas fa-question-circle"></i>
					</a>
				</div>
				<div class="col-12 col-md-4">Popis</div>
			</div>

			<div v-for="(sensor, index) in item.sensors" :key="sensor.id" class="row" :class="index % 2 ? 'bg-light': ''">
				<div class="col-6 col-md-2">
					<b><!-- TODO -->
						<a href="Sensor:show sensor.id" >{{ sensor.name }}</a> 
						<a v-if="sensor.warningIcon > 0"
							data-toggle="tooltip" data-placement="top" :title="'Senzor nedodává data. Posledné data: ' + sensor.last_data_time + '.'"
						>
							<i :class="sensor.warningIcon ==1 ? 'text-danger' : 'text-warning'" class="fas fa-exclamation-triangle"></i>
						</a>
					</b>
				</div>

				<div class="col-6 col-md-2" v-if="sensor.last_out_value!==NULL">
					{{ sensor.last_out_value }} {{ sensor.unit }}

					<a v-if="sensor.warn_max_fired" data-toggle="tooltip" data-placement="top" :title="'Od ' + sensor.warn_max_fired + 'je hodnota nad limitom.'"
					><i class="text-danger fas fa-arrow-circle-up"></i></a>
					
					<a v-if="sensor.warn_min_fired" data-toggle="tooltip" data-placement="top" :title="'Od ' + sensor.warn_min_fired + 'je hodnota pod limitom.'"
					><i class="text-danger fas fa-arrow-circle-down"></i></a>
				</div>
				<div class="col-6 col-md-2" v-else>- [{{ sensor.unit }}]</div>

				<div class="col-12 col-md-4">
					<a v-if="sensor.warn_max" data-toggle="tooltip" data-placement="top" title="Senzor má nastavené posielanie varovaní pri prekročení horného limitu."
					><i class="fas fa-sort-amount-up"></i></a>
					<a v-if="sensor.warn_min" data-toggle="tooltip" data-placement="top" title="Senzor má nastavené posielanie varovaní pri prekročení dolného limitu."
					><i class="fas fa-sort-amount-down"></i></a>
					<i>{{ sensor.desc }}</i>
				</div>
				<div class="col-12 col-md-4"><!-- TODO -->
					<a href="../../chart/sensorstat/show/{sensor.id}/?current=1">Štatistika</a>· 
					<a href="../../chart/sensor/show/{sensor.id}/?current=1">Graf</a>· 
					<a href="Sensor:show sensor.id" >Info</a>· 
					<a href="Sensor:edit sensor.id" >Edit</a>
				</div>
			</div>
		</div>
	</div>

	<div class="px-2 pb-2 pt-4" v-if="item.json_token">
		<h3>Data zariadenia</h3>
		Data zo senzorov zariadenia vo forme JSON sú dostupné tu: 
		<br><small><a :href="item.jsonUrl">{{ item.jsonUrl }}</a></small>
		<br>Pokiaľ je zariadenie meteostanice so senzormi teploty a zrážok, je možné získať data pre display meteostanice tu:
		<br><small><a :href="item.jsonUrl2">{{ item.jsonUrl2 }}</a></small>
		<br><small>Každý, kdo pozná tieto URL, si môže data zobraziť. Prístup k JSON súboru už <b>nie je chránený heslom</b>.</small>
	</div>

	<div class="px-2 pb-2 pt-4" v-if="item.blob_token">
		<h3>Galéria obrázkov</h3>
		Galéria obrázkov (pokiaľ ju zariadenie generuje) je dostupná tu: 
		<br><small><a :href="item.blobUrl">{{ item.blobUrl }}</a></small>
		<br><small>Každý, kdo pozná túto URL, si môže obrázky zobraziť. Prístup k obrázkom už <b>nie je chránený heslom</b>.</small>
	</div>

	<div class="row px-2 pt-3" v-if="item.subory">
		<div class="col-12">
			<h3>Súbory</h3><!-- TODO -->
			V zariadení je uložené <a href="Device:blobs item.id"><b>{{ item.subory }}</b> súborov</a>.
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