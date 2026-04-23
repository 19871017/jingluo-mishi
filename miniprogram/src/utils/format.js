export function formatPrice(value) {
  const amount = Number(value || 0)
  if (Number.isNaN(amount)) return '¥0'
  return `¥${amount.toLocaleString('zh-CN')}`
}

export function formatScriptPrice(item, fallback = '面议') {
  const price = Number(item?.price_tier1 || item?.authorization_price || item?.price || item?.min_price || 0)
  if (price > 0) {
    return `￥${price}起`
  }

  const priceRange = item?.price_range
  if (priceRange) {
    const min = String(priceRange).match(/¥?(\d+)/)
    return min ? `¥${min[1]}起` : priceRange
  }

  return fallback
}

export function joinPlayers(min, max) {
  return `${min}-${max} 人`
}

export function safeArray(value) {
  return Array.isArray(value) ? value : []
}
