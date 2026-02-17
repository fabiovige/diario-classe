<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            ['name' => 'EMEB Profª Maria José de Oliveira', 'inep_code' => '35101001', 'address' => 'Rua das Acácias, 120 - Jd. Nossa Senhora de Fátima, Jandira-SP', 'phone' => '(11) 4707-1001', 'email' => 'emeb.mariajose@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. João Baptista Nogueira', 'inep_code' => '35101002', 'address' => 'Rua Amazonas, 350 - Centro, Jandira-SP', 'phone' => '(11) 4707-1002', 'email' => 'emeb.joaobaptista@jandira.sp.gov.br'],
            ['name' => 'EMEB Anísio Teixeira', 'inep_code' => '35101003', 'address' => 'Av. Conceição, 890 - Jd. São Luiz, Jandira-SP', 'phone' => '(11) 4707-1003', 'email' => 'emeb.anisioteixeira@jandira.sp.gov.br'],
            ['name' => 'EMEB Paulo Freire', 'inep_code' => '35101004', 'address' => 'Rua Tocantins, 210 - Vila Lia, Jandira-SP', 'phone' => '(11) 4707-1004', 'email' => 'emeb.paulofreire@jandira.sp.gov.br'],
            ['name' => 'EMEB Monteiro Lobato', 'inep_code' => '35101005', 'address' => 'Rua São Paulo, 455 - Jd. Gabriela, Jandira-SP', 'phone' => '(11) 4707-1005', 'email' => 'emeb.monteirolobato@jandira.sp.gov.br'],
            ['name' => 'EMEB Cecília Meireles', 'inep_code' => '35101006', 'address' => 'Rua Rio de Janeiro, 78 - Jd. Alvorada, Jandira-SP', 'phone' => '(11) 4707-1006', 'email' => 'emeb.ceciliameireles@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Ruth Cardoso', 'inep_code' => '35101007', 'address' => 'Rua Bahia, 600 - Pq. Esperança, Jandira-SP', 'phone' => '(11) 4707-1007', 'email' => 'emeb.ruthcardoso@jandira.sp.gov.br'],
            ['name' => 'EMEB Darcy Ribeiro', 'inep_code' => '35101008', 'address' => 'Rua Paraná, 145 - Vila Eunice, Jandira-SP', 'phone' => '(11) 4707-1008', 'email' => 'emeb.darcyribeiro@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Clarice Lispector', 'inep_code' => '35101009', 'address' => 'Av. Jandira, 1200 - Jd. do Lago, Jandira-SP', 'phone' => '(11) 4707-1009', 'email' => 'emeb.claricelispector@jandira.sp.gov.br'],
            ['name' => 'EMEB Vinícius de Moraes', 'inep_code' => '35101010', 'address' => 'Rua Goiás, 320 - Jd. Sta. Tereza, Jandira-SP', 'phone' => '(11) 4707-1010', 'email' => 'emeb.viniciusmoraes@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Florestan Fernandes', 'inep_code' => '35101011', 'address' => 'Rua Minas Gerais, 88 - Jd. São João, Jandira-SP', 'phone' => '(11) 4707-1011', 'email' => 'emeb.florestanfernandes@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Dorina Nowill', 'inep_code' => '35101012', 'address' => 'Rua Maranhão, 500 - Vila Cristina, Jandira-SP', 'phone' => '(11) 4707-1012', 'email' => 'emeb.dorinanowill@jandira.sp.gov.br'],
            ['name' => 'EMEB Zilda Arns', 'inep_code' => '35101013', 'address' => 'Rua Ceará, 275 - Jd. Petrópolis, Jandira-SP', 'phone' => '(11) 4707-1013', 'email' => 'emeb.zildaarns@jandira.sp.gov.br'],
            ['name' => 'EMEB Carlos Drummond de Andrade', 'inep_code' => '35101014', 'address' => 'Rua Pernambuco, 430 - Vila Maria, Jandira-SP', 'phone' => '(11) 4707-1014', 'email' => 'emeb.drummond@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Milton Santos', 'inep_code' => '35101015', 'address' => 'Av. dos Trabalhadores, 680 - Jd. Guanabara, Jandira-SP', 'phone' => '(11) 4707-1015', 'email' => 'emeb.miltonsantos@jandira.sp.gov.br'],
            ['name' => 'EMEB Cora Coralina', 'inep_code' => '35101016', 'address' => 'Rua Espírito Santo, 190 - Jd. Ipê, Jandira-SP', 'phone' => '(11) 4707-1016', 'email' => 'emeb.coracoralina@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Tarsila do Amaral', 'inep_code' => '35101017', 'address' => 'Rua Mato Grosso, 310 - Pq. Primavera, Jandira-SP', 'phone' => '(11) 4707-1017', 'email' => 'emeb.tarsiladoamaral@jandira.sp.gov.br'],
            ['name' => 'EMEB Villa-Lobos', 'inep_code' => '35101018', 'address' => 'Rua Sergipe, 95 - Jd. Europa, Jandira-SP', 'phone' => '(11) 4707-1018', 'email' => 'emeb.villalobos@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Ruy Barbosa', 'inep_code' => '35101019', 'address' => 'Rua Piauí, 440 - Jd. América, Jandira-SP', 'phone' => '(11) 4707-1019', 'email' => 'emeb.ruybarbosa@jandira.sp.gov.br'],
            ['name' => 'EMEB Santos Dumont', 'inep_code' => '35101020', 'address' => 'Rua Alagoas, 160 - Vila Nova, Jandira-SP', 'phone' => '(11) 4707-1020', 'email' => 'emeb.santosdumont@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Emília Ferreiro', 'inep_code' => '35101021', 'address' => 'Rua Pará, 380 - Jd. Califórnia, Jandira-SP', 'phone' => '(11) 4707-1021', 'email' => 'emeb.emiliaferreiro@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Maria Montessori', 'inep_code' => '35101022', 'address' => 'Rua Rio Grande do Sul, 540 - Jd. das Flores, Jandira-SP', 'phone' => '(11) 4707-1022', 'email' => 'emeb.montessori@jandira.sp.gov.br'],
            ['name' => 'EMEB Portinari', 'inep_code' => '35101023', 'address' => 'Rua Santa Catarina, 200 - Vila Progresso, Jandira-SP', 'phone' => '(11) 4707-1023', 'email' => 'emeb.portinari@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Nise da Silveira', 'inep_code' => '35101024', 'address' => 'Rua Acre, 130 - Jd. Sta. Rita, Jandira-SP', 'phone' => '(11) 4707-1024', 'email' => 'emeb.nisedasilveira@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Sérgio Buarque de Holanda', 'inep_code' => '35101025', 'address' => 'Av. Brasil, 780 - Jd. Paulista, Jandira-SP', 'phone' => '(11) 4707-1025', 'email' => 'emeb.sergiobuarque@jandira.sp.gov.br'],
            ['name' => 'EMEB Olavo Bilac', 'inep_code' => '35101026', 'address' => 'Rua Rondônia, 55 - Jd. Centenário, Jandira-SP', 'phone' => '(11) 4707-1026', 'email' => 'emeb.olavobilac@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Carolina de Jesus', 'inep_code' => '35101027', 'address' => 'Rua Amapá, 290 - Vila Esperança, Jandira-SP', 'phone' => '(11) 4707-1027', 'email' => 'emeb.carolinadejesus@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Luiz Gonzaga', 'inep_code' => '35101028', 'address' => 'Rua Roraima, 170 - Jd. Bela Vista, Jandira-SP', 'phone' => '(11) 4707-1028', 'email' => 'emeb.luizgonzaga@jandira.sp.gov.br'],
            ['name' => 'EMEB Profª Lygia Fagundes Telles', 'inep_code' => '35101029', 'address' => 'Rua Tocantins, 420 - Jd. Novo Horizonte, Jandira-SP', 'phone' => '(11) 4707-1029', 'email' => 'emeb.lygiafagundes@jandira.sp.gov.br'],
            ['name' => 'EMEB Prof. Vital Brasil', 'inep_code' => '35101030', 'address' => 'Rua Distrito Federal, 310 - Jd. dos Lírios, Jandira-SP', 'phone' => '(11) 4707-1030', 'email' => 'emeb.vitalbrasil@jandira.sp.gov.br'],
        ];

        foreach ($schools as $schoolData) {
            School::updateOrCreate(
                ['inep_code' => $schoolData['inep_code']],
                array_merge($schoolData, [
                    'type' => 'municipal',
                    'active' => true,
                ]),
            );
        }
    }
}
