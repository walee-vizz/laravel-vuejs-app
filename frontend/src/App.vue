<script setup>
import { ref } from "vue";

let count = ref(0);

</script>

<template>
  <div class="text-center">
    <h4 class="text-danger">The current count is</h4>
    <h4>{{ count }}</h4>
    <button class="btn btn-danger mx-1 w-25" @click="count--">-</button>
    <button class="btn btn-success mx-1 w-25" @click="count++">+</button>
  </div>
</template>

<style scoped>
</style>