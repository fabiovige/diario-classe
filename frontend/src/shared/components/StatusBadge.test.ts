import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StatusBadge from './StatusBadge.vue'

const TagStub = {
  name: 'Tag',
  props: ['value', 'severity'],
  template: '<span :class="`p-tag p-tag-${severity}`">{{ value }}</span>',
}

describe('StatusBadge', () => {
  const mountBadge = (props: { status: string; label?: string }) =>
    mount(StatusBadge, {
      props,
      global: {
        stubs: { Tag: TagStub },
      },
    })

  it('renders the label text when provided', () => {
    const wrapper = mountBadge({ status: 'active', label: 'Ativo' })
    expect(wrapper.text()).toContain('Ativo')
  })

  it('renders status as label when no label prop', () => {
    const wrapper = mountBadge({ status: 'active' })
    expect(wrapper.text()).toContain('active')
  })

  it('applies success severity for active status', () => {
    const wrapper = mountBadge({ status: 'active' })
    expect(wrapper.find('.p-tag-success').exists()).toBe(true)
  })

  it('applies danger severity for blocked status', () => {
    const wrapper = mountBadge({ status: 'blocked' })
    expect(wrapper.find('.p-tag-danger').exists()).toBe(true)
  })

  it('applies danger severity for absent status', () => {
    const wrapper = mountBadge({ status: 'absent' })
    expect(wrapper.find('.p-tag-danger').exists()).toBe(true)
  })

  it('applies warn severity for pending status', () => {
    const wrapper = mountBadge({ status: 'pending' })
    expect(wrapper.find('.p-tag-warn').exists()).toBe(true)
  })

  it('applies info severity for validated status', () => {
    const wrapper = mountBadge({ status: 'validated' })
    expect(wrapper.find('.p-tag-info').exists()).toBe(true)
  })

  it('applies secondary severity for transferred status', () => {
    const wrapper = mountBadge({ status: 'transferred' })
    expect(wrapper.find('.p-tag-secondary').exists()).toBe(true)
  })

  it('applies secondary severity for unknown status', () => {
    const wrapper = mountBadge({ status: 'something_unknown' })
    expect(wrapper.find('.p-tag-secondary').exists()).toBe(true)
  })
})
