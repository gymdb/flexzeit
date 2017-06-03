<script>
  import moment from 'moment';
  import _ from 'lodash';

  export default {
    data() {
      return {
        firstDate: null,
        lastDate: null,
        number: null,
        name: this.oldName,
        room: this.oldRoom,
        roomOriginal: this.oldRoom,
        yearFrom: this.oldYearFrom,
        yearTo: this.oldYearFrom,
        lessonsWithCourse: [],
        lessonsForNewCourse: [],
        error: null
      }
    },
    props: {
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
        return this.yearTo ? Math.min(this.maxYear, this.yearTo) : this.maxYear;
      },
      minYearTo() {
        return this.yearFrom ? Math.max(this.minYear, this.yearFrom) : this.minYear;
      },
      loadLessonsOptions() {
        if (!this.firstDate || !this.number) {
          return null;
        } else {
          return {
            firstDate: this.firstDate.format('YYYY-MM-DD'),
            lastDate: this.lastDate ? this.lastDate.format('YYYY-MM-DD') : null,
            number: this.number
          };
        }
      },
      buttonDisabled() {
        return !this.firstDate || !this.number || !this.name || !this.room
            || this.lessonsWithCourse.length !== 0 || this.lessonsForNewCourse.length === 0;
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
          this.lessonsWithCourse = [];
          this.lessonsForNewCourse = [];
        } else {
          let self = this;
          this.$http.get('/teacher/api/course/lessonsForCreate', {
            params: params
          }).then(function (response) {
            self.error = null;
            self.lessonsWithCourse = response.data.withCourse;
            self.lessonsForNewCourse = response.data.forNewCourse;

            let room = (self.lessonsWithCourse.length === 0 && self.lessonsForNewCourse.length > 0) ? self.lessonsForNewCourse[0].room : null;
            if (!self.room || self.room === self.roomOriginal) {
              self.room = room;
            }
            self.roomOriginal = room;
          }).catch(function (error) {
            console.log(error);
            self.error = error.response ? error.response.status : 100;
          });
        }
      }, 50)
    }
  }
</script>
