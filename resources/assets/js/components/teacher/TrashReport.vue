<!--suppress JSValidateTypes, JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <a href="#" @click.prevent="save()" class="btn hidden-print" :class="{disabled: loading}"
     :title="$t(trashed ? 'bugreports.restore' : 'bugreports.trash')">
    <span class="glyphicon" :class="trashed ? 'glyphicon-repeat icon-flip' : 'glyphicon-trash'"></span>
  </a>
</template>

<script>
  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        loading: false
      };
    },
    props: {
      id: {
        'type': Number,
        'required': true
      },
      trashed: {
        'type': Boolean,
        'required': true
      }
    },
    methods: {
      save() {
        if (!this.loading) {
          let self = this;
          this.loading = true;
          this.$http.post('teacher/api/bugreports/' + (this.trashed ? 'restore' : 'trash') + '/' + this.id, {}).then(function (response) {
            if (response.data.success) {
              self.$emit('success');
            } else {
              self.$emit('error', response.data.error);
            }
            self.loading = false;
          }).catch(function (error) {
            self.$emit('error', error);
            self.loading = false;
          });
        }
      }
    }
  }
</script>
