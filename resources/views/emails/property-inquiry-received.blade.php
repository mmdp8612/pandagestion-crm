<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Nueva consulta por {{ $property['codigo'] }}</title>
    </head>
    <body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,sans-serif;">
        <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
                <p style="margin:0;color:#047857;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;">PandaGestion</p>
                <h1 style="margin:10px 0 0;font-size:24px;line-height:1.3;">Nueva consulta por {{ $property['codigo'] }}</h1>
                <p style="margin:10px 0 0;color:#475569;line-height:1.6;">
                    {{ $property['domicilio'] ?: 'Domicilio a consultar' }}{{ $property['ubicacion'] ? ' · '.$property['ubicacion'] : '' }}
                </p>

                <div style="margin-top:24px;padding:18px;background:#f8fafc;border-radius:12px;">
                    <p style="margin:0 0 8px;"><strong>Nombre:</strong> {{ $inquiry['nombre'] }}</p>
                    @if ($inquiry['email'])
                        <p style="margin:0 0 8px;"><strong>Correo:</strong> <a href="mailto:{{ $inquiry['email'] }}">{{ $inquiry['email'] }}</a></p>
                    @endif
                    @if ($inquiry['telefono'])
                        <p style="margin:0;"><strong>Teléfono:</strong> {{ $inquiry['telefono'] }}</p>
                    @endif
                </div>

                <h2 style="margin:24px 0 8px;font-size:16px;">Mensaje</h2>
                <p style="margin:0;color:#334155;line-height:1.7;white-space:pre-line;">{{ $inquiry['mensaje'] }}</p>

                <p style="margin:26px 0 0;">
                    <a href="{{ route('admin.inquiries.show', $inquiry['id']) }}" style="display:inline-block;padding:12px 18px;background:#059669;color:#ffffff;text-decoration:none;border-radius:9px;font-weight:700;">Abrir en PandaGestion</a>
                </p>
                <p style="margin:14px 0 0;color:#64748b;font-size:12px;">La consulta quedó guardada en la bandeja administrativa.</p>
            </div>
        </div>
    </body>
</html>
