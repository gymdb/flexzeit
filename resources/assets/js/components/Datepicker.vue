<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<template>
  <div>
    <input type="hidden" :value="isoVal" :name="name" :required="required"/>
    <div class="input-group date" :class="{'disabled': disabled}">
      <!--suppress HtmlFormInputWithoutLabel -->
      <input class="form-control" :disabled="computedDisabled" :required="required" :id="name" readonly :placeholder="placeholder"/>
      <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
    </div>
  </div>
</template>

<script>
  /* global require */
  let $ = window.jQuery = require('jquery');
  import moment from 'moment';
  //noinspection SpellCheckingInspection
  import datetimepicker from 'eonasdan-bootstrap-datetimepicker';

  const lang = document.documentElement.lang;
  moment.locale(lang);
  datetimepicker.defaults.locale = lang;
  datetimepicker.defaults.tooltips = require('../lang/' + lang + '/datetimepicker').tooltips;

  //noinspection JSUnusedGlobalSymbols
  export default {
    name: 'vue-datetimepicker',
    data() {
      return {
        picker: null,
        val: this.value
      }
    },
    props: {
      value: {
        'default': null
      },
      name: {
        type: String,
        required: true
      },
      disabled: {
        type: Boolean,
        'default': false
      },
      required: {
        type: Boolean,
        'default': false
      },
      showToday: {
        type: Boolean,
        'default': false
      },
      disabledDaysOfWeek: {
        type: Array,
        'default': function () {
          return [];
        }
      },
      disabledDates: {
        type: Array,
        'default': function () {
          return [];
        }
      },
      minDate: {
        required: true
      },
      maxDate: {
        required: true
      },
      placeholder: {
        type: String,
        'default': ''
      }
    },
    watch: {
      value: function (value) {
        if (this.picker) {
          this.picker.datetimepicker('date', value || null);
        }
      },
      val: function (val) {
        this.$emit('input', val);
      },
      required(required) {
        if (this.picker) {
          this.picker.datetimepicker('showClear', !required);
        }
      },
      computedShowToday(showToday) {
        if (this.picker) {
          this.picker.datetimepicker('showTodayButton', showToday);
        }
      },
      computedDisabledDaysOfWeek(disabledDaysOfWeek) {
        if (this.picker) {
          this.picker.datetimepicker('daysOfWeekDisabled', disabledDaysOfWeek);
        }
      },
      computedDisabledDates(disabledDates) {
        if (this.picker) {
          this.picker.datetimepicker('disabledDates', this.computedDisabled ? [] : disabledDates);
        }
      },
      computedViewDate(viewDate) {
        if (this.picker) {
          this.picker.datetimepicker('viewDate', viewDate);
        }
      },
      computedMinDate(minDate) {
        if (this.picker) {
          this.picker.datetimepicker('minDate', minDate);
        }
      },
      computedMaxDate(maxDate) {
        if (this.picker) {
          this.picker.datetimepicker('maxDate', maxDate);
        }
      }, removeVal(removeVal) {
        if (removeVal) {
          this.val = null;
        }
      },
    },
    computed: {
      isoVal() {
        return this.val ? this.val.format('YYYY-MM-DD') : null;
      },
      computedDisabled() {
        return this.disabled || !this.minDate || !this.maxDate || this.minDate.isAfter(this.maxDate);
      },
      computedShowToday() {
        return !this.computedDisabled && this.showToday && !this.minDate.isAfter() && !this.maxDate.isBefore();
      },
      computedDisabledDaysOfWeek() {
        return this.computedDisabled ? [] : this.disabledDaysOfWeek;
      },
      computedDisabledDates() {
        return this.computedDisabled ? [] : this.disabledDates;
      },
      computedViewDate() {
        if (this.computedDisabled) {
          return null;
        }
        return this.showToday ? false : this.minDate.clone();
      },
      computedMinDate() {
        return this.computedDisabled ? false : this.minDate;
      },
      computedMaxDate() {
        return this.computedDisabled ? false : this.maxDate;
      },
      removeVal() {
        return this.computedDisabled || (this.val && (this.disabledDaysOfWeek.indexOf(this.val.day()) >= 0
            || this.val.isBefore(this.minDate) || this.val.isAfter(this.maxDate)));
      }
    },
    mounted: function () {
      let self = this;
      this.picker = $('.date', this.$el);
      const options = {
        'allowInputToggle': true,
        'ignoreReadonly': true,
        'format': this.$t('messages.dateformat'),
        'useCurrent': false,
        'showClear': !this.required || true,
        'showTodayButton': this.computedShowToday,
        'daysOfWeekDisabled': this.computedDisabledDaysOfWeek,
        'disabledDates': this.computedDisabledDates,
        'viewDate': this.computedViewDate,
        'minDate': this.computedMinDate,
        'maxDate': this.computedMaxDate,
        'defaultDate': this.value
      };

      this.picker.datetimepicker(options)
          .on('dp.change', function (e) {
            if (e.date) {
              if (self.val && e.date.isSame(self.val)) {
                return;
              }
              e.date.startOf('day');
            }
            self.val = e.date || null;
          });
    },
    destroyed: function () {
      if (this.picker) {
        this.picker.off().datetimepicker('destroy');
      }
    }
  }
</script>
