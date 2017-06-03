<script>
  export default {
    data() {
      return {
        error: null
      }
    },
    methods: {
      setSuccess() {
        this.error = null;
      },
      setError(error) {
        console.log(error);
        this.error = error;
      }
    }
  }
</script>
