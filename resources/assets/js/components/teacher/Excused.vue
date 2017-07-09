<!--suppress JSValidateTypes, JSUnresolvedVariable -->
<template>
  <a href="#" @click.prevent="refresh()" class="btn btn-default btn-xs" :title="$t('registrations.untis.reload')">
    <span class="glyphicon glyphicon-refresh" :class="{spin: loading}"></span> {{label(excused ? 'excused' : 'present')}}
  </a>
</template>

<script>
  export default {
    data() {
      return {
        loading: false
      };
    },
    props: {
      date: {
        'type': String,
        'required': true
      },
      excused: {
        'type': Boolean,
        'default': false
      }
    },
    methods: {
      refresh() {
        if (!this.loading) {
          let self = this;
          this.loading = true;
          this.$http.post('/teacher/api/absences/refresh/' + this.date, {}).then(function (response) {
            if (response.data.success) {
              self.$emit('refreshed');
            } else {
              self.$emit('error', response.data.error);
            }
            self.loading = false;
          }).catch(function (error) {
            self.$emit('error', error);
            self.loading = false;
          });
        }
      },
      label(key) {
        return this.$t('registrations.attendance.' + key);
      }
    }
  }
</script>
