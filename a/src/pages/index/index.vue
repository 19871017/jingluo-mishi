<template>
  <view class="page-index">
    <Banner :banners="homeData.banners" />
    <AdBanner :ads="homeData.ads" />

    <view class="section">
      <view class="section-header">
        <text class="section-title">热门剧本</text>
        <text class="section-more" @click="goSearch">更多 ></text>
      </view>
      <ScriptCard :list="homeData.scripts" />
    </view>
  </view>
</template>

<script>
import Banner from '../../components/Banner.vue'
import AdBanner from '../../components/AdBanner.vue'
import ScriptCard from '../../components/ScriptCard.vue'
import { homeApi } from '../../services/api'

export default {
  components: {
    Banner,
    AdBanner,
    ScriptCard
  },
  data() {
    return {
      homeData: {
        banners: [],
        ads: [],
        scripts: []
      }
    }
  },
  onLoad() {
    this.fetchHomeData()
  },
  onPullDownRefresh() {
    this.fetchHomeData().finally(() => {
      uni.stopPullDownRefresh()
    })
  },
  methods: {
    async fetchHomeData() {
      try {
        const data = await homeApi.getHomeData()
        this.homeData = data
      } catch (e) {
        console.error('Failed to fetch home data:', e)
      }
    },
    goSearch() {
      uni.navigateTo({ url: '/pages/search/search' })
    }
  }
}
</script>

<style scoped>
.section {
  padding: 24rpx;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
}

.section-more {
  font-size: 26rpx;
  color: #999;
}
</style>
