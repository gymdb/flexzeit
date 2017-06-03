<script>
  export default {
    data() {
      return {
        attendanceError: null,
        unregisterError: null
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
        console.log(error);
        this.attendanceError = error;
        this.unregisterError = null;
      },
      setUnregisterSuccess() {
        location.reload();
      },
      setUnregisterError(error) {
        console.log(error);
        this.unregisterError = error;
        this.attendanceError = null;
      },
      setAttendanceChecked() {
        let self = this;
        this.$http.post('/teacher/api/attendanceChecked/' + this.id).then(function (response) {
          if (response.data.success) {
            self.attendanceChecked = true;
            self.setAttendanceSuccess();
          } else {
            self.setAttendanceError(response.data.error);
          }
        }).catch(function (error) {
          self.setAttendanceError(error.response ? error.response.status : 100);
        });
      },
      openFeedback(id) {
        this.$refs.feedbackModal.open(id);
      }
    }
  }
</script>
