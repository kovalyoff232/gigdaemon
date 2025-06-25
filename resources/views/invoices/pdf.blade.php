<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Счет №{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .details { margin-bottom: 40px; }
        .details table { width: 100%; }
        .items-table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .items-table td { padding: 8px; vertical-align: top; }
        .items-table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .items-table tr.item td { border-bottom: 1px solid #eee; }
        .total { text-align: right; font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>Счет на оплату №{{ $invoice->invoice_number }}</h1>
            <span>от {{ $invoice->issue_date->format('d.m.Y') }}</span>
        </div>
        <div class="details">
            <table>
                <tr>
                    <td>
                        <strong>Исполнитель:</strong><br>
                        {{ $invoice->user->name }}
                    </td>
                    <td style="text-align: right;">
                        <strong>Заказчик:</strong><br>
                        {{ $invoice->client->name }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <tr class="heading">
                <td>Описание</td>
                <td style="text-align: right;">Кол-во (часы)</td>
                <td style="text-align: right;">Цена за час</td>
                <td style="text-align: right;">Сумма</td>
            </tr>
            @foreach($invoice->items as $item)
                <tr class="item">
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2, ',', '') }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            @endforeach
            <tr class="heading total">
                <td colspan="3">Итого к оплате:</td>
                <td>{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
        </table>
        <p style="margin-top: 40px;">Оплатить до: {{ $invoice->due_date->format('d.m.Y') }}</p>
    </div>
</body>
</html>