// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter_test/flutter_test.dart';

import 'package:arzana_mobile/main.dart';

void main() {
  testWidgets('App smoke test renders Warkop Ruma splash screen', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const WarkopRumaApp());

    // Verify that Warkop Ruma branding is displayed.
    expect(find.text('WARKOP RUMA'), findsWidgets);

    // Drain splash delay timer without waiting for infinite spinner
    await tester.pump(const Duration(milliseconds: 600));
  });
}
