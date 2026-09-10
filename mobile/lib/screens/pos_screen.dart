import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../services/api_client.dart';
import '../theme/app_theme.dart';

class PosScreen extends StatefulWidget {
  const PosScreen({super.key});

  @override
  State<PosScreen> createState() => _PosScreenState();
}

class _PosScreenState extends State<PosScreen> {
  final _money = NumberFormat.currency(locale: 'en_NG', symbol: '₦');
  final _searchCtrl = TextEditingController();

  Timer? _readyTimer;
  List<CatalogCategory> _categories = [];
  List<PosTable> _tables = [];
  List<CreditCustomer> _customers = [];
  List<TableGuest> _guests = [];
  List<KitchenOrder> _ready = [];
  int? _selectedCategoryId;
  bool _loading = true;
  bool _loadingGuests = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
    _readyTimer = Timer.periodic(const Duration(seconds: 8), (_) => _refreshReady());
  }

  @override
  void dispose() {
    _readyTimer?.cancel();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = context.read<ApiClient>();
      final catalog = await api.get('/staff/pos/catalog');
      final tables = await api.get('/staff/pos/tables');
      final customers = await api.get('/staff/pos/customers');
      final ready = await api.get('/staff/pos/ready-orders');

      _categories = ((catalog['categories'] as List?) ?? [])
          .map((e) => CatalogCategory.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      _tables = ((tables['tables'] as List?) ?? [])
          .map((e) => PosTable.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      _customers = ((customers['customers'] as List?) ?? [])
          .map((e) => CreditCustomer.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      _ready = ((ready['orders'] as List?) ?? [])
          .map((e) => KitchenOrder.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();

      if (!mounted) return;
      final cart = context.read<CartProvider>();
      if (cart.tableId != null) {
        await _loadGuests(cart.tableId!, silent: true);
      }
    } catch (e) {
      _error = e.toString();
    }
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _refreshReady() async {
    if (!mounted || _loading) return;
    try {
      final ready = await context.read<ApiClient>().get('/staff/pos/ready-orders');
      if (!mounted) return;
      setState(() {
        _ready = ((ready['orders'] as List?) ?? [])
            .map((e) => KitchenOrder.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList();
      });
    } catch (_) {}
  }

  Future<void> _loadGuests(int tableId, {bool silent = false}) async {
    if (!silent) setState(() => _loadingGuests = true);
    try {
      final data = await context.read<ApiClient>().get('/staff/pos/tables/$tableId/guests');
      final guests = ((data['guests'] as List?) ?? [])
          .map((e) => TableGuest.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      if (!mounted) return;
      setState(() => _guests = guests);

      final cart = context.read<CartProvider>();
      if (cart.guestId != null && !guests.any((g) => g.id == cart.guestId)) {
        cart.clearGuest();
      } else if (cart.guestId == null && guests.length == 1) {
        cart.selectGuest(id: guests.first.id, name: guests.first.guestName);
      }
    } catch (e) {
      if (!mounted) return;
      if (!silent) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
      setState(() => _guests = []);
    } finally {
      if (mounted && !silent) setState(() => _loadingGuests = false);
    }
  }

  Future<void> _onTableChanged(int? tableId) async {
    final cart = context.read<CartProvider>();
    if (tableId == null) {
      cart.selectWalkIn();
      setState(() => _guests = []);
      return;
    }
    final table = _tables.firstWhere((t) => t.id == tableId);
    cart.selectTable(id: table.id, number: table.number);
    await _loadGuests(table.id);
  }

  Future<void> _addGuest() async {
    final cart = context.read<CartProvider>();
    final tableId = cart.tableId;
    if (tableId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Select a table first')),
      );
      return;
    }

    final nameCtrl = TextEditingController(
      text: 'Guest ${_guests.length + 1}',
    );

    final name = await showDialog<String>(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          backgroundColor: AppTheme.surface,
          title: Text('Add guest · ${cart.tableNumber ?? 'Table'}'),
          content: TextField(
            controller: nameCtrl,
            autofocus: true,
            textCapitalization: TextCapitalization.words,
            decoration: const InputDecoration(
              labelText: 'Guest name',
              hintText: 'e.g. John',
            ),
            onSubmitted: (v) => Navigator.pop(ctx, v.trim()),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
            FilledButton(
              onPressed: () => Navigator.pop(ctx, nameCtrl.text.trim()),
              child: const Text('Add'),
            ),
          ],
        );
      },
    );

    final guestName = name?.trim() ?? '';
    nameCtrl.dispose();
    if (guestName.isEmpty || !mounted) return;

    try {
      final res = await context.read<ApiClient>().post(
        '/staff/pos/tables/$tableId/guests',
        body: {'guest_name': guestName},
      );
      final guestJson = res['guest'];
      if (guestJson is! Map) {
        await _loadGuests(tableId);
        return;
      }
      final guest = TableGuest.fromJson(Map<String, dynamic>.from(guestJson));
      setState(() {
        _guests = [..._guests, guest];
      });
      cart.selectGuest(id: guest.id, name: guest.guestName);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('${guest.guestName} added — taking their order')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  List<CatalogProduct> get _products {
    final q = _searchCtrl.text.trim().toLowerCase();
    final list = <CatalogProduct>[];
    for (final cat in _categories) {
      if (_selectedCategoryId != null && cat.id != _selectedCategoryId) continue;
      for (final p in cat.products) {
        if (q.isEmpty || p.name.toLowerCase().contains(q)) list.add(p);
      }
    }
    return list;
  }

  void _tapProduct(CatalogProduct p) {
    final cart = context.read<CartProvider>();
    if (!cart.canAddItems()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Select or add a guest before ordering')),
      );
      return;
    }
    cart.add(p);
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${p.name} → ${cart.orderLabel}'),
        duration: const Duration(milliseconds: 900),
      ),
    );
  }

  Future<void> _openCart() async {
    final cart = context.read<CartProvider>();
    if (cart.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cart is empty')));
      return;
    }
    if (cart.requiresGuest && !cart.hasGuestSelected) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Select a guest for this table order')),
      );
      return;
    }

    String payment = 'cash';
    int? customerId;
    final amountCtrl = TextEditingController(text: cart.total.toStringAsFixed(2));
    final notesCtrl = TextEditingController();
    final checkoutTableId = cart.tableId;
    final checkoutGuestId = cart.guestId;
    final orderLabel = cart.orderLabel;

    void syncAmount() {
      if (payment != 'credit') {
        amountCtrl.text = cart.total.toStringAsFixed(2);
      }
    }

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppTheme.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModal) {
            return Padding(
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                top: 16,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Cart', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                              Text(orderLabel, style: const TextStyle(color: AppTheme.muted, fontSize: 13)),
                            ],
                          ),
                        ),
                        IconButton(onPressed: () => Navigator.pop(ctx, false), icon: const Icon(Icons.close)),
                      ],
                    ),
                    ...cart.items.map((item) {
                      return ListTile(
                        contentPadding: EdgeInsets.zero,
                        title: Text(item.product.name, style: const TextStyle(fontWeight: FontWeight.w700)),
                        subtitle: Text(_money.format(item.product.sellingPrice)),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            IconButton(
                              onPressed: () {
                                cart.setQty(item.product.id, item.quantity - 1);
                                syncAmount();
                                setModal(() {});
                                if (cart.isEmpty) Navigator.pop(ctx, false);
                              },
                              icon: const Icon(Icons.remove_circle_outline),
                            ),
                            Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.w800)),
                            IconButton(
                              onPressed: () {
                                cart.setQty(item.product.id, item.quantity + 1);
                                syncAmount();
                                setModal(() {});
                              },
                              icon: const Icon(Icons.add_circle_outline),
                            ),
                          ],
                        ),
                      );
                    }),
                    const Divider(color: AppTheme.border),
                    DropdownButtonFormField<String>(
                      value: payment,
                      decoration: const InputDecoration(labelText: 'Payment method'),
                      items: const [
                        DropdownMenuItem(value: 'cash', child: Text('Cash')),
                        DropdownMenuItem(value: 'transfer', child: Text('Transfer')),
                        DropdownMenuItem(value: 'pos', child: Text('POS')),
                        DropdownMenuItem(value: 'credit', child: Text('Credit')),
                      ],
                      onChanged: (v) => setModal(() {
                        payment = v ?? 'cash';
                        syncAmount();
                      }),
                    ),
                    if (payment == 'credit') ...[
                      const SizedBox(height: 10),
                      DropdownButtonFormField<int?>(
                        value: customerId,
                        decoration: const InputDecoration(labelText: 'Credit customer'),
                        items: [
                          const DropdownMenuItem(value: null, child: Text('-- Choose --')),
                          ..._customers.map(
                            (c) => DropdownMenuItem(
                              value: c.id,
                              child: Text('${c.name} (₦${c.availableCredit.toStringAsFixed(0)})'),
                            ),
                          ),
                        ],
                        onChanged: (v) => setModal(() => customerId = v),
                      ),
                    ] else ...[
                      const SizedBox(height: 10),
                      TextField(
                        controller: amountCtrl,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: const InputDecoration(labelText: 'Amount paid'),
                      ),
                    ],
                    const SizedBox(height: 10),
                    TextField(
                      controller: notesCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Notes (optional)',
                        hintText: 'Kitchen notes, allergies…',
                      ),
                    ),
                    const SizedBox(height: 14),
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppTheme.surface2,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Row(
                        children: [
                          const Text('Total', style: TextStyle(fontWeight: FontWeight.w700)),
                          const Spacer(),
                          Text(
                            _money.format(cart.total),
                            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.accent),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    FilledButton(
                      onPressed: () {
                        if (payment == 'credit' && customerId == null) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            const SnackBar(content: Text('Choose a credit customer')),
                          );
                          return;
                        }
                        if (payment != 'credit') {
                          final paid = double.tryParse(amountCtrl.text) ?? 0;
                          if (paid < cart.total) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(content: Text('Amount paid must cover the total')),
                            );
                            return;
                          }
                        }
                        Navigator.pop(ctx, true);
                      },
                      child: const Text('Complete Sale'),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );

    final notes = notesCtrl.text.trim();
    final amountPaid = payment == 'credit' ? 0.0 : double.tryParse(amountCtrl.text) ?? 0.0;
    amountCtrl.dispose();
    notesCtrl.dispose();

    if (confirmed != true || !mounted) return;

    try {
      final api = context.read<ApiClient>();
      final body = {
        'items': cart.items
            .map((i) => {
                  'product_id': i.product.id,
                  'quantity': i.quantity,
                  'unit_price': i.product.sellingPrice,
                  'discount': 0,
                })
            .toList(),
        'payment_method': payment,
        'amount_paid': amountPaid,
        'customer_id': payment == 'credit' ? customerId : null,
        'table_id': checkoutTableId,
        'table_guest_id': checkoutGuestId,
        'discount': 0,
        if (notes.isNotEmpty) 'notes': notes,
      };
      final res = await api.post('/staff/pos/checkout', body: body);
      cart.clear();
      if (!mounted) return;

      final saleJson = res['sale'];
      if (saleJson is Map) {
        final receipt = SaleReceipt.fromJson(Map<String, dynamic>.from(saleJson));
        await _showReceipt(receipt);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'Sale completed')),
        );
      }
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _showReceipt(SaleReceipt sale) async {
    await showDialog<void>(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          backgroundColor: AppTheme.surface,
          title: Text('Sale #${sale.invoiceNumber}'),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              mainAxisSize: MainAxisSize.min,
              children: [
                if (sale.tableNumber != null) Text('Table ${sale.tableNumber}'),
                if (sale.customerName != null) Text(sale.customerName!),
                Text(
                  sale.paymentMethod.toUpperCase(),
                  style: const TextStyle(color: AppTheme.muted, fontSize: 12),
                ),
                const SizedBox(height: 10),
                ...sale.items.map(
                  (i) => Padding(
                    padding: const EdgeInsets.only(bottom: 4),
                    child: Text('${i.quantity}× ${i.productName}'),
                  ),
                ),
                const Divider(color: AppTheme.border),
                Row(
                  children: [
                    const Text('Total', style: TextStyle(fontWeight: FontWeight.w800)),
                    const Spacer(),
                    Text(_money.format(sale.total), style: const TextStyle(fontWeight: FontWeight.w800, color: AppTheme.accent)),
                  ],
                ),
                if (sale.paymentMethod != 'credit') ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Text('Paid', style: TextStyle(color: AppTheme.muted)),
                      const Spacer(),
                      Text(_money.format(sale.amountPaid)),
                    ],
                  ),
                  if (sale.change > 0)
                    Row(
                      children: [
                        const Text('Change', style: TextStyle(color: AppTheme.muted)),
                        const Spacer(),
                        Text(_money.format(sale.change)),
                      ],
                    ),
                ],
                if (sale.kitchenStatus != null) ...[
                  const SizedBox(height: 10),
                  Text(
                    'Sent to kitchen (${sale.kitchenStatus})',
                    style: const TextStyle(color: AppTheme.success, fontWeight: FontWeight.w700),
                  ),
                ],
              ],
            ),
          ),
          actions: [
            FilledButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Done'),
            ),
          ],
        );
      },
    );
  }

  Future<void> _markServed(int id) async {
    try {
      await context.read<ApiClient>().post('/staff/kitchen/$id/served');
      await _refreshReady();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Widget _tableGuestBar(CartProvider cart) {
    return Container(
      margin: const EdgeInsets.fromLTRB(14, 8, 14, 4),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          DropdownButtonFormField<int?>(
            value: cart.tableId,
            decoration: const InputDecoration(
              labelText: 'Table',
              isDense: true,
              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            ),
            items: [
              const DropdownMenuItem(value: null, child: Text('Walk-in / No table')),
              ..._tables.map(
                (t) => DropdownMenuItem(
                  value: t.id,
                  child: Text(
                    t.guestCount > 0 ? '${t.number} (${t.guestCount} guests)' : t.number,
                  ),
                ),
              ),
            ],
            onChanged: (v) => _onTableChanged(v),
          ),
          if (cart.tableId != null) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Text('Guests', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                const Spacer(),
                if (_loadingGuests)
                  const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                else
                  TextButton.icon(
                    onPressed: _addGuest,
                    icon: const Icon(Icons.person_add_alt_1, size: 18),
                    label: const Text('Add guest'),
                    style: TextButton.styleFrom(
                      foregroundColor: AppTheme.accent,
                      padding: EdgeInsets.zero,
                      visualDensity: VisualDensity.compact,
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 6),
            if (_guests.isEmpty)
              Text(
                'No guests yet — add one to start ordering',
                style: TextStyle(color: AppTheme.muted.withValues(alpha: 0.9), fontSize: 13),
              )
            else
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: _guests.map((g) {
                  final selected = cart.guestId == g.id;
                  final qty = cart.countForGuest(g.id);
                  return ChoiceChip(
                    label: Text(qty > 0 ? '${g.guestName} ($qty)' : g.guestName),
                    selected: selected,
                    onSelected: (_) => cart.selectGuest(id: g.id, name: g.guestName),
                    selectedColor: AppTheme.accent.withValues(alpha: 0.22),
                    labelStyle: TextStyle(
                      color: selected ? AppTheme.accent : AppTheme.muted,
                      fontWeight: FontWeight.w700,
                    ),
                    side: BorderSide(
                      color: selected ? AppTheme.accent.withValues(alpha: 0.45) : AppTheme.border,
                    ),
                    backgroundColor: AppTheme.surface2,
                  );
                }).toList(),
              ),
          ],
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Cashier POS'),
            Text(user?.name ?? '', style: const TextStyle(fontSize: 12, color: AppTheme.muted, fontWeight: FontWeight.w500)),
          ],
        ),
        actions: [
          IconButton(onPressed: _loading ? null : _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
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
              : Stack(
                  children: [
                    RefreshIndicator(
                      onRefresh: _load,
                      child: CustomScrollView(
                        slivers: [
                          if (_ready.isNotEmpty)
                            SliverToBoxAdapter(
                              child: Container(
                                margin: const EdgeInsets.fromLTRB(14, 8, 14, 8),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(colors: [
                                    AppTheme.success.withValues(alpha: 0.18),
                                    AppTheme.info.withValues(alpha: 0.12),
                                  ]),
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: AppTheme.success.withValues(alpha: 0.35)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      '${_ready.length} ready order(s)',
                                      style: const TextStyle(fontWeight: FontWeight.w800),
                                    ),
                                    const SizedBox(height: 8),
                                    ..._ready.take(5).map((o) => Padding(
                                          padding: const EdgeInsets.only(bottom: 8),
                                          child: Row(
                                            children: [
                                              Expanded(
                                                child: Text('#${o.invoiceNumber} · ${o.displayMeta}'),
                                              ),
                                              FilledButton(
                                                style: FilledButton.styleFrom(
                                                  backgroundColor: AppTheme.success,
                                                  minimumSize: const Size(84, 36),
                                                ),
                                                onPressed: () => _markServed(o.id),
                                                child: const Text('Served'),
                                              ),
                                            ],
                                          ),
                                        )),
                                  ],
                                ),
                              ),
                            ),
                          SliverToBoxAdapter(child: _tableGuestBar(cart)),
                          SliverToBoxAdapter(
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(14, 4, 14, 8),
                              child: TextField(
                                controller: _searchCtrl,
                                onChanged: (_) => setState(() {}),
                                decoration: const InputDecoration(
                                  prefixIcon: Icon(Icons.search),
                                  hintText: 'Search products...',
                                ),
                              ),
                            ),
                          ),
                          SliverToBoxAdapter(
                            child: SizedBox(
                              height: 46,
                              child: ListView(
                                scrollDirection: Axis.horizontal,
                                padding: const EdgeInsets.symmetric(horizontal: 14),
                                children: [
                                  _CatChip(
                                    label: 'All',
                                    selected: _selectedCategoryId == null,
                                    onTap: () => setState(() => _selectedCategoryId = null),
                                  ),
                                  ..._categories.map(
                                    (c) => _CatChip(
                                      label: c.name,
                                      selected: _selectedCategoryId == c.id,
                                      onTap: () => setState(() => _selectedCategoryId = c.id),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SliverToBoxAdapter(child: SizedBox(height: 10)),
                          SliverPadding(
                            padding: EdgeInsets.fromLTRB(14, 0, 14, cart.count > 0 ? 100 : 24),
                            sliver: SliverGrid(
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                mainAxisSpacing: 10,
                                crossAxisSpacing: 10,
                                childAspectRatio: 0.95,
                              ),
                              delegate: SliverChildBuilderDelegate(
                                (context, index) {
                                  final p = _products[index];
                                  return Material(
                                    color: AppTheme.surface,
                                    borderRadius: BorderRadius.circular(16),
                                    child: InkWell(
                                      borderRadius: BorderRadius.circular(16),
                                      onTap: () => _tapProduct(p),
                                      child: Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          borderRadius: BorderRadius.circular(16),
                                          border: Border.all(color: AppTheme.border),
                                        ),
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                p.name,
                                                maxLines: 3,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(fontWeight: FontWeight.w700, height: 1.2),
                                              ),
                                            ),
                                            Text('Stock ${p.stockQuantity}', style: const TextStyle(color: AppTheme.muted, fontSize: 12)),
                                            const SizedBox(height: 6),
                                            Text(
                                              _money.format(p.sellingPrice),
                                              style: const TextStyle(
                                                color: AppTheme.accent,
                                                fontWeight: FontWeight.w800,
                                                fontSize: 16,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  );
                                },
                                childCount: _products.length,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (cart.count > 0)
                      Positioned(
                        left: 14,
                        right: 14,
                        bottom: 14,
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            onTap: _openCart,
                            borderRadius: BorderRadius.circular(16),
                            child: Ink(
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(colors: [AppTheme.accent, AppTheme.accentDeep]),
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppTheme.accent.withValues(alpha: 0.35),
                                    blurRadius: 18,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                child: Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 14,
                                      backgroundColor: Colors.black.withValues(alpha: 0.2),
                                      child: Text('${cart.count}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Text(
                                            _money.format(cart.total),
                                            style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF1A1208)),
                                          ),
                                          Text(
                                            cart.orderLabel,
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.w600,
                                              color: const Color(0xFF1A1208).withValues(alpha: 0.7),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const Text(
                                      'Checkout',
                                      style: TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF1A1208)),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }
}

class _CatChip extends StatelessWidget {
  const _CatChip({required this.label, required this.selected, required this.onTap});
  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        selectedColor: AppTheme.accent.withValues(alpha: 0.22),
        labelStyle: TextStyle(
          color: selected ? AppTheme.accent : AppTheme.muted,
          fontWeight: FontWeight.w700,
        ),
        side: BorderSide(color: selected ? AppTheme.accent.withValues(alpha: 0.45) : AppTheme.border),
        backgroundColor: AppTheme.surface2,
      ),
    );
  }
}
