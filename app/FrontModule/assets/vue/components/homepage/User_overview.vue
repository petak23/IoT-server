<script>
//import { onMounted, ref } from 'vue'
//import MainService from '../../services/MainService'
import { useMainStore } from '../../store/main'
import { RouterLink } from 'vue-router'

export default {
	setup () {
		const store = useMainStore()
	
		return { store }
	}
}
</script>

<template>
	<div class="col" v-if="store.user != null">
		<div class="card h-100 text-bg-dark border-warning">
			<h5 class="card-header">
				{{ store.user.email }}
			</h5>
			<div class="card-body">
				<p v-if="store.user.prev_login_time" class="card-text">
					Predošlé prihlásenie:<br />
					<b>{{ store.user.prev_login_time }}</b><br />
					<small>z IP adresy <b>{{ store.user.prev_login_ip }}</b></small>
				</p>
				<hr />	
				<p v-if="store.user.last_error_time" class="card-text text-danger">
					Posledné neúspešné prihlásenie:<br />
					<b>{{ store.user.last_error_time }}</b><br />
					<small>z IP adresy <b>{{ store.user.last_error_ip }}</b></small>
				</p>
	
				<div class="d-flex justify-content-end border-top border-secondary pt-2">
					<RouterLink to="/user" class="btn btn-outline-info">
						Viac info <i class="fa-solid fa-angles-right"></i>
					</RouterLink>
				</div>
			</div>
		</div>
	</div>
</template>


<style lang="scss" scoped>
	.table{
		margin-top: 1rem;

		tr {
			border: 1px solid #999;
		}

		th {
			border-right: 2px solid #999;
		}

		td {
			border-right: 1px solid #aaa;
		}
	}
</style>