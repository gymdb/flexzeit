/* global require */
require('es6-promise').polyfill();
require('./bootstrap');

import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueI18n from 'vue-i18n';

/** @namespace Vue.config */
Vue.config.debug = true;
Vue.config.devtools = true;

/** @namespace Vue.use */
Vue.use(VueAxios, axios);
Vue.use(VueI18n);

const messages = {};
['en', 'de'].forEach(lang => {
  messages[lang] = {};
  ['messages', 'errors', 'lessons', 'courses', 'registrations', 'student', 'bugreports'].forEach(file => {
    messages[lang][file] = require('./lang/' + lang + '/' + file);
  });
});

const dateTimeFormats = {
  'en': {
    short: {
      year: 'numeric', month: '2-digit', day: '2-digit', weekday: 'short'
    },
    datetime: {
      year: 'numeric', month: '2-digit', day: '2-digit', hour: 'numeric', minute: 'numeric', hour12: true
    }
  },
  'de': {
    short: {
      year: 'numeric', month: '2-digit', day: '2-digit', weekday: 'short'
    },
    datetime: {
      year: 'numeric', month: '2-digit', day: '2-digit', hour: 'numeric', minute: 'numeric', hour12: false
    }
  }
};

//noinspection JSUnresolvedVariable
const i18n = new VueI18n({
  locale: document.documentElement.lang,
  fallbackLocale: 'en',
  messages,
  dateTimeFormats
});

/** @namespace Vue.component */

// Register general components
Vue.component('bug-report', require('./components/BugReport.vue').default);
Vue.component('confirm', require('./components/Confirm.vue').default);
Vue.component('datepicker', require('./components/Datepicker.vue').default);
Vue.component('daterange', require('./components/Daterange.vue').default);
Vue.component('error', require('./components/Error.vue').default);
Vue.component('filtered-list', require('./components/FilteredList.vue').default);
Vue.component('unregister', require('./components/Unregister.vue').default);

// Register components for teacher pages
Vue.component('teacher-absent', require('./components/teacher/Absent.vue').default);
Vue.component('teacher-attendance', require('./components/teacher/Attendance.vue').default);
Vue.component('teacher-bugreports', require('./components/teacher/BugReports.vue').default);
Vue.component('teacher-excused', require('./components/teacher/Excused.vue').default);
Vue.component('teacher-feedback', require('./components/teacher/Feedback.vue').default);
Vue.component('teacher-lesson', require('./components/teacher/Lesson.vue').default);
Vue.component('teacher-register', require('./components/teacher/Register.vue').default);
Vue.component('teacher-register-student', require('./components/teacher/RegisterStudent.vue').default);
Vue.component('teacher-substitute', require('./components/teacher/Substitute.vue').default);
Vue.component('teacher-trash-report', require('./components/teacher/TrashReport.vue').default);
Vue.component('course-create', require('./components/course/Create.vue').default);
Vue.component('course-edit', require('./components/course/Edit.vue').default);
Vue.component('course-show', require('./components/course/Show.vue').default);

// Register components for student pages
Vue.component('student-documentation', require('./components/student/Documentation.vue').default);
Vue.component('student-register', require('./components/student/Register.vue').default);
Vue.component('student-registrations', require('./components/student/Registrations.vue').default);

// Register vue-strap components
require('vue-strap/dist/vue-strap-lang');
Vue.component('dropdown', require('vue-strap/src/Dropdown.vue').default);
Vue.component('modal', require('vue-strap/src/Modal.vue').default);
Vue.component('popover', require('vue-strap/src/Popover.vue').default);
Vue.component('v-select', require('vue-strap/src/Select.vue').default);

Vue.prototype.moment = require('moment');

// Workaround: Bug in Popover component
// noinspection JSUnresolvedVariable
/** Vue.options.components.popover.options.methods.position = function () {
  // noinspection JSUnresolvedFunction
  this.$nextTick(() => {
    // noinspection JSUnresolvedVariable
    const popover = this.$refs.popover;
    if (!popover) {
      return;
    }
    // noinspection JSUnresolvedVariable
    const trigger = this.$refs.trigger.children[0];
    const pos = $(trigger).offset();
    this.left = pos.left + trigger.offsetWidth;
    this.top = pos.top + trigger.offsetHeight / 2 - popover.offsetHeight / 2;
    popover.style.top = this.top + 'px';
    popover.style.left = this.left + 'px';
  });
}; **/

// noinspection JSUnusedLocalSymbols
const app = new Vue({
  i18n,
  el: '#app',
  computed: {
    categoryOptionsList() {
      const options = [];
      for (let i = 0; i <= 5; i++) {
        options.push({ label: this.$t('courses.category')[i]});
      }
      return options;
    },
  }
},);
