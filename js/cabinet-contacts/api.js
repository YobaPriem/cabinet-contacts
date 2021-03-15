class contactsApi {
	constructor(http) {
		this._http = http;
		this._siteDir = '/';
		this._url = 'cabinet-contacts-api/';
		this._limited = 0;
		this._lang = 'ru';
		this.prevRequests = [];
	}

	setSiteDir(siteDir) {
		this._siteDir = siteDir;
	}

	setLimited() {
		this._limited = 1;
	}

	setLang(lang) {
		this._lang = lang;
	}

	httpGet(url, params = {}) {
		const that = this;
		params.lang = this._lang;
		return new Promise((resolve) =>
			this._http.get(url, {params: params, before(request) {
				if (that.prevRequests.length > 0) {
					for (let i = 0; i < that.prevRequests.length; i++) {
						if (that.prevRequests[i].url == url) {
							that.prevRequests[i].abort();
							that.prevRequests.splice(i, 1);
							break;
						}
					}
				}
				that.prevRequests.push(request);
			}}).then(response => {
				if (that.prevRequests.length > 0) {
					for (let i = 0; i < that.prevRequests.length; i++) {
						if (that.prevRequests[i].url == url) {
							that.prevRequests.splice(i, 1);
							break;
						}
					}
				}

				if(response.data.success === true) {
					resolve(response.data.dataSet);
				} else {
					console.warn(response);
					resolve([]);
				}
			}).catch(function() {
				// console.log(e);
			})
		);
	}

	init() {
		return this.httpGet(this._siteDir + this._url + 'init-page-data/', {});
	}

	loadConstrCategories() {
		return this.httpGet(this._siteDir + this._url + 'get-constr-categories/');
	}

	loadSupportTypes() {
		return this.httpGet(this._siteDir + this._url + 'get-support-types/', {limited: this._limited});
	}

	loadSupportQuestions(params) {
		return this.httpGet(this._siteDir + this._url + 'get-support-questions/', params);
	}

	loadSpecialists(params) {
		return this.httpGet(this._siteDir + this._url + 'get-support-specialists/', params);
	}
}

export default contactsApi;