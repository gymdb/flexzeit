<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        error: null
      }
    },
    methods: {
      setUnregisterSuccess() {
        location.reload();
      },
      setUnregisterError(error) {
        this.error = error;
      },
      openDocumentation(id) {
        //noinspection JSUnresolvedVariable
        this.$refs.documentationModal.open(id);
      }
    }
  }
</script>
