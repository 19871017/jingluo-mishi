<template>
  <view class="favorites-page">
    <view v-if="loading" class="loading">加载中...</view>
    <view v-else-if="favorites.length === 0" class="empty">暂无收藏</view>
    <view v-else>
      <ScriptCard :list="favorites" />
      <view v-if="hasMore" class="loading" @click="loadMore">加载更多</view>
    </view>
  </view>
</template>

<script>
import ScriptCard from '../../components/ScriptCard.vue'
import { userApi } from '../../services/api'

export default {
  components: {
    ScriptCard
  },
  data() {
    return {
      favorites: [],
      page: 1,
      limit: 20,
      loading: false,
      hasMore: true
    }
  },
  onLoad() {
    this.fetchFavorites()
  },
  onPullDownRefresh() {
    this.fetchFavorites().finally(() => {
      uni.stopPullDownRefresh()
    })
  },
  methods: {
    async fetchFavorites() {
      this.loading = true
      try {
        const data = await userApi.getFavorites({ page: this.page, limit: this.limit })
        this.favorites = [...this.favorites, ...data.list]
        this.hasMore = data.list.length === this.limit
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    loadMore() {
      this.page++
      this.fetchFavorites()
    }
  }
}
</script>

<style scoped>
.favorites-page {
  padding: 24rpx;
}

.loading, .empty {
  text-align: center;
  padding: 100rpx;
  color: #999;
}
</style>
