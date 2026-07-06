<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code de réinitialisation</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">

          <!-- Header brand -->
          <tr>
            <td align="center" style="padding-bottom:24px;">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#1e3a5f;border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                    <span style="font-size:22px;line-height:44px;">🏗️</span>
                  </td>
                  <td style="padding-left:10px;font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">
                    GesChantier
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Card -->
          <tr>
            <td style="background:#ffffff;border-radius:16px;border:1px solid #e2e8f0;padding:40px 36px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">

              <p style="font-size:15px;color:#64748b;margin:0 0 8px;">Bonjour <strong style="color:#0f172a;">{{ $prenom }}</strong>,</p>
              <p style="font-size:15px;color:#64748b;margin:0 0 32px;line-height:1.6;">
                Vous avez demandé à réinitialiser votre mot de passe.<br/>
                Voici votre code de vérification à usage unique :
              </p>

              <!-- OTP Code -->
              <div style="text-align:center;margin:0 0 32px;">
                <div style="display:inline-block;background:#f8fafc;border:2px dashed #cbd5e1;border-radius:16px;padding:24px 40px;">
                  <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;">Votre code</p>
                  <p style="margin:0;font-size:42px;font-weight:800;letter-spacing:0.18em;color:#0f172a;font-family:monospace;">{{ $code }}</p>
                </div>
              </div>

              <!-- Timer warning -->
              <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:28px;display:flex;align-items:center;">
                <p style="margin:0;font-size:13.5px;color:#92400e;line-height:1.5;">
                  ⏱️ <strong>Ce code expire dans {{ $expiresInMinutes }} minutes.</strong><br/>
                  Il est à usage unique et ne peut pas être réutilisé.
                </p>
              </div>

              <p style="font-size:13.5px;color:#64748b;margin:0 0 8px;line-height:1.6;">
                Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.
                Votre mot de passe restera inchangé.
              </p>

              <hr style="border:none;border-top:1px solid #e2e8f0;margin:28px 0;"/>

              <p style="font-size:12px;color:#94a3b8;margin:0;text-align:center;">
                Cet email a été envoyé automatiquement par <strong>GesChantier</strong>.<br/>
                Ne répondez pas à ce message.
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding-top:20px;text-align:center;">
              <p style="font-size:12px;color:#94a3b8;margin:0;">
                © {{ date('Y') }} GesChantier — Gestion de chantiers & matériaux
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>