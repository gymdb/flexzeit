/* global require */
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
  ['messages', 'errors', 'lessons', 'registrations', 'student', 'bugreports'].forEach(file => {
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
Vue.component('bug-report', require('./components/BugReport.vue'));
Vue.component('confirm', require('./components/Confirm.vue'));
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
Vue.component('teacher-register-student', require('./components/teacher/RegisterStudent.vue'));
Vue.component('teacher-substitute', require('./components/teacher/Substitute.vue'));
Vue.component('course-create', require('./components/course/Create.vue'));
Vue.component('course-edit', require('./components/course/Edit.vue'));
Vue.component('course-show', require('./components/course/Show.vue'));

// Register components for student pages
Vue.component('student-documentation', require('./components/student/Documentation.vue'));
Vue.component('student-register', require('./components/student/Register.vue'));
Vue.component('student-registrations', require('./components/student/Registrations.vue'));

// Register vue-strap components
require('vue-strap/dist/vue-strap-lang');
Vue.component('dropdown', require('vue-strap/src/Dropdown.vue'));
Vue.component('modal', require('vue-strap/src/Modal.vue'));
Vue.component('popover', require('vue-strap/src/Popover.vue'));
Vue.component('v-select', require('vue-strap/src/Select.vue'));

Vue.prototype.moment = require('moment');

// Workaround: Bug in Popover component
// noinspection JSUnresolvedVariable
Vue.options.components.popover.options.methods.position = function () {
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
};

// noinspection JSUnusedLocalSymbols
const app = new Vue({
  i18n,
  el: '#app'
});
