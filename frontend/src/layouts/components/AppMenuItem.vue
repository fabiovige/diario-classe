<script setup lang="ts">
import { computed } from 'vue'
import { useLayout } from '../composables/useLayout'

interface MenuItem {
  label: string
  icon?: string
  to?: string
  path?: string
  items?: MenuItem[]
  visible?: boolean
}

const props = defineProps<{
  item: MenuItem
  root?: boolean
  parentPath?: string | null
}>()

const { layoutState, isDesktop } = useLayout()

const fullPath = computed(() =>
  props.item.path
    ? (props.parentPath ? props.parentPath + props.item.path : props.item.path)
    : null
)

const isActive = computed(() => {
  if (props.item.path) {
    return layoutState.activePath?.startsWith(fullPath.value!) ?? false
  }
  return layoutState.activePath === props.item.to
})

function itemClick(_event: Event, item: MenuItem) {
  if (item.items) {
    if (isActive.value) {
      layoutState.activePath = layoutState.activePath!.replace(item.path!, '')
      return
    }
    layoutState.activePath = fullPath.value
    layoutState.menuHoverActive = true
    return
  }

  layoutState.overlayMenuActive = false
  layoutState.mobileMenuActive = false
  layoutState.menuHoverActive = false
}

function onMouseEnter() {
  if (!isDesktop()) return
  if (!props.root) return
  if (!props.item.items) return
  if (!layoutState.menuHoverActive) return

  layoutState.activePath = fullPath.value
}
</script>

<template>
  <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActive }">
    <div v-if="root && item.items && item.visible !== false" class="layout-menuitem-root-text">{{ item.label }}</div>
    <a
      v-if="(!item.to || item.items) && item.visible !== false"
      href="javascript:void(0)"
      @click="itemClick($event, item)"
      tabindex="0"
      @mouseenter="onMouseEnter"
    >
      <i :class="item.icon" class="layout-menuitem-icon" />
      <span class="layout-menuitem-text">{{ item.label }}</span>
      <i v-if="item.items" class="pi pi-fw pi-angle-down layout-submenu-toggler" />
    </a>
    <router-link
      v-if="item.to && !item.items && item.visible !== false"
      @click="itemClick($event, item)"
      exactActiveClass="active-route"
      tabindex="0"
      :to="item.to"
      @mouseenter="onMouseEnter"
    >
      <i :class="item.icon" class="layout-menuitem-icon" />
      <span class="layout-menuitem-text">{{ item.label }}</span>
    </router-link>
    <Transition v-if="item.items && item.visible !== false" name="layout-submenu">
      <ul v-show="root ? true : isActive" class="layout-submenu">
        <AppMenuItem
          v-for="child in item.items"
          :key="child.label + '_' + (child.to || child.path)"
          :item="child"
          :root="false"
          :parent-path="fullPath"
        />
      </ul>
    </Transition>
  </li>
</template>
