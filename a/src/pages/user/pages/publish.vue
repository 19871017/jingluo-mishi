<template>
  <view class="publish-page">
    <view class="form-section">
      <view class="form-item">
        <text class="form-label">类型</text>
        <radio-group @change="onTypeChange">
          <label><radio value="sell" checked />出售</label>
          <label class="ml-20"><radio value="buy" />求购</label>
        </radio-group>
      </view>

      <view class="form-item">
        <text class="form-label">标题</text>
        <input v-model="form.title" class="form-input" placeholder="请输入标题" />
      </view>

      <view class="form-item">
        <text class="form-label">描述</text>
        <textarea v-model="form.description" class="form-textarea" placeholder="请输入描述" />
      </view>

      <view class="form-item">
        <text class="form-label">价格</text>
        <input v-model="form.price" class="form-input" type="digit" placeholder="请输入价格（元）" />
      </view>

      <view class="form-item">
        <text class="form-label">图片</text>
        <view class="image-list">
          <view v-for="(img, index) in form.images" :key="index" class="image-item">
            <image :src="img" mode="aspectFill" />
            <view class="image-remove" @click="removeImage(index)">×</view>
          </view>
          <view class="image-add" @click="chooseImage">+</view>
        </view>
      </view>
    </view>

    <button class="submit-btn" @click="submit">发布</button>
  </view>
</template>

<script>
import { marketApi } from '../../services/api'

export default {
  data() {
    return {
      form: {
        type: 'sell',
        title: '',
        description: '',
        price: '',
        images: []
      }
    }
  },
  methods: {
    onTypeChange(e) {
      this.form.type = e.detail.value
    },
    chooseImage() {
      uni.chooseImage({
        count: 9 - this.form.images.length,
        success: (res) => {
          this.form.images = [...this.form.images, ...res.tempFilePaths]
        }
      })
    },
    removeImage(index) {
      this.form.images.splice(index, 1)
    },
    async submit() {
      if (!this.form.title.trim()) {
        uni.showToast({ title: '请输入标题', icon: 'none' })
        return
      }

      try {
        await marketApi.createListing(this.form)
        uni.showToast({ title: '发布成功', icon: 'success' })
        setTimeout(() => {
          uni.navigateBack()
        }, 1500)
      } catch (e) {
        console.error(e)
        uni.showToast({ title: '发布失败', icon: 'none' })
      }
    }
  }
}
</script>

<style scoped>
.publish-page {
  padding: 24rpx;
}

.form-section {
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 30rpx;
}

.form-item {
  margin-bottom: 24rpx;
}

.form-label {
  display: block;
  font-size: 28rpx;
  color: #333;
  margin-bottom: 10rpx;
}

.form-input {
  height: 80rpx;
  padding: 0 20rpx;
  background: #f5f5f5;
  border-radius: 8rpx;
  font-size: 28rpx;
}

.form-textarea {
  width: 100%;
  height: 200rpx;
  padding: 20rpx;
  background: #f5f5f5;
  border-radius: 8rpx;
  font-size: 28rpx;
}

.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 16rpx;
}

.image-item {
  position: relative;
  width: 200rpx;
  height: 200rpx;
}

.image-item image {
  width: 100%;
  height: 100%;
  border-radius: 8rpx;
}

.image-remove {
  position: absolute;
  top: -16rpx;
  right: -16rpx;
  width: 40rpx;
  height: 40rpx;
  line-height: 40rpx;
  text-align: center;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  border-radius: 50%;
  font-size: 28rpx;
}

.image-add {
  width: 200rpx;
  height: 200rpx;
  line-height: 200rpx;
  text-align: center;
  background: #f5f5f5;
  border-radius: 8rpx;
  font-size: 60rpx;
  color: #999;
}

.ml-20 {
  margin-left: 40rpx;
}

.submit-btn {
  width: 100%;
  height: 88rpx;
  line-height: 88rpx;
  background: #1890ff;
  color: #fff;
  border-radius: 44rpx;
  font-size: 32rpx;
}
</style>
