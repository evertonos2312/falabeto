<x-modal name="terms" title="Termos de Uso">
    <x-slot:subtitle>
        Estes Termos regulam o acesso e o uso do Fala Beto (“Serviço”). Ao criar sua conta e utilizar o Serviço,
        você declara que leu e concorda com estas condições.
    </x-slot:subtitle>

    <div class="space-y-4 bg-white text-slate-900">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">1) Identificação do responsável</h3>
            <p class="mt-2 !text-slate-700">
                O Serviço é operado por pessoa física (“Responsável pelo Serviço”):
            </p>
            <p class="mt-2 !text-slate-700">
                <span class="font-semibold">Everton Oliveira da Silva</span><br>
                <span class="font-semibold">São Paulo - SP</span><br>
                <span class="font-semibold">Contato:</span> everton.oliveirasilva@outlook.com
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">2) O que o serviço oferece</h3>
            <p class="mt-2 !text-slate-700">
                O Fala Beto ajuda você a organizar suas finanças pessoais, permitindo registrar e consultar movimentações
                (gastos, receitas e contas), além de gerar resumos e lembretes, inclusive por interações em chat.
            </p>
            <p class="mt-2 !text-slate-700">
                <span class="font-semibold">Importante:</span> o Serviço não fornece consultoria financeira, contábil ou jurídica,
                e não substitui profissionais especializados.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">3) Cadastro, acesso e responsabilidade do usuário</h3>
            <p class="mt-2 !text-slate-700">
                Para utilizar o Serviço, você deve fornecer informações corretas e manter sua senha em sigilo.
                Você é responsável por toda atividade realizada na sua conta.
            </p>
            <p class="mt-2 !text-slate-700">
                Você concorda em usar o Serviço de forma lícita e responsável, sem tentar burlar limites, explorar falhas,
                automatizar abusos ou interferir no funcionamento do sistema.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">4) Planos, testes, limites e disponibilidade</h3>
            <p class="mt-2 !text-slate-700">
                O Serviço pode oferecer período de teste e planos pagos. Benefícios e limites (como mensagens por dia e recursos)
                variam por plano e podem ser ajustados para novas contratações.
            </p>
            <p class="mt-2 !text-slate-700">
                Para manter estabilidade e segurança, o Serviço pode aplicar limites de uso e medidas contra abuso.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">5) Dados, logs e auditoria</h3>
            <p class="mt-2 !text-slate-700">
                Guardamos dados necessários para operar o Serviço, como dados de cadastro e registros financeiros inseridos por você.
                Também podemos manter logs técnicos e de auditoria por período limitado para segurança, prevenção de fraude,
                controle de uso e melhoria do Serviço.
            </p>
            <p class="mt-2 !text-slate-700">
                Os detalhes sobre dados coletados, finalidades, retenção e seus direitos são descritos na
                <span class="font-semibold">Política de Privacidade apresentada no momento do cadastro</span>.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">6) Segurança e criptografia</h3>
            <p class="mt-2 !text-slate-700">
                Adotamos boas práticas de segurança e, quando aplicável, utilizamos criptografia em campos sensíveis para proteção adicional.
                Ainda assim, nenhum sistema é totalmente imune a falhas. Recomendamos não enviar informações desnecessárias no chat.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">7) Uso de IA (interpretação de mensagens)</h3>
            <p class="mt-2 !text-slate-700">
                Para entender mensagens e automatizar lançamentos, o Serviço pode utilizar modelos de IA para interpretar o texto enviado.
                Você reconhece que interpretações podem conter erros e que você pode revisar, corrigir ou desfazer lançamentos.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">8) Cancelamento e encerramento</h3>
            <p class="mt-2 !text-slate-700">
                Você pode cancelar o plano a qualquer momento. Após o cancelamento ou término do plano, o acesso pode ser reduzido
                ou encerrado, respeitando obrigações legais e prazos de retenção descritos na Política de Privacidade.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">9) Alterações destes Termos</h3>
            <p class="mt-2 !text-slate-700">
                Estes Termos podem ser atualizados para refletir melhorias do Serviço e requisitos legais. Quando houver mudanças relevantes,
                informaremos por meios razoáveis. O uso contínuo do Serviço após a atualização indica concordância com a versão vigente.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-semibold !text-slate-900">10) Contato</h3>
            <p class="mt-2 !text-slate-700">
                Dúvidas ou solicitações: <span class="font-semibold">everton.oliveirasilva@outlook.com</span>
            </p>
        </div>

        <p class="pt-2 text-xs !text-slate-500">
            Versão: {{ config('legal.terms_version', '2026-01-31') }}
        </p>
    </div>
</x-modal>
