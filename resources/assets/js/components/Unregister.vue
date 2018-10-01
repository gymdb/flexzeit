<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<template>
  <a href="#" @click.prevent="unregister()" :class="button ? 'btn btn-xs btn-default' : ''"
     :title="$t('registrations.unregister.submit')" :disabled="saving">
    <slot>{{$t('registrations.unregister.submit')}}</slot>
  </a>
</template>

<script>
  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        saving: false
      };
    },
    props: {
      baseUrl: {
        'type': String,
        'required': true
      },
      id: {
        'type': Number,
        'required': true
      },
      confirmText: {
        'type': String,
        'required': true
      },
      course: {
        'type': Boolean,
        'default': false
      },
      courseId: {
        'type': Number
      },
      button: {
        'type': Boolean,
        'default': true
      }
    },
    methods: {
      unregister() {
        // noinspection JSCheckFunctionSignatures
        if (window.confirm(this.confirmText)) {
          let url = this.baseUrl + '/api/unregister/' + (this.course ? 'course' : 'lesson') + '/';
          this.save(url);
        }
      },
      save(url) {
        let self = this;
        this.saving = true;
        this.$http.post(url + (this.courseId ? this.courseId + '/' : '') + this.id).then(function (response) {
          if (response.data.success) {
            self.$emit('success');
          } else {
            self.$emit('error', response.data.error);
          }
          self.saving = false;
        }).catch(function (error) {
          self.saving = false;
          self.$emit('error', error);
        });
      },
    }
  }
</script>
