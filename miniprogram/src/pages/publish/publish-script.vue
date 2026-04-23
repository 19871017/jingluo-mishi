<template>
  <view class="disabled-page">
    <view class="card notice-card">
      <text class="title">剧本发布已迁移到品牌方后台</text>
      <text class="desc">普通浏览用户只能浏览剧本、品牌介绍并参与社区互动，不能在小程序内发布剧本。</text>
      <text class="desc">如你是品牌方，请联系平台管理员开通品牌后台账号后，在品牌方后台登录发布本品牌剧本。</text>
      <button class="back-btn" @click="goBack">返回上一页</button>
    </view>
  </view>
</template>

<script setup>
function goBack() {
  uni.navigateBack({
    fail: () => {
      uni.switchTab({ url: '/pages/user/user' })
    },
  })
}
</script>

<style scoped>
.disabled-page {
  min-height: 100vh;
  padding: 32rpx 24rpx;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.12), transparent 20%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 24%, #f6f7fb 100%);
}

.notice-card {
  padding: 32rpx 28rpx;
}

.title {
  display: block;
  font-size: 36rpx;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.4;
}

.desc {
  display: block;
  margin-top: 18rpx;
  font-size: 26rpx;
  color: #6b7280;
  line-height: 1.8;
}

.back-btn {
  margin-top: 28rpx;
  height: 84rpx;
  line-height: 84rpx;
  border-radius: 18rpx;
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
  font-size: 28rpx;
  font-weight: 700;
}
</style>
