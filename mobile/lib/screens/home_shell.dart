import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../theme/app_theme.dart';
import 'kitchen_screen.dart';
import 'pos_screen.dart';

class _NavItem {
  const _NavItem({
    required this.label,
    required this.icon,
    required this.selectedIcon,
    required this.page,
    this.posArea,
  });

  final String label;
  final IconData icon;
  final IconData selectedIcon;
  final Widget page;
  final String? posArea;
}

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
    final items = _itemsFor(user);
    if (user?.home == 'kitchen') {
      final kitchenIdx = items.indexWhere((i) => i.label == 'Kitchen');
      if (kitchenIdx >= 0) _index = kitchenIdx;
    }
  }

  List<_NavItem> _itemsFor(StaffUser? user) {
    final items = <_NavItem>[];
    if (user?.canAccessPos == true) {
      items.add(const _NavItem(
        label: 'Bar POS',
        icon: Icons.local_bar_outlined,
        selectedIcon: Icons.local_bar,
        page: PosScreen(area: 'bar'),
        posArea: 'bar',
      ));
      items.add(const _NavItem(
        label: 'Kitchen POS',
        icon: Icons.restaurant_outlined,
        selectedIcon: Icons.restaurant,
        page: PosScreen(area: 'kitchen'),
        posArea: 'kitchen',
      ));
    }
    if (user?.canAccessKitchen == true) {
      items.add(const _NavItem(
        label: 'Kitchen',
        icon: Icons.soup_kitchen_outlined,
        selectedIcon: Icons.soup_kitchen,
        page: KitchenScreen(),
      ));
    }
    return items;
  }

  void _select(int i, List<_NavItem> items) {
    setState(() => _index = i);
    final area = items[i].posArea;
    if (area != null) {
      context.read<CartProvider>().setPosArea(area);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user!;
    final items = _itemsFor(user);

    if (items.isEmpty) {
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

    final safeIndex = _index.clamp(0, items.length - 1);

    return Scaffold(
      body: IndexedStack(
        index: safeIndex,
        children: items.map((i) => i.page).toList(),
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(10, 0, 10, 8),
          child: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: AppTheme.surface,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: AppTheme.border),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.28),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Row(
              children: [
                for (var i = 0; i < items.length; i++)
                  Expanded(
                    child: _NavButton(
                      label: items[i].label,
                      icon: i == safeIndex ? items[i].selectedIcon : items[i].icon,
                      selected: i == safeIndex,
                      onTap: () => _select(i, items),
                    ),
                  ),
                Expanded(
                  child: _NavButton(
                    label: 'Logout',
                    icon: Icons.logout,
                    selected: false,
                    onTap: auth.logout,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _NavButton extends StatelessWidget {
  const _NavButton({
    required this.label,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 180),
      curve: Curves.easeOut,
      margin: const EdgeInsets.symmetric(horizontal: 2),
      decoration: BoxDecoration(
        color: selected ? AppTheme.accent : Colors.transparent,
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20, color: selected ? const Color(0xFF1A1208) : AppTheme.muted),
              const SizedBox(height: 2),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  color: selected ? const Color(0xFF1A1208) : AppTheme.muted,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
