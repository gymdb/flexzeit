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
                   v-on:filter="setFilter"
                   v-on:data="setData">
      <template slot="chooseStudent"></template>
      <template slot="empty">
        <p v-if="!saveDisabled" class="text-center"><strong>{{$t('registrations.register.confirm')}}</strong></p>
      </template>
      <template scope="props">
        <div v-if="isSameLesson" class="alert alert-danger">
          <strong>{{$t('registrations.warnings.sameLesson')}}</strong>
        </div>
        <div v-else class="alert alert-warning">
          <strong>{{$t('registrations.warnings.heading')}}</strong>
          <ul>
            <li v-for="(data, key) in props.data">
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

        <p v-if="!saveDisabled" class="text-center">
          <strong>{{$t(course ? 'registrations.register.confirmCourse' : 'registrations.register.confirm')}}</strong>
        </p>
      </template>
    </filtered-list>

    <error :error="error">{{$t('registrations.register.saveError')}}</error>
  </modal>
</template>

<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        shown: false,
        url: '/teacher/api/registrations/warnings/' + (this.course ? 'course' : 'lesson') + '/' + this.id,
        student: null,
        registeredLesson: null,
        registeredLessons: null,
        timetable: null,
        saving: false,
        error: null,
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
      isSameLesson() {
        return !this.course && this.registeredLesson && this.registeredLesson === this.id;
      },
      saveDisabled() {
        return this.saving || !this.student || this.isSameLesson
            || (!this.admin && (this.registeredLesson || this.registeredLessons || this.timetable));
      },
      submitLabel() {
        return (this.admin && this.registeredLesson && !this.isSameLesson)
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
        this.student = null;
      },
      setData(data) {
        if (data) {
          this.student = this.$refs.filter.student;
          if (data.lesson) {
            this.registeredLesson = data.lesson.id;
          } else if (data.course) {
            this.registeredLesson = data.course.id;
          } else {
            this.registeredLesson = null;
          }

          this.registeredLessons = !!data.lessons;
          this.timetable = !!data.timetable;
        } else {
          this.student = null;
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;
          this.$http.post('/teacher/api/register/' + (this.course ? 'course' : 'lesson') + '/' + this.id + '/' + this.student, {}).then(function (response) {
            if (response.data.success) {
              self.error = null;
              self.reload = true;
              self.$refs.filter.student = null;
            } else {
              self.error = response.data.error;
            }
            self.saving = false;
          }).catch(function (error) {
            self.saving = false;
            self.error = error;
          });
        }
      }
    }
  }
</script>
