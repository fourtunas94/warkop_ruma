// ignore_for_file: avoid_web_libraries_in_flutter, deprecated_member_use
import 'dart:html' as html;

bool printHtmlDoc(String htmlContent, {String windowFeatures = 'width=950,height=850,resizable=yes,scrollbars=yes'}) {
  try {
    final blob = html.Blob([htmlContent], 'text/html;charset=utf-8');
    final url = html.Url.createObjectUrlFromBlob(blob);

    html.WindowBase? win;
    try {
      final features = windowFeatures.isNotEmpty ? windowFeatures : 'width=950,height=850,resizable=yes,scrollbars=yes';
      win = html.window.open(url, '_blank', features);
    } catch (_) {
      win = null;
    }

    // Fallback: If popup was blocked by browser, use hidden iframe to trigger print
    if (win == null) {
      final iframe = html.IFrameElement()
        ..style.position = 'fixed'
        ..style.right = '0'
        ..style.bottom = '0'
        ..style.width = '0'
        ..style.height = '0'
        ..style.border = 'none'
        ..src = url;

      html.document.body?.append(iframe);

      iframe.onLoad.listen((_) {
        try {
          final contentWindow = iframe.contentWindow as html.Window?;
          contentWindow?.print();
        } catch (_) {}
      });


      Future.delayed(const Duration(seconds: 60), () {
        iframe.remove();
        html.Url.revokeObjectUrl(url);
      });
    }

    return true;
  } catch (_) {
    return false;
  }
}

bool printHtmlReceipt(String htmlContent) {
  return printHtmlDoc(htmlContent, windowFeatures: 'width=420,height=680');
}
