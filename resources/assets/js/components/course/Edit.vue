<!--suppress JSUnresolvedVariable -->
<script>
  import moment from 'moment';
  import _ from 'lodash';

  // noinspection JSUnusedGlobalSymbols
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
        yearTo: this.old.yearTo,
        maxStudents: this.old.maxStudents,
        maxStudentsOriginal: this.getRoomCapacity(this.old.room),
        subject: this.old.subject,
        groups: this.old.groups || [],
        withCourse: [],
        added: [],
        removed: [],
        roomOccupation: [],
        withObligatory: [],
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
      parsedRooms() {
        return _.map(this.rooms, (room) => {
          return {
            id: room.id,
            label: (this.roomOccupation[room.id] && this.roomOccupation[room.id].length) ? '<span class="text-muted">' + room.name + '</span>' : room.name
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
        return {
          course: this.data.id,
          lastDate: this.lastDate ? this.lastDate.format('YYYY-MM-DD') : null,
          number: this.number,
          groups: this.groups.length ? this.groups : null
        };
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
      getRoomCapacity(room) {
        const data = _.find(this.rooms, {id: room});
        return (data && data.capacity) ? data.capacity : null;
      },
      loadLessonsForCourse: _.debounce(function (params) {
        let self = this;
        this.$http.get('/teacher/api/course/dataForEdit', {
          params: params
        }).then(function (response) {
          self.error = null;
          self.withCourse = response.data.withCourse || [];
          self.added = response.data.added || [];
          self.removed = response.data.removed || [];
          self.roomOccupation = response.data.roomOccupation || [];
          if (self.obligatory) {
            self.withObligatory = response.data.withObligatory || [];
          }
        }).catch(function (error) {
          self.error = error;
        });
      }, 50)
    }
  }
</script>
