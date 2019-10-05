<!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
<script>
  //noinspection JSUnusedGlobalSymbols
  export default {
    data() {
      return {
        unregisterError: null
      }
    },
    computed: {
      categoryOptionsList() {
      const options = [];
        for (let i = 0; i <= 5; i++) {
         options.push({ label: this.$t('courses.category')[i]});
        }
        return options;
      },
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
