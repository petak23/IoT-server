<script setup>
import { useMainStore } from '../store/main'
const store = useMainStore()

//import dayjs from 'dayjs'; //https://day.js.org/docs/en/display/format

// TODO over potrebu...
/*const format_date = (value) => {
	const date = dayjs(value)
	// Then specify how you want your dates to be formatted
	return date.format('D.M.YYYY HH:mm:ss');
}*/
</script>

<template>
<div v-if="store.user != null">
	<div class="row px-2 bg-light" v-if="store.user.prev_login_time" >
		<div class="col-12 col-md-3">Predošlé prihlásenie:</div>
		<div class="col-12 col-md-9">
				<b>{{ store.user.prev_login_time }}</b> z IP adresy 
				<span v-if="store.user.prev_login_name">
					{{ store.user.prev_login_ip }} (<b>{{ store.user.prev_login_name }}</b>)
				</span>
				<b v-else>{{ store.user.prev_login_ip }}</b>
				<br /><small>{{ store.user.prev_login_browser }}</small>
		</div>
	</div>

	<div class="row px-2" v-if="store.user.last_error_time">
		<div class="col-12 col-md-3">Posledné neúspešné prihlásenie:</div>
		<div class="col-12 col-md-9 text-danger">
			<b>{{ store.user.last_error_time }}</b> z IP adresy: 
			<span v-if="store.user.last_error_name">
				{{ store.user.last_error_ip }} (<b>{{ store.user.last_error_name }}</b>)
			</span>
			<b v-else>{{ store.user.last_error_ip }}</b>
			<br /><small>{{ store.user.last_error_browser }}</small>
		</div>
	</div>

	<div class="row px-2 pt-4 pb-2">
		<div class="col-12">
			<h3>Vlastnosti účtu</h3>
		</div>
	</div>

	<div class="row px-2">
		<div class="col-12 col-md-3">E-mail adresa:</div>
		<div class="col-12 col-md-9">
			<b>{{ store.user.email }}</b>
			<br><small>Túto adresu využívate pri prihlásení a na túto adresu budú zasielané e-mail notifikácie.</small>
		</div>
	</div>

	<div class="row px-2 bg-light">
		<div class="col-12 col-md-3">Token pre prístup k monitoringu:</div>
		<div class="col-12 col-md-9"><b>{{ store.user.monitoring_token }}</b></div>
	</div>    

	<div class="row px-2  pt-3">
		<div class="col-12">
			<!-- TODO links-->
			<a :href="'Inventory:edit'" class="btn btn-outline-primary btn-sm" role="button">Zmeniť nastavenie</a>
			<a :href="'Inventory:password'" class="btn btn-outline-primary btn-sm" role="button">Zmeniť heslo</a>
		</div>
	</div>

	<div class="px-2 pb-2 pt-4" v-if="store.user.monitoring_token">
		<h3>Monitoring</h3>
		Dáta monitoringu sú dostupné tu: 
		<!-- TODO link-->
		<b><a :href="store.user.monitoringUrl"> {{ store.user.monitoringUrl }}</a></b>
		<br />
		<small>
			Každý, kdo pozná túto URL, si môže data zobraziť. Prístup už ďalej <b>nieje chránený heslon</b>.
		</small>
	</div>


	<div class="row px-2 pt-4 pb-2">
		<div class="col-12">
			<h3>Retencia dát</h3>
			Uložené dáta staršie než určený čas budú zmazané.
		</div>
	</div>

	<div class="row px-2 bg-light">
		<div class="col-12 col-md-3">Detailné dáta (zdrojové hodnoty):</div>
		<div class="col-12 col-md-9">
			<b v-if="store.user.measures_retention != 0">{{ store.user.measures_retention }} dní</b>
			<span v-else>neobmedzené</span>
		</div>
	</div>

	<div class="row px-2">
		<div class="col-12 col-md-3">Sumárné dáta (hodinové priemery, maximá, minimá):</div>
		<div class="col-12 col-md-9">
			<b v-if="store.user.sumdata_retention != 0">{{ store.user.sumdata_retention }} dní</b>
			<span v-else>neobmedzené</span>
		</div>
	</div>

	<div class="row px-2 bg-light">
		<div class="col-12 col-md-3">Súbory:</div>
		<div class="col-12 col-md-9">
			<b v-if="store.user.blob_retention">{{ store.user.blob_retention }} dní</b>
			<span v-else>neobmedzené</span>
		</div>
	</div>


	<div class="row px-2  pt-3">
		<div class="col-12">
			<RouterLink to="/devices" class="btn btn-outline-primary btn-sm" role="button">Zariadenia</RouterLink>
			<!-- TODO link-->
			<a :href="'View:views'" class="btn btn-outline-primary btn-sm" role="button">Grafy</a>
		</div>
	</div>
</div>
</template>