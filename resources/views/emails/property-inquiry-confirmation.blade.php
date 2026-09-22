<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Recibimos tu consulta por {{ $property['codigo'] }}</title>
    </head>
    <body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,sans-serif;">
        <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
                <p style="margin:0;color:#047857;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;">{{ $agency['nombre'] }}</p>
                <h1 style="margin:10px 0 0;font-size:24px;line-height:1.3;">Recibimos tu consulta</h1>
                <p style="margin:14px 0 0;color:#475569;line-height:1.7;">
                    Hola {{ $inquiry['nombre'] }}, tu mensaje por la propiedad <strong>{{ $property['codigo'] }}</strong> fue recibido correctamente. La inmobiliaria se pondr&aacute; en contacto con vos a la brevedad.
                </p>

                <div style="margin-top:24px;padding:18px;background:#f8fafc;border-radius:12px;">
                    <p style="margin:0 0 8px;"><strong>Propiedad:</strong> {{ $property['codigo'] }}</p>
                    <p style="margin:0 0 8px;"><strong>Domicilio:</strong> {{ $property['domicilio'] ?: 'A consultar' }}</p>
                    @if ($property['ubicacion'])
                        <p style="margin:0;"><strong>Ubicaci&oacute;n:</strong> {{ $property['ubicacion'] }}</p>
                    @endif
                </div>

                <h2 style="margin:24px 0 8px;font-size:16px;">Tu mensaje</h2>
                <p style="margin:0;color:#334155;line-height:1.7;white-space:pre-line;">{{ $inquiry['mensaje'] }}</p>

                <p style="margin:26px 0 0;">
                    <a href="{{ route('public.properties.show', $property['slug']) }}" style="display:inline-block;padding:12px 18px;background:#059669;color:#ffffff;text-decoration:none;border-radius:9px;font-weight:700;">Volver a ver la propiedad</a>
                </p>
                <p style="margin:18px 0 0;color:#64748b;font-size:12px;line-height:1.6;">
                    Pod&eacute;s responder directamente a este correo para comunicarte con {{ $agency['nombre'] }}.
                </p>
            </div>
        </div>
    </body>
</html>
