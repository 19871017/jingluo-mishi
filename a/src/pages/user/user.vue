<template>
  <view class="user-page">
    <view class="user-header" @click="goLogin">
      <image
        class="user-avatar"
        :src="userInfo?.avatar || '/static/images/default-avatar.png'"
        mode="aspectFill"
      />
      <view class="user-info">
        <text class="user-nickname">{{ userInfo?.nickname || '点击登录' }}</text>
        <text class="user-tip">登录后享受更多服务</text>
      </view>
    </view>

    <view class="menu-section">
      <view class="menu-item" @click="goPage('/pages/user/pages/favorites')">
        <text class="menu-icon">❤️</text>
        <text class="menu-text">我的收藏</text>
        <text class="menu-arrow">></text>
      </view>
      <view class="menu-item" @click="goPage('/pages/user/pages/follows')">
        <text class="menu-icon">⭐</text>
        <text class="menu-text">我的关注</text>
        <text class="menu-arrow">></text>
      </view>
      <view class="menu-item" @click="goPage('/pages/user/pages/publish')">
        <text class="menu-icon">📝</text>
        <text class="menu-text">发布管理</text>
        <text class="menu-arrow">></text>
      </view>
    </view>

    <view class="menu-section">
      <view class="menu-item" @click="logout" v-if="isLoggedIn">
        <text class="menu-icon">🚪</text>
        <text class="menu-text">退出登录</text>
        <text class="menu-arrow">></text>
      </view>
    </view>
  </view>
</template>

<script>
import { useUserStore } from '../../stores/user'

export default {
  data() {
    return {
      userStore: useUserStore()
    }
  },
  computed: {
    userInfo() {
      return this.userStore.userInfo
    },
    isLoggedIn() {
      return this.userStore.isLoggedIn
    }
  },
  onShow() {
    if (this.isLoggedIn && !this.userInfo) {
      this.userStore.fetchProfile()
    }
  },
  methods: {
    goLogin() {
      if (!this.isLoggedIn) {
        uni.login({
          provider: 'weixin',
          success: async (res) => {
            try {
              await this.userStore.login(res.code)
              uni.showToast({ title: '登录成功', icon: 'success' })
            } catch (e) {
              uni.showToast({ title: '登录失败', icon: 'none' })
            }
          },
          fail: () => {
            uni.showToast({ title: '微信登录失败', icon: 'none' })
          }
        })
      }
    },
    goPage(url) {
      if (!this.isLoggedIn) {
        uni.showToast({ title: '请先登录', icon: 'none' })
        return
      }
      uni.navigateTo({ url })
    },
    logout() {
      uni.showModal({
        title: '提示',
        content: '确定要退出登录吗？',
        success: (res) => {
          if (res.confirm) {
            this.userStore.logout()
            uni.showToast({ title: '已退出', icon: 'none' })
          }
        }
      })
    }
  }
}
</script>

<style scoped>
.user-page {
  padding: 24rpx;
}

.user-header {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 16rpx;
  padding: 40rpx;
  margin-bottom: 30rpx;
}

.user-avatar {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  margin-right: 24rpx;
}

.user-info {
  flex: 1;
}

.user-nickname {
  display: block;
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 10rpx;
}

.user-tip {
  font-size: 26rpx;
  color: #999;
}

.menu-section {
  background: #fff;
  border-radius: 16rpx;
  margin-bottom: 20rpx;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 30rpx 24rpx;
  border-bottom: 1rpx solid #f5f5f5;
}

.menu-item:last-child {
  border-bottom: none;
}

.menu-icon {
  font-size: 36rpx;
  margin-right: 20rpx;
}

.menu-text {
  flex: 1;
  font-size: 28rpx;
  color: #333;
}

.menu-arrow {
  font-size: 28rpx;
  color: #ccc;
}
</style>
