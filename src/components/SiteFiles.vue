<template>
  <k-panel-inside>
    <k-header>Media Library</k-header>

    <k-tabs
      :tab="tab"
      :tabs="[
        {
          name: 'images',
          label: 'Images',
          link: '/media-library/images',
          icon: 'image',
        },
        {
          name: 'videos',
          label: 'Videos',
          link: '/media-library/videos',
          icon: 'video',
        },
        {
          name: 'other',
          label: 'Other',
          link: '/media-library/other',
          icon: 'file',
        },
      ]"
    />

    <k-items v-if="tab === 'images'" :items="images" layout="cardlets" />
    <k-items v-if="tab === 'videos'" :items="videos" layout="cardlets" />
  </k-panel-inside>
</template>

<script>
export default {
  props: {
    tab: {
      type: String,
      default: 'images',
    },
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
