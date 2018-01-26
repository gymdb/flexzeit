<!--suppress JSUnresolvedVariable, JSUnresolvedFunction, CommaExpressionJS -->
<template>
  <modal v-if="shown" :value="show" effect="fade" :title="title" @cancel="cancel" large>
    <div v-if="!lesson" class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" :disabled="true">{{$t('registrations.register.submit')}}</button>
    </div>
    <div v-else class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="selectLesson(null)">{{$t('registrations.student.back')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{submitLabel}}</button>
    </div>

    <div v-show="!lesson">
      <p v-if="date">
        <strong>{{$t('registrations.student.lessons', {date: $d(moment(date), 'short'), number: number})}}</strong>
      </p>
      <filtered-list
              url="/teacher/api/lessons"
              :teachers="teachers"
              :date="date"
              :number="number"
              :min-date="minDate"
              :max-date="maxDate"
              :disabled-days-of-week="disabledDaysOfWeek"
              :disabled-dates="disabledDates"
              :keepFilter="false"
              :error-text="$t('registrations.student.lessonsError')">
        <div slot="empty" class="alert alert-warning">{{$t('registrations.student.none')}}</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-condensed">
              <thead>
              <tr>
                <th v-if="!date">{{$t('messages.date')}}</th>
                <th v-if="!number">{{$t('messages.time')}}</th>
                <th>{{$t('messages.teacher')}}</th>
                <th>{{$t('messages.room')}}</th>
                <th>{{$t('messages.course')}}</th>
                <th>{{$t('messages.participants')}}</th>
                <th></th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="lesson in props.data" :class="{'text-muted': lesson.cancelled}">
                <td v-if="!date">{{$d(moment(lesson.date), 'short')}}</td>
                <td v-if="!number">{{$t('messages.range', lesson.time)}}</td>
                <td>{{lesson.teacher}}</td>
                <td>{{lesson.room}}</td>
                <td class="course">{{lesson.course ? lesson.course.name : ''}}</td>
                <td>{{lesson.participants}}<span v-if="lesson.maxstudents">/{{lesson.maxstudents}}</span></td>
                <td>
                  <a v-if="!lesson.cancelled" href="#" class="btn btn-xs btn-default" @click.prevent="selectLesson(lesson)">
                    {{$t('registrations.student.select')}}
                  </a>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
    <div v-show="lesson">
      <error :error="loadError">{{$t('registrations.student.warningsError')}}</error>
      <error :error="saveError">{{$t('registrations.student.saveError')}}</error>

      <p v-if="warningsLoading" class="lead text-center"><span class="glyphicon glyphicon-refresh spin"></span></p>
      <div v-else-if="isSameLesson" class="alert alert-danger">
        <strong>{{$t('registrations.warnings.sameLesson')}}</strong>
      </div>
      <div v-else-if="hasWarnings" class="alert alert-warning">
        <strong>{{$t('registrations.warnings.heading')}}</strong>
        <ul>
          <li v-for="(data, key) in warnings">
            {{$t('registrations.warnings.' + key, data)}}
          </li>
        </ul>
      </div>
    </div>
  </modal>
</template>

<script>
  import moment from 'moment';
  import _ from 'lodash';

  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        shown: false,
        student: null,
        lesson: null,
        date: null,
        number: null,
        warnings: null,
        warningsLoading: false,
        warningsLoaded: false,
        saving: false,
        loadError: null,
        saveError: null,
        reload: false
      }
    },
    props: {
      teachers: {
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
        'default': null
      },
      disabledDates: {
        'type': Array,
        'default': null
      }
    },
    watch: {
      lesson(lesson) {
        if (lesson) {
          let self = this;
          this.warningsLoading = true;
          this.warningsLoaded = false;
          this.warnings = null;
          this.$http.get('/teacher/api/registrations/warnings/lesson/' + lesson.id, {
            params: {
              student: this.student.id
            }
          }).then(function (response) {
            self.loadError = null;
            self.warningsLoading = false;
            self.warningsLoaded = true;
            self.warnings = response.data;
          }).catch(function (error) {
            self.warningsLoading = false;
            self.loadError = error;
          });
        }
      }
    },
    computed: {
      title() {
        if (!this.student) {
          return null;
        }
        return this.lesson
            ? this.$t('registrations.student.submitHeading', {
              student: this.student.name,
              date: this.$d(moment(this.lesson.date), 'short'),
              number: this.lesson.time.number,
              teacher: this.lesson.teacher
            })
            : this.$t('registrations.student.selectHeading', {student: this.student.name});
      },
      hasWarnings() {
        return !_.isEmpty(this.warnings);
      },
      registeredLesson() {
        if (!this.warnings) {
          return null;
        }
        if (this.warnings.lesson) {
          return this.warnings.lesson;
        }
        if (this.warnings.course) {
          return this.warnings.course;
        }
        return null;
      },
      isSameLesson() {
        return this.lesson && this.registeredLesson && this.registeredLesson.id === this.lesson.id;
      },
      saveDisabled() {
        return this.saving || this.isSameLesson || !this.student || !this.lesson || !this.warningsLoaded;
      },
      submitLabel() {
        return this.registeredLesson && !this.isSameLesson
            ? this.$t('registrations.register.change')
            : this.$t('registrations.register.submit');
      }
    },
    methods: {
      open(student, date, number) {
        this.student = student;
        this.date = date || null;
        this.number = number || null;
        this.lesson = null;
        this.show = true;
        this.shown = true;
      },
      selectLesson(lesson) {
        this.lesson = lesson;
      },
      cancel() {
        this.show = false;
        if (this.reload) {
          location.reload();
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;

          this.$http.post('/teacher/api/register/' + this.lesson.id + '/' + this.student.id, {}).then(function (response) {
            if (response.data.success) {
              self.saveError = null;
              self.reload = true;
              self.cancel();
            } else {
              self.saveError = response.data.error;
            }
            self.saving = false;
          }).catch(function (error) {
            self.saving = false;
            self.saveError = error;
          });
        }
      }
    }
  }
</script>
