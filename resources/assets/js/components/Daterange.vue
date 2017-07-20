<!--suppress XmlInvalidId, JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <div class="col-sm-6 col-xs-12">
    <div class="date-range clearfix">
      <div class="form-group" :class="{required: required}">
        <label for="firstDate" :class="{'sr-only': hideLabels}">{{labelFirst}}</label>
        <datepicker v-model="firstDate" name="firstDate" :placeholder="defaultStartDate || labelFirst"
                    :required="required"
                    :show-today="showToday"
                    :disabled="firstDateDisabled"
                    :disabled-days-of-week="firstDateDisabled ? null : disabledDaysOfWeek"
                    :disabled-dates="disabledDatesMoment"
                    :min-date="firstDateDisabled ? null : minDateMoment"
                    :max-date="firstDateDisabled ? null : maxFirstDate">
        </datepicker>
      </div>

      <div class="date-range-connector">
        <div :class="{'labels-hidden': hideLabels}">&ndash;</div>
      </div>

      <div class="form-group">
        <label for="lastDate" :class="{'sr-only': hideLabels}">{{labelLast}}</label>
        <datepicker v-model="lastDate" name="lastDate" :placeholder="defaultEndDate || labelLast"
                    :show-today="showToday"
                    :disabled="lastDateDisabled"
                    :disabled-days-of-week="lastDateDisabled ? null: lastDateDisabledDaysOfWeek"
                    :disabled-dates="lastDateDisabled ? null: disabledDatesMoment"
                    :min-date="lastDateDisabled ? null: minLastDate"
                    :max-date="lastDateDisabled ? null: maxDateMoment">
        </datepicker>
      </div>
    </div>
  </div>
</template>

<script>
  import moment from 'moment';

  const types = [{
    'required': true,
    'course': true
  }, {
    'showToday': true
  }, {
    'firstDisabled': true,
    'course': true,
    'edit': true
  }, {
    'firstDisabled': true,
    'lastDisabled': true
  }];

  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        firstDate: this.oldFirstDate ? moment(this.oldFirstDate, 'YYYY-MM-DD', true) : null,
        lastDate: this.oldLastDate ? moment(this.oldLastDate, 'YYYY-MM-DD', true) : null,
        minDateMoment: this.minDate ? moment(this.minDate, 'YYYY-MM-DD', true) : null,
        maxDateMoment: this.maxDate ? moment(this.maxDate, 'YYYY-MM-DD', true).endOf('day') : null,
        disabledDatesMoment: this.disabledDates.map(function (date) {
          return moment(date, 'YYYY-MM-DD', true);
        }),
        required: types[this.type].required,
        showToday: types[this.type].showToday,
        firstDateDisabled: types[this.type].firstDisabled
      }
    },
    props: {
      type: {
        'type': Number,
        'default': 0
      },
      defaultStartDate: {
        'type': String,
        'default': null
      },
      defaultEndDate: {
        'type': String,
        'default': null
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
        return (types[this.type].course || !this.lastDate) ? this.maxDateMoment : this.lastDate.clone().endOf('day');
      },
      minLastDate() {
        if (this.firstDate) {
          if (types[this.type].edit) {
            //noinspection JSCheckFunctionSignatures
            return moment.max(this.firstDate, this.minDateMoment.clone().subtract(1, 'w'));
          }
          return types[this.type].course ? this.firstDate.clone().add(1, 'w') : this.firstDate;
        }
        return this.minDateMoment;
      },
      lastDateDisabledDaysOfWeek() {
        if (types[this.type].course && this.firstDate) {
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
        if (types[this.type].lastDisabled) {
          return true;
        }
        return types[this.type].course ? !this.firstDate : false;
      }
    },
    created() {
      this.$emit('first', this.firstDate);
      this.$emit('last', this.lastDate);
    }
  }
</script>
