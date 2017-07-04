<script>
  export default {
    data() {
      return {
        attendanceError: null,
        refreshError: null
      }
    },
    methods: {
      setAttendanceSuccess() {
        this.attendanceError = null;
        this.refreshError = null;
      },
      setAttendanceError(error) {
        this.attendanceError = error;
        this.refreshError = null;
      },
      setRefreshSuccess() {
        location.reload();
      },
      setRefreshError(error) {
        this.refreshError = error;
        this.attendanceError = null;
      }
    }
  }
</script>
