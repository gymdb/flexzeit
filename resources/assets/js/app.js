require('./bootstrap');

import axios from 'axios';
import VueAxios from 'vue-axios';

Vue.config.debug = true;
Vue.config.devtools = true;

Vue.use(VueAxios, axios);

// Register general components
Vue.component('datepicker', require('./components/Datepicker.vue'));
Vue.component('daterange', require('./components/Daterange.vue'));
Vue.component('error', require('./components/Error.vue'));
Vue.component('error-container', require('./components/ErrorContainer.vue'));
Vue.component('filter-options', require('./components/Filter.vue'));
Vue.component('unregister', require('./components/Unregister.vue'));

// Register components for teacher pages
Vue.component('attendance', require('./components/teacher/Attendance.vue'));
Vue.component('documentation', require('./components/teacher/Documentation.vue'));
Vue.component('feedback-edit', require('./components/teacher/FeedbackEdit.vue'));
Vue.component('feedback-show', require('./components/teacher/FeedbackShow.vue'));
Vue.component('teacher-lesson', require('./components/teacher/Lesson.vue'));
Vue.component('teacher-lessons', require('./components/teacher/Lessons.vue'));
Vue.component('course-create', require('./components/course/Create.vue'));

// Register components for student pages
Vue.component('student-register', require('./components/StudentRegister.vue'));

// Register vue-strap components
Vue.component('dropdown', require('vue-strap/src/Dropdown.vue'));
Vue.component('modal', require('vue-strap/src/Modal.vue'));
Vue.component('popover', require('vue-strap/src/Popover.vue'));

const app = new Vue({
  el: '#app'
});
