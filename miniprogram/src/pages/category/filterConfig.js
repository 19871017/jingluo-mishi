import { SCRIPT_TAXONOMY } from '../../constants/taxonomy'

export const defaultFilterState = {
  keyword: '',
  authCities: [],
  priceMin: '',
  priceMax: '',
  players: [],
  playersMin: '',
  playersMax: '',
  types: [],
  horrorLevel: '',
  difficulty: '',
  roomSize: '',
  features: [],
  areaPreset: '',
  areaMin: '',
  areaMax: '',
  roomCountMin: '',
  roomCountMax: '',
  rotationMin: '',
  rotationMax: '',
  npcMin: '',
  npcMax: '',
  durationRange: '',
  corridorCountMin: '',
  corridorCountMax: '',
  suitablePlayers: [],
  authStatus: '',
  authServices: [],
  authorizedCities: [],
}

const countOptions = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '10+']
const featureOptions = [
  '角色扮演',
  '武侠',
  '全息投影',
  '械战',
  '韩式',
  '玄幻',
  'VR',
  '有换装',
  '二次元',
  '欧式',
  '穿越',
  '情感',
  '多支线',
  '运动量大',
  '魔法',
  '机械',
  '科幻',
  '单人任务',
  '港风',
  '日式',
  '追逐',
  '对抗',
  '校园',
  '无换装',
  '大型机械',
  '悬疑',
  '美式',
  '有剧情',
  '宫廷',
  '古风',
]

export const categoryFilterSections = [
  {
    key: 'authCities',
    label: '可授权城市',
    type: 'provinceCitySelect',
    mode: 'multiple',
    groups: [],
  },
  {
    key: 'price',
    label: '授权价格',
    type: 'rangeInput',
    minKey: 'priceMin',
    maxKey: 'priceMax',
    minPlaceholder: '自定义最低价格',
    maxPlaceholder: '自定义最高价格',
  },
  {
    key: 'players',
    label: '人数范围',
    type: 'rangeInput',
    minKey: 'playersMin',
    maxKey: 'playersMax',
    minPlaceholder: '最少人数，例如 3',
    maxPlaceholder: '最多人数，例如 5',
  },
  {
    key: 'types',
    label: '类型',
    type: 'tagGroup',
    mode: 'multiple',
    options: SCRIPT_TAXONOMY,
  },
  {
    key: 'horrorLevel',
    label: '恐怖程度',
    type: 'tagGroup',
    mode: 'single',
    options: ['重恐', '中恐', '微恐', '非恐'],
  },
  {
    key: 'difficulty',
    label: '难度',
    type: 'tagGroup',
    mode: 'single',
    options: ['烧脑', '中等难度', '简单'],
  },
  {
    key: 'roomSize',
    label: '密室大小',
    type: 'tagGroup',
    mode: 'single',
    options: ['巨型密室', '大型密室', '中型密室', '小型密室'],
  },
  {
    key: 'features',
    label: '特色标签',
    type: 'tagGroup',
    mode: 'multiple',
    options: featureOptions,
  },
  {
    key: 'area',
    label: '面积',
    type: 'rangeInput',
    presetKey: 'areaPreset',
    minKey: 'areaMin',
    maxKey: 'areaMax',
    minPlaceholder: '自定义最小面积',
    maxPlaceholder: '自定义最大面积',
    presets: ['0-10㎡', '11-30㎡', '31-60㎡', '61-90㎡', '91-120㎡', '121-150㎡', '151-200㎡', '200㎡+'],
  },
  {
    key: 'roomCount',
    label: '房间数量',
    type: 'rangeInput',
    minKey: 'roomCountMin',
    maxKey: 'roomCountMax',
    minPlaceholder: '最少房间数',
    maxPlaceholder: '最多房间数',
  },
  {
    key: 'rotation',
    label: '滚场',
    type: 'rangeInput',
    minKey: 'rotationMin',
    maxKey: 'rotationMax',
    minPlaceholder: '最少滚场数',
    maxPlaceholder: '最多滚场数',
  },
  {
    key: 'npcs',
    label: 'NPC 数量',
    type: 'rangeInput',
    minKey: 'npcMin',
    maxKey: 'npcMax',
    minPlaceholder: '最少 NPC 数',
    maxPlaceholder: '最多 NPC 数',
  },
  {
    key: 'durationRange',
    label: '游戏时长',
    type: 'tagGroup',
    mode: 'single',
    options: ['0-30分钟', '31-60分钟', '61-90分钟', '91-120分钟'],
  },
  {
    key: 'corridorCounts',
    label: '走廊数量',
    type: 'rangeInput',
    minKey: 'corridorCountMin',
    maxKey: 'corridorCountMax',
    minPlaceholder: '最少走廊数',
    maxPlaceholder: '最多走廊数',
  },
  {
    key: 'suitablePlayers',
    label: '适合玩家',
    type: 'tagGroup',
    mode: 'multiple',
    options: ['情侣约会', '学生组队', '团建聚会', '新手玩家', '硬核解谜', '戏精演绎', '亲子家庭', '高玩挑战', '恐怖爱好者', '社牛玩家', '微恐体验', '剧情控玩家'],
  },
  {
    key: 'authStatus',
    label: '授权状态',
    type: 'tagGroup',
    mode: 'single',
    options: ['不可授权', '可授权'],
  },
  {
    key: 'authServices',
    label: '授权服务',
    type: 'tagGroup',
    mode: 'multiple',
    options: ['主题详情', '主题流程', '图片资料', '音频资料', '平面图纸', '机关清单', '店员培训', '强弱电图纸', '海报', '宣传视频'],
  },
  {
    key: 'authorizedCities',
    label: '已授权城市',
    type: 'provinceCitySelect',
    mode: 'multiple',
    groups: [],
  },
]

export function buildFilterSections(dynamic = {}, cityGroups = []) {
  return categoryFilterSections.map((section) => {
    if (section.type === 'provinceCitySelect') {
      return {
        ...section,
        groups: cityGroups,
      }
    }

    if (section.key === 'features') {
      return section
    }

    if (section.key === 'types' && Array.isArray(dynamic.types) && dynamic.types.length) {
      const mergedOptions = Array.from(new Set([
        ...section.options,
        ...dynamic.types.filter((item) => isCleanLabel(item)),
      ]))

      return {
        ...section,
        options: mergedOptions,
      }
    }

    if (section.key && Array.isArray(dynamic[section.key]) && dynamic[section.key].length) {
      const cleanOptions = dynamic[section.key].filter((item) => isCleanLabel(item))
      if (!cleanOptions.length) {
        return section
      }
      return {
        ...section,
        options: cleanOptions,
      }
    }

    return section
  })
}

function isCleanLabel(value) {
  const text = String(value || '').trim()
  if (!text) return false
  if (text.includes('?') || text.includes('�')) return false
  return true
}
