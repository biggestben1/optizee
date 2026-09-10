import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_theme.dart';

class KitchenScreen extends StatefulWidget {
  const KitchenScreen({super.key});

  @override
  State<KitchenScreen> createState() => _KitchenScreenState();
}

class _KitchenScreenState extends State<KitchenScreen> {
  Timer? _timer;
  bool _loading = true;
  String? _error;
  String _filter = 'all';
  Map<String, dynamic> _stats = {};
  List<KitchenOrder> _pending = [];
  List<KitchenOrder> _ready = [];
  bool _canControl = false;
  int? _lastPendingCount;

  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(const Duration(seconds: 8), (_) => _load(silent: true));
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }
    try {
      final data = await context.read<ApiClient>().get('/staff/kitchen/live');
      final pending = ((data['pending_orders'] as List?) ?? [])
          .map((e) => KitchenOrder.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      final ready = ((data['ready_orders'] as List?) ?? [])
          .map((e) => KitchenOrder.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      final pendingOnly = pending.where((o) => o.kitchenStatus == 'pending').length;

      if (_lastPendingCount != null && pendingOnly > _lastPendingCount!) {
        SystemSound.play(SystemSoundType.alert);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('New kitchen order')),
          );
        }
      }
      _lastPendingCount = pendingOnly;

      if (!mounted) return;
      setState(() {
        _pending = pending;
        _ready = ready;
        _stats = Map<String, dynamic>.from(data['stats'] as Map? ?? {});
        _canControl = data['can_control'] == true;
        _loading = false;
        _error = null;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  List<KitchenOrder> get _visible {
    final all = [..._pending, ..._ready];
    if (_filter == 'all') return all;
    return all.where((o) => o.kitchenStatus == _filter).toList();
  }

  Future<void> _action(KitchenOrder order, String action) async {
    try {
      await context.read<ApiClient>().post('/staff/kitchen/${order.id}/$action');
      await _load(silent: true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return AppTheme.warning;
      case 'preparing':
        return AppTheme.info;
      case 'ready':
        return AppTheme.success;
      default:
        return AppTheme.muted;
    }
  }

  String _ago(String dateStr) {
    try {
      final dt = DateTime.parse(dateStr);
      final mins = DateTime.now().difference(dt).inMinutes;
      if (mins < 1) return 'Just now';
      if (mins == 1) return '1 min ago';
      return '$mins mins ago';
    } catch (_) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Kitchen'),
            Text(
              '${user?.name ?? ''} · Live orders',
              style: const TextStyle(fontSize: 12, color: AppTheme.muted, fontWeight: FontWeight.w500),
            ),
          ],
        ),
        actions: [
          IconButton(onPressed: () => _load(), icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading && _pending.isEmpty && _ready.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _pending.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(_error!, textAlign: TextAlign.center),
                        const SizedBox(height: 12),
                        FilledButton(onPressed: _load, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(14, 8, 14, 24),
                    children: [
                      Row(
                        children: [
                          _Stat(label: 'Pending', value: '${_stats['pending'] ?? 0}', color: AppTheme.warning),
                          const SizedBox(width: 8),
                          _Stat(label: 'Prep', value: '${_stats['preparing'] ?? 0}', color: AppTheme.info),
                          const SizedBox(width: 8),
                          _Stat(label: 'Ready', value: '${_stats['ready'] ?? 0}', color: AppTheme.success),
                          const SizedBox(width: 8),
                          _Stat(label: 'Served', value: '${_stats['served'] ?? 0}', color: AppTheme.muted),
                        ],
                      ),
                      const SizedBox(height: 14),
                      SizedBox(
                        height: 42,
                        child: ListView(
                          scrollDirection: Axis.horizontal,
                          children: [
                            for (final f in [
                              ('all', 'All'),
                              ('pending', 'Pending'),
                              ('preparing', 'Preparing'),
                              ('ready', 'Ready'),
                            ])
                              Padding(
                                padding: const EdgeInsets.only(right: 8),
                                child: ChoiceChip(
                                  label: Text(f.$2),
                                  selected: _filter == f.$1,
                                  onSelected: (_) => setState(() => _filter = f.$1),
                                  selectedColor: AppTheme.accent.withValues(alpha: 0.22),
                                  labelStyle: TextStyle(
                                    color: _filter == f.$1 ? AppTheme.accent : AppTheme.muted,
                                    fontWeight: FontWeight.w700,
                                  ),
                                  side: BorderSide(
                                    color: _filter == f.$1
                                        ? AppTheme.accent.withValues(alpha: 0.45)
                                        : AppTheme.border,
                                  ),
                                  backgroundColor: AppTheme.surface2,
                                ),
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),
                      if (_visible.isEmpty)
                        const Padding(
                          padding: EdgeInsets.only(top: 60),
                          child: Center(child: Text('No orders right now.', style: TextStyle(color: AppTheme.muted))),
                        )
                      else
                        ..._visible.map((order) {
                          final color = _statusColor(order.kitchenStatus);
                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: AppTheme.surface,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: AppTheme.border),
                            ),
                            child: IntrinsicHeight(
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Container(
                                    width: 5,
                                    decoration: BoxDecoration(
                                      color: color,
                                      borderRadius: const BorderRadius.horizontal(left: Radius.circular(18)),
                                    ),
                                  ),
                                  Expanded(
                                    child: Padding(
                                      padding: const EdgeInsets.all(14),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment: CrossAxisAlignment.start,
                                                  children: [
                                                    Text(
                                                      '#${order.invoiceNumber}',
                                                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                                                    ),
                                                    const SizedBox(height: 2),
                                                    Text(order.displayMeta, style: const TextStyle(color: AppTheme.muted, fontSize: 13)),
                                                    Text(_ago(order.createdAt), style: const TextStyle(color: AppTheme.muted, fontSize: 12)),
                                                  ],
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                                decoration: BoxDecoration(
                                                  color: color.withValues(alpha: 0.18),
                                                  borderRadius: BorderRadius.circular(999),
                                                ),
                                                child: Text(
                                                  order.kitchenStatus,
                                                  style: TextStyle(color: color, fontWeight: FontWeight.w800, fontSize: 12),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 10),
                                          ...order.items.map(
                                            (item) => Padding(
                                              padding: const EdgeInsets.only(bottom: 6),
                                              child: Row(
                                                children: [
                                                  Expanded(child: Text(item.productName)),
                                                  Text('×${item.quantity}', style: const TextStyle(fontWeight: FontWeight.w800)),
                                                ],
                                              ),
                                            ),
                                          ),
                                          if (order.notes != null && order.notes!.isNotEmpty) ...[
                                            const SizedBox(height: 4),
                                            Text('Note: ${order.notes}', style: const TextStyle(color: AppTheme.muted, fontSize: 12)),
                                          ],
                                          const SizedBox(height: 10),
                                          if (order.kitchenStatus == 'pending' && _canControl)
                                            FilledButton(
                                              style: FilledButton.styleFrom(backgroundColor: AppTheme.warning),
                                              onPressed: () => _action(order, 'preparing'),
                                              child: const Text('Start Preparing'),
                                            )
                                          else if (order.kitchenStatus == 'preparing' && _canControl)
                                            FilledButton(
                                              style: FilledButton.styleFrom(backgroundColor: AppTheme.success),
                                              onPressed: () => _action(order, 'ready'),
                                              child: const Text('Mark Ready'),
                                            )
                                          else if (order.kitchenStatus == 'ready')
                                            FilledButton(
                                              onPressed: () => _action(order, 'served'),
                                              child: const Text('Mark Served'),
                                            ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                    ],
                  ),
                ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value, required this.color});
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppTheme.border),
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: color)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 11, color: AppTheme.muted, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );
  }
}
