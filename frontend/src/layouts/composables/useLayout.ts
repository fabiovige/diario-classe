import { reactive, computed, toRefs } from 'vue'

const layoutState = reactive({
  staticMenuInactive: false,
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
    'layout-static-inactive': layoutState.staticMenuInactive,
    'layout-mobile-active': layoutState.mobileMenuActive,
  }))

  return {
    layoutState,
    containerClass,
    ...toRefs(layoutState),
    toggleMenu,
    hideMobileMenu,
    isDesktop,
    setActivePath,
  }
}
