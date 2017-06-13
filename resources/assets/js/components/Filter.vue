<template>
  <div class="row clearfix hidden-print">
    <div class="col-xs-12">
      <error :error="error">{{errorText}}</error>
    </div>

    <div v-if="showGroup" class="form-group col-sm-3 col-xs-6">
      <label for="group" class="sr-only">{{groupLabel}}</label>
      <select class="form-control" id="group" v-model="group">
        <option :value="null">{{groupLabel}}</option>
        <option v-for="g in groups" :value="g.id">{{g.name}}</option>
      </select>
    </div>

    <div v-if="groups" class="form-group col-sm-3 col-xs-6">
      <label for="student" class="sr-only">{{studentLabel}}</label>
      <select class="form-control" id="student" :disabled="!group" v-model="student">
        <option :value="null">{{studentLabel}}</option>
        <option v-for="s in students" :value="s.id">{{s.name}}</option>
      </select>
    </div>

    <div v-if="teachers" class="form-group col-sm-3 col-xs-6">
      <label for="teacher" class="sr-only">{{teacherLabel}}</label>
      <select class="form-control" id="teacher" v-model="teacher">
        <option :value="null">{{teacherLabel}}</option>
        <option v-for="t in teachers" :value="t.id">{{t.name}}</option>
      </select>
    </div>

    <div v-if="subjects" class="form-group col-sm-3 col-xs-6">
      <label for="subject" class="sr-only">{{subjectLabel}}</label>
      <select class="form-control" id="subject" v-model="subject">
        <option :value="null">{{subjectLabel}}</option>
        <option v-for="s in subjects" :value="s.id">{{s.name}}</option>
      </select>
    </div>

    <daterange v-if="minDate"
               :type="1"
               :min-date="minDate"
               :max-date="maxDate"
               :disabled-days-of-week="disabledDaysOfWeek"
               :disabled-dates="disabledDates"
               :old-first-date="null"
               :old-last-date="null"
               :label-first="startLabel"
               :label-last="endLabel"
               :hide-labels="true"
               v-on:first="setStart"
               v-on:last="setEnd">
    </daterange>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        // Workaround for some weird behaviour: Property fires change event on parent re-rendering
        groupsList: this.groups,
        subjectsList: this.subjects,
        teachersList: this.teachers,
        teacher: null,
        group: this.groups && this.groups.length === 1 ? this.groups[0].id : null,
        student: null,
        subject: this.subjects && this.subjects.length === 1 ? this.subjects[0].id : null,
        start: null,
        end: null,
        students: [],
        error: null
      }
    },
    props: {
      teachers: {
        'type': Array,
        'default': null
      },
      groups: {
        'type': Array,
        'default': null
      },
      subjects: {
        'type': Array,
        'default': null
      },
      minDate: {
        'type': String,
        'default': null
      },
      maxDate: {
        'type': String,
        'default': null
      },
      disabledDaysOfWeek: {
        'type': Array,
        'default': function () {
          return [];
        }
      },
      disabledDates: {
        'type': Array,
        'default': function () {
          return [];
        }
      },
      errorText: {
        'type': String,
        'required': true
      },
      teacherLabel: {
        'type': String,
        'default': ''
      },
      groupLabel: {
        'type': String,
        'default': ''
      },
      studentLabel: {
        'type': String,
        'default': ''
      },
      subjectLabel: {
        'type': String,
        'default': ''
      },
      startLabel: {
        'type': String,
        'default': ''
      },
      endLabel: {
        'type': String,
        'default': ''
      }
    },
    watch: {
      group(newGroup) {
        this.student = null;
        this.students = [];
        if (newGroup && this.groups) {
          let self = this;
          this.$http.get('/teacher/api/students', {
            params: {
              group: newGroup
            }
          }).then(function (response) {
            self.error = null;
            self.students = response.data;
          }).catch(function (error) {
            self.error = error.response ? error.response.status : 100;
          });
        }
      },
      filter: _.debounce(function(filter) {
        this.$emit('filter', filter);
      }, 50)
    },
    created() {
      this.$emit('filter', this.filter);
    },
    computed: {
      showGroup() {
        return this.groups && this.groups.length > 1;
      },
      filter() {
        if (this.groupsList && (!this.group || !this.student)) {
          return null;
        }

        let filter = {};

        if (this.groupsList) {
          filter.group = this.group;
          filter.student = this.student;
        }
        if (this.teachersList) {
          filter.teacher = this.teacher;
        }
        if (this.subjectsList) {
          filter.subject = this.subject;
        }
        if (this.minDate) {
          filter.start = this.start ? this.start.format('YYYY-MM-DD') : null;
          filter.end = this.end ? this.end.format('YYYY-MM-DD') : null;
        }
        return filter;
      }
    },
    methods: {
      setStart(date) {
        this.start = date;
      },
      setEnd(date) {
        this.end = date;
      }
    }
  }
</script>
