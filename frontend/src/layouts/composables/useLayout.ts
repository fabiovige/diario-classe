import { reactive, computed, toRefs } from 'vue'

const layoutConfig = reactive({
  menuMode: 'static' as 'static' | 'overlay',
})

const layoutState = reactive({
  staticMenuInactive: false,
  overlayMenuActive: false,
  mobileMenuActive: false,
  menuHoverActive: false,
  activePath: null as string | null,
})

export function useLayout() {
  function isDesktop() {
    return window.innerWidth > 991
  }

  function toggleMenu() {
    if (isDesktop()) {
      layoutState.staticMenuInactive = !layoutState.staticMenuInactive
      return
    }
    layoutState.mobileMenuActive = !layoutState.mobileMenuActive
  }

  function hideMobileMenu() {
    layoutState.mobileMenuActive = false
    layoutState.menuHoverActive = false
  }

  function setActivePath(path: string | null) {
    layoutState.activePath = path
  }

  const containerClass = computed(() => ({
    'layout-static': layoutConfig.menuMode === 'static',
    'layout-static-inactive': layoutState.staticMenuInactive && layoutConfig.menuMode === 'static',
    'layout-mobile-active': layoutState.mobileMenuActive,
  }))

  return {
    layoutConfig,
    layoutState,
    containerClass,
    ...toRefs(layoutState),
    toggleMenu,
    hideMobileMenu,
    isDesktop,
    setActivePath,
  }
}
