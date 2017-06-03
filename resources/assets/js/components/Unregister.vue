<template>
  <a href="#" @click.prevent="unregister()" class="btn btn-xs btn-default">
    <slot>{{buttonText}}</slot>
  </a>
</template>

<script>
  export default {
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
      buttonText: {
        'type': String,
        'required': true
      }
    },
    methods: {
      unregister() {
        if (window.confirm(this.confirmText)) {
          let url = '/' + this.baseUrl + '/api/unregister/' + (this.course ? 'course' : 'lesson') + '/';
          this.save(url);
        }
      },
      save(url) {
        let self = this;
        this.$http.post(url + this.id).then(function (response) {
          if (response.data.success) {
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
      },
    }
  }
</script>
