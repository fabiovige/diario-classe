import { useConfirm as usePrimeConfirm } from 'primevue/useconfirm'

export function useConfirm() {
  const confirm = usePrimeConfirm()

  function confirmDelete(onAccept: () => void, message?: string) {
    confirm.require({
      message: message ?? 'Tem certeza que deseja excluir este registro?',
      header: 'Confirmar Exclusao',
      icon: 'pi pi-exclamation-triangle',
      rejectLabel: 'Cancelar',
      acceptLabel: 'Excluir',
      acceptClass: 'p-button-danger',
      accept: onAccept,
    })
  }

  function confirmAction(message: string, onAccept: () => void, header?: string) {
    confirm.require({
      message,
      header: header ?? 'Confirmacao',
      icon: 'pi pi-question-circle',
      rejectLabel: 'Cancelar',
      acceptLabel: 'Confirmar',
      accept: onAccept,
    })
  }

  return { confirmDelete, confirmAction }
}
