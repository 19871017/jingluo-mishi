export function syncCustomTabBar(index) {
  const currentPage = getCurrentPages().slice(-1)[0]
  if (!currentPage || typeof currentPage.getTabBar !== 'function') return
  const tabBar = currentPage.getTabBar()
  if (tabBar && typeof tabBar.setSelected === 'function') {
    tabBar.setSelected(index)
  }
}
