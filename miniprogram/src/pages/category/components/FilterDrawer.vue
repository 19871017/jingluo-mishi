<template>
  <view v-if="visible" class="drawer-root">
    <view class="drawer-mask" @click="close"></view>
    <view class="drawer-panel">
      <view class="drawer-handle"></view>
      <view class="drawer-header">
        <text class="drawer-title">筛选条件</text>
        <text class="drawer-close" @click="close">关闭</text>
      </view>

      <scroll-view scroll-y class="drawer-content">
        <view class="search-section">
          <text class="section-title">主题搜索</text>
          <input v-model="draft.keyword" class="search-input" placeholder="请输入主题名称" maxlength="40" />
        </view>

        <view v-for="section in sections" :key="section.key" class="filter-section">
          <view class="section-head">
            <text class="section-title">{{ section.label }}</text>
            <text v-if="sectionSelectedCount(section)" class="section-count">已选 {{ sectionSelectedCount(section) }}</text>
          </view>

          <view v-if="section.type === 'provinceCitySelect'" class="province-city-block">
            <input
              :value="citySearchMap[section.key] || ''"
              class="search-input city-search-input"
              placeholder="搜索城市名"
              maxlength="20"
              @input="updateCitySearch(section.key, $event.detail.value)"
            />

            <view v-if="!(citySearchMap[section.key] || '').trim()" class="search-empty-tip">
              输入城市名后再选择，避免一次显示全国城市影响体验
            </view>

            <view v-else class="tag-grid city-result-grid">
              <text
                v-for="city in getMatchedCities(section)"
                :key="city"
                class="tag-item"
                :class="{ active: isSelected(section, city) }"
                @click="toggleOption(section, city)"
              >
                {{ city }}
              </text>
            </view>
          </view>

          <view v-else-if="section.type === 'tagGroup'" class="tag-grid">
            <text
              v-for="option in section.options"
              :key="option"
              class="tag-item"
              :class="{ active: isSelected(section, option) }"
              @click="toggleOption(section, option)"
            >
              {{ option }}
            </text>
          </view>

          <view v-else-if="section.type === 'rangeInput'" class="range-block">
            <view v-if="section.presets?.length" class="tag-grid">
              <text
                v-for="preset in section.presets"
                :key="preset"
                class="tag-item"
                :class="{ active: draft[section.presetKey] === preset }"
                @click="selectPreset(section, preset)"
              >
                {{ preset }}
              </text>
            </view>
            <view class="range-input-row">
              <input
                :value="draft[section.minKey]"
                class="range-input"
                type="number"
                :placeholder="section.minPlaceholder"
                @input="updateNumber(section.minKey, $event.detail.value, section.presetKey)"
              />
              <text class="range-separator">-</text>
              <input
                :value="draft[section.maxKey]"
                class="range-input"
                type="number"
                :placeholder="section.maxPlaceholder"
                @input="updateNumber(section.maxKey, $event.detail.value, section.presetKey)"
              />
            </view>
          </view>
        </view>
        <view class="drawer-spacer"></view>
      </scroll-view>

      <view class="drawer-footer">
        <button class="footer-btn reset" @click="resetDraft">重置</button>
        <button class="footer-btn confirm" @click="confirm">确定{{ totalSelectedCount ? `(${totalSelectedCount})` : '' }}</button>
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import { defaultFilterState } from '../filterConfig'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  sections: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: Object,
    default: () => ({ ...defaultFilterState }),
  },
})

const emit = defineEmits(['close', 'apply'])

const draft = reactive({ ...defaultFilterState })
const citySearchMap = reactive({})

const totalSelectedCount = computed(() => {
  return Object.entries(draft).reduce((count, [, value]) => {
    if (Array.isArray(value)) return count + (value.length ? 1 : 0)
    return count + (value !== '' && value !== null && value !== undefined ? 1 : 0)
  }, 0)
})

watch(
  () => props.visible,
  (visible) => {
    if (visible) {
      Object.assign(draft, cloneState(props.modelValue))
      initializeSearchState()
    }
  },
  { immediate: true }
)

function cloneState(state) {
  return JSON.parse(JSON.stringify({ ...defaultFilterState, ...state }))
}

function isSelected(section, option) {
  const current = draft[section.key]
  if (section.mode === 'multiple') {
    return Array.isArray(current) && current.includes(option)
  }
  return current === option
}

function sectionSelectedCount(section) {
  if (section.type === 'rangeInput') {
    let count = 0
    if (section.presetKey && draft[section.presetKey]) count += 1
    if (section.minKey && draft[section.minKey]) count += 1
    if (section.maxKey && draft[section.maxKey]) count += 1
    return count
  }

  const current = draft[section.key]
  if (Array.isArray(current)) return current.length
  return current ? 1 : 0
}

function toggleOption(section, option) {
  if (section.mode === 'multiple') {
    const current = Array.isArray(draft[section.key]) ? [...draft[section.key]] : []
    draft[section.key] = current.includes(option) ? current.filter((item) => item !== option) : [...current, option]
    return
  }
  draft[section.key] = draft[section.key] === option ? '' : option
}

function updateNumber(key, value, presetKey = '') {
  draft[key] = String(value || '').replace(/\D+/g, '')
  if (presetKey) {
    draft[presetKey] = ''
  }
}

function selectPreset(section, preset) {
  draft[section.presetKey] = draft[section.presetKey] === preset ? '' : preset
  draft[section.minKey] = ''
  draft[section.maxKey] = ''
}

function resetDraft() {
  Object.assign(draft, cloneState(defaultFilterState))
  initializeSearchState()
}

function confirm() {
  if (!validateRange('playersMin', 'playersMax', '人数范围')) return
  if (!validateRange('priceMin', 'priceMax', '授权价格')) return
  if (!validateRange('areaMin', 'areaMax', '面积')) return
  if (!validateRange('roomCountMin', 'roomCountMax', '房间数量')) return
  if (!validateRange('rotationMin', 'rotationMax', '滚场数量')) return
  if (!validateRange('npcMin', 'npcMax', 'NPC 数量')) return
  if (!validateRange('corridorCountMin', 'corridorCountMax', '走廊数量')) return
  emit('apply', cloneState(draft))
}

function validateRange(minKey, maxKey, label) {
  const min = Number(draft[minKey] || 0)
  const max = Number(draft[maxKey] || 0)
  if (draft[minKey] && draft[maxKey] && min > max) {
    uni.showToast({ title: `${label}最小值不能大于最大值`, icon: 'none' })
    return false
  }
  return true
}

function close() {
  emit('close')
}

function initializeSearchState() {
  for (const key of Object.keys(citySearchMap)) {
    delete citySearchMap[key]
  }
  for (const section of props.sections) {
    if (section.type !== 'provinceCitySelect') continue
    citySearchMap[section.key] = ''
  }
}

function updateCitySearch(sectionKey, value) {
  citySearchMap[sectionKey] = String(value || '').trim()
}

function getMatchedCities(section) {
  const keyword = normalizeText(citySearchMap[section.key] || '')
  const groups = Array.isArray(section.groups) ? section.groups : []

  if (!keyword) {
    return []
  }

  return groups
    .flatMap((group) => (group.cities || []).map((city) => ({ province: group.province, city })))
    .filter((item) => normalizeText(item.city).includes(keyword))
    .slice(0, 80)
    .map((item) => item.city)
}

function normalizeText(value) {
  return String(value || '').replace(/\s+/g, '').toLowerCase()
}
</script>

<style scoped>
.drawer-root {
  position: fixed;
  inset: 0;
  z-index: 50;
}

.drawer-mask {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
}

.drawer-panel {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 78vh;
  border-radius: 28rpx 28rpx 0 0;
  background: #ffffff;
  overflow: hidden;
}

.drawer-handle {
  width: 84rpx;
  height: 8rpx;
  margin: 18rpx auto 0;
  border-radius: 999rpx;
  background: #e5e7eb;
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 22rpx 24rpx;
}

.drawer-title {
  font-size: 32rpx;
  font-weight: 800;
  color: #1f2937;
}

.drawer-close {
  font-size: 24rpx;
  color: #9ca3af;
}

.drawer-content {
  height: calc(78vh - 170rpx);
  padding: 0 24rpx;
}

.search-section,
.filter-section {
  margin-bottom: 28rpx;
}

.section-title {
  display: block;
  font-size: 26rpx;
  font-weight: 700;
  color: #374151;
}

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12rpx;
  margin-bottom: 14rpx;
}

.section-count {
  flex-shrink: 0;
  font-size: 20rpx;
  color: #f97316;
  background: #fff1de;
  border-radius: 999rpx;
  padding: 6rpx 12rpx;
}

.search-input,
.range-input {
  height: 80rpx;
  padding: 0 18rpx;
  border-radius: 16rpx;
  background: #f8fafc;
  font-size: 24rpx;
}

.province-city-block {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.city-search-input {
  margin-bottom: 4rpx;
}

.search-empty-tip {
  padding: 20rpx 18rpx;
  border-radius: 16rpx;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 22rpx;
}

.city-result-grid {
  margin-top: 4rpx;
}

.tag-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12rpx;
}

.tag-item {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 72rpx;
  padding: 0 10rpx;
  border-radius: 16rpx;
  background: #ffffff;
  color: #4b5563;
  font-size: 22rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tag-item.active {
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
  box-shadow: 0 10rpx 20rpx rgba(255, 138, 31, 0.18);
}

.range-block {
  display: flex;
  flex-direction: column;
  gap: 14rpx;
}

.range-input-row {
  display: flex;
  align-items: center;
  gap: 12rpx;
}

.range-input {
  flex: 1;
}

.range-separator {
  color: #94a3b8;
}

.drawer-spacer {
  height: 24rpx;
}

.drawer-footer {
  display: flex;
  gap: 16rpx;
  padding: 20rpx 24rpx calc(20rpx + env(safe-area-inset-bottom));
  border-top: 1rpx solid #f1f5f9;
  background: rgba(255, 255, 255, 0.96);
}

.footer-btn {
  flex: 1;
  height: 84rpx;
  border-radius: 20rpx;
  font-size: 28rpx;
  font-weight: 700;
  border: none;
}

.footer-btn.reset {
  background: #f8fafc;
  color: #475569;
}

.footer-btn.confirm {
  background: linear-gradient(135deg, #ffb24c, #ff7b00);
  color: #ffffff;
}
</style>
