<script>
  export default {
    data() {
      return {
        registrations: null,
        error: null
      }
    },
    methods: {
      loadData(filter) {
        if (!filter) {
          this.registrations = null;
          this.error = null;
        } else {
          let self = this;
          this.$http.get('/teacher/api/feedback', {params: filter}).then(function (response) {
            self.error = null;
            self.registrations = response.data;
          }).catch(function (error) {
            self.error = error.response ? error.response.status : 100;
          });
        }
      }
    }
  }
</script>
