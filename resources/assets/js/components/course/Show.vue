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
      },
      resizePopover(id) {
        this.$refs['popover-' + id].position();
      }
    }
  }
</script>
