<template>
  <view class="category-page">
    <view class="category-list">
      <view
        v-for="cat in categories"
        :key="cat.id"
        class="category-item"
        :class="{ active: currentCategory === cat.id }"
        @click="selectCategory(cat.id)"
      >
        {{ cat.name }}
      </view>
    </view>

    <view class="category-content">
      <view v-if="loading" class="loading">加载中...</view>
      <view v-else class="script-list">
        <ScriptCard :list="scripts" />
        <view v-if="hasMore" class="loading" @click="loadMore">加载更多</view>
        <view v-else-if="scripts.length > 0" class="loading">没有更多了</view>
        <view v-else class="empty">该分类下暂无剧本</view>
      </view>
    </view>
  </view>
</template>

<script>
import ScriptCard from '../../components/ScriptCard.vue'
import { categoryApi } from '../../services/api'

export default {
  components: {
    ScriptCard
  },
  data() {
    return {
      categories: [],
      currentCategory: null,
      scripts: [],
      page: 1,
      limit: 20,
      hasMore: true,
      loading: false
    }
  },
  onLoad() {
    this.fetchCategories()
  },
  methods: {
    async fetchCategories() {
      try {
        const data = await categoryApi.list()
        this.categories = data.list
        if (this.categories.length > 0) {
          this.currentCategory = this.categories[0].id
          this.fetchScripts()
        }
      } catch (e) {
        console.error(e)
      }
    },
    selectCategory(id) {
      this.currentCategory = id
      this.page = 1
      this.scripts = []
      this.hasMore = true
      this.fetchScripts()
    },
    async fetchScripts() {
      this.loading = true
      try {
        const data = await categoryApi.getScripts(this.currentCategory, {
          page: this.page,
          limit: this.limit
        })
        this.scripts = [...this.scripts, ...data.scripts]
        this.hasMore = this.scripts.length < data.total
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
.category-page {
  display: flex;
  height: 100vh;
}

.category-list {
  width: 180rpx;
  background: #f5f5f5;
  overflow-y: auto;
}

.category-item {
  padding: 30rpx 20rpx;
  text-align: center;
  font-size: 28rpx;
  color: #666;
  border-left: 6rpx solid transparent;
}

.category-item.active {
  background: #fff;
  color: #1890ff;
  border-left-color: #1890ff;
}

.category-content {
  flex: 1;
  padding: 24rpx;
  overflow-y: auto;
}

.loading, .empty {
  text-align: center;
  padding: 40rpx;
  color: #999;
}
</style>
