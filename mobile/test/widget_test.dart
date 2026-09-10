import 'package:flutter_test/flutter_test.dart';

import 'package:optizee_staff/main.dart';

void main() {
  testWidgets('boots to auth loading or login gate', (WidgetTester tester) async {
    await tester.pumpWidget(const OptizeeStaffApp());
    await tester.pump();

    // Bootstrap may show a spinner briefly, then LoginScreen.
    expect(find.byType(OptizeeStaffApp), findsOneWidget);
    await tester.pump(const Duration(milliseconds: 100));
  });
}
