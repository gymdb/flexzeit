<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <div>
    <slot></slot>
    <modal :value="show" effect="fade" :title="$t('bugreports.create.heading')" @cancel="cancel">
      <div class="modal-footer" slot="modal-footer">
        <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
        <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{$t('bugreports.create.submit')}}</button>
      </div>

      <form>
        <error :error="error">{{$t('bugreports.create.error')}}</error>
        <div class="form-group">
          <label for="report" class="sr-only">{{$t('bugreports.create.label')}}</label>
          <textarea id="report" class="form-control" v-model.trim="text"></textarea>
        </div>
      </form>
    </modal>
  </div>
</template>

<script>
  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        text: null,
        error: null
      }
    },
    computed: {
      saveDisabled() {
        return !this.text
      }
    },
    methods: {
      open() {
        this.error = null;
        this.text = null;
        this.show = true;
      },
      cancel() {
        this.show = false;
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.$http.post('/api/bugReport', {text: this.text}).then(function (response) {
            if (response.data.success) {
              self.error = null;
              self.text = null;
              self.cancel();
            } else {
              self.error = response.data.error;
            }
          }).catch(function (error) {
            self.error = error;
          });
        }
      }
    }
  }
</script>
