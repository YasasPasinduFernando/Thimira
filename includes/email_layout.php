<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function email_brand_display_name(): string
{
    return 'Village Traveler';
}

/**
 * Wrap pre-escaped inner HTML (use esc() on all user-controlled fragments before building $innerHtml).
 */
function email_layout_html(string $eyebrow, string $heading, string $innerHtml): string
{
    $brand = email_brand_display_name();
    $tagline = 'Explore local attractions · Sri Lanka';

    $eyebrowH = htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8');
    $headingH = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
    $brandH = htmlspecialchars($brand, ENT_QUOTES, 'UTF-8');
    $taglineH = htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8');

    return '<!DOCTYPE html>'
        . '<html lang="en">'
        . '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . $headingH . '</title></head>'
        . '<body style="margin:0;padding:0;background-color:#e8eef4;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#e8eef4;padding:28px 14px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;border-radius:20px;overflow:hidden;box-shadow:0 20px 50px rgba(15,23,42,0.12);">'
        . '<tr><td style="background:linear-gradient(135deg,#0d9488 0%,#115e59 42%,#1d4ed8 100%);padding:26px 28px;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>'
        . '<td width="48" valign="middle" style="width:48px;">'
        . '<div style="width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,0.22);text-align:center;line-height:48px;font-size:24px;">&#128205;</div>'
        . '</td>'
        . '<td valign="middle" style="padding-left:16px;">'
        . '<div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.88);">' . $eyebrowH . '</div>'
        . '<div style="font-size:21px;font-weight:800;color:#ffffff;margin-top:6px;letter-spacing:-0.03em;line-height:1.2;">' . $brandH . '</div>'
        . '<div style="font-size:13px;color:rgba(255,255,255,0.78);margin-top:6px;line-height:1.4;">' . $taglineH . '</div>'
        . '</td></tr></table>'
        . '</td></tr>'
        . '<tr><td style="background:#ffffff;padding:28px 28px 8px 28px;">'
        . '<h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.03em;line-height:1.25;">' . $headingH . '</h1>'
        . '</td></tr>'
        . '<tr><td style="background:#ffffff;padding:4px 28px 32px 28px;font-size:15px;line-height:1.65;color:#334155;">'
        . $innerHtml
        . '</td></tr>'
        . '<tr><td style="background:#f1f5f9;padding:22px 28px;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.55;color:#64748b;text-align:center;">'
        . 'This message was sent by <strong style="color:#0f766e;">' . $brandH . '</strong> (' . htmlspecialchars((string) APP_NAME, ENT_QUOTES, 'UTF-8') . ').<br>'
        . 'Please do not reply directly to this automated email.'
        . '</td></tr>'
        . '</table>'
        . '</td></tr></table>'
        . '</body></html>';
}
