import Vue from 'vue'
import { MLInstaller, MLCreate, MLanguage } from 'vue-multilanguage'
import ruTranslations from './lang/ru.json';
import mdTranslations from './lang/md.json';
import kzTranslations from './lang/kz.json';


Vue.use(MLInstaller);

export default new MLCreate({
	initial: 'ru',
	save: process.env.NODE_ENV === 'production',
	languages: [
		new MLanguage('ru').create(ruTranslations),
		new MLanguage('md').create(mdTranslations),
		new MLanguage('kz').create(kzTranslations),
	],
});