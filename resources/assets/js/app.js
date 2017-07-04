/* global require */
require('./bootstrap');

import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueI18n from 'vue-i18n';

Vue.config.debug = true;
Vue.config.devtools = true;

Vue.use(VueAxios, axios);
Vue.use(VueI18n);

const messages = {};
['en', 'de'].forEach(lang => {
  messages[lang] = {};
  ['messages', 'errors', 'registrations', 'student'].forEach(file => {
    messages[lang][file] = require('./lang/' + lang + '/' + file);
  });
});

const dateTimeFormats = {
  'en': {
    short: {
      year: 'numeric', month: '2-digit', day: '2-digit'
    }
  },
  'de': {
    short: {
      year: 'numeric', month: '2-digit', day: '2-digit'
    }
  }
};

const i18n = new VueI18n({
  locale: window.Laravel.lang,
  fallbackLocale: 'en',
  messages,
  dateTimeFormats
});

// Register general components
Vue.component('datepicker', require('./components/Datepicker.vue'));
Vue.component('daterange', require('./components/Daterange.vue'));
Vue.component('error', require('./components/Error.vue'));
Vue.component('filtered-list', require('./components/FilteredList.vue'));
Vue.component('unregister', require('./components/Unregister.vue'));

// Register components for teacher pages
Vue.component('teacher-absent', require('./components/teacher/Absent.vue'));
Vue.component('teacher-attendance', require('./components/teacher/Attendance.vue'));
Vue.component('teacher-excused', require('./components/teacher/Excused.vue'));
Vue.component('teacher-feedback', require('./components/teacher/Feedback.vue'));
Vue.component('teacher-lesson', require('./components/teacher/Lesson.vue'));
Vue.component('teacher-register', require('./components/teacher/Register.vue'));
Vue.component('course-create', require('./components/course/Create.vue'));

// Register components for student pages
Vue.component('student-documentation',require('./components/student/Documentation.vue'));
Vue.component('student-register', require('./components/student/Register.vue'));
Vue.component('student-registrations',require('./components/student/Registrations.vue'));

// Register vue-strap components
Vue.component('dropdown', require('vue-strap/src/Dropdown.vue'));
Vue.component('modal', require('vue-strap/src/Modal.vue'));
Vue.component('popover', require('vue-strap/src/Popover.vue'));

Vue.prototype.moment = require('moment');

const app = new Vue({
  i18n,
  el: '#app'
});
