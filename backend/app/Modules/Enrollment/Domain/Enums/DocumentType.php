<?php

namespace App\Modules\Enrollment\Domain\Enums;

enum DocumentType: string
{
    case BirthCertificate = 'birth_certificate';
    case IdCard = 'id_card';
    case ProofOfAddress = 'proof_of_address';
    case SchoolTranscript = 'school_transcript';
    case TransferDeclaration = 'transfer_declaration';
    case VaccinationCard = 'vaccination_card';
    case Photo3x4 = 'photo_3x4';
    case SusCard = 'sus_card';
    case NisNumber = 'nis_number';
    case MedicalReport = 'medical_report';

    public function label(): string
    {
        return match ($this) {
            self::BirthCertificate => 'Certidão de Nascimento',
            self::IdCard => 'RG / Documento de Identidade',
            self::ProofOfAddress => 'Comprovante de Endereço',
            self::SchoolTranscript => 'Histórico Escolar',
            self::TransferDeclaration => 'Declaração de Transferência',
            self::VaccinationCard => 'Carteira de Vacinação',
            self::Photo3x4 => 'Foto 3x4',
            self::SusCard => 'Cartão SUS',
            self::NisNumber => 'Número NIS',
            self::MedicalReport => 'Laudo Médico',
        };
    }
}
