import 'package:flutter/foundation.dart';

import '../models/models.dart';

/// Separate carts per table guest so cashiers can take orders for different people.
class CartProvider extends ChangeNotifier {
  int? tableId;
  String? tableNumber;
  int? guestId;
  String? guestName;

  /// key: walkin | table_{id}_guest_{guestId}
  final Map<String, Map<int, CartItem>> _carts = {};

  String get _key {
    if (tableId == null || guestId == null) return 'walkin';
    return 'table_${tableId}_guest_$guestId';
  }

  Map<int, CartItem> get _items => _carts.putIfAbsent(_key, () => {});

  List<CartItem> get items => _items.values.toList();
  int get count => _items.values.fold(0, (sum, i) => sum + i.quantity);
  double get total => _items.values.fold(0.0, (sum, i) => sum + i.lineTotal);
  bool get isEmpty => _items.isEmpty;
  bool get requiresGuest => tableId != null;
  bool get hasGuestSelected => guestId != null;

  String get orderLabel {
    if (tableId != null && guestName != null) {
      return '${tableNumber ?? 'Table'} · $guestName';
    }
    if (tableId != null) return tableNumber ?? 'Table';
    return 'Walk-in';
  }

  int countForGuest(int? forGuestId) {
    if (tableId == null || forGuestId == null) return 0;
    final key = 'table_${tableId}_guest_$forGuestId';
    final items = _carts[key];
    if (items == null) return 0;
    return items.values.fold(0, (sum, i) => sum + i.quantity);
  }

  void selectWalkIn() {
    tableId = null;
    tableNumber = null;
    guestId = null;
    guestName = null;
    notifyListeners();
  }

  void selectTable({required int id, required String number}) {
    if (tableId == id) return;
    tableId = id;
    tableNumber = number;
    guestId = null;
    guestName = null;
    notifyListeners();
  }

  void selectGuest({required int id, required String name}) {
    guestId = id;
    guestName = name;
    notifyListeners();
  }

  void clearGuest() {
    guestId = null;
    guestName = null;
    notifyListeners();
  }

  bool canAddItems() => tableId == null || guestId != null;

  void add(CatalogProduct product) {
    if (!canAddItems()) return;
    final existing = _items[product.id];
    if (existing != null) {
      if (existing.quantity >= product.stockQuantity) return;
      existing.quantity += 1;
    } else {
      _items[product.id] = CartItem(product: product, quantity: 1);
    }
    notifyListeners();
  }

  void setQty(int productId, int qty) {
    final item = _items[productId];
    if (item == null) return;
    if (qty <= 0) {
      _items.remove(productId);
    } else if (qty <= item.product.stockQuantity) {
      item.quantity = qty;
    }
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }

  void clearAll() {
    _carts.clear();
    tableId = null;
    tableNumber = null;
    guestId = null;
    guestName = null;
    notifyListeners();
  }
}
