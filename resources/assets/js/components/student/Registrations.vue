<script>
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
        this.$refs.documentationModal.open(id);
      }
    }
  }
</script>
