class StaffUser {
  StaffUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.canAccessPos,
    required this.canAccessKitchen,
    required this.canControlKitchen,
    required this.home,
    this.isCashier = false,
    this.isKitchen = false,
  });

  final int id;
  final String name;
  final String email;
  final String? role;
  final bool canAccessPos;
  final bool canAccessKitchen;
  final bool canControlKitchen;
  final String home;
  final bool isCashier;
  final bool isKitchen;

  factory StaffUser.fromJson(Map<String, dynamic> json) {
    return StaffUser(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      role: json['role'] as String?,
      canAccessPos: json['can_access_pos'] == true,
      canAccessKitchen: json['can_access_kitchen'] == true,
      canControlKitchen: json['can_control_kitchen'] == true,
      home: json['home'] as String? ?? 'pos',
      isCashier: json['is_cashier'] == true,
      isKitchen: json['is_kitchen'] == true,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'role': role,
        'can_access_pos': canAccessPos,
        'can_access_kitchen': canAccessKitchen,
        'can_control_kitchen': canControlKitchen,
        'home': home,
        'is_cashier': isCashier,
        'is_kitchen': isKitchen,
      };
}

class CatalogProduct {
  CatalogProduct({
    required this.id,
    required this.name,
    required this.sellingPrice,
    required this.stockQuantity,
    required this.categoryId,
  });

  final int id;
  final String name;
  final double sellingPrice;
  final int stockQuantity;
  final int categoryId;

  factory CatalogProduct.fromJson(Map<String, dynamic> json) {
    return CatalogProduct(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      sellingPrice: (json['selling_price'] as num?)?.toDouble() ?? 0,
      stockQuantity: (json['stock_quantity'] as num?)?.toInt() ?? 0,
      categoryId: json['category_id'] as int? ?? 0,
    );
  }
}

class CatalogCategory {
  CatalogCategory({
    required this.id,
    required this.name,
    required this.products,
  });

  final int id;
  final String name;
  final List<CatalogProduct> products;

  factory CatalogCategory.fromJson(Map<String, dynamic> json) {
    return CatalogCategory(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      products: ((json['products'] as List?) ?? [])
          .map((e) => CatalogProduct.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(),
    );
  }
}

class CartItem {
  CartItem({
    required this.product,
    required this.quantity,
  });

  final CatalogProduct product;
  int quantity;

  double get lineTotal => product.sellingPrice * quantity;
}

class KitchenOrder {
  KitchenOrder({
    required this.id,
    required this.invoiceNumber,
    required this.kitchenStatus,
    required this.createdAt,
    required this.items,
    this.notes,
    this.tableNumber,
    this.guestName,
    this.customerName,
  });

  final int id;
  final String invoiceNumber;
  final String kitchenStatus;
  final String createdAt;
  final String? notes;
  final String? tableNumber;
  final String? guestName;
  final String? customerName;
  final List<KitchenOrderItem> items;

  factory KitchenOrder.fromJson(Map<String, dynamic> json) {
    return KitchenOrder(
      id: json['id'] as int,
      invoiceNumber: json['invoice_number'] as String? ?? '',
      kitchenStatus: json['kitchen_status'] as String? ?? '',
      createdAt: json['created_at'] as String? ?? '',
      notes: json['notes'] as String?,
      tableNumber: (json['table'] as Map?)?['number']?.toString(),
      guestName: (json['table_guest'] as Map?)?['guest_name']?.toString(),
      customerName: (json['customer'] as Map?)?['name']?.toString(),
      items: ((json['items'] as List?) ?? [])
          .map((e) => KitchenOrderItem.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(),
    );
  }

  String get displayMeta {
    final parts = <String>[
      if (tableNumber != null && tableNumber!.isNotEmpty) tableNumber!,
      if (guestName != null && guestName!.isNotEmpty) guestName!,
      if (customerName != null && customerName!.isNotEmpty) customerName!,
    ];
    return parts.isEmpty ? 'Walk-in' : parts.join(' · ');
  }
}

class KitchenOrderItem {
  KitchenOrderItem({
    required this.id,
    required this.productName,
    required this.quantity,
  });

  final int id;
  final String productName;
  final int quantity;

  factory KitchenOrderItem.fromJson(Map<String, dynamic> json) {
    return KitchenOrderItem(
      id: json['id'] as int? ?? 0,
      productName: json['product_name'] as String? ?? '',
      quantity: (json['quantity'] as num?)?.toInt() ?? 0,
    );
  }
}

class PosTable {
  PosTable({
    required this.id,
    required this.number,
    this.status,
    this.guestCount = 0,
  });
  final int id;
  final String number;
  final String? status;
  final int guestCount;

  factory PosTable.fromJson(Map<String, dynamic> json) {
    return PosTable(
      id: json['id'] as int,
      number: json['number']?.toString() ?? '',
      status: json['status']?.toString(),
      guestCount: (json['guest_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class TableGuest {
  TableGuest({
    required this.id,
    required this.guestName,
    this.customerName,
  });

  final int id;
  final String guestName;
  final String? customerName;

  factory TableGuest.fromJson(Map<String, dynamic> json) {
    return TableGuest(
      id: json['id'] as int,
      guestName: json['guest_name'] as String? ?? '',
      customerName: (json['customer'] as Map?)?['name']?.toString(),
    );
  }
}

class CreditCustomer {
  CreditCustomer({
    required this.id,
    required this.name,
    required this.availableCredit,
  });

  final int id;
  final String name;
  final double availableCredit;

  factory CreditCustomer.fromJson(Map<String, dynamic> json) {
    return CreditCustomer(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      availableCredit: (json['available_credit'] as num?)?.toDouble() ?? 0,
    );
  }
}

class SaleReceipt {
  SaleReceipt({
    required this.id,
    required this.invoiceNumber,
    required this.total,
    required this.amountPaid,
    required this.change,
    required this.paymentMethod,
    required this.items,
    this.kitchenStatus,
    this.tableNumber,
    this.customerName,
  });

  final int id;
  final String invoiceNumber;
  final double total;
  final double amountPaid;
  final double change;
  final String paymentMethod;
  final String? kitchenStatus;
  final String? tableNumber;
  final String? customerName;
  final List<KitchenOrderItem> items;

  factory SaleReceipt.fromJson(Map<String, dynamic> json) {
    return SaleReceipt(
      id: json['id'] as int,
      invoiceNumber: json['invoice_number'] as String? ?? '',
      total: (json['total'] as num?)?.toDouble() ?? 0,
      amountPaid: (json['amount_paid'] as num?)?.toDouble() ?? 0,
      change: (json['change'] as num?)?.toDouble() ?? 0,
      paymentMethod: json['payment_method'] as String? ?? '',
      kitchenStatus: json['kitchen_status'] as String?,
      tableNumber: (json['table'] as Map?)?['number']?.toString(),
      customerName: (json['customer'] as Map?)?['name']?.toString(),
      items: ((json['items'] as List?) ?? [])
          .map((e) => KitchenOrderItem.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(),
    );
  }
}
