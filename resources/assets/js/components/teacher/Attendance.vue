<template>
  <dropdown v-if="changeable" size="xs">
    <template slot="button">
      <span class="glyphicon" :class="iconClass"></span> {{text}}
    </template>
    <ul slot="dropdown-menu" class="dropdown-menu">
      <li :class="{active: computedAttendance}"><a href="#" @click.prevent="setPresent()">{{presentText}}</a></li>
      <li :class="{active: !computedAttendance}"><a href="#" @click.prevent="setAbsent()">{{excused ? excusedText : absentText}}</a></li>
    </ul>
  </dropdown>
  <span v-else>
    <span class="glyphicon" :class="iconClass"></span> {{text}}
  </span>
</template>

<script>
  export default {
    data() {
      return {
        currAttendance: this.attendance,
      }
    },
    props: {
      id: {
        'type': Number,
        'required': true
      },
      attendance: {
        'type': Boolean,
        'default': null
      },
      excused: {
        'type': Boolean,
        'default': false
      },
      changeable: {
        'type': Boolean,
        'default': false
      },
      presentText: {
        'type': String,
        'required': true
      },
      absentText: {
        'type': String,
        'required': true
      },
      excusedText: {
        'type': String,
        'required': true
      }
    },
    computed: {
      computedAttendance() {
        return this.currAttendance === true || (this.currAttendance === null && !this.excused);
      },
      iconClass() {
        if (this.computedAttendance) {
          return 'glyphicon-ok text-success';
        }
        if (this.excused) {
          return 'glyphicon-remove text-warning';
        }
        if (this.currAttendance === false) {
          return 'glyphicon-remove text-danger';
        }
      },
      text() {
        if (this.computedAttendance) {
          return this.presentText;
        }
        if (this.excused) {
          return this.excusedText;
        }
        if (this.currAttendance === false) {
          return this.absentText;
        }
      }
    },
    methods: {
      setPresent() {
        this.save(true);
      },
      setAbsent() {
        this.save(false);
      },
      save(newAttendance) {
        if (newAttendance !== null) {
          let self = this;
          this.$http.post('/teacher/api/attendance/' + this.id, {attendance: newAttendance}).then(function (response) {
            if (response.data.success) {
              self.currAttendance = newAttendance;
              self.$emit('success');
            } else {
              self.$emit('error', response.data.error);
            }
          }).catch(function (error) {
            if (error.response) {
              self.$emit('error', error.response.status);
            } else {
              self.$emit('error', 100);
            }
          });
        }
      }
    }
  }
</script>
