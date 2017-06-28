<template>
  <modal :value="show" effect="fade" :title="title" @cancel="cancel">
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{cancelText}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{okText}}</button>
    </div>

    <form>
      <error :error="loadError">{{loadErrorText}}</error>
      <error :error="saveError">{{saveErrorText}}</error>
      <div class="form-group">
        <label for="feedback" class="sr-only">{{label}}</label>
        <textarea id="feedback" class="form-control" v-model.trim="text" :disabled="loading || loadError"></textarea>
      </div>
    </form>
  </modal>
</template>

<script>
  export default {
    data() {
      return {
        show: false,
        loading: true,
        id: null,
        original: null,
        text: null,
        student: null,
        loadError: null,
        saveError: null
      }
    },
    props: {
      header: {
        'type': String,
        'required': true
      },
      cancelText: {
        'type': String,
        'required': true
      },
      okText: {
        'type': String,
        'required': true
      },
      label: {
        'type': String,
        'required': true
      },
      loadErrorText: {
        'type': String,
        'required': true
      },
      saveErrorText: {
        'type': String,
        'required': true
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

          this.$http.get('/teacher/api/feedback/' + id).then(function (response) {
            self.student = response.data.student;
            self.original = self.text = response.data.feedback;
            self.loading = false;
            self.loadError = null;
          }).catch(function (error) {
            self.loadError = error.response ? error.response.status : 100;
          });
        }
      }
    },
    computed: {
      saveDisabled() {
        return this.loadError || this.loading || this.original === this.text;
      },
      title() {
        return this.loadError ? this.loadErrorText : this.header + ' ' + this.student;
      }
    },
    methods: {
      open(id) {
        this.id = id;
        this.show = !!this.id;
      },
      cancel(p) {
        this.show = false;
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.$http.post('/teacher/api/feedback/' + this.id, {feedback: this.text}).then(function (response) {
            if (response.data.success) {
              self.original = self.text;
              self.saveError = null;
            } else {
              self.saveError = response.data.error;
            }
          }).catch(function (error) {
            self.saveError = error.response ? error.response.status : 100;
          });
        }
      }
    }
  }
</script>
