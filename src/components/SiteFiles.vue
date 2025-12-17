<template>
  <k-panel-inside>
    <k-header>{{ headerTitle }}</k-header>

    <!-- Tabs -->
    <k-tabs :tab="tab" :tabs="tabs" />

    <!-- Collection with items, loading, empty, and pagination -->
    <k-collection
      :items="items"
      :layout="'cardlets'"
      :empty="emptyState"
      :pagination="paginationConfig"
      :loading="loading"
      @paginate="handlePaginate"
      :help="'To ensure optimal performance: images up to 1 MB and videos up to 3 MB'"
    />
  </k-panel-inside>
</template>

<script>
export default {
  props: {
    tab: { type: String, default: 'images' },
  },
  data() {
    return {
      items: [],
      total: 0,
      page: 1,
      itemsPerPage: 30,
      totalPages: 1,
      loading: false,
      stats: {},
    }
  },
  computed: {
    tabs() {
      return [
        {
          name: 'images',
          label: this.getTabLabel('images'),
          link: '/media-library/images',
          icon: 'image',
        },
        {
          name: 'videos',
          label: this.getTabLabel('videos'),
          link: '/media-library/videos',
          icon: 'video',
        },
        {
          name: 'documents',
          label: this.getTabLabel('documents'),
          link: '/media-library/documents',
          icon: 'document',
        },
        {
          name: 'other',
          label: this.getTabLabel('other'),
          link: '/media-library/other',
          icon: 'file',
        },
      ]
    },
    headerTitle() {
      const titles = {
        images: 'Images',
        videos: 'Videos',
        documents: 'Documents',
        other: 'Other Files',
      }

      return titles[this.tab] || 'Media Library'
    },
    emptyState() {
      return {
        icon: 'info',
        text: `No ${this.tab} found`,
      }
    },
    paginationConfig() {
      if (this.totalPages <= 1) return false

      return {
        page: this.page,
        limit: this.itemsPerPage,
        total: this.total,
        details: true,
      }
    },
  },
  watch: {
    tab() {
      this.page = 1
      this.loadFiles()
    },
  },
  mounted() {
    this.loadStats()
    this.loadFiles()
  },
  methods: {
    async loadStats() {
      try {
        this.stats = await this.$api.get('media-library/stats')
      } catch (error) {
        console.error('Failed to load stats:', error)
      }
    },
    getTabLabel(tab) {
      const labels = {
        images: 'Images',
        videos: 'Videos',
        documents: 'Documents',
        other: 'Other',
      }
      const count = this.stats[tab]
      return count !== undefined ? `${count} ${labels[tab]}` : labels[tab]
    },
    async loadFiles() {
      this.loading = true

      try {
        const response = await this.$api.get('media-library/files', {
          tab: this.tab,
          page: this.page,
          limit: this.itemsPerPage,
        })

        this.items = response.items
        this.total = response.total
        this.totalPages = response.pages
      } catch (error) {
        console.error('Failed to load files:', error)
        this.$store.dispatch('notification/error', 'Failed to load files')
      } finally {
        this.loading = false
      }
    },
    handlePaginate(event) {
      this.page = event.page
      this.loadFiles()
    },
  },
}
</script>
