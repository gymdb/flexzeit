<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        attendanceError: null,
        unregisterError: null,
        modifiedAttendanceChecked: this.attendanceChecked
      }
    },
    props: {
      id: {
        'type': Number,
        'required': true
      },
      attendanceChecked: {
        'type': Boolean,
        'default': false
      }
    },
    methods: {
      setAttendanceSuccess() {
        this.attendanceError = null;
        this.unregisterError = null;
      },
      setAttendanceError(error) {
        this.attendanceError = error;
        this.unregisterError = null;
      },
      setUnregisterSuccess() {
        location.reload();
      },
      setUnregisterError(error) {
        this.unregisterError = error;
        this.attendanceError = null;
      },
      setAttendanceChecked() {
        let self = this;
        this.$http.post('/teacher/api/attendanceChecked/' + this.id).then(function (response) {
          if (response.data.success) {
            self.modifiedAttendanceChecked = true;
            self.setAttendanceSuccess();
          } else {
            self.setAttendanceError(response.data.error);
          }
        }).catch(function (error) {
          self.setAttendanceError(error);
        });
      },
      openFeedback(id) {
        this.$refs.feedbackModal.open(id);
      },
      openRegister() {
        this.$refs.registerModal.open();
      },
      openChangeRegistration(student, date, number) {
        this.$refs.changeRegistrationModal.open(student, date, number);
      },
      openSubstitute() {
        this.$refs.substituteModal.open();
      }
    }
  }
</script>
