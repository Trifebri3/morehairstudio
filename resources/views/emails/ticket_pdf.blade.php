<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Ticket - More Hair Studio</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
        }
        .ticket-container {
            border: 2px dashed #000000;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 2px;
            color: #0A3D91;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666666;
        }
        .ticket-details {
            margin-bottom: 25px;
        }
        .detail-row {
            display: block;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .detail-label {
            font-weight: bold;
            color: #666666;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            display: inline-block;
            width: 150px;
        }
        .detail-value {
            display: inline-block;
            font-weight: 700;
            color: #111111;
        }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .services-table th {
            text-align: left;
            border-bottom: 1px solid #333333;
            padding-bottom: 5px;
            font-size: 10px;
            text-transform: uppercase;
            color: #666666;
        }
        .services-table td {
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px dashed #dddddd;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }
        .qr-code {
            width: 180px;
            height: 180px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .passcode-box {
            background-color: #f5f5f5;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1.5px;
            color: #0A3D91;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #999999;
            margin-top: 40px;
            border-top: 1px solid #eeeeee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <h1>MORE HAIR STUDIO</h1>
            <p>Digital Booking & Verification Pass</p>
        </div>

        <div class="ticket-details">
            <div class="detail-row">
                <span class="detail-label">Nama Pelanggan:</span>
                <span class="detail-value">{{ $customer->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Kode Booking:</span>
                <span class="detail-value" style="font-family: monospace; font-size: 14px;">{{ $booking->booking_code }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status Tiket:</span>
                <span class="detail-value" style="text-transform: uppercase; color: #0A3D91;">{{ $booking->status }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Lokasi Studio:</span>
                <span class="detail-value">{{ $outlet->name }} ({{ $outlet->address }})</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tanggal Sesi:</span>
                <span class="detail-value">{{ $booking->booking_date->format('d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Waktu Sesi:</span>
                <span class="detail-value" style="font-family: monospace;">{{ substr($booking->booking_date, 11, 5) ?: 'Sesuai Jadwal' }}</span>
            </div>
            @if($stylist)
                <div class="detail-row">
                    <span class="detail-label">Stylist / Barber:</span>
                    <span class="detail-value">{{ $stylist->name }}</span>
                </div>
            @endif
        </div>

        <table class="services-table">
            <thead>
                <tr>
                    <th>Layanan Jasa</th>
                    <th style="text-align: right;">Tarif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->service->name }}</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="qr-section">
            <p style="font-size: 11px; font-weight: bold; margin-bottom: 15px; color: #666666; text-transform: uppercase; letter-spacing: 0.5px;">Scan QR Code untuk Verifikasi Kehadiran</p>
            <img src="{{ $qrCodeUrl }}" class="qr-code" alt="QR Verification" />
            <br>
            <div class="passcode-box">
                PASSCODE: {{ $ticket->passcode }}
            </div>
            <p style="font-size: 9px; color: #999999; margin-top: 10px;">Gunakan Passcode di atas jika pemindai QR Code mengalami kendala.</p>
        </div>

        <div class="footer">
            <p>MORE Hair Studio &copy; {{ date('Y') }}. All rights reserved.</p>
            <p>Harap tunjukkan tiket digital ini kepada kasir/barber saat kedatangan di outlet.</p>
        </div>
    </div>
</body>
</html>
