<?php

/**
 * Exemplo de Uso Completo - Maestro Payment Package
 * 
 * Este exemplo demonstra a nova arquitetura:
 * 1. Usuario monta DTOs (interface externa, sem validação)
 * 2. Factory converte DTO → VO (com validação)
 * 3. Adapter/Mapper usa VOs (processamento interno)
 * 4. Retorna DTOs (interface externa)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\PixDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\CreditCardDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Factories\PaymentFactory;
use Flowcoders\Maestro\Adapters\MercadoPagoAdapter;
use Flowcoders\Maestro\Enums\CardBrand;
use Flowcoders\Maestro\Mappers\MercadoPagoPaymentMapper;

echo "=== MAESTRO PAYMENT PACKAGE - EXEMPLO DE USO ===\n\n";

// ================================================================
// EXEMPLO 1: PAGAMENTO PIX
// ================================================================

echo "📱 EXEMPLO 1: Pagamento PIX\n";
echo "=".str_repeat("=", 50)."\n\n";

// 1. USUARIO monta DTOs (interface externa - sem validação)
echo "1️⃣ Usuario monta DTOs (interface externa):\n";

$customer = CustomerDTO::create(
    email: 'joao.silva@email.com',
    firstName: 'João',
    lastName: 'Silva',
    document: '12345678901', // CPF como string
    documentType: DocumentType::CPF,
    phone: '11999999999'
);

$pixMethod = PixDTO::create(expiresAt: 60); // PIX expira em 60 minutos

$pixPayment = PaymentDTO::create(
    amount: 10000, // R$ 100,00 em centavos
    currency: Currency::BRL,
    description: 'Compra de produto XYZ',
    paymentMethod: $pixMethod,
    customer: $customer,
    externalReference: 'ORDER-123456',
    notificationUrl: 'https://meusite.com/webhook'
);

echo "✅ CustomerDTO criado: {$customer->firstName} {$customer->lastName}\n";
echo "✅ PixDTO criado: expira em {$pixMethod->expiresAt} minutos\n";
echo "✅ PaymentDTO criado: R$ " . number_format($pixPayment->amount / 100, 2, ',', '.') . "\n\n";

// 2. FACTORY converte DTO → VO (com validação)
echo "2️⃣ Factory converte DTO → VO (com validação):\n";

try {
    $validatedPayment = PaymentFactory::fromDTO($pixPayment);
    echo "✅ Payment VO criado e validado!\n";
    echo "   - Customer com documento válido: " . ($validatedPayment->customer->hasValidDocument() ? 'SIM' : 'NÃO') . "\n";
    echo "   - PaymentMethod tipo: {$validatedPayment->paymentMethod->getType()}\n";
    echo "   - Requer customer: " . ($validatedPayment->requiresCustomer() ? 'SIM' : 'NÃO') . "\n\n";
} catch (\InvalidArgumentException $e) {
    echo "❌ Erro de validação: {$e->getMessage()}\n\n";
    exit(1);
}

// 3. ADAPTER/MAPPER usa VOs (processamento interno)
echo "3️⃣ Adapter/Mapper usa VOs (processamento interno):\n";

$mapper = new MercadoPagoPaymentMapper();
$pspPayload = $mapper->mapCreatePaymentRequest($validatedPayment);

echo "✅ Payload do MercadoPago gerado:\n";
echo json_encode($pspPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// ================================================================
// EXEMPLO 2: PAGAMENTO CARTÃO DE CRÉDITO
// ================================================================

echo "💳 EXEMPLO 2: Pagamento Cartão de Crédito\n";
echo "=".str_repeat("=", 50)."\n\n";

// 1. USUARIO monta DTOs (interface externa)
echo "1️⃣ Usuario monta DTOs (interface externa):\n";

$customerCard = CustomerDTO::create(
    email: 'maria.santos@email.com',
    firstName: 'Maria',
    lastName: 'Santos',
    // Sem documento para cartão (opcional)
    phone: '11888888888'
);

$cardBrand = CardBrand::VISA;

$creditCard = CreditCardDTO::create(
    token: 'card_token_abc123',
    holderName: 'MARIA SANTOS',
    expirationMonth: 12,
    expirationYear: 2025,
    brand: $cardBrand,
    lastFourDigits: '1234'
);

$cardPayment = PaymentDTO::create(
    amount: 50000, // R$ 500,00 em centavos
    currency: Currency::BRL,
    description: 'Assinatura Premium - 12 meses',
    paymentMethod: $creditCard, // Mesmo interface, diferente implementação!
    installments: 3, // 3 parcelas
    customer: $customerCard,
    externalReference: 'SUBSCRIPTION-789'
);

echo "✅ CustomerDTO criado: {$customerCard->firstName} {$customerCard->lastName}\n";
echo "✅ CreditCardDTO criado: {$creditCard->brand} ****{$creditCard->lastFourDigits}\n";
echo "✅ PaymentDTO criado: {$cardPayment->installments}x de R$ " . 
     number_format(($cardPayment->amount / $cardPayment->installments) / 100, 2, ',', '.') . "\n\n";

// 2. FACTORY converte DTO → VO (com validação)
echo "2️⃣ Factory converte DTO → VO (com validação):\n";

try {
    $validatedCardPayment = PaymentFactory::fromDTO($cardPayment);
    echo "✅ Payment VO criado e validado!\n";
    echo "   - Customer sem documento: " . ($validatedCardPayment->customer->hasValidDocument() ? 'SIM' : 'NÃO') . "\n";
    echo "   - PaymentMethod tipo: {$validatedCardPayment->paymentMethod->getType()}\n";
    echo "   - Parcelas: {$validatedCardPayment->installments}x\n\n";
} catch (\InvalidArgumentException $e) {
    echo "❌ Erro de validação: {$e->getMessage()}\n\n";
    exit(1);
}

// 3. ADAPTER/MAPPER usa VOs (processamento interno)
echo "3️⃣ Adapter/Mapper usa VOs (processamento interno):\n";

$cardPspPayload = $mapper->mapCreatePaymentRequest($validatedCardPayment);

echo "✅ Payload do MercadoPago gerado:\n";
echo json_encode($cardPspPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// ================================================================
// EXEMPLO 3: DEMONSTRAÇÃO DE VALIDAÇÕES
// ================================================================

echo "🛡️ EXEMPLO 3: Demonstração de Validações\n";
echo "=".str_repeat("=", 50)."\n\n";

echo "1️⃣ Testando PIX sem customer (deve falhar):\n";

$pixWithoutCustomer = PaymentDTO::create(
    amount: 1000,
    currency: Currency::BRL,
    description: 'Teste sem customer',
    paymentMethod: PixDTO::create(expiresAt: 30)
    // customer: null - PIX requer customer!
);

try {
    PaymentFactory::fromDTO($pixWithoutCustomer);
    echo "❌ ERRO: Deveria ter falhado!\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ Validação funcionando: {$e->getMessage()}\n\n";
}

echo "2️⃣ Testando valor inválido (deve falhar):\n";

$invalidAmount = PaymentDTO::create(
    amount: 0, // Valor inválido!
    currency: Currency::BRL,
    description: 'Teste valor zero',
    paymentMethod: PixDTO::create(expiresAt: 30),
    customer: $customer
);

try {
    PaymentFactory::fromDTO($invalidAmount);
    echo "❌ ERRO: Deveria ter falhado!\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ Validação funcionando: {$e->getMessage()}\n\n";
}

echo "3️⃣ Testando customer sem dados mínimos (deve falhar):\n";

$invalidCustomer = CustomerDTO::create(); // Sem email, nome, nada!

$invalidPayment = PaymentDTO::create(
    amount: 1000,
    currency: Currency::BRL,
    description: 'Teste customer inválido',
    paymentMethod: PixDTO::create(expiresAt: 30),
    customer: $invalidCustomer
);

try {
    PaymentFactory::fromDTO($invalidPayment);
    echo "❌ ERRO: Deveria ter falhado!\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ Validação funcionando: {$e->getMessage()}\n\n";
}

// ================================================================
// EXEMPLO 4: FLUXO COMPLETO COM ADAPTER (SIMULADO)
// ================================================================

echo "🚀 EXEMPLO 4: Fluxo Completo com Adapter (Simulado)\n";
echo "=".str_repeat("=", 50)."\n\n";

echo "1️⃣ Preparando pagamento PIX completo:\n";

// Usuario monta dados
$finalCustomer = CustomerDTO::create(
    email: 'teste@example.com',
    firstName: 'Cliente',
    lastName: 'Teste',
    document: '11122233344',
    documentType: DocumentType::CPF,
    phone: '11999888777'
);

$finalPix = PixDTO::create(expiresAt: 120);

$finalPayment = PaymentDTO::create(
    amount: 25000, // R$ 250,00
    currency: Currency::BRL,
    description: 'Pagamento de teste - Produto ABC',
    paymentMethod: $finalPix,
    customer: $finalCustomer,
    externalReference: 'TEST-'.uniqid(),
    notificationUrl: 'https://webhook.example.com/payment'
);

echo "✅ DTOs criados pelo usuario\n";

// Factory valida
$validatedFinalPayment = PaymentFactory::fromDTO($finalPayment);
echo "✅ Payment VO validado pela factory\n";

// Mapper gera payload
$finalPayload = $mapper->mapCreatePaymentRequest($validatedFinalPayment);
echo "✅ Payload do PSP gerado pelo mapper\n";

// Simulação de resposta do PSP
$simulatedResponse = [
    'id' => 'MP-' . uniqid(),
    'status' => 'pending',
    'transaction_amount' => 250.00,
    'currency_id' => 'BRL',
    'description' => 'Pagamento de teste - Produto ABC',
    'external_reference' => $finalPayment->externalReference,
    'payment_method_id' => 'pix',
    'date_created' => (new DateTime())->format('c'),
    'date_last_updated' => (new DateTime())->format('c'),
];

// Mapper converte resposta
$responseDTO = $mapper->mapPaymentResponse($simulatedResponse);
echo "✅ PaymentResponseDTO criado a partir da resposta do PSP\n\n";

echo "📊 RESULTADO FINAL:\n";
echo "   - Payment ID: {$responseDTO->id}\n";
echo "   - Status: {$responseDTO->status->value}\n";
echo "   - Valor: R$ " . number_format($responseDTO->amount / 100, 2, ',', '.') . "\n";
echo "   - Método: {$responseDTO->paymentMethod}\n";
echo "   - Referência: {$responseDTO->externalReference}\n\n";

// ================================================================
// RESUMO DA ARQUITETURA
// ================================================================

echo "📋 RESUMO DA NOVA ARQUITETURA\n";
echo "=".str_repeat("=", 50)."\n\n";

echo "1️⃣ USUARIO trabalha com DTOs:\n";
echo "   ✅ Interface simples e limpa\n";
echo "   ✅ Sem validação (tipos primitivos)\n";
echo "   ✅ Fácil de serializar/deserializar\n";
echo "   ✅ CustomerDTO, PaymentDTO, PixDTO, CreditCardDTO\n\n";

echo "2️⃣ FACTORY converte DTO → VO:\n";
echo "   ✅ PaymentFactory::fromDTO()\n";
echo "   ✅ CustomerFactory::fromDTO()\n";
echo "   ✅ Validação automática na conversão\n";
echo "   ✅ Erros claros se dados inválidos\n\n";

echo "3️⃣ VALUEOBJECTS com validação:\n";
echo "   ✅ Payment VO, Customer VO\n";
echo "   ✅ Email VO, PhoneNumber VO, Cpf VO\n";
echo "   ✅ Regras de negócio internas\n";
echo "   ✅ Dados sempre válidos\n\n";

echo "4️⃣ MAPPERS usam VOs validados:\n";
echo "   ✅ Sem necessidade de validação adicional\n";
echo "   ✅ Foco na transformação de dados\n";
echo "   ✅ Polimorfismo entre PaymentMethods\n";
echo "   ✅ Código mais limpo\n\n";

echo "5️⃣ INTERFACE POLIMÓRFICA:\n";
echo "   ✅ PaymentMethodInterface permite PIX/Card\n";
echo "   ✅ Mesmo código para diferentes métodos\n";
echo "   ✅ Extensível para novos PSPs\n";
echo "   ✅ Type-safe com PHP 8+\n\n";

echo "🎉 EXEMPLO CONCLUÍDO COM SUCESSO!\n";
echo "A nova arquitetura está funcionando perfeitamente!\n\n";
