<script>
  import moment from 'moment';
  import _ from 'lodash';

  export default {
    data() {
      return {
        firstDate: null,
        lastDate: null,
        number: this.oldNumber,
        name: this.old.name,
        room: this.old.room,
        description: this.old.description,
        yearFrom: this.old.yearFrom,
        yearTo: this.old.yearFrom,
        maxStudents: this.old.maxStudents,
        subject: this.old.subject,
        groups: this.old.groups || [],
        withCourse: [],
        added: [],
        removed: [],
        withObligatory: [],
        error: null
      }
    },
    props: {
      obligatory: {
        'type': Boolean,
        'default': false
      },
      lessons: {
        'type': Object
      },
      minYear: {
        'type': Number,
        'default': 1,
      },
      maxYear: {
        'type': Number,
        'default': Number.MAX_VALUE
      },
      data: {
        'type': Object,
        'required': true
      },
      old: {
        'type': Object,
        'required': true
      }
    },
    watch: {
      loadLessonsOptions(options) {
        this.loadLessonsForCourse(options);
      }
    },
    computed: {
      maxYearFrom() {
        //noinspection JSCheckFunctionSignatures
        return this.yearTo ? Math.min(this.maxYear, this.yearTo) : this.maxYear;
      },
      minYearTo() {
        //noinspection JSCheckFunctionSignatures
        return this.yearFrom ? Math.max(this.minYear, this.yearFrom) : this.minYear;
      },
      loadLessonsOptions() {
        if (!this.changed) {
          return null;
        } else {
          return {
            course: this.data.id,
            lastDate: this.lastDate ? this.lastDate.format('YYYY-MM-DD') : null,
            number: this.number,
            groups: this.groups.length ? this.groups : null
          };
        }
      },
      buttonDisabled() {
        if (!this.changed) {
          return true;
        }
        return this.obligatory
            ? !this.name || !this.room || !this.subject || !this.groups.length || this.withCourse.length > 0 || this.withObligatory.length > 0
            : !this.name || !this.room || this.withCourse.length > 0;
      },
      changed() {
        if (!this.lastDate || this.lastDate.format('YYYY-MM-DD') !== this.data.lastDate ||
            this.name !== this.data.name || this.room !== this.data.room || this.description !== this.data.description) {
          return true;
        }
        return this.obligatory
            ? (this.subject !== this.data.subject || !_.isEqual(this.groups.sort(), this.data.groups.sort()))
            : (this.yearFrom !== this.data.yearFrom || this.yearTo !== this.data.yearTo || this.maxStudents !== this.data.maxStudents);
      }
    },
    methods: {
      setLastDate(date) {
        this.lastDate = date;
      },
      loadLessonsForCourse: _.debounce(function (params) {
        if (!params) {
          this.error = null;
          this.withCourse = [];
          this.added = [];
          this.removed = [];
          this.withObligatory = [];
        } else {
          let self = this;
          this.$http.get('/teacher/api/course/lessonsForEdit', {
            params: params
          }).then(function (response) {
            self.error = null;
            self.withCourse = response.data.withCourse || [];
            self.added = response.data.added || [];
            self.removed = response.data.removed || [];
            if (self.obligatory) {
              self.withObligatory = response.data.withObligatory || [];
            }
          }).catch(function (error) {
            self.error = error;
          });
        }
      }, 50)
    }
  }
</script>
