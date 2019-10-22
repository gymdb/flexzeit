<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<template>
  <modal :value="show" effect="fade" :title="title" @cancel="cancel">
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{$t('registrations.feedback.submit')}}</button>
    </div>

    <form>
      <error :error="loadError">{{$t('registrations.feedback.loadError')}}</error>
      <error :error="saveError">{{$t('registrations.feedback.saveError')}}</error>
      <div class="form-group">
        <label for="feedback" class="sr-only">{{$t('registrations.feedback.label')}}</label>
        <textarea id="feedback" class="form-control" v-model.trim="text" :disabled="loading || loadError"></textarea>
      </div>
    </form>
  </modal>
</template>

<script>
  // noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        loading: true,
        id: null,
        original: null,
        text: null,
        student: null,
        saving: false,
        loadError: null,
        saveError: null
      }
    },
    watch: {
      id(id) {
        this.loading = true;
        this.original = null;
        this.text = null;
        this.student = null;
        this.loadError = null;
        this.saveError = null;

        if (this.id) {
          let self = this;

          this.$http.get('teacher/api/feedback/' + id).then(function (response) {
            self.student = response.data.student;
            self.original = self.text = response.data.feedback;
            self.loading = false;
            self.loadError = null;
          }).catch(function (error) {
            self.loadError = error;
          });
        }
      }
    },
    computed: {
      saveDisabled() {
        return this.saving || this.loadError || this.loading || this.original === this.text;
      },
      title() {
        return this.loadError ? this.$t('registrations.feedback.loadError') : this.$t('registrations.feedback.heading', {student: this.student});
      }
    },
    methods: {
      open(id) {
        this.id = id;
        this.show = !!this.id;
      },
      cancel() {
        this.show = false;
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;
          this.$http.post('teacher/api/feedback/' + this.id, {feedback: this.text}).then(function (response) {
            if (response.data.success) {
              self.original = self.text;
              self.saveError = null;
              self.cancel();
            } else {
              self.saveError = response.data.error;
            }
            self.saving = false;
          }).catch(function (error) {
            self.saving = false;
            self.saveError = error;
          });
        }
      }
    }
  }
</script>
