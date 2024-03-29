<!--suppress JSUnresolvedVariable -->
<script>
  import moment from 'moment';
  import _ from 'lodash';

  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        firstDate: null,
        lastDate: null,
        frequency: null,
        category: null,
        number: this.oldNumber,
        name: this.oldName,
        room: this.oldRoom,
        roomOriginal: null,
        maxStudents: this.oldMaxStudents,
        maxStudentsOriginal: null,
        yearFrom: this.oldYearFrom,
        yearTo: this.oldYearFrom,
        subject: this.oldSubject,
        teacher: this.oldTeacher,
        withCourse: [],
        forNewCourse: [],
        cancelled: [],
        roomOccupation: [],
        withObligatory: [],
        timetable: [],
        offdays: [],
        groups: [],
        loading: false,
        error: null
      };
    },
    props: {
      obligatory: {
        'type': Boolean,
        'default': false
      },
      lessons: {
        'type': Object
      },
      rooms: {
        'type': Array,
        'required': true
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
        'type': Number,
        'default': null
      },
      oldMaxStudents: {
        'type': Number,
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
      },
      oldTeacher: {
        'type': Number,
        'default': null
      }
    },
    watch: {
      loadLessonsOptions(options) {
        this.loadLessonsForCourse(options);
      },
      room(room) {
        if (room) {
          const capacity = this.getRoomCapacity(room);
          if (!this.maxStudents || this.maxStudents === this.maxStudentsOriginal) {
            this.maxStudents = capacity;
          }
          this.maxStudentsOriginal = capacity;
        }
      }
    },
    computed: {
      frequencyMax() {
        return this.lastDate.diff(this.firstDate, 'weeks');
      },
      frequencyDisabled() {
        return !this.lastDate || this.frequencyMax < 1;
      },
      frequencyOptions() {
        if (this.frequencyDisabled) {
          this.frequency = null;
          return [];
        }

        if (!this.frequency) {
          this.frequency = 1;
        } else if (this.frequency > this.frequencyMax) {
          this.frequency = this.frequencyMax;
        }

        const options = [];
        for (let i = 1; i <= this.frequencyMax; i++) {
          options.push({value: i, text: this.$tc('courses.frequency', i, {count: i})});
        }
        return options;
      },
      categoryOptionsList() {
        const options = [];
        for (let i = 0; i <= 5; i++) {
            options.push({value: i, text: this.$t('courses.category')[i]});
        }
        return options;
      },
      lessonsOnDay() {
        return (this.firstDate && this.lessons[this.firstDate.day()]) ? this.lessons[this.firstDate.day()] : null;
      },
      parsedRooms() {
        return _.map(this.rooms, (room) => {
          return {
            value: room.id,
            text: room.name,
            disabled: (this.roomOccupation[room.id] && this.roomOccupation[room.id].length)
          };
        });
      },
      occupied() {
        return (this.room && this.roomOccupation[this.room]) ? this.roomOccupation[this.room] : [];
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
            frequency: this.frequency,
            category: this.category,
            number: this.number,
            groups: this.groups.length ? this.groups : null,
            teacher: this.teacher || null
          };
        }
      },
      buttonDisabled() {
        if (this.obligatory) {
          return this.error || this.loading || !this.firstDate || !this.number || !this.name || !this.room || !this.subject || !this.groups.length
              || this.withCourse.length > 0 || this.withObligatory.length > 0 || this.timetable.length > 0 || this.offdays.length > 0 || (this.category == null)
              || !this.forNewCourse.length;
        }

        return this.error || this.loading || !this.firstDate || !this.number || !this.name || !this.room || (this.category == null)
            || this.withCourse.length > 0 || !this.forNewCourse.length;
      }
    },
    methods: {
      setFirstDate(date) {
        this.firstDate = date;
      },
      setLastDate(date) {
        this.lastDate = date;
      },
      getRoomCapacity(room) {
        const data = _.find(this.rooms, {id: room});
        return (data && data.capacity) ? data.capacity : null;
      },
      loadLessonsForCourse: _.debounce(function (params) {
        if (!params) {
          this.error = null;
          this.loading = false;
          this.withCourse = [];
          this.forNewCourse = [];
          this.cancelled = [];
          this.roomOccupation = [];
          this.withObligatory = [];
          this.timetable = [];
          this.offdays = [];
        } else {
          let self = this;
          this.loading = true;
          this.$http.get('teacher/api/course/dataForCreate', {
            params: params
          }).then(function (response) {
            self.error = null;
            self.loading = false;
            self.withCourse = response.data.withCourse || [];
            self.forNewCourse = response.data.forNewCourse || [];
            self.cancelled = response.data.cancelled || [];
            self.roomOccupation = response.data.roomOccupation || [];
            if (self.obligatory) {
              self.withObligatory = response.data.withObligatory || [];
              self.timetable = response.data.timetable || [];
              self.offdays = response.data.offdays || [];
            }

            if (!self.room || self.room === self.roomOriginal) {
              self.room = response.data.room || null;
            }
            self.roomOriginal = response.data.room || null;
          }).catch(function (error) {
            self.error = error;
            self.loading = false;
          });
        }
      }, 50)
    }
  }
</script>
