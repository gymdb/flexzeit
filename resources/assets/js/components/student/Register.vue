<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <modal :value="show" effect="fade" :title="heading" @cancel="cancel" large>
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{$t('student.register.submit')}}</button>
    </div>

    <error :error="error">{{$t('student.register.saveError')}}</error>

    <template v-if="lesson">
      <template v-if="lesson.course">
        <p>{{$t('student.register.course.info', {teacher: lesson.teacher.name, course: lesson.course.name})}}</p>
        <ul>
          <li v-for="date in lesson.course.lessons">{{$d(moment(date), 'short')}}</li>
        </ul>
        <p>{{lesson.course.description}}</p>
      </template>
      <template v-else>
        <p>{{$t('student.register.lesson.info', {
          teacher: lesson.teacher.name,
          date: $d(moment(lesson.date), 'short'),
          time: $t('messages.range', lesson.time)
        })}}</p>
      </template>
    </template>
  </modal>
</template>

<script>
  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        lesson: null,
        error: null
      }
    },
    computed: {
      heading() {
        if (!this.lesson) {
          return null;
        }
        return this.lesson.course
            ? this.$t('student.register.course.heading', {course: this.lesson.course.name})
            : this.$t('student.register.lesson.heading', {teacher: this.lesson.teacher.name});
      },
      saveDisabled() {
        return !this.lesson;
      }
    },
    methods: {
      open(lesson) {
        this.lesson = lesson;
        this.show = !!lesson;
      },
      cancel() {
        this.show = false;
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;

          const url = this.lesson.course
              ? '/student/api/register/course/' + this.lesson.course.id
              : '/student/api/register/lesson/' + this.lesson.id;

          this.$http.post(url, {}).then(function (response) {
            if (response.data.success) {
              self.error = null;
              location.reload();
            } else {
              self.error = response.data.error;
            }
          }).catch(function (error) {
            self.error = error.response ? error.response.status : 100;
          });
        }
      }
    }
  }
</script>
