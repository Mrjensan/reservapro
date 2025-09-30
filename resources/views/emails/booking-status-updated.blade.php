<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 24px; }
        .mail-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; }
        .mail-card h1 { font-size: 20px; }
        .status { font-weight: bold; color: #0d6efd; }
        .footer { font-size: 12px; color: #777777; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="mail-card">
        <h1>Status da sua reserva foi atualizado</h1>
        <p>Ola {{ $booking->customer?->name }},</p>
        <p>Temos novidades sobre a sua reserva <strong>{{ $booking->service?->name }}</strong>.</p>
        <p>O status agora e: <span class="status">{{ strtoupper($booking->status) }}</span></p>
        <p><strong>Quando:</strong> {{ $booking->start_at->format('d/m/Y H:i') }} - {{ $booking->end_at->format('H:i') }}</p>

        <p>Qualquer duvida, estamos a disposicao por e-mail ou telefone.</p>

        <p>Equipe {{ config('app.name') }}</p>

        <div class="footer">
            <p>Este e um e-mail automatico. Caso nao tenha solicitado, desconsidere.</p>
        </div>
    </div>
</body>
</html>
