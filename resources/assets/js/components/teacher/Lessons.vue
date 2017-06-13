<script>
  export default {
    data() {
      return {
        lessons: null,
        error: null
      }
    },
    methods: {
      loadData(filter) {
        if (!filter) {
          this.lessons = null;
          this.error = null;
        } else {
          let self = this;
          this.$http.get('/teacher/api/lessons', {params: filter}).then(function (response) {
            self.error = null;
            self.lessons = response.data;
          }).catch(function (error) {
            self.error = error.response ? error.response.status : 100;
          });
        }
      }
    }
  }
</script>
