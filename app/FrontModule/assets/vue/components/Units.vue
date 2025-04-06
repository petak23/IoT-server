<script setup>
import { onMounted, ref } from 'vue'
import MainService from '../services/MainService'

const items = ref(null)

const getUnits = () => {
	MainService.getUnits()
		.then(response => {
			items.value = response.data
		})
		.catch((error) => {
			console.error(error)
		})
}

onMounted(()=> {
	getUnits();
})
</script>

<template>
	<div v-if="items != null" class="col-12 table table-responsive">
		<table class="table">
			<tbody>
				<tr>
					<th>Id</th>
					<td v-for="(id) in items" :key="id">{{ id }}</td>
				</tr>
				<tr>
					<th>Meno</th>
					<td v-for="(id, unit) in items" :key="id">{{ unit }}</td>
				</tr>
			</tbody>	
		</table>
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