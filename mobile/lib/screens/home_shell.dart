import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../theme/app_theme.dart';
import 'kitchen_screen.dart';
import 'pos_screen.dart';

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;
  bool _didInit = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_didInit) return;
    _didInit = true;
    final user = context.read<AuthProvider>().user;
    if (user?.home == 'kitchen' && user!.canAccessPos) {
      _index = 1;
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user!;
    final pages = <Widget>[];
    final destinations = <NavigationDestination>[];

    if (user.canAccessPos) {
      pages.add(const PosScreen());
      destinations.add(const NavigationDestination(
        icon: Icon(Icons.point_of_sale_outlined),
        selectedIcon: Icon(Icons.point_of_sale),
        label: 'POS',
      ));
    }
    if (user.canAccessKitchen) {
      pages.add(const KitchenScreen());
      destinations.add(const NavigationDestination(
        icon: Icon(Icons.soup_kitchen_outlined),
        selectedIcon: Icon(Icons.soup_kitchen),
        label: 'Kitchen',
      ));
    }

    if (pages.isEmpty) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('No access for this account'),
              const SizedBox(height: 12),
              FilledButton(onPressed: auth.logout, child: const Text('Logout')),
            ],
          ),
        ),
      );
    }

    final safeIndex = _index.clamp(0, pages.length - 1);

    return Scaffold(
      body: IndexedStack(index: safeIndex, children: pages),
      bottomNavigationBar: SafeArea(
        child: Container(
          decoration: const BoxDecoration(
            color: AppTheme.surface,
            border: Border(top: BorderSide(color: AppTheme.border)),
          ),
          padding: const EdgeInsets.fromLTRB(8, 6, 8, 6),
          child: Row(
            children: [
              for (var i = 0; i < destinations.length; i++)
                Expanded(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(14),
                    onTap: () => setState(() => _index = i),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            i == safeIndex
                                ? (i == 0 && user.canAccessPos
                                    ? Icons.point_of_sale
                                    : Icons.soup_kitchen)
                                : (destinations[i].icon as Icon).icon,
                            color: i == safeIndex ? AppTheme.accent : AppTheme.muted,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            destinations[i].label,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: i == safeIndex ? AppTheme.accent : AppTheme.muted,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              Expanded(
                child: InkWell(
                  borderRadius: BorderRadius.circular(14),
                  onTap: auth.logout,
                  child: const Padding(
                    padding: EdgeInsets.symmetric(vertical: 10),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.logout, color: AppTheme.muted),
                        SizedBox(height: 4),
                        Text(
                          'Logout',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.muted),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
