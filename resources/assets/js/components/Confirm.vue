<script>
  export default {
    props: {
      confirmText: {
        'type': String,
        'required': true
      }
    },
    methods: {
      destroy($event) {
        if (!window.confirm(this.confirmText)) {
          $event.preventDefault();
        }
      }
    }
  }
</script>
