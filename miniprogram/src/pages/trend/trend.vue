<template>
  <scroll-view scroll-y class="trend-page" :refresher-enabled="true" :refresher-triggered="refreshing" @refresherrefresh="onRefresh">
    <view class="trend-shell">
      <view class="hero-card">
        <view class="hero-glow"></view>
        <view class="hero-top">
          <view class="hero-copy">
            <text class="hero-caption">Trend Intelligence</text>
            <text class="hero-title">主题趋势周报</text>
            <text class="hero-range">{{ rangeText }}</text>
          </view>
          <view class="hero-badge">运营分析</view>
        </view>

        <view class="hero-highlight">
          <view class="highlight-number">{{ report.overview.total_scripts }}</view>
          <view class="highlight-copy">
            <text class="highlight-title">周期内剧本样本</text>
            <text class="highlight-sub">总浏览 {{ formatNumber(report.overview.total_views) }} · 总喜欢 {{ formatNumber(report.overview.total_likes) }}</text>
          </view>
        </view>

        <view class="hero-tabs">
          <text v-for="item in periodOptions" :key="item.value" class="hero-tab" :class="{ active: activePeriod === item.value }" @click="switchPeriod(item.value)">{{ item.label }}</text>
        </view>
      </view>

      <view class="stats-grid">
        <view v-for="item in metricCards" :key="item.label" class="stat-card card">
          <text class="stat-label">{{ item.label }}</text>
          <text class="stat-value">{{ item.value }}</text>
          <text class="stat-tip">{{ item.tip }}</text>
        </view>
      </view>

      <view class="section-card section-card-accent">
        <view class="section-head">
          <view>
            <text class="section-title">{{ trendSectionTitle }}</text>
            <text class="section-subtitle">{{ trendSectionSubtitle }}</text>
          </view>
          <text class="section-total">{{ report.overview.total_scripts }} 条</text>
        </view>
        <view class="trend-insights">
          <view class="insight-chip">
            <text class="insight-label">峰值日</text>
            <text class="insight-value">{{ peakDay.label }}</text>
          </view>
          <view class="insight-chip">
            <text class="insight-label">单日峰值</text>
            <text class="insight-value">{{ peakDay.value }}</text>
          </view>
          <view class="insight-chip">
            <text class="insight-label">日均样本</text>
            <text class="insight-value">{{ averageDaily }}</text>
          </view>
        </view>
        <view class="line-chart-card">
          <view class="line-grid">
            <view v-for="item in gridLevels" :key="item" class="line-grid-row">
              <text class="line-grid-text">{{ item }}</text>
            </view>
          </view>
          <view class="line-chart">
            <view v-for="item in trendBars" :key="item.name" class="line-item">
              <text class="line-value">{{ item.value }}</text>
              <view class="line-bar-wrap">
                <view class="line-bar" :style="{ height: `${item.height}%` }"></view>
              </view>
              <text class="line-label">{{ item.label }}</text>
            </view>
          </view>
        </view>
      </view>

      <view class="section-card">
        <view class="section-head section-head-tight">
          <view>
            <text class="section-title">城市分布</text>
            <text class="section-subtitle">按授权价格城市键位统计热度覆盖范围</text>
          </view>
          <text class="section-total">{{ cityTotal }}</text>
        </view>

        <view v-if="report.city_distribution.length" class="city-featured">
          <view class="city-badge">Top 1</view>
          <text class="city-featured-name">{{ report.city_distribution[0].name }}</text>
          <text class="city-featured-value">{{ report.city_distribution[0].value }} 次覆盖</text>
        </view>

        <view class="rank-list compact-list">
          <view v-for="(item, index) in report.city_distribution.slice(0, 8)" :key="item.name" class="rank-row rank-row-large">
            <text class="rank-index">{{ index + 1 }}</text>
            <text class="rank-name">{{ item.name }}</text>
            <view class="rank-bar-track">
              <view class="rank-bar-fill" :style="{ width: `${toPercent(item.value, cityMax)}%` }"></view>
            </view>
            <text class="rank-value">{{ item.value }}</text>
          </view>
        </view>
      </view>

      <view class="section-card">
        <view class="section-head section-head-tight">
          <view>
            <text class="section-title">榜单排行</text>
            <text class="section-subtitle">品牌与剧本两个视角快速查看头部集中度</text>
          </view>
        </view>
        <view class="split-grid">
          <view class="mini-panel mini-panel-strong">
            <view class="mini-panel-head">
              <text class="mini-title">品牌榜</text>
              <text class="mini-pill">Top 5</text>
            </view>
            <view v-for="(item, index) in report.rankings.brands.slice(0, 5)" :key="item.name" class="mini-rank-row">
              <text class="mini-rank-name">{{ index + 1 }}. {{ item.name }}</text>
              <text class="mini-rank-value">{{ item.value }}</text>
            </view>
          </view>
          <view class="mini-panel">
            <view class="mini-panel-head">
              <text class="mini-title">剧本榜</text>
              <text class="mini-pill mini-pill-soft">Top 5</text>
            </view>
            <view v-for="(item, index) in report.rankings.categories.slice(0, 5)" :key="item.name" class="mini-rank-row">
              <text class="mini-rank-name">{{ index + 1 }}. {{ item.name }}</text>
              <text class="mini-rank-value">{{ item.value }}</text>
            </view>
          </view>
        </view>
      </view>

      <view class="section-card" v-for="chart in donutCharts" :key="chart.title">
        <view class="section-head section-head-tight">
          <view>
            <text class="section-title">{{ chart.title }}</text>
            <text class="section-subtitle">多维样本结构占比分析</text>
          </view>
          <text class="section-total">{{ chart.total }} 样本</text>
        </view>
        <view class="donut-layout">
          <view class="donut-panel">
            <view class="donut" :style="{ background: chart.gradient }">
              <view class="donut-ring"></view>
              <view class="donut-inner">
                <text class="donut-total">样本</text>
                <text class="donut-number">{{ chart.total }}</text>
              </view>
            </view>
          </view>
          <view class="legend-list">
            <view v-for="item in chart.items" :key="item.name" class="legend-row legend-row-rich">
              <view class="legend-left">
                <view class="legend-dot" :style="{ background: item.color }"></view>
                <view class="legend-copy">
                  <text class="legend-name">{{ item.name }}</text>
                  <text class="legend-percent">{{ item.percent }}%</text>
                </view>
              </view>
              <text class="legend-value">{{ item.value }}</text>
            </view>
          </view>
        </view>
      </view>

      <view class="section-card">
        <view class="section-head section-head-tight">
          <view>
            <text class="section-title">标签热度</text>
            <text class="section-subtitle">从类型、品牌、剧本、属性中提取出的高频标签</text>
          </view>
          <text class="section-total">{{ report.tags.length }} 个</text>
        </view>

        <view class="tag-cloud">
          <text v-for="item in tagCloud" :key="item.name" class="tag-chip" :style="item.style">{{ item.name }}</text>
        </view>

        <view class="rank-list compact-list tag-rank-list">
          <view v-for="(item, index) in report.tags.slice(0, 10)" :key="item.name" class="rank-row rank-row-large">
            <text class="rank-index">{{ index + 1 }}</text>
            <text class="rank-name">{{ item.name }}</text>
            <view class="rank-bar-track">
              <view class="rank-bar-fill" :style="{ width: `${toPercent(item.value, tagMax)}%` }"></view>
            </view>
            <text class="rank-value">{{ item.value }}</text>
          </view>
        </view>
      </view>
    </view>
  </scroll-view>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { api } from '../../services/api'

const palette = ['#ff8a1f', '#2f8cff', '#52c7a7', '#ffb648', '#8d6bff', '#48c6ef']

const emptyReport = {
  range: { start_date: '', end_date: '' },
  overview: { total_scripts: 0, active_brands: 0, total_views: 0, total_likes: 0, avg_duration: 0, avg_players: 0 },
  daily_trend: [],
  city_distribution: [],
  rankings: { brands: [], categories: [] },
  distributions: { horror: [], types: [], duration: [], players: [], price: [] },
  tags: [],
}

const report = ref(emptyReport)
const refreshing = ref(false)
const activePeriod = ref('week')
const periodOptions = [
  { label: '周', value: 'week' },
  { label: '月', value: 'month' },
  { label: '累计', value: 'all' },
]

const rangeText = computed(() => `${report.value.range.start_date || '--'} ~ ${report.value.range.end_date || '--'}`)
const cityMax = computed(() => Math.max(...report.value.city_distribution.map((item) => item.value), 1))
const cityTotal = computed(() => report.value.city_distribution.reduce((sum, item) => sum + item.value, 0))
const tagMax = computed(() => Math.max(...report.value.tags.map((item) => item.value), 1))
const trendSectionTitle = computed(() => ({ week: '近 7 日趋势', month: '近 30 日趋势', all: '累计趋势' }[activePeriod.value]))
const trendSectionSubtitle = computed(() => ({ week: '适合看短周期波峰和内容集中上线时间', month: '观察最近一个月的样本分布变化', all: '累计窗口下的整体趋势参考' }[activePeriod.value]))

const metricCards = computed(() => [
  {
    label: '活跃品牌',
    value: formatNumber(report.value.overview.active_brands),
    tip: '覆盖授权品牌数',
  },
  {
    label: '平均时长',
    value: `${report.value.overview.avg_duration} 分钟`,
    tip: '样本平均流程时长',
  },
  {
    label: '平均人数',
    value: `${report.value.overview.avg_players} 人`,
    tip: '建议组局规模',
  },
  {
    label: '总热度',
    value: formatNumber(report.value.overview.total_likes),
    tip: '喜欢总量参考',
  },
])

const trendBars = computed(() => {
  const list = report.value.daily_trend || []
  const max = Math.max(...list.map((item) => item.value), 1)
  const take = activePeriod.value === 'week' ? 7 : activePeriod.value === 'month' ? 10 : 12
  return list.slice(-take).map((item) => ({
    ...item,
    label: item.name.slice(5),
    height: Math.max(10, Math.round((item.value / max) * 100)),
  }))
})

const peakDay = computed(() => {
  const source = report.value.daily_trend || []
  if (!source.length) return { label: '--', value: 0 }
  const top = source.reduce((best, item) => (item.value > best.value ? item : best), source[0])
  return { label: top.name.slice(5), value: top.value }
})

const averageDaily = computed(() => {
  const source = report.value.daily_trend || []
  if (!source.length) return 0
  const total = source.reduce((sum, item) => sum + item.value, 0)
  return Math.round(total / source.length)
})

const gridLevels = computed(() => {
  const max = Math.max(...trendBars.value.map((item) => item.value), 1)
  return [max, Math.round(max * 0.66), Math.round(max * 0.33), 0]
})

const donutCharts = computed(() => {
  const defs = [
    { title: '恐怖程度', items: report.value.distributions.horror || [] },
    { title: '类型', items: report.value.distributions.types || [] },
    { title: '容量大小', items: report.value.distributions.players || [] },
    { title: '时长', items: report.value.distributions.duration || [] },
    { title: '价格区间', items: report.value.distributions.price || [] },
  ]

  return defs.map((chart) => buildDonut(chart.title, chart.items))
})

const tagCloud = computed(() => {
  return (report.value.tags || []).slice(0, 8).map((item, index) => ({
    ...item,
    style: {
      background: index % 3 === 0 ? '#fff1de' : index % 3 === 1 ? '#edf5ff' : '#eefaf6',
      color: index % 3 === 0 ? '#f97316' : index % 3 === 1 ? '#2563eb' : '#0f9f6e',
    },
  }))
})

async function fetchReport() {
  const { startDate, endDate } = buildDateRange(activePeriod.value)
  report.value = await api.getTrendReport(startDate, endDate)
}

async function onRefresh() {
  refreshing.value = true
  await fetchReport()
  refreshing.value = false
  uni.showToast({ title: '刷新成功', icon: 'success' })
}

function formatNumber(value) {
  return Number(value || 0).toLocaleString('zh-CN')
}

function switchPeriod(period) {
  if (activePeriod.value === period) return
  activePeriod.value = period
  fetchReport()
}

function buildDateRange(period) {
  const end = new Date()
  const start = new Date(end)
  if (period === 'week') {
    start.setDate(end.getDate() - 6)
  } else if (period === 'month') {
    start.setDate(end.getDate() - 29)
  } else {
    start.setDate(end.getDate() - 89)
  }

  return {
    startDate: formatDate(start),
    endDate: formatDate(end),
  }
}

function formatDate(date) {
  const year = date.getFullYear()
  const month = `${date.getMonth() + 1}`.padStart(2, '0')
  const day = `${date.getDate()}`.padStart(2, '0')
  return `${year}-${month}-${day}`
}

function toPercent(value, max) {
  if (!max) return 0
  return Math.max(6, Math.round((value / max) * 100))
}

function buildDonut(title, items) {
  const total = items.reduce((sum, item) => sum + item.value, 0)
  if (!total) {
    return {
      title,
      total: 0,
      items: [],
      gradient: 'conic-gradient(#f1f1f1 0deg 360deg)',
    }
  }

  let start = 0
  const enriched = items.slice(0, 5).map((item, index) => {
    const color = palette[index % palette.length]
    const angle = (item.value / total) * 360
    const current = {
      ...item,
      color,
      start,
      end: start + angle,
      percent: Math.round((item.value / total) * 100),
    }
    start += angle
    return current
  })

  const gradient = `conic-gradient(${enriched.map((item) => `${item.color} ${item.start}deg ${item.end}deg`).join(', ')})`

  return {
    title,
    total,
    items: enriched,
    gradient,
  }
}

onMounted(fetchReport)
</script>

<style scoped>
.trend-page {
  height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(255, 181, 72, 0.15), transparent 24%),
    linear-gradient(180deg, #fff7ef 0%, #f6f7fb 22%, #f6f7fb 100%);
}

.trend-shell {
  padding: 24rpx;
}

.hero-card {
  position: relative;
  overflow: hidden;
  padding: 32rpx;
  border-radius: 30rpx;
  background: linear-gradient(135deg, #ffb64a 0%, #ff8914 55%, #ff6a00 100%);
  color: #ffffff;
  box-shadow: 0 16rpx 40rpx rgba(255, 132, 0, 0.18);
}

.hero-glow {
  position: absolute;
  top: -100rpx;
  right: -40rpx;
  width: 260rpx;
  height: 260rpx;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.18);
}

.hero-top {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.hero-copy {
  max-width: 70%;
}

.hero-caption {
  display: block;
  font-size: 20rpx;
  letter-spacing: 1rpx;
  opacity: 0.75;
}

.hero-title {
  display: block;
  margin-top: 10rpx;
  font-size: 42rpx;
  font-weight: 700;
}

.hero-range {
  display: block;
  margin-top: 10rpx;
  font-size: 22rpx;
  opacity: 0.9;
}

.hero-badge {
  position: relative;
  z-index: 1;
  padding: 12rpx 20rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.18);
  font-size: 22rpx;
}

.hero-highlight {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 18rpx;
  margin-top: 28rpx;
  padding: 24rpx;
  border-radius: 22rpx;
  background: rgba(255, 255, 255, 0.16);
  backdrop-filter: blur(8px);
}

.highlight-number {
  min-width: 120rpx;
  font-size: 60rpx;
  font-weight: 700;
  line-height: 1;
}

.highlight-copy {
  flex: 1;
}

.highlight-title {
  display: block;
  font-size: 28rpx;
  font-weight: 600;
}

.highlight-sub {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  opacity: 0.9;
}

.hero-tabs {
  position: relative;
  z-index: 1;
  display: flex;
  gap: 16rpx;
  margin-top: 24rpx;
}

.hero-tab {
  padding: 10rpx 28rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.14);
  font-size: 24rpx;
}

.hero-tab.active {
  background: #ffffff;
  color: #ff8a1f;
  font-weight: 600;
}

.trend-insights {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16rpx;
  margin-bottom: 18rpx;
}

.insight-chip {
  padding: 18rpx;
  border-radius: 18rpx;
  background: #fff4e8;
}

.insight-label {
  display: block;
  font-size: 20rpx;
  color: #9a6b36;
}

.insight-value {
  display: block;
  margin-top: 8rpx;
  font-size: 26rpx;
  font-weight: 700;
  color: #f97316;
}

.line-chart-card {
  position: relative;
  padding: 22rpx 18rpx 10rpx 18rpx;
  border-radius: 20rpx;
  background: linear-gradient(180deg, #ffffff, #fffaf5);
}

.line-grid {
  position: absolute;
  inset: 22rpx 18rpx 44rpx 18rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
}

.line-grid-row {
  position: relative;
  border-top: 1rpx dashed #f3d3b1;
}

.line-grid-text {
  position: absolute;
  top: -16rpx;
  left: 0;
  padding-right: 10rpx;
  background: #fffdfb;
  font-size: 18rpx;
  color: #c08a4d;
}

.stats-grid {
  margin-top: 20rpx;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18rpx;
}

.stat-card {
  padding: 24rpx;
  border: 1rpx solid rgba(255, 138, 31, 0.08);
  border-radius: 20rpx;
  box-shadow: 0 10rpx 24rpx rgba(17, 24, 39, 0.04);
}

.stat-label {
  display: block;
  font-size: 22rpx;
  color: #9ca3af;
}

.stat-value {
  display: block;
  margin-top: 14rpx;
  font-size: 34rpx;
  font-weight: 700;
  color: #1f2937;
}

.stat-tip {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #f97316;
}

.section-card {
  margin-top: 20rpx;
  padding: 24rpx;
  border-radius: 24rpx;
  background: #ffffff;
  box-shadow: 0 12rpx 28rpx rgba(17, 24, 39, 0.04);
}

.section-card-accent {
  background: linear-gradient(180deg, #fffaf5 0%, #ffffff 100%);
}

.section-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24rpx;
  gap: 16rpx;
}

.section-head-tight {
  margin-bottom: 20rpx;
}

.section-title {
  display: block;
  font-size: 32rpx;
  font-weight: 700;
  color: #1f2937;
}

.section-subtitle {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #9ca3af;
}

.section-total {
  padding: 10rpx 18rpx;
  border-radius: 999rpx;
  background: #fff4e7;
  color: #f97316;
  font-size: 22rpx;
  white-space: nowrap;
}

.line-chart {
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12rpx;
  height: 300rpx;
}

.line-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  gap: 10rpx;
  height: 100%;
}

.line-value {
  font-size: 18rpx;
  color: #f97316;
}

.line-bar-wrap {
  display: flex;
  align-items: flex-end;
  width: 100%;
  height: 220rpx;
  padding-top: 10rpx;
  border-bottom: 1rpx solid #f1e0c9;
}

.line-bar {
  width: 100%;
  border-radius: 12rpx 12rpx 0 0;
  background: linear-gradient(180deg, #ffc164 0%, #ff8a1f 100%);
  box-shadow: 0 8rpx 18rpx rgba(255, 138, 31, 0.2);
}

.line-label {
  font-size: 18rpx;
  color: #9ca3af;
}

.city-featured {
  display: flex;
  align-items: center;
  gap: 16rpx;
  margin-bottom: 20rpx;
  padding: 18rpx 20rpx;
  border-radius: 18rpx;
  background: linear-gradient(90deg, #fff6ec, #fffdf9);
}

.city-badge {
  padding: 8rpx 14rpx;
  border-radius: 999rpx;
  background: #ff8a1f;
  color: #ffffff;
  font-size: 20rpx;
}

.city-featured-name {
  font-size: 28rpx;
  font-weight: 700;
  color: #1f2937;
}

.city-featured-value {
  margin-left: auto;
  font-size: 22rpx;
  color: #f97316;
}

.rank-list {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.compact-list {
  gap: 16rpx;
}

.rank-row {
  display: grid;
  align-items: center;
  gap: 14rpx;
}

.rank-row-large {
  grid-template-columns: 40rpx 130rpx 1fr 54rpx;
}

.rank-index {
  font-size: 22rpx;
  font-weight: 700;
  color: #ff8a1f;
  text-align: center;
}

.rank-name {
  font-size: 22rpx;
  color: #4b5563;
}

.rank-bar-track {
  overflow: hidden;
  height: 16rpx;
  border-radius: 999rpx;
  background: #f3f4f6;
}

.rank-bar-fill {
  height: 100%;
  border-radius: 999rpx;
  background: linear-gradient(90deg, #ffb24c, #ff7b00);
}

.rank-value {
  text-align: right;
  font-size: 22rpx;
  color: #6b7280;
}

.split-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 18rpx;
}

.mini-panel {
  padding: 22rpx;
  border-radius: 20rpx;
  background: #fafafa;
}

.mini-panel-strong {
  background: linear-gradient(180deg, #fff7ef, #fafafa);
}

.mini-panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14rpx;
}

.mini-title {
  font-size: 24rpx;
  font-weight: 700;
  color: #374151;
}

.mini-pill {
  padding: 6rpx 12rpx;
  border-radius: 999rpx;
  background: #fff1de;
  color: #f97316;
  font-size: 20rpx;
}

.mini-pill-soft {
  background: #eef4ff;
  color: #2563eb;
}

.mini-rank-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12rpx;
  padding: 10rpx 0;
}

.mini-rank-name,
.mini-rank-value {
  font-size: 22rpx;
  color: #6b7280;
}

.donut-layout {
  display: flex;
  gap: 28rpx;
  align-items: center;
}

.donut-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 240rpx;
}

.donut {
  position: relative;
  width: 224rpx;
  height: 224rpx;
  border-radius: 50%;
}

.donut-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  box-shadow: inset 0 0 0 1rpx rgba(255, 255, 255, 0.6);
}

.donut-inner {
  position: absolute;
  top: 50%;
  left: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 126rpx;
  height: 126rpx;
  border-radius: 50%;
  background: #ffffff;
  transform: translate(-50%, -50%);
  box-shadow: 0 8rpx 18rpx rgba(17, 24, 39, 0.06);
}

.donut-total {
  font-size: 20rpx;
  color: #9ca3af;
}

.donut-number {
  margin-top: 6rpx;
  font-size: 34rpx;
  font-weight: 700;
  color: #1f2937;
}

.legend-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

.legend-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12rpx;
}

.legend-row-rich {
  padding: 10rpx 0;
  border-bottom: 1rpx solid #f5f6f8;
}

.legend-row-rich:last-child {
  border-bottom: 0;
}

.legend-left {
  display: flex;
  align-items: center;
  gap: 10rpx;
}

.legend-copy {
  display: flex;
  flex-direction: column;
  gap: 4rpx;
}

.legend-dot {
  width: 16rpx;
  height: 16rpx;
  border-radius: 50%;
}

.legend-name {
  font-size: 22rpx;
  color: #4b5563;
}

.legend-percent {
  font-size: 20rpx;
  color: #9ca3af;
}

.legend-value {
  font-size: 22rpx;
  color: #6b7280;
}

.tag-cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 14rpx;
  margin-bottom: 22rpx;
}

.tag-chip {
  padding: 12rpx 18rpx;
  border-radius: 999rpx;
  font-size: 22rpx;
  font-weight: 600;
  box-shadow: 0 6rpx 16rpx rgba(17, 24, 39, 0.04);
}

.tag-rank-list {
  margin-top: 6rpx;
}
</style>
