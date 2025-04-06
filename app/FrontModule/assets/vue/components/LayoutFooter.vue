<script setup>
import { useMainStore } from '../store/main'
const store = useMainStore()
</script>

<template>
	<footer class="py-0 text-bg-dark mt-auto">
		<div class="container-fluid">
			<div class="d-flex align-items-center justify-content-between small">
				<small class="text-secondary">{{ store.appName }}</small>
				<ul class="nav" v-if="store.links.length > 0">
					<li class="nav-item" v-for="(link, index) in store.links" :key="index">
						<a
							class="nav-link link-success" 
							:href="link.link"
							target="_blank">
							<small>{{ link.name }}</small>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</footer>
</template>



<style lang="scss" scoped>

</style>