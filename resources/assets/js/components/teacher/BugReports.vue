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
        this.error = null;
      },
      setError(error) {
        this.error = error;
      }
    }
  }
</script>
