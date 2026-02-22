import type { User } from './auth'
import type { DisabilityType } from './enums'
import type { School } from './school-structure'

export interface Student {
  id: number
  name: string
  social_name: string | null
  display_name: string
  birth_date: string | null
  gender: string
  race_color: string
  cpf: string
  has_disability: boolean
  disability_type: DisabilityType | null
  disability_type_label: string | null
  active: boolean
  guardians?: Guardian[]
  current_enrollment?: {
    school_name: string | null
    class_group_label: string | null
  } | null
  created_at: string
}

export interface Guardian {
  id: number
  name: string
  cpf: string
  phone: string
  email: string
  address: string
  occupation: string
  relationship?: string | null
  is_primary?: boolean | null
  students?: Student[]
}

export interface Teacher {
  id: number
  user_id: number
  school_id: number
  registration_number: string
  specialization: string
  hire_date: string | null
  active: boolean
  user?: User
  school?: School
}
