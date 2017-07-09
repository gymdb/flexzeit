<script>
  import moment from 'moment';
  import _ from 'lodash';

  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        firstDate: null,
        lastDate: null,
        number: this.oldNumber,
        name: this.oldName,
        room: this.oldRoom,
        roomOriginal: null,
        yearFrom: this.oldYearFrom,
        yearTo: this.oldYearFrom,
        subject: this.oldSubject,
        groups: this.oldGroups,
        withCourse: [],
        forNewCourse: [],
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
      oldNumber: {
        'type': Number,
        'default': null
      },
      oldName: {
        'type': String,
        'default': null
      },
      oldRoom: {
        'type': String,
        'default': null
      },
      oldYearFrom: {
        'type': Number,
        'default': null
      },
      oldYearTo: {
        'type': Number,
        'default': null
      },
      oldSubject: {
        'type': Number,
        'default': null
      },
      oldGroups: {
        'type': Array,
        'default': function () {
          return [];
        }
      }
    },
    watch: {
      loadLessonsOptions(options) {
        this.loadLessonsForCourse(options);
      }
    },
    computed: {
      lessonsOnDay() {
        return (this.firstDate && this.lessons[this.firstDate.day()]) ? this.lessons[this.firstDate.day()] : null;
      },
      maxYearFrom() {
        //noinspection JSCheckFunctionSignatures
        return this.yearTo ? Math.min(this.maxYear, this.yearTo) : this.maxYear;
      },
      minYearTo() {
        //noinspection JSCheckFunctionSignatures
        return this.yearFrom ? Math.max(this.minYear, this.yearFrom) : this.minYear;
      },
      loadLessonsOptions() {
        if (!this.firstDate || !this.number) {
          return null;
        } else {
          return {
            firstDate: this.firstDate.format('YYYY-MM-DD'),
            lastDate: this.lastDate ? this.lastDate.format('YYYY-MM-DD') : null,
            number: this.number,
            groups: this.groups.length ? this.groups : null
          };
        }
      },
      buttonDisabled() {
        if (this.obligatory) {
          return !this.firstDate || !this.number || !this.name || !this.room || !this.subject || !this.groups.length
              || this.withCourse.length || this.withObligatory.length || !this.forNewCourse.length;
        }

        return !this.firstDate || !this.number || !this.name || !this.room
            || this.withCourse.length || !this.forNewCourse.length;
      }
    },
    methods: {
      setFirstDate(date) {
        this.firstDate = date;
      },
      setLastDate(date) {
        this.lastDate = date;
      },
      loadLessonsForCourse: _.debounce(function (params) {
        if (!params) {
          this.error = null;
          this.withCourse = [];
          this.forNewCourse = [];
          this.withObligatory = [];
        } else {
          let self = this;
          this.$http.get('/teacher/api/course/lessonsForCreate', {
            params: params
          }).then(function (response) {
            self.error = null;
            self.withCourse = response.data.withCourse;
            self.forNewCourse = response.data.forNewCourse;
            if (self.obligatory) {
              self.withObligatory = response.data.withObligatory || [];
            }

            let room = (self.withCourse.length === 0 && self.forNewCourse.length > 0) ? self.forNewCourse[0].room : null;
            if (!self.room || self.room === self.roomOriginal) {
              self.room = room;
            }
            self.roomOriginal = room;
          }).catch(function (error) {
            self.error = error;
          });
        }
      }, 50)
    }
  }
</script>
