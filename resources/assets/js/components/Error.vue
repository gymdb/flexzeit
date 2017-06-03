<template>
  <div v-if="error" class="alert alert-danger">
    <strong><slot/></strong>
    <p>{{error}}</p>
  </div>
</template>

<script>
  export default {
    props: {
      error: {
        type: Number,
        default: null
      }
    }
  }
</script>
