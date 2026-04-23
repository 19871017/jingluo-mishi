<template>
  <view class="publish-form card">
    <view class="intro-block">
      <text class="intro-title">发起交流</text>
      <text class="intro-desc">分享踩店体验、剧本看法、店铺运营经验或组局话题</text>
    </view>
    <input class="field" v-model="form.title" placeholder="请输入帖子标题" />
    <textarea class="field area" v-model="form.description" placeholder="请输入交流内容" />
    <button class="primary-btn" @click="submit">发布话题</button>
  </view>
</template>

<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['submit'])
const form = reactive({
  type: 'discussion',
  title: '',
  description: '',
  price: 0,
})

function submit() {
  emit('submit', { ...form, price: 0 })
}
</script>

<style scoped>
.publish-form {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
  padding: 24rpx;
}

.intro-block {
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.intro-title {
  font-size: 32rpx;
  font-weight: 800;
  color: #1f2937;
}

.intro-desc {
  font-size: 22rpx;
  color: #9ca3af;
}

.field {
  width: 100%;
  min-height: 84rpx;
  padding: 20rpx;
  border-radius: 16rpx;
  background: #f9fafb;
}

.area {
  height: 180rpx;
}
</style>
