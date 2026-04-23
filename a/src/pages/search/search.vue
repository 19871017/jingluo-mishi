<template>
  <view class="search-page">
    <view class="search-bar">
      <input
        v-model="keyword"
        class="search-input"
        placeholder="搜索剧本名称"
        @confirm="doSearch"
      />
      <button class="search-btn" @click="doSearch">搜索</button>
    </view>

    <view v-if="loading" class="loading">加载中...</view>
    <view v-else-if="!hasSearched" class="empty">请输入关键词搜索剧本</view>
    <view v-else-if="scripts.length === 0" class="empty">未找到相关剧本</view>
    <view v-else class="result-list">
      <ScriptCard :list="scripts" />
      <view v-if="hasMore" class="loading" @click="loadMore">加载更多</view>
      <view v-else class="loading">没有更多了</view>
    </view>
  </view>
</template>

<script>
import ScriptCard from '../../components/ScriptCard.vue'
import { scriptApi } from '../../services/api'

export default {
  components: {
    ScriptCard
  },
  data() {
    return {
      keyword: '',
      scripts: [],
      page: 1,
      limit: 20,
      total: 0,
      loading: false,
      hasSearched: false,
      hasMore: true
    }
  },
  methods: {
    async doSearch() {
      if (!this.keyword.trim()) return
      this.page = 1
      this.scripts = []
      this.hasSearched = true
      this.hasMore = true
      await this.fetchScripts()
    },
    async fetchScripts() {
      this.loading = true
      try {
        const data = await scriptApi.search({
          keyword: this.keyword,
          page: this.page,
          limit: this.limit
        })
        this.scripts = [...this.scripts, ...data.list]
        this.total = data.total
        this.hasMore = this.scripts.length < this.total
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    loadMore() {
      this.page++
      this.fetchScripts()
    }
  }
}
</script>

<style scoped>
.search-page {
  padding: 24rpx;
}

.search-bar {
  display: flex;
  gap: 20rpx;
  margin-bottom: 30rpx;
}

.search-input {
  flex: 1;
  height: 72rpx;
  padding: 0 24rpx;
  background: #fff;
  border-radius: 36rpx;
  font-size: 28rpx;
}

.search-btn {
  width: 140rpx;
  height: 72rpx;
  line-height: 72rpx;
  background: #1890ff;
  color: #fff;
  border-radius: 36rpx;
  font-size: 28rpx;
}

.loading, .empty {
  text-align: center;
  padding: 40rpx;
  color: #999;
}
</style>
