<template>
  <swiper class="banner" :indicator-dots="true" :autoplay="true" :interval="3000" :duration="500" circular>
    <swiper-item v-for="item in banners" :key="item.id" @click="onClick(item)">
      <image class="banner-image" :src="item.image" mode="aspectFill" />
    </swiper-item>
  </swiper>
</template>

<script>
export default {
  props: {
    banners: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    onClick(item) {
      if (item.link) {
        if (item.link.startsWith('script/')) {
          const id = item.link.split('/')[1]
          uni.navigateTo({ url: `/pages/script/script-detail?id=${id}` })
        } else if (item.link.startsWith('brand/')) {
          const id = item.link.split('/')[1]
          uni.navigateTo({ url: `/pages/brand/brand-detail?id=${id}` })
        }
      }
    }
  }
}
</script>

<style scoped>
.banner {
  width: 100%;
  height: 320rpx;
}

.banner-image {
  width: 100%;
  height: 100%;
}
</style>
