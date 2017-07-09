<template>
  <div>
    <input type="hidden" :value="isoVal" :name="name" :required="required"/>
    <div class="input-group date" :class="{'disabled': disabled}">
      <!--suppress HtmlFormInputWithoutLabel -->
      <input type="text" class="form-control" :disabled="computedDisabled" :required="required" :id="name" readonly :placeholder="placeholder"/>
      <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
    </div>
  </div>
</template>

<script>
  /* global require */
  let $ = window.jQuery = require('jquery');
  import moment from 'moment';
  //noinspection SpellCheckingInspection
  import eonosdandatetimepicker from 'eonasdan-bootstrap-datetimepicker';

  moment.locale('de');

  //noinspection JSUnusedGlobalSymbols
  export default {
    name: 'vue-datetimepicker',
    data() {
      return {
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
      options: function (options) {
        //noinspection JSUnresolvedFunction
        $('.date', this.$el).datetimepicker('options', options);
      },
      value: function (value) {
        //noinspection JSUnresolvedFunction
        $('.date', this.$el).datetimepicker('date', value || null);
      },
      val: function (val) {
        this.$emit('input', val);
      }
    },
    computed: {
      isoVal() {
        return this.val ? this.val.format('YYYY-MM-DD') : null;
      },
      computedDisabled() {
        return this.disabled || !this.minDate || !this.maxDate || this.minDate.isAfter(this.maxDate);
      },
      options() {
        if (this.disabled) {
          this.val = null;

          return {
            'allowInputToggle': true,
            'ignoreReadonly': true,
            'format': 'L',
            'locale': 'de',
            'useCurrent': false
          }
        }

        if (this.val &&
            (this.disabledDaysOfWeek.indexOf(this.val.day()) >= 0
            || this.val.isBefore(this.minDate) || this.val.isAfter(this.maxDate))) {
          this.val = null;
        }

        return {
          'allowInputToggle': true,
          'ignoreReadonly': true,
          'format': 'L',
          'locale': 'de',
          'useCurrent': false,
          'showClear': !this.required,
          'showTodayButton': this.showToday && !this.minDate.isAfter() && !this.maxDate.isBefore(),
          'daysOfWeekDisabled': this.disabledDaysOfWeek,
          'disabledDates': this.disabledDates,
          'viewDate': this.showToday ? false : this.minDate.clone(),
          'minDate': this.minDate,
          'maxDate': this.maxDate
        }
      }
    },
    mounted: function () {
      let self = this;
      //noinspection JSUnresolvedFunction
      $('.date', this.$el)
          .datetimepicker($.extend({'defaultDate': this.value}, this.options))
          .on('dp.change', function (e) {
            if (e.date) {
              e.date.startOf('day');
            }
            self.val = e.date || null;
          });
    },
    destroyed: function () {
      //noinspection JSUnresolvedFunction
      $('.date', this.$el).off().datetimepicker('destroy');
    }
  }
</script>
