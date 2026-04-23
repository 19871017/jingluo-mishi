<template>
  <view class="app">
    <router-view />
  </view>
</template>

<script>
export default {
  onLaunch() {
    console.log('App Launch')
  },
  onShow() {
    console.log('App Show')
  },
  onHide() {
    console.log('App Hide')
  }
}
</script>

<style>
@import './src/static/styles/common.css';

.app {
  width: 100%;
  min-height: 100vh;
  background-color: #f5f5f5;
}
</style>
