<template>
  <div v-if="code" class="alert alert-danger">
    <p><strong>
      <slot></slot>
    </strong></p>
    <p v-if="text">{{text}}</p>
    <p v-if="message">{{$t('errors.message')}}: <em>{{message}}</em></p>
  </div>
</template>

<script>
  const httpError = [401, 403, 404];

  //noinspection JSUnusedGlobalSymbols
  export default {
    props: {
      error: {
        'default': null
      }
    },
    computed: {
      code() {
        if (typeof this.error === 'object') {
          return this.error ? ((this.error.response ? this.error.response.status : this.error.code) || 99) : null;
        }
        return this.error || null;
      },
      text() {
        if (!this.code) {
          return null;
        }
        if (this.code < 100 || httpError.indexOf(this.code) >= 0) {
          return this.$t('errors.' + this.code);
        }
        if (this.code < 500) {
          return this.$t('errors.http', {code: this.code});
        }
        if (this.code < 600) {
          return this.$t('errors.5xx', {code: this.code})
        }
        return this.$t('errors.unknown', {code: this.code});
      },
      message() {
        return (typeof this.error === 'object' && this.error && this.error.response && this.error.response.data)
            ? this.error.response.data.message : this.error.message;
      }
    }
  }
</script>
