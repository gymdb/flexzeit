<!--suppress JSUnresolvedVariable -->
<script>
  import _ from 'lodash';

  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        courseId: this.data.id,
        lastDate: this.old.lastDate || null,
        name: this.old.name,
        room: this.old.room,
        description: this.old.description,
        category: this.old.category,
        yearFrom: this.old.yearFrom,
        yearTo: this.old.yearTo,
        maxStudents: this.old.maxStudents,
        maxStudentsOriginal: this.getRoomCapacity(this.old.room),
        subject: this.old.subject,
        groups: this.old.groups || [],
        withCourse: [],
        added: [],
        removed: [],
        cancelled: [],
        roomOccupation: [],
        withObligatory: [],
        timetable: [],
        offdays: [],
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
      loadDataOptions(options) {
        this.loadData(options);
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
      frequencyLabel() {
        const val = this.lastDate === this.data.firstDate ? 0 : this.data.frequency;
        return this.$tc('courses.frequency', val, {count: val});
      },
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
      loadDataOptions() {
        let options = {
          course: this.courseId,
          lastDate: this.lastDate
        };
        if (this.obligatory && this.groups.length) {
          options.groups = this.groups;
        }
        return options;
      },
      categoryOptionsList() {
        const options = [];
        for (let i = 0; i <= 5; i++) {
          options.push({value: i, label: this.$t('courses.category')[i]});
        }
        return options;
      },
      buttonDisabled() {
        if (!this.changed || this.error || this.loading) {
          return true;
        }
        return this.obligatory
            ? !this.name || !this.room || !this.subject || !this.groups.length || this.withCourse.length > 0 || this.withObligatory.length > 0 || this.timetable.length > 0 || this.offdays.length > 0
            : !this.name || !this.room || this.withCourse.length > 0;
      },
      changed() {
        if (this.lastDate !== this.data.lastDate ||
            this.name !== this.data.name || this.room !== this.data.room || this.description !== this.data.description || this.category !== this.data.category) {
          return true;
        }
        return this.obligatory
            ? (this.subject !== this.data.subject || !_.isEqual(this.groups.sort(), this.data.groups.sort()))
            : (this.yearFrom !== this.data.yearFrom || this.yearTo !== this.data.yearTo || this.maxStudents !== this.data.maxStudents);
      }
    },
    created() {
      this.loadData(this.loadDataOptions);
    },
    methods: {
      setLastDate(date) {
        this.lastDate = date ? date.format('YYYY-MM-DD') : null;
      },
      getRoomCapacity(room) {
        const data = _.find(this.rooms, {id: room});
        return (data && data.capacity) ? data.capacity : null;
      },
      loadData: _.debounce(function (params) {
        let self = this;
        this.loading = true;
        this.$http.get('teacher/api/course/dataForEdit', {
          params: params
        }).then(function (response) {
          self.error = null;
          self.loading = false;
          self.withCourse = response.data.withCourse || [];
          self.added = response.data.added || [];
          self.removed = response.data.removed || [];
          self.cancelled = response.data.cancelled || [];
          self.roomOccupation = response.data.roomOccupation || [];
          if (self.obligatory) {
            self.withObligatory = response.data.withObligatory || [];
            self.timetable = response.data.timetable || [];
            self.offdays = response.data.offdays || [];
          }
        }).catch(function (error) {
          self.error = error;
          self.loading = false;
        });
      }, 50)
    }
  }
</script>
