<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 24px; }
        .mail-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; }
        .mail-card h1 { font-size: 20px; }
        .details { margin: 24px 0; padding: 16px; border-radius: 8px; background: #f1f3fb; }
        .footer { font-size: 12px; color: #777777; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="mail-card">
        <h1>Reserva recebida</h1>
        <p>Ola {{ $booking->customer?->name }},</p>
        <p>Recebemos sua solicitacao de atendimento e estamos preparando tudo para te receber.</p>

        <div class="details">
            <p><strong>Servico:</strong> {{ $booking->service?->name }}</p>
            <p><strong>Data:</strong> {{ $booking->start_at->format('d/m/Y') }}</p>
            <p><strong>Horario:</strong> {{ $booking->start_at->format('H:i') }} - {{ $booking->end_at->format('H:i') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>
            <p><strong>Codigo de confirmacao:</strong> {{ $booking->confirmation_code }}</p>
        </div>

        <p>Se precisar alterar alguma informacao, basta responder este e-mail ou falar com nossa equipe.</p>

        <p>Nos vemos em breve!</p>
        <p>Equipe {{ config('app.name') }}</p>

        <div class="footer">
            <p>Este e um e-mail automatico. Caso nao tenha feito esta solicitacao, entre em contato conosco.</p>
        </div>
    </div>
</body>
</html>
