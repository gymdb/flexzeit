<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <modal v-if="shown" :value="show" effect="fade" @cancel="cancel" large
         :title="$t(course ? 'registrations.register.headingCourse' : 'registrations.register.heading')">
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{submitLabel}}</button>
    </div>

    <filtered-list ref="filter"
                   :url="url"
                   :groups="groups"
                   :error-text="$t('registrations.register.loadError')"
                   :keep-filter="false"
                   :multiple-students="true"
                   v-on:filter="setFilter"
                   v-on:data="setData">
      <template slot="chooseStudent"></template>
      <template slot="empty" slot-scope="props">
        <p v-if="!saveDisabled" class="text-center">
          <strong>{{$t(course ? 'registrations.register.confirmCourse' : 'registrations.register.confirm')}}</strong>
          <br>{{props.studentName}}
        </p>
      </template>
      <template slot-scope="props">
        <div v-for="student in props.sorted">
          <div v-if="isSameLesson(student.data)" class="alert alert-danger">
            <strong>{{student.name}}: {{$t('registrations.warnings.sameLesson')}}</strong>
          </div>
          <div v-else class="alert alert-warning">
            <strong>{{$t('registrations.warnings.heading')}}</strong> {{student.name}}
            <ul>
              <li v-for="(data, key) in student.data">
                {{$t('registrations.warnings.' + key, data)}}
                <ul v-if="key === 'lessons'">
                  <li v-for="lesson in data">
                    {{$d(moment(lesson.date), 'short')}}: {{lesson.teacher}}<span v-if="lesson.course"> ({{lesson.course}})</span>
                  </li>
                </ul>
                <ul v-if="key === 'offdays'">
                  <li v-for="offday in data">
                    {{$d(moment(offday.date), 'short')}}: {{offday.group}}
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        <p v-if="!saveDisabled" class="text-center">
          <strong>{{$t(course ? 'registrations.register.confirmCourse' : 'registrations.register.confirm')}}</strong>
          <br>{{props.studentName}}
        </p>
      </template>
    </filtered-list>

    <error v-if="errors" v-for="(error,i) in errors" :key="i" :error="error">
      {{$t('registrations.register.saveError')}}
    </error>
  </modal>
</template>

<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        shown: false,
        url: 'teacher/api/registrations/warnings/' + (this.course ? 'course' : 'lesson') + '/' + this.id,
        students: null,
        hasSameLesson: false,
        hasAdminOnly: false,
        hasChangeLesson: false,
        timetable: null,
        saving: false,
        errors: null,
        reload: false
      }
    },
    props: {
      groups: {
        'type': Array,
        'required': true
      },
      admin: {
        'type': Boolean,
        'default': false
      },
      id: {
        'type': Number,
        'required': true
      },
      course: {
        'type': Boolean,
        'default': false
      }
    },
    computed: {
      saveDisabled() {
        return this.saving || !this.students || this.hasSameLesson || (!this.admin && this.hasAdminOnly);
      },
      submitLabel() {
        return (this.admin && this.hasChangeLesson)
            ? this.$t('registrations.register.change')
            : this.$t('registrations.register.submit');
      }
    },
    methods: {
      open() {
        this.show = true;
        this.shown = true;
      },
      cancel() {
        this.show = false;
        if (this.reload) {
          location.reload();
        }
      },
      setFilter() {
        this.students = null;
      },
      isSameLesson(data) {
        return !this.course &&
            ((data.lesson && data.lesson.id === this.id) || (data.course && data.course.id === this.id));
      },
      setData(data) {
        if (data) {
          this.students = this.$refs.filter.students;
          this.hasSameLesson = false;
          this.hasAdminOnly = false;
          this.hasChangeLesson = false;
          _.each(data, value => {
            let studentData = value.data;
            if (this.isSameLesson(studentData)) {
              this.hasSameLesson = true;
            }
            if (studentData.lesson || studentData.course || studentData.lessons || studentData.timetable) {
              this.hasAdminOnly = true;
            }
            if (studentData.lesson || studentData.course) {
              this.hasChangeLesson = true;
            }
          });
        } else {
          this.students = null;
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;

          let promises = this.students.map(student => {
            let url = `teacher/api/register/${this.course ? 'course' : 'lesson'}/${this.id}/${student}`;
            return this.$http.post(url, {});
          });

          Promise.all(promises).then(function (responses) {
            let errors = responses
                .filter(r => !r.data.success)
                .map(r => r.data.error);
            if (_.isEmpty(errors)) {
              self.errors = null;
              self.reload = true;
              self.$refs.filter.students = [];
            } else {
              self.errors = errors;
            }
            self.saving = false;
          }).catch(function (error) {
            self.saving = false;
            self.errors = [error];
          });
        }
      }
    }
  }
</script>
