export const formatNumber = (num) => {
  if (num >= 10000) {
    return (num / 10000).toFixed(1) + '万'
  }
  return num.toString()
}

export const formatPrice = (price) => {
  if (!price) return '价格面议'
  if (price >= 10000) {
    return (price / 10000).toFixed(1) + '万'
  }
  return price + '元'
}

export const formatDuration = (minutes) => {
  if (!minutes) return ''
  if (minutes >= 60) {
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return mins > 0 ? `${hours}小时${mins}分钟` : `${hours}小时`
  }
  return `${minutes}分钟`
}

export const formatPlayers = (min, max) => {
  if (min === max) return `${min}人`
  return `${min}-${max}人`
}

export const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}
