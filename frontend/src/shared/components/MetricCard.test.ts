import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MetricCard from './MetricCard.vue'

describe('MetricCard', () => {
  it('renders title, value, and label', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Total Alunos',
        value: 150,
        label: 'matriculados',
      },
    })

    expect(wrapper.text()).toContain('Total Alunos')
    expect(wrapper.text()).toContain('150')
    expect(wrapper.text()).toContain('matriculados')
  })

  it('shows loading state when loading is true', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Total',
        value: 0,
        label: 'items',
        loading: true,
      },
    })

    const divs = wrapper.findAll('div')
    const loadingDiv = divs.find(d => d.text() === '...')
    expect(loadingDiv).toBeDefined()
    expect(loadingDiv!.text()).toBe('...')
  })

  it('shows value when loading is false', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Total',
        value: 42,
        label: 'items',
        loading: false,
      },
    })

    expect(wrapper.text()).not.toContain('...')
    const valueDiv = wrapper.findAll('div').find(d => d.text() === '42' && d.attributes('style'))
    expect(valueDiv).toBeDefined()
    expect(valueDiv!.text()).toBe('42')
  })

  it('applies custom color to icon and value', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Test',
        value: 10,
        label: 'test',
        color: '#ff0000',
      },
    })

    const icon = wrapper.find('i')
    expect(icon.attributes('style')).toContain('color: rgb(255, 0, 0)')

    const valueDiv = wrapper.findAll('div').find(d => d.text() === '10' && d.attributes('style'))
    expect(valueDiv).toBeDefined()
    expect(valueDiv!.attributes('style')).toContain('color: rgb(255, 0, 0)')
  })

  it('uses default icon class', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Test',
        value: 10,
        label: 'test',
      },
    })

    const icon = wrapper.find('i')
    expect(icon.classes()).toContain('pi')
    expect(icon.classes()).toContain('pi-chart-bar')
  })

  it('uses custom icon class', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Test',
        value: 10,
        label: 'test',
        icon: 'pi pi-users',
      },
    })

    const icon = wrapper.find('i')
    expect(icon.classes()).toContain('pi')
    expect(icon.classes()).toContain('pi-users')
  })
})
