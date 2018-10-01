<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <div>
    <div class="row clearfix hidden-print">
      <div v-if="showGroup" class="form-group col-sm-3 col-xs-6">
        <label for="group" class="sr-only">{{$t('messages.group')}}</label>
        <select class="form-control" id="group" v-model="group">
          <option :value="null">{{$t('messages.group')}}</option>
          <option v-for="g in groupsList" :value="g.id">{{g.name}}</option>
        </select>
      </div>

      <div v-if="!multipleStudents && groupsList && showStudent" class="form-group col-sm-3 col-xs-6">
        <label for="student" class="sr-only">{{$t('messages.student')}}</label>
        <select class="form-control" id="student" :disabled="!group" v-model="student">
          <option :value="null">{{$t('messages.student')}}</option>
          <option v-for="s in studentsList" :value="s.id">{{s.name}}</option>
        </select>
      </div>
      <div v-if="multipleStudents && groupsList && showStudent" class="form-group col-sm-3 col-xs-6">
        <label for="students" class="sr-only">{{$t('messages.student')}}</label>
        <v-select class="select-container" id="students" :disabled="!group" v-model="students" multiple search
            :placeholder="$t('messages.student')" :options="studentsList" options-value="id" options-label="name">
        </v-select>
      </div>

      <div v-if="teachersList" class="form-group col-sm-3 col-xs-6">
        <label for="teacher" class="sr-only">{{$t('messages.teacher')}}</label>
        <select class="form-control" id="teacher" v-model="teacher">
          <option :value="null">{{$t('messages.teacher')}}</option>
          <option v-for="t in teachersList" :value="t.id">{{t.name}}</option>
        </select>
      </div>

      <div v-if="subjectsList" class="form-group col-sm-3 col-xs-6">
        <label for="subject" class="sr-only">{{$t('messages.subject')}}</label>
        <select class="form-control" id="subject" v-model="subject">
          <option :value="null">{{$t('messages.subject')}}</option>
          <option v-for="s in subjectsList" :value="s.id">{{s.name}}</option>
        </select>
      </div>

      <div v-if="typesList" class="form-group col-sm-3 col-xs-6">
        <label for="type" class="sr-only">{{$t('messages.type')}}</label>
        <select class="form-control" id="type" v-model="type">
          <option :value="null">{{$t('messages.type')}}</option>
          <option v-for="type in typesList">{{type}}</option>
        </select>
      </div>

      <daterange v-if="minDate"
          :type="1"
          :default-start-date="defaultStartDate"
          :default-end-date="defaultEndDate"
          :min-date="minDate"
          :max-date="maxDate"
          :disabled-days-of-week="disabledDaysOfWeek"
          :disabled-dates="disabledDates"
          :old-first-date="initialStart"
          :old-last-date="initialEnd"
          :label-first="$t('messages.from')"
          :label-last="$t('messages.to')"
          hide-labels
          v-on:first="setStart"
          v-on:last="setEnd">
      </daterange>

      <div v-if="hasTrashed" class="form-group col-sm-3 col-xs-6">
        <label for="showTrashed" class="sr-only">{{$t('messages.showTrashed')}}</label>
        <label class="checkbox-inline">
          <input type="checkbox" id="showTrashed" v-model="showTrashed"/> {{$t('messages.showTrashed')}}
        </label>
      </div>

      <div class="col-xs-12">
        <error :error="studentsError">{{$t('messages.studentsError')}}</error>
      </div>

      <div class="col-xs-12">
        <error :error="dataError">{{errorText}}</error>
      </div>
    </div>

    <dl class="dl-horizontal dl-narrow visible-print">
      <dt v-if="groupName">{{$t('messages.group')}}</dt>
      <dd v-if="groupName">{{groupName}}</dd>

      <dt v-if="studentName">{{$t('messages.student')}}</dt>
      <dd v-if="studentName">{{studentName}}</dd>

      <dt v-if="teacherName">{{$t('messages.teacher')}}</dt>
      <dd v-if="teacherName">{{teacherName}}</dd>

      <dt v-if="subjectName">{{$t('messages.subject')}}</dt>
      <dd v-if="subjectName">{{subjectName}}</dd>

      <dt v-if="type">{{$t('messages.type')}}</dt>
      <dd v-if="type">{{type}}</dd>

      <dt v-if="start">{{$t('messages.from')}}</dt>
      <dd v-if="start">{{start.format('L')}}</dd>

      <dt v-if="end">{{$t('messages.to')}}</dt>
      <dd v-if="end">{{end.format('L')}}</dd>
    </dl>

    <slot v-if="!filter" name="chooseStudent">
      <div class="alert alert-info">{{$t(requireStudent ? 'messages.chooseStudent' : 'messages.chooseGroup')}}</div>
    </slot>
    <p v-else-if="loading" class="lead text-center"><span class="glyphicon glyphicon-refresh spin"></span></p>
    <slot v-else-if="!hasData" name="empty" :studentName="studentName">
      <div class="alert alert-warning">{{$t('messages.emptyResult')}}</div>
    </slot>

    <slot v-if="hasData" :data="data" :filter="filter" :studentName="studentName" :sorted="sorted"></slot>
  </div>
</template>

<script>
  import moment from 'moment';
  import _ from 'lodash';

  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      let params = {};
      if (location.search) {
        location.search.substr(1).split("&")
            .forEach(item => {
              let [k, v] = item.split("=", 2);
              if (k && v) {
                params[k] = decodeURIComponent(v);
              }
            });
      }

      let group = this.groups && this.groups.length === 1 ? this.groups[0].id : (params.group || this.defaultGroup);
      if (group) {
        this.loadStudents(group);
      }

      return {
        // Workaround for some weird behaviour: Property fires change event on parent re-rendering
        groupsList: this.groups,
        studentsList: [],
        teachersList: this.teachers,
        subjectsList: this.subjects,
        typesList: this.roomTypes,
        group: group,
        student: params.student || null,
        students: [],
        teacher: params.teacher || null,
        subject: this.subjects && this.subjects.length === 1 ? this.subjects[0].id : (params.subject || null),
        type: params.type || null,
        showTrashed: params.showTrashed || null,
        initialStart: params.start || null,
        initialEnd: params.end || null,
        start: params.start ? moment(params.start) : null,
        end: params.end ? moment(params.end) : null,
        data: null,
        loading: false,
        studentsError: null,
        dataError: null
      }
    },
    props: {
      url: {
        'type': String,
        'required': true
      },
      groups: {
        'type': Array,
        'default': null
      },
      teachers: {
        'type': Array,
        'default': null
      },
      subjects: {
        'type': Array,
        'default': null
      },
      roomTypes: {
        'type': Array,
        'default': null
      },
      date: {
        'type': String,
        'default': null
      },
      number: {
        'type': Number,
        'default': null,
      },
      defaultGroup: {
        'type': Number,
        'default': null
      },
      defaultStartDate: {
        'type': String,
        'default': null
      },
      defaultEndDate: {
        'type': String,
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
      keepFilter: {
        'type': Boolean,
        'default': true
      },
      requireTeacher: {
        'type': Boolean,
        'default': false
      },
      requireGroup: {
        'type': Boolean,
        'default': true
      },
      requireStudent: {
        'type': Boolean,
        'default': true
      },
      showStudent: {
        'type': Boolean,
        'default': true
      },
      multipleStudents: {
        'type': Boolean,
        'default': false
      },
      hasTrashed: {
        'type': Boolean,
        'default': false
      },
      errorText: {
        'type': String,
        'required': true
      }
    },
    watch: {
      group(newGroup) {
        this.loadStudents(newGroup);
      },
      filter(filter) {
        this.loadData(filter);
        this.$emit('filter', filter);
      },
      data(data) {
        this.$emit('data', data);
      },
      query(query) {
        if (this.keepFilter) {
          // noinspection JSCheckFunctionSignatures
          window.history.replaceState(null, null, window.location.pathname + (query ? '?' + query : ''));
        }
      }
    },
    created() {
      this.loadData(this.filter);
    },
    computed: {
      hasData() {
        return !_.isEmpty(this.data);
      },
      showGroup() {
        return this.groupsList && this.groupsList.length > 1;
      },
      filter() {
        const hasStudent = this.multipleStudents ? !_.isEmpty(this.students) : this.student;
        if (this.groupsList && this.requireGroup && (!this.group || (this.requireStudent && !hasStudent))) {
          return null;
        }
        if (this.requireTeacher && !this.teacher) {
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
        if (this.typesList) {
          filter.type = this.type;
        }
        if (this.date) {
          filter.start = this.date;
          filter.end = this.date;
        } else if (this.minDate) {
          filter.start = this.start ? this.start.format('YYYY-MM-DD') : null;
          filter.end = this.end ? this.end.format('YYYY-MM-DD') : null;
        }
        if (this.number) {
          filter.number = this.number;
        }
        if (this.hasTrashed) {
          filter.showTrashed = this.showTrashed ? 1 : 0;
        }

        return filter;
      },
      query() {
        let params = {};

        if (this.groupsList) {
          if (this.group) {
            params.group = this.group;
          }
          if (this.student) {
            params.student = this.student;
          }
        }
        if (this.teachersList && this.teacher) {
          params.teacher = this.teacher;
        }
        if (this.subjectsList && this.subject) {
          params.subject = this.subject;
        }
        if (this.typesList && this.type) {
          params.type = this.type;
        }
        if (this.minDate) {
          if (this.start) {
            params.start = this.start.format('YYYY-MM-DD');
          }
          if (this.end) {
            params.end = this.end.format('YYYY-MM-DD');
          }
        }
        if (this.hasTrashed && this.showTrashed) {
          params.showTrashed = 1;
        }

        return $.param(params, true);
      },
      sorted() {
        return this.multipleStudents ? _.sortBy(this.data, 'name') : this.data;
      },
      groupName() {
        return this.showGroup ? this.findName(this.groupsList, +this.group) : null;
      },
      studentName() {
        return this.multipleStudents
            ? this.students.map(s => this.findName(this.studentsList, +s)).sort().join(", ")
            : this.findName(this.studentsList, +this.student);
      },
      teacherName() {
        return this.findName(this.teachersList, +this.teacher);
      },
      subjectName() {
        return this.findName(this.subjectsList, +this.subject);
      }
    },
    methods: {
      refresh() {
        this.loadData(this.filter);
      },
      loadStudents(group) {
        this.student = null;
        this.studentsList = [];
        if (group && this.groups) {
          let self = this;
          this.$http.get('teacher/api/students', {
            params: {
              group: group
            }
          }).then(function (response) {
            self.studentsError = null;
            self.studentsList = response.data;
          }).catch(function (error) {
            self.studentsError = error;
          });
        }
      },
      loadData: _.debounce(function (filter) {
        if (!filter) {
          this.dataError = null;
          this.data = null;
        } else {
          let self = this;
          this.loading = true;
          let promise = this.multipleStudents
              ? this.loadForMultipleStudents(filter)
              : this.loadForSingleStudent(filter);
          promise.catch(function (error) {
            self.dataError = error;
            self.data = null;
            self.loading = false;
          });
        }
      }, 50),
      loadForMultipleStudents(filter) {
        // Things get a little bit more complicated if data has to be loaded for multiple students
        let self = this;
        let students = this.students.slice(0);

        // Create a list of promises, reusing the already loaded data if available
        let promises = students.map(student => {
          return this.data && this.data[student]
              ? Promise.resolve({data: this.data[student].data})
              : this.$http.get(this.url, {params: Object.assign({}, filter, {student})});
        });

        // Resolve all promises together
        return Promise.all(promises).then(function (responses) {
          self.dataError = null;
          self.loading = false;

          let data = {};
          responses.forEach((response, i) => {
            if (!_.isEmpty(response.data)) {
              let student = students[i];
              data[student] = {
                name: self.findName(self.studentsList, +student),
                data: response.data
              };
            }
          });
          self.data = data;
        });
      },
      loadForSingleStudent(filter) {
        let self = this;
        return this.$http.get(this.url, {params: filter}).then(function (response) {
          self.dataError = null;
          self.data = response.data;
          self.loading = false;
        });
      },
      setStart(date) {
        this.start = date;
      },
      setEnd(date) {
        this.end = date;
      },
      findName(list, id) {
        if (!list || !id) {
          return null;
        }

        const item = list.find(item => item.id === id);
        return item ? item.name : null;
      }
    }
  }
</script>
