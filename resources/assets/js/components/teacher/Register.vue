<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <modal v-if="shown" :value="show" effect="fade" :title="$t('registrations.register.heading')" @cancel="cancel" large>
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
      <template slot="empty"></template>
      <template scope="props">
        <div v-if="isSameLesson" class="alert alert-danger">
          <strong>{{$t('registrations.warnings.sameLesson')}}</strong>
        </div>
        <div v-else class="alert alert-warning">
          <strong>{{$t('registrations.warnings.heading')}}</strong>
          <ul>
            <li v-for="(data, key) in props.data">
              {{$t('registrations.warnings.' + key, data)}}
            </li>
          </ul>
        </div>
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
        url: '/teacher/api/registrations/warnings/' + this.lesson,
        student: null,
        registeredLesson: null,
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
      date: {
        'type': String,
        'required': true
      },
      number: {
        'type': Number,
        'required': true
      },
      admin: {
        'type': Boolean,
        'default': false
      },
      lesson: {
        'type': Number,
        'required': true
      }
    },
    computed: {
      isSameLesson() {
        return this.registeredLesson && this.registeredLesson === this.lesson;
      },
      saveDisabled() {
        return this.saving || !this.student || (this.registeredLesson && (!this.admin || this.isSameLesson)) || (!this.admin && this.timetable);
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

          this.timetable = data.timetable || null;
        } else {
          this.student = null;
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;
          this.$http.post('/teacher/api/register/' + this.lesson + '/' + this.student, {}).then(function (response) {
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
