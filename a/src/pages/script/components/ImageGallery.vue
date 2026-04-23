<template>
  <swiper class="image-gallery" :indicator-dots="true" :circular="true">
    <swiper-item v-for="(img, index) in images" :key="index">
      <image class="gallery-image" :src="img" mode="aspectFill" @click="preview(index)" />
    </swiper-item>
  </swiper>
</template>

<script>
export default {
  props: {
    images: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    preview(index) {
      uni.previewImage({
        urls: this.images,
        current: index
      })
    }
  }
}
</script>

<style scoped>
.image-gallery {
  width: 100%;
  height: 500rpx;
}

.gallery-image {
  width: 100%;
  height: 100%;
}
</style>
