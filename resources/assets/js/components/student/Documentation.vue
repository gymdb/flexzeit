<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<template>
  <modal :value="show" effect="fade" :title="title" @cancel="cancel">
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{$t('student.documentation.submit')}}</button>
    </div>

    <form>
      <error :error="loadError">{{$t('student.documentation.loadError')}}</error>
      <error :error="saveError">{{$t('student.documentation.saveError')}}</error>
      <div class="form-group">
        <label for="feedback" class="sr-only">{{$t('student.documentation.label')}}</label>
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
        el: null,
        original: null,
        text: null,
        teacher: null,
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
        this.loadError = null;
        this.saveError = null;

        if (this.id) {
          let self = this;

          this.$http.get('/student/api/documentation/' + id).then(function (response) {
            self.original = self.text = response.data.documentation;
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
        return this.loadError ? this.$t('student.documentation.loadError') : this.$t('student.documentation.heading', {teacher: this.teacher});
      }
    },
    methods: {
      open(id, teacher, el) {
        this.id = id;
        this.teacher = teacher || null;
        this.el = el || null;
        this.show = !!this.id;
      },
      cancel() {
        this.show = false;
        if (this.el) {
          this.el.classList.remove(this.text ? 'btn-danger' : 'btn-default');
          this.el.classList.add(this.text ? 'btn-default' : 'btn-danger');
          this.el.text = this.$t(this.text ? 'student.documentation.edit' : 'student.documentation.add')
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;
          this.$http.post('/student/api/documentation/' + this.id, {documentation: this.text}).then(function (response) {
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
