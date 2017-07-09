<!--suppress JSUnresolvedVariable -->
<template>
  <modal :value="show" effect="fade" :title="$t('registrations.register.heading')" @cancel="cancel" large>
    <div class="modal-footer" slot="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{submitLabel}}</button>
    </div>

    <filtered-list ref="filter"
                    :url="url"
                    :groups="groups"
                    :error-text="$t('registrations.register.loadError')"
                    :keep-filter="false"
                    v-on:filter="setFilter"
                    v-on:data="setData">
      <template slot="chooseStudent"></template>
      <template slot="empty"></template>
      <template scope="props">
        <div class="alert alert-warning">
          {{$t('registrations.register.registered')}}: {{props.data[0].teacher}} <span v-if="props.data[0].course">({{props.data[0].course}}</span>
        </div>
      </template>
    </filtered-list>

    <error :error="error">{{$t('registrations.register.saveError')}}</error>
  </modal>
</template>

<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        url: '/teacher/api/registrations/' + this.date + '/' + this.number,
        student: null,
        registration: null,
        error: null,
        reload: false
      }
    },
    props: {
      groups: {
        'type': Array,
        'required': true
      },
      date: {
        'type': String,
        'required': true
      },
      number: {
        'type': Number,
        'required': true
      },
      admin: {
        'type': Boolean,
        'default': false
      },
      lesson: {
        'type': Number,
        'required': true
      }
    },
    computed: {
      saveDisabled() {
        return !this.student || (this.registration && (!this.admin || this.registration === this.lesson));
      },
      submitLabel() {
        return (this.admin && this.registration && this.registration !== this.lesson)
            ? this.$t('registrations.register.change')
            : this.$t('registrations.register.submit');
      }
    },
    methods: {
      open() {
        this.show = true;
      },
      cancel() {
        this.show = false;
        if (this.reload) {
          location.reload();
        }
      },
      setFilter() {
        this.student = null;
      },
      setData(data) {
        if (data) {
          this.student = this.$refs.filter.student;
          this.registration = data.length ? data[0].lesson_id : null;
        } else {
          this.student = null;
        }
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.$http.post('/teacher/api/register/' + this.lesson + '/' + this.student, {}).then(function (response) {
            if (response.data.success) {
              self.error = null;
              self.reload = true;
              self.$refs.filter.loadData();
            } else {
              self.error = response.data.error;
            }
          }).catch(function (error) {
            self.error = error.response ? error.response.status : 100;
          });
        }
      }
    }
  }
</script>
