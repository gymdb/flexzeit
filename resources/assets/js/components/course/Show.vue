<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        unregisterError: null
      }
    },
    methods: {
      setUnregisterSuccess() {
        location.reload();
      },
      setUnregisterError(error) {
        this.unregisterError = error;
      },
      openRegister() {
        this.$refs.registerModal.open();
      }
    }
  }
</script>
