<script setup>
/**
 * Komponenta pre vypísanie flash správ.
 * Posledna zmena 14.01.2025
 *
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.7
 */

import { ref, watch, toRaw, computed } from 'vue'
import { useFlashStore } from './store/flash'
import { storeToRefs } from 'pinia';

const storeF = useFlashStore()

const mess = ref([])	// pole aktuálne zobrezených správ
		
const hide = () => {
	mess.value.shift()
}

const { flashMessages } = storeToRefs(storeF)

const visible = computed(() => {
	return mess.value.length > 0 
})

watch(storeF.flashMessages, (newFlashMessages) => {
	if (flashMessages.value.length > 0) {
		const fm = toRaw(flashMessages.value.shift())
		mess.value.push(fm)
		if (typeof fm.timeout !== 'undefined' && parseInt(fm.timeout) > 0) {
			setTimeout(() => hide(), parseInt(fm.timeout))
		}
	}
})

</script>

<template>
	<ul class="alert-container" v-if="visible">
		<li
			v-for="(m, index) in mess" 
			:key="index"
		>
			<transition name="fade">
				<div 
					class="alert alert-dismissible fade show" 			
					:class="'alert-'+m.type"
				>
					<h4 class="alert-heading" v-if="m.heading">{{ m.heading }}</h4>
					<div v-html="m.message"></div>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</transition>
		</li>
	</ul>
</template>

<style lang="scss" scoped>
.alert-container {
	position: fixed;
	right: 2em;
	bottom: 2em;
  max-width: 50vw;
  z-index: 2000;

	li {
		list-style: none;
	}
}
.alert {
  font-size: 0.9rem;
	border-width: .25rem;
}
.alert-heading {
	font-weight: bold;
	border-bottom: 1px solid #999;
}
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
	opacity: 0;
}
</style>