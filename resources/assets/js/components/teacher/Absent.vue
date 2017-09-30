<script>
  //noinspection JSUnusedGlobalSymbols
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
        this.$refs.filter.refresh();
      },
      setRefreshError(error) {
        this.refreshError = error;
        this.attendanceError = null;
      }
    }
  }
</script>
