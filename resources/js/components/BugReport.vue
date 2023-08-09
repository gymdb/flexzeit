<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <div>
    <slot></slot>
    <div class="modal fade in" id="feedbackDlg"   @cancel="cancel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLabel">{{$t('bugreports.create.heading')}}</h4>
          </div>
          <div class="modal-body">
            <form>
              <error :error="error">{{$t('bugreports.create.error')}}</error>
              <div class="form-group">
                <label for="report" class="sr-only">{{$t('bugreports.create.label')}}</label>
                <textarea id="report" class="form-control" v-model.trim="text"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer" slot="modal-footer">
            <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
            <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{$t('bugreports.create.submit')}}</button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
// noinspection JSUnusedGlobalSymbols
export default {
  data() {
    return {
      show: false,
      text: null,
      saving: false,
      error: null
    }
  },
  computed: {
    saveDisabled() {
      return this.saving || !this.text;
    }
  },
  methods: {
    open() {
      this.error = null;
      this.text = null;
      this.show = true;
      $("#feedbackDlg").show();
    },
    cancel() {
      this.show = false;
      $("#feedbackDlg").hide();
    },
    save() {
      if (!this.saveDisabled) {
        let self = this;
        this.saving = true;
        this.$http.post('api/bugReport', {text: this.text}).then(function (response) {
          if (response.data.success) {
            self.error = null;
            self.text = null;
            self.cancel();
          } else {
            self.error = response.data.error;
          }
          self.saving = false;
        }).catch(function (error) {
          self.saving = false;
          self.error = error;
        });
      }
    }
  }
}
</script>
