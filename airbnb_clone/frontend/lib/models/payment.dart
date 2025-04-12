import 'reservation.dart';

class Payment {
  final int id;
  final int reservationId;
  final String paymentMethod;
  final String transactionId;
  final double amount;
  final String currency;
  final String status;
  final Map<String, dynamic>? paymentDetails;
  final Reservation? reservation;
  final DateTime? paidAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  Payment({
    required this.id,
    required this.reservationId,
    required this.paymentMethod,
    required this.transactionId,
    required this.amount,
    required this.currency,
    required this.status,
    this.paymentDetails,
    this.reservation,
    this.paidAt,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'],
      reservationId: json['reservation_id'],
      paymentMethod: json['payment_method'],
      transactionId: json['transaction_id'],
      amount: double.parse(json['amount'].toString()),
      currency: json['currency'],
      status: json['status'],
      paymentDetails: json['payment_details'] != null 
          ? Map<String, dynamic>.from(json['payment_details'])
          : null,
      reservation: json['reservation'] != null 
          ? Reservation.fromJson(json['reservation'])
          : null,
      paidAt: json['paid_at'] != null 
          ? DateTime.parse(json['paid_at'])
          : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'reservation_id': reservationId,
      'payment_method': paymentMethod,
      'transaction_id': transactionId,
      'amount': amount,
      'currency': currency,
      'status': status,
      'payment_details': paymentDetails,
      'reservation': reservation?.toJson(),
      'paid_at': paidAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Payment copyWith({
    int? id,
    int? reservationId,
    String? paymentMethod,
    String? transactionId,
    double? amount,
    String? currency,
    String? status,
    Map<String, dynamic>? paymentDetails,
    Reservation? reservation,
    DateTime? paidAt,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Payment(
      id: id ?? this.id,
      reservationId: reservationId ?? this.reservationId,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      transactionId: transactionId ?? this.transactionId,
      amount: amount ?? this.amount,
      currency: currency ?? this.currency,
      status: status ?? this.status,
      paymentDetails: paymentDetails ?? this.paymentDetails,
      reservation: reservation ?? this.reservation,
      paidAt: paidAt ?? this.paidAt,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isCompleted => status.toLowerCase() == 'completed';
  bool get isFailed => status.toLowerCase() == 'failed';
  bool get isRefunded => status.toLowerCase() == 'refunded';

  String get formattedAmount {
    return '$currency ${amount.toStringAsFixed(2)}';
  }

  String get formattedStatus {
    return status[0].toUpperCase() + status.substring(1).toLowerCase();
  }

  String get maskedTransactionId {
    if (transactionId.length <= 8) return transactionId;
    return '${transactionId.substring(0, 4)}...${transactionId.substring(transactionId.length - 4)}';
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Payment &&
      other.id == id &&
      other.reservationId == reservationId &&
      other.paymentMethod == paymentMethod &&
      other.transactionId == transactionId &&
      other.amount == amount &&
      other.currency == currency &&
      other.status == status;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      reservationId.hashCode ^
      paymentMethod.hashCode ^
      transactionId.hashCode ^
      amount.hashCode ^
      currency.hashCode ^
      status.hashCode;
  }
}
