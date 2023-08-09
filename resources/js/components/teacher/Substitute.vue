<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<template>
  <div id="substituteDlg" class="modal fade in" >

    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content ">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel">{{ $t('lessons.substitute.heading') }}</h4>
        </div>
        <div class="modal-body">

    <filtered-list ref="filter"
                   :url="url"
                   :teachers="teachers"
                   :error-text="$t('lessons.substitute.loadError')"
                   :keep-filter="false"
                   :require-teacher="true"
                   v-on:filter="setFilter"
                   v-on:data="setData">
      <template slot="chooseStudent"></template>
      <template slot="empty"></template>
      <template v-if="!loading && data">
        <div v-if="data.sameTeacher" class="alert alert-danger">
          <p><strong>{{$t('lessons.substitute.sameTeacher')}}</strong></p>
        </div>
        <div v-else-if="data.new" class="alert alert-info">
          <p><strong>{{$t('lessons.substitute.new')}}</strong></p>
        </div>
        <div v-else-if="data.lesson && data.lesson.cancelled" class="alert alert-danger">
          <p><strong>{{$t('lessons.substitute.cancelled')}}</strong></p>
        </div>
        <div v-else-if="data.lesson" class="alert alert-info">
          <p><strong>{{$t('lessons.substitute.existing')}}</strong></p>
          <p>{{$t(data.lesson.maxstudents ? 'lessons.substitute.participantsWithMax' : 'lessons.substitute.participants', data.lesson)}}</p>
          <p>{{$t('messages.room')}}: {{data.lesson.room}}</p>
          <p v-if="data.lesson.course">{{$t('messages.course')}}: {{data.lesson.course}}</p>
        </div>
      </template>
    </filtered-list>

    <error :error="error">{{$t('lessons.substitute.saveError')}}</error>
        </div>


    <div class="modal-footer">
      <button type="button" class="btn btn-default" @click="cancel">{{$t('messages.cancel')}}</button>
      <button type="button" class="btn btn-primary" @click="save" :disabled="saveDisabled">{{submitLabel}}</button>
    </div>
      </div>
    </div>
  </div>
</template>

<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        show: false,
        shown: false,
        url: 'teacher/api/lessons/substitute/' + this.lesson,
        teacher: null,
        data: null,
        loading: false,
        saving: false,
        error: null,
        reload: false
      }
    },
    props: {
      teachers: {
        'type': Array,
        'required': true
      },
      lesson: {
        'type': Number,
        'required': true
      }
    },
    computed: {
      saveDisabled() {
        return this.loading || this.saving || !this.teacher || !this.data || this.data.sameTeacher || (this.data.lesson && this.data.lesson.cancelled);
      },
      submitLabel() {
        return (this.data && this.data.lesson && !this.data.lesson.cancelled)
            ? this.$t('lessons.substitute.reassign')
            : this.$t('lessons.substitute.submit');
      }
    },
    methods: {
      open() {
        this.show = true;
        this.shown = true;
        $("#substituteDlg").show();
      },
      cancel() {
        this.show = false;
        $("#substituteDlg").hide();
        if (this.reload) {
          location.reload();
        }
      },
      setFilter() {
        this.teacher = null;
        this.loading = true;
      },
      setData(data) {
        this.teacher = data ? this.$refs.filter.teacher : null;
        this.data = data;
        this.loading = false;
      },
      save() {
        if (!this.saveDisabled) {
          let self = this;
          this.saving = true;
          this.$http.post('teacher/api/lessons/substitute/' + this.lesson + '/' + this.teacher, {}).then(function (response) {
            if (response.data.success) {
              self.error = null;
              self.reload = true;
              self.$refs.filter.teacher = null;
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
