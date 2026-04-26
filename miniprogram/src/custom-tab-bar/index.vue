<template>
  <view class="tabbar-wrap">
    <view class="tabbar-inner">
      <view
        v-for="(item, index) in tabs"
        :key="item.pagePath"
        class="tab-item"
        :class="{ active: selected === index }"
        @click="switchTab(index)"
      >
        <view class="tab-icon-shell">
          <image class="tab-icon-image" :src="item.icon" mode="aspectFit" />
        </view>
        <text class="tab-text">{{ item.text }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const selected = ref(0)

function svgToDataUri(svg) {
  return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`
}

function buildIcon(type, active) {
  const stroke = active ? '#1f2937' : '#9ca3af'
  const fill = active ? '#fff4e8' : '#ffffff'

  const svgs = {
    category: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="8" y="8" width="48" height="48" rx="18" fill="${fill}"/><rect x="18" y="18" width="11" height="11" rx="3" fill="none" stroke="${stroke}" stroke-width="3.2"/><rect x="35" y="18" width="11" height="11" rx="3" fill="none" stroke="${stroke}" stroke-width="3.2"/><rect x="18" y="35" width="11" height="11" rx="3" fill="none" stroke="${stroke}" stroke-width="3.2"/><rect x="35" y="35" width="11" height="11" rx="3" fill="none" stroke="${stroke}" stroke-width="3.2"/></svg>`,
    brand: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="8" y="8" width="48" height="48" rx="18" fill="${fill}"/><path d="M20 24h18l6 8-6 8H20V24Zm8 5.5a2.5 2.5 0 1 0 0 .1Z" fill="none" stroke="${stroke}" stroke-width="3.2" stroke-linejoin="round"/></svg>`,
    case: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="8" y="8" width="48" height="48" rx="18" fill="${fill}"/><path d="M19 42h26" stroke="${stroke}" stroke-width="3.2" stroke-linecap="round"/><path d="M22 42l10-20h3l10 20" stroke="${stroke}" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 34h14" stroke="${stroke}" stroke-width="2.8" stroke-linecap="round"/></svg>`,
    community: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="8" y="8" width="48" height="48" rx="18" fill="${fill}"/><path d="M21 23h22a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H31l-8 6v-6h-2a4 4 0 0 1-4-4V27a4 4 0 0 1 4-4Z" fill="none" stroke="${stroke}" stroke-width="3.2" stroke-linejoin="round"/><path d="M25 30h14M25 36h10" stroke="${stroke}" stroke-width="3.2" stroke-linecap="round"/></svg>`,
    user: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect x="8" y="8" width="48" height="48" rx="18" fill="${fill}"/><circle cx="32" cy="27" r="7" fill="none" stroke="${stroke}" stroke-width="3.2"/><path d="M19 45c3.5-6 8.5-9 13-9s9.5 3 13 9" fill="none" stroke="${stroke}" stroke-width="3.2" stroke-linecap="round"/></svg>`,
  }

  return svgToDataUri(svgs[type])
}

const tabs = ref([
  { pagePath: '/pages/category/category', text: '剧本', icon: buildIcon('category', true), activeIcon: buildIcon('category', true), inactiveIcon: buildIcon('category', false) },
  { pagePath: '/pages/brand/brand-list', text: '品牌', icon: buildIcon('brand', false), activeIcon: buildIcon('brand', true), inactiveIcon: buildIcon('brand', false) },
  { pagePath: '/pages/case/case-list', text: '案例', icon: buildIcon('case', false), activeIcon: buildIcon('case', true), inactiveIcon: buildIcon('case', false) },
  { pagePath: '/pages/market/market', text: '社区', icon: buildIcon('community', false), activeIcon: buildIcon('community', true), inactiveIcon: buildIcon('community', false) },
  { pagePath: '/pages/user/user', text: '我的', icon: buildIcon('user', false), activeIcon: buildIcon('user', true), inactiveIcon: buildIcon('user', false) },
])

function switchTab(index) {
  selected.value = index
  tabs.value = tabs.value.map((item, current) => ({
    ...item,
    icon: current === index ? item.activeIcon : item.inactiveIcon,
  }))
  uni.switchTab({ url: tabs.value[index].pagePath })
}

defineExpose({
  setSelected(index) {
    selected.value = index
    tabs.value = tabs.value.map((item, current) => ({
      ...item,
      icon: current === index ? item.activeIcon : item.inactiveIcon,
    }))
  },
})
</script>

<style scoped>
.tabbar-wrap {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(16rpx);
  box-shadow: 0 -10rpx 24rpx rgba(17, 24, 39, 0.06);
  padding-bottom: env(safe-area-inset-bottom);
}

.tabbar-inner {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  padding: 10rpx 12rpx 8rpx;
}

.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  min-height: 100rpx;
  border-radius: 20rpx;
}

.tab-item.active {
  background: linear-gradient(180deg, #fff7ef, #ffffff);
}

.tab-icon-shell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56rpx;
  height: 56rpx;
}

.tab-icon-image {
  width: 44rpx;
  height: 44rpx;
}

.tab-text {
  font-size: 22rpx;
  line-height: 1.2;
  font-weight: 700;
  color: #6b7280;
  letter-spacing: 0.2rpx;
}

.tab-item.active .tab-text {
  color: #1f2937;
}
</style>
