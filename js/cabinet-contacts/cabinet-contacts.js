import Vue from 'vue';
import vBem from 'v-bem';
import vueResource from 'vue-resource';
import Api from './api';
import ml from './ml';

import cabinetContacts from 'components-vue/cabinet-contacts/contacts-app';

Vue.use(vBem, {blockPrefix: 'b-', modSeparator: '--'});
Vue.use(vueResource);

let app = new Vue({
	ml,
	components: {cabinetContacts},
});
let $api = new Api(app.$http);
Vue.mixin({
	data() {
		return {
			lang: 'ru',
		}
	},
	created: function() {
		this.$api = $api;
		this.lang = document.querySelector('body').getAttribute('data-lang') || 'ru';

		let uri = window.location.search.substring(1);
		let params = new URLSearchParams(uri);
		this.cityIdFromQuery = params.get('city');

		if (!this.$ml.list.includes(this.lang)) {
			this.lang = 'ru'
		}
		this.$ml.change(this.lang);
	},
	methods: {
		isEmptyObject(object) {
			return JSON.stringify(object) === '{}' || JSON.stringify(object) === '[]';
		}
	}
});
document.addEventListener("DOMContentLoaded", function () {
	app.$mount("#cabinet_contacts_app");
});

export {app};
