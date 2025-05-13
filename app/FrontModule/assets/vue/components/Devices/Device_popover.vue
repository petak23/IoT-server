<script setup>
import { BPopover } from 'bootstrap-vue-next';

const props = defineProps({
	fa_icon: {
		type: String,
		default: null
	},
	text_to_target: {
		type: String,
		default: null
	},
	text: {
		type: String,
		default: ""
	},
	click_me: {
		type: Boolean,
		default: false
	}
})
</script>

<template>
	<BPopover
		:click="props.click_me"	
	>
		<template #target>
			<i v-if="props.fa_icon != null"	
				class="fas"
				:class="'fa-' + props.fa_icon"
			></i>
			<span v-if="props.text_to_target != null">{{ props.text_to_target }}</span>
		</template>
		{{ props.text }}
	</BPopover>
</template>