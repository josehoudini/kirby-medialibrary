<template>
  <k-panel-inside>
    <k-header>{{ headerTitle }}</k-header>

    <!-- Tabs -->
    <k-tabs :tab="tab" :tabs="tabs" />

    <!-- Items -->
    <k-items
      v-if="currentItems.length"
      :items="paginatedItems"
      layout="cardlets"
    />

    <!-- Kirby Pagination -->
    <k-pagination
      v-if="totalPages > 1"
      :page="page"
      :limit="itemsPerPage"
      :total="currentItems.length"
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
    other: { type: Array, default: () => [] },
  },
  data() {
    return {
      page: 1,
      itemsPerPage: 30,
    }
  },
  computed: {
    tabs() {
      return [
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
      ]
    },
    currentItems() {
      return (
        {
          images: this.images,
          videos: this.videos,
          other: this.other,
        }[this.tab] || []
      )
    },

    headerTitle() {
      const titles = {
        images: 'Images',
        videos: 'Videos',
        other: 'Other',
      }

      const title = titles[this.tab] || 'Media Library'
      return `${title} (${this.currentItems.length} items)`
    },

    paginatedItems() {
      const start = (this.page - 1) * this.itemsPerPage
      return this.currentItems.slice(start, start + this.itemsPerPage)
    },

    totalPages() {
      return Math.ceil(this.currentItems.length / this.itemsPerPage)
    },
  },
  watch: {
    currentItems() {
      if (this.page > this.totalPages) {
        this.page = Math.max(this.totalPages, 1)
      }
    },
    tab() {
      this.page = 1
    },
  },
}
</script>
