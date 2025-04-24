<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import MainService from '../../services/MainService'
import dayjs from 'dayjs'; //https://day.js.org/docs/en/display/format

const props = defineProps({
	id_device: { type: Number, default: 0 }
})

const item = ref({name: "..."})

onMounted(()=> {
	getDevice();
})

watch(() => props.id_device, () => {
	getDevice();
});

const rssiComputed = computed(() => {
	let out = ""
	if (item.value.rssi > -50) out = "- skvělá kvalita signálu."
	else if (item.value.rssi > -60 ) out = "- dobrá kvalita signálu, mělo by fungovat i posílání souborů a logů."
	else if (item.value.rssi > -70 ) out = '<i class="text-warning fas fa-exclamation-triangle"></i> Omezená kvalita signálu - data projdou, ale u souborů a logů očekávejte problémy.'
	else out = '<i class="text-danger fas fa-exclamation-triangle"></i> Špatná kvalita signálu - očekávejte problémy.'
	return out
})

const format_date = (value) => {
	const date = dayjs(value);
	// Then specify how you want your dates to be formatted
	return date.format('D.M.YYYY HH:mm:ss');
}

const getDevice = () => {
	MainService.getDevice(props.id_device)
		.then(response => {
			//console.log(response.data)
			item.value = response.data
		})
		.catch((error) => {
			console.error(error);
		});
}
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
	<div class="row px-2  pt-3">
		<div class="col-12">
			<a n:href="Device:config $device['id']" class="btn btn-outline-primary btn-sm" role="button">
				Zobraziť konfiguráciu
			</a>
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
			<div class="col-12 col-md-10"><b>{{ item.uptime }}</b> (při poslední komunikaci)</div>
	</div>

	<div class="row px-2  bg-light" v-if="item.rssi">
			<div class="col-12 col-md-2">Síla WiFi signálu:</div>
			<div class="col-12 col-md-10">
				<b>{{ item.rssi }} dBm</b> (při posledním přihlášení)
				<span v-html="rssiComputed"></span>
			</div>
	</div>

	<div class="row px-2  ">
			<div class="col-12 col-md-2">Bezp. token pro JSON data:</div>
			<div class="col-12 col-md-10">{{ item.json_token }}</div>
	</div>

	<div class="row px-2 bg-light">
			<div class="col-12 col-md-2">Bezp. token pro galerii:</div>
			<div class="col-12 col-md-10">{{ item.blob_token }}</div>
	</div>

	<div class="row px-2">
			<div class="col-12 col-md-2">Kontrolovat v monitoringu:</div>
			<div class="col-12 col-md-10">{{ item.monitoring ==1 ? 'ano' : 'ne' }}</div>
	</div>

	<div class="row px-2 pt-3">
			<div class="col-12">
					<a n:href="Device:edit $device['id']" class="btn btn-outline-primary btn-sm" role="button">Upravit zařízení</a>
					<a n:href="Device:sendconfig $device['id']" class="btn btn-outline-primary btn-sm" role="button">Poslat změnu konfigurace</a>
					<a n:href="Device:update $device['id']" class="btn btn-outline-primary btn-sm" role="button">Poslat OTA aktualizaci aplikace</a>
			</div>
	</div>

	<div class="px-2 pb-0 pt-4">
			<h3>OTA aktualizace aplikace</h3>
	</div>
	<div class="row pl-4 pr-1 py-0" v-if="item.updates">
		<div class="col-12">
			<div class="row text-secondary">
					<div class="col   col-md-1">ID</div>
					<div class="col-6 col-md-3">Z verze</div>
					<div class="col-6 col-md-3">Nahráno</div>
					<div class="col-6 col-md-3">Staženo</div>
					<div class="col   col-md-1">&nbsp;</div>
			</div>
			<div v-for="(upd, index) in item.updates" :key="upd.id" class="row" :class="index % 2 ? 'bg-light': ''">
					<div class="col   col-md-1">{$update['id']}</div>
					<div class="col-6 col-md-3">{$update['fromVersion']}</div>
					<div class="col-6 col-md-3">{$update['inserted']}</div>
					<div class="col-6 col-md-3">{$update['downloaded']}</div>
					<div class="col   col-md-1"><a n:href="Device:deleteupdate $device['id'], $update['id']">Smazat</a></div>
			</div>
			{last}
					</div></div>
			{/last}
	{/foreach}

	{if $device['config_data']}
			<div class="px-2 pb-0 pt-4">
					<h3>Změna konfigurace</h3>
					<i class="far fa-share-square text-danger"></i>&nbsp;Pro zařízení čeká změna konfigurace:
					<div class="px-4 py-0">
							<i><pre>{$device['config_data']}</pre></i>
					</div>
			</div>
	{/if}

	{foreach $sensors as $sensor}
			{first}
					<div class="px-2 pb-0 pt-4">
							<h3>Senzory</h3>
					</div>
					<div class="row pl-4 pr-1 py-0"><div class="col-12">
							<div class="row text-secondary">
									<div class="col-6 col-md-2">Senzor</div>
									<div class="col-6 col-md-2">
											Stav
											<a href="#" data-toggle="tooltip" data-placement="top" title="Pro impulzní senzory denní suma (může mít cca minutu zpoždění). Pro ostatní poslední zaslaná hodnota (ihned)."
											><i class="fas fa-question-circle"></i></a>
									</div>
									<div class="col-12 col-md-4">Popis</div>
							</div>
			{/first}

			<div class="row {if $iterator->odd}bg-light{/if}">
					<div class="col-6 col-md-2"><b><a n:href="Sensor:show $sensor['id']" >{$sensor['name']}</a>
							{if ($sensor['warningIcon']>0)} 
									<a href="#" data-toggle="tooltip" data-placement="top" title="Senzor nedodává data. Poslední data: {$sensor['last_data_time']}."
									><i class="{if ($sensor['warningIcon']==1)}text-danger{else}text-warning{/if} fas fa-exclamation-triangle"></i></a>
							{/if}
					</b></div>
					<div class="col-6 col-md-2">
							{if ($sensor['last_out_value']!==NULL)}
									{$sensor['last_out_value']} {$sensor['unit']}

									{if ($sensor['warn_max_fired'])} 
											<a href="#" data-toggle="tooltip" data-placement="top" title="Od {$sensor['warn_max_fired']} je hodnota nad limitem."
											><i class="text-danger fas fa-arrow-circle-up"></i></a>
									{/if}
									{if ($sensor['warn_min_fired'])} 
											<a href="#" data-toggle="tooltip" data-placement="top" title="Od {$sensor['warn_min_fired']} je hodnota pod limitem."
											><i class="text-danger fas fa-arrow-circle-down"></i></a>
									{/if}
							{else}  
									- [{$sensor['unit']}]
							{/if}
						
					</div>
					<div class="col-12 col-md-4">
							{if $sensor['warn_max']} 
									<a href="#" data-toggle="tooltip" data-placement="top" title="Senzor má nastaveno posílání varování při překročení horního limitu."
											><i class="fas fa-sort-amount-up"></i></a>
							{/if}
							{if $sensor['warn_min']} 
									<a href="#" data-toggle="tooltip" data-placement="top" title="Senzor má nastaveno posílání varování při překročení spodního limitu."
											><i class="fas fa-sort-amount-down"></i></a>
							{/if}
							<i>{$sensor['desc']}</i>
					</div>
					<div class="col-12 col-md-4">
							<a href="../../chart/sensorstat/show/{$sensor['id']}/?current=1">Statistika</a>
									· 
							<a href="../../chart/sensor/show/{$sensor['id']}/?current=1">Graf</a>
							· 
							<a n:href="Sensor:show $sensor['id']" >Info</a>
							· 
							<a n:href="Sensor:edit $sensor['id']" >Edit</a>
					</div>
			</div>

			{last}
					</div></div>
			{/last}
	{/foreach}

	{if ($device['json_token'])}
	<div class="px-2 pb-2 pt-4">
			<h3>Data zařízení</h3>
			Data ze senzorů zařízení ve formě JSON jsou dostupná zde: 
			<br><small><a href="{$jsonUrl}">{$jsonUrl}</a></small>
			<br>Pokud je zařízení meteostanice se senzory teploty a srážek, lze získat data pro display meteostanice zde:
			<br><small><a href="{$jsonUrl2}">{$jsonUrl2}</a></small>
			<br><small>Každý, kdo zná tato URL, si může data zobrazit. Přístup k JSON souboru již dále <b>není chráněn heslen</b>.</small>
	</div>
	{/if}

	{if ($device['blob_token'])}
	<div class="px-2 pb-2 pt-4">
			<h3>Galerie obrázků</h3>
			Galerie obrázků (pokud je zařízení generuje) je dostupná zde: 
			<br><small><a href="{$blobUrl}">{$blobUrl}</a></small>
			<br><small>Každý, kdo zná toto URL, si může data zobrazit. Přístup k obrázkům již dále <b>není chráněn heslen</b>.</small>
	</div>
	{/if}

	{if ($soubory>0) }
	<div class="row px-2 pt-3">
			<div class="col-12">
					<h3>Soubory</h3>
					U zařízení je uloženo <a n:href="Device:blobs $device['id']"><b>{$soubory}</b> souborů</a>.
			</div>
	</div>
	{/if}
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