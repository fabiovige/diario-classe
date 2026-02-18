import { apiGet, apiPost, apiPut } from '@/config/api'
import type { PaginatedData } from '@/types/api'
import type { LessonRecord } from '@/types/class-record'

export const classRecordService = {
  getRecords(params?: Record<string, unknown>): Promise<PaginatedData<LessonRecord>> {
    return apiGet<PaginatedData<LessonRecord>>('lesson-records', params)
  },
  getRecord(id: number): Promise<LessonRecord> {
    return apiGet<LessonRecord>(`lesson-records/${id}`)
  },
  createRecord(data: Record<string, unknown>): Promise<LessonRecord> {
    return apiPost<LessonRecord>('lesson-records', data)
  },
  updateRecord(id: number, data: Record<string, unknown>): Promise<LessonRecord> {
    return apiPut<LessonRecord>(`lesson-records/${id}`, data)
  },
}
