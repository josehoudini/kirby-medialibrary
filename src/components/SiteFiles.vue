<template>
  <k-panel-inside>
    <k-header>Media Library</k-header>

    <!-- Tabs -->
    <k-tabs
      :tab="tab"
      :tabs="[
        { name: 'images', label: 'Images', link: '/media-library/images', icon: 'image' },
        { name: 'videos', label: 'Videos', link: '/media-library/videos', icon: 'video' },
        { name: 'other',  label: 'Other',  link: '/media-library/other',  icon: 'file' }
      ]"
    />

    <!-- Items -->
    <k-items v-if="tab === 'images'" :items="paginatedItems" layout="cardlets" />
    <k-items v-if="tab === 'videos'" :items="paginatedItems" layout="cardlets" />
    <k-items v-if="tab === 'other'"  :items="paginatedItems" layout="cardlets" />

    <!-- Kirby Pagination -->
    <k-pagination
      v-if="totalPages > 1"
      :page="page"
      :total="totalPages"
      :details="true"
      @paginate="page = $event.page"
      style="margin-top: var(--spacing-12)"
    />
  </k-panel-inside>
</template>

<script>
export default {
  props: {
    tab: { type: String, default: 'images' },
    images: { type: Array, default: () => [] },
    videos: { type: Array, default: () => [] },
    other:  { type: Array, default: () => [] }
  },
  data() {
    return {
      page: 1,
      itemsPerPage: 32, 
    }
  },
  computed: {
    currentItems() {
      if (this.tab === 'images') return this.images
      if (this.tab === 'videos') return this.videos
      if (this.tab === 'other')  return this.other
      return []
    },
    paginatedItems() {
      const start = (this.page - 1) * this.itemsPerPage
      return this.currentItems.slice(start, start + this.itemsPerPage)
    },
    totalPages() {
      return Math.ceil(this.currentItems.length / this.itemsPerPage)
    }
  },
  watch: {
    tab() {
      this.page = 1
    }
  }
}
</script>