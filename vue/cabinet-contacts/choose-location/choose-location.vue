<template>
	<div v-bem>
		<div v-bem.close @click="close">&times;</div>
		<div v-bem.countries>
			<div v-bem.section-title>{{ $ml.get('selectCountry') }}</div>
			<div v-bem.country v-for="item in primaryGeo">
				<input v-bem.country-input :name="'country'" :value="item" v-model="currentCountry" :id="'country-' + item.id"  type="radio">
				<label v-bem.country-label :for="'country-' + item.id" @click="chooseCountry(item)">
					{{ item.name }}
				</label>
			</div>
		</div>

		<div v-bem.cities v-if="!isEmptyObject(currentCountry)">
			<div v-bem.section-title>{{ $ml.get('selectCity') }}</div>
			<div v-bem.city v-for="item in currentCountry.cities" @click="chooseCity(item)">
				<input v-bem.city-input :value="item" v-model="currentCity" :id="'city-' + item.id"  type="radio">
				<label v-bem.city-label :for="'city-' + item.id">
					{{ item.name }}
				</label>
			</div>
		</div>
		<div v-bem.city-search v-if="!isEmptyObject(currentCountry)">
			<div v-bem.section-title>{{ $ml.get('orEnterInTheField') }}</div>
			<input v-bem.search-str v-model="searchStr" type="text" @focus="showSuggestions = true">
			<div v-bem.ok @click="close">{{ $ml.get('ok') }}</div>
			<div v-bem.city-suggestions v-if="showSuggestions && searchStr.length > 1 && filteredCitySuggestions.length > 0">
				<spinner v-if="!(citySuggestions.length > 0)" :size="30" :line-size="8" line-fg-color="#e41f25" v-bem.spinner></spinner>
				<div v-bem.city-suggestion v-for="city in filteredCitySuggestions" @click="chooseCity(city)">{{ city.name }}</div>
			</div>
		</div>
	</div>

</template>

<script>
	import spinner from 'vue-simple-spinner';
	module.exports = {
		data() {
			return {
				searchStr: '',
				citySuggestions: [],
				showSuggestions: false,
				currentCountry: {},
				currentCity: {},
			};
		},
		props: {
			primaryGeo: Array,
			curCountry: String,
		},
		computed: {
			filteredCitySuggestions() {
				let str = this.searchStr;
				if (this.searchStr === '' || this.citySuggestions.length == 0) {
					return [];
				} else {
					return this.citySuggestions.filter(function(item) {
						return item.name.toLowerCase().indexOf(str.toLowerCase()) > -1;
					});
				}
			},
		},
		methods: {
			acceptChoice(close = true) {
				let geo = {};
				if (!this.isEmptyObject(this.currentCountry)) {
					geo['countryName'] = this.currentCountry.name;
					geo['countryId'] = this.currentCountry.id;
				}
				if (!this.isEmptyObject(this.currentCity)) {
					geo['regionName'] = this.currentCity.regionName;
					geo['regionId'] = this.currentCity.regionId;
					geo['cityName'] = this.currentCity.name;
					geo['cityId'] = this.currentCity.id;
				}
				this.$emit('accept-choice', geo);
				if (close) {
					this.close();
				}
			},
			chooseCountry(country) {
				this.currentCountry = country;
				this.currentCity = {};
				this.citySuggestions = [];
				this.searchStr = '';
				this.getCitySuggestions();
			},
			chooseCity(city) {
				this.currentCity = city;
				this.searchStr = city.name;
				this.showSuggestions = false;
				this.acceptChoice();
			},
			close() {
				this.$emit('close');
			},
			getCitySuggestions() {
				this.$api.loadCitiesByParams(
					{
						'countryId': this.currentCountry['id'],
					}).then(data => {
						this.citySuggestions = data;
					}
				);
			},
		},
		watch: {},
		components: {spinner},
		mixins:[],
		created() {
			let that = this;
			this.primaryGeo.forEach(function (item) {
				if (item.name === that.curCountry) {
					that.currentCountry = item;
				}
			});
			this.getCitySuggestions();
		},
	}
</script>