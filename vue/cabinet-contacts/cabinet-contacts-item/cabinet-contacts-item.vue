<template>
	<article v-bem>
		<header v-bem.header>
			<img v-bem.image :src="item.img" :alt="fullname">
			<h3 v-bem.title>{{ item.surname }} {{ item.name }} {{ item.patronymic }}</h3>
			<div v-bem.favourite>
				<div class="add-to-favourites"
					:data-element-id="item.id"
					data-remove-text="Удалить из избранного"
					data-add-text="Добавить в избранное">
					<i class="fa fa-star-o" aria-hidden="true"></i>
					<span>В избранное</span>
				</div>
			</div>
		</header>
		<h4 v-bem.subtitle>
			{{ item.position }} {{ item.department }}
		</h4>
		<p v-if="item.location" v-bem.location>г. {{ item.location }}</p>
		<div v-bem.contacts>
			<a v-bem.contacts-item="'phone'" :href="item.phoneHref">{{ item.phone }}</a>
			<a v-bem.contacts-item="'email'" :href="'mailto:' + item.email">{{ item.email }}</a>
		</div>
		<div v-if="item.mentor" v-bem.mentor>
			<h5 v-bem.mentor-title>Руководитель</h5>
			<div v-bem.mentor-popup-wrap>
				<div v-bem.mentor-name>
					{{ item.mentor.surname }} {{ item.mentor.name }} {{ item.mentor.patronymic }}
				</div>
				<div v-bem.mentor-popup>
					<p v-bem.mentor-popup-title>
						{{ item.mentor.surname }} {{ item.mentor.name }} {{ item.mentor.patronymic }}
					</p>
					<div v-bem.mentor-popup-contacts>
						<div v-bem.mentor-popup-close @click="showMentorPopup = !showMentorPopup">&times;</div>
						<a v-bem.mentor-contacts-item="'phone'" :href="item.mentor.phone">{{ item.mentor.phone }}</a>
						<a v-bem.mentor-contacts-item="'email'" :href="'mailto:' + item.mentor.email">{{ item.mentor.email }}</a>
					</div>
				</div>
			</div>
		</div>
	</article>
</template>

<script>
	module.exports = {
		data() {
			return {
				isShowContacts: false,
				showMentorPopup:  false
			};
		},
		props: {
			item: Object
		},
		computed: {
			fullname() {
				return this.item.surname + this.item.name + this.item.patronymic;
			}
		},
		methods: {
			showContacts() {
				this.isShowContacts = true;
			}
		},
		watch: {},
		components: {},
		mixins: [],
		created() {
		},
	}
</script>