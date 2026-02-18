import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import EmptyState from './EmptyState.vue'

describe('EmptyState', () => {
  it('renders default message', () => {
    const wrapper = mount(EmptyState)
    expect(wrapper.text()).toContain('Nenhum registro encontrado')
  })

  it('renders custom message', () => {
    const wrapper = mount(EmptyState, {
      props: { message: 'Sem dados disponiveis' },
    })
    expect(wrapper.text()).toContain('Sem dados disponiveis')
  })

  it('shows default icon class', () => {
    const wrapper = mount(EmptyState)
    const icon = wrapper.find('i')
    expect(icon.classes()).toContain('pi')
    expect(icon.classes()).toContain('pi-inbox')
  })

  it('shows custom icon class', () => {
    const wrapper = mount(EmptyState, {
      props: { icon: 'pi pi-search' },
    })
    const icon = wrapper.find('i')
    expect(icon.classes()).toContain('pi')
    expect(icon.classes()).toContain('pi-search')
  })

  it('renders slot content', () => {
    const wrapper = mount(EmptyState, {
      slots: {
        default: '<button>Adicionar</button>',
      },
    })
    expect(wrapper.find('button').text()).toBe('Adicionar')
  })
})
