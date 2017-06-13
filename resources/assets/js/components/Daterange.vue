<template>
  <div class="col-sm-6 col-xs-12">
    <div class="date-range clearfix">
      <div class="form-group" :class="{required: type === 0}">
        <label for="firstDate" :class="{'sr-only': hideLabels}">{{labelFirst}}</label>
        <datepicker v-model="firstDate" name="firstDate" :placeholder="labelFirst"
                    :required="type === 0"
                    :show-today="type === 1"
                    :disabled-days-of-week="disabledDaysOfWeek"
                    :disabled-dates="disabledDatesMoment"
                    :min-date="minDateMoment"
                    :max-date="maxFirstDate">
        </datepicker>
      </div>

      <div class="date-range-connector">
        <div :class="{'labels-hidden': hideLabels}">&ndash;</div>
      </div>

      <div class="form-group">
        <label for="lastDate" :class="{'sr-only': hideLabels}">{{labelLast}}</label>
        <datepicker v-model="lastDate" name="lastDate" :placeholder="labelLast"
                    :show-today="type === 1"
                    :disabled="lastDateDisabled"
                    :disabled-days-of-week="lastDateDisabledDaysOfWeek"
                    :disabled-dates="disabledDatesMoment"
                    :min-date="minLastDate"
                    :max-date="maxDateMoment">
        </datepicker>
      </div>
    </div>
  </div>
</template>


<script>
  import moment from 'moment';

  export default {
    data() {
      return {
        firstDate: this.oldFirstDate ? moment(this.oldFirstDate, 'YYYY-MM-DD', true) : null,
        lastDate: this.oldLastDate ? moment(this.oldLastDate, 'YYYY-MM-DD', true) : null,
        minDateMoment: this.minDate ? moment(this.minDate, 'YYYY-MM-DD', true) : null,
        maxDateMoment: this.maxDate ? moment(this.maxDate, 'YYYY-MM-DD', true).endOf('day') : null,
        disabledDatesMoment: this.disabledDates.map(function (date) {
          return moment(date, 'YYYY-MM-DD', true);
        })
      }
    },
    props: {
      type: {
        'type': Number,
        'default': 0
      },
      minDate: {
        'type': String,
        'required': true
      },
      maxDate: {
        'type': String,
        'required': true
      },
      disabledDaysOfWeek: {
        'type': Array,
        'default': function () {
          return [];
        }
      },
      disabledDates: {
        'type': Array,
        'default': function () {
          return [];
        }
      },
      oldFirstDate: {
        'type': String,
        'default': null
      },
      oldLastDate: {
        'type': String,
        'default': null
      },
      labelFirst: {
        'type': String,
        'required': true
      },
      labelLast: {
        'type': String,
        'required': true
      },
      hideLabels: {
        'type': Boolean,
        'default': false
      }
    },
    watch: {
      firstDate(date) {
        this.$emit('first', date);
      },
      lastDate(date) {
        this.$emit('last', date);
      }
    },
    computed: {
      maxFirstDate() {
        return this.type === 0 || !this.lastDate ? this.maxDateMoment : this.lastDate.clone().endOf('day');
      },
      minLastDate() {
        if (this.firstDate) {
          return this.type === 0 ? this.firstDate.clone().add(1, 'w') : this.firstDate;
        }
        return this.minDateMoment;
      },
      lastDateDisabledDaysOfWeek() {
        if (this.type === 0 && this.firstDate) {
          let disabled = [];
          for (let i = 0; i < 7; i++) {
            if (i !== this.firstDate.day()) {
              disabled.push(i);
            }
          }
          return disabled;
        }

        return this.disabledDaysOfWeek;
      },
      lastDateDisabled() {
        return this.type === 0 ? !this.firstDate : false;
      }
    },
    methods: {}
  }
</script>
