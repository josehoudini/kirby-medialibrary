<template>
  <k-panel-inside>
    <k-header>Media Library</k-header>

    <k-items :items="images" layout="cardlets" />
  </k-panel-inside>
</template>

<script>
export default {
  props: {
    images: {
      type: Array,
      default: () => [],
    },
    videos: {
      type: Array,
      default: () => [],
    },
  },
}
</script>
