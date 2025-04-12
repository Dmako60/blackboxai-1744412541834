import 'user.dart';
import 'property.dart';
import 'payment.dart';

class Reservation {
  final int id;
  final int userId;
  final int propertyId;
  final DateTime checkIn;
  final DateTime checkOut;
  final int guestCount;
  final double totalPrice;
  final String status;
  final String? specialRequests;
  final User? user;
  final Property? property;
  final Payment? payment;
  final DateTime createdAt;
  final DateTime updatedAt;

  Reservation({
    required this.id,
    required this.userId,
    required this.propertyId,
    required this.checkIn,
    required this.checkOut,
    required this.guestCount,
    required this.totalPrice,
    required this.status,
    this.specialRequests,
    this.user,
    this.property,
    this.payment,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Reservation.fromJson(Map<String, dynamic> json) {
    return Reservation(
      id: json['id'],
      userId: json['user_id'],
      propertyId: json['property_id'],
      checkIn: DateTime.parse(json['check_in']),
      checkOut: DateTime.parse(json['check_out']),
      guestCount: json['guest_count'],
      totalPrice: double.parse(json['total_price'].toString()),
      status: json['status'],
      specialRequests: json['special_requests'],
      user: json['user'] != null ? User.fromJson(json['user']) : null,
      property: json['property'] != null ? Property.fromJson(json['property']) : null,
      payment: json['payment'] != null ? Payment.fromJson(json['payment']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'property_id': propertyId,
      'check_in': checkIn.toIso8601String(),
      'check_out': checkOut.toIso8601String(),
      'guest_count': guestCount,
      'total_price': totalPrice,
      'status': status,
      'special_requests': specialRequests,
      'user': user?.toJson(),
      'property': property?.toJson(),
      'payment': payment?.toJson(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Reservation copyWith({
    int? id,
    int? userId,
    int? propertyId,
    DateTime? checkIn,
    DateTime? checkOut,
    int? guestCount,
    double? totalPrice,
    String? status,
    String? specialRequests,
    User? user,
    Property? property,
    Payment? payment,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Reservation(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      propertyId: propertyId ?? this.propertyId,
      checkIn: checkIn ?? this.checkIn,
      checkOut: checkOut ?? this.checkOut,
      guestCount: guestCount ?? this.guestCount,
      totalPrice: totalPrice ?? this.totalPrice,
      status: status ?? this.status,
      specialRequests: specialRequests ?? this.specialRequests,
      user: user ?? this.user,
      property: property ?? this.property,
      payment: payment ?? this.payment,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  int get numberOfNights {
    return checkOut.difference(checkIn).inDays;
  }

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isConfirmed => status.toLowerCase() == 'confirmed';
  bool get isCancelled => status.toLowerCase() == 'cancelled';
  bool get isCompleted => status.toLowerCase() == 'completed';

  bool get canBeCancelled {
    final now = DateTime.now();
    return !isCancelled && 
           !isCompleted && 
           now.isBefore(checkIn) &&
           now.difference(checkIn).inDays >= 2; // 48-hour cancellation policy
  }

  bool get canBeReviewed {
    return isCompleted && 
           DateTime.now().difference(checkOut).inDays <= 14; // 14-day review window
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Reservation &&
      other.id == id &&
      other.userId == userId &&
      other.propertyId == propertyId &&
      other.checkIn == checkIn &&
      other.checkOut == checkOut &&
      other.guestCount == guestCount &&
      other.totalPrice == totalPrice &&
      other.status == status &&
      other.specialRequests == specialRequests;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      userId.hashCode ^
      propertyId.hashCode ^
      checkIn.hashCode ^
      checkOut.hashCode ^
      guestCount.hashCode ^
      totalPrice.hashCode ^
      status.hashCode ^
      specialRequests.hashCode;
  }
}
