<template>
	<div v-bem>
		<div v-bem.row>
			<!-- <div v-bem.geo>
				<span v-bem.geo-title>{{ currentLocationTitle }}</span>
				<span v-bem.current-location @click="chooseLocation = true">{{ currentLocation }}</span>
				<choose-location
					v-if="chooseLocation"
					:primaryGeo="primaryGeo"
					v-on:close="chooseLocation = false">
				</choose-location>
			</div> -->
			<div v-bem.search>
				<div v-bem.search-input-wrap>
					<input v-bem.search-input v-model="searchStr" type="text" :placeholder="$ml.get('searchByName')" @change="searchSpecialists">
					<div v-bem.search-clear v-if="searchStr" @click="clearSearch">&times;</div>
				</div>
				<!-- <button v-bem.search-btn @click="searchSpecialists">{{ $ml.get('show') }}</button> -->
			</div>
			<div v-bem.filter v-if="searchStr === ''">
				<div v-bem.support-types v-if="shouldShowSupportTypes">
					<div v-bem.support-type="{current: item.id == currentSupportType}" v-for="item in supportTypes">
						<input v-bem.support-type-input="{current: item.id == currentSupportType}" :value="item.id" v-model="supportType" :id="'support-type-' + item.id"  type="radio">
						<label v-bem.support-type-label="{current: item.id == currentSupportType}" :for="'support-type-' + item.id">
							{{ item.name }}
						</label>
					</div>
				</div>
				<div v-bem.support-questions>
					<div v-bem.support-question="{current: item.id == currentSupportQuestion}" v-for="item in supportQuestions">
						<input v-bem.support-question-input="{current: item.id == currentSupportQuestion}" :value="item.id" v-model="supportQuestion" :id="'support-question-' + item.id"  type="radio">
						<label
							v-bem.support-question-label="{current: item.id == currentSupportQuestion}"
							:for="'support-question-' + item.id"
							@click="setSpecialists(item.id)"
							>
							<img v-if="item.cabinetIcon" :src="item.cabinetIcon" :alt="item.name"> {{ item.name }}
						</label>
					</div>
				</div>
			</div>
			<div v-bem.items>
				<cabinet-contacts-item v-for="item, id in specialists" :item="item" :key="id"></cabinet-contacts-item>
				<spinner v-if="loading" :size="70" :line-size="15" line-fg-color="#e41f25" v-bem.spinner></spinner>
			</div>
			<button v-bem.more-button v-if="amountOfPages > 1 && amountOfPages !== currentPage && !loading" @click="loadMore">Показать еще</button>
		</div>
	</div>
</template>

<script>
	import cabinetContactsItem from 'components-vue/cabinet-contacts/cabinet-contacts-item';
	import chooseLocation from 'components-vue/cabinet-contacts/choose-location';
	import spinner from 'vue-simple-spinner';

	module.exports = {
		data() {
			return {
				'geo': {},
				'primaryGeo': {},
				'chooseLocation': false,
				'supportTypes': [],
				'currentSupportType': '',
				'supportQuestions': [],
				'currentSupportQuestion': '',
				'searchStr': '',
				'specialists': {},
				'loading': true,
				'loadingManager': true,
				'manager': {},
				'geoInit': false,
				'currentPage': '1',
				'amountOfPages': '',
			};
		},
		props: [],
		computed: {
			currentLocationTitle: function() {
				if (this.geo.cityName) {
					return this.$ml.get('city');
				} else if (this.geo.countryName) {
					return this.$ml.get('country') + ':';
				}
				return this.$ml.get('city');
			},
			currentLocation: function() {
				if (this.geo.cityName) {
					return this.geo.cityName;
				} else if (this.geo.countryName) {
					return this.geo.countryName;
				}
				return '';
			},
			supportType: {
				get() { return this.currentSupportType; },
				set(value) {
					this.specialists = {};
					this.supportQuestions = {};
					this.searchStr = '';

					this.currentSupportType = value;
					this.getSupportQuestions();
				}
			},
			supportQuestion: {
				get() { return this.currentSupportQuestion; },
				set(value) {
					this.specialists = {};
					this.searchStr = '';
				}
			},
			shouldShowSupportTypes() {
				return this.countElements(this.supportTypes) > 1;
			}
		},
		methods: {
			setSpecialists(value) {
				this.specialists = {};
				this.searchStr = '';

				console.log(value);
				console.log(this.currentSupportQuestion);
				console.log(value == this.currentSupportQuestion);

				if (value == this.currentSupportQuestion) {
					this.loading = true;
					this.currentSupportQuestion = '';

					this.$api.init().then(data => {
						this.specialists = data['specialists'];
						this.amountOfPages = data['amountOfPages'];
						this.supportTypes = data['supportTypes']
						this.supportQuestions = data['supportQuestions'];
						this.loading = false;
					});
				} else {
					this.currentSupportQuestion = value;

					if (value !== '') {
						this.getSpecialists();
					}
				}
			},
			getSupportTypes() {
				this.loading = true;
				return this.$api.loadSupportTypes().then(data => {
					this.loading = false;
					this.supportTypes = data;
					this.currentSupportType = this.supportTypes[0].id;
				});
			},
			getSupportQuestions() {
				this.loading = true;
				this.supportQuestions = {};
				this.specialists = {};
				let params = {
					'supportTypeId': this.currentSupportType.id,
				};
				if (this.geo['countryId']) {
					params['countryId'] = this.geo['countryId'];
				}
				if (this.geo['regionId']) {
					params['regionId'] = this.geo['regionId'];
				}
				if (this.geo['cityId']) {
					params['cityId'] = this.geo['cityId'];
				}
				return this.$api.loadSupportQuestions(params).then(data => {
					console.log(data);
					this.supportQuestions = data;
					this.currentSupportQuestion = '';
					this.loading = false;
				});
			},
			getSpecialists() {
				this.loading = true;
				this.$api.loadSpecialists(
					{
						'questionId': this.currentSupportQuestion,
						'countryId': this.geo['countryId'],
						'regionId': this.geo['regionId'],
						'cityId': this.geo['cityId'],
					}).then(data => {
						this.specialists = data['specialists'];
						this.amountOfPages = data['amountOfPages'];
						this.loading = false;
						this.handleFavourites();
						this.scrollToSpecialists();
					}
				);
			},
			handleFavourites() {
				let langCode = $('body').data('lang');
				/* global $ */
				// TODO: make possible more than one link on a page
				var addToFavLink = function($block) {
					this._$block = $block;
					this._$textBlock = $block.find('span');
					this._removeText = this._$block.attr('data-remove-text');
					this._addText = this._$block.attr('data-add-text');
					this.elementId = parseInt(this._$block.attr('data-element-id'), 10);
					this.action = this._$block.hasClass('add-to-favourites--remove') ? 'remove' : 'add'
				};

				addToFavLink.prototype.setRemove = function setRemove() {
					this._$textBlock.text(this._removeText);
					this._$block.addClass('add-to-favourites--remove');
				};

				addToFavLink.prototype.setAdd = function setAdd() {
					this._$textBlock.text(this._addText);
					this._$block.removeClass('add-to-favourites--remove');
				};

				addToFavLink.prototype.show = function show() {
					this._$block.addClass('add-to-favourites--visible');
				};

				addToFavLink.prototype.hide = function hide() {
					this._$block.removeClass('add-to-favourites--visible');
				};

				var addSuccess = function addSuccess(link) {
					link.setRemove();
				};

				var addFailed = function addFailed(link, data, message) {
					alert(message);
				};

				var removeSuccess = function addSuccess(link) {
					link.setAdd();
				};

				var removeFailed = function addFailed(link, data, message) {
					alert(message);
				};

				$(function() {
					$('.add-to-favourites').each(function() { // TODO: optimize to one request for multiple links

						var link = new addToFavLink($(this));
						if (!link.elementId) {
							return false;
						}

						$.ajax({
							method: 'GET',
							url: '/local/components/techart/add_to_favourites/ajax/status.php',
							data: {element_id: link.elementId, isHL: true},
							success: function(data) {
								if (data.status != 'success' && data.statusCode !== 'NOAUTH') {
									return;
								}
								if (data.statusCode != 'NOTINFAV' && data.statusCode !== 'NOAUTH') {
									link.setRemove()
								}
								link.show();
							}
						});
					});

					$('.add-to-favourites').click(function() {
						var link = new addToFavLink($(this));
						if (!link.elementId) {
							return false;
						}
						if (link.action != 'remove') {
							$.ajax({
								method: 'POST',
								url: '/local/components/techart/add_to_favourites/ajax/add.php',
								data: {element_id: link.elementId, isHL: true},
								success: function (data) {
									if (data.statusCode === 'NOAUTH') {
										console.log();
										BEM.Registry.waitBlock('b-header').then(header => header.showLoginForm(header.elem('form-link')));
									}

									data.status == 'success' ?
										addSuccess(link, data) :
										addFailed(link, data, messages.add[data.statusCode] ? messages.add[data.statusCode] : messages.add['UNK']);
								},
								error: function (data) {
									addFailed(link, data, lang[langCode]['add-NOEL']);
								}
							});
						} else {
							$.ajax({
								method: 'POST',
								url: '/local/components/techart/add_to_favourites/ajax/remove.php',
								data: {element_id: link.elementId, isHL: true},
								success: function(data) {
									if (data.statusCode === 'NOAUTH') {
										BEM.Registry.waitBlock('b-header').then(header => header.showLoginForm(header.elem('form-link')));
									}

									data.status == 'success' ?
										removeSuccess(link, data) :
										removeFailed(link, data, messages.remove[data.statusCode] ? messages.remove[data.statusCode] : messages.remove['UNK']);
								},
								error: function (data) {
									removeFailed(link, data, lang[langCode]['remove-NOEL']);
								}
							});
						}
						return false;
					});
				});
			},
			getCurrentManager() {
				this.loadingManager = true;
				this.manager = {};
				this.specialists = {};
				let params = {};
				if (this.geo['countryId']) {
					params['countryId'] = this.geo['countryId'];
					params['countryName'] = this.geo['countryName'];
				}
				if (this.geo['regionId']) {
					params['regionId'] = this.geo['regionId'];
					params['regionName'] = this.geo['regionName'];
				}
				if (this.geo['cityId']) {
					params['cityId'] = this.geo['cityId'];
					params['cityName'] = this.geo['cityName'];
				}
				return this.$api.loadCurrentManager(params).then(data => {
					this.manager = data;
					localStorage.setItem('contactsManager', JSON.stringify(data));
					this.loadingManager = false;
				});
			},
			searchSpecialists() {
				var that = this;

				setTimeout(() => {
					if (that.searchStr !== '') {
						that.specialists = {};
						that.currentSupportQuestion = '';
						that.loading = true;
						that.$api.loadSpecialists(
							{
								'searchStr': that.searchStr,
							}).then(data => {
								that.specialists = data['specialists'];
								that.amountOfPages = data['amountOfPages'];
								that.loading = false;
							}
						);
					}
				}, 0);
 			},
			clearSearch() {
				this.searchStr = '';

				this.loading = true;

				this.$api.init().then(data => {
					this.specialists = data['specialists'];
					this.amountOfPages = data['amountOfPages'];
					this.loading = false;
				});
			},
			countElements(object) {
				let count = 0;
				for(let key in object) {
					count++;
				}
				return count;
			},
			scrollToSpecialists() {
				if (document.documentElement.clientWidth < 970) {
					document.querySelector('.b-contacts-app__specialists').scrollIntoView({
						behavior: 'smooth',
						block: 'start',
					});
				}
			},
			loadMore() {
					this.$api.loadSpecialists(
					{
						'page': parseInt(this.currentPage) + parseInt(1),
					}).then(data => {
						this.currentPage = parseInt(this.currentPage) + parseInt(1);
						this.specialists = this.specialists.concat(data['specialists']);
						this.amountOfPages = data['amountOfPages'];
						this.loading = false;
						this.handleFavourites();
						this.scrollToSpecialists();
					}
				);
			}
		},
		watch: {},
		components: {cabinetContactsItem, spinner},
		mixins:[],
		created() {
			this.$api.setSiteDir(siteDir);
			this.$api.setLang(this.lang);
			this.$api.init().then(data => {
				this.specialists = data['specialists'];
				this.amountOfPages = data['amountOfPages'];
				this.supportTypes = data['supportTypes']
				this.supportQuestions = data['supportQuestions'];
				this.handleFavourites();
				this.loading = false;
			});
		},
	}
</script>