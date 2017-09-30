<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        error: null,
      }
    },
    methods: {
      refresh() {
        this.$refs.filter.refresh();
      },
      setError(error) {
        this.error = error;
      }
    }
  }
</script>
